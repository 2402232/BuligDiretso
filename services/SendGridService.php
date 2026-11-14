<?php

class SendGridService
{
    public static function send(string $recipient, string $subject, string $message): array
    {
        if (SENDGRID_API_KEY === '' || SENDGRID_FROM_EMAIL === '') {
            error_log('[SendGridService] Missing SENDGRID_API_KEY or SENDGRID_FROM_EMAIL configuration.');
            return ['success' => false, 'message' => 'Email service is not configured.'];
        }

        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            error_log('[SendGridService] Invalid recipient email address.');
            return ['success' => false, 'message' => 'Invalid recipient email address.'];
        }

        if (!function_exists('curl_init')) {
            error_log('[SendGridService] PHP cURL extension is unavailable.');
            return ['success' => false, 'message' => 'Email transport is unavailable.'];
        }

        $payload = [
            'personalizations' => [[
                'to' => [['email' => $recipient]],
                'subject' => $subject,
            ]],
            'from' => [
                'email' => SENDGRID_FROM_EMAIL,
                'name' => SENDGRID_FROM_NAME,
            ],
            'content' => [[
                'type' => 'text/plain',
                'value' => $message,
            ]],
        ];

        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . SENDGRID_API_KEY,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlError !== '') {
            error_log('[SendGridService] Request failed: ' . $curlError);
            return ['success' => false, 'message' => 'Email request failed.'];
        }

        if ($httpCode === 202) {
            return ['success' => true, 'message' => 'Email accepted by SendGrid.'];
        }

        error_log('[SendGridService] API returned HTTP ' . $httpCode . '.');
        return ['success' => false, 'message' => 'SendGrid rejected the email (HTTP ' . $httpCode . ').'];
    }
}
