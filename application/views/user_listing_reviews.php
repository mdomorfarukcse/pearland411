<!--CENTER SECTION-->

<div class="ud-cen">

    <div class="log-bor">&nbsp;</div> <span class="udb-inst">Listing Reviews</span>

    <?php if ($this->session->flashdata('message')) { ?>

        <div class="log-suc">
            <p><?= $this->session->flashdata('message') ?></p>
        </div>

    <?php } ?>

    <?php if ($this->session->flashdata('error')) { ?>

        <div class="log-err">
            <p><?= $this->session->flashdata('error') ?></p>
        </div>

    <?php } ?>

    <div class="ud-cen-s2">

        <h2>All Reviews for Your Listings</h2>

        <!-- Filter Buttons -->
        <div class="review-filters" style="margin-bottom: 20px;">
            <button class="filter-btn active" data-status="all" style="margin-right: 10px; padding: 8px 16px; border: 1px solid #ddd; background: #007bff; color: white; border-radius: 4px; cursor: pointer;">All Reviews</button>
            <button class="filter-btn" data-status="pending" style="margin-right: 10px; padding: 8px 16px; border: 1px solid #ddd; background: #f8f9fa; color: #333; border-radius: 4px; cursor: pointer;">Pending</button>
            <button class="filter-btn" data-status="approved" style="margin-right: 10px; padding: 8px 16px; border: 1px solid #ddd; background: #f8f9fa; color: #333; border-radius: 4px; cursor: pointer;">Approved</button>
            <button class="filter-btn" data-status="cancelled" style="padding: 8px 16px; border: 1px solid #ddd; background: #f8f9fa; color: #333; border-radius: 4px; cursor: pointer;">Cancelled</button>
        </div>

        <!-- Search Box -->
        <div class="ad-int-sear">
            <input type="text" id="reviewSearchBox" class="form-control" placeholder="Search by Reviewer Name or Listing...">
        </div>

        <div class="table-responsive">

            <table class="table bordered" id="reviewsTable">

                <thead>

                    <tr>

                        <th>No</th>

                        <th>Listing Name</th>

                        <th>Reviewer</th>

                        <th>Rating</th>

                        <th>Review Date</th>

                        <th>Status</th>

                        <th>View</th>

                        <th>Action</th>

                    </tr>

                </thead>

                <tbody id="reviewsTableBody">

                    <?php

                    if (!empty($AllReviews)) {

                        $i = isset($start_number) ? (int)$start_number : 1;

                        foreach ($AllReviews as $review) {

                    ?>

                            <tr data-status="<?= $review->status ?>">

                                <td><?= $i; ?></td>

                                <td>
                                    <strong><?= e($review->listing_name) ?></strong>
                                </td>

                                <td>
                                    <strong><?= $review->reviewer_name; ?></strong><br>
                                    <small><?= $review->reviewer_email; ?></small>
                                </td>

                                <td>
                                    <div class="rating-stars">
                                        <?php for ($star = 1; $star <= 5; $star++) { ?>
                                            <span class="star <?= $star <= $review->rating ? 'filled' : '' ?>">★</span>
                                        <?php } ?>
                                        <span class="rating-number">(<?= $review->rating ?>/5)</span>
                                    </div>
                                </td>

                                <td>
                                    <span><?= date('d M Y', strtotime($review->created_at)); ?></span><br>
                                    <small><?= date('h:i A', strtotime($review->created_at)); ?></small>
                                </td>

                                <td>
                                    <span class="status-badge status-<?= $review->status ?>"><?= ucfirst($review->status); ?></span>
                                </td>

                                <td>
                                    <button class="db-list-edit view-review-btn"
                                        data-review-id="<?= $review->review_id ?>"
                                        data-listing="<?= htmlspecialchars($review->listing_name) ?>"
                                        data-reviewer="<?= htmlspecialchars($review->reviewer_name) ?>"
                                        data-email="<?= htmlspecialchars($review->reviewer_email) ?>"
                                        data-mobile="<?= htmlspecialchars($review->reviewer_mobile) ?>"
                                        data-city="<?= htmlspecialchars($review->reviewer_city) ?>"
                                        data-rating="<?= $review->rating ?>"
                                        data-message="<?= htmlspecialchars($review->review_message) ?>"
                                        data-status="<?= $review->status ?>"
                                        data-date="<?= date('d M Y h:i A', strtotime($review->created_at)) ?>">
                                        View
                                    </button>
                                </td>

                                <td>
                                    <?php if ($review->status == 'pending') { ?>
                                        <div class="action-buttons">
                                            <button class="btn btn-success btn-sm approve-btn" data-review-id="<?= $review->review_id ?>" style="margin-right: 5px;">Approve</button>
                                            <button class="btn btn-danger btn-sm cancel-btn" data-review-id="<?= $review->review_id ?>">Cancel</button>
                                        </div>
                                    <?php } else { ?>
                                        <span class="text-muted">No action</span>
                                    <?php } ?>
                                </td>

                            </tr>

                        <?php

                            $i++;
                        }
                    } else { ?>
                        <tr>
                            <td colspan="8"> No Reviews Available!!</td>
                        </tr>
                    <?php }

                    ?>
                </tbody>
            </table>
            <div class="float-right">
                <?= isset($link) ? $link : ''; ?>
            </div>
        </div>

    </div>

</div>

<!-- Review Details Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" role="dialog" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewModalLabel">Review Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><strong>Listing Information:</strong></h6>
                        <p><strong>Listing Name:</strong> <span id="modal-listing-name"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6><strong>Review Information:</strong></h6>
                        <p><strong>Review Date:</strong> <span id="modal-review-date"></span></p>
                        <p><strong>Status:</strong> <span id="modal-review-status" class="status-badge"></span></p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <h6><strong>Reviewer Information:</strong></h6>
                        <p><strong>Name:</strong> <span id="modal-reviewer-name"></span></p>
                        <p><strong>Email:</strong> <span id="modal-reviewer-email"></span></p>
                        <p><strong>Mobile:</strong> <span id="modal-reviewer-mobile"></span></p>
                        <p><strong>City:</strong> <span id="modal-reviewer-city"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6><strong>Rating:</strong></h6>
                        <div id="modal-rating-stars" class="rating-stars" style="font-size: 24px; margin-bottom: 15px;"></div>
                    </div>
                </div>
                <hr>
                <h6><strong>Review Message:</strong></h6>
                <div id="modal-review-message" style="background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff;"></div>
            </div>
            <div class="modal-footer">
                <div id="modal-pending-actions" style="display: none;">
                    <button type="button" id="modal-approve-btn" class="btn btn-success" data-review-id="">Approve Review</button>
                    <button type="button" id="modal-cancel-btn" class="btn btn-danger" data-review-id="" style="margin-left: 10px;">Cancel Review</button>
                </div>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    .rating-stars .star {
        font-size: 18px;
        color: #ddd;
    }

    .rating-stars .star.filled {
        color: #ffc107;
    }

    .rating-number {
        margin-left: 8px;
        font-size: 14px;
        color: #666;
    }

    .status-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .status-approved {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .status-cancelled {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .filter-btn.active {
        background: #007bff !important;
        color: white !important;
    }

    .filter-btn:hover {
        background: #e9ecef;
    }

    .action-buttons {
        display: flex;
        flex-wrap: wrap;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Filter functionality
        const filterBtns = document.querySelectorAll('.filter-btn');
        const tableRows = document.querySelectorAll('#reviewsTableBody tr');
        const reviewModal = $('#reviewModal');

        // Filter button click handlers
        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // Update active button
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const status = this.getAttribute('data-status');

                // Filter rows
                tableRows.forEach(row => {
                    if (status === 'all' || row.getAttribute('data-status') === status) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });

        // Search functionality
        const searchBox = document.getElementById('reviewSearchBox');
        searchBox.addEventListener('input', function() {
            const query = searchBox.value.toLowerCase();
            tableRows.forEach(row => {
                const reviewerName = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                const listingName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();

                if (reviewerName.includes(query) || listingName.includes(query)) {
                    // Only show if also matches current filter
                    const currentFilter = document.querySelector('.filter-btn.active').getAttribute('data-status');
                    if (currentFilter === 'all' || row.getAttribute('data-status') === currentFilter) {
                        row.style.display = '';
                    }
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // View review button click handlers
        document.querySelectorAll('.view-review-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Populate modal with review data
                document.getElementById('modal-listing-name').textContent = this.getAttribute('data-listing');
                document.getElementById('modal-reviewer-name').textContent = this.getAttribute('data-reviewer');
                document.getElementById('modal-reviewer-email').textContent = this.getAttribute('data-email');
                document.getElementById('modal-reviewer-mobile').textContent = this.getAttribute('data-mobile') || 'Not provided';
                document.getElementById('modal-reviewer-city').textContent = this.getAttribute('data-city') || 'Not provided';
                document.getElementById('modal-review-date').textContent = this.getAttribute('data-date');
                document.getElementById('modal-review-message').textContent = this.getAttribute('data-message');

                const status = this.getAttribute('data-status');
                const statusSpan = document.getElementById('modal-review-status');
                statusSpan.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                statusSpan.className = 'status-badge status-' + status;

                // Generate rating stars
                const rating = parseInt(this.getAttribute('data-rating'));
                const starsContainer = document.getElementById('modal-rating-stars');
                let starsHtml = '';
                for (let i = 1; i <= 5; i++) {
                    starsHtml += '<span class="star ' + (i <= rating ? 'filled' : '') + '">★</span>';
                }
                starsHtml += '<span class="rating-number">(' + rating + '/5)</span>';
                starsContainer.innerHTML = starsHtml;

                // Show/hide pending actions
                const pendingActions = document.getElementById('modal-pending-actions');
                const reviewId = this.getAttribute('data-review-id');

                if (status === 'pending') {
                    pendingActions.style.display = 'block';
                    document.getElementById('modal-approve-btn').setAttribute('data-review-id', reviewId);
                    document.getElementById('modal-cancel-btn').setAttribute('data-review-id', reviewId);
                } else {
                    pendingActions.style.display = 'none';
                }

                // Show modal
                reviewModal.modal('show');
            });
        });

        // Approve/Cancel button handlers (both in table and modal)
        function handleStatusChange(reviewId, newStatus) {
            if (confirm('Are you sure you want to ' + newStatus + ' this review?')) {
                // Show loading state
                const loadingMsg = 'Updating review status...';
                console.log(loadingMsg);

                // AJAX call to update status
                fetch('<?= base_url("user/update_review_status") ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: 'review_id=' + encodeURIComponent(reviewId) + '&status=' + encodeURIComponent(newStatus)
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        if (!response.ok) {
                            throw new Error('Network response was not ok: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            alert('Review status updated successfully!');
                            location.reload(); // Reload page to show updated status
                        } else {
                            alert('Error updating review status: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error details:', error);
                        alert('Error updating review status. Please check the console for details.');
                    });
            }
        }

        // Table approve/cancel buttons
        document.querySelectorAll('.approve-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                handleStatusChange(this.getAttribute('data-review-id'), 'approved');
            });
        });

        document.querySelectorAll('.cancel-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                handleStatusChange(this.getAttribute('data-review-id'), 'cancelled');
            });
        });

        // Modal approve/cancel buttons
        document.getElementById('modal-approve-btn').addEventListener('click', function() {
            handleStatusChange(this.getAttribute('data-review-id'), 'approved');
        });

        document.getElementById('modal-cancel-btn').addEventListener('click', function() {
            handleStatusChange(this.getAttribute('data-review-id'), 'cancelled');
        });
    });
</script>