<?php
/**
 * Render one ad zone.
 *
 *   <?php $slot = 'home-hero'; include APPPATH.'views/partials/ad_slot.php'; ?>
 *
 * Three states, in order of preference:
 *   1. A live ad targeting this zone (ads.ad_status = 1, inside its date window).
 *   2. A built-in default creative — "advertise here", the Space Center Systems
 *      web-development ad, or the ad/banner-design ad. Which one is fixed per
 *      zone (see $__house_map below), so a slot always shows the same thing.
 *   3. Nothing at all, when the includer sets $slot_fallback = false.
 *
 * Variables consumed (the includer sets them; only $slot is required):
 *   $slot          (string) — slot_key. See migrations 0006 + 0017 + 0019.
 *   $slot_class    (string) — extra CSS class on the wrapper.
 *   $slot_label    (string) — override the "Sponsored" microlabel.
 *   $slot_fallback (bool)   — false = render nothing when no ad is sold.
 *   $slot_alias    (string|array) — older slot_key(s) this position replaced.
 *                  Only consulted when nothing is sold on $slot, so inventory
 *                  booked against a retired zone keeps running.
 *   $slot_creative (string) — force which house creative fills an unsold zone
 *                  ('house-ad' | 'scs-ad' | 'banner-design'), overriding the
 *                  per-slot map below. Used where the page already carries a
 *                  permanent web-services block and would otherwise repeat it.
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
	unset($slot_class, $slot_label, $slot_fallback, $slot_alias, $slot_creative);
	return;
}

// CI loads partials in the caller's scope, so reach the model via the instance.
// No `&` — get_instance() returns an object, and assigning a function's return
// value by reference raises "Only variables should be assigned by reference".
$__ci = get_instance();

$__ad   = method_exists($__ci->usermodel, 'getAdForSlot')  ? $__ci->usermodel->getAdForSlot($slot)  : null;
$__meta = method_exists($__ci->usermodel, 'getAdSlotMeta') ? $__ci->usermodel->getAdSlotMeta($slot) : null;

// Nothing sold on this zone? Fall through to any zone it superseded, so an
// ad an operator booked against a retired slot_key still gets its impressions
// instead of silently disappearing when a page is re-cut.
if (!$__ad && !empty($slot_alias) && method_exists($__ci->usermodel, 'getAdForSlot')) {
	foreach ((array) $slot_alias as $__alias_key) {
		if (empty($__alias_key)) continue;
		$__ad = $__ci->usermodel->getAdForSlot($__alias_key);
		if ($__ad) break;
	}
}

$__fallback = isset($slot_fallback) ? (bool) $slot_fallback : true;
if (!$__ad && !$__fallback) {
	unset($slot_class, $slot_label, $slot_fallback, $slot_alias, $slot_creative);
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

// Which house creative fills an unsold zone. Explicit per slot rather than
// hashed: the sidebar deliberately carries one web-development ad and one
// ad-design ad, and every other zone sells the space itself.
$__house_map = array(
	'right-side-banner-1' => 'scs-ad',        // website development
	'right-side-banner-2' => 'banner-design', // ad / banner artwork
	'listing-sidebar'     => 'scs-ad',
	'news-sidebar'        => 'banner-design',
);
$__creative = isset($__house_map[$slot]) ? $__house_map[$slot] : 'house-ad';
if (!empty($slot_creative) && in_array($slot_creative, array('house-ad', 'scs-ad', 'banner-design'), true)) {
	$__creative = $slot_creative;
}
$__default  = 'assets/images/ads/' . $__creative . '-' . $__shape . '.svg';

switch ($__creative) {
	case 'scs-ad':
		$__href = 'https://www.spacecentersystems.com/website-development/';
		$__alt  = 'Space Center Systems — website design, hosting and local SEO';
		break;
	case 'banner-design':
		$__href = base_url('request-service');
		$__alt  = 'Need a banner designed? Pearland411 designs your ad artwork';
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
		.ad-slot__link img { display: block; width: 100%; height: 100%; object-fit: cover; }
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
	$__shape, $__creative, $__house_map, $__default, $__external, $__href, $__alt,
	$__label, $__cls, $__style, $__need_css, $__alias_key,
	// Per-include options must not leak into the next include on the page.
	$slot_class, $slot_label, $slot_fallback, $slot_alias, $slot_creative
);
// Don't unset $slot — the includer owns it.
?>
