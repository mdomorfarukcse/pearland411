

<!-- START -->
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <section class="login-reg">
        <div class="container">
          <div class="row">
            <div class="login-main add-list posr">
              <div class="log-bor">&nbsp;</div>
              <span class="udb-inst">Add Slider Photo</span>
              <div class="log log-1">
                <div class="login">
                  <h4>Add New Slider Photo</h4>
                  <?php if($this->session->flashdata('message')){ ?>
                    <div class="log-suc" ><p><?= $this->session->flashdata('message') ?></p></div>
                  <?php } ?>
                  <form name="slider_form" id="slider_form" method="post" action="<?= base_url('admin/slider_create') ?>" enctype="multipart/form-data">

                    <ul>
                      <li>
                        <!--FILED START-->
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <label>Choose slider image</label>
                              <input type="file" name="slider_photo" class="form-control" required>
                              <div class='formError'><?= form_error('slider_photo') ?></div>
                            </div>
                          </div>
                        </div>
                        <!--FILED END-->
                        <!--FILED START-->
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <textarea  id="slider_link"  name="slider_link" class="form-control" placeholder="Slider External link" ></textarea>
                            </div>
                          </div>
                        </div>

                      </li>
                    </ul>
                    <!--FILED START-->
                    <div class="row">
                      <div class="col-md-12">
                        <button type="submit" name="slider_submit" class="btn btn-primary">Submit</button>
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
