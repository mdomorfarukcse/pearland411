<?php
/**
 * /video-details/<slug> — watch one video.
 *
 * Same shell as listing-details: a hero band with the title and meta, then a
 * main column and a right-hand sidebar. Styling is in
 * assets/css/directory.css (the shared kit) + assets/css/content.css, linked
 * through $page_css. No <style> block — the previous version carried ~200
 * lines of them plus a hand-copied marketing card.
 *
 * From User::video_details(): $video, $logged_id, $user, $related_videos,
 * $share_urls, $popular_videos, $ad_slots.
 */

if (empty($video)) {
	echo '<div class="container" style="padding:60px 0;"><h1>Video not found</h1></div>';
	return;
}

$logged_id      = isset($logged_id) ? $logged_id : null;
$user           = isset($user) ? $user : null;
$related_videos = (isset($related_videos) && is_array($related_videos)) ? $related_videos : array();
$popular_videos = (isset($popular_videos) && is_array($popular_videos)) ? $popular_videos : array();

$ad_slots = (isset($ad_slots) && is_array($ad_slots)) ? $ad_slots : array(
	'top'      => 'top-banner-site-wide',
	'inline'   => 'middle-banner-category',
	'side_1'   => 'right-side-banner-1',
	'side_2'   => 'right-side-banner-2',
	'side_sky' => 'right-panel-skyscraper',
	'bottom'   => 'bottom-banner-site-wide',
);

/* ---- normalised fields ------------------------------------------------- */

$vd_title = (string) ($video->video_title ?? '');
$vd_key   = !empty($video->video_slug) ? (string) $video->video_slug : (string) ($video->video_id ?? '');
$vd_link  = base_url('video-details/' . rawurlencode($vd_key));
$vd_date  = !empty($video->created_at) ? strtotime((string) $video->created_at) : false;
$vd_views = (int) ($video->views ?? 0);

// video_code is meant to be the bare YouTube id, but the admin form takes free
// text, so a watch URL or a share link turns up too. An empty result means
// there is nothing to embed and the page says so rather than framing a 404.
$vd_embed = youtube_embed_url($video->video_code ?? '', array('rel' => 0, 'modestbranding' => 1));
$vd_watch = youtube_id($video->video_code ?? '') !== ''
	? 'https://www.youtube.com/watch?v=' . youtube_id($video->video_code ?? '')
	: '';
$vd_poster = !empty($video->video_image) ? base_url($video->video_image) : youtube_thumb_url($video->video_code ?? '', 'maxresdefault');

$vd_cat_name = (string) ($video->category_name ?? '');
$vd_cat_slug = (string) ($video->category_slug ?? '');
if ($vd_cat_slug === '' && $vd_cat_name !== '') $vd_cat_slug = pearland_slug($vd_cat_name);
$vd_cat_url  = $vd_cat_slug !== '' ? base_url('videos') . '?' . http_build_query(array('category' => $vd_cat_slug)) : '';

$vd_body = content_text($video->video_description ?? '');

$vd_placeholder = base_url('assets/images/placeholder-listing.svg');

/* ---- sharing ----------------------------------------------------------- */
/* Rebuilt against the canonical short URL, like the news page — the
   controller's $share_urls point at the long /user/video-details form. */

$share_url_enc   = rawurlencode($vd_link);
$share_title_enc = rawurlencode($vd_title);
$share_image_enc = rawurlencode($vd_poster !== '' ? $vd_poster : base_url('assets/images/pearland-logo.png'));

$share = array(
	'facebook'  => 'https://www.facebook.com/sharer/sharer.php?u=' . $share_url_enc,
	'twitter'   => 'https://twitter.com/intent/tweet?url=' . $share_url_enc . '&text=' . $share_title_enc,
	'whatsapp'  => 'https://wa.me/?text=' . $share_title_enc . '%20-%20' . $share_url_enc,
	'linkedin'  => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $share_url_enc,
	'pinterest' => 'https://pinterest.com/pin/create/button/?url=' . $share_url_enc . '&media=' . $share_image_enc . '&description=' . $share_title_enc,
);

/** Watch URL for a related/popular row. */
$vd_url = function ($row) {
	$key = !empty($row->video_slug) ? (string) $row->video_slug : (string) ($row->video_id ?? '');
	return base_url('video-details/' . rawurlencode($key));
};

/** Uploaded still, else YouTube's own. */
$vd_thumb = function ($row) {
	if (!empty($row->video_image)) return base_url($row->video_image);
	return youtube_thumb_url($row->video_code ?? '');
};
?>

<section class="p411-ct p411-ct--video-details">

	<!-- ============================================================ HERO -->
	<div class="p411-hero">
		<div class="p411-hero__bg" style="background-image:url('<?= e_attr($vd_poster !== '' ? $vd_poster : base_url('assets/images/home-bg2.jpg')) ?>');" aria-hidden="true"></div>
		<div class="container">
			<nav class="p411-crumb" aria-label="Breadcrumb">
				<a href="<?= base_url() ?>">Home</a>
				<span aria-hidden="true">/</span>
				<a href="<?= e_url(base_url('videos')) ?>">Videos</a>
				<?php if ($vd_cat_name !== ''): ?>
					<span aria-hidden="true">/</span>
					<a href="<?= e_url($vd_cat_url) ?>"><?= e($vd_cat_name) ?></a>
				<?php endif; ?>
				<span aria-hidden="true">/</span>
				<span class="is-current"><?= e($vd_title) ?></span>
			</nav>

			<?php if ($vd_cat_name !== '' || $vd_date): ?>
				<div class="p411-ct-badges">
					<?php if ($vd_cat_name !== ''): ?>
						<a class="p411-tag p411-tag--green" href="<?= e_url($vd_cat_url) ?>"><?= e($vd_cat_name) ?></a>
					<?php endif; ?>
					<?php if ($vd_date): ?>
						<span class="p411-tag"><?= e(date('j M Y', $vd_date)) ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<h1><?= e($vd_title) ?></h1>

			<div class="p411-ct-facts">
				<?php if ($vd_date): ?>
					<span><i class="material-icons" aria-hidden="true">event</i>Published <?= e(date('j F Y', $vd_date)) ?></span>
				<?php endif; ?>
				<?php if ($vd_views > 0): ?>
					<span><i class="material-icons" aria-hidden="true">visibility</i><?= number_format($vd_views) ?> views</span>
				<?php endif; ?>
				<?php if ($vd_watch !== ''): ?>
					<a href="<?= e_url($vd_watch) ?>" target="_blank" rel="noopener nofollow"><i class="material-icons" aria-hidden="true">open_in_new</i>Watch on YouTube</a>
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

				<div class="p411-ct-body">

					<!-- PLAYER -->
					<div class="p411-card" id="watch">
						<?php if ($vd_embed !== ''): ?>
							<div class="p411-ct-player">
								<iframe src="<?= e_url($vd_embed) ?>" title="<?= e_attr($vd_title) ?>"
										allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
										allowfullscreen loading="lazy"></iframe>
							</div>
						<?php else: ?>
							<div class="p411-empty p411-empty--inline">
								<h3>This video can't be played here</h3>
								<p>
									No usable video id is saved against this entry, so there is nothing to embed.
									If it's yours, edit the video and paste the YouTube link again.
								</p>
								<div class="p411-empty__actions">
									<a class="p411-btn p411-btn--sm p411-btn--ghost" href="<?= e_url(base_url('videos')) ?>">Back to the library</a>
								</div>
							</div>
						<?php endif; ?>

						<div class="p411-ct-byline" style="margin:16px 0 0;padding-bottom:0;border-bottom:0;">
							<?php if ($vd_date): ?>
								<span><i class="material-icons" aria-hidden="true">event</i><?= e(date('j M Y', $vd_date)) ?></span>
							<?php endif; ?>
							<?php if ($vd_views > 0): ?>
								<span><i class="material-icons" aria-hidden="true">visibility</i><?= number_format($vd_views) ?> views</span>
							<?php endif; ?>
							<?php if ($vd_cat_name !== ''): ?>
								<span><i class="material-icons" aria-hidden="true">label_outline</i><a href="<?= e_url($vd_cat_url) ?>"><?= e($vd_cat_name) ?></a></span>
							<?php endif; ?>
						</div>
					</div>

					<!-- ABOUT -->
					<div class="p411-card" id="about">
						<div class="p411-card__head">
							<h2>About this video</h2>
						</div>
						<?php if ($vd_body !== ''): ?>
							<div class="p411-prose"><?= nl2br(e($vd_body)) ?></div>
						<?php else: ?>
							<div class="p411-empty p411-empty--inline">
								<h3>No description</h3>
								<p>Nothing was written up for this one &mdash; the video speaks for itself.</p>
							</div>
						<?php endif; ?>

						<div class="p411-share" style="margin-top:22px;" aria-label="Share this video">
							<a class="so-fb" target="_blank" rel="noopener" title="Share on Facebook" href="<?= e_url($share['facebook']) ?>"
							   onclick="window.open(this.href,'share-fb','width=800,height=600');return false;">
								<img src="<?= base_url('assets/images/social/3.png') ?>" alt="Facebook" loading="lazy"></a>
							<a class="so-tw" target="_blank" rel="noopener" title="Share on X / Twitter" href="<?= e_url($share['twitter']) ?>"
							   onclick="window.open(this.href,'share-tw','width=800,height=600');return false;">
								<img src="<?= base_url('assets/images/social/2.png') ?>" alt="Twitter" loading="lazy"></a>
							<a class="so-wa" target="_blank" rel="noopener" title="Share on WhatsApp" href="<?= e_url($share['whatsapp']) ?>">
								<img src="<?= base_url('assets/images/social/6.png') ?>" alt="WhatsApp" loading="lazy"></a>
							<a class="so-li" target="_blank" rel="noopener" title="Share on LinkedIn" href="<?= e_url($share['linkedin']) ?>"
							   onclick="window.open(this.href,'share-li','width=800,height=600');return false;">
								<img src="<?= base_url('assets/images/social/1.png') ?>" alt="LinkedIn" loading="lazy"></a>
							<a class="so-pi" target="_blank" rel="noopener" title="Share on Pinterest" href="<?= e_url($share['pinterest']) ?>"
							   onclick="window.open(this.href,'share-pi','width=800,height=600');return false;">
								<img src="<?= base_url('assets/images/social/9.png') ?>" alt="Pinterest" loading="lazy"></a>
							<a class="so-link" href="javascript:void(0)" title="Copy link" onclick="p411CopyLink(<?= e_attr(json_encode($vd_link)) ?>)">
								<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
									<g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
										<path d="M10.7 13.3a3 3 0 0 0 4.24 0l2.12-2.12a3 3 0 0 0-4.24-4.24L11.4 8.36"/>
										<path d="M13.3 10.7a3 3 0 0 0-4.24 0L6.94 12.8a3 3 0 0 0 4.24 4.24l1.42-1.42"/>
									</g>
								</svg>
							</a>
						</div>
					</div>

					<!-- IN-CONTENT AD -->
					<div class="p411-adrow" style="margin:0;">
						<?php $slot = $ad_slots['inline'];
						include APPPATH . 'views/partials/ad_slot.php'; ?>
					</div>

					<!-- RELATED -->
					<div class="p411-card" id="related">
						<div class="p411-card__head">
							<h2>Watch next</h2>
							<a class="p411-more" href="<?= e_url(base_url('videos')) ?>">All videos &rarr;</a>
						</div>

						<?php if ($related_videos): ?>
							<div class="p411-rel">
								<?php foreach ($related_videos as $rel): ?>
									<?php
									$rel_thumb = $vd_thumb($rel);
									$rel_ts    = !empty($rel->created_at) ? strtotime((string) $rel->created_at) : false;
									?>
									<a class="p411-rel__item" href="<?= e_url($vd_url($rel)) ?>">
										<div class="p411-rel__thumb" style="position:relative;">
											<img src="<?= e_attr($rel_thumb !== '' ? $rel_thumb : $vd_placeholder) ?>" alt="" loading="lazy" decoding="async">
											<span class="p411-ct-card__play" aria-hidden="true">
												<i><svg viewBox="0 0 24 24" role="presentation"><path fill="#fff" d="M8 5v14l11-7z"/></svg></i>
											</span>
										</div>
										<div class="p411-rel__body">
											<b><?= e($rel->video_title ?? '') ?></b>
											<span>
												<?= $rel_ts ? e(date('j M Y', $rel_ts)) : '' ?>
												<?= !empty($rel->views) ? ($rel_ts ? ' &middot; ' : '') . number_format((int) $rel->views) . ' views' : '' ?>
											</span>
										</div>
									</a>
								<?php endforeach; ?>
							</div>
						<?php else: ?>
							<div class="p411-empty p411-empty--inline">
								<h3>This is the only video so far</h3>
								<p>Nothing else has been published to the library yet.</p>
								<div class="p411-empty__actions">
									<a class="p411-btn p411-btn--sm p411-btn--ghost" href="<?= e_url(base_url('news')) ?>">Read the news instead</a>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<!-- ================================================= SIDEBAR -->
			<aside class="p411-shell__side">

				<!-- Enquiry -->
				<div class="p411-card p411-form" id="enquire">
					<div class="p411-card__head">
						<h2>Get in touch</h2>
					</div>

					<?php $__msg = $this->session->flashdata('message'); ?>
					<?php if (!empty($__msg)): ?>
						<div class="p411-note p411-note--ok"><?= e($__msg) ?></div>
					<?php endif; ?>

					<div id="vd_enq_ok"   class="p411-note p411-note--ok"  style="display:none;">Thanks &mdash; your message has been sent.</div>
					<div id="vd_enq_fail" class="p411-note p411-note--err" style="display:none;">Sorry, that didn't send. Please try again.</div>

					<?php if (empty($logged_id)): ?>
						<p class="p411-form__hint">Log in to ask us about this video &mdash; where it was filmed, who's in it, or whether we can make one for you.</p>
						<a class="p411-btn p411-btn--block" href="<?= e_url(base_url('login')) ?>">Log in to write to us</a>
						<a class="p411-btn p411-btn--block p411-btn--ghost" style="margin-top:8px;" href="<?= e_url(base_url('contact-us')) ?>">Or use the contact form</a>
					<?php else: ?>
						<p class="p411-form__hint">A question about this video, or about getting one made for your business.</p>
						<form method="post" name="vd_enquiry_form" id="vd_enquiry_form"
							  action="<?= base_url('user/add-enquiry/video_details/' . rawurlencode($vd_key)) ?>">
							<input type="hidden" name="listing_id" value="">
							<input type="hidden" name="event_id" value="">
							<input type="hidden" name="news_id" value="">
							<input type="hidden" name="blog_id" value="">
							<input type="hidden" name="product_id" value="">
							<input type="hidden" name="classifieds_id" value="">
							<input type="hidden" name="rental_id" value="">
							<input type="hidden" name="video_id" value="<?= e_attr($vd_key) ?>">
							<input type="hidden" name="receiving_user_id" value="<?= (int) ($video->user_id ?? 0) ?>">
							<input type="hidden" name="enquiry_sender_id" value="<?= (int) $logged_id ?>">
							<input type="hidden" name="enquiry_source" value="<?= e_attr($vd_title !== '' ? $vd_title : 'Website') ?>">
							<input type="hidden" name="enquiry_type" value="video">

							<div class="form-group">
								<label class="sr-only" for="vd_enq_name">Your name</label>
								<input id="vd_enq_name" type="text" name="enquiry_name" class="form-control" required
									   placeholder="Enter name*" value="<?= e_attr($user->name ?? '') ?>">
							</div>
							<div class="form-group">
								<label class="sr-only" for="vd_enq_email">Email</label>
								<input id="vd_enq_email" type="email" name="enquiry_email" class="form-control" required
									   placeholder="Enter email*" value="<?= e_attr($user->email_id ?? '') ?>">
							</div>
							<div class="form-group">
								<label class="sr-only" for="vd_enq_phone">Phone</label>
								<input id="vd_enq_phone" type="tel" name="enquiry_mobile" class="form-control" required
									   placeholder="Enter mobile number*" pattern="[0-9+\-\s()]{7,20}"
									   title="7 to 20 characters: digits, spaces, + ( ) and - are allowed"
									   value="<?= e_attr($user->mobile_number ?? '') ?>">
							</div>
							<div class="form-group">
								<label class="sr-only" for="vd_enq_msg">Your message</label>
								<textarea id="vd_enq_msg" name="enquiry_message" class="form-control" rows="3"
										  placeholder="Enter your query or message"></textarea>
							</div>
							<button type="submit" id="vd_enquiry_submit" name="enquiry_submit" class="p411-btn p411-btn--block">Send message</button>
						</form>
					<?php endif; ?>
				</div>

				<?php $slot = $ad_slots['side_1'];
				$slot_creative = 'house-ad';
				include APPPATH . 'views/partials/ad_slot.php'; ?>

				<?php include APPPATH . 'views/partials/service_promo.php'; ?>

				<!-- Most watched -->
				<?php if (!empty($popular_videos)): ?>
					<div class="p411-card">
						<div class="p411-card__head">
							<h2>Most watched</h2>
							<a class="p411-more" href="<?= e_url(base_url('videos')) ?>">All &rarr;</a>
						</div>
						<div class="p411-rail">
							<?php foreach ($popular_videos as $item): ?>
								<?php $item_thumb = $vd_thumb($item); ?>
								<a class="p411-rail__item" href="<?= e_url($vd_url($item)) ?>">
									<?php if ($item_thumb !== ''): ?>
										<span class="p411-rail__thumb"><img src="<?= e_attr($item_thumb) ?>" alt="" loading="lazy" decoding="async"></span>
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

		var form = document.getElementById('vd_enquiry_form');
		if (!form) return;

		var ok   = document.getElementById('vd_enq_ok');
		var fail = document.getElementById('vd_enq_fail');
		var btn  = document.getElementById('vd_enquiry_submit');

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

	// Copy the video URL. Called inline from the share row.
	function p411CopyLink(url) {
		function toast(message) {
			var el = document.createElement('div');
			el.textContent = message;
			el.setAttribute('role', 'status');
			el.style.cssText = 'position:fixed;top:20px;right:20px;z-index:10000;background:#4E8A28;color:#fff;'
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
