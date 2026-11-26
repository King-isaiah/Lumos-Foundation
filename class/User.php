<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Validator.php';
require_once __DIR__ . '/ImageUploader.php'; 
require_once __DIR__ . '/Mailer.php'; 

class User extends Database {

    // ---------------------------------------------------------
    // Read Methods
    // ---------------------------------------------------------
    public function getById(int $id): ?array {
        return $this->fetchOne(
            "SELECT id, unique_id, username, email, first_name, last_name,
                    phone, gender, avatar_url, role, account_status,
                    email_verified_at, last_seen_at, created_at
             FROM users WHERE id = ?",
            [$id]
        );
    }

    public function getByUniqueId(int $uniqueId): ?array {
        return $this->fetchOne(
            "SELECT id, unique_id, username, email, first_name, last_name,
                    phone, gender, avatar_url, role, account_status,
                    email_verified_at, last_seen_at, created_at
             FROM users WHERE unique_id = ?",
            [$uniqueId]
        );
    }
    
    public function getAllUsers(): array {
        return $this->fetchAll(
            "SELECT id, unique_id, username, email, first_name, last_name, role, account_status, created_at 
             FROM users 
             ORDER BY created_at DESC"
        );
    }

    public function getByEmail(string $email): ?array {
        return $this->fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
    }

    public function getByUsername(string $username): ?array {
        return $this->fetchOne("SELECT * FROM users WHERE username = ?", [$username]);
    }

    public function emailExists(string $email): bool {
        return $this->recordExists('users', ['email' => $email]);
    }

    public function usernameExists(string $username): bool {
        return $this->recordExists('users', ['username' => $username]);
    }

    // ---------------------------------------------------------
    // Create
    // ---------------------------------------------------------
    public function create(array $input): array {
        $data = (new Validator($input))
            ->required('email', 'username', 'password', 'first_name', 'last_name')
            ->email('email')
            ->min('username', 3)->max('username', 30)
            ->max('first_name', 50)->max('last_name', 50)
            ->password('password')
            ->in('gender', ['male', 'female', 'other', null, ''])
            ->validate();

        // Uniqueness
        if ($this->emailExists($data['email'])) {
            throw new Exception('An account with that email already exists.', 409);
        }
        if ($this->usernameExists($data['username'])) {
            throw new Exception('That username is taken.', 409);
        }

        $uniqueId = $this->generateUniqueId();

        // Handle Base64 Image via ImgBB
        $avatarUrl = null;
        if (!empty($data['avatar_base64'])) {
            try {
                $avatarUrl = ImageUploader::uploadToImgBB($data['avatar_base64']);
            } catch (Exception $e) {
                Logger::error('Avatar upload failed during registration', ['error' => $e->getMessage()]);
            }
        }

        $id = $this->insert('users', [
            'unique_id'       => $uniqueId,
            'username'        => $data['username'],
            'email'           => strtolower($data['email']),
            'password_hash'   => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            'first_name'      => $data['first_name'],
            'last_name'       => $data['last_name'],
            'phone'           => $data['phone'] ?? null,
            'gender'          => $data['gender'] ?? null,
            'avatar_url'      => $avatarUrl,
            'role'            => 'user',
            'account_status'  => 'active',
        ]);

        Logger::info('User registered', ['user_id' => $id, 'username' => $data['username']]);

        // Generate OTP and send verification email
        $otp = (string)random_int(100000, 999999);
        
        $this->insert('email_verifications', [
            'user_id'    => $id,
            'code_hash'  => password_hash($otp, PASSWORD_BCRYPT, ['cost' => 10]),
            'expires_at' => date('Y-m-d H:i:s', time() + 300)
        ]);

        try {
            $mailer = new Mailer();
            $mailer->sendVerificationEmail($data['email'], $data['first_name'], $otp);
            Logger::info('Verification email sent', ['user_id' => $id]);
        } catch (Throwable $e) {
            Logger::error('Failed to send verification email', ['user_id' => $id, 'error' => $e->getMessage()]);
        }

        return ['id' => $id, 'unique_id' => $uniqueId];
    }

    // ---------------------------------------------------------
    // Update Methods
    // ---------------------------------------------------------
    public function updateProfile(int $id, array $input): array {
        // 1. Filter allowed text fields (excluding avatar_base64 for now)
        $allowed = ['first_name', 'last_name', 'phone', 'gender'];
        $patch   = array_intersect_key($input, array_flip($allowed));

        if (isset($patch['first_name'])) $patch['first_name'] = trim($patch['first_name']);
        if (isset($patch['last_name']))  $patch['last_name']  = trim($patch['last_name']);

        if (isset($patch['gender']) && !in_array($patch['gender'], ['male','female','other'], true)) {
            throw new Exception('Invalid gender value.', 400);
        }

        // 2. Handle the Avatar if a new Base64 string was provided
        if (!empty($input['avatar_base64'])) {
            try {
                // Upload to ImgBB and get the URL
                $avatarUrl = ImageUploader::uploadToImgBB($input['avatar_base64']);
                $patch['avatar_url'] = $avatarUrl; 
            } catch (Exception $e) {
                Logger::error('Avatar upload failed during profile update', ['user_id' => $id, 'error' => $e->getMessage()]);
                throw new Exception('Failed to upload profile picture. Please try a smaller image.', 400);
            }
        }

        if (empty($patch)) {
            throw new Exception('Nothing to update.', 400);
        }

        // 3. Update the database
        $this->update('users', $patch, 'id = ?', [$id]);
        
        // 4. Return the updated user object so the frontend can refresh instantly
        return $this->getById($id);
    }

    public function setPassword(int $id, string $newPassword): void {
        if (strlen($newPassword) < 8) {
            throw new Exception('Password must be at least 8 characters.', 400);
        }
        $this->update('users', [
            'password_hash' => password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]),
        ], 'id = ?', [$id]);
        Logger::security('Password changed', ['user_id' => $id]);
    }

    public function markEmailVerified(int $id): void {
        $this->update('users', ['email_verified_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
    }

    public function touchLastSeen(int $id): void {
        $this->update('users', ['last_seen_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
    }

    // ---------------------------------------------------------
    // Admin Methods
    // ---------------------------------------------------------
    public function toggleStatus(array $input): void {
        $userId = (int)($input['user_id'] ?? 0);
        $newStatus = $input['status'] ?? '';

        if (!$userId || !in_array($newStatus, ['active', 'suspended'])) {
            throw new Exception('Invalid user ID or status provided.', 400);
        }

        $targetUser = $this->getById($userId);
        if (!$targetUser) {
            throw new Exception('User not found.', 404);
        }

        // Security: Prevent suspending other Admins
        if ($targetUser['role'] === 'admin' || $targetUser['role'] === 'super_admin') {
            throw new Exception('Cannot suspend or modify an admin account.', 403);
        }

        $this->update('users', ['account_status' => $newStatus], 'id = ?', [$userId]);
        
        Logger::security('User status changed by Admin', [
            'target_user_id' => $userId, 
            'new_status' => $newStatus, 
            'admin_id' => $_SESSION['user_id']
        ]);
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------
    private function generateUniqueId(): int {
        do {
            $length = random_int(5, 8);
            $min = (int) ('1' . str_repeat('0', $length - 1));
            $max = (int) str_repeat('9', $length);
            $candidate = random_int($min, $max);
        } while ($this->recordExists('users', ['unique_id' => $candidate]));
        
        return $candidate;
    }
}