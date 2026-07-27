<?php
/**
 * /all-listing — the public business directory.
 *
 * Laid out like the homepage: a hero band, a category strip, then a main
 * column plus a right-hand sidebar. Styling lives in assets/css/directory.css
 * (scoped to `.p411-dir`, linked via $page_css) rather than in a <style> block
 * here, so nothing leaks into the ~200 views that share style.css.
 *
 * From User::all_listing(): $allListings, $link, $total_pages, $current_page,
 * $total_listings, $current_offset, $per_page, $sort, $allCategories,
 * $categoriesWithCounts, $randListings, $current_category_slug,
 * $current_category_name, $ad_slots, $user.
 *
 * Every one is defaulted below: this view is also reachable through older
 * entry points, and a missing variable should never be able to fatal the page.
 */

$allListings  = (isset($allListings)  && is_array($allListings))  ? $allListings  : array();
$randListings = (isset($randListings) && is_array($randListings)) ? $randListings : array();
$allCategories = (isset($allCategories) && is_array($allCategories)) ? $allCategories : array();
$categoriesWithCounts = (isset($categoriesWithCounts) && is_array($categoriesWithCounts)) ? $categoriesWithCounts : array();

$total_listings = isset($total_listings) ? (int) $total_listings : count($allListings);
$current_offset = isset($current_offset) ? (int) $current_offset : 0;
$per_page       = isset($per_page)       ? (int) $per_page       : 10;
$current_page   = isset($current_page)   ? (int) $current_page   : 1;
$total_pages    = isset($total_pages)    ? (int) $total_pages    : 1;
$sort           = isset($sort)           ? (string) $sort        : 'featured';
$cat_slug       = !empty($current_category_slug) ? (string) $current_category_slug : '';
$cat_name       = !empty($current_category_name) ? (string) $current_category_name : '';
if ($cat_slug !== '' && $cat_name === '') {
	$cat_name = ucwords(str_replace('-', ' ', $cat_slug));
}

$ad_slots = (isset($ad_slots) && is_array($ad_slots)) ? $ad_slots : array(
	'top'      => 'top-banner-site-wide',
	'inline'   => 'middle-banner-category',
	'side_1'   => 'right-side-banner-1',
	'side_2'   => 'right-side-banner-2',
	'side_sky' => 'right-panel-skyscraper',
	'bottom'   => 'bottom-banner-site-wide',
);

$dir_placeholder = base_url('assets/images/placeholder-listing.svg');

/** Category URL that matches the routes in application/config/routes.php. */
$dir_cat_url = function ($slug) {
	return $slug !== '' ? base_url('all_listing/' . rawurlencode($slug)) : base_url('all_listing');
};

/**
 * Slug for a category row, falling back to a generated one.
 *
 * The fallback goes through the shared pearland_slug() so this page, the
 * homepage and the header all derive the same URL from a given category name.
 * They used to disagree over "&" and "/", so a category with either in its
 * name reached a populated page from one menu and an empty one from another.
 */
$dir_cat_slug = function ($cat) {
	if (!empty($cat->category_slug)) return (string) $cat->category_slug;
	if (!empty($cat->category_name)) return pearland_slug($cat->category_name);
	return isset($cat->category_id) ? (string) $cat->category_id : '';
};

/** Stable colour for a letter avatar, so a listing keeps the same one. */
$dir_swatch = function ($name) {
	$palette = array('#4E8A28', '#2563EB', '#0E7490', '#B45309', '#7C3AED', '#BE185D', '#0F766E', '#180F5E');
	return $palette[crc32((string) $name) % count($palette)];
};

$showing_from = $total_listings > 0 ? $current_offset + 1 : 0;
$showing_to   = $current_offset + count($allListings);
?>

<section class="p411-dir">

	<!-- ============================================================ HERO -->
	<div class="p411-hero">
		<div class="p411-hero__bg" style="background-image:url('<?= base_url('assets/images/all-listing-bg.webp') ?>');" aria-hidden="true"></div>
		<div class="container">
			<nav class="p411-crumb" aria-label="Breadcrumb">
				<a href="<?= base_url() ?>">Home</a>
				<span aria-hidden="true">/</span>
				<?php if ($cat_slug !== ''): ?>
					<a href="<?= e_url(base_url('all_listing')) ?>">Directory</a>
					<span aria-hidden="true">/</span>
					<span class="is-current"><?= e($cat_name) ?></span>
				<?php else: ?>
					<span class="is-current">Directory</span>
				<?php endif; ?>
			</nav>

			<h1><?= $cat_slug !== '' ? e($cat_name) . ' in Pearland' : 'Pearland Business Directory' ?></h1>
			<p class="p411-hero__lede">
				<?php if ($cat_slug !== ''): ?>
					Every <?= e(strtolower($cat_name)) ?> business listed on Pearland411 — with phone numbers,
					addresses, reviews and directions.
				<?php else: ?>
					Find local businesses and service providers across Pearland, TX. Search by category,
					call them straight from the listing, or send one enquiry and let them come to you.
				<?php endif; ?>
			</p>

			<div class="p411-hero__stats">
				<div><b><?= number_format($total_listings) ?></b><?= $cat_slug !== '' ? 'in this category' : 'businesses listed' ?></div>
				<?php if (!empty($categoriesWithCounts)): ?>
					<div><b><?= number_format(count($categoriesWithCounts)) ?>+</b>categories</div>
				<?php endif; ?>
				<div><b>Free</b>to list your business</div>
			</div>
		</div>
	</div>

	<!-- ================================================== CATEGORY STRIP -->
	<?php if (!empty($categoriesWithCounts)): ?>
		<div class="p411-catstrip">
			<div class="container">
				<div class="p411-cats">
					<a href="<?= e_url(base_url('all_listing')) ?>" class="<?= $cat_slug === '' ? 'is-active' : '' ?>">
						<b>All categories</b>
						<?php // $total_listings is the count for the ACTIVE filter, so it
						      // would read as the size of the whole directory only when
						      // nothing is filtered. ?>
						<span><?= $cat_slug === '' ? number_format($total_listings) . ' listings' : 'Browse everything' ?></span>
					</a>
					<?php foreach (array_slice($categoriesWithCounts, 0, 7) as $cat): ?>
						<?php $slug_here = $dir_cat_slug($cat); ?>
						<a href="<?= e_url($dir_cat_url($slug_here)) ?>" class="<?= $slug_here === $cat_slug ? 'is-active' : '' ?>">
							<b><?= e($cat->category_name) ?></b>
							<?php if (isset($cat->listing_count)): ?>
								<span><?= (int) $cat->listing_count ?> listing<?= ((int) $cat->listing_count === 1 ? '' : 's') ?></span>
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
					$slot_alias = 'listing-top';
					include APPPATH . 'views/partials/ad_slot.php'; ?>
				</div>

				<?php if (!empty($allListings)): ?>
					<div class="p411-toolbar">
						<div class="p411-toolbar__count">
							Showing <b><?= number_format($showing_from) ?>&ndash;<?= number_format($showing_to) ?></b>
							of <b><?= number_format($total_listings) ?></b> business<?= $total_listings === 1 ? '' : 'es' ?>
							<span class="p411-toolbar__meta">
								<?= $cat_slug !== '' ? e($cat_name) . ' &middot; ' : '' ?>Pearland, TX
								<?php if ($total_pages > 1): ?>
									&middot; page <?= (int) $current_page ?> of <?= number_format($total_pages) ?>
								<?php endif; ?>
							</span>
						</div>
						<div class="p411-toolbar__right">
							<label for="dirSort">Sort</label>
							<select id="dirSort" class="p411-select" onchange="p411Sort(this.value)">
								<option value="featured" <?= $sort === 'featured' ? 'selected' : '' ?>>Featured first</option>
								<option value="newest"   <?= $sort === 'newest'   ? 'selected' : '' ?>>Newest</option>
								<option value="name"     <?= $sort === 'name'     ? 'selected' : '' ?>>Name (A&ndash;Z)</option>
								<option value="rating"   <?= $sort === 'rating'   ? 'selected' : '' ?>>Top rated</option>
							</select>
						</div>
					</div>

					<div class="p411-results">
						<?php foreach ($allListings as $index => $value): ?>
							<?php
							$name        = (string) ($value->listing_name ?? '');
							$detail_url  = base_url('listing-details/' . rawurlencode((string) ($value->listing_slug ?? '')));
							$cat_label   = !empty($value->category_name) ? (string) $value->category_name : '';
							$cat_link    = !empty($value->category_slug)
								? $dir_cat_url((string) $value->category_slug)
								: base_url('all-category');
							$is_featured = !empty($value->featured_listing) || !empty($value->spotlight_listing);
							$is_new      = !empty($value->created_at) && strtotime((string) $value->created_at) > strtotime('-30 days');
							$show_addr   = ((int) ($value->display_address ?? 0) === 1) && !empty($value->listing_address);
							$show_phone  = ((int) ($value->display_phone ?? 0) === 1) && !empty($value->listing_mobile);
							$reviews_n   = (int) ($value->total_reviews ?? 0);
							$rating      = (float) ($value->average_rating ?? 0);
							?>
							<article class="p411-biz<?= $is_featured ? ' p411-biz--featured' : '' ?>">
								<div class="p411-biz__media">
									<a href="<?= e_url($detail_url) ?>" tabindex="-1" aria-hidden="true">
										<?php if (!empty($value->listing_image)): ?>
											<?= responsive_img($value->listing_image, $name) ?>
										<?php else: ?>
											<img src="<?= e_attr($dir_placeholder) ?>" alt="" loading="lazy" decoding="async">
										<?php endif; ?>
									</a>
									<?php if ($cat_label !== ''): ?>
										<a class="p411-flag" href="<?= e_url($cat_link) ?>"><?= e($cat_label) ?></a>
									<?php endif; ?>
									<?php if ($is_featured): ?>
										<span class="p411-flag p411-flag--featured">Featured</span>
									<?php elseif ($is_new): ?>
										<span class="p411-flag p411-flag--new">New</span>
									<?php endif; ?>
								</div>

								<div class="p411-biz__body">
									<h3><a href="<?= e_url($detail_url) ?>"><?= e($name) ?></a></h3>

									<?php if ($reviews_n > 0 && $rating > 0): ?>
										<div class="p411-biz__rate">
											<i class="material-icons" aria-hidden="true">star</i>
											<b><?= number_format($rating, 1) ?></b>
											<span><?= $reviews_n ?> review<?= $reviews_n === 1 ? '' : 's' ?></span>
										</div>
									<?php endif; ?>

									<div class="p411-biz__meta">
										<?php if ($show_addr): ?>
											<span><i class="material-icons" aria-hidden="true">place</i><em><?= e($value->listing_address) ?></em></span>
										<?php endif; ?>
										<?php if ($show_phone): ?>
											<span><i class="material-icons" aria-hidden="true">call</i><em><?= e($value->listing_mobile) ?></em></span>
										<?php endif; ?>
										<?php if (!empty($value->listing_email)): ?>
											<span><i class="material-icons" aria-hidden="true">mail_outline</i><em><?= e($value->listing_email) ?></em></span>
										<?php endif; ?>
										<?php if (!$show_addr && !$show_phone && empty($value->listing_email)): ?>
											<span><i class="material-icons" aria-hidden="true">info_outline</i><em>Contact details on the listing page</em></span>
										<?php endif; ?>
									</div>

									<div class="p411-biz__actions">
										<a class="p411-btn p411-btn--sm" href="<?= e_url($detail_url) ?>">View details</a>
										<?php if ($show_phone): ?>
											<a class="p411-btn p411-btn--sm p411-btn--ghost"
											   href="tel:<?= e_attr(preg_replace('/[^0-9+]/', '', (string) $value->listing_mobile)) ?>">Call now</a>
										<?php endif; ?>
										<?php if (!empty($value->listing_whatsapp)): ?>
											<a class="p411-btn p411-btn--sm p411-btn--wa" target="_blank" rel="noopener nofollow"
											   href="https://wa.me/<?= e_attr(preg_replace('/[^0-9]/', '', (string) $value->listing_whatsapp)) ?>">WhatsApp</a>
										<?php endif; ?>
									</div>
								</div>
							</article>

							<?php // One in-list banner, after the fourth card, on every page that
							      // has more than four results. Category-priced zone. ?>
							<?php if ($index === 3 && count($allListings) > 4): ?>
								<div class="p411-adrow p411-adrow--grid">
									<?php $slot = $ad_slots['inline'];
									$slot_alias = 'listing-inline';
									include APPPATH . 'views/partials/ad_slot.php'; ?>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>

					<?php if (!empty($link)): ?>
						<nav class="p411-pager" aria-label="Directory pages"><?= $link ?></nav>
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
						<?php if ($cat_slug !== ''): ?>
							<h3>No businesses in <?= e($cat_name) ?> yet</h3>
							<p>
								Nothing is listed under this category at the moment. Try another category,
								browse the whole directory, or add your business &mdash; the basic listing is free.
							</p>
						<?php else: ?>
							<h3>No businesses are published yet</h3>
							<p>
								The directory is empty right now. If you run a business in Pearland you can add
								it in a couple of minutes &mdash; the basic listing is free and appears in search.
							</p>
						<?php endif; ?>
						<div class="p411-empty__actions">
							<?php if ($cat_slug !== ''): ?>
								<a class="p411-btn" href="<?= e_url(base_url('all_listing')) ?>">View all listings</a>
								<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('all-category')) ?>">Browse categories</a>
							<?php else: ?>
								<a class="p411-btn" href="<?= e_url(base_url('register')) ?>">Add my business &mdash; free</a>
								<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('all-category')) ?>">Browse categories</a>
							<?php endif; ?>
						</div>
					</div>

					<?php // The zone still renders when there are no results — an empty
					      // page is exactly where a paid banner should not go missing. ?>
					<div class="p411-adrow" style="margin-top:22px;">
						<?php $slot = $ad_slots['inline'];
						$slot_alias = 'listing-inline';
						include APPPATH . 'views/partials/ad_slot.php'; ?>
					</div>

					<?php // A results page with nothing on it leaves a tall empty column
					      // beside the sidebar. Fill it with the one thing that is always
					      // relevant here: what Pearland411 itself can build for you. ?>
					<?php $promo_variant = 'banner';
					include APPPATH . 'views/partials/service_promo.php'; ?>
				<?php endif; ?>
			</div>

			<!-- ================================================= SIDEBAR -->
			<aside class="p411-shell__side">

				<?php $slot = $ad_slots['side_1'];
				$slot_alias = 'listing-sidebar';
				// The static promo directly below already carries the
				// web-services pitch; an unsold zone sells the space itself
				// rather than repeating it.
				$slot_creative = 'house-ad';
				include APPPATH . 'views/partials/ad_slot.php'; ?>

				<?php // Static house promo — never sold, never rotated. ?>
				<?php include APPPATH . 'views/partials/service_promo.php'; ?>

				<!-- Featured businesses -->
				<div class="p411-card">
					<div class="p411-card__head">
						<h2>Top Featured</h2>
					</div>
					<?php if (!empty($randListings)): ?>
						<div class="p411-rail">
							<?php foreach ($randListings as $item): ?>
								<?php
								$item_name = (string) ($item->listing_name ?? '');
								$item_url  = base_url('listing-details/' . rawurlencode((string) ($item->listing_slug ?? '')));
								$item_addr = ((int) ($item->display_address ?? 0) === 1 && !empty($item->listing_address))
									? (string) $item->listing_address
									: (!empty($item->category_name) ? (string) $item->category_name : 'Pearland area');
								?>
								<a class="p411-rail__item" href="<?= e_url($item_url) ?>">
									<?php if (!empty($item->listing_image)): ?>
										<span class="p411-rail__thumb"><?= responsive_img($item->listing_image, $item_name) ?></span>
									<?php else: ?>
										<span class="p411-rail__thumb" style="background:<?= e_attr($dir_swatch($item_name)) ?>;" aria-hidden="true"><?= e(strtoupper(mb_substr(trim($item_name), 0, 1) ?: '•')) ?></span>
									<?php endif; ?>
									<span class="p411-rail__body">
										<b><?= e($item_name) ?></b>
										<span><?= e($item_addr) ?></span>
									</span>
									<span class="p411-rail__go" aria-hidden="true"><i class="material-icons">chevron_right</i></span>
								</a>
							<?php endforeach; ?>
						</div>
					<?php else: ?>
						<div class="p411-empty p411-empty--inline">
							<h3>No featured businesses yet</h3>
							<p>Featured placement pins a business to the top of its category and to the homepage.</p>
							<div class="p411-empty__actions">
								<a class="p411-btn p411-btn--sm" href="<?= e_url(base_url('pricing-plan')) ?>">See packages</a>
							</div>
						</div>
					<?php endif; ?>
				</div>

				<!-- Categories -->
				<div class="p411-card">
					<div class="p411-card__head">
						<h2>Categories</h2>
						<a class="p411-more" href="<?= e_url(base_url('all-category')) ?>">All &rarr;</a>
					</div>

					<div class="form-group" style="margin-bottom:12px;">
						<label class="sr-only" for="selectCategory">Filter by category</label>
						<select class="p411-select" id="selectCategory" style="width:100%;" onchange="p411FilterByCategory(this.value)">
							<option value="">All categories</option>
							<?php foreach ($allCategories as $cat): ?>
								<?php $slug_here = $dir_cat_slug($cat); ?>
								<option value="<?= e_attr($slug_here) ?>" <?= $slug_here === $cat_slug ? 'selected' : '' ?>><?= e($cat->category_name) ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<?php if (!empty($categoriesWithCounts)): ?>
						<ul class="p411-catlist">
							<?php foreach (array_slice($categoriesWithCounts, 0, 10) as $cat): ?>
								<?php $slug_here = $dir_cat_slug($cat); ?>
								<li>
									<a href="<?= e_url($dir_cat_url($slug_here)) ?>" class="<?= $slug_here === $cat_slug ? 'is-active' : '' ?>">
										<?= e($cat->category_name) ?>
										<?php if (isset($cat->listing_count)): ?><em><?= (int) $cat->listing_count ?></em><?php endif; ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php elseif (empty($allCategories)): ?>
						<p class="p411-form__hint" style="margin:0;">No categories have been set up yet.</p>
					<?php endif; ?>
				</div>

				<?php $slot = $ad_slots['side_2'];
				// The category-priced zones seeded by migration 0019. They are
				// separate sellable packages that render in this same position,
				// so anything booked on one still runs here.
				$slot_alias = array('right-banner-1', 'right-banner-2');
				// Unsold, this position becomes the ad/banner design card
				// rather than a flat picture of an advertisement — the person
				// this zone is for is the advertiser who has no artwork yet.
				// A real booking still wins the slot.
				$slot_fallback_partial = 'banner_design_promo.php';
				// Kept as the creative for the case where the partial is
				// missing, so the position never renders the generic house ad.
				$slot_creative = 'banner-design';
				include APPPATH . 'views/partials/ad_slot.php'; ?>

				<!-- One enquiry, sent to the Pearland411 team -->
				<div class="p411-card p411-form">
					<div class="p411-card__head">
						<h2>What service do you need?</h2>
					</div>
					<p class="p411-form__hint">Tell us what you're looking for and we'll point you at the right local business.</p>

					<div id="dir_enq_success" class="p411-note p411-note--ok" style="display:none;">Thanks &mdash; your enquiry has been sent.</div>
					<div id="dir_enq_fail" class="p411-note p411-note--err" style="display:none;">Sorry, that didn't send. Please try again.</div>

					<form name="dir_enquiry_form" id="dir_enquiry_form" method="post"
						  action="<?= base_url('user/add_enquiry/all_listing') ?>">
						<input type="hidden" name="listing_id" value="0">
						<input type="hidden" name="receiving_user_id" value="0">
						<input type="hidden" name="enquiry_sender_id" value="<?= e_attr(isset($user) && $user ? $user->user_id : '') ?>">
						<input type="hidden" name="enquiry_source" value="Directory<?= $cat_slug !== '' ? ' — ' . e_attr($cat_name) : '' ?>">
						<input type="hidden" name="enquiry_type" value="general">

						<div class="form-group">
							<label class="sr-only" for="dir_enq_name">Your name</label>
							<input id="dir_enq_name" type="text" name="enquiry_name" class="form-control" required
								   placeholder="Enter name*" value="<?= e_attr(isset($user) && isset($user->name) ? $user->name : '') ?>">
						</div>
						<div class="form-group">
							<label class="sr-only" for="dir_enq_email">Email</label>
							<input id="dir_enq_email" type="email" name="enquiry_email" class="form-control" required
								   placeholder="Enter email*" title="Enter a valid email address"
								   value="<?= e_attr(isset($user) && isset($user->email_id) ? $user->email_id : '') ?>">
						</div>
						<div class="form-group">
							<label class="sr-only" for="dir_enq_phone">Phone</label>
							<input id="dir_enq_phone" type="tel" name="enquiry_mobile" class="form-control" required
								   placeholder="Enter mobile number*" pattern="[0-9+\-\s()]{7,20}"
								   title="7 to 20 characters: digits, spaces, + ( ) and - are allowed"
								   value="<?= e_attr(isset($user) && isset($user->mobile_number) ? $user->mobile_number : '') ?>">
						</div>
						<div class="form-group">
							<label class="sr-only" for="dir_enq_msg">Your message</label>
							<textarea id="dir_enq_msg" name="enquiry_message" class="form-control" rows="3"
									  placeholder="What are you looking for?"></textarea>
						</div>
						<button type="submit" id="dir_enquiry_submit" class="p411-btn p411-btn--block p411-btn--navy">Submit requirements</button>
					</form>
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
				<h2>Is your business on this list?</h2>
				<p>List free in the Pearland411 directory, then add featured placement, a website or a banner when you're ready.</p>
			</div>
			<div class="p411-feat4">
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V9l7-5 7 5v12"/><path d="M10 21v-6h4v6"/></svg></i>
					<h3>List your business</h3>
					<p>A free listing puts you in search and on your category page.</p>
				</div>
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 2.6 5.6 6.4.8-4.6 4.3 1.1 6.3L12 17l-5.5 3 1.1-6.3L3 9.4l6.4-.8z"/></svg></i>
					<h3>Get to the top</h3>
					<p>Featured listings are pinned above every other result in their category.</p>
				</div>
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="14" rx="2"/><path d="M8 22h8M12 18v4"/></svg></i>
					<h3>Websites &amp; local SEO</h3>
					<p>Fast, mobile-first sites built and hosted for Pearland businesses.</p>
				</div>
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 15h10M7 11h6"/></svg></i>
					<h3>Advertise here</h3>
					<p>Banner positions on this page and across the site, from $20 a month.</p>
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

<script>
	(function () {
		'use strict';

		var DIR_BASE = <?= json_encode(base_url('all_listing')) ?>;
		var DIR_CAT  = <?= json_encode($cat_slug) ?>;

		// Category filter — rebuilds the path, dropping any page offset so the
		// visitor always lands on page one of the new category.
		window.p411FilterByCategory = function (slug) {
			window.location.href = slug ? DIR_BASE + '/' + encodeURIComponent(slug) : DIR_BASE;
		};

		// Sort is a query string, so the category/offset segments are untouched.
		window.p411Sort = function (value) {
			var path = DIR_BASE + (DIR_CAT ? '/' + encodeURIComponent(DIR_CAT) : '');
			window.location.href = value && value !== 'featured' ? path + '?sort=' + encodeURIComponent(value) : path;
		};

		// Enquiry form: post in the background so the visitor keeps their place
		// in the results. The CSRF hook has already injected the hidden token
		// into this form, and FormData carries it along.
		var form = document.getElementById('dir_enquiry_form');
		if (!form) return;

		var ok   = document.getElementById('dir_enq_success');
		var fail = document.getElementById('dir_enq_fail');
		var btn  = document.getElementById('dir_enquiry_submit');

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			ok.style.display = fail.style.display = 'none';
			btn.disabled = true;

			var xhr = new XMLHttpRequest();
			xhr.open('POST', form.action, true);
			xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			xhr.onreadystatechange = function () {
				if (xhr.readyState !== 4) return;
				btn.disabled = false;
				var success = false;
				if (xhr.status === 200) {
					try { success = !!JSON.parse(xhr.responseText).success; } catch (err) { success = false; }
				}
				if (success) { ok.style.display = 'block'; form.reset(); }
				else { fail.style.display = 'block'; }
			};
			xhr.send(new FormData(form));
		});
	})();
</script>
