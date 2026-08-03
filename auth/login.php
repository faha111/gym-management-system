<?php
/**
 * Shared Login Page - Admin, Trainer, and Member all log in here.
 * The system checks the account's role after successful login and
 * redirects to the correct area.
 */
require_once __DIR__ . '/../classes/Auth.php';

$auth = new Auth();
$auth->ensureDefaultAdmin(); // makes sure the one Admin account always exists

if (Auth::isLoggedIn()) {
    // Already logged in - bounce straight to the right area
    header("Location: " . BASE_URL . ($_SESSION['role'] === 'admin' ? '/dashboard.php' : ($_SESSION['role'] === 'trainer' ? '/trainer-portal/trainer_portal.php' : '/member-portal/member_portal.php')));
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errors[] = "Please enter both email and password.";
    } else {
        $result = $auth->attemptLogin($email, $password);
        if (is_string($result)) {
            $errors[] = $result;
        } else {
            $auth->login($result);
            $redirect = $result['role'] === 'admin' ? '/dashboard.php' : ($result['role'] === 'trainer' ? '/trainer-portal/trainer_portal.php' : '/member-portal/member_portal.php');
            header("Location: " . BASE_URL . $redirect);
            exit;
        }
    }
}

require_once __DIR__ . '/../includes/auth_layout.php';
renderAuthHeader('Login');
?>
    <h1>Welcome Back</h1>
    <p class="subtitle">Log in as Admin, Trainer, or Member using the same form below.</p>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <ul><?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo BASE_URL; ?>/auth/login.php">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%; margin-top:0.5rem;">Log In</button>
    </form>

    <div class="auth-footer-link">
        New here? <a href="<?php echo BASE_URL; ?>/auth/register_member.php">Join as a Member</a> or <a href="<?php echo BASE_URL; ?>/auth/register_trainer.php">Register as a Trainer</a>
    </div>
<?php
renderAuthFooter();
