<?php 

    Router\Router::post('/', [Controllers\ApiController::class, 'rootPost']);

    Router\Router::post('/api/echo', [Controllers\ApiController::class, 'echoPost']);

?>
