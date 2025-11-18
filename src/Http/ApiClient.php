<?php

namespace App\Http;

class ApiClient
{
    private string $baseUrl;
    private int $timeout;

    public function __construct(string $baseUrl, int $timeout = 30)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;
    }

    public function get(string $path, array $query = [], array $headers = []): array
    {
        $url = $this->baseUrl . '/' . ltrim($path, '/');

        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $this->request('GET', $url, null, $headers);
    }

    public function post(string $path, array $body = [], array $headers = []): array
    {
        $url = $this->baseUrl . '/' . ltrim($path, '/');
        $jsonBody = !empty($body) ? json_encode($body) : null;

        $headers[] = 'Content-Type: application/json';

        return $this->request('POST', $url, $jsonBody, $headers);
    }

    private function request(string $method, string $url, ?string $body, array $headers): array
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $responseBody = curl_exec($ch);
        $httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError    = curl_error($ch);

        curl_close($ch);

        if ($curlError) {
            throw new \RuntimeException("HTTP request error: {$curlError}");
        }

        $data = json_decode($responseBody, true);

        return [
            'status_code' => $httpCode,
            'body'        => $data,
            'raw'         => $responseBody,
        ];
    }
}