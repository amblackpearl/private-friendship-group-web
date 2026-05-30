<?php
/**
 * Navigation Bar Include
 * 
 * Responsive navigation with:
 * - Logo/brand
 * - Main navigation links
 * - Admin panel link (admin only)
 * - User info and logout
 * - Mobile hamburger menu
 * 
 * Requires an active session with user data.
 */

$currentPage = basename($_SERVER['PHP_SELF'], '.php');

function isActive($page) {
    global $currentPage;
    return $currentPage === $page ? 'active' : '';
}

function e_nav($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>
<nav class="navbar" id="navbar">
    <div class="nav-container">
        <a href="/pages/dashboard.php" class="nav-brand">
            <svg width="32" height="32" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="24" cy="24" r="24" fill="url(#nav-logo-gradient)"/>
                <path d="M16 28c0-4.418 3.582-8 8-8s8 3.582 8 8" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                <circle cx="24" cy="16" r="4" stroke="white" stroke-width="2.5"/>
                <path d="M10 32c0-3.314 2.686-6 6-6" stroke="white" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                <circle cx="13" cy="22" r="3" stroke="white" stroke-width="2" opacity="0.6"/>
                <path d="M38 32c0-3.314-2.686-6-6-6" stroke="white" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                <circle cx="35" cy="22" r="3" stroke="white" stroke-width="2" opacity="0.6"/>
                <defs>
                    <linearGradient id="nav-logo-gradient" x1="0" y1="0" x2="48" y2="48">
                        <stop stop-color="#6366f1"/>
                        <stop offset="1" stop-color="#8b5cf6"/>
                    </linearGradient>
                </defs>
            </svg>
            <span class="nav-brand-text"><?= e_nav(APP_NAME) ?></span>
        </a>

        <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation">
            <span class="hamburger"></span>
        </button>

        <div class="nav-menu" id="nav-menu">
            <ul class="nav-links">
                <li>
                    <a href="/pages/dashboard.php" class="nav-link <?= isActive('dashboard') ?>" id="nav-dashboard">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="nav-icon">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="/pages/gallery.php" class="nav-link <?= isActive('gallery') ?>" id="nav-gallery">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="nav-icon">
                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                        </svg>
                        <span>Gallery</span>
                    </a>
                </li>
                <li>
                    <a href="/pages/votes.php" class="nav-link <?= isActive('votes') ?>" id="nav-votes">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="nav-icon">
                            <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"/>
                            <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"/>
                        </svg>
                        <span>Votes</span>
                    </a>
                </li>
                <li>
                    <a href="/pages/agendas.php" class="nav-link <?= isActive('agendas') ?>" id="nav-agendas">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="nav-icon">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        <span>Trip Agenda</span>
                    </a>
                </li>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <li>
                    <a href="/admin/index.php" class="nav-link <?= isActive('index') && strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? 'active' : '' ?>" id="nav-admin">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="nav-icon">
                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                        </svg>
                        <span>Admin</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <div class="nav-user">
                <div class="nav-user-info">
                    <div class="nav-avatar">
                        <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div class="nav-user-details">
                        <span class="nav-user-name"><?= e_nav($_SESSION['user_name'] ?? 'User') ?></span>
                        <span class="nav-user-role"><?= e_nav(ucfirst($_SESSION['user_role'] ?? 'member')) ?></span>
                    </div>
                </div>
                <a href="/auth/logout.php" class="btn btn-ghost btn-sm" id="logout-btn" title="Logout">
                    <svg viewBox="0 0 20 20" fill="currentColor" class="btn-icon-only">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</nav>
<main class="main-content">
