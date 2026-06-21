-- ---------------------------------------------------------------------------
-- 0007_site_settings.sql
-- ---------------------------------------------------------------------------
-- Site-wide key/value settings managed via /admin/setting:
--   * SEO / analytics tokens (GA4, GSC, Bing, Facebook App ID)
--   * Social-media profile URLs (FB, X/Twitter, IG, LinkedIn, YouTube)
--   * Google Maps API key
--   * Default Open Graph image
--   * Brand defaults (site title, description, contact phone/email)
--
-- The schema is generic enough to add new keys later without a fresh
-- migration. Values are stored as TEXT so we can hold long blobs
-- (description, OG image URL, comma-separated lists) without truncation.
--
-- Run once after 0006:
--   mysql -u <user> -p <db> < database/migrations/0007_site_settings.sql
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `site_settings` (
    `setting_key`   VARCHAR(80)  NOT NULL,
    `setting_value` TEXT         NULL,
    `updated_at`    DATETIME     NOT NULL,
    PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed the canonical keys so the admin form has placeholders for every
-- setting on first load. Empty values are fine — partials check before
-- emitting the corresponding meta/script tag.
INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
    ('site_title',                 'Pearland411 — Local Businesses, Events, Deals & News in Pearland, TX', NOW()),
    ('site_description',           'Pearland411 is your community guide to Pearland, Texas — find local businesses, upcoming events, deals, news and jobs all in one place.', NOW()),
    ('site_keywords',              'Pearland businesses, Pearland events, Pearland deals, Pearland news, Pearland TX directory', NOW()),
    ('site_og_image',              '', NOW()),
    ('contact_phone',              '281-482-2031', NOW()),
    ('contact_email',              'sales@pearland411.com', NOW()),
    -- SEO / Analytics
    ('google_analytics_id',        '', NOW()),
    ('google_site_verification',   '', NOW()),
    ('bing_site_verification',     '', NOW()),
    ('facebook_app_id',            '', NOW()),
    -- Google Maps
    ('google_maps_api_key',        '', NOW()),
    -- Social profile URLs
    ('social_facebook_url',        '', NOW()),
    ('social_twitter_url',         '', NOW()),
    ('social_instagram_url',       '', NOW()),
    ('social_linkedin_url',        '', NOW()),
    ('social_youtube_url',         '', NOW());
