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
              <p><?= $this->session->flashdata('message') ?></p>
            </div>
          <?php } ?>
          <div class="ad-int-sear">

            <input type="text" id="pg-sear" placeholder="Search this page..">

          </div>

          <a href="<?= base_url() ?>admin/add_new_listing_category" class="db-tit-btn">Add New Listing Category</a>

          <form method="post" action="<?= base_url('admin/delete_selected_listing_category'); ?>" id="bulk-category-form">

            <div class="bulk-actions" style="margin: 10px 0;">
              <button type="submit" id="bulk-delete-btn" class="db-tit-btn" style="background:#d9534f;" disabled>Delete Selected</button>
              <span id="bulk-selected-count" style="margin-left:10px;color:#666;"></span>
            </div>

          <table data-admin-table class="responsive-table bordered" id="pg-resu">

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

                    <td><a href="<?= base_url('admin/all_listing_sub_category/' . $category->category_id); ?>" class="db-list-edit">View</a></td>

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