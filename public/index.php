<?php
    require dirname(__DIR__) . DIRECTORY_SEPARATOR .'vendor' . DIRECTORY_SEPARATOR .'autoload.php';
    
    // Charger les variables d'environnement depuis .env
    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'env.php';
    loadEnv(dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env');

    $core = new Core\Core();
    $router = new AltoRouter();
    $route = new Router\Router($router);

    $core->header_cors_call();
  
    // origin obligatoire à remplir lorsque l api sera en deployement 
    // Router\Router::origin();


    $core->require_api_route_files($core);

    Router\Router::matcher();
?>