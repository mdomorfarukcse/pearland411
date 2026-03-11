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

          <table class="responsive-table bordered">

            <thead>

              <tr>

                <th>No</th>

                <th>Listing Name</th>

                <th>Enquirer Name</th>

                <th>Enquirer Mobile</th>

                <th>Enquirer Email Id</th>

                <th>Verification Image</th>

                <th>Enquirer Message</th>

                <th>Enquiry Date</th>

                <th>Status</th>

                <th>Approve</th>

                <th>Delete</th>

                <!-- <th>Preview</th> -->

              </tr>

            </thead>

            <tbody>

              <?php

              if ($listing_requests) {

                $i = 0;

                foreach ($listing_requests as $value) {

                  $i++;

              ?>

                  <tr>

                    <td><?= $i; ?></td>

                    <td><?= $value->enquiry_source; ?></td>

                    <td><?= $value->enquiry_name; ?></td>



                    <td><?= $value->enquiry_mobile; ?></td>

                    <td><?= $value->enquiry_email; ?></td>

                    <td><img src="<?= base_url($value->enquiry_image); ?>" width="50px"></td>

                    <td><?= $value->enquiry_message; ?></td>

                    <td><span class="db-list-rat"><?= date('d, M Y', strtotime($value->claim_created_at)); ?></span></td>



                    <td>

                      <?php if ($value->claim_status == 0) { ?>

                        <span class="db-list-rat">Pending</span>

                      <?php }
                      if ($value->claim_status == 1) { ?>

                        <span class="db-list-rat">Approved</span>

                      <?php } ?>

                    </td>



                    <td>

                      <?php if ($value->claim_status == 0) { ?>

                        <a href="<?= base_url('admin/claim-approve/' . $value->claim_listing_id); ?>"><button class="db-list-ststus">Approve</button></a>

                      <?php } else { ?>

                        <span style="text-align: center;">-</span>

                      <?php } ?>

                      <input type="hidden" id="updateField" value="claim_status">

                      <input type="hidden" id="tableName" value="claim_listings">

                      <input type="hidden" id="tableIdName" value="claim_listing_id">

                      <input type="hidden" id="id" value="<?= $value->claim_listing_id; ?>">

                    </td>





                    <td><a href="#" onclick="javascript:deleteConfirm('<?php echo base_url() . 'admin/delete_claim/' . $value->claim_listing_id; ?>');" class="db-list-edit">Delete</a></td>

                    <!-- <td><a href="../listing-details.html?code=LIST384" class="db-list-edit" target="_blank">Preview</a></td> -->

                  </tr>

                <?php }
              } else { ?>

                <tr>

                  <td colspan="11">
                    <h5>No Requests Available.</h5>
                  </td>

                </tr>

              <?php } ?>

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