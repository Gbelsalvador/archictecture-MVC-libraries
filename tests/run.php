<?php

require dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/src/config/env.php';

if (file_exists(dirname(__DIR__) . '/.env')) {
    loadEnv(dirname(__DIR__) . '/.env');
}

final class RouterDispatchProbeController extends App\Controllers\Controller
{
    public static array $captured = [];

    public function capture(array $params = []): void
    {
        self::$captured = $params;
    }
}

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function makeJwt(array $payload, string $secret): string
{
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    $encode = static function (array $data): string {
        return rtrim(strtr(base64_encode(json_encode($data, JSON_UNESCAPED_SLASHES)), '+/', '-_'), '=');
    };

    $header64 = $encode($header);
    $payload64 = $encode($payload);
    $signature = hash_hmac('sha256', $header64 . '.' . $payload64, $secret, true);
    $signature64 = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

    return $header64 . '.' . $payload64 . '.' . $signature64;
}

function testRequestJsonParsesPayload(): void
{
    $request = new App\Core\Request('{"hello":"world"}');
    $decoded = $request->json();

    assertTrue(($decoded['hello'] ?? null) === 'world', 'La requête JSON devrait être décodée.');
}

function testAuthServiceAcceptsValidToken(): void
{
    $_ENV['JWT_SECRET'] = 'test_secret';
    putenv('JWT_SECRET=test_secret');

    $response = new App\Core\Response();
    $auth = new App\Core\AuthService($response);
    $token = makeJwt(['user_id' => 42, 'exp' => time() + 3600], 'test_secret');

    $payload = $auth->verifyToken($token);

    assertTrue(($payload['user_id'] ?? null) === 42, 'Le token valide devrait être accepté.');
}

function testAuthServiceRejectsExpiredToken(): void
{
    $_ENV['JWT_SECRET'] = 'test_secret';
    putenv('JWT_SECRET=test_secret');

    $response = new App\Core\Response();
    $auth = new App\Core\AuthService($response);
    $token = makeJwt(['user_id' => 42, 'exp' => time() - 60], 'test_secret');

    $payload = $auth->verifyToken($token);

    assertTrue($payload === null, 'Le token expiré devrait être refusé.');
}

function testRouterDispatchesControllerArrayTarget(): void
{
    RouterDispatchProbeController::$captured = [];

    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/tests/router/7';

    $router = new AltoRouter();
    new App\Router\Router($router);
    App\Router\Router::setDispatcher(static fn (string $controllerClass) => new $controllerClass());
    App\Router\Router::get('/tests/router/[i:id]', [RouterDispatchProbeController::class, 'capture']);
    App\Router\Router::matcher();

    assertTrue((RouterDispatchProbeController::$captured['id'] ?? null) === '7', 'Le routeur devrait dispatcher une cible contrôleur.');
}

$tests = [
    'request_json' => 'testRequestJsonParsesPayload',
    'auth_valid_token' => 'testAuthServiceAcceptsValidToken',
    'auth_expired_token' => 'testAuthServiceRejectsExpiredToken',
    'router_controller_dispatch' => 'testRouterDispatchesControllerArrayTarget',
];

$failures = [];

foreach ($tests as $name => $test) {
    try {
        $test();
        echo "[OK] {$name}\n";
    } catch (Throwable $exception) {
        $failures[] = "[FAIL] {$name}: " . $exception->getMessage();
    }
}

if ($failures !== []) {
    foreach ($failures as $failure) {
        echo $failure . "\n";
    }
    exit(1);
}

echo "All tests passed.\n";
