<?php
/** @var array<int, array<string, mixed>> $rows */
?>

<h2>My monitors</h2>
<p><a href="/monitors/new">Create</a></p>

<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>Name</th><th>Type</th><th>Target</th>
        <th>Status</th><th>RT</th><th>HTTP</th><th>Checked</th>
    </tr>
    <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= htmlspecialchars($r['name']) ?></td>
            <td><?= htmlspecialchars($r['type']) ?></td>
            <td><?= htmlspecialchars($r['target']) ?></td>
            <td><?= htmlspecialchars($r['last_status'] ?? '-') ?></td>
            <td><?= htmlspecialchars((string)($r['last_rt'] ?? '-')) ?></td>
            <td><?= htmlspecialchars((string)($r['last_http'] ?? '-')) ?></td>
            <td><?= htmlspecialchars((string)($r['last_checked'] ?? '-')) ?></td>
        </tr>
    <?php endforeach; ?>
</table>
