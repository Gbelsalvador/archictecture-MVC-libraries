<?php

namespace Models;

class UserModel
{
    protected DataModel $db;

    public function __construct(?DataModel $db = null)
    {
        $this->db = $db ?? new DataModel();
    }

    /**
     * Vérifie et décode un token JWT (HS256).
     * Retourne le payload en tableau associatif si valide, sinon null.
     */
    public function verifyJWT(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$h64, $p64, $s64] = $parts;

        $header = $this->base64UrlDecode($h64);
        $payload = $this->base64UrlDecode($p64);
        $sig = $this->base64UrlDecode($s64);

        if ($header === false || $payload === false || $sig === false) {
            return null;
        }

        $secret = $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET') ?: 'dev_secret';

        $expected = hash_hmac('sha256', "$h64.$p64", $secret, true);

        if (!hash_equals($expected, $sig)) {
            return null;
        }

        $decoded = json_decode($payload, true);
        if (!is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    private function base64UrlDecode(string $input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        $decoded = base64_decode(strtr($input, '-_', '+/'));
        return $decoded === false ? false : $decoded;
    }
}

?>