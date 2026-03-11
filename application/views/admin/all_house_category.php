<!-- START -->
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <div class="ud-cen">
        <div class="log-bor">&nbsp;</div>
        <span class="udb-inst">House Category</span>
        <div class="ud-cen-s2 hcat-cho">
          <h2>All House Category</h2>
          <?php if ($this->session->flashdata('message')) { ?>
            <div class="log-suc">
              <p><?= $this->session->flashdata('message') ?></p>
            </div>
          <?php } ?>
          <div class="ad-int-sear">
            <input type="text" id="pg-sear" placeholder="Search this page..">
          </div>

          <a href="<?= base_url() ?>admin/add-house-category" class="db-tit-btn">Add house Category</a>
          <table class="responsive-table bordered" id="pg-resu">
            <thead>
              <tr>
                <th>No</th>
                <th>Category Name</th>
                <th>Category Image</th>
                <th>Created date</th>
                <th>House</th>
                <th>Edit</th>
                <th>Delete</th>
              </tr>
            </thead>
            <tbody>
              <?php
              if ($all_house_category) {
                $i = $start_number;
                foreach ($all_house_category as $category) {

              ?>
                  <tr>
                    <td><?= $i++; ?></td>
                    <td><b class="db-list-rat"><?= $category->category_name; ?></b></td>
                    <td><img src="<?= ($category->category_image == '') ? base_url('assets/images/no_photo.jpg') : base_url($category->category_image); ?>" alt="<?= $category->category_name; ?>"></td>
                    <td><?= date('d, M Y', strtotime($category->created_at)); ?></td>
                    <td><span class="db-list-ststus" data-toggle="tooltip" title="Total house in this category"><?= isset($category->house_count) ? $category->house_count : 0; ?></span></td>
                    <td><a href="<?= base_url('admin/edit_house_category/' . $category->category_slug); ?>" class="db-list-edit">Edit</a></td>
                    <td>
                      <a class="db-list-edit" onclick="javascript:deleteConfirm('<?php echo base_url() . 'admin/delete_house_category/' . $category->category_slug; ?>');" href="#">Delete</a>
                    </td>

                  </tr>
              <?php }
              } ?>
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