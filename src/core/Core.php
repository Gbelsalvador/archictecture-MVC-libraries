<?php

namespace App\Core;
use APP\Core\Response;
class Core
{
    protected Response $response;
    protected AuthService $authService;
    protected Request $request;

    public function __construct(?Response $response = null, ?AuthService $authService = null, ?Request $request = null)
    {
        $this->response = $response ?? new Response();
        $this->request = $request ?? new Request();
        $this->authService = $authService ?? new AuthService($this->response);
    }

    /**
     * Retourne une réponse JSON normalisée.
     */
    public function http_json_status_methode(int $status, string $message = '', array $datas = []): void
    {
        $payload = ['success' => $status < 400, 'message' => $message];
        if (!empty($datas)) {
            $payload['data'] = $datas;
        }

        $this->response->json($payload, $status);
    }

    /**
     * Récupère et décode le corps JSON de la requête.
     */
    public function http_json_input(): arraybà   
    {
        try {
            return $this->request->json();
        } catch (\InvalidArgumentException $exception) {
            $this->response->json([
                'success' => false,
                'error' => 'invalid_json_payload',
                'message' => $exception->getMessage()
            ], 400);
        }
    }

    /**
     * Retourne le header Authorization si disponible.
     */
    protected function getAuthorizationHeader(): ?string
    {
        return $this->request->authorizationHeader();
    }

    /**
     * Extrait le token Bearer depuis le header Authorization.
     */
    protected function getBearerToken(): ?string
    {
        $header = $this->getAuthorizationHeader();
        if (!$header) {
            return null;
        }

        if (preg_match('/Bearer\s+(\S+)/i', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Vérifie le token JWT et retourne l'ID utilisateur.
     */
    public function getArtisteIdFromToken(bool $required = true): ?int
    {
        $token = $this->getBearerToken();
        if (!$token) {
            if ($required) {
                $this->authService->abortUnauthorized('Le header Authorization Bearer est requis.', 'missing_token');
            }
            return null;
        }

        $payload = $this->authService->verifyToken($token);
        if (!$payload || empty($payload['user_id'])) {
            if ($required) {
                $this->authService->abortUnauthorized('Token invalide ou expiré.');
            }
            return null;
        }

        return (int) $payload['user_id'];
    }

    /**
     * Sert de helper explicite pour les endpoints authentifiés.
     */
    public function requireAuthenticatedUserId(): int
    {
        $userId = $this->getArtisteIdFromToken(true);
        if ($userId === null) {
            // getArtisteIdFromToken gère déjà la réponse + exit
            exit;
        }
        return $userId;
    }

    public function require_api_route_files(): void
    {
        require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'get.php';
        require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'post.php';
        require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'put.php';
        require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'delete.php';
    }


    /**
     * header_cors_call
     * cette methode recevera les en-tetes php pour l appel de cors
     * @return void
     */
    public function header_cors_call(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');

        $origin = $this->request->server('HTTP_ORIGIN');
        $allowedOrigins = AppConfig::allowedOrigins();

        if ($origin && in_array($origin, $allowedOrigins, true)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Vary: Origin');
        } elseif (!$origin && empty($allowedOrigins) && !AppConfig::isProduction()) {
            header('Access-Control-Allow-Origin: *');
        }

        if ($this->request->method() === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    /**
     * Envoie une réponse JSON et termine éventuellement la requête.
     */
    public function jsonResponse(int $status, array $payload, bool $terminate = false): void
    {
        $this->response->json($payload, $status, $terminate);
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function makeController(string $controllerClass): object
    {
        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Contrôleur introuvable : {$controllerClass}");
        }

        return new $controllerClass(null, $this->response, $this->request);
    }
}

?>
