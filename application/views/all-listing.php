<style>
	.pg_title {
		font-size: 40px !important;
		font-weight: 700 !important;
		margin-bottom: 20px;
	}

	@media (max-width: 480px) {
		.pg_title {
			font-size: 30px !important;
		}
	}

	.all-listing-pg {
		background: linear-gradient(to top, #00000000 10%, #00000078 90%), url(/assets/images/all-listing-bg.webp);
		background-size: cover;
		background-position: top;
		background-attachment: fixed;
	}

	/* ---- Sidebar polish ------------------------------------------------ */

	.all-filt .filt-alist-near .tit h4,
	.all-filt .filt-com h4 {
		font-size: 14px;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.08em;
		color: #180f5e;
	}

	/* Top featured listing cards */
	.all-filt .near-ser-list ul li .near-bx {
		border: 1px solid #e6e8ee !important;
		border-radius: 12px !important;
		padding: 10px !important;
		margin-bottom: 10px !important;
		background: #fff;
		display: flex !important;
		align-items: center;
		gap: 12px;
		transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
	}

	.all-filt .near-ser-list ul li .near-bx:hover {
		transform: translateY(-2px);
		border-color: #cdd6e3 !important;
		box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
	}

	.all-filt .near-ser-list ul li .near-bx .ne-1 {
		width: 48px !important;
		height: 48px !important;
		flex-shrink: 0;
		border-radius: 10px;
		overflow: hidden;
		background: #f1f5f9;
	}

	.all-filt .near-ser-list ul li .near-bx .ne-1 img {
		width: 100% !important;
		height: 100% !important;
		object-fit: cover;
		border-radius: 10px;
	}

	/* CSS letter avatar shown when the listing has no image */
	.all-filt .near-ser-list ul li .near-bx .ne-1.is-placeholder {
		display: flex;
		align-items: center;
		justify-content: center;
		color: #fff;
		font-weight: 700;
		font-size: 18px;
		letter-spacing: 0.02em;
	}

	.all-filt .near-ser-list ul li .near-bx .ne-2 {
		min-width: 0;
		flex: 1;
	}

	.all-filt .near-ser-list ul li .near-bx .ne-2 h5 {
		font-size: 14px !important;
		font-weight: 600 !important;
		color: #1f2937 !important;
		margin: 0 0 2px 0 !important;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		line-height: 1.3;
	}

	.all-filt .near-ser-list ul li .near-bx .ne-2 p {
		font-size: 12px !important;
		color: #6b7280 !important;
		margin: 0 !important;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	/* Categories dropdown */
	.all-filt .lhs-cate .dropdown select.form-control {
		height: 44px;
		border: 1.5px solid #e2e8f0;
		border-radius: 10px;
		background: #fff;
		font-size: 14px;
		color: #1f2937;
		padding: 0 14px;
		transition: border-color 0.2s ease, box-shadow 0.2s ease;
	}

	.all-filt .lhs-cate .dropdown select.form-control:focus {
		border-color: #7bb73f;
		box-shadow: 0 0 0 3px rgba(123, 183, 63, 0.15);
		outline: none;
	}

	/* ---- Card category chip (was the misleading "open" badge) --------- */

	.eve-box .al-img .open-stat {
		position: absolute;
		top: 10px;
		left: 10px;
		max-width: calc(100% - 20px);
		background: rgba(15, 23, 42, 0.78) !important;
		color: #fff !important;
		font-size: 11px !important;
		font-weight: 600 !important;
		letter-spacing: 0.02em;
		text-transform: uppercase;
		padding: 5px 10px !important;
		border-radius: 999px !important;
		line-height: 1.2 !important;
		backdrop-filter: blur(4px);
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		display: inline-block;
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
	}

	.eve-box .al-img .open-stat:hover {
		background: #180f5e !important;
		color: #fff !important;
	}

	/* NEW badge alignment */
	.eve-box .al-badge-new {
		vertical-align: middle;
	}
</style>

<!-- START -->
<section>
	<div class="all-listing all-listing-pg" style="margin-top: -105px;">
		<!--FILTER ON MOBILE VIEW-->
		<div class="fil-mob fil-mob-act">
			<h4>Listing filters <i class="material-icons">filter_list</i></h4>
		</div>
		<div class="all-list-bre">
			<div class="container sec-all-list-bre">
				<div class="row">
					<div class="col-md-4">
						<h1>Business</h1>
						<ul>
							<li><a href="<?= base_url('/'); ?>">Home</a>
							</li>
							<!--<li><a href="all-category.html">All category</a>-->
							<!--</li>-->
							<!--<li> <a href="all-listing.html">Business</a>-->
							<!--</li>-->
						</ul>
					</div>
					<!-- <div class="col-md-8">
						<div class="ban-ati-com ads-all-list">
							<a href="#"><span>Ad</span><img src="<?= base_url('assets/images/AD-SAMPLE.png'); ?>" alt=""></a>
						</div>
					</div> -->
				</div>
			</div>
		</div>
		<div class="container">
			<div class="row">
				<div class="col-md-3 fil-mob-view listing_col3">
					<div class="all-filt"> <span class="fil-mob-clo"><i class="material-icons">close</i></span>
						<!--START-->
						<div class="filt-alist-near">
							<div class="tit">
								<h4>Top Featured Listing</h4>
							</div>
							<div class="near-ser-list top-ser-secti-prov">
								<ul>
									<?php
									// Palette for letter-avatar fallbacks; index by hash so the
									// same listing always gets the same colour across page loads.
									$avatar_palette = array(
										'#180f5e', '#2563eb', '#7bb73f', '#0e7490',
										'#b45309', '#7c3aed', '#be185d', '#0f766e',
									);
									if ($randListings) {
										foreach ($randListings as $value):
											$has_addr   = ((int) ($value->display_address ?? 0) === 1 && !empty($value->listing_address));
											$first_char = strtoupper(mb_substr(trim((string) $value->listing_name), 0, 1) ?: '•');
											$swatch     = $avatar_palette[crc32((string) $value->listing_name) % count($avatar_palette)];
											$detail     = base_url('listing-details/' . rawurlencode((string) $value->listing_slug));
									?>
											<li>
												<div class="near-bx">
													<?php if (empty($value->listing_image)): ?>
														<div class="ne-1 is-placeholder" style="background: <?= e_attr($swatch) ?>;" aria-hidden="true">
															<?= e($first_char) ?>
														</div>
													<?php else: ?>
														<div class="ne-1">
															<?= responsive_img($value->listing_image, $value->listing_name ?? '') ?>
														</div>
													<?php endif; ?>
													<div class="ne-2">
														<h5><?= e($value->listing_name) ?></h5>
														<p><?= $has_addr ? e($value->listing_address) : 'Pearland area' ?></p>
													</div>
													<a href="<?= e_attr($detail) ?>" aria-label="<?= e_attr($value->listing_name) ?>"></a>
												</div>
											</li>
									<?php
										endforeach;
									}
									?>
								</ul>
							</div>

						</div>
						<!--END-->
						<!--START-->
						<div class="filt-com lhs-cate">
							<h4>Categories</h4>

							<div class="dropdown">
								<select class="form-control" id="selectCategory" onchange="filterByCategory(this.value)">
									<option value="">All Categories</option>
									<?php if ($allCategories) {
										foreach ($allCategories as $value) {
											// Use slug if available, otherwise generate from name, fallback to ID
											$option_value = '';
											if (isset($value->category_slug) && !empty($value->category_slug)) {
												$option_value = $value->category_slug;
											} elseif (!empty($value->category_name)) {
												$option_value = strtolower(str_replace(' ', '-', $value->category_name));
											} else {
												$option_value = $value->category_id;
											}
											$selected = (isset($current_category_slug) && $current_category_slug == $option_value) ? 'selected' : '';
									?>
											<option value="<?= $option_value; ?>" <?= $selected; ?>><?= $value->category_name; ?></option>
									<?php
										}
									}
									?>
								</select>
							</div>
						</div>
						<!--END-->
						<!--START-->
						<?php $slot = 'listing-sidebar'; include APPPATH . 'views/partials/ad_slot.php'; ?>
						<?php include APPPATH . 'views/partials/web_dev_cta.php'; ?>
						<!--END-->
						<div class="all-list-filt-form form-position" style="z-index: 10;" >
							<div class="tit">
								<h3>What service do you need? <span>Pearland411 will help you</span></h3>
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
								<form name="home_slide_enquiry_form" id="home_slide_enquiry_form" method="post" action="<?= base_url('user/add_enquiry/all_listing'); ?>" enctype="multipart/form-data">
									<input type="hidden" class="form-control" name="listing_id" value="0">
									<input type="hidden" class="form-control" name="receiving_user_id" value="0">
									<input type="hidden" class="form-control" name="enquiry_sender_id" value="<?= isset($user) && $user ? $user->user_id : ''; ?>">
									<input type="hidden" class="form-control" name="enquiry_source" value="All Listing Page">
									<input type="hidden" class="form-control" name="enquiry_type" value="general">
									<div class="form-group">
										<input type="text" name="enquiry_name" value="<?= isset($user) && $user && isset($user->name) ? $user->name : ''; ?>" required="required" class="form-control" placeholder="Enter name*">
									</div>
									<div class="form-group">
										<input type="email" class="form-control" placeholder="Enter email*" required="required" value="<?= isset($user) && $user && isset($user->email_id) ? $user->email_id : ''; ?>" name="enquiry_email" pattern="^[\w]{1,}[\w.+-]{0,}@[\w-]{2,}([.][a-zA-Z]{2,}|[.][\w-]{2,}[.][a-zA-Z]{2,})$" title="Invalid email address">
									</div>
									<div class="form-group">
										<input type="tel" class="form-control" value="<?= isset($user) && $user && isset($user->mobile_number) ? $user->mobile_number : ''; ?>" name="enquiry_mobile" placeholder="Enter mobile number *" pattern="[0-9+\-\s\(\)]{10,15}" title="Please enter a valid phone number" required="">
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
					</div>
				</div>
				<div class="col-md-9" style="margin-bottom:110px; overflow-x: hidden;">
					<div class="f2">
						<div class="vfilter"> <i class="material-icons ic1 " title="Grid view">apps</i>
							<i class="material-icons ic2 act" title="List view">format_list_bulleted</i>
							<i class="material-icons ic3" title="Map view">location_on</i>
						</div>
					</div>
					<?php $slot = 'listing-top'; include APPPATH . 'views/partials/ad_slot.php'; ?>
					<!-- Loader Image -->
					<div id="loadingmessage" style="display:none">
						<div id="loadingmessage1">&nbsp;</div>
					</div>
					<!-- Loader Image -->

					<!-- Fixed Listing Count Info -->
					<?php if (isset($allListings) && count($allListings) > 0): ?>
						<div class="listing-count-info" style="
							background: #f8f9fa; 
							padding: 15px 20px; 
							border-radius: 8px; 
							margin-bottom: 20px; 
							border-left: 4px solid #7bb73f; 
							box-shadow: 0 1px 3px rgba(0,0,0,0.1);
							overflow: hidden;
							width: 100%;
							box-sizing: border-box;
						">
							<div style="
								display: flex;
								justify-content: space-between;
								align-items: center;
								width: 100%;
							">
								<div style="
									margin-bottom: 8px;
									width: 100%;
								">
									<div style="
										font-size: 16px; 
										color: #2d3748; 
										font-weight: 600; 
										line-height: 1.4;
									">
										<i class="material-icons" style="
											vertical-align: middle; 
											color: #7bb73f; 
											margin-right: 6px; 
											font-size: 20px;
										">business</i>
										<?php
										$start_count = isset($current_offset) ? $current_offset + 1 : 1;
										$end_count = isset($current_offset) ? $current_offset + count($allListings) : count($allListings);
										?>
										Showing <strong style="color: #7bb73f;"><?= $start_count; ?>-<?= $end_count; ?></strong> of <strong><?= isset($total_listings) ? number_format($total_listings) : 'many'; ?></strong> businesses
									</div>
									<?php if (isset($current_category_slug) && $current_category_slug): ?>
										<?php
										$category_name = '';
										if ($allCategories) {
											foreach ($allCategories as $cat) {
												$cat_slug = '';
												if (isset($cat->category_slug) && !empty($cat->category_slug)) {
													$cat_slug = $cat->category_slug;
												} elseif (!empty($cat->category_name)) {
													$cat_slug = strtolower(str_replace(' ', '-', $cat->category_name));
												}
												if ($cat_slug == $current_category_slug) {
													$category_name = $cat->category_name;
													break;
												}
											}
										}
										?>
										<div style="
											font-size: 13px; 
											color: #6c757d; 
											margin-top: 4px;
											line-height: 1.3;
										">
											<i class="material-icons" style="
												vertical-align: middle; 
												font-size: 14px; 
												margin-right: 4px;
											">category</i>
											Category: <strong><?= $category_name ? $category_name : 'Selected Category'; ?></strong>
										</div>
									<?php endif; ?>
								</div>
								<div style="
									text-align: right;
									margin-top: 8px;
									width: 100%;
								">
									<?php if (isset($current_page) && isset($total_pages)): ?>
										<div style="
											font-size: 14px; 
											color: #495057; 
											font-weight: 500;
											line-height: 1.3;
										">
											<i class="material-icons" style="
												vertical-align: middle; 
												font-size: 16px; 
												margin-right: 4px; 
												color: #7bb73f;
											">pages</i>
											Page <strong style="color: #7bb73f;"><?= $current_page; ?></strong> of <strong><?= number_format($total_pages); ?></strong>
											<span style="margin: 0 8px; color: #dee2e6;">|</span>
											<i class="material-icons" style="
												vertical-align: middle; 
												font-size: 14px; 
												margin-right: 2px;
											">location_on</i>
											Pearland Area
										</div>
									<?php else: ?>
										<div style="
											font-size: 13px; 
											color: #6c757d;
											line-height: 1.3;
										">
											<i class="material-icons" style="
												vertical-align: middle; 
												font-size: 14px; 
												margin-right: 2px;
											">location_on</i>
											Pearland Area
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endif; ?>

					<div class="all-list-sh all-listing-total">
						<ul>
							<?php

							if (isset($allListings) && count($allListings) > 0) {
								foreach ($allListings as $value) {
							?>
									<?php
										$cat_label = !empty($value->category_name) ? $value->category_name : '';
										$cat_link  = !empty($value->category_slug)
											? base_url('all_listing/' . rawurlencode((string) $value->category_slug))
											: base_url('all-category');
										$is_new = !empty($value->created_at)
											&& (strtotime($value->created_at) > strtotime('-30 days'));
										$is_verified = !empty($value->featured_listing) || !empty($value->spotlight_listing);
										$detail_url  = base_url('listing-details/' . rawurlencode((string) $value->listing_slug));
									?>
									<li>
										<div class="eve-box" style="width: 100%;">
											<!---LISTING IMAGE--->
											<div class="al-img">
												<?php if ($cat_label !== ''): ?>
													<a class="open-stat" href="<?= e_attr($cat_link) ?>" style="text-decoration:none;"><?= e($cat_label) ?></a>
												<?php endif; ?>
												<a href="<?= e_attr($detail_url) ?>">
													<?php if (empty($value->listing_image)): ?>
															<img src="<?= base_url('assets/images/no_photo.jpg') ?>" alt="<?= e_attr($value->listing_name ?? '') ?>" loading="lazy" decoding="async">
														<?php else: ?>
															<?= responsive_img($value->listing_image, $value->listing_name ?? '') ?>
														<?php endif; ?>
												</a>
											</div>
											<!---END LISTING IMAGE--->
											<!---LISTING NAME--->
											<div>
												<h4>
													<a href="<?= e_attr($detail_url) ?>"><?= e($value->listing_name); ?></a>
													<?php if ($is_verified): ?>
														<i class="li-veri" title="Verified business"><img src="<?= base_url('assets/images/icon/svg/verified.png'); ?>" alt="Verified"></i>
													<?php endif; ?>
													<?php if ($is_new && !$is_verified): ?>
														<span class="al-badge-new" style="display:inline-block;margin-left:6px;padding:2px 8px;border-radius:999px;background:#dbeafe;color:#1d4ed8;font-size:11px;font-weight:600;letter-spacing:0.02em;">NEW</span>
													<?php endif; ?>
												</h4>

												<?php if ($value->display_address == 1 && !empty($value->listing_address)): ?>
													<span class="addr"><?= e($value->listing_address); ?></span>
												<?php endif; ?>

												<?php if ($value->display_phone == 1 && !empty($value->listing_mobile)): ?>
													<span class="pho"><?= e($value->listing_mobile); ?></span>
												<?php endif; ?>

												<?php if (!empty($value->listing_email)): ?>
													<span class="mail"><?= e($value->listing_email); ?></span>
												<?php endif; ?>

												<div class="links">
													<a href="<?= e_attr($detail_url) ?>">View more</a>
													<?php if ($value->display_phone == 1 && !empty($value->listing_mobile)): ?>
														<a href="tel:<?= e_attr(preg_replace('/[^0-9+]/', '', $value->listing_mobile)); ?>">Call Now</a>
													<?php endif; ?>
													<?php if (!empty($value->listing_whatsapp)): ?>
														<a href="https://wa.me/<?= e_attr(preg_replace('/[^0-9]/', '', $value->listing_whatsapp)); ?>" class="what" target="_blank" rel="noopener">WhatsApp</a>
													<?php endif; ?>
												</div>
											</div>
											<!---END LISTING NAME--->

										</div>
									</li>

								<?php  }
							} else {
								?>
								<li style="list-style: none; width: 100%; position: relative; z-index: 10; clear: both;">
									<style>
										.modern-empty-state {
											background: rgba(255, 255, 255, 0.98);
											backdrop-filter: blur(10px);
											border-radius: 24px;
											padding: 60px 40px;
											max-width: 600px;
											width: 100%;
											text-align: center;
											box-shadow: 0 20px 60px rgba(24, 15, 94, 0.15);
											animation: fadeInUp 0.6s ease-out;
											margin: 60px auto;
											border: 2px solid rgba(123, 183, 63, 0.2);
											position: relative;
											z-index: 100;
											overflow: hidden;
											top: 55px;
										}

										.modern-empty-state::before {
											content: '';
											position: absolute;
											top: 0;
											left: 0;
											right: 0;
											bottom: 0;
											background: linear-gradient(135deg, rgba(123, 183, 63, 0.05) 0%, rgba(24, 15, 94, 0.05) 100%);
											border-radius: 24px;
											z-index: -1;
										}

										@keyframes fadeInUp {
											from {
												opacity: 0;
												transform: translateY(30px);
											}

											to {
												opacity: 1;
												transform: translateY(0);
											}
										}

										.modern-empty-state .icon-container {
											position: relative;
											width: 120px;
											height: 120px;
											margin: 0 auto 30px;
										}

										.modern-empty-state .icon-bg {
											width: 100%;
											height: 100%;
											background: #7bb73f;
											border-radius: 50%;
											display: flex;
											align-items: center;
											justify-content: center;
											animation: pulse 2s ease-in-out infinite;
										}

										@keyframes pulse {

											0%,
											100% {
												transform: scale(1);
												box-shadow: 0 0 0 0 rgba(123, 183, 63, 0.4);
											}

											50% {
												transform: scale(1.05);
												box-shadow: 0 0 0 20px rgba(123, 183, 63, 0);
											}
										}

										.modern-empty-state .search-icon {
											width: 60px;
											height: 60px;
											stroke: white;
											stroke-width: 2;
											fill: none;
											animation: rotate 3s ease-in-out infinite;
										}

										@keyframes rotate {

											0%,
											100% {
												transform: rotate(0deg);
											}

											50% {
												transform: rotate(10deg);
											}
										}

										.modern-empty-state h1 {
											font-size: 32px;
											color: #2d3748;
											margin-bottom: 16px;
											font-weight: 700;
											font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
										}

										.modern-empty-state p {
											font-size: 18px;
											color: #718096;
											margin-bottom: 35px;
											line-height: 1.6;
											font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
										}

										.modern-empty-state .modern-btn {
											display: inline-block;
											padding: 16px 40px;
											background: #7bb73f;
											color: white;
											text-decoration: none;
											border-radius: 50px;
											font-size: 16px;
											font-weight: 600;
											transition: all 0.3s ease;
											box-shadow: 0 10px 25px rgba(123, 183, 63, 0.3);
											border: none;
											cursor: pointer;
											margin-right: 15px;
											margin-bottom: 10px;
										}

										.modern-empty-state .modern-btn:hover {
											transform: translateY(-2px);
											box-shadow: 0 15px 35px rgba(123, 183, 63, 0.4);
											color: white;
											background: #180f5e;
										}

										.modern-empty-state .modern-btn:active {
											transform: translateY(0);
										}

										.modern-empty-state .secondary-link {
											display: inline-block;
											margin-top: 20px;
											color: #180f5e;
											text-decoration: none;
											font-size: 15px;
											font-weight: 500;
											transition: color 0.3s ease;
											font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
										}

										.modern-empty-state .secondary-link:hover {
											color: #7bb73f;
											text-decoration: underline;
										}

										@media (max-width: 600px) {
											.modern-empty-state {
												padding: 40px 30px;
											}

											.modern-empty-state h1 {
												font-size: 26px;
											}

											.modern-empty-state p {
												font-size: 16px;
											}

											.modern-empty-state .icon-container {
												width: 100px;
												height: 100px;
											}

											.modern-empty-state .search-icon {
												width: 50px;
												height: 50px;
											}

											.modern-empty-state .modern-btn {
												padding: 12px 30px;
												font-size: 14px;
												margin-right: 0;
												margin-bottom: 15px;
												display: block;
												width: 100%;
												max-width: 250px;
												margin-left: auto;
												margin-right: auto;
											}
										}

										/* Fix overlapping issues */
										.all-list-sh {
											position: relative;
											z-index: 1;
										}

										.ban-ati-com.ads-all-list {
											position: relative;
											z-index: 2;
											margin: 20px 0;
										}

										.modern-empty-state+.ban-ati-com {
											margin-top: 60px;
										}

										/* Ensure proper spacing around empty state */
										.modern-empty-state {
											clear: both;
											display: block;
											margin: 60px auto !important;
										}
									</style>

									<div class="modern-empty-state">
										<div class="icon-container">
											<div class="icon-bg">
												<svg class="search-icon" viewBox="0 0 24 24">
													<circle cx="11" cy="11" r="8"></circle>
													<path d="m21 21-4.35-4.35"></path>
												</svg>
											</div>
										</div>

										<h1>No Listings Found</h1>
										<p>
											<?php if (isset($current_category_slug) && $current_category_slug): ?>
												We couldn't find any businesses in this category. Try exploring a different category or check out all available listings.
											<?php else: ?>
												No businesses are currently available. Please check back later or explore our categories.
											<?php endif; ?>
										</p>

										<a href="<?= base_url('all_listing'); ?>" class="modern-btn">View All Listings</a>

										<a href="<?= base_url('all-category'); ?>" class="secondary-link">Browse Popular Categories →</a>
									</div>
								</li>
							<?php } ?>
						</ul>
					</div>

					<!-- Pagination Section -->
					<?php if (isset($link) && !empty($link)): ?>
						<div class="pagination-wrapper" style="margin: 40px 0; text-align: center; clear: both; width: 100%;">
							<div class="pagination-container" style="display: inline-block; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; max-width: 100%;">
								<div class="pagination-info" style="margin-bottom: 15px; color: #718096; font-size: 14px;">
									<i class="material-icons" style="vertical-align: middle; font-size: 18px; margin-right: 5px;">pages</i>
									Navigate through listings
								</div>
								<div class="pagination-links">
									<?= $link; ?>
								</div>
							</div>
						</div>
						<style>
							/* Enhanced Pagination Styles */
							.pagination-wrapper .pagination {
								margin: 0;
								display: flex;
								justify-content: center;
								align-items: center;
								flex-wrap: wrap;
								gap: 5px;
								list-style: none;
								padding: 0;
							}

							.pagination-wrapper .pagination li {
								list-style: none;
								margin: 0;
								display: inline-block;
							}

							.pagination-wrapper .pagination li a,
							.pagination-wrapper .pagination li span {
								display: inline-block;
								padding: 8px 14px;
								margin: 0;
								text-decoration: none;
								background: #fff;
								color: #4a5568;
								border: 2px solid #e2e8f0;
								border-radius: 8px;
								font-weight: 500;
								font-size: 14px;
								transition: all 0.3s ease;
								min-width: 44px;
								text-align: center;
								position: relative;
								overflow: hidden;
							}

							.pagination-wrapper .pagination li a:hover {
								background: #7bb73f;
								color: white;
								border-color: #7bb73f;
								transform: translateY(-2px);
								box-shadow: 0 4px 12px rgba(123, 183, 63, 0.3);
							}

							.pagination-wrapper .pagination li.active a,
							.pagination-wrapper .pagination li.page-item.active a {
								background: linear-gradient(135deg, #7bb73f 0%, #6ba532 100%);
								color: white;
								border-color: #7bb73f;
								box-shadow: 0 4px 12px rgba(123, 183, 63, 0.4);
								font-weight: 600;
							}

							.pagination-wrapper .pagination li:first-child a,
							.pagination-wrapper .pagination li:last-child a {
								background: #f7fafc;
								color: #2d3748;
								font-weight: 600;
								border-color: #cbd5e0;
							}

							.pagination-wrapper .pagination li:first-child a:hover,
							.pagination-wrapper .pagination li:last-child a:hover {
								background: #180f5e;
								color: white;
								border-color: #180f5e;
							}

							/* Responsive Pagination */
							@media (max-width: 768px) {

								.pagination-wrapper .pagination li a,
								.pagination-wrapper .pagination li span {
									padding: 8px 12px;
									font-size: 13px;
									min-width: 38px;
								}

								.pagination-container {
									padding: 15px !important;
								}

								.pagination-info {
									font-size: 13px !important;
								}
							}

							@media (max-width: 480px) {
								.pagination-wrapper .pagination {
									gap: 4px;
								}

								.pagination-wrapper .pagination li a,
								.pagination-wrapper .pagination li span {
									padding: 6px 10px;
									font-size: 12px;
									min-width: 32px;
								}
							}

							/* Add animation */
							.pagination-wrapper .pagination li a::before {
								content: '';
								position: absolute;
								top: 0;
								left: -100%;
								width: 100%;
								height: 100%;
								background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
								transition: left 0.5s;
							}

							.pagination-wrapper .pagination li a:hover::before {
								left: 100%;
							}
						</style>
					<?php endif; ?>

					<?php $slot = 'listing-inline'; include APPPATH . 'views/partials/ad_slot.php'; ?>
				</div>
			</div>
		</div>
	</div>
</section>
<!-- END -->

<script>
	function filterByCategory(categorySlug) {
		// Always use the working URL structure (/user/all_listing)
		// This ensures consistency until clean URLs are properly configured
		var basePath = '<?= base_url("all_listing"); ?>';

		if (categorySlug === '' || categorySlug === null) {
			// Redirect to all listings page (no category filter)
			window.location.href = basePath;
		} else {
			// Redirect to category-specific listings page
			window.location.href = basePath + '/' + categorySlug;
		}
	}

	// Handle enquiry form submission
	document.addEventListener('DOMContentLoaded', function() {
		// Check if jQuery is available
		if (typeof $ !== 'undefined') {
			// Use jQuery
			$('#home_slide_enquiry_form').on('submit', function(e) {
				handleEnquirySubmission(e, this);
			});
		} else {
			// Use vanilla JavaScript
			document.getElementById('home_slide_enquiry_form').addEventListener('submit', function(e) {
				handleEnquirySubmission(e, this);
			});
		}

	});

	function handleEnquirySubmission(e, form) {
		e.preventDefault();

		// Hide all messages first
		hideAllMessages();

		// Get form data
		var formData = new FormData(form);

		// Submit via AJAX
		var xhr = new XMLHttpRequest();
		xhr.open('POST', form.action, true);
		xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

		xhr.onreadystatechange = function() {
			if (xhr.readyState === 4) {
				if (xhr.status === 200) {
					try {
						var response = JSON.parse(xhr.responseText);
						if (response && response.success) {
							showMessage('home_slide_enq_success');
							form.reset();
						} else {
							showMessage('home_slide_enq_fail');
						}
					} catch (e) {
						showMessage('home_slide_enq_fail');
					}
				} else {
					showMessage('home_slide_enq_fail');
				}
			}
		};

		xhr.send(formData);
	}

	function hideAllMessages() {
		var messages = ['home_slide_enq_success', 'home_slide_enq_fail', 'home_slide_enq_same'];
		messages.forEach(function(id) {
			var element = document.getElementById(id);
			if (element) element.style.display = 'none';
		});
	}

	function showMessage(messageId) {
		var element = document.getElementById(messageId);
		if (element) element.style.display = 'block';
	}
</script>