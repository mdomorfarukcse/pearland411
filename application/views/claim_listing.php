<section style="background:#0e2a3a;color:#fff;padding:40px 0;">
	<div class="container">
		<div style="font-size:0.85rem;opacity:0.75;margin-bottom:6px;">
			<a href="<?= base_url('listing-details/' . rawurlencode($listing->listing_slug)) ?>"
			   style="color:#cbd5e1;">&larr; Back to listing</a>
		</div>
		<h1 style="margin:0;font-size:1.8rem;">Claim <?= e($listing->listing_name) ?></h1>
		<p style="margin:6px 0 0 0;opacity:0.85;">
			Are you the owner? Submit a claim and an administrator will verify and
			transfer the listing to your account.
		</p>
	</div>
</section>

<section style="padding:40px 0;">

		<?php if (!empty($error)): ?>
			<div class="alert alert-danger"><?= e($error) ?></div>
		<?php endif; ?>

		<div class="row">
			<div class="col-md-7">
				<form method="post" action="<?= base_url('claim-listing/' . rawurlencode($listing->listing_slug)) ?>">
					<div class="form-group">
						<label>Your name *</label>
						<input type="text" name="enquiry_name" class="form-control"
							   value="<?= e_attr($prefill['enquiry_name'] ?? '') ?>" required>
					</div>
					<div class="form-group">
						<label>Email *</label>
						<input type="email" name="enquiry_email" class="form-control"
							   value="<?= e_attr($prefill['enquiry_email'] ?? '') ?>" required>
					</div>
					<div class="form-group">
						<label>Phone</label>
						<input type="tel" name="enquiry_mobile" class="form-control">
					</div>
					<div class="form-group">
						<label>Tell us why this is your business *</label>
						<textarea name="enquiry_message" class="form-control" rows="5"
								  minlength="20" required
								  placeholder="e.g. I am the owner / I run this location. Mention how we can verify (current website, business license, etc.)"></textarea>
						<small style="color:#6b7280;">
							At least 20 characters. The more verifiable detail you
							give, the faster the approval.
						</small>
					</div>
					<button type="submit" class="btn btn-primary">Submit claim</button>
					<a href="<?= base_url('listing-details/' . rawurlencode($listing->listing_slug)) ?>"
					   class="btn btn-secondary">Cancel</a>
				</form>
			</div>
			<div class="col-md-5">
				<div style="border:1px solid #e5e7eb;border-radius:8px;padding:20px;background:#f9fafb;">
					<h4 style="margin:0 0 10px 0;font-size:1.1rem;">Listing on file</h4>
					<p style="margin:0;"><strong><?= e($listing->listing_name) ?></strong></p>
					<?php if (!empty($listing->listing_address)): ?>
						<p style="margin:6px 0 0 0;color:#374151;"><?= e($listing->listing_address) ?></p>
					<?php endif; ?>
					<?php if (!empty($listing->listing_email)): ?>
						<p style="margin:6px 0 0 0;color:#374151;font-size:0.9rem;"><?= e($listing->listing_email) ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>

</section>
