<?php
/**
 * Auth Class - Handles Registration, Email Verification, Login & Sessions
 *
 * Roles:
 *  - admin   : exactly ONE account, auto-created on first run. Never self-registers.
 *  - trainer : self-registers via register_trainer.php, linked to a `trainers` row.
 *  - member  : self-registers via register_member.php, linked to a `members` row.
 */

require_once __DIR__ . '/../config/Database.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Creates the single Admin account the first time the app is ever used.
    // Safe to call on every load of login.php - it only inserts if missing.
    public function ensureDefaultAdmin() {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
        $stmt->execute();
        if (!$stmt->fetch()) {
            $defaultEmail = 'admin@pulsefitgym.com';
            $defaultPass  = 'Admin@123';
            $hash = password_hash($defaultPass, PASSWORD_DEFAULT);
            $ins = $this->db->prepare("INSERT INTO users (email, password_hash, role, ref_id, is_verified) 
                                        VALUES (:email, :hash, 'admin', NULL, 1)");
            $ins->execute([':email' => $defaultEmail, ':hash' => $hash]);
        }
    }

    // Checks if an email is already used in the users table
    public function emailExists($email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => trim($email)]);
        return (bool)$stmt->fetch();
    }

    // Checks if a specific trainer/member record already has a linked login account
    public function hasAccount($role, $refId) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE role = :role AND ref_id = :ref_id");
        $stmt->execute([':role' => $role, ':ref_id' => $refId]);
        return (bool)$stmt->fetch();
    }

    // Admin-created accounts skip email verification since the Admin is trusted
    // and is typically setting this up in person or over the phone.
    public function createVerifiedAccount($email, $password, $role, $refId) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (email, password_hash, role, ref_id, is_verified) 
                                     VALUES (:email, :hash, :role, :ref_id, 1)");
        return $stmt->execute([
            ':email' => trim($email),
            ':hash'  => $hash,
            ':role'  => $role,
            ':ref_id'=> $refId
        ]);
    }

    private function generateCode() {
        return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    // Validates the password + confirm password pair. Returns array of errors.
    public function validatePassword($password, $confirmPassword) {
        $errors = [];
        if (empty($password) || strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters long.";
        }
        if ($password !== $confirmPassword) {
            $errors[] = "Password and Confirm Password do not match.";
        }
        return $errors;
    }

    // Creates the users row linking to a member/trainer record, with a verification code
    public function createAccount($email, $password, $role, $refId) {
        $code = $this->generateCode();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (email, password_hash, role, ref_id, is_verified, verification_code) 
                                     VALUES (:email, :hash, :role, :ref_id, 0, :code)");
        $stmt->execute([
            ':email' => trim($email),
            ':hash'  => $hash,
            ':role'  => $role,
            ':ref_id'=> $refId,
            ':code'  => $code
        ]);
        return $code;
    }

    // Sends the verification code by real email if SMTP is configured in
    // config/mail_config.php (MAIL_ENABLED = true). Otherwise, this silently
    // does nothing, and verify.php falls back to showing the code on screen
    // for demo/testing purposes.
    public function sendVerificationCode($email, $code) {
        require_once __DIR__ . '/../config/mail_config.php';

        if (!MAIL_ENABLED) {
            return false; // Demo mode - verify.php will display the code instead
        }

        require_once __DIR__ . '/../libs/PHPMailer/src/Exception.php';
        require_once __DIR__ . '/../libs/PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/../libs/PHPMailer/src/SMTP.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = MAIL_PORT;

            $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "PulseFit Gym - Email Verification Code";
            $mail->Body    = "<p>Your PulseFit Gym verification code is:</p><h2 style='letter-spacing:4px;'>$code</h2><p>Enter this code on the verification page to activate your account.</p>";
            $mail->AltBody = "Your PulseFit Gym verification code is: $code";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mail send failed: " . $mail->ErrorInfo);
            return false; // Falls back to demo mode display
        }
    }

    // Verifies the code entered by the user against what's stored
    public function verifyCode($email, $code) {
        $stmt = $this->db->prepare("SELECT id, verification_code FROM users WHERE email = :email");
        $stmt->execute([':email' => trim($email)]);
        $user = $stmt->fetch();

        if (!$user) {
            return "No account found with that email address.";
        }
        if (trim($code) !== $user['verification_code']) {
            return "Incorrect verification code. Please try again.";
        }

        $upd = $this->db->prepare("UPDATE users SET is_verified = 1, verification_code = NULL WHERE id = :id");
        $upd->execute([':id' => $user['id']]);
        return true; // success
    }

    // Attempts login. Returns user row on success, or an error string on failure.
    public function attemptLogin($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => trim($email)]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return "Invalid email or password.";
        }
        if (!$user['is_verified']) {
            return "Your email hasn't been verified yet. Please check your inbox for the verification code.";
        }

        return $user;
    }

    // Starts a session for a successfully authenticated user
    public function login($user) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role']    = $user['role'];
        $_SESSION['ref_id']  = $user['ref_id'];
        $_SESSION['email']   = $user['email'];
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION = [];
        session_destroy();
    }

    public static function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return isset($_SESSION['user_id']);
    }

    public static function hasRole($role) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return self::isLoggedIn() && $_SESSION['role'] === $role;
    }
}
