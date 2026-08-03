<?php
/**
 * Admin Tool: Create Login for an existing Trainer or Member
 * (for people who were added directly by Admin before this login
 * system existed, and therefore have no `users` account yet)
 */
$pageTitle = "Create Login Access";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Member.php';
require_once __DIR__ . '/../classes/Trainer.php';

$auth = new Auth();
$role = ($_GET['role'] ?? '') === 'trainer' ? 'trainer' : 'member';
$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($role === 'trainer') {
    $trainerObj = new Trainer();
    $record = $trainerObj->getById($id);
    $name = $record['full_name'] ?? null;
} else {
    $memberObj = new Member();
    $record = $memberObj->getById($id);
    $name = $record ? ($record['first_name'] . ' ' . $record['last_name']) : null;
}

if (!$record) {
    setAlert("Record not found.", "danger");
    header("Location: " . BASE_URL . ($role === 'trainer' ? '/trainers/trainers.php' : '/members/members.php'));
    exit;
}

if ($auth->hasAccount($role, $id)) {
    setAlert("This person already has login access.", "warning");
    header("Location: " . BASE_URL . ($role === 'trainer' ? '/trainers/trainers.php' : '/members/members.php'));
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = $auth->validatePassword($_POST['password'] ?? '', $_POST['confirm_password'] ?? '');
    if ($auth->emailExists($record['email'])) {
        $errors[] = "This email is already linked to another login account.";
    }
    if (empty($errors)) {
        if ($auth->createVerifiedAccount($record['email'], $_POST['password'], $role, $id)) {
            setAlert("Login access created for " . htmlspecialchars($name) . "! They can now log in with email {$record['email']}.", "success");
            header("Location: " . BASE_URL . ($role === 'trainer' ? '/trainers/trainers.php' : '/members/members.php'));
            exit;
        } else {
            $errors[] = "Failed to create the login account. Please try again.";
        }
    }
}
?>

<div class="glass-card" style="max-width: 520px; margin: 0 auto;">
    <h2 style="font-family: var(--font-heading); margin-bottom: 0.25rem;">Create Login Access</h2>
    <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.5rem;">
        Set up a portal password for <strong><?php echo htmlspecialchars($name); ?></strong> so they can log in and view their own <?php echo $role; ?> portal.
    </p>

    <?php if (!empty($errors)): ?>
        <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem;">
            <ul style="margin-left: 1.5rem;">
                <?php foreach ($errors as $err): ?><li><?php echo htmlspecialchars($err); ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Login Email (from their profile)</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($record['email']); ?>" disabled>
        </div>
        <div class="form-group">
            <label for="password">Set a Password *</label>
            <input type="password" name="password" id="password" class="form-control" required minlength="6">
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm Password *</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="6">
            <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.35rem;">Share this password with them directly - it won't be shown again after this.</div>
        </div>
        <div style="display:flex; gap:1rem; margin-top:1.5rem;">
            <button type="submit" class="btn btn-primary">Create Login</button>
            <a href="<?php echo BASE_URL . ($role === 'trainer' ? '/trainers/trainers.php' : '/members/members.php'); ?>" class="btn btn-danger">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
