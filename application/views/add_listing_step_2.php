	<section class=" login-reg">
		<div class="container">
			<div class="row">
				<div class="add-list-ste">
					<div class="add-list-ste-inn">
						<ul>
							<li>
								<a href="edit-listing-step-1.html"> <span>Step 1</span>
									<b>Basic Info</b>
								</a>
							</li>
							<li>
								<a href="edit-listing-step-2.html" class="act"> <span>Step 2</span>
									<b>Services</b>
								</a>
							</li>
							<li>
								<a href="edit-listing-step-3.html"> <span>Step 3</span>
									<b>offers</b>
								</a>
							</li>
							<li>
								<a href="edit-listing-step-4.html"> <span>Step 4</span>
									<b>map</b>
								</a>
							</li>
							<li>
								<a href="edit-listing-step-5.html"> <span>Step 5</span>
									<b>Other</b>
								</a>
							</li>
							<li>
								<a href="edit-listing-step-6.html"> <span>Step 6</span>
									<b>done</b>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="login-main add-list add-list-ser">
					<div class="log-bor">&nbsp;</div> <span class="steps">Step 2</span>
					<div class="log">
						<div class="login">
							<h4>Services provide</h4>
<!-- 							<span class="add-list-add-btn lis-ser-add-btn" title="add new offer">+</span>
							<span class="add-list-rem-btn lis-ser-rem-btn" title="remove offer">-</span> -->
<!-- 							<form action="add-listing-step-3.html" class="listing_form_2" id="listing_form_2" name="listing_form_2" method="post" enctype="multipart/form-data"> -->
							<?= form_open_multipart('user/add-listing-step-2',['class'=>'listing_form_2','id'=>'listing_form_2']); ?>
		<!-- 						<input id="listing_name" name="listing_name" type="hidden" value="qwerqw" required="required" class="validate">
								<input id="listing_mobile" name="listing_mobile" type="hidden" value="89769876" required="required" class="validate">
								<input id="listing_email" name="listing_email" type="hidden" value="hgfjhg@JHgfjh.in" required="required" class="validate">
								<input id="listing_website" name="listing_website" type="hidden" value="qwerqw.asdfasdf.in" required="required" class="validate">
								<input id="category_id" name="category_id" type="hidden" value="19" required="required" class="validate">
								<input id="sub_category_id" name="sub_category_id" type="hidden" value="Array" required="required" class="validate">
								<input id="listing_description" name="listing_description" type="hidden" value="<p>asaDasd</p>
" required="required" class="validate">
								<input id="mobile_number" readonly="" name="mobile_number" value="5522114422" required="required" type="hidden" class="validate">
								<input id="email_id" readonly="" name="email_id" value="rn53themes@gmail.com" required="required" type="hidden" class="validate">
								<input id="listing_address" name="listing_address" value="adsfa adfas" required="required" type="hidden" class="validate">
								<input id="city_id" name="city_id" value="Array" required="required" type="hidden" class="validate">
								<input id="country_id" name="country_id" value="101" required="required" type="hidden" class="validate">
								<input id="profile_image" name="profile_image" value="82551rn53.png" required="required" type="hidden" class="validate">
								<input id="cover_image" name="cover_image" value="36220user-type.png" required="required" type="hidden" class="validate"> -->
								<input type="hidden" name="listing_id" value="<?= $listing_id; ?>" required="required">
								<ul>
									<li>
										<!--FILED START-->
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label>Service name:</label>
													<input type="text" name="service_name" class="form-control" placeholder="Ex: Plumbile" value="<?= set_value('service_name');?>">
												</div>
												<span class="text-danger"><?= form_error("service_name")?form_error("service_name"):'' ;?></span>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label>Choose profile image</label>
													<input type="file" name="service_image" class="form-control">
												</div>
												<span class="text-danger"><?= form_error("service_image")?form_error("service_image"):'' ;?></span>
											</div>
										</div>
										<!--FILED END-->
									</li>
								</ul>
								<!--FILED START-->
								<div class="row">
									<div class="col-md-6">
										<a href="<?= base_url('user/add-listing-step-1/'.$listing_id);?>">
											<button type="button" class="btn btn-primary">Previous</button>
										</a>
									</div>
									<div class="col-md-6">
										<button type="submit" name="listing_submit" class="btn btn-primary">Next</button>
									</div>
									<div class="col-md-12"> 
										<a href="<?= base_url('user/add-listing-step-3/'.$listing_id);?>" class="skip">Skip this&gt;&gt;</a>
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