<!-- START -->

<style>
  .tb_cus th,
  .tb_cus td {
    padding: 15px 0 !important;
  }

  .tb_cus tr td:nth-child(2) {
    font-size: 16px !important;
  }
</style>

<section>

  <div class="ad-com">

    <div class="ad-dash leftpadd">

      <div class="ud-cen">

        <div class="log-bor">&nbsp;</div>

        <span class="udb-inst">Listing Sub Categories</span>

        <div class="ud-cen-s2 hcat-cho">

          <h2>All Listing Sub Categories</h2>
          <?php if ($this->session->flashdata('message')) { ?>
            <div class="log-suc">
              <p><?= $this->session->flashdata('message') ?></p>
            </div>
          <?php } ?>
          <?php
          // Server-side search, same contract as /admin/all_listings. Matches
          // the parent category name too, so searching "Home Services" lists
          // all of its sub-categories. The page-only "pg-sear" input is gone.
          $search_query = isset($search_query) ? $search_query : '';
          ?>
          <div class="ad-int-sear">
            <form method="get" action="<?= base_url('admin/all_listing_sub_category') ?>" style="display:flex; gap:8px; align-items:center;">
              <input type="search" name="q" value="<?= e_attr($search_query) ?>" placeholder="Search all sub-categories…" style="min-width:220px;">
              <button type="submit" class="db-tit-btn" style="position:static; top:auto; right:auto;">Search</button>
              <?php if ($search_query !== '') { ?>
                <a href="<?= base_url('admin/all_listing_sub_category') ?>" style="font-size:0.9rem;">Clear</a>
              <?php } ?>
            </form>
          </div>
          <?php if ($search_query !== '') { ?>
            <p style="margin:8px 0 0; color:#6b7280;">
              <?= (int) ($total_rows ?? 0) ?> sub-categor<?= ((int) ($total_rows ?? 0) === 1 ? 'y' : 'ies') ?>
              matching &ldquo;<?= e($search_query) ?>&rdquo; across every page.
            </p>
          <?php } ?>

          <a href="<?= base_url() ?>admin/add_new_listing_sub_category" class="db-tit-btn">Add new Listing sub category</a>

          <form method="post" action="<?= base_url('admin/delete_selected_listing_sub_category'); ?>" id="bulk-sub-category-form">

            <div class="bulk-actions" style="margin: 10px 0;">
              <button type="submit" id="bulk-delete-sub-btn" class="db-tit-btn" style="background:#d9534f;" disabled>Delete Selected</button>
              <span id="bulk-selected-sub-count" style="margin-left:10px;color:#666;"></span>
            </div>
          <table data-admin-table data-server-search data-page-size="0" class="responsive-table bordered tb_cus" id="pg-resu">

            <thead>

              <tr>

                <th><input type="checkbox" id="select-all-sub-categories" title="Select all"></th>

                <th>No</th>

                <th>Sub Category Name</th>

                <th>Main Category Name</th>

                <th>Created date</th>

                <th>Listing</th>

                <th>Edit</th>

                <th>Delete</th>

              </tr>

            </thead>

            <tbody>
              <?php

              if ($all_listing_sub_category) {

                $i = $start_number;

                foreach ($all_listing_sub_category as $sub_category) {


              ?>
                  <tr>

                    <td><input type="checkbox" class="sub-category-checkbox" name="sub_category_ids[]" value="<?= $sub_category->sub_category_id; ?>"></td>

                    <td><?= $i++; ?></td>

                    <td><b class="db-list-rat"><?= $sub_category->sub_category_name; ?></b></td>

                    <td><b class="db-list-rat"><?= $sub_category->category_name; ?></b></td>

                    <td><?= date('d, M Y', strtotime($sub_category->created_at)); ?></td>

                    <td><span class="db-list-ststus" data-toggle="tooltip" title="Total listings in this sub category"><?= isset($sub_category->listing_count) ? $sub_category->listing_count : 0; ?></span></td>

                    <td><a href="<?= base_url('admin/edit_listing_sub_category/' . $sub_category->sub_category_id); ?>" class="db-list-edit">Edit</a></td>

                    <td><a class="db-list-edit" onclick="javascript:deleteConfirm('<?php echo base_url() . 'admin/delete_listing_sub_category/' . $sub_category->sub_category_id; ?>');" href="#">Delete</a></td>

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
    var selectAll   = document.getElementById('select-all-sub-categories');
    var form        = document.getElementById('bulk-sub-category-form');
    var deleteBtn   = document.getElementById('bulk-delete-sub-btn');
    var countLabel  = document.getElementById('bulk-selected-sub-count');

    if (!form || !deleteBtn) return;

    function checkboxes() {
      return Array.prototype.slice.call(form.querySelectorAll('.sub-category-checkbox'));
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
      if (e.target && e.target.classList.contains('sub-category-checkbox')) refresh();
    });

    form.addEventListener('submit', function (e) {
      var selected = checkboxes().filter(function (b) { return b.checked; });
      if (selected.length === 0) {
        e.preventDefault();
        return;
      }
      if (!confirm('Delete the ' + selected.length + ' selected sub ' + (selected.length === 1 ? 'category' : 'categories') + '? This cannot be undone.')) {
        e.preventDefault();
      }
    });

    refresh();
  })();
</script>

<!-- END -->