<?php
require_once __DIR__ . '/Database.php';

class Dashboard extends Database {
    
    public function getAdminStats(): array {
        $totalRevenue = (int)$this->fetchValue("SELECT COALESCE(SUM(amount_kobo), 0) FROM payments WHERE status = 'success'");
        $totalPayouts = (int)$this->fetchValue("SELECT COALESCE(SUM(amount_kobo), 0) FROM payouts WHERE status = 'paid'");
        
        $totalExams = (int)$this->fetchValue("SELECT COUNT(*) FROM exams");
        $activeExams = (int)$this->fetchValue("SELECT COUNT(*) FROM exams WHERE status = 'open'");
        $totalAttempts = (int)$this->fetchValue("SELECT COUNT(*) FROM exam_attempts WHERE status = 'submitted'");
        $openComplaints = (int)$this->fetchValue("SELECT COUNT(*) FROM complaints WHERE status = 'open'");
        $pendingPayouts = (int)$this->fetchValue("SELECT COUNT(*) FROM payouts WHERE status = 'pending'");
        
        return [
            'total_users' => (int)$this->fetchValue("SELECT COUNT(*) FROM users WHERE role = 'user'"),
            'revenue_kobo' => $totalRevenue,
            'payouts_kobo' => $totalPayouts,
            'profit_kobo' => $totalRevenue - $totalPayouts,
            'total_exams' => $totalExams,
            'active_exams' => $activeExams,
            'total_attempts' => $totalAttempts,
            'open_complaints' => $openComplaints,
            'pending_payouts' => $pendingPayouts,
        ];
    }

    public function getStudentStats(int $userId): array {
        $attempts = $this->fetchAll("
            SELECT score_percentage 
            FROM exam_attempts 
            WHERE user_id = ? AND status = 'submitted'
        ", [$userId]);

        $examsTaken = count($attempts);
        $totalScore = 0;
        $highestScore = 0;

        foreach ($attempts as $a) {
            $score = (float)$a['score_percentage'];
            $totalScore += $score;
            if ($score > $highestScore) $highestScore = $score;
        }

        $avgScore = $examsTaken > 0 ? round($totalScore / $examsTaken, 1) : 0;

        // FIXED: Looking for 'in_progress'
        $ongoingAttempt = $this->fetchOne("
            SELECT a.id as attempt_id, e.title, e.slug
            FROM exam_attempts a
            JOIN exams e ON a.exam_id = e.id
            WHERE a.user_id = ? AND a.status = 'in_progress'
            LIMIT 1
        ", [$userId]);

        $openExams = $this->fetchAll("
            SELECT e.id, e.title, e.subject, e.price_kobo, e.duration_minutes, e.slug
            FROM exams e
            WHERE e.status = 'open' 
            AND e.id NOT IN (
                SELECT exam_id FROM exam_attempts WHERE user_id = ? AND status = 'submitted'
            )
            ORDER BY e.created_at DESC
            LIMIT 3
        ", [$userId]);

        $logs = $this->fetchAll("
            SELECT DISTINCT DATE(created_at) as log_date 
            FROM activity_logs 
            WHERE user_id = ? 
            ORDER BY log_date DESC 
            LIMIT 10
        ", [$userId]);
        
        $streak = 0;
        if (!empty($logs)) {
            $today = date('Y-m-d');
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $dates = array_column($logs, 'log_date');
            
            if ($dates[0] == $today || $dates[0] == $yesterday) {
                $streak = 1;
                $expectedDate = date('Y-m-d', strtotime($dates[0] . ' -1 day'));
                for ($i = 1; $i < count($dates); $i++) {
                    if ($dates[$i] == $expectedDate) {
                        $streak++;
                        $expectedDate = date('Y-m-d', strtotime($dates[$i] . ' -1 day'));
                    } else {
                        break;
                    }
                }
            }
        }

        // FIXED: Backticks around `rank`
        $awards = $this->fetchAll("
            SELECT l.rank_position as `rank`, e.title as exam, l.updated_at as date
            FROM exam_leaderboard l
            JOIN exams e ON l.exam_id = e.id
            WHERE l.user_id = ? AND l.rank_position <= 3
            ORDER BY l.updated_at DESC
            LIMIT 4
        ", [$userId]);

        $upcomingPaid = $this->fetchOne("
            SELECT e.title 
            FROM payments p 
            JOIN exams e ON p.exam_id = e.id 
            WHERE p.user_id = ? AND p.status = 'success' 
            AND e.status = 'open' 
            AND e.id NOT IN (SELECT exam_id FROM exam_attempts WHERE user_id = ? AND status = 'submitted')
            LIMIT 1
        ", [$userId, $userId]);

        return [
            'exams_taken' => $examsTaken,
            'avg_score' => $avgScore,
            'highest_score' => $highestScore,
            'ongoing_attempt' => $ongoingAttempt,
            'open_exams' => $openExams,
            'streak' => $streak,
            'awards' => $awards,
            'upcoming_paid_exam' => $upcomingPaid ? $upcomingPaid['title'] : null
        ];
    }
}