# Changelog

All notable changes to Pearland411 are recorded here, newest first. Format loosely follows
[Keep a Changelog](https://keepachangelog.com/). When you make a change, add an entry with the
migrations and key files touched so the next session doesn't have to re-read the codebase.

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
