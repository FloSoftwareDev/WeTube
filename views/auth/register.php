<?php include VIEWS_PATH . '/layouts/header.php'; ?>

<h1>Register</h1>

<?php if ($error): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" action="/WeTube/public/register">
    <label>Username
        <input type="text" name="username" required value="<?= htmlspecialchars($old['username'] ?? '') ?>">
    </label>
    <label>Email
        <input type="email" name="email" required value="<?= htmlspecialchars($old['email'] ?? '') ?>">
    </label>
    <label>Password (8+ characters)
        <input type="password" name="password" required minlength="8">
    </label>
    <button type="submit">Create account</button>
</form>

<p>Already registered? <a href="/WeTube/public/login">Log in</a></p>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>