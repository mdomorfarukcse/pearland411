<?php
/**
 * /coupons — every live coupon and deal from Pearland businesses.
 *
 * Rebuilt on the shared page kit (assets/css/directory.css + content.css
 * through $page_css), the same shell as /event and /news. The previous version
 * was ~50 lines of inline `style="…"` in the old teal palette (#0e2a3a header,
 * #fff7d6 code chip) with no hero, no sidebar and no empty state beyond a
 * Bootstrap alert.
 *
 * From User::coupons(): $coupons, $ad_slots. Both defaulted.
 */

$coupons  = (isset($coupons) && is_array($coupons)) ? $coupons : array();
$ad_slots = (isset($ad_slots) && is_array($ad_slots)) ? $ad_slots : array(
	'top'    => 'top-banner-site-wide',
	'inline' => 'middle-banner-category',
	'side_1' => 'right-side-banner-1',
	'side_2' => 'right-side-banner-2',
	'bottom' => 'bottom-banner-site-wide',
);

$today_ts = strtotime('today');

/** Days left on a coupon, or null when the end date is missing/unparseable. */
$cp_days_left = function ($coupon) use ($today_ts) {
	if (empty($coupon->coupon_end_date)) return null;
	$end = strtotime((string) $coupon->coupon_end_date);
	if (!$end) return null;
	return (int) floor(($end - $today_ts) / 86400);
};

// How many businesses are represented — a more honest headline number than the
// coupon count when one business posts five offers.
$businesses = array();
foreach ($coupons as $c) {
	$key = !empty($c->listing_name) ? (string) $c->listing_name : (string) ($c->owner_name ?? '');
	if ($key !== '') $businesses[$key] = true;
}

$ending_soon = 0;
foreach ($coupons as $c) {
	$d = $cp_days_left($c);
	if ($d !== null && $d >= 0 && $d <= 7) $ending_soon++;
}
?>

<section class="p411-ct p411-ct--coupons">

	<!-- ================================================================ HERO -->
	<div class="p411-hero">
		<div class="p411-hero__bg" style="background-image:url('<?= base_url('assets/images/coupon-bg.png') ?>');" aria-hidden="true"></div>
		<div class="container">
			<nav class="p411-crumb" aria-label="Breadcrumb">
				<a href="<?= base_url() ?>">Home</a>
				<span aria-hidden="true">/</span>
				<span class="is-current">Deals &amp; coupons</span>
			</nav>

			<h1>Deals &amp; coupons in Pearland</h1>
			<p class="p411-hero__lede">
				Current offers from Pearland-area businesses. Show the code in store, or use it online &mdash;
				no account needed, nothing to print unless you want to.
			</p>

			<div class="p411-hero__stats">
				<div><b><?= number_format(count($coupons)) ?></b>live offer<?= count($coupons) === 1 ? '' : 's' ?></div>
				<div><b><?= number_format(count($businesses)) ?></b>local business<?= count($businesses) === 1 ? '' : 'es' ?></div>
				<div><b><?= number_format($ending_soon) ?></b>ending this week</div>
			</div>
		</div>
	</div>

	<div class="container">
		<div class="p411-shell">

			<!-- ============================================================ MAIN -->
			<div class="p411-shell__main">

				<div class="p411-adrow">
					<?php $slot = $ad_slots['top'];
					include APPPATH . 'views/partials/ad_slot.php'; ?>
				</div>

				<?php if (!empty($coupons)): ?>
					<div class="p411-toolbar">
						<div class="p411-toolbar__count">
							Showing <b><?= number_format(count($coupons)) ?></b>
							offer<?= count($coupons) === 1 ? '' : 's' ?>
							<span class="p411-toolbar__meta">Valid today</span>
						</div>
						<div class="p411-toolbar__right">
							<a class="p411-btn p411-btn--sm p411-btn--ghost" href="<?= e_url(base_url('user/coupons')) ?>">Post a deal</a>
						</div>
					</div>

					<div class="p411-ct-grid">
						<?php foreach ($coupons as $index => $c): ?>
							<?php
							$url        = base_url('coupons/' . (int) $c->coupon_id);
							$days_left  = $cp_days_left($c);
							$ends_soon  = $days_left !== null && $days_left >= 0 && $days_left <= 7;
							$biz_name   = !empty($c->listing_name) ? (string) $c->listing_name : (string) ($c->owner_name ?? '');
							$biz_url    = !empty($c->listing_slug)
								? base_url('listing-details/' . rawurlencode((string) $c->listing_slug))
								: '';
							?>
							<article class="p411-ct-card p411-coupon">
								<div class="p411-ct-card__media">
									<a class="p411-ct-card__link" href="<?= e_url($url) ?>" tabindex="-1" aria-hidden="true">
										<?php if (!empty($c->coupon_photo)): ?>
											<?= responsive_img($c->coupon_photo, (string) $c->coupon_name) ?>
										<?php else: ?>
											<span class="p411-coupon__art" aria-hidden="true">
												<svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
													<path d="M3 9V6a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v3a3 3 0 0 0 0 6v3a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-3a3 3 0 0 0 0-6z"/>
													<path d="M15 8.5 9 15.5"/><circle cx="9.5" cy="9" r=".8"/><circle cx="14.5" cy="15" r=".8"/>
												</svg>
											</span>
										<?php endif; ?>
									</a>

									<?php if ($ends_soon): ?>
										<span class="p411-flag p411-flag--featured">
											<?= $days_left === 0 ? 'Ends today' : 'Ends in ' . (int) $days_left . ' day' . ($days_left === 1 ? '' : 's') ?>
										</span>
									<?php endif; ?>
								</div>

								<div class="p411-ct-card__body">
									<h3><a href="<?= e_url($url) ?>"><?= e($c->coupon_name) ?></a></h3>

									<?php if ($biz_name !== ''): ?>
										<ul class="p411-ct-meta">
											<li>
												<i class="material-icons" aria-hidden="true">store</i>
												<em>
													<?php if ($biz_url !== ''): ?>
														<a href="<?= e_url($biz_url) ?>"><?= e($biz_name) ?></a>
													<?php else: ?>
														<?= e($biz_name) ?>
													<?php endif; ?>
												</em>
											</li>
										</ul>
									<?php endif; ?>

									<div class="p411-code">
										<span class="p411-code__label">Coupon code</span>
										<b><?= e($c->coupon_code) ?></b>
									</div>
								</div>

								<div class="p411-ct-card__foot">
									<span class="p411-coupon__valid">
										<?php if (!empty($c->coupon_end_date)): ?>
											Valid through <?= e(date('M j, Y', strtotime((string) $c->coupon_end_date))) ?>
										<?php else: ?>
											While stocks last
										<?php endif; ?>
									</span>
									<a class="p411-btn p411-btn--sm" href="<?= e_url($url) ?>">View deal</a>
								</div>
							</article>

							<?php if ($index === 5 && count($coupons) > 6): ?>
								<div class="p411-adrow">
									<?php $slot = $ad_slots['inline'];
									include APPPATH . 'views/partials/ad_slot.php'; ?>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>

				<?php else: ?>
					<!-- ===================================================== EMPTY -->
					<div class="p411-empty">
						<div class="p411-empty__icon">
							<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<path d="M3 9V6a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v3a3 3 0 0 0 0 6v3a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-3a3 3 0 0 0 0-6z"/>
								<path d="M15 8.5 9 15.5"/>
							</svg>
						</div>
						<h3>No offers running right now</h3>
						<p>
							Nothing is live at the moment &mdash; deals come and go quickly, so it's worth checking back.
							If you run a business here, posting one is free.
						</p>
						<div class="p411-empty__actions">
							<a class="p411-btn" href="<?= e_url(base_url('user/coupons')) ?>">Post a deal</a>
							<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('all-listing')) ?>">Browse local businesses</a>
						</div>
					</div>

					<div class="p411-adrow" style="margin-top:22px;">
						<?php $slot = $ad_slots['inline'];
						include APPPATH . 'views/partials/ad_slot.php'; ?>
					</div>
				<?php endif; ?>

				<?php $promo_variant = 'banner';
				include APPPATH . 'views/partials/service_promo.php'; ?>
			</div>

			<!-- ========================================================= SIDEBAR -->
			<aside class="p411-shell__side">

				<?php $slot = $ad_slots['side_1'];
				$slot_creative = 'house-ad-burgundy';
				include APPPATH . 'views/partials/ad_slot.php'; ?>

				<div class="p411-card">
					<div class="p411-card__head">
						<h2>How the codes work</h2>
					</div>
					<ol class="p411-steps">
						<li>Open the deal and note the code.</li>
						<li>Show it on your phone in store, or type it in at checkout online.</li>
						<li>Check the dates &mdash; every offer here is valid today unless it says otherwise.</li>
					</ol>
					<p class="p411-form__hint" style="margin:12px 0 0;">
						Deals are posted by the businesses themselves. If a code is refused, tell us and we'll chase it.
					</p>
					<a class="p411-btn p411-btn--block p411-btn--ghost" style="margin-top:10px;" href="<?= e_url(base_url('contact-us')) ?>">Report a problem</a>
				</div>

				<?php $slot = $ad_slots['side_2'];
				$slot_fallback_partial = 'banner_design_promo.php';
				$slot_creative = 'banner-design';
				include APPPATH . 'views/partials/ad_slot.php'; ?>

				<div class="p411-card">
					<div class="p411-card__head">
						<h2>Running an offer?</h2>
					</div>
					<p class="p411-form__hint">
						Post it here and it shows up on this page, on your listing and on the homepage &mdash;
						free with any listing.
					</p>
					<a class="p411-btn p411-btn--block" href="<?= e_url(base_url('user/coupons')) ?>">Post a deal</a>
					<a class="p411-btn p411-btn--block p411-btn--ghost" style="margin-top:8px;" href="<?= e_url(base_url('pricing-plan')) ?>">See advertising packages</a>
				</div>

			</aside>
		</div>

		<div class="p411-adrow" style="margin-top:6px;">
			<?php $slot = $ad_slots['bottom'];
			include APPPATH . 'views/partials/ad_slot.php'; ?>
		</div>
	</div>
</section>
