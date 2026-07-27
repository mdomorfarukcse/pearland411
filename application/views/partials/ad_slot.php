<?php
/**
 * Render one ad zone.
 *
 *   <?php $slot = 'home-hero'; include APPPATH.'views/partials/ad_slot.php'; ?>
 *
 * Three states, in order of preference:
 *   1. A live ad targeting this zone (ads.ad_status = 1, inside its date window).
 *   2. A built-in default creative — either the "advertise here" house ad or the
 *      Space Center Systems services ad, picked deterministically per zone so a
 *      given slot always shows the same one.
 *   3. Nothing at all, when the includer sets $slot_fallback = false.
 *
 * Variables consumed (the includer sets them; only $slot is required):
 *   $slot          (string) — slot_key. See migrations 0006 + 0015.
 *   $slot_class    (string) — extra CSS class on the wrapper.
 *   $slot_label    (string) — override the "Sponsored" microlabel.
 *   $slot_fallback (bool)   — false = render nothing when no ad is sold.
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
	unset($slot_class, $slot_label, $slot_fallback);
	return;
}

// CI loads partials in the caller's scope, so reach the model via the instance.
// No `&` — get_instance() returns an object, and assigning a function's return
// value by reference raises "Only variables should be assigned by reference".
$__ci = get_instance();

$__ad   = method_exists($__ci->usermodel, 'getAdForSlot')  ? $__ci->usermodel->getAdForSlot($slot)  : null;
$__meta = method_exists($__ci->usermodel, 'getAdSlotMeta') ? $__ci->usermodel->getAdSlotMeta($slot) : null;

$__fallback = isset($slot_fallback) ? (bool) $slot_fallback : true;
if (!$__ad && !$__fallback) {
	unset($slot_class, $slot_label, $slot_fallback);
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
// Four buckets, because the zones in use span 970x90 (10.8:1) to 300x600
// (0.5:1) and a single "wide" artwork cropped unreadably at the extremes.
//   strip  970x90   ratio >= 6      — leaderboards
//   wide   900x300  2.0 <= r < 6    — 728x200, 728x250, 970x250
//   box    600x500  0.9 < r < 2.0   — 300x250
//   tall   300x600  ratio <= 0.9    — 300x600
$__ratio = ($__w && $__h) ? ($__w / $__h) : 3.0;
if     ($__ratio >= 6.0) $__shape = 'strip';
elseif ($__ratio >= 2.0) $__shape = 'wide';
elseif ($__ratio <= 0.9) $__shape = 'tall';
else                     $__shape = 'box';

// Alternate the two default creatives down the page. An explicit map beats
// hashing the key: crc32 happened to give 5 house ads to 2 Space Center ones
// on the homepage set. Anything not listed falls back to the house ad.
$__scs_slots = array('home-hero', 'home-sidebar-top', 'home-sidebar-skyscraper', 'listing-sidebar', 'news-sidebar');
$__creative  = in_array($slot, $__scs_slots, true) ? 'scs-ad' : 'house-ad';
$__default   = 'assets/images/ads/' . $__creative . '-' . $__shape . '.svg';

$__is_house  = ($__creative === 'house-ad');
$__href      = $__is_house ? base_url('post-your-ads') : 'https://www.spacecentersystems.com/website-development/';
$__alt       = $__is_house
	? 'Advertise your business on Pearland411'
	: 'Space Center Systems — website design, hosting and local SEO';

$__label = isset($slot_label) ? $slot_label : 'Sponsored';

$__cls = 'ad-slot ad-slot-' . preg_replace('/[^a-z0-9\-]/i', '', $slot);
if (!empty($slot_class)) $__cls .= ' ' . $slot_class;
if (!$__ad)              $__cls .= ' ad-slot--default';

// Reserve the box so the page doesn't jump when the creative paints.
$__style = ($__w && $__h) ? 'max-width:' . $__w . 'px;aspect-ratio:' . $__w . ' / ' . $__h . ';' : '';
?>

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
			<?= $__is_house ? '' : 'target="_blank" rel="nofollow sponsored noopener"' ?>
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
	$__shape, $__creative, $__default, $__is_house, $__href, $__alt,
	$__label, $__cls, $__style,
	// Per-include options must not leak into the next include on the page.
	$slot_class, $slot_label, $slot_fallback
);
// Don't unset $slot — the includer owns it.
?>
