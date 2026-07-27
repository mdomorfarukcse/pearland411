-- ---------------------------------------------------------------------------
-- 0015_plan_durations_columns.sql
-- ---------------------------------------------------------------------------
-- `plan_durations` holds the per-term pricing for a plan (1 month, 3 months,
-- 12 months, ...). It was never created by a migration — it only ever existed
-- in the production database — so two things were broken:
--
--   1. A fresh install had no `plan_durations` table at all, so the seed
--      `database/seeds/pearland411_packages.sql` and the admin duration editor
--      both failed.
--   2. On the live database the table exists but lacks `currency` and
--      `updated_at`. `Adminmodel::updatePricingDuration()` writes `updated_at`
--      on every save, so the UPDATE errored and **saving a duration price
--      silently did nothing** — the admin "Edit Durations" screen appeared to
--      accept the change and then discarded it.
--
-- This migration creates the table when missing and back-fills any missing
-- column when it already exists, so it is correct in both situations.
--
-- Idempotent — safe to re-run. Run after 0014:
--   mysql -u <user> -p <db> < database/migrations/0015_plan_durations_columns.sql
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `plan_durations` (
    `id`              INT(11) NOT NULL AUTO_INCREMENT,
    `plan_id`         INT(11) NOT NULL,
    `duration_months` INT(11) NOT NULL DEFAULT 1  COMMENT 'Term length in months (0 for day-based terms)',
    `duration_days`   INT(11) NOT NULL DEFAULT 30 COMMENT 'Term length in days — authoritative for expiry maths',
    `price`           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `currency`        VARCHAR(10) NOT NULL DEFAULT 'USD',
    `is_active`       TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`      DATETIME NULL DEFAULT NULL,
    `updated_at`      DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_plan_durations_plan` (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP PROCEDURE IF EXISTS pearland411_fix_plan_durations;
DELIMITER //
CREATE PROCEDURE pearland411_fix_plan_durations()
BEGIN
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plan_durations'
                      AND COLUMN_NAME = 'currency') THEN
        ALTER TABLE `plan_durations`
            ADD COLUMN `currency` VARCHAR(10) NOT NULL DEFAULT 'USD' AFTER `price`;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plan_durations'
                      AND COLUMN_NAME = 'duration_days') THEN
        ALTER TABLE `plan_durations`
            ADD COLUMN `duration_days` INT(11) NOT NULL DEFAULT 30 AFTER `duration_months`;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plan_durations'
                      AND COLUMN_NAME = 'is_active') THEN
        ALTER TABLE `plan_durations`
            ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plan_durations'
                      AND COLUMN_NAME = 'created_at') THEN
        ALTER TABLE `plan_durations`
            ADD COLUMN `created_at` DATETIME NULL DEFAULT NULL;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plan_durations'
                      AND COLUMN_NAME = 'updated_at') THEN
        ALTER TABLE `plan_durations`
            ADD COLUMN `updated_at` DATETIME NULL DEFAULT NULL;
    END IF;

    -- Day-based terms drive every expiry calculation. Any row that carries a
    -- month count but no day count would expire immediately, so derive it.
    UPDATE `plan_durations`
       SET `duration_days` = `duration_months` * 30
     WHERE (`duration_days` IS NULL OR `duration_days` = 0)
       AND `duration_months` > 0;
END //
DELIMITER ;

CALL pearland411_fix_plan_durations();
DROP PROCEDURE IF EXISTS pearland411_fix_plan_durations;
