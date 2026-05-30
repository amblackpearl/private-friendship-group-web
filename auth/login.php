<?php
/**
 * User Login
 * 
 * Handles user authentication via username/email and password.
 * 
 * Security:
 * - Credentials verified with password_verify()
 * - Session ID regenerated after successful login
 * - Deleted users cannot log in
 * - Generic error message on failure (no user enumeration)
 */

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /pages/dashboard.php');
    exit;
}

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

$errors = [];
$old_login = '';

// Check for flash success message (from registration)
$flash_success = '';
if (isset($_SESSION['flash_success'])) {
    $flash_success = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    $old_login = $login;

    // Validation
    if ($login === '') {
        $errors[] = 'Username or email is required.';
    }
    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {
        // Look up user by username or email (non-deleted only)
        $stmt = $pdo->prepare(
            'SELECT id, name, username, email, password_hash, role
             FROM users
             WHERE (username = :username OR email = :email)
               AND deleted_at IS NULL
             LIMIT 1'
        );
        $stmt->execute(['username' => $login,
                        'email' => $login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            // Store user data in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];

            header('Location: /pages/dashboard.php');
            exit;
        } else {
            $errors[] = 'Invalid username/email or password.';
        }
    }
}

// Helper function for escaping output
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Log in to the Friendship Group Web Application">
    <title>Login — <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="24" cy="24" r="24" fill="url(#logo-gradient)"/>
                        <path d="M16 28c0-4.418 3.582-8 8-8s8 3.582 8 8" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                        <circle cx="24" cy="16" r="4" stroke="white" stroke-width="2.5"/>
                        <path d="M10 32c0-3.314 2.686-6 6-6" stroke="white" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                        <circle cx="13" cy="22" r="3" stroke="white" stroke-width="2" opacity="0.6"/>
                        <path d="M38 32c0-3.314-2.686-6-6-6" stroke="white" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                        <circle cx="35" cy="22" r="3" stroke="white" stroke-width="2" opacity="0.6"/>
                        <defs>
                            <linearGradient id="logo-gradient" x1="0" y1="0" x2="48" y2="48">
                                <stop stop-color="#6366f1"/>
                                <stop offset="1" stop-color="#8b5cf6"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <h1><?= e(APP_NAME) ?></h1>
                <p class="auth-subtitle">Welcome back! Sign in to continue</p>
            </div>

            <?php if ($flash_success): ?>
                <div class="alert alert-success" id="alert-success">
                    <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p><?= e($flash_success) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error" id="alert-error">
                    <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <?php foreach ($errors as $error): ?>
                            <p><?= e($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="/auth/login.php" class="auth-form" novalidate>
                <div class="form-group">
                    <label for="login">Username or Email</label>
                    <input type="text" id="login" name="login" value="<?= e($old_login) ?>" placeholder="Enter your username or email" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-full" id="login-btn">
                    <span>Sign In</span>
                    <svg viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
                        <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="/auth/register.php">Register</a></p>
            </div>
        </div>
    </div>
    <script src="/assets/js/app.js"></script>
</body>
</html>
