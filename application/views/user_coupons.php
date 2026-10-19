<?php
/**
 * /user/my-coupons — the deals this member has posted.
 *
 * From User::my_coupons(): $coupons, $listings, $live_count, $coupon_cap,
 * $logged_id.
 *
 * Not /user/coupons: that URL resolves to User::coupons, the PUBLIC deals
 * page, which is what the "Post a deal" buttons used to land on.
 */

$coupons    = (isset($coupons)  && is_array($coupons))  ? $coupons  : array();
$listings   = (isset($listings) && is_array($listings)) ? $listings : array();
$live_count = isset($live_count) ? (int) $live_count : 0;
$coupon_cap = isset($coupon_cap) ? (int) $coupon_cap : 10;
$today      = date('Y-m-d');
?>
<!--CENTER SECTION-->
<div class="ud-cen">
	<div class="log-bor">&nbsp;</div> <span class="udb-inst">Deals &amp; coupons</span>
	<div class="ud-cen-s2">

		<?php if ($m = $this->session->flashdata('coupon_message')): ?>
			<div class="log-suc"><p><?= e($m) ?></p></div>
		<?php endif; ?>
		<?php if ($m = $this->session->flashdata('coupon_error')): ?>
			<div class="alert alert-danger"><p><?= e($m) ?></p></div>
		<?php endif; ?>

		<h2>My deals</h2>

		<?php if (!empty($listings)): ?>
			<a href="<?= e_url(base_url('user/add-coupon')) ?>" class="db-tit-btn">Post a deal</a>
		<?php endif; ?>

		<p style="margin:12px 0 18px;color:#5A6072;font-size:14px;">
			A deal shows on <a href="<?= e_url(base_url('coupons')) ?>">the public deals page</a>, on your
			listing and on the homepage. It is free with any listing.
			<?php if ($coupon_cap > 0): ?>
				You have <strong><?= (int) $live_count ?></strong> live of <strong><?= (int) $coupon_cap ?></strong>.
			<?php endif; ?>
		</p>

		<?php if (empty($listings)):
			// Tracker: "guide users without published listings to create or
			// publish one first". Say what is missing for THIS member — nothing
			// yet, a listing waiting for approval, or a claim under review —
			// and give them the button for the next step.
			$pending_listings = (isset($pending_listings) && is_array($pending_listings)) ? $pending_listings : array();
			$pending_claims   = (isset($pending_claims)   && is_array($pending_claims))   ? $pending_claims   : array();
		?>
			<div class="alert alert-warning" style="padding:18px 20px;">
				<p style="margin:0 0 10px;font-weight:600;">Before you can post a deal, you need a published business listing.</p>
				<p style="margin:0 0 12px;">A deal is shown on the business it belongs to, so shoppers know whose offer it is.</p>

				<?php if ($pending_listings): ?>
					<p style="margin:0 0 6px;">Not published yet:</p>
					<ul style="margin:0 0 12px 18px;list-style:disc;">
						<?php foreach ($pending_listings as $pl): ?>
							<li><?= e($pl->listing_name) ?> &mdash; you can post a deal as soon as it is published.</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<?php if ($pending_claims): ?>
					<p style="margin:0 0 6px;">Claims under review:</p>
					<ul style="margin:0 0 12px 18px;list-style:disc;">
						<?php foreach ($pending_claims as $pc): ?>
							<li><?= e($pc->listing_name ?: $pc->enquiry_source) ?> &mdash; once your claim is approved the listing is yours and you can post deals for it.</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<p style="margin:0;">
					<a href="<?= e_url(base_url('user/add_new_listing_start')) ?>" class="btn btn-primary btn-sm" style="margin:4px 8px 4px 0;">Add your business</a>
					<a href="<?= e_url(base_url('all-listing')) ?>" class="btn btn-outline-primary btn-sm" style="margin:4px 8px 4px 0;">Find &amp; claim your business</a>
					<?php if ($pending_claims): ?>
						<a href="<?= e_url(base_url('user/my-claims')) ?>" class="btn btn-link btn-sm" style="margin:4px 0;">My claims</a>
					<?php endif; ?>
				</p>
			</div>
		<?php endif; ?>

		<?php if (empty($coupons)): ?>
			<div class="alert alert-info">
				<p style="margin:0;">You have not posted a deal yet.</p>
			</div>
		<?php else: ?>
			<div class="table-responsive">
				<table class="table table-striped">
					<thead>
						<tr>
							<th>Deal</th>
							<th>Code</th>
							<th>Runs</th>
							<th>Status</th>
							<th style="width:150px;">&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($coupons as $c): ?>
							<?php
							$ended  = !empty($c->coupon_end_date)   && $c->coupon_end_date   < $today;
							$queued = !empty($c->coupon_start_date) && $c->coupon_start_date > $today;
							$off    = (int) ($c->coupon_status ?? 0) !== 1;
							?>
							<tr>
								<td>
									<?php if (!empty($c->coupon_photo)): ?>
										<img src="<?= e_attr(base_url(thumb_url($c->coupon_photo, 'thumb'))) ?>" alt=""
											 style="width:38px;height:38px;object-fit:cover;border-radius:6px;margin-right:8px;vertical-align:middle;">
									<?php endif; ?>
									<strong><?= e($c->coupon_name ?? '') ?></strong>
								</td>
								<td><code><?= e($c->coupon_code ?? '') ?></code></td>
								<td>
									<?= e(us_date($c->coupon_start_date ?? '')) ?>
									&ndash;
									<?= e(us_date($c->coupon_end_date ?? '')) ?>
								</td>
								<td>
									<?php if ($off): ?>
										<span class="badge badge-secondary">Switched off</span>
									<?php elseif ($ended): ?>
										<span class="badge badge-secondary">Ended</span>
									<?php elseif ($queued): ?>
										<span class="badge badge-info">Starts <?= e(us_date($c->coupon_start_date)) ?></span>
									<?php else: ?>
										<span class="badge badge-success">Live</span>
									<?php endif; ?>
								</td>
								<td>
									<a href="<?= e_url(base_url('user/edit-coupon/' . (int) $c->coupon_id)) ?>">Edit</a>
									<?php // POST, not a link: a GET delete is one crawler away from
									      // emptying the member's deals. ?>
									<form method="post" style="display:inline;margin-left:8px;"
										  action="<?= e_url(base_url('user/delete-coupon/' . (int) $c->coupon_id)) ?>"
										  onsubmit="return confirm('Delete this deal? It will come off the public page straight away.');">
										<button type="submit" class="btn btn-link p-0"
												style="color:#6D263D;border:0;background:none;padding:0;">Delete</button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
</div>
<!--END CENTER SECTION-->
