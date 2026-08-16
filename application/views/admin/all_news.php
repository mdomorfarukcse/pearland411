<!-- START -->
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <div class="ud-cen">
        <div class="log-bor">&nbsp;</div>
        <span class="udb-inst">News</span>
        <div class="ud-cen-s2">
          <h2>News List</h2>
          <div class="ad-int-sear">
            <input type="text" id="pg-sear" placeholder="Search this page..">
          </div>
          <a href="<?= base_url('admin/add-news'); ?>" class="db-tit-btn">Add News</a>

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
                <th>News Title</th>
                <th>Category</th>
                <th>Created by</th>
                <th>Views</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              if ($newsList != NULL) {
                $i = $start_number;
                foreach ($newsList as $news) {
              ?>
                  <tr>
                    <td><?= $i; ?></td>
                    <td>
                      <?php if (empty($news->news_image)): ?>
                        <img src="<?= base_url('assets/images/no_photo.jpg') ?>" alt="<?= e_attr($news->news_title) ?>" loading="lazy">
                      <?php else: ?>
                        <?= responsive_img($news->news_image, $news->news_title) ?>
                      <?php endif; ?>
                      <?= e($news->news_title); ?><span><?= date('d, M Y', strtotime($news->created_at)); ?></span>
                    </td>
                    <td>
                      <?php if (!empty($news->category_name)) : ?>
                        <a href="" class="db-list-ststus" target="_blank"><?= e($news->category_name) ?></a>
                      <?php endif; ?>
                    </td>
                    <td>
                      <a href="" class="db-list-ststus" target="_blank"><?= e(author_name($news->name)) ?></a>
                    </td>
                    <?php $n_views = (isset($views_counts) && isset($views_counts[$news->news_id])) ? $views_counts[$news->news_id] : 0; ?>
                    <td><a href="" class="db-list-ststus" target="_blank"><?= $n_views; ?></a></td>
                    <td>
                      <?php if ($news->news_status == 0) { ?>
                        <button class="db-list-ststus" onclick="updateStatus(1,<?= $news->news_id; ?>)">In-Active</button>
                      <?php } else { ?>
                        <button class="db-list-ststus" onclick="updateStatus(0,<?= $news->news_id; ?>)">Active</button>
                      <?php } ?>

                      <input type="hidden" id="updateField" value="news_status">
                      <input type="hidden" id="tableName" value="news">
                      <input type="hidden" id="tableIdName" value="news_id">
                      <input type="hidden" id="id" value="<?= $news->news_id; ?>">
                    </td>

                    <td><?= admin_actions(array(
                      'view'   => base_url('news_details/' . rawurlencode((string) $news->news_slug)),
                      'edit'   => base_url('admin/edit-news/' . rawurlencode((string) $news->news_slug)),
                      'delete' => base_url('admin/delete-news/' . rawurlencode((string) $news->news_slug)),
                      'delete_confirm' => 'Delete this news post?',
                    ), 'news post') ?></td>
                  </tr>
                <?php
                  $i++;
                }
              } else {
                ?>
                <tr>
                  <td colspan="5"> <strong class="text-red">No News Available!!</strong></td>
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