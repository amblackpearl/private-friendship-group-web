<?php
/**
 * Trip Agendas List Page
 * 
 * Displays all non-deleted trip agenda proposals.
 * Admin users see a "Remove" button on each agenda card.
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

// Fetch all non-deleted trip agendas with submitter name
$stmt = $pdo->prepare(
    'SELECT ta.*, u.name AS submitter_name
     FROM trip_agendas ta
     JOIN users u ON ta.user_id = u.id
     WHERE ta.deleted_at IS NULL
     ORDER BY ta.proposed_date ASC'
);
$stmt->execute();
$agendas = $stmt->fetchAll();

$isAdmin = ($_SESSION['user_role'] === 'admin');

$pageTitle = 'Trip Agendas';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="container">
    <div class="page-header">
        <div class="page-header-actions">
            <div>
                <h1>🗺️ Trip Agendas</h1>
                <p>Plan our next adventure together</p>
            </div>
            <a href="/pages/agenda_create.php" class="btn btn-primary" id="create-agenda-btn">
                <svg viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
                <span>Submit Proposal</span>
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

    <?php if (empty($agendas)): ?>
        <div class="card">
            <div class="empty-state">
                <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <h3>No trip proposals yet</h3>
                <p>Have an idea for our next getaway? Submit a proposal!</p>
                <a href="/pages/agenda_create.php" class="btn btn-primary">Submit Proposal</a>
            </div>
        </div>
    <?php else: ?>
        <div class="agenda-grid">
            <?php foreach ($agendas as $agenda): ?>
                <div class="agenda-card stagger-item">
                    <div class="agenda-card-accent"></div>
                    <div class="agenda-card-body">
                        <h3 class="agenda-card-destination"><?= e($agenda['destination']) ?></h3>
                        
                        <div class="agenda-card-info">
                            <div class="agenda-info-item">
                                <span class="agenda-info-label">Proposed Date</span>
                                <span class="agenda-info-value"><?= date('D, M j, Y', strtotime($agenda['proposed_date'])) ?></span>
                            </div>
                            <div class="agenda-info-item">
                                <span class="agenda-info-label">Est. Budget</span>
                                <span class="agenda-info-value">Rp <?= number_format($agenda['estimated_budget'], 0, ',', '.') ?></span>
                            </div>
                        </div>
                        
                        <p class="agenda-card-description"><?= e($agenda['description']) ?></p>
                    </div>
                    
                    <div class="agenda-card-footer">
                        <span>By <?= e($agenda['submitter_name']) ?></span>
                        <div class="flex gap-2">
                            <?php if ($isAdmin): ?>
                                <button type="button" class="btn btn-ghost btn-sm delete-trigger"
                                        data-action="/pages/agenda_delete.php"
                                        data-id="<?= (int)$agenda['id'] ?>"
                                        data-name="agenda_id"
                                        data-label="this trip agenda">
                                    <svg viewBox="0 0 20 20" fill="currentColor" class="btn-icon-only text-error-500">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            <?php endif; ?>
                            <a href="/pages/agenda_detail.php?id=<?= (int)$agenda['id'] ?>" class="btn btn-primary btn-sm">View Details</a>
                        </div>
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
