-- Run this once in phpMyAdmin with the BuligDiretso database selected.
-- It updates an existing report table or creates it when it is missing.

CREATE TABLE IF NOT EXISTS emergency_reports (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    report_code VARCHAR(20) NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    emergency_type VARCHAR(50) NOT NULL,
    severity ENUM('minor', 'low', 'moderate', 'high', 'critical') NOT NULL DEFAULT 'moderate',
    status ENUM(
        'pending_verification', 'fake', 'pending', 'dispatched', 'responding',
        'en_route', 'on_scene', 'resolved', 'cancelled'
    ) NOT NULL DEFAULT 'pending_verification',
    description TEXT DEFAULT NULL,
    location VARCHAR(255) NOT NULL DEFAULT 'Unknown',
    latitude DECIMAL(10,7) DEFAULT NULL,
    longitude DECIMAL(10,7) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_emergency_reports_code (report_code),
    KEY idx_emergency_reports_user (user_id),
    KEY idx_emergency_reports_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @has_latitude = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'emergency_reports'
      AND COLUMN_NAME = 'latitude'
);
SET @migration_sql = IF(
    @has_latitude = 0,
    'ALTER TABLE emergency_reports ADD COLUMN latitude DECIMAL(10,7) DEFAULT NULL AFTER location',
    'SELECT 1'
);
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @has_longitude = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'emergency_reports'
      AND COLUMN_NAME = 'longitude'
);
SET @migration_sql = IF(
    @has_longitude = 0,
    'ALTER TABLE emergency_reports ADD COLUMN longitude DECIMAL(10,7) DEFAULT NULL AFTER latitude',
    'SELECT 1'
);
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

ALTER TABLE emergency_reports
    MODIFY COLUMN severity ENUM(
        'minor', 'low', 'moderate', 'high', 'critical'
    ) NOT NULL DEFAULT 'moderate',
    MODIFY COLUMN status ENUM(
        'pending_verification', 'fake', 'pending', 'dispatched', 'responding',
        'en_route', 'on_scene', 'resolved', 'cancelled'
    ) NOT NULL DEFAULT 'pending_verification';

-- The existing HelioHost dump stores reports in the legacy `emergencies` table.
-- Copy them once; report_code is unique, so re-running this is safe.
INSERT IGNORE INTO emergency_reports (
    report_code,
    user_id,
    emergency_type,
    severity,
    status,
    description,
    location,
    latitude,
    longitude,
    created_at,
    updated_at
)
SELECT
    e.reference_code,
    e.reporter_id,
    e.emergency_type,
    CASE e.priority_level WHEN 'low' THEN 'minor' ELSE e.priority_level END,
    e.status,
    e.description,
    COALESCE(NULLIF(e.location_text, ''), 'Unknown'),
    e.latitude,
    e.longitude,
    e.created_at,
    e.updated_at
FROM emergencies e
INNER JOIN users u ON u.id = e.reporter_id;
