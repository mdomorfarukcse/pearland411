<style>
  /* Multi-step form styles */
  .multistep-progress-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    position: relative;
  }

  .multistep-progress-bar::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 2px;
    background: #e0e0e0;
    transform: translateY(-50%);
    z-index: 1;
  }

  .multistep-progress-bar .multistep-progress-line {
    position: absolute;
    top: 50%;
    left: 0;
    height: 2px;
    background: #007bff;
    transform: translateY(-50%);
    transition: width 0.3s ease;
    z-index: 2;
  }

  .multistep-step-indicator {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: #666;
    position: relative;
    z-index: 3;
    transition: all 0.3s ease;
    cursor: pointer;
  }

  .multistep-step-indicator.multistep-active {
    background: #007bff;
    color: white;
  }

  .multistep-step-indicator.multistep-completed {
    background: #28a745;
    color: white;
  }

  .multistep-step-indicator.multistep-completed::after {
    content: '✓';
    font-size: 16px;
  }

  .multistep-step-content {
    display: none;
    animation: fadeIn 0.3s ease-in-out;
  }

  .multistep-step-content.multistep-active-content {
    display: block;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(20px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .multistep-navigation-buttons {
    display: flex;
    justify-content: space-between;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
  }

  .multistep-btn-prev,
  .multistep-btn-next,
  .multistep-btn-submit {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
    font-weight: 500;
  }

  .multistep-btn-prev {
    background: #6c757d;
    color: white;
  }

  .multistep-btn-prev:hover {
    background: #545b62;
  }

  .multistep-btn-next,
  .multistep-btn-submit {
    background: #007bff;
    color: white;
  }

  .multistep-btn-next:hover,
  .multistep-btn-submit:hover {
    background: #0056b3;
  }

  .multistep-btn-prev:disabled {
    background: #e9ecef;
    color: #6c757d;
    cursor: not-allowed;
  }

  /* Hide original step indicators */
  .udb-inst,
  .steps {
    display: none;
  }

  /* Smooth transitions */
  .login-main {
    transition: all 0.3s ease;
  }
</style>


<!-- START -->
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <div class="login-reg">
        <div class="container">
          <!-- Multi-step progress bar -->
          <div class="multistep-progress-bar">
            <div class="multistep-progress-line" style="width: 33.33%;"></div>
            <div class="multistep-step-indicator multistep-active" data-step="1">1</div>
            <div class="multistep-step-indicator" data-step="2">2</div>
            <div class="multistep-step-indicator" data-step="3">3</div>
          </div>

          <form action="" class="listing_form" id="listing_form" name="listing_form" method="post" enctype="multipart/form-data">

            <!-- Step 1: Listing Details -->
            <div class="multistep-step-content multistep-active-content" data-step="1">
              <div class="row">
                <div class="login-main add-list posr">
                  <div class="log-bor">&nbsp;</div>
                  <span class="udb-inst">step 1</span>
                  <div class="log log-1">
                    <div class="login">
                      <h4>Edit Listing Details</h4>
                      <!--FILED START-->
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <select name="user_id" class="form-control" required="required">
                              <option value="" disabled>User Name</option>
                              <?php
                              if (isset($users) && count($users) > 0) {
                                foreach ($users as $value) {
                              ?>
                                  <option value="<?= $value->user_id; ?>" <?= (isset($listing) && $listing->user_id == $value->user_id) ? 'SELECTED' : '' ?>><?= $value->name; ?></option>
                              <?php
                                }
                              }
                              ?>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <input id="listing_name" name="listing_name" type="text" required="required" class="form-control" placeholder="Listing name *" value="<?= isset($listing) ? $listing->listing_name : '' ?>">
                          </div>
                        </div>
                      </div>
                      <!--FILED END-->
                      <!--FILED START-->
                      <div class="row mb-1">
                        <div class="col-md-6">
                          <div>
                            <div class="chbox">
                              <input type="checkbox" id="featured_listing" name="featured_listing" value="1" <?= (isset($listing) && $listing->featured_listing == 1) ? 'CHECKED' : '' ?>>
                              <label for="featured_listing">Featured Listing</label>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div>
                            <div class="chbox">
                              <input type="checkbox" id="spotlight_listing" name="spotlight_listing" value="1" <?= (isset($listing) && $listing->spotlight_listing == 1) ? 'CHECKED' : '' ?>>
                              <label for="spotlight_listing">Spotlight Listing</label>
                            </div>
                          </div>
                        </div>
                      </div>
                      <!--FILED END-->
                      <!--FILED START-->
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <select onChange="getSubCategory(this.value);" name="category_id" id="category_id" class="form-control">
                              <option value="">Select Category</option>
                              <?php
                              if (isset($categories) && count($categories) > 0) {
                                foreach ($categories as $value) {
                              ?>
                                  <option value="<?= $value->category_id; ?>" <?= (isset($listing) && $listing->category_id == $value->category_id) ? 'SELECTED' : '' ?>><?= $value->category_name; ?> </option>
                              <?php
                                }
                              }
                              ?>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <select data-placeholder="Select Sub Category" name="sub_category_id" id="sub_category_id" class="chosen-select form-control">
                              <?php if (isset($sub_category_listing) && $sub_category_listing) { ?>
                                <option value="">Choose a Sub Category</option>
                                <?php
                                foreach ($sub_category_listing as $subcat) { ?>
                                  <option value="<?= $subcat->sub_category_id  ?>" <?= (isset($listing) && $subcat->sub_category_id == $listing->sub_category_id) ? 'SELECTED' : '' ?>><?= $subcat->sub_category_name ?></option>
                              <?php
                                }
                              } ?>
                            </select>
                          </div>
                        </div>
                      </div>
                      <!--FILED END-->
                      <!--FILED START-->
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <input type="text" name="listing_mobile" class="form-control" placeholder="Phone number" value="<?= isset($listing) ? $listing->listing_mobile : '' ?>">
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <input type="text" name="listing_whatsapp" class="form-control" placeholder="Whatsapp Number" value="<?= isset($listing) ? $listing->listing_whatsapp : '' ?>">
                          </div>
                        </div>
                        <div class="col-md-12">
                          <div class="form-group row">
                            <label class="col-sm-4">Display Phone Number?</label>
                            <div class="col-sm-4">
                              <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="display_phone" id="display_phone" value="1" <?= (isset($listing) && $listing->display_phone == '1') ? 'CHECKED' : '' ?>>
                                <label class="form-check-label">Yes</label>
                              </div>
                              <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="display_phone" id="display_phone" value="0" <?= (isset($listing) && $listing->display_phone == '0') ? 'CHECKED' : '' ?>>
                                <label class="form-check-label">No</label>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <!--FILED END-->
                      <!--FILED START-->
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <select name="county_name" id="" class="form-control">
                              <option value="">Choose a County</option>
                              <option value="Brazoria" <?= (isset($listing) && $listing->county_name == 'Brazoria') ? 'SELECTED' : '' ?>>Brazoria</option>
                              <option value="Fort Bend" <?= (isset($listing) && $listing->county_name == 'Fort Bend') ? 'SELECTED' : '' ?>>Fort Bend</option>
                              <option value="Harris" <?= (isset($listing) && $listing->county_name == 'Harris') ? 'SELECTED' : '' ?>>Harris</option>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <select name="postal_code" id="" class="form-control">
                              <option value="">Choose a Postal Code</option>
                              <option value="77584" <?= (isset($listing) && $listing->postal_code == '77584') ? 'SELECTED' : '' ?>>77584</option>
                              <option value="77581" <?= (isset($listing) && $listing->postal_code == '77581') ? 'SELECTED' : '' ?>>77581</option>
                              <option value="77588" <?= (isset($listing) && $listing->postal_code == '77588') ? 'SELECTED' : '' ?>>77588</option>
                              <option value="77047" <?= (isset($listing) && $listing->postal_code == '77047') ? 'SELECTED' : '' ?>>77047</option>
                              <option value="77089" <?= (isset($listing) && $listing->postal_code == '77089') ? 'SELECTED' : '' ?>>77089</option>
                            </select>
                          </div>
                        </div>
                      </div>
                      <!--FILED END-->
                      <!--FILED START-->
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <input type="text" name="listing_lat" class="form-control" placeholder="Latitude i.e 40.730610" value="<?= isset($listing) ? $listing->listing_lat : '' ?>">
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <input type="text" name="listing_lng" class="form-control" placeholder="Longitude i.e -73.935242" value="<?= isset($listing) ? $listing->listing_lng : '' ?>">
                          </div>
                        </div>
                      </div>
                      <!--FILED END-->
                      <!--FILED START-->
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <input type="email" name="listing_email" class="form-control" placeholder="Email id" value="<?= isset($listing) ? $listing->listing_email : '' ?>">
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <input type="text" name="listing_website" class="form-control" placeholder="Website(www.website.com)" value="<?= isset($listing) ? $listing->listing_website : '' ?>">
                          </div>
                        </div>
                      </div>
                      <!--FILED END-->
                      <!--FILED START-->
                      <div class="row">
                        <div class="col-md-12">
                          <div class="form-group">
                            <input type="text" name="listing_address" required="required" class="form-control" placeholder="Business address" value="<?= isset($listing) ? $listing->listing_address : '' ?>">
                          </div>
                        </div>
                        <div class="col-md-12">
                          <div class="form-group row">
                            <label class="col-sm-4">Display Address?</label>
                            <div class="col-sm-4">
                              <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="display_address" value="1" id="display_address_yes" <?= (isset($listing) && $listing->display_address == '1') ? 'CHECKED' : '' ?>>
                                <label class="form-check-label">Yes</label>
                              </div>
                              <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="display_address" value="0" id="display_address_no" <?= (isset($listing) && $listing->display_address == '0') ? 'CHECKED' : '' ?>>
                                <label class="form-check-label">No</label>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <!--FILED END-->
                      <!--FILED START-->
                      <div class="row">
                        <div class="col-md-12">
                          <div class="form-group">
                            <label>Details about your listing</label>
                            <textarea class="form-control" id="listing_description" name="listing_description" placeholder="Details about your listing"><?= isset($listing) ? $listing->listing_description : '' ?></textarea>
                          </div>
                        </div>
                      </div>
                      <!--FILED END-->
                      <!--FILED START-->
                      <div class="row">
                        <div class="col-md-12">
                          <div class="form-group">
                            <textarea class="form-control" id="service_locations" name="service_locations" placeholder="Enter your service locations... &#10;(i.e) Pearland,Arlington, Austin"><?= isset($listing) ? $listing->service_locations : '' ?></textarea>
                          </div>
                        </div>
                        <div class="col-md-12">
                          <div class="form-group">
                            <textarea class="form-control" id="service_offered" name="service_offered" placeholder="Enter Offered Services ... &#10;(i.e) Car Wash, Car Service"><?= isset($listing) ? $listing->service_offered : '' ?></textarea>
                          </div>
                        </div>
                      </div>
                      <!--FILED END-->
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Step 2: Photo & Video Gallery -->
            <div class="multistep-step-content" data-step="2">
              <div class="row">
                <div class="login-main add-list">
                  <div class="log-bor">&nbsp;</div>
                  <!-- <span class="steps">Step 2</span> -->
                  <div class="log add-list-map">
                    <div class="login add-list-map">
                      <h4 class="pt30">Photo gallery</h4>
                      <!--FILED START-->
                      <div class="row">
                        <div class="col-md-12">
                          <div class="form-group">
                            <label>Upload Listing Logo</label>
                            <input type="file" name="listing_profile_image" class="form-control">
                            <?php if (isset($listing) && !empty($listing->listing_profile_image)) { ?>
                              <img src="<?= base_url('assets/images/listings/' . $listing->listing_profile_image) ?>" alt="Current Logo" style="max-width:120px; margin-top:10px;" />
                            <?php } ?>
                          </div>
                        </div>
                        <div class="col-md-12">
                          <div class="form-group">
                            <label>Upload Gallery Images (Multiple Images)</label>
                            <input type="file" name="listing_image_gallery[]" class="form-control" multiple accept="image/*">
                            <small class="form-text text-muted">You can select multiple images for your gallery. Supported formats: JPG, PNG, GIF</small>
                            <?php if (isset($listing) && !empty($listing->listing_image_gallery)) {
                              $gallery_images = json_decode($listing->listing_image_gallery, true);
                              if ($gallery_images && is_array($gallery_images)) { ?>
                                <div class="current-gallery" style="margin-top:10px;">
                                  <label>Current Gallery Images:</label>
                                  <div class="gallery-images" style="display:flex; flex-wrap:wrap; gap:10px;">
                                    <?php foreach ($gallery_images as $gallery_img) { ?>
                                      <img src="<?= base_url($gallery_img) ?>" alt="Gallery Image" style="max-width:80px; height:60px; object-fit:cover; border:1px solid #ddd;" />
                                    <?php } ?>
                                  </div>
                                </div>
                            <?php }
                            } ?>
                          </div>
                        </div>
                      </div>
                      <!--FILED END-->

                      <h4>Video Gallery</h4>
                      <ul>
                        <li>
                          <div class="row">
                            <div class="col-md-12">
                              <div class="form-group">
                                <textarea id="listing_video" name="listing_video" class="form-control" placeholder="Paste Your Youtube Url here"><?= isset($listing) ? $listing->listing_video : '' ?></textarea>
                              </div>
                            </div>
                          </div>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Step 3: SEO Settings -->
            <div class="multistep-step-content" data-step="3">
              <div class="row">
                <div class="login-main add-list">
                  <div class="log-bor">&nbsp;</div>
                  <span class="steps">SEO Setting</span>
                  <div class="log add-list-map">
                    <div class="login add-list-map">
                      <div class="form-group">
                        <label>Page title</label>
                        <input type="text" name="listing_seo_title" placeholder="" class="form-control" value="<?= isset($listing) ? $listing->listing_seo_title : '' ?>">
                      </div>
                      <div class="form-group">
                        <label>Page keywords</label>
                        <input type="text" name="listing_seo_keywords" class="form-control" placeholder="i.e:wedding hall, best wedding hall" value="<?= isset($listing) ? $listing->listing_seo_keywords : '' ?>">
                      </div>
                      <div class="form-group">
                        <label>Page descriptions</label>
                        <input type="text" name="listing_seo_description" class="form-control" value="<?= isset($listing) ? $listing->listing_seo_description : '' ?>">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Navigation buttons -->
            <div class="multistep-navigation-buttons">
              <button type="button" class="multistep-btn-prev" disabled>Previous</button>
              <button type="button" class="multistep-btn-next">Next</button>
              <button type="submit" name="listing_submit" class="multistep-btn-submit btn btn-primary" style="display: none;">Update Listing</button>
            </div>

            <!-- Original submit section (hidden) -->
            <div class="row" style="display: none;">
              <div class="login-main add-list">
                <div class="log">
                  <div class="login add-lis-oth">
                    <div class="row">
                      <div class="col-md-12">
                        <button type="submit" name="listing_submit" class="btn btn-primary">Update Listing</button>
                      </div>
                      <div class="col-md-12">
                        <a href="<?= base_url('admin/all-listings'); ?>" class="skip">Go to Listing Dashboard >></a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- END -->

<script>
  // Multi-step form functionality
  document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    const totalSteps = 3;
    const stepIndicators = document.querySelectorAll('.multistep-step-indicator');
    const stepContents = document.querySelectorAll('.multistep-step-content');
    const progressLine = document.querySelector('.multistep-progress-line');
    const prevBtn = document.querySelector('.multistep-btn-prev');
    const nextBtn = document.querySelector('.multistep-btn-next');
    const submitBtn = document.querySelector('.multistep-btn-submit');
    const navButtons = document.querySelector('.multistep-navigation-buttons');
    const originalSubmitSection = document.querySelector('.row[style="display: none;"]');

    function validateCurrentStep() {
      const currentStepContent = document.querySelector(`[data-step="${currentStep}"]`);
      const requiredFields = currentStepContent.querySelectorAll('[required]');
      for (let field of requiredFields) {
        if (!field.value.trim()) {
          field.focus();
          alert('Please fill in all required fields before proceeding.');
          return false;
        }
      }
      return true;
    }

    function updateStepDisplay() {
      // Update step indicators
      stepIndicators.forEach((indicator, index) => {
        const stepNumber = index + 1;
        indicator.classList.remove('multistep-active', 'multistep-completed');
        if (stepNumber < currentStep) {
          indicator.classList.add('multistep-completed');
        } else if (stepNumber === currentStep) {
          indicator.classList.add('multistep-active');
        }
      });
      // Update step content
      stepContents.forEach((content, index) => {
        content.classList.remove('multistep-active-content');
        if (index + 1 === currentStep) {
          content.classList.add('multistep-active-content');
        }
      });
      // Update progress line
      const progressWidth = ((currentStep - 1) / (totalSteps - 1)) * 100;
      progressLine.style.width = progressWidth + '%';
      // Update navigation buttons and submit section
      prevBtn.disabled = currentStep === 1;
      if (currentStep === totalSteps) {
        if (navButtons) navButtons.style.display = 'none';
        if (originalSubmitSection) originalSubmitSection.style.display = 'flex';
      } else {
        if (navButtons) navButtons.style.display = 'flex';
        if (originalSubmitSection) originalSubmitSection.style.display = 'none';
      }
    }

    // Next button click
    nextBtn.addEventListener('click', function() {
      if (validateCurrentStep() && currentStep < totalSteps) {
        currentStep++;
        updateStepDisplay();
      }
    });
    // Previous button click
    prevBtn.addEventListener('click', function() {
      if (currentStep > 1) {
        currentStep--;
        updateStepDisplay();
      }
    });
    // Step indicator click (allow direct navigation)
    stepIndicators.forEach((indicator, index) => {
      indicator.addEventListener('click', function() {
        const targetStep = index + 1;
        if (targetStep <= currentStep || (targetStep === currentStep + 1 && validateCurrentStep())) {
          currentStep = targetStep;
          updateStepDisplay();
        }
      });
    });
    // Initialize
    updateStepDisplay();
  });

  // Fetches sub-categories for the chosen parent category and rebuilds
  // the #sub_category_id <select>. Hits the same admin endpoint that
  // add_new_listings_start.php uses, so the two forms stay in sync.
  function getSubCategory(val) {
    $.ajax({
      type: 'POST',
      url:  '<?= base_url() ?>admin/get_listing_sub_category',
      data: { category_id: val },
      success: function (data) {
        $('#sub_category_id').html(data);
        $('#sub_category_id').trigger('chosen:updated');
        if (window.jQuery && $.fn.select2) {
          $('#sub_category_id').trigger('change.select2');
        }
      }
    });
  }

  // CKEditor would be initialized here if available
  // CKEDITOR.replace('listing_description');
</script>