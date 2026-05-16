<?php

return [
    'base_url' => env('BKASH_BASE_URL', 'https://tokenized.sandbox.bka.sh/v1.2.0-beta'),
    'app_key' => env('BKASH_APP_KEY', '4f6o0cjiki2rfm34kfdadl1eqq'),
    'app_secret' => env('BKASH_APP_SECRET', '2is7hdktrekvrbljjh44ll3d9l1dtjo4pasmjvs5vl5qr3fug4b'),
    'username' => env('BKASH_USERNAME', 'sandboxTokenizedUser02'),
    'password' => env('BKASH_PASSWORD', 'sandboxTokenizedUser02@12345'),

    'callback_url' => env('APP_URL') . 'success',

    'routes' => [
        'enable' => false, // Vendor routes disable
    ],
];

