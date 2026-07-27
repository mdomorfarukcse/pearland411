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
- **Migration `0017_home_ad_slots.sql`** — five new `ads_prices` zones: `home-top-leaderboard`
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
## 2026-07-27 — Fix admin pricing page (blocked script, duplicate jQuery, unreachable Save) + term dropdown

### Fixed
- **Mixed content killed the admin JS.** 30 views loaded Chosen from
  `http://harvesthq.github.io/chosen/chosen.jquery.js` — plain HTTP on an HTTPS page, so browsers
  blocked it. `admin-custom.js` then called `$('.chosen-select').chosen()`, which threw
  `$(...).chosen is not a function` and aborted the rest of that ready handler, taking unrelated
  admin UI down with it. All 30 references now use the HTTPS cdnjs build, and the call is guarded so
  a missing widget degrades to a plain `<select>` instead of breaking the page.
- **`admin/pricing.php` loaded a second copy of jQuery (3.6.0) and Bootstrap (4.6.0)** mid-page,
  on top of the jQuery 3.4.1 / Bootstrap 4.3.1 that `admin/footer.php` loads after the view. That
  replaced `window.jQuery` halfway down the document, so plugins and handlers registered against one
  instance were invisible to the other — leaving the Edit Pricing Plan modal and the duration editor
  unreliable. The duplicates are gone; a small `ppAdminReady()` shim defers this page's two inline
  blocks until the footer's jQuery exists.
- **The Save Plan button was unreachable.** The plan form is long enough that the modal grew past
  the viewport, putting the footer below the fold with no way to scroll to it — edits could be typed
  but never saved. `.modal-body` is now height-capped and scrolls, with the footer sticky. A direct
  cap is used rather than Bootstrap's `.modal-dialog-scrollable` because a `<form>` sits between
  `.modal-content` and `.modal-body`, which breaks that rule's flex assumptions.
- **Checkout showed no term choice.** `planTermOptions()` used the configured `plan_durations` rows
  *instead of* the standard multiples, and the seed gives every plan exactly one row (1 month) — so
  there was one option and the dropdown was hidden. Configured rows and multiples are now merged,
  keyed by term length: an admin-configured term wins (keeping its discount) and 3/6/12/24 are
  always offered.
- **A 42-day term was labelled "1 month."** `termLabel()` rounded any span of 28+ days to months.
  It now converts to months only for exact multiples of 30, so day-based placements read in days.

### Verified
- Term logic extracted from the shipped source and exercised directly: a $15/month plan with one
  configured 1-month row yields 5 options (1 month $15 configured, 3 months $45, 6 months $90,
  1 year $180, 2 years $360), no duplicate term lengths, legacy flat carts still resolve to the base
  term, and a 7-day plan labels in days (7 / 21 / 42 / 84 / 168).
- Checkout re-rendered for that exact case: the "Buy for" dropdown now renders with 5 options where
  it previously showed static "1 month" text.
- Admin modal markup rendered headless at 800px and 1000px viewport heights: the Save Plan button is
  fully visible in both and `.modal-body` scrolls. (Bootstrap's CSS is CDN-blocked in the build
  environment, so the harness inlines the modal rules being relied on.)

---

## 2026-07-27 — Plan terms: buy for 12 months / 2 years, fix admin duration saves

### Fixed
- **Saving a plan duration in admin did nothing.** `plan_durations` was never created by a
  migration — it existed only in the production database, and there it lacks `currency` and
  `updated_at`. `Adminmodel::updatePricingDuration()` wrote `updated_at` on every save, so the
  UPDATE errored and the admin screen discarded the change while looking like it worked. Added
  migration **`0015_plan_durations_columns.sql`** (creates the table when missing, back-fills the
  missing columns when present) and made the model write only columns that exist. The endpoint now
  reports the real DB error instead of a bare "DB update failed".
- **The billing area's upgrade/downgrade payment had the same broken-`fetch` + missing-CSRF bug**
  fixed for the cart last change — `user_account.php` called `plan-change-init` and
  `process-checkout` via `fetch`. Both now go through `ppPost`.
- **Every admin plan card looked identical.** Presence/absence of a feature was conveyed only by an
  icon, so when the icon font did not render, Basic Listing appeared to include "Featured listing"
  and "Analytics dashboard". Absent features are now muted and struck through, and `Up to 0 …` rows
  for unused content types are no longer printed.
- **"Next Billing" / "Renews on"** in the account page were shown for any active plan, including
  ones with auto-renew off. They now say "Expires On" unless the subscription actually renews.

### Added
- **Term selection at checkout.** Each cart line gets a "Buy for" dropdown — 1 / 3 / 6 / 12 / 24
  periods — priced from the plan's configured `plan_durations` rows when an admin has set them
  (so long-term discounts apply), otherwise from multiples of the plan's base period. The line
  shows **"Runs until <date>"** and the total re-prices server-side. New endpoint
  `POST /cart-set-term`; the term is validated against what the plan actually offers rather than
  trusted from the request.
- **Auto-renew opt-in restored at checkout.** `cart_checkout.php` had no checkbox (the legacy
  `checkout.php` did), so every purchase was one-off regardless of the plan being recurring. It is
  shown when the cart holds a recurring tier and flows through `cart_pay_init` →
  `recordCartPurchase` → `activateHelcimSubscription`.
- **Admin can add and delete plan terms**, not just re-price existing ones — previously there was
  no way to offer a 12- or 24-month option at all. Months/days are editable per row.
- Migration **`0016_transaction_term.sql`** — `term_days`, `expires_at`, `duration_id` on
  `transactions`, so a 12-month banner records its real expiry instead of being reported against the
  plan's 30-day base period. Existing rows are back-filled.

### Changed
- The session cart now carries the chosen term per line (`plan_id` + `duration_id`/`multiplier`).
  Old flat `[plan_id, ...]` carts are still accepted, so a cart built before this change keeps
  working.
- `getUserPurchasedPackages()` reads the purchased expiry when present, falling back to the plan's
  base period on databases that have not run 0016.

### Verified
- Browser test through `ppPost` against a stub CSRF gate: add → change term posts
  `plan_id=7&multiplier=12` with the token and re-prices, no page errors.
- Checkout rendered with mixed terms (12-month listing + 3-month banner): dropdowns, per-line
  "Runs until", auto-renew box and the recomputed total all correct at 1280px and 390px.

### Not verified
- No MySQL in this environment, so migrations 0015/0016 are **unrun**. Until 0015 is applied the
  admin duration save will still fail — the model guard keeps it from erroring, but the columns must
  exist for the value to persist.

---

## 2026-07-26 — Security: block `.git/` from the web, allow `.env` above the webroot

Prompted by a review of secret handling before live Helcim credentials were added. The `.env`
mechanism itself checked out — gitignored, `.htaccess`-denied, and every config file has read
through `env()` since the first commit (`499a926`), so **no credential has ever been committed
and nothing needs rotating**. Two gaps around it did need closing.

### Security
- **`.git/` was served over HTTP.** Deployment is `git pull` on the server, so `.git/` sits in
  the webroot. The existing `<FilesMatch>` rules match file *names* ending in `.git`, which
  `.git/config`, `.git/HEAD` and `.git/index` do not — and the `RewriteCond %{REQUEST_FILENAME} -f`
  passthrough served them directly. Anyone could reconstruct the full source from
  `https://<site>/.git/`. Added two `RewriteRule`s in the root **`.htaccess`**, placed *before*
  the passthrough, denying `.git/` and dot-paths generally, with a `.well-known/` carve-out so
  cPanel/AutoSSL ACME renewal keeps working. Impact was source disclosure only.
- **`.env` can now live above the webroot.** `index.php` calls `pearland411_load_env()` twice —
  `dirname(__DIR__) . '/.env'` first, then the repo-root path as a fallback. Safe because the
  loader (`application/config/env.php:62`) only sets a key when it is not already in the
  environment, so the parent file wins and the fallback backfills; call order is significant.
  Existing installs keep booting untouched. On cPanel: `mv ~/public_html/.env ~/.env && chmod 600 ~/.env`.

### Added
- **`Helcim_lib::health_check()`** — a live credential check. `is_configured()` only tests that the
  token string is non-empty, so a token missing the checkout scope still rendered a working-looking
  "Pay securely" button (`checkout.php:435-442`) that failed only when a real customer clicked it.
  `health_check()` calls `/helcim-pay/initialize` (nothing is charged; the session is never
  redeemed) and maps the outcome to an actionable message — naming the specific dashboard control
  to fix on `401`/`403`, distinguishing network/TLS failure, and passing other HTTP statuses
  through. Reuses the existing private `request()` helper.
- **Payments panel on `/admin/setting`** — read-only, deliberately outside the settings form since
  credentials live in `.env` and not in `site_settings`. Always shows the cheap
  Configured / Not-configured state; the live check runs only behind an explicit
  "Test connection" link (`?payments_check=1`) so an outbound API round-trip is not added to every
  page load. Notes that a green result proves the *checkout* scope only — auto-renewal charges go
  through `/payment/purchase`, gated separately by "Transaction Processing", which is verified by
  running `Cli::renew_subscriptions`.

### Fixed
- **Canonical domain corrected to `https://pearland411.com`** (was `new.pearland411.com`, a
  staging hostname). Updated the `base_url` fallback in `application/config/config.php`, the
  `Sitemap:` line in `robots.txt`, `BASE_URL` in `.env.example`, the OG-image placeholder on
  `/admin/setting`, and the note in `REVIEW_AND_ISSUES.md`. The `robots.txt` sitemap URL mattered
  most — it was pointing crawlers at the wrong host.
- **Three hardcoded absolute logo URLs in `application/views/listing_details.php`** (listing
  header, review avatars, related listings) now call `base_url('assets/images/pearland-logo.png')`
  like the rest of the file, so the fallback image follows `BASE_URL` instead of pinning a domain.

Not changed: `application/views/error_log` still names `new.pearland411.com`, but those are
filesystem paths in a historical log (the cPanel docroot is literally named that), not URLs.

### Changed
- `README.md` — new "Where to put `.env`" subsection under **Environment**, including why Apache
  `SetEnv` is the wrong place for secrets here (`.htaccess` is tracked in git).

Key files: `.htaccess`, `index.php`, `README.md`, `application/libraries/Helcim_lib.php`,
`application/controllers/Admin.php`, `application/views/admin/admin_setting.php`. No migration.

---

## 2026-07 — Fix: cart AJAX dead in production (broken fetch + missing CSRF token)

### Fixed
- **"Add to bundle" threw `Cannot read properties of undefined (reading 'then')`.** `assets/js/csrf.js`
  (and potentially other third-party scripts) replace `window.fetch` with a wrapper; when a wrapper
  returns `undefined`, every `fetch(...).then(...)` caller dies. The cart and checkout both called
  `fetch` directly, so **the entire payment path — `cart-pay-init` and `process-checkout` — failed the
  same way**, not just the bundle button.
- **No CSRF token on any cart POST.** `csrf_protection` is TRUE and `cookie_httponly` is TRUE, so JS
  cannot read the token cookie — the cart's POSTs would 403 in production regardless of the fetch bug.

### Added
- **`assets/js/pp_post.js`** — `window.ppPost(url, params, onOk, onErr)`, the single way the cart and
  checkout talk to the server. Uses `XMLHttpRequest` (not monkey-patched by our own code, and no
  dependency on jQuery, which `footer.php` loads *after* view markup), reads the token from the
  `<meta name="csrf-token">` tag that `hooks/Csrf_inject.php` already injects, and appends it to the
  body where `Security::csrf_verify()` looks. Re-stores a rotated token into the meta tag and every
  hidden form input, and maps failures to readable messages (403 → "Your session expired…").

### Changed
- `pricing_plan.php` and `cart_checkout.php` now call `ppPost` instead of `fetch`; buttons disable
  during flight. Pricing page gained an inline error banner; checkout's pay button keeps its
  amount-bearing label ("Pay USD 70.00 securely") on reset instead of being overwritten with a
  hard-coded string. A `process-checkout` failure *after* Helcim approved now says the payment went
  through and to contact support, rather than inviting a second charge.

### Verified
- Browser test against a stub that mimics CI's CSRF gate **with `window.fetch` deliberately sabotaged
  to return `undefined`**: add and clear both work, the token rides in every POST body, and a 403
  surfaces the expiry message instead of failing silently.

---

## 2026-07 — Checkout restyled to match the pricing page

### Changed
- **`views/cart_checkout.php`** now shares the pricing page's design system (same palette, card
  radius, shadows and button treatment): gradient hero, a 3-step rail (Choose packages → Checkout →
  You're live), order lines with right-aligned prices and category captions, a prominent
  "Pay USD X securely" button, and a trust list (Helcim / encrypted / card details never stored).
  Guest state gets segmented Sign in / Create account tabs with restyled inputs and focus rings.
  Payment JS untouched — `#cco-err`, `#cco-pay`, `.cco-tab`, `.cco-pane`, `#pane-login`,
  `#pane-register` and the `data-plan` remove buttons all preserved. Verified at 1280px and 390px
  (no horizontal overflow), in both logged-in and guest states.

### Notes
- `views/checkout.php` is the legacy single-plan checkout and is **no longer loaded** by any
  controller (`User::checkout()` renders `cart_checkout`). Left in place, untouched.

---

## 2026-07 — Pricing page redesign (professional UI/UX pass)

### Fixed
- **Nested-document bug.** `views/pricing_plan.php` emitted a full `<!DOCTYPE><html><head><body>`
  document while being loaded *between* the `header` and `footer` views, so the site header/nav was
  rendered a second time in the middle of the page and the layout broke. It is now a proper view
  fragment.

### Changed
- **Rebuilt the pricing page.** Full-width gradient hero; each catalog section (Business Listing,
  Add-ons, Site Wide / Category Banners, Site Wide / Category Videos, Special Package) gets a
  heading + one-line explainer; packages render as an even, responsive card grid with clear
  price/interval, readable checkmark feature lists, and "Most popular" / "Best value" / "Your plan"
  badges. Verified at 1280px and 390px (no horizontal overflow).
- **Buy Now on every paid card** (`/buy-now?plan_id=X` → replaces cart → `/checkout`), with a
  secondary "+ Add to bundle" toggle for buying several things together.
- **Floating cart box replaced** by a slim sticky bottom bar (item count, total, item names, Clear,
  Checkout) that only appears once something is selected.

### Added
- **"Your active packages" strip** at the top of the pricing page for logged-in users — each owned
  package with its expiry date, days remaining, an "Auto-renews" pill, and an amber highlight when
  it expires within 14 days. Backed by `Usermodel::getUserPurchasedPackages()` (active subscriptions
  plus paid ad placements, expiry computed from the plan's `duration_days`).

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
