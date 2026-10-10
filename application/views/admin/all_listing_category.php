<!-- START -->

<section>

  <div class="ad-com">

    <div class="ad-dash leftpadd">

      <div class="ud-cen">

        <div class="log-bor">&nbsp;</div>

        <span class="udb-inst">Listing Category</span>

        <div class="ud-cen-s2 hcat-cho">

          <h2>All Listing Category</h2>
          <?php if ($this->session->flashdata('message')) { ?>
            <div class="log-suc">
              <p><?= e($this->session->flashdata('message')) ?></p>
            </div>
          <?php } ?>
          <?php if ($this->session->flashdata('error')) { ?>
            <div class="log-err">
              <p><?= e($this->session->flashdata('error')) ?></p>
            </div>
          <?php } ?>
          <?php
          // Server-side search, same contract as /admin/all_listings. The id
          // "pg-sear" is deliberately gone: admin-custom.js binds a page-only
          // filter to it, which would silently hide rows this search found.
          $search_query = isset($search_query) ? $search_query : '';
          ?>
          <div class="ad-int-sear">
            <form method="get" action="<?= base_url('admin/all_listing_category') ?>" style="display:flex; gap:8px; align-items:center;">
              <input type="search" name="q" value="<?= e_attr($search_query) ?>" placeholder="Search all categories…" style="min-width:220px;">
              <button type="submit" class="db-tit-btn" style="position:static; top:auto; right:auto;">Search</button>
              <?php if ($search_query !== '') { ?>
                <a href="<?= base_url('admin/all_listing_category') ?>" style="font-size:0.9rem;">Clear</a>
              <?php } ?>
            </form>
          </div>
          <?php if ($search_query !== '') { ?>
            <p style="margin:8px 0 0; color:#6b7280;">
              <?= (int) ($total_rows ?? 0) ?> categor<?= ((int) ($total_rows ?? 0) === 1 ? 'y' : 'ies') ?>
              matching &ldquo;<?= e($search_query) ?>&rdquo; across every page.
            </p>
          <?php } ?>

          <a href="<?= base_url() ?>admin/add_new_listing_category" class="db-tit-btn">Add New Listing Category</a>

          <?php
          /*
           * Categories that read as the same thing.
           *
           * /all-category was listing "Arts & Entertainment" (4 businesses)
           * next to "Arts and Entertainment" (6), and "Community & Government"
           * (empty) next to "Community and Government" (14) — two URLs and two
           * counts for one kind of business, with the businesses split between
           * them and nothing on either page admitting the other existed.
           *
           * Migration 0031 merges the pairs that are mechanically the same
           * name. This panel is for the ones it deliberately leaves alone —
           * a category added by hand since, and the judgement calls like
           * Finance / Financial Services, which are one category or two
           * depending on how this directory wants to be read.
           */
          $duplicate_groups = (isset($duplicate_groups) && is_array($duplicate_groups)) ? $duplicate_groups : array();
          ?>
          <?php if (!empty($duplicate_groups)): ?>
            <div style="margin:16px 0;padding:14px 16px;border:1px solid #F0D8A8;background:#FDF7EA;border-radius:8px;">
              <h3 style="margin:0 0 6px;font-size:15px;color:#20245B;">
                <?= count($duplicate_groups) ?>
                <?= count($duplicate_groups) === 1 ? 'category is' : 'categories are' ?> listed twice
              </h3>
              <p style="margin:0 0 12px;font-size:13px;color:#5A6072;">
                These names read the same to a visitor, so the businesses are split across two
                directory pages and neither page mentions the other. Merging moves every listing,
                sub-category and category-targeted ad onto the one you keep, then removes the other.
                It is recorded and cannot be undone from here.
              </p>

              <?php foreach ($duplicate_groups as $group): ?>
                <?php $keep = $group[0]; ?>
                <div style="padding:10px 0;border-top:1px solid #F0D8A8;">
                  <div style="margin-bottom:8px;font-size:13.5px;">
                    <?php foreach ($group as $i => $member): ?>
                      <?= $i ? ' &middot; ' : '' ?>
                      <strong><?= e($member->category_name) ?></strong>
                      <span style="color:#6b7280;">(<?= (int) $member->listing_count ?>)</span>
                    <?php endforeach; ?>
                  </div>

                  <?php // One form per loser, so the admin merges a pair at a time
                        // and can see which way round each one goes. ?>
                  <?php foreach ($group as $i => $member): ?>
                    <?php if ($i === 0) continue; ?>
                    <form method="post" action="<?= base_url('admin/merge_listing_category') ?>"
                          style="display:inline-block;margin:0 10px 6px 0;"
                          onsubmit="return confirm('Move the <?= (int) $member->listing_count ?> listing(s) in &quot;<?= e_attr($member->category_name) ?>&quot; into &quot;<?= e_attr($keep->category_name) ?>&quot; and delete the first? This cannot be undone from here.');">
                      <input type="hidden" name="from_category_id" value="<?= (int) $member->category_id ?>">
                      <input type="hidden" name="to_category_id" value="<?= (int) $keep->category_id ?>">
                      <button type="submit" class="db-tit-btn" style="position:static;top:auto;right:auto;">
                        Merge &ldquo;<?= e($member->category_name) ?>&rdquo; into &ldquo;<?= e($keep->category_name) ?>&rdquo;
                      </button>
                    </form>
                  <?php endforeach; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <form method="post" action="<?= base_url('admin/delete_selected_listing_category'); ?>" id="bulk-category-form">

            <div class="bulk-actions" style="margin: 10px 0;">
              <button type="submit" id="bulk-delete-btn" class="db-tit-btn" style="background:#d9534f;" disabled>Delete Selected</button>
              <span id="bulk-selected-count" style="margin-left:10px;color:#666;"></span>
            </div>

          <?php // Rows per page comes from the toolbar dropdown (admin-table.js),
                // so "Select all" only ever ticks rows the admin can see. ?>
          <table data-admin-table data-server-search data-page-size="0" class="responsive-table bordered" id="pg-resu">

            <thead>

              <tr>

                <th><input type="checkbox" id="select-all-categories" title="Select all"></th>

                <th>No</th>

                <th>Category Name</th>

                <th>Category Image</th>

                <th>Created date</th>

                <th>Listing</th>

                <th>Sub Cate</th>

                <th>Edit</th>

                <th>View Sub Cate</th>

                <th>Delete</th>

              </tr>

            </thead>

            <tbody>

              <?php

              if ($all_listing_category) {

                $i = $start_number;

                foreach ($all_listing_category as $category) {

              ?>
                  <tr>

                    <td><input type="checkbox" class="category-checkbox" name="category_ids[]" value="<?= $category->category_id; ?>"></td>

                    <td><?= $i++; ?></td>

                    <td><b class="db-list-rat"><?= $category->category_name; ?></b></td>

                    <td>
                      <img src="<?= ($category->category_image == '') ? base_url('assets/images/no_photo.jpg') : base_url($category->category_image); ?>" alt="<?= $category->category_name; ?>">
                    </td>

                    <td><?= date('d, M Y', strtotime($category->created_at)); ?></td>

                    <td><span class="db-list-ststus" data-toggle="tooltip" title="Total listings in this category"><?= isset($category->listing_count) ? $category->listing_count : 0; ?></span></td>

                    <td><span class="db-list-ststus" data-toggle="tooltip" title="Total Sub Category"><?= isset($category->sub_category_count) ? $category->sub_category_count : 0; ?></span></td>

                    <td><a href="<?= base_url('admin/edit_listing_category/' . $category->category_id); ?>" class="db-list-edit">Edit</a></td>

                    <?php // ?category_id= — the third URI segment is the PAGE number
                          // (all_listing_sub_category($page = 1)), so passing the
                          // category id there asked for page 7 of every sub-category
                          // rather than filtering to this one. ?>
                    <td><a href="<?= base_url('admin/all_listing_sub_category') . '?category_id=' . (int) $category->category_id; ?>" class="db-list-edit">View</a></td>

                    <td>
                      <a class="db-list-edit" onclick="javascript:deleteConfirm('<?php echo base_url() . 'admin/delete_listing_category/' . $category->category_id; ?>');" href="#">Delete</a>
                    </td>

                  </tr>
              <?php }
              } ?>

            </tbody>

          </table>

          </form>
          <div class="float-right" style="margin-top: 20px;">

            <?= $link; ?>

          </div>
        </div>



      </div>

    </div>

  </div>

</section>

<script>
  (function () {
    var selectAll   = document.getElementById('select-all-categories');
    var form        = document.getElementById('bulk-category-form');
    var deleteBtn   = document.getElementById('bulk-delete-btn');
    var countLabel  = document.getElementById('bulk-selected-count');

    if (!form || !deleteBtn) return;

    function checkboxes() {
      return Array.prototype.slice.call(form.querySelectorAll('.category-checkbox'));
    }

    function refresh() {
      var boxes    = checkboxes();
      var selected = boxes.filter(function (b) { return b.checked; });

      deleteBtn.disabled = selected.length === 0;
      countLabel.textContent = selected.length ? selected.length + ' selected' : '';

      if (selectAll) {
        selectAll.checked = boxes.length > 0 && selected.length === boxes.length;
        selectAll.indeterminate = selected.length > 0 && selected.length < boxes.length;
      }
    }

    if (selectAll) {
      selectAll.addEventListener('change', function () {
        checkboxes().forEach(function (b) { b.checked = selectAll.checked; });
        refresh();
      });
    }

    form.addEventListener('change', function (e) {
      if (e.target && e.target.classList.contains('category-checkbox')) refresh();
    });

    form.addEventListener('submit', function (e) {
      var selected = checkboxes().filter(function (b) { return b.checked; });
      if (selected.length === 0) {
        e.preventDefault();
        return;
      }
      if (!confirm('Delete the ' + selected.length + ' selected ' + (selected.length === 1 ? 'category' : 'categories') + '? This cannot be undone.')) {
        e.preventDefault();
      }
    });

    refresh();
  })();
</script>

<!-- END -->