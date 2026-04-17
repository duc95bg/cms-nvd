<?php

return [
    'bank_name' => env('PAYMENT_BANK_NAME', ''),
    'bank_account' => env('PAYMENT_BANK_ACCOUNT', ''),
    'bank_holder' => env('PAYMENT_BANK_HOLDER', ''),

    'vnpay' => [
        'tmn_code' => env('VNPAY_TMN_CODE', ''),
        'hash_secret' => env('VNPAY_HASH_SECRET', ''),
        'url' => env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
        'return_url' => env('VNPAY_RETURN_URL', ''),
    ],

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID', ''),
        'secret' => env('PAYPAL_SECRET', ''),
        'mode' => env('PAYPAL_MODE', 'sandbox'),
        'usd_rate' => (float) env('PAYPAL_USD_RATE', 25000),
    ],
];
