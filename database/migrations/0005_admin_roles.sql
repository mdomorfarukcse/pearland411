-- ---------------------------------------------------------------------------
-- 0005_admin_roles.sql
-- ---------------------------------------------------------------------------
-- Minimal role system for the admin table so sub-admins can exist
-- without inheriting full super-admin powers. The existing
-- admin_user_options / admin_type columns were vestigial — keeping
-- them in place but ignoring them in code.
--
-- Roles:
--   super_admin — full access (manages other admins, billing, etc.)
--   editor      — content management (listings, news, events, blogs,
--                 services, classifieds) + service requests +
--                 newsletter campaigns
--
-- The seeded admin (admin_id = 1) is upgraded to super_admin. Any
-- new sub-admin defaults to 'editor' unless explicitly set otherwise.
--
-- Run once after 0004:
--   mysql -u <user> -p <db> < database/migrations/0005_admin_roles.sql
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_add_admin_role;
DELIMITER //
CREATE PROCEDURE pearland411_add_admin_role()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'admin'
          AND COLUMN_NAME = 'role'
    ) THEN
        ALTER TABLE `admin`
            ADD COLUMN `role` ENUM('super_admin','editor') NOT NULL DEFAULT 'editor'
                AFTER `admin_type`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'admin'
          AND COLUMN_NAME = 'is_active'
    ) THEN
        ALTER TABLE `admin`
            ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1
                AFTER `role`;
    END IF;
END //
DELIMITER ;
CALL pearland411_add_admin_role();
DROP PROCEDURE pearland411_add_admin_role;

-- Promote the original seeded admin to super_admin so they can manage
-- the role system. Anyone else seeded later defaults to 'editor'.
UPDATE `admin` SET `role` = 'super_admin' WHERE `admin_id` = 1;
