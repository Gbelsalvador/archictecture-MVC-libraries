<?php

namespace Core;

class Core
{
    protected Response $response;
    protected AuthService $authService;

    public function __construct(?Response $response = null, ?AuthService $authService = null)
    {
        $this->response = $response ?? new Response();
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
    public function http_json_input(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->response->json([
                'success' => false,
                'error' => 'invalid_json_payload',
                'message' => json_last_error_msg()
            ], 400);
        }

        if (!is_array($decoded)) {
            $this->response->json([
                'success' => false,
                'error' => 'invalid_json_payload',
                'message' => 'Le contenu JSON doit être un objet ou un tableau associatif.'
            ], 400);
        }

        return $decoded;
    }

    /**
     * Retourne le header Authorization si disponible.
     */
    protected function getAuthorizationHeader(): ?string
    {
        if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
            return $_SERVER['HTTP_AUTHORIZATION'];
        }

        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            foreach ($headers as $key => $value) {
                if (strcasecmp($key, 'Authorization') === 0) {
                    return $value;
                }
            }
        }

        return null;
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

    /**
     * require_api_route_files
     *
     * @param Core $core : une instance de la classe est passé en params de la methode
     * pour en affaire appel dans les fonctions anonymes lors de la creation d une route
     * @return void
     */
    public function require_api_route_files(): void
    {
        $core = $this;
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

        $origin = $_SERVER['HTTP_ORIGIN'] ?? null;
        $allowedOrigins = AppConfig::allowedOrigins();

        if ($origin && in_array($origin, $allowedOrigins, true)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Vary: Origin');
        } elseif (!$origin && empty($allowedOrigins) && !AppConfig::isProduction()) {
            header('Access-Control-Allow-Origin: *');
        }

        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
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
}

?>
