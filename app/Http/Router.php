<?php
declare(strict_types=1);

namespace App\Http;

final class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->map('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->map('POST', $path, $handler);
    }

    private function map(string $method, string $path, callable $handler): void
    {
        $regex = '#^' . preg_replace('/\{[^}]+\}/', '([^/]+)', rtrim($path, '/')) . '$#';
        $this->routes[$method][] = ['regex' => $regex, 'handler' => $handler];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = rtrim(parse_url($uri, PHP_URL_PATH) ?? '/', '/');

        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['regex'], $path, $matches)) {
                array_shift($matches);
                $matches = array_map(function ($m) {
                    return ctype_digit($m) ? (int)$m : $m;
                }, $matches);

                $handler = $route['handler'];
                $handler(...$matches);
                return;
            }
        }

        http_response_code(404);
        echo '<h1>404</h1>';
    }
}
