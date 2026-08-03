<?php
/**
 * Member Portal - Dashboard / Overview page.
 * Profile details live in member_profile.php, billing in member_payments.php.
 */
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Member.php';

if (!Auth::hasRole('member')) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$memberObj = new Member();
$member = $memberObj->getById($_SESSION['ref_id']);

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT COUNT(*) AS c FROM attendance WHERE member_id = :id");
$stmt->execute([':id' => $_SESSION['ref_id']]);
$totalVisits = $stmt->fetch()['c'];

$daysLeft = (int)floor((strtotime($member['expire_date']) - strtotime(date('Y-m-d'))) / 86400);
$planActive = $member['status'] === 'Active';

$pageTitle = "Dashboard";
require_once __DIR__ . '/../includes/portal_header.php';
?>

<div style="margin-bottom: 1.75rem;">
    <h2 style="font-family: var(--font-heading); font-size: 1.6rem; margin-bottom: 0.25rem;">Welcome back, <?php echo htmlspecialchars($member['first_name']); ?> 👋</h2>
    <p style="color: var(--text-muted); margin: 0;">Here's your membership at a glance - <?php echo htmlspecialchars($member['member_code']); ?></p>
</div>

<?php if ($member['status'] === 'Pending'): ?>
    <div class="glass-card" style="text-align:center; padding:3rem 2rem; margin-bottom:2rem; border: 1px solid rgba(245, 158, 11, 0.35); background: rgba(245, 158, 11, 0.06);">
        <div style="font-size:2.5rem; margin-bottom:1rem;">⏳</div>
        <h2 style="font-family:var(--font-heading); margin-bottom:0.5rem;">Your Membership is Pending Approval</h2>
        <p style="color:var(--text-muted); max-width:520px; margin:0 auto;">
            Thanks for joining! An Admin will review and activate your membership shortly. You'll get full access once it's approved.
        </p>
    </div>
<?php elseif (!$planActive): ?>
    <div class="glass-card" style="text-align:center; padding:3rem 2rem; margin-bottom:2rem; border: 1px solid rgba(239, 68, 68, 0.35); background: rgba(239, 68, 68, 0.06);">
        <div style="font-size:2.5rem; margin-bottom:1rem;">🚫</div>
        <h2 style="font-family:var(--font-heading); margin-bottom:0.5rem;">Your Membership is <?php echo htmlspecialchars($member['status']); ?></h2>
        <p style="color:var(--text-muted);">Please visit the front desk or contact the Admin to renew or reactivate your plan.</p>
    </div>
<?php else: ?>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">💪</div>
            <div class="stat-info"><h4>Current Plan</h4><div class="value" style="font-size:1.1rem;"><?php echo htmlspecialchars($member['plan_name']); ?></div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🧑‍🏫</div>
            <div class="stat-info"><h4>My Trainer</h4><div class="value" style="font-size:1.1rem;"><?php echo htmlspecialchars($member['trainer_name'] ?? 'Not Assigned'); ?></div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-info"><h4>Plan Renews In</h4><div class="value"><?php echo $daysLeft >= 0 ? $daysLeft . ' days' : 'Overdue'; ?></div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🏃</div>
            <div class="stat-info"><h4>Total Visits</h4><div class="value"><?php echo $totalVisits; ?></div></div>
        </div>
    </div>

    <?php if ($daysLeft <= 7): ?>
        <div class="glass-card" style="margin-bottom:2rem; padding:1.25rem 1.5rem; border: 1px solid rgba(245, 158, 11, 0.35); background: rgba(245, 158, 11, 0.08); display:flex; align-items:center; gap:1rem;">
            <span style="font-size:1.5rem;">🔔</span>
            <div>
                <strong>Your plan is <?php echo $daysLeft < 0 ? 'overdue for renewal' : "renewing in $daysLeft day(s)"; ?>.</strong>
                <div style="color:var(--text-muted); font-size:0.88rem;">Head over to <a href="<?php echo BASE_URL; ?>/member-portal/member_payments.php" style="color: var(--primary);">My Payments</a> to see your billing details, or visit the front desk to renew.</div>
            </div>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        <a href="<?php echo BASE_URL; ?>/member-portal/member_profile.php" class="glass-card" style="text-decoration:none; color:inherit; display:flex; align-items:center; gap:1rem;">
            <div style="font-size:2rem;">👤</div>
            <div>
                <h3 style="font-family:var(--font-heading); margin-bottom:0.25rem;">My Profile</h3>
                <p style="color:var(--text-muted); font-size:0.85rem; margin:0;">View your personal details and membership info.</p>
            </div>
        </a>
        <a href="<?php echo BASE_URL; ?>/member-portal/member_payments.php" class="glass-card" style="text-decoration:none; color:inherit; display:flex; align-items:center; gap:1rem;">
            <div style="font-size:2rem;">💳</div>
            <div>
                <h3 style="font-family:var(--font-heading); margin-bottom:0.25rem;">My Payments</h3>
                <p style="color:var(--text-muted); font-size:0.85rem; margin:0;">See your payment history and attendance log.</p>
            </div>
        </a>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/portal_footer.php'; ?>
