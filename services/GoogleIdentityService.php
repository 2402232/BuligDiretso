<?php

class GoogleIdentityService {
    private const TOKEN_INFO_URL = 'https://oauth2.googleapis.com/tokeninfo';

    public static function verifyIdToken(string $idToken): array {
        $clientId = defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : getenv('GOOGLE_CLIENT_ID');
        if (!$clientId) {
            throw new RuntimeException('GOOGLE_CLIENT_ID is not configured.');
        }
        if (!function_exists('curl_init')) {
            throw new RuntimeException('The PHP cURL extension is required for Google Sign-In.');
        }

        $handle = curl_init(self::TOKEN_INFO_URL . '?id_token=' . rawurlencode($idToken));
        if ($handle === false) {
            throw new RuntimeException('Unable to initialize Google token verification.');
        }
        curl_setopt_array($handle, [
            CURLOPT_HTTPGET => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CAINFO => 'C:\xampp\apache\bin\curl-ca-bundle.crt',
        ]);
        $body = curl_exec($handle);
        $curlError = curl_error($handle);
        $status = (int)curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        if ($body === false) {
            throw new RuntimeException('Google token verification failed: ' . $curlError);
        }
        $payload = json_decode($body, true);
        if ($status < 200 || $status >= 300 || !is_array($payload)) {
            throw new RuntimeException('Google rejected the sign-in token.');
        }
        if (($payload['aud'] ?? '') !== $clientId) {
            throw new RuntimeException('Google sign-in is configured for a different client.');
        }
        if (!in_array($payload['iss'] ?? '', ['accounts.google.com', 'https://accounts.google.com'], true)) {
            throw new RuntimeException('Google sign-in returned an invalid issuer.');
        }
        if ((int)($payload['exp'] ?? 0) <= time()) {
            throw new RuntimeException('Google sign-in has expired.');
        }
        if (($payload['email_verified'] ?? '') !== 'true' && ($payload['email_verified'] ?? false) !== true) {
            throw new RuntimeException('Google account email is not verified.');
        }

        $email = strtolower(trim((string)($payload['email'] ?? '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Google did not return a valid email address.');
        }
        return [
            'subject' => (string)($payload['sub'] ?? ''),
            'email' => $email,
            'first_name' => (string)($payload['given_name'] ?? ''),
            'last_name' => (string)($payload['family_name'] ?? ''),
        ];
    }
}
