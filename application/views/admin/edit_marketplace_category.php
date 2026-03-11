<!-- START -->
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <section class="login-reg">
        <div class="container">
          <div class="row">
            <div class="login-main add-list add-ncate">
              <div class="log-bor">&nbsp;</div>
              <span class="udb-inst">Edit marketpalce Category</span>

              <div class="log log-1">

                <div class="login">

                  <h4>Edit this marketpalce Category</h4>
                  <?php echo form_open_multipart('admin/edit_marketpalce_category/'.$category->category_id,['class'=>'cre-dup-form cre-dup-form-show', 'id'=>'category_form']);?>
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
                        <div class="col-md-12">
                          <div class="form-group">
                            <label>Choose category image</label>
                            <input type="file" class="form-control" name="category_image" id="category_image" >
                          </div>
                        </div>
                        <div class="col-md-12">
                          <div class="form-group">
                            <label>SEO Page title</label>
                            <input type="text" name="category_seo_title" value="<?= $category->category_seo_title;?>"placeholder=""  class="form-control">
                          </div>
                        </div>
                        <div class="col-md-12">
                          <div class="form-group">
                            <label>SEO Page keywords</label>
                            <input type="text" name="category_seo_keywords" value="<?= $category->category_seo_keywords;?>" class="form-control" placeholder="i.e:wedding hall, best wedding hall">
                          </div>
                        </div>
                        <div class="col-md-12">
                          <div class="form-group">
                            <label>SEO Page descriptions</label>
                            <input type="text" name="category_seo_description" value="<?= $category->category_seo_description;?>"  class="form-control">
                          </div>
                        </div>
                      </div>
                      <!--FILED END-->
                    </li>
                  </ul>
                  <button type="submit" name="category_submit" class="btn btn-primary">Update</button>
                </form>
                <div class="col-md-12">
                  <a href="<?= base_url() ?>admin/all-marketpalce-category" class="skip">Go to All marketpalce Category >></a>
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

