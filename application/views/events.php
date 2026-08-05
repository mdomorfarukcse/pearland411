<?php
/**
 * /event — every community event on Pearland411.
 *
 * Same shell as the homepage and the directory: a hero band, a filter strip,
 * then a main column plus a right-hand sidebar. Styling is in
 * assets/css/directory.css (the shared kit) + assets/css/content.css, linked
 * through $page_css; nothing here touches the shared theme, and there is no
 * <style> block — the previous version carried ~230 lines of them.
 *
 * From User::event(): $Allevent, $pagination_links, $search_query, $when,
 * $when_counts, $when_auto, $total_events, $per_page, $current_page,
 * $total_pages, $current_offset, $upcoming_events, $ad_slots, $user.
 *
 * Every one is defaulted below — User::event_search() renders this same view
 * with a thinner set, and a missing variable must never fatal the page.
 */

$Allevent        = (isset($Allevent)        && is_array($Allevent))        ? $Allevent        : array();
$upcoming_events = (isset($upcoming_events) && is_array($upcoming_events)) ? $upcoming_events : array();
$when_counts     = (isset($when_counts)     && is_array($when_counts))     ? $when_counts     : array();

$search_query   = isset($search_query)   ? (string) $search_query : '';
$when           = isset($when)           ? (string) $when         : 'all';
$when_auto      = !empty($when_auto);
$total_events   = isset($total_events)   ? (int) $total_events   : count($Allevent);
$per_page       = isset($per_page)       ? (int) $per_page       : 10;
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

$ev_placeholder = base_url('assets/images/placeholder-listing.svg');
$today_ts       = strtotime('today');

/** Detail URL for an event row, falling back to a slug built from the name. */
$ev_url = function ($event) {
	$slug = !empty($event->event_slug) ? (string) $event->event_slug : pearland_slug($event->event_name ?? '');
	return base_url('event-details/' . rawurlencode($slug));
};

/** The date this event is filed under: its start date, else when it was added. */
$ev_ts = function ($event) {
	$src = !empty($event->event_start_date) ? $event->event_start_date : ($event->created_at ?? '');
	$ts  = $src ? strtotime((string) $src) : false;
	return $ts ?: null;
};

/** Index URL for a time window, keeping any search term. */
$ev_filter_url = function ($window) use ($search_query) {
	$qs = array();
	if ($window !== 'upcoming') $qs['when'] = $window;
	if ($search_query !== '')   $qs['q']    = $search_query;
	return base_url('event') . ($qs ? '?' . http_build_query($qs) : '');
};

$showing_from = $total_events > 0 ? $current_offset + 1 : 0;
$showing_to   = $current_offset + count($Allevent);

$window_label = array(
	'upcoming' => 'Upcoming events',
	'past'     => 'Past events',
	'all'      => 'All events',
);
?>

<section class="p411-ct p411-ct--events">

	<!-- ============================================================ HERO -->
	<div class="p411-hero">
		<div class="p411-hero__bg" style="background-image:url('<?= base_url('assets/images/event-bg.jpg') ?>');" aria-hidden="true"></div>
		<div class="container">
			<nav class="p411-crumb" aria-label="Breadcrumb">
				<a href="<?= base_url() ?>">Home</a>
				<span aria-hidden="true">/</span>
				<span class="is-current">Events</span>
			</nav>

			<h1>Events in Pearland</h1>
			<p class="p411-hero__lede">
				Concerts, markets, festivals, seminars and community meet-ups around Pearland, TX —
				with the date, the time, the address and who to call.
			</p>

			<div class="p411-hero__stats">
				<div><b><?= number_format(isset($when_counts['upcoming']) ? (int) $when_counts['upcoming'] : 0) ?></b>coming up</div>
				<div><b><?= number_format(isset($when_counts['all']) ? (int) $when_counts['all'] : $total_events) ?></b>events listed</div>
				<div><b>Free</b>to submit yours</div>
			</div>

			<div class="p411-ct-hero__search">
				<form action="<?= base_url('event') ?>" method="get" role="search">
					<?php if ($when !== 'upcoming'): ?>
						<input type="hidden" name="when" value="<?= e_attr($when) ?>">
					<?php endif; ?>
					<div class="p411-ct-search">
						<label class="sr-only" for="event-search">Search events</label>
						<input type="search" name="q" id="event-search"
							   placeholder="Search events by name, description or address&hellip;"
							   value="<?= e_attr($search_query) ?>" autocomplete="off">
						<?php if ($search_query !== ''): ?>
							<a class="p411-ct-search__clear" href="<?= e_url($ev_filter_url($when)) ?>" title="Clear search" aria-label="Clear search">
								<i class="material-icons" aria-hidden="true">close</i>
							</a>
						<?php endif; ?>
						<button type="submit" aria-label="Search events">
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
			<div class="p411-ct-tabs" role="navigation" aria-label="Filter events by date">
				<?php foreach (array('upcoming' => 'Upcoming', 'past' => 'Past', 'all' => 'All events') as $key => $label): ?>
					<a href="<?= e_url($ev_filter_url($key)) ?>" class="<?= $when === $key ? 'is-active' : '' ?>"
					   <?= $when === $key ? 'aria-current="page"' : '' ?>>
						<?= $label ?>
						<?php if (isset($when_counts[$key])): ?><em><?= number_format((int) $when_counts[$key]) ?></em><?php endif; ?>
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

				<?php if ($when_auto): ?>
					<?php // Landing on an empty page because nothing is scheduled is
					      // worse than showing the archive and saying so. ?>
					<div class="p411-note p411-note--info" style="margin-bottom:16px;">
						Nothing is on the calendar just yet, so this is <b>every event</b> we have —
						most recent first.
					</div>
				<?php endif; ?>

				<?php if (!empty($Allevent)): ?>
					<div class="p411-toolbar">
						<div class="p411-toolbar__count">
							Showing <b><?= number_format($showing_from) ?>&ndash;<?= number_format($showing_to) ?></b>
							of <b><?= number_format($total_events) ?></b> event<?= $total_events === 1 ? '' : 's' ?>
							<span class="p411-toolbar__meta">
								<?= e(isset($window_label[$when]) ? $window_label[$when] : 'All events') ?>
								<?php if ($total_pages > 1): ?>
									&middot; page <?= (int) $current_page ?> of <?= number_format($total_pages) ?>
								<?php endif; ?>
							</span>
						</div>
						<div class="p411-toolbar__right">
							<a class="p411-btn p411-btn--sm p411-btn--ghost" href="<?= e_url(base_url('user/add_user_event')) ?>">Submit an event</a>
						</div>
					</div>

					<div class="p411-ct-grid">
						<?php foreach ($Allevent as $index => $event): ?>
							<?php
							$detail_url  = $ev_url($event);
							$date_ts     = $ev_ts($event);
							$is_upcoming = $date_ts !== null && $date_ts >= $today_ts;
							$host_name   = !empty($event->host_name) ? (string) $event->host_name : 'Pearland411';
							$host_image  = !empty($event->host_image) ? base_url($event->host_image) : '';
							$excerpt     = content_excerpt($event->event_description ?? '', 130);
							?>
							<article class="p411-ct-card">
								<div class="p411-ct-card__media">
									<a class="p411-ct-card__link" href="<?= e_url($detail_url) ?>" tabindex="-1" aria-hidden="true">
										<?php if (!empty($event->event_image)): ?>
											<?= responsive_img($event->event_image, (string) ($event->event_name ?? '')) ?>
										<?php else: ?>
											<img src="<?= e_attr($ev_placeholder) ?>" alt="" loading="lazy" decoding="async">
										<?php endif; ?>
									</a>

									<?php if ($date_ts !== null): ?>
										<span class="p411-ct-card__date<?= $is_upcoming ? '' : ' p411-ct-card__date--past' ?>">
											<span><?= e(date('M', $date_ts)) ?></span>
											<b><?= e(date('d', $date_ts)) ?></b>
										</span>
									<?php endif; ?>

									<?php if ($is_upcoming): ?>
										<span class="p411-flag p411-flag--featured">Upcoming</span>
									<?php elseif ($date_ts !== null): ?>
										<span class="p411-flag p411-flag--featured p411-flag--muted">Past event</span>
									<?php endif; ?>
								</div>

								<div class="p411-ct-card__body">
									<h3><a href="<?= e_url($detail_url) ?>"><?= e($event->event_name ?? 'Untitled event') ?></a></h3>

									<ul class="p411-ct-meta">
										<?php if ($date_ts !== null): ?>
											<li><i class="material-icons" aria-hidden="true">event</i><em><?= e(date('D, j M Y', $date_ts)) ?></em></li>
										<?php endif; ?>
										<?php if (!empty($event->event_time)): ?>
											<li><i class="material-icons" aria-hidden="true">schedule</i><em><?= e($event->event_time) ?></em></li>
										<?php endif; ?>
										<?php if (!empty($event->event_address)): ?>
											<li><i class="material-icons" aria-hidden="true">place</i><em><?= e($event->event_address) ?></em></li>
										<?php endif; ?>
										<?php if (!empty($event->event_mobile)): ?>
											<li><i class="material-icons" aria-hidden="true">call</i><em><?= e($event->event_mobile) ?></em></li>
										<?php endif; ?>
									</ul>

									<?php if ($excerpt !== ''): ?>
										<p class="p411-ct-excerpt"><?= e($excerpt) ?></p>
									<?php endif; ?>
								</div>

								<div class="p411-ct-card__foot">
									<div class="p411-ct-host">
										<?php if ($host_image !== ''): ?>
											<img src="<?= e_attr($host_image) ?>" alt="" loading="lazy" decoding="async">
										<?php else: ?>
											<span class="p411-ct-host__ava" aria-hidden="true"><?= e(strtoupper(mb_substr($host_name, 0, 1))) ?></span>
										<?php endif; ?>
										<span>
											<small>Hosted by</small>
											<b><?= e($host_name) ?></b>
										</span>
									</div>
									<a class="p411-btn p411-btn--sm" href="<?= e_url($detail_url) ?>">View event</a>
								</div>
							</article>

							<?php // One in-list banner after the sixth card, on pages that
							      // have more than six results. ?>
							<?php if ($index === 5 && count($Allevent) > 6): ?>
								<div class="p411-adrow">
									<?php $slot = $ad_slots['inline'];
									include APPPATH . 'views/partials/ad_slot.php'; ?>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>

					<?php if (!empty($pagination_links)): ?>
						<nav class="p411-pager" aria-label="Event pages">
							<?= $pagination_links ?>
							<p class="p411-pager__hint">Page <?= (int) $current_page ?> of <?= number_format($total_pages) ?></p>
						</nav>
					<?php endif; ?>

				<?php else: ?>
					<!-- ======================================= EMPTY STATE -->
					<div class="p411-empty">
						<div class="p411-empty__icon">
							<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<rect x="3" y="5" width="18" height="16" rx="2"></rect>
								<path d="M16 3v4M8 3v4M3 11h18"></path>
							</svg>
						</div>
						<?php if ($search_query !== ''): ?>
							<h3>No events match &ldquo;<?= e($search_query) ?>&rdquo;</h3>
							<p>Try a shorter phrase, search by the venue instead of the event name, or browse everything that's listed.</p>
							<div class="p411-empty__actions">
								<a class="p411-btn" href="<?= e_url(base_url('event')) ?>">Browse all events</a>
								<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('all_listing')) ?>">Search the directory instead</a>
							</div>
						<?php elseif ($when === 'past'): ?>
							<h3>Nothing in the archive yet</h3>
							<p>No event on Pearland411 has been and gone. Have a look at what's coming up.</p>
							<div class="p411-empty__actions">
								<a class="p411-btn" href="<?= e_url($ev_filter_url('upcoming')) ?>">See upcoming events</a>
							</div>
						<?php else: ?>
							<h3>No events listed right now</h3>
							<p>
								Nothing is on the calendar at the moment. If you're running something in Pearland —
								a market, a class, a fundraiser — listing it here is free.
							</p>
							<div class="p411-empty__actions">
								<a class="p411-btn" href="<?= e_url(base_url('user/add_user_event')) ?>">Submit your event</a>
								<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('news')) ?>">Read the local news</a>
							</div>
						<?php endif; ?>
					</div>

					<?php // The zone still renders when there are no results — an empty
					      // page is exactly where a paid banner should not go missing. ?>
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
				// 0006 seeded 'events-sidebar' for this position. It matches no
				// package on /pricing-plan, but an operator may have booked one
				// by hand, so anything live on it keeps running here.
				$slot_alias = 'events-sidebar';
				// The static promo directly below already carries the
				// web-services pitch; an unsold zone sells the space itself.
				$slot_creative = 'house-ad';
				include APPPATH . 'views/partials/ad_slot.php'; ?>

				<?php include APPPATH . 'views/partials/service_promo.php'; ?>

				<!-- What's coming up -->
				<div class="p411-card">
					<div class="p411-card__head">
						<h2>Coming up</h2>
						<?php if ($when !== 'upcoming'): ?>
							<a class="p411-more" href="<?= e_url($ev_filter_url('upcoming')) ?>">All &rarr;</a>
						<?php endif; ?>
					</div>
					<?php if (!empty($upcoming_events)): ?>
						<div class="p411-rail">
							<?php foreach ($upcoming_events as $item): ?>
								<?php $item_ts = $ev_ts($item); ?>
								<a class="p411-rail__item" href="<?= e_url($ev_url($item)) ?>">
									<?php if (!empty($item->event_image)): ?>
										<span class="p411-rail__thumb"><?= responsive_img($item->event_image, (string) ($item->event_name ?? '')) ?></span>
									<?php else: ?>
										<span class="p411-rail__thumb" style="background:var(--p411-bu);" aria-hidden="true"><?= $item_ts ? e(date('d', $item_ts)) : '&bull;' ?></span>
									<?php endif; ?>
									<span class="p411-rail__body">
										<b><?= e($item->event_name ?? '') ?></b>
										<span><?= $item_ts ? e(date('D, j M', $item_ts)) : e($item->event_address ?? 'Pearland area') ?></span>
									</span>
									<span class="p411-rail__go" aria-hidden="true"><i class="material-icons">chevron_right</i></span>
								</a>
							<?php endforeach; ?>
						</div>
					<?php else: ?>
						<div class="p411-empty p411-empty--inline">
							<h3>Nothing scheduled yet</h3>
							<p>No future events have been submitted. Yours would be the first &mdash; and it's free to list.</p>
							<div class="p411-empty__actions">
								<a class="p411-btn p411-btn--sm" href="<?= e_url(base_url('user/add_user_event')) ?>">Submit an event</a>
							</div>
						</div>
					<?php endif; ?>
				</div>

				<?php $slot = $ad_slots['side_2'];
				$slot_alias = array('right-banner-1', 'right-banner-2');
				$slot_fallback_partial = 'banner_design_promo.php';
				$slot_creative = 'banner-design';
				include APPPATH . 'views/partials/ad_slot.php'; ?>

				<!-- Submit your event -->
				<div class="p411-card">
					<div class="p411-card__head">
						<h2>Running an event?</h2>
					</div>
					<p class="p411-form__hint">
						Add it to the Pearland411 calendar and it shows up here, on the homepage and in search &mdash;
						with your date, venue and contact details.
					</p>
					<a class="p411-btn p411-btn--block" href="<?= e_url(base_url('user/add_user_event')) ?>">Submit your event</a>
					<a class="p411-btn p411-btn--block p411-btn--ghost" style="margin-top:8px;" href="<?= e_url(base_url('contact-us')) ?>">Ask us about promotion</a>
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
				<h2>Get your event in front of Pearland</h2>
				<p>Listing is free. When you want more than a listing, we can put you on the homepage, in the newsletter or on a banner.</p>
			</div>
			<div class="p411-feat4">
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/></svg></i>
					<h3>List your event</h3>
					<p>Date, time, venue and contact details on a page of its own.</p>
				</div>
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 2.6 5.6 6.4.8-4.6 4.3 1.1 6.3L12 17l-5.5 3 1.1-6.3L3 9.4l6.4-.8z"/></svg></i>
					<h3>Reach further</h3>
					<p>Featured placement pins your event to the top of this page.</p>
				</div>
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 15h10M7 11h6"/></svg></i>
					<h3>Advertise here</h3>
					<p>Banner positions on this page and across the site, from $20 a month.</p>
				</div>
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="14" rx="2"/><path d="M8 22h8M12 18v4"/></svg></i>
					<h3>Websites &amp; local SEO</h3>
					<p>Fast, mobile-first sites built and hosted for Pearland businesses.</p>
				</div>
			</div>
			<div class="p411-owner__cta">
				<a class="p411-btn" href="<?= e_url(base_url('user/add_user_event')) ?>">Submit an event</a>
				<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('pricing-plan')) ?>">See all packages</a>
				<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('contact-us')) ?>">Talk to us</a>
			</div>
		</div>
	</div>
</section>
