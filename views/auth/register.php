<?php include VIEWS_PATH . '/layouts/header.php'; ?>

<div class="wt-auth-page">
    <h1>Register</h1>

    <?php if ($error): ?>
        <p class="wt-auth-error"><?= htmlspecialchars($error) ?></p>
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
        <label>Confirm Password
            <input type="password" name="confirm_password" required minlength="8">
        </label>
        <button type="submit">Create account</button>
    </form>

    <p>Already registered? <a href="/WeTube/public/login">Log in</a></p>
</div>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
