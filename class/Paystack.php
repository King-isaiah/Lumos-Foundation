<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/Logger.php';

class Paystack {
    private string $secretKey;

    public function __construct() {
        $config = Config::paystack();
        $this->secretKey = $config['secret_key'];
    }

    public function initialize(string $email, int $amountKobo, string $reference, string $callbackUrl): array {
        $fields = [
            'email' => $email,
            'amount' => $amountKobo,
            'reference' => $reference,
            'callback_url' => $callbackUrl
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/transaction/initialize");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$this->secretKey}",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (!$data['status']) {
            Logger::error('Paystack Init Failed', ['response' => $data]);
            throw new Exception('Payment gateway error. Please try again.', 500);
        }

        return $data['data'];
    }

    public function verify(string $reference): ?array {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/transaction/verify/{$reference}");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer {$this->secretKey}"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if ($data['status'] && $data['data']['status'] === 'success') {
            return $data['data'];
        }
        return null;
    }
        // Add this to class/Paystack.php

    public function createTransferRecipient(string $name, string $accountNumber, string $bankCode): string {
        $fields = [
            'type' => 'nuban',
            'name' => $name,
            'account_number' => $accountNumber,
            'bank_code' => $bankCode,
            'currency' => 'NGN'
        ];

        $ch = curl_init("https://api.paystack.co/transferrecipient");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$this->secretKey}",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (!$response['status']) throw new Exception('Failed to create recipient: ' . $response['message']);
        
        return $response['data']['recipient_code'];
    }

    public function initiateTransfer(int $amountKobo, string $recipientCode, string $reason): array {
        $fields = [
            'source' => 'balance',
            'amount' => $amountKobo,
            'recipient' => $recipientCode,
            'reason' => $reason
        ];

        $ch = curl_init("https://api.paystack.co/transfer");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$this->secretKey}",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (!$response['status']) throw new Exception('Transfer failed: ' . $response['message']);
        
        return $response['data'];
    }
}