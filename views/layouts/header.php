<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>WeTube</title>
</head>
<body>
<nav>
    <a href="/WeTube/public/">WeTube</a>
    <?php if (AuthService::check()): ?>
        Logged in as <?= htmlspecialchars($_SESSION['username']) ?>
        <form method="POST" action="/WeTube/public/logout" style="display:inline">
            <button type="submit">Logout</button>
        </form>
    <?php else: ?>
        <a href="/WeTube/public/login">Login</a>
        <a href="/WeTube/public/register">Register</a>
    <?php endif; ?>
</nav>
<main>