<?php
/**
 * Central config. GITIGNORE THIS FILE ON PRODUCTION.
 * Never commit secrets.
 */
final class Config {
    // Helper to load the secrets array once
    private static function getSecrets(): array {
        static $secrets = null;
        if ($secrets === null) {
            $path = __DIR__ . '/secrets.php';
            $secrets = file_exists($path) ? require $path : [];
        }
        return $secrets;
    }

    public static function isLocal(): bool {
        return in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1', '::1'], true);
    }

    public static function database(): array {
        $secrets = self::getSecrets();
        return self::isLocal()
            ? [
                'host' => 'localhost',
                'name' => 'lumos_cbt',
                'user' => 'root',
                'pass' => '',
            ]
            : [
                'host' => 'sql308.infinityfree.com',
                'name' => 'if0_43462708_lumos_cbt',
                'user' => 'if0_43462708',
                'pass' => $secrets['db_pass'] ?? 'TuVpWwSq9mco2',
            ];
    }

    public static function isDebug(): bool {
        return self::isLocal() || ($_GET['debug'] ?? '') === 'true';
    }

    public static function corsOrigin(): string {
        return '*'; // You can restrict this to your domain later
    }

    public static function appUrl(): string {
        return self::isLocal() ? 'http://localhost/Lumos-Foundation' : 'https://lumos-foundation.infy.click';
    }

    public static function mail(): array {
        $secrets = self::getSecrets();
        return [
            'provider' => 'brevo',
            'api_key'  => $secrets['brevo_key'],
            'from'     => 'eshiozemheafuwape@gmail.com',
            'from_name'=> 'kingisaiah Dev',
            // 'from_name'=> 'Lumos Foundation',
        ];
    }

    public static function paystack(): array {
        $secrets = self::getSecrets();
        return [
            'public_key' => $secrets['paystack_public'] ?? 'pk_test_xxx',
            'secret_key' => $secrets['paystack_secret'] ?? 'sk_test_xxx',
            'webhook_secret' => $secrets['paystack_webhook'] ?? 'xxx',
        ];
    }

    public static function images(): array {
        $secrets = self::getSecrets();
        return [
            'imgbb_api_key' => $secrets['imgbb_key'] ?? 'YOUR_IMGBB_KEY',
        ];
    }
}