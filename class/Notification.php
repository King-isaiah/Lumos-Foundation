<?php
require_once __DIR__ . '/Database.php';

class Notification extends Database {
    
    public function create(int $userId, string $type, string $title, string $body, array $data = []): int {
        return $this->insert('notifications', [
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data_json' => $data ? json_encode($data) : null
        ]);
    }

    public function getMyNotifications(int $userId): array {
        return $this->fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10",
            [$userId]
        );
    }

    /**
     * NEW: Marks all unread notifications for a user as read.
     */
    public function markAllAsRead(int $userId): void {
        $this->update('notifications', ['is_read' => 1], 'user_id = ? AND is_read = 0', [$userId]);
    }
}