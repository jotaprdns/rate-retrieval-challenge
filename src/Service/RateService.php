<?php

namespace App\Service;

use App\Http\ApiClient;
use App\Auth\TokenManager;

class RateService
{
    private ApiClient $client;
    private TokenManager $tokenManager;
    private string $vendorId;

    public function __construct(ApiClient $client, TokenManager $tokenManager, string $vendorId)
    {
        $this->client       = $client;
        $this->tokenManager = $tokenManager;
        $this->vendorId     = $vendorId;
    }

    public function getRates(array $params): array
    {
        $token = $this->tokenManager->getValidToken();

        $headers = [
            'Authorization: Bearer ' . $token,
        ];

        // API: GET /database/vendor/contract/{vendorId}/rate
        // freightInfo es un arreglo de objetos JSON -> se manda como string JSON en un query param
        if (isset($params['freightInfo']) && is_array($params['freightInfo'])) {
            $params['freightInfo'] = json_encode($params['freightInfo']);
        }

        $path = 'database/vendor/contract/' . $this->vendorId . '/rate';

        $response = $this->client->get($path, $params, $headers);

        if ($response['status_code'] !== 200) {
            throw new \RuntimeException('Error getting rates: ' . $response['raw']);
        }

        $body = $response['body'] ?? [];

        if (!isset($body['data']['results']) || !is_array($body['data']['results'])) {
            return [];
        }

        // Transforma la respuesta al formato solicitado
        return $this->transformRates($body['data']['results']);
    }

    private function transformRates(array $results): array
    {
        $response = [];

        foreach ($results as $rate) {
            $response[] = [
                'CARRIER'       => $rate['name']         ?? '',
                'SERVICE LEVEL' => $rate['serviceLevel'] ?? '',
                'RATE TYPE'     => $rate['rateType']     ?? '',
                'TOTAL'         => $rate['total']        ?? 0,
                'TRANSIT TIME'  => $rate['transitDays']  ?? null,
            ];
        }

        return $response;
    }
}