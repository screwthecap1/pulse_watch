<?php
/** @var array $m */
/** @var string $csrf */
?>
<h2>Edit monitor</h2>
<form action="/monitors/<?= (int)$m['id'] ?>" method="post">
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

  <label>Name: <input name="name" required value="<?= htmlspecialchars($m['name']) ?>"></label><br>
  <label>Type:
    <select name="type">
      <option value="HTTP" <?= $m['type']==='HTTP'?'selected':'' ?>>HTTP</option>
      <option value="PING" <?= $m['type']==='PING'?'selected':'' ?>>PING</option>
      <option value="TCP"  <?= $m['type']==='TCP' ?'selected':'' ?>>TCP</option>
    </select>
  </label><br>
  <label>Target (URL/host:port):
    <input name="target" required value="<?= htmlspecialchars($m['target']) ?>">
  </label><br>
  <label>Interval, sec:
    <input name="interval_sec" type="number" min="10" value="<?= (int)$m['interval_sec'] ?>">
  </label><br>
  <label>Timeout, ms:
    <input name="timeout_ms" type="number" min="100" value="<?= (int)$m['timeout_ms'] ?>">
  </label><br>
  <label><input type="checkbox" name="is_active" <?= (int)$m['is_active'] ? 'checked' : '' ?>> Active</label><br>

  <button type="submit">Save</button>
  <a href="/monitors" style="margin-left:8px">Cancel</a>
</form>