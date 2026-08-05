<!--CENTER SECTION-->

<div class="ud-cen">

	<div class="log-bor">&nbsp;</div> <span class="udb-inst">Paid ads</span>

	<?php if ($this->session->flashdata('message')) { ?>

		<div class="log-suc">
			<p><?= $this->session->flashdata('message') ?></p>
		</div>

	<?php } ?>

	<div class="ud-cen-s2">

		<h2>Your Bannear Ads</h2>

		<a href="<?= base_url() ?>post_ads" class="db-tit-btn db-tit-btn-2-ads">Post your Ads</a>

		<a href="<?= base_url('user/ads-price'); ?>" class="db-tit-btn">Pricing and other details</a>

		<!-- Subscription Details -->
		<?php
		// Entity-specific subscription view logic
		$entity = 'ad';
		$label = 'Ads';

		// Safe defaults so the view doesn't break if controller doesn't pass subscription vars
		$has_subscription = isset($has_subscription) ? (bool)$has_subscription : false;
		$subscription_limits = isset($subscription_limits) && is_array($subscription_limits) ? $subscription_limits : [];
		$plan_name = isset($subscription_limits['plan_name']) ? $subscription_limits['plan_name'] : 'Free';
		$limit = isset($subscription_limits[$entity]) ? (int)$subscription_limits[$entity] : -1; // -1 means Unlimited

		// Source list from controller (fallback to empty array)
		$sourceList = (isset($ads) && is_array($ads)) ? $ads : [];

		// Determine what to display: non-subscribed users see no data; subscribed users use pagination data
		$displayList = $has_subscription ? $sourceList : [];

		// Pagination + usage: derive from actual data to keep progress and table in sync
		$per_page = isset($per_page) ? (int)$per_page : 10;
		$total_available = $has_subscription ? (is_array($displayList) ? count($displayList) : 0) : 0;
		$used_count = $has_subscription ? ($limit === -1 ? $total_available : min($total_available, $limit)) : 0;

		// Gating: non-subscribed OR reached limit (for limited plans)
		$limit_reached = (!$has_subscription) || ($limit > -1 && $used_count >= $limit);
		$percent = ($limit > 0) ? min(100, round(($used_count / max(1, $limit)) * 100)) : 0;
		?>

		<div class="subscription-info-card" style="background:#f8f9fa;border:1px solid #e9ecef;border-radius:10px;padding:16px;margin:16px 0;">
			<?php if (!$has_subscription): ?>
				<div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
					<div style="width:36px;height:36px;border-radius:50%;background:#e3f2fd;display:flex;align-items:center;justify-content:center;color:#1976d2;">
						<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true" focusable="false">
							<path d="M12 .587l3.668 7.431 8.199 1.192-5.933 5.787 1.401 8.163L12 18.896 4.665 23.16l1.401-8.163L.133 9.21l8.199-1.192z" />
						</svg>
					</div>
					<div style="flex:1;min-width:220px;">
						<div style="font-weight:600;color:#2c3e50;">Premium access required</div>
						<div style="font-size:13px;color:#6c757d;">Subscribe to unlock <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?> limits and more features.</div>
					</div>
					<a href="<?= site_url('pricing-plan') ?>" class="btn btn-primary btn-sm">View plans</a>
				</div>
			<?php else: ?>
				<div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
					<div style="flex:1;min-width:240px;">
						<div style="font-weight:600;color:#2c3e50;">Your plan: <span style="color:#0d6efd;"><?= htmlspecialchars($plan_name, ENT_QUOTES, 'UTF-8') ?></span></div>
						<div style="font-size:13px;color:#6c757d;">
							<?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>:
							<span class="<?= $limit_reached ? 'text-danger' : 'text-success' ?>">
								<?= (int)$used_count ?> / <?= ($limit === -1 ? 'Unlimited' : (int)$limit) ?>
							</span>
						</div>
						<?php if ($limit > 0): ?>
							<div class="progress" style="height:8px;margin-top:8px;">
								<div class="progress-bar <?= $limit_reached ? 'bg-danger' : 'bg-success' ?>" role="progressbar" style="width: <?= $percent ?>%;" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100"></div>
							</div>
						<?php endif; ?>
					</div>
					<?php if ($limit_reached): ?>
						<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#upgradeModal">
							Upgrade to add more
						</button>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<!-- Upgrade / choose-a-plan modal (shown only when the add button is gated).
		     Shared partial so every dashboard tells the same story — see
		     views/partials/plan_limit_modal.php. -->
		<?php include APPPATH . 'views/partials/plan_limit_modal.php'; ?>

		<script>
			// Gate the existing "Post your Ads" button if limit is reached, without moving elements
			document.addEventListener('DOMContentLoaded', function() {
				var limitReached = <?= $limit_reached ? 'true' : 'false' ?>;
				if (limitReached) {
					var addBtn = document.querySelector('.db-tit-btn');
					if (addBtn) {
						addBtn.setAttribute('href', '#');
						addBtn.setAttribute('data-toggle', 'modal');
						addBtn.setAttribute('data-target', '#upgradeModal');
						addBtn.classList.add('disabled');
						addBtn.addEventListener('click', function(e) {
							e.preventDefault();
						});
					}
				}
			});
		</script>
		<!-- End Subscription Details -->

		<?php if ($has_subscription): ?>
			<table data-admin-table class="responsive-table bordered">

				<thead>

					<tr>

						<th>No</th>

						<!--<th>Ads Name</th>-->

						<th>Ads Position</th>

						<th>Start date</th>

						<th>End date</th>

						<th>Duration</th>

						<th>Status</th>

						<th>Views</th>

						<th>Clicks</th>

					</tr>

				</thead>

				<tbody>

					<?php

					if (!empty($displayList)) {

						$j = isset($start_number) ? (int)$start_number : 1;
						$rendered = 0;

						foreach ($displayList as $key => $value) {
							// Enforce plan limit across pagination using global index
							if ($limit !== -1 && $j > $limit) {
								$j++;
								continue;
							}

					?>
							<tr>

								<td><?= $j++; ?></td>

								<td>
									<?php
									// $value->ads_price_id;
									if (isset($value->ads_price_name)) {
										echo $value->ads_price_name;
									} else {
										echo "unset";
									}
									?>
								</td>

								<td><?= date('d, M Y', strtotime($value->ad_start_date)); ?></td>

								<td><?= date('d, M Y', strtotime($value->ad_end_date)); ?></td>

								<td><?= $value->ad_total_days; ?> day(s)</td>

								<td><span class="db-list-rat"><?= ($value->ad_status ==  1) ? 'Active' : 'In-Active'; ?></span></td>

								<td><span class="db-list-rat">1k</span></td>

								<td><span class="db-list-rat">642</span></td>

							</tr>

						<?php
							$rendered++;
						}
						if ($rendered === 0) { ?>
							<tr>
								<td colspan="9">No Ads Available!</td>
							</tr>
						<?php }
					} else { ?>
						<tr>
							<td colspan="9">No Ads Available!</td>
						</tr>
					<?php }

					?>

				</tbody>

			</table>
			<div class="float-right mt-3">

				<?php if ($used_count > $per_page) {
					echo $link;
				} ?>

			</div>
		<?php endif; ?>

		<div class="ud-notes">

			<p><b>Notes:</b> Hi, Before start "Ads Payment" you must know the pricing details and positions and all. You just click the "Pricing and other details" button in this same page and you know the all details. If your payment done means your invoice automaticall received your "Payment invoice" page and you never stop your Ads till the end date.</p>

		</div>

	</div>

</div>

<!-- <tr>

				<td>1</td>

				<!--                        <td>Taj Luxury Hotel</td>--

				<td>Home page Bottom</td>

				<td>31, Mar 2021</td>

				<td>29, Apr 2021</td>

				<td>0 Days</td>

				<td><span class="db-list-ststus">InActive</span>

				</td>

				<td><span class="db-list-rat">1k</span>

				</td>

				<td><span class="db-list-rat">642</span>

				</td>

			</tr> -->