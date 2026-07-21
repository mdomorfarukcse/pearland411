-- ---------------------------------------------------------------------------
-- 0010_plan_capabilities.sql
-- ---------------------------------------------------------------------------
-- Freemium listing model: a claimed listing can edit basic fields (name /
-- phone / email) for free, but richer fields — photos, Google map, services,
-- and "extra details" — unlock only with a paid package that grants the
-- capability. The `plans` table already carries per-content COUNT limits and
-- an `allow_video` flag (reused as the YouTube-video capability); it has no
-- field-level capability flags. This migration adds them.
--
--   can_photos         may upload a logo + gallery photos
--   photo_limit        max gallery photos (-1 = unlimited, 0 = none)
--   can_map            may set the Google map / lat-lng
--   can_services       may fill "services offered"
--   can_extra_details  may add the extra Q&A / SEO detail block
--
-- (Video reuses the existing `plans.allow_video`.)
--
-- Idempotent — each column is added only when missing, so the migration is
-- safe to re-run. Run after 0009:
--   mysql -u <user> -p <db> < database/migrations/0010_plan_capabilities.sql
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_add_plan_capabilities;
DELIMITER //
CREATE PROCEDURE pearland411_add_plan_capabilities()
BEGIN
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans'
                      AND COLUMN_NAME = 'can_photos') THEN
        ALTER TABLE `plans`
            ADD COLUMN `can_photos` TINYINT(1) NOT NULL DEFAULT 0
            COMMENT 'May upload listing logo + gallery photos';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans'
                      AND COLUMN_NAME = 'photo_limit') THEN
        ALTER TABLE `plans`
            ADD COLUMN `photo_limit` INT(11) NOT NULL DEFAULT 0
            COMMENT 'Max gallery photos (-1 = unlimited, 0 = none)';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans'
                      AND COLUMN_NAME = 'can_map') THEN
        ALTER TABLE `plans`
            ADD COLUMN `can_map` TINYINT(1) NOT NULL DEFAULT 0
            COMMENT 'May set the Google map / lat-lng';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans'
                      AND COLUMN_NAME = 'can_services') THEN
        ALTER TABLE `plans`
            ADD COLUMN `can_services` TINYINT(1) NOT NULL DEFAULT 0
            COMMENT 'May fill services offered';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans'
                      AND COLUMN_NAME = 'can_extra_details') THEN
        ALTER TABLE `plans`
            ADD COLUMN `can_extra_details` TINYINT(1) NOT NULL DEFAULT 0
            COMMENT 'May add extra Q&A / SEO detail block';
    END IF;
END //
DELIMITER ;
CALL pearland411_add_plan_capabilities();
DROP PROCEDURE pearland411_add_plan_capabilities;
