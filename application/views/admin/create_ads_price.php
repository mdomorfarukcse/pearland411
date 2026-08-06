
<!-- START -->
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <section class="login-reg">
        <div class="container">
          <div class="row">
            <div class="login-main add-list posr">
              <div class="log-bor">&nbsp;</div>
              <span class="udb-inst">Ad details</span>
              <div class="log log-1">
                <div class="login">
                  <h4>Ad Pricing and other details</h4>                                                                        
                  <form name="ads_price_form" id="ads_price_form" method="post" action="<?= base_url('admin/create-ads-price');?>" enctype="multipart/form-data">
                    <ul>
                      <li>
                        <!--FILED START-->
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <input type="text" class="form-control" name="name" value="<?= set_value('name');?>" placeholder="Ad position name" required>
                            </div>
                            <span class="text-danger"><?= form_error("name")?form_error("name"):'' ;?></span>
                          </div>
                        </div>
                        <!--FILED END-->
                        <!--FILED START-->
                        <?php // The position code. A view asks for a zone by this
                              // string, and a package fills it when plans.slug
                              // matches — so a row without one is a price with no
                              // position, which is what every hand-made row was. ?>
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <label>Slot key <span style="font-weight:400;color:#5a6472;">(position code — leave blank to derive it from the name)</span></label>
                              <input type="text" class="form-control" name="slot_key" value="<?= set_value('slot_key');?>"
                                     placeholder="e.g. right-side-banner-1" pattern="[a-zA-Z0-9\-]+"
                                     maxlength="60">
                            </div>
                            <span class="text-danger"><?= form_error("slot_key")?form_error("slot_key"):'' ;?></span>
                          </div>
                        </div>
                        <!--FILED END-->
                        <!--FILED START-->
                        <?php // Stored as ads_prices.size ("728x90"). Every page
                              // reserves exactly this box before the creative
                              // loads, so it is what an advertiser is quoted. ?>
                        <div class="row">
                          <div class="col-md-6">
                            <div class="form-group">
                              <label>Ad width (px)</label>
                              <input type="number" class="form-control" name="width" value="<?= set_value('width');?>" placeholder="e.g. 728" min="10" max="9999" required>
                            </div>
                            <span class="text-danger"><?= form_error("width")?form_error("width"):'' ;?></span>
                          </div>
                          <div class="col-md-6">
                            <div class="form-group">
                              <label>Ad height (px)</label>
                              <input type="number" class="form-control" name="height" value="<?= set_value('height');?>" placeholder="e.g. 90" min="10" max="9999" required>
                            </div>
                            <span class="text-danger"><?= form_error("height")?form_error("height"):'' ;?></span>
                          </div>
                        </div>
                        <!--FILED END-->
                        <!--FILED START-->
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <!-- onkeypress="return isNumber(event)" -->
                              <input type="text" class="form-control" name="cost" value="<?= set_value('cost'); ?>" placeholder="Ad cost" required>
                            </div>
                            <span class="text-danger"><?= form_error("cost")?form_error("cost"):'' ;?></span>
                          </div>
                        </div>
                        <!--FILED END-->
                        <!--FILED START-->
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <label>Choose Ad preview image <span style="font-weight:400;color:#5a6472;">(optional)</span></label>
                              <input type="file" name="img" class="form-control" accept="image/*">
                              <p style="margin:6px 0 0;font-size:12.5px;color:#5a6472;">
                                Leave this empty and the zone gets a generated preview —
                                a page wireframe with the position highlighted and its pixel
                                size labelled. This image is only the thumbnail shown here;
                                it is never served to visitors.
                              </p>
                            </div>
                          </div>
                        </div>
                        <!--FILED END-->
                        <!--FILED START-->
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <select name="status" class="form-control">
                                <option  value="1" >Active</option>
                                <option  value="0" >InActive</option>
                              </select>
                            </div>
                            <span class="text-danger"><?= form_error("status")?form_error("status"):'' ;?></span>
                          </div>
                        </div>
                        <!--FILED END-->
                      </li>
                    </ul>
                    <!--FILED START-->
                    <div class="row">
                      <div class="col-md-12">
                        <button type="submit" name="ad_price_submit" class="btn btn-primary">Submit</button>
                      </div>
                      <div class="col-md-12">
                        <a href="<?= base_url('admin/ads-price');?>" class="skip">Go to Current Ads >></a>
                      </div>
                    </div>
                    <!--FILED END-->
                  </form>
                  <!--                                    <div class="ud-notes">-->
                    <!--                                        <p><b>Notes:</b> Hi, Before submit your <b>Ads</b> please check the <b>available-->
                      <!--                                                date</b> because previous Ads running in same date. Kindly check this-->
                      <!--                                            manually</p>-->
                      <!--                                    </div>-->
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
