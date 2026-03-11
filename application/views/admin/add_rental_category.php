<!-- START -->
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <section class="login-reg">
        <div class="container">
          <div class="row">
            <div class="login-main add-list add-ncate">
              <div class="log-bor">&nbsp;</div>
              <span class="udb-inst">New Rental Category</span>
              <div class="log log-1">
                <div class="login">
                  <h4>Add Rental Category</h4>
                  <form name="category_form" id="category_form" method="post" action="<?= base_url('admin/add_rental_category') ?>" class="cre-dup-form cre-dup-form-show" enctype="multipart/form-data">
                    <ul>
                      <li>
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <input type="text" id="category_name" name="category_name" class="form-control" placeholder="Category name *" required>
                              <div class='formError'><?= form_error('category_name') ?></div>
                            </div>
                          </div>
                          <div class="col-md-12">
                            <div class="form-group">
                              <label>Choose category image</label>
                              <input type="file" name="category_image" id="category_image" class="form-control" >
                            </div>
                          </div>
                          <div class="col-md-12">
                            <div class="form-group">
                              <label>SEO Page title</label>
                              <input type="text" name="category_seo_title" value="" placeholder=""  class="form-control">
                            </div>
                          </div>
                          <div class="col-md-12">
                            <div class="form-group">
                              <label>SEO Page keywords</label>
                              <input type="text" name="category_seo_keywords" value=""  class="form-control" placeholder="i.e:wedding hall, best wedding hall">
                            </div>
                          </div>
                          <div class="col-md-12">
                            <div class="form-group">
                              <label>SEO Page descriptions</label>
                              <input type="text" name="category_seo_description" value=""  class="form-control">
                            </div>
                          </div>
                        </div>
                      </li>
                    </ul>
                    <button type="submit" name="category_submit" class="btn btn-primary">Submit</button>
                  </form>
                  <div class="col-md-12">
                    <a href="<?= base_url() ?>admin/all-rental-category" class="skip">Go to All Rental Category >></a>
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



