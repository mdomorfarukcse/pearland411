<?php
/**
 * /event-details/<slug> — one event.
 *
 * Same shell as listing-details: a hero band carrying the identity and the
 * actions, then a main column and a right-hand sidebar. Styling is in
 * assets/css/directory.css (the shared kit) + assets/css/content.css, linked
 * through $page_css. No <style> block here — the previous version carried
 * ~300 lines of them, including a second copy of the sidebar promo card.
 *
 * From User::event_details(): $event, $logged_id, $user, $related_events,
 * $event_host, $upcoming_events, $ad_slots.
 */

if (empty($event)) {
	// Belt and braces: the controller show_404()s before reaching the view.
	echo '<div class="container" style="padding:60px 0;"><h1>Event not found</h1></div>';
	return;
}

$logged_id       = isset($logged_id) ? $logged_id : null;
$user            = isset($user) ? $user : null;
$related_events  = (isset($related_events)  && is_array($related_events))  ? $related_events  : array();
$upcoming_events = (isset($upcoming_events) && is_array($upcoming_events)) ? $upcoming_events : array();
$event_host      = (isset($event_host)      && is_array($event_host))      ? $event_host      : array();

$ad_slots = (isset($ad_slots) && is_array($ad_slots)) ? $ad_slots : array(
	'top'      => 'top-banner-site-wide',
	'inline'   => 'middle-banner-category',
	'side_1'   => 'right-side-banner-1',
	'side_2'   => 'right-side-banner-2',
	'side_sky' => 'right-panel-skyscraper',
	'bottom'   => 'bottom-banner-site-wide',
);

/* ---- normalised fields ------------------------------------------------- */

$ev_name  = (string) ($event->event_name ?? '');
$ev_slug  = !empty($event->event_slug) ? (string) $event->event_slug : pearland_slug($ev_name);
$ev_link  = base_url('event-details/' . rawurlencode($ev_slug));
$ev_image = !empty($event->event_image) ? base_url($event->event_image) : '';
$ev_cover = $ev_image !== '' ? $ev_image : base_url('assets/images/event-bg.jpg');

$ev_start_ts = !empty($event->event_start_date) ? strtotime((string) $event->event_start_date) : false;
$ev_end_ts   = !empty($event->event_end_date)   ? strtotime((string) $event->event_end_date)   : false;
$ev_upcoming = $ev_start_ts && $ev_start_ts >= strtotime('today');

$ev_address = trim((string) ($event->event_address ?? ''));
$ev_phone   = trim((string) ($event->event_mobile ?? ''));
$ev_email   = trim((string) ($event->event_email ?? ''));
$ev_website = trim((string) ($event->event_website ?? ''));
$ev_contact = trim((string) ($event->event_contact_name ?? ''));
$ev_time    = trim((string) ($event->event_time ?? ''));

// Stored as a pasted Google Maps embed snippet. embed_src() takes the src out
// of it so the page builds its own iframe rather than echoing admin markup.
$ev_map = embed_src($event->event_map ?? '');

$ev_about = content_text($event->event_description ?? '');

/**
 * "Add to Google Calendar" for an all-day entry on the event's date.
 *
 * event_time is free text ("4:00 PM – 9:00 PM", "6PM till late"), so it can't
 * be turned into a reliable start/end timestamp. The all-day entry is honest,
 * always right, and carries the written time in the description.
 */
$ev_calendar = '';
if ($ev_start_ts) {
	$cal_end = $ev_end_ts && $ev_end_ts >= $ev_start_ts ? $ev_end_ts : $ev_start_ts;
	$details = trim(($ev_time !== '' ? 'Time: ' . $ev_time . "\n\n" : '')
		. content_excerpt($event->event_description ?? '', 600)
		. "\n\n" . $ev_link);
	$ev_calendar = 'https://calendar.google.com/calendar/render?' . http_build_query(array(
		'action'   => 'TEMPLATE',
		'text'     => $ev_name,
		'dates'    => date('Ymd', $ev_start_ts) . '/' . date('Ymd', strtotime('+1 day', $cal_end)),
		'details'  => $details,
		'location' => $ev_address,
	));
}

$ev_directions = $ev_address !== ''
	? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($ev_address)
	: '';

/* ---- host -------------------------------------------------------------- */

$host_name   = !empty($event_host['name'])   ? (string) $event_host['name']   : 'Pearland411';
$host_image  = !empty($event_host['image'])  ? (string) $event_host['image']  : '';
$host_joined = !empty($event_host['joined']) ? (string) $event_host['joined'] : '';

/* ---- sharing ----------------------------------------------------------- */

$share_url   = rawurlencode($ev_link);
$share_title = rawurlencode($ev_name);
$share_image = rawurlencode($ev_image !== '' ? $ev_image : base_url('assets/images/pearland-logo.png'));

/* ---- in-page nav (only the sections that exist) ------------------------ */

$ev_nav = array('about' => 'About');
$ev_nav['details'] = 'Event details';
if ($ev_map !== '' || $ev_address !== '') $ev_nav['location'] = 'Location';
$ev_nav['enquire'] = 'Contact the organiser';
if ($related_events)                      $ev_nav['related']  = 'More events';
?>

<section class="p411-ct p411-ct--event-details">

	<!-- ============================================================ HERO -->
	<div class="p411-hero">
		<div class="p411-hero__bg" style="background-image:url('<?= e_attr($ev_cover) ?>');" aria-hidden="true"></div>
		<div class="container">
			<nav class="p411-crumb" aria-label="Breadcrumb">
				<a href="<?= base_url() ?>">Home</a>
				<span aria-hidden="true">/</span>
				<a href="<?= e_url(base_url('event')) ?>">Events</a>
				<span aria-hidden="true">/</span>
				<span class="is-current"><?= e($ev_name) ?></span>
			</nav>

			<div class="p411-ct-badges">
				<?php if ($ev_upcoming): ?>
					<span class="p411-tag p411-tag--green"><i class="material-icons" aria-hidden="true">event_available</i> Upcoming</span>
				<?php elseif ($ev_start_ts): ?>
					<span class="p411-tag">This event has passed</span>
				<?php endif; ?>
				<?php if ($ev_start_ts): ?>
					<span class="p411-tag"><?= e(date('D, j M Y', $ev_start_ts)) ?></span>
				<?php endif; ?>
				<?php if ($ev_time !== ''): ?>
					<span class="p411-tag"><?= e($ev_time) ?></span>
				<?php endif; ?>
			</div>

			<h1><?= e($ev_name) ?></h1>

			<div class="p411-ct-facts">
				<?php if ($ev_start_ts): ?>
					<span><i class="material-icons" aria-hidden="true">event</i><?= e(date('l, j F Y', $ev_start_ts)) ?><?= $ev_end_ts && $ev_end_ts > $ev_start_ts ? ' &ndash; ' . e(date('j F Y', $ev_end_ts)) : '' ?></span>
				<?php endif; ?>
				<?php if ($ev_time !== ''): ?>
					<span><i class="material-icons" aria-hidden="true">schedule</i><?= e($ev_time) ?></span>
				<?php endif; ?>
				<?php if ($ev_address !== ''): ?>
					<span><i class="material-icons" aria-hidden="true">place</i><?= e($ev_address) ?></span>
				<?php endif; ?>
				<?php if ($ev_phone !== ''): ?>
					<a href="tel:<?= e_attr(preg_replace('/[^0-9+]/', '', $ev_phone)) ?>"><i class="material-icons" aria-hidden="true">call</i><?= e($ev_phone) ?></a>
				<?php endif; ?>
				<?php if ($ev_email !== ''): ?>
					<a href="mailto:<?= e_attr($ev_email) ?>"><i class="material-icons" aria-hidden="true">mail_outline</i><?= e($ev_email) ?></a>
				<?php endif; ?>
				<?php if ($ev_website !== ''): ?>
					<a href="<?= e_url($ev_website) ?>" target="_blank" rel="noopener nofollow"><i class="material-icons" aria-hidden="true">language</i>Event website</a>
				<?php endif; ?>
			</div>

			<div class="p411-ct-cta">
				<?php if ($ev_calendar !== '' && $ev_upcoming): ?>
					<a class="p411-btn" href="<?= e_url($ev_calendar) ?>" target="_blank" rel="noopener">
						<i class="material-icons" style="font-size:17px;" aria-hidden="true">calendar_today</i> Add to calendar
					</a>
				<?php endif; ?>
				<?php if ($ev_directions !== ''): ?>
					<a class="p411-btn p411-btn--ghost" href="<?= e_url($ev_directions) ?>" target="_blank" rel="noopener">Get directions</a>
				<?php endif; ?>
				<?php if ($ev_phone !== ''): ?>
					<a class="p411-btn p411-btn--ghost" href="tel:<?= e_attr(preg_replace('/[^0-9+]/', '', $ev_phone)) ?>">Call the organiser</a>
				<?php endif; ?>
				<?php if ($ev_website !== ''): ?>
					<a class="p411-btn p411-btn--ghost" href="<?= e_url($ev_website) ?>" target="_blank" rel="noopener nofollow">Tickets &amp; info</a>
				<?php endif; ?>
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

				<nav class="p411-ct-jump" aria-label="On this page">
					<?php foreach ($ev_nav as $anchor => $label): ?>
						<a href="#<?= e_attr($anchor) ?>"><?= e($label) ?></a>
					<?php endforeach; ?>
				</nav>

				<div class="p411-ct-body">

					<?php if ($ev_image !== ''): ?>
						<figure class="p411-ct-figure" style="margin-bottom:0;">
							<?= responsive_img($event->event_image, $ev_name, array('fetchpriority' => 'high')) ?>
						</figure>
					<?php endif; ?>

					<!-- ABOUT -->
					<div class="p411-card" id="about">
						<div class="p411-card__head">
							<h2>About this event</h2>
						</div>
						<?php if ($ev_about !== ''): ?>
							<?php // Descriptions are stored with pasted markup. content_text()
							      // turns the block tags into newlines and strips the rest, so
							      // the page reads as text instead of printing "<p>". ?>
							<div class="p411-prose"><?= nl2br(e($ev_about)) ?></div>
						<?php else: ?>
							<div class="p411-empty p411-empty--inline">
								<h3>No description yet</h3>
								<p>The organiser hasn&rsquo;t written one. The date, time and address below are all confirmed &mdash; call or email them for the rest.</p>
							</div>
						<?php endif; ?>
					</div>

					<!-- EVENT DETAILS -->
					<div class="p411-card" id="details">
						<div class="p411-card__head">
							<h2>Event details</h2>
						</div>
						<?php
						$rows = array();
						$rows['Event'] = e($ev_name);
						if ($ev_start_ts)      $rows['Date']    = e(date('l, j F Y', $ev_start_ts));
						if ($ev_end_ts && $ev_end_ts > $ev_start_ts) $rows['Ends'] = e(date('l, j F Y', $ev_end_ts));
						if ($ev_time !== '')   $rows['Time']    = e($ev_time);
						if ($ev_address !== '') $rows['Address'] = e($ev_address);
						if ($ev_contact !== '') $rows['Contact'] = e($ev_contact);
						if ($ev_phone !== '')   $rows['Phone']   = '<a href="tel:' . e_attr(preg_replace('/[^0-9+]/', '', $ev_phone)) . '">' . e($ev_phone) . '</a>';
						if ($ev_email !== '')   $rows['Email']   = '<a href="mailto:' . e_attr($ev_email) . '">' . e($ev_email) . '</a>';
						if ($ev_website !== '') $rows['Website'] = '<a href="' . e_url($ev_website) . '" target="_blank" rel="noopener nofollow">' . e($ev_website) . '</a>';
						$rows['Hosted by'] = e($host_name);
						?>
						<ul class="p411-info">
							<?php foreach ($rows as $label => $value): ?>
								<li><?= $label ?><span><?= $value ?></span></li>
							<?php endforeach; ?>
						</ul>

						<div class="p411-ct-map__actions" style="margin-top:16px;">
							<?php if ($ev_calendar !== ''): ?>
								<a class="p411-btn p411-btn--sm" href="<?= e_url($ev_calendar) ?>" target="_blank" rel="noopener">Add to Google Calendar</a>
							<?php endif; ?>
							<?php if ($ev_directions !== ''): ?>
								<a class="p411-btn p411-btn--sm p411-btn--ghost" href="<?= e_url($ev_directions) ?>" target="_blank" rel="noopener">Open in Google Maps</a>
							<?php endif; ?>
						</div>
					</div>

					<!-- LOCATION -->
					<?php if ($ev_map !== '' || $ev_address !== ''): ?>
						<div class="p411-card" id="location">
							<div class="p411-card__head">
								<h2>Location</h2>
							</div>
							<?php if ($ev_map !== ''): ?>
								<div class="p411-ct-map">
									<iframe src="<?= e_url($ev_map) ?>" title="Map showing <?= e_attr($ev_name) ?>"
											allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
								</div>
								<?php if ($ev_address !== ''): ?>
									<p class="p411-form__hint" style="margin:12px 0 0;"><?= e($ev_address) ?></p>
								<?php endif; ?>
							<?php else: ?>
								<div class="p411-empty p411-empty--inline">
									<h3>No map pin yet</h3>
									<p>The organiser hasn&rsquo;t embedded a map, but the address is <?= e($ev_address) ?>.</p>
									<div class="p411-empty__actions">
										<a class="p411-btn p411-btn--sm p411-btn--ghost" href="<?= e_url($ev_directions) ?>" target="_blank" rel="noopener">Find it on Google Maps</a>
									</div>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<!-- IN-CONTENT AD -->
					<div class="p411-adrow" style="margin:0;">
						<?php $slot = $ad_slots['inline'];
						include APPPATH . 'views/partials/ad_slot.php'; ?>
					</div>

					<!-- SHARE -->
					<div class="p411-card" id="share">
						<div class="p411-card__head">
							<h2>Share this event</h2>
						</div>
						<p class="p411-form__hint">Know someone who'd go? Send it to them.</p>
						<div class="p411-share" aria-label="Share this event">
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
							   onclick="p411CopyLink(<?= e_attr(json_encode($ev_link)) ?>)">
								<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
									<g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
										<path d="M10.7 13.3a3 3 0 0 0 4.24 0l2.12-2.12a3 3 0 0 0-4.24-4.24L11.4 8.36"/>
										<path d="M13.3 10.7a3 3 0 0 0-4.24 0L6.94 12.8a3 3 0 0 0 4.24 4.24l1.42-1.42"/>
									</g>
								</svg>
							</a>
						</div>
					</div>

					<!-- RELATED -->
					<div class="p411-card" id="related">
						<div class="p411-card__head">
							<h2>More events</h2>
							<a class="p411-more" href="<?= e_url(base_url('event')) ?>">See all &rarr;</a>
						</div>

						<?php if ($related_events): ?>
							<div class="p411-rel">
								<?php foreach ($related_events as $rel): ?>
									<?php
									$rel_slug = !empty($rel->event_slug) ? (string) $rel->event_slug : pearland_slug($rel->event_name ?? '');
									$rel_url  = base_url('event-details/' . rawurlencode($rel_slug));
									$rel_ts   = !empty($rel->event_start_date) ? strtotime((string) $rel->event_start_date) : false;
									?>
									<a class="p411-rel__item" href="<?= e_url($rel_url) ?>">
										<div class="p411-rel__thumb">
											<?php if (!empty($rel->event_image)): ?>
												<?= responsive_img($rel->event_image, (string) ($rel->event_name ?? '')) ?>
											<?php else: ?>
												<img src="<?= base_url('assets/images/placeholder-listing.svg') ?>" alt="" loading="lazy" decoding="async">
											<?php endif; ?>
										</div>
										<div class="p411-rel__body">
											<b><?= e($rel->event_name ?? '') ?></b>
											<span><?= $rel_ts ? e(date('D, j M Y', $rel_ts)) : e($rel->event_address ?? 'Pearland area') ?></span>
										</div>
									</a>
								<?php endforeach; ?>
							</div>
						<?php else: ?>
							<div class="p411-empty p411-empty--inline">
								<h3>This is the only event listed</h3>
								<p>Nothing else is on the Pearland411 calendar yet. If you're running something, listing it is free.</p>
								<div class="p411-empty__actions">
									<a class="p411-btn p411-btn--sm" href="<?= e_url(base_url('user/add_user_event')) ?>">Submit an event</a>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<!-- ================================================= SIDEBAR -->
			<aside class="p411-shell__side">

				<!-- Contact the organiser -->
				<div class="p411-card p411-form" id="enquire">
					<div class="p411-card__head">
						<h2>Contact the organiser</h2>
					</div>

					<?php $__msg = $this->session->flashdata('message'); ?>
					<?php if (!empty($__msg)): ?>
						<div class="p411-note p411-note--ok"><?= e($__msg) ?></div>
					<?php endif; ?>

					<div id="ev_enq_ok"   class="p411-note p411-note--ok"  style="display:none;">Thanks &mdash; your message has been sent.</div>
					<div id="ev_enq_fail" class="p411-note p411-note--err" style="display:none;">Sorry, that didn't send. Please try again.</div>

					<?php if (empty($logged_id)): ?>
						<p class="p411-form__hint">Log in to message the organiser &mdash; it keeps your details and their inbox tidy.</p>
						<a class="p411-btn p411-btn--block" href="<?= e_url(base_url('login')) ?>">Log in to enquire</a>
						<a class="p411-btn p411-btn--block p411-btn--ghost" style="margin-top:8px;" href="<?= e_url(base_url('register')) ?>">Create an account</a>
						<?php if ($ev_phone !== ''): ?>
							<p class="p411-form__hint" style="margin:12px 0 0;">In a hurry? Call <a href="tel:<?= e_attr(preg_replace('/[^0-9+]/', '', $ev_phone)) ?>"><?= e($ev_phone) ?></a>.</p>
						<?php endif; ?>
					<?php else: ?>
						<p class="p411-form__hint">Ask about tickets, timings, parking &mdash; whatever you need to know.</p>
						<form method="post" name="ev_enquiry_form" id="ev_enquiry_form"
							  action="<?= base_url('user/add-enquiry/event_details/' . rawurlencode($ev_slug)) ?>">
							<input type="hidden" name="listing_id" value="">
							<input type="hidden" name="event_id" value="<?= e_attr($ev_slug) ?>">
							<input type="hidden" name="blog_id" value="">
							<input type="hidden" name="product_id" value="">
							<input type="hidden" name="receiving_user_id" value="<?= (int) ($event->user_id ?? 0) ?>">
							<input type="hidden" name="enquiry_sender_id" value="<?= (int) $logged_id ?>">
							<input type="hidden" name="enquiry_source" value="<?= e_attr($ev_name) ?>">
							<input type="hidden" name="enquiry_type" value="event">

							<div class="form-group">
								<label class="sr-only" for="ev_enq_name">Your name</label>
								<input id="ev_enq_name" type="text" name="enquiry_name" class="form-control" required
									   placeholder="Enter name*" value="<?= e_attr($user->name ?? '') ?>">
							</div>
							<div class="form-group">
								<label class="sr-only" for="ev_enq_email">Email</label>
								<input id="ev_enq_email" type="email" name="enquiry_email" class="form-control" required
									   placeholder="Enter email*" value="<?= e_attr($user->email_id ?? '') ?>">
							</div>
							<div class="form-group">
								<label class="sr-only" for="ev_enq_phone">Phone</label>
								<input id="ev_enq_phone" type="tel" name="enquiry_mobile" class="form-control" required
									   placeholder="Enter mobile number*" pattern="[0-9+\-\s()]{7,20}"
									   title="7 to 20 characters: digits, spaces, + ( ) and - are allowed"
									   value="<?= e_attr($user->mobile_number ?? '') ?>">
							</div>
							<div class="form-group">
								<label class="sr-only" for="ev_enq_msg">Your message</label>
								<textarea id="ev_enq_msg" name="enquiry_message" class="form-control" rows="3"
										  placeholder="What would you like to know?"></textarea>
							</div>
							<button type="submit" id="ev_enquiry_submit" name="enquiry_submit" class="p411-btn p411-btn--block">Send message</button>
						</form>
					<?php endif; ?>
				</div>

				<!-- Host -->
				<div class="p411-card">
					<div class="p411-card__head">
						<h2>Hosted by</h2>
					</div>
					<div class="p411-profile">
						<div class="p411-profile__img">
							<?php if ($host_image !== ''): ?>
								<img src="<?= e_attr($host_image) ?>" alt="<?= e_attr($host_name) ?>" loading="lazy" decoding="async">
							<?php else: ?>
								<img src="<?= base_url('assets/images/fallback_profile.svg') ?>" alt="" loading="lazy" decoding="async">
							<?php endif; ?>
						</div>
						<h3><?= e($host_name) ?></h3>
						<?php if ($host_joined !== ''): ?><p>On Pearland411 since <?= e($host_joined) ?></p><?php endif; ?>
						<?php if ($ev_contact !== '' && $ev_contact !== $host_name): ?>
							<p>Contact person: <?= e($ev_contact) ?></p>
						<?php endif; ?>
					</div>
				</div>

				<?php $slot = $ad_slots['side_1'];
				$slot_alias = 'events-sidebar';
				$slot_creative = 'house-ad';
				include APPPATH . 'views/partials/ad_slot.php'; ?>

				<?php include APPPATH . 'views/partials/service_promo.php'; ?>

				<!-- Coming up -->
				<?php if (!empty($upcoming_events)): ?>
					<div class="p411-card">
						<div class="p411-card__head">
							<h2>Coming up</h2>
							<a class="p411-more" href="<?= e_url(base_url('event')) ?>">All &rarr;</a>
						</div>
						<div class="p411-rail">
							<?php foreach ($upcoming_events as $item): ?>
								<?php
								$item_slug = !empty($item->event_slug) ? (string) $item->event_slug : pearland_slug($item->event_name ?? '');
								$item_ts   = !empty($item->event_start_date) ? strtotime((string) $item->event_start_date) : false;
								?>
								<a class="p411-rail__item" href="<?= e_url(base_url('event-details/' . rawurlencode($item_slug))) ?>">
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
</section>

<script>
	(function () {
		'use strict';

		// Smooth-scroll the jump chips.
		Array.prototype.forEach.call(document.querySelectorAll('.p411-ct-jump a[href^="#"]'), function (link) {
			link.addEventListener('click', function (e) {
				var target = document.getElementById(this.getAttribute('href').slice(1));
				if (!target) return;
				e.preventDefault();
				target.scrollIntoView({ behavior: 'smooth', block: 'start' });
			});
		});

		// Enquiry form: post in the background so the reader keeps their place.
		// The CSRF hook has already injected its hidden token into this form,
		// and FormData carries it along.
		var form = document.getElementById('ev_enquiry_form');
		if (!form) return;

		var ok   = document.getElementById('ev_enq_ok');
		var fail = document.getElementById('ev_enq_fail');
		var btn  = document.getElementById('ev_enquiry_submit');

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

	// Copy the event URL. Called inline from the share row.
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
