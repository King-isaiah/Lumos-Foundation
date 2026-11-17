<?php
/**
 * Cron entry point. Called by EasyCron every 5 minutes.
 * Locked down by a secret key so only EasyCron can trigger it.
 *
 * Setup in EasyCron:
 *   URL:   https://yoursite.infinityfreeapp.com/cron.php?key=YOUR_SECRET
 *   Every: 5 minutes
 */

require_once __DIR__ . '/lib/Logger.php';
require_once __DIR__ . '/lib/Database.php';
require_once __DIR__ . '/config.php';

$expectedKey = getenv('CRON_KEY') ?: 'CHANGE_ME_CRON_SECRET';
$providedKey = $_GET['key'] ?? '';

if (!hash_equals($expectedKey, $providedKey)) {
    http_response_code(403);
    exit('Forbidden');
}

header('Content-Type: application/json');

try {
    $tasks = [];

    // Placeholder — real tasks added in later slices:
    // $tasks[] = ['name' => 'close_expired_exams', 'count' => (new Exam())->closeExpired()];
    // $tasks[] = ['name' => 'settle_finished_exams', 'count' => (new Exam())->settleFinished()];
    // $tasks[] = ['name' => 'cleanup_rate_limits', 'count' => (new Database())->cleanupRateLimits()];

    // For now: just prove the hook works
    $tasks[] = ['name' => 'heartbeat', 'ran_at' => date('Y-m-d H:i:s')];

    Logger::info('Cron ran', ['tasks' => $tasks]);

    echo json_encode(['ok' => true, 'tasks' => $tasks]);
} catch (Throwable $e) {
    Logger::exception($e, ['context' => 'cron.php']);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}