<!--CENTER SECTION-->
<div class="ud-cen">
	<div class="log-bor">&nbsp;</div> <span class="udb-inst">All News</span>

	<?php if ($this->session->flashdata('message')) { ?>
		<div class="log-suc">
			<p><?= $this->session->flashdata('message') ?></p>
		</div>
	<?php } ?>

	<div class="ud-cen-s2">
		<h2>News Details</h2>
		<!-- Search Box -->
		<div class="ad-int-sear">
			<input type="text" id="newsSearchBox" class="form-control" placeholder="Search by News Name...">
		</div>
		<a href="<?= base_url('user/user-add-news'); ?>" class="db-tit-btn">Add new news</a>

		<!-- Subscription Details -->
		<?php
		// Entity-specific subscription view logic for news
		$entity = 'news';
		$label = 'News';

		// Safe defaults so the view doesn't break if controller doesn't pass subscription vars
		$has_subscription = isset($has_subscription) ? (bool)$has_subscription : false;
		$subscription_limits = isset($subscription_limits) && is_array($subscription_limits) ? $subscription_limits : [];
		$plan_name = isset($subscription_limits['plan_name']) ? $subscription_limits['plan_name'] : 'Free';
		$limit = isset($subscription_limits[$entity]) ? (int)$subscription_limits[$entity] : -1; // -1 means Unlimited

		// Source list from controller (fallback to empty array)
		$sourceList = (isset($allNews) && is_array($allNews)) ? $allNews : [];

		// Determine what to display: non-subscribed users see no data; subscribed users use pagination data
		$displayList = $has_subscription ? $sourceList : [];

		// Pagination + usage: use actual total from database instead of paginated count
		$per_page = isset($per_page) ? (int)$per_page : 10;
		$total_available = $has_subscription ? (isset($total_rows) ? (int)$total_rows : count($displayList)) : 0;
		$used_count = $has_subscription ? $total_available : 0; // Show actual count, not limited by plan
		$is_unlimited = ($limit === -1 || $limit === 0);

		// For unlimited plans, make sure we show all available news by adjusting display list
		if ($has_subscription && $is_unlimited && isset($total_rows) && $total_rows > count($displayList)) {
			// If we have more total news than what's in displayList, we need to show all
			// This handles cases where pagination limits the display but user has unlimited plan
			$displayList = $sourceList; // Use full source list for unlimited users
		}

		// Gating: non-subscribed OR reached limit (for limited plans only)
		$limit_reached = (!$has_subscription) || (!$is_unlimited && $used_count >= $limit);
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

		<!-- Upgrade Modal (shown only when limit reached) -->
		<div class="modal fade" id="upgradeModal" tabindex="-1" role="dialog" aria-labelledby="upgradeModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;">
						<h5 class="modal-title" id="upgradeModalLabel">Upgrade your plan</h5>
						<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<p style="margin-bottom:0.75rem;color:#495057;">You've reached your current plan's <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?> limit.</p>
						<div style="background:#f8f9fa;border:1px solid #e9ecef;border-radius:8px;padding:12px;">
							<strong>Usage:</strong>
							<span class="<?= $limit_reached ? 'text-danger' : 'text-success' ?>">
								<?php if (!$is_unlimited): ?>
									<?= (int)$used_count ?> / <?= (int)$limit ?>
								<?php else: ?>
									∞ Unlimited
								<?php endif; ?>
							</span>
						</div>
					</div>
					<div class="modal-footer">
						<a href="<?= site_url('pricing-plan') ?>" class="btn btn-primary">View upgrade options</a>
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>

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
		<?php if ($has_subscription): ?>
			<table data-admin-table class="responsive-table bordered">
				<thead>
					<tr>
						<th>No</th>
						<th>news Name</th>
						<th>Views</th>
						<!-- <th>Expiry on</th> -->
						<th>Status</th>
						<th>Actions</th>
					</tr>
				</thead>

				<tbody id="newsTableBody">
					<?php
					if (!empty($displayList)) {
						$i = isset($start_number) ? (int)$start_number : 1; // global row index start (1-based)
						$rendered = 0;
						foreach ($displayList as $news) {
							// Enforce plan limit across pagination using global index (skip for unlimited plans)
							if (!$is_unlimited && $limit > 0 && $i > $limit) {
								$i++;
								continue;
							}
					?>

							<tr>
								<td><?= $i; ?></td>
								<td>
									<img src="<?= (empty($news->news_image)) ? base_url('assets/images/no_photo.jpg') :  base_url($news->news_image) ?>	" alt="<?= $news->news_title; ?> ">
									<?= $news->news_title; ?>
									<span><?= date('d, M, Y', strtotime($news->created_at)); ?></span>
								</td>
								<td><span class="db-list-rat"><?= isset($news->views) ? $news->views : 0 ?></span></td>
								<td>
									<span class="db-list-ststus">
										<?= $news->news_status == 1 ? 'Active' : 'Inactive'; ?>
									</span>
								</td>
								<td><?= admin_actions(array(
									'view' => base_url('news_details/' . rawurlencode((string) ($news->news_slug ?? $news->news_id))),
									'edit' => base_url('user/user-edit-news/' . rawurlencode((string) $news->news_slug)),
								), 'news post') ?></td>
							</tr>
							<?php
							$i++;
							$rendered++;

							// Stop showing more news if we hit the plan limit (skip for unlimited plans)
							if (!$is_unlimited && $limit > 0 && $rendered >= $limit) {
								break;
							}
						}
						if ($rendered === 0) { ?>
							<tr>
								<td colspan="6"> No News Available!!</td>
							</tr>
						<?php }
					} else { ?>
						<tr>
							<td colspan="6"> No News Available!!</td>
						</tr>
					<?php } ?>

				</tbody>

			</table>
			<div class="float-right mt-3">
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
		const searchBox = document.getElementById('newsSearchBox');
		const tableBody = document.getElementById('newsTableBody');
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