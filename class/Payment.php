<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Paystack.php';
require_once __DIR__ . '/Exam.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/Notification.php';
require_once __DIR__ . '/Mailer.php';

class Payment extends Database {
    
    public function initializeExamEntry(int $userId, int $examId): string {
        $user = (new User())->getById($userId);
        $exam = (new Exam())->getById($examId);

        if (!$exam || $exam['status'] !== 'open') {
            throw new Exception('This exam is not currently open for registration.', 400);
        }

        $existing = $this->fetchOne("SELECT id FROM payments WHERE user_id = ? AND exam_id = ? AND status = 'success'", [$userId, $examId]);
        if ($existing) {
            throw new Exception('You have already paid for this exam.', 400);
        }

        $reference = 'LUMOS-' . strtoupper(bin2hex(random_bytes(4))) . '-' . time();
        
        $this->insert('payments', [
            'user_id' => $userId,
            'exam_id' => $examId,
            'reference' => $reference,
            'amount_kobo' => $exam['price_kobo'],
            'status' => 'pending'
        ]);

        $paystack = new Paystack();
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
    
    public function verifyTransaction(array $input): array {
        $reference = $input['reference'] ?? '';
        
        if (!$reference) {
            throw new Exception('Payment reference is required.', 400);
        }

        $payment = $this->fetchOne("SELECT * FROM payments WHERE reference = ?", [$reference]);
        if (!$payment) {
            throw new Exception('Payment record not found.', 404);
        }

        if ($payment['status'] === 'success') {
            return ['status' => 'success', 'message' => 'Payment already verified.', 'exam_id' => $payment['exam_id']];
        }

        $paystack = new Paystack();
        $verified = $paystack->verify($reference);

        if ($verified && $verified['status'] === 'success' && $verified['amount'] == $payment['amount_kobo']) {
            $this->update('payments', [
                'status' => 'success',
                'gateway_ref' => $verified['id'],
                'paid_at' => date('Y-m-d H:i:s'),
                'gateway_response_json' => json_encode($verified)
            ], 'id = ?', [$payment['id']]);

            try {
                $user = (new User())->getById((int)$payment['user_id']);
                $exam = (new Exam())->getById((int)$payment['exam_id']);
                $amountNaira = number_format($payment['amount_kobo'] / 100, 2);

                (new Notification())->create(
                    (int)$payment['user_id'],
                    'payment',
                    'Payment Successful',
                    "Your payment of ₦{$amountNaira} for {$exam['title']} was successful. Good luck on your exam!",
                    ['payment_id' => $payment['id'], 'exam_id' => $payment['exam_id']]
                );

                (new Mailer())->sendPaymentConfirmation($user['email'], $user['first_name'], $exam['title'], $amountNaira);

            } catch (Throwable $e) {
                Logger::error('Failed to send payment notification/email', ['error' => $e->getMessage(), 'payment_id' => $payment['id']]);
            }

            Logger::info('Payment verified and recorded', ['payment_id' => $payment['id'], 'reference' => $reference]);
            
            return ['status' => 'success', 'message' => 'Payment verified successfully.', 'exam_id' => $payment['exam_id']];
        }

        Logger::error('Payment verification failed', ['reference' => $reference, 'response' => $verified]);
        throw new Exception('Payment verification failed. Please contact support if you were debited.', 400);
    }

    public function processWebhook(string $reference, array $paystackData): void {
        $payment = $this->fetchOne("SELECT id, user_id, exam_id, status, amount_kobo FROM payments WHERE reference = ?", [$reference]);
        
        if ($payment && $payment['status'] === 'pending') {
            $this->update('payments', [
                'status' => 'success',
                'gateway_ref' => $paystackData['id'],
                'paid_at' => date('Y-m-d H:i:s'),
                'gateway_response_json' => json_encode($paystackData)
            ], 'id = ?', [$payment['id']]);

            try {
                $user = (new User())->getById((int)$payment['user_id']);
                $exam = (new Exam())->getById((int)$payment['exam_id']);
                $amountNaira = number_format($payment['amount_kobo'] / 100, 2);

                (new Notification())->create(
                    (int)$payment['user_id'],
                    'payment', 
                    'Payment Successful',
                    "Your payment of ₦{$amountNaira} for {$exam['title']} was successful. Good luck on your exam!",
                    ['payment_id' => $payment['id'], 'exam_id' => $payment['exam_id']]
                );

                (new Mailer())->sendPaymentConfirmation($user['email'], $user['first_name'], $exam['title'], $amountNaira);

            } catch (Throwable $e) {
                Logger::error('Webhook: Failed to send payment notification/email', ['error' => $e->getMessage(), 'payment_id' => $payment['id']]);
            }

            Logger::info('Payment confirmed via webhook', ['reference' => $reference, 'amount' => $payment['amount_kobo']]);
        }
    }

    public function getMyPayments(int $userId): array {
        return $this->fetchAll(
            "SELECT exam_id, status FROM payments WHERE user_id = ? AND status = 'success'",
            [$userId]
        );
    }

    public function getMyFinancialLedger(int $userId): array {
        $sql = "
            SELECT 
                'payment' as type,
                p.id as transaction_id,
                p.paid_at as transaction_date,
                e.title as exam_title,
                p.amount_kobo as amount,
                'out' as flow,
                p.status as status
            FROM payments p
            JOIN exams e ON p.exam_id = e.id
            WHERE p.user_id = ? AND p.status = 'success'
            
            UNION ALL
            
            SELECT 
                'payout' as type,
                py.id as transaction_id,
                py.created_at as transaction_date,
                e.title as exam_title,
                py.amount_kobo as amount,
                'in' as flow,
                py.status as status
            FROM payouts py
            JOIN exams e ON py.exam_id = e.id
            WHERE py.user_id = ?
            
            ORDER BY transaction_date DESC
        ";

        $ledger = $this->fetchAll($sql, [$userId, $userId]);

        foreach ($ledger as &$item) {
            $item['amount_formatted'] = '₦' . number_format($item['amount'] / 100, 2);
            $item['date_formatted'] = date('M j, Y g:i A', strtotime($item['transaction_date']));
            
            if ($item['type'] === 'payment') {
                $item['display_type'] = 'Exam Registration';
                $item['status_badge'] = 'completed';
            } else {
                $item['display_type'] = 'Prize Payout';
                $item['status_badge'] = $item['status'] === 'paid' ? 'completed' : 'pending';
            }
        }

        return $ledger;
    }
    /**
     * NEW: Logs a notification when a user prints/generates a receipt.
     */
    public function logReceiptGeneration(int $userId, array $input): void {
        // 1. Extract the payment ID from the input array
        $paymentId = (int)($input['payment_id'] ?? 0);
        
        if (!$paymentId) {
            throw new Exception('Payment ID is required.', 400);
        }

        $payment = $this->fetchOne(
            "SELECT p.amount_kobo, e.title as exam_title 
             FROM payments p 
             JOIN exams e ON p.exam_id = e.id 
             WHERE p.id = ? AND p.user_id = ? AND p.status = 'success'", 
            [$paymentId, $userId]
        );

        if ($payment) {
            $amount = number_format($payment['amount_kobo'] / 100, 2);
            
            (new Notification())->create(
                $userId,
                'payment', // Changed from 'receipt' to 'payment' because your DB ENUM only allows specific types
                'Receipt Generated',
                "You successfully generated a receipt for your ₦{$amount} payment for {$payment['exam_title']}.",
                ['payment_id' => $paymentId]
            );
        }
    }
}