<?php

/**
 * AuthService — handles registration, login, logout, and session state.
 *
 * Session keys used:
 *   $_SESSION['user_id']
 *   $_SESSION['username']
 *   $_SESSION['role']
 */
class AuthService
{
    /**
     * Register a new user. Returns the User on success, throws on validation failure.
     *
     * @param array $data ['username' => ..., 'email' => ..., 'password' => ...]
     * @throws RuntimeException with a user-friendly message
     */
    public static function register(array $data): User {

        $username        = trim($data['username'] ?? '');
        $email           = trim($data['email'] ?? '');
        $password        = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';

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

        $user = new User();
        $user->username = $username;
        $user->email   = $email;
        $user->role     = User::ROLE_USER;
        $user->setPassword($password);
        $user->save();

        return $user;
    }
    /**
     * Try to log in with email-or-username + password.
     * Returns the User on success, null on failure (so you can show "wrong credentials").
     */
    public static function login(string $identifier, string $password): ?User
    {
        $identifier = trim($identifier);

        // Auto-detect: if it looks like an email, search by email; else by username
        $user = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? User::findByEmail($identifier)
            : User::findByUsername($identifier);

        if ($user === null || !$user->verifyPassword($password)) {
            return null;
        }

        self::startSessionFor($user);
        return $user;
    }

    /**
     * Log the current user out. Clears the session entirely.
     */
    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'],
                      $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    /**
     * Quick boolean: is someone logged in right now?
     */
    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Get the current user's ID, or null if logged out.
     */
    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get the current user's role, or null if logged out.
     */
    public static function role(): ?int
    {
        $r = $_SESSION['role'] ?? null;
        return $r !== null ? (int) $r : null;
    }

    /**
     * Get the current User object (full DB lookup). Null if logged out.
     */
    public static function currentUser(): ?User
    {
        $id = self::id();
        return $id === null ? null : User::findById($id);
    }

    /**
     * Set the session variables for a freshly-authenticated user.
     */
    private static function startSessionFor(User $user): void
    {
        // Regenerate the session ID on login to prevent session fixation
        session_regenerate_id(true);

        $_SESSION['user_id']  = $user->userId;
        $_SESSION['username'] = $user->username;
        $_SESSION['role']     = $user->role;
    }
}