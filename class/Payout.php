<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Paystack.php';
require_once __DIR__ . '/Exam.php';
require_once __DIR__ . '/Logger.php';

class Payout extends Database {
    
    // --- NEW: For Admin Master View ---
    public function getFinishedExams(): array {
        // Fetch open exams that have ended AND have at least 3 participants
        return $this->fetchAll(
            "SELECT e.id, e.title, e.subject, e.ends_at, 
                    COUNT(a.id) as participant_count
             FROM exams e
             JOIN exam_attempts a ON e.id = a.exam_id AND a.status = 'submitted'
             WHERE e.status = 'open' AND e.ends_at < NOW()
             GROUP BY e.id
             HAVING participant_count >= 3
             ORDER BY e.ends_at DESC"
        );
    }

    // --- NEW: For Admin Detail View ---
    public function getExamParticipants(int $examId): array {
        return $this->fetchAll(
            "SELECT u.first_name, u.last_name, u.email,
                    a.score_percentage, a.correct_count, a.wrong_count, 
                    a.duration_seconds, a.submitted_at
             FROM exam_attempts a
             JOIN users u ON a.user_id = u.id
             WHERE a.exam_id = ? AND a.status = 'submitted'
             ORDER BY a.score_percentage DESC, a.duration_seconds ASC",
            [$examId]
        );
    }

    // --- Existing Method (Untouched) ---
    public function settleExam(int $examId): array {
        $exam = (new Exam())->getById($examId);
        if ($exam['status'] !== 'open') {
            throw new Exception('Only open exams can be settled.', 400);
        }

        // STRICT RULE: Cannot settle before the end date
        if (strtotime($exam['ends_at']) > time()) {
            throw new Exception('Cannot settle exam. The exam period has not ended yet.', 400);
        }

        // 1. Get Top 3 Users (Highest score, then fastest time)
        $topUsers = $this->fetchAll(
            "SELECT user_id, score_percentage, duration_seconds 
             FROM exam_attempts 
             WHERE exam_id = ? AND status = 'submitted'
             ORDER BY score_percentage DESC, duration_seconds ASC
             LIMIT 3",
            [$examId]
        );

        if (count($topUsers) === 0) {
            throw new Exception('No one has taken this exam yet.', 400);
        }

        // 2. Calculate Prize Pool
        $totalRevenue = (int)$this->fetchValue("SELECT COALESCE(SUM(amount_kobo), 0) FROM payments WHERE exam_id = ? AND status = 'success'", [$examId]);
        $prizePool = (int)($totalRevenue * ($exam['prize_pool_pct'] / 100));
        
        // 3. Determine splits (50%, 30%, 20%)
        $splits = [1 => 0.50, 2 => 0.30, 3 => 0.20];
        
        $this->beginTransaction();
        try {
            foreach ($topUsers as $index => $user) {
                $rank = $index + 1;
                $prizeAmount = (int)($prizePool * $splits[$rank]);

                // Check if payout already exists for this user and exam to avoid duplicates
                $existing = $this->fetchValue("SELECT COUNT(*) FROM payouts WHERE exam_id = ? AND user_id = ?", [$examId, $user['user_id']]);
                if (!$existing) {
                    $this->insert('payouts', [
                        'exam_id' => $examId,
                        'user_id' => $user['user_id'],
                        'rank_position' => $rank,
                        'reference' => 'PAYOUT-' . strtoupper(bin2hex(random_bytes(4))),
                        'amount_kobo' => $prizeAmount,
                        'status' => 'pending'
                    ]);
                }
            }

            $this->update('exams', ['status' => 'settled'], 'id = ?', [$examId]);
            $this->commit();
            
            return ['message' => 'Exam settled successfully. Payouts are pending.'];
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    // --- Existing Method (Untouched) ---
    public function pay(array $input): array {
        $payoutId = (int)($input['payout_id'] ?? 0);
        $bankCode = $input['bank_code'] ?? '';
        $accountNumber = $input['account_number'] ?? '';
        
        if (!$payoutId || !$bankCode || !$accountNumber) {
            throw new Exception('Missing payout details.', 400);
        }

        $payout = $this->fetchOne("SELECT p.*, u.first_name, u.last_name FROM payouts p JOIN users u ON p.user_id = u.id WHERE p.id = ?", [$payoutId]);
        
        if (!$payout || $payout['status'] !== 'pending') {
            throw new Exception('Payout not found or already paid.', 400);
        }

        $paystack = new Paystack();
        $name = $payout['first_name'] . ' ' . $payout['last_name'];
        
        try {
            $recipientCode = $paystack->createTransferRecipient($name, $accountNumber, $bankCode);
            $transfer = $paystack->initiateTransfer(
                (int)$payout['amount_kobo'], 
                $recipientCode, 
                "Lumos Prize - Rank {$payout['rank_position']}"
            );

            $this->update('payouts', [
                'status' => 'processing',
                'gateway_ref' => $transfer['reference'],
                'paid_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$payoutId]);

            Logger::info('Payout initiated', ['payout_id' => $payoutId, 'ref' => $transfer['reference']]);
            return ['message' => 'Payout initiated successfully.'];
            
        } catch (Exception $e) {
            Logger::error('Payout failed', ['payout_id' => $payoutId, 'err' => $e->getMessage()]);
            throw new Exception('Payout failed: ' . $e->getMessage(), 500);
        }
    }

    // --- Existing Method (Untouched) ---
    public function getPendingPayouts(): array {
        return $this->fetchAll(
            "SELECT p.id, p.amount_kobo, p.rank_position, e.title as exam_title, u.first_name, u.last_name 
             FROM payouts p 
             JOIN exams e ON p.exam_id = e.id 
             JOIN users u ON p.user_id = u.id 
             WHERE p.status = 'pending'"
        );
    }
}