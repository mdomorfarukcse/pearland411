-- ---------------------------------------------------------------------------
-- 0012_helcim_billing.sql
-- ---------------------------------------------------------------------------
-- Payment processor swap: Authorize.Net -> Helcim (HelcimPay.js hosted
-- checkout + card vault for recurring). The `transactions` and `payment_cards`
-- tables carry Authorize.Net-specific columns (auth_net_*). Rather than drop
-- them (they hold historical rows), we ADD parallel Helcim columns and leave
-- the old ones in place, unused.
--
--   transactions.helcim_transaction_id   Helcim transactionId
--   transactions.helcim_card_token       token returned by HelcimPay.js
--   transactions.helcim_approval_code    gateway approval code
--   payment_cards.helcim_card_token      vaulted card token (recurring)
--   payment_cards.helcim_customer_code   Helcim customer/vault code
--
-- `subscriptions.subscription_id` (commented "Authorize.Net subscription ID")
-- is repurposed to hold the Helcim recurring reference — no rename needed.
-- `payment_cards.pan_encrypted` stays NULL; we never store PANs (HelcimPay.js
-- tokenizes card data client-side).
--
-- Idempotent — each column added only when missing. Run after 0011:
--   mysql -u <user> -p <db> < database/migrations/0012_helcim_billing.sql
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_add_helcim_billing;
DELIMITER //
CREATE PROCEDURE pearland411_add_helcim_billing()
BEGIN
    -- transactions
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions'
                      AND COLUMN_NAME = 'helcim_transaction_id') THEN
        ALTER TABLE `transactions`
            ADD COLUMN `helcim_transaction_id` VARCHAR(255) DEFAULT NULL
            COMMENT 'Helcim transactionId';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions'
                      AND COLUMN_NAME = 'helcim_card_token') THEN
        ALTER TABLE `transactions`
            ADD COLUMN `helcim_card_token` VARCHAR(255) DEFAULT NULL
            COMMENT 'Card token returned by HelcimPay.js';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions'
                      AND COLUMN_NAME = 'helcim_approval_code') THEN
        ALTER TABLE `transactions`
            ADD COLUMN `helcim_approval_code` VARCHAR(50) DEFAULT NULL
            COMMENT 'Gateway approval code';
    END IF;

    -- payment_cards
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payment_cards'
                      AND COLUMN_NAME = 'helcim_card_token') THEN
        ALTER TABLE `payment_cards`
            ADD COLUMN `helcim_card_token` VARCHAR(255) DEFAULT NULL
            COMMENT 'Vaulted card token for recurring charges';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payment_cards'
                      AND COLUMN_NAME = 'helcim_customer_code') THEN
        ALTER TABLE `payment_cards`
            ADD COLUMN `helcim_customer_code` VARCHAR(100) DEFAULT NULL
            COMMENT 'Helcim customer / vault code';
    END IF;
END //
DELIMITER ;
CALL pearland411_add_helcim_billing();
DROP PROCEDURE pearland411_add_helcim_billing;
