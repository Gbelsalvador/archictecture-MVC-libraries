<?php
namespace Controllers;
require_once __DIR__ . '/../models/DataModel.php';

use \DataModel;
class Controller {
    protected $db;

    public function __construct() {
        $this->db = new DataModel();
    }

    public function index(...$args) {
        // page d'accueil, affichage le view index.php(ou index.tsx)
        // Les classes enfants peuvent override avec leurs propres signatures
    }

    public function render($view, $data = []) {
        extract($data);
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
        return $this->db instanceof \PDO;
    }
}
