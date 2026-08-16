<!-- START -->
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <div class="ud-cen">
        <div class="log-bor">&nbsp;</div>
        <span class="udb-inst">Rental</span>
        <div class="ud-cen-s2">
          <h2>Rental List</h2>
          <div class="ad-int-sear">
            <input type="text" id="pg-sear" placeholder="Search this page..">
          </div>
          <a href="<?= base_url('admin/add-rental'); ?>" class="db-tit-btn">Add Rental</a>

          <?php if ($this->session->flashdata('message')) { ?>
            <div class="log-suc">
              <p><?= $this->session->flashdata('message') ?></p>
            </div>
          <?php } ?>
          <table data-admin-table class="responsive-table bordered" id="pg-resu">
            <thead>
              <tr>
                <th>No</th>
                <th>rental Title</th>
                <th>Category</th>
                <th>Created by</th>
                <th>Views</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              if ($rentalList != NULL) {
                $i = $start_number;
                foreach ($rentalList as $rental) {
              ?>
                  <tr>
                    <td><?= $i; ?></td>
                    <td>
                      <img src="<?= (empty($rental->rental_image)) ? base_url('assets/images/no_photo.jpg') :  base_url($rental->rental_image) ?>"><?= $rental->rental_title; ?><span><?= date('d, M Y', strtotime($rental->created_at)); ?></span>
                    </td>
                    <td>
                      <?php if (!empty($rental->category_name)) : ?>
                        <a href="" class="db-list-ststus" target="_blank"><?= $rental->category_name ?></a>
                      <?php endif; ?>
                    </td>
                    <td>
                      <a href="" class="db-list-ststus" target="_blank"><?= e(author_name($rental->name)) ?></a>
                    </td>
                    <?php $r_views = (isset($views_counts) && isset($views_counts[$rental->rental_id])) ? $views_counts[$rental->rental_id] : 0; ?>
                    <td><a href="" class="db-list-ststus" target="_blank"><?= $r_views; ?></a></td>
                    <td>
                      <?php if ($rental->rental_status == 0) { ?>
                        <button class="db-list-ststus" onclick="updateStatus(1,<?= $rental->rental_id; ?>)">In-Active</button>
                      <?php } else { ?>
                        <button class="db-list-ststus" onclick="updateStatus(0,<?= $rental->rental_id; ?>)">Active</button>
                      <?php } ?>

                      <input type="hidden" id="updateField" value="rental_status">
                      <input type="hidden" id="tableName" value="rental">
                      <input type="hidden" id="tableIdName" value="rental_id">
                      <input type="hidden" id="id" value="<?= $rental->rental_id; ?>">
                    </td>

                    <td><?= admin_actions(array(
                      'view'   => base_url('rental-details/' . rawurlencode((string) $rental->rental_slug)),
                      'edit'   => base_url('admin/edit-rental/' . rawurlencode((string) $rental->rental_slug)),
                      'delete' => base_url('admin/delete-rental/' . rawurlencode((string) $rental->rental_slug)),
                      'delete_confirm' => 'Delete this rental?',
                    ), 'rental') ?></td>
                  </tr>
                <?php
                  $i++;
                }
              } else {
                ?>
                <tr>
                  <td colspan="5"> <strong class="text-red">No Rental Available!!</strong></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
          <div class="float-right">

            <?= $link; ?>

          </div>
        </div>

      </div>
    </div>
  </div>
</section>
<!-- END -->