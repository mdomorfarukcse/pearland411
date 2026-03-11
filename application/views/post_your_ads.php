
	<!-- START -->
	<!--PRICING DETAILS-->
	<section class=" login-reg">
		<div class="container">
			<div class="row">
				<div class="login-main add-list">
					<div class="log-bor">&nbsp;</div> <span class="steps">Create new Ads</span>  

					<?php if($this->session->flashdata('message')){ ?>
						<div class="log-suc" ><p><?= $this->session->flashdata('message') ?></p></div>
					<?php } ?>	 
					
					<div class="log">
						<div class="login">
							<h4>Submit your Ads</h4>
							<form name="create_ads_form" id="create_ads_form" method="post" action="<?= base_url('post-ads');?>" enctype="multipart/form-data">
							<input type="hidden" value="" name="ad_total_days" id="ad_total_days" class="validate">
							<input type="hidden" value="" name="ad_cost_per_day" id="ad_cost_per_day" class="validate">
                    		<input type="hidden" value="" name="ad_total_cost" id="ad_total_cost" class="validate">
                    		<input type="hidden" value="" name="ad_width" id="ad_width" class="validate">
                    		<input type="hidden" value="" name="ad_height" id="ad_height" class="validate">
								<ul>
									<li>
										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="form-group ca-sh-user">
												<select name="ads_price_id" required="required" class="form-control"  id="adposi">
													<option value="">Choose Ads Position *</option>
													<?php
													if($adsPrices && $adsPrices != null){
														foreach ($adsPrices as $key => $value) {
													?>
													<!-- <option myTag="<?= $value->cost;?>" adWidth="<?=$value->width;?>" adHeight="<?=$value->height;?>" value="<?= $value->id;?>"><?= $value->name;?> (<?= $value->cost;?> /per day)</option> -->
													<option myTag="<?= $value->cost;?>"><?= $value->name;?> (<?= $value->cost;?> /per day)</option>
													<?php
														}
													}
													?>
												</select>
												<a href="<?= base_url('user/ads-price'); ?>" class="frmtip" target="_blank">Pricing
                                                        details</a>
												</div>
												<span class="text-danger"><?= form_error("ads_price_id")?form_error("ads_price_id"):'' ;?></span>
											</div>
										</div>
										<!--FILED END-->
										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<input type="date" autocomplete="off" value="<?= set_value('ad_start_date');?>" name="ad_start_date" id="" class="form-control" placeholder="Ad start date (MM/DD/YYYY)" required>
												</div>
												<span class="text-danger"><?= form_error("ad_start_date")?form_error("ad_start_date"):'' ;?></span>
											</div>
										</div>
										<!--FILED END-->
										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<input type="date" autocomplete="off" value="<?= set_value('ad_end_date');?>" name="ad_end_date" id="" class="form-control" placeholder="Ad end date (MM/DD/YYYY)" required>
												</div>
												<span class="text-danger"><?= form_error("ad_end_date")?form_error("ad_end_date"):'' ;?></span>
											</div>
										</div>
										<!--FILED END-->
										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label>Choose Ad image</label>
													<input type="file" name="ad_photo" class="form-control" required>
												</div>
												<span class="text-danger"><?= form_error("ad_photo")?form_error("ad_photo"):'' ;?></span>
											</div>
										</div>
										<!--FILED END-->
										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<textarea id="ad_link" name="ad_link" class="form-control" placeholder="Advertisement External link" required></textarea>
												</div>
                            					<span class="text-danger"><?= form_error("ad_link")?form_error("ad_link"):'' ;?></span>
											</div>
										</div>
										<!--FILED END-->
										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="ad-pri-cal">
													<ul>
														<li>
															<div> <span>Total days</span>
																<h5 class="ad-tdays">0</h5>
															</div>
														</li>
														<li>
															<div> <span>Cost Per Day</span>
																<h5>$<b class="ad-pocost">0</b></h5>
															</div>
														</li>
														<li>
															<div> <span>Tax</span>
																<h5>$4</h5>
															</div>
														</li>
														<li>
															<div> <span>Total Cost</span>
																<h5>$<b class="ad-tcost">0</b></h5>
															</div>
														</li>
													</ul>
												</div>
											</div>
										</div>
										<!--FILED END-->
									</li>
								</ul>
								<!--FILED START-->
								<div class="row">
									<div class="col-md-12">
										<button type="submit" name="create_ad_submit" class="btn btn-primary">Publish this Ad</button>
									</div>
									<div class="col-md-12"> <a href="<?= base_url('user/ads-summary');?>" class="skip">Go to Add Summary Dashboard >></a>
									</div>
								</div>
								<!--FILED END-->
							</form>
							<div class="ud-notes">
								<p><b>Notes:</b> Once you submit your Ad then Admin or support team will contact you shortly.</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!--END PRICING DETAILS-->

	<script src="<?= base_url('assets/ckeditor/ckeditor.js');?>"></script>
	<script>
		CKEDITOR.replace('ad_link');
	</script>