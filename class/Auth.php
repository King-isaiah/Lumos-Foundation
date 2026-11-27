<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Validator.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Mailer.php';
require_once __DIR__ . '/../config.php'; // Ensure Config is loaded

class Auth extends Database {

    public function __construct() {
        parent::__construct();
        
        // Force Lagos Timezone
        date_default_timezone_set('Africa/Lagos');

        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'secure'   => !Config::isLocal(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }
    public static function requireAdmin(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $role = $_SESSION['role'] ?? '';
        if ($role !== 'admin' && $role !== 'super_admin') {
            header('Location: ' . Config::appUrl() . '/public/login.php');
            exit;
        }
    }
    public function login(array $input): array {
        $data = (new Validator($input))
            ->required('email', 'password')
            ->email('email')
            ->validate();

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if (!$this->rateLimit("login:ip:{$ip}", 5, 60)) {
            throw new Exception('Too many login attempts. Try again in a minute.', 429);
        }
        if (!$this->rateLimit("login:email:" . strtolower($data['email']), 5, 900)) {
            throw new Exception('Too many attempts for this account. Try again later.', 429);
        }

        $user = (new User())->getByEmail(strtolower($data['email']));
        if (!$user || !password_verify($data['password'], $user['password_hash'])) {
            Logger::security('Login failed', ['email' => $data['email'], 'ip' => $ip]);
            throw new Exception('Invalid email or password.', 401);
        }

        if ($user['account_status'] !== 'active') {
            throw new Exception('Your account is not active. Please contact support.', 403);
        }

        if (empty($user['email_verified_at'])) {
            throw new Exception('Please verify your email address before logging in. Check your inbox for the code.', 403);
        }

        session_regenerate_id(true);
        $_SESSION['user_id']    = (int)$user['id'];
        $_SESSION['unique_id']  = (int)$user['unique_id'];
        $_SESSION['role']       = $user['role'];
        $_SESSION['username']   = $user['username'];

        (new User())->touchLastSeen((int)$user['id']);
        Logger::info('Login success', ['user_id' => $user['id'], 'ip' => $ip]);

        return [
            'user' => [
                'id'         => (int)$user['id'],
                'unique_id'  => (int)$user['unique_id'],
                'username'   => $user['username'],
                'email'      => $user['email'],
                'first_name' => $user['first_name'],
                'last_name'  => $user['last_name'],
                'role'       => $user['role'],
            ],
        ];
    }

    public function logout(): array {
        $uid = $_SESSION['user_id'] ?? null;
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        Logger::info('Logout', ['user_id' => $uid]);
        return ['message' => 'Logged out.'];
    }

    public function getSessionUser(): ?array {
        if (empty($_SESSION['user_id'])) return null;
        return (new User())->getById((int)$_SESSION['user_id']);
    }

    // ---------------------------------------------------------
    // REQUEST PASSWORD RESET (Step 1) - Fixed to 6 Mins
    // ---------------------------------------------------------
    public function requestPasswordReset(array $input): array {
        $data = (new Validator($input))->required('email')->email('email')->validate();
        $email = strtolower($data['email']);

        if (!$this->rateLimit("reset:email:{$email}", 3, 3600)) {
            throw new Exception('Too many reset requests. Please try again later.', 429);
        }

        $user = (new User())->getByEmail($email);

        if ($user) {
            $code = (string)random_int(100000, 999999);
            
            $this->insert('password_resets', [
                'user_id'    => $user['id'],
                'code_hash'  => password_hash($code, PASSWORD_BCRYPT, ['cost' => 10]),
                'expires_at' => date('Y-m-d H:i:s', time() + 360), // <--- 360 seconds = 6 minutes
            ]);

            try {
                (new Mailer())->sendPasswordReset($user['email'], $user['first_name'], $code);
                Logger::info('Password reset code sent', ['user_id' => $user['id']]);
            } catch (Throwable $e) {
                Logger::error('Password reset email failed', ['user_id' => $user['id'], 'err' => $e->getMessage()]);
            }
        }

        return ['message' => 'If an account exists for that email, a reset code has been sent.'];
    }

    // ---------------------------------------------------------
    // VERIFY RESET CODE (Step 2) - Fixed Timezone
    // ---------------------------------------------------------
    public function verifyResetCode(array $input): array {
        $data = (new Validator($input))
            ->required('email', 'code')
            ->email('email')
            ->regex('code', '/^\d{6}$/', 'Code must be 6 digits.')
            ->validate();

        $user = (new User())->getByEmail(strtolower($data['email']));
        if (!$user) throw new Exception('Invalid or expired code.', 400);

        $now = date('Y-m-d H:i:s');

        $row = $this->fetchOne(
            "SELECT * FROM password_resets
             WHERE user_id = ? AND used_at IS NULL AND expires_at > ?
             ORDER BY id DESC LIMIT 1",
            [$user['id'], $now]
        );

        if (!$row) throw new Exception('Invalid or expired code.', 400);
        if ((int)$row['attempts'] >= 5) {
            throw new Exception('Too many wrong attempts. Please request a new code.', 400);
        }

        if (!password_verify($data['code'], $row['code_hash'])) {
            $this->executeQuery("UPDATE password_resets SET attempts = attempts + 1 WHERE id = ?", [$row['id']]);
            throw new Exception('Invalid or expired code.', 400);
        }

        $token = bin2hex(random_bytes(32));
        $this->update('password_resets', [
            'token_hash' => password_hash($token, PASSWORD_BCRYPT, ['cost' => 10]),
        ], 'id = ?', [$row['id']]);

        return ['reset_token' => $token];
    }

    // ---------------------------------------------------------
    // RESET PASSWORD (Step 3) - Fixed Timezone
    // ---------------------------------------------------------
    public function resetPassword(array $input): array {
        $data = (new Validator($input))
            ->required('email', 'reset_token', 'new_password')
            ->email('email')
            ->password('new_password')
            ->validate();

        $user = (new User())->getByEmail(strtolower($data['email']));
        if (!$user) throw new Exception('Invalid reset session.', 400);

        $now = date('Y-m-d H:i:s');

        $row = $this->fetchOne(
            "SELECT * FROM password_resets
             WHERE user_id = ? AND used_at IS NULL AND expires_at > ? AND token_hash IS NOT NULL
             ORDER BY id DESC LIMIT 1",
            [$user['id'], $now]
        );

        if (!$row || !password_verify($data['reset_token'], $row['token_hash'])) {
            throw new Exception('Invalid or expired reset session.', 400);
        }

        (new User())->setPassword((int)$user['id'], $data['new_password']);
        $this->executeQuery("UPDATE password_resets SET used_at = ? WHERE id = ?", [$now, $row['id']]);

        Logger::security('Password reset completed', ['user_id' => $user['id']]);
        return ['message' => 'Password updated. You can now log in.'];
    }

    // ---------------------------------------------------------
    // VERIFY EMAIL - Fixed user_id column
    // ---------------------------------------------------------
    public function verifyEmail(array $input): array {
        $data = (new Validator($input))
            ->required('email', 'code')
            ->email('email')
            ->validate();

        $user = (new User())->getByEmail(strtolower($data['email']));
        if (!$user) throw new Exception('User not found.', 404);
        
        if ($user['email_verified_at']) {
            throw new Exception('This email is already verified.', 400);
        }

        $now = date('Y-m-d H:i:s');

        // FIXED: Changed unique_id to user_id to match the table structure
        $record = $this->fetchOne(
            "SELECT * FROM email_verifications 
             WHERE user_id = ? AND used_at IS NULL 
             ORDER BY id DESC LIMIT 1", 
            [$user['id']]
        );

        if (!$record) throw new Exception('No verification code found. Please register again.', 400);
        
        if (strtotime($record['expires_at']) < time()) {
            throw new Exception('This code has expired. Please request a new one.', 400);
        }

        if (!password_verify($data['code'], $record['code_hash'])) {
            throw new Exception('Invalid verification code.', 400);
        }

        $this->executeQuery("UPDATE email_verifications SET used_at = ? WHERE id = ?", [$now, $record['id']]);
        (new User())->markEmailVerified($user['id']);

        return ['message' => 'Email verified successfully.'];
    }
}