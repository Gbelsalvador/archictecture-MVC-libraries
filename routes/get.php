<?php 

    Router\Router::get('/', [Controllers\ApiController::class, 'root']);

    Router\Router::get('/api', [Controllers\ApiController::class, 'index']);

    Router\Router::get('/api/hello', [Controllers\ApiController::class, 'hello']);

    Router\Router::get('/api/user/[i:id]', [Controllers\ApiController::class, 'index']);


?>
