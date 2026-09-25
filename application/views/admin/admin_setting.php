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

	<!-- Payments — read-only status, deliberately outside the form ------ -->
	<section class="setting-card">
		<h2>Payments (Helcim)</h2>
		<p class="hint">
			Payment credentials live in <code>.env</code>, never in this form or the database
			(see <code>README.md</code> &rarr; Environment). This panel only reports what the
			server currently sees.
		</p>

		<?php if (empty($helcim_configured)): ?>
			<div class="alert alert-warn">
				<strong>Not configured.</strong> <code>HELCIM_API_TOKEN</code> is empty, so
				<code>/checkout</code> shows an &ldquo;online payments are being set up&rdquo;
				notice instead of the pay button. No one can subscribe until this is set.
			</div>
		<?php else: ?>
			<div class="alert alert-ok">
				<strong>Token present.</strong> Charging in <?= e($helcim_currency ?? 'USD') ?>.
			</div>

			<?php if (($helcim_health ?? null) === null): ?>
				<p class="hint">
					A token being present doesn&rsquo;t prove it has the right permissions — a
					wrongly-scoped token still shows a working-looking pay button and only fails
					when a customer clicks it. Run the live check to be sure.
				</p>
				<p><a href="<?= base_url('admin/setting?payments_check=1') ?>">Test connection to Helcim &rarr;</a></p>
			<?php elseif (!empty($helcim_health['success'])): ?>
				<div class="alert alert-ok">
					<strong>Connection OK.</strong> Helcim accepted the token for checkout.
				</div>
				<p class="hint">
					This confirms the checkout permission only. Auto-renewal charges use a
					different permission (&ldquo;Transaction Processing&rdquo;) — verify it by
					running <code>php index.php cli renew_subscriptions</code>, otherwise a
					scope problem there stays hidden until the first renewal falls due.
				</p>
			<?php else: ?>
				<div class="alert alert-err">
					<strong>Connection failed.</strong> <?= e($helcim_health['error']) ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</section>

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
				<input type="url" id="site_og_image" name="site_og_image" value="<?= e_attr($s['site_og_image'] ?? '') ?>" placeholder="https://pearland411.com/assets/images/og-default.jpg">
			</div>
			<div class="setting-row">
				<label for="site_logo_url">Header &amp; footer logo<small>URL of the light logo used on the navy header and footer. Upload it to <code>assets/images/</code> and paste the address, or leave blank for the built-in default.</small></label>
				<input type="url" id="site_logo_url" name="site_logo_url" value="<?= e_attr($s['site_logo_url'] ?? '') ?>" placeholder="https://pearland411.com/assets/images/pearland-logo-light.png">
			</div>
			<div class="setting-row">
				<label for="contact_phone">Contact phone<small>Footer + tel: links</small></label>
				<input type="tel" id="contact_phone" name="contact_phone" value="<?= e_attr($s['contact_phone'] ?? '') ?>" placeholder="281-482-2031">
			</div>
			<div class="setting-row">
				<label for="contact_email">Contact email<small>Footer + mailto: links</small></label>
				<input type="email" id="contact_email" name="contact_email" value="<?= e_attr($s['contact_email'] ?? '') ?>" placeholder="sales@pearland411.com">
			</div>
			<div class="setting-row">
				<label for="service_quote_email">Quote requests to<small>Where /request-quote and service quote forms are emailed. Blank falls back to the address below.</small></label>
				<input type="email" id="service_quote_email" name="service_quote_email" value="<?= e_attr($s['service_quote_email'] ?? '') ?>" placeholder="joshua@spacecentersystems.com">
			</div>
			<div class="setting-row">
				<label for="forms_recipient_email">All form submissions to<small>One address for every public form &mdash; enquiries, contact, callback requests and quotes. Each form falls back to this before the site's From address, so setting it here changes them all at once.</small></label>
				<input type="email" id="forms_recipient_email" name="forms_recipient_email" value="<?= e_attr($s['forms_recipient_email'] ?? '') ?>" placeholder="info@pearland411.com">
			</div>
		</section>

		<!-- Enquiry routing --------------------------------------------- -->
		<section class="setting-card">
			<h2>Enquiry routing</h2>
			<p class="hint">
				The contact form and the callback panel on <code>/pricing-plan</code> ask the
				visitor <strong>which department</strong> they need. This used to be a list of nine
				sales representatives by name; most of them no longer work here, so a caller could
				ask for somebody who left and the message would sit waiting for a person who was
				never going to pick it up. A department outlives whoever staffs it.
				The first one in the list is the default &mdash; it is what the forms preselect.
			</p>
			<div class="setting-row">
				<label for="enquiry_departments">Departments<small>One per line. Leave blank for the built-in list: Sales Department, Customer Service, Billing Department, Advertising &amp; Marketing, Technical Support.</small></label>
				<textarea id="enquiry_departments" name="enquiry_departments" rows="6"
						  placeholder="Sales Department&#10;Customer Service&#10;Billing Department&#10;Advertising &amp; Marketing&#10;Technical Support"><?= e($s['enquiry_departments'] ?? '') ?></textarea>
			</div>
			<div class="setting-row">
				<label for="enquiry_department_emails">Department mailboxes<small>Optional, one <code>Department = address</code> per line. A department listed here gets its messages directly; anything not listed falls back to the address above, so leaving this blank changes nothing.</small></label>
				<textarea id="enquiry_department_emails" name="enquiry_department_emails" rows="6"
						  placeholder="Sales Department = sales@pearland411.com&#10;Billing Department = billing@pearland411.com"><?= e($s['enquiry_department_emails'] ?? '') ?></textarea>
			</div>
		</section>

		<!-- Advertising --------------------------------------------------- -->
		<section class="setting-card">
			<h2>Advertising</h2>
			<p class="hint">
				A zone with nothing sold in it falls back to a house &ldquo;advertise here&rdquo;
				banner. When every zone did that, a page carried six house ads and no advertisers
				&mdash; which reads as <em>nobody advertises here</em>, not as premium inventory.
				This caps how many a single page may fill; the rest render nothing at all, and the
				page looks quieter and more sold-out. <strong>A paid ad is never affected</strong>
				&mdash; the cap governs house creatives only. Pages whose search or category comes
				back empty use one regardless.
			</p>
			<div class="setting-row">
				<label for="ads_house_max_per_page">House ads per page<small>Default 2. Use 0 to never show one, or &minus;1 for no cap.</small></label>
				<input type="number" id="ads_house_max_per_page" name="ads_house_max_per_page"
					   min="-1" max="12" step="1"
					   value="<?= e_attr(($s['ads_house_max_per_page'] ?? '') !== '' ? $s['ads_house_max_per_page'] : '2') ?>">
			</div>
		</section>

		<!-- Deals &amp; coupons ------------------------------------------- -->
		<section class="setting-card">
			<h2>Deals &amp; coupons</h2>
			<p class="hint">
				Members post their own offers at <a href="<?= base_url('user/my-coupons') ?>">My deals</a>,
				and they appear on <a href="<?= base_url('coupons') ?>">/coupons</a>, on the business&rsquo;s
				listing and on the homepage. It is free with any published listing, so this is the only
				ceiling on it &mdash; a spam limit, not a package entitlement. Expired deals drop off by
				themselves and do not count.
			</p>
			<div class="setting-row">
				<label for="coupon_max_per_member">Live deals per member<small>Default 10.</small></label>
				<input type="number" id="coupon_max_per_member" name="coupon_max_per_member"
					   min="1" max="100" step="1"
					   value="<?= e_attr(($s['coupon_max_per_member'] ?? '') !== '' ? $s['coupon_max_per_member'] : '10') ?>">
			</div>
		</section>

		<!-- Billing ------------------------------------------------------- -->
		<section class="setting-card">
			<h2>Billing</h2>
			<p class="hint">
				The amount staff type into <a href="<?= base_url('admin/billing') ?>">Customer Billing</a>
				is the only figure in the whole system a person enters freehand &mdash; every other
				charge is priced server-side from a plan. This is the ceiling on it, so a stray
				keystroke on a $39 invoice cannot become a $3,900 charge on somebody's card without
				a deliberate change here.
			</p>
			<div class="setting-row">
				<label for="billing_manual_charge_max">Largest manual charge<small>Default 25000. Use 0 for no limit.</small></label>
				<input type="number" id="billing_manual_charge_max" name="billing_manual_charge_max"
					   min="0" step="1"
					   value="<?= e_attr(($s['billing_manual_charge_max'] ?? '') !== '' ? $s['billing_manual_charge_max'] : '25000') ?>">
			</div>
			<div class="setting-row">
				<label for="billing_notify_emails">Copy billing alerts to<small>One address per line. Every active admin is always included &mdash; these are extra.</small></label>
				<textarea id="billing_notify_emails" name="billing_notify_emails" rows="3"
						  placeholder="joshua@spacecentersystems.com"><?= e($s['billing_notify_emails'] ?? '') ?></textarea>
			</div>
			<p class="hint">
				Sent when a card goes on file, a payment is taken, a recurring schedule changes,
				and when an automatic renewal succeeds or fails.
			</p>
		</section>

		<!-- Top menu ------------------------------------------------------ -->
		<section class="setting-card">
			<h2>Top menu categories</h2>
			<p class="hint">
				The two category links in the main menu, after Classifieds. Leave a slot on
				<em>Auto</em> and it picks a matching category by name; choose <em>Hide</em> to drop
				the link entirely. Auto is what put &ldquo;Sports&rdquo; on the Travel and Recreation
				page &mdash; there was no sports category with published listings, so it fell through
				to a keyword that matched travel.
			</p>

			<?php
			$nav_categories = (isset($nav_categories) && is_array($nav_categories)) ? $nav_categories : array();
			$nav_slots = array(
				'nav_category_1' => 'First menu category',
				'nav_category_2' => 'Second menu category',
			);
			foreach ($nav_slots as $nav_key => $nav_label):
				$nav_current = (string) ($s[$nav_key] ?? '');
			?>
				<div class="setting-row">
					<label for="<?= e_attr($nav_key) ?>"><?= e($nav_label) ?><small>Shown in the top menu and linked to that category's directory page.</small></label>
					<select id="<?= e_attr($nav_key) ?>" name="<?= e_attr($nav_key) ?>">
						<option value=""<?= $nav_current === '' ? ' selected' : '' ?>>Auto &mdash; pick a matching category by name</option>
						<option value="-"<?= $nav_current === '-' ? ' selected' : '' ?>>Hide &mdash; no link in this slot</option>
						<?php foreach ($nav_categories as $nav_cat):
							$nav_slug = !empty($nav_cat->category_slug) ? (string) $nav_cat->category_slug : pearland_slug($nav_cat->category_name); ?>
							<option value="<?= e_attr($nav_slug) ?>"<?= $nav_current === $nav_slug ? ' selected' : '' ?>><?= e($nav_cat->category_name) ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endforeach; ?>
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

		<!-- Services --------------------------------------------------- -->
		<section class="setting-card">
			<h2>Services</h2>
			<p class="hint">Turn optional content sections on or off. When a service is off it disappears from the public site and this admin's sidebar, and it can't be added to a package. The core directory, coupons and advertising are always on.</p>

			<?php
			$service_toggles = array(
				'service_events_enabled'      => 'Events',
				'service_realestate_enabled'  => 'Real Estate (Houses + Apartments/Rentals)',
				'service_classifieds_enabled' => 'Classifieds',
				'service_news_enabled'        => 'News',
				'service_blog_enabled'        => 'Blogs',
				'service_products_enabled'    => 'Products',
				'service_videos_enabled'      => 'Videos',
			);
			foreach ($service_toggles as $skey => $slabel):
				$son = (($s[$skey] ?? '') !== '0'); ?>
				<div class="setting-row">
					<label for="<?= $skey ?>"><?= $slabel ?></label>
					<span>
						<input type="hidden" name="<?= $skey ?>" value="0">
						<input type="checkbox" id="<?= $skey ?>" name="<?= $skey ?>" value="1" <?= $son ? 'checked' : '' ?>>
					</span>
				</div>
			<?php endforeach; ?>
		</section>

		<!-- Listings tools ---------------------------------------------- -->
		<section class="setting-card">
			<h2>Listings</h2>
			<p class="hint">Optional tools on the listings admin. These are off unless you switch them on.</p>

			<?php // Unlike the service toggles above, this one defaults to OFF, so
			      // an unset value must read as unticked rather than ticked.
			$dup_on = (($s['feature_duplicate_listing_enabled'] ?? '0') === '1'); ?>
			<div class="setting-row">
				<label for="feature_duplicate_listing_enabled">
					Create duplicate listings
					<span class="hint" style="display:block;font-weight:400;">Copies an existing listing into a new one. Off by default — it sat next to "Create listing from scratch" and one mis-click made a duplicate business record.</span>
				</label>
				<span>
					<input type="hidden" name="feature_duplicate_listing_enabled" value="0">
					<input type="checkbox" id="feature_duplicate_listing_enabled" name="feature_duplicate_listing_enabled" value="1" <?= $dup_on ? 'checked' : '' ?>>
				</span>
			</div>
		</section>

		<div class="setting-actions">
			<button type="submit" class="btn-save">Save settings</button>
		</div>
	</form>
</div>
