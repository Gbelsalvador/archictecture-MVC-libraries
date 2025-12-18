<?php 

    Router\Router::get('/',function() use ($core){
        $core->http_json_status_methode(200,'api fonctionnelle !!!!!!', ['datas' => 'des datas']);
    });

    // Test route that uses the example controller
    Router\Router::get('/api', function() use ($core) {
        $controller = new Controllers\ApiController();
        $data = $controller->index();
        $core->jsonResponse(200, $data, true);
    });

    Router\Router::get('/api/hello', function() use ($core) {
        $controller = new Controllers\ApiController();
        $data = $controller->hello();
        $core->jsonResponse(200, $data, true);
    });

    Router\Router::get('/api/user/[i:id]', function($params) use ($core) {
        $controller = new Controllers\ApiController();
        $data = $controller->index($params);
        $core->jsonResponse(200, $data, true);
    });


?>