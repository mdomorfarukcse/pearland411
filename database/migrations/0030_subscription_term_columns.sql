-- ---------------------------------------------------------------------------
-- 0030_subscription_term_columns.sql
-- ---------------------------------------------------------------------------
-- Creates the `subscriptions` columns the code has always written but no
-- migration has ever added.
--
-- Found by /admin/billing-customer/<id> returning HTTP 500. The page asked for
-- `subscriptions.duration_id` and `subscriptions.duration_days`; MySQL answers
-- a missing column with error 1054 rather than a NULL, so the request died.
-- Those two, and several others, are written by
-- Usermodel::activateHelcimSubscription() — the self-serve checkout — and
-- appear in no `ALTER TABLE` anywhere in this repo. On a database where no
-- customer has ever completed a checkout they were simply never created.
--
-- Which means the self-serve checkout has been broken here the whole time, in
-- the same way and for the same reason: its INSERT names columns the table
-- does not have. Nobody noticed because nobody had bought anything yet.
--
-- Every column is added ONLY when missing, so on a database where a checkout
-- did once succeed this file changes nothing at all. Defaults match what
-- activateHelcimSubscription() writes for a fresh subscription:
--
--   duration_id       plan_durations row bought, when a term was chosen
--   duration_days     the RECURRING cadence — recordRenewalSuccess() extends
--                     the term by exactly this many days on every renewal
--   duration_months   the same span in months, for display
--   total_amount      what was paid for the current term
--   currency          ISO currency of that amount
--   billing_cycle     'monthly' / 'yearly', for display
--   payment_status    'paid' / 'pending'
--   start_date        when the current term began
--   last_transaction_id  the payment that bought the current term
--
-- The application no longer depends on this file: the billing screen reads the
-- subscription with SELECT * and treats every one of these as optional, and
-- the writes drop unknown columns before inserting. Run it anyway — without
-- these columns a renewal cannot know its own term length, and every
-- subscription would silently fall back to 30 days.
--
-- Idempotent. Run after 0029:
--   mysql -u <user> -p <db> < database/migrations/0030_subscription_term_columns.sql
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_add_subscription_terms;
DELIMITER //
CREATE PROCEDURE pearland411_add_subscription_terms()
BEGIN
    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions') THEN

        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
                          AND COLUMN_NAME = 'duration_id') THEN
            ALTER TABLE `subscriptions`
                ADD COLUMN `duration_id` INT(11) NULL DEFAULT NULL
                COMMENT 'plan_durations row bought, when a term was chosen';
        END IF;

        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
                          AND COLUMN_NAME = 'duration_days') THEN
            ALTER TABLE `subscriptions`
                ADD COLUMN `duration_days` INT(11) NOT NULL DEFAULT 30
                COMMENT 'Recurring cadence in days. Each renewal extends by this';
        END IF;

        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
                          AND COLUMN_NAME = 'duration_months') THEN
            ALTER TABLE `subscriptions`
                ADD COLUMN `duration_months` INT(11) NOT NULL DEFAULT 1
                COMMENT 'The same span in months, for display';
        END IF;

        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
                          AND COLUMN_NAME = 'total_amount') THEN
            ALTER TABLE `subscriptions`
                ADD COLUMN `total_amount` DECIMAL(10,2) NULL DEFAULT NULL
                COMMENT 'What was paid for the current term';
        END IF;

        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
                          AND COLUMN_NAME = 'currency') THEN
            ALTER TABLE `subscriptions`
                ADD COLUMN `currency` VARCHAR(10) NOT NULL DEFAULT 'USD'
                COMMENT 'ISO currency of total_amount';
        END IF;

        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
                          AND COLUMN_NAME = 'billing_cycle') THEN
            ALTER TABLE `subscriptions`
                ADD COLUMN `billing_cycle` VARCHAR(20) NOT NULL DEFAULT 'monthly'
                COMMENT 'monthly / yearly, for display';
        END IF;

        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
                          AND COLUMN_NAME = 'payment_status') THEN
            ALTER TABLE `subscriptions`
                ADD COLUMN `payment_status` VARCHAR(20) NOT NULL DEFAULT 'pending'
                COMMENT 'paid / pending';
        END IF;

        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
                          AND COLUMN_NAME = 'start_date') THEN
            ALTER TABLE `subscriptions`
                ADD COLUMN `start_date` DATETIME NULL DEFAULT NULL
                COMMENT 'When the current term began';
        END IF;

        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
                          AND COLUMN_NAME = 'last_transaction_id') THEN
            ALTER TABLE `subscriptions`
                ADD COLUMN `last_transaction_id` INT(11) NULL DEFAULT NULL
                COMMENT 'The payment that bought the current term';
        END IF;
    END IF;
END //
DELIMITER ;
CALL pearland411_add_subscription_terms();
DROP PROCEDURE pearland411_add_subscription_terms;
