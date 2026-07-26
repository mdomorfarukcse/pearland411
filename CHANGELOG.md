# Changelog

All notable changes to Pearland411 are recorded here, newest first. Format loosely follows
[Keep a Changelog](https://keepachangelog.com/). When you make a change, add an entry with the
migrations and key files touched so the next session doesn't have to re-read the codebase.

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
