-- ---------------------------------------------------------------------------
-- 0033_plan_catalog_corrections.sql
-- ---------------------------------------------------------------------------
-- Three corrections to the advertising catalog on /pricing-plan. The seed
-- (database/seeds/pearland411_packages.sql) is not re-run on a live database,
-- so the fixes have to arrive as a migration.
--
-- (1) Wrong sizes in the package descriptions (tracker: "Skyscraper / Right
--     panel skyscraper / Right side banner same price"). Every right-hand
--     banner was described as "Size 900x100px" — the size of the full-width
--     strips — though the zones they sell render at 300x250 (ads_prices,
--     migrations 0017 / 0019 / 0025, and docs/AD_INVENTORY.md). An advertiser
--     designing to the description made artwork that does not fit. Only rows
--     still carrying the seeded "900x100" text are touched, so a description
--     an operator has since rewritten is left alone.
--
-- (2) The "Skyscraper" package (slug `skyscraper`) has no ad zone. Every
--     plans.slug a banner package sells must equal an ads_prices.slot_key
--     (0017's rule), and no page renders a `skyscraper` slot — the 295x600
--     position is `right-panel-skyscraper`, sold separately at the same $20.
--     So it could be bought and never shown. Taken off sale (is_active = 0),
--     not deleted: any transaction that references it keeps its plan row.
--     Skipped if a `skyscraper` zone has since been created.
--
-- (3) The category header banner could not be bought. 0024 seeded the
--     `top-banner-category` ZONE (ads_prices) but no PLAN with that slug, so
--     /pricing-plan had nothing to sell for it. Seeded here in the
--     category-banners group at the zone's own price, with its 30-day base
--     term in plan_durations (Admin::syncPlanBaseTerm keeps the two in step
--     from then on). Skipped if a plan with that slug already exists.
--
-- (4) `transactions.target_category_id` — which listing category a
--     category-specific banner or video was bought for. Checkout now asks;
--     before this there was nowhere to record the answer, so a "Category
--     Specific Banner" was sold without anyone knowing which category.
--
-- Prices are deliberately NOT changed: the per-size prices are the client's
-- decision (tracker #26) and are edited at /admin/pricing-plan.
--
-- Idempotent — INFORMATION_SCHEMA guards throughout. Safe to re-run.
-- ---------------------------------------------------------------------------

-- (1) Descriptions: 300x250 for the right-hand boxes.
UPDATE `plans` SET `description` = 'Site-wide right side banner (position 1). Size 300x250px.'
 WHERE `slug` = 'right-side-banner-1' AND `description` LIKE '%900x100%';
UPDATE `plans` SET `description` = 'Site-wide right side banner (position 2). Size 300x250px.'
 WHERE `slug` = 'right-side-banner-2' AND `description` LIKE '%900x100%';
UPDATE `plans` SET `description` = 'Site-wide right side banner (position 3). Size 300x250px.'
 WHERE `slug` = 'right-side-banner-3' AND `description` LIKE '%900x100%';
UPDATE `plans` SET `description` = 'Category-specific right banner (position 1). Size 300x250px.'
 WHERE `slug` = 'right-banner-1' AND `description` LIKE '%900x100%';
UPDATE `plans` SET `description` = 'Category-specific right banner (position 2). Size 300x250px.'
 WHERE `slug` = 'right-banner-2' AND `description` LIKE '%900x100%';

-- (2) Retire the zone-less "Skyscraper" package.
UPDATE `plans` SET `is_active` = 0
 WHERE `slug` = 'skyscraper'
   AND NOT EXISTS (SELECT 1 FROM `ads_prices` WHERE `ads_prices`.`slot_key` = 'skyscraper');

-- (3) A sellable plan for the category header zone.
INSERT INTO `plans`
 (`name`,`slug`,`price`,`currency`,`billing_interval`,`duration_days`,`description`,
  `listing_limit`,`product_limit`,`event_limit`,`classified_limit`,`blog_limit`,`news_limit`,
  `video_limit`,`rental_limit`,`house_limit`,`ad_limit`,`ad_boost_limit`,`placeholder_images_limit`,
  `featured_listing`,`priority_support`,`analytics_access`,
  `is_active`,`sort_order`,`category`,`created_at`,`updated_at`)
SELECT * FROM (
    SELECT 'Category Header Banner' AS name, 'top-banner-category' AS slug, 25.00 AS price, 'USD' AS currency,
           'monthly' AS billing_interval, 30 AS duration_days,
           'Header banner across the top of one category''s directory page. Size 900x100px.' AS description,
           0 AS l1,0 AS l2,0 AS l3,0 AS l4,0 AS l5,0 AS l6,0 AS l7,0 AS l8,0 AS l9,0 AS l10,0 AS l11,0 AS l12,
           0 AS f1,0 AS f2,0 AS f3,
           1 AS is_active,
           (SELECT COALESCE(MAX(p.`sort_order`), 0) + 1 FROM `plans` p) AS sort_order,
           'category-banners' AS category, NOW() AS created_at, NOW() AS updated_at
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `plans` WHERE `plans`.`slug` = 'top-banner-category');

INSERT INTO `plan_durations` (`plan_id`,`duration_months`,`duration_days`,`price`,`currency`,`is_active`,`created_at`,`updated_at`)
SELECT p.`id`, 1, 30, p.`price`, p.`currency`, 1, NOW(), NOW()
  FROM `plans` p
 WHERE p.`slug` = 'top-banner-category'
   AND NOT EXISTS (SELECT 1 FROM `plan_durations` d WHERE d.`plan_id` = p.`id` AND d.`duration_days` = 30);

-- (4) Which category a category-specific placement was bought for.
DROP PROCEDURE IF EXISTS pearland411_add_txn_target_category;
DELIMITER //
CREATE PROCEDURE pearland411_add_txn_target_category()
BEGIN
    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions')
       AND NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions'
                          AND COLUMN_NAME = 'target_category_id') THEN
        ALTER TABLE `transactions`
            ADD COLUMN `target_category_id` INT(11) NULL DEFAULT NULL
            COMMENT 'listing_categories.category_id a category-specific banner/video was bought for';
    END IF;
END //
DELIMITER ;
CALL pearland411_add_txn_target_category();
DROP PROCEDURE pearland411_add_txn_target_category;
