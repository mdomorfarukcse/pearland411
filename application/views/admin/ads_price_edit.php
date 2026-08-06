
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
                  <?php
                    // `ads_prices` stores one `size` column ("728x90"), not two
                    // numeric ones. The form asks for width and height, and used
                    // to bind them to $price->width / $price->height — columns
                    // that do not exist — so both boxes opened empty on every
                    // edit and, being `required`, made the form unsubmittable
                    // without retyping a size the operator could not see.
                    $ap_w = $ap_h = '';
                    if (preg_match('/^\s*(\d{2,4})\s*[xX\*]\s*(\d{2,4})\s*$/', (string) ($price->size ?? ''), $ap_m)) {
                        $ap_w = (int) $ap_m[1];
                        $ap_h = (int) $ap_m[2];
                    }
                  ?>
                  <form name="ads_price_form" id="ads_price_form" method="post" action="<?= base_url('admin/ads-price-edit/'.$price->id);?>" enctype="multipart/form-data">
                    <ul>
                      <li>
                        <!--FILED START-->
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <input type="text" class="form-control" name="name" value="<?= $price->name;?>" placeholder="Ad position name" required>
                            </div>
                            <span class="text-danger"><?= form_error("name")?form_error("name"):'' ;?></span>
                          </div>
                        </div>
                        <!--FILED END-->
                        <!--FILED START-->
                        <?php // The position code the views ask for. Editable only
                              // while it is blank: re-keying a live zone would
                              // unhook it from whatever page renders it, with
                              // nothing on screen to say so. ?>
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <label>Slot key</label>
                              <?php if (trim((string) ($price->slot_key ?? '')) !== ''): ?>
                                <input type="text" class="form-control" value="<?= e_attr($price->slot_key ?? '') ?>" readonly>
                                <p style="margin:6px 0 0;font-size:12.5px;color:#5a6472;">
                                  Fixed — the pages request this zone by this name, and a
                                  package fills it when its slug matches.
                                </p>
                              <?php else: ?>
                                <input type="text" class="form-control" name="slot_key" value="<?= set_value('slot_key');?>"
                                       placeholder="e.g. right-side-banner-1" pattern="[a-zA-Z0-9\-]+" maxlength="60">
                                <p style="margin:6px 0 0;font-size:12.5px;color:#5a6472;">
                                  This zone has no position code, so no page can render it.
                                  Give it one — blank derives it from the name.
                                </p>
                              <?php endif; ?>
                            </div>
                            <span class="text-danger"><?= form_error("slot_key")?form_error("slot_key"):'' ;?></span>
                          </div>
                        </div>
                        <!--FILED END-->
                        <!--FILED START-->
                        <div class="row">
                          <div class="col-md-6">
                            <div class="form-group">
                              <label>Ad width (px)</label>
                              <input type="number" class="form-control" name="width" value="<?= set_value('width', $ap_w);?>" placeholder="e.g. 728" min="10" max="9999" required>
                            </div>
                            <span class="text-danger"><?= form_error("width")?form_error("width"):'' ;?></span>
                          </div>
                          <div class="col-md-6">
                            <div class="form-group">
                              <label>Ad height (px)</label>
                              <input type="number" class="form-control" name="height" value="<?= set_value('height', $ap_h);?>" placeholder="e.g. 90" min="10" max="9999" required>
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
                              <input type="text" class="form-control" name="cost" value="<?= $price->cost; ?>" placeholder="Ad cost" required>
                            </div>
                            <span class="text-danger"><?= form_error("cost")?form_error("cost"):'' ;?></span>
                          </div>
                        </div>
                        <!--FILED END-->
                        <!--FILED START-->
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <label>Ad preview image</label>
                              <?php // Show what is stored. Without this the only way
                                    // to know whether a zone had artwork was to go
                                    // back to the list and look. ?>
                              <div style="margin:0 0 10px;">
                                <img src="<?= base_url(ad_zone_preview($price)) ?>"
                                     alt="<?= e_attr($price->name . ' ad zone preview') ?>"
                                     style="display:block;width:260px;max-width:100%;height:auto;border:1px solid #e3e7ec;border-radius:6px;background:#fff;">
                                <span style="display:block;margin-top:6px;font-size:12.5px;color:#5a6472;">
                                  <?= trim((string) ($price->img ?? '')) !== '' ? 'Uploaded artwork' : 'Generated preview — upload one to replace it' ?>
                                </span>
                              </div>
                              <input type="file" name="img" class="form-control" accept="image/*">
                            </div>
                          </div>
                        </div>
                        <!--FILED END-->
                        <!--FILED START-->
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <select name="status" class="form-control">
                                <option  value="1" <?= ($price->status == 1) ? 'selected':''; ?>>Active</option>
                                <option  value="0"<?= ($price->status != 1) ? 'selected':''; ?>>InActive</option>
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
                        <button type="submit" name="ad_price_submit" class="btn btn-primary">update</button>
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
