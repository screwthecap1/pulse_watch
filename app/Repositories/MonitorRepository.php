<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Support\DB;

final class MonitorRepository
{
    public static function create(int $userId, string $name, string $type, string $target, int $intervalSec = 60, int $timeoutMs = 5000): int
    {
        DB::run('INSERT INTO monitors(user_id,name,type,target,interval_sec,timeout_ms) VALUES (?,?,?,?,?,?)',
            [$userId, $name, $type, $target, $intervalSec, $timeoutMs]);
        return (int)DB::pdo()->lastInsertId();
    }
}
