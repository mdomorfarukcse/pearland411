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
  - `Cli.php` — CLI jobs: `php index.php cli <method>` (`images_backfill`, `renew_subscriptions`,
    `decode_content_entities` — one-off, decodes news/video/blog rows the old admin write paths
    stored entity-encoded; records itself in `site_settings` so it cannot run twice).
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
  The one piece of header chrome they *do* share is `views/partials/section_strip.php` — the navy
  Home · Directory · Events · … strip, included by both. It was `header.php`-only, which is why
  the homepage had no strip and no Classifieds link at all; keep it data-driven, because anything
  hard-coded there has to be kept in step on two pages.
- **Dates:** print them with `us_date($value)` (`application/helpers/view_helper.php`) — US order
  (`Aug 5, 2026`), and `''` rather than the epoch when the stored value is NULL, empty or MySQL's zero
  date. `us_date_ts()` gives the timestamp for the same rules. Applied on the blog pages; the other
  sections still call `date('j M Y', strtotime(...))` directly and print 1970 for a missing date.
- **Cache busting:** stylesheet `<link>`s go through `asset_css('assets/css/x.css')`
  (`application/helpers/view_helper.php`), which stamps the file's mtime as `?v=`. Never hand-write a
  `?v=N` — the numbers this replaced went stale four times, shipping fixes that returning visitors
  never saw. `page_css` entries are plain paths; `header.php` runs them through the helper.
- **Per-page CSS:** a controller sets `$data['page_css']` (path or array of paths) and `header.php`
  links them after the shared sheets. `assets/css/directory.css` is the **shared page kit** — hero,
  two-column shell, cards, buttons, forms, notes, empty states, pager, rail, owner band — and its
  design tokens are declared on the three page scopes that opt in: `.p411-dir` (`/all-listing`),
  `.p411-ld` (`/listing-details`, `/coupons/<id>`, `/services/<slug>`) and `.p411-ct` (events /
  news / videos + their detail pages, `/about-us`, `/all-category`, `/classifieds`, `/login`,
  `/register`, `/contact-us`, `/coupons` and `/services` — all of which add
  `assets/css/content.css` on top). The homepage is
  `assets/css/home-sections.css` (`.p411-home`) — the **whole** page, header to newsletter band, so
  it can be re-skinned without touching PHP. The kit's tokens are the **same values** as the
  homepage's, so the two cannot drift. The rule everywhere: **prefix every selector and
  never edit the shared 345 KB `style.css`,** which ~200 views depend on. Three files have no page
  scope because nothing else emits their class names, or (the header) because there is no scope
  class to hang them off: `assets/css/promo-cards.css` (the two house promo cards),
  `assets/css/site-footer.css` (`.p411-foot`, the shared footer) and `assets/css/site-header.css`
  (the theme's `.hom-top` / `.news-top-menu` strip, **paint only** — the geometry stays the
  theme's, because `.news-top-menu` clears the fixed bar with a hard-coded `margin-top: 60px`).
  The footer and header sheets are loaded by `header.php` for every page, so those two blocks
  can't drift; the homepage loads neither, painting the same markup itself.
- **Colour:** navy `#20245B` for header, headings, footer and structure; pear green `#79B82A` as an
  **accent** on buttons, icons, links and labels; burgundy `#6D263D` for promotional and secondary
  bands (Featured/paid flags, section rules, date blocks); cream `#F7F5F0` / light grey `#F2F3F5`
  for grounds. Green as a large background is what the 2026-08 review asked to stop.
  Two traps when adding styles to any of these pages: `style.css` carries **bare `:before` /
  `:after` rules** (`position: absolute; font-family: "Material Icons"`) that apply to every
  element on the site, so every pseudo-element must state its own position, font and size; and
  `pearland-brand.css` paints the pager and `.form-control:focus` with `!important` in the 2025
  style sheet's olive `#909D3E`, which specificity cannot outrank.
- **Admin list paging:** every admin table carries a rows-per-page dropdown
  (10/20/30/50/all) rendered by `assets/admin/js/admin-table.js`. One value drives both
  pagers — the server-side CI pagination in ~20 `Admin` methods and the client-side one in the
  41 views that opt in — carried in `?show=` (**not** `per_page` — that is `CI_Pagination`'s reserved segment, which
  `reuse_query_string` strips from every link it builds) and resolved by `admin_per_page($default)`
  (`application/helpers/admin_helper.php`, autoloaded) and remembered in the session. A page can
  still pin its own with `data-page-size`; `0` means "show whatever the server sent", which is
  what the category lists use. `ADMIN_PER_PAGE_ALL` caps "all" at 5,000 rows.
- **Admin media fields:** `views/admin/partials/media_field.php` renders one image upload with
  what is already stored beneath it — lightbox, filename, and a Remove tickbox per image for
  galleries. Driven by `assets/admin/js/media-preview.js`, which is markup-first: `data-preview`
  on a file input previews the chosen files, `data-lightbox` (or any `<img>` inside
  `[data-media-grid]`) opens full size, `data-video-preview` renders a player per YouTube URL as
  you type. Used by the listing, news, blog, video and event edit screens — and by the
  **member-facing** `views/edit_listings.php`, which is the point: the owner's own edit form and
  the admin's now render the same control, after a round where only the admin one was fixed.
  `$mf_disabled` locks the file input for a plan without photos while leaving the Remove tickboxes
  live, so a downgraded owner can still delete a photo they can no longer replace. The public page
  has to link `assets/admin/css/media-preview.css` (via `page_css`) and the matching JS itself.
- **Public form validation:** `assets/js/form-validate.js` attaches to any `<form data-validate>`
  and validates from the markup's own attributes (`required`, `type`, `minlength`, `maxlength`,
  `pattern`, plus `data-match` for confirm fields). It binds in the **capture** phase, so a form
  that also posts over AJAX cannot send an invalid payload. The server validates independently —
  this is the courtesy layer, not the guard.
- **Advertising:** **28 public pages, 156 placements**; the full
  table, with sizes and prices, is **[`docs/AD_INVENTORY.md`](./AD_INVENTORY.md)**, generated
  from the source by `python3 scripts/ad_inventory.py` (re-run it after moving a zone). A page
  declares its zones with `User::pageAdSlots($shape)` — `full` (main + sidebar, 6 zones),
  `wide` (full-width, 3), `banner` (a form, 2) — which also **primes** them, so a six-zone page
  costs one query rather than six. Every `slot_key` is a `plans.slug` sold on `/pricing-plan`,
  so nothing here is page-specific inventory: buying a site-wide package fills the position on
  all of them. `/checkout`, `/checkout-success`, `/user/*` and `/admin/*` are deliberately
  ad-free, and so are `/login`, `/register`, `/privacy-policy`, `/terms-of-use`, `/pricing-plan`
  and `/claim-listing` — deliberately, see docs/AD_INVENTORY.md for why. An unsold zone shows a
  house creative, but only the first `ads_house_max_per_page` of them (default **2**, at
  `/admin/setting`); past that budget it renders nothing, so a page never looks like a wall of
  house ads. `User::thinPageAdCap()` drops that to 1 when a search or category comes back empty.
  The skyscraper is held back until sold, and the unsold top banner hides below 768px. **A sold ad
  is exempt from all of it.** `service_ads_enabled` switches advertising off entirely. In admin, a zone with no
  `slot_key` renders on no page and is shown as such in the booking dropdown rather than
  looking bookable, and `ad_zone_preview()` (view helper) gives every pricing row a thumbnail —
  a real upload if there is one, otherwise the generated wireframe in
  `assets/images/ads/previews/`.
- **Reusable blocks:** `partials/ad_slot.php` (one sellable ad zone; options `$slot_class`,
  `$slot_label`, `$slot_fallback`, `$slot_alias`, `$slot_creative`, `$slot_fallback_partial`),
  `partials/site_footer.php` (the site footer — rendered by `views/footer.php` **and** by the
  self-contained homepage, so the two can't diverge; contact details and the logo come from
  `/admin/setting`), `partials/service_promo.php` (the static website/hosting/SEO promo →
  `/contact-us`), `partials/banner_design_promo.php` (the ad/banner-design promo →
  `/request-service`; the unsold fallback for `right-side-banner-2` via `$slot_fallback_partial`) —
  neither promo is sold or rotated, so neither is ever empty — `partials/sales_help.php` (phone,
  email and a callback form for people who would rather not check out online; posts to
  `/contact-submit` with `return_to` and `topic`, and offers the `enquiry_departments()` list) — plus
  `partials/web_dev_cta.php`, `partials/seo_head.php`.
- **Brand artwork** is generated, not hand-drawn: `python3 scripts/generate_brand_art.py` writes
  `assets/images/placeholders/*.svg` (one per kind of business, plus news — served by
  `category_placeholder()`), `assets/images/ads/house-ad-{,burgundy-,navy-,cream-}{strip,wide,box,tall}.svg`
  (the creative an unsold zone falls back to, four palettes so no two unsold zones on a page repeat),
  `assets/images/ads/templates/` (editable advertiser banners + README) and
  `assets/images/ads/previews/` (one per `slot_key`: the zone drawn in place on a page wireframe
  and labelled with its size — the admin's "Ads Preview" thumbnails). It is deterministic and
  safe to re-run. The hero illustration `assets/images/home/hero-pearland.svg` is hand-authored.
- **Helpers** (autoloaded): `site_setting` (settings + feature flags), `image` (uploads/resize:
  `safe_upload_image`, `uploadAndResize`, `responsive_img`), `view` — escaping (`e`, `e_attr`,
  `e_url`, `e_nl2br`), `json_ld`, `pearland_slug`, `enquiry_departments()` / `normalise_enquiry_department()` /
  `enquiry_department_email()` (the departments a public form can address a message to — Sales,
  Customer Service, Billing… — editable at `/admin/setting`; these replaced a hard-coded list of
  nine named sales reps, most of whom have left), `form_recipient_email()` (one address for every public form, set
  at `/admin/setting`; falls back through the form's own override, `contact_email`, then
  `MAIL_FROM_ADDRESS`), `external_url()` (an operator-supplied link, given a scheme and
  refused unless it is http/https/mailto/tel), `listing_gallery_paths()` (decode
  `listings.listing_image_gallery`; the read and the write must agree, or adding one photograph
  wipes the rest), `category_placeholder()` (a listing with no
  photograph → the placeholder for its kind of business), plus the content helpers the
  events/news/videos pages share: `content_text()` / `content_excerpt()` (stored rich text → clean
  **unescaped** text; escape at output), `content_html()` (the same input → sanitised **HTML** with
  the author's paragraphs kept and every attribute dropped bar `href`/`title` — output is already
  safe, so never wrap it in `e()`; use it for a whole article, `content_text()` for a summary),
  `author_name()` (creator-name fallback, one definition for admin and public),
  `image_guidance()` (the recommended upload size shown beside each image field),
  `billing_interval_noun()` (`plans.billing_interval` free text → the noun the price copy needs, so
  a card can never read "$15 per" with nothing after it), `youtube_id()` / `youtube_thumb_url()` /
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
| Packages (customer) | `/pricing-plan` | `User::pricing_plan` → `pricing_plan.php` | lists all active `plans`; carries `partials/sales_help.php` (`#talk-to-us`) so a caller can buy by phone |
| Start a free plan | `/start-free` (POST) | `User::start_free_plan` → `Usermodel::activateFreePlan` | free tiers are excluded from cart/checkout, so this is the only way to get one; login required, never downgrades an existing subscription |
| Site search | `/search?q=`, `/search-suggestions` (AJAX) | `User::search` → `Usermodel::globalSearch` | Word-by-word: `searchTerms()` splits the query, `applySearchTerms()` ANDs the words and ORs the columns, `searchRelevanceOrder()` ranks name matches first. Sections switched off at `/admin/setting` are not searched |
| Sign in / register | `/login`, `/register` | `User::login`, `register` | Shared kit (`.p411-ct--auth`); the "who helped you sign up" list comes from `enquiry_departments()` |
| Contact | `/contact-us` → `/contact-submit` | `User::contact_us`, `contact_submit` | Shared kit; an email address **or** a phone number is enough; `return_to` sends callback requests back to the page they came from. The posted `topic` sets `enquiries.enquiry_type` — `package` for the `/pricing-plan` callback panel, `contact` for this page — which is what splits sales leads from general questions in admin. `department` (whitelisted against `enquiry_departments()`) records who it is for and picks the mailbox |
| Enquiries (admin) | `/admin/enquiries[?type=]`, `/admin/saved-enquiry` | `Admin::enquiries`, `saved_enquiry` | One tab per `enquiry_type`, newest first; Department column. Departments and their optional mailboxes are set at `/admin/setting` → Enquiry routing |
| Deals & coupons | `/coupons`, `/coupons/<id>` | `User::coupons`, `coupon_details` | Shared kit + 5 ad zones on the index |
| Services (web/IT) | `/services[?category=]`, `/services/<slug>` | `User::services`, `service_details` | Shared kit; each service page carries its own quote form |
| Custom quote | `/request-quote[?service=<slug>]` (GET form), `/request-service[/<slug>]` (POST) | `User::request_quote`, `request_service` | One POST handler for both forms — rate-limited 5/IP/hour, saved to `service_requests`, emailed to `site_setting('service_quote_email')` with the sender as Reply-To |
| Checkout | `/checkout?plan_id=X` | `User::checkout` / `helcim_init` / `process_checkout` | HelcimPay.js |
| Manage plan | `/user/my_account` | upgrade/downgrade/cancel | prorated upgrade |
| Renewal cron | — | `Cli::renew_subscriptions` | daily |
| Admin | `/admin` | `Admin::*` | |
| Site settings / services | `/admin/setting` | `Admin::admin_setting` | feature flags. `service_*_enabled` default **on**; `feature_duplicate_listing_enabled` defaults **off** (see `duplicate_listing_enabled()`) |
| Add a listing (admin) | `/admin/add_new_listings` | `Admin::add_new_listings` | with duplication off it redirects straight to `add_listing_scratch`; the chooser page only appears when the flag is on |
| Plan pricing/capabilities | `/admin/pricing` | `Admin::add/edit_pricing_plan` | |
| Events | `/event[?when=upcoming\|past\|all&q=]`, `/event-details/<slug>` | `User::event`, `event_details` | Homepage layout + 6 ad zones each; defaults to **upcoming**, widens to the archive by itself when nothing is scheduled |
| News | `/news[?category=<slug>&q=]`, `/news-details/<slug>` | `User::news`, `news_details` | Same shell; counted section strip; both hyphen and underscore detail URLs route, hyphen is canonical |
| Videos | `/videos[?category=<slug>&q=]`, `/video-details/<slug>` | `User::videos`, `video_details` | Same shell; YouTube thumbnail fallback when no still was uploaded |
| Blog | `/all-blog[?q=&page=]`, `/blog-details/<slug>` | `User::all_blog`, `blog_details` | Both on the shared kit (same shells as `/news` and `/news-details`). Publish date editable in admin — see migration 0021. Bodies are CKEditor markup; render with `content_text()` + `nl2br(e())`, never raw |
| Other content types | products/classifieds/houses/rentals | `User::*` + admin CRUD | gated by `service_*_enabled`; still on the old theme layout |

## Key DB tables
`user`, `listings` (+ `listing_categories`, `listing_sub_categories`, `listing_other_info`),
`claim_listings`, `plans`, `plan_durations`, `subscriptions`, `transactions`, `payment_cards`,
`user_usage_tracking`, `site_settings`, content tables (`events`, `news`, `blogs`, `products`,
`classifieds`, `house`, `rental`, `video`, `coupons`), `ads` / `ads_prices`.
`listing_sub_category_map` (0023) is the listing↔sub-category join — a listing may sit under
several. `listings.sub_category_id` survives alongside it holding the **first** selection,
because the admin table, the CSV export and several model joins still read it.
`ads.category_id` (0024) is nullable: NULL runs an ad in its slot everywhere, an id restricts
it to that listing category and outranks an untargeted booking in the same slot.
Core tables are **not** in repo migrations (they predate git); migrations `0010+` add columns to
them. Get the run order from `database/README.md`.

## Run / deploy
- Copy `.env.example` → `.env`; fill DB, SMTP, `HELCIM_API_TOKEN`, `HELCIM_WEBHOOK_VERIFIER_TOKEN`.
- Run migrations `0000`→latest (or `database/migrations_combined_phpmyadmin.sql`).
- Activate services in `/admin/setting`; set plan capabilities in `/admin/pricing`.
- Cron daily: `php index.php cli renew_subscriptions`.

## Deeper docs
`CHANGELOG.md` (history) · `README.md` (setup/ops) · `PROJECT_AUDIT_REPORT.md` (full audit) ·
`REVIEW_AND_ISSUES.md` (known issues) · `database/README.md` (migration order) ·
`docs/AD_INVENTORY.md` (every ad zone, per page, with sizes — generated).
