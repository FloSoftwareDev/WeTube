<?php

class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $config = require CONFIG_PATH . '/config.php';
        $db     = $config['db'];

        $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4";

        $this->pdo = new PDO(
            $dsn,
            $db['user'],
            $db['pass'],
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    }

    /**
     * Get the shared Database instance.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Run any SQL with parameters. Returns the prepared statement
     * so you can chain ->fetch() / ->rowCount() / etc. if needed.
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch a single row, or null if nothing matched.
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $row = $this->query($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Fetch every row as an array (empty array if no matches).
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * ID of the last inserted row. Use right after an INSERT.
     */
    public function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }

    // Prevent cloning / unserializing the singleton
    private function __clone() {}
    public function __wakeup() { throw new RuntimeException('Cannot unserialize singleton'); }
}