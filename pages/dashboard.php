<?php
/**
 * Dashboard Page
 * 
 * Main landing page after login showing:
 * - Welcome message with user name
 * - Recent photos preview
 * - Active votes section
 * - Recent trip agendas
 */

require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Fetch recent photos (6 most recent, non-deleted)
$stmtPhotos = $pdo->prepare(
    'SELECT p.*, u.name AS uploader_name
     FROM photos p
     JOIN users u ON p.user_id = u.id
     WHERE p.deleted_at IS NULL
     ORDER BY p.created_at DESC
     LIMIT 6'
);
$stmtPhotos->execute();
$recentPhotos = $stmtPhotos->fetchAll();

// Fetch active votes (5 most recent, non-deleted, active status)
$stmtVotes = $pdo->prepare(
    'SELECT v.*, u.name AS creator_name,
            (SELECT COUNT(*) FROM vote_responses vr WHERE vr.vote_id = v.id) AS total_voters
     FROM votes v
     JOIN users u ON v.created_by = u.id
     WHERE v.deleted_at IS NULL
       AND v.status = "active"
       AND v.deadline > NOW()
     ORDER BY v.created_at DESC
     LIMIT 5'
);
$stmtVotes->execute();
$activeVotes = $stmtVotes->fetchAll();

// Fetch recent trip agendas (5 most recent, non-deleted)
$stmtAgendas = $pdo->prepare(
    'SELECT ta.*, u.name AS submitter_name
     FROM trip_agendas ta
     JOIN users u ON ta.user_id = u.id
     WHERE ta.deleted_at IS NULL
     ORDER BY ta.created_at DESC
     LIMIT 5'
);
$stmtAgendas->execute();
$recentAgendas = $stmtAgendas->fetchAll();

// Stats
$totalPhotos = $pdo->query('SELECT COUNT(*) FROM photos WHERE deleted_at IS NULL')->fetchColumn();
$totalVotes = $pdo->query('SELECT COUNT(*) FROM votes WHERE deleted_at IS NULL')->fetchColumn();
$totalAgendas = $pdo->query('SELECT COUNT(*) FROM trip_agendas WHERE deleted_at IS NULL')->fetchColumn();

$pageTitle = 'Dashboard';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="container">
    <!-- Welcome Banner -->
    <div class="dashboard-welcome animate-slide-in">
        <h1>Welcome back, <?= e($_SESSION['user_name']) ?>! 👋</h1>
        <p>Here's what's happening in your friendship group today.</p>
    </div>

    <!-- Quick Stats -->
    <div class="admin-stats mb-8 animate-fade-in">
        <div class="stat-card">
            <div class="stat-value"><?= (int)$totalPhotos ?></div>
            <div class="stat-label">Photos Shared</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= (int)$totalVotes ?></div>
            <div class="stat-label">Total Votes</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= (int)$totalAgendas ?></div>
            <div class="stat-label">Trip Proposals</div>
        </div>
    </div>

    <!-- Recent Photos Section -->
    <div class="dashboard-section">
        <div class="section-header">
            <h2>📸 Recent Photos</h2>
            <a href="/pages/gallery.php">View All →</a>
        </div>

        <?php if (empty($recentPhotos)): ?>
            <div class="card">
                <div class="empty-state">
                    <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <h3>No photos uploaded yet</h3>
                    <p>Be the first to share a memory with your group!</p>
                    <a href="/pages/photo_create.php" class="btn btn-primary">
                        <span>Upload Photo</span>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="gallery-grid">
                <?php foreach ($recentPhotos as $index => $photo): ?>
                    <div class="photo-card stagger-item">
                        <div class="photo-card-image-wrapper">
                            <img src="/uploads/gallery/<?= e($photo['file_path']) ?>" alt="<?= e($photo['caption']) ?>" class="photo-card-image" loading="lazy">
                        </div>
                        <div class="photo-card-body">
                            <div class="photo-card-caption"><?= e($photo['caption']) ?></div>
                            <div class="photo-card-meta">
                                <span>By <?= e($photo['uploader_name']) ?></span>
                                <span><?= date('M j, Y', strtotime($photo['created_at'])) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-4 text-center">
                <a href="/pages/photo_create.php" class="btn btn-primary">Upload New Photo</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Active Votes Section -->
    <div class="dashboard-section">
        <div class="section-header">
            <h2>🗳️ Active Votes</h2>
            <a href="/pages/votes.php">View All →</a>
        </div>

        <?php if (empty($activeVotes)): ?>
            <div class="card">
                <div class="empty-state">
                    <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <h3>No active votes right now</h3>
                    <p>Start a group decision by creating a new vote!</p>
                    <a href="/pages/vote_create.php" class="btn btn-primary">
                        <span>Create Vote</span>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="dashboard-grid">
                <?php foreach ($activeVotes as $index => $vote): ?>
                    <div class="vote-card stagger-item">
                        <div class="vote-card-header">
                            <div>
                                <div class="vote-card-title"><?= e($vote['title']) ?></div>
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
                            <a href="/pages/vote_detail.php?id=<?= (int)$vote['id'] ?>" class="btn btn-primary btn-sm">Vote Now</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-4 text-center">
                <a href="/pages/vote_create.php" class="btn btn-primary">Create New Vote</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Trip Agendas Section -->
    <div class="dashboard-section">
        <div class="section-header">
            <h2>🗺️ Trip Proposals</h2>
            <a href="/pages/agendas.php">View All →</a>
        </div>

        <?php if (empty($recentAgendas)): ?>
            <div class="card">
                <div class="empty-state">
                    <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <h3>No trip agenda submitted yet</h3>
                    <p>Propose your next adventure with the group!</p>
                    <a href="/pages/agenda_create.php" class="btn btn-primary">
                        <span>Submit Agenda</span>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="agenda-grid">
                <?php foreach ($recentAgendas as $index => $agenda): ?>
                    <div class="agenda-card stagger-item">
                        <div class="agenda-card-accent"></div>
                        <div class="agenda-card-body">
                            <div class="agenda-card-destination"><?= e($agenda['destination']) ?></div>
                            <div class="agenda-card-info">
                                <div class="agenda-info-item">
                                    <span class="agenda-info-label">Date</span>
                                    <span class="agenda-info-value"><?= date('M j, Y', strtotime($agenda['proposed_date'])) ?></span>
                                </div>
                                <div class="agenda-info-item">
                                    <span class="agenda-info-label">Budget</span>
                                    <span class="agenda-info-value">Rp <?= number_format($agenda['estimated_budget'], 0, ',', '.') ?></span>
                                </div>
                            </div>
                            <div class="agenda-card-description"><?= e($agenda['description']) ?></div>
                        </div>
                        <div class="agenda-card-footer">
                            <span>By <?= e($agenda['submitter_name']) ?></span>
                            <a href="/pages/agenda_detail.php?id=<?= (int)$agenda['id'] ?>" class="btn btn-ghost btn-sm">View Details →</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-4 text-center">
                <a href="/pages/agenda_create.php" class="btn btn-primary">Submit New Agenda</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
