<!--CENTER SECTION-->
<div class="ud-cen">
	<div class="log-bor">&nbsp;</div> <span class="udb-inst">Leads</span>

	<?php if ($this->session->flashdata('success_msg')) { ?>
		<div class="log-suc">
			<p><?= e($this->session->flashdata('success_msg')) ?></p>
		</div>
	<?php } ?>

	<?php if ($this->session->flashdata('error_msg')) { ?>
		<div class="log-err">
			<p><?= e($this->session->flashdata('error_msg')) ?></p>
		</div>
	<?php } ?>

	<div class="ud-cen-s2">
		<h2>Enquiry Details</h2>

		<!-- Search Box -->
		<div class="ad-int-sear">
			<input type="text" id="enquiriesSearchBox" class="form-control" placeholder="Search by Name, Email, or Message...">
		</div>

		<div class="table-responsive">
			<?php
			// Every lead the member may see is shown — no plan required. This used
			// to show nothing at all, and "Premium subscription required to view
			// enquiries", to anyone without a paid plan: so the owner of a CLAIMED
			// (free) listing never saw the quote requests for their own business,
			// and they reached only the admin team (tracker: "all the inquiries
			// that they have should be on their dashboards not our admin
			// dashboard"). No plan has ever had an enquiry limit to enforce.
			$displayList = (isset($allEnquiries) && is_array($allEnquiries)) ? $allEnquiries : [];
			$per_page    = isset($per_page) ? (int) $per_page : 10;
			$used_count  = isset($total_rows) ? (int) $total_rows : count($displayList);
			?>
			<p style="margin:12px 0 16px;color:#5A6072;font-size:14px;">
				Quote requests and messages about your businesses. Each one is also emailed to you when it arrives;
				reply to the customer straight from that email or using the details here.
			</p>

			<table data-admin-table class="responsive-table bordered">
				<thead>
					<tr>
						<th>No</th>
						<th>Enquiry Number</th>
						<th>Name</th>
						<th>Email</th>
						<th>About</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody id="enquiriesTableBody">
					<?php if (!empty($displayList)): ?>
						<?php
						$counter = isset($start_number) ? (int)$start_number : 1;
						foreach ($displayList as $enquiry):
						?>
							<tr>
								<td><?php echo $counter; ?></td>
								<td><?php echo isset($enquiry->enquiry_id) ? 'ENQ-' . str_pad($enquiry->enquiry_id, 4, '0', STR_PAD_LEFT) : 'N/A'; ?></td>
								<td><?php echo htmlspecialchars((string) $enquiry->enquiry_name); ?> <span><?php echo e(us_date($enquiry->enquiry_created_at ?? '')); ?></span></td>
								<td><?php echo htmlspecialchars((string) $enquiry->enquiry_email); ?></td>
								<td><?= e($enquiry->enquiry_source ?? '') ?></td>
								<td>
									<button type="button" class="enq-view-btn" onclick="openEnquiryModal(<?php echo $enquiry->enquiry_id; ?>)"
										data-id="<?php echo $enquiry->enquiry_id; ?>"
										data-name="<?php echo htmlspecialchars((string) $enquiry->enquiry_name); ?>"
										data-email="<?php echo htmlspecialchars((string) $enquiry->enquiry_email); ?>"
										data-phone="<?php echo htmlspecialchars((string) $enquiry->enquiry_mobile); ?>"
										data-message="<?php echo htmlspecialchars((string) $enquiry->enquiry_message); ?>"
										data-source="<?php echo htmlspecialchars((string) $enquiry->enquiry_source); ?>"
										data-type="<?php echo htmlspecialchars((string) $enquiry->enquiry_type); ?>"
										data-url="<?php if (!empty($enquiry->listing_id)): ?>listing-details?src=<?php echo urlencode((string) $enquiry->enquiry_source); ?>&amp;code=<?php echo (int) $enquiry->listing_id; ?><?php else: ?>-<?php endif; ?>"
										data-date="<?php echo e_attr(us_date($enquiry->enquiry_created_at ?? '')); ?>">
										View
									</button>
									<form method="post" action="<?= e_attr(base_url('user/delete-enquiry/' . (int) $enquiry->enquiry_id)) ?>" style="display:inline;"
										  onsubmit="return confirm('Are you sure you want to delete this enquiry?');">
										<button type="submit" class="enq-delete-btn" style="border:0;cursor:pointer;">Delete</button>
									</form>
								</td>
							</tr>
							<?php $counter++; ?>
						<?php endforeach; ?>
					<?php else: ?>
						<tr>
							<td colspan="6" style="text-align: center; padding: 20px;">
								No enquiries yet. When a customer presses <strong>Get a quote</strong> on one of your listings it appears here.
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>

			<!-- Pagination -->
			<div class="float-right mt-3">
				<?php if ($used_count > $per_page && !empty($link)): ?>
					<?php echo $link; ?>
				<?php endif; ?>
			</div>
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

				// Column 2 is the enquiry number; name and email are 3 and 4.
				const nameCell = row.querySelector('td:nth-child(3)'); // Name column
				const emailCell = row.querySelector('td:nth-child(4)'); // Email column

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