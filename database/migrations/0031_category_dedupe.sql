-- ---------------------------------------------------------------------------
-- 0031_category_dedupe.sql
-- ---------------------------------------------------------------------------
-- Merges categories that are the same category written two ways.
--
-- THE SYMPTOM
-- /all-category lists 52 categories, and reading down it you find:
--
--     Arts & Entertainment        4     Arts and Entertainment      6
--     Beauty & Personal Care     26     Beauty and Personal Care   25
--     Community & Government      -     Community and Government   14
--
-- Two entries, two URLs, two counts, one kind of business. A visitor who picks
-- "Beauty & Personal Care" sees 26 of the 51 salons on the site and no
-- indication that the other 25 exist. It happened because the directory was
-- seeded more than once from sources that punctuated their taxonomy
-- differently, and nothing in the application has ever noticed.
--
-- WHAT COUNTS AS THE SAME
-- Only an exact match once the writing is normalised: lower-cased, "&" read as
-- "and", punctuation dropped, runs of whitespace collapsed. That merges the
-- three pairs above and anything else spelt two ways, and it deliberately does
-- NOT merge judgement calls like Finance / Financial Services, or Education /
-- Education & Childcare / Education & Training. Those are three real and
-- different things or they are not, and a migration is the wrong place to
-- decide: /admin/categories has a Merge tool for them, added alongside this.
--
-- WHICH ROW SURVIVES
-- The one with the most published listings, and the lowest id to break a tie.
-- Everything pointing at a loser is re-pointed at it:
--   * listings.category_id
--   * listing_sub_categories.category_id  (the sub-category's parent)
--   * ads.category_id                     (category-targeted bookings, 0024)
-- If the survivor has no slug and a loser does, it inherits it, so the busier
-- category does not lose a URL that is already linked and indexed.
--
-- The losers are then deleted, but every merge is written to
-- `listing_category_merges` first, with the old id, name and slug.
--
-- ROLLBACK
--   Re-create the rows from `listing_category_merges` and send the listings
--   back:
--     INSERT INTO `listing_categories` (`category_id`,`category_name`,`category_slug`)
--     SELECT `from_category_id`,`from_category_name`,`from_category_slug`
--       FROM `listing_category_merges`;
--   then repoint by hand — the mapping does not record WHICH listings moved,
--   only where they went, because a listing may legitimately have been
--   recategorised since.
--
-- Idempotent: a second run finds no two categories sharing a normalised name
-- and does nothing. Safe to re-run.
--
-- Run once after 0030:
--   mysql -u <user> -p <db> < database/migrations/0031_category_dedupe.sql
--
-- BEFORE / AFTER CHECK
--   SELECT COUNT(*) FROM `listing_categories`;
--   SELECT `category_name`, COUNT(*) FROM `listing_categories`
--    GROUP BY LOWER(REPLACE(`category_name`,'&','and')) HAVING COUNT(*) > 1;
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_category_dedupe;
DELIMITER //
CREATE PROCEDURE pearland411_category_dedupe()
BEGIN
    -- Everything dropped from the comparison key. Note '&' is NOT here: it is
    -- rewritten to the word first, which is the whole point.
    DECLARE v_punct VARCHAR(64) DEFAULT '!"#$%''()*+,-./:;<=>?@[\\]^_`{|}~';
    DECLARE v_i     INT         DEFAULT 1;

    -- ---- audit trail ----------------------------------------------------
    CREATE TABLE IF NOT EXISTS `listing_category_merges` (
        `id`                 INT(11) NOT NULL AUTO_INCREMENT,
        `from_category_id`   INT(11) NOT NULL,
        `from_category_name` VARCHAR(255) NULL DEFAULT NULL,
        `from_category_slug` VARCHAR(191) NULL DEFAULT NULL,
        `to_category_id`     INT(11) NOT NULL,
        `listings_moved`     INT(11) NOT NULL DEFAULT 0,
        `merged_at`          DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `lcm_from_idx` (`from_category_id`),
        KEY `lcm_to_idx`   (`to_category_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      COMMENT='Categories merged by migration 0031 / the admin Merge tool';

    -- Scratch tables. Ordinary, not TEMPORARY: picking the survivor below
    -- reads the same scratch table twice in one statement, and MySQL answers
    -- that on a temporary table with "Can't reopen table". They are dropped
    -- before use as well as after, so a run that died half way leaves nothing
    -- for the next one to trip over.
    DROP TABLE IF EXISTS `p411_cat_loser`;
    DROP TABLE IF EXISTS `p411_cat_winner`;
    DROP TABLE IF EXISTS `p411_cat_rank`;
    DROP TABLE IF EXISTS `p411_cat_key`;

    -- ---- 1. normalised comparison key, one transformation at a time -----
    CREATE TABLE `p411_cat_key` (
        `category_id` INT(11) NOT NULL,
        `merge_key`   VARCHAR(191) NULL,
        PRIMARY KEY (`category_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    INSERT INTO `p411_cat_key` (`category_id`, `merge_key`)
    SELECT `category_id`, LEFT(LOWER(TRIM(`category_name`)), 191) FROM `listing_categories`;

    -- "&" and "+" both read as the word, padded so "R&D" does not become
    -- "randd" while "Arts & Crafts" becomes "arts and crafts".
    UPDATE `p411_cat_key` SET `merge_key` = REPLACE(`merge_key`, '&', ' and ');
    UPDATE `p411_cat_key` SET `merge_key` = REPLACE(`merge_key`, '+', ' and ');

    WHILE v_i <= CHAR_LENGTH(v_punct) DO
        UPDATE `p411_cat_key`
           SET `merge_key` = REPLACE(`merge_key`, SUBSTRING(v_punct, v_i, 1), ' ');
        SET v_i = v_i + 1;
    END WHILE;

    -- Collapse whitespace runs. Four passes flatten a run of up to 16.
    SET v_i = 0;
    WHILE v_i < 4 DO
        UPDATE `p411_cat_key` SET `merge_key` = REPLACE(`merge_key`, '  ', ' ');
        SET v_i = v_i + 1;
    END WHILE;
    UPDATE `p411_cat_key` SET `merge_key` = TRIM(`merge_key`);

    DELETE FROM `p411_cat_key` WHERE `merge_key` IS NULL OR `merge_key` = '';

    -- ---- 2. how busy each category is -----------------------------------
    DROP TABLE IF EXISTS `p411_cat_rank`;
    CREATE TABLE `p411_cat_rank` (
        `category_id`   INT(11) NOT NULL,
        `merge_key`     VARCHAR(191) NULL,
        `listing_count` INT(11) NOT NULL DEFAULT 0,
        PRIMARY KEY (`category_id`),
        KEY `p411_cr_key` (`merge_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    INSERT INTO `p411_cat_rank` (`category_id`, `merge_key`, `listing_count`)
    SELECT k.`category_id`,
           k.`merge_key`,
           (SELECT COUNT(*) FROM `listings` l
             WHERE l.`category_id` = k.`category_id`
               AND (l.`is_deleted` IS NULL OR l.`is_deleted` = 0)
               AND l.`listing_status` = 1)
      FROM `p411_cat_key` k;

    -- ---- 3. the survivor for each key -----------------------------------
    -- Busiest first, lowest id to break the tie. Written out as a correlated
    -- pick rather than a window function: this has to run on MySQL 5.7.
    DROP TABLE IF EXISTS `p411_cat_winner`;
    CREATE TABLE `p411_cat_winner` (
        `merge_key`   VARCHAR(191) NOT NULL,
        `category_id` INT(11) NOT NULL,
        PRIMARY KEY (`merge_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    INSERT INTO `p411_cat_winner` (`merge_key`, `category_id`)
    SELECT r.`merge_key`,
           (SELECT w.`category_id` FROM `p411_cat_rank` w
             WHERE w.`merge_key` = r.`merge_key`
             ORDER BY w.`listing_count` DESC, w.`category_id` ASC
             LIMIT 1)
      FROM `p411_cat_rank` r
     GROUP BY r.`merge_key`
    HAVING COUNT(*) > 1;

    -- ---- 4. the losers ---------------------------------------------------
    DROP TABLE IF EXISTS `p411_cat_loser`;
    CREATE TABLE `p411_cat_loser` (
        `from_category_id` INT(11) NOT NULL,
        `to_category_id`   INT(11) NOT NULL,
        PRIMARY KEY (`from_category_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    INSERT INTO `p411_cat_loser` (`from_category_id`, `to_category_id`)
    SELECT r.`category_id`, w.`category_id`
      FROM `p411_cat_rank` r
      JOIN `p411_cat_winner` w ON w.`merge_key` = r.`merge_key`
     WHERE r.`category_id` <> w.`category_id`;

    -- ---- 5. record it before anything moves ------------------------------
    INSERT INTO `listing_category_merges`
        (`from_category_id`, `from_category_name`, `from_category_slug`,
         `to_category_id`, `listings_moved`, `merged_at`)
    SELECT lc.`category_id`,
           lc.`category_name`,
           lc.`category_slug`,
           p.`to_category_id`,
           (SELECT COUNT(*) FROM `listings` l WHERE l.`category_id` = lc.`category_id`),
           NOW()
      FROM `p411_cat_loser` p
      JOIN `listing_categories` lc ON lc.`category_id` = p.`from_category_id`;

    -- ---- 6. the survivor inherits a slug if it has none -------------------
    -- Done before the losers are deleted, and only when the survivor's own
    -- slug is empty, so an indexed URL is not thrown away.
    UPDATE `listing_categories` win
      JOIN `p411_cat_loser` p   ON p.`to_category_id` = win.`category_id`
      JOIN `listing_categories` lose ON lose.`category_id` = p.`from_category_id`
       SET win.`category_slug` = lose.`category_slug`
     WHERE (win.`category_slug` IS NULL OR win.`category_slug` = '')
       AND lose.`category_slug` IS NOT NULL
       AND lose.`category_slug` <> '';

    -- ---- 7. re-point everything ------------------------------------------
    UPDATE `listings` l
      JOIN `p411_cat_loser` p ON p.`from_category_id` = l.`category_id`
       SET l.`category_id` = p.`to_category_id`;

    IF EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'listing_sub_categories'
          AND COLUMN_NAME = 'category_id'
    ) THEN
        UPDATE `listing_sub_categories` s
          JOIN `p411_cat_loser` p ON p.`from_category_id` = s.`category_id`
           SET s.`category_id` = p.`to_category_id`;
    END IF;

    IF EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'ads'
          AND COLUMN_NAME = 'category_id'
    ) THEN
        UPDATE `ads` a
          JOIN `p411_cat_loser` p ON p.`from_category_id` = a.`category_id`
           SET a.`category_id` = p.`to_category_id`;
    END IF;

    -- ---- 8. and drop the duplicates --------------------------------------
    DELETE lc FROM `listing_categories` lc
      JOIN `p411_cat_loser` p ON p.`from_category_id` = lc.`category_id`;

    DROP TABLE IF EXISTS `p411_cat_loser`;
    DROP TABLE IF EXISTS `p411_cat_winner`;
    DROP TABLE IF EXISTS `p411_cat_rank`;
    DROP TABLE IF EXISTS `p411_cat_key`;
END //
DELIMITER ;
CALL pearland411_category_dedupe();
DROP PROCEDURE pearland411_category_dedupe;
