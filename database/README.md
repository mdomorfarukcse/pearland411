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
mysql -u <user> -p <db_name> < database/migrations/0018_homepage_promos.sql
mysql -u <user> -p <db_name> < database/migrations/0019_directory_ad_slots.sql
mysql -u <user> -p <db_name> < database/migrations/0020_listing_status_normalise.sql
mysql -u <user> -p <db_name> < database/migrations/0021_blog_dates.sql
mysql -u <user> -p <db_name> < database/migrations/0022_listing_deleted_date.sql
mysql -u <user> -p <db_name> < database/migrations/0023_listing_subcategory_map.sql
mysql -u <user> -p <db_name> < database/migrations/0024_ads_category_target.sql

# One-off data fix (not a migration) — decode news/video/blog rows that the old
# admin write paths stored entity-encoded, so headlines stop showing "&#039;".
# Reports first; pass `apply` to write. Records itself in site_settings so it
# cannot run twice by accident.
php index.php cli decode_content_entities
php index.php cli decode_content_entities apply

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
| `0017_home_ad_slots.sql` | Seeds the homepage ad zones. Each `slot_key` is deliberately the **same string as the `plans.slug`** of the package sold on `/pricing-plan` (`top-banner-site-wide`, `bottom-banner-site-wide`, `middle-banner-category`, `right-side-banner-1/2/3`, `right-panel-skyscraper`), so a purchase maps to a position on the page — previously ad zones and sellable packages were two unrelated vocabularies. **Data only** (0006 added `slot_key`/`size`), so no DDL and no `DELIMITER`; standalone file and combined-bundle block are identical. |
| `0018_homepage_promos.sql` | Adds `listings.hotspot_until` and `listings.week_until` (both `DATE NULL`, indexed). These record the **Business Hotspot on Homepage** ($25/30d) and **Business Of the Week** ($20/7d) packages, which were already sellable but had nowhere to be stored, so the homepage could never render them. Dates rather than flags, so a placement expires on its own at the end of the paid term. |
| `0019_directory_ad_slots.sql` | Seeds the three **category-priced** ad zones the directory (`/all-listing`) and listing-details pages render: `right-banner-1`, `right-banner-2` and `bottom-banner-category`. Same rule as 0017 — each `slot_key` is the `plans.slug` of a package sold on `/pricing-plan`; those three were sellable but had no `ads_prices` row, so nothing bought for them could ever appear. Sizes are `300x250` for the right banners (they sit in a sidebar column, not the 900x100 the packages seed still claims) and `900x100` for the category bottom banner. **Data only**, so the standalone file and the combined-bundle block are identical. Note: the 0006 zones `listing-top` / `listing-inline` / `listing-sidebar` match no package and are no longer rendered directly — the two views pass them as `$slot_alias`, so an ad an operator booked against one by hand still runs in the equivalent new position. |
| `0020_listing_status_normalise.sql` | **Fixes the "255 listings" box over a page showing 13.** `listings.listing_status` is read as `1` = published everywhere (admin listings table, both user dashboards, sitemap, every public query), but almost nothing wrote `1`: the admin CSV import hardcoded `0`, and the public add-listing form wrote the *string* `'Active'` into an `INT` column, which MySQL coerces to `0`. So imported and user-submitted businesses were all stored unpublished — the category counters (which filtered only `is_deleted`) counted them, the listing queries (which also filtered `listing_status = 1`) didn't. Backs the old values up to `listings.listing_status_backup` first, then publishes every non-deleted listing that is `0` or `NULL`. Also backfills `listing_categories.category_slug` where it is empty, using the same rule as `pearland_slug()` in `application/helpers/view_helper.php`, so admin-created categories stop relying on a name-matching fallback the three menus each guessed differently. Idempotent: the backup column is the guard, so a re-run cannot re-publish something an operator has since switched off. **Rollback:** `UPDATE listings SET listing_status = listing_status_backup WHERE listing_status_backup IS NOT NULL;` |
| `0021_blog_dates.sql` | **Fixes "all blogs have the same date".** `Admin::add_blog()` never wrote `blogs.created_at` — the payload it builds has user_id, name, description, status, isenquiry and slug and nothing else — so admin-created posts took the column default (zero date / NULL). The index then printed `date('d, M Y', strtotime(...))`, and `strtotime()` returns `FALSE` for those, which `date()` renders as the epoch: every dateless post showed the *same* date. Makes the column nullable with `DEFAULT CURRENT_TIMESTAMP` so a write path that forgets still records something real, and turns existing zero dates into `NULL`. It deliberately does **not** invent dates — there is no signal in the row for what the real one was; `NULL` is honest and the views now omit the line. Set the true dates per post at `/admin/blog-list` → Edit, which grew a **Publish date** field alongside this migration. Idempotent. |
| `0022_listing_deleted_date.sql` | **Fixes "Deleted listings cannot be restored — HTTP 500".** `Admin::restore_listing()` read the trashed row, set two properties on it, and wrote the whole object back — including `deleted_date`, a column that appeared exactly **once** in the entire application, on that line. Nothing created it and nothing else wrote it: the soft delete set only `is_deleted = 1`, and the trash page's "Deleted Date" heading was being filled with `postal_code`. So the UPDATE emitted `SET … deleted_date = '…'` against a column that does not exist → MySQL 1054 → the 500. The column is added rather than removed from the code, because a trash page that cannot say when something was deleted is not much of a trash page: `softDeleteListing()` now sets it and `restoreListing()` clears it (the old code *stamped* it with "now" while restoring, which was backwards). Existing trashed rows get NULL — there is no signal in the row for when they were deleted, and the view prints `''` rather than inventing one. Idempotent. |
| `0023_listing_subcategory_map.sql` | **Fixes "why can't I add subcategories, even though I have included all the available subcategories?"** It was never possible. `listings.sub_category_id` is a single INT column and the admin form posted a single-value `<select>`, so a listing could hold exactly one subcategory and the last one picked won. `listing_sub_categories` is the **catalogue** of subcategories, not a listing-to-subcategory join, and no join table existed anywhere in the schema. Adds `listing_sub_category_map` (composite primary key, so a pair cannot be recorded twice) and back-fills the existing single values, so nothing already recorded is lost. `listings.sub_category_id` deliberately **stays**, holding the first selection: it is read by the admin listings table, the CSV export, the public listing page and several model joins, and retiring it belongs in its own migration. Idempotent. |
| `0024_ads_category_target.sql` | **Fixes "no option to add category-specific banners and header banner in Business Listings."** Ads had exactly one targeting dimension — `ads_prices.slot_key`, the position on the page — and the booking form offered user, price, dates, artwork and a link, with no category anywhere. The slot keys that read as category-specific are not: `middle-banner-category` is one global inventory item included by eight views, so "category" meant *rendered on category pages*, not *sold per category*. Adds `ads.category_id`, nullable: NULL runs the ad in its slot everywhere (every ad booked before this migration), an id restricts it to that listing category, and a category booking outranks an untargeted one in the same slot. Also seeds `top-banner-category`, the directory header slot the report asks for, following the 0017/0019 rule that a `slot_key` is the `plans.slug` of the package sold for it. Idempotent. |
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
