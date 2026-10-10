<!-- START -->
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
     <section class="login-reg">
      <div class="container">
       <div class="row">
        <div class="login-main add-list posr">
         <div class="log-bor">&nbsp;</div>
         <span class="udb-inst">New News</span>
         <div class="log log-1">
          <div class="login">
            <h4>Create News</h4>
            <form name="news_form" id="news_form" method="post" action="<?= base_url('admin/add-news') ?>" enctype="multipart/form-data">
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
                        <option value="<?= $user->user_id; ?>"><?= e(user_option_label($user)) ?></option>

                        <?php } } ?>

                      </select>
                        <?php // Byline (migration 0035). The author account above decides who
                              // owns the post; this decides the NAME printed on it — so a post
                              // filed under a staff login can read "Pearland411" or a reporter's
                              // name instead of whatever that login happens to be called. ?>
                        <label for="news_byline" style="display:block;margin-top:10px;">Byline <small class="text-muted">(optional — the author name shown on the site; leave empty to use the account&rsquo;s name)</small></label>
                        <input type="text" name="news_byline" id="news_byline" maxlength="120" class="form-control"
                               placeholder="e.g. Pearland411 Staff" value="<?= e_attr(set_value('news_byline', '')) ?>">
                        <?= validation_errors('<div class="text-danger" style="margin-top:8px;">', '</div>') ?>                        
                        
                      </div>
                    </div>
                  </div>
                  <!--FILED END-->
                  <!--FILED START-->
                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <input type="text" name="news_title" required="required" class="form-control" placeholder="News Title *">
                      </div>
                    </div>
                  </div>
                  <!--FILED END-->
                  <!--FILED START-->

                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <select onChange="getSubCategory(this.value);" name="news_category_id" id="news_category_id" class="form-control">
                          <option value="">Select Category</option>
                          <?php
                          if(count($all_news_category) > 0){
                            foreach ($all_news_category as $value) {
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
                          <input type="checkbox" name="featured_news"  id="featured_news" value="1">
                          <label for="featured_news">Featured News</label>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!--FILED END-->  
                  <!--FILED START-->
                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label>News Details</label>
                        <textarea name="news_description" id="news_description" required="required" class="form-control" placeholder="News details"></textarea>
                      </div>
                    </div>
                  </div>
                  <!--FILED END-->
                  <!--FILED START-->
                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label>Choose News image</label>
                        <input type="file" name="news_image" class="form-control">
                      </div>
                    </div>
                  </div>
                  <!--FILED END-->
                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label>SEO title</label>
                        <input type="text" name="news_seo_title" value="" placeholder=""  class="form-control">
                      </div>
                      <div class="form-group">
                        <label>SEO keywords</label>
                        <input type="text" name="news_seo_keywords" value=""  class="form-control" placeholder="i.e:wedding hall, best wedding hall">
                      </div>
                      <div class="form-group">
                        <label>SEO descriptions</label>
                        <input type="text" name="news_seo_description" value=""  class="form-control">
                      </div>
                    </div>
                  </div>  

                </li>
              </ul>
              <!--FILED START-->
              <div class="row">
                <div class="col-md-12">
                  <button type="submit" name="news_submit" class="btn btn-primary">Submit</button>
                </div>
              </div>
              <!--FILED END-->
            </form>
            <div class="col-md-12">
              <a href="<?= base_url('admin/news-list') ?>" class="skip">Go to News Dashboard >></a>
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
  CKEDITOR.replace('news_description');
</script>