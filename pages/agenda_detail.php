<?php
/**
 * Trip Agenda Detail Page
 * 
 * Displays all fields for a specific trip agenda.
 */

require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

$agendaId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$agendaId) {
    header('Location: /pages/agendas.php');
    exit;
}

$isAdmin = ($_SESSION['user_role'] === 'admin');

// Fetch agenda details
$stmt = $pdo->prepare(
    'SELECT ta.*, u.name AS submitter_name 
     FROM trip_agendas ta 
     JOIN users u ON ta.user_id = u.id 
     WHERE ta.id = :id AND ta.deleted_at IS NULL'
);
$stmt->execute(['id' => $agendaId]);
$agenda = $stmt->fetch();

if (!$agenda) {
    header('Location: /pages/agendas.php');
    exit;
}

$successMsg = '';
if (isset($_SESSION['flash_success'])) {
    $successMsg = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

$pageTitle = $agenda['destination'];
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="container">
    <div class="page-header">
        <a href="/pages/agendas.php" class="btn btn-ghost btn-sm mb-4" style="margin-left: -12px;">
            <svg viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
            </svg>
            Back to Agendas
        </a>
    </div>

    <div class="agenda-detail">
        <?php if ($successMsg): ?>
            <div class="alert alert-success">
                <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p><?= e($successMsg) ?></p>
            </div>
        <?php endif; ?>

        <div class="card">
            <div style="height: 8px; background: var(--gradient-primary);"></div>
            <div class="card-body pt-8 pb-8">
                <!-- Header Info -->
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h1 class="font-bold text-4xl mb-2 text-neutral-900"><?= e($agenda['destination']) ?></h1>
                        <p class="text-neutral-500">Proposed by <strong class="text-neutral-700"><?= e($agenda['submitter_name']) ?></strong> on <?= date('M j, Y', strtotime($agenda['created_at'])) ?></p>
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-8">
                    <h3 class="detail-field-label">The Pitch</h3>
                    <p class="detail-field-value text-lg"><?= nl2br(e($agenda['description'])) ?></p>
                </div>

                <!-- Key Details Grid -->
                <div class="agenda-detail-grid bg-neutral-50 p-6 rounded-xl border border-neutral-200 mb-8">
                    <div class="detail-field mb-0">
                        <div class="detail-field-label">Proposed Date</div>
                        <div class="detail-field-value font-bold text-primary-700 text-xl">
                            <?= date('l, M j, Y', strtotime($agenda['proposed_date'])) ?>
                        </div>
                    </div>
                    <div class="detail-field mb-0">
                        <div class="detail-field-label">Est. Budget per Person</div>
                        <div class="detail-field-value font-bold text-success-600 text-xl">
                            Rp <?= number_format($agenda['estimated_budget'], 0, ',', '.') ?>
                        </div>
                    </div>
                    <?php if ($agenda['meeting_point']): ?>
                    <div class="detail-field mb-0 mt-4 md:mt-0 col-span-full">
                        <div class="detail-field-label">Meeting Point</div>
                        <div class="detail-field-value">
                            <?= e($agenda['meeting_point']) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Plans Grid -->
                <div class="agenda-detail-grid mb-8">
                    <?php if ($agenda['transportation_plan']): ?>
                    <div class="detail-field">
                        <div class="detail-field-label flex items-center gap-2">
                            <span>✈️</span> Transportation
                        </div>
                        <div class="detail-field-value">
                            <?= nl2br(e($agenda['transportation_plan'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($agenda['accommodation_plan']): ?>
                    <div class="detail-field">
                        <div class="detail-field-label flex items-center gap-2">
                            <span>🏨</span> Accommodation
                        </div>
                        <div class="detail-field-value">
                            <?= nl2br(e($agenda['accommodation_plan'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($agenda['activity_list']): ?>
                <div class="detail-field mb-8 border-t border-neutral-100 pt-6">
                    <div class="detail-field-label flex items-center gap-2">
                        <span>🎯</span> Key Activities
                    </div>
                    <div class="detail-field-value">
                        <?= nl2br(e($agenda['activity_list'])) ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($agenda['notes']): ?>
                <div class="detail-field bg-warning-50 p-4 rounded-lg border border-warning-100 mb-4">
                    <div class="detail-field-label text-warning-600 flex items-center gap-2">
                        <span>⚠️</span> Additional Notes
                    </div>
                    <div class="detail-field-value">
                        <?= nl2br(e($agenda['notes'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($isAdmin): ?>
            <div class="card-footer" style="background: var(--neutral-50); justify-content: flex-end;">
                <button type="button" class="btn btn-danger btn-sm delete-trigger"
                        data-action="/pages/agenda_delete.php"
                        data-id="<?= $agendaId ?>"
                        data-name="agenda_id"
                        data-label="this trip agenda">
                    Remove Agenda
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<?php if ($isAdmin): ?>
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
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
