<?php
/**
 * /user/analytics — the member's own figures (checklist §10).
 *
 * From User::analytics(): $days, $since, $full (plan includes Analytics),
 * $listings, $listing_stats (id => [event => hits]), $leads (id => n),
 * $coupons, $coupon_stats, $ads, $ad_stats, $tracking_on.
 *
 * Views and leads are for everyone; the click breakdown, deals and banners
 * are the plan feature and show as an upgrade without it.
 */
$n = function (array $stats, $id, $event) {
	return (int) ($stats[(int) $id][$event] ?? 0);
};
$lock = '<a href="' . base_url('pricing-plan') . '" title="Included with the Analytics feature" style="color:#9AA1AE;">&#128274;</a>';
?>
<!--CENTER SECTION-->
<div class="ud-cen">
	<div class="log-bor">&nbsp;</div> <span class="udb-inst">Analytics</span>
	<div class="ud-cen-s2">

		<h2>How your business is doing</h2>
		<p style="margin:6px 0 12px;color:#5A6072;font-size:14px;">
			The last <?= (int) $days ?> days, from <?= e(us_date($since)) ?>. Views are counted once per visitor per day;
			search engines and bots are left out.
		</p>
		<p style="margin:0 0 16px;">
			<?php foreach (array(7, 30, 90) as $__d): ?>
				<a href="<?= base_url('user/analytics?days=' . $__d) ?>" class="btn btn-sm <?= $__d === (int) $days ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= $__d ?> days</a>
			<?php endforeach; ?>
		</p>

		<?php if (!$tracking_on): ?>
			<div class="alert alert-info">Click tracking starts once the site&rsquo;s database update (0037) has been run.</div>
		<?php endif; ?>
		<?php if (!$full): ?>
			<div class="alert alert-warning">
				Website, phone, email and map clicks, deal activity and banner results are part of the
				<strong>Analytics</strong> feature, which your current package does not include.
				<a href="<?= base_url('pricing-plan') ?>">See the packages</a>.
			</div>
		<?php endif; ?>

		<h3 style="font-size:17px;margin:18px 0 8px;">Your listings</h3>
		<?php if (empty($listings)): ?>
			<p>You don&rsquo;t have a listing yet. <a href="<?= base_url('all-listing') ?>">Find and claim your business</a> or
				<a href="<?= base_url('user/add_new_listing_start') ?>">add it</a>.</p>
		<?php else: ?>
			<div class="table-responsive">
				<table class="table table-striped">
					<thead>
						<tr>
							<th>Business</th>
							<th>Views</th>
							<th>Leads</th>
							<th>Website</th>
							<th>Phone</th>
							<th>Email</th>
							<th>Map</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($listings as $l): $id = (int) $l->listing_id; ?>
							<tr>
								<td><a href="<?= e_url(base_url('listing-details/' . rawurlencode((string) $l->listing_slug))) ?>"><?= e($l->listing_name) ?></a></td>
								<td><?= $n($listing_stats, $id, 'view') ?></td>
								<td><a href="<?= base_url('user/enquiries') ?>"><?= (int) ($leads[$id] ?? 0) ?></a></td>
								<?php if ($full): ?>
									<td><?= $n($listing_stats, $id, 'website') ?></td>
									<td><?= $n($listing_stats, $id, 'phone') + $n($listing_stats, $id, 'whatsapp') ?></td>
									<td><?= $n($listing_stats, $id, 'email') ?></td>
									<td><?= $n($listing_stats, $id, 'map') ?></td>
								<?php else: ?>
									<td><?= $lock ?></td><td><?= $lock ?></td><td><?= $lock ?></td><td><?= $lock ?></td>
								<?php endif; ?>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>

		<?php if ($full && !empty($coupons)): ?>
			<h3 style="font-size:17px;margin:18px 0 8px;">Your deals</h3>
			<div class="table-responsive">
				<table class="table table-striped">
					<thead><tr><th>Deal</th><th>Ends</th><th>Views</th><th>Code copied / link opened</th></tr></thead>
					<tbody>
						<?php foreach ($coupons as $c): $cid = (int) $c->coupon_id; ?>
							<tr>
								<td><?= e($c->coupon_name) ?></td>
								<td><?= e(us_date($c->coupon_end_date)) ?></td>
								<td><?= $n($coupon_stats, $cid, 'view') ?></td>
								<td><?= $n($coupon_stats, $cid, 'click') ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>

		<?php if ($full && !empty($ads)): ?>
			<h3 style="font-size:17px;margin:18px 0 8px;">Your banners</h3>
			<div class="table-responsive">
				<table class="table table-striped">
					<thead><tr><th>Position</th><th>Runs</th><th>Shown</th><th>Clicks</th></tr></thead>
					<tbody>
						<?php foreach ($ads as $a): $aid = (int) $a->id; ?>
							<tr>
								<td><?= e($a->zone_name ?: 'Banner') ?></td>
								<td><?= e(us_date($a->ad_start_date)) ?> &ndash; <?= e(us_date($a->ad_end_date)) ?></td>
								<td><?= $n($ad_stats, $aid, 'impression') ?></td>
								<td><?= $n($ad_stats, $aid, 'click') ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
</div>
