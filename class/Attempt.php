<?php
require_once __DIR__ . '/Database.php';

class Attempt extends Database {

    public function start(int $userId, int $examId): array {
        // 1. Check if there's an ongoing attempt
        $existing = $this->fetchOne(
            "SELECT id FROM exam_attempts WHERE user_id = ? AND exam_id = ? AND status = 'in_progress'",
            [$userId, $examId]
        );

        if ($existing) {
            return ['attempt_id' => $existing['id']];
        }

        // 2. Check if already submitted
        $isTaken = $this->fetchValue(
            "SELECT COUNT(*) FROM exam_attempts WHERE user_id = ? AND exam_id = ? AND status = 'submitted'",
            [$userId, $examId]
        );
        if ($isTaken) throw new Exception("You have already taken this exam.", 403);

        // 3. Check if user paid
        $hasPaid = $this->fetchValue(
            "SELECT COUNT(*) FROM payments WHERE user_id = ? AND exam_id = ? AND status = 'success'",
            [$userId, $examId]
        );
        if (!$hasPaid) throw new Exception("Payment required to start this exam.", 403);

        // 4. Get exam details and check time
        $exam = $this->fetchOne("SELECT duration_minutes, starts_at, ends_at FROM exams WHERE id = ?", [$examId]);
        
        $now = time();
        if ($now < strtotime($exam['starts_at']) || $now > strtotime($exam['ends_at'])) {
            throw new Exception("This exam is not currently active.", 403);
        }

        // 5. Create the attempt
        $attemptId = $this->insert('exam_attempts', [
            'user_id' => $userId,
            'exam_id' => $examId,
            'status'  => 'in_progress',
            'started_at' => date('Y-m-d H:i:s'),
        ]);

        return ['attempt_id' => $attemptId];
    }

    public function getRoomDetails(int $userId, int $attemptId): array {
        $rawAttempt = $this->fetchOne(
            "SELECT id, user_id, status, started_at FROM exam_attempts WHERE id = ?",
            [$attemptId]
        );

        if (!$rawAttempt) {
            throw new Exception("Attempt ID {$attemptId} does not exist in the database.", 404);
        }
        if ($rawAttempt['user_id'] != $userId) {
            throw new Exception("Security Error: This attempt belongs to another user.", 403);
        }
        if ($rawAttempt['status'] !== 'in_progress') {
            throw new Exception("This exam is already {$rawAttempt['status']}. You cannot enter.", 403);
        }

        $attempt = $this->fetchOne(
            "SELECT a.*, e.id as exam_id, e.title, e.duration_minutes, e.starts_at, e.ends_at 
             FROM exam_attempts a 
             JOIN exams e ON a.exam_id = e.id 
             WHERE a.id = ?",
            [$attemptId]
        );

        $startTime = $attempt['started_at'] ? strtotime($attempt['started_at']) : time();
        $durationSeconds = $attempt['duration_minutes'] * 60;
        $endTime = $startTime + $durationSeconds;
        $timeRemaining = $endTime - time();

        if ($timeRemaining <= 0) {
            $this->submit($userId, ['attempt_id' => $attemptId, 'answers' => []]);
            throw new Exception("Time has expired. Your exam has been submitted.", 400);
        }

        $questions = $this->fetchAll(
            "SELECT id, question_text, options_json, question_type, media_url 
             FROM exam_questions 
             WHERE exam_id = ?", 
            [$attempt['exam_id']]
        );

        if (empty($questions)) {
            throw new Exception("No questions found for this exam. Please contact support.", 404);
        }

        shuffle($questions);
        foreach ($questions as &$q) {
            $opts = json_decode($q['options_json'], true);
            if (is_array($opts)) {
                shuffle($opts); 
                $q['options_json'] = $opts;
            } else {
                $q['options_json'] = [];
            }
        }

        return [
            'attempt_id' => $attemptId,
            'exam_title' => $attempt['title'],
            'time_remaining_seconds' => $timeRemaining,
            'questions' => $questions
        ];
    }

    public function submit(int $userId, array $input): array {
        $attemptId = (int)($input['attempt_id'] ?? 0);
        $answers = $input['answers'] ?? [];

        $attempt = $this->fetchOne(
            "SELECT * FROM exam_attempts WHERE id = ? AND user_id = ? AND status = 'in_progress'",
            [$attemptId, $userId]
        );
        
        if (!$attempt) {
            $checkSubmitted = $this->fetchOne("SELECT status FROM exam_attempts WHERE id = ? AND user_id = ?", [$attemptId, $userId]);
            if ($checkSubmitted && $checkSubmitted['status'] === 'submitted') {
                return ['score' => 'Already Submitted'];
            }
            throw new Exception("Invalid attempt.", 404);
        }

        $examId = $attempt['exam_id'];
        $questions = $this->fetchAll("SELECT id, correct_option_key FROM exam_questions WHERE exam_id = ?", [$examId]);
        
        $totalQuestions = count($questions);
        $score = 0;
        $wrongCount = 0;

        foreach ($questions as $q) {
            // Fallback to null if skipped
            $selected = $answers[$q['id']] ?? null; 
            
            $isCorrect = ($selected !== null && $selected === $q['correct_option_key']) ? 1 : 0;
            
            if ($isCorrect) {
                $score++;
            } else {
                $wrongCount++;
            }

            // FIXED: Changed 'selected_option_key' to 'chosen_option_key' to match your database
            $this->insert('exam_answers', [
                'attempt_id' => $attemptId,
                'question_id' => $q['id'],
                'chosen_option_key' => $selected, 
                'is_correct' => $isCorrect
            ]);
        }

        $percentage = $totalQuestions > 0 ? ($score / $totalQuestions) * 100 : 0;
        $durationSeconds = time() - strtotime($attempt['started_at']);

        $this->update('exam_attempts', [
            'status' => 'submitted',
            'score_percentage' => round($percentage, 2),
            'submitted_at' => date('Y-m-d H:i:s'),
            'duration_seconds' => $durationSeconds,
            'total_questions' => $totalQuestions,
            'correct_count' => $score,
            'wrong_count' => $wrongCount
        ], 'id = ?', [$attemptId]);

        return ['score' => round($percentage, 2)];
    }

    // KEPT: Exactly as you requested
    public function getActiveAttempt(int $userId, int $attemptId): array {
        $attempt = $this->fetchOne("SELECT * FROM exam_attempts WHERE id = ? AND user_id = ?", [$attemptId, $userId]);
        if (!$attempt) throw new Exception('Attempt not found.', 404);
        if ($attempt['status'] !== 'in_progress') throw new Exception('Exam already submitted.', 400);

        $exam = (new Exam())->getById($attempt['exam_id']);
        
        $timeRemaining = ($exam['duration_minutes'] * 60) - (time() - strtotime($attempt['started_at']));

        if ($timeRemaining <= 0) {
            $this->submit($userId, ['attempt_id' => $attemptId, 'answers' => []]); // Auto-submit
            throw new Exception('Time is up. Exam submitted automatically.', 400);
        }

        $questions = (new Question())->getByExam($attempt['exam_id']);
        $safeQuestions = array_map(function($q) {
            unset($q['correct_option_key']); 
            $q['options_json'] = json_decode($q['options_json'], true);
            return $q;
        }, $questions);

        return [
            'attempt_id' => $attemptId,
            'time_remaining_seconds' => (int)$timeRemaining,
            'questions' => $safeQuestions
        ];
    }

    // KEPT: Exactly as you requested
    public function getUserHistory(int $userId): array {
        return $this->fetchAll(
            "SELECT a.id, a.score_percentage, a.submitted_at, e.title, e.subject 
             FROM exam_attempts a
             JOIN exams e ON a.exam_id = e.id
             WHERE a.user_id = ? AND a.status = 'submitted'
             ORDER BY a.submitted_at DESC",
            [$userId]
        );
    }
}