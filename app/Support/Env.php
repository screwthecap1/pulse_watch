<?php
declare(strict_types=1);

namespace App\Support;

final class Env
{
    public static function load(string $path): void
    {
        if (!is_file($path)) return;
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            [$k, $v] = array_map('trim', explode('=', $line, 2));
            $v = preg_replace('/^([\'"])(.*)\1$/', '$2', $v);
            $_ENV[$k] = $_SERVER[$k] = $v;
            putenv("$k=$v");
        }
    }

    public static function get(string $key, ?string $default = null): ?string {
        return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
    }
}