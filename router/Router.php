<?php

 namespace Router;
   class Router
   {
       private static $router;

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

       public static function matcher()
       {
          $match = static::$router->match();
   
          if($match && is_callable($match['target'])){
            call_user_func($match['target'],$match['params']);
          }
          else{
            self::respondNotFound();
          }
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