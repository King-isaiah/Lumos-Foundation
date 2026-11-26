<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Validator.php';

class Complaint extends Database {
    
    public function create(int $userId, array $input): array {
        // Added max length to subject to prevent DB overflow
        $data = (new Validator($input))
            ->required('subject', 'message')
            ->max('subject', 200)
            ->validate();

        $id = $this->insert('complaints', [
            'user_id' => $userId,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'status' => 'open'
        ]);

        return ['id' => $id];
    }

    public function getMyComplaints(int $userId): array {
        return $this->fetchAll(
            "SELECT id, subject, message, status, admin_response, created_at 
             FROM complaints 
             WHERE user_id = ? 
             ORDER BY created_at DESC",
            [$userId]
        );
    }

    // --- Admin Methods (Untouched) ---

    public function getAll(): array {
        return $this->fetchAll(
            "SELECT c.*, u.first_name, u.last_name, u.email 
             FROM complaints c
             JOIN users u ON c.user_id = u.id
             ORDER BY c.created_at DESC"
        );
    }

    public function resolve(int $adminId, array $input): array {
        $complaintId = (int)($input['complaint_id'] ?? 0);
        $response = $input['response'] ?? '';
        
        if (!$complaintId || !$response) {
            throw new Exception('Missing complaint details.', 400);
        }

        $this->update('complaints', [
            'status' => 'resolved',
            'admin_response' => $response,
            'resolved_by' => $adminId,
            'resolved_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$complaintId]);

        return ['message' => 'Complaint resolved.'];
    }
}