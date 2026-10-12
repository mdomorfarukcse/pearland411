<?php
/**
 * The site footer.
 *
 *   <?php include APPPATH.'views/partials/site_footer.php'; ?>
 *
 * Rendered by the homepage (views/index.php) and by the shared views/footer.php
 * so the two cannot drift — before this partial existed they were two hand-kept
 * copies with different links, different phone numbers and different category
 * lists, and the homepage's copy still hard-coded eight category slugs.
 *
 * Styles: assets/css/site-footer.css. Every class here is a `.p411-foot`
 * descendant and nothing else in the codebase emits them, so any view that
 * includes this partial only has to link that one stylesheet.
 *
 * Optional variable (set it before the include):
 *   $footer_categories  array of category rows (category_name + category_slug)
 *                       for the Categories column. Falls back to a live query
 *                       when the including view has none to hand.
 *
 * Contact details and social URLs come from /admin/setting via site_setting(),
 * so they are changed in one place rather than in two views.
 */

$__ci = get_instance();

// --- Categories column ----------------------------------------------------
// Prefer what the page already loaded; only query when it has nothing, so the
// homepage doesn't pay for a second lookup.
if (!isset($footer_categories) || !is_array($footer_categories) || !$footer_categories) {
	$footer_categories = (isset($__ci->usermodel) && method_exists($__ci->usermodel, 'getCategoriesWithCounts'))
		? $__ci->usermodel->getCategoriesWithCounts(7)
		: array();
}

// Same slug rule as every other link on the site: the stored slug verbatim when
// there is one, otherwise pearland_slug() of the name. Getting this wrong is
// what used to send footer links to an empty category page.
$__foot_cat_url = function ($category) {
	$slug = !empty($category->category_slug)
		? (string) $category->category_slug
		: pearland_slug($category->category_name);
	return base_url('all_listing/' . rawurlencode($slug));
};

// The header and the footer show the same light logo. The default is the
// horizontal lockup the site has always used; it lives on a third-party image
// host, which is why /admin/setting now carries a `site_logo_url` field —
// upload the logo to assets/images/ and paste the address there to drop the
// external dependency.
$__foot_logo = site_setting('site_logo_url', '') ?: 'https://i.ibb.co/93BYZ4DZ/pp.png';

$__foot_phone = site_setting('contact_phone', '281-482-2031');
$__foot_email = site_setting('contact_email', 'sales@pearland411.com');
$__foot_tel   = preg_replace('/[^0-9+]/', '', $__foot_phone);

// A service switched off in /admin/setting 404s, so the footer must not link
// to it either.
$__foot_events = site_setting('service_events_enabled', '1') !== '0';
$__foot_news   = site_setting('service_news_enabled',   '1') !== '0';
$__foot_videos = site_setting('service_videos_enabled', '1') !== '0';
$__foot_blog   = site_setting('service_blog_enabled',   '1') !== '0';

$__foot_socials = array(
	'facebook'  => array('Facebook',  external_url(site_setting('social_facebook_url', '')), '<path d="M14 8.5h2.5V5.4A32 32 0 0 0 13.9 5C11.4 5 9.7 6.5 9.7 9.3v2.2H7v3.4h2.7V23h3.4v-8.1h2.7l.4-3.4h-3.1V9.6c0-1 .3-1.1 1.9-1.1z"/>'),
	'instagram' => array('Instagram', external_url(site_setting('social_instagram_url', '')), '<path d="M12 5.6c2.1 0 2.3 0 3.1.05 2.1.1 3.1 1.1 3.2 3.2 0 .8.05 1 .05 3.1s0 2.3-.05 3.1c-.1 2.1-1.1 3.1-3.2 3.2-.8.05-1 .05-3.1.05s-2.3 0-3.1-.05c-2.1-.1-3.1-1.1-3.2-3.2C5.65 14.3 5.65 14.1 5.65 12s0-2.3.05-3.1c.1-2.1 1.1-3.1 3.2-3.2.8-.05 1-.05 3.1-.05zM12 4c-2.2 0-2.4 0-3.3.05C5.9 4.2 4.2 5.9 4.05 8.7 4 9.6 4 9.8 4 12s0 2.4.05 3.3C4.2 18.1 5.9 19.8 8.7 20c.9.05 1.1.05 3.3.05s2.4 0 3.3-.05c2.8-.15 4.5-1.85 4.65-4.65.05-.9.05-1.1.05-3.3s0-2.4-.05-3.3C19.8 5.9 18.1 4.2 15.3 4.05 14.4 4 14.2 4 12 4zm0 3.9a4.1 4.1 0 1 0 0 8.2 4.1 4.1 0 0 0 0-8.2zm0 6.75a2.65 2.65 0 1 1 0-5.3 2.65 2.65 0 0 1 0 5.3zM17.2 6.6a1 1 0 1 0 0 1.9 1 1 0 0 0 0-1.9z"/>'),
	'twitter'   => array('X',         external_url(site_setting('social_twitter_url', '')), '<path d="M17.5 5h2.8l-6.1 7 7.2 9.5h-5.6l-4.4-5.8-5 5.8H3.6l6.5-7.5L3.2 5h5.8l4 5.3zm-1 14.8h1.6L8.5 6.6H6.8z"/>'),
	'linkedin'  => array('LinkedIn',  external_url(site_setting('social_linkedin_url', '')), '<path d="M8 9.5H4.8V21H8zM6.4 4a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM21 14.4c0-3.3-1.8-4.9-4.1-4.9-1.9 0-2.8 1.05-3.3 1.8V9.5h-3.2c.05.9 0 11.5 0 11.5h3.2v-6.4c0-.3 0-.6.1-.8.3-.6.8-1.2 1.8-1.2 1.3 0 1.8 1 1.8 2.4V21H21z"/>'),
	'youtube'   => array('YouTube',   external_url(site_setting('social_youtube_url', '')), '<path d="M21.5 8.2a2.5 2.5 0 0 0-1.75-1.75C18.2 6 12 6 12 6s-6.2 0-7.75.45A2.5 2.5 0 0 0 2.5 8.2 26 26 0 0 0 2 12.5a26 26 0 0 0 .5 4.3 2.5 2.5 0 0 0 1.75 1.75C5.8 19 12 19 12 19s6.2 0 7.75-.45a2.5 2.5 0 0 0 1.75-1.75 26 26 0 0 0 .5-4.3 26 26 0 0 0-.5-4.3zM10 15.4V9.6l5.2 2.9z"/>'),
);
$__foot_social_any = false;
foreach ($__foot_socials as $__row) { if ($__row[1] !== '') { $__foot_social_any = true; break; } }
?>

<footer class="p411-foot">
	<div class="container px-10">

		<div class="p411-foot__grid">

			<div class="p411-foot__brand">
				<a href="<?= base_url() ?>">
					<img src="<?= e_attr($__foot_logo) ?>" alt="Pearland411" loading="lazy" decoding="async">
				</a>
				<p>Your complete guide to local businesses, events, deals, news and community in Pearland, Texas.</p>
				<?php if ($__foot_social_any): ?>
					<div class="p411-foot__social">
						<?php foreach ($__foot_socials as $__row): list($__label, $__url, $__path) = $__row; if ($__url === '') continue; ?>
							<a href="<?= e_attr($__url) ?>" target="_blank" rel="noopener" aria-label="Pearland411 on <?= e_attr($__label) ?>">
								<svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><?= $__path ?></svg>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div>
				<h4>Categories</h4>
				<ul>
					<?php foreach ($footer_categories as $__cat): ?>
						<li><a href="<?= e_url($__foot_cat_url($__cat)) ?>"><?= e($__cat->category_name) ?></a></li>
					<?php endforeach; ?>
					<li><a class="p411-foot__more" href="<?= base_url('all-category') ?>">View all categories &rarr;</a></li>
				</ul>
			</div>

			<div>
				<h4>Community</h4>
				<ul>
					<?php if ($__foot_events): ?><li><a href="<?= base_url('event') ?>">Events calendar</a></li><?php endif; ?>
					<?php if ($__foot_news):   ?><li><a href="<?= base_url('news') ?>">Local news</a></li><?php endif; ?>
					<?php if ($__foot_blog):   ?><li><a href="<?= base_url('all-blog') ?>">Blog</a></li><?php endif; ?>
					<li><a href="<?= base_url('coupons') ?>">Deals &amp; coupons</a></li>
					<?php if ($__foot_videos): ?><li><a href="<?= base_url('videos') ?>">Videos</a></li><?php endif; ?>
					<li><a href="<?= base_url('all_listing') ?>">Business directory</a></li>
					<li><a href="<?= base_url('about-us') ?>">About Pearland411</a></li>
				</ul>
			</div>

			<div>
				<h4>For businesses</h4>
				<ul>
					<li><a href="<?= base_url('register') ?>">List your business free</a></li>
					<li><a href="<?= base_url('pricing-plan') ?>">Featured placements</a></li>
					<li><a href="<?= base_url('pricing-plan#pp-site-wide-banners') ?>">Advertising packages</a></li>
					<li><a href="<?= base_url('services') ?>">Website services</a></li>
					<li><a href="<?= base_url('request-quote') ?>">Banner &amp; ad design</a></li>
				</ul>
			</div>

			<div>
				<h4>Support</h4>
				<ul>
					<li><a href="<?= base_url('contact-us') ?>">Contact us</a></li>
					<li><a href="<?= base_url('faq') ?>">FAQ</a></li>
					<li><a href="<?= base_url('privacy-policy') ?>">Privacy policy</a></li>
					<li><a href="<?= base_url('terms-of-use') ?>">Terms of use</a></li>
				</ul>
				<h4 style="margin-top:22px;">Contact</h4>
				<ul class="p411-foot__contact">
					<li>
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>
						<a href="tel:<?= e_attr($__foot_tel) ?>"><?= e($__foot_phone) ?></a>
					</li>
					<li>
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2.5" y="4.5" width="19" height="15" rx="2"/><path d="m3 6 9 6.5L21 6"/></svg>
						<a href="mailto:<?= e_attr($__foot_email) ?>"><?= e($__foot_email) ?></a>
					</li>
					<li>
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10.5c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10.5" r="2.8"/></svg>
						<span>Pearland, Texas &mdash; and the surrounding area</span>
					</li>
				</ul>
			</div>

		</div>

		<div class="p411-foot__bar">
			<p>&copy; <?= date('Y') ?> Pearland411. All rights reserved.</p>
			<p>Made with <span class="p411-foot__heart" aria-hidden="true">&#9829;</span><span class="sr-only"> love</span> in Pearland, Texas</p>
			<p>Powered by <a href="https://www.spacecentersystems.com/" target="_blank" rel="noopener">Space Center Systems</a></p>
		</div>

	</div>
</footer>

<?php unset(
	$__ci, $__foot_cat_url, $__foot_logo, $__foot_phone, $__foot_email, $__foot_tel,
	$__foot_events, $__foot_news, $__foot_videos,
	$__foot_socials, $__foot_social_any, $__row, $__label, $__url, $__path, $__cat,
	$footer_categories
); ?>
