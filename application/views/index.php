<!doctype html>
<?php
/**
 * ============================================================================
 * Pearland411 homepage
 * ============================================================================
 * Data contract — every variable below is set by User::index().
 * Keep the two in sync: the previous version of this file looped
 * $featured_listings, which the controller never set, so the hero carousel
 * silently rendered its "coming soon" fallback forever.
 *
 *   featured_listings       getFeaturedListings(6)
 *   latest_listings         getLatestListings(9)
 *   latest_videos           getLatestVideos(3)        [gated by show_videos]
 *   latest_news             getLatestNews(3)          [gated by show_news]
 *   home_events             getUpcomingEvents(3)      [gated by show_events]
 *   home_deals              getActiveCoupons(4)
 *   home_categories         getCategoriesWithCounts(12)
 *   all_listing_categories  getHeaderNavigationCategories(50)
 *   ad_rate_card            getAdSlotRateCard(4)
 *   listing_plans           getActivePlans()
 *   show_news / show_videos / show_events   site_setting('service_*_enabled')
 *
 * Presentation lives in assets/css/homepage.css. Every class is `hp-`
 * prefixed, so nothing here can collide with style.css or Bootstrap.
 * This page loads no JS framework — the interactions below are vanilla.
 * ============================================================================
 */

// Small SVG icon set, keyed by a keyword found in the category name.
// Icons, not emoji — they inherit currentColor and stay crisp at any size.
$hp_icons = array(
	'home'       => '<path d="M3 21h18M5 21V9l7-5 7 5v12M10 21v-6h4v6"/>',
	'health'     => '<path d="M3 12h4l2-6 4 12 2-6h6"/>',
	'fitness'    => '<path d="M3 12h4l2-6 4 12 2-6h6"/>',
	'medical'    => '<path d="M12 3v18M3 12h18"/><rect x="3" y="3" width="18" height="18" rx="4"/>',
	'dental'     => '<path d="M12 3v18M3 12h18"/><rect x="3" y="3" width="18" height="18" rx="4"/>',
	'auto'       => '<path d="M5 17a2 2 0 1 0 4 0 2 2 0 1 0-4 0M15 17a2 2 0 1 0 4 0 2 2 0 1 0-4 0"/><path d="M3 17V10h12l4 4v3"/>',
	'real'       => '<path d="M3 21h8V3H3zM11 21h10v-9H11z"/><path d="M6 7h2M6 11h2M6 15h2M15 16h2"/>',
	'food'       => '<path d="M7 3v8a3 3 0 0 0 6 0V3M10 11v10"/><path d="M17 3c-1.5 2-2 4-2 6s.5 3 2 3v9"/>',
	'restaurant' => '<path d="M7 3v8a3 3 0 0 0 6 0V3M10 11v10"/><path d="M17 3c-1.5 2-2 4-2 6s.5 3 2 3v9"/>',
	'beauty'     => '<path d="M8 3v6a4 4 0 0 0 8 0V3M12 13v8"/>',
	'business'    => '<path d="M5 18h14M6 18V8h12v10"/><path d="M9 8V5h6v3"/>',
	'professional' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 13h18"/>',
	'financial'  => '<path d="M12 2v20M17 6H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
	'insurance'  => '<path d="M12 3l8 3v6c0 5-3.5 8.5-8 9-4.5-.5-8-4-8-9V6z"/><path d="m9 12 2 2 4-4"/>',
	'legal'      => '<path d="M12 3v18M7 21h10M5 8h14M8 8l-3 6h6zM16 8l-3 6h6z"/>',
	'attorney'   => '<path d="M12 3v18M7 21h10M5 8h14M8 8l-3 6h6zM16 8l-3 6h6z"/>',
	'education'  => '<path d="m3 9 9-5 9 5-9 5z"/><path d="M7 11v5c0 1.5 2.2 3 5 3s5-1.5 5-3v-5"/>',
	'pet'        => '<circle cx="8" cy="7" r="2"/><circle cx="16" cy="7" r="2"/><circle cx="5" cy="13" r="2"/><circle cx="19" cy="13" r="2"/><path d="M12 12c-3 0-5 2.5-5 5a3 3 0 0 0 3 3h4a3 3 0 0 0 3-3c0-2.5-2-5-5-5z"/>',
	'pest'       => '<path d="M12 4v16M8 8l-4-2M16 8l4-2M8 14l-4 2M16 14l4 2"/><ellipse cx="12" cy="12" rx="4" ry="7"/>',
	'art'        => '<path d="M4 6h16v12H4z"/><path d="m9 10 6 2-6 2z"/>',
	'entertain'  => '<path d="M4 6h16v12H4z"/><path d="m9 10 6 2-6 2z"/>',
	'sport'      => '<circle cx="12" cy="12" r="9"/><path d="M12 3v18M3 12h18M6 6l12 12M18 6 6 18"/>',
	'tech'       => '<rect x="2" y="4" width="20" height="13" rx="2"/><path d="M8 21h8M12 17v4"/>',
	'clothing'   => '<path d="m8 3 4 3 4-3 5 4-3 3v11H6V10L3 7z"/>',
	'construct'  => '<path d="M3 21h18M5 21V11h14v10"/><path d="m4 11 8-7 8 7"/>',
	'glass'      => '<rect x="4" y="4" width="16" height="16" rx="1"/><path d="M4 12h16M12 4v16"/>',
	'travel'     => '<path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/><circle cx="12" cy="12" r="10"/>',
	'church'     => '<path d="M12 2v6M9 5h6M5 21V11l7-4 7 4v10"/><path d="M10 21v-5h4v5"/>',
	'_default'   => '<circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 2"/>',
);

/** Pick an icon for a category name. */
$hp_icon_for = function ($name) use ($hp_icons) {
	$n = strtolower((string) $name);
	foreach ($hp_icons as $key => $path) {
		if ($key !== '_default' && strpos($n, $key) !== false) return $path;
	}
	return $hp_icons['_default'];
};

/** Category URL, tolerating a missing category_slug column. */
$hp_cat_url = function ($cat) {
	$raw = (!empty($cat->category_slug)) ? $cat->category_slug : (isset($cat->category_name) ? $cat->category_name : '');
	return base_url('all_listing/' . pearland_slug($raw));
};

$hp_placeholder = base_url('assets/images/placeholder-listing.svg');
$hp_phone       = site_setting('contact_phone', '281-482-2031');
$hp_email       = site_setting('contact_email', 'sales@pearland411.com');
$hp_address     = site_setting('contact_address', 'Pearland, Texas, USA');
$hp_logged_in   = !empty($_SESSION['logged_user']);
?>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#5E8F26">

	<?php include APPPATH . 'views/partials/seo_head.php'; ?>

	<link rel="shortcut icon" href="<?= base_url('assets/images/fav.ico') ?>" type="image/x-icon">

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">

	<!-- The homepage is styled entirely by homepage.css. It deliberately does
	     NOT load style.css, Bootstrap, jQuery, Swiper or FullCalendar — none
	     of them are used here any more. -->
	<link rel="stylesheet" href="<?= base_url('assets/css/homepage.css') ?>?v=2">
</head>

<body class="hp-page">

	<a class="hp-skip" href="#hp-content">Skip to main content</a>

	<!-- ==================================================== utility bar == -->
	<div class="hp-util">
		<div class="hp-wrap">
			<a href="tel:<?= e_attr(preg_replace('/[^0-9+]/', '', $hp_phone)) ?>">
				<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>
				<?= e($hp_phone) ?>
			</a>
			<a href="mailto:<?= e_attr($hp_email) ?>">
				<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/></svg>
				<?= e($hp_email) ?>
			</a>

			<span class="hp-util__spacer"></span>

			<a class="hp-util__cta" href="<?= base_url('post-your-ads') ?>">Advertise with us</a>

			<span class="hp-util__social">
				<?php
				$hp_socials = array(
					'social_facebook_url'  => array('Facebook',  '<path d="M14 9h3V6h-3a4 4 0 0 0-4 4v2H8v3h2v7h3v-7h2.5l.5-3H13v-2a1 1 0 0 1 1-1z"/>'),
					'social_instagram_url' => array('Instagram', '<rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3.6" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="17" cy="7" r="1.2"/>'),
					'social_twitter_url'   => array('X',         '<path d="M3 3h4.5l4.2 5.8L16.8 3H21l-6.7 8.2L21.4 21h-4.5l-4.5-6.2L7 21H3l7-8.6z"/>'),
					'social_youtube_url'   => array('YouTube',   '<path d="M22 12s0-3.3-.4-4.9a2.5 2.5 0 0 0-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.3a2.5 2.5 0 0 0-1.8 1.8C2 8.7 2 12 2 12s0 3.3.4 4.9a2.5 2.5 0 0 0 1.8 1.8C5.8 19 12 19 12 19s6.2 0 7.8-.3a2.5 2.5 0 0 0 1.8-1.8C22 15.3 22 12 22 12zM10 15.5v-7l6 3.5z"/>'),
					'social_linkedin_url'  => array('LinkedIn',  '<path d="M4.5 3.5a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM3 9h3v12H3zM9 9h2.9v1.7h.04A3.2 3.2 0 0 1 15 9c3 0 3.6 2 3.6 4.6V21h-3v-6.2c0-1.5 0-3.4-2-3.4s-2.4 1.6-2.4 3.3V21H9z"/>'),
				);
				foreach ($hp_socials as $key => $meta):
					$url = site_setting($key, '');
					if (!$url) continue;
				?>
					<a href="<?= e_url($url) ?>" target="_blank" rel="noopener" aria-label="<?= e_attr($meta[0]) ?>">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><?= $meta[1] ?></svg>
					</a>
				<?php endforeach; ?>
			</span>
		</div>
	</div>

	<!-- ======================================================== header == -->
	<header class="hp-head">
		<div class="hp-wrap">
			<a class="hp-logo" href="<?= base_url() ?>">
				<img src="<?= base_url('assets/images/pearland-logo.png') ?>" alt="Pearland411 — Pearland, Texas business directory" width="180" height="42">
			</a>

			<button class="hp-explore" type="button" id="hp-explore-btn" aria-expanded="false" aria-controls="hp-mega">
				<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
				Explore
				<svg class="hp-explore__chev" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
			</button>

			<form class="hp-search" method="get" action="<?= base_url('search') ?>" role="search">
				<label class="hp-sr" for="hp-q">Search businesses</label>
				<input type="text" id="hp-q" name="q" autocomplete="off"
				       placeholder="Search businesses, services, categories&hellip;">
				<button type="submit" aria-label="Search">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
				</button>
				<ul class="hp-suggest" id="hp-suggest" role="listbox" aria-label="Search suggestions"></ul>
			</form>

			<div class="hp-head__act">
				<?php if ($hp_logged_in): ?>
					<a class="hp-head__link" href="<?= base_url('user/dashboard') ?>">Dashboard</a>
					<a class="hp-btn hp-btn--green" href="<?= base_url('user/add_new_listing_start') ?>">+ Add listing</a>
				<?php else: ?>
					<a class="hp-head__link" href="<?= base_url('login') ?>">Sign in</a>
					<a class="hp-btn hp-btn--green" href="<?= base_url('register') ?>">Create an account</a>
				<?php endif; ?>
				<button class="hp-burger" type="button" id="hp-burger" aria-expanded="false" aria-controls="hp-mega" aria-label="Open menu">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
				</button>
			</div>
		</div>
	</header>

	<!-- ===================================================== mega menu == -->
	<nav class="hp-mega" id="hp-mega" aria-label="Browse Pearland411">
		<div class="hp-wrap">
			<div class="hp-mega__grid">
				<div>
					<?php // The header's Sign in / Dashboard link is hidden below 900px,
					// so the menu has to carry it or phone users lose account access. ?>
					<p class="hp-mega__title hp-mega__auth">Your account</p>
					<ul class="hp-mega__list hp-mega__auth" style="grid-template-columns:1fr;">
						<?php if ($hp_logged_in): ?>
							<li><a href="<?= base_url('user/dashboard') ?>">My dashboard</a></li>
							<li><a href="<?= base_url('user/add_new_listing_start') ?>">Add a listing</a></li>
							<li><a href="<?= base_url('logout') ?>">Sign out</a></li>
						<?php else: ?>
							<li><a href="<?= base_url('login') ?>">Sign in</a></li>
							<li><a href="<?= base_url('register') ?>">Create an account</a></li>
						<?php endif; ?>
					</ul>

					<p class="hp-mega__title">Sections</p>
					<ul class="hp-mega__list" style="grid-template-columns:1fr;">
						<li><a href="<?= base_url('all_listing') ?>">All businesses</a></li>
						<li><a href="<?= base_url('all-category') ?>">All categories</a></li>
						<li><a href="<?= base_url('coupons') ?>">Deals &amp; coupons</a></li>
						<?php if (!empty($show_events)): ?><li><a href="<?= base_url('event') ?>">Events</a></li><?php endif; ?>
						<?php if (!empty($show_news)): ?><li><a href="<?= base_url('all-news') ?>">News</a></li><?php endif; ?>
						<?php if (!empty($show_videos)): ?><li><a href="<?= base_url('videos') ?>">Videos</a></li><?php endif; ?>
						<li><a href="<?= base_url('services') ?>">Web services</a></li>
						<li><a href="<?= base_url('post-your-ads') ?>">Advertise</a></li>
						<li><a href="<?= base_url('contact-us') ?>">Contact us</a></li>
					</ul>
				</div>
				<div>
					<p class="hp-mega__title">Business categories</p>
					<?php if (!empty($all_listing_categories)): ?>
						<ul class="hp-mega__list">
							<?php foreach (array_slice($all_listing_categories, 0, 24) as $cat): ?>
								<li><a href="<?= e_url($hp_cat_url($cat)) ?>"><?= e($cat->category_name) ?></a></li>
							<?php endforeach; ?>
						</ul>
						<p style="margin-top:14px;"><a class="hp-sec__more" href="<?= base_url('all-category') ?>">Browse all categories &rarr;</a></p>
					<?php else: ?>
						<p style="font-size:.88rem;color:var(--hp-ink-2);">
							No categories have been added yet.
							<a href="<?= base_url('all_listing') ?>">Browse the directory</a>.
						</p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</nav>

	<!-- ========================================================== hero == -->
	<section class="hp-hero">
		<div class="hp-wrap">
			<h1>Discover Pearland&rsquo;s Best Businesses</h1>
			<p class="hp-hero__sub">Your complete guide to local services, restaurants, shops and professionals in Pearland, Texas.</p>

			<form class="hp-hero__form" method="get" action="<?= base_url('search') ?>" role="search">
				<label class="hp-sr" for="hp-hero-q">What are you looking for?</label>
				<input type="text" id="hp-hero-q" name="q" placeholder="What are you looking for?" required>

				<?php if (!empty($home_categories)): ?>
					<label class="hp-sr" for="hp-hero-cat">Jump to a category</label>
					<?php // No `name` on purpose: User::search() only reads `q`, so posting a
					// category here would be a parameter the server silently ignores. The
					// script below navigates to the category page instead. ?>
					<select id="hp-hero-cat">
						<option value="">All categories</option>
						<?php foreach ($home_categories as $cat): ?>
							<option value="<?= e_attr($hp_cat_url($cat)) ?>"><?= e($cat->category_name) ?></option>
						<?php endforeach; ?>
					</select>
				<?php endif; ?>

				<button type="submit">Search</button>
			</form>

			<?php if (!empty($home_categories)): ?>
				<div class="hp-hero__chips">
					<span>Popular:</span>
					<?php foreach (array_slice($home_categories, 0, 6) as $cat): ?>
						<a href="<?= e_url($hp_cat_url($cat)) ?>"><?= e($cat->category_name) ?></a>
					<?php endforeach; ?>
					<a href="<?= base_url('all-category') ?>">Browse all &rarr;</a>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<!-- ========================================= top leaderboard ad slot == -->
	<div class="hp-wrap" style="padding-top:20px;">
		<?php $slot = 'home-top-leaderboard';
		include APPPATH . 'views/partials/ad_slot.php'; ?>
	</div>

	<!-- ================================================ category strip == -->
	<?php if (!empty($home_categories)): ?>
		<section class="hp-cats" style="margin-top:20px;" aria-label="Browse by category">
			<div class="hp-wrap">
				<div class="hp-cats__grid">
					<?php foreach (array_slice($home_categories, 0, 8) as $cat): ?>
						<a href="<?= e_url($hp_cat_url($cat)) ?>">
							<span class="hp-cats__ic">
								<svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $hp_icon_for($cat->category_name) ?></svg>
							</span>
							<span class="hp-cats__nm"><?= e($cat->category_name) ?></span>
							<?php if (isset($cat->listing_count)): ?>
								<span class="hp-cats__ct"><?= (int) $cat->listing_count ?> listing<?= ((int) $cat->listing_count === 1 ? '' : 's') ?></span>
							<?php endif; ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- ================================================== main content == -->
	<main class="hp-main" id="hp-content">
		<div class="hp-wrap">
			<div class="hp-cols">

				<!-- ============================================ main column == -->
				<div class="hp-col-main">

					<!-- Featured businesses ------------------------------------ -->
					<section aria-labelledby="hp-featured-h">
						<div class="hp-sec__head">
							<h2 id="hp-featured-h">Featured Businesses</h2>
							<a class="hp-sec__more" href="<?= base_url('all_listing') ?>">See all businesses &rarr;</a>
						</div>

						<?php if (!empty($featured_listings)): ?>
							<div class="hp-grid">
								<?php foreach (array_slice($featured_listings, 0, 3) as $item): ?>
									<article class="hp-card">
										<a class="hp-card__media" href="<?= base_url('listing-details/' . rawurlencode($item->listing_slug)) ?>">
											<?php if (!empty($item->listing_image)): ?>
												<?= responsive_img($item->listing_image, $item->listing_name) ?>
											<?php else: ?>
												<img src="<?= e_attr($hp_placeholder) ?>" alt="<?= e_attr($item->listing_name) ?>" loading="lazy" decoding="async" width="640" height="480">
											<?php endif; ?>
											<span class="hp-card__badges">
												<span class="hp-tag hp-tag--feat"><?= !empty($item->spotlight_listing) ? 'Spotlight' : 'Featured' ?></span>
											</span>
										</a>
										<div class="hp-card__body">
											<?php if (!empty($item->category_name)): ?>
												<span class="hp-card__cat"><?= e($item->category_name) ?></span>
											<?php endif; ?>
											<h3 class="hp-card__title">
												<a href="<?= base_url('listing-details/' . rawurlencode($item->listing_slug)) ?>"><?= e($item->listing_name) ?></a>
											</h3>
											<?php if (!empty($item->listing_description)): ?>
												<p class="hp-card__desc"><?= e(mb_strimwidth(trim(strip_tags(html_entity_decode($item->listing_description))), 0, 110, '…')) ?></p>
											<?php endif; ?>
											<div class="hp-card__foot">
												<a class="hp-btn hp-btn--green hp-btn--sm" href="<?= base_url('listing-details/' . rawurlencode($item->listing_slug)) ?>">View details</a>
											</div>
										</div>
									</article>
								<?php endforeach; ?>
							</div>
						<?php else: ?>
							<div class="hp-empty">
								<span class="hp-empty__ic">
									<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 2.6 5.6 6.4.8-4.6 4.3 1.1 6.3L12 17l-5.5 3 1.1-6.3L3 9.4l6.4-.8z"/></svg>
								</span>
								<h3>No featured businesses yet</h3>
								<p>Featured placement puts a business at the top of the homepage. No listing is currently marked as featured or spotlight.</p>
								<a class="hp-btn hp-btn--deep" href="<?= base_url('pricing-plan') ?>">See featured packages</a>
							</div>
						<?php endif; ?>
					</section>

					<!-- In-feed ad -------------------------------------------- -->
					<?php $slot = 'home-hero';
					include APPPATH . 'views/partials/ad_slot.php'; ?>

					<!-- Newest businesses ------------------------------------- -->
					<section aria-labelledby="hp-newest-h">
						<div class="hp-sec__head">
							<h2 id="hp-newest-h">Explore Pearland Businesses</h2>
							<a class="hp-sec__more" href="<?= base_url('all_listing') ?>">View all listings &rarr;</a>
						</div>

						<?php if (!empty($latest_listings)): ?>
							<div class="hp-grid">
								<?php foreach ($latest_listings as $row): ?>
									<?php $is_new = !empty($row->created_at) && strtotime($row->created_at) > strtotime('-7 days'); ?>
									<article class="hp-card">
										<a class="hp-card__media" href="<?= base_url('listing-details/' . rawurlencode($row->listing_slug)) ?>">
											<?php if (!empty($row->listing_image)): ?>
												<?= responsive_img($row->listing_image, $row->listing_name) ?>
											<?php else: ?>
												<img src="<?= e_attr($hp_placeholder) ?>" alt="<?= e_attr($row->listing_name) ?>" loading="lazy" decoding="async" width="640" height="480">
											<?php endif; ?>
											<?php if ($is_new): ?>
												<span class="hp-card__badges"><span class="hp-tag hp-tag--new">New</span></span>
											<?php endif; ?>
										</a>
										<div class="hp-card__body">
											<?php if (!empty($row->category_name)): ?>
												<span class="hp-card__cat"><?= e($row->category_name) ?></span>
											<?php endif; ?>
											<h3 class="hp-card__title">
												<a href="<?= base_url('listing-details/' . rawurlencode($row->listing_slug)) ?>"><?= e($row->listing_name) ?></a>
											</h3>
											<?php if (!empty($row->listing_description)): ?>
												<p class="hp-card__desc"><?= e(mb_strimwidth(trim(strip_tags(html_entity_decode($row->listing_description))), 0, 105, '…')) ?></p>
											<?php endif; ?>
											<div class="hp-card__foot">
												<a class="hp-btn hp-btn--ghost hp-btn--sm" href="<?= base_url('listing-details/' . rawurlencode($row->listing_slug)) ?>">View details</a>
											</div>
										</div>
									</article>
								<?php endforeach; ?>
							</div>
						<?php else: ?>
							<div class="hp-empty">
								<span class="hp-empty__ic">
									<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V9l7-5 7 5v12"/><path d="M10 21v-6h4v6"/></svg>
								</span>
								<h3>No businesses listed yet</h3>
								<p>Once businesses are added to the directory they&rsquo;ll appear here, newest first.</p>
								<a class="hp-btn hp-btn--deep" href="<?= base_url('register') ?>">Add the first business</a>
							</div>
						<?php endif; ?>
					</section>

					<!-- Videos ------------------------------------------------ -->
					<?php if (!empty($show_videos)): ?>
						<section aria-labelledby="hp-videos-h">
							<div class="hp-sec__head">
								<h2 id="hp-videos-h">Community Highlights</h2>
								<a class="hp-sec__more" href="<?= base_url('videos') ?>">More videos &rarr;</a>
							</div>

							<?php if (!empty($latest_videos)): ?>
								<div class="hp-grid">
									<?php foreach (array_slice($latest_videos, 0, 3) as $video): ?>
										<?php $vurl = base_url('video_details/' . rawurlencode($video->video_slug)); ?>
										<article class="hp-card">
											<a class="hp-card__media hp-card__media--video" href="<?= $vurl ?>">
												<?php if (!empty($video->video_image)): ?>
													<?= responsive_img($video->video_image, $video->video_title) ?>
												<?php else: ?>
													<img src="<?= e_attr($hp_placeholder) ?>" alt="<?= e_attr($video->video_title) ?>" loading="lazy" decoding="async" width="640" height="480">
												<?php endif; ?>
												<span class="hp-card__play">
													<svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
												</span>
											</a>
											<div class="hp-card__body">
												<h3 class="hp-card__title"><a href="<?= $vurl ?>"><?= e($video->video_title) ?></a></h3>
												<?php if (!empty($video->video_description)): ?>
													<p class="hp-card__desc"><?= e(mb_strimwidth(trim(strip_tags(html_entity_decode($video->video_description))), 0, 105, '…')) ?></p>
												<?php endif; ?>
											</div>
										</article>
									<?php endforeach; ?>
								</div>
							<?php else: ?>
								<div class="hp-empty hp-empty--sm">
									<span class="hp-empty__ic">
										<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m10 9 5 3-5 3z"/></svg>
									</span>
									<h3>No videos published yet</h3>
									<p>Community videos will show up here once they&rsquo;re added.</p>
								</div>
							<?php endif; ?>
						</section>
					<?php endif; ?>

					<!-- In-content billboard ---------------------------------- -->
					<?php $slot = 'home-inline-billboard';
					include APPPATH . 'views/partials/ad_slot.php'; ?>

					<!-- News -------------------------------------------------- -->
					<?php if (!empty($show_news)): ?>
						<section aria-labelledby="hp-news-h">
							<div class="hp-sec__head">
								<h2 id="hp-news-h">Local Announcements &amp; Updates</h2>
								<a class="hp-sec__more" href="<?= base_url('all-news') ?>">More news &rarr;</a>
							</div>

							<?php if (!empty($latest_news)): ?>
								<div class="hp-grid">
									<?php foreach ($latest_news as $news): ?>
										<?php $nurl = base_url('news_details/' . (int) $news->news_id); ?>
										<article class="hp-card">
											<a class="hp-card__media" href="<?= $nurl ?>">
												<?php if (!empty($news->news_image)): ?>
													<?= responsive_img($news->news_image, $news->news_title) ?>
												<?php else: ?>
													<img src="<?= e_attr($hp_placeholder) ?>" alt="<?= e_attr($news->news_title) ?>" loading="lazy" decoding="async" width="640" height="480">
												<?php endif; ?>
												<span class="hp-card__badges"><span class="hp-tag hp-tag--cat">News</span></span>
											</a>
											<div class="hp-card__body">
												<h3 class="hp-card__title"><a href="<?= $nurl ?>"><?= e($news->news_title) ?></a></h3>
												<?php if (!empty($news->news_description)): ?>
													<p class="hp-card__desc"><?= e(mb_strimwidth(trim(strip_tags(html_entity_decode($news->news_description))), 0, 110, '…')) ?></p>
												<?php endif; ?>
												<div class="hp-card__foot">
													<a class="hp-btn hp-btn--ghost hp-btn--sm" href="<?= $nurl ?>">Read more</a>
												</div>
											</div>
										</article>
									<?php endforeach; ?>
								</div>
							<?php else: ?>
								<div class="hp-empty hp-empty--sm">
									<span class="hp-empty__ic">
										<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" aria-hidden="true"><path d="M4 5h13v14H4z"/><path d="M17 9h3v8a2 2 0 0 1-4 0"/><path d="M7 9h7M7 13h7"/></svg>
									</span>
									<h3>No news published yet</h3>
									<p>Local announcements will appear here once they&rsquo;re posted.</p>
								</div>
							<?php endif; ?>
						</section>
					<?php endif; ?>

					<!-- Deals ------------------------------------------------- -->
					<section aria-labelledby="hp-deals-h">
						<div class="hp-sec__head">
							<h2 id="hp-deals-h">Deals &amp; Coupons</h2>
							<a class="hp-sec__more" href="<?= base_url('coupons') ?>">All coupons &rarr;</a>
						</div>

						<?php if (!empty($home_deals)): ?>
							<div class="hp-grid">
								<?php foreach (array_slice($home_deals, 0, 3) as $deal): ?>
									<a class="hp-deal" href="<?= base_url('coupons/' . (int) $deal->coupon_id) ?>">
										<?php if (!empty($deal->coupon_code)): ?>
											<span class="hp-deal__off"><?= e($deal->coupon_code) ?></span>
										<?php endif; ?>
										<h3 class="hp-deal__title"><?= e($deal->coupon_name) ?></h3>
										<?php if (!empty($deal->listing_name)): ?>
											<span class="hp-deal__biz"><?= e($deal->listing_name) ?></span>
										<?php elseif (!empty($deal->owner_name)): ?>
											<span class="hp-deal__biz"><?= e($deal->owner_name) ?></span>
										<?php endif; ?>
										<?php if (!empty($deal->coupon_end_date) && strtotime($deal->coupon_end_date)): ?>
											<span class="hp-deal__exp">Ends <?= e(date('M j, Y', strtotime($deal->coupon_end_date))) ?></span>
										<?php endif; ?>
									</a>
								<?php endforeach; ?>
							</div>
						<?php else: ?>
							<div class="hp-empty hp-empty--sm">
								<span class="hp-empty__ic">
									<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" aria-hidden="true"><path d="M20 12V7H4v5a2 2 0 0 1 0 4v1h16v-1a2 2 0 0 1 0-4z"/><path d="M12 7v10"/></svg>
								</span>
								<h3>No live coupons right now</h3>
								<p>Deals posted by Pearland businesses appear here while they&rsquo;re running.</p>
							</div>
						<?php endif; ?>
					</section>

					<!-- Events ------------------------------------------------ -->
					<?php if (!empty($show_events)): ?>
						<section aria-labelledby="hp-events-h">
							<div class="hp-sec__head">
								<h2 id="hp-events-h">Upcoming Events</h2>
								<a class="hp-sec__more" href="<?= base_url('event') ?>">Full calendar &rarr;</a>
							</div>

							<?php if (!empty($home_events)): ?>
								<div class="hp-grid">
									<?php foreach (array_slice($home_events, 0, 3) as $event): ?>
										<?php
										$ets  = !empty($event->event_start_date) ? strtotime($event->event_start_date) : false;
										$eurl = !empty($event->event_slug)
											? base_url('event-details/' . rawurlencode($event->event_slug))
											: base_url('event');
										?>
										<a class="hp-event" href="<?= $eurl ?>">
											<?php if ($ets): ?>
												<span class="hp-event__date">
													<span class="hp-event__mon"><?= e(date('M', $ets)) ?></span>
													<span class="hp-event__day"><?= e(date('j', $ets)) ?></span>
												</span>
											<?php endif; ?>
											<span class="hp-event__body">
												<h3 class="hp-event__title"><?= e($event->event_name) ?></h3>
												<?php if (!empty($event->event_address)): ?>
													<span class="hp-event__meta"><?= e($event->event_address) ?></span>
												<?php endif; ?>
												<?php if (!empty($event->event_time)): ?>
													<span class="hp-event__meta"><?= e($event->event_time) ?></span>
												<?php endif; ?>
											</span>
										</a>
									<?php endforeach; ?>
								</div>
							<?php else: ?>
								<div class="hp-empty hp-empty--sm">
									<span class="hp-empty__ic">
										<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 11h18"/></svg>
									</span>
									<h3>No upcoming events</h3>
									<p>Events scheduled for today or later will be listed here.</p>
								</div>
							<?php endif; ?>
						</section>
					<?php endif; ?>

				</div><!-- /.hp-col-main -->

				<!-- ================================================ sidebar == -->
				<aside class="hp-col-side">

					<?php $slot = 'home-sidebar-top';
					include APPPATH . 'views/partials/ad_slot.php'; ?>

					<!-- Weather ---------------------------------------------- -->
					<div class="hp-widget">
						<h2 class="hp-widget__head">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="4.2"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M5 5l1.4 1.4M17.6 17.6 19 19M19 5l-1.4 1.4M6.4 17.6 5 19"/></svg>
							Pearland Weather
						</h2>
						<div class="hp-widget__body">
							<div id="hp-weather" class="hp-wx">
								<p class="hp-wx__load">Loading current conditions&hellip;</p>
							</div>
						</div>
					</div>

					<!-- Categories ------------------------------------------- -->
					<div class="hp-widget">
						<h2 class="hp-widget__head">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
							Business Categories
						</h2>
						<div class="hp-widget__body">
							<?php if (!empty($home_categories)): ?>
								<ul class="hp-catlist">
									<?php foreach (array_slice($home_categories, 0, 10) as $cat): ?>
										<li>
											<a href="<?= e_url($hp_cat_url($cat)) ?>">
												<span><?= e($cat->category_name) ?></span>
												<?php if (isset($cat->listing_count)): ?>
													<span class="hp-catlist__n"><?= (int) $cat->listing_count ?></span>
												<?php endif; ?>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
								<a class="hp-btn hp-btn--ghost hp-btn--sm hp-btn--block" style="margin-top:12px;" href="<?= base_url('all-category') ?>">Browse all categories</a>
							<?php else: ?>
								<p style="font-size:.86rem;color:var(--hp-ink-2);">No categories yet. <a href="<?= base_url('all_listing') ?>">Browse the directory</a>.</p>
							<?php endif; ?>
						</div>
					</div>

					<?php $slot = 'home-sidebar';
					include APPPATH . 'views/partials/ad_slot.php'; ?>

					<!-- Deals mini-list -------------------------------------- -->
					<?php if (!empty($home_deals)): ?>
						<div class="hp-widget">
							<h2 class="hp-widget__head">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M20 12V7H4v5a2 2 0 0 1 0 4v1h16v-1a2 2 0 0 1 0-4z"/><path d="M12 7v10"/></svg>
								Latest Deals
							</h2>
							<div class="hp-widget__body">
								<ul class="hp-mini">
									<?php foreach (array_slice($home_deals, 0, 4) as $i => $deal): ?>
										<li>
											<span class="hp-mini__rk"><?= (int) ($i + 1) ?></span>
											<span class="hp-mini__tx">
												<b><a href="<?= base_url('coupons/' . (int) $deal->coupon_id) ?>"><?= e($deal->coupon_name) ?></a></b>
												<span><?= e(!empty($deal->listing_name) ? $deal->listing_name : (!empty($deal->owner_name) ? $deal->owner_name : 'Pearland411')) ?></span>
											</span>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</div>
					<?php endif; ?>

					<!-- Events mini-list ------------------------------------- -->
					<?php if (!empty($show_events) && !empty($home_events)): ?>
						<div class="hp-widget">
							<h2 class="hp-widget__head">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 11h18"/></svg>
								Upcoming Events
							</h2>
							<div class="hp-widget__body">
								<ul class="hp-mini">
									<?php foreach (array_slice($home_events, 0, 3) as $event): ?>
										<?php
										$ets  = !empty($event->event_start_date) ? strtotime($event->event_start_date) : false;
										$eurl = !empty($event->event_slug)
											? base_url('event-details/' . rawurlencode($event->event_slug))
											: base_url('event');
										?>
										<li>
											<span class="hp-mini__rk"><?= $ets ? e(date('j', $ets)) : '&bull;' ?></span>
											<span class="hp-mini__tx">
												<b><a href="<?= $eurl ?>"><?= e($event->event_name) ?></a></b>
												<span><?= $ets ? e(date('M j', $ets)) : '' ?><?= (!empty($event->event_address) ? ' &middot; ' . e($event->event_address) : '') ?></span>
											</span>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</div>
					<?php endif; ?>

					<!-- Newsletter ------------------------------------------- -->
					<div class="hp-widget hp-nl">
						<h2 class="hp-widget__head">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/></svg>
							Subscribe for Local Deals
						</h2>
						<div class="hp-widget__body">
							<p>Get the best local deals, new openings and events in Pearland &mdash; straight to your inbox.</p>
							<form id="hp-newsletter" novalidate>
								<label class="hp-sr" for="hp-nl-email">Email address</label>
								<input type="email" id="hp-nl-email" name="email" placeholder="Your email address" required>
								<button class="hp-btn hp-btn--green" type="submit">Join</button>
							</form>
							<p class="hp-nl__msg" id="hp-nl-msg" role="status" aria-live="polite"></p>
						</div>
					</div>

					<?php
					// Held back until sold: an unsold 600px-tall house ad is more
					// harm than the zone is worth. Sells normally, shows nothing
					// while empty.
					$slot = 'home-sidebar-skyscraper';
					$slot_fallback = false;
					include APPPATH . 'views/partials/ad_slot.php';
					?>

					<!-- Services CTA — the only one on the page --------------- -->
					<div class="hp-svc">
						<span class="hp-svc__eyebrow">Pearland411 Services</span>
						<h3>Need a website that actually brings customers?</h3>
						<p>We build fast, mobile-first sites for local Pearland businesses.</p>
						<ul>
							<li><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" aria-hidden="true"><path d="m4 12 5 5L20 6"/></svg> Website design &amp; build</li>
							<li><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" aria-hidden="true"><path d="m4 12 5 5L20 6"/></svg> Local SEO that ranks</li>
							<li><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" aria-hidden="true"><path d="m4 12 5 5L20 6"/></svg> Hosting &amp; maintenance</li>
						</ul>
						<a class="hp-btn hp-btn--white hp-btn--block" href="<?= base_url('services') ?>">Explore our services</a>
					</div>

				</aside><!-- /.hp-col-side -->
			</div>
		</div>
	</main>

	<!-- ================================================ advertise band == -->
	<section class="hp-band hp-band--alt" aria-labelledby="hp-adv-h">
		<div class="hp-wrap">
			<div class="hp-band__head">
				<h2 id="hp-adv-h">Advertise on <em>Pearland411</em></h2>
				<p>Put your business in front of Pearland residents searching for exactly what you offer.</p>
			</div>

			<?php if (!empty($ad_rate_card)): ?>
				<div class="hp-rates">
					<?php foreach ($ad_rate_card as $i => $rate): ?>
						<div class="hp-rate<?= $i === 0 ? ' hp-rate--hi' : '' ?>">
							<?php if ($i === 0): ?><span class="hp-rate__flag">Most visible</span><?php endif; ?>
							<span class="hp-rate__key"><?= e($rate->slot_key) ?></span>
							<h3><?= e($rate->name) ?></h3>
							<?php if (!empty($rate->size)): ?>
								<span class="hp-rate__dim"><?= e(str_replace('x', ' &times; ', $rate->size)) ?></span>
							<?php endif; ?>
							<p class="hp-rate__price">$<?= e(number_format((float) $rate->cost, 0)) ?> <small>/ month</small></p>
							<ul>
								<li><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" aria-hidden="true"><path d="m4 12 5 5L20 6"/></svg> Shown to every Pearland411 visitor</li>
								<li><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" aria-hidden="true"><path d="m4 12 5 5L20 6"/></svg> Links straight to your website</li>
								<li><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" aria-hidden="true"><path d="m4 12 5 5L20 6"/></svg> Standard ad size &mdash; use creative you already have</li>
							</ul>
							<a class="hp-btn <?= $i === 0 ? 'hp-btn--green' : 'hp-btn--ghost' ?>" href="<?= base_url('post-your-ads') ?>">Get started</a>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else: ?>
				<div class="hp-empty">
					<span class="hp-empty__ic">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 15h10M7 11h6"/></svg>
					</span>
					<h3>Ad packages aren&rsquo;t set up yet</h3>
					<p>Prices on this page come straight from the ad-zone catalogue, so they can never drift out of date. Run migration 0015 and set your rates to fill this in.</p>
					<a class="hp-btn hp-btn--deep" href="<?= base_url('post-your-ads') ?>">Talk to us about advertising</a>
				</div>
			<?php endif; ?>

			<?php if (!empty($listing_plans)): ?>
				<p style="text-align:center;margin-top:24px;font-size:.9rem;color:var(--hp-ink-2);">
					Looking for a business listing instead?
					<a class="hp-sec__more" href="<?= base_url('pricing-plan') ?>">See all <?= count($listing_plans) ?> packages &rarr;</a>
				</p>
			<?php endif; ?>
		</div>
	</section>

	<!-- ============================================ footer billboard ad == -->
	<div class="hp-wrap" style="padding-top:28px;">
		<?php
		// Also held back until sold — the site-wide footer banner already
		// covers this position.
		$slot = 'home-footer-billboard';
		$slot_fallback = false;
		include APPPATH . 'views/partials/ad_slot.php';
		?>
	</div>

	<!-- ======================================================= services == -->
	<section class="hp-band" aria-labelledby="hp-grow-h">
		<div class="hp-wrap">
			<div class="hp-band__head">
				<h2 id="hp-grow-h">Grow Your Business Online</h2>
				<p>Professional website development for Pearland businesses &mdash; and a discount for anyone listed on Pearland411.</p>
			</div>
			<div class="hp-feat4">
				<div>
					<span class="hp-feat4__ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="14" rx="2"/><path d="M8 22h8M12 18v4"/></svg></span>
					<h3>Custom Websites</h3>
					<p>Built around how your business actually works, not a template.</p>
				</div>
				<div>
					<span class="hp-feat4__ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="6" y="2" width="12" height="20" rx="2.5"/><path d="M11 19h2"/></svg></span>
					<h3>Mobile First</h3>
					<p>Most of your customers arrive on a phone. They come first.</p>
				</div>
				<div>
					<span class="hp-feat4__ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></span>
					<h3>Local SEO</h3>
					<p>Get found when someone nearby searches for what you do.</p>
				</div>
				<div>
					<span class="hp-feat4__ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 14v-3a8 8 0 0 1 16 0v3"/><path d="M4 14a2 2 0 0 0 2 2h1v-4H6a2 2 0 0 0-2 2zM20 14a2 2 0 0 1-2 2h-1v-4h1a2 2 0 0 1 2 2z"/><path d="M18 16v1a3 3 0 0 1-3 3h-3"/></svg></span>
					<h3>Ongoing Support</h3>
					<p>Hosting, updates and someone to call when you need a change.</p>
				</div>
			</div>
			<p style="text-align:center;margin-top:28px;">
				<a class="hp-btn hp-btn--deep hp-btn--lg" href="<?= base_url('request-service') ?>">Get a quote</a>
			</p>
		</div>
	</section>

	<!-- ==================================================== how it works == -->
	<section class="hp-band hp-band--alt" aria-labelledby="hp-how-h">
		<div class="hp-wrap">
			<div class="hp-band__head">
				<h2 id="hp-how-h">How It Works</h2>
				<p>Four steps from signing up to getting found by Pearland customers.</p>
			</div>
			<div class="hp-steps">
				<div>
					<span class="hp-steps__n">1</span>
					<h3>Create an account</h3>
					<p>Register free in under a minute &mdash; no card needed.</p>
				</div>
				<div>
					<span class="hp-steps__n">2</span>
					<h3>Add your business</h3>
					<p>Claim an existing listing or add a new one with your details.</p>
				</div>
				<div>
					<span class="hp-steps__n">3</span>
					<h3>Get found</h3>
					<p>Appear in searches and category pages across the directory.</p>
				</div>
				<div>
					<span class="hp-steps__n">4</span>
					<h3>Grow</h3>
					<p>Upgrade for photos, a map, a video and featured placement.</p>
				</div>
			</div>
		</div>
	</section>

	<!-- ===================================================== final CTA == -->
	<section class="hp-final">
		<div class="hp-wrap">
			<h2>List your business for free</h2>
			<p>Join the Pearland businesses already reaching local customers through Pearland411. Free to list, upgrade whenever you&rsquo;re ready.</p>
			<a class="hp-btn hp-btn--white hp-btn--lg" href="<?= base_url('register') ?>">Add my business &rarr;</a>
		</div>
	</section>

	<!-- ======================================================== footer == -->
	<footer class="hp-foot">
		<div class="hp-wrap">
			<p class="hp-foot__supp">
				<b>Free support:</b> <a href="tel:<?= e_attr(preg_replace('/[^0-9+]/', '', $hp_phone)) ?>"><?= e($hp_phone) ?></a>
				&nbsp;&middot;&nbsp;
				<b>Email:</b> <a href="mailto:<?= e_attr($hp_email) ?>"><?= e($hp_email) ?></a>
			</p>

			<div class="hp-foot__cols">
				<div>
					<h4>Top Categories</h4>
					<ul>
						<?php if (!empty($home_categories)): ?>
							<?php foreach (array_slice($home_categories, 0, 6) as $cat): ?>
								<li><a href="<?= e_url($hp_cat_url($cat)) ?>"><?= e($cat->category_name) ?></a></li>
							<?php endforeach; ?>
						<?php else: ?>
							<li><a href="<?= base_url('all-category') ?>">Browse all categories</a></li>
						<?php endif; ?>
					</ul>
				</div>
				<div>
					<h4>Browse</h4>
					<ul>
						<li><a href="<?= base_url('all_listing') ?>">All listings</a></li>
						<li><a href="<?= base_url('coupons') ?>">Deals &amp; coupons</a></li>
						<?php if (!empty($show_events)): ?><li><a href="<?= base_url('event') ?>">Events</a></li><?php endif; ?>
						<?php if (!empty($show_news)): ?><li><a href="<?= base_url('all-news') ?>">News</a></li><?php endif; ?>
						<?php if (!empty($show_videos)): ?><li><a href="<?= base_url('videos') ?>">Videos</a></li><?php endif; ?>
						<li><a href="<?= base_url('post-your-ads') ?>">Advertise with us</a></li>
					</ul>
				</div>
				<div>
					<h4>Help &amp; Support</h4>
					<ul>
						<li><a href="<?= base_url('about-us') ?>">About us</a></li>
						<li><a href="<?= base_url('contact-us') ?>">Contact us</a></li>
						<li><a href="<?= base_url('faq') ?>">FAQ</a></li>
						<li><a href="<?= base_url('privacy-policy') ?>">Privacy policy</a></li>
						<li><a href="<?= base_url('terms-of-use') ?>">Terms of use</a></li>
					</ul>
				</div>
				<div>
					<h4>Get In Touch</h4>
					<ul>
						<li><?= e($hp_address) ?></li>
						<li><a href="tel:<?= e_attr(preg_replace('/[^0-9+]/', '', $hp_phone)) ?>"><?= e($hp_phone) ?></a></li>
						<li><a href="mailto:<?= e_attr($hp_email) ?>"><?= e($hp_email) ?></a></li>
					</ul>
					<div class="hp-foot__social">
						<?php foreach ($hp_socials as $key => $meta):
							$url = site_setting($key, '');
							if (!$url) continue;
						?>
							<a href="<?= e_url($url) ?>" target="_blank" rel="noopener" aria-label="<?= e_attr($meta[0]) ?>">
								<svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><?= $meta[1] ?></svg>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<p class="hp-foot__cr">
				&copy; <?= date('Y') ?> Pearland411. All rights reserved.
			</p>
		</div>
	</footer>

	<script>
		/* Homepage behaviour — vanilla, no framework.
		   Replaces the jQuery + Bootstrap + Swiper + FullCalendar stack the
		   old homepage loaded (over 1 MB of JS) with roughly 4 KB. */
		(function () {
			'use strict';

			var BASE = <?= json_encode(rtrim(base_url(), '/') . '/') ?>;

			/* ---- Explore / mobile menu -------------------------------- */
			var mega = document.getElementById('hp-mega');
			['hp-explore-btn', 'hp-burger'].forEach(function (id) {
				var btn = document.getElementById(id);
				if (!btn || !mega) return;
				btn.addEventListener('click', function () {
					var open = mega.classList.toggle('is-open');
					btn.setAttribute('aria-expanded', String(open));
				});
			});
			document.addEventListener('keydown', function (e) {
				if (e.key === 'Escape' && mega && mega.classList.contains('is-open')) {
					mega.classList.remove('is-open');
					['hp-explore-btn', 'hp-burger'].forEach(function (id) {
						var b = document.getElementById(id);
						if (b) b.setAttribute('aria-expanded', 'false');
					});
				}
			});

			/* ---- Hero category jump ----------------------------------- */
			/* With no keyword typed, picking a category goes straight to that
			   category's page rather than running an empty search. */
			var heroForm = document.querySelector('.hp-hero__form');
			var heroCat = document.getElementById('hp-hero-cat');
			var heroQ = document.getElementById('hp-hero-q');

			if (heroForm && heroCat && heroQ) {
				heroQ.addEventListener('input', function () {
					heroQ.required = heroCat.value === '';
				});
				heroForm.addEventListener('submit', function (e) {
					if (heroCat.value && heroQ.value.trim() === '') {
						e.preventDefault();
						window.location.href = heroCat.value;
					}
				});
				heroCat.addEventListener('change', function () {
					heroQ.required = heroCat.value === '';
				});
			}

			/* ---- Search suggestions ----------------------------------- */
			var q = document.getElementById('hp-q');
			var box = document.getElementById('hp-suggest');
			var timer = null;

			if (q && box) {
				q.addEventListener('input', function () {
					var term = q.value.trim();
					clearTimeout(timer);
					if (term.length < 2) { box.classList.remove('is-open'); return; }

					timer = setTimeout(function () {
						var body = new FormData();
						body.append('term', term);

						fetch(BASE + 'user/search_suggestions', {
							method: 'POST',
							body: body,
							credentials: 'same-origin'
						})
							.then(function (r) { return r.ok ? r.json() : []; })
							.then(function (rows) {
								box.textContent = '';
								if (!rows || !rows.length) { box.classList.remove('is-open'); return; }
								rows.slice(0, 8).forEach(function (row) {
									var text = row && row.suggestion ? row.suggestion : row;
									if (!text) return;
									var li = document.createElement('li');
									var a = document.createElement('a');
									a.href = BASE + 'search?q=' + encodeURIComponent(text);
									a.textContent = text;          // textContent, never innerHTML
									li.appendChild(a);
									box.appendChild(li);
								});
								box.classList.add('is-open');
							})
							.catch(function () { box.classList.remove('is-open'); });
					}, 280);
				});

				document.addEventListener('click', function (e) {
					if (!e.target.closest('.hp-search')) box.classList.remove('is-open');
				});
			}

			/* ---- Newsletter ------------------------------------------- */
			var nl = document.getElementById('hp-newsletter');
			var nlMsg = document.getElementById('hp-nl-msg');

			if (nl && nlMsg) {
				nl.addEventListener('submit', function (e) {
					e.preventDefault();
					var input = document.getElementById('hp-nl-email');
					var email = input ? input.value.trim() : '';
					var btn = nl.querySelector('button');

					nlMsg.className = 'hp-nl__msg';
					if (!email || email.indexOf('@') < 1) {
						nlMsg.classList.add('is-err');
						nlMsg.textContent = 'Please enter a valid email address.';
						return;
					}

					if (btn) { btn.disabled = true; btn.textContent = 'Joining…'; }

					var body = new FormData();
					body.append('email', email);

					fetch(BASE + 'user/subscribe_newsletter', {
						method: 'POST',
						body: body,
						credentials: 'same-origin'
					})
						.then(function (r) { return r.json(); })
						.then(function (res) {
							nlMsg.classList.add(res && res.success ? 'is-ok' : 'is-err');
							nlMsg.textContent = (res && res.message)
								? res.message
								: (res && res.success ? 'Thanks — check your inbox to confirm.' : 'Something went wrong. Please try again.');
							if (res && res.success && input) input.value = '';
						})
						.catch(function () {
							nlMsg.classList.add('is-err');
							nlMsg.textContent = 'Could not reach the server. Please try again.';
						})
						.then(function () {
							if (btn) { btn.disabled = false; btn.textContent = 'Join'; }
						});
				});
			}

			/* ---- Weather (Open-Meteo via our own proxy) ---------------- */
			var wx = document.getElementById('hp-weather');

			// Open-Meteo WMO weather codes → short English.
			function describe(code) {
				if (code === 0) return 'Clear sky';
				if (code === 1) return 'Mainly clear';
				if (code === 2) return 'Partly cloudy';
				if (code === 3) return 'Overcast';
				if (code === 45 || code === 48) return 'Fog';
				if (code >= 51 && code <= 57) return 'Drizzle';
				if (code >= 61 && code <= 67) return 'Rain';
				if (code >= 71 && code <= 77) return 'Snow';
				if (code >= 80 && code <= 82) return 'Rain showers';
				if (code >= 85 && code <= 86) return 'Snow showers';
                if (code === 95) return 'Thunderstorm';
				if (code >= 96 && code <= 99) return 'Thunderstorm, hail';
				return 'Current conditions';
			}

			function bearing(deg) {
				var dirs = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];
				return dirs[Math.round(((deg || 0) % 360) / 22.5) % 16];
			}

			function el(tag, cls, text) {
				var n = document.createElement(tag);
				if (cls) n.className = cls;
				if (text !== undefined) n.textContent = text;
				return n;
			}

			if (wx) {
				fetch(BASE + 'weather', { credentials: 'same-origin' })
					.then(function (r) { return r.ok ? r.json() : null; })
					.then(function (d) {
						if (!d || d.error || !d.current) throw new Error('no data');

						var c = d.current;
						wx.textContent = '';

						var now = el('div', 'hp-wx__now');
						now.appendChild(el('span', 'hp-wx__temp', Math.round(c.temperature_2m) + '°'));

						var desc = el('span', 'hp-wx__desc');
						desc.appendChild(el('b', null, describe(c.weather_code)));
						desc.appendChild(document.createTextNode('Feels like ' + Math.round(c.apparent_temperature) + '°'));
						now.appendChild(desc);
						wx.appendChild(now);

						var meta = el('div', 'hp-wx__meta');
						[
							['Wind', Math.round(c.wind_speed_10m) + ' mph ' + bearing(c.wind_direction_10m)],
							['Humidity', Math.round(c.relative_humidity_2m) + '%'],
							['High', d.daily ? Math.round(d.daily.temperature_2m_max[0]) + '°' : '—'],
							['Low', d.daily ? Math.round(d.daily.temperature_2m_min[0]) + '°' : '—']
						].forEach(function (pair) {
							var s = el('span', null, pair[0] + ' ');
							s.appendChild(el('b', null, pair[1]));
							meta.appendChild(s);
						});
						wx.appendChild(meta);

						if (d.daily && d.daily.time) {
							var days = el('div', 'hp-wx__days');
							for (var i = 1; i <= 3 && i < d.daily.time.length; i++) {
								var day = el('div', 'hp-wx__day');
								var dt = new Date(d.daily.time[i] + 'T12:00:00');
								day.appendChild(el('span', 'hp-wx__dname',
									dt.toLocaleDateString('en-US', { weekday: 'short' })));
								day.appendChild(el('span', 'hp-wx__dtemp',
									Math.round(d.daily.temperature_2m_max[i]) + '/' + Math.round(d.daily.temperature_2m_min[i])));
								days.appendChild(day);
							}
							wx.appendChild(days);
						}
					})
					.catch(function () {
						wx.textContent = '';
						wx.appendChild(el('p', 'hp-wx__load', 'Weather is unavailable right now.'));
					});
			}
		})();
	</script>

</body>

</html>
