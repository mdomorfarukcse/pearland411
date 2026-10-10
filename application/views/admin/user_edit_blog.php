<section class=" login-reg">
		<div class="container">
			<div class="row">
				<div class="login-main add-list">
					<div class="log-bor">&nbsp;</div> <span class="steps">Edit Blog post</span>
					<div class="log">
						<div class="login add-list-off">
							<h4>Edit this Blog Post</h4>

					          <?php if($this->session->flashdata('message')){ ?>
					            <div class="log-suc" ><p><?= $this->session->flashdata('message') ?></p></div>
					          <?php } ?>		
					          					
							<form action="<?= base_url('admin/edit-blog/'.$blog->blog_slug); ?>" class="blog_edit_form" id="blog_edit_form" name="blog_form" method="post" enctype="multipart/form-data">
								<ul>
									<li>
										<input type="hidden" name="blog_id" value="<?= set_value('blog_id',$blog->blog_id);?>">
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
                                                <option value="<?= $user->user_id; ?>" <?= ($user->user_id == $blog->user_id)?'SELECTED':'';?> ><?= e(user_option_label($user)) ?></option>
                        
                                                <?php } } ?>
                        
                                              </select>
                        <?php // Byline (migration 0035). The author account above decides who
                              // owns the post; this decides the NAME printed on it — so a post
                              // filed under a staff login can read "Pearland411" or a reporter's
                              // name instead of whatever that login happens to be called. ?>
                        <label for="blog_byline" style="display:block;margin-top:10px;">Byline <small class="text-muted">(optional — the author name shown on the site; leave empty to use the account&rsquo;s name)</small></label>
                        <input type="text" name="blog_byline" id="blog_byline" maxlength="120" class="form-control"
                               placeholder="e.g. Pearland411 Staff" value="<?= e_attr(set_value('blog_byline', (string) ($blog->blog_byline ?? ''))) ?>">
                        <?= form_error('user_id', '<div class="text-danger" style="margin-top:8px;">', '</div>') ?>                        
                                                
                                              </div>
                                            </div>
                                          </div>
                                          <!--FILED END-->
										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<input type="text" name="blog_name" required="required" value="<?= set_value('blog_name',$blog->blog_name);?>" class="form-control" placeholder="Post Name*">
												</div>
												<span class="text-danger"><?= form_error("blog_name")?form_error("blog_name"):'' ;?></span>
											</div>
										</div>
										<!--FILED END-->
										<!--FILED START-->
										<?php
										// Publish date. Posts created before this field existed have no
										// usable date stored and nothing in the row to recover it from,
										// so the box comes up empty and an admin types the real one.
										$blog_dt = us_date_ts($blog->created_at ?? null);
										?>
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label for="blog_created_at">Publish date</label>
													<input type="datetime-local" name="created_at" id="blog_created_at" class="form-control"
														   value="<?= e_attr(set_value('created_at', $blog_dt ? date('Y-m-d\TH:i', $blog_dt) : '')) ?>">
													<?php if (!$blog_dt): ?>
														<small class="text-danger">No date recorded for this post &mdash; it shows without one until you set it.</small>
													<?php endif; ?>
												</div>
											</div>
										</div>
										<!--FILED END-->
										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<textarea name="blog_description" id="blog_description" required="required" class="form-control" placeholder="Post Details">
														<?= e($blog->blog_description);?>
															
														</textarea>
												</div>
												<span class="text-danger"><?= form_error("blog_description")?form_error("blog_description"):'' ;?></span>
											</div>
										</div>
										<!--FILED END-->
										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<?php
													$mf_name    = 'blog_image';
													$mf_label   = 'Choose banner image';
													$mf_hint    = 'Leave empty to keep the current image.';
													$mf_current = $blog->blog_image ?? '';
													$mf_empty   = 'No banner uploaded yet.';
													include APPPATH . 'views/admin/partials/media_field.php';
													?>
												</div>
											</div>
										</div>
										<!--FILED END-->
										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div>
													<div class="chbox">
														<input type="checkbox" name="isenquiry" id="evefmenab" <?= $blog->isenquiry == '1' ? 'checked': '' ?>>
														<label for="evefmenab">Enquiry or Registration form enable</label>
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
										<button type="submit" name="blog_submit" class="btn btn-primary">Submit</button>
									</div>
									<div class="col-md-12"> <a href="<?= base_url('/admin/blog-list'); ?>" class="skip">Go to Blog dashboard >></a>
									</div>
								</div>
								<!--FILED END-->
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	
	
	
	
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>

<script src="<?= base_url() ?>assets/ckeditor/ckeditor.js"></script>
<script>
  CKEDITOR.replace('blog_description');
</script>