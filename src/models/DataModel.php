<?php

namespace App\Models;

class DataModel
{
    protected ?\PDO $pdo = null;

    public function getPDO(): ?\PDO
    {
        if (!$this->pdo instanceof \PDO) {
            $this->connect();
        }

        return $this->pdo;
    }

    public function connect(): void
    {
        if ($this->pdo instanceof \PDO) {
            return;
        }

        $configPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';
        $config = require $configPath;

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $config['DB_HOST'],
            $config['DB_NAME'],
            $config['DB_CHARSET']
        );

        try {
            $this->pdo = new \PDO($dsn, $config['DB_USER'], $config['DB_PASS'], [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
        } catch (\PDOException $e) {
            throw new \RuntimeException('Erreur de connexion à la base de données : ' . $e->getMessage(), 0, $e);
        }
    }
}

?>
