<!-- START -->
<style>
  .add_ads textarea {
    display: block !important;
  }
</style>
<section>

  <div class="ad-com">

    <div class="ad-dash leftpadd add_ads">

      <section class="login-reg">

        <div class="container">

          <div class="row">

            <div class="login-main add-list posr">

              <div class="log-bor">&nbsp;</div>

              <span class="udb-inst">Create ads</span>

              <div class="log log-1">

                <div class="login">

                  <h4>Create new Ads</h4>

                  <form id="create_ads_form" method="post" action="<?= base_url('admin/create-ads') ?>" enctype="multipart/form-data">

                    <input type="hidden" value="" name="ad_total_days" id="ad_total_days" class="validate">

                    <input type="hidden" value="" name="ad_cost_per_day" id="ad_cost_per_day" class="validate">

                    <input type="hidden" value="" name="ad_total_cost" id="ad_total_cost" class="validate">

                    <input type="hidden" value="" name="ad_width" id="ad_width" class="validate">

                    <input type="hidden" value="" name="ad_height" id="ad_height" class="validate">

                    <ul>

                      <li>

                        <!--FILED START-->

                        <div class="row">

                          <div class="col-md-12">

                            <div class="form-group">

                              <select name="user_id" required="required" class="form-control" id="user_id">

                                <option value="">Choose a user *</option>

                                <?php

                                if ($users && $users != null) {

                                  foreach ($users as $key => $value) {

                                ?>

                                    <option value="<?= $value->user_id; ?>"><?= e(user_option_label($value)) ?></option>



                                <?php

                                  }
                                }

                                ?>

                              </select>

                            </div>

                            <span class="text-danger"><?= form_error("user_id") ? form_error("user_id") : ''; ?></span>

                          </div>

                        </div>

                        <!--FILED END-->

                        <!--FILED START-->

                        <div class="row">

                          <div class="col-md-12">

                            <div class="form-group ca-sh-user">

                              <select name="ads_price_id" required="required" class="form-control" id="adposi">

                                <option value="">Choose Ads Position *</option>

                                <?php
                                /*
                                 * Split live inventory from dead. A zone with no
                                 * `slot_key` is one no page can ask for: an ad
                                 * booked against it is paid for, dated, approved
                                 * — and renders on zero pages, with nothing
                                 * anywhere to say why. That is bookable from this
                                 * dropdown, and was indistinguishable from a real
                                 * position. Same for a zone switched off.
                                 */
                                $ap_live = $ap_dead = array();
                                foreach (($adsPrices ?: array()) as $value) {
                                  if (trim((string) ($value->slot_key ?? '')) !== '' && (int) $value->status === 1) {
                                    $ap_live[] = $value;
                                  } else {
                                    $ap_dead[] = $value;
                                  }
                                }
                                ?>

                                <?php foreach ($ap_live as $value): ?>
                                  <option myTag="<?= e_attr($value->cost); ?>" value="<?= (int) $value->id; ?>">
                                    <?= e($value->name); ?> (<?= e($value->cost); ?> /per day) (<?= e($value->size); ?>)
                                  </option>
                                <?php endforeach; ?>

                                <?php if ($ap_dead): ?>
                                  <optgroup label="Not on any page — fix the zone first">
                                    <?php foreach ($ap_dead as $value): ?>
                                      <option value="<?= (int) $value->id; ?>" disabled>
                                        <?= e($value->name); ?>
                                        <?= trim((string) ($value->slot_key ?? '')) === '' ? '— no position code' : '— switched off' ?>
                                      </option>
                                    <?php endforeach; ?>
                                  </optgroup>
                                <?php endif; ?>

                              </select>

                              <a href="<?= base_url('admin/ads-price'); ?>" class="frmtip" target="_blank">Pricing details</a>

                            </div>

                            <span class="text-danger"><?= form_error("ads_price_id") ? form_error("ads_price_id") : ''; ?></span>

                          </div>

                          <?php
                          // Category targeting (migration 0024). Ads used to have one
                          // dimension — the slot — so a "category banner" was a single
                          // global inventory item that ran on every category page. An
                          // advertiser could not buy the Restaurants banner.
                          ?>
                          <div class="col-md-12">
                            <div class="form-group ca-sh-user">
                              <label for="ad_category_id">Show only in category</label>
                              <select name="category_id" class="form-control" id="ad_category_id">
                                <option value="">All categories &mdash; run this ad site-wide in its position</option>
                                <?php if (!empty($adCategories)) {
                                  foreach ($adCategories as $__cat) { ?>
                                    <option value="<?= (int) $__cat->category_id ?>"<?= set_select('category_id', (string) $__cat->category_id) ?>><?= html_escape($__cat->category_name) ?></option>
                                <?php }
                                } ?>
                              </select>
                              <small class="form-text text-muted">
                                Pick a category to sell that category&rsquo;s banner. A category booking
                                takes the position ahead of a site-wide one; leave it on
                                &ldquo;All categories&rdquo; to behave exactly as before.
                              </small>
                            </div>
                            <span class="text-danger"><?= form_error("category_id") ? form_error("category_id") : ''; ?></span>
                          </div>

                        </div>

                        <!--FILED END-->

                        <!--FILED START-->

                        <div class="row">

                          <div class="col-md-12">

                            <div class="form-group">

                              <input type="text" id="stdate" autocomplete="off" name="ad_start_date" value="<?= set_value('ads_start_date'); ?>" class="form-control" placeholder="Ad start date (MM/DD/YYYY)" required>

                            </div>

                            <span class="text-danger"><?= form_error("ad_start_date") ? form_error("ad_start_date") : ''; ?></span>

                          </div>

                        </div>

                        <!--FILED END-->

                        <!--FILED START-->

                        <div class="row">

                          <div class="col-md-12">

                            <div class="form-group">

                              <input type="text" id="endate" autocomplete="off" name="ad_end_date" value="<?= set_value('ad_end_date'); ?>" class="form-control" placeholder="Ad end date (MM/DD/YYYY)" required>

                            </div>

                            <span class="text-danger"><?= form_error("ad_end_date") ? form_error("ad_end_date") : ''; ?></span>

                          </div>

                        </div>

                        <!--FILED END-->

                        <!--FILED START-->

                        <div class="row">

                          <div class="col-md-12">

                            <div class="form-group">

                              <label>Choose Ad image</label>

                              <input type="file" name="ad_photo" class="form-control" required>

                            </div>

                            <span class="text-danger"><?= form_error("ad_photo") ? form_error("ad_photo") : ''; ?></span>

                          </div>

                        </div>

                        <!--FILED END-->

                        <!--FILED START-->

                        <div class="row">

                          <div class="col-md-12">

                            <div class="form-group">

                              <label for="ad_link">Advertisement External link</label>

                              <textarea style="display: block !important;" id="ad_link" name="ad_link" class="form-control" placeholder="Advertisement External link" required></textarea>

                            </div>

                            <span class="text-danger"><?= form_error("ad_link") ? form_error("ad_link") : ''; ?></span>

                          </div>

                        </div>

                        <!--FILED END-->

                        <!--FILED START-->

                        <div class="row">

                          <div class="col-md-12">

                            <div class="ad-pri-cal">

                              <ul>

                                <li>

                                  <div>

                                    <span>Total days</span>

                                    <h5 class="ad-tdays">0</h5>

                                  </div>

                                </li>

                                <li>

                                  <div>

                                    <span>Cost Per Day</span>

                                    <h5>$<b class="ad-pocost">0</b></h5>

                                  </div>

                                </li>

                                <li>

                                  <!-- <div>

                              <span>Tax</span>

                              <h5>$0</h5>

                            </div> -->

                                </li>

                                <li>

                                  <div>

                                    <span>Total Cost</span>

                                    <h5>$<b class="ad-tcost">0</b></h5>

                                  </div>

                                </li>

                              </ul>

                            </div>

                          </div>

                        </div>

                        <!--FILED END-->

                      </li>

                    </ul>

                    <!--FILED START-->

                    <div class="row">

                      <div class="col-md-12">

                        <button type="submit" name="create_ad_submit" class="btn btn-primary">Publish this Ad</button>

                      </div>

                      <div class="col-md-12">

                        <a href="<?= base_url('admin/dashboard'); ?>" class="skip">Go to User Dashboard >></a>

                      </div>

                    </div>

                    <!--FILED END-->

                  </form>

                  <div class="ud-notes">

                    <p><b>Notes:</b> Hi, Before submit your <b>Ads</b> please check the <b>available date</b> because previous Ads running in same date. Kindly check this manually</p>

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