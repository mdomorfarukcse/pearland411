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
}
