-- ---------------------------------------------------------------------------
-- 0006_ad_zones.sql
-- ---------------------------------------------------------------------------
-- Adds a short, code-referenced `slot_key` to ads_prices so the
-- frontend can request "the ad for the home-hero slot" without
-- relying on free-text matches against the admin-edited `name`.
--
-- Seeds the canonical slot list our views reference via the new
-- ad_slot partial.
--
-- Run once after 0005:
--   mysql -u <user> -p <db> < database/migrations/0006_ad_zones.sql
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_add_slot_key;
DELIMITER //
CREATE PROCEDURE pearland411_add_slot_key()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'ads_prices'
          AND COLUMN_NAME = 'slot_key'
    ) THEN
        ALTER TABLE `ads_prices`
            ADD COLUMN `slot_key` VARCHAR(60) NULL AFTER `name`,
            ADD KEY `ads_prices_slot_key_idx` (`slot_key`);
    END IF;
END //
DELIMITER ;
CALL pearland411_add_slot_key();
DROP PROCEDURE pearland411_add_slot_key;

-- Seed the canonical slot list. Insert only if no row exists with
-- the same slot_key yet, so re-running is idempotent.
INSERT INTO `ads_prices` (`name`, `slot_key`, `size`, `cost`, `status`, `created_at`, `updated_at`)
SELECT * FROM (
    SELECT 'Homepage hero'         AS name, 'home-hero'        AS slot_key, '728x200' AS size, 99.00 AS cost, 1 AS status, NOW() AS created_at, NOW() AS updated_at UNION
    SELECT 'Homepage sidebar',           'home-sidebar',           '300x250',  49.00, 1, NOW(), NOW() UNION
    SELECT 'Listing detail sidebar',     'listing-sidebar',        '300x250',  49.00, 1, NOW(), NOW() UNION
    SELECT 'Listing list — top',         'listing-top',            '728x90',   59.00, 1, NOW(), NOW() UNION
    SELECT 'Listing list — between rows','listing-inline',         '728x90',   49.00, 1, NOW(), NOW() UNION
    SELECT 'Events page sidebar',        'events-sidebar',         '300x250',  39.00, 1, NOW(), NOW() UNION
    SELECT 'News article sidebar',       'news-sidebar',           '300x250',  39.00, 1, NOW(), NOW() UNION
    SELECT 'Footer banner',              'footer-banner',          '970x90',   29.00, 1, NOW(), NOW()
) AS seed
WHERE NOT EXISTS (
    SELECT 1 FROM `ads_prices` WHERE `ads_prices`.`slot_key` = seed.slot_key
);
