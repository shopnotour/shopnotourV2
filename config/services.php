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
// config/services.php
    'whatsapp' => [
        'api_key' => env('WHATSAPP_API_KEY'),
        'phone_number' => env('WHATSAPP_PHONE_NUMBER'),
        'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),
    ],

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

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    'air_arabia' => [
        // Endpoints
        'auth_url'          => env('AIR_ARABIA_AUTH_URL'),
        'search_url'        => env('AIR_ARABIA_SEARCH_URL'),
        'soap_url'          => env('AIR_ARABIA_SOAP_URL'),

        // Credentials
        'login'             => env('AIR_ARABIA_LOGIN'),
        'username'          => env('AIR_ARABIA_USERNAME'),
        'password'          => env('AIR_ARABIA_PASSWORD'),
        'agent_code'        => env('AIR_ARABIA_AGENT_CODE'),

        // Defaults
        'default_currency'  => env('AIR_ARABIA_DEFAULT_CURRENCY', 'BDT'),
        'default_country'   => env('AIR_ARABIA_DEFAULT_COUNTRY',  'BD'),
        'default_station'   => env('AIR_ARABIA_DEFAULT_STATION',  'DAC'),

        // SSL (staging = false, live = true)
        'ssl_verify'        => env('AIR_ARABIA_SSL_VERIFY', false),

        // Token cache TTL (ঘণ্টায়)
        'token_ttl_hours'   => env('AIR_ARABIA_TOKEN_TTL_HOURS', 23),
    ],

];
