<?php
/**
 * Member Payments & Invoicing Page
 * Page 8 of 8 (Payment logging & revenue tracking)
 */
$pageTitle = "Payments & Revenue Invoicing";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../classes/Payment.php';
require_once __DIR__ . '/../classes/Member.php';

$paymentObj = new Payment();
$memberObj  = new Member();

$members = $memberObj->getAll();
$errors  = [];

// Handle New Payment Record
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['member_id']) || empty($_POST['amount'])) {
        $errors[] = "Please select a member and enter a valid amount.";
    } else {
        if ($paymentObj->create($_POST)) {
            setAlert("Payment transaction recorded successfully!", "success");
            header("Location: " . BASE_URL . "/payments/payments.php");
            exit;
        } else {
            $errors[] = "Failed to log payment transaction.";
        }
    }
}

// Handle Delete Transaction
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if ($paymentObj->delete($_GET['id'])) {
        setAlert("Invoice record deleted.", "success");
    } else {
        setAlert("Failed to delete record.", "danger");
    }
    header("Location: " . BASE_URL . "/payments/payments.php");
    exit;
}

// One-click confirmation: flips a self-registration's Pending invoice to Paid
if (isset($_GET['action']) && $_GET['action'] === 'markpaid' && isset($_GET['id'])) {
    if ($paymentObj->markPaid($_GET['id'])) {
        setAlert("Payment marked as Paid!", "success");
    } else {
        setAlert("Failed to update payment status.", "danger");
    }
    header("Location: " . BASE_URL . "/payments/payments.php");
    exit;
}

$payments     = $paymentObj->getAll();
$totalRevenue = $paymentObj->getTotalRevenue();
?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
    <!-- Record Payment Form -->
    <div class="glass-card">
        <h3 style="font-family: var(--font-heading); margin-bottom: 1rem;">💳 Record Member Payment</h3>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.5rem;">Generate an invoice and log membership fees.</p>

        <?php if (!empty($errors)): ?>
            <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171; padding: 0.8rem; border-radius: var(--radius-md); margin-bottom: 1rem; font-size:0.85rem;">
                <?php echo implode('<br>', $errors); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo BASE_URL; ?>/payments/payments.php" class="needs-validation">
            <div class="form-group">
                <label for="member_id">Select Gym Member *</label>
                <select name="member_id" id="member_id" class="form-control" required>
                    <option value="">-- Choose Member --</option>
                    <?php foreach ($members as $m): ?>
                        <option value="<?php echo $m['id']; ?>">
                            <?php echo htmlspecialchars($m['member_code'] . ' - ' . $m['first_name'] . ' ' . $m['last_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="amount">Payment Amount (Rs.) *</label>
                <input type="number" step="1" name="amount" id="amount" class="form-control" required placeholder="0">
            </div>

            <div class="form-group">
                <label for="payment_method">Payment Method *</label>
                <select name="payment_method" id="payment_method" class="form-control" required>
                    <option value="Cash">Cash</option>
                    <option value="Credit Card">Credit Card</option>
                    <option value="Debit Card">Debit Card</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Online">Online Payment</option>
                </select>
            </div>

            <div class="form-group">
                <label for="payment_date">Transaction Date</label>
                <input type="date" name="payment_date" id="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="form-group">
                <label for="payment_status">Payment Status</label>
                <select name="payment_status" id="payment_status" class="form-control">
                    <option value="Paid" selected>Paid</option>
                    <option value="Pending">Pending</option>
                    <option value="Refunded">Refunded</option>
                </select>
            </div>

            <div class="form-group">
                <label for="notes">Transaction Notes</label>
                <input type="text" name="notes" id="notes" class="form-control" placeholder="e.g. Monthly fee payment">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                💰 Save & Issue Invoice
            </button>
        </form>
    </div>

    <!-- Payments List & Summary -->
    <div class="glass-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h3 style="font-family: var(--font-heading);">Payment Transactions</h3>
                <p style="color: var(--text-muted); font-size: 0.85rem;">Total Revenue Collected: <strong style="color: #10b981;">Rs. <?php echo number_format($totalRevenue, 0); ?></strong></p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Invoice No</th>
                        <th>Member</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($payments)): ?>
                        <?php foreach ($payments as $p): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($p['invoice_no']); ?></strong></td>
                                <td>
                                    <div><?php echo htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?></div>
                                    <div style="font-size:0.75rem; color:var(--text-muted);"><?php echo htmlspecialchars($p['plan_name']); ?></div>
                                </td>
                                <td><strong style="color:#10b981;">Rs. <?php echo number_format($p['amount'], 0); ?></strong></td>
                                <td><?php echo htmlspecialchars($p['payment_method']); ?></td>
                                <td><?php echo date('d M Y', strtotime($p['payment_date'])); ?></td>
                                <td>
                                    <span class="badge <?php echo $p['payment_status'] === 'Paid' ? 'badge-active' : 'badge-pending'; ?>">
                                        <?php echo $p['payment_status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display:flex; gap:0.4rem;">
                                        <?php if ($p['payment_status'] === 'Pending'): ?>
                                            <a href="<?php echo BASE_URL; ?>/payments/payments.php?action=markpaid&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-accent" title="Confirm Payment Collected">✅ Mark Paid</a>
                                        <?php endif; ?>
                                        <a href="<?php echo BASE_URL; ?>/payments/payments.php?action=delete&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger btn-delete-confirm">🗑️</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align: center; color: var(--text-muted);">No payment invoices recorded yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
