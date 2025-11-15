<?php 

    Router\Router::get('/',function() use ($core){
        $core->http_json_status_methode(200,'api fonctionnelle !!!!!!', ['datas' => 'des datas']);
    });


?>