<?php

class AuthController
{
    public function showLogin(): void
    {
        if (AuthService::check()) {
            header('Location: /WeTube/public/');
            exit;
        }
        header('Location: /WeTube/public/?modal=login');
        exit;
    }

    public function login(): void
    {
        $identifier = $_POST['identifier'] ?? '';
        $password   = $_POST['password']   ?? '';

        $user = AuthService::login($identifier, $password);

        if ($user === null) {
            $_SESSION['flash_error'] = 'Invalid login credentials.';
            $_SESSION['flash_modal'] = 'login';
            header('Location: /WeTube/public/');
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
        header('Location: /WeTube/public/?modal=register');
        exit;
    }

    public function register(): void
    {
        try {
            AuthService::register([
                'username'         => $_POST['username']         ?? '',
                'email'            => $_POST['email']            ?? '',
                'password'         => $_POST['password']         ?? '',
                'confirm_password' => $_POST['confirm_password'] ?? '',
            ]);
        } catch (RuntimeException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $_SESSION['flash_modal'] = 'register';
            $_SESSION['flash_old']   = [
                'username' => $_POST['username'] ?? '',
                'email'    => $_POST['email']    ?? '',
            ];
            header('Location: /WeTube/public/');
            exit;
        }

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
