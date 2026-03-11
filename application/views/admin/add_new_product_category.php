

<!-- START -->

<section>

  <div class="ad-com">

    <div class="ad-dash leftpadd">

      <section class="login-reg">

        <div class="container">

          <div class="row">

            <div class="login-main add-list add-ncate">

              <div class="log-bor">&nbsp;</div>

              <span class="udb-inst">New Product Category</span>

              <div class="log log-1">

                <div class="login">

                  <h4>Add New Product Category</h4>

                  <!-- <span class="add-list-add-btn cate-add-btn" data-toggle="tooltip" title="Click to make additional category">+</span>

                  <span class="add-list-rem-btn cate-rem-btn" data-toggle="tooltip" title="Click to remove last category">-</span> -->

                  <form name="category_form" id="category_form" method="post" action="<?= base_url('admin/add_new_product_category') ?>" class="cre-dup-form cre-dup-form-show" enctype="multipart/form-data">

                    <ul>

                      <li>

                        <div class="row">

                          <div class="col-md-12">

                            <div class="form-group">

                              <input type="text" id="category_name" name="category_name" class="form-control" placeholder="Category name *" required>

                            </div>

                          </div>

                          <div class="col-md-12">

                            <div class="form-group">

                              <label>Choose category image</label>

                              <input type="file" name="category_image" id="category_image" class="form-control" required>

                            </div>

                          </div>

                        </div>

                      </li>

                    </ul>

                    <button type="submit" name="category_submit" class="btn btn-primary">Submit</button>

                  </form>

                  <div class="col-md-12">

                    <a href="<?= base_url() ?>admin/all-product-category" class="skip">Go to All Product Category >></a>

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

