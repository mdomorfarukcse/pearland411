<!--CENTER SECTION-->

<div class="ud-cen">

	<div class="log-bor">&nbsp;</div> <span class="udb-inst">All Listings</span>

	<?php if ($this->session->flashdata('message')) { ?>

		<div class="log-suc">
			<p><?= e($this->session->flashdata('message')) ?></p>
		</div>

	<?php } ?>
	<?php // A partly failed save (a refused logo or photo) — said nowhere before. ?>
	<?php if ($this->session->flashdata('error')) { ?>
		<div class="log-err">
			<p><?= e($this->session->flashdata('error')) ?></p>
		</div>
	<?php } ?>
	<?php // The add-listing gate's message (plan limit reached). It was set and never printed. ?>
	<?php if ($this->session->flashdata('error_message')) { ?>
		<div class="log-err">
			<p><?= e($this->session->flashdata('error_message')) ?></p>
		</div>
	<?php } ?>

	<div class="ud-cen-s2">

		<?php include APPPATH . 'views/partials/listing_tabs.php'; ?>

		<h2>Listing Details</h2>

		<!-- Search Box -->
		<div class="ad-int-sear">
			<input type="text" id="listingSearchBox" class="form-control" placeholder="Search by Listing Name...">
		</div>

		<a href="<?= base_url('user/add_new_listing_start'); ?>" class="db-tit-btn">Add New Listing</a>
		<div class="table-responsive">

			<!-- Subscription Details -->
			<?php
			// Entity-specific subscription view logic
			$entity = 'listing';
			$label = 'Listings';

			// Safe defaults so the view doesn't break if controller doesn't pass subscription vars
			$has_subscription = isset($has_subscription) ? (bool)$has_subscription : false;
			$subscription_limits = isset($subscription_limits) && is_array($subscription_limits) ? $subscription_limits : [];
			$plan_name = isset($subscription_limits['plan_name']) ? $subscription_limits['plan_name'] : 'Free';
			$limit = isset($subscription_limits[$entity]) ? (int)$subscription_limits[$entity] : -1; // -1 means Unlimited

			// Source list from controller (fallback to empty array)
			$sourceList = (isset($AllListings) && is_array($AllListings)) ? $AllListings : [];

			// Get current user id from session
			$current_user_id = null;
			if ($this->session && $this->session->userdata('logged_user')) {
				$session = $this->session->userdata('logged_user');
				$current_user_id = isset($session['logged_id']) ? $session['logged_id'] : null;
			}

			// Filter listings for current user only
			$userListings = [];
			if ($current_user_id !== null) {
				foreach ($sourceList as $listing) {
					if (isset($listing->user_id) && $listing->user_id == $current_user_id) {
						$userListings[] = $listing;
					}
				}
			}

			// For premium/unlimited plans: limit = 0 or -1 means unlimited
			$is_unlimited = ($limit === -1 || $limit === 0);

			// Every listing the member owns is shown. This used to show nothing
			// at all without a paid subscription — so a member who had CLAIMED a
			// business (the free tier) could not see it — and to hide any listing
			// past the plan's limit. The plan limits how many listings can be
			// ADDED, never which of your own businesses you may see.
			$displayList = $userListings;

			// Pagination + usage: use total_rows from pagination for accurate count
			$per_page = isset($per_page) ? (int)$per_page : 10;

			// Get total count from pagination config (not paginated display list)
			$total_available = isset($total_rows) ? (int)$total_rows : count($displayList);
			$used_count = $total_available;

			// Gating: no plan and no free plan to start on, OR reached limit (limited plans only)
			$free_plan_available = !empty($free_plan_available);
			$limit_reached = $has_subscription
				? (!$is_unlimited && $used_count >= $limit)
				: !$free_plan_available;
			$percent = (!$is_unlimited && $limit > 0) ? min(100, round(($used_count / max(1, $limit)) * 100)) : 0;
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
							<?php if ($free_plan_available): ?>
								<div style="font-weight:600;color:#2c3e50;">You are not on a plan yet</div>
								<div style="font-size:13px;color:#6c757d;">Your next listing starts on the Free Community plan. Upgrade for a logo, photos, hours, social links and more.</div>
							<?php else: ?>
								<div style="font-weight:600;color:#2c3e50;">Choose a plan to add a listing</div>
								<div style="font-size:13px;color:#6c757d;">Pick a plan to add your business, or claim an existing listing for free.</div>
							<?php endif; ?>
						</div>
						<a href="<?= site_url('pricing-plan') ?>" class="btn btn-primary btn-sm">View plans</a>
					</div>
				<?php else: ?>
					<div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
						<div style="flex:1;min-width:240px;">
							<div style="font-weight:600;color:#2c3e50;">Your plan: <span style="color:#0d6efd;"><?= htmlspecialchars($plan_name, ENT_QUOTES, 'UTF-8') ?></span></div>
							<div style="font-size:13px;color:#6c757d;">
								<?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>:
								<?php if (!$is_unlimited): ?>
									<span class="<?= $limit_reached ? 'text-danger' : 'text-success' ?>">
										<?= (int)$used_count ?> / <?= (int)$limit ?>
									</span>
								<?php else: ?>
									<span class="badge badge-success">∞ Unlimited</span>
								<?php endif; ?>
							</div>
							<?php if (!$is_unlimited && $limit > 0): ?>
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
				// Gate the existing "Add new post" button if limit is reached, without moving elements
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

			<?php if (true): // always — see the note on $displayList above ?>
				<table class="table bordered" id="listingTable">

					<thead>

						<tr>

							<th>No</th>

							<th>Listing Name</th>

							<th>Rating</th>

							<th>Views</th>

							<!--<th>Expiry on</th>-->

							<th>Status</th>

							<th>Actions</th>

						</tr>

					</thead>

					<tbody id="listingTableBody">

						<?php

						if (!empty($displayList)) {

							$i = isset($start_number) ? (int)$start_number : 1;

							$rendered = 0;
							foreach ($displayList as $value) {

						?>

								<tr>

									<td><?= $i; ?></td>

									<td>

										<img src="<?= (empty($value->listing_image)) ? base_url('assets/images/no_photo.jpg') :  base_url($value->listing_image) ?>"><?= e($value->listing_name) ?> <span><?= date('d, M Y', strtotime($value->created_at)); ?></span>

									</td>

									<td><span class="db-list-rat">0</span>

									</td>

									<td><span class="db-list-rat"><?= isset($value->views) ? (int)$value->views : 0 ?></span>

									</td>

									<!--									<td><span class="db-list-ststus">8 June 2020</span></td>-->

									<td>

										<?php // Unpublished = submitted and not live yet (or switched off by staff). ?>
										<span class="db-list-ststus"><?= (int) $value->listing_status === 1 ? 'Live' : 'Not published yet'; ?></span>

									</td>

									<td><?= admin_actions(array(
										'view' => base_url('listing-details/' . rawurlencode((string) $value->listing_slug)),
										'edit' => base_url('user/edit-listing/' . rawurlencode((string) $value->listing_slug)),
									), 'listing') ?>
									</td>

								</tr>

								<?php

								$i++;
								$rendered++;
							}
							if ($rendered === 0) { ?>
								<tr>
									<td colspan="8"> No Listings Available!!</td>
								</tr>
							<?php }
						} else { ?>
							<tr>
								<td colspan="8"> No Listings Available!!</td>
							</tr>
						<?php }

						?>
					</tbody>
				</table>
				<div class="float-right">
					<?php if ($used_count > $per_page) {
						echo $link;
					} ?>
				</div>
			<?php endif; ?>
		</div>

	</div>



	<script>
		// Search/filter functionality for listings table
		document.addEventListener('DOMContentLoaded', function() {
			const searchBox = document.getElementById('listingSearchBox');
			const tableBody = document.getElementById('listingTableBody');
			const rows = Array.from(tableBody.getElementsByTagName('tr'));
			searchBox.addEventListener('input', function() {
				const query = searchBox.value.toLowerCase();
				rows.forEach(row => {
					const nameCell = row.querySelector('td:nth-child(2)');
					if (nameCell) {
						const nameText = nameCell.textContent.toLowerCase();
						row.style.display = nameText.includes(query) ? '' : 'none';
					}
				});
			});
		});
	</script>
</div>