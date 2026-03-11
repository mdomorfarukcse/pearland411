

<!-- START -->

<section>

  <div class="ad-com">

    <div class="ad-dash leftpadd">

      <section class="login-reg">

        <div class="container">

          <div class="row">

            <div class="login-main add-list add-ncate">

              <div class="log-bor">&nbsp;</div>

              <span class="udb-inst">Edit Product Sub Category</span>

              <div class="log log-1">

                <div class="login">

                  <h4>Edit Product Sub Category</h4>

                  <form name="sub_category_form" id="sub_category_form" method="post" action="" enctype="multipart/form-data" action="<?= base_url('admin/edit_product_sub_category/'.$sub_category->sub_category_slug); ?>" class="cre-dup-form cre-dup-form-show">

                    <input type="hidden" class="validate" id="sub_category_id" name="sub_category_id" value="<?= $sub_category->sub_category_id ?>" required="required">



                    <div class="row">

                      <div class="col-md-12">

                        <div class="form-group">
                          <input type="hidden" name="category_id" value="<?= $sub_category->category_id?>" />
                          <select  class="form-control" id="category_name" disabled="true" >
                            <?php 
                            
                            if($all_product_category){

                              $i = 0;

                              foreach($all_product_category as $product_category){
                                $i++;
                              
                            ?>
                            <option value="<?= $product_category->category_id ?>" <?= ($sub_category->category_id == $product_category->category_id) ? 'selected' : ''  ?> readonly ><?= $product_category->category_name ?></option>
                            <?php } } ?>

                          </select>



                        </div>

                      </div>

                    </div>

                    <br>

                    <ul>

                      <li>

                        <div class="row">

                          <div class="col-md-12">

                            <div class="form-group">

                              <input type="text" value="<?= $sub_category->sub_category_name ?>" name="sub_category_name" class="form-control" placeholder="Sub category name *" required>

                            </div>

                          </div>

                        </div>

                      </li>

                    </ul>

                    <button type="submit" name="sub_category_submit" class="btn btn-primary">Update</button>

                  </form>

                  <div class="col-md-12">

                    <a href="<?= base_url('admin/all-product-sub-category');?>" class="skip">Go to All Product Sub Category >></a>

                  </div>



                </div>

              </div>

            </div>

          </div>

        </div>

      </section>



    </div>

  </div>

</section>

<!-- END -->

