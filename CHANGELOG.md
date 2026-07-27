# Changelog

All notable changes to Pearland411 are recorded here, newest first. Format loosely follows
[Keep a Changelog](https://keepachangelog.com/). When you make a change, add an entry with the
migrations and key files touched so the next session doesn't have to re-read the codebase.

---

## 2026-07 — Homepage redesign (brand-logo palette, real data, ad zones)

Rebuilt `views/index.php` on the actual logo colours (pear green `#8CC63F` + wordmark gray),
replaced every block of invented content with live database queries, and opened five new ad
zones. The view went from **4,381 lines to ~1,160**, and the page no longer loads jQuery,
Bootstrap, jQuery UI, Swiper, slick or FullCalendar — over 1 MB of JS replaced by ~5 KB of
vanilla script.

### Added
- **`assets/css/homepage.css`** (was an empty, unlinked file). Every class is `hp-` prefixed, so
  it cannot collide with the 345 KB `style.css` or with Bootstrap. Replaces the 2,228-line inline
  `<style>` block that overrode bare Bootstrap selectors (`.container`, `.col-md-4`,
  `.list-group-item`, …).
- **Branded image placeholder** `assets/images/placeholder-listing.svg` — a pear + camera "No photo
  yet" tile. Listings without a photo previously fell back to hot-linked Unsplash / techsmith.com
  images or the raw logo.
- **Six default ad creatives** in `assets/images/ads/` — `house-ad-*` ("advertise here") and
  `scs-ad-*` (Space Center Systems), each in `strip` (970x90), `wide` (900x300), `box` (600x500)
  and `tall` (300x600). Four shapes because the live zones span 10.8:1 to 0.5:1; one artwork
  cropped unreadably at the extremes.
- **Migration `0015_home_ad_slots.sql`** — five new `ads_prices` zones: `home-top-leaderboard`
  970x90, `home-sidebar-top` 300x250, `home-inline-billboard` 728x250, `home-sidebar-skyscraper`
  300x600, `home-footer-billboard` 970x250. Data only (0006 already added `slot_key`/`size`), so
  no DDL and no `DELIMITER` — the standalone file and the combined bundle block are identical.
- **`database/seeds/pearland_sample_content.sql`** — importable Pearland, TX demo content
  (8 categories, 12 businesses incl. 3 featured, 3 news, 3 videos, 4 coupons, 3 upcoming events)
  with a documented rollback. Coupons and events use relative dates so they never go stale.
- **Model:** `getUpcomingEvents()`, `getAdSlotRateCard()`, `getCategoriesWithCounts()`,
  `primeAdSlots()` / `getAdSlotMeta()`.
- **Helper:** guarded `pearland_slug()` in `view_helper.php`, replacing `createSafeSlug()` which
  the homepage declared three times (one unguarded).
- `service_ads_enabled` — one-line global off-switch honoured by `ad_slot.php`. Deliberately
  unseeded so the helper default keeps advertising on.

### Changed
- **`User::index()`** now passes `featured_listings`, `home_deals`, `home_events`,
  `home_categories`, `ad_rate_card`, `listing_plans` and `show_news`/`show_videos`/`show_events`.
  Listings widened 6 → 9.
- Content sections are gated on their `service_*_enabled` flag, so the homepage no longer links to
  pages that 404.
- Every section renders an honest empty state when its query returns nothing.
- **`partials/ad_slot.php`** — emits `width`/`height` parsed from `ads_prices.size` (fixes layout
  shift on every ad zone site-wide), gained `$slot_fallback` and `$slot_label`, and now unsets its
  own options so they can't leak into the next include.
- Ad rotation uses an explicit slot→creative map; `crc32() % 2` skewed 5:2 on the homepage set.
- The skyscraper and footer billboard ship with `$slot_fallback = false` — sellable, but invisible
  until sold rather than showing a 600px-tall house ad.
- Pricing block is built from `ads_prices` / `plans` instead of the hard-coded $29/$59/$99 that
  linked to plans actually costing $0/$15/$25.
- The website-services pitch appeared five times; it now appears twice (one sidebar card, one band).

### Fixed
- **The hero carousel never worked.** The view looped `$featured_listings`, which the controller
  never set, so it was permanently stuck on its "Featured Businesses Coming Soon" fallback slide.
- **Mobile rendered the sidebar first.** Two contradictory rules (`991.98px → column` vs
  `768px → column-reverse`) fought each other. One stacking rule now, main column first in the DOM.
- Four sidebar widgets carried `hide_on_phone`, so phone users lost weather, deals, the newsletter
  and events entirely. All visible now.
- `getActiveCoupons()` joined `listings` on the OWNER, not the coupon — a business with five
  listings returned the same coupon five times. Added `GROUP BY c.coupon_id`; also fixes `/coupons`.
- Seven ad zones would have fired seven `ORDER BY RAND()` queries; `primeAdSlots()` makes it one.
- Removed the per-card `getCategoryName()` N+1 — both queries already select `category_name`.
- Dropped a 404 request for the non-existent `assets/js/new.js`, plus duplicate jQuery (the CDN
  copy was 3.3.1 **slim**, which has no `$.ajax`), Popper and Bootstrap loads.
- Removed the external `i.ibb.co` logo hot-link and the Unsplash / techsmith.com image fallbacks.
- Lorem ipsum in the CTA band and "How It Works"; the typo "Achieve goles".
- Fixed unbalanced markup (4 stray `</div>`, 1 stray `</section>`) — the new view is tag-balanced.
- `ad_slot.php` used `$__ci =& get_instance()`, raising "Only variables should be assigned by
  reference" under `E_ALL`.
- CSS specificity bug caught in review: `.hp-page a` (0,1,1) beat `.hp-btn--white` (0,1,0), so
  white buttons rendered white-on-white. Base link rules are now wrapped in `:where()`.

### Known follow-ups
- `listings.listing_status = 0` means published, while 11 other listing queries in `Usermodel`
  filter on `= 1`. The two homepage sections agree with each other; the wider inconsistency is a
  data audit, logged in `REVIEW_AND_ISSUES.md`.
- `Admin::create_ads_price()` / `ads_price_edit()` still drop `slot_key`/`size` and reference
  undefined `$uploaded_image_path` / `$info['width']`, so new zones need SQL rather than the admin
  form.
- `/all-listing` and `/listing-details` are unchanged — still on the old navy palette.

---

## 2026-07 — Advertising cart & multi-item checkout

Turned the single-plan pricing/checkout into a multi-item cart so customers can buy several
things at once (a listing tier + add-ons + banners + videos + the special package).

### Added
- **Session-backed cart.** `User::cart_add/remove/get/clear/cart_pay_init` + explicit routes
  (guests may build a cart before logging in). Business-listing is single-select (radio); every
  other category is multi-select (checkbox).
- **Selectable pricing page.** `pricing_plan.php` — per-package radio/checkbox wired to the cart via
  AJAX + a sticky cart summary (items, total, per-item remove, Proceed to Checkout); persists across
  reloads.
- **Cart-aware checkout.** `User::checkout` now renders `views/cart_checkout.php` (line items +
  remove + total). Guests get inline **login / register** tabs (no forced redirect); logged-in
  users pay the whole cart total via HelcimPay.js. `Usermodel::recordCartPurchase` fulfills the
  cart — the listing tier activates the capability subscription, other items are recorded as paid
  transactions for admin fulfillment; `process_checkout` gained a `cart` mode.

### Changed
- `/checkout` no longer forces login or requires `?plan_id` — it is cart-driven (old `?plan_id=X`
  links still work by folding that plan into the cart).

---

## 2026-07 — Packages, Payments & Freemium redesign

Reworked monetization: freemium capability-gated listing editing, an admin service registry, a
switch from Authorize.Net to **Helcim**, and a full recurring-billing lifecycle. Shipped as
Sprints 0–8 on `claude/package-payment-system-redesign-a7q73r`.

### Added
- **Freemium listing capabilities.** New `plans` columns `can_photos`, `photo_limit`, `can_map`,
  `can_services`, `can_extra_details` (video reuses existing `allow_video`). Admin sets them per
  plan in `/admin/pricing`. Migration `0010_plan_capabilities.sql`.
  Optional seed `database/seeds/plan_capability_defaults.sql`.
- **Admin service registry (feature flags).** `/admin/setting ▸ Services` toggles optional sections
  (events, real estate, classifieds, news, blogs, products, videos) via `service_*_enabled` keys in
  `site_settings`. Gates the admin sidebar, public nav (`views/header.php`, `index.php`,
  `footer.php`), the user dashboard sidebar, and 404s the pages when off. Migration
  `0011_service_registry.sql` (seeds them **off**).
- **Helcim payments.** `application/libraries/Helcim_lib.php` (initialize HelcimPay.js checkout,
  SHA-256 hash validation, vaulted-card charge, webhook verify) + loaded `config/helcim.php`.
  Endpoints `helcim_init`, `process_checkout`, `helcim_webhook` in `User.php`; explicit routes in
  `routes.php`. Migration `0012_helcim_billing.sql` (Helcim columns on `transactions` /
  `payment_cards`). Env keys `HELCIM_API_TOKEN`, `HELCIM_WEBHOOK_VERIFIER_TOKEN`, `HELCIM_CURRENCY`.
- **Recurring / upgrade / downgrade / cancel.** Auto-renew opt-in at checkout (vaults the card
  token); daily cron `Cli::renew_subscriptions` charges due cards with a 3-retry / 2-day-grace
  dunning policy (Mailer emails). Account manage-plan panel (`user_account.php`): prorated upgrade,
  scheduled downgrade, cancel. Model helpers on `Usermodel` (`activateHelcimSubscription`,
  `getRenewableSubscriptions`, `recordRenewalSuccess/Failure`, `applyPlanUpgrade`,
  `scheduleDowngrade`, `cancelAutoRenew`). Migration `0013_subscription_lifecycle.sql`
  (`renewal_attempts`, `last_renewal_error`, `grace_until`, `pending_plan_id`).
- **Ad catalog seed** (commit `b8bd9bc`, separate from the sprints): `database/seeds/`
  `pearland411_packages.sql` builds the 22-package old-site catalog (listings tiers, banners,
  videos, $230 bundle) as `plans` + `plan_durations` rows; migration `0014_plan_category.sql`.

### Changed
- Checkout (`views/checkout.php`) now uses the HelcimPay.js modal instead of the disabled
  "coming soon" form; `pricing_plan` page (`/pricing-plan`) lists all active plans.
- README + `database/README.md` updated (migration run order 0000–0013, Helcim env, cron).

### Fixed
- Removed dead Authorize.Net library/config; `.env.example` AUTHNET_* block dropped.

### Security
- **Listing edit was unguarded** — any logged-in user could edit any listing by slug.
  `User::edit_listing` and the `edit_listing_step_*` methods now enforce ownership
  (`requireOwnedListing`). Gated rich fields are also stripped server-side so a crafted POST can't
  bypass the locked inputs.

### Known follow-ups
- **`plan_durations` missing columns.** `pearland411_packages.sql` and
  `Adminmodel::updatePricingDuration` reference `plan_durations.currency` and
  `plan_durations.updated_at`, which the base table lacks → add via a pending migration
  `0015_plan_durations_columns.sql` (idempotent `ADD COLUMN` for both) before running that seed.
- **Staging verification needed** (no MySQL/Helcim creds in the build env): confirm the HelcimPay.js
  response JSON shape used for hash validation, and that the account returns a reusable vaulted card
  token for renewals.
