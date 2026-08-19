<?php
/**
 * /all-blog — the community blog index.
 *
 * Rebuilt on the shared page kit (assets/css/directory.css + content.css
 * through $page_css), the same shell as /news, /event and /videos: hero band,
 * main column, right-hand sidebar, ad zones. It was the last public content
 * section still on the theme's own `blog-head` / `blog-body` markup — a bare
 * heading over a plain grid with inline `padding-bottom: 170px`, no hero, no
 * sidebar and no ad slots — which is why it did not look like the rest of the
 * site.
 *
 * Dates go through us_date() (view_helper.php). The old call was
 * `date('d, M Y', strtotime($blog->created_at))`, which printed "05, Aug 2026"
 * for a real date and the epoch for a missing one — so every post that had no
 * date recorded showed the same one. See migration 0021_blog_dates.sql.
 *
 * From User::all_blog(): $AllBlog, $pagination_links, $search_query,
 * $total_blogs, $per_page, $current_page, $total_pages, $current_offset,
 * $popular_blogs, $ad_slots.
 */

$AllBlog       = (isset($AllBlog)       && is_array($AllBlog))       ? $AllBlog       : array();
$popular_blogs = (isset($popular_blogs) && is_array($popular_blogs)) ? $popular_blogs : array();

$search_query   = isset($search_query)   ? (string) $search_query : '';
$total_blogs    = isset($total_blogs)    ? (int) $total_blogs    : count($AllBlog);
$current_page   = isset($current_page)   ? (int) $current_page   : 1;
$total_pages    = isset($total_pages)    ? (int) $total_pages    : 1;
$current_offset = isset($current_offset) ? (int) $current_offset : 0;

$ad_slots = (isset($ad_slots) && is_array($ad_slots)) ? $ad_slots : array(
	'top'      => 'top-banner-site-wide',
	'inline'   => 'middle-banner-category',
	'side_1'   => 'right-side-banner-1',
	'side_2'   => 'right-side-banner-2',
	'side_sky' => 'right-panel-skyscraper',
	'bottom'   => 'bottom-banner-site-wide',
);

$bl_placeholder = base_url('assets/images/placeholder-listing.svg');

/** Post URL. blog_slug is the key the detail route reads. */
$bl_url = function ($blog) {
	$key = !empty($blog->blog_slug) ? (string) $blog->blog_slug : (string) ($blog->blog_id ?? '');
	return base_url('blog-details/' . rawurlencode($key));
};

$showing_from = $total_blogs > 0 ? $current_offset + 1 : 0;
$showing_to   = $current_offset + count($AllBlog);

// The lead post only earns the extra space on an unfiltered first page with
// something behind it; otherwise every card is the same size.
$has_lead = ($current_page === 1 && $search_query === '' && count($AllBlog) > 2);
?>

<section class="p411-ct p411-ct--blog">

	<!-- ============================================================ HERO -->
	<div class="p411-hero">
		<div class="container">
			<nav class="p411-crumb" aria-label="Breadcrumb">
				<a href="<?= base_url() ?>">Home</a>
				<span aria-hidden="true">/</span>
				<span class="is-current">Blog</span>
			</nav>

			<h1>The Pearland411 blog</h1>
			<p class="p411-hero__lede">
				Guides, advice and stories from the businesses and people around Pearland, TX &mdash;
				written by the community, free to read.
			</p>

			<div class="p411-hero__stats">
				<div><b><?= number_format($total_blogs) ?></b>post<?= $total_blogs === 1 ? '' : 's' ?> published</div>
				<div><b>Local</b>writers only</div>
				<div><b>Free</b>to read, always</div>
			</div>

			<div class="p411-ct-hero__search">
				<form action="<?= base_url('all-blog') ?>" method="get" role="search">
					<div class="p411-ct-search">
						<label class="sr-only" for="blog-search">Search blog posts</label>
						<input type="search" name="q" id="blog-search" placeholder="Search posts by title or subject&hellip;"
							   value="<?= e_attr($search_query) ?>" autocomplete="off">
						<?php if ($search_query !== ''): ?>
							<a class="p411-ct-search__clear" href="<?= e_url(base_url('all-blog')) ?>" title="Clear search" aria-label="Clear search">
								<i class="material-icons" aria-hidden="true">close</i>
							</a>
						<?php endif; ?>
						<button type="submit" aria-label="Search blog posts">
							<i class="material-icons" aria-hidden="true">search</i>
						</button>
					</div>
				</form>
			</div>
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

				<?php if (!empty($AllBlog)): ?>
					<div class="p411-toolbar">
						<div class="p411-toolbar__count">
							Showing <b><?= number_format($showing_from) ?>&ndash;<?= number_format($showing_to) ?></b>
							of <b><?= number_format($total_blogs) ?></b> post<?= $total_blogs === 1 ? '' : 's' ?>
							<span class="p411-toolbar__meta">
								<?= $search_query !== '' ? 'Matching &ldquo;' . e($search_query) . '&rdquo; &middot; ' : '' ?>Newest first
								<?php if ($total_pages > 1): ?>
									&middot; page <?= (int) $current_page ?> of <?= number_format($total_pages) ?>
								<?php endif; ?>
							</span>
						</div>
					</div>

					<?php
					$lead = $has_lead ? array_slice($AllBlog, 0, 1) : array();
					$rest = $has_lead ? array_slice($AllBlog, 1)    : $AllBlog;
					?>

					<?php foreach ($lead as $blog): ?>
						<?php
						$detail_url = $bl_url($blog);
						$date_txt   = us_date($blog->created_at ?? null);
						$excerpt    = content_excerpt($blog->blog_description ?? '', 220);
						?>
						<article class="p411-ct-card p411-ct-card--lead">
							<div class="p411-ct-card__media">
								<a class="p411-ct-card__link" href="<?= e_url($detail_url) ?>" tabindex="-1" aria-hidden="true">
									<?php if (!empty($blog->blog_image)): ?>
										<?= responsive_img($blog->blog_image, (string) ($blog->blog_name ?? ''), array('fetchpriority' => 'high')) ?>
									<?php else: ?>
										<img src="<?= e_attr($bl_placeholder) ?>" alt="" loading="lazy" decoding="async">
									<?php endif; ?>
								</a>
								<span class="p411-flag p411-flag--featured">Latest</span>
							</div>
							<div style="display:flex;flex-direction:column;">
								<div class="p411-ct-card__body">
									<h3><a href="<?= e_url($detail_url) ?>"><?= e($blog->blog_name ?? '') ?></a></h3>
									<?php if ($excerpt !== ''): ?>
										<p class="p411-ct-excerpt"><?= e($excerpt) ?></p>
									<?php endif; ?>
								</div>
								<div class="p411-ct-card__foot">
									<div class="p411-ct-meta p411-ct-meta--inline">
										<?php if ($date_txt !== ''): ?>
											<span><i class="material-icons" aria-hidden="true">event</i><em><?= e($date_txt) ?></em></span>
										<?php endif; ?>
										<?php if (!empty($blog->views)): ?>
											<span><i class="material-icons" aria-hidden="true">visibility</i><em><?= number_format((int) $blog->views) ?> reads</em></span>
										<?php endif; ?>
									</div>
									<a class="p411-btn p411-btn--sm" href="<?= e_url($detail_url) ?>">Read the post</a>
								</div>
							</div>
						</article>
					<?php endforeach; ?>

					<div class="p411-ct-grid">
						<?php foreach ($rest as $index => $blog): ?>
							<?php
							$detail_url = $bl_url($blog);
							$date_ts    = us_date_ts($blog->created_at ?? null);
							$excerpt    = content_excerpt($blog->blog_description ?? '', 120);
							?>
							<article class="p411-ct-card">
								<div class="p411-ct-card__media">
									<a class="p411-ct-card__link" href="<?= e_url($detail_url) ?>" tabindex="-1" aria-hidden="true">
										<?php if (!empty($blog->blog_image)): ?>
											<?= responsive_img($blog->blog_image, (string) ($blog->blog_name ?? '')) ?>
										<?php else: ?>
											<img src="<?= e_attr($bl_placeholder) ?>" alt="" loading="lazy" decoding="async">
										<?php endif; ?>
									</a>
									<?php if ($date_ts): ?>
										<span class="p411-ct-card__date">
											<span><?= e(date('M', $date_ts)) ?></span>
											<b><?= e(date('d', $date_ts)) ?></b>
										</span>
									<?php endif; ?>
								</div>

								<div class="p411-ct-card__body">
									<h3><a href="<?= e_url($detail_url) ?>"><?= e($blog->blog_name ?? '') ?></a></h3>
									<?php if ($excerpt !== ''): ?>
										<p class="p411-ct-excerpt"><?= e($excerpt) ?></p>
									<?php endif; ?>
								</div>

								<div class="p411-ct-card__foot">
									<div class="p411-ct-meta p411-ct-meta--inline">
										<?php if ($date_ts): ?>
											<span><i class="material-icons" aria-hidden="true">event</i><em><?= e(us_date($date_ts)) ?></em></span>
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

					<?php
					$pager_label       = 'Blog pages';
					$pager_links       = isset($pagination_links) ? $pagination_links : '';
					$pager_current     = isset($current_page) ? $current_page : 1;
					$pager_total       = isset($total_pages) ? $total_pages : 0;
					$pager_jump_action = isset($pager_jump_action) ? $pager_jump_action : '';
					$pager_jump_hidden = isset($pager_jump_hidden) ? $pager_jump_hidden : array();
					include APPPATH . 'views/partials/pager.php';
					?>

				<?php else: ?>
					<!-- ======================================= EMPTY STATE -->
					<div class="p411-empty">
						<div class="p411-empty__icon">
							<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<path d="M4 4h16v16H4z"></path><path d="M8 8h8M8 12h8M8 16h5"></path>
							</svg>
						</div>
						<?php if ($search_query !== ''): ?>
							<h3>No posts match &ldquo;<?= e($search_query) ?>&rdquo;</h3>
							<p>Try fewer words, or a subject rather than a phrase.</p>
							<div class="p411-empty__actions">
								<a class="p411-btn" href="<?= e_url(base_url('all-blog')) ?>">Browse all posts</a>
								<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('news')) ?>">Read the news instead</a>
							</div>
						<?php else: ?>
							<h3>No posts published yet</h3>
							<p>Nothing has been written to the blog yet. Check back soon &mdash; or write the first one.</p>
							<div class="p411-empty__actions">
								<a class="p411-btn" href="<?= e_url(base_url('register')) ?>">Start writing</a>
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

				<!-- Most read -->
				<?php if (!empty($popular_blogs)): ?>
					<div class="p411-card">
						<div class="p411-card__head">
							<h2>Most read</h2>
						</div>
						<div class="p411-rail">
							<?php foreach ($popular_blogs as $item): ?>
								<a class="p411-rail__item" href="<?= e_url($bl_url($item)) ?>">
									<?php if (!empty($item->blog_image)): ?>
										<span class="p411-rail__thumb"><?= responsive_img($item->blog_image, (string) ($item->blog_name ?? '')) ?></span>
									<?php else: ?>
										<span class="p411-rail__thumb" style="background:var(--p411-navy);" aria-hidden="true">&bull;</span>
									<?php endif; ?>
									<span class="p411-rail__body">
										<b><?= e($item->blog_name ?? '') ?></b>
										<span>
											<?= e(us_date($item->created_at ?? null)) ?>
											<?= !empty($item->views) ? ' &middot; ' . number_format((int) $item->views) . ' reads' : '' ?>
										</span>
									</span>
									<span class="p411-rail__go" aria-hidden="true"><i class="material-icons">chevron_right</i></span>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<?php $slot = $ad_slots['side_2'];
				$slot_fallback_partial = 'banner_design_promo.php';
				$slot_creative = 'banner-design';
				include APPPATH . 'views/partials/ad_slot.php'; ?>

				<!-- Write for us -->
				<div class="p411-card">
					<div class="p411-card__head">
						<h2>Write for Pearland411</h2>
					</div>
					<p class="p411-form__hint">
						Know something the neighbourhood should? Business owners on Pearland411 can publish
						straight to this page &mdash; no editor, no wait.
					</p>
					<a class="p411-btn p411-btn--block" href="<?= e_url(base_url('user/add_blog')) ?>">Write a post</a>
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
				<p>Pearland411 is where local people look things up. A listing is free; a banner beside the blog costs less than a classified.</p>
			</div>
			<div class="p411-owner__cta">
				<a class="p411-btn" href="<?= e_url(base_url('register')) ?>">Add my business &mdash; free</a>
				<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('pricing-plan')) ?>">See all packages</a>
				<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('contact-us')) ?>">Talk to us</a>
			</div>
		</div>
	</div>
</section>
