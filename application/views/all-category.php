<?php
/**
 * /all-category — every business category on Pearland411.
 *
 * Same shell as the directory, events, news and videos: a hero band, a
 * counted strip, then a main column plus a right-hand sidebar. Styling comes
 * from assets/css/directory.css (the shared kit) + assets/css/content.css via
 * $page_css; there is no <style> block here — the previous version opened with
 * ~320 lines of them, then a second block mid-page and a third inside the
 * pagination markup.
 *
 * NOT PAGINATED. The whole index is on this page: the busiest twelve
 * categories as picture tiles, then every category in a dense A–Z list that
 * filters in the browser. A category list is a lookup table, not a feed —
 * paged twenty at a time, a visitor hunting for "Plumbers" had to guess which
 * page held it, and find-in-page could only ever see a twentieth of the list.
 *
 * From User::all_category(): $AllCategory, $popular_categories,
 * $categories_by_letter, $search_query, $total_categories, $live_categories,
 * $total_listings, $ad_slots. Every one is defaulted below so a thin caller
 * can never fatal the page.
 */

$AllCategory = (isset($AllCategory) && is_array($AllCategory)) ? $AllCategory : array();

$popular_categories   = (isset($popular_categories)   && is_array($popular_categories))   ? $popular_categories   : array();
$categories_by_letter = (isset($categories_by_letter) && is_array($categories_by_letter)) ? $categories_by_letter : array();

$search_query     = isset($search_query)     ? (string) $search_query : '';
$total_categories = isset($total_categories) ? (int) $total_categories : count($AllCategory);
$live_categories  = isset($live_categories)  ? (int) $live_categories  : 0;
$total_listings   = isset($total_listings)   ? (int) $total_listings   : 0;

// A thin caller (or the search view) may pass only $AllCategory. Build the
// two derived shapes here rather than fataling on a missing variable.
if (!$popular_categories && $AllCategory) {
	foreach ($AllCategory as $__c) {
		if ((int) ($__c->listing_count ?? 0) > 0) $popular_categories[] = $__c;
		if (count($popular_categories) >= 12) break;
	}
	unset($__c);
}
if (!$categories_by_letter && $AllCategory) {
	foreach ($AllCategory as $__c) {
		$__l = strtoupper(substr(trim((string) $__c->category_name), 0, 1));
		if ($__l === '' || !preg_match('/[A-Z]/', $__l)) $__l = '#';
		$categories_by_letter[$__l][] = $__c;
	}
	ksort($categories_by_letter);
	unset($__c, $__l);
}

$ad_slots = (isset($ad_slots) && is_array($ad_slots)) ? $ad_slots : array(
	'top'      => 'top-banner-site-wide',
	'inline'   => 'middle-banner-category',
	'side_1'   => 'right-side-banner-1',
	'side_2'   => 'right-side-banner-2',
	'side_sky' => 'right-panel-skyscraper',
	'bottom'   => 'bottom-banner-site-wide',
);

/**
 * Directory URL for a category. The stored slug verbatim when there is one —
 * that is the string the directory matches on — and only a missing slug is
 * generated, through the shared pearland_slug() so link generation and link
 * resolution can never disagree.
 */
$cat_url = function ($category) {
	$slug = !empty($category->category_slug)
		? (string) $category->category_slug
		: pearland_slug($category->category_name);
	return base_url('all_listing/' . rawurlencode($slug));
};

/** Icons keyed by a word in the category name — the same set the homepage uses. */
$cat_icons = array(
	'home'         => '<path d="M3 11.5 12 4l9 7.5"/><path d="M5.5 10.5V20h13v-9.5"/><path d="M10 20v-5h4v5"/>',
	'health'       => '<path d="M20.5 8.6a4.6 4.6 0 0 0-8.5-2.4 4.6 4.6 0 0 0-8.5 2.4c0 4.7 8.5 10 8.5 10s8.5-5.3 8.5-10z"/>',
	'medical'      => '<rect x="3.5" y="6" width="17" height="14" rx="2.5"/><path d="M9 6V4.5A1.5 1.5 0 0 1 10.5 3h3A1.5 1.5 0 0 1 15 4.5V6"/><path d="M12 10v6M9 13h6"/>',
	'dental'       => '<rect x="3.5" y="6" width="17" height="14" rx="2.5"/><path d="M9 6V4.5A1.5 1.5 0 0 1 10.5 3h3A1.5 1.5 0 0 1 15 4.5V6"/><path d="M12 10v6M9 13h6"/>',
	'auto'         => '<path d="M4 16.5h16"/><path d="M5.5 16.5V12l2-4.5h9l2 4.5v4.5"/><path d="M5.5 12h13"/><circle cx="8" cy="17.5" r="1.7"/><circle cx="16" cy="17.5" r="1.7"/>',
	'real'         => '<path d="M3.5 20.5V8.5l6-4 6 4v12"/><path d="M15.5 12.5h5v8"/><path d="M6.5 11h2M6.5 14.5h2M6.5 18h2"/>',
	'food'         => '<path d="M7 3.5v7a2.5 2.5 0 0 0 5 0v-7"/><path d="M9.5 13v7.5"/><path d="M17.5 3.5c-1.3 1.8-2 3.6-2 5.4s.7 2.8 2 2.8v8.8"/>',
	'restaurant'   => '<path d="M7 3.5v7a2.5 2.5 0 0 0 5 0v-7"/><path d="M9.5 13v7.5"/><path d="M17.5 3.5c-1.3 1.8-2 3.6-2 5.4s.7 2.8 2 2.8v8.8"/>',
	'beauty'       => '<circle cx="6.5" cy="17.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/><path d="m8.4 15.7 8.2-11.2M15.6 15.7 7.4 4.5"/>',
	'professional' => '<rect x="2.5" y="7" width="19" height="12.5" rx="2"/><path d="M8.5 7V5.5A1.5 1.5 0 0 1 10 4h4a1.5 1.5 0 0 1 1.5 1.5V7"/><path d="M2.5 12.5h19"/>',
	'business'     => '<rect x="2.5" y="7" width="19" height="12.5" rx="2"/><path d="M8.5 7V5.5A1.5 1.5 0 0 1 10 4h4a1.5 1.5 0 0 1 1.5 1.5V7"/><path d="M2.5 12.5h19"/>',
	'financial'    => '<path d="M12 3.5v17"/><path d="M16.5 7H10a2.75 2.75 0 0 0 0 5.5h4a2.75 2.75 0 0 1 0 5.5H7"/>',
	'insurance'    => '<path d="M12 3l8 3v6c0 5-3.5 8.5-8 9-4.5-.5-8-4-8-9V6z"/><path d="m9 12 2 2 4-4"/>',
	'legal'        => '<path d="M12 3.5v17M6.5 20.5h11"/><path d="M4 7.5h16"/><path d="m7.5 7.5-3 6h6zM16.5 7.5l-3 6h6z"/>',
	'attorney'     => '<path d="M12 3.5v17M6.5 20.5h11"/><path d="M4 7.5h16"/><path d="m7.5 7.5-3 6h6zM16.5 7.5l-3 6h6z"/>',
	'education'    => '<path d="m2.8 9 9.2-4.6L21.2 9 12 13.6z"/><path d="M6.6 11v4.6c0 1.5 2.4 2.9 5.4 2.9s5.4-1.4 5.4-2.9V11"/>',
	'pet'          => '<circle cx="8" cy="6.5" r="1.9"/><circle cx="16" cy="6.5" r="1.9"/><circle cx="4.6" cy="12" r="1.7"/><circle cx="19.4" cy="12" r="1.7"/><path d="M12 11.5c-3 0-5.2 2.6-5.2 5.1 0 1.7 1.3 3 3 3h4.4c1.7 0 3-1.3 3-3 0-2.5-2.2-5.1-5.2-5.1z"/>',
	'shop'         => '<path d="M5 8h14l-1.2 11.5H6.2z"/><path d="M8.7 8V6.2a3.3 3.3 0 0 1 6.6 0V8"/>',
	'retail'       => '<path d="M5 8h14l-1.2 11.5H6.2z"/><path d="M8.7 8V6.2a3.3 3.3 0 0 1 6.6 0V8"/>',
	'construct'    => '<path d="M4 20.5h16"/><path d="M6.5 20.5v-6h11v6"/><path d="M4.5 14.5 12 8l7.5 6.5"/>',
	'clean'        => '<path d="M8.5 20.5h7l-1-8h-5z"/><path d="M12 12.5V4M9.5 6.5h5"/>',
	'tech'         => '<rect x="2.5" y="4.5" width="19" height="12.5" rx="2"/><path d="M9 20.5h6M12 17v3.5"/>',
	'sport'        => '<circle cx="12" cy="12" r="8.5"/><path d="M12 3.5c2.6 2.3 4 5.3 4 8.5s-1.4 6.2-4 8.5c-2.6-2.3-4-5.3-4-8.5s1.4-6.2 4-8.5z"/><path d="M3.6 12h16.8"/>',
	'_default'     => '<path d="M4 9.5 5.5 5h13L20 9.5"/><path d="M4 9.5a2.4 2.4 0 0 0 4 1.6 2.4 2.4 0 0 0 4 0 2.4 2.4 0 0 0 4 0 2.4 2.4 0 0 0 4-1.6"/><path d="M5.5 12.4V20h13v-7.6"/><path d="M10 20v-4.5h4V20"/>',
);
$cat_icon = function ($name) use ($cat_icons) {
	$n = strtolower((string) $name);
	foreach ($cat_icons as $k => $path) {
		if ($k !== '_default' && strpos($n, $k) !== false) return $path;
	}
	return $cat_icons['_default'];
};

// Three accents in rotation, so a grid of two dozen tiles doesn't read as one
// green block — the same rule as the homepage strip.
$accents = array('green', 'bu', 'green', 'navy', 'green', 'bu', 'navy', 'green');

// The fullest categories, for the sidebar rail — the same rows the tiles use.
$top_cats = array_slice($popular_categories, 0, 8);
?>

<section class="p411-ct p411-ct--categories">

	<!-- ============================================================ HERO -->
	<div class="p411-hero">
		<div class="p411-hero__bg" style="background-image:url('<?= base_url('assets/images/home/hero-pearland.svg') ?>');" aria-hidden="true"></div>
		<div class="container">
			<nav class="p411-crumb" aria-label="Breadcrumb">
				<a href="<?= base_url() ?>">Home</a>
				<span aria-hidden="true">/</span>
				<span class="is-current">Categories</span>
			</nav>

			<h1>Browse Pearland by category</h1>
			<p class="p411-hero__lede">
				Every kind of business on Pearland411 &mdash; home services, healthcare, restaurants,
				automotive, professional services and more. Pick a category to see who does it locally.
			</p>

			<div class="p411-hero__stats">
				<div><b><?= number_format($total_categories) ?></b>categories</div>
				<?php if ($live_categories > 0): ?>
					<div><b><?= number_format($live_categories) ?></b>with businesses in them</div>
				<?php endif; ?>
				<?php if ($total_listings > 0): ?>
					<div><b><?= number_format($total_listings) ?></b>businesses listed</div>
				<?php endif; ?>
			</div>

		</div>
	</div>

	<!-- ==================================================== FILTER STRIP -->
	<div class="p411-ct-filters">
		<div class="container">
			<div class="p411-ct-tabs" role="navigation" aria-label="Browse">
				<a href="<?= e_url(base_url('all-category')) ?>" class="is-active" aria-current="page">
					All categories<em><?= number_format($total_categories) ?></em>
				</a>
				<a href="<?= e_url(base_url('all_listing')) ?>">Every business<?php if ($total_listings > 0): ?><em><?= number_format($total_listings) ?></em><?php endif; ?></a>
				<a href="<?= e_url(base_url('all_listing') . '?sort=featured') ?>">Featured first</a>
			</div>
			<p class="p411-ct-filters__note">
				<?= $search_query !== ''
					? 'Searching for &ldquo;' . e($search_query) . '&rdquo;'
					: 'Pearland, TX and the surrounding area' ?>
			</p>
		</div>
	</div>

	<div class="container">
		<div class="p411-shell">

			<!-- ==================================================== MAIN -->
			<div class="p411-shell__main">

				<div class="p411-adrow">
					<?php $slot = $ad_slots['top'];
					include APPPATH . 'views/partials/ad_slot.php'; ?>
				</div>

				<?php if (!empty($AllCategory)): ?>
					<div class="p411-toolbar">
						<div class="p411-toolbar__count">
							<b><?= number_format($total_categories) ?></b>
							categor<?= $total_categories === 1 ? 'y' : 'ies' ?>, all on this page
							<span class="p411-toolbar__meta" id="catFilterCount" role="status"></span>
						</div>
						<div class="p411-toolbar__right">
							<a class="p411-btn p411-btn--sm p411-btn--ghost" href="<?= e_url(base_url('all_listing')) ?>">View all businesses</a>
						</div>
					</div>

					<?php // ------------------------------------------ POPULAR --
					      // The busiest dozen get picture tiles. Everything else
					      // is in the A-Z index below: 500 tiles would be a
					      // beautiful way to make a category impossible to find. ?>
					<?php if (!empty($popular_categories)): ?>
						<section class="p411-azsec" data-cat-block="popular">
							<div class="p411-sec__head">
								<h2>Most businesses</h2>
							</div>
							<div class="p411-cats p411-cats--index">
								<?php foreach ($popular_categories as $i => $category): ?>
									<?php $count = (int) ($category->listing_count ?? 0); ?>
									<a class="p411-cat--<?= $accents[$i % count($accents)] ?>" href="<?= e_url($cat_url($category)) ?>"
									   data-cat-name="<?= e_attr(strtolower($category->category_name)) ?>">
										<i><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $cat_icon($category->category_name) ?></svg></i>
										<b><?= e($category->category_name) ?></b>
										<span><?= number_format($count) ?> business<?= $count === 1 ? '' : 'es' ?></span>
									</a>
								<?php endforeach; ?>
							</div>
						</section>
					<?php endif; ?>

					<div class="p411-adrow" style="margin-top:22px;">
						<?php $slot = $ad_slots['inline'];
						include APPPATH . 'views/partials/ad_slot.php'; ?>
					</div>

					<?php // ---------------------------------------------- A-Z --
					      // The complete index. Text and a count, no image: that
					      // is what makes a list of several hundred rows cheap
					      // enough to ship in one page. ?>
					<section class="p411-azsec">
						<div class="p411-sec__head">
							<h2>All categories A&ndash;Z</h2>
						</div>

						<?php // Filter and jump bar travel together and stick to the top
						      // of the window: on an index this long the controls have to
						      // stay reachable from wherever you have scrolled to. ?>
						<div class="p411-azctl">
							<?php // ------------------------------------------- FILTER --
							      // Filters the rendered list in the browser: no request,
							      // no page reload, and it searches every category rather
							      // than the twenty that used to be on screen. The form
							      // still submits for visitors without JavaScript, which
							      // is what the SQL search in the controller is for. ?>
							<form class="p411-catfilter" action="<?= base_url('all-category') ?>" method="get" role="search">
								<label class="sr-only" for="catFilter">Filter categories</label>
								<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
								<input type="search" name="q" id="catFilter" value="<?= e_attr($search_query) ?>"
									   placeholder="Start typing to filter <?= number_format($total_categories) ?> categories&hellip;"
									   autocomplete="off">
								<button type="submit" class="p411-btn p411-btn--sm">Search</button>
							</form>


							<?php if (count($categories_by_letter) > 1): ?>
								<nav class="p411-azbar" aria-label="Jump to a letter">
									<?php foreach (array_keys($categories_by_letter) as $letter): ?>
										<a href="#cat-<?= e_attr($letter === '#' ? 'other' : strtolower($letter)) ?>"><?= e($letter) ?></a>
									<?php endforeach; ?>
								</nav>
							<?php endif; ?>
						</div>

						<?php foreach ($categories_by_letter as $letter => $rows): ?>
							<?php // content-visibility lets the browser skip the layout
							      // and paint of a group that is off screen. On a long
							      // index that is the difference between a page that
							      // renders instantly and one that stutters. ?>
							<div class="p411-azgroup" id="cat-<?= e_attr($letter === '#' ? 'other' : strtolower($letter)) ?>"
								 data-cat-group="<?= e_attr($letter) ?>">
								<h3 class="p411-azgroup__letter"><?= e($letter) ?></h3>
								<ul class="p411-azlist">
									<?php foreach ($rows as $category): ?>
										<?php $count = (int) ($category->listing_count ?? 0); ?>
										<li data-cat-name="<?= e_attr(strtolower($category->category_name)) ?>"
											<?= $count === 0 ? 'class="is-empty"' : '' ?>>
											<a href="<?= e_url($cat_url($category)) ?>">
												<span><?= e($category->category_name) ?></span>
												<em><?= $count > 0 ? number_format($count) : '&mdash;' ?></em>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endforeach; ?>

						<p class="p411-aznone" id="catFilterNone" hidden>
							No category matches that. <a href="<?= e_url(base_url('all_listing')) ?>">Search the businesses instead &rarr;</a>
						</p>
					</section>

				<?php else: ?>
					<!-- ======================================= EMPTY STATE -->
					<div class="p411-empty">
						<div class="p411-empty__icon">
							<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<circle cx="11" cy="11" r="7"></circle>
								<path d="m20 20-3.5-3.5"></path>
							</svg>
						</div>
						<?php if ($search_query !== ''): ?>
							<h3>No categories match &ldquo;<?= e($search_query) ?>&rdquo;</h3>
							<p>
								Try a shorter word, or search the directory itself &mdash; a business can be
								named for something its category is not.
							</p>
							<div class="p411-empty__actions">
								<a class="p411-btn" href="<?= e_url(base_url('all-category')) ?>">View all categories</a>
								<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('search') . '?q=' . rawurlencode($search_query)) ?>">Search businesses instead</a>
							</div>
						<?php else: ?>
							<h3>No categories yet</h3>
							<p>Categories appear here as soon as they are added in the admin panel.</p>
							<div class="p411-empty__actions">
								<a class="p411-btn" href="<?= e_url(base_url('all_listing')) ?>">Browse the directory</a>
							</div>
						<?php endif; ?>
					</div>

					<?php // The zone still renders on an empty page — that is exactly
					      // where a paid banner should not go missing. ?>
					<div class="p411-adrow" style="margin-top:22px;">
						<?php $slot = $ad_slots['inline'];
						include APPPATH . 'views/partials/ad_slot.php'; ?>
					</div>

				<?php endif; ?>
			</div>

			<!-- ================================================= SIDEBAR -->
			<aside class="p411-shell__side">

				<?php $slot = $ad_slots['side_1'];
				// The static promo directly below already carries the
				// web-services pitch, so an unsold zone sells the space itself.
				$slot_creative = 'house-ad-burgundy';
				include APPPATH . 'views/partials/ad_slot.php'; ?>

				<?php include APPPATH . 'views/partials/service_promo.php'; ?>

				<?php if (!empty($top_cats)): ?>
					<div class="p411-card">
						<div class="p411-card__head">
							<h2>Most businesses</h2>
							<a class="p411-more" href="<?= e_url(base_url('all_listing')) ?>">Directory &rarr;</a>
						</div>
						<ul class="p411-catlist">
							<?php foreach ($top_cats as $category): ?>
								<li>
									<a href="<?= e_url($cat_url($category)) ?>">
										<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $cat_icon($category->category_name) ?></svg>
										<?= e($category->category_name) ?>
										<em><?= number_format((int) $category->listing_count) ?></em>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<?php $slot = $ad_slots['side_2'];
				$slot_fallback_partial = 'banner_design_promo.php';
				include APPPATH . 'views/partials/ad_slot.php'; ?>

				<!-- Add your business -->
				<div class="p411-card">
					<div class="p411-card__head">
						<h2>Don&rsquo;t see your category?</h2>
					</div>
					<p class="p411-form__hint">
						Add your business and tell us what you do &mdash; if there is no category for it yet,
						we&rsquo;ll create one. The basic listing is free.
					</p>
					<a class="p411-btn p411-btn--block" href="<?= e_url(base_url('register')) ?>">Add my business &mdash; free</a>
					<a class="p411-btn p411-btn--block p411-btn--ghost" style="margin-top:8px;" href="<?= e_url(base_url('contact-us')) ?>">Ask us about a category</a>
				</div>

				<?php // 295x600. Held back until sold — an unsold half-page house ad
				      // does more harm than the zone is worth. ?>
				<?php $slot = $ad_slots['side_sky'];
				$slot_fallback = false;
				include APPPATH . 'views/partials/ad_slot.php'; ?>

			</aside>
		</div>

		<!-- ==================================================== BOTTOM AD -->
		<div class="p411-adrow" style="margin-top:6px;">
			<?php $slot = $ad_slots['bottom'];
			include APPPATH . 'views/partials/ad_slot.php'; ?>
		</div>
	</div>

	<!-- ================================================== OWNER BAND -->
	<div class="p411-owner">
		<div class="container">
			<div class="p411-owner__head">
				<h2>Get found in your category</h2>
				<p>A free listing puts you on your category page and in search. When you want more, we can put you at the top of it.</p>
			</div>
			<div class="p411-feat4">
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 11.5 12 4l9 7.5"/><path d="M5.5 10.5V20h13v-9.5"/><path d="M10 20v-5h4v5"/></svg></i>
					<h3>List your business</h3>
					<p>Free, and it appears on your category page the moment it is approved.</p>
				</div>
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 2.6 5.6 6.4.8-4.6 4.3 1.1 6.3L12 17l-5.5 3 1.1-6.3L3 9.4l6.4-.8z"/></svg></i>
					<h3>Get to the top</h3>
					<p>Featured placement pins you above every other business in your category.</p>
				</div>
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="14" rx="2"/><path d="M8 22h8M12 18v4"/></svg></i>
					<h3>Websites &amp; local SEO</h3>
					<p>Fast websites. Better rankings. More local customers.</p>
				</div>
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 15h10M7 11h6"/></svg></i>
					<h3>Advertise here</h3>
					<p>Category banners across the site, starting at $20 a month.</p>
				</div>
			</div>
			<div class="p411-owner__cta">
				<a class="p411-btn p411-btn--lg" href="<?= e_url(base_url('register')) ?>">Add my business &mdash; free</a>
				<a class="p411-btn p411-btn--lg p411-btn--ghost" href="<?= e_url(base_url('pricing-plan')) ?>">See all packages</a>
			</div>
		</div>
	</div>

</section>

<script>
/* ---------------------------------------------------------------------------
 * Filter the category index in the browser.
 *
 * The whole index is already in the page, so filtering is a walk over the DOM
 * — no request, no reload, and it searches every category instead of the
 * twenty that used to be on screen. The <form> still submits for visitors
 * without JavaScript; the controller runs the same match in SQL.
 * ------------------------------------------------------------------------- */
(function () {
	var input = document.getElementById('catFilter');
	if (!input) return;

	var form   = input.form;
	var count  = document.getElementById('catFilterCount');
	var none   = document.getElementById('catFilterNone');
	var groups = Array.prototype.slice.call(document.querySelectorAll('[data-cat-group]'));
	var blocks = Array.prototype.slice.call(document.querySelectorAll('[data-cat-block]'));
	var bar    = document.querySelector('.p411-azbar');

	// Read the names once. Re-reading textContent on every keystroke is what
	// makes this kind of filter feel slow on a long list.
	var rows = Array.prototype.slice.call(document.querySelectorAll('.p411-azlist > li')).map(function (li) {
		return { el: li, name: li.getAttribute('data-cat-name') || '' };
	});
	var tiles = Array.prototype.slice.call(document.querySelectorAll('.p411-cats [data-cat-name]')).map(function (a) {
		return { el: a, name: a.getAttribute('data-cat-name') || '' };
	});

	function apply(term) {
		term = term.trim().toLowerCase();
		var showing = 0;

		for (var i = 0; i < rows.length; i++) {
			var hit = term === '' || rows[i].name.indexOf(term) !== -1;
			rows[i].el.hidden = !hit;
			if (hit) showing++;
		}
		for (var t = 0; t < tiles.length; t++) {
			tiles[t].el.hidden = term !== '' && tiles[t].name.indexOf(term) === -1;
		}

		// Hide a letter whose rows have all gone, and the "most businesses"
		// block when nothing in it survives.
		for (var g = 0; g < groups.length; g++) {
			groups[g].hidden = !groups[g].querySelector('li:not([hidden])');
		}
		for (var b = 0; b < blocks.length; b++) {
			blocks[b].hidden = term !== '' && !blocks[b].querySelector('[data-cat-name]:not([hidden])');
		}
		if (bar)  bar.hidden  = term !== '';
		if (none) none.hidden = showing !== 0;
		if (count) {
			count.textContent = term === ''
				? ''
				: showing + (showing === 1 ? ' match' : ' matches') + ' for \u201c' + term + '\u201d';
		}
	}

	input.addEventListener('input', function () { apply(this.value); });

	// With JavaScript running there is nothing to submit to — the answer is
	// already on the page.
	if (form) form.addEventListener('submit', function (e) { e.preventDefault(); apply(input.value); });

	if (input.value) apply(input.value);
}());
</script>
