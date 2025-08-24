<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use App\Support\Session;
use App\Support\View;

final class AuthController
{
    public function showRegister(): void
    {
        View::render('auth/register', ['csrf' => Session::csrf()]);
    }

    public function register(): void
    {
        if (!Session::checkCsrf($_POST['_csrf'] ?? null)) { http_response_code(400); echo 'Bad CSRF'; return; }

        $email = trim((string)($_POST['email'] ?? ''));
        $pass  = trim((string)($_POST['password'] ?? ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6) {
            View::render('auth/register', ['csrf' => Session::csrf(), 'error' => 'Wrong data']); return;
        }
        if (UserRepository::findByEmail($email)) {
            View::render('auth/register', ['csrf' => Session::csrf(), 'error' => 'Email already taken']); return;
        }

        $uid = UserRepository::create($email, $pass);
        session_regenerate_id(true);
        Session::login($uid);
        header('Location: /'); exit;
    }

    public function showLogin(): void
    {
        View::render('auth/login', ['csrf' => Session::csrf()]);
    }

    public function login(): void
    {
        if (!Session::checkCsrf($_POST['_csrf'] ?? null)) { http_response_code(400); echo 'Bad CSRF'; return; }

        $email = trim((string)($_POST['email'] ?? ''));
        $pass  = trim((string)($_POST['password'] ?? ''));

        $user = UserRepository::findByEmail($email);
        if (!$user || !password_verify($pass, $user['password_hash'])) {
            View::render('auth/login', ['csrf' => Session::csrf(), 'error' => 'Wrong email or password']); return;
        }

        session_regenerate_id(true);
        Session::login((int)$user['id']);
        header('Location: /'); exit;
    }

    public function logout(): void
    {
        Session::logout();
        header('Location: /login');
        exit;
    }
}