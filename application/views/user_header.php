<!doctype html>
<html lang="en">

<head>
	<title>Pearland 411</title>
	<!--== META TAGS ==-->
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<meta name="theme-color" content="#76cef1" />
	<meta property="og:image" content="<?= base_url() ?>assets/images/home/logo-b.png" />
	<meta name="description" content="Kick start your online directory with BizBook Directory Template. Its comes with ultimate features like listings, events, blog, community. Try Bizbook directory now!">
	<meta name="keyword" content="bizbook directory, bizbook directory template, business directory template, php directory template, functional php template, php listing template, php dirctory database, local listing php template">
	<!--== FAV ICON(BROWSER TAB ICON) ==-->
	<link rel="shortcut icon" href="<?= base_url() ?>assets/images/fav.ico" type="image/x-icon">
	<!--== GOOGLE FONTS ==-->
	<link href="https://fonts.googleapis.com/css?family=Oswald:700|Source+Sans+Pro:300,400,600,700&display=swap" rel="stylesheet">
	<!--== WEB ICON FONTS ==-->
	<link rel="preload" as="font" href="css/icon.woff2" type="font/woff2" crossorigin="anonymous">
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	<!--== CSS FILES ==-->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">



	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">



	<link rel="stylesheet" href="<?= base_url() ?>assets/css/style.css">

	<!-- Select2 CSS CDN -->
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

	<link rel="stylesheet" href="<?= base_url() ?>assets/css/fonts.css">
	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->
	<!--    Google Analytics Code Starts-->
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-90614514-2"></script>
	<script>
		window.dataLayer = window.dataLayer || [];

		function gtag() {
			dataLayer.push(arguments);
		}
		gtag('js', new Date());

		gtag('config', 'UA-90614514-2');
	</script>
	<!--    Google Analytics Code Ends-->
</head>

<body>
	<!-- Preloader -->
	<div id="preloader">
		<div id="status">&nbsp;</div>
	</div>
	<!-- START -->
	<section>
		<div class="str">
			<div>
				<div class="hom-top">
					<div class="container">
						<div class="row">
							<div class="hom-nav  db-open ">
								<!--MOBILE MENU-->
								<!--<div class="menu">
                                <i class="material-icons mopen">menu</i>
                            </div>-->
								<a href="index.html" class="top-log">
									<img src="<?= base_url() ?>assets/images/home/logo-b.png" alt="" class="ic-logo">
								</a>
								<div class="menu">
									<h4>All Category</h4>
								</div>
								<div class="pop-menu">
									<div class="container">
										<div class="row"> <i class="material-icons clopme">close</i>
											<div class="pmenu-spri">
												<ul>
													<li>
														<a href="all-category.html" class="act">
															<img src="<?= base_url() ?>assets/images/icon/shop.png">All Services</a>
													</li>
													<li>
														<a href="events.html">
															<img src="<?= base_url() ?>assets/images/icon/calendar.png">Events</a>
													</li>
													<li>
														<a href="all-products.html">
															<img src="<?= base_url() ?>assets/images/icon/cart.png">Products</a>
													</li>
													<li>
														<a href="coupons.html">
															<img src="<?= base_url() ?>assets/images/icon/coupons.png">Coupon & deals</a>
													</li>
													<li>
														<a href="blog-posts.html">
															<img src="<?= base_url() ?>assets/images/icon/blog1.png">Blogs</a>
													</li>
													<li>
														<a href="community.html">
															<img src="<?= base_url() ?>assets/images/icon/11.png">Community</a>
													</li>
												</ul>
											</div>
											<div class="pmenu-cat">
												<h4>All Categories</h4>
												<input type="text" id="pg-sear" placeholder="Search category">
												<ul id="pg-resu">
													<li><a href="all-listing.html">Wedding halls - <span>03</span></a>
													</li>
													<li><a href="all-listing.html">Hotel & Food - <span>01</span></a>
													</li>
													<li><a href="all-listing.html">Pet shop - <span>12</span></a>
													</li>
													<li><a href="all-listing.html">Digital Products - <span>31</span></a>
													</li>
													<li><a href="all-listing.html">Spa and Facial - <span>10</span></a>
													</li>
													<li><a href="all-listing.html">Real Estate - <span>23</span></a>
													</li>
													<li><a href="all-listing.html">Sports - <span>05</span></a>
													</li>
													<li><a href="all-listing.html">Education - <span>02</span></a>
													</li>
													<li><a href="all-listing.html">Electricals - <span>05</span></a>
													</li>
													<li><a href="all-listing.html">Automobiles - <span>06</span></a>
													</li>
													<li><a href="all-listing.html">Transportation - <span>02</span></a>
													</li>
													<li><a href="all-listing.html">Hospitals - <span>08</span></a>
													</li>
													<li><a href="all-listing.html">Hotels And Resorts - <span>09</span></a>
													</li>
												</ul>
											</div>
											<div class="dir-home-nav-bot">
												<ul>
													<li>A few reasons you’ll love Online Business Directory <span>Call us on: +01 6214 6548</span>
													</li>
													<li><a href="post-your-ads.html.html" class="waves-effect waves-light btn-large"><i class="material-icons">font_download</i> Advertise with us</a>
													</li>
													<li>
														<a href="pricing-details.html.html" class="waves-effect waves-light btn-large"> <i class="material-icons">store</i> Add your business</a>
													</li>
												</ul>
											</div>
										</div>
									</div>
								</div>
								<!--END MOBILE MENU-->
								<div class="top-ser">
									<form name="filter_form" id="filter_form" class="filter_form">
										<ul>
											<li class="sr-sea">
												<!--                                            <input type="text"  id="-->
												<!--" class="autocomplete"-->
												<!--                                                   placeholder="-->
												<!--">-->
												<input type="text" autocomplete="off" id="top-select-search" placeholder="Search for services and business...">
												<ul id="tser-res1" class="tser-res tser-res2">
													<li>
														<div>
															<h4>The Royal Spa Center For Womens</h4>
															<span>No:2, 4th Avenue, Newyork, USA, Near to Airport</span>
															<a href="all-listing.html"></a>
														</div>
													</li>
													<li>
														<div>
															<h4>Real estate</h4>
															<span>Chennai, India</span>
															<a href="all-listing.html"></a>
														</div>
													</li>
													<li>
														<div>
															<h4>Education</h4>
															<span>Schools, university, colleges, online classes, tution centers, distance education..</span>
															<a href="all-listing.html"></a>
														</div>
													</li>

												</ul>
									</form>
								</div>
								<div class="al">
									<div class="head-pro">
										<img src="<?= base_url() ?>assets/images/user/62736rn53themes.png" alt=""> <b>Profile by</b>
										<br>
										<h4>Rn53 Themes</h4>
										<a href="dashboard.html" class="fclick"></a>
									</div>
									<div class="db-menu">
										<ul>
											<li>
												<a href="<?= base_url() ?>dashboard" class="db-lact">
													<img src="<?= base_url() ?>assets/images/icon/dbl1.png" alt="" />My Dashboard</a>
											</li>
											<li>
												<a href="<?= base_url() ?>user_listing">
													<img src="<?= base_url() ?>assets/images/icon/dbl7.png" alt="" />All Listings</a>
											</li>

											<li>
												<a href="<?= base_url() ?>user/enquiries">
													<img src="<?= base_url() ?>assets/images/icon/dbl14.png" alt="" />Lead enquiry</a>
											</li>
											<li>
												<a href="<?= base_url() ?>user-events">
													<img src="<?= base_url() ?>assets/images/icon/dbl4.png" alt="" />Events</a>
											</li>
											<li>
												<a href="<?= base_url() ?>blog-list">
													<img src="<?= base_url() ?>assets/images/icon/dbl10.png" alt="" />Blog posts</a>
											</li>
											<li>
												<a href="db-review.html">
													<img src="<?= base_url() ?>assets/images/icon/dbl13.png" alt="" />Reviews</a>
											</li>
											<li>
												<a href="/user/my_account">
													<img src="<?= base_url() ?>assets/images/icon/dbl6.png" alt="" />My Profile</a>
											</li>
											<li>
												<a href="#">
													<img src="<?= base_url() ?>assets/images/icon/dbl12.png" alt="" />Log Out</a>
											</li>
										</ul>
									</div>
								</div>
								<!--MOBILE MENU-->
								<div class="mob-menu">
									<div class="mob-me-ic"><i class="material-icons">menu</i>
									</div>
									<div class="mob-me-all">
										<div class="mob-me-clo"><i class="material-icons">close</i>
										</div>
										<div class="mv-pro ud-lhs-s1">
											<img src="<?= base_url() ?>assets/images/user/62736rn53themes.png" alt="">
											<h4>Rn53 Themes</h4>
											<b>Join on 26, Mar 2021</b>
										</div>
										<div class="mv-pro-menu ud-lhs-s2">
											<ul>
												<li>
													<a href="<?= base_url() ?>dashboard" class="">
														<img src="<?= base_url() ?>assets/images/icon/dbl1.png" alt="" />My Dashboard</a>
												</li>
												<li>
													<a href="<?= base_url() ?>user_listing">
														<img src="<?= base_url() ?>assets/images/icon/dbl7.png" alt="" />All Listings</a>
												</li>

												<li>
													<a href="<?= base_url() ?>user/enquiries">
														<img src="<?= base_url() ?>assets/images/icon/dbl14.png" alt="" />Lead enquiry</a>
												</li>
												<li>
													<a href="<?= base_url() ?>user-events">
														<img src="<?= base_url() ?>assets/images/icon/dbl4.png" alt="" />Events</a>
												</li>
												<li>
													<a href="<?= base_url() ?>blog-list">
														<img src="<?= base_url() ?>assets/images/icon/dbl10.png" alt="" />Blog posts</a>
												</li>
												<li>
													<a href="db-review.html">
														<img src="<?= base_url() ?>assets/images/icon/dbl13.png" alt="" />Reviews</a>
												</li>
												<li>
													<a href="/user/my_account">
														<img src="<?= base_url() ?>assets/images/icon/dbl6.png" alt="" />My Profile</a>
												</li>
												<li>
													<a href="#">
														<img src="<?= base_url() ?>assets/images/icon/dbl12.png" alt="" />Log Out</a>
												</li>
											</ul>
										</div>
										<div class="mv-cate">
											<h4>All Categories</h4>
											<ul>
												<li> <a href="all-listing.html">Wedding halls</a>
												</li>
												<li> <a href="all-listing.html">Hotel & Food</a>
												</li>
												<li> <a href="all-listing.html">Pet shop</a>
												</li>
												<li> <a href="all-listing.html">Digital Products</a>
												</li>
												<li> <a href="all-listing.html">Spa and Facial</a>
												</li>
												<li> <a href="all-listing.html">Real Estate</a>
												</li>
												<li> <a href="all-listing.html">Sports</a>
												</li>
												<li> <a href="all-listing.html">Education</a>
												</li>
												<li> <a href="all-listing.html">Electricals</a>
												</li>
												<li> <a href="all-listing.html">Automobiles</a>
												</li>
												<li> <a href="all-listing.html">Transportation</a>
												</li>
												<li> <a href="all-listing.html">Hospitals</a>
												</li>
												<li> <a href="all-listing.html">Hotels And Resorts</a>
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
	<!-- START -->
	<!--PRICING DETAILS-->
	<section class=" ud">
		<div class="ud-inn">
			<!--LEFT SECTION-->
			<div class="ud-lhs">
				<div class="ud-lhs-s1">
					<img src="<?= base_url() ?>assets/images/user/62736rn53themes.png" alt="">
					<h4>Rn53 Themes</h4>
					<b>Join on 26, Mar 2021</b>
					<a class="ud-lhs-view-pro" target="_blank" href="profile.html">My profile</a>
				</div>
				<div class="ud-lhs-s2">
					<ul>
						<li>
							<a href="<?= base_url() ?>dashboard" class="">
								<img src="<?= base_url() ?>assets/images/icon/dbl1.png" alt="" />My Dashboard</a>
						</li>
						<li>
							<a href="<?= base_url() ?>user_listing" class="db-lact">
								<img src="<?= base_url() ?>assets/images/icon/shop.png" alt="" />All Listings</a>
						</li>
						<li>
							<a href="<?= base_url() ?>user_enquiry" class="">
								<img src="<?= base_url() ?>assets/images/icon/tick.png" alt="" />Lead enquiry</a>
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
							<a href="<?= base_url() ?>user_coupons" class="">
								<img src="<?= base_url() ?>assets/images/icon/coupons.png" alt="" />Coupons</a>
						</li>
						<li>
							<a href="<?= base_url() ?>user/my_account" class="">
								<img src="<?= base_url() ?>assets/images/icon/dbl6.png" alt="" />My Profile</a>
						</li>
						<li>
							<a href="#">
								<img src="<?= base_url() ?>assets/images/icon/dbl12.png" alt="" />Log Out</a>
						</li>
					</ul>
				</div>
			</div>