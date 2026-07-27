# Pearland411 — Project Overview

Fast orientation for a new session. Read this + `CHANGELOG.md`, then search for specifics. Don't
read the mega-controllers end-to-end.

## What it is
A community **business directory + advertising platform** for Pearland, TX: business listings
(with a claim → own → edit flow), plus content sections (events, news, blogs, products, videos,
classifieds, real estate, coupons) and a paid **package/subscription** system.

## Stack & layout
- **CodeIgniter 3 + MySQL + jQuery / Bootstrap 4.** Boots through `index.php` (serve the repo root;
  `application/` and `system/` are not web-served).
- **Controllers** (`application/controllers/`):
  - `User.php` — the public site + logged-in user dashboard (**mega-controller, ~7k lines**).
  - `Admin.php` — the admin panel (**~5.8k lines**).
  - `Cli.php` — CLI jobs: `php index.php cli <method>` (`images_backfill`, `renew_subscriptions`).
  - `Weather.php`, `Sitemap.php`, `Errors.php`.
- **Models** (`application/models/`): `Usermodel.php`, `Adminmodel.php` (most DB logic lives here).
  Two rules in `Usermodel` are load-bearing and should never be re-implemented inline:
  `applyPublishedListingFilter()` / `publishedListingJoinCondition()` define **"a listing the
  public may see"** (`is_deleted = 0 AND listing_status = 1`; `1` = published), and
  `resolveCategoryIdBySlug()` turns a category slug from a URL back into an id — stored
  `category_slug` first, then by running `pearland_slug()` over the names in PHP, so link
  generation and link resolution use the same function and cannot diverge. A count and the list it
  labels disagreeing, or a category link landing on an empty page, is what happens when they do.
- **Views** (`application/views/`): public views at the top level (`index.php`, `all-listing.php`,
  `edit_listings.php`, `checkout.php`, `pricing_plan.php`, `user_account.php`, …); admin views in
  `admin/`; shared partials `header.php` / `footer.php` / `user_left_sidebar.php`.
  Note: the **homepage `index.php` is self-contained** (its own header), separate from `header.php`.
- **Per-page CSS:** a controller sets `$data['page_css']` (path or array of paths) and `header.php`
  links them after the shared sheets. Used by `/all-listing` and `/listing-details`
  (`assets/css/directory.css`, scoped `.p411-dir` / `.p411-ld`) and mirrored on the homepage by
  `assets/css/home-sections.css` (`.p411-home`). The rule everywhere: **prefix every selector and
  never edit the shared 345 KB `style.css`,** which ~200 views depend on. The one file without a
  page scope is `assets/css/promo-cards.css` — it styles the two house promo cards below, whose
  class names nothing else in the codebase emits, and it is shared by all three pages that render
  them so they can't drift apart.
- **Reusable blocks:** `partials/ad_slot.php` (one sellable ad zone; options `$slot_class`,
  `$slot_label`, `$slot_fallback`, `$slot_alias`, `$slot_creative`, `$slot_fallback_partial`),
  `partials/service_promo.php` (the static website/hosting/SEO promo → `/contact-us`),
  `partials/banner_design_promo.php` (the ad/banner-design promo → `/request-service`; the unsold
  fallback for `right-side-banner-2` via `$slot_fallback_partial`) — neither is sold or rotated, so
  neither is ever empty — plus `partials/web_dev_cta.php`, `partials/seo_head.php`.
- **Helpers** (autoloaded): `site_setting` (settings + feature flags), `image` (uploads/resize:
  `safe_upload_image`, `uploadAndResize`, `responsive_img`), `view` (escaping/JSON-LD).
- **Libraries:** `Helcim_lib` (payments), `Mailer` (SMTP email), `Rate_limit`.

## Routing
`application/config/routes.php`. A catch-all `$route['(:any)'] = 'User/$1'` plus
`translate_uri_dashes = TRUE` means most URLs resolve to the same-named `User` method
(`/event` → `User::event`, `/checkout-success` → `User::checkout_success`). Explicit routes exist
for auth, services, coupons, and **payments** (webhook + AJAX endpoints). `User::__construct` holds
a `$public_actions` whitelist; anything not in it requires a logged-in user (`logged_user` session).

## Monetization model (the core of the recent redesign)
1. **Free (claimed) tier:** anyone can *claim* an admin-added listing; after approval they edit
   **name / phone / email** for free.
2. **Paid packages unlock capabilities:** photos, map, services, a YouTube video, SEO details —
   gated by per-plan flags on `plans` and enforced in `User::edit_listing` (server strips
   disallowed fields; the view locks them with an "Upgrade" hint).
3. **Content-type quotas:** `plans.*_limit` + `user_usage_tracking`; `Usermodel::canUserAddContent`
   gates every "add" with an "upgrade your plan" prompt.
4. **Recurring subscriptions via Helcim:** checkout → `subscriptions`; auto-renew vaults the card;
   `Cli::renew_subscriptions` renews with dunning; upgrade/downgrade/cancel on the account page.

## Feature → where it lives
| Area | Route(s) | Controller / model | Notes |
|---|---|---|---|
| Home | `/` | `User::index` → `index.php` | Self-contained homepage |
| Directory / listing | `/all-listing`, `/all_listing/<cat>[/<offset>]`, `/listing-details/<slug>` | `User::all_listing`, `listing_details` | Homepage layout + 6 ad zones each; `?sort=featured\|newest\|name\|rating` |
| Claim a listing | `/claim-listing/<slug>` | `User::claim_listing`; admin `Admin::claim_approve` | transfers `listings.user_id` |
| Edit own listing | `/user/edit-listing/<slug>` | `User::edit_listing` | ownership + capability gated |
| Packages (customer) | `/pricing-plan` | `User::pricing_plan` → `pricing_plan.php` | lists all active `plans` |
| Checkout | `/checkout?plan_id=X` | `User::checkout` / `helcim_init` / `process_checkout` | HelcimPay.js |
| Manage plan | `/user/my_account` | upgrade/downgrade/cancel | prorated upgrade |
| Renewal cron | — | `Cli::renew_subscriptions` | daily |
| Admin | `/admin` | `Admin::*` | |
| Site settings / services | `/admin/setting` | `Admin::admin_setting` | feature flags |
| Plan pricing/capabilities | `/admin/pricing` | `Admin::add/edit_pricing_plan` | |
| Content types | events/news/blogs/products/videos/classifieds/houses/rentals | `User::*` + admin CRUD | gated by `service_*_enabled` |

## Key DB tables
`user`, `listings` (+ `listing_categories`, `listing_sub_categories`, `listing_other_info`),
`claim_listings`, `plans`, `plan_durations`, `subscriptions`, `transactions`, `payment_cards`,
`user_usage_tracking`, `site_settings`, content tables (`events`, `news`, `blogs`, `products`,
`classifieds`, `house`, `rental`, `video`, `coupons`), `ads` / `ads_prices`.
Core tables are **not** in repo migrations (they predate git); migrations `0010+` add columns to
them. Get the run order from `database/README.md`.

## Run / deploy
- Copy `.env.example` → `.env`; fill DB, SMTP, `HELCIM_API_TOKEN`, `HELCIM_WEBHOOK_VERIFIER_TOKEN`.
- Run migrations `0000`→latest (or `database/migrations_combined_phpmyadmin.sql`).
- Activate services in `/admin/setting`; set plan capabilities in `/admin/pricing`.
- Cron daily: `php index.php cli renew_subscriptions`.

## Deeper docs
`CHANGELOG.md` (history) · `README.md` (setup/ops) · `PROJECT_AUDIT_REPORT.md` (full audit) ·
`REVIEW_AND_ISSUES.md` (known issues) · `database/README.md` (migration order).
