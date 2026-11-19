<?php

class GmailService {
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const SEND_URL = 'https://gmail.googleapis.com/gmail/v1/users/me/messages/send';

    public static function sendSignupVerification(string $to, string $firstName, string $token): void {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('A valid recipient email address is required.');
        }
        $clientId = getenv('GMAIL_CLIENT_ID');
        $clientSecret = getenv('GMAIL_CLIENT_SECRET');
        $refreshToken = getenv('GMAIL_REFRESH_TOKEN');
        $senderEmail = getenv('GMAIL_SENDER_EMAIL');
        if (!$clientId || !$clientSecret || !$refreshToken || !$senderEmail) {
            throw new RuntimeException('Gmail API environment configuration is incomplete.');
        }
        if (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('GMAIL_SENDER_EMAIL is not a valid email address.');
        }

        $tokenResponse = self::post(self::TOKEN_URL, 'application/x-www-form-urlencoded', http_build_query([
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ]));
        $accessToken = $tokenResponse['access_token'] ?? '';
        if (!is_string($accessToken) || $accessToken === '') {
            throw new RuntimeException('Google did not return an access token.');
        }

        $safeName = trim(preg_replace('/[\r\n\x00-\x1F\x7F]/', '', $firstName)) ?: 'there';
        $verificationUrl = BASE_URL . 'index.php?action=verify_email&token=' . rawurlencode($token);
        $subject = 'Verify your BuligDiretso account';
        $body = "Hi {$safeName},\r\n\r\n"
            . "Please verify your email address to finish creating your BuligDiretso account:\r\n"
            . $verificationUrl . "\r\n\r\n"
            . "This link expires in 24 hours. If you did not create this account, you can ignore this email.\r\n";
        $message = "To: {$to}\r\nFrom: {$senderEmail}\r\n"
            . 'Subject: =?UTF-8?B?' . base64_encode($subject) . "?=\r\n"
            . "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n\r\n" . $body;
        $rawMessage = rtrim(strtr(base64_encode($message), '+/', '-_'), '=');
        $payload = json_encode(['raw' => $rawMessage], JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            throw new RuntimeException('Unable to encode the Gmail API message.');
        }
        self::post(self::SEND_URL, 'application/json', $payload, ['Authorization: Bearer ' . $accessToken]);
    }

    private static function post(string $url, string $contentType, string $body, array $headers = []): array {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('The PHP cURL extension is required for Gmail API requests.');
        }
        $handle = curl_init($url);
        if ($handle === false) {
            throw new RuntimeException('Unable to initialize the Gmail API HTTP request.');
        }
        curl_setopt_array($handle, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => array_merge(['Content-Type: ' . $contentType, 'Accept: application/json'], $headers),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
        ]);
        $responseBody = curl_exec($handle);
        $curlError = curl_error($handle);
        $statusCode = (int)curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);
        if ($responseBody === false) {
            throw new RuntimeException('Gmail API request failed: ' . $curlError);
        }
        $response = json_decode($responseBody, true);
        if ($statusCode < 200 || $statusCode >= 300 || !is_array($response)) {
            throw new RuntimeException('Gmail API request returned HTTP ' . $statusCode . '.');
        }
        return $response;
    }
}
