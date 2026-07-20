<!doctype html>
<html lang="en">

<head>
	<!--== META TAGS ==-->
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<?php
	// Centralised SEO + social + analytics tags. Controllers can set
	// $title, $meta_description, $meta_keywords, $og_image, $canonical_url,
	// $structured_data (array or JSON-LD string), or pre-built $json_ld.
	include APPPATH . 'views/partials/seo_head.php';
	?>

	<!--== FAV ICON(BROWSER TAB ICON) ==-->
	<link rel="shortcut icon" href="<?= base_url() ?>assets/images/fav.ico" type="image/x-icon">

	<!--== GOOGLE FONTS ==-->
	<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Oswald:700|Source+Sans+Pro:300,400,600,700&display=swap" rel="stylesheet">

	<!--== WEB ICON FONTS ==-->
	<link rel="preload" as="font" href="css/icon.woff2" type="font/woff2" crossorigin="anonymous">

	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

	<!--== CSS FILES ==-->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
	<link rel="stylesheet" href="<?= base_url() ?>assets/css/style.css">
	<link rel="stylesheet" href="<?= base_url() ?>assets/css/fonts.css">
	<!-- Brand palette + typography overrides — loaded last so it wins. -->
	<link rel="stylesheet" href="<?= base_url() ?>assets/css/pearland-brand.css?v=1">

	<!--DataTable-->
	<link rel="stylesheet" href="//cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">

	<!-- Select2 CSS CDN -->
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />


	<style>
		/* phone */
		@media (max-width: 767px) {
			.mob-me-all {
				display: none;
			}

			.ud-lhs-s2 {
				background-color: white;
			}
		}
	</style>
</head>

<body>
	<!-- START -->
	<section>
		<div class="str">
			<?php
			if (current_url() == base_url()) {
			?>
				<div class="hom-head">
				<?php
			} else { ?>
					<div>
					<?php } ?>
					<style>
						.mt5 {

							margin-top: 5px;

						}
					</style>
					<div class="hom-top">
						<div class="container">
							<div class="row">
								<div class="hom-nav  db-open ">
									<!--MOBILE MENU-->
									<a href="<?= base_url(); ?>" class="top-log">
										<img src="<?= base_url() ?>assets/images/pearland-logo.png" alt="" class="ic-logo">
									</a>
									<div class="mt30">
										<div class="menu">
											<h4><a href="#" data-toggle="modal" data-target="#categoriesModal" style="color: inherit; text-decoration: none;">All Categories</a></h4>
										</div>
										<div class="pop-menu">
											<div class="container">
												<div class="row"> <i class="material-icons clopme">close</i>
													<div class="pmenu-spri">
														<ul>
															<li>
																<a href="<?= base_url('all-category') ?>" class="act"><img src="<?= base_url() ?>assets/images/icon/shop.png">All Services</a>
															</li>
															<li>
																<a href="<?= base_url('event') ?>">

																	<img src="<?= base_url() ?>assets/images/icon/calendar.png">Events</a>

															</li>

															<li>

																<a href="<?= base_url('all_products') ?>">

																	<img src="<?= base_url() ?>assets/images/icon/cart.png">Products</a>

															</li>


															<li>

																<a href="<?= base_url('all_blog') ?>">

																	<img src="<?= base_url() ?>assets/images/icon/blog1.png">Blogs</a>

															</li>

															<li>
																<a href="<?= base_url('services') ?>">
																	<img src="<?= base_url() ?>assets/images/icon/blog1.png">Services</a>
															</li>

															<li>
																<a href="<?= base_url('coupons') ?>">
																	<img src="<?= base_url() ?>assets/images/icon/blog1.png">Coupons</a>
															</li>



														</ul>

													</div>

													<div class="pmenu-cat">

														<h4>All Categories</h4>

														<ul id="pg-resu">
															<?php
															// Use controller-passed categories
															if (isset($allCategories) && !empty($allCategories)) {																// Render categories from data (limit to 25 for mobile menu)
																$limited_categories = array_slice($allCategories, 0, 25);
																foreach ($limited_categories as $category) {
																	// Generate category URL
																	if (!empty($category->category_slug)) {
																		$category_url = $category->category_slug;
																	} else {
																		// Generate slug from category name
																		$category_url = strtolower(str_replace([' ', '&', '/'], ['-', 'and', '-'], $category->category_name));
																		$category_url = preg_replace('/[^a-z0-9\-]/', '', $category_url);
																		$category_url = preg_replace('/\-+/', '-', $category_url);
																		$category_url = trim($category_url, '-');
																	}

																	echo '<li><a href="' . base_url('user/all_listing/' . htmlspecialchars($category_url)) . '">' . htmlspecialchars($category->category_name) . '</a></li>';
																}
															} else {
																// Fallback categories only if no dynamic data available
																$fallback_categories = [
																	'business' => 'Business Services',
																	'food' => 'Food & Dining',
																	'health' => 'Health & Medical',
																	'automotive' => 'Automotive',
																	'shopping' => 'Shopping',
																	'real-estate' => 'Real Estate',
																	'education' => 'Education',
																	'travel' => 'Travel & Tourism',
																	'legal' => 'Legal Services',
																	'financial' => 'Financial Services',
																	'technology' => 'Technology',
																	'beauty' => 'Beauty & Spa',
																	'fitness' => 'Fitness & Gym',
																	'home-services' => 'Home Services',
																	'entertainment' => 'Entertainment',
																	'pets' => 'Pet Services',
																	'sports' => 'Sports & Recreation',
																	'arts' => 'Arts & Crafts',
																	'clothing' => 'Clothing & Fashion',
																	'photography' => 'Photography',
																	'music' => 'Music & Audio',
																	'construction' => 'Construction',
																	'agriculture' => 'Agriculture',
																	'transport' => 'Transportation',
																	'nonprofit' => 'Non-Profit'
																];

																foreach ($fallback_categories as $slug => $name) {
																	echo '<li><a href="' . base_url('user/all_listing/' . $slug) . '">' . $name . '</a></li>';
																}
															}
															?>
														</ul>

														<!-- Explore All Categories Button - Bottom Right -->
														<div style="text-align: right; margin-top: 20px; padding-top: 15px;">
															<a href="<?= base_url('all-category') ?>" class="waves-effect waves-light btn" style="background: #7bb73f; color: white; padding: 8px 20px; border-radius: 20px; text-decoration: none; font-weight: 500; transition: all 0.3s ease; display: inline-block; font-size: 13px;">
																<i class="material-icons" style="vertical-align: middle; margin-right: 3px; font-size: 16px;">explore</i>
																Explore All
															</a>
														</div>

													</div>

													<div class="dir-home-nav-bot">

														<ul>

															<li>A few reasons you’ll love Online Business Directory <span>Call us on: 281-482-2031</span>

															</li>

															<li><a href="/ads-summery" class="waves-effect waves-light btn-large"><i class="material-icons">font_download</i> Advertise with us</a>

															</li>

															<li>

																<a href="<?= base_url() ?>pricing-plan" class="waves-effect waves-light btn-large"> <i class="material-icons">store</i> Add your business</a>

															</li>

														</ul>

													</div>

												</div>

											</div>

										</div>

										<!--END MOBILE MENU-->

										<div class="top-ser">

											<form name="filter_form" id="filter_form" class="filter_form" method="GET" action="<?= base_url('search'); ?>">

												<ul>

													<li class="sr-sea">

														<input type="text" autocomplete="off" id="top-select-search" name="q" placeholder="Search for services and business...">

													</li>

													<li class="sbtn">

														<button type="submit" class="btn btn-success" id="top_filter_submit"><i class="material-icons">search</i>

														</button>

													</li>

												</ul>

											</form>



										</div>





										<?php



										if (!isset($_SESSION['logged_user']) && empty($_SESSION['logged_user'])) { ?>



											<ul class="bl">

												<!-- <li><div id="google_translate_element"></div></li> -->

												<li><a href="<?= base_url() ?>pricing-plan">Add business</a></li>



												<li><a href="<?= base_url() ?>login">Sign In</a></li>



												<li><a href="<?= base_url() ?>register">Create an account</a></li>



											</ul>



										<?php } else { ?>

											<!-- <ul class="bl">

                  	<li><div id="google_translate_element"></div></li>

                  </ul>	 -->

											<div class="al">

												<div class="head-pro">
													<?php
													// Get user information for header
													$header_user_name = 'User';
													$header_profile_img = base_url('assets/images/fallback_profile.svg'); // fallback to logo
													$header_join_date = '';

													// Check if CI session exists and user is logged in
													if (isset($this) && method_exists($this, 'session')) {
														// Use CodeIgniter session
														$session_data = $this->session->userdata('logged_user');
													} elseif (isset($_SESSION['logged_user']) && !empty($_SESSION['logged_user'])) {
														// Fallback to PHP session
														$session_data = $_SESSION['logged_user'];
													} else {
														$session_data = null;
													}

													if ($session_data && !empty($session_data)) {
														// Get user name
														if (isset($session_data['name']) && !empty($session_data['name'])) {
															$header_user_name = $session_data['name'];
														}

														// Get profile image with fallback to logo
														if (isset($session_data['profile_image']) && !empty($session_data['profile_image'])) {
															$header_profile_img = base_url($session_data['profile_image']);
														}

														// Get join date
														if (isset($session_data['user_created']) && !empty($session_data['user_created'])) {
															$header_join_date = date('d, M Y', strtotime($session_data['user_created']));
														}
													}
													?>

													<img src="<?= $header_profile_img ?>" alt="<?= htmlspecialchars($header_user_name) ?>" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;" onerror="this.src='<?= base_url('assets/images/pearland-logo.png') ?>'">
													<b>Profile by</b>
													<br>
													<h4><?= htmlspecialchars($header_user_name) ?></h4>

													<a href="#" class="fclick"></a>

												</div>

												<div class="db-menu">

													<ul>

														<li>

															<a href="<?= base_url('dashboard') ?>" class="db-lact">

																<img src="<?= base_url() ?>assets/images/icon/dbl1.png" alt="" />My Dashboard</a>

														</li>

														<li>

															<a href="<?= base_url('user-listing') ?>">

																<img src="<?= base_url() ?>assets/images/icon/dbl7.png" alt="" />All Listings</a>

														</li>


														<li>

															<a href="<?= base_url('user-events') ?>">

																<img src="<?= base_url() ?>assets/images/icon/dbl4.png" alt="" />Events</a>

														</li>

														<li>

															<a href="<?= base_url('blog-list') ?>">

																<img src="<?= base_url() ?>assets/images/icon/dbl10.png" alt="" />Blog posts</a>

														</li>

														<li>

															<a href="<?= base_url('logout') ?>">

																<img src="<?= base_url() ?>assets/images/icon/dbl12.png" alt="" />Log Out</a>

														</li>

													</ul>

												</div>

											</div>



										<?php } ?>

									</div>

									<script type="text/javascript">
										/*function googleTranslateElementInit() {

									  new google.translate.TranslateElement({pageLanguage: 'en'}, 'google_translate_element');

									}*/

										// Header search functionality
										$(document).ready(function() {
											// Handle search form submission
											$('#filter_form').on('submit', function(e) {
												var searchValue = $('#top-select-search').val().trim();
												if (searchValue === '') {
													e.preventDefault();
													alert('Please enter a search term');
													return false;
												}
											});

											// Handle enter key press in search input
											$('#top-select-search').on('keypress', function(e) {
												if (e.which === 13) { // Enter key
													e.preventDefault();
													var searchValue = $(this).val().trim();
													if (searchValue !== '') {
														$('#filter_form').submit();
													} else {
														alert('Please enter a search term');
													}
												}
											});

											// Handle search button click
											$('#top_filter_submit').on('click', function(e) {
												var searchValue = $('#top-select-search').val().trim();
												if (searchValue === '') {
													e.preventDefault();
													alert('Please enter a search term');
													return false;
												}
												// If search value exists, let the form submit naturally
											});
										});
									</script>



									<!-- <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script> -->





									<!--MOBILE MENU-->

									<div class="mob-menu">

										<div class="mob-me-ic"><i class="material-icons">menu</i>

										</div>

										<div class="mob-me-all">

											<div class="mob-me-clo"><i class="material-icons">close</i>

											</div>

											<div class="mv-pro ud-lhs-s1">
												<img src="<?= $header_profile_img ?>" alt="<?= htmlspecialchars($header_user_name) ?>" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;" onerror="this.src='<?= base_url('assets/images/pearland-logo.png') ?>'">

												<h4><?= htmlspecialchars($header_user_name) ?></h4>

												<?php if ($header_join_date): ?>
													<b>Join on <?= $header_join_date ?></b>
												<?php else: ?>
													<b>Welcome!</b>
												<?php endif; ?>

											</div>

											<div class="mv-pro-menu ud-lhs-s2">

												<ul>

													<li>

														<a href="<?= base_url() ?>dashboard" class="">

															<img src="<?= base_url() ?>assets/images/icon/dbl1.png" alt="" />My Dashboard</a>

													</li>

													<li>

														<a href="<?= base_url() ?>user_listing" class="">

															<img src="<?= base_url() ?>assets/images/icon/shop.png" alt="" />All Listings</a>

													</li>

													<li>

														<a href="<?= base_url() ?>add_listing_start">

															<img src="<?= base_url() ?>assets/images/icon/dbl3.png" alt="" />Add New Listing</a>

													</li>

													<li>
														<a href="<?= base_url() ?>user_enquiry" class=""><img src="<?= base_url() ?>assets/images/icon/tick.png" alt="" />Lead enquiry</a>

													</li>

													<li>

														<a href="<?= base_url() ?>user_products" class="">

															<img src="<?= base_url() ?>assets/images/icon/cart.png" alt="" />All Products</a>

													</li>

													<li>

														<a href="<?= base_url() ?>user_events" class="">

															<img src="<?= base_url() ?>assets/images/icon/calendar.png" alt="" />Events</a>

													</li>

													<li>

														<a href="<?= base_url() ?>user_blog_posts" class="">

															<img src="<?= base_url() ?>assets/images/icon/blog1.png" alt="" />Blog posts</a>

													</li>



													<li>

														<a href="<?= base_url() ?>my_profile" class="">

															<img src="<?= base_url() ?>assets/images/icon/dbl6.png" alt="" />My Profile</a>

													</li>



													<li>

														<a href="<?= base_url() ?>logout">

															<img src="<?= base_url() ?>assets/images/icon/dbl12.png" alt="" />Log Out</a>

													</li>

												</ul>

											</div>

										</div>

									</div>

									<!--END MOBILE MENU-->

								</div>

							</div>

						</div>

					</div>



					</div>

				</div>

	</section>

	<!-- END -->

	<section class="news-top-menu">

		<div class="container">

			<div class="row">

				<div class="news-menu">

					<ul>

						<li><a href="<?= base_url() ?>" class="act">Home</a></li>

						<li><a href="<?= base_url() ?>all-listing" class="">Directory</a></li>

<?php if (site_setting('service_events_enabled','1') !== '0'): ?><li><a href="<?= base_url() ?>event" class="">Events</a></li><?php endif; ?>

<?php if (site_setting('service_news_enabled','1') !== '0'): ?><li><a href="<?= base_url() ?>news" class="">News</a></li><?php endif; ?>

<?php if (site_setting('service_videos_enabled','1') !== '0'): ?><li><a href="<?= base_url() ?>videos" class="">Videos</a></li><?php endif; ?>

<?php if (site_setting('service_classifieds_enabled','1') !== '0'): ?><li><a href="<?= base_url() ?>classifieds" class="">Classifieds</a></li><?php endif; ?>

						<li><a href="<?= base_url() ?>user/all_listing/sports-clubs" class="">Sports</a></li>

						<li><a href="<?= base_url() ?>user/all_listing/health-services" class="">Health</a></li>



					</ul>

				</div>

			</div>

		</div>

	</section>

	<script>
		// select the elements
		const mobIcon = document.querySelector('.mob-me-ic');
		const sidebar = document.querySelector('.ud-lhs-s2');

		// by default sidebar hidden
		sidebar.style.display = 'none';

		// toggle on click
		mobIcon.addEventListener('click', function() {
			if (sidebar.style.display === 'none') {
				sidebar.style.display = 'block';
			} else {
				sidebar.style.display = 'none';
			}
		});
	</script>