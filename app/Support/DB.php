<?php
declare(strict_types=1);

namespace App\Support;

use PDO;

final class DB
{
    private static ?PDO $pdo = null;

    private static function init(string $dsn, string $user, string $pass): void
    {
        self::$pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_general_ci",
        ]);
    }

    public static function run(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function exec(string $sql): void {
        self::pdo()->exec($sql);
    }

    public static function pdo(): PDO
    {
        if (!self::$pdo) {
            self::init(
                Env::get('DB_DSN', ''),
                Env::get('DB_USER', ''),
                Env::get('DB_PASS', '')
            );
        }
        return self::$pdo;
    }
}