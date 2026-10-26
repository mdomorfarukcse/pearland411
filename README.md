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
| `SMTP_TIMEOUT` | Seconds to wait on the SMTP conversation. Default 30; CodeIgniter's own default of 5 is short enough that a slow TLS handshake looks like a bad password. |
| `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | "From" header on transactional + newsletter email. **Must be an address the SMTP account may send as** — see [Email troubleshooting](#email-troubleshooting). |
| `MAIL_PROTOCOL` | `smtp` / `mail` / `sendmail`. Blank = SMTP when host+user+pass are set, otherwise the host's own MTA. |
| `HELCIM_API_TOKEN` | Helcim API token (Settings ▸ Integrations ▸ API). While empty, `/checkout` shows a "being set up" notice instead of the pay button — this is the live-payments gate, so no separate feature flag is needed. |
| `HELCIM_WEBHOOK_VERIFIER_TOKEN` | Verifier token for signing inbound Helcim webhooks (`/helcim-webhook`). |
| `RECAPTCHA_SITE_KEY`, `RECAPTCHA_SECRET_KEY` | Google reCAPTCHA **v2 "I'm not a robot" checkbox** key pair for the public forms — see [Turn on reCAPTCHA](#turn-on-recaptcha-spam-protection). While either is empty the captcha is off (the hidden trap field still works). |

## First sign-in

The seeded admin is `admin@gmail.com` / `12345` (a legacy MD5 hash
in the dump). After running migrations:

1. Sign in once — the password upgrades to bcrypt transparently.
2. Visit `/admin/change-password` and rotate it.
3. Visit `/admin/sub-admins` to create per-person editor accounts
   for staff instead of sharing the super-admin login.

## Email troubleshooting

Signup verification and password reset both depend on outgoing mail, and login
is blocked until a new account's address is confirmed — so when mail stops, new
users cannot sign in at all. Two tools answer "is email working?":

- **`/admin/email-diagnostics`** (super-admin). Shows the effective settings,
  flags the combinations known to break delivery, sends a test message that
  reports the mail server's own error, and lists the last week of attempts from
  the `email_log` table (migration `0031_email_log.sql`).
- **`php index.php cli mail_test you@example.com`** — the same checks from the
  shell, for when nobody can sign in to reach the admin page. Exits non-zero on
  a failed send.

Read the result like this:

| Symptom | Meaning |
|---|---|
| Test send **fails** with an SMTP error | The site can't hand the message over: wrong credentials, wrong port/crypto pairing, or outbound SMTP blocked by the host. The error names which. |
| Test send **succeeds** but nothing arrives | The relay accepted it and the *recipient* discarded it. This is an authentication problem on the sending domain, not a setting in `.env`. |
| Delivery log is empty during a real signup | The code path never ran — check the CI log, and that `application/logs/` exists and is writable by the web server. |

**The usual cause of "accepted but never delivered"** is a From address the SMTP
account isn't authorised to use: sending as `no-reply@pearland411.com` through a
Gmail or shared-hosting account on some other domain. The receiving server fails
SPF/DMARC and drops the message silently — it appears in neither Inbox nor Spam,
and no bounce comes back, which is precisely why it looks like the site never
sent anything. Fix it at the domain, not in the code:

1. Relay through a provider that has verified `pearland411.com` (Google
   Workspace, Amazon SES, Postmark, Mailgun — or the domain's own cPanel
   mailbox), and set `SMTP_USER` to a mailbox on that domain.
2. Publish an **SPF** record authorising that relay, e.g.
   `v=spf1 include:_spf.google.com ~all`.
3. Turn on **DKIM** in the provider's console and add the DNS record it gives you.
4. Only then add a **DMARC** record (`p=none` first, to collect reports).

Until steps 1–3 are done, the honest short-term fix is to set
`MAIL_FROM_ADDRESS` to the SMTP account's own address, so the From domain and
the authenticated domain match. `/admin/email-diagnostics` flags the mismatch
explicitly.

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

### Turn on reCAPTCHA (spam protection)
The public forms (contact, Help & Support, quote requests, service
requests, registration, password reset, resend verification, newsletter)
carry a hidden trap field always, and Google's "I'm not a robot" box once
keys are set:
1. Sign in at https://www.google.com/recaptcha/admin with the business's
   Google account and create a key: type **reCAPTCHA v2 → "I'm not a
   robot" Checkbox**, domain `pearland411.com` (add a staging domain too
   if there is one).
2. Put the two halves in `.env`: `RECAPTCHA_SITE_KEY=` and
   `RECAPTCHA_SECRET_KEY=`.
3. Open `/contact-us` signed out, click into the form: the checkbox
   appears above the Send button.

Signed-in members never see it. If Google cannot be reached, or the
secret is wrong, posts are let through and the reason is written to
`application/logs/` — so a typo never costs a lead. Both checks have an
off switch at `/admin/setting` → Spam protection.

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
- **Renewal cron** — recurring billing only happens if a daily cron runs
  `php index.php cli renew_subscriptions`. It charges due vaulted cards,
  extends the term on success, and applies a 3-retry / 2-day-grace dunning
  policy (with email) before expiring. Setup and checks below.

## The renewal cron (recurring billing)

**The website never charges a recurring payment by itself.** Every schedule
staff set under *Recurring billing* on `/admin/billing-customer/<id>` is taken
by one nightly job:

```
php index.php cli renew_subscriptions
```

If that cron is not installed, nothing is charged — the admin screen goes on
showing the next charge date and the day simply passes.

### Install it

cPanel ▸ Advanced ▸ **Cron Jobs** ▸ *Add New Cron Job*, filling the five
schedule boxes by hand — Minute `5`, Hour `2`, Day `*`, Month `*`, Weekday `*`
— and this as the **Command**, on one line:

```cron
/usr/local/bin/php /home/pearlan/public_html/index.php cli renew_subscriptions >> /home/pearlan/renew_subscriptions.log 2>&1
```

That is the live account (home `/home/pearlan`, repo root
`/home/pearlan/public_html`), so it can be pasted as it stands. The equivalent
for `crontab -e`, run as the account that owns `public_html` and never root:

```cron
5 2 * * * /usr/local/bin/php /home/pearlan/public_html/index.php cli renew_subscriptions >> /home/pearlan/renew_subscriptions.log 2>&1
```

On any other account, substitute the real home — and substitute it
*everywhere*. The first cron installed on the live server still had the
example's `USERNAME` in its path, and a cron that cannot find `index.php`
looks from `/admin/billing` exactly like a quiet night with nothing due. Over
SSH or cPanel ▸ Terminal, `echo $HOME` and `which php` print the two values
the line needs.

- **Check the PHP version first.** `/usr/local/bin/php` is the server default,
  which is frequently older than the PHP the domain actually runs. cPanel ▸
  **MultiPHP Manager** shows the version assigned to the domain; if it is not
  the default, use that binary instead — `/usr/local/bin/ea-php81
  /home/pearlan/public_html/index.php cli renew_subscriptions …`. A CLI PHP
  older than the site's aborts on a missing extension before it charges
  anything.
- The job `chdir`s to the repo root itself, so an absolute path is enough; no
  `cd` needed.
- Daily is enough, and re-running is safe: a subscription is only picked up
  once its next charge date has arrived, and that date moves forward as soon
  as the charge succeeds.
- Keep the log outside `public_html` so it is not web-readable.
- It refuses to run over HTTP (`Cli` 404s on any web request), so the URL
  cannot be used as a cron target.

### Check that it is working

`/admin/billing` shows a heartbeat line at the top — *“Nightly billing job ran
Sep 2, 2026, 2:05am — 3 due, 3 renewed…”*. Never run, or older than 48 hours,
shows as a warning. It is stamped on every run, including the early return
when Helcim is not configured, so *“ran but not set up”* and *“never ran”* stay
distinguishable. After installing the cron, run the command by hand once and
read its output rather than waiting a night to find out.

**A failed run is on a clock.** Each failure raises `renewal_attempts` and
pushes the retry to tomorrow; the third consecutive failure sets the
subscription to `expired`, which drops the customer's package. So a run that
prints `failed sub N` is worth reading the same day, not next week — the
message after the colon is Helcim's own, and it is also in
`application/logs/`.

A subscription is only charged when **all** of these hold: auto-renew is on
(*“Charge this card automatically…”*), the subscription is `active`, the next
charge date has arrived, a card is vaulted for the owner, and
`HELCIM_API_TOKEN` is set. Rows missing a card or an amount are skipped and
logged, not silently ignored.

### Payments missed while the cron was off

Nothing is lost. The job takes every schedule whose date has **passed**, not
only today's, so the first successful run charges the backlog — one charge per
subscription, then the term rolls forward.

If instead you take a missed charge by hand with **Take a payment**, move
*Next charge on* forward to the following term date and save. A staff payment
is deliberately recorded without touching the schedule, so leaving the date in
the past means the nightly job charges that customer a second time.

## Known follow-ups

- **Migrate off CodeIgniter 3** — CI3 is EOL; see
  `PROJECT_AUDIT_REPORT.md` §12 for the long-term recommendation.

## License

MIT (CodeIgniter framework). All original code in this repository
is provided to the client under the same terms.
