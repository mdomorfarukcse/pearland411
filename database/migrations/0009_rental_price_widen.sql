-- ---------------------------------------------------------------------------
-- 0009_rental_price_widen.sql
-- ---------------------------------------------------------------------------
-- The `rental`.`rental_price` column was too small (VARCHAR(10)), so longer
-- prices — especially ranges like "$890 – $1,500" — were silently truncated
-- on save (stored as "$890 – $1,"). Widen it to hold a full price string.
--
-- Prices are free-form here (single amounts, ranges, currency symbols), so a
-- roomy VARCHAR is the right type. Converting from a numeric type keeps the
-- existing values intact.
--
-- Idempotent — only alters the column when it is narrower than the target.
-- Run after 0008:
--   mysql -u <user> -p <db> < database/migrations/0009_rental_price_widen.sql
-- ---------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pearland411_widen_rental_price;
DELIMITER //
CREATE PROCEDURE pearland411_widen_rental_price()
BEGIN
    DECLARE col_type   VARCHAR(64) DEFAULT NULL;
    DECLARE col_length BIGINT      DEFAULT NULL;

    SELECT DATA_TYPE, CHARACTER_MAXIMUM_LENGTH
      INTO col_type, col_length
      FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME   = 'rental'
       AND COLUMN_NAME  = 'rental_price';

    -- Only widen when the column exists and is not already a VARCHAR of at
    -- least 50 (covers narrow VARCHARs and legacy numeric types).
    IF col_type IS NOT NULL
       AND NOT (col_type = 'varchar' AND col_length >= 50) THEN
        ALTER TABLE `rental`
            MODIFY COLUMN `rental_price` VARCHAR(50) NULL DEFAULT NULL;
    END IF;
END //
DELIMITER ;
CALL pearland411_widen_rental_price();
DROP PROCEDURE pearland411_widen_rental_price;
