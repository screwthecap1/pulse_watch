<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Support\DB;
use App\Support\Env;

Env::load(__DIR__ . '/../.env');
DB::pdo()->exec("CREATE TABLE IF NOT EXISTS migrations(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(190) NOT NULL UNIQUE,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB");

$dir = realpath(__DIR__ . '/../app\Migrations');
$files = glob($dir . '/*.sql');
sort($files);

foreach ($files as $file) {
    $name = basename($file);
    $applied = DB::run('SELECT 1 FROM migrations WHERE filename = ?', [$name])->fetchColumn();
    if ($applied) {
        echo "Skip $name\n";
        continue;
    }

    $sql = file_get_contents($file);
    DB::exec($sql);
    DB::run('INSERT INTO migrations(filename) VALUES (?)', [$name]);
    echo "Apply $name\n";
}

echo "Done.\n";