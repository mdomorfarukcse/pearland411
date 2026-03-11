	<section class=" login-reg">
		<div class="container">
			<div class="row">
				<div class="add-list-ste">
					<div class="add-list-ste-inn">
						<ul>
							<li>
								<a href="add-listing-step-1.html"> <span>Step 1</span>
									<b>Basic Info</b>
								</a>
							</li>
							<li>
								<a href="add-listing-step-2.html"> <span>Step 2</span>
									<b>Services</b>
								</a>
							</li>
							<li>
								<a href="add-listing-step-3.html" class="act"> <span>Step 3</span>
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
					<div class="log-bor">&nbsp;</div> <span class="steps">Step 3</span>
					<div class="log">
						<div class="login add-list-off">
							<h4>Special offers</h4>
<!-- 							<span class="add-list-add-btn lis-add-off" title="add new offer">+</span>
							<span class="add-list-rem-btn lis-add-rem" title="remove offer">-</span> -->
<!-- 							<form action="add-listing-step-4.html" class="listing_form_3" id="listing_form_3" name="listing_form_3" method="post" enctype="multipart/form-data"> -->
							<?= form_open_multipart('user/add-listing-step-3',['class'=>'listing_form_3','id'=>'listing_form_3']); ?>
								<ul>
									<li>
										<input type="hidden" name="listing_id" value="<?= $listing_id; ?>" required="required">
										<!--FILED START-->
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<input type="text" name="service_1_name" class="form-control" placeholder="Offer name*" value="<?= set_value('service_1_name');?>">
												</div>
												<span class="text-danger"><?= form_error("service_1_name")?form_error("service_1_name"):'' ;?></span>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<input type="text" name="service_1_price" onkeypress="return isNumber(event)" class="form-control" placeholder="Price" value="<?= set_value('service_1_price');?>">
												</div>
												<span class="text-danger"><?= form_error("service_1_price")?form_error("service_1_price"):'' ;?></span>
											</div>
										</div>
										<!--FILED END-->
										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<textarea name="service_1_detail" class="form-control" placeholder="Details about this offer" value="<?= set_value('service_1_detail');?>"></textarea>
												</div>
												<span class="text-danger"><?= form_error("service_1_detail")?form_error("service_1_detail"):'' ;?></span>
											</div>
										</div>
										<!--FILED END-->
										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label>Choose offer image</label>
													<input type="file" name="service_1_image" class="form-control">
												</div>
											</div>
										</div>
										<!--FILED END-->
										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<input type="text" name="service_1_view_more" class="form-control" placeholder="View More Link" value="<?= set_value('service_1_view_more');?>">
												</div>
												<span class="text-danger"><?= form_error("service_1_view_more")?form_error("service_1_view_more"):'' ;?></span>
											</div>
										</div>
										<!--FILED END-->
									</li>
								</ul>
								<!--FILED START-->
								<div class="row">
									<div class="col-md-6">
										<a href="<?= base_url('user/add-listing-step-2/'.$listing_id);?>">
											<button type="button" class="btn btn-primary">Previous</button>
										</a>
									</div>
									<div class="col-md-6">
										<button type="submit" name="listing_submit" class="btn btn-primary">Next</button>
									</div>
									<div class="col-md-12"> <a href="<?= base_url('user/add-listing-step-4/'.$listing_id);?>" class="skip">Skip this                                        >></a>
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