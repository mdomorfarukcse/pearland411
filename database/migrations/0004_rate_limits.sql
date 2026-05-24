-- ---------------------------------------------------------------------------
-- 0004_rate_limits.sql
-- ---------------------------------------------------------------------------
-- Backing table for the Rate_limit library (application/libraries/
-- Rate_limit.php). One row per (action, identifier) sliding-window
-- bucket. Rows older than their window_end are garbage-collected
-- opportunistically by the library and via the maintenance task in
-- application/controllers/Sitemap.php style cron, if scheduled.
--
-- Run once after 0003:
--   mysql -u <user> -p <db> < database/migrations/0004_rate_limits.sql
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `rate_limits` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `bucket_key`    VARCHAR(180) NOT NULL,
    `attempts`      INT UNSIGNED NOT NULL DEFAULT 0,
    `window_start`  DATETIME NOT NULL,
    `window_end`    DATETIME NOT NULL,
    `last_ip`       VARCHAR(45) NULL,
    `created_at`    DATETIME NOT NULL,
    `updated_at`    DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `rate_limits_bucket_uq` (`bucket_key`),
    KEY `rate_limits_window_end_idx` (`window_end`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
