-- ---------------------------------------------------------------------------
-- 0011_service_registry.sql
-- ---------------------------------------------------------------------------
-- Admin "service registry" — a master on/off switch per optional content
-- section (news / events / blog / products / videos / classifieds / real
-- estate). Rides the existing generic `site_settings` key/value table (0007)
-- and is read everywhere via the autoloaded site_setting() helper, so no new
-- table and no schema change are needed.
--
-- These drive BOTH the admin sidebar (admin can add/delete that content type
-- only when its service is active) AND the public navigation. They also
-- satisfy the client's "hide events / apartments / etc. for now" request.
--
-- Seeded DEACTIVATED ('0'): the client's model is "admin activates a service
-- to turn it on." Nothing about the core directory / coupons / advertising is
-- gated here — only these optional sections. Flip any of them on from
-- /admin/setting when ready.
--
-- Gating code reads e.g. site_setting('service_events_enabled','1'), so if
-- this migration has NOT been run the sections stay visible (current
-- behaviour); once run, they default off until the admin activates them.
--
-- INSERT IGNORE keeps it idempotent (PK = setting_key). Run after 0010:
--   mysql -u <user> -p <db> < database/migrations/0011_service_registry.sql
-- ---------------------------------------------------------------------------

INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
    ('service_events_enabled',      '0', NOW()),
    ('service_realestate_enabled',  '0', NOW()),
    ('service_classifieds_enabled', '0', NOW()),
    ('service_news_enabled',        '0', NOW()),
    ('service_blog_enabled',        '0', NOW()),
    ('service_products_enabled',    '0', NOW()),
    ('service_videos_enabled',      '0', NOW());
