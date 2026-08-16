-- ===========================================================================
-- Pearland411 — combined migrations 0000..0006 (phpMyAdmin v2)
-- ===========================================================================
-- USE THIS FILE WHEN IMPORTING THROUGH phpMyAdmin.
--
-- The previous version used CREATE PROCEDURE blocks for idempotency
-- guards. phpMyAdmin's Import tab splits SQL on the `;` character, so
-- the semicolons inside BEGIN...END procedure bodies broke the parse
-- (#1064 errors). This rewrite uses inline prepared statements
-- instead — every column / index / unique-key add is wrapped in an
-- IF()-based dynamic SQL block that becomes a no-op if the target
-- already exists. No procedures, no DELIMITER directives.
--
-- IMPORT STEPS (phpMyAdmin):
--   1. Take a full backup of `pearlan_new` first.
--   2. Select the `pearlan_new` database in the left rail.
--   3. Import tab → Choose File → pick this file → Go.
--
-- IMPORT STEPS (mysql CLI alternative):
--   mysql -u <user> -p pearlan_new < migrations_combined_phpmyadmin.sql
--
-- RE-RUN SAFE: every section is idempotent. Re-importing on an
-- already-migrated database succeeds with zero errors.
-- ===========================================================================

SET SQL_MODE = '';
SET FOREIGN_KEY_CHECKS = 0;


-- ============================================================
-- 0000_schema_fixups.sql
-- ============================================================

-- 1. Widen admin password column to hold bcrypt hashes.
ALTER TABLE `admin`
    MODIFY COLUMN `admin_password` VARCHAR(255) DEFAULT NULL;

-- 2. Add `banned` to admin (idempotent).
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME   = 'admin'
             AND COLUMN_NAME  = 'banned');
SET @s := IF(@c = 0,
    'ALTER TABLE `admin` ADD COLUMN `banned` TINYINT(1) NOT NULL DEFAULT 0 AFTER `admin_type`',
    'DO 0');
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Widen user.email_id to 255 chars.
ALTER TABLE `user`
    MODIFY COLUMN `email_id` VARCHAR(255) DEFAULT NULL;

-- 4. Add UNIQUE index on user.email_id (idempotent + safe when
--    existing data already has duplicate emails). Falls back to a
--    plain non-unique index when duplicates are present so the rest
--    of the migration succeeds; promote to UNIQUE manually after
--    cleaning the dupes (see README).
SET @uq_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = DATABASE()
                     AND TABLE_NAME   = 'user'
                     AND INDEX_NAME   = 'user_email_id_uq');
SET @idx_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME   = 'user'
                      AND INDEX_NAME   = 'user_email_id_idx');
SET @dupe_count := (SELECT COUNT(*) FROM (
    SELECT 1 FROM `user` WHERE email_id IS NOT NULL
     GROUP BY email_id HAVING COUNT(*) > 1
) AS d);
SET @s := CASE
    WHEN @uq_exists > 0 OR @idx_exists > 0 THEN 'DO 0'
    WHEN @dupe_count = 0 THEN 'ALTER TABLE `user` ADD UNIQUE KEY `user_email_id_uq` (`email_id`)'
    ELSE 'ALTER TABLE `user` ADD INDEX `user_email_id_idx` (`email_id`)'
END;
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5. Hot-path index on listings(listing_slug) (idempotent).
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME   = 'listings'
             AND INDEX_NAME   = 'listings_listing_slug_idx');
SET @s := IF(@c = 0,
    'ALTER TABLE `listings` ADD INDEX `listings_listing_slug_idx` (`listing_slug`(191))',
    'DO 0');
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


-- ============================================================
-- 0001_auth_flows.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS `password_resets` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    BIGINT UNSIGNED NOT NULL,
    `email`      VARCHAR(255)    NOT NULL,
    `token_hash` CHAR(64)        NOT NULL,
    `created_at` DATETIME        NOT NULL,
    `expires_at` DATETIME        NOT NULL,
    `used_at`    DATETIME        NULL DEFAULT NULL,
    `ip`         VARCHAR(45)     NULL,
    PRIMARY KEY (`id`),
    KEY `password_resets_token_idx` (`token_hash`),
    KEY `password_resets_user_idx`  (`user_id`),
    KEY `password_resets_email_idx` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- user.email_verified
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user' AND COLUMN_NAME = 'email_verified');
SET @s := IF(@c = 0,
    'ALTER TABLE `user` ADD COLUMN `email_verified` TINYINT(1) NOT NULL DEFAULT 0 AFTER `email_id`',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- user.email_verification_token
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user' AND COLUMN_NAME = 'email_verification_token');
SET @s := IF(@c = 0,
    'ALTER TABLE `user` ADD COLUMN `email_verification_token` CHAR(64) NULL DEFAULT NULL AFTER `email_verified`',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- user.email_verification_sent_at
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user' AND COLUMN_NAME = 'email_verification_sent_at');
SET @s := IF(@c = 0,
    'ALTER TABLE `user` ADD COLUMN `email_verification_sent_at` DATETIME NULL DEFAULT NULL AFTER `email_verification_token`',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- user.email_verified_at
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user' AND COLUMN_NAME = 'email_verified_at');
SET @s := IF(@c = 0,
    'ALTER TABLE `user` ADD COLUMN `email_verified_at` DATETIME NULL DEFAULT NULL AFTER `email_verification_sent_at`',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Verification-token lookup index.
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user' AND INDEX_NAME = 'user_email_verification_token_idx');
SET @s := IF(@c = 0,
    'CREATE INDEX `user_email_verification_token_idx` ON `user` (`email_verification_token`)',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Back-fill existing users as verified so legacy accounts aren't locked out.
UPDATE `user`
   SET `email_verified` = 1,
       `email_verified_at` = COALESCE(`email_verified_at`, NOW())
 WHERE `email_verified` IS NULL OR `email_verified` = 0;


-- ============================================================
-- 0002_services.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS `services` (
    `service_id`        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug`              VARCHAR(160) NOT NULL,
    `name`              VARCHAR(160) NOT NULL,
    `short_description` VARCHAR(500) NOT NULL,
    `body`              MEDIUMTEXT NULL,
    `icon`              VARCHAR(100) NULL,
    `image`             VARCHAR(255) NULL,
    `category`          VARCHAR(80)  NULL,
    `base_price`        DECIMAL(10,2) NULL,
    `price_unit`        ENUM('one_time','monthly','annual','project','hourly','custom')
                              NOT NULL DEFAULT 'custom',
    `sort_order`        INT NOT NULL DEFAULT 0,
    `is_active`         TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`        DATETIME NOT NULL,
    `updated_at`        DATETIME NOT NULL,
    PRIMARY KEY (`service_id`),
    UNIQUE KEY `services_slug_uq` (`slug`),
    KEY `services_active_sort_idx` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `service_requests` (
    `request_id`   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `service_id`   BIGINT UNSIGNED NULL,
    `user_id`      BIGINT UNSIGNED NULL,
    `name`         VARCHAR(160) NOT NULL,
    `email`        VARCHAR(255) NOT NULL,
    `phone`        VARCHAR(40)  NULL,
    `company`      VARCHAR(160) NULL,
    `budget_range` VARCHAR(40)  NULL,
    `message`      TEXT         NOT NULL,
    `status`       ENUM('new','in_review','quoted','won','lost','spam')
                          NOT NULL DEFAULT 'new',
    `admin_notes`  TEXT         NULL,
    `ip`           VARCHAR(45)  NULL,
    `user_agent`   VARCHAR(255) NULL,
    `created_at`   DATETIME NOT NULL,
    `updated_at`   DATETIME NOT NULL,
    PRIMARY KEY (`request_id`),
    KEY `service_requests_service_idx` (`service_id`),
    KEY `service_requests_user_idx`    (`user_id`),
    KEY `service_requests_status_idx`  (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed catalog (skip rows whose slug already exists).
INSERT INTO `services`
    (`slug`, `name`, `short_description`, `body`, `icon`, `category`,
     `base_price`, `price_unit`, `sort_order`, `is_active`,
     `created_at`, `updated_at`)
SELECT * FROM (
    SELECT 'website-design-development' AS slug,
           'Website Design & Development' AS name,
           'Custom-built, mobile-first websites for local businesses — built to convert visitors into customers.' AS short_description,
           '<p>From a simple brochure site to a full custom build, we design and develop websites that look professional, load fast, and read clearly on every device.</p><ul><li>Custom layout designed around your brand</li><li>Mobile-first responsive build</li><li>SEO-friendly structure out of the box</li><li>Connected to your Google Business Profile</li><li>Up to 10 content pages in the starter package</li></ul>' AS body,
           'web' AS icon, 'Web Development' AS category,
           1499.00 AS base_price, 'project' AS price_unit, 10 AS sort_order, 1 AS is_active,
           NOW() AS created_at, NOW() AS updated_at
    UNION ALL SELECT 'website-hosting','Website Hosting & SSL','Fast, secure hosting on US-based servers with daily backups, free SSL, and uptime monitoring.','<p>Managed hosting designed for small business websites. We handle the server so you do not have to.</p><ul><li>SSD storage, US data centers</li><li>Free SSL certificate (HTTPS)</li><li>Daily off-site backups, 30-day retention</li><li>24/7 uptime monitoring</li><li>Email support included</li></ul>','cloud','Hosting & Infrastructure',29.00,'monthly',20,1,NOW(),NOW()
    UNION ALL SELECT 'website-maintenance','Website Maintenance & Care Plan','We keep your site updated, backed up, secure, and online — so it does not break the week you finally need it.','<p>Monthly care plans for sites we host or sites we did not build. Includes plugin and core updates, security scans, malware cleanup, and a fixed monthly allowance of content edits.</p><ul><li>Monthly software / plugin updates</li><li>Security monitoring + malware cleanup</li><li>Up to 1 hour of content edits / month</li><li>Monthly performance + uptime report</li></ul>','tools','Hosting & Infrastructure',99.00,'monthly',30,1,NOW(),NOW()
    UNION ALL SELECT 'search-engine-optimization','Search Engine Optimization (SEO)','Local SEO that gets your business found in Pearland-area Google searches and the local 3-pack.','<p>We focus on local SEO that drives real foot traffic and phone calls. Includes keyword research, on-page optimization, Google Business Profile management, citations cleanup, and monthly reporting.</p><ul><li>Local keyword research and content plan</li><li>On-page SEO + technical fixes</li><li>Google Business Profile optimization</li><li>NAP citation cleanup</li><li>Monthly ranking + traffic report</li></ul>','search','Marketing',499.00,'monthly',40,1,NOW(),NOW()
    UNION ALL SELECT 'logo-brand-identity','Logo & Brand Identity','A clean, professional logo + brand kit you can use everywhere — site, signage, business cards, social.','<p>Includes three logo concepts, two revision rounds, and a finished brand kit (logo files in all formats, color palette, typography guide).</p><ul><li>3 initial logo concepts</li><li>2 revision rounds</li><li>Final logo in PNG, SVG, PDF, EPS</li><li>Color + typography guide</li><li>Business card design (optional add-on)</li></ul>','palette','Design & Branding',599.00,'project',50,1,NOW(),NOW()
    UNION ALL SELECT 'ecommerce-development','eCommerce / Online Store','WooCommerce or Shopify stores set up, designed, and connected to your payment processor — ready to sell.','<p>Complete eCommerce setup including theme customization, product import for up to 50 SKUs, payment gateway integration (Stripe / Square / Authorize.Net), shipping configuration, and tax setup. Training included.</p>','shopping_cart','Web Development',2499.00,'project',60,1,NOW(),NOW()
    UNION ALL SELECT 'it-support-helpdesk','Business IT Support & Helpdesk','On-call IT support for small business owners — printers, email, Wi-Fi, computer issues. We answer the phone.','<p>Block-of-hours or monthly retainer support for everyday business IT problems. Remote first; on-site available in the Pearland / Houston metro area.</p><ul><li>Email and phone support</li><li>Remote screen-share troubleshooting</li><li>On-site visits available</li><li>Network / Wi-Fi setup + repair</li><li>Printer + email configuration</li></ul>','support_agent','IT Support',125.00,'hourly',70,1,NOW(),NOW()
    UNION ALL SELECT 'business-email-microsoft-365','Business Email & Microsoft 365 Setup','Set up name@yourbusiness.com on Google Workspace or Microsoft 365 — done right, with backups and spam filtering.','<p>Includes domain configuration, mailbox setup, mobile device sync, spam and phishing protection, and onboarding for up to 5 users. Ongoing user management available as an add-on.</p>','mail','IT Support',199.00,'project',80,1,NOW(),NOW()
    UNION ALL SELECT 'paid-ads-google-meta','Paid Ads — Google & Meta','Targeted Google Ads and Facebook/Instagram campaigns for local businesses, managed monthly.','<p>Campaign strategy, ad copywriting, creative, audience targeting, and weekly optimization. You pay the ad spend directly to Google / Meta; this is the management fee.</p><ul><li>Strategy + landing-page review</li><li>Up to 3 campaigns / month</li><li>Weekly optimization</li><li>Monthly performance call</li></ul>','campaign','Marketing',599.00,'monthly',90,1,NOW(),NOW()
    UNION ALL SELECT 'social-media-management','Social Media Management','Posting, engagement, and growth on Facebook and Instagram — managed by us, branded for you.','<p>Includes content calendar, 12 branded posts per month across Facebook + Instagram, community management (comments + messages), and monthly reporting.</p>','share','Marketing',449.00,'monthly',100,1,NOW(),NOW()
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `services` WHERE `services`.`slug` = seed.slug);


-- ============================================================
-- 0003_newsletter_campaigns.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS `newsletter_campaigns` (
    `campaign_id`     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `subject`         VARCHAR(255) NOT NULL,
    `body_html`       MEDIUMTEXT  NOT NULL,
    `from_name`       VARCHAR(160) NULL,
    `from_address`    VARCHAR(255) NULL,
    `status`          ENUM('draft','queued','sending','sent','failed','canceled')
                          NOT NULL DEFAULT 'draft',
    `total_recipients` INT UNSIGNED NOT NULL DEFAULT 0,
    `sent_count`      INT UNSIGNED NOT NULL DEFAULT 0,
    `failed_count`    INT UNSIGNED NOT NULL DEFAULT 0,
    `scheduled_at`    DATETIME NULL,
    `started_at`      DATETIME NULL,
    `finished_at`     DATETIME NULL,
    `created_by`      BIGINT UNSIGNED NULL,
    `created_at`      DATETIME NOT NULL,
    `updated_at`      DATETIME NOT NULL,
    PRIMARY KEY (`campaign_id`),
    KEY `nc_status_idx` (`status`),
    KEY `nc_scheduled_idx` (`scheduled_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `newsletter_campaign_sends` (
    `send_id`     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `campaign_id` BIGINT UNSIGNED NOT NULL,
    `email`       VARCHAR(255) NOT NULL,
    `status`      ENUM('pending','sent','failed','skipped')
                       NOT NULL DEFAULT 'pending',
    `error`       VARCHAR(500) NULL,
    `sent_at`     DATETIME NULL,
    `created_at`  DATETIME NOT NULL,
    PRIMARY KEY (`send_id`),
    KEY `ncs_campaign_idx` (`campaign_id`, `status`),
    UNIQUE KEY `ncs_campaign_email_uq` (`campaign_id`, `email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 0004_rate_limits.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS `rate_limits` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `bucket_key`    VARCHAR(180) NOT NULL,
    `attempts`      INT UNSIGNED NOT NULL DEFAULT 0,
    `window_start`  DATETIME NOT NULL,
    `window_end`    DATETIME NOT NULL,
    `last_ip`       VARCHAR(45) NULL,
    `created_at`    DATETIME NOT NULL,
    `updated_at`    DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `rate_limits_bucket_uq` (`bucket_key`),
    KEY `rate_limits_window_end_idx` (`window_end`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 0005_admin_roles.sql
-- ============================================================

-- admin.role
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admin' AND COLUMN_NAME = 'role');
SET @s := IF(@c = 0,
    "ALTER TABLE `admin` ADD COLUMN `role` ENUM('super_admin','editor') NOT NULL DEFAULT 'editor' AFTER `admin_type`",
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- admin.is_active
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admin' AND COLUMN_NAME = 'is_active');
SET @s := IF(@c = 0,
    'ALTER TABLE `admin` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `role`',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Promote the original admin (id=1) to super_admin.
UPDATE `admin` SET `role` = 'super_admin' WHERE `admin_id` = 1;


-- ============================================================
-- 0006_ad_zones.sql
-- ============================================================

-- ads_prices.slot_key + index
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ads_prices' AND COLUMN_NAME = 'slot_key');
SET @s := IF(@c = 0,
    'ALTER TABLE `ads_prices` ADD COLUMN `slot_key` VARCHAR(60) NULL AFTER `name`',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ads_prices' AND INDEX_NAME = 'ads_prices_slot_key_idx');
SET @s := IF(@c = 0,
    'ALTER TABLE `ads_prices` ADD KEY `ads_prices_slot_key_idx` (`slot_key`)',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Seed the canonical slot list (skip slots that already exist).
INSERT INTO `ads_prices` (`name`, `slot_key`, `size`, `cost`, `status`, `created_at`, `updated_at`)
SELECT * FROM (
    SELECT 'Homepage hero'             AS name, 'home-hero'        AS slot_key, '728x200' AS size, 99.00 AS cost, 1 AS status, NOW() AS created_at, NOW() AS updated_at
    UNION ALL SELECT 'Homepage sidebar',            'home-sidebar',           '300x250',  49.00, 1, NOW(), NOW()
    UNION ALL SELECT 'Listing detail sidebar',      'listing-sidebar',        '300x250',  49.00, 1, NOW(), NOW()
    UNION ALL SELECT 'Listing list — top',          'listing-top',            '728x90',   59.00, 1, NOW(), NOW()
    UNION ALL SELECT 'Listing list — between rows', 'listing-inline',         '728x90',   49.00, 1, NOW(), NOW()
    UNION ALL SELECT 'Events page sidebar',         'events-sidebar',         '300x250',  39.00, 1, NOW(), NOW()
    UNION ALL SELECT 'News article sidebar',        'news-sidebar',           '300x250',  39.00, 1, NOW(), NOW()
    UNION ALL SELECT 'Footer banner',               'footer-banner',          '970x90',   29.00, 1, NOW(), NOW()
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `ads_prices` WHERE `ads_prices`.`slot_key` = seed.slot_key);

-- ---------------------------------------------------------------------------
-- 0007 — site_settings (SEO/analytics tokens, social URLs, GMaps key)
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `site_settings` (
    `setting_key`   VARCHAR(80) NOT NULL,
    `setting_value` TEXT        NULL,
    `updated_at`    DATETIME    NOT NULL,
    PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
    ('site_title',                 'Pearland411 — Local Businesses, Events, Deals & News in Pearland, TX', NOW()),
    ('site_description',           'Pearland411 is your community guide to Pearland, Texas — find local businesses, upcoming events, deals, news and jobs all in one place.', NOW()),
    ('site_keywords',              'Pearland businesses, Pearland events, Pearland deals, Pearland news, Pearland TX directory', NOW()),
    ('site_og_image',              '', NOW()),
    ('contact_phone',              '281-482-2031', NOW()),
    ('contact_email',              'sales@pearland411.com', NOW()),
    ('google_analytics_id',        '', NOW()),
    ('google_site_verification',   '', NOW()),
    ('bing_site_verification',     '', NOW()),
    ('facebook_app_id',            '', NOW()),
    ('google_maps_api_key',        '', NOW()),
    ('social_facebook_url',        '', NOW()),
    ('social_twitter_url',         '', NOW()),
    ('social_instagram_url',       '', NOW()),
    ('social_linkedin_url',        '', NOW()),
    ('social_youtube_url',         '', NOW());


-- ============================================================
-- 0008_event_admin_host.sql
-- events.admin_id — host is now an admin (staff) account.
-- ============================================================

-- events.admin_id
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'admin_id');
SET @s := IF(@c = 0,
    'ALTER TABLE `events` ADD COLUMN `admin_id` INT NULL DEFAULT NULL AFTER `user_id`',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.admin_id index
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND INDEX_NAME = 'events_admin_id_idx');
SET @s := IF(@c = 0,
    'ALTER TABLE `events` ADD KEY `events_admin_id_idx` (`admin_id`)',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- ============================================================
-- 0009_rental_price_widen.sql
-- rental.rental_price was too small (VARCHAR(10)) and truncated
-- longer prices / ranges. Widen it to VARCHAR(50).
-- ============================================================

SET @len := (SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'rental' AND COLUMN_NAME = 'rental_price');
SET @typ := (SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'rental' AND COLUMN_NAME = 'rental_price');
SET @s := IF(@typ IS NOT NULL AND NOT (@typ = 'varchar' AND @len >= 50),
    'ALTER TABLE `rental` MODIFY COLUMN `rental_price` VARCHAR(50) NULL DEFAULT NULL',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- ============================================================
-- 0010_plan_capabilities.sql
-- Field-level capability flags on `plans` for the freemium
-- listing model (photos / map / services / extra details).
-- Video reuses the existing `plans.allow_video`.
-- ============================================================
SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='plans' AND COLUMN_NAME='can_photos');
SET @s := IF(@e = 0, 'ALTER TABLE `plans` ADD COLUMN `can_photos` TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''May upload listing logo + gallery photos''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='plans' AND COLUMN_NAME='photo_limit');
SET @s := IF(@e = 0, 'ALTER TABLE `plans` ADD COLUMN `photo_limit` INT(11) NOT NULL DEFAULT 0 COMMENT ''Max gallery photos (-1 = unlimited, 0 = none)''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='plans' AND COLUMN_NAME='can_map');
SET @s := IF(@e = 0, 'ALTER TABLE `plans` ADD COLUMN `can_map` TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''May set the Google map / lat-lng''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='plans' AND COLUMN_NAME='can_services');
SET @s := IF(@e = 0, 'ALTER TABLE `plans` ADD COLUMN `can_services` TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''May fill services offered''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='plans' AND COLUMN_NAME='can_extra_details');
SET @s := IF(@e = 0, 'ALTER TABLE `plans` ADD COLUMN `can_extra_details` TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''May add extra Q&A / SEO detail block''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- ============================================================
-- 0011_service_registry.sql
-- Admin master on/off switches per optional content section.
-- Seeded deactivated ('0'); admin activates from /admin/setting.
-- ============================================================
INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
    ('service_events_enabled',      '0', NOW()),
    ('service_realestate_enabled',  '0', NOW()),
    ('service_classifieds_enabled', '0', NOW()),
    ('service_news_enabled',        '0', NOW()),
    ('service_blog_enabled',        '0', NOW()),
    ('service_products_enabled',    '0', NOW()),
    ('service_videos_enabled',      '0', NOW());


-- ============================================================
-- 0012_helcim_billing.sql
-- Parallel Helcim columns on transactions / payment_cards.
-- Old auth_net_* columns are left in place for history.
-- ============================================================
SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='transactions' AND COLUMN_NAME='helcim_transaction_id');
SET @s := IF(@e = 0, 'ALTER TABLE `transactions` ADD COLUMN `helcim_transaction_id` VARCHAR(255) DEFAULT NULL COMMENT ''Helcim transactionId''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='transactions' AND COLUMN_NAME='helcim_card_token');
SET @s := IF(@e = 0, 'ALTER TABLE `transactions` ADD COLUMN `helcim_card_token` VARCHAR(255) DEFAULT NULL COMMENT ''Card token returned by HelcimPay.js''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='transactions' AND COLUMN_NAME='helcim_approval_code');
SET @s := IF(@e = 0, 'ALTER TABLE `transactions` ADD COLUMN `helcim_approval_code` VARCHAR(50) DEFAULT NULL COMMENT ''Gateway approval code''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='payment_cards' AND COLUMN_NAME='helcim_card_token');
SET @s := IF(@e = 0, 'ALTER TABLE `payment_cards` ADD COLUMN `helcim_card_token` VARCHAR(255) DEFAULT NULL COMMENT ''Vaulted card token for recurring charges''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='payment_cards' AND COLUMN_NAME='helcim_customer_code');
SET @s := IF(@e = 0, 'ALTER TABLE `payment_cards` ADD COLUMN `helcim_customer_code` VARCHAR(100) DEFAULT NULL COMMENT ''Helcim customer / vault code''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- ============================================================
-- 0013_subscription_lifecycle.sql
-- Recurring billing lifecycle columns on `subscriptions`.
-- ============================================================
SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='subscriptions' AND COLUMN_NAME='renewal_attempts');
SET @s := IF(@e = 0, 'ALTER TABLE `subscriptions` ADD COLUMN `renewal_attempts` INT(11) NOT NULL DEFAULT 0 COMMENT ''Consecutive failed auto-renew charges''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='subscriptions' AND COLUMN_NAME='last_renewal_error');
SET @s := IF(@e = 0, 'ALTER TABLE `subscriptions` ADD COLUMN `last_renewal_error` VARCHAR(255) DEFAULT NULL COMMENT ''Last gateway error on renewal''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='subscriptions' AND COLUMN_NAME='grace_until');
SET @s := IF(@e = 0, 'ALTER TABLE `subscriptions` ADD COLUMN `grace_until` DATETIME DEFAULT NULL COMMENT ''Access kept until this time during dunning''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='subscriptions' AND COLUMN_NAME='pending_plan_id');
SET @s := IF(@e = 0, 'ALTER TABLE `subscriptions` ADD COLUMN `pending_plan_id` INT(11) DEFAULT NULL COMMENT ''Scheduled downgrade target, applied at renewal''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- ============================================================
-- 0014_plan_category.sql
-- Advertising catalog group slug on `plans`.
-- ============================================================
SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='plans' AND COLUMN_NAME='category');
SET @s := IF(@e = 0, 'ALTER TABLE `plans` ADD COLUMN `category` VARCHAR(50) NOT NULL DEFAULT ''business-listing'' COMMENT ''Advertising catalog group''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;




-- ---------------------------------------------------------------------------
-- 0015_plan_durations_columns.sql
-- Per-term pricing table. Never created by a migration before, so a fresh
-- install had no table and the live DB lacked `currency`/`updated_at` — which
-- made admin duration saves fail silently.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `plan_durations` (
    `id`              INT(11) NOT NULL AUTO_INCREMENT,
    `plan_id`         INT(11) NOT NULL,
    `duration_months` INT(11) NOT NULL DEFAULT 1,
    `duration_days`   INT(11) NOT NULL DEFAULT 30,
    `price`           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `currency`        VARCHAR(10) NOT NULL DEFAULT 'USD',
    `is_active`       TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`      DATETIME NULL DEFAULT NULL,
    `updated_at`      DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_plan_durations_plan` (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='plan_durations' AND COLUMN_NAME='currency');
SET @s := IF(@e = 0, 'ALTER TABLE `plan_durations` ADD COLUMN `currency` VARCHAR(10) NOT NULL DEFAULT ''USD'' AFTER `price`', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='plan_durations' AND COLUMN_NAME='duration_days');
SET @s := IF(@e = 0, 'ALTER TABLE `plan_durations` ADD COLUMN `duration_days` INT(11) NOT NULL DEFAULT 30 AFTER `duration_months`', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='plan_durations' AND COLUMN_NAME='is_active');
SET @s := IF(@e = 0, 'ALTER TABLE `plan_durations` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='plan_durations' AND COLUMN_NAME='created_at');
SET @s := IF(@e = 0, 'ALTER TABLE `plan_durations` ADD COLUMN `created_at` DATETIME NULL DEFAULT NULL', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='plan_durations' AND COLUMN_NAME='updated_at');
SET @s := IF(@e = 0, 'ALTER TABLE `plan_durations` ADD COLUMN `updated_at` DATETIME NULL DEFAULT NULL', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

UPDATE `plan_durations` SET `duration_days` = `duration_months` * 30
 WHERE (`duration_days` IS NULL OR `duration_days` = 0) AND `duration_months` > 0;


-- ---------------------------------------------------------------------------
-- 0016_transaction_term.sql
-- Records the term a customer actually bought for an ad placement, so a
-- 12-month banner is not reported as expiring in 30 days.
-- ---------------------------------------------------------------------------
SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='transactions' AND COLUMN_NAME='term_days');
SET @s := IF(@e = 0, 'ALTER TABLE `transactions` ADD COLUMN `term_days` INT(11) NULL DEFAULT NULL', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='transactions' AND COLUMN_NAME='expires_at');
SET @s := IF(@e = 0, 'ALTER TABLE `transactions` ADD COLUMN `expires_at` DATETIME NULL DEFAULT NULL', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='transactions' AND COLUMN_NAME='duration_id');
SET @s := IF(@e = 0, 'ALTER TABLE `transactions` ADD COLUMN `duration_id` INT(11) NULL DEFAULT NULL', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

UPDATE `transactions` t JOIN `plans` p ON p.id = t.plan_id
   SET t.term_days = p.duration_days,
       t.expires_at = DATE_ADD(t.created_at, INTERVAL p.duration_days DAY)
 WHERE t.term_days IS NULL AND p.duration_days > 0;


-- ============================================================
-- 0017_home_ad_slots.sql
-- Homepage ad zones. Each slot_key equals the plans.slug of the
-- package sold on /pricing-plan, so a purchase maps to a position.
-- DATA ONLY (0006 added slot_key/size) -> identical to the
-- standalone file. Idempotent via NOT EXISTS.
-- ============================================================
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

-- ============================================================
-- 0018_homepage_promos.sql
-- listings.hotspot_until / week_until — the two homepage
-- placements sold as business-hotspot ($25/30d) and
-- business-of-the-week ($20/7d). DATE, so the placement expires
-- on its own. PREPARE/EXECUTE form for phpMyAdmin (no DELIMITER).
-- ============================================================
SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='listings' AND COLUMN_NAME='hotspot_until');
SET @s := IF(@e = 0, 'ALTER TABLE `listings` ADD COLUMN `hotspot_until` DATE NULL DEFAULT NULL COMMENT ''Business Hotspot on Homepage - last day the placement shows'', ADD KEY `listings_hotspot_until_idx` (`hotspot_until`)', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='listings' AND COLUMN_NAME='week_until');
SET @s := IF(@e = 0, 'ALTER TABLE `listings` ADD COLUMN `week_until` DATE NULL DEFAULT NULL COMMENT ''Business Of the Week - last day the placement shows'', ADD KEY `listings_week_until_idx` (`week_until`)', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- ============================================================
-- 0019_directory_ad_slots.sql
-- Category-priced ad zones for /all-listing and
-- /listing-details. Same rule as 0017: each slot_key is the
-- plans.slug of the package sold on /pricing-plan, so a
-- purchase maps to a position. Data only.
-- ============================================================
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


-- ============================================================
-- 0020_listing_status_normalise.sql
-- Publishes the listings that were imported / submitted as
-- listing_status = 0 (the CSV import hardcoded 0; the public
-- add-listing form wrote the string 'Active', which MySQL
-- coerced to 0), so the category counters and the category
-- pages finally agree. Backs the old values up to
-- listings.listing_status_backup first.
-- Also backfills listing_categories.category_slug, matching
-- pearland_slug() in application/helpers/view_helper.php.
-- PREPARE/EXECUTE + temp tables for phpMyAdmin (no DELIMITER).
--
-- ROLLBACK (publish step only):
--   UPDATE `listings` SET `listing_status` = `listing_status_backup`
--    WHERE `listing_status_backup` IS NOT NULL;
-- ============================================================

-- The backup column IS the idempotency guard: @p411_ls_new is 1 only on the
-- run that creates it, so a re-import can never re-publish something an
-- operator has since switched off.
SET @p411_ls_new := (SELECT IF(COUNT(*) = 0, 1, 0) FROM INFORMATION_SCHEMA.COLUMNS
                     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'listings'
                       AND COLUMN_NAME = 'listing_status_backup');

SET @s := IF(@p411_ls_new = 1, 'ALTER TABLE `listings` ADD COLUMN `listing_status_backup` TINYINT(1) NULL DEFAULT NULL COMMENT ''listing_status before migration 0020 - rollback source''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF(@p411_ls_new = 1, 'UPDATE `listings` SET `listing_status_backup` = `listing_status`', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF(@p411_ls_new = 1, 'UPDATE `listings` SET `listing_status` = 1 WHERE (`listing_status` IS NULL OR `listing_status` = 0) AND (`is_deleted` IS NULL OR `is_deleted` = 0)', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- listing_categories.category_slug — guard-add for older installs.
SET @e := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='listing_categories' AND COLUMN_NAME='category_slug');
SET @s := IF(@e = 0, 'ALTER TABLE `listing_categories` ADD COLUMN `category_slug` VARCHAR(191) NULL DEFAULT NULL', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Backfill the empty slugs. The REPLACE chain is pearland_slug() in SQL:
-- lowercase, spaces to hyphens, drop every other punctuation character,
-- collapse hyphen runs, trim. "Medical & Dental" -> "medical-dental".
DROP TEMPORARY TABLE IF EXISTS `p411_slug_calc`;
CREATE TEMPORARY TABLE `p411_slug_calc` AS
SELECT `category_id`,
       TRIM(BOTH '-' FROM REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(TRIM(`category_name`)), ' ', '-'), '!', ''), '"', ''), '#', ''), '$', ''), '%', ''), '&', ''), '''', ''), '(', ''), ')', ''), '*', ''), '+', ''), ',', ''), '.', ''), '/', ''), ':', ''), ';', ''), '<', ''), '=', ''), '>', ''), '?', ''), '@', ''), '[', ''), '\\', ''), ']', ''), '^', ''), '_', ''), '`', ''), '{', ''), '|', ''), '}', ''), '~', ''), '---', '-'), '--', '-'), '--', '-'), '--', '-')) AS `slug`
  FROM `listing_categories`
 WHERE `category_slug` IS NULL OR `category_slug` = '';

-- Discard anything that didn't reduce to a usable slug (an all-punctuation
-- name, or non-ASCII the chain can't transliterate) and anything that would
-- collide with a slug already in use. Those rows keep an empty slug and stay
-- on name matching rather than being given a URL that lands elsewhere.
DELETE FROM `p411_slug_calc`
 WHERE `slug` IS NULL OR `slug` = ''
    OR `slug` NOT REGEXP '^[a-z0-9]([a-z0-9-]*[a-z0-9])?$';

DELETE c FROM `p411_slug_calc` c
 WHERE EXISTS (SELECT 1 FROM `listing_categories` lc WHERE lc.`category_slug` = c.`slug`);

-- Collisions within the batch. A temporary table can only be opened once per
-- statement, so the duplicates go to a second scratch table, not a subquery.
DROP TEMPORARY TABLE IF EXISTS `p411_slug_dupes`;
CREATE TEMPORARY TABLE `p411_slug_dupes` AS
SELECT `slug` FROM `p411_slug_calc` GROUP BY `slug` HAVING COUNT(*) > 1;

DELETE c FROM `p411_slug_calc` c JOIN `p411_slug_dupes` d ON d.`slug` = c.`slug`;

UPDATE `listing_categories` lc
  JOIN `p411_slug_calc` c ON c.`category_id` = lc.`category_id`
   SET lc.`category_slug` = c.`slug`
 WHERE lc.`category_slug` IS NULL OR lc.`category_slug` = '';

DROP TEMPORARY TABLE IF EXISTS `p411_slug_dupes`;
DROP TEMPORARY TABLE IF EXISTS `p411_slug_calc`;



-- ===========================================================================
-- 0021_blog_dates.sql
-- ---------------------------------------------------------------------------
-- "All blogs have the same date": Admin::add_blog() never wrote
-- blogs.created_at, so those rows carried the zero date / NULL and the index
-- printed the epoch for every one of them. Make the column nullable with a
-- sensible default and turn the zero dates into NULL — honest "no date
-- recorded", which the views now omit rather than printing 1970. Real dates
-- are set per post at /admin/blog-list -> Edit.
--
-- phpMyAdmin cannot run DELIMITER blocks, so the guards here are prepared
-- statements built from INFORMATION_SCHEMA rather than a stored procedure.
-- ===========================================================================

SET @p411_blog_created_missing = (
    SELECT COUNT(*) = 0 FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'blogs' AND COLUMN_NAME = 'created_at');

SET @sql = IF(@p411_blog_created_missing,
    'ALTER TABLE `blogs` ADD COLUMN `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP',
    'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @p411_blog_created_needs_fix = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'blogs' AND COLUMN_NAME = 'created_at'
       AND (IS_NULLABLE <> 'YES' OR COLUMN_DEFAULT IS NULL));

SET @sql = IF(@p411_blog_created_needs_fix > 0,
    'ALTER TABLE `blogs` MODIFY COLUMN `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP',
    'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

UPDATE `blogs` SET `created_at` = NULL WHERE `created_at` = '0000-00-00 00:00:00';


-- ===========================================================================
-- 0022_listing_deleted_date.sql
-- ---------------------------------------------------------------------------
-- "Deleted listings cannot be restored — HTTP 500 Internal Server Error."
-- Admin::restore_listing() wrote `listings.deleted_date`, a column that
-- appeared exactly once in the whole application — on that line. Nothing
-- created it and nothing else wrote it (the soft delete set only
-- is_deleted = 1, and the trash page's "Deleted Date" heading was being filled
-- with postal_code), so the UPDATE hit MySQL 1054 and the request 500'd.
--
-- The column is worth keeping rather than dropping from the code: a trash page
-- that cannot say when something was deleted is not much of a trash page. The
-- soft delete now sets it and restore clears it.
--
-- phpMyAdmin cannot run DELIMITER blocks, so the guard is a prepared statement
-- built from INFORMATION_SCHEMA rather than a stored procedure.
-- ===========================================================================

SET @p411_listing_deleted_date_missing = (
    SELECT COUNT(*) = 0 FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'listings' AND COLUMN_NAME = 'deleted_date');

SET @sql = IF(@p411_listing_deleted_date_missing,
    'ALTER TABLE `listings` ADD COLUMN `deleted_date` DATETIME NULL DEFAULT NULL COMMENT ''When the listing was moved to trash; NULL once restored''',
    'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- A listing that is not in the trash must not carry a deletion stamp.
UPDATE `listings`
   SET `deleted_date` = NULL
 WHERE `deleted_date` IS NOT NULL
   AND (`is_deleted` = 0 OR `is_deleted` IS NULL);


-- ===========================================================================
-- 0023_listing_subcategory_map.sql
-- ---------------------------------------------------------------------------
-- "Why can't I add subcategories, even though I have included all the
-- available subcategories?" It was never possible: listings.sub_category_id is
-- a single INT and the admin form posted a single-value <select>, so a listing
-- could hold exactly one and the last one picked won. listing_sub_categories is
-- the CATALOGUE of subcategories, not a listing-to-subcategory join, and no
-- join table existed anywhere. This adds one and back-fills existing values.
--
-- listings.sub_category_id stays, holding the FIRST selection, so the many
-- readers that still use it keep working; it can be retired separately.
-- ===========================================================================

CREATE TABLE IF NOT EXISTS `listing_sub_category_map` (
    `listing_id`      INT(11) NOT NULL,
    `sub_category_id` INT(11) NOT NULL,
    PRIMARY KEY (`listing_id`, `sub_category_id`),
    KEY `lscm_sub_category_idx` (`sub_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Many-to-many: a listing may sit under several subcategories';

INSERT INTO `listing_sub_category_map` (`listing_id`, `sub_category_id`)
SELECT l.`listing_id`, l.`sub_category_id`
  FROM `listings` l
 WHERE l.`sub_category_id` IS NOT NULL
   AND l.`sub_category_id` > 0
   AND NOT EXISTS (
       SELECT 1 FROM `listing_sub_category_map` m
        WHERE m.`listing_id` = l.`listing_id`
          AND m.`sub_category_id` = l.`sub_category_id`
   );


-- ===========================================================================
-- 0024_ads_category_target.sql
-- ---------------------------------------------------------------------------
-- "No option to add category-specific banners and header banner in Business
-- Listings." Ads had exactly one targeting dimension - ads_prices.slot_key,
-- the position on the page - so the slots named "category" were single global
-- inventory items rendered on category pages, not banners sold per category.
--
-- ads.category_id: NULL runs the ad in its slot everywhere (which is every ad
-- booked before this migration), an id restricts it to that listing category.
-- A category booking outranks an untargeted one in the same slot.
-- ===========================================================================

SET @p411_ads_category_missing = (
    SELECT COUNT(*) = 0 FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ads' AND COLUMN_NAME = 'category_id');

SET @sql = IF(@p411_ads_category_missing,
    'ALTER TABLE `ads` ADD COLUMN `category_id` INT(11) NULL DEFAULT NULL',
    'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @p411_ads_category_idx_missing = (
    SELECT COUNT(*) = 0 FROM INFORMATION_SCHEMA.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ads' AND INDEX_NAME = 'ads_category_idx');

SET @sql = IF(@p411_ads_category_idx_missing,
    'ALTER TABLE `ads` ADD KEY `ads_category_idx` (`category_id`)',
    'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- The header banner slot the report asks for, so there is something to sell.
INSERT INTO `ads_prices` (`name`, `slot_key`, `size`, `cost`, `status`, `created_at`, `updated_at`)
SELECT * FROM (
    SELECT 'Category Header Banner' AS name,
           'top-banner-category'    AS slot_key,
           '900x100'                AS size,
           25.00                    AS cost,
           1                        AS status,
           NOW()                    AS created_at,
           NOW()                    AS updated_at
) AS seed
WHERE NOT EXISTS (
    SELECT 1 FROM `ads_prices` WHERE `ads_prices`.`slot_key` = seed.slot_key
);


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


-- ============================================================
-- 0026_enquiry_departments.sql
-- ============================================================
-- Splits package callbacks out of the general contact pile, and replaces the
-- named sales reps on the public forms with departments. See
-- database/migrations/0026_enquiry_departments.sql for the full reasoning;
-- this is the same migration rewritten in the inline prepared-statement style
-- this bundle uses, because phpMyAdmin's importer cannot parse the procedure
-- form.
--
-- `enquiries` predates this repo's migrations, so every step below is guarded
-- on the table existing (@t) as well as on the column/index (@c).

-- 1. The department column. NULL = filed before departments existed.
SET @t := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME   = 'enquiries');
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME   = 'enquiries'
             AND COLUMN_NAME  = 'enquiry_department');
SET @s := IF(@t = 1 AND @c = 0,
    'ALTER TABLE `enquiries` ADD COLUMN `enquiry_department` VARCHAR(80) NULL DEFAULT NULL',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. An index behind the admin tabs — /admin/enquiries filters on enquiry_type.
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME   = 'enquiries'
             AND INDEX_NAME   = 'enquiries_type_idx');
SET @s := IF(@t = 1 AND @c = 0,
    'ALTER TABLE `enquiries` ADD KEY `enquiries_type_idx` (`enquiry_type`)',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3. Reclassify the callback requests already in the table, so the new tabs
--    are not empty on day one and the existing leads stop hiding among the
--    general messages. Matched with LIKE on the ASCII prefix of
--    'Pricing plan - callback': the em dash in the real string is the one
--    character whose bytes depend on the client charset, and an import that
--    negotiates latin1 would match nothing and silently leave leads misfiled.
SET @s := IF(@t = 1,
    'UPDATE `enquiries` SET `enquiry_type` = ''package'' WHERE `enquiry_source` LIKE ''Pricing plan%'' AND `enquiry_type` = ''contact''',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 4. Backfill the department. A package callback was a sales lead; a
--    contact-form message was not. Anything else keeps NULL rather than being
--    guessed at. Re-checks the column, since step 1 is a no-op on a database
--    that already has it and @c is reused above.
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME   = 'enquiries'
             AND COLUMN_NAME  = 'enquiry_department');
SET @s := IF(@t = 1 AND @c = 1,
    'UPDATE `enquiries` SET `enquiry_department` = ''Sales Department'' WHERE `enquiry_type` = ''package'' AND (`enquiry_department` IS NULL OR `enquiry_department` = '''')',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF(@t = 1 AND @c = 1,
    'UPDATE `enquiries` SET `enquiry_department` = ''Customer Service'' WHERE `enquiry_type` = ''contact'' AND (`enquiry_department` IS NULL OR `enquiry_department` = '''')',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 5. Retire the named-rep list. The nine names shown on the live site are not
--    in the code — the code's fallback was overridden by this row, and
--    sales_representatives() is gone. The text of past enquiries is NOT
--    touched: a message that says "Asked for: <name>" stays as it was sent.
--    The department list is deliberately not seeded — blank means "use the
--    built-in list" in enquiry_departments().
DELETE FROM `site_settings` WHERE `setting_key` = 'sales_representatives';

SET FOREIGN_KEY_CHECKS = 1;
