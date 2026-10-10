<?php
/**
 * /post-your-ads (Post your ads) — public page.
 *
 * Two ways to buy: online at /pricing-plan (cart + Helcim checkout, the
 * self-serve flow), or by sending this form, which goes through the same
 * /contact-submit pipeline as /contact-us so a request is never lost.
 */
?>

<section class="login-reg">
	<div class="container">
		<div class="row">
			<div class="col-12">
				<?php // The page that sells the space shows the space. ?>
				<div class="p411-adrow" style="margin-bottom:18px;">
					<?php $slot = $ad_slots['top'] ?? '';
					include APPPATH . 'views/partials/ad_slot.php'; ?>
				</div>
			</div>
		</div>
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
							Display ads on the homepage, listing pages and category pages,
							sold in terms of 3, 6, 12 or 24 months.
						</p>
						<p>
							<a class="btn btn-primary" href="<?= e_url(base_url('pricing-plan')) ?>">Buy online &rarr; see packages &amp; prices</a>
						</p>
						<p>
							Would rather talk it through? Tell us a bit about your business and
							we&rsquo;ll send you placement options and pricing.
						</p>

						<form method="post" action="<?= base_url('contact-submit') ?>">
							<input type="hidden" name="subject" value="Advertising enquiry">
							<?php // Back to this page afterwards (it shows the confirmation), and
							      // filed as a package lead rather than a general question. ?>
							<input type="hidden" name="return_to" value="post-your-ads">
							<input type="hidden" name="topic" value="package">

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
							You can <a href="<?= e_url(base_url('pricing-plan')) ?>">buy any ad package online</a>
							and pay by card at checkout. Send this form instead and our sales team will
							contact you with placements and pricing within one business day.
						</p>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-12">
				<div class="p411-adrow" style="margin-top:10px;">
					<?php $slot = $ad_slots['bottom'] ?? '';
					include APPPATH . 'views/partials/ad_slot.php'; ?>
				</div>
			</div>
		</div>
	</div>
</section>
