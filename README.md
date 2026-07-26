# Pearland411

A community business directory + news / events / classifieds /
services platform for Pearland, TX. Built on CodeIgniter 3 +
MySQL + jQuery / Bootstrap 4.

This README is the operator / new-developer landing page.
For the full audit and longer-term recommendations see
[`PROJECT_AUDIT_REPORT.md`](./PROJECT_AUDIT_REPORT.md).

---

## Quick start (test / staging server)

```bash
# 1. Pull the code
git clone <repo> pearland411 && cd pearland411

# 2. Set up environment
cp .env.example .env
# edit .env — see "Environment" below

# 3. Set up the CI3 database config (gitignored — won't be tracked)
cp application/config/database.php.example application/config/database.php
# The example file already reads everything from .env via env()
# calls. If you'd rather hard-code credentials, edit this file
# directly — your changes won't show up in `git status`.

# 4. Run the database migrations IN ORDER
mysql -u <user> -p <db> < database/migrations/0000_schema_fixups.sql
mysql -u <user> -p <db> < database/migrations/0001_auth_flows.sql
mysql -u <user> -p <db> < database/migrations/0002_services.sql
mysql -u <user> -p <db> < database/migrations/0003_newsletter_campaigns.sql
mysql -u <user> -p <db> < database/migrations/0004_rate_limits.sql
mysql -u <user> -p <db> < database/migrations/0005_admin_roles.sql
mysql -u <user> -p <db> < database/migrations/0006_ad_zones.sql
mysql -u <user> -p <db> < database/migrations/0007_site_settings.sql
mysql -u <user> -p <db> < database/migrations/0008_event_admin_host.sql
mysql -u <user> -p <db> < database/migrations/0009_rental_price_widen.sql
mysql -u <user> -p <db> < database/migrations/0010_plan_capabilities.sql
mysql -u <user> -p <db> < database/migrations/0011_service_registry.sql
mysql -u <user> -p <db> < database/migrations/0012_helcim_billing.sql
mysql -u <user> -p <db> < database/migrations/0013_subscription_lifecycle.sql

# Optional: give the existing Basic/Standard/Premium plans sensible
# freemium editing capabilities (photos/map/services/video). Edit any
# time in /admin/pricing.
mysql -u <user> -p <db> < database/seeds/plan_capability_defaults.sql

# If you're importing through phpMyAdmin instead, use the single
# bundled file:
#   database/migrations_combined_phpmyadmin.sql
# (see database/README.md)

# 5. (Optional but recommended) generate sized image variants for
#    legacy uploads so list pages serve the smaller _thumb.jpg /
#    _medium.jpg from the start.
php index.php cli images_backfill
```

Point your webserver at the repo root. The application boots through
`index.php` — `application/` and `system/` should not be web-served.

## Environment

Every secret / per-environment value lives in `.env` (see
`.env.example` for the full list). Required at minimum:

### Where to put `.env`

`index.php` looks for `.env` **above the webroot first**, then falls back to
the repo root. On cPanel (where the repo root is `public_html/`) put it in the
account home directory so it is not in a web-servable path at all:

```
mv ~/public_html/.env ~/.env
chmod 600 ~/.env
```

The repo-root location still works — it is denied by `.htaccess` — but
above-webroot survives a lost or overridden `.htaccess`. If both exist, the
above-webroot file wins and the repo-root one only fills in missing keys.
`.env` is gitignored; never commit it. Do not use Apache `SetEnv` for secrets:
`.htaccess` **is** tracked in git.

| Key | Purpose |
|---|---|
| `CI_ENV` | `production` on prod, `development` locally. Drives error display + DB query log. |
| `BASE_URL` | Public URL of the site, with trailing slash. |
| `ENCRYPTION_KEY` | 32-byte random key. Generate with `php -r 'echo bin2hex(random_bytes(16)), "\n";'` |
| `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME` | MySQL credentials. |
| `SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASS`, `SMTP_CRYPTO` | Outgoing email. For Gmail use an App Password, not the account password. |
| `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | "From" header on transactional + newsletter email. |
| `HELCIM_API_TOKEN` | Helcim API token (Settings ▸ Integrations ▸ API). While empty, `/checkout` shows a "being set up" notice instead of the pay button — this is the live-payments gate, so no separate feature flag is needed. |
| `HELCIM_WEBHOOK_VERIFIER_TOKEN` | Verifier token for signing inbound Helcim webhooks (`/helcim-webhook`). |

## First sign-in

The seeded admin is `admin@gmail.com` / `12345` (a legacy MD5 hash
in the dump). After running migrations:

1. Sign in once — the password upgrades to bcrypt transparently.
2. Visit `/admin/change-password` and rotate it.
3. Visit `/admin/sub-admins` to create per-person editor accounts
   for staff instead of sharing the super-admin login.

## What ships in this branch

This branch contains the security and feature work described in
[`PROJECT_AUDIT_REPORT.md`](./PROJECT_AUDIT_REPORT.md). High-level:

### Security
- All credentials sourced from `.env`, no secrets in git.
- `password_hash` / `password_verify`; legacy MD5 hashes are
  accepted once and transparently upgraded.
- Distinct `logged_admin` vs `logged_user` sessions; admin
  routes are gated by role (`super_admin` / `editor`).
- CSRF enforced globally with an output hook that auto-injects
  the token into every `<form method="post">` and exposes it
  to AJAX via a `<meta>` tag + `assets/js/csrf.js`.
- Output escaping helpers in `application/helpers/view_helper.php`
  (`e()` / `e_nl2br()` / `e_url()` / `e_attr()` / `json_ld()`) —
  used across detail + list views.
- File-upload hardening: MIME-sniffed `safe_upload_image()`
  helper, random filenames, auto-written `uploads/.htaccess`
  blocks PHP / CGI execution.
- Rate limiting (`application/libraries/Rate_limit.php`) on
  login / register / password-reset / enquiry / review /
  newsletter signup / service-request / claim-listing.
- Payments run through **Helcim** (HelcimPay.js). Card data is
  tokenized client-side — the PAN never reaches the server; the server
  only validates the returned transaction hash against a per-checkout
  secret. Listing edit is ownership-guarded.
- Real password reset + email-verification flows + Mailer
  library wrapping CI's email class around `.env` SMTP config.

### New features
- **IT/web services catalog** — public `/services` grid + detail
  pages, admin CRUD at `/admin/services`, quote-request inbox at
  `/admin/service-requests`, email notifications on each
  submission.
- **Newsletter campaigns** — compose / queue / batch-send pipeline
  at `/admin/campaigns`.
- **Sub-admins** — role-based CRUD at `/admin/sub-admins`.
- **Listing claims** — public `/claim-listing/<slug>` form;
  approval transfers ownership and emails both parties.
- **Coupons** — public `/coupons` browse + `/coupons/<id>` detail.
- **Cross-content search** — extended to include services +
  coupons; all detail links use friendly URLs.
- **SEO** — `/sitemap.xml`, `/robots.txt`, per-page meta /
  canonical / Open Graph / Twitter cards, JSON-LD on listings
  (LocalBusiness), news (NewsArticle), events (Event), blogs
  (BlogPosting), services (Service).
- **Image pipeline** — sized `_thumb.jpg` (200w) + `_medium.jpg`
  (800w) variants generated on upload, `responsive_img()` helper
  with `srcset` adopted on the homepage / list views, CLI
  backfill via `php index.php cli images_backfill`.
- **Weather widget proxy** — moved out of `views/index.php` into
  `application/controllers/Weather.php` with TLS verification
  and 15-minute response caching.
- **CKEditor** — rich-text editing on service body + newsletter
  campaign body.

## Project layout

```
application/
  config/            CI config (incl. env.php loader, routes.php, hooks.php)
  controllers/       Admin.php, User.php, Sitemap.php, Weather.php, Cli.php
  models/            Adminmodel.php, Usermodel.php
  views/             public/* and admin/*
  helpers/           image_helper.php (safe_upload_image, thumb_url, responsive_img), view_helper.php (escaping + JSON-LD)
  libraries/         Helcim_lib.php, Mailer.php, Rate_limit.php
  hooks/             Csrf_inject.php
assets/              CSS / JS / images (CDN-loaded JS via headers)
database/
  README.md          migration run order
  migrations/        0000..0005 SQL files
uploads/             user uploads (gitignored except .htaccess)
.env.example         every required env var, with comments
PROJECT_AUDIT_REPORT.md   the full audit with section-by-section findings
```

## Common operator tasks

### Send a newsletter campaign
1. `/admin/campaigns/new` — compose with CKEditor.
2. Save draft. From the campaign view, click **Queue for sending**.
3. Use the **Send next batch** button (default 25 / batch) or
   schedule a cron job hitting
   `https://your-host/admin/campaigns/send-batch/<id>` every 5
   minutes (cron auth-by-secret is a follow-up).

### Approve a listing claim
1. `/admin/claimed-listings`
2. **Approve** transfers ownership and emails the claimant.
3. **Reject** marks the claim rejected (status = 2) and emails
   the claimant a polite explanation.

### Edit the services catalog
The 10 seed services in `0002_services.sql` are placeholders. Edit
or replace them at `/admin/services` to match the real catalog.

## Packages, services & billing (2024 redesign)

- **Freemium listings.** A claimed listing can edit name / phone / email
  free; photos, map, services, a YouTube video and SEO details unlock per
  the owner's package (capability flags on `plans`, set in `/admin/pricing`).
- **Service registry.** `/admin/setting` ▸ Services turns optional
  sections (events, real estate, classifieds, news, blogs, products,
  videos) on/off — gating the admin sidebar, public nav and their pages.
  They ship **off**; activate what you want live. This is also the
  "hide a section for now" switch.
- **Helcim billing.** `/checkout` uses HelcimPay.js. Buyers can opt into
  **auto-renew** (the card token is vaulted). Upgrades on the account
  page charge a prorated difference immediately; downgrades apply at the
  next renewal; cancel turns off auto-renew and runs to term end.
- **Renewal cron** — schedule daily:
  ```
  php index.php cli renew_subscriptions
  ```
  It charges due vaulted cards, extends the term on success, and applies
  a 3-retry / 2-day-grace dunning policy (with email) before expiring.

## Known follow-ups

- **Migrate off CodeIgniter 3** — CI3 is EOL; see
  `PROJECT_AUDIT_REPORT.md` §12 for the long-term recommendation.

## License

MIT (CodeIgniter framework). All original code in this repository
is provided to the client under the same terms.
