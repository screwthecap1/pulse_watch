<?php
/** @var string      $csrf */
/** @var null|string $error */
?>

<?php if (!empty($error)): ?>
    <div style="color:red"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<h2>New monitor</h2>
<form action="/monitors" method="post">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
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
<p><a href="/monitors">Back to list</a></p>
