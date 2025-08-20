<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Http\Router;
use App\Support\Env;
use App\Support\DB;
use App\Support\Session;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MonitorController;

Env::load(__DIR__ . '/../.env');
Session::start();
DB::pdo();

$router = new Router();

$router->get('/', function () {
    $uid = Session::id();
    echo '<h1>PulseWatch</h1>';
    if ($uid) {
        echo '<p>You are logged in. <a href="/monitors/new">Create monitor</a> | <a href="/logout">Logout</a></p>';
    } else {
        echo '<p><a href="/login">Enter</a> or <a href="/register">Registrate</a></p>';
    }
    echo '<p><a href="/healthz">/healthz</a></p>';
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

$auth = new AuthController();
$router->get('/register', [$auth, 'showRegister']);
$router->post('/register', [$auth, 'register']);
$router->get('/login', [$auth, 'showLogin']);
$router->post('/login', [$auth, 'login']);
$router->get('/logout', [$auth, 'logout']);

$monik = new MonitorController();
$router->get('/monitors/new', [$monik, 'createForm']);
$router->post('/monitors', [$monik, 'store']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
