-- ---------------------------------------------------------------------------
-- 0018_homepage_promos.sql
-- ---------------------------------------------------------------------------
-- Records the two homepage placements that are already being SOLD but that
-- nothing in the schema could represent:
--
--   plans.slug = 'business-hotspot'      $25 / 30 days
--       "Business Hotspot on Homepage — Your business listed on the homepage
--        for a month."
--   plans.slug = 'business-of-the-week'  $20 /  7 days
--       "Business Of the Week Listing — Your business listed on the homepage
--        for a week for extra exposure."
--
-- `listings` only had `featured_listing` and `spotlight_listing`, so a customer
-- could buy either package and there was no field to put the result in — the
-- homepage had no way to render them.
--
-- WHY DATES, NOT BOOLEANS
-- Both packages are time-boxed (30 and 7 days). A DATE column means the
-- placement drops off by itself when the term ends, instead of relying on
-- someone remembering to untick a box. Set the column to the LAST day the
-- placement should show:
--
--   UPDATE `listings` SET `hotspot_until` = DATE_ADD(CURDATE(), INTERVAL 30 DAY)
--    WHERE `listing_id` = 123;
--   UPDATE `listings` SET `week_until`    = DATE_ADD(CURDATE(), INTERVAL  7 DAY)
--    WHERE `listing_id` = 456;
--
-- To end a placement early, set it back to NULL.
--
-- Idempotent: guarded through INFORMATION_SCHEMA, same pattern as 0006.
-- Safe to re-run.
--
-- Run once after 0017:
--   mysql -u <user> -p <db> < database/migrations/0018_homepage_promos.sql
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_add_promo_cols;
DELIMITER //
CREATE PROCEDURE pearland411_add_promo_cols()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'listings'
          AND COLUMN_NAME = 'hotspot_until'
    ) THEN
        ALTER TABLE `listings`
            ADD COLUMN `hotspot_until` DATE NULL DEFAULT NULL
                COMMENT 'Business Hotspot on Homepage — last day the placement shows',
            ADD KEY `listings_hotspot_until_idx` (`hotspot_until`);
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'listings'
          AND COLUMN_NAME = 'week_until'
    ) THEN
        ALTER TABLE `listings`
            ADD COLUMN `week_until` DATE NULL DEFAULT NULL
                COMMENT 'Business Of the Week — last day the placement shows',
            ADD KEY `listings_week_until_idx` (`week_until`);
    END IF;
END //
DELIMITER ;
CALL pearland411_add_promo_cols();
DROP PROCEDURE pearland411_add_promo_cols;
