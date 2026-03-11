

<!-- START -->
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <section class="login-reg">
        <div class="container">
          <div class="row">
            <div class="login-main add-list posr">
              <div class="log-bor">&nbsp;</div>
              <span class="udb-inst">Edit Slider Photo</span>
              <div class="log log-1">
                <div class="login">
                  <h4>Edit this Slider Photo</h4>
                  <form name="edit_slider_form" id="edit_slider_form" method="post" action="<?= base_url() ?>admin/slider_edit/<?= $slider->slider_id ?>" enctype="multipart/form-data">
                    <input type="hidden" class="validate" id="slider_id" name="slider_id" value="10" required="required">
                    <input type="hidden" class="validate" id="slider_photo_old" name="slider_photo_old" value="90890557952.jpg" required="required">
                    <ul>
                      <li>
                        <!--FILED START-->
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <label>Old Slider Photo</label><br>
                              <img src="<?= base_url((!empty($slider->slider_photo)) ? $slider->slider_photo : 'assets/images/no_photo.jpg');?>" alt="" width="100%" height="150px">
                            </div>
                          </div>
                        </div>
                        <!--FILED END-->
                        <!--FILED START-->
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <label>Choose Slider Photo</label>
                              <input type="file" name="slider_photo" class="form-control" >
                            </div>
                          </div>
                        </div>
                        <!--FILED END-->
                        
                        <!--FILED START-->
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <textarea  id="slider_link"  name="slider_link" class="form-control" placeholder="Slider Photo External link" required><?= $slider->slider_link ?></textarea>
                            </div>
                          </div>
                        </div>
                        <!--FILED END-->
                      </li>
                    </ul>
                    <!--FILED START-->
                    <div class="row">
                      <div class="col-md-12">
                        <button type="submit" name="edit_slider_submit" class="btn btn-primary">Update And Submit</button>
                      </div>
                      <div class="col-md-12">
                        <a href="profile.html" class="skip">Go to User Dashboard >></a>
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