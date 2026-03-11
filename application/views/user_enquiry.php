<!--CENTER SECTION-->
<div class="ud-cen">
	<div class="log-bor">&nbsp;</div> <span class="udb-inst">Leads</span>

	<?php if ($this->session->flashdata('success_msg')) { ?>
		<div class="log-suc">
			<p><?= $this->session->flashdata('success_msg') ?></p>
		</div>
	<?php } ?>

	<?php if ($this->session->flashdata('error_msg')) { ?>
		<div class="log-err">
			<p><?= $this->session->flashdata('error_msg') ?></p>
		</div>
	<?php } ?>

	<div class="ud-cen-s2">
		<h2>Enquiry Details</h2>

		<!-- Search Box -->
		<div class="ad-int-sear">
			<input type="text" id="enquiriesSearchBox" class="form-control" placeholder="Search by Name, Email, or Message...">
		</div>

		<div class="table-responsive">
			<!-- Subscription Details -->
			<?php
			// Entity-specific subscription view logic for enquiries
			$entity = 'enquiry';
			$label = 'Enquiries';

			// Safe defaults so the view doesn't break if controller doesn't pass subscription vars
			$has_subscription = isset($has_subscription) ? (bool)$has_subscription : false;
			$subscription_limits = isset($subscription_limits) && is_array($subscription_limits) ? $subscription_limits : [];
			$plan_name = isset($subscription_limits['plan_name']) ? $subscription_limits['plan_name'] : 'Free';
			$limit = isset($subscription_limits[$entity]) ? (int)$subscription_limits[$entity] : -1; // -1 means Unlimited

			// Source list from controller (fallback to empty array)
			$sourceList = (isset($allEnquiries) && is_array($allEnquiries)) ? $allEnquiries : [];

			// Determine what to display: non-subscribed users see no data; subscribed users use pagination data
			$displayList = $has_subscription ? $sourceList : [];

			// Pagination + usage: use total_rows from pagination for accurate count
			$per_page = isset($per_page) ? (int)$per_page : 10;

			// Get total count from pagination config (not paginated display list)
			$total_available = $has_subscription ? (isset($total_rows) ? (int)$total_rows : count($displayList)) : 0;
			$used_count = $has_subscription ? $total_available : 0;

			// For premium/unlimited plans: limit = 0 or -1 means unlimited
			$is_unlimited = ($limit === -1 || $limit === 0);

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
			<!-- End Subscription Details -->

			<table class="responsive-table bordered">
				<thead>
					<tr>
						<th>No</th>
						<th>Enquiry Number</th>
						<th>Name</th>
						<th>Email</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody id="enquiriesTableBody">
					<?php if ($has_subscription): ?>
						<?php if (!empty($displayList)): ?>
							<?php
							$counter = isset($start_number) ? (int)$start_number : 1;
							$rendered = 0;
							foreach ($displayList as $enquiry):
								// Enforce plan limit across pagination using global index (skip for unlimited plans)
								if (!$is_unlimited && $limit > 0 && $counter > $limit) {
									$counter++;
									continue;
								}
							?>
								<tr>
									<td><?php echo $counter; ?></td>
									<td><?php echo isset($enquiry->enquiry_id) ? 'ENQ-' . str_pad($enquiry->enquiry_id, 4, '0', STR_PAD_LEFT) : 'N/A'; ?></td>
									<td><?php echo htmlspecialchars($enquiry->enquiry_name); ?> <span><?php echo date('d, M Y', strtotime($enquiry->enquiry_created_at)); ?></span></td>
									<td><?php echo htmlspecialchars($enquiry->enquiry_email); ?></td>
									<td>
										<button type="button" class="enq-view-btn" onclick="openEnquiryModal(<?php echo $enquiry->enquiry_id; ?>)"
											data-id="<?php echo $enquiry->enquiry_id; ?>"
											data-name="<?php echo htmlspecialchars($enquiry->enquiry_name); ?>"
											data-email="<?php echo htmlspecialchars($enquiry->enquiry_email); ?>"
											data-phone="<?php echo htmlspecialchars($enquiry->enquiry_mobile); ?>"
											data-message="<?php echo htmlspecialchars($enquiry->enquiry_message); ?>"
											data-source="<?php echo htmlspecialchars($enquiry->enquiry_source); ?>"
											data-type="<?php echo htmlspecialchars($enquiry->enquiry_type); ?>"
											data-url="<?php if (!empty($enquiry->listing_id)): ?>listing-details?src=<?php echo urlencode($enquiry->enquiry_source); ?>&code=<?php echo htmlspecialchars($enquiry->listing_id); ?><?php else: ?>-<?php endif; ?>"
											data-date="<?php echo date('d, M Y', strtotime($enquiry->enquiry_created_at)); ?>">
											View
										</button>
										<a href="<?php echo (function_exists('base_url') ? base_url() : '') . 'user/delete_enquiry/' . $enquiry->enquiry_id; ?>" class="enq-delete-btn" onclick="return confirm('Are you sure you want to delete this enquiry?');">Delete</a>
									</td>
								</tr>
								<?php
								$counter++;
								$rendered++;

								// Stop showing more enquiries if we hit the plan limit (skip for unlimited plans)
								if (!$is_unlimited && $limit > 0 && $rendered >= $limit) {
									break;
								}
								?>
							<?php endforeach; ?>
							<?php if ($rendered === 0): ?>
								<tr>
									<td colspan="5" style="text-align: center; padding: 20px;">No enquiries available!</td>
								</tr>
							<?php endif; ?>
						<?php else: ?>
							<tr>
								<td colspan="5" style="text-align: center; padding: 20px;">No enquiries available!</td>
							</tr>
						<?php endif; ?>
					<?php else: ?>
						<tr>
							<td colspan="5" style="text-align: center; padding: 20px;">
								<div style="color:#6c757d;font-size:14px;">
									<i class="fas fa-lock" style="margin-right:8px;"></i>
									Premium subscription required to view enquiries
								</div>
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>

			<!-- Pagination -->
			<?php if ($has_subscription): ?>
				<div class="float-right mt-3">
					<?php if ($used_count > $per_page && !empty($link)): ?>
						<?php echo $link; ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<!-- Enquiry Details Modal -->
<div id="enquiryDetailsModal" class="enq-modal-overlay">
	<div class="enq-modal-container">
		<div class="enq-modal-header">
			<h3 class="enq-modal-title">Enquiry Details</h3>
			<button type="button" class="enq-modal-close" onclick="closeEnquiryModal()">&times;</button>
		</div>
		<div class="enq-modal-body">
			<div class="enq-details-grid">
				<div class="enq-detail-item">
					<label class="enq-detail-label">Name:</label>
					<span id="modalEnqName" class="enq-detail-value"></span>
				</div>
				<div class="enq-detail-item">
					<label class="enq-detail-label">Email:</label>
					<span id="modalEnqEmail" class="enq-detail-value"></span>
				</div>
				<div class="enq-detail-item">
					<label class="enq-detail-label">Phone:</label>
					<span id="modalEnqPhone" class="enq-detail-value"></span>
				</div>
				<div class="enq-detail-item">
					<label class="enq-detail-label">Date:</label>
					<span id="modalEnqDate" class="enq-detail-value"></span>
				</div>
				<div class="enq-detail-item">
					<label class="enq-detail-label">Page Name:</label>
					<span id="modalEnqSource" class="enq-detail-value"></span>
				</div>
				<div class="enq-detail-item">
					<label class="enq-detail-label">Tracking ID:</label>
					<span id="modalEnqType" class="enq-detail-value"></span>
				</div>
				<div class="enq-detail-item">
					<label class="enq-detail-label">URL:</label>
					<span id="modalEnqUrl" class="enq-detail-value"></span>
				</div>
				<div class="enq-detail-item enq-message-item">
					<label class="enq-detail-label">Message:</label>
					<div id="modalEnqMessage" class="enq-message-content"></div>
				</div>
			</div>
		</div>
		<div class="enq-modal-footer">
			<button type="button" class="enq-modal-btn enq-btn-secondary" onclick="closeEnquiryModal()">Close</button>
		</div>
	</div>
</div>

<style>
	/* Enquiry Modal Styles */
	.enq-modal-overlay {
		display: none;
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background-color: rgba(0, 0, 0, 0.5);
		z-index: 9999;
		backdrop-filter: blur(3px);
	}

	.enq-modal-container {
		position: absolute;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
		background: #ffffff;
		border-radius: 12px;
		width: 90%;
		max-width: 600px;
		max-height: 80vh;
		overflow-y: auto;
		box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
		animation: enqModalSlideIn 0.3s ease-out;
	}

	@keyframes enqModalSlideIn {
		from {
			opacity: 0;
			transform: translate(-50%, -60%);
		}

		to {
			opacity: 1;
			transform: translate(-50%, -50%);
		}
	}

	.enq-modal-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 20px 24px;
		border-bottom: 1px solid #e5e7eb;
		background: #f9fafb;
		border-radius: 12px 12px 0 0;
	}

	.enq-modal-title {
		margin: 0;
		font-size: 1.25rem;
		font-weight: 600;
		color: #1f2937;
	}

	.enq-modal-close {
		background: none;
		border: none;
		font-size: 24px;
		color: #6b7280;
		cursor: pointer;
		padding: 4px;
		border-radius: 4px;
		transition: all 0.2s ease;
	}

	.enq-modal-close:hover {
		color: #dc2626;
		background-color: #fee2e2;
	}

	.enq-modal-body {
		padding: 24px;
	}

	.enq-details-grid {
		display: grid;
		gap: 16px;
	}

	.enq-detail-item {
		display: flex;
		flex-direction: column;
		gap: 4px;
	}

	.enq-message-item {
		grid-column: 1 / -1;
	}

	.enq-detail-label {
		font-weight: 600;
		color: #374151;
		font-size: 0.875rem;
		text-transform: uppercase;
		letter-spacing: 0.025em;
	}

	.enq-detail-value {
		color: #1f2937;
		font-size: 1rem;
		padding: 8px 12px;
		background-color: #f9fafb;
		border-radius: 6px;
		border: 1px solid #e5e7eb;
		word-wrap: break-word;
	}

	.enq-message-content {
		color: #1f2937;
		font-size: 1rem;
		padding: 12px;
		background-color: #f9fafb;
		border-radius: 6px;
		border: 1px solid #e5e7eb;
		white-space: pre-wrap;
		word-wrap: break-word;
		min-height: 60px;
	}

	.enq-modal-footer {
		padding: 16px 24px;
		border-top: 1px solid #e5e7eb;
		display: flex;
		justify-content: flex-end;
		gap: 12px;
		background: #f9fafb;
		border-radius: 0 0 12px 12px;
	}

	.enq-modal-btn {
		padding: 10px 20px;
		border-radius: 6px;
		font-weight: 500;
		font-size: 0.875rem;
		cursor: pointer;
		transition: all 0.2s ease;
		border: 1px solid transparent;
	}

	.enq-btn-secondary {
		background-color: #6b7280;
		color: white;
		border-color: #6b7280;
	}

	.enq-btn-secondary:hover {
		background-color: #4b5563;
		border-color: #4b5563;
	}

	/* Table Action Buttons */
	.enq-view-btn {
		background-color: #3b82f6;
		color: white;
		border: none;
		padding: 4px 8px;
		border-radius: 3px;
		font-size: 0.75rem;
		cursor: pointer;
		margin-right: 6px;
		transition: all 0.2s ease;
	}

	.enq-view-btn:hover {
		background-color: #2563eb;
		transform: translateY(-1px);
	}

	.enq-delete-btn {
		background-color: #dc2626;
		color: white;
		text-decoration: none;
		padding: 4px 8px;
		border-radius: 3px;
		font-size: 0.75rem;
		transition: all 0.2s ease;
		display: inline-block;
	}

	.enq-delete-btn:hover {
		background-color: #b91c1c;
		transform: translateY(-1px);
		text-decoration: none;
		color: white;
	}

	/* Responsive Design */
	@media (max-width: 768px) {
		.enq-modal-container {
			width: 95%;
			margin: 10px;
		}

		.enq-modal-header,
		.enq-modal-body,
		.enq-modal-footer {
			padding: 16px;
		}

		.enq-details-grid {
			gap: 12px;
		}
	}
</style>

<script>
	// Search/filter functionality for enquiries table
	document.addEventListener('DOMContentLoaded', function() {
		const searchBox = document.getElementById('enquiriesSearchBox');
		const tableBody = document.getElementById('enquiriesTableBody');
		const rows = Array.from(tableBody.getElementsByTagName('tr'));

		searchBox.addEventListener('input', function() {
			const query = searchBox.value.toLowerCase();

			rows.forEach(row => {
				// Skip the "No enquiries found" row
				if (row.querySelector('td[colspan]')) {
					return;
				}

				const nameCell = row.querySelector('td:nth-child(2)'); // Name column
				const emailCell = row.querySelector('td:nth-child(3)'); // Email column

				if (nameCell && emailCell) {
					const nameText = nameCell.textContent.toLowerCase();
					const emailText = emailCell.textContent.toLowerCase();

					const matches = nameText.includes(query) || emailText.includes(query);

					row.style.display = matches ? '' : 'none';
				}
			});
		});
	});

	// Modal Functions
	function openEnquiryModal(enquiryId) {
		const button = document.querySelector(`[data-id="${enquiryId}"]`);

		if (!button) return;

		// Get data from button attributes
		const name = button.getAttribute('data-name') || 'N/A';
		const email = button.getAttribute('data-email') || 'N/A';
		const phone = button.getAttribute('data-phone') || 'N/A';
		const message = button.getAttribute('data-message') || 'N/A';
		const source = button.getAttribute('data-source') || 'N/A';
		const type = button.getAttribute('data-type') || 'N/A';
		const url = button.getAttribute('data-url') || 'N/A';
		const date = button.getAttribute('data-date') || 'N/A';

		// Populate modal with data
		document.getElementById('modalEnqName').textContent = name;
		document.getElementById('modalEnqEmail').textContent = email;
		document.getElementById('modalEnqPhone').textContent = phone;
		document.getElementById('modalEnqMessage').textContent = message;
		document.getElementById('modalEnqSource').textContent = source;
		document.getElementById('modalEnqType').textContent = type;
		document.getElementById('modalEnqUrl').textContent = url;
		document.getElementById('modalEnqDate').textContent = date;

		// Show modal
		const modal = document.getElementById('enquiryDetailsModal');
		modal.style.display = 'block';
		document.body.style.overflow = 'hidden'; // Prevent background scrolling
	}

	function closeEnquiryModal() {
		const modal = document.getElementById('enquiryDetailsModal');
		modal.style.display = 'none';
		document.body.style.overflow = 'auto'; // Restore scrolling
	}

	// Close modal when clicking outside
	document.addEventListener('click', function(event) {
		const modal = document.getElementById('enquiryDetailsModal');
		if (event.target === modal) {
			closeEnquiryModal();
		}
	});

	// Close modal with Escape key
	document.addEventListener('keydown', function(event) {
		if (event.key === 'Escape') {
			const modal = document.getElementById('enquiryDetailsModal');
			if (modal.style.display === 'block') {
				closeEnquiryModal();
			}
		}
	});
</script>