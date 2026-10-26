<?php
/**
 * /listing-details/<slug> — one business.
 *
 * Same shell as the directory and the homepage: hero, then a main column and a
 * right-hand sidebar. Styling is in assets/css/directory.css (scoped to
 * `.p411-ld`, linked via $page_css); nothing here touches the shared theme.
 *
 * From User::listing_details(): $listing, $user, $logged_id, $RelatedListing,
 * $listing_reviews, $review_stats, $review_total, $review_average,
 * $listing_category, $owner_socials, $ad_slots.
 *
 * Admin::listing_preview() renders this view too, with a thinner data set, so
 * every variable is defaulted below rather than assumed.
 */

if (empty($listing)) {
	// Belt and braces: the controllers show_404() before reaching the view.
	echo '<div class="container" style="padding:60px 0;"><h1>Listing not found</h1></div>';
	return;
}

$logged_id       = isset($logged_id) ? $logged_id : null;
$user            = isset($user) ? $user : null;
$RelatedListing  = (isset($RelatedListing)  && is_array($RelatedListing))  ? $RelatedListing  : array();
$listing_reviews = (isset($listing_reviews) && is_array($listing_reviews)) ? $listing_reviews : array();
$review_stats    = (isset($review_stats)    && is_array($review_stats))    ? $review_stats    : array();
$owner_socials   = (isset($owner_socials)   && is_array($owner_socials))   ? $owner_socials   : array();
$listing_category = isset($listing_category) ? $listing_category : null;

// Ad targeting. Without this every ad_slot include on this page falls into
// getAdForSlot()'s untargeted branch (`WHERE a.category_id IS NULL`), which
// actively FILTERS OUT a banner bought for this listing's category — so a
// Restaurants booking appeared on the directory index but never on a
// restaurant's own page. /all-listing has passed this since 0024; this page
// never did. Re-assigned before each include below rather than set once,
// because ad_slot.php unsets it on its early-return paths.
$ad_category_id = isset($listing_category->category_id) ? (int) $listing_category->category_id : null;

// Totals: the controller derives these, but Admin's preview doesn't.
if (!isset($review_total)) {
	$review_total = $review_stats ? array_sum($review_stats) : (int) ($listing->total_reviews ?? 0);
}
if (!isset($review_average)) {
	$review_average = 0.0;
	if ($review_stats && $review_total > 0) {
		$weighted = 0;
		foreach ($review_stats as $stars => $count) $weighted += ($stars * $count);
		$review_average = $weighted / $review_total;
	} else {
		$review_average = (float) ($listing->average_rating ?? 0);
	}
}
$review_total   = (int) $review_total;
$review_average = (float) $review_average;

$ad_slots = (isset($ad_slots) && is_array($ad_slots)) ? $ad_slots : array(
	'top'      => 'top-banner-site-wide',
	'inline'   => 'middle-banner-category',
	'side_1'   => 'right-side-banner-1',
	'side_2'   => 'right-side-banner-2',
	'side_sky' => 'right-panel-skyscraper',
	'bottom'   => 'bottom-banner-site-wide',
);

/* ---- normalised fields ------------------------------------------------- */

$ld_name    = (string) ($listing->listing_name ?? '');
$ld_slug    = (string) ($listing->listing_slug ?? '');
$ld_url     = base_url('listing-details/' . rawurlencode($ld_slug));
$ld_logo    = !empty($listing->listing_image) ? base_url($listing->listing_image) : base_url('assets/images/placeholder-listing.svg');
$ld_cover   = !empty($listing->cover_image)   ? base_url($listing->cover_image)   : base_url('assets/images/llisting_details_bg.jpg');
// listings.is_claimed, not user_id — every listing has an owner, so that
// test hid the Claim button on the whole imported directory. See
// listing_is_claimed().
$ld_claimed = listing_is_claimed($listing);
$ld_owner_view = !empty($viewer_owns_listing);
$ld_claim_url  = base_url('claim-listing/' . rawurlencode((string) ($listing->listing_slug ?? '')));
$ld_lid        = (int) ($listing->listing_id ?? 0);
$ld_verified = !empty($listing->featured_listing) || !empty($listing->spotlight_listing);

$show_addr  = ((int) ($listing->display_address ?? 0) === 1) && !empty($listing->listing_address);
$show_phone = ((int) ($listing->display_phone ?? 0) === 1) && !empty($listing->listing_mobile);
$wa_number  = preg_replace('/[^0-9]/', '', (string) (!empty($listing->listing_whatsapp) ? $listing->listing_whatsapp : ($listing->listing_mobile ?? '')));
$show_wa    = $show_phone && $wa_number !== '';

/**
 * Descriptions were pasted in with markup (`<p>…<br />`), and escaping the
 * whole string printed those tags on the page. Turn the block-level tags into
 * newlines, drop the rest, then escape — so stored HTML reads as clean text
 * and no markup can be injected either.
 */
$ld_text = function ($value) {
	$value = preg_replace('#<\s*(br|/p|/div|/li)\s*/?\s*>#i', "\n", (string) $value);
	$value = strip_tags($value);
	$value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	$value = preg_replace("/\n{3,}/", "\n\n", trim($value));
	return $value;
};

/* ---- optional blocks --------------------------------------------------- */

$ld_services = array();
if (!empty($listing->service_offered)) {
	$ld_services = array_values(array_filter(array_map('trim', explode(',', (string) $listing->service_offered)), 'strlen'));
}

$ld_areas = array();
if (!empty($listing->service_locations)) {
	$ld_areas = array_values(array_filter(array_map('trim', explode(',', (string) $listing->service_locations)), 'strlen'));
}

$ld_gallery = array();
if (!empty($listing->listing_image_gallery)) {
	$decoded = json_decode((string) $listing->listing_image_gallery, true);
	if (is_array($decoded)) {
		$ld_gallery = array_values(array_filter(array_map('trim', $decoded), 'strlen'));
	}
}

$ld_videos = array();
if (!empty($listing->listing_video)) {
	// listing_video_urls(): commas OR new lines, as the forms ask for.
	foreach (listing_video_urls($listing->listing_video) as $video_url) {
		$embed = $video_url;
		if (strpos($video_url, 'youtube.com/watch?v=') !== false) {
			parse_str((string) parse_url($video_url, PHP_URL_QUERY), $qs);
			if (!empty($qs['v'])) $embed = 'https://www.youtube.com/embed/' . rawurlencode($qs['v']);
		} elseif (strpos($video_url, 'youtu.be/') !== false) {
			$id = trim((string) parse_url($video_url, PHP_URL_PATH), '/');
			if ($id !== '') $embed = 'https://www.youtube.com/embed/' . rawurlencode($id);
		}
		$ld_videos[] = $embed;
	}
}

$has_map = !empty($listing->google_map) || (!empty($listing->listing_lat) && !empty($listing->listing_lng));

/* ---- sub-nav ----------------------------------------------------------- */

$ld_nav = array('about' => 'About');
if ($ld_services)                 $ld_nav['services'] = 'Services';
if ($ld_gallery)                  $ld_nav['gallery']  = 'Photos';
if ($ld_videos)                   $ld_nav['videos']   = 'Videos';
$ld_nav['reviews'] = 'Reviews' . ($review_total > 0 ? ' (' . $review_total . ')' : '');
if ($has_map)                     $ld_nav['location'] = 'Location';
if ($RelatedListing)              $ld_nav['related']  = 'Similar businesses';

/* ---- sharing ----------------------------------------------------------- */

$share_url   = rawurlencode($ld_url);
$share_title = rawurlencode($ld_name);
$share_image = rawurlencode(!empty($listing->listing_image) ? base_url($listing->listing_image) : base_url('assets/images/pearland-logo.png'));

/** Five gold/grey stars for a 0–5 value. */
$ld_stars = function ($value) {
	$out = '';
	$full = (int) round((float) $value);
	for ($i = 1; $i <= 5; $i++) {
		$out .= '<i class="material-icons" aria-hidden="true">' . ($i <= $full ? 'star' : 'star_border') . '</i>';
	}
	return $out;
};
?>

<section class="p411-ld" data-track-listing="<?= $ld_lid ?>">

	<!-- ============================================================ HERO -->
	<div class="p411-ld__hero">
		<div class="p411-ld__cover" style="background-image:url('<?= e_attr($ld_cover) ?>');" aria-hidden="true"></div>
		<div class="container">
			<nav class="p411-crumb" aria-label="Breadcrumb">
				<a href="<?= base_url() ?>">Home</a>
				<span aria-hidden="true">/</span>
				<a href="<?= e_url(base_url('all_listing')) ?>">Directory</a>
				<?php if (!empty($listing_category->category_name)): ?>
					<span aria-hidden="true">/</span>
					<a href="<?= e_url(base_url('all_listing/' . rawurlencode((string) ($listing_category->category_slug ?: $listing_category->category_id)))) ?>"><?= e($listing_category->category_name) ?></a>
				<?php endif; ?>
				<span aria-hidden="true">/</span>
				<span class="is-current"><?= e($ld_name) ?></span>
			</nav>

			<div class="p411-ld__ident">
				<div class="p411-ld__logo">
					<img src="<?= e_attr($ld_logo) ?>" alt="<?= e_attr($ld_name) ?>" loading="eager" decoding="async">
				</div>

				<div class="p411-ld__head">
					<div class="p411-ld__badges">
						<?php if (!empty($listing_category->category_name)): ?>
							<a class="p411-tag" href="<?= e_url(base_url('all_listing/' . rawurlencode((string) ($listing_category->category_slug ?: $listing_category->category_id)))) ?>"><?= e($listing_category->category_name) ?></a>
						<?php endif; ?>
						<?php if ($ld_verified): ?>
							<?php // `stars`, not `verified`: the classic Material Icons font that
						      // header.php loads has no `verified` ligature, so the browser fell
						      // back to rendering the eight letters of the word in the icon face
						      // — 112px of blank badge in front of the label. ?>
						<span class="p411-tag p411-tag--bu"><i class="material-icons" aria-hidden="true">stars</i> Featured</span>
						<?php endif; ?>
						<?php if (!$ld_claimed): ?>
							<span class="p411-tag p411-tag--amber">Unclaimed</span>
						<?php endif; ?>
						<?php if (!empty($listing->year_established)): ?>
							<span class="p411-tag">Since <?= e($listing->year_established) ?></span>
						<?php endif; ?>
					</div>

					<h1><?= e($ld_name) ?></h1>

					<div class="p411-ld__rate">
						<?php if ($review_total > 0): ?>
							<b><?= number_format($review_average, 1) ?></b>
							<span class="p411-stars"><?= $ld_stars($review_average) ?></span>
							<span><?= number_format($review_total) ?> review<?= $review_total === 1 ? '' : 's' ?></span>
						<?php else: ?>
							<span class="p411-stars"><?= $ld_stars(0) ?></span>
							<span>No reviews yet &mdash; <a href="#write-review" style="color:#fff;text-decoration:underline;">be the first</a></span>
						<?php endif; ?>
						<?php if (!empty($listing->views)): ?>
							<span>&middot; <?= number_format((int) $listing->views) ?> views</span>
						<?php endif; ?>
					</div>

					<div class="p411-ld__facts">
						<?php if ($show_addr): ?>
							<span><i class="material-icons" aria-hidden="true">place</i><?= e($listing->listing_address) ?></span>
						<?php endif; ?>
						<?php if ($show_phone): ?>
							<a data-track="phone" href="tel:<?= e_attr(preg_replace('/[^0-9+]/', '', (string) $listing->listing_mobile)) ?>"><i class="material-icons" aria-hidden="true">call</i><?= e($listing->listing_mobile) ?></a>
						<?php endif; ?>
						<?php if (!empty($listing->listing_email)): ?>
							<a data-track="email" href="mailto:<?= e_attr($listing->listing_email) ?>"><i class="material-icons" aria-hidden="true">mail_outline</i><?= e($listing->listing_email) ?></a>
						<?php endif; ?>
						<?php if (!empty($listing->listing_website)): ?>
							<a data-track="website" href="<?= e_url($listing->listing_website) ?>" target="_blank" rel="noopener nofollow"><i class="material-icons" aria-hidden="true">language</i><?= e($listing->listing_website) ?></a>
						<?php endif; ?>
					</div>

					<div class="p411-ld__cta">
						<?php if ($show_phone): ?>
							<a class="p411-btn" data-track="phone" href="tel:<?= e_attr(preg_replace('/[^0-9+]/', '', (string) $listing->listing_mobile)) ?>">
								<i class="material-icons" style="font-size:17px;" aria-hidden="true">call</i> Call now
							</a>
						<?php endif; ?>
						<?php if ($show_wa): ?>
							<a class="p411-btn p411-btn--wa" data-track="whatsapp" href="https://wa.me/<?= e_attr($wa_number) ?>" target="_blank" rel="noopener nofollow">WhatsApp</a>
						<?php endif; ?>
						<?php // Opens the quote form for THIS business in a dialog (script at the
						      // foot of the page). It is the sidebar card itself, moved into the
						      // dialog and back — never a second copy of the form, which is what
						      // the old modal was ("double entry"). Scrolling to the sidebar
						      // instead read on a phone as being sent somewhere else (tracker:
						      // "redirects to the quote enquiry form"). Without JS the anchor
						      // still scrolls to the card. ?>
						<a class="p411-btn p411-btn--ghost" href="#get-a-quote" data-quote-open>Get a quote</a>
						<?php if (!empty($listing->listing_lat) && !empty($listing->listing_lng)): ?>
							<a class="p411-btn p411-btn--ghost" target="_blank" rel="noopener" data-track="map"
							   href="https://www.google.com/maps/dir/?api=1&amp;destination=<?= (float) $listing->listing_lat ?>,<?= (float) $listing->listing_lng ?>">Directions</a>
						<?php endif; ?>
						<?php if (!$ld_claimed): ?>
							<?php // Burgundy, not navy: this row of buttons sits on the navy hero. ?>
							<a class="p411-btn p411-btn--bu" href="<?= e_url($ld_claim_url) ?>">
								<i class="material-icons" style="font-size:17px;" aria-hidden="true">how_to_reg</i> Claim This Business
							</a>
						<?php elseif ($ld_owner_view): ?>
							<a class="p411-btn p411-btn--bu" href="<?= e_url(base_url('user/edit-listing/' . rawurlencode($ld_slug))) ?>">
								<i class="material-icons" style="font-size:17px;" aria-hidden="true">edit</i> Edit your listing
							</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- ========================================================= SUB-NAV -->
	<div class="p411-ld__nav">
		<div class="container">
			<ul>
				<?php foreach ($ld_nav as $anchor => $label): ?>
					<li><a href="#<?= e_attr($anchor) ?>"><?= $label ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>

	<div class="container">
		<?php $__claim_msg = $this->session->flashdata('claim_message'); ?>
		<?php if (!empty($__claim_msg)): ?>
			<div class="p411-note p411-note--info" style="margin-top:18px;"><?= e($__claim_msg) ?></div>
		<?php endif; ?>

		<div class="p411-shell">

			<!-- ==================================================== MAIN -->
			<div class="p411-shell__main">
				<div class="p411-adrow">
					<?php $slot = $ad_slots['top'];
					$slot_alias = 'listing-top';
					$slot_category = $ad_category_id;
					include APPPATH . 'views/partials/ad_slot.php'; ?>
				</div>

				<div class="p411-ld__body">

					<!-- ABOUT -->
					<div class="p411-card" id="about">
						<div class="p411-card__head">
							<h2>About <?= e($ld_name) ?></h2>
						</div>

						<?php $ld_about = $ld_text($listing->listing_description ?? ''); ?>
						<?php if ($ld_about !== ''): ?>
							<div class="p411-prose"><?= nl2br(e($ld_about)) ?></div>
						<?php else: ?>
							<div class="p411-empty p411-empty--inline">
								<h3>No description yet</h3>
								<p>
									<?= $ld_claimed
										? 'This business hasn&rsquo;t added a description yet. Call or message them using the details above.'
										: 'Nobody has claimed this listing yet, so there&rsquo;s no description. If it&rsquo;s your business you can claim it and add one for free.' ?>
								</p>
								<?php if (!$ld_claimed): ?>
									<div class="p411-empty__actions">
										<a class="p411-btn p411-btn--sm" href="<?= e_url($ld_claim_url) ?>">Claim This Business</a>
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<div class="p411-share" style="margin-top:18px;" aria-label="Share this business">
							<a class="so-fb" target="_blank" rel="noopener" title="Share on Facebook"
							   href="https://www.facebook.com/sharer/sharer.php?u=<?= $share_url ?>"
							   onclick="window.open(this.href,'share-fb','width=800,height=600');return false;">
								<img src="<?= base_url('assets/images/social/3.png') ?>" alt="Facebook" loading="lazy"></a>
							<a class="so-tw" target="_blank" rel="noopener" title="Share on X / Twitter"
							   href="https://twitter.com/intent/tweet?url=<?= $share_url ?>&amp;text=<?= $share_title ?>"
							   onclick="window.open(this.href,'share-tw','width=800,height=600');return false;">
								<img src="<?= base_url('assets/images/social/2.png') ?>" alt="Twitter" loading="lazy"></a>
							<a class="so-wa" target="_blank" rel="noopener" title="Share on WhatsApp"
							   href="https://wa.me/?text=<?= $share_title ?>%20-%20<?= $share_url ?>">
								<img src="<?= base_url('assets/images/social/6.png') ?>" alt="WhatsApp" loading="lazy"></a>
							<a class="so-li" target="_blank" rel="noopener" title="Share on LinkedIn"
							   href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $share_url ?>"
							   onclick="window.open(this.href,'share-li','width=800,height=600');return false;">
								<img src="<?= base_url('assets/images/social/1.png') ?>" alt="LinkedIn" loading="lazy"></a>
							<a class="so-pi" target="_blank" rel="noopener" title="Share on Pinterest"
							   href="https://pinterest.com/pin/create/button/?url=<?= $share_url ?>&amp;media=<?= $share_image ?>&amp;description=<?= $share_title ?>"
							   onclick="window.open(this.href,'share-pi','width=800,height=600');return false;">
								<img src="<?= base_url('assets/images/social/9.png') ?>" alt="Pinterest" loading="lazy"></a>
							<a class="so-link" href="javascript:void(0)" title="Copy link"
							   onclick="p411CopyLink(<?= e_attr(json_encode($ld_url)) ?>)">
								<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
									<g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
										<path d="M10.7 13.3a3 3 0 0 0 4.24 0l2.12-2.12a3 3 0 0 0-4.24-4.24L11.4 8.36"/>
										<path d="M13.3 10.7a3 3 0 0 0-4.24 0L6.94 12.8a3 3 0 0 0 4.24 4.24l1.42-1.42"/>
									</g>
								</svg>
							</a>
						</div>
					</div>

					<!-- SERVICES + AREAS -->
					<?php if ($ld_services || $ld_areas || $ld_claimed): ?>
						<div class="p411-card" id="services">
							<div class="p411-card__head">
								<h2>Services &amp; coverage</h2>
							</div>

							<h3 style="font-size:14px;font-weight:700;margin-bottom:10px;">Services offered</h3>
							<?php if ($ld_services): ?>
								<div class="p411-chips">
									<?php foreach ($ld_services as $service): ?><span><?= e($service) ?></span><?php endforeach; ?>
								</div>
							<?php else: ?>
								<p class="p411-form__hint" style="margin:0;">No services listed yet &mdash; contact the business to ask what they cover.</p>
							<?php endif; ?>

							<h3 style="font-size:14px;font-weight:700;margin:20px 0 10px;">Areas served</h3>
							<?php if ($ld_areas): ?>
								<div class="p411-chips p411-chips--plain">
									<?php foreach ($ld_areas as $area): ?><span><?= e($area) ?></span><?php endforeach; ?>
								</div>
							<?php else: ?>
								<p class="p411-form__hint" style="margin:0;">No service areas listed. Pearland and the surrounding area is a safe assumption &mdash; ask to be sure.</p>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<!-- PHOTO GALLERY -->
					<?php if ($ld_gallery): ?>
						<div class="p411-card" id="gallery">
							<div class="p411-card__head">
								<h2>Photos</h2>
							</div>
							<div class="p411-gallery">
								<?php foreach ($ld_gallery as $image): ?>
									<a href="<?= e_url(base_url($image)) ?>" target="_blank" rel="noopener">
										<img src="<?= e_attr(base_url($image)) ?>" alt="<?= e_attr($ld_name) ?>" loading="lazy" decoding="async">
									</a>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>

					<!-- VIDEOS -->
					<?php if ($ld_videos): ?>
						<div class="p411-card" id="videos">
							<div class="p411-card__head">
								<h2>Videos</h2>
							</div>
							<div class="p411-video">
								<?php foreach ($ld_videos as $embed): ?>
									<div class="p411-embed">
										<iframe src="<?= e_url($embed) ?>" title="<?= e_attr($ld_name) ?> video" allowfullscreen loading="lazy"></iframe>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>

					<!-- 360 TOUR -->
					<?php if (!empty($listing->virtual_tour_link)): ?>
						<div class="p411-card" id="tour">
							<div class="p411-card__head">
								<h2>360&deg; tour</h2>
							</div>
							<div class="p411-embed">
								<iframe src="<?= e_url($listing->virtual_tour_link) ?>" title="360 degree tour" allowfullscreen loading="lazy"></iframe>
							</div>
						</div>
					<?php endif; ?>

					<!-- =============================================== IN-CONTENT AD -->
					<div class="p411-adrow" style="margin:0;">
						<?php $slot = $ad_slots['inline'];
						$slot_alias = 'listing-inline';
						$slot_category = $ad_category_id;
						include APPPATH . 'views/partials/ad_slot.php'; ?>
					</div>

					<!-- REVIEWS -->
					<?php
					/*
					 * One card, one heading, one way in.
					 *
					 * This was two cards — "Reviews" and "Write a review" — carrying four
					 * separate invitations to write one: a "Write a review" link in the
					 * first card's header, a "Write the first review" button in its empty
					 * state, the second card's own heading, and "be the first" up in the
					 * hero. Three of them jumped a few hundred pixels down the page to the
					 * fourth. The reported "multiple option and tabs to write or view
					 * review" is that: the page offered a choice where there was none.
					 *
					 * Reading and writing are now the two halves of one #reviews section,
					 * in that order. #write-review stays as an id on the second half, so
					 * the hero link and any external anchor still land in the right place.
					 */
					?>
					<div class="p411-card" id="reviews">
						<div class="p411-card__head">
							<h2>Reviews</h2>
						</div>

						<?php if ($review_total > 0): ?>
							<div class="p411-revsum">
								<div class="p411-revsum__score">
									<b><?= number_format($review_average, 1) ?></b>
									<div class="p411-stars"><?= $ld_stars($review_average) ?></div>
									<span><?= number_format($review_total) ?> review<?= $review_total === 1 ? '' : 's' ?></span>
								</div>
								<div>
									<?php
									$labels = array(5 => 'Excellent', 4 => 'Very good', 3 => 'Good', 2 => 'Fair', 1 => 'Poor');
									foreach ($labels as $stars => $label):
										$count = isset($review_stats[$stars]) ? (int) $review_stats[$stars] : 0;
										$pct   = $review_total > 0 ? round(($count / $review_total) * 100) : 0;
									?>
										<div class="p411-revbar">
											<span class="p411-revbar__label"><?= $label ?></span>
											<span class="p411-revbar__track"><span class="p411-revbar__fill" style="width:<?= (int) $pct ?>%;"></span></span>
											<span class="p411-revbar__n"><?= $count ?></span>
										</div>
									<?php endforeach; ?>
								</div>
							</div>

							<?php if ($listing_reviews): ?>
								<ul class="p411-revlist" style="margin-top:20px;">
									<?php foreach ($listing_reviews as $review): ?>
										<li>
											<div class="p411-revlist__ava">
												<img src="<?= e_attr(!empty($review->profile_image) ? base_url($review->profile_image) : base_url('assets/images/fallback_profile.svg')) ?>"
													 alt="" loading="lazy" decoding="async">
											</div>
											<div class="p411-revlist__body">
												<h4><?= e($review->reviewer_name ?? 'Visitor') ?></h4>
												<div class="p411-stars" style="margin:3px 0;"><?= $ld_stars($review->rating ?? 0) ?></div>
												<span class="p411-revlist__when">
													<?= !empty($review->created_at) ? e(date('j M Y', strtotime((string) $review->created_at))) : '' ?>
													<?= !empty($review->reviewer_city) ? ' &middot; ' . e($review->reviewer_city) : '' ?>
												</span>
												<?php if (!empty($review->review_message)): ?>
													<p><?= nl2br(e($review->review_message)) ?></p>
												<?php endif; ?>
											</div>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						<?php else: ?>
							<div class="p411-empty p411-empty--inline">
								<h3>No reviews yet</h3>
								<p>Nobody has reviewed <?= e($ld_name) ?> on Pearland411. If you&rsquo;ve used them, your review helps the next person decide &mdash; the form is just below.</p>
							</div>
						<?php endif; ?>

						<!-- ...and the other half of the same section: writing one -->
						<div class="p411-revwrite p411-form" id="write-review">
							<h3>Write a review</h3>

							<?php $__rev_ok = $this->session->flashdata('review_success'); ?>
							<?php if (!empty($__rev_ok)): ?>
								<div class="p411-note p411-note--ok"><?= e($__rev_ok) ?></div>
							<?php endif; ?>
							<?php $__rev_err = $this->session->flashdata('review_error'); ?>
							<?php if (!empty($__rev_err)): ?>
								<div class="p411-note p411-note--err" role="alert"><?= e($__rev_err) ?></div>
							<?php endif; ?>

							<?php if (empty($logged_id)): ?>
								<p class="p411-form__hint">Reviews are tied to an account so businesses can trust them. It takes a minute to register.</p>
								<div class="p411-empty__actions">
									<a class="p411-btn p411-btn--sm" href="<?= e_url(base_url('login')) ?>">Log in</a>
									<a class="p411-btn p411-btn--sm p411-btn--ghost" href="<?= e_url(base_url('register')) ?>">Create an account</a>
								</div>
							<?php else: ?>
								<p class="p411-form__hint">Say what you had done, how it went and whether you'd go back. Keep it factual &mdash; it's more useful that way.</p>

								<?php // onsubmit disables the button once the form is on its way — a
								      // second click used to post a second copy of the review. It
								      // checks defaultPrevented after the other handlers have run, so
								      // a submit form-validate.js cancelled leaves the button usable. ?>
								<form name="review_form" id="review_form" method="post" data-validate
									  action="<?= base_url('user/submit_review') ?>"
									  onsubmit="var ev=event,b=document.getElementById('review_submit');setTimeout(function(){if(b&&!ev.defaultPrevented){b.disabled=true;b.textContent='Sending\u2026';}},0);">
									<input type="hidden" name="listing_id" value="<?= (int) ($listing->listing_id ?? 0) ?>">
									<input type="hidden" name="listing_user_id" value="<?= (int) ($listing->user_id ?? 0) ?>">
									<input type="hidden" name="review_user_id" value="<?= (int) $logged_id ?>">

									<div style="margin-bottom:14px;">
										<fieldset class="p411-rating">
											<?php foreach (array(5 => 'Awesome', 4 => 'Excellent', 3 => 'Good', 2 => 'Average', 1 => 'Poor') as $stars => $word): ?>
												<input type="radio" id="p411star<?= $stars ?>" name="price_rating" class="price_rating"
													   value="<?= $stars ?>" <?= $stars === 3 ? 'checked' : '' ?>>
												<label for="p411star<?= $stars ?>" title="<?= $stars ?> &ndash; <?= $word ?>"><span class="sr-only"><?= $stars ?> stars</span></label>
											<?php endforeach; ?>
										</fieldset>
										<div class="p411-rating__value" id="p411-star-value">3 stars &mdash; Good</div>
									</div>

									<div class="p411-grid2">
										<div class="form-group">
											<label class="sr-only" for="review_name">Your name</label>
											<input id="review_name" name="review_name" type="text" class="form-control" required
												   placeholder="Your name" value="<?= e_attr($user->name ?? '') ?>" <?= !empty($user->name) ? 'readonly' : '' ?>>
										</div>
										<div class="form-group">
											<label class="sr-only" for="review_mobile">Mobile number</label>
											<input id="review_mobile" name="review_mobile" type="tel" class="form-control"
												   placeholder="Mobile number" value="<?= e_attr($user->mobile_number ?? '') ?>" <?= !empty($user->mobile_number) ? 'readonly' : '' ?>>
										</div>
										<div class="form-group">
											<label class="sr-only" for="review_email">Email</label>
											<input id="review_email" name="review_email" type="email" class="form-control" required
												   placeholder="Email address" value="<?= e_attr($user->email_id ?? '') ?>" <?= !empty($user->email_id) ? 'readonly' : '' ?>>
										</div>
										<div class="form-group">
											<label class="sr-only" for="review_city">City</label>
											<input id="review_city" name="review_city" type="text" class="form-control" placeholder="City">
										</div>
									</div>
									<div class="form-group">
										<label class="sr-only" for="review_message">Your review</label>
										<textarea id="review_message" name="review_message" class="form-control" rows="4" required
											  minlength="10" maxlength="2000" placeholder="Write your review"></textarea>
									</div>
									<button type="submit" id="review_submit" name="review_submit" class="p411-btn">Submit review</button>
								</form>
							<?php endif; ?>
						</div>
					</div>

					<!-- RELATED -->
					<div class="p411-card" id="related">
						<div class="p411-card__head">
							<h2>Similar businesses</h2>
							<?php if (!empty($listing_category->category_slug)): ?>
								<a class="p411-more" href="<?= e_url(base_url('all_listing/' . rawurlencode((string) $listing_category->category_slug))) ?>">See all &rarr;</a>
							<?php endif; ?>
						</div>

						<?php if ($RelatedListing): ?>
							<div class="p411-rel">
								<?php foreach ($RelatedListing as $rel): ?>
									<?php $rel_url = base_url('listing-details/' . rawurlencode((string) ($rel->listing_slug ?? ''))); ?>
									<a class="p411-rel__item" href="<?= e_url($rel_url) ?>">
										<div class="p411-rel__thumb">
											<?php if (!empty($rel->listing_image)): ?>
												<?= responsive_img($rel->listing_image, (string) ($rel->listing_name ?? '')) ?>
											<?php else: ?>
												<img src="<?= base_url('assets/images/placeholder-listing.svg') ?>" alt="" loading="lazy" decoding="async">
											<?php endif; ?>
										</div>
										<div class="p411-rel__body">
											<b><?= e($rel->listing_name ?? '') ?></b>
											<span><?= e(!empty($rel->category_name) ? $rel->category_name : 'Pearland area') ?></span>
										</div>
									</a>
								<?php endforeach; ?>
							</div>
						<?php else: ?>
							<div class="p411-empty p411-empty--inline">
								<h3>Nothing similar listed yet</h3>
								<p>
									<?= !empty($listing_category->category_name)
										? 'This is currently the only published business in ' . e($listing_category->category_name) . '.'
										: 'This listing isn&rsquo;t assigned to a category yet, so we can&rsquo;t suggest similar businesses.' ?>
								</p>
								<div class="p411-empty__actions">
									<a class="p411-btn p411-btn--sm p411-btn--ghost" href="<?= e_url(base_url('all_listing')) ?>">Browse the directory</a>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<!-- ================================================= SIDEBAR -->
			<aside class="p411-shell__side">

				<!-- Get a quote -->
				<?php
				/*
				 * The form is rendered for everybody, signed in or not.
				 *
				 * It used to be swapped for a "Log in to enquire" panel for anonymous
				 * visitors, which is where the reported "Get a quote does not work"
				 * came from: the hero button is an anchor to this card, and the only
				 * id on the page was the form's, so for a logged-out visitor — most
				 * of the people who want a quote — the button pointed at nothing.
				 *
				 * Nothing behind it needed the login either. `add_enquiry` is on
				 * User::$public_actions, takes `enquiry_sender_id` as optional, and
				 * is rate-limited to 8 per IP per hour precisely because it accepts
				 * anonymous senders. Gating the form only cost the business the lead.
				 */
				$__enq_old = (array) $this->session->flashdata('enquiry_old');
				$__enq_bad = (array) $this->session->flashdata('enquiry_errors');
				$__enq_val = function ($field, $fallback = '') use ($__enq_old) {
					return isset($__enq_old[$field]) ? (string) $__enq_old[$field] : (string) $fallback;
				};
				?>
				<div class="p411-card p411-form" id="get-a-quote">
					<div class="p411-card__head">
						<h2 id="p411-quote-title">Get a quote</h2>
						<button type="button" class="p411-quote-close" data-quote-close aria-label="Close" hidden>&times;</button>
					</div>

					<?php // Which business this goes to — the request is filed against this
					      // listing and emailed to it, so say so where the visitor types. ?>
					<div class="p411-quote-for">
						<img class="p411-quote-for__logo" src="<?= e_url($ld_logo) ?>" alt="" loading="lazy">
						<div class="p411-quote-for__text">
							<strong><?= e($ld_name) ?></strong>
							<?php if (!empty($listing_category->category_name)): ?>
								<span><?= e($listing_category->category_name) ?></span>
							<?php endif; ?>
							<?php if ($show_addr): ?>
								<span><?= e($listing->listing_address) ?></span>
							<?php endif; ?>
						</div>
					</div>

					<div class="p411-quote-status" aria-live="polite"></div>

					<?php $__enq_msg = $this->session->flashdata('message'); ?>
					<?php if (!empty($__enq_msg)): ?>
						<div class="p411-note p411-note--ok"><?= e($__enq_msg) ?></div>
					<?php endif; ?>
					<?php $__enq_err = $this->session->flashdata('error_msg'); ?>
					<?php if (!empty($__enq_err)): ?>
						<div class="p411-note p411-note--err"><?= e($__enq_err) ?></div>
					<?php endif; ?>

					<p class="p411-form__hint">Tell <?= e($ld_name) ?> what you need and they&rsquo;ll come back to you.</p>

					<form method="post" name="detail_enquiry_form" id="detail_enquiry_form" data-validate
						  action="<?= base_url('user/add-enquiry/listing_details/' . rawurlencode($ld_slug)) ?>">
						<input type="hidden" name="listing_id" value="<?= (int) ($listing->listing_id ?? 0) ?>">
						<input type="hidden" name="enquiry_source" value="<?= e_attr($ld_name) ?>">
						<input type="hidden" name="enquiry_type" value="listing">

						<div class="form-group">
							<label class="sr-only" for="enq_name">Your name</label>
							<input id="enq_name" type="text" name="enquiry_name" class="form-control" required maxlength="100"
								   placeholder="Enter name*" value="<?= e_attr($__enq_val('enquiry_name', $user->name ?? '')) ?>">
							<?php if (!empty($__enq_bad['enquiry_name'])): ?>
								<small class="p411-form__err"><?= e($__enq_bad['enquiry_name']) ?></small>
							<?php endif; ?>
						</div>
						<div class="form-group">
							<label class="sr-only" for="enq_email">Email</label>
							<input id="enq_email" type="email" name="enquiry_email" class="form-control" required maxlength="190"
								   placeholder="Enter email*" value="<?= e_attr($__enq_val('enquiry_email', $user->email_id ?? '')) ?>">
							<?php if (!empty($__enq_bad['enquiry_email'])): ?>
								<small class="p411-form__err"><?= e($__enq_bad['enquiry_email']) ?></small>
							<?php endif; ?>
						</div>
						<div class="form-group">
							<label class="sr-only" for="enq_phone">Phone</label>
							<input id="enq_phone" type="tel" name="enquiry_mobile" class="form-control" required
								   placeholder="Enter mobile number*" pattern="[0-9+\-\s()]{7,20}"
								   title="7 to 20 characters: digits, spaces, + ( ) and - are allowed"
								   value="<?= e_attr($__enq_val('enquiry_mobile', $user->mobile_number ?? '')) ?>">
							<?php if (!empty($__enq_bad['enquiry_mobile'])): ?>
								<small class="p411-form__err"><?= e($__enq_bad['enquiry_mobile']) ?></small>
							<?php endif; ?>
						</div>
						<div class="form-group">
							<label class="sr-only" for="enq_msg">Your message</label>
							<textarea id="enq_msg" name="enquiry_message" class="form-control" rows="3" maxlength="5000"
									  placeholder="What do you need a quote for?"><?= e($__enq_val('enquiry_message')) ?></textarea>
							<?php if (!empty($__enq_bad['enquiry_message'])): ?>
								<small class="p411-form__err"><?= e($__enq_bad['enquiry_message']) ?></small>
							<?php endif; ?>
						</div>
						<?= spam_form_fields() ?>
						<button type="submit" id="detail_enquiry_submit" name="enquiry_submit" class="p411-btn p411-btn--block">Send enquiry</button>
					</form>

					<?php if (empty($logged_id)): ?>
						<p class="p411-form__hint" style="margin:12px 0 0;">
							<a href="<?= e_url(base_url('login')) ?>">Sign in</a> first and we&rsquo;ll fill your details in
							and keep the reply with your account.
						</p>
					<?php endif; ?>
					<?php if ($show_phone): ?>
						<p class="p411-form__hint" style="margin:8px 0 0;">In a hurry? Call <a data-track="phone" href="tel:<?= e_attr(preg_replace('/[^0-9+]/', '', (string) $listing->listing_mobile)) ?>"><?= e($listing->listing_mobile) ?></a>.</p>
					<?php endif; ?>
				</div>

				<?php if (!empty($listing_coupons)): ?>
					<!-- Current deals (posted by the owner at /user/my-coupons) -->
					<div class="p411-card" id="deals">
						<div class="p411-card__head">
							<h2>Current deals</h2>
						</div>
						<ul class="p411-info">
							<?php foreach ($listing_coupons as $deal): ?>
								<li>
									<a href="<?= e_url(base_url('coupons/' . (int) $deal->coupon_id)) ?>"><?= e($deal->coupon_name) ?></a>
									<span>
										<?php if (!empty($deal->coupon_code)): ?>Code <strong><?= e($deal->coupon_code) ?></strong> &middot; <?php endif; ?>
										until <?= e(us_date($deal->coupon_end_date)) ?>
									</span>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<!-- Contact details -->
				<div class="p411-card">
					<div class="p411-card__head">
						<h2>Business info</h2>
					</div>
					<?php
					$info_rows = array();
					if ($show_phone)                        $info_rows['Phone']     = '<a data-track="phone" href="tel:' . e_attr(preg_replace('/[^0-9+]/', '', (string) $listing->listing_mobile)) . '">' . e($listing->listing_mobile) . '</a>';
					if ($show_wa)                           $info_rows['WhatsApp']  = '<a data-track="whatsapp" href="https://wa.me/' . e_attr($wa_number) . '" target="_blank" rel="noopener nofollow">Message on WhatsApp</a>';
					if (!empty($listing->listing_email))    $info_rows['Email']     = '<a data-track="email" href="mailto:' . e_attr($listing->listing_email) . '">' . e($listing->listing_email) . '</a>';
					if (!empty($listing->listing_website))  $info_rows['Website']   = '<a data-track="website" href="' . e_url($listing->listing_website) . '" target="_blank" rel="noopener nofollow">' . e($listing->listing_website) . '</a>';
					if (!empty($listing->contact_person))   $info_rows['Contact']   = e($listing->contact_person);
					if ($show_addr)                         $info_rows['Address']   = e($listing->listing_address);
					if (!empty($listing->business_hours))   $info_rows['Hours']     = nl2br(e($ld_text($listing->business_hours)));
					if (!empty($listing->year_established)) $info_rows['Established'] = e($listing->year_established);
					?>
					<?php if ($info_rows): ?>
						<ul class="p411-info">
							<?php foreach ($info_rows as $label => $value): ?>
								<li><?= $label ?><span><?= $value ?></span></li>
							<?php endforeach; ?>
						</ul>
					<?php else: ?>
						<p class="p411-form__hint" style="margin:0;">
							This business hasn&rsquo;t published contact details.
							<?= !$ld_claimed ? 'If it&rsquo;s yours, claim the listing to add them.' : 'Use the enquiry form above to get in touch.' ?>
						</p>
					<?php endif; ?>
				</div>

				<?php // Owner profile / claim prompt. Deliberately high in the
				      // column — for an unclaimed listing this is the single
				      // most useful thing on the page for the person who owns
				      // the business, and it used to sit below the map, both
				      // sidebar ads and the services promo. ?>
				<!-- Owner profile -->
				<?php $has_socials = (bool) array_filter($owner_socials); ?>
				<?php if ($ld_claimed || $has_socials): ?>
					<div class="p411-card">
						<div class="p411-card__head">
							<h2>Business profile</h2>
						</div>
						<div class="p411-profile">
							<div class="p411-profile__img">
								<img src="<?= e_attr($ld_logo) ?>" alt="<?= e_attr($ld_name) ?>" loading="lazy" decoding="async">
							</div>
							<h3><?= e($ld_name) ?></h3>
							<?php if ($show_addr): ?><p><?= e($listing->listing_address) ?></p><?php endif; ?>

							<?php if ($has_socials): ?>
								<div class="p411-profile__soc">
									<?php
									// The business's own links (listing_social_links()). The
									// theme ships no Instagram icon, so that one is lettered.
									$soc_icons = array(
										'facebook'  => array('3.png', 'Facebook'),
										'instagram' => array('', 'Instagram', 'IG'),
										'twitter'   => array('2.png', 'X / Twitter'),
										'youtube'   => array('5.png', 'YouTube'),
										'linkedin'  => array('1.png', 'LinkedIn'),
									);
									foreach ($soc_icons as $key => $meta):
										// external_url() is what the site footer's own icons use
										// (partials/site_footer.php:64-71): it adds https:// to a
										// schemeless "www.facebook.com/x" and returns '' for
										// anything that is not http/https/mailto/tel. Without it a
										// schemeless value was emitted as a RELATIVE href, so the
										// icon navigated back into the site instead of out to the
										// profile — the reported "sends you to the homepage".
										$soc_href = external_url($owner_socials[$key] ?? '');
										if ($soc_href === '') continue;
									?>
										<a href="<?= e_attr($soc_href) ?>" target="_blank" rel="noopener nofollow" aria-label="<?= e_attr($meta[1]) ?>" title="<?= e_attr($meta[1]) ?>">
											<?php if ($meta[0] !== ''): ?>
												<img src="<?= base_url('assets/images/social/' . $meta[0]) ?>" alt="<?= e_attr($meta[1]) ?>" loading="lazy">
											<?php else: ?>
												<b aria-hidden="true"><?= e($meta[2]) ?></b>
											<?php endif; ?>
										</a>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				<?php else: ?>
					<div class="p411-card">
						<div class="p411-card__head">
							<h2>Is this your business?</h2>
						</div>
						<p class="p411-form__hint">
							This listing hasn&rsquo;t been claimed. Claim it to edit the details, add photos and reply to reviews.
						</p>
						<a class="p411-btn p411-btn--block" href="<?= e_url($ld_claim_url) ?>">Claim This Business</a>
					</div>
				<?php endif; ?>

				<!-- Location -->
				<div class="p411-card" id="location">
					<div class="p411-card__head">
						<h2>Location</h2>
					</div>
					<?php if (!empty($listing->google_map)): ?>
						<div class="p411-map">
							<iframe src="<?= e_url($listing->google_map) ?>" title="Map showing <?= e_attr($ld_name) ?>"
									allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
						</div>
					<?php elseif (!empty($listing->listing_lat) && !empty($listing->listing_lng)): ?>
						<div class="p411-map">
							<iframe src="https://maps.google.com/maps?q=<?= (float) $listing->listing_lat ?>,<?= (float) $listing->listing_lng ?>&amp;output=embed"
									title="Map showing <?= e_attr($ld_name) ?>" allowfullscreen loading="lazy"></iframe>
						</div>
					<?php else: ?>
						<?php // A default map used to be rendered here — centred on San Francisco.
						      // Better to say nothing is set than to point people 1,900 miles away. ?>
						<div class="p411-empty p411-empty--inline">
							<h3>No map pin yet</h3>
							<p>
								<?= $show_addr
									? 'This business hasn&rsquo;t added a map, but its address is listed above.'
									: 'This business hasn&rsquo;t shared a location.' ?>
							</p>
							<?php if ($show_addr): ?>
								<div class="p411-empty__actions">
									<a class="p411-btn p411-btn--sm p411-btn--ghost" target="_blank" rel="noopener" data-track="map"
									   href="https://www.google.com/maps/search/?api=1&amp;query=<?= rawurlencode((string) $listing->listing_address) ?>">Find on Google Maps</a>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php if (!empty($listing->listing_lat) && !empty($listing->listing_lng)): ?>
						<div class="p411-map__actions">
							<a class="p411-btn p411-btn--sm p411-btn--ghost" target="_blank" rel="noopener" data-track="map"
							   href="https://www.google.com/maps?q=<?= (float) $listing->listing_lat ?>,<?= (float) $listing->listing_lng ?>">Open in Google Maps</a>
							<a class="p411-btn p411-btn--sm" target="_blank" rel="noopener" data-track="map"
							   href="https://www.google.com/maps/dir/?api=1&amp;destination=<?= (float) $listing->listing_lat ?>,<?= (float) $listing->listing_lng ?>">Get directions</a>
						</div>
					<?php endif; ?>
				</div>

				<?php $slot = $ad_slots['side_1'];
				$slot_alias = 'listing-sidebar';
				// The static promo below already carries the web-services
				// pitch, so an unsold zone sells the space itself.
				$slot_creative = 'house-ad';
				$slot_category = $ad_category_id;
				include APPPATH . 'views/partials/ad_slot.php'; ?>

				<?php // Static house promo — website development, hosting, local SEO. ?>
				<?php include APPPATH . 'views/partials/service_promo.php'; ?>

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
				$slot_category = $ad_category_id;
				include APPPATH . 'views/partials/ad_slot.php'; ?>

				<?php $slot = $ad_slots['side_sky'];
				$slot_fallback = false;
				$slot_category = $ad_category_id;
				include APPPATH . 'views/partials/ad_slot.php'; ?>

			</aside>
		</div>

		<!-- ==================================================== BOTTOM AD -->
		<div class="p411-adrow" style="margin-top:6px;">
			<?php $slot = $ad_slots['bottom'];
			$slot_category = $ad_category_id;
			include APPPATH . 'views/partials/ad_slot.php'; ?>
		</div>
	</div>
</section>

<?php // The "Get a quote" dialog. It starts empty: the script moves the sidebar
      // card (#get-a-quote) in on open and puts it back on close, so there is
      // only ever ONE quote form on the page. The old modal here held a second,
      // identical copy of it — the reported "double entry". Inside .p411-ld so
      // the kit's tokens and form styles still apply. ?>
<div class="p411-ld p411-ld-modal" id="p411-quote-modal" role="dialog" aria-modal="true" aria-labelledby="p411-quote-title" hidden>
	<div class="p411-ld-modal__box"></div>
</div>

<script>
	(function () {
		'use strict';

		// Star picker label. The fieldset is reversed in CSS so the markup can
		// stay 5→1; the label text follows whatever is actually checked.
		var words = { '5': 'Awesome', '4': 'Excellent', '3': 'Good', '2': 'Average', '1': 'Poor' };
		var out = document.getElementById('p411-star-value');
		Array.prototype.forEach.call(document.querySelectorAll('input[name="price_rating"]'), function (input) {
			input.addEventListener('change', function () {
				if (out) out.innerHTML = this.value + ' star' + (this.value === '1' ? '' : 's') + ' &mdash; ' + words[this.value];
			});
		});

		// Smooth-scroll the sub-nav and the hero's own anchors without losing
		// the sticky offset. The hero selector covers "be the first", which
		// jumps past the fold. "Get a quote" opens the dialog below instead.
		Array.prototype.forEach.call(document.querySelectorAll('.p411-ld__nav a[href^="#"], .p411-ld__hero a[href^="#"], .p411-ld a[href="#write-review"]'), function (link) {
			if (link.hasAttribute('data-quote-open')) return;
			link.addEventListener('click', function (e) {
				var target = document.getElementById(this.getAttribute('href').slice(1));
				if (!target) return;
				e.preventDefault();
				target.scrollIntoView({ behavior: 'smooth', block: 'start' });
			});
		});
	})();

	/*
	 * "Get a quote": open the quote card for this business in a dialog and send
	 * it without leaving the page.
	 *
	 * The card is MOVED into the dialog and back (a placeholder marks its place
	 * in the sidebar), so there is one form, one set of ids and one CSRF token.
	 * The form still posts normally if this script fails; with it, the request
	 * goes over XHR — add_enquiry() already answers JSON for that — and the
	 * confirmation appears where the visitor typed.
	 */
	(function () {
		'use strict';
		var modal = document.getElementById('p411-quote-modal');
		var card  = document.getElementById('get-a-quote');
		if (!modal || !card) return;
		var box    = modal.querySelector('.p411-ld-modal__box');
		var close  = card.querySelector('[data-quote-close]');
		var form   = card.querySelector('form');
		var status = card.querySelector('.p411-quote-status');
		var home   = document.createComment('get-a-quote');
		var opener = null;

		function open(e) {
			if (e) e.preventDefault();
			opener = document.activeElement;
			card.parentNode.insertBefore(home, card);
			box.appendChild(card);
			// Moving the card reloads the reCAPTCHA iframe; draw it afresh.
			if (window.p411Recaptcha) window.p411Recaptcha.remount(form);
			modal.hidden = false;
			if (close) close.hidden = false;
			document.body.classList.add('p411-modal-open');
			var first = card.querySelector('input:not([type=hidden]), textarea');
			if (first) first.focus();
		}
		function shut() {
			if (modal.hidden) return;
			if (home.parentNode) home.parentNode.replaceChild(card, home);
			if (window.p411Recaptcha) window.p411Recaptcha.remount(form);
			modal.hidden = true;
			if (close) close.hidden = true;
			document.body.classList.remove('p411-modal-open');
			if (opener && opener.focus) opener.focus();
		}

		Array.prototype.forEach.call(document.querySelectorAll('[data-quote-open]'), function (a) {
			a.addEventListener('click', open);
		});
		if (close) close.addEventListener('click', shut);
		modal.addEventListener('click', function (e) { if (e.target === modal) shut(); });
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape') shut(); });

		// Back from a no-JS submission (or a refused one) with a result to show:
		// open the dialog so it is in view, not a long scroll down on a phone.
		if (location.hash === '#get-a-quote' && card.querySelector('.p411-note')) open();

		function say(kind, text) {
			status.innerHTML = '';
			var n = document.createElement('div');
			n.className = 'p411-note p411-note--' + kind;
			n.textContent = text;
			status.appendChild(n);
		}

		if (!form || !window.fetch || !window.FormData) return;
		form.addEventListener('submit', function (e) {
			// form-validate.js marks an invalid form in the capture phase.
			if (e.defaultPrevented) return;
			e.preventDefault();
			var btn = form.querySelector('[type=submit]');
			if (btn) { btn.disabled = true; btn.dataset.label = btn.textContent; btn.textContent = 'Sending…'; }
			Array.prototype.forEach.call(form.querySelectorAll('.p411-form__err[data-ajax]'), function (el) { el.remove(); });

			fetch(form.action, {
				method: 'POST',
				body: new FormData(form),
				credentials: 'same-origin',
				headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
			}).then(function (r) {
				return r.text().then(function (t) {
					try { return JSON.parse(t); } catch (err) {
						return { success: false, message: r.status === 429
							? 'Too many requests from this connection. Please try again later or call the business.'
							: 'We could not send that just now. Please try again.' };
					}
				});
			}).then(function (res) {
				if (res && res.success) {
					say('ok', 'Thanks — your request was sent to ' + <?= json_encode($ld_name) ?> + '. They will reply to the email address you gave.');
					form.reset();
					form.hidden = true;
					var again = document.createElement('button');
					again.type = 'button';
					again.className = 'p411-btn p411-btn--ghost p411-btn--block';
					again.textContent = 'Send another request';
					again.addEventListener('click', function () { form.hidden = false; status.innerHTML = ''; again.remove(); });
					status.appendChild(again);
				} else {
					say('err', (res && res.message) || 'Something went wrong. Please try again.');
					var errs = (res && res.errors) || {};
					Object.keys(errs).forEach(function (name) {
						var f = form.querySelector('[name="' + name + '"]');
						if (!f) return;
						var m = document.createElement('small');
						m.className = 'p411-form__err';
						m.setAttribute('data-ajax', '');
						m.textContent = errs[name];
						f.parentNode.appendChild(m);
					});
				}
			}).catch(function () {
				say('err', 'We could not send that just now. Please check your connection and try again.');
			}).then(function () {
				// A reCAPTCHA answer is good for one send; untick for the next.
				if (window.p411Recaptcha) window.p411Recaptcha.reset(form);
				if (btn) { btn.disabled = false; btn.textContent = btn.dataset.label || 'Send enquiry'; }
			});
		});
	})();

	// Copy the listing URL. Called inline from the share row.
	function p411CopyLink(url) {
		function toast(message) {
			var el = document.createElement('div');
			el.textContent = message;
			el.setAttribute('role', 'status');
			el.style.cssText = 'position:fixed;top:20px;right:20px;z-index:10000;background:#5E9220;color:#fff;'
				+ 'padding:13px 18px;border-radius:9px;font-size:14px;box-shadow:0 8px 26px rgba(0,0,0,.22);';
			document.body.appendChild(el);
			setTimeout(function () { el.remove(); }, 2600);
		}

		if (navigator.clipboard && window.isSecureContext) {
			navigator.clipboard.writeText(url).then(function () { toast('Link copied'); },
				function () { toast('Could not copy the link'); });
			return;
		}

		var field = document.createElement('textarea');
		field.value = url;
		field.style.cssText = 'position:fixed;top:0;left:0;opacity:0;';
		document.body.appendChild(field);
		field.select();
		try { toast(document.execCommand('copy') ? 'Link copied' : 'Could not copy the link'); }
		catch (err) { toast('Could not copy the link'); }
		document.body.removeChild(field);
	}
</script>
