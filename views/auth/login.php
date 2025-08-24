<?php if (!empty($error)): ?>
    <div style="color:red"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<h2>Enter form</h2>
<form method="post" action="/login">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
    <label>Email: <input name="email" type="email" required></label><br>
    <label>Password: <input name="password" type="password" required></label><br>
    <button type="submit">Enter</button>
</form>
<p>No account yet? <a href="/register">Register</a></p>
