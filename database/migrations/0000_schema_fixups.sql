-- ---------------------------------------------------------------------------
-- 0000_schema_fixups.sql
-- ---------------------------------------------------------------------------
-- MUST RUN BEFORE 0001_auth_flows.sql.
--
-- Corrects existing-schema problems found when comparing the live
-- pearlan_new dump against the new auth code:
--
--   1. `admin`.`admin_password` was varchar(50). password_hash() emits
--      a 60-character bcrypt hash; MySQL would silently truncate it
--      on insert, locking the admin out on their next login. Widen
--      to varchar(255).
--
--   2. `admin` has no `banned` column even though Adminmodel::login
--      now reads $admin->banned. Add it as a tinyint flag.
--
--   3. `user`.`email_id` was varchar(50). RFC 5321 allows up to 254
--      characters and real-world signups will hit this limit. Widen.
--
-- Run once:
--   mysql -u <user> -p <db> < database/migrations/0000_schema_fixups.sql
-- ---------------------------------------------------------------------------

-- 1. Widen admin password column to hold bcrypt hashes.
ALTER TABLE `admin`
    MODIFY COLUMN `admin_password` VARCHAR(255) DEFAULT NULL;

-- 2. Add `banned` flag to admin (idempotent via INFORMATION_SCHEMA guard).
DROP PROCEDURE IF EXISTS pearland411_add_admin_banned;
DELIMITER //
CREATE PROCEDURE pearland411_add_admin_banned()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'admin'
          AND COLUMN_NAME = 'banned'
    ) THEN
        ALTER TABLE `admin`
            ADD COLUMN `banned` TINYINT(1) NOT NULL DEFAULT 0 AFTER `admin_type`;
    END IF;
END //
DELIMITER ;
CALL pearland411_add_admin_banned();
DROP PROCEDURE pearland411_add_admin_banned;

-- 3. Widen user.email_id to 255 chars.
ALTER TABLE `user`
    MODIFY COLUMN `email_id` VARCHAR(255) DEFAULT NULL;

-- 4. Add UNIQUE index on user.email_id so the existing
--    is_unique[user.email_id] validation rule actually has a constraint
--    backing it. The seed dump shows no duplicate emails today.
DROP PROCEDURE IF EXISTS pearland411_add_user_email_uq;
DELIMITER //
CREATE PROCEDURE pearland411_add_user_email_uq()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'user'
          AND INDEX_NAME = 'user_email_id_uq'
    ) THEN
        ALTER TABLE `user`
            ADD UNIQUE KEY `user_email_id_uq` (`email_id`);
    END IF;
END //
DELIMITER ;
CALL pearland411_add_user_email_uq();
DROP PROCEDURE pearland411_add_user_email_uq;

-- 5. Hot-path index on listings(listing_slug). The 30k-listing scale
--    will benefit immediately.
DROP PROCEDURE IF EXISTS pearland411_add_listing_slug_idx;
DELIMITER //
CREATE PROCEDURE pearland411_add_listing_slug_idx()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'listings'
          AND INDEX_NAME = 'listings_listing_slug_idx'
    ) THEN
        ALTER TABLE `listings`
            ADD INDEX `listings_listing_slug_idx` (`listing_slug`(191));
    END IF;
END //
DELIMITER ;
CALL pearland411_add_listing_slug_idx();
DROP PROCEDURE pearland411_add_listing_slug_idx;
