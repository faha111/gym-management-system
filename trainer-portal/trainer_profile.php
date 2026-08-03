<?php
/**
 * Trainer Portal - My Profile page (personal & professional information)
 */
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Trainer.php';

if (!Auth::hasRole('trainer')) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$trainerObj = new Trainer();
$trainer = $trainerObj->getById($_SESSION['ref_id']);

$pageTitle = "My Profile";
require_once __DIR__ . '/../includes/portal_header.php';
?>

<div style="margin-bottom: 1.75rem;">
    <h2 style="font-family: var(--font-heading); font-size: 1.6rem; margin-bottom: 0.25rem;">My Profile</h2>
    <p style="color: var(--text-muted); margin: 0;">Your personal and professional details.</p>
</div>

<div style="display: grid; grid-template-columns: 1fr 1.4fr; gap: 2rem; align-items: start;">
    <!-- Photo + Identity -->
    <div class="glass-card" style="text-align:center; padding: 2.5rem 1.5rem;">
        <img src="<?php echo BASE_URL; ?>/assets/uploads/<?php echo htmlspecialchars($trainer['photo'] ?? 'default_user.png'); ?>" alt="" style="width:110px; height:110px; border-radius:50%; object-fit:cover; background:#334155; margin-bottom:1.25rem; border: 3px solid var(--border-color);" onerror="this.src='<?php echo BASE_URL; ?>/assets/uploads/default_user.png'">
        <h3 style="font-family:var(--font-heading); margin-bottom:0.4rem;"><?php echo htmlspecialchars($trainer['full_name']); ?></h3>
        <span class="badge <?php echo $trainer['status'] === 'Active' ? 'badge-active' : 'badge-pending'; ?>"><?php echo htmlspecialchars($trainer['status']); ?></span>
        <p style="color:var(--text-muted); font-size:0.85rem; margin-top:1rem;">Specialization<br><strong style="color:var(--text-main); font-size:1rem;"><?php echo htmlspecialchars($trainer['specialization']); ?></strong></p>
    </div>

    <!-- Details -->
    <div class="glass-card">
        <h3 style="font-family: var(--font-heading); margin-bottom: 1.5rem;">Contact Details</h3>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.4rem; font-size:0.92rem;">
            <div><span style="color:var(--text-muted);">Email Address</span><br><strong><?php echo htmlspecialchars($trainer['email']); ?></strong></div>
            <div><span style="color:var(--text-muted);">Phone Number</span><br><strong><?php echo htmlspecialchars($trainer['phone']); ?></strong></div>
        </div>

        <hr style="border-color: var(--border-color); margin: 1.75rem 0;">

        <h3 style="font-family: var(--font-heading); margin-bottom: 1.5rem;">Professional Details</h3>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.4rem; font-size:0.92rem;">
            <div><span style="color:var(--text-muted);">Specialization</span><br><strong><?php echo htmlspecialchars($trainer['specialization']); ?></strong></div>
            <div><span style="color:var(--text-muted);">Joined On</span><br><strong><?php echo date('d M Y', strtotime($trainer['joining_date'])); ?></strong></div>
            <div><span style="color:var(--text-muted);">Employment Status</span><br><strong><?php echo htmlspecialchars($trainer['status']); ?></strong></div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/portal_footer.php'; ?>
