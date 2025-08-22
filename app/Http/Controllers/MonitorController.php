<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\MonitorRepository;
use App\Support\Session;

final class MonitorController
{
    private function requireAuth(): ?int
    {
        $uid = Session::id();
        if (!$uid) {
            header("Location: /login");
            return null;
        }
        return $uid;
    }

    public function createForm(): void
    {
        if (!$this->requireAuth()) return;
        $csrf = Session::csrf();
        echo <<<HTML
             <h2>Новый монитор</h2>
             <form action="/monitors" method="post">
               <input type="hidden" name="_csrf" value="$csrf">
               <label>Name: <input name="name" required></label><br>
               <label>Type:
                 <select name="type">
                   <option value="HTTP">HTTP</option>
                   <option value="PING">PING</option>
                   <option value="TCP">TCP</option>
                 </select>
               </label><br>
               <label>Target (URL/host:port): <input name="target" required></label><br>
               <label>Interval, sec: <input name="interval_sec" type="number" min="10" value="60"></label><br>
               <label>Timeout, ms: <input name="timeout_ms" type="number" min="100" value="5000"></label><br>
               <button type="submit">Create</button>
            </form>
            HTML;
    }

    public function store(): void
    {
        $uid = $this->requireAuth();
        if (!$uid) return;
        if (!Session::checkCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(400);
            echo 'Bad CSRF';
            return;
        }

        $name = trim(((string)$_POST['name'] ?? ''));
        $type = (string)($_POST['type'] ?? '');
        $target = trim(((string)$_POST['target'] ?? ''));
        $interval = max(10, (int)($_POST['interval_sec'] ?? 60));
        $timeout = max(100, (int)($_POST['timeout_ms'] ?? 5000));

        if ($type === 'HTTP' && !filter_var($target, FILTER_VALIDATE_URL)) {
            echo 'Wrong URL';
        }

        if ($name === '' || $target === '') {
            echo 'Fill the data';
            return;
        }

        $id = MonitorRepository::create($uid, $name, $type, $target, $interval, $timeout);
        header('Location: /');
        exit;
    }

    public function index(): void
    {
        $uid = $this->requireAuth();
        if (!$uid) return;
        $rows = MonitorRepository::allWithLastStatus($uid);

        echo '<h2>Мои мониторы</h2>';
        echo '<p><a href="/monitors/new">Создать монитор</a></p>';
        echo '<table border="1" cellpadding="6" cellspacing="0">';
        echo '<tr><th>Название</th><th>Тип</th><th>Цель</th><th>Статус</th><th>RT</th><th>HTTP</th><th>Проверен</th></tr>';
        foreach ($rows as $r) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($r['name']) . '</td>';
            echo '<td>' . htmlspecialchars($r['type']) . '</td>';
            echo '<td>' . htmlspecialchars($r['target']) . '</td>';
            echo '<td>' . htmlspecialchars($r['last_status'] ?? '-') . '</td>';
            echo '<td>' . htmlspecialchars((string)($r['last_rt'] ?? '-')) . '</td>';
            echo '<td>' . htmlspecialchars((string)($r['last_http'] ?? '-')) . '</td>';
            echo '<td>' . htmlspecialchars((string)($r['last_checked'] ?? '-')) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}