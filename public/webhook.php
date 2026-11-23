<?php
// Note the ../ paths because this file is in the /public folder
require_once __DIR__ . '/../class/Database.php';
require_once __DIR__ . '/../class/Logger.php';
require_once __DIR__ . '/../class/Notification.php'; 
require_once __DIR__ . '/../class/Mailer.php';       
require_once __DIR__ . '/../class/User.php';         
require_once __DIR__ . '/../class/Exam.php';         
require_once __DIR__ . '/../class/Payment.php'; // <-- MUST ADD THIS
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
    
    // Call the new method inside the Payment class
    (new Payment())->processWebhook($reference, $event['data']);
}

http_response_code(200);
echo "Webhook received";