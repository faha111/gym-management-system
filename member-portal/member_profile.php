<?php
/**
 * Member Portal - My Profile page (personal information only)
 */
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Member.php';

if (!Auth::hasRole('member')) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$memberObj = new Member();
$member = $memberObj->getById($_SESSION['ref_id']);

$pageTitle = "My Profile";
require_once __DIR__ . '/../includes/portal_header.php';
?>

<div style="margin-bottom: 1.75rem;">
    <h2 style="font-family: var(--font-heading); font-size: 1.6rem; margin-bottom: 0.25rem;">My Profile</h2>
    <p style="color: var(--text-muted); margin: 0;">Your personal information and membership summary.</p>
</div>

<div style="display: grid; grid-template-columns: 1fr 1.4fr; gap: 2rem; align-items: start;">
    <!-- Photo + Identity -->
    <div class="glass-card" style="text-align:center; padding: 2.5rem 1.5rem;">
        <img src="<?php echo BASE_URL; ?>/assets/uploads/<?php echo htmlspecialchars($member['photo'] ?? 'default_user.png'); ?>" alt="" style="width:110px; height:110px; border-radius:50%; object-fit:cover; background:#334155; margin-bottom:1.25rem; border: 3px solid var(--border-color);" onerror="this.src='<?php echo BASE_URL; ?>/assets/uploads/default_user.png'">
        <h3 style="font-family:var(--font-heading); margin-bottom:0.4rem;"><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></h3>
        <span class="badge <?php echo $member['status'] === 'Active' ? 'badge-active' : 'badge-pending'; ?>"><?php echo htmlspecialchars($member['status']); ?></span>
        <p style="color:var(--text-muted); font-size:0.85rem; margin-top:1rem;">Member Code<br><strong style="color:var(--text-main); font-size:1rem;"><?php echo htmlspecialchars($member['member_code']); ?></strong></p>
    </div>

    <!-- Personal Details -->
    <div class="glass-card">
        <h3 style="font-family: var(--font-heading); margin-bottom: 1.5rem;">Personal Details</h3>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.4rem; font-size:0.92rem;">
            <div><span style="color:var(--text-muted);">Email Address</span><br><strong><?php echo htmlspecialchars($member['email']); ?></strong></div>
            <div><span style="color:var(--text-muted);">Phone Number</span><br><strong><?php echo htmlspecialchars($member['phone']); ?></strong></div>
            <div><span style="color:var(--text-muted);">Gender</span><br><strong><?php echo htmlspecialchars($member['gender']); ?></strong></div>
            <div><span style="color:var(--text-muted);">Date of Birth</span><br><strong><?php echo !empty($member['dob']) ? date('d M Y', strtotime($member['dob'])) : '-'; ?></strong></div>
            <div style="grid-column: 1 / -1;"><span style="color:var(--text-muted);">Address</span><br><strong><?php echo htmlspecialchars($member['address'] ?: '-'); ?></strong></div>
        </div>

        <hr style="border-color: var(--border-color); margin: 1.75rem 0;">

        <h3 style="font-family: var(--font-heading); margin-bottom: 1.5rem;">Membership Summary</h3>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.4rem; font-size:0.92rem;">
            <div><span style="color:var(--text-muted);">Current Plan</span><br><strong><?php echo htmlspecialchars($member['plan_name']); ?></strong></div>
            <div><span style="color:var(--text-muted);">Assigned Trainer</span><br><strong><?php echo htmlspecialchars($member['trainer_name'] ?? 'Not Assigned'); ?></strong></div>
            <div><span style="color:var(--text-muted);">Joined On</span><br><strong><?php echo date('d M Y', strtotime($member['join_date'])); ?></strong></div>
            <div><span style="color:var(--text-muted);">Plan Valid Until</span><br><strong><?php echo date('d M Y', strtotime($member['expire_date'])); ?></strong></div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/portal_footer.php'; ?>
