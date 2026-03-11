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
		<img src="<?= (empty($listing->cover_image)) ? '/assets/images/llisting_details_bg.jpg' :  base_url($listing->cover_image); ?>" alt="">
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
					<img src="<?= (empty($listing->listing_image)) ? '/assets/images/fallback_profile.svg' :  base_url($listing->listing_image); ?>" alt=""> <span class="stat"><i class="material-icons">verified_user</i></span>
				</div>
				<div class="pg-list-1-left">
					<h3><?= $listing->listing_name; ?></h3>
					<div class="list-rat-all" id="main-rating-display">
						<?php
						// Calculate actual review count and average rating as fallback
						$actual_total_reviews = isset($listing->total_reviews) && $listing->total_reviews > 0 ? $listing->total_reviews : 0;

						// If no total_reviews from database, calculate from review_stats
						if ($actual_total_reviews == 0 && isset($review_stats) && is_array($review_stats)) {
							$actual_total_reviews = array_sum($review_stats);
						}

						// Calculate weighted average if we don't have it from database
						$actual_average_rating = isset($listing->average_rating) && $listing->average_rating > 0 ? $listing->average_rating : 0;
						if ($actual_average_rating == 0 && isset($review_stats) && is_array($review_stats) && $actual_total_reviews > 0) {
							$weighted_sum = 0;
							for ($i = 1; $i <= 5; $i++) {
								$count = isset($review_stats[$i]) ? $review_stats[$i] : 0;
								$weighted_sum += ($i * $count);
							}
							$actual_average_rating = $weighted_sum / $actual_total_reviews;
						}
						?>
						<b><?= number_format($actual_average_rating, 1); ?></b>
						<label class="rat">
							<?php
							$rating_display = (int)$actual_average_rating;
							for ($i = 1; $i <= 5; $i++) {
								if ($i <= $rating_display) {
									echo '<i class="material-icons">star</i>';
								} else {
									echo '<i class="material-icons">star_border</i>';
								}
							}
							?>
						</label>
						<span><?= $actual_total_reviews; ?> Review<?= ($actual_total_reviews != 1) ? 's' : ''; ?></span>
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
						<!-- <li><span class="cta cta-like like-button " data-listing-id="5408" id="like-btn-5408">
								<b class="like-count">0</b>
								<span class="like-text">Like</span>
							</span></li> -->
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
								<?php
								// Current page URL for sharing
								$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
								$current_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

								// Listing details for sharing
								$share_title = urlencode($listing->listing_name);
								$share_description = urlencode(substr(strip_tags($listing->listing_description), 0, 150) . '...');
								$share_image_url = !empty($listing->listing_image) ? $protocol . $_SERVER['HTTP_HOST'] . '/' . $listing->listing_image : $protocol . $_SERVER['HTTP_HOST'] . '/assets/images/pearland-logo.png';
								?>
								<ul>
									<li>
										<a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($current_url) ?>&quote=<?= $share_title ?>" class="so-fb" onclick="window.open(this.href, 'facebook-share-dialog', 'width=800,height=600'); return false;">
											<img src="<?= base_url() ?>assets/images/social/3.png" alt="Share on Facebook" title="Share on Facebook">
										</a>
									</li>
									<li>
										<a target="_blank" href="https://twitter.com/intent/tweet?url=<?= urlencode($current_url) ?>&text=<?= $share_title ?>&hashtags=pearland411,business" class="so-tw" onclick="window.open(this.href, 'twitter-share-dialog', 'width=800,height=600'); return false;">
											<img src="<?= base_url() ?>assets/images/social/2.png" alt="Share On Twitter" title="Share On Twitter">
										</a>
									</li>
									<li>
										<a target="_blank" href="https://wa.me/?text=<?= $share_title ?>%20-%20<?= urlencode($current_url) ?>" class="so-wa" onclick="window.open(this.href, 'whatsapp-share-dialog', 'width=800,height=600'); return false;">
											<img src="<?= base_url() ?>assets/images/social/6.png" alt="Share on WhatsApp" title="Share on WhatsApp">
										</a>
									</li>
									<li>
										<a target="_blank" href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($current_url) ?>&title=<?= $share_title ?>&summary=<?= $share_description ?>" class="so-li" onclick="window.open(this.href, 'linkedin-share-dialog', 'width=800,height=600'); return false;">
											<img src="<?= base_url() ?>assets/images/social/1.png" alt="Share on LinkedIn" title="Share on LinkedIn">
										</a>
									</li>
									<li>
										<a target="_blank" href="https://pinterest.com/pin/create/button/?url=<?= urlencode($current_url) ?>&media=<?= urlencode($share_image_url) ?>&description=<?= $share_title ?>" class="so-pi" onclick="window.open(this.href, 'pinterest-share-dialog', 'width=800,height=600'); return false;">
											<img src="<?= base_url() ?>assets/images/social/9.png" alt="Share on Pinterest" title="Share on Pinterest">
										</a>
									</li>
									<li>
										<a href="javascript:void(0)" onclick="copyToClipboard('<?= $current_url ?>')" class="so-link" title="Copy Link">
											<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" aria-hidden="false" role="img">
												<title>Copy link</title>
												<g fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
													<!-- link chain -->
													<path d="M10.7 13.3a3 3 0 0 0 4.24 0l2.12-2.12a3 3 0 0 0 0-4.24 3 3 0 0 0-4.24 0L10.7 9.1" />
													<path d="M13.3 10.7a3 3 0 0 0-4.24 0L6.94 13a3 3 0 0 0 0 4.24 3 3 0 0 0 4.24 0L13.3 14.9" />
													<!-- copy / clipboard overlay -->
													<rect x="3.5" y="3.5" width="7" height="9" rx="1" />
													<path d="M5.5 6.5h3" />
												</g>
											</svg>

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
									if (!empty($listing->service_offered) && trim($listing->service_offered) !== '') {
										$service_offered = explode(',', $listing->service_offered);
										foreach ($service_offered as $service_name) {
											$service_name = trim($service_name);
											if (!empty($service_name)) {
									?>
												<li><span><?= $service_name ?></span></li>
										<?php
											}
										}
									} else {
										?>
										<li><span>No services listed</span></li>
									<?php
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
									if (!empty($listing->service_locations) && trim($listing->service_locations) !== '') {
										$service_area = explode(',', $listing->service_locations);
										foreach ($service_area as $area) {
											$area = trim($area);
											if (!empty($area)) {
									?>
												<li><span><?= $area ?></span></li>
										<?php
											}
										}
									} else {
										?>
										<li><span>No service areas listed</span></li>
									<?php
									}
									?>
								</ul>
							</div>
						</div>
					</div>
					<!--END SERVICE AREAS-->
					<!--LISTING DETAILS: LEFT PART 3 - PHOTO GALLERY-->
					<?php
					// Check if gallery images exist
					$has_gallery_images = false;
					if (!empty($listing->listing_image_gallery)) {
						$gallery_check = json_decode($listing->listing_image_gallery, true);
						$has_gallery_images = ($gallery_check && is_array($gallery_check) && count($gallery_check) > 0);
					}

					// DEBUG: Show gallery rendering status
					if (isset($_GET['debug'])) {
						echo "<!-- PHOTO GALLERY RENDER DEBUG: ";
						echo "\nHas Gallery Images: " . ($has_gallery_images ? 'YES' : 'NO');
						echo "\nGallery Data: " . ($listing->listing_image_gallery ?? 'NULL');
						echo "-->";
					}

					if ($has_gallery_images) { ?>
						<div id="ld-gal" class="pglist-bg pglist-p-com">
							<div class="pglist-p-com-ti">
								<h3><span>Photo</span> Gallery</h3>
							</div>
							<div class="list-pg-inn-sp">
								<div id="demo" class="carousel slide" data-ride="carousel">
									<!-- Indicators -->
									<ul class="carousel-indicators">
										<?php
										// DEBUG: Show gallery data
										if (isset($_GET['debug'])) {
											echo "<!-- GALLERY DEBUG INFO:";
											echo "\nListing Image Gallery: " . ($listing->listing_image_gallery ?? 'NULL');
											if (!empty($listing->listing_image_gallery)) {
												$debug_gallery = json_decode($listing->listing_image_gallery, true);
												echo "\nGallery Images Count: " . (is_array($debug_gallery) ? count($debug_gallery) : 0);
												if (is_array($debug_gallery)) {
													foreach ($debug_gallery as $idx => $img) {
														echo "\nImage " . ($idx + 1) . ": " . $img;
													}
												}
											}
											echo "-->";
										}

										// Only show gallery images from listing_image_gallery field
										$gallery_images = [];
										if (!empty($listing->listing_image_gallery)) {
											$gallery_data = json_decode($listing->listing_image_gallery, true);
											if ($gallery_data && is_array($gallery_data)) {
												foreach ($gallery_data as $img) {
													// Gallery images are stored with full path like assets/images/listings/filename.jpg
													$gallery_images[] = $img;
												}
											}
										}

										$all_images = $gallery_images;
										$carousel_count = max(count($all_images), 1); // At least 1 slide
										for ($i = 0; $i < $carousel_count; $i++) {
										?>
											<li data-target="#demo" data-slide-to="<?= $i ?>" class="<?= $i == 0 ? 'active' : '' ?>"></li>
										<?php } ?>
									</ul>
									<!-- The slideshow -->
									<div class="carousel-inner">
										<?php
										if (!empty($all_images) && count($all_images) > 0) {
											foreach ($all_images as $index => $image) {
										?>
												<div class="carousel-item <?= $index == 0 ? 'active' : '' ?>">
													<img src="<?= base_url(trim($image)); ?>" alt="<?= $listing->listing_name; ?>">
												</div>
											<?php
											}
										} else {
											?>
											<div class="carousel-item active">
												<div style="text-align:center; padding:60px 20px; background:#f5f5f5; color:#666;">
													<i class="fa fa-image" style="font-size:48px; margin-bottom:15px;"></i>
													<h4>No Gallery Images</h4>
													<p>Gallery images will appear here once uploaded.</p>
												</div>
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
					<?php } ?>

					<!--LISTING DETAILS: VIDEO GALLERY SECTION-->
					<?php if (!empty($listing->listing_video)) {
						// DEBUG: Show video data
						if (isset($_GET['debug'])) {
							echo "<!-- VIDEO DEBUG INFO:";
							echo "\nListing Video: " . ($listing->listing_video ?? 'NULL');
							$debug_videos = array_filter(array_map('trim', explode(',', $listing->listing_video)));
							echo "\nVideo URLs Count: " . count($debug_videos);
							foreach ($debug_videos as $idx => $video) {
								echo "\nVideo " . ($idx + 1) . ": " . $video;
							}
							echo "-->";
						}
					?>
						<div id="ld-video" class="pglist-bg pglist-p-com">
							<div class="pglist-p-com-ti">
								<h3><span>Video</span> Gallery</h3>
							</div>
							<div class="list-pg-inn-sp">
								<div id="videoDemo" class="carousel slide" data-ride="carousel">
									<!-- Video indicators -->
									<ul class="carousel-indicators">
										<?php
										// Parse video URLs - could be comma separated for multiple videos
										$video_urls = array_filter(array_map('trim', explode(',', $listing->listing_video)));
										for ($i = 0; $i < count($video_urls); $i++) {
										?>
											<li data-target="#videoDemo" data-slide-to="<?= $i ?>" class="<?= $i == 0 ? 'active' : '' ?>"></li>
										<?php } ?>
									</ul>
									<!-- The video slideshow -->
									<div class="carousel-inner">
										<?php
										if (!empty($video_urls)) {
											foreach ($video_urls as $index => $video_url) {
												// Convert YouTube URL to embed format
												$embed_url = $video_url;
												if (strpos($video_url, 'youtube.com/watch?v=') !== false) {
													$video_id = str_replace('https://www.youtube.com/watch?v=', '', $video_url);
													// Remove any additional parameters
													$video_id = explode('&', $video_id)[0];
													$embed_url = "https://www.youtube.com/embed/" . $video_id;
												} elseif (strpos($video_url, 'youtu.be/') !== false) {
													$video_id = str_replace('https://youtu.be/', '', $video_url);
													$embed_url = "https://www.youtube.com/embed/" . $video_id;
												}
										?>
												<div class="carousel-item <?= $index == 0 ? 'active' : '' ?>">
													<div class="video-container" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden;">
														<iframe
															src="<?= $embed_url ?>"
															style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"
															frameborder="0"
															allowfullscreen>
														</iframe>
													</div>
												</div>
										<?php
											}
										}
										?>
									</div>
									<!-- Left and right controls for video -->
									<?php if (count($video_urls) > 1) { ?>
										<a class="carousel-control-prev" href="#videoDemo" data-slide="prev">
											<span class="carousel-control-prev-icon"></span>
										</a>
										<a class="carousel-control-next" href="#videoDemo" data-slide="next">
											<span class="carousel-control-next-icon"></span>
										</a>
									<?php } ?>
								</div>
							</div>
						</div>
						<!--END VIDEO GALLERY SECTION-->
					<?php } ?>


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
								<?php if ($logged_id == NULL): ?>
									<!-- Show login required message if user not logged in -->
									<div style="text-align:center;color: #856404;padding: 15px;background: #fff3cd;border: 1px solid #ffeaa7;border-radius: 5px;margin: 10px 0;">
										<p><strong>Please login to write a review.</strong></p>
										<a href="<?= base_url('user/login'); ?>" class="btn btn-primary">Login Now</a>
									</div>
								<?php else: ?>
									<!-- Show review form if user is logged in -->
									<form class="col" name="review_form" id="review_form" method="post" action="<?= base_url('user/submit_review'); ?>">
										<input type="hidden" name="listing_id" value="<?= $listing->listing_id; ?>">
										<input type="hidden" name="listing_user_id" value="<?= $listing->user_id; ?>">
										<input name="review_user_id" value="<?= $logged_id; ?>" type="hidden">

										<?php if ($this->session->flashdata('review_success')): ?>
											<div style="text-align:center;color: green;padding: 10px;background: #d4edda;border: 1px solid #c3e6cb;border-radius: 5px;margin: 10px 0;">
												<?= $this->session->flashdata('review_success'); ?>
											</div>
										<?php endif; ?>

										<p>Writing great reviews may help others discover the places that are just apt for them. Here are a few tips to write a good review:</p>
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
												</fieldset>
												<div id="star-value">3 Star</div>
											</div>
										</div>
										<div class="row review_fields">
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
								<?php endif; ?>
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
									<?php
									// Use the same calculated total_reviews from above
									$total_reviews = $actual_total_reviews;

									$rating_labels = [5 => 'Excellent', 4 => 'Very Good', 3 => 'Good', 2 => 'Fair', 1 => 'Poor'];
									$rating_classes = [5 => '', 4 => 'lp-ur-all-left-Good', 3 => 'lp-ur-all-left-satis', 2 => 'lp-ur-all-left-below', 1 => 'lp-ur-all-left-poor'];

									foreach ($rating_labels as $rating => $label) {
										$count = isset($review_stats[$rating]) ? $review_stats[$rating] : 0;
										$percentage = $total_reviews > 0 ? ($count / $total_reviews) * 100 : 0;
									?>
										<div class="lp-ur-all-left-1">
											<div class="lp-ur-all-left-11"><?= $label ?> (<?= $count ?>)</div>
											<div class="lp-ur-all-left-12">
												<div class="lp-ur-all-left-13 <?= $rating_classes[$rating] ?>" style="width: <?= $percentage ?>%;"></div>
											</div>
										</div>
									<?php } ?>
								</div>
								<div class="lp-ur-all-right">
									<h5>Overall Ratings</h5>
									<p id="overall-rating-display">
										<label class="rat">
											<?php
											$avg_rating_display = (int)$actual_average_rating;
											for ($i = 1; $i <= 5; $i++) {
												if ($i <= $avg_rating_display) {
													echo '<i class="material-icons">star</i>';
												} else {
													echo '<i class="material-icons">star_border</i>';
												}
											}
											?>
										</label> <span>based on <?= $total_reviews ?> review<?= $total_reviews != 1 ? 's' : '' ?></span>
									</p>
								</div>
							</div>
							<div class="lp-ur-all-rat" id="reviews-list">
								<h5>Reviews (<?= $total_reviews ?>)</h5>
								<ul>
									<?php if (!empty($listing_reviews)) { ?>
										<?php foreach ($listing_reviews as $review) { ?>
											<li>
												<div class="lr-user-wr-img">
													<img src="<?= !empty($review->profile_image) ? base_url($review->profile_image) : 'https://new.pearland411.com/assets/images/pearland-logo.png'; ?>" alt="<?= $review->reviewer_name; ?>">
												</div>
												<div class="lr-user-wr-con">
													<h6><?= $review->reviewer_name; ?></h6>
													<label class="rat">
														<?php for ($i = 1; $i <= 5; $i++) { ?>
															<?php if ($i <= $review->rating) { ?>
																<i class="material-icons">star</i>
															<?php } else { ?>
																<i class="material-icons">star_border</i>
															<?php } ?>
														<?php } ?>
													</label>
													<span class="lr-revi-date"><?= date('d M Y', strtotime($review->created_at)); ?></span>
													<?php if (!empty($review->review_message)) { ?>
														<p><?= nl2br(htmlspecialchars($review->review_message)); ?></p>
													<?php } ?>
													<?php if (!empty($review->reviewer_city)) { ?>
														<small style="color: #666; font-style: italic;">From: <?= $review->reviewer_city; ?></small>
													<?php } ?>
												</div>
											</li>
										<?php } ?>
									<?php } else { ?>
										<li id="no-reviews">
											<p style="text-align: center; color: #666; padding: 20px;">No reviews yet. Be the first to review this listing!</p>
										</li>
									<?php } ?>
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

							<?php if ($this->session->flashdata('message')): ?>
								<div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;">
									<?= $this->session->flashdata('message'); ?>
								</div>
							<?php endif; ?>

							<?php if ($this->session->flashdata('error_msg')): ?>
								<div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;">
									<?= $this->session->flashdata('error_msg'); ?>
								</div>
							<?php endif; ?>

							<div id="detail_enq_success" class="log" style="display: none;">
								<p>Your Enquiry Is Submitted Successfully</p>
							</div>
							<div id="detail_enq_same" class="log" style="display: none;">
								<p>You cannot make enquiry on your own listing</p>
							</div>
							<div id="detail_enq_fail" class="log" style="display: none;">
								<p>Something Went Wrong!!!</p>
							</div>
							<form method="post" action="<?= base_url('user/add-enquiry/listing_details/' . $listing->listing_slug); ?>" name="detail_enquiry_form" id="detail_enquiry_form">
								<!-- Hidden fields for enquiry data -->
								<input type="hidden" name="listing_id" value="<?= $listing->listing_id; ?>">
								<input type="hidden" name="event_id" value="">
								<input type="hidden" name="blog_id" value="">
								<input type="hidden" name="product_id" value="">
								<input type="hidden" name="receiving_user_id" value="<?= $listing->user_id; ?>">
								<input type="hidden" name="enquiry_sender_id" value="<?= isset($logged_id) ? $logged_id : ''; ?>">
								<input type="hidden" name="enquiry_source" value="<?= $listing->listing_name; ?>">
								<input type="hidden" name="enquiry_type" value="listing">
								<?php $disabled = empty($logged_id) ? 'disabled' : ''; ?>
								<div class="form-group ic-user">
									<input type="text" name="enquiry_name" value="<?= isset($user->name) ? $user->name : ''; ?>" required="required" class="form-control" placeholder="Enter name*" <?= $disabled; ?>>
								</div>
								<div class="form-group ic-eml">
									<input type="email" class="form-control" placeholder="Enter email*" required="required" value="<?= isset($user->email_id) ? $user->email_id : ''; ?>" name="enquiry_email" pattern="^[\w]{1,}[\w.+-]{0,}@[\w-]{2,}([.][a-zA-Z]{2,}|[.][\w-]{2,}[.][a-zA-Z]{2,})$" title="Invalid email address" <?= $disabled; ?>>
								</div>
								<div class="form-group ic-pho">
									<input type="text" class="form-control" value="<?= isset($user->mobile_number) ? $user->mobile_number : ''; ?>" name="enquiry_mobile" placeholder="Enter mobile number *" pattern="[0-9]{10,15}" title="Please enter a valid phone number" required <?= $disabled; ?>>
								</div>
								<div class="form-group">
									<textarea class="form-control" rows="3" name="enquiry_message" placeholder="Enter your query or message" <?= $disabled; ?>></textarea>
								</div>
								<?php if (!empty($logged_id)): ?>
									<button type="submit" id="detail_enquiry_submit" name="enquiry_submit" class="btn btn-primary">Submit</button>
								<?php else: ?>
									<a href="<?= base_url('/login'); ?>"><button type="button" class="btn btn-primary" style="background:#f44336; border:none;">Please Log In First</button></a>
								<?php endif; ?>
							</form>
						</div>
					</div>
					<!--END LISTING DETAILS: LEFT PART 9-->
					<!--LISTING DETAILS: LEFT PART 7-->
					<!-- <div class="lide-guar pglist-bg pglist-p-com">
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
					</div> -->
					<!--END LISTING DETAILS: LEFT PART 7-->
					<!--LISTING DETAILS: COMPANY BADGE-->
					<div class="ld-rhs-pro pglist-bg pglist-p-com">
						<div class="lis-comp-badg">
							<div class="s1">
								<div> <span class="by">Business profile</span>
									<img class="proi" src="<?= !empty($listing->listing_image) ? base_url($listing->listing_image) : '/assets/images/fallback_profile.svg'; ?>" alt="">
									<h4><?= isset($listing->listing_name) ? $listing->listing_name : 'Business Name'; ?></h4>
									<p>Address: <?= isset($listing->listing_address) ? $listing->listing_address : 'Business Address'; ?></p>
									<ul>
										<li>
											<a href="https://www.facebook.com/" target="_blank">
												<img src="https://bizbookdirectorytemplate.com/images/social/3.png">
											</a>
										</li>
										<li>
											<a href="https://twitter.com/" target="_blank">
												<img src="https://bizbookdirectorytemplate.com/images/social/2.png">
											</a>
										</li>
										<li>
											<a href="https://www.youtube.com/" target="_blank">
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
								<?php if (!empty($listing->google_map)) { ?>
									<!-- Render map from google_map field -->
									<iframe src="<?= $listing->google_map ?>" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>

									<!-- Additional map actions -->
									<div class="map-actions" style="margin-top: 15px; text-align: center;">
										<?php if (!empty($listing->listing_lat) && !empty($listing->listing_lng)) { ?>
											<a href="https://www.google.com/maps?q=<?= $listing->listing_lat ?>,<?= $listing->listing_lng ?>" target="_blank" class="btn btn-primary btn-sm" style="margin-right: 10px; margin-bottom: 15px; width: 100%;">
												<i class="fa fa-external-link"></i> Open in Google Maps
											</a>
											<a href="https://www.google.com/maps/dir/?api=1&destination=<?= $listing->listing_lat ?>,<?= $listing->listing_lng ?>" target="_blank" class="btn btn-success btn-sm" style="width: 100%;">
												<i class="fa fa-location-arrow"></i> Get Directions
											</a>
										<?php } ?>
									</div>
								<?php } elseif (!empty($listing->listing_lat) && !empty($listing->listing_lng)) { ?>
									<!-- Fallback: Generate map URL from lat/lng if google_map field is empty -->
									<iframe src="https://maps.google.com/maps?q=<?= $listing->listing_lat ?>,<?= $listing->listing_lng ?>&output=embed" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
								<?php } else { ?>
									<!-- Default map if no location data -->
									<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d100940.17087601054!2d-122.50781157529548!3d37.75767917396271!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x80859a6d00690021%3A0x4a501367f076adff!2sSan%20Francisco%2C%20CA%2C%20USA!5e0!3m2!1sen!2sin!4v1615094456570!5m2!1sen!2sin" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
									<p style="text-align: center; margin-top: 10px; color: #666; font-size: 14px;">
										<i class="fa fa-info-circle"></i> Default location shown. Please contact business for exact location.
									</p>
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

<!-- Social Sharing JavaScript -->
<script>
	// Copy to clipboard function
	function copyToClipboard(text) {
		// Add copying animation
		var copyBtn = document.querySelector('.so-link');
		if (copyBtn) {
			copyBtn.classList.add('copying');
			setTimeout(function() {
				copyBtn.classList.remove('copying');
			}, 600);
		}

		if (navigator.clipboard && window.isSecureContext) {
			// Use the Clipboard API if available
			navigator.clipboard.writeText(text).then(function() {
				showCopyMessage('Link copied to clipboard!');
			}, function(err) {
				console.error('Could not copy text: ', err);
				fallbackCopyTextToClipboard(text);
			});
		} else {
			// Fallback for older browsers
			fallbackCopyTextToClipboard(text);
		}
	} // Fallback copy function for older browsers
	function fallbackCopyTextToClipboard(text) {
		var textArea = document.createElement("textarea");
		textArea.value = text;

		// Avoid scrolling to bottom
		textArea.style.top = "0";
		textArea.style.left = "0";
		textArea.style.position = "fixed";

		document.body.appendChild(textArea);
		textArea.focus();
		textArea.select();

		try {
			var successful = document.execCommand('copy');
			if (successful) {
				showCopyMessage('Link copied to clipboard!');
			} else {
				showCopyMessage('Failed to copy link');
			}
		} catch (err) {
			console.error('Fallback: Oops, unable to copy', err);
			showCopyMessage('Failed to copy link');
		}

		document.body.removeChild(textArea);
	}

	// Show copy success message
	function showCopyMessage(message) {
		// Create toast notification
		var toast = document.createElement('div');
		toast.innerHTML = `
		<div style="display: flex; align-items: center; gap: 8px;">
			<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" fill="currentColor"/>
			</svg>
			<span>${message}</span>
		</div>
	`;
		toast.style.cssText = `
		position: fixed;
		top: 20px;
		right: 20px;
		background: linear-gradient(135deg, #28a745, #20c997);
		color: white;
		padding: 16px 20px;
		border-radius: 8px;
		z-index: 10000;
		font-size: 14px;
		font-weight: 500;
		box-shadow: 0 8px 32px rgba(40, 167, 69, 0.3);
		transform: translateX(100%);
		transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
		backdrop-filter: blur(10px);
		border: 1px solid rgba(255, 255, 255, 0.1);
	`;

		document.body.appendChild(toast);

		// Animate in
		setTimeout(function() {
			toast.style.transform = 'translateX(0)';
		}, 10);

		// Remove toast after 3 seconds
		setTimeout(function() {
			toast.style.transform = 'translateX(100%)';
			toast.style.opacity = '0';
			setTimeout(function() {
				if (toast.parentNode) {
					document.body.removeChild(toast);
				}
			}, 400);
		}, 3000);
	} // Share tracking (optional - for analytics)
	function trackShare(platform) {
		// You can add Google Analytics or other tracking here
		console.log('Shared on:', platform);

		// Example: Google Analytics event tracking
		// if (typeof gtag !== 'undefined') {
		//     gtag('event', 'share', {
		//         method: platform,
		//         content_type: 'listing',
		//         item_id: '<?= isset($listing->listing_slug) ? $listing->listing_slug : '' ?>'
		//     });
		// }
	}

	// Add click tracking to social media links
	document.addEventListener('DOMContentLoaded', function() {
		// Facebook
		var fbLink = document.querySelector('.so-fb');
		if (fbLink) {
			fbLink.addEventListener('click', function() {
				trackShare('facebook');
			});
		}

		// Twitter
		var twLink = document.querySelector('.so-tw');
		if (twLink) {
			twLink.addEventListener('click', function() {
				trackShare('twitter');
			});
		}

		// WhatsApp
		var waLink = document.querySelector('.so-wa');
		if (waLink) {
			waLink.addEventListener('click', function() {
				trackShare('whatsapp');
			});
		}

		// LinkedIn
		var liLink = document.querySelector('.so-li');
		if (liLink) {
			liLink.addEventListener('click', function() {
				trackShare('linkedin');
			});
		}

		// Pinterest
		var piLink = document.querySelector('.so-pi');
		if (piLink) {
			piLink.addEventListener('click', function() {
				trackShare('pinterest');
			});
		}

		// Review form functionality
		initializeReviewForm();


	});

	// Initialize review form with dynamic rating and AJAX submission
	function initializeReviewForm() {
		// Dynamic star rating
		var ratingInputs = document.querySelectorAll('input[name="price_rating"]');
		var starValue = document.getElementById('star-value');

		ratingInputs.forEach(function(input) {
			input.addEventListener('change', function() {
				var rating = this.value;
				var ratingText = '';
				switch (rating) {
					case '5':
						ratingText = '5 Star - Awesome';
						break;
					case '4':
						ratingText = '4 Star - Excellent';
						break;
					case '3':
						ratingText = '3 Star - Good';
						break;
					case '2':
						ratingText = '2 Star - Average';
						break;
					case '1':
						ratingText = '1 Star - Poor';
						break;
					default:
						ratingText = '0 Star - Very Poor';
						break;
				}
				if (starValue) {
					starValue.textContent = ratingText;
				}
			});
		});

		// Simple form validation before submit
		var reviewForm = document.getElementById('review_form');
		if (reviewForm) {
			reviewForm.addEventListener('submit', function(e) {
				// Check if user login input exists (means user is logged in)
				var userIdInput = document.querySelector('input[name="review_user_id"]');
				if (!userIdInput) {
					alert('Please login first to submit a review.');
					window.location.href = '<?= base_url("user/login"); ?>';
					return false;
				}

				// Basic validation
				var rating = document.querySelector('input[name="price_rating"]:checked');
				var name = document.getElementById('review_name').value.trim();
				var email = document.getElementById('review_email').value.trim();

				if (!rating) {
					alert('Please select a rating');
					return false;
				}

				if (!name) {
					alert('Please enter your name');
					document.getElementById('review_name').focus();
					return false;
				}

				if (!email) {
					alert('Please enter your email');
					document.getElementById('review_email').focus();
					return false;
				}

				// Allow normal form submission
				return true;
			});
		}
	}

	// Function to update rating display after new review
	function updateRatingDisplay() {
		// This would be called after successful review submission
		// For now, we'll rely on page refresh after form submission
		// But this function is ready for AJAX implementation
		console.log('Rating display updated');
	}
</script>


<!-- Social Sharing Styles -->
<style>
	.share-btn {
		margin: 20px 0;
	}

	.share-btn ul {
		list-style: none;
		padding: 0;
		margin: 0;
		display: flex;
		flex-wrap: wrap;
		gap: 12px;
	}

	.share-btn ul li {
		display: inline-block;
	}

	.share-btn ul li a {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 44px;
		height: 44px;
		border-radius: 50%;
		transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
		text-decoration: none;
		position: relative;
		overflow: hidden;
	}

	/* Default state with brand colors */
	.share-btn ul li a.so-fb {
		background: #1877f2;
		box-shadow: 0 2px 8px rgba(24, 119, 242, 0.3);
	}

	.share-btn ul li a.so-tw {
		background: #1da1f2;
		box-shadow: 0 2px 8px rgba(29, 161, 242, 0.3);
	}

	.share-btn ul li a.so-wa {
		background: #25d366;
		box-shadow: 0 2px 8px rgba(37, 211, 102, 0.3);
	}

	.share-btn ul li a.so-li {
		background: #0a66c2;
		box-shadow: 0 2px 8px rgba(10, 102, 194, 0.3);
	}

	.share-btn ul li a.so-pi {
		background: #e60023;
		box-shadow: 0 2px 8px rgba(230, 0, 35, 0.3);
	}

	.share-btn ul li a.so-link {
		background: #32373aff;
		box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
		color: white;
	}

	.share-btn ul li a.so-link svg {
		margin-right: -5px;
		margin-bottom: -5px;
	}

	/* Hover effects */
	.share-btn ul li a:hover {
		transform: translateY(-3px) scale(1.05);
		box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
	}

	.share-btn ul li a.so-fb:hover {
		box-shadow: 0 8px 20px rgba(24, 119, 242, 0.4);
	}

	.share-btn ul li a.so-tw:hover {
		box-shadow: 0 8px 20px rgba(29, 161, 242, 0.4);
	}

	.share-btn ul li a.so-wa:hover {
		box-shadow: 0 8px 20px rgba(37, 211, 102, 0.4);
	}

	.share-btn ul li a.so-li:hover {
		box-shadow: 0 8px 20px rgba(10, 102, 194, 0.4);
	}

	.share-btn ul li a.so-pi:hover {
		box-shadow: 0 8px 20px rgba(230, 0, 35, 0.4);
	}

	.share-btn ul li a.so-link:hover {
		box-shadow: 0 8px 20px rgba(108, 117, 125, 0.4);
	}

	/* Icon styling */
	.share-btn ul li a img {
		width: 20px;
		height: 20px;
		transition: all 0.3s ease;
	}

	.share-btn ul li a:hover img {
		transform: scale(1.1);
	}

	/* Active state */
	.share-btn ul li a:active {
		transform: translateY(-1px) scale(1.02);
	}

	/* Tooltip on hover */
	.share-btn ul li a::before {
		content: attr(title);
		position: absolute;
		bottom: 120%;
		left: 50%;
		transform: translateX(-50%);
		background: rgba(0, 0, 0, 0.8);
		color: white;
		padding: 6px 10px;
		border-radius: 4px;
		font-size: 12px;
		white-space: nowrap;
		opacity: 0;
		visibility: hidden;
		transition: all 0.3s ease;
		z-index: 1000;
	}

	.share-btn ul li a::after {
		content: '';
		position: absolute;
		bottom: 110%;
		left: 50%;
		transform: translateX(-50%);
		border: 4px solid transparent;
		border-top-color: rgba(0, 0, 0, 0.8);
		opacity: 0;
		visibility: hidden;
		transition: all 0.3s ease;
	}

	.share-btn ul li a:hover::before,
	.share-btn ul li a:hover::after {
		opacity: 1;
		visibility: visible;
	}

	/* Responsive design */
	@media (max-width: 480px) {
		.share-btn ul {
			justify-content: center;
			gap: 10px;
		}

		.share-btn ul li a {
			width: 40px;
			height: 40px;
		}

		.share-btn ul li a img {
			width: 18px;
			height: 18px;
		}
	}

	/* Loading animation for copy button */
	.share-btn ul li a.so-link.copying {
		animation: pulse 0.6s ease-in-out;
	}

	@keyframes pulse {
		0% {
			transform: scale(1);
		}

		50% {
			transform: scale(1.1);
		}

		100% {
			transform: scale(1);
		}
	}

	/* Review System Styles */
	.lp-ur-all-left-12 {
		background-color: #e9ecef;
		height: 8px;
		border-radius: 4px;
		overflow: hidden;
		margin: 5px 0;
	}

	.lp-ur-all-left-13 {
		height: 100%;
		background-color: #ffc107;
		transition: width 0.3s ease;
		border-radius: 4px;
	}

	.lp-ur-all-left-13.lp-ur-all-left-Good {
		background-color: #28a745;
	}

	.lp-ur-all-left-13.lp-ur-all-left-satis {
		background-color: #17a2b8;
	}

	.lp-ur-all-left-13.lp-ur-all-left-below {
		background-color: #fd7e14;
	}

	.lp-ur-all-left-13.lp-ur-all-left-poor {
		background-color: #dc3545;
	}

	.lp-ur-all-left-11 {
		font-size: 14px;
		color: #495057;
		margin-bottom: 3px;
	}

	/* Star Rating Styles */
	fieldset.rating {
		border: none;
		margin: 10px 0;
		padding: 0;
		display: inline-block;
		/* direction: rtl; */
		unicode-bidi: bidi-override;
		margin-top: -22px;
		margin-left: 10px;
	}

	fieldset.rating input {
		display: none;
	}

	fieldset.rating label {
		display: inline-block;
		padding: 5px;
		font-size: 30px;
		color: #ddd;
		cursor: pointer;
		transition: color 0.2s;
		position: relative;
	}

	fieldset.rating label:before {
		content: "★";
	}

	fieldset.rating label:hover,
	fieldset.rating label:hover~label,
	fieldset.rating input:checked~label {
		color: #ffc107;
	}

	.review_fields {
		margin-top: 30px;
	}


	#star-value {
		margin: 10px 0;
		font-weight: bold;
		color: #495057;
		padding: 5px 10px;
		background: #f8f9fa;
		border-radius: 4px;
		display: inline-block;
	}

	/* Review Form Styles */
	#review_form {
		background: #f8f9fa;
		padding: 20px;
		border-radius: 8px;
		margin: 15px 0;
	}

	#review_form .input-field {
		margin-bottom: 15px;
	}

	#review_form input[type="text"],
	#review_form input[type="email"],
	#review_form textarea {
		width: 100%;
		padding: 10px;
		border: 1px solid #ddd;
		border-radius: 4px;
		font-size: 14px;
	}

	#review_form input[type="submit"] {
		background: #007bff;
		color: white;
		padding: 12px 30px;
		border: none;
		border-radius: 4px;
		cursor: pointer;
		font-size: 16px;
		transition: background 0.3s;
	}

	#review_form input[type="submit"]:hover {
		background: #0056b3;
	}

	#review_form input[type="submit"]:disabled {
		background: #6c757d;
		cursor: not-allowed;
	}

	/* Review Display Styles */
	.lr-user-wr-con h6 {
		color: #495057;
		font-weight: bold;
		margin-bottom: 5px;
	}

	.lr-revi-date {
		color: #6c757d;
		font-size: 12px;
		margin-left: 10px;
	}

	.lr-user-wr-con p {
		margin: 10px 0;
		line-height: 1.5;
		color: #495057;
	}

	.lr-user-wr-img img {
		width: 50px;
		height: 50px;
		border-radius: 50%;
		object-fit: cover;
	}

	#reviews-list ul li {
		padding: 15px 0;
		border-bottom: 1px solid #e9ecef;
		display: flex;
		gap: 15px;
	}

	#reviews-list ul li:last-child {
		border-bottom: none;
	}

	/* Responsive adjustments */
	@media (max-width: 768px) {
		.lp-ur-all {
			flex-direction: column;
		}

		.rating label {
			font-size: 20px;
			padding: 3px;
		}

		#review_form {
			padding: 15px;
		}
	}
</style>

<!-- Get Quote Modal -->
<section>
	<div class="pop-ups pop-quo">
		<!-- The Modal -->
		<div class="modal fade" id="quote">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="log-bor">&nbsp;</div>
					<span class="udb-inst">Send enquiry</span>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<!-- Modal Header -->
					<div class="quote-pop">
						<h4>Get quote</h4>

						<?php if ($this->session->flashdata('message')): ?>
							<div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;">
								<?= $this->session->flashdata('message'); ?>
							</div>
						<?php endif; ?>

						<?php if ($this->session->flashdata('error_msg')): ?>
							<div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;">
								<?= $this->session->flashdata('error_msg'); ?>
							</div>
						<?php endif; ?>

						<form method="post" action="<?= base_url('user/add-enquiry/listing_details/' . $listing->listing_slug); ?>" name="enquiry_form" id="enquiry_form">

							<!-- Hidden fields for enquiry data -->
							<input type="hidden" name="listing_id" value="<?= $listing->listing_id; ?>">
							<input type="hidden" name="event_id" value="">
							<input type="hidden" name="blog_id" value="">
							<input type="hidden" name="product_id" value="">
							<input type="hidden" name="receiving_user_id" value="<?= $listing->user_id; ?>">
							<input type="hidden" name="enquiry_sender_id" value="<?= isset($logged_id) ? $logged_id : ''; ?>">
							<input type="hidden" name="enquiry_source" value="<?= $listing->listing_name; ?>">
							<input type="hidden" name="enquiry_type" value="listing">

							<div class="form-group">
								<input type="text" class="form-control" name="enquiry_name" placeholder="Enter name*" required>
							</div>
							<div class="form-group">
								<input type="email" class="form-control" name="enquiry_email" placeholder="Enter email*" pattern="^[\w]{1,}[\w.+-]{0,}@[\w-]{2,}([.][a-zA-Z]{2,}|[.][\w-]{2,}[.][a-zA-Z]{2,})$" title="Invalid email address" required>
							</div>
							<div class="form-group">
								<input type="text" class="form-control" name="enquiry_mobile" placeholder="Enter mobile number *" pattern="[0-9]{10,15}" title="Please enter a valid phone number (10-15 digits)" required>
							</div>
							<div class="form-group">
								<textarea class="form-control" rows="3" name="enquiry_message" placeholder="Enter your query or message"></textarea>
							</div>
							<button type="submit" class="btn btn-primary">Submit</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>