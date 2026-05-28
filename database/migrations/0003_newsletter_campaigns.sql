-- ---------------------------------------------------------------------------
-- 0003_newsletter_campaigns.sql
-- ---------------------------------------------------------------------------
-- Admin can already collect newsletter subscribers but had no way to
-- email them. This migration adds the campaign queue + per-recipient
-- send log so admins can compose, queue, and ship campaigns.
--
-- Run once after 0002_services.sql:
--   mysql -u <user> -p <db> < database/migrations/0003_newsletter_campaigns.sql
--
-- Sender is intentionally simple: cron (or manual admin action) walks
-- through queued campaigns and sends each recipient one by one. For
-- bigger lists, swap to a real ESP (SendGrid, SES, Postmark) later.
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `newsletter_campaigns` (
    `campaign_id`     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `subject`         VARCHAR(255) NOT NULL,
    `body_html`       MEDIUMTEXT  NOT NULL,
    `from_name`       VARCHAR(160) NULL,
    `from_address`    VARCHAR(255) NULL,
    `status`          ENUM('draft','queued','sending','sent','failed','canceled')
                          NOT NULL DEFAULT 'draft',
    `total_recipients` INT UNSIGNED NOT NULL DEFAULT 0,
    `sent_count`      INT UNSIGNED NOT NULL DEFAULT 0,
    `failed_count`    INT UNSIGNED NOT NULL DEFAULT 0,
    `scheduled_at`    DATETIME NULL,
    `started_at`      DATETIME NULL,
    `finished_at`     DATETIME NULL,
    `created_by`      BIGINT UNSIGNED NULL,
    `created_at`      DATETIME NOT NULL,
    `updated_at`      DATETIME NOT NULL,
    PRIMARY KEY (`campaign_id`),
    KEY `nc_status_idx` (`status`),
    KEY `nc_scheduled_idx` (`scheduled_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `newsletter_campaign_sends` (
    `send_id`     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `campaign_id` BIGINT UNSIGNED NOT NULL,
    `email`       VARCHAR(255) NOT NULL,
    `status`      ENUM('pending','sent','failed','skipped')
                       NOT NULL DEFAULT 'pending',
    `error`       VARCHAR(500) NULL,
    `sent_at`     DATETIME NULL,
    `created_at`  DATETIME NOT NULL,
    PRIMARY KEY (`send_id`),
    KEY `ncs_campaign_idx` (`campaign_id`, `status`),
    UNIQUE KEY `ncs_campaign_email_uq` (`campaign_id`, `email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
