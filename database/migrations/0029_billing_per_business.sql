-- ---------------------------------------------------------------------------
-- 0029_billing_per_business.sql
-- ---------------------------------------------------------------------------
-- Bills a BUSINESS, not an account, and lets staff agree a price.
--
-- 0028 built /admin/billing around `user`, because that is what `subscriptions`
-- and `payment_cards` are keyed on. On this site that is the wrong unit: the
-- listings were bulk-loaded under a handful of data-entry logins, so one
-- account owns dozens of unrelated businesses. Searching for "central bbq"
-- returned the import account "10 dollars Galveston shuttle" with a hundred
-- business names strung after it, and billing it would have billed all of them
-- at once. What staff sell, invoice and cancel is one business.
--
--   subscriptions.listing_id     the business this subscription bills for.
--                                NULL = an account-level subscription, which is
--                                every row that exists today and every one the
--                                self-serve checkout creates. Nothing changes
--                                for those.
--   subscriptions.custom_amount  an agreed price that overrides the plan's on
--                                every renewal. NULL = charge the plan or its
--                                chosen term, exactly as before. This is the
--                                "custom amount or package" choice: a package
--                                sets the entitlements, this sets the price,
--                                and a founding-member rate no longer breaks
--                                the first time the plan's price is edited.
--   transactions.listing_id      so billing history reads per business rather
--                                than pooling every business the account owns.
--
-- Also seeds `billing_notify_emails` — who is copied on billing activity, one
-- address per line, on top of every active admin.
--
-- All three columns are nullable with no default, so every existing row stays
-- valid and nothing is back-filled: an existing subscription keeps billing the
-- account exactly as it does today until somebody attaches it to a business.
--
-- Idempotent — each column added only when missing, each step guarded on its
-- table existing. Run after 0028:
--   mysql -u <user> -p <db> < database/migrations/0029_billing_per_business.sql
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_add_billing_per_business;
DELIMITER //
CREATE PROCEDURE pearland411_add_billing_per_business()
BEGIN
    -- subscriptions -------------------------------------------------------
    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions') THEN

        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
                          AND COLUMN_NAME = 'listing_id') THEN
            ALTER TABLE `subscriptions`
                ADD COLUMN `listing_id` INT(11) NULL DEFAULT NULL
                COMMENT 'Business this bills for. NULL means account-level';
        END IF;

        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
                          AND INDEX_NAME = 'idx_subscriptions_listing') THEN
            ALTER TABLE `subscriptions`
                ADD INDEX `idx_subscriptions_listing` (`listing_id`);
        END IF;

        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
                          AND COLUMN_NAME = 'custom_amount') THEN
            ALTER TABLE `subscriptions`
                ADD COLUMN `custom_amount` DECIMAL(10,2) NULL DEFAULT NULL
                COMMENT 'Agreed price that overrides the plan price on renewal';
        END IF;
    END IF;

    -- transactions --------------------------------------------------------
    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions') THEN

        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions'
                          AND COLUMN_NAME = 'listing_id') THEN
            ALTER TABLE `transactions`
                ADD COLUMN `listing_id` INT(11) NULL DEFAULT NULL
                COMMENT 'Business this payment was for';
        END IF;

        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions'
                          AND INDEX_NAME = 'idx_transactions_listing') THEN
            ALTER TABLE `transactions`
                ADD INDEX `idx_transactions_listing` (`listing_id`);
        END IF;
    END IF;

    -- Who gets copied on billing activity, on top of every active admin.
    -- INSERT IGNORE so re-running never overwrites an edited list.
    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'site_settings') THEN
        INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`, `updated_at`)
             VALUES ('billing_notify_emails', 'joshua@spacecentersystems.com', NOW());
    END IF;
END //
DELIMITER ;
CALL pearland411_add_billing_per_business();
DROP PROCEDURE pearland411_add_billing_per_business;
