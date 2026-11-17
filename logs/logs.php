<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Logger.php';

// Only allow admins / localhost
session_start();
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
$isLocal = Config::isLocal();

if (!$isAdmin && !$isLocal) {
    http_response_code(403);
    die('Forbidden');
}

// Discover log files
$logDir = __DIR__ . '/../logs';
$logFiles = [];
if (is_dir($logDir)) {
    $logFiles = glob($logDir . '/*.log');
}

$selected = $_GET['file'] ?? ($logFiles[0] ?? null);
$level    = $_GET['level'] ?? '';
$q        = trim($_GET['q'] ?? '');
$limit    = min((int) ($_GET['limit'] ?? 200), 2000);

$entries = [];

if ($selected && is_file($selected) && str_starts_with(realpath($selected), realpath($logDir))) {
    $lines = file($selected, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lines = array_reverse($lines); // newest first

    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if (!$entry) continue;

        if ($level && strtolower($entry['level'] ?? '') !== strtolower($level)) continue;
        if ($q && stripos($line, $q) === false) continue;

        $entries[] = $entry;
        if (count($entries) >= $limit) break;
    }
}

function e($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>API Logs</title>
<style>
    body { font-family: system-ui, sans-serif; margin: 0; background: #0f172a; color: #e2e8f0; }
    header { background: #1e293b; padding: 16px 24px; border-bottom: 1px solid #334155; }
    header h1 { margin: 0 0 8px; font-size: 20px; }
    .controls { display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }
    .controls select, .controls input, .controls button {
        background: #0f172a; color: #e2e8f0; border: 1px solid #334155;
        padding: 6px 10px; border-radius: 6px; font-size: 13px;
    }
    .controls button { background: #3b82f6; border-color: #3b82f6; cursor: pointer; }
    .summary { padding: 12px 24px; color: #94a3b8; font-size: 13px; }
    .entry {
        border-bottom: 1px solid #1e293b;
        padding: 10px 24px;
        font-size: 13px;
        display: grid;
        grid-template-columns: 160px 90px 1fr;
        gap: 12px;
        align-items: start;
    }
    .entry:hover { background: #1e293b; }
    .time { color: #94a3b8; font-family: monospace; }
    .level { font-weight: 700; }
    .level.ERROR    { color: #f87171; }
    .level.WARNING  { color: #fbbf24; }
    .level.SECURITY { color: #f472b6; }
    .level.INFO     { color: #34d399; }
    .level.API      { color: #60a5fa; }
    .level.DB       { color: #c084fc; }
    .msg { font-weight: 500; }
    .ctx {
        margin-top: 6px; padding: 8px; background: #0b1220; border-radius: 6px;
        color: #cbd5e1; font-family: monospace; font-size: 12px;
        white-space: pre-wrap; word-break: break-all;
        max-height: 300px; overflow: auto;
    }
    .meta { color: #64748b; font-size: 11px; margin-top: 4px; }
    .empty { padding: 40px; text-align: center; color: #64748b; }
</style>
</head>
<body>
<header>
    <h1>🔍 API Log Viewer</h1>
    <form method="get" class="controls">
        <select name="file" onchange="this.form.submit()">
            <?php foreach ($logFiles as $f): ?>
                <option value="<?= e($f) ?>" <?= $selected === $f ? 'selected' : '' ?>>
                    <?= e(basename($f)) ?> (<?= e(number_format(filesize($f) / 1024, 1)) ?> KB)
                </option>
            <?php endforeach; ?>
        </select>
        <select name="level">
            <option value="">All levels</option>
            <?php foreach (['ERROR','WARNING','SECURITY','INFO','API','DB'] as $l): ?>
                <option value="<?= e($l) ?>" <?= $level === $l ? 'selected' : '' ?>><?= e($l) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="q" placeholder="Search..." value="<?= e($q) ?>">
        <input type="number" name="limit" value="<?= e($limit) ?>" style="width:80px" title="Max entries">
        <button type="submit">Apply</button>
    </form>
</header>

<div class="summary">
    Showing <strong><?= count($entries) ?></strong> entries
    <?= $level ? "at level <strong>$level</strong>" : '' ?>
    <?= $q ? "matching \"<strong>" . e($q) . "</strong>\"" : '' ?>
</div>

<?php if (empty($entries)): ?>
    <div class="empty">No log entries match your filters.</div>
<?php else: ?>
    <?php foreach ($entries as $entry): ?>
        <div class="entry">
            <div class="time"><?= e($entry['time'] ?? '') ?></div>
            <div class="level <?= e($entry['level'] ?? '') ?>"><?= e($entry['level'] ?? '') ?></div>
            <div>
                <div class="msg"><?= e($entry['message'] ?? '') ?></div>
                <div class="meta">
                    <?= e($entry['method'] ?? '') ?> <?= e($entry['uri'] ?? '') ?>
                    <?= $entry['entity'] ? '· ' . e($entry['entity']) : '' ?>
                    <?= $entry['action'] ? '· ' . e($entry['action']) : '' ?>
                    <?= $entry['ip'] ? '· ' . e($entry['ip']) : '' ?>
                    · req <?= e($entry['request_id'] ?? '') ?>
                </div>
                <?php if (!empty($entry['context'])): ?>
                    <details>
                        <summary style="cursor:pointer;color:#60a5fa;font-size:12px;">context</summary>
                        <div class="ctx"><?= e(json_encode($entry['context'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></div>
                    </details>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>