<?php 

    Router\Router::get('/',function() use ($core){
        $core->http_json_status_methode(200,'api fonctionnelle !!!!!!', ['datas' => 'des datas']);
    });

    // Test route that uses the example controller
    Router\Router::get('/api', function() use ($core) {
        $controller = new Controllers\ApiController(null, $core->getResponse());
        $controller->index();
    });

    Router\Router::get('/api/hello', function() use ($core) {
        $controller = new Controllers\ApiController(null, $core->getResponse());
        $controller->hello();
    });

    Router\Router::get('/api/user/[i:id]', function($params) use ($core) {
        $controller = new Controllers\ApiController(null, $core->getResponse());
        $controller->index($params);
    });


?>
