<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Thin wrapper around CI's email library, pre-configured from .env.
 *
 * Usage from a controller / model:
 *   $this->load->library('mailer');
 *   $ok = $this->mailer->send(
 *       'user@example.com',
 *       'Confirm your email',
 *       '<p>Click <a href="...">here</a></p>'
 *   );
 *
 * For Gmail SMTP, SMTP_PASS must be an "App Password", not the
 * Google account password. See .env.example.
 *
 * ---------------------------------------------------------------------------
 * 2026-09-24: why this class grew a diagnostics half
 * ---------------------------------------------------------------------------
 * Signup and password-reset mail stopped arriving on the live site — not in
 * Spam either, simply gone — and nothing in the codebase could say why:
 *
 *   * A failed send was reported to `log_message('error', ...)` and nowhere
 *     else, and `application/logs/` did not exist in the repo, so on a fresh
 *     deploy CI's logger disabled itself and the error went to /dev/null.
 *   * `send()`'s return value was discarded by both callers, so the user was
 *     shown "account created, now sign in" whether or not the mail left the
 *     building — and login is blocked until they click a link they never got.
 *   * The transport was hard-wired to SMTP even with `SMTP_USER` / `SMTP_PASS`
 *     empty, which fails 100% of the time instead of falling back to the
 *     host's own MTA.
 *   * `smtp_timeout` was CI's default 5 seconds. A shared host opening TLS to
 *     smtp.gmail.com regularly needs more than that, and the timeout looks
 *     exactly like a credential failure from the outside.
 *
 * So: every attempt is now recorded in `email_log` (migration 0031), the last
 * error is readable by the caller, the transport degrades to PHP mail() rather
 * than guaranteeing failure, and `config_status()` reports the mismatches that
 * cause mail to be accepted by the relay and then dropped by the recipient —
 * chiefly a From address on a domain the SMTP account is not authorised to
 * send for, which is what DMARC discards silently. /admin/email-diagnostics
 * renders all of it and can send a test message.
 */
class Mailer
{
    /** @var CI_Controller */
    protected $CI;

    /** Transport actually in use: 'smtp', 'mail' or 'sendmail'. */
    protected $protocol = 'smtp';

    /** The config handed to CI's email library. */
    protected $config = array();

    /** Debug output from the most recent failure ('' when the last send worked). */
    protected $last_error = '';

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library('email');

        $host = (string) env('SMTP_HOST', 'smtp.gmail.com');
        $user = (string) env('SMTP_USER', '');
        $pass = (string) env('SMTP_PASS', '');

        // MAIL_PROTOCOL wins when set ('smtp' / 'mail' / 'sendmail'). Otherwise
        // SMTP is used when it is actually configured, and the host's own MTA
        // when it is not. Forcing SMTP at an empty username is not a safer
        // default — it is a guaranteed bounce on every message the site sends.
        $protocol = strtolower(trim((string) env('MAIL_PROTOCOL', '')));
        if (!in_array($protocol, array('smtp', 'mail', 'sendmail'), true)) {
            $protocol = ($host !== '' && $user !== '' && $pass !== '') ? 'smtp' : 'mail';
        }
        $this->protocol = $protocol;

        $config = array(
            'protocol'  => $protocol,
            'mailtype'  => 'html',
            'charset'   => 'utf-8',
            'wordwrap'  => true,
            'newline'   => "\r\n",
            'crlf'      => "\r\n",
            // CI stamps its own name into BOTH `User-Agent` and `X-Mailer`,
            // so every message announced itself as sent by a PHP framework.
            // Filters read those headers, and "script on a web host" is not
            // the reputation a signup confirmation wants. Name the product.
            'useragent' => 'Pearland411',
            // CI defaults to quoted-printable for HTML, which is right; being
            // explicit keeps 8-bit characters out of the raw body, where some
            // filters score them.
            'bcc_batch_mode' => false,
        );

        if ($protocol === 'smtp') {
            $config['smtp_host']    = $host;
            $config['smtp_port']    = (int) env('SMTP_PORT', 587);
            $config['smtp_user']    = $user;
            $config['smtp_pass']    = $pass;
            $config['smtp_crypto']  = (string) env('SMTP_CRYPTO', 'tls');
            // CI's default is 5 seconds. A TLS handshake to a public relay from
            // a busy shared host routinely takes longer, and the failure that
            // produces is indistinguishable from a wrong password.
            $config['smtp_timeout'] = max(5, (int) env('SMTP_TIMEOUT', 30));
            // 'none' is a legitimate value for a local relay on port 25; CI
            // wants the key absent rather than set to a bogus scheme.
            if (!in_array($config['smtp_crypto'], array('tls', 'ssl'), true)) {
                unset($config['smtp_crypto']);
            }
        } elseif ($protocol === 'sendmail') {
            $config['mailpath'] = (string) env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail');
        }

        $this->config = $config;
        $this->CI->email->initialize($config);
    }

    /* =====================================================================
     * Sending
     * ===================================================================== */

    /**
     * Send an HTML email. Returns true on success.
     *
     * @param string|array $to       Single address or array of addresses.
     * @param string       $subject
     * @param string       $html
     * @param string|null  $text     Optional plain-text alternate. When null
     *                               one is derived from $html — a multipart
     *                               message scores better than HTML alone and
     *                               some filters reject HTML-only outright.
     * @param array        $opts     'reply_to' / 'reply_to_name' — for
     *                               notifications about someone else's
     *                               enquiry, so hitting Reply answers them
     *                               rather than the site's no-reply box.
     *                               Ignored when the address is not valid.
     *                               'log_type' — label for the email_log row.
     */
    public function send($to, $subject, $html, $text = null, array $opts = array())
    {
        $this->last_error = '';

        $from_addr = $this->from_address();
        $from_name = $this->from_name();

        $this->CI->email->clear(true);
        $this->CI->email->from($from_addr, $from_name);

        $reply_to = isset($opts['reply_to']) ? trim((string) $opts['reply_to']) : '';
        if ($reply_to === '' || !filter_var($reply_to, FILTER_VALIDATE_EMAIL)) {
            // No caller-supplied Reply-To: point it at a mailbox a person
            // actually reads, via the same helper every public form uses.
            // CI's own fallback is the From address — `no-reply@` — which
            // sends a customer's "I still can't get in" reply into a black
            // hole, and which Gmail treats as a mild negative signal in its
            // own right. An unreplyable transactional email is a support
            // failure waiting to happen.
            $reply_to   = function_exists('form_recipient_email') ? form_recipient_email() : '';
            $reply_name = $this->from_name();
        } else {
            $reply_name = isset($opts['reply_to_name']) ? trim((string) $opts['reply_to_name']) : '';
        }
        if ($reply_to !== '' && filter_var($reply_to, FILTER_VALIDATE_EMAIL)) {
            $this->CI->email->reply_to($reply_to, $reply_name);
        }

        $this->CI->email->to($to);
        $this->CI->email->subject($subject);
        $this->CI->email->message($html);

        if ($text === null) {
            $text = $this->text_from_html($html);
        }
        if ($text !== '') {
            $this->CI->email->set_alt_message($text);
        }

        $log_type = isset($opts['log_type']) ? (string) $opts['log_type'] : 'general';

        try {
            $ok = $this->CI->email->send(false);
        } catch (Exception $e) {
            // A refused TCP connection surfaces as a PHP warning under CI, but
            // a stream wrapper or a DNS failure can throw. Either way the
            // caller gets false, not a fatal on a signup page.
            $ok = false;
            $this->last_error = $e->getMessage();
        }

        if (!$ok && $this->last_error === '') {
            // print_debugger() returns HTML: <br /> between lines and the
            // headers entity-escaped. An admin reading this on a diagnostics
            // page needs `"Pearland411" <no-reply@…>`, not `&quot;…&lt;`.
            $debug = (string) $this->CI->email->print_debugger(array('headers'));
            $debug = str_ireplace(array('<br />', '<br/>', '<br>'), "\n", $debug);
            $debug = html_entity_decode(strip_tags($debug), ENT_QUOTES, 'UTF-8');
            $this->last_error = trim(preg_replace("/\n{3,}/", "\n\n", $debug));
        }

        $this->record($to, $subject, $log_type, $ok, $this->last_error);

        if (!$ok) {
            log_message('error', 'Mailer: send failed (' . $this->protocol . ') to '
                . (is_array($to) ? implode(', ', $to) : $to) . ': ' . $this->last_error);
        }

        return (bool) $ok;
    }

    /**
     * Debug output from the most recent failed send. Empty string when the
     * last send succeeded. Never shown to an end user — it carries the SMTP
     * conversation, including the username.
     */
    public function last_error()
    {
        return $this->last_error;
    }

    /** Transport in use: 'smtp', 'mail' or 'sendmail'. */
    public function protocol()
    {
        return $this->protocol;
    }

    public function from_address()
    {
        return (string) env('MAIL_FROM_ADDRESS', 'no-reply@pearland411.com');
    }

    public function from_name()
    {
        return (string) env('MAIL_FROM_NAME', 'Pearland411');
    }

    /* =====================================================================
     * Diagnostics
     * ===================================================================== */

    /**
     * True when the configured transport has everything it needs. A 'mail' /
     * 'sendmail' transport is always "configured" — whether the host's MTA
     * actually relays is something only a test send can answer.
     */
    public function is_configured()
    {
        if ($this->protocol !== 'smtp') {
            return true;
        }
        return !empty($this->config['smtp_host'])
            && !empty($this->config['smtp_user'])
            && !empty($this->config['smtp_pass']);
    }

    /**
     * The settings as they will be used, with the password masked, for display
     * on /admin/email-diagnostics.
     *
     * @return array
     */
    public function config_summary()
    {
        $out = array(
            'protocol'     => $this->protocol,
            'from_address' => $this->from_address(),
            'from_name'    => $this->from_name(),
        );

        if ($this->protocol === 'smtp') {
            $out['smtp_host']    = (string) $this->config['smtp_host'];
            $out['smtp_port']    = (int) $this->config['smtp_port'];
            $out['smtp_user']    = (string) $this->config['smtp_user'];
            $out['smtp_pass']    = $this->config['smtp_pass'] === '' ? '' : str_repeat('•', 10);
            $out['smtp_crypto']  = isset($this->config['smtp_crypto']) ? $this->config['smtp_crypto'] : 'none';
            $out['smtp_timeout'] = (int) $this->config['smtp_timeout'];
        } elseif ($this->protocol === 'sendmail') {
            $out['mailpath'] = (string) $this->config['mailpath'];
        }

        return $out;
    }

    /**
     * Configuration problems that do not stop a send but do stop delivery.
     *
     * The important one is the last: a relay will happily accept a message
     * whose From is `no-reply@pearland411.com` while the session authenticated
     * as `someone@gmail.com`, and the receiving side will then fail SPF and
     * DMARC and discard it without a bounce and without a Spam copy. That is
     * exactly the "not in Inbox or Spam" symptom, and no amount of looking at
     * the sending side reveals it.
     *
     * @return array[] each: ['level' => 'error'|'warn', 'title', 'detail']
     */
    public function config_status()
    {
        $issues = array();

        $from = $this->from_address();
        if ($from === '' || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $issues[] = array(
                'level'  => 'error',
                'title'  => 'MAIL_FROM_ADDRESS is not a valid address',
                'detail' => 'Every message is rejected at the first hop without a usable From header.',
            );
        }

        if ($this->protocol === 'smtp') {
            foreach (array('SMTP_HOST' => 'smtp_host', 'SMTP_USER' => 'smtp_user', 'SMTP_PASS' => 'smtp_pass') as $label => $key) {
                if (empty($this->config[$key])) {
                    $issues[] = array(
                        'level'  => 'error',
                        'title'  => $label . ' is empty',
                        'detail' => 'SMTP authentication cannot succeed, so no mail leaves the site.',
                    );
                }
            }

            $port   = (int) $this->config['smtp_port'];
            $crypto = isset($this->config['smtp_crypto']) ? $this->config['smtp_crypto'] : 'none';
            if ($port === 465 && $crypto !== 'ssl') {
                $issues[] = array(
                    'level'  => 'warn',
                    'title'  => 'Port 465 with SMTP_CRYPTO=' . $crypto,
                    'detail' => 'Port 465 is implicit TLS — set SMTP_CRYPTO=ssl, or use port 587 with tls.',
                );
            }
            if ($port === 587 && $crypto === 'ssl') {
                $issues[] = array(
                    'level'  => 'warn',
                    'title'  => 'Port 587 with SMTP_CRYPTO=ssl',
                    'detail' => 'Port 587 expects STARTTLS — set SMTP_CRYPTO=tls, or use port 465 with ssl.',
                );
            }

            $user = (string) $this->config['smtp_user'];
            if ($user !== '' && $from !== '' && strcasecmp($user, $from) !== 0) {
                $user_domain = $this->domain_of($user);
                $from_domain = $this->domain_of($from);
                if ($user_domain !== '' && $from_domain !== '' && strcasecmp($user_domain, $from_domain) !== 0) {
                    $issues[] = array(
                        'level'  => 'error',
                        'title'  => 'From domain does not match the SMTP account',
                        'detail' => 'Mail is sent as ' . $from . ' through an account at ' . $user_domain . '. '
                            . 'Unless ' . $from_domain . ' publishes SPF/DKIM authorising ' . $user_domain
                            . ', the receiving server fails DMARC and discards the message — it will not '
                            . 'appear in Inbox or Spam, and no bounce is returned. Either send as ' . $user
                            . ', or relay through a provider that has verified ' . $from_domain . '.',
                    );
                }
            }
        } else {
            $issues[] = array(
                'level'  => 'warn',
                'title'  => 'Sending through the local MTA (' . $this->protocol . '), not SMTP',
                'detail' => 'Mail from a shared host without SPF/DKIM for the From domain is commonly '
                    . 'discarded on arrival. Set SMTP_HOST / SMTP_USER / SMTP_PASS to relay through an '
                    . 'authenticated provider.',
            );
        }

        return $issues;
    }

    /**
     * Send a message to $address and report what happened. Used by
     * /admin/email-diagnostics and `php index.php cli mail_test`.
     *
     * @return array ['success' => bool, 'error' => string]
     */
    public function test_send($address)
    {
        $address = trim((string) $address);
        if ($address === '' || !filter_var($address, FILTER_VALIDATE_EMAIL)) {
            return array('success' => false, 'error' => 'Enter a valid email address.');
        }

        $stamp = date('Y-m-d H:i:s T');
        $body  = '<p>This is a test message from the Pearland411 email diagnostics page.</p>'
            . '<p>If you are reading it, the site can deliver to this address: signup '
            . 'verification and password-reset links will arrive the same way.</p>'
            . '<table role="presentation" cellpadding="4" cellspacing="0" border="0" style="font-size:13px; color:#5A6472;">'
            . '<tr><td>Sent</td><td>' . html_escape($stamp) . '</td></tr>'
            . '<tr><td>Transport</td><td>' . html_escape($this->protocol) . '</td></tr>'
            . '<tr><td>From</td><td>' . html_escape($this->from_address()) . '</td></tr>'
            . '</table>';

        $ok = $this->send(
            $address,
            'Pearland411 email test — ' . $stamp,
            $this->render('Email test', $body, array(
                'intro'     => 'Sent from /admin/email-diagnostics.',
                'preheader' => 'Delivery test from Pearland411.',
            )),
            null,
            array('log_type' => 'test')
        );

        return array('success' => $ok, 'error' => $ok ? '' : $this->last_error);
    }

    /* =====================================================================
     * Templating
     * ===================================================================== */

    /**
     * Wrap body markup in the standard Pearland411 email shell
     * (application/views/emails/layout.php) and return the full HTML.
     *
     * Everything the site sends should go through here. Before this the
     * templates were built by string concatenation at each call site, so a
     * subscription email, a renewal warning and an enquiry alert were three
     * different-looking messages from the same company — and a customer being
     * asked to check their card had no reason to believe the mail was ours.
     *
     * The signup and password-reset mails were still bypassing it as late as
     * 2026-09: a bare `<p>` fragment with no document, no shell and no text
     * part is also one of the cheapest things for a filter to score badly, so
     * the two messages a new user must receive were the two worst-formed.
     *
     *   $html = $this->mailer->render('Payment received', $body, [
     *       'intro'     => 'Thanks — your August invoice is settled.',
     *       'preheader' => 'Receipt for USD 39.00',
     *   ]);
     *
     * @param string $heading  the <h1>; also a sensible default subject
     * @param string $body     trusted HTML for the body (escape your own data)
     * @param array  $opts     intro, preheader, footnote
     */
    public function render($heading, $body, array $opts = array())
    {
        return $this->CI->load->view('emails/layout', array(
            'heading'   => $heading,
            'body'      => $body,
            'intro'     => isset($opts['intro']) ? $opts['intro'] : '',
            'preheader' => isset($opts['preheader']) ? $opts['preheader'] : '',
            'footnote'  => isset($opts['footnote']) ? $opts['footnote'] : '',
        ), true);
    }

    /* =====================================================================
     * Billing notifications
     * ===================================================================== */

    /**
     * Everyone who should hear about money moving: every active admin, plus
     * whatever `billing_notify_emails` lists at /admin/setting (one per line).
     *
     * Deduplicated case-insensitively, because an admin whose login address is
     * also in the extra list should not get the message twice.
     *
     * @return string[]
     */
    public function billing_recipients()
    {
        $out = array();

        $this->CI->load->model('adminmodel');
        if (method_exists($this->CI->adminmodel, 'getActiveAdminsForDropdown')) {
            foreach ($this->CI->adminmodel->getActiveAdminsForDropdown() as $admin) {
                if (!empty($admin->admin_email)) {
                    $out[] = trim($admin->admin_email);
                }
            }
        }

        foreach (preg_split('/[\r\n,;]+/', (string) site_setting('billing_notify_emails', '')) as $extra) {
            $extra = trim($extra);
            if ($extra !== '') {
                $out[] = $extra;
            }
        }

        $seen = array();
        $keep = array();
        foreach ($out as $address) {
            $key = strtolower($address);
            if (isset($seen[$key]) || !filter_var($address, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $seen[$key] = true;
            $keep[]     = $address;
        }

        return $keep;
    }

    /**
     * Tell the team something happened. Never throws and never blocks the
     * caller: a payment that went through must not be reported as failed
     * because the mail server was down.
     *
     * @return bool TRUE if it was handed to the mailer.
     */
    public function notify_billing($subject, $heading, $body, array $opts = array())
    {
        try {
            $to = $this->billing_recipients();
            if (empty($to)) {
                log_message('error', 'Mailer: no billing notification recipients configured.');
                return false;
            }

            return $this->send($to, $subject, $this->render($heading, $body, $opts), null, array(
                'log_type' => 'billing',
            ));
        } catch (Exception $e) {
            log_message('error', 'Mailer: billing notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /* =====================================================================
     * Internals
     * ===================================================================== */

    /**
     * Record the attempt in `email_log` (migration 0031). Best-effort: a
     * missing table must never turn a delivered email into a failed signup,
     * and it must never turn a *failed* email into a fatal error either —
     * which is the whole point of catching around it.
     */
    protected function record($to, $subject, $type, $ok, $error)
    {
        try {
            if (!$this->CI->db->table_exists('email_log')) {
                return;
            }
            $this->CI->db->insert('email_log', array(
                'to_email'   => substr(is_array($to) ? implode(', ', $to) : (string) $to, 0, 255),
                'subject'    => substr((string) $subject, 0, 255),
                'mail_type'  => substr((string) $type, 0, 40),
                'protocol'   => $this->protocol,
                'status'     => $ok ? 'sent' : 'failed',
                // Truncated: the SMTP transcript can run to kilobytes and the
                // first line is the part that names the problem.
                'error'      => $ok ? null : substr((string) $error, 0, 2000),
                'created_at' => date('Y-m-d H:i:s'),
            ));
        } catch (Exception $e) {
            log_message('error', 'Mailer: could not write email_log row: ' . $e->getMessage());
        }
    }

    /**
     * A readable plain-text part derived from the HTML one.
     *
     * The link URLs have to survive. The entire purpose of a verification or
     * reset message is its link, and a recipient reading the text part of a
     * multipart message — which is what a plain-text client, a watch, and some
     * accessibility tools show — must be able to reach it.
     *
     * Hence the placeholder dance: the obvious `$text <$url>` substitution is
     * eaten by the strip_tags() that follows, because `<https://…>` looks
     * exactly like a tag. Links are pulled out first and put back afterwards.
     */
    protected function text_from_html($html)
    {
        $text = (string) $html;

        $text = preg_replace('#<(style|script|head|title)\b.*?</\1>#is', '', $text);

        $links = array();
        $text  = preg_replace_callback(
            '#<a\b[^>]*href=(["\'])(.*?)\1[^>]*>(.*?)</a>#is',
            function ($m) use (&$links) {
                $url   = html_entity_decode($m[2], ENT_QUOTES, 'UTF-8');
                $label = trim(html_entity_decode(strip_tags($m[3]), ENT_QUOTES, 'UTF-8'));
                // "https://x ( https://x )" helps nobody.
                $piece = ($label === '' || $label === $url) ? $url : $label . ' ( ' . $url . ' )';
                $key   = "\x02" . count($links) . "\x03";
                $links[$key] = $piece;
                return $key;
            },
            $text
        );

        $text = preg_replace('#<(br|/p|/div|/tr|/h[1-6]|/li)\b[^>]*>#i', "\n", $text);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8');

        if (!empty($links)) {
            $text = strtr($text, $links);
        }

        $text = preg_replace("/[ \t]+/", ' ', $text);
        $text = preg_replace("/ *\n */", "\n", $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        return trim($text);
    }

    /** The domain half of an address, lower-cased; '' when there isn't one. */
    protected function domain_of($address)
    {
        $at = strrpos((string) $address, '@');
        return $at === false ? '' : strtolower(substr($address, $at + 1));
    }
}
