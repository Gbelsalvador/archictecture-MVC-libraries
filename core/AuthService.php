<?php

namespace Core;

class AuthService
{
    public function __construct(private readonly Response $response)
    {
    }

    public function verifyToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;

        $headerJson = $this->base64UrlDecode($encodedHeader);
        $payloadJson = $this->base64UrlDecode($encodedPayload);
        $signature = $this->base64UrlDecode($encodedSignature);

        if ($headerJson === false || $payloadJson === false || $signature === false) {
            return null;
        }

        $header = json_decode($headerJson, true);
        $payload = json_decode($payloadJson, true);

        if (!is_array($header) || !is_array($payload)) {
            return null;
        }

        if (($header['alg'] ?? null) !== 'HS256') {
            return null;
        }

        $secret = $this->getJwtSecret();
        if ($secret === null) {
            return null;
        }

        $expectedSignature = hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, $secret, true);
        if (!hash_equals($expectedSignature, $signature)) {
            return null;
        }

        if (!$this->validateTimeClaims($payload)) {
            return null;
        }

        return $payload;
    }

    public function abortUnauthorized(string $message, string $error = 'invalid_token'): void
    {
        $this->response->json([
            'success' => false,
            'error' => $error,
            'message' => $message,
        ], 401);
    }

    private function getJwtSecret(): ?string
    {
        $secret = trim((string) AppConfig::env('JWT_SECRET', ''));
        if ($secret === '' || $secret === 'change_me_in_production') {
            return null;
        }

        return $secret;
    }

    private function validateTimeClaims(array $payload): bool
    {
        $now = time();

        if (isset($payload['exp']) && (!is_numeric($payload['exp']) || (int) $payload['exp'] < $now)) {
            return false;
        }

        if (isset($payload['nbf']) && (!is_numeric($payload['nbf']) || (int) $payload['nbf'] > $now)) {
            return false;
        }

        if (isset($payload['iat']) && (!is_numeric($payload['iat']) || (int) $payload['iat'] > $now)) {
            return false;
        }

        return true;
    }

    private function base64UrlDecode(string $input): string|false
    {
        $remainder = strlen($input) % 4;
        if ($remainder !== 0) {
            $input .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($input, '-_', '+/'), true);
    }
}

