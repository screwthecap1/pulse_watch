<?php
/** @var array $monitor */
/** @var float $uptime */
$title  = htmlspecialchars($monitor['name']);
$target = htmlspecialchars($monitor['target']);
$type   = htmlspecialchars($monitor['type']);
$id     = (int)$monitor['id'];
?>
<h2><?= $title ?></h2>
<p>Type: <b><?= $type ?></b> • Target: <code><?= $target ?></code></p>
<p>Uptime (24h): <b><?= $uptime ?>%</b></p>
<p><a href="/monitors">← Back to list</a></p>

<canvas id="rtChart" width="900" height="300"></canvas>

<p class="muted" style="margin:8px 0">Last checks:</p>
<table style="width:100%;border-collapse:collapse">
    <thead>
    <tr>
        <th>Time</th><th>Status</th><th>RT, ms</th><th>HTTP</th><th>Msg</th>
    </tr>
    </thead>
    <tbody id="tbody"></tbody>
</table>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (async () => {
        const res  = await fetch('/api/monitor/results?id=<?= $id ?>&minutes=1440&limit=200');
        const data = await res.json();

        // table
        const tbody = document.getElementById('tbody');
        tbody.innerHTML = data.map(r => `
    <tr>
      <td>${r.checked_at}</td>
      <td>${r.status}</td>
      <td>${r.response_time_ms ?? '-'}</td>
      <td>${r.http_code ?? '-'}</td>
      <td>${(r.message || '').replace(/[<>]/g,'')}</td>
    </tr>
  `).join('');

        // chart
        const labels = data.map(r => r.checked_at).reverse();
        const rts    = data.map(r => r.response_time_ms ?? null).reverse();

        new Chart(document.getElementById('rtChart'), {
            type: 'line',
            data: { labels, datasets: [{ label: 'Response time (ms)', data: rts, spanGaps: true, cubicInterpolationMode: 'monotone' }] },
            options: { animation: false, scales: { y: { beginAtZero: true } } }
        });
    })();
</script>
