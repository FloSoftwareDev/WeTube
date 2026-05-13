<?php
$_flashModal = $_SESSION['flash_modal'] ?? null;
$_flashError = $_SESSION['flash_error'] ?? null;
$_flashOld   = $_SESSION['flash_old']   ?? [];
unset($_SESSION['flash_modal'], $_SESSION['flash_error'], $_SESSION['flash_old']);

$_autoOpen = $_flashModal
    ?? (in_array($_GET['modal'] ?? '', ['login', 'register'], true) ? $_GET['modal'] : null);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>WeTube</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&family=Orbitron:wght@700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/WeTube/public/css/style.css">
</head>
<body>

<div class="ambient-blob-1"></div>
<div class="ambient-blob-2"></div>

<nav class="wt-nav">
    <a href="/WeTube/public/" class="wt-logo">WeTube</a>

    <form method="GET" action="/WeTube/public/search" class="wt-search">
        <input type="search" name="q" placeholder="Search videos"
               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
               class="wt-search__input">
        <button type="submit" class="wt-search__btn">Search</button>
    </form>

    <div class="wt-nav__actions">
        <?php if (AuthService::check()): ?>
            <a href="/WeTube/public/upload" class="wt-nav__link wt-nav__link--primary">+ Upload</a>
            <a href="/WeTube/public/profile" class="wt-nav__username">
                <div class="wt-nav__avatar">
                    <?= htmlspecialchars(mb_strtoupper(mb_substr($_SESSION['username'], 0, 1))) ?>
                </div>
                <?= htmlspecialchars($_SESSION['username']) ?>
            </a>
            <form method="POST" action="/WeTube/public/logout" class="wt-nav__logout">
                <button type="submit">Logout</button>
            </form>
        <?php else: ?>
            <a href="/WeTube/public/login"    onclick="openModal('login');return false;"    class="wt-nav__link">Login</a>
            <a href="/WeTube/public/register" onclick="openModal('register');return false;" class="wt-nav__link">Register</a>
        <?php endif; ?>
    </div>
</nav>

<!-- Login modal -->
<div id="login-modal" class="modal-overlay" onclick="if(event.target===this)closeModal('login')">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('login')">&times;</button>
        <h2>Log in</h2>
        <?php if ($_flashModal === 'login' && $_flashError): ?>
            <p class="modal-error"><?= htmlspecialchars($_flashError) ?></p>
        <?php endif; ?>
        <form method="POST" action="/WeTube/public/login">
            <p><label>Email or username
                <input type="text" name="identifier" required autocomplete="username">
            </label></p>
            <p><label>Password
                <input type="password" name="password" required autocomplete="current-password">
            </label></p>
            <button type="submit">Log in</button>
        </form>
        <p class="modal-switch">No account?
            <a href="#" onclick="closeModal('login');openModal('register');return false;">Register</a>
        </p>
    </div>
</div>

<!-- Register modal -->
<div id="register-modal" class="modal-overlay" onclick="if(event.target===this)closeModal('register')">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('register')">&times;</button>
        <h2>Create account</h2>
        <?php if ($_flashModal === 'register' && $_flashError): ?>
            <p class="modal-error"><?= htmlspecialchars($_flashError) ?></p>
        <?php endif; ?>
        <form method="POST" action="/WeTube/public/register">
            <p><label>Username
                <input type="text" name="username" required autocomplete="username"
                       value="<?= htmlspecialchars($_flashOld['username'] ?? '') ?>">
            </label></p>
            <p><label>Email
                <input type="email" name="email" required autocomplete="email"
                       value="<?= htmlspecialchars($_flashOld['email'] ?? '') ?>">
            </label></p>
            <p><label>Password (8+ characters)
                <input type="password" name="password" required minlength="8" autocomplete="new-password">
            </label></p>
            <p><label>Confirm password
                <input type="password" name="confirm_password" required minlength="8" autocomplete="new-password">
            </label></p>
            <button type="submit">Create account</button>
        </form>
        <p class="modal-switch">Already registered?
            <a href="#" onclick="closeModal('register');openModal('login');return false;">Log in</a>
        </p>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id + '-modal').classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id + '-modal').classList.remove('active');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(function(m) {
            m.classList.remove('active');
        });
        document.body.style.overflow = '';
    }
});
<?php if ($_autoOpen): ?>
openModal('<?= $_autoOpen ?>');
<?php endif; ?>
</script>

<main>
