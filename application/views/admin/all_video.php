<!-- START -->
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <div class="ud-cen">
        <div class="log-bor">&nbsp;</div>
        <span class="udb-inst">video</span>
        <div class="ud-cen-s2">
          <h2>Video List</h2>
          <div class="ad-int-sear">
            <input type="text" id="pg-sear" placeholder="Search this page..">
          </div>
          <a href="<?= base_url('admin/add-video'); ?>" class="db-tit-btn">Add Video</a>

          <?php if ($this->session->flashdata('message')) { ?>
            <div class="log-suc">
              <p><?= e($this->session->flashdata('message')) ?></p>
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
                <th>Video Title</th>
                <th>Category</th>
                <th>Created by</th>
                <th>Views</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              if ($videoList != NULL) {
                $i = $start_number;
                foreach ($videoList as $video) {
              ?>
                  <tr>
                    <td><?= $i; ?></td>
                    <td>
                      <img src="<?= (empty($video->video_image)) ? base_url('assets/images/no_photo.jpg') :  base_url($video->video_image) ?>"><?= $video->video_title; ?><span><?= date('d, M Y', strtotime($video->created_at)); ?></span>
                    </td>
                    <td>
                      <?php if (!empty($video->category_name)) : ?>
                        <a href="" class="db-list-ststus" target="_blank"><?= $video->category_name ?></a>
                      <?php endif; ?>
                    </td>
                    <td>
                      <a href="" class="db-list-ststus" target="_blank"><?= e(author_name($video->name)) ?></a>
                    </td>
                    <?php $v_views = (isset($views_counts) && isset($views_counts[$video->video_id])) ? $views_counts[$video->video_id] : 0; ?>
                    <td><a href="" class="db-list-ststus" target="_blank"><?= $v_views; ?></a></td>
                    <td>
                      <?php if ($video->video_status == 0) { ?>
                        <button class="db-list-ststus" onclick="updateStatus(1,<?= $video->video_id; ?>)">In-Active</button>
                      <?php } else { ?>
                        <button class="db-list-ststus" onclick="updateStatus(0,<?= $video->video_id; ?>)">Active</button>
                      <?php } ?>

                      <input type="hidden" id="updateField" value="video_status">
                      <input type="hidden" id="tableName" value="video">
                      <input type="hidden" id="tableIdName" value="video_id">
                      <input type="hidden" id="id" value="<?= $video->video_id; ?>">
                    </td>

                    <td><?= admin_actions(array(
                      'view'   => base_url('video_details/' . rawurlencode((string) $video->video_slug)),
                      'edit'   => base_url('admin/edit-video/' . rawurlencode((string) $video->video_slug)),
                      'delete' => base_url('admin/delete-video/' . rawurlencode((string) $video->video_slug)),
                      'delete_confirm' => 'Delete this video?',
                    ), 'video') ?></td>
                  </tr>
                <?php
                  $i++;
                }
              } else {
                ?>
                <tr>
                  <td colspan="5"> <strong class="text-red">No Video Available!!</strong></td>
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