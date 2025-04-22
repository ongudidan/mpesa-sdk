# M-Pesa SDK for PHP

**Overview**

`mpesa-sdk` is a lightweight PHP SDK that simplifies integration with Safaricom's M-Pesa Daraja API. It supports B2C, B2B, C2B, STK Push, reversals, transaction status, account balance, and more.

This SDK is **framework-agnostic**, meaning you can use it with **Laravel, Symfony, Yii, CodeIgniter, or even plain/vanilla PHP**.

---

## 📦 Installation

Install via Composer:

```bash
composer require ongudidan/mpesa-sdk
```

---

## ⚙️ Configuration

You'll need:

- Consumer Key & Consumer Secret — from [Safaricom Developer Portal](https://developer.safaricom.co.ke/)
- An M-Pesa API User (Initiator) created from your Safaricom Business account
- Password for that Initiator

You can pass credentials directly in the data array or load them using environment variables/configuration.

---

## 🚀 Usage Examples

---

### ✅ B2C (Business to Customer)

Send funds from your shortcode to a customer's phone.

```php
$mpesa = new \Mpesa\Mpesa();

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
    'environment' => 'sandbox or live',
    'consumer_key' => 'your_consumer_key',
    'consumer_secret' => 'your_consumer_secret',
];

$response = $mpesa->b2c($data);
```

---

### 💼 Account Balance

Check your Paybill/Till balance.

```php
$mpesa = new \Mpesa\Mpesa();

$data = [
    'IdentifierType' => '4', // 1: Shortcode, 2: Till, 4: Org
    'PartyA' => '600XXX', // Your shortcode or organization ID
    'Initiator' => 'API user created on dashboard',
    'initiatorPassword' => 'Password of the API user',
    'QueueTimeOutURL' => 'https://yourdomain.com/timeout',
    'ResultURL' => 'https://yourdomain.com/result',
    'environment' => 'sandbox or live',
    'consumer_key' => 'your_consumer_key',
    'consumer_secret' => 'your_consumer_secret',
];

$response = $mpesa->accountBalance($data);
```

---

### 🔍 Transaction Status

Check the status of a transaction.

```php
$mpesa = new \Mpesa\Mpesa();

$data = [
    'TransactionID' => 'OEI2AK4Q16', // M-Pesa transaction ID
    'PartyA' => '600XXX', // Your shortcode
    'IdentifierType' => '1', // 1: Shortcode, 2: Till, etc.
    'Initiator' => 'API user',
    'initiatorPassword' => 'Password of the API user',
    'ResultURL' => 'https://yourdomain.com/result',
    'QueueTimeOutURL' => 'https://yourdomain.com/timeout',
    'environment' => 'sandbox or live',
    'consumer_key' => 'your_consumer_key',
    'consumer_secret' => 'your_consumer_secret',
];

$response = $mpesa->transactionStatus($data);
```

---

### 🏢 B2B (Business to Business)

Send money from your organization to another.

```php
$mpesa = new \Mpesa\Mpesa();

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
    'environment' => 'sandbox or live',
    'consumer_key' => 'your_consumer_key',
    'consumer_secret' => 'your_consumer_secret',
];

$response = $mpesa->b2b($data);
```

---

### 👥 C2B (Customer to Business Simulation)

Simulate a customer payment to your shortcode.

```php
$mpesa = new \Mpesa\Mpesa();

$data = [
    'ShortCode' => '600XXX',
    'CommandID' => 'CustomerPayBillOnline', // or CustomerBuyGoodsOnline
    'Amount' => '500',
    'Msisdn' => '2547XXXXXXXX', // Customer phone
    'BillRefNumber' => 'INV2024',
    'environment' => 'sandbox or live',
    'consumer_key' => 'your_consumer_key',
    'consumer_secret' => 'your_consumer_secret',
];

$response = $mpesa->c2b($data);
```

---

### 📲 STK Push (Lipa Na M-Pesa)

Trigger a payment prompt on a customer's phone.

```php
$mpesa = new \Mpesa\Mpesa();

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
    'environment' => 'sandbox or live',
    'consumer_key' => 'your_consumer_key',
    'consumer_secret' => 'your_consumer_secret',
];

$response = $mpesa->STKPushSimulation($data);
```

---

### 📡 STK Push Query

Query the status of an STK Push request.

```php
$mpesa = new \Mpesa\Mpesa();

$data = [
    'checkoutRequestID' => 'ws_CO_123456789',
    'BusinessShortCode' => '174379',
    'LipaNaMpesaPasskey' => 'your_lnm_passkey',
    'environment' => 'sandbox or live',
    'consumer_key' => 'your_consumer_key',
    'consumer_secret' => 'your_consumer_secret',
];

$response = $mpesa->STKPushQuery($data);
```

---

### 🔄 Reversal

Reverse a transaction.

```php
$mpesa = new \Mpesa\Mpesa();

$data = [
    'CommandID' => 'TransactionReversal',
    'TransactionID' => 'OEI2AK4Q16',
    'Amount' => '100',
    'ReceiverParty' => '600XXX', //your shortcode 
    'RecieverIdentifierType' => '11', //11: when using shortcode
    'ResultURL' => 'https://yourdomain.com/result',
    'QueueTimeOutURL' => 'https://yourdomain.com/timeout',
    'Remarks' => 'Refund for duplicate payment',
    'Occasion' => 'ErroneousPayment',
    'Initiator' => 'API user',
    'initiatorPassword' => 'Password of the API user',
    'environment' => 'sandbox or live',
    'consumer_key' => 'your_consumer_key',
    'consumer_secret' => 'your_consumer_secret',
];

$response = $mpesa->reversal($data);
```

---

### 🔐 Register Validation & Confirmation URLs

Register endpoints to handle C2B payments.

```php
$mpesa = new \Mpesa\Mpesa();

$data = [
    'ShortCode' => '600XXX',
    'ResponseType' => 'Completed',
    'ConfirmationURL' => 'https://yourdomain.com/confirmation',
    'ValidationURL' => 'https://yourdomain.com/validation',
    'environment' => 'sandbox or live',
    'consumer_key' => 'your_consumer_key',
    'consumer_secret' => 'your_consumer_secret',
];

$response = $mpesa->registerUrl($data);
```

---

### 📥 Handle Callback Data

#### Get Data from Callback:

```php
$mpesa = new \Mpesa\Mpesa();
$data = $mpesa->getDataFromCallback();
```

#### Send Callback Response:

```php
$mpesa = new \Mpesa\Mpesa();
$mpesa->finishTransaction(); // success
$mpesa->finishTransaction(false); // failure
```

---

### 📥 Handling M-PESA Daraja Callback Responses

To log the response from any M-PESA Daraja callback (e.g., `C2B Confirmation`, `C2B Validation`, `STK Push`, etc.), you can use the following snippet in your `callback.php` (or any relevant endpoint). This helps with debugging and record-keeping.

```php
<?php
// callback.php

// Capture raw POST data from Daraja
$callbackData = file_get_contents('php://input');

// Optional: Decode JSON for easier inspection
// $decoded = json_decode($callbackData, true);

// Define log file location (you can customize path)
$logFile = __DIR__ . '/M_PESAConfirmationResponse.json';

// Append the raw callback data with a newline for separation
file_put_contents($logFile, $callbackData . PHP_EOL, FILE_APPEND);

// You can add any additional logic to handle the data here
```

#### ✅ Best Practices:
- Always log the full callback for reference during development and support.
- Use different log files per endpoint if needed, like `STKCallback.json`, `C2BValidation.json`, etc.
- Avoid exposing these logs publicly—store them securely or behind server-level access control.

---

## 📘 More Info

- Safaricom API docs: https://developer.safaricom.co.ke/
- Ensure your callback URLs are publicly accessible and accept POST data.

---

## 🛠 Contributing

Pull requests and suggestions welcome! Let’s improve mobile money in PHP together.

---
