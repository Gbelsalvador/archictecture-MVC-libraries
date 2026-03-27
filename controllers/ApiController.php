<?php
namespace Controllers;

class ApiController extends Controller
{
    public function root(array $params = []): void
    {
        $this->jsonResponse([
            'success' => true,
            'message' => 'api fonctionnelle !!!!!!',
            'data' => [
                'datas' => 'des datas',
                'params' => $params,
                'method' => 'GET',
            ],
        ]);
    }

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
    public function echoPost(...$args): void
    {
        $this->jsonResponse([
            'received' => $this->readJsonBody()
        ]);
    }

    public function rootPost(...$args): void
    {
        $this->jsonResponse([
            'success' => true,
            'message' => 'api fonctionnelle !!!!!!',
            'data' => [
                'datas' => 'des datas',
                'method' => 'POST',
                'received' => $this->readJsonBody(),
            ],
        ], 201);
    }

    public function rootPut(...$args): void
    {
        $this->jsonResponse([
            'success' => true,
            'message' => 'api fonctionnelle !!!!!!',
            'data' => [
                'datas' => 'des datas',
                'method' => 'PUT',
                'received' => $this->readJsonBody(),
            ],
        ]);
    }

    public function rootDelete(...$args): void
    {
        $this->jsonResponse([
            'success' => true,
            'message' => 'api fonctionnelle !!!!!!',
            'data' => [
                'datas' => 'des datas',
                'method' => 'DELETE',
            ],
        ]);
    }

    private function readJsonBody(): array
    {
        try {
            return $this->request->json();
        } catch (\InvalidArgumentException $exception) {
            $this->jsonError('Payload JSON invalide: ' . $exception->getMessage(), 400);
            return [];
        }
    }
}

?>
