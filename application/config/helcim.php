<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helcim payment configuration.
 *
 * Unlike the old authorize_net.php (which was never loaded), this file IS
 * loaded by Helcim_lib from its constructor via CI's config loader, so these
 * values reach the gateway at runtime. All secrets come from .env — never
 * hard-code an API token here.
 *
 * HelcimPay.js flow:
 *   1. Server POSTs to {api_base}/helcim-pay/initialize with the `api-token`
 *      header to get a checkoutToken + secretToken.
 *   2. The browser renders the modal with the checkoutToken.
 *   3. Server validates the returned transaction hash against secretToken.
 */
$config['helcim'] = array(
    'api_token'              => env('HELCIM_API_TOKEN', ''),
    'currency'               => env('HELCIM_CURRENCY', 'USD'),
    'webhook_verifier_token' => env('HELCIM_WEBHOOK_VERIFIER_TOKEN', ''),
    'api_base'               => 'https://api.helcim.com/v2',
    // HelcimPay.js front-end script (loaded in the checkout view).
    'helcimpay_js_url'       => 'https://secure.helcim.app/helcim-pay/services/start.js',
);
