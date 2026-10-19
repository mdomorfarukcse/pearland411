<?php
/**
 * /contact-us — public contact form.
 *
 * Rendered between the public header.php + footer.php (which already emit
 * <html>/<head>/<body>) — this view is a FRAGMENT, not a document.
 *
 * Rebuilt on the shared page kit (assets/css/directory.css + content.css
 * through $page_css). It used to carry ~260 lines of its own <style> — inside
 * a doubled `<style><style>` tag, so the first line of CSS was printed to the
 * page as text — in the old palette (#180f5e navy on a #2a1a8a gradient with
 * #7bb73f icons).
 *
 * The form posts to /contact-submit (User::contact_submit) which persists into
 * `enquiries`, emails the admin address and acknowledges to the sender.
 */

$c_phone = trim((string) site_setting('contact_phone', '281-482-2031'));
$c_email = trim((string) site_setting('contact_email', 'sales@pearland411.com'));
$c_tel   = preg_replace('/[^0-9+]/', '', $c_phone);

$c_ok  = $this->session->flashdata('contact_success');
$c_err = $this->session->flashdata('contact_error');
?>

<section class="p411-ct p411-ct--contact">

	<!-- ================================================================ HERO -->
	<div class="p411-hero">
		<div class="p411-hero__bg" style="background-image:url('<?= base_url('assets/images/pearland-contact.jpg') ?>');" aria-hidden="true"></div>
		<div class="container">
			<nav class="p411-crumb" aria-label="Breadcrumb">
				<a href="<?= base_url() ?>">Home</a>
				<span aria-hidden="true">/</span>
				<span class="is-current">Contact us</span>
			</nav>

			<h1>Get in touch with Pearland411</h1>
			<p class="p411-hero__lede">
				A question about your listing, an advert you'd like to run, or something that isn't working —
				write to us here, or pick up the phone. We answer both.
			</p>

			<div class="p411-hero__stats">
				<div><b>24h</b>typical reply</div>
				<div><b>Mon&ndash;Sat</b>phone lines open</div>
				<div><b>Local</b>Pearland, TX</div>
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
						<h2>Send us a message</h2>
					</div>

					<?php if (!empty($c_ok)): ?>
						<div class="p411-note p411-note--ok" role="status"><?= e($c_ok) ?></div>
					<?php endif; ?>
					<?php if (!empty($c_err)): ?>
						<div class="p411-note p411-note--err" role="alert"><?= e($c_err) ?></div>
					<?php endif; ?>

					<p class="p411-form__hint">
						Fill this in and we'll get back to you &mdash; usually the same working day.
						Fields marked <span class="p411-req">*</span> are required.
					</p>

					<form id="p411ContactForm" method="post" data-validate action="<?= base_url('contact-submit') ?>">
						<input type="hidden" name="return_to" value="contact-us">

						<div class="p411-form__row">
							<div class="form-group">
								<label for="p411_full_name">Full name <span class="p411-req">*</span></label>
								<input id="p411_full_name" type="text" class="form-control" name="fullName"
									   autocomplete="name" required>
							</div>
							<div class="form-group">
								<label for="p411_phone">Phone number</label>
								<input id="p411_phone" type="tel" class="form-control" name="phone"
									   autocomplete="tel" pattern="[0-9+()\- ]{7,20}"
									   title="Enter a phone number (7–20 characters)">
							</div>
						</div>

						<div class="form-group">
							<label for="p411_email">Email address <span class="p411-req">*</span></label>
							<input id="p411_email" type="email" class="form-control" name="email"
								   autocomplete="email" required>
						</div>

						<div class="form-group">
							<label for="p411_subject">Subject <span class="p411-req">*</span></label>
							<input id="p411_subject" type="text" class="form-control" name="subject"
								   placeholder="What is this about?" required>
						</div>

						<div class="form-group">
							<label for="p411_message">Message <span class="p411-req">*</span></label>
							<textarea id="p411_message" class="form-control" name="message" rows="6"
									  minlength="10" required></textarea>
						</div>

						<button type="submit" class="p411-btn p411-btn--lg">Send message</button>
					</form>
				</div>

				<div class="p411-adrow" style="margin-top:22px;">
					<?php $slot = $ad_slots['inline'] ?? '';
					$slot_creative = 'house-ad-cream';
					include APPPATH . 'views/partials/ad_slot.php'; ?>
				</div>

				<?php $promo_variant = 'banner';
				include APPPATH . 'views/partials/service_promo.php'; ?>

			</div>

			<!-- ========================================================= SIDEBAR -->
			<aside class="p411-shell__side">

				<?php // Above the contact card deliberately: the sidebar's own
				      // content is short here, so a zone below the fold would
				      // never be seen. ?>
				<?php $slot = $ad_slots['side_1'] ?? '';
				$slot_creative = 'house-ad-burgundy';
				include APPPATH . 'views/partials/ad_slot.php'; ?>

				<div class="p411-card">
					<div class="p411-card__head">
						<h2>Reach us directly</h2>
					</div>

					<ul class="p411-contact">
						<?php if ($c_phone !== ''): ?>
							<li>
								<i aria-hidden="true">
									<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>
								</i>
								<div>
									<h3>Phone</h3>
									<p><a href="tel:<?= e_attr($c_tel) ?>"><?= e($c_phone) ?></a></p>
									<p class="p411-contact__sub">Mon&ndash;Fri 9:00&nbsp;AM&ndash;6:00&nbsp;PM, Sat 10:00&nbsp;AM&ndash;4:00&nbsp;PM</p>
								</div>
							</li>
						<?php endif; ?>

						<?php if ($c_email !== ''): ?>
							<li>
								<i aria-hidden="true">
									<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/></svg>
								</i>
								<div>
									<h3>Email</h3>
									<p><a href="mailto:<?= e_attr($c_email) ?>"><?= e($c_email) ?></a></p>
									<p class="p411-contact__sub">We reply within one working day</p>
								</div>
							</li>
						<?php endif; ?>

						<li>
							<i aria-hidden="true">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>
							</i>
							<div>
								<h3>Where we are</h3>
								<p>Pearland, Texas</p>
								<p class="p411-contact__sub">Serving 77581, 77584 and 77588</p>
							</div>
						</li>

						<li>
							<i aria-hidden="true">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
							</i>
							<div>
								<h3>Opening hours</h3>
								<p>Mon&ndash;Fri 9:00&nbsp;AM&ndash;6:00&nbsp;PM</p>
								<p>Sat 10:00&nbsp;AM&ndash;4:00&nbsp;PM &middot; Sun closed</p>
							</div>
						</li>
					</ul>
				</div>

				<div class="p411-card">
					<div class="p411-card__head">
						<h2>Want to advertise?</h2>
					</div>
					<p class="p411-form__hint">
						<?php $p411_from = ad_price_from(); ?>
						Listing your business is free. Banners, featured placement and video
						<?= $p411_from !== '' ? 'start at ' . e($p411_from) . ' a month' : 'are available' ?> &mdash;
						and we'll happily set the whole thing up over the phone.
					</p>
					<a class="p411-btn p411-btn--block" href="<?= e_url(base_url('pricing-plan')) ?>">See packages &amp; prices</a>
					<a class="p411-btn p411-btn--block p411-btn--ghost" style="margin-top:8px;"
					   href="<?= e_url(base_url('pricing-plan')) ?>#talk-to-us">Ask us to call you back</a>
				</div>

				<div class="p411-card">
					<div class="p411-card__head">
						<h2>Common questions</h2>
					</div>
					<ul class="p411-catlist">
						<li><a href="<?= e_url(base_url('faq')) ?>">How do I add my business?</a></li>
						<?php // /claim-listing needs a slug, so this points at the
						      // directory: find the listing, claim it from there. ?>
						<li><a href="<?= e_url(base_url('all-listing')) ?>">This is my business &mdash; how do I claim it?</a></li>
						<li><a href="<?= e_url(base_url('pricing-plan')) ?>">What does advertising cost?</a></li>
						<li><a href="<?= e_url(base_url('all-category')) ?>">Browse every category</a></li>
					</ul>
				</div>

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
