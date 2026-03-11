<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Dedicated Authorize.Net configuration file
// Toggle sandbox => TRUE for test, FALSE for production
$__authnet_cfg = array(
    'api_login_id' => '6Wau4j6YnkQ',
    'transaction_key' => '97rn54P6e8uEKUma',
    'signature_key' => '679811E35E054203C2AC7203B1E308E31455C02CD1C13D8DF4BF6F4591419CDAB8E88A377D669AC187E1ABF3270924D4FA943DB814CA90BEE3FD0E86C17484C2',
    'client_key' => '8zY728jrxz283wNTHDNbD9UU5ntS2R5nPFn7vVKEzLq253PWsEbA39s2SY7yd5Sn',
    'environment' => 'sandbox', // change to production when going live
    'endpoint_url' => 'https://apitest.authorize.net/xml/v1/request.api',
    'currency' => 'USD',

    // Webhook settings
    'webhook_url' => 'https://yourdomain.com/webhooks/authorize_net', // UPDATE THIS

    // Email settings (for subscription notifications)
    'email_enabled' => true,
    'email_from' => 'noreply@yourdomain.com', // UPDATE THIS
    'email_name' => 'Your Site Name' // UPDATE THIS
);

if (!isset($config) || !is_array($config)) {
    $config = array();
}
$config['authorize_net'] = $__authnet_cfg;
unset($__authnet_cfg);
