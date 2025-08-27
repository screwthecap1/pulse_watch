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

    public static function findOwned(int $userId, int $monitorId): ?array
    {
        $row = DB::run('SELECT * FROM monitors WHERE id=? and user_id=?', [$monitorId, $userId])->fetch();
        return $row ?: null;
    }

    public static function results(
        int $userId,
        int $monitorId,
        int $limit = 200,
        ?int $sinceMinutes = null
    ): array {
        $params = [$monitorId];
        $where = 'monitor_id = ?';
        if ($sinceMinutes !== null) {
            $where .= 'AND checked_at >= (NOW() - INTERVAL ? MINUTE)';
            $params[] = $sinceMinutes;
        }
        $sql = "SELECT checked_at, status, response_time_ms, http_code, message
        FROM monitor_results
        WHERE {$where}
        ORDER BY checked_at DESC
        LIMIT " . max(1, $limit);

        $owned = DB::run(
            'SELECT 1 FROM monitors WHERE id=? AND user_id=?',
            [$monitorId, $userId]
        )->fetchColumn();
        if (!$owned) return [];

        return DB::run($sql, $params)->fetchAll();
    }

    public static function uptimePercent(int $userId, int $monitorId, int $sinceMinutes = 1440): float
    {
        $rows = self::results($userId, $monitorId, 10000, $sinceMinutes);
        if (!$rows) return 0.0;

        $ok = 0;
        foreach ($rows as $r) {
            if (($r['status'] ?? '') === 'OK') $ok++;
        }
        return round($ok / count($rows) * 100, 2);
    }

    public static function deleteOwned(int $userId, int $monitorId): bool
    {
        $r = DB::run("DELETE FROM monitors WHERE id=? and user_id=?", [$monitorId, $userId]);
        return $r->rowCount() > 0;
    }

    public static function updateOwned(int $userId, int $monitorId, array $fields): bool
    {
        if (!$fields) return false;
        $cols = [];
        $vals = [];
        foreach ($fields as $k => $v) {
            $cols[] = "{$k} = ?";
            $vals[] = $v;
        }
        $vals[] = $monitorId;
        $vals[] = $userId;
        $sql = 'UPDATE monitors SET '.implode(', ', $cols).' WHERE id=? AND user_id=?';
        $r = DB::run($sql, $vals);
        return $r->rowCount() > 0;
    }

    public static function toggleActive(int $userId, int $monitorId): bool
    {
        $row = self::findOwned($userId, $monitorId);
        if (!$row) return false;
        $new = (int)!((int)$row['is_active']);
        return self::updateOwned($userId, $monitorId, ['is_active' => $new]);
    }
}
