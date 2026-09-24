<?php
/**
 * =========================================================================
 * L'ÉCOLE — DATABASE CONFIGURATION
 * =========================================================================
 * Central connection settings for MySQL.
 * Environment-aware with zero hardcoded credentials in core classes.
 * =========================================================================
 */

$isDocker = (gethostbyname('db') !== 'db');

return [
    'host'     => getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? ($isDocker ? 'db' : '127.0.0.1')),
    'database' => getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'l_ecole'),
    'username' => getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'root'),
    'password' => getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? 'root'),
    'port'     => (int)(getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? ($isDocker ? 3306 : 3307))),
    'charset'  => 'utf8mb4',

    // Recommended PDO security & performance options
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false, // Enforces true native prepared statements (SQL injection defense)
    ]
];
