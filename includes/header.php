<?php
/**
 * Page Header Include
 * 
 * Outputs the HTML <head> section with meta tags, fonts, and stylesheet.
 * 
 * Expected variables:
 * - $pageTitle (string) — The page-specific title
 * 
 * Usage: $pageTitle = 'Dashboard'; include __DIR__ . '/../includes/header.php';
 */

if (!isset($pageTitle)) {
    $pageTitle = 'Home';
}

require_once __DIR__ . '/../config/app.php';

function e_header($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e_header($pageTitle) ?> — <?= e_header(APP_NAME) ?>">
    <title><?= e_header($pageTitle) ?> — <?= e_header(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
