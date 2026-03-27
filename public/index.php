<?php
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'env.php';
loadEnv(dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env');

$response = new Core\Response();

try {
    $core = new Core\Core($response);
    $router = new AltoRouter();
    $route = new Router\Router($router);
    Router\Router::setDispatcher(fn (string $controllerClass) => $core->makeController($controllerClass));

    $core->header_cors_call();

    // origin obligatoire à remplir lorsque l api sera en deployement
    // Router\Router::origin();

    $core->require_api_route_files();

    Router\Router::matcher();
} catch (\Throwable $exception) {
    $message = Core\AppConfig::isDebug()
        ? $exception->getMessage()
        : 'Une erreur interne est survenue.';

    $response->json([
        'success' => false,
        'error' => 'server_error',
        'message' => $message,
    ], 500);
}
?>
