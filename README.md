# MVC Architecture for PHP APIs and Server-Rendered Applications

`architecture-MVC-libraries` is a lightweight PHP MVC foundation designed for small to medium-sized APIs and server-rendered applications.

The project provides:

- a front controller in `public/`
- a routing layer based on AltoRouter
- controller and model base classes
- request, response, and authentication services
- helper utilities for uploads, mail, and security
- a CLI generator named `automat`

The current architecture favors:

- explicit controller dispatch
- centralized JSON responses
- lazy database access
- environment-driven configuration
- a clear separation between HTTP concerns and business logic

## Requirements

- PHP 8.0 or higher
- Composer
- A local or remote MySQL-compatible database

## Getting Started

```bash
composer install
composer dump-autoload
cp .env.example .env
```

Update `.env` with your runtime configuration:

- `DB_HOST`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`
- `JWT_SECRET`
- `CORS_ALLOWED_ORIGINS`
- SMTP variables if mail delivery is required

Start the application locally:

```bash
php -S localhost:8000 -t public
```

## Project Structure

```text
config/       Environment and application configuration
controllers/  Base controller and application controllers
core/         Request, response, auth, config, and bootstrapping services
models/       Data access classes and domain models
public/       HTTP entry point
router/       AltoRouter wrapper and controller dispatcher
routes/       Route declarations grouped by HTTP verb
tests/        Lightweight test runner and architecture checks
utils/        Reusable helpers (security, uploads, mail)
views/        PHP templates for server-rendered pages
automat       CLI generator for models and controllers
```

## Request Lifecycle

The runtime flow is intentionally simple and explicit:

1. `public/index.php` loads Composer, environment variables, and core services.
2. `Core\Core` configures request handling, CORS, authentication helpers, and route loading.
3. `routes/*.php` register route definitions with `Router\Router`.
4. `Router\Router` resolves the incoming request and dispatches the matched controller action.
5. Controllers read input through `Core\Request`, access persistence through models, and send output through `Core\Response`.

This keeps the HTTP pipeline understandable while remaining extensible for future middleware or service container work.

## Routing

Routes are declared by HTTP verb in the `routes/` directory and now use controller targets directly:

```php
Router\Router::get('/api', [Controllers\ApiController::class, 'index']);
Router\Router::post('/api/articles', [Controllers\ArticleController::class, 'apiStore']);
Router\Router::put('/api/articles/[i:id]', [Controllers\ArticleController::class, 'apiUpdate']);
Router\Router::delete('/api/articles/[i:id]', [Controllers\ArticleController::class, 'apiDestroy']);
```

This pattern is preferred over route closures for application endpoints because it is:

- easier to test
- more consistent
- easier to document
- better aligned with controller-based frameworks

## Controllers

Application controllers extend `Controllers\Controller`.

The base controller exposes:

- `render()` for PHP views
- `jsonResponse()`, `jsonSuccess()`, `jsonError()` for API responses
- `redirection()` for redirects
- injected `DataModel`, `Response`, and `Request` dependencies

Example:

```php
<?php

namespace Controllers;

class ArticleController extends Controller
{
    public function apiShow(array $params = []): void
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            $this->jsonError('ID non fourni', 400);
            return;
        }

        $model = new \Models\ArticleModel($this->db);
        $article = $model->findById((int) $id);

        if (!$article) {
            $this->jsonError('Article non trouvé', 404);
            return;
        }

        $this->jsonResponse([
            'success' => true,
            'data' => $article,
        ]);
    }
}
```

## Request and Response Services

The `core/` layer centralizes HTTP behavior:

- `Core\Request` reads JSON payloads, headers, request method, URI, and uploaded files
- `Core\Response` standardizes JSON output and status handling
- `Core\AuthService` validates bearer tokens
- `Core\AppConfig` reads runtime configuration from environment variables

This makes controllers thinner and avoids repeating low-level HTTP logic throughout the codebase.

## Database Access

`Models\DataModel` manages the PDO connection.

The connection is now lazy:

- the application no longer opens a database connection during bootstrap
- the connection is created only when `getPDO()` is first called

This allows non-database endpoints to remain functional even if the database is temporarily unavailable.

## Authentication

JWT validation is handled by `Core\AuthService`.

The current implementation:

- validates the token structure
- enforces `HS256`
- verifies the signature
- checks time-based claims such as `exp`, `nbf`, and `iat`
- refuses insecure fallback secrets

To require authentication in an endpoint, use:

```php
$userId = $core->requireAuthenticatedUserId();
```

or call:

```php
$userId = $core->getArtisteIdFromToken();
```

## File Uploads

`Utils\UploadHandler` provides a basic but safer upload workflow:

- optional MIME and extension allowlists
- file size limitation
- random file naming
- validation with `is_uploaded_file()`
- public-facing relative path in the returned payload

Example:

```php
use Utils\UploadHandler;

$uploader = new UploadHandler(
    __DIR__ . '/../uploads',
    5_000_000,
    ['image/jpeg', 'image/png'],
    ['jpg', 'jpeg', 'png']
);

$result = $uploader->handle('file');
```

## Security Utilities

`Utils\Security` contains helper methods for:

- input sanitation
- HTML escaping
- password hashing and verification
- CSRF token generation and validation
- session-based rate limiting

Recommended usage:

- sanitize input when normalizing user data
- escape output when rendering HTML
- do not store HTML-escaped data in the database

## CORS Configuration

CORS is environment-driven through `CORS_ALLOWED_ORIGINS`.

Example:

```env
CORS_ALLOWED_ORIGINS=http://localhost:8000,http://localhost:3000
```

In non-production environments, the application can fall back to permissive behavior when no explicit origin is configured. In production, define allowed origins explicitly.

## Code Generation with `automat`

`automat` is a CLI helper that scaffolds models and controllers:

```bash
php automat list
php automat create:model Article
php automat create:controller ArticleController
```

Generated controllers include:

- classic MVC actions such as `index`, `show`, `create`, `store`, `edit`, `update`, `destroy`
- API actions such as `apiIndex`, `apiShow`, `apiStore`, `apiUpdate`, `apiDestroy`
- JSON-aware request reading through the injected `Request` object

After generating a controller, `automat` also prints route examples using the controller-target style introduced in this architecture.

## Testing

A lightweight test runner is available:

```bash
php tests/run.php
```

The current test suite covers:

- request JSON decoding
- JWT validation behavior
- controller dispatch through the router

## Operational Notes

- Do not commit real secrets to the repository.
- Keep `composer.json` and `composer.lock` versioned together.
- Do not edit `vendor/` manually.
- Restrict `CORS_ALLOWED_ORIGINS` in production.
- Use strong, non-default JWT secrets.
- Validate upload types and sizes server-side even if the client already does.

## Roadmap

Natural next steps for this codebase are:

- introduce a dedicated dependency container
- add a richer automated test suite
- formalize error handling for HTML responses
- add middleware support for authentication and rate limiting

## License

This project is licensed under the Apache License 2.0. See [LICENSE](/c:/wamp64/www/architecture-MVC-libraries/LICENSE).
