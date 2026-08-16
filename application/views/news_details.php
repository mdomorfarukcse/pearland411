<?php
/**
 * /news-details/<slug> — one story.
 *
 * Same shell as listing-details: a hero band with the headline and byline,
 * then a main column and a right-hand sidebar. Styling is in
 * assets/css/directory.css (the shared kit) + assets/css/content.css, linked
 * through $page_css. No <style> block — the previous version carried ~200
 * lines of them plus a second copy of the sidebar promo card.
 *
 * From User::news_details(): $news, $logged_id, $user, $related_news,
 * $share_urls, $news_author, $popular_news, $ad_slots.
 */

if (empty($news)) {
	echo '<div class="container" style="padding:60px 0;"><h1>Story not found</h1></div>';
	return;
}

$logged_id     = isset($logged_id) ? $logged_id : null;
$user          = isset($user) ? $user : null;
$related_news  = (isset($related_news)  && is_array($related_news))  ? $related_news  : array();
$popular_news  = (isset($popular_news)  && is_array($popular_news))  ? $popular_news  : array();

$ad_slots = (isset($ad_slots) && is_array($ad_slots)) ? $ad_slots : array(
	'top'      => 'top-banner-site-wide',
	'inline'   => 'middle-banner-category',
	'side_1'   => 'right-side-banner-1',
	'side_2'   => 'right-side-banner-2',
	'side_sky' => 'right-panel-skyscraper',
	'bottom'   => 'bottom-banner-site-wide',
);

/* ---- normalised fields ------------------------------------------------- */

$nw_title = (string) ($news->news_title ?? '');
$nw_key   = !empty($news->news_slug) ? (string) $news->news_slug : (string) ($news->news_id ?? '');
$nw_link  = base_url('news-details/' . rawurlencode($nw_key));
$nw_image = !empty($news->news_image) ? base_url($news->news_image) : '';
$nw_date  = !empty($news->created_at) ? strtotime((string) $news->created_at) : false;
$nw_views = (int) ($news->views ?? 0);

$nw_cat_name = (string) ($news->category_name ?? '');
$nw_cat_slug = (string) ($news->category_slug ?? '');
if ($nw_cat_slug === '' && $nw_cat_name !== '') $nw_cat_slug = pearland_slug($nw_cat_name);
$nw_cat_url  = $nw_cat_slug !== '' ? base_url('news') . '?' . http_build_query(array('category' => $nw_cat_slug)) : '';

$nw_author = isset($news_author) && $news_author !== '' ? (string) $news_author : 'Pearland411';

// The body is stored with pasted markup. content_text() turns the block tags
// into newlines and strips the rest, so the story reads as text — the old view
// ran strip_tags() over the whole thing and lost every paragraph break.
$nw_body = content_text($news->news_description ?? '');

// ~230 words a minute, rounded up, minimum one.
$nw_words = $nw_body === '' ? 0 : str_word_count($nw_body);
$nw_read  = $nw_words > 0 ? max(1, (int) ceil($nw_words / 230)) : 0;

$nw_placeholder = base_url('assets/images/placeholder-listing.svg');

/* ---- sharing ----------------------------------------------------------- */
/* The controller's $share_urls are built against the long /user/news-details
   form. Share links are what people paste into Facebook and what search
   engines then see, so they are rebuilt here against the canonical short URL
   — the same one the <link rel=canonical> and the JSON-LD carry. */

$share_url_enc   = rawurlencode($nw_link);
$share_title_enc = rawurlencode($nw_title);
$share_image_enc = rawurlencode($nw_image !== '' ? $nw_image : base_url('assets/images/pearland-logo.png'));

$share = array(
	'facebook'  => 'https://www.facebook.com/sharer/sharer.php?u=' . $share_url_enc,
	'twitter'   => 'https://twitter.com/intent/tweet?url=' . $share_url_enc . '&text=' . $share_title_enc,
	'whatsapp'  => 'https://wa.me/?text=' . $share_title_enc . '%20-%20' . $share_url_enc,
	'linkedin'  => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $share_url_enc,
	'pinterest' => 'https://pinterest.com/pin/create/button/?url=' . $share_url_enc . '&media=' . $share_image_enc . '&description=' . $share_title_enc,
);

/** Story URL for a related/popular row. */
$nw_url = function ($row) {
	$key = !empty($row->news_slug) ? (string) $row->news_slug : (string) ($row->news_id ?? '');
	return base_url('news-details/' . rawurlencode($key));
};
?>

<section class="p411-ct p411-ct--news-details">

	<!-- ============================================================ HERO -->
	<div class="p411-hero">
		<div class="p411-hero__bg" style="background-image:url('<?= e_attr($nw_image !== '' ? $nw_image : base_url('assets/images/news-bg.jpg')) ?>');" aria-hidden="true"></div>
		<div class="container">
			<nav class="p411-crumb" aria-label="Breadcrumb">
				<a href="<?= base_url() ?>">Home</a>
				<span aria-hidden="true">/</span>
				<a href="<?= e_url(base_url('news')) ?>">News</a>
				<?php if ($nw_cat_name !== ''): ?>
					<span aria-hidden="true">/</span>
					<a href="<?= e_url($nw_cat_url) ?>"><?= e($nw_cat_name) ?></a>
				<?php endif; ?>
				<span aria-hidden="true">/</span>
				<span class="is-current"><?= e($nw_title) ?></span>
			</nav>

			<?php if ($nw_cat_name !== '' || $nw_date): ?>
				<div class="p411-ct-badges">
					<?php if ($nw_cat_name !== ''): ?>
						<a class="p411-tag p411-tag--green" href="<?= e_url($nw_cat_url) ?>"><?= e($nw_cat_name) ?></a>
					<?php endif; ?>
					<?php if ($nw_date): ?>
						<span class="p411-tag"><?= e(date('j M Y', $nw_date)) ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<h1><?= e($nw_title) ?></h1>

			<div class="p411-ct-facts">
				<span><i class="material-icons" aria-hidden="true">person_outline</i><?= e($nw_author) ?></span>
				<?php if ($nw_date): ?>
					<span><i class="material-icons" aria-hidden="true">event</i><?= e(date('l, j F Y', $nw_date)) ?></span>
				<?php endif; ?>
				<?php if ($nw_read > 0): ?>
					<span><i class="material-icons" aria-hidden="true">schedule</i><?= (int) $nw_read ?> min read</span>
				<?php endif; ?>
				<?php if ($nw_views > 0): ?>
					<span><i class="material-icons" aria-hidden="true">visibility</i><?= number_format($nw_views) ?> reads</span>
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

					<!-- THE STORY -->
					<article class="p411-card" id="story">
						<?php if ($nw_image !== ''): ?>
							<figure class="p411-ct-figure">
								<?= responsive_img($news->news_image, $nw_title, array('fetchpriority' => 'high')) ?>
							</figure>
						<?php endif; ?>

						<div class="p411-ct-byline">
							<span><i class="material-icons" aria-hidden="true">person_outline</i><b><?= e($nw_author) ?></b></span>
							<?php if ($nw_date): ?>
								<span><i class="material-icons" aria-hidden="true">event</i><?= e(date('j M Y', $nw_date)) ?></span>
							<?php endif; ?>
							<?php if ($nw_read > 0): ?>
								<span><i class="material-icons" aria-hidden="true">schedule</i><?= (int) $nw_read ?> min read</span>
							<?php endif; ?>
							<?php if ($nw_cat_name !== ''): ?>
								<span><i class="material-icons" aria-hidden="true">label_outline</i><a href="<?= e_url($nw_cat_url) ?>"><?= e($nw_cat_name) ?></a></span>
							<?php endif; ?>
						</div>

						<?php if ($nw_body !== ''): ?>
							<?php /* content_html() keeps the author's own paragraphs and
							         spaces them in CSS. Flattening to text and rebuilding
							         the breaks with nl2br() made the gap between two
							         paragraphs depend on how much whitespace happened to
							         sit between the tags, so the same article read evenly
							         in one place and cluttered in another. Already
							         sanitised — do not wrap in e(). */ ?>
							<div class="p411-prose"><?= content_html($news->news_description ?? '') ?></div>
						<?php else: ?>
							<div class="p411-empty p411-empty--inline">
								<h3>This story has no text yet</h3>
								<p>The headline was published without a body. Have a look at the rest of the newsroom in the meantime.</p>
								<div class="p411-empty__actions">
									<a class="p411-btn p411-btn--sm" href="<?= e_url(base_url('news')) ?>">Back to the news</a>
								</div>
							</div>
						<?php endif; ?>

						<div class="p411-share" style="margin-top:22px;" aria-label="Share this story">
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
							<a class="so-link" href="javascript:void(0)" title="Copy link" onclick="p411CopyLink(<?= e_attr(json_encode($nw_link)) ?>)">
								<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
									<g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
										<path d="M10.7 13.3a3 3 0 0 0 4.24 0l2.12-2.12a3 3 0 0 0-4.24-4.24L11.4 8.36"/>
										<path d="M13.3 10.7a3 3 0 0 0-4.24 0L6.94 12.8a3 3 0 0 0 4.24 4.24l1.42-1.42"/>
									</g>
								</svg>
							</a>
						</div>
					</article>

					<!-- IN-CONTENT AD -->
					<div class="p411-adrow" style="margin:0;">
						<?php $slot = $ad_slots['inline'];
						include APPPATH . 'views/partials/ad_slot.php'; ?>
					</div>

					<!-- RELATED -->
					<div class="p411-card" id="related">
						<div class="p411-card__head">
							<h2>More from the newsroom</h2>
							<a class="p411-more" href="<?= e_url(base_url('news')) ?>">All news &rarr;</a>
						</div>

						<?php if ($related_news): ?>
							<div class="p411-rel">
								<?php foreach ($related_news as $rel): ?>
									<?php $rel_ts = !empty($rel->created_at) ? strtotime((string) $rel->created_at) : false; ?>
									<a class="p411-rel__item" href="<?= e_url($nw_url($rel)) ?>">
										<div class="p411-rel__thumb">
											<?php if (!empty($rel->news_image)): ?>
												<?= responsive_img($rel->news_image, (string) ($rel->news_title ?? '')) ?>
											<?php else: ?>
												<img src="<?= e_attr($nw_placeholder) ?>" alt="" loading="lazy" decoding="async">
											<?php endif; ?>
										</div>
										<div class="p411-rel__body">
											<b><?= e($rel->news_title ?? '') ?></b>
											<span>
												<?= $rel_ts ? e(date('j M Y', $rel_ts)) : '' ?>
												<?= !empty($rel->category_name) ? ($rel_ts ? ' &middot; ' : '') . e($rel->category_name) : '' ?>
											</span>
										</div>
									</a>
								<?php endforeach; ?>
							</div>
						<?php else: ?>
							<div class="p411-empty p411-empty--inline">
								<h3>This is the only story so far</h3>
								<p>Nothing else has been published yet. Events and the business directory are worth a look in the meantime.</p>
								<div class="p411-empty__actions">
									<a class="p411-btn p411-btn--sm p411-btn--ghost" href="<?= e_url(base_url('event')) ?>">See what's on</a>
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

					<div id="nw_enq_ok"   class="p411-note p411-note--ok"  style="display:none;">Thanks &mdash; your message has been sent.</div>
					<div id="nw_enq_fail" class="p411-note p411-note--err" style="display:none;">Sorry, that didn't send. Please try again.</div>

					<?php if (empty($logged_id)): ?>
						<p class="p411-form__hint">Log in to send the newsroom a message about this story &mdash; a correction, a tip-off, or a question.</p>
						<a class="p411-btn p411-btn--block" href="<?= e_url(base_url('login')) ?>">Log in to write to us</a>
						<a class="p411-btn p411-btn--block p411-btn--ghost" style="margin-top:8px;" href="<?= e_url(base_url('contact-us')) ?>">Or use the contact form</a>
					<?php else: ?>
						<p class="p411-form__hint">A correction, a tip-off or a question about this story &mdash; it comes straight to us.</p>
						<form method="post" name="nw_enquiry_form" id="nw_enquiry_form" data-validate
							  action="<?= base_url('user/add-enquiry/news_details/' . rawurlencode($nw_key)) ?>">
							<input type="hidden" name="listing_id" value="">
							<input type="hidden" name="event_id" value="">
							<input type="hidden" name="news_id" value="<?= e_attr($nw_key) ?>">
							<input type="hidden" name="blog_id" value="">
							<input type="hidden" name="product_id" value="">
							<input type="hidden" name="receiving_user_id" value="<?= (int) ($news->user_id ?? 0) ?>">
							<input type="hidden" name="enquiry_sender_id" value="<?= (int) $logged_id ?>">
							<input type="hidden" name="enquiry_source" value="<?= e_attr($nw_title) ?>">
							<input type="hidden" name="enquiry_type" value="news">

							<div class="form-group">
								<label class="sr-only" for="nw_enq_name">Your name</label>
								<input id="nw_enq_name" type="text" name="enquiry_name" class="form-control" required
									   placeholder="Enter name*" value="<?= e_attr($user->name ?? '') ?>">
							</div>
							<div class="form-group">
								<label class="sr-only" for="nw_enq_email">Email</label>
								<input id="nw_enq_email" type="email" name="enquiry_email" class="form-control" required
									   placeholder="Enter email*" value="<?= e_attr($user->email_id ?? '') ?>">
							</div>
							<div class="form-group">
								<label class="sr-only" for="nw_enq_phone">Phone</label>
								<input id="nw_enq_phone" type="tel" name="enquiry_mobile" class="form-control" required
									   placeholder="Enter mobile number*" pattern="[0-9+\-\s()]{7,20}"
									   title="7 to 20 characters: digits, spaces, + ( ) and - are allowed"
									   value="<?= e_attr($user->mobile_number ?? '') ?>">
							</div>
							<div class="form-group">
								<label class="sr-only" for="nw_enq_msg">Your message</label>
								<textarea id="nw_enq_msg" name="enquiry_message" class="form-control" rows="3"
										  placeholder="What would you like to tell us?"></textarea>
							</div>
							<button type="submit" id="nw_enquiry_submit" name="enquiry_submit" class="p411-btn p411-btn--block">Send message</button>
						</form>
					<?php endif; ?>
				</div>

				<?php $slot = $ad_slots['side_1'];
				$slot_alias = 'news-sidebar';
				$slot_creative = 'house-ad';
				include APPPATH . 'views/partials/ad_slot.php'; ?>

				<?php include APPPATH . 'views/partials/service_promo.php'; ?>

				<!-- Most read -->
				<?php if (!empty($popular_news)): ?>
					<div class="p411-card">
						<div class="p411-card__head">
							<h2>Most read</h2>
							<a class="p411-more" href="<?= e_url(base_url('news')) ?>">All &rarr;</a>
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

		// Enquiry form: post in the background so the reader keeps their place.
		// The CSRF hook has already injected its token; FormData carries it.
		var form = document.getElementById('nw_enquiry_form');
		if (!form) return;

		var ok   = document.getElementById('nw_enq_ok');
		var fail = document.getElementById('nw_enq_fail');
		var btn  = document.getElementById('nw_enquiry_submit');

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

	// Copy the story URL. Called inline from the share row.
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
