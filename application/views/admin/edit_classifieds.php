<section class=" login-reg">
	<div class="container">
		<div class="row">
			<div class="login-main add-list">
				<div class="log-bor">&nbsp;</div> <span class="steps">Edit Classifieds</span>
				<div class="log">
					<div class="login add-list-off">
						<h4>Edit this Classifieds</h4>
						<?php if($this->session->flashdata('message')){ ?>
							<div class="log-suc" ><p><?= $this->session->flashdata('message') ?></p></div>
						<?php } ?>		
						<form action="<?= base_url('admin/edit-classifieds/'.$classifieds->classifieds_slug); ?>" class="classifieds_edit_form" id="classifieds_edit_form" name="classifieds_form" method="post" enctype="multipart/form-data">

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
															<option value="<?= $user->user_id; ?>" <?= ($classifieds->user_id == $user->user_id) ? 'SELECTED' : '';?> ><?= e($user->name) ?></option>
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
													<input type="text" name="classifieds_title" required="required" value="<?= set_value('classifieds_title',$classifieds->classifieds_title);?>" class="form-control" placeholder="classifieds Title*">
												</div>
												<span class="text-danger"><?= form_error("classifieds_title")?form_error("classifieds_title"):'' ;?></span>
											</div>
										</div>

										<!--FILED END-->
						                  <!--FILED START-->
						                  <div class="row">
						                    <div class="col-md-12">
						                      <div class="form-group">
						                        <label for="">Price</label>
						                        <input type="text" value="<?= set_value('classifieds_price',$classifieds->classifieds_price);?>" name="classifieds_price" required="required" class="form-control" placeholder="Exp - 200">
						                      </div>
												<span class="text-danger"><?= form_error("classifieds_title")?form_error("classifieds_title"):'' ;?></span>
						                    </div>
						                  </div>
						                  <!--FILED END-->
										<!--FILED START-->

										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<select onChange="getSubCategory(this.value);" name="classifieds_category_id" id="classifieds_category_id"  class="form-control">
														<option value="">Select Category</option>
														<?php
														if(count($all_classifieds_category) > 0){
															foreach ($all_classifieds_category as $value) {
																?>
																<option value="<?= $value->category_id;?>" <?= ($classifieds->classifieds_category_id == $value->category_id) ? 'SELECTED' : '';?>><?= $value->category_name;?></option>
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
											<div class="col-md-6">
												<div>
													<div class="chbox">
														<input type="checkbox" name="featured_classifieds" value="1"  id="featured_classifieds" <?= $classifieds->featured_classifieds == '1' ? 'CHECKED': '' ?>>
														<label for="featured_classifieds">Featured classifieds</label>
													</div>
												</div>
											</div>
						                    <div class="col-md-6">
						                      <div>
						                        <div class="chbox">
						                          <input type="checkbox" id="spotlight_classifieds" name="spotlight_classifieds" value="1" <?= $classifieds->spotlight_classifieds == '1' ? 'CHECKED': '' ?>>
						                          <label for="spotlight_classifieds">Spotlight Classifieds</label>
						                        </div>
						                      </div>
						                    </div>
										</div>

										<!--FILED END-->
						                  <!--FILED START-->
						                  <div class="row">
						                    <div class="col-md-12">
						                      <div class="chbox">
						                        <input type="checkbox" name="display_contact"  id="display_contact" value="1" <?= $classifieds->display_contact == '1' ? 'CHECKED': '' ?>>
						                          <label for="display_contact">Display Contact Information</label>
						                      </div>
						                    </div>
						                  </div>
						                  <!--FILED END-->
										<!--FILED START-->

										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label for="">classifieds Details</label>
													<textarea name="classifieds_description" id="classifieds_description" required="required" class="form-control" placeholder="Post Details">
														<?= $classifieds->classifieds_description;?>
													</textarea>
												</div>

												<span class="text-danger"><?= form_error("classifieds_description")?form_error("classifieds_description"):'' ;?></span>

											</div>

										</div>

										<!--FILED END-->

										<!--FILED START-->

										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label>Choose classifieds preview image</label>
													<input type="file" name="classifieds_image" class="form-control">
												</div>
											</div>
										</div>

										<!--FILED END-->

										
										<div class="row">
	                    <div class="col-md-12">
	                      <div class="form-group">
	                        <label>SEO Title</label>
	                        <input type="text" name="classifieds_seo_title" placeholder=""  class="form-control" value="<?= set_value('classifieds_seo_title',$classifieds->classifieds_seo_title);?>">
	                      </div>
	                      <div class="form-group">
	                        <label>SEO Keywords</label>
	                        <input type="text" name="classifieds_seo_keywords"  class="form-control" placeholder="i.e:wedding hall, best wedding hall" value="<?= set_value('classifieds_seo_keywords',$classifieds->classifieds_seo_keywords);?>">
	                      </div>
	                      <div class="form-group">
	                        <label>SEO Descriptions</label>
	                        <input type="text" name="classifieds_seo_description"   class="form-control" value="<?= set_value('classifieds_seo_description',$classifieds->classifieds_seo_description);?>">
	                      </div>
	                    </div>
	                  </div> 

									</li>

								</ul>

								<!--FILED START-->

								<div class="row">

									<div class="col-md-12">

										<button type="submit" name="classifieds_submit" class="btn btn-primary">Submit</button>

									</div>

									<div class="col-md-12"> <a href="<?= base_url('/admin/classifieds-list'); ?>" class="skip">Go to Classifieds Dashboard >></a>

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

		CKEDITOR.replace('classifieds_description');

	</script>