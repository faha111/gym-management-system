<?php
/**
 * Trainer Portal - My Clients page (assigned members list)
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
$stmt = $db->prepare("SELECT id, first_name, last_name, email, phone, status, photo, join_date FROM members WHERE trainer_id = :tid ORDER BY id DESC");
$stmt->execute([':tid' => $_SESSION['ref_id']]);
$assignedMembers = $stmt->fetchAll();

$pageTitle = "My Clients";
require_once __DIR__ . '/../includes/portal_header.php';
?>

<div style="margin-bottom: 1.75rem;">
    <h2 style="font-family: var(--font-heading); font-size: 1.6rem; margin-bottom: 0.25rem;">My Clients</h2>
    <p style="color: var(--text-muted); margin: 0;">Members currently assigned to you (<?php echo count($assignedMembers); ?>).</p>
</div>

<?php if (empty($assignedMembers)): ?>
    <div class="glass-card" style="text-align:center; padding:3rem 2rem;">
        <div style="font-size:2.5rem; margin-bottom:1rem;">👥</div>
        <h3 style="font-family:var(--font-heading); margin-bottom:0.5rem;">No Clients Yet</h3>
        <p style="color:var(--text-muted);">Once the Admin assigns members to you, they'll show up here.</p>
    </div>
<?php else: ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.25rem;">
        <?php foreach ($assignedMembers as $m): ?>
            <div class="glass-card" style="display:flex; align-items:center; gap:1rem;">
                <img src="<?php echo BASE_URL; ?>/assets/uploads/<?php echo htmlspecialchars($m['photo'] ?? 'default_user.png'); ?>" alt="" style="width:52px; height:52px; border-radius:50%; object-fit:cover; background:#334155; flex-shrink:0;" onerror="this.src='<?php echo BASE_URL; ?>/assets/uploads/default_user.png'">
                <div style="flex:1; min-width:0;">
                    <strong><?php echo htmlspecialchars($m['first_name'] . ' ' . $m['last_name']); ?></strong>
                    <div style="font-size:0.8rem; color:var(--text-muted); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php echo htmlspecialchars($m['email']); ?></div>
                    <div style="font-size:0.8rem; color:var(--text-muted);"><?php echo htmlspecialchars($m['phone']); ?></div>
                </div>
                <span class="badge <?php echo $m['status'] === 'Active' ? 'badge-active' : 'badge-pending'; ?>"><?php echo htmlspecialchars($m['status']); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/portal_footer.php'; ?>
