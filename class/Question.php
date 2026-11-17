<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Validator.php';

class Question extends Database {

    private function ensureExamIsDraft(int $examId): void {
        $examStatus = $this->fetchValue("SELECT status FROM exams WHERE id = ?", [$examId]);
        if ($examStatus !== 'draft') {
            throw new Exception('Questions cannot be modified because this exam is already active or settled.', 403);
        }
    }

    public function getByExam(int $examId): array {
        return $this->fetchAll(
            "SELECT id, position, question_text, question_type, media_url, options_json, correct_option_key 
             FROM exam_questions 
             WHERE exam_id = ? 
             ORDER BY position ASC",
            [$examId]
        );
    }

    public function getQuestionById(int $id): ?array {
        return $this->fetchOne("SELECT * FROM exam_questions WHERE id = ?", [$id]);
    }

    public function create(int $examId, array $input): array {
        $this->ensureExamIsDraft($examId);

        $data = (new Validator($input))
            ->required('position', 'question_text', 'options_json', 'correct_option_key')
            ->validate();

        $options = json_decode($data['options_json'], true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($options)) {
            throw new Exception('Options must be a valid JSON array.', 400);
        }

        $validKeys = array_column($options, 'key');
        if (!in_array($data['correct_option_key'], $validKeys)) {
            throw new Exception('The correct option key must match one of the options provided.', 400);
        }

        if ($this->recordExists('exam_questions', ['exam_id' => $examId, 'position' => $data['position']])) {
            throw new Exception("Question position {$data['position']} already exists for this exam. Please choose another.", 409);
        }

        $id = $this->insert('exam_questions', [
            'exam_id'            => $examId,
            'position'           => (int)$data['position'],
            'question_text'      => $data['question_text'],
            'question_type'      => $data['question_type'] ?? 'text',
            'media_url'          => $data['media_url'] ?? null,
            'options_json'       => json_encode($options),
            'correct_option_key' => $data['correct_option_key'],
        ]);

        return ['id' => $id, 'position' => (int)$data['position']];
    }

    public function updateQuestion(int $id, array $input): void {
        $examId = (int)$this->fetchValue("SELECT exam_id FROM exam_questions WHERE id = ?", [$id]);
        $this->ensureExamIsDraft($examId);

        $data = (new Validator($input))
            ->required('question_text', 'options_json', 'correct_option_key')
            ->validate();

        $options = json_decode($data['options_json'], true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($options)) {
            throw new Exception('Options must be a valid JSON array.', 400);
        }

        $validKeys = array_column($options, 'key');
        if (!in_array($data['correct_option_key'], $validKeys)) {
            throw new Exception('The correct option key must match one of the options provided.', 400);
        }

        $this->update('exam_questions', [
            'question_text'      => $data['question_text'],
            'options_json'       => json_encode($options),
            'correct_option_key' => $data['correct_option_key'],
        ], 'id = ?', [$id]);
    }

    public function deleteQuestion(int $id): int {
        $examId = (int)$this->fetchValue("SELECT exam_id FROM exam_questions WHERE id = ?", [$id]);
        $this->ensureExamIsDraft($examId);

        return $this->delete('exam_questions', 'id = ?', [$id]);
    }
}