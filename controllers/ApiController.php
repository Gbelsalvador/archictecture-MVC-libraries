<?php
namespace Controllers;

class ApiController extends Controller
{
    /**
     * Route: GET /api
     */
    public function index(...$args)
    {
        $params = $args[0] ?? [];
        return [
            'status' => 'ok',
            'message' => 'API Controller index',
            'params' => $params
        ];
    }

    /**
     * Route: GET /api/hello
     */
    public function hello($params = [])
    {
        return [
            'message' => 'Hello from ApiController',
            'params' => $params
        ];
    }

    /**
     * Route: POST /api/echo
     */
    public function echoPost(array $input = [])
    {
        return [
            'received' => $input
        ];
    }
}

?>