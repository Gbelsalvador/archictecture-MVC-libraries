<?php

namespace App\Models;

use App\Core\AuthService;
use App\Core\Response;

class UserModel
{
    protected DataModel $db;

    public function __construct(?DataModel $db = null)
    {
        $this->db = $db ?? new DataModel();
    }

    /**
     * Vérifie et décode un token JWT (HS256).
     * Retourne le payload en tableau associatif si valide, sinon null.
     */
    public function verifyJWT(string $token): ?array
    {
        $authService = new AuthService(new Response());
        return $authService->verifyToken($token);
    }
}
