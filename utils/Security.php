<?php
namespace Utils;

class Security
{
    public static function sanitize($input)
    {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }

        if (!is_string($input)) {
            return $input;
        }

        $s = trim($input);
        $s = strip_tags($s);
        return $s;
    }

    public static function escape(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function generateCsrfToken(string $key = 'csrf_token'): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        $token = bin2hex(random_bytes(32));
        $_SESSION[$key] = $token;
        return $token;
    }

    public static function verifyCsrfToken(string $token, string $key = 'csrf_token'): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        if (empty($_SESSION[$key])) return false;
        $ok = hash_equals($_SESSION[$key], $token);
        if ($ok) {
            unset($_SESSION[$key]);
        }
        return $ok;
    }

    /**
     * Very small rate limiter stored in session: maxRequests per windowSeconds
     */
    public static function rateLimit(int $maxRequests = 30, int $windowSeconds = 60, string $key = 'rate_limit'): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $now = time();
        $_SESSION[$key] = $_SESSION[$key] ?? [];
        $_SESSION[$key][$ip] = $_SESSION[$key][$ip] ?? ['ts' => $now, 'count' => 0];
        $entry = &$_SESSION[$key][$ip];
        if ($now - $entry['ts'] > $windowSeconds) {
            $entry['ts'] = $now;
            $entry['count'] = 0;
        }
        $entry['count']++;
        return $entry['count'] <= $maxRequests;
    }
}

?>
