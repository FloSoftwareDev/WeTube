<?php
/**
 * Database — wraps a single PDO connection so the rest of the app
 * can just call Database::query(...), Database::fetchOne(...) etc.
 *
 * The connection is opened the first time it's needed and reused
 * after that. Settings come from config/config.php.
 */
class Database
{
    private static $pdo = null;

    // Open the connection. Called automatically the first time
    // any of the methods below need the database.
    private static function connect()
    {
        $config = require CONFIG_PATH . '/config.php';
        $db = $config['db'];

        $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4";

        self::$pdo = new PDO($dsn, $db['user'], $db['pass']);
        // Throw an exception when something goes wrong (much easier to spot bugs)
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Return rows as ['column' => value] arrays
        self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        // Send numbers as numbers (needed so "LIMIT ?" works with an int param)
        self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    // Run any SQL with parameters. Returns the prepared statement.
    public static function query($sql, $params = [])
    {
        if (self::$pdo === null) {
            self::connect();
        }
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    // Fetch one row, or null if nothing matched.
    public static function fetchOne($sql, $params = [])
    {
        $row = self::query($sql, $params)->fetch();
        if ($row === false) {
            return null;
        }
        return $row;
    }

    // Fetch every matching row as an array.
    public static function fetchAll($sql, $params = [])
    {
        return self::query($sql, $params)->fetchAll();
    }

    // ID of the row that was just inserted.
    public static function lastInsertId()
    {
        return (int) self::$pdo->lastInsertId();
    }
}
