<?php
/**
 * Photo Delete Handler
 * 
 * Admin-only endpoint for soft-deleting photos.
 * 
 * Security:
 * - Requires active session (auth_check)
 * - Requires admin role (403 if not)
 * - Requires POST method (405 if not)
 * - Validates photo_id parameter
 * - Performs soft delete (sets deleted_at)
 */

require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../config/database.php';

// Check admin role
if ($_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit('Access denied.');
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

// Validate photo ID
$photoId = filter_input(INPUT_POST, 'photo_id', FILTER_VALIDATE_INT);

if (!$photoId) {
    http_response_code(400);
    exit('Invalid photo ID.');
}

// Check if photo exists and is not already deleted
$stmt = $pdo->prepare('SELECT id, file_path FROM photos WHERE id = :id AND deleted_at IS NULL');
$stmt->execute(['id' => $photoId]);
$photo = $stmt->fetch();

if (!$photo) {
    http_response_code(404);
    exit('Photo not found.');
}

// Soft delete
$stmt = $pdo->prepare('UPDATE photos SET deleted_at = NOW() WHERE id = :id');
$stmt->execute(['id' => $photoId]);

$_SESSION['flash_success'] = 'Photo removed successfully.';
header('Location: /pages/gallery.php');
exit;
