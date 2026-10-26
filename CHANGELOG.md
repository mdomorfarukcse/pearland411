# Changelog

All notable changes to Pearland411 are recorded here, newest first. Format loosely follows
[Keep a Changelog](https://keepachangelog.com/). When you make a change, add an entry with the
migrations and key files touched so the next session doesn't have to re-read the codebase.

---

## 2026-10-26 — Website Update Checklist (before the 100-business acquisition campaign)

Worked from "PEARLAND 411 — WEBSITE UPDATE CHECKLIST (Draft)". Section 2 (new pricing) is struck
through in the document and was left alone: prices live in `plans` and are set at `/admin/pricing`.
Everything was reproduced or traced on a local copy (MariaDB 10.11, PHP 8.3, schema rebuilt from the
code + every migration, Chromium) before it was changed, then re-tested end to end. **Run migration
0037** (`database/migrations/0037_claim_onboarding_tracking.sql`, also appended to
`database/migrations_combined_phpmyadmin.sql`). The code works before it is run (checked against a
copy without it), but the Claim button only appears once it has.

### Fixed — §1 Claim This Business: the button never showed

- **Every listing looked claimed.** The site decided "claimed" from `listings.user_id`, but every
  listing has an owner — the CSV import and the admin form require one, and the imported directory
  sits under a few data-entry logins. So no listing ever showed the Claim button, the "Unclaimed"
  tag, or the "Is this your business?" card, and the homepage called every business "Verified".
  New `listings.is_claimed` (0037), read through `listing_is_claimed()`. Backfill: claimed when the
  owner had a claim approved, staff billing has a subscription for it, or the owner holds 3 live
  listings or fewer; everything else is claimable. Admins can flip a listing (editor checkbox, a
  Claimed/Claimable button on Business Listings) or a whole data-entry account (`/admin/users` →
  *Make listings claimable*). A member's own new listing is claimed on creation.
- The button is now **"Claim This Business"** in the listing header (with an icon, burgundy on the
  navy hero), in the empty-description panel and the sidebar card, and a *"Is this your business?
  Claim it"* line on unclaimed directory cards. The owner sees *Edit your listing* instead.
- **Claim form** rebuilt on the shared kit: business name, owner/contact name, business email, phone,
  role, verification method (phone / domain email / document / website note / other), optional proof
  document (photo or PDF), notes, and an "I'm authorised" box. Values are kept on a refused post.
  A guest is sent to sign in *with a message saying why*. The 3-per-hour limit counted refused posts,
  so two typos locked an owner out for an hour — now 10 attempts.
- **Admin review** (`/admin/claimed-listings`): tabs (needs action / pending / waiting on claimant /
  approved / rejected), each claim with role, verification method, document, the claimant's account
  and who holds the listing now. **Approve / Reject with a reason / Ask for more information** — all
  POST (they were GET links). Approving a listing another owner already has needs "transfer anyway".
- **More information** (`claim_status` 3): the claimant is emailed the question and answers on
  `/user/my-claims` with a note and/or a document, which puts the claim back in the queue and alerts
  the office. Rejection reasons and questions show on My claims.
- Claim emails (received, approved with a link straight to the editor, rejected with the reason,
  more information) go through the new `Notifications` library, to the **account** address.

### Fixed — §4 Listing editing: the address of the page changed on every save

- Member and admin edits rebuilt the slug from the name on **every** save — `central-texas-bbq`
  became `Central-Texas-BBQ`, breaking the links in the claim and welcome emails — and a name another
  listing already used failed the save on the unique index ("Failed to update listing"). The slug now
  stays unless the business is renamed, and a rename gets a unique one.
- The required-fields error was set and never printed; it is now. Saving returns to the form with
  *Saved — your changes are live* and a **View your live listing** link (it used to drop the owner on
  My Listings). Description, services and SEO fields are escaped (a quote broke the input; a
  `</textarea>` broke the form). Debug flash/log lines removed.
- **Opening hours and the business's own social links** (Facebook, Instagram, X, YouTube, LinkedIn)
  on the member and admin forms and the listing page. The page used to show the *owner account's*
  profile links — a data-entry login's Facebook page on every business it held. Hours and social
  links are plan features (`plans.can_hours` / `can_social_links`, seeded from `can_photos`, editable
  at `/admin/pricing`, listed on the pricing cards); locked fields show an upgrade note and are
  ignored server-side.

### Fixed — §3 Checkout & payment, §4 onboarding, §11 emails

- **No email followed an online payment**, while the success page said "We've emailed a receipt".
  Every checkout path (single plan, cart, upgrade) now sends `User::afterPurchase()`: the customer's
  **receipt** (lines, terms, renews or one-time, next charge date), a **welcome / next-steps** email
  (finish the listing — or find/claim/add it — and send artwork for banner/video/homepage items),
  and an **office order alert** saying what still needs doing by hand (homepage dates, banner booking).
- **Success page** rebuilt: the real items, the total, whether and when the card is charged again
  (it showed "Next billing" a month out for one-time purchases), who the receipt went to (or that it
  could not be sent), and a Next steps panel linking to the listing editor. The 30-second `confirm()`
  pop-up is gone.
- **"Cancel auto-renew" cancelled the wrong subscription.** `Usermodel::getUserSubscription()`
  selected `s.*, p.*`, so the plan's `id` overwrote the subscription's, and cancel, upgrade, scheduled
  downgrade and usage tracking all wrote to the subscription whose id equalled the plan id. Fixed in
  the query. Cancelling now also emails a confirmation.
- **Helcim webhooks were refused** with a 403: `helcim-webhook` was not exempt from CSRF, which a
  server-to-server post can never pass. Exempted; the signature check still guards it (unsigned →
  401).
- **Listing published**: switching a claimed listing from inactive to active in admin emails the
  owner.
- **Dashboard onboarding card** ("Finish your listing"): business info, website, description, logo,
  photos, hours, social links, services, with progress and links into the editor; items the plan
  lacks show as upgrades.

### Fixed — §5 Lead form

- Leads for an **unclaimed** listing with no business email went to the data-entry login that holds
  it, where nobody at the business would see them. They go to the office inbox now. A lead to an
  unclaimed business's own email carries a line inviting it to claim the listing.
- Deals on a listing page come only from a claimed listing's owner.

### Added — §7 Homepage & featured, §10 analytics

- **`/admin/homepage-placements`**: Business Hotspot and Business of the Week (sold on
  `/pricing-plan`, stored as dates since 0018 — but nothing in admin could set them, so a placement
  bought online never appeared) and Featured: find a business, add with an end date, extend, renew,
  remove; ended ones listed for 30 days. The dates are also on the admin listing editor. The Featured
  row and Hotspot rail **rotate** when more are live than fit (they always showed the same ones).
- **Analytics**: `activity_daily` counters (0037) via `libraries/Activity.php` — listing views (once
  per visitor per day, bots skipped), website / phone / email / map / WhatsApp clicks (`track.js` →
  `POST /track`), coupon views and code copies / link opens, banner impressions and clicks
  (`/ad-click/<id>` counts and redirects). **`/user/analytics`** (views + leads for everyone; clicks,
  deals and banners with the plan's Analytics feature) and **`/admin/analytics`** (site totals,
  busiest businesses, banner click rates, coupons). Sidebar links in both.

### Verified

Local copy: 104 end-to-end checks through the real controllers (claim 33, owner editing 15, leads 8,
checkout/emails/cancel 19 incl. the upgrade receipt, homepage placements 9, analytics 15, admin editor
and toggles 7); the same pages and the claim → approve → edit path against a database **without**
0037 (20); 0037 and the combined bundle run twice on a fresh and on a populated schema, backfill as
described, re-runs keep an admin's choice; Chromium at 390px — no horizontal overflow on 12 customer
pages, the empty claim form stopped in the browser, the quote dialog fits (the theme's floated
`.form-group` pushed two claim fields off-screen on a phone; fixed).

### Needs the operator / client

- Run **0037**. Then check `/admin/all-listings` → Claimed column: the backfill treats an account with
  3 listings or fewer as the business itself. Use *Make listings claimable* on `/admin/users` for any
  data-entry account it got wrong.
- Decide which plans include hours, social links and Analytics (`/admin/pricing`). See the open
  questions sent with this change for the rest.

Key files: `database/migrations/0037_claim_onboarding_tracking.sql`, `application/libraries/{Activity,Notifications}.php` (new),
`application/controllers/{User,Admin}.php`, `application/models/{Usermodel,Adminmodel}.php`, `application/helpers/view_helper.php`,
`application/views/{claim_listing,listing_details,all-listing,index,edit_listings,checkout_success_view,user_claims,user_dashboard,user_analytics,user_left_sidebar,coupon_details,pricing_plan,footer}.php`,
`application/views/partials/ad_slot.php`, `application/views/admin/{claim_listing,homepage_placements,analytics,edit_listings,all_listings,users,pricing,header}.php`,
`assets/js/track.js` (new), `assets/css/{content,directory}.css`, `application/config/{routes,config}.php`.

---

## 2026-10-26 — reCAPTCHA fitted to narrow forms (directory sidebar card)

No migration.

### Fixed

- **The "What service do you need?" card on `/all-listing` showed a tall, square checkbox** that
  didn't line up with anything. The card is 282px wide, under the widget's fixed 304px, so
  `assets/js/recaptcha.js` switched to Google's 164×144 "compact" widget. Narrow forms now get the
  normal widget **scaled to the form's width** (`fit()`: `transform: scale`, with the saved height
  given back), so it is exactly as wide as the fields and button. It refits on resize or rotation.
  The compact widget is kept only for a form under ~213px, too narrow to scale legibly. The
  Help & Support slide-out on a phone gets the same treatment.
- **That card sends over XHR and never reset the checkbox**, so a second enquiry from the same page
  would have reused a spent answer and been refused. It now resets after each send, as the quote
  dialog and newsletter already did. The event, video and news enquiry forms got the same line;
  they are members-only today, so no checkbox shows there yet.

### Verified

Chromium, Google's script stubbed: in the directory card at 1280px the widget is 282px wide, exactly
the fields' width and left edge, 72px tall, with 14px above the button; it refits at 1024px; at 390px
the card is wide enough for the full widget with no overflow; the phone slide-out's widget is
scaled to its 293px fields; a 200px form falls back to compact. The directory card's XHR is blocked
while unticked, then sends, thanks and resets. The earlier 33 browser checks still pass.

Key files: `assets/js/recaptcha.js`, `application/views/all-listing.php`, `event_details.php`,
`video_details.php`, `news_details.php`.

---

## 2026-10-26 — Follow-up: admin settings sidebar; reCAPTCHA shown without a click

Two problems reported after the spam-protection change below. No migration.

### Fixed

- **`/admin/setting` lost its header and sidebar** and returned HTTP 500. The new Spam protection
  card loaded the reCAPTCHA library *inside the view* and called `$this->recaptcha->site_key()`. A
  view's `$this` is the `CI_Loader`, which copies the controller's properties only when the view
  starts, so the library wasn't there and the call was a fatal error. CodeIgniter was still holding
  the already-rendered admin header in its output buffer, so only the partial settings form
  reached the browser. Reproduced on the previous commit (500, no sidebar). The controller now
  looks it up (`Recaptcha::has_keys()`, new) and passes `recaptcha_has_keys` to the view. The rule
  is in `docs/PROJECT_OVERVIEW.md`.
- **The "I'm not a robot" box appeared only after a click** into the form or on Send. That was the
  lazy load, and it read as a missing checkbox. `assets/js/recaptcha.js` now draws it as soon as
  its **form** is on screen (`IntersectionObserver`, 200px ahead; the form rather than the box,
  since the box sits at the foot of the form, below the fold). The contact form, registration and
  a listing's quote card show it on page load; the homepage newsletter shows it when scrolled to.
  The Help & Support slide-out is parked off the right edge until opened, so it still doesn't
  pull Google's script into every page. The box keeps the checkbox's height (78px) and margin from
  the start (`spam_helper.php`), so the Send button doesn't jump when it arrives. Clicking into a form
  is still a fallback trigger, and a browser without `IntersectionObserver` draws every box on load.

### Verified

Local copy in production mode (HTTPS faked for the Secure cookies): `/admin/setting` 200 with sidebar,
admin footer and the card; the "Not active yet" note only without keys; the two switches save and
reload (9 checks, with and without keys). Chromium (Google's script stubbed): checkbox drawn with no
interaction on `/contact-us`, `/register` and the listing quote card; slide-out not drawn until
opened, then drawn with Google fetched once per page; homepage fetches nothing until the newsletter
is scrolled to; Send button position unchanged when the checkbox draws (API held back 1.5s);
plus the earlier dialog, XHR, blocking and 390px checks (33 in all). The form-post suites (with and
without keys) and the `verify()` unit test were rerun and still pass.

Key files: `application/views/admin/admin_setting.php`, `application/controllers/Admin.php`,
`application/libraries/Recaptcha.php`, `assets/js/recaptcha.js`, `application/helpers/spam_helper.php`,
`docs/PROJECT_OVERVIEW.md`.

---

## 2026-10-26 — Contact-form spam: reCAPTCHA, honeypot and a name filter on every public form

The client was getting **700+ spam emails a day**: "[Pearland411] Contact-Us message for … from
Новый перевод 52012 руб. …" (Russian: "new transfer of 52,012 rubles", a payment scam). No
migration (the two new switches are `site_settings` keys). **To turn the captcha on, add
`RECAPTCHA_SITE_KEY` / `RECAPTCHA_SECRET_KEY` to `.env`**: a v2 "I'm not a robot" checkbox key from
https://www.google.com/recaptcha/admin; README → "Turn on reCAPTCHA". Until then the honeypot and
the name filter already apply.

### Security — what was wrong

- `/contact-submit` (the contact page, the Help & Support slide-out on **every** page, the pricing
  callback panel, Advertise with us) had CSRF, which a bot passes by loading the page first, and
  6 posts per IP per hour, which a botnet passes by rotating IPs. Nothing else.
- **The site was relaying the spam.** Every submission also emailed "We received your message"
  **to the address the visitor typed, quoting their message**. A bot typed its victim's address and
  its scam text, and pearland411.com delivered it, hurting the domain's sending reputation along
  with the client's inbox. The registration verification email did the same with the typed name
  ("Welcome to Pearland411, {name}.").

### Added

- **One spam gate for every anonymous form that saves a row or sends an email**:
  `User::spamRefusal()`, called after each handler's rate limit, with the fields printed by
  `spam_form_fields()` (`application/helpers/spam_helper.php`, autoloaded):
  1. **Honeypot**: the off-screen `website_url` field registration already had, now on every
     form. A filled-in one is answered **as if it worked**: the usual thanks, but nothing saved
     and nothing sent, so the bot learns nothing.
  2. **Name filter**: a name containing a web address (`http`, `www.`, `bit.ly`, `t.me`, …) or
     Cyrillic / Chinese / Japanese / Korean characters is refused with a plain message. That is
     where this spam puts its payload. Accented Latin names (José Núñez) pass. Switch:
     `spam_name_filter_enabled`.
  3. **Google reCAPTCHA v2** (`application/libraries/Recaptcha.php`): required when both keys are
     set and the visitor is signed out (a member was captcha-checked at registration and verified
     their email). It **fails open**: if Google can't be reached, or the secret is wrong, the post
     goes through and the reason goes to `application/logs/`, so an outage or a typo never costs
     a lead. Switch: `recaptcha_enabled`.
- Forms covered: contact (`contact-us`, footer slide-out, `partials/sales_help`, `post_your_ads`,
  the slide-out copies in `all-listing2` / `all-products`); every "Get a quote" / enquiry form
  (`listing_details`, `all-listing`, event / video / news / blog / product / classifieds / rental /
  house details, `all-products`); `/request-service` (`service_details`, `request_quote`);
  `/register` (and the cart's sign-up form); `/forgot-password`; `/resend-verification`; the
  homepage newsletter. Not covered, deliberately: login (sends no mail; already rate limited),
  and reviews, claims and deals, which all require signing in.
- `assets/js/recaptcha.js`: Google's script loads **only once someone clicks into a protected
  form**, so the slide-out on every page adds no download until used. The compact widget is used
  where the form is narrower than 304px (the slide-out on a phone). An unticked submit is stopped
  before it posts ("Please tick the 'I'm not a robot' box"), after form-validate.js has reported any
  bad fields. The quote dialog redraws the widget when the form moves in and out of the dialog,
  and both XHR forms untick it after each send.
- `/admin/setting` → **Spam protection**: the two switches, and a note when the keys are missing.

### Changed

- The contact auto-reply no longer quotes the message. It says it was received and who it went to.
  `contact_submit` also caps the message at 5,000 characters, as the enquiry form already did.
- Registration's own honeypot check (`Usermodel::registerUser`) moved into the shared gate.

### Fixed

- **The homepage newsletter signup never worked.** The form had no `method`, so the CSRF hook gave
  it no token, and the homepage doesn't load `csrf.js`. Every signup got "The action you have
  requested is not allowed" (reproduced on the code before this change). It now posts the whole
  form (`method="post"`, `new FormData(form)`).

### Verified

Local copy (MariaDB 10.11, PHP 8.3, Chromium). Google is unreachable from the test box, so its
script and its verify endpoint were replaced by local stand-ins. **No keys**: 32 end-to-end posts
through the real controllers. Each form works as before; the honeypot drops silently (no row, no
mail); the scam names from the screenshot, `http://…` and `bit.ly` are refused, while O'Neil,
José Núñez and Dr. J. R. Smith pass; the filter switch works; the auto-reply carries no visitor
text. **With Google's test keys**: 43 checks. Every page with a form prints the box, includes the
script once and does not fetch Google up front; a post with no token is refused on every handler
with nothing saved or mailed; the admin switch turns it off; Google unreachable lets the post
through and logs it. `verify()` was unit-tested against a stub for yes / no / wrong secret / non-JSON
/ connection refused. **In Chromium**: lazy load, blocked unticked submit and the note, the
theme's floated `.form-group` (the box needed `clear: both`), the quote dialog's move and XHR send,
the newsletter's XHR, and no horizontal overflow at 390px, with the compact widget in the phone slide-out.

### Key files

`application/libraries/Recaptcha.php` (new), `application/helpers/spam_helper.php` (new),
`assets/js/recaptcha.js` (new), `application/controllers/User.php` (`spamRefusal()`,
`contact_submit`, `add_enquiry`, `request_service`, `register`, `forgot_password`,
`resend_verification`, `subscribe_newsletter`), `application/models/Usermodel.php`,
`application/controllers/Admin.php` + `views/admin/admin_setting.php`, `application/config/autoload.php`,
the form views listed above, `assets/css/home-sections.css`, `.env.example`, `README.md`,
`docs/PROJECT_OVERVIEW.md`.

---

## 2026-10-19 — PEARLAND411 PROJECT TRACKER 2: all 17 rows

Worked from the "Website error report sheet" PDF (Angel, Jana, anish, Monette). Every
row was reproduced or traced against a local copy (MariaDB 10.11, PHP 8.3, a schema
rebuilt from the code, Chromium driving the admin screens) before it was fixed, and
re-tested afterwards. **Run migration 0036** (`database/migrations/0036_tracker_round3.sql`,
also appended to `database/migrations_combined_phpmyadmin.sql`; idempotent, run twice
on a fresh and on a populated schema).

### Fixed — the "This page isn't working" errors (Monette: news, videos)

- **Adding news or a video was an HTTP 500** whenever "Featured" was left unticked.
  The checkbox posts nothing, the controller wrote that NULL into `featured_news` /
  `featured_video`, the live columns are NOT NULL, and on PHP 8.1+ mysqli throws
  instead of returning FALSE — so the error never reached the "could not save"
  branch. Reproduced locally (`Column 'featured_news' cannot be null`), then fixed
  twice over: the flags are written as 0/1, and the writes go through the new
  **`db_safe_insert()` / `db_safe_update()`** (`application/helpers/db_safe_helper.php`,
  autoloaded), which fit a row to the live table — unknown columns dropped, NULL or ''
  for a NOT NULL column turned into its default / 0 / '' / now — and catch the
  exception, so a refused row is a message, never a 500. Admin and member add/edit
  for news and video use it; `Usermodel::addEnquiry()` (the first copy of the rule)
  now calls it. The same unticked-checkbox NULL is fixed on listing featured/spotlight,
  classifieds, marketplace and event `isenquiry` (which stored the string "on").
- The member news edit deleted the old picture before the upload ran (the bug fixed
  for videos last round) — fixed the same way.

### Fixed — pricing (Angel: "Still can't edit the pricing"; outreach prices differ)

- **The plan editor's Save button could not be clicked.** A stray `</div>` in
  `views/admin/pricing.php` closed `.modal-content` before the footer, putting
  "Save Plan" under `.modal-dialog`, which Bootstrap gives `pointer-events: none`.
  The modal opened and filled in; no click ever reached Save. Verified in Chromium
  before and after: price and base term now save together.
- **Prices typed into nine pages.** "from $20 a month" was hard-coded on the homepage,
  About, Events, Videos, News, Directory, Classifieds, Categories and Contact — two of
  them wrong already (no top or category banner costs $20). They now read the live
  lowest monthly price from `plans` (`ad_price_from()` in `view_helper.php`). The
  generated house-ad artwork no longer bakes a price in (`scripts/generate_brand_art.py`,
  re-run; 39 SVGs changed text only).
- `admin-custom.js` fetched a non-existent `city_search.html` on every admin page and
  threw; it now runs only where its search box exists.

### Fixed / added — pricing page copy (anish: add-ons one-time or recurring; skyscraper vs right banner)

- Every card now says what happens to the **card**: one-time ("your card is charged
  once … it never renews") or recurring only with auto-renew ("charged every month
  until you cancel, and we email you before each charge"). A line under the hero says
  the same for the whole catalogue and links the refund policy. Checkout's auto-renew
  box says the same.
- **Customers are now told before an automatic renewal charge**, and get a receipt
  after it: `Cli::renew_subscriptions` emails each auto-renewing customer
  `renewal_reminder_days` (default 3, `/admin/setting`) before the charge — once per
  charge, via `subscriptions.renewal_reminded_for` (0036) — and a receipt on success.
  Before, the only renewal email a customer ever got was the failure notice.
- Banner and video cards state the zone's **real size** from `ads_prices.size`
  ("295 × 600 px tall skyscraper", "300 × 250 px box banner"), not the free-text
  description that used to say 900x100. The prices themselves are the client's call —
  now editable at `/admin/pricing-plan` (above).
- Listing-tier cards list what the listing may carry (logo + N photos, N videos, map,
  services) from the same flags the forms enforce.

### Added — category banners and header banner in Business Listings (Angel)

- **Header banner**: the admin add and edit listing forms upload `listings.cover_image`,
  the picture at the top of `/listing-details` (no admin form could set it).
- **Category banners for this business**: a section at the foot of
  `/admin/edit-listing/<slug>` (and a "Banners" link on every row of `/admin/all_listings`)
  books a Category Specific Banner — Category Header Banner, middle, bottom, right
  #1/#2 — for that business: position, category (its own by default), artwork, link
  (its page by default), dates; priced from the package; listed with Live/Scheduled/Ended
  and a Remove button. `Admin::listing_banner_add` / `listing_banner_remove` (POST,
  super-admin, like the rest of ad sales); `ads.listing_id` (0036) records the business.
- **`primeAdSlots()` leaked category bookings onto every page.** The cached ad lookup
  never filtered `category_id`, so a banner sold for one category would also have run
  on the homepage and every primed page. Untargeted bookings only now, as
  `getAdForSlot()` always did. Verified: a Medical & Dental header banner shows on that
  category only.
- `/admin/create-ads` and `ads_edit` stored dates with 12-hour `h`, so every booking
  started and ended at noon; whole days now. `getAllAds()` LEFT-joins the advertiser so a
  booking for an unclaimed business still lists.

### Fixed — Get a quote (Jana)

- The button scrolled to a card in the sidebar — far down the page on a phone, which
  read as "redirects to the quote form". It now **opens the quote form for that business
  in a dialog** (the sidebar card itself, moved in and back — still one form), showing
  the business's logo, name and category, and sends it over XHR with an inline
  confirmation ("sent to <business> …"); the no-JS post still works and reopens the
  dialog on return.
- **`customCode.js` replaced `window.fetch` on every public page** with a theme stub
  (`function fetch(){…}` posting to `user/user_events_list`), so every `fetch()` returned
  undefined — including the `/checkout` payment button, the review-moderation switch
  and this form. Removed from `customCode.js` and `customCode2.js`; both script tags
  (and `admin-custom.js`) are now cache-busted by mtime.

### Fixed — Post a deal (Jana)

- `/user/coupons` (the URL in the tracker and in old links) now routes to the member's
  own deals screen; it showed the public deals page. Members without a published listing
  get a guide: what is missing (nothing yet / a listing not published / a claim under
  review) and buttons to add or claim a business.

### Fixed — Add listing (anish: no confirmation, no email; returned to Step 1)

- The wizard's slug had no uniqueness pass, so a business with an existing name failed
  the insert where `listing_slug` is unique (`Usermodel::uniqueListingSlug()` now); a
  failure redirected to an empty Step 1 with a flash nobody printed, while the page
  printed "DEBUG: POST data received" and "SUCCESS: Form submission detected"; a member
  with no plan was refused only after all three steps, with a message My Listings never
  printed. Now: a refused submission re-renders the wizard with the reason and every
  value kept; a member with no plan is started on the free Basic plan (shown up front);
  success lands on **`/user/listing-submitted/<id>`** — status, link, plan, what next —
  and **emails a confirmation** to the member and a heads-up to the office
  (`form_recipient_email()`); the insert goes through `db_safe_insert()`.

### Added — plan-based media limits (Angel: Basic gets no logo/photos/video; per-plan limits)

- The add wizard and the member edit form enforce the plan: Basic (can_photos and
  allow_video off) gets no logo, gallery or video — the fields are replaced by an
  upgrade note, and the server refuses them too; paid plans are capped by `photo_limit`
  and the new **`plans.listing_video_limit`** (0036; default 1, special package
  unlimited; editable at `/admin/pricing` → "Listing Video Limit"). Anything over the
  limit is now reported ("your plan includes 1 video; only the first link was kept")
  instead of being dropped silently. Map, services and SEO fields are gated at
  creation as they already were on edit. One parser for video links
  (`listing_video_urls()`: commas or new lines — the listing page split on commas only).

### Added — My Claims (anish)

- **`/user/my-claims`** — every business the member asked to claim, with Pending
  review / Approved / Not approved and what happens next (manage the listing and its
  enquiries; or send a new claim with more proof). A **My listings | My claims** tab strip
  heads both screens (`views/partials/listing_tabs.php`); sidebar link added.
  A rejected claim no longer blocks a new one (it used to — and called it "pending").

### Fixed — claimed listings' enquiries go to the owner (Angel)

- **The member Leads page showed nothing without a paid plan** ("Premium subscription
  required to view enquiries"), so the owner of a claimed (free) listing never saw its
  quote requests — they reached only the admin team. Leads are shown to every owner now.
- A listing's enquiries follow whoever owns it **now**
  (`Usermodel::applyEnquiryReceiverScope()`); approving a claim also re-addresses them
  (`moveListingEnquiries()`) and says so in the approval email; 0036 back-fills claims
  approved before today.
- The member dashboard shows the latest enquiries; the "Last week report" panel, which
  was static HTML ("00" leads, "02" views for everyone), shows real figures.
- **My Listings showed nothing without a paid plan** and hid owned listings past the
  plan limit; it now lists every listing the member owns, with Live / Not published yet.

### Added — refund & cancellation policy (anish: Terms, FAQ)

- `views/partials/refund_policy.php`, section 8 of `/terms-of-use` (anchor `#refunds`)
  and FAQ 11–12 (`/faq#refunds` opens it): what renews and what does not, **self-service
  cancellation** (My Account → Cancel auto-renew; plan runs to the end of the paid term),
  refunds by **contacting support** (charged in error / placement could not run), the
  request window and support contacts. `refund_request_days` (default 30) and optional
  `refund_policy_extra` wording are at `/admin/setting` — **the client should confirm the
  refund terms**. FAQ 4 no longer promises a 24–48 hour approval for listings that go
  live immediately.

### Needs the operator / client

- Run **0036** on live (and 0033–0035 if not yet run — the add-on copy and catalogue
  corrections depend on 0033).
- Set the prices quoted on calls at `/admin/pricing-plan` (now saves); decide the
  skyscraper vs right-banner prices (tracker: "there should be a price change").
- Confirm the refund policy wording and window at `/admin/setting`.
- Outgoing mail must work for the new confirmation, reminder and receipt emails
  (`/admin/email-diagnostics`).

Key files: `application/helpers/db_safe_helper.php` (new), `application/controllers/{User,Admin,Cli}.php`,
`application/models/{Usermodel,Adminmodel}.php`, `application/helpers/view_helper.php`,
`application/views/{add_new_listing_start,listing_submitted,user_claims,user_listing,user_enquiry,user_dashboard,user_coupons,listing_details,pricing_plan,terms_of_use,faq}.php`,
`application/views/partials/{listing_tabs,refund_policy}.php`, `application/views/admin/{pricing,edit_listings,add_new_listings_start,all_listings,admin_setting}.php`,
`assets/js/customCode{,2}.js`, `assets/css/directory.css`, `application/config/{routes,autoload}.php`,
`database/migrations/0036_tracker_round3.sql`.

---

## 2026-10-10 — Tracker remaining work: 26 open rows, the contradictory six, five new bugs

Worked from `PEARLAND411_REMAINING_WORK.md` (the 105-row tracker audit). Every row
that needed code is addressed below; rows that need an operator, the live database
or the client's decision are listed at the end. **Run migrations 0033–0035** (also
appended to `database/migrations_combined_phpmyadmin.sql`). All three were run twice
against MariaDB 10.11 (idempotent), and the model, cart, enquiry, review, coupon,
pricing and moderation paths were exercised end-to-end against a local database.

### Security / privacy

- **`/user/enquiries` showed every business's leads to every member** (#6). It used
  `getAllEnquiries()` ("showing all for now"). Now `Usermodel::getEnquiriesForReceiver()`
  — leads addressed to the member, or about a listing they own (legacy rows with no
  receiver). Pagination pointed at `user/user-enquiries`, a different method; fixed.
- **Any member could delete any enquiry by id, by GET** (N4). `delete_enquiry` is POST
  only (CSRF via the existing hook) and owner-scoped through `getEnquiryForReceiver()`.
- **`add_enquiry` trusted `receiving_user_id` and `enquiry_sender_id` from the form**
  (N3). The sender is the session (or none); the receiver is the listing's owner, looked
  up from the URL slug — the house, rental and classifieds forms also posted their id in
  the wrong field (`news_id`, `product_id`), now ignored. Hidden id fields removed.
- **The member dashboard listed other members' events and blogs** (#5).
  `dashboardInfo()` had no WHERE; now `getAllEvent()/getAllBlogs()` for the member, and
  "Used" is a COUNT (`Usermodel::countOwnedContent()`), not the length of a preview.
- **Pricing AJAX endpoints were open to editors** — `get/update/add/delete_pricing_duration`
  and `get_pricing_plan` are now in `$super_admin_only`; the Pricing/Subscriptions/Billing
  menu links are hidden from editors.
- **Member moderation** (B2). `/admin/users` gains Suspend / Restore (writes
  `user.banned`, migration 0035) and Delete for accounts that own nothing at all
  (`Adminmodel::userOwnership()` checks 13 tables). `User::__construct` signs a suspended
  member out on their next request; login already refused them. Owner pickers label a
  suspended account. The 14 owner dropdowns that printed a bare name now use
  `user_option_label()`.
- Review debug `error_log()` calls that wrote reviewers' emails and phones to the server
  log are gone.

### Fixed — checkout and pricing

- **Checkout term picker never re-priced** (#2). `footer.php` wrapped every select in
  Select2, which fires only jQuery events; the native listener never ran, so the widget
  said "3 months" while the cart, total, end date and charge stayed on 1 month. The
  select carries `no-select2` (footer honours it, like admin), the handler binds through
  jQuery, and a failed POST puts the picker back on the term the cart holds.
- **Admin plan editor** (#4). The Edit-Durations button had `id="manage_durations_btn mt-3"`
  so nothing bound to it; the plan modal had no price/currency/term inputs while
  `edit_pricing_plan` wrote `price => NULL` — any save made the plan $0, "Get started
  free", and claimable through `/start-free`. Now: price, currency, term (days), slug and
  sort order in the modal, validated server-side (`Admin::pricingPlanPost`); new plans
  get a unique slug and a base term; `plans.price` and the base-term `plan_durations` row
  are kept in step both ways (`syncPlanBaseTerm` / `syncPlanPriceFromTerm`);
  Deactivate/Activate (POST `toggle_pricing_plan`) replaces the commented-out delete, and
  inactive plans stay visible in admin; a one-time plan no longer prints "/ month";
  `$('form')` selectors that rewrote every form on the page now target `#planForm`.
- **`/start-free` could hand out a plan whose price was NULL** — refused unless the price
  is set to 0, it is a business-listing tier, and no term on it costs money.
- **One-time vs recurring** (#25). Only a business-listing tier becomes a subscription,
  but `special` was flagged recurring, so checkout offered "Renew automatically" for an
  item that could never renew. Every card on `/pricing-plan` and every checkout line now
  says which it is (`plan_term_copy()`), and the auto-renew box says it applies to the
  listing only.
- **Catalog** (#26, migration 0033): right-hand banners described as 900x100 → 300x250;
  the zone-less "Skyscraper" taken off sale; prices untouched (client decision).
- **Category-specific banners** (#19). 0033 seeds a sellable Category Header Banner plan
  for the `top-banner-category` zone; checkout asks which category a category banner or
  video is for (`/cart-set-category`), refuses payment without one, and stores it in
  `transactions.target_category_id` (shown in admin billing history). The category
  banner upload no longer deletes the old banner before the new one succeeds, and is
  checked for shape (≥1200px wide, ≥2.5:1). Shortcuts to category banners on the admin
  listings screens.
- **`/pricing-plan` single-card sections stretched across 1180px** (#24): `auto-fill`.

### Fixed — content, forms and uploads

- **"Post a deal" could not be submitted without a picture** (#17): the stale
  `coupon_photo: required` rule in `custom_validation.js` is gone; a refused picture is
  now reported instead of dropped; a deal with no picture writes `''` so a NOT NULL
  column accepts it; and the owner's live deals appear on their listing (the post-a-deal
  screen always promised they would).
- **Admin coupon screen** — `/admin/coupons`: list, search, switch off, delete. The old
  sidebar links went to `admin-coupons.html`.
- **Get a quote** (#16): the redirect lands on `#get-a-quote`; the insert drops columns
  the live table lacks and turns NULL into 0/'' for NOT NULL columns
  (`Usermodel::addEnquiry`); the business is emailed with the visitor as Reply-To; the
  classifieds/house/rental quote forms are open to visitors like the listing page.
- **Reviews** (#18): a member's second review of a business replaces the first (migration
  0034 clears existing copies into a backup table and adds a unique key); a failed insert
  is no longer reported as success; `updateListingRating()` only writes cache columns
  that exist; the button disables once the form is really submitted.
- **Videos** (#8): admin `edit_video` deleted the old still before the upload, redirected
  to `admin/edit-video<slug>` (no slash) and fataled on a bad slug; member add/edit
  uploaded to `images/videos`, which does not exist, so every still was lost — and edit
  then unlinked the old one. All fixed, with the reason shown when an upload is refused.
- **Video categories** (#23): page N used N as a row offset (page 2 started at row 2);
  rename checks uniqueness and only regenerates the slug when the name changes; delete
  uncategorises the category's videos instead of orphaning them.
- **Bylines** (#20, migration 0035): Byline box on admin news/blog forms, printed on the
  article and in search; the author account is required server-side; homepage deals and
  directory search no longer print a data-entry login as a business's "owner".
- **Uploads** (#7, N5, B4): a 40-megapixel hard cap refuses an image before decoding it,
  even where `memory_limit` is unlimited; `uploadMultipleAndResize` no longer squashes
  photos to a fixed size through Image_lib, and reports refused files; a refused logo no
  longer overwrites the stored one with NULL (admin and owner edit); partial gallery
  failures are reported instead of "updated successfully"; WebP accepted (`mimes.php`);
  `safe_upload_image()` records why it refused.
- **Contact form** (N2): the enquiry is saved through `addEnquiry()`, both mails go
  through `Mailer::render()` with the sender as Reply-To and `log_type` `contact`, and the
  visitor is told when the message was neither saved nor sent. `/post-your-ads` posts as
  a package lead and returns to its own page.
- **Add to Calendar** (B3) is timed when `event_time` can be read ("4 - 9 pm", "7:30
  p.m.") via `event_calendar_url()`, all-day otherwise.
- **Blog links** (B5): header Explore, homepage, footer and section strip, shown only
  while the Blogs service is on.
- **Popular Categories on `/search`** (#13–#15) listed marketplace product categories
  ("Laptop", "Simulator") as `/all_listing/` links; now busiest listing categories with
  counts. The header-category fallback had the same bug.
- Small: classifieds CTAs (#9); homepage "Create an Account" → `/register` (#10);
  `/post-your-ads` "checkout coming soon" → Buy online (#11), copy says 3/6/12/24-month
  terms; `resend_verification` added to `$public_actions` (N1); social `sameAs` URLs
  through `external_url()`; dead `views/user_footer.php` deleted (#1); dashboard greets
  the member by name (was "Welcom back, User Test"); 0 plan limits show ∞ on the account
  page; admin enquiries linked in the menu; `merge_listing_category` reports failures as
  errors; "Merge any two categories" picker (#12).

### Follow-up (2026-10-12) — checked against the main tracker spreadsheet

- **Member dashboard limits are hidden** (tracker row R192, Angel: "internal limits …
  should not be displayed on the user-facing dashboard"). The tiles now show only how
  many listings, events and blog posts the member has, linking to each list; the add
  screens still enforce the plan limits.
- **PHP 8.1+ safety.** This is CodeIgniter 3.1.11, which predates PHP 8.1 making mysqli
  throw on a failed query, so a failure surfaces as HTTP 500 whatever `db_debug` says.
  The video-category delete now reads the column's nullability before writing instead
  of trying NULL and falling back on the error; `addEnquiry()`, its column probe and the
  review save catch the exception and take their normal failure path. Verified in
  production mode on PHP 8.3.
- **Add to Calendar** no longer guesses morning for a time with no am/pm ("7:00",
  "6:30 - 9:00" → all-day entry); a time that does carry am/pm wins ("Doors 6:30, show
  7:30pm" → 7:30 pm).
- `assets/js/custom_validation.js` is now versioned by mtime in `footer.php`, so
  returning visitors stop running the cached copy with the old "Coupon Photo is
  Required" rule. `/post-your-ads` copy includes the 1-month term.

### Needs an operator / the client (no code can do these)

- Run 0024, 0027, 0031 (both), 0032 and **0033–0035** on live.
- SMTP credentials, From on the authenticated domain, SPF/DKIM/DMARC, then
  `php index.php cli mail_test <addr>` (#3); set `forms_recipient_email`.
- Enter the five social URLs at `/admin/setting` (#1); switch Blogs on if wanted (B5);
  pin the two top-menu categories (#21, #22).
- Decide: which near-duplicate categories to merge (#12, now possible in admin); per-size
  ad prices (#26); which accounts to suspend (B2).
- Rename/delete junk video and product categories (#23, #14).
- Retest each row's URL on a deployed copy — #2, #24 and the upload limits especially.

Key files: `application/controllers/{User,Admin}.php`, `application/models/{Usermodel,Adminmodel}.php`,
`application/helpers/{image,view}_helper.php`, `application/views/{cart_checkout,pricing_plan,listing_details,user_enquiry,user_dashboard,search,…}.php`,
`application/views/admin/{pricing,users,coupons,all_listing_category,…}.php`, `application/config/{routes,form_validation,mimes}.php`,
`assets/js/custom_validation.js`, `database/migrations/0033–0035`.

---

## 2026-09-25 — Six QA reports: dead theme JS, duplicated data, unusable search

Six separate reports from a pass over the live site. Two of them turned out to
be one cause, and two more turned out to be the same cause as each other.

### The one that explained two reports

> *"Get a quote button does not work on any of the business"*

`assets/js/custom_validation.js` carried eight jQuery-validate blocks from the
bought theme, each hijacking a live form's submit button to POST to
`enquiry_submit.html`, `review_submit.html` or `claim_submit.html` — flat-file
endpoints that have never existed in this CodeIgniter application — and each
building that URL from `webpage_full_link`, a variable no page defines. So the
handler threw a `ReferenceError` before it even got as far as the 404.

Three of those bindings were on forms that are still rendered:
`#detail_enquiry_submit` (the quote form on `/listing-details`,
`/classifieds-details`, `/house-details` and `/rental-details`),
`#review_submit` (the review form), and `#home_slide_enquiry_submit` — the
Help & Support slide-out in `footer.php`, which is to say **every page on the
site**. Somebody had already commented out the `#popup_claim_submit` binding
for exactly this reason and left the rest live.

All eight are gone. The forms post to their real endpoints, and client-side
validation comes from the markup via `assets/js/form-validate.js` — one
implementation that cannot drift from what the server accepts.

### Fixed

- **"Get a quote" pointed at an id that was not on the page.** The hero button
  is `href="#detail_enquiry_form"`, and that form was only rendered for a
  signed-in visitor. For everyone else — most of the people who want a quote —
  the button was an anchor to nothing and clicking it did nothing at all. The
  button now points at the sidebar **card**, which is always there, and the
  form is rendered for everyone: `add_enquiry` is on `User::$public_actions`,
  treats `enquiry_sender_id` as optional and is rate-limited to 8 per IP per
  hour *because* it accepts anonymous senders. Gating the form only ever cost
  the business the lead. The rejected-submit path now also re-renders what the
  visitor typed, with the per-field messages the controller has always flashed
  and no view ever read.
- **Four ways to write one review.** `/listing-details` had a Reviews card and
  a separate Write a review card, between them offering a "Write a review"
  header link, a "Write the first review" button, a second `<h2>`, a "Sign in
  to review" `<h3>` and a "be the first" link in the hero — three of which
  jumped a few hundred pixels down the page to the fourth. That is the
  reported *"multiple option and tabs to write or view review"*: a choice
  where there was none. Reading and writing are now the two halves of one
  `#reviews` section. `#write-review` survives as an id, so the hero link and
  any external anchor still land in the right place.
- **"Post a deal" was a link back to the page you were reading.** All three
  buttons on `/coupons` pointed at `base_url('user/coupons')`, and
  `/user/coupons` resolves to `User::coupons` — the public deals page. It was
  not a typo in a `href`: the theme shipped views for a member coupon screen,
  all posting to `user/add-coupon`, `user/edit-coupon` and
  `user/delete-coupon`, and **none of those methods was ever written**, on
  `User` or on `Admin`. Nothing anywhere in the application could create a
  coupon; `/coupons` read a table only phpMyAdmin could fill. Written now, as
  `/user/my-coupons` — not `/user/coupons`, because quietly shadowing the
  public URL is how this started.
- **Search found no restaurants when asked for "restaurants".** Two causes.
  There was no singular/plural folding, so the only rows containing the
  literal string were the ones that mention *serving* restaurants — which is
  how an electrician came back as the single business result. And every column
  counted the same, with the row id as the tie-break below the title, so a
  passing mention in a description outranked a business filed under the
  matching category because it happened to be newer. `"IT company"` was the
  other half: `LIKE '%it%'` is true of Smith, Whitney, furniture and capital.
- **Categories listed twice.** `/all-category` offered `Arts & Entertainment`
  (4 businesses) beside `Arts and Entertainment` (6), `Beauty & Personal Care`
  (26) beside `Beauty and Personal Care` (25), `Community & Government` (empty)
  beside `Community and Government` (14). Two URLs, two counts and two
  directory pages for one kind of business, with the businesses split between
  them and neither page admitting the other existed.
- **The same shop repeated down a category page.** `/all_listing/shopping-retail`
  said "42 businesses" over a first page that repeated the same shops. The
  count was never wrong — there really are 42 rows, several of them the same
  business twice. Same cause as the categories: the directory was seeded more
  than once and nothing has ever checked whether a business was already there.
- **A review's rating was never validated.** `price_rating` went from the POST
  body into `listing_reviews` and then into `updateListingRating()`, so a
  hand-posted `price_rating=500` moved a business's public average to 500
  stars. Clamped to 1–5, with the rest of the fields checked alongside it, and
  the redirect now lands on `#reviews` so the confirmation is beside the form
  it came from.
- **`/coupons` fanned out.** It called `getActiveCoupons()`, whose join runs
  through the *owner's* listings, so a business with four listings put the
  same offer on the page four times. The homepage has always called the
  de-duplicating `getDistinctActiveCoupons()`; this page never did.

### Added

- **Member coupon management** — `/user/my-coupons`, `/user/add-coupon`,
  `/user/edit-coupon/<id>`, `/user/delete-coupon/<id>`, plus a **Deals &
  coupons** item in the dashboard sidebar. Posting requires a published
  listing the member owns, which is the same trust gate the claim flow already
  applies, and the number of live deals per member is capped by
  `coupon_max_per_member` at `/admin/setting` (default 10). Delete is POST
  only — the theme's view called it from an `href`, which is one crawler away
  from clearing somebody's offers. The add and edit forms share
  `views/partials/coupon_form_fields.php` so they cannot ask for different
  fields against the same table, which is what they did before.
- **Merge control for duplicate categories** at `/admin/all_listing_category`.
  A panel lists the categories that read as the same thing and merges a pair
  in one click, moving every listing, sub-category and category-targeted ad
  before deleting the duplicate. It exists because migration 0031
  deliberately stops at names that are mechanically identical: whether
  Finance and Financial Services are one category, or Education and Education
  & Childcare, is an editorial decision about this directory and a migration
  is the wrong place to make it.
- **`/admin/duplicate_listings`** — businesses the directory holds more than
  once, with the evidence. Migration 0032 clears the cases where the duplicate
  is unclaimed; this is the rest, and nothing on it happens by itself, because
  "same name, same phone number" is a franchise and a head office as often as
  it is one business entered twice. Hiding one is the same soft delete the
  Trash button does.
- **Name-clash validation on categories.** `is_unique[]` compares the string,
  so it happily accepted "Arts and Entertainment" next to "Arts &
  Entertainment"; the edit form had no uniqueness rule of any kind, so
  renaming a category onto an existing one was simply allowed. Both now
  compare on `pearland_merge_key()`.
- **`pearland_merge_key()`** (`application/helpers/view_helper.php`) — the key
  two names are compared on: lower-cased, `&` and `+` read as the word "and",
  punctuation dropped, whitespace collapsed. Deliberately narrow, and matched
  statement for statement by migration 0031's SQL.

### Changed

- **Search matches a word's forms, not its spelling.** `searchTermVariants()`
  folds a term to its stem — `restaurants` → `restaurant`, `services` →
  `service`, `plumbers` → `plumber` — which under `LIKE '%stem%'` covers both
  forms for the price of the same one clause. `parties` → `party` + `parties`
  is the exception, because there neither contains the other. The stem has to
  be at least four characters: `news` would otherwise fold to `new` and match
  every renewal and newsletter on the site.
- **A word of one or two letters has to sit on a word boundary.** Separators
  are flattened to spaces first, so "Pearland IT, Inc." still matches "IT". It
  is a stack of `REPLACE()` rather than a `REGEXP` because the word-boundary
  syntax MySQL understands changed between 5.7 and 8.0 and this has to run on
  both. Three letters and up keep substring matching — "spa" should still find
  "MedSpa".
- **Where a word matched decides the order.** `searchRelevanceOrder()` takes
  weighted columns; `searchListings()` weights the name 8, the category 6,
  services offered 4, address and service area 2, and the description 1.
- **`applySearchTerms()` builds one SQL fragment** instead of nested
  `group_start()` calls: a short word needs a boundary test the query builder
  cannot express, and mixing the two inside one OR group is more error-prone
  than writing it out. Everything interpolated goes through
  `escape_like_str()`/`escape()`, and `searchTerms()` has already dropped
  everything that is not a letter or a digit.

### Migrations

- **`0031_category_dedupe.sql`** — merges categories that are the same name
  once normalised. The survivor is the one with the most published listings
  (lowest id breaks the tie) and inherits the loser's slug only if it has none
  of its own, so no indexed URL is thrown away. `listings`,
  `listing_sub_categories` and `ads.category_id` are all re-pointed and each
  merge is recorded in `listing_category_merges` before the row is deleted.
- **`0032_listing_dedupe.sql`** — hides businesses entered twice. Matches on
  the same name **and** one hard identifier: the phone number (digits only,
  last ten, so `+1 281-992-2064` and `(281) 992 2064` are one number), the
  street address, or the website host. Name alone is never enough — Pearland
  has more than one Subway. **It will not touch a listing with an owner:**
  claimed listings are somebody's property, quite possibly somebody's paid
  package, and where two claimed rows collide both are left for a person. The
  loser is soft-deleted, so reviews and enquiries stay attached and it is one
  click from restoring at `/admin/trash_listings`; every action is recorded in
  `listing_duplicate_merges`. **Run after 0031.**

Both are idempotent, both are mirrored into
`database/migrations_combined_phpmyadmin.sql` with their loops unrolled into
`REPLACE()` chains, and both use ordinary scratch tables rather than
`TEMPORARY` ones — choosing the survivor reads the same scratch table twice in
one statement, which MySQL answers on a temporary table with "Can't reopen
table".

### Key files

`assets/js/custom_validation.js`, `application/views/listing_details.php`,
`application/controllers/User.php`, `application/controllers/Admin.php`,
`application/models/Usermodel.php`, `application/models/Adminmodel.php`,
`application/helpers/view_helper.php`, `application/views/user_coupons.php`,
`application/views/add_coupons.php`, `application/views/edit_coupons.php`,
`application/views/partials/coupon_form_fields.php`,
`application/views/admin/duplicate_listings.php`,
`application/views/admin/all_listing_category.php`,
`assets/css/directory.css`, migrations `0031`/`0032`.

### Still open

Coupons are now member-posted and public, and there is still no admin screen
for them — the admin sidebar's coupon links have always been dead `.html`
theme markup. Posting is gated on owning a published listing, which an admin
approves through the claim flow, so the trust bar is real; a takedown screen
is the obvious next piece.
## 2026-09-24 — Nobody could sign up, and nothing said why

Reported from the live site: new accounts are created successfully, the
verification email never arrives — Inbox or Spam — and because login is blocked
until the address is confirmed, the account cannot be used. Forgot Password had
the same symptom.

The mail code was not obviously wrong, which is the interesting part. What was
wrong is that **every way of finding out had been closed off**:

- `registerUser()` and `requestPasswordReset()` both called
  `$this->mailer->send(...)` and **discarded the return value**. A signup showed
  "You're all set. Sign in and you can add your business straight away" whether
  or not the message left the building — and that sentence was never true
  anyway, since `Usermodel::login()` refuses an unverified account.
- The only report of a failure was `log_message('error', ...)`, and
  **`application/logs/` is not in the repository**. CI's logger disables itself
  when its directory is missing, so on any fresh deploy every mailer error went
  nowhere. `.gitignore` had an exception for `application/logs/index.html`, but
  the file it was protecting had never been committed.
- Nothing recorded an attempt anywhere else, so there was no way to tell a
  refused send from a delivered-and-discarded one.
- A user whose message was lost had **no way back in**: nothing on the site
  could issue a second verification link.

So the outage is fixed by making it visible and recoverable, and by removing the
two configurations most likely to have caused it.

### Added

- **`/admin/email-diagnostics`** (super-admin). The effective mail settings, the
  combinations known to break delivery, a test send that reports the mail
  server's own error rather than a boolean, and the last seven days of attempts.
  Also warns when CI's log directory is unwritable, since that is what hid the
  original failures.
- **`php index.php cli mail_test <address>`** — the same checks from the shell,
  for when nobody can sign in to reach the admin page. Exits non-zero on failure.
- **`email_log` table** (migration `0031_email_log.sql`): recipient, subject,
  type, transport, status and truncated error for every message the site tries
  to send. Sending works without it — `Mailer::record()` checks `table_exists`
  first — you just can't see what happened afterwards.
- **`/resend-verification`**, rate-limited 5/hour/IP, linked from `/login` (and
  pre-filled with the address that was just refused), from the signup
  confirmation, from `/forgot-password` and from a failed `/verify-email`. It
  re-issues the token as well as the mail, and answers identically whether or
  not the address is registered, so it can't be used to enumerate accounts.
- **`Mailer::config_status()`** — the check that names the likeliest cause of
  "accepted but never delivered": a From address on a domain the SMTP account
  is not authorised to send for. A relay accepts such a message; the recipient
  fails SPF/DMARC and discards it with no Spam copy and no bounce, which is
  exactly the reported symptom and is invisible from the sending side.
- **`application/logs/`** now exists in the repo, with an `index.html` and an
  `.htaccess` that denies access (log files carry SMTP transcripts and query
  text).

### Fixed

- **Verification and password-reset mail now goes through `Mailer::render()`**
  like everything else. Both were bare `<p>` fragments handed straight to
  `send()`: no document, no branding, no plain-text part. The two messages a
  customer must act on were the two worst-formed things the site sent, and
  HTML-only fragments score badly with filters.
- **Every message now carries a plain-text alternative**, derived from the HTML
  with link URLs preserved. (The obvious `text <url>` substitution is eaten by
  `strip_tags` — `<https://…>` looks like a tag — so links are extracted before
  the strip and restored after. Caught by testing the actual output rather than
  the regex.)
- **`send_subscription_email()` was sending as `noreply@yoursite.com` ("Your
  Site Name")** — the placeholder that shipped with the theme. A domain the site
  does not own fails SPF everywhere, so payment receipts were being discarded or
  spam-foldered, and any that landed looked like a phishing attempt for a
  payment the customer had just made. Now routed through the Mailer like the
  rest.
- **The transport no longer forces SMTP at empty credentials.** With
  `SMTP_USER` / `SMTP_PASS` blank the old config still set `protocol => 'smtp'`,
  which fails 100% of the time; it now falls back to the host's own MTA.
  `MAIL_PROTOCOL` overrides either way.
- **`SMTP_TIMEOUT`, default 30s.** CI's default is 5, which a shared host
  opening TLS to a public relay routinely overshoots — and the resulting failure
  is indistinguishable from a wrong password.
- **The signup confirmation no longer tells people to sign in.** It says to
  confirm the email, names the address it went to, and offers a resend; when the
  send failed it says so and offers the resend and a contact link instead.
- **`config_status()` also catches the port/crypto mismatches** (465 with `tls`,
  587 with `ssl`) that produce an unexplained connection failure.

### Changed

- `/forgot-password`, `/reset-password` and `/verify-email` rebuilt on the
  shared page kit, so the reset flow stops looking like three different sites.
  Their controller methods never set `$page_css`, so the kit wasn't loaded on
  them at all. The failed-verification page now offers a way forward rather than
  a button back to a login form that will refuse the account.
- `Mailer::send()` takes a `log_type` option and returns a meaningful bool;
  `last_error()` exposes the (entity-decoded, readable) SMTP transcript for
  admin eyes only.

### Operator note

The code change cannot fix a From domain the SMTP account may not send for —
that is DNS, not PHP. If `/admin/email-diagnostics` shows the mismatch warning,
follow **Email troubleshooting** in `README.md`: relay through a provider where
`pearland411.com` is verified, publish SPF, turn on DKIM. Until then, setting
`MAIL_FROM_ADDRESS` to the SMTP account's own address makes the domains match
and is the honest short-term fix.

### Key files

`application/libraries/Mailer.php`, `application/models/Usermodel.php`,
`application/controllers/User.php`, `application/controllers/Admin.php`,
`application/controllers/Cli.php`, `application/config/routes.php`,
`application/views/{login,register,forgot_password,reset_password,verify_email,resend_verification}.php`,
`application/views/admin/{email_diagnostics,header}.php`,
`database/migrations/0031_email_log.sql`, `.env.example`, `README.md`,
`database/README.md`.

---

## 2026-09-03 — No vaulted card had ever been charged

The cron installed yesterday ran, found the one due subscription, and printed:

> `failed sub 16: HTTP 400 (retry)`

Not a decline. Helcim's Payment API requires an `idempotency-key` header — a
unique 25-character alphanumeric string it remembers for five minutes — and
answers a request without one with a bare 400. `Helcim_lib::request()` sent
`api-token`, `Content-Type` and `Accept`, and nothing else, so **every**
`/payment/purchase` call this project has ever made was rejected before a card
was touched.

That means the failure was never about the cron. `Cli::renew_subscriptions`
and the admin's **Charge the card on file** button are the only two callers of
`charge_vaulted_card()`, and both have been broken since the Helcim migration
landed. It stayed invisible because the paths that *do* work — self-serve
checkout and staff's **Enter a card & charge** — go through
`/helcim-pay/initialize`, which requires no such header. The Aug 27 payment on
the account that prompted this was taken that way. Recurring billing has never
once collected.

### Fixed

- **`idempotency-key` on Payment API calls.** Generated per attempt, or
  derived from a caller-supplied seed. `/helcim-pay/initialize` does not take
  the header and does not get it.
- **`ipAddress` in the purchase payload.** Helcim scores it for fraud and
  documents it as required; it was sent only when a caller happened to pass
  one, and neither caller did. A cron has no client, so `SERVER_ADDR` — or the
  host's resolved address under CLI — stands in.
- **Errors that name the problem.** `Helcim_lib::error_message()` reads
  `errors`, `message` and `error`, and falls back to a trimmed copy of the
  response body instead of the literal string `HTTP 400`. The old summary is
  the reason this cost a day: the job dutifully reported a number that
  described nothing, every night, while a customer went unbilled. The log line
  now carries the endpoint too.

### Changed

- **Renewals key their idempotency on the subscription and its due date**
  (`Cli::renew_subscriptions`), so a duplicate cron — or a hand-run alongside
  the scheduled one — collapses into a single charge instead of billing twice.
  A retry the next day moves `next_billing_date`, so it keys anew and does
  charge. Staff one-off charges keep a fresh random key: charging the same
  amount twice on purpose is a thing they are allowed to do.

### Key files

`application/libraries/Helcim_lib.php`, `application/controllers/Cli.php`,
`README.md`. No schema change, no migration.

---

## 2026-09-02 — The renewal cron, written down where it can be found

A customer set to renew on the 1st was not charged. Nothing in the code was
wrong: `Cli::renew_subscriptions` was never installed as a cron on the live
server, and the only record of how to install it was one four-line bullet in
the README. The screen at `/admin/billing-customer/<id>` says the charge is
coming; without the cron it never comes, and the first person to notice is the
customer.

### Added

- **README ▸ “The renewal cron (recurring billing)”** — the cron line for the
  live account written out in full (`/home/pearlan/public_html`), field by
  field for cPanel's *Add New Cron Job* form as well as for `crontab -e`, so
  there is nothing left to substitute and no placeholder to leave behind. The
  first cron on the live server shipped with the example's `USERNAME` still in
  its path, which fails exactly like a quiet night with nothing due; `echo
  $HOME` / `which php` are there for any other account. Also the MultiPHP
  trap — `/usr/local/bin/php` is the *server* default and often older than the
  PHP the domain runs, so the version in MultiPHP Manager is the one to use —
  and the five conditions a subscription must meet to be charged at all.
- **What to do about charges missed while the cron was off.** The job selects
  `next_billing_date <= NOW()`, so the first run takes the backlog on its own.
  Taking a missed charge by hand instead is the trap worth documenting:
  **Take a payment** is deliberately recorded without moving the schedule, so
  a date left in the past is charged again by the next nightly run.
- **How to tell it is running** — the `/admin/billing` heartbeat
  (`billing_cron_last_run`), and the advice to run the command by hand once
  after installing the cron rather than waiting a night to learn it failed.

Documentation only. No code, schema or migration changes; behaviour of
`Cli::renew_subscriptions` is unchanged.

### Key files

`README.md`.

---

## 2026-08-25 — The billing 500 was mine

The diagnostic screen added an hour earlier did its job on the first load:

> **Call to undefined method Adminmodel::getAllPlanDurations()** — Admin.php line 6854

Not a schema gap. Not a missing migration. A method I deleted myself.

Rewriting the billing model block for 0029 replaced a span of `Adminmodel`
running from the `billingFromSql()` docblock to the `getCustomerCards()`
docblock — and `getAllPlanDurations()` sat inside it. The controller still
called it. Nothing caught this: every suite here exercises models directly,
and not one of them walks a controller's calls.

Both of my earlier theories about this 500 — the missing `duration_*` columns
and `plans.sort_order` — were wrong about *this* failure. The hardening they
produced is worth keeping on its own merits (the schema really is missing those
columns, and the self-serve checkout really is broken because of it), but
neither was the cause, and I should have made the page report before guessing
twice.

### Fixed

- **`Adminmodel::getAllPlanDurations()` restored.** `/admin/billing-customer/<id>`
  loads again.

### Added

- **A resolver check across all three controllers.** It walks every
  `$this->adminmodel->x()` and `$this->usermodel->x()` call — 707 of them — and
  asserts the model really defines it. Case-insensitive, because PHP method
  names are: `allclassifiedsCategory()` genuinely does reach
  `allClassifiedsCategory()`, and a case-sensitive check reported two false
  positives before I matched PHP's own semantics.

  This is the check that was missing. It costs a second to run and would have
  caught the deletion the moment it happened, instead of in production two
  deploys later.

### Verified

177 assertions across ten suites. The resolver check passes across all 707
calls, including the restored method.

---

## 2026-08-25 — Stop guessing at the billing 500

The billing detail page was still returning a bare HTTP 500 after two fixes
aimed at it. `ENVIRONMENT` is `production`, so `display_errors` is off and a
fatal reaches the browser with nothing in it — and on a screen that touches
eight tables, several carrying columns no migration in this repo ever created,
that turns every schema gap into a guessing game played one deploy at a time.

So this round stops guessing and makes the page report.

### Added

- **A diagnostic screen instead of a blank 500.** `Admin::billing_customer()`
  is now a thin wrapper around `billing_customer_page()` that catches
  `Throwable` — PHP 7+ `Error`s included, so "call to a member function row()
  on bool", which is what a failed query becomes when `db_debug` is off, is
  caught like any exception. Staff get the message, the file and the line, a
  pointer at the migrations, and the reassurance that nothing was charged; the
  log keeps the full trace. Super-admin-only route, so nothing leaks.

### Fixed

- **`Usermodel::getAllPlans()` is no longer on this path.** It orders by
  `plans.sort_order` unconditionally, and the billing screen was its **only
  caller anywhere in the codebase** — so nothing had ever proved that column
  exists on this database. New `Adminmodel::getPlansForBilling()` applies the
  ordering only when the column is really there, and treats a `plans` table
  without `is_active` as "every plan is sellable". A plan list in the wrong
  order is cosmetic; a missing column is a 500.

### Verified

10 new assertions on the wrapper, lifted from the real source: the happy path
renders the real page and logs nothing; a failed query renders the error screen
inside the admin chrome and logs once, naming the business; and an
unknown-column message reaches the view verbatim with its file, line and
listing id. 176 assertions across nine suites.

### Note

If the page still fails after this, it will say why on screen. That message is
the thing to send back — it names the table and column, which no amount of
reading the code from outside can.

---

## 2026-08-25 — The billing page's 500, and the checkout bug behind it

`/admin/billing-customer/5951` returned HTTP 500 on every business. The list
worked; the detail page did not.

The detail query named `subscriptions.duration_id` and
`subscriptions.duration_days`. **No migration in this repo has ever created
those columns.** They are written only by
`Usermodel::activateHelcimSubscription()` — the self-serve checkout — so on a
database where no customer has ever completed one, they do not exist. MySQL
answers a missing column with error 1054 rather than a NULL, and the request
died. The list page survived because it happens not to select them.

Which surfaces a bigger thing: **the self-serve checkout has been broken here
the whole time, in the same way and for the same reason.** Its INSERT names
`duration_id`, `duration_days`, `duration_months`, `total_amount`,
`billing_cycle`, `payment_status`, `start_date` and `last_transaction_id` —
none of which any migration creates. Nobody noticed because nobody had bought
anything yet. Central Barbecue would have been the first to find out.

### Fixed

- **`getBillingCustomer()` no longer depends on columns that may not exist.**
  It reads the listing and its owner in one query and the subscription in
  another with `SELECT *`, then flattens the two onto one object with `??`, so
  a column this schema never created reads as "not recorded" rather than
  taking the page down. The split also removes the collision that made the
  single join fragile — `s.user_id` overwriting `u.user_id` and blanking the
  owner of the business being looked at.
- **Every billing write drops unknown columns before inserting.**
  `startSubscriptionForCustomer()` would have thrown the same 1054 the moment
  anyone pressed *Save recurring billing*; `recordManualCharge()` now filters
  its whole row rather than a hand-picked few, because by the time it runs the
  money has already moved at Helcim and a schema gap must cost a field, never
  the record of the payment.

### Migrations

`database/migrations/0030_subscription_term_columns.sql` — guard-adds the nine
`subscriptions` columns the code writes and no migration ever created. Each is
added only when missing, so a database where a checkout did once succeed is
untouched.

The application no longer *depends* on it — that is what the two fixes above
buy — but it should still be run: without `duration_days` a renewal cannot
know its own term length, and `recordRenewalSuccess()` would silently extend
every subscription by the 30-day fallback regardless of what was sold.

### Verified

24 new assertions, run against the real methods with a stub whose
`subscriptions` table is missing exactly the columns production is missing: the
page builds for a business with no subscription and for one with a
subscription, every field the view reads is present, the absent columns read
as NULL without warning, and starting a subscription still writes the business,
the agreed price, auto-renew and the charge date while quietly dropping the
columns the table cannot hold. 166 assertions across eight suites in total.

---

## 2026-08-25 — Billing follows the business, not the login

Searching the new billing screen for "central bbq" returned exactly one row:
the import account **"10 dollars Galveston shuttle"**, with a hundred unrelated
business names strung after it. That is not a search bug — it is the screen
being keyed on the wrong thing.

0028 built `/admin/billing` around `user`, because `subscriptions` and
`payment_cards` are keyed on `user`. On this site the listings were bulk-loaded
under a handful of data-entry logins, so **one account owns dozens of unrelated
businesses**. Billing that row would have billed all of them at once, and
"expire the account's active subscription before starting a new one" would have
cancelled twenty businesses as a side effect of signing up the twenty-first.
What staff sell, invoice and cancel is one business.

### Changed

- **`/admin/billing` lists businesses.** One row per listing: business, owner
  account, package or agreed price, status, card, auto-renew, next charge.
  Search matches the business name, or the owner's name or email. The default
  view is businesses with billing activity; a search reaches every listing, so
  a business that has never been billed is one search away.
- **`/admin/billing-customer/<id>` takes a listing id**, not a user id, and is
  scoped to that business throughout — the subscription it reads and writes,
  and the payment history it shows. The **card still belongs to the owning
  account**, because that is where Helcim vaults it and one owner paying for
  three of their businesses uses one card. The page says so rather than
  pretending otherwise.
- **Starting a subscription expires only that business's** previous one. This
  is the change most worth the test that covers it.
- **Payment history is per business**, falling back to the account's own
  unattached payments so nothing recorded before this migration disappears.

### Added

- **An agreed price per business** (`subscriptions.custom_amount`). A package
  still sets what the business *gets*; this sets what it *pays*, on every
  renewal. Blank charges the package price, so nothing changes for anyone who
  does not need a negotiated rate. It also closes the gap flagged last round:
  a founding-member rate survives a later rise in the plan's list price,
  instead of silently re-pricing every customer on that plan.
- **A standard email layout** — `application/views/emails/layout.php`, rendered
  by `Mailer::render()`. Every message the site sends should go through it.
  Before this each template was built by string concatenation at its call site,
  so a subscription email, a renewal warning and an enquiry alert were three
  different-looking messages from the same company — and a customer being asked
  to check their card had no reason to believe the mail was ours. Table-based
  with inline styles and no external images, because Outlook renders with
  Word's engine and images are blocked by default.
- **Billing notifications.** Every active admin plus whatever
  `billing_notify_emails` lists at `/admin/setting` — seeded with
  **joshua@spacecentersystems.com** — is emailed when a card goes on file, a
  payment is taken, a recurring schedule changes, and when the nightly renewal
  charges, retries or expires. Recurring billing is the one thing here that
  moves money with nobody watching; a silent 2am failure that surfaces as a
  customer complaint is the failure mode worth designing against.
  `Mailer::notify_billing()` never throws and never blocks its caller: a mail
  server outage must not turn a successful charge into a reported failure.

### Added — the cron can now be checked from the admin

`/admin/billing` shows when the nightly job last ran and what it did, stamped
by `Cli::renew_subscriptions` into `billing_cron_last_run` /
`billing_cron_last_summary` on every run — including the early return when
Helcim is not configured, so "ran but not set up" and "never ran" stay
distinguishable.

This exists because the first cron installed on the live server still had the
example's `USERNAME` placeholder in its path. From the admin that is
indistinguishable from a quiet night with nothing due, and the first sign of
the difference would have been a customer whose card was never charged. Never
run is a loud warning, over 48 hours is a warning, otherwise a quiet line with
the last summary. Neither key is in the settings whitelist — they are written
by the job, not edited.

### Migrations

`database/migrations/0029_billing_per_business.sql` — `subscriptions.listing_id`
(+ index), `subscriptions.custom_amount`, `transactions.listing_id` (+ index),
and seeds `billing_notify_emails`. All nullable with no default, nothing
back-filled: **NULL means account-level**, which is every row that exists today
and everything the self-serve checkout creates, so those keep working exactly
as they do now. Idempotent, each step guarded on its table. Mirrored into
`database/migrations_combined_phpmyadmin.sql` and `database/README.md`.

Note for the bundle: the column comment deliberately contains no semicolon.
phpMyAdmin's importer splits on `;`, and one inside a quoted `COMMENT` string
is exactly the kind of thing that breaks it.

### Verified

142 assertions across seven suites, all green, against the real function source — including the heartbeat banner's never-run / 47h / 49h / week-old boundaries.

The new ones cover what this change risks. Per-business scoping: a new term is
attached to the business, only that business's previous subscription is
expired, and the `WHERE` carries the listing id rather than the shared account
— while an account-level subscription still scopes by account **and** by
`listing_id IS NULL`, so the old behaviour is untouched. The agreed price:
stored on create, settable and clearable on reschedule, beating both the plan
price and the term price, with blank and zero correctly *not* treated as an
override and a missing property safe on a pre-0029 row. The email layout is
rendered and parsed: heading, body, preheader and contact details present, a
hostile heading escaped rather than executed, no flexbox, no external images,
and no fatal HTML errors.

All five billing queries still parse as MySQL with matching bind counts, as do
the migration's five statements in both the standalone file and the bundle.

### Key files

`application/controllers/Admin.php` (billing rekeyed to listings,
`billingNotify`), `application/controllers/Cli.php` (renewal notifications),
`application/models/Adminmodel.php` (listing-centric queries, agreed price,
per-business expiry), `application/models/Usermodel.php` (`renewalAmountFor`
honours the agreed price), `application/libraries/Mailer.php` (`render`,
`billing_recipients`, `notify_billing`),
`application/views/emails/layout.php` (new),
`application/views/admin/billing.php`, `billing_customer.php`,
`admin_setting.php`, `database/migrations/0029_billing_per_business.sql`.

---

## 2026-08-25 — Tracker rows 164–169

Six rows off the manager's error tracker, this time with the screenshots
attached — which mattered: two of them are only explicable from the picture.

### Fixed

- **`/admin/add-news` returned HTTP 500 on upload (row 169).** `uploadAndResize()`
  handed the file to CodeIgniter's `Image_lib` with `maintain_ratio` off and
  **no width or height** — those two lines had been commented out, and
  `Image_lib::initialize()` fills a missing width/height in from the source. So
  it allocated the original dimensions *twice* to write a same-size copy: the
  full memory cost of a resize, and none of the resizing.

  Measured on a 4000×3000 upload — a 4 MB file, well inside the 5 MB limit —
  that is **92 MB of real memory, of which PHP reports 2 MB**. With bundled
  libgd the image buffers are `malloc`'d outside the Zend allocator, so
  `memory_limit` never sees them and never intervenes; what stops the request is
  the process or container limit, from outside PHP, which is exactly why the
  symptom was a blank 500 rather than an error message. Capped at 2400px the
  same file costs 33 MB.

  New `pearland411_downscale_image()` asks whether the allocation fits *before*
  making it, caps the longest edge at 2400 (above both sizes `image_guidance()`
  recommends, so an image prepared to spec is left byte-identical), preserves
  PNG/WebP alpha, and returns FALSE rather than attempting work it cannot
  finish. `pearland411_generate_variants()` gained the same guard —
  `safe_upload_image()` could take the process down the same way.
- **A rejected upload used to save the row and say nothing.** `uploadAndResize()`
  threw the reason away and returned NULL, so an article or listing saved
  without its picture and no screen mentioned it. New `upload_last_error()`
  carries the reason to the caller — passed through a module-level static
  rather than by changing the return value, so all ~40 existing call sites
  behave exactly as before — and `add_news` / `edit_news` now say what happened.
- **Editing a news article could destroy its picture.** `edit_news()` called
  `unlink()` on the existing image *before* running the upload, so a replacement
  the upload then rejected — wrong type, too large, or the resize that used to
  fatal — took the old one with it and left the article with none. The old file
  is deleted only once a new one exists. Its failure branch also redirected to
  `admin/edit-news<slug>`, missing a slash, which is a 404 rather than the form.
- **The burger menu offered a signed-out visitor a member dashboard (row 165).**
  Below 993px `.bl` is hidden, so Add business / Sign In / Create an account
  leave the bar; the only navigation left was a panel listing My Dashboard, My
  Profile and **Log Out** — to somebody who had never logged in. From a narrow
  window there was no way to reach sign-in at all. Signed-out visitors now get
  Sign In, Create an account, Add your business, the directory, categories and
  Contact us.
- **The section strip was cropped and unreachable between 769 and 992px
  (row 165).** The bar switches to its narrow layout at 992px but the strip only
  became scrollable at 768px, and `html, body { overflow-x: clip }` *crops*
  rather than scrolls — so in that 224px band the right-hand items (the pinned
  categories, "All categories") ran past the edge with no scrollbar and no way
  to get to them. That is the screenshot: a burger, and a strip ending
  mid-word. The strip now scrolls from the same width the bar collapses at.
  The burger also had `cursor: pointer` only below 767px, so for 225px of that
  range it did not look clickable — part of "doesn't work sometimes".
- **The burger menu's profile picture was broken for signed-out visitors
  (row 166).** `$header_profile_img`, `$header_user_name` and
  `$header_join_date` were assigned inside the **logged-in half** of the header's
  branch, but the mobile menu that reads all three renders for everyone. Signed
  out, the panel opened with `<img src="">` — which browsers draw as a broken
  image and which never fires the `onerror` fallback. All three are resolved
  once, above the branch.
- **…and the panel stayed open when the window widened (row 166).** Nothing
  cleared `.mobmenu-show` on resize, so a full-screen panel stayed pinned over
  the desktop layout with no way to shut it: its close button is inside the
  panel, and `.mob-me-ic` is `display:none` at that width, so the state could
  not be undone without a reload. That is the reported trigger — *press the
  burger, then widen the view*. It closes on resize past 992px, and on Escape.
- **The dark band behind that profile picture was a tiled photograph.**
  `.ud-lhs-s1:before` tiled `assets/images/face.jpg` — a 400×200 file — at
  `background-size: 101px` under `brightness(0.3)`, so across a full-width band
  it repeated as a row of dark chopped-up rectangles. It reads as a broken
  image, and worst of all at desktop width, which is where the reporter saw it.
  Brand navy now, with nothing to tile.
- **Reaching page 20 of the directory took 17 clicks (row 164).** The pager
  shows the current page plus two either side, then first and last — so from
  page 1 of 47 the only numbers on offer are 1, 2, 3 and 47. `/all_listing` now
  also accepts `?page=N`, and a **"Go to page"** box under the pager posts to
  it. A plain GET form, so it works with JavaScript off, and it carries the
  sort so jumping does not silently reset it. The positional
  `/all_listing/<category>/<offset>` URLs are untouched — every existing link,
  bookmark and indexed URL still resolves.
- **A coupon page showed a code and gave you nothing to do with it (row 167).**
  "Redeem this offer" only rendered when a redemption link happened to be set;
  without one the page was a code and a full stop, which is the reported "I
  don't see any use for this, how do users claim a coupon?". The code now has a
  **Copy code** button (revealed by script only where the browser can actually
  copy, so it is never inert), a linked business gets a **Where to use it**
  button, and the instructions say what is actually possible instead of
  promising an online checkout on every deal.
- **"Posted by Mydynamic VA" (row 167).** `owner_name` is the account that
  *typed the coupon in* — for anything entered by the team, a staff or agency
  login. Used as a fallback it put the data-entry user on a customer-facing page
  as the merchant, and had the copy read "Mydynamic VA is offering this deal to
  Pearland411 readers". A coupon is attributed to the business it is linked to,
  or to Pearland411 when there is none. The same fallback is gone from the
  coupons index (where it also inflated the "businesses represented" count: ten
  coupons keyed in by one staff login counted as one business) and from the page's
  meta description, which was telling search engines the same thing.
- **The admin owner dropdown was unreadable (row 168).** It printed `user.name`
  and nothing else, and accounts are named after businesses rather than people,
  so the Edit Listing picker read "10 dollars Galveston shuttle", "ACT
  Electrical", "Angel Coliflores- Test" with no way to tell a customer from a
  leftover. New `user_option_label()` adds the email and the number of listings
  the account owns — the count is what actually separates a live customer from a
  test row. Applied to all seven user dropdowns.

### Added

- **`/admin/users` — Members**, a read-only review list: account, email, phone,
  businesses owned, whether the email is verified, and when they joined, most
  listings first, searchable by name, email **or business name**. Row 168 asks
  to "review each user and provide context if those are final", and that could
  not be done by anyone: there was no user screen in the admin at all — the only
  place an account appeared was inside a dropdown. Read-only on purpose;
  deleting a member orphans every listing, event and article they own, and that
  needs its own screen with its own warnings.

### Verified

41 assertions, all green, against the real function source rather than a
re-implementation, plus 8 SQL statements parsed as MySQL with matching bind
counts.

The image tests run real files through the real helper: a 4000×3000 JPEG capped
to 2400 with its aspect ratio kept, an 800×600 left **byte-identical** (not
re-encoded), a transparent PNG that stays a PNG with its alpha intact, garbage
and missing files refused. The memory behaviour is measured in subprocesses at
fixed limits: the old path's 92 MB against 2 MB visible to PHP, the new path
declining inside a 96 MB budget and exiting cleanly, doing the work when given
512 MB, and the same image costing 33 MB once capped.

### Not changed — flagged

- **`user_header.php` still says "Rn53 Themes"** in its mobile menu, with the
  theme's demo photograph and "Join on 26, Mar 2021". No controller loads that
  file — it is one of the unreferenced views already flagged — so it renders
  nowhere and was left alone rather than fixed in place.
- **~40 other upload call sites** still use the `if ($_FILES && $_FILES['x']['name'])`
  idiom, which is an "Undefined array key" warning whenever a form is saved
  without that image. Harmless, pre-existing, and the helper now guards itself,
  so rewriting all of them belongs in its own pass.
- **The 500 could not be reproduced against the live server** from here. The
  memory fault above is real, measured, and in the reported code path, but if
  uploading news still fails after this, the new error reporting and the log
  line will name the cause instead of showing a blank page.

### Key files

`application/helpers/image_helper.php` (`pearland411_downscale_image`,
`pearland411_image_fits_memory`, `pearland411_memory_limit_bytes`,
`upload_last_error`), `application/helpers/view_helper.php`
(`user_option_label`), `application/controllers/Admin.php` (`add_news`,
`edit_news`, `users`), `application/controllers/User.php` (`all_listing`,
`coupon_details`), `application/models/Adminmodel.php`,
`application/views/header.php`, `application/views/admin/users.php` (new),
`application/views/coupon_details.php`, `coupons.php`, `all-listing.php`,
seven admin dropdown views, `assets/js/custom.js`,
`assets/css/{site-header,directory,content}.css`. No migration.

---

## 2026-08-25 — Staff can bill a customer: card on file, this month's payment, next month's date

Central Barbecue is the first paying customer, and the request was to put their
card on the August bill and restart recurring billing in September — plus "is
there somewhere in the admin to do this?"

There was not. **A card could only ever be vaulted by the customer**, inside
`User::process_checkout()`, and only if they ticked auto-renew during their own
checkout: `Usermodel::activateHelcimSubscription()` is the only writer of
`payment_cards`. `/admin/all_subscriptions` could list, cancel and delete a
subscription and nothing else — no card, no payment, no schedule. So a business
signed up over the phone had no card on file, `Cli::renew_subscriptions` found
no token, and the row was skipped every night with "no vaulted card".

### Added

- **`/admin/billing` — Customer Billing**, under Pricing Plans in the admin
  menu, super-admin only like the rest of the billing screens. The list shows
  plan, status, card-on-file, auto-renew and next charge date per customer.
  `?q=` searches **every** customer in SQL, word by word, across the account
  name, the email **and the names of businesses they own** — staff know a
  customer as "Central Barbecue", not as whoever registered the account. With
  no search it lists only customers who have a subscription, a card or a
  payment, so the handful who pay are not buried under every registered
  member. The table carries `data-server-search` so the table enhancer does
  not add a second box that only filters the current page.
- **`/admin/billing-customer/<user_id>`**, which does the three things that
  were missing:
  - **Card on file.** Opens the same HelcimPay.js iframe the storefront uses,
    in `verify` mode — a zero-dollar check that confirms the card and returns
    a token, charging nothing. The card number is typed into Helcim's window
    and goes straight to Helcim; this server never sees it. If an account does
    not allow zero-dollar verification the error says so and points at the
    payment flow, which returns a token too.
  - **Take a payment.** Either charge the card already on file (server-side,
    no browser round trip) or enter a card and charge it, optionally keeping
    it for renewals. Recorded with a note, so "August 2026 bill" is
    distinguishable from an automatic renewal in the history.
  - **Recurring billing.** Auto-renew on/off and the date of the next charge.
    An **active** subscription is rescheduled in place; anything else — expired,
    cancelled, or none at all — starts a new term on the chosen plan, because
    `getRenewableSubscriptions()` only ever charges `status = 'active'` rows.
    Switching auto-renew on for an expired subscription would have looked like
    it worked and then silently charged nothing, which is the worst available
    outcome for a billing screen.
- **`Helcim_lib::initialize_card_verification()`** — a `verify` session for
  vaulting a card without moving money.
- **`Usermodel::renewalAmountFor()`** — the renewal price (the chosen
  `plan_durations` term, else the plan) lifted out of
  `Cli::renew_subscriptions` so the screen and the cron cannot disagree.
  Nothing displayed the figure anywhere before this; the only way to know what
  a customer would be charged was to read the job.
- **`billing_manual_charge_max`** at `/admin/setting` → Billing, default
  **25000**. The amount on the billing screen is the only figure in the system
  a human types freehand — everything else is priced server-side from a plan —
  so it gets a ceiling. `0` removes it.

### Changed

- **Rescheduling clears the dunning state.** `renewal_attempts`,
  `last_renewal_error` and `grace_until` describe failures against the *old*
  schedule. Left in place, a customer whose card had already failed twice would
  get one attempt on the new date and then be expired by the 3-strike rule.
- **A next billing date later than the term end now moves the term end with
  it.** The subscription would otherwise expire in the gap between the two
  dates, and the renewal job — which only looks at active rows — would never
  run for it.
- **Expiry "01/2099" renders as "Not recorded".** That is the placeholder the
  self-serve checkout writes when the gateway returned no expiry date; printing
  it as a date would tell staff the card is good for another seventy years.

### Security / PCI

- Card data is tokenized in the browser by HelcimPay.js and never posted to
  Pearland411, on the admin path exactly as on the storefront. The response is
  trusted only after `validate_transaction()` recomputes its SHA-256 against
  the `secretToken` held server-side, and the payload is **never logged** —
  only the checkout token is, because the payload carries card details.
- The pending checkout is consumed from the session **before** the payment is
  recorded, so a replayed post cannot book the same money twice.
- Every new route is in `Admin::__construct()`'s `$super_admin_only` list.
  Card removal is scoped by `user_id` as well as card id, so a hand-edited
  form cannot delete someone else's card.
- Migration 0028 adds **no** column that could hold a PAN, expiry or CVV beyond
  the last four digits.
- If a charge succeeds and the bookkeeping insert then fails, the message names
  the Helcim transaction id and says **do not charge again** rather than
  offering a retry.

### Migrations

`database/migrations/0028_admin_billing.sql` — `payment_cards.card_brand`,
`card_source` (`customer` / `admin`), `added_by_admin_id`;
`transactions.created_by_admin_id`, `note`. All nullable or defaulted, so no
existing row changes and nothing is back-filled; every step guarded on its
table as well as its column. Idempotent. Mirrored into
`database/migrations_combined_phpmyadmin.sql` in that bundle's inline-PREPARE
style and added to `database/README.md`. The screen degrades rather than
breaking if it has not been run: the audit and display columns are written only
when `field_exists()` finds them.

### Verified

62 assertions, all green, run against the **real source** of the methods lifted
out of `Admin.php` and `Adminmodel.php` rather than a re-implementation.

The subscription-creation cases caught the one bug worth naming here: the first
term is deliberately short (set up on the 25th, first renewal on the 1st), and
`duration_days` was being derived from that gap — but `recordRenewalSuccess()`
extends by `duration_days` on **every** renewal, so a customer set up that way
would have been billed every seven days for the life of the subscription.
`duration_days` now always carries the plan's cadence; only `end_date` is short.

The four staff-input helpers cover `"$1,234.50"` and `"1,234.50"` parsing,
rejection of zero / negative / non-numeric / injection-shaped input, the
default and operator-set ceilings, `0` meaning no limit, date parsing from both
`<input type=date>` and typed US dates, card-detail extraction from masked PANs
and from responses that carry none of the optional fields, and description
truncation that cuts on a character rather than through a multi-byte one.

The schedule cases cover a monthly and a yearly cadence, no date given, a term
end pulled forward to meet an earlier charge date, one already past it being
left alone, auto-renew off clearing the date without touching the term, and the
dunning counters being reset on every reschedule.

All five generated billing queries parse as MySQL and their `?` counts match
their bindings, including the multi-term search; so do all ten `ALTER`
statements across the standalone migration and the phpMyAdmin bundle.

### Not done — flagged

- **A customer still cannot replace their own card.** The dunning email says
  "make sure your card on file is valid from your account", and no page lets
  them: the only vaulting path is a fresh checkout. Staff can now do it for
  them, which unblocks today's problem, but the self-serve half is still
  missing.
- **The renewal amount cannot be overridden per customer.** It comes from the
  plan or its term, so a founding-member rate lock would break the first time
  the plan's price changes. That needs the Rate Lock Policy that is still open
  on the tracker, not a code change on its own.

### Key files

`application/controllers/Admin.php` (the billing screens + `billing_manual_charge_max`),
`application/models/Adminmodel.php` (the billing queries and writes),
`application/models/Usermodel.php` (`renewalAmountFor`),
`application/controllers/Cli.php`, `application/libraries/Helcim_lib.php`,
`application/views/admin/billing.php` (new),
`application/views/admin/billing_customer.php` (new),
`application/views/admin/header.php`, `application/views/admin/admin_setting.php`,
`database/migrations/0028_admin_billing.sql`.

---

## 2026-08-16 — Third tracker round: the fixes that only reached one of two code paths

Eighteen rows arrived on a new copy of the manager's error tracker. Every
screenshot in it is stamped **Tue Jul 21**, which predates both the 2026-08-05
and 2026-08-06 rounds — so the first job was working out what was still true.
Seven rows were already fixed and are recorded as such below rather than
touched again.

The pattern behind most of what remained: **a fix that landed on the admin path
and not on the member path.** This codebase carries two near-duplicate
implementations of almost every content operation — `Admin.php` +
`views/admin/*` and `User.php` + `views/*` — and the previous rounds repeatedly
patched one. Angel's note on the gallery row, *"This is still not fix"*, was
correct for exactly that reason.

### Fixed

- **A business owner lost every photo by adding one.** The 2026-08-06 gallery
  fix went into `Admin::edit_listing` only. `User::edit_listing` still wrote
  `json_encode()` of just the newly uploaded files and never read the stored
  column, so adding one photo replaced the gallery. It merges now and honours
  `remove_gallery` the same way the admin path does. Removal is read **outside**
  the `can_photos` gate on purpose: an owner on a downgraded plan can no longer
  add photos but must still be able to delete a published one. The plan's
  `photo_limit` is measured against what will remain after the save rather than
  against a single upload, or the cap could be passed one upload at a time.
- **The member edit form had no way to remove a gallery image.** It rendered its
  own read-only `<img>` loop; it now includes `views/admin/partials/media_field.php`,
  the same partial the admin screen uses, so the two behave identically. The
  partial gained an optional `$mf_disabled` for the freemium lock.
- **`/search` printed `<p>` and `&rsquo;` as visible text.** All nine result
  sections built their excerpt with `strip_tags()` + byte-based `substr()` +
  `htmlspecialchars()`, so a legacy entity-encoded body was never decoded and
  its ampersands were escaped a second time. They use `content_excerpt()` + `e()`
  now, like `/news` and `/blog-posts`. That also ends a mid-character
  truncation: cutting 150 **bytes** through a curly apostrophe handed
  `htmlspecialchars()` invalid UTF-8, which prints a replacement glyph on
  PHP ≥ 8.1 and drops the whole description on anything older, leaving a bare
  `...`.
- **The member write paths were still entity-encoding on the way in** — 30 call
  sites in `User.php`, the half of the 2026-08-06 change that only reached
  `Admin.php`. A business name typed `Joe's BBQ` was stored `Joe&#039;s BBQ`.
  Six views echoed `listing_name` and friends unescaped, two inside `value=""`
  attributes; storing raw is only safe once output escapes, so those are fixed
  in the same commit.
- **The admin category and sub-category lists searched only the rows already on
  screen.** The box was labelled "Search this page.." and was exactly that, so a
  record on page 3 came back as no match — which is what the tracker's
  "Ora Zimmerman → No matches" screenshot shows. Both lists use the server-side
  `?q=` contract from `/admin/all_listings` now, matching word by word;
  sub-categories also match on their parent category name.
- **"View Sub Cate" asked for the wrong page.** It passed the category id as the
  third URI segment, which `all_listing_sub_category($page = 1)` reads as the
  **page number** — so viewing category 7 showed page 7 of every sub-category.
  It passes `?category_id=` now, and that branch seeds `start_number`, which it
  never set.
- **Re-clicking the current page number left the admin panel.** Ten pagers built
  their active item as `<a href="/">`, so clicking it went to the public
  homepage and lost anything typed into the page.
- **Pricing cards read "$15 per" with nothing after it.** `billing_interval` was
  echoed raw with no guard, and the column stores the adjective `monthly` where
  the copy needs a noun. New `billing_interval_noun()` normalises it and falls
  back to **month**. Card rows also did not line up across a band — a shorter
  description raised that card's divider and button — so the cards are a
  five-row subgrid.
- **`/news-details` paragraph spacing was decided by incidental whitespace.** The
  view flattened the body to text and rebuilt the breaks with `nl2br()`, so the
  gap between two paragraphs depended on how much space the editor happened to
  leave between the tags — uneven inside a single article. New `content_html()`
  keeps the author's own `<p>` elements and `content.css` pins the rhythm. It
  drops every attribute from the whitelisted tags, which is what stops an
  imported article's inline `style="color:…"` repainting the page.
- **The homepage carried no section strip at all.** `index.php` is a
  self-contained page and simply did not have the element, so Classifieds
  appeared nowhere on `/` and the strip disagreed with every other page — the
  "items on Homepage and Admin don't match" row. It is now
  `views/partials/section_strip.php`, included by both.
- **Auto resolved *both* menu category slots to health categories.** "fitness"
  only matches "Health, Wellness & Fitness", so the strip read that beside
  "Healthcare" where Sports was expected. The token is gone and the two slots
  can no longer resolve to the same category.
- **`.hom-nav` hung out of the header bar on mobile.** `style.css` clamps
  `.hom-top` to `height: 55px` below 993px while leaving the row a floated
  stack, and the 90px logo is 54px tall. The row is laid out rather than
  clamped, and the logo is capped by **height** — both marks are ~1.67:1, so a
  width cap never bounded the height.
- **FAQ item 10's "Contact Us" was `href="#"`.**
- **The `/news` "See packages" house ad** linked to `/post-your-ads`, a lead form
  with no packages on it.
- **Social links sent visitors back into the site.** The footer icons were fixed
  earlier by `external_url()`; the per-business buttons on `/listing-details`
  never got it, so a schemeless `www.facebook.com/x` was emitted as a relative
  href. A sold ad's click-through had the same defect plus an empty `ad_link`
  falling to `href="#"`.
- **The admin header printed the literal "Admin User"** — a theme placeholder on
  all ~104 admin screens — so renaming the account changed nothing. Renaming
  yourself in sub-admins also never refreshed the session, which would have
  re-created the same symptom one layer down.
- **`/pricing-plan#talk-to-us`** had ~170px of blank navy under the email link.
- **`/search` never linked `promo-cards.css`,** so its sidebar house card
  rendered unstyled.

### Added

- **`content_html()`** — the markup-preserving sibling of `content_text()`, for
  views that render a whole article rather than summarise it. Structural tags
  only, every attribute dropped bar `href`/`title` on a link. Used by
  `/news-details` and `/services/<slug>`.
- **`author_name()`** — one definition of the creator-name fallback. Seeded rows
  carry `user_id = 0`, so the same event read "Hosted by Pearland411" on the
  site and blank in the admin table.
- **`image_guidance()` and on-screen upload sizes**, which answers the tracker's
  "what is the optimal image size?". The directory card crops to **16:9** and a
  gallery tile to **4:3**, both `object-fit: cover`, so a square logo loses its
  top and bottom. Recommended **1200 × 675** for the logo and **1600 × 1200**
  for gallery photos — those boxes at 2x. Shown on all four listing upload
  forms.
- **Category header banners, finishing the banner row.** `0024` had already
  built the *sold* half (`ads.category_id`, the `top-banner-category` zone).
  Three gaps remained and are closed: the admin **edit** screen never got the
  targeting field, so a targeted ad could be created and never changed; the
  bookings list did not show what an ad was targeted at; and `/listing-details`
  passed no category, so `getAdForSlot()` fell into its untargeted branch and
  actively filtered a category's own banner **out** of its listings' pages. The
  *editorial* half is new: `listing_categories.category_banner`, uploaded in
  Business Listings and rendered as the hero of `/all_listing/<slug>`, where
  every category used to draw the same photograph.
- **`cli decode_content_entities` covers `listings` and `user`,** and its guard
  is per table rather than per run — one global flag meant a table added later
  could never run on a site that had already done the first six, while clearing
  it would decode those six twice and unwrap their literal `&amp;`.

### Security

- Six views echoed public-entered listing fields unescaped, two inside
  attributes; ten admin lists echoed `user.name` raw. All now go through
  `e()` / `e_attr()`. This had to land with the input-encoding removal above,
  not after it.
- `content_html()` strips `<script>`/`<style>` **and their contents** before
  whitelisting, unwraps a link whose href fails `external_url()`, and keeps tags
  balanced — verified against attribute-XSS, `javascript:` hrefs and orphan tags.

### Already fixed — verified, not touched

The clipped sponsored banner, the rows-per-page dropdown, live form validation,
`asset_css()` cache-busting, the "Request a custom quote" destination,
word-by-word search matching, and the settings-driven top-menu slots were all
confirmed working in current code. The tracker rows describing them are stale.

### Not changed — flagged for the operator

- **`uploadAndResize()` does not resize.** Its width/height lines are commented
  out, so CI's `Image_lib` copies the file at its original dimensions and every
  upload is stored full size. Turning it on rewrites what is written for every
  image on the site, so it is called out rather than bundled into a bug-fix
  round.
- **Homepage copy** (hero headline, popular searches) is still hard-coded. Making
  it editable needs a page editor, which is a feature rather than a fix.
- **17 unreferenced views** remain in the tree, including `all-listing2.php` and
  the `add_listing_step_*` set.

### Migrations

`database/migrations/0027_category_header_banner.sql` — adds
`listing_categories.category_banner`. NULL means "use the standard directory
header", so there is no seed, no backfill, and every existing category is
unchanged. Mirrored into `database/migrations_combined_phpmyadmin.sql` in that
bundle's inline-PREPARE style and added to `database/README.md`.

**Also run** (no database was reachable from the build environment):

```bash
php index.php cli decode_content_entities          # report
php index.php cli decode_content_entities apply    # write
```

### Key files

`application/controllers/User.php`, `Admin.php`, `Cli.php`,
`application/models/Adminmodel.php`, `application/helpers/view_helper.php`
(`content_html`, `author_name`, `image_guidance`, `billing_interval_noun`),
`application/views/partials/section_strip.php` (new),
`views/admin/partials/media_field.php`, `edit_listings.php`, `search.php`,
`news_details.php`, `listing_details.php`, `all-listing.php`, `index.php`,
`header.php`, `pricing_plan.php`, `partials/ad_slot.php`,
`partials/sales_help.php`, `faq.php`, the admin category and ad views, and
`assets/css/{content,site-header,home-sections}.css`.

---

## 2026-08-15 — Enquiries go to a department, and package leads stop hiding in the contact pile

Two problems reported against the callback panel on `/pricing-plan` ("Would you
rather we set it up for you?"), which turned out to be the same problem seen
from two sides: **nothing about a message said where it was supposed to go.**

### Fixed

- **A package callback no longer arrives looking like a contact-form message.**
  The callback panel and `/contact-us` both post to `/contact-submit`, and both
  wrote `enquiry_type = 'contact'`. The only thing telling them apart was the
  free-text `enquiry_source` string, and nothing filtered on it — so somebody
  leaving a phone number to **buy a package** landed in the same undifferentiated
  pile as a general website question, in a list that was neither sorted nor
  filtered, and could sit there unread. Package leads now file as
  `enquiry_type = 'package'` and get their own tab at `/admin/enquiries`.
  The panel carries the type in a `topic` field, so it stays right wherever the
  partial is included; the server whitelists it and falls back to the page it
  posted from.
- **The enquiry list is newest-first.** `Adminmodel::getEnquiries()` was an
  unordered, unfiltered `SELECT *`, so the message that just came in could
  appear anywhere in the table.
- **The "Page Name" link no longer breaks on a contact enquiry.** Both enquiry
  views computed the back-link with two variables declared outside the loop and
  never reset: once one listing enquiry had rendered, every later row inherited
  its link, and rows with no id column produced `admin//` plus a PHP notice for
  reading an empty property name. The link is now derived per row and rendered
  as plain text when there is nothing to link to.

### Security

- **`admin/saved_enquiries.php` echoed every field raw.** Enquiry names and
  message bodies are typed by the public and stored unescaped by design
  ("escape on output"), so a submitted `<script>` tag ran inside the admin
  panel. Now escaped through `e()` / `e_nl2br()`, matching `all_enquiry.php`.

### Changed

- **The forms ask for a department, not a person.** The dropdown named nine
  individual sales reps; most no longer work here, so a caller could ask by name
  for somebody who left and the enquiry then waited on a person who was never
  going to pick it up. It offers **Sales Department, Customer Service, Billing
  Department, Advertising & Marketing, Technical Support** instead — the first is
  the default and is what the panel preselects. A department outlives whoever
  staffs it, which is the point of routing to one. The same list replaces the
  named reps on `/register` ("Did someone here help you sign up?").
- **The alert email says who it is for.** Subject and body carry the department
  and whether it is a package callback or a contact message, and a department
  with a mailbox of its own receives it directly — otherwise it falls through
  `form_recipient_email()` to the site-wide address exactly as before, so
  leaving the mailboxes blank changes nothing.

### Added

- **`enquiry_departments()`, `default_enquiry_department()`,
  `normalise_enquiry_department()`, `enquiry_department_email()`** in
  `helpers/view_helper.php`, replacing `sales_representatives()`. The posted
  department is matched back against the list, so the column only ever holds a
  department that exists — and an unknown value falls back to the default rather
  than being rejected, because an enquiry is never worth losing over a dropdown.
- **`/admin/setting` → Enquiry routing** — the department list (one per line;
  blank uses the built-in list) and optional `Department = address` mailboxes.
  The old rep list claimed in its docblock to be editable at `/admin/setting`
  and was not: `sales_representatives` was never in the settings whitelist, so
  the only way to change it was a direct SQL insert. These two keys are wired up
  properly.
- **Department column and filter tabs** on `/admin/enquiries` and
  `/admin/saved-enquiry`, with a count per tab.

### Database

- **`database/migrations/0026_enquiry_departments.sql`** — adds
  `enquiries.enquiry_department` and an index on `enquiry_type`; reclassifies the
  callback requests already in the table from `contact` to `package` and
  backfills their department, so the new tabs are not empty on day one; and
  **deletes the `sales_representatives` setting**, which is where the departed
  staff names live on production — they are not in the code, the code's fallback
  was being overridden by that row. `enquiries` predates this repo's migrations,
  so every step is guarded on the table existing. Idempotent. Mirrored into
  `database/migrations_combined_phpmyadmin.sql`.

  The text of past enquiries is **not** rewritten — a message that says
  "Asked for: <name>" stays as it was sent, because it is a record of what
  somebody actually wrote.

### Key files

`application/helpers/view_helper.php`, `application/views/partials/sales_help.php`,
`application/controllers/User.php` (`contact_submit`), `application/controllers/Admin.php`
(`enquiries`, `admin_setting`), `application/models/Adminmodel.php`,
`application/views/admin/all_enquiry.php`, `application/views/admin/saved_enquiries.php`,
`application/views/admin/admin_setting.php`, `application/views/register.php`,
`database/migrations/0026_enquiry_departments.sql`.

---

## 2026-08-06 — Fewer, better ad positions, and money moved off dead ones

Advertising had been added to every public page. Reviewing it, seven
placements on a full page was one or two too many and in several places
plainly wrong. **204 placements → 156, across 28 pages instead of 34**, losing
nothing sellable.

### Changed

- **Six pages carry no advertising at all now.** `/login` and `/register` —
  someone typing a password should not be looking at an ad; it reads as less
  trustworthy and the traffic is repeat users who never click one.
  `/privacy-policy` and `/terms-of-use` — the pages a payment processor, an app
  reviewer or a cautious customer opens to check the site is legitimate.
  `/pricing-plan` — three house ads pitching ad packages on the page that sells
  ad packages is circular. `/claim-listing` — a business owner proving they own
  a listing, not a browsing page.
- **The footer strip is gone, site-wide.** It sat directly under
  `bottom-banner-site-wide`, so every page ended in a 900x100 stacked on a
  970x90 with the call-to-action band under that. And `footer-banner` is a 0006
  zone whose slot_key matches no `plans.slug`, so it was inventory nobody could
  buy: 33 pages of house ad earning nothing.

### Added

- **A house-ad budget: `ads_house_max_per_page`, default 2.** Every unsold zone
  used to fall back to a house creative, so a site with no advertisers rendered
  as 100% house ads on every page — which reads as "nobody advertises here",
  not as premium inventory. Past the budget an unsold zone renders nothing at
  all. Includes run in page order, so the budget spends itself on the positions
  nearest the top. `0` shows none, `-1` restores the old behaviour. Editable at
  `/admin/setting` → Advertising.

  **A sold ad is never affected.** The cap governs house creatives only; an
  advertiser who paid for a position keeps it.
- **`User::thinPageAdCap()`** — a page whose search or category comes back
  empty allows one house ad, not two. "No businesses in this category yet"
  beside a leaderboard, a sidebar box and a skyscraper is advertising
  outnumbering content, and that empty category page is what was reported.
  Wired into `/all-listing`, `/all-category`, `/search` and `/coupons`.
- **The unsold top banner is hidden below 768px.** Local-directory traffic is
  mostly phones, and the leaderboard plus its "Sponsored" label consumed the
  first screen before a single business appeared. Only the house version
  hides — a paid banner runs on every device, which is what was bought.

### Database

- **`database/ad_zone_audit.sql`** — read-only, four SELECTs, safe on
  production. Shows which positions render, **how much money is already sitting
  on positions that render nowhere**, which bookings are stuck awaiting
  approval or expired, and which packages have no zone behind them.
- **`database/migrations/0025_ad_zone_reconciliation.sql`** — repoints bookings
  from the pre-0006 hand-made zones (`Above Footer Left`,
  `Feature Business Right 1`, `Home Page Below Slider left`, …) onto the
  equivalent live zone, then deactivates those rows so nothing new can be
  booked against them. The six `Usermodel::get*Ads()` methods that once drew
  them are dead code no view calls, so an ad booked against one was paid for,
  dated, approved and rendered on zero pages.

  Nothing is deleted: the rows stay, and each booking keeps its own
  `ad_cost_per_day` / `ad_total_cost`, so billing history is untouched. Also
  retires `footer-banner`, backfills any missing `size`, and seeds the house-ad
  cap. Idempotent. Mirrored into the phpMyAdmin bundle.

### Not done — needs your prices

Category targeting has worked in the admin booking form since 0024, but
`/pricing-plan` does not offer it, so no customer knows it exists. That is the
change most likely to raise revenue — the constraint is demand, not supply;
156 placements earn nothing until someone buys — but it needs a per-category
package and a price, which are yours to set.

### Key files

`application/views/partials/ad_slot.php` (house-ad budget, mobile rule),
`application/controllers/User.php` (`thinPageAdCap()`, six methods stripped),
`application/controllers/Admin.php` + `views/admin/admin_setting.php` (the new
setting), `application/views/footer.php`, six public views,
`database/ad_zone_audit.sql`, `database/migrations/0025_*`,
`scripts/ad_inventory.py`.

### Verified

The reconciliation logic proven on a fixture mirroring the reported table —
legacy rows, canonical rows and bookings across both: seven mapped bookings
move, the unmapped one is kept rather than silently lost, every dead zone ends
inactive, no row or booking is deleted, the billing total is unchanged, and a
second run changes nothing. Both SQL files parse as MySQL. The partial's suite
gains nine cases for the budget: the default renders exactly 2, the budget goes
to the top of the page, the fifth zone renders nothing, the setting raises and
lowers it, `0` and `-1` behave, a thin page overrides to 1, and **a paid ad
still renders past an exhausted budget without consuming it**.

---

## 2026-08-06 — Banner artwork drawn at each zone's exact size

Reported as "banner not showing properly": the navy top banner rendered as
"D PLACEMENT … red business placement available" with its call-to-action
button sliced in half.

### Fixed

- **The creative was cropped, badly.** `ad_slot.php` picked artwork by aspect
  ratio from four fixed shapes, and `top-banner-site-wide` is 700x100 — a 7:1
  box — while the "strip" shape is 970x90, 10.8:1. `object-fit: cover` scaled
  the artwork to the box height and threw away the 35% of width that didn't
  fit, 17% off each side. The same crop hit `middle-banner-category` and
  `bottom-banner-site-wide` more mildly, and the two hand-authored service
  creatives had a comment explaining which parts of them survived it.

  Creatives are now generated **per creative per catalogue size** — 42 files —
  so the artwork's viewBox equals the zone's box and cropping is arithmetically
  impossible. The four shape names remain, at their own dimensions, for a zone
  whose size isn't in the catalogue.
- **`object-fit: cover` → `contain`.** With exact-size artwork the two are
  identical, but an advertiser who uploads a banner a few pixels off its zone
  now sees a hairline of background rather than losing the end of their phone
  number.
- **The pear mark was clipped on short banners.** It was scaled about the
  origin, but its stem and leaf reach to y −80 while the body stops at +47, so
  a third of the glyph hung above the point it was placed at. Marks are fitted
  and centred on their true bounding box now.
- **Two identical green banners stacked at the foot of every page.**
  `footer-banner` had no entry in the house-creative map, so it fell through to
  the same green default as `bottom-banner-site-wide` directly above it — the
  exact repetition that map exists to prevent. The footer takes the
  web-services creative.

### Changed

- **The creatives are laid out from (width, height), and the text is
  measured.** Every headline is fitted to the room actually left after the mark
  and the button, against DejaVu Sans metrics — an `<img>` renders its SVG in
  isolation and can't reach the page's Poppins, so what draws is a system
  fallback, and DejaVu is the widest of them. The stacked box and skyscraper
  layouts now treat height as a budget, shedding the supporting line and then
  the price before shrinking type: the 300x250 sidebar creative was drawing its
  button at y 245 in a 250-tall box, so it showed an offer with no way to act
  on it.
- Call-to-action pills carry a chevron and size themselves to their own label,
  with the chevron given its own room instead of being carved out of the
  right-hand padding.

### Key files

`scripts/generate_brand_art.py` (section 2 rewritten: parametric, measured,
exact-size), `application/views/partials/ad_slot.php` (exact-size lookup,
`contain`, footer palette), `assets/images/ads/*.svg` (regenerated; 42 new
exact-size files).

### Verified

Every zone rendered in Chromium through the partial's real CSS at its real box:
no image's rendered ratio differs from its artwork's, so nothing is cropped or
letterboxed. The partial's test suite additionally asserts that each of the 42
exact-size creatives exists and carries the matching viewBox, and that an
unknown size (640x160) still falls back to a shape bucket rather than a 404.

---

## 2026-08-06 — Advertising on every public page, and the reasons it wasn't

Reported as "no ads banner showing on any page". Seventeen public pages carried
no ad zone at all, and three separate faults in the admin made a booked ad
render nowhere. Both ends are fixed.

### Added

- **Ad zones on the seventeen public pages that had none** — `/search`, `/faq`,
  `/contact-us`, `/privacy-policy`, `/terms-of-use`, `/all-products`,
  `/product-details`, `/classifieds-details`, `/house-details`,
  `/rental-details`, `/pricing-plan`, `/services`, `/services/<slug>`,
  `/request-quote`, `/claim-listing`, `/post-your-ads`, `/login` and
  `/register`. `/coupons/<id>` had one zone and now has six; `/about-us` gains
  its in-content banner; `/coupons` renders the skyscraper its controller was
  already priming and no view ever drew.

  **34 public pages, 204 placements.** Nothing new was invented to sell: every
  zone reuses a `slot_key` that is already a `plans.slug` on `/pricing-plan`,
  so a package a customer bought months ago now runs on thirty pages instead
  of one. "Site wide" finally means site wide.

- **`User::pageAdSlots($shape)`** — one place that declares a page's zones and
  primes them in a single query. Three shapes: `full` (main + sidebar, six
  zones), `wide` (full-width, three), `banner` (a form, two). Without priming,
  a six-zone page fires six `ORDER BY RAND()` queries.

- **A generated preview for every ad zone** —
  `assets/images/ads/previews/<slot_key>.svg`, a page wireframe with the
  position highlighted where it actually sits and labelled with its pixel size.
  Written by `scripts/generate_brand_art.py`; resolved by the new
  `ad_zone_preview()` helper, which prefers a real upload, falls back to the
  zone's drawing, then to one keyed by size, then to a generic. It refuses a
  stored path containing `..` or a scheme, and falls through when the file an
  `img` column names is no longer on disk.

- **`docs/AD_INVENTORY.md` + `docs/images/ad-layout-*.svg`**, generated by
  `scripts/ad_inventory.py` from the controller and the views, so the inventory
  can't drift from what the pages render. Re-run it after moving a zone.

### Fixed

- **The admin "Ads Preview" column was a row of broken images.** It printed
  `base_url($row->img)` unconditionally, and only the handful of pricing rows
  created by hand before the slot_key migrations ever had an `img` — so for
  every zone seeded by 0006 / 0017 / 0019 / 0024 the browser was asked to load
  the site root as an image.
- **`Admin::create_ads_price()` inserted an undefined variable.**
  `'img' => $uploaded_image_path` — never assigned — plus
  `$info['width']`/`$info['height']`, removed from the array three lines above.
  It also never stored `size` or `slot_key`, so a zone created in admin had no
  pixel box and no position code: nothing could render it and nothing could
  reserve space for it. Both are saved now, `size` composed from the width and
  height the form already collected.
- **`Admin::ads_price_edit()` opened with two empty, required boxes.** Width
  and height bound to `$price->width` / `$price->height`, columns that do not
  exist; the column is `size`. It is parsed back into the two fields now. The
  method also deleted the existing artwork *before* the replacement upload was
  accepted, so a rejected file left the zone with no image at all — upload
  first, unlink second. A missing id was a fatal error on `->img` rather than a
  redirect.
- **The booking form let you buy inventory that renders nowhere.** The zone
  dropdown listed every `ads_prices` row, including the legacy ones with no
  `slot_key` — an ad booked against one is paid for, approved, dated, and
  appears on zero pages with nothing anywhere to say why. Those are now grouped
  under "Not on any page — fix the zone first" and disabled. The zone an
  existing booking already points at stays selectable, so re-saving can't move
  it.
- **`Admin::approveAdStatus()` could only ever approve.** It hard-coded
  `ad_status = 1` and ignored the posted value, so the "Active" button — which
  posts `0` — re-approved the ad instead of taking it down. There was no way to
  pull a live ad short of deleting it.
- **`/all-products` advertised ThemeForest with two broken images.**
  `<img src="images/ads/ads1.jpg">` — no `assets/` prefix, so a 404 — inside a
  link to the template's sales page. Replaced with three real zones.
- Empty `ad_photo` in the admin ad lists rendered the same broken-image icon;
  those cells say "No artwork" now.

### Key files

`application/controllers/User.php` (`pageAdSlots()`, 21 call sites),
`application/controllers/Admin.php` (`adsSizeString()`, `adsSlotKey()`,
`create_ads_price`, `ads_price_edit`, `approveAdStatus`),
`application/helpers/view_helper.php` (`ad_zone_preview()`),
`application/views/admin/{ads_price,ads_price_edit,create_ads_price,create_ads,ads_edit,ads_history,ads_request}.php`,
21 public views, `assets/admin/css/admin-ui.css`,
`scripts/generate_brand_art.py`, `scripts/ad_inventory.py`.

**No migration.** Every zone reuses a `slot_key` the existing seeds already
carry, and the previews resolve at render time rather than being written into
`ads_prices.img` — which keeps that column meaning "artwork an operator
uploaded" and means re-running the art generator updates all of them at once.

### Verified

`ad_slot.php` exercised in isolation against a stubbed model: a live booking
renders the advertiser's creative with `rel="nofollow sponsored"` and the right
reserved box; each unsold zone falls to the house creative mapped to it, in the
shape bucket its size implies, and every one of those files exists; the CSS is
emitted once per request, not once per include; `slot_fallback = false` and
`service_ads_enabled = 0` both render nothing; and no per-include option
(`slot_creative`, `slot_fallback`) leaks into the next include on the page.
`ad_zone_preview()` checked across all four resolution steps plus traversal and
absolute-URL inputs. A cross-check confirms every `$ad_slots` key a view reads
is published by every controller method that loads it, and that no method
primes a zone its views never render.

---

## 2026-08-06 — Second tracker round: media, forms, line breaks, menu

Four reports after the first pass went live.

### Fixed

- **The listing edit form never showed its logo.** It read
  `$listing->listing_profile_image` — the name of the file *input*, not a
  column; the column is `listing_image` — and prepended
  `assets/images/listings/` to a path that already contained it. News, blog,
  video and event edits showed nothing either: a bare file input and no sight
  of what was already stored.
- **Doubled line breaks in article bodies.** `content_text()` turns block tags
  into newlines, but CKEditor already separates its tags with real ones, so a
  paragraph break arrived as `"\n\r\n\r\n"`. There was a collapse rule for
  this — `\n{3,}` — and the stray `\r` broke the run, so it never matched.
  Line endings are normalised first now. The Picos article goes from 12 `<br>`
  to 6.
- **"Sports" in the top menu pointed at Travel and Recreation.** The slot
  resolved by keyword from `('sport', 'recreation')`; no sports category held
  published listings, so it fell through to "recreation", which matches the
  travel category. Keyword matching is whole-word now and "recreation" is gone
  from that list — but the real fix is that both slots are settings.
- **Six phone boxes were `type="text"`**, which accepts anything.

### Added

- **`application/views/admin/partials/media_field.php`** — one image field,
  rendered the same way on every edit screen: the stored image beneath the
  input, opening full size in a lightbox, with a Remove tickbox per image for
  galleries. Ticking one greys it out, so the effect of saving is visible
  before you save.
- **`assets/admin/js/media-preview.js`** + `media-preview.css` — markup-driven,
  so a page opts in with attributes: `data-preview` (thumbnails of the files
  just chosen, marked not-yet-saved), `data-lightbox`, and
  `data-video-preview` (parses YouTube URLs as you type, renders a player for
  each, and names anything that is not a YouTube link rather than dropping it).
- **`assets/js/form-validate.js`** — live validation for any
  `<form data-validate>`: checks each field as it is edited, writes the reason
  under the control, blocks submit while anything is invalid and focuses the
  first offender. Rules come from the markup (`required`, `type`, `minlength`,
  `maxlength`, `pattern`, `data-match`), so there is no per-form script to keep
  in step. It listens in the **capture** phase, because the directory enquiry
  form posts over AJAX in its own handler that would otherwise fire first.
  Applied to the directory enquiry form, login, register, contact,
  request-a-quote and nine detail-page enquiry forms.
- **Two "Top menu categories" settings.** Auto keeps the keyword search, Hide
  drops the item, or pick any category by name. The link text follows the
  category, so pinning a different one does not leave the old word in the menu.
- **An "All form submissions to" setting**, read through
  `form_recipient_email()`. Every form reached for `env('MAIL_FROM_ADDRESS')`
  independently, so changing who receives enquiries meant a deploy and a search
  through four controllers. Order: the form's own override, then this setting,
  then `contact_email`, then `MAIL_FROM_ADDRESS` — first valid address wins.

### Verified

Browser-tested against the real stylesheets. Validation: letters in a phone
field, a malformed email, a short password and mismatched passwords each block
submission with the right message on the right field; an empty optional phone
passes; what was typed survives a blocked submit. Media: two valid YouTube
links render two players and a junk third entry is reported, the lightbox opens
on click and closes on Escape, and ticking Remove greys the right cell and
leaves its neighbour alone. `content_text()` was exercised across CRLF, LF,
headings, `<br>`, lists and legacy entity-encoded bodies.

### Also added

**A rows-per-page dropdown on every admin table** — 10, 20, 30, 50 or Show all,
in the toolbar above each list, with the pagination following it. The choice is
remembered in the session, so moving between admin screens keeps the size you
picked instead of snapping back to ten.

**Default 20**, not ten — ten was small enough that every list needed paging
through.

There were two pagers to reconcile: 20 controllers paginate server-side through
CodeIgniter, and 41 views page in the browser through `admin-table.js`. Both now
read one value. `admin_per_page($default)` resolves it — the operator's choice
wins, otherwise the page's own default, because the category lists deliberately
serve 100 so their inline search covers the whole list. The value is validated
against the option list before it goes anywhere near a `LIMIT`.

Two things that would have made this subtly wrong: the pager links needed
`reuse_query_string` or clicking page 2 dropped the size back to the default;
and CodeIgniter puts the page number in the URL **path**, not the query string,
so changing size while on `/admin/all_listings/20` had to strip that segment or
it would apply the old offset to the new page size.

Two defects stopped the dropdown doing anything at all, and neither was
visible without opening the page.

**select2 was eating the control.** `views/admin/footer.php` runs
`$('select:not(.admin-table-size):not(.no-select2)').select2(…)` — and the
opt-out class sat on the `<label>`, not the `<select>`. So select2 replaced the
dropdown with its own widget (which is why the arrow looked wrong) and fired
jQuery events, and a jQuery `.trigger('change')` never reaches a native
`addEventListener('change')` handler. Picking a size did nothing. The select
carries both opt-out classes now, and the handler is bound natively *and*
through jQuery so a future wrapper cannot break it again.

**`per_page` is CodeIgniter's own reserved name.** `CI_Pagination` uses it as
its query-string segment (`Pagination.php:268`) and, with `reuse_query_string`
on, explicitly `unset()`s it from every link it builds (line 435) — so even
once the dropdown worked, turning a page would have dropped the size. The
parameter is `?show=` instead.

Two follow-ups after the first cut. `/admin/all_listings` showed no dropdown at
all: that table had `data-admin-table` removed when its server-side search
landed, so nothing rendered the toolbar. It opts back in with
`data-server-search`, which draws the dropdown but not a second search box. And
the category list *displayed* "10 per page" while showing all 23 rows — with no
explicit choice the published value was `''`, no `<option>` matched, and the
browser fell back to showing the first one. The footer publishes the effective
size now, so the control always states what you are actually looking at.

**Admin buttons are one size.** `admin-style.css` styles
`.ud-cen-s2 a.db-tit-btn` — an *anchor* — so a `<button>` with the same class
matched none of it and fell through to the theme's generic rule, which is why
Search rendered as a large pill beside anchors half its height. New
`assets/admin/css/admin-ui.css` covers both forms at 32px, and sets
`box-sizing` explicitly: the theme gives `<button>` border-box but leaves `<a>`
on content-box, so an identical `min-height` plus padding still produced a 42px
anchor next to a 32px button. It also tidies the toolbar's search box and
select, and puts the primary "Add new …" action in green.

"All" is capped at 5,000 rows rather than being unbounded — CI's pagination
divides by `per_page` to count pages, so NULL would be a division by zero, and a
browser handed 50,000 `<tr>`s stops responding.

### Also fixed

**Special characters, everywhere else.** The first pass covered News, Blogs and
Videos. Products, Classifieds, Marketplace, Ads and Sliders were still running
every text field through `htmlentities()` on the way in — 79 call sites — so an
apostrophe stored as `&#039;` and came back that way in the edit box, because
`set_value()` escapes on output and a value that is already encoded gets
encoded twice. Four **slugs** were encoded too, which is worse than cosmetic.
Output already escaped (`e()` / `htmlspecialchars()` on titles), so storing raw
is what those views expect; the two description views moved onto the same
`nl2br(e(content_text(...)))` convention as news and blogs.
`cli decode_content_entities` now covers those tables as well, taking titles,
tags and SEO fields rather than just the body.

### Not changed

`edit_product.php`'s `gallery_image[]` — no admin controller reads that field,
so there is no column to show current images from. Wiring it up is a separate
change from making the existing ones visible.

The **featured icon** (About Us). Rendered against the real stylesheets it
measures 5.39px above the icon against 5.41px below, and the `stars` ligature
is present in `assets/css/icon.woff2`, so it draws as a glyph rather than the
letters of the word. Waiting on a screenshot of what was actually seen.

### Key files

`application/views/admin/partials/media_field.php` (new),
`assets/admin/js/media-preview.js` + `assets/admin/css/media-preview.css` (new),
`assets/js/form-validate.js` (new), `application/helpers/view_helper.php`
(`content_text()`), `application/helpers/site_setting_helper.php`
(`form_recipient_email()`), `application/models/Usermodel.php`
(`findCategoryByKeywords()`, `getCategoryBySlug()`), `application/views/header.php`,
`application/views/admin/admin_setting.php`, and the admin edit + public form views.

No migrations.

---

## 2026-08-05 — The manager's error tracker, worked through end to end

47 findings from `PEARLAND411_PROJECT_TRACKER.pdf`, recorded by Angel and
Monette over the life of the project. Every row was re-verified against the
code rather than taken from the tracker's own STATUS column, which turned out
to be wrong in both directions: row 9 is marked **Fixed** and was never
possible, and rows 29/40 are marked open but had been fixed and simply not
deployed.

### Fixed — data loss and launch blockers

- **A new business listing could not be submitted** (row 44). Three independent
  causes. The Featured and Spotlight checkboxes carried `required` while
  sitting on step 1, which the multistep JS hides by the time you reach Submit
  on step 3 — unticking either made the browser abort the submit and say
  nothing, because it cannot focus an invalid control inside a `display:none`
  container. The save path was gated on a non-empty `user_id`, so a submit
  without one fell through to a blank re-render. And a rejected INSERT
  redirected with no flashdata, which with `db_debug` off is indistinguishable
  from a dead button.
- **Deleting one duplicate deleted the original too** (row 45). Both delete
  paths keyed on `listing_slug`, which is not unique — the slug is derived from
  the business name with no uniqueness pass. Permanent delete was worse: it
  removed every row sharing the slug and unlinked the image they had in common.
  Deletes and restore key on `listing_id` now, and new listings go through the
  `makeUniqueListingSlug()` that until now only the CSV importer used.
- **Restore returned HTTP 500** (row 46). It wrote `listings.deleted_date`, a
  column that appeared exactly once in the whole application — on that line.
  Migration `0022` creates it; the soft delete sets it and restore clears it.
- **Adding a gallery image wiped the gallery** (row 20) and **there was no way
  to remove one** (row 19). The update path never read the stored JSON before
  overwriting it. It merges now, and the edit form grew a Remove tickbox per
  image.
- **Multi-image upload saved one image** (row 43). CI3's loader ignores a second
  `load->library()` for a class it already holds, so `image_lib` kept the first
  file's paths for every subsequent image. All three upload helpers call
  `initialize()` per file.

### Fixed — content

- **HTML tags shown as plain text on News and Videos** (rows 26, 47). The admin
  write paths ran the editor body through `htmlentities()`, so the column held
  `&lt;p&gt;`; every read escapes again on output. Titles too, which is why an
  apostrophe surfaced as `&#039;`. Stored as typed now, like events and like the
  blog paths fixed in the previous entry. Existing rows: `php index.php cli
  decode_content_entities`.
- **"Special characters are not allowed" and "the date is not updated"**
  (rows 15, 40). One root cause: `edit_blog()` regenerated `blog_slug` from the
  title on every save and then updated `WHERE blog_slug =` the *old* slug, so
  saving once changed the post's address and the bookmarked edit URL 404'd.
  Slugs are permalinks now. The six member-side generators replaced spaces and
  nothing else, so an apostrophe or `&` went into the URL and CI3 answered *"The
  URI you submitted has disallowed characters"* — they use `pearland_slug()`.
- **The author picked in admin was discarded** (row 13). `Usermodel::add()`
  overwrote `user_id` from `userdata('logged_user')`; an admin session carries
  `logged_admin`, so it wrote NULL.
- **Renaming a video category silently did nothing** (row 25). It called
  `updatemarketplaceCategory()` — wrong table, wrong key — and CI3's `update()`
  returns TRUE whenever the query runs, so every rename reported success. The
  correct method existed and was dead code. The marketplace category edit had
  the identical defect and is fixed too.
- **"Cannot add more news / events"** (rows 12, 14). A failed save ended the
  request with no view and no redirect — a blank page. Plus colliding slugs, and
  `canUserAddContent()` treating a `0` limit as "none allowed" while every
  dashboard reads `0` as unlimited.
- **Event contact person, phone and email are optional** (row 17), as is the
  banner image, which was `required` in markup with no server rule behind it.

### Fixed — public site

- **The enquiry form accepted anything and explained nothing** (row 32).
  Server-side validation was `if (empty($name) || empty($email))`; a 10 MB
  message was inserted verbatim. Proper rules now, keyed by field, and the
  browser marks the offending box — the script read only `.success` and threw
  the message away. A failed submit keeps what was typed.
- **"Sports and Health" showed as "General"** (row 34). An unresolvable slug
  returned the literal `'General'` over an empty list, which reads as a real but
  empty section rather than a dead link.
- **Search stopped at the first handful** (row 22). `globalSearch()` declared
  `$limit`/`$offset` and ignored both, and `/search` had no pager at all.
- **Login and registration** (row 27). The server accepted a six-character
  password while the form promised eight. Real rules, a confirmation field, a
  phone format and a honeypot. A CAPTCHA is the remaining gap — see below.
- **Social links returned you to the homepage** (row 7). `site_setting()`
  returns the stored string verbatim, so `www.facebook.com/x` is a *relative*
  href. New `external_url()` adds the scheme and refuses `javascript:`.

### Fixed — presentation

- **The favicon was Google's** (row 24) — the theme's Google Maps pin, never
  touched since the initial import. `scripts/generate_favicon.py` draws the
  pear mark in brand colours; there is now a root `/favicon.ico` too.
- **Double entry on the listing page** (row 33): the "Get a quote" modal held a
  second, identical copy of the sidebar enquiry form.
- **The logo was letterboxed** (row 38), **the About "second row" sat hard
  left** (row 36), and **event cards did not line up** (row 21) — the card title
  was unclamped while the excerpt below it was clamped to three lines.
- Row 35 (Vision/Mission icon) and row 37 (spacing) adjusted by eye.

### Added

- **A listing can hold several sub-categories** (row 9). Never possible before:
  `listings.sub_category_id` is one INT and there was no join table. Migration
  `0023` adds `listing_sub_category_map`.
- **Banners can be sold per category** (row 18), and the directory has a header
  banner slot. Ads had one targeting dimension — the slot — so
  `middle-banner-category` was a single global item shown on category pages, not
  a banner sold per category. Migration `0024` adds `ads.category_id`.

### Verified

The presentation changes were rendered against the real stylesheets with
Chromium and measured, not eyeballed: the About orphan row is centred at
1440/1280/1024/768/390 with no horizontal overflow; the logo box overflows at
no breakpoint and a wide mark now uses 94% of the available height rather than
about 40%; event cards' meta lists and footers start at the same offset across
a row of deliberately uneven content. `listing_gallery_paths()`, the gallery
merge, the enquiry rules, the search limit clamp and `external_url()` were each
exercised across their edge cases.

### Not changed, deliberately

- **Row 39, the featured icon.** Rendered, it is centred — 5.39px above the icon
  against 5.41px below — and the `stars` ligature is present in
  `assets/css/icon.woff2`, so it draws as a glyph rather than the letters of the
  word. Needs a screenshot of what was actually seen.
- **A CAPTCHA on registration.** Provider is a product decision.
- Products and classifieds still store entity-encoded bodies. They are not in
  the tracker and `content_text()` decodes them on the way out; converting them
  belongs with the rest of that section's rebuild.

### Key files

`application/controllers/Admin.php`, `User.php`, `Cli.php`
(`decode_content_entities`), `application/models/Adminmodel.php`,
`Usermodel.php`, `application/helpers/view_helper.php` (`external_url()`,
`listing_gallery_paths()`), `image_helper.php`,
`application/config/form_validation.php`, `scripts/generate_favicon.py` (new),
`assets/css/directory.css`, `content.css`, and the admin and public views for
listings, events, news, videos, blogs, search, about and the directory.

Migrations: `0022_listing_deleted_date.sql`, `0023_listing_subcategory_map.sql`,
`0024_ads_category_target.sql` — all mirrored into
`migrations_combined_phpmyadmin.sql`. Plus the one-off
`php index.php cli decode_content_entities`.

---

## 2026-08-05 — Blog detail page rebuilt, and `<p>` stops showing up in the text

Two reports: the detail page rendered as a white page, and `<p>` and `&rsquo;`
were printed as visible text in the excerpts on the index.

### Fixed
- **`<p>` and `&rsquo;` shown as text.** `Admin::add_blog()` and `edit_blog()`
  saved the CKEditor body through `htmlentities()`, so the column held
  `&lt;p&gt;Why Pearland is one of America&amp;rsquo;s…` instead of the markup.
  `content_text()` then found no tags to strip and decoded once at the end,
  producing a literal `<p>` and `&rsquo;`, which the view escaped and printed.
  The public form (`User::add_blog`) never did this, so it only affected
  admin-created posts.

  Both ends are fixed. **Existing rows display correctly without a migration:**
  `content_text()` now decodes an entity-encoded body *before* the tag work.
  The signal is an encoded closing tag (`&lt;/p&gt;`) rather than a bare
  `&lt;`, so a post explaining "use the `&lt;p&gt;` tag" still shows what it
  typed. And the admin write paths store the body as typed, like the public
  form, with escaping at the point of display as everywhere else.
- Because bodies are now stored as real markup, the four places that echoed
  `blog_name` / `blog_description` unescaped were fixed — including the two
  CKEditor textareas, which need the body escaped *into* the field.

### Changed
- **`/blog-details/<slug>` is rebuilt on the shared page kit**, the same shell
  as `/news-details`: hero with breadcrumb, title, byline and read time; the
  post in a card with its figure, prose and share row; "More from the blog"
  as proper cards; a sidebar with the enquiry form, promos and ad zones.

  What it replaces: ~130 lines of its own `<style>` over the theme's
  `eve-deta-*` classes, including `margin-top:-96px !important` on the related
  posts, which dragged them up over their own images. The body was rendered
  with `strip_tags(html_entity_decode($blog->blog_description))` — every
  paragraph break in the post collapsed into a single run of text. It now goes
  through `content_text()` + `nl2br(e())` like every other detail page, so the
  paragraphs survive and nothing stored can inject markup.
- **The author query moved out of the template.** The view ran
  `$this->db->select('name')->from('user')…` inline. Resolved in
  `User::blog_details()` and passed in as `$blog_author`.

### On the white page
Rendered against the real stylesheets, the old page came up as a tall pale band
with the content pushed far below the fold and the related posts collapsed onto
their own images — consistent with "white page" on a first look. The exact
production failure could not be reproduced here without the live database, so
this is a rebuild rather than a one-line fix: the old view's dependencies on
theme classes, the negative-margin hacks and the in-template query are all
gone, and the page now renders from the same kit as the sections that work.

### Verified
The rebuilt page renders with no PHP errors and no horizontal scroll; the body
shows both paragraphs with a real apostrophe, and the page contains no literal
`<p>` or `&rsquo;`. `content_excerpt()` was checked against four storage
shapes: admin-stored (entity-encoded), user-stored (raw markup), plain text,
and a body that deliberately shows `&lt;p&gt;` — the last still displays it.

### Key files
`application/views/blog_details.php` (rebuilt), `application/helpers/view_helper.php`
(`content_text()`), `application/controllers/Admin.php` (`add_blog`, `edit_blog`),
`application/controllers/User.php` (`blog_details`), plus the four views that
echoed blog fields unescaped.

No migrations.

---

## 2026-08-05 — The blog index joins the rest of the site, and its dates become real

Three reports on `/all-blog`: it didn't look like the other pages, every post
showed the same date with no way to change it, and the date wasn't written the
way Americans write dates.

### Fixed
- **Every post showed the same date.** `Admin::add_blog()` never wrote
  `blogs.created_at` — the payload it builds has user_id, name, description,
  status, isenquiry and slug, and nothing else. (`User::add_blog()`, the public
  form, does write it, which is why only admin-created posts were affected.)
  With nothing supplied the column took its default, and the index printed
  `date('d, M Y', strtotime($blog->created_at))`. `strtotime()` returns FALSE
  for NULL, `''` and the zero date, and `date()` renders FALSE as the epoch —
  so every dateless post printed the *same* string: `01, Jan 1970`, or
  `30, Nov -0001` from a zero date. Not the same date. **No** date, printed
  identically. The admin add path now writes it, and migration
  `0021_blog_dates.sql` makes the column nullable with `DEFAULT
  CURRENT_TIMESTAMP` and turns existing zero dates into NULL.
- **No way to set a custom date.** Neither admin blog form had a date field.
  Both now carry **Publish date** (`datetime-local`): blank on the add form
  means "now"; on the edit form it holds the stored value, and a post that has
  none says so in red so it can be found and fixed. `Admin::edit_blog()` writes
  it, keeping the stored value when the field is left alone.

  The migration deliberately does **not** invent dates for existing posts —
  there is no signal in the row for what the real publication date was. NULL is
  honest, the views omit the line, and an admin sets the true date per post.

### Changed
- **`/all-blog` is rebuilt on the shared page kit** — the same shell as `/news`,
  `/event` and `/videos`: hero band with search, results toolbar, a lead card
  over a card grid with date badges, a sidebar (most-read rail, "write for us",
  ad zones) and the owner band. It was the last public content section still on
  the theme's own `blog-head`/`blog-body` markup: a bare heading over a plain
  grid with inline `padding-bottom:170px`, no hero, no sidebar, no ad slots.
- **Dates are US-formatted** via a new `us_date()` / `us_date_ts()` pair in
  `view_helper.php`. `Aug 5, 2026` rather than `05, Aug 2026`, and — the part
  that matters — `''` rather than the epoch for a value that isn't a date, so
  callers omit the line instead of printing a wrong one. Applied across all six
  blog surfaces: the index, the detail page, the user dashboard list, the admin
  list, the admin detail and the admin edit form.
- **`/blog-details/<slug>` is routed.** Only `blogs-details/(:any)` existed, so
  the singular form 404'd — CI3's catch-all `(:any)` is `[^/]+` and matches one
  segment. All three spellings now resolve, as they already do for news, videos
  and events.

### Still on the old theme
The blog **detail** page (`views/blog_details.php`, 481 lines) was not rebuilt —
only its dates were fixed. Clicking a post from the new index still lands on the
old layout.

### Verified
The rebuilt index was rendered against the real stylesheets and screenshotted:
five posts including one with a zero date, which shows **no** date rather than
1970, while the others read `Jul 28, 2026`. Links resolve to the new route, no
horizontal scroll. `us_date()` was exercised across nine inputs — real dates,
zero dates, NULL, empty, 0, a numeric timestamp and junk — alongside the old
expression, which is where the identical `01, Jan 1970` / `30, Nov -0001`
outputs above come from.

### Key files
`application/views/blog-posts.php` (rebuilt), `application/helpers/view_helper.php`
(`us_date()`, `us_date_ts()`), `application/controllers/Admin.php`
(`blogPublishDate()`, `add_blog()`, `edit_blog()`), `application/controllers/User.php`
(`all_blog()`), `application/views/admin/add_new_blogs.php`,
`application/views/admin/user_edit_blog.php`, `application/views/admin/all_blogs.php`,
`application/views/admin/blog_details.php`, `application/views/blog_details.php`,
`application/views/user_blog_posts.php`, `application/config/routes.php`.

Migration: `database/migrations/0021_blog_dates.sql` (mirrored into
`migrations_combined_phpmyadmin.sql`).

---

## 2026-08-05 — "Request a custom quote" now has somewhere to go

The button on `/services` did nothing. It pointed at `/request-service`, which
is the **POST handler** for the quote form — its first act on a GET is
`redirect('services')`. So clicking it reloaded the page you were already on
and showed no form.

Seven links across the site had the same target, all equally dead: the two on
the `/services` hero and footer CTA, the empty-category state, the "Or request
a custom quote" line in the web-dev promo (twice), the "Banner &amp; ad design"
link in the site footer, and the `banner-design` ad creative.

### Added
- **`/request-quote`** (`User::request_quote` → `views/request_quote.php`) — a
  real page on the shared content kit, same shell as `/contact-us`: name,
  email, phone, company, which service (a dropdown off the live catalog, so it
  cannot list something that has been retired), budget range and the job
  description. Signed-in members get their name and email filled in.
- It posts to the existing `request-service` handler, so it inherits everything
  already built: rate limiting (5/IP/hour), validation, storage in
  `service_requests`, and the acknowledgement email to the sender.

### Changed
- **Quote requests are emailed to `joshua@spacecentersystems.com`**, via
  `site_setting('service_quote_email', …)` so the address can be changed at
  `/admin/setting` (new "Quote requests to" field) without a deploy. It falls
  back to `MAIL_FROM_ADDRESS` only if that setting is blanked, so a request
  cannot end up going nowhere. This covers the per-service forms on
  `/services/{slug}` too — same handler, same kind of enquiry; they previously
  went to `MAIL_FROM_ADDRESS`.
- **The alert carries a Reply-To of the person asking**, so hitting Reply
  answers the customer instead of the site's no-reply box. `Mailer::send()`
  gained an optional `$opts` array for this (`reply_to`, `reply_to_name`);
  the address is validated before it is used, and every existing call is
  unaffected.
- **A failed submission no longer loses what was typed.** Validation errors
  flashed a message and redirected, discarding the form; the standalone page
  now gets the values back. `return_to` is whitelisted to the one value —
  never a posted URL.
- A GET to `/request-service` now lands on `/request-quote` rather than
  bouncing to `/services`, so old links reach the form instead of a dead end.

### Verified
The page was rendered against the real stylesheets and screenshotted at 1280px
and 390px: the form posts to `/request-service` by POST, carries all eight
fields, marks exactly the three the server requires (name, email, message), and
the layout stacks with no horizontal scroll.

### Key files
`application/views/request_quote.php` (new), `application/controllers/User.php`
(`request_quote()`, `request_service()`), `application/libraries/Mailer.php`,
`application/config/routes.php`, `application/controllers/Admin.php` +
`application/views/admin/admin_setting.php` (the new setting), and the six
views that carried a dead link.

No migrations — `service_requests` (0002_services.sql) already stores these.

---

## 2026-08-05 — Search finds "Central Texas BBQ" when you type "central bbq"

`/search?q=central+bbq` returned nothing while `/listing-details/Central-Texas-BBQ`
sat right there in the directory.

**Why.** Every search sent the whole search box to the database as one string:
`listing_name LIKE '%central bbq%'`. Both words are in that business's name —
they are just not next to each other, so the phrase never matched. Any query of
more than one word only worked if the visitor happened to type a contiguous
substring of the record.

### Fixed
- **Word-by-word matching.** `searchTerms()` splits the query; `applySearchTerms()`
  builds `(word1 in any column) AND (word2 in any column) AND …`. Each word has
  to appear somewhere in the row, but not in the same column and not adjacent.
  Applied to all twelve searches (listings, news, events, blogs, products,
  houses, rentals, classifieds, videos, categories, services, coupons) and to
  the autocomplete dropdown, which had the same defect and so could suggest
  nothing for a query the results page would answer.
- **Results are ordered by relevance, not by id.** Every search ended
  `ORDER BY <id> DESC`, so a business whose *name* matched could sit below any
  newer row that merely mentioned the word in a description. Now: exact name,
  then name starting with the query, then name containing it, then how many of
  the words are in the name — with featured listings and recency as
  tie-breakers.
- **Listings also match on `service_offered`.** A barbecue place that does not
  say "catering" in its description usually says it there.
- **Unpublished blog drafts were searchable.** `searchBlogs()` had no status
  filter, and searched the title only. It now filters `blog_status = 1` and
  searches the body. `getAllBlog()` gained the same filter — it is the browse
  half of the same page (`/all-blog`), and without it a draft would be listed
  when browsing and hidden when searching.

### Changed (speed)
- **A search no longer runs twelve full-table scans regardless.** Sections
  switched off at `/admin/setting` are skipped — those results were being
  computed and shown even though every link in them 404s via `requireService()`.
  With the default set-up that is a handful of scans saved per search; with the
  optional sections off it is most of them.
- **A query with nothing searchable in it returns immediately** instead of
  running the full set of scans to discover it matched everything.
- Words are de-duplicated and capped at six, so a pasted paragraph cannot build
  a hundred-clause query.

### Note on `searchBlogs()`'s signature
`$limit` stays optional and defaults to *no* limit. `/all-blog?q=` slices the
whole result set in PHP to paginate (`User::all_blog`), so capping it by default
would have silently truncated that page to one or two pages. The site-wide
search passes an explicit 15, like every other section.

### Verified
`searchTerms()` and the compiled SQL were checked against CodeIgniter's own
query builder offline (`get_compiled_select()`): `"central bbq"` produces
`((name LIKE '%central%' OR …) AND (name LIKE '%bbq%' OR …))`, and the hand-built
ORDER BY fragments carry their own `ESCAPE '!'` — `escape_like_str()` neutralises
`%` and `_` with a `!` prefix that means nothing to MySQL without one, so
"50% Off Tyres" would otherwise have ranked by a pattern containing a literal `!`.

### Key files
`application/models/Usermodel.php` — `searchTerms()`, `applySearchTerms()`,
`searchRelevanceOrder()` (new), `globalSearch()`, all twelve `search*()` methods,
`getSearchSuggestions()`, `getAllBlog()`.

No migrations.

---

## 2026-08-05 — Duplicate listings hidden behind a switch; "Add new listing" goes straight to the form

QA flagged that **"Create duplicate listing"** sat one click from "Create
listing from scratch" on `/admin/add_new_listings`, so a mis-click made a
duplicate business record. The directory already carries enough of those that
the homepage de-duplicates by flattened name (`User::index`).

The tool is **not deleted** — it is switched off and hidden, and can be brought
back from `/admin/setting` without a code change.

### Changed
- New `feature_duplicate_listing_enabled` setting, read through
  `duplicate_listing_enabled()` (`application/helpers/view_helper.php`). Note
  the inverted default: every `service_*_enabled` key defaults to `'1'` so an
  un-migrated site keeps working, but this one must be switched on
  deliberately, so an unset value reads as off.
- **`/admin/add_new_listings` now redirects to `admin/add_listing_scratch`.**
  That page existed only to offer the scratch/duplicate choice; with
  duplication off there is nothing to choose, so the admin lands on the form
  instead of clicking twice. Same for the singular `add_new_listing` URL. The
  three places that link to it — the admin top menu, the sidebar and the trash
  page's "Add new listing" button — all now end on the form.
- **`Admin::create_duplicate_listing()` refuses to render when the flag is
  off**, redirecting to the same form. Hiding the menu links alone would only
  mean "not linked" — the URL was still typeable. The method's body is
  untouched below the guard.
- The two menu entries ("Create duplicate listings", in `admin/header.php` and
  `admin/inc/left_sidebar.php`) are wrapped in the flag.
- `views/user_add_listing_start.php` carries a user-facing duplicate form. It
  is dead — no controller loads it (the live view is
  `add_new_listing_start.php`, which has no duplicate option) and its form
  posts to `user/duplicate-listing`, which does not exist — but its block is
  behind the same flag now, so wiring the file up later cannot quietly bring
  the feature back.

Nothing else in the app offers duplication: events, news, blogs, coupons,
products, videos, classifieds, rentals, houses and ads have no clone action.

### Key files
`application/helpers/view_helper.php` (`duplicate_listing_enabled()`),
`application/controllers/Admin.php` (`add_new_listings`, `add_new_listing`,
`create_duplicate_listing`, `admin_setting`'s `$known_keys`),
`application/views/admin/admin_setting.php` (the new Listings card),
`application/views/admin/header.php`, `application/views/admin/inc/left_sidebar.php`,
`application/views/user_add_listing_start.php`.

No migrations — `site_settings` (0007) already stores arbitrary keys.

---

## 2026-08-05 — The header fix that never reached anyone's browser

The header showed **two dropdown carets** beside "Explore" and a search box whose
green button was a hard-cornered block hanging off the end of a rounded white
field, the magnifier drawn in the theme's grey on top of the green.

Both were fixed on 2026-08-03 ("Use one Explore menu and one header search
across the site"). That commit added 87 lines to `assets/css/site-header.css`
— including `.menu::after { content: none }`, which switches off the theme's
own absolutely-positioned arrow now that the chevron is an `<svg>` in the
markup — but left `header.php` asking for `site-header.css?v=1`, the key set
two commits earlier. Every browser that had visited before kept serving the old
copy from cache. The navy bar and green rule *were* in that cached copy, which
is why the header looked half-converted rather than untouched.

Reproduced by rendering the real header markup against `git show
4530678:assets/css/site-header.css`: two carets, square green button, grey
glyph — a pixel match for the report.

### Fixed
- **`site-header.css`** — the doubled caret and the unstyled search box above.
- **`site-footer.css`** — carried the same defect. The white strip above the
  footer, reported by the client and fixed on 2026-08-03, was still on the
  `?v=2` key set the day before, so that fix had not reached a returning
  visitor either.
- **`promo-cards.css`** — changed on 2026-08-02 and bumped to `?v=2` in
  `index.php` only; the four other call sites (`/all-listing`,
  `/listing-details`, the content pages, admin's listing preview) still asked
  for `?v=1`.

### Changed
- **Stylesheet versions are no longer written by hand.** New
  `asset_css('assets/css/x.css')` in `application/helpers/view_helper.php`
  stamps the file's own `filemtime()`, so the key changes exactly when the
  bytes do. It strips any incoming `?query` (a caller still passing `?v=3` gets
  the mtime, not a doubled string) and falls back to the bare URL when the file
  is not on disk. All 9 link tags and the `page_css` loop now use it, and
  `page_css` entries in the controllers are plain paths.

  This had already been fixed once by hand, for `directory.css` on 2026-08-04.
  Four occurrences of one bug is the argument for the helper.

### Key files
`application/helpers/view_helper.php` (`asset_css()`),
`application/views/header.php`, `application/views/index.php`,
`application/views/admin/header.php`, `application/controllers/User.php`
(`contentPageCss()` + 2 `page_css` literals), `application/controllers/Admin.php`.

No CSS was edited — the stylesheets were already correct.

---

## 2026-08-05 — "You've reached your limit: ∞ Unlimited", and the free plan that asked you to register

Two dead ends in the same journey: a member with no plan clicked **Add New
Listing**, got a modal telling them they had exhausted an unlimited allowance,
followed it to `/pricing-plan`, chose the free tier — and was shown a signup
form they had no use for, because they were already signed in.

### Fixed
- **The plan-limit modal described the wrong situation when there was no plan.**
  The dashboards read the cap out of `subscription_limits`, which is `null`
  without a subscription, so `$limit` fell back to `-1` — the sentinel for
  *unlimited*. `$limit_reached` was separately true because
  `!$has_subscription`. The modal therefore rendered both halves at once:
  "You've reached your current plan's Listings limit" over "Usage: ∞ Unlimited".
  Nothing was unlimited; the member simply had no plan. The modal now tells the
  two states apart — *Choose a plan* / "You don't have an active plan yet"
  when there is no subscription, *Upgrade your plan* / `used / limit` when a
  real cap is full — and the usage line is printed only when there is a
  countable cap to print.
- **The free plan's button sent signed-in members to `/register`.** The pricing
  card chose its link from `$user_subscription`, which is empty both for a
  guest *and* for a logged-in member who has not bought anything, so the second
  group was bounced to a signup form. Free plans are excluded from the cart and
  checkout (`User::cart_add`, `buy_now`), so there was no other route to one.
  The card now branches on being signed in: guests still get `/register`;
  someone already on a plan gets a link to their listings (the free tier would
  be a downgrade); a signed-in member with no plan gets a POST button that
  activates the plan and lands them on their dashboard with the allowance live.
- **Flash messages on `/pricing-plan` had nowhere to render.** `buy_now()`
  already redirected there with `flashdata('error')` and the page never printed
  it. It now renders server-side notices in the existing `.pp-err` area.

### Changed
- The upgrade modal was copy-pasted into **11 dashboard views** with two
  divergent variants of the usage line. It is now one partial,
  `views/partials/plan_limit_modal.php`, included by all 11 — the fix above
  applies everywhere rather than only on the page it was reported from.
- `Usermodel::activateFreePlan()` gives free subscriptions an open-ended term
  rather than the plan's `duration_days`. Renewal is card-driven
  (`getRenewableSubscriptions()` only picks up `auto_renew` rows with a
  `next_billing_date`), so a 30-day end date would have stranded free members
  after a month with no way to renew.

### Key files
`application/views/partials/plan_limit_modal.php` (new), the 11 dashboard views
that now include it (`user_listing`, `user_events`, `user_blog_posts`,
`user_news`, `user_products`, `user_videos`, `user_enquiry`, `post_ads`,
`all_classifieds`, `all_rental`, `all_house`), `application/views/pricing_plan.php`,
`application/controllers/User.php` (`start_free_plan()`, `pricing_plan()` now
passes `is_logged_in`), `application/models/Usermodel.php`
(`activateFreePlan()`), `application/config/routes.php` (`start-free`).

No migrations.

---

## 2026-08-04 — Directory hero: the second sentence gets its own line

### Fixed
- **The directory hero lede split mid-sentence.** `/all-listing` (no category
  filter) reads "Find local businesses and service providers across Pearland,
  TX. Search by category, call them straight from the listing…", and at the
  hero's `62ch` measure the wrap landed between "Search by" and "category",
  stranding those two words at the end of line one. The second sentence is now
  a `<span class="p411-hero__lede-line">`, and the kit gives that class
  `display: block`, so it always begins its own line. A block element rather
  than a `<br>`: the sentence still reflows normally at every width, and the
  class is opt-in, so the other nine views that share `.p411-hero__lede`
  (news, events, videos, classifieds, coupons, about, contact, services,
  all-category) are untouched.

### Key files
`application/views/all-listing.php`, `assets/css/directory.css`,
`application/controllers/User.php` + `application/controllers/Admin.php`
(cache-busting `directory.css?v=3` — Admin's listing preview was still asking
for `?v=1`, so the shared sheet now has one cache key across the site).

---

## 2026-08-03 — The rest of the site follows the homepage: navy structure, green as an accent

The homepage was rebuilt yesterday (entry below) but the pages it links to were
not: the directory, listing details, events, news, videos and About were still
the previous scheme — a **dark-green hero**, green card frames, green tinted
"no photo" wells and a green rule under every heading — under a **medium-blue**
header and a **black** section bar. Clicking a business on the new homepage
landed you on what looked like a different website.

Those pages take their design from two stylesheets (`directory.css`, the shared
page kit, and `content.css` on top of it), so this is a re-skin of the kit
rather than a rewrite of the views: **the markup is unchanged except where a
colour was hard-coded in it.** Same tokens as `home-sections.css`, so the two
cannot drift.

### Changed
- **Design tokens.** `--p411-green` `#8CC63F` → `#79B82A` and demoted to an
  accent; navy `#20245B`, burgundy `#6D263D`, cream `#F7F5F0` and light grey
  `#F2F3F5` added. The token *names* were kept — several views set
  `style="background:var(--p411-navy)"` inline on rail thumbnails, and renaming
  a custom property silently paints its users transparent.
- **Heroes** (directory, listing details, events, news, videos, About and the
  three detail pages) are the homepage hero: navy blending into burgundy over
  the page's photograph, with a dark wash so the headline stays readable
  whatever picture an editor uploaded, and a green-to-burgundy rule along the
  foot — the same motif the footer wears along its top. They were `#1d2b12`,
  a dark olive-green.
- **Section headings** take the homepage's burgundy rule; sidebar panel titles
  take its green tab. **Cards** (business results, articles, events, videos,
  related, About) take its white/`#E4E6EC`/12px/soft-shadow frame and hover
  lift. **Featured** is burgundy — a burgundy rule across the top of the card
  and a burgundy flag — because burgundy is the promotional colour everywhere
  else; **New** is the one green flag; the category chip on artwork is navy.
- **The event calendar chip** is the homepage's block: a burgundy month band
  over the day on white. It was a translucent dark-green square.
- **Category strip, filter tabs, toolbar, pager, rail, forms, notes, empty
  states, chips, review bars, the owner band** — all repainted. The pager's
  current page is navy (a state), links and buttons stay green (actions). The
  owner band's four icons rotate green / burgundy / navy instead of four
  identical green tiles.
- **About**: the vision/mission band is navy-to-burgundy rather than dark green.
- **Letter avatars** in the directory sidebar were eight unrelated hues (a
  bright blue, a teal, a violet, a pink) chosen to be distinguishable; they are
  now six shades of the three brand colours.
- **The site header and section bar** are painted to match the homepage:
  navy `#20245B` with a 3px green rule under it, a green pill for the last
  nav item, and the section strip in `#171A45` with a green underline on the
  current page. They were `#1e68ac` and `#000000` with `#b71d16` red
  underlines — three colours that appear nowhere in the brand. New file
  `assets/css/site-header.css`, loaded from `header.php` for every page that
  uses it, exactly as `site-footer.css` already is. **Paint only** — the bar
  stays `position: fixed` at its current height, because `.news-top-menu`
  clears it with a hard-coded `margin-top: 60px`.

### Fixed
- **The "Featured" badge on a listing page had 112px of blank space in front of
  the word.** The classic Material Icons font that `header.php` loads has no
  `verified` ligature, so the browser rendered the eight letters of the word in
  the icon face — invisible, but not weightless. It is `stars` now. Every other
  icon name these pages use was checked against the served font; `verified` was
  the only one missing.
- **The burgundy rule on a Featured card has never actually painted**, on the
  homepage either. `style.css` carries bare `:before` / `:after` rules —
  `{ position: absolute; font-family: "Material Icons"; … }` — that apply to
  *every* element on the site, so `content: ""; height: 3px` inherits
  `position: absolute`, shrink-wraps to zero width and draws nothing. Both the
  homepage's rule and the kit's new one now state `position: static` and an
  explicit width.
- **The pager's current page was olive `#909D3E`** and so was the focus ring on
  every form in the kit: `pearland-brand.css` sets those with `!important`, and
  an important declaration cannot be outranked by specificity. The kit now
  matches `!important` in those two places (and only those two), with a comment
  saying why.
- **Links inside stored copy** — a listing description, an article body — fell
  through to `pearland-brand.css`'s bare `a { color: … }` and came out olive.
  `.p411-prose a` is green now.
- **"Claim this listing"** was a navy button sitting on what is now a navy hero.
  It is burgundy.

### Key files
`assets/css/directory.css`, `assets/css/content.css`, `assets/css/site-header.css`
(new), `assets/css/home-sections.css`, `application/views/header.php`,
`application/views/{all-listing,listing_details,events,event_details,news_details,video_details}.php`,
`application/controllers/User.php` (cache-busting `?v=2`).

---

## 2026-08-03 — Sign in, register, contact, deals and services on the shared kit; buy by phone

### Added
- **A way to buy without checking out.** New `partials/sales_help.php`: the
  phone number as a 25px green pill (a `tel:` link), opening hours, the email
  address, and a short callback form — name, phone, email *optional*, "ask for
  someone in particular" (the named reps), and what they need. It sits at the
  foot of `/pricing-plan` with a one-line pointer to it in the hero
  (`#talk-to-us`), and is linked from the register page's sidebar.

  Behind it, three small changes so the form actually works for someone who
  doesn't want to type an email address:
  - `User::contact_submit()` takes a whitelisted **`return_to`**
    (`pricing-plan` / `contact-us`), so a callback request lands back where it
    was made instead of on /contact-us;
  - **email is no longer `required`** — an address *or* a phone number is
    enough, enforced after validation, and the acknowledgement email is only
    sent when there is somewhere to send it;
  - the enquiry is filed as **"Pricing plan — callback"** with the requested
    representative in the message body (`enquiries` has no column for it).
- **`sales_representatives()`** in `helpers/view_helper.php` — the nine names
  that were hard-coded in `views/register.php`, now shared with the callback
  panel and overridable through a `sales_representatives` site setting (one per
  line or comma separated) so the list can change without a deploy.

### Changed
- **`/login` and `/register`** are on the shared kit (`.p411-ct--auth`): a
  white form card beside a navy panel that says what an account is *for*,
  labelled fields, real error notes and a "call us if you're stuck" line under
  both. They were the theme's `login-reg` block — an unlabelled form on a white
  page, a dead Google `g-signin2` placeholder (nothing loads Google's
  platform.js, so it rendered an empty box) and a copy of the theme's unused
  "Get quote" modal. Register also **shows its validation errors**: the model
  returns them bare and the old view printed nothing at all, and the zip-code
  radios posted **`1`** for 77581 instead of the zip.
- **`/contact-us`** is on the kit. It carried ~260 lines of its own CSS inside a
  doubled `<style><style>` tag — so the first line of it printed to the page as
  text — in the old #180f5e/#7bb73f palette. Now: the standard hero, the form
  in the main column, and a sidebar with phone / email / location / hours,
  advertising links and the common questions.
- **`/coupons` and `/coupons/<id>`** are on the kit, replacing ~110 lines of
  inline `style="…"` in the old teal (#0e2a3a header, #fff7d6 code chip). The
  code is a green dashed chip; a deal ending within a week gets an "Ends in N
  days" flag; a deal with no photograph gets burgundy ticket art rather than
  the grey "no photo" well; and there is a real empty state instead of a
  Bootstrap alert. Five ad zones on the index, as on /event and /news.
- **`/services` and `/services/<slug>`** are on the kit, replacing ~370 lines of
  page-local CSS (a three-colour #180f5e → #2563eb → #7bb73f hero, blue-tinted
  cards, a lime-gradient CTA). The category filter is the standard pill row,
  and the quote form is a sticky card beside the copy.
- **`/pricing-plan` keeps its layout but takes the brand palette**: `--brand`
  #180f5e → navy #20245B, `--go` #21a179 teal → green #79B82A, the mint
  headline → `#A9E060`, "most popular"/"best value" amber → burgundy, and the
  page ground → cream. Structure and the bundle logic are untouched.
- **New kit components** in `content.css`, all built from the existing tokens:
  `.p411-auth*`, `.p411-contact`, `.p411-code` (coupon codes), `.p411-svc*`
  (service cards), `.p411-facts` (a label/value table inside a card),
  `.p411-choices` (radio pills), `.p411-steps`, `.p411-form__row`, `.p411-req`
  and `.p411-lede`.
- **Stored HTML finally gets styled.** `.p411-prose` only ever styled
  paragraphs and links, so admin-entered `<h3>` came out at browser defaults
  and every `<ul>` lost its bullets to the theme's global
  `li { list-style: none }`. Headings, lists and blockquotes now paint — this
  affects service bodies, article bodies and listing descriptions alike.

### Fixed
- **The white gap between the page and the footer** (client report). The
  site-wide footer banner sat in a `background:#f9fafb` wrapper that was
  printed whether or not the zone returned anything — with advertising switched
  off in /admin/setting it was an empty near-white strip across the page.
  `views/footer.php` buffers the slot and only prints the wrapper when there is
  a banner in it; the band is cream, and the banner is capped at leaderboard
  width (a zone with no size on record was stretching to the full container).
- `/login` and `/register` never called `loadHeaderCategories()`, so the
  Explore mega menu came up with an empty category column on both.

### Key files
`application/views/{login,register,contact-us,coupons,coupon_details,services_index,service_details,pricing_plan,footer}.php`,
`application/views/partials/sales_help.php` (new),
`application/controllers/User.php` (`contact_submit`, plus `page_css` on the seven rebuilt pages),
`application/helpers/view_helper.php`, `assets/css/content.css`, `assets/css/site-footer.css`.

---

## 2026-08-03 — The "Add my business" band above the footer joins the palette

### Changed
- **`.full-bot-book`, the last band before the footer**, was the one stretch of
  the theme's demo styling left on every inner page: a near-black `#191919`
  strip carrying a card in a **yellow-to-orange gradient** (`#ffea31` →
  `#ffbe5e`) with black text and a **navy-blue** pill — four colours that
  appear nowhere in the brand, sitting directly on top of the new navy footer.
  It is now a cream `#F7F5F0` band (so the page steps cream → navy rather than
  slamming into black) carrying a rounded navy card with white copy and the
  green pill the rest of the site uses for calls to action.
- **Layout.** The theme floats all three children and Bootstrap gives them
  `col-md-*` widths; the card is a flex row now, so the icon, the copy and the
  button line up on one baseline at any width. Below 760px it stacks
  left-aligned with a full-width button — the theme's own phone rules centred
  the text, pushed the copy down by 30px and left the button shrink-wrapped.
- **Gutters.** The theme strips the padding off `.container`, so below the
  fixed-width breakpoint the card ran into both edges of the screen; the band
  carries the same 15px gutter the footer's columns sit on.

The wording ("#1 Business Directory and Service Provider") and the markup in
`views/footer.php` are untouched — this is paint and layout only. Note that
`views/user_footer.php` (the logged-in dashboard) has its own copy of this band
still carrying the vendor's Lorem Ipsum and a dead `pricing-details.html` link,
inside a footer that is still entirely theme demo content; it is left alone
here rather than half-restyled.

### Key files
`assets/css/site-footer.css` (bumped to `?v=2` in `views/header.php` and
`views/index.php`).

---

## 2026-08-03 — One Explore menu and one header search across the site

### Fixed
- **The header search was two shapes fighting.** The theme builds it as a 33px
  input with a 40px square `position: absolute` button pinned to its right
  edge, so a rounded white field had a hard-cornered green block hanging off
  the end of it — and the magnifier was drawn in the theme's grey (#596873) on
  top of the green, from a 14px `search.png` sitting behind a Material
  `search` glyph. It is one pill now, with the button in the flow as a flex
  item and a single white icon.
- **The mega menu was a different menu on inner pages.** Three differences,
  all of them visible:
  - its trigger was `<a href="#" data-toggle="modal" data-target="#categoriesModal">`,
    pointing at a Bootstrap modal **that does not exist anywhere in the
    codebase** — while `assets/js/custom.js` also opened the panel underneath
    it, so one click did two things and one of them did nothing;
  - its services column linked to `all_products` and `all_blog`, neither of
    which is a route;
  - it ignored the `/admin/setting` service switches, so it advertised
    sections that 404 when they are turned off.

  `views/header.php` now emits the homepage's menu verbatim — same trigger
  ("Explore" with the chevron as markup, not the theme's stray
  `right: -16px` `::after`), same service column behind the same flags, same
  category list through the shared slug rule, same pill buttons.

### Key files
`application/views/header.php`, `assets/css/site-header.css`.

---

## 2026-08-03 — /all-category shows every category on one page

Follow-up to the rebuild below: the index is no longer paginated.

A category list is a lookup table, not a feed. Paged twenty at a time, a
visitor hunting for "Plumbers" had to guess which of eight pages held it, and
find-in-page — the tool people actually reach for — could only ever see a
twentieth of the list.

- **One query, the whole index** (`getAllCategoriesWithCounts()`), then the
  busiest twelve as picture tiles and everything else as a dense A–Z index:
  name, count, nothing else. Tiles do not scale past a couple of dozen; 500 of
  them is a beautiful way to make a category impossible to find.
- **Filter in the browser.** Typing narrows the whole list instantly — no
  request, no reload — hiding empty letters as it goes and reporting the match
  count. The form still submits for visitors without JavaScript, which is what
  the SQL search is for, and `?q=` links still work.
- **A sticky filter + A–Z jump bar** that clears the theme's fixed header, so
  the controls stay reachable however far down the page you are.
- **`content-visibility: auto` on each letter group**, so the browser skips
  layout and paint for the groups off screen; `contain-intrinsic-size` stops
  the scrollbar jumping while it does.
- Categories with no listings are still listed — somebody has to be first —
  but dimmed, with a dash instead of a count, so they don't compete.
- The hero's search box is gone: the filter is the search on this page, and
  two search boxes on one screen is one too many.

### Fixed
- **`overflow-x: hidden` on `html`/`body` was silently disabling
  `position: sticky`** everywhere below it — a scroll-container ancestor does
  that. It is `overflow-x: clip` now (behind `@supports`, with `hidden` as the
  fallback), which crops without scrolling.

### Key files
`application/views/all-category.php`, `application/controllers/User.php`
(`all_category`), `application/models/Usermodel.php`,
`application/config/routes.php`, `assets/css/directory.css`,
`assets/css/site-header.css`.

---

## 2026-08-03 — /all-category and /classifieds on the shared page kit

The last two public pages still on the old theme. Both are rebuilt on the same
shell as the directory, events, news and videos — hero band, filter strip,
main column plus sidebar, six ad zones, owner band — with their styling in
`directory.css` + `content.css` through `$page_css`. Between them they carried
five `<style>` blocks and about 740 lines of CSS, including two hand-copied
versions of the pagination rules the kit already provides; there are none now.

### Fixed
- **The category index searched only the page you were on.** `/all-category`
  filtered its twenty rows in PHP, so a category that lived on page 3 came back
  as "No Categories Found". The search runs in SQL now
  (`Usermodel::getCategoriesPaged()`), across names and descriptions.
- **It ordered by `category_id DESC`,** i.e. by whichever categories happened to
  be created last, and showed no counts — a visitor could not tell a category
  holding 257 businesses from an empty one. Busiest first, with the live count
  on every tile.
- **Its pager pointed at `/user/all_category/`.** `/all-category/(:num)` is a
  route now, so page 2 keeps the clean URL, and the search term survives paging.
- **`/classifieds` reported the wrong result count** — it printed
  `count($all_classifieds)`, the size of the current page, so a search with 68
  matches said "10 classifieds found".
- **The Sports and Health links in the section strip were hard-coded slugs**
  (`all_listing/sports-clubs`, `all_listing/health-services`). Categories are
  operator-editable, so neither was guaranteed to resolve, and a slug that
  matches nothing lands on an empty directory page. Both resolve by name now
  through `Usermodel::findCategoryByKeywords()` — Health prefers a category
  called Healthcare — and only against categories that hold published listings,
  so an item with nowhere to go is left out instead of pointing at an empty
  page. An "All categories" link joins them.
- **Every inner page scrolled sideways.** Two of the theme's chrome elements
  sit outside the viewport — `.mob-me-all`, the off-canvas menu parked a full
  screen to the right, and `.ani-quo-form`, the help slide-out. Both are
  `position: fixed`, so they ignore any ancestor's overflow and the guard has to
  be on `<html>`/`<body>`; it is in `site-header.css`, which every page loads.
  The homepage has had the same line since its rebuild.
- Bootstrap and jQuery UI were pulled from CDNs on every page although the
  repository ships the same versions. Served locally now, as on the homepage.

### Added
- `Usermodel::getCategoriesPaged()` / `countCategoriesWithSearch()` — the
  category index with live counts and a SQL search.
- `Usermodel::findCategoryByKeywords()` — resolve "whatever this site calls
  Healthcare" for navigation, one cached query per request.
- `Usermodel::getClassifiedsCategoriesWithCounts()` — the classifieds sidebar
  and its filter tabs.
- A tinted variant of the shared category tile (`.p411-cats--index` +
  `.p411-cat--green|bu|navy`) in `directory.css`, matching the homepage strip.
  Additive: the plain strip emits none of those classes and is unchanged.

### Key files
`application/views/all-category.php` (rewritten), `application/views/classifieds.php`
(rewritten), `application/views/header.php`, `application/controllers/User.php`
(`all_category`, `classifieds`), `application/models/Usermodel.php`,
`application/config/routes.php`, `assets/css/directory.css`,
`assets/css/site-header.css`.

---

## 2026-08-02 — Homepage: Explore menu fixes, hero photograph support

Follow-up to the redesign below, from the client's review of the deployed page.

### Fixed
- **The Explore mega menu opened above the top of the window.** `.pop-menu` is
  `position: absolute` with no `top`, so it fell back to its static position —
  and it is a child of `.hom-nav`, which the redesign made a flex row. An
  absolutely positioned flex child takes its static position from the
  container's alignment, so `align-items: center` centred a 408px panel on a
  43px header row: the browser computed `top: -184px` and the upper half of the
  menu sat off-screen, unreachable. It now has an explicit `top: 100%`.
  Below 993px the theme turns the same element into a full-screen
  `position: fixed` sheet, where `top: 100%` would have meant "one viewport
  down", so the two cases are separate media queries.
- **`.hom-top` is `position: relative`, not `static`.** It has to stay a
  containing block or the menu anchors to `.hom-head`, which wraps the whole
  hero — on a phone that put the panel 900px down the page. `static` was also
  silently discarding the theme's `z-index: 20`.
- **Two arrows on "Explore".** The theme gives `.menu` its own `::after`, an
  absolutely positioned `down-arrow.svg` pinned to `right: -16px`. That assumes
  the old floated header, where `.menu` was much wider than its text; in the
  flex header the element shrink-wraps and the arrow landed on top of the "E".
  It is switched off and the chevron is an `<svg>` in the markup, in the flow.
- The menu's own contents were still raw theme styling, which only became
  visible once the panel was reachable: a 32px olive phone number in
  'Quicksand', two square navy blocks for its calls to action, and a grey
  cityscape strip behind them. Now navy/green type and pill buttons. Its
  category `<ul>` had floated `<li>`s and no clearing, so the list collapsed to
  zero height and left ~200px of blank panel; it is a grid.

### Added
- **Hero photograph.** Drop a file at `assets/images/home/hero-pearland.jpg`
  (or `.jpeg` / `.webp` / `.png` / `.avif`) and the hero uses it; with no file
  there the drawn illustration still shows. Detected in PHP (`$hero_photo` in
  `views/index.php`) rather than layered in CSS, because a missing background
  layer would 404 on every page view. A photograph gets the full-strength
  image and a slightly heavier overlay (`.p411-hero--photo`).

### Key files
`application/views/index.php`, `assets/css/home-sections.css`.

---

## 2026-08-02 — Homepage redesign: navy structure, green as an accent

The homepage was pear green on pear green — green hero, green card frames,
green "no photo" tiles, green house ads — which the client's review summed up
as looking "too much like a lawn or landscaping website". The page is rebuilt
against the supplied mockup: **navy** for the header, headings, footer and
structure, **burgundy** for promotional and secondary bands, **green kept as
an accent** on buttons, icons, links and labels, over cream and light-grey
grounds. Same theme, same CodeIgniter plumbing; no new framework.

`views/index.php` went from 3,736 lines to ~1,100: its 2,200-line inline
`<style>` block is now `assets/css/home-sections.css`, so the page can be
re-skinned without touching PHP.

### Changed
- **Section order** now matches the brief: header · hero · categories ·
  featured · deals · events · explore · news + videos · grow your business ·
  email signup · footer, with the weather in the right rail. The business
  grid used to run before everything else and pushed deals, events and news
  off the first two screens.
- **Hero.** Navy at the top blending into burgundy, over a new Pearland dusk
  illustration (water tower, a Town Center-style parade of shops, people on
  the plaza) with a dark overlay for legibility. Green appears once, behind
  the search bar. Two calls to action under it — *Explore Local Businesses*
  and *List Your Business Free* — and the supporting line is the client's.
- **Business cards.** One component, used by both Featured and Explore, so
  they cannot drift: fixed-ratio media box, flex body, footer pinned to the
  bottom. Every card in a row is the same height whether or not the listing
  has a description — the old cards were up to a full screen tall when a
  listing was empty. Each carries category, name, rating, one or two lines of
  description, service area and a **View Business** button; Featured adds a
  burgundy rule and a **Call** button.
- **Explore is capped at six listings** with a *View All Pearland Businesses*
  button, instead of nine full-width cards.
- **Category strip.** Bigger icons, a light tint per tile, the accent rotating
  navy / green / burgundy, a hover lift, quieter counts, and the whole card is
  the link.
- **Deals and events** gained a logo, category, expiry, a **Redeem Deal**
  button, a burgundy date block and **Add to Calendar** (a Google template
  URL, so nothing to install).
- **News** is one lead story with a large image plus four supporting rows,
  navy headlines, a burgundy *News* label and a green *Community* one. The
  bright blue links were the theme's, not the brand's.
- **Weather** shows three days plus current conditions, with the rest behind
  *View full forecast*; it was six days and a block of statistics.
- **The service promo card** (`partials/service_promo.php`) is navy with green
  accents rather than a third large green panel; the design card stays
  burgundy. `promo-cards.css` is shared, so the directory and listing pages
  follow.
- **Footer**, rewritten as `partials/site_footer.php` and shared with
  `views/footer.php`: lighter navy (`#1E2F52`), a green-to-burgundy rule
  across the top, the logo, a description, five link columns, contact details
  and social icons. The theme's black phone quick-bar matches it now.

### Fixed
- **The same business could appear three times on one screen.** Hotspot,
  Business of the Week, Featured and Explore each ran their own query with no
  idea what the others had returned. `User::index()` now fills them in
  priority order — paid placements first — and de-duplicates by `listing_id`
  *and* by a flattened name, because the directory holds genuine duplicate
  rows and two ids for "Monster Tree Service of Pearland" look identical to a
  reader.
- **Featured could be a row of blank cards.** `getHomepageFeatured()` only
  returns listings with a real description (25+ characters, which is past
  "-", "n/a" and an empty `<p></p>`), preferring those with a photograph.
  When nothing qualifies the section sells the placement instead of showing
  empty cards — a Featured row of blanks makes the paid slot look worthless.
  `getHomepageListings()` applies the same bar and tops up if the grid would
  otherwise be short.
- **"No photo yet" was one pear-green tile repeated down the page.** There are
  now 18 quiet cream/grey placeholders — one per kind of business plus one for
  news — each with a category glyph and a single small green accent, picked by
  the new `category_placeholder()` helper. Thumbnails under ~120px draw an
  icon instead, because the artwork's caption turns to grey mush at that size.
- **Every unsold ad zone showed the same green "your ad here" rectangle.**
  House creatives now come in four palettes (green, burgundy, navy, cream)
  with four different messages, assigned per position; the top, bottom, third
  sidebar and skyscraper zones render *nothing* when unsold rather than a
  placeholder, and the "Sponsored" label is smaller. All seven zones stay
  bookable. The wide creative was also re-laid-out inside a safe area — at
  970x250 `object-fit: cover` was cutting its button in half.
- `getLatestNews()` ignored `is_deleted`, so a soft-deleted story stayed on the
  homepage, and it selected neither the slug nor the date — the homepage linked
  stories by id while the rest of the site uses `/news-details/<slug>`.
- The shared footer had drifted into a second, hand-kept copy: a different
  phone number and email from the homepage's, eight hard-coded category slugs
  (several of which no longer resolve), and `Copyright © 2025-2035`. It is one
  partial now, categories come from the same query the rest of the page uses,
  contact details come from `/admin/setting`, and the year is `date('Y')`.
- Every link in the phone quick-bar pointed at a `.html` file — `index.html`,
  `all-category.html`, `events.html` — left over from the static template the
  theme was cut from. None of them resolved. Its items are also `width: 20%`
  `inline-block`, so five of them overflowed and the last sat half off-screen;
  the row is flex now and the homepage caps it at five.
- Bootstrap and jQuery UI were pulled from CDNs on the homepage although the
  repository ships the same versions; both are served locally now. jQuery UI's
  *script* was missing entirely, and `custom.js` calls `.autocomplete()` and
  `.datepicker()` unconditionally, so everything in that file after those
  calls was dead on the homepage.
- `<meta name="viewport">` carried `maximum-scale=1`, which blocks pinch-zoom.

### Added
- `assets/css/home-sections.css` — the whole homepage, scoped to `.p411-home`.
- `assets/css/site-footer.css` + `views/partials/site_footer.php` — the shared
  footer, scoped to `.p411-foot`.
- `scripts/generate_brand_art.py` — generates the category placeholders, the
  four house-ad palettes and the advertiser templates. Deterministic, writes
  only under `assets/images/`, safe to re-run.
- `assets/images/ads/templates/` — four editable advertiser banner templates
  (970x90, 900x300, 600x500, 300x600) where six declarations at the top of the
  file set the colours, plus a README on customising them for an advertiser's
  own brand.
- `assets/images/home/hero-pearland.svg` — the hero illustration, hand-authored.
- `category_placeholder()` in `helpers/view_helper.php`.
- `site_logo_url` in `/admin/setting`: the header and footer logo is still
  hot-linked from a third-party image host by default, and this is the switch
  that moves it into `assets/images/` without a deploy.

### Not done
- **The 20 complete business listings** the client asked for are data entry in
  the production database (logos, descriptions, phone numbers), not a code
  change — there is no listing content in this repository to edit. The
  completeness rules above mean those 20 will fill the homepage the moment
  they exist, and until then the best of what is there is shown first.
- **Photographs of Pearland.** The hero is an illustration; the placeholders
  are drawn. Real photography of the Town Center, local owners and community
  events has to be supplied or licensed.

### Migrations
None.

### Key files
`application/views/index.php` (rewritten), `application/views/partials/site_footer.php` (new),
`application/views/partials/ad_slot.php`, `application/views/footer.php`,
`application/views/header.php`, `application/controllers/User.php` (`index`),
`application/controllers/Admin.php` + `views/admin/admin_setting.php` (`site_logo_url`),
`application/models/Usermodel.php` (`getHomepageFeatured`, `getHomepageListings`,
`getLatestNews`, `getActiveCoupons`), `application/helpers/view_helper.php`,
`assets/css/home-sections.css` (rewritten), `assets/css/site-footer.css` (new),
`assets/css/promo-cards.css`, `scripts/generate_brand_art.py` (new),
`assets/images/placeholders/*`, `assets/images/ads/*`, `assets/images/home/hero-pearland.svg`.

---

## 2026-07-30 — About us rebuilt on the same layout

`/about-us` now uses the shared page kit — hero with breadcrumb, section
headings, cards, chips, buttons and the closing owner band — plus a few
About-only bands (text/media splits, a live figures strip, the vision and
mission panels) added to `assets/css/content.css`. The view went from 839 lines
to 404.

### Fixed
- **526 lines of inline `<style>`, in a palette that matched nothing else on
  the site.** The page was indigo and purple (`#2d1b69`-ish hero, purple
  vision/mission band) while every other page is the logo's pear green and
  navy. It now takes its colours from the same tokens as the homepage,
  directory and content sections, so it reads as part of the site.
- **Two hotlinked Unsplash photos** (`images.unsplash.com/photo-…?w=1200`) —
  an external request on every page load, a third-party log entry for every
  visitor, and no guarantee the images stay there. The intro now uses a local
  asset with an honest caption; the "For business owners" stock photo of a
  meeting is replaced by a card of the three real steps to getting listed,
  which tells an owner something the photo didn't.
- **"Explore thousands of verified businesses" was static copy.** The page now
  counts what is actually on the site and links each figure to the page it came
  from — businesses via the same `directoryQuery()` the directory counts with,
  categories that actually have a listing in them (`countAllCategories()` would
  have included the empty ones and read higher than the directory), plus
  events, stories and videos. A section switched off in `/admin/setting` 404s,
  so its figure is left out rather than advertised.
- **"Upgrade to our Premium Plan" quoted no price and linked nowhere useful.**
  The "from $N a month" line is read off the active `plans` rows, so it cannot
  drift from what `/pricing-plan` charges, and the section links to both
  `/register` and `/pricing-plan`.
- `href="/contact-us"` was root-relative, which breaks in any sub-directory
  install; it goes through `base_url()` like everything else.
- No `<title>` beyond "About Us", no meta description, no canonical URL and no
  structured data. It now has all four, including `AboutPage` +
  `Organization` + `BreadcrumbList` JSON-LD.
- On a phone the intro led with a photograph and pushed the heading below it.
  Text comes first in the source now; the image is pulled into the left column
  only once there are two columns.

### Added
- Site-wide ad zones (`top-banner-site-wide`, `bottom-banner-site-wide`) and
  the house services promo, so the page carries the inventory a site-wide
  package was sold on. Two positions only — it is full-width editorial with no
  sidebar to hang the other four off.

### Migrations
None.

### Key files
`application/views/about.php` (rewritten), `application/controllers/User.php`
(`about_us`), `assets/css/content.css`.

---

## 2026-07-29 — Events, news and videos rebuilt on the homepage / directory layout

`/event`, `/news`, `/videos` and all three detail pages now use the same shell
as the reorganised homepage and directory — hero band, filter or category
strip, main column plus a right-hand sidebar, sellable ad zones, the two house
promo cards and the owner band. All six views were rewritten and their **~1,300
lines of inline `<style>`** (including two hand-copied duplicates of the
sidebar marketing card) replaced by one small scoped stylesheet.

### Fixed
- **Every "Related posts" link on a news story was a 404.** They pointed at
  `/news-details/<slug>` — the canonical form the share buttons, the
  `<link rel=canonical>` and the JSON-LD all use — but only the underscore
  spelling had a route, and CI3's catch-all `(:any)` is `[^/]+`, so a
  two-segment hyphenated URL matched nothing at all. Both spellings are routed
  now for news, videos and events, the hyphenated one is canonical everywhere
  (sitemap, homepage rails, search results, share links), and the detail
  controllers build `canonical_url` from it instead of the long
  `/user/news-details/…` form they were advertising to Google.
- **The events page was an archive labelled as a calendar.** It sorted by
  `event_start_date DESC`, so next year's fair sat above tonight's concert and
  everything that had already happened was mixed in with what hadn't. `/event`
  now takes a `?when=upcoming|past|all` window, defaults to **upcoming** and
  sorts soonest-first inside it — and if nothing is scheduled it widens to the
  archive by itself and says so, rather than showing an empty page.
- **Event descriptions printed their own markup.** They are saved with pasted
  HTML (`<p>…<br />`) and the view escaped the lot, so visitors read literal
  `<p>` tags down the page. News and video bodies had the opposite bug:
  `strip_tags()` over the whole string, which threw away every paragraph
  break and ran the story together. Both now go through the new
  `content_text()` helper — block tags become newlines, the rest is stripped,
  entities decoded, then escaped at output.
- **`events.event_map` was echoed raw**, so whatever an admin pasted into that
  field became live markup on a public page. The new `embed_src()` helper takes
  the `src` out of the pasted snippet (or accepts a bare URL, rejecting
  anything that isn't `http(s)`) and the view builds its own iframe.
- **Videos with no uploaded thumbnail rendered a broken image.** `video_code`
  was not even in the index's select list, though YouTube hosts a still for
  every video id. It is now, and `youtube_thumb_url()` fills the gap. The
  player embed goes through `youtube_id()`, which copes with the watch URLs,
  `youtu.be` links and embed URLs that turn up in a free-text field — and when
  there is genuinely no id, the page says so instead of framing a 404.
- `getRelatedNews()` ignored `is_deleted`, so a soft-deleted story could still
  be recommended. `getRelatedEvents()` ordered by `event_id DESC`, which put a
  concert from last spring above a festival next week; it now offers what is
  still to come first. Both, and `getRelatedVideos()`, prefer the same
  category and top up site-wide so the block is never short.
- `User::event_search()` was a second, thinner copy of `event()` — no
  pagination, no window, no counts, no sidebar, no ad zones — so
  `/user/event_search?q=x` rendered a visibly poorer page than `/event?q=x`.
  It delegates now, as `listing_details_global()` does.
- The news detail view ran its own `$this->db` query for the byline, mid-markup;
  the event detail view called `get_instance()` to resolve the host the same
  way. Both moved into the controller.
- An offset past the end of the results rendered a blank list on all three
  indexes; it clamps to the last real page, like the directory.
- Every empty state now names the thing that is missing and what to do next —
  no upcoming events vs no archive vs no events at all, no search matches, an
  empty category, a story with no body, a video with no playable id.

### Added
- **`assets/css/content.css`** — the components the six pages add on top of the
  shared kit: the hero search pill, the time/category filters, the event/story/
  video cards, the lead story, the article body and the 16:9 player. Every
  selector starts `p411-ct`; the file is linked only from those views.
- **`content_text()`, `content_excerpt()`, `youtube_id()`,
  `youtube_thumb_url()`, `youtube_embed_url()` and `embed_src()`** in
  `helpers/view_helper.php`, so the six views share one definition of each
  instead of six copies. `content_text()` / `content_excerpt()` return
  **unescaped** text — the caller escapes at output, as everywhere else here.
- **Ad zones keyed to the packages people can actually buy.** Six placements on
  each of the six pages — top banner, in-content banner, two sidebar banners, a
  skyscraper and a footer banner — primed in one query per page. The 0006 keys
  these pages used to render (`events-sidebar`, `news-sidebar`) match no
  package and could never be bought, so they stay live as `$slot_alias`: an ad
  booked against one by hand keeps its impressions.
- **Section and channel filters.** `/news?category=<slug>` and
  `/videos?category=<slug>`, with counted strips and a sidebar list, resolved
  through `Usermodel::resolveContentCategoryId()` — stored `category_slug`
  first, then `pearland_slug()` over the names, because the admin's category
  form builds slugs with a third expression again. A query string rather than a
  path segment, so it composes with `?q=` and `?page=`.
- Sidebar rails that aren't the first page repeated: **Coming up** on events,
  **Most read** on news, **Most watched** on videos (`getPopularNews()`,
  `getPopularVideos()`, `getUpcomingEvents()` with an exclusion).
- Event details: an **Add to Google Calendar** link (all-day, since
  `event_time` is free text and can't be parsed into a reliable start/end),
  directions, a real map, an event-details table, a share row with copy-link,
  and `eventStatus` / `eventAttendanceMode` / `organizer` on the JSON-LD.
- Video details: `VideoObject` JSON-LD, `video_seo_*` meta support and a
  canonical URL — it had none of the three.
- News and videos indexes: per-page `<title>`, meta description and canonical.
- `attributes => ['class' => 'page-link']` on all three paginators, so the
  shared pager styling actually reaches the anchors CI3 emits.

### Changed
- `assets/css/directory.css` is now the **shared page kit**: its design tokens
  are declared on `.p411-dir`, `.p411-ld` and the new `.p411-ct`, so the
  content pages take the hero, cards, buttons, forms, notes, empty states,
  pager, rail and owner band from one file rather than a second copy. Nothing
  else in it changed; the two directory pages are untouched.
- Existing model signatures gained optional trailing parameters only —
  `getEventsWithPagination($search, $limit, $offset, $when)`,
  `getNewsWithSearch(…, $category_id)`, `getAllVideos(…, $category_id)` and
  their counts, which apply exactly the same filters as the list they label.
- `getEventBySlug()` now accepts a slug generated by **either** rule in the
  codebase (`createSafeSlug()` or `pearland_slug()`); they agree on
  "Market: A Market" and disagree on "Don't Miss", and resolving on only one of
  them makes a link that generation produced unreachable.
- Per page: 12 results instead of 10, matching the 2/3-column grid.

### Migrations
None.

### Still to do
- `getAllVideos()` / `countVideosWithSearch()` still don't filter on
  `video_status`, so a video switched off in `/admin` stays publicly visible.
  The list and its count agree, so nothing is inconsistent — but the admin
  toggle doesn't do what it says. Left alone deliberately: admin-created videos
  don't set the column on insert, so adding the filter could empty the page,
  depending on the column default. Check `SELECT video_status, COUNT(*) FROM
  video GROUP BY 1` before switching it on.
- The events index has no "featured event" concept, though the owner band
  offers one. It needs a column and a package before the page can honour it.

### Key files
`application/views/events.php`, `event_details.php`, `news.php`,
`news_details.php`, `videos.php`, `video_details.php` (all rewritten),
`application/controllers/User.php` (`event`, `event_search`, `news`, `videos`,
`event_details`, `news_details`, `video_details`, plus the private
`contentAdSlots()` / `contentPageCss()`), `application/models/Usermodel.php`,
`application/helpers/view_helper.php`, `application/config/routes.php`,
`application/controllers/Sitemap.php`, `assets/css/content.css` (new),
`assets/css/directory.css`, and the news/video links in `views/index.php`,
`views/search.php`, `views/user_news.php`, `views/user_videos.php`.

---

## 2026-07-27 — Listing-count mismatch, category slugs, and the two house promo cards

Four issues reported after the homepage rebuild. The first turned out to be a
data bug hiding most of the directory from visitors; the rest are reuse of the
services card that already existed on `/all-listing`.

### Fixed
- **A category box said "255 listings"; the category page showed 13.** Two
  queries on the same screen disagreed about what "published" means.
  `getCategoriesWithCounts()` filtered only `is_deleted`; `directoryQuery()`
  also required `listing_status = 1`. And almost nothing wrote `1` — the admin
  CSV import hardcoded `listing_status => 0` (`Admin.php`), and the public
  add-listing form wrote the **string** `'Active'` into an `INT` column
  (`User.php`), which MySQL coerces to `0`. So bulk-imported and user-submitted
  businesses were all stored unpublished, counted by the box and filtered out
  of the list. Meanwhile the admin listings table, both user dashboards and
  `Sitemap.php` all read `1` = Active.

  The fix is one definition instead of eleven hand-rolled copies:
  `Usermodel::applyPublishedListingFilter()` (and
  `publishedListingJoinCondition()` for counting LEFT JOINs, where the
  predicate must sit in `ON` or empty categories vanish). Every public listing
  query now routes through it, so a count and the list it labels can't drift
  apart again. Migration **0020** normalises the stored data and both write
  paths now write `1`.
- **Category links with punctuation led to an empty page.** Three slug
  generators disagreed: `pearland_slug()` gave `medical-dental`,
  `all-listing.php` gave `medical-&-dental`, `header.php` gave
  `medical-and-dental`, and the SQL fallback in `directoryQuery()` matched a
  fourth form. `Admin::add_new_listing_category()` never wrote
  `category_slug`, so admin-created categories depended on that fallback — and
  for any name containing `&` or `/` the homepage link resolved to nothing
  while the directory's own link worked.

  All six category-link sites now call `pearland_slug()`; the new
  `Usermodel::resolveCategoryIdBySlug()` resolves a URL back to a category by
  running that same PHP function over the names, rather than rebuilding the
  slug rule in SQL where it could diverge again. `getCategoryNameBySlug()`,
  `getCategoryIdBySlug()` and `getCategoryBySlugOrId()` go through it too, so
  the page heading can no longer say "General" over a populated list. Admin
  add/edit now writes a unique `category_slug`, and migration 0020 backfills
  the empty ones.
- The homepage footer's "Top Category" / "Trending Category" links were
  **relative** (`href="user/all_listing/…"`), so they 404'd from anywhere but
  the homepage itself.
- `searchCategories()` selected `listing_categories.*` alongside a `GROUP BY`
  on one column — rejected outright by MySQL 5.7+ under the default
  `ONLY_FULL_GROUP_BY`. Now a correlated subquery, no GROUP BY.
- `/all-listing` mapped its second sidebar zone to `right-banner-1` while
  `/listing-details` used `right-side-banner-2`, so an ad sold for that
  position appeared on one page and not the other. Both are now
  `right-side-banner-2`, with the category-priced zones (`right-banner-1/2`,
  migration 0019) kept live as `$slot_alias`.

### Added
- `partials/banner_design_promo.php` — the ad/banner-design service as a card
  in the burgundy palette the `banner-design-*.svg` creatives already use,
  mirroring `service_promo.php`. An unsold `right-side-banner-2` now renders it
  instead of a flat picture of an advertisement: the person that zone is for is
  the advertiser who has no artwork yet, and an image can't tell them what they
  get or give them somewhere to click. A real booking still wins the slot.
- `ad_slot.php` gained **`$slot_fallback_partial`** — render a partial instead
  of the default creative when nothing is sold. Ignored when an ad *is* sold,
  and when `$slot_fallback = false`.
- `assets/css/promo-cards.css` — the styles for both cards, split out of
  `directory.css` now that the homepage renders them too. Every selector is a
  `.p411-promo` / `.p411-bpromo` descendant, and nothing else in the codebase
  emits those class names. The cards no longer borrow `.p411-btn`: they sit on
  a dark panel and need a white button, and three page-scoped `.p411-btn`
  rules across three stylesheets is how white-on-white buttons happen.
  `.p411-promo__cta` / `.p411-bpromo__cta` are theirs alone.
- `database/migrations/0020_listing_status_normalise.sql` — backs the current
  values up to `listings.listing_status_backup`, publishes every non-deleted
  listing that is `0`/`NULL`, and backfills the empty `category_slug`s using
  the same rule as `pearland_slug()`. Idempotent: the backup column is the
  guard, so a re-run can't re-publish something switched off since. Rollback is
  in the file header. Mirrored into `migrations_combined_phpmyadmin.sql` (a
  `REPLACE` chain instead of the standalone file's `WHILE` loop, since
  phpMyAdmin can't take `DELIMITER`) and documented in `database/README.md`.

### Changed
- The homepage sidebar now carries both cards: `right-side-banner-1` is forced
  to the generic `house-ad` creative with `service_promo.php` directly beneath
  it — the same pairing the directory uses, so the web-services pitch isn't
  printed twice — and `right-side-banner-2` falls back to the design card.
- On `/listing-details`, the "Is this your business? / Business profile" block
  moved from sixth in the sidebar to directly under **Business info**. For an
  unclaimed listing that call-to-action is the most useful thing on the page
  for the person who owns the business, and it used to sit below the map, both
  sidebar ads and the services promo.
- `Admin::listing_preview()` loads `promo-cards.css` alongside `directory.css`.

### Migrations
`database/migrations/0020_listing_status_normalise.sql` (run after 0019, or
re-import `migrations_combined_phpmyadmin.sql`).

**Verify afterwards** — every category's count must equal the "Showing 1–N of
X" on its own page:

```sql
SELECT lc.category_name,
       (SELECT COUNT(*) FROM listings l
         WHERE l.category_id = lc.category_id
           AND l.is_deleted = 0 AND l.listing_status = 1) AS n
  FROM listing_categories lc ORDER BY n DESC;
```

### Key files
`application/models/Usermodel.php`, `application/controllers/User.php`,
`application/controllers/Admin.php`, `application/views/index.php`,
`application/views/all-listing.php`, `application/views/listing_details.php`,
`application/views/header.php`, `application/views/all-category.php`,
`application/views/search.php`, `application/views/partials/ad_slot.php`,
`application/views/partials/service_promo.php`,
`application/views/partials/banner_design_promo.php` (new),
`assets/css/promo-cards.css` (new), `assets/css/directory.css`,
`database/migrations/0020_listing_status_normalise.sql` (new),
`database/migrations_combined_phpmyadmin.sql`, `database/README.md`.

---

## 2026-07 — Directory + listing details rebuilt on the homepage layout

`/all-listing` and `/listing-details/<slug>` now use the same shell as the
reorganised homepage — hero, category strip, main column plus a right-hand
sidebar — instead of the theme's left-rail layout. Both views were rewritten
(`all-listing.php` 1,347 → 542 lines, `listing_details.php` 1,566 → 897),
their ~1,000 lines of inline `<style>` replaced by one scoped stylesheet.

### Fixed
- **The directory could show "No Listings Found" on a database full of
  businesses.** `getListingByCategory()` / `countListingsByCategory()` joined
  `user` with an **INNER** join, so every admin-imported listing — the ones
  with `user_id = 0`, i.e. everything nobody has claimed yet — was dropped from
  both the results and the count. Now a LEFT join.
- **Featured listings were excluded from the directory outright**
  (`featured_listing = 0 AND spotlight_listing = 0` in the WHERE clause). A
  customer paying $25/month for a Featured Listing vanished from the page
  people actually browse. Featured/spotlight are now included and pinned to the
  top of the results with a badge, which is what the package sells.
- **Descriptions printed their own markup.** Listings were saved with pasted
  HTML (`<p>…<br />`); `e_nl2br()` escaped it, so visitors read literal `<p>`
  and `<br />` on the page. Block tags now become newlines, the rest is
  stripped, then the text is escaped — clean text, and still no injection.
- **No listing ever emitted an `aggregateRating`.** `getReviewStats()` returns
  an array keyed 5..1, but the JSON-LD block tested `is_object($rs) &&
  $rs->total_reviews`, which cannot be true for an array. Rating and count are
  now derived once in the controller and used by both the view and the JSON-LD.
- **A listing with no coordinates rendered a map of San Francisco**, 1,900
  miles from Pearland, with no indication it was a placeholder. Replaced with
  an empty state that offers a Google Maps address search when there is one.
- `getRelatedListing()` interpolated the slug straight into a hand-built SQL
  string and filtered on **nothing** but `category_id`, so it returned
  soft-deleted and unpublished businesses. Now bound parameters plus
  `is_deleted = 0` / `listing_status = 1`, featured first.
- "Showing 1–10 of N" was stuck on the first page's numbers: the view read
  `$current_offset`, which the controller never set. An offset past the end of
  the results (hand-typed, or after listings were removed) rendered a blank
  list; it now clamps to the last real page.
- **`/all_listing/<category>/<offset>` was a 404.** CI3's `(:any)` is `[^/]+`,
  not `.+`, so the two-segment form matched no route at all — which is why
  pagination links had to be written as `/user/all_listing/…`. Added
  `all_listing/(:any)/(:num)` (and the `all-listing` spelling), so page 2 of a
  category is a clean URL.
- A `:before { position: absolute; font-family: "Material Icons" }` rule at
  `style.css:139` applies to **every element on the site**, which silently
  collapsed the review star picker to a 0×0 pile. The new stylesheet undoes it
  where a pseudo-element has to sit in the text flow; noted in a comment so the
  next person doesn't lose an afternoon to it. The review form's rating readout
  also had to move off `#star-value`, which `style.css:6841` pins to
  `left: 175px` on an amber background.
- The directory's enquiry form used `id="home_slide_enquiry_form"` — the same
  id as the Help & Support form in `footer.php`, so every page carried two
  elements with one id and `getElementById` could only ever reach one of them.
- `listing_details_global()` was a thinner second copy of `listing_details()`:
  no reviews, no review stats, no owner socials, no SEO tags, no ad zones. It
  now delegates, so both URLs render the same page. `Admin::listing_preview()`
  loaded the shared view with no data at all and, because it called
  `$this->load->view('header')` without `$data`, no stylesheet either.
- Every empty state now says which thing is missing and what to do next —
  no results (category vs whole directory), no description, no services, no
  service areas, no reviews, no map, no similar businesses, no featured
  businesses, not logged in.

### Added
- **`assets/css/directory.css`** — one stylesheet for both pages, every
  selector prefixed `.p411-dir` / `.p411-ld`, using the same tokens as
  `home-sections.css` (logo pear green `#8CC63F`, the 3px green heading rule,
  the same pill buttons and card frame) so the three pages read as one site.
- **`$page_css` hook in `header.php`** — a controller sets
  `$data['page_css']` (string or array) and the sheets load after the shared
  ones. Pages that don't set it are unaffected; this is how the two views get
  their CSS without touching `style.css`.
- **`partials/service_promo.php`** — the static right-column block for
  Pearland411's own services (website development, hosting, local SEO), linking
  to `/contact-us`. Deliberately *not* a sellable zone: it never queries `ads`,
  never rotates, and always renders, so the position is never empty. Sidebar
  and full-width banner variants.
- **Ad zones on both pages, keyed to the packages people can actually buy.**
  Six placements each: top banner, in-content banner (after the 4th result /
  mid-article), two sidebar banners, a skyscraper and a footer banner. The
  directory's bottom zone switches to `bottom-banner-category` when a category
  filter is on. Migration `0019_directory_ad_slots.sql` seeds the three
  category-priced zones that were sellable but had no `ads_prices` row —
  `right-banner-1`, `right-banner-2`, `bottom-banner-category`.
- `ad_slot.php` gained `$slot_alias` (fall through to zone(s) this position
  replaced, so inventory booked against the retired 0006 keys `listing-top` /
  `listing-inline` / `listing-sidebar` keeps running) and `$slot_creative`
  (force which house creative fills an unsold zone — the sidebar's permanent
  services block would otherwise be repeated by the `scs-ad` fallback directly
  above it).
- Directory: sort control (featured / newest / A–Z / top rated) as a query
  string, so it can't collide with the positional category/offset segments;
  category strip and sidebar category list with live counts; result cards
  carrying rating, address, phone, email and Call/WhatsApp actions.
- Listing details: breadcrumb with the real category, a sticky sub-nav that
  only lists sections that exist, a photo grid (was a carousel that rendered a
  "no images" slide even when hidden), review distribution bars, and an
  accessible star picker.
- `getFeaturedListingsForDirectory()` — the sidebar's featured rail now prefers
  businesses in the category being browsed and tops up site-wide, instead of
  three random featured listings from anywhere.

### Changed
- Both pages read their per-page pixel box from `ads_prices.size`, as the
  homepage does, so quoted size and rendered box cannot drift apart.
- `listings` search/count logic lives in one private `directoryQuery()` helper,
  so the list and its count can no longer disagree about what is visible.

### Still to do
- The packages seed still describes the right-hand banners as `900x100`; the
  zones are seeded at `300x250` because they sit in a sidebar column. The
  `/pricing-plan` copy needs the same correction 0017 already called for.
- `#categoriesModal` is referenced by the shared `header.php` but defined
  nowhere outside the homepage, so "All Categories" opens nothing on every
  other page. Site-wide header fix, out of scope here.

---

## 2026-07 — Homepage reorganised on the existing design

Follow-up to the previous entry. That attempt replaced the whole theme, which
was not the ask — the homepage needed **organising**, not a new design. The
original `views/index.php` was restored and its **body** reorganised in place,
on the theme's own classes.

### Fixed
- **The 500 on the homepage was a regression introduced here.** `GROUP BY
  c.coupon_id` had been added to `getActiveCoupons()` to fix its row fan-out,
  but the select list carries `l.listing_name`, `l.listing_slug` and `u.name` —
  columns from joined tables that are not functionally dependent on
  `coupons.coupon_id`. Under `ONLY_FULL_GROUP_BY` (MySQL 5.7+ default) that is
  error 1055, the query returns false, and the page dies. Nothing appeared in
  the PHP error log because CI3 traps database errors itself via `show_error()`,
  so it never became a PHP fatal. `getActiveCoupons()` is back to its original
  SQL; the new `getDistinctActiveCoupons()` de-dupes in PHP instead.
- **Mobile showed the sidebar before the content.** `@media (max-width:768px)`
  set `flex-direction: column-reverse`, contradicting the `991.98px` rule above
  it, so phone visitors met the weather widget before a single business.
- Four sidebar widgets carried `hide_on_phone`, so phones lost weather, deals,
  the newsletter and events outright.
- Sideways scroll on phones, from the theme's `.pop-menu` (5px over) and the
  `position:fixed` `.mob-me-all` off-canvas panel. Contained on `<body>`, since
  a fixed element ignores an ancestor's overflow.
- The category strip inherited `.category-tabs`, which the theme hides below
  768px with `!important`; it now has its own class and stays visible.
- Dropped a 404 for the non-existent `assets/js/new.js`, and the CDN copies of
  jQuery (**3.3.1 slim — no `$.ajax`, which the newsletter needs**), Popper and
  Bootstrap 4.1.3 that were loading *after*, and therefore overriding, the local
  full builds. Swiper and FullCalendar removed with the widgets they drove
  (~400 KB). 20 relative `../assets/` paths switched to `base_url()`.
- `style.css:2050` points `.hom-head` at `images/banner4.jpg`, which does not
  exist — neutralised for the homepage only.
- Markup is now tag-balanced; the original carried four stray `</div>`.

### Added
- **Business Hotspot** and **Business of the Week** sections. Both packages were
  already on sale (`business-hotspot` $25/30d, `business-of-the-week` $20/7d) and
  both promise homepage placement, but `listings` had only `featured_listing` and
  `spotlight_listing` — no field could record either, so neither could ever
  render. Migration `0018_homepage_promos.sql` adds `hotspot_until` and
  `week_until` as **dates**, so a placement expires on its own at the end of the
  paid term. Model: `getHotspotListings()`, `getBusinessOfTheWeek()`.
- `assets/css/home-sections.css` — only the genuinely new pieces. Every selector
  is prefixed `.p411-home` and no other view links it, so the shared `style.css`
  and the other ~200 views are untouched.
- Third house creative, `banner-design-*` (ad/banner artwork), joining
  `house-ad-*` and `scs-ad-*` in strip/wide/box/tall. The sidebar carries one
  web-development ad and one ad-design ad; every other zone sells itself.

### Changed
- **Ad zones now use the catalogue's own slugs.** `0017_home_ad_slots.sql` seeds
  `top-banner-site-wide`, `bottom-banner-site-wide`, `middle-banner-category`,
  `right-side-banner-1/2/3` and `right-panel-skyscraper` — each `slot_key`
  identical to the `plans.slug` a customer buys on `/pricing-plan`. Previously
  ad zones and sellable packages were two unrelated vocabularies, so nothing
  connected a purchase to a position on the page.
- Sizes: the pricing page lists most banners as `900x100`, including both
  skyscrapers, which cannot be right. Right-side banners are seeded at `300x250`
  and the skyscraper at `295x600`. **The pricing page copy still needs
  correcting to match.**
- Section count cut from ~16 to 12. Deals + events merged into **What's On**;
  videos + news merged into **Community Highlights**; the services band and
  "How It Works" merged into one owner band. Each paid tier now has a distinct
  shape — Hotspot is a slim logo rail, Business of the Week is a single wide
  spotlight, Featured is a 3-up row — rather than four near-identical card rows.
- `ad_slot.php` ships its own CSS, emitted once per request, so the eight views
  that include it need no stylesheet changes.
- The strip creative is drawn at 8:1 (was 10.8:1) because the live zones are 7:1
  and 9:1, and the wider artwork cropped its own text off.

### Still to do
- `Admin::create_ads_price()` / `ads_price_edit()` still drop `slot_key`/`size`
  and reference an undefined `$uploaded_image_path`, so new zones need SQL.
- No admin UI yet for `hotspot_until` / `week_until` — set them with SQL for now
  (`database/seeds/homepage_ad_zones_only.sql` has the statements).
- `/all-listing` and `/listing-details` are unchanged.

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
