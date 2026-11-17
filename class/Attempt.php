<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Question.php';
require_once __DIR__ . '/Exam.php';

class Attempt extends Database {

    public function start(int $userId, int $examId): array {
        $paid = $this->fetchOne("SELECT id FROM payments WHERE user_id = ? AND exam_id = ? AND status = 'success'", [$userId, $examId]);
        if (!$paid) throw new Exception('You have not paid for this exam.', 403);

        $existing = $this->fetchOne("SELECT id FROM exam_attempts WHERE user_id = ? AND exam_id = ?", [$userId, $examId]);
        if ($existing) throw new Exception('You have already taken this exam.', 400);

        $attemptId = $this->insert('exam_attempts', [
            'exam_id' => $examId,
            'user_id' => $userId,
            'status' => 'in_progress',
            'total_questions' => (new Exam())->getById($examId)['question_count']
        ]);

        return ['attempt_id' => $attemptId];
    }

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

    // UPDATED: Now takes $input array directly
    public function submit(int $userId, array $input): array {
        $attemptId = (int)($input['attempt_id'] ?? 0);
        $answers = $input['answers'] ?? [];
        
        if (!$attemptId) throw new Exception('attempt_id is required.', 400);

        $attempt = $this->fetchOne("SELECT * FROM exam_attempts WHERE id = ? AND user_id = ?", [$attemptId, $userId]);
        if (!$attempt || $attempt['status'] !== 'in_progress') throw new Exception('Invalid attempt.', 400);

        $questions = (new Question())->getByExam($attempt['exam_id']);
        $correctCount = 0;
        $wrongCount = 0;

        $this->beginTransaction();
        try {
            foreach ($questions as $q) {
                $chosen = $answers[$q['id']] ?? null;
                $isCorrect = ($chosen === $q['correct_option_key']) ? 1 : 0;
                
                if ($isCorrect) $correctCount++;
                else $wrongCount++;

                $this->insert('exam_answers', [
                    'attempt_id' => $attemptId,
                    'question_id' => $q['id'],
                    'chosen_option_key' => $chosen,
                    'is_correct' => $isCorrect
                ]);
            }

            $score = count($questions) > 0 ? ($correctCount / count($questions)) * 100 : 0;
            $duration = time() - strtotime($attempt['started_at']);

            $this->update('exam_attempts', [
                'status' => 'submitted',
                'submitted_at' => date('Y-m-d H:i:s'),
                'correct_count' => $correctCount,
                'wrong_count' => $wrongCount,
                'score_percentage' => $score,
                'duration_seconds' => $duration
            ], 'id = ?', [$attemptId]);

            $this->commit();
            return ['score' => round($score, 2), 'correct' => $correctCount, 'total' => count($questions)];

        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

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