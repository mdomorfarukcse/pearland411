<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Authorize.Net Payment Gateway Library
 * Simplified version for debugging
 */
class Authorize_net_lib
{
    private $api_login_id;
    private $transaction_key;
    private $signature_key;
    private $environment;
    private $endpoint_url;
    private $currency;

    public function __construct()
    {
        // Hardcoded configuration for testing
        $this->api_login_id = '6Wau4j6YnkQ';
        $this->transaction_key = '97rn54P6e8uEKUma';
        $this->signature_key = '679811E35E054203C2AC7203B1E308E31455C02CD1C13D8DF4BF6F4591419CDAB8E88A377D669AC187E1ABF3270924D4FA943DB814CA90BEE3FD0E86C17484C2';
        $this->environment = 'sandbox';
        $this->endpoint_url = 'https://apitest.authorize.net/xml/v1/request.api';
        $this->currency = 'USD';

        error_log('Authorize.Net Library Initialized - API Login: ' . substr($this->api_login_id, 0, 3) . '***');
    }

    /**
     * Process Credit Card Payment
     */
    public function charge_credit_card($payment_data)
    {
        try {
            error_log('Authorize.Net: Starting payment processing');

            // Validate required fields
            if (empty($payment_data['amount']) || empty($payment_data['card_number'])) {
                error_log('Authorize.Net: Missing required payment data');
                return array(
                    'success' => false,
                    'error_message' => 'Missing required payment information',
                    'response_code' => 'VALIDATION_ERROR'
                );
            }

            // Clean and validate card number
            $clean_card_number = preg_replace('/[^0-9]/', '', $payment_data['card_number']);
            if (strlen($clean_card_number) < 13 || strlen($clean_card_number) > 19) {
                error_log('Authorize.Net: Invalid card number length: ' . strlen($clean_card_number));
                return array(
                    'success' => false,
                    'error_message' => 'Invalid card number format',
                    'response_code' => 'VALIDATION_ERROR'
                );
            }

            // Update payment data with cleaned card number
            $payment_data['card_number'] = $clean_card_number;

            // Validate and clean CVV
            if (isset($payment_data['cvv'])) {
                $clean_cvv = preg_replace('/[^0-9]/', '', $payment_data['cvv']);
                if (strlen($clean_cvv) < 3 || strlen($clean_cvv) > 4) {
                    error_log('Authorize.Net: Invalid CVV length: ' . strlen($clean_cvv));
                    return array(
                        'success' => false,
                        'error_message' => 'Invalid CVV format',
                        'response_code' => 'VALIDATION_ERROR'
                    );
                }
                $payment_data['cvv'] = $clean_cvv;
            }

            // Generate unique transaction ID
            $trans_id = 'TXN_' . time() . '_' . rand(100, 999);

            // Prepare XML request
            $xml_request = $this->build_payment_xml($payment_data, $trans_id);

            error_log('Authorize.Net: Sending request to gateway');
            // Send request to Authorize.Net
            $response = $this->send_request($xml_request);

            if ($response) {
                error_log('Authorize.Net: Received response, parsing...');
                return $this->parse_response($response, $trans_id);
            } else {
                error_log('Authorize.Net: No response received from gateway');
                return array(
                    'success' => false,
                    'error_message' => 'Network error: Could not connect to payment gateway',
                    'response_code' => 'E00001'
                );
            }
        } catch (Exception $e) {
            error_log('Authorize.Net Error: ' . $e->getMessage());
            return array(
                'success' => false,
                'error_message' => 'Payment processing error: ' . $e->getMessage(),
                'response_code' => 'E99999'
            );
        }
    }

    /**
     * Build Payment XML Request
     */
    private function build_payment_xml($payment_data, $trans_id)
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>';
        $xml .= '<createTransactionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">';
        $xml .= '<merchantAuthentication>';
        $xml .= '<name>' . $this->api_login_id . '</name>';
        $xml .= '<transactionKey>' . $this->transaction_key . '</transactionKey>';
        $xml .= '</merchantAuthentication>';

        $xml .= '<transactionRequest>';
        $xml .= '<transactionType>authCaptureTransaction</transactionType>';
        $xml .= '<amount>' . number_format($payment_data['amount'], 2, '.', '') . '</amount>';

        // Payment information - Clean card number of any formatting
        $clean_card_number = preg_replace('/[^0-9]/', '', $payment_data['card_number']);
        $clean_cvv = preg_replace('/[^0-9]/', '', $payment_data['cvv']);

        // Debug: Log cleaned data
        error_log('Authorize.Net: Original card: ' . $payment_data['card_number']);
        error_log('Authorize.Net: Cleaned card: ' . $clean_card_number . ' (length: ' . strlen($clean_card_number) . ')');
        error_log('Authorize.Net: CVV: ' . $clean_cvv . ' (length: ' . strlen($clean_cvv) . ')');

        $xml .= '<payment>';
        $xml .= '<creditCard>';
        $xml .= '<cardNumber>' . $clean_card_number . '</cardNumber>';
        $xml .= '<expirationDate>' . $payment_data['exp_date'] . '</expirationDate>';
        $xml .= '<cardCode>' . $clean_cvv . '</cardCode>';
        $xml .= '</creditCard>';
        $xml .= '</payment>';

        // Order information
        $xml .= '<order>';
        $xml .= '<invoiceNumber>' . $trans_id . '</invoiceNumber>';
        $xml .= '<description>' . htmlspecialchars($payment_data['description']) . '</description>';
        $xml .= '</order>';

        // Billing information
        if (isset($payment_data['billing_info'])) {
            $billing = $payment_data['billing_info'];
            $xml .= '<billTo>';
            $xml .= '<firstName>' . htmlspecialchars($billing['first_name']) . '</firstName>';
            $xml .= '<lastName>' . htmlspecialchars($billing['last_name']) . '</lastName>';
            $xml .= '<address>' . htmlspecialchars($billing['address']) . '</address>';
            $xml .= '<city>' . htmlspecialchars($billing['city']) . '</city>';
            $xml .= '<state>' . htmlspecialchars(isset($billing['state']) ? $billing['state'] : '') . '</state>';
            $xml .= '<zip>' . htmlspecialchars($billing['zip']) . '</zip>';
            $xml .= '<country>' . htmlspecialchars($billing['country']) . '</country>';
            if (isset($billing['phone'])) {
                $xml .= '<phoneNumber>' . htmlspecialchars($billing['phone']) . '</phoneNumber>';
            }
            if (isset($billing['email'])) {
                $xml .= '<email>' . htmlspecialchars($billing['email']) . '</email>';
            }
            $xml .= '</billTo>';
        }

        // Customer IP (for fraud protection)
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $xml .= '<customerIP>' . $_SERVER['REMOTE_ADDR'] . '</customerIP>';
        }

        $xml .= '</transactionRequest>';
        $xml .= '</createTransactionRequest>';

        return $xml;
    }

    /**
     * Send Request to Authorize.Net
     */
    private function send_request($xml_request)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->endpoint_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/xml',
            'Content-Length: ' . strlen($xml_request)
        ));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_request);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);

        $response = curl_exec($ch);
        $curl_error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($curl_error) {
            error_log('Authorize.Net cURL Error: ' . $curl_error);
            return false;
        }

        if ($http_code != 200) {
            error_log('Authorize.Net HTTP Error: ' . $http_code);
            return false;
        }

        return $response;
    }

    /**
     * Parse Response from Authorize.Net
     */
    private function parse_response($xml_response, $trans_id)
    {
        try {
            // Debug: Log raw response
            error_log('Authorize.Net Raw Response: ' . substr($xml_response, 0, 200));

            // Handle BOM and namespace issues
            $xml_response = ltrim($xml_response, "\xEF\xBB\xBF"); // Remove BOM

            // Use libxml to disable errors and warnings
            $prev_setting = libxml_use_internal_errors(true);
            libxml_clear_errors();

            $xml = simplexml_load_string($xml_response);

            // Log any XML errors
            $xml_errors = libxml_get_errors();
            if (!empty($xml_errors)) {
                foreach ($xml_errors as $error) {
                    error_log('XML Parse Error: ' . $error->message);
                }
            }

            // Restore previous libxml error handling
            libxml_use_internal_errors($prev_setting);

            if ($xml === false) {
                error_log('XML Parse Error. Raw response: ' . substr($xml_response, 0, 500));
                return array(
                    'success' => false,
                    'error_message' => 'Invalid response format from payment gateway',
                    'response_code' => 'E00003'
                );
            }

            // Safely extract response data
            $result_code = isset($xml->transactionResponse->responseCode) ? (string)$xml->transactionResponse->responseCode : '';
            $transaction_id = isset($xml->transactionResponse->transId) ? (string)$xml->transactionResponse->transId : '';
            $auth_code = isset($xml->transactionResponse->authCode) ? (string)$xml->transactionResponse->authCode : '';

            $message = '';
            if (isset($xml->transactionResponse->messages->message->description)) {
                $message = (string)$xml->transactionResponse->messages->message->description;
            } elseif (isset($xml->messages->message->text)) {
                $message = (string)$xml->messages->message->text;
            }

            // Response codes: 1 = Approved, 2 = Declined, 3 = Error, 4 = Held for Review
            if ($result_code == '1') {
                return array(
                    'success' => true,
                    'transaction_id' => $transaction_id,
                    'auth_code' => $auth_code,
                    'response_code' => $result_code,
                    'message' => $message,
                    'txn_ref' => $trans_id,
                    'raw_response' => $xml_response
                );
            } else {
                $error_message = $message;

                // Check for transaction response errors
                if (isset($xml->transactionResponse->errors->error->errorText)) {
                    $error_message = (string)$xml->transactionResponse->errors->error->errorText;
                }
                // Check for general errors
                elseif (isset($xml->messages->message->text)) {
                    $error_message = (string)$xml->messages->message->text;
                }
                // Fallback to raw response if no clear error message
                elseif (empty($error_message)) {
                    $error_message = 'Payment failed - ' . substr($xml_response, 0, 100);
                }

                return array(
                    'success' => false,
                    'error_message' => $error_message,
                    'response_code' => $result_code,
                    'transaction_id' => $transaction_id,
                    'txn_ref' => $trans_id,
                    'raw_response' => $xml_response
                );
            }
        } catch (Exception $e) {
            error_log('Authorize.Net Response Parse Error: ' . $e->getMessage());
            return array(
                'success' => false,
                'error_message' => 'Response parsing error: ' . $e->getMessage(),
                'response_code' => 'E00004'
            );
        }
    }

    /**
     * Validate webhook signature (for future use)
     */
    public function validate_webhook($payload, $signature, $timestamp)
    {
        $hash = hash_hmac('sha512', $payload, $this->signature_key);
        $computed_signature = strtoupper($hash);

        return hash_equals($computed_signature, strtoupper($signature));
    }

    /**
     * Get test credit card numbers (for sandbox testing)
     */
    public function get_test_card_numbers()
    {
        return array(
            'visa' => '4111111111111111',
            'mastercard' => '5555555555554444',
            'discover' => '6011111111111117',
            'amex' => '378282246310005'
        );
    }

    /**
     * Create Recurring Subscription
     */
    public function create_subscription($subscription_data)
    {
        try {
            error_log('Authorize.Net: Starting subscription creation');

            // Validate required fields
            if (empty($subscription_data['amount']) || empty($subscription_data['card_number']) || empty($subscription_data['interval'])) {
                error_log('Authorize.Net: Missing required subscription data');
                return array(
                    'success' => false,
                    'error_message' => 'Missing required subscription information',
                    'response_code' => 'VALIDATION_ERROR'
                );
            }

            // Clean card data
            $subscription_data['card_number'] = preg_replace('/[^0-9]/', '', $subscription_data['card_number']);
            if (isset($subscription_data['cvv'])) {
                $subscription_data['cvv'] = preg_replace('/[^0-9]/', '', $subscription_data['cvv']);
            }

            // Generate unique subscription name
            $subscription_name = 'SUB_' . time() . '_' . rand(100, 999);

            // Build XML request
            $xml_request = $this->build_subscription_xml($subscription_data, $subscription_name);

            error_log('Authorize.Net: Sending subscription request to gateway');
            $response = $this->send_request($xml_request);

            if ($response) {
                error_log('Authorize.Net: Received subscription response, parsing...');
                return $this->parse_subscription_response($response, $subscription_name);
            } else {
                error_log('Authorize.Net: No response received from gateway');
                return array(
                    'success' => false,
                    'error_message' => 'Network error: Could not connect to payment gateway',
                    'response_code' => 'E00001'
                );
            }
        } catch (Exception $e) {
            error_log('Authorize.Net Subscription Error: ' . $e->getMessage());
            return array(
                'success' => false,
                'error_message' => 'Subscription creation error: ' . $e->getMessage(),
                'response_code' => 'E99999'
            );
        }
    }

    /**
     * Build Subscription XML Request
     */
    private function build_subscription_xml($subscription_data, $subscription_name)
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>';
        $xml .= '<ARBCreateSubscriptionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">';
        $xml .= '<merchantAuthentication>';
        $xml .= '<name>' . $this->api_login_id . '</name>';
        $xml .= '<transactionKey>' . $this->transaction_key . '</transactionKey>';
        $xml .= '</merchantAuthentication>';

        $xml .= '<subscription>';
        $xml .= '<name>' . htmlspecialchars($subscription_name) . '</name>';

        // Payment Schedule
        $xml .= '<paymentSchedule>';
        $xml .= '<interval>';
        $xml .= '<length>' . $subscription_data['interval_length'] . '</length>';
        $xml .= '<unit>' . $subscription_data['interval_unit'] . '</unit>'; // months or days
        $xml .= '</interval>';
        $xml .= '<startDate>' . date('Y-m-d') . '</startDate>';
        $xml .= '<totalOccurrences>9999</totalOccurrences>'; // Infinite recurring
        $xml .= '</paymentSchedule>';

        $xml .= '<amount>' . number_format($subscription_data['amount'], 2, '.', '') . '</amount>';

        // Payment
        $xml .= '<payment>';
        $xml .= '<creditCard>';
        $xml .= '<cardNumber>' . $subscription_data['card_number'] . '</cardNumber>';
        $xml .= '<expirationDate>' . $subscription_data['exp_date'] . '</expirationDate>';
        if (isset($subscription_data['cvv'])) {
            $xml .= '<cardCode>' . $subscription_data['cvv'] . '</cardCode>';
        }
        $xml .= '</creditCard>';
        $xml .= '</payment>';

        // Order
        $xml .= '<order>';
        $xml .= '<invoiceNumber>' . $subscription_name . '</invoiceNumber>';
        $xml .= '<description>' . htmlspecialchars($subscription_data['description']) . '</description>';
        $xml .= '</order>';

        // Customer
        if (isset($subscription_data['billing_info'])) {
            $billing = $subscription_data['billing_info'];
            $xml .= '<customer>';
            $xml .= '<id>' . (isset($subscription_data['customer_id']) ? $subscription_data['customer_id'] : 'CUST_' . time()) . '</id>';
            // Only add email if it exists
            if (isset($billing['email'])) {
                $xml .= '<email>' . htmlspecialchars($billing['email']) . '</email>';
            }
            $xml .= '</customer>';

            $xml .= '<billTo>';
            $xml .= '<firstName>' . htmlspecialchars($billing['first_name']) . '</firstName>';
            $xml .= '<lastName>' . htmlspecialchars($billing['last_name']) . '</lastName>';
            $xml .= '<address>' . htmlspecialchars($billing['address']) . '</address>';
            $xml .= '<city>' . htmlspecialchars($billing['city']) . '</city>';
            if (isset($billing['state'])) {
                $xml .= '<state>' . htmlspecialchars($billing['state']) . '</state>';
            }
            $xml .= '<zip>' . htmlspecialchars($billing['zip']) . '</zip>';
            $xml .= '<country>' . htmlspecialchars($billing['country']) . '</country>';
            $xml .= '</billTo>';
        }

        $xml .= '</subscription>';
        $xml .= '</ARBCreateSubscriptionRequest>';

        return $xml;
    }

    /**
     * Parse Subscription Response
     */
    private function parse_subscription_response($xml_response, $subscription_name)
    {
        try {
            error_log('Authorize.Net Subscription Raw Response: ' . substr($xml_response, 0, 200));

            $xml_response = ltrim($xml_response, "\xEF\xBB\xBF");
            $prev_setting = libxml_use_internal_errors(true);
            libxml_clear_errors();

            $xml = simplexml_load_string($xml_response);
            libxml_use_internal_errors($prev_setting);

            if ($xml === false) {
                error_log('Subscription XML Parse Error. Raw response: ' . substr($xml_response, 0, 500));
                return array(
                    'success' => false,
                    'error_message' => 'Invalid response format from payment gateway',
                    'response_code' => 'E00003'
                );
            }

            $result_code = isset($xml->messages->resultCode) ? (string)$xml->messages->resultCode : '';
            $subscription_id = isset($xml->subscriptionId) ? (string)$xml->subscriptionId : '';

            $message = '';
            if (isset($xml->messages->message->text)) {
                $message = (string)$xml->messages->message->text;
            }

            if (strtolower($result_code) == 'ok') {
                return array(
                    'success' => true,
                    'subscription_id' => $subscription_id,
                    'subscription_name' => $subscription_name,
                    'message' => $message,
                    'response_code' => $result_code,
                    'raw_response' => $xml_response
                );
            } else {
                $error_message = $message;
                if (isset($xml->messages->message->text)) {
                    $error_message = (string)$xml->messages->message->text;
                }

                return array(
                    'success' => false,
                    'error_message' => $error_message,
                    'response_code' => $result_code,
                    'subscription_id' => $subscription_id,
                    'raw_response' => $xml_response
                );
            }
        } catch (Exception $e) {
            error_log('Authorize.Net Subscription Parse Error: ' . $e->getMessage());
            return array(
                'success' => false,
                'error_message' => 'Response parsing error: ' . $e->getMessage(),
                'response_code' => 'E00004'
            );
        }
    }

    /**
     * Cancel Subscription
     */
    public function cancel_subscription($subscription_id)
    {
        try {
            $xml = '<?xml version="1.0" encoding="utf-8"?>';
            $xml .= '<ARBCancelSubscriptionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">';
            $xml .= '<merchantAuthentication>';
            $xml .= '<name>' . $this->api_login_id . '</name>';
            $xml .= '<transactionKey>' . $this->transaction_key . '</transactionKey>';
            $xml .= '</merchantAuthentication>';
            $xml .= '<subscriptionId>' . $subscription_id . '</subscriptionId>';
            $xml .= '</ARBCancelSubscriptionRequest>';

            $response = $this->send_request($xml);
            return $this->parse_subscription_response($response, 'CANCEL');
        } catch (Exception $e) {
            error_log('Authorize.Net Cancel Subscription Error: ' . $e->getMessage());
            return array('success' => false, 'error_message' => $e->getMessage());
        }
    }

    /**
     * Test connection to Authorize.Net
     */
    public function test_connection()
    {
        error_log('Testing Authorize.Net connection...');

        // Create a simple test payment data
        $test_data = array(
            'amount' => 1.00,
            'card_number' => '4111111111111111',
            'exp_date' => '12/25',
            'cvv' => '123',
            'description' => 'Test Connection',
            'billing_info' => array(
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@example.com',
                'address' => '123 Test St',
                'city' => 'Test City',
                'state' => 'TX',
                'zip' => '12345',
                'country' => 'US'
            )
        );

        return $this->charge_credit_card($test_data);
    }
}
