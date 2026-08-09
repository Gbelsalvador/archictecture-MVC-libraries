<?php
namespace App\middleware;

class Pipeline{
    private array $middlewares = [];

    public function pipe(MiddlewareInterface $middleware):self {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function process(array $request, callable $target): mixed{
        $pipeline = array_reduce(
            array_reverse($this->middlewares),
            function($next, $middleware){
                return function ($request) use ($next, $middleware){
                    return($middleware($request, $next));
            };
            },
            $target
        );
        return $pipeline($request);
    }
}