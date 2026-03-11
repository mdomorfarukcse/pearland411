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

  /* Error message styles */
  .multistep-error-message {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
    border-radius: 5px;
    padding: 12px 15px;
    margin-bottom: 20px;
    display: none;
    animation: slideDown 0.3s ease;
  }

  .multistep-error-message.show {
    display: block;
  }

  @keyframes slideDown {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .multistep-error-message ul {
    margin: 0;
    padding-left: 20px;
  }

  .multistep-error-message li {
    margin-bottom: 3px;
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

          <!-- Error message area -->
          <div id="multistep-error-container" class="multistep-error-message">
            <strong>To continue, please complete the following:</strong>
            <ul id="multistep-error-list"></ul>
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

                      <h4>Listing Details</h4>
                      <!--FILED START-->
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <select name="user_id" class="form-control" required="required">
                              <option value="" disabled selected>User Name</option>
                              <?php
                              if (count($users) > 0) {
                                foreach ($users as $value) {
                              ?>
                                  <option value="<?= $value->user_id; ?>"><?= $value->name; ?></option>

                              <?php

                                }
                              }

                              ?>

                            </select>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <input id="listing_name" name="listing_name" type="text" required="required" class="form-control" placeholder="Listing name *">
                          </div>
                        </div>
                      </div>
                      <!--FILED END-->


                      <!--FILED START-->
                      <div class="row mb-1">
                        <div class="col-md-6">
                          <div>
                            <div class="chbox">
                              <input type="checkbox" id="featured_listing" name="featured_listing" required="required" checked value="1">
                              <label for="featured_listing">Featured Listing</label>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div>
                            <div class="chbox">
                              <input type="checkbox" id="spotlight_listing" name="spotlight_listing" required="required" checked value="1">
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
                              if (count($categories) > 0) {
                                foreach ($categories as $value) {
                              ?>
                                  <option value="<?= $value->category_id; ?>"><?= $value->category_name; ?></option>
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
                            </select>
                          </div>
                        </div>
                      </div>
                      <!--FILED END-->

                      <!--FILED START-->

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <input type="text" name="listing_mobile" class="form-control" placeholder="Phone number">
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <input type="text" name="listing_whatsapp" class="form-control" placeholder="Whatsapp Number">
                          </div>
                        </div>
                        <div class="col-md-12">
                          <div class="form-group row">
                            <label class="col-sm-4">Display Phone Number?</label>
                            <div class="col-sm-4">
                              <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="display_phone" id="display_phone" value="1">
                                <label class="form-check-label">Yes</label>
                              </div>
                              <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="display_phone" id="display_phone" value="0">
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
                              <option value="Brazoria">Brazoria</option>
                              <option value="Fort Bend">Fort Bend</option>
                              <option value="Harris">Harris</option>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <select name="postal_code" id="" class="form-control">
                              <option value="">Choose a Postal Code</option>
                              <option value="77584">77584</option>
                              <option value="77581">77581</option>
                              <option value="77588">77588</option>
                              <option value="77047">77047</option>
                              <option value="77089">77089</option>
                            </select>
                          </div>
                        </div>
                      </div>
                      <!--FILED END-->
                      <!--FILED START-->

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <input type="text" name="listing_lat" class="form-control" placeholder="Latitude i.e 40.730610">
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <input type="text" name="listing_lng" class="form-control" placeholder="Longitude i.e -73.935242">
                          </div>
                        </div>
                      </div>
                      <!--FILED END-->
                      <!--FILED START-->

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <input type="email" name="listing_email" class="form-control" placeholder="Email id">
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <input type="text" name="listing_website" class="form-control" placeholder="Website(www.website.com)">
                          </div>
                        </div>
                      </div>

                      <!--FILED END-->


                      <!--FILED START-->
                      <div class="row">
                        <div class="col-md-12">
                          <div class="form-group">
                            <input type="text" name="listing_address" required="required" class="form-control" placeholder="Business address">
                          </div>
                        </div>
                        <div class="col-md-12">
                          <div class="form-group row">
                            <label class="col-sm-4">Display Address?</label>
                            <div class="col-sm-4">
                              <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="display_address" value="1" id="display_phone">
                                <label class="form-check-label">Yes</label>
                              </div>
                              <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="display_address" value="0" id="display_phone">
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
                            <textarea class="form-control" id="listing_description" name="listing_description" placeholder="Details about your listing"></textarea>
                          </div>
                        </div>
                      </div>
                      <!--FILED END-->
                      <!--FILED START-->

                      <div class="row">
                        <div class="col-md-12">
                          <div class="form-group">
                            <textarea class="form-control" id="service_locations" name="service_locations" placeholder="Enter your service locations... &#10;(i.e) Pearland,Arlington, Austin"></textarea>
                          </div>
                        </div>
                        <div class="col-md-12">
                          <div class="form-group">
                            <textarea class="form-control" id="service_offered" name="service_offered" placeholder="Enter Offered Services ... &#10;(i.e) Car Wash, Car Service"></textarea>
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
                  <span class="steps">Step 2</span>
                  <div class="log add-list-map">
                    <div class="login add-list-map">
                      <h4 class="pt30">Photo gallery</h4>
                      <!--FILED START-->

                      <!--FILED START-->
                      <div class="row">
                        <div class="col-md-12">
                          <div class="form-group">
                            <label>Upload Listing Logo</label>
                            <input type="file" name="listing_profile_image" class="form-control">
                          </div>
                        </div>
                      </div>
                      <!--FILED END-->

                      <!--GALLERY IMAGES START-->
                      <div class="row">
                        <div class="col-md-12">
                          <div class="form-group">
                            <label>Upload Gallery Images (Multiple)</label>
                            <input type="file" name="listing_image_gallery[]" class="form-control" multiple accept="image/*">
                            <small class="form-text text-muted">You can select multiple images for gallery</small>
                          </div>
                        </div>
                      </div>
                      <!--GALLERY IMAGES END-->

                      <h4>Video Gallery</h4>
                      <ul>
                        <li>
                          <div class="row">
                            <div class="col-md-12">
                              <div class="form-group">
                                <textarea id="listing_video" name="listing_video" class="form-control" placeholder="Paste Your Youtube Url here"></textarea>
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
                        <input type="text" name="listing_seo_title" value="" placeholder="" class="form-control">
                      </div>
                      <div class="form-group">
                        <label>Page keywords</label>
                        <input type="text" name="listing_seo_keywords" value="" class="form-control" placeholder="i.e:wedding hall, best wedding hall">
                      </div>
                      <div class="form-group">
                        <label>Page descriptions</label>
                        <input type="text" name="listing_seo_description" value="" class="form-control">
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
              <button type="submit" name="listing_submit" class="multistep-btn-submit btn btn-primary" style="display: none;">Submit Listing</button>
            </div>

            <!-- Original submit section (hidden) -->
            <div class="row" style="display: none;">
              <div class="login-main add-list">
                <div class="log">
                  <div class="login add-lis-oth">


                    <!--FILED START-->

                    <div class="row">
                      <div class="col-md-12">
                        <button type="submit" name="listing_submit" class="btn btn-primary">Submit Listing</button>
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



<script src="../js/select-opt.js"></script>

<script>
  function getSubCategory(val) {

    $.ajax({

      type: "POST",

      url: '<?= base_url() ?>/admin/get_listing_sub_category',

      data: 'category_id=' + val,

      success: function(data) {

        $("#sub_category_id").html(data);

        $('#sub_category_id').trigger("chosen:updated");

      }

    });

  }
</script>


<script src="<?= base_url() ?>assets/ckeditor/ckeditor.js"></script>

<script>
  CKEDITOR.replace('listing_description');
</script>

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

    // Error display functions
    function showErrors(errors) {
      const errorContainer = document.getElementById('multistep-error-container');
      const errorList = document.getElementById('multistep-error-list');

      if (errors.length > 0) {
        errorList.innerHTML = '';
        errors.forEach(error => {
          const li = document.createElement('li');
          li.textContent = error;
          errorList.appendChild(li);
        });
        errorContainer.classList.add('show');

        // Scroll to top to show the error
        errorContainer.scrollIntoView({
          behavior: 'smooth',
          block: 'center'
        });
      } else {
        hideErrors();
      }
    }

    function hideErrors() {
      const errorContainer = document.getElementById('multistep-error-container');
      errorContainer.classList.remove('show');
    }

    // Validation functions for each step
    function validateStep1() {
      const requiredFields = [{
          id: 'user_id',
          name: 'User Name',
          type: 'select'
        },
        {
          id: 'listing_name',
          name: 'Listing Name',
          type: 'input'
        },
        {
          id: 'category_id',
          name: 'Category',
          type: 'select'
        },
        {
          id: 'listing_address',
          name: 'Business Address',
          type: 'input'
        }
      ];

      const missingFields = [];

      for (let field of requiredFields) {
        const element = document.querySelector(`[name="${field.id}"]`) || document.getElementById(field.id);
        if (!element || !element.value.trim()) {
          missingFields.push(field.name);
        }
      }

      // Check CKEditor content
      if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances.listing_description) {
        const ckeditorContent = CKEDITOR.instances.listing_description.getData().trim();
        if (!ckeditorContent || ckeditorContent === '') {
          missingFields.push('Listing Description');
        }
      }

      if (missingFields.length > 0) {
        const userFriendlyMessages = missingFields.map(field => {
          switch (field) {
            case 'User Name':
              return 'Please select a user from the dropdown';
            case 'Listing Name':
              return 'Please enter a name for your listing';
            case 'Category':
              return 'Please choose a category for your listing';
            case 'Business Address':
              return 'Please provide your business address';
            case 'Listing Description':
              return 'Please add details about your listing';
            default:
              return `Please fill in the ${field} field`;
          }
        });
        showErrors(userFriendlyMessages);
        return false;
      }
      hideErrors();
      return true;
    }

    function validateStep2() {
      // Check if at least listing logo is uploaded or video URL is provided
      const logoInput = document.querySelector('[name="listing_profile_image"]');
      const videoInput = document.querySelector('[name="listing_video"]');

      const hasLogo = logoInput && logoInput.files && logoInput.files.length > 0;
      const hasVideo = videoInput && videoInput.value.trim();

      if (!hasLogo && !hasVideo) {
        showErrors(['Please add a photo or video to make your listing more attractive to customers']);
        return false;
      }
      hideErrors();
      return true;
    }

    function validateStep3() {
      // Step 3 SEO settings are optional
      // No required fields, so always return true
      hideErrors();
      return true;
    }

    function validateCurrentStep() {
      switch (currentStep) {
        case 1:
          return validateStep1();
        case 2:
          return validateStep2();
        case 3:
          return validateStep3();
        default:
          return true;
      }
    }

    function updateStepDisplay() {
      stepIndicators.forEach((indicator, index) => {
        const stepNumber = index + 1;
        indicator.classList.remove('multistep-active', 'multistep-completed');
        if (stepNumber < currentStep) {
          indicator.classList.add('multistep-completed');
        } else if (stepNumber === currentStep) {
          indicator.classList.add('multistep-active');
        }
      });
      stepContents.forEach((content, index) => {
        content.classList.remove('multistep-active-content');
        if (index + 1 === currentStep) {
          content.classList.add('multistep-active-content');
        }
      });
      const progressWidth = ((currentStep - 1) / (totalSteps - 1)) * 100;
      progressLine.style.width = progressWidth + '%';
      prevBtn.disabled = currentStep === 1;
      if (currentStep === totalSteps) {
        navButtons.style.display = 'none';
        if (originalSubmitSection) originalSubmitSection.style.display = 'flex';
      } else {
        navButtons.style.display = 'flex';
        if (originalSubmitSection) originalSubmitSection.style.display = 'none';
      }
    }

    nextBtn.addEventListener('click', function() {
      // Validate current step before proceeding
      if (validateCurrentStep()) {
        if (currentStep < totalSteps) {
          currentStep++;
          updateStepDisplay();
        }
      }
    });

    prevBtn.addEventListener('click', function() {
      if (currentStep > 1) {
        currentStep--;
        updateStepDisplay();
      }
    });

    stepIndicators.forEach((indicator, index) => {
      indicator.addEventListener('click', function() {
        const targetStep = index + 1;

        // Only allow navigation to previous steps or next step if current step is valid
        if (targetStep < currentStep) {
          // Allow going back
          currentStep = targetStep;
          updateStepDisplay();
        } else if (targetStep === currentStep + 1) {
          // Allow going forward only if current step is valid
          if (validateCurrentStep()) {
            currentStep = targetStep;
            updateStepDisplay();
          }
        }
        // Don't allow skipping steps ahead
      });
    });

    updateStepDisplay();
  });
</script>