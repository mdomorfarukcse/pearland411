-- ---------------------------------------------------------------------------
-- 0017_home_ad_slots.sql
-- ---------------------------------------------------------------------------
-- Five extra ad zones for the redesigned homepage.
--
-- DATA ONLY — no DDL. `ads_prices.slot_key` and `ads_prices.size` are both
-- added by 0006, so there is nothing to ALTER here and therefore no stored
-- procedure and no DELIMITER. That means this file and the block appended to
-- database/migrations_combined_phpmyadmin.sql are byte-identical.
--
-- Idempotent: the correlated NOT EXISTS skips any slot_key already present,
-- so re-running is a no-op.
--
-- Depends on: 0006_ad_zones.sql
--
-- Run once after 0016:
--   mysql -u <user> -p <db> < database/migrations/0017_home_ad_slots.sql
--
-- Zones added (all rendered by application/views/partials/ad_slot.php):
--   home-top-leaderboard      970x90   under the hero, above the fold
--   home-sidebar-top          300x250  first unit in the sidebar
--   home-inline-billboard     728x250  between the video and news rows
--   home-sidebar-skyscraper   300x600  sticky, bottom of the sidebar
--   home-footer-billboard     970x250  last unit before the footer
--
-- The last two are included on the homepage with $slot_fallback = false, so
-- they stay invisible until an ad is actually sold against them.
--
-- Costs are a starting point — edit them in /admin ▸ Ads ▸ Ad Pricing, or with
-- a plain UPDATE. The homepage rate card reads these rows directly, so the
-- page can never advertise a price that disagrees with the catalog.
-- ---------------------------------------------------------------------------

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
