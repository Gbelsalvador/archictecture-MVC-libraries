<?php
namespace App\middleware;

interface MiddlewareInterface{
    /**
     * @param array $request donnée de la requete
     * @param callable $next le middleware suivant ou le controleur
     */
    public function handle(array $request, callable $next): mixed ;
}