# Pearland411 — Review &amp; Issues Log

Generated after the security/feature work on
`claude/audit-codeigniter-repo-khLDD` (27 commits). Walks the
public site, admin panel, forms, and the wider plumbing and
lists everything I'd want fixed before launch.

Severity scale:
- **Critical** — data-loss / security / completely broken core flow.
- **High** — page loads broken, form does nothing, missing core handler.
- **Medium** — silent bug, dead code, fragile UX, content mismatch.
- **Low** — polish / nits / future cleanup.

---

## 1. Missing view files (pages that 500 when reached)

These nine view files are referenced by controller methods but
**do not exist on disk** — the page will throw an "Unable to load
the requested file" CI error.

| Severity | File expected | Referenced from |
|---|---|---|
| High | `application/views/my_profile.php` | `User::my_profile` |
| High | `application/views/user_classifieds.php` | `User::user_classifieds` |
| High | `application/views/advanced_search.php` | `User::advanced_search` |
| High | `application/views/all-category-search.php` | `User::category_search` |
| High | `application/views/admin/news_details_n.php` | `Admin::news_details/{slug}` |
| High | `application/views/admin/rental_preview.php` | `Admin::rental_details` (one branch) |
| High | `application/views/admin/user_usage_stats.php` | `Admin::user_usage_stats` |
| High | `application/views/admin/admin_subscription_modal.php` | `Admin::subscription_view` |
| Medium | `application/views/admin/sub_admin_create.php` | I deleted it intentionally in commit 17 (subsumed into `sub_admin_edit.php`); confirm the `add` controller branch passes `$admin_row = null` instead. (It does — verified.) |

Action: either create stub views or remove the unreachable
controller methods entirely.

## 2. Dead allow-list entries

`User::__construct` (around line 30–55) lists six public methods
that **don't exist in the class**:

- `forget_password` (typo for `forgot_password`)
- `news_lists` (only `news_list` exists, and only on Admin)
- `news_preview`
- `listing_preview`
- `listingdetails`
- `test_account`

Same typo `forget_password` is in `Admin::__construct`. **Medium** —
these aren't security holes, but they're confusing and obscure
which methods are actually public. Drop them.

## 3. Public forms that don't submit anywhere (or post to 404)

The most painful set, because the UX looks normal — user types,
clicks submit, gets either a thank-you alert or a silent reload —
and the data never reaches the server.

| Severity | View | Form | What's broken |
|---|---|---|---|
| **Critical** | `application/views/contact-us.php:346` | `#p411ContactForm` | No `action`/`method`; JS handler at L404 just calls `alert('Thank you...')`. **Every Contact Us submission is silently discarded.** |
| **Critical** | `application/views/post_your_ads.php:17` | `#create_ads_form` | Posts to `base_url('post-ads')`, but `User::post_ads` does not exist (only `ads_summary` is wired). Every ad-purchase POST 404s. |
| High | `application/views/footer.php:17` | `#home_slide_enquiry_form` (global enquiry slide-out shown across many pages) | `method="post"` but no `action` — submits to whatever URL is current. Hidden fields `listing_id=0`, `listing_user_id=0`, `enquiry_source="Website"` mean the row lands in `enquiries` orphaned to nothing. Also: hardcoded category dropdown (lines 32–49) — completely unrelated to live `listing_categories` data. |
| High | `application/views/all-listing2.php:308`, `all-products.php:336` | Same global enquiry form | Same problem. |
| High | `application/views/index.php:3383` | `#newsletterForm` | Uses **`fetch()` POST without a CSRF token**. With CSRF on (commit 7), `User::subscribe_newsletter` will return 403. The widget appears to work in the browser (we still show "Subscribing…") but the server rejects every signup. **The whole newsletter signup is dead.** |
| Medium | `application/views/login.php:132`, `register.php:107` | "Get quote" modal `<form>` | No action/method; pressing submit reloads the page with no handler. |
| Medium | `application/views/checkout.php:343` | Checkout form posts to `process-checkout` | Method exists but logs `"process_checkout called before live payments are wired; refusing."` and bounces. Expected behaviour until AuthNet is wired, but worth flagging. |

**Phone-number `pattern`** on the global form / multiple enquiry
forms is `[7-9]{1}[0-9]{9}` — that's the **Indian mobile-number
regex**. US Pearland numbers (area code 281, 832, 713, etc.) start
with 2/7/8, so most US visitors can't submit. Replace with a
permissive `[0-9+()\- ]{7,20}` or drop the pattern.

## 4. Hardcoded sample/foreign data in production code

| Severity | File | Issue |
|---|---|---|
| High | `application/views/footer.php:32–49` | Hardcoded list of 13 categories (Wedding halls, Pet shop, …). These don't match the live `listing_categories` table; the dropdown is misleading and any selection is meaningless. Should render from `$header_categories`. |
| High | `application/views/index.php:2860–2940` | Six **hardcoded "business" cards** with sample data (Bella's Bistro, Pearland Fit Gym, Tony's Pizzeria, Care+ Medical Clinic, AutoCare Plus, Luxe Hair Salon) pointing at external Unsplash images. This block should either be removed or replaced with real `$latest_listings`. |
| Medium | `application/views/event_details.php:80` (legacy) | Static Google Maps embed for "New York, NY" — looks left over from a template. Confirm current event_details no longer renders this. |
| Medium | `application/views/index.php:14–110` | An entire **`SimpleSearchHandler` PHP class defined inside the view**, with `error_reporting()` override and `include_once 'Usermodel.php'`. Dead parallel-search code that never executes correctly. Delete; the real `/search` already works via `User::search`. |
| Low | `application/views/admin/sub_admin_*.php` | These already replaced. ✓ |

## 5. Routing / controller wiring issues

| Severity | Where | Issue |
|---|---|---|
| High | `User::index` (line 156–162) | Sniffs `$_SERVER['REQUEST_URI']` with a regex to detect `/listing-details/...` and dispatches manually, **even though** `application/config/routes.php` already maps that URL via an explicit route. The home page also accidentally renders the listing-details controller for any URL containing the substring `listing-details/` anywhere. Remove the manual re-routing. |
| High | `Admin::news_details/{slug}` | Loads view `news_details_n` which doesn't exist (see §1). |
| High | `Admin::rental_details/{slug}` | One branch loads `admin/rental_preview.php` which doesn't exist. |
| High | `Admin::subscription_view/{id}` | Loads `admin_subscription_modal.php` which doesn't exist. |
| High | `Admin::user_usage_stats/{user_id}` | Loads `user_usage_stats.php` which doesn't exist. |
| Medium | `Admin::marketplace_list` | Method exists; its view is incomplete / renders only the admin header. The whole `marketplace_*` module duplicates `products_*` (audit M4) — recommend deleting the module entirely. |
| Medium | `User::checkout` is in the public allow-list | Anyone (signed in or not) can visit `/checkout?plan_id=X` and see the disabled-payments page. Restrict to `logged_user` — keep the page UX, just require sign-in first. |
| Medium | `User::add_enquiry` is public | Anonymous POST is intentional + rate-limited, but flag for review: should we require login for at least the listing-claim-style enquiries? |
| Medium | `User::handle_clean_url` | Implementation 301-redirects but isn't in the public allow-list, so anonymous visits go to login first. Either add to allow-list or delete the method. |
| Medium | `Admin::getSubscriptionDataForDashboard` | Declared `public` but is used as a helper. Reachable as `/admin/getSubscriptionDataForDashboard/{id}` by any super-admin via direct URL. Make it `private`. |
| Low | `Admin::pricing` and `Admin::pricing_plan` | Render the same view. Pick one and delete the other. |
| Low | `User::newsletter_confirm` | Emits inline HTML via `echo '<!doctype html>...'` instead of using a real view. |
| Low | `Admin::saved_enquiry()` | Body is commented out, method is empty stub. Delete or implement. |
| Low | `User::blog_post()`, `User::news_post()` | One-line empty stubs. |

## 6. Dead / commented-out code (Medium)

- `application/controllers/User.php` lines ~2442–2890: six fully
  commented-out `add_listing_step_*` method bodies (~450 lines).
- `Admin::saved_enquiry()` commented out.
- `application/views/index.php` lines 14–110: the dead
  `SimpleSearchHandler` block (see §4).
- `application/views/index.php` lines 2860–2940: hardcoded
  sample listings (see §4).

## 7. Frontend / UX bugs

| Severity | Where | Issue |
|---|---|---|
| High | `User::index` slug-sniff (above) | Means the home page may load `listing_details` instead of the home view when a request URI happens to contain `listing-details/` (e.g. someone manually visits `/?ref=listing-details/foo`). |
| High | `assets/js/csrf.js` only attaches the token via `jQuery $.ajaxPrefilter` | Anything using raw `fetch()` (e.g. the newsletter form, future SPA-style features) bypasses the prefilter and gets a 403. Extend the shim to also patch `window.fetch`, OR update the newsletter form to use jQuery. |
| Medium | `application/views/header.php` viewport meta | I removed `maximum-scale=1` in commit 12 — confirm the header.php in the codebase reflects that. (It does.) ✓ |
| Medium | All "enquiry_mobile" `pattern` attributes | Indian regex, see §3. |
| Medium | `application/views/listing_details.php:1497–1554` "Get quote" modal | Works (posts to add_enquiry) but the modal-trigger `#quote` is also used by `register.php`, `login.php`, and a few other places where the modal markup is **defined but the matching `<div id="quote">` is missing** — clicking the trigger silently does nothing on those pages. |
| Medium | `application/views/checkout.php` | Loads but hidden card fields are now gone (commit 6). Confirm the "Coming soon" badge is visible enough; today it's a 16px notice — should be a bigger heading. |
| Medium | `application/views/admin/dashboard.php` | Stats grid uses `sys_getloadavg()` and `disk_total_space('/')` — fragile on shared hosts (PHP may not have permission to read `/`). Wrap in try/catch + fall back to "—" if not available. |
| Low | `application/views/footer.php` | Loads `select-opt.js` twice (lines 198 and 202). |
| Low | `application/views/footer.php` | Loads Bootstrap 4 once from CDN and once from `assets/`. Same for jQuery UI. |
| Low | `application/views/header.php` | Loads `html5shiv.js` + `respond.min.js` (IE8/9 polyfills) — drop. |
| Low | All list pages | "Get quote" buttons everywhere even when the visitor isn't on a business page — clutter. Audit which CTAs make sense per context. |

## 8. Admin panel polish (Medium / Low)

- `Admin.php` lines 10–319 declare methods (`add_house`,
  `house_list`, `newsletter_subscribers`, …) **above** the
  `__construct()`. Works, but it's a sign of a sloppy merge.
  Move them below the constructor for code-review sanity.
- `Admin::edit_blog` loads `admin/user_edit_blog.php` (the
  user-side blog editor view). Inconsistent — should be
  `admin/edit_blog.php` or similar.
- `Admin::product_details` loads the **public** header/footer
  around `admin/product_details` — admin chrome breaks on that
  page.
- `Admin::blog_details`, `Admin::video_details`,
  `Admin::event_details`, `Admin::classifieds_details` — same
  inconsistent chrome (public header + footer with admin
  body). Pick one (recommend admin chrome).
- `Admin::classifieds_details` loads the **public** view
  `application/views/classifieds_details.php` from inside the
  admin panel.
- Admin "Newsletter Subscribers" list page renders raw enquiry
  text — fine but apply `e()` for hardening (same pass as
  commit 26 for the other admin lists).
- `application/views/admin/dashboard.php` has a large inline
  `<style>` block. Move to `assets/admin/css/`.

## 9. Data / model issues

| Severity | Where | Issue |
|---|---|---|
| Medium | `Usermodel::globalSearch` (line ~3302) | Has typo bucket `'rental'` vs the table `rental` — correct here, but `searchRental` uses an inconsistent column list. Audit each `searchX()` helper for actual column names that match the dump (`rental.rental_title`, `house.house_title`, etc.). |
| Medium | `Adminmodel::deleteAdmin` correctly refuses to delete the last super-admin, but `Admin::sub_admin_delete` calls `deleteAdmin` and then sets a flash even on failure — the flash message wording could be tighter. |
| Medium | `Usermodel::login` rejects unverified accounts (commit 9). Confirm the seeded admin / existing legacy users are auto-marked `email_verified = 1` by migration 0001. They are (verified). ✓ |
| Medium | `listings` table has no FK constraints (audit D2). When `Admin::claim_approve` writes `listings.user_id`, there's no constraint to keep it consistent if that `user` is later deleted. Add `ON DELETE SET NULL` once you're ready to risk a migration. |
| Low | `Usermodel::getActiveCoupons` (commit 20) joins to `user` and `listings` on `coupon_user_id` — but `coupons` table dump shows the column is the listing-owner ID, not necessarily the listing ID. If a user has multiple listings, the join picks the first. Acceptable. |

## 10. Performance

| Severity | Where | Suggestion |
|---|---|---|
| Medium | `Sitemap::index` | Selects every listing/news/blog/event/classified/product without LIMIT. At 30k+ listings the rendered XML can blow the 50k-URL sitemap limit. Split into per-resource child sitemaps + `<sitemapindex>` root. |
| Medium | `application/views/index.php` | Still loads many external Unsplash images for the hardcoded "sample" listings (see §4). |
| Medium | `application/views/footer.php` | `select-opt.js` loaded twice; Bootstrap and jQuery UI loaded from CDN AND from `assets/`. |
| Low | No HTTP caching headers from CI for static views. Add `Cache-Control` headers in a global hook for assets. |

## 11. SEO

| Severity | Where | Issue |
|---|---|---|
| Medium | `application/views/event_details.php` | Per-page meta_description is set, but `event_map` is rendered raw HTML (admin can paste an iframe). Browsers index that as content; switch to a `lat/lng` field + server-rendered map iframe. |
| Medium | `Sitemap::index` | Always declares `priority=1.0` for `/`, which is the convention but most search engines ignore. Low impact. |
| Low | `robots.txt` | The `Sitemap:` line hardcodes `https://new.pearland411.com/sitemap.xml`. If you move domains, update. |

## 12. Newsletter signup is dead (combined finding)

Worth its own callout because the entire feature is broken:

- `index.php:3383` form uses `fetch()` without CSRF.
- CSRF enforcement (commit 7) returns 403 on every POST.
- The footer `csrf.js` shim only patches **jQuery** AJAX.
- Visitor sees the spinner, then "An error occurred." every time.

**Fix:** extend `assets/js/csrf.js` to wrap `window.fetch` too,
OR rewrite the newsletter form's `fetch()` call to first read the
`<meta name="csrf-token">` value and append it to the FormData.
Either is a 10-minute fix; ship as a single commit.

## 13. Contact form is dead (combined finding)

- Live page: `application/views/contact-us.php:346`.
- `<form id="p411ContactForm">` — no action, no method.
- JS at line 404: `e.preventDefault(); alert('Thank you...')`.
- **Every Contact Us message ever sent has been silently discarded.**

**Fix:** add a `User::contact_submit` POST action that validates,
optionally rate-limits, and (a) inserts into `enquiries` with
`enquiry_source = 'contact-us'`, (b) emails MAIL_FROM_ADDRESS,
(c) acknowledges to the sender. Same shape as the existing
`request_service` flow.

## 14. Ad purchase flow broken (combined finding)

- `User::ads_summary` (read-only "your ads" page) exists.
- `application/views/post_your_ads.php:17` posts to `post-ads`.
- No `post_ads` method, no route → 404.

**Fix:** either implement `User::post_ads` POST handler (would
hook into Authorize.Net when keys arrive), or remove the
`post_your_ads.php` page until ads self-serve is built.

---

## Recommended remediation order

1. **Fix the three dead forms** (newsletter, contact, post-ads) — visitors are literally typing into voids. Half-day.
2. **Delete the dead allow-list entries and dead methods** (`forget_password`, `news_lists`, `news_preview`, `listing_preview`, `listingdetails`, `test_account`, the six commented `add_listing_step_*` methods, `SimpleSearchHandler` in `views/index.php`). Half-day.
3. **Create or remove the nine missing views** (§1). Quick decision per view; ~2 hours.
4. **Replace the hardcoded sample listings + categories** in `index.php` and `footer.php`. ~2 hours.
5. **Fix the phone-number regex** on every enquiry form (Indian → US-permissive). ~30 min.
6. **Move admin methods below `__construct()`** in `Admin.php` (cosmetic, lowers regression risk). ~30 min.
7. **Make `User::checkout` require login** + `Admin::getSubscriptionDataForDashboard` private. ~15 min.
8. **Fix `User::index` slug-sniff** — remove the manual REQUEST_URI parse. ~10 min.
9. **Remove duplicate JS loads** in footer + drop the IE polyfills in header. ~10 min.
10. **Standardize admin chrome** (one of public/admin header+footer across all admin views). ~1–2 hours.

That whole list is **~1 dev-day** of focused work. Most of it is
deletion + small wiring, no architectural changes.

After that, the next strategic pieces remain:
- Authorize.Net wiring (blocked on credentials).
- Replace seed services with the real spacecentersystems.com list.
- Sitemap split for &gt;50k URLs.
- Drop the `marketplace_*` orphan module.

Long-term, the §12 rebuild recommendation from
`PROJECT_AUDIT_REPORT.md` still stands.

---

## 15. Additional findings from full page walkthrough

Surfaced during a second-pass review of every public detail page.
Re-grouped into the same severity buckets so they slot into the
remediation list above.

### Critical

- **`routes.php:124` blog detail route is broken.**
  `$route['blogs-details/(:any)'] = 'user/blogs-details/$1'`. With
  `translate_uri_dashes=TRUE` that resolves to `user/blogs_details`
  (plural) — but the controller method is `blog_details`
  (singular). **Every `/blogs-details/<slug>` URL 404s.** The
  search results page (`search.php:615`) links here, so every blog
  result is dead. Routes file even has a comment "blogs - Not
  working". **Fix:** change the route to
  `'user/blog_details/$1'`. One-line fix; ship immediately.

- **`contact-us.php` is a full standalone HTML document.**
  Lines 1–9 contain `<!DOCTYPE>`, `<html>`, `<head>` and line 412
  has the closing `</body></html>`. The controller wraps it with
  `header.php` (which already emits `<html><head><body>`) +
  `footer.php`, producing a doubly-nested HTML document. Browsers
  render *something*, but it's invalid markup. **Fix:** rewrite as
  a fragment (drop the `<html>`/`<head>`/`<body>` wrapper, keep
  the inner content).

- **Hardcoded sample social URLs on every listing page.**
  `listing_details.php:765–789` "COMPANY BADGE" hardcodes
  `https://www.facebook.com/`, `https://twitter.com/`,
  `https://www.youtube.com/` plus icon URLs pointing at
  `bizbookdirectorytemplate.com`. Every listing shows the same
  dummy social links — there's no per-listing customization.
  Either wire these to `$listing->facebook_url` etc. (add columns
  if missing) or remove the block.

- **`/all-news`, `pricing-details`, `post-your-ads` links 404.**
  `index.php:3047, 2551, 2567, 2484` point at these URLs but no
  matching controller method exists.

- **Image fallback bug — `<img src="<?= base_url($news->news_image) ?>"`**
  in `news_details.php:208, 405` and `blog_details.php:238, 497`.
  When the column is NULL, `base_url(null)` returns the site root
  URL, so the page renders the home page as a 404'd image. Add an
  `empty()` check (the pattern is already used elsewhere — apply
  here too).

### High

- **`event_details.php:407` echoes `$event->event_map` as raw HTML.**
  Admins can paste arbitrary markup; today's UI lets them paste
  full `<iframe>` tags. Stored XSS sink for any admin account
  (after the role gating that's a smaller risk, but not zero).
  Replace with a `lat/lng` column + a server-rendered iframe.

- **N+1 queries in `events.php:96–107`** — calls
  `$CI->usermodel->getSingleValue(...)` per row inside the loop to
  fetch the event owner's name. Move the join into the controller.
  Same problem in `news_details.php:284–289` and
  `blog_details.php:367–371` which run raw `$this->db->...` queries
  inside the view to fetch publisher info.

- **`all-listing.php:220–221`** still echoes
  `<?= $value->listing_name; ?>` and `listing_address` raw — the
  earlier escape sweep (commits 10, 26) missed this view's `ne-2`
  card body. Wrap with `e()` / `e_attr()`.

- **External-CDN production fallbacks.**
  `event_details.php:308, 549` fall back to
  `https://placehold.co/600x400/...`; `event_details.php:424`
  defaults the user avatar to a `freepik.com` URL. Move to local
  `assets/images/` fallbacks so the site doesn't depend on third
  parties for default imagery.

- **`index.php` hardcoded image paths.** Multiple references mix
  `/assets/...` (root-relative), `../assets/...` (parent-relative),
  and `assets/...` (no prefix). On sub-paths these break. Use
  `base_url('assets/...')` consistently.

- **Search form action is relative.** `search.php:390`
  `<form action="search">` — submitting from `/search/foo` posts
  to `/search/foo/search` and 404s. Use
  `action="<?= base_url('search') ?>"`.

- **`flashdata()` echoes still unescaped in several pages:**
  `listing_details.php:492, 689, 695`,
  `news_details.php:217`, `blog_details.php:257`,
  `event_details.php:320`. The values today are admin-controlled
  but the pattern is fragile — wrap with `e()`.

- **`$listing->display_address` / `display_phone`** accessed
  unguarded throughout `listing_details.php` (lines 12, 78, 83,
  108, 116). PHP 8.1 emits deprecation warnings on NULL → string
  coercion; PHP 8.2+ may error on stricter setups. Add `?? 0`
  or wrap with `isset()`.

- **`listing_details.php:288–330, 314–324, 384–390`** has debug
  `echo "Listing Image Gallery: ..."` blocks that leak DB content
  into HTML comments visible in page source. Remove.

### Medium

- **Homepage SEO is bare.** `User::index` passes no `$title`,
  meta description, canonical, or JSON-LD. We render a single
  hardcoded `<title>Home - Pearland411</title>` in `index.php:137`
  — but `header.php` would render a proper title if `$title` were
  set. Wire it.

- **Category / news / event / blog landing pages also have no
  per-page meta description / canonical / `ItemList` JSON-LD.**
  Only the detail pages got the SEO pass in commit 12.

- **`all_listing` slug-fallback logic in views.** Lines 249–258,
  428–434 of `all-listing.php` build URLs from a `listing_slug ??
  listing_id` fallback inline. Move into the controller (or a
  helper) so all view files share one rule.

- **`coupons.php:36`** links to `listing-details/<slug>` even when
  the coupon has no linked listing — produces `/listing-details/`
  → 404. Guard with `if (!empty($c->listing_slug))`.

- **`coupons.php:45`** `strtotime($c->coupon_end_date)` with a
  null `coupon_end_date` returns false; `date()` then prints
  1970-01-01. Defaults look bad; guard.

- **`all-listing.php:177`** hardcodes `<h1>Business</h1>` — when
  the user filters by category the title should reflect the
  selection.

- **Mixed link styles in `all-listing.php`.** Lines 225, 512, 525
  variously use `base_url('user/listing-details/...')` and
  `base_url('listing-details/...')`. The first form bypasses the
  clean route. Standardize.

- **`contact-us.php:20` background image** uses `./assets/...`
  relative — 404s from `/contact-us/`. Use `base_url('assets/...')`.

- **`contact-us.php:348–365` `<label>` tags missing `for=`** —
  not associated with their inputs (screen-reader / a11y miss).

### Low

- `index.php:2772–2941` images use `alt=""` on many tags.
- `news_details.php` etc. — same pattern; add per-image `alt`.
- `blog-posts.php:46` links to `user/blog-details/...` (via the
  catch-all + translate-dashes route). Works, but inconsistent
  with the explicit `blogs-details` route (which is also broken
  per §15.Critical).
- `index.php:2413` and footer links use bare relative paths
  (`href="index"`, `href="user/all_listing/..."`) — fine at site
  root, broken at sub-paths.
- `news.php:271`, `blog-posts.php` and similar — `$x->news_slug`
  / `$x->blog_slug` accessed without `??` fallback. PHP 8.1
  deprecation; will warn in logs.

---

### Combined remediation impact

The third-pass findings stack onto the original list. Most are
small (1-line route fix, missing escape, hardcoded URL → variable
swap) but two are blocking:

1. **Fix `routes.php:124`** — every blog detail link in the search
   results is currently 404'ing.
2. **Rewrite `contact-us.php` as a fragment** — invalid HTML
   today; this is on top of the form being dead.

Updated total work estimate to clear the consolidated list:
**~1.5–2 dev-days** (was 1 dev-day, plus this pass added ~half a
day of additional fix work).
