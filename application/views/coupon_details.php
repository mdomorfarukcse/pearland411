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

$biz_name = !empty($coupon->listing_name) ? (string) $coupon->listing_name : (string) ($coupon->owner_name ?? '');
$biz_url  = !empty($coupon->listing_slug)
	? base_url('listing-details/' . rawurlencode((string) $coupon->listing_slug))
	: '';
?>

<section class="p411-ld p411-ld--coupon">

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
							Quote the code below in store, or enter it at checkout online.
						</p>
					</div>

					<div class="p411-code p411-code--lg">
						<span class="p411-code__label">Coupon code</span>
						<b><?= e($coupon->coupon_code) ?></b>
					</div>

					<?php if (!empty($coupon->coupon_link)): ?>
						<a class="p411-btn p411-btn--lg" href="<?= e_url($coupon->coupon_link) ?>"
						   target="_blank" rel="noopener noreferrer">Redeem this offer &rarr;</a>
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
						<li><span>Posted by</span><b><?= $biz_name !== '' ? e($biz_name) : 'A Pearland411 member' ?></b></li>
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
