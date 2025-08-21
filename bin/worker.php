<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Support\Env;
use App\Support\DB;

Env::load(__DIR__ . '/../.env');
$pdo = DB::pdo();

$sql = <<<SQL
SELECT m.*
FROM monitors m
LEFT JOIN (
    SELECT monitor_id, MAX(checked_at) AS last_checked
    FROM monitor_results
    GROUP BY monitor_id
) r ON r.monitor_id = m.id
WHERE m.is_active = 1
AND (
    r.last_checked IS NULL
    OR TIMESTAMPDIFF(SECOND, r.last_checked, NOW()) >= m.interval_sec
)
ORDER BY m.id ASC 
SQL;

$monitors = $pdo->query($sql)->fetchAll();

foreach ($monitors as $mon) {
    $status = 'UNKNOWN';
    $httpCode = null;
    $timeMs = null;
    $message = null;

    $start = microtime(true);

    try {
        switch ($mon['type']) {
            case 'HTTP':
                [$status, $httpCode, $timeMs, $message] = checkHttp(
                    $mon['target'],
                    (int)$mon['timeout_ms']
                );
                break;
            case 'TCP':
                [$status, $timeMs, $message] = checkTcp(
                    $mon['target'],
                    (int)$mon['timeout_ms']
                );
                break;
            case 'PING':
                [$status, $timeMs, $message] = checkPing(
                    $mon['target'],
                    (int)$mon['timeout_ms']
                );
                break;
            default:
                $status = 'ERROR';
                $message = 'Unsupported monitor type';
        }
    } catch (Throwable $e) {
        $status = 'ERROR';
        $message = $e->getMessage();
    }

    DB::run('INSERT INTO monitor_results(monitor_id, status, response_time_ms, http_code, message) VALUES(?,?,?,?,?)',
        [$mon['id'], $status, $timeMs, $httpCode, $message]);

    $printTime = $timeMs !== null ? "{$timeMs}ms" : "-";
    echo "[{$mon['type']}] {$mon['name']} -> {$status} ({$printTime})";
    if ($httpCode !== null) echo " http={$httpCode}";
    if ($message) echo " :: {$message}";
    echo PHP_EOL;
}

function checkHttp(string $url, int $timeoutMS): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_NOBODY => true,
        CURLOPT_TIMEOUT_MS => max(100, $timeoutMS),
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_USERAGENT => 'PulseWatch/1.0'
    ]);

    $start = microtime(true);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $timeMs = (int)round((microtime(true) - $start) * 1000);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        return ['ERROR', $httpCode ?: null, $timeMs, $err];
    }

    if ($httpCode >= 200 && $httpCode < 400) {
        return ['OK', $httpCode, $timeMs, null];
    }

    return ['FAIL', $httpCode, $timeMs, "Unexpected HTTP code {$httpCode}"];
}

function checkTcp(string $target, int $timeoutMs): array
{
    [$host, $port] = explode(":", $target, 2) + [null, null];
    $host = trim((string)$host);
    $port = (int)($port ?: 80);

    $start = microtime(true);
    $conn = @fsockopen($host, $port, $errno, $errstr, max(0.1, $timeoutMs / 1000));
    $timeMs = (int)round((microtime(true) - $start) * 1000);

    if ($conn) {
        fclose($conn);
        return ['OK', $timeMs, null];
    }
    return ['FAIL', $timeMs, $errstr ?: "Connection failed ({$host}:{$port})"];
}

function checkPing(string $host, int $timeoutMs): array
{
    if (!function_exists('exec')) {
        return ['ERROR', null, 'exec() is disabled; ping is unavailable'];
    }

    $host = escapeshellarg($host);
    $isWindows = stripos(PHP_OS, 'WIN') === 0;

    if ($isWindows) {
        $timeout = max(100, $timeoutMs);
        $cmd = "ping -n 1 -w {$timeout} {$host}";
    } else {
        $sec = max(1, (int)ceil($timeoutMs / 1000));
        $cmd = "ping -c 1 -W {$sec} {$host}";
    }

    $start = microtime(true);
    @exec($cmd, $output, $code);
    $timeMs = (int)round((microtime(true) - $start) * 1000);

    if ($code === 0) {
        return ['OK', $timeMs, null];
    }
    return ['FAIL', $timeMs, 'Ping failed'];
}