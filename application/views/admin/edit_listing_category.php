
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
                    <?php // The two hidden inputs that were here carried hard-coded
                          // values from the purchased template (category_id="19",
                          // category_image_old="48466ser4.jpg"). Neither was ever
                          // read — the controller uses the URL id and re-reads the
                          // old image from the row — so they are removed rather
                          // than wired up. ?>
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
                            <?php
                            // Through the shared partial so the stored image is
                            // visible: this form used to show a bare file input,
                            // with no way to tell what was already set.
                            $mf_name    = 'category_image';
                            $mf_id      = 'category_image';
                            $mf_label   = 'Choose category image';
                            $mf_hint    = 'The small icon shown beside this category in lists. '
                                          . image_guidance('category')
                                          . ' Leave empty to keep the current one.';
                            $mf_current = isset($category->category_image) ? $category->category_image : '';
                            $mf_empty   = 'No category image yet.';
                            include APPPATH . 'views/admin/partials/media_field.php';
                            ?>
                          </div>
                        </div>
                        <!--FILED END-->
                        <!--FILED START-->
                        <div class="row">
                          <div class="col-md-12">
                            <?php
                            // The editorial header banner (migration 0027). Separate
                            // from the icon above: this one is wide and sits behind
                            // the title at the top of the category's directory page.
                            $mf_name    = 'category_banner';
                            $mf_id      = 'category_banner';
                            $mf_label   = 'Category header banner';
                            $mf_hint    = 'Shown across the top of this category&rsquo;s directory page. '
                                          . '<strong>1600 &times; 420 px</strong> works best. '
                                          . 'Leave empty to use the standard directory header.';
                            $mf_current = isset($category->category_banner) ? $category->category_banner : '';
                            $mf_remove  = 'remove_category_banner';
                            $mf_empty   = 'No banner set &mdash; this category uses the standard directory header.';
                            include APPPATH . 'views/admin/partials/media_field.php';
                            ?>
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
