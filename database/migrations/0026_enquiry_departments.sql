-- ---------------------------------------------------------------------------
-- 0026_enquiry_departments.sql
-- ---------------------------------------------------------------------------
-- **Fixes "client message doesn't go to marketing package, and other goes to
-- marketing package versus contact form" + "remove all names, no longer here".**
--
-- Two problems, one table.
--
-- 1. EVERY public message landed in the same pile. The callback panel on
--    /pricing-plan ("Would you rather we set it up for you?") and the
--    /contact-us form both post to /contact-submit, and both were written with
--    `enquiry_type = 'contact'`. The only thing telling them apart was the
--    free-text `enquiry_source` string, which nothing filtered on. So a person
--    leaving their phone number to BUY A PACKAGE arrived in admin looking
--    exactly like someone asking a general question, and could sit unread
--    among them. Package leads now file as `enquiry_type = 'package'` and get
--    their own tab in /admin/enquiries.
--
-- 2. The "Ask for someone in particular?" dropdown named nine individual sales
--    reps, most of whom no longer work here. A caller could ask by name for
--    somebody who left, and the enquiry then waited on a person who was never
--    going to pick it up. Enquiries now address a DEPARTMENT — Sales, Customer
--    Service, Billing, Advertising & Marketing, Technical Support — which
--    outlives whoever staffs it. That is the whole point of routing to one.
--
-- This migration:
--   * adds `enquiries`.`enquiry_department`
--   * indexes `enquiry_type` so the admin tabs filter on a key
--   * reclassifies the callback requests already sitting in the table
--   * removes the stale `sales_representatives` setting, which is where the
--     departed staff names live on production (they are NOT in the code — the
--     code's fallback list was overridden by a row in `site_settings`)
--
-- `enquiries` predates this repo's migrations, so every step is guarded on the
-- table and the column existing. Idempotent — INFORMATION_SCHEMA guards
-- throughout. Safe to re-run.
--
-- The department list itself is NOT seeded: blank means "use the built-in
-- list" in enquiry_departments() (application/helpers/view_helper.php), so
-- there is one source of truth until an operator overrides it at
-- /admin/setting -> Enquiry routing.
--
-- Run once after 0025:
--   mysql -u <user> -p <db> < database/migrations/0026_enquiry_departments.sql
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_add_enquiry_departments;
DELIMITER //
CREATE PROCEDURE pearland411_add_enquiry_departments()
BEGIN
    -- Legacy table, not created by any migration in this repo. On an install
    -- that somehow lacks it there is nothing to alter and nothing to fix.
    IF EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'enquiries'
    ) THEN

        -- 1. The column. NULL = filed before departments existed.
        IF NOT EXISTS (
            SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'enquiries'
              AND COLUMN_NAME = 'enquiry_department'
        ) THEN
            ALTER TABLE `enquiries`
                ADD COLUMN `enquiry_department` VARCHAR(80) NULL DEFAULT NULL
                    COMMENT 'Sales Department / Customer Service / Billing Department / ...';
        END IF;

        -- 2. An index behind the admin tabs. /admin/enquiries now filters on
        --    enquiry_type, and the model's SELECT was previously unordered and
        --    unfiltered, so there was never a reason for a key here.
        IF NOT EXISTS (
            SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'enquiries'
              AND INDEX_NAME = 'enquiries_type_idx'
        ) THEN
            ALTER TABLE `enquiries` ADD KEY `enquiries_type_idx` (`enquiry_type`);
        END IF;

        -- 3. Reclassify what is already in the table, so the new tabs are not
        --    empty on day one and the existing leads stop hiding among the
        --    general messages. Only touches rows the callback panel wrote —
        --    `enquiry_source` is the string User::contact_submit() has always
        --    set for it, 'Pricing plan — callback'. Matched with LIKE on the
        --    ASCII prefix rather than the whole string: the em dash is the one
        --    character here whose bytes depend on the client charset, and a
        --    `mysql <` run that negotiates latin1 would match nothing at all
        --    and silently leave every lead misfiled. Listing/blog/event
        --    enquiries are left alone.
        UPDATE `enquiries`
           SET `enquiry_type` = 'package'
         WHERE `enquiry_source` LIKE 'Pricing plan%'
           AND `enquiry_type`   = 'contact';

        -- 4. Backfill the department. A package callback was a sales lead; a
        --    contact-form message was not. Anything else keeps NULL rather
        --    than being guessed at.
        UPDATE `enquiries`
           SET `enquiry_department` = 'Sales Department'
         WHERE `enquiry_type` = 'package'
           AND (`enquiry_department` IS NULL OR `enquiry_department` = '');

        UPDATE `enquiries`
           SET `enquiry_department` = 'Customer Service'
         WHERE `enquiry_type` = 'contact'
           AND (`enquiry_department` IS NULL OR `enquiry_department` = '');

    END IF;
END//
DELIMITER ;

CALL pearland411_add_enquiry_departments();
DROP PROCEDURE IF EXISTS pearland411_add_enquiry_departments;


-- ===========================================================================
-- 5. Retire the named-rep list
-- ===========================================================================
-- The nine names shown on the live site are not in the code — the code's
-- fallback was overridden by this `site_settings` row. sales_representatives()
-- is gone, so the row is now dead weight that would only confuse the next
-- person to read the settings table. The historical text of past enquiries is
-- NOT touched: a message that says "Asked for: <name>" stays as it was sent,
-- because it is a record of what somebody actually wrote.
DELETE FROM `site_settings` WHERE `setting_key` = 'sales_representatives';


-- ===========================================================================
-- 6. Confirm what changed
-- ===========================================================================
-- Expect the callback requests to appear as `package`, each with a department.
SELECT
    `enquiry_type`,
    COALESCE(NULLIF(`enquiry_department`, ''), '(none)') AS department,
    COALESCE(NULLIF(`enquiry_source`, ''), '(no page)')  AS came_from,
    COUNT(*)                                             AS messages
FROM `enquiries`
GROUP BY `enquiry_type`, `enquiry_department`, `enquiry_source`
ORDER BY `enquiry_type`, messages DESC;
