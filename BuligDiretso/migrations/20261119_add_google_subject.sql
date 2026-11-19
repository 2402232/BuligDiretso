ALTER TABLE users
    ADD COLUMN google_subject VARCHAR(255) DEFAULT NULL AFTER email_verified_at,
    ADD UNIQUE KEY uq_users_google_subject (google_subject);
