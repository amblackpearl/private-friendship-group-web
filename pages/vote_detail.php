<?php
/**
 * Vote Detail Page
 * 
 * Displays vote information, handles voting submission, and shows results.
 */

require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

$voteId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$voteId) {
    header('Location: /pages/votes.php');
    exit;
}

$userId = $_SESSION['user_id'];
$isAdmin = ($_SESSION['user_role'] === 'admin');

// Fetch vote details
$stmt = $pdo->prepare(
    'SELECT v.*, u.name AS creator_name 
     FROM votes v 
     JOIN users u ON v.created_by = u.id 
     WHERE v.id = :id AND v.deleted_at IS NULL'
);
$stmt->execute(['id' => $voteId]);
$vote = $stmt->fetch();

if (!$vote) {
    // Note: Can't easily use 404 header if content is output, so just redirect or error
    header('Location: /pages/votes.php');
    exit;
}

// Determine true status
$dispStatus = $vote['status'];
$isExpired = false;
if ($dispStatus === 'active' && strtotime($vote['deadline']) <= time()) {
    $dispStatus = 'expired';
    $isExpired = true;
}
$isAcceptingVotes = ($dispStatus === 'active');

// Fetch options
$stmtOpt = $pdo->prepare('SELECT * FROM vote_options WHERE vote_id = :vote_id ORDER BY id ASC');
$stmtOpt->execute(['vote_id' => $voteId]);
$options = $stmtOpt->fetchAll();

// Check if user has voted
$stmtHasVoted = $pdo->prepare('SELECT option_id FROM vote_responses WHERE vote_id = :vote_id AND user_id = :user_id');
$stmtHasVoted->execute(['vote_id' => $voteId, 'user_id' => $userId]);
$userVote = $stmtHasVoted->fetch();
$hasVoted = ($userVote !== false);
$userOptionId = $hasVoted ? $userVote['option_id'] : null;

$errors = [];
$successMsg = '';
if (isset($_SESSION['flash_success'])) {
    $successMsg = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_vote'])) {
    if (!$isAcceptingVotes) {
        $errors[] = 'This vote is no longer accepting responses.';
    } elseif ($hasVoted) {
        $errors[] = 'You have already voted on this topic.';
    } else {
        $optionId = filter_input(INPUT_POST, 'option_id', FILTER_VALIDATE_INT);
        if (!$optionId) {
            $errors[] = 'Please select an option to vote.';
        } else {
            // Verify option belongs to vote
            $validOption = false;
            foreach ($options as $opt) {
                if ($opt['id'] == $optionId) {
                    $validOption = true;
                    break;
                }
            }
            if (!$validOption) {
                $errors[] = 'Invalid option selected.';
            } else {
                try {
                    $stmtIns = $pdo->prepare('INSERT INTO vote_responses (vote_id, option_id, user_id, created_at) VALUES (:v, :o, :u, NOW())');
                    $stmtIns->execute(['v' => $voteId, 'o' => $optionId, 'u' => $userId]);
                    
                    $_SESSION['flash_success'] = 'Your vote has been recorded!';
                    header("Location: /pages/vote_detail.php?id=" . $voteId);
                    exit;
                } catch (PDOException $e) {
                    // Check for unique constraint violation (code 23000)
                    if ($e->getCode() == '23000') {
                        $errors[] = 'You have already voted on this topic.';
                    } else {
                        $errors[] = 'An error occurred while saving your vote.';
                    }
                }
            }
        }
    }
}

// If showing results (has voted or not accepting votes)
$results = [];
$totalVotes = 0;
$maxVotes = 0;
if ($hasVoted || !$isAcceptingVotes) {
    // Aggregate votes
    $stmtCount = $pdo->prepare(
        'SELECT option_id, COUNT(*) as count 
         FROM vote_responses 
         WHERE vote_id = :vote_id 
         GROUP BY option_id'
    );
    $stmtCount->execute(['vote_id' => $voteId]);
    $counts = $stmtCount->fetchAll(PDO::FETCH_KEY_PAIR);
    
    foreach ($options as $opt) {
        $count = $counts[$opt['id']] ?? 0;
        $totalVotes += $count;
        if ($count > $maxVotes) $maxVotes = $count;
        $results[$opt['id']] = [
            'text' => $opt['option_text'],
            'count' => $count,
        ];
    }
}

$pageTitle = $vote['title'];
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="container">
    <div class="page-header">
        <a href="/pages/votes.php" class="btn btn-ghost btn-sm mb-4" style="margin-left: -12px;">
            <svg viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
            </svg>
            Back to Votes
        </a>
    </div>

    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <div class="card-body">
            <div class="flex justify-between items-center mb-2">
                <div class="badge badge-<?= $dispStatus ?>"><?= ucfirst($dispStatus) ?></div>
                <div class="text-xs text-muted">
                    Created by <?= e($vote['creator_name']) ?> • <?= date('M j, Y', strtotime($vote['created_at'])) ?>
                </div>
            </div>

            <h1 class="font-bold text-3xl mb-4 text-neutral-900"><?= e($vote['title']) ?></h1>

            <?php if ($vote['description']): ?>
                <p class="text-neutral-600 mb-6 line-height-1.6"><?= nl2br(e($vote['description'])) ?></p>
            <?php endif; ?>
            
            <div class="flex items-center gap-2 text-sm text-neutral-500 mb-8 pb-4 border-b border-neutral-100">
                <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                </svg>
                Deadline: <strong class="<?= $isExpired ? 'text-error-500' : 'text-neutral-800' ?>"><?= date('M j, Y - H:i', strtotime($vote['deadline'])) ?></strong>
            </div>

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

            <?php if ($successMsg): ?>
                <div class="alert alert-success">
                    <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p><?= e($successMsg) ?></p>
                </div>
            <?php endif; ?>

            <!-- Interactive Voting Form OR Results -->
            <?php if ($isAcceptingVotes && !$hasVoted): ?>
                <form method="POST" action="/pages/vote_detail.php?id=<?= $voteId ?>">
                    <input type="hidden" name="submit_vote" value="1">
                    <div class="vote-options">
                        <?php foreach ($options as $opt): ?>
                            <div class="vote-option">
                                <input type="radio" id="opt_<?= $opt['id'] ?>" name="option_id" value="<?= $opt['id'] ?>" required>
                                <label for="opt_<?= $opt['id'] ?>">
                                    <div class="vote-option-radio"></div>
                                    <span><?= e($opt['option_text']) ?></span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn btn-primary btn-full mt-4 btn-lg">Submit Vote</button>
                </form>

            <?php else: ?>
                <!-- Results View -->
                <div class="mb-4">
                    <h3 class="font-bold text-lg text-neutral-800 mb-4">Results</h3>
                    <?php if ($totalVotes == 0): ?>
                        <p class="text-neutral-500 italic">No votes have been cast yet.</p>
                    <?php else: ?>
                        <div class="vote-results">
                            <?php foreach ($results as $optId => $res): 
                                $pct = $totalVotes > 0 ? round(($res['count'] / $totalVotes) * 100) : 0;
                                $isWinner = ($res['count'] == $maxVotes && $maxVotes > 0);
                                $isUserVote = ($optId == $userOptionId);
                            ?>
                                <div class="vote-result-item">
                                    <div class="vote-result-header">
                                        <div class="flex items-center gap-2">
                                            <span class="vote-result-label <?= $isWinner ? 'font-bold text-neutral-900' : '' ?>">
                                                <?= e($res['text']) ?>
                                            </span>
                                            <?php if ($isUserVote): ?>
                                                <span class="badge badge-member" style="padding: 2px 6px; font-size: 10px;">Your Vote</span>
                                            <?php endif; ?>
                                            <?php if ($isWinner): ?>
                                                <span title="Current Leader">👑</span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="vote-result-count"><?= $res['count'] ?> votes</span>
                                    </div>
                                    <div class="vote-result-bar">
                                        <!-- Inline width style for chart fill -->
                                        <div class="vote-result-fill <?= $isWinner ? 'winner' : '' ?>" style="width: <?= $pct ?>%;"></div>
                                    </div>
                                    <div class="vote-result-percentage"><?= $pct ?>%</div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-center text-sm text-neutral-500 mt-6 pt-4 border-t border-neutral-100">
                            Total votes cast: <strong><?= $totalVotes ?></strong>
                        </p>
                    <?php endif; ?>
                </div>

                <?php if ($hasVoted && $isAcceptingVotes): ?>
                    <div class="alert alert-info mt-6">
                        <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <p>You have already voted on this topic. Waiting for deadline to close.</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <?php if ($isAdmin): ?>
        <div class="card-footer" style="background: var(--neutral-50); justify-content: flex-end;">
            <button type="button" class="btn btn-danger btn-sm delete-trigger"
                    data-action="/pages/vote_delete.php"
                    data-id="<?= $voteId ?>"
                    data-name="vote_id"
                    data-label="this vote">
                Remove Vote
            </button>
        </div>
        <?php endif; ?>
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
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
