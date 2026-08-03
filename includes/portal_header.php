<?php
/**
 * Shared Portal Header - Used by trainer_portal.php and member_portal.php
 * Same visual system as the Admin dashboard (sidebar, glass cards, stat tiles)
 * but scoped to a single logged-in Trainer or Member.
 */

// See includes/header.php for why this is needed: pages that include this
// portal header run their own action handling (and header() redirects)
// AFTER this include, so output must be buffered until the script ends.
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/alerts.php';
require_once __DIR__ . '/../classes/Auth.php';

// This header is shared by both portals - allow either role in
if (!Auth::hasRole('trainer') && !Auth::hasRole('member')) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$role = $_SESSION['role'];
$currentPage = basename($_SERVER['SCRIPT_NAME']);

if ($role === 'trainer') {
    $navItems = [
        ['icon' => '📊', 'label' => 'Dashboard',  'match' => 'trainer_portal.php',  'file' => 'trainer-portal/trainer_portal.php'],
        ['icon' => '👤', 'label' => 'My Profile',  'match' => 'trainer_profile.php', 'file' => 'trainer-portal/trainer_profile.php'],
        ['icon' => '👥', 'label' => 'My Clients',  'match' => 'trainer_clients.php', 'file' => 'trainer-portal/trainer_clients.php'],
    ];
} else {
    $navItems = [
        ['icon' => '📊', 'label' => 'Dashboard',    'match' => 'member_portal.php',   'file' => 'member-portal/member_portal.php'],
        ['icon' => '👤', 'label' => 'My Profile',    'match' => 'member_profile.php',  'file' => 'member-portal/member_profile.php'],
        ['icon' => '💳', 'label' => 'My Payments',   'match' => 'member_payments.php', 'file' => 'member-portal/member_payments.php'],
    ];
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . " - PulseFit Gym" : "PulseFit Gym"; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('gym_theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
</head>
<body>
<?php displayAlert(); ?>

<div class="app-container">
    <aside class="sidebar">
        <a href="<?php echo BASE_URL; ?>/index.php" class="brand-logo">
            <img src="<?php echo BASE_URL; ?>/assets/img/logo_icon.svg" alt="" style="height: 36px; width: auto;">
            <span>PulseFit Gym</span>
        </a>
        <ul class="nav-menu">
            <?php foreach ($navItems as $item): ?>
                <li class="nav-item <?php echo $currentPage === $item['match'] ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL . '/' . $item['file']; ?>">
                        <span class="nav-icon"><?php echo $item['icon']; ?></span>
                        <span><?php echo $item['label']; ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>/index.php">
                    <span class="nav-icon">🌐</span>
                    <span>Public Home</span>
                </a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <button class="mobile-menu-toggle" id="mobileMenuBtn" aria-label="Toggle Navigation Menu">☰</button>
                <h1 class="page-title"><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : "My Portal"; ?></h1>
            </div>
            <div class="header-right">
                <button class="theme-toggle-btn" id="themeToggleBtn" type="button">
                    <span id="themeIcon">🌙</span>
                    <span id="themeText">Dark Mode</span>
                </button>
                <div class="user-profile">
                    <span style="font-weight:600; font-size:0.85rem;"><?php echo htmlspecialchars($_SESSION['email']); ?></span>
                    <span class="badge badge-active">Online</span>
                </div>
                <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="btn btn-sm btn-danger">Logout</a>
            </div>
        </header>
