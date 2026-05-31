<style>
	.svc-hero {
		background: linear-gradient(135deg, #180f5e 0%, #2563eb 60%, #7bb73f 100%);
		color: #fff;
		padding: 72px 0 88px;
		text-align: center;
		position: relative;
		overflow: hidden;
	}

	.svc-hero::before {
		content: '';
		position: absolute;
		inset: 0;
		background:
			radial-gradient(circle at 15% 25%, rgba(255, 255, 255, 0.12) 0%, transparent 45%),
			radial-gradient(circle at 85% 75%, rgba(255, 255, 255, 0.08) 0%, transparent 45%);
		pointer-events: none;
	}

	.svc-hero .container { position: relative; z-index: 2; }

	.svc-hero .eyebrow {
		display: inline-block;
		font-size: 0.78rem;
		letter-spacing: 0.16em;
		text-transform: uppercase;
		background: rgba(255, 255, 255, 0.14);
		border: 1px solid rgba(255, 255, 255, 0.25);
		padding: 6px 14px;
		border-radius: 999px;
		margin-bottom: 18px;
	}

	.svc-hero h1 {
		font-size: clamp(1.9rem, 4vw, 2.6rem);
		font-weight: 700;
		margin: 0 0 14px;
		line-height: 1.15;
	}

	.svc-hero p {
		font-size: 1.08rem;
		max-width: 680px;
		margin: 0 auto 26px;
		opacity: 0.92;
		line-height: 1.55;
	}

	.svc-hero .hero-actions {
		display: inline-flex;
		gap: 12px;
		flex-wrap: wrap;
		justify-content: center;
	}

	.svc-hero .hero-actions a {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		padding: 13px 24px;
		border-radius: 999px;
		font-weight: 600;
		text-decoration: none;
		transition: transform 0.2s ease, box-shadow 0.2s ease;
	}

	.svc-hero .hero-actions a.primary {
		background: #fff;
		color: #180f5e;
		box-shadow: 0 14px 30px rgba(0, 0, 0, 0.22);
	}

	.svc-hero .hero-actions a.secondary {
		background: rgba(255, 255, 255, 0.14);
		color: #fff;
		border: 1px solid rgba(255, 255, 255, 0.4);
	}

	.svc-hero .hero-actions a:hover { transform: translateY(-2px); }

	/* Trust strip */
	.svc-trust {
		background: #fff;
		margin-top: -36px;
		border-radius: 18px;
		box-shadow: 0 18px 50px rgba(15, 23, 42, 0.10);
		position: relative;
		z-index: 3;
		padding: 22px 24px;
		display: grid;
		grid-template-columns: repeat(4, 1fr);
		gap: 18px;
	}

	.svc-trust .trust-item {
		display: flex;
		align-items: center;
		gap: 12px;
		font-size: 0.92rem;
		color: #1f2937;
	}

	.svc-trust .trust-item .icon-wrap {
		width: 38px;
		height: 38px;
		border-radius: 50%;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: #e6f4d8;
		color: #4d7a1f;
		flex-shrink: 0;
	}

	.svc-trust .trust-item .icon-wrap .material-icons { font-size: 20px; }

	@media (max-width: 768px) {
		.svc-trust { grid-template-columns: 1fr 1fr; }
	}

	@media (max-width: 480px) {
		.svc-trust { grid-template-columns: 1fr; }
	}

	/* Section + categories */
	.svc-section {
		padding: 56px 0 24px;
	}

	.svc-section-head {
		text-align: center;
		max-width: 640px;
		margin: 0 auto 28px;
	}

	.svc-section-head h2 {
		font-size: 1.85rem;
		font-weight: 700;
		color: #180f5e;
		margin: 0 0 8px;
	}

	.svc-section-head p {
		color: #475569;
		margin: 0;
	}

	.svc-cats {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
		justify-content: center;
		margin-bottom: 32px;
	}

	.svc-cats a {
		display: inline-flex;
		align-items: center;
		padding: 8px 16px;
		border-radius: 999px;
		font-size: 0.9rem;
		font-weight: 500;
		text-decoration: none;
		color: #1f2937;
		background: #fff;
		border: 1.5px solid #e2e8f0;
		transition: all 0.2s ease;
	}

	.svc-cats a:hover {
		border-color: #7bb73f;
		color: #4d7a1f;
		transform: translateY(-1px);
	}

	.svc-cats a.is-active {
		background: linear-gradient(135deg, #7bb73f 0%, #6ba532 100%);
		border-color: #6ba532;
		color: #fff;
		box-shadow: 0 6px 16px rgba(123, 183, 63, 0.32);
	}

	/* Service cards */
	.svc-grid {
		display: grid;
		grid-template-columns: repeat(3, 1fr);
		gap: 22px;
	}

	@media (max-width: 992px) {
		.svc-grid { grid-template-columns: repeat(2, 1fr); }
	}

	@media (max-width: 600px) {
		.svc-grid { grid-template-columns: 1fr; }
	}

	.svc-card {
		background: #fff;
		border: 1px solid #e6e8ee;
		border-radius: 14px;
		padding: 24px 22px 22px;
		display: flex;
		flex-direction: column;
		position: relative;
		overflow: hidden;
		transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
	}

	.svc-card::after {
		content: '';
		position: absolute;
		top: 0; left: 0; right: 0; height: 3px;
		background: linear-gradient(90deg, #7bb73f, #2563eb);
		opacity: 0;
		transition: opacity 0.25s ease;
	}

	.svc-card:hover {
		transform: translateY(-4px);
		box-shadow: 0 18px 40px rgba(15, 23, 42, 0.10);
		border-color: #cdd6e3;
	}

	.svc-card:hover::after { opacity: 1; }

	.svc-card .svc-icon {
		width: 52px;
		height: 52px;
		border-radius: 12px;
		background: linear-gradient(135deg, rgba(123, 183, 63, 0.18), rgba(37, 99, 235, 0.12));
		color: #2563eb;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		margin-bottom: 16px;
	}

	.svc-card .svc-icon .material-icons { font-size: 28px; }

	.svc-card .svc-tag {
		font-size: 0.72rem;
		font-weight: 600;
		text-transform: uppercase;
		letter-spacing: 0.08em;
		color: #6b7280;
		margin-bottom: 6px;
	}

	.svc-card h3 {
		font-size: 1.15rem;
		font-weight: 700;
		color: #180f5e;
		margin: 0 0 10px;
		line-height: 1.3;
	}

	.svc-card h3 a {
		color: inherit;
		text-decoration: none;
	}

	.svc-card h3 a:hover { color: #2563eb; }

	.svc-card p.svc-desc {
		color: #475569;
		font-size: 0.93rem;
		line-height: 1.55;
		margin: 0 0 18px;
		flex: 1;
	}

	.svc-card .svc-footer {
		display: flex;
		justify-content: space-between;
		align-items: center;
		gap: 12px;
		border-top: 1px dashed #e6e8ee;
		padding-top: 14px;
	}

	.svc-card .svc-price {
		display: flex;
		flex-direction: column;
		line-height: 1.1;
	}

	.svc-card .svc-price strong {
		font-size: 1.25rem;
		color: #180f5e;
		font-weight: 700;
	}

	.svc-card .svc-price small {
		font-size: 0.74rem;
		color: #6b7280;
		text-transform: capitalize;
		margin-top: 2px;
	}

	.svc-card .svc-cta {
		display: inline-flex;
		align-items: center;
		gap: 4px;
		padding: 9px 18px;
		background: #180f5e;
		color: #fff;
		border-radius: 999px;
		font-size: 0.86rem;
		font-weight: 600;
		text-decoration: none;
		transition: background 0.2s ease;
	}

	.svc-card .svc-cta:hover {
		background: #2563eb;
		color: #fff;
	}

	.svc-card .svc-cta .material-icons { font-size: 16px; }

	/* Bottom custom-quote panel */
	.svc-custom {
		background: linear-gradient(135deg, #f8fafc 0%, #e6f4d8 100%);
		border-radius: 16px;
		padding: 36px 28px;
		margin: 56px 0 28px;
		text-align: center;
		border: 1px solid #e6e8ee;
	}

	.svc-custom h3 {
		font-size: 1.4rem;
		color: #180f5e;
		margin: 0 0 8px;
		font-weight: 700;
	}

	.svc-custom p {
		color: #475569;
		max-width: 540px;
		margin: 0 auto 18px;
	}

	.svc-custom a {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		padding: 13px 26px;
		background: linear-gradient(135deg, #7bb73f 0%, #6ba532 100%);
		color: #fff;
		border-radius: 999px;
		font-weight: 600;
		text-decoration: none;
		box-shadow: 0 10px 22px rgba(123, 183, 63, 0.35);
		transition: transform 0.2s ease;
	}

	.svc-custom a:hover {
		transform: translateY(-2px);
		color: #fff;
	}

	.svc-empty {
		text-align: center;
		padding: 60px 20px;
		color: #475569;
		background: #f8fafc;
		border-radius: 14px;
	}
</style>

<?php
// Map the icon strings in the services.icon column (set by 0002_services.sql)
// to Material Icon names. Anything not in the map falls back to a generic
// "widgets" glyph.
$svc_icon_map = array(
	'web'          => 'language',
	'cloud'        => 'cloud',
	'tools'        => 'build',
	'search'       => 'search',
	'palette'      => 'palette',
	'shopping_cart'=> 'shopping_cart',
	'support_agent'=> 'support_agent',
	'mail'         => 'mail',
	'campaign'     => 'campaign',
	'share'        => 'share',
);
?>

<section class="svc-hero">
	<div class="container">
		<span class="eyebrow">Pearland411 Services</span>
		<h1>Websites, hosting, SEO &amp; IT support — built for Pearland businesses</h1>
		<p>One local team for everything your business needs to show up online and stay running. No agency hand-offs, no offshore call centers.</p>
		<div class="hero-actions">
			<a class="primary" href="#services">
				<i class="material-icons" aria-hidden="true">apps</i>
				Browse services
			</a>
			<a class="secondary" href="<?= base_url('request-service') ?>">
				Request a custom quote
			</a>
		</div>
	</div>
</section>

<section class="container" style="padding:0 15px;">
	<div class="svc-trust">
		<div class="trust-item">
			<span class="icon-wrap"><i class="material-icons">location_on</i></span>
			<span><strong>Local team</strong><br><small style="color:#6b7280;">Based in Pearland, Texas</small></span>
		</div>
		<div class="trust-item">
			<span class="icon-wrap"><i class="material-icons">verified_user</i></span>
			<span><strong>Fixed pricing</strong><br><small style="color:#6b7280;">No surprise invoices</small></span>
		</div>
		<div class="trust-item">
			<span class="icon-wrap"><i class="material-icons">phone_in_talk</i></span>
			<span><strong>Real support</strong><br><small style="color:#6b7280;">Phone &amp; email, M–F</small></span>
		</div>
		<div class="trust-item">
			<span class="icon-wrap"><i class="material-icons">handshake</i></span>
			<span><strong>Month-to-month</strong><br><small style="color:#6b7280;">Cancel anytime</small></span>
		</div>
	</div>
</section>

<section class="svc-section" id="services">
	<div class="container">

		<?php $ok = $this->session->flashdata('service_request_success'); if (!empty($ok)): ?>
			<div class="alert alert-success" style="border-radius:10px;"><?= html_escape($ok) ?></div>
		<?php endif; ?>
		<?php $err = $this->session->flashdata('service_request_error'); if (!empty($err)): ?>
			<div class="alert alert-danger" style="border-radius:10px;"><?= html_escape($err) ?></div>
		<?php endif; ?>

		<div class="svc-section-head">
			<h2>Everything you need under one roof</h2>
			<p>Pick a service to learn more, see what's included and get started.</p>
		</div>

		<?php if (!empty($categories)): ?>
			<div class="svc-cats">
				<a href="<?= base_url('services') ?>" class="<?= empty($selected_category) ? 'is-active' : '' ?>">
					All services
				</a>
				<?php foreach ($categories as $c): if (empty($c->category)) continue; ?>
					<a href="<?= base_url('services?category=' . rawurlencode($c->category)) ?>"
					   class="<?= ($selected_category === $c->category) ? 'is-active' : '' ?>">
						<?= e($c->category) ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if (empty($services)): ?>
			<div class="svc-empty">
				<h3 style="color:#180f5e;margin:0 0 8px;">No services in this category yet</h3>
				<p style="margin:0 0 16px;">Browse all services or request a custom quote.</p>
				<a href="<?= base_url('services') ?>" class="btn btn-primary">View all services</a>
			</div>
		<?php else: ?>
			<div class="svc-grid">
				<?php foreach ($services as $svc): ?>
					<?php
						$detail_url = base_url('services/' . rawurlencode((string) $svc->slug));
						$icon_key   = isset($svc->icon) ? (string) $svc->icon : '';
						$icon_name  = $svc_icon_map[$icon_key] ?? 'widgets';
					?>
					<article class="svc-card">
						<div class="svc-icon">
							<i class="material-icons" aria-hidden="true"><?= e($icon_name) ?></i>
						</div>

						<?php if (!empty($svc->category)): ?>
							<div class="svc-tag"><?= e($svc->category) ?></div>
						<?php endif; ?>

						<h3><a href="<?= e_attr($detail_url) ?>"><?= e($svc->name) ?></a></h3>

						<p class="svc-desc"><?= e($svc->short_description) ?></p>

						<div class="svc-footer">
							<div class="svc-price">
								<?php if ($svc->base_price !== null && $svc->price_unit !== 'custom'): ?>
									<strong>$<?= number_format((float) $svc->base_price, 0) ?></strong>
									<small><?= e(str_replace('_', ' ', $svc->price_unit)) ?></small>
								<?php else: ?>
									<strong>Custom quote</strong>
									<small>Tailored to your needs</small>
								<?php endif; ?>
							</div>
							<a href="<?= e_attr($detail_url) ?>" class="svc-cta">
								Learn more
								<i class="material-icons" aria-hidden="true">arrow_forward</i>
							</a>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="svc-custom">
			<h3>Don't see what you need?</h3>
			<p>Custom development, integrations, ongoing IT — we'll scope a fixed-price quote tailored to your business.</p>
			<a href="<?= base_url('request-service') ?>">
				Request a custom quote
				<i class="material-icons" aria-hidden="true">arrow_forward</i>
			</a>
		</div>
	</div>
</section>
