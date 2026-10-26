-- ---------------------------------------------------------------------------
-- 0037_claim_onboarding_tracking.sql
-- ---------------------------------------------------------------------------
-- Columns and one table for the "Website Update Checklist" (the changes
-- needed before the 100-business acquisition campaign). Every column is
-- nullable or defaulted, and the code checks for each one before using it.
--
-- (1) listings.is_claimed / listings.claimed_at — checklist §1: "Add a clear
--     Claim This Business button to unclaimed listings". The site decided
--     "claimed" from `listings.user_id`, but every listing has an owner: the
--     CSV import and the admin form both require one, and the imported
--     directory sits under a handful of data-entry logins. So no listing ever
--     looked unclaimed and the Claim button never showed. "Claimed" is now
--     its own flag, set by an approved claim, by a member adding their own
--     business, or by an admin on the listing screen.
--
--     Backfill (only when the column is first created, so an admin's later
--     choice is never overwritten by a re-run). A listing counts as claimed
--     when ANY of these holds:
--       (a) its current owner had a claim for it approved;
--       (b) staff billing has a subscription for this business (0029);
--       (c) its owner owns at most 3 live listings — a business owner's own
--           account. The data-entry logins own dozens each.
--     Everything else — the imported directory — becomes claimable. An admin
--     can flip any listing either way on /admin/edit-listing/<slug>.
--
-- (2) claim_listings.claim_business_name / claim_role / claim_verification /
--     claim_admin_note / claim_reviewed_at / claim_updated_at — checklist §1:
--     the claim form asks for the business name, the claimant's role and how
--     they can be verified; the admin can approve, reject with a reason, or
--     ask for more information (claim_status 3), and the claimant sees the
--     admin's note on /user/my-claims.
--
-- (3) listings.business_hours and five social links — checklist §4 / §6: the
--     owner provides hours and social links during onboarding. The listing
--     page already printed `business_hours` where an install had it; the
--     social icons used to come from the OWNER'S PROFILE, which painted a
--     data-entry login's Facebook page onto every business it owned.
--
-- (4) plans.can_hours / plans.can_social_links — checklist §6: hours and
--     social links are plan features (Starter and up in the checklist). When
--     first created they copy can_photos, so every plan that already allows a
--     logo allows these too, and the free tier does not. Editable at
--     /admin/pricing.
--
-- (5) activity_daily — checklist §10: listing views, website / phone / email
--     / map clicks, coupon activity and ad impressions / clicks, one row per
--     thing per event per day (a counter, not a log — nothing about the
--     visitor is stored). Read by /user/analytics and /admin/analytics.
--
-- Idempotent — INFORMATION_SCHEMA guards throughout. Safe to re-run.
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_claim_onboarding;
DELIMITER //
CREATE PROCEDURE pearland411_claim_onboarding()
BEGIN
    -- (1) listings.is_claimed + claimed_at, with the one-time backfill
    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'listings')
       AND NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'listings'
                          AND COLUMN_NAME = 'is_claimed') THEN
        ALTER TABLE `listings`
            ADD COLUMN `is_claimed` TINYINT(1) NOT NULL DEFAULT 0
            COMMENT '1 = the business owner has this listing (approved claim, own listing, or set by an admin)',
            ADD COLUMN `claimed_at` DATETIME NULL DEFAULT NULL
            COMMENT 'When the listing became claimed';

        -- (a) an approved claim by the current owner
        IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'claim_listings'
                      AND COLUMN_NAME = 'claim_status') THEN
            UPDATE `listings` l
               SET l.`is_claimed` = 1
             WHERE l.`user_id` > 0
               AND EXISTS (SELECT 1 FROM `claim_listings` c
                            WHERE c.`listing_id` = l.`listing_id`
                              AND c.`enquiry_sender_id` = l.`user_id`
                              AND c.`claim_status` = 1);
        END IF;

        -- (b) a staff-billing subscription for this business
        IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions'
                      AND COLUMN_NAME = 'listing_id') THEN
            UPDATE `listings` l
               SET l.`is_claimed` = 1
             WHERE EXISTS (SELECT 1 FROM `subscriptions` s
                            WHERE s.`listing_id` = l.`listing_id`);
        END IF;

        -- (c) an owner with only a few businesses is the business itself
        UPDATE `listings` l
          JOIN (SELECT `user_id`, COUNT(*) AS n
                  FROM `listings`
                 WHERE `user_id` > 0 AND (`is_deleted` IS NULL OR `is_deleted` = 0)
                 GROUP BY `user_id`) o ON o.`user_id` = l.`user_id`
           SET l.`is_claimed` = 1
         WHERE o.n <= 3;

        UPDATE `listings`
           SET `claimed_at` = COALESCE(`updated_at`, `created_at`, NOW())
         WHERE `is_claimed` = 1;
    END IF;

    -- (2) claim_listings: what the claim form and the review now record
    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'claim_listings') THEN
        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'claim_listings'
                          AND COLUMN_NAME = 'claim_business_name') THEN
            ALTER TABLE `claim_listings`
                ADD COLUMN `claim_business_name` VARCHAR(255) NULL DEFAULT NULL
                COMMENT 'Business name as the claimant typed it';
        END IF;
        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'claim_listings'
                          AND COLUMN_NAME = 'claim_role') THEN
            ALTER TABLE `claim_listings`
                ADD COLUMN `claim_role` VARCHAR(50) NULL DEFAULT NULL
                COMMENT 'Claimant relationship to the business (owner, manager, ...)';
        END IF;
        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'claim_listings'
                          AND COLUMN_NAME = 'claim_verification') THEN
            ALTER TABLE `claim_listings`
                ADD COLUMN `claim_verification` VARCHAR(50) NULL DEFAULT NULL
                COMMENT 'How the claimant asked to be verified (phone, email, document, ...)';
        END IF;
        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'claim_listings'
                          AND COLUMN_NAME = 'claim_admin_note') THEN
            ALTER TABLE `claim_listings`
                ADD COLUMN `claim_admin_note` TEXT NULL
                COMMENT 'Rejection reason or the information the admin asked for; shown to the claimant';
        END IF;
        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'claim_listings'
                          AND COLUMN_NAME = 'claim_reviewed_at') THEN
            ALTER TABLE `claim_listings`
                ADD COLUMN `claim_reviewed_at` DATETIME NULL DEFAULT NULL
                COMMENT 'When an admin last approved / rejected / asked for more';
        END IF;
        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'claim_listings'
                          AND COLUMN_NAME = 'claim_updated_at') THEN
            ALTER TABLE `claim_listings`
                ADD COLUMN `claim_updated_at` DATETIME NULL DEFAULT NULL
                COMMENT 'When the claimant last sent information';
        END IF;
    END IF;

    -- (3) listings: hours and the business's own social links
    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'listings') THEN
        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'listings'
                          AND COLUMN_NAME = 'business_hours') THEN
            ALTER TABLE `listings`
                ADD COLUMN `business_hours` TEXT NULL
                COMMENT 'Opening hours as the owner writes them, one line per day or range';
        END IF;
        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'listings'
                          AND COLUMN_NAME = 'listing_facebook') THEN
            ALTER TABLE `listings`
                ADD COLUMN `listing_facebook`  VARCHAR(255) NULL DEFAULT NULL,
                ADD COLUMN `listing_instagram` VARCHAR(255) NULL DEFAULT NULL,
                ADD COLUMN `listing_twitter`   VARCHAR(255) NULL DEFAULT NULL,
                ADD COLUMN `listing_youtube`   VARCHAR(255) NULL DEFAULT NULL,
                ADD COLUMN `listing_linkedin`  VARCHAR(255) NULL DEFAULT NULL;
        END IF;
    END IF;

    -- (4) plans: hours and social links as plan features
    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans')
       AND NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans'
                          AND COLUMN_NAME = 'can_hours') THEN
        ALTER TABLE `plans`
            ADD COLUMN `can_hours` TINYINT(1) NOT NULL DEFAULT 0
            COMMENT 'Listing may show opening hours',
            ADD COLUMN `can_social_links` TINYINT(1) NOT NULL DEFAULT 0
            COMMENT 'Listing may show its social media links';
        IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans'
                      AND COLUMN_NAME = 'can_photos') THEN
            UPDATE `plans` SET `can_hours` = `can_photos`, `can_social_links` = `can_photos`;
        END IF;
    END IF;

    -- (5) activity_daily: counters for the analytics pages
    CREATE TABLE IF NOT EXISTS `activity_daily` (
        `subject_type` VARCHAR(16) NOT NULL COMMENT 'listing | coupon | ad',
        `subject_id`   INT(11)     NOT NULL,
        `event_type`   VARCHAR(24) NOT NULL COMMENT 'view, website, phone, email, map, impression, click, ...',
        `event_date`   DATE        NOT NULL,
        `hits`         INT(11)     NOT NULL DEFAULT 0,
        PRIMARY KEY (`subject_type`, `subject_id`, `event_type`, `event_date`),
        KEY `activity_daily_date_idx` (`event_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
END //
DELIMITER ;
CALL pearland411_claim_onboarding();
DROP PROCEDURE pearland411_claim_onboarding;
