<?php

// Load environment variables from .env file
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Skip comments
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, "'\"");
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
        }
    }
}

class Database {
    private static $host;
    private static $dbName;
    private static $username;
    private static $password;

    private static function getEnvVar($key, $default = '') {
        return $_ENV[$key] ?? $default;
    }

    private static function init() {
        self::$host = self::getEnvVar('DB_HOST', 'localhost');
        self::$dbName = self::getEnvVar('DB_NAME', 'melodex2');
        self::$username = self::getEnvVar('DB_USER', 'root');
        self::$password = self::getEnvVar('DB_PASS', '');
    }

    private static $connection = null;

    public static function connect() {
        if (self::$connection === null) {
            self::init();
            try {
                self::$connection = new PDO(
                    "mysql:host=" . self::$host . ";dbname=" . self::$dbName,
                    self::$username,
                    self::$password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }
        return self::$connection;
    }
}
?>