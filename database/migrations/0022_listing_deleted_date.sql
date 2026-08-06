-- ---------------------------------------------------------------------------
-- 0022_listing_deleted_date.sql
-- ---------------------------------------------------------------------------
-- **Fixes "Deleted listings cannot be restored — HTTP 500 Internal Server
-- Error".**
--
-- `Admin::restore_listing()` read the trashed row, set two properties on the
-- row object, and wrote the WHOLE object back:
--
--     $listings = $this->adminmodel->getSingleValue($slug, 'listing_slug', 'listings');
--     $listings->is_deleted   = 0;
--     $listings->deleted_date = date('Y-m-d H:i:s');
--     $this->adminmodel->update($slug, $listings, 'listing_slug', 'listings');
--
-- `deleted_date` appeared exactly ONCE in the entire application — that line.
-- Nothing created the column and nothing else wrote it: the soft delete set
-- only `is_deleted = 1`, and the trash page's "Deleted Date" heading was being
-- filled with `postal_code`. So the UPDATE emitted `SET … deleted_date = '…'`
-- against a column that does not exist → MySQL 1054 → the 500.
--
-- The column is worth having rather than dropping from the code: a trash page
-- that cannot say when something was deleted is not much of a trash page. The
-- restore path now CLEARS it (the old code stamped it with "now" while
-- restoring, which was backwards), and `softDeleteListing()` sets it.
--
-- Existing trashed rows get NULL — there is no signal in the row for when they
-- were deleted, and the view prints '' rather than inventing a date.
--
-- Idempotent: guarded through INFORMATION_SCHEMA, same pattern as 0018.
-- Safe to re-run.
--
-- Run once after 0021:
--   mysql -u <user> -p <db> < database/migrations/0022_listing_deleted_date.sql
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_add_listing_deleted_date;
DELIMITER //
CREATE PROCEDURE pearland411_add_listing_deleted_date()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'listings'
          AND COLUMN_NAME = 'deleted_date'
    ) THEN
        ALTER TABLE `listings`
            ADD COLUMN `deleted_date` DATETIME NULL DEFAULT NULL
                COMMENT 'When the listing was moved to trash; NULL once restored';
    END IF;

    -- A listing that is not in the trash must not carry a deletion stamp.
    UPDATE `listings`
       SET `deleted_date` = NULL
     WHERE `deleted_date` IS NOT NULL
       AND (`is_deleted` = 0 OR `is_deleted` IS NULL);
END //
DELIMITER ;
CALL pearland411_add_listing_deleted_date();
DROP PROCEDURE pearland411_add_listing_deleted_date;
