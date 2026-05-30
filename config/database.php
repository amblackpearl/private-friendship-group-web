<?php
/**
 * Database Connection Configuration
 * 
 * Creates a PDO connection to the MySQL database.
 * Uses utf8mb4 charset for full Unicode support.
 * 
 * Usage: require_once __DIR__ . '/../config/database.php';
 *        Then use $pdo for all database operations.
 */

// Database credentials
$db_host = 'sql213.infinityfree.com';
$db_name = 'if0_42045184_friendship_group_db';
$db_user = 'if0_42045184';
$db_pass = '0f6uAO28lvRJjVf';

// Data Source Name
$dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";

// PDO options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    // In production, log the error and show a generic message
    error_log('Database connection failed: ' . $e->getMessage());
    die('Database connection failed. Please try again later.');
}
