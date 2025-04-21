<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use Mpesa\Mpesa; // Assuming the Mpesa SDK is already included via Composer

class MpesaController extends Controller
{
    /**
     * Action to handle B2C Payment (Business to Customer)
     */
    public function actionB2c()
    {
        $mpesa = new Mpesa();

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

        $response = $mpesa->b2c($data);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $response;
    }

    /**
     * Action to check account balance
     */
    public function actionAccountBalance()
    {
        $mpesa = new Mpesa();

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

        $response = $mpesa->accountBalance($data);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $response;
    }

    /**
     * Action to check the status of a transaction
     */
    public function actionTransactionStatus()
    {
        $mpesa = new Mpesa();

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

        $response = $mpesa->transactionStatus($data);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $response;
    }

    /**
     * Action to handle B2B Payment (Business to Business)
     */
    public function actionB2b()
    {
        $mpesa = new Mpesa();

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

        $response = $mpesa->b2b($data);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $response;
    }

    /**
     * Action to simulate a C2B Payment (Customer to Business)
     */
    public function actionC2b()
    {
        $mpesa = new Mpesa();

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

        $response = $mpesa->c2b($data);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $response;
    }

    /**
     * Action to initiate an STK Push (Lipa Na M-Pesa)
     */
    public function actionStkPush()
    {
        $mpesa = new Mpesa();

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

        $response = $mpesa->STKPushSimulation($data);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $response;
    }

    /**
     * Action to query the status of an STK Push request
     */
    public function actionStkPushQuery()
    {
        $mpesa = new Mpesa();

        $data = [
            'checkoutRequestID' => 'ws_CO_123456789',
            'BusinessShortCode' => '174379',
            'LipaNaMpesaPasskey' => 'your_lnm_passkey',
            'environment' => 'sandbox',
            'consumer_key' => 'your_consumer_key',
            'consumer_secret' => 'your_consumer_secret',
        ];

        $response = $mpesa->STKPushQuery($data);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $response;
    }

    /**
     * Action to reverse a transaction
     */
    public function actionReversal()
    {
        $mpesa = new Mpesa();

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

        $response = $mpesa->reversal($data);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $response;
    }

    /**
     * Action to register validation and confirmation URLs for C2B payments
     */
    public function actionRegisterUrl()
    {
        $mpesa = new Mpesa();

        $data = [
            'ShortCode' => '600XXX',
            'ResponseType' => 'Completed',
            'ConfirmationURL' => 'https://yourdomain.com/confirmation',
            'ValidationURL' => 'https://yourdomain.com/validation',
            'environment' => 'sandbox',
            'consumer_key' => 'your_consumer_key',
            'consumer_secret' => 'your_consumer_secret',
        ];

        $response = $mpesa->registerUrl($data);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $response;
    }

    /**
     * Action to handle the callback data
     */
    public function actionHandleCallback()
    {
        $mpesa = new Mpesa();
        $data = $mpesa->getDataFromCallback();

        // Process the data as needed

        // Respond based on whether the transaction was successful or not
        $mpesa->finishTransaction(); // success
        // $mpesa->finishTransaction(false); // failure

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $data;
    }
}
