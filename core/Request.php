<?php

namespace Core;

class Request
{
    private ?array $jsonCache = null;
    private bool $jsonDecoded = false;

    public function __construct(
        private readonly ?string $rawBody = null,
        private readonly ?array $server = null,
        private readonly ?array $files = null
    ) {
    }

    public function json(): array
    {
        if ($this->jsonDecoded) {
            return $this->jsonCache ?? [];
        }

        $this->jsonDecoded = true;
        $raw = $this->body();

        if ($raw === '') {
            $this->jsonCache = [];
            return $this->jsonCache;
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException(json_last_error_msg());
        }

        if (!is_array($decoded)) {
            throw new \InvalidArgumentException('Le contenu JSON doit être un objet ou un tableau associatif.');
        }

        $this->jsonCache = $decoded;
        return $this->jsonCache;
    }

    public function body(): string
    {
        if ($this->rawBody !== null) {
            return $this->rawBody;
        }

        $raw = file_get_contents('php://input');
        if ($raw === false) {
            return '';
        }

        return $raw;
    }

    public function server(string $key, mixed $default = null): mixed
    {
        $server = $this->server ?? $_SERVER;
        return $server[$key] ?? $default;
    }

    public function authorizationHeader(): ?string
    {
        $header = $this->server('HTTP_AUTHORIZATION');
        if (!empty($header)) {
            return (string) $header;
        }

        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            foreach ($headers as $key => $value) {
                if (strcasecmp($key, 'Authorization') === 0) {
                    return $value;
                }
            }
        }

        return null;
    }

    public function method(): string
    {
        return strtoupper((string) $this->server('REQUEST_METHOD', 'GET'));
    }

    public function uri(): string
    {
        return (string) $this->server('REQUEST_URI', '/');
    }

    public function files(): array
    {
        return $this->files ?? $_FILES;
    }
}

