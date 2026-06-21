<?php
$flash_ok  = $this->session->flashdata('message');
$flash_err = $this->session->flashdata('error');
$s = isset($settings) ? $settings : array();
?>
<style>
	.setting-page { padding: 28px 32px 80px; max-width: 980px; margin: 0 auto; }
	.setting-page h1 { font-family: 'Poppins', sans-serif; color: #28282B; margin: 0 0 4px; }
	.setting-page .lede { color: #6b7280; margin-bottom: 24px; }
	.setting-card {
		background: #fff;
		border: 1px solid #e6e8ee;
		border-radius: 14px;
		padding: 24px 26px;
		margin-bottom: 22px;
		box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
	}
	.setting-card h2 {
		font-family: 'Poppins', sans-serif;
		font-size: 1.05rem;
		font-weight: 700;
		color: #909D3E;
		text-transform: uppercase;
		letter-spacing: 0.08em;
		margin: 0 0 4px;
	}
	.setting-card p.hint {
		color: #6b7280;
		font-size: 0.88rem;
		margin: 0 0 18px;
	}
	.setting-row {
		display: grid;
		grid-template-columns: 220px 1fr;
		gap: 14px;
		align-items: start;
		padding: 10px 0;
		border-top: 1px dashed #eef0f4;
	}
	.setting-row:first-of-type { border-top: 0; }
	.setting-row label {
		font-weight: 600;
		color: #28282B;
		font-size: 0.92rem;
		padding-top: 9px;
	}
	.setting-row label small {
		display: block;
		color: #6b7280;
		font-weight: 400;
		font-size: 0.78rem;
		margin-top: 2px;
	}
	.setting-row input[type="text"],
	.setting-row input[type="url"],
	.setting-row input[type="email"],
	.setting-row input[type="tel"],
	.setting-row textarea {
		width: 100%;
		padding: 9px 12px;
		border: 1.5px solid #e2e8f0;
		border-radius: 8px;
		font-size: 0.92rem;
		font-family: 'Open Sans', sans-serif;
		color: #1f2937;
		background: #fff;
		transition: border-color 0.15s ease, box-shadow 0.15s ease;
	}
	.setting-row input:focus,
	.setting-row textarea:focus {
		outline: none;
		border-color: #909D3E;
		box-shadow: 0 0 0 3px rgba(144, 157, 62, 0.18);
	}
	.setting-row textarea { min-height: 76px; resize: vertical; }
	.setting-actions {
		display: flex;
		gap: 10px;
		justify-content: flex-end;
		margin-top: 8px;
	}
	.btn-save {
		background: #909D3E;
		color: #fff;
		border: 0;
		padding: 11px 26px;
		border-radius: 999px;
		font-weight: 600;
		font-family: 'Poppins', sans-serif;
		cursor: pointer;
		box-shadow: 0 8px 18px rgba(144, 157, 62, 0.32);
		transition: background 0.18s ease;
	}
	.btn-save:hover { background: #788332; }
	.alert {
		padding: 12px 16px;
		border-radius: 10px;
		margin-bottom: 18px;
		font-size: 0.93rem;
	}
	.alert-ok { background: #e8eed4; color: #4d5a1a; border: 1px solid #c8d398; }
	.alert-err { background: #fde2e7; color: #591A2D; border: 1px solid #f1b3bf; }
	.alert-warn { background: #fff7e0; color: #6b4f00; border: 1px solid #ead9a0; }
	@media (max-width: 720px) {
		.setting-row { grid-template-columns: 1fr; }
		.setting-row label { padding-top: 0; }
	}
</style>

<div class="setting-page">
	<h1>SEO &amp; Settings</h1>
	<p class="lede">Site-wide configuration. Changes take effect immediately on the next page load.</p>

	<?php if (!empty($flash_ok)): ?>
		<div class="alert alert-ok"><?= e($flash_ok) ?></div>
	<?php endif; ?>
	<?php if (!empty($flash_err)): ?>
		<div class="alert alert-err"><?= e($flash_err) ?></div>
	<?php endif; ?>
	<?php if (empty($has_table)): ?>
		<div class="alert alert-warn">
			<strong>Heads up:</strong> the <code>site_settings</code> table doesn't exist yet.
			Run <code>database/migrations/0007_site_settings.sql</code> via phpMyAdmin, then
			reload this page to start saving values.
		</div>
	<?php endif; ?>

	<form method="post" action="<?= base_url('admin/setting') ?>" autocomplete="off">

		<!-- General ----------------------------------------------------- -->
		<section class="setting-card">
			<h2>General</h2>
			<p class="hint">Site title and description used as the homepage default and as a fallback for pages that don't set their own SEO copy.</p>

			<div class="setting-row">
				<label for="site_title">Site title<small>Shown in the browser tab + Google results</small></label>
				<input type="text" id="site_title" name="site_title" maxlength="200" value="<?= e_attr($s['site_title'] ?? '') ?>" placeholder="Pearland411 — Local Businesses, Events, Deals & News">
			</div>
			<div class="setting-row">
				<label for="site_description">Meta description<small>120–160 characters is ideal</small></label>
				<textarea id="site_description" name="site_description" maxlength="320"><?= e($s['site_description'] ?? '') ?></textarea>
			</div>
			<div class="setting-row">
				<label for="site_keywords">Meta keywords<small>Comma-separated. Largely ignored by Google but kept for Bing.</small></label>
				<input type="text" id="site_keywords" name="site_keywords" value="<?= e_attr($s['site_keywords'] ?? '') ?>" placeholder="Pearland businesses, Pearland events, Pearland deals">
			</div>
			<div class="setting-row">
				<label for="site_og_image">Default share image<small>Absolute URL. Used as og:image when a page doesn't set its own.</small></label>
				<input type="url" id="site_og_image" name="site_og_image" value="<?= e_attr($s['site_og_image'] ?? '') ?>" placeholder="https://new.pearland411.com/assets/images/og-default.jpg">
			</div>
			<div class="setting-row">
				<label for="contact_phone">Contact phone<small>Footer + tel: links</small></label>
				<input type="tel" id="contact_phone" name="contact_phone" value="<?= e_attr($s['contact_phone'] ?? '') ?>" placeholder="281-482-2031">
			</div>
			<div class="setting-row">
				<label for="contact_email">Contact email<small>Footer + mailto: links</small></label>
				<input type="email" id="contact_email" name="contact_email" value="<?= e_attr($s['contact_email'] ?? '') ?>" placeholder="sales@pearland411.com">
			</div>
		</section>

		<!-- SEO / analytics --------------------------------------------- -->
		<section class="setting-card">
			<h2>SEO &amp; Analytics</h2>
			<p class="hint">Verification tokens for Google &amp; Bing webmaster tools, GA4 measurement ID. Tags are only emitted when a value is set.</p>

			<div class="setting-row">
				<label for="google_analytics_id">Google Analytics ID<small>GA4 measurement ID, format <code>G-XXXXXXX</code></small></label>
				<input type="text" id="google_analytics_id" name="google_analytics_id" value="<?= e_attr($s['google_analytics_id'] ?? '') ?>" placeholder="G-XXXXXXXXXX">
			</div>
			<div class="setting-row">
				<label for="google_site_verification">Google Search Console<small>The <code>content="..."</code> value from the GSC HTML-tag method</small></label>
				<input type="text" id="google_site_verification" name="google_site_verification" value="<?= e_attr($s['google_site_verification'] ?? '') ?>">
			</div>
			<div class="setting-row">
				<label for="bing_site_verification">Bing Webmaster Tools<small>The <code>msvalidate.01</code> token</small></label>
				<input type="text" id="bing_site_verification" name="bing_site_verification" value="<?= e_attr($s['bing_site_verification'] ?? '') ?>">
			</div>
			<div class="setting-row">
				<label for="facebook_app_id">Facebook App ID<small>Optional — improves share analytics</small></label>
				<input type="text" id="facebook_app_id" name="facebook_app_id" value="<?= e_attr($s['facebook_app_id'] ?? '') ?>">
			</div>
		</section>

		<!-- Google Maps ------------------------------------------------- -->
		<section class="setting-card">
			<h2>Google Maps</h2>
			<p class="hint">API key with the Maps JavaScript API + Places API enabled. Used to render the map on listing-details pages.</p>

			<div class="setting-row">
				<label for="google_maps_api_key">Maps API key</label>
				<input type="text" id="google_maps_api_key" name="google_maps_api_key" value="<?= e_attr($s['google_maps_api_key'] ?? '') ?>" placeholder="AIzaSy...">
			</div>
		</section>

		<!-- Social ------------------------------------------------------ -->
		<section class="setting-card">
			<h2>Social Media</h2>
			<p class="hint">Profile URLs surfaced in the footer + the Organization JSON-LD <code>sameAs</code> array. Leave a field blank and that icon is hidden.</p>

			<div class="setting-row">
				<label for="social_facebook_url">Facebook</label>
				<input type="url" id="social_facebook_url" name="social_facebook_url" value="<?= e_attr($s['social_facebook_url'] ?? '') ?>" placeholder="https://www.facebook.com/pearland411">
			</div>
			<div class="setting-row">
				<label for="social_twitter_url">Twitter / X</label>
				<input type="url" id="social_twitter_url" name="social_twitter_url" value="<?= e_attr($s['social_twitter_url'] ?? '') ?>" placeholder="https://x.com/pearland411">
			</div>
			<div class="setting-row">
				<label for="social_instagram_url">Instagram</label>
				<input type="url" id="social_instagram_url" name="social_instagram_url" value="<?= e_attr($s['social_instagram_url'] ?? '') ?>" placeholder="https://www.instagram.com/pearland411">
			</div>
			<div class="setting-row">
				<label for="social_linkedin_url">LinkedIn</label>
				<input type="url" id="social_linkedin_url" name="social_linkedin_url" value="<?= e_attr($s['social_linkedin_url'] ?? '') ?>" placeholder="https://www.linkedin.com/company/pearland411">
			</div>
			<div class="setting-row">
				<label for="social_youtube_url">YouTube</label>
				<input type="url" id="social_youtube_url" name="social_youtube_url" value="<?= e_attr($s['social_youtube_url'] ?? '') ?>" placeholder="https://www.youtube.com/@pearland411">
			</div>
		</section>

		<div class="setting-actions">
			<button type="submit" class="btn-save">Save settings</button>
		</div>
	</form>
</div>
