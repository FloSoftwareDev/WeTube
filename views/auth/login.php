<?php include VIEWS_PATH . '/layouts/header.php'; ?>

<h1>Log in</h1>

<?php if ($error): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" action="/WeTube/public/login">
    <label>Email or username
        <input type="text" name="identifier" required>
    </label>
    <label>Password
        <input type="password" name="password" required>
    </label>
    <button type="submit">Log in</button>
</form>

<p>No account? <a href="/WeTube/public/register">Register</a></p>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>