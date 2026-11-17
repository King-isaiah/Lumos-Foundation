<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/Logger.php';

class ImageUploader {
    
    /**
     * Uploads a base64 image to ImgBB and returns the direct URL.
     * Throws an exception if the upload fails.
     */
    public static function uploadToImgBB(string $base64String): string {
        // 1. Get the API key securely from your secrets.php via Config
        $apiKey = Config::images()['imgbb_api_key'];
        
        if (empty($apiKey) || $apiKey === 'YOUR_IMGBB_KEY') {
            throw new Exception('ImgBB API key is missing or invalid.');
        }

        // 2. Strip the data URI prefix (e.g., "data:image/jpeg;base64,")
        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $base64String);

        // 3. Prepare the POST request to ImgBB
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.imgbb.com/1/upload?key=' . $apiKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'image' => $imageData
        ]);
        
        // WAMP Localhost often lacks proper SSL certificates. 
        // This disables SSL verification for local development.
        if (Config::isLocal()) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        // 4. Execute the request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch); // CAPTURE CURL ERROR
        curl_close($ch);

        // 5. If cURL failed entirely (e.g., no internet, SSL failure)
        if ($curlError) {
            Logger::error('cURL Error during ImgBB upload', ['error' => $curlError]);
            throw new Exception('cURL Error: ' . $curlError);
        }

        $responseData = json_decode($response, true);

        // 6. Check if it was successful
        if ($httpCode === 200 && isset($responseData['data']['url'])) {
            return $responseData['data']['url']; // Success! Return the URL
        }

        // 7. If it failed, log the exact API response and throw an exception
        $errorMessage = $responseData['error']['message'] ?? 'HTTP Code: ' . $httpCode . ' | Raw Response: ' . $response;
        Logger::error('ImgBB Upload Failed', ['response' => $responseData, 'http_code' => $httpCode]);
        throw new Exception('Image upload failed: ' . $errorMessage);
    }
}