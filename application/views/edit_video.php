<section class=" login-reg">
	<div class="container">
		<div class="row">
			<div class="login-main add-list">
				<div class="log-bor">&nbsp;</div> <span class="steps">Edit Video</span>
				<div class="log">
					<div class="login add-list-off">
						<h4>Edit this Video</h4>
						<?php if($this->session->flashdata('message')){ ?>
							<div class="log-suc" ><p><?= $this->session->flashdata('message') ?></p></div>
						<?php } ?>		
						<form action="<?= base_url('user/edit-video/'.$video->video_slug); ?>" class="video_edit_form" id="video_edit_form" name="video_form" method="post" enctype="multipart/form-data">

							<ul>
								<li>

									<!--FILED START-->
									<div class="row">
										<div class="col-md-12">
											<div class="form-group">
												<input type="text" name="video_title" required="required" value="<?= set_value('video_title',$video->video_title);?>" class="form-control" placeholder="video Title*">
											</div>
											<span class="text-danger"><?= form_error("video_title")?form_error("video_title"):'' ;?></span>
										</div>
									</div>

									<!--FILED END-->

									<!--FILED START-->
					                <div class="row">
					                    <div class="col-md-12">
					                      <div class="form-group">
					                        <label for="">Your Youtube Video Code</label>
					                        
					                        <input type="text" name="video_code" required="required" class="form-control" placeholder="Youtube Video Code*" value="<?= set_value('video_code',$video->video_code);?>">
					                        <p class="alert alert-warning">Example - This is your video url - https://www.youtube.com/watch?v=C9g3InA8XKc Copy after "v=" C9g3InA8XKc and paste this field.</p>
					                        <span class="text-danger"><?= form_error("video_code")?form_error("video_code"):'' ;?></span>
					                      </div>
					                    </div>
					                </div>
					                <!--FILED END-->
										<!--FILED START-->

										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<select onChange="getSubCategory(this.value);" name="video_category_id" id="video_category_id"  class="form-control">
														<option value="">Select Category</option>
														<?php
														if(count($all_video_category) > 0){
															foreach ($all_video_category as $value) {
																?>
																<option value="<?= $value->category_id;?>" <?= ($video->video_category_id == $value->category_id) ? 'SELECTED' : '';?>><?= $value->category_name;?></option>
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
														<input type="checkbox" name="featured_video" value="1"  id="featured_video" <?= $video->featured_video == '1' ? 'CHECKED': '' ?>>
														<label for="featured_video">Featured video</label>
													</div>
												</div>
											</div>
										</div>

										<!--FILED END-->
										<!--FILED START-->

										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label for="">Video Details</label>
													<textarea name="video_description" id="video_description" required="required" class="form-control" placeholder="Post Details">
														<?= $video->video_description;?>
													</textarea>
												</div>

												<span class="text-danger"><?= form_error("video_description")?form_error("video_description"):'' ;?></span>

											</div>

										</div>

										<!--FILED END-->

										<!--FILED START-->

										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label>Choose video preview image</label>
													<input type="file" name="video_image" class="form-control">
												</div>
											</div>
										</div>

										<!--FILED END-->

										
										<div class="row">
						                    <div class="col-md-12">
						                      <div class="form-group">
						                        <label>SEO Title</label>
						                        <input type="text" name="video_seo_title" placeholder=""  class="form-control" value="<?= set_value('video_seo_title',$video->video_seo_title);?>">
						                      </div>
						                      <div class="form-group">
						                        <label>SEO Keywords</label>
						                        <input type="text" name="video_seo_keywords"  class="form-control" placeholder="i.e:wedding hall, best wedding hall" value="<?= set_value('video_seo_keywords',$video->video_seo_keywords);?>">
						                      </div>
						                      <div class="form-group">
						                        <label>SEO Descriptions</label>
						                        <input type="text" name="video_seo_description"   class="form-control" value="<?= set_value('video_seo_description',$video->video_seo_description);?>">
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

									<div class="col-md-12"> <a href="<?= base_url('/user/video-list'); ?>" class="skip">Go to Video Dashboard >></a>

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

		CKEDITOR.replace('video_description');

	</script>