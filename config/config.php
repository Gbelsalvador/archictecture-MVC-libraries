<?php
// Configuration de la base de données
// Les valeurs sont chargées depuis .env, avec des valeurs par défaut en fallback
return [
	 'DB_HOST' => $_ENV['DB_HOST'] ?? 'localhost',
	 'DB_NAME' => $_ENV['DB_NAME'] ?? '',
	 'DB_USER' => $_ENV['DB_USER'] ?? 'root',
	 'DB_PASS' => $_ENV['DB_PASS'] ?? '',
	 'DB_CHARSET' => $_ENV['DB_CHARSET'] ?? 'utf8mb4'
];

$url = $_ENV['BASE_URL'] ?? "";// not necessary for now
