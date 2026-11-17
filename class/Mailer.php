<?php
require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/../config.php';

class Mailer {
    public function sendVerificationEmail(string $toEmail, string $firstName, string $code): bool {
        $subject = 'Your Verification Code - Lumos Foundation';
        $htmlContent = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #0f172a;'>Hello {$firstName},</h2>
                <p>Thank you for registering with Lumos Foundation. Please use the verification code below to activate your account:</p>
                <div style='background: #f4f6f9; padding: 20px; text-align: center; border-radius: 8px; margin: 20px 0;'>
                    <h1 style='color: #39ff14; font-size: 36px; letter-spacing: 5px; margin: 0;'>{$code}</h1>
                </div>
                <p style='color: #64748b; font-size: 14px;'>This code will expire in 5 minutes. If you did not request this, please ignore this email.</p>
            </div>
        ";
        
        return $this->sendViaBrevo($toEmail, $firstName, $subject, $htmlContent);
    }
    
    public function sendPasswordReset(string $toEmail, string $firstName, string $code): bool {
        $subject = 'Your Password Reset Code';
        $htmlContent = "
            <h2>Hello {$firstName},</h2>
            <p>You requested to reset your password. Use the code below to proceed:</p>
            <h1 style='font-size: 32px; letter-spacing: 5px; color: #0a0e27;'>{$code}</h1>
            <p>This code expires in 10 minutes. If you did not request this, please ignore this email.</p>
        ";
        
        return $this->sendViaBrevo($toEmail, $firstName, $subject, $htmlContent);
    }

    private function sendViaBrevo(string $toEmail, string $toName, string $subject, string $htmlContent): bool {
        $config = Config::mail();
        $apiKey = $config['api_key'];

        $payload = [
            'sender'      => ['name' => $config['from_name'], 'email' => $config['from']],
            'to'          => [['email' => $toEmail, 'name' => $toName]],
            'subject'     => $subject,
            'htmlContent' => $htmlContent
        ];

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'api-key: ' . $apiKey,
            'content-type: application/json'
        ]);

        // --- NEW: Bypass localhost SSL issue ---
        if (Config::isLocal()) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch); // --- NEW: Capture cURL Error ---
        curl_close($ch);

        // --- NEW: Check for cURL failure first ---
        if ($curlError) {
            Logger::error('cURL Error during Brevo email send', ['to' => $toEmail, 'error' => $curlError]);
            return false;
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            Logger::info('Email sent via Brevo', ['to' => $toEmail, 'subject' => $subject]);
            return true;
        }

        Logger::error('Brevo email failed', ['to' => $toEmail, 'response' => $response]);
        return false;
    }
}