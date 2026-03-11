<section>

	<div class="top_slider">
		<div class="container-fluid px-0">
			<div class="row no-gutters">
				<div class="col-md-12">
					<div id="demo" class="carousel slide cate-sli caro-home" data-ride="carousel">
						<div class="carousel-inner">
							

<!-- 

								<div class="carousel-item ">
									<img src="<?//= base_url() ?>assets/images/slider/2.jpg" alt="Los Angeles">
									<a href="demo.html" target="_blank"></a>
								</div> -->
								<?php
								$i = 0;
								foreach ($allSliders as $slider) {
									$i++;
									?>

									<div class="carousel-item <?= ($i == 1) ? 'active' : '' ?>">

										<img src="<?= base_url($slider->slider_photo); ?>" alt="Slider Image">

										<a href="<?= base_url($slider->slider_photo);?>" target="_blank"></a>

									</div>

									<?php	

								}

								?>		

							</div>						

						</div>
						<a class="carousel-control-prev" href="#demo" data-slide="prev"> <span class="carousel-control-prev-icon"></span>
						</a>
						<a class="carousel-control-next" href="#demo" data-slide="next"> <span class="carousel-control-next-icon"></span>
						</a>
					</div>

				</div>

			</div>

		</div>

	</div>
</section>
<style>

.caro-home {

	margin-top: 4% !important;

}

.top_slider .container-fluid{
}

.hom-head{

	padding: 0px !important;

	margin-bottom: 0px !important;

}

.hom-top {
	transition: all 0.5s ease;
	background: #1e68ac;
	box-shadow: none;
}

.top-ser {
	/*display: none;*/
}

.dmact .top-ser {
	display: block;
}

.caro-home {
	margin-top: 90px;
	float: left;
	width: 100%;
}

.carousel-item:before {
	background: none;
}
</style>

<!-- START -->
<section>
	<div class="str">
		<div class="container">
			<div class="row">

				<div class="col-md-12">

					<div class="ban-ati-com ads-all-list"> 

						<a href="http://www.pearland411.com/graphic/banner.jpg">

							<span>Ad</span>

							<img src="http://www.pearland411.com/graphic/banner.jpg" height="100" alt="">

						</a>

					</div>

				</div>

			</div>

		</div>

	</div>

</section>

<!-- End -->

<!-- START -->
<section>
	<div class="str">
		<div class="container-fluid">
			<div class="row">
				<div class="home-tit">

					<h2><span>Top Service Provider</span> in city</h2>

					<p>lacinia viverra lectus. Fusce imperdiet ullamcorper metus eu fringilla.</p>

				</div>

				<div class="col-md-9">

					<div class="ho-popu-bod">
						<!--Top Branding Hotels-->
						<div class="col-md-4">
							<div class="hot-page2-hom-pre-head">

								<h4>Top Branding                                        

									<span>Business</span></h4>

								</div>
								<div class="hot-page2-hom-pre">

									<ul>

										<!--LISTINGS-->
										<?php 
										if(count($topBusiness) > 0){
											foreach ($topBusiness as $value) {
												?>	
												<li>

													<div class="hot-page2-hom-pre-1">

														<img src="<?= (empty($value->profile_image)) ? base_url('assets/images/no_photo.jpg') :  base_url($value->profile_image);?>" alt="">

													</div>

													<div class="hot-page2-hom-pre-2">

														<h5><?= $value->listing_name; ?></h5>

														<span><?= $value->listing_address; ?></span>

													</div>

													<a href="<?= base_url('user/listing-preview/'.$value->listing_id);?>" class="fclick"></a>

												</li>
											<?php }}else { ?>
												<div class="eve-box">
													<h5 style='text-align: center'> No Listings Available</h5>
												</div>
												<?php } ?>											<!--LISTINGS-->

											</ul>
										</div>
									</div>
									<div class="col-md-4">
										<div class="hot-page2-hom-pre-head">
											<h4>Top Branding                                        <span>Digital Products</span></h4>
										</div>
										<div class="hot-page2-hom-pre">
											<ul>
												<!--LISTINGS-->
												<li>
													<div class="hot-page2-hom-pre-1">
														<img src="<?= base_url() ?>assets/images/services/6.jpeg" alt="">
													</div>
													<div class="hot-page2-hom-pre-2">
														<h5>BizBookBusiness Directory Template</h5>
														<span>No:2, 4th Avenue,  Newyork, USA, Near to Airport</span>
													</div>
													<a href="listing-details.html" class="fclick"></a>
												</li>
												<!--LISTINGS-->
											</ul>
										</div>
									</div>
									<div class="col-md-4">
										<div class="hot-page2-hom-pre-head">
											<h4>Top Branding                                        <span>Hospitals</span></h4>
										</div>
										<div class="hot-page2-hom-pre">
											<ul>
												<!--LISTINGS-->
												<li>
													<div class="hot-page2-hom-pre-1">
														<img src="<?= base_url() ?>assets/images/services/11.jpg" alt="">
													</div>
													<div class="hot-page2-hom-pre-2">
														<h5>William Chil care Hospital</h5>
														<span>No:2, 4th Avenue,  Newyork, USA, Near to Airport</span>
													</div>
													<a href="listing-details.html" class="fclick"></a>
												</li>
												<!--LISTINGS-->

											</ul>
										</div>
									</div>
									<!--End Top Branding Hotels-->
								</div>

							</div>	

							<div class="col-md-3">

								<div class="filt-com lhs-ads">

									<ul>
										<div class="ban-ati-com ads-det-page">
	                  	<a href=""><span>Ad</span><img src="https://bizbookdirectorytemplate.com/images/ads/59040boat-728x90.png"></a>
										</div>
										<li>
											<div class="ads-box">
												<a href=""> <span>Ad</span>
													<img src="<?= base_url() ?>assets/images/ads/ads1.jpg" alt="">
												</a>
											</div>
										</li>
										<li>
											<a href="post-your-ads.html.html" class="btn btn-info btn-large"> Click Here to Advertise with Us! </a>
										</li>
										<li>
											<div class="ads-box">
												<a href=""> <span>Ad</span>
													<img src="<?= base_url() ?>assets/images/ads/ads2.jpg" alt="">
												</a>
											</div>
										</li>
										<div class="ban-ati-com ads-det-page">
            					<a href=""><span>Ad</span><img src="https://bizbookdirectorytemplate.com/images/ads/59040boat-728x90.png"></a>
										</div>
									</ul>



								</div>



							</div>
						</div>
					</div>
				</div>
			</section>
			<!-- END -->

			<!-- START -->
			<section>
				<div class="str">
					<div class="container-fluid">
						<div class="row">
							<div class="col-md-9">
								<div class="home-tit">
									<h2><span>Featured Services</span> in your city</h2>
									<p>lacinia viverra lectus. Fusce imperdiet ullamcorper metus eu fringilla.</p>
								</div>
								<div class="land-pack">
									<ul>

										<?php

										if (count($categories) > 0) {

											foreach ($categories as $value) {

												?>

												<li>

													<div class="land-pack-grid">

														<div class="land-pack-grid-img">

															<img src="<?= base_url($value->cat_image); ?>" alt="">

														</div>

														<div class="land-pack-grid-text">

															<h4><?= $value->cat_name;?> <span class="dir-ho-cat">Show All (<?= $value->totalValue;?>)</span></h4>

														</div> <a href="<?= base_url('user/all-listing/'.$value->cat_id); ?>" class="land-pack-grid-btn">View all listings</a>

													</div>

												</li>							

												<?php

											}

										}

										?>

									</ul> 

									<?php if (count($categories) > 7){?>

										<a href="<?= base_url('user/all-category');?>" class="more">View all services</a>

									<?php } ?>

								</div>

							</div>

							<div class="col-md-3">

								<div class="filt-com lhs-ads">

									<ul>
										<li>
											<div class="ads-box">
												<a href=""> <span>Ad</span>
													<img src="<?= base_url() ?>assets/images/ads/ads2.jpg" alt="">
												</a>
											</div>
										</li>
										<li>
											<div class="ads-box">
												<a href=""> <span>Ad</span>
													<img src="<?= base_url() ?>assets/images/ads/ads2.jpg" alt="">
												</a>
											</div>
										</li>
										<li>
											<a href="post-your-ads.html.html" class="btn btn-info btn-large"> Click Here to Advertise with Us! </a>
										</li>
										<div class="ban-ati-com ads-det-page">
	                  						<a href=""><span>Ad</span><img src="https://bizbookdirectorytemplate.com/images/ads/59040boat-728x90.png"></a>
										</div>
									</ul>



								</div>



							</div>
						</div>
					</div>
				</div>
			</section>
			<!-- END -->
			<!-- START -->
			<section>
				<div class="str">
					<div class="container">
						<div class="row">
							<div class="home-tit">
								<!--<h2><span>Explore City</span> Category                        </h2>-->
								<!--<p>lacinia viverra lectus. Fusce imperdiet ullamcorper metus eu fringilla.</p>-->

								<h2><span>Explore Products</span></h2>
							</div>
							<div class="home-city">
								<ul>

									<?php

									if(count($products) > 0 ){

										foreach($products as $product){

											?>							

											<li>

												<div class="hcity">

													<div>

														<?php

														$images = json_decode($product->gallery_image);

														$i = 0;

														$data = array();

														foreach($images as $image){

															$data[$i] = $image;

															$i++;

														}					

														?>

														<img src="<?= base_url($data[0]); ?>" alt="">

													</div>

													<div>

														<img src="<?= base_url() ?>assets/images/services/1.jpg" alt="">

														<h4><?= $product->product_name;?></h4>

														<div class="list-rat-all"> <b>3.0</b>

															<label class="rat"> <i class="material-icons">star</i>

																<i class="material-icons">star</i>

																<i class="material-icons">star</i>

																<i class="material-icons ratstar">star</i>

																<i class="material-icons ratstar">star</i>

															</label> <span>2 Reviews</span>

														</div>

														<p>09 Listings</p>

													</div> <a href="<?= base_url('user/product_details/'.$product->product_id);?>" class="fclick">&nbsp;</a>

												</div>

											</li>



											<?php

										}
									}

									?>
									
								</ul>
							</div>
						</div>
					</div>
				</div>
			</section>
			<!-- END -->



			<!-- START -->
			<section>
				<div class="hom-mpop-ser">
					<div class="container">
						<div class="hom-mpop-main">
							<div class="home-tit">
								<h2>
									<span>Featured Services</span> in your city                        </h2>
									<p>lacinia viverra lectus. Fusce imperdiet ullamcorper metus eu fringilla.</p>
								</div>
								<div class="col-md-6">
									<div>
										<!--POPULAR LISTINGS-->
										<div class="hom-mpop">
											<!--POPULAR LISTINGS IMAGE-->
											<div class="col-md-3">
												<img src="<?= base_url() ?>assets/images/listings/1.jpg" alt="" />
											</div>
											<!--POPULAR LISTINGS: CONTENT-->
											<div class="col-md-9">
												<h3>Titan Wedding Hall</h3>
												<h4>Wedding halls</h4>
												<p>Titan wedding happ, North street, No 2, Newyork, USA</p> <span class="rat-sh">5.0</span>
											</div> <a href="listing-details.html">&nbsp;</a>
										</div>
										<!--POPULAR LISTINGS-->
										<div class="hom-mpop">
											<!--POPULAR LISTINGS IMAGE-->
											<div class="col-md-3">
												<img src="<?= base_url() ?>assets/images/listings/2.jpg" alt="" />
											</div>
											<!--POPULAR LISTINGS: CONTENT-->
											<div class="col-md-9">
												<h3>Gill Automobiles and Services</h3>
												<h4>Automobiles</h4>
												<p>Titan wedding happ, North street, No 2, Newyork, USA</p>
											</div>
										</div>
										<!--POPULAR LISTINGS-->
										<div class="hom-mpop">
											<!--POPULAR LISTINGS IMAGE-->
											<div class="col-md-3">
												<img src="<?= base_url() ?>assets/images/listings/3.jpeg" alt="" />
											</div>
											<!--POPULAR LISTINGS: CONTENT-->
											<div class="col-md-9">
												<h3>Rolexo Villas in California</h3>
												<h4>Real Estate</h4>
												<p>28800 Orchard Lake Road, Suite 180 Farmington Hills, U.S.A.</p> <span class="rat-sh">5.0</span>
											</div><a href="listing-details.html">&nbsp;</a>
										</div>
										<!--POPULAR LISTINGS-->
										<div class="hom-mpop">
											<!--POPULAR LISTINGS IMAGE-->
											<div class="col-md-3">
												<img src="<?= base_url() ?>assets/images/listings/4.jpg" alt="" />
											</div>
											<!--POPULAR LISTINGS: CONTENT-->
											<div class="col-md-9">
												<h3>The Spa at Mandarin Oriental</h3>
												<h4>Hospitals</h4>
												<p>No:2, 4th Avenue, Newyork, USA, Near to Airport</p> <span class="rat-sh">4.0</span>
											</div><a href="listing-details.html">&nbsp;</a>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div>
										<!--POPULAR LISTINGS-->
										<div class="hom-mpop">
											<!--POPULAR LISTINGS IMAGE-->
											<div class="col-md-3">
												<img src="<?= base_url() ?>assets/images/listings/5.jpeg" alt="" />
											</div>
											<!--POPULAR LISTINGS: CONTENT-->
											<div class="col-md-9">
												<h3>IPM Business Software</h3>
												<h4>Digital Products</h4>
												<p>No:2, 4th Avenue, Newyork, USA, Near to Airport</p>
											</div><a href="listing-details.html">&nbsp;</a>
										</div>
										<!--POPULAR LISTINGS-->
										<div class="hom-mpop">
											<!--POPULAR LISTINGS IMAGE-->
											<div class="col-md-3">
												<img src="<?= base_url() ?>assets/images/listings/6.jpg" alt="" />
											</div>
											<!--POPULAR LISTINGS: CONTENT-->
											<div class="col-md-9">
												<h3>Rachel Taj Hotels</h3>
												<h4>Hotels And Resorts</h4>
												<p>No:2, 4th Avenue, Newyork, USA, Near to Airport</p> <span class="rat-sh">3.0</span>
											</div><a href="listing-details.html">&nbsp;</a>
										</div>
										<!--POPULAR LISTINGS-->
										<div class="hom-mpop">
											<!--POPULAR LISTINGS IMAGE-->
											<div class="col-md-3">
												<img src="<?= base_url() ?>assets/images/listings/7.jpg" alt="" />
											</div>
											<!--POPULAR LISTINGS: CONTENT-->
											<div class="col-md-9">
												<h3>Joseph Multispeciality Hospital</h3>
												<h4>Hospitals</h4>
												<p>No:2, 4th Avenue, Newyork, USA, Near to Airport</p>
											</div> <a href="listing-details.html">&nbsp;</a>
										</div>
										<!--POPULAR LISTINGS-->
										<div class="hom-mpop">
											<!--POPULAR LISTINGS IMAGE-->
											<div class="col-md-3">
												<img src="<?= base_url() ?>assets/images/listings/8.jpeg" alt="" />
											</div>
											<!--POPULAR LISTINGS: CONTENT-->
											<div class="col-md-9">
												<h3>Green Healthcare Hospital</h3>
												<h4>Hospitals</h4>
												<p>No:2, 4th Avenue, Newyork, USA, Near to Airport</p> <span class="rat-sh">3.0</span>
											</div> <a href="listing-details.html">&nbsp;</a>
										</div>
									</div>
								</div>
							</div>
							<div class="hlead-coll">
								<div class="col-md-6">
									<div class="hom-cre-acc-left">
										<h3>What service do you need?                                <span>BizBook Directory</span>
										</h3>
										<p>Tell us more about your requirements so that we can connect you to the right service provider.</p>
										<ul>
											<li>
												<img src="<?= base_url() ?>assets/images/icon/blog.png" alt="">
												<div>
													<h5>Tell us more about your requirements</h5>
													<p>HI Imagine you have made your presence online through a local online directory, but your competitors have..</p>
												</div>
											</li>
											<li>
												<img src="<?= base_url() ?>assets/images/icon/shield.png" alt="">
												<div>
													<h5>We connect with right service provider</h5>
													<p>Advertising your business to area specific has many advantages. For local businessmen, it is an opportunity..</p>
												</div>
											</li>
											<li>
												<img src="<?= base_url() ?>assets/images/icon/general.png" alt="">
												<div>
													<h5>Happy with our service</h5>
													<p>Your local business too needs brand management and image making. As you know the local market..</p>
												</div>
											</li>
										</ul>
									</div>
								</div>
								<div class="col-md-6">
									<div class="hom-col-req">
										<div class="log-bor">&nbsp;</div> <span class="udb-inst">Fill the form</span>
										<h4>What you looking for?</h4>
										<div id="home_enq_success" class="log" style="display: none;">
											<p>Your Enquiry Is Submitted Successfully!!!</p>
										</div>
										<div id="home_enq_fail" class="log" style="display: none;">
											<p>Something Went Wrong!!!</p>
										</div>
										<div id="home_enq_same" class="log" style="display: none;">
											<p>You cannot make enquiry on your own listing</p>
										</div>
										<form name="home_enquiry_form" id="home_enquiry_form" method="post" enctype="multipart/form-data">
											<input type="hidden" class="form-control" name="listing_id" value="0" placeholder="" required>
											<input type="hidden" class="form-control" name="listing_user_id" value="0" placeholder="" required>
											<input type="hidden" class="form-control" name="enquiry_sender_id" value="" placeholder="" required>
											<input type="hidden" class="form-control" name="enquiry_source" value="Website" placeholder="" required>
											<div class="form-group">
												<input type="text" name="enquiry_name" value="" required="required" class="form-control" placeholder="Enter name*">
											</div>
											<div class="form-group">
												<input type="email" class="form-control" placeholder="Enter email*" required="required" value="" name="enquiry_email" pattern="^[\w]{1,}[\w.+-]{0,}@[\w-]{2,}([.][a-zA-Z]{2,}|[.][\w-]{2,}[.][a-zA-Z]{2,})$" title="Invalid email address">
											</div>
											<div class="form-group">
												<input type="text" class="form-control" value="" name="enquiry_mobile" placeholder="Enter mobile number *" pattern="[7-9]{1}[0-9]{9}" title="Phone number starting with 7-9 and remaing 9 digit with 0-9" required="">
											</div>
											<div class="form-group">
												<select name="enquiry_category" id="enquiry_category" class="form-control">
													<option value="">Select Category</option>
													<option value="19">Wedding halls</option>
													<option value="18">Hotel & Food</option>
													<option value="17">Pet shop</option>
													<option value="16">Digital Products</option>
													<option value="15">Spa and Facial</option>
													<option value="10">Real Estate</option>
													<option value="8">Sports</option>
													<option value="7">Education</option>
													<option value="6">Electricals</option>
													<option value="5">Automobiles</option>
													<option value="3">Transportation</option>
													<option value="2">Hospitals</option>
													<option value="1">Hotels And Resorts</option>
												</select>
											</div>
											<div class="form-group">
												<textarea class="form-control" rows="3" name="enquiry_message" placeholder="Enter your query or message"></textarea>
											</div>
											<input type="hidden" id="source">
											<button type="submit" id="home_enquiry_submit" name="home_enquiry_submit" class="btn btn-primary">Submit Requirements</button>
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
				</section>
				<!-- END -->

				<!-- START -->
				<section>
					<div class="str count">
						<div class="container">
							<div class="row">
								<div class="home-tit">
									<h2><span>Feature Events</span> in city                        </h2>
									<p>lacinia viverra lectus. Fusce imperdiet ullamcorper metus eu fringilla.</p>
								</div>
								<div class="hom-event">

									<?php

									if(count($events) > 0){

										?>

										<div class="hom-eve-com hom-eve-lhs">
											<div class="hom-eve-lhs-1 col-md-4">

												<div class="eve-box">

													<div>

														<a href="<?= base_url('user/event-details/'.$events[0]->event_id);?>">

															<img src="<?= base_url($events[0]->event_image); ?>" alt=""> 

															<span><b><?= date('d, M Y',strtotime($events[0]->event_start_date));?></b></span>

														</a>

													</div>

													<div>

														<h4>

															<a href="event-details.html"><?= $events[0]->event_name;?></a>

														</h4>

														<span class="addr"><?= $events[0]->event_address;?></span>

														<span class="pho"><?= $events[0]->event_mobile;?></span>

													</div>

								<!-- <div>

										<div class="auth">

											<img src="<?//= base_url() ?>assets/images/user/1.png" alt=""> <b>Hosted by</b>

											<br>

											<h4>Directory Finder</h4>

											<a target="_blank" href="profile.html" class="fclick"></a>

										</div>

									</div> -->

								</div>

							</div>
							<?php if(count($events) > 1){ ?>
								<div class="hom-eve-lhs-1 col-md-4">
									<div class="eve-box">
										<div>
											<a href="<?= base_url('user/event-details/'.$events[1]->event_id);?>">
												<img src="<?= base_url($events[1]->event_image); ?>" alt=""> 
												<span><b><?= date('d, M Y',strtotime($events[1]->event_start_date));?></b></span>
											</a>
										</div>
										<div>
											<h4>
												<a href="event-details.html"><?= $events[1]->event_name;?></a>
											</h4>
											<span class="addr"><?= $events[1]->event_address;?></span>
											<span class="pho"><?= $events[1]->event_mobile;?></span>
										</div>

									<!--<div>
										<div class="auth">
											<img src="<?//= base_url() ?>assets/images/user/2.jpeg" alt=""> <b>Hosted by</b>
											<br>
											<h4>Chris moris</h4>
											<a target="_blank" href="profile.html" class="fclick"></a>
										</div>
									</div> -->
								</div>
							</div>

							<div class="hom-eve-lhs-2 col-md-4">
								<ul>
									<?php 
									if(count($events) > 2){ 
										for($i = 2 ; $i< count($events); $i++){ 
											?>
											<li>
												<div class="eve-box-list">
													<img src="<?= base_url($events[$i]->event_image); ?>" alt="">
													<h4 title="<?= $events[$i]->event_name; ?>"><?= $events[$i]->event_name; ?></h4>
													<p><?= $events[$i]->event_address;?></p> 
													<span><?= date('M d',strtotime($events[$i]->event_start_date));?></span>
													<a href="<?= base_url('user/event-details/'.$events[$i]->event_id);?>" class="fclick"></a>
												</div>
											</li>
										<?php } } ?>
									</ul>
								</div>
								<?php } ?>						</div>

							<?php } ?>

						</div>
						<div class="how-wrks">
							<div class="home-tit">
								<h2><span>How It Works</span></h2>
								<p>Explore some of the best tips from around the world from our
									<br>partners and friends.lacinia viverra lectus.</p>
								</div>
								<div class="how-wrks-inn">
									<ul>
										<li>
											<div> <span>1</span>
												<img src="<?= base_url() ?>assets/images/icon/how1.png" alt="">
												<h4>Create an account</h4>
												<p>Fusce imperdiet ullamcorper metus eu fringilla. from around the world from our partners and friends.</p>
											</div>
										</li>
										<li>
											<div> <span>2</span>
												<img src="<?= base_url() ?>assets/images/icon/how2.png" alt="">
												<h4>Add your business</h4>
												<p>Fusce imperdiet ullamcorper metus eu fringilla. from around the world from our partners and friends.</p>
											</div>
										</li>
										<li>
											<div> <span>3</span>
												<img src="<?= base_url() ?>assets/images/icon/how3.png" alt="">
												<h4>Get more leads</h4>
												<p>Fusce imperdiet ullamcorper metus eu fringilla. from around the world from our partners and friends.</p>
											</div>
										</li>
										<li>
											<div> <span>4</span>
												<img src="<?= base_url() ?>assets/images/icon/how4.png" alt="">
												<h4>Archive goles</h4>
												<p>Fusce imperdiet ullamcorper metus eu fringilla. from around the world from our partners and friends.</p>
											</div>
										</li>
									</ul>
								</div>
							</div>

						</div>
					</div>
				</div>
			</section>
			<!-- END -->
			<!-- START -->
			<section>
				<div class="hom-ads">
					<div class="container">
						<div class="row">
							<div class="filt-com lhs-ads">
								<div class="ads-box">
									<a href=""> <span>Ad</span>
										<img src="<?= base_url() ?>assets/images/ads/ads2.jpg" alt="">
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
			<!-- END -->
			<!-- START -->
			<div class="ani-quo">
				<div class="ani-q1">
					<h4>What you looking for?</h4>
					<p>We connect you to service experts.</p> <span>Get experts</span>
				</div>
				<div class="ani-q2">
					<img src="<?= base_url() ?>assets/images/quote.png" alt="">
				</div>
			</div>
			<!-- END -->
			<!-- START -->
			<span class="btn-ser-need-ani">Help & Support</span>
			<div class="ani-quo-form"> <i class="material-icons ani-req-clo">close</i>
				<div class="tit">
					<h3>What service do you need? <span>BizBook will help you</span></h3>
				</div>
				<div class="hom-col-req">
					<div id="home_slide_enq_success" class="log" style="display: none;">
						<p>Your Enquiry Is Submitted Successfully!!!</p>
					</div>
					<div id="home_slide_enq_fail" class="log" style="display: none;">
						<p>Something Went Wrong!!!</p>
					</div>
					<div id="home_slide_enq_same" class="log" style="display: none;">
						<p>You cannot make enquiry on your own listing</p>
					</div>
					<form name="home_slide_enquiry_form" id="home_slide_enquiry_form" method="post" enctype="multipart/form-data">
						<input type="hidden" class="form-control" name="listing_id" value="0" placeholder="" required>
						<input type="hidden" class="form-control" name="listing_user_id" value="0" placeholder="" required>
						<input type="hidden" class="form-control" name="enquiry_sender_id" value="" placeholder="" required>
						<input type="hidden" class="form-control" name="enquiry_source" value="Website" placeholder="" required>
						<div class="form-group">
							<input type="text" name="enquiry_name" value="" required="required" class="form-control" placeholder="Enter name*">
						</div>
						<div class="form-group">
							<input type="email" class="form-control" placeholder="Enter email*" required="required" value="" name="enquiry_email" pattern="^[\w]{1,}[\w.+-]{0,}@[\w-]{2,}([.][a-zA-Z]{2,}|[.][\w-]{2,}[.][a-zA-Z]{2,})$" title="Invalid email address">
						</div>
						<div class="form-group">
							<input type="text" class="form-control" value="" name="enquiry_mobile" placeholder="Enter mobile number *" pattern="[7-9]{1}[0-9]{9}" title="Phone number starting with 7-9 and remaing 9 digit with 0-9" required="">
						</div>
						<div class="form-group">
							<select name="enquiry_category" id="enquiry_category" class="form-control">
								<option value="">Select Category</option>
								<option value="19">Wedding halls</option>
								<option value="18">Hotel & Food</option>
								<option value="17">Pet shop</option>
								<option value="16">Digital Products</option>
								<option value="15">Spa and Facial</option>
								<option value="10">Real Estate</option>
								<option value="8">Sports</option>
								<option value="7">Education</option>
								<option value="6">Electricals</option>
								<option value="5">Automobiles</option>
								<option value="3">Transportation</option>
								<option value="2">Hospitals</option>
								<option value="1">Hotels And Resorts</option>
							</select>
						</div>
						<div class="form-group">
							<textarea class="form-control" rows="3" name="enquiry_message" placeholder="Enter your query or message"></textarea>
						</div>
						<input type="hidden" id="source">
						<button type="submit" id="home_slide_enquiry_submit" name="home_slide_enquiry_submit" class="btn btn-primary">Submit Requirements</button>
					</form>
				</div>
			</div>
	<!-- END -->