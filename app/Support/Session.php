<?php
declare(strict_types=1);

namespace App\Support;

final class Session {

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
            session_start();
        }
        if (!isset($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(16));
        }
    }

    public static function id(): ?int
    {
        return $_SESSION['uid'] ?? null;
    }

    public static function login(int $uid): void
    {
        $_SESSION['uid'] = $uid;
    }

    public static function logout(): void
    {
        session_destroy();
    }

    public static function csrf(): string
    {
        return $_SESSION['_csrf'];
    }

    public static function checkCsrf(?string $token): bool
    {
        return is_string($token) && hash_equals($_SESSION['_csrf'] ?? '', $token);
    }
}