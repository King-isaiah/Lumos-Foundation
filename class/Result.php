<?php
require_once __DIR__ . '/Database.php';

class Result extends Database {
    
    /**
     * Fetches all submitted attempts for a specific exam, joined with user info.
     * Ordered by Score (Desc) and Time (Asc) to calculate ranks.
     */
    public function getExamResults(int $examId): array {
        $sql = "SELECT 
                    a.id as attempt_id,
                    a.score_percentage,
                    a.duration_seconds,
                    a.correct_count,
                    a.wrong_count,
                    a.started_at,
                    a.submitted_at,
                    u.first_name,
                    u.last_name,
                    u.unique_id,
                    u.email
                FROM exam_attempts a
                JOIN users u ON a.user_id = u.id
                WHERE a.exam_id = ? AND a.status = 'submitted'
                ORDER BY a.score_percentage DESC, a.duration_seconds ASC";
                
        $results = $this->fetchAll($sql, [$examId]);

        // Dynamically assign rank based on the sorted order
        foreach ($results as $index => &$row) {
            $row['rank'] = $index + 1;
            
            // Format time spent (e.g., "15m 30s")
            $mins = floor($row['duration_seconds'] / 60);
            $secs = $row['duration_seconds'] % 60;
            $row['time_spent_formatted'] = "{$mins}m {$secs}s";
            
            // Format start/end times
            $row['started_formatted'] = date('M j, Y g:i A', strtotime($row['started_at']));
            $row['submitted_formatted'] = date('M j, Y g:i A', strtotime($row['submitted_at']));
        }

        return $results;
    }
}