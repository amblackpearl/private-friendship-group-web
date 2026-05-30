<?php
/**
 * Admin Panel Dashboard
 * 
 * Overview of system metrics and user management.
 */

require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

// Check admin role
if ($_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    die('Access denied.');
}

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Fetch metrics
$totalUsers = $pdo->query('SELECT COUNT(*) FROM users WHERE deleted_at IS NULL')->fetchColumn();
$totalAdmins = $pdo->query('SELECT COUNT(*) FROM users WHERE role = "admin" AND deleted_at IS NULL')->fetchColumn();
$totalPhotos = $pdo->query('SELECT COUNT(*) FROM photos WHERE deleted_at IS NULL')->fetchColumn();
$totalVotes = $pdo->query('SELECT COUNT(*) FROM votes WHERE deleted_at IS NULL')->fetchColumn();
$totalAgendas = $pdo->query('SELECT COUNT(*) FROM trip_agendas WHERE deleted_at IS NULL')->fetchColumn();

// Fetch users
$stmtUsers = $pdo->query(
    'SELECT id, name, username, email, role, created_at 
     FROM users 
     WHERE deleted_at IS NULL 
     ORDER BY created_at DESC'
);
$users = $stmtUsers->fetchAll();

$pageTitle = 'Admin Panel';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="container">
    <div class="page-header">
        <h1>🛡️ Admin Panel</h1>
        <p>Manage users and system content</p>
    </div>

    <!-- System Stats -->
    <div class="admin-stats">
        <div class="stat-card">
            <div class="stat-value"><?= (int)$totalUsers ?></div>
            <div class="stat-label">Total Users (<?= (int)$totalAdmins ?> Admins)</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= (int)$totalPhotos ?></div>
            <div class="stat-label">Total Photos</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= (int)$totalVotes ?></div>
            <div class="stat-label">Total Votes</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= (int)$totalAgendas ?></div>
            <div class="stat-label">Trip Agendas</div>
        </div>
    </div>

    <div class="dashboard-grid">
        <!-- Content Management Links -->
        <div class="card mb-8">
            <div class="card-header">
                <h2 class="card-title">Content Management</h2>
            </div>
            <div class="card-body">
                <p class="text-sm text-neutral-600 mb-6">As an admin, you can remove any content from the platform. Use the links below to browse content and look for the red "Remove" buttons.</p>
                
                <div class="flex flex-col gap-3">
                    <a href="/pages/gallery.php" class="btn btn-secondary justify-between w-full">
                        <span>Manage Photos</span>
                        <span>→</span>
                    </a>
                    <a href="/pages/votes.php" class="btn btn-secondary justify-between w-full">
                        <span>Manage Votes</span>
                        <span>→</span>
                    </a>
                    <a href="/pages/agendas.php" class="btn btn-secondary justify-between w-full">
                        <span>Manage Trip Agendas</span>
                        <span>→</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- System Configuration Info -->
        <div class="card mb-8">
            <div class="card-header">
                <h2 class="card-title">System Settings</h2>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <p>Registration passcodes are securely configured in <code>config/app.php</code>.</p>
                </div>
                
                <div class="mt-4 border-t border-neutral-100 pt-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-bold text-neutral-700">Max Upload Size:</span>
                        <span class="badge badge-member"><?= MAX_UPLOAD_SIZE / 1024 / 1024 ?> MB</span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-bold text-neutral-700">Allowed Formats:</span>
                        <span class="text-sm text-neutral-600">JPG, PNG, WEBP</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Management -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Registered Users</h2>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td class="font-bold"><?= e($u['name']) ?></td>
                                <td>@<?= e($u['username']) ?></td>
                                <td><?= e($u['email']) ?></td>
                                <td>
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="badge badge-admin">Admin</span>
                                    <?php else: ?>
                                        <span class="badge badge-member">Member</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
