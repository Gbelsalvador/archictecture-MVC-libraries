<?php

 namespace App\Router;
   class Router
   {
       private static $router;
       private static $dispatcher;

       public function __construct(\AltoRouter $router)
       {
            static::$router =   $router;
       }

       public static function get(string $route, $target, string $name = '')
       {
         static::$router->map('GET',$route,$target,$name);
       }

       public static function post(string $route, $target, string $name = '')
       {
         static::$router->map('POST',$route,$target,$name);
       }

       public static function delete(string $route, $target, string $name = '')
       {
         static::$router->map('DELETE',$route,$target,$name);
       }

       public static function put(string $route, $target, string $name = '')
       {
         static::$router->map('PUT',$route,$target,$name);
       }

       public static function origin($path)
       {
         static::$router->setBasePath($path);
       }

       public static function setDispatcher(callable $dispatcher)
       {
         static::$dispatcher = $dispatcher;
       }

       public static function matcher()
       {
          $match = static::$router->match();
   
          if ($match && is_callable($match['target'])) {
            call_user_func($match['target'], $match['params']);
          }
          elseif ($match && is_array($match['target']) && count($match['target']) === 2) {
            self::dispatchControllerTarget($match['target'], $match['params']);
          }
          else{
            self::respondNotFound();
          }
       }

       private static function dispatchControllerTarget(array $target, array $params): void
       {
           [$controllerClass, $method] = $target;

           if (!is_string($controllerClass) || !is_string($method)) {
               throw new \RuntimeException('Cible de route invalide.');
           }

           if (!is_callable(static::$dispatcher)) {
               throw new \RuntimeException('Aucun dispatcher de contrôleurs n\'est configuré.');
           }

           $controller = call_user_func(static::$dispatcher, $controllerClass);
           if (!method_exists($controller, $method)) {
               throw new \RuntimeException("Méthode de contrôleur introuvable : {$controllerClass}::{$method}");
           }

           $controller->{$method}($params);
       }

       private  static function respondNotFound()
       {
           http_response_code(404);
           echo json_encode([
               'status' => 404,
               'message' => 'Route introuvable'
           ]);
       }

   }
?>
