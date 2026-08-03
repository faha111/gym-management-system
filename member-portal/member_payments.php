<?php
/**
 * Member Portal - My Payments page (billing + attendance/usage log)
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
$stmt = $db->prepare("SELECT * FROM payments WHERE member_id = :id ORDER BY payment_date DESC");
$stmt->execute([':id' => $_SESSION['ref_id']]);
$payments = $stmt->fetchAll();

$stmt2 = $db->prepare("SELECT * FROM attendance WHERE member_id = :id ORDER BY date DESC, check_in DESC LIMIT 15");
$stmt2->execute([':id' => $_SESSION['ref_id']]);
$attendance = $stmt2->fetchAll();

$totalPaid = array_sum(array_column(array_filter($payments, fn($p) => $p['payment_status'] === 'Paid'), 'amount'));
$totalPending = array_sum(array_column(array_filter($payments, fn($p) => $p['payment_status'] === 'Pending'), 'amount'));

$pageTitle = "My Payments";
require_once __DIR__ . '/../includes/portal_header.php';
?>

<div style="margin-bottom: 1.75rem;">
    <h2 style="font-family: var(--font-heading); font-size: 1.6rem; margin-bottom: 0.25rem;">My Payments</h2>
    <p style="color: var(--text-muted); margin: 0;">Your billing history and gym attendance log.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-info"><h4>Total Paid</h4><div class="value">Rs. <?php echo number_format($totalPaid, 0); ?></div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div class="stat-info"><h4>Pending Amount</h4><div class="value">Rs. <?php echo number_format($totalPending, 0); ?></div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-info"><h4>Plan Valid Until</h4><div class="value" style="font-size:1.1rem;"><?php echo date('d M Y', strtotime($member['expire_date'])); ?></div></div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1.3fr 1fr; gap: 2rem; align-items: start;">
    <!-- Payment History -->
    <div class="glass-card">
        <h3 style="font-family: var(--font-heading); margin-bottom: 1.25rem;">Payment History</h3>
        <?php if (empty($payments)): ?>
            <p style="color:var(--text-muted); text-align:center; padding:2rem 0;">No payments recorded yet.</p>
        <?php else: ?>
            <?php foreach ($payments as $p): ?>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:0.85rem 0; border-bottom:1px solid var(--border-color); font-size:0.9rem;">
                    <div>
                        <div><?php echo date('d M Y', strtotime($p['payment_date'])); ?></div>
                        <div style="color:var(--text-muted); font-size:0.8rem;"><?php echo htmlspecialchars($p['payment_method']); ?> <?php echo !empty($p['notes']) ? '- ' . htmlspecialchars($p['notes']) : ''; ?></div>
                    </div>
                    <div style="text-align:right;">
                        <div><strong>Rs. <?php echo number_format($p['amount'], 0); ?></strong></div>
                        <span class="badge <?php echo $p['payment_status'] === 'Paid' ? 'badge-active' : 'badge-pending'; ?>" style="font-size:0.72rem;"><?php echo htmlspecialchars($p['payment_status']); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Attendance -->
    <div class="glass-card">
        <h3 style="font-family: var(--font-heading); margin-bottom: 1.25rem;">Recent Attendance</h3>
        <?php if (empty($attendance)): ?>
            <p style="color:var(--text-muted); text-align:center; padding:2rem 0;">No check-ins recorded yet.</p>
        <?php else: ?>
            <?php foreach ($attendance as $a): ?>
                <div style="padding:0.7rem 0; border-bottom:1px solid var(--border-color); font-size:0.87rem;">
                    <div><?php echo date('d M Y', strtotime($a['date'])); ?></div>
                    <div style="color:var(--text-muted); font-size:0.8rem;">
                        In <?php echo date('h:i A', strtotime($a['check_in'])); ?><?php echo $a['check_out'] ? ' — Out ' . date('h:i A', strtotime($a['check_out'])) : ' (still checked in)'; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/portal_footer.php'; ?>
