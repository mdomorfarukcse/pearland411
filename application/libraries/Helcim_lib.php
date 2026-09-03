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

    /**
     * Live credential check for the admin settings screen.
     *
     * is_configured() only proves a token string exists — it says nothing about
     * whether that token carries the right scopes. A token missing the checkout
     * permission still renders a working-looking "Pay securely" button and only
     * fails when a real customer clicks it, so this asks Helcim directly.
     *
     * Initializing a checkout session is the cheapest call that exercises the
     * same scope the storefront needs. Nothing is charged and no customer is
     * involved; the unused session token is simply never redeemed.
     *
     * Note this only proves the CHECKOUT scope. Charging a vaulted card for a
     * renewal hits /payment/purchase, which is gated separately by the
     * "Transaction Processing" restriction — verify that with
     * `php index.php cli renew_subscriptions`.
     *
     * @return array ['success'=>bool, 'error'=>?string]
     */
    public function health_check()
    {
        if (!$this->is_configured()) {
            return array(
                'success' => false,
                'error'   => 'No API token set. Add HELCIM_API_TOKEN to your .env file.',
            );
        }

        $res = $this->request('POST', '/helcim-pay/initialize', array(
            'paymentType' => 'purchase',
            'amount'      => 1.00,
            'currency'    => $this->currency(),
        ));

        if ($res['success']) {
            return array('success' => true, 'error' => null);
        }

        if ($res['status'] === 0) {
            $msg = 'Could not reach Helcim — network or TLS error. Check that this server allows outbound HTTPS.';
        } elseif ($res['status'] === 401 || $res['status'] === 403) {
            $msg = 'Helcim rejected the token (HTTP ' . $res['status'] . '). In the Helcim dashboard under '
                 . 'Settings > Integrations > API, open this token\'s API Access Configuration and confirm '
                 . '"I will use this token for a checkout integration" is ticked. If the token was recently '
                 . 'regenerated, the value in .env is stale. Helcim said: ' . $res['error'];
        } else {
            $msg = 'Helcim returned HTTP ' . $res['status'] . ': ' . $res['error'];
        }

        return array('success' => false, 'error' => $msg);
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
     * Initialize a HelcimPay.js session whose only purpose is to put a card on
     * file. No money moves: `verify` runs the card for a zero-dollar
     * authorization and hands back a `cardToken` we can charge later.
     *
     * This is what /admin/billing uses when staff take a card over the phone
     * for a customer who is not paying anything today, and what a customer
     * replacing an expiring card needs. Charging is a separate step — see
     * charge_vaulted_card().
     *
     * If the Helcim account does not have zero-dollar verification enabled the
     * initialize call fails, and the caller should fall back to taking the
     * payment with `initialize_checkout()` (a purchase returns a cardToken
     * too). The billing screen surfaces the error and offers exactly that.
     *
     * @param array $opts optional: description
     * @return array ['success'=>bool, 'checkoutToken'=>?, 'secretToken'=>?, 'error'=>?]
     */
    public function initialize_card_verification($opts = array())
    {
        $opts['paymentType'] = 'verify';
        return $this->initialize_checkout(0, null, $opts);
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
     * Charge a previously vaulted card token — the nightly renewal and the
     * admin "charge the card on file" button both come through here.
     *
     * The Payment API demands two things the HelcimPay endpoints do not, and
     * missing either is answered with a bare HTTP 400, so no card is charged
     * and nothing says why:
     *
     *   - an `idempotency-key` header, a unique 25-character alphanumeric
     *     string that Helcim remembers for five minutes. A repeat inside that
     *     window returns the original transaction rather than charging again.
     *   - `ipAddress` in the body, which Helcim scores for fraud. A cron has
     *     no client, so the server's own address stands in.
     *
     * Pass `idempotency_key` for a charge that must not happen twice — a
     * renewal keys on its subscription and due date, so two runs on the same
     * night collapse into one charge. Omit it and a fresh random key is
     * generated, which is what a deliberate one-off staff charge wants.
     *
     * @param string $card_token
     * @param float  $amount
     * @param array  $opts  optional: currency, ipAddress, description, idempotency_key
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
            'ipAddress'   => !empty($opts['ipAddress']) ? $opts['ipAddress'] : $this->default_ip(),
            'cardData'    => array('cardToken' => $card_token),
        );
        if (!empty($opts['description'])) { $payload['description'] = $opts['description']; }

        $key = $this->idempotency_key(isset($opts['idempotency_key']) ? $opts['idempotency_key'] : null);

        $res = $this->request('POST', '/payment/purchase', $payload, $key);
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
     *
     * `$idempotency_key` is sent only when given: the Payment API requires it,
     * the HelcimPay initialize endpoint does not accept it.
     */
    private function request($method, $path, $payload = null, $idempotency_key = null)
    {
        $url = rtrim($this->cfg['api_base'], '/') . $path;

        $ch = curl_init($url);
        $headers = array(
            'api-token: ' . $this->cfg['api_token'],
            'Content-Type: application/json',
            'Accept: application/json',
        );
        if ($idempotency_key !== null && $idempotency_key !== '') {
            $headers[] = 'idempotency-key: ' . $idempotency_key;
        }

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
            $msg = $this->error_message($body, $raw, $status);
            log_message('error', 'Helcim API ' . $status . ' on ' . $path . ': ' . $msg);
            return array('success' => false, 'body' => $body, 'status' => $status, 'error' => $msg);
        }

        return array('success' => true, 'body' => $body, 'status' => $status, 'error' => null);
    }

    /**
     * Turn a rejection into something the person reading a cron log can act on.
     *
     * Helcim documents errors as an `errors` object keyed by the offending
     * field, but not every rejection arrives in that shape. A purchase sent
     * without an idempotency key came back in none of the known shapes, and
     * this reported it as the literal string "HTTP 400" — which is what the
     * nightly job printed, night after night, while a customer went unbilled.
     * Anything unrecognised now carries a trimmed copy of the body.
     */
    private function error_message($body, $raw, $status)
    {
        foreach (array('errors', 'message', 'error') as $key) {
            if (isset($body[$key]) && $body[$key] !== '') {
                return is_string($body[$key]) ? $body[$key] : json_encode($body[$key]);
            }
        }

        $snippet = trim(preg_replace('/\s+/', ' ', (string) $raw));
        return 'HTTP ' . $status . ($snippet !== '' ? ': ' . substr($snippet, 0, 300) : '');
    }

    /**
     * A 25-character alphanumeric idempotency key, the shape the Payment API
     * requires.
     *
     * Seeded, it is deterministic: the same seed always yields the same key,
     * so a charge attempted twice inside Helcim's five-minute memory is
     * collapsed into one. Unseeded it is simply unique. This is a collision
     * guard, not a secret, so `uniqid()` entropy is sufficient and keeps the
     * call working on the PHP 5.x this project still declares support for.
     */
    private function idempotency_key($seed = null)
    {
        if ($seed !== null && $seed !== '') {
            return substr(sha1($seed), 0, 25);
        }
        return substr(sha1(uniqid('helcim', true) . mt_rand()), 0, 25);
    }

    /**
     * The address to hand Helcim for fraud scoring. `SERVER_ADDR` covers web
     * requests; the renewal cron has no client and no such variable, so the
     * host's own resolved address stands in for it.
     */
    private function default_ip()
    {
        if (!empty($_SERVER['SERVER_ADDR'])) {
            return $_SERVER['SERVER_ADDR'];
        }
        $resolved = gethostbyname(php_uname('n'));
        return filter_var($resolved, FILTER_VALIDATE_IP) ? $resolved : '127.0.0.1';
    }
}
