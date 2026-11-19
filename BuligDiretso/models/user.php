<?php
/**
 * User model — uses the shared db() connection from config.
 * Load config before this: require_once CONFIG_PATH . 'config.php';
 */

if (!function_exists('db')) {
    require_once dirname(__DIR__) . '/config/config.php';
}

class User {

    public static function findByGoogleSubject(string $subject): ?array {
        $pdo = db();
        $stmt = $pdo->prepare(
            "SELECT * FROM users
             WHERE google_subject = ? AND is_active = 1
             LIMIT 1"
        );
        $stmt->execute([$subject]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function linkGoogleSubject(int $userId, string $subject): void {
        $pdo = db();
        $stmt = $pdo->prepare(
            "UPDATE users
             SET google_subject = ?, email_verified_at = COALESCE(email_verified_at, CURRENT_TIMESTAMP)
             WHERE id = ?"
        );
        $stmt->execute([$subject, $userId]);
    }

    /**
     * Find a user by email (case-insensitive).
     * @return array|null User row or null
     */
    public static function findByEmail(string $email): ?array {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1");
        $stmt->execute([trim($email)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Find an active, unverified account and its current verification token timestamp.
     * @return array|null
     */
    public static function findUnverifiedByEmail(string $email): ?array {
        $pdo = db();
        $stmt = $pdo->prepare(
            "SELECT u.id, u.first_name, t.created_at AS token_created_at
             FROM users u
             LEFT JOIN email_verification_tokens t ON t.user_id = u.id
             WHERE LOWER(u.email) = LOWER(?) AND u.is_active = 1
               AND u.email_verified_at IS NULL
             LIMIT 1"
        );
        $stmt->execute([trim($email)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function saveEmailVerificationToken(int $userId, string $tokenHash): void {
        $pdo = db();
        $stmt = $pdo->prepare(
            "INSERT INTO email_verification_tokens (user_id, token_hash, expires_at)
             VALUES (?, ?, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 1 DAY))
             ON DUPLICATE KEY UPDATE token_hash = VALUES(token_hash),
                                     expires_at = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 1 DAY),
                                     created_at = CURRENT_TIMESTAMP"
        );
        $stmt->execute([$userId, $tokenHash]);
    }

    public static function verifyEmailToken(string $tokenHash): bool {
        $pdo = db();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                "SELECT user_id
                 FROM email_verification_tokens
                 WHERE token_hash = ? AND expires_at > CURRENT_TIMESTAMP
                 LIMIT 1
                 FOR UPDATE"
            );
            $stmt->execute([$tokenHash]);
            $userId = $stmt->fetchColumn();

            if ($userId === false) {
                $pdo->rollBack();
                return false;
            }

            $stmt = $pdo->prepare(
                "UPDATE users
                 SET email_verified_at = COALESCE(email_verified_at, CURRENT_TIMESTAMP)
                 WHERE id = ?"
            );
            $stmt->execute([(int)$userId]);

            $stmt = $pdo->prepare("DELETE FROM email_verification_tokens WHERE user_id = ?");
            $stmt->execute([(int)$userId]);
            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Find a user by ID.
     * @return array|null User row or null
     */
    public static function findById(int $id): ?array {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Create a new user. Pass associative array with keys matching table columns.
     * password_hash should already be hashed (e.g. via hash_password() from config).
     * @return int|false New user id or false on failure
     */
    public static function create(array $data) {
        $pdo = db();
        $stmt = $pdo->prepare(
            "INSERT INTO users (first_name, last_name, email, phone, date_of_birth, address, role, password_hash) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $ok = $stmt->execute([
            $data['first_name'] ?? '',
            $data['last_name'] ?? '',
            $data['email'] ?? '',
            $data['phone'] ?? '',
            $data['date_of_birth'] ?: null,
            $data['address'] ?? '',
            $data['role'] ?? 'pwd',
            $data['password_hash'] ?? '',
        ]);
        return $ok ? (int) $pdo->lastInsertId() : false;
    }

    /**
     * Verify login: returns user row if email exists and password matches.
     * @return array|null User row or null
     */
    public static function verifyLogin(string $email, string $password): ?array {
        try {
            $user = self::findByEmail($email);
            if (!$user || empty($user['is_active']) || empty($user['email_verified_at'])) {
                return null;
            }

            $storedHash = (string)($user['password_hash'] ?? '');
            if ($storedHash === '') {
                return null;
            }

            $isValid = false;
            if (function_exists('verify_password') && password_get_info($storedHash)['algo'] !== null) {
                $isValid = verify_password($password, $storedHash);
            } else {
                // Backward compatibility for legacy plaintext/md5 passwords.
                $isValid = hash_equals($storedHash, $password) || hash_equals($storedHash, md5($password));
            }

            if (!$isValid) {
                return null;
            }

            // Auto-upgrade legacy password storage to strong hash.
            if (password_get_info($storedHash)['algo'] === null && function_exists('hash_password')) {
                $newHash = hash_password($password);
                $pdo = db();
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $stmt->execute([$newHash, (int)$user['id']]);
                $user['password_hash'] = $newHash;
            }

            return $user;
        } catch (Throwable $e) {
            error_log("Login verification error: " . $e->getMessage());
            return null;
        }
    }
}
