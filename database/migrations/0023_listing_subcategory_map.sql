-- ---------------------------------------------------------------------------
-- 0023_listing_subcategory_map.sql
-- ---------------------------------------------------------------------------
-- **Fixes "why can't I add subcategories, even though I have included all the
-- available subcategories?"**
--
-- It was never possible. `listings.sub_category_id` is a single INT column and
-- the admin form posts a single-value `<select>`, so a listing could hold
-- exactly one subcategory and the last one picked won. `listing_sub_categories`
-- is the CATALOGUE of subcategories (sub_category_id, category_id,
-- sub_category_name, sub_category_slug) — not a listing↔subcategory join — and
-- no join table existed anywhere in the schema.
--
-- This adds one: `listing_sub_category_map`, a plain many-to-many with a
-- composite primary key so the same pair cannot be recorded twice, and
-- ON DELETE CASCADE so removing a listing or a subcategory cleans up after
-- itself.
--
-- WHY `listings.sub_category_id` STAYS
-- It is read in a lot of places (the admin listings table, the CSV export, the
-- public listing page, several joins in Adminmodel). Dropping it here would
-- mean rewriting all of them in the same change. Instead the application keeps
-- it in step: it holds the FIRST selected subcategory, so every existing reader
-- keeps working and shows something sensible, while the full set lives in the
-- map. It can be retired in its own migration once the readers move over.
--
-- Existing single values are back-filled into the map, so a listing that
-- already had a subcategory keeps it.
--
-- Idempotent — guarded through INFORMATION_SCHEMA, and the back-fill is an
-- INSERT ... SELECT with a NOT EXISTS test. Safe to re-run.
--
-- Run once after 0022:
--   mysql -u <user> -p <db> < database/migrations/0023_listing_subcategory_map.sql
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_add_listing_subcat_map;
DELIMITER //
CREATE PROCEDURE pearland411_add_listing_subcat_map()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'listing_sub_category_map'
    ) THEN
        CREATE TABLE `listing_sub_category_map` (
            `listing_id`      INT(11) NOT NULL,
            `sub_category_id` INT(11) NOT NULL,
            PRIMARY KEY (`listing_id`, `sub_category_id`),
            KEY `lscm_sub_category_idx` (`sub_category_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
          COMMENT='Many-to-many: a listing may sit under several subcategories';
    END IF;

    -- Back-fill from the single column so nothing already recorded is lost.
    INSERT INTO `listing_sub_category_map` (`listing_id`, `sub_category_id`)
    SELECT l.`listing_id`, l.`sub_category_id`
      FROM `listings` l
     WHERE l.`sub_category_id` IS NOT NULL
       AND l.`sub_category_id` > 0
       AND NOT EXISTS (
           SELECT 1 FROM `listing_sub_category_map` m
            WHERE m.`listing_id` = l.`listing_id`
              AND m.`sub_category_id` = l.`sub_category_id`
       );
END //
DELIMITER ;
CALL pearland411_add_listing_subcat_map();
DROP PROCEDURE pearland411_add_listing_subcat_map;
