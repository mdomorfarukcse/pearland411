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

-- ============================================================
-- 0027_category_header_banner.sql
-- ============================================================
-- Adds the EDITORIAL half of "No option to add category-specific banners and
-- header banner in Business Listings". 0024 added the sold half
-- (`ads.category_id` plus the `top-banner-category` zone); this adds the
-- category's own header artwork, which is not inventory. See
-- database/migrations/0027_category_header_banner.sql for the full reasoning;
-- this is the same migration in the inline prepared-statement style this
-- bundle uses, because phpMyAdmin's importer cannot parse the procedure form.
--
-- NULL means "use the standard directory header", so every existing category
-- looks exactly as it does today. No seed, no backfill.
--
-- `listing_categories` predates this repo's migrations, so the step is guarded
-- on the table existing (@t) as well as on the column (@c).

SET @t := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME   = 'listing_categories');
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME   = 'listing_categories'
             AND COLUMN_NAME  = 'category_banner');
SET @s := IF(@t = 1 AND @c = 0,
    'ALTER TABLE `listing_categories` ADD COLUMN `category_banner` VARCHAR(255) NULL DEFAULT NULL',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- ============================================================
-- 0028_admin_billing.sql
-- ============================================================
-- Backs /admin/billing — the screen where staff put a customer's card on
-- file, take a one-off payment, and schedule recurring billing. Until it
-- existed a card could only be vaulted by the CUSTOMER, during their own
-- checkout, and only if they ticked auto-renew: staff signing a business up
-- over the phone had no path at all, and Cli::renew_subscriptions had nothing
-- to charge. See database/migrations/0028_admin_billing.sql for the full
-- reasoning; this is the same migration in the inline prepared-statement
-- style this bundle uses, because phpMyAdmin's importer cannot parse the
-- procedure form.
--
-- All five columns are nullable or defaulted, so every existing row stays
-- valid and nothing is back-filled. Nothing here can hold a PAN, expiry or
-- CVV beyond the last four digits — card data is tokenized by HelcimPay.js in
-- the browser and never reaches the server, as 0012 established.
--
-- `payment_cards` and `transactions` both predate this repo's migrations, so
-- each step is guarded on its table (@t) as well as on the column (@c).

-- payment_cards.card_brand
SET @t := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME   = 'payment_cards');
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME   = 'payment_cards'
             AND COLUMN_NAME  = 'card_brand');
SET @s := IF(@t = 1 AND @c = 0,
    'ALTER TABLE `payment_cards` ADD COLUMN `card_brand` VARCHAR(30) NULL DEFAULT NULL COMMENT ''Card brand for display (Visa, Mastercard, ...)''',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- payment_cards.card_source
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME   = 'payment_cards'
             AND COLUMN_NAME  = 'card_source');
SET @s := IF(@t = 1 AND @c = 0,
    'ALTER TABLE `payment_cards` ADD COLUMN `card_source` VARCHAR(20) NOT NULL DEFAULT ''customer'' COMMENT ''customer = self-serve checkout, admin = keyed in by staff''',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- payment_cards.added_by_admin_id
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME   = 'payment_cards'
             AND COLUMN_NAME  = 'added_by_admin_id');
SET @s := IF(@t = 1 AND @c = 0,
    'ALTER TABLE `payment_cards` ADD COLUMN `added_by_admin_id` INT(11) NULL DEFAULT NULL COMMENT ''admin.admin_id who put this card on file, when staff did''',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- transactions.created_by_admin_id
SET @t := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME   = 'transactions');
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME   = 'transactions'
             AND COLUMN_NAME  = 'created_by_admin_id');
SET @s := IF(@t = 1 AND @c = 0,
    'ALTER TABLE `transactions` ADD COLUMN `created_by_admin_id` INT(11) NULL DEFAULT NULL COMMENT ''admin.admin_id who took this payment manually''',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- transactions.note
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME   = 'transactions'
             AND COLUMN_NAME  = 'note');
SET @s := IF(@t = 1 AND @c = 0,
    'ALTER TABLE `transactions` ADD COLUMN `note` VARCHAR(255) NULL DEFAULT NULL COMMENT ''What this charge was for, e.g. "August 2026 bill"''',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- ============================================================
-- 0029_billing_per_business.sql
-- ============================================================
-- Bills a BUSINESS, not an account, and lets staff agree a price. 0028 built
-- /admin/billing around `user`, because that is what `subscriptions` and
-- `payment_cards` are keyed on — but the listings here were bulk-loaded under a
-- handful of data-entry logins, so one account owns dozens of unrelated
-- businesses. Searching "central bbq" returned the import account "10 dollars
-- Galveston shuttle" with a hundred business names strung after it, and billing
-- that row would have billed all of them at once. See
-- database/migrations/0029_billing_per_business.sql for the full reasoning.
--
-- All three columns are nullable with no default, so every existing row stays
-- valid and nothing is back-filled: an existing subscription keeps billing the
-- account exactly as it does today until somebody attaches it to a business.

-- subscriptions.listing_id
SET @t := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions');
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
             AND COLUMN_NAME = 'listing_id');
SET @s := IF(@t = 1 AND @c = 0,
    'ALTER TABLE `subscriptions` ADD COLUMN `listing_id` INT(11) NULL DEFAULT NULL COMMENT ''Business this bills for. NULL means account-level''',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- subscriptions index on listing_id
SET @i := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
             AND INDEX_NAME = 'idx_subscriptions_listing');
SET @s := IF(@t = 1 AND @i = 0,
    'ALTER TABLE `subscriptions` ADD INDEX `idx_subscriptions_listing` (`listing_id`)',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- subscriptions.custom_amount
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
             AND COLUMN_NAME = 'custom_amount');
SET @s := IF(@t = 1 AND @c = 0,
    'ALTER TABLE `subscriptions` ADD COLUMN `custom_amount` DECIMAL(10,2) NULL DEFAULT NULL COMMENT ''Agreed price that overrides the plan price on renewal''',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- transactions.listing_id
SET @t := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions');
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions'
             AND COLUMN_NAME = 'listing_id');
SET @s := IF(@t = 1 AND @c = 0,
    'ALTER TABLE `transactions` ADD COLUMN `listing_id` INT(11) NULL DEFAULT NULL COMMENT ''Business this payment was for''',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- transactions index on listing_id
SET @i := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions'
             AND INDEX_NAME = 'idx_transactions_listing');
SET @s := IF(@t = 1 AND @i = 0,
    'ALTER TABLE `transactions` ADD INDEX `idx_transactions_listing` (`listing_id`)',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Who is copied on billing activity, on top of every active admin.
-- INSERT IGNORE so re-running never overwrites an edited list.
SET @t := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'site_settings');
SET @s := IF(@t = 1,
    'INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`, `updated_at`) VALUES (''billing_notify_emails'', ''joshua@spacecentersystems.com'', NOW())',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- ============================================================
-- 0030_subscription_term_columns.sql
-- ============================================================
-- Creates the `subscriptions` columns the code has always written but no
-- migration ever added. Found by /admin/billing-customer/<id> returning HTTP
-- 500: it asked for `duration_id` / `duration_days`, and MySQL answers a
-- missing column with error 1054 rather than a NULL. Those are written only by
-- the self-serve checkout, which has never completed on this database -- so
-- they were never created, and the checkout has been broken here the whole
-- time in exactly the same way. See
-- database/migrations/0030_subscription_term_columns.sql for the full note.
--
-- Every column is added ONLY when missing, so on a database where a checkout
-- did once succeed this block changes nothing.

SET @t := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions');

-- subscriptions.duration_id
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
             AND COLUMN_NAME = 'duration_id');
SET @s := IF(@t = 1 AND @c = 0,
    'ALTER TABLE `subscriptions` ADD COLUMN `duration_id` INT(11) NULL DEFAULT NULL COMMENT ''plan_durations row bought, when a term was chosen''',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- subscriptions.duration_days
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
             AND COLUMN_NAME = 'duration_days');
SET @s := IF(@t = 1 AND @c = 0,
    'ALTER TABLE `subscriptions` ADD COLUMN `duration_days` INT(11) NOT NULL DEFAULT 30 COMMENT ''Recurring cadence in days. Each renewal extends by this''',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- subscriptions.duration_months
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
             AND COLUMN_NAME = 'duration_months');
SET @s := IF(@t = 1 AND @c = 0,
    'ALTER TABLE `subscriptions` ADD COLUMN `duration_months` INT(11) NOT NULL DEFAULT 1 COMMENT ''The same span in months, for display''',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- subscriptions.total_amount
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
             AND COLUMN_NAME = 'total_amount');
SET @s := IF(@t = 1 AND @c = 0,
    'ALTER TABLE `subscriptions` ADD COLUMN `total_amount` DECIMAL(10,2) NULL DEFAULT NULL COMMENT ''What was paid for the current term''',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- subscriptions.currency
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
             AND COLUMN_NAME = 'currency');
SET @s := IF(@t = 1 AND @c = 0,
    'ALTER TABLE `subscriptions` ADD COLUMN `currency` VARCHAR(10) NOT NULL DEFAULT ''USD'' COMMENT ''ISO currency of total_amount''',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- subscriptions.billing_cycle
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
             AND COLUMN_NAME = 'billing_cycle');
SET @s := IF(@t = 1 AND @c = 0,
    'ALTER TABLE `subscriptions` ADD COLUMN `billing_cycle` VARCHAR(20) NOT NULL DEFAULT ''monthly'' COMMENT ''monthly / yearly, for display''',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- subscriptions.payment_status
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
             AND COLUMN_NAME = 'payment_status');
SET @s := IF(@t = 1 AND @c = 0,
    'ALTER TABLE `subscriptions` ADD COLUMN `payment_status` VARCHAR(20) NOT NULL DEFAULT ''pending'' COMMENT ''paid / pending''',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- subscriptions.start_date
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
             AND COLUMN_NAME = 'start_date');
SET @s := IF(@t = 1 AND @c = 0,
    'ALTER TABLE `subscriptions` ADD COLUMN `start_date` DATETIME NULL DEFAULT NULL COMMENT ''When the current term began''',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- subscriptions.last_transaction_id
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
             AND COLUMN_NAME = 'last_transaction_id');
SET @s := IF(@t = 1 AND @c = 0,
    'ALTER TABLE `subscriptions` ADD COLUMN `last_transaction_id` INT(11) NULL DEFAULT NULL COMMENT ''The payment that bought the current term''',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- ============================================================
-- 0031_category_dedupe.sql
-- ============================================================
-- Merges categories that are the same category written two ways:
-- "Arts & Entertainment" beside "Arts and Entertainment",
-- "Community & Government" (empty) beside "Community and
-- Government" (14 businesses). Two URLs, two counts, one kind of
-- business, with the listings split between them.
--
-- Same name once normalised ONLY — lower-cased, "&" read as "and",
-- punctuation out, whitespace collapsed. Judgement calls like
-- Finance / Financial Services are deliberately left alone;
-- /admin/all_listing_category has a Merge control for those.
--
-- The survivor is the one with the most published listings, lowest
-- id breaking the tie. Everything is re-pointed at it, the merge is
-- recorded in `listing_category_merges`, then the duplicate row is
-- deleted.
--
-- The WHILE loop in the per-file version is unrolled into a REPLACE
-- chain here, and the scratch tables are ordinary tables (the
-- winner is chosen with a correlated read of the table being
-- scanned, which MySQL refuses on a TEMPORARY table).
--
-- Idempotent: a second run finds no two categories sharing a
-- normalised name and does nothing.
-- ============================================================

CREATE TABLE IF NOT EXISTS `listing_category_merges` (
    `id`                 INT(11) NOT NULL AUTO_INCREMENT,
    `from_category_id`   INT(11) NOT NULL,
    `from_category_name` VARCHAR(255) NULL DEFAULT NULL,
    `from_category_slug` VARCHAR(191) NULL DEFAULT NULL,
    `to_category_id`     INT(11) NOT NULL,
    `listings_moved`     INT(11) NOT NULL DEFAULT 0,
    `merged_at`          DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `lcm_from_idx` (`from_category_id`),
    KEY `lcm_to_idx`   (`to_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Categories merged by migration 0031 / the admin Merge tool';

DROP TABLE IF EXISTS `p411_cat_loser`;
DROP TABLE IF EXISTS `p411_cat_winner`;
DROP TABLE IF EXISTS `p411_cat_rank`;
DROP TABLE IF EXISTS `p411_cat_key`;

CREATE TABLE `p411_cat_key` (
    `category_id` INT(11) NOT NULL,
    `merge_key`   VARCHAR(191) NULL,
    PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- pearland_merge_key() in SQL: lowercase, "&"/"+" to the word "and",
-- every other punctuation character to a space, spaces collapsed, trimmed.
INSERT INTO `p411_cat_key` (`category_id`, `merge_key`)
SELECT `category_id`, TRIM(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LEFT(LOWER(TRIM(`category_name`)), 191), '&', ' and '), '+', ' and '), '!', ' '), '"', ' '), '#', ' '), '$', ' '), '%', ' '), '''', ' '), '(', ' '), ')', ' '), '*', ' '), '+', ' '), ',', ' '), '-', ' '), '.', ' '), '/', ' '), ':', ' '), ';', ' '), '<', ' '), '=', ' '), '>', ' '), '?', ' '), '@', ' '), '[', ' '), '\\', ' '), ']', ' '), '^', ' '), '_', ' '), '`', ' '), '{', ' '), '|', ' '), '}', ' '), '~', ' '), '  ', ' '), '  ', ' '), '  ', ' '), '  ', ' '))
  FROM `listing_categories`;

DELETE FROM `p411_cat_key` WHERE `merge_key` IS NULL OR `merge_key` = '';

CREATE TABLE `p411_cat_rank` (
    `category_id`   INT(11) NOT NULL,
    `merge_key`     VARCHAR(191) NULL,
    `listing_count` INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`category_id`),
    KEY `p411_cr_key` (`merge_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `p411_cat_rank` (`category_id`, `merge_key`, `listing_count`)
SELECT k.`category_id`, k.`merge_key`,
       (SELECT COUNT(*) FROM `listings` l
         WHERE l.`category_id` = k.`category_id`
           AND (l.`is_deleted` IS NULL OR l.`is_deleted` = 0)
           AND l.`listing_status` = 1)
  FROM `p411_cat_key` k;

CREATE TABLE `p411_cat_winner` (
    `merge_key`   VARCHAR(191) NOT NULL,
    `category_id` INT(11) NOT NULL,
    PRIMARY KEY (`merge_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `p411_cat_winner` (`merge_key`, `category_id`)
SELECT r.`merge_key`,
       (SELECT w.`category_id` FROM `p411_cat_rank` w
         WHERE w.`merge_key` = r.`merge_key`
         ORDER BY w.`listing_count` DESC, w.`category_id` ASC
         LIMIT 1)
  FROM `p411_cat_rank` r
 GROUP BY r.`merge_key`
HAVING COUNT(*) > 1;

CREATE TABLE `p411_cat_loser` (
    `from_category_id` INT(11) NOT NULL,
    `to_category_id`   INT(11) NOT NULL,
    PRIMARY KEY (`from_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `p411_cat_loser` (`from_category_id`, `to_category_id`)
SELECT r.`category_id`, w.`category_id`
  FROM `p411_cat_rank` r
  JOIN `p411_cat_winner` w ON w.`merge_key` = r.`merge_key`
 WHERE r.`category_id` <> w.`category_id`;

-- Recorded before anything moves.
INSERT INTO `listing_category_merges`
    (`from_category_id`, `from_category_name`, `from_category_slug`,
     `to_category_id`, `listings_moved`, `merged_at`)
SELECT lc.`category_id`, lc.`category_name`, lc.`category_slug`, p.`to_category_id`,
       (SELECT COUNT(*) FROM `listings` l WHERE l.`category_id` = lc.`category_id`),
       NOW()
  FROM `p411_cat_loser` p
  JOIN `listing_categories` lc ON lc.`category_id` = p.`from_category_id`;

-- The survivor inherits a slug only when it has none: rewriting a live slug
-- breaks every link already pointing at it.
UPDATE `listing_categories` win
  JOIN `p411_cat_loser` p        ON p.`to_category_id` = win.`category_id`
  JOIN `listing_categories` lose ON lose.`category_id` = p.`from_category_id`
   SET win.`category_slug` = lose.`category_slug`
 WHERE (win.`category_slug` IS NULL OR win.`category_slug` = '')
   AND lose.`category_slug` IS NOT NULL AND lose.`category_slug` <> '';

UPDATE `listings` l
  JOIN `p411_cat_loser` p ON p.`from_category_id` = l.`category_id`
   SET l.`category_id` = p.`to_category_id`;

-- listing_sub_categories.category_id — guarded, the column is not on every install.
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'listing_sub_categories'
             AND COLUMN_NAME = 'category_id');
SET @s := IF(@c = 1,
    'UPDATE `listing_sub_categories` s JOIN `p411_cat_loser` p ON p.`from_category_id` = s.`category_id` SET s.`category_id` = p.`to_category_id`',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ads.category_id — category-targeted bookings, added by 0024.
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ads'
             AND COLUMN_NAME = 'category_id');
SET @s := IF(@c = 1,
    'UPDATE `ads` a JOIN `p411_cat_loser` p ON p.`from_category_id` = a.`category_id` SET a.`category_id` = p.`to_category_id`',
    'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

DELETE lc FROM `listing_categories` lc
  JOIN `p411_cat_loser` p ON p.`from_category_id` = lc.`category_id`;

DROP TABLE IF EXISTS `p411_cat_loser`;
DROP TABLE IF EXISTS `p411_cat_winner`;
DROP TABLE IF EXISTS `p411_cat_rank`;
DROP TABLE IF EXISTS `p411_cat_key`;


-- ============================================================
-- 0032_listing_dedupe.sql
-- ============================================================
-- Hides businesses the directory holds twice — the cause of
-- "/all_listing/shopping-retail says 42 businesses and the first
-- page repeats the same shops".
--
-- The same name once punctuation and case are out, AND one hard
-- identifier in common: the phone number (digits only, last ten),
-- the street address, or the website host. Name alone is never
-- enough — Pearland has more than one Subway.
--
-- A listing with an owner (`user_id` > 0) is NEVER hidden: claimed
-- listings are somebody's property, quite possibly somebody's paid
-- package. Where two claimed rows collide both are left alone, and
-- /admin/all_listings shows the pair for a person to decide.
--
-- The loser is soft-deleted (`is_deleted` = 1, `deleted_date` =
-- NOW()) — nothing is erased, reviews and enquiries stay attached,
-- and every action is recorded in `listing_duplicate_merges` first.
--
-- ROLLBACK
--   UPDATE `listings` l
--     JOIN `listing_duplicate_merges` m ON m.`listing_id` = l.`listing_id`
--      SET l.`is_deleted` = 0, l.`deleted_date` = NULL;
--
-- RUN AFTER 0031: two copies of one shop filed under "Beauty &
-- Personal Care" and "Beauty and Personal Care" have to become one
-- category first.
--
-- Idempotent: a soft-deleted row is out of scope for every later
-- pass and for a second run.
-- ============================================================

CREATE TABLE IF NOT EXISTS `listing_duplicate_merges` (
    `id`               INT(11) NOT NULL AUTO_INCREMENT,
    `listing_id`       INT(11) NOT NULL COMMENT 'the row that was hidden',
    `kept_listing_id`  INT(11) NOT NULL COMMENT 'the row it duplicated',
    `listing_name`     VARCHAR(255) NULL DEFAULT NULL,
    `matched_on`       VARCHAR(16)  NULL DEFAULT NULL COMMENT 'phone | address | website',
    `merged_at`        DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `ldm_listing_uniq` (`listing_id`),
    KEY `ldm_kept_idx` (`kept_listing_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Listings hidden as duplicates by migration 0032 / the admin Duplicates screen';

-- listings.deleted_date — added by 0022; guard-add for an install that skipped it.
SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'listings'
             AND COLUMN_NAME = 'deleted_date');
SET @s := IF(@c = 0, 'ALTER TABLE `listings` ADD COLUMN `deleted_date` DATETIME NULL DEFAULT NULL', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

DROP TABLE IF EXISTS `p411_dup_loser`;
DROP TABLE IF EXISTS `p411_dup_winner`;
DROP TABLE IF EXISTS `p411_dup_key`;

CREATE TABLE `p411_dup_key` (
    `listing_id`   INT(11) NOT NULL,
    `name_key`     VARCHAR(191) NULL,
    `phone_key`    VARCHAR(64)  NULL,
    `address_key`  VARCHAR(191) NULL,
    `website_key`  VARCHAR(191) NULL,
    `match_key`    VARCHAR(255) NULL,
    `owned`        TINYINT(1)   NOT NULL DEFAULT 0,
    `promoted`     TINYINT(1)   NOT NULL DEFAULT 0,
    `has_image`    TINYINT(1)   NOT NULL DEFAULT 0,
    `descr_len`    INT(11)      NOT NULL DEFAULT 0,
    PRIMARY KEY (`listing_id`),
    KEY `p411_dk_match` (`match_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `p411_dup_key`
    (`listing_id`, `name_key`, `phone_key`, `address_key`, `website_key`,
     `owned`, `promoted`, `has_image`, `descr_len`)
SELECT l.`listing_id`,
       TRIM(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LEFT(LOWER(TRIM(COALESCE(l.`listing_name`, ''))), 191), '!', ' '), '"', ' '), '#', ' '), '$', ' '), '%', ' '), '&', ' '), '''', ' '), '(', ' '), ')', ' '), '*', ' '), '+', ' '), ',', ' '), '-', ' '), '.', ' '), '/', ' '), ':', ' '), ';', ' '), '<', ' '), '=', ' '), '>', ' '), '?', ' '), '@', ' '), '[', ' '), '\\', ' '), ']', ' '), '^', ' '), '_', ' '), '`', ' '), '{', ' '), '|', ' '), '}', ' '), '~', ' '), '  ', ' '), '  ', ' '), '  ', ' '), '  ', ' ')),
       REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LEFT(COALESCE(l.`listing_mobile`, ''), 64), ' ', ''), '-', ''), '(', ''), ')', ''), '+', ''), '.', ''), '/', ''), '#', ''),
       TRIM(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LEFT(LOWER(TRIM(COALESCE(l.`listing_address`, ''))), 191), '!', ' '), '"', ' '), '#', ' '), '$', ' '), '%', ' '), '&', ' '), '''', ' '), '(', ' '), ')', ' '), '*', ' '), '+', ' '), ',', ' '), '-', ' '), '.', ' '), '/', ' '), ':', ' '), ';', ' '), '<', ' '), '=', ' '), '>', ' '), '?', ' '), '@', ' '), '[', ' '), '\\', ' '), ']', ' '), '^', ' '), '_', ' '), '`', ' '), '{', ' '), '|', ' '), '}', ' '), '~', ' '), '  ', ' '), '  ', ' '), '  ', ' '), '  ', ' ')),
       SUBSTRING_INDEX(REPLACE(REPLACE(REPLACE(LEFT(LOWER(TRIM(COALESCE(l.`listing_website`, ''))), 191), 'https://', ''), 'http://', ''), 'www.', ''), '/', 1),
       CASE WHEN COALESCE(l.`user_id`, 0) > 0 THEN 1 ELSE 0 END,
       CASE WHEN COALESCE(l.`featured_listing`, 0) > 0
              OR COALESCE(l.`spotlight_listing`, 0) > 0 THEN 1 ELSE 0 END,
       CASE WHEN COALESCE(l.`listing_image`, '') = '' THEN 0 ELSE 1 END,
       CHAR_LENGTH(COALESCE(l.`listing_description`, ''))
  FROM `listings` l
 WHERE (l.`is_deleted` IS NULL OR l.`is_deleted` = 0);

-- Anything still carrying a letter — an extension, a note — is not a number
-- this can compare, so it sits pass 1 out.
UPDATE `p411_dup_key` SET `phone_key` = NULL WHERE `phone_key` NOT REGEXP '^[0-9]+$';

-- Last ten digits: a stored country code must not split one number in two.
UPDATE `p411_dup_key` SET `phone_key` = RIGHT(`phone_key`, 10) WHERE `phone_key` IS NOT NULL;

DELETE FROM `p411_dup_key` WHERE `name_key` IS NULL OR `name_key` = '';

-- ---- pass 1: same name + same phone ----
UPDATE `p411_dup_key`
   SET `match_key` = CASE WHEN `phone_key` IS NOT NULL AND CHAR_LENGTH(`phone_key`) >= 7
                          THEN CONCAT('p:', `name_key`, '|', `phone_key`) END;

DROP TABLE IF EXISTS `p411_dup_winner`;
CREATE TABLE `p411_dup_winner` (
    `match_key`  VARCHAR(255) NOT NULL,
    `listing_id` INT(11) NOT NULL,
    KEY `p411_dw_match` (`match_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `p411_dup_winner` (`match_key`, `listing_id`)
SELECT k.`match_key`,
       (SELECT w.`listing_id` FROM `p411_dup_key` w
         WHERE w.`match_key` = k.`match_key`
         ORDER BY w.`owned` DESC, w.`promoted` DESC, w.`has_image` DESC,
                  w.`descr_len` DESC, w.`listing_id` ASC
         LIMIT 1)
  FROM `p411_dup_key` k
 WHERE k.`match_key` IS NOT NULL
 GROUP BY k.`match_key`
HAVING COUNT(*) > 1;

DROP TABLE IF EXISTS `p411_dup_loser`;
CREATE TABLE `p411_dup_loser` (
    `listing_id`      INT(11) NOT NULL,
    `kept_listing_id` INT(11) NOT NULL,
    PRIMARY KEY (`listing_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- `owned` = 0 only: a listing with an owner is never hidden automatically.
INSERT INTO `p411_dup_loser` (`listing_id`, `kept_listing_id`)
SELECT k.`listing_id`, w.`listing_id`
  FROM `p411_dup_key` k
  JOIN `p411_dup_winner` w ON w.`match_key` = k.`match_key`
 WHERE k.`listing_id` <> w.`listing_id`
   AND k.`owned` = 0;

INSERT IGNORE INTO `listing_duplicate_merges`
    (`listing_id`, `kept_listing_id`, `listing_name`, `matched_on`, `merged_at`)
SELECT p.`listing_id`, p.`kept_listing_id`, l.`listing_name`, 'phone', NOW()
  FROM `p411_dup_loser` p
  JOIN `listings` l ON l.`listing_id` = p.`listing_id`;

UPDATE `listings` l
  JOIN `p411_dup_loser` p ON p.`listing_id` = l.`listing_id`
   SET l.`is_deleted` = 1, l.`deleted_date` = NOW();

DELETE k FROM `p411_dup_key` k
  JOIN `p411_dup_loser` p ON p.`listing_id` = k.`listing_id`;

-- ---- pass 2: same name + same address ----
UPDATE `p411_dup_key`
   SET `match_key` = CASE WHEN `address_key` IS NOT NULL AND CHAR_LENGTH(`address_key`) >= 6
                          THEN CONCAT('a:', `name_key`, '|', `address_key`) END;

DROP TABLE IF EXISTS `p411_dup_winner`;
CREATE TABLE `p411_dup_winner` (
    `match_key`  VARCHAR(255) NOT NULL,
    `listing_id` INT(11) NOT NULL,
    KEY `p411_dw_match` (`match_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `p411_dup_winner` (`match_key`, `listing_id`)
SELECT k.`match_key`,
       (SELECT w.`listing_id` FROM `p411_dup_key` w
         WHERE w.`match_key` = k.`match_key`
         ORDER BY w.`owned` DESC, w.`promoted` DESC, w.`has_image` DESC,
                  w.`descr_len` DESC, w.`listing_id` ASC
         LIMIT 1)
  FROM `p411_dup_key` k
 WHERE k.`match_key` IS NOT NULL
 GROUP BY k.`match_key`
HAVING COUNT(*) > 1;

DROP TABLE IF EXISTS `p411_dup_loser`;
CREATE TABLE `p411_dup_loser` (
    `listing_id`      INT(11) NOT NULL,
    `kept_listing_id` INT(11) NOT NULL,
    PRIMARY KEY (`listing_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- `owned` = 0 only: a listing with an owner is never hidden automatically.
INSERT INTO `p411_dup_loser` (`listing_id`, `kept_listing_id`)
SELECT k.`listing_id`, w.`listing_id`
  FROM `p411_dup_key` k
  JOIN `p411_dup_winner` w ON w.`match_key` = k.`match_key`
 WHERE k.`listing_id` <> w.`listing_id`
   AND k.`owned` = 0;

INSERT IGNORE INTO `listing_duplicate_merges`
    (`listing_id`, `kept_listing_id`, `listing_name`, `matched_on`, `merged_at`)
SELECT p.`listing_id`, p.`kept_listing_id`, l.`listing_name`, 'address', NOW()
  FROM `p411_dup_loser` p
  JOIN `listings` l ON l.`listing_id` = p.`listing_id`;

UPDATE `listings` l
  JOIN `p411_dup_loser` p ON p.`listing_id` = l.`listing_id`
   SET l.`is_deleted` = 1, l.`deleted_date` = NOW();

DELETE k FROM `p411_dup_key` k
  JOIN `p411_dup_loser` p ON p.`listing_id` = k.`listing_id`;

-- ---- pass 3: same name + same website ----
UPDATE `p411_dup_key`
   SET `match_key` = CASE WHEN `website_key` IS NOT NULL AND CHAR_LENGTH(`website_key`) >= 4
                          THEN CONCAT('w:', `name_key`, '|', `website_key`) END;

DROP TABLE IF EXISTS `p411_dup_winner`;
CREATE TABLE `p411_dup_winner` (
    `match_key`  VARCHAR(255) NOT NULL,
    `listing_id` INT(11) NOT NULL,
    KEY `p411_dw_match` (`match_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `p411_dup_winner` (`match_key`, `listing_id`)
SELECT k.`match_key`,
       (SELECT w.`listing_id` FROM `p411_dup_key` w
         WHERE w.`match_key` = k.`match_key`
         ORDER BY w.`owned` DESC, w.`promoted` DESC, w.`has_image` DESC,
                  w.`descr_len` DESC, w.`listing_id` ASC
         LIMIT 1)
  FROM `p411_dup_key` k
 WHERE k.`match_key` IS NOT NULL
 GROUP BY k.`match_key`
HAVING COUNT(*) > 1;

DROP TABLE IF EXISTS `p411_dup_loser`;
CREATE TABLE `p411_dup_loser` (
    `listing_id`      INT(11) NOT NULL,
    `kept_listing_id` INT(11) NOT NULL,
    PRIMARY KEY (`listing_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- `owned` = 0 only: a listing with an owner is never hidden automatically.
INSERT INTO `p411_dup_loser` (`listing_id`, `kept_listing_id`)
SELECT k.`listing_id`, w.`listing_id`
  FROM `p411_dup_key` k
  JOIN `p411_dup_winner` w ON w.`match_key` = k.`match_key`
 WHERE k.`listing_id` <> w.`listing_id`
   AND k.`owned` = 0;

INSERT IGNORE INTO `listing_duplicate_merges`
    (`listing_id`, `kept_listing_id`, `listing_name`, `matched_on`, `merged_at`)
SELECT p.`listing_id`, p.`kept_listing_id`, l.`listing_name`, 'website', NOW()
  FROM `p411_dup_loser` p
  JOIN `listings` l ON l.`listing_id` = p.`listing_id`;

UPDATE `listings` l
  JOIN `p411_dup_loser` p ON p.`listing_id` = l.`listing_id`
   SET l.`is_deleted` = 1, l.`deleted_date` = NOW();

DELETE k FROM `p411_dup_key` k
  JOIN `p411_dup_loser` p ON p.`listing_id` = k.`listing_id`;

DROP TABLE IF EXISTS `p411_dup_loser`;
DROP TABLE IF EXISTS `p411_dup_winner`;
DROP TABLE IF EXISTS `p411_dup_key`;


SET FOREIGN_KEY_CHECKS = 1;
