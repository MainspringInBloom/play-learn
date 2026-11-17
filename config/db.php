<?php
/**
 * Database connection (PDO).
 * TODO: set DB_PASS to your local MySQL root password before running.
 */

const DB_HOST = '127.0.0.1';
const DB_NAME = 'play_and_learn';
const DB_USER = 'root';
const DB_PASS = 'REPLACE_WITH_YOUR_MYSQL_PASSWORD';
const DB_CHARSET = 'utf8mb4';

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // In production this should log, not echo. Fine for local dev/demo.
    die('Database connection failed: ' . $e->getMessage());
}
