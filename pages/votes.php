<?php
/**
 * Votes List Page
 * 
 * Displays all non-deleted voting forms, separated into Active and Closed/Expired.
 * Admin users see a "Remove" button on each vote card.
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

// Fetch Active Votes
$stmtActive = $pdo->prepare(
    'SELECT v.*, u.name AS creator_name,
            (SELECT COUNT(*) FROM vote_responses vr WHERE vr.vote_id = v.id) AS total_voters
     FROM votes v
     JOIN users u ON v.created_by = u.id
     WHERE v.deleted_at IS NULL
       AND v.status = "active"
       AND v.deadline > NOW()
     ORDER BY v.deadline ASC'
);
$stmtActive->execute();
$activeVotes = $stmtActive->fetchAll();

// Fetch Closed/Expired/Draft Votes
$stmtPast = $pdo->prepare(
    'SELECT v.*, u.name AS creator_name,
            (SELECT COUNT(*) FROM vote_responses vr WHERE vr.vote_id = v.id) AS total_voters
     FROM votes v
     JOIN users u ON v.created_by = u.id
     WHERE v.deleted_at IS NULL
       AND (v.status != "active" OR v.deadline <= NOW())
     ORDER BY v.created_at DESC'
);
$stmtPast->execute();
$pastVotes = $stmtPast->fetchAll();

$isAdmin = ($_SESSION['user_role'] === 'admin');

// Helper to determine accurate display status
function getVoteStatus($vote) {
    if ($vote['status'] === 'draft') return 'draft';
    if ($vote['status'] === 'closed') return 'closed';
    if ($vote['status'] === 'active' && strtotime($vote['deadline']) <= time()) return 'expired';
    return 'active';
}

$pageTitle = 'Votes';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="container">
    <div class="page-header">
        <div class="page-header-actions">
            <div>
                <h1>🗳️ Group Votes</h1>
                <p>Make decisions together</p>
            </div>
            <a href="/pages/vote_create.php" class="btn btn-primary" id="create-vote-btn">
                <svg viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
                <span>Create Vote</span>
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

    <!-- Active Votes Section -->
    <div class="dashboard-section">
        <div class="section-header">
            <h2>Active Votes</h2>
        </div>

        <?php if (empty($activeVotes)): ?>
            <div class="card mb-8">
                <div class="empty-state">
                    <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <h3>No active votes right now</h3>
                    <p>Start a group decision by creating a new vote!</p>
                </div>
            </div>
        <?php else: ?>
            <div class="dashboard-grid mb-8">
                <?php foreach ($activeVotes as $vote): ?>
                    <div class="vote-card stagger-item">
                        <div class="vote-card-header">
                            <div>
                                <a href="/pages/vote_detail.php?id=<?= (int)$vote['id'] ?>" class="vote-card-title"><?= e($vote['title']) ?></a>
                                <div class="vote-card-meta">
                                    <span>By <?= e($vote['creator_name']) ?></span>
                                    <span><?= (int)$vote['total_voters'] ?> votes</span>
                                </div>
                            </div>
                            <span class="badge badge-active">Active</span>
                        </div>
                        <?php if ($vote['description']): ?>
                            <div class="vote-card-description"><?= e(mb_substr($vote['description'], 0, 100)) ?><?= mb_strlen($vote['description']) > 100 ? '...' : '' ?></div>
                        <?php endif; ?>
                        <div class="vote-card-footer">
                            <span class="text-xs text-muted">Deadline: <?= date('M j, Y H:i', strtotime($vote['deadline'])) ?></span>
                            <div class="flex gap-2">
                                <?php if ($isAdmin): ?>
                                    <button type="button" class="btn btn-ghost btn-sm delete-trigger"
                                            data-action="/pages/vote_delete.php"
                                            data-id="<?= (int)$vote['id'] ?>"
                                            data-name="vote_id"
                                            data-label="this vote"
                                            title="Remove Vote">
                                        <svg viewBox="0 0 20 20" fill="currentColor" class="btn-icon-only text-error-500">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                <?php endif; ?>
                                <a href="/pages/vote_detail.php?id=<?= (int)$vote['id'] ?>" class="btn btn-primary btn-sm">Vote Now</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Past Votes Section -->
    <?php if (!empty($pastVotes)): ?>
        <div class="dashboard-section mt-8">
            <div class="section-header">
                <h2>Past Votes</h2>
            </div>
            <div class="dashboard-grid">
                <?php foreach ($pastVotes as $vote): 
                    $dispStatus = getVoteStatus($vote);
                ?>
                    <div class="vote-card stagger-item">
                        <div class="vote-card-header">
                            <div>
                                <a href="/pages/vote_detail.php?id=<?= (int)$vote['id'] ?>" class="vote-card-title"><?= e($vote['title']) ?></a>
                                <div class="vote-card-meta">
                                    <span>By <?= e($vote['creator_name']) ?></span>
                                    <span><?= (int)$vote['total_voters'] ?> votes</span>
                                </div>
                            </div>
                            <span class="badge badge-<?= $dispStatus ?>"><?= ucfirst($dispStatus) ?></span>
                        </div>
                        <div class="vote-card-footer">
                            <span class="text-xs text-muted">Ended: <?= date('M j, Y H:i', strtotime($vote['deadline'])) ?></span>
                            <div class="flex gap-2">
                                <?php if ($isAdmin): ?>
                                    <button type="button" class="btn btn-ghost btn-sm delete-trigger"
                                            data-action="/pages/vote_delete.php"
                                            data-id="<?= (int)$vote['id'] ?>"
                                            data-name="vote_id"
                                            data-label="this vote"
                                            title="Remove Vote">
                                        <svg viewBox="0 0 20 20" fill="currentColor" class="btn-icon-only text-error-500">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                <?php endif; ?>
                                <a href="/pages/vote_detail.php?id=<?= (int)$vote['id'] ?>" class="btn btn-secondary btn-sm">View Results</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
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
        <p>Are you sure you want to remove <span id="delete-label"></span>? All associated options and votes will also be removed.</p>
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
