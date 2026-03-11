<!-- START -->
<section>
	<div class="list-det-fix">
		<div class="container">
			<div class="row">
				<div class="list-det-fix-inn">
					<div class="list-fix-pro">
						<img src="<?= (empty($listing->listing_image)) ? 'https://new.pearland411.com/assets/images/pearland-logo.png' :  base_url($listing->listing_image); ?>" alt="">
					</div>
					<div class="list-fix-tit">
						<h3><?= $listing->listing_name; ?></h3>
						<?php if ($listing->display_address == 1) { ?>
							<p><b>Address:</b> <?= $listing->listing_address; ?></p>
						<?php } ?>
					</div>
					<div class="list-fix-btn"> <span data-toggle="modal" data-target="#quote" class="pulse">Send an enquiry</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<!-- END -->
<!-- START -->
<section>
	<div class="list-bann">
		<img src="<?= (empty($listing->cover_image)) ? 'https://new.pearland411.com/assets/images/pearland-logo.png' :  base_url($listing->cover_image); ?>" alt="">
	</div>
</section>
<!-- END -->
<!-- START -->
<!--LISTING DETAILS-->
<section class=" pg-list-1">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="pg-list-1-pro">
					<img src="<?= (empty($listing->listing_image)) ? 'https://new.pearland411.com/assets/images/pearland-logo.png' :  base_url($listing->listing_image); ?>" alt=""> <span class="stat"><i class="material-icons">verified_user</i></span>
				</div>
				<div class="pg-list-1-left">
					<h3><?= $listing->listing_name; ?></h3>
					<div class="list-rat-all"> <b><?= isset($listing->average_rating) ? number_format($listing->average_rating, 1) : '5.0'; ?></b>
						<label class="rat">
							<?php
							$rating = isset($listing->average_rating) ? (int)$listing->average_rating : 5;
							for ($i = 1; $i <= 5; $i++) {
								if ($i <= $rating) {
									echo '<i class="material-icons">star</i>';
								} else {
									echo '<i class="material-icons">star_border</i>';
								}
							}
							?>
						</label> <span><?= isset($listing->total_reviews) ? $listing->total_reviews : '0'; ?> Reviews</span>
					</div>
					<?php if ($listing->display_address == 1) { ?>
						<p><b>Address:</b> <?= $listing->listing_address; ?></p>
					<?php } ?>
					<div class="list-number pag-p1-phone">
						<ul>
							<?php if ($listing->display_phone == 1) { ?>
								<a href="Tel:<?= $listing->listing_mobile; ?>">
									<li class="ic-php"><?= $listing->listing_mobile; ?></li>
								</a>
							<?php } ?>
							<a href="mailto:<?= $listing->listing_email; ?>">
								<li class="ic-mai"><?= $listing->listing_email; ?></li>
							</a>
							<a target="_blank" href="<?= $listing->listing_website; ?>">
								<li class="ic-web"><?= $listing->listing_website; ?></li>
							</a>
						</ul>
					</div>
				</div>
				<div class="list-ban-btn">
					<ul>
						<?php if ($listing->display_phone == '1') { ?>
							<li> <a href="tel:<?= $listing->listing_mobile; ?>" class="cta cta-call">Call now</a>
							</li>
						<?php } ?>
						<li><span class="cta cta-like ldelik Animatedheartfunc385 " data-for="1" data-section="1" data-num="325" data-item="37" data-id='385'>
								<b class="like-content385">1</b> Likes</span>
						</li>
						<li> <a href="https://wa.me/<?= $listing->display_phone ?>" class="cta cta-rev">WhatsApp</a>
						</li>
						<li> <span data-toggle="modal" data-target="#quote" class="pulse cta cta-get">Get quote</span>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</section>
<section class=" list-pg-bg">
	<div class="container">
		<div class="row">
			<div class="com-padd">
				<div id="ld-abo" class="list-pg-lt list-page-com-p">
					<!--                        -->
					<!--LISTING DETAILS: LEFT PART 1-->
					<div class="pglist-bg pglist-p-com">
						<div class="pglist-p-com-ti">
							<h3><span>About</span> <?= $listing->listing_name; ?></h3>
						</div>
						<div class="list-pg-inn-sp list-pg-inn-abo">
							<div class="share-btn">
								<ul>
									<li>
										<a target="_blank" href="" class="so-fb">
											<img src="<?= base_url() ?>assets/images/social/3.png" alt="Share on Facebook" title="Share on Facebook">
										</a>
									</li>
									<li>
										<a target="_blank" href="" class="so-tw">
											<img src="<?= base_url() ?>assets/images/social/2.png" alt="Share On Twitter" title="Share On Twitter">
										</a>
									</li>
									<li>
										<a target="_blank" href="" class="so-wa">
											<img src="<?= base_url() ?>assets/images/social/6.png" alt="Share on WhatsApp" title="Share on WhatsApp">
										</a>
									</li>
									<li>
										<a target="_blank" href="" class="so-li">
											<img src="<?= base_url() ?>assets/images/social/1.png" alt="Share on LinkedIn" title="Share on LinkedIn">
										</a>
									</li>
									<li>
										<a target="_blank" href="h" class="so-pi">
											<img src="<?= base_url() ?>assets/images/social/9.png" alt="Share on Pinterest" title="Share on Pinterest">
										</a>
									</li>
								</ul>
							</div>
							<p>
								<?= $listing->listing_description; ?>
							</p>
						</div>
					</div>
					<!--                            -->
					<!--END LISTING DETAILS: LEFT PART 1-->
					<!--LISTING DETAILS: LEFT PART 2-->
					<div id="ld-ser" class="pglist-bg pglist-p-com">
						<div class="pglist-p-com-ti">
							<h3><span>Services</span> Offered</h3>
						</div>
						<div class="list-pg-inn-sp">
							<div class="row pg-list-ser-area">
								<ul>
									<?php
									if (!empty($listing->service_offered)) {
										$service_offered = explode(',', $listing->service_offered);
										foreach ($service_offered as $service_name) {
									?>
											<li><span><?= trim($service_name) ?></span></li>
									<?php
										}
									}
									?>
								</ul>
							</div>
						</div>
					</div>
					<!--END LISTING DETAILS: LEFT PART 2-->
					<!--START SERVICE AREAS-->
					<div id="ld-ser" class="pglist-bg pglist-p-com">
						<div class="pglist-p-com-ti">
							<h3><span>Service</span> Areas</h3>
						</div>
						<div class="list-pg-inn-sp">
							<div class="row pg-list-ser-area">
								<ul>
									<?php
									if (!empty($listing->service_locations)) {
										$service_area = explode(',', $listing->service_locations);
										foreach ($service_area as $area) {
									?>
											<li><span><?= trim($area) ?></span></li>
									<?php
										}
									}
									?>
								</ul>
							</div>
						</div>
					</div>
					<!--END SERVICE AREAS-->
					<!--LISTING DETAILS: LEFT PART 3-->
					<div id="ld-gal" class="pglist-bg pglist-p-com">
						<div class="pglist-p-com-ti">
							<h3><span>Photo</span> Gallery</h3>
						</div>
						<div class="list-pg-inn-sp">
							<div id="demo" class="carousel slide" data-ride="carousel">
								<!-- Indicators -->
								<ul class="carousel-indicators">
									<?php
									$listing_images = !empty($listing->listing_image) ? explode(',', $listing->listing_image) : [];
									$carousel_count = max(count($listing_images), 1); // At least 1 slide
									for ($i = 0; $i < $carousel_count; $i++) {
									?>
										<li data-target="#demo" data-slide-to="<?= $i ?>" class="<?= $i == 0 ? 'active' : '' ?>"></li>
									<?php } ?>
								</ul>
								<!-- The slideshow -->
								<div class="carousel-inner">
									<?php
									if (!empty($listing_images) && count($listing_images) > 0) {
										foreach ($listing_images as $index => $image) {
									?>
											<div class="carousel-item <?= $index == 0 ? 'active' : '' ?>">
												<img src="<?= base_url(trim($image)); ?>" alt="<?= $listing->listing_name; ?>">
											</div>
										<?php
										}
									} else {
										?>
										<div class="carousel-item active">
											<img src="<?= base_url('assets/images/no_photo.jpg'); ?>" alt="<?= $listing->listing_name; ?>">
										</div>
									<?php } ?>
								</div>
								<!-- Left and right controls -->
								<a class="carousel-control-prev" href="#demo" data-slide="prev"> <span class="carousel-control-prev-icon"></span>
								</a>
								<a class="carousel-control-next" href="#demo" data-slide="next"> <span class="carousel-control-next-icon"></span>
								</a>
							</div>
						</div>
					</div>
					<!--END LISTING DETAILS: LEFT PART 3-->
					<!--LISTING DETAILS: LEFT PART 4-->
					<div id="ld-off" class="pglist-bg pglist-off-last pglist-p-com">
						<div class="pglist-p-com-ti">
							<h3><span>Special</span> Offers</h3>
						</div>
						<div class="list-pg-inn-sp">
							<?php if (!empty($listing->special_offers)) { ?>
								<div class="home-list-pop">
									<!--LISTINGS IMAGE-->
									<div class="col-md-3">
										<img src="<?= (empty($listing->listing_image)) ? 'https://new.pearland411.com/assets/images/pearland-logo.png' :  base_url($listing->listing_image); ?>" alt="">
									</div>
									<!--LISTINGS: CONTENT-->
									<div class="col-md-9 home-list-pop-desc inn-list-pop-desc list-room-deta"><a href="#!">
											<h3><?= isset($listing->offer_title) ? $listing->offer_title : 'Special Offer'; ?></h3>
										</a>
										<p><?= $listing->special_offers; ?></p>
										<span class="home-list-pop-rat list-rom-pric"><?= isset($listing->offer_price) ? '$' . $listing->offer_price : ''; ?></span>
										<div class="list-enqu-btn">
											<ul>
												<li> <a target="_blank" href="#!">View more</a>
												</li>
												<li><a href="#!" data-toggle="modal" data-target="#quote">Send enquiry</a>
												</li>
											</ul>
										</div>
									</div>
								</div>
							<?php } else { ?>
								<p>No special offers available at this time.</p>
							<?php } ?>
						</div>
					</div>
					<!--360 DEGREE VIEW-->
					<?php if (!empty($listing->virtual_tour_link)) { ?>
						<div class="pglist-bg pglist-p-com" id="ld-360">
							<div class="pglist-p-com-ti">
								<h3><span>360 </span> Degree View</h3>
							</div>
							<div class="list-pg-inn-sp list-360">
								<iframe src="<?= $listing->virtual_tour_link; ?>" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
							</div>
						</div>
					<?php } ?>
					<!--END 360 DEGREE MAP: LEFT PART 8-->
					<!--LISTING DETAILS: LEFT PART 6-->
					<!--LISTING DETAILS: LEFT PART 6-->
					<div class="pglist-bg pglist-p-com" style="" id="ld-rew">
						<div class="pglist-p-com-ti">
							<h3><span>Write Your</span> Reviews</h3>
						</div>
						<div class="list-pg-inn-sp">
							<div class="list-pg-write-rev">
								<form class="col" name="review_form" id="review_form" method="post">
									<input type="hidden" class="form-control" name="listing_id" value="385">
									<input type="hidden" class="form-control" name="listing_user_id" value="325">
									<input name="review_user_id" value="37" type="hidden">
									<p>Writing great reviews may help others discover the places that are just apt for them. Here are a few tips to write a good review:</p>
									<div id="review_success" style="text-align:center;display: none;color: green;">Thanks for your Review !! Your Review Is Successful!!</div>
									<div id="review_fail" style="text-align:center;display: none;color: red;">Something Went Wrong!!!</div>
									<div class="row">
										<div>
											<fieldset class="rating">
												<input type="radio" id="star5" name="price_rating" class="price_rating" value="5" />
												<label class="full" for="star5" title="Awesome"></label>
												<input type="radio" id="star4" name="price_rating" class="price_rating" value="4" />
												<label class="full" for="star4" title="Excellent"></label>
												<input type="radio" checked="checked" id="star3" class="price_rating" name="price_rating" value="3" />
												<label class="full" for="star3" title="Good"></label>
												<input type="radio" id="star2" name="price_rating" class="price_rating" value="2" />
												<label class="full" for="star2" title="Average"></label>
												<input type="radio" id="star1" name="price_rating" class="price_rating" value="1" />
												<label class="full" for="star1" title="Poor"></label>
												<input type="radio" id="star0" name="price_rating" class="price_rating" value="0" />
												<label class="" for="star0" title="Very Poor"></label>
											</fieldset>
											<div id="star-value">3 Star</div>
										</div>
									</div>
									<div class="row">
										<div class="input-field col s6">
											<?php if ($logged_id != NULL) { ?>
												<input id="review_name" readonly name="review_name" type="text" value="<?= isset($user->name) ? $user->name : ''; ?>">
											<?php } else { ?>
												<input id="review_name" name="review_name" type="text" value="" placeholder="Enter name">
											<?php } ?>
										</div>
										<div class="input-field col s6">
											<?php if ($logged_id != NULL) { ?>
												<input id="review_mobile" readonly name="review_mobile" type="text" onkeypress="return isNumber(event)" placeholder="Mobile number" value="<?= isset($user->mobile_number) ? $user->mobile_number : ''; ?>">
											<?php } else { ?>
												<input id="review_mobile" name="review_mobile" type="text" onkeypress="return isNumber(event)" placeholder="Mobile number" value="">
											<?php } ?>
										</div>
									</div>
									<div class="row">
										<div class="input-field col s6">
											<?php if ($logged_id != NULL) { ?>
												<input id="review_email" readonly name="review_email" type="email" placeholder="Email Id" value="<?= isset($user->email_id) ? $user->email_id : ''; ?>">
											<?php } else { ?>
												<input id="review_email" name="review_email" type="email" placeholder="Email Id" value="">
											<?php } ?>
										</div>
										<div class="input-field col s6">
											<input id="review_city" name="review_city" placeholder="City" type="text">
										</div>
									</div>
									<div class="row">
										<div class="input-field col s12">
											<textarea id="review_message" placeholder="Write review" name="review_message"></textarea>
										</div>
									</div>
									<div class="row">
										<div class="input-field col s12">
											<input type="submit" id="review_submit" name="review_submit" value="Submit Review">
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
					<!--END LISTING DETAILS: LEFT PART 6-->
					<!--END LISTING DETAILS: LEFT PART 6-->
					<!--LISTING DETAILS: LEFT PART 5-->
					<!--LISTING DETAILS: LEFT PART 5-->
					<div class="pglist-p3 pglist-bg pglist-p-com" id="ld-rev">
						<div class="pglist-p-com-ti">
							<h3><span>User</span> Reviews</h3>
						</div>
						<div class="list-pg-inn-sp">
							<div class="lp-ur-all">
								<div class="lp-ur-all-left">
									<div class="lp-ur-all-left-1">
										<div class="lp-ur-all-left-11">Excellent</div>
										<div class="lp-ur-all-left-12">
											<div class="lp-ur-all-left-13"></div>
										</div>
									</div>
									<div class="lp-ur-all-left-1">
										<div class="lp-ur-all-left-11">Good</div>
										<div class="lp-ur-all-left-12">
											<div class="lp-ur-all-left-13 lp-ur-all-left-Good"></div>
										</div>
									</div>
									<div class="lp-ur-all-left-1">
										<div class="lp-ur-all-left-11">Satisfactory</div>
										<div class="lp-ur-all-left-12">
											<div class="lp-ur-all-left-13 lp-ur-all-left-satis"></div>
										</div>
									</div>
									<div class="lp-ur-all-left-1">
										<div class="lp-ur-all-left-11">Below Average</div>
										<div class="lp-ur-all-left-12">
											<div class="lp-ur-all-left-13 lp-ur-all-left-below"></div>
										</div>
									</div>
									<div class="lp-ur-all-left-1">
										<div class="lp-ur-all-left-11">Below Average</div>
										<div class="lp-ur-all-left-12">
											<div class="lp-ur-all-left-13 lp-ur-all-left-poor"></div>
										</div>
									</div>
								</div>
								<div class="lp-ur-all-right">
									<h5>Overall Ratings</h5>
									<p>
										<label class="rat"> <i class="material-icons">star</i>
											<i class="material-icons">star</i>
											<i class="material-icons">star</i>
											<i class="material-icons">star</i>
											<i class="material-icons">star</i>
										</label> <span>based on 1 reviews</span>
									</p>
								</div>
							</div>
							<div class="lp-ur-all-rat">
								<h5>Reviews</h5>
								<ul>
									<li>
										<div class="lr-user-wr-img">
											<img src="https://new.pearland411.com/assets/images/pearland-logo.png" alt="">
										</div>
										<div class="lr-user-wr-con">
											<h6>Sample User</h6>
											<label class="rat"> <i class="material-icons">star</i>
												<i class="material-icons">star</i>
												<i class="material-icons">star</i>
												<i class="material-icons">star</i>
												<i class="material-icons">star</i>
											</label> <span class="lr-revi-date">07, Mar 2021</span>
											<p>verified_userRolexo Villas is well-known to all as a premier builder of villas and apartments. Even though they have various departments they stay united in giving the customers the best service. I really had a good service right from on time delivery, quality of work, perfection in work completion. The relationship does not end in just in hand over but they stand strong in all tuff times for the commitment given. After 3+ years of handover they addressed the compound wall cracks which expanded and in a week condition. They still respond to any queries / faults on time and track it to closure.</p>
										</div>
									</li>
								</ul>
							</div>
						</div>
					</div>
					<!--END LISTING DETAILS: LEFT PART 5-->
					<!--ADS-->
					<div class="ban-ati-com ads-det-page"> <a href=""><span>Ad</span>
							<img src="<?= base_url() ?>assets/images/ads/3.png"></a>
					</div>
					<!--ADS-->
					<!--RELATED PREMIUM LISTINGS-->
					<div class="list-det-rel-pre">
						<h2>Related listings:</h2>
						<ul>
							<?php if (!empty($RelatedListing)) { ?>
								<?php foreach ($RelatedListing as $RelatedList) { ?>
									<li>
										<div class="land-pack-grid">
											<div class="land-pack-grid-img">
												<img src="<?= (empty($RelatedList->listing_image)) ? 'https://new.pearland411.com/assets/images/pearland-logo.png' : base_url($RelatedList->listing_image); ?>" alt="">
											</div>
											<div class="land-pack-grid-text">
												<h4><?= $RelatedList->listing_name ?></h4>
												<div class="list-rat-all"> <b></b>
												</div>
											</div>
											<a target="_blank" href="<?= base_url('user/listing-details/' . $RelatedList->listing_slug); ?>" class="land-pack-grid-btn"></a>
										</div>
									</li>
								<?php } ?>
							<?php } else { ?>
								<li>
									<div class="land-pack-grid-text">
										<h4>No related listings found</h4>
									</div>
								</li>
							<?php } ?>
						</ul>
					</div>
					<!--RELATED PREMIUM LISTINGS-->
				</div>
				<div class="list-pg-rt">
					<!--LISTING DETAILS: LEFT PART 9-->
					<div class="list-rhs-form pglist-bg pglist-p-com">
						<div class="quote-pop">
							<h3><span>Get</span> Quote</h3>
							<div id="detail_enq_success" class="log" style="display: none;">
								<p>Your Enquiry Is Submitted Successfully</p>
							</div>
							<div id="detail_enq_same" class="log" style="display: none;">
								<p>You cannot make enquiry on your own listing</p>
							</div>
							<div id="detail_enq_fail" class="log" style="display: none;">
								<p>Something Went Wrong!!!</p>
							</div>
							<form method="post" action="<?= base_url('user/add-enquiry/' . 'listing_details/' . $listing->listing_slug); ?>" name="detail_enquiry_form" id="detail_enquiry_form">
								<input type="hidden" class="form-control" name="listing_id" value="<?= $listing->listing_id; ?>" placeholder="" required>
								<input type="hidden" class="form-control" name="listing_user_id" value="<?= $listing->user_id; ?>" placeholder="" required>
								<?php if ($logged_id != NULL) { ?>
									<input type="hidden" class="form-control" name="enquiry_sender_id" value="<?= $logged_id; ?>" placeholder="" required>
								<?php } ?>
								<input type="hidden" class="form-control" name="enquiry_source" value="Website" placeholder="" required>
								<div class="form-group ic-user">
									<?php if ($logged_id != NULL) { ?>
										<input type="text" name="enquiry_name" value="<?= isset($user->name) ? $user->name : ''; ?>" required="required" class="form-control" placeholder="Enter name*">
									<?php } else { ?>
										<input type="text" name="enquiry_name" value="" required="required" class="form-control" placeholder="Enter name*">
									<?php } ?>
								</div>
								<div class="form-group ic-eml">
									<?php if ($logged_id != NULL) { ?>
										<input type="email" class="form-control" placeholder="Enter email*" required="required" value="<?= isset($user->email_id) ? $user->email_id : ''; ?>" name="enquiry_email" pattern="^[\w]{1,}[\w.+-]{0,}@[\w-]{2,}([.][a-zA-Z]{2,}|[.][\w-]{2,}[.][a-zA-Z]{2,})$" title="Invalid email address">
									<?php } else { ?>
										<input type="email" class="form-control" placeholder="Enter email*" required="required" value="" name="enquiry_email" pattern="^[\w]{1,}[\w.+-]{0,}@[\w-]{2,}([.][a-zA-Z]{2,}|[.][\w-]{2,}[.][a-zA-Z]{2,})$" title="Invalid email address">
									<?php } ?>
								</div>
								<div class="form-group ic-pho">
									<?php if ($logged_id != NULL) { ?>
										<input type="text" class="form-control" value="<?= isset($user->mobile_number) ? $user->mobile_number : ''; ?>" name="enquiry_mobile" placeholder="Enter mobile number *" pattern="[0-9]{10,15}" title="Please enter a valid phone number" required>
									<?php } else { ?>
										<input type="text" class="form-control" value="" name="enquiry_mobile" placeholder="Enter mobile number *" pattern="[0-9]{10,15}" title="Please enter a valid phone number" required>
									<?php } ?>
								</div>
								<div class="form-group">
									<textarea class="form-control" rows="3" name="enquiry_message" placeholder="Enter your query or message"></textarea>
								</div>
								<input type="hidden" id="source">
								<button type="submit" id="detail_enquiry_submit" name="enquiry_submit" class="btn btn-primary">Submit</button>
							</form>
						</div>
					</div>
					<!--END LISTING DETAILS: LEFT PART 9-->
					<!--LISTING DETAILS: LEFT PART 7-->
					<div class="lide-guar pglist-bg pglist-p-com">
						<div class="pglist-p-com-ti pglist-p-com-ti-right">
							<h3><span>Claim</span> Listing</h3>
						</div>
						<div class="list-pg-inn-sp">
							<div class="list-pg-guar">
								<ul>
									<li>
										<div class="list-pg-guar-img">
											<img src="<?= base_url() ?>assets/images/icon/g2.png" alt="" />
										</div>
										<h4>Claim this business</h4>
										<span data-toggle="modal" data-target="#claim" class="clim-edit">Suggest an edit</span>
									</li>
								</ul>
							</div>
						</div>
					</div>
					<!--END LISTING DETAILS: LEFT PART 7-->
					<!--LISTING DETAILS: COMPANY BADGE-->
					<div class="ld-rhs-pro pglist-bg pglist-p-com">
						<div class="lis-comp-badg">
							<div class="s1">
								<div> <span class="by">Business profile</span>
									<img class="proi" src="<?= !empty($listing->listing_image) ? base_url($listing->listing_image) : 'https://new.pearland411.com/assets/images/pearland-logo.png'; ?>" alt="">
									<h4><?= isset($listing->listing_name) ? $listing->listing_name : 'Business Name'; ?></h4>
									<p>Address: <?= isset($listing->listing_address) ? $listing->listing_address : 'Business Address'; ?></p>
									<ul>
										<li>
											<a href="https://www.facebook.com/53themes" target="_blank">
												<img src="https://bizbookdirectorytemplate.com/images/social/3.png">
											</a>
										</li>
										<li>
											<a href="https://twitter.com/53themes" target="_blank">
												<img src="https://bizbookdirectorytemplate.com/images/social/2.png">
											</a>
										</li>
										<li>
											<a href="https://www.youtube.com/channel/UC3wN3O2GXTt7ZZniIoMZumg" target="_blank">
												<img src="https://bizbookdirectorytemplate.com/images/social/5.png">
											</a>
										</li>
										<li>
											<a href="#" target="_blank">
												<img src="https://bizbookdirectorytemplate.com/images/social/6.png">
											</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="s2"> <a target="_blank" href="company-profile.html" class="use-fol">View profile</a>
								<a target="_blank" href="company-profile.html#home_enquiry_form">Get in touch with us</a>
							</div>
						</div>
					</div>
					<!--END LISTING DETAILS: COMPANY BADGE-->
					<!--LISTING DETAILS: LOCATION MAP-->
					<div class="pglist-p3 pglist-bg pglist-p-com">
						<div class="pglist-p-com-ti pglist-p-com-ti-right">
							<h3><span>Our</span> Location</h3>
						</div>
						<div class="list-pg-inn-sp">
							<div class="list-pg-map">
								<?php if (!empty($listing->map_latitude) && !empty($listing->map_longitude)) { ?>
									<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3000!2d<?= $listing->map_longitude ?>!3d<?= $listing->map_latitude ?>!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s<?= $listing->map_latitude ?>%2C<?= $listing->map_longitude ?>!5e0!3m2!1sen!2sin" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
								<?php } else { ?>
									<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d100940.17087601054!2d-122.50781157529548!3d37.75767917396271!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x80859a6d00690021%3A0x4a501367f076adff!2sSan%20Francisco%2C%20CA%2C%20USA!5e0!3m2!1sen!2sin!4v1615094456570!5m2!1sen!2sin" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
								<?php } ?>
							</div>
						</div>
					</div>
					<!--LISTING DETAILS: LEFT PART 9-->
					<div class="pglist-p3 pglist-bg pglist-p-com">
						<div class="pglist-p-com-ti pglist-p-com-ti-right">
							<h3><span>Company</span> Info</h3>
						</div>
						<div class="list-pg-inn-sp">
							<div class="list-pg-oth-info">
								<ul>
									<?php if (!empty($listing->listing_mobile)) { ?>
										<li>Contact number <span><?= $listing->listing_mobile; ?></span></li>
									<?php } ?>
									<?php if (!empty($listing->listing_mobile)) { ?>
										<li>WhatsApp <span><?= $listing->listing_mobile; ?></span></li>
									<?php } ?>
									<?php if (!empty($listing->listing_email)) { ?>
										<li>Email id <span><?= $listing->listing_email; ?></span></li>
									<?php } ?>
									<?php if (!empty($listing->contact_person)) { ?>
										<li>Contact name <span><?= $listing->contact_person; ?></span></li>
									<?php } ?>
									<?php if (!empty($listing->listing_website)) { ?>
										<li>Website <span><?= $listing->listing_website; ?></span></li>
									<?php } ?>
									<?php if (!empty($listing->business_hours)) { ?>
										<li>Business Hours <span><?= $listing->business_hours; ?></span></li>
									<?php } ?>
									<?php if (!empty($listing->year_established)) { ?>
										<li>Established <span><?= $listing->year_established; ?></span></li>
									<?php } ?>
								</ul>
							</div>
						</div>
					</div>
					<!--END LISTING DETAILS: LEFT PART 9-->
					<!--LISTING DETAILS: LEFT PART 7-->
					<div class="ld-rhs-pro pglist-bg pglist-p-com">
						<div class="lis-pro-badg">
							<div>
								<?php if (!empty($listing->average_rating)) { ?>
									<span class="rat" alt="User rating"><?= number_format($listing->average_rating, 1); ?></span>
								<?php } ?>
								<span class="by">Created by</span>
								<img src="<?= !empty($listing->user_profile_image) ? base_url($listing->user_profile_image) : 'https://new.pearland411.com/assets/images/pearland-logo.png'; ?>" alt="">
								<h4><?= !empty($listing->user_name) ? $listing->user_name : 'Business Owner'; ?></h4>
								<p>Member since <?= !empty($listing->user_created) ? date('M Y', strtotime($listing->user_created)) : 'N/A'; ?></p>
							</div> <a href="profile.html" class="fclick" target="_blank">&nbsp;</a>
						</div>
					</div>
					<!--END LISTING DETAILS: LEFT PART 7-->
					<!--LISTING DETAILS: LEFT PART 10-->
					<!--LISTING DETAILS: WHO LIKED THIS-->
					<?php if (!empty($listing_likes) && count($listing_likes) > 0) { ?>
						<div class="list-mig-like">
							<div class="list-ri-peo-like">
								<h3>Who all are like this</h3>
								<ul>
									<?php foreach ($listing_likes as $like) { ?>
										<li>
											<a href="profile.html" target="_blank">
												<img src="<?= !empty($like->profile_image) ? base_url($like->profile_image) : 'https://new.pearland411.com/assets/images/pearland-logo.png'; ?>" alt="<?= $like->name; ?>">
											</a>
										</li>
									<?php } ?>
								</ul>
							</div>
						</div>
					<?php } ?>
					<!--END LISTING DETAILS: LEFT PART 10-->
					<!--ADS-->
					<div class="ban-ati-com ads-det-page"> <a href=""><span>Ad</span><img
								src="<?= base_url() ?>assets/images/ads/59040boat-728x90.png"></a>
					</div>
					<!--ADS-->
				</div>
			</div>
		</div>
	</div>
</section>