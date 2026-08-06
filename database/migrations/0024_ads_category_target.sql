-- ---------------------------------------------------------------------------
-- 0024_ads_category_target.sql
-- ---------------------------------------------------------------------------
-- **Fixes "No option to add category-specific banners and header banner in
-- Business Listings."**
--
-- The ad system had exactly ONE targeting dimension: `ads_prices.slot_key`,
-- the position on the page. `Usermodel::getAdForSlot()` matched on that alone,
-- and the admin booking form (`views/admin/create_ads.php`) offered user,
-- price, dates, artwork and a click-through URL — no category, no listing.
--
-- The slot keys that read as category-specific are not: `middle-banner-category`
-- and `bottom-banner-category` are single global inventory items included by
-- eight different views. "Category" there means "rendered on category pages",
-- not "sold per category". So an advertiser could not buy the Restaurants
-- banner; they could only buy *the* banner that happens to appear on category
-- pages, whichever category the visitor was looking at.
--
-- This adds `ads.category_id`, nullable:
--
--   NULL  the ad runs in that slot everywhere, exactly as today.
--   <id>  the ad runs in that slot only while the visitor is browsing that
--         listing category.
--
-- Selection prefers a category-specific booking over an untargeted one, so a
-- sold category banner beats the general one and an unsold category still
-- falls back to whatever is booked site-wide. Nothing already in `ads` changes
-- behaviour, because existing rows get NULL.
--
-- Also seeds the header slot the report asks for: `top-banner-category`, a
-- 900x100 above the directory listing, alongside the existing
-- `middle-banner-category` and `bottom-banner-category`. Same rule as 0017 and
-- 0019 — the slot_key is the plans.slug of the package sold for it.
--
-- Idempotent — INFORMATION_SCHEMA guard on the column, NOT EXISTS on the seed.
-- Safe to re-run.
--
-- Run once after 0023:
--   mysql -u <user> -p <db> < database/migrations/0024_ads_category_target.sql
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_add_ads_category_target;
DELIMITER //
CREATE PROCEDURE pearland411_add_ads_category_target()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'ads'
          AND COLUMN_NAME = 'category_id'
    ) THEN
        ALTER TABLE `ads`
            ADD COLUMN `category_id` INT(11) NULL DEFAULT NULL
                COMMENT 'listing_categories.category_id — NULL runs the ad everywhere',
            ADD KEY `ads_category_idx` (`category_id`);
    END IF;

    -- The header banner for the directory, so there is a slot to sell.
    -- Same column list and guard as 0017 / 0019.
    IF EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'ads_prices'
          AND COLUMN_NAME = 'slot_key'
    ) THEN
        INSERT INTO `ads_prices` (`name`, `slot_key`, `size`, `cost`, `status`, `created_at`, `updated_at`)
        SELECT * FROM (
            SELECT 'Category Header Banner' AS name,
                   'top-banner-category'    AS slot_key,
                   '900x100'                AS size,
                   25.00                    AS cost,
                   1                        AS status,
                   NOW()                    AS created_at,
                   NOW()                    AS updated_at
        ) AS seed
        WHERE NOT EXISTS (
            SELECT 1 FROM `ads_prices` WHERE `ads_prices`.`slot_key` = seed.slot_key
        );
    END IF;
END //
DELIMITER ;
CALL pearland411_add_ads_category_target();
DROP PROCEDURE pearland411_add_ads_category_target;
