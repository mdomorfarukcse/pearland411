 <!-- START -->

 <section>

   <div class="ad-com">

     <div class="ad-dash leftpadd">

       <div class="ud-cen">

         <div class="log-bor">&nbsp;</div>

         <span class="udb-inst">Product Category</span>

         <div class="ud-cen-s2 hcat-cho">

           <h2>All Product Category</h2>
           <?php if ($this->session->flashdata('message')) { ?>
             <div class="log-suc">
               <p><?= $this->session->flashdata('message') ?></p>
             </div>
           <?php } ?>
           <div class="ad-int-sear">

             <input type="text" id="pg-sear" placeholder="Search this page..">

           </div>

           <a href="<?= base_url() ?>admin/add_new_product_category" class="db-tit-btn">Add new product category</a>

           <table data-admin-table class="responsive-table bordered" id="pg-resu">

             <thead>

               <tr>

                 <th>No</th>

                 <th>Category Name</th>

                 <th>Category Image</th>

                 <th>Created date</th>

                 <th>Product</th>

                 <th>Sub Cate</th>

                 <th>Edit</th>

                 <th>View Sub Cate</th>

                 <th>Delete</th>

               </tr>

             </thead>

             <tbody>
               <?php

                if ($all_product_category) {
                  $i = $start_number;
                  foreach ($all_product_category as $category) {


                ?>
                   <tr>

                     <td><?= $i++ ?></td>

                     <td><b class="db-list-rat"><?= $category->category_name; ?></b></td>

                     <td><img src="<?= base_url($category->category_image); ?>" alt="<?= $category->category_name; ?>" alt=""></td>

                     <td><?= date('d, M Y', strtotime($category->created_at)); ?></td>

                     <td>
                       <span class="db-list-ststus" data-toggle="tooltip" title="Total listings in this category"><?= $category->total_product; ?></span>
                     </td>

                     <td><span class="db-list-ststus" data-toggle="tooltip" title="Total Sub Category"><?= $category->total_sub_cate; ?></span></td>

                     <td><a href="<?= base_url('admin/edit_product_category/' . $category->category_id); ?>" class="db-list-edit">Edit</a></td>


                     <td><a href="<?= base_url('admin/all_product_sub_category/' . $category->category_id); ?>" class="db-list-edit">View</a></td>

                     <td><a class="db-list-edit" onclick="javascript:deleteConfirm('<?php echo base_url() . 'admin/delete_product_category/' . $category->category_id; ?>');" href="#">Delete</a></td>

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