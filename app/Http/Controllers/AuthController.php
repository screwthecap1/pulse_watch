<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use App\Support\Session;

final class AuthController
{
    public function showRegister(): void
    {
        $csrf = Session::csrf();
        echo <<<HTML
                <h2>Registration</h2>
                <form action="/register" method="post">
                    <input type="hidden" name="_csrf" value="$csrf">
                    <label>Email: <input name="email" type="email" required></label>
                    <label>Password: <input name="password" type="password" required minlength="6"></label>
                    <button type="submit">Register</button>
                </form>
                <p>Already have the account? <a href="/login">Enter</a></p>
HTML;
    }

    public function register(): void
    {
        if (!Session::checkCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(400);
            echo 'Bad CSRF';
            return;
        }
        $email = trim((string)($_POST['email'] ?? ''));
        $pass = trim((string)($_POST['password'] ?? ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6) {
            echo 'Wrong data!';
            return;
        }
        if (UserRepository::findByEmail($email)) {
            echo 'Email is already taken! Use another one!';
            return;
        }

        $uid = UserRepository::create($email, $pass);
        Session::login($uid);
        header('Location: /');
        exit;
    }

    public function showLogin(): void
    {
        $csrf = Session::csrf();
        echo <<<HTML
                <h2>Enter Form</h2>
                <form action="/login" method="post">
                    <input type="hidden" name="_csrf" value="$csrf">
                    <label>Email: <input name="email" type="email" required></label>
                    <label>Password: <input name="password" type="password" required></label>
                    <button type="submit">Enter</button>
                </form>
                <p>No account? <a href="/register">Registration</a></p>
HTML;
    }

    public function login(): void
    {
        if (!Session::checkCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(400);
            echo 'Bad CSRF';
            return;
        }
        $email = trim((string)($_POST['email'] ?? ''));
        $pass = trim((string)($_POST['password'] ?? ''));

        $user = UserRepository::findByEmail($email);
        if (!$user || !password_verify($pass, $user['password_hash'])) {
            echo "Wrong email/password!";
            return;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        Session::login((int)$user['id']);
        header('Location: /');
        exit;
    }

    public function logout(): void
    {
        Session::logout();
        header('Location: /login');
        exit;
    }
}