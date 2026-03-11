<!-- START -->
<section>
    <div class="ad-com">
        <div class="ad-dash leftpadd">
            <div class="ud-cen">
                <div class="log-bor">&nbsp;</div>
                <span class="udb-inst">Newsletter Subscribers</span>
                <div class="ud-cen-s2">
                    <h2>Newsletter Subscribers List</h2>

                    <!-- Search Box -->
                    <div class="ad-int-sear">
                        <input type="text" id="subscriberSearchBox" class="form-control" placeholder="Search by Email or Name...">
                    </div>

                    <?php if ($this->session->flashdata('message')) { ?>
                        <div class="log-suc">
                            <p><?= $this->session->flashdata('message') ?></p>
                        </div>
                    <?php } ?>

                    <table class="responsive-table bordered" id="pg-resu">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Email</th>
                                <th>Name</th>
                                <th>IP Address</th>
                                <th>User Agent</th>
                                <th>Confirmed</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="subscriberTableBody">
                            <?php
                            if (!empty($subscribers)) {
                                $i = isset($start_number) ? (int)$start_number : 1;
                                foreach ($subscribers as $s) {
                            ?>
                                    <tr>
                                        <td><?= $i; ?></td>
                                        <td><?= htmlspecialchars($s->email, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars(isset($s->name) ? $s->name : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars(isset($s->ip_address) ? $s->ip_address : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td style="max-width:220px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;"><?= htmlspecialchars(isset($s->user_agent) ? $s->user_agent : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= isset($s->is_confirmed) && $s->is_confirmed ? '<span class="text-success">Confirmed</span>' : '<span class="text-danger">Pending</span>'; ?></td>
                                        <td><?= isset($s->created_at) ? date('d, M Y', strtotime($s->created_at)) : ''; ?></td>
                                        <td>
                                            <a href="#" class="db-list-edit view-subscriber" data-toggle="modal" data-target="#subscriberModal"
                                                data-email="<?= htmlspecialchars($s->email, ENT_QUOTES, 'UTF-8') ?>"
                                                data-name="<?= htmlspecialchars(isset($s->name) ? $s->name : '', ENT_QUOTES, 'UTF-8') ?>"
                                                data-ip="<?= htmlspecialchars(isset($s->ip_address) ? $s->ip_address : '', ENT_QUOTES, 'UTF-8') ?>"
                                                data-agent="<?= htmlspecialchars(isset($s->user_agent) ? $s->user_agent : '', ENT_QUOTES, 'UTF-8') ?>"
                                                data-confirmed="<?= isset($s->is_confirmed) ? (int)$s->is_confirmed : 0 ?>"
                                                data-created="<?= isset($s->created_at) ? $s->created_at : '' ?>">View</a>

                                            <?php if (isset($s->is_confirmed) && $s->is_confirmed): ?>
                                                <a href="<?= base_url('admin/cancel-newsletter-subscription/' . $s->id); ?>" class="db-list-edit" onclick="return confirm('Cancel this subscription?');">Cancel</a>
                                            <?php else: ?>
                                                <span class="db-list-edit disabled">Cancel</span>
                                            <?php endif; ?>

                                            <a href="<?= base_url('admin/delete-newsletter-subscriber/' . $s->id); ?>" class="db-list-edit" onclick="return confirm('Delete this subscriber?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php
                                    $i++;
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="8"> <strong class="text-red">No Subscribers Found!!</strong></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                    <div class="float-right mt-3">
                        <?php if (!empty($link)) {
                            echo $link;
                        } ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
<!-- END -->

<!-- Subscriber Detail Modal -->
<div class="modal fade" id="subscriberModal" tabindex="-1" role="dialog" aria-labelledby="subscriberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;">
                <h5 class="modal-title" id="subscriberModalLabel">Subscriber Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p><strong>Email:</strong> <span id="modalEmail"></span></p>
                <p><strong>Name:</strong> <span id="modalName"></span></p>
                <p><strong>IP Address:</strong> <span id="modalIp"></span></p>
                <p><strong>User Agent:</strong> <span id="modalAgent"></span></p>
                <p><strong>Confirmed:</strong> <span id="modalConfirmed"></span></p>
                <p><strong>Created At:</strong> <span id="modalCreated"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Search/filter functionality for subscribers table
    document.addEventListener('DOMContentLoaded', function() {
        const searchBox = document.getElementById('subscriberSearchBox');
        const tableBody = document.getElementById('subscriberTableBody');
        const rows = Array.from(tableBody.getElementsByTagName('tr'));
        searchBox.addEventListener('input', function() {
            const query = searchBox.value.toLowerCase();
            rows.forEach(row => {
                const emailCell = row.querySelector('td:nth-child(2)');
                const nameCell = row.querySelector('td:nth-child(3)');
                if (emailCell || nameCell) {
                    const emailText = emailCell ? emailCell.textContent.toLowerCase() : '';
                    const nameText = nameCell ? nameCell.textContent.toLowerCase() : '';
                    row.style.display = (emailText.includes(query) || nameText.includes(query)) ? '' : 'none';
                }
            });
        });

        // Modal population
        const viewButtons = document.querySelectorAll('.view-subscriber');
        viewButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('modalEmail').textContent = this.getAttribute('data-email') || '';
                document.getElementById('modalName').textContent = this.getAttribute('data-name') || '';
                document.getElementById('modalIp').textContent = this.getAttribute('data-ip') || '';
                document.getElementById('modalAgent').textContent = this.getAttribute('data-agent') || '';
                document.getElementById('modalConfirmed').textContent = (this.getAttribute('data-confirmed') === '1') ? 'Yes' : 'No';
                document.getElementById('modalCreated').textContent = this.getAttribute('data-created') || '';
            });
        });
    });
</script>