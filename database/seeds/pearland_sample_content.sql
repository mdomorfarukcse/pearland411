-- ===========================================================================
-- pearland_sample_content.sql
-- ===========================================================================
-- Demo content for Pearland, Texas so the redesigned homepage has something
-- real to render in every section: categories, listings (including featured),
-- news, videos, coupons and upcoming events.
--
-- WHY YOU MIGHT WANT THIS
--   The homepage now shows an honest empty state wherever the database has
--   no rows. That's correct behaviour, but it makes the page hard to judge
--   visually. Import this, look at the page, then roll it back (see the very
--   bottom) once your own content is in.
--
-- HOW TO RUN
--   mysql -u <user> -p <database> < database/seeds/pearland_sample_content.sql
--   …or paste it into phpMyAdmin ▸ SQL.
--
-- SAFE TO RE-RUN
--   Every INSERT is guarded by NOT EXISTS on a unique-ish column, so running
--   this twice does not duplicate anything.
--
-- BEFORE YOU RUN — two things to know
--
--   1. `listings.listing_status` = 0 means PUBLISHED in this codebase.
--      That looks backwards, and it is inconsistent with news/videos/events
--      (which use 1 = published), but `getLatestListings()` and
--      `getFeaturedListings()` both filter on 0, so the seed matches them.
--      If your directory pages disagree, check that column first.
--
--   2. These tables predate the migrations, so their exact column list isn't
--      in the repo. This seed only sets columns the application code is known
--      to read. If your copy has extra NOT NULL columns with no default,
--      MySQL will name them in the error — add them to the column list.
--      To see what you have:
--          SHOW COLUMNS FROM `listings`;
--          SHOW COLUMNS FROM `listing_categories`;
--          SHOW COLUMNS FROM `news`;
--          SHOW COLUMNS FROM `video`;
--          SHOW COLUMNS FROM `coupons`;
--          SHOW COLUMNS FROM `events`;
--
-- Listings are seeded WITHOUT photos on purpose, so you can see the branded
-- "No photo yet" placeholder that replaced the broken-image tiles.
-- ===========================================================================

SET NAMES utf8mb4;
SET SQL_MODE = '';


-- ---------------------------------------------------------------------------
-- 1. Categories
-- ---------------------------------------------------------------------------

INSERT INTO `listing_categories` (`category_name`, `category_slug`)
SELECT * FROM (
    SELECT 'Home Services'          AS category_name, 'home-services'          AS category_slug
    UNION ALL SELECT 'Medical & Dental',       'medical-dental'
    UNION ALL SELECT 'Restaurants & Food',     'restaurants-food'
    UNION ALL SELECT 'Auto Services',          'auto-services'
    UNION ALL SELECT 'Beauty & Personal Care', 'beauty-personal-care'
    UNION ALL SELECT 'Real Estate',            'real-estate'
    UNION ALL SELECT 'Professional Services',  'professional-services'
    UNION ALL SELECT 'Health & Fitness',       'health-fitness'
) AS seed
WHERE NOT EXISTS (
    SELECT 1 FROM `listing_categories` c WHERE c.`category_slug` = seed.category_slug
);


-- ---------------------------------------------------------------------------
-- 2. Business listings
--    12 businesses on real Pearland streets. The first three are marked
--    featured/spotlight so the "Featured Businesses" row has content.
-- ---------------------------------------------------------------------------

INSERT INTO `listings`
    (`user_id`, `listing_name`, `listing_slug`, `listing_description`,
     `category_id`, `listing_status`, `is_deleted`,
     `featured_listing`, `spotlight_listing`, `created_at`)
SELECT
    0,
    seed.listing_name,
    seed.listing_slug,
    seed.listing_description,
    COALESCE((SELECT c.category_id FROM `listing_categories` c
              WHERE c.category_slug = seed.category_slug LIMIT 1), 1),
    0,   -- 0 = published, see note at top
    0,
    seed.featured_listing,
    seed.spotlight_listing,
    DATE_SUB(NOW(), INTERVAL seed.age_days DAY)
FROM (
    SELECT 'Broadway Family Dental' AS listing_name,
           'broadway-family-dental' AS listing_slug,
           'Family and cosmetic dentistry on Broadway Street, serving Pearland families for over fifteen years. Same-day emergency appointments available.' AS listing_description,
           'medical-dental' AS category_slug,
           1 AS featured_listing, 0 AS spotlight_listing, 2 AS age_days
    UNION ALL SELECT 'Shadow Creek Auto Care', 'shadow-creek-auto-care',
        'Full-service auto repair and Texas state inspections near Shadow Creek Ranch. ASE-certified technicians, free estimates.',
        'auto-services', 1, 0, 4
    UNION ALL SELECT 'Pearland Parkway Plumbing', 'pearland-parkway-plumbing',
        'Licensed plumbers covering Pearland and Friendswood. Water heaters, slab leaks, drain cleaning and repiping — 24-hour emergency service.',
        'home-services', 0, 1, 5
    UNION ALL SELECT 'Silverlake Family Medicine', 'silverlake-family-medicine',
        'Primary care for the whole family in Silverlake. Annual physicals, chronic care management and same-week sick visits.',
        'medical-dental', 0, 0, 8
    UNION ALL SELECT 'Main Street Barber Co.', 'main-street-barber-co',
        'Classic cuts, hot-towel shaves and beard trims on Main Street. Walk-ins welcome seven days a week.',
        'beauty-personal-care', 0, 0, 11
    UNION ALL SELECT 'Manvel Road Tree Service', 'manvel-road-tree-service',
        'Tree trimming, removal and stump grinding across Brazoria County. Fully insured with free on-site quotes.',
        'home-services', 0, 0, 14
    UNION ALL SELECT 'Dixie Farm Fitness', 'dixie-farm-fitness',
        'Neighbourhood gym on Dixie Farm Road with strength equipment, group classes and personal training. Open 5am to 10pm.',
        'health-fitness', 0, 0, 18
    UNION ALL SELECT 'Barry Rose Realty Group', 'barry-rose-realty-group',
        'Residential sales and leasing across Pearland, Manvel and Friendswood. Free home valuations for local sellers.',
        'real-estate', 0, 0, 21
    UNION ALL SELECT 'Clear Creek Tax & Bookkeeping', 'clear-creek-tax-bookkeeping',
        'Small-business bookkeeping, payroll and tax preparation. Year-round support, not just in April.',
        'professional-services', 0, 0, 25
    UNION ALL SELECT 'Town Center Coffee Roasters', 'town-center-coffee-roasters',
        'Small-batch coffee roasted in Pearland, plus breakfast tacos and pastries. Free wifi and plenty of seating.',
        'restaurants-food', 0, 0, 29
    UNION ALL SELECT 'Green Tee Lawn & Landscape', 'green-tee-lawn-landscape',
        'Weekly lawn maintenance, landscape design and irrigation repair for Pearland homes and businesses.',
        'home-services', 0, 0, 34
    UNION ALL SELECT 'Southdown Pediatric Dentistry', 'southdown-pediatric-dentistry',
        'Gentle dental care for infants, children and teens. Kid-friendly office with a play area and TVs in every room.',
        'medical-dental', 0, 0, 40
) AS seed
WHERE NOT EXISTS (
    SELECT 1 FROM `listings` l WHERE l.`listing_slug` = seed.listing_slug
);


-- ---------------------------------------------------------------------------
-- 3. News
-- ---------------------------------------------------------------------------

INSERT INTO `news` (`user_id`, `news_title`, `news_slug`, `news_description`, `news_status`)
SELECT 0, seed.news_title, seed.news_slug, seed.news_description, 1
FROM (
    SELECT 'City of Pearland approves Broadway Street improvements' AS news_title,
           'pearland-approves-broadway-street-improvements'         AS news_slug,
           'Pearland City Council has approved a package of improvements along Broadway Street, including resurfacing, new signal timing and upgraded pedestrian crossings. Work is expected to be staged over several months to limit disruption to local businesses.' AS news_description
    UNION ALL SELECT 'New retail development breaks ground near Pearland Town Center',
        'new-retail-development-pearland-town-center',
        'A mixed-use retail development has broken ground near Pearland Town Center, bringing additional restaurant and shop space to the area. Several tenants have already been announced, with the first units expected to open next year.'
    UNION ALL SELECT 'Pearland Parks and Recreation announces expanded summer programme',
        'pearland-parks-expanded-summer-programme',
        'Pearland Parks and Recreation has published an expanded summer schedule with youth sports camps, swimming lessons and adult fitness classes across the city''s recreation centres. Registration opens to residents first.'
) AS seed
WHERE NOT EXISTS (
    SELECT 1 FROM `news` n WHERE n.`news_slug` = seed.news_slug
);


-- ---------------------------------------------------------------------------
-- 4. Videos
--    NOTE: video_code is left empty. The homepage does not use it, but the
--    video detail page embeds it — paste a real YouTube ID in before
--    relying on /video_details.
-- ---------------------------------------------------------------------------

INSERT INTO `video` (`video_title`, `video_slug`, `video_description`, `video_code`, `video_status`)
SELECT seed.video_title, seed.video_slug, seed.video_description, '', 1
FROM (
    SELECT 'Inside Pearland''s new farmers market'      AS video_title,
           'inside-pearlands-new-farmers-market'        AS video_slug,
           'A walk through the weekly Pearland farmers market, meeting the growers and makers who set up there every Saturday morning.' AS video_description
    UNION ALL SELECT 'Small business spotlight: Main Street',
        'small-business-spotlight-main-street',
        'Local owners along Main Street talk about starting up in Pearland, what has changed over the last decade, and what they would tell someone opening today.'
    UNION ALL SELECT 'Pearland then and now: Broadway Street',
        'pearland-then-and-now-broadway-street',
        'Archive photographs and present-day footage tracing how Broadway Street has developed from a quiet road into one of the city''s main commercial corridors.'
) AS seed
WHERE NOT EXISTS (
    SELECT 1 FROM `video` v WHERE v.`video_slug` = seed.video_slug
);


-- ---------------------------------------------------------------------------
-- 5. Coupons
--    Dates are relative to today, so these stay live for 45 days after import
--    and never need editing to keep the homepage populated.
--    coupon_user_id attaches to the lowest existing user id.
-- ---------------------------------------------------------------------------

INSERT INTO `coupons`
    (`coupon_user_id`, `coupon_name`, `coupon_code`,
     `coupon_start_date`, `coupon_end_date`, `coupon_status`, `coupon_cdt`)
SELECT
    COALESCE((SELECT MIN(u.user_id) FROM `user` u), 0),
    seed.coupon_name, seed.coupon_code,
    CURDATE(), DATE_ADD(CURDATE(), INTERVAL 45 DAY), 1, NOW()
FROM (
    SELECT 'New patient exam, x-rays and cleaning' AS coupon_name, '20% OFF' AS coupon_code
    UNION ALL SELECT 'Full synthetic oil change with tyre rotation', '$25 OFF'
    UNION ALL SELECT 'Water heater flush with any service call',     'FREE'
    UNION ALL SELECT 'First month of lawn maintenance',              '15% OFF'
) AS seed
WHERE NOT EXISTS (
    SELECT 1 FROM `coupons` c WHERE c.`coupon_name` = seed.coupon_name
);


-- ---------------------------------------------------------------------------
-- 6. Events
--    Dated forward from today so they always satisfy the "upcoming" filter
--    (event_start_date >= CURDATE()).
-- ---------------------------------------------------------------------------

INSERT INTO `events`
    (`user_id`, `event_name`, `event_slug`, `event_address`,
     `event_start_date`, `event_time`, `event_description`, `event_status`)
SELECT
    0, seed.event_name, seed.event_slug, seed.event_address,
    DATE_ADD(CURDATE(), INTERVAL seed.in_days DAY),
    seed.event_time, seed.event_description, 1
FROM (
    SELECT 'Pearland Farmers Market'  AS event_name,
           'pearland-farmers-market'  AS event_slug,
           'Zychlinski Park, Pearland, TX' AS event_address,
           6 AS in_days, '9:00 AM – 1:00 PM' AS event_time,
           'Weekly market with local growers, bakers and makers. Free to attend, family friendly, plenty of parking.' AS event_description
    UNION ALL SELECT 'Small Business Networking Breakfast', 'small-business-networking-breakfast',
        'Pearland Chamber of Commerce, Pearland, TX', 13, '7:30 AM – 9:00 AM',
        'Monthly breakfast for Pearland business owners. Short round of introductions followed by open networking. Coffee and breakfast provided.'
    UNION ALL SELECT 'Free Community Health Screening', 'free-community-health-screening',
        'Pearland Recreation Center, Pearland, TX', 22, '10:00 AM – 2:00 PM',
        'Free blood pressure, glucose and cholesterol screening run by local clinics. No appointment needed, walk-ins welcome.'
) AS seed
WHERE NOT EXISTS (
    SELECT 1 FROM `events` e WHERE e.`event_slug` = seed.event_slug
);


-- ---------------------------------------------------------------------------
-- 7. Turn on the content services the homepage links to
--    Migration 0011 seeds these OFF. While a service is off, its page 404s,
--    so the homepage correctly hides that section. Switch on the ones you
--    want visible (also available in /admin/setting ▸ Services).
-- ---------------------------------------------------------------------------

INSERT INTO `site_settings` (`setting_key`, `setting_value`)
VALUES
    ('service_news_enabled',   '1'),
    ('service_videos_enabled', '1'),
    ('service_events_enabled', '1')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);


-- ---------------------------------------------------------------------------
-- 8. Check what landed
-- ---------------------------------------------------------------------------

SELECT 'categories' AS what, COUNT(*) AS rows_now FROM `listing_categories`
UNION ALL SELECT 'listings (published)', COUNT(*) FROM `listings` WHERE `listing_status` = 0 AND `is_deleted` = 0
UNION ALL SELECT 'listings (featured)',  COUNT(*) FROM `listings` WHERE `listing_status` = 0 AND `is_deleted` = 0 AND (`featured_listing` = 1 OR `spotlight_listing` = 1)
UNION ALL SELECT 'news (published)',     COUNT(*) FROM `news`   WHERE `news_status` = 1
UNION ALL SELECT 'videos (published)',   COUNT(*) FROM `video`  WHERE `video_status` = 1
UNION ALL SELECT 'coupons (live today)', COUNT(*) FROM `coupons` WHERE `coupon_status` = 1 AND `coupon_start_date` <= CURDATE() AND `coupon_end_date` >= CURDATE()
UNION ALL SELECT 'events (upcoming)',    COUNT(*) FROM `events` WHERE `event_status` = 1 AND `event_start_date` >= CURDATE();


-- ===========================================================================
-- ROLLBACK — removes only what this file added
-- ===========================================================================
-- Run these to take the demo content back out. Categories are left alone on
-- purpose: your real listings may have been filed under them by then.
--
-- DELETE FROM `listings` WHERE `listing_slug` IN (
--     'broadway-family-dental','shadow-creek-auto-care','pearland-parkway-plumbing',
--     'silverlake-family-medicine','main-street-barber-co','manvel-road-tree-service',
--     'dixie-farm-fitness','barry-rose-realty-group','clear-creek-tax-bookkeeping',
--     'town-center-coffee-roasters','green-tee-lawn-landscape','southdown-pediatric-dentistry');
--
-- DELETE FROM `news` WHERE `news_slug` IN (
--     'pearland-approves-broadway-street-improvements',
--     'new-retail-development-pearland-town-center',
--     'pearland-parks-expanded-summer-programme');
--
-- DELETE FROM `video` WHERE `video_slug` IN (
--     'inside-pearlands-new-farmers-market',
--     'small-business-spotlight-main-street',
--     'pearland-then-and-now-broadway-street');
--
-- DELETE FROM `coupons` WHERE `coupon_name` IN (
--     'New patient exam, x-rays and cleaning',
--     'Full synthetic oil change with tyre rotation',
--     'Water heater flush with any service call',
--     'First month of lawn maintenance');
--
-- DELETE FROM `events` WHERE `event_slug` IN (
--     'pearland-farmers-market','small-business-networking-breakfast',
--     'free-community-health-screening');
-- ===========================================================================
