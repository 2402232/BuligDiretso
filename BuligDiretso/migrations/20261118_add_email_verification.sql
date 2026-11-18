ALTER TABLE users
    ADD COLUMN email_verified_at DATETIME DEFAULT NULL AFTER is_active;

UPDATE users
SET email_verified_at = COALESCE(created_at, CURRENT_TIMESTAMP)
WHERE email_verified_at IS NULL;

CREATE TABLE email_verification_tokens (
    user_id         INT UNSIGNED    NOT NULL,
    token_hash      CHAR(64)        NOT NULL,
    expires_at      DATETIME        NOT NULL,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (user_id),
    UNIQUE KEY uq_email_verification_token_hash (token_hash),
    KEY idx_email_verification_expires_at (expires_at),
    CONSTRAINT fk_email_verification_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
