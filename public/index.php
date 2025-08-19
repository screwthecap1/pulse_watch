<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Http\Router;
use App\Support\Env;
use App\Support\DB;

Env::load(__DIR__ . '/../.env');

$router = new Router();

$router->get('/', function () {
    echo '<h1>PulseWatch</h1><p>Base is working. Next - migrations and dashboard</p>';
});

$router->get('/healthz', function () {
    try {
        DB::pdo()->query('SELECT 1');
        echo '<pre>DB: OK</pre>';
    } catch (Throwable $e) {
        http_response_code(500);
        echo '<pre>DB error: ' . htmlspecialchars($e->getMessage()) . '</pre>';
    }
});

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
