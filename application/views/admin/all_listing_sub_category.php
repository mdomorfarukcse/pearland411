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
          <div class="ad-int-sear">

            <input type="text" id="pg-sear" placeholder="Search this page..">

          </div>

          <a href="<?= base_url() ?>admin/add_new_listing_sub_category" class="db-tit-btn">Add new Listing sub category</a>

          <table class="responsive-table bordered tb_cus" id="pg-resu">

            <thead>

              <tr>

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
          <div class="float-right" style="margin-top: 20px;">

            <?= $link; ?>

          </div>
        </div>


      </div>

    </div>

  </div>

</section>

<!-- END -->