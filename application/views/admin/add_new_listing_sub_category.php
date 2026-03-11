<!-- START -->

<section>

  <div class="ad-com">

    <div class="ad-dash leftpadd">

      <section class="login-reg">

        <div class="container">

          <div class="row">

            <div class="login-main add-list add-ncate">

              <div class="log-bor">&nbsp;</div>

              <span class="udb-inst">New Listing Sub Category</span>

              <div class="log log-1">

                <div class="login">

                  <h4>Add New Listing Sub Category</h4>

<!--                   <span class="add-list-add-btn scat-add-btn" data-toggle="tooltip" title="Click to create additional Sub Category field">+</span>

                  <span class="add-list-rem-btn scat-rem-btn" data-toggle="tooltip" title="Click to remove Sub Category field">-</span> -->



                  <form  name="sub_category_form" id="sub_category_form" method="post" action="<?= base_url('admin/add_new_listing_sub_category') ?>" enctype="multipart/form-data" class="cre-dup-form cre-dup-form-show">

                    <div class="row">

                      <div class="col-md-12">

                        <div class="form-group">

                          <select name="category_id" class="form-control" id="category_name">
                            <?php 
                              if($all_listing_category){
                                $i = 0;
                                foreach($all_listing_category as $category){
                                  $i++;
                            ?>        

                            <option value="<?= $category->category_id; ?>"><?= $category->category_name; ?></option>
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

                              <!-- <input type="text" name="sub_category_name[]" class="form-control" placeholder="Sub category name *" required> -->
                              <input type="text" name="sub_category_name" class="form-control" placeholder="Sub category name *" required>
                              <div class='formError'><?= form_error('sub_category_name') ?></div>

                            </div>

                          </div>

                        </div>

                      </li>

                    </ul>

                    <button type="submit" name="sub_category_submit" class="btn btn-primary">Submit</button>

                  </form>

                  <div class="col-md-12">

                    <a href="<?= base_url() ?>admin/all_listing_sub_category" class="skip">Go to All Listing Sub Category >></a>

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

