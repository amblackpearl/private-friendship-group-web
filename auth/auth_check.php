<?php
/**
 * Authentication Guard
 * 
 * Include this file at the top of every protected page.
 * Checks if the user has an active session with a valid user_id.
 * Redirects unauthenticated users to the login page.
 * 
 * Usage: require_once __DIR__ . '/../auth/auth_check.php';
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit;
}
