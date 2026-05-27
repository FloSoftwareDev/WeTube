<?php

/**
 * User model — one object = one row in the `users` table.
 *
 * Use the static finders (findById / findByEmail / findByUsername) to load
 * an existing user from the database, or do `new User()` + ->save() to
 * create a brand new one.
 */
class User
{
    const ROLE_USER  = 1;
    const ROLE_ADMIN = 2;

    public $userId         = null;
    public $username       = '';
    public $email          = '';
    public $passwordHash   = '';
    public $role           = 1;   // 1 = normal user, 2 = admin
    public $profilePicture = null;
    public $bio            = null;
    public $createdAt      = null;

    // Look up a user by their ID.
    public static function findById($id)
    {
        $row = Database::fetchOne('SELECT * FROM users WHERE user_id = ?', [$id]);
        return self::fromRow($row);
    }

    // Look up a user by email. Used during login.
    public static function findByEmail($email)
    {
        $row = Database::fetchOne('SELECT * FROM users WHERE email = ?', [$email]);
        return self::fromRow($row);
    }

    // Look up a user by username. Also used during login.
    public static function findByUsername($username)
    {
        $row = Database::fetchOne('SELECT * FROM users WHERE username = ?', [$username]);
        return self::fromRow($row);
    }

    // Save this user. If they don't have an ID yet -> INSERT a new row,
    // otherwise UPDATE the existing one.
    public function save()
    {
        if ($this->userId === null) {
            Database::query(
                'INSERT INTO users (username, email, password_hash, role, profile_picture)
                 VALUES (?, ?, ?, ?, ?)',
                [$this->username, $this->email, $this->passwordHash, $this->role, $this->profilePicture]
            );
            $this->userId = Database::lastInsertId();
        } else {
            Database::query(
                'UPDATE users
                 SET username = ?, email = ?, role = ?, profile_picture = ?, bio = ?
                 WHERE user_id = ?',
                [$this->username, $this->email, $this->role, $this->profilePicture, $this->bio, $this->userId]
            );
        }
    }

    // Hash the password before storing it. Call this before ->save().
    public function setPassword($plain)
    {
        $this->passwordHash = password_hash($plain, PASSWORD_DEFAULT);
    }

    // Check a typed-in password against the stored hash.
    public function verifyPassword($plain)
    {
        return password_verify($plain, $this->passwordHash);
    }

    // Turn a database row (array) into a User object.
    // Returns null if the row was null (nothing found).
    private static function fromRow($row)
    {
        if ($row === null) {
            return null;
        }

        $user = new User();
        $user->userId         = (int) $row['user_id'];
        $user->username       = $row['username'];
        $user->email          = $row['email'];
        $user->passwordHash   = $row['password_hash'];
        $user->role           = (int) $row['role'];
        $user->profilePicture = $row['profile_picture'];
        $user->bio            = isset($row['bio']) ? $row['bio'] : null;
        $user->createdAt      = $row['created_at'];
        return $user;
    }
}
