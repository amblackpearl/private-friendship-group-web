<?php
/**
 * Application Entry Point
 * 
 * Simple router that redirects the user based on session state.
 * - Logged in → Dashboard
 * - Guest → Login Page
 */

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: /pages/dashboard.php');
} else {
    header('Location: /auth/login.php');
}
exit;
