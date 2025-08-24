<?php if (!empty($error)): ?>
    <div style="color:red"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<h2>Registration</h2>
<form method="post" action="/register">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
    <label>Email: <input name="email" type="email" required></label><br>
    <label>Password: <input name="password" type="password" required minlength="6"></label><br>
    <button type="submit">Register</button>
</form>
<p>Already have account? <a href="/login">Enter</a></p>
