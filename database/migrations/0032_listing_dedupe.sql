-- ---------------------------------------------------------------------------
-- 0032_listing_dedupe.sql
-- ---------------------------------------------------------------------------
-- Hides businesses the directory holds more than once.
--
-- THE SYMPTOM
-- /all_listing/shopping-retail says "42 businesses" and the first page repeats
-- the same shops. The count is not wrong — there really are 42 rows — but
-- several of them are the same business entered twice. It is the same cause as
-- the duplicate categories in 0031: the directory was seeded more than once,
-- and nothing has ever checked whether a business was already there.
--
-- WHAT COUNTS AS THE SAME BUSINESS
-- The same name, once punctuation and case are taken out, AND one hard
-- identifier in common. Three passes, most reliable first:
--
--   pass 1  same name + same phone number  (digits only)
--   pass 2  same name + same street address
--   pass 3  same name + same website host
--
-- Name alone is never enough: Pearland has more than one Subway and more than
-- one State Farm office, and they are separate businesses with separate
-- listings. A shared phone number or a shared front door is what makes two
-- rows one business.
--
-- WHAT IT WILL NOT TOUCH
-- A listing with an owner (`user_id` > 0). Claimed listings are somebody's
-- property — quite possibly somebody's paid package — and no automatic rule
-- gets to retire one. Where two claimed rows collide, both are left exactly
-- as they are and the pair is left for staff: /admin/all_listings ▸ Duplicates
-- lists them with the evidence, and a human decides.
--
-- WHAT IT DOES
-- Soft-deletes the loser: `is_deleted = 1`, `deleted_date = NOW()`. Nothing is
-- erased, the row keeps its reviews and its enquiries, and restoring it is one
-- UPDATE. Every action is written to `listing_duplicate_merges` first, with
-- which listing it was folded into and why.
--
-- The survivor is picked in this order: an owner beats none, a featured or
-- spotlight listing beats a plain one, one with a photograph beats one
-- without, a longer description beats a shorter one, and the lowest id breaks
-- the tie.
--
-- ROLLBACK
--   UPDATE `listings` l
--     JOIN `listing_duplicate_merges` m ON m.`listing_id` = l.`listing_id`
--      SET l.`is_deleted` = 0, l.`deleted_date` = NULL;
--
-- Idempotent: a soft-deleted row is out of scope for every later pass and for
-- a second run, so re-running finds only duplicates created since. Safe to
-- re-run.
--
-- Run once after 0031 — the category merge has to go first, or two copies of
-- one shop filed under "Beauty & Personal Care" and "Beauty and Personal Care"
-- still look like two businesses in two categories.
--   mysql -u <user> -p <db> < database/migrations/0032_listing_dedupe.sql
--
-- BEFORE / AFTER CHECK
--   SELECT COUNT(*) FROM `listings`
--    WHERE `is_deleted` = 0 AND `listing_status` = 1;
--   SELECT * FROM `listing_duplicate_merges` ORDER BY `id` DESC LIMIT 20;
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_listing_dedupe;
DELIMITER //
CREATE PROCEDURE pearland411_listing_dedupe()
BEGIN
    DECLARE v_punct VARCHAR(64) DEFAULT '!"#$%&''()*+,-./:;<=>?@[\\]^_`{|}~';
    DECLARE v_i     INT         DEFAULT 1;
    DECLARE v_pass  INT         DEFAULT 1;

    -- ---- audit trail ----------------------------------------------------
    CREATE TABLE IF NOT EXISTS `listing_duplicate_merges` (
        `id`               INT(11) NOT NULL AUTO_INCREMENT,
        `listing_id`       INT(11) NOT NULL COMMENT 'the row that was hidden',
        `kept_listing_id`  INT(11) NOT NULL COMMENT 'the row it duplicated',
        `listing_name`     VARCHAR(255) NULL DEFAULT NULL,
        `matched_on`       VARCHAR(16)  NULL DEFAULT NULL COMMENT 'phone | address | website',
        `merged_at`        DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `ldm_listing_uniq` (`listing_id`),
        KEY `ldm_kept_idx` (`kept_listing_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      COMMENT='Listings hidden as duplicates by migration 0032 / the admin Duplicates screen';

    -- `deleted_date` arrived in 0022. Guard-add it so this migration can run
    -- on an install that skipped it rather than failing on the UPDATE.
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'listings'
          AND COLUMN_NAME = 'deleted_date'
    ) THEN
        ALTER TABLE `listings`
            ADD COLUMN `deleted_date` DATETIME NULL DEFAULT NULL;
    END IF;

    -- Ordinary scratch tables, not TEMPORARY: the winner is chosen with a
    -- correlated read of the same table being scanned, and MySQL answers that
    -- on a temporary table with "Can't reopen table". Dropped at both ends so
    -- a run that died half way leaves nothing behind.
    DROP TABLE IF EXISTS `p411_dup_loser`;
    DROP TABLE IF EXISTS `p411_dup_winner`;
    DROP TABLE IF EXISTS `p411_dup_key`;

    -- ---- the comparison keys, one transformation at a time ---------------
    CREATE TABLE `p411_dup_key` (
        `listing_id`   INT(11) NOT NULL,
        `name_key`     VARCHAR(191) NULL,
        `phone_key`    VARCHAR(64)  NULL,
        `address_key`  VARCHAR(191) NULL,
        `website_key`  VARCHAR(191) NULL,
        `match_key`    VARCHAR(255) NULL,
        `owned`        TINYINT(1)   NOT NULL DEFAULT 0,
        `promoted`     TINYINT(1)   NOT NULL DEFAULT 0,
        `has_image`    TINYINT(1)   NOT NULL DEFAULT 0,
        `descr_len`    INT(11)      NOT NULL DEFAULT 0,
        PRIMARY KEY (`listing_id`),
        KEY `p411_dk_match` (`match_key`(191))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    INSERT INTO `p411_dup_key`
        (`listing_id`, `name_key`, `phone_key`, `address_key`, `website_key`,
         `owned`, `promoted`, `has_image`, `descr_len`)
    SELECT l.`listing_id`,
           LEFT(LOWER(TRIM(COALESCE(l.`listing_name`, ''))), 191),
           LEFT(COALESCE(l.`listing_mobile`, ''), 64),
           LEFT(LOWER(TRIM(COALESCE(l.`listing_address`, ''))), 191),
           LEFT(LOWER(TRIM(COALESCE(l.`listing_website`, ''))), 191),
           CASE WHEN COALESCE(l.`user_id`, 0) > 0 THEN 1 ELSE 0 END,
           CASE WHEN COALESCE(l.`featured_listing`, 0) > 0
                  OR COALESCE(l.`spotlight_listing`, 0) > 0 THEN 1 ELSE 0 END,
           CASE WHEN COALESCE(l.`listing_image`, '') = '' THEN 0 ELSE 1 END,
           CHAR_LENGTH(COALESCE(l.`listing_description`, ''))
      FROM `listings` l
     WHERE (l.`is_deleted` IS NULL OR l.`is_deleted` = 0);

    -- Strip punctuation out of the name and the address.
    WHILE v_i <= CHAR_LENGTH(v_punct) DO
        UPDATE `p411_dup_key`
           SET `name_key`    = REPLACE(`name_key`,    SUBSTRING(v_punct, v_i, 1), ' '),
               `address_key` = REPLACE(`address_key`, SUBSTRING(v_punct, v_i, 1), ' ');
        SET v_i = v_i + 1;
    END WHILE;

    SET v_i = 0;
    WHILE v_i < 4 DO
        UPDATE `p411_dup_key`
           SET `name_key`    = REPLACE(`name_key`,    '  ', ' '),
               `address_key` = REPLACE(`address_key`, '  ', ' ');
        SET v_i = v_i + 1;
    END WHILE;
    UPDATE `p411_dup_key`
       SET `name_key`    = TRIM(`name_key`),
           `address_key` = TRIM(`address_key`);

    -- The phone is compared as bare digits, and separators come out BEFORE
    -- the last ten are taken — otherwise "+1 281-992-2064" and
    -- "(281) 992 2064" are trimmed at different points and the same number
    -- yields two different keys.
    UPDATE `p411_dup_key`
       SET `phone_key` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
               `phone_key`, ' ', ''), '-', ''), '(', ''), ')', ''), '+', ''), '.', ''), '/', ''), '#', '');

    -- Anything still carrying a letter — an extension ("x12"), a note — is not
    -- a number this can compare, so it sits pass 1 out and is matched, if at
    -- all, on its address or its website instead.
    UPDATE `p411_dup_key`
       SET `phone_key` = NULL
     WHERE `phone_key` NOT REGEXP '^[0-9]+$';

    -- Last ten digits: a stored country code must not split one number in two.
    UPDATE `p411_dup_key`
       SET `phone_key` = RIGHT(`phone_key`, 10)
     WHERE `phone_key` IS NOT NULL;

    -- The website is compared on its host: a scheme, a www. prefix and a path
    -- are not three different companies.
    UPDATE `p411_dup_key`
       SET `website_key` = REPLACE(REPLACE(`website_key`, 'https://', ''), 'http://', '');
    UPDATE `p411_dup_key` SET `website_key` = REPLACE(`website_key`, 'www.', '');
    UPDATE `p411_dup_key` SET `website_key` = SUBSTRING_INDEX(`website_key`, '/', 1);

    DELETE FROM `p411_dup_key` WHERE `name_key` IS NULL OR `name_key` = '';

    -- ---- three passes ----------------------------------------------------
    WHILE v_pass <= 3 DO

        -- Which identifier this pass pairs the name with. A row with nothing
        -- usable in that column gets a NULL key and takes no part.
        IF v_pass = 1 THEN
            UPDATE `p411_dup_key`
               SET `match_key` = CASE
                       WHEN `phone_key` IS NOT NULL AND CHAR_LENGTH(`phone_key`) >= 7
                       THEN CONCAT('p:', `name_key`, '|', `phone_key`) END;
        ELSEIF v_pass = 2 THEN
            UPDATE `p411_dup_key`
               SET `match_key` = CASE
                       WHEN `address_key` IS NOT NULL AND CHAR_LENGTH(`address_key`) >= 6
                       THEN CONCAT('a:', `name_key`, '|', `address_key`) END;
        ELSE
            UPDATE `p411_dup_key`
               SET `match_key` = CASE
                       WHEN `website_key` IS NOT NULL AND CHAR_LENGTH(`website_key`) >= 4
                       THEN CONCAT('w:', `name_key`, '|', `website_key`) END;
        END IF;

        -- The survivor of each group.
        DROP TABLE IF EXISTS `p411_dup_winner`;
        CREATE TABLE `p411_dup_winner` (
            `match_key`  VARCHAR(255) NOT NULL,
            `listing_id` INT(11) NOT NULL,
            KEY `p411_dw_match` (`match_key`(191))
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        INSERT INTO `p411_dup_winner` (`match_key`, `listing_id`)
        SELECT k.`match_key`,
               (SELECT w.`listing_id` FROM `p411_dup_key` w
                 WHERE w.`match_key` = k.`match_key`
                 ORDER BY w.`owned` DESC, w.`promoted` DESC, w.`has_image` DESC,
                          w.`descr_len` DESC, w.`listing_id` ASC
                 LIMIT 1)
          FROM `p411_dup_key` k
         WHERE k.`match_key` IS NOT NULL
         GROUP BY k.`match_key`
        HAVING COUNT(*) > 1;

        -- The losers — but only the unclaimed ones. A listing with an owner is
        -- never hidden automatically, however obvious the duplicate looks; two
        -- owned rows that collide are left alone for the admin screen.
        DROP TABLE IF EXISTS `p411_dup_loser`;
        CREATE TABLE `p411_dup_loser` (
            `listing_id`      INT(11) NOT NULL,
            `kept_listing_id` INT(11) NOT NULL,
            PRIMARY KEY (`listing_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        INSERT INTO `p411_dup_loser` (`listing_id`, `kept_listing_id`)
        SELECT k.`listing_id`, w.`listing_id`
          FROM `p411_dup_key` k
          JOIN `p411_dup_winner` w ON w.`match_key` = k.`match_key`
         WHERE k.`listing_id` <> w.`listing_id`
           AND k.`owned` = 0;

        INSERT IGNORE INTO `listing_duplicate_merges`
            (`listing_id`, `kept_listing_id`, `listing_name`, `matched_on`, `merged_at`)
        SELECT p.`listing_id`,
               p.`kept_listing_id`,
               l.`listing_name`,
               CASE v_pass WHEN 1 THEN 'phone' WHEN 2 THEN 'address' ELSE 'website' END,
               NOW()
          FROM `p411_dup_loser` p
          JOIN `listings` l ON l.`listing_id` = p.`listing_id`;

        UPDATE `listings` l
          JOIN `p411_dup_loser` p ON p.`listing_id` = l.`listing_id`
           SET l.`is_deleted`   = 1,
               l.`deleted_date` = NOW();

        -- Out of scope for the passes that follow, and for a second run.
        DELETE k FROM `p411_dup_key` k
          JOIN `p411_dup_loser` p ON p.`listing_id` = k.`listing_id`;

        SET v_pass = v_pass + 1;
    END WHILE;

    DROP TABLE IF EXISTS `p411_dup_loser`;
    DROP TABLE IF EXISTS `p411_dup_winner`;
    DROP TABLE IF EXISTS `p411_dup_key`;
END //
DELIMITER ;
CALL pearland411_listing_dedupe();
DROP PROCEDURE pearland411_listing_dedupe;
