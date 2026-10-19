<?php
/**
 * /news — local news and announcements.
 *
 * Same shell as the homepage and the directory: a hero band, a category strip,
 * then a main column plus a right-hand sidebar. Styling is in
 * assets/css/directory.css (the shared kit) + assets/css/content.css, linked
 * through $page_css; the ~220 lines of inline <style> the previous version
 * carried — including a second copy of the sidebar promo card — are gone.
 *
 * From User::news(): $all_news, $pagination_links, $search_query,
 * $news_categories, $current_category_slug, $current_category_name,
 * $total_news, $per_page, $current_page, $total_pages, $current_offset,
 * $popular_news, $ad_slots, $user.
 */

$all_news        = (isset($all_news)        && is_array($all_news))        ? $all_news        : array();
$popular_news    = (isset($popular_news)    && is_array($popular_news))    ? $popular_news    : array();
$news_categories = (isset($news_categories) && is_array($news_categories)) ? $news_categories : array();

$search_query   = isset($search_query)   ? (string) $search_query : '';
$total_news     = isset($total_news)     ? (int) $total_news     : count($all_news);
$per_page       = isset($per_page)       ? (int) $per_page       : 10;
$current_page   = isset($current_page)   ? (int) $current_page   : 1;
$total_pages    = isset($total_pages)    ? (int) $total_pages    : 1;
$current_offset = isset($current_offset) ? (int) $current_offset : 0;
$cat_slug       = !empty($current_category_slug) ? (string) $current_category_slug : '';
$cat_name       = !empty($current_category_name) ? (string) $current_category_name : '';

$ad_slots = (isset($ad_slots) && is_array($ad_slots)) ? $ad_slots : array(
	'top'      => 'top-banner-site-wide',
	'inline'   => 'middle-banner-category',
	'side_1'   => 'right-side-banner-1',
	'side_2'   => 'right-side-banner-2',
	'side_sky' => 'right-panel-skyscraper',
	'bottom'   => 'bottom-banner-site-wide',
);

$nw_placeholder = base_url('assets/images/placeholder-listing.svg');

/** Story URL. news_slug is the canonical key; fall back to the id. */
$nw_url = function ($news) {
	$key = !empty($news->news_slug) ? (string) $news->news_slug : (string) ($news->news_id ?? '');
	return base_url('news-details/' . rawurlencode($key));
};

/**
 * Slug for a category row, falling back to a generated one.
 *
 * Admin's category form builds `category_slug` with its own expression, so a
 * stored slug is used verbatim and only a missing one is slugified — the same
 * pair of rules Usermodel::resolveContentCategoryId() reads it back with.
 */
$nw_cat_slug = function ($cat) {
	if (!empty($cat->category_slug)) return (string) $cat->category_slug;
	if (!empty($cat->category_name)) return pearland_slug($cat->category_name);
	return isset($cat->category_id) ? (string) $cat->category_id : '';
};

/** Index URL for a category, keeping any search term. */
$nw_cat_url = function ($slug) use ($search_query) {
	$qs = array();
	if ($slug !== '')         $qs['category'] = $slug;
	if ($search_query !== '') $qs['q']        = $search_query;
	return base_url('news') . ($qs ? '?' . http_build_query($qs) : '');
};

$showing_from = $total_news > 0 ? $current_offset + 1 : 0;
$showing_to   = $current_offset + count($all_news);

// The lead story only earns the extra space on an unfiltered first page with
// something behind it; otherwise every card is the same size.
$has_lead = ($current_page === 1 && $search_query === '' && count($all_news) > 2);
?>

<section class="p411-ct p411-ct--news">

	<!-- ============================================================ HERO -->
	<div class="p411-hero">
		<div class="p411-hero__bg" style="background-image:url('<?= base_url('assets/images/news-bg.jpg') ?>');" aria-hidden="true"></div>
		<div class="container">
			<nav class="p411-crumb" aria-label="Breadcrumb">
				<a href="<?= base_url() ?>">Home</a>
				<span aria-hidden="true">/</span>
				<?php if ($cat_slug !== ''): ?>
					<a href="<?= e_url(base_url('news')) ?>">News</a>
					<span aria-hidden="true">/</span>
					<span class="is-current"><?= e($cat_name !== '' ? $cat_name : ucwords(str_replace('-', ' ', $cat_slug))) ?></span>
				<?php else: ?>
					<span class="is-current">News</span>
				<?php endif; ?>
			</nav>

			<h1><?= $cat_slug !== '' ? e($cat_name !== '' ? $cat_name : ucwords(str_replace('-', ' ', $cat_slug))) : 'Pearland News' ?></h1>
			<p class="p411-hero__lede">
				<?php if ($cat_slug !== ''): ?>
					Everything filed under <?= e(strtolower($cat_name !== '' ? $cat_name : $cat_slug)) ?> &mdash;
					city announcements, business news and what's happening around Pearland.
				<?php else: ?>
					What's happening in Pearland, TX: city announcements, business openings, public safety
					and the stories local people are talking about.
				<?php endif; ?>
			</p>

			<div class="p411-hero__stats">
				<div><b><?= number_format($total_news) ?></b><?= $cat_slug !== '' ? 'in this section' : 'stories published' ?></div>
				<?php if (!empty($news_categories)): ?>
					<div><b><?= number_format(count($news_categories)) ?></b>section<?= count($news_categories) === 1 ? '' : 's' ?></div>
				<?php endif; ?>
				<div><b>Free</b>to read, always</div>
			</div>

			<div class="p411-ct-hero__search">
				<form action="<?= base_url('news') ?>" method="get" role="search">
					<?php if ($cat_slug !== ''): ?>
						<input type="hidden" name="category" value="<?= e_attr($cat_slug) ?>">
					<?php endif; ?>
					<div class="p411-ct-search">
						<label class="sr-only" for="news-search">Search news</label>
						<input type="search" name="q" id="news-search" placeholder="Search news by headline or story&hellip;"
							   value="<?= e_attr($search_query) ?>" autocomplete="off">
						<?php if ($search_query !== ''): ?>
							<a class="p411-ct-search__clear" href="<?= e_url($nw_cat_url($cat_slug)) ?>" title="Clear search" aria-label="Clear search">
								<i class="material-icons" aria-hidden="true">close</i>
							</a>
						<?php endif; ?>
						<button type="submit" aria-label="Search news">
							<i class="material-icons" aria-hidden="true">search</i>
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<!-- ================================================== CATEGORY STRIP -->
	<?php if (!empty($news_categories)): ?>
		<div class="p411-catstrip">
			<div class="container">
				<div class="p411-cats">
					<a href="<?= e_url($nw_cat_url('')) ?>" class="<?= $cat_slug === '' ? 'is-active' : '' ?>">
						<b>All news</b>
						<span><?= $cat_slug === '' ? number_format($total_news) . ' stories' : 'Everything' ?></span>
					</a>
					<?php foreach (array_slice($news_categories, 0, 7) as $cat): ?>
						<?php $slug_here = $nw_cat_slug($cat); ?>
						<a href="<?= e_url($nw_cat_url($slug_here)) ?>" class="<?= $slug_here === $cat_slug ? 'is-active' : '' ?>">
							<b><?= e($cat->category_name) ?></b>
							<?php if (isset($cat->item_count)): ?>
								<span><?= (int) $cat->item_count ?> stor<?= ((int) $cat->item_count === 1 ? 'y' : 'ies') ?></span>
							<?php endif; ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<div class="container">
		<div class="p411-shell">

			<!-- ==================================================== MAIN -->
			<div class="p411-shell__main">

				<div class="p411-adrow">
					<?php $slot = $ad_slots['top'];
					include APPPATH . 'views/partials/ad_slot.php'; ?>
				</div>

				<?php if (!empty($all_news)): ?>
					<div class="p411-toolbar">
						<div class="p411-toolbar__count">
							Showing <b><?= number_format($showing_from) ?>&ndash;<?= number_format($showing_to) ?></b>
							of <b><?= number_format($total_news) ?></b> stor<?= $total_news === 1 ? 'y' : 'ies' ?>
							<span class="p411-toolbar__meta">
								<?= $cat_slug !== '' ? e($cat_name !== '' ? $cat_name : $cat_slug) . ' &middot; ' : '' ?>Newest first
								<?php if ($total_pages > 1): ?>
									&middot; page <?= (int) $current_page ?> of <?= number_format($total_pages) ?>
								<?php endif; ?>
							</span>
						</div>
					</div>

					<?php
					$lead = $has_lead ? array_slice($all_news, 0, 1) : array();
					$rest = $has_lead ? array_slice($all_news, 1)    : $all_news;
					?>

					<?php foreach ($lead as $news): ?>
						<?php
						$detail_url = $nw_url($news);
						$date_ts    = !empty($news->created_at) ? strtotime((string) $news->created_at) : false;
						$excerpt    = content_excerpt($news->news_description ?? '', 220);
						?>
						<article class="p411-ct-card p411-ct-card--lead">
							<div class="p411-ct-card__media">
								<a class="p411-ct-card__link" href="<?= e_url($detail_url) ?>" tabindex="-1" aria-hidden="true">
									<?php if (!empty($news->news_image)): ?>
										<?= responsive_img($news->news_image, (string) ($news->news_title ?? ''), array('fetchpriority' => 'high')) ?>
									<?php else: ?>
										<img src="<?= e_attr($nw_placeholder) ?>" alt="" loading="lazy" decoding="async">
									<?php endif; ?>
								</a>
								<span class="p411-flag p411-flag--featured">Latest</span>
							</div>
							<div style="display:flex;flex-direction:column;">
								<div class="p411-ct-card__body">
									<?php if (!empty($news->category_name)): ?>
										<div class="p411-ct-meta p411-ct-meta--inline">
											<span><i class="material-icons" aria-hidden="true">label_outline</i><em><?= e($news->category_name) ?></em></span>
										</div>
									<?php endif; ?>
									<h3><a href="<?= e_url($detail_url) ?>"><?= e($news->news_title ?? '') ?></a></h3>
									<?php if ($excerpt !== ''): ?>
										<p class="p411-ct-excerpt"><?= e($excerpt) ?></p>
									<?php endif; ?>
								</div>
								<div class="p411-ct-card__foot">
									<div class="p411-ct-meta p411-ct-meta--inline">
										<?php if ($date_ts): ?>
											<span><i class="material-icons" aria-hidden="true">event</i><em><?= e(date('j M Y', $date_ts)) ?></em></span>
										<?php endif; ?>
										<?php if (!empty($news->views)): ?>
											<span><i class="material-icons" aria-hidden="true">visibility</i><em><?= number_format((int) $news->views) ?> reads</em></span>
										<?php endif; ?>
									</div>
									<a class="p411-btn p411-btn--sm" href="<?= e_url($detail_url) ?>">Read the story</a>
								</div>
							</div>
						</article>
					<?php endforeach; ?>

					<div class="p411-ct-grid">
						<?php foreach ($rest as $index => $news): ?>
							<?php
							$detail_url = $nw_url($news);
							$date_ts    = !empty($news->created_at) ? strtotime((string) $news->created_at) : false;
							$excerpt    = content_excerpt($news->news_description ?? '', 120);
							?>
							<article class="p411-ct-card">
								<div class="p411-ct-card__media">
									<a class="p411-ct-card__link" href="<?= e_url($detail_url) ?>" tabindex="-1" aria-hidden="true">
										<?php if (!empty($news->news_image)): ?>
											<?= responsive_img($news->news_image, (string) ($news->news_title ?? '')) ?>
										<?php else: ?>
											<img src="<?= e_attr($nw_placeholder) ?>" alt="" loading="lazy" decoding="async">
										<?php endif; ?>
									</a>
									<?php if ($date_ts): ?>
										<span class="p411-ct-card__date">
											<span><?= e(date('M', $date_ts)) ?></span>
											<b><?= e(date('d', $date_ts)) ?></b>
										</span>
									<?php endif; ?>
									<?php if (!empty($news->category_name)): ?>
										<a class="p411-flag" style="top:auto;bottom:10px;" href="<?= e_url($nw_cat_url($nw_cat_slug($news))) ?>"><?= e($news->category_name) ?></a>
									<?php endif; ?>
								</div>

								<div class="p411-ct-card__body">
									<h3><a href="<?= e_url($detail_url) ?>"><?= e($news->news_title ?? '') ?></a></h3>
									<?php if ($excerpt !== ''): ?>
										<p class="p411-ct-excerpt"><?= e($excerpt) ?></p>
									<?php endif; ?>
								</div>

								<div class="p411-ct-card__foot">
									<div class="p411-ct-meta p411-ct-meta--inline">
										<?php if ($date_ts): ?>
											<span><i class="material-icons" aria-hidden="true">event</i><em><?= e(date('j M Y', $date_ts)) ?></em></span>
										<?php endif; ?>
									</div>
									<a class="p411-btn p411-btn--sm" href="<?= e_url($detail_url) ?>">Read</a>
								</div>
							</article>

							<?php if ($index === 5 && count($rest) > 6): ?>
								<div class="p411-adrow">
									<?php $slot = $ad_slots['inline'];
									include APPPATH . 'views/partials/ad_slot.php'; ?>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>

					<?php if (!empty($pagination_links)): ?>
						<nav class="p411-pager" aria-label="News pages">
							<?= $pagination_links ?>
							<p class="p411-pager__hint">Page <?= (int) $current_page ?> of <?= number_format($total_pages) ?></p>
						</nav>
					<?php endif; ?>

				<?php else: ?>
					<!-- ======================================= EMPTY STATE -->
					<div class="p411-empty">
						<div class="p411-empty__icon">
							<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<path d="M4 5h13a1 1 0 0 1 1 1v12a2 2 0 0 0 2 2H5a1 1 0 0 1-1-1z"></path>
								<path d="M18 8h2a1 1 0 0 1 1 1v9a2 2 0 0 1-2 2M8 9h6M8 13h6M8 17h4"></path>
							</svg>
						</div>
						<?php if ($search_query !== ''): ?>
							<h3>No stories match &ldquo;<?= e($search_query) ?>&rdquo;</h3>
							<p>Try fewer words, or a name rather than a phrase. You can also browse a section from the strip above.</p>
							<div class="p411-empty__actions">
								<a class="p411-btn" href="<?= e_url(base_url('news')) ?>">Browse all news</a>
								<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('event')) ?>">See what's on instead</a>
							</div>
						<?php elseif ($cat_slug !== ''): ?>
							<h3>Nothing published in this section yet</h3>
							<p>No stories have been filed under <?= e($cat_name !== '' ? $cat_name : $cat_slug) ?>. The rest of the newsroom is still there.</p>
							<div class="p411-empty__actions">
								<a class="p411-btn" href="<?= e_url(base_url('news')) ?>">Read all news</a>
							</div>
						<?php else: ?>
							<h3>No news published yet</h3>
							<p>Nothing has been posted to the Pearland411 newsroom. Check back soon &mdash; or tell us what we're missing.</p>
							<div class="p411-empty__actions">
								<a class="p411-btn" href="<?= e_url(base_url('contact-us')) ?>">Send us a story</a>
								<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('event')) ?>">See what's on</a>
							</div>
						<?php endif; ?>
					</div>

					<div class="p411-adrow" style="margin-top:22px;">
						<?php $slot = $ad_slots['inline'];
						include APPPATH . 'views/partials/ad_slot.php'; ?>
					</div>

					<?php $promo_variant = 'banner';
					include APPPATH . 'views/partials/service_promo.php'; ?>
				<?php endif; ?>
			</div>

			<!-- ================================================= SIDEBAR -->
			<aside class="p411-shell__side">

				<?php $slot = $ad_slots['side_1'];
				// 0006 seeded 'news-sidebar' for this position. It matches no
				// package on /pricing-plan, but anything an operator booked on
				// it by hand keeps running here.
				$slot_alias = 'news-sidebar';
				$slot_creative = 'house-ad';
				include APPPATH . 'views/partials/ad_slot.php'; ?>

				<?php include APPPATH . 'views/partials/service_promo.php'; ?>

				<!-- Most read -->
				<?php if (!empty($popular_news)): ?>
					<div class="p411-card">
						<div class="p411-card__head">
							<h2>Most read</h2>
						</div>
						<div class="p411-rail">
							<?php foreach ($popular_news as $item): ?>
								<a class="p411-rail__item" href="<?= e_url($nw_url($item)) ?>">
									<?php if (!empty($item->news_image)): ?>
										<span class="p411-rail__thumb"><?= responsive_img($item->news_image, (string) ($item->news_title ?? '')) ?></span>
									<?php else: ?>
										<span class="p411-rail__thumb" style="background:var(--p411-navy);" aria-hidden="true">&bull;</span>
									<?php endif; ?>
									<span class="p411-rail__body">
										<b><?= e($item->news_title ?? '') ?></b>
										<span>
											<?= !empty($item->created_at) ? e(date('j M Y', strtotime((string) $item->created_at))) : '' ?>
											<?= !empty($item->views) ? ' &middot; ' . number_format((int) $item->views) . ' reads' : '' ?>
										</span>
									</span>
									<span class="p411-rail__go" aria-hidden="true"><i class="material-icons">chevron_right</i></span>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<!-- Sections -->
				<?php if (!empty($news_categories)): ?>
					<div class="p411-card">
						<div class="p411-card__head">
							<h2>Sections</h2>
						</div>
						<ul class="p411-catlist">
							<li>
								<a href="<?= e_url($nw_cat_url('')) ?>" class="<?= $cat_slug === '' ? 'is-active' : '' ?>">All news<em><?= number_format($total_news) ?></em></a>
							</li>
							<?php foreach (array_slice($news_categories, 0, 10) as $cat): ?>
								<?php $slug_here = $nw_cat_slug($cat); ?>
								<li>
									<a href="<?= e_url($nw_cat_url($slug_here)) ?>" class="<?= $slug_here === $cat_slug ? 'is-active' : '' ?>">
										<?= e($cat->category_name) ?>
										<?php if (isset($cat->item_count)): ?><em><?= (int) $cat->item_count ?></em><?php endif; ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<?php $slot = $ad_slots['side_2'];
				$slot_alias = array('right-banner-1', 'right-banner-2');
				$slot_fallback_partial = 'banner_design_promo.php';
				$slot_creative = 'banner-design';
				include APPPATH . 'views/partials/ad_slot.php'; ?>

				<!-- Got a story -->
				<div class="p411-card">
					<div class="p411-card__head">
						<h2>Got a story?</h2>
					</div>
					<p class="p411-form__hint">
						Opening, closing, moving, hiring, fundraising &mdash; if it matters to Pearland we'll cover it.
						Send us the details and we'll come back to you.
					</p>
					<a class="p411-btn p411-btn--block" href="<?= e_url(base_url('contact-us')) ?>">Send us a story</a>
				</div>

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
				<h2>Reach the people reading this</h2>
				<p>Pearland411 is where local people look things up. A listing is free; a banner beside the news costs less than a classified.</p>
			</div>
			<div class="p411-feat4">
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V9l7-5 7 5v12"/><path d="M10 21v-6h4v6"/></svg></i>
					<h3>List your business</h3>
					<p>A free listing puts you in search and on your category page.</p>
				</div>
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 15h10M7 11h6"/></svg></i>
					<h3>Advertise here</h3>
					<p>Banner positions on this page and across the site<?php $p411_from = ad_price_from(); echo $p411_from !== '' ? ', from ' . e($p411_from) . ' a month' : ''; ?>.</p>
				</div>
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="14" rx="2"/><path d="M8 22h8M12 18v4"/></svg></i>
					<h3>Websites &amp; local SEO</h3>
					<p>Fast, mobile-first sites built and hosted for Pearland businesses.</p>
				</div>
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5h13a1 1 0 0 1 1 1v12a2 2 0 0 0 2 2H5a1 1 0 0 1-1-1z"/><path d="M8 9h6M8 13h6"/></svg></i>
					<h3>Tell us your news</h3>
					<p>Send a story and we'll write it up for the community.</p>
				</div>
			</div>
			<div class="p411-owner__cta">
				<a class="p411-btn" href="<?= e_url(base_url('register')) ?>">Add my business &mdash; free</a>
				<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('pricing-plan')) ?>">See all packages</a>
				<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('contact-us')) ?>">Talk to us</a>
			</div>
		</div>
	</div>
</section>
