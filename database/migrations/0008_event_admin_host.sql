-- ---------------------------------------------------------------------------
-- 0008_event_admin_host.sql
-- ---------------------------------------------------------------------------
-- Events are now hosted/created by an ADMIN account (staff), not a public
-- user. The Add/Edit Event "Host" dropdown lists admin accounts so the list
-- only ever shows our own recognisable names.
--
-- We add a dedicated `admin_id` column to `events` for the host instead of
-- overloading the existing `user_id` column, because:
--   * `user_id` still drives enquiry routing and legacy host display, and
--   * an admin_id could collide with an unrelated user_id and show the wrong
--     name.
--
-- The host name is resolved in code as COALESCE(admin.admin_name, user.name)
-- so legacy events (with only user_id) keep working unchanged.
--
-- Idempotent — safe to run more than once. Run after 0007:
--   mysql -u <user> -p <db> < database/migrations/0008_event_admin_host.sql
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_add_event_admin_host;
DELIMITER //
CREATE PROCEDURE pearland411_add_event_admin_host()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'events'
          AND COLUMN_NAME = 'admin_id'
    ) THEN
        ALTER TABLE `events`
            ADD COLUMN `admin_id` INT NULL DEFAULT NULL AFTER `user_id`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'events'
          AND INDEX_NAME = 'events_admin_id_idx'
    ) THEN
        ALTER TABLE `events`
            ADD INDEX `events_admin_id_idx` (`admin_id`);
    END IF;
END //
DELIMITER ;
CALL pearland411_add_event_admin_host();
DROP PROCEDURE pearland411_add_event_admin_host;
