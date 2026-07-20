-- ---------------------------------------------------------------------------
-- seeds/plan_capability_defaults.sql   (OPTIONAL — not part of the numbered
-- migration run order; run by hand once if you want sensible starting values)
-- ---------------------------------------------------------------------------
-- Migration 0010 adds the field-level capability columns to `plans` with a
-- DEFAULT of 0 (locked). For a fresh install that is correct. But an existing
-- site already has paid plans, and once Sprint 3 gating goes live those plans
-- would unlock nothing until an admin ticks the new boxes. This seed gives the
-- three stock plans (Basic / Standard / Premium) reasonable capabilities so
-- paid listings keep their rich fields. Adjust freely in /admin/pricing — this
-- file is a convenience, not a source of truth.
--
-- Free (claimed but no active plan) always keeps name / phone / email editable;
-- these capabilities are what a PAID plan unlocks on top of that.
--
-- Safe to run once; it only UPDATEs by id and won't insert or delete. If your
-- plan ids differ, edit the WHERE clauses. Run AFTER 0010_plan_capabilities.sql.
-- ---------------------------------------------------------------------------

-- Basic — photos (a few) + map. No services / video / extra details.
UPDATE `plans` SET
    `can_photos` = 1, `photo_limit` = 3, `can_map` = 1,
    `can_services` = 0, `allow_video` = 0, `can_extra_details` = 0
  WHERE `slug` = 'basic-plan';

-- Standard — photos (more) + map + services + video.
UPDATE `plans` SET
    `can_photos` = 1, `photo_limit` = 10, `can_map` = 1,
    `can_services` = 1, `allow_video` = 1, `can_extra_details` = 0
  WHERE `slug` = 'standard-plan';

-- Premium — everything, unlimited photos.
UPDATE `plans` SET
    `can_photos` = 1, `photo_limit` = -1, `can_map` = 1,
    `can_services` = 1, `allow_video` = 1, `can_extra_details` = 1
  WHERE `slug` = 'premium-plan';
