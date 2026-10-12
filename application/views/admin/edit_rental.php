<!-- START -->
<section>
	<div class="ad-com">
		<div class="ad-dash leftpadd">
			<div class="login-reg">
				<div class="container">
					<form action="<?= base_url('admin/edit-rental/'.$rental->rental_slug); ?>" class="rental_edit_form" id="rental_edit_form" name="rental_form" method="post" enctype="multipart/form-data">
						<div class="row">
							<div class="login-main add-list posr">
								<div class="log-bor">&nbsp;</div>
								<span class="udb-inst">Edit Apartments & Rentals</span>
								<div class="log log-1">
									<div class="login">
										<h4>Edit Apartments & Rentals</h4>
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
																<option value="<?= $user->user_id; ?>" <?= ($rental->user_id == $user->user_id) ? 'SELECTED' : '';?> ><?= e(user_option_label($user)) ?></option>
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
														<label for="">Property Title</label>
														<input type="text" value="<?= set_value('rental_title',$rental->rental_title);?>" name="rental_title" required="required" class="form-control" placeholder="Property Title *" >
														<span class="text-danger"><?= form_error("rental_title")?form_error("rental_title"):'' ;?></span>
													</div>
												</div>
											</div>
											<!--FILED END-->
											<!--FILED START-->
											<div class="row">
												<div class="col-md-12">
													<div class="form-group">
														<label for="">Rental Price</label>
														<input type="text" value="<?= set_value('rental_price',$rental->rental_price);?>" name="rental_price" required="required" maxlength="50" class="form-control" placeholder="e.g. 2000 or $890 - $1,500">
													</div>
												</div>
											</div>
											<!--FILED END-->
											<!--FILED START-->
											<div class="row">
												<div class="col-md-12">
													<div class="form-group">
														<select onChange="getSubCategory(this.value);" name="rental_category_id" id="rental_category_id" class="form-control">
															<option value="">Select A Category</option>
															<?php
															if(count($all_rental_category) > 0){
																foreach ($all_rental_category as $value) {
																	?>
																	<option value="<?= $value->category_id;?>" <?= ($rental->rental_category_id == $value->category_id) ? 'SELECTED' : '';?>><?= $value->category_name;?></option>
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
															<input type="checkbox" id="featured_rental" name="featured_rental" value="1" <?= $rental->featured_rental == '1' ? 'CHECKED': '' ?>>
															<label for="featured_rental">Featured Rental</label>
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div>
														<div class="chbox">
															<input type="checkbox" id="spotlight_rental" name="spotlight_rental" value="1" <?= $rental->spotlight_rental == '1' ? 'CHECKED': '' ?>>
															<label for="spotlight_rental">Spotlight Rental</label>
														</div>
													</div>
												</div>
											</div>
											<!--FILED END-->  
											<!--FILED START-->
											<div class="row">
												<div class="col-md-12">
													<div class="chbox">
														<input type="checkbox" name="display_contact"  id="display_contact" value="1" <?= $rental->display_contact == '1' ? 'CHECKED': '' ?>>
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
														<textarea name="rental_address" id="rental_address" required="required" class="form-control" placeholder="Property Address">
															<?= $rental->rental_address;?>
														</textarea>
													</div>
												</div>
											</div>
											<!--FILED END-->

											<!--FILED START-->
											<div class="row">
												<div class="col-md-12">
													<div class="form-group">
														<label>Choose rental Image</label>
														<input type="file" name="rental_image" class="form-control">
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
														<textarea name="rental_description" id="rental_description" required="required" class="form-control" placeholder="Property details">
															<?= $rental->rental_description;?>
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
														<input type="text" value="<?= set_value('bedrooms',$rental->bedrooms);?>" name="bedrooms" class="form-control" placeholder="Exp 2">
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label for="">Living Area (Sq. Ft.)</label>
														<input type="text" value="<?= set_value('living_area',$rental->living_area);?>" name="living_area" class="form-control" placeholder="Exp 2000">
													</div>
												</div> 
												<div class="col-md-4">
													<div class="form-group">
														<label for="">Bathroom(s) </label>
														<input type="text" value="<?= set_value('bathroom',$rental->bathroom);?>" name="bathroom" class="form-control" placeholder="Exp 2">
													</div>
												</div> 
											</div>
											<!--FILED END-->
											<!--FILED START-->
											<div class="row">
												<div class="col-md-4">
													<div class="form-group">
														<label>Property Age</label>
														<input type="text" value="<?= set_value('property_age',$rental->property_age);?>" name="property_age" value="" placeholder="" class="form-control">
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label for="">Garage(s)</label>
														<input type="text" value="<?= set_value('garage',$rental->garage);?>" name="garage" class="form-control" placeholder="Exp 2000">
													</div>
												</div> 
												<div class="col-md-4">
													<div class="form-group">
														<label for="">Floors </label>
														<input type="text" value="<?= set_value('floors',$rental->floors);?>" name="floors" class="form-control" placeholder="Exp 2">
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
																<input type="checkbox" id="rent_to_own" name="rent_to_own" value="1" <?= $rental->rent_to_own == '1' ? 'CHECKED': '' ?>>
																<label for="rent_to_own">Rent to Own?</label>
															</div>
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<div>
															<div class="chbox">
																<input type="checkbox" id="can_sublet" name="can_sublet" value="1" <?= $rental->can_sublet == '1' ? 'CHECKED': '' ?>>
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
												<input type="text" value="<?= set_value('rental_seo_title',$rental->rental_seo_title);?>" name="rental_seo_title" value="" placeholder="" class="form-control">
											</div>
											<div class="form-group">
												<label>Page keywords</label>
												<input type="text" value="<?= set_value('rental_seo_keywords',$rental->rental_seo_keywords);?>" name="rental_seo_keywords" value="" class="form-control" placeholder="i.e:wedding hall, best wedding hall">
											</div>
											<div class="form-group">
												<label>Page descriptions</label>
												<input type="text" value="<?= set_value('rental_seo_description',$rental->rental_seo_description);?>" name="rental_seo_description" value="" class="form-control">
											</div>
											<!--FILED START-->
											<div class="row">
												<div class="col-md-12">
													<button type="submit" name="rental_submit" class="btn btn-primary">Update</button>
												</div>
											</div>
											<!--FILED END-->
											<div class="col-md-12">
												<a href="<?= base_url('admin/rental-list') ?>" class="skip">Go to Apartments & Rentals Dashboard >></a>
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
		CKEDITOR.replace('rental_description');
		CKEDITOR.replace('rental_address');
	</script>