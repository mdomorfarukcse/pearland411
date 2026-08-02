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
  links them after the shared sheets. `assets/css/directory.css` is the **shared page kit** — hero,
  two-column shell, cards, buttons, forms, notes, empty states, pager, rail, owner band — and its
  design tokens are declared on the three page scopes that opt in: `.p411-dir` (`/all-listing`),
  `.p411-ld` (`/listing-details`) and `.p411-ct` (events / news / videos + their detail pages, and
  `/about-us`, which add `assets/css/content.css` on top). The homepage is
  `assets/css/home-sections.css` (`.p411-home`) — the **whole** page, header to newsletter band, so
  it can be re-skinned without touching PHP. The rule everywhere: **prefix every selector and
  never edit the shared 345 KB `style.css`,** which ~200 views depend on. Two files have no page
  scope because nothing else emits their class names: `assets/css/promo-cards.css` (the two house
  promo cards) and `assets/css/site-footer.css` (`.p411-foot`, the shared footer). Both are loaded
  by every page that renders those blocks, so the blocks can't drift apart.
- **Colour:** navy `#20245B` for header, headings, footer and structure; pear green `#79B82A` as an
  **accent** on buttons, icons, links and labels; burgundy `#6D263D` for promotional and secondary
  bands; cream `#F7F5F0` / light grey `#F2F3F5` for grounds. Green as a large background is what
  the 2026-08 review asked to stop.
- **Reusable blocks:** `partials/ad_slot.php` (one sellable ad zone; options `$slot_class`,
  `$slot_label`, `$slot_fallback`, `$slot_alias`, `$slot_creative`, `$slot_fallback_partial`),
  `partials/site_footer.php` (the site footer — rendered by `views/footer.php` **and** by the
  self-contained homepage, so the two can't diverge; contact details and the logo come from
  `/admin/setting`), `partials/service_promo.php` (the static website/hosting/SEO promo →
  `/contact-us`), `partials/banner_design_promo.php` (the ad/banner-design promo →
  `/request-service`; the unsold fallback for `right-side-banner-2` via `$slot_fallback_partial`) —
  neither promo is sold or rotated, so neither is ever empty — plus `partials/web_dev_cta.php`,
  `partials/seo_head.php`.
- **Brand artwork** is generated, not hand-drawn: `python3 scripts/generate_brand_art.py` writes
  `assets/images/placeholders/*.svg` (one per kind of business, plus news — served by
  `category_placeholder()`), `assets/images/ads/house-ad-{,burgundy-,navy-,cream-}{strip,wide,box,tall}.svg`
  (the creative an unsold zone falls back to, four palettes so no two unsold zones on a page repeat)
  and `assets/images/ads/templates/` (editable advertiser banners + README). It is deterministic and
  safe to re-run. The hero illustration `assets/images/home/hero-pearland.svg` is hand-authored.
- **Helpers** (autoloaded): `site_setting` (settings + feature flags), `image` (uploads/resize:
  `safe_upload_image`, `uploadAndResize`, `responsive_img`), `view` — escaping (`e`, `e_attr`,
  `e_url`, `e_nl2br`), `json_ld`, `pearland_slug`, `category_placeholder()` (a listing with no
  photograph → the placeholder for its kind of business), plus the content helpers the
  events/news/videos pages share: `content_text()` / `content_excerpt()` (stored rich text → clean
  **unescaped** text; escape at output), `youtube_id()` / `youtube_thumb_url()` /
  `youtube_embed_url()`, and `embed_src()` (pull the `src` out of a pasted map iframe instead of
  echoing admin markup).
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
| Home | `/` | `User::index` → `index.php` | Self-contained (own header; footer via `partials/site_footer.php`). Hotspot → Business of the Week → Featured → Explore are filled in that order and de-duplicated by id **and** name; Featured and Explore only take listings with a real description (`Usermodel::getHomepageFeatured` / `getHomepageListings`). 7 ad zones, 4 of which render nothing when unsold. |
| About | `/about-us` | `User::about_us` → `about.php` | Shared kit; the figures in "Pearland411 today" are counted live and each links to its page |
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
| Events | `/event[?when=upcoming\|past\|all&q=]`, `/event-details/<slug>` | `User::event`, `event_details` | Homepage layout + 6 ad zones each; defaults to **upcoming**, widens to the archive by itself when nothing is scheduled |
| News | `/news[?category=<slug>&q=]`, `/news-details/<slug>` | `User::news`, `news_details` | Same shell; counted section strip; both hyphen and underscore detail URLs route, hyphen is canonical |
| Videos | `/videos[?category=<slug>&q=]`, `/video-details/<slug>` | `User::videos`, `video_details` | Same shell; YouTube thumbnail fallback when no still was uploaded |
| Other content types | blogs/products/classifieds/houses/rentals | `User::*` + admin CRUD | gated by `service_*_enabled`; still on the old theme layout |

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
