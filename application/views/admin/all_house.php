<!-- START -->
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <div class="ud-cen">
        <div class="log-bor">&nbsp;</div>
        <span class="udb-inst">House For Sale</span>
        <div class="ud-cen-s2">
          <h2>House For Sale List</h2>
          <div class="ad-int-sear">
            <input type="text" id="pg-sear" placeholder="Search this page..">
          </div>
          <a href="<?= base_url('admin/add-house'); ?>" class="db-tit-btn">Add House For Sale</a>

          <?php if ($this->session->flashdata('message')) { ?>
            <div class="log-suc">
              <p><?= $this->session->flashdata('message') ?></p>
            </div>
          <?php } ?>
          <table data-admin-table class="responsive-table bordered" id="pg-resu">
            <thead>
              <tr>
                <th>No</th>
                <th>House Title</th>
                <th>Category</th>
                <th>Created by</th>
                <th>Views</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              if ($houseList != NULL) {
                $i = $start_number;
                foreach ($houseList as $house) {
              ?>
                  <tr>
                    <td><?= $i; ?></td>
                    <td>
                      <img src="<?= (empty($house->house_image)) ? base_url('assets/images/no_photo.jpg') :  base_url($house->house_image) ?>"><?= $house->house_title; ?><span><?= date('d, M Y', strtotime($house->created_at)); ?></span>
                    </td>
                    <td>
                      <?php if (!empty($house->category_name)) : ?>
                        <a href="" class="db-list-ststus" target="_blank"><?= $house->category_name ?></a>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if (!empty($house->name)) : ?>
                        <a href="" class="db-list-ststus" target="_blank"><?= $house->name; ?></a>
                      <?php endif; ?>
                    </td>
                    <?php $h_views = (isset($views_counts) && isset($views_counts[$house->house_id])) ? $views_counts[$house->house_id] : 0; ?>
                    <td><a href="" class="db-list-ststus" target="_blank"><?= $h_views; ?></a></td>
                    <td>
                      <?php if ($house->house_status == 0) { ?>
                        <button class="db-list-ststus" onclick="updateStatus(1,<?= $house->house_id; ?>)">In-Active</button>
                      <?php } else { ?>
                        <button class="db-list-ststus" onclick="updateStatus(0,<?= $house->house_id; ?>)">Active</button>
                      <?php } ?>

                      <input type="hidden" id="updateField" value="house_status">
                      <input type="hidden" id="tableName" value="house">
                      <input type="hidden" id="tableIdName" value="house_id">
                      <input type="hidden" id="id" value="<?= $house->house_id; ?>">
                    </td>

                    <td><?= admin_actions(array(
                      'view'   => base_url('house-details/' . rawurlencode((string) $house->house_slug)),
                      'edit'   => base_url('admin/edit_house/' . rawurlencode((string) $house->house_slug)),
                      'delete' => base_url('admin/delete_house/' . rawurlencode((string) $house->house_slug)),
                      'delete_confirm' => 'Delete this house listing?',
                    ), 'house listing') ?></td>
                  </tr>
                <?php
                  $i++;
                }
              } else {
                ?>
                <tr>
                  <td colspan="5"> <strong class="text-red">No house Available!!</strong></td>
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