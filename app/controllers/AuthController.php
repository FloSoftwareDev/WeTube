<?php

/**
 * AuthController — handles the login, register and logout pages.
 * The real work happens in AuthService — this controller just
 * reads the form, calls the service, and redirects the user.
 */
class AuthController
{
    // /login (GET) — open the homepage with the login modal showing
    public function showLogin()
    {
        if (AuthService::check()) {
            header('Location: /WeTube/public/');
            exit;
        }
        header('Location: /WeTube/public/?modal=login');
        exit;
    }

    // /login (POST) — handle the submitted login form
    public function login()
    {
        $identifier = isset($_POST['identifier']) ? $_POST['identifier'] : '';
        $password   = isset($_POST['password']) ? $_POST['password'] : '';

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

    // /register (GET) — open the homepage with the register modal showing
    public function showRegister()
    {
        if (AuthService::check()) {
            header('Location: /WeTube/public/');
            exit;
        }
        header('Location: /WeTube/public/?modal=register');
        exit;
    }

    // /register (POST) — handle the submitted register form
    public function register()
    {
        $username        = isset($_POST['username']) ? $_POST['username'] : '';
        $email           = isset($_POST['email']) ? $_POST['email'] : '';
        $password        = isset($_POST['password']) ? $_POST['password'] : '';
        $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

        try {
            AuthService::register([
                'username'         => $username,
                'email'            => $email,
                'password'         => $password,
                'confirm_password' => $confirmPassword,
            ]);
        } catch (RuntimeException $e) {
            // Validation failed — show the modal again with the error message
            $_SESSION['flash_error'] = $e->getMessage();
            $_SESSION['flash_modal'] = 'register';
            $_SESSION['flash_old']   = ['username' => $username, 'email' => $email];
            header('Location: /WeTube/public/');
            exit;
        }

        // Account created -> log them in straight away
        AuthService::login($email, $password);
        header('Location: /WeTube/public/');
        exit;
    }

    // /logout (POST) — clear the session
    public function logout()
    {
        AuthService::logout();
        header('Location: /WeTube/public/');
        exit;
    }
}
