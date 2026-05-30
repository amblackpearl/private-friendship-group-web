<?php
/**
 * Photo Upload Page
 * 
 * Allows logged-in users to upload a new photo to the gallery.
 * 
 * Validates:
 * - File exists and is a valid image
 * - MIME type is allowed (JPG, PNG, WEBP)
 * - File size is within limit (5MB)
 * - Caption is required
 * 
 * Security:
 * - Generates unique filename
 * - Validates MIME type
 * - Rolls back file if DB insert fails
 */

require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

$errors = [];
$old = [
    'caption' => '',
    'description' => '',
    'location' => '',
    'trip_date' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caption = trim($_POST['caption'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $tripDate = trim($_POST['trip_date'] ?? '');

    $old['caption'] = $caption;
    $old['description'] = $description;
    $old['location'] = $location;
    $old['trip_date'] = $tripDate;

    // Validate caption
    if ($caption === '') {
        $errors[] = 'Caption is required.';
    } elseif (mb_strlen($caption) > 150) {
        $errors[] = 'Caption must not exceed 150 characters.';
    }

    // Validate trip_date if provided
    if ($tripDate !== '' && !strtotime($tripDate)) {
        $errors[] = 'Trip date is not a valid date.';
    }

    // Validate file
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Photo file is required.';
    } elseif ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload error. Please try again.';
    } else {
        $file = $_FILES['photo'];

        // Check file size
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            $errors[] = 'File size exceeds the maximum limit of ' . (MAX_UPLOAD_SIZE / 1024 / 1024) . ' MB.';
        }

        // Check MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
            $errors[] = 'Only JPG, JPEG, PNG, and WEBP images are allowed.';
        }

        // Check extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_IMAGE_EXTENSIONS)) {
            $errors[] = 'Invalid file extension.';
        }
    }

    if (empty($errors)) {
        // Generate unique filename
        $uniqueName = uniqid('photo_', true) . '.' . $extension;
        $targetPath = UPLOAD_DIR . $uniqueName;

        // Ensure upload directory exists
        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0755, true);
        }

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            try {
                $stmt = $pdo->prepare(
                    'INSERT INTO photos (user_id, caption, description, file_path, location, trip_date, created_at)
                     VALUES (:user_id, :caption, :description, :file_path, :location, :trip_date, NOW())'
                );
                $stmt->execute([
                    'user_id' => $_SESSION['user_id'],
                    'caption' => $caption,
                    'description' => $description ?: null,
                    'file_path' => $uniqueName,
                    'location' => $location ?: null,
                    'trip_date' => $tripDate ?: null,
                ]);

                $_SESSION['flash_success'] = 'Photo uploaded successfully!';
                header('Location: /pages/gallery.php');
                exit;
            } catch (Exception $ex) {
                // Remove uploaded file if DB insert fails
                if (file_exists($targetPath)) {
                    unlink($targetPath);
                }
                $errors[] = 'Failed to save photo data. Please try again.';
            }
        } else {
            $errors[] = 'Failed to save uploaded file. Please try again.';
        }
    }
}

$pageTitle = 'Upload Photo';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="container">
    <div class="page-header">
        <h1>📷 Upload Photo</h1>
        <p>Share a memory with your group</p>
    </div>

    <div class="card" style="max-width: 700px; margin: 0 auto;">
        <div class="card-body">
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

            <form method="POST" enctype="multipart/form-data" action="/pages/photo_create.php" id="photo-upload-form">
                <!-- Photo file -->
                <div class="form-group">
                    <label for="photo">Photo</label>
                    <div class="file-upload-area" id="file-upload-area">
                        <svg class="file-upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="file-upload-text">
                            <strong>Click to upload</strong> or drag and drop
                        </p>
                        <p class="file-upload-hint">JPG, JPEG, PNG or WEBP (max 5MB)</p>
                        <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/webp" class="hidden" required>
                    </div>
                    <div class="image-preview" id="image-preview">
                        <img src="" alt="Preview" id="preview-img">
                    </div>
                </div>

                <!-- Caption -->
                <div class="form-group">
                    <label for="caption">Caption <span class="text-muted text-xs">(required)</span></label>
                    <input type="text" id="caption" name="caption" value="<?= e($old['caption']) ?>" placeholder="Give your photo a title" required maxlength="150">
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label for="description">Description <span class="text-muted text-xs">(optional)</span></label>
                    <textarea id="description" name="description" placeholder="Tell the story behind this photo..." rows="3"><?= e($old['description']) ?></textarea>
                </div>

                <div class="form-row">
                    <!-- Location -->
                    <div class="form-group">
                        <label for="location">Location <span class="text-muted text-xs">(optional)</span></label>
                        <input type="text" id="location" name="location" value="<?= e($old['location']) ?>" placeholder="e.g. Bali, Indonesia" maxlength="150">
                    </div>

                    <!-- Trip Date -->
                    <div class="form-group">
                        <label for="trip_date">Trip Date <span class="text-muted text-xs">(optional)</span></label>
                        <input type="date" id="trip_date" name="trip_date" value="<?= e($old['trip_date']) ?>">
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="submit" class="btn btn-primary" id="upload-submit-btn">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span>Upload Photo</span>
                    </button>
                    <a href="/pages/gallery.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
