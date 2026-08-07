-- ---------------------------------------------------------------------------
-- ad_zone_audit.sql — READ ONLY. Changes nothing. Run this first.
-- ---------------------------------------------------------------------------
-- Answers "which of my ad positions actually render, and where did the money
-- go?" before 0025_ad_zone_reconciliation.sql changes anything.
--
--   mysql -u <user> -p <db> < database/ad_zone_audit.sql
--
-- or paste the four blocks into phpMyAdmin one at a time.
--
-- Every SELECT here is safe to run on production, at any time, as often as
-- you like. There is no UPDATE, INSERT or DELETE in this file.
--
-- WHAT MAKES A ZONE RENDER. Three things have to line up:
--   1. `ads_prices.slot_key` — the position code a page asks for by name.
--      NULL or '' means no page can ever request it.
--   2. `ads_prices.status` = 1.
--   3. A view that includes that slot_key. The full list is in
--      docs/AD_INVENTORY.md.
-- A booking against a row that fails (1) is paid for, dated, approved — and
-- appears on zero pages, with nothing anywhere to say why.
-- ---------------------------------------------------------------------------


-- ===========================================================================
-- 1. Every ad position, and whether a page can render it
-- ===========================================================================
SELECT
    ap.id,
    ap.name                                        AS zone_name,
    COALESCE(NULLIF(ap.slot_key, ''), '(none)')    AS slot_key,
    COALESCE(NULLIF(ap.size, ''), '(none)')        AS size,
    ap.cost                                        AS cost_per_day,
    CASE ap.status WHEN 1 THEN 'active' ELSE 'inactive' END AS status,
    CASE
        WHEN ap.slot_key IS NULL OR ap.slot_key = ''
            THEN 'DEAD — no position code, renders nowhere'
        WHEN ap.slot_key IN (
                'top-banner-site-wide', 'middle-banner-category',
                'bottom-banner-site-wide', 'right-side-banner-1',
                'right-side-banner-2', 'right-side-banner-3',
                'right-panel-skyscraper', 'top-banner-category',
                'bottom-banner-category', 'right-banner-1', 'right-banner-2')
            THEN 'LIVE — rendered by the public pages'
        WHEN ap.slot_key = 'footer-banner'
            THEN 'RETIRED — the footer strip was removed (see footer.php)'
        ELSE 'LEGACY — reachable only as a $slot_alias fallback'
    END                                            AS renders,
    (SELECT COUNT(*) FROM ads a WHERE a.ads_price_id = ap.id)  AS bookings_total,
    (SELECT COUNT(*) FROM ads a
      WHERE a.ads_price_id = ap.id
        AND a.ad_status = 1
        AND a.ad_start_date <= NOW()
        AND a.ad_end_date   >= NOW())              AS bookings_live_now
FROM ads_prices ap
ORDER BY
    (ap.slot_key IS NULL OR ap.slot_key = '') DESC,
    ap.status DESC,
    ap.name;


-- ===========================================================================
-- 2. Money already spent on positions that render nowhere
-- ===========================================================================
-- If this returns rows, those advertisers are paying for nothing. 0025
-- repoints them onto the equivalent live position.
SELECT
    ap.name                       AS booked_zone,
    COUNT(*)                      AS bookings,
    SUM(a.ad_total_cost)          AS total_paid,
    MIN(a.ad_start_date)          AS earliest,
    MAX(a.ad_end_date)            AS latest,
    SUM(CASE WHEN a.ad_status = 1
              AND a.ad_end_date >= NOW() THEN 1 ELSE 0 END) AS still_running
FROM ads a
JOIN ads_prices ap ON ap.id = a.ads_price_id
WHERE ap.slot_key IS NULL OR ap.slot_key = ''
GROUP BY ap.id, ap.name
ORDER BY still_running DESC, total_paid DESC;


-- ===========================================================================
-- 3. Ads that will not render for a reason other than the zone
-- ===========================================================================
-- The zone is fine; the booking is not. Worth checking before blaming the
-- code — "my ad isn't showing" is usually one of these three.
SELECT
    a.id                          AS ad_id,
    ap.name                       AS zone_name,
    a.ad_start_date,
    a.ad_end_date,
    CASE
        WHEN a.ad_status <> 1                  THEN 'awaiting approval at /admin/ads-request'
        WHEN a.ad_start_date > NOW()           THEN 'starts in the future'
        WHEN a.ad_end_date   < NOW()           THEN 'already expired'
        WHEN a.ad_photo IS NULL OR a.ad_photo = '' THEN 'no artwork uploaded'
        ELSE 'running'
    END                           AS why_not
FROM ads a
JOIN ads_prices ap ON ap.id = a.ads_price_id
WHERE ap.slot_key IS NOT NULL AND ap.slot_key <> ''
  AND (a.ad_status <> 1
       OR a.ad_start_date > NOW()
       OR a.ad_end_date   < NOW()
       OR a.ad_photo IS NULL OR a.ad_photo = '')
ORDER BY a.ad_end_date DESC;


-- ===========================================================================
-- 4. What a package buys, and whether the position exists to sell
-- ===========================================================================
-- `plans.slug` and `ads_prices.slot_key` must be the SAME STRING. A package
-- whose slug matches no zone is sellable but unfillable; a zone matching no
-- package renders but cannot be bought.
SELECT
    p.slug                        AS plan_slug,
    p.name                        AS plan_name,
    ap.slot_key                   AS matching_zone,
    CASE WHEN ap.id IS NULL
         THEN 'NO ZONE — this package can be bought but never renders'
         ELSE 'ok' END            AS state
FROM plans p
LEFT JOIN ads_prices ap ON ap.slot_key = p.slug
WHERE p.slug LIKE '%banner%' OR p.slug LIKE '%skyscraper%' OR ap.id IS NOT NULL
ORDER BY state DESC, p.slug;
