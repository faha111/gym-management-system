<?php
/**
 * Edit / Update Member Page
 * Page 4 of 8 (Update with validation)
 */
$pageTitle = "Edit Member Details";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../classes/Member.php';
require_once __DIR__ . '/../classes/Plan.php';
require_once __DIR__ . '/../classes/Trainer.php';

$memberObj  = new Member();
$planObj    = new Plan();
$trainerObj = new Trainer();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$member = $memberObj->getById($id);

if (!$member) {
    setAlert("Member not found!", "danger");
    header("Location: " . BASE_URL . "/members/members.php");
    exit;
}

$plans    = $planObj->getActive();
$trainers = $trainerObj->getActive();
$errors   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = $memberObj->validate($_POST, $id);

    $photoFileName = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileExtension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowedExts   = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($fileExtension, $allowedExts)) {
            $newFileName = 'member_' . time() . '_' . rand(100, 999) . '.' . $fileExtension;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $newFileName)) {
                $photoFileName = $newFileName;
            }
        } else {
            $errors[] = "Invalid image format. Allowed formats: JPG, JPEG, PNG, WEBP.";
        }
    }

    if (empty($errors)) {
        if ($memberObj->update($id, $_POST, $photoFileName)) {
            setAlert("Member details updated successfully!", "success");
            header("Location: " . BASE_URL . "/members/members.php");
            exit;
        } else {
            $errors[] = "Failed to update member in database.";
        }
    }
}
?>

<div class="glass-card" style="max-width: 900px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <h2 style="font-family: var(--font-heading);">Edit Member: <?php echo htmlspecialchars($member['member_code']); ?></h2>
            <p style="color: var(--text-muted); font-size: 0.85rem;">Update profile information, membership status, or package tier.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/members/members.php" class="btn btn-sm btn-danger">⬅ Back to List</a>
    </div>

    <!-- Display Validation Errors -->
    <?php if (!empty($errors)): ?>
        <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem;">
            <strong>Validation Errors:</strong>
            <ul style="margin-left: 1.5rem; margin-top: 0.5rem;">
                <?php foreach ($errors as $err): ?>
                    <li><?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo BASE_URL; ?>/members/member_edit.php?id=<?php echo $id; ?>" enctype="multipart/form-data" class="needs-validation">
        <div class="form-grid">
            <div class="form-group">
                <label for="first_name">First Name *</label>
                <input type="text" name="first_name" id="first_name" class="form-control" required pattern="[A-Za-z\s'\-]+" title="Only letters are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s'\-]/g, '')" value="<?php echo htmlspecialchars($_POST['first_name'] ?? $member['first_name']); ?>">
            </div>

            <div class="form-group">
                <label for="last_name">Last Name *</label>
                <input type="text" name="last_name" id="last_name" class="form-control" required pattern="[A-Za-z\s'\-]+" title="Only letters are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s'\-]/g, '')" value="<?php echo htmlspecialchars($_POST['last_name'] ?? $member['last_name']); ?>">
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" name="email" id="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? $member['email']); ?>">
            </div>

            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" name="phone" id="phone" class="form-control" required pattern="0[0-9]{9}" maxlength="10" placeholder="e.g. 0772352232" title="Enter a valid Sri Lankan number, e.g. 0772352232" value="<?php echo htmlspecialchars($_POST['phone'] ?? $member['phone']); ?>">
            </div>

            <div class="form-group">
                <label for="gender">Gender *</label>
                <select name="gender" id="gender" class="form-control" required>
                    <option value="Male" <?php echo ($_POST['gender'] ?? $member['gender']) === 'Male' ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo ($_POST['gender'] ?? $member['gender']) === 'Female' ? 'selected' : ''; ?>>Female</option>
                    <option value="Other" <?php echo ($_POST['gender'] ?? $member['gender']) === 'Other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="dob">Date of Birth</label>
                <input type="date" name="dob" id="dob" class="form-control" value="<?php echo htmlspecialchars($_POST['dob'] ?? $member['dob']); ?>">
            </div>

            <div class="form-group">
                <label for="plan_id">Membership Plan *</label>
                <select name="plan_id" id="plan_id" class="form-control" required>
                    <?php foreach ($plans as $p): ?>
                        <option value="<?php echo $p['id']; ?>" <?php echo ($_POST['plan_id'] ?? $member['plan_id']) == $p['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['name']); ?> (Rs. <?php echo number_format($p['price'], 0); ?> / <?php echo $p['duration_months']; ?> mo)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="trainer_id">Assigned Personal Trainer</label>
                <select name="trainer_id" id="trainer_id" class="form-control">
                    <option value="">-- None --</option>
                    <?php foreach ($trainers as $t): ?>
                        <option value="<?php echo $t['id']; ?>" <?php echo ($_POST['trainer_id'] ?? $member['trainer_id']) == $t['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($t['full_name']); ?> (<?php echo htmlspecialchars($t['specialization']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="join_date">Registration Date</label>
                <input type="date" name="join_date" id="join_date" class="form-control" value="<?php echo htmlspecialchars($_POST['join_date'] ?? $member['join_date']); ?>">
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status" class="form-control">
                    <option value="Active" <?php echo ($_POST['status'] ?? $member['status']) === 'Active' ? 'selected' : ''; ?>>Active</option>
                    <option value="Expired" <?php echo ($_POST['status'] ?? $member['status']) === 'Expired' ? 'selected' : ''; ?>>Expired</option>
                    <option value="Pending" <?php echo ($_POST['status'] ?? $member['status']) === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Suspended" <?php echo ($_POST['status'] ?? $member['status']) === 'Suspended' ? 'selected' : ''; ?>>Suspended</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="address">Address</label>
            <textarea name="address" id="address" class="form-control" rows="3"><?php echo htmlspecialchars($_POST['address'] ?? $member['address']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="photo">Member Profile Picture</label>
            <div style="display:flex; align-items:center; gap:1rem;">
                <img id="photo-preview" src="<?php echo BASE_URL; ?>/assets/uploads/<?php echo htmlspecialchars($member['photo'] ?? 'default_user.png'); ?>" alt="" style="width:56px; height:56px; border-radius:50%; object-fit:cover; background:#334155; border:2px solid var(--border-color); flex-shrink:0;" onerror="this.src='<?php echo BASE_URL; ?>/assets/uploads/default_user.png'">
                <input type="file" name="photo" id="photo" class="form-control" accept="image/*" onchange="const f=this.files[0]; if(f){document.getElementById('photo-preview').src=URL.createObjectURL(f);}">
            </div>
            <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.35rem;">Leave empty to keep the current photo.</div>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Update Member Record</button>
            <a href="<?php echo BASE_URL; ?>/members/members.php" class="btn btn-danger">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
