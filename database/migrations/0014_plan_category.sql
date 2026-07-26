-- ---------------------------------------------------------------------------
-- 0014_plan_category.sql
-- ---------------------------------------------------------------------------
-- The advertising catalog (Business Listing tiers, Add-ons, Site-wide Banners,
-- Category Banners, Videos, and the Special Package) groups packages into
-- sections. The `plans` table had no way to record which section a package
-- belongs to, so the admin/public pricing pages could not render the packages
-- under their catalog headings. This migration adds a `category` column.
--
--   category   Advertising catalog group slug. One of:
--                business-listing, add-ons, site-wide-banners,
--                category-banners, site-wide-videos, category-videos, special
--
-- Idempotent — the column is added only when missing, so the migration is safe
-- to re-run. Run after 0013:
--   mysql -u <user> -p <db> < database/migrations/0014_plan_category.sql
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_add_plan_category;
DELIMITER //
CREATE PROCEDURE pearland411_add_plan_category()
BEGIN
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans'
                      AND COLUMN_NAME = 'category') THEN
        ALTER TABLE `plans`
            ADD COLUMN `category` VARCHAR(50) NOT NULL DEFAULT 'business-listing'
            COMMENT 'Advertising catalog group (business-listing, add-ons, site-wide-banners, category-banners, site-wide-videos, category-videos, special)'
            AFTER `sort_order`;
    END IF;
END //
DELIMITER ;
CALL pearland411_add_plan_category();
DROP PROCEDURE pearland411_add_plan_category;
