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
}