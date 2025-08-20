<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Support\Env;
use App\Support\DB;

Env::load(__DIR__ . '/../.env');
DB::pdo();

$email = $argv[1] ?? null;
$pass = $argv[2] ?? null;

if (!$email || !$pass) {
    echo "Usage: php bin/seed_admin.php <email> <password>";
    exit(1);
}

$exists = DB::run('SELECT 1 FROM users WHERE email=?', [$email])->fetchColumn();
if ($exists) {
    echo "User with email $email already exists\n";
    exit(0);
}

$hash = password_hash($pass, PASSWORD_DEFAULT);
DB::run('INSERT INTO users(email,password_hash,role) VALUES (?,?,?)', [$email, $hash, 'admin']);
echo "Admin successfully created:\n $email | $pass \n";