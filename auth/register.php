<?php
/**
 * User Registration
 * 
 * Handles user registration with verification passcode validation.
 * - Admin passcode → creates admin account
 * - Member passcode → creates member account
 * - Invalid passcode → registration declined
 * 
 * Security:
 * - Passwords hashed with password_hash()
 * - All inputs validated server-side
 * - Prepared statements for all queries
 * - Output escaped with htmlspecialchars()
 * - Raw passcode never stored in database
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
$success = '';
$old = [
    'name' => '',
    'username' => '',
    'email' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and trim inputs
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $passcode = trim($_POST['verification_passcode'] ?? '');

    // Preserve old input for re-display
    $old['name'] = $name;
    $old['username'] = $username;
    $old['email'] = $email;

    // ---- Validation ----

    // Name
    if ($name === '') {
        $errors[] = 'Name is required.';
    } elseif (mb_strlen($name) > 100) {
        $errors[] = 'Name must not exceed 100 characters.';
    }

    // Username
    if ($username === '') {
        $errors[] = 'Username is required.';
    } elseif (mb_strlen($username) > 50) {
        $errors[] = 'Username must not exceed 50 characters.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username may only contain letters, numbers, and underscores.';
    }

    // Email
    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (mb_strlen($email) > 100) {
        $errors[] = 'Email must not exceed 100 characters.';
    }

    // Password
    if ($password === '') {
        $errors[] = 'Password is required.';
    } elseif (mb_strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    // Confirm password
    if ($confirmPassword === '') {
        $errors[] = 'Confirm password is required.';
    } elseif ($password !== $confirmPassword) {
        $errors[] = 'Password and confirm password do not match.';
    }

    // Verification passcode
    if ($passcode === '') {
        $errors[] = 'Verification passcode is required.';
    }

    // Determine role from passcode
    $role = null;
    if ($passcode !== '') {
        if ($passcode === ADMIN_REGISTER_PASSCODE) {
            $role = 'admin';
        } elseif ($passcode === MEMBER_REGISTER_PASSCODE) {
            $role = 'member';
        } else {
            $errors[] = 'Invalid verification passcode. Registration declined.';
        }
    }

    // Check uniqueness only if no errors so far
    if (empty($errors)) {
        // Check duplicate username
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username AND deleted_at IS NULL');
        $stmt->execute(['username' => $username]);
        if ($stmt->fetch()) {
            $errors[] = 'This username is already taken.';
        }

        // Check duplicate email
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email AND deleted_at IS NULL');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = 'This email is already registered.';
        }
    }

    // ---- Create Account ----
    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare(
            'INSERT INTO users (name, username, email, password_hash, role, created_at)
             VALUES (:name, :username, :email, :password_hash, :role, NOW())'
        );
        $stmt->execute([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password_hash' => $passwordHash,
            'role' => $role,
        ]);

        // Redirect to login with success message
        $_SESSION['flash_success'] = 'Registration successful. Please log in.';
        header('Location: /auth/login.php');
        exit;
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
    <meta name="description" content="Register for the Friendship Group Web Application">
    <title>Register — <?= e(APP_NAME) ?></title>
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
                <h1>Create Account</h1>
                <p class="auth-subtitle">Join your friendship group</p>
            </div>

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

            <form method="POST" action="/auth/register.php" class="auth-form" novalidate>
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="<?= e($old['name']) ?>" placeholder="Enter your full name" required autofocus>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?= e($old['username']) ?>" placeholder="Choose a username" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= e($old['email']) ?>" placeholder="Enter your email" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Min. 6 characters" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="verification_passcode">Verification Passcode</label>
                    <input type="password" id="verification_passcode" name="verification_passcode" placeholder="Enter your group passcode" required>
                    <span class="form-hint">Ask your group admin for the passcode</span>
                </div>

                <button type="submit" class="btn btn-primary btn-full" id="register-btn">
                    <span>Create Account</span>
                    <svg viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
                        <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="/auth/login.php">Log in</a></p>
            </div>
        </div>
    </div>
    <script src="/assets/js/app.js"></script>
</body>
</html>
