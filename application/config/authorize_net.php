<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Authorize.Net configuration — values come from the environment.
// See .env.example for the variable list.
//
// Sandbox endpoint:    https://apitest.authorize.net/xml/v1/request.api
// Production endpoint: https://api.authorize.net/xml/v1/request.api
//
// IMPORTANT: never commit real API credentials to this file.

$config['authorize_net'] = array(
    'api_login_id'    => env('AUTHNET_API_LOGIN_ID', ''),
    'transaction_key' => env('AUTHNET_TRANSACTION_KEY', ''),
    'signature_key'   => env('AUTHNET_SIGNATURE_KEY', ''),
    'client_key'      => env('AUTHNET_CLIENT_KEY', ''),
    'environment'     => env('AUTHNET_ENVIRONMENT', 'sandbox'),
    'endpoint_url'    => env('AUTHNET_ENDPOINT_URL', 'https://apitest.authorize.net/xml/v1/request.api'),
    'currency'        => env('AUTHNET_CURRENCY', 'USD'),
    'webhook_url'     => env('AUTHNET_WEBHOOK_URL', ''),
    'email_enabled'   => true,
    'email_from'      => env('MAIL_FROM_ADDRESS', 'no-reply@pearland411.com'),
    'email_name'      => env('MAIL_FROM_NAME', 'Pearland411'),
);
