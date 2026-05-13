<?php

/**
 * User model.
 *
 * Represents a row in the `users` table. Use the static finders
 * to load existing users, or `new User()` + ->save() to create one.
 */
class User
{
    const ROLE_USER  = 1;
    const ROLE_ADMIN = 2;

    public ?int      $userId         = null;
    public string    $username       = '';
    public string    $email          = '';
    private string   $passwordHash   = '';
    public int       $role           = self::ROLE_USER; // 1 = user, 2 = admin
    public ?string   $profilePicture = null;
    public ?string   $bio            = null;
    public ?string   $createdAt      = null;

    /**
     * Find a user by their primary key.
     */
    public static function findById(int $id): ?User
    {
        $row = Database::getInstance()->fetchOne(
            'SELECT * FROM users WHERE user_id = ?',
            [$id]
        );
        return $row ? self::hydrate($row) : null;
    }

    /**
     * Find a user by email. Useful for login.
     */
    public static function findByEmail(string $email): ?User
    {
        $row = Database::getInstance()->fetchOne(
            'SELECT * FROM users WHERE email = ?',
            [$email]
        );
        return $row ? self::hydrate($row) : null;
    }

    /**
     * Find a user by username. Used by login when the input isn't an email.
     */
    public static function findByUsername(string $username): ?User
    {
        $row = Database::getInstance()->fetchOne(
            'SELECT * FROM users WHERE username = ?',
            [$username]
        );
        return $row ? self::hydrate($row) : null;
    }

    /**
     * Insert a new user, or update the existing one.
     * Returns true on success.
     */
    public function save(): bool
    {
        $db = Database::getInstance();

        if ($this->userId === null) {
            // INSERT
            $db->query(
                'INSERT INTO users (username, email, password_hash, role, profile_picture)
                 VALUES (?, ?, ?, ?, ?)',
                [
                    $this->username,
                    $this->email,
                    $this->passwordHash,
                    $this->role,
                    $this->profilePicture,
                ]
            );
            $this->userId = $db->lastInsertId();
        } else {
            // UPDATE
            $db->query(
                'UPDATE users
                 SET username = ?, email = ?, role = ?, profile_picture = ?, bio = ?
                 WHERE user_id = ?',
                [
                    $this->username,
                    $this->email,
                    $this->role,
                    $this->profilePicture,
                    $this->bio,
                    $this->userId,
                ]
            );
        }
        return true;
    }

    /**
     * Hash and set the user's password. Call before ->save() on registration.
     */
    public function setPassword(string $plain): void
    {
        $this->passwordHash = password_hash($plain, PASSWORD_DEFAULT);
    }

    /**
     * Check a plaintext password against the stored hash.
     */
    public function verifyPassword(string $plain): bool
    {
        return password_verify($plain, $this->passwordHash);
    }

    /**
     * Build a User object from a DB row.
     */
    private static function hydrate(array $row): User
    {
        $user = new User();
        $user->userId         = (int) $row['user_id'];
        $user->username       = $row['username'];
        $user->email          = $row['email'];
        $user->passwordHash   = $row['password_hash'];
        $user->role           = (int) $row['role'];
        $user->profilePicture = $row['profile_picture'];
        $user->bio            = $row['bio'] ?? null;
        $user->createdAt      = $row['created_at'];
        return $user;
    }
}