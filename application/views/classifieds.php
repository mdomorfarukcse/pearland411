<?php
/**
 * /classifieds — the local small ads.
 *
 * Same shell as the directory, events, news and videos: a hero band, a filter
 * strip, then a main column plus a right-hand sidebar. Styling comes from
 * assets/css/directory.css (the shared kit) + assets/css/content.css via
 * $page_css; there is no <style> block here — the previous version carried
 * three of them, ~420 lines in total, including its own copy of the pagination
 * rules the kit already provides.
 *
 * From User::classifieds(): $all_classifieds, $pagination_links,
 * $search_query, $total_classifieds, $per_page, $current_page, $total_pages,
 * $current_offset, $classifieds_categories, $ad_slots. All defaulted below.
 */

$all_classifieds        = (isset($all_classifieds)        && is_array($all_classifieds))        ? $all_classifieds        : array();
$classifieds_categories = (isset($classifieds_categories) && is_array($classifieds_categories)) ? $classifieds_categories : array();

$search_query      = isset($search_query)      ? (string) $search_query : '';
$total_classifieds = isset($total_classifieds) ? (int) $total_classifieds : count($all_classifieds);
$per_page          = isset($per_page)          ? (int) $per_page          : 10;
$current_page      = isset($current_page)      ? (int) $current_page      : 1;
$total_pages       = isset($total_pages)       ? (int) $total_pages       : 1;
$current_offset    = isset($current_offset)    ? (int) $current_offset    : 0;
$pagination_links  = isset($pagination_links)  ? $pagination_links        : '';

$ad_slots = (isset($ad_slots) && is_array($ad_slots)) ? $ad_slots : array(
	'top'      => 'top-banner-site-wide',
	'inline'   => 'middle-banner-category',
	'side_1'   => 'right-side-banner-1',
	'side_2'   => 'right-side-banner-2',
	'side_sky' => 'right-panel-skyscraper',
	'bottom'   => 'bottom-banner-site-wide',
);

$cl_placeholder = base_url('assets/images/placeholders/general.svg');
$cl_post_url    = base_url('user/add_classifieds');

/** Detail URL for an advert, falling back to a slug built from the title. */
$cl_url = function ($item) {
	$slug = !empty($item->classifieds_slug)
		? (string) $item->classifieds_slug
		: pearland_slug($item->classifieds_title ?? '');
	return base_url('classifieds-details/' . rawurlencode($slug));
};

$showing_from = $total_classifieds > 0 ? $current_offset + 1 : 0;
$showing_to   = $current_offset + count($all_classifieds);

// Adverts posted in the last week get a flag — what is new is the whole point
// of a classifieds page.
$fresh_since = strtotime('-7 days');
?>

<section class="p411-ct p411-ct--classifieds">

	<!-- ============================================================ HERO -->
	<div class="p411-hero">
		<div class="p411-hero__bg" style="background-image:url('<?= base_url('assets/images/home/hero-pearland.svg') ?>');" aria-hidden="true"></div>
		<div class="container">
			<nav class="p411-crumb" aria-label="Breadcrumb">
				<a href="<?= base_url() ?>">Home</a>
				<span aria-hidden="true">/</span>
				<span class="is-current">Classifieds</span>
			</nav>

			<h1>Pearland classifieds</h1>
			<p class="p411-hero__lede">
				Buy, sell, rent and hire locally &mdash; vehicles, property, furniture, jobs and services
				posted by people and businesses around Pearland, TX.
			</p>

			<div class="p411-hero__stats">
				<div><b><?= number_format($total_classifieds) ?></b>live classified<?= $total_classifieds === 1 ? '' : 's' ?></div>
				<?php if (!empty($classifieds_categories)): ?>
					<div><b><?= number_format(count($classifieds_categories)) ?></b>categories</div>
				<?php endif; ?>
				<div><b>Free</b>to post yours</div>
			</div>

			<div class="p411-ct-hero__search">
				<form action="<?= base_url('classifieds') ?>" method="get" role="search">
					<div class="p411-ct-search">
						<label class="sr-only" for="classifieds-search">Search classifieds</label>
						<input type="search" name="q" id="classifieds-search"
							   placeholder="Search classifieds &mdash; car, apartment, sofa, job&hellip;"
							   value="<?= e_attr($search_query) ?>" autocomplete="off">
						<?php if ($search_query !== ''): ?>
							<a class="p411-ct-search__clear" href="<?= e_url(base_url('classifieds')) ?>" title="Clear search" aria-label="Clear search">
								<i class="material-icons" aria-hidden="true">close</i>
							</a>
						<?php endif; ?>
						<button type="submit" aria-label="Search classifieds">
							<i class="material-icons" aria-hidden="true">search</i>
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<!-- ==================================================== FILTER STRIP -->
	<div class="p411-ct-filters">
		<div class="container">
			<div class="p411-ct-tabs" role="navigation" aria-label="Browse classifieds">
				<a href="<?= e_url(base_url('classifieds')) ?>" class="<?= $search_query === '' ? 'is-active' : '' ?>"
				   <?= $search_query === '' ? 'aria-current="page"' : '' ?>>
					All classifieds<em><?= number_format($total_classifieds) ?></em>
				</a>
				<?php // The list has no category filter of its own, so these run
				      // through the search the page already supports. ?>
				<?php foreach (array_slice($classifieds_categories, 0, 4) as $cc): ?>
					<a href="<?= e_url(base_url('classifieds') . '?q=' . rawurlencode($cc->category_name)) ?>"
					   class="<?= strcasecmp($search_query, (string) $cc->category_name) === 0 ? 'is-active' : '' ?>">
						<?= e($cc->category_name) ?><em><?= number_format((int) $cc->item_count) ?></em>
					</a>
				<?php endforeach; ?>
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

				<?php if (!empty($all_classifieds)): ?>
					<div class="p411-toolbar">
						<div class="p411-toolbar__count">
							Showing <b><?= number_format($showing_from) ?>&ndash;<?= number_format($showing_to) ?></b>
							of <b><?= number_format($total_classifieds) ?></b> classified<?= $total_classifieds === 1 ? '' : 's' ?>
							<span class="p411-toolbar__meta">
								Newest first
								<?php if ($total_pages > 1): ?>
									&middot; page <?= (int) $current_page ?> of <?= number_format($total_pages) ?>
								<?php endif; ?>
							</span>
						</div>
						<div class="p411-toolbar__right">
							<a class="p411-btn p411-btn--sm p411-btn--ghost" href="<?= e_url($cl_post_url) ?>">Post a Free Classified</a>
						</div>
					</div>

					<div class="p411-ct-grid">
						<?php foreach ($all_classifieds as $index => $item): ?>
							<?php
							$detail_url = $cl_url($item);
							$posted_ts  = !empty($item->created_at) ? strtotime((string) $item->created_at) : false;
							$is_fresh   = $posted_ts && $posted_ts >= $fresh_since;
							$excerpt    = content_excerpt($item->classifieds_description ?? '', 130);
							?>
							<article class="p411-ct-card">
								<div class="p411-ct-card__media">
									<a class="p411-ct-card__link" href="<?= e_url($detail_url) ?>" tabindex="-1" aria-hidden="true">
										<?php if (!empty($item->classifieds_image)): ?>
											<?= responsive_img($item->classifieds_image, (string) ($item->classifieds_title ?? '')) ?>
										<?php else: ?>
											<img src="<?= e_attr($cl_placeholder) ?>" alt="" loading="lazy" decoding="async">
										<?php endif; ?>
									</a>

									<?php if (!empty($item->category_name)): ?>
										<span class="p411-flag"><?= e($item->category_name) ?></span>
									<?php endif; ?>
									<?php if ($is_fresh): ?>
										<span class="p411-flag p411-flag--new">New</span>
									<?php endif; ?>
								</div>

								<div class="p411-ct-card__body">
									<h3><a href="<?= e_url($detail_url) ?>"><?= e($item->classifieds_title ?? '') ?></a></h3>

									<?php if ($excerpt !== ''): ?>
										<p class="p411-ct-excerpt"><?= e($excerpt) ?></p>
									<?php endif; ?>

									<div class="p411-ct-meta">
										<?php if ($posted_ts): ?>
											<span><i class="material-icons" aria-hidden="true">schedule</i><em><?= e(date('j M Y', $posted_ts)) ?></em></span>
										<?php endif; ?>
										<?php if (!empty($item->name)): ?>
											<span><i class="material-icons" aria-hidden="true">person_outline</i><em><?= e($item->name) ?></em></span>
										<?php endif; ?>
									</div>

									<div class="p411-ct-card__foot">
										<a class="p411-btn p411-btn--sm" href="<?= e_url($detail_url) ?>">View details</a>
									</div>
								</div>
							</article>

							<?php // One in-list banner, at the end of the first row — the
							      // grid is three across, so breaking after the fourth card
							      // would strand it alone. Category-priced zone. ?>
							<?php if ($index === 2 && count($all_classifieds) > 3): ?>
								<div class="p411-adrow p411-adrow--grid">
									<?php $slot = $ad_slots['inline'];
									include APPPATH . 'views/partials/ad_slot.php'; ?>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>

					<?php if (!empty($pagination_links)): ?>
						<nav class="p411-pager" aria-label="Classifieds pages"><?= $pagination_links ?></nav>
					<?php endif; ?>

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
							<h3>Nothing matches &ldquo;<?= e($search_query) ?>&rdquo;</h3>
							<p>
								No live classified mentions that. Try a shorter word, or browse everything
								that is posted at the moment.
							</p>
							<div class="p411-empty__actions">
								<a class="p411-btn" href="<?= e_url(base_url('classifieds')) ?>">Browse all classifieds</a>
								<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('all_listing')) ?>">Search local businesses</a>
							</div>
						<?php else: ?>
							<h3>No classifieds posted yet</h3>
							<p>
								Nothing is listed at the moment. Selling something, letting a room or hiring?
								Yours would be the first &mdash; and it is free to post.
							</p>
							<div class="p411-empty__actions">
								<a class="p411-btn" href="<?= e_url($cl_post_url) ?>">Post Your First Classified</a>
								<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('all_listing')) ?>">Browse the directory</a>
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

				<?php if (!empty($classifieds_categories)): ?>
					<div class="p411-card">
						<div class="p411-card__head">
							<h2>Browse by category</h2>
						</div>
						<ul class="p411-catlist">
							<?php foreach ($classifieds_categories as $cc): ?>
								<li>
									<a href="<?= e_url(base_url('classifieds') . '?q=' . rawurlencode($cc->category_name)) ?>">
										<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7.5h16v13H4z"/><path d="M9 7.5V5a3 3 0 0 1 6 0v2.5"/></svg>
										<?= e($cc->category_name) ?>
										<em><?= number_format((int) $cc->item_count) ?></em>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<?php $slot = $ad_slots['side_2'];
				$slot_fallback_partial = 'banner_design_promo.php';
				include APPPATH . 'views/partials/ad_slot.php'; ?>

				<!-- Post a free classified -->
				<div class="p411-card">
					<div class="p411-card__head">
						<h2>Got something to sell?</h2>
					</div>
					<p class="p411-form__hint">
						Post it free and it appears here and in search &mdash; with your photo, your description
						and a way for buyers to reach you.
					</p>
					<a class="p411-btn p411-btn--block" href="<?= e_url($cl_post_url) ?>">Post a Free Classified</a>
					<a class="p411-btn p411-btn--block p411-btn--ghost" style="margin-top:8px;" href="<?= e_url(base_url('coupons')) ?>">See local deals instead</a>
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
				<h2>Selling to Pearland</h2>
				<p>A classified is free. If you do this for a living, a business listing puts you in the directory and in search all year round.</p>
			</div>
			<div class="p411-feat4">
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7.5h16v13H4z"/><path d="M9 7.5V5a3 3 0 0 1 6 0v2.5"/></svg></i>
					<h3>Post it free</h3>
					<p>A photo, a description and your contact details. No fee, no commission.</p>
				</div>
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></i>
					<h3>Found by neighbours</h3>
					<p>People searching Pearland411 are already local. No shipping, no strangers.</p>
				</div>
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2.5" y="7" width="19" height="12.5" rx="2"/><path d="M8.5 7V5.5A1.5 1.5 0 0 1 10 4h4a1.5 1.5 0 0 1 1.5 1.5V7"/><path d="M2.5 12.5h19"/></svg></i>
					<h3>Doing it as a business?</h3>
					<p>List the business itself &mdash; you get a page, a category and a phone number.</p>
				</div>
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 15h10M7 11h6"/></svg></i>
					<h3>Advertise here</h3>
					<p>Banner positions on this page and across the site, from $20 a month.</p>
				</div>
			</div>
			<div class="p411-owner__cta">
				<a class="p411-btn p411-btn--lg" href="<?= e_url($cl_post_url) ?>">Post a Free Classified</a>
				<a class="p411-btn p411-btn--lg p411-btn--ghost" href="<?= e_url(base_url('register')) ?>">List my business</a>
			</div>
		</div>
	</div>

</section>
