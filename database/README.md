# Database

This project did not previously ship its schema in git. As we make
schema changes for security/feature work, the DDL lands here as
numbered SQL files (`0000_*.sql`, `0001_*.sql`, ...).

## Running migrations

Two ways to run them.

### Option 1 — one combined file (easiest for phpMyAdmin)

`database/migrations_combined_phpmyadmin.sql` is an
auto-generated bundle of all seven files **in the correct order**
with the `DELIMITER //` directives stripped (some phpMyAdmin
versions can't parse them). Import this single file via
phpMyAdmin → **Import** → **Choose File** → Go.

Re-generate the bundle whenever a new per-file migration is added:

```bash
scripts/build_combined_migration.sh
```

### Option 2 — per-file (mysql CLI or modern phpMyAdmin)

Each file is safe to run **once** against the live MySQL database
and (where MySQL allows) idempotent on re-run. Run them in order:

```bash
mysql -u <user> -p <db_name> < database/migrations/0000_schema_fixups.sql
mysql -u <user> -p <db_name> < database/migrations/0001_auth_flows.sql
mysql -u <user> -p <db_name> < database/migrations/0002_services.sql
mysql -u <user> -p <db_name> < database/migrations/0003_newsletter_campaigns.sql
mysql -u <user> -p <db_name> < database/migrations/0004_rate_limits.sql
mysql -u <user> -p <db_name> < database/migrations/0005_admin_roles.sql
mysql -u <user> -p <db_name> < database/migrations/0006_ad_zones.sql
```

## Why each migration exists

| File | Why |
|---|---|
| `0000_schema_fixups.sql` | **Must run first.** Widens `admin.admin_password` to 255 chars (bcrypt is 60 chars; the existing `varchar(50)` would silently truncate it and lock the admin out on the next login). Adds `admin.banned` flag (referenced by `Adminmodel::login`). Widens `user.email_id` to 255 and adds a UNIQUE index. Adds a slug index on `listings`. |
| `0001_auth_flows.sql` | Password-reset (`password_resets` table) + email-verification columns on `user`. Back-fills `email_verified = 1` for existing rows so legacy accounts aren't locked out. |
| `0002_services.sql` | IT/web services catalog (`services`, `service_requests`). Seeds 10 placeholder services — edit in `/admin/services` to match the real Space Center Systems catalog. |
| `0003_newsletter_campaigns.sql` | `newsletter_campaigns` + `newsletter_campaign_sends` so the admin can compose, queue, and batch-send emails to the existing subscriber list. |
| `0004_rate_limits.sql` | `rate_limits` table backing the `Rate_limit` library. Protects login, register, password-reset, enquiry, review, newsletter signup, and service-request endpoints from brute-force / spam. |
| `0005_admin_roles.sql` | Adds `admin.role` (`super_admin`/`editor`) + `admin.is_active`. Promotes the seeded admin (id=1) to `super_admin`. Sub-admin management, billing, and ad-sales screens are gated to super-admins; editors can manage content. |
| `0006_ad_zones.sql` | Adds `ads_prices.slot_key` so the frontend ad-slot partial (`application/views/partials/ad_slot.php`) can request "the ad for the home-hero slot" by code rather than admin-typed name. Seeds 8 canonical slots: `home-hero`, `home-sidebar`, `listing-sidebar`, `listing-top`, `listing-inline`, `events-sidebar`, `news-sidebar`, `footer-banner`. |

## Outstanding follow-up

A complete schema dump still needs to be checked in (see audit
recommendation D1). Suggested:

```bash
mysqldump --no-data --routines --triggers <db_name> > database/schema.sql
mysqldump --no-create-info --skip-extended-insert <db_name> \
    listing_categories listing_sub_categories plans plan_durations \
    > database/seed_reference.sql
```
