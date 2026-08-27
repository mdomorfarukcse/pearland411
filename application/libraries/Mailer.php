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
 * On send failure the underlying CI debug output is forwarded to
 * the CI log (log_message('error', ...)) so admins can diagnose
 * without exposing SMTP details to end users.
 */
class Mailer
{
    /** @var CI_Controller */
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library('email');

        $config = array(
            'protocol'    => 'smtp',
            'smtp_host'   => env('SMTP_HOST', 'smtp.gmail.com'),
            'smtp_port'   => (int) env('SMTP_PORT', 587),
            'smtp_user'   => env('SMTP_USER', ''),
            'smtp_pass'   => env('SMTP_PASS', ''),
            'smtp_crypto' => env('SMTP_CRYPTO', 'tls'),
            'mailtype'    => 'html',
            'charset'     => 'utf-8',
            'wordwrap'    => true,
            'newline'     => "\r\n",
            'crlf'        => "\r\n",
        );

        $this->CI->email->initialize($config);
    }

    /**
     * Send an HTML email. Returns true on success.
     *
     * @param string|array $to       Single address or array of addresses.
     * @param string       $subject
     * @param string       $html
     * @param string|null  $text     Optional plain-text alternate.
     * @param array        $opts     'reply_to' / 'reply_to_name' — for
     *                               notifications about someone else's
     *                               enquiry, so hitting Reply answers them
     *                               rather than the site's no-reply box.
     *                               Ignored when the address is not valid.
     */
    public function send($to, $subject, $html, $text = null, array $opts = array())
    {
        $from_addr = env('MAIL_FROM_ADDRESS', 'no-reply@pearland411.com');
        $from_name = env('MAIL_FROM_NAME', 'Pearland411');

        $this->CI->email->clear(true);
        $this->CI->email->from($from_addr, $from_name);

        $reply_to = isset($opts['reply_to']) ? trim((string) $opts['reply_to']) : '';
        if ($reply_to !== '' && filter_var($reply_to, FILTER_VALIDATE_EMAIL)) {
            $reply_name = isset($opts['reply_to_name']) ? trim((string) $opts['reply_to_name']) : '';
            $this->CI->email->reply_to($reply_to, $reply_name);
        }

        $this->CI->email->to($to);
        $this->CI->email->subject($subject);
        $this->CI->email->message($html);
        if ($text !== null) {
            $this->CI->email->set_alt_message($text);
        }

        if ($this->CI->email->send(false)) {
            return true;
        }

        log_message('error', 'Mailer: send failed: ' . $this->CI->email->print_debugger(array('headers')));
        return false;
    }

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

            return $this->send($to, $subject, $this->render($heading, $body, $opts));
        } catch (Exception $e) {
            log_message('error', 'Mailer: billing notification failed: ' . $e->getMessage());
            return false;
        }
    }
}
