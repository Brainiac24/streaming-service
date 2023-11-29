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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'tinkoff' => [
        'terminal_key' => env("TINKOFF_TERMINAL_KEY", ''),
        'password' => env("TINKOFF_PASSWORD", ''),
        'api_url' => env("TINKOFF_API_URL", ''),
        'success_url' => env("TINKOFF_SUCCESS_URL", ''),
        'notification_url' => env("TINKOFF_NOTIFICATION_URL", '')
    ],

    'cloudpayments' => [
        'host_api' => env("CLOUDPAYMENTS_API_HOST", 'https://api.cloudpayments.ru/'),
        'notification_pay_callback_url' => env("CLOUDPAYMENTS_NOTIFICATION_PAY_CALLBACK", 'https://example.com/api/v1/payments/cloudpayments/notification/pay'),
        'notification_fail_callback_url' => env("CLOUDPAYMENTS_FAIL_PAY_CALLBACK", 'https://example.com/api/v1/payments/cloudpayments/notification/fail'),
    ],

    'nimble' => [
        'client_id' => env('NIMBLE_CLIENT_ID'),
        'api_key' => env('NIMBLE_API_KEY'),
        'data_slice' => env('NIMBLE_DATA_SLICE'),
        'transcoder_default_url' => env('NIMBLE_TRANSCODER_DEFAULT_URL'),
        'edge_servers' => [
            "edge4" => [
                "host" => 'edge4.example.com',
                "priority" => 0,
                "status" => 0
            ],
            "edge11" => [
                "host" => 'edge11.example.com',
                "priority" => 0,
                "status" => 0
            ],
            "gcore" => [
                "host" => 'cdn1.example.com',
                "priority" => 20,
                "status" => 1
            ],
            "cdnvideo" => [
                "host" => 'cdn2.example.com',
                "priority" => 0,
                "status" => 0
            ]
        ]
    ],

    'centrifugo' => [
        'url' => env('CENTRIFUGO_API_ADDRESS'),
        'key' => env('CENTRIFUGO_APIKEY'),
        'secret' => env('JWT_SECRET_KEY'),
    ]
];
