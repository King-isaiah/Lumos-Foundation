<?php
class Logger {
    private const LOG_DIR = __DIR__ . '/logs';
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;
    private const MAX_FILES = 5;

    private const CHANNELS = [
        'error'    => 'error.log',
        'warning'  => 'warning.log',
        'info'     => 'app.log',
        'security' => 'security.log',
        'api'      => 'api.log',
        'db'       => 'database.log',
    ];

    public static function error(string $m, array $c = []): void    { self::write('error', $m, $c); }
    public static function warning(string $m, array $c = []): void  { self::write('warning', $m, $c); }
    public static function info(string $m, array $c = []): void     { self::write('info', $m, $c); }
    public static function security(string $m, array $c = []): void { self::write('security', $m, $c); }
    public static function api(string $m, array $c = []): void      { self::write('api', $m, $c); }
    public static function db(string $m, array $c = []): void       { self::write('db', $m, $c); }

    public static function exception(Throwable $e, array $c = []): void {
        self::write('error', $e->getMessage(), array_merge($c, [
            'class' => get_class($e), 'code' => $e->getCode(),
            'file'  => $e->getFile(), 'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]));
    }

    public static function requestId(): string {
        static $id = null;
        return $id ??= bin2hex(random_bytes(8));
    }

    private static function write(string $channel, string $message, array $context): void {
        try {
            if (!is_dir(self::LOG_DIR)) mkdir(self::LOG_DIR, 0755, true);
            $file = self::LOG_DIR . '/' . (self::CHANNELS[$channel] ?? 'app.log');
            if (is_file($file) && filesize($file) > self::MAX_FILE_SIZE) self::rotate($file);

            $entry = [
                'time'      => date('Y-m-d H:i:s'),
                'level'     => strtoupper($channel),
                'message'   => $message,
                'request_id'=> self::requestId(),
                'ip'        => $_SERVER['REMOTE_ADDR'] ?? null,
                'entity'    => $_GET['entity'] ?? null,
                'action'    => $_GET['action'] ?? null,
                'method'    => $_SERVER['REQUEST_METHOD'] ?? null,
                'uri'       => $_SERVER['REQUEST_URI'] ?? null,
                'context'   => self::scrub($context),
            ];
            file_put_contents($file, json_encode($entry, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND|LOCK_EX);
        } catch (Throwable $ignored) {
            error_log("[Logger failed] $message");
        }
    }

    private static function rotate(string $file): void {
        for ($i = self::MAX_FILES - 1; $i >= 1; $i--) {
            $old = preg_replace('/(\.log)$/', ".$i.log", $file);
            $new = preg_replace('/(\.log)$/', '.' . ($i+1) . '.log', $file);
            if (is_file($old)) @rename($old, $new);
        }
        @rename($file, preg_replace('/(\.log)$/', '.1.log', $file));
    }

    private static function scrub(array $context): array {
        $sensitive = ['password','pass','pwd','token','authorization','secret','api_key','apikey','code','code_hash','token_hash'];
        array_walk_recursive($context, function (&$v, $k) use ($sensitive) {
            if (in_array(strtolower((string)$k), $sensitive, true)) $v = '[REDACTED]';
        });
        return $context;
    }
}