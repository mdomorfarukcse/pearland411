-- ---------------------------------------------------------------------------
-- 0025_ad_zone_reconciliation.sql
-- ---------------------------------------------------------------------------
-- **Moves ads booked against dead positions onto positions that render, and
-- takes the dead positions out of circulation.**
--
-- BEFORE YOU RUN THIS: run `database/ad_zone_audit.sql` first. It changes
-- nothing and tells you exactly what this file is about to touch — most
-- importantly, whether anyone has paid for a zone that renders nowhere.
--
-- THE PROBLEM. A page asks for a zone by `ads_prices.slot_key`. The rows made
-- by hand before the 0006 migration have no slot_key at all — 'Above Footer
-- Left', 'Feature Business Right 1', 'Home Page Below Slider left' and the
-- rest. The six `Usermodel::get*Ads()` methods that once rendered them are
-- dead code called by no view. So those rows are inventory that:
--
--   * appears in the admin booking dropdown, indistinguishable from a real
--     position (fixed separately in views/admin/create_ads.php);
--   * can be paid for, dated and approved; and
--   * renders on zero pages, with nothing anywhere to say why.
--
-- WHAT THIS DOES.
--   1. Repoints every booking on a dead zone onto the equivalent live zone,
--      so an ad someone already paid for starts appearing.
--   2. Deactivates the dead zones so nothing new can be booked against them.
--      They are NOT deleted — the rows stay for your records, and every
--      booking keeps its own `ad_cost_per_day` / `ad_total_cost`, so what a
--      customer was charged is untouched.
--   3. Deactivates `footer-banner`, which has a slot_key but is no longer
--      included by any view (see the note in views/footer.php) and matches no
--      package on /pricing-plan, so nobody could buy it anyway.
--   4. Backfills `ads_prices.size` where it is missing, so a zone reserves the
--      right box instead of falling back to a guessed 3:1 shape.
--   5. Seeds `ads_house_max_per_page`, the cap on how many unsold zones a page
--      may fill with a house "advertise here" banner.
--
-- CHECK THE MAPPING IN STEP 1 BEFORE RUNNING. It is written for the six legacy
-- names this codebase references by name. If your `ads_prices` has others, the
-- audit's query 1 lists them, and they will simply be deactivated by step 2
-- rather than repointed — so nothing is lost, but a live ad on one would stop
-- being bookable. Add a row to the mapping for anything you want carried over.
--
-- Idempotent — re-running repoints nothing (the sources are already inactive
-- and empty) and re-deactivates rows that are already inactive. Safe to re-run.
--
-- Depends on: 0006 (slot_key), 0007 (site_settings), 0017 / 0019 / 0024 (the
-- canonical zones). Running it before those is a harmless no-op: the joins
-- find no target and update zero rows.
--
-- Run once after 0024:
--   mysql -u <user> -p <db> < database/migrations/0025_ad_zone_reconciliation.sql
-- ---------------------------------------------------------------------------

-- ===========================================================================
-- 1. Repoint bookings from dead zones onto the equivalent live zone
-- ===========================================================================
-- Read the mapping as "an ad bought for THIS should now run in THAT".
--   Home Page Below Slider left   the band under the homepage hero  -> top banner
--   Home Page Below Slider right  its right-hand twin; today one    -> mid banner
--                                 full-width band sits there
--   Feature Business Right 1/2/3  the three sidebar boxes           -> sidebar 1/2/3
--   Above Footer Left             the banner above the footer       -> page-foot banner
--   Above Footer Right            its twin; the foot carries one    -> category foot banner
--                                 banner now, so this goes to the
--                                 category-priced one
--
-- MIN(id) on the target: if a slot_key somehow appears twice, pick one and be
-- deterministic rather than fail or fan out.
UPDATE `ads` a
JOIN `ads_prices` src ON src.id = a.ads_price_id
JOIN (
    SELECT 'Home Page Below Slider left'  AS legacy_name, 'top-banner-site-wide'    AS slot_key
    UNION ALL SELECT 'Home Page Below Slider right', 'middle-banner-category'
    UNION ALL SELECT 'Feature Business Right 1',     'right-side-banner-1'
    UNION ALL SELECT 'Feature Business Right 2',     'right-side-banner-2'
    UNION ALL SELECT 'Feature Business Right 3',     'right-side-banner-3'
    UNION ALL SELECT 'Above Footer Left',            'bottom-banner-site-wide'
    UNION ALL SELECT 'Above Footer Right',           'bottom-banner-category'
) map ON map.legacy_name = src.name
JOIN (
    SELECT slot_key, MIN(id) AS id
    FROM `ads_prices`
    WHERE slot_key IS NOT NULL AND slot_key <> ''
    GROUP BY slot_key
) dst ON dst.slot_key = map.slot_key
SET a.ads_price_id = dst.id
WHERE (src.slot_key IS NULL OR src.slot_key = '')
  AND a.ads_price_id <> dst.id;


-- ===========================================================================
-- 2. Take every remaining dead zone out of circulation
-- ===========================================================================
-- No slot_key means no page can ask for it. Deactivated, not deleted: the row
-- stays for your records and can be revived by giving it a position code at
-- /admin/ads-price-edit (the form offers that field while it is blank).
UPDATE `ads_prices`
   SET `status` = 0
 WHERE (`slot_key` IS NULL OR `slot_key` = '')
   AND `status` = 1;


-- ===========================================================================
-- 3. Retire the footer strip
-- ===========================================================================
-- It has a slot_key, so the admin would show it as live — but no view includes
-- it any more. It sat directly under `bottom-banner-site-wide`, making the
-- foot of every page two wide banners stacked, and its slot_key matches no
-- `plans.slug`, so it was never purchasable in the first place.
--
-- To bring it back: restore the include in views/footer.php, add a matching
-- package on /pricing-plan, then set status = 1 here.
UPDATE `ads_prices`
   SET `status` = 0
 WHERE `slot_key` = 'footer-banner'
   AND `status` = 1;


-- ===========================================================================
-- 4. Backfill any missing pixel box
-- ===========================================================================
-- `size` is what a page emits as width/height so the browser reserves the box
-- before the creative paints. An empty one makes ad_slot.php guess a 3:1
-- shape, which is how a banner ends up in a box the wrong shape for it.
UPDATE `ads_prices` SET `size` = '700x100' WHERE `slot_key` = 'top-banner-site-wide'    AND (`size` IS NULL OR `size` = '');
UPDATE `ads_prices` SET `size` = '900x100' WHERE `slot_key` = 'middle-banner-category'  AND (`size` IS NULL OR `size` = '');
UPDATE `ads_prices` SET `size` = '900x100' WHERE `slot_key` = 'bottom-banner-site-wide' AND (`size` IS NULL OR `size` = '');
UPDATE `ads_prices` SET `size` = '900x100' WHERE `slot_key` = 'top-banner-category'     AND (`size` IS NULL OR `size` = '');
UPDATE `ads_prices` SET `size` = '900x100' WHERE `slot_key` = 'bottom-banner-category'  AND (`size` IS NULL OR `size` = '');
UPDATE `ads_prices` SET `size` = '300x250' WHERE `slot_key` IN ('right-side-banner-1', 'right-side-banner-2', 'right-side-banner-3', 'right-banner-1', 'right-banner-2') AND (`size` IS NULL OR `size` = '');
UPDATE `ads_prices` SET `size` = '295x600' WHERE `slot_key` = 'right-panel-skyscraper'  AND (`size` IS NULL OR `size` = '');
UPDATE `ads_prices` SET `size` = '970x90'  WHERE `slot_key` = 'footer-banner'           AND (`size` IS NULL OR `size` = '');


-- ===========================================================================
-- 5. The house-ad cap
-- ===========================================================================
-- How many unsold zones on one page may fall back to a house "advertise here"
-- banner. Every zone doing so meant a page of house ads and no advertisers,
-- which reads as "nobody advertises here" rather than as premium inventory.
-- The rest render nothing at all.
--
--   2   the default, and what this seeds
--   0   never show a house ad
--  -1   no cap (the behaviour before this change)
--
-- A SOLD ad is never affected. Editable at /admin/setting -> Advertising.
-- INSERT IGNORE, so an operator who has already tuned it keeps their value.
INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`, `updated_at`)
VALUES ('ads_house_max_per_page', '2', NOW());


-- ===========================================================================
-- 6. Confirm what changed
-- ===========================================================================
-- Re-run database/ad_zone_audit.sql afterwards. Query 2 should come back
-- empty: nobody is paying for a position that renders nowhere.
SELECT
    COALESCE(NULLIF(`slot_key`, ''), '(no position code)') AS slot_key,
    `name`,
    `size`,
    CASE `status` WHEN 1 THEN 'active' ELSE 'inactive' END AS status,
    (SELECT COUNT(*) FROM `ads` a WHERE a.ads_price_id = ads_prices.id) AS bookings
FROM `ads_prices`
ORDER BY `status` DESC, `slot_key`;
