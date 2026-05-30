<?php
/**
 * Photo Gallery Page
 * 
 * Displays all non-deleted photos in a responsive grid.
 * Admin users see a "Remove" button on each photo card.
 */

require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Check for flash messages
$flash_success = '';
if (isset($_SESSION['flash_success'])) {
    $flash_success = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

// Fetch all non-deleted photos with uploader name
$stmt = $pdo->prepare(
    'SELECT p.*, u.name AS uploader_name
     FROM photos p
     JOIN users u ON p.user_id = u.id
     WHERE p.deleted_at IS NULL
     ORDER BY p.created_at DESC'
);
$stmt->execute();
$photos = $stmt->fetchAll();

$isAdmin = ($_SESSION['user_role'] === 'admin');

$pageTitle = 'Photo Gallery';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="container">
    <div class="page-header">
        <div class="page-header-actions">
            <div>
                <h1>📸 Photo Gallery</h1>
                <p>Memories shared by the group</p>
            </div>
            <a href="/pages/photo_create.php" class="btn btn-primary" id="add-photo-btn">
                <svg viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
                <span>Add Photo</span>
            </a>
        </div>
    </div>

    <?php if ($flash_success): ?>
        <div class="alert alert-success" id="alert-success">
            <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <p><?= e($flash_success) ?></p>
        </div>
    <?php endif; ?>

    <?php if (empty($photos)): ?>
        <div class="card">
            <div class="empty-state">
                <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3>No photos uploaded yet</h3>
                <p>Be the first to share a memory with your group!</p>
                <a href="/pages/photo_create.php" class="btn btn-primary">Upload Photo</a>
            </div>
        </div>
    <?php else: ?>
        <div class="gallery-grid">
            <?php foreach ($photos as $index => $photo): ?>
                <div class="photo-card stagger-item">
                    <div class="photo-card-image-wrapper">
                        <img src="/uploads/gallery/<?= e($photo['file_path']) ?>" alt="<?= e($photo['caption']) ?>" class="photo-card-image" loading="lazy">
                    </div>
                    <div class="photo-card-body">
                        <div class="photo-card-caption"><?= e($photo['caption']) ?></div>
                        <?php if ($photo['description']): ?>
                            <p class="text-sm text-muted mt-2"><?= e(mb_substr($photo['description'], 0, 100)) ?></p>
                        <?php endif; ?>
                        <div class="photo-card-meta mt-2">
                            <span>By <?= e($photo['uploader_name']) ?></span>
                            <span><?= date('M j, Y', strtotime($photo['created_at'])) ?></span>
                        </div>
                    </div>
                    <div class="photo-card-footer">
                        <?php if ($photo['location']): ?>
                            <span class="photo-card-location">
                                <svg width="12" height="12" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                </svg>
                                <?= e($photo['location']) ?>
                            </span>
                        <?php else: ?>
                            <span></span>
                        <?php endif; ?>

                        <?php if ($isAdmin): ?>
                            <button type="button" class="btn btn-danger btn-sm delete-trigger"
                                    data-action="/pages/photo_delete.php"
                                    data-id="<?= (int)$photo['id'] ?>"
                                    data-name="photo_id"
                                    data-label="this photo"
                                    id="delete-photo-<?= (int)$photo['id'] ?>">
                                Remove
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="delete-modal">
    <div class="modal">
        <svg class="modal-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <h3>Confirm Removal</h3>
        <p>Are you sure you want to remove <span id="delete-label"></span>? This action cannot be undone.</p>
        <form method="POST" id="delete-form">
            <input type="hidden" name="" id="delete-input" value="">
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" id="delete-cancel">Cancel</button>
                <button type="submit" class="btn btn-danger">Remove</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
