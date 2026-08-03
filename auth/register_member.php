<?php
/**
 * Public Self-Registration Page - Gym Members
 */
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Member.php';
require_once __DIR__ . '/../classes/Plan.php';
require_once __DIR__ . '/../classes/Trainer.php';
require_once __DIR__ . '/../classes/Payment.php';

$auth       = new Auth();
$memberObj  = new Member();
$planObj    = new Plan();
$trainerObj = new Trainer();
$paymentObj = new Payment();

$plans    = $planObj->getActive();
$trainers = $trainerObj->getActive();
$errors   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = $memberObj->validate($_POST);
    $errors = array_merge($errors, $auth->validatePassword($_POST['password'] ?? '', $_POST['confirm_password'] ?? ''));

    if (!empty($_POST['email']) && $auth->emailExists($_POST['email'])) {
        $errors[] = "An account with this email already exists. Please log in instead.";
    }
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = "Profile picture is required.";
    }

    $photoFileName = 'default_user.png';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
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
        // Self-registration always joins today, and stays Pending until email is verified
        $postData = $_POST;
        $postData['join_date'] = date('Y-m-d');
        $postData['status']    = 'Pending';

        $newMemberId = $memberObj->create($postData, $photoFileName);
        if ($newMemberId) {
            // Create a Pending payment invoice for the plan they chose, so the
            // Admin can see and confirm it under Payments & Billing, and the
            // member can see it under their own portal's payment history.
            $chosenPlan = $planObj->getById($_POST['plan_id']);
            if ($chosenPlan) {
                $paymentObj->create([
                    'member_id'      => $newMemberId,
                    'amount'         => $chosenPlan['price'],
                    'payment_date'   => date('Y-m-d'),
                    'payment_method' => 'Pending Payment',
                    'payment_status' => 'Pending',
                    'notes'          => 'Registration payment for ' . $chosenPlan['name']
                ]);
            }

            $code = $auth->createAccount($_POST['email'], $_POST['password'], 'member', $newMemberId);
            $auth->sendVerificationCode($_POST['email'], $code);
            header("Location: " . BASE_URL . "/auth/verify.php?email=" . urlencode($_POST['email']));
            exit;
        } else {
            $errors[] = "Something went wrong while creating your account. Please try again.";
        }
    }
}

require_once __DIR__ . '/../includes/auth_layout.php';
renderAuthHeader('Join as a Member', true);
?>
    <h1>Join PulseFit Gym</h1>
    <p class="subtitle">Fill in your details to create your member account.</p>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <ul><?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo BASE_URL; ?>/auth/register_member.php" enctype="multipart/form-data">
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
                <input type="tel" name="phone" id="phone" class="form-control" required pattern="0[0-9]{9}" maxlength="10" placeholder="e.g. 0772352232" title="Enter a valid Sri Lankan number" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
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
                <input type="date" name="dob" id="dob" class="form-control" max="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($_POST['dob'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="plan_id">Membership Plan *</label>
                <select name="plan_id" id="plan_id" class="form-control" required>
                    <option value="">-- Select Package Plan --</option>
                    <?php foreach ($plans as $p): ?>
                        <option value="<?php echo $p['id']; ?>" <?php echo ($_POST['plan_id'] ?? $_GET['plan_id'] ?? '') == $p['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['name']); ?> (Rs. <?php echo number_format($p['price'], 0); ?> / <?php echo $p['duration_months']; ?> month(s))
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="trainer_id">Preferred Personal Trainer</label>
                <select name="trainer_id" id="trainer_id" class="form-control">
                    <option value="">-- Optional --</option>
                    <?php foreach ($trainers as $t): ?>
                        <option value="<?php echo $t['id']; ?>" <?php echo ($_POST['trainer_id'] ?? '') == $t['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($t['full_name']); ?> (<?php echo htmlspecialchars($t['specialization']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="address">Residential Address</label>
            <textarea name="address" id="address" class="form-control" rows="2"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="photo">Profile Picture *</label>
            <input type="file" name="photo" id="photo" class="form-control" accept="image/*" required>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" name="password" id="password" class="form-control" required minlength="6">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password *</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="6">
                <div class="auth-hint">⚠️ Remember this password carefully — you'll need to enter this same email and password every time you log in to PulseFit Gym.</div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; margin-top:1rem;">Create My Account</button>
    </form>

    <div class="auth-footer-link">
        Already have an account? <a href="<?php echo BASE_URL; ?>/auth/login.php">Log in here</a>
    </div>
<?php
renderAuthFooter();
