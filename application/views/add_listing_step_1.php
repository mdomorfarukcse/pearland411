<section class=" login-reg">
	<div class="container">
		<div class="row">
			<div class="add-list-ste">
				<div class="add-list-ste-inn">
					<ul>
						<li>
							<a href="add-listing-step-1.html" class="act"> <span>Step 1</span>
								<b>Basic Info</b>
							</a>
						</li>
						<li>
							<a href="add-listing-step-2.html"> <span>Step 2</span><b>Services</b>
							</a>

						</li>

						<li>

							<a href="add-listing-step-3.html"> <span>Step 3</span>

								<b>offers</b>

							</a>

						</li>

						<li>

							<a href="add-listing-step-4.html"> <span>Step 4</span>

								<b>map</b>

							</a>

						</li>

						<li>

							<a href="add-listing-step-5.html"> <span>Step 5</span>

								<b>Other</b>

							</a>

						</li>

						<li>

							<a href="add-listing-step-6.html"> <span>Step 6</span>

								<b>done</b>

							</a>

						</li>

					</ul>

				</div>

			</div>

		</div>

		<div class="row">

			<div class="login-main add-list">

				<div class="log-bor">&nbsp;</div> <span class="steps">Step 1</span>

				<div class="log">

					<div class="login">

						<h4>Listing Details</h4>

						<!--<form action="add-listing-step-2.html" class="listing_form_1" id="listing_form_1" name="listing_form_1" method="post" enctype="multipart/form-data">-->

							<?= form_open_multipart('user/add-listing-step-1',['class'=>'listing_form_1','id'=>'listing_form_1']); ?>	

							<!--FILED START-->

							<div class="row">

								<div class="col-md-12">

									<div class="form-group">

										<input id="listing_name" name="listing_name" type="text" required="required" class="form-control" value="<?= set_value('listing_name');?>" placeholder="Listing Name*">

									</div>

									<span class="text-danger"><?= form_error("listing_name")?form_error("listing_name"):'' ;?></span>

								</div>

							</div>

							<!--FILED END-->

							<!--FILED START-->

							<div class="row">

								<div class="col-md-6">

									<div class="form-group">

										<input type="text" name="listing_mobile" class="form-control" value="<?= set_value('listing_mobile');?>" placeholder="Phone number">

									</div>

									<span class="text-danger"><?= form_error("listing_mobile")?form_error("listing_mobile"):'' ;?></span>

								</div>

								<div class="col-md-6">

									<div class="form-group">

										<input type="text" name="listing_email" class="form-control" value="<?= set_value('listing_email');?>" placeholder="Email Id">

									</div>

									<span class="text-danger"><?= form_error("listing_email")?form_error("listing_email"):'' ;?></span>

								</div>

							</div>

							<!--FILED END-->

							<!--FILED START-->

							<div class="row">

								<div class="col-md-12">

									<div class="form-group">

										<input type="text" name="listing_whatsapp" class="form-control" value="<?= set_value('listing_whatsapp');?>" placeholder="Whatsapp Number (e.g. +919876543210)">

									</div>

									<span class="text-danger"><?= form_error("listing_whatsapp")?form_error("listing_whatsapp"):'' ;?></span>

								</div>

							</div>

							<!--FILED END-->

							<!--FILED START-->

							<div class="row">

								<div class="col-md-12">

									<div class="form-group">

										<input type="text" name="listing_website" class="form-control" value="<?= set_value('listing_website');?>" placeholder="Website(www.rn53themes.net)">

									</div>

									<span class="text-danger"><?= form_error("listing_website")?form_error("listing_website"):'' ;?></span>

								</div>

							</div>

							<!--FILED END-->

							<!--FILED START-->

							<div class="row">

								<div class="col-md-12">

									<div class="form-group">

										<input type="text" name="listing_address" class="form-control" value="<?= set_value('listing_address');?>" placeholder="Shop address">

									</div>

									<span class="text-danger"><?= form_error("listing_address")?form_error("listing_address"):'' ;?></span>

								</div>

							</div>

							<!--FILED END-->

							<!--FILED START-->

							<div class="row">

								<div class="col-md-6">

									<div class="form-group">

										<input type="text" name="listing_lat" class="form-control" value="<?= set_value('listing_lat');?>" placeholder="Latitude i.e 40.730610">

									</div>

									<span class="text-danger"><?= form_error("listing_lat")?form_error("listing_lat"):'' ;?></span>

								</div>

								<div class="col-md-6">

									<div class="form-group">

										<input type="text" name="listing_lng" class="form-control" value="<?= set_value('listing_lng');?>" placeholder="Longitude i.e -73.935242">

									</div>

									<span class="text-danger"><?= form_error("listing_lng")?form_error("listing_lng"):'' ;?></span>

								</div>

							</div>

							<!--FILED END-->

							<!--FILED START-->

							<div class="row">

								<div class="col-md-12">

									<div class="form-group">

<!-- 											<select onChange="getCities(this.value);" name="country_id" required="required" id="country_id" class="chosen-select form-control">

											<option value="">Select your Country</option>

											<option value="101">India</option>

											<option value="230">United Kingdom</option>

											<option value="231">United States</option>

										</select> -->

										<input type="text" name="country_name" class="form-control" value="<?= set_value('country_name');?>" placeholder="Country">

									</div>

									<span class="text-danger"><?= form_error("country_name")?form_error("country_name"):'' ;?></span>

								</div>

							</div>

							<!--FILED END-->

							<!--FILED START-->

							<!--                            <div class="row">-->

								<!--                                <div class="col-md-12">-->

									<!--                                    <div class="form-group">-->

										<!--                                        <input id="select-city" name="city_id" required="required" type="text"-->

										<!--                                               value="-->

										<!--"-->

										<!--                                               class="autocomplete form-control" placeholder="City name">-->

										<!--                                    </div>-->

										<!--                                </div>-->

										<!--                            </div>-->

										<!--FILED END-->

										<!--FILED START-->

										<div class="row">

											<div class="col-md-12">

												<div class="form-group">

													<input type="text" name="city_name" class="form-control" value="<?= set_value('city_name');?>" placeholder="City">

												</div>

												<span class="text-danger"><?= form_error("city_name")?form_error("city_name"):'' ;?></span>

											</div>

										</div>

										<!--FILED END-->

										<!--FILED START-->

										<div class="row">

											<div class="col-md-12">

												<div class="form-group">

													<select onChange="getSubCategory(this.value);" name="category_id" id="category_id" class="chosen-select form-control">

														<option value="">Select Category</option>

														<?php

														if(count($categories) > 0) {

															foreach ($categories as $category) {

																?>

																<option value="<?= $category->category_id; ?>"> <?= $category->category_name; ?> </option>

																<?php			

															}

														}

														?>

													</select>

												</div>

												<span class="text-danger"><?= form_error("category_id")?form_error("category_id"):'' ;?></span>

											</div>

										</div>

										<!--FILED END-->

										<!--FILED START-->

										<div class="row">

											<div class="col-md-12">

												<div class="form-group">

													<select data-placeholder="Select Sub Category" name="sub_category_id[]" id="sub_category_id" multiple class="chosen-select form-control">

														<option value="">Select Sub Category</option>

													</select>

												</div>

												<span class="text-danger"><?= form_error("sub_category_id")?form_error("sub_category_id"):'' ;?></span>

											</div>

										</div>

										<!--FILED END-->

										<!--FILED START-->

										<div class="row">

											<div class="col-md-12">

												<div class="form-group">

													<textarea class="form-control" id="listing_description" name="listing_description" placeholder="Details about your listing"></textarea>

												</div>

												<span class="text-danger"><?= form_error("listing_description")?form_error("listing_description"):'' ;?></span>

											</div>

										</div>

										<!--FILED END-->

										<!--FILED START-->

										<div class="row">

											<div class="col-md-6">

												<div class="form-group">

													<label>Choose profile image</label>

													<input type="file" required="required" name="profile_image" class="form-control">

												</div>

											</div>

											<div class="col-md-6">

												<div class="form-group">

													<label>Choose cover image</label>

													<input type="file" required="required" name="cover_image" class="form-control">

												</div>

											</div>

										</div>

										<!--FILED END-->

										<!--FILED START-->

										<div class="row">

											<div class="col-md-12">

												<div class="form-group">

													<textarea class="form-control" id="service_locations" name="service_locations" placeholder="Enter your service locations... &#10;(i.e) London, Dallas, Wall Street, Opera House"></textarea>

												</div>

												<span class="text-danger"><?= form_error("service_locations")?form_error("service_locations"):'' ;?></span>

											</div>

										</div>

										<!--FILED END-->

										<button type="submit" name="listing_submit" class="btn btn-primary">Next</button>

									</form>

								</div>

							</div>

						</div>

					</div>

				</div>

			</section>