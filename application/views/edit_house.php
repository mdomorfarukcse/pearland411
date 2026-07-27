<!-- START -->
<section>
	<div class="ad-com">
		<div class="ad-dash leftpadd">
			<div class="login-reg">
				<div class="container">
					<form action="<?= base_url('user/edit-house/'.$house->house_slug); ?>" class="house_edit_form" id="house_edit_form" name="house_form" method="post" enctype="multipart/form-data">
						<div class="row">
							<div class="login-main add-list posr">
								<div class="log-bor">&nbsp;</div>
								<span class="udb-inst">Edit House For Sale</span>
								<div class="log log-1">
									<div class="login">
										<h4>Edit House For Sale</h4>
											<!--FILED START-->
											<div class="row">
												<div class="col-md-12">
													<div class="form-group">
														<label for="">Property Title</label>
														<input type="text" value="<?= set_value('house_title',$house->house_title);?>" name="house_title" required="required" class="form-control" placeholder="Property Title *" >
														<span class="text-danger"><?= form_error("house_title")?form_error("house_title"):'' ;?></span>
													</div>
												</div>
											</div>
											<!--FILED END-->
											<!--FILED START-->
											<div class="row">
												<div class="col-md-12">
													<div class="form-group">
														<label for="">house Price</label>
														<input type="text" value="<?= set_value('house_price',$house->house_price);?>" name="house_price" required="required" class="form-control" placeholder="Exp - 200">
													</div>
												</div>
											</div>
											<!--FILED END-->
											<!--FILED START-->
											<div class="row">
												<div class="col-md-12">
													<div class="form-group">
														<select onChange="getSubCategory(this.value);" name="house_category_id" id="house_category_id" class="form-control">
															<option value="">Select A Category</option>
															<?php
															if(count($all_house_category) > 0){
																foreach ($all_house_category as $value) {
																	?>
																	<option value="<?= $value->category_id;?>" <?= ($house->house_category_id == $value->category_id) ? 'SELECTED' : '';?>><?= $value->category_name;?></option>
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
															<input type="checkbox" id="featured_house" name="featured_house" value="1" <?= $house->featured_house == '1' ? 'CHECKED': '' ?>>
															<label for="featured_house">Featured house</label>
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div>
														<div class="chbox">
															<input type="checkbox" id="spotlight_house" name="spotlight_house" value="1" <?= $house->spotlight_house == '1' ? 'CHECKED': '' ?>>
															<label for="spotlight_house">Spotlight house</label>
														</div>
													</div>
												</div>
											</div>
											<!--FILED END-->  
											<!--FILED START-->
											<div class="row">
												<div class="col-md-12">
													<div class="chbox">
														<input type="checkbox" name="display_contact"  id="display_contact" value="1" <?= $house->display_contact == '1' ? 'CHECKED': '' ?>>
														<label for="display_contact">Display Contact Information</label>
													</div>
												</div>
											</div>
											<!--FILED END-->
											<!--FILED START-->
											<div class="row">
												<div class="col-md-12">
													<div class="form-group">
														<label>Property Address </label>
														<textarea name="house_address" id="house_address" required="required" class="form-control" placeholder="Property Address">
															<?= $house->house_address;?>
														</textarea>
													</div>
												</div>
											</div>
											<!--FILED END-->

											<!--FILED START-->
											<div class="row">
												<div class="col-md-12">
													<div class="form-group">
														<label>Choose house Image</label>
														<input type="file" name="house_image" class="form-control">
													</div>
												</div>
											</div>
											<!--FILED END-->
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="login-main add-list">
									<div class="log-bor">&nbsp;</div>
									<span class="steps">General Property Information</span>
									<div class="log add-list-map">
										<div class="login add-list-map">
											<!--FILED START-->
											<div class="row">
												<div class="col-md-12">
													<div class="form-group">
														<label>Property Description </label>
														<textarea name="house_description" id="house_description" required="required" class="form-control" placeholder="Property details">
															<?= $house->house_description;?>
														</textarea>
													</div>
												</div>
											</div>
											<!--FILED END-->
											<!--FILED START-->
											<div class="row">
												<div class="col-md-4">
													<div class="form-group">
														<label for="">Bedroom(s) </label>
														<input type="text" value="<?= set_value('bedrooms',$house->bedrooms);?>" name="bedrooms" class="form-control" placeholder="Exp 2">
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label for="">Living Area (Sq. Ft.)</label>
														<input type="text" value="<?= set_value('living_area',$house->living_area);?>" name="living_area" class="form-control" placeholder="Exp 2000">
													</div>
												</div> 
												<div class="col-md-4">
													<div class="form-group">
														<label for="">Bathroom(s) </label>
														<input type="text" value="<?= set_value('bathroom',$house->bathroom);?>" name="bathroom" class="form-control" placeholder="Exp 2">
													</div>
												</div> 
											</div>
											<!--FILED END-->
											<!--FILED START-->
											<div class="row">
												<div class="col-md-4">
													<div class="form-group">
														<label>Property Age</label>
														<input type="text" value="<?= set_value('property_age',$house->property_age);?>" name="property_age" value="" placeholder="" class="form-control">
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label for="">Garage(s)</label>
														<input type="text" value="<?= set_value('garage',$house->garage);?>" name="garage" class="form-control" placeholder="Exp 2000">
													</div>
												</div> 
												<div class="col-md-4">
													<div class="form-group">
														<label for="">Floors </label>
														<input type="text" value="<?= set_value('floors',$house->floors);?>" name="floors" class="form-control" placeholder="Exp 2">
													</div>
												</div> 
											</div>
											<!--FILED END-->
											<!--FILED START-->
											<div class="row">
												<div class="col-md-4">
													<div class="form-group">
														<div>
															<div class="chbox">
																<input type="checkbox" id="rent_to_own" name="rent_to_own" value="1" <?= $house->rent_to_own == '1' ? 'CHECKED': '' ?>>
																<label for="rent_to_own">Rent to Own?</label>
															</div>
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<div>
															<div class="chbox">
																<input type="checkbox" id="can_sublet" name="can_sublet" value="1" <?= $house->can_sublet == '1' ? 'CHECKED': '' ?>>
																<label for="can_sublet">Can Sublet? </label>
															</div>
														</div>
													</div>
												</div> 
											</div>
											<!--FILED END-->
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="login-main add-list">
									<div class="log-bor">&nbsp;</div>
									<span class="steps">SEO Setting</span>
									<div class="log add-list-map">
										<div class="login add-list-map">
											<div class="form-group">
												<label>Page title</label>
												<input type="text" value="<?= set_value('house_seo_title',$house->house_seo_title);?>" name="house_seo_title" value="" placeholder="" class="form-control">
											</div>
											<div class="form-group">
												<label>Page keywords</label>
												<input type="text" value="<?= set_value('house_seo_keywords',$house->house_seo_keywords);?>" name="house_seo_keywords" value="" class="form-control" placeholder="i.e:wedding hall, best wedding hall">
											</div>
											<div class="form-group">
												<label>Page descriptions</label>
												<input type="text" value="<?= set_value('house_seo_description',$house->house_seo_description);?>" name="house_seo_description" value="" class="form-control">
											</div>
											<!--FILED START-->
											<div class="row">
												<div class="col-md-12">
													<button type="submit" name="house_submit" class="btn btn-primary">Update</button>
												</div>
											</div>
											<!--FILED END-->
											<div class="col-md-12">
												<a href="<?= base_url('user/user-house-for-sale') ?>" class="skip">Go to House Dashboard >></a>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</section>
	<!-- END -->    

	<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>

	<script src="<?= base_url() ?>assets/ckeditor/ckeditor.js"></script>
	<script>
		CKEDITOR.replace('house_description');
		CKEDITOR.replace('house_address');
	</script>