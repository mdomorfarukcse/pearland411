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


SET FOREIGN_KEY_CHECKS = 1;
