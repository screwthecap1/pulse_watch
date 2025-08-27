<?php
/** @var array<int, array<string, mixed>> $rows */
use App\Support\Session;
?>
<h2>My monitors</h2>
<p><a href="/monitors/new">Create</a></p>

<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>Name</th><th>Type</th><th>Target</th>
        <th>Status</th><th>RT</th><th>HTTP</th><th>Checked</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($rows as $r): ?>
        <tr>
            <td>
                <a href="/monitors/<?= (int)$r['id'] ?>">
                    <?= htmlspecialchars($r['name']) ?>
                </a>
            </td>
            <td><?= htmlspecialchars($r['type']) ?></td>
            <td><?= htmlspecialchars($r['target']) ?></td>
            <td><?= htmlspecialchars($r['last_status'] ?? '-') ?></td>
            <td><?= htmlspecialchars((string)($r['last_rt'] ?? '-')) ?></td>
            <td><?= htmlspecialchars((string)($r['last_http'] ?? '-')) ?></td>
            <td><?= htmlspecialchars((string)($r['last_checked'] ?? '-')) ?></td>
            <td>
                <form action="/monitors/<?= (int)$r['id'] ?>/toggle" method="post" style="display:inline;margin:0">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Session::csrf()) ?>">
                    <button type="submit"><?= (int)$r['is_active'] ? 'Pause' : 'Resume' ?></button>
                </form>

                <a href="/monitors/<?= (int)$r['id'] ?>/edit" style="margin-left:6px">Edit</a>

                <form action="/monitors/<?= (int)$r['id'] ?>/del" method="post"
                      style="display:inline;margin:0" onsubmit="return confirm('Delete monitor?')">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Session::csrf()) ?>">
                    <button type="submit" style="margin-left:6px">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
