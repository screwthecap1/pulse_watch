<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\MonitorRepository;
use App\Support\Session;

final class ApiController
{
    public function monitorResults(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $uid = Session::id();
        if (!$uid) {
            http_response_code(401);
            echo json_encode(['error' => 'Unathorized']);
            return;
        }

        $monitorId = (int)($_GET['id'] ?? 0);
        $minutes = (int)($_GET['minutes'] ?? 0);
        $limit = (int)($_GET['limit'] ?? 0);

        $rows = MonitorRepository::results($uid, $monitorId, $limit, $minutes);
        echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
