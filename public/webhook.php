<?php
require_once __DIR__ . '/../class/Database.php';
require_once __DIR__ . '/../class/Logger.php';
require_once __DIR__ . '/../config.php';

// Paystack sends JSON data via raw POST
$input = file_get_contents('php://input');
$event = json_decode($input, true);

// Verify Signature (Security)
$secret = Config::paystack()['secret_key'];
$signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';

if (hash_hmac('sha512', $input, $secret) !== $signature) {
    Logger::security('Invalid Paystack Webhook Signature', ['ip' => $_SERVER['REMOTE_ADDR']]);
    http_response_code(401);
    exit('Invalid signature');
}

if ($event && $event['event'] === 'charge.success') {
    $reference = $event['data']['reference'];
    
    $db = new Database();
    
    // Check if pending
    $payment = $db->fetchOne("SELECT id, status, amount_kobo FROM payments WHERE reference = ?", [$reference]);
    
    if ($payment && $payment['status'] === 'pending') {
        // Mark as success!
        $db->update('payments', [
            'status' => 'success',
            'gateway_ref' => $event['data']['id'],
            'paid_at' => date('Y-m-d H:i:s'),
            'gateway_response_json' => json_encode($event['data'])
        ], 'id = ?', [$payment['id']]);

        Logger::info('Payment confirmed via webhook', ['reference' => $reference, 'amount' => $payment['amount_kobo']]);
    }
}

http_response_code(200);
echo "Webhook received";