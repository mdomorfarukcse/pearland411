<!-- START -->

<section>

  <div class="ad-com">

    <div class="ad-dash leftpadd">

      <div class="ud-cen">

        <div class="log-bor">&nbsp;</div>

        <span class="udb-inst">All Trash listings</span>
        <?php if ($this->session->flashdata('message')) { ?>
          <div class="log-suc">
            <p><?= $this->session->flashdata('message') ?></p>
          </div>
        <?php } ?>
        <div class="ud-cen-s2">
          <h2>Listing details</h2>
          <div class="ad-int-sear">
            <input type="text" id="pg-sear" placeholder="Search this page..">
          </div>
          <a href="<?= base_url() ?>admin/add_new_listings" class="db-tit-btn">Add new listing</a>
          <table data-admin-table class="responsive-table bordered" id="pg-resu">
            <thead>
              <tr>
                <th>No</th>
                <th>Listing Name</th>
                <th>Created Date</th>
                <th>Deleted Date</th>
                <th>Created by</th>
                <th>Edit</th>
                <th>restore</th>
                <th>Delete</th>
                <th>Preview</th>
              </tr>
            </thead>

            <tbody>
              <?php
              if (count($AllListings) > 0) {
                $i = 1;
                foreach ($AllListings as $value) {
              ?>

                  <tr>
                    <td><?= $i; ?></td>
                    <td>
                      <img src="<?= (empty($value->listing_image)) ? base_url('assets/images/no_photo.jpg') :  base_url($value->listing_image) ?>"><?= $value->listing_name; ?><span><?= date('d, M Y', strtotime($value->created_at)); ?></span>
                    </td>
                    <td><span class="db-list-rat"><?= $value->category_name ?></span></td>
                    <td><span class="db-list-rat"><?= $value->postal_code ?></span></td>
                    <td>
                      <span class="db-list-ststus"><?= $value->name; ?></span>
                    </td>
                    <td>
                      <a href="<?= base_url('admin/edit-listing/' . $value->listing_slug); ?>" class="db-list-edit">Edit</a>
                    </td>
                    <td>
                      <a href="#" onclick="javascript:restoreConfirm('<?php echo base_url('admin/restore-listing/' . $value->listing_slug); ?>');" class="db-list-edit">Restore</a>
                    </td>
                    <td>
                      <a href="#" onclick="javascript:deleteConfirm('<?php echo base_url('admin/listing-delete/' . $value->listing_slug); ?>');" class="db-list-edit">Delete Permanently</a>
                    </td>
                    <td>
                      <a href="<?= base_url('admin/listing-preview/' . $value->listing_slug); ?>" class="db-list-edit" target="_blank">Preview</a>
                    </td>
                  </tr>

              <?php
                  $i++;
                }
              }
              ?>
            </tbody>
          </table>
        </div>

        <div class="float-right">
          <?= $link; ?>
        </div>

      </div>

      <div class="ad-pgnat">

        <ul class="pagination">

          <li class="page-item"><a class="page-link" href="#">Previous</a></li>

          <li class="page-item active"><a class="page-link" href="#">1</a></li>

          <li class="page-item"><a class="page-link" href="#">2</a></li>

          <li class="page-item"><a class="page-link" href="#">3</a></li>

          <li class="page-item"><a class="page-link" href="#">Next</a></li>

        </ul>

      </div>

    </div>

  </div>

</section>

<!-- END -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>