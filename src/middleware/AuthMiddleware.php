<?php
namespace App\middleware;
class AuthMiddleware implements MiddlewareInterface{
    public function handle(array $request, callable $next): mixed {

    if(!isset($_SESSION['user'])){
        header('location: /login');
        exit;
    }

    return $next($request);
    }
}