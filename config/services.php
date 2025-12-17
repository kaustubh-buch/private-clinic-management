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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sns' => [
        'sms_on' => env('AWS_SNS_SMS_ON', false),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'stripe' => [
        'stripe_public_key'         => env('STRIPE_PUBLIC_KEY', ''),
        'stripe_secret_key'         => env('STRIPE_SECRET_KEY', ''),
        'stripe_endpoint_secret'    => env('STRIPE_ENDPOINT_SECRET', ''),
    ],

    'cellcast' => [
        'api_key' => env('CELLCAST_API_KEY'),
        'base_url' => 'https://api.cellcast.com/api/v1/',
        'sms_on' => env('CELLCAST_SMS_ON', false),
        'user_name' => env('CELLCAST_USERNAME'),
        'password' => env('CELLCAST_PASSWORD'),
    ],
    'shortio' => [
        'api_key' => env('SHORTIO_API_KEY'),
        'domain'  => env('SHORTIO_DOMAIN'),
        'base_url' => env('SHORTIO_API_BASE_URL', 'https://api.short.io'),
    ],
];
