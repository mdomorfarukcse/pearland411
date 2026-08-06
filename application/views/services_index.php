<?php
/**
 * /services — the Pearland411 web / IT services catalog.
 *
 * Rebuilt on the shared page kit (assets/css/directory.css + content.css
 * through $page_css). The page previously carried ~370 lines of its own
 * <style>: a #180f5e → #2563eb → #7bb73f three-colour hero, blue-tinted cards
 * and a lime-gradient call to action, none of which exist elsewhere on the
 * site.
 *
 * From User::services(): $services, $categories, $selected_category.
 */

$services   = (isset($services)   && is_array($services))   ? $services   : array();
$categories = (isset($categories) && is_array($categories)) ? $categories : array();
$selected_category = isset($selected_category) ? (string) $selected_category : '';

// The `services`.`icon` column (0002_services.sql) stores short keys; map them
// onto Material Icon names, with a generic glyph for anything unrecognised.
$svc_icon_map = array(
	'web'           => 'language',
	'cloud'         => 'cloud',
	'tools'         => 'build',
	'search'        => 'search',
	'palette'       => 'palette',
	'shopping_cart' => 'shopping_cart',
	'support_agent' => 'support_agent',
	'mail'          => 'mail',
	'campaign'      => 'campaign',
	'share'         => 'share',
);

$svc_ok  = $this->session->flashdata('service_request_success');
$svc_err = $this->session->flashdata('service_request_error');

$svc_phone = trim((string) site_setting('contact_phone', '281-482-2031'));
?>

<section class="p411-ct p411-ct--services">

	<!-- ================================================================ HERO -->
	<div class="p411-hero">
		<div class="container">
			<nav class="p411-crumb" aria-label="Breadcrumb">
				<a href="<?= base_url() ?>">Home</a>
				<span aria-hidden="true">/</span>
				<span class="is-current">Services</span>
			</nav>

			<h1>Websites, hosting, SEO and IT support &mdash; built for Pearland businesses</h1>
			<p class="p411-hero__lede">
				One local team for everything your business needs to show up online and stay running.
				No agency hand-offs, no offshore call centres.
			</p>

			<div class="p411-hero__stats">
				<div><b>Local</b>based in Pearland</div>
				<div><b>Fixed</b>pricing, no surprises</div>
				<div><b>Monthly</b>cancel any time</div>
			</div>

			<div class="p411-owner__cta" style="margin-top:20px;">
				<a class="p411-btn" href="#services">Browse services</a>
				<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('request-quote')) ?>">Request a custom quote</a>
			</div>
		</div>
	</div>

	<div class="container" id="services">

		<div class="p411-adrow" style="margin-top:22px;">
			<?php $slot = $ad_slots['top'] ?? '';
			include APPPATH . 'views/partials/ad_slot.php'; ?>
		</div>

		<?php if (!empty($svc_ok)): ?>
			<div class="p411-note p411-note--ok" style="margin-top:22px;" role="status"><?= e($svc_ok) ?></div>
		<?php endif; ?>
		<?php if (!empty($svc_err)): ?>
			<div class="p411-note p411-note--err" style="margin-top:22px;" role="alert"><?= e($svc_err) ?></div>
		<?php endif; ?>

		<!-- ============================================================== TRUST -->
		<div class="p411-feat4 p411-feat4--trust">
			<div>
				<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg></i>
				<h3>A local team</h3>
				<p>Based in Pearland, Texas &mdash; you can meet us.</p>
			</div>
			<div>
				<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 4 6v6c0 4.4 3.4 8.5 8 9 4.6-.5 8-4.6 8-9V6z"/><path d="m9 12 2 2 4-4"/></svg></i>
				<h3>Fixed pricing</h3>
				<p>You agree the price before we start. No surprise invoices.</p>
			</div>
			<div>
				<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg></i>
				<h3>Somebody answers</h3>
				<p>Phone and email support, Monday to Friday.</p>
			</div>
			<div>
				<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12h4l3 7 4-14 3 7h4"/></svg></i>
				<h3>Month to month</h3>
				<p>No long contracts. Stop whenever you like.</p>
			</div>
		</div>

		<!-- =========================================================== CATEGORY -->
		<div class="p411-sec">
			<div class="p411-sec__head">
				<h2>Everything under one roof</h2>
			</div>
			<p class="p411-lede">Pick a service to see what's included and what it costs.</p>

			<?php if (!empty($categories)): ?>
				<div class="p411-ct-tabs" role="navigation" aria-label="Filter services by category">
					<a href="<?= e_url(base_url('services')) ?>" class="<?= $selected_category === '' ? 'is-active' : '' ?>">All services</a>
					<?php foreach ($categories as $c): ?>
						<?php if (empty($c->category)) continue; ?>
						<a href="<?= e_url(base_url('services?category=' . rawurlencode((string) $c->category))) ?>"
						   class="<?= $selected_category === (string) $c->category ? 'is-active' : '' ?>">
							<?= e($c->category) ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if (empty($services)): ?>
				<div class="p411-empty">
					<div class="p411-empty__icon">
						<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M8 9h8M8 13h5"/>
						</svg>
					</div>
					<h3>Nothing listed in this category yet</h3>
					<p>Have a look at everything we do, or tell us what you need and we'll quote for it.</p>
					<div class="p411-empty__actions">
						<a class="p411-btn" href="<?= e_url(base_url('services')) ?>">View all services</a>
						<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('request-quote')) ?>">Request a quote</a>
					</div>
				</div>
			<?php else: ?>
				<div class="p411-svcgrid">
					<?php foreach ($services as $svc): ?>
						<?php
						$detail_url = base_url('services/' . rawurlencode((string) $svc->slug));
						$icon_key   = isset($svc->icon) ? (string) $svc->icon : '';
						$icon_name  = isset($svc_icon_map[$icon_key]) ? $svc_icon_map[$icon_key] : 'widgets';
						$has_price  = ($svc->base_price !== null && $svc->price_unit !== 'custom');
						?>
						<article class="p411-svc">
							<div class="p411-svc__top">
								<span class="p411-svc__icon" aria-hidden="true">
									<i class="material-icons"><?= e($icon_name) ?></i>
								</span>
								<?php if (!empty($svc->category)): ?>
									<span class="p411-tag p411-tag--navy"><?= e($svc->category) ?></span>
								<?php endif; ?>
							</div>

							<h3><a href="<?= e_url($detail_url) ?>"><?= e($svc->name) ?></a></h3>
							<p class="p411-svc__desc"><?= e($svc->short_description) ?></p>

							<div class="p411-svc__foot">
								<span class="p411-svc__price">
									<?php if ($has_price): ?>
										<b>$<?= number_format((float) $svc->base_price, 0) ?></b>
										<small><?= e(str_replace('_', ' ', (string) $svc->price_unit)) ?></small>
									<?php else: ?>
										<b>Custom quote</b>
										<small>priced to your job</small>
									<?php endif; ?>
								</span>
								<a class="p411-btn p411-btn--sm" href="<?= e_url($detail_url) ?>">Learn more</a>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="p411-adrow" style="margin-top:22px;">
			<?php $slot = $ad_slots['inline'] ?? '';
			$slot_creative = 'house-ad-cream';
			include APPPATH . 'views/partials/ad_slot.php'; ?>
		</div>
	</div>

	<!-- ========================================================== OWNER BAND -->
	<div class="p411-owner">
		<div class="container">
			<div class="p411-owner__head">
				<h2>Don't see what you need?</h2>
				<p>
					Custom development, integrations, ongoing IT &mdash; tell us what the job is and we'll come back
					with a fixed price. Or just call us; it's usually quicker.
				</p>
			</div>
			<div class="p411-owner__cta">
				<a class="p411-btn" href="<?= e_url(base_url('request-quote')) ?>">Request a custom quote</a>
				<?php if ($svc_phone !== ''): ?>
					<a class="p411-btn p411-btn--ghost" href="tel:<?= e_attr(preg_replace('/[^0-9+]/', '', $svc_phone)) ?>">Call <?= e($svc_phone) ?></a>
				<?php endif; ?>
				<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('contact-us')) ?>">Send a message</a>
			</div>
		</div>
	</div>

	<div class="container">
		<div class="p411-adrow" style="margin-top:6px;">
			<?php $slot = $ad_slots['bottom'] ?? '';
			include APPPATH . 'views/partials/ad_slot.php'; ?>
		</div>
	</div>
</section>
