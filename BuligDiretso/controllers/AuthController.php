<?php
require_once 'HomeController.php';
require_once MODEL_PATH . 'user.php';
require_once ROOT_PATH . 'services/GmailService.php';
require_once ROOT_PATH . 'services/GoogleIdentityService.php';

class AuthController extends HomeController {
    private const REMEMBER_COOKIE = 'bd_remember';
    private const REMEMBER_DAYS = 30;

    private function rememberSecret(): string {
        return hash('sha256', DB_NAME . '|' . DB_USER . '|' . DB_HOST . '|BuligDiretsoRemember');
    }

    private function setUserSession(array $user): void {
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        $_SESSION['user_email'] = $user['email'] ?? '';
        $_SESSION['user_role'] = $user['role'] ?? 'pwd';
    }

    private function createEmailVerificationToken(int $userId): string {
        $token = bin2hex(random_bytes(32));
        User::saveEmailVerificationToken(
            $userId,
            hash('sha256', $token)
        );
        return $token;
    }

    private function createRememberCookie(int $userId): void {
        $expiresAt = time() + (self::REMEMBER_DAYS * 86400);
        $signature = hash_hmac('sha256', $userId . '|' . $expiresAt, $this->rememberSecret());
        $value = $userId . ':' . $expiresAt . ':' . $signature;
        setcookie(self::REMEMBER_COOKIE, $value, [
            'expires'  => $expiresAt,
            'path'     => '/',
            'secure'   => !IS_LOCAL,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public static function clearRememberCookie(): void {
        setcookie(self::REMEMBER_COOKIE, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => !IS_LOCAL,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public static function autoLoginFromRememberCookie(): bool {
        if (!empty($_SESSION['user_id']) || empty($_COOKIE[self::REMEMBER_COOKIE])) {
            return false;
        }

        $raw = (string)$_COOKIE[self::REMEMBER_COOKIE];
        $parts = explode(':', $raw);
        if (count($parts) !== 3) {
            self::clearRememberCookie();
            return false;
        }

        [$userIdRaw, $expiresRaw, $signature] = $parts;
        $userId = (int)$userIdRaw;
        $expiresAt = (int)$expiresRaw;
        if ($userId <= 0 || $expiresAt < time()) {
            self::clearRememberCookie();
            return false;
        }

        $secret = hash('sha256', DB_NAME . '|' . DB_USER . '|' . DB_HOST . '|BuligDiretsoRemember');
        $expected = hash_hmac('sha256', $userId . '|' . $expiresAt, $secret);
        if (!hash_equals($expected, $signature)) {
            self::clearRememberCookie();
            return false;
        }

        try {
            $user = User::findById($userId);
            if (!$user) {
                self::clearRememberCookie();
                return false;
            }

            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user_name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
            $_SESSION['user_email'] = $user['email'] ?? '';
            $_SESSION['user_role'] = $user['role'] ?? 'pwd';
            return true;
        } catch (Throwable $e) {
            error_log("Auto-login cookie error: " . $e->getMessage());
            self::clearRememberCookie();
            return false;
        }
    }

    public function showLogin() {
        if (!empty($_SESSION['user_id'])) {
            $role = $_SESSION['user_role'] ?? 'pwd';
            $target = ($role === 'admin') ? 'admin-dashboard' : 'dashboard';
            header("Location: " . BASE_URL . "index.php?action=" . $target);
            exit();
        }

        $pageTitle = "Login - BuligDiretso";

        // Shared header/footer data
        extract($this->getSharedData());

        require_once VIEW_PATH . 'login.php';
    }

    public function showSignup() {
        $pageTitle = "Sign Up - BuligDiretso";

        // Shared header/footer data
        extract($this->getSharedData());

        require_once VIEW_PATH . 'signup.php';
    }

    public function processGoogleSignIn() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "index.php?action=login");
            exit();
        }

        $credential = (string)($_POST['credential'] ?? '');
        if ($credential === '') {
            $_SESSION['error'] = "Google Sign-In did not return a valid credential.";
            header("Location: " . BASE_URL . "index.php?action=login");
            exit();
        }

        try {
            $google = GoogleIdentityService::verifyIdToken($credential);
            $user = User::findByGoogleSubject($google['subject']);

            if (!$user) {
                $user = User::findByEmail($google['email']);
                if ($user) {
                    if (empty($user['email_verified_at'])) {
                        throw new RuntimeException('Verify this email through BuligDiretso before using Google Sign-In.');
                    }
                    User::linkGoogleSubject((int)$user['id'], $google['subject']);
                }
            }

            if (!$user) {
                throw new RuntimeException('No BuligDiretso account exists for this Google email. Please sign up first.');
            }

            $this->setUserSession($user);
            self::clearRememberCookie();
            $_SESSION['success'] = "Login successful!";

            $target = (($user['role'] ?? 'pwd') === 'admin')
                ? 'admin-dashboard'
                : 'dashboard';
            header("Location: " . BASE_URL . "index.php?action=" . $target);
            exit();
        } catch (Throwable $e) {
            error_log("Google Sign-In error: " . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            header("Location: " . BASE_URL . "index.php?action=login");
            exit();
        }
    }

    /**
     * Process login form submission — validates against users table in SQL
     */
    public function processLogin() {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: " . BASE_URL . "index.php?action=login");
            exit();
        }

        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $rememberMe = !empty($_POST['remember_me']);

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = "All fields are required!";
            header("Location: " . BASE_URL . "index.php?action=login");
            exit();
        }

        try {
            $user = User::verifyLogin($email, $password);
        } catch (Throwable $e) {
            error_log("Process login error: " . $e->getMessage());
            $_SESSION['error'] = "Unable to log in right now. Please try again.";
            header("Location: " . BASE_URL . "index.php?action=login");
            exit();
        }

        if ($user) {
            $this->setUserSession($user);
            if ($rememberMe) {
                $this->createRememberCookie((int)$user['id']);
            } else {
                self::clearRememberCookie();
            }
            $_SESSION['success'] = "Login successful!";

            if ($user['role'] === 'admin') {
                header("Location: " . BASE_URL . "index.php?action=admin-dashboard");
            } else {
                header("Location: " . BASE_URL . "index.php?action=dashboard");
            }
            exit();
        }

        $_SESSION['error'] = "Invalid email or password.";
        header("Location: " . BASE_URL . "index.php?action=login");
        exit();
    }

    /**
     * Process signup form submission
     */
    public function processSignup() {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: " . BASE_URL . "index.php?action=signup");
            exit();
        }

        $fname            = trim($_POST['first_name']       ?? '');
        $lname            = trim($_POST['last_name']        ?? '');
        $email            = trim($_POST['email']            ?? '');
        $phone            = trim($_POST['phone']            ?? '');
        $dob              = trim($_POST['dob']              ?? '');
        $address          = trim($_POST['address']          ?? '');
        $role             = trim($_POST['role']             ?? '');
        $password         = $_POST['password']              ?? '';
        $confirm_password = $_POST['confirm_password']      ?? '';

        $errors = [];

        if (empty($fname))    $errors[] = "First name is required.";
        if (empty($lname))    $errors[] = "Last name is required.";
        if (empty($dob))      $errors[] = "Date of birth is required.";
        if (empty($address))  $errors[] = "Address is required.";
        if (empty($role))     $errors[] = "Please select a role.";

        $validRoles = ['admin', 'pwd', 'responder'];
        if (!empty($role) && !in_array($role, $validRoles)) {
            $errors[] = "Invalid role selected.";
        }

        if (empty($email)) {
            $errors[] = "Email address is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address.";
        }

        if (empty($phone)) {
            $errors[] = "Phone number is required.";
        } elseif (!preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {
            $errors[] = "Please enter a valid phone number.";
        }

        if (empty($password)) {
            $errors[] = "Password is required.";
        } elseif (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters.";
        }

        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }

        if (empty($errors) && User::findByEmail($email)) {
            $errors[] = "An account with that email address already exists.";
        }

        if (!empty($errors)) {
            $_SESSION['signup_errors'] = $errors;
            $_SESSION['signup_old']    = [
                'first_name' => $fname,
                'last_name'  => $lname,
                'email'      => $email,
                'phone'      => $phone,
                'dob'        => $dob,
                'address'    => $address,
                'role'       => $role,
            ];
            header("Location: " . BASE_URL . "index.php?action=signup");
            exit();
        }

        $password_hash = hash_password($password);
        $data = [
            'first_name'    => $fname,
            'last_name'     => $lname,
            'email'         => $email,
            'phone'         => $phone,
            'date_of_birth' => $dob ?: null,
            'address'       => $address,
            'role'          => $role,
            'password_hash' => $password_hash,
        ];

        $pdo = db();
        try {
            $pdo->beginTransaction();
            $userId = User::create($data);
            if (!$userId) {
                throw new RuntimeException('User creation did not return an ID.');
            }

            if ($role === 'responder') {
                $pdo->prepare("INSERT INTO responders (user_id) VALUES (?)")->execute([$userId]);
            }

            $verificationToken = $this->createEmailVerificationToken((int)$userId);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Signup database error: " . $e->getMessage());
            $msg = "Registration failed. Please try again later.";
            if (defined('IS_LOCAL') && IS_LOCAL) {
                $msg .= " [Debug: " . htmlspecialchars($e->getMessage()) . "]";
            }
            $_SESSION['signup_errors'] = [$msg];
            $_SESSION['signup_old'] = [
                'first_name' => $fname,
                'last_name'  => $lname,
                'email'      => $email,
                'phone'      => $phone,
                'dob'        => $dob,
                'address'    => $address,
                'role'       => $role,
            ];
            header("Location: " . BASE_URL . "index.php?action=signup");
            exit();
        }

        $emailSent = true;
        try {
            GmailService::sendSignupVerification($email, $fname, $verificationToken);
        } catch (Throwable $e) {
            $emailSent = false;
            error_log("Signup verification email error: " . $e->getMessage());
        }

        try {
            if (!empty($phone)) {
                SmsService::send(
                    SmsService::formatPhone($phone),
                    "Welcome to BuligDiretso, {$fname}! Please verify your email address before logging in."
                );
            }
        } catch (Throwable $e) {
            error_log("Signup welcome SMS error: " . $e->getMessage());
        }

        if ($emailSent) {
            $_SESSION['success'] = "Account created. Check your email for a verification link before logging in.";
        } else {
            $_SESSION['error'] = "Your account was created, but we could not send the verification email. Use the resend option below.";
        }
        header("Location: " . BASE_URL . "index.php?action=login");
        exit();
    }

    public function verifyEmail() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $token = (string)($_GET['token'] ?? '');
            if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
                $_SESSION['error'] = "That verification link is invalid or has expired.";
                header("Location: " . BASE_URL . "index.php?action=login");
                exit();
            }

            header('Referrer-Policy: no-referrer');
            $pageTitle = "Verify Email - BuligDiretso";
            $verificationToken = $token;
            extract($this->getSharedData());
            require_once VIEW_PATH . 'verify-email.php';
            return;
        }

        $token = (string)($_POST['token'] ?? '');
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            $_SESSION['error'] = "That verification link is invalid or has expired.";
            header("Location: " . BASE_URL . "index.php?action=login");
            exit();
        }

        try {
            if (User::verifyEmailToken(hash('sha256', $token))) {
                $_SESSION['success'] = "Email verified. You can now log in.";
            } else {
                $_SESSION['error'] = "That verification link is invalid or has expired.";
            }
        } catch (Throwable $e) {
            error_log("Email verification error: " . $e->getMessage());
            $_SESSION['error'] = "We could not verify your email right now. Please try again.";
        }

        header("Location: " . BASE_URL . "index.php?action=login");
        exit();
    }

    public function resendVerification() {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: " . BASE_URL . "index.php?action=login");
            exit();
        }

        $email = strtolower(trim($_POST['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Enter a valid email address to request a verification link.";
            header("Location: " . BASE_URL . "index.php?action=login");
            exit();
        }

        try {
            $user = User::findUnverifiedByEmail($email);
            if ($user) {
                $lastSent = !empty($user['token_created_at'])
                    ? strtotime($user['token_created_at'])
                    : false;

                if ($lastSent === false || $lastSent <= time() - 60) {
                    $token = $this->createEmailVerificationToken((int)$user['id']);
                    GmailService::sendSignupVerification($email, (string)$user['first_name'], $token);
                }
            }

            $_SESSION['success'] = "If an unverified account matches that email, a verification link will be sent. Please wait a minute before requesting another.";
        } catch (Throwable $e) {
            error_log("Resend verification email error: " . $e->getMessage());
            $_SESSION['error'] = "We could not send a verification email right now. Please try again later.";
        }

        header("Location: " . BASE_URL . "index.php?action=login");
        exit();
    }

    /**
     * Logout user
     */
    public function logout() {
        self::clearRememberCookie();
        $_SESSION = [];
        session_destroy();
        header("Location: " . BASE_URL . "index.php?action=home");
        exit();
    }
}