-- ===========================================================================
-- homepage_ad_zones_only.sql
-- ===========================================================================
-- Everything the reorganised homepage needs, for a site that ALREADY HAS its
-- own listings. Paste the whole file into phpMyAdmin ▸ SQL and run it.
--
-- It touches three things:
--     ads_prices     — the 7 homepage ad zones            (= migration 0017)
--     listings       — 2 new DATE columns for the promos  (= migration 0018)
--     site_settings  — switches News / Videos / Events on
--
-- It inserts NO listings, categories, news, videos, coupons or events, and
-- modifies no existing row. Safe to run more than once.
--
-- (database/seeds/pearland_sample_content.sql is the other file — that one
--  DOES add demo businesses. Do not run it if you already have real data.)
-- ===========================================================================

SET NAMES utf8mb4;


-- ---------------------------------------------------------------------------
-- 1. Ad zones  (same rows as database/migrations/0017_home_ad_slots.sql)
-- ---------------------------------------------------------------------------
-- Each slot_key equals the plans.slug of the package sold on /pricing-plan,
-- so buying "Top Banner (Site Wide)" fills the top-banner-site-wide position.
--
-- Sizes: your pricing page lists most of these as "900px X 100px" including
-- both skyscrapers, which can't be right. Right-side banners are set to
-- 300x250 and the skyscraper to 295x600 here. The homepage reserves the box
-- from THIS value, so edit these rows (or /admin ▸ Ads ▸ Ad Pricing) and the
-- page follows — it can never show a size that disagrees with the catalogue.
--
-- Needs migration 0006. If this errors with "Unknown column 'slot_key'",
-- run database/migrations/0006_ad_zones.sql first.

INSERT INTO `ads_prices` (`name`, `slot_key`, `size`, `cost`, `status`, `created_at`, `updated_at`)
SELECT * FROM (
    SELECT 'Top Banner (Site Wide)'  AS name,
           'top-banner-site-wide'    AS slot_key,
           '700x100'                 AS size,
           75.00                     AS cost,
           1                         AS status,
           NOW()                     AS created_at,
           NOW()                     AS updated_at
    UNION ALL SELECT 'Bottom Banner (Site Wide)', 'bottom-banner-site-wide', '900x100', 45.00, 1, NOW(), NOW()
    UNION ALL SELECT 'Middle Banner (Category)',  'middle-banner-category',  '900x100', 25.00, 1, NOW(), NOW()
    UNION ALL SELECT '#1 Right Side Banner',      'right-side-banner-1',     '300x250', 25.00, 1, NOW(), NOW()
    UNION ALL SELECT '#2 Right Side Banner',      'right-side-banner-2',     '300x250', 25.00, 1, NOW(), NOW()
    UNION ALL SELECT '#3 Right Side Banner',      'right-side-banner-3',     '300x250', 20.00, 1, NOW(), NOW()
    UNION ALL SELECT 'Right Panel Skyscraper',    'right-panel-skyscraper',  '295x600', 20.00, 1, NOW(), NOW()
) AS seed
WHERE NOT EXISTS (
    SELECT 1 FROM `ads_prices` WHERE `ads_prices`.`slot_key` = seed.slot_key
);


-- ---------------------------------------------------------------------------
-- 2. Columns for the two homepage promo packages  (= migration 0018)
-- ---------------------------------------------------------------------------
-- You already sell "Business Hotspot on Homepage" ($25/30d) and "Business Of
-- the Week" ($20/7d), and both descriptions promise homepage placement — but
-- `listings` had no field to record either, so neither could ever appear.
--
-- DATE, not a flag, so the placement expires by itself at the end of the term.

SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='listings' AND COLUMN_NAME='hotspot_until');
SET @s := IF(@e = 0, 'ALTER TABLE `listings` ADD COLUMN `hotspot_until` DATE NULL DEFAULT NULL COMMENT ''Business Hotspot on Homepage - last day the placement shows'', ADD KEY `listings_hotspot_until_idx` (`hotspot_until`)', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='listings' AND COLUMN_NAME='week_until');
SET @s := IF(@e = 0, 'ALTER TABLE `listings` ADD COLUMN `week_until` DATE NULL DEFAULT NULL COMMENT ''Business Of the Week - last day the placement shows'', ADD KEY `listings_week_until_idx` (`week_until`)', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- ---------------------------------------------------------------------------
-- 3. Switch on the content services
-- ---------------------------------------------------------------------------
-- Migration 0011 seeds these OFF. While a service is off its page 404s, so the
-- homepage hides that section rather than linking to a dead page. Turn on the
-- ones you actually publish. Also available in /admin/setting ▸ Services.

INSERT INTO `site_settings` (`setting_key`, `setting_value`)
VALUES
    ('service_news_enabled',   '1'),
    ('service_videos_enabled', '1'),
    ('service_events_enabled', '1')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);


-- ---------------------------------------------------------------------------
-- 4. Check it landed
-- ---------------------------------------------------------------------------

SELECT 'homepage ad zones (want 7)' AS what, COUNT(*) AS n
  FROM `ads_prices`
 WHERE `slot_key` IN ('top-banner-site-wide','bottom-banner-site-wide','middle-banner-category',
                      'right-side-banner-1','right-side-banner-2','right-side-banner-3',
                      'right-panel-skyscraper')
UNION ALL
SELECT 'promo columns (want 2)', COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'listings'
   AND COLUMN_NAME IN ('hotspot_until','week_until')
UNION ALL
SELECT 'services on', COUNT(*)
  FROM `site_settings`
 WHERE `setting_key` IN ('service_news_enabled','service_videos_enabled','service_events_enabled')
   AND `setting_value` = '1';


-- ---------------------------------------------------------------------------
-- 5. Try the new sections out
-- ---------------------------------------------------------------------------
-- Nothing shows in Business Hotspot / Business of the Week until a listing is
-- given a date. Swap in your own listing_ids:
--
--     UPDATE `listings` SET `hotspot_until` = DATE_ADD(CURDATE(), INTERVAL 30 DAY)
--      WHERE `listing_id` IN (1, 2, 3, 4, 5, 6);
--
--     UPDATE `listings` SET `week_until` = DATE_ADD(CURDATE(), INTERVAL 7 DAY)
--      WHERE `listing_id` = 7;
--
-- To end a placement early, set the column back to NULL.
--
-- And if Featured Businesses is empty:
--
--     SELECT COUNT(*) FROM `listings`
--      WHERE `listing_status` = 0 AND `is_deleted` = 0
--        AND (`featured_listing` = 1 OR `spotlight_listing` = 1);
--
-- `listing_status = 0` means PUBLISHED in this codebase — that is what
-- getLatestListings() and getFeaturedListings() both filter on.
-- ---------------------------------------------------------------------------
