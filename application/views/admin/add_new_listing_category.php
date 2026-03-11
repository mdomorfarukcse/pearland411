

<!-- START -->

<section>

  <div class="ad-com">

    <div class="ad-dash leftpadd">

      <section class="login-reg">

        <div class="container">

        	<div class="row">

            <div class="login-main add-list add-ncate">

              <div class="log-bor">&nbsp;</div>

              <span class="udb-inst">New Listing Category</span>

              <div class="log log-1">

                <div class="login">

                  <h4>Add New Listing Category</h4>

                  <!-- <span class="add-list-add-btn cate-add-btn" data-toggle="tooltip" title="Click to make additional category">+</span>

				          <span class="add-list-rem-btn cate-rem-btn" data-toggle="tooltip" title="Click to remove last category">-</span> -->

                  <form name="category_form" id="category_form" method="post" action="<?= base_url('admin/add_new_listing_category') ?>" class="cre-dup-form cre-dup-form-show" enctype="multipart/form-data">

                    <ul>

                      <li>
                        <!--<input type="hidden" name="category_arr[]" value="cate1">-->
                        <div class="row">

                          <div class="col-md-12">

                            <div class="form-group">

                              <!--<input type="text" id="category_name" name="category_name[cate1]" class="form-control" placeholder="Category name *" required>-->
                              <input type="text" id="category_name" name="category_name" class="form-control" placeholder="Category name *" required>
                              <div class='formError'><?= form_error('category_name') ?></div>

                            </div>

                          </div>

                          <div class="col-md-12">

                            <div class="form-group">

                              <label>Choose category image</label>

                              <!--<input type="file" name="category_image[cate1]" id="category_image" class="form-control" >-->
                              <input type="file" name="category_image" id="category_image" class="form-control" >

                            </div>

                          </div>

                        </div>

                      </li>

                    </ul>

                    <button type="submit" name="category_submit" class="btn btn-primary">Submit</button>

                  </form>

                  <div class="col-md-12">

                    <a href="<?= base_url() ?>admin/all_listing_category" class="skip">Go to All Listing Category >></a>

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



