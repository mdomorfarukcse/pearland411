<?php
/**
 * /services/{slug} — one service, with the quote form beside it.
 *
 * On the listing-details shell (`.p411-ld`, assets/css/directory.css +
 * content.css through $page_css), so a service page reads like a business page
 * rather than the bare inline-styled two-column block it was (#0e2a3a header,
 * #f3f4f6 price well, unstyled Bootstrap form).
 *
 * From User::service_details(): $service (required), $prefill.
 */

$prefill = (isset($prefill) && is_array($prefill))
	? $prefill
	: array('name' => '', 'email' => '', 'phone' => '', 'company' => '');

$has_price = ($service->base_price !== null && $service->price_unit !== 'custom');

$sd_ok  = $this->session->flashdata('service_request_success');
$sd_err = $this->session->flashdata('service_request_error');

$sd_phone = trim((string) site_setting('contact_phone', '281-482-2031'));
?>

<section class="p411-ld p411-ld--service">

	<!-- ================================================================ HERO -->
	<div class="p411-ld__hero">
		<div class="container">
			<nav class="p411-crumb" aria-label="Breadcrumb">
				<a href="<?= base_url() ?>">Home</a>
				<span aria-hidden="true">/</span>
				<a href="<?= base_url('services') ?>">Services</a>
				<span aria-hidden="true">/</span>
				<span class="is-current"><?= e($service->name) ?></span>
			</nav>

			<div class="p411-ld__ident">
				<div class="p411-ld__head">
					<h1><?= e($service->name) ?></h1>

					<div class="p411-ld__badges">
						<?php if (!empty($service->category)): ?>
							<span class="p411-tag p411-tag--navy"><?= e($service->category) ?></span>
						<?php endif; ?>
						<span class="p411-tag p411-tag--green">
							<?= $has_price
								? e(implode(' ', service_price_label($service)))
								: 'Custom quote' ?>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="container">

		<?php if (!empty($sd_ok)): ?>
			<div class="p411-note p411-note--ok" style="margin-top:22px;" role="status"><?= e($sd_ok) ?></div>
		<?php endif; ?>
		<?php if (!empty($sd_err)): ?>
			<div class="p411-note p411-note--err" style="margin-top:22px;" role="alert"><?= e($sd_err) ?></div>
		<?php endif; ?>

		<div class="p411-shell">

			<!-- ============================================================ MAIN -->
			<div class="p411-shell__main">

				<div class="p411-adrow">
					<?php $slot = $ad_slots['top'] ?? '';
					include APPPATH . 'views/partials/ad_slot.php'; ?>
				</div>


				<div class="p411-card">
					<div class="p411-card__head">
						<h2>What this is</h2>
					</div>

					<div class="p411-prose">
						<p class="p411-lede"><?= e($service->short_description) ?></p>

						<?php if (!empty($service->body)): ?>
							<?php
							/* Admin-edited HTML from the `services` table, through the
							   same sanitiser the news body uses: structural tags kept,
							   every attribute dropped bar href/title on a link. bare
							   strip_tags() left attributes intact, so a pasted
							   style="color:…" repainted the page. */
							echo content_html($service->body);
							?>
						<?php endif; ?>
					</div>
				</div>

				<div class="p411-card">
					<div class="p411-card__head">
						<h2>What it costs</h2>
					</div>

					<div class="p411-price">
						<?php if ($has_price): $__pl = service_price_label($service); ?>
							<b><?= e($__pl[0]) ?></b>
							<span><?= e($__pl[1]) ?></span>
							<p>Final pricing depends on the scope of the job. Send us the details and we'll come back with an exact figure &mdash; there is no charge for the quote.</p>
						<?php else: ?>
							<b>Custom quote</b>
							<span>priced to your job</span>
							<p>Tell us what you need and we'll come back with a fixed price. There is no charge for the quote and no obligation.</p>
						<?php endif; ?>
					</div>
				</div>

				<?php $promo_variant = 'banner';
				include APPPATH . 'views/partials/service_promo.php'; ?>

				<div class="p411-adrow" style="margin-top:22px;">
					<?php $slot = $ad_slots['inline'] ?? '';
					$slot_creative = 'house-ad-cream';
					include APPPATH . 'views/partials/ad_slot.php'; ?>
				</div>
			</div>

			<!-- ===================================================== QUOTE FORM -->
			<aside class="p411-shell__side">

				<div class="p411-card p411-form p411-quote">
					<div class="p411-card__head">
						<h2>Request a quote</h2>
					</div>
					<p class="p411-form__hint">
						A bit about the project and we'll get back to you within one working day.
					</p>

					<form method="post" action="<?= base_url('request-service/' . rawurlencode((string) $service->slug)) ?>">
						<div class="form-group">
							<label for="rs_name">Your name <span class="p411-req">*</span></label>
							<input id="rs_name" type="text" name="name" class="form-control"
								   autocomplete="name" value="<?= e_attr($prefill['name']) ?>" required>
						</div>
						<div class="form-group">
							<label for="rs_email">Email <span class="p411-req">*</span></label>
							<input id="rs_email" type="email" name="email" class="form-control"
								   autocomplete="email" value="<?= e_attr($prefill['email']) ?>" required>
						</div>
						<div class="form-group">
							<label for="rs_phone">Phone</label>
							<input id="rs_phone" type="tel" name="phone" class="form-control"
								   autocomplete="tel" value="<?= e_attr($prefill['phone']) ?>">
						</div>
						<div class="form-group">
							<label for="rs_company">Business / company</label>
							<input id="rs_company" type="text" name="company" class="form-control"
								   autocomplete="organization" value="<?= e_attr($prefill['company']) ?>">
						</div>
						<div class="form-group">
							<label for="rs_budget">Budget range</label>
							<div class="p411-select">
								<select id="rs_budget" name="budget_range" class="form-control">
									<option value="">&mdash; select &mdash;</option>
									<option value="under_500">Under $500</option>
									<option value="500_1500">$500 &ndash; $1,500</option>
									<option value="1500_5000">$1,500 &ndash; $5,000</option>
									<option value="5000_15000">$5,000 &ndash; $15,000</option>
									<option value="15000_plus">$15,000+</option>
									<option value="not_sure">Not sure yet</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="rs_message">Tell us about your project <span class="p411-req">*</span></label>
							<textarea id="rs_message" name="message" class="form-control" rows="4" required></textarea>
						</div>

						<?= spam_form_fields() ?>
						<button type="submit" class="p411-btn p411-btn--block p411-btn--lg">Send request</button>
					</form>

					<?php if ($sd_phone !== ''): ?>
						<p class="p411-form__hint" style="margin:12px 0 0;">
							Rather talk it through? Call
							<a href="tel:<?= e_attr(preg_replace('/[^0-9+]/', '', $sd_phone)) ?>"><?= e($sd_phone) ?></a>.
						</p>
					<?php endif; ?>
				</div>

				<div class="p411-card">
					<div class="p411-card__head">
						<h2>Other services</h2>
					</div>
					<p class="p411-form__hint">
						Websites, hosting, local SEO and IT support &mdash; all from the same local team.
					</p>
					<a class="p411-btn p411-btn--block p411-btn--ghost" href="<?= e_url(base_url('services')) ?>">See all services</a>
				</div>

				<?php // Below the quote form, not above it — the form is why the
				      // visitor is here and must not be pushed under an ad. ?>
				<?php $slot = $ad_slots['side_1'] ?? '';
				$slot_creative = 'house-ad-burgundy';
				include APPPATH . 'views/partials/ad_slot.php'; ?>

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
