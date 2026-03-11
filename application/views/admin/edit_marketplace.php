<section class=" login-reg">
	<div class="container">
		<div class="row">
			<div class="login-main add-list">
				<div class="log-bor">&nbsp;</div> <span class="steps">Edit marketplace</span>
				<div class="log">
					<div class="login add-list-off">
						<h4>Edit this marketplace</h4>
						<?php if($this->session->flashdata('message')){ ?>
							<div class="log-suc" ><p><?= $this->session->flashdata('message') ?></p></div>
						<?php } ?>		
						<form action="<?= base_url('admin/edit-marketplace/'.$marketplace->marketplace_id); ?>" class="marketplace_edit_form" id="marketplace_edit_form" name="marketplace_form" method="post" enctype="multipart/form-data">

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
															<option value="<?= $user->user_id; ?>" <?= ($marketplace->user_id == $user->user_id) ? 'SELECTED' : '';?> ><?= $user->name; ?></option>
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
													<input type="text" name="marketplace_title" required="required" value="<?= set_value('marketplace_title',$marketplace->marketplace_title);?>" class="form-control" placeholder="marketplace Title*">
												</div>
												<span class="text-danger"><?= form_error("marketplace_title")?form_error("marketplace_title"):'' ;?></span>
											</div>
										</div>

										<!--FILED END-->
										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label for="">Price</label>
													<input type="text" value="<?= set_value('marketplace_price',$marketplace->marketplace_price);?>" name="marketplace_price" required="required" class="form-control" placeholder="Exp - 200">
												</div>
												<span class="text-danger"><?= form_error("marketplace_title")?form_error("marketplace_title"):'' ;?></span>
											</div>
										</div>
										<!--FILED END-->
										<!--FILED START-->

										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<select onChange="getSubCategory(this.value);" name="marketplace_category_id" id="marketplace_category_id"  class="form-control">
														<option value="">Select Category</option>
														<?php
														if(count($all_marketplace_category) > 0){
															foreach ($all_marketplace_category as $value) {
																?>
																<option value="<?= $value->category_id;?>" <?= ($marketplace->marketplace_category_id == $value->category_id) ? 'SELECTED' : '';?>><?= $value->category_name;?></option>
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
														<input type="checkbox" name="featured_marketplace" value="1"  id="featured_marketplace" <?= $marketplace->featured_marketplace == '1' ? 'CHECKED': '' ?>>
														<label for="featured_marketplace">Featured marketplace</label>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div>
													<div class="chbox">
														<input type="checkbox" id="spotlight_marketplace" name="spotlight_marketplace" value="1" <?= $marketplace->spotlight_marketplace == '1' ? 'CHECKED': '' ?>>
														<label for="spotlight_marketplace">Spotlight marketplace</label>
													</div>
												</div>
											</div>
										</div>

										<!--FILED END-->
										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="chbox">
													<input type="checkbox" name="display_contact"  id="display_contact" value="1" <?= $marketplace->display_contact == '1' ? 'CHECKED': '' ?>>
													<label for="display_contact">Display Contact Information</label>
												</div>
											</div>
										</div>
										<!--FILED END-->
										<!--FILED START-->

										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label for="">marketplace Details</label>
													<textarea name="marketplace_description" id="marketplace_description" required="required" class="form-control" placeholder="Post Details">
														<?= $marketplace->marketplace_description;?>
													</textarea>
												</div>

												<span class="text-danger"><?= form_error("marketplace_description")?form_error("marketplace_description"):'' ;?></span>

											</div>

										</div>

										<!--FILED END-->

										<!--FILED START-->

										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label>Choose marketplace preview image</label>
													<input type="file" name="marketplace_image" class="form-control">
												</div>
											</div>
										</div>

										<!--FILED END-->

										
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label>SEO Title</label>
													<input type="text" name="marketplace_seo_title" placeholder=""  class="form-control" value="<?= set_value('marketplace_seo_title',$marketplace->marketplace_seo_title);?>">
												</div>
												<div class="form-group">
													<label>SEO Keywords</label>
													<input type="text" name="marketplace_seo_keywords"  class="form-control" placeholder="i.e:wedding hall, best wedding hall" value="<?= set_value('marketplace_seo_keywords',$marketplace->marketplace_seo_keywords);?>">
												</div>
												<div class="form-group">
													<label>SEO Descriptions</label>
													<input type="text" name="marketplace_seo_description"   class="form-control" value="<?= set_value('marketplace_seo_description',$marketplace->marketplace_seo_description);?>">
												</div>
											</div>
										</div> 

									</li>

								</ul>

								<!--FILED START-->

								<div class="row">

									<div class="col-md-12">

										<button type="submit" name="marketplace_submit" class="btn btn-primary">Submit</button>

									</div>

									<div class="col-md-12"> <a href="<?= base_url('/admin/dashboard'); ?>" class="skip">Go to Admin Dashboard >></a>

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

	

	

	

	

	<script src="http://harvesthq.github.io/chosen/chosen.jquery.js"></script>



	<script src="<?= base_url() ?>assets/ckeditor/ckeditor.js"></script>

	<script>

		CKEDITOR.replace('marketplace_description');

	</script>