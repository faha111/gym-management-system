<?php
/**
 * Trainer Portal - Dashboard / Overview page.
 * Profile lives in trainer_profile.php, assigned members in trainer_clients.php.
 */
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Trainer.php';

if (!Auth::hasRole('trainer')) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$trainerObj = new Trainer();
$trainer = $trainerObj->getById($_SESSION['ref_id']);

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT status FROM members WHERE trainer_id = :tid");
$stmt->execute([':tid' => $_SESSION['ref_id']]);
$assignedMembers = $stmt->fetchAll();
$activeAssigned = count(array_filter($assignedMembers, fn($m) => $m['status'] === 'Active'));

$pageTitle = "Dashboard";
require_once __DIR__ . '/../includes/portal_header.php';
?>

<div style="margin-bottom: 1.75rem;">
    <h2 style="font-family: var(--font-heading); font-size: 1.6rem; margin-bottom: 0.25rem;">Welcome back, <?php echo htmlspecialchars($trainer['full_name']); ?> 👋</h2>
    <p style="color: var(--text-muted); margin: 0;">Here's what's happening with your trainer profile.</p>
</div>

<?php if ($trainer['status'] === 'Pending'): ?>
    <div class="glass-card" style="text-align:center; padding:3rem 2rem; margin-bottom:2rem; border: 1px solid rgba(245, 158, 11, 0.35); background: rgba(245, 158, 11, 0.06);">
        <div style="font-size:2.5rem; margin-bottom:1rem;">⏳</div>
        <h2 style="font-family:var(--font-heading); margin-bottom:0.5rem;">Your Trainer Profile is Pending Approval</h2>
        <p style="color:var(--text-muted); max-width:520px; margin:0 auto;">
            Thanks for registering, <?php echo htmlspecialchars($trainer['full_name']); ?>! An Admin needs to review and approve your trainer profile before you can be assigned members or appear on the public site. Please check back soon.
        </p>
    </div>
<?php elseif ($trainer['status'] !== 'Active'): ?>
    <div class="glass-card" style="text-align:center; padding:3rem 2rem; margin-bottom:2rem; border: 1px solid rgba(239, 68, 68, 0.35); background: rgba(239, 68, 68, 0.06);">
        <div style="font-size:2.5rem; margin-bottom:1rem;">🚫</div>
        <h2 style="font-family:var(--font-heading); margin-bottom:0.5rem;">Your Account is <?php echo htmlspecialchars($trainer['status']); ?></h2>
        <p style="color:var(--text-muted);">Please contact the gym Admin if you believe this is a mistake.</p>
    </div>
<?php else: ?>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-info"><h4>Assigned Members</h4><div class="value"><?php echo count($assignedMembers); ?></div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-info"><h4>Active Clients</h4><div class="value"><?php echo $activeAssigned; ?></div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🎯</div>
            <div class="stat-info"><h4>Specialization</h4><div class="value" style="font-size:1.1rem;"><?php echo htmlspecialchars($trainer['specialization']); ?></div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-info"><h4>Trainer Since</h4><div class="value" style="font-size:1.1rem;"><?php echo date('M Y', strtotime($trainer['joining_date'])); ?></div></div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        <a href="<?php echo BASE_URL; ?>/trainer-portal/trainer_profile.php" class="glass-card" style="text-decoration:none; color:inherit; display:flex; align-items:center; gap:1rem;">
            <div style="font-size:2rem;">👤</div>
            <div>
                <h3 style="font-family:var(--font-heading); margin-bottom:0.25rem;">My Profile</h3>
                <p style="color:var(--text-muted); font-size:0.85rem; margin:0;">View your personal and professional details.</p>
            </div>
        </a>
        <a href="<?php echo BASE_URL; ?>/trainer-portal/trainer_clients.php" class="glass-card" style="text-decoration:none; color:inherit; display:flex; align-items:center; gap:1rem;">
            <div style="font-size:2rem;">👥</div>
            <div>
                <h3 style="font-family:var(--font-heading); margin-bottom:0.25rem;">My Clients</h3>
                <p style="color:var(--text-muted); font-size:0.85rem; margin:0;">See the members currently assigned to you.</p>
            </div>
        </a>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/portal_footer.php'; ?>
