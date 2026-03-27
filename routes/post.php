<?php 

    Router\Router::post('/',function() use ($core){
        $core->http_json_status_methode(200,'api fonctionnelle !!!!!!', ['datas' => 'des datas']);
    });

    // Test POST route: echo JSON body
    Router\Router::post('/api/echo', function() use ($core) {
        $input = $core->http_json_input();
        $controller = new Controllers\ApiController(null, $core->getResponse());
        $controller->echoPost($input);
    });

?>
