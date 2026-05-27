<!-- START -->
<section>
    <div class="ad-com">
        <div class="ad-dash leftpadd">
            <div class="ud-cen">
                <div class="log-bor">&nbsp;</div>
                <span class="udb-inst">Subscriptions</span>
                <div class="ud-cen-s2">
                    <h2>Subscription records</h2>
                    <div class="ad-int-sear">
                        <input type="text" id="pg-sear" placeholder="Search this page..">
                    </div>

                    <?php if ($this->session->flashdata('message')) { ?>
                        <div class="log-suc">
                            <p><?= $this->session->flashdata('message') ?></p>
                        </div>
                    <?php } ?>

                    <table data-admin-table class="responsive-table bordered" id="pg-resu">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>User</th>
                                <th>Plan</th>
                                <th>Price</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($subscriptions)) {
                                $i = $start_number;
                                foreach ($subscriptions as $sub) {
                            ?>
                                    <tr>
                                        <td><?= $i; ?></td>
                                        <td>
                                            <?= isset($sub->user_name) ? $sub->user_name : 'N/A'; ?>
                                            <span><?= isset($sub->user_email) ? $sub->user_email : ''; ?></span>
                                        </td>
                                        <td><?= isset($sub->plan_name) ? $sub->plan_name : 'N/A'; ?></td>
                                        <td><?= isset($sub->plan_price) ? '$' . number_format((float)$sub->plan_price, 2) : (isset($sub->total_amount) ? '$' . number_format((float)$sub->total_amount, 2) : '$0.00'); ?></td>
                                        <td><?= isset($sub->start_date) ? date('d, M Y', strtotime($sub->start_date)) : '-'; ?></td>
                                        <td><?= isset($sub->end_date) ? date('d, M Y', strtotime($sub->end_date)) : '-'; ?></td>
                                        <td>
                                            <a href="#" class="db-list-edit view-sub" data-toggle="modal" data-target="#subModal" data-id="<?= $sub->id; ?>">View</a>
                                            <?php if ($sub->status != 'canceled') { ?>
                                                <a href="<?= base_url('admin/cancel_subscription/' . $sub->id); ?>" class="db-list-edit">Cancel</a>
                                            <?php } ?>
                                            <a href="<?= base_url('admin/delete_subscription/' . $sub->id); ?>" onclick="return confirm('Are you sure you want to delete this subscription?');" class="db-list-edit">Delete</a>
                                        </td>
                                    </tr>
                                    <!-- Hidden details fragment for subscription <?= $sub->id ?> -->
                                    <tr id="sub-frag-row-<?= $sub->id; ?>" style="display:none;">
                                        <td colspan="7">
                                            <div id="sub-details-<?= $sub->id; ?>">
                                                <div class="container-fluid">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6>User Information</h6>
                                                            <p><strong>Name:</strong> <?= isset($sub->user_name) ? htmlspecialchars($sub->user_name) : '-'; ?></p>
                                                            <?php
                                                            // Resolve user email: prefer subscription's user_email, otherwise fetch from user table
                                                            $userEmail = '-';
                                                            if (isset($sub->user_email) && $sub->user_email) {
                                                                $userEmail = htmlspecialchars($sub->user_email);
                                                            } else {
                                                                $u = $this->adminmodel->getSingleValue(isset($sub->user_id) ? $sub->user_id : null, 'user_id', 'user');
                                                                if ($u) {
                                                                    if (!empty($u->email_id)) $userEmail = htmlspecialchars($u->email_id);
                                                                    elseif (!empty($u->mobile_number)) $userEmail = htmlspecialchars($u->mobile_number);
                                                                }
                                                            }
                                                            ?>
                                                            <p><strong>Email:</strong> <?= $userEmail; ?></p>
                                                            <p><strong>User ID:</strong> <?= isset($sub->user_id) ? intval($sub->user_id) : '-'; ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6>Subscription</h6>
                                                            <p><strong>Plan:</strong> <?= isset($sub->plan_name) ? htmlspecialchars($sub->plan_name) : '-'; ?></p>
                                                            <p><strong>Price:</strong> <?= isset($sub->plan_price) ? '$' . number_format((float)$sub->plan_price, 2) : (isset($sub->total_amount) ? '$' . number_format((float)$sub->total_amount, 2) : '$0.00'); ?></p>
                                                            <p><strong>Start:</strong> <?= isset($sub->start_date) ? date('d, M Y', strtotime($sub->start_date)) : '-'; ?></p>
                                                            <p><strong>End:</strong> <?= isset($sub->end_date) ? date('d, M Y', strtotime($sub->end_date)) : '-'; ?></p>
                                                            <p><strong>Status:</strong> <?= isset($sub->status) ? htmlspecialchars(ucfirst($sub->status)) : '-'; ?></p>
                                                        </div>
                                                    </div>
                                                    <hr />
                                                    <h6>Transactions</h6>
                                                    <?php
                                                    // Prefer transactions passed from controller (map: subscription_id => transactions)
                                                    if (isset($subscription_transactions) && isset($subscription_transactions[$sub->id])) {
                                                        $transactions = $subscription_transactions[$sub->id];
                                                    } else {
                                                        $transactions = $this->adminmodel->getSubscriptionTransactions($sub->id);
                                                    }
                                                    if (!empty($transactions)) { ?>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-striped table-bordered">
                                                                <thead>
                                                                    <tr>
                                                                        <th>#</th>
                                                                        <th>Transaction ID</th>
                                                                        <th>Amount</th>
                                                                        <th>Method</th>
                                                                        <th>Status</th>
                                                                        <th>Date</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php $ti = 1;
                                                                    foreach ($transactions as $tr) { ?>
                                                                        <tr>
                                                                            <td><?= $ti++; ?></td>
                                                                            <td><?= isset($tr->txn_ref) ? htmlspecialchars($tr->txn_ref) : (isset($tr->transaction_id) ? htmlspecialchars($tr->transaction_id) : '-'); ?></td>
                                                                            <td><?= isset($tr->amount) ? '$' . number_format((float)$tr->amount, 2) : (isset($tr->total_amount) ? '$' . number_format((float)$tr->total_amount, 2) : '-'); ?></td>
                                                                            <td><?= isset($tr->method) ? htmlspecialchars($tr->method) : (isset($tr->payment_method) ? htmlspecialchars($tr->payment_method) : '-'); ?></td>
                                                                            <td><?= isset($tr->status) ? htmlspecialchars(ucfirst($tr->status)) : (isset($tr->payment_status) ? htmlspecialchars(ucfirst($tr->payment_status)) : '-'); ?></td>
                                                                            <td><?= isset($tr->created_at) ? date('d, M Y H:i', strtotime($tr->created_at)) : '-'; ?></td>
                                                                        </tr>
                                                                    <?php } ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    <?php } else { ?>
                                                        <p>No transactions found.</p>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php
                                    $i++;
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="7"> No Subscriptions Available!!</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="float-right">

                    <?= $link; ?>

                </div>
            </div>
        </div>
    </div>

    <!-- Subscription Modal -->
    <div class="modal" id="subModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Subscription Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="subModalBody">
                    <!-- AJAX loaded content -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

</section>

<style>
    /* Slightly larger fonts for modal content and table for readability */
    #subModal .modal-body {
        font-size: 15px;
    }

    #subModal .modal-body h6 {
        font-size: 16px;
    }

    #subModal table {
        font-size: 14px;
    }

    #subModal .modal-title {
        font-size: 18px;
    }

    /* Make close button slightly larger and easier to click */
    #subModal .modal-footer .btn {
        font-size: 14px;
        padding: 10px 14px;
    }
</style>

<script>
    (function() {
        console.debug('subscription modal script loaded');
        // Helper: show modal with fallback if Bootstrap/jQuery modal isn't available
        function showModalById(id) {
            var modal = document.getElementById(id);
            if (!modal) return;

            // If jQuery + Bootstrap modal is available, use it
            if (typeof $ !== 'undefined' && typeof $.fn === 'object' && typeof $.fn.modal === 'function') {
                try {
                    console.debug('Using Bootstrap modal for', id);
                    $('#' + id).modal('show');
                } catch (err) {
                    console.error('Bootstrap modal show failed', err);
                }
                // If Bootstrap didn't make the modal visible (CSS conflicts), fall back to the vanilla display after a short tick
                setTimeout(function() {
                    try {
                        var comp = window.getComputedStyle ? window.getComputedStyle(modal) : null;
                        var disp = comp ? comp.display : modal.style.display;
                        if (!disp || disp === 'none') {
                            console.warn('Bootstrap modal did not make modal visible, forcing fallback display for', id);
                            // Apply fallback display / backdrop
                            modal.style.position = 'fixed';
                            modal.style.left = '0';
                            modal.style.top = '0';
                            modal.style.width = '100%';
                            modal.style.zIndex = '1055';
                            modal.style.display = 'block';
                            modal.classList.add('show');
                            modal.setAttribute('aria-modal', 'true');
                            modal.setAttribute('role', 'dialog');
                            document.body.classList.add('modal-open');
                            var existing = document.getElementById(id + '-backdrop');
                            if (!existing) {
                                var bd = document.createElement('div');
                                bd.className = 'modal-backdrop fade show';
                                bd.id = id + '-backdrop';
                                document.body.appendChild(bd);
                            }
                        }
                    } catch (e) {
                        console.error('Error checking modal visibility', e);
                    }
                }, 25);
                return;
            }

            // Vanilla JS fallback: add classes and backdrop
            // ensure modal is positioned above other content and visible
            modal.style.position = 'fixed';
            modal.style.left = '0';
            modal.style.top = '0';
            modal.style.width = '100%';
            modal.style.zIndex = '1055';
            modal.style.display = 'block';
            modal.classList.add('show');
            modal.setAttribute('aria-modal', 'true');
            modal.setAttribute('role', 'dialog');
            modal.removeAttribute('aria-hidden');

            // Prevent body scroll
            document.body.classList.add('modal-open');

            // Add backdrop
            var backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = id + '-backdrop';
            // append backdrop to body so it covers entire viewport
            document.body.appendChild(backdrop);
        }

        // Centralized cleanup that forcibly hides modal and removes backdrops
        function cleanupModal(modal) {
            try {
                modal.classList.remove('show');
            } catch (e) {}
            try {
                modal.style.display = 'none';
            } catch (e) {}
            // remove inline positioning so original CSS can apply later
            modal.style.position = '';
            modal.style.left = '';
            modal.style.top = '';
            modal.style.width = '';
            modal.style.zIndex = '';
            try {
                modal.setAttribute('aria-hidden', 'true');
            } catch (e) {}
            // Ensure body scroll is restored
            try {
                document.body.classList.remove('modal-open');
            } catch (e) {}

            // Remove backdrop(s) created by our fallback or by Bootstrap
            try {
                var backdropById = document.getElementById(modal.id + '-backdrop');
                if (backdropById && backdropById.parentNode) backdropById.parentNode.removeChild(backdropById);
            } catch (e) {}
            try {
                var allBackdrops = document.querySelectorAll('.modal-backdrop');
                allBackdrops.forEach(function(b) {
                    if (b && b.parentNode) b.parentNode.removeChild(b);
                });
            } catch (e) {}
        }

        function hideModalById(id) {
            var modal = document.getElementById(id);
            if (!modal) return;

            // If Bootstrap is available, ask it to hide, but still perform a guaranteed cleanup after a short delay
            if (typeof $ !== 'undefined' && typeof $.fn === 'object' && typeof $.fn.modal === 'function') {
                try {
                    $('#' + id).modal('hide');
                } catch (err) {
                    console.error('Bootstrap hide threw', err);
                }
                // after Bootstrap runs, ensure it's really hidden
                setTimeout(function() {
                    try {
                        var comp = window.getComputedStyle ? window.getComputedStyle(modal) : null;
                        var disp = comp ? comp.display : modal.style.display;
                        if (!disp || disp === 'none' || !modal.classList.contains('show')) {
                            // already hidden
                            cleanupModal(modal);
                        } else {
                            // still visible for some reason -> force cleanup
                            console.warn('Modal still visible after Bootstrap hide, forcing cleanup for', id);
                            cleanupModal(modal);
                        }
                    } catch (e) {
                        console.error('Error while verifying modal hide state', e);
                        cleanupModal(modal);
                    }
                }, 50);
                return;
            }

            // No Bootstrap: immediate forced cleanup
            cleanupModal(modal);
        }

        // Delegated click listener for view links
        document.addEventListener('click', function(e) {
            var el = e.target;
            while (el && el.tagName && el.tagName.toLowerCase() !== 'a') {
                el = el.parentNode;
            }
            if (!el) return;
            if (el.classList && el.classList.contains('view-sub')) {
                e.preventDefault();
                var id = el.getAttribute('data-id');
                if (!id) return;
                console.debug('View click detected for subscription id=', id);
                // Inject pre-rendered fragment into modal body
                var frag = document.getElementById('sub-details-' + id);
                var body = document.getElementById('subModalBody');
                if (frag && body) {
                    body.innerHTML = frag.innerHTML;
                    console.debug('Injected fragment into modal body for id=', id);
                } else if (body) {
                    body.innerHTML = '<p>Details not available</p>';
                    console.warn('Fragment not found for id=', id);
                }

                // Show modal (with fallback)
                showModalById('subModal');
            }
        });

        // Force-close modal: always hide and clean up, never rely on Bootstrap only
        function forceCloseModal(e) {
            if (e) e.preventDefault();
            hideModalById('subModal');
        }

        // Attach direct listeners to both close buttons (header X and footer Close)
        var _modalEl = document.getElementById('subModal');
        if (_modalEl) {
            var headerClose = _modalEl.querySelector('.modal-header .close');
            if (headerClose) {
                headerClose.onclick = forceCloseModal;
            }
            var footerClose = _modalEl.querySelector('.modal-footer button');
            if (footerClose) {
                footerClose.onclick = forceCloseModal;
            }
        }

        // Delegated click for any [data-dismiss="modal"] inside #subModal
        document.getElementById('subModal').addEventListener('click', function(e) {
            var el = e.target;
            while (el && el !== this) {
                if (el.getAttribute && el.getAttribute('data-dismiss') === 'modal') {
                    forceCloseModal(e);
                    return;
                }
                el = el.parentNode;
            }
        });

        // Capturing handler: intercept close clicks before other listeners (prevents Bootstrap from blocking/overriding)
        document.addEventListener('click', function captureCloseHandler(e) {
            var el = e.target;
            var modal = document.getElementById('subModal');
            if (!modal) return;
            var matched = false;
            var inner = el;
            while (inner && inner !== document) {
                if (inner.getAttribute && inner.getAttribute('data-dismiss') === 'modal') {
                    matched = true;
                    break;
                }
                if (inner.classList && inner.classList.contains('close')) {
                    matched = true;
                    break;
                }
                inner = inner.parentNode;
            }
            if (matched && modal.contains(inner)) {
                try {
                    e.preventDefault();
                    e.stopPropagation();
                } catch (err) {}
                // Remove Bootstrap modal handlers to avoid any re-show triggered by framework events
                if (typeof $ !== 'undefined' && typeof $.fn === 'object' && typeof $.fn.modal === 'function') {
                    try {
                        $('#' + modal.id).off('show.bs.modal shown.bs.modal hide.bs.modal hidden.bs.modal');
                    } catch (err) {}
                }
                cleanupModal(modal);
            }
        }, true);

        // Also close modal when clicking on backdrop in fallback
        document.addEventListener('click', function(e) {
            var target = e.target;
            if (target && target.classList && target.classList.contains('modal-backdrop')) {
                // find which backdrop this is
                var id = target.id && target.id.replace('-backdrop', '');
                if (id) hideModalById(id);
            }
        });
    })();
</script>

<!-- END -->