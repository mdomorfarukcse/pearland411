
<!-- START -->
<section>
	<div class="ad-com">
		<div class="ad-dash leftpadd">
			<section class="login-reg">
				<div class="container">
					<div class="row">
						<div class="login-main add-list posr">
							<div class="log-bor">&nbsp;</div> <span class="udb-inst">Edit ads</span>
							<div class="log log-1">
								<div class="login">
									<h4>Edit this Ad</h4>
									<form name="edit_ads_form" id="edit_ads_form" method="post" action="<?= base_url('admin/ads-edit/'.$ad->id); ?>" enctype="multipart/form-data">
										<!-- <input type="hidden" class="validate" id="all_ads_enquiry_id" name="all_ads_enquiry_id" value="45" required="required"> -->
										<!-- <input type="hidden" class="validate" id="ad_enquiry_photo_old" name="ad_enquiry_photo_old" value="54524soumam.png" required="required"> -->
										<input type="hidden" value="0" name="ad_total_days" id="ad_total_days" class="validate">
										<input type="hidden" value="70" name="ad_cost_per_day" id="ad_cost_per_day" class="validate">
										<input type="hidden" value="0" name="ad_total_cost" id="ad_total_cost" class="validate">
										<input type="hidden" value="<?= set_value('ad_width',@$ad->photo_width);?>" name="ad_width" id="ad_width" class="validate">
                    					<input type="hidden" value="<?= set_value('ad_height',@$ad->photo_height);?>" name="ad_height" id="ad_height" class="validate">
										<!-- <input type="hidden" value="" name="path" id="path" class="validate"> -->
										<ul>
											<li>
												<!--FILED START-->
												<div class="row">
													<div class="col-md-12">
														<div class="form-group">
															<select name="user_id" required="required" class="form-control" id="user_id">
																<option value="">Choose a user *</option>
																<?php
																if($users && $users != null){
																	foreach ($users as $key => $value) {
																?>
																<option value="<?= $value->user_id;?>" <?= ($value->user_id == $ad->u_id)? 'selected': '';?> ><?= $value->name;?></option>
																
																<?php
																	}
																}
																?>
															</select>
														</div>
                            							<span class="text-danger"><?= form_error("user_id")?form_error("user_id"):'' ;?></span>
													</div>
												</div>
												<!--FILED END-->
												<!--FILED START-->
												<div class="row">
													<div class="col-md-12">
														<div class="form-group ca-sh-user">
															<select name="ads_price_id" required="required" class="form-control" id="adposi">
																<option value="">Choose Ads Position *</option>
																<?php
																if($adsPrices && $adsPrices != null){
																	foreach ($adsPrices as $key => $value) {
																?>
																<option myTag="<?= $value->cost;?>" value="<?= $value->id;?>" <?= ($value->id == $ad->ads_price_id)? 'selected': '';?> ><?= $value->name;?> (<?= $value->cost;?> /per day)</option>
																<?php
																	}
																}
																?>
															</select> <a href="<?= base_url('admin/ads-price');?>" class="frmtip" target="_blank">Pricing details</a>
														</div>
                            							<span class="text-danger"><?= form_error("ads_price_id")?form_error("ads_price_id"):'' ;?></span>
													</div>
												</div>
												<!--FILED END-->
												<!--FILED START-->
												<div class="row">
													<div class="col-md-12">
														<div class="form-group">
															<input type="text" id="stdate" name="ad_start_date" value="<?= date('m/d/Y',strtotime($ad->ad_start_date)); ?>" class="form-control" placeholder="Ad start date (MM/DD/YYYY)" required>
														</div>
                            							<span class="text-danger"><?= form_error("ad_start_date")?form_error("ad_start_date"):'' ;?></span>
													</div>
												</div>
												<!--FILED END-->
												<!--FILED START-->
												<div class="row">
													<div class="col-md-12">
														<div class="form-group">
															<input type="text" id="endate" name="ad_end_date" value="<?= date('m/d/Y',strtotime($ad->ad_end_date)); ?>" class="form-control" placeholder="Ad end date (MM/DD/YYYY)" required>
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
															<input type="file" name="ad_photo" class="form-control">
														</div>
                            							<span class="text-danger"><?= form_error("ad_photo")?form_error("ad_photo"):'' ;?></span>
													</div>
												</div>
												<!--FILED END-->
												<!--FILED START-->
												<div class="row">
													<div class="col-md-12">
														<div class="form-group">
															<textarea id="ad_link" name="ad_link" class="form-control" placeholder="Advertisement External link" required><?= $ad->ad_link; ?></textarea>
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
																		<h5 class="ad-tdays"><?= $ad->ad_total_days; ?></h5>
																	</div>
																</li>
																<li>
																	<div> <span>Cost Per Day</span>
																		<h5>$<b class="ad-pocost"><?= $ad->ad_cost_per_day; ?></b></h5>
																	</div>
																</li>
																<li>
																	<div> <span>Tax</span>
																		<h5>$0</h5>
																	</div>
																</li>
																<li>
																	<div> <span>Total Cost</span>
																		<h5>$<b class="ad-tcost"><?= $ad->ad_total_cost; ?></b></h5>
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
												<button type="submit" name="edit_ad_submit" class="btn btn-primary">Publish this Ad</button>
											</div>
											<div class="col-md-12"> <a href="<?= base_url('admin/dashboard');?>" class="skip">Go to User Dashboard >></a>
											</div>
										</div>
										<!--FILED END-->
									</form>
									<div class="ud-notes">
										<p><b>Notes:</b> Hi, Before submit your <b>Ads</b> please check the <b>available date</b> because previous Ads running in same date. Kindly check this manually</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>
	</div>
</section>
<!-- END -->
	