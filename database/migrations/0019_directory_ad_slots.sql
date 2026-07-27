-- ---------------------------------------------------------------------------
-- 0019_directory_ad_slots.sql
-- ---------------------------------------------------------------------------
-- Ad zones for the reorganised directory (/all-listing) and listing-details
-- pages.
--
-- 0017 seeded the seven site-wide zones the homepage uses. The directory is a
-- CATEGORY page, and the package catalogue (database/seeds/pearland411_packages.sql)
-- sells category-specific banners separately and more cheaply than site-wide
-- ones — but those three had no `ads_prices` row, so nothing a customer bought
-- could ever render. Same rule as 0017: every slot_key is the SAME STRING as
-- the `plans.slug` sold on /pricing-plan.
--
--   plans.slug               ads_prices.slot_key      where it renders
--   -----------------------  -----------------------  -----------------------
--   right-banner-1           right-banner-1           directory sidebar, lower
--   right-banner-2           right-banner-2           category pages, spare
--   bottom-banner-category   bottom-banner-category   directory foot, when a
--                                                     category filter is on
--
-- The pages also render the 0017 zones (`top-banner-site-wide`,
-- `middle-banner-category`, `right-side-banner-1/2`, `right-panel-skyscraper`,
-- `bottom-banner-site-wide`), which is what "site wide" is supposed to mean —
-- until now those packages only ever appeared on the homepage.
--
-- SIZES: the packages seed still describes most banners as "900x100px",
-- including the right-hand ones, which cannot be right for a sidebar column.
-- The right banners are seeded at 300x250 to match right-side-banner-1/2/3,
-- and the category bottom banner at 900x100 to match the site-wide one. The
-- views read the size from here and reserve exactly that box, so what an
-- advertiser is quoted and what the page renders cannot drift apart.
--
-- DATA ONLY: 0006 added ads_prices.slot_key and size, so there is no DDL, no
-- stored procedure and no DELIMITER. This file and the block appended to
-- database/migrations_combined_phpmyadmin.sql are byte-identical.
--
-- Idempotent: the correlated NOT EXISTS skips any slot_key already present.
--
-- Depends on: 0006_ad_zones.sql (and logically follows 0017).
-- Run once after 0018:
--   mysql -u <user> -p <db> < database/migrations/0019_directory_ad_slots.sql
-- ---------------------------------------------------------------------------

INSERT INTO `ads_prices` (`name`, `slot_key`, `size`, `cost`, `status`, `created_at`, `updated_at`)
SELECT * FROM (
    SELECT '#1 Right Banner (Category)' AS name,
           'right-banner-1'             AS slot_key,
           '300x250'                    AS size,
           25.00                        AS cost,
           1                            AS status,
           NOW()                        AS created_at,
           NOW()                        AS updated_at
    UNION ALL SELECT '#2 Right Banner (Category)',  'right-banner-2',         '300x250', 25.00, 1, NOW(), NOW()
    UNION ALL SELECT 'Bottom Banner (Category)',    'bottom-banner-category', '900x100', 25.00, 1, NOW(), NOW()
) AS seed
WHERE NOT EXISTS (
    SELECT 1 FROM `ads_prices` WHERE `ads_prices`.`slot_key` = seed.slot_key
);
