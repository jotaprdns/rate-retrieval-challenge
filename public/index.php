<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Http\ApiClient;
use App\Auth\TokenManager;
use App\Service\RateService;
use App\Helpers\RateHelper;

$config = require __DIR__ . '/../config/config.php';

$apiClient = new ApiClient($config['base_url']);

$tokenManager = new TokenManager(
    $apiClient,
    $config['credentials']['username'],
    $config['credentials']['password'],
    $config['token_storage']
);

$rateService = new RateService(
    $apiClient,
    $tokenManager,
    $config['vendor_id']
);

// Parámetros
$params = [
    'originCity'         => 'KEY LARGO',
    'originState'        => 'FL',
    'originZipcode'      => '33037',
    'originCountry'      => 'US',
    'destinationCity'    => 'LOS ANGELES',
    'destinationState'   => 'CA',
    'destinationZipcode' => '90001',
    'destinationCountry' => 'US',
    'UOM'                => 'US',
    'freightInfo'        => [
        [
            'qty'        => 1,
            'weight'     => 100,
            'weightType' => 'each',
            'length'     => 40,
            'width'      => 40,
            'height'     => 40,
            'class'      => 100,
            'hazmat'     => 0,
            'commodity'  => '',
            'dimType'    => 'PLT',
            'stack'      => false,
        ],
    ],
];

try {
    $rates = $rateService->getRates($params);

    echo "All Rates:\n";
    print_r($rates);

    $cheapest = RateHelper::getCheapestRatesByServiceLevel($rates);

    echo "\nCheapest Rates by Service Level:\n";
    print_r($cheapest);
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage();
}