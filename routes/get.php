<?php 

    Router\Router::get('/',function() use ($core){
        $core->http_json_status_methode(200,'api fonctionnelle !!!!!!', ['datas' => 'des datas']);
    });

    Router\Router::get('/api', [Controllers\ApiController::class, 'index']);

    Router\Router::get('/api/hello', [Controllers\ApiController::class, 'hello']);

    Router\Router::get('/api/user/[i:id]', [Controllers\ApiController::class, 'index']);


?>
