<?php
/**
 * /request-quote — the standalone "Request a custom quote" form.
 *
 * /services pointed its three "Request a custom quote" buttons at
 * /request-service, which is the POST handler and redirects any GET straight
 * back to /services. There was no form to reach; this is it. It posts to the
 * same handler as the per-service form on /services/{slug}, with the service
 * chosen from a dropdown instead of taken from the URL.
 *
 * On the shared content shell (.p411-ct, assets/css/directory.css +
 * content.css through $page_css), same as /contact-us.
 *
 * From User::request_quote(): $prefill, $services.
 */

$services = (isset($services) && is_array($services)) ? $services : array();
$prefill  = (isset($prefill)  && is_array($prefill))  ? $prefill  : array();
foreach (array('name', 'email', 'phone', 'company', 'budget_range', 'message', 'service_slug') as $__k) {
	if (!isset($prefill[$__k])) $prefill[$__k] = '';
}

$rq_ok  = $this->session->flashdata('service_request_success');
$rq_err = $this->session->flashdata('service_request_error');

$rq_phone = trim((string) site_setting('contact_phone', '281-482-2031'));
$rq_email = trim((string) site_setting('contact_email', ''));

$budgets = array(
	'under_500'   => 'Under $500',
	'500_1500'    => '$500 – $1,500',
	'1500_5000'   => '$1,500 – $5,000',
	'5000_15000'  => '$5,000 – $15,000',
	'15000_plus'  => '$15,000+',
	'not_sure'    => 'Not sure yet',
);
?>

<section class="p411-ct p411-ct--quote">

	<!-- ================================================================ HERO -->
	<div class="p411-hero">
		<div class="container">
			<nav class="p411-crumb" aria-label="Breadcrumb">
				<a href="<?= base_url() ?>">Home</a>
				<span aria-hidden="true">/</span>
				<a href="<?= e_url(base_url('services')) ?>">Services</a>
				<span aria-hidden="true">/</span>
				<span class="is-current">Request a quote</span>
			</nav>

			<h1>Tell us what you need &mdash; we'll come back with a fixed price</h1>
			<p class="p411-hero__lede">
				Websites, hosting, local SEO, ongoing IT support or something custom.
				Describe the job and we'll price it properly, not by the hour.
			</p>

			<div class="p411-hero__stats">
				<div><b>1 day</b>typical reply</div>
				<div><b>Fixed</b>quote, no surprises</div>
				<div><b>Local</b>based in Pearland</div>
			</div>
		</div>
	</div>

	<div class="container">
		<div class="p411-shell">

			<!-- ============================================================ FORM -->
			<div class="p411-shell__main">

				<div class="p411-adrow">
					<?php $slot = $ad_slots['top'] ?? '';
					include APPPATH . 'views/partials/ad_slot.php'; ?>
				</div>


				<div class="p411-card p411-form">
					<div class="p411-card__head">
						<h2>Request a custom quote</h2>
					</div>

					<?php if (!empty($rq_ok)): ?>
						<div class="p411-note p411-note--ok" role="status"><?= e($rq_ok) ?></div>
					<?php endif; ?>
					<?php if (!empty($rq_err)): ?>
						<div class="p411-note p411-note--err" role="alert"><?= e($rq_err) ?></div>
					<?php endif; ?>

					<p class="p411-form__hint">
						The more you can tell us about the job, the closer the price will be.
						Fields marked <span class="p411-req">*</span> are required.
					</p>

					<form method="post" data-validate action="<?= e_url(base_url('request-service')) ?>">
						<?php // Come back here on error so the message isn't retyped — whitelisted
						      // in User::request_service(), which never redirects to a posted URL. ?>
						<input type="hidden" name="return_to" value="request-quote">

						<div class="p411-form__row">
							<div class="form-group">
								<label for="rq_name">Your name <span class="p411-req">*</span></label>
								<input id="rq_name" type="text" name="name" class="form-control"
									   autocomplete="name" value="<?= e_attr($prefill['name']) ?>" required>
							</div>
							<div class="form-group">
								<label for="rq_phone">Phone</label>
								<input id="rq_phone" type="tel" name="phone" class="form-control"
									   autocomplete="tel" value="<?= e_attr($prefill['phone']) ?>">
							</div>
						</div>

						<div class="p411-form__row">
							<div class="form-group">
								<label for="rq_email">Email <span class="p411-req">*</span></label>
								<input id="rq_email" type="email" name="email" class="form-control"
									   autocomplete="email" value="<?= e_attr($prefill['email']) ?>" required>
							</div>
							<div class="form-group">
								<label for="rq_company">Business / company</label>
								<input id="rq_company" type="text" name="company" class="form-control"
									   autocomplete="organization" value="<?= e_attr($prefill['company']) ?>">
							</div>
						</div>

						<div class="p411-form__row">
							<div class="form-group">
								<label for="rq_service">What's it about?</label>
								<div class="p411-select">
									<select id="rq_service" name="service_slug" class="form-control">
										<option value="">&mdash; something else / not sure &mdash;</option>
										<?php foreach ($services as $svc): ?>
											<option value="<?= e_attr($svc->slug) ?>"
												<?= ($prefill['service_slug'] === $svc->slug) ? 'selected' : '' ?>>
												<?= e($svc->name) ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label for="rq_budget">Budget range</label>
								<div class="p411-select">
									<select id="rq_budget" name="budget_range" class="form-control">
										<option value="">&mdash; select &mdash;</option>
										<?php foreach ($budgets as $bkey => $blabel): ?>
											<option value="<?= e_attr($bkey) ?>"
												<?= ($prefill['budget_range'] === $bkey) ? 'selected' : '' ?>>
												<?= e($blabel) ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
						</div>

						<div class="form-group">
							<label for="rq_message">Tell us about the job <span class="p411-req">*</span></label>
							<textarea id="rq_message" name="message" class="form-control" rows="7"
									  placeholder="What you need, what you have now, and any deadline you're working to."
									  required><?= e($prefill['message']) ?></textarea>
						</div>

						<button type="submit" class="p411-btn p411-btn--lg">Send request</button>
					</form>
				</div>

				<div class="p411-adrow" style="margin-top:22px;">
					<?php $slot = $ad_slots['inline'] ?? '';
					$slot_creative = 'house-ad-cream';
					include APPPATH . 'views/partials/ad_slot.php'; ?>
				</div>
			</div>

			<!-- ============================================================ SIDE -->
			<aside class="p411-shell__side">

				<?php $slot = $ad_slots['side_1'] ?? '';
				$slot_creative = 'house-ad-burgundy';
				include APPPATH . 'views/partials/ad_slot.php'; ?>

				<div class="p411-card">
					<div class="p411-card__head">
						<h2>Rather talk it through?</h2>
					</div>
					<p class="p411-form__hint">
						Some jobs are quicker to describe out loud. Call us and we'll scope it on the phone.
					</p>
					<?php if ($rq_phone !== ''): ?>
						<a class="p411-btn p411-btn--block" href="tel:<?= e_attr(preg_replace('/[^0-9+]/', '', $rq_phone)) ?>">
							Call <?= e($rq_phone) ?>
						</a>
					<?php endif; ?>
					<?php if ($rq_email !== ''): ?>
						<a class="p411-btn p411-btn--block p411-btn--ghost" style="margin-top:8px;"
						   href="mailto:<?= e_attr($rq_email) ?>">Email us</a>
					<?php endif; ?>
				</div>

				<div class="p411-card">
					<div class="p411-card__head">
						<h2>What happens next</h2>
					</div>
					<?php // .p411-prose is the kit's answer to the theme's global
					      // `li { list-style: none }` — see content.css. ?>
					<div class="p411-prose p411-form__hint">
						<ul>
							<li>We read it and come back within one working day.</li>
							<li>If anything's unclear we'll ask before quoting.</li>
							<li>You get a fixed price and a start date &mdash; no hourly surprises.</li>
						</ul>
					</div>
				</div>

				<div class="p411-card">
					<div class="p411-card__head">
						<h2>Browse the catalog</h2>
					</div>
					<p class="p411-form__hint">
						Plenty of jobs are already priced &mdash; worth a look before you write.
					</p>
					<a class="p411-btn p411-btn--block p411-btn--ghost" href="<?= e_url(base_url('services')) ?>">See all services</a>
				</div>

				<?php $slot = $ad_slots['side_2'] ?? '';
				$slot_creative = 'house-ad-navy';
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
