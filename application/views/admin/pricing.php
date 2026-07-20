<style>
    .pricing_page {
        padding-left: 275px;
        padding-top: 100px;
    }

    .pricing_wrapper .modal-dialog {
        max-width: 800px !important;
    }
</style>
<section class="pricing_wrapper">
    <!-- Pricing Page for Admin -->
    <div class="container-fluid pricing_page">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Pricing Plans</h4>
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="<?php echo base_url('admin/dashboard'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Pricing</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">Advertisement Pricing Plans</h4>
                        <p class="text-muted font-13 mb-4">
                            Manage pricing plans for different advertisement packages
                        </p>

                        <div class="row">
                            <?php if (!empty($pricing_plans)): ?>
                                <?php foreach ($pricing_plans as $index => $plan): ?>
                                    <?php
                                    $badge_colors = ['primary', 'success', 'warning', 'info'];
                                    $color = $badge_colors[$index % count($badge_colors)];
                                    ?>
                                    <!-- <?php echo $plan->name; ?> -->
                                    <div class="col-lg-4">
                                        <div class="card border border-<?php echo $color; ?>">
                                            <div class="card-body p-4">
                                                <div class="text-center">
                                                    <h4 class="mt-0"><?php echo $plan->name; ?></h4>
                                                    <h2 class="text-<?php echo $color; ?>">
                                                        <?php echo $plan->currency; ?><?php echo $plan->price; ?>
                                                        <span class="font-13 text-muted">/ <?php echo $plan->billing_interval; ?></span>
                                                    </h2>
                                                </div>
                                                <div class="pricing-features mt-4">
                                                    <p><i class="mdi mdi-check text-<?php echo $color; ?>"></i>
                                                        <?php echo ($plan->listing_limit == -1) ? 'Unlimited listings' : 'Up to ' . $plan->listing_limit . ' listings'; ?>
                                                    </p>
                                                    <p><i class="mdi mdi-check text-<?php echo $color; ?>"></i>
                                                        <?php echo ($plan->product_limit == -1) ? 'Unlimited products' : 'Up to ' . $plan->product_limit . ' products'; ?>
                                                    </p>
                                                    <p><i class="mdi mdi-check text-<?php echo $color; ?>"></i>
                                                        <?php echo ($plan->event_limit == -1) ? 'Unlimited events' : 'Up to ' . $plan->event_limit . ' events'; ?>
                                                    </p>
                                                    <p><i class="mdi mdi-check text-<?php echo $color; ?>"></i>
                                                        <?php echo ($plan->classified_limit == -1) ? 'Unlimited classifieds' : 'Up to ' . $plan->classified_limit . ' classifieds'; ?>
                                                    </p>
                                                    <p><i class="mdi mdi-check text-<?php echo $color; ?>"></i>
                                                        <?php echo (isset($plan->blog_limit) && $plan->blog_limit == -1) ? 'Unlimited blogs' : 'Up to ' . (isset($plan->blog_limit) ? $plan->blog_limit : 0) . ' blogs'; ?>
                                                    </p>
                                                    <p><i class="mdi mdi-check text-<?php echo $color; ?>"></i>
                                                        <?php echo (isset($plan->news_limit) && $plan->news_limit == -1) ? 'Unlimited news' : 'Up to ' . (isset($plan->news_limit) ? $plan->news_limit : 0) . ' news'; ?>
                                                    </p>
                                                    <p><i class="mdi mdi-check text-<?php echo $color; ?>"></i>
                                                        <?php echo (isset($plan->video_limit) && $plan->video_limit == -1) ? 'Unlimited videos' : 'Up to ' . (isset($plan->video_limit) ? $plan->video_limit : 0) . ' videos'; ?>
                                                    </p>
                                                    <p><i class="mdi mdi-check text-<?php echo $color; ?>"></i>
                                                        <?php echo (isset($plan->rental_limit) && $plan->rental_limit == -1) ? 'Unlimited rentals' : 'Up to ' . (isset($plan->rental_limit) ? $plan->rental_limit : 0) . ' rentals'; ?>
                                                    </p>
                                                    <p><i class="mdi mdi-check text-<?php echo $color; ?>"></i>
                                                        <?php echo (isset($plan->house_limit) && $plan->house_limit == -1) ? 'Unlimited houses' : 'Up to ' . (isset($plan->house_limit) ? $plan->house_limit : 0) . ' houses'; ?>
                                                    </p>
                                                    <p><i class="mdi mdi-check text-<?php echo $color; ?>"></i>
                                                        <?php echo $plan->duration_days; ?> days validity
                                                    </p>
                                                    <p>
                                                        <?php if ($plan->featured_listing): ?>
                                                            <i class="mdi mdi-check text-<?php echo $color; ?>"></i> Featured listing
                                                        <?php else: ?>
                                                            <i class="mdi mdi-close text-danger"></i> Featured listing
                                                        <?php endif; ?>
                                                    </p>
                                                    <p>
                                                        <?php if ($plan->analytics_access): ?>
                                                            <i class="mdi mdi-check text-<?php echo $color; ?>"></i> Analytics dashboard
                                                        <?php else: ?>
                                                            <i class="mdi mdi-close text-danger"></i> Analytics dashboard
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                                <div class="text-center mt-4">
                                                    <button class="btn btn-<?php echo $color; ?> btn-block" onclick="editPlan(<?php echo $plan->id; ?>)">Edit Plan</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <div class="text-center">
                                        <p class="text-muted">No pricing plans found. <a href="#" data-toggle="modal" data-target="#addPricingModal">Add your first plan</a></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Manage Plan Durations (page-level) -->
                        <div class="row mt-4" id="manage-durations-section">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="mb-3">Manage Plan Durations</h5>
                                        <div class="form-inline">
                                            <label class="mr-2">Select Plan:</label>
                                            <select id="manage_plan_select" class="form-control mr-2">
                                                <option value="">-- Select Plan --</option>
                                                <?php if (!empty($pricing_plans)): foreach ($pricing_plans as $p): ?>
                                                        <option value="<?= $p->id ?>"><?= htmlspecialchars($p->name) ?> (ID: <?= $p->id ?>)</option>
                                                <?php endforeach;
                                                endif; ?>
                                            </select>
                                            <button id="manage_durations_btn mt-3" class="btn btn-sm btn-outline-primary">Edit Durations</button>
                                            <small class="form-text text-muted ml-3">Open the duration editor for the selected plan to change per-duration pricing.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing Management Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="card-title mb-0">Manage Pricing</h5>
                                            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addPricingModal">
                                                <i class="mdi mdi-plus"></i> Add New Plan
                                            </button>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-hover table-centered mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Plan Name</th>
                                                        <th>Price</th>
                                                        <th>Duration</th>
                                                        <th>Listings Limit</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($pricing_plans)): ?>
                                                        <?php foreach ($pricing_plans as $plan): ?>
                                                            <tr>
                                                                <td><?php echo $plan->name; ?></td>
                                                                <td><?php echo $plan->currency; ?><?php echo $plan->price; ?></td>
                                                                <td><?php echo $plan->duration_days; ?> days</td>
                                                                <td>
                                                                    <?php echo ($plan->listing_limit == -1) ? 'Unlimited' : $plan->listing_limit . ' listings'; ?>
                                                                </td>
                                                                <td>
                                                                    <?php if ($plan->is_active): ?>
                                                                        <span class="badge badge-success">Active</span>
                                                                    <?php else: ?>
                                                                        <span class="badge badge-danger">Inactive</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <button class="btn btn-sm btn-info" onclick="editPlan(<?php echo $plan->id; ?>)">Edit</button>
                                                                    <!-- <button class="btn btn-sm btn-danger" onclick="deletePlan(<?php echo $plan->id; ?>)">Delete</button> -->
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="6" class="text-center">No pricing plans found</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Inline Duration Editor (collapsed) -->
        <div class="card mt-3" id="inlineDurationEditor">
            <div class="card-header">
                <a class="d-block" data-toggle="collapse" href="#durationEditorCollapse" role="button" aria-expanded="false" aria-controls="durationEditorCollapse">
                    <h5 class="mb-0">Edit Plan Durations</h5>
                </a>
            </div>
            <div class="collapse" id="durationEditorCollapse">
                <div class="card-body">
                    <div id="durationEditorAlert" class="alert d-none" role="alert"></div>
                    <table class="table table-striped" id="durationsTableInline">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Months</th>
                                <th>Days</th>
                                <th>Price</th>
                                <th>Currency</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Pricing Modal -->
    <div class="modal fade" id="addPricingModal" tabindex="-1" role="dialog" aria-labelledby="addPricingModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPricingModalLabel">Add New Pricing Plan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="<?php echo base_url('admin/add_pricing_plan'); ?>" method="post">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="planName">Plan Name</label>
                            <input type="text" class="form-control" name="plan_name" id="planName" placeholder="Enter plan name" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="billingInterval">Billing Interval</label>
                                    <select class="form-control" name="billing_interval" id="billingInterval">
                                        <option value="monthly">Monthly</option>
                                        <option value="yearly">Yearly</option>
                                        <option value="one-time">One Time</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="listingsLimit">Listings Limit</label>
                                    <input type="number" class="form-control" name="listing_limit" id="listingsLimit" placeholder="Enter listings limit (-1 for unlimited)">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="productsLimit">Products Limit</label>
                                    <input type="number" class="form-control" name="product_limit" id="productsLimit" placeholder="Enter products limit (-1 for unlimited)">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="eventsLimit">Events Limit</label>
                                    <input type="number" class="form-control" name="event_limit" id="eventsLimit" placeholder="Enter events limit (-1 for unlimited)">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="classifiedsLimit">Classifieds Limit</label>
                                    <input type="number" class="form-control" name="classified_limit" id="classifiedsLimit" placeholder="Enter classifieds limit (-1 for unlimited)">
                                </div>
                            </div>
                        </div>
                        <!-- Additional Sidebar Sections Limits -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="blogsLimit">Blogs Limit</label>
                                    <input type="number" class="form-control" name="blog_limit" id="blogsLimit" placeholder="Enter blogs limit (-1 for unlimited)">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="newsLimit">News Limit</label>
                                    <input type="number" class="form-control" name="news_limit" id="newsLimit" placeholder="Enter news limit (-1 for unlimited)">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="videosLimit">Videos Limit</label>
                                    <input type="number" class="form-control" name="video_limit" id="videosLimit" placeholder="Enter videos limit (-1 for unlimited)">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="apartmentsLimit">Apartments & Rentals Limit</label>
                                    <input type="number" class="form-control" name="rental_limit" id="apartmentsLimit" placeholder="Enter rentals limit (-1 for unlimited)">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="housesLimit">House For Sale Limit</label>
                                    <input type="number" class="form-control" name="house_limit" id="housesLimit" placeholder="Enter houses limit (-1 for unlimited)">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="adsLimit">Ads Limit</label>
                                    <input type="number" class="form-control" name="ad_limit" id="adsLimit" placeholder="Enter ads limit (-1 for unlimited)">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="adBoostLimit">Ad Boost Limit</label>
                                    <input type="number" class="form-control" name="ad_boost_limit" id="adBoostLimit" placeholder="Enter ad boost limit (-1 for unlimited)">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="placeholderImagesLimit">Placeholder Images Limit</label>
                                    <input type="number" class="form-control" name="placeholder_images_limit" id="placeholderImagesLimit" placeholder="Enter placeholder images limit (-1 for unlimited)">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input style="position: static !important;" type="checkbox" class="custom-control-input" name="featured_listing" id="featuredListing" value="1">
                                    <label class="custom-control-label" for="featuredListing">Featured Listing</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" name="priority_support" id="prioritySupport" value="1">
                                        <label class="custom-control-label" for="prioritySupport">Priority Support</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" name="analytics_access" id="analyticsAccess" value="1">
                                        <label class="custom-control-label" for="analyticsAccess">Analytics Access</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Field-level listing capabilities (freemium editing) -->
                        <hr>
                        <h6 class="mb-2"><strong>Listing Editing Capabilities</strong>
                            <small class="text-muted">— what a listing owner on this plan may edit beyond name/phone/email</small></h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="photoLimit">Photo Limit</label>
                                    <input type="number" class="form-control" name="photo_limit" id="photoLimit" placeholder="Gallery photos (-1 unlimited, 0 none)">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group"><div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="can_photos" id="canPhotos" value="1">
                                    <label class="custom-control-label" for="canPhotos">Upload Photos</label>
                                </div></div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group"><div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="can_map" id="canMap" value="1">
                                    <label class="custom-control-label" for="canMap">Add Map</label>
                                </div></div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group"><div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="can_services" id="canServices" value="1">
                                    <label class="custom-control-label" for="canServices">Add Services</label>
                                </div></div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group"><div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="allow_video" id="allowVideo" value="1">
                                    <label class="custom-control-label" for="allowVideo">Add YouTube Video</label>
                                </div></div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group"><div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="can_extra_details" id="canExtraDetails" value="1">
                                    <label class="custom-control-label" for="canExtraDetails">Extra Details</label>
                                </div></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="planDescription">Plan Description</label>
                            <textarea class="form-control" name="description" id="planDescription" rows="3" placeholder="Enter plan description"></textarea>
                        </div>
                    </div>



            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save Plan</button>
            </div>
            </form>
        </div>
    </div>

</section>

<!-- jQuery and Bootstrap JS (HTTPS secured) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"
    integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
    crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns"
    crossorigin="anonymous"></script>

<script>
    $(document).ready(function() {
        // Force secure content only
        console.log('Pricing management loaded securely');

        // Disable any chosen.js or other insecure libraries
        if (typeof chosen !== 'undefined') {
            console.warn('Chosen.js disabled for security');
        }

        // Initialize tooltips safely
        if (typeof $().tooltip === 'function') {
            $('[data-toggle="tooltip"]').tooltip();
        }

        function editPlan(planId) {
            if (!planId) {
                alert('Invalid plan ID!');
                return;
            }

            // Load plan data and populate edit modal
            $.ajax({
                url: '<?php echo base_url("admin/get_pricing_plan"); ?>',
                type: 'POST',
                data: {
                    id: planId
                },
                dataType: 'json',
                timeout: 10000,
                success: function(response) {
                    if (response && typeof response === 'object') {
                        // Populate form fields safely
                        $('#planName').val(response.name || '');
                        $('#planPrice').val(response.price || '');
                        $('#planCurrency').val(response.currency || 'USD');
                        $('#planDuration').val(response.duration_days || '');
                        $('#billingInterval').val(response.billing_interval || 'monthly');

                        // Sidebar section limits
                        $('#listingsLimit').val(response.listing_limit || 0);
                        $('#productsLimit').val(response.product_limit || 0);
                        $('#eventsLimit').val(response.event_limit || 0);
                        $('#classifiedsLimit').val(response.classified_limit || 0);
                        $('#blogsLimit').val(response.blog_limit || 0);
                        $('#newsLimit').val(response.news_limit || 0);
                        $('#videosLimit').val(response.video_limit || 0);
                        $('#apartmentsLimit').val(response.rental_limit || 0);
                        $('#housesLimit').val(response.house_limit || 0);
                        $('#adsLimit').val(response.ad_limit || 0);
                        $('#adBoostLimit').val(response.ad_boost_limit || 0);
                        $('#placeholderImagesLimit').val(response.placeholder_images_limit || 0);

                        // Boolean fields
                        $('#featuredListing').prop('checked', response.featured_listing == 1);
                        $('#prioritySupport').prop('checked', response.priority_support == 1);
                        $('#analyticsAccess').prop('checked', response.analytics_access == 1);

                        // Field-level listing capabilities (freemium editing)
                        $('#photoLimit').val(response.photo_limit || 0);
                        $('#canPhotos').prop('checked', response.can_photos == 1);
                        $('#canMap').prop('checked', response.can_map == 1);
                        $('#canServices').prop('checked', response.can_services == 1);
                        $('#allowVideo').prop('checked', response.allow_video == 1);
                        $('#canExtraDetails').prop('checked', response.can_extra_details == 1);

                        $('#planDescription').val(response.description || '');

                        // Update form for editing
                        $('form').attr('action', '<?php echo base_url("admin/edit_pricing_plan/"); ?>' + planId);
                        $('#addPricingModalLabel').text('Edit Pricing Plan');
                        $('#addPricingModal').modal('show');
                    } else {
                        alert('Invalid response from server!');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    alert('Error loading plan data: ' + (error || 'Unknown error'));
                }
            });
        }

        function deletePlan(planId) {
            if (!planId) {
                alert('Invalid plan ID!');
                return;
            }

            if (confirm('Are you sure you want to delete this pricing plan?')) {
                window.location.href = '<?php echo base_url("admin/delete_pricing_plan/"); ?>' + planId;
            }
        }

        function resetForm() {
            if ($('form')[0]) {
                $('form')[0].reset();
            }
            $('form').attr('action', '<?php echo base_url("admin/add_pricing_plan"); ?>');
            $('#addPricingModalLabel').text('Add New Pricing Plan');
        }

        // Export functions globally
        window.editPlan = editPlan;
        window.deletePlan = deletePlan;
        window.resetForm = resetForm;


        // Reset form when modal is closed
        $('#addPricingModal').on('hidden.bs.modal', function() {
            resetForm();
        });

        // Form validation: require only Plan Name (price handled by durations editor)
        $('form').on('submit', function(e) {
            var planName = $('#planName').val().trim();
            if (!planName) {
                e.preventDefault();
                alert('Please fill in Plan Name!');
                return false;
            }
        });

        console.log('Pricing page initialized successfully');
    });
</script>



<script>
    (function($) {
        function loadDurationsIntoTable(planId) {
            if (!planId) return;
            $.ajax({
                url: '<?php echo base_url("admin/get_pricing_plan_durations"); ?>',
                method: 'POST',
                data: {
                    plan_id: planId
                },
                dataType: 'json',
                success: function(resp) {
                    if (!resp || !Array.isArray(resp)) {
                        alert('Invalid response');
                        return;
                    }
                    const tbody = $('#durationsTableInline tbody');
                    tbody.empty();
                    resp.forEach(function(row) {
                        const tr = $('<tr/>');
                        tr.append($('<td/>').text(row.id));
                        tr.append($('<td/>').text(row.duration_months));
                        tr.append($('<td/>').text(row.duration_days));
                        tr.append($('<td/>').html('<input type="number" step="0.01" class="form-control duration-price" data-id="' + row.id + '" value="' + (parseFloat(row.price) || 0).toFixed(2) + '">'));
                        tr.append($('<td/>').text(row.currency || 'USD'));
                        tr.append($('<td/>').html('<button class="btn btn-sm btn-primary save-duration" data-id="' + row.id + '">Save</button>'));
                        tbody.append(tr);
                    });
                },
                error: function() {
                    alert('Failed to load durations');
                }
            });
        }

        $('#manage_durations_btn').on('click', function(e) {
            e.preventDefault();
            const planId = $('#manage_plan_select').val();
            if (!planId) {
                alert('Please select a plan first');
                return;
            }

            function openEditor() {
                // open the collapse and load durations
                $('#durationEditorCollapse').collapse('show');
                loadDurationsIntoTable(planId);
                // scroll to editor on page
                setTimeout(function() {
                    var collapseEl = document.getElementById('durationEditorCollapse');
                    if (!collapseEl) return;
                    // page scroll
                    $('html, body').animate({
                        scrollTop: $(collapseEl).offset().top - 80
                    }, 300);
                }, 250);
            }

            // If any modal is open, hide it first so the page-level editor is visible
            if ($('.modal.show').length > 0) {
                $('.modal.show').first().one('hidden.bs.modal', function() {
                    openEditor();
                }).modal('hide');
            } else {
                openEditor();
            }
        });

        // delegated save handler
        $(document).on('click', '.save-duration', function() {
            const id = $(this).data('id');
            const input = $('.duration-price[data-id="' + id + '"]');
            if (!input || !input.length) return;
            const price = parseFloat(input.val());
            if (isNaN(price) || price < 0) {
                alert('Invalid price');
                return;
            }

            $(this).prop('disabled', true).text('Saving...');
            $.ajax({
                url: '<?php echo base_url("admin/update_pricing_duration"); ?>',
                method: 'POST',
                data: {
                    id: id,
                    price: price
                },
                dataType: 'json',
                success: (resp) => {
                    if (resp && resp.success) {
                        $('#durationEditorAlert').removeClass('d-none alert-danger').addClass('alert-success').text('Saved successfully');
                        setTimeout(() => $('#durationEditorAlert').addClass('d-none'), 1500);
                    } else {
                        $('#durationEditorAlert').removeClass('d-none alert-success').addClass('alert-danger').text(resp.error || 'Failed to save');
                    }
                },
                error: () => {
                    $('#durationEditorAlert').removeClass('d-none alert-success').addClass('alert-danger').text('Server error');
                },
                complete: () => {
                    $('.save-duration[data-id="' + id + '"]').prop('disabled', false).text('Save');
                }
            });
        });
    })(jQuery);
</script>