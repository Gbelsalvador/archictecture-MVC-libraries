<?php

namespace Core;

class AppConfig
{
    public static function env(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }

        return $default;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::env($key, $default);
        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower((string) $value);
        if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
            return false;
        }

        return $default;
    }

    public static function environment(): string
    {
        return strtolower((string) self::env('APP_ENV', 'development'));
    }

    public static function isProduction(): bool
    {
        return self::environment() === 'production';
    }

    public static function isDebug(): bool
    {
        return self::bool('APP_DEBUG', !self::isProduction());
    }

    public static function allowedOrigins(): array
    {
        $origins = (string) self::env('CORS_ALLOWED_ORIGINS', '');
        if ($origins === '') {
            return [];
        }

        $values = array_map('trim', explode(',', $origins));
        return array_values(array_filter($values, static fn (string $origin): bool => $origin !== ''));
    }
}

