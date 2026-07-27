-- ---------------------------------------------------------------------------
-- 0016_transaction_term.sql
-- ---------------------------------------------------------------------------
-- Customers can now buy a package for a longer term (12 months, 2 years, ...)
-- instead of only its base period. Listing tiers record that on `subscriptions`
-- (duration_days / end_date), but ad placements — banners, videos, add-ons —
-- are recorded as rows in `transactions`, which had nowhere to store the term.
--
-- Without this the expiry shown to the customer was recomputed from the plan's
-- base `duration_days`, so someone who paid for 12 months of a banner was told
-- it expires in 30 days.
--
--   term_days   Length of the purchased term in days.
--   expires_at  When the placement lapses (purchase date + term_days).
--   duration_id The plan_durations row bought, when one was chosen.
--
-- Idempotent — safe to re-run. Run after 0015:
--   mysql -u <user> -p <db> < database/migrations/0016_transaction_term.sql
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_add_transaction_term;
DELIMITER //
CREATE PROCEDURE pearland411_add_transaction_term()
BEGIN
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions'
                      AND COLUMN_NAME = 'term_days') THEN
        ALTER TABLE `transactions`
            ADD COLUMN `term_days` INT(11) NULL DEFAULT NULL
            COMMENT 'Length of the purchased term in days';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions'
                      AND COLUMN_NAME = 'expires_at') THEN
        ALTER TABLE `transactions`
            ADD COLUMN `expires_at` DATETIME NULL DEFAULT NULL
            COMMENT 'When this placement lapses (purchase date + term_days)';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions'
                      AND COLUMN_NAME = 'duration_id') THEN
        ALTER TABLE `transactions`
            ADD COLUMN `duration_id` INT(11) NULL DEFAULT NULL
            COMMENT 'plan_durations row purchased, when a term was chosen';
    END IF;

    -- Back-fill existing placements from their plan's base term so the
    -- "Your active packages" strip keeps showing a sensible expiry.
    UPDATE `transactions` t
      JOIN `plans` p ON p.id = t.plan_id
       SET t.term_days  = p.duration_days,
           t.expires_at = DATE_ADD(t.created_at, INTERVAL p.duration_days DAY)
     WHERE t.term_days IS NULL
       AND p.duration_days > 0;
END //
DELIMITER ;

CALL pearland411_add_transaction_term();
DROP PROCEDURE IF EXISTS pearland411_add_transaction_term;
