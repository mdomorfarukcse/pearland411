-- ---------------------------------------------------------------------------
-- 0020_listing_status_normalise.sql
-- ---------------------------------------------------------------------------
-- Fixes the bug where a category box said "255 listings" and the category page
-- said "13".
--
-- THE CAUSE
-- `listings.listing_status` is read as 1 = published everywhere it matters —
-- the admin listings table, both user dashboards, the sitemap, and every
-- public query in Usermodel. But almost nothing WROTE 1:
--
--   * the admin CSV import hardcoded `listing_status => 0`
--     (application/controllers/Admin.php)
--   * the public add-listing form wrote the *string* 'Active' into an INT
--     column, which MySQL coerces to 0
--     (application/controllers/User.php)
--
-- So bulk-imported and user-submitted businesses were all stored unpublished.
-- The homepage / directory category counters filtered only `is_deleted`, so
-- they counted those rows; the listing queries also filtered
-- `listing_status = 1`, so they didn't. Both write paths are fixed in code
-- alongside this migration; this file repairs the rows already in the table.
--
-- WHAT IT DOES
--   1. Adds `listings.listing_status_backup` and copies the current value into
--      it, so the change is reversible.
--   2. Publishes every non-deleted listing that is currently 0 or NULL.
--   3. Backfills `listing_categories.category_slug` where it is empty, using
--      the same rule as pearland_slug() in application/helpers/view_helper.php.
--      Without a stored slug, a category whose name contains punctuation
--      ("Medical & Dental") got a different URL from each of the three places
--      that link to it, and one of them resolved to an empty page.
--
-- ROLLBACK (step 2 only)
--   UPDATE `listings` SET `listing_status` = `listing_status_backup`
--    WHERE `listing_status_backup` IS NOT NULL;
--
-- Idempotent: step 2 is guarded on the backup column not yet existing, so a
-- second run cannot re-publish something an operator has since switched off.
-- Step 3 only ever touches rows whose slug is still empty. Safe to re-run.
--
-- Run once after 0019:
--   mysql -u <user> -p <db> < database/migrations/0020_listing_status_normalise.sql
--
-- BEFORE / AFTER CHECK
--   SELECT `listing_status`, COUNT(*) FROM `listings`
--    WHERE `is_deleted` = 0 GROUP BY `listing_status`;
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_normalise_listing_status;
DELIMITER //
CREATE PROCEDURE pearland411_normalise_listing_status()
BEGIN
    -- Every character pearland_slug() strips: it keeps [a-z0-9-] and turns
    -- spaces into hyphens, so this is printable ASCII punctuation minus '-'.
    DECLARE v_punct VARCHAR(64) DEFAULT '!"#$%&''()*+,./:;<=>?@[\\]^_`{|}~';
    DECLARE v_i     INT         DEFAULT 1;

    -- ---- 1 + 2: back up, then publish ----------------------------------
    -- The backup column IS the guard. Once it exists this whole block is
    -- skipped, so re-running can never resurrect a listing that an operator
    -- has deliberately unpublished since.
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'listings'
          AND COLUMN_NAME = 'listing_status_backup'
    ) THEN
        ALTER TABLE `listings`
            ADD COLUMN `listing_status_backup` TINYINT(1) NULL DEFAULT NULL
                COMMENT 'listing_status as it stood before migration 0020 - rollback source';

        UPDATE `listings` SET `listing_status_backup` = `listing_status`;

        UPDATE `listings`
           SET `listing_status` = 1
         WHERE (`listing_status` IS NULL OR `listing_status` = 0)
           AND (`is_deleted` IS NULL OR `is_deleted` = 0);
    END IF;

    -- ---- 3: category slugs ---------------------------------------------
    -- Guard-add the column first: some older installs never had it, and the
    -- directory then has to fall back to matching on the category name.
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'listing_categories'
          AND COLUMN_NAME = 'category_slug'
    ) THEN
        ALTER TABLE `listing_categories`
            ADD COLUMN `category_slug` VARCHAR(191) NULL DEFAULT NULL;
    END IF;

    -- Build the slugs one transformation at a time in a scratch table. Doing
    -- it as thirty nested REPLACE()s in a single expression would be both
    -- unreadable and easy to mis-nest; the table only ever holds the handful
    -- of categories that still have no slug, so the extra passes cost nothing.
    DROP TEMPORARY TABLE IF EXISTS `pearland411_slug_calc`;
    CREATE TEMPORARY TABLE `pearland411_slug_calc` AS
    SELECT `category_id`, LOWER(TRIM(`category_name`)) AS `slug`
      FROM `listing_categories`
     WHERE `category_slug` IS NULL OR `category_slug` = '';

    UPDATE `pearland411_slug_calc` SET `slug` = REPLACE(`slug`, ' ', '-');

    WHILE v_i <= CHAR_LENGTH(v_punct) DO
        UPDATE `pearland411_slug_calc`
           SET `slug` = REPLACE(`slug`, SUBSTRING(v_punct, v_i, 1), '');
        SET v_i = v_i + 1;
    END WHILE;

    -- Collapse the hyphen runs the removals left behind ("bars,-and--pubs"),
    -- then trim the ends. Four passes flatten a run of up to 16.
    SET v_i = 0;
    WHILE v_i < 4 DO
        UPDATE `pearland411_slug_calc` SET `slug` = REPLACE(`slug`, '--', '-');
        SET v_i = v_i + 1;
    END WHILE;
    UPDATE `pearland411_slug_calc` SET `slug` = TRIM(BOTH '-' FROM `slug`);

    -- Discard anything that didn't reduce to a usable slug (a name that was
    -- all punctuation, or non-ASCII this pass can't transliterate) and
    -- anything that would collide with a slug already in use. Those rows keep
    -- an empty slug and stay on the name-matching fallback, rather than being
    -- given a wrong-but-plausible URL that lands on another category.
    DELETE FROM `pearland411_slug_calc`
     WHERE `slug` IS NULL
        OR `slug` = ''
        OR `slug` NOT REGEXP '^[a-z0-9]([a-z0-9-]*[a-z0-9])?$';

    DELETE c FROM `pearland411_slug_calc` c
     WHERE EXISTS (
        SELECT 1 FROM `listing_categories` lc
         WHERE lc.`category_slug` = c.`slug`
    );

    -- Collisions *within* this batch. A temporary table can only be opened
    -- once per statement, so the duplicate slugs go into a second scratch
    -- table rather than a subquery over the first.
    DROP TEMPORARY TABLE IF EXISTS `pearland411_slug_dupes`;
    CREATE TEMPORARY TABLE `pearland411_slug_dupes` AS
    SELECT `slug` FROM `pearland411_slug_calc`
     GROUP BY `slug` HAVING COUNT(*) > 1;

    DELETE c FROM `pearland411_slug_calc` c
      JOIN `pearland411_slug_dupes` d ON d.`slug` = c.`slug`;

    UPDATE `listing_categories` lc
      JOIN `pearland411_slug_calc` c ON c.`category_id` = lc.`category_id`
       SET lc.`category_slug` = c.`slug`
     WHERE lc.`category_slug` IS NULL OR lc.`category_slug` = '';

    DROP TEMPORARY TABLE IF EXISTS `pearland411_slug_dupes`;
    DROP TEMPORARY TABLE IF EXISTS `pearland411_slug_calc`;
END //
DELIMITER ;
CALL pearland411_normalise_listing_status();
DROP PROCEDURE pearland411_normalise_listing_status;
