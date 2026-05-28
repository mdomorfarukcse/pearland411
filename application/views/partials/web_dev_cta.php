<?php
/**
 * Reusable "We build websites for businesses like yours" CTA card.
 *
 *   <?php include APPPATH.'views/partials/web_dev_cta.php'; ?>
 *
 * Optional variables (the includer can set them before include):
 *   $cta_variant   'sidebar' (default — compact, sticky-friendly)
 *                  'banner'  (wide horizontal strip)
 *   $cta_headline  Override the default headline.
 *   $cta_subline   Override the default body copy.
 */
$cta_variant  = isset($cta_variant)  ? $cta_variant  : 'sidebar';
$cta_headline = isset($cta_headline) ? $cta_headline : 'Need a website that actually brings customers?';
$cta_subline  = isset($cta_subline)  ? $cta_subline  : 'We build fast, mobile-first sites for local Pearland businesses — design, hosting, SEO, the lot.';
?>

<?php if ($cta_variant === 'banner'): ?>
	<section class="web-dev-cta web-dev-cta-banner"
	         style="margin:32px 0;background:linear-gradient(135deg,#180f5e 0%,#2563eb 60%,#7bb73f 100%);color:#fff;border-radius:14px;padding:36px 28px;position:relative;overflow:hidden;">
		<div style="display:flex;align-items:center;gap:24px;flex-wrap:wrap;position:relative;z-index:2;">
			<div style="flex:1;min-width:260px;">
				<div style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.1em;opacity:0.8;margin-bottom:6px;">
					Pearland411 Services
				</div>
				<h3 style="margin:0 0 6px 0;font-size:1.5rem;line-height:1.25;color:#fff;">
					<?= e($cta_headline) ?>
				</h3>
				<p style="margin:0;opacity:0.92;font-size:1rem;max-width:640px;">
					<?= e($cta_subline) ?>
				</p>
			</div>
			<div style="display:flex;gap:10px;flex-wrap:wrap;">
				<a href="<?= base_url('services') ?>"
				   style="background:#fff;color:#180f5e;padding:12px 22px;border-radius:99px;font-weight:600;text-decoration:none;white-space:nowrap;">
					See our services
				</a>
				<a href="<?= base_url('request-service') ?>"
				   style="background:rgba(255,255,255,0.14);color:#fff;border:1px solid rgba(255,255,255,0.4);padding:12px 22px;border-radius:99px;font-weight:600;text-decoration:none;white-space:nowrap;">
					Get a quote
				</a>
			</div>
		</div>
	</section>

<?php else: /* sidebar variant */ ?>
	<aside class="web-dev-cta web-dev-cta-sidebar"
	       style="margin:18px 0;background:linear-gradient(145deg,#2563eb 0%,#1e40af 100%);color:#fff;border-radius:14px;padding:24px 20px;position:relative;overflow:hidden;">
		<div style="width:44px;height:44px;background:rgba(255,255,255,0.16);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
			<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
				<line x1="8" y1="21" x2="16" y2="21"></line>
				<line x1="12" y1="17" x2="12" y2="21"></line>
			</svg>
		</div>
		<h4 style="margin:0 0 8px 0;font-size:1.1rem;color:#fff;line-height:1.3;">
			<?= e($cta_headline) ?>
		</h4>
		<p style="margin:0 0 16px 0;font-size:0.92rem;opacity:0.9;">
			<?= e($cta_subline) ?>
		</p>
		<ul style="list-style:none;padding:0;margin:0 0 18px 0;font-size:0.88rem;opacity:0.95;">
			<li style="padding:3px 0;">&#10003; Website design &amp; build</li>
			<li style="padding:3px 0;">&#10003; Local SEO that ranks</li>
			<li style="padding:3px 0;">&#10003; Hosting &amp; maintenance</li>
		</ul>
		<a href="<?= base_url('services') ?>"
		   style="display:block;background:#fff;color:#2563eb;padding:12px 18px;border-radius:99px;text-align:center;font-weight:600;text-decoration:none;">
			Explore our services
		</a>
		<a href="<?= base_url('request-service') ?>"
		   style="display:block;margin-top:8px;color:#fff;text-align:center;font-size:0.85rem;opacity:0.85;text-decoration:underline;">
			Or request a custom quote
		</a>
	</aside>
<?php endif; ?>

<?php unset($cta_variant, $cta_headline, $cta_subline); ?>
