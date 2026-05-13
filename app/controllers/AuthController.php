<?php

/**
 * AuthController — login, register, logout HTTP handlers.
 */
class AuthController
{
    public function showLogin(): void
    {
        // If already logged in, just send them home
        if (AuthService::check()) {
            header('Location: /WeTube/public/');
            exit;
        }
        $error = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_error']);

        include VIEWS_PATH . '/auth/login.php';
    }

    public function login(): void
    {
        $identifier = $_POST['identifier'] ?? '';
        $password   = $_POST['password']   ?? '';

        $user = AuthService::login($identifier, $password);

        if ($user === null) {
            $_SESSION['flash_error'] = 'Invalid login credentials.';
            header('Location: /WeTube/public/login');
            exit;
        }

        header('Location: /WeTube/public/');
        exit;
    }

    public function showRegister(): void
    {
        if (AuthService::check()) {
            header('Location: /WeTube/public/');
            exit;
        }
        $error = $_SESSION['flash_error'] ?? null;
        $old   = $_SESSION['flash_old']   ?? [];
        unset($_SESSION['flash_error'], $_SESSION['flash_old']);

        include VIEWS_PATH . '/auth/register.php';
    }

    public function register(): void
    {
        try {
            AuthService::register([
                'username' => $_POST['username'] ?? '',
                'email'    => $_POST['email']    ?? '',
                'password' => $_POST['password'] ?? '',
            ]);
        } catch (RuntimeException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $_SESSION['flash_old']   = [
                'username' => $_POST['username'] ?? '',
                'email'    => $_POST['email']    ?? '',
            ];
            header('Location: /WeTube/public/register');
            exit;
        }

        // Auto-login after registration
        AuthService::login($_POST['email'], $_POST['password']);

        header('Location: /WeTube/public/');
        exit;
    }

    public function logout(): void
    {
        AuthService::logout();
        header('Location: /WeTube/public/');
        exit;
    }
}