-- ---------------------------------------------------------------------------
-- 0034_review_dedupe.sql
-- ---------------------------------------------------------------------------
-- **"Review submit → page isn't working; duplicates on Back"** (tracker #18).
--
-- User::submit_review() inserted without ever checking for an earlier review
-- (Usermodel::hasUserReviewed() existed and was never called), so the Back
-- button, a refresh or a double click each added another copy — the
-- Y2j-Test listing shows several identical reviews from one member, and
-- every copy counted towards the business's average.
--
-- The controller now updates a member's existing review instead of adding a
-- second one. This migration clears the copies already stored and adds the
-- key that makes a second copy impossible:
--
--   1. Copies a member's older reviews of the same listing into
--      `listing_reviews_dedupe_backup` (same columns, so nothing is lost and
--      any row can be put back by hand).
--   2. Deletes them, keeping the NEWEST review per (listing_id, user_id).
--      Only rows with a real user_id (> 0) are considered — reviews are
--      login-only, and a legacy row with no member is left alone.
--   3. Adds UNIQUE KEY `listing_reviews_one_per_member` (listing_id, user_id)
--      — but only if no duplicate pair remains (e.g. legacy user_id = 0
--      rows), so the migration can never fail half-way on live data.
--   4. Refreshes listings.average_rating / total_reviews where those cache
--      columns exist.
--
-- Idempotent — guarded throughout; a re-run finds nothing to move.
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_review_dedupe;
DELIMITER //
CREATE PROCEDURE pearland411_review_dedupe()
BEGIN
    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'listing_reviews') THEN

        CREATE TABLE IF NOT EXISTS `listing_reviews_dedupe_backup` LIKE `listing_reviews`;

        INSERT IGNORE INTO `listing_reviews_dedupe_backup`
        SELECT r.* FROM `listing_reviews` r
          JOIN `listing_reviews` n
            ON n.`listing_id` = r.`listing_id`
           AND n.`user_id`    = r.`user_id`
           AND n.`review_id`  > r.`review_id`
         WHERE r.`user_id` > 0;

        DELETE r FROM `listing_reviews` r
          JOIN `listing_reviews` n
            ON n.`listing_id` = r.`listing_id`
           AND n.`user_id`    = r.`user_id`
           AND n.`review_id`  > r.`review_id`
         WHERE r.`user_id` > 0;

        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'listing_reviews'
                          AND INDEX_NAME = 'listing_reviews_one_per_member')
           AND NOT EXISTS (SELECT 1 FROM (
                    SELECT `listing_id`, `user_id` FROM `listing_reviews`
                     GROUP BY `listing_id`, `user_id` HAVING COUNT(*) > 1) AS d) THEN
            ALTER TABLE `listing_reviews`
                ADD UNIQUE KEY `listing_reviews_one_per_member` (`listing_id`, `user_id`);
        END IF;

        IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'listings'
                      AND COLUMN_NAME = 'average_rating')
           AND EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'listings'
                      AND COLUMN_NAME = 'total_reviews') THEN
            UPDATE `listings` l
              LEFT JOIN (SELECT `listing_id`, ROUND(AVG(`rating`), 1) AS avg_r, COUNT(*) AS n
                           FROM `listing_reviews` WHERE `review_status` = 1
                          GROUP BY `listing_id`) s ON s.`listing_id` = l.`listing_id`
               SET l.`average_rating` = COALESCE(s.avg_r, 0),
                   l.`total_reviews`  = COALESCE(s.n, 0)
             WHERE l.`listing_id` IN (SELECT `listing_id` FROM `listing_reviews_dedupe_backup`);
        END IF;
    END IF;
END //
DELIMITER ;
CALL pearland411_review_dedupe();
DROP PROCEDURE pearland411_review_dedupe;
