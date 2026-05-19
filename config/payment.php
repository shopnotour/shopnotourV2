<?php
return [

    /*
    |------------------------------------------------------------------
    | Mobile Banking
    |------------------------------------------------------------------
    | প্রতিটা entry একটা method।
    | id     — unique key (ছোট হাতে, space ছাড়া)
    | number — phone number
    | icon   — emoji
    | bg     — background color (light)
    | color  — text/button color
    | border — border color
    | sub    — subtitle text
    |------------------------------------------------------------------
    */
    'mobile' => [
        [
            'id'     => 'bkash',
            'name'   => 'bKash',
            'number' => env('BKASH_NUMBER', '01708154802'),
            'icon'   => 'images/icons/svg/BKash-Icon-Logo.wine.svg',
            'bg'     => '#fff0f7',
            'color'  => '#db2777',
            'border' => '#fbcfe8',
            'sub'    => 'Payment / 1.2% charge',
        ],
        //        [
        //            'id'     => 'nagad',
        //            'name'   => 'Nagad',
        //            'number' => env('NAGAD_NUMBER', '01XXXXXXXXX'),
        //            'icon'   => '🟠',
        //            'bg'     => '#fff7ed',
        //            'color'  => '#ea580c',
        //            'border' => '#fed7aa',
        //            'sub'    => 'Send Money / Personal',
        //        ],
        //        [
        //            'id'     => 'rocket',
        //            'name'   => 'Rocket',
        //            'number' => env('ROCKET_NUMBER', '01XXXXXXXXX'),
        //            'icon'   => '🚀',
        //            'bg'     => '#f5f3ff',
        //            'color'  => '#7c3aed',
        //            'border' => '#ddd6fe',
        //            'sub'    => 'Send Money / Personal',
        //        ],
    ],

    /*
    |------------------------------------------------------------------
    | Bank Accounts
    |------------------------------------------------------------------
    | একাধিক bank add করতে এই array-এ নতুন entry যোগ করো।
    | fields[] — প্রতিটা row যা দেখাবে।
    |   label — বাম দিকে label
    |   value — ডান দিকে value
    |   mono  — true হলে monospace font (account no, routing এর জন্য)
    |------------------------------------------------------------------
    */
    'banks' => [

        // ── Bank 1: Dutch Bangla Bank ──────────────────────────────
        [
            'id'     => 'dbbl',
            'name'   => env('BANK1_NAME',   'Dutch Bangla Bank Ltd.'),
            'icon'   => 'images\icons\svg\Dutch-bangla-bank-ltd.png',
            'bg'     => '#eff6ff',
            'color'  => '#1d4ed8',
            'border' => '#bfdbfe',
            'fields' => [
                ['label' => 'Account Name', 'value' => env('BANK1_ACC_NAME', 'Shopno Tour'),      'mono' => false],
                ['label' => 'Account No.',  'value' => env('BANK1_ACC_NO',   '2461100010970'),  'mono' => true],
                ['label' => 'Branch',       'value' => env('BANK1_BRANCH',   'Gulshan circle 1'),   'mono' => false],
                ['label' => 'Routing No.',  'value' => env('BANK1_ROUTING',  '090260463'),        'mono' => true],
                ['label' => 'SWIFT/BIC',    'value' => env('BANK1_SWIFT',    'DBBLBDDH'),         'mono' => true],
            ],
        ],

        // ── Bank 2: Islami Bank ────────────────────────────────────
        // এই block uncomment করে ব্যবহার করো
        // [
        //     'id'     => 'ibbl',
        //     'name'   => env('BANK2_NAME',   'Islami Bank Bangladesh'),
        //     'icon'   => '🏛️',
        //     'bg'     => '#f0fdf4',
        //     'color'  => '#16a34a',
        //     'border' => '#bbf7d0',
        //     'fields' => [
        //         ['label' => 'Account Name', 'value' => env('BANK2_ACC_NAME', 'Shopno Tour'),      'mono' => false],
        //         ['label' => 'Account No.',  'value' => env('BANK2_ACC_NO',   '20501XXXXXXXXXX'), 'mono' => true],
        //         ['label' => 'Branch',       'value' => env('BANK2_BRANCH',   'Motijheel Branch'),'mono' => false],
        //         ['label' => 'Routing No.',  'value' => env('BANK2_ROUTING',  '125XXXXXX'),       'mono' => true],
        //     ],
        // ],

        // ── Bank 3: City Bank ──────────────────────────────────────
        [
            'id'     => 'citybank',
            'name'   => env('BANK3_NAME',   'City Bank Ltd.'),
            'icon'   => 'images\icons\svg\6986e9fad797b-City-Bank.svg',
            'bg'     => '#fdf4ff',
            'color'  => '#9333ea',
            'border' => '#e9d5ff',
            'fields' => [
                ['label' => 'Account Name', 'value' => env('BANK3_ACC_NAME', 'Shopno Tour'),     'mono' => false],
                ['label' => 'Account No.',  'value' => env('BANK3_ACC_NO',   '1401957897001'), 'mono' => true],
                ['label' => 'Branch',       'value' => env('BANK3_BRANCH',   'Gulshan'),      'mono' => false],
                ['label' => 'Routing No.',  'value' => env('BANK3_ROUTING',  '225261729'),      'mono' => true],
            ],
        ],

        // ── Bank 4: Eastern Bank ──────────────────────────────────────
        [
            'id'     => 'Eastern Bank',
            'name'   => env('BANK4_NAME',   'Eastern Bank Ltd.'),
            'icon'   => 'images\icons\svg\Eastern_Bank_PLC-_idN8BD0Tqn_1.svg',
            'bg'     => '#fdf4ff',
            'color'  => '#9333ea',
            'border' => '#e9d5ff',
            'fields' => [
                ['label' => 'Account Name', 'value' => env('BANK4_ACC_NAME', 'Shopno Tour'),     'mono' => false],
                ['label' => 'Account No.',  'value' => env('BANK4_ACC_NO',   '1041060000865'), 'mono' => true],
                ['label' => 'Branch',       'value' => env('BANK4_BRANCH',   'Gulshan Avenue'),      'mono' => false],
                ['label' => 'Routing No.',  'value' => env('BANK4_ROUTING',  '095261733'),      'mono' => true],
            ],
        ],


        // ── Bank 6: BRAC Bank ──────────────────────────────────────
        [
            'id'     => 'BRAC Bank',
            'name'   => env('BANK6_NAME',   'BRAC Bank.'),
            'icon'   => 'images\icons\svg\BRAC_Bank_PLC_idMnF4oGtS_1.svg',
            'bg'     => '#fdf4ff',
            'color'  => '#9333ea',
            'border' => '#e9d5ff',
            'fields' => [
                ['label' => 'Account Name', 'value' => env('BANK6_ACC_NAME', 'Shopno Tour'),     'mono' => false],
                ['label' => 'Account No.',  'value' => env('BANK6_ACC_NO',   '2055681460001'), 'mono' => true],
                ['label' => 'Branch',       'value' => env('BANK6_BRANCH',   'Gulshan'),      'mono' => false],
                ['label' => 'Routing No.',  'value' => env('BANK6_ROUTING',  '60261726'),      'mono' => true],
            ],
        ],
        // ── Bank 6: BRAC Bank ──────────────────────────────────────
        [
            'id'     => 'IBBL Bank',
            'name'   => env('BANK7_NAME',   'IBBL Bank.'),
            'icon'   => 'images\icons\svg\Islami_Bank_Bangladesh_PLC_idJDXI09ZB_1.svg',
            'bg'     => '#fdf4ff',
            'color'  => '#9333ea',
            'border' => '#e9d5ff',
            'fields' => [
                ['label' => 'Account Name', 'value' => env('BANK7_ACC_NAME', 'Shopno Tour'),     'mono' => false],
                ['label' => 'Account No.',  'value' => env('BANK7_ACC_NO',   '20502760100217315'), 'mono' => true],
                ['label' => 'Branch',       'value' => env('BANK7_BRANCH',   'Gulshan circle 1'),      'mono' => false],
                ['label' => 'Routing No.',  'value' => env('BANK7_ROUTING',  '02313850'),      'mono' => true],
            ],
        ],

    ],

    'gateways' => [
        'offline_payment' => Modules\Booking\Gateways\OfflinePaymentGateway::class,
        'paypal' => Modules\Booking\Gateways\PaypalGateway::class,
        'stripe' => Modules\Booking\Gateways\StripeGateway::class,
        'payrexx' => Modules\Booking\Gateways\PayrexxGateway::class,
        'paystack' => Modules\Booking\Gateways\PaystackGateway::class,
    ],
];
