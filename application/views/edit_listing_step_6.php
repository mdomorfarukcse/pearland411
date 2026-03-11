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
								<a href="add-listing-step-5.html"> <span>Step 5</span>
									<b>Other</b>
								</a>
							</li>
							<li>
								<a href="add-listing-step-6.html" class="act"> <span>Step 6</span>
									<b>done</b>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="login-main add-list">
					<div class="log-bor">&nbsp;</div> <span class="steps">Step 6</span>
					<div class="log">
						<div class="login add-lis-done">
							<h4>Success</h4>
							<p>Your listing has been submitted successfully.</p>
							<form>
								<!--FILED START-->
								<div class="row">
									<div class="col-md-12"> <i class="material-icons succ">done</i>
									</div>
								</div>
								<!--FILED END-->
								<!--FILED START-->
								<div class="row">
									<div class="col-md-6"> <a href="<?= base_url('user/dashboard');?>" class="btn btn-primary">Go to user dashboard</a>
									</div>
									<div class="col-md-6"> <a target="_blank" href="<?= base_url('user/listing-preview/'.$listing_id);?>" class="btn btn-primary">Listing preview</a>
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