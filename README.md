# Architecture MVC - Guide d'utilisation

Ce dépôt fournit une architecture minimale MVC pour construire une API/mini-application PHP.

Prérequis
- PHP 8+
- Composer
- Serveur web local (ex: PHP built-in, Apache)

Installation rapide

```bash
composer install
composer dump-autoload
cp .env.example .env
# éditer .env pour renseigner DB_*, JWT_SECRET, MAIL_FROM, et SMTP_* si nécessaire
php -S localhost:8000 -t public
```

Vue d'ensemble des dossiers
- `public/` : point d'entrée (`public/index.php`)
- `core/` : classes utilitaires (ex: `Core\Core`)
- `router/` : wrapper pour AltoRouter (`Router\Router`)
- `controllers/` : contrôleurs (ex: `Controller`, `ApiController`)
- `models/` : modèles (ex: `Models\DataModel`, `Models\UserModel`)
- `views/` : vues PHP
- `utils/` : utilitaires (Mailer, Security, UploadHandler)

Créer un nouveau Model

1. Placer le fichier dans `models/` et utiliser le namespace `Models`.
2. Hériter ou utiliser `Models\DataModel` pour accéder à la base :

Exemple minimal :

```php
<?php
namespace Models;

class ArticleModel
{
	protected DataModel $db;

	public function __construct(DataModel $db = null)
	{
		$this->db = $db ?? new DataModel();
	}

	public function findAll(): array
	{
		$pdo = $this->db->getPDO();
		$stmt = $pdo->query('SELECT * FROM articles');
		return $stmt->fetchAll();
	}
}
```

Créer un nouveau Controller

1. Placer le fichier dans `controllers/` et utiliser le namespace `Controllers`.
2. Étendre `Controllers\Controller` pour bénéficier de `render()`, `redirection()` et `$this->db`.

Exemple minimal :

```php
<?php
namespace Controllers;

class ArticleController extends Controller
{
	public function index(...$args)
	{
		$model = new \Models\ArticleModel($this->db);
		$articles = $model->findAll();
		$this->render('articles/index', ['articles' => $articles]);
	}
}
```

Créer une View

Placer un fichier PHP dans `views/`, par ex. `views/articles/index.php` et utiliser les variables passées via `render()`.

Utiliser les routes

- Déclarez vos routes dans `routes/get.php`, `routes/post.php`, etc. Exemple pour AltoRouter :

```php
Router\Router::get('/articles', function() use ($core) {
	$controller = new Controllers\ArticleController();
	$controller->index();
});
```

Utilisation du token (JWT)

- Le projet contient `Models\UserModel::verifyJWT($token)` qui vérifie un token HS256 utilisant la clé `JWT_SECRET` dans `.env`.
- Pour récupérer l'ID utilisateur depuis un header Bearer, utilisez `Core\Core::getArtisteIdFromToken()` ou `requireAuthenticatedUserId()`.

Génération d'un token (exemple simple côté serveur lors de l'authentification) :

```php
$payload = ['user_id' => $userId, 'exp' => time() + 3600];
$header = base64_encode(json_encode(['alg'=>'HS256','typ'=>'JWT']));
$body = base64_encode(json_encode($payload));
$sig = hash_hmac('sha256', "$header.$body", $_ENV['JWT_SECRET'] ?? 'dev_secret', true);
$token = rtrim(strtr(base64_encode($header), '+/', '-_'), '=') . '.' .
		 rtrim(strtr(base64_encode($body), '+/', '-_'), '=') . '.' .
		 rtrim(strtr(base64_encode($sig), '+/', '-_'), '=');
```

Mailer (`Utils\Mailer`)

- Utilisez `Utils\Mailer::send($to, $subject, $body, $options)`.
- Supporte PHPMailer (si installé via Composer) et retombe sur `mail()` sinon.
- Config via `.env`: `MAIL_FROM`, `SMTP_HOST`, `SMTP_USER`, `SMTP_PASS`, `SMTP_PORT`, `SMTP_SECURE`.

Exemple d'envoi simple :

```php
use Utils\Mailer;
Mailer::send('user@example.com', 'Test', '<b>Bonjour</b>', ['is_html' => true]);
```

Upload de fichiers (`Utils\UploadHandler`)

- Instancier `UploadHandler` et appeler `handle('field_name')` sur une requête `multipart/form-data`.
- Exemple :

```php
$u = new Utils\UploadHandler(__DIR__ . '/../uploads', 5_000_000, ['image/jpeg','image/png'], ['jpg','jpeg','png']);
$res = $u->handle('file');
if ($res['success']) { /* fichier en $res['path'] */ }
```

Sécurité (`Utils\Security`)

- `Security::sanitize($input)` : nettoie les entrées utilisateur.
- `Security::hashPassword()` / `Security::verifyPassword()` : gestion sécurisée des mots de passe.
- `Security::generateCsrfToken()` / `verifyCsrfToken()` : tokens CSRF stockés en session.
- `Security::rateLimit()` : simple limiteur de requêtes en session (pour usage léger).

Exemples courts :

```php
use Utils\Security;
$safe = Security::sanitize($_POST['name'] ?? '');
$hash = Security::hashPassword('secret');
Security::verifyPassword('secret', $hash);
```

Bonnes pratiques

- Ne stockez jamais `JWT_SECRET` ou mots de passe en clair dans le dépôt. Utilisez `.env` et variables d'environnement.
- Validez et limitez les types/taille d'upload côté serveur.
- Activez TLS pour SMTP (`SMTP_SECURE` = tls/ssl) en production.
- Versionnez `composer.json` et `composer.lock` ensemble et utilisez `composer require` pour ajouter des dépendances.

Support & tests

- Pour tester les routes, démarrez le serveur et utilisez `curl` ou Postman.
- Exemple :

```bash
curl http://localhost:8000/api
curl -X POST -H "Content-Type: application/json" -d '{"foo":"bar"}' http://localhost:8000/api/echo
```

Souhaitez-vous que je :
- ajoute un contrôleur d'exemple CRUD complet ?
- crée un petit script d'envoi d'e-mails de test et un endpoint pour l'upload ?
