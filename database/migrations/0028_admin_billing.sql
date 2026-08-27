-- ---------------------------------------------------------------------------
-- 0028_admin_billing.sql
-- ---------------------------------------------------------------------------
-- Backs /admin/billing — the screen where staff put a customer's card on file,
-- take a one-off payment, and schedule recurring billing.
--
-- Until now a card could only be vaulted by the CUSTOMER, inside
-- User::process_checkout(): `activateHelcimSubscription()` writes a
-- `payment_cards` row only when the buyer ticked auto-renew during their own
-- checkout, with `card_last4 = '0000'` and `exp 1/2099` placeholders because
-- nothing ever displayed them. An admin taking a card over the phone had no
-- path at all, and `Cli::renew_subscriptions` therefore had nothing to charge.
--
-- Five columns, all nullable / defaulted, so every existing row stays valid:
--
--   payment_cards.card_brand         'Visa' / 'Mastercard' / ... for display.
--   payment_cards.card_source        'customer' (self-serve checkout) or
--                                    'admin' (keyed in by staff). Lets the
--                                    billing screen say where a card came from
--                                    and lets support tell the two apart.
--   payment_cards.added_by_admin_id  which admin put it on file — an audit
--                                    trail for a card the customer did not
--                                    enter themselves.
--   transactions.created_by_admin_id which admin took a manual payment. NULL
--                                    for every self-serve and cron charge.
--   transactions.note                free text shown in billing history, e.g.
--                                    "August 2026 bill". `transactions` had
--                                    nowhere to record WHY a one-off charge
--                                    was taken, so a manual payment was
--                                    indistinguishable from a renewal.
--
-- Deliberately NOT added: any column that could hold a PAN, expiry, or CVV
-- beyond the last four digits. Card data is tokenized by HelcimPay.js in the
-- browser and never reaches this server; `pan_encrypted` stays NULL, as 0012
-- established.
--
-- Idempotent — each column added only when missing, and each step guarded on
-- its table existing (`payment_cards` and `transactions` both predate this
-- repo's migrations). Run after 0027:
--   mysql -u <user> -p <db> < database/migrations/0028_admin_billing.sql
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_add_admin_billing;
DELIMITER //
CREATE PROCEDURE pearland411_add_admin_billing()
BEGIN
    -- payment_cards -------------------------------------------------------
    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payment_cards') THEN

        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payment_cards'
                          AND COLUMN_NAME = 'card_brand') THEN
            ALTER TABLE `payment_cards`
                ADD COLUMN `card_brand` VARCHAR(30) NULL DEFAULT NULL
                COMMENT 'Card brand for display (Visa, Mastercard, ...)';
        END IF;

        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payment_cards'
                          AND COLUMN_NAME = 'card_source') THEN
            ALTER TABLE `payment_cards`
                ADD COLUMN `card_source` VARCHAR(20) NOT NULL DEFAULT 'customer'
                COMMENT 'customer = self-serve checkout, admin = keyed in by staff';
        END IF;

        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payment_cards'
                          AND COLUMN_NAME = 'added_by_admin_id') THEN
            ALTER TABLE `payment_cards`
                ADD COLUMN `added_by_admin_id` INT(11) NULL DEFAULT NULL
                COMMENT 'admin.admin_id who put this card on file, when staff did';
        END IF;
    END IF;

    -- transactions --------------------------------------------------------
    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions') THEN

        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions'
                          AND COLUMN_NAME = 'created_by_admin_id') THEN
            ALTER TABLE `transactions`
                ADD COLUMN `created_by_admin_id` INT(11) NULL DEFAULT NULL
                COMMENT 'admin.admin_id who took this payment manually';
        END IF;

        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions'
                          AND COLUMN_NAME = 'note') THEN
            ALTER TABLE `transactions`
                ADD COLUMN `note` VARCHAR(255) NULL DEFAULT NULL
                COMMENT 'What this charge was for, e.g. "August 2026 bill"';
        END IF;
    END IF;
END //
DELIMITER ;
CALL pearland411_add_admin_billing();
DROP PROCEDURE pearland411_add_admin_billing;
