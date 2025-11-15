<?php

namespace Core;

class Core
{
    /** @var \UserModel */
    protected $userModel;

    public function __construct()
    {
        $baseDir = dirname(__DIR__);
        require_once $baseDir . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'DataModel.php';
        require_once $baseDir . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'UserModel.php';

        $this->userModel = new \UserModel();
    }

    /**
     * Retourne une réponse JSON normalisée.
     */
    public function http_json_status_methode(int $status, string $message = '', array $datas = []): void
    {
        $payload = ['message' => $message];
        if (!empty($datas)) {
            $payload['data'] = $datas;
        }

        $this->jsonResponse($status, $payload);
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
            $this->jsonResponse(400, [
                'error' => 'invalid_json_payload',
                'message' => json_last_error_msg()
            ]);
            exit;
        }

        if (!is_array($decoded)) {
            $this->jsonResponse(400, [
                'error' => 'invalid_json_payload',
                'message' => 'Le contenu JSON doit être un objet ou un tableau associatif.'
            ]);
            exit;
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
                $this->jsonResponse(401, [
                    'error' => 'missing_token',
                    'message' => 'Le header Authorization Bearer est requis.'
                ]);
                exit;
            }
            return null;
        }

        $payload = $this->userModel->verifyJWT($token);
        if (!$payload || empty($payload['user_id'])) {
            if ($required) {
                $this->jsonResponse(401, [
                    'error' => 'invalid_token',
                    'message' => 'Token invalide ou expiré.'
                ]);
                exit;
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
    public function require_api_route_files($core): void
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
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');

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
        http_response_code($status);
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($terminate) {
            exit;
        }
    }
}

?>