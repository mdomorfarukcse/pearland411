<section>

  <div class="ad-com">

    <div class="ad-dash leftpadd">

      <div class="ud-cen">

        <div class="log-bor">&nbsp;</div>

        <span class="udb-inst">All Claim Request listings</span>

        <?php if ($this->session->flashdata('message')) { ?>

          <div class="log-suc">
            <p><?= $this->session->flashdata('message') ?></p>
          </div>

        <?php } ?>

        <div class="ud-cen-s2">

          <h2>Claim Request details</h2>

          <!-- <a href="admin-add-new-listings.html" class="db-tit-btn">Add new listing</a> -->

          <table data-admin-table class="responsive-table bordered">
            <thead>
              <tr>
                <th>No</th>
                <th>Listing</th>
                <th>Claimant</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Proof image</th>
                <th>Message</th>
                <th>Submitted</th>
                <th>Status</th>
                <th>Decision</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($listing_requests): $i = 0; foreach ($listing_requests as $value): $i++; ?>
                <tr>
                  <td><?= $i ?></td>
                  <td><?= e($value->enquiry_source) ?></td>
                  <td><?= e($value->enquiry_name) ?></td>
                  <td><?= e($value->enquiry_mobile) ?></td>
                  <td><a href="mailto:<?= e_attr($value->enquiry_email) ?>"><?= e($value->enquiry_email) ?></a></td>
                  <td>
                    <?php if (!empty($value->enquiry_image)): ?>
                      <a href="<?= base_url($value->enquiry_image) ?>" target="_blank" rel="noopener noreferrer">
                        <img src="<?= base_url($value->enquiry_image) ?>" width="50" alt="Proof" loading="lazy">
                      </a>
                    <?php else: ?>
                      <span style="color:#999;">—</span>
                    <?php endif; ?>
                  </td>
                  <td><?= e_nl2br($value->enquiry_message) ?></td>
                  <td><span class="db-list-rat"><?= date('d, M Y', strtotime($value->claim_created_at)) ?></span></td>
                  <td>
                    <?php $st = (int) $value->claim_status; ?>
                    <?php if ($st === 0): ?>
                      <span class="db-list-rat">Pending</span>
                    <?php elseif ($st === 1): ?>
                      <span style="color:green;font-weight:bold;">Approved</span>
                    <?php elseif ($st === 2): ?>
                      <span style="color:#c00;">Rejected</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ((int) $value->claim_status === 0): ?>
                      <a href="<?= base_url('admin/claim-approve/' . (int) $value->claim_listing_id) ?>"
                         onclick="return confirm('Approve this claim? The listing will be transferred to the claimant.');">
                        <button class="db-list-ststus">Approve</button>
                      </a>
                      <a href="<?= base_url('admin/claim-reject/' . (int) $value->claim_listing_id) ?>"
                         onclick="return confirm('Reject this claim? The claimant will be emailed.');">
                        <button class="db-list-ststus" style="background:#c00;color:#fff;">Reject</button>
                      </a>
                    <?php else: ?>
                      <span>—</span>
                    <?php endif; ?>
                  </td>
                  <td><?php
                    // Build the View link to the listing-details page when we have a slug.
                    $listing_row = $this->db
                      ->select('listing_slug')
                      ->where('listing_id', (int) $value->listing_id)
                      ->limit(1)->get('listings')->row();
                    echo admin_actions(array(
                      'view'   => !empty($listing_row->listing_slug)
                        ? base_url('listing-details/' . rawurlencode((string) $listing_row->listing_slug))
                        : null,
                      'delete' => base_url('admin/delete_claim/' . (int) $value->claim_listing_id),
                      'delete_confirm' => 'Delete this claim?',
                    ), 'claim');
                  ?></td>
                </tr>
              <?php endforeach; else: ?>
                <tr><td colspan="11"><h5>No requests yet.</h5></td></tr>
              <?php endif; ?>
            </tbody>
          </table>

        </div>



      </div>

      <div class="ad-pgnat">

        <!--         <ul class="pagination">

          <li class="page-item"><a class="page-link" href="#">Previous</a></li>

          <li class="page-item active"><a class="page-link" href="#">1</a></li>

          <li class="page-item"><a class="page-link" href="#">2</a></li>

          <li class="page-item"><a class="page-link" href="#">3</a></li>

          <li class="page-item"><a class="page-link" href="#">Next</a></li>

        </ul> -->

      </div>

    </div>

  </div>

</section>

<!-- END -->