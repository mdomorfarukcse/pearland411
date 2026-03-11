
<!-- START -->
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <section class="login-reg">
        <div class="container">
          <div class="row">
            <div class="login-main add-list add-ncate">
              <div class="log-bor">&nbsp;</div>
              <span class="udb-inst">Edit Listing Category</span>
              <div class="log log-1">
                <div class="login">
                  <h4>Edit this Listing Category</h4>
                  <!--<form name="category_form" id="category_form" method="post" action="update_category.html" enctype="multipart/form-data" class="cre-dup-form cre-dup-form-show">-->
                  <?php echo form_open_multipart('admin/edit_listing_category/'.$category->category_id,['class'=>'cre-dup-form cre-dup-form-show', 'id'=>'category_form']);?>
                    <input type="hidden" class="validate" id="category_id" name="category_id" value="19" required="required">
                    <input type="hidden" class="validate" id="category_image_old" name="category_image_old" value="48466ser4.jpg" required="required">
                    <ul>
                      <li>
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <input type="text" class="form-control"  id="category_name" name="category_name" value="<?= $category->category_name;?>"  placeholder="Category name *" required>
                            </div>
                          </div>
                        </div>
                        <!--FILED START-->
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <label>Choose category image</label>
                              <input type="file" class="form-control" name="category_image" id="category_image" >
                            </div>
                          </div>
                        </div>
                        <!--FILED END-->
                      </li>
                    </ul>
                    <button type="submit" name="category_submit" class="btn btn-primary">Update</button>
                  </form>
                  <div class="col-md-12">
                    <a href="admin-all-category.html" class="skip">Go to All Listing Category >></a>
                  </div>
                  <div class="ud-notes">
                    <p><b>Notes:</b> Hi, Category image size like 640x480 PX. You better to upload compressed images because it's affected page loading time. <br>Image compressing tool: <a href="https://tinypng.com" target="_blank">tinypng.com</a></p>
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
