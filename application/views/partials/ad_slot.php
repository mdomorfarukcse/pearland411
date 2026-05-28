<?php
/**
 * Render one active ad for a given zone, or a tasteful house-ad
 * placeholder when no live ad is targeting that zone.
 *
 *   <?php $slot = 'home-hero'; include APPPATH.'views/partials/ad_slot.php'; ?>
 *
 * Variables consumed (the includer sets them):
 *   $slot          (string, required) — slot_key (see migration 0006).
 *   $slot_class    (string, optional) — extra CSS class for the wrapper.
 *
 * Falls back gracefully when ads_prices.slot_key doesn't exist yet
 * (migration 0006 not run) — the partial just emits nothing.
 */

if (!isset($slot) || $slot === '') return;

// CI loads partials inside the same scope, so we need to grab the
// active CI instance to query the model.
$__ci      =& get_instance();
$__ad_slot = method_exists($__ci->usermodel, 'getAdForSlot')
    ? $__ci->usermodel->getAdForSlot($slot)
    : null;

$wrapper_class = 'ad-slot ad-slot-' . preg_replace('/[^a-z0-9\-]/i', '', $slot);
if (!empty($slot_class)) $wrapper_class .= ' ' . $slot_class;
?>

<aside class="<?= e_attr($wrapper_class) ?>"
       data-ad-slot="<?= e_attr($slot) ?>"
       aria-label="Sponsored placement"
       style="margin:18px 0;text-align:center;">
	<?php if ($__ad_slot && !empty($__ad_slot->ad_photo)): ?>
		<a href="<?= e_url($__ad_slot->ad_link ?: '#') ?>"
		   target="_blank" rel="nofollow sponsored noopener"
		   data-ad-id="<?= (int) $__ad_slot->id ?>"
		   style="display:inline-block;max-width:100%;">
			<small style="display:block;color:#94a3b8;font-size:11px;letter-spacing:0.05em;text-transform:uppercase;margin-bottom:4px;">Sponsored</small>
			<img src="<?= base_url($__ad_slot->ad_photo) ?>"
			     alt="Sponsored"
			     loading="lazy" decoding="async"
			     style="max-width:100%;height:auto;border:1px solid #e5e7eb;border-radius:8px;display:block;margin:0 auto;">
		</a>
	<?php else: ?>
		<a href="<?= base_url('post-your-ads') ?>"
		   style="display:block;max-width:100%;background:linear-gradient(135deg,#180f5e 0%,#2563eb 100%);color:#fff;padding:22px 16px;border-radius:8px;text-decoration:none;">
			<small style="display:block;font-size:11px;letter-spacing:0.08em;text-transform:uppercase;opacity:0.7;margin-bottom:6px;">Ad space available</small>
			<strong style="display:block;font-size:1.05rem;margin-bottom:4px;">Your ad here</strong>
			<span style="opacity:0.85;font-size:0.9rem;">Reach Pearland-area customers &mdash; from $29 / week.</span>
		</a>
	<?php endif; ?>
</aside>

<?php
unset($__ad_slot, $wrapper_class);
// Don't unset $slot — the includer owns it.
?>
