<?php
// config/sabre.php

return [
    /*
    |--------------------------------------------------------------------------
    | Sabre API Configuration
    |--------------------------------------------------------------------------
    */

    'enabled' => env('FLIGHT_VALIDATION_ENABLED', true),

    // REST API Configuration
    'rest' => [
        'base_url' => env('SABRE_BASE_URL', 'https://api.cert.platform.sabre.com'),
        'client_id' => env('SABRE_CLIENT_ID'),
        'client_secret' => env('SABRE_CLIENT_SECRET'),
        'username' => env('SABRE_USERNAME'),
        'password' => env('SABRE_PASSWORD'),
    ],

    // SOAP API Configuration
    'soap' => [
        'base_url' => env('SABRE_SOAP_URL', 'https://webservices.cert.platform.sabre.com'),
        'username' => env('SABRE_USERNAME_SOAP'),
    ],

    // PCC & Organization
    'pcc' => env('SABRE_PCC', '27YK'),
    'organization' => env('SABRE_ORGANIZATION', '27YK'),

    // Token Cache
    'token_cache_minutes' => env('SABRE_TOKEN_CACHE_MINUTES', 14),

    // Cookie (if needed)
    'cookie' => env('SABRE_COOKIE'),

    // API Version
    'api_version' => '5',

    // Search Preferences
    'search' => [
        'num_trips' => env('SABRE_MAX_SOLUTIONS', 50),
        'request_type' => '50ITINS',
    ],
];
