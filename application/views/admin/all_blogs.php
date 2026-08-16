<!-- START -->
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <div class="ud-cen">
        <div class="log-bor">&nbsp;</div>
        <span class="udb-inst">Blogs</span>
        <div class="ud-cen-s2">
          <h2>Blog post details</h2>
          <div class="ad-int-sear">
            <input type="text" id="pg-sear" placeholder="Search this page..">
          </div>
          <a href="<?= base_url('admin/add-blog'); ?>" class="db-tit-btn">Add new post</a>

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
                <th>Post Name</th>
                <th>Created by</th>
                <th>Views</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              if ($blogList != NULL) {
                $i = $start_number;
                foreach ($blogList as $blog) {
              ?>
                  <tr>
                    <td><?= $i; ?></td>
                    <td>
                      <?= e($blog->blog_name); ?>
                      <?php // Was date('d, M Y', strtotime(...)), which printed the epoch
                            // for the posts that have no date rather than saying so. ?>
                      <span><?= ($__d = us_date($blog->created_at ?? null)) !== '' ? e($__d) : '<em>no date set</em>' ?></span>
                    </td>
                    <td>
                      <a href="#" class="db-list-ststus" target="_blank"><?= e(author_name($blog->name)) ?></a>
                    </td>
                    <?php $b_views = (isset($views_counts) && isset($views_counts[$blog->blog_id])) ? $views_counts[$blog->blog_id] : 0; ?>
                    <td><span class="db-list-rat"><?= (int) $b_views; ?></span></td>

                    <td>
                      <?php if ($blog->blog_status == 0) { ?>
                        <button class="db-list-ststus" onclick="updateStatus(1,<?= $blog->blog_id; ?>)">In-Active</button>
                      <?php } else { ?>
                        <button class="db-list-ststus" onclick="updateStatus(0,<?= $blog->blog_id; ?>)">Active</button>
                      <?php } ?>

                      <input type="hidden" id="updateField" value="blog_status">
                      <input type="hidden" id="tableName" value="blogs">
                      <input type="hidden" id="tableIdName" value="blog_id">
                      <input type="hidden" id="id" value="<?= $blog->blog_id; ?>">
                    </td>

                    <td><?= admin_actions(array(
                      'view'   => base_url('blogs-details/' . rawurlencode((string) $blog->blog_slug)),
                      'edit'   => base_url('admin/edit-blog/' . rawurlencode((string) $blog->blog_slug)),
                      'delete' => base_url('admin/delete-blog/' . rawurlencode((string) $blog->blog_slug)),
                      'delete_confirm' => 'Delete this blog post?',
                    ), 'blog') ?></td>
                  </tr>
                <?php
                  $i++;
                }
              } else {
                ?>
                <tr>
                  <td colspan="5"> No Blogs Available!!</td>
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