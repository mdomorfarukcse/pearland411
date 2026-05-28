<?php
/**
 * /post-your-ads (Post your ads) — public page.
 *
 * The previous form posted to /post-ads which had no controller
 * method behind it (every submission 404'd). The full
 * self-serve ad-purchase flow is blocked on the Authorize.Net
 * integration; until that lands we collect interest via the same
 * Contact-Us pipeline so requests aren't lost.
 */
?>

<section class="login-reg">
	<div class="container">
		<div class="row">
			<div class="login-main add-list">
				<div class="log-bor">&nbsp;</div>
				<span class="steps">Advertise on Pearland411</span>

				<?php $__ok = $this->session->flashdata('contact_success'); if (!empty($__ok)): ?>
					<div class="alert alert-success"><?= e($__ok) ?></div>
				<?php endif; ?>
				<?php $__er = $this->session->flashdata('contact_error'); if (!empty($__er)): ?>
					<div class="alert alert-danger"><?= e($__er) ?></div>
				<?php endif; ?>

				<div class="log">
					<div class="login">
						<h4>Reach Pearland-area customers</h4>
						<p>
							Display ads on the homepage, listing pages and category pages.
							Inventory is sold by week or month. Tell us a bit about your
							business and we&rsquo;ll send you placement options + pricing.
						</p>

						<form method="post" action="<?= base_url('contact-submit') ?>">
							<input type="hidden" name="subject" value="Advertising enquiry">

							<div class="form-group">
								<label for="ads_fullname">Your name *</label>
								<input id="ads_fullname" type="text" name="fullName"
									   class="form-control" required>
							</div>
							<div class="form-group">
								<label for="ads_email">Email *</label>
								<input id="ads_email" type="email" name="email"
									   class="form-control" required>
							</div>
							<div class="form-group">
								<label for="ads_phone">Phone</label>
								<input id="ads_phone" type="tel" name="phone"
									   class="form-control"
									   pattern="[0-9+()\- ]{7,20}"
									   title="Enter a phone number (7–20 characters, digits/+/()/-/space allowed)">
							</div>
							<div class="form-group">
								<label for="ads_message">Tell us about your business *</label>
								<textarea id="ads_message" name="message"
										  class="form-control" rows="5" required
										  placeholder="Your business name, what you sell, which placements interest you (homepage banner, sidebar, category page, etc.) and the dates you want to run."></textarea>
							</div>

							<button type="submit" class="btn btn-primary">
								Send my advertising request
							</button>
						</form>

						<p style="margin-top:18px;color:#6b7280;">
							Self-serve ad checkout is coming soon. In the meantime our
							sales team will contact you with placements and pricing
							within one business day.
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
