-- ---------------------------------------------------------------------------
-- 0021_blog_dates.sql
-- ---------------------------------------------------------------------------
-- Fixes the client report that "all blogs have the same date".
--
-- THE CAUSE
-- `Admin::add_blog()` never wrote `blogs`.`created_at` — the payload it builds
-- has user_id, name, description, status, isenquiry and slug, and nothing
-- else. (The public form, User::add_blog(), does write it, which is why only
-- admin-created posts were affected.) With no value supplied, the column took
-- whatever it defaults to: MySQL's zero date, or NULL.
--
-- The blog index then printed `date('d, M Y', strtotime($blog->created_at))`.
-- strtotime() returns FALSE for NULL, '' and the zero date, and date() prints
-- the epoch for FALSE — so every post with no date rendered the *same* string
-- ("01, Jan 1970", or "30, Nov -0001" from a zero date). Not the same date:
-- no date at all, printed identically.
--
-- WHAT THIS DOES
--   1. Makes `created_at` nullable with DEFAULT CURRENT_TIMESTAMP, so a write
--      path that forgets it still records the moment the row was created
--      rather than a placeholder.
--   2. Turns existing zero dates into NULL. There is no signal anywhere in the
--      row for what the real publication date was, so this deliberately does
--      not invent one — NULL is honest, and the views now omit the date line
--      instead of printing 1970. Admins can set the true date per post at
--      /admin/blog-list → Edit, which grew a "Publish date" field alongside
--      this migration.
--
-- Code changes that go with it: Admin::add_blog()/edit_blog() write and edit
-- created_at, and every view formats dates through us_date() (view_helper.php),
-- which returns '' for an unusable value rather than the epoch.
--
-- Idempotent: step 1 only alters the column when it is not already nullable
-- with a default, step 2 only touches zero dates. Safe to re-run.
--
-- Run after 0020:
--   mysql -u <user> -p <db> < database/migrations/0021_blog_dates.sql
--
-- BEFORE / AFTER CHECK
--   SELECT COUNT(*) AS undated FROM `blogs`
--    WHERE `created_at` IS NULL OR `created_at` = '0000-00-00 00:00:00';
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_fix_blog_dates;
DELIMITER //
CREATE PROCEDURE pearland411_fix_blog_dates()
BEGIN
    DECLARE col_exists   INT DEFAULT 0;
    DECLARE col_nullable VARCHAR(3) DEFAULT NULL;
    DECLARE col_default  VARCHAR(64) DEFAULT NULL;

    SELECT COUNT(*) INTO col_exists
      FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME   = 'blogs'
       AND COLUMN_NAME  = 'created_at';

    -- Older installs may not have the column at all.
    IF col_exists = 0 THEN
        ALTER TABLE `blogs`
            ADD COLUMN `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP;
    ELSE
        SELECT IS_NULLABLE, COLUMN_DEFAULT
          INTO col_nullable, col_default
          FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME   = 'blogs'
           AND COLUMN_NAME  = 'created_at';

        IF col_nullable <> 'YES' OR col_default IS NULL THEN
            ALTER TABLE `blogs`
                MODIFY COLUMN `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP;
        END IF;
    END IF;

    -- Zero dates become NULL so the views can tell "no date recorded" from a
    -- real one. Nothing is invented: an admin sets the true date per post.
    UPDATE `blogs`
       SET `created_at` = NULL
     WHERE `created_at` = '0000-00-00 00:00:00';
END //
DELIMITER ;
CALL pearland411_fix_blog_dates();
DROP PROCEDURE pearland411_fix_blog_dates;
