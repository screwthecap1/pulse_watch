<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Support\DB;

final class UserRepository {
    public static function findByEmail(string $email): ?array
    {
        $row = DB::run('SELECT * FROM users WHERE email = ?', [$email])->fetch();
        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $row = DB::run('SELECT * FROM users WHERE id = ?', [$id])->fetch();
        return $row ?: null;
    }

    public static function create(string $email, string $password): int
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        DB::run('INSERT INTO users(email, password_hash) VALUES(?,?)', [$email, $hash]);
        return (int)DB::pdo()->lastInsertId();
    }
}