<?php
/**
 * Trip Agenda Delete Handler
 * 
 * Admin-only endpoint for soft-deleting trip agendas.
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

// Validate ID
$agendaId = filter_input(INPUT_POST, 'agenda_id', FILTER_VALIDATE_INT);

if (!$agendaId) {
    http_response_code(400);
    exit('Invalid agenda ID.');
}

// Check if exists
$stmt = $pdo->prepare('SELECT id FROM trip_agendas WHERE id = :id AND deleted_at IS NULL');
$stmt->execute(['id' => $agendaId]);
if (!$stmt->fetch()) {
    http_response_code(404);
    exit('Agenda not found.');
}

// Soft delete
$stmt = $pdo->prepare('UPDATE trip_agendas SET deleted_at = NOW() WHERE id = :id');
$stmt->execute(['id' => $agendaId]);

$_SESSION['flash_success'] = 'Trip agenda removed successfully.';
header('Location: /pages/agendas.php');
exit;
