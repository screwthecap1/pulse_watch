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

    public static function allWithLastStatus(int $userId): array
    {
        return DB::run(
        /** @lang SQL */
            "SELECT m.*,
                    r.status AS last_status,
                    r.response_time_ms AS last_rt,
                    r.http_code AS last_http,
                    r.checked_at AS last_checked
                FROM monitors m
                LEFT JOIN (
                    SELECT t.*
                    FROM monitor_results t
                    JOIN (
                        SELECT monitor_id, MAX(checked_at) as mx
                        FROM monitor_results
                        GROUP BY monitor_id
                    ) x ON x.monitor_id = t.monitor_id AND x.mx = t.checked_at
                ) r ON r.monitor_id = m.id
                WHERE m.user_id = ?
                ORDER BY m.id DESC 
            ", [$userId]
        )->fetchAll();
    }
}
