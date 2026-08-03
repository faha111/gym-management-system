<?php
/**
 * Email Verification Page
 * After registering, the user is sent here to enter the 6-digit code.
 */
require_once __DIR__ . '/../classes/Auth.php';

$auth  = new Auth();
$email = $_GET['email'] ?? ($_POST['email'] ?? '');
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $auth->verifyCode($_POST['email'] ?? '', $_POST['code'] ?? '');
    if ($result === true) {
        $success = true;
    } else {
        $errors[] = $result;
    }
}

// Demo-mode helper: only shown when real SMTP sending isn't configured yet
// (see config/mail_config.php). Once MAIL_ENABLED is true, real emails go
// out and this box disappears automatically.
require_once __DIR__ . '/../config/mail_config.php';
$demoCode = null;
if (!$success && !MAIL_ENABLED && !empty($email)) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT verification_code FROM users WHERE email = :email AND is_verified = 0");
    $stmt->execute([':email' => trim($email)]);
    $row = $stmt->fetch();
    if ($row) $demoCode = $row['verification_code'];
}

require_once __DIR__ . '/../includes/auth_layout.php';
renderAuthHeader('Verify Your Email');
?>
    <?php if ($success): ?>
        <h1>✅ Email Verified!</h1>
        <p class="subtitle">Your account is now active. You can log in whenever you're ready.</p>
        <a href="<?php echo BASE_URL; ?>/auth/login.php" class="btn btn-primary" style="width:100%; text-align:center; display:block;">Go to Login</a>
    <?php else: ?>
        <h1>Verify Your Email</h1>
        <p class="subtitle">We've sent a 6-digit verification code to your email address. Enter it below to activate your account.</p>

        <?php if (!empty($errors)): ?>
            <div class="error-box">
                <ul><?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?></ul>
            </div>
        <?php endif; ?>

        <?php if ($demoCode): ?>
            <div class="error-box" style="background: rgba(99,102,241,0.12); border-color: rgba(99,102,241,0.35); color: var(--text-main);">
                <strong>Demo Mode:</strong> Your project's local server isn't connected to a real mail service, so for testing purposes your code is shown here: <strong style="font-size:1.1rem; letter-spacing:2px;"><?php echo htmlspecialchars($demoCode); ?></strong>
                <div class="auth-hint">In a real deployment, this code would only be emailed — never shown on screen.</div>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo BASE_URL; ?>/auth/verify.php">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" class="form-control" required value="<?php echo htmlspecialchars($email); ?>">
            </div>
            <div class="form-group">
                <label for="code">Verification Code</label>
                <input type="text" name="code" id="code" class="form-control" required maxlength="6" placeholder="6-digit code">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; margin-top:0.5rem;">Verify My Account</button>
        </form>
    <?php endif; ?>
<?php
renderAuthFooter();
