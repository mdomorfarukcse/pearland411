<?php
/**
 * /videos — the Pearland411 video library.
 *
 * Same shell as the homepage and the directory: a hero band, a category strip,
 * then a main column plus a right-hand sidebar. Styling is in
 * assets/css/directory.css (the shared kit) + assets/css/content.css, linked
 * through $page_css; the ~250 lines of inline <style> and the hand-copied
 * marketing card the previous version carried are gone.
 *
 * From User::videos(): $all_videos, $pagination_links, $search_query,
 * $video_categories, $current_category_slug, $current_category_name,
 * $total_videos, $per_page, $current_page, $total_pages, $current_offset,
 * $popular_videos, $ad_slots, $user.
 */

$all_videos       = (isset($all_videos)       && is_array($all_videos))       ? $all_videos       : array();
$popular_videos   = (isset($popular_videos)   && is_array($popular_videos))   ? $popular_videos   : array();
$video_categories = (isset($video_categories) && is_array($video_categories)) ? $video_categories : array();

$search_query   = isset($search_query)   ? (string) $search_query : '';
$total_videos   = isset($total_videos)   ? (int) $total_videos   : count($all_videos);
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

$vd_placeholder = base_url('assets/images/placeholder-listing.svg');

/** Watch URL. video_slug is the canonical key; fall back to the id. */
$vd_url = function ($video) {
	$key = !empty($video->video_slug) ? (string) $video->video_slug : (string) ($video->video_id ?? '');
	return base_url('video-details/' . rawurlencode($key));
};

/**
 * Card artwork.
 *
 * An uploaded still wins. Otherwise fall back to YouTube's own thumbnail for
 * the video id, which is why video_code is in the select list now: half the
 * library had no image and rendered a broken-image box.
 */
$vd_thumb = function ($video) {
	if (!empty($video->video_image)) return array('src' => base_url($video->video_image), 'local' => true);
	$yt = youtube_thumb_url($video->video_code ?? '');
	return array('src' => $yt, 'local' => false);
};

$vd_cat_slug = function ($cat) {
	if (!empty($cat->category_slug)) return (string) $cat->category_slug;
	if (!empty($cat->category_name)) return pearland_slug($cat->category_name);
	return isset($cat->category_id) ? (string) $cat->category_id : '';
};

/** Index URL for a category, keeping any search term. */
$vd_cat_url = function ($slug) use ($search_query) {
	$qs = array();
	if ($slug !== '')         $qs['category'] = $slug;
	if ($search_query !== '') $qs['q']        = $search_query;
	return base_url('videos') . ($qs ? '?' . http_build_query($qs) : '');
};

$showing_from = $total_videos > 0 ? $current_offset + 1 : 0;
$showing_to   = $current_offset + count($all_videos);
?>

<section class="p411-ct p411-ct--videos">

	<!-- ============================================================ HERO -->
	<div class="p411-hero">
		<div class="p411-hero__bg" style="background-image:url('<?= base_url('assets/images/home-bg2.jpg') ?>');" aria-hidden="true"></div>
		<div class="container">
			<nav class="p411-crumb" aria-label="Breadcrumb">
				<a href="<?= base_url() ?>">Home</a>
				<span aria-hidden="true">/</span>
				<?php if ($cat_slug !== ''): ?>
					<a href="<?= e_url(base_url('videos')) ?>">Videos</a>
					<span aria-hidden="true">/</span>
					<span class="is-current"><?= e($cat_name !== '' ? $cat_name : ucwords(str_replace('-', ' ', $cat_slug))) ?></span>
				<?php else: ?>
					<span class="is-current">Videos</span>
				<?php endif; ?>
			</nav>

			<h1><?= $cat_slug !== '' ? e($cat_name !== '' ? $cat_name : ucwords(str_replace('-', ' ', $cat_slug))) : 'Pearland on video' ?></h1>
			<p class="p411-hero__lede">
				Reports, walk-throughs and community clips from around Pearland, TX &mdash; watch them
				here without leaving the page.
			</p>

			<div class="p411-hero__stats">
				<div><b><?= number_format($total_videos) ?></b><?= $cat_slug !== '' ? 'in this channel' : 'videos to watch' ?></div>
				<?php if (!empty($video_categories)): ?>
					<div><b><?= number_format(count($video_categories)) ?></b>channel<?= count($video_categories) === 1 ? '' : 's' ?></div>
				<?php endif; ?>
				<div><b>Free</b>to watch</div>
			</div>

			<div class="p411-ct-hero__search">
				<form action="<?= base_url('videos') ?>" method="get" role="search">
					<?php if ($cat_slug !== ''): ?>
						<input type="hidden" name="category" value="<?= e_attr($cat_slug) ?>">
					<?php endif; ?>
					<div class="p411-ct-search">
						<label class="sr-only" for="videos-search">Search videos</label>
						<input type="search" name="q" id="videos-search" placeholder="Search videos by title or description&hellip;"
							   value="<?= e_attr($search_query) ?>" autocomplete="off">
						<?php if ($search_query !== ''): ?>
							<a class="p411-ct-search__clear" href="<?= e_url($vd_cat_url($cat_slug)) ?>" title="Clear search" aria-label="Clear search">
								<i class="material-icons" aria-hidden="true">close</i>
							</a>
						<?php endif; ?>
						<button type="submit" aria-label="Search videos">
							<i class="material-icons" aria-hidden="true">search</i>
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<!-- ================================================== CATEGORY STRIP -->
	<?php if (!empty($video_categories)): ?>
		<div class="p411-catstrip">
			<div class="container">
				<div class="p411-cats">
					<a href="<?= e_url($vd_cat_url('')) ?>" class="<?= $cat_slug === '' ? 'is-active' : '' ?>">
						<b>All videos</b>
						<span><?= $cat_slug === '' ? number_format($total_videos) . ' videos' : 'Everything' ?></span>
					</a>
					<?php foreach (array_slice($video_categories, 0, 7) as $cat): ?>
						<?php $slug_here = $vd_cat_slug($cat); ?>
						<a href="<?= e_url($vd_cat_url($slug_here)) ?>" class="<?= $slug_here === $cat_slug ? 'is-active' : '' ?>">
							<b><?= e($cat->category_name) ?></b>
							<?php if (isset($cat->item_count)): ?>
								<span><?= (int) $cat->item_count ?> video<?= ((int) $cat->item_count === 1 ? '' : 's') ?></span>
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

				<?php if (!empty($all_videos)): ?>
					<div class="p411-toolbar">
						<div class="p411-toolbar__count">
							Showing <b><?= number_format($showing_from) ?>&ndash;<?= number_format($showing_to) ?></b>
							of <b><?= number_format($total_videos) ?></b> video<?= $total_videos === 1 ? '' : 's' ?>
							<span class="p411-toolbar__meta">
								<?= $cat_slug !== '' ? e($cat_name !== '' ? $cat_name : $cat_slug) . ' &middot; ' : '' ?>Newest first
								<?php if ($total_pages > 1): ?>
									&middot; page <?= (int) $current_page ?> of <?= number_format($total_pages) ?>
								<?php endif; ?>
							</span>
						</div>
					</div>

					<div class="p411-ct-grid">
						<?php foreach ($all_videos as $index => $video): ?>
							<?php
							$detail_url = $vd_url($video);
							$date_ts    = !empty($video->created_at) ? strtotime((string) $video->created_at) : false;
							$thumb      = $vd_thumb($video);
							$excerpt    = content_excerpt($video->video_description ?? '', 110);
							?>
							<article class="p411-ct-card">
								<div class="p411-ct-card__media">
									<a class="p411-ct-card__link" href="<?= e_url($detail_url) ?>" aria-label="Watch <?= e_attr($video->video_title ?? 'this video') ?>">
										<?php if ($thumb['local']): ?>
											<?= responsive_img($video->video_image, (string) ($video->video_title ?? '')) ?>
										<?php elseif ($thumb['src'] !== ''): ?>
											<img src="<?= e_attr($thumb['src']) ?>" alt="" loading="lazy" decoding="async">
										<?php else: ?>
											<img src="<?= e_attr($vd_placeholder) ?>" alt="" loading="lazy" decoding="async">
										<?php endif; ?>
									</a>

									<span class="p411-ct-card__play" aria-hidden="true">
										<i>
											<svg viewBox="0 0 24 24" role="presentation"><path fill="#fff" d="M8 5v14l11-7z"/></svg>
										</i>
									</span>

									<?php if (!empty($video->category_name)): ?>
										<a class="p411-flag" style="top:auto;bottom:10px;" href="<?= e_url($vd_cat_url($vd_cat_slug($video))) ?>"><?= e($video->category_name) ?></a>
									<?php endif; ?>
								</div>

								<div class="p411-ct-card__body">
									<h3><a href="<?= e_url($detail_url) ?>"><?= e($video->video_title ?? '') ?></a></h3>
									<?php if ($excerpt !== ''): ?>
										<p class="p411-ct-excerpt"><?= e($excerpt) ?></p>
									<?php endif; ?>
								</div>

								<div class="p411-ct-card__foot">
									<div class="p411-ct-meta p411-ct-meta--inline">
										<?php if ($date_ts): ?>
											<span><i class="material-icons" aria-hidden="true">event</i><em><?= e(date('j M Y', $date_ts)) ?></em></span>
										<?php endif; ?>
										<?php if (!empty($video->views)): ?>
											<span><i class="material-icons" aria-hidden="true">visibility</i><em><?= number_format((int) $video->views) ?></em></span>
										<?php endif; ?>
									</div>
									<a class="p411-btn p411-btn--sm" href="<?= e_url($detail_url) ?>">Watch</a>
								</div>
							</article>

							<?php if ($index === 5 && count($all_videos) > 6): ?>
								<div class="p411-adrow">
									<?php $slot = $ad_slots['inline'];
									include APPPATH . 'views/partials/ad_slot.php'; ?>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>

					<?php if (!empty($pagination_links)): ?>
						<nav class="p411-pager" aria-label="Video pages">
							<?= $pagination_links ?>
							<p class="p411-pager__hint">Page <?= (int) $current_page ?> of <?= number_format($total_pages) ?></p>
						</nav>
					<?php endif; ?>

				<?php else: ?>
					<!-- ======================================= EMPTY STATE -->
					<div class="p411-empty">
						<div class="p411-empty__icon">
							<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<rect x="2" y="5" width="20" height="14" rx="2"></rect>
								<path d="m10 9 5 3-5 3z"></path>
							</svg>
						</div>
						<?php if ($search_query !== ''): ?>
							<h3>No videos match &ldquo;<?= e($search_query) ?>&rdquo;</h3>
							<p>Try a shorter phrase, or browse a channel from the strip above.</p>
							<div class="p411-empty__actions">
								<a class="p411-btn" href="<?= e_url(base_url('videos')) ?>">Browse all videos</a>
								<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('news')) ?>">Read the news instead</a>
							</div>
						<?php elseif ($cat_slug !== ''): ?>
							<h3>Nothing in this channel yet</h3>
							<p>No videos have been filed under <?= e($cat_name !== '' ? $cat_name : $cat_slug) ?>. The rest of the library is still there.</p>
							<div class="p411-empty__actions">
								<a class="p411-btn" href="<?= e_url(base_url('videos')) ?>">Watch everything</a>
							</div>
						<?php else: ?>
							<h3>No videos published yet</h3>
							<p>The library is empty at the moment. Check back soon &mdash; or send us something worth filming.</p>
							<div class="p411-empty__actions">
								<a class="p411-btn" href="<?= e_url(base_url('contact-us')) ?>">Get in touch</a>
								<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('news')) ?>">Read the news</a>
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
				$slot_creative = 'house-ad';
				include APPPATH . 'views/partials/ad_slot.php'; ?>

				<?php include APPPATH . 'views/partials/service_promo.php'; ?>

				<!-- Most watched -->
				<?php if (!empty($popular_videos)): ?>
					<div class="p411-card">
						<div class="p411-card__head">
							<h2>Most watched</h2>
						</div>
						<div class="p411-rail">
							<?php foreach ($popular_videos as $item): ?>
								<?php $item_thumb = $vd_thumb($item); ?>
								<a class="p411-rail__item" href="<?= e_url($vd_url($item)) ?>">
									<?php if ($item_thumb['src'] !== ''): ?>
										<span class="p411-rail__thumb"><img src="<?= e_attr($item_thumb['src']) ?>" alt="" loading="lazy" decoding="async"></span>
									<?php else: ?>
										<span class="p411-rail__thumb" style="background:var(--p411-navy);" aria-hidden="true">&#9654;</span>
									<?php endif; ?>
									<span class="p411-rail__body">
										<b><?= e($item->video_title ?? '') ?></b>
										<span>
											<?= !empty($item->created_at) ? e(date('j M Y', strtotime((string) $item->created_at))) : '' ?>
											<?= !empty($item->views) ? ' &middot; ' . number_format((int) $item->views) . ' views' : '' ?>
										</span>
									</span>
									<span class="p411-rail__go" aria-hidden="true"><i class="material-icons">chevron_right</i></span>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<!-- Channels -->
				<?php if (!empty($video_categories)): ?>
					<div class="p411-card">
						<div class="p411-card__head">
							<h2>Channels</h2>
						</div>
						<ul class="p411-catlist">
							<li>
								<a href="<?= e_url($vd_cat_url('')) ?>" class="<?= $cat_slug === '' ? 'is-active' : '' ?>">All videos<em><?= number_format($total_videos) ?></em></a>
							</li>
							<?php foreach (array_slice($video_categories, 0, 10) as $cat): ?>
								<?php $slug_here = $vd_cat_slug($cat); ?>
								<li>
									<a href="<?= e_url($vd_cat_url($slug_here)) ?>" class="<?= $slug_here === $cat_slug ? 'is-active' : '' ?>">
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
				<h2>Want your business on video?</h2>
				<p>A short film of what you do, hosted here and on your own website &mdash; plus the site to put it on.</p>
			</div>
			<div class="p411-feat4">
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="m10 9 5 3-5 3z"/></svg></i>
					<h3>Video for your listing</h3>
					<p>Paid packages let you put a video on your business page.</p>
				</div>
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="14" rx="2"/><path d="M8 22h8M12 18v4"/></svg></i>
					<h3>Websites &amp; local SEO</h3>
					<p>Fast, mobile-first sites built and hosted for Pearland businesses.</p>
				</div>
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 15h10M7 11h6"/></svg></i>
					<h3>Advertise here</h3>
					<p>Banner positions on this page and across the site<?php $p411_from = ad_price_from(); echo $p411_from !== '' ? ', from ' . e($p411_from) . ' a month' : ''; ?>.</p>
				</div>
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V9l7-5 7 5v12"/><path d="M10 21v-6h4v6"/></svg></i>
					<h3>List your business</h3>
					<p>A free listing puts you in search and on your category page.</p>
				</div>
			</div>
			<div class="p411-owner__cta">
				<a class="p411-btn" href="<?= e_url(base_url('pricing-plan')) ?>">See all packages</a>
				<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('register')) ?>">Add my business &mdash; free</a>
				<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('contact-us')) ?>">Talk to us</a>
			</div>
		</div>
	</div>
</section>
