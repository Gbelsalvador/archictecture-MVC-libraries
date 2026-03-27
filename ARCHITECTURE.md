# Architecture Reference

This document describes the runtime architecture, conventions, and extension points of the project.

It is intended for contributors who need to understand how requests flow through the application and how new features should be implemented.

## 1. Architectural Overview

The application follows a lightweight MVC structure with explicit HTTP dispatch:

- `public/index.php` acts as the front controller
- `router/Router.php` wraps AltoRouter and dispatches controller targets
- `routes/*.php` define routes by HTTP verb
- `controllers/` contains entry points for application and API behavior
- `models/` encapsulates persistence concerns
- `views/` contains server-rendered templates
- `core/` centralizes HTTP and cross-cutting services
- `utils/` contains reusable infrastructure helpers

This architecture aims to keep the framework surface area small while preserving enough structure for maintainable application growth.

## 2. Runtime Flow

### 2.1 Bootstrap

`public/index.php` is responsible for:

- loading Composer autoloading
- loading `.env`
- creating the `Response` and `Core` services
- initializing the router
- registering the controller dispatcher
- loading route files
- matching and dispatching the request

Exceptions thrown during bootstrap or dispatch are converted into JSON error responses.

### 2.2 Route Resolution

`Router\Router` stores the AltoRouter instance and exposes helpers:

- `get()`
- `post()`
- `put()`
- `delete()`
- `origin()`

Routes may point to:

- a closure
- a controller target in the form `[ControllerClass::class, 'method']`

Controller targets are the preferred approach for production code.

### 2.3 Controller Dispatch

The dispatcher configured in `public/index.php` delegates controller instantiation to `Core\Core::makeController()`.

This ensures that each controller receives shared dependencies:

- `Models\DataModel`
- `Core\Response`
- `Core\Request`

The current implementation still uses direct instantiation and is intentionally simple, but it already provides a clear seam for future dependency injection improvements.

## 3. Core Services

### 3.1 `Core\AppConfig`

Provides read access to environment variables and convenience helpers:

- `env()`
- `bool()`
- `environment()`
- `isProduction()`
- `isDebug()`
- `allowedOrigins()`

Use this class whenever runtime behavior depends on environment configuration.

### 3.2 `Core\Request`

Encapsulates incoming HTTP request access.

Responsibilities:

- reading raw body content
- decoding JSON payloads
- reading server variables
- retrieving the Authorization header
- exposing request method and URI
- exposing uploaded files

Controllers should prefer this object over direct access to `php://input` or `$_SERVER`.

### 3.3 `Core\Response`

Standardizes JSON responses.

Responsibilities:

- setting HTTP status codes
- setting JSON content type
- encoding the payload
- optionally terminating the request

This service exists to keep response semantics consistent across controllers and infrastructure code.

### 3.4 `Core\AuthService`

Handles bearer token validation.

Current guarantees:

- validates the JWT structure
- requires `HS256`
- verifies the HMAC signature
- validates temporal claims when present
- refuses missing or placeholder secrets

This service should be extended rather than bypassed if authentication rules evolve.

### 3.5 `Core\Core`

Acts as an orchestration service rather than a god object.

Current responsibilities:

- route loading
- request/response wiring
- CORS setup
- authenticated user extraction
- controller factory logic

It should not absorb model or business logic.

## 4. Controllers

### 4.1 Base Controller

`Controllers\Controller` is the base class for concrete controllers.

It provides:

- `render()` for HTML responses
- `redirection()` for redirects
- `jsonResponse()`, `jsonSuccess()`, `jsonError()` for APIs
- access to `$this->db`
- access to `$this->response`
- access to `$this->request`

### 4.2 Controller Design Rules

When adding a controller:

- keep HTTP orchestration in the controller
- keep SQL and persistence in models
- use `jsonError()` and `jsonResponse()` for API consistency
- read JSON request bodies through `$this->request->json()`
- avoid direct access to globals unless unavoidable

### 4.3 Example Route-to-Controller Mapping

```php
Router\Router::get('/api/articles', [Controllers\ArticleController::class, 'apiIndex']);
Router\Router::post('/api/articles', [Controllers\ArticleController::class, 'apiStore']);
Router\Router::put('/api/articles/[i:id]', [Controllers\ArticleController::class, 'apiUpdate']);
Router\Router::delete('/api/articles/[i:id]', [Controllers\ArticleController::class, 'apiDestroy']);
```

## 5. Models

### 5.1 `Models\DataModel`

Provides a centralized PDO connection.

Important characteristics:

- lazy connection initialization
- environment-driven configuration via `config/config.php`
- exception mode enabled
- associative fetch mode enabled

### 5.2 Domain Models

Domain models belong in `models/` and should:

- use the `Models` namespace
- receive `DataModel` through the constructor when possible
- keep SQL and persistence logic contained
- return arrays or domain-shaped data structures

Avoid placing request parsing, response formatting, or authentication logic in models.

## 6. Views

Views remain simple PHP templates under `views/`.

Convention:

- `views/<resource>/<template>.php`

Use `render('articles/index', [...])` from controllers to include them.

For API-only controllers, do not render views; return JSON instead.

## 7. Utilities

### 7.1 `Utils\Security`

Provides:

- sanitation helpers
- HTML escaping
- password hashing and verification
- CSRF token helpers
- lightweight session rate limiting

Design note:

- sanitize on input normalization
- escape on output rendering

### 7.2 `Utils\UploadHandler`

Provides a basic upload abstraction with:

- target directory creation
- size validation
- MIME and extension allowlists
- server-side upload validation
- randomized file naming

The returned file path is public-facing and relative, not the raw internal filesystem path.

### 7.3 `Utils\Mailer`

Provides a mail abstraction that:

- uses PHPMailer when available
- falls back to `mail()` otherwise
- reads SMTP settings from the environment

## 8. Route Organization

Routes are split by verb:

- `routes/get.php`
- `routes/post.php`
- `routes/put.php`
- `routes/delete.php`

This organization is acceptable for a small codebase.

If the application grows significantly, consider evolving toward:

- domain-based route files
- grouped route registration
- middleware-aware route definitions

## 9. Code Generation with `automat`

`automat` is the built-in scaffolding tool.

### 9.1 Supported Commands

- `php automat list`
- `php automat help`
- `php automat create:model Article`
- `php automat create:controller ArticleController`

### 9.2 Generated Model Capabilities

Generated models include:

- `findAll()`
- `findById()`
- `create()`
- `update()`
- `delete()`

### 9.3 Generated Controller Capabilities

Generated controllers include:

- HTML-oriented actions: `index`, `show`, `create`, `store`, `edit`, `update`, `destroy`
- API-oriented actions: `apiIndex`, `apiShow`, `apiStore`, `apiUpdate`, `apiDestroy`
- request parsing through a shared `requestData()` helper

The generator also prints route examples aligned with the controller-target routing style.

## 10. Security Considerations

### 10.1 JWT

- never deploy with placeholder secrets
- rotate secrets through environment configuration
- validate bearer tokens through `AuthService`

### 10.2 CORS

- configure `CORS_ALLOWED_ORIGINS` explicitly in production
- avoid broad `*` policies for authenticated frontends

### 10.3 Uploads

- enforce extension and MIME allowlists
- keep upload directories out of sensitive paths
- do not trust client-provided filenames

### 10.4 Error Handling

- the front controller converts uncaught exceptions to JSON
- use `APP_DEBUG=false` in production

## 11. Testing Strategy

The repository currently includes a lightweight test runner in `tests/run.php`.

Covered concerns:

- JSON request decoding
- JWT acceptance and rejection behavior
- controller dispatch through the router

Recommended next additions:

- controller behavior tests
- model integration tests
- route coverage for generated CRUD controllers

## 12. Recommended Contribution Rules

When extending the project:

- keep bootstrap logic inside `public/index.php` minimal
- add behavior to services before duplicating logic in controllers
- prefer controller targets over route closures
- keep models focused on persistence
- document new environment variables in `.env.example`
- update `README.md` and this document when architecture changes materially

## 13. Future Improvements

The current architecture is intentionally lightweight, but the most natural next improvements are:

- a proper dependency injection container
- middleware support
- richer exception mapping
- stronger typed DTOs or request validators
- a dedicated test framework configuration

The present structure is already a strong foundation for these upgrades because responsibilities are now better separated than in the earlier version of the project.
