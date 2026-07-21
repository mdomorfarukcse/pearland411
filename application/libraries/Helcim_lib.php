<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helcim payment gateway wrapper (HelcimPay.js + Payment API v2).
 *
 * Replaces the retired Authorize_net_lib. Card data is tokenized client-side
 * by HelcimPay.js — the PAN never touches this server. This class:
 *   - initializes a HelcimPay.js checkout session (server-side),
 *   - validates the transaction hash the browser returns,
 *   - charges a vaulted card token for recurring renewals,
 *   - verifies inbound webhook signatures.
 *
 * Credentials come from application/config/helcim.php (env-backed).
 */
class Helcim_lib
{
    /** @var array */
    private $cfg;

    public function __construct()
    {
        $CI =& get_instance();
        // use_sections = FALSE => $config['helcim'] merges into global config,
        // fail_gracefully = TRUE => no fatal if the file is somehow absent.
        $CI->config->load('helcim', FALSE, TRUE);
        $this->cfg = config_item('helcim') ?: array();
    }

    /**
     * TRUE once a live API token is present. The checkout page uses this to
     * decide whether to offer real payment or a "coming soon" notice.
     */
    public function is_configured()
    {
        return !empty($this->cfg['api_token']);
    }

    public function currency()
    {
        return isset($this->cfg['currency']) ? $this->cfg['currency'] : 'USD';
    }

    public function helcimpay_js_url()
    {
        return isset($this->cfg['helcimpay_js_url'])
            ? $this->cfg['helcimpay_js_url']
            : 'https://secure.helcim.app/helcim-pay/services/start.js';
    }

    /**
     * Step 1 — initialize a HelcimPay.js checkout session.
     *
     * @param float  $amount
     * @param string $currency  ISO currency (defaults to configured)
     * @param array  $opts       optional: paymentType, description
     * @return array ['success'=>bool, 'checkoutToken'=>?, 'secretToken'=>?, 'error'=>?]
     */
    public function initialize_checkout($amount, $currency = null, $opts = array())
    {
        if (!$this->is_configured()) {
            return array('success' => false, 'error' => 'Helcim is not configured.');
        }

        $payload = array(
            'paymentType' => isset($opts['paymentType']) ? $opts['paymentType'] : 'purchase',
            'amount'      => round((float) $amount, 2),
            'currency'    => $currency ?: $this->currency(),
        );
        // A description/invoice reference helps reconcile in the Helcim dashboard.
        if (!empty($opts['description'])) {
            $payload['description'] = $opts['description'];
        }

        $res = $this->request('POST', '/helcim-pay/initialize', $payload);
        if (!$res['success']) {
            return array('success' => false, 'error' => $res['error']);
        }

        $body = $res['body'];
        if (empty($body['checkoutToken']) || empty($body['secretToken'])) {
            return array('success' => false, 'error' => 'Unexpected initialize response from Helcim.');
        }

        return array(
            'success'       => true,
            'checkoutToken' => $body['checkoutToken'],
            'secretToken'   => $body['secretToken'],
        );
    }

    /**
     * Step 3 — validate the transaction payload the browser posts back.
     *
     * HelcimPay.js returns the transaction data plus a `hash`. The hash is
     * sha256( json_encode(data) . secretToken ). We recompute and compare in
     * constant time. `$transaction_data` must be the raw `data` object exactly
     * as returned (do not re-order keys) — pass the decoded array/section that
     * excludes the hash itself.
     *
     * @param array|string $transaction_data  the `data` portion (array or raw JSON string)
     * @param string       $hash              the returned hash
     * @param string       $secret_token      the secretToken stored at init
     * @return bool
     */
    public function validate_transaction($transaction_data, $hash, $secret_token)
    {
        if (empty($hash) || empty($secret_token)) {
            return false;
        }
        $json = is_string($transaction_data) ? $transaction_data : json_encode($transaction_data);
        $expected = hash('sha256', $json . $secret_token);
        return hash_equals($expected, (string) $hash);
    }

    /**
     * Charge a previously vaulted card token (used by recurring renewals).
     *
     * @param string $card_token
     * @param float  $amount
     * @param array  $opts  optional: currency, ipAddress, description
     * @return array ['success'=>bool, 'transactionId'=>?, 'approvalCode'=>?, 'raw'=>?, 'error'=>?]
     */
    public function charge_vaulted_card($card_token, $amount, $opts = array())
    {
        if (!$this->is_configured()) {
            return array('success' => false, 'error' => 'Helcim is not configured.');
        }

        $payload = array(
            'paymentType' => 'purchase',
            'currency'    => isset($opts['currency']) ? $opts['currency'] : $this->currency(),
            'amount'      => round((float) $amount, 2),
            'cardData'    => array('cardToken' => $card_token),
        );
        if (!empty($opts['ipAddress']))   { $payload['ipAddress'] = $opts['ipAddress']; }
        if (!empty($opts['description'])) { $payload['description'] = $opts['description']; }

        $res = $this->request('POST', '/payment/purchase', $payload);
        if (!$res['success']) {
            return array('success' => false, 'error' => $res['error'], 'raw' => $res['body']);
        }

        $body = $res['body'];
        return array(
            'success'       => true,
            'transactionId' => isset($body['transactionId']) ? $body['transactionId'] : null,
            'approvalCode'  => isset($body['approvalCode']) ? $body['approvalCode'] : null,
            'raw'           => $body,
        );
    }

    /**
     * Verify an inbound Helcim webhook. Helcim signs the raw request body with
     * an HMAC-SHA256 using the account's webhook verifier token; the signature
     * arrives in the `webhook-signature` header.
     *
     * @param string $raw_body    the exact raw POST body
     * @param string $signature   value of the webhook-signature header
     * @return bool
     */
    public function verify_webhook($raw_body, $signature)
    {
        $secret = isset($this->cfg['webhook_verifier_token']) ? $this->cfg['webhook_verifier_token'] : '';
        if ($secret === '' || $signature === '' || $signature === null) {
            return false;
        }
        $expected = base64_encode(hash_hmac('sha256', $raw_body, $secret, true));
        return hash_equals($expected, (string) $signature);
    }

    /**
     * Low-level JSON request to the Helcim API. Returns
     * ['success'=>bool, 'body'=>array, 'status'=>int, 'error'=>?string].
     */
    private function request($method, $path, $payload = null)
    {
        $url = rtrim($this->cfg['api_base'], '/') . $path;

        $ch = curl_init($url);
        $headers = array(
            'api-token: ' . $this->cfg['api_token'],
            'Content-Type: application/json',
            'Accept: application/json',
        );

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $raw    = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno  = curl_errno($ch);
        $errmsg = curl_error($ch);
        curl_close($ch);

        if ($errno) {
            log_message('error', 'Helcim request error: ' . $errmsg);
            return array('success' => false, 'body' => array(), 'status' => 0, 'error' => 'Network error contacting Helcim.');
        }

        $body = json_decode($raw, true);
        if (!is_array($body)) {
            $body = array();
        }

        if ($status < 200 || $status >= 300) {
            $msg = isset($body['errors']) ? json_encode($body['errors'])
                 : (isset($body['message']) ? $body['message'] : ('HTTP ' . $status));
            log_message('error', 'Helcim API ' . $status . ': ' . $msg);
            return array('success' => false, 'body' => $body, 'status' => $status, 'error' => $msg);
        }

        return array('success' => true, 'body' => $body, 'status' => $status, 'error' => null);
    }
}
