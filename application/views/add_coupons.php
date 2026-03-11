	<!-- START -->
	<!--PRICING DETAILS-->
	<section class=" login-reg">
		<div class="container">
			<div class="row">
				<div class="login-main add-list">
					<div class="log-bor">&nbsp;</div> <span class="steps">Add new Coupons</span>
			        <?php if($this->session->flashdata('message')){ ?>
			            <div class="log-suc" ><p><?= $this->session->flashdata('message') ?></p></div>
			        <?php } ?>					
					<div class="log">
						<div class="login">
							<h4>Add New Coupons</h4>
							<form name="coupon_form" id="coupon_form" enctype="multipart/form-data" method="post" action="<?= base_url('user/add-coupon');?>" class="listing_form_1">
								<!--FILED START-->
								<div class="row">
									<div class="col-md-12">
										<div class="form-group">
											<input type="text" class="form-control" name="coupon_name" placeholder="Coupon name" value="<?= set_value('coupon_name');?>" required>
										</div>
											<span class="text-danger"><?= form_error("coupon_name")?form_error("coupon_name"):'' ;?></span>
									</div>
								</div>
								<!--FILED END-->
								<!--FILED START-->
								<div class="row">
									<div class="col-md-12">
										<div class="form-group">
											<input type="text" class="form-control" name="coupon_code" placeholder="Offer code" value="<?= set_value('coupon_code');?>" required>
										</div>
											<span class="text-danger"><?= form_error("coupon_code")?form_error("coupon_code"):'' ;?></span>
									</div>
								</div>
								<!--FILED END-->
								<!--FILED START-->
								<div class="row">
									<div class="col-md-12">
										<div class="form-group">
											<input type="text" class="form-control" name="coupon_link" value="<?= set_value('coupon_link');?>" placeholder="Website link(if online offer)">
										</div>
											<span class="text-danger"><?= form_error("coupon_link")?form_error("coupon_link"):'' ;?></span>
									</div>
								</div>
								<!--FILED END-->
								<!--FILED START-->
								<div class="row">
									<div class="col-md-12">
										<div class="form-group">
											<label>Brand logo or Offer image(Recommended size 65 X 65)</label>
											<input type="file" required="required" name="coupon_image" class="form-control" placeholder="Offer image">
										</div>
									</div>
								</div>
								<!--FILED END-->
								<!--FILED START-->
								<div class="row">
									<div class="col-md-6">
										<div class="form-group">
											<label>Start date</label>
											<input type="date" class="form-control" value="<?= set_value('coupon_start_date');?>" name="coupon_start_date" required>
										</div>
											<span class="text-danger"><?= form_error("coupon_start_date")?form_error("coupon_start_date"):'' ;?></span>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label>End date</label>
											<input type="date" class="form-control" value="<?= set_value('coupon_end_date');?>" name="coupon_end_date" required>
										</div>
											<span class="text-danger"><?= form_error("coupon_end_date")?form_error("coupon_end_date"):'' ;?></span>
									</div>
								</div>
								<!--FILED END-->
								<button type="submit" name="coupon_submit" class="btn btn-primary">Submit</button>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!--END PRICING DETAILS-->