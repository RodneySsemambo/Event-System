<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MarzPay API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the MarzPay API client.
    | You can set these values in your .env file or directly in this config.
    |
    */

    'api_key' => function_exists('env') ? env('MARZPAY_API_KEY') : ($_ENV['MARZPAY_API_KEY'] ?? null),
    'api_secret' => function_exists('env') ? env('MARZPAY_API_SECRET') : ($_ENV['MARZPAY_API_SECRET'] ?? null),
    'base_url' => function_exists('env') ? env('MARZPAY_BASE_URL', 'https://wallet.wearemarz.com/api/v1') : ($_ENV['MARZPAY_BASE_URL'] ?? 'https://wallet.wearemarz.com/api/v1'),
    'timeout' => function_exists('env') ? env('MARZPAY_TIMEOUT', 30) : ($_ENV['MARZPAY_TIMEOUT'] ?? 30),
];
