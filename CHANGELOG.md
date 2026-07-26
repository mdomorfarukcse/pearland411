# Changelog

All notable changes to Pearland411 are recorded here, newest first. Format loosely follows
[Keep a Changelog](https://keepachangelog.com/). When you make a change, add an entry with the
migrations and key files touched so the next session doesn't have to re-read the codebase.

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
