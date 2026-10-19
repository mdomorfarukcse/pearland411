<?php
/**
 * /user/listing-submitted/<id> — shown once the add-a-listing wizard has saved
 * a business (User::add_listing_scratch → User::listing_submitted).
 *
 * The wizard used to end on "Listing Added Successfully." at the top of My
 * Listings — a page that, for a member without a paid plan, then showed no
 * listing at all — or, when the save failed, back on an empty Step 1.
 *
 * $listing, $category, $caps, $flash (notes about anything the plan did not
 * include or an upload refused, the plan started, whether the email went).
 */
$flash   = (isset($flash) && is_array($flash)) ? $flash : array();
$notes   = isset($flash['notes']) && is_array($flash['notes']) ? $flash['notes'] : array();
$live    = (int) $listing->listing_status === 1;
$url     = base_url('listing-details/' . rawurlencode((string) $listing->listing_slug));
$session = (array) $this->session->userdata('logged_user');
?>
<!--CENTER SECTION-->
<div class="ud-cen">
	<div class="log-bor">&nbsp;</div> <span class="udb-inst">Listing submitted</span>
	<div class="ud-cen-s2">

		<div class="log-suc" style="padding:18px 20px;">
			<p style="margin:0;font-size:17px;"><strong>Thank you &mdash; <?= e($listing->listing_name) ?> has been submitted.</strong></p>
		</div>

		<table class="table" style="margin-top:14px;">
			<tbody>
				<tr><th style="width:170px;">Status</th>
					<td><?= $live ? '<span class="badge badge-success" style="display:inline-block;width:auto;">Live</span> It is in the directory now.' : '<span class="badge badge-secondary" style="display:inline-block;width:auto;">Not published yet</span>' ?></td></tr>
				<tr><th>Business page</th>
					<td><a href="<?= e_url($url) ?>" target="_blank" rel="noopener"><?= e($url) ?></a></td></tr>
				<?php if (!empty($category->category_name)): ?>
					<tr><th>Category</th><td><?= e($category->category_name) ?></td></tr>
				<?php endif; ?>
				<?php if (!empty($caps['plan_name'])): ?>
					<tr><th>Plan</th><td><?= e($caps['plan_name']) ?><?= !empty($flash['plan']) ? ' &mdash; ' . e($flash['plan']) : '' ?></td></tr>
				<?php endif; ?>
				<tr><th>Confirmation email</th>
					<td><?php if (!empty($flash['emailed'])): ?>
							Sent to <?= e($session['email_id'] ?? 'your account email') ?>.
						<?php elseif (array_key_exists('emailed', $flash)): ?>
							We could not send it just now &mdash; this page is your confirmation.
						<?php else: ?>
							Sent when the listing was saved.
						<?php endif; ?></td></tr>
			</tbody>
		</table>

		<?php if ($notes): ?>
			<div class="alert alert-warning">
				<p style="margin:0 0 6px;"><strong>Please note:</strong></p>
				<ul style="margin:0 0 0 18px;list-style:disc;">
					<?php foreach ($notes as $n): ?><li><?= e($n) ?></li><?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<h3 style="margin:22px 0 10px;font-size:18px;">What next</h3>
		<p style="margin:0 0 16px;">
			<a href="<?= e_url($url) ?>" class="btn btn-primary btn-sm" target="_blank" rel="noopener" style="margin:0 8px 8px 0;">View your listing</a>
			<a href="<?= e_url(base_url('user/edit-listing/' . rawurlencode((string) $listing->listing_slug))) ?>" class="btn btn-outline-primary btn-sm" style="margin:0 8px 8px 0;">Edit it</a>
			<a href="<?= e_url(base_url('user/add-coupon')) ?>" class="btn btn-outline-primary btn-sm" style="margin:0 8px 8px 0;">Post a deal</a>
			<a href="<?= e_url(base_url('user-listing')) ?>" class="btn btn-link btn-sm" style="margin:0 0 8px;">My listings</a>
		</p>
		<ul style="margin:0 0 0 18px;list-style:disc;color:#5A6072;font-size:14px;">
			<li>Customers who press <strong>Get a quote</strong> on your listing reach you by email and on your dashboard.</li>
			<?php if (empty($caps['can_photos']) || empty($caps['can_video'])): ?>
				<li>Want a logo, photos, a video or featured placement? <a href="<?= e_url(base_url('pricing-plan')) ?>">See our packages</a>.</li>
			<?php endif; ?>
		</ul>
	</div>
</div>
