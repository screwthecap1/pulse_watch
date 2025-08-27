<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\MonitorRepository;
use App\Support\Session;
use App\Support\View;

final class MonitorController
{
    private function requireAuth(): ?int
    {
        $uid = Session::id();
        if (!$uid) { header('Location: /login'); return null; }
        return $uid;
    }

    public function createForm(): void
    {
        if (!$this->requireAuth()) return;
        View::render('monitors/new', ['csrf' => Session::csrf()]);
    }

    public function store(): void
    {
        $uid = $this->requireAuth(); if (!$uid) return;

        if (!Session::checkCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(400);
            View::render('monitors/new', ['csrf'=>Session::csrf(), 'error'=>'Bad CSRF']);
            return;
        }

        $name     = trim((string)($_POST['name'] ?? ''));
        $type     = (string)($_POST['type'] ?? 'HTTP');
        $target   = trim((string)($_POST['target'] ?? ''));
        $interval = max(10, (int)($_POST['interval_sec'] ?? 60));
        $timeout  = max(100, (int)($_POST['timeout_ms'] ?? 5000));

        if ($type === 'HTTP' && !filter_var($target, FILTER_VALIDATE_URL)) {
            View::render('monitors/new', ['csrf'=>Session::csrf(), 'error'=>'Wrong URL']); return;
        }
        if ($name === '' || $target === '') {
            View::render('monitors/new', ['csrf'=>Session::csrf(), 'error'=>'Fill the data']); return;
        }

        MonitorRepository::create($uid, $name, $type, $target, $interval, $timeout);
        header('Location: /monitors'); exit;
    }

    public function index(): void
    {
        $uid = $this->requireAuth(); if (!$uid) return;
        $rows = MonitorRepository::allWithLastStatus($uid);
        View::render('monitors/index', ['rows' => $rows]);
    }

    public function show(int $id): void
    {
        $uid = $this->requireAuth();
        if (!$uid) return;

        $monitor = MonitorRepository::findOwned($uid, $id);
        if (!$monitor) { http_response_code(404); echo "Not found"; return; }

        $uptime = MonitorRepository::uptimePercent($uid, $id);
        View::render('monitors/show', [
            'monitor' => $monitor,
            'uptime'  => $uptime,
        ]);
    }

    public function editForm(int $id): void
    {
        $uid = $this->requireAuth(); if (!$uid) return;
        $m = MonitorRepository::findOwned($uid, $id);
        if (!$m) { http_response_code(404); echo 'Not found'; return; }
        View::render('monitors/edit', ['csrf' => Session::csrf(), 'm' => $m]);
    }

    public function update(int $id): void
    {
        $uid = $this->requireAuth(); if(!$uid) return;
        if (!Session::checkCsrf($_POST['_csrf'] ?? null)) { http_response_code(400); echo 'Bad CSRF'; return; }

        $name = trim((string)($_POST['name'] ?? ''));
        $type = (string)($_POST['type'] ?? 'HTTP');
        $target = trim((string)$_POST['target'] ?? '');
        $interval = max(10, (int)$_POST['interval_sec'] ?? 60);
        $timeout = max(100, (int)($_POST['timeout_ms'] ?? 5000));
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($type==='HTTP' && !filter_var($target, FILTER_VALIDATE_URL)) { echo 'Wrong URL'; return; }
        if ($name==='' || $target==='') { echo 'Fill the data'; return; }

        MonitorRepository::updateOwned($uid, $id, [
            'name' => $name,
            'type' => $type,
            'target' => $target,
            'interval_sec' => $interval,
            'timeout_ms' => $timeout,
            'is_active' => $isActive,
        ]);
        header('Location: /monitors'); exit;
    }

    public function destroy(int $id): void
    {
        $uid = $this->requireAuth(); if(!$uid) return;
        if (!Session::checkCsrf($_POST['_csrf'] ?? null)) { http_response_code(400); echo 'Bad CSRF'; return; }
        MonitorRepository::deleteOwned($uid, $id);
        header('Location: /monitors'); exit;
    }

    public function toggle(int $id): void
    {
        $uid = $this->requireAuth(); if(!$uid) return;
        if (!Session::checkCsrf($_POST['_csrf'] ?? null)) { http_response_code(400); echo 'Bad CSRF'; return; }
        MonitorRepository::toggleActive($uid, $id);
        header('Location: /monitors'); exit;
    }
}


