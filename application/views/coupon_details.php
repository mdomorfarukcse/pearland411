<?php
/**
 * /coupons/{id} — one deal.
 *
 * Shares the listing-details shell (`.p411-ld`, assets/css/directory.css +
 * content.css through $page_css) so a coupon page and a business page look
 * like the same site. Was ~60 lines of inline `style="…"` in the old teal
 * palette with a monospace code box.
 *
 * From User::coupon_details(): $coupon (required — the controller 404s
 * without it).
 */

$c_start = !empty($coupon->coupon_start_date) ? strtotime((string) $coupon->coupon_start_date) : null;
$c_end   = !empty($coupon->coupon_end_date)   ? strtotime((string) $coupon->coupon_end_date)   : null;
$c_days  = $c_end ? (int) floor(($c_end - strtotime('today')) / 86400) : null;

/*
 * `owner_name` is the ACCOUNT that typed the coupon in. For everything entered
 * by the team that is a staff or agency login, so falling back to it printed
 * "Posted by Mydynamic VA" on a customer-facing page, and had the offer read
 * as "Mydynamic VA is offering this deal to Pearland411 readers". It is the
 * data-entry user, not the merchant, and it must never be presented as one.
 *
 * A coupon is attributed to the BUSINESS it is linked to. With no linked
 * listing there is no business to name and the deal is simply Pearland411's to
 * present.
 */
$biz_name = !empty($coupon->listing_name) ? (string) $coupon->listing_name : '';
$biz_url  = ($biz_name !== '' && !empty($coupon->listing_slug))
	? base_url('listing-details/' . rawurlencode((string) $coupon->listing_slug))
	: '';
$posted_by = $biz_name !== '' ? $biz_name : 'Pearland411';

// What a reader can actually DO with this. The page used to print a code and,
// unless a redemption link happened to be set, offer nothing at all — which is
// the reported "I don't see any use for this, how do users claim a coupon?".
$has_link = !empty($coupon->coupon_link);
$has_code = trim((string) $coupon->coupon_code) !== '';
?>

<section class="p411-ld p411-ld--coupon" data-track-coupon="<?= (int) $coupon->coupon_id ?>">

	<!-- ================================================================ HERO -->
	<div class="p411-ld__hero">
		<div class="container">
			<nav class="p411-crumb" aria-label="Breadcrumb">
				<a href="<?= base_url() ?>">Home</a>
				<span aria-hidden="true">/</span>
				<a href="<?= base_url('coupons') ?>">Deals &amp; coupons</a>
				<span aria-hidden="true">/</span>
				<span class="is-current"><?= e($coupon->coupon_name) ?></span>
			</nav>

			<div class="p411-ld__ident">
				<div class="p411-ld__head">
					<h1><?= e($coupon->coupon_name) ?></h1>

					<div class="p411-ld__badges">
						<?php if ($c_days !== null && $c_days >= 0 && $c_days <= 7): ?>
							<span class="p411-flag p411-flag--featured">
								<?= $c_days === 0 ? 'Ends today' : 'Ends in ' . $c_days . ' day' . ($c_days === 1 ? '' : 's') ?>
							</span>
						<?php elseif ($c_end): ?>
							<span class="p411-tag p411-tag--green">Valid today</span>
						<?php endif; ?>
						<?php if ($biz_name !== ''): ?>
							<span class="p411-tag p411-tag--navy">
								<?php if ($biz_url !== ''): ?>
									<a href="<?= e_url($biz_url) ?>"><?= e($biz_name) ?></a>
								<?php else: ?>
									<?= e($biz_name) ?>
								<?php endif; ?>
							</span>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="container">
		<div class="p411-shell">

			<!-- ============================================================ MAIN -->
			<div class="p411-shell__main">

				<div class="p411-adrow">
					<?php $slot = $ad_slots['top'] ?? '';
					include APPPATH . 'views/partials/ad_slot.php'; ?>
				</div>

				<?php if (!empty($coupon->coupon_photo)): ?>
					<div class="p411-card p411-card--flush">
						<figure class="p411-ct-figure">
							<?= responsive_img($coupon->coupon_photo, (string) $coupon->coupon_name) ?>
						</figure>
					</div>
				<?php endif; ?>

				<div class="p411-card">
					<div class="p411-card__head">
						<h2>About this offer</h2>
					</div>

					<div class="p411-prose">
						<p>
							<?php if ($biz_name !== ''): ?>
								<b><?= e($biz_name) ?></b> is offering this deal to Pearland411 readers.
							<?php else: ?>
								This deal is open to Pearland411 readers.
							<?php endif; ?>
							<?php /* Say what is actually possible. The old copy promised an
							        online checkout on every coupon, including the ones with
							        no link to check out at. */ ?>
							<?php if ($has_code && $has_link): ?>
								Copy the code, then use it at checkout &mdash; or quote it in store.
							<?php elseif ($has_code): ?>
								There is nothing to sign up for: copy the code and quote it
								when you order or pay.
							<?php else: ?>
								No code needed &mdash; just mention Pearland411 when you order.
							<?php endif; ?>
						</p>
					</div>

					<?php if ($has_code): ?>
						<?php /*
						 * The code was previously printed and nothing more, so "claiming"
						 * meant selecting the text by hand. The button copies it; without
						 * JavaScript the code is still right there to read, which is why
						 * the button is added by script rather than shipped disabled.
						 */ ?>
						<div class="p411-code p411-code--lg" data-coupon-code="<?= e_attr($coupon->coupon_code) ?>">
							<span class="p411-code__label">Coupon code</span>
							<b><?= e($coupon->coupon_code) ?></b>
							<button type="button" class="p411-code__copy" data-track="click" hidden>Copy code</button>
						</div>
					<?php endif; ?>

					<div class="p411-coupon-actions">
						<?php if ($has_link): ?>
							<a class="p411-btn p411-btn--lg" data-track="click" href="<?= e_url($coupon->coupon_link) ?>"
							   target="_blank" rel="noopener noreferrer">Redeem this offer &rarr;</a>
						<?php endif; ?>
						<?php if ($biz_url !== ''): ?>
							<?php /* With no redemption link this is the only way to act on the
							        deal: the business page carries the address, phone and hours. */ ?>
							<a class="p411-btn <?= $has_link ? 'p411-btn--ghost' : 'p411-btn--lg' ?>"
							   href="<?= e_url($biz_url) ?>">
								<?= $has_link ? 'About ' . e($biz_name) : 'Where to use it &rarr;' ?>
							</a>
						<?php endif; ?>
					</div>

					<?php
					/*
					 * The terms the business typed. Nothing rendered this column before,
					 * which was fine while no screen could write one — /user/add-coupon
					 * asks for it now, and a term nobody can read is a term the reader
					 * finds out about at the till.
					 */
					$c_terms = isset($coupon->coupon_details) ? content_text($coupon->coupon_details) : '';
					?>
					<?php if ($c_terms !== ''): ?>
						<div class="p411-prose" style="margin-top:18px;padding-top:16px;border-top:1px solid var(--p411-line);">
							<h3 style="font-size:15px;font-weight:700;color:var(--p411-navy);margin:0 0 8px;">The small print</h3>
							<?= nl2br(e($c_terms)) ?>
						</div>
					<?php endif; ?>

					<?php if (!$has_link && $biz_url === ''): ?>
						<p class="p411-form__hint" style="margin:14px 0 0;">
							This deal is redeemed with the business directly. If you are not sure
							where, <a href="<?= e_url(base_url('contact-us')) ?>">ask us</a> and
							we will point you at them.
						</p>
					<?php endif; ?>
				</div>

				<div class="p411-card">
					<div class="p411-card__head">
						<h2>The small print</h2>
					</div>
					<ul class="p411-facts">
						<?php if ($c_start): ?>
							<li><span>Valid from</span><b><?= e(date('M j, Y', $c_start)) ?></b></li>
						<?php endif; ?>
						<?php if ($c_end): ?>
							<li><span>Valid through</span><b><?= e(date('M j, Y', $c_end)) ?></b></li>
						<?php endif; ?>
						<?php if (!empty($coupon->coupon_use_members)): ?>
							<li><span>Who can use it</span><b><?= e($coupon->coupon_use_members) ?></b></li>
						<?php endif; ?>
						<?php /* The business, or Pearland411 — never the staff account that
						        keyed the coupon in. See the note at the top of this file. */ ?>
						<li><span>Posted by</span><b><?= e($posted_by) ?></b></li>
					</ul>
					<p class="p411-form__hint" style="margin:12px 0 0;">
						Terms are set by the business, not by Pearland411. If the code is refused,
						<a href="<?= e_url(base_url('contact-us')) ?>">let us know</a>.
					</p>
				</div>

				<div class="p411-adrow" style="margin-top:22px;">
					<?php $slot = $ad_slots['inline'] ?? '';
					$slot_creative = 'house-ad-cream';
					include APPPATH . 'views/partials/ad_slot.php'; ?>
				</div>

			</div>

			<!-- ========================================================= SIDEBAR -->
			<aside class="p411-shell__side">

				<?php if ($biz_url !== ''): ?>
					<div class="p411-card">
						<div class="p411-card__head">
							<h2>The business</h2>
						</div>
						<p class="p411-form__hint">
							Opening hours, phone number, photographs and reviews for <b><?= e($biz_name) ?></b>.
						</p>
						<a class="p411-btn p411-btn--block" href="<?= e_url($biz_url) ?>">View the listing</a>
					</div>
				<?php endif; ?>

				<?php $slot = $ad_slots['side_1'] ?? '';
				$slot_creative = 'house-ad-burgundy';
				include APPPATH . 'views/partials/ad_slot.php'; ?>

				<div class="p411-card">
					<div class="p411-card__head">
						<h2>More local deals</h2>
					</div>
					<p class="p411-form__hint">
						Every live coupon from Pearland businesses, in one place.
					</p>
					<a class="p411-btn p411-btn--block p411-btn--ghost" href="<?= e_url(base_url('coupons')) ?>">Browse all deals</a>
				</div>

				<?php include APPPATH . 'views/partials/service_promo.php'; ?>

				<?php $slot = $ad_slots['side_2'] ?? '';
				$slot_fallback_partial = 'banner_design_promo.php';
				$slot_creative = 'banner-design';
				include APPPATH . 'views/partials/ad_slot.php'; ?>

				<?php // 295x600. Held back until sold — an unsold half-page house
				      // ad does more harm than the zone is worth. ?>
				<?php $slot = $ad_slots['side_sky'] ?? '';
				$slot_fallback = false;
				include APPPATH . 'views/partials/ad_slot.php'; ?>

			</aside>
		</div>

		<div class="p411-adrow" style="margin-top:6px;">
			<?php $slot = $ad_slots['bottom'] ?? '';
			include APPPATH . 'views/partials/ad_slot.php'; ?>
		</div>
	</div>
</section>

<script>
/*
 * "How do users claim a coupon?" — by taking the code with them. The page used
 * to print it and stop there, leaving the reader to select the text by hand.
 *
 * The button is added by script rather than shipped in the markup, so a
 * browser that cannot copy never shows a control that would do nothing: the
 * code itself is on the page either way.
 */
(function () {
	'use strict';

	var box = document.querySelector('[data-coupon-code]');
	if (!box) { return; }

	var btn  = box.querySelector('.p411-code__copy');
	var code = box.getAttribute('data-coupon-code') || '';
	if (!btn || code === '') { return; }

	function copy(text) {
		if (navigator.clipboard && navigator.clipboard.writeText) {
			return navigator.clipboard.writeText(text);
		}
		// Older browsers, and any page not served over HTTPS, where the
		// async Clipboard API is unavailable.
		return new Promise(function (resolve, reject) {
			var ta = document.createElement('textarea');
			ta.value = text;
			ta.setAttribute('readonly', '');
			ta.style.position = 'fixed';
			ta.style.opacity = '0';
			document.body.appendChild(ta);
			ta.select();
			var ok = false;
			try { ok = document.execCommand('copy'); } catch (e) { ok = false; }
			document.body.removeChild(ta);
			ok ? resolve() : reject();
		});
	}

	btn.hidden = false;
	btn.addEventListener('click', function () {
		copy(code).then(function () {
			btn.textContent = 'Copied';
			btn.classList.add('is-copied');
		}, function () {
			// Never claim it worked when it did not — tell them what to do.
			btn.textContent = 'Press Ctrl+C';
		});
		window.setTimeout(function () {
			btn.textContent = 'Copy code';
			btn.classList.remove('is-copied');
		}, 2500);
	});
})();
</script>
