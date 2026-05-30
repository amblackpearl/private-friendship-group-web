<?php
/**
 * Create Trip Agenda Page
 * 
 * Allows users to submit a new trip proposal.
 */

require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

$errors = [];
$old = [
    'destination' => '',
    'proposed_date' => '',
    'estimated_budget' => '',
    'meeting_point' => '',
    'transportation_plan' => '',
    'accommodation_plan' => '',
    'activity_list' => '',
    'description' => '',
    'notes' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and trim inputs
    $fields = array_keys($old);
    foreach ($fields as $field) {
        $old[$field] = trim($_POST[$field] ?? '');
    }

    // Validation (Required fields)
    if ($old['destination'] === '') {
        $errors[] = 'Destination is required.';
    } elseif (mb_strlen($old['destination']) > 150) {
        $errors[] = 'Destination must not exceed 150 characters.';
    }

    if ($old['proposed_date'] === '') {
        $errors[] = 'Proposed date is required.';
    } else {
        $dateParts = explode('-', $old['proposed_date']);
        if (count($dateParts) !== 3 || !checkdate((int)$dateParts[1], (int)$dateParts[2], (int)$dateParts[0])) {
            $errors[] = 'Invalid proposed date format.';
        }
    }

    if ($old['estimated_budget'] === '') {
        $errors[] = 'Estimated budget is required.';
    } elseif (!is_numeric($old['estimated_budget']) || $old['estimated_budget'] < 0) {
        $errors[] = 'Estimated budget must be a positive number.';
    }

    if ($old['description'] === '') {
        $errors[] = 'Description is required.';
    }

    // Optional field constraints
    if (mb_strlen($old['meeting_point']) > 150) {
        $errors[] = 'Meeting point must not exceed 150 characters.';
    }

    // Save agenda
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO trip_agendas 
                 (user_id, destination, proposed_date, estimated_budget, meeting_point, transportation_plan, accommodation_plan, activity_list, description, notes, created_at)
                 VALUES 
                 (:u, :dest, :date, :budget, :mp, :tp, :ap, :act, :desc, :notes, NOW())'
            );
            $stmt->execute([
                'u' => $_SESSION['user_id'],
                'dest' => $old['destination'],
                'date' => $old['proposed_date'],
                'budget' => $old['estimated_budget'],
                'mp' => $old['meeting_point'] ?: null,
                'tp' => $old['transportation_plan'] ?: null,
                'ap' => $old['accommodation_plan'] ?: null,
                'act' => $old['activity_list'] ?: null,
                'desc' => $old['description'],
                'notes' => $old['notes'] ?: null,
            ]);

            $agendaId = $pdo->lastInsertId();

            $_SESSION['flash_success'] = 'Trip agenda submitted successfully!';
            header('Location: /pages/agenda_detail.php?id=' . $agendaId);
            exit;
        } catch (Exception $ex) {
            $errors[] = 'Failed to submit agenda. Please try again.';
            error_log($ex->getMessage());
        }
    }
}

$pageTitle = 'Submit Trip Agenda';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="container">
    <div class="page-header">
        <h1>🗺️ Submit Trip Proposal</h1>
        <p>Pitch your idea for our next group adventure</p>
    </div>

    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <div class="card-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
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

            <form method="POST" action="/pages/agenda_create.php">
                <h3 class="font-bold text-lg mb-4 text-neutral-800 border-b border-neutral-200 pb-2">The Basics</h3>
                
                <div class="form-group">
                    <label for="destination">Destination <span class="text-muted text-xs">(required)</span></label>
                    <input type="text" id="destination" name="destination" value="<?= e($old['destination']) ?>" placeholder="e.g. Kyoto, Japan or Mount Bromo" required maxlength="150">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="proposed_date">Proposed Date <span class="text-muted text-xs">(required)</span></label>
                        <input type="date" id="proposed_date" name="proposed_date" value="<?= e($old['proposed_date']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="estimated_budget">Estimated Budget (Rp) <span class="text-muted text-xs">(required)</span></label>
                        <input type="number" id="estimated_budget" name="estimated_budget" value="<?= e($old['estimated_budget']) ?>" placeholder="e.g. 5000000" required min="0" step="1000">
                    </div>
                </div>

                <div class="form-group mb-8">
                    <label for="description">Why should we go here? (Description) <span class="text-muted text-xs">(required)</span></label>
                    <textarea id="description" name="description" placeholder="Sell us on this destination! What's the vibe?" rows="4" required><?= e($old['description']) ?></textarea>
                </div>

                <h3 class="font-bold text-lg mb-4 text-neutral-800 border-b border-neutral-200 pb-2">The Details (Optional but helpful)</h3>

                <div class="form-group">
                    <label for="meeting_point">Meeting Point</label>
                    <input type="text" id="meeting_point" name="meeting_point" value="<?= e($old['meeting_point']) ?>" placeholder="e.g. Soekarno-Hatta Airport Terminal 3" maxlength="150">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="transportation_plan">Transportation Plan</label>
                        <textarea id="transportation_plan" name="transportation_plan" placeholder="Flights, train, renting cars?" rows="3"><?= e($old['transportation_plan']) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="accommodation_plan">Accommodation Plan</label>
                        <textarea id="accommodation_plan" name="accommodation_plan" placeholder="Airbnb, hotels, camping?" rows="3"><?= e($old['accommodation_plan']) ?></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label for="activity_list">Key Activities</label>
                    <textarea id="activity_list" name="activity_list" placeholder="What are the main things to do?" rows="3"><?= e($old['activity_list']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="notes">Additional Notes</label>
                    <textarea id="notes" name="notes" placeholder="Any visa requirements, weather notes, etc." rows="2"><?= e($old['notes']) ?></textarea>
                </div>

                <div class="flex gap-3 mt-8 pt-4 border-t border-neutral-100">
                    <button type="submit" class="btn btn-primary">Submit Proposal</button>
                    <a href="/pages/agendas.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
