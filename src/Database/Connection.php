<?php

namespace App\Database;

use PDO;
use PDOException;

/**
 * Database Connection Class
 * Singleton pattern for database connections
 */
class Connection
{
    private static ?PDO $instance = null;

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
        // Prevent instantiation
    }

    /**
     * Get PDO instance (singleton)
     *
     * @return PDO
     * @throws PDOException
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::createConnection();
        }

        return self::$instance;
    }

    /**
     * Create new PDO connection
     *
     * @return PDO
     * @throws PDOException
     */
    private static function createConnection(): PDO
    {
        $config = config('database');

        $dsn = sprintf(
            "mysql:host=%s;dbname=%s;charset=%s",
            $config['host'],
            $config['database'],
            $config['charset']
        );

        try {
            $pdo = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );

            // Set additional attributes
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            return $pdo;
        } catch (PDOException $e) {
            // Log error
            error_log("Database Connection Error: " . $e->getMessage());
            throw new PDOException("Could not connect to database");
        }
    }

    /**
     * Reset connection (useful for testing)
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
