<?php
namespace Controllers;

use Core\Response;
use Core\Request;
use Models\DataModel;

class Controller {
    protected DataModel $db;
    protected Response $response;
    protected Request $request;

    public function __construct(?DataModel $db = null, ?Response $response = null, ?Request $request = null) {
        $this->db = $db ?? new DataModel();
        $this->response = $response ?? new Response();
        $this->request = $request ?? new Request();
    }

    public function index(...$args) {
        // page d'accueil, affichage le view index.php(ou index.tsx)
        // Les classes enfants peuvent override avec leurs propres signatures
    }

    public function render($view, $data = []) {
        extract($data, EXTR_SKIP);
        $viewFile = __DIR__ . "/../views/" . $view . ".php";
        if (!file_exists($viewFile)) {
            throw new \Exception("Vue non trouvée : " . $viewFile);
        }
        include $viewFile;
    }

    public function redirection($url) {
        if (empty($url)) return;
        if (!headers_sent()) {
            header("Location: $url"); 
        } else {
            echo "<script>window.location.href='" . htmlspecialchars($url, ENT_QUOTES) . "';</script>";
        }
        exit;
    }

    public function isConnected() {
        return $this->db->getPDO() instanceof \PDO;
    }

    public function success($message) {
            echo "<h1>Succès : </h1><p>" . htmlspecialchars($message, ENT_QUOTES) . "</p>";
        }

    public function error($message) {
            echo "<h1>Erreur : </h1><p>" . htmlspecialchars($message, ENT_QUOTES) . "</p>";
        }

        /**
         * Réponse JSON générique
         *
         * @param mixed $data Données à renvoyer
         * @param int $status Code HTTP
         * @return void
         */
        public function jsonResponse($data, int $status = 200)
        {
            $this->response->json((array) $data, $status);
        }

        /**
         * Réponse JSON pour succès simple
         */
        public function jsonSuccess(string $message, array $extra = [], int $status = 200)
        {
            $payload = array_merge(['success' => true, 'message' => $message], $extra);
            $this->jsonResponse($payload, $status);
        }

        /**
         * Réponse JSON pour erreur
         */
        public function jsonError(string $message, int $status = 400, array $extra = [])
        {
            $payload = array_merge(['success' => false, 'error' => $message], $extra);
            $this->jsonResponse($payload, $status);
        }

}
