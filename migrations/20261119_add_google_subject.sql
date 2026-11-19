ALTER TABLE users
    ADD COLUMN email_verified_at DATETIME DEFAULT NULL AFTER is_active,
    ADD COLUMN google_subject VARCHAR(255) DEFAULT NULL AFTER email_verified_at,
    ADD UNIQUE KEY uq_users_google_subject (google_subject);

UPDATE users
SET email_verified_at = COALESCE(created_at, CURRENT_TIMESTAMP)
WHERE email_verified_at IS NULL;
