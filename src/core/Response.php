<?php

namespace App\Core;

class Response
{
    public function json(array $payload, int $status = 200, bool $terminate = true): void
    {
        http_response_code($status);

        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($terminate) {
            exit;
        }
    }
}

