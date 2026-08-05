<?php
/**
 * /blog-details/<slug> — one blog post.
 *
 * Rebuilt on the shared page kit (assets/css/directory.css + content.css
 * through $page_css), the same shell as /news-details: hero band with the
 * title and byline, main column, right-hand sidebar, ad zones.
 *
 * What it replaces: ~130 lines of its own <style> over the theme's
 * `eve-deta-*` classes, including `margin-top:-96px !important` on the related
 * posts, which pulled them up over their own images. The body was rendered
 * with `strip_tags(html_entity_decode($blog->blog_description))` — every
 * paragraph break in the post collapsed into one run of text. And the author
 * name came from a `$this->db->select('name')` query written inline in the
 * template; that is now resolved in User::blog_details() and passed in.
 *
 * From User::blog_details(): $blog (required), $related_blogs, $blog_author,
 * $logged_id, $user, $share_urls, $ad_slots.
 */

if (empty($blog)) {
	echo '<div class="container" style="padding:60px 0;"><h1>Post not found</h1></div>';
	return;
}

$logged_id     = isset($logged_id) ? $logged_id : null;
$related_blogs = (isset($related_blogs) && is_array($related_blogs)) ? $related_blogs : array();

$ad_slots = (isset($ad_slots) && is_array($ad_slots)) ? $ad_slots : array(
	'top'      => 'top-banner-site-wide',
	'inline'   => 'middle-banner-category',
	'side_1'   => 'right-side-banner-1',
	'side_2'   => 'right-side-banner-2',
	'side_sky' => 'right-panel-skyscraper',
	'bottom'   => 'bottom-banner-site-wide',
);

/* ---- normalised fields ------------------------------------------------- */

$bd_title  = (string) ($blog->blog_name ?? '');
$bd_key    = !empty($blog->blog_slug) ? (string) $blog->blog_slug : (string) ($blog->blog_id ?? '');
$bd_link   = base_url('blog-details/' . rawurlencode($bd_key));
$bd_image  = !empty($blog->blog_image) ? base_url($blog->blog_image) : '';
$bd_date   = us_date_ts($blog->created_at ?? null);
$bd_views  = (int) ($blog->views ?? 0);
$bd_author = (isset($blog_author) && $blog_author !== '') ? (string) $blog_author : 'Pearland411';

// content_text() turns the block tags into newlines and strips the rest, and
// decodes bodies the admin form stored entity-encoded — see the note in
// view_helper.php. Rendered with nl2br(e()) like every other detail page:
// paragraph breaks survive, and nothing stored can inject markup.
$bd_body = content_text($blog->blog_description ?? '');

// ~230 words a minute, rounded up, minimum one.
$bd_words = $bd_body === '' ? 0 : str_word_count($bd_body);
$bd_read  = $bd_words > 0 ? max(1, (int) ceil($bd_words / 230)) : 0;

$bd_placeholder = base_url('assets/images/placeholder-listing.svg');

/* ---- sharing ----------------------------------------------------------- */
/* Rebuilt against the canonical short URL rather than the controller's long
   /user/blog-details form — share links are what people paste into Facebook
   and what search engines then see. */

$share_url_enc   = rawurlencode($bd_link);
$share_title_enc = rawurlencode($bd_title);
$share_image_enc = rawurlencode($bd_image !== '' ? $bd_image : base_url('assets/images/pearland-logo.png'));

$share = array(
	'facebook'  => 'https://www.facebook.com/sharer/sharer.php?u=' . $share_url_enc,
	'twitter'   => 'https://twitter.com/intent/tweet?url=' . $share_url_enc . '&text=' . $share_title_enc,
	'whatsapp'  => 'https://wa.me/?text=' . $share_title_enc . '%20-%20' . $share_url_enc,
	'linkedin'  => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $share_url_enc,
	'pinterest' => 'https://pinterest.com/pin/create/button/?url=' . $share_url_enc . '&media=' . $share_image_enc . '&description=' . $share_title_enc,
);

/** Post URL for a related row. */
$bd_url = function ($row) {
	$key = !empty($row->blog_slug) ? (string) $row->blog_slug : (string) ($row->blog_id ?? '');
	return base_url('blog-details/' . rawurlencode($key));
};
?>

<section class="p411-ct p411-ct--blog-details">

	<!-- ============================================================ HERO -->
	<div class="p411-hero">
		<?php if ($bd_image !== ''): ?>
			<div class="p411-hero__bg" style="background-image:url('<?= e_attr($bd_image) ?>');" aria-hidden="true"></div>
		<?php endif; ?>
		<div class="container">
			<nav class="p411-crumb" aria-label="Breadcrumb">
				<a href="<?= base_url() ?>">Home</a>
				<span aria-hidden="true">/</span>
				<a href="<?= e_url(base_url('all-blog')) ?>">Blog</a>
				<span aria-hidden="true">/</span>
				<span class="is-current"><?= e($bd_title) ?></span>
			</nav>

			<?php if ($bd_date): ?>
				<div class="p411-ct-badges">
					<span class="p411-tag"><?= e(us_date($bd_date)) ?></span>
				</div>
			<?php endif; ?>

			<h1><?= e($bd_title) ?></h1>

			<div class="p411-ct-facts">
				<span><i class="material-icons" aria-hidden="true">person_outline</i><?= e($bd_author) ?></span>
				<?php if ($bd_date): ?>
					<span><i class="material-icons" aria-hidden="true">event</i><?= e(date('l, F j, Y', $bd_date)) ?></span>
				<?php endif; ?>
				<?php if ($bd_read > 0): ?>
					<span><i class="material-icons" aria-hidden="true">schedule</i><?= (int) $bd_read ?> min read</span>
				<?php endif; ?>
				<?php if ($bd_views > 0): ?>
					<span><i class="material-icons" aria-hidden="true">visibility</i><?= number_format($bd_views) ?> reads</span>
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

					<!-- THE POST -->
					<article class="p411-card" id="post">
						<?php if ($bd_image !== ''): ?>
							<figure class="p411-ct-figure">
								<?= responsive_img($blog->blog_image, $bd_title, array('fetchpriority' => 'high')) ?>
							</figure>
						<?php endif; ?>

						<div class="p411-ct-byline">
							<span><i class="material-icons" aria-hidden="true">person_outline</i><b><?= e($bd_author) ?></b></span>
							<?php if ($bd_date): ?>
								<span><i class="material-icons" aria-hidden="true">event</i><?= e(us_date($bd_date)) ?></span>
							<?php endif; ?>
							<?php if ($bd_read > 0): ?>
								<span><i class="material-icons" aria-hidden="true">schedule</i><?= (int) $bd_read ?> min read</span>
							<?php endif; ?>
						</div>

						<?php if ($bd_body !== ''): ?>
							<div class="p411-prose"><?= nl2br(e($bd_body)) ?></div>
						<?php else: ?>
							<div class="p411-empty p411-empty--inline">
								<h3>This post has no text yet</h3>
								<p>The title was published without a body. Have a look at the rest of the blog in the meantime.</p>
								<div class="p411-empty__actions">
									<a class="p411-btn p411-btn--sm" href="<?= e_url(base_url('all-blog')) ?>">Back to the blog</a>
								</div>
							</div>
						<?php endif; ?>

						<div class="p411-share" style="margin-top:22px;" aria-label="Share this post">
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
						</div>
					</article>

					<div class="p411-adrow">
						<?php $slot = $ad_slots['inline'];
						include APPPATH . 'views/partials/ad_slot.php'; ?>
					</div>

					<!-- RELATED -->
					<?php if (!empty($related_blogs)): ?>
						<section class="p411-card" aria-label="More from the blog">
							<div class="p411-card__head">
								<h2>More from the blog</h2>
							</div>
							<div class="p411-ct-grid">
								<?php foreach ($related_blogs as $rel): ?>
									<?php
									$rel_url = $bd_url($rel);
									$rel_ts  = us_date_ts($rel->created_at ?? null);
									$rel_ex  = content_excerpt($rel->blog_description ?? '', 110);
									?>
									<article class="p411-ct-card">
										<div class="p411-ct-card__media">
											<a class="p411-ct-card__link" href="<?= e_url($rel_url) ?>" tabindex="-1" aria-hidden="true">
												<?php if (!empty($rel->blog_image)): ?>
													<?= responsive_img($rel->blog_image, (string) ($rel->blog_name ?? '')) ?>
												<?php else: ?>
													<img src="<?= e_attr($bd_placeholder) ?>" alt="" loading="lazy" decoding="async">
												<?php endif; ?>
											</a>
											<?php if ($rel_ts): ?>
												<span class="p411-ct-card__date">
													<span><?= e(date('M', $rel_ts)) ?></span>
													<b><?= e(date('d', $rel_ts)) ?></b>
												</span>
											<?php endif; ?>
										</div>
										<div class="p411-ct-card__body">
											<h3><a href="<?= e_url($rel_url) ?>"><?= e($rel->blog_name ?? '') ?></a></h3>
											<?php if ($rel_ex !== ''): ?>
												<p class="p411-ct-excerpt"><?= e($rel_ex) ?></p>
											<?php endif; ?>
										</div>
										<div class="p411-ct-card__foot">
											<div class="p411-ct-meta p411-ct-meta--inline">
												<?php if ($rel_ts): ?>
													<span><i class="material-icons" aria-hidden="true">event</i><em><?= e(us_date($rel_ts)) ?></em></span>
												<?php endif; ?>
											</div>
											<a class="p411-btn p411-btn--sm" href="<?= e_url($rel_url) ?>">Read</a>
										</div>
									</article>
								<?php endforeach; ?>
							</div>
						</section>
					<?php endif; ?>
				</div>
			</div>

			<!-- ================================================= SIDEBAR -->
			<aside class="p411-shell__side">

				<?php $slot = $ad_slots['side_1'];
				$slot_creative = 'house-ad';
				include APPPATH . 'views/partials/ad_slot.php'; ?>

				<!-- Enquiry form. Only when the author switched it on. -->
				<?php if (!empty($blog->isenquiry)): ?>
					<div class="p411-card p411-form">
						<div class="p411-card__head">
							<h2>Ask about this post</h2>
						</div>

						<div id="event_detail_enq_succ" class="p411-note p411-note--ok" role="status" style="display:none;">
							Thanks &mdash; your message has been sent.
						</div>
						<div id="event_detail_enq_same" class="p411-note p411-note--err" role="alert" style="display:none;">
							You cannot send an enquiry on your own post.
						</div>
						<div id="event_detail_enq_fail" class="p411-note p411-note--err" role="alert" style="display:none;">
							Something went wrong. Please try again.
						</div>

						<?php if (empty($logged_id)): ?>
							<p class="p411-form__hint">
								<a href="<?= e_url(base_url('login')) ?>">Sign in</a> to send the author a message.
							</p>
						<?php endif; ?>

						<?php $disabled = empty($logged_id) ? 'disabled' : ''; ?>
						<form method="post" action="<?= e_url(base_url('user/add-enquiry/blog_details/' . (int) $blog->blog_id)) ?>"
							  name="detail_enquiry_form" id="detail_enquiry_form">
							<input type="hidden" name="listing_id" value="">
							<input type="hidden" name="event_id" value="">
							<input type="hidden" name="blog_id" value="<?= (int) $blog->blog_id ?>">
							<input type="hidden" name="product_id" value="">
							<input type="hidden" name="receiving_user_id" value="<?= (int) ($blog->user_id ?? 0) ?>">
							<input type="hidden" name="enquiry_sender_id" value="<?= (int) $logged_id ?>">
							<input type="hidden" name="enquiry_source" value="<?= e_attr($bd_title) ?>">
							<input type="hidden" name="enquiry_type" value="blog">

							<div class="form-group">
								<label for="enq_name">Your name <span class="p411-req">*</span></label>
								<input id="enq_name" type="text" name="enquiry_name" class="form-control"
									   autocomplete="name" value="<?= e_attr(set_value('enquiry_name')) ?>" required <?= $disabled ?>>
							</div>
							<div class="form-group">
								<label for="enq_email">Email <span class="p411-req">*</span></label>
								<input id="enq_email" type="email" name="enquiry_email" class="form-control"
									   autocomplete="email" value="<?= e_attr(set_value('enquiry_email')) ?>" required <?= $disabled ?>>
							</div>
							<div class="form-group">
								<label for="enq_mobile">Phone <span class="p411-req">*</span></label>
								<input id="enq_mobile" type="tel" name="enquiry_mobile" class="form-control"
									   autocomplete="tel" pattern="[0-9]{10,15}" title="Enter a valid phone number (10–15 digits)"
									   value="<?= e_attr(set_value('enquiry_mobile')) ?>" required <?= $disabled ?>>
							</div>
							<div class="form-group">
								<label for="enq_message">Message</label>
								<textarea id="enq_message" name="enquiry_message" class="form-control" rows="4" <?= $disabled ?>></textarea>
							</div>

							<button type="submit" class="p411-btn p411-btn--block" <?= $disabled ?>>
								<?= empty($logged_id) ? 'Please log in first' : 'Send message' ?>
							</button>
						</form>
					</div>
				<?php endif; ?>

				<?php include APPPATH . 'views/partials/service_promo.php'; ?>

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
						Business owners on Pearland411 can publish straight to the blog &mdash; no editor, no wait.
					</p>
					<a class="p411-btn p411-btn--block" href="<?= e_url(base_url('user/add_blog')) ?>">Write a post</a>
					<a class="p411-btn p411-btn--block p411-btn--ghost" style="margin-top:8px;" href="<?= e_url(base_url('all-blog')) ?>">All blog posts</a>
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
</section>
