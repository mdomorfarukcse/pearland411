	<section class=" login-reg">
		<div class="container">
			<div class="row">
				<div class="login-main add-list">
					<div class="log-bor">&nbsp;</div> <span class="steps">Create new</span>
					<div class="log">
						<div class="login">
							<h4>Add New Listing</h4>
							<?php // This view is not loaded by any controller — the live one is
							      // add_new_listing_start.php, which has no duplicate option — but
							      // the block below is kept behind the same flag as the admin tool
							      // so wiring this file up later can't quietly bring it back. ?>
							<div class="row cre-dup">
								<div class="<?= duplicate_listing_enabled() ? 'col-md-6' : 'col-md-12' ?>"> <a href="<?= base_url('user/add_listing_scratch');?>">Create listing from scratch</a>
								</div>
								<?php if (duplicate_listing_enabled()): ?>
								<div class="col-md-6"> <span class="cre-dup-btn">Create duplicate listing</span>
								</div>
								<?php endif; ?>
							</div>
							<?php if (duplicate_listing_enabled()): ?>
							<form name="duplicate_listing_form" action="<?= base_url('user/duplicate-listing'); ?>" id="duplicate_listing_form" method="post" class="cre-dup-form cre-dup-form-show">
								<!--FILED START-->
								<div class="row">
									<div class="col-md-12">
										<div class="form-group">
											<select name="listing_id" id="listing_id" class="chosen-select form-control" required="required">
												<option value="" disabled selected>Select Listing Name</option>
												<?php
													if (count($listings) > 0) {
														foreach ($listings as $value) {
												?>
												<option value="<?= $value['listing_id']; ?>"><?= e($value['listing_name']) ?></option>
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
										<div class="form-group">
											<input type="text" name="listing_name" required="required" class="form-control" placeholder="New Listing Name*">
										</div>
									</div>
								</div>
								<!--FILED END-->
								<button type="submit" name="listing_submit" class="btn btn-primary">Create Now</button>
							</form>
							<?php endif; ?>
							<div class="col-md-12"> <a href="<?= base_url('user/dashboard');?>" class="skip">Go to user dashboard >></a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>