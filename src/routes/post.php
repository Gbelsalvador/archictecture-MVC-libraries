<?php 

    App\Router\Router::post('/', [App\Controllers\ApiController::class, 'rootPost']);

    App\Router\Router::post('/api/echo', [App\Controllers\ApiController::class, 'echoPost']);

?>
