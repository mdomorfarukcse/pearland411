-- ---------------------------------------------------------------------------
-- 0035_bylines_and_member_ban.sql
-- ---------------------------------------------------------------------------
-- Two small columns, both nullable/defaulted so every existing row behaves
-- exactly as it does today.
--
-- (1) news.news_byline, blogs.blog_byline — tracker #20, "User Name still not
--     updated across entire website". A news article or blog post printed
--     the `user.name` of whichever account it was filed under, and most are
--     filed under staff data-entry logins, so the byline was a login name
--     the client did not choose. The admin forms now carry a Byline box;
--     when it is filled that text is the author shown on the article, in
--     search results and in the JSON-LD; when it is empty (NULL, the default
--     for every existing row) the account name is shown, as before.
--
-- (2) user.banned — tracker B2, "Are these users all allowed to publish
--     content?". Usermodel::login() already refuses an account with
--     banned = 1, but nothing anywhere could SET it: /admin/users was
--     read-only. The new Suspend / Restore buttons on /admin/users write it.
--     The column is part of the original schema on the live site (login
--     reads it); it is added here only for installs where it is missing.
--
-- Idempotent — INFORMATION_SCHEMA guards throughout. Safe to re-run.
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_bylines_and_ban;
DELIMITER //
CREATE PROCEDURE pearland411_bylines_and_ban()
BEGIN
    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'news')
       AND NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'news'
                          AND COLUMN_NAME = 'news_byline') THEN
        ALTER TABLE `news`
            ADD COLUMN `news_byline` VARCHAR(120) NULL DEFAULT NULL
            COMMENT 'Author name shown on the article; NULL = the owning account name';
    END IF;

    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'blogs')
       AND NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'blogs'
                          AND COLUMN_NAME = 'blog_byline') THEN
        ALTER TABLE `blogs`
            ADD COLUMN `blog_byline` VARCHAR(120) NULL DEFAULT NULL
            COMMENT 'Author name shown on the post; NULL = the owning account name';
    END IF;

    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user')
       AND NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user'
                          AND COLUMN_NAME = 'banned') THEN
        ALTER TABLE `user`
            ADD COLUMN `banned` TINYINT(1) NOT NULL DEFAULT 0
            COMMENT '1 = suspended at /admin/users: cannot sign in, hidden from owner pickers';
    END IF;
END //
DELIMITER ;
CALL pearland411_bylines_and_ban();
DROP PROCEDURE pearland411_bylines_and_ban;
