<?php
/**
 * Public Self-Registration Page - Trainers
 */
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Trainer.php';

$auth       = new Auth();
$trainerObj = new Trainer();
$errors     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['full_name']) || empty($_POST['email']) || empty($_POST['phone']) || empty($_POST['specialization'])) {
        $errors[] = "Name, email, phone, and specialization are required fields.";
    } elseif (!preg_match('/^[A-Za-z\s\'\-]+$/', trim($_POST['full_name']))) {
        $errors[] = "Full name can only contain letters.";
    } elseif (!preg_match('/^0[0-9]{9}$/', trim($_POST['phone']))) {
        $errors[] = "Phone number must be a valid Sri Lankan number (e.g. 0772352232).";
    }

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
            $newFileName = 'trainer_' . time() . '_' . rand(100, 999) . '.' . $fileExtension;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $newFileName)) {
                $photoFileName = $newFileName;
            }
        } else {
            $errors[] = "Invalid image format. Allowed formats: JPG, JPEG, PNG, WEBP.";
        }
    }

    if (empty($errors)) {
        $postData = $_POST;
        $postData['joining_date'] = date('Y-m-d'); // self-registration always joins today
        $postData['status']       = 'Pending';     // must be approved by Admin before becoming Active

        if ($trainerObj->create($postData, $photoFileName)) {
            $newTrainerId = Database::getInstance()->getConnection()->lastInsertId();
            $code = $auth->createAccount($_POST['email'], $_POST['password'], 'trainer', $newTrainerId);
            $auth->sendVerificationCode($_POST['email'], $code);
            header("Location: " . BASE_URL . "/auth/verify.php?email=" . urlencode($_POST['email']));
            exit;
        } else {
            $errors[] = "Something went wrong while creating your account. Please try again.";
        }
    }
}

require_once __DIR__ . '/../includes/auth_layout.php';
renderAuthHeader('Register as a Trainer', true);
?>
    <h1>Join as a Trainer</h1>
    <p class="subtitle">Fill in your details to register as a PulseFit Gym trainer.</p>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <ul><?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo BASE_URL; ?>/auth/register_trainer.php" enctype="multipart/form-data">
        <div class="form-grid">
            <div class="form-group">
                <label for="full_name">Full Name *</label>
                <input type="text" name="full_name" id="full_name" class="form-control" required pattern="[A-Za-z\s'\-]+" title="Only letters are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s'\-]/g, '')" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
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
                <label for="specialization">Fitness Specialization *</label>
                <input type="text" name="specialization" id="specialization" class="form-control" required placeholder="e.g. Bodybuilding, HIIT" value="<?php echo htmlspecialchars($_POST['specialization'] ?? ''); ?>">
            </div>
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
