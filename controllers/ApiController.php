<?php
namespace Controllers;

class ApiController extends Controller
{
    /**
     * Route: GET /api
     */
    public function index(...$args): void
    {
        $params = $args[0] ?? [];
        $this->jsonResponse([
            'status' => 'ok',
            'message' => 'API Controller index',
            'params' => $params
        ]);
    }

    /**
     * Route: GET /api/hello
     */
    public function hello($params = []): void
    {
        $this->jsonResponse([
            'message' => 'Hello from ApiController',
            'params' => $params
        ]);
    }

    /**
     * Route: POST /api/echo
     */
    public function echoPost(array $input = []): void
    {
        $this->jsonResponse([
            'received' => $input
        ]);
    }
}

?>
