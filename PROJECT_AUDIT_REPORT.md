# Pearland411 — Project Audit Report

**Repository:** `mdomorfarukcse/pearland411`
**Branch audited:** `claude/audit-codeigniter-repo-khLDD`
**Audit date:** 2026-04-30
**Audited against client goals:** woodlandsonline.com feature parity, IT/web services sales, ad sales, community content
**Stack:** CodeIgniter 3.1.11, PHP (composer requires `>=5.3.7`), MySQL, jQuery 3.4.1, Bootstrap 4.3.1

---

## 1. Executive Summary

Pearland411 is a CodeIgniter 3 community directory site with a functioning admin panel, registration/login, business listing creation, and a checkout flow tied to a `subscriptions` / `plans` model. The codebase contains a wide range of feature scaffolding — listings, news, events, videos, classifieds, blogs, products, real estate (`house`), rentals, marketplace, coupons, ads, sliders, enquiries, reviews, and a newsletter. On the surface it looks broad. In practice it is shallow: most modules are CRUD pages glued together with copy-paste, the admin panel and the user dashboard share the same session, the payment flow is mocked, and the controllers and models break almost every CodeIgniter best practice (one 5,200-line `Admin.php`, one 6,800-line `User.php`, one 4,600-line `Usermodel.php`, raw `htmlentities()` inserted into the database, `md5()` passwords, raw SQL with string-interpolated `$_POST`).

The project is built on **CodeIgniter 3, which is end-of-life** (the official CI3 branch is no longer receiving security patches) and the `composer.json` requires PHP `>=5.3.7` — also long out of support. There is **no migrations/SQL file in the repo**, so the database schema is live-only and cannot be reproduced from source. There is **no `.gitignore`** for caches/zips, so dozens of `*.zip` snapshots of old controllers/views are tracked in git. **Production database credentials, Authorize.Net API keys, and SMTP placeholders are committed in plain text** in `application/config/database.php` and `application/config/authorize_net.php`. CSRF protection is **disabled**, global XSS filtering is **disabled**, and the admin/user authentication uses the same session key (`logged_user`), meaning a regular registered user's session can in many cases satisfy admin-side access checks.

**Estimated completion against the woodlandsonline.com vision:** ~35–40% feature breadth, ~20% production quality.
**Production-readiness verdict: NOT READY.** Launching this codebase in its current state would expose customer PII, payment card data, and the database to trivially exploitable attacks. Significant security remediation and architectural cleanup are required before this site can responsibly take real users or money. A rebuild on a maintained framework should be considered (see §12).

---

## 2. Technology & Architecture Overview

### 2.1 Framework, language, and entry point

| Item | Value | Notes |
|---|---|---|
| Framework | CodeIgniter **3.1.11** (`system/core/CodeIgniter.php` line ~`const CI_VERSION = '3.1.11'`) | **End-of-life.** CI3 is unmaintained; security patches are not being issued. |
| `composer.json` PHP requirement | `>=5.3.7` | PHP 5.3 is over a decade unsupported. Production servers should be on PHP 8.1+. |
| Environment default | `'development'` (`index.php:56`) | Exposes errors/stack traces to visitors when `CI_ENV` is not set. |
| Default controller | `User` (`application/config/routes.php`) | Routes `(:any)` → `User/$1` — the public site is served entirely by the `User` controller. |
| Admin entry | `/admin` → `Admin/index` | Single 5,223-line controller. |
| Encryption key | empty (`config.php:328` — `$config['encryption_key'] = '';`) | Sessions/cookies are not encrypted. |
| CSRF protection | `FALSE` (`config.php`) | All form posts unprotected against CSRF. |
| Global XSS filtering | `FALSE` (`config.php`) | XSS must be handled per-output; coverage is inconsistent. |

### 2.2 Database

There is **no schema dump, no migration file, no seeder**, and no database documentation in the repo. `find . -name "*.sql"` returns nothing outside `system/`. The schema below is reverse-engineered from model queries.

| Table | Purpose | Notes / Concerns |
|---|---|---|
| `admin` | Admin login (single-table; no roles) | `admin_password` stored as **MD5** (Adminmodel.php:151–153). |
| `user` | End users / business owners | `password` stored as **MD5** (Usermodel.php:125, 154). `banned` flag, `user_zip_code`, `sale_representative`, `hear_us`. |
| `listings` | Business directory entries | Has `is_deleted`, `listing_status`, `listing_slug`, `category_id`, `sub_category_id`, `listing_lat/lng`, `google_map`, `cover_image`, `virtual_tour_link`. |
| `listing_categories`, `listing_sub_categories` | Listing taxonomy | |
| `listing_reviews` | Reviews/ratings on listings | `review_status`, `rating`, free-text. |
| `listing_likes` | Per-listing likes | |
| `claim_listings` | "Claim this listing" requests | |
| `enquiries` | Contact-form submissions across modules | |
| `events` | Community events | |
| `news`, `news_categories` | News articles | |
| `blogs` | Blog posts | |
| `videos`, `video_categories` | Video gallery (YouTube embed) | |
| `classifieds`, `classifieds_categories` | Classifieds | |
| `products`, `product_categories`, `product_sub_categories` | Marketplace products | |
| `marketplace`, `marketplace_categories` | Separate "marketplace" module (overlaps `products`) | Unclear separation from `products` — likely orphaned. |
| `coupons` | Coupons | |
| `house`, `house_categories` | Real estate / homes-for-sale | |
| `rental`, `rental_categories` | Apartments/rentals | |
| `slider` | Homepage banner slider | |
| `ads`, `ads_prices` | Display ads (paid placement) | `ad_status`, `ad_total_cost`, `ad_start_date`, `ad_end_date`. |
| `plans`, `plan_durations` | Subscription plan catalog | |
| `subscriptions` | Active user subscriptions | `total_amount`, `currency`, `billing_cycle`. |
| `transactions` | Payment transaction log | `txn_ref`, `response_payload` (JSON, **stores full POST body**). |
| `payment_cards` | Saved card metadata | Stores card holder + **last 4 digits** (good), but the controller passes the full card number through `substr(..., -4)` from `$_POST` without any tokenization (User.php:6618). |
| `user_usage_tracking` | Per-feature usage counters for plan limits | |
| `newsletter_subscriptions` | Newsletter sign-ups w/ confirmation token | |

**Schema-level concerns** (also expanded in §8):
- No migrations means no reproducible environment for QA, staging, or new developers.
- No FK constraints can be inferred from any model; deletes are by primary key only.
- Soft-delete is inconsistent (`is_deleted` exists on `listings` but not most siblings).
- `marketplace*` and `products*` overlap — looks like dead/parallel code.
- `category_status` filtering in `getCategoriesForHeader()` is unreliable enough that the controller has a fallback at User.php:46–59 that ignores the filter.

### 2.3 Folder structure & code organization

The layout is stock CodeIgniter 3, but quality is poor.

| Concern | Evidence |
|---|---|
| Mega-controllers | `application/controllers/Admin.php` = **5,223 lines / ~140 public actions**. `application/controllers/User.php` = **6,824 lines / ~95 public actions**. |
| Mega-models | `application/models/Adminmodel.php` = **2,420 lines**. `application/models/Usermodel.php` = **4,609 lines**. |
| Source-controlled backups | 23 `*.zip` files of old controllers/views/models tracked in git (`Admin.php-20-11.zip`, `User.php-20-11-2022-2.zip`, `Usermodel.php.zip`, `style.css.zip`, `header.php.zip`, etc.). These leak prior-version source if the web server serves `application/` (it shouldn't, but the risk is real). |
| Multiple "abandoned" view sets | `application/views/extra html/` (with a space in the directory name), `application/views/html_extra_files/`, `home-backup.php`, `old_index.php`, `index.php.zip`, `listing_details_n.php`, `listing_list_n.php`, `pricing_n.php`, `event_details_n.php`, `add_listing_step_1..6.php` plus `edit_listing_step_1..6.php` — three or four parallel design experiments coexist. |
| Logic in views | `application/views/index.php:1–80` includes a **PHP weather-API proxy with `Access-Control-Allow-Origin: *`** wired into the view file. Views also build SQL-aware data and run `htmlspecialchars` ad hoc. |
| Commented-out blocks | `User.php:2189–2645` is hundreds of lines of commented-out `add_listing_step_*` methods. |
| Dead/test endpoints in production controller | `User::test_listing_urls()` (3074), `User::test_payment_gateway()` (3106), `User::simple_test()` (6180), `User::test_ajax()` (6187) are all routable on the public site. |
| `_remap` magic | `User::_remap()` (User.php:93–120) mixes URL handling with controller dispatch. |

### 2.4 Third-party libraries

| Library | Source | Notes |
|---|---|---|
| Authorize.Net (custom) | `application/libraries/Authorize_net_lib.php` | Hand-rolled XML client; sandbox endpoint hardcoded. |
| CKEditor | `assets/ckeditor/` | Bundled, version unverified. |
| jQuery 3.4.1 | `assets/js/jquery.min.js` | Released 2019, has known XSS CVEs (CVE-2020-11022/11023); current is 3.7.x. |
| jQuery UI 1.12.1 | CDN (`header.php`) | Released 2016. |
| Bootstrap 4.3.1 | CDN (`header.php`), also bundled in `assets/css/bootstrap.css` | Released 2019; current is 5.3.x. |
| Select2 4.0.3 | CDN | Released 2016. |
| jspdf, dom-to-image, slick, popper, html5shiv, respond.js | bundled | `html5shiv`/`respond` are IE8/9 polyfills — unnecessary in 2026. |
| Composer dependencies | none beyond CI itself | No `composer.lock`, no `vendor/` discipline. |

---

## 3. Implemented Features — Admin Panel

The admin panel is a single class (`application/controllers/Admin.php`) with ~140 public actions. There is one role only — there is no sub-admin/manager/editor role despite a `sub_admin_*` view set existing (`application/views/admin/sub_admin_all.php`, `sub_admin_create.php`, `sub_admin_edit.php` — orphaned, no controller methods).

### 3.1 Admin feature inventory

| Module | Actions in `Admin.php` | Notes |
|---|---|---|
| Auth | `index` (login, line 347), `logout` (564), `forgot_password` (557 — view only) | MD5 password, raw SQL login, `forgot_password` is a stub. |
| Dashboard | `dashboard` (576) | Counts users/listings/events/products/houses/classifieds/rentals/ads + monthly stats + total subscription revenue. |
| Listings — Categories | `all_listing_category` (627), `add_new_listing_category` (675), `edit_listing_category` (710), `delete_listing_category` (761) | |
| Listings — Sub-Categories | `all_listing_sub_category` (773), `add_new_listing_sub_category` (845), `edit_listing_sub_category` (874), `delete_listing_sub_category` (904), `get_listing_sub_category` (916) | |
| Listings | `all_listings` (928), `add_new_listings` (979), `add_new_listing` (990), `add_listing_scratch` (1001), `edit_listing` (1127), `listing_preview` (1249), `edit_listings` (1278), `create_duplicate_listing` (1290), `claim_listing` (1322), `trash_listing` (1332), `restore_listing` (1374), `listing_delete` (1394), `delete_listing` (1408) | Multiple parallel "add" methods, two delete methods. |
| Enquiries | `all_enquiry` (1422), `enquiries` (4325), `saved_enquiry` (4340), `delete_enquiry` (4355), `delete_saved_enquiry` (4364) | Read enquiries from contact forms. |
| Products — Categories | `all_product_category` (1442), `add_new_product_category` (1509), `edit_product_category` (1547), `delete_product_category` (1615) | |
| Products — Sub-Categories | `all_product_sub_category` (1627), `add_new_product_sub_category` (1699), `edit_product_sub_category` (1729), `delete_product_sub_category` (1758), `get_product_sub_category` (1771) | |
| Products | `products` (1786), `product_details` (1845), `add_product` (1867), `edit_products` (1924), `delete_product` (2020) | |
| Blogs | `blog_list` (2050), `blog_details` (2118), `add_blog` (2128), `edit_blog` (2170), `delete_blog` (2226) | |
| News | `news_list` (2239), `news_details` (2308), `add_news` (2326), `edit_news` (2369), `delete_news` (2426) | |
| News Categories | `all_news_category` (2439), `add_news_category` (2452), `edit_news_category` (2488), `delete_news_category` (2539) | |
| Videos | `video_list` (2717), `video_details` (2786), `add_video` (2796), `edit_video` (2838), `delete_video` (2895) | |
| Video Categories | `all_video_category` (2556), `add_video_category` (2617), `edit_video_category` (2653), `delete_video_category` (2704) | |
| Classifieds | `classifieds_list` (3082), `classifieds_details` (3147), `add_classifieds` (3165), `edit_classifieds` (3209), `delete_classifieds` (3268) | |
| Classifieds Categories | `all_classifieds_category` (2913), `add_classifieds_category` (2979), `edit_classifieds_category` (3017), `delete_classifieds_category` (3067) | |
| Marketplace (orphan) | `all_marketplace_category` (3286), `add_marketplace_category` (3299), `edit_marketplace_category` (3335), `delete_marketplace_category` (3386), `marketplace_list` (3401), `marketplace_details` (3410), `add_marketplace` (3420), `edit_marketplace` (3464), `delete_marketplace` (3523) | Parallel to `products`. Likely abandoned. |
| Rentals | `all_rental_category` (3541), `add_rental_category` (3607), `edit_rental_category` (3643), `delete_rental_category` (3694), `rental_list` (3708), `add_rental` (3775), `edit_rental` (3849), `delete_rental` (3933), `rental_details` (3946) | |
| Houses (Real Estate) | `add_house` (10), `edit_house` (101), `delete_house` (187), `house_list` (199), `all_house_category` (367), `add_house_category` (428), `edit_house_category` (484), `delete_house_category` (546) | |
| Events | `all_events` (3987), `event_details` (4055), `add_new_event` (4073), `edit_event` (4123), `delete_event` (4185) | |
| Slider | `slider_all` (4212), `slider_create` (4224), `slider_edit` (4257), `slider_delete` (4310) | Homepage hero slider. |
| Claimed Listings | `claimed_listings` (4400), `claim_approve` (4415), `delete_claim` (4443) | |
| Generic status updater | `update_status` (4377) | **Critical: accepts arbitrary `tableName` + `fieldName` + `value` from POST.** See §7. |
| Ads (banner placements) | `current_ads` (4459), `create_ads` (4472), `ads_edit` (4525), `ads_delete` (4590), `ads_request` (4718), `ads_history` (4731), `ads_history_delete` (4743), `approveAdStatus` (4758) | Manual ad CRUD; no slot/zone abstraction. |
| Ads pricing | `ads_price` (4602), `create_ads_price` (4615), `ads_price_edit` (4663) | |
| Subscription plans | `pricing` (4780), `pricing_plan` (4869), `add_pricing_plan` (4881), `get_pricing_plan` (4930), `get_pricing_plan_durations` (4945), `update_pricing_duration` (4955), `edit_pricing_plan` (4971), `delete_pricing_plan` (5017) | |
| Subscriptions | `all_subscriptions` (5030), `subscription_view` (5085), `cancel_subscription` (5099), `delete_subscription` (5109), `user_usage_stats` (5119), `check_user_limit` (5131), `purchase_subscription` (5200) | |
| Newsletter | `newsletter_subscribers` (256), `delete_newsletter_subscriber` (299), `cancel_newsletter_subscription` (309) | |

### 3.2 Admin features that exist as views but are NOT wired up

The following view files exist with no corresponding controller actions — they are **dead UI**:

- `application/views/admin/sub_admin_all.php`, `sub_admin_create.php`, `sub_admin_edit.php` (sub-admin/role management UI; no methods).
- `application/views/admin/saved_enquiries.php` exists but maps to a stubbed action.
- `application/views/admin/all_subscriptions.php` is wired but the underlying flow is half-built.
- `application/views/admin/pricing.php` and `pricing.php` view content does not match the controller's data shape in places.
- `application/views/admin/extra html/` — entire alternate set of templates (note the directory name has a space, which often breaks deployment tooling).

### 3.3 Cross-cutting admin concerns

- **No role/permission system.** A row in the `admin` table grants full access to every action.
- **Same session key as users** (`logged_user`). The `Admin::__construct()` (Admin.php:320) only checks `$this->session->userdata('logged_user')` exists, not that the session is for an admin user. A logged-in regular customer's session will satisfy the check on most admin URLs (see §7 for severity).
- **No CSRF tokens** on any admin form (CSRF disabled globally).

---

## 4. Implemented Features — Registered User / Business Owner

All user-area code lives in `application/controllers/User.php`. Public vs. logged-in pages are distinguished by a single hard-coded allow-list in `User::__construct()` (line 31) — every method **not** in the allow-list requires a session.

### 4.1 Account & profile

| Feature | Method(s) | Notes |
|---|---|---|
| Registration | `register` (1661) | `usermodel::registerUser()` — MD5 password, `htmlentities()` applied **before** insert (corrupts data round-trip), `is_unique[user.email_id]` enforced. |
| Login | `login` (1700) | MD5 + raw SQL string interpolation. Redirects to `/dashboard`. |
| Forgot password | `forgot_password` (1751) | View-only. **No actual reset flow** — no token table, no email send, no reset endpoint. |
| Logout | `logout` (1768) | `sess_destroy()`. |
| Dashboard | `dashboard` (1798) | Shows listings count, events, blogs, subscription summary, plan limits, usage counters. |
| Profile / account | `my_profile` (1857), `my_account` (1869), `account` (1912), `update_account` (1918) | Multiple overlapping methods; `update_account` re-hashes password with MD5 (User.php:1957). |

### 4.2 Business listings

| Feature | Method(s) | Notes |
|---|---|---|
| List my listings | `user_listing` (3232) | |
| Add listing | `add_new_listing_start` (3022), `add_listing_scratch` (3316) | Older `add_listing_step_1..6` are commented out; current flow is single-page `add_listing_scratch`. |
| Edit listing | `edit_listing` (3639), `edit_listing_step_1..5` (2646–3022) | Step-based editor still exists. |
| Per-listing reviews moderation | `user_listing_reviews` (6207), `update_review_status` (6292) | Approve/reject reviews on the user's listings. |

### 4.3 Other content the user can publish

| Feature | Method(s) |
|---|---|
| Events | `user_events` (457), `add_user_event` (5716), `edit_user_event` (5824) |
| News | `user_news` (4522), `user_add_news` (4613), `user_edit_news` (4705) |
| Blogs | `user_blog_posts` (555), `add_blog` (671), `edit_blog` (761) |
| Videos | `video_list` (4795), `add_video` (4865), `edit_video` (4961) |
| Classifieds | `user_classifieds` (5037), `add_classifieds` (5107), `edit_classifieds` (5205) |
| Products | `products` (3782), `add_product` (3977), `add_new_product` (4073), `edit_products` (3866) |
| House (real estate) | `user_house_for_sale` (4098), `add_house` (4198), `edit_house` (4305) |
| Rentals | `user_apartments_and_rental` (5332), `add_rental` (5404), `edit_rental` (5514) |
| Enquiries inbox | `enquiries` (890), `delete_enquiry` (976) |

### 4.4 Subscriptions / payments / ads

| Feature | Method(s) | Notes |
|---|---|---|
| Pricing plans | `pricing_plan` (6380) | Lists active plans + durations. |
| Checkout | `checkout` (6412) | Renders billing form pre-filled from `user`. |
| Process checkout | `process_checkout` (6491) | **Payment is mocked** — `$payment_successful = true; // This would be actual payment result` (User.php:6605). Card data is read from POST and a `card_last4` row is written, but no live Authorize.Net call is made. |
| Checkout success | `checkout_success` (6704) | Reads `checkout_success_data` from session. |
| Ads summary (per-user) | `ads_summary` (4446) | Lists the user's purchased ads. |
| Submit review on a listing | `submit_review` (5910) | |

**Subscription quota enforcement** is partly implemented: `Admin::checkSubscriptionBeforeAction()` (5147) and `trackUsageAfterSuccess()` (5170) exist, plus `getSubscriptionDataForDashboard()` (5178), but they're invoked inconsistently (e.g. user-side `add_*` actions don't always call them).

---

## 5. Implemented Features — Frontend (Public Visitors)

Routes outside `User::__construct()`'s logged-in gate are public. The allow-list is a hard-coded `if`-chain at `User.php:31`.

| Public feature | Method | Notes |
|---|---|---|
| Home page | `index` (123) | Latest listings, latest videos, latest news, header categories, random 10 categories. Includes an **embedded weather-API proxy** in the view (`application/views/index.php:1–80`) with `Access-Control-Allow-Origin: *`. |
| Static pages | `about_us`, `faq`, `contact_us`, `privacy_policy`, `terms_of_use` | Hard-coded view files. |
| Browse listings (all) | `all_listing` (1269) | Category-filtered listing pages with pagination. |
| Listing detail | `listing_details` (2027), `listing_details_global` (285) | Reviews, gallery, Google Maps embed (iframe), virtual tour, YouTube embed. |
| Category index | `all_category` (1166), `category_search` (1237) | |
| Search | `search` (334), `search_suggestions` (404), `advanced_search` (429) | |
| News | `news` (225), `news_details` (2139) | |
| Videos | `videos` (1546), `video_details` (2089) | YouTube embed via slug. |
| Events | `event` (1457), `event_details` (5661), `event_search` (1517) | |
| Classifieds | `classifieds` (1603), `classifieds_details` (5284) | |
| Blogs | `all_blog` (1403), `blog_details` (834), `blog_post` (653), `blog_list` (662) | |
| Products | `all_products` (1017), `product_details` (1120) | |
| Houses | `house_details` (4393) | Browse list lives behind login. |
| Rentals | `rental_details` (5604) | Browse list lives behind login. |
| Enquiry / contact | `add_enquiry` (5967) | Universal handler for listing/blog/event/product enquiries. |
| Pricing | `pricing_plan` (6380) | Public page; checkout requires login. |
| Newsletter signup + double opt-in | `subscribe_newsletter` (6757), `newsletter_confirm` (6811) | Sends an email via `mail()` (no SMTP). |
| Test endpoints | `test_listing_urls` (3074), `test_payment_gateway` (3106), `simple_test` (6180), `test_ajax` (6187) | **Should not exist on the public site.** They print debug HTML and DB rows. |

---

## 6. Missing Features (Compared to Woodlandsonline.com & Client Goals)

The reference site (woodlandsonline.com) is a long-running community portal with deep editorial, classified, business-services, and local-info coverage. Compared against it and against the client's stated business model (sell IT/web services to listed businesses, sell ad placements, drive community traffic), the gaps are substantial.

### 6.1 Feature parity matrix

Status legend: **✅ Implemented** · **🟡 Partial / scaffolded but not finished** · **❌ Missing**

| Capability | Pearland411 | Woodlandsonline | Status |
|---|---|---|---|
| Local business directory with categories | Yes | Yes | ✅ |
| Listing detail page with photos, hours, map | Yes | Yes | ✅ |
| Reviews & ratings on listings | `submit_review` + moderation | Yes | ✅ |
| Claim-this-listing flow | `claim_listings` table + admin approve | Yes | 🟡 (no email to claimer, no proof-of-ownership step) |
| Events calendar (date/category/recurring) | `events` table, list & detail | Robust calendar w/ filtering | 🟡 (no calendar view, no recurring events, no "add to Google/iCal") |
| News / editorial section | `news`, `news_categories` | Daily news, multiple verticals | 🟡 (no editor workflow, no scheduling, no author bylines) |
| Blogs / community columns | `blogs` | Yes | 🟡 |
| Classifieds (free + paid) | `classifieds`, categories | Yes | 🟡 (no paid promotion tier, no expiry, no flag/report) |
| Job board | None (no `jobs` table or controller) | Yes (employment section) | ❌ |
| Forums / community discussion | None | Yes (very active forums) | ❌ |
| Real estate / homes-for-sale | `house`, `house_categories`, detail page | Yes (MLS-feed integration) | 🟡 (no MLS feed, no agent profiles, no map search) |
| Apartments / rentals | `rental`, detail page | Yes | 🟡 |
| Restaurant guide / menus | None (would be a `listings` category) | Dedicated section w/ menus | ❌ |
| Photo galleries | Per-listing only | Site-wide community galleries | 🟡 |
| Video gallery (YouTube embeds) | `videos`, categories | Yes | ✅ |
| Newsletter signup + double opt-in | `newsletter_subscriptions` + token confirm | Yes | ✅ |
| Newsletter campaign send | None (only subscribe + admin list) | Yes | ❌ |
| Coupons / deals | `coupons` table + add/edit views | Yes | 🟡 (UI exists, no public coupon browse page wired in) |
| Ad slot / zone management (sell positions) | Single `ads` table; ads tied to manually entered "ads_price" rows | Yes — bookable inventory | 🟡 |
| Self-serve ad purchase by businesses | None — admin enters ads manually after sale | Yes | ❌ |
| Ad rotation, impression / click tracking | None (no tracking columns) | Yes | ❌ |
| IT/web service ordering system (the client's main monetization) | None | n/a | ❌ |
| Service catalog & quote requests | None | n/a | ❌ |
| Invoicing / receipts | None (only `transactions` row) | Yes | ❌ |
| Payment gateway (live) | Authorize.Net library exists; **checkout flow does not call it** — payment is mocked | n/a | ❌ |
| Recurring billing / dunning | None | n/a | ❌ |
| Refund / cancellation flows | `cancel_subscription` admin action only | n/a | 🟡 |
| Email notifications (transactional) | `send_subscription_email` w/ placeholder Gmail SMTP creds; newsletter uses `mail()` | Yes | 🟡 |
| Password reset by email | Login page link only — endpoint is a view stub | Yes | ❌ |
| Email verification on signup | None | Yes | ❌ |
| Two-factor / MFA | None | Optional | ❌ |
| Sitemap.xml | None in repo | Yes | ❌ |
| robots.txt | None in repo | Yes | ❌ |
| Schema.org / structured data | None | Yes (LocalBusiness JSON-LD) | ❌ |
| Open Graph / Twitter cards | Single OG image, hardcoded | Per-page | 🟡 |
| Mobile responsive | Bootstrap 4 grid present | Yes | 🟡 (untested; many bespoke widths in views) |
| Accessibility (WCAG) | Not addressed | n/a | ❌ |
| API / mobile app | None | n/a | ❌ |
| Site search across all content types | `search` only hits listings | Cross-content | 🟡 |
| Saved searches / favourites | None | Yes | ❌ |
| User-to-user messaging | None | Limited | ❌ |
| Weather widget | Open-Meteo proxy in `views/index.php` | Yes | 🟡 (works, but logic is in a view) |
| Polls / surveys | None | Yes | ❌ |
| Photo contests / community engagement | None | Yes | ❌ |
| Admin reporting / analytics | Monthly count charts on dashboard only | Yes | 🟡 |
| GDPR / privacy controls (export, delete) | Static "privacy" page only | n/a | ❌ |

### 6.2 Top business-model gaps (most damaging to monetization)

1. **No live payment processing.** `User::process_checkout` writes a "successful" subscription/transaction without calling Authorize.Net. The site cannot collect money today.
2. **No service-ordering / quote-request flow** for the IT/web services line — the central revenue stream is not represented anywhere in the code.
3. **No self-serve ad purchase or impression/click tracking.** Ads are sold offline and pasted into the admin manually. No reporting back to the buyer.
4. **No invoice generation, no receipt PDFs, no tax handling beyond a flat 10% in code.**
5. **No password reset, no email verification.** Will block real customer signup.

---

## 7. Code Quality Issues

Severity scale: **Critical** (immediate exploit / data loss risk) · **High** (likely to be exploited or to cause production failure) · **Medium** (maintainability, partial feature failures) · **Low** (polish).

### 7.1 Critical issues

| # | File:line | Issue | Recommendation |
|---|---|---|---|
| C1 | `application/config/database.php:33–35` | **Production DB credentials committed to git** in plain text (`'username' => 'pearlan_new'`, `'password' => 'Yk7wZ~srrish'`). | Rotate the password **immediately**. Move to env vars (`getenv('DB_PASS')`) or a `database.local.php` excluded by `.gitignore`. Audit git history with BFG/`git filter-repo` to scrub the leaked password from all prior commits. |
| C2 | `application/config/authorize_net.php:7–10` | **Authorize.Net API login, transaction key, signature key, and client key committed in plain text.** Even though sandbox, treat as compromised. | Rotate sandbox keys, move to env, add `.gitignore` entry. Never commit production keys. |
| C3 | `application/models/Usermodel.php:155` and `application/models/Adminmodel.php:153` | **SQL injection in login.** `"SELECT * FROM user WHERE email_id = '$email_id' AND password = '$password'"` — `$email_id` is `$_POST['email_id']` run only through `htmlentities()`, which does **not** escape SQL quotes. | Replace with CI's query builder: `$this->db->where('email_id', $email)->where('password', $hash)->get('user')`. Apply the same to all 7+ raw query sites in `Usermodel.php` (lines 1747, 1815, 1833) and `Adminmodel.php` (lines 17, 28, 42, 56, 121, 187, 958, 1114, 1365, 2011). |
| C4 | `application/models/Usermodel.php:125, 154`; `application/models/Adminmodel.php:151`; `application/controllers/User.php:1957` | **Passwords stored as MD5.** MD5 is not salted, not slow, and is trivially brute-forced with rainbow tables. | Migrate to `password_hash($pw, PASSWORD_DEFAULT)` and `password_verify()`. Force a password reset on next login for all existing users. |
| C5 | `application/controllers/Admin.php:4377–4398` (`update_status`) | **Arbitrary table/column update from POST.** Reads `tableName`, `tableIdName`, `fieldName`, `id`, `value` from `$_POST` and runs `$this->db->where($tableID, $id)->update($tableName, $data)` with no allow-list. Any logged-in user (admin or not — see C7) can update any row in any table, including `user.banned`, `admin.admin_password`, etc. | Replace with explicit per-resource endpoints. Hard-code the table + field whitelist. |
| C6 | `application/config/config.php` | **CSRF disabled** (`$config['csrf_protection'] = FALSE;`) and **global XSS filtering disabled**. | Enable `csrf_protection`, add `<?= form_open() ?>` everywhere, regenerate tokens on submission. Prefer per-output escaping over `global_xss_filtering` (which is deprecated guidance). |
| C7 | `application/controllers/Admin.php:336` and `application/controllers/User.php:33` | **Admin and user sessions share the same key (`logged_user`).** Admin checks only `if (!$this->session->userdata('logged_user')) redirect(base_url());` — a session created by a regular customer login satisfies the check. | Use distinct keys (`logged_admin` vs. `logged_user`); add a role check; prefer a proper auth library (e.g. Ion Auth, or rebuild the auth layer). |
| C8 | `application/controllers/User.php:6491–6698` (`process_checkout`) | **Payment is faked** (`$payment_successful = true;`). Stored "transaction" rows tell the user (and the admin's revenue dashboard) money was received. The full POST body — which contains the full `card_number`, `cvv`, `exp_month`, `exp_year`, `card_name` — is then JSON-encoded into `transactions.response_payload`. **This stores raw PAN + CVV in the DB. Massive PCI violation.** | Stop storing CVV ever. Stop storing the full PAN. Send the card data directly to Authorize.Net via `Authorize_net_lib`, store only the gateway tokens + last4 + brand. Until live, the checkout endpoint must refuse all card input. |
| C9 | `application/controllers/Admin.php:1992` and `application/controllers/User.php:5770` | **Unrestricted file upload.** `move_uploaded_file($files['tmp_name'][$i], 'uploads/products/' . uniqid('prod_') . '.' . $ext)` accepts any extension, no MIME check, into a web-served directory. Trivial PHP shell upload. | Use CI's `upload` library (which is used elsewhere) with `$config['allowed_types'] = 'jpg|jpeg|png|gif|webp'` and `is_image` validation. Store outside the web root and serve through a controller, or block PHP execution in `uploads/` with `.htaccess`. |
| C10 | `application/views/index.php:1–80` | **PHP weather-proxy embedded in a view file**, with `Access-Control-Allow-Origin: *`, `CURLOPT_SSL_VERIFYPEER => false`, executed when the home view is loaded. Mixing logic in views violates MVC and disables TLS validation. | Move to a controller method, enable TLS verification, scope CORS to your own origin. |

### 7.2 High-severity issues

| # | File:line | Issue | Recommendation |
|---|---|---|---|
| H1 | `application/controllers/User.php:3074, 3106, 6180, 6187` | Public test/debug endpoints (`test_listing_urls`, `test_payment_gateway`, `simple_test`, `test_ajax`) are routable on the public site and `echo` DB rows / phpinfo-like data. | Delete. |
| H2 | `index.php:56` | `ENVIRONMENT` defaults to `'development'`. Production servers without `CI_ENV` set will display PHP errors and stack traces to visitors. | Default to `production` and set `CI_ENV=development` only on dev. |
| H3 | `application/config/config.php:328` | `$config['encryption_key']` is empty. Sessions and any encrypted cookies fall back to weak defaults. | Set a 32-byte random key. |
| H4 | `application/controllers/User.php:5778, 6494, 6586, etc.` | Direct `$_POST[...]` and `$_FILES[...]` access throughout, bypassing CI's input class (which provides XSS filtering hooks). Mixed with `$this->input->post()`. | Standardize on `$this->input->post('field', TRUE)`. |
| H5 | `application/models/Usermodel.php:123–129`, `application/models/Adminmodel.php` (many) | **`htmlentities()` applied at the model layer before insert.** This corrupts data: an apostrophe in a name becomes `&#039;` in the DB and double-encoded in views. It also doesn't protect against SQLi. | Store raw data; escape on output (`html_escape()`). Use parameterized queries or the QB for SQL safety. |
| H6 | `application/views/listing_details.php` (98 echoes, 1 `htmlspecialchars` call) | Output not escaped. User-submitted listing names, addresses, descriptions are rendered raw — stored XSS. Same pattern in `news_details`, `event_details`, `product_details`, `classifieds_details`, `blog_details`. | Wrap every dynamic echo with `html_escape()`. Use `nl2br(html_escape($x))` for multi-line text. |
| H7 | `application/views/listing_details_n.php:583` | `iframe src="https://www.google.com/maps/embed?...!2d<?= $listing->map_longitude ?>!3d<?= $listing->map_latitude ?>..."` — listing-controlled values interpolated into a URL inside an iframe. Combined with H6 this is an XSS sink (and also breaks if values aren't numeric). | Validate as `float`, escape, or use `https://maps.google.com/maps?q=lat,lng&output=embed`. |
| H8 | `application/controllers/Admin.php:320–344` | Admin login bypass paths: the constructor only blocks methods other than `register`, `login`, `index`, `forget_password`. Any logged-in user (including a customer per C7) reaches all other actions. | Tighten the access guard, require an explicit admin role check. |
| H9 | `application/controllers/User.php:31` | The public allow-list is a 60+ item `if`-chain string-compared to `fetch_method()`. Fragile, easy to add a method and forget to gate it (e.g. `add_enquiry` is public — anyone can spam enquiries). | Replace with method-attribute / per-method `before` filter. Add CAPTCHA / rate limiting on `add_enquiry`, `submit_review`, `subscribe_newsletter`. |
| H10 | `application/models/Usermodel.php:691–700` | Hardcoded SMTP placeholder credentials (`'your-email@gmail.com'`, `'your-password'`) — emails will silently fail in production. | Move SMTP config to `application/config/email.php` with env vars; verify on deploy. |
| H11 | Many model methods (e.g. `Adminmodel.php:1625`, `Usermodel.php:1747, 1815, 1833`) | Dynamic table names and `$where` strings concatenated into raw SQL. | Refactor to QB. |
| H12 | 23 `*.zip` snapshots in repo + `home-backup.php`, `old_index.php` | Backup files in source control bloat the repo and risk being served by a misconfigured webserver. | Remove from repo, add to `.gitignore`, scrub from history. |

### 7.3 Medium-severity issues

| # | Issue | Recommendation |
|---|---|---|
| M1 | Mega-controllers / mega-models (`Admin.php` 5,223 LOC, `User.php` 6,824 LOC, `Usermodel.php` 4,609 LOC). | Split per-resource: `Admin_Listings`, `Admin_News`, `Admin_Ads`, etc. Same for models. |
| M2 | `User::_remap` at User.php:93 mixes URL routing with controller dispatch; `routes.php` is sparse and hyphenated routes are handled inconsistently. | Centralize routing in `routes.php`. |
| M3 | Hundreds of lines of commented-out code (e.g. `User.php:2189–2645`). | Delete. Git history is the backup. |
| M4 | Duplicate parallel modules: `marketplace*` and `products*`, `add_listing_step_*` and `add_listing_scratch`, `_n` view variants. | Pick one path; delete the others. |
| M5 | Inconsistent slug generation; some modules slugify by name, some by id. | Centralize a `slug_helper`. |
| M6 | View files contain `<style>` blocks (e.g. `application/views/admin/dashboard.php`). | Move to `assets/admin/css`. |
| M7 | Pagination config copy-pasted into every list method (~20 occurrences). | Extract to a helper. |
| M8 | `User::dashboard` calls 6 model methods and shows partial data even when the user has no subscription, with no caching. | Add basic caching or aggregate query. |
| M9 | `application/views/extra html/` — directory name with a space. | Rename or delete. |
| M10 | `application/cache/` writable, not gitignored beyond `index.html`. | Add `.gitignore`. |

### 7.4 Low-severity issues

| # | Issue |
|---|---|
| L1 | `composer.json` requires PHP `>=5.3.7`; should be raised to `>=8.1`. |
| L2 | No `.editorconfig`, no PHP-CS-Fixer / phpcs config; inconsistent indentation (mix of tabs and spaces). |
| L3 | `readme.rst` is the unmodified CI3 framework README — there is no project-specific readme. |
| L4 | `application/config/constants.php` and friends untouched from CI defaults. |
| L5 | Logs at `application/logs/log-2026-03-11.php` exist in repo; should be gitignored. |
| L6 | `.DS_Store` committed at repo root. |
| L7 | `assets/css/icon.woff2` referenced via `css/icon.woff2` (relative) — breaks at sub-paths. |
| L8 | jQuery 3.4.1 has CVE-2020-11022/11023; upgrade to 3.7.x. |

---

## 8. Database Issues

There is **no schema dump in the repo** — the items below are inferred from the queries in `Adminmodel.php` / `Usermodel.php`. The first action is to export the live schema and check it in.

| # | Issue | Recommendation |
|---|---|---|
| D1 | **No SQL/migration files in the repo.** No way to stand up a clean dev database. | Export current schema as `database/schema.sql`. Adopt CI3 migrations (`application/config/migration.php` already exists, just unused) or move to a tool like Phinx. |
| D2 | **No foreign key constraints visible in any query.** `listings.category_id`, `listings.user_id`, `listing_reviews.listing_id`, `subscriptions.plan_id`, `transactions.subscription_id`, etc. are all unenforced. | Add FKs with `ON DELETE` rules. Today, deleting a category leaves dangling `listings.category_id` rows. |
| D3 | **No indexes mentioned** on hot columns. Every model uses `where('listing_slug', ...)`, `where('email_id', ...)`, `where('user_id', ...)`, `where('category_id', ...)` — all need indexes. | Add indexes: `user(email_id)` UNIQUE; `listings(listing_slug)` UNIQUE; `listings(category_id)`, `listings(user_id)`; `listing_reviews(listing_id)`; `subscriptions(user_id, status)`; `events(event_slug)`; `news(news_slug)`; `classifieds(classifieds_slug)`; `house(house_slug)`; `rental(rental_slug)`; `newsletter_subscriptions(email)`. Without these, list/search pages will slow down rapidly as data grows. |
| D4 | **Inconsistent naming.** `user_id` vs. `u_id` (in `ads.u_id`, `Admin.php:4501`); `created_at`/`user_created`/`ad_start_date` mixed; some tables prefix columns with their table name (`listing_*`), others don't (`coupons`, `house`, `rental`). | Standardize: snake_case, table-prefixed primary key (`listing_id`, `user_id`), `created_at`/`updated_at` on every table. |
| D5 | **Inconsistent timestamps.** `users.user_created` exists but no `updated_at`; `listings.created_at` exists, `updated_at` is unclear; `events`, `news`, `coupons`, `house`, `rental` are inconsistent. | Add `created_at` + `updated_at` to every table; let MySQL set defaults. |
| D6 | **Soft-delete only on `listings`** (`is_deleted`). Other content types delete hard, which loses audit trail. | Add `is_deleted` + `deleted_at` consistently, or a generic `audit_log`. |
| D7 | **Marketplace tables look orphaned.** `marketplace`, `marketplace_categories` exist alongside `products`, `product_categories`. The frontend uses `products`. | Confirm with client and drop one tree. |
| D8 | `transactions.response_payload` is `JSON(_POST)` — stores card number + CVV. | See C8 — fix at controller level; existing rows must be sanitized. |
| D9 | `payment_cards` stores last4 + brand + holder name (good) but **no gateway customer/payment-profile token**. The site cannot charge a saved card without re-collecting it. | Store Authorize.Net `customerProfileId` + `paymentProfileId`. |
| D10 | `subscriptions` uses `total_amount` + `currency` + `billing_cycle` but the `process_checkout` controller comment notes the table "does not include duration_id/duration_days/duration_months/price columns" — controller works around it. | Add the missing columns or retire the workaround. |
| D11 | `listing_categories.category_status` is filtered in some queries and ignored in others (`User.php:46–59` falls back when status filtering returns 0 rows). | Decide a single semantic; backfill the column. |
| D12 | `enquiries` table appears to hold contact-form submissions from listings, blogs, events, products in one shape — but the controller (`User::add_enquiry`) accepts `$source` + `$identifier` from the URL. | Add a `source_type` column with a CHECK constraint and an index on `(source_type, source_id)`. |
| D13 | `slider` table holds homepage banners but there is no scheduling (start/end date) — admin must manually toggle `is_active`. | Add `starts_at` / `ends_at`. |
| D14 | `ads` table has no impression/click counters and no `placement_zone` foreign key. Combined with no rotation logic, ads are static. | See M-tier recs in §10. |
| D15 | `user.banned` is a tinyint flag — no reason, no banned-by, no banned-at. | Add audit columns or move to a `user_status` enum + `user_audit` table. |
| D16 | Default DB collation/charset not pinned in `database.php`. | Set `'char_set' => 'utf8mb4', 'dbcollat' => 'utf8mb4_unicode_ci'` to handle emoji and full Unicode. |

---

## 9. Frontend / UX Issues

### 9.1 Performance & build

| # | Issue | Recommendation |
|---|---|---|
| F1 | No bundling / minification / cache-busting. Each page loads `bootstrap.css`, `style.css`, `homepage.css`, `new-styles.css`, `fonts.css`, `custom.js`, `customCode.js`, `customCode2.js`, `slick.js`, `select-opt.js`, `jquery-ui.js`, `popper.min.js`, `bootstrap.min.js`, etc. — no concat. | Adopt Laravel Mix / Vite / a simple gulp pipeline. Ship one CSS + one JS bundle with hashed filenames. |
| F2 | jQuery and Bootstrap loaded **twice** (CDN in `header.php` + bundled copies in `assets/`). | Pick one source. |
| F3 | Listing detail page is 1,531 lines of mixed PHP+HTML+inline `<style>` + `<script>`. First paint will be heavy. | Break into partials, defer non-critical CSS, lazy-load images. |
| F4 | Images served from `uploads/` are not resized; admin saves the user-provided file. Hero images and gallery thumbs use the same source. | Generate sized variants on upload (the `image_helper.php` and `uploadAndResize` helper exist — wire them up consistently). |
| F5 | Bootstrap 4.3.1 (2019). | Upgrade to Bootstrap 5.3 (or replace with a lighter stack like Tailwind). |
| F6 | jQuery UI 1.12.1 (2016) loaded for what appears to be a date picker. | Replace with `<input type="date">` or Flatpickr. |
| F7 | `html5shiv` and `respond.min.js` (IE8/9 polyfills) still loaded. | Delete. |
| F8 | No HTTP caching headers, no service worker, no CDN configuration. | Add at the webserver layer. |

### 9.2 Mobile responsiveness

- The viewport meta tag is set (`header.php:9`) with `maximum-scale=1` — **bad for accessibility** (prevents users from pinch-zooming). Remove `maximum-scale`.
- Bootstrap 4 responsive classes are used in templates, but many views (`listing_details.php`, `index.php`) override widths inline (`style="width: 100%;"`, `style="margin-right: 10px;"`) — not tested on small screens.
- Admin panel uses `padding-left: 290px;` on the dashboard container (`admin/dashboard.php:6`) for a fixed left sidebar — will overflow on tablet/mobile.

### 9.3 Browser compatibility & rendering

- IE polyfills loaded but no clear browser support matrix. In 2026 IE support can be dropped.
- Uses `<?= ?>` short echo — fine on PHP 7+, but `composer.json` still claims PHP 5.3 support.

### 9.4 Accessibility

- No `aria-*` attributes on interactive elements seen in `index.php` slider, header dropdowns, forms.
- Many `<a>` tags use `href="#"` for click handlers.
- Color contrast not assessed.
- No skip-to-content link.
- Form fields lack associated `<label>` in several views.
- Recommended: run axe DevTools / Lighthouse Accessibility audit; target WCAG 2.1 AA.

### 9.5 SEO

| # | Issue | Recommendation |
|---|---|---|
| S1 | `application/views/header.php:12–13` — `<meta name="description" content="">` and `<meta name="keyword" content="">` are **empty on every page**. | Per-controller `$data['meta_description']` and `$data['meta_keywords']`; render in `header.php`. |
| S2 | `<title>Pearland411</title>` is **hardcoded** in `header.php:5` regardless of `$title` passed in. | Use `<title><?= isset($title) ? html_escape($title) : 'Pearland411' ?></title>`. |
| S3 | No `sitemap.xml` and no controller that generates one. | Add a `sitemap` controller that emits XML for listings, news, events, blogs, categories. |
| S4 | No `robots.txt`. | Add one that allows crawling and points to the sitemap. |
| S5 | No structured data. Local-business listings are the prime use case for `LocalBusiness` JSON-LD with name, address, geo, phone, hours, aggregateRating. | Add JSON-LD on `listing_details.php`, `event_details.php`, `news_details.php`, `blog_details.php`. |
| S6 | URLs are mostly slugged (good) but inconsistent (`listing-details/`, `news_details/`, `event-details/`, `video_details/` — mix of dashes and underscores). | Normalize to dashes; redirect underscored variants. |
| S7 | Open Graph image is hardcoded to the logo. No per-page OG image, OG title, OG description, Twitter card. | Per-page social meta. |
| S8 | Canonical tags missing. | Render `<link rel="canonical" href="<?= current_url() ?>">`. |
| S9 | No 301 strategy for the dozens of duplicate URL variants (`listing_details_n`, `listing_list_n`, `event_details_n`). | After cleanup, redirect old URLs. |
| S10 | Page load time (uncompressed, many requests) will hurt Core Web Vitals. | See §9.1. |

---

## 10. Recommended Improvements (Prioritized)

### 10.1 Must Fix Before Launch (security + critical bugs)

These are non-negotiable. The site cannot go live for real users until they are addressed.

1. **Rotate all leaked credentials** (DB, Authorize.Net) and scrub from git history. (C1, C2)
2. **Replace MD5 password hashing with `password_hash`/`password_verify`** and force a global password reset. (C4)
3. **Remove all raw-SQL string interpolation; use the query builder or parameter binding.** Audit every `db->query("...$var...")` call. (C3)
4. **Stop storing card numbers and CVV** — refactor `process_checkout` to send card data straight to Authorize.Net via `Authorize_net_lib`, store only gateway tokens + last4 + brand. Sanitize existing `transactions.response_payload` rows. (C8)
5. **Replace the generic `Admin::update_status` with per-resource endpoints.** (C5)
6. **Separate admin and user sessions** (`logged_admin` vs `logged_user`) and add a real role check on every admin action. (C7)
7. **Enable CSRF protection** and add tokens to every form. (C6)
8. **Fix file uploads**: use the CI `upload` library with a strict allow-list, validate MIME, store outside the web root or block PHP execution in `uploads/`. (C9)
9. **Move config secrets to environment variables**, set `ENVIRONMENT=production`, set `encryption_key`, set real SMTP credentials. (H2, H3, H10, C1, C2)
10. **Delete the public test endpoints** (`test_listing_urls`, `test_payment_gateway`, `simple_test`, `test_ajax`). (H1)
11. **Add output escaping** (`html_escape()`) on every dynamic echo in detail views. (H6, H7)
12. **Implement a real password-reset flow** (token table + email), and email verification on signup. (Section 6.2 #5)
13. **Remove all `*.zip` backup snapshots, `home-backup.php`, `old_index.php`, `extra html/` directory, `application/views/admin/extra html/`** from the repo, and add a proper `.gitignore`. (H12)
14. **Pin PHP to 8.1+** and bump CodeIgniter to its security-patched mainline (or migrate, see Phase 3).

**Effort estimate for "Must Fix Before Launch": ~120–160 hours (3–4 dev-weeks for one experienced PHP dev).**

### 10.2 Phase 1 — Core Missing Features (to match the client's business model)

Required to actually monetize. The site cannot earn revenue today.

1. **Live Authorize.Net integration on `process_checkout`** — replace the mock with the existing `Authorize_net_lib`; receipts; failed-payment handling; basic invoice PDF.
2. **IT/web service catalog + quote-request flow.** New tables (`services`, `service_packages`, `service_requests`), public service catalog page, request form, admin queue.
3. **Self-serve ad slot purchase.** Define `ad_zones` (homepage hero, sidebar, listing-page banner, footer), book by date range, charge via Authorize.Net, basic impression/click tracking.
4. **Recurring billing** for subscriptions (Authorize.Net ARB) + dunning emails.
5. **Email backbone**: configure SMTP (one source of truth), templated transactional emails (signup, verify, reset, listing claim, enquiry received, ad approved, subscription renewal), use the CI `email` library or a service like SendGrid/SES.
6. **Password reset + email verification** (also listed in Must Fix).
7. **Basic CAPTCHA / rate limiting** on register, login, contact, enquiry, review, newsletter.
8. **Sitemap + robots.txt + JSON-LD** for indexing the directory.
9. **Admin role/permission system** (admin, editor, sales, support).
10. **Remove the marketplace duplicate module** or merge with products.

**Effort estimate for Phase 1: ~280–360 hours (~7–9 dev-weeks).**

### 10.3 Phase 2 — Enhancements (to approach woodlandsonline.com scope)

1. **Events calendar** with month/week views, recurring events, "add to Google Calendar" / iCal export, category filtering.
2. **News editorial workflow**: drafts, scheduling, author profiles, related articles, RSS feed.
3. **Cross-content site search** (currently `search` only hits listings).
4. **Job board** module (employers post, jobseekers apply or visit external link).
5. **Forums / community discussion** — either build (lots of work) or integrate Discourse via SSO.
6. **Coupons / deals public browse page** (the admin and tables exist; the public side is missing).
7. **Restaurant guide** as a curated `listings` view with menu uploads.
8. **Review moderation queue** for the admin (currently per-listing-owner only).
9. **Saved listings / favourites for visitors.**
10. **Newsletter campaign sender** (admin composes + sends to the `newsletter_subscriptions` list).
11. **Photo galleries / community uploads.**
12. **Polls, photo contests, business-of-the-month** — community engagement.
13. **Accessibility (WCAG 2.1 AA) pass** + keyboard nav + a11y testing in CI.
14. **Mobile responsiveness audit & fix** across all detail/list pages.
15. **Image pipeline**: generate sized variants on upload; serve via `<picture>`/srcset.
16. **Analytics**: integrate GA4 / Plausible; admin dashboards beyond monthly counts.
17. **GDPR/CCPA**: data export, account delete.

**Effort estimate for Phase 2: ~600–900 hours (~15–22 dev-weeks).**

### 10.4 Phase 3 — Long-term modernization

1. **Migrate off CodeIgniter 3.** CI3 is end-of-life. Two realistic paths:
   - **CI4** if the team wants to keep the CodeIgniter mental model — supports PHP 8.x, modern routing, real ORM, CLI, official migrations. Migration is rewrite-shaped, not search-and-replace.
   - **Laravel 11/12** (recommended) — far larger ecosystem, Eloquent, Sanctum/Fortify for auth, Cashier for billing, Nova/Filament for admin, Livewire/Inertia for frontend. The hiring market is also much larger.
2. **Headless option**: Laravel API + Next.js / Nuxt frontend if a mobile app is on the roadmap.
3. **Containerize** (Docker) + CI/CD (GitHub Actions) + staging environment + automated backups.
4. **Test coverage**: PHPUnit / Pest for models, browser tests (Dusk/Playwright) for the checkout and listing flows.
5. **Observability**: Sentry for errors, basic uptime monitoring, structured logs.

**Effort estimate for Phase 3: ~700–1100 hours (~17–27 dev-weeks) for a Laravel rebuild that achieves Phase 1 + Phase 2 parity. See §12 for the build-vs-rebuild decision.**

---

## 11. Estimated Effort

Estimates assume one senior PHP/full-stack engineer (~$80–$140/hr in the US market depending on geography). Use the day-rate × effort columns to budget.

### 11.1 Must Fix Before Launch

| Item | Effort |
|---|---|
| Rotate credentials, scrub git history | 4–6 h |
| MD5 → `password_hash` migration + forced reset flow | 12–16 h |
| Replace all raw-SQL queries with QB / parameter binding | 24–32 h |
| Fix `process_checkout` to use Authorize.Net + remove PAN/CVV storage + retroactively scrub `transactions.response_payload` | 24–32 h |
| Remove `Admin::update_status` and replace with per-resource endpoints | 12–16 h |
| Separate admin/user sessions + role middleware | 8–12 h |
| Enable CSRF + add tokens to forms | 12–20 h |
| Harden file uploads | 8–12 h |
| Move config to env, set encryption key, default to production env | 4–6 h |
| Remove test endpoints + zip backups + alternate view sets + `.gitignore` | 4–6 h |
| Output-escape every dynamic echo in detail/list views | 16–24 h |
| Real password reset + email verification | 12–16 h |
| **Subtotal** | **140–198 h (≈ $11k–$28k)** |

### 11.2 Phase 1 — Core Missing Features

| Item | Effort |
|---|---|
| Live Authorize.Net checkout (one-time + recurring/ARB) + receipts + invoice PDF | 60–80 h |
| Service catalog + quote-request flow | 50–70 h |
| Self-serve ad slot purchase + impression/click tracking | 60–80 h |
| Email backbone (SMTP / SES / SendGrid) + transactional templates | 24–32 h |
| CAPTCHA + rate limiting | 8–12 h |
| Sitemap + robots.txt + JSON-LD | 12–16 h |
| Admin role/permission system | 24–32 h |
| Marketplace cleanup / merge | 8–12 h |
| **Subtotal** | **246–334 h (≈ $20k–$47k)** |

### 11.3 Phase 2 — Enhancements

| Item | Effort |
|---|---|
| Events calendar with recurrence + iCal | 50–70 h |
| News editorial workflow + RSS | 30–40 h |
| Cross-content search (Algolia or MySQL FT/Meilisearch) | 30–50 h |
| Job board module | 50–70 h |
| Forums (Discourse SSO) or build basic forum | 40–80 h |
| Coupons public side + redemption tracking | 24–32 h |
| Review moderation admin queue | 16–24 h |
| Newsletter campaign sender | 30–40 h |
| Photo galleries (with moderation) | 30–40 h |
| Saved listings / favourites | 16–24 h |
| Polls / contests / business-of-the-month | 30–40 h |
| WCAG 2.1 AA pass | 50–80 h |
| Mobile responsiveness pass | 60–80 h |
| Image pipeline (srcset, WebP, sized variants) | 24–32 h |
| Analytics + GDPR data tools | 24–40 h |
| **Subtotal** | **504–742 h (≈ $40k–$104k)** |

### 11.4 Phase 3 — Modernization

| Item | Effort |
|---|---|
| Greenfield Laravel rebuild that re-implements Phase 1 + Phase 2 features on a clean schema, plus data migration from existing DB | **700–1100 h (≈ $56k–$154k)** |
| Test suite + CI/CD + staging | 80–120 h |
| Observability + monitoring | 16–24 h |
| **Subtotal** | **796–1244 h (≈ $64k–$174k)** |

### 11.5 Headline numbers

| Path | Total effort | Total cost (mid-range $110/hr) |
|---|---|---|
| **Salvage path** (Must Fix + Phase 1 + Phase 2) | ~890–1,274 h | **~$98k–$140k** |
| **Rebuild path** (Must Fix small subset to keep current site limping + Phase 3 fresh build) | ~880–1,300 h | **~$97k–$143k** |

The two paths land in roughly the same dollar range. The rebuild gets you a maintainable, secure, hireable-for codebase at the end. Salvage gets you the same feature set on a dead framework with a decade of accumulated debt.

---

## 12. Final Recommendation

**Recommendation: rebuild on a modern stack (Laravel 11/12), with a 4–6 week stabilization window on the existing site to keep it safe in the meantime.**

**Reasoning:**

1. **The framework is dead.** CodeIgniter 3 is end-of-life. There are no security patches coming. Any path that keeps CI3 forever is a path that keeps shipping unpatched software.
2. **The code is structurally unsalvageable for incremental improvement.** Two ~6,000-line controllers and a 4,600-line model with copy-paste pagination, parallel "marketplace vs products" modules, three sets of "edit listing" flows, hundreds of lines of commented-out code, and zero tests cannot be incrementally cleaned up faster than they can be re-written. Any large refactor that tries to keep this code's identity will get bogged down.
3. **Security work alone justifies the rebuild.** The list of Critical issues (raw SQL on login, MD5 passwords, faked payments storing PAN+CVV in `transactions`, an arbitrary-table-update endpoint, shared admin/user session, hardcoded credentials in source) is not a "fix the bugs" list — it is symptomatic of an architecture that was never security-aware. Addressing each surface individually still leaves the same patterns elsewhere.
4. **The business-critical revenue features don't exist yet.** Live payments, ad self-serve, IT-services ordering, and recurring billing are all unbuilt. They are roughly the same effort to build on a fresh stack as on the existing one — and on the fresh stack they'll be far easier to maintain.
5. **The hiring story matters.** Hiring PHP devs willing to maintain a 5,000-line CI3 controller is hard and getting harder; hiring Laravel devs is easy.
6. **The dollar math is ~equivalent** (see §11.5). Same budget, dramatically better outcome.

### Recommended path

- **Weeks 0–4 (parallel track A — "stop the bleeding" on the current site).** Land items #1–#10 of §10.1 only — credentials, MD5, raw SQL, mock checkout (disable it entirely until rebuild), `update_status`, sessions, file uploads, kill test endpoints, env+encryption-key, remove zip backups. **Do not** invest in feature work or refactor on CI3 beyond this.
- **Weeks 0–24 (parallel track B — "rebuild").** Greenfield Laravel project. Schema designed properly with FKs, indexes, migrations. Filament for the admin. Cashier (Authorize.Net adapter or Stripe) for billing. Implement Phase 1 features as part of the rebuild. Migrate data over via one-shot migration script (export from `pearlan_new` → import).
- **Weeks 20–24.** Cutover. Old site goes read-only or 301s to the new one. Phase 2 features ship over the following months.

If the client refuses a rebuild, the second-best option is **CI4 migration** rather than continuing on CI3 — but the effort to migrate to CI4 is similar to the effort to rewrite in Laravel, and the strategic benefits of Laravel are significantly larger.

The least-defensible option is "keep building features on CI3." Every feature added to this codebase makes the eventual cleanup more expensive, while leaving the security exposure live in production.

---

*End of report.*




