<section style="background:#0e2a3a;color:#fff;padding:40px 0;text-align:center;">
	<div class="container">
		<h1 style="margin:0 0 6px 0;font-size:2rem;">Local Coupons &amp; Deals</h1>
		<p style="margin:0;opacity:0.85;">Current offers from Pearland-area businesses.</p>
	</div>
</section>

<section style="padding:40px 0;">
	<div class="container">
		<?php if (empty($coupons)): ?>
			<div class="alert alert-info">
				No active coupons right now. Check back soon &mdash; or
				<a href="<?= base_url('all_listing') ?>">browse local businesses</a>.
			</div>
		<?php else: ?>
			<div class="row">
				<?php foreach ($coupons as $c): ?>
					<div class="col-md-6 col-lg-4" style="margin-bottom:24px;">
						<div style="border:1px solid #e5e7eb;border-radius:8px;padding:0;background:#fff;overflow:hidden;height:100%;display:flex;flex-direction:column;">
							<?php if (!empty($c->coupon_photo)): ?>
								<a href="<?= base_url('coupons/' . (int) $c->coupon_id) ?>"
								   style="display:block;width:100%;height:160px;overflow:hidden;">
									<?= responsive_img($c->coupon_photo, $c->coupon_name, array(
										'style' => 'width:100%;height:160px;object-fit:cover;display:block;',
									)) ?>
								</a>
							<?php endif; ?>
							<div style="padding:16px;flex:1;display:flex;flex-direction:column;">
								<h3 style="font-size:1.15rem;margin:0 0 6px 0;">
									<a href="<?= base_url('coupons/' . (int) $c->coupon_id) ?>" style="color:#0e2a3a;">
										<?= e($c->coupon_name) ?>
									</a>
								</h3>
								<?php if (!empty($c->listing_name)): ?>
									<div style="color:#6b7280;font-size:0.9rem;margin-bottom:8px;">
										<a href="<?= base_url('listing-details/' . rawurlencode((string) $c->listing_slug)) ?>" style="color:#6b7280;">
											<?= e($c->listing_name) ?>
										</a>
									</div>
								<?php endif; ?>
								<div style="background:#fff7d6;border:1px dashed #d4a017;color:#92400e;padding:8px;border-radius:6px;text-align:center;font-family:monospace;font-weight:bold;margin-bottom:8px;letter-spacing:1px;">
									<?= e($c->coupon_code) ?>
								</div>
								<div style="color:#6b7280;font-size:0.85rem;margin-top:auto;">
									Valid through <?= e(date('M j, Y', strtotime($c->coupon_end_date))) ?>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php $cta_variant = "banner"; include APPPATH . "views/partials/web_dev_cta.php"; ?>
