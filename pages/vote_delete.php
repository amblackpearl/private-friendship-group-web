<?php
/**
 * Vote Delete Handler
 * 
 * Admin-only endpoint for soft-deleting votes.
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

// Validate vote ID
$voteId = filter_input(INPUT_POST, 'vote_id', FILTER_VALIDATE_INT);

if (!$voteId) {
    http_response_code(400);
    exit('Invalid vote ID.');
}

// Check if vote exists and is not already deleted
$stmt = $pdo->prepare('SELECT id FROM votes WHERE id = :id AND deleted_at IS NULL');
$stmt->execute(['id' => $voteId]);
if (!$stmt->fetch()) {
    http_response_code(404);
    exit('Vote not found.');
}

// Soft delete
$stmt = $pdo->prepare('UPDATE votes SET deleted_at = NOW() WHERE id = :id');
$stmt->execute(['id' => $voteId]);

$_SESSION['flash_success'] = 'Vote removed successfully.';
header('Location: /pages/votes.php');
exit;
