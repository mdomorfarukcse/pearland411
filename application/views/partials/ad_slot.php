<?php
/**
 * Render one ad zone.
 *
 *   <?php $slot = 'home-hero'; include APPPATH.'views/partials/ad_slot.php'; ?>
 *
 * Three states, in order of preference:
 *   1. A live ad targeting this zone (ads.ad_status = 1, inside its date window).
 *   2. A built-in default — either a house partial ($slot_fallback_partial) or
 *      a house creative: "advertise here", the Space Center Systems
 *      web-development ad, or the ad/banner-design ad. Which creative is fixed
 *      per zone (see $__house_map below), so a slot always shows the same thing.
 *   3. Nothing at all, when the includer sets $slot_fallback = false.
 *
 * Variables consumed (the includer sets them; only $slot is required):
 *   $slot          (string) — slot_key. See migrations 0006 + 0017 + 0019.
 *   $slot_class    (string) — extra CSS class on the wrapper.
 *   $slot_label    (string) — override the "Sponsored" microlabel.
 *   $slot_fallback (bool)   — false = render nothing when no ad is sold.
 *   $slot_category (int) — listing category being browsed. An ad booked for
 *                  that category wins this slot; without it only untargeted
 *                  bookings run. See migration 0024.
 *   $slot_alias    (string|array) — older slot_key(s) this position replaced.
 *                  Only consulted when nothing is sold on $slot, so inventory
 *                  booked against a retired zone keeps running.
 *   $slot_creative (string) — force which house creative fills an unsold zone,
 *                  overriding the per-slot map below. Used where the page
 *                  already carries a permanent web-services block and would
 *                  otherwise repeat it. One of:
 *                    house-ad           pear green   "advertise where Pearland searches"
 *                    house-ad-burgundy  burgundy     "reach thousands of Pearland customers"
 *                    house-ad-navy      navy         "featured business placement available"
 *                    house-ad-cream     cream        "get your business in front of local customers"
 *                    scs-ad             web development
 *                    banner-design      ad / banner artwork
 *                  Four palettes exist because two unsold zones on one page
 *                  used to show the same green rectangle twice, which is what
 *                  made the site look like it had no advertisers.
 *   $slot_fallback_partial (string) — filename under views/partials/ to render
 *                  in place of the default creative when nothing is sold, e.g.
 *                  'banner_design_promo.php'. A card can say what the service
 *                  is, what it costs and where to click; a flat image can't.
 *                  Ignored when an ad IS sold — a paying advertiser always
 *                  wins the position. Ignored too when $slot_fallback = false.
 *
 * The zone's pixel size comes from ads_prices.size and is emitted as
 * width/height so the browser reserves the box before the image loads.
 *
 * Degrades to nothing when migration 0006 hasn't been run.
 */

if (!isset($slot) || $slot === '') return;

// Global off-switch. Deliberately unseeded — the helper's default keeps
// advertising on unless an operator turns it off in /admin/setting.
if (site_setting('service_ads_enabled', '1') === '0') {
	unset($slot_class, $slot_label, $slot_fallback, $slot_alias, $slot_creative, $slot_fallback_partial, $slot_category);
	return;
}

// CI loads partials in the caller's scope, so reach the model via the instance.
// No `&` — get_instance() returns an object, and assigning a function's return
// value by reference raises "Only variables should be assigned by reference".
$__ci = get_instance();

// The listing category being browsed, when the page has one. An ad booked
// against that category wins the slot over an untargeted booking; pages with
// no category only ever run untargeted ads. See migration 0024.
$__ad_cat = isset($slot_category) && $slot_category !== '' ? (int) $slot_category : NULL;

$__ad   = method_exists($__ci->usermodel, 'getAdForSlot')  ? $__ci->usermodel->getAdForSlot($slot, $__ad_cat)  : null;
$__meta = method_exists($__ci->usermodel, 'getAdSlotMeta') ? $__ci->usermodel->getAdSlotMeta($slot) : null;

// Nothing sold on this zone? Fall through to any zone it superseded, so an
// ad an operator booked against a retired slot_key still gets its impressions
// instead of silently disappearing when a page is re-cut.
if (!$__ad && !empty($slot_alias) && method_exists($__ci->usermodel, 'getAdForSlot')) {
	foreach ((array) $slot_alias as $__alias_key) {
		if (empty($__alias_key)) continue;
		$__ad = $__ci->usermodel->getAdForSlot($__alias_key, $__ad_cat);
		if ($__ad) break;
	}
}

$__fallback = isset($slot_fallback) ? (bool) $slot_fallback : true;
if (!$__ad && !$__fallback) {
	unset($slot_class, $slot_label, $slot_fallback, $slot_alias, $slot_creative, $slot_fallback_partial, $slot_category);
	return;
}

// ---- How many house ads this page is allowed ----------------------------
// An unsold zone falls back to a house creative. With six zones on a page
// that meant six house ads and no advertisers, which does not read as
// premium inventory — it reads as "nobody advertises here". Cap how many a
// page may fill; the rest render nothing at all. Includes run in page order,
// so the budget spends itself on the positions nearest the top, which are
// the ones worth advertising.
//
//   $ad_house_cap  per-page override the controller may set. Browse pages
//                  with no results set it to 1: a "nothing found" page
//                  carrying three house ads is advertising outnumbering
//                  content, which is the one layout search engines actually
//                  penalise. -1 means no cap.
//   ads_house_max_per_page  the site-wide default, editable at /admin/setting.
//
// A SOLD ad is never touched by this. Someone paid for the position.
if (!$__ad) {
	$__cap = isset($ad_house_cap) && $ad_house_cap !== ''
		? (int) $ad_house_cap
		: (int) site_setting('ads_house_max_per_page', '2');

	if ($__cap >= 0) {
		$__house_shown = isset($GLOBALS['__pearland411_house_ads'])
			? (int) $GLOBALS['__pearland411_house_ads'] : 0;

		if ($__house_shown >= $__cap) {
			unset($slot_class, $slot_label, $slot_fallback, $slot_alias, $slot_creative,
				$slot_fallback_partial, $slot_category, $__ci, $__ad, $__meta, $__fallback, $__cap,
				$__house_shown, $__ad_cat);
			return;
		}
	}

	// Past the gate: whatever this include renders below is a house ad.
	$GLOBALS['__pearland411_house_ads'] =
		(isset($GLOBALS['__pearland411_house_ads']) ? (int) $GLOBALS['__pearland411_house_ads'] : 0) + 1;
	unset($__cap, $__house_shown);
}

// ---- Unsold, and the includer supplied a house partial -------------------
// Render it and stop: the sizing, the "Sponsored" microlabel and the creative
// picking below all describe an <img> the partial isn't going to emit.
if (!$__ad && !empty($slot_fallback_partial)) {
	$__partial = APPPATH . 'views/partials/' . basename((string) $slot_fallback_partial);
	// Clear the per-include options before handing over — the partial is a
	// sibling of this one and must not inherit this include's settings.
	unset($slot_class, $slot_label, $slot_fallback, $slot_alias, $slot_creative,
		$slot_fallback_partial, $__ci, $__ad, $__meta, $__fallback);
	if (is_file($__partial)) {
		include $__partial;
	}
	unset($__partial);
	return;
}

// ---- The zone's true pixel box, e.g. "728x200" ---------------------------
$__w = $__h = 0;
$__size = '';
if ($__ad && !empty($__ad->size))         $__size = $__ad->size;
elseif ($__meta && !empty($__meta->size)) $__size = $__meta->size;

if ($__size !== '' && preg_match('/^\s*(\d{2,4})\s*[xX\*]\s*(\d{2,4})\s*$/', $__size, $__m)) {
	$__w = (int) $__m[1];
	$__h = (int) $__m[2];
}

// ---- Pick a default creative shaped for that box ------------------------
// The generator writes one creative per size the catalogue sells, so the
// artwork below is normally drawn at this zone's exact pixel box and cannot
// be cropped. The four generic buckets remain, for a zone whose size it does
// not know about:
//   strip  970x90   ratio >= 6      — leaderboards
//   wide   900x300  2.0 <= r < 6    — 728x200, 728x250, 970x250
//   box    600x500  0.9 < r < 2.0   — 300x250
//   tall   300x600  ratio <= 0.9    — 300x600
//
// Matching only by bucket is what broke the top banner: `top-banner-site-wide`
// is 700x100 (7:1), the strip is 970x90 (10.8:1), and covering the one with
// the other threw away 35% of the width — the headline lost its first word and
// the call-to-action button was sliced in half.
$__ratio = ($__w && $__h) ? ($__w / $__h) : 3.0;
if     ($__ratio >= 6.0) $__shape = 'strip';
elseif ($__ratio >= 2.0) $__shape = 'wide';
elseif ($__ratio <= 0.9) $__shape = 'tall';
else                     $__shape = 'box';

// Which house creative fills an unsold zone. Explicit per slot rather than
// hashed: the sidebar deliberately carries one web-development ad and one
// ad-design ad, and every other zone sells the space itself — in a palette
// chosen per position, so no two unsold zones on a page repeat each other.
$__house_map = array(
	'top-banner-site-wide'    => 'house-ad-navy',
	'middle-banner-category'  => 'house-ad-cream',
	'bottom-banner-site-wide' => 'house-ad',
	'right-side-banner-1'     => 'house-ad-burgundy',
	'right-side-banner-2'     => 'banner-design', // ad / banner artwork
	'right-side-banner-3'     => 'house-ad-navy',
	'listing-sidebar'         => 'scs-ad',
	'news-sidebar'            => 'banner-design',
	// The footer strip sits directly under `bottom-banner-site-wide`, and both
	// fell through to the unsuffixed green default — two identical green
	// banners stacked at the foot of every page, which is the exact thing this
	// map exists to stop. The web-services pitch is the one creative no
	// rendered zone uses (`listing-sidebar` is only ever reached as an alias).
	'footer-banner'           => 'scs-ad',
);
$__creatives = array(
	'house-ad', 'house-ad-burgundy', 'house-ad-navy', 'house-ad-cream',
	'scs-ad', 'banner-design',
);
$__creative = isset($__house_map[$slot]) ? $__house_map[$slot] : 'house-ad';
if (!empty($slot_creative) && in_array($slot_creative, $__creatives, true)) {
	$__creative = $slot_creative;
}
// Exact-size artwork when it exists, the generic bucket otherwise.
$__default = '';
if ($__w && $__h) {
	$__exact = 'assets/images/ads/' . $__creative . '-' . $__w . 'x' . $__h . '.svg';
	if (is_file(FCPATH . $__exact)) {
		$__default = $__exact;
	}
}
if ($__default === '') {
	$__default = 'assets/images/ads/' . $__creative . '-' . $__shape . '.svg';
}

switch ($__creative) {
	case 'scs-ad':
		$__href = 'https://www.spacecentersystems.com/website-development/';
		$__alt  = 'Space Center Systems — website design, hosting and local SEO';
		break;
	case 'banner-design':
		$__href = base_url('request-quote');
		$__alt  = 'Need a banner designed? Pearland411 designs your ad artwork';
		break;
	case 'house-ad-navy':
		$__href = base_url('pricing-plan');
		$__alt  = 'Featured business placement available on Pearland411';
		break;
	// These two creatives have "See packages" drawn into the artwork, so they
	// have to land on the packages, not on the lead form below.
	case 'house-ad':
	case 'house-ad-cream':
		$__href = base_url('pricing-plan');
		$__alt  = 'Advertising packages on Pearland411';
		break;
	default:
		$__href = base_url('post-your-ads');
		$__alt  = 'Advertise your business on Pearland411';
}

// Only scs-ad leaves the site, so only it gets target/rel.
$__external = ($__creative === 'scs-ad');

$__label = isset($slot_label) ? $slot_label : 'Sponsored';

$__cls = 'ad-slot ad-slot-' . preg_replace('/[^a-z0-9\-]/i', '', $slot);
if (!empty($slot_class)) $__cls .= ' ' . $slot_class;
if (!$__ad)              $__cls .= ' ad-slot--default';

// Reserve the box so the page doesn't jump when the creative paints.
$__style = ($__w && $__h) ? 'max-width:' . $__w . 'px;aspect-ratio:' . $__w . ' / ' . $__h . ';' : '';

// This partial is included by eight different views. Rather than ask each of
// them to load a stylesheet — which would mean touching pages that are not
// part of this work — it ships its own CSS and emits it once per request.
$__need_css = !defined('PEARLAND411_AD_SLOT_CSS');
if ($__need_css) define('PEARLAND411_AD_SLOT_CSS', 1);
?>

<?php if ($__need_css): ?>
	<style>
		.ad-slot { margin: 18px 0; text-align: center; }
		.ad-slot__label {
			display: block; font-size: 10px; line-height: 1.4; text-transform: uppercase;
			letter-spacing: .14em; color: #9aa09a; margin-bottom: 5px;
		}
		.ad-slot__link {
			display: block; margin: 0 auto; max-width: 100%; border-radius: 8px;
			overflow: hidden; border: 1px solid #e4e7de; background: #fafbf7; line-height: 0;
		}
		.ad-slot__link:hover { border-color: #cfe3a8; }
		/*
		 * `contain`, not `cover`. Cover fills the box by cropping whatever
		 * does not fit, which on a banner means cutting words off the ends of
		 * a headline and slicing the call-to-action button in half. An
		 * advertiser who supplies artwork a few pixels off its zone would
		 * rather see a hairline of background than lose their phone number.
		 * The house creatives are drawn at each zone's exact size, so for
		 * them the two are identical.
		 */
		.ad-slot__link img { display: block; width: 100%; height: 100%; object-fit: contain; }

		/*
		 * Above the fold on a phone, the top leaderboard and its "Sponsored"
		 * label eat the first screen before a single business appears — and
		 * local-directory traffic is mostly phones. Only the UNSOLD one is
		 * hidden: a paid banner there is revenue, and an advertiser bought
		 * that position on every device.
		 */
		@media (max-width: 767px) {
			.ad-slot--default.ad-slot-top-banner-site-wide,
			.ad-slot--default.ad-slot-top-banner-category { display: none; }
		}
	</style>
<?php endif; ?>

<aside class="<?= e_attr($__cls) ?>" data-ad-slot="<?= e_attr($slot) ?>" aria-label="Sponsored placement">
	<span class="ad-slot__label"><?= e($__label) ?></span>

	<?php if ($__ad && !empty($__ad->ad_photo)): ?>
		<a class="ad-slot__link" href="<?= e_url($__ad->ad_link ?: '#') ?>"
		   target="_blank" rel="nofollow sponsored noopener"
		   data-ad-id="<?= (int) $__ad->id ?>"
		   style="<?= e_attr($__style) ?>">
			<img src="<?= base_url($__ad->ad_photo) ?>" alt="Advertisement"
				<?= $__w ? 'width="' . (int) $__w . '"' : '' ?>
				<?= $__h ? 'height="' . (int) $__h . '"' : '' ?>
				loading="lazy" decoding="async">
		</a>
	<?php else: ?>
		<a class="ad-slot__link" href="<?= e_url($__href) ?>"
			<?= $__external ? 'target="_blank" rel="nofollow sponsored noopener"' : '' ?>
		   style="<?= e_attr($__style) ?>">
			<img src="<?= base_url($__default) ?>" alt="<?= e_attr($__alt) ?>"
				<?= $__w ? 'width="' . (int) $__w . '"' : '' ?>
				<?= $__h ? 'height="' . (int) $__h . '"' : '' ?>
				loading="lazy" decoding="async">
		</a>
	<?php endif; ?>
</aside>

<?php
unset(
	$__ci, $__ad, $__meta, $__fallback, $__w, $__h, $__size, $__m, $__ratio,
	$__shape, $__creative, $__creatives, $__house_map, $__default, $__exact, $__external, $__href, $__alt,
	$__label, $__cls, $__style, $__need_css, $__alias_key,
	// Per-include options must not leak into the next include on the page.
	$slot_class, $slot_label, $slot_fallback, $slot_alias, $slot_creative,
	$slot_fallback_partial
);
// Don't unset $slot — the includer owns it.
?>
