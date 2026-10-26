<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Google reCAPTCHA v2 ("I'm not a robot" checkbox) for the public forms.
 *
 *   $this->load->library('recaptcha');
 *   if ($this->recaptcha->required() && !$this->recaptcha->verify()) { ... }
 *
 * Keys come from .env (RECAPTCHA_SITE_KEY / RECAPTCHA_SECRET_KEY, a v2
 * checkbox pair from https://www.google.com/recaptcha/admin). With either key
 * missing, or `recaptcha_enabled` switched off at /admin/setting, the captcha
 * is off and every form behaves as it did before — so a fresh checkout, or a
 * production box before the keys are added, needs nothing.
 *
 * The widget itself is printed by spam_form_fields() (spam_helper.php) and
 * loaded on demand by assets/js/recaptcha.js. User::spamRefusal() is the one
 * place a handler asks whether to accept a post.
 */
class Recaptcha
{
    const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /** @var CI_Controller */
    protected $CI;

    /** Why the last verify() refused, for logging. */
    public $last_error = '';

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function site_key()
    {
        return trim((string) env('RECAPTCHA_SITE_KEY', ''));
    }

    protected function secret_key()
    {
        return trim((string) env('RECAPTCHA_SECRET_KEY', ''));
    }

    /** Both keys are in .env (the admin switch aside). */
    public function has_keys()
    {
        return $this->site_key() !== '' && $this->secret_key() !== '';
    }

    /** Both keys present and not switched off at /admin/setting. */
    public function enabled()
    {
        return $this->has_keys() && site_setting('recaptcha_enabled', '1') !== '0';
    }

    /**
     * Whether this visitor has to pass it. A signed-in member does not: the
     * account was captcha-checked at registration and its address verified
     * before it could sign in at all.
     */
    public function required()
    {
        return $this->enabled() && !$this->CI->session->userdata('logged_user');
    }

    /**
     * Ask Google whether the posted token is a solved challenge.
     *
     * Fails OPEN when Google cannot be asked (network, timeout, a reply that
     * is not JSON): an outage on their side must not stop real customers
     * reaching the business, and the honeypot and per-IP rate limits still
     * apply. A missing token or a "no" from Google is a refusal.
     *
     * @param  string|null $token defaults to the posted g-recaptcha-response
     * @return bool
     */
    public function verify($token = NULL)
    {
        $this->last_error = '';
        if ($token === NULL) {
            $token = (string) $this->CI->input->post('g-recaptcha-response');
        }
        $token = trim((string) $token);
        if ($token === '') {
            $this->last_error = 'missing-input-response';
            return FALSE;
        }

        $ch = curl_init(static::VERIFY_URL);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'secret'   => $this->secret_key(),
            'response' => $token,
            'remoteip' => $this->CI->input->ip_address(),
        )));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $body  = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        $reply = is_string($body) ? json_decode($body, TRUE) : NULL;
        if (!is_array($reply) || !array_key_exists('success', $reply)) {
            log_message('error', 'reCAPTCHA: could not verify (' . ($error ?: 'unexpected reply') . '); let the post through');
            return TRUE;
        }

        if ($reply['success'] === TRUE) {
            return TRUE;
        }

        $codes = isset($reply['error-codes']) ? (array) $reply['error-codes'] : array();
        $this->last_error = $codes ? implode(',', $codes) : 'rejected';
        // A wrong secret would refuse every visitor, not just bots — every
        // lead lost to a typo in .env. That is the operator's mistake, so it
        // goes in the log and the post goes through, like an outage.
        if (array_intersect(array('invalid-input-secret', 'missing-input-secret'), $codes)) {
            log_message('error', 'reCAPTCHA: RECAPTCHA_SECRET_KEY is wrong or missing; let the post through');
            return TRUE;
        }
        return FALSE;
    }
}
