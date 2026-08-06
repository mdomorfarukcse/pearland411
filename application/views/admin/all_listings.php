<!-- START -->

<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <div class="ud-cen">
        <div class="log-bor">&nbsp;</div>
        <span class="udb-inst">All Listing Details</span>
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
        <div class="ud-cen-s2">

          <h2>Listing details</h2>
          <?php
          // A real GET form, not the old #pg-sear box. That one was picked up by
          // admin-table.js and filtered the ten rows already on the page, so a
          // business on page four could not be found by typing its name. This
          // searches every listing in SQL — see Adminmodel::getAllListings().
          $search_query = isset($search_query) ? $search_query : '';
          ?>
          <div class="ad-int-sear">
            <form method="get" action="<?= base_url('admin/all_listings') ?>" style="display:flex; gap:8px; align-items:center;">
              <input type="search" name="q" value="<?= e_attr($search_query) ?>" placeholder="Search all listings…" style="min-width:220px;">
              <button type="submit" class="db-tit-btn" style="position:static; top:auto; right:auto;">Search</button>
              <?php if ($search_query !== '') { ?>
                <a href="<?= base_url('admin/all_listings') ?>" style="font-size:0.9rem;">Clear</a>
              <?php } ?>
            </form>
          </div>
          <?php if ($search_query !== '') { ?>
            <p style="margin:8px 0 0; color:#6b7280;">
              <?= (int) ($total_rows ?? 0) ?> listing<?= ((int) ($total_rows ?? 0) === 1 ? '' : 's') ?>
              matching &ldquo;<?= e($search_query) ?>&rdquo; across the whole directory.
            </p>
          <?php } ?>
          <div class="listing-action-btns" style="position:absolute; right:35px; top:18px; display:flex; flex-wrap:wrap; gap:8px; justify-content:flex-end; z-index:5;">
            <a href="<?= base_url() ?>admin/export-listings" class="db-tit-btn" style="position:static; top:auto; right:auto;">Export CSV</a>
            <a href="<?= base_url() ?>admin/import-listings" class="db-tit-btn" style="position:static; top:auto; right:auto;">Import CSV/Excel</a>
            <a href="<?= base_url() ?>admin/add-new-listing" class="db-tit-btn" style="position:static; top:auto; right:auto;">Add new listing</a>
          </div>

          <?php // data-server-search: the toolbar renders the rows-per-page dropdown
                // but NOT a second search box, because the search above is the
                // server's and covers every listing rather than this page.
                // data-page-size="0" leaves paging to the server pager below. ?>
          <table data-admin-table data-server-search data-page-size="0" class="responsive-table bordered" id="pg-resu">
            <thead>
              <tr>
                <th>No</th>
                <th>Listing Name</th>
                <th>Category</th>
                <th>Postal Code</th>
                <th>Views</th>
                <th>Created by</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>

            <tbody>

              <?php

              if (count($AllListings) > 0) {

                $i = $start_number;

                foreach ($AllListings as $value) {

              ?>

                  <tr>

                    <td><?= $i; ?></td>

                    <td>
                      <?php if (empty($value->listing_image)): ?>
                        <img src="<?= base_url('assets/images/no_photo.jpg') ?>" alt="<?= e_attr($value->listing_name) ?>" loading="lazy">
                      <?php else: ?>
                        <?= responsive_img($value->listing_image, $value->listing_name) ?>
                      <?php endif; ?>
                      <?= e($value->listing_name); ?><span><?= date('d, M Y', strtotime($value->created_at)); ?></span>

                    <td>
                      <?php if (!empty($value->category_name)): ?>
                        <span class="db-list-rat"><?= e($value->category_name) ?></span>
                      <?php endif; ?>
                    </td>

                    <td>
                      <?php if (!empty($value->postal_code)): ?>
                        <span class="db-list-rat"><?= e($value->postal_code) ?></span>
                      <?php endif; ?>
                    </td>


                    <?php $view_count = (isset($views_counts) && isset($views_counts[$value->listing_id])) ? $views_counts[$value->listing_id] : 0; ?>
                    <td><span class="db-list-rat"><?= $view_count; ?></span> </td>

                    <td>
                      <?php if (!empty($value->name)) : ?>
                        <?= e($value->name); ?>
                      <?php endif; ?>
                    </td>

                    <td>

                      <?php if ($value->listing_status == 0) { ?>

                        <button class="db-list-ststus" onclick="updateStatus(1,<?= $value->listing_id; ?>)">In-Active</button>

                      <?php } else { ?>

                        <button class="db-list-ststus" onclick="updateStatus(0,<?= $value->listing_id; ?>)">Active</button>

                      <?php } ?>

                      <input type="hidden" id="updateField" value="listing_status">

                      <input type="hidden" id="tableName" value="listings">

                      <input type="hidden" id="tableIdName" value="listing_id">

                      <input type="hidden" id="id" value="<?= $value->listing_id; ?>">

                    </td>

                    <td><?= admin_actions(array(
                      'view'   => base_url('listing-details/' . rawurlencode((string) $value->listing_slug)),
                      'edit'   => base_url('admin/edit-listing/' . rawurlencode((string) $value->listing_slug)),
                      // Delete by id, not slug: two businesses with the same name
                      // share a slug, and deleting by slug took both of them.
                      'delete' => base_url('admin/delete_listing/' . (int) $value->listing_id),
                      'delete_confirm' => 'Delete this listing?',
                    ), 'listing') ?></td>
                  </tr>

              <?php

                  $i++;
                }
              }

              ?>

            </tbody>

          </table>
          <div class="float-right" style="margin-top: 20px;">

            <?= $link; ?>

          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- END -->

<script>
  function deleteConfirm(url) {
    if (confirm('Are you sure you want to delete this listing? This action cannot be undone.')) {
      window.location.href = url;
    }
  }
</script>