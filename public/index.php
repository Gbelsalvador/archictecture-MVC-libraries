<?php
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'env.php';
loadEnv(dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env');

$response = new App\Core\Response();

try {
    $core = new App\Core\Core($response);
    $router = new AltoRouter();
    $route = new App\Router\Router($router);
    App\Router\Router::setDispatcher(fn (string $controllerClass) => $core->makeController($controllerClass));

    $core->header_cors_call();

    // origin obligatoire à remplir lorsque l api sera en deployement
    // App\Router\Router::origin();

    $core->require_api_route_files();

    App\Router\Router::matcher();
} catch (\Throwable $exception) {
    $message = App\Core\AppConfig::isDebug()
        ? $exception->getMessage()
        : 'Une erreur interne est survenue.';

    $response->json([
        'success' => false,
        'error' => 'server_error',
        'message' => $message,
    ], 500);
}
?>
