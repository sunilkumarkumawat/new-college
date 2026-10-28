<?php

use App\Payments\Drivers\Hdfc\HdfcGateway;
use App\Payments\Drivers\Icici\IciciGateway;
use App\Payments\Drivers\Sbi\SbiGateway;

/*
 * Every payment account the project can collect money into.
 * The key (icici_1, ...) is stored with each payment and used in URLs:
 *   /pay/{key}   /payment/{key}/callback   /payment/{key}/webhook
 * Keep keys short and never rename one after real payments exist.
 * txn_prefix: up to 4 letters, different per account (shows which account a txn belongs to).
 */
return [
    'accounts' => [

        'icici_1' => [
            'label'                => 'ICICI Bank (account 1)',
            'driver'               => IciciGateway::class,
            'txn_prefix'           => 'ICA',
            'env'                  => env('PAY_ICICI1_ENV', 'uat'),   // uat | prod
            'merchant_id'          => env('PAY_ICICI1_MERCHANT_ID'),
            'aggregator_id'        => env('PAY_ICICI1_AGGREGATOR_ID'),
            'secret_key'           => env('PAY_ICICI1_SECRET_KEY'),
            'strict_response_hash' => (bool) env('PAY_ICICI1_STRICT_HASH', false),
        ],

        'icici_2' => [
            'label'                => 'ICICI Bank (account 2)',
            'driver'               => IciciGateway::class,
            'txn_prefix'           => 'ICB',
            'env'                  => env('PAY_ICICI2_ENV', 'uat'),
            'merchant_id'          => env('PAY_ICICI2_MERCHANT_ID'),
            'aggregator_id'        => env('PAY_ICICI2_AGGREGATOR_ID'),
            'secret_key'           => env('PAY_ICICI2_SECRET_KEY'),
            'strict_response_hash' => (bool) env('PAY_ICICI2_STRICT_HASH', false),
        ],

        // Uncomment once app/Payments/Drivers/Sbi/SbiGateway.php is filled from the SBI kit.
        // Add whatever keys the SBI kit needs (they reach the driver as $this->cfg).
        // 'sbi' => [
        //     'label'       => 'State Bank of India',
        //     'driver'      => SbiGateway::class,
        //     'txn_prefix'  => 'SBI',
        //     'env'         => env('PAY_SBI_ENV', 'uat'),
        //     'merchant_id' => env('PAY_SBI_MERCHANT_ID'),
        //     'secret_key'  => env('PAY_SBI_SECRET_KEY'),
        // ],

        // Uncomment once app/Payments/Drivers/Hdfc/HdfcGateway.php is filled from the HDFC kit.
        // 'hdfc' => [
        //     'label'       => 'HDFC Bank',
        //     'driver'      => HdfcGateway::class,
        //     'txn_prefix'  => 'HDF',
        //     'env'         => env('PAY_HDFC_ENV', 'uat'),
        //     'merchant_id' => env('PAY_HDFC_MERCHANT_ID'),
        //     'secret_key'  => env('PAY_HDFC_SECRET_KEY'),
        // ],
    ],
];
