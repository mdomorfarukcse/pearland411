-- ---------------------------------------------------------------------------
-- 0036_tracker_round3.sql
-- ---------------------------------------------------------------------------
-- Columns for the 2026-10-19 tracker round (PEARLAND411_PROJECT_TRACKER_2).
-- Every column is nullable or defaulted, so existing rows behave exactly as
-- they do today, and the code checks for each one before using it.
--
-- (1) plans.listing_video_limit — tracker: "Each membership plan should have
--     its own upload limits for photos and videos". photo_limit already caps
--     gallery photos; nothing capped the YouTube links on a listing. How many a
--     listing on the plan may show when allow_video is on (-1 = unlimited).
--     Default 1; the special package (already unlimited photos) gets -1.
--     Editable at /admin/pricing ("Listing Video Limit").
--
-- (2) ads.listing_id — tracker: "No option to add category-specific banners
--     and header banner in Business Listings". Category banners are now booked
--     from a business's own edit screen (Admin::listing_banner_add); this
--     records which business a booking was sold to, so the screen can list it.
--     NULL for every booking made at /admin/create-ads, as before.
--
-- (3) listings.cover_image — the header banner at the top of /listing-details.
--     The page has always read it; it is added only where an install lacks it.
--
-- (4) subscriptions.renewal_reminded_for — the next_billing_date the customer
--     was last emailed about (Cli::send_renewal_reminders), so the nightly job
--     reminds once per charge. Tracker: add-ons/plans must say whether the card
--     is charged again, "or the owner will be notified".
--
-- (5) Backfill — tracker: "once they claim the listing, all the inquiries
--     that they have should be on their dashboards". Enquiries about a listing
--     whose current owner had their claim approved are re-addressed to that
--     owner (Admin::claim_approve does the same for claims approved from now
--     on). Re-running changes nothing: it only touches rows still addressed to
--     someone else.
--
-- Idempotent — INFORMATION_SCHEMA guards throughout. Safe to re-run.
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_tracker_round3;
DELIMITER //
CREATE PROCEDURE pearland411_tracker_round3()
BEGIN
    -- (1) plans.listing_video_limit
    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans')
       AND NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans'
                          AND COLUMN_NAME = 'listing_video_limit') THEN
        ALTER TABLE `plans`
            ADD COLUMN `listing_video_limit` INT NOT NULL DEFAULT 1
            COMMENT 'YouTube videos a listing on this plan may show when allow_video = 1; -1 = unlimited';
        -- Only when the column is first created, so an admin's later choice
        -- is never overwritten by a re-run.
        UPDATE `plans` SET `listing_video_limit` = -1
         WHERE `slug` = 'pearland411-special-package';
    END IF;

    -- (2) ads.listing_id
    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ads')
       AND NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ads'
                          AND COLUMN_NAME = 'listing_id') THEN
        ALTER TABLE `ads`
            ADD COLUMN `listing_id` INT NULL DEFAULT NULL
            COMMENT 'Business this booking was sold to (booked from Business Listings); NULL = not recorded',
            ADD KEY `ads_listing_id_idx` (`listing_id`);
    END IF;

    -- (3) listings.cover_image
    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'listings')
       AND NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'listings'
                          AND COLUMN_NAME = 'cover_image') THEN
        ALTER TABLE `listings`
            ADD COLUMN `cover_image` VARCHAR(255) NULL DEFAULT NULL
            COMMENT 'Header banner at the top of the listing page';
    END IF;

    -- (4) subscriptions.renewal_reminded_for
    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions')
       AND NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
                          AND COLUMN_NAME = 'renewal_reminded_for') THEN
        ALTER TABLE `subscriptions`
            ADD COLUMN `renewal_reminded_for` DATETIME NULL DEFAULT NULL
            COMMENT 'next_billing_date the renewal reminder email was sent for';
    END IF;

    -- (5) enquiries follow an approved claim
    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'enquiries'
                  AND COLUMN_NAME = 'listing_id')
       AND EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'enquiries'
                      AND COLUMN_NAME = 'receiving_user_id')
       AND EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'claim_listings'
                      AND COLUMN_NAME = 'claim_status') THEN
        UPDATE `enquiries` e
          JOIN `listings` l ON l.`listing_id` = e.`listing_id`
           SET e.`receiving_user_id` = l.`user_id`
         WHERE l.`user_id` > 0
           AND (e.`receiving_user_id` IS NULL OR e.`receiving_user_id` <> l.`user_id`)
           AND EXISTS (SELECT 1 FROM `claim_listings` c
                        WHERE c.`listing_id` = l.`listing_id`
                          AND c.`enquiry_sender_id` = l.`user_id`
                          AND c.`claim_status` = 1);
    END IF;
END //
DELIMITER ;
CALL pearland411_tracker_round3();
DROP PROCEDURE pearland411_tracker_round3;
