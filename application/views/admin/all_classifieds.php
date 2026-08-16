<!-- START -->
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <div class="ud-cen">
        <div class="log-bor">&nbsp;</div>
        <span class="udb-inst">Classifieds</span>
        <div class="ud-cen-s2">
          <h2>Classifieds List</h2>
          <div class="ad-int-sear">
            <input type="text" id="pg-sear" placeholder="Search this page..">
          </div>
          <a href="<?= base_url('admin/add-classifieds'); ?>" class="db-tit-btn">Add classifieds</a>

          <?php if ($this->session->flashdata('message')) { ?>
            <div class="log-suc">
              <p><?= $this->session->flashdata('message') ?></p>
            </div>
          <?php } ?>
          <?php if ($this->session->flashdata('error')) { ?>
            <div class="alert alert-danger" style="background:#fde2e7;color:#591A2D;border:1px solid #f1b3bf;padding:12px 16px;border-radius:10px;margin-bottom:14px;">
              <?= e($this->session->flashdata('error')) ?>
            </div>
          <?php } ?>
          <table data-admin-table class="responsive-table bordered" id="pg-resu">
            <thead>
              <tr>
                <th>No</th>
                <th>classifieds Title</th>
                <th>Category</th>
                <th>Created by</th>
                <th>Views</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              if ($classifiedsList != NULL) {
                $i = $start_number;
                foreach ($classifiedsList as $classifieds) {
              ?>
                  <tr>
                    <td><?= $i; ?></td>
                    <td>
                      <img src="<?= (empty($classifieds->classifieds_image)) ? base_url('assets/images/no_photo.jpg') :  base_url($classifieds->classifieds_image) ?>"><?= $classifieds->classifieds_title; ?><span><?= date('d, M Y', strtotime($classifieds->created_at)); ?></span>
                    </td>
                    <td>
                      <?php if (!empty($classifieds->category_name)) : ?>
                        <a href="" class="db-list-ststus" target="_blank"><?= $classifieds->category_name ?></a>
                      <?php endif; ?>
                    </td>
                    <td>
                      <a href="" class="db-list-ststus" target="_blank"><?= e(author_name($classifieds->name)) ?></a>
                    </td>
                    <?php $cl_views = (isset($views_counts) && isset($views_counts[$classifieds->classifieds_id])) ? $views_counts[$classifieds->classifieds_id] : 0; ?>
                    <td><a href="" class="db-list-ststus" target="_blank"><?= $cl_views; ?></a></td>
                    <td>
                      <?php if ($classifieds->classifieds_status == 0) { ?>
                        <button class="db-list-ststus" onclick="updateStatus(1,<?= $classifieds->classifieds_id; ?>)">In-Active</button>
                      <?php } else { ?>
                        <button class="db-list-ststus" onclick="updateStatus(0,<?= $classifieds->classifieds_id; ?>)">Active</button>
                      <?php } ?>

                      <input type="hidden" id="updateField" value="classifieds_status">
                      <input type="hidden" id="tableName" value="classifieds">
                      <input type="hidden" id="tableIdName" value="classifieds_id">
                      <input type="hidden" id="id" value="<?= $classifieds->classifieds_id; ?>">
                    </td>

                    <td><?= admin_actions(array(
                      'view'   => base_url('classifieds-details/' . rawurlencode((string) $classifieds->classifieds_slug)),
                      'edit'   => base_url('admin/edit-classifieds/' . rawurlencode((string) $classifieds->classifieds_slug)),
                      'delete' => base_url('admin/delete-classifieds/' . rawurlencode((string) $classifieds->classifieds_slug)),
                      'delete_confirm' => 'Delete this classifieds entry?',
                    ), 'classifieds entry') ?></td>
                  </tr>
                <?php
                  $i++;
                }
              } else {
                ?>
                <tr>
                  <td colspan="5"> <strong class="text-red">No Classifieds Available!!</strong></td>
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