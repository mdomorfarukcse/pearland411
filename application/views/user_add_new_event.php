	<section class=" login-reg">
		<div class="container">
			<div class="row">
				<div class="login-main add-list">
					<div class="log-bor">&nbsp;</div> <span class="steps">Add new Event</span>
					<div class="log">

					<?php if($this->session->flashdata('message')){ ?>
	            		<div class="log-suc" ><p><?= $this->session->flashdata('message') ?></p></div>
	            	<?php } ?>	

						<div class="login add-list-off">
							<h4>Create Event</h4>

							<?= form_open_multipart('user/add_user_event',['class'=>'event_form','id'=>'event_form']); ?>	

								<ul>
									<li>
										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<input type="text" name="event_name" required="required" class="form-control" placeholder="Event Name*">
												</div>
												<span class="text-danger"><?= form_error("event_name")?form_error("event_name"):'' ;?></span>
											</div>
										</div>
										<!--FILED END-->

										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<input type="text" name="event_address" required="required" class="form-control" placeholder="Address*">
												</div>
												<span class="text-danger"><?= form_error("event_address")?form_error("event_address"):'' ;?></span>
											</div>
										</div>
										<!--FILED END-->

										<!--FILED START-->
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<input type="date" name="event_start_date" required="required" class="form-control" placeholder="Date*">
												</div>
												<span class="text-danger"><?= form_error("event_start_date")?form_error("event_start_date"):'' ;?></span>
											</div>

											<div class="col-md-6">
												<div class="form-group">
													<input type="text" name="event_time" required="required" class="form-control" placeholder="Time*">
												</div>
												<span class="text-danger"><?= form_error("event_time")?form_error("event_time"):'' ;?></span>
											</div>
										</div>
										<!--FILED END-->

										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<textarea class="form-control" required="required" name="event_description" id="event_description" placeholder="Event Details"></textarea>
												</div>
												<span class="text-danger"><?= form_error("event_description")?form_error("event_description"):'' ;?></span>
											</div>
										</div>
										<!--FILED END-->

										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<textarea class="form-control" name="event_map" placeholder="Google map location"></textarea>
													<!-- INPUT TOOL TIP -->
													<div class="inp-ttip"> <b>Iframe code from google</b>
														Copy and paste Google iframe code here.</div>
													<!-- END INPUT TOOL TIP -->
												</div>
												<span class="text-danger"><?= form_error("event_map")?form_error("event_map"):'' ;?></span>
											</div>
										</div>
										<!--FILED END-->

										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label>Choose banner image</label>
													<input type="file" name="event_image" required="required" class="form-control">
												</div>
											</div>
										</div>
										<!--FILED END-->

										<!--FILED START-->
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<input type="text" name="event_contact_name" required="required" class="form-control" placeholder="Contact person*">
												</div>
												<span class="text-danger"><?= form_error("event_contact_name")?form_error("event_contact_name"):'' ;?></span>
											</div>

											<div class="col-md-6">
												<div class="form-group">
													<input type="text" name="event_mobile" required="required" class="form-control" placeholder="Contact phone number*">
												</div>
												<span class="text-danger"><?= form_error("event_mobile")?form_error("event_mobile"):'' ;?></span>
											</div>
										</div>
										<!--FILED END-->

										<!--FILED START-->
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<input type="email" name="event_email" required="required" class="form-control" placeholder="Contact Email Id *">
												</div>
												<span class="text-danger"><?= form_error("event_email")?form_error("event_email"):'' ;?></span>
											</div>

											<div class="col-md-6">
												<div class="form-group">
													<input type="text" name="event_website" class="form-control" placeholder="Event Website">
												</div>
												<span class="text-danger"><?= form_error("event_website")?form_error("event_website"):'' ;?></span>
											</div>
										</div>
										<!--FILED END-->

										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div>
													<div class="chbox">
														<input type="checkbox" id="isenquiry" name="isenquiry" checked="" value='1'>
														<label for="isenquiry">Enquiry or Registration form enable</label>
													</div>
												</div>
											</div>
										</div>
										<!--FILED END-->

									</li>
								</ul>

								<!--FILED START-->
								<div class="row">
									<div class="col-md-12">
										<button type="submit" name="event_submit" class="btn btn-primary">Submit</button>
									</div>
									<div class="col-md-12"> 
										<a href="<?= base_url('user/user-events'); ?>" class="skip">
											Go to Event dashboard>>	
										</a>
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
	

<script src="<?= base_url('assets/ckeditor/ckeditor.js');?>"></script>
<script>
	CKEDITOR.replace('event_description');
</script>