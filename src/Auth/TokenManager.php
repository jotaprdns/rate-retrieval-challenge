<?php

namespace App\Auth;

use App\Http\ApiClient;

class TokenManager
{
    private ApiClient $client;
    private string $username;
    private string $password;
    private string $tokenStoragePath;
    private ?array $cachedToken = null;

    public function __construct(ApiClient $client, string $username, string $password, string $tokenStoragePath)
    {
        $this->client           = $client;
        $this->username         = $username;
        $this->password         = $password;
        $this->tokenStoragePath = $tokenStoragePath;
    }

    public function getValidToken(): string
    {
        if ($this->cachedToken === null) {
            $this->cachedToken = $this->loadTokenFromStorage();
        }

        if ($this->cachedToken && !$this->isExpired($this->cachedToken['token'])) {
            return $this->cachedToken['token'];
        }

        // Si hay token viejo pero expiró, intentar refresh
        if ($this->cachedToken) {
            $refreshed = $this->refreshToken($this->cachedToken['token']);
            if ($refreshed !== null) {
                $this->saveTokenToStorage($refreshed);
                $this->cachedToken = ['token' => $refreshed];
                return $refreshed;
            }
        }

        // Si no hay token o no se pudo refrescar, hacer login
        $newToken = $this->login();
        $this->saveTokenToStorage($newToken);
        $this->cachedToken = ['token' => $newToken];

        return $newToken;
    }

    private function login(): string
    {
        $response = $this->client->post('login', [
            'username' => $this->username,
            'password' => $this->password,
        ]);

        if (
            $response['status_code'] !== 200
            || !isset($response['body']['data']['accessToken'])
        ) {
            throw new \RuntimeException('Login failed: ' . $response['raw']);
        }

        return $response['body']['data']['accessToken'];
    }

    private function refreshToken(string $oldToken): ?string
    {
        $response = $this->client->post('refreshtoken', [
            'token' => $oldToken,
        ]);

        if (
            $response['status_code'] === 200
            && isset($response['body']['data']['accessToken'])
        ) {
            return $response['body']['data']['accessToken'];
        }

        // Si el refresh falla, devuelve null para forzar un login completo
        return null;
    }

    private function loadTokenFromStorage(): ?array
    {
        if (!file_exists($this->tokenStoragePath)) {
            return null;
        }

        $content = file_get_contents($this->tokenStoragePath);
        $data    = json_decode($content, true);

        return $data ?: null;
    }

    private function saveTokenToStorage(string $token): void
    {
        $data = [
            'token' => $token,
        ];

        file_put_contents($this->tokenStoragePath, json_encode($data));
    }

    private function isExpired(string $jwt): bool
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return true; // JWT inválido -> forzar login
        }

        $payload = json_decode($this->base64UrlDecode($parts[1]), true);

        if (!isset($payload['exp'])) {
            return true;
        }

        $now = time();
        // Pequeño margen de seguridad de 60 segundos
        return ($payload['exp'] <= $now + 60);
    }

    private function base64UrlDecode(string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        $input = strtr($input, '-_', '+/');

        return base64_decode($input);
    }
}