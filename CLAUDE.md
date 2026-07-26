# CLAUDE.md — read this first

Pearland411 is a **community business directory + advertising platform** for Pearland, TX.
Stack: **CodeIgniter 3 + MySQL + jQuery / Bootstrap 4**. It is a **large** codebase.

## ⚠️ Start every session here (don't re-read the whole codebase)

1. Read **[`CHANGELOG.md`](./CHANGELOG.md)** — what has changed and why (newest first).
2. Read **[`docs/PROJECT_OVERVIEW.md`](./docs/PROJECT_OVERVIEW.md)** — architecture + feature map +
   key files/routes.

Those two give you the mental model in ~1 minute. After that, use **targeted search** (Grep/Glob)
for the specific area you're touching — the two mega-controllers alone are ~13k lines, so reading
them end-to-end wastes tokens.

## ✍️ After you make changes

Add a dated entry to **`CHANGELOG.md`** (Added / Changed / Fixed / Security). Note the migrations
and the key files touched. Keep `docs/PROJECT_OVERVIEW.md` accurate if you change architecture or
add a feature/route.

## Conventions to reuse (don't reinvent)

- **Config / secrets:** everything per-environment lives in `.env`, read via `env('KEY', $default)`
  (see `application/config/env.php`). Never hard-code credentials.
- **Settings & feature flags:** the autoloaded `site_setting('key', $default)` /
  `site_setting_save()` helpers back onto the `site_settings` table. Service on/off switches use
  `service_*_enabled` keys, managed at `/admin/setting`.
- **Migrations:** idempotent, in `database/migrations/NNNN_*.sql` (stored-proc / `INFORMATION_SCHEMA`
  guard pattern so re-runs are safe). Run order + a phpMyAdmin combined bundle are in
  `database/README.md`. Mirror every new migration into
  `database/migrations_combined_phpmyadmin.sql`.
- **CLI jobs:** `php index.php cli <method>` (`application/controllers/Cli.php`) — e.g.
  `images_backfill`, `renew_subscriptions`.
- **Payments:** Helcim via `application/libraries/Helcim_lib.php` + `application/config/helcim.php`
  (HelcimPay.js; card data never touches the server).
- **Routing:** `application/config/routes.php`; a catch-all `(:any)` → `User/$1` plus
  `translate_uri_dashes` maps most URLs to same-named `User` methods. Add explicit routes for
  session-less endpoints (e.g. webhooks).

## Deeper references

- `README.md` — operator/setup guide (env, migrations, first sign-in, cron).
- `PROJECT_AUDIT_REPORT.md` — full audit + long-term recommendations (large; read sections, not all).
- `REVIEW_AND_ISSUES.md` — known issues log.
