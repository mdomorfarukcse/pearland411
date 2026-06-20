	<!-- START -->
	<span class="btn-ser-need-ani">Help & Support</span>
	<div class="ani-quo-form"> <i class="material-icons ani-req-clo">close</i>
		<div class="tit">
			<h3>What service do you need? <span>Pearland will help you</span></h3>
		</div>
		<div class="hom-col-req">
			<?php $__hs = $this->session->flashdata('contact_success'); if (!empty($__hs)): ?>
				<div class="log"><p><?= e($__hs) ?></p></div>
			<?php endif; ?>
			<?php $__he = $this->session->flashdata('contact_error'); if (!empty($__he)): ?>
				<div class="alert alert-danger"><p><?= e($__he) ?></p></div>
			<?php endif; ?>

			<form name="home_slide_enquiry_form" id="home_slide_enquiry_form"
				  method="post" action="<?= base_url('contact-submit') ?>">
				<input type="hidden" name="subject" value="Help &amp; Support slide-out">

				<div class="form-group">
					<label for="hs_name" style="position:absolute;left:-9999px;">Your name</label>
					<input id="hs_name" type="text" name="fullName" required class="form-control" placeholder="Enter name*">
				</div>
				<div class="form-group">
					<label for="hs_email" style="position:absolute;left:-9999px;">Email</label>
					<input id="hs_email" type="email" name="email" required class="form-control"
						   placeholder="Enter email*"
						   pattern="^[\w]{1,}[\w.+-]{0,}@[\w-]{2,}([.][a-zA-Z]{2,}|[.][\w-]{2,}[.][a-zA-Z]{2,})$"
						   title="Invalid email address">
				</div>
				<div class="form-group">
					<label for="hs_phone" style="position:absolute;left:-9999px;">Phone</label>
					<input id="hs_phone" type="tel" name="phone" class="form-control"
						   placeholder="Enter mobile number *"
						   pattern="[0-9+()\- ]{7,20}"
						   title="Enter a phone number (7–20 characters, digits/+/()/-/space allowed)" required>
				</div>
				<div class="form-group">
					<label for="hs_msg" style="position:absolute;left:-9999px;">Message</label>
					<textarea id="hs_msg" class="form-control" rows="3" name="message" required
							  placeholder="Enter your query or message"></textarea>
				</div>
				<button type="submit" id="home_slide_enquiry_submit" class="btn btn-primary">
					Submit Requirements
				</button>
			</form>
		</div>
	</div>
	<!-- END -->

	<!-- Site-wide footer banner ad slot -->
	<section style="background:#f9fafb;padding:24px 0;">
		<div class="container">
			<?php $slot = 'footer-banner'; include APPPATH . 'views/partials/ad_slot.php'; ?>
		</div>
	</section>

	<!-- START -->
	<section>
		<div class="full-bot-book">
			<div class="container">
				<div class="row">
					<div class="bot-book">
						<div class="col-md-2 bb-img">
							<img src="<?= base_url() ?>assets/images/idea.png" alt="">
						</div>
						<div class="col-md-7 bb-text">
							<h4>#1 Business Directory and Service Provider</h4>
							<p>A platform that connects businesses and service providers, helping users easily find trusted local services in one place.</p>
						</div>
						<div class="col-md-3 bb-link"> <a href="/pricing-plan">Add my business</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- END -->
	<section class=" wed-hom-footer">
		<div class="container">
			<div class="row foot-supp">
				<h2><span>Free support:</span> 281-482-2031 &nbsp;&nbsp;|&nbsp;&nbsp; <span>Email:</span>info@pearland411.com</h2>
			</div>
			<div class="row wed-foot-link">
				<div class="col-md-4 foot-tc-mar-t-o">
					<h4>Top Category</h4>
					<ul>
						<li><a href="/all_listing/clothing-retail">Clothing-Retail</a>
						</li>
						<li><a href="/all_listing/tailors">Tailors</a>
						</li>
						<li><a href="/all_listing/glass-circles-other-special-shapes">Glass-Circles & Other Special Shapes</a>
						</li>
						<li><a href="/all_listing/apartments">Apartments</a>
						</li>
						<li><a href="/all_listing/chemicals-manufacturers">Chemicals-Manufacturers</a>
						</li>
						<li><a href="/all_listing/attorneys">Attorneys</a>
						</li>
						<li><a href="/all_listing/burglar-alarm-systems-whls">Burglar Alarm Systems (Whls)</a>
						</li>
						<li><a href="/all_listing/pipe-new-whls">Pipe New (Whls)</a>
						</li>
					</ul>
				</div>
				<div class="col-md-4">
					<!-- <h4>Trending Category</h4> -->
					<p>Address: Pearland, Texas, USA</p>
					<p>Phone: <a href="tel:281-482-2031">281-482-2031</a>
					</p>
					<p>Email: <a href="mailto:sales@pearland411.com">sales@pearland411.com</a>
					</p>
					<!--
						Social icons are hidden until the brand provides real
						profile URLs. Re-enable by setting the hrefs below and
						removing the surrounding HTML comment. Pointing them at
						the homepage as a placeholder confused visitors (clicking
						a Facebook icon and landing on the site home).
					-->
					<!--
					<a target="_blank" rel="noopener" href="https://www.facebook.com/YOUR_PROFILE">
						<img src="<?= base_url() ?>assets/images/social/1.png" alt="Facebook">
					</a>
					<a target="_blank" rel="noopener" href="https://twitter.com/YOUR_HANDLE">
						<img src="<?= base_url() ?>assets/images/social/2.png" alt="Twitter / X">
					</a>
					<a target="_blank" rel="noopener" href="https://www.instagram.com/YOUR_HANDLE">
						<img src="<?= base_url() ?>assets/images/social/3.png" alt="Instagram">
					</a>
					<a target="_blank" rel="noopener" href="https://www.linkedin.com/company/YOUR_COMPANY">
						<img src="<?= base_url() ?>assets/images/social/4.png" alt="LinkedIn">
					</a>
					-->
				</div>
				<div class="col-md-4">
					<h4>HELP &amp; SUPPORT</h4>
					<ul>
						<li><a href="/about-us">About us</a>
						</li>
						<li><a href="/faq">FAQ</a>
						</li>
						<li><a href="/privacy-policy">Privacy Policy</a>
						</li>
						<li><a href="/terms-of-use">Terms of Use</a>
						</li>

						<li><a href="/contact-us">Contact us</a>
						</li>
						<!--                    <li><a href="#">-->
						<!--</a>-->
						<!--                    </li>-->
					</ul>
				</div>
			</div>

		</div>
	</section>
	<!-- START -->
	<section>
		<div class="cr">
			<div class="container">
				<div class="row">
					<p>Copyright © 2025-2035 <a href="<?= base_url() ?>">Pearland411</a>. Proudly powered by <a href="https://www.spacecentersystems.com/" target="_blank" rel="noopener">Space Center Systems</a>
					</p>
				</div>
			</div>
		</div>
	</section>
	<!-- END -->
	<!-- START -->
	<div class="fqui-menu">
		<ul>
			<li>
				<a href="index.html">
					<img src="<?= base_url() ?>assets/images/icon/home.png">Home</a>
			</li>
			<li><span class="mob-sear"><img src="<?= base_url() ?>assets/images/icon/search1.png">Search</span>
			</li>
			<li>
				<a href="all-category.html" class="act">
					<img src="<?= base_url() ?>assets/images/icon/shop.png">Services</a>
			</li>
			<li>
				<a href="events.html">
					<img src="<?= base_url() ?>assets/images/icon/calendar.png">Events</a>
			</li>

			<li>
				<a href="coupons.html">
					<img src="<?= base_url() ?>assets/images/icon/coupons.png">Coupons</a>
			</li>
			<li>
				<a href="blog-posts.html">
					<img src="<?= base_url() ?>assets/images/icon/blog1.png">Bolgs</a>
			</li>
		</ul>
	</div>
	<!-- END -->
	<!-- Optional JavaScript -->

	<!-- jQuery first, then Popper.js, then Bootstrap JS -->
	<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
	<!-- CSRF token wiring for jQuery AJAX (reads <meta name="csrf-token">) -->
	<script src="<?= base_url() ?>assets/js/csrf.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
	<script src="<?= base_url('assets/js/select-opt.js') ?>"></script>
	<script src="<?= base_url('assets/js/custom.js') ?>"></script>
	<script src="<?= base_url() ?>assets/js/jquery.validate.min.js"></script>
	<script src="<?= base_url() ?>assets/js/custom_validation.js"></script>
	<script src="https://cdn.ckeditor.com/4.16.1/standard/ckeditor.js"></script>
	<!--DataTable-->
	<script src="//cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
	<script src="<?= base_url('assets/js/customCode.js'); ?>"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>

	<script type="text/javascript">
		$("#selectCategory").select2();
	</script>

	<script>
		$("#selectCategory").on('change', function() {
			var id = $('#selectCategory').val();
			window.location.replace('<?= base_url('user/all-listing/'); ?>' + id)
		});
	</script>

	<script>
		$(window).scroll(function() {
			var scroll = $(window).scrollTop();
			if (scroll >= 250) {
				$(".hom-top").addClass("dmact");
			} else {
				$(".hom-top").removeClass("dmact");
			}
		});

		$('.owl-carousel').owlCarousel({
			loop: true,
			margin: 10,
			autoplay: true,
			nav: true,
			responsive: {
				0: {
					items: 1
				},
				600: {
					items: 1
				},
				1000: {
					items: 1
				}
			}
		});
	</script>

	<!-- Select2 JS CDN -->
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
	<script>
		$(document).ready(function() {
			$('select').select2({
				width: '100%',
				minimumResultsForSearch: 0 // always show search box
			});
		});
	</script>
	</body>

	</html>