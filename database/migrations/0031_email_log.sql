-- ---------------------------------------------------------------------------
-- 0031_email_log.sql
-- ---------------------------------------------------------------------------
-- One row per email the site tries to send, written by Mailer::record()
-- (application/libraries/Mailer.php) and read by /admin/email-diagnostics.
--
-- Why this exists: signup verification and password-reset mail stopped
-- arriving on the live site, and there was no record anywhere that a send had
-- even been attempted — the only report of a failure was log_message('error'),
-- and `application/logs/` did not exist on the server, so CI's logger switched
-- itself off and the message was discarded. With this table an admin can open
-- one page and see whether the site handed the message to a relay, when, and
-- with what error if it refused.
--
-- Run once after 0030:
--   mysql -u <user> -p <db> < database/migrations/0031_email_log.sql
--
-- Idempotent: CREATE TABLE IF NOT EXISTS.
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `email_log` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `to_email`   VARCHAR(255) NOT NULL,
    `subject`    VARCHAR(255) NOT NULL,
    -- 'verification', 'password_reset', 'billing', 'test', 'general', …
    `mail_type`  VARCHAR(40)  NOT NULL DEFAULT 'general',
    -- Transport that carried it: smtp / mail / sendmail. A change here between
    -- a working day and a broken one is itself the answer.
    `protocol`   VARCHAR(20)  NOT NULL DEFAULT 'smtp',
    `status`     VARCHAR(10)  NOT NULL DEFAULT 'sent',
    -- Truncated SMTP transcript for a failure; NULL when it went out.
    `error`      TEXT NULL DEFAULT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `email_log_created_idx` (`created_at`),
    KEY `email_log_status_idx`  (`status`),
    KEY `email_log_to_idx`      (`to_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
