<!-- START -->
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
     <section class="login-reg">
      <div class="container">
       <div class="row">
        <div class="login-main add-list posr">
         <div class="log-bor">&nbsp;</div>
         <span class="udb-inst">Add Video</span>
         <div class="log log-1">
          <div class="login">
            <h4>Add video</h4>
            <form name="video_form" id="video_form" method="post" action="<?= base_url('admin/add-video') ?>" enctype="multipart/form-data">
              <ul>
                <li>
                  <!--FILED START-->
                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">

                      <select name="user_id" required="required" class="form-control" id="user_id">
                        <option value="">Choose a user</option>
                        <?php 
                        if($users){
                          foreach($users as $user){
                        ?>
                        <option value="<?= $user->user_id; ?>"><?= e($user->name) ?></option>

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
                        <input type="text" name="video_title" required="required" class="form-control" placeholder="video Title *">
                      </div>
                    </div>
                  </div>
                  <!--FILED END-->
                  <!--FILED START-->
                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label for="">Your Youtube Video Code</label>
                        
                        <input type="text" name="video_code" required="required" class="form-control" placeholder="Youtube Video Code*">
                        <p class="alert alert-warning">Example - This is your video url - https://www.youtube.com/watch?v=C9g3InA8XKc Copy after "v=" C9g3InA8XKc and paste this field.</p>
                      </div>
                    </div>
                  </div>
                  <!--FILED END-->
                  
                  <!--FILED START-->

                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <select onChange="getSubCategory(this.value);" name="video_category_id" id="video_category_id" class="form-control">
                          <option value="">Select Category</option>
                          <?php
                          if(count($all_video_category) > 0){
                            foreach ($all_video_category as $value) {
                              ?>
                              <option value="<?= $value->category_id;?>"><?= $value->category_name;?></option>
                              <?php 
                            }
                          }
                          ?>
                        </select>
                      </div>
                    </div>
                  </div>
                  <!--FILED END-->
                  <!--FILED START-->
                  <div class="row">
                    <div class="col-md-12">
                      <div>
                        <div class="chbox">
                          <input type="checkbox" name="featured_video"  id="featured_video" value="1">
                          <label for="featured_video">Featured Video</label>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!--FILED END-->  
                  <!--FILED START-->
                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label>Video Details</label>
                        <textarea name="video_description" id="video_description" required="required" class="form-control" placeholder="Video details"></textarea>
                      </div>
                    </div>
                  </div>
                  <!--FILED END-->
                  <!--FILED START-->
                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label>Choose Video Image</label>
                        <input type="file" name="video_image" class="form-control">
                      </div>
                    </div>
                  </div>
                  <!--FILED END-->
                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label>SEO Title</label>
                        <input type="text" name="video_seo_title" value="" placeholder=""  class="form-control">
                      </div>
                      <div class="form-group">
                        <label>SEO Keywords</label>
                        <input type="text" name="video_seo_keywords" value=""  class="form-control" placeholder="i.e:wedding hall, best wedding hall">
                      </div>
                      <div class="form-group">
                        <label>SEO Descriptions</label>
                        <input type="text" name="video_seo_description" value=""  class="form-control">
                      </div>
                    </div>
                  </div>  

                </li>
              </ul>
              <!--FILED START-->
              <div class="row">
                <div class="col-md-12">
                  <button type="submit" name="video_submit" class="btn btn-primary">Submit</button>
                </div>
              </div>
              <!--FILED END-->
            </form>
            <div class="col-md-12">
              <a href="<?= base_url('admin/video-list') ?>" class="skip">Go to Video Dashboard >></a>
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
  CKEDITOR.replace('video_description');
</script>