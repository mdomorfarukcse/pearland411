
<!-- START -->
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <section class="login-reg">
        <div class="container">
          <div class="row">
            <div class="login-main add-list posr">
              <div class="log-bor">&nbsp;</div>
              <span class="udb-inst">Edit event</span>
              <div class="log log-1">
                <div class="login">
                  <h4>Edit this Event</h4>
                  <form action="" class="event_edit_form" id="event_edit_form" name="event_edit_form" method="post" enctype="multipart/form-data">
                  <input type="hidden" id="event_id" value="48"
                  name="event_id" class="validate">
                  <input type="hidden" id="event_image_old"
                  value="88783banner16130393609bct2.jpg" name="event_image_old"
                  class="validate">
                  <ul>
                    
                  <li>
                    <!--FILED START-->
                    <div class="row">
                      <div class="col-md-12">
                        <div class="form-group">
                          <select name="user_id" required="required" class="form-control" id="user_id">
                            <option value="Admin" <?= $event->user_id == 'Admin' ? 'SELECTED' : '' ?>>Admin</option>
                            <?php 
                            if($allUsers){
                              foreach($allUsers as $user){
                            ?>
                            <option value="<?= $user->user_id; ?>" <?= $user->user_id == $event->user_id ? 'SELECTED' : '' ?>><?= $user->name; ?></option>

                            <?php } } ?>
                          </select>
                        </div>
                      </div>
                    </div>
                    <!--FILED END-->
                    <!--FILED START-->

                    <div class="row">

                      <div class="col-md-12">

                        <div class="form-group">

                          <input type="text" name="event_name" required="required" class="form-control" value="<?= $event->event_name; ?>" placeholder="Event Name*">

                        </div>

                        <span class="text-danger"><?= form_error("event_name")?form_error("event_name"):'' ;?></span>

                      </div>

                    </div>

                    <!--FILED END-->

                    <!--FILED START-->

                    <div class="row">

                      <div class="col-md-12">

                        <div class="form-group">

                          <input type="text" name="event_address" required="required" class="form-control" value="<?= $event->event_address; ?>" placeholder="Address*">

                        </div>

                        <span class="text-danger"><?= form_error("event_address")?form_error("event_address"):'' ;?></span>

                      </div>

                    </div>

                    <!--FILED END-->

                    <!--FILED START-->

                    <div class="row">

                      <div class="col-md-6">

                        <div class="form-group">

                          <input type="date" id="event_start_date2" name="event_start_date" required="required" class="form-control" value="<?= date('m/d/Y',strtotime($event->event_start_date)); ?>" placeholder="Date*">

                        </div>

                        <span class="text-danger"><?= form_error("event_start_date")?form_error("event_start_date"):'' ;?></span>

                      </div>

                      <div class="col-md-6">

                        <div class="form-group">

                          <input type="text" name="event_time" required="required" class="form-control" value="<?= $event->event_time; ?>" placeholder="Time*">

                        </div>

                        <span class="text-danger"><?= form_error("event_time")?form_error("event_time"):'' ;?></span>

                      </div>

                    </div>

                    <!--FILED END-->

                    <!--FILED START-->

                    <div class="row">

                      <div class="col-md-12">

                        <div class="form-group">

                          <textarea class="form-control" required="required" name="event_description" id="event_description" placeholder="Event Details">

                            <?= $event->event_description; ?>

                          </textarea>

                        </div>

                        <span class="text-danger"><?= form_error("event_description")?form_error("event_description"):'' ;?></span>

                      </div>

                    </div>

                    <!--FILED END-->

                    <!--FILED START-->

                    <div class="row">

                      <div class="col-md-12">

                        <div class="form-group">

                          <textarea class="form-control" name="event_map" placeholder="Google map location"><?= $event->event_map; ?></textarea>

                        </div>

                        <span class="text-danger"><?= form_error("event_map")?form_error("event_map"):'' ;?></span>

                      </div>

                    </div>

                    <!--FILED END-->

                    <!--FILED START-->

                    <div class="row">

                      <div class="col-md-12">

                        <div class="form-group">

                          <label>Choose banner image</label>

                          <input type="file" name="event_image" class="form-control">

                        </div>

                      </div>

                    </div>

                    <!--FILED END-->

                    <!--FILED START-->

                    <div class="row">

                      <div class="col-md-6">

                        <div class="form-group">

                          <input type="text" name="event_contact_name" required="required" class="form-control" value="<?= $event->event_contact_name; ?>" placeholder="Contact person*">

                        </div>

                        <span class="text-danger"><?= form_error("event_contact_name")?form_error("event_contact_name"):'' ;?></span>

                      </div>

                      <div class="col-md-6">

                        <div class="form-group">

                          <input type="text" name="event_mobile" required="required" class="form-control" value="<?= $event->event_mobile; ?>" placeholder="Contact phone number*">

                        </div>

                        <span class="text-danger"><?= form_error("event_mobile")?form_error("event_mobile"):'' ;?></span>

                      </div>

                    </div>

                    <!--FILED END-->

                    <!--FILED START-->

                    <div class="row">

                      <div class="col-md-6">

                        <div class="form-group">

                          <input type="email" name="event_email" required="required" class="form-control" value="<?= $event->event_email; ?>" placeholder="Contact Email Id *">

                        </div>

                        <span class="text-danger"><?= form_error("event_email")?form_error("event_email"):'' ;?></span>

                      </div>

                      <div class="col-md-6">

                        <div class="form-group">

                          <input type="text" name="event_website" class="form-control" value="<?= $event->event_website; ?>" placeholder="Event Website">

                        </div>

                        <span class="text-danger"><?= form_error("event_website")?form_error("event_website"):'' ;?></span>

                      </div>

                    </div>

                    <!--FILED END-->

                    <!--FILED START-->

                    <div class="row">

                      <div class="col-md-12">

                        <div>

                          <div class="chbox">

                            <input type="checkbox" id="isenquiry" name="isenquiry" <?= ($event->isenquiry == "1") ? 'checked' : '';?>>

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
                    <button type="submit" name="event_submit" class="btn btn-primary">Save Changes</button>
                  </div>
                </div>
                <!--FILED END-->
              </form>
              <div class="col-md-12">
                <a href="<?= base_url('admin/all-events'); ?>" class="skip">Go to user dashboard
                  >></a>
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


<script src="<?= base_url() ?>assets/ckeditor/ckeditor.js"></script>
<script>
CKEDITOR.replace('event_description');
</script>