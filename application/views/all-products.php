<style>
	.products_wrapper .all-pro-box {
		border-radius: 10px;
	}

	.products_wrapper .all-pro-img {
		padding: 0px;
	}

	.products_wrapper .all-pro-img img {
		height: 200px;
		object-fit: cover;
		border-top-right-radius: 10px;
		border-top-left-radius: 10px;
	}
</style>

<!-- START -->
<section>
	<div class="all-list-bre all-pro-bre" style="margin-top: 0px !important;">
		<div class="container sec-all-list-bre">
			<div class="row">
				<h1>All Products</h1>
				<ul>
					<li><a href="/">Home</a>
					</li>
					<li><a href="all-products">All Products</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</section>
<!-- END -->
<!-- START -->
<section>
	<div class="all-listing all-products">
		<!--FILTER ON MOBILE VIEW-->
		<div class="fil-mob fil-mob-act">
			<h4>Product filters <i class="material-icons">filter_list</i></h4>
		</div>
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="p411-adrow" style="margin-bottom:18px;">
						<?php $slot = $ad_slots['top'] ?? '';
						include APPPATH . 'views/partials/ad_slot.php'; ?>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-3 fil-mob-view">
					<form id="productFilterForm" method="get" action="<?= base_url('all-products') ?>">
						<div class="filt-com lhs-cate">
							<h4>Categories</h4>
							<div class="dropdown">
								<select name="category" id="cat_check" class="chosen-select form-control" onchange="document.getElementById('productFilterForm').submit();">
									<option selected value="">Select Category</option>
									<?php if (!empty($categories)) {
										$selected_slug = isset($_GET['category']) ? $_GET['category'] : '';
										foreach ($categories as $cat) { ?>
											<option value="<?= htmlspecialchars($cat->category_slug) ?>" <?= $selected_slug == $cat->category_slug ? 'selected' : '' ?>><?= htmlspecialchars($cat->category_name) ?></option>
									<?php }
									} ?>
								</select>
							</div>
						</div>
						<div class="filt-com sub_cat_section pro-fil-sub">
							<h4>Sub category</h4>
							<ul>
								<?php if (!empty($subcategories)) {
									$selected_subcats = isset($_GET['sub_category_id']) ? (array)$_GET['sub_category_id'] : array();
									foreach ($subcategories as $subcat) { ?>
										<li>
											<div class="chbox">
												<input type="checkbox" class="sub_cat_check" name="sub_category_id[]" value="<?= $subcat->sub_category_id ?>" id="subcat_<?= $subcat->sub_category_id ?>" <?= in_array($subcat->sub_category_id, $selected_subcats) ? 'checked' : '' ?> onchange="document.getElementById('productFilterForm').submit();" />
												<label for="subcat_<?= $subcat->sub_category_id ?>"><?= htmlspecialchars($subcat->sub_category_name) ?></label>
											</div>
										</li>
								<?php }
								} ?>
							</ul>
						</div>
						<div class="filt-com pro-fil-pri">
							<h4>Price</h4>
							<ul>
								<?php
								$priceOptions = [
									['1000+', 'Above $1000'],
									['501-1000', '$501 - $1000'],
									['251-500', '$251 - $500'],
									['101-250', '$101 - $250'],
									['0-100', 'Below $100'],
								];
								$selected_price = isset($_GET['price']) ? $_GET['price'] : '';
								foreach ($priceOptions as $idx => $opt) {
								?>
									<li>
										<div class="chbox">
											<input style="display: none;" type="radio" name="price" value="<?= $opt[0] ?>" id="price_check<?= $idx ?>" <?= $selected_price == $opt[0] ? 'checked' : '' ?> onchange="document.getElementById('productFilterForm').submit();" />
											<label for="price_check<?= $idx ?>"><?= $opt[1] ?></label>
										</div>
									</li>
								<?php } ?>
							</ul>
						</div>
						<div class="filt-com pro-fil-dis">
							<h4>Discounts</h4>
							<ul>
								<?php
								$discountOptions = [
									['70+', 'Above 70%'],
									['51-70', '51% - 70%'],
									['26-50', '26% - 50%'],
									['11-25', '11% - 25%'],
									['0-10', 'Below 10%'],
								];
								$selected_discount = isset($_GET['discount']) ? $_GET['discount'] : '';
								foreach ($discountOptions as $idx => $opt) {
								?>
									<li>
										<div class="chbox">
											<input style="display: none;" type="radio" name="discount" value="<?= $opt[0] ?>" id="discount_check<?= $idx ?>" <?= $selected_discount == $opt[0] ? 'checked' : '' ?> onchange="document.getElementById('productFilterForm').submit();" />
											<label for="discount_check<?= $idx ?>"><?= $opt[1] ?></label>
										</div>
									</li>
								<?php } ?>
							</ul>
						</div>
						<div style="margin-top: 15px; padding: 15px; text-align: left;">
							<a href="<?= base_url('all-products') ?>" class="btn btn-warning" style="padding: 5px 15px;">Clear Filter</a>
						</div>
					</form>
					<?php
					/*
					 * Two real ad zones. What was here: <img src="images/ads/ads1.jpg">
					 * — a path with no `assets/` prefix, so it 404'd on every request
					 * — inside a link to the themeforest page the theme was bought
					 * from. Two broken images advertising someone else's template.
					 */
					?>
					<div class="filt-com lhs-ads">
						<?php $slot = $ad_slots['side_1'] ?? '';
						$slot_creative = 'house-ad-burgundy';
						include APPPATH . 'views/partials/ad_slot.php'; ?>

						<?php $slot = $ad_slots['side_2'] ?? '';
						$slot_fallback_partial = 'banner_design_promo.php';
						$slot_creative = 'banner-design';
						include APPPATH . 'views/partials/ad_slot.php'; ?>

						<?php // 295x600, held back until sold. ?>
						<?php $slot = $ad_slots['side_sky'] ?? '';
						$slot_fallback = false;
						include APPPATH . 'views/partials/ad_slot.php'; ?>
					</div>
					<!--END-->
				</div>
				<div class="col-md-9">
					<div class="all-list-sh all-product-total products_wrapper">
						<ul>
							<?php
							$not_found = false;
							$products = $AllProduct;
							if (is_array($AllProduct) && isset($AllProduct['not_found'])) {
								$not_found = $AllProduct['not_found'];
								$products = $AllProduct['products'];
							}
							if (!empty($products)) {
								foreach ($products as $product) {
									$images = json_decode($product->gallery_image);
									$imgSrc = (!empty($images) && !empty($images[0])) ? base_url($images[0]) : base_url('assets/images/no_photo.jpg');
							?>
									<li>
										<div class="all-pro-box">
											<div class="all-pro-img">
												<img src="<?= $imgSrc ?>">
											</div>
											<div class="all-pro-txt">
												<h4><?= htmlspecialchars($product->product_name); ?></h4>
												<span class="pri"><b class="pro-off">$<?= htmlspecialchars($product->product_price); ?></b> <?= (!empty($product->product_price_offer)) ?  htmlspecialchars($product->product_price_offer) . "%off" : '' ?> </span>
												<div class="links"> <a href="#" data-toggle="modal" data-target="#quote">Get quote</a>
												</div>
											</div>
											<a href="<?= base_url('user/product-details/' . $product->product_slug); ?>" class="pro-view-full"></a>
										</div>
									</li>
								<?php }
							}
							if ($not_found || empty($products)) {
								?>
								<li style="width:100%;text-align:center;padding:40px 0;color:#888;font-size:20px;list-style:none;">No products found for the selected filter(s).</li>
							<?php } ?>
						</ul>
						<?php if (!empty($pagination_links)): ?>
							<div class="pagination-wrapper" style="margin: 40px 0; text-align: center; clear: both; width: 100%; padding-top: 30px;">
								<div class="pagination-container" style="display: inline-block; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; max-width: 100%;">
									<div class="pagination-info" style="margin-bottom: 15px; color: #718096; font-size: 14px;">
										<i class="material-icons" style="vertical-align: middle; font-size: 18px; margin-right: 5px;">pages</i>
										Navigate through products
									</div>
									<div class="pagination-links">
										<?= $pagination_links; ?>
									</div>
								</div>
							</div>
							<style>
								.pagination-wrapper .pagination {
									margin: 0;
									display: flex;
									justify-content: center;
									align-items: center;
									/* flex-wrap: wrap; */
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
									white-space: nowrap;
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

						<div class="p411-adrow" style="margin:22px 0 0;">
							<?php $slot = $ad_slots['inline'] ?? '';
							$slot_creative = 'house-ad-cream';
							include APPPATH . 'views/partials/ad_slot.php'; ?>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-12">
					<div class="p411-adrow" style="margin-top:10px;">
						<?php $slot = $ad_slots['bottom'] ?? '';
						include APPPATH . 'views/partials/ad_slot.php'; ?>
					</div>
				</div>
			</div>

		</div>
	</div>
	</div>
</section>
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
		<form name="home_slide_enquiry_form" id="home_slide_enquiry_form" method="post" action="<?= base_url('contact-submit') ?>"><input type="hidden" name="subject" value="Help &amp; Support slide-out">
			<input type="hidden" class="form-control" name="listing_id" value="0" placeholder="" required>
			<input type="hidden" class="form-control" name="listing_user_id" value="0" placeholder="" required>
			<input type="hidden" class="form-control" name="enquiry_sender_id" value="" placeholder="" required>
			<input type="hidden" class="form-control" name="enquiry_source" value="Website" placeholder="" required>
			<div class="form-group">
				<input type="text" name="fullName" value="" required="required" class="form-control" placeholder="Enter name*">
			</div>
			<div class="form-group">
				<input type="email" class="form-control" placeholder="Enter email*" required="required" value="" name="email" pattern="^[\w]{1,}[\w.+-]{0,}@[\w-]{2,}([.][a-zA-Z]{2,}|[.][\w-]{2,}[.][a-zA-Z]{2,})$" title="Invalid email address">
			</div>
			<div class="form-group">
				<input type="text" class="form-control" value="" name="phone" placeholder="Enter mobile number *" pattern="[0-9+()\- ]{7,20}" title="Enter a phone number (7–20 characters, digits/+/()/-/space allowed)" required="">
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
				<textarea class="form-control" rows="3" name="message" placeholder="Enter your query or message"></textarea>
			</div>
			<input type="hidden" id="source">
			<button type="submit" id="home_slide_enquiry_submit" name="home_slide_enquiry_submit" class="btn btn-primary">Submit Requirements</button>
		</form>
	</div>
</div>
<!-- END -->

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

						<form method="post" action="<?= base_url('user/add-enquiry/product_details/' . $product->product_id); ?>">

							<!-- Hidden fields for enquiry data -->
							<input type="hidden" name="listing_id" value="">
							<input type="hidden" name="event_id" value="">
							<input type="hidden" name="blog_id" value="">
							<input type="hidden" name="product_id" value="<?= $product->product_id; ?>">
							<input type="hidden" name="receiving_user_id" value="<?= $product->user_id; ?>">
							<input type="hidden" name="enquiry_sender_id" value="<?= isset($logged_id) ? $logged_id : ''; ?>">
							<input type="hidden" name="enquiry_source" value="<?= $product->product_name; ?>">
							<input type="hidden" name="enquiry_type" value="product">

							<div class="form-group">

								<input type="text" class="form-control" name="fullName" placeholder="Enter name*" required>

							</div>

							<div class="form-group">

								<input type="email" class="form-control" name="email" placeholder="Enter email*" pattern="^[\w]{1,}[\w.+-]{0,}@[\w-]{2,}([.][a-zA-Z]{2,}|[.][\w-]{2,}[.][a-zA-Z]{2,})$" title="Invalid email address" required>

							</div>

							<div class="form-group">

								<input type="text" class="form-control" name="phone" placeholder="Enter mobile number *" pattern="[0-9]{10,15}" title="Please enter a valid phone number (10-15 digits)" required>

							</div>

							<div class="form-group">

								<textarea class="form-control" rows="3" name="message" placeholder="Enter your query or message"></textarea>

							</div>

							<button type="submit" class="btn btn-primary">Submit</button>

						</form>

					</div>

				</div>

			</div>

		</div>

	</div>

</section>