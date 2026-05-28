#!/bin/bash
# ---------------------------------------------------------------------------
# scripts/build_combined_migration.sh
# ---------------------------------------------------------------------------
# The combined SQL at database/migrations_combined_phpmyadmin.sql is now
# HAND-MAINTAINED. The previous version of this script auto-built it from
# the per-file scripts by stripping DELIMITER //, but phpMyAdmin's Import
# tab splits SQL on ';' and chokes on the semicolons inside the resulting
# BEGIN...END procedure bodies (#1064 errors). The hand-written bundle uses
# inline PREPARE / EXECUTE for idempotency instead, so phpMyAdmin parses
# every statement cleanly.
#
# When you add a new database/migrations/NNNN_*.sql file:
#   1. Read the new file.
#   2. Manually translate any "CREATE PROCEDURE ... BEGIN ... END" guards
#      into the inline pattern:
#         SET @c := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS ...);
#         SET @s := IF(@c = 0, '<the ALTER TABLE...>', 'DO 0');
#         PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
#   3. Append the translated block to the combined SQL.
# ---------------------------------------------------------------------------
echo "database/migrations_combined_phpmyadmin.sql is hand-maintained."
echo "Edit it directly when adding a new per-file migration."
echo "See the comment at the top of this script for the inline-PREPARE pattern."
