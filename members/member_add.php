<?php
/**
 * Add New Member Page
 * Page 3 of 8 (Create with validation & photo upload)
 */
$pageTitle = "Add New Gym Member";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../classes/Member.php';
require_once __DIR__ . '/../classes/Plan.php';
require_once __DIR__ . '/../classes/Trainer.php';

$memberObj  = new Member();
$planObj    = new Plan();
$trainerObj = new Trainer();

$plans    = $planObj->getActive();
$trainers = $trainerObj->getActive();
$errors   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = $memberObj->validate($_POST);

    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = "Member profile picture is required.";
    }

    // Handle File Upload if provided
    $photoFileName = 'default_user.png';
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
        $newMemberId = $memberObj->create($_POST, $photoFileName);
        if ($newMemberId) {
            setAlert("Member registered successfully!", "success");
            header("Location: " . BASE_URL . "/members/members.php");
            exit;
        } else {
            $errors[] = "Failed to register new member into database.";
        }
    }
}
?>

<div class="glass-card" style="max-width: 900px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <h2 style="font-family: var(--font-heading);">Member Registration Form</h2>
            <p style="color: var(--text-muted); font-size: 0.85rem;">Fill out the required information to add a new member.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/members/members.php" class="btn btn-sm btn-danger">⬅ Back to List</a>
    </div>

    <!-- Display Validation Errors -->
    <?php if (!empty($errors)): ?>
        <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem;">
            <strong>Registration Errors:</strong>
            <ul style="margin-left: 1.5rem; margin-top: 0.5rem;">
                <?php foreach ($errors as $err): ?>
                    <li><?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo BASE_URL; ?>/members/member_add.php" enctype="multipart/form-data" class="needs-validation">
        <div class="form-grid">
            <div class="form-group">
                <label for="first_name">First Name *</label>
                <input type="text" name="first_name" id="first_name" class="form-control" required pattern="[A-Za-z\s'\-]+" title="Only letters are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s'\-]/g, '')" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="last_name">Last Name *</label>
                <input type="text" name="last_name" id="last_name" class="form-control" required pattern="[A-Za-z\s'\-]+" title="Only letters are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s'\-]/g, '')" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" name="email" id="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" name="phone" id="phone" class="form-control" required pattern="0[0-9]{9}" maxlength="10" placeholder="e.g. 0772352232" title="Enter a valid Sri Lankan number, e.g. 0772352232" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="gender">Gender *</label>
                <select name="gender" id="gender" class="form-control" required>
                    <option value="Male" <?php echo ($_POST['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo ($_POST['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                    <option value="Other" <?php echo ($_POST['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="dob">Date of Birth</label>
                <input type="date" name="dob" id="dob" class="form-control" value="<?php echo htmlspecialchars($_POST['dob'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="plan_id">Membership Plan *</label>
                <select name="plan_id" id="plan_id" class="form-control" required>
                    <option value="">-- Select Package Plan --</option>
                    <?php foreach ($plans as $p): ?>
                        <option value="<?php echo $p['id']; ?>" <?php echo ($_POST['plan_id'] ?? '') == $p['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['name']); ?> (Rs. <?php echo number_format($p['price'], 0); ?> / <?php echo $p['duration_months']; ?> month(s))
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="trainer_id">Assigned Personal Trainer</label>
                <select name="trainer_id" id="trainer_id" class="form-control">
                    <option value="">-- Optional Trainer Assignment --</option>
                    <?php foreach ($trainers as $t): ?>
                        <option value="<?php echo $t['id']; ?>" <?php echo ($_POST['trainer_id'] ?? '') == $t['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($t['full_name']); ?> (<?php echo htmlspecialchars($t['specialization']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="join_date">Registration Date</label>
                <input type="date" name="join_date" id="join_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($_POST['join_date'] ?? date('Y-m-d')); ?>">
            </div>

            <div class="form-group">
                <label for="status">Membership Status</label>
                <select name="status" id="status" class="form-control">
                    <option value="Active" selected>Active</option>
                    <option value="Pending">Pending</option>
                    <option value="Suspended">Suspended</option>
                </select>
            </div>
        </div>

        <div class="form-group" style="grid-column: 1 / -1;">
            <label for="address">Residential Address</label>
            <textarea name="address" id="address" class="form-control" rows="3"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="photo">Member Profile Picture *</label>
            <input type="file" name="photo" id="photo" class="form-control" accept="image/*" required>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Save & Register Member</button>
            <a href="<?php echo BASE_URL; ?>/members/members.php" class="btn btn-danger">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
