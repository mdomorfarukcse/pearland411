-- ===========================================================================
-- homepage_ad_zones_only.sql
-- ===========================================================================
-- The MINIMUM the redesigned homepage needs, for a site that already has its
-- own listings. Paste this whole file into phpMyAdmin ▸ SQL and run it.
--
-- It touches exactly two tables:
--     ads_prices      — adds 5 homepage ad zones
--     site_settings   — switches on News / Videos / Events
--
-- It does NOT insert listings, categories, news, videos, coupons or events.
-- Nothing existing is modified or deleted. Safe to run more than once.
--
-- (database/seeds/pearland_sample_content.sql is the other file — that one
--  DOES add demo businesses. Do not run it if you already have real data.)
-- ===========================================================================

SET NAMES utf8mb4;


-- ---------------------------------------------------------------------------
-- 1. Ad zones  (identical to database/migrations/0017_home_ad_slots.sql)
-- ---------------------------------------------------------------------------
-- Without these rows the five new slots render nothing and the
-- "Advertise on Pearland411" band shows its empty state.
--
-- Prices are a starting point. The homepage rate card reads these rows
-- directly, so edit them here (or in /admin ▸ Ads ▸ Ad Pricing) and the page
-- updates itself — it can never advertise a price that disagrees with the
-- catalogue.
--
-- Requires migration 0006 (adds ads_prices.slot_key). If this errors with
-- "Unknown column 'slot_key'", run database/migrations/0006_ad_zones.sql first.

INSERT INTO `ads_prices` (`name`, `slot_key`, `size`, `cost`, `status`, `created_at`, `updated_at`)
SELECT * FROM (
    SELECT 'Homepage top leaderboard'      AS name,
           'home-top-leaderboard'          AS slot_key,
           '970x90'                        AS size,
           120.00                          AS cost,
           1                               AS status,
           NOW()                           AS created_at,
           NOW()                           AS updated_at
    UNION ALL SELECT 'Homepage sidebar — top',        'home-sidebar-top',        '300x250',  69.00, 1, NOW(), NOW()
    UNION ALL SELECT 'Homepage in-content billboard', 'home-inline-billboard',   '728x250',  79.00, 1, NOW(), NOW()
    UNION ALL SELECT 'Homepage sidebar skyscraper',   'home-sidebar-skyscraper', '300x600',  89.00, 1, NOW(), NOW()
    UNION ALL SELECT 'Homepage footer billboard',     'home-footer-billboard',   '970x250',  59.00, 1, NOW(), NOW()
) AS seed
WHERE NOT EXISTS (
    SELECT 1 FROM `ads_prices` WHERE `ads_prices`.`slot_key` = seed.slot_key
);


-- ---------------------------------------------------------------------------
-- 2. Switch on the content services
-- ---------------------------------------------------------------------------
-- Migration 0011 seeds these OFF. While a service is off its page 404s, so
-- the homepage deliberately hides that whole section rather than linking to a
-- dead page. Turn on the ones you actually publish.
--
-- Drop any line you don't want. You can also do this in /admin/setting ▸ Services.

INSERT INTO `site_settings` (`setting_key`, `setting_value`)
VALUES
    ('service_news_enabled',   '1'),
    ('service_videos_enabled', '1'),
    ('service_events_enabled', '1')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);


-- ---------------------------------------------------------------------------
-- 3. Check the result
-- ---------------------------------------------------------------------------

SELECT 'ad zones (want 13)' AS what, COUNT(*) AS n
  FROM `ads_prices` WHERE `slot_key` IS NOT NULL AND `slot_key` <> ''
UNION ALL
SELECT 'services on', COUNT(*)
  FROM `site_settings`
 WHERE `setting_key` IN ('service_news_enabled','service_videos_enabled','service_events_enabled')
   AND `setting_value` = '1';


-- ---------------------------------------------------------------------------
-- 4. Why is my Featured Businesses row empty?
-- ---------------------------------------------------------------------------
-- Run this. If it returns 0, no listing is flagged featured or spotlight, and
-- the homepage correctly shows its "no featured businesses yet" state.
--
--     SELECT COUNT(*) AS featured_now
--       FROM `listings`
--      WHERE `listing_status` = 0 AND `is_deleted` = 0
--        AND (`featured_listing` = 1 OR `spotlight_listing` = 1);
--
-- Note `listing_status = 0` means PUBLISHED in this codebase — that is what
-- getLatestListings() and getFeaturedListings() both filter on. If the count
-- above is 0 but you know you have featured listings, check whether yours use
-- listing_status = 1 instead; eleven other queries in Usermodel do.
--
-- To feature three businesses for testing (swap in your own ids):
--
--     UPDATE `listings` SET `featured_listing` = 1 WHERE `listing_id` IN (1,2,3);
-- ---------------------------------------------------------------------------
