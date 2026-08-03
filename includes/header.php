<?php
/**
 * ==============================================================================
 * CST 226-2 WEB APPLICATION DEVELOPMENT ASSIGNMENT - GYM MANAGEMENT SYSTEM
 * Global Header Component with Mobile Drawer & Sync Dark/Light Theme Switcher
 * ==============================================================================
 * OOP Concept: Modular Component Inclusion & Session State Management
 * - Handles active page tab highlighting dynamically using $_SERVER['PHP_SELF']
 * - Implements flash notification banner renderer from alerts.php
 * ==============================================================================
 */

// Buffer all output from here on. Pages that include this header do their
// action handling (check-in, delete, approve, etc.) AFTER this include, and
// those actions redirect with header(). Without buffering, the HTML this
// file prints below would already be sent to the browser by the time that
// header() call runs, causing a "headers already sent" warning and breaking
// the redirect. Buffering holds everything until the script ends, so a
// header() call anywhere later in the request still works correctly.
ob_start();

// Start session if not already initialized
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/alerts.php';
require_once __DIR__ . '/../classes/Auth.php';

// Every page that includes this header is an Admin-only backend page.
// If there's no logged-in Admin session, bounce to the shared login page.
if (!Auth::hasRole('admin')) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

// Dynamically retrieve current filename to set 'active' class on menu items
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . " - PulseFit Gym" : "PulseFit Gym Management System"; ?></title>
    
    <!-- External Google Fonts & Modern Design System Stylesheet -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">

    <!-- Instant Theme Synchronization Script (Prevents screen flickering during navigation) -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('gym_theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
</head>
<body>

<!-- Display Session Alert Messages if available -->
<?php displayAlert(); ?>

<div class="app-container">
    <!-- Sidebar Navigation Drawer -->
    <aside class="sidebar">
        <!-- Brand Logo & Home Link -->
        <a href="<?php echo BASE_URL; ?>/index.php" class="brand-logo">
            <img src="<?php echo BASE_URL; ?>/assets/img/logo_icon.svg" alt="" style="height: 36px; width: auto;">
            <span>PulseFit Gym</span>
        </a>

        <!-- Main Navigation Links -->
        <ul class="nav-menu">
            <li class="nav-item <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/index.php">
                    <span class="nav-icon">🌐</span>
                    <span>Public Home</span>
                </a>
            </li>
            <li class="nav-item <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/dashboard.php">
                    <span class="nav-icon">📊</span>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item <?php echo in_array($currentPage, ['members.php', 'member_add.php', 'member_edit.php']) ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/members/members.php">
                    <span class="nav-icon">👥</span>
                    <span>Members</span>
                </a>
            </li>
            <li class="nav-item <?php echo $currentPage === 'plans.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/plans/plans.php">
                    <span class="nav-icon">💳</span>
                    <span>Membership Plans</span>
                </a>
            </li>
            <li class="nav-item <?php echo $currentPage === 'trainers.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/trainers/trainers.php">
                    <span class="nav-icon">🏋️‍♂️</span>
                    <span>Trainers & Staff</span>
                </a>
            </li>
            <li class="nav-item <?php echo $currentPage === 'attendance.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/attendance/attendance.php">
                    <span class="nav-icon">📅</span>
                    <span>Attendance Log</span>
                </a>
            </li>
            <li class="nav-item <?php echo $currentPage === 'payments.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/payments/payments.php">
                    <span class="nav-icon">💰</span>
                    <span>Payments & Billing</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content Body Wrapper -->
    <main class="main-content">
        <!-- Top Navigation Header -->
        <header class="top-header">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <!-- Mobile Drawer Toggle Button -->
                <button class="mobile-menu-toggle" id="mobileMenuBtn" aria-label="Toggle Navigation Menu">☰</button>
                <h1 class="page-title"><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : "Dashboard"; ?></h1>
            </div>

            <div class="header-right">
                <!-- Synchronized Dark / Light Theme Toggle Switcher -->
                <button class="theme-toggle-btn" id="themeToggleBtn" type="button">
                    <span id="themeIcon">🌙</span>
                    <span id="themeText">Dark Mode</span>
                </button>

                <!-- Current User Profile Badge -->
                <div class="user-profile">
                    <span style="font-weight:600; font-size:0.85rem;"><?php echo htmlspecialchars($_SESSION['email'] ?? 'Admin'); ?></span>
                    <span class="badge badge-active">Online</span>
                </div>
                <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="btn btn-sm btn-danger">Logout</a>
            </div>
        </header>
