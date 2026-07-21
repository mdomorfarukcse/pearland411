-- ---------------------------------------------------------------------------
-- 0013_subscription_lifecycle.sql
-- ---------------------------------------------------------------------------
-- Recurring billing lifecycle columns on `subscriptions`:
--   renewal_attempts    consecutive failed auto-renew charges (dunning counter)
--   last_renewal_error  last gateway error message (support visibility)
--   grace_until         access is kept until this time while dunning retries
--   pending_plan_id     scheduled downgrade target, applied at next renewal
--
-- Auto-renew itself (auto_renew, next_billing_date) already exists on the
-- table. These columns drive Cli::renew_subscriptions (charge the vaulted
-- Helcim card token, extend the term on success, 3-retry/grace dunning on
-- failure) and the user "manage plan" flow (upgrade now / downgrade at term).
--
-- Idempotent — each column added only when missing. Run after 0012:
--   mysql -u <user> -p <db> < database/migrations/0013_subscription_lifecycle.sql
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_add_subscription_lifecycle;
DELIMITER //
CREATE PROCEDURE pearland411_add_subscription_lifecycle()
BEGIN
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
                      AND COLUMN_NAME = 'renewal_attempts') THEN
        ALTER TABLE `subscriptions`
            ADD COLUMN `renewal_attempts` INT(11) NOT NULL DEFAULT 0
            COMMENT 'Consecutive failed auto-renew charges';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
                      AND COLUMN_NAME = 'last_renewal_error') THEN
        ALTER TABLE `subscriptions`
            ADD COLUMN `last_renewal_error` VARCHAR(255) DEFAULT NULL
            COMMENT 'Last gateway error on renewal';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
                      AND COLUMN_NAME = 'grace_until') THEN
        ALTER TABLE `subscriptions`
            ADD COLUMN `grace_until` DATETIME DEFAULT NULL
            COMMENT 'Access kept until this time during dunning';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
                      AND COLUMN_NAME = 'pending_plan_id') THEN
        ALTER TABLE `subscriptions`
            ADD COLUMN `pending_plan_id` INT(11) DEFAULT NULL
            COMMENT 'Scheduled downgrade target, applied at renewal';
    END IF;
END //
DELIMITER ;
CALL pearland411_add_subscription_lifecycle();
DROP PROCEDURE pearland411_add_subscription_lifecycle;
