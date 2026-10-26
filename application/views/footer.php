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
				<?= spam_form_fields() ?>
				<button type="submit" id="home_slide_enquiry_submit" class="btn btn-primary">
					Submit Requirements
				</button>
			</form>
		</div>
	</div>
	<!-- END -->

	<?php
	/*
	 * The site-wide footer banner used to render here, and no longer does.
	 *
	 * Two reasons. It sat directly under `bottom-banner-site-wide`, so the
	 * foot of every page was a 900x100 banner stacked on a 970x90 banner —
	 * two wide ads within one screen, with the call-to-action band below
	 * them. And `footer-banner` is a 0006 zone whose slot_key matches no
	 * package on /pricing-plan, so it was inventory nobody could actually
	 * buy: 33 pages of house ad earning nothing and costing a screenful.
	 *
	 * The zone still exists in `ads_prices` and ad_slot.php still knows how
	 * to draw it, so re-instating it is one include — but sell it first.
	 */
	?>

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
						<div class="col-md-3 bb-link"> <a href="<?= base_url('pricing-plan') ?>">Add my business</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- END -->
	<?php
	/*
	 * The footer. Shared with the homepage through one partial — this used to
	 * be a second, hand-kept copy that had drifted: a different phone number,
	 * a different email, eight hard-coded category slugs (several of which no
	 * longer resolve) and a copyright line reading "2025-2035".
	 *
	 * Styles: assets/css/site-footer.css, linked from views/header.php.
	 */
	include APPPATH . 'views/partials/site_footer.php';
	?>
	<!-- START -->
	<?php /* Every link in here pointed at a .html file — index.html,
	         all-category.html, events.html — which is the static template this
	         theme was cut from. None of them resolved. */ ?>
	<div class="fqui-menu">
		<ul>
			<li>
				<a href="<?= base_url() ?>">
					<img src="<?= base_url() ?>assets/images/icon/home.png" alt="">Home</a>
			</li>
			<li><span class="mob-sear"><img src="<?= base_url() ?>assets/images/icon/search1.png" alt="">Search</span>
			</li>
			<li>
				<a href="<?= base_url('all-category') ?>" class="act">
					<img src="<?= base_url() ?>assets/images/icon/shop.png" alt="">Services</a>
			</li>
			<?php if (site_setting('service_events_enabled','1') !== '0'): ?>
			<li>
				<a href="<?= base_url('event') ?>">
					<img src="<?= base_url() ?>assets/images/icon/calendar.png" alt="">Events</a>
			</li>
			<?php endif; ?>

			<li>
				<a href="<?= base_url('coupons') ?>">
					<img src="<?= base_url() ?>assets/images/icon/coupons.png" alt="">Deals</a>
			</li>
			<?php if (site_setting('service_news_enabled','1') !== '0'): ?>
			<li>
				<a href="<?= base_url('news') ?>">
					<img src="<?= base_url() ?>assets/images/icon/news.png" alt="">News</a>
			</li>
			<?php endif; ?>
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
	<?php // Versioned by mtime like form-validate.js below: this file changed (the
	      // stale "Coupon Photo is Required" rule was removed), and without ?v= a
	      // returning visitor's cached copy kept blocking photo-less deals. ?>
	<script src="<?= base_url('assets/js/custom_validation.js') ?>?v=<?= @filemtime(FCPATH . 'assets/js/custom_validation.js') ?: 1 ?>"></script>
	<script src="https://cdn.ckeditor.com/4.16.1/standard/ckeditor.js"></script>
	<!--DataTable-->
	<script src="//cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
	<script src="<?= base_url('assets/js/customCode.js') ?>?v=<?= @filemtime(FCPATH . 'assets/js/customCode.js') ?: 1 ?>"></script>
	<?php // Live validation for any <form data-validate>. See the file header. ?>
	<script src="<?= base_url('assets/js/form-validate.js') ?>?v=<?= @filemtime(FCPATH . 'assets/js/form-validate.js') ?: 1 ?>"></script>
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
			// `no-select2` opts a select out (admin/footer.php has the same
			// rule): Select2 only fires jQuery events, so a page script that
			// listens with addEventListener('change') never hears the change.
			$('select:not(.no-select2)').select2({
				width: '100%',
				minimumResultsForSearch: 0 // always show search box
			});
		});
	</script>
	</body>

	</html>