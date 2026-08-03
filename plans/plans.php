<?php
/**
 * Membership Plans Management Page
 * Page 5 of 8 (CRUD for Plans)
 */
$pageTitle = "Membership Packages & Plans";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../classes/Plan.php';

$planObj = new Plan();
$errors  = [];
$editPlan = null;

// Handle Actions: Add, Edit, Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['name']) || empty($_POST['duration_months']) || empty($_POST['price'])) {
        $errors[] = "Plan name, duration, and price are required fields.";
    } else {
        if (isset($_POST['plan_id']) && !empty($_POST['plan_id'])) {
            // Update Plan
            $planId = (int)$_POST['plan_id'];
            if ($planObj->update($planId, $_POST)) {
                setAlert("Membership plan updated successfully!", "success");
                header("Location: " . BASE_URL . "/plans/plans.php");
                exit;
            } else {
                $errors[] = "Failed to update membership plan.";
            }
        } else {
            // Create Plan
            if ($planObj->create($_POST)) {
                setAlert("New membership plan created successfully!", "success");
                header("Location: " . BASE_URL . "/plans/plans.php");
                exit;
            } else {
                $errors[] = "Failed to create membership plan.";
            }
        }
    }
}

// Handle GET Actions (Delete & Edit prep)
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'delete' && isset($_GET['id'])) {
        if ($planObj->delete($_GET['id'])) {
            setAlert("Membership plan deleted!", "success");
        } else {
            setAlert("Failed to delete membership plan.", "danger");
        }
        header("Location: " . BASE_URL . "/plans/plans.php");
        exit;
    } elseif ($_GET['action'] === 'edit' && isset($_GET['id'])) {
        $editPlan = $planObj->getById($_GET['id']);
    }
}

$plans = $planObj->getAll();
?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
    <!-- Add / Edit Plan Form -->
    <div class="glass-card">
        <h3 style="font-family: var(--font-heading); margin-bottom: 1rem;">
            <?php echo $editPlan ? 'Edit Membership Plan' : 'Create New Plan'; ?>
        </h3>

        <?php if (!empty($errors)): ?>
            <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171; padding: 0.8rem; border-radius: var(--radius-md); margin-bottom: 1rem; font-size:0.85rem;">
                <?php echo implode('<br>', $errors); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo BASE_URL; ?>/plans/plans.php" class="needs-validation">
            <?php if ($editPlan): ?>
                <input type="hidden" name="plan_id" value="<?php echo $editPlan['id']; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="name">Package Name *</label>
                <input type="text" name="name" id="name" class="form-control" required value="<?php echo htmlspecialchars($editPlan['name'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="duration_months">Duration (Months) *</label>
                <input type="number" name="duration_months" id="duration_months" class="form-control" min="1" max="36" required value="<?php echo htmlspecialchars($editPlan['duration_months'] ?? 1); ?>">
            </div>

            <div class="form-group">
                <label for="price">Price (Rs.) *</label>
                <input type="number" step="1" name="price" id="price" class="form-control" required value="<?php echo htmlspecialchars($editPlan['price'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="description">Plan Details & Perks</label>
                <textarea name="description" id="description" class="form-control" rows="3"><?php echo htmlspecialchars($editPlan['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status" class="form-control">
                    <option value="Active" <?php echo ($editPlan['status'] ?? '') === 'Active' ? 'selected' : ''; ?>>Active</option>
                    <option value="Inactive" <?php echo ($editPlan['status'] ?? '') === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <?php echo $editPlan ? 'Update Plan' : 'Save New Package'; ?>
            </button>
            <?php if ($editPlan): ?>
                <a href="<?php echo BASE_URL; ?>/plans/plans.php" class="btn btn-danger" style="width: 100%; margin-top: 0.5rem; text-align: center; justify-content: center;">Cancel Edit</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Active Membership Packages Grid / Table -->
    <div class="glass-card">
        <h3 style="font-family: var(--font-heading); margin-bottom: 1.5rem;">Existing Gym Packages</h3>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Package Name</th>
                        <th>Duration</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($plans)): ?>
                        <?php foreach ($plans as $p): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($p['name']); ?></strong>
                                    <div style="font-size:0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($p['description']); ?></div>
                                </td>
                                <td><?php echo $p['duration_months']; ?> month(s)</td>
                                <td><strong style="color: #10b981;">Rs. <?php echo number_format($p['price'], 0); ?></strong></td>
                                <td>
                                    <span class="badge <?php echo $p['status'] === 'Active' ? 'badge-active' : 'badge-expired'; ?>">
                                        <?php echo $p['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display:flex; gap:0.4rem;">
                                        <a href="<?php echo BASE_URL; ?>/plans/plans.php?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary">✏️ Edit</a>
                                        <a href="<?php echo BASE_URL; ?>/plans/plans.php?action=delete&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger btn-delete-confirm">🗑️ Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center; color: var(--text-muted);">No plans defined yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
