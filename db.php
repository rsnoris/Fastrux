<?php
/**
 * db.php — MySQL PDO connection
 *
 * Configuration via environment variables (or fallback defaults):
 *   DB_HOST  — MySQL hostname  (default: localhost)
 *   DB_PORT  — MySQL port      (default: 3306)
 *   DB_NAME  — Database name   (default: fastrux)
 *   DB_USER  — Username        (default: root)
 *   DB_PASS  — Password        (default: empty string)
 */

function getDb(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $host = getenv('DB_HOST') ?: 'localhost';
    $port = getenv('DB_PORT') ?: '3306';
    $name = getenv('DB_NAME') ?: 'fastrux';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';

    // Validate host (alphanumeric, dots, hyphens, underscores only)
    if (!preg_match('/^[A-Za-z0-9.\-_]+$/', $host)) {
        throw new \InvalidArgumentException('DB_HOST contains invalid characters.');
    }
    // Validate port is numeric and in valid range
    $portInt = (int) $port;
    if ($portInt < 1 || $portInt > 65535) {
        throw new \InvalidArgumentException('DB_PORT must be a number between 1 and 65535.');
    }

    $dsn = "mysql:host={$host};port={$portInt};dbname={$name};charset=utf8mb4";

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    return $pdo;
}
