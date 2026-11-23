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
    
    public function markAllAsRead(int $userId): void {
        $this->executeQuery("UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL", [$userId]);
    }
}