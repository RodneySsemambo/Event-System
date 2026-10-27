<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'marzpay' => [
        'api_key'        => env('MARZPAY_API_KEY'),
        'api_secret'     => env('MARZPAY_API_SECRET'),
        'base_url'       => env('MARZPAY_BASE_URL', 'https://wallet.wearemarz.com/api/v1'),
        'timeout'        => env('MARZPAY_TIMEOUT', 30),
        'webhook_secret' => env('MARZPAY_WEBHOOK_SECRET'),
    ],

    'africastalking' => [
        'username' => env('AT_USERNAME'),
        'key'      => env('AT_KEY'),
        'from'     => env('AT_FROM'),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI'),
    ],

    'yoola' => [
        'api_key' => env('YOOLA_API_KEY'),
        'sender'  => env('YOOLA_SENDER', 'Evently'),
    ],

    'nylonpay' => [
        'api_key'        => env('NYLONPAY_API_KEY'),
        'api_secret'     => env('NYLONPAY_API_SECRET'),
        'base_url'       => env('NYLONPAY_BASE_URL', 'https://api.nylonpay.nilesquad.com/api/services'),
        'webhook_secret' => env('NYLONPAY_WEBHOOK_SECRET'),
    ],
];
