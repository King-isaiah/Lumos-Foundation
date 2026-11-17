<?php
require_once __DIR__ . '/Database.php';

class Dashboard extends Database {
    public function getAdminStats(): array {
        $totalRevenue = (int)$this->fetchValue("SELECT COALESCE(SUM(amount_kobo), 0) FROM payments WHERE status = 'success'");
        $totalPayouts = (int)$this->fetchValue("SELECT COALESCE(SUM(amount_kobo), 0) FROM payouts WHERE status = 'paid'");
        
        // NEW STATS:
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
            
            // Return new stats
            'total_exams' => $totalExams,
            'active_exams' => $activeExams,
            'total_attempts' => $totalAttempts,
            'open_complaints' => $openComplaints,
            'pending_payouts' => $pendingPayouts,
        ];
    }
}