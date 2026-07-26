-- ---------------------------------------------------------------------------
-- seeds/pearland411_packages.sql
-- ---------------------------------------------------------------------------
-- Replaces the placeholder pricing plans (Basic $29 / Standard $59 /
-- Premium $99) with the real Pearland411 advertising catalog (22 packages),
-- exactly as it appeared on the "Advertise with us" page:
--
--   Business Listing ..... Basic (Free), Advanced ($15), Featured ($25)
--   Add-ons .............. Business of the Week ($20/7d), Business Hotspot
--                          ($25), Add More Subcategories ($5)
--   Site Wide Banners .... Top ($75), Bottom ($45), #1 Right ($25),
--                          Skyscraper ($20), #2 Right ($25), #3 Right ($20),
--                          Right Panel Skyscraper ($20)
--   Category Banners ..... Middle ($25), Bottom ($25), #1 Right ($25),
--                          #2 Right ($25)
--   Site Wide Videos ..... Right Video #1 ($35), Right Video #2 ($30)
--   Category Videos ...... Right Video #1 ($20), Right Video #2 ($20)
--   Special Package ...... Pearland411 Special Package ($230)
--
-- This is a DATA seed (not part of the numbered migration run order). It is
-- self-contained, idempotent, and phpMyAdmin-safe: it guard-adds the `category`
-- column (so it works even if 0014 has not been applied), hard-wipes the two
-- plan tables (the system has no live subscribers yet), and re-inserts the full
-- catalog. Re-running converges to the same 22-row catalog every time.
--
--   Import via phpMyAdmin  -> Import -> Choose File -> Go, or:
--   mysql -u <user> -p <db> < database/seeds/pearland411_packages.sql
-- ---------------------------------------------------------------------------

SET SQL_MODE = '';
SET FOREIGN_KEY_CHECKS = 0;

-- (a) Guarded add of `category` (mirrors migration 0014; inline PREPARE so
--     phpMyAdmin parses it cleanly and the seed is standalone).
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans'
             AND COLUMN_NAME = 'category');
SET @s := IF(@c = 0,
  'ALTER TABLE `plans` ADD COLUMN `category` VARCHAR(50) NOT NULL DEFAULT ''business-listing'' AFTER `sort_order`',
  'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- (b) Clean wipe (no live subscribers). Makes the seed fully re-runnable.
DELETE FROM `plan_durations`;
DELETE FROM `plans`;
ALTER TABLE `plans` AUTO_INCREMENT = 1;
ALTER TABLE `plan_durations` AUTO_INCREMENT = 1;

-- (c) The 22 packages. Explicit ids 1..22 (deterministic; lets plan_durations
--     reference known ids with no LAST_INSERT_ID mapping). sort_order keeps each
--     category contiguous so the pricing pages render clean section headers.
INSERT INTO `plans`
 (`id`,`name`,`slug`,`price`,`currency`,`billing_interval`,`duration_days`,`description`,
  `listing_limit`,`product_limit`,`event_limit`,`classified_limit`,`blog_limit`,`news_limit`,
  `video_limit`,`rental_limit`,`house_limit`,`ad_limit`,`ad_boost_limit`,`placeholder_images_limit`,
  `featured_listing`,`priority_support`,`analytics_access`,
  `can_photos`,`photo_limit`,`can_map`,`can_services`,`can_extra_details`,`allow_video`,
  `is_active`,`sort_order`,`category`,`created_at`,`updated_at`)
VALUES
-- ===== Business Listing =====
 (1,'Basic Listing','basic-listing',0.00,'USD','monthly',30,
  'FREE listing with minimum details. Appears in search.',
  1,0,0,0,0,0,0,0,0,0,0,0, 0,0,0, 0,0,0,0,0,0, 1,1,'business-listing',NOW(),NOW()),
 (2,'Advanced Listing','advanced-listing',15.00,'USD','monthly',30,
  'Logo/Picture (120x120), appears in one subcategory, Google address map, website link, and extra details (who, what, when).',
  1,0,0,0,0,0,0,0,0,0,0,0, 0,0,0, 1,5,1,1,1,0, 1,2,'business-listing',NOW(),NOW()),
 (3,'Featured Listing','featured-listing',25.00,'USD','monthly',30,
  'Everything in Advanced plus your listing changes color wherever it appears and your logo is displayed in the listing.',
  1,0,0,0,0,0,0,0,0,0,0,0, 1,0,0, 1,10,1,1,1,1, 1,3,'business-listing',NOW(),NOW()),
-- ===== Add-ons =====
 (4,'Business Of the Week Listing','business-of-the-week',20.00,'USD','monthly',7,
  'Your business listed on the homepage for a week for extra exposure.',
  0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0, 0,0,0,0,0,0, 1,4,'add-ons',NOW(),NOW()),
 (5,'Business Hotspot on Homepage','business-hotspot',25.00,'USD','monthly',30,
  'Your business listed on the homepage for a month.',
  0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0, 0,0,0,0,0,0, 1,5,'add-ons',NOW(),NOW()),
 (6,'Add More Subcategories','add-subcategory',5.00,'USD','monthly',30,
  'List your business in each additional relevant subcategory (for example life, auto, and home insurance).',
  0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0, 0,0,0,0,0,0, 1,6,'add-ons',NOW(),NOW()),
-- ===== Site Wide Banners =====
 (7,'Top Banner (Site Wide)','top-banner-site-wide',75.00,'USD','monthly',30,
  'Site-wide top banner. Size 700x100px. Formats: jpg, gif, png, swf.',
  0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0, 0,0,0,0,0,0, 1,7,'site-wide-banners',NOW(),NOW()),
 (8,'Bottom Banner (Site Wide)','bottom-banner-site-wide',45.00,'USD','monthly',30,
  'Site-wide bottom banner. Size 900x100px. Formats: jpg, gif, png, swf.',
  0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0, 0,0,0,0,0,0, 1,8,'site-wide-banners',NOW(),NOW()),
 (9,'#1 Right Side Banner','right-side-banner-1',25.00,'USD','monthly',30,
  'Site-wide right side banner (position 1). Size 900x100px.',
  0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0, 0,0,0,0,0,0, 1,9,'site-wide-banners',NOW(),NOW()),
 (10,'Skyscraper','skyscraper',20.00,'USD','monthly',30,
  'Site-wide skyscraper banner. Size 900x100px.',
  0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0, 0,0,0,0,0,0, 1,10,'site-wide-banners',NOW(),NOW()),
 (11,'#2 Right Side Banner','right-side-banner-2',25.00,'USD','monthly',30,
  'Site-wide right side banner (position 2). Size 900x100px.',
  0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0, 0,0,0,0,0,0, 1,11,'site-wide-banners',NOW(),NOW()),
 (12,'#3 Right Side Banner','right-side-banner-3',20.00,'USD','monthly',30,
  'Site-wide right side banner (position 3). Size 900x100px.',
  0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0, 0,0,0,0,0,0, 1,12,'site-wide-banners',NOW(),NOW()),
 (13,'Right Panel Skyscraper','right-panel-skyscraper',20.00,'USD','monthly',30,
  'Right panel skyscraper. Size 295x600px.',
  0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0, 0,0,0,0,0,0, 1,13,'site-wide-banners',NOW(),NOW()),
-- ===== Category Specific Banners =====
 (14,'Middle Banner','middle-banner-category',25.00,'USD','monthly',30,
  'Category-specific middle banner. Size 900x100px.',
  0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0, 0,0,0,0,0,0, 1,14,'category-banners',NOW(),NOW()),
 (15,'Bottom Banner (Category)','bottom-banner-category',25.00,'USD','monthly',30,
  'Category-specific bottom banner. Size 900x100px.',
  0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0, 0,0,0,0,0,0, 1,15,'category-banners',NOW(),NOW()),
 (16,'#1 Right Banner','right-banner-1',25.00,'USD','monthly',30,
  'Category-specific right banner (position 1). Size 900x100px.',
  0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0, 0,0,0,0,0,0, 1,16,'category-banners',NOW(),NOW()),
 (17,'#2 Right Banner','right-banner-2',25.00,'USD','monthly',30,
  'Category-specific right banner (position 2). Size 900x100px.',
  0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0, 0,0,0,0,0,0, 1,17,'category-banners',NOW(),NOW()),
-- ===== Site Wide Videos =====
 (18,'Right Video #1 (Site Wide)','right-video-1-site-wide',35.00,'USD','monthly',30,
  'Site-wide right video (position 1). YouTube video under 5 minutes.',
  0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0, 0,0,0,0,0,0, 1,18,'site-wide-videos',NOW(),NOW()),
 (19,'Right Video #2 (Site Wide)','right-video-2-site-wide',30.00,'USD','monthly',30,
  'Site-wide right video (position 2). YouTube video under 5 minutes.',
  0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0, 0,0,0,0,0,0, 1,19,'site-wide-videos',NOW(),NOW()),
-- ===== Category Specific Videos =====
 (20,'Right Video #1 (Category)','right-video-1-category',20.00,'USD','monthly',30,
  'Category-specific right video (position 1). YouTube video under 5 minutes.',
  0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0, 0,0,0,0,0,0, 1,20,'category-videos',NOW(),NOW()),
 (21,'Right Video #2 (Category)','right-video-2-category',20.00,'USD','monthly',30,
  'Category-specific right video (position 2). YouTube video under 5 minutes.',
  0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0, 0,0,0,0,0,0, 1,21,'category-videos',NOW(),NOW()),
-- ===== Special Package =====
 (22,'Pearland411 Special Package','pearland411-special-package',230.00,'USD','monthly',30,
  'All-in-one package: top banner site-wide, business of the week for one month, right side banner, featured listing, coupon posting, category-specific right side video, additional subcategory listing, plus a FREE business hotspot (25 USD value).',
  -1,0,0,0,0,0,0,0,0,0,0,0, 1,1,1, 1,-1,1,1,1,1, 1,22,'special',NOW(),NOW());

-- (d) One duration row per package (30-day term; 7-day for Business of the
--     Week). is_active=1 is REQUIRED: Usermodel::getPlanDurations() filters on
--     it, so without it the checkout duration dropdown would be empty.
--     duration_months = 1 for 30-day terms, 0 for the 7-day term.
INSERT INTO `plan_durations`
 (`plan_id`,`duration_months`,`duration_days`,`price`,`currency`,`is_active`,`created_at`,`updated_at`)
VALUES
 (1,1,30,0.00,'USD',1,NOW(),NOW()),
 (2,1,30,15.00,'USD',1,NOW(),NOW()),
 (3,1,30,25.00,'USD',1,NOW(),NOW()),
 (4,0,7,20.00,'USD',1,NOW(),NOW()),
 (5,1,30,25.00,'USD',1,NOW(),NOW()),
 (6,1,30,5.00,'USD',1,NOW(),NOW()),
 (7,1,30,75.00,'USD',1,NOW(),NOW()),
 (8,1,30,45.00,'USD',1,NOW(),NOW()),
 (9,1,30,25.00,'USD',1,NOW(),NOW()),
 (10,1,30,20.00,'USD',1,NOW(),NOW()),
 (11,1,30,25.00,'USD',1,NOW(),NOW()),
 (12,1,30,20.00,'USD',1,NOW(),NOW()),
 (13,1,30,20.00,'USD',1,NOW(),NOW()),
 (14,1,30,25.00,'USD',1,NOW(),NOW()),
 (15,1,30,25.00,'USD',1,NOW(),NOW()),
 (16,1,30,25.00,'USD',1,NOW(),NOW()),
 (17,1,30,25.00,'USD',1,NOW(),NOW()),
 (18,1,30,35.00,'USD',1,NOW(),NOW()),
 (19,1,30,30.00,'USD',1,NOW(),NOW()),
 (20,1,30,20.00,'USD',1,NOW(),NOW()),
 (21,1,30,20.00,'USD',1,NOW(),NOW()),
 (22,1,30,230.00,'USD',1,NOW(),NOW());

SET FOREIGN_KEY_CHECKS = 1;
