<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\DataModel;

class Controller
{
    protected DataModel $db;
    protected Response $response;
    protected Request $request;

    public function __construct(?DataModel $db = null, ?Response $response = null, ?Request $request = null)
    {
        $this->db = $db ?? new DataModel();
        $this->response = $response ?? new Response();
        $this->request = $request ?? new Request();
    }

    public function index(...$args)
    {
        // Point d'entrée par défaut pour les contrôleurs enfants.
    }

    public function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new \Exception('Vue non trouvée : ' . $viewFile);
        }

        include $viewFile;
    }

    public function redirection(string $url): void
    {
        if ($url === '') {
            return;
        }

        if (!headers_sent()) {
            header("Location: $url");
        } else {
            echo "<script>window.location.href='" . htmlspecialchars($url, ENT_QUOTES) . "';</script>";
        }

        exit;
    }

    public function isConnected(): bool
    {
        return $this->db->getPDO() instanceof \PDO;
    }

    public function success(string $message): void
    {
        echo '<h1>Succès :</h1><p>' . htmlspecialchars($message, ENT_QUOTES) . '</p>';
    }

    public function error(string $message): void
    {
        echo '<h1>Erreur :</h1><p>' . htmlspecialchars($message, ENT_QUOTES) . '</p>';
    }

    /**
     * Réponse JSON générique.
     *
     * @param mixed $data Données à renvoyer
     */
    public function jsonResponse($data, int $status = 200): void
    {
        $this->response->json((array) $data, $status);
    }

    public function jsonSuccess(string $message, array $extra = [], int $status = 200): void
    {
        $payload = array_merge(['success' => true, 'message' => $message], $extra);
        $this->jsonResponse($payload, $status);
    }

    public function jsonError(string $message, int $status = 400, array $extra = []): void
    {
        $payload = array_merge(['success' => false, 'error' => $message], $extra);
        $this->jsonResponse($payload, $status);
    }
}
