<section class="service-detail-hero" style="background:#0e2a3a;color:#fff;padding:40px 0;">
	<div class="container">
		<div style="font-size:0.85rem;opacity:0.75;margin-bottom:6px;">
			<a href="<?= base_url('services') ?>" style="color:#cbd5e1;">&larr; All services</a>
		</div>
		<h1 style="margin:0;font-size:2rem;"><?= e($service->name) ?></h1>
		<?php if (!empty($service->category)): ?>
			<div style="margin-top:6px;font-size:0.85rem;opacity:0.85;"><?= e($service->category) ?></div>
		<?php endif; ?>
	</div>
</section>

<section class="service-detail-body" style="padding:40px 0;">
	<div class="container">

		<?php $ok = $this->session->flashdata('service_request_success'); if (!empty($ok)): ?>
			<div class="alert alert-success"><?= html_escape($ok) ?></div>
		<?php endif; ?>
		<?php $err = $this->session->flashdata('service_request_error'); if (!empty($err)): ?>
			<div class="alert alert-danger"><?= html_escape($err) ?></div>
		<?php endif; ?>

		<div class="row">
			<div class="col-lg-7" style="margin-bottom:32px;">
				<p style="font-size:1.1rem;color:#374151;"><?= e($service->short_description) ?></p>

				<?php if (!empty($service->body)): ?>
					<div class="service-body" style="margin-top:16px;">
						<?php
						// The service body is admin-edited HTML stored in the
						// `services` table. We allow a small set of inline tags
						// (p, br, strong, em, ul, ol, li, a, h2-h4, blockquote)
						// and strip everything else as a defense-in-depth measure.
						$allowed = '<p><br><strong><b><em><i><ul><ol><li><a><h2><h3><h4><blockquote>';
						echo strip_tags($service->body, $allowed);
						?>
					</div>
				<?php endif; ?>

				<?php if ($service->base_price !== null && $service->price_unit !== 'custom'): ?>
					<div style="margin-top:24px;padding:16px;background:#f3f4f6;border-radius:8px;">
						<strong>Starting at $<?= number_format((float) $service->base_price, 0) ?></strong>
						<small style="color:#6b7280;">
							<?= e(str_replace('_', ' ', $service->price_unit)) ?>
						</small>
						<div style="font-size:0.85rem;color:#6b7280;margin-top:4px;">
							Final pricing depends on scope. Send us your details for an exact quote.
						</div>
					</div>
				<?php else: ?>
					<div style="margin-top:24px;padding:16px;background:#f3f4f6;border-radius:8px;">
						<strong>Custom quote</strong>
						<div style="font-size:0.85rem;color:#6b7280;margin-top:4px;">
							Send us your details and we&rsquo;ll get back to you with pricing.
						</div>
					</div>
				<?php endif; ?>
			</div>

			<div class="col-lg-5">
				<div style="border:1px solid #e5e7eb;border-radius:8px;padding:24px;background:#fff;position:sticky;top:20px;">
					<h3 style="margin:0 0 12px 0;font-size:1.25rem;">Request a quote</h3>
					<p style="color:#6b7280;font-size:0.9rem;margin-bottom:16px;">
						Tell us a bit about your project and we&rsquo;ll get back to you within one business day.
					</p>

					<form method="post" action="<?= base_url('request-service/' . rawurlencode($service->slug)) ?>">
						<div class="form-group">
							<label for="rs_name">Your name *</label>
							<input id="rs_name" type="text" name="name" class="form-control"
								   value="<?= e_attr($prefill['name']) ?>" required>
						</div>
						<div class="form-group">
							<label for="rs_email">Email *</label>
							<input id="rs_email" type="email" name="email" class="form-control"
								   value="<?= e_attr($prefill['email']) ?>" required>
						</div>
						<div class="form-group">
							<label for="rs_phone">Phone</label>
							<input id="rs_phone" type="tel" name="phone" class="form-control"
								   value="<?= e_attr($prefill['phone']) ?>">
						</div>
						<div class="form-group">
							<label for="rs_company">Business / company</label>
							<input id="rs_company" type="text" name="company" class="form-control"
								   value="<?= e_attr($prefill['company']) ?>">
						</div>
						<div class="form-group">
							<label for="rs_budget">Budget range</label>
							<select id="rs_budget" name="budget_range" class="form-control">
								<option value="">— select —</option>
								<option value="under_500">Under $500</option>
								<option value="500_1500">$500 – $1,500</option>
								<option value="1500_5000">$1,500 – $5,000</option>
								<option value="5000_15000">$5,000 – $15,000</option>
								<option value="15000_plus">$15,000+</option>
								<option value="not_sure">Not sure yet</option>
							</select>
						</div>
						<div class="form-group">
							<label for="rs_message">Tell us about your project *</label>
							<textarea id="rs_message" name="message" class="form-control" rows="4" required></textarea>
						</div>
						<button type="submit" class="btn btn-primary" style="width:100%;">
							Send request
						</button>
					</form>
				</div>
			</div>
		</div>

	</div>
</section>
