<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Paystack.php';
require_once __DIR__ . '/Exam.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/../config.php';

class Payment extends Database {
    
    public function initializeExamEntry(int $userId, int $examId): string {
        $user = (new User())->getById($userId);
        $exam = (new Exam())->getById($examId);

        if (!$exam || $exam['status'] !== 'open') {
            throw new Exception('This exam is not currently open for registration.', 400);
        }

        // Check if already paid
        $existing = $this->fetchOne("SELECT id FROM payments WHERE user_id = ? AND exam_id = ? AND status = 'success'", [$userId, $examId]);
        if ($existing) {
            throw new Exception('You have already paid for this exam.', 400);
        }

        $reference = 'LUMOS-' . strtoupper(bin2hex(random_bytes(4))) . '-' . time();
        
        // Save as pending
        $this->insert('payments', [
            'user_id' => $userId,
            'exam_id' => $examId,
            'reference' => $reference,
            'amount_kobo' => $exam['price_kobo'],
            'status' => 'pending'
        ]);

        $paystack = new Paystack();
        // FIXED: Pointing to the new callback location in the students folder
        $callbackUrl = Config::appUrl() . '/public/students/payment-callback.php?reference=' . $reference;
        
        $result = $paystack->initialize($user['email'], $exam['price_kobo'], $reference, $callbackUrl);
        
        return $result['authorization_url'];
    }

    public function verifyPayment(string $reference): array {
        $payment = $this->fetchOne("SELECT * FROM payments WHERE reference = ?", [$reference]);
        if (!$payment) throw new Exception('Payment record not found.', 404);

        if ($payment['status'] === 'success') return ['message' => 'Already verified'];

        $paystack = new Paystack();
        $verified = $paystack->verify($reference);

        if ($verified && $verified['amount'] == $payment['amount_kobo']) {
            $this->update('payments', [
                'status' => 'success',
                'gateway_ref' => $verified['id'],
                'paid_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$payment['id']]);

            return ['message' => 'Payment successful', 'exam_id' => $payment['exam_id']];
        }

        throw new Exception('Payment verification failed.', 400);
    }

    public function getAllPayments(): array {
        return $this->fetchAll(
            "SELECT p.*, u.first_name, u.last_name, u.email, e.title as exam_title 
             FROM payments p 
             JOIN users u ON p.user_id = u.id 
             JOIN exams e ON p.exam_id = e.id 
             ORDER BY p.created_at DESC"
        );
    }

    // FIXED: Changed to accept array to match ApiHandler routing
    public function manualVerify(array $input): void {
        $paymentId = (int)($input['id'] ?? 0);
        if (!$paymentId) throw new Exception('Payment ID is required.', 400);

        $payment = $this->fetchOne("SELECT * FROM payments WHERE id = ?", [$paymentId]);
        if (!$payment) throw new Exception('Payment record not found.', 404);
        if ($payment['status'] === 'success') throw new Exception('Payment is already successful.', 400);

        $this->update('payments', [
            'status' => 'success',
            'paid_at' => date('Y-m-d H:i:s'),
            'gateway_response_json' => json_encode(['message' => 'Manually verified by Admin'])
        ], 'id = ?', [$paymentId]);

        Logger::info('Payment manually verified', ['payment_id' => $paymentId, 'admin_id' => $_SESSION['user_id']]);
    }

    public function manualCreate(array $input): void {
        $userId = (int)($input['user_id'] ?? 0);
        $examId = (int)($input['exam_id'] ?? 0);
        
        if (!$userId || !$examId) throw new Exception('User and Exam are required.', 400);

        $exam = $this->fetchOne("SELECT price_kobo, status FROM exams WHERE id = ?", [$examId]);
        if (!$exam || $exam['status'] !== 'open') {
            throw new Exception('Cannot manually enroll a user in a non-active exam.', 400);
        }

        $existing = $this->fetchOne("SELECT id FROM payments WHERE user_id = ? AND exam_id = ? AND status = 'success'", [$userId, $examId]);
        if ($existing) {
            throw new Exception('User has already successfully paid for this exam.', 400);
        }

        $reference = 'MANUAL-' . strtoupper(bin2hex(random_bytes(4))) . '-' . time();

        $this->insert('payments', [
            'user_id' => $userId,
            'exam_id' => $examId,
            'reference' => $reference,
            'amount_kobo' => $exam['price_kobo'],
            'status' => 'success',
            'paid_at' => date('Y-m-d H:i:s'),
            'gateway_response_json' => json_encode(['message' => 'Manually created by Admin'])
        ]);

        Logger::info('Manual payment created by Admin', ['user_id' => $userId, 'exam_id' => $examId, 'admin_id' => $_SESSION['user_id']]);
    }

    public function getOpenExams(): array {
        return $this->fetchAll("SELECT id, title FROM exams WHERE status = 'open'");
    }

    public function getAllUsers(): array {
        return $this->fetchAll("SELECT id, first_name, last_name, email FROM users WHERE role = 'user'");
    }
    
    /**
     * Verifies a payment with Paystack and updates the database.
     * This is called from the callback page.
     */
    public function verifyTransaction(string $reference): array {
        $payment = $this->fetchOne("SELECT * FROM payments WHERE reference = ?", [$reference]);
        if (!$payment) {
            throw new Exception('Payment record not found.', 404);
        }

        // Idempotency: If already success, return early
        if ($payment['status'] === 'success') {
            return ['status' => 'success', 'message' => 'Payment already verified.'];
        }

        $paystack = new Paystack();
        $verified = $paystack->verify($reference);

        if ($verified && $verified['status'] === 'success' && $verified['amount'] == $payment['amount_kobo']) {
            // Payment is legitimate. Update the DB.
            $this->update('payments', [
                'status' => 'success',
                'gateway_ref' => $verified['id'],
                'paid_at' => date('Y-m-d H:i:s'),
                'gateway_response_json' => json_encode($verified)
            ], 'id = ?', [$payment['id']]);

            Logger::info('Payment verified and recorded', ['payment_id' => $payment['id'], 'reference' => $reference]);
            
            return ['status' => 'success', 'message' => 'Payment verified successfully.', 'exam_id' => $payment['exam_id']];
        }

        // Verification failed
        Logger::error('Payment verification failed', ['reference' => $reference, 'response' => $verified]);
        throw new Exception('Payment verification failed. Please contact support if you were debited.', 400);
    }
}