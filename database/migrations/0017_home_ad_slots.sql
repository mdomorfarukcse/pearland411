-- ---------------------------------------------------------------------------
-- 0017_home_ad_slots.sql
-- ---------------------------------------------------------------------------
-- Ad zones for the reorganised homepage.
--
-- KEY POINT: every slot_key below is the SAME STRING as the `plans.slug` of
-- the package a customer actually buys on /pricing-plan. Before this, ad zones
-- ("home-hero", "home-sidebar") and sellable packages ("top-banner-site-wide")
-- were two unrelated vocabularies, so nothing connected a purchase to a
-- position on the page. Now they line up one-to-one.
--
--   plans.slug              ads_prices.slot_key      where it renders
--   ----------------------  -----------------------  ----------------------
--   top-banner-site-wide    top-banner-site-wide     under the hero
--   middle-banner-category  middle-banner-category   mid-content
--   bottom-banner-site-wide bottom-banner-site-wide  above the footer
--   right-side-banner-1     right-side-banner-1      sidebar, top
--   right-side-banner-2     right-side-banner-2      sidebar, middle
--   right-side-banner-3     right-side-banner-3      sidebar, lower
--   right-panel-skyscraper  right-panel-skyscraper   sidebar, bottom (sticky)
--
-- SIZES: the pricing page currently lists most of these as "900px X 100px",
-- including both skyscrapers, which cannot be right — a skyscraper is tall.
-- The right-side banners are set to 300x250 here and the skyscraper to 295x600
-- (the one figure on that page that looks deliberate). Update the pricing page
-- copy to match, or edit these rows — the homepage reads the size from here and
-- reserves the box accordingly, so the two can never drift apart on-screen.
--
-- DATA ONLY: 0006 already added ads_prices.slot_key and size, so there is no
-- DDL, no stored procedure and no DELIMITER. This file and the block appended
-- to database/migrations_combined_phpmyadmin.sql are byte-identical.
--
-- Idempotent: the correlated NOT EXISTS skips any slot_key already present.
--
-- Depends on: 0006_ad_zones.sql
-- Run once after 0016:
--   mysql -u <user> -p <db> < database/migrations/0017_home_ad_slots.sql
-- ---------------------------------------------------------------------------

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
