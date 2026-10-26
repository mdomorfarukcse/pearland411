-- ---------------------------------------------------------------------------
-- 0038_listing_packages.sql
-- ---------------------------------------------------------------------------
-- The business listing packages from "Pearland411 Business Listing &
-- Advertising Packages" — Free Community ($0), Pearland Starter ($29/mo),
-- Pearland Business ($79/mo) and Pearland Premier ($149/mo) — replacing the
-- old listing plans (Basic / Advanced $15 / Featured $25 / the $230 Special
-- Package, and any other listing tier on the live site such as the $299 and
-- $499 ones the checklist names). Run after 0037.
--
-- (1) New plan columns for what the packages table distinguishes and the
--     existing flags could not:
--       can_email       the listing may show an email address (Starter+)
--       can_coupons     the owner may post deals / coupons (Starter+)
--       verified_badge  the listing shows the Verified badge (Starter+)
--       extra_features  one line each: what staff deliver by hand for the
--                       tier (priority search, homepage feature, banner,
--                       newsletter, social spotlight) — shown on the
--                       pricing cards, editable at /admin/pricing.
--     When first created, every EXISTING plan gets can_email and can_coupons
--     (nobody loses what they have today), and verified_badge for the paid
--     listing tiers.
--
-- (2) The four packages, each with its 30-day base term in plan_durations.
--     Inserted only where the slug does not exist yet.
--
-- (3) Only on the run that creates them (so a later admin change is never
--     undone by a re-run):
--       - active members on an old FREE listing plan move to Free Community
--         (both cost nothing; Free Community also includes Google Maps);
--       - every other listing tier and the Special Package is taken off sale
--         (is_active = 0). A paying customer keeps their plan, its features
--         and its price until they change or cancel — renewals still charge
--         the price they signed up at;
--       - an old plan nobody ever bought (no subscription and no
--         transaction refers to it) is deleted, with its terms.
--     Banner, video and add-on packages are not touched.
--
-- Idempotent — INFORMATION_SCHEMA guards throughout. Safe to re-run.
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_listing_packages;
DELIMITER //
CREATE PROCEDURE pearland411_listing_packages()
BEGIN
    DECLARE v_first TINYINT DEFAULT 0;
    DECLARE v_free  INT DEFAULT NULL;

    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans') THEN

        -- (1) columns
        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans'
                          AND COLUMN_NAME = 'can_email') THEN
            ALTER TABLE `plans`
                ADD COLUMN `can_email` TINYINT(1) NOT NULL DEFAULT 0
                COMMENT 'Listing may show an email address',
                ADD COLUMN `can_coupons` TINYINT(1) NOT NULL DEFAULT 0
                COMMENT 'Owner may post deals / coupons',
                ADD COLUMN `verified_badge` TINYINT(1) NOT NULL DEFAULT 0
                COMMENT 'Listing shows the Verified badge (when claimed)',
                ADD COLUMN `extra_features` TEXT NULL
                COMMENT 'One per line: features delivered by staff, shown on the pricing card';
            UPDATE `plans` SET `can_email` = 1, `can_coupons` = 1;
            UPDATE `plans` SET `verified_badge` = 1
             WHERE `price` > 0 AND `category` IN ('business-listing', 'special');
        END IF;

        SET v_first = IF(EXISTS (SELECT 1 FROM `plans` WHERE `slug` = 'free-community'), 0, 1);

        -- (2) the four packages
        INSERT INTO `plans`
            (`name`, `slug`, `price`, `currency`, `billing_interval`, `duration_days`, `description`,
             `listing_limit`, `product_limit`, `event_limit`, `classified_limit`, `blog_limit`, `news_limit`,
             `video_limit`, `rental_limit`, `house_limit`, `ad_limit`, `ad_boost_limit`, `placeholder_images_limit`,
             `featured_listing`, `priority_support`, `analytics_access`,
             `can_photos`, `photo_limit`, `can_map`, `can_services`, `can_extra_details`, `allow_video`,
             `listing_video_limit`, `can_hours`, `can_social_links`,
             `can_email`, `can_coupons`, `verified_badge`, `extra_features`,
             `is_active`, `sort_order`, `category`, `created_at`, `updated_at`)
        SELECT 'Free Community', 'free-community', 0.00, 'USD', 'monthly', 30,
               'Get found in the Pearland directory: business name, category, address, phone, website, Google Maps and a short description.',
               1,0,0,0,0,0,0,0,0,0,0,0,
               0,0,0,
               0,0,1,0,0,0,
               0,0,0,
               0,0,0, 'Customer lead form',
               1, 1, 'business-listing', NOW(), NOW()
          FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `plans` WHERE `slug` = 'free-community');

        INSERT INTO `plans`
            (`name`, `slug`, `price`, `currency`, `billing_interval`, `duration_days`, `description`,
             `listing_limit`, `product_limit`, `event_limit`, `classified_limit`, `blog_limit`, `news_limit`,
             `video_limit`, `rental_limit`, `house_limit`, `ad_limit`, `ad_boost_limit`, `placeholder_images_limit`,
             `featured_listing`, `priority_support`, `analytics_access`,
             `can_photos`, `photo_limit`, `can_map`, `can_services`, `can_extra_details`, `allow_video`,
             `listing_video_limit`, `can_hours`, `can_social_links`,
             `can_email`, `can_coupons`, `verified_badge`, `extra_features`,
             `is_active`, `sort_order`, `category`, `created_at`, `updated_at`)
        SELECT 'Pearland Starter', 'pearland-starter', 29.00, 'USD', 'monthly', 30,
               'Everything in Free Community, plus your logo, email, hours, 5 photos, social media links, deals and a Verified badge.',
               1,0,0,0,0,0,0,0,0,0,0,0,
               0,0,0,
               1,5,1,1,1,0,
               0,1,1,
               1,1,1, 'Customer lead form',
               1, 2, 'business-listing', NOW(), NOW()
          FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `plans` WHERE `slug` = 'pearland-starter');

        INSERT INTO `plans`
            (`name`, `slug`, `price`, `currency`, `billing_interval`, `duration_days`, `description`,
             `listing_limit`, `product_limit`, `event_limit`, `classified_limit`, `blog_limit`, `news_limit`,
             `video_limit`, `rental_limit`, `house_limit`, `ad_limit`, `ad_boost_limit`, `placeholder_images_limit`,
             `featured_listing`, `priority_support`, `analytics_access`,
             `can_photos`, `photo_limit`, `can_map`, `can_services`, `can_extra_details`, `allow_video`,
             `listing_video_limit`, `can_hours`, `can_social_links`,
             `can_email`, `can_coupons`, `verified_badge`, `extra_features`,
             `is_active`, `sort_order`, `category`, `created_at`, `updated_at`)
        SELECT 'Pearland Business', 'pearland-business', 79.00, 'USD', 'monthly', 30,
               'Everything in Starter, plus 15 photos, a featured spot in your category, priority in search, a monthly homepage feature, a small banner and analytics.',
               1,0,0,0,0,0,0,0,0,0,0,0,
               1,0,1,
               1,15,1,1,1,0,
               0,1,1,
               1,1,1, 'Customer lead form\nPriority in search results\nHomepage feature, monthly\nSmall banner advertising\nNewsletter feature, quarterly\nSocial media spotlight, quarterly',
               1, 3, 'business-listing', NOW(), NOW()
          FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `plans` WHERE `slug` = 'pearland-business');

        INSERT INTO `plans`
            (`name`, `slug`, `price`, `currency`, `billing_interval`, `duration_days`, `description`,
             `listing_limit`, `product_limit`, `event_limit`, `classified_limit`, `blog_limit`, `news_limit`,
             `video_limit`, `rental_limit`, `house_limit`, `ad_limit`, `ad_boost_limit`, `placeholder_images_limit`,
             `featured_listing`, `priority_support`, `analytics_access`,
             `can_photos`, `photo_limit`, `can_map`, `can_services`, `can_extra_details`, `allow_video`,
             `listing_video_limit`, `can_hours`, `can_social_links`,
             `can_email`, `can_coupons`, `verified_badge`, `extra_features`,
             `is_active`, `sort_order`, `category`, `created_at`, `updated_at`)
        SELECT 'Pearland Premier', 'pearland-premier', 149.00, 'USD', 'monthly', 30,
               'Everything in Business, plus unlimited photos, a promotional video, a premium banner, priority homepage placement, and monthly newsletter and social media features.',
               1,0,0,0,0,0,0,0,0,0,0,0,
               1,1,1,
               1,-1,1,1,1,1,
               1,1,1,
               1,1,1, 'Customer lead form\nPriority in search results\nPriority homepage feature\nPremium banner advertising\nNewsletter feature, monthly\nSocial media spotlight, monthly',
               1, 4, 'business-listing', NOW(), NOW()
          FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `plans` WHERE `slug` = 'pearland-premier');

        -- The 30-day base term checkout charges (Admin::syncPlanBaseTerm keeps
        -- it in step with plans.price from now on).
        IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plan_durations') THEN
            INSERT INTO `plan_durations` (`plan_id`, `duration_months`, `duration_days`, `price`, `currency`, `is_active`, `created_at`, `updated_at`)
            SELECT p.`id`, 1, 30, p.`price`, p.`currency`, 1, NOW(), NOW()
              FROM `plans` p
             WHERE p.`slug` IN ('free-community', 'pearland-starter', 'pearland-business', 'pearland-premier')
               AND NOT EXISTS (SELECT 1 FROM `plan_durations` d WHERE d.`plan_id` = p.`id` AND d.`duration_days` = 30);
        END IF;

        -- (3) retire the old listing plans — first run only
        IF v_first = 1 THEN
            SELECT `id` INTO v_free FROM `plans` WHERE `slug` = 'free-community' LIMIT 1;

            IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions') THEN
                UPDATE `subscriptions` s
                  JOIN `plans` p ON p.`id` = s.`plan_id`
                   SET s.`plan_id` = v_free
                 WHERE s.`status` = 'active'
                   AND p.`category` = 'business-listing'
                   AND COALESCE(p.`price`, 0) = 0
                   AND p.`id` <> v_free;
                IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
                              AND COLUMN_NAME = 'pending_plan_id') THEN
                    UPDATE `subscriptions` s
                      JOIN `plans` p ON p.`id` = s.`pending_plan_id`
                       SET s.`pending_plan_id` = v_free
                     WHERE p.`category` = 'business-listing'
                       AND COALESCE(p.`price`, 0) = 0
                       AND p.`id` <> v_free;
                END IF;
            END IF;

            UPDATE `plans`
               SET `is_active` = 0, `updated_at` = NOW()
             WHERE `category` IN ('business-listing', 'special')
               AND `slug` NOT IN ('free-community', 'pearland-starter', 'pearland-business', 'pearland-premier');

            -- Delete what nobody ever bought. A scratch table, because MySQL
            -- will not delete from a table its own subquery reads.
            DROP TABLE IF EXISTS `p411_retired_plans`;
            CREATE TABLE `p411_retired_plans` (`id` INT(11) NOT NULL PRIMARY KEY) ENGINE=InnoDB;
            INSERT INTO `p411_retired_plans` (`id`)
            SELECT p.`id` FROM `plans` p
             WHERE p.`category` IN ('business-listing', 'special')
               AND p.`slug` NOT IN ('free-community', 'pearland-starter', 'pearland-business', 'pearland-premier');

            IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions') THEN
                DELETE r FROM `p411_retired_plans` r
                 WHERE EXISTS (SELECT 1 FROM `subscriptions` s WHERE s.`plan_id` = r.`id`);
                IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
                              AND COLUMN_NAME = 'pending_plan_id') THEN
                    DELETE r FROM `p411_retired_plans` r
                     WHERE EXISTS (SELECT 1 FROM `subscriptions` s WHERE s.`pending_plan_id` = r.`id`);
                END IF;
            END IF;
            IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions') THEN
                DELETE r FROM `p411_retired_plans` r
                 WHERE EXISTS (SELECT 1 FROM `transactions` t WHERE t.`plan_id` = r.`id`);
            END IF;

            IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plan_durations') THEN
                DELETE d FROM `plan_durations` d JOIN `p411_retired_plans` r ON r.`id` = d.`plan_id`;
            END IF;
            DELETE p FROM `plans` p JOIN `p411_retired_plans` r ON r.`id` = p.`id`;
            DROP TABLE IF EXISTS `p411_retired_plans`;
        END IF;
    END IF;

    -- (4) À la carte marketing services, at the packages document's prices.
    --     Existing services keep their wording; only the price changes.
    --     services.price_from marks a "starting at" price ("From $995").
    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'services') THEN
        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'services'
                          AND COLUMN_NAME = 'price_from') THEN
            ALTER TABLE `services`
                ADD COLUMN `price_from` TINYINT(1) NOT NULL DEFAULT 0
                COMMENT '1 = base_price is a starting price ("From $X")';

            UPDATE `services` SET `base_price` = 995.00, `price_unit` = 'project', `price_from` = 1 WHERE `slug` = 'website-design-development';
            UPDATE `services` SET `base_price` =  39.00, `price_unit` = 'monthly', `price_from` = 0 WHERE `slug` = 'website-hosting';
            UPDATE `services` SET `base_price` = 295.00, `price_unit` = 'project', `price_from` = 0 WHERE `slug` = 'logo-brand-identity';
            UPDATE `services` SET `base_price` = 299.00, `price_unit` = 'monthly', `price_from` = 1 WHERE `slug` = 'social-media-management';
            UPDATE `services` SET `base_price` = 495.00, `price_unit` = 'monthly', `price_from` = 1 WHERE `slug` = 'search-engine-optimization';
            -- Split into Google Ads and Facebook & Instagram Ads below.
            UPDATE `services` SET `is_active` = 0 WHERE `slug` = 'paid-ads-google-meta';
        END IF;

        INSERT INTO `services` (`slug`, `name`, `short_description`, `body`, `icon`, `category`, `base_price`, `price_unit`, `price_from`, `sort_order`, `is_active`, `created_at`, `updated_at`)
        SELECT * FROM (
            SELECT 'banner-ad-design' AS slug, 'Banner Ad Design' AS name,
                   'Professional banner artwork sized for your Pearland411 placement, ready to run.' AS short_description,
                   '<p>We design your banner at the exact size of the placement you bought, in your colours, with one round of changes.</p>' AS body,
                   'brush' AS icon, 'Design & Branding' AS category, 125.00 AS base_price, 'one_time' AS price_unit, 0 AS price_from, 55 AS sort_order, 1 AS is_active, NOW() AS created_at, NOW() AS updated_at
            UNION ALL SELECT 'facebook-instagram-ads', 'Facebook & Instagram Ads',
                   'Targeted Facebook and Instagram campaigns for Pearland customers, managed monthly.',
                   '<p>Strategy, ad copy, creative, audience targeting and weekly optimisation. You pay the ad spend to Meta directly; this is the management fee.</p>',
                   'campaign', 'Marketing', 399.00, 'monthly', 1, 91, 1, NOW(), NOW()
            UNION ALL SELECT 'google-ads-management', 'Google Ads Management',
                   'Google search and maps ads that put your business in front of people looking for it, managed monthly.',
                   '<p>Keyword research, ad copy, landing-page review and weekly optimisation. You pay the ad spend to Google directly; this is the management fee.</p>',
                   'google_ads', 'Marketing', 399.00, 'monthly', 1, 92, 1, NOW(), NOW()
            UNION ALL SELECT 'email-marketing-campaign', 'Email Marketing Campaign',
                   'A designed, written and sent email campaign to your customer list.',
                   '<p>Copywriting, design and sending, with a report on opens and clicks.</p>',
                   'mail', 'Marketing', 149.00, 'project', 1, 93, 1, NOW(), NOW()
            UNION ALL SELECT 'featured-email-blast', 'Featured Email Blast',
                   'Your business as the featured story in a Pearland411 email to our subscribers.',
                   '<p>One campaign featuring your business, offer or event to the Pearland411 mailing list.</p>',
                   'email_blast', 'Marketing', 99.00, 'one_time', 0, 94, 1, NOW(), NOW()
            UNION ALL SELECT 'sponsored-article', 'Sponsored Article',
                   'A story about your business published on Pearland411 and shared with our readers.',
                   '<p>We interview you, write the article and publish it on Pearland411, clearly marked as sponsored.</p>',
                   'article', 'Marketing', 150.00, 'one_time', 0, 95, 1, NOW(), NOW()
            UNION ALL SELECT 'business-spotlight-video', 'Business Spotlight Video',
                   'A short professional video that shows customers who you are and what you do.',
                   '<p>Filmed at your business and edited for your website, social media and your Pearland411 listing.</p>',
                   'video', 'Design & Branding', 395.00, 'project', 1, 96, 1, NOW(), NOW()
            UNION ALL SELECT 'professional-photography', 'Professional Photography',
                   'Photos of your business, team and products that make your listing and website stand out.',
                   '<p>An on-site shoot with edited, web-ready photos you can use everywhere.</p>',
                   'photo', 'Design & Branding', 250.00, 'project', 1, 97, 1, NOW(), NOW()
            UNION ALL SELECT 'reputation-management', 'Reputation Management',
                   'Get more good reviews, answer the bad ones, and keep an eye on what people say about you online.',
                   '<p>Review requests, monitoring and responses across Google, Facebook and Pearland411, with a monthly report.</p>',
                   'reviews', 'Marketing', 99.00, 'monthly', 1, 98, 1, NOW(), NOW()
            UNION ALL SELECT 'event-promotion', 'Event Promotion',
                   'Get the word out about your event on Pearland411 and beyond.',
                   '<p>A featured event listing, social posts and newsletter mention, put together for your event.</p>',
                   'event', 'Marketing', 150.00, 'project', 1, 99, 1, NOW(), NOW()
        ) AS seed
        WHERE NOT EXISTS (SELECT 1 FROM `services` s WHERE s.`slug` = seed.`slug`);
    END IF;
END //
DELIMITER ;
CALL pearland411_listing_packages();
DROP PROCEDURE pearland411_listing_packages;
