<section class="services-hero" style="background:#0e2a3a;color:#fff;padding:48px 0;text-align:center;">
	<div class="container">
		<h1 style="margin:0 0 8px 0;font-size:2.25rem;">IT &amp; Web Services for Local Businesses</h1>
		<p style="margin:0;font-size:1.05rem;opacity:0.85;">
			Websites, hosting, SEO and IT support — built for Pearland-area businesses.
		</p>
	</div>
</section>

<section class="services-list" style="padding:40px 0;">
	<div class="container">

		<?php $ok = $this->session->flashdata('service_request_success'); if (!empty($ok)): ?>
			<div class="alert alert-success"><?= html_escape($ok) ?></div>
		<?php endif; ?>
		<?php $err = $this->session->flashdata('service_request_error'); if (!empty($err)): ?>
			<div class="alert alert-danger"><?= html_escape($err) ?></div>
		<?php endif; ?>

		<?php if (!empty($categories)): ?>
			<div class="services-cats" style="margin-bottom:24px;">
				<a href="<?= base_url('services') ?>"
				   class="btn btn-sm <?= empty($selected_category) ? 'btn-primary' : 'btn-outline-primary' ?>"
				   style="margin:4px;">All</a>
				<?php foreach ($categories as $c): if (empty($c->category)) continue; ?>
					<a href="<?= base_url('services?category=' . rawurlencode($c->category)) ?>"
					   class="btn btn-sm <?= ($selected_category === $c->category) ? 'btn-primary' : 'btn-outline-primary' ?>"
					   style="margin:4px;">
						<?= e($c->category) ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if (empty($services)): ?>
			<div class="alert alert-info">No services available right now. Please check back soon.</div>
		<?php else: ?>
			<div class="row">
				<?php foreach ($services as $svc): ?>
					<div class="col-md-6 col-lg-4" style="margin-bottom:24px;">
						<div class="service-card" style="height:100%;border:1px solid #e5e7eb;border-radius:8px;padding:20px;display:flex;flex-direction:column;background:#fff;">
							<?php if (!empty($svc->category)): ?>
								<div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:#6b7280;margin-bottom:8px;">
									<?= e($svc->category) ?>
								</div>
							<?php endif; ?>

							<h3 style="font-size:1.25rem;margin:0 0 12px 0;">
								<a href="<?= base_url('services/' . rawurlencode($svc->slug)) ?>" style="color:#0e2a3a;">
									<?= e($svc->name) ?>
								</a>
							</h3>

							<p style="color:#374151;flex:1;"><?= e($svc->short_description) ?></p>

							<div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px;">
								<?php if ($svc->base_price !== null): ?>
									<div>
										<?php if ($svc->price_unit === 'custom'): ?>
											<strong style="color:#0e2a3a;">Custom quote</strong>
										<?php else: ?>
											<strong style="color:#0e2a3a;">$<?= number_format((float) $svc->base_price, 0) ?></strong>
											<small style="color:#6b7280;">
												<?= e(str_replace('_', ' ', $svc->price_unit)) ?>
											</small>
										<?php endif; ?>
									</div>
								<?php else: ?>
									<div><strong style="color:#0e2a3a;">Custom quote</strong></div>
								<?php endif; ?>

								<a href="<?= base_url('services/' . rawurlencode($svc->slug)) ?>" class="btn btn-sm btn-primary">
									Learn more
								</a>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div style="margin-top:32px;text-align:center;">
			<p style="margin-bottom:8px;color:#374151;">Don&rsquo;t see what you need? We do custom work too.</p>
			<a href="<?= base_url('request-service') ?>" class="btn btn-primary">Request a custom quote</a>
		</div>
	</div>
</section>
