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
								<a href="add-listing-step-5.html" class="act"> <span>Step 5</span>
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
					<div class="log-bor">&nbsp;</div> <span class="steps">Step 5</span>
					<div class="log">
						<div class="login add-lis-oth">
							<h4>Other informations</h4>
							<span class="add-list-add-btn lis-add-oad" title="add new offer">+</span>
							<span class="add-list-rem-btn lis-add-ore" title="remove offer">-</span>
							<!-- <form action="listing_insert.html" class="listing_form" id="listing_form_5" name="listing_form_5" method="post" enctype="multipart/form-data"> -->
							<?= form_open_multipart('user/edit-listing-step-5',['class'=>'listing_form_5','id'=>'listing_form_5']); ?>
									
								<input type="hidden" name="listing_id" value="<?= $listing_id; ?>" required="required">
								<ul>
									<li>
										<!--FILED START-->
										<div class="row">
											<div class="col-md-5">
												<div class="form-group">
													<input type="text" name="listing_info_question[]" class="form-control" placeholder="Experience">
												</div>
												<span class="text-danger"><?= form_error("listing_info_question")?form_error("listing_info_question"):'' ;?></span>
											</div>
											<div class="col-md-2">
												<div class="form-group"> <i class="material-icons">arrow_forward</i>
												</div>
											</div>
											<div class="col-md-5">
												<div class="form-group">
													<input type="text" name="listing_info_answer[]" class="form-control" placeholder="20 years">
												</div>
												<span class="text-danger"><?= form_error("listing_info_answer")?form_error("listing_info_answer"):'' ;?></span>
											</div>
										</div>
										<!--FILED END-->
									</li>
								</ul>
								<!--FILED START-->
								<div class="row">
									<div class="col-md-6">
										<a href="<?= base_url('user/edit-listing-step-4/'.$listing_id);?>">
											<button type="button" class="btn btn-primary">Previous</button>
										</a>
									</div>
									<div class="col-md-6">
										<button type="submit" name="listing_submit" class="btn btn-primary">Finish</button>
									</div>		
									<div class="col-md-12"> 
										<a href="<?= base_url('user/dashboard');?>" class="skip"> Go to user deshboard &gt;&gt;</a>
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