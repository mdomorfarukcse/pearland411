<!-- START -->

<section>

  <div class="ad-com">

    <div class="ad-dash leftpadd">

      <div class="ud-cen">

        <div class="log-bor">&nbsp;</div>

        <span class="udb-inst">Product Sub Categories</span>

        <div class="ud-cen-s2 hcat-cho">

          <h2>All Product Sub Categories</h2>
          <?php if ($this->session->flashdata('message')) { ?>
            <div class="log-suc">
              <p><?= $this->session->flashdata('message') ?></p>
            </div>
          <?php } ?>
          <div class="ad-int-sear">

            <input type="text" id="pg-sear" placeholder="Search this page..">

          </div>

          <a href="<?= base_url() ?>admin/add_new_product_sub_category" class="db-tit-btn">Add new product sub category</a>

          <table data-admin-table class="responsive-table bordered" id="pg-resu">

            <thead>

              <tr>

                <th>No</th>

                <th>Sub Category Name</th>

                <th>Main Category Name</th>

                <th>Created date</th>

                <!-- <th>Products</th> -->

                <th>Edit</th>

                <th>Delete</th>

              </tr>

            </thead>

            <tbody>

              <tr>
                <?php

                if ($all_product_sub_category) {

                  $i = $start_number;

                  foreach ($all_product_sub_category as $sub_category) {

                ?>
                    <td><?= $i++; ?></td>

                    <td><b class="db-list-rat"><?= $sub_category->sub_category_name; ?></b></td>

                    <td><b class="db-list-rat"><?= $sub_category->category_name; ?></b></td>

                    <td><?= date('d, M Y', strtotime($sub_category->created_at)); ?></td>
                    <!-- 
                <td>
                  <span class="db-list-ststus" data-toggle="tooltip" title="Total listings in this category">
                    <? //= $sub_category->total_product; 
                    ?>                      
                  </span>
                </td> -->


                    <td><a href="<?= base_url('admin/edit_product_sub_category/' . $sub_category->sub_category_slug); ?>" class="db-list-edit">Edit</a></td>

                    <td><a class="db-list-edit" onclick="javascript:deleteConfirm('<?php echo base_url() . 'admin/delete_product_sub_category/' . $sub_category->sub_category_slug; ?>');" href="#">Delete</a></td>

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