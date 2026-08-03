<?php
/**
 * Members Information Display Page
 * Page 2 of 8 (List, Search, Filter, Delete, Actions)
 */
$pageTitle = "Members List & Management";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../classes/Member.php';
require_once __DIR__ . '/../classes/Auth.php';

$memberObj = new Member();
$auth      = new Auth();

// Handle Delete Request
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $deleteId = (int)$_GET['id'];
    if ($memberObj->delete($deleteId)) {
        setAlert("Member deleted successfully!", "success");
    } else {
        setAlert("Failed to delete member.", "danger");
    }
    header("Location: " . BASE_URL . "/members/members.php");
    exit;
}

// One-click Approve: flips a self-registered Pending member straight to Active
if (isset($_GET['action']) && $_GET['action'] === 'approve' && isset($_GET['id'])) {
    $approveId = (int)$_GET['id'];
    $target = $memberObj->getById($approveId);
    if ($target) {
        $postData = $target;
        $postData['status'] = 'Active';
        if ($memberObj->update($approveId, $postData)) {
            setAlert("Member approved and activated!", "success");
        } else {
            setAlert("Failed to approve member.", "danger");
        }
    }
    header("Location: " . BASE_URL . "/members/members.php");
    exit;
}

$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$members = $memberObj->getAll($search, $status);
?>

<div class="glass-card">
    <!-- Header Controls & Search Filter -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <div>
            <h2 style="font-family: var(--font-heading); font-size: 1.4rem;">All Registered Members</h2>
            <p style="color: var(--text-muted); font-size: 0.85rem;">View, search, filter, update, or remove member accounts.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/members/member_add.php" class="btn btn-primary">➕ Add New Member</a>
    </div>

    <!-- Filters Form -->
    <form method="GET" action="<?php echo BASE_URL; ?>/members/members.php" style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
        <input type="text" name="search" id="tableSearchInput" class="form-control" placeholder="Search by name, email, phone or code..." value="<?php echo htmlspecialchars($search); ?>">
        <select name="status" class="form-control" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="Active" <?php echo $status === 'Active' ? 'selected' : ''; ?>>Active</option>
            <option value="Expired" <?php echo $status === 'Expired' ? 'selected' : ''; ?>>Expired</option>
            <option value="Pending" <?php echo $status === 'Pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="Suspended" <?php echo $status === 'Suspended' ? 'selected' : ''; ?>>Suspended</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter Results</button>
    </form>

    <!-- Data Table -->
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Member Code</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Plan</th>
                    <th>Assigned Trainer</th>
                    <th>Expire Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($members)): ?>
                    <?php foreach ($members as $m): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($m['member_code']); ?></strong></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <img src="<?php echo BASE_URL; ?>/assets/uploads/<?php echo htmlspecialchars($m['photo'] ?? 'default_user.png'); ?>" alt="<?php echo htmlspecialchars($m['first_name']); ?>" style="width:36px; height:36px; border-radius:50%; object-fit:cover; background:#334155;" onerror="this.src='<?php echo BASE_URL; ?>/assets/uploads/default_user.png'">
                                    <div>
                                        <div style="font-weight: 600;"><?php echo htmlspecialchars($m['first_name'] . ' ' . $m['last_name']); ?></div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo htmlspecialchars($m['gender']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($m['email']); ?></div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($m['phone']); ?></div>
                            </td>
                            <td><span class="badge badge-active" style="background: rgba(99, 102, 241, 0.2); color:#818cf8; border-color: rgba(99, 102, 241, 0.3);"><?php echo htmlspecialchars($m['plan_name']); ?></span></td>
                            <td><?php echo $m['trainer_name'] ? htmlspecialchars($m['trainer_name']) : '<span style="color:var(--text-dim);">None</span>'; ?></td>
                            <td>
                                <?php 
                                    $isExpired = strtotime($m['expire_date']) < time();
                                    $dateColor = $isExpired ? '#f87171' : 'var(--text-main)';
                                ?>
                                <span style="color: <?php echo $dateColor; ?>; font-weight: 600;">
                                    <?php echo date('d M Y', strtotime($m['expire_date'])); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo $m['status'] === 'Active' ? 'badge-active' : ($m['status'] === 'Pending' ? 'badge-pending' : 'badge-expired'); ?>">
                                    <?php echo $m['status']; ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.4rem; flex-wrap:wrap;">
                                    <?php if ($m['status'] === 'Pending'): ?>
                                        <a href="<?php echo BASE_URL; ?>/members/members.php?action=approve&id=<?php echo $m['id']; ?>" class="btn btn-sm btn-accent" title="Approve & Activate Member">✅ Approve</a>
                                    <?php endif; ?>
                                    <?php if (!$auth->hasAccount('member', $m['id'])): ?>
                                        <a href="<?php echo BASE_URL; ?>/auth/create_login.php?role=member&id=<?php echo $m['id']; ?>" class="btn btn-sm btn-accent">🔑 Create Login</a>
                                    <?php endif; ?>
                                    <a href="<?php echo BASE_URL; ?>/members/member_edit.php?id=<?php echo $m['id']; ?>" class="btn btn-sm btn-primary" title="Edit Member">✏️ Edit</a>
                                    <a href="<?php echo BASE_URL; ?>/members/members.php?action=delete&id=<?php echo $m['id']; ?>" class="btn btn-sm btn-danger btn-delete-confirm" title="Delete Member">🗑️ Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                            No members found matching your search query.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
