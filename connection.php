<?php

/**
 * Return the shared PDO connection for the application.
 *
 * Database credentials are read from (in order):
 *   1) config.php in the same folder (for InfinityFree/shared hosting),
 *   2) DB_HOST, DB_NAME, DB_USER, DB_PASS environment variables,
 *   3) local XAMPP defaults.
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $fileConfig = [];
    $configPath = __DIR__ . '/config.php';
    if (is_file($configPath)) {
        $loaded = require $configPath;
        if (is_array($loaded)) {
            $fileConfig = $loaded;
        }
    }

    $host = $fileConfig['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1';
    $name = $fileConfig['DB_NAME'] ?? getenv('DB_NAME') ?: 'barangay_document_requests';
    $user = $fileConfig['DB_USER'] ?? getenv('DB_USER') ?: 'root';
    $pass = $fileConfig['DB_PASS'] ?? getenv('DB_PASS') ?: '';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO("mysql:host={$host};dbname={$name};charset=utf8mb4", $user, $pass, $options);
    } catch (PDOException $first) {
        // On InfinityFree/free hosting, CREATE DATABASE is not allowed —
        // the database must already exist (created in the control panel).
        // Only auto-create on local servers (localhost / 127.0.0.1).
        $isLocal = in_array($host, ['localhost', '127.0.0.1', '::1'], true);
        if (!$isLocal || (int) ($first->errorInfo[1] ?? 0) !== 1049) {
            throw new RuntimeException('Database connection failed. Check config.php (DB_HOST, DB_NAME, DB_USER, DB_PASS).');
        }

        $server = new PDO("mysql:host={$host};charset=utf8mb4", $user, $pass, $options);
        $server->exec('CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '``', $name) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $pdo = new PDO("mysql:host={$host};dbname={$name};charset=utf8mb4", $user, $pass, $options);
    }

    $exists = $pdo->query("SHOW TABLES LIKE 'users'")->fetchColumn();
    if (!$exists) {
        $schemaPath = __DIR__ . '/database/schema.sql';
        $sql = file_get_contents($schemaPath);
        $sql = preg_replace('/CREATE DATABASE IF NOT EXISTS.*?;|USE [`]?[^;]+;/', '', $sql);
        $pdo->exec($sql);
    }

    return $pdo;
}
