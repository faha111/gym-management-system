<?php
/**
 * Daily Attendance Logging & Check-In Page
 * Page 7 of 8 (Check-in, Check-out, Filter by date)
 */
$pageTitle = "Member Attendance Tracker";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../classes/Attendance.php';
require_once __DIR__ . '/../classes/Member.php';

$attendanceObj = new Attendance();
$memberObj     = new Member();

$activeMembers = $memberObj->getAll('', 'Active');

// Handle Check-In Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'check_in') {
    $memberId = (int)$_POST['member_id'];
    $notes    = $_POST['notes'] ?? '';
    
    $result = $attendanceObj->checkIn($memberId, $notes);
    if ($result['success']) {
        setAlert($result['message'], "success");
    } else {
        setAlert($result['message'], "warning");
    }
    header("Location: " . BASE_URL . "/attendance/attendance.php");
    exit;
}

// Handle Check-Out Request
if (isset($_GET['action']) && $_GET['action'] === 'checkout' && isset($_GET['id'])) {
    if ($attendanceObj->checkOut($_GET['id'])) {
        setAlert("Member checked out successfully!", "success");
    } else {
        setAlert("Failed to check out member.", "danger");
    }
    header("Location: " . BASE_URL . "/attendance/attendance.php");
    exit;
}

// Handle Delete Record
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if ($attendanceObj->delete($_GET['id'])) {
        setAlert("Attendance entry deleted.", "success");
    } else {
        setAlert("Failed to delete entry.", "danger");
    }
    header("Location: " . BASE_URL . "/attendance/attendance.php");
    exit;
}

$viewMode = ($_GET['view'] ?? 'day') === 'month' ? 'month' : 'day';

$selectedDate  = $_GET['date'] ?? date('Y-m-d');
$selectedMonth = $_GET['month'] ?? date('Y-m');

if ($viewMode === 'month') {
    $logs = $attendanceObj->getRecordsByMonth($selectedMonth);
    $monthlySummary = $attendanceObj->getMonthlySummary($selectedMonth);
} else {
    $logs = $attendanceObj->getRecords($selectedDate);
}
?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
    <!-- Quick Check-in Panel -->
    <div class="glass-card">
        <h3 style="font-family: var(--font-heading); margin-bottom: 1rem;">⏱️ Member Check-In</h3>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.5rem;">Select an active member to record check-in timestamp.</p>

        <form method="POST" action="<?php echo BASE_URL; ?>/attendance/attendance.php" class="needs-validation">
            <input type="hidden" name="action" value="check_in">

            <div class="form-group">
                <label for="member_id">Select Gym Member *</label>
                <select name="member_id" id="member_id" class="form-control" required>
                    <option value="">-- Choose Member --</option>
                    <?php foreach ($activeMembers as $m): ?>
                        <option value="<?php echo $m['id']; ?>">
                            <?php echo htmlspecialchars($m['member_code'] . ' - ' . $m['first_name'] . ' ' . $m['last_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="notes">Workout Session Notes (Optional)</label>
                <input type="text" name="notes" id="notes" class="form-control" placeholder="e.g. Legs & Core session">
            </div>

            <button type="submit" class="btn btn-accent" style="width: 100%;">
                ⚡ Record Check-In
            </button>
        </form>
    </div>

    <!-- Attendance Logs Display Table -->
    <div class="glass-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
            <h3 style="font-family: var(--font-heading);">Attendance Logs</h3>

            <div style="display: flex; gap: 1.25rem; align-items: center; flex-wrap: wrap;">
                <!-- Day / Month Toggle -->
                <div style="display:flex; border:1px solid var(--border-color); border-radius: var(--radius-md); overflow:hidden;">
                    <a href="<?php echo BASE_URL; ?>/attendance/attendance.php?view=day&date=<?php echo htmlspecialchars($selectedDate); ?>"
                       style="padding:0.4rem 0.9rem; font-size:0.8rem; text-decoration:none; font-weight:600; <?php echo $viewMode === 'day' ? 'background:var(--accent-color, #6366f1); color:#fff;' : 'color:var(--text-muted);'; ?>">
                       Day View
                    </a>
                    <a href="<?php echo BASE_URL; ?>/attendance/attendance.php?view=month&month=<?php echo htmlspecialchars($selectedMonth); ?>"
                       style="padding:0.4rem 0.9rem; font-size:0.8rem; text-decoration:none; font-weight:600; <?php echo $viewMode === 'month' ? 'background:var(--accent-color, #6366f1); color:#fff;' : 'color:var(--text-muted);'; ?>">
                       Month View
                    </a>
                </div>

                <?php if ($viewMode === 'month'): ?>
                    <form method="GET" action="<?php echo BASE_URL; ?>/attendance/attendance.php" style="display: flex; gap: 0.5rem; align-items: center;">
                        <input type="hidden" name="view" value="month">
                        <label style="font-size:0.85rem; color:var(--text-muted);">Month:</label>
                        <input type="month" name="month" class="form-control" value="<?php echo htmlspecialchars($selectedMonth); ?>" onchange="this.form.submit()" style="padding:0.4rem 0.8rem; font-size:0.85rem;">
                    </form>
                <?php else: ?>
                    <form method="GET" action="<?php echo BASE_URL; ?>/attendance/attendance.php" style="display: flex; gap: 0.5rem; align-items: center;">
                        <input type="hidden" name="view" value="day">
                        <label style="font-size:0.85rem; color:var(--text-muted);">Date:</label>
                        <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($selectedDate); ?>" onchange="this.form.submit()" style="padding:0.4rem 0.8rem; font-size:0.85rem;">
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($viewMode === 'month'): ?>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.25rem;">
                Showing <strong><?php echo count($logs); ?></strong> check-in(s) across <strong><?php echo date('F Y', strtotime($selectedMonth . '-01')); ?></strong>.
            </p>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <?php if ($viewMode === 'month'): ?><th>Date</th><?php endif; ?>
                        <th>Check-In Time</th>
                        <th>Check-Out Time</th>
                        <th>Session Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($log['first_name'] . ' ' . $log['last_name']); ?></strong>
                                    <div style="font-size:0.75rem; color:var(--text-muted);"><?php echo htmlspecialchars($log['member_code']); ?></div>
                                </td>
                                <?php if ($viewMode === 'month'): ?>
                                    <td><?php echo date('d M Y', strtotime($log['date'])); ?></td>
                                <?php endif; ?>
                                <td><span style="color:#10b981; font-weight:600;"><?php echo date('h:i:s A', strtotime($log['check_in'])); ?></span></td>
                                <td>
                                    <?php if ($log['check_out']): ?>
                                        <span style="color:#f59e0b; font-weight:600;"><?php echo date('h:i:s A', strtotime($log['check_out'])); ?></span>
                                    <?php else: ?>
                                        <a href="<?php echo BASE_URL; ?>/attendance/attendance.php?action=checkout&id=<?php echo $log['id']; ?>" class="btn btn-sm btn-accent" style="font-size:0.75rem; padding:0.25rem 0.6rem;">⏱️ Check-Out</a>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($log['notes'] ?: 'Standard Entry'); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/attendance/attendance.php?action=delete&id=<?php echo $log['id']; ?>" class="btn btn-sm btn-danger btn-delete-confirm">🗑️</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="<?php echo $viewMode === 'month' ? 6 : 5; ?>" style="text-align: center; color: var(--text-muted);">No attendance entries recorded for this <?php echo $viewMode === 'month' ? 'month' : 'date'; ?>.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($viewMode === 'month' && !empty($monthlySummary)): ?>
        <!-- Monthly Visit Summary -->
        <div class="glass-card" style="grid-column: 1 / -1;">
            <h3 style="font-family: var(--font-heading); margin-bottom: 1.25rem;">Visit Summary — <?php echo date('F Y', strtotime($selectedMonth . '-01')); ?></h3>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Total Visits This Month</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($monthlySummary as $s): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <img src="<?php echo BASE_URL; ?>/assets/uploads/<?php echo htmlspecialchars($s['photo'] ?? 'default_user.png'); ?>" alt="" style="width:32px; height:32px; border-radius:50%; object-fit:cover; background:#334155;" onerror="this.src='<?php echo BASE_URL; ?>/assets/uploads/default_user.png'">
                                        <div>
                                            <strong><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?></strong>
                                            <div style="font-size:0.75rem; color:var(--text-muted);"><?php echo htmlspecialchars($s['member_code']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><strong><?php echo $s['visit_count']; ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

