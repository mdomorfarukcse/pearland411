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
          <table class="responsive-table bordered" id="pg-resu">
            <thead>
              <tr>
                <th>No</th>
                <th>Post Name</th>
                <th>Created by</th>
                <th>Views</th>
                <th>Status</th>
                <th>Edit</th>
                <th>Delete</th>
                <th>Preview</th>
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
                      <span><?= date('d, M Y', strtotime($blog->created_at)); ?></span>
                    </td>
                    <td>
                      <?php if (!empty($blog->name)) : ?>
                        <a href="#" class="db-list-ststus" target="_blank"><?= e($blog->name); ?></a>
                      <?php endif; ?>
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

                    <td>
                      <a href="<?= base_url('admin/edit-blog/' . $blog->blog_slug); ?>" class="db-list-edit">Edit</a>
                    </td>
                    <td>
                      <a href="#" onclick="javascript:deleteConfirm('<?php echo base_url() . 'admin/delete-blog/' . $blog->blog_slug; ?>');" class="db-list-edit">Delete</a>
                    </td>
                    <td><a href="<?= base_url('admin/blog-details/' . $blog->blog_slug); ?>" class="db-list-edit" target="_blank">Preview</a></td>
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