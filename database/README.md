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
mysql -u <user> -p <db_name> < database/migrations/0007_site_settings.sql
mysql -u <user> -p <db_name> < database/migrations/0008_event_admin_host.sql
mysql -u <user> -p <db_name> < database/migrations/0009_rental_price_widen.sql
mysql -u <user> -p <db_name> < database/migrations/0010_plan_capabilities.sql
mysql -u <user> -p <db_name> < database/migrations/0011_service_registry.sql
mysql -u <user> -p <db_name> < database/migrations/0012_helcim_billing.sql
mysql -u <user> -p <db_name> < database/migrations/0013_subscription_lifecycle.sql
mysql -u <user> -p <db_name> < database/migrations/0014_plan_category.sql
mysql -u <user> -p <db_name> < database/migrations/0015_plan_durations_columns.sql
mysql -u <user> -p <db_name> < database/migrations/0016_transaction_term.sql
mysql -u <user> -p <db_name> < database/migrations/0017_home_ad_slots.sql

# Optional convenience seed (not a migration):
mysql -u <user> -p <db_name> < database/seeds/plan_capability_defaults.sql

# Advertising catalog seed — replaces the placeholder plans with the real
# 22-package Pearland411 catalog (deletes current plans, inserts the catalog).
# Self-contained: guard-adds `category` itself, so it can run before or after 0014.
mysql -u <user> -p <db_name> < database/seeds/pearland411_packages.sql
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
| `0014_plan_category.sql` | Adds `plans.category` — the advertising-catalog group slug (`business-listing`, `add-ons`, `site-wide-banners`, `category-banners`, `site-wide-videos`, `category-videos`, `special`). Lets the admin and public pricing pages render packages under their catalog section headings. Populated by `seeds/pearland411_packages.sql`. |
| `0017_home_ad_slots.sql` | Seeds five extra `ads_prices` zones for the redesigned homepage (`home-top-leaderboard` 970x90, `home-sidebar-top` 300x250, `home-inline-billboard` 728x250, `home-sidebar-skyscraper` 300x600, `home-footer-billboard` 970x250). **Data only** — 0006 already added `slot_key`/`size`, so there is no DDL, no stored procedure and no `DELIMITER`; the standalone file and the combined-bundle block are identical. Rendered by `views/partials/ad_slot.php`; prices feed the homepage rate card directly. |
| `0015_plan_durations_columns.sql` | Creates `plan_durations` when missing and back-fills `currency` / `duration_days` / `is_active` / `created_at` / `updated_at` on databases that predate them. **Required** — without it `Adminmodel::updatePricingDuration()` writes a column that does not exist, so saving a duration price in admin silently does nothing. |
| `0016_transaction_term.sql` | Adds `term_days`, `expires_at` and `duration_id` to `transactions` so an ad placement bought for a longer term (12 months, 2 years) records its real expiry instead of being reported against the plan's 30-day base period. Back-fills existing rows from the plan's base term. |
| *(seed)* `seeds/homepage_ad_zones_only.sql` | phpMyAdmin-friendly version of 0017 for sites that **already have real listings**: adds only the five homepage ad zones and switches on the News / Videos / Events services. Inserts no listings, categories or content. Use `seeds/pearland_sample_content.sql` instead only on an empty/demo database. |

## Outstanding follow-up

A complete schema dump still needs to be checked in (see audit
recommendation D1). Suggested:

```bash
mysqldump --no-data --routines --triggers <db_name> > database/schema.sql
mysqldump --no-create-info --skip-extended-insert <db_name> \
    listing_categories listing_sub_categories plans plan_durations \
    > database/seed_reference.sql
```
