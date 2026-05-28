-- ---------------------------------------------------------------------------
-- 0001_auth_flows.sql
-- ---------------------------------------------------------------------------
-- Adds tables/columns required for:
--   * email verification on signup
--   * password reset by emailed token
--
-- Run once against the live MySQL database:
--   mysql -u <user> -p <db_name> < database/migrations/0001_auth_flows.sql
--
-- All statements are idempotent where MySQL allows (CREATE TABLE
-- IF NOT EXISTS; column adds are guarded by an INFORMATION_SCHEMA
-- check via a stored procedure so re-running is a no-op).
-- ---------------------------------------------------------------------------

-- New: password reset request log
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    BIGINT UNSIGNED NOT NULL,
    `email`      VARCHAR(255)    NOT NULL,
    `token_hash` CHAR(64)        NOT NULL,
    `created_at` DATETIME        NOT NULL,
    `expires_at` DATETIME        NOT NULL,
    `used_at`    DATETIME        NULL DEFAULT NULL,
    `ip`         VARCHAR(45)     NULL,
    PRIMARY KEY (`id`),
    KEY `password_resets_token_idx` (`token_hash`),
    KEY `password_resets_user_idx`  (`user_id`),
    KEY `password_resets_email_idx` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add the email-verification columns to `user`. Guard with a
-- stored procedure so the script is safe to re-run.
DROP PROCEDURE IF EXISTS pearland411_add_col_if_missing;
DELIMITER //
CREATE PROCEDURE pearland411_add_col_if_missing(
    IN tbl   VARCHAR(64),
    IN col   VARCHAR(64),
    IN ddl   VARCHAR(255)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = tbl
          AND COLUMN_NAME  = col
    ) THEN
        SET @s = CONCAT('ALTER TABLE `', tbl, '` ADD COLUMN ', ddl);
        PREPARE stmt FROM @s;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END //
DELIMITER ;

CALL pearland411_add_col_if_missing(
    'user', 'email_verified',
    '`email_verified` TINYINT(1) NOT NULL DEFAULT 0 AFTER `email_id`'
);
CALL pearland411_add_col_if_missing(
    'user', 'email_verification_token',
    '`email_verification_token` CHAR(64) NULL DEFAULT NULL AFTER `email_verified`'
);
CALL pearland411_add_col_if_missing(
    'user', 'email_verification_sent_at',
    '`email_verification_sent_at` DATETIME NULL DEFAULT NULL AFTER `email_verification_token`'
);
CALL pearland411_add_col_if_missing(
    'user', 'email_verified_at',
    '`email_verified_at` DATETIME NULL DEFAULT NULL AFTER `email_verification_sent_at`'
);

DROP PROCEDURE pearland411_add_col_if_missing;

-- Hot-path index on the verification token lookup.
CREATE INDEX `user_email_verification_token_idx`
    ON `user` (`email_verification_token`);

-- Existing rows should be considered verified so we don't lock out
-- legacy customers. Only NEW signups will start unverified.
UPDATE `user`
   SET `email_verified` = 1,
       `email_verified_at` = COALESCE(`email_verified_at`, NOW())
 WHERE `email_verified` IS NULL OR `email_verified` = 0;
