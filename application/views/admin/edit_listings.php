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
          <?php // A failed save redirects back here with a reason; nothing printed it. ?>
          <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger" style="margin:0 0 16px;"><?= e($this->session->flashdata('error')) ?></div>
          <?php endif; ?>
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
                                  <option value="<?= $value->user_id; ?>" <?= (isset($listing) && $listing->user_id == $value->user_id) ? 'SELECTED' : '' ?>><?= e(user_option_label($value)) ?></option>
                              <?php
                                }
                              }
                              ?>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <input id="listing_name" name="listing_name" type="text" required="required" class="form-control" placeholder="Listing name *" value="<?= e_attr(isset($listing) ? $listing->listing_name : '') ?>">
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
                            <?php if (!empty($listing->category_id)) { ?>
                              <small class="form-text text-muted">
                                The header banner on this category&rsquo;s page is set on the category:
                                <a href="<?= base_url('admin/edit_listing_category/' . (int) $listing->category_id) ?>" target="_blank" rel="noopener">edit category banner</a>.
                              </small>
                            <?php } ?>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <?php
                            // Multiple. A listing could only ever hold one sub-category —
                            // the column is a single INT and this was a single-value
                            // select — so "I have included all the available
                            // subcategories" could not be stored. The set now lives in
                            // listing_sub_category_map (migration 0023).
                            $selected_subs = isset($listing_sub_categories) && is_array($listing_sub_categories)
                                ? array_map('intval', $listing_sub_categories)
                                : array();
                            ?>
                            <label for="sub_category_id">Sub categories <small class="text-muted">(hold Ctrl / Cmd to pick more than one)</small></label>
                            <select data-placeholder="Select Sub Category" name="sub_category_id[]" id="sub_category_id" class="chosen-select form-control" multiple size="6">
                              <?php if (isset($sub_category_listing) && $sub_category_listing) {
                                foreach ($sub_category_listing as $subcat) {
                                  $sid = (int) $subcat->sub_category_id; ?>
                                  <option value="<?= $sid ?>"<?= in_array($sid, $selected_subs, TRUE) ? ' selected' : '' ?>><?= html_escape($subcat->sub_category_name) ?></option>
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
                            <input type="text" name="listing_mobile" class="form-control" placeholder="Phone number" value="<?= e_attr(isset($listing) ? $listing->listing_mobile : '') ?>">
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <input type="text" name="listing_whatsapp" class="form-control" placeholder="Whatsapp Number" value="<?= e_attr(isset($listing) ? $listing->listing_whatsapp : '') ?>">
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
                            <input type="email" name="listing_email" class="form-control" placeholder="Email id" value="<?= e_attr(isset($listing) ? $listing->listing_email : '') ?>">
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <input type="text" name="listing_website" class="form-control" placeholder="Website(www.website.com)" value="<?= e_attr(isset($listing) ? $listing->listing_website : '') ?>">
                          </div>
                        </div>
                      </div>
                      <!--FILED END-->
                      <!--FILED START-->
                      <div class="row">
                        <div class="col-md-12">
                          <div class="form-group">
                            <input type="text" name="listing_address" required="required" class="form-control" placeholder="Business address" value="<?= e_attr(isset($listing) ? $listing->listing_address : '') ?>">
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
                          <?php
                          // The logo. This block used to read
                          // $listing->listing_profile_image — the name of the file
                          // INPUT, not a column; the column is `listing_image` — and
                          // then prepended assets/images/listings/ to a path that
                          // already contained it. So the current logo never appeared.
                          $mf_name    = 'listing_profile_image';
                          $mf_label   = 'Upload Listing Logo';
                          $mf_hint    = 'Shown at the top of the listing page. Leave empty to keep the current one. ' . image_guidance('logo');
                          $mf_current = $listing->listing_image ?? '';
                          $mf_empty   = 'No logo uploaded yet.';
                          include APPPATH . 'views/admin/partials/media_field.php';
                          ?>
                        </div>
                        <div class="col-md-12">
                          <?php
                          // The header banner: the wide picture behind the business
                          // name at the top of its page (listings.cover_image). Neither
                          // admin form could set it, so every listing the team entered
                          // showed the stock background.
                          $mf_name    = 'cover_image';
                          $mf_label   = 'Header banner (top of the listing page)';
                          $mf_hint    = 'Optional. Leave empty to keep the current one. ' . image_guidance('cover');
                          $mf_current = $listing->cover_image ?? '';
                          $mf_remove  = 'remove_cover_image';
                          $mf_empty   = 'No header banner &mdash; the page uses the standard background.';
                          include APPPATH . 'views/admin/partials/media_field.php';
                          ?>
                        </div>
                        <div class="col-md-12">
                          <?php
                          $mf_name     = 'listing_image_gallery[]';
                          $mf_id       = 'listing_image_gallery';
                          $mf_label    = 'Upload Gallery Images (Multiple Images)';
                          $mf_hint     = 'You can select several at once. New images are <strong>added</strong> to the ones below &mdash; nothing is replaced. ' . image_guidance('gallery');
                          $mf_current  = listing_gallery_paths($listing->listing_image_gallery ?? null);
                          $mf_multiple = true;
                          $mf_remove   = 'remove_gallery[]';
                          $mf_empty    = 'No gallery images uploaded yet.';
                          include APPPATH . 'views/admin/partials/media_field.php';
                          ?>
                        </div>
                      </div>
                      <!--FILED END-->

                      <h4>Video Gallery</h4>
                      <ul>
                        <li>
                          <div class="row">
                            <div class="col-md-12">
                              <div class="form-group">
                                <textarea id="listing_video" name="listing_video" class="form-control" rows="3"
                                          data-video-preview="#listing_video_preview"
                                          placeholder="Paste YouTube links here — one per line, or separated by commas"><?= e($listing->listing_video ?? '') ?></textarea>
                                <small class="form-text text-muted">Each link is checked as you type; anything that is not a YouTube URL is called out rather than silently ignored.</small>
                                <?php // Rendered by media-preview.js from whatever is in the box. ?>
                                <div class="p411-media p411-media__videos" id="listing_video_preview"></div>
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

          <?php
          /* Category-specific banners sold to THIS business (Admin::listing_banner_add).
             Its own form — forms cannot nest, and booking a banner must not
             depend on the listing form validating. */
          $lb_zones    = isset($banner_zones) ? $banner_zones : array();
          $lb_booked   = isset($listing_banners) ? $listing_banners : array();
          $lb_can_book = !empty($can_book_banners);
          ?>
          <div id="banners" class="card" style="margin:28px 0 10px;padding:22px;border:1px solid #e4e6ec;border-radius:12px;background:#fff;">
            <h4 style="margin:0 0 6px;">Category banners for this business</h4>
            <?php if ($this->session->flashdata('banner_message')): ?>
              <div class="alert alert-success" style="margin:8px 0 12px;"><?= e($this->session->flashdata('banner_message')) ?></div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('banner_error')): ?>
              <div class="alert alert-danger" style="margin:8px 0 12px;"><?= e($this->session->flashdata('banner_error')) ?></div>
            <?php endif; ?>
            <p class="text-muted" style="margin:0 0 16px;">
              The <a href="<?= base_url('pricing-plan') ?>#pp-category-banners" target="_blank">Category Specific Banners</a>
              this business has bought &mdash; header, middle, bottom and right-hand banners that run only on one
              category&rsquo;s directory pages. The <em>header banner</em> in the form above is different: it is the
              picture at the top of this business&rsquo;s own page.
            </p>

            <?php if (!empty($lb_booked)): ?>
              <table class="table table-sm" style="margin-bottom:18px;">
                <thead><tr><th>Banner</th><th>Position</th><th>Category</th><th>Runs</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($lb_booked as $lb): ?>
                  <?php $lb_live = (int) $lb->ad_status === 1 && strtotime($lb->ad_start_date) <= time() && strtotime($lb->ad_end_date) >= time(); ?>
                  <tr>
                    <td><?php if (!empty($lb->ad_photo)): ?><img src="<?= e_url(base_url($lb->ad_photo)) ?>" alt="" style="max-width:160px;max-height:60px;border-radius:4px;" data-lightbox><?php endif; ?></td>
                    <td><?= e($lb->zone_name ?? '') ?><?= !empty($lb->size) ? ' <small class="text-muted">(' . e($lb->size) . ')</small>' : '' ?></td>
                    <td><?= e($lb->category_name ?? '') ?></td>
                    <td><?= e(us_date($lb->ad_start_date)) ?> &ndash; <?= e(us_date($lb->ad_end_date)) ?></td>
                    <td><?= $lb_live ? '<span style="color:#2e7d32;font-weight:600;">Live</span>' : (strtotime($lb->ad_end_date) < time() ? 'Ended' : 'Scheduled') ?></td>
                    <td>
                      <?php if ($lb_can_book): ?>
                        <form method="post" action="<?= base_url('admin/listing_banner_remove/' . (int) $lb->id) ?>" onsubmit="return confirm('Remove this banner? It stops showing immediately.');" style="display:inline;">
                          <input type="hidden" name="<?= e_attr($this->security->get_csrf_token_name()) ?>" value="<?= e_attr($this->security->get_csrf_hash()) ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                        </form>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            <?php else: ?>
              <p style="margin:0 0 16px;">No category banners booked for this business yet.</p>
            <?php endif; ?>

            <?php if (!$lb_can_book): ?>
              <p class="text-muted" style="margin:0;">Booking a paid banner is a super-admin task (ad sales). Ask a super-admin to add it here.</p>
            <?php elseif (empty($banner_tracking)): ?>
              <p class="alert alert-warning" style="margin:0;">Run <code>database/migrations/0036_tracker_round3.sql</code> to book banners from this screen.</p>
            <?php elseif (empty($lb_zones)): ?>
              <p class="alert alert-warning" style="margin:0;">No category banner positions are set up. Check the Category Specific Banners packages at <a href="<?= base_url('admin/pricing-plan') ?>">Pricing Plans</a>.</p>
            <?php else: ?>
              <form method="post" enctype="multipart/form-data" action="<?= base_url('admin/listing_banner_add/' . (int) $listing->listing_id) ?>">
                <input type="hidden" name="<?= e_attr($this->security->get_csrf_token_name()) ?>" value="<?= e_attr($this->security->get_csrf_hash()) ?>">
                <div class="row">
                  <div class="col-md-6 form-group">
                    <label for="lb_zone">Banner position</label>
                    <select id="lb_zone" name="ads_price_id" class="form-control no-select2" required>
                      <option value="">Choose…</option>
                      <?php foreach ($lb_zones as $z): ?>
                        <option value="<?= (int) $z->id ?>"><?= e($z->plan_name ?: $z->name) ?><?= $z->size ? ' — ' . e($z->size) . ' px' : '' ?> — $<?= e(number_format((float) $z->price, 2)) ?> / <?= (int) ($z->duration_days ?: 30) ?> days</option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-6 form-group">
                    <label for="lb_cat">Runs in category</label>
                    <select id="lb_cat" name="category_id" class="form-control no-select2" required>
                      <?php foreach ((array) $categories as $c): ?>
                        <option value="<?= (int) $c->category_id ?>" <?= (int) $c->category_id === (int) $listing->category_id ? 'selected' : '' ?>><?= e($c->category_name) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-6 form-group">
                    <label for="lb_photo">Banner artwork</label>
                    <input type="file" id="lb_photo" name="ad_photo" class="form-control" accept="image/*" required>
                    <small class="form-text text-muted">Match the position&rsquo;s size shown in the list. JPG, PNG, GIF or WebP.</small>
                  </div>
                  <div class="col-md-6 form-group">
                    <label for="lb_link">Clicks go to</label>
                    <input type="text" id="lb_link" name="ad_link" class="form-control" placeholder="<?= e_attr(base_url('listing-details/' . $listing->listing_slug)) ?>">
                    <small class="form-text text-muted">Leave empty to link to this business&rsquo;s page.</small>
                  </div>
                  <div class="col-md-3 form-group">
                    <label for="lb_start">Start</label>
                    <input type="date" id="lb_start" name="ad_start_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                  </div>
                  <div class="col-md-3 form-group">
                    <label for="lb_end">End</label>
                    <input type="date" id="lb_end" name="ad_end_date" class="form-control" value="<?= date('Y-m-d', strtotime('+29 days')) ?>" required>
                  </div>
                  <div class="col-md-6 form-group" style="display:flex;align-items:flex-end;">
                    <button type="submit" class="btn btn-primary">Book category banner</button>
                  </div>
                </div>
              </form>
            <?php endif; ?>
          </div>
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
    // Send what is currently ticked so the server can mark it selected again.
    // Swapping category replaces this select's innerHTML wholesale, and the
    // partial had no notion of a current value — so touching the category
    // dropdown silently cleared the sub-categories already saved.
    var keep = $('#sub_category_id').val() || [];
    $.ajax({
      type: 'POST',
      url:  '<?= base_url() ?>admin/get_listing_sub_category',
      data: { category_id: val, selected: keep },
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