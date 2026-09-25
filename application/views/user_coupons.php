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

		<?php if (empty($listings)): ?>
			<div class="alert alert-warning">
				<p style="margin:0;">
					A deal is posted against one of your businesses, so you need a published listing first.
					<a href="<?= e_url(base_url('user-listing')) ?>">Your listings</a> &middot;
					<a href="<?= e_url(base_url('all_listing')) ?>">find and claim your business</a>
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
