<?php
/**
 * Application Configuration
 * 
 * Stores application-wide constants including:
 * - App name and base URL
 * - Registration verification passcodes
 * - Upload settings
 * 
 * IMPORTANT: Do not expose passcodes in frontend code, HTML, or public files.
 * For production, use environment variables instead of hardcoded values.
 */

// Application name
define('APP_NAME', 'Friendship Group Web');

// Registration verification passcodes (server-side only)
// Change these before any real usage
define('ADMIN_REGISTER_PASSCODE', '---');
define('MEMBER_REGISTER_PASSCODE', '---');

// File upload settings
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);
define('UPLOAD_DIR', __DIR__ . '/../uploads/gallery/');

// Base path for URL construction (adjust if app is in a subdirectory)
define('BASE_URL', '/');
