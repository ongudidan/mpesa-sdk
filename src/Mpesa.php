<?php

/**
 * Created by PhpStorm.
 * User: moses
 * Date: 15/10/17
 * Time: 4:59 PM
 */

namespace Mpesa;

/**
 * Class Mpesa
 * @package Safaricom\Mpesa
 */
class Mpesa
{

    /**
     * Generates a security credential by encrypting the initiator password
     * using the appropriate certificate based on the environment.
     *
     * @param string $environment Either 'live' or 'sandbox'.
     * @param string $initiatorPassword The password of the initiator.
     * @return string Base64 encoded encrypted password.
     */
    public static function generateSecurityCredential($environment, $initiatorPassword)
    {
        if (!isset($initiatorPassword)) {
            die("Please declare the initiator password as defined in the documentation.");
        }

        switch ($environment) {
            case 'live':
                $certificatePath = __DIR__ . '/ProductionCertificate.cer';
                break;
            case 'sandbox':
                $certificatePath = __DIR__ . '/SandboxCertificate.cer';
                break;
            default:
                die("Invalid application status. Must be 'live' or 'sandbox'.");
        }

        if (!file_exists($certificatePath)) {
            die("Certificate file not found at: $certificatePath");
        }

        // Encrypt the password using the certificate
        $certificate = file_get_contents($certificatePath);
        openssl_public_encrypt($initiatorPassword, $encrypted, $certificate, OPENSSL_PKCS1_PADDING);

        // Return the base64-encoded encrypted password
        return base64_encode($encrypted);
    }



    /**
     * Generates a token for the live environment.
     *
     * @param string $consumer_key The consumer key.
     * @param string $consumer_secret The consumer secret.
     * @return string The access token.
     */
    public static function generateLiveToken($consumer_key, $consumer_secret)
    {
        // Validate inputs
        if (empty($consumer_key) || empty($consumer_secret)) {
            die("Please declare the consumer key and consumer secret as defined in the documentation.");
        }

        // Prepare the URL and credentials
        $url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        $credentials = base64_encode($consumer_key . ':' . $consumer_secret);

        // Initialize cURL session
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => ['Authorization: Basic ' . $credentials],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HEADER => false,
        ]);

        // Execute cURL and get the response
        $curl_response = curl_exec($curl);

        if (curl_errno($curl)) {
            die("Error in cURL request: " . curl_error($curl));
        }

        // Close the cURL session
        curl_close($curl);

        // Decode and return the access token
        $response_data = json_decode($curl_response);
        return $response_data->access_token ?? null;
    }



    /**
     * Generates a token for the sandbox environment.
     *
     * @param string $consumer_key The consumer key.
     * @param string $consumer_secret The consumer secret.
     * @return string The access token.
     */
    public static function generateSandBoxToken($consumer_key, $consumer_secret)
    {
        // Validate inputs
        if (empty($consumer_key) || empty($consumer_secret)) {
            die("Please declare the consumer key and consumer secret as defined in the documentation.");
        }

        // Prepare the URL and credentials
        $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        $credentials = base64_encode($consumer_key . ':' . $consumer_secret);

        // Initialize cURL session
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => ['Authorization: Basic ' . $credentials],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HEADER => false,
        ]);

        // Execute cURL and get the response
        $curl_response = curl_exec($curl);

        if (curl_errno($curl)) {
            die("Error in cURL request: " . curl_error($curl));
        }

        // Close the cURL session
        curl_close($curl);

        // Decode and return the access token
        $response_data = json_decode($curl_response);
        return $response_data->access_token ?? null;
    }


    /**
     * Initiates a reversal request for a transaction.
     *
     * @param array $data Data required to initiate the reversal.
     * @return mixed|string The response from the API or an error message.
     */
    public static function reversal($data)
    {
        // Extract necessary data from input array
        extract($data);

        // Validate required fields
        $requiredFields = [
            'CommandID',
            'Initiator',
            'initiatorPassword',
            'TransactionID',
            'Amount',
            'ReceiverParty',
            'RecieverIdentifierType',
            'ResultURL',
            'QueueTimeOutURL',
            'Remarks',
            'consumer_key',
            'consumer_secret',
            'environment'
        ];
        foreach ($requiredFields as $field) {
            if (empty($$field)) {
                die("Missing required field: $field");
            }
        }

        // Generate security credential
        $SecurityCredential = self::generateSecurityCredential($environment, $initiatorPassword);

        // Set API URL and token based on environment
        switch ($environment) {
            case 'live':
                $url = 'https://api.safaricom.co.ke/mpesa/reversal/v1/request';
                $token = self::generateLiveToken($consumer_key, $consumer_secret);
                break;
            case 'sandbox':
                $url = 'https://sandbox.safaricom.co.ke/mpesa/reversal/v1/request';
                $token = self::generateSandBoxToken($consumer_key, $consumer_secret);
                break;
            default:
                return json_encode(["Message" => "Invalid application status"]);
        }

        // Prepare cURL request
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'CommandID' => $CommandID,
                'Initiator' => $Initiator,
                'SecurityCredential' => $SecurityCredential,
                'TransactionID' => $TransactionID,
                'Amount' => $Amount,
                'ReceiverParty' => $ReceiverParty,
                'RecieverIdentifierType' => $RecieverIdentifierType,
                'ResultURL' => $ResultURL,
                'QueueTimeOutURL' => $QueueTimeOutURL,
                'Remarks' => $Remarks,
                'Occasion' => $Occasion
            ]),
            CURLOPT_HEADER => false,
        ]);

        // Execute cURL request and handle response
        $curl_response = curl_exec($curl);
        if (curl_errno($curl)) {
            die("cURL Error: " . curl_error($curl));
        }

        curl_close($curl);

        // Return the decoded response
        return json_decode($curl_response);
    }


    /**
     * Initiates a B2C payment request.
     *
     * @param array $data Data required to initiate the payment request.
     * @return string The response from the API.
     */
    public static function b2c($data)
    {
        // Extract necessary data from input array
        extract($data);

        // Validate required fields
        $requiredFields = [
            'InitiatorName',
            'initiatorPassword',
            'CommandID',
            'Amount',
            'PartyA',
            'PartyB',
            'Remarks',
            'QueueTimeOutURL',
            'ResultURL',
            'consumer_key',
            'consumer_secret',
            'environment'
        ];
        foreach ($requiredFields as $field) {
            if (empty($$field)) {
                die("Missing required field: $field");
            }
        }

        // Generate security credential
        $SecurityCredential = self::generateSecurityCredential($environment, $initiatorPassword);

        // Set API URL and token based on environment
        switch ($environment) {
            case 'live':
                $url = 'https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest';
                $token = self::generateLiveToken($consumer_key, $consumer_secret);
                break;
            case 'sandbox':
                $url = 'https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest';
                $token = self::generateSandBoxToken($consumer_key, $consumer_secret);
                break;
            default:
                return json_encode(["Message" => "Invalid application status"]);
        }

        // Prepare cURL request
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'InitiatorName' => $InitiatorName,
                'SecurityCredential' => $SecurityCredential,
                'CommandID' => $CommandID,
                'Amount' => $Amount,
                'PartyA' => $PartyA,
                'PartyB' => $PartyB,
                'Remarks' => $Remarks,
                'QueueTimeOutURL' => $QueueTimeOutURL,
                'ResultURL' => $ResultURL,
                'Occasion' => $Occasion
            ]),
            CURLOPT_HEADER => false,
        ]);

        // Execute cURL request and handle response
        $curl_response = curl_exec($curl);
        if (curl_errno($curl)) {
            die("cURL Error: " . curl_error($curl));
        }

        curl_close($curl);

        // Return the response
        return json_encode($curl_response);
    }

    /**
     * Initiates a C2B transaction.
     *
     * @param array $data Data required to initiate the C2B transaction.
     * @return string The response from the API.
     */
    public static function c2b($data)
    {
        // Extract necessary data from input array
        extract($data);

        // Validate required fields
        $requiredFields = ['ShortCode', 'CommandID', 'Amount', 'Msisdn', 'consumer_key', 'consumer_secret', 'environment'];
        foreach ($requiredFields as $field) {
            if (empty($$field)) {
                die("Missing required field: $field");
            }
        }

        // Set environment and get token
        switch ($environment) {
            case 'live':
                $url = 'https://api.safaricom.co.ke/mpesa/c2b/v1/simulate';
                $token = self::generateLiveToken($consumer_key, $consumer_secret);
                break;
            case 'sandbox':
                $url = 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/simulate';
                $token = self::generateSandBoxToken($consumer_key, $consumer_secret);
                break;
            default:
                return json_encode(["Message" => "Invalid application status"]);
        }

        // Prepare cURL request
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'ShortCode' => $ShortCode,
                'CommandID' => $CommandID,
                'Amount' => $Amount,
                'Msisdn' => $Msisdn,
                'BillRefNumber' => $BillRefNumber
            ]),
            CURLOPT_HEADER => false,
        ]);

        // Execute cURL request and handle response
        $curl_response = curl_exec($curl);
        if (curl_errno($curl)) {
            die("cURL Error: " . curl_error($curl));
        }

        curl_close($curl);

        // Return the response
        return $curl_response;
    }



    /**
     * Initiates a balance inquiry request.
     *
     * @param array $data Data required to initiate the account balance inquiry.
     * @return string The response from the API.
     */
    public static function accountBalance($data)
    {
        // Extract necessary data from input array
        extract($data);

        // Validate required fields
        $requiredFields = ['IdentifierType', 'QueueTimeOutURL', 'ResultURL', 'Initiator', 'initiatorPassword', 'PartyA', 'consumer_key', 'consumer_secret', 'environment'];
        foreach ($requiredFields as $field) {
            if (empty($$field)) {
                die("Missing required field: $field");
            }
        }

        // Generate security credential
        $SecurityCredential = self::generateSecurityCredential($environment, $initiatorPassword);

        // Set CommandID and Remarks
        $CommandID = 'AccountBalance';
        $Remarks = 'Account Balance';

        // Set environment and get token
        switch ($environment) {
            case 'live':
                $url = 'https://api.safaricom.co.ke/mpesa/accountbalance/v1/query';
                $token = self::generateLiveToken($consumer_key, $consumer_secret);
                break;
            case 'sandbox':
                $url = 'https://sandbox.safaricom.co.ke/mpesa/accountbalance/v1/query';
                $token = self::generateSandBoxToken($consumer_key, $consumer_secret);
                break;
            default:
                return json_encode(["Message" => "Invalid application status"]);
        }

        // Prepare cURL request
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'CommandID' => $CommandID,
                'Initiator' => $Initiator,
                'SecurityCredential' => $SecurityCredential,
                'PartyA' => $PartyA,
                'IdentifierType' => $IdentifierType,
                'Remarks' => $Remarks,
                'QueueTimeOutURL' => $QueueTimeOutURL,
                'ResultURL' => $ResultURL
            ]),
            CURLOPT_HEADER => false,
        ]);

        // Execute cURL request and handle response
        $curl_response = curl_exec($curl);
        if (curl_errno($curl)) {
            die("cURL Error: " . curl_error($curl));
        }

        curl_close($curl);

        // Return the response
        return $curl_response;
    }

    /**
     * Initiates a transaction status request.
     *
     * @param array $data Data required to initiate the transaction status request.
     * @return string The response from the API.
     */
    public function transactionStatus($data)
    {
        // Extract necessary data from input array
        extract($data);

        // Validate required fields
        $requiredFields = ['TransactionID', 'IdentifierType', 'ResultURL', 'QueueTimeOutURL', 'PartyA', 'Initiator', 'initiatorPassword', 'consumer_key', 'consumer_secret', 'environment'];
        foreach ($requiredFields as $field) {
            if (empty($$field)) {
                die("Missing required field: $field");
            }
        }

        // Generate security credential
        $SecurityCredential = self::generateSecurityCredential($environment, $initiatorPassword);

        // Set CommandID and Remarks
        $CommandID = 'TransactionStatusQuery';
        $Remarks = 'Transaction Status';
        $Occasion = 'Transaction Status';

        // Set environment and get token
        switch ($environment) {
            case 'live':
                $url = 'https://api.safaricom.co.ke/mpesa/transactionstatus/v1/query';
                $token = self::generateLiveToken($consumer_key, $consumer_secret);
                break;
            case 'sandbox':
                $url = 'https://sandbox.safaricom.co.ke/mpesa/transactionstatus/v1/query';
                $token = self::generateSandBoxToken($consumer_key, $consumer_secret);
                break;
            default:
                return json_encode(["Message" => "Invalid application status"]);
        }

        // Prepare cURL request
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'Initiator' => $Initiator,
                'SecurityCredential' => $SecurityCredential,
                'CommandID' => $CommandID,
                'TransactionID' => $TransactionID,
                'PartyA' => $PartyA,
                'IdentifierType' => $IdentifierType,
                'ResultURL' => $ResultURL,
                'QueueTimeOutURL' => $QueueTimeOutURL,
                'Remarks' => $Remarks,
                'Occasion' => $Occasion
            ]),
            CURLOPT_HEADER => false,
        ]);

        // Execute cURL request and handle response
        $curl_response = curl_exec($curl);
        if (curl_errno($curl)) {
            die("cURL Error: " . curl_error($curl));
        }

        curl_close($curl);

        // Return the response
        return $curl_response;
    }



    /**
     * Initiates a B2B payment request.
     *
     * @param array $data Data required to initiate the B2B payment request.
     * @return string The response from the API.
     */
    public function b2b($data)
    {
        // Extract necessary data from the input array
        extract($data);

        // Validate required fields
        $requiredFields = [
            'Initiator',
            'initiatorPassword',
            'Amount',
            'PartyA',
            'PartyB',
            'Remarks',
            'QueueTimeOutURL',
            'ResultURL',
            'AccountReference',
            'commandID',
            'SenderIdentifierType',
            'RecieverIdentifierType',
            'consumer_key',
            'consumer_secret',
            'environment'
        ];
        foreach ($requiredFields as $field) {
            if (empty($$field)) {
                die("Missing required field: $field");
            }
        }

        // Generate security credential
        $SecurityCredential = self::generateSecurityCredential($environment, $initiatorPassword);

        // Set environment and get token
        switch ($environment) {
            case 'live':
                $url = 'https://api.safaricom.co.ke/mpesa/b2b/v1/paymentrequest';
                $token = self::generateLiveToken($consumer_key, $consumer_secret);
                break;
            case 'sandbox':
                $url = 'https://sandbox.safaricom.co.ke/mpesa/b2b/v1/paymentrequest';
                $token = self::generateSandBoxToken($consumer_key, $consumer_secret);
                break;
            default:
                return json_encode(["Message" => "Invalid application status"]);
        }

        // Prepare cURL request
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'Initiator' => $Initiator,
                'SecurityCredential' => $SecurityCredential,
                'CommandID' => $commandID,
                'SenderIdentifierType' => $SenderIdentifierType,
                'RecieverIdentifierType' => $RecieverIdentifierType,
                'Amount' => $Amount,
                'PartyA' => $PartyA,
                'PartyB' => $PartyB,
                'AccountReference' => $AccountReference,
                'Remarks' => $Remarks,
                'QueueTimeOutURL' => $QueueTimeOutURL,
                'ResultURL' => $ResultURL
            ])
        ]);

        // Execute cURL request and handle response
        $curl_response = curl_exec($curl);
        if (curl_errno($curl)) {
            die("cURL Error: " . curl_error($curl));
        }

        curl_close($curl);

        // Return the response
        return $curl_response;
    }


    /**
     * Initiates an STKPush Simulation request.
     *
     * @param array $data Data required to initiate the STKPush request.
     * @return string The response from the API.
     */
    public function STKPushSimulation($data)
    {
        // Extract necessary data from the input array
        extract($data);

        // Validate required fields
        $requiredFields = [
            'TransactionType',
            'Amount',
            'PartyA',
            'PartyB',
            'PhoneNumber',
            'CallBackURL',
            'AccountReference',
            'TransactionDesc',
            'Remarks',
            'environment',
            'BusinessShortCode',
            'LipaNaMpesaPasskey',
            'consumer_key',
            'consumer_secret'
        ];
        foreach ($requiredFields as $field) {
            if (empty($$field)) {
                die("Missing required field: $field");
            }
        }

        // Set environment and get token
        switch ($environment) {
            case 'live':
                $url = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
                $token = self::generateLiveToken($consumer_key, $consumer_secret);
                break;
            case 'sandbox':
                $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
                $token = self::generateSandBoxToken($consumer_key, $consumer_secret);
                break;
            default:
                return json_encode(["Message" => "Invalid application status"]);
        }

        // Generate password for the request using base64 encoding
        $timestamp = '20' . date("ymdhis");
        $password = base64_encode($BusinessShortCode . $LipaNaMpesaPasskey . $timestamp);

        // Prepare cURL request
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'BusinessShortCode' => $BusinessShortCode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => $TransactionType,
                'Amount' => $Amount,
                'PartyA' => $PartyA,
                'PartyB' => $PartyB,
                'TransactionDesc' => $TransactionDesc,
                'Remarks' => $Remarks,
                'PhoneNumber' => $PhoneNumber,
                'CallBackURL' => $CallBackURL,
                'AccountReference' => $AccountReference
            ])
        ]);

        // Execute cURL request and handle response
        $curl_response = curl_exec($curl);
        if (curl_errno($curl)) {
            die("cURL Error: " . curl_error($curl));
        }

        curl_close($curl);

        // Return the response
        return $curl_response;
    }



    /**
     * Initiates an STKPush Status Query request.
     *
     * @param array $data Data required to initiate the STKPush Status Query.
     * @return string The response from the API.
     */
    public static function STKPushQuery($data)
    {
        // Extract necessary data from the input array
        extract($data);

        // Validate required fields
        $requiredFields = ['checkoutRequestID', 'environment', 'consumer_key', 'consumer_secret', 'BusinessShortCode', 'LipaNaMpesaPasskey'];
        foreach ($requiredFields as $field) {
            if (empty($$field)) {
                die("Missing required field: $field");
            }
        }

        // Set environment and get token
        switch ($environment) {
            case 'live':
                $url = 'https://api.safaricom.co.ke/mpesa/stkpushquery/v2/query';
                $token = self::generateLiveToken($consumer_key, $consumer_secret);
                break;
            case 'sandbox':
                $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query';
                $token = self::generateSandBoxToken($consumer_key, $consumer_secret);
                break;
            default:
                return json_encode(["Message" => "Invalid application status"]);
        }

        // Generate password for the request using base64 encoding
        $timestamp = '20' . date("ymdhis");
        $password = base64_encode($BusinessShortCode . $LipaNaMpesaPasskey . $timestamp);

        // Prepare cURL request
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'BusinessShortCode' => $BusinessShortCode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'CheckoutRequestID' => $checkoutRequestID
            ])
        ]);

        // Execute cURL request and handle response
        $curl_response = curl_exec($curl);
        if (curl_errno($curl)) {
            die("cURL Error: " . curl_error($curl));
        }

        curl_close($curl);

        // Return the response
        return $curl_response;
    }


    /**
     * Confirms all transactions in callback routes.
     *
     * @param bool $status The status of the transaction confirmation (default is true).
     * @return void
     */
    public function finishTransaction($status = true)
    {
        // Define the result array based on the status
        $resultArray = $status ?
            ["ResultDesc" => "Confirmation Service request accepted successfully", "ResultCode" => "0"] :
            ["ResultDesc" => "Confirmation Service not accepted", "ResultCode" => "1"];

        // Set the response header and output the result as JSON
        header('Content-Type: application/json');
        echo json_encode($resultArray);
    }



    /**
     *Use this function to get callback data posted in callback routes
     */
    public function getDataFromCallback()
    {
        $callbackJSONData = file_get_contents('php://input');
        return $callbackJSONData;
    }

    /**
     * Registers a URL for C2B transactions.
     *
     * @param array $data The data containing the URLs, environment, and other parameters.
     * @return mixed|string The response from the API.
     */
    public function registerUrl($data)
    {
        // Extract data from the input array
        $ResponseType = $data['ResponseType'];
        $ConfirmationURL = $data['ConfirmationURL'];
        $ValidationURL = $data['ValidationURL'];
        $environment = $data['environment'];
        $consumer_key = $data['consumer_key'];
        $consumer_secret = $data['consumer_secret'];
        $ShortCode = $data['ShortCode'];

        // Determine the environment and set the appropriate URL and token
        if ($environment === "live") {
            $url = 'https://api.safaricom.co.ke/mpesa/c2b/v2/registerurl';
            $token = self::generateLiveToken($consumer_key, $consumer_secret);
        } elseif ($environment === "sandbox") {
            $url = 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl';
            $token = self::generateSandBoxToken($consumer_key, $consumer_secret);
        } else {
            return json_encode(["Message" => "invalid application status"]);
        }

        // Ensure the ShortCode is set
        if (!isset($ShortCode)) {
            return json_encode(["Message" => "Please declare the business shortcode as defined in the documentation"]);
        }

        // Initialize cURL and set options
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json', 'Authorization:Bearer ' . $token]);

        // Prepare the POST data
        $curl_post_data = [
            'ShortCode' => $ShortCode,
            'ResponseType' => $ResponseType,
            'ConfirmationURL' => $ConfirmationURL,
            'ValidationURL' => $ValidationURL,
        ];

        // Convert the data to JSON
        $data_string = json_encode($curl_post_data);

        // Set cURL options for the request
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HEADER, false);

        // Execute the cURL request and return the response
        return curl_exec($curl);
    }
}
