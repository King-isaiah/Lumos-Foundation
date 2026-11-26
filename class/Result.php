<?php
require_once __DIR__ . '/Database.php';

class Result extends Database {
    
    /**
     * For Admin: Fetches all submitted attempts for a specific exam.
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

        foreach ($results as $index => &$row) {
            $row['rank'] = $index + 1;
            $mins = floor($row['duration_seconds'] / 60);
            $secs = $row['duration_seconds'] % 60;
            $row['time_spent_formatted'] = "{$mins}m {$secs}s";
            $row['started_formatted'] = date('M j, Y g:i A', strtotime($row['started_at']));
            $row['submitted_formatted'] = date('M j, Y g:i A', strtotime($row['submitted_at']));
        }

        return $results;
    }

    /**
     * For Student: Fetches all exams the student paid for + their results + stats
     */
    public function getMyReports(int $userId): array {
        $sql = "SELECT 
                    e.id as exam_id, 
                    e.title, 
                    e.subject, 
                    e.duration_minutes, 
                    e.question_count,
                    e.status as exam_status,
                    p.paid_at,
                    a.id as attempt_id,
                    a.score_percentage,
                    a.duration_seconds as time_spent,
                    a.correct_count,
                    a.wrong_count,
                    a.submitted_at,
                    a.status as attempt_status
                FROM exams e
                JOIN payments p ON e.id = p.exam_id AND p.user_id = ? AND p.status = 'success'
                LEFT JOIN exam_attempts a ON e.id = a.exam_id AND a.user_id = ? AND a.status = 'submitted'
                ORDER BY p.paid_at DESC";
                
        $reports = $this->fetchAll($sql, [$userId, $userId]);

        foreach ($reports as &$r) {
            if ($r['attempt_id']) {
                $r['total_students'] = (int)$this->fetchValue(
                    "SELECT COUNT(*) FROM exam_attempts WHERE exam_id = ? AND status = 'submitted'",
                    [$r['exam_id']]
                );

                $rankQuery = "SELECT COUNT(*) + 1 as `rank` 
                              FROM exam_attempts 
                              WHERE exam_id = ? AND status = 'submitted' 
                              AND (score_percentage > ? OR (score_percentage = ? AND duration_seconds < ?))";
                $r['rank'] = (int)$this->fetchValue($rankQuery, [
                    $r['exam_id'], 
                    $r['score_percentage'], 
                    $r['score_percentage'], 
                    $r['time_spent']
                ]);

                $r['class_average'] = round((float)$this->fetchValue(
                    "SELECT AVG(score_percentage) FROM exam_attempts WHERE exam_id = ? AND status = 'submitted'",
                    [$r['exam_id']]
                ), 1);
                
                $mins = floor($r['time_spent'] / 60);
                $secs = $r['time_spent'] % 60;
                $r['time_spent_formatted'] = "{$mins}m {$secs}s";
            }
        }

        return $reports;
    }

    /**
     * NEW: Get a list of all exams that have at least 1 submitted attempt.
     */
    public function getLeaderboardExams(): array {
        $sql = "SELECT 
                    e.id, 
                    e.title, 
                    e.subject, 
                    COUNT(a.id) as total_participants,
                    MAX(a.score_percentage) as top_score
                FROM exams e
                JOIN exam_attempts a ON e.id = a.exam_id AND a.status = 'submitted'
                GROUP BY e.id
                ORDER BY e.created_at DESC";
                
        return $this->fetchAll($sql);
    }

    /**
     * NEW: Get the leaderboard for a specific exam.
     */
    public function getExamLeaderboard(int $examId): array {
        $sql = "SELECT 
                    a.score_percentage as score,
                    a.duration_seconds,
                    u.first_name,
                    u.last_name,
                    u.avatar_url
                FROM exam_attempts a
                JOIN users u ON a.user_id = u.id
                WHERE a.exam_id = ? AND a.status = 'submitted'
                ORDER BY a.score_percentage DESC, a.duration_seconds ASC";
                
        $results = $this->fetchAll($sql, [$examId]);

        foreach ($results as $index => &$row) {
            // Format name for privacy (e.g., "John D.")
            $row['display_name'] = $row['first_name'] . ' ' . substr($row['last_name'], 0, 1) . '.';
            
            $mins = floor($row['duration_seconds'] / 60);
            $secs = $row['duration_seconds'] % 60;
            $row['time_taken'] = "{$mins}m {$secs}s";
            
            $row['rank'] = $index + 1;
        }

        return $results;
    }
}