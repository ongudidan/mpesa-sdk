<?php

// Include the Mpesa SDK
require_once 'vendor/autoload.php';

class MpesaController
{

    private $mpesa;

    public function __construct()
    {
        // Initialize Mpesa SDK
        $this->mpesa = new \Mpesa\Mpesa();
    }

    public function b2c()
    {
        $data = [
            'Initiator' => 'API user created on Safaricom Business dashboard',
            'initiatorPassword' => 'Password of the API user',
            'CommandID' => 'BusinessPayment', // or SalaryPayment, PromotionPayment
            'Amount' => '100', // Amount to send
            'PartyA' => '600XXX', // Your shortcode
            'PartyB' => '2547XXXXXXXX', // Customer phone number
            'Remarks' => 'Salary payment',
            'QueueTimeOutURL' => 'https://yourdomain.com/timeout',
            'ResultURL' => 'https://yourdomain.com/result',
            'Occasion' => 'JunePayroll',
            'environment' => 'sandbox',
            'consumer_key' => 'your_consumer_key',
            'consumer_secret' => 'your_consumer_secret',
        ];

        $response = $this->mpesa->b2c($data);
        echo json_encode($response);
    }

    public function accountBalance()
    {
        $data = [
            'IdentifierType' => '4', // 1: Shortcode, 2: Till, 4: Org
            'PartyA' => '600XXX', // Your shortcode or organization ID
            'Initiator' => 'API user created on dashboard',
            'initiatorPassword' => 'Password of the API user',
            'QueueTimeOutURL' => 'https://yourdomain.com/timeout',
            'ResultURL' => 'https://yourdomain.com/result',
            'environment' => 'sandbox',
            'consumer_key' => 'your_consumer_key',
            'consumer_secret' => 'your_consumer_secret',
        ];

        $response = $this->mpesa->accountBalance($data);
        echo json_encode($response);
    }

    public function transactionStatus()
    {
        $data = [
            'TransactionID' => 'OEI2AK4Q16', // M-Pesa transaction ID
            'PartyA' => '600XXX', // Your shortcode
            'IdentifierType' => '1', // 1: Shortcode, 2: Till, etc.
            'Initiator' => 'API user',
            'initiatorPassword' => 'Password of the API user',
            'ResultURL' => 'https://yourdomain.com/result',
            'QueueTimeOutURL' => 'https://yourdomain.com/timeout',
            'environment' => 'sandbox',
            'consumer_key' => 'your_consumer_key',
            'consumer_secret' => 'your_consumer_secret',
        ];

        $response = $this->mpesa->transactionStatus($data);
        echo json_encode($response);
    }

    public function b2b()
    {
        $data = [
            'Initiator' => 'API user',
            'initiatorPassword' => 'Password of the API user',
            'CommandID' => 'BusinessPayBill', // or MerchantToMerchantTransfer
            'SenderIdentifierType' => '4',
            'RecieverIdentifierType' => '4',
            'Amount' => '1000',
            'PartyA' => '600XXX', // Your shortcode
            'PartyB' => '600YYY', // Recipient shortcode
            'AccountReference' => 'Invoice#234',
            'Remarks' => 'B2B Payment',
            'QueueTimeOutURL' => 'https://yourdomain.com/timeout',
            'ResultURL' => 'https://yourdomain.com/result',
            'environment' => 'sandbox',
            'consumer_key' => 'your_consumer_key',
            'consumer_secret' => 'your_consumer_secret',
        ];

        $response = $this->mpesa->b2b($data);
        echo json_encode($response);
    }

    public function c2b()
    {
        $data = [
            'ShortCode' => '600XXX',
            'CommandID' => 'CustomerPayBillOnline', // or CustomerBuyGoodsOnline
            'Amount' => '500',
            'Msisdn' => '2547XXXXXXXX', // Customer phone
            'BillRefNumber' => 'INV2024',
            'environment' => 'sandbox',
            'consumer_key' => 'your_consumer_key',
            'consumer_secret' => 'your_consumer_secret',
        ];

        $response = $this->mpesa->c2b($data);
        echo json_encode($response);
    }

    public function stkPush()
    {
        $data = [
            'BusinessShortCode' => '174379',
            'LipaNaMpesaPasskey' => 'your_lnm_passkey',
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => '100',
            'PartyA' => '2547XXXXXXXX', // Customer phone
            'PartyB' => '174379', // Your shortcode
            'PhoneNumber' => '2547XXXXXXXX',
            'CallBackURL' => 'https://yourdomain.com/callback',
            'AccountReference' => 'Ref001',
            'TransactionDesc' => 'Payment for services',
            'Remarks' => 'Online Payment',
            'environment' => 'sandbox',
            'consumer_key' => 'your_consumer_key',
            'consumer_secret' => 'your_consumer_secret',
        ];

        $response = $this->mpesa->STKPushSimulation($data);
        echo json_encode($response);
    }

    public function stkPushQuery()
    {
        $data = [
            'checkoutRequestID' => 'ws_CO_123456789',
            'BusinessShortCode' => '174379',
            'LipaNaMpesaPasskey' => 'your_lnm_passkey',
            'environment' => 'sandbox',
            'consumer_key' => 'your_consumer_key',
            'consumer_secret' => 'your_consumer_secret',
        ];

        $response = $this->mpesa->STKPushQuery($data);
        echo json_encode($response);
    }

    public function reversal()
    {
        $data = [
            'CommandID' => 'TransactionReversal',
            'TransactionID' => 'OEI2AK4Q16',
            'Amount' => '100',
            'ReceiverParty' => '600XXX',
            'RecieverIdentifierType' => '4',
            'ResultURL' => 'https://yourdomain.com/result',
            'QueueTimeOutURL' => 'https://yourdomain.com/timeout',
            'Remarks' => 'Refund for duplicate payment',
            'Occasion' => 'ErroneousPayment',
            'Initiator' => 'API user',
            'initiatorPassword' => 'Password of the API user',
            'environment' => 'sandbox',
            'consumer_key' => 'your_consumer_key',
            'consumer_secret' => 'your_consumer_secret',
        ];

        $response = $this->mpesa->reversal($data);
        echo json_encode($response);
    }

    public function registerUrls()
    {
        $data = [
            'ShortCode' => '600XXX',
            'ResponseType' => 'Completed',
            'ConfirmationURL' => 'https://yourdomain.com/confirmation',
            'ValidationURL' => 'https://yourdomain.com/validation',
            'environment' => 'sandbox',
            'consumer_key' => 'your_consumer_key',
            'consumer_secret' => 'your_consumer_secret',
        ];

        $response = $this->mpesa->registerUrl($data);
        echo json_encode($response);
    }

    public function handleCallback()
    {
        $data = $this->mpesa->getDataFromCallback();
        // Handle the callback data as needed
        echo json_encode($data);
    }

    public function finishTransaction($status = true)
    {
        $this->mpesa->finishTransaction($status); // success or failure
        echo $status ? "Transaction completed successfully" : "Transaction failed";
    }
}

// Example usage:
$controller = new MpesaController();
$controller->b2c(); // Call any action here
