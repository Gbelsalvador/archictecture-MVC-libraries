<?php

namespace App\Router;

use AltoRouter;

class Router
{
    private static ?AltoRouter $router = null;
    private static $dispatcher = null;

    public function __construct(?AltoRouter $router = null)
    {
        static::$router = $router ?? new AltoRouter();
    }

    private static function router(): AltoRouter
    {
        if (!static::$router instanceof AltoRouter) {
            static::$router = new AltoRouter();
        }

        return static::$router;
    }

    public static function get(string $route, $target, string $name = ''): void
    {
        static::router()->map('GET', $route, $target, $name);
    }

    public static function post(string $route, $target, string $name = ''): void
    {
        static::router()->map('POST', $route, $target, $name);
    }

    public static function delete(string $route, $target, string $name = ''): void
    {
        static::router()->map('DELETE', $route, $target, $name);
    }

    public static function put(string $route, $target, string $name = ''): void
    {
        static::router()->map('PUT', $route, $target, $name);
    }

    public static function origin(string $path): void
    {
        static::router()->setBasePath($path);
    }

    public static function setDispatcher(callable $dispatcher): void
    {
        static::$dispatcher = $dispatcher;
    }

    public static function matcher(): void
    {
        $match = static::router()->match();

        if ($match && is_array($match['target']) && count($match['target']) === 2) {
            self::dispatchControllerTarget($match['target'], $match['params']);
            return;
        }

        if ($match && is_callable($match['target'])) {
            call_user_func($match['target'], $match['params']);
            return;
        }

        self::respondNotFound();
    }

    private static function dispatchControllerTarget(array $target, array $params): void
    {
        [$controllerClass, $method] = $target;

        if (!is_string($controllerClass) || !is_string($method)) {
            throw new \RuntimeException('Cible de route invalide.');
        }

        if (is_callable(static::$dispatcher)) {
            $controller = call_user_func(static::$dispatcher, $controllerClass);
        } elseif (class_exists($controllerClass)) {
            $controller = new $controllerClass();
        } else {
            throw new \RuntimeException('Aucun dispatcher de contrôleurs n\'est configuré.');
        }

        if (!is_object($controller) || !method_exists($controller, $method)) {
            throw new \RuntimeException("Méthode de contrôleur introuvable : {$controllerClass}::{$method}");
        }

        $controller->{$method}($params);
    }

    private static function respondNotFound(): void
    {
        http_response_code(404);

        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        echo json_encode([
            'status' => 404,
            'message' => 'Route introuvable',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
