<?php
/**
 * Create Vote Page
 * 
 * Allows users to create a new voting form.
 * Validates at least 2 options and a future deadline.
 */

require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

$errors = [];
$old = [
    'title' => '',
    'description' => '',
    'deadline' => '',
    'status' => 'active',
];
// Default 2 empty options if not submitted
$old_options = ['', ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $deadline = trim($_POST['deadline'] ?? '');
    $status = trim($_POST['status'] ?? 'active');
    $optionsInput = $_POST['options'] ?? [];

    $old['title'] = $title;
    $old['description'] = $description;
    $old['deadline'] = $deadline;
    $old['status'] = $status;
    $old_options = $optionsInput;

    // Filter out empty options
    $options = array_filter(array_map('trim', $optionsInput), function($val) {
        return $val !== '';
    });

    // Validation
    if ($title === '') {
        $errors[] = 'Vote title is required.';
    } elseif (mb_strlen($title) > 150) {
        $errors[] = 'Title must not exceed 150 characters.';
    }

    if (count($options) < 2) {
        $errors[] = 'Please provide at least 2 valid options.';
    }

    if ($deadline === '') {
        $errors[] = 'Deadline is required.';
    } else {
        $deadlineTime = strtotime($deadline);
        if (!$deadlineTime) {
            $errors[] = 'Invalid deadline format.';
        } elseif ($deadlineTime <= time()) {
            $errors[] = 'Deadline must be a date/time in the future.';
        }
    }

    if (!in_array($status, ['draft', 'active', 'closed'])) {
        $status = 'active'; // fallback
    }

    // Save vote
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                'INSERT INTO votes (created_by, title, description, status, deadline, created_at)
                 VALUES (:created_by, :title, :description, :status, :deadline, NOW())'
            );
            $stmt->execute([
                'created_by' => $_SESSION['user_id'],
                'title' => $title,
                'description' => $description ?: null,
                'status' => $status,
                'deadline' => date('Y-m-d H:i:s', strtotime($deadline))
            ]);
            $voteId = $pdo->lastInsertId();

            $stmtOption = $pdo->prepare('INSERT INTO vote_options (vote_id, option_text, created_at) VALUES (:vote_id, :option_text, NOW())');
            foreach ($options as $opt) {
                $stmtOption->execute([
                    'vote_id' => $voteId,
                    'option_text' => $opt
                ]);
            }

            $pdo->commit();

            $_SESSION['flash_success'] = 'Vote created successfully!';
            header('Location: /pages/vote_detail.php?id=' . $voteId);
            exit;
        } catch (Exception $ex) {
            $pdo->rollBack();
            $errors[] = 'Failed to create vote. Please try again.';
            error_log($ex->getMessage());
        }
    }
} else {
    // Default deadline: tomorrow at 23:59
    $old['deadline'] = date('Y-m-d\TH:i', strtotime('tomorrow 23:59:00'));
}

$pageTitle = 'Create Vote';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="container">
    <div class="page-header">
        <h1>🗳️ Create Vote</h1>
        <p>Ask the group to make a decision</p>
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

            <form method="POST" action="/pages/vote_create.php" id="create-vote-form">
                <!-- Title -->
                <div class="form-group">
                    <label for="title">Question / Title <span class="text-muted text-xs">(required)</span></label>
                    <input type="text" id="title" name="title" value="<?= e($old['title']) ?>" placeholder="e.g. Which destination for our next trip?" required maxlength="150">
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label for="description">Description <span class="text-muted text-xs">(optional)</span></label>
                    <textarea id="description" name="description" placeholder="Provide more details about what we're voting on..." rows="3"><?= e($old['description']) ?></textarea>
                </div>

                <!-- Options -->
                <div class="form-group">
                    <label>Options <span class="text-muted text-xs">(at least 2 required)</span></label>
                    <div id="options-container">
                        <?php 
                        // Ensure at least 2 options are shown
                        $display_options = count($old_options) >= 2 ? $old_options : array_pad($old_options, 2, '');
                        foreach ($display_options as $index => $opt): 
                        ?>
                            <div class="option-input-group">
                                <input type="text" name="options[]" value="<?= e($opt) ?>" placeholder="Option <?= $index + 1 ?>" required maxlength="150">
                                <?php if ($index >= 2): ?>
                                    <button type="button" class="btn-remove-option" title="Remove option" onclick="this.parentElement.remove()">
                                        <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                <?php else: ?>
                                    <div style="width: 36px; height: 36px;"></div><!-- Spacer for alignment -->
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm mt-2" id="add-option-btn">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                        Add Option
                    </button>
                </div>

                <div class="form-row">
                    <!-- Deadline -->
                    <div class="form-group">
                        <label for="deadline">Deadline <span class="text-muted text-xs">(required)</span></label>
                        <input type="datetime-local" id="deadline" name="deadline" value="<?= e($old['deadline']) ?>" required>
                    </div>

                    <!-- Status -->
                    <div class="form-group">
                        <label for="status">Initial Status</label>
                        <select id="status" name="status">
                            <option value="active" <?= $old['status'] === 'active' ? 'selected' : '' ?>>Active (Ready for votes)</option>
                            <option value="draft" <?= $old['status'] === 'draft' ? 'selected' : '' ?>>Draft (Hidden until published)</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="submit" class="btn btn-primary" id="vote-submit-btn">
                        <span>Create Vote</span>
                    </button>
                    <a href="/pages/votes.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
