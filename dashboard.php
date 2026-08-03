<?php
/**
 * Admin Dashboard Overview Page
 */
$pageTitle = "Admin Dashboard";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/Member.php';
require_once __DIR__ . '/classes/Trainer.php';
require_once __DIR__ . '/classes/Plan.php';
require_once __DIR__ . '/classes/Payment.php';
require_once __DIR__ . '/classes/Attendance.php';

$memberObj     = new Member();
$trainerObj    = new Trainer();
$planObj       = new Plan();
$paymentObj    = new Payment();
$attendanceObj = new Attendance();

$totalMembers    = $memberObj->countTotal();
$activeMembers   = $memberObj->countActive();
$expiredMembers  = $memberObj->countExpired();
$activeTrainers  = $trainerObj->countActive();
$totalRevenue    = $paymentObj->getTotalRevenue();
$todayCheckIns   = $attendanceObj->countTodayCheckIns();

$recentMembers   = $memberObj->getAll();
$todayAttendance = $attendanceObj->getRecords();
?>

<!-- Statistics Overview Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-info">
            <h4>Active Members</h4>
            <div class="value"><?php echo $activeMembers; ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🏋️‍♂️</div>
        <div class="stat-info">
            <h4>Active Trainers</h4>
            <div class="value"><?php echo $activeTrainers; ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">⚡</div>
        <div class="stat-info">
            <h4>Today's Check-ins</h4>
            <div class="value"><?php echo $todayCheckIns; ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-info">
            <h4>Total Revenue</h4>
            <div class="value">Rs. <?php echo number_format($totalRevenue, 0); ?></div>
        </div>
    </div>
</div>

<!-- Action Shortcuts -->
<div style="display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap;">
    <a href="<?php echo BASE_URL; ?>/members/member_add.php" class="btn btn-primary">➕ Register New Member</a>
    <a href="<?php echo BASE_URL; ?>/attendance/attendance.php" class="btn btn-accent">⏱️ Quick Check-In</a>
    <a href="<?php echo BASE_URL; ?>/payments/payments.php" class="btn btn-primary" style="background: linear-gradient(135deg, #10b981, #047857);">💳 Record Payment</a>
    <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-primary" style="background: rgba(255,255,255,0.1); border: 1px solid var(--border-color);">🌐 View Public Home Page</a>
</div>

<!-- Dashboard Content Grid -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
    <!-- Recent Members -->
    <div class="glass-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="font-family: var(--font-heading);">Recent Registered Members</h3>
            <a href="<?php echo BASE_URL; ?>/members/members.php" class="btn btn-sm btn-primary">View All</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Plan</th>
                        <th>Join Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentMembers)): ?>
                        <?php foreach (array_slice($recentMembers, 0, 5) as $m): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($m['member_code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($m['first_name'] . ' ' . $m['last_name']); ?></td>
                                <td><span class="badge badge-active" style="background: rgba(99, 102, 241, 0.2); color:#818cf8; border-color: rgba(99, 102, 241, 0.3);"><?php echo htmlspecialchars($m['plan_name']); ?></span></td>
                                <td><?php echo date('d M Y', strtotime($m['join_date'])); ?></td>
                                <td>
                                    <span class="badge <?php echo $m['status'] === 'Active' ? 'badge-active' : 'badge-expired'; ?>">
                                        <?php echo $m['status']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center; color: var(--text-muted);">No members registered yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Today's Attendance Overview -->
    <div class="glass-card">
        <h3 style="font-family: var(--font-heading); margin-bottom: 1.5rem;">Today's Check-ins</h3>
        <?php if (!empty($todayAttendance)): ?>
            <ul style="list-style: none; display: flex; flex-direction: column; gap: 1rem;">
                <?php foreach (array_slice($todayAttendance, 0, 4) as $att): ?>
                    <li style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: rgba(255,255,255,0.03); border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                        <div>
                            <strong><?php echo htmlspecialchars($att['first_name'] . ' ' . $att['last_name']); ?></strong>
                            <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($att['plan_name']); ?></div>
                        </div>
                        <span style="font-size: 0.85rem; color: #10b981; font-weight: 600;">
                            <?php echo date('h:i A', strtotime($att['check_in'])); ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p style="color: var(--text-muted); font-size: 0.9rem;">No attendance logged for today yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
