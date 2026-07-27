
<!-- START -->
<style>
  .req-star { color: #d00; font-weight: 700; margin-left: 2px; }
  .field-label { display: block; font-weight: 600; margin-bottom: 6px; color: #333; }
  .field-hint { display: block; font-weight: 400; font-size: 12px; color: #888; margin-top: 2px; }
  .formError { color: #d00; font-size: 13px; margin-top: 4px; }
  .form-alert-error {
    background: #fde2e7; color: #591A2D; border: 1px solid #f1b3bf;
    padding: 12px 16px; border-radius: 10px; margin-bottom: 16px;
  }
  .form-alert-error ul { margin: 6px 0 0 18px; }
</style>
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
     <section class="login-reg">
      <div class="container">
       <div class="row">
        <div class="login-main add-list posr">
         <div class="log-bor">&nbsp;</div>
         <span class="udb-inst">new event</span>
         <div class="log log-1">
          <div class="login">
            <h4>Create Event</h4>

            <?php if ($this->session->flashdata('message')) { ?>
              <div class="form-alert-error"><?= e($this->session->flashdata('message')) ?></div>
            <?php } ?>
            <?php if (validation_errors()) { ?>
              <div class="form-alert-error">
                <strong>Please fix the following before saving:</strong>
                <?= validation_errors('<ul><li>', '</li></ul>') ?>
              </div>
            <?php } ?>

            <p style="color:#888;font-size:13px;margin-bottom:14px;">
              Fields marked with <span class="req-star">*</span> are required.
            </p>

            <form  name="event_form" id="event_form" method="post" action="<?= base_url('admin/add-new-event') ?>" enctype="multipart/form-data">
             <ul>
              <li>
                <!--FILED START-->
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="field-label" for="admin_id">Host <span class="req-star">*</span>
                        <span class="field-hint">Choose the admin hosting this event.</span>
                      </label>
                      <select name="admin_id" required="required" class="form-control" id="admin_id">
                        <option value="">Choose a host</option>
                        <?php
                        if(!empty($admins)){
                          foreach($admins as $admin){
                        ?>
                        <option value="<?= $admin->admin_id; ?>" <?= set_select('admin_id', $admin->admin_id); ?>><?= e($admin->admin_name); ?></option>

                        <?php } } ?>

                      </select>
                      <div class="formError"><?= form_error('admin_id') ?></div>
                    </div>
                  </div>
                </div>
                <!--FILED END-->
                <!--FILED START-->
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="field-label" for="event_name">Event name <span class="req-star">*</span></label>
                      <input type="text" id="event_name" name="event_name" required="required" class="form-control" placeholder="Event name" value="<?= set_value('event_name') ?>">
                      <div class="formError"><?= form_error('event_name') ?></div>
                    </div>
                  </div>
                </div>
                <!--FILED END-->
                <!--FILED START-->
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="field-label" for="event_address">Address <span class="req-star">*</span></label>
                      <input type="text" id="event_address" name="event_address" required="required" class="form-control" placeholder="Address" value="<?= set_value('event_address') ?>">
                      <div class="formError"><?= form_error('event_address') ?></div>
                    </div>
                  </div>
                </div>
                <!--FILED END-->
                <!--FILED START-->
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="field-label" for="event_start_date">Event date <span class="req-star">*</span></label>
                      <input type="date" id="event_start_date" name="event_start_date" required="required" class="form-control" value="<?= set_value('event_start_date') ?>">
                      <div class="formError"><?= form_error('event_start_date') ?></div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="field-label" for="event_time">Time <span class="req-star">*</span></label>
                      <input type="text" id="event_time" name="event_time" required="required" class="form-control" placeholder="e.g. 6:00 PM" value="<?= set_value('event_time') ?>">
                      <div class="formError"><?= form_error('event_time') ?></div>
                    </div>
                  </div>
                </div>
                <!--FILED END-->
                <!--FILED START-->
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="field-label" for="event_description">Event details <span class="req-star">*</span>
                        <span class="field-hint">Describe the event — what it is about, schedule, who should attend, etc.</span>
                      </label>
                      <textarea class="form-control" id="event_description" name="event_description" placeholder="Event details"><?= set_value('event_description') ?></textarea>
                      <div class="formError"><?= form_error('event_description') ?></div>
                    </div>
                  </div>
                </div>
                <!--FILED END-->
                <!--FILED START-->
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="field-label" for="event_map">Google map location
                        <span class="field-hint">Optional. Paste a Google Maps embed &lt;iframe&gt; or share link.</span>
                      </label>
                      <textarea class="form-control" id="event_map" name="event_map" placeholder="Google map location"><?= set_value('event_map') ?></textarea>
                      <div class="formError"><?= form_error('event_map') ?></div>
                    </div>
                  </div>
                </div>
                <!--FILED END-->
                <!--FILED START-->
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="field-label" for="event_image">Banner image <span class="req-star">*</span></label>
                      <input type="file" id="event_image" name="event_image" required="required" class="form-control" accept="image/*">
                      <div class="formError"><?= form_error('event_image') ?></div>
                    </div>
                  </div>
                </div>
                <!--FILED END-->
                <!--FILED START-->
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="field-label" for="event_contact_name">Contact person <span class="req-star">*</span></label>
                      <input type="text" id="event_contact_name" name="event_contact_name" required="required" class="form-control" placeholder="Contact person" value="<?= set_value('event_contact_name') ?>">
                      <div class="formError"><?= form_error('event_contact_name') ?></div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="field-label" for="event_mobile">Contact phone number <span class="req-star">*</span></label>
                      <input type="text" id="event_mobile" name="event_mobile" required="required" class="form-control" placeholder="Contact phone number" value="<?= set_value('event_mobile') ?>">
                      <div class="formError"><?= form_error('event_mobile') ?></div>
                    </div>
                  </div>
                </div>
                <!--FILED END-->
                <!--FILED START-->
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="field-label" for="event_email">Contact email <span class="req-star">*</span></label>
                      <input  type="email" id="event_email" name="event_email" required="required" class="form-control"
                      placeholder="Contact Email Id" value="<?= set_value('event_email') ?>">
                      <div class="formError"><?= form_error('event_email') ?></div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="field-label" for="event_website">Event website</label>
                      <input type="text" id="event_website" name="event_website" class="form-control"
                      placeholder="Event Website" value="<?= set_value('event_website') ?>">
                      <div class="formError"><?= form_error('event_website') ?></div>
                    </div>
                  </div>
                </div>
                <!--FILED END-->
                <!--FILED START-->
                <div class="row">
                  <div class="col-md-12">
                    <div>
                      <div class="chbox">
                        <input type="checkbox" id="isenquiry" name="isenquiry" value="1" <?= set_checkbox('isenquiry', '1'); ?>>
                        <label for="isenquiry">Enquiry or Registration form enable</label>
                      </div>
                    </div>
                  </div>
                </div>
                <!--FILED END-->
              </li>
            </ul>
            <!--FILED START-->
            <div class="row">
              <div class="col-md-12">
                <button type="submit" name="event_submit" class="btn btn-primary">Submit</button>
              </div>
            </div>
            <!--FILED END-->
          </form>
          <div class="col-md-12">
            <a href="<?= base_url('admin/dashboard');?>" class="skip">Go to user dashboard >></a>
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


<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>

<script src="<?= base_url() ?>assets/ckeditor/ckeditor.js"></script>
<script>
  CKEDITOR.replace('event_description');

  // CKEditor hides the real <textarea>, so a native `required` attribute on it
  // makes the browser silently block submit ("invalid control not focusable").
  // We drop that attribute above and instead push the editor's HTML back into
  // the textarea right before the form submits, then let server-side
  // validation enforce that it is not empty.
  (function () {
    var form = document.getElementById('event_form');
    if (form) {
      form.addEventListener('submit', function () {
        if (window.CKEDITOR && CKEDITOR.instances.event_description) {
          CKEDITOR.instances.event_description.updateElement();
        }
      });
    }
  })();
</script>
