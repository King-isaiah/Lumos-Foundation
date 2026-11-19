<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Validator.php';

class Exam extends Database {

    public function getAll(): array {
        return $this->fetchAll(
            "SELECT id, slug, title, subject, description, price_kobo, duration_minutes, 
                    question_count, starts_at, ends_at, status, banner_url
             FROM exams 
             WHERE status NOT IN ('draft', 'deleted')
             ORDER BY created_at DESC"
        );
    }

    public function getBySlug(string $slug): ?array {
        $exam = $this->fetchOne(
            "SELECT * FROM exams WHERE slug = ? AND status != 'deleted'",
            [$slug]
        );

        if (!$exam) return null;
        $exam['current_prize_pool_kobo'] = $this->calculatePrizePool((int)$exam['id']);
        return $exam;
    }

    public function getById(int $id): ?array {
        $exam = $this->fetchOne("SELECT * FROM exams WHERE id = ?", [$id]);
        if ($exam) {
            $exam['current_prize_pool_kobo'] = $this->calculatePrizePool($id);
        }
        return $exam;
    }

    public function getAllWithStats(): array {
        return $this->fetchAll(
            "SELECT e.*, 
                (SELECT COUNT(*) FROM exam_attempts a WHERE a.exam_id = e.id AND a.status = 'submitted') as student_count,
                (SELECT COUNT(*) FROM payouts p WHERE p.exam_id = e.id) as winners_count
             FROM exams e 
             WHERE e.status != 'deleted'
             ORDER BY e.created_at DESC"
        );
    }

    public function create(array $input): array {
        $data = (new Validator($input))
            ->required('title', 'subject', 'price_kobo', 'duration_minutes', 'question_count', 'starts_at', 'ends_at')
            ->validate();

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title']))) . '-' . rand(1000, 9999);
        
        if (strtotime($data['starts_at']) >= strtotime($data['ends_at'])) {
            throw new Exception('End date must be after start date.', 400);
        }

        $adminId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;

        $id = $this->insert('exams', [
            'slug'              => $slug,
            'title'             => $data['title'],
            'subject'           => $data['subject'],
            'description'       => $data['description'] ?? null,
            'rules_text'        => $data['rules_text'] ?? null,
            'price_kobo'        => (int)$data['price_kobo'],
            'duration_minutes'  => (int)$data['duration_minutes'],
            'question_count'    => (int)$data['question_count'],
            'starts_at'         => date('Y-m-d H:i:s', strtotime($data['starts_at'])),
            'ends_at'           => date('Y-m-d H:i:s', strtotime($data['ends_at'])),
            'status'            => 'draft',
            'prize_pool_pct'    => $data['prize_pool_pct'] ?? 60.00,
            'created_by'        => $adminId,
        ]);

        Logger::info('Exam created', ['exam_id' => $id, 'title' => $data['title']]);
        return ['id' => $id, 'slug' => $slug];
    }

    public function updateExam(int $id, array $input): void {
        $exam = $this->getById($id);
        if (!$exam) throw new Exception('Exam not found.', 404);

        // LOCK: Prevent editing if not a draft
        if ($exam['status'] !== 'draft') {
            throw new Exception('Cannot edit an exam once it has been activated. It is locked.', 403);
        }

        $data = (new Validator($input))
            ->required('title', 'subject', 'price_kobo', 'duration_minutes', 'question_count', 'starts_at', 'ends_at')
            ->validate();

        if (strtotime($data['starts_at']) >= strtotime($data['ends_at'])) {
            throw new Exception('End date must be after start date.', 400);
        }

        $this->update('exams', [
            'title'             => $data['title'],
            'subject'           => $data['subject'],
            'description'       => $data['description'] ?? null,
            'price_kobo'        => (int)$data['price_kobo'],
            'duration_minutes'  => (int)$data['duration_minutes'],
            'question_count'    => (int)$data['question_count'],
            'starts_at'         => date('Y-m-d H:i:s', strtotime($data['starts_at'])),
            'ends_at'           => date('Y-m-d H:i:s', strtotime($data['ends_at'])),
            'prize_pool_pct'    => $data['prize_pool_pct'] ?? 60.00,
        ], 'id = ?', [$id]);

        Logger::info('Exam updated', ['exam_id' => $id]);
    }

    public function softDelete(int $id): void {
        $exam = $this->getById($id);
        if (!$exam) throw new Exception('Exam not found.', 404);

        // LOCK: Prevent deleting if not a draft
        if ($exam['status'] !== 'draft') {
            throw new Exception('Cannot delete an exam once it has been activated.', 403);
        }

        $this->update('exams', ['status' => 'deleted'], 'id = ?', [$id]);
        Logger::security('Exam soft deleted', ['exam_id' => $id]);
    }

    public function publish(int $id): void {
        $exam = $this->getById($id);
        if (!$exam) throw new Exception('Exam not found', 404);

        $actualQuestions = (int)$this->fetchValue("SELECT COUNT(*) FROM exam_questions WHERE exam_id = ?", [$id]);
        if ($actualQuestions !== (int)$exam['question_count']) {
            throw new Exception("Cannot publish. Exam requires {$exam['question_count']} questions, but only {$actualQuestions} exist.", 400);
        }

        $this->update('exams', ['status' => 'open'], 'id = ?', [$id]);
        Logger::info('Exam published', ['exam_id' => $id]);
    }

    private function calculatePrizePool(int $examId): int {
        $totalRevenue = (int)$this->fetchValue(
            "SELECT COALESCE(SUM(amount_kobo), 0) FROM payments WHERE exam_id = ? AND status = 'success'",
            [$examId]
        );
        
        $exam = $this->fetchOne("SELECT prize_pool_pct FROM exams WHERE id = ?", [$examId]);
        $pct = (float)($exam['prize_pool_pct'] ?? 60.00) / 100;

        return (int)($totalRevenue * $pct);
    }
    public function getActiveExams(): array {
        return $this->fetchAll(
            "SELECT id, slug, title, subject, description, rules_text, price_kobo, duration_minutes, 
                    question_count, starts_at, ends_at, status, banner_url, prize_pool_pct
             FROM exams 
             WHERE status = 'open'
             ORDER BY starts_at ASC"
        );
    }
    public function getAnalytics(int $id): array {
        $exam = $this->getById($id);
        if (!$exam) throw new Exception('Exam not found.', 404);

        // 1. Get Student Count & Highest Markdd
        $stats = $this->fetchOne(
            "SELECT COUNT(id) as student_count, MAX(score_percentage) as highest_mark 
             FROM exam_attempts 
             WHERE exam_id = ? AND status = 'submitted'",
            [$id]
        );

        $studentCount = (int)($stats['student_count'] ?? 0);
        $highestMark = $stats['highest_mark'] !== null ? round((float)$stats['highest_mark'], 2) : null;

        // 2. Most Failed / Most Gotten Question (Optional/Bonus logic)
        // We will query the exam_answers table to find the question with the most 'is_correct = 0'
        $mostFailedQ = $this->fetchOne(
            "SELECT q.question_text, COUNT(a.id) as fail_count
             FROM exam_answers a
             JOIN exam_questions q ON a.question_id = q.id
             WHERE a.attempt_id IN (SELECT id FROM exam_attempts WHERE exam_id = ? AND status = 'submitted') 
             AND a.is_correct = 0
             GROUP BY q.id
             ORDER BY fail_count DESC
             LIMIT 1",
            [$id]
        );

        return [
            'title' => $exam['title'],
            'description' => $exam['description'] ?: 'No description provided.',
            'duration_minutes' => $exam['duration_minutes'],
            'question_count' => $exam['question_count'],
            'created_at' => date('M j, Y', strtotime($exam['created_at'])),
            'starts_at' => date('M j, Y g:i A', strtotime($exam['starts_at'])),
            'ends_at' => date('M j, Y g:i A', strtotime($exam['ends_at'])),
            'student_count' => $studentCount,
            'highest_mark' => $highestMark,
            'most_failed_question' => $mostFailedQ ? $mostFailedQ['question_text'] : 'N/A (Not enough data)'
        ];
    }
}