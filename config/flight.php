<?php
return [
    'flight_route_prefix' => env("FLIGHT_ROUTE_PREFIX","flight"),

    // Supplier fee as percentage of base fare (e.g., 2 = 2%)
    'supplier_fee_percent' => env('FLIGHT_SUPPLIER_FEE_PERCENT', 0),

    // Ticketing fee as flat amount in base currency (e.g., 50 BDT)
    'ticketing_fee_amount' => env('FLIGHT_TICKETING_FEE_AMOUNT', 0),

    'sabre' => [
        'pcc' => env('SABRE_PCC', '27YK'),
        'requestor_id' => env('SABRE_REQUESTOR_ID', '1'),
        'company_code' => env('SABRE_COMPANY_CODE', 'TN'),
        'company_short_name' => env('SABRE_COMPANY_SHORT_NAME', 'TN'),
    ],
];
