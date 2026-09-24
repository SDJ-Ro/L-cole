<?php
/**
 * =========================================================================
 * L'ÉCOLE — DATABASE CONNECTION SINGLETON
 * =========================================================================
 * Thread-safe, single-instance PDO MySQL connection manager.
 * - Reads configuration dynamically from config/database.php.
 * - Enforces PDO prepared statements natively (SQL injection defense).
 * - Disallows cloning and serialization to protect connection integrity.
 * =========================================================================
 */

class Database {

    private static ?PDO $instance = null;

    /**
     * Private constructor to prevent direct instantiation from outside.
     */
    private function __construct() {}

    /**
     * Prevent object cloning.
     */
    private function __clone() {}

    /**
     * Prevent deserialization.
     */
    public function __wakeup() {
        throw new \Exception("Cannot deserialize a database connection singleton.");
    }

    /**
     * Returns the active PDO connection instance.
     * Initializes connection on first call (Lazy Loading).
     *
     * @return PDO
     * @throws PDOException if connection fails
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../config/database.php';

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options'] ?? []
                );
            } catch (PDOException $e) {
                // In production, write to internal log; output clean message for now
                error_log("Database Connection Error: " . $e->getMessage());
                die("Critical System Error: Database unavailable. Please try again shortly.");
            }
        }

        return self::$instance;
    }
}
