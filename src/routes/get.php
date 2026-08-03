<?php 

    App\Router\Router::get('/', [App\Controllers\ApiController::class, 'root']);

    App\Router\Router::get('/api', [App\Controllers\ApiController::class, 'index']);

    App\Router\Router::get('/api/hello', [App\Controllers\ApiController::class, 'hello']);

    App\Router\Router::get('/api/user/[i:id]', [App\Controllers\ApiController::class, 'index']);


?>
