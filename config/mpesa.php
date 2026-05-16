<?php

return [
    'environment'      => env('MPESA_ENV', 'sandbox'),
    'consumer_key'     => env('MPESA_CONSUMER_KEY'),
    'consumer_secret'  => env('MPESA_CONSUMER_SECRET'),
    'passkey'          => env('MPESA_PASSKEY'),
    'short_code'       => env('MPESA_SHORT_CODE', '174379'),
    'callback_url'     => env('MPESA_CALLBACK_URL', 'https://2e34ead255ea.ngrok-free.app/mpesa/callback'),
    'timeout'          => env('MPESA_TIMEOUT', 60),
    'transaction_type' => env('MPESA_TRANSACTION_TYPE', 'CustomerPayBillOnline'),
    'verify_ssl'       => env('MPESA_VERIFY_SSL', false),

    // Subscription plan amounts (set to 1 for sandbox testing)
    'monthly_amount'   => (int) env('MPESA_MONTHLY_AMOUNT', 1),
    'annual_amount'    => (int) env('MPESA_ANNUAL_AMOUNT', 1),
];
