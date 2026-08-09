<?php

namespace App\middleware;

class TrimStringsMiddleware implements MiddlewareInterface {
    public function handle(array $request, callable $next): mixed {
        if (isset($request["POST"])) {
            foreach ($request["POST"] as $key => $value) {
                if(is_string($value)) {
                    $request["POST"][$key] = trim($value);
                }
            }
        }

        return $next($request);

    }
}