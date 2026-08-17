-- ---------------------------------------------------------------------------
-- 0027_category_header_banner.sql
-- ---------------------------------------------------------------------------
-- **Finishes "No option to add category-specific banners and header banner in
-- Business Listings."**
--
-- 0024 covered the SOLD half of that report: `ads.category_id` lets an
-- advertiser buy a banner that runs only in one category, and it seeded the
-- `top-banner-category` zone to sell. What it did not add is the EDITORIAL
-- half — the category's own header artwork, which is not inventory and is not
-- for sale. Every category page draws the same hard-coded photograph
-- (`assets/images/all-listing-bg.webp`, referenced from all-listing.php), so
-- Restaurants and Plumbers look identical at the top of the page and an
-- operator has no way to change either.
--
-- This migration adds one nullable column:
--
--   listing_categories.category_banner
--
-- uploaded from /admin/edit_listing_category/<id> — the screen in Business
-- Listings the client was already looking at — and rendered as the hero
-- background of /all_listing/<slug>.
--
-- NULL means "use the standard directory header", so every existing category
-- behaves exactly as it does today. There is deliberately no seed and no
-- backfill: an empty column is the correct state for a category whose banner
-- has not been chosen yet.
--
-- The column is separate from the existing `category_image`, which is resized
-- to 400x250 into images/services and used as a card thumbnail — a different
-- shape for a different job. Reusing it would mean one file that is either a
-- bad thumbnail or a bad banner.
--
-- `listing_categories` predates this repo's migrations, so the work is guarded
-- on the table existing as well as the column. Idempotent — INFORMATION_SCHEMA
-- guards throughout. Safe to re-run.
--
-- Depends on nothing. Running it on an install that never ran 0024 is
-- harmless: the two halves are independent.
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_add_category_banner;
DELIMITER //
CREATE PROCEDURE pearland411_add_category_banner()
BEGIN
    IF EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'listing_categories'
    ) THEN
        IF NOT EXISTS (
            SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'listing_categories'
              AND COLUMN_NAME = 'category_banner'
        ) THEN
            ALTER TABLE `listing_categories`
                ADD COLUMN `category_banner` VARCHAR(255) NULL DEFAULT NULL
                    COMMENT 'Wide header artwork for /all_listing/<slug>; NULL = the site default hero';
        END IF;
    END IF;
END //
DELIMITER ;

CALL pearland411_add_category_banner();
DROP PROCEDURE IF EXISTS pearland411_add_category_banner;
