<?php

/**
 * AuthService — everything to do with logging users in and out.
 *
 * After a successful login we put three things in the session:
 *   $_SESSION['user_id']
 *   $_SESSION['username']
 *   $_SESSION['role']
 */
class AuthService
{
    // Create a new user account. Returns the User object on success.
    // Throws a RuntimeException with a nice message if something is wrong.
    public static function register($data)
    {
        $username        = isset($data['username']) ? trim($data['username']) : '';
        $email           = isset($data['email']) ? trim($data['email']) : '';
        $password        = isset($data['password']) ? $data['password'] : '';
        $confirmPassword = isset($data['confirm_password']) ? $data['confirm_password'] : '';

        // ---- Validate ----
        if ($username === '') {
            throw new RuntimeException('Username is required.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Please enter a valid email address.');
        }
        if (strlen($password) < 8) {
            throw new RuntimeException('Password must be at least 8 characters.');
        }
        if ($password !== $confirmPassword) {
            throw new RuntimeException('Passwords do not match.');
        }
        if (User::findByUsername($username) !== null) {
            throw new RuntimeException('That username is already taken.');
        }
        if (User::findByEmail($email) !== null) {
            throw new RuntimeException('An account with that email already exists.');
        }

        // ---- Create the user ----
        $user = new User();
        $user->username = $username;
        $user->email    = $email;
        $user->role     = User::ROLE_USER;
        $user->setPassword($password);
        $user->save();

        return $user;
    }

    // Try to log in with either an email or a username, plus a password.
    // Returns the User on success, or null if the credentials are wrong.
    public static function login($identifier, $password)
    {
        $identifier = trim($identifier);

        // If the input looks like an email, search by email; otherwise by username
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $user = User::findByEmail($identifier);
        } else {
            $user = User::findByUsername($identifier);
        }

        if ($user === null) {
            return null;
        }
        if (!$user->verifyPassword($password)) {
            return null;
        }

        // Login OK -> store the user in the session
        session_regenerate_id(true);  // new session id for safety
        $_SESSION['user_id']  = $user->userId;
        $_SESSION['username'] = $user->username;
        $_SESSION['role']     = $user->role;

        return $user;
    }

    // Log the current user out and clear the session.
    public static function logout()
    {
        $_SESSION = [];
        session_destroy();
    }

    // Is somebody logged in right now? (true/false)
    public static function check()
    {
        return isset($_SESSION['user_id']);
    }

    // Get the role of the logged-in user, or null if logged out.
    public static function role()
    {
        if (!isset($_SESSION['role'])) {
            return null;
        }
        return (int) $_SESSION['role'];
    }
}
