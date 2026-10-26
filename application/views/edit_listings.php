<?php
// Freemium field gating. $caps comes from User::edit_listing (owner's plan
// capabilities). Default to all-unlocked if somehow absent so the form still
// works. cap_lock_note() renders the inline "upgrade to unlock" hint.
$caps = isset($caps) && is_array($caps) ? $caps : array(
	'can_photos' => true, 'photo_limit' => -1, 'can_map' => true,
	'can_services' => true, 'can_video' => true, 'video_limit' => -1, 'can_extra_details' => true,
);
if (!function_exists('cap_lock_note')) {
	function cap_lock_note($allowed)
	{
		if ($allowed) return '';
		return '<small class="form-text text-warning" style="display:block;margin-top:4px;">'
			. '&#128274; Locked on your current package. '
			. '<a href="' . base_url('pricing-plan') . '">Upgrade to unlock</a>.</small>';
	}
}
?>
<style>
	/* Multi-step form styles */
	.multistep-progress-bar {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 30px;
		position: relative;
		width: 80%;
		margin-left: auto;
		margin-right: auto;
	}

	.multistep-progress-bar::before {
		content: '';
		position: absolute;
		top: 50%;
		left: 0;
		right: 0;
		height: 2px;
		background: #e0e0e0;
		transform: translateY(-50%);
		z-index: 1;
	}

	.multistep-progress-bar .multistep-progress-line {
		position: absolute;
		top: 50%;
		left: 0;
		height: 2px;
		background: #007bff;
		transform: translateY(-50%);
		transition: width 0.3s ease;
		z-index: 2;
	}

	.multistep-step-indicator {
		width: 40px;
		height: 40px;
		border-radius: 50%;
		background: #e0e0e0;
		display: flex;
		align-items: center;
		justify-content: center;
		font-weight: bold;
		color: #666;
		position: relative;
		z-index: 3;
		transition: all 0.3s ease;
		cursor: pointer;
	}

	.multistep-step-indicator.multistep-active {
		background: #007bff;
		color: white;
	}

	.multistep-step-indicator.multistep-completed {
		background: #28a745;
		color: white;
	}

	.multistep-step-indicator.multistep-completed::after {
		content: '✓';
		font-size: 16px;
	}

	.multistep-step-content {
		display: none;
		animation: fadeIn 0.3s ease-in-out;
	}

	.multistep-step-content.multistep-active-content {
		display: block;
	}

	@keyframes fadeIn {
		from {
			opacity: 0;
			transform: translateY(20px);
		}

		to {
			opacity: 1;
			transform: translateY(0);
		}
	}

	.multistep-navigation-buttons {
		display: flex;
		justify-content: space-between;
		margin-top: 30px;
		padding-top: 20px;
		border-top: 1px solid #e0e0e0;
		width: 100%;
	}

	.multistep-btn-prev,
	.multistep-btn-next,
	.multistep-btn-submit {
		padding: 10px 20px;
		border: none;
		border-radius: 5px;
		cursor: pointer;
		transition: all 0.3s ease;
		font-size: 14px;
		font-weight: 500;
	}

	.multistep-btn-prev {
		background: #6c757d;
		color: white;
	}

	.multistep-btn-prev:hover {
		background: #545b62;
	}

	.multistep-btn-next,
	.multistep-btn-submit {
		background: #007bff;
		color: white;
	}

	.multistep-btn-next:hover,
	.multistep-btn-submit:hover {
		background: #0056b3;
	}

	.multistep-btn-prev:disabled {
		background: #e9ecef;
		color: #6c757d;
		cursor: not-allowed;
	}

	/* Hide original step indicators */
	.udb-inst,
	.steps {
		display: none;
	}

	/* Smooth transitions */
	.login-main {
		transition: all 0.3s ease;
	}

	/* Ensure proper spacing for step content */
	.multistep-step-content {
		min-height: 400px;
		padding-bottom: 20px;
	}

	/* Error message styles */
	.multistep-error-message {
		background-color: #f8d7da;
		border: 1px solid #f5c6cb;
		color: #721c24;
		padding: 15px;
		border-radius: 5px;
		margin-bottom: 20px;
		display: none;
		width: 80%;
		margin-left: auto;
		margin-right: auto;
	}

	.multistep-error-message.show {
		display: block;
	}

	.multistep-error-message ul {
		margin: 0;
		padding-left: 20px;
	}

	.multistep-error-message li {
		margin-bottom: 3px;
	}
</style>

<section class=" login-reg">

	<div class="container">

		<!-- Multi-step progress bar -->
		<div class="multistep-progress-bar">
			<div class="multistep-progress-line" style="width: 33.33%;"></div>
			<div class="multistep-step-indicator multistep-active" data-step="1">1</div>
			<div class="multistep-step-indicator" data-step="2">2</div>
			<div class="multistep-step-indicator" data-step="3">3</div>
		</div>

		<?php // Save results. The required-fields error was set by the controller
		      // and never printed, and a successful save only said so on another
		      // page — with no way to see the result. ?>
		<?php $__saved = $this->session->flashdata('saved_listing'); ?>
		<?php if ($this->session->flashdata('message')): ?>
			<div class="alert alert-success" role="status" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;justify-content:space-between;">
				<span><?= e($this->session->flashdata('message')) ?></span>
				<?php if (!empty($__saved)): ?>
					<a class="btn btn-success btn-sm" href="<?= e_url(base_url('listing-details/' . rawurlencode($__saved))) ?>" target="_blank" rel="noopener">View your live listing &rarr;</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php if ($this->session->flashdata('error')): ?>
			<div class="alert alert-warning" role="alert"><?= e($this->session->flashdata('error')) ?></div>
		<?php endif; ?>
		<?php if (!empty($error)): ?>
			<div class="alert alert-danger" role="alert"><?= e($error) ?></div>
		<?php endif; ?>

		<!-- Error message area -->
		<div id="multistep-error-container" class="multistep-error-message">
			<strong>To continue, please complete the following:</strong>
			<ul id="multistep-error-list"></ul>
		</div>

		<div class="row">

			<div class="login-main add-list">

				<div class="log-bor">&nbsp;</div> <span class="steps">Edit Listing</span>

				<div class="log">

					<div class="login">

						<!--<form action="add-listing-step-2.html" class="listing_form_1" id="listing_form_1" name="listing_form_1" method="post" enctype="multipart/form-data">-->

						<?= form_open_multipart('user/edit-listing/' . $listing_slug, ['class' => 'listing_form_1', 'id' => 'listing_form_1']); ?>

						<!-- Step 1: Listing Details -->
						<div class="multistep-step-content multistep-active-content" data-step="1">
							<h4>Edit Listing Details</h4>
							<!--FILED START-->
							<input type="hidden" name="listing_slug" value="<?= $listing_slug; ?>" required="required">

							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<input id="listing_name" name="listing_name" type="text" required="required" class="form-control" placeholder="Listing name *" value="<?= e_attr($listing->listing_name) ?>">
									</div>
								</div>
								<span class="text-danger"><?= form_error("listing_name") ? form_error("listing_name") : ''; ?></span>
							</div>

							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<select onChange="getSubCategory(this.value);" name="category_id" id="category_id" class="form-control">
											<option value="">Select Category</option>
											<?php
											if (count($categories) > 0) {
												foreach ($categories as $value) {
											?>
													<option value="<?= $value->category_id; ?>" <?= ($listing->category_id == $value->category_id) ? 'SELECTED' : '' ?>><?= $value->category_name; ?> </option>
											<?php
												}
											}
											?>
										</select>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<select data-placeholder="Select Sub Category" name="sub_category_id" id="sub_category_id" class="chosen-select form-control">
											<?php if ($sub_category_listing) { ?>
												<option value="">Choose a Sub Category</option>
												<?php
												foreach ($sub_category_listing as $subcat) { ?>
													<option value="<?= $subcat->sub_category_id  ?>" <?= ($subcat->sub_category_id == $listing->sub_category_id) ? 'SELECTED' : '' ?>><?= $subcat->sub_category_name ?></option>
											<?php
												}
											}
											?>
										</select>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<input type="text" name="listing_mobile" class="form-control" placeholder="Phone number" value="<?= e_attr($listing->listing_mobile) ?>">
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<input type="text" name="listing_whatsapp" class="form-control" placeholder="Whatsapp Number" value="<?= e_attr($listing->listing_whatsapp) ?>">
									</div>
								</div>
								<div class="col-md-12">
									<div class="form-group row">
										<label class="col-sm-4">Display Phone Number?</label>
										<div class="col-sm-4">
											<div class="form-check form-check-inline">
												<input class="form-check-input" type="radio" name="display_phone" id="display_phone" value="1" <?= ($listing->display_phone == '1') ? 'CHECKED' : '' ?>>
												<label class="form-check-label">Yes</label>
											</div>
											<div class="form-check form-check-inline">
												<input class="form-check-input" type="radio" name="display_phone" id="display_phone" value="0" <?= ($listing->display_phone == '0') ? 'CHECKED' : '' ?>>
												<label class="form-check-label">No</label>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<select name="county_name" id="" class="form-control">
											<option value="">Choose a County</option>
											<option value="Brazoria" <?= ($listing->county_name == 'Brazoria') ? 'SELECTED' : '' ?>>Brazoria</option>
											<option value="Fort Bend" <?= ($listing->county_name == 'Fort Bend') ? 'SELECTED' : '' ?>>Fort Bend</option>
											<option value="Harris" <?= ($listing->county_name == 'Harris') ? 'SELECTED' : '' ?>>Harris</option>
										</select>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<select name="postal_code" id="" class="form-control">
											<option value="">Choose a Postal Code</option>
											<option value="77584" <?= ($listing->postal_code == '77584') ? 'SELECTED' : '' ?>>77584</option>
											<option value="77581" <?= ($listing->postal_code == '77581') ? 'SELECTED' : '' ?>>77581</option>
											<option value="77588" <?= ($listing->postal_code == '77588') ? 'SELECTED' : '' ?>>77588</option>
											<option value="77047" <?= ($listing->postal_code == '77047') ? 'SELECTED' : '' ?>>77047</option>
											<option value="77089" <?= ($listing->postal_code == '77089') ? 'SELECTED' : '' ?>>77089</option>
										</select>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<input type="text" name="listing_lat" class="form-control" placeholder="Latitude i.e 40.730610" value="<?= e_attr((string) $listing->listing_lat) ?>" <?= $caps['can_map'] ? '' : 'disabled' ?>>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<input type="text" name="listing_lng" class="form-control" placeholder="Longitude i.e -73.935242" value="<?= e_attr($listing->listing_lng) ?>" <?= $caps['can_map'] ? '' : 'disabled' ?>>
											<?= cap_lock_note($caps['can_map']) ?>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<input type="email" name="listing_email" class="form-control" placeholder="Email id" value="<?= e_attr($listing->listing_email) ?>">
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<input type="text" name="listing_website" class="form-control" placeholder="Website(www.website.com)" value="<?= e_attr($listing->listing_website) ?>">
									</div>
								</div>
							</div>

							<?php // Opening hours and the business's own social links (migration
							      // 0037) — part of onboarding, and plan features: locked with an
							      // upgrade note on a plan that does not include them. ?>
							<?php if (property_exists($listing, 'business_hours')): ?>
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<label for="business_hours">Opening hours</label>
										<textarea class="form-control" id="business_hours" name="business_hours" rows="3" maxlength="1000"
												  placeholder="Mon–Fri 9am–6pm&#10;Sat 10am–4pm&#10;Sun closed" <?= $caps['can_hours'] ? '' : 'disabled' ?>><?= e((string) $listing->business_hours) ?></textarea>
										<?= cap_lock_note($caps['can_hours'], 'Opening hours') ?>
									</div>
								</div>
							</div>
							<?php endif; ?>
							<?php if (property_exists($listing, 'listing_facebook')): ?>
							<div class="row">
								<div class="col-md-12"><label>Social media links</label></div>
								<?php foreach (array('facebook' => 'Facebook page link', 'instagram' => 'Instagram link', 'twitter' => 'X / Twitter link', 'youtube' => 'YouTube channel link', 'linkedin' => 'LinkedIn link') as $__net => $__ph): ?>
								<div class="col-md-6">
									<div class="form-group">
										<input type="text" name="listing_<?= $__net ?>" class="form-control" maxlength="255" placeholder="<?= e_attr($__ph) ?>"
											   value="<?= e_attr((string) $listing->{'listing_' . $__net}) ?>" <?= $caps['can_social_links'] ? '' : 'disabled' ?>>
									</div>
								</div>
								<?php endforeach; ?>
								<div class="col-md-12"><?= cap_lock_note($caps['can_social_links'], 'Social media links') ?></div>
							</div>
							<?php endif; ?>

							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<input type="text" name="listing_address" required="required" class="form-control" placeholder="Business address" value="<?= e_attr($listing->listing_address) ?>">
									</div>
								</div>
								<div class="col-md-12">
									<div class="form-group row">
										<label class="col-sm-4">Display Address?</label>
										<div class="col-sm-4">
											<div class="form-check form-check-inline">
												<input class="form-check-input" type="radio" name="display_address" value="1" id="display_phone" <?= ($listing->display_address == '1') ? 'CHECKED' : '' ?>>
												<label class="form-check-label">Yes</label>
											</div>
											<div class="form-check form-check-inline">
												<input class="form-check-input" type="radio" name="display_address" value="0" id="display_phone" <?= ($listing->display_address == '0') ? 'CHECKED' : '' ?>>
												<label class="form-check-label">No</label>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<label>Details about your listing</label>
										<textarea class="form-control" id="listing_description" name="listing_description" placeholder="Details about your listing"><?= e((string) $listing->listing_description) ?></textarea>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<textarea class="form-control" id="service_locations" name="service_locations" placeholder="Enter your service locations... &#10;(i.e) Pearland,Arlington, Austin"><?= e((string) $listing->service_locations) ?></textarea>
									</div>
								</div>
								<div class="col-md-12">
									<div class="form-group">
										<textarea class="form-control" id="service_offered" name="service_offered" placeholder="Enter Offered Services ... &#10;(i.e) Car Wash, Car Service" <?= $caps['can_services'] ? '' : 'disabled' ?>><?= e((string) $listing->service_offered) ?></textarea>
											<?= cap_lock_note($caps['can_services']) ?>
									</div>
								</div>
							</div>
						</div>

						<!-- Step 2: Photo & Video Gallery -->
						<div class="multistep-step-content" data-step="2">
							<div class="log add-list-map">
								<div class="login add-list-map">
									<h4 class="pt30">Photo gallery</h4>
									<!--FILED START-->

									<!--FILED START-->
									<div class="row">
										<div class="col-md-12">
											<?php
											// Same partial the admin edit screen uses, so the owner's
											// form and the admin's behave identically.
											$mf_name     = 'listing_profile_image';
											$mf_id       = 'listing_profile_image';
											$mf_label    = 'Upload Listing Logo';
											$mf_current  = isset($listing->listing_image) ? $listing->listing_image : '';
											$mf_hint     = image_guidance('logo');
											$mf_disabled = !$caps['can_photos'];
											$mf_empty    = 'No logo uploaded yet.';
											include APPPATH . 'views/admin/partials/media_field.php';
											?>
											<?= cap_lock_note($caps['can_photos']) ?>
										</div>
									</div>
									<!--GALLERY IMAGES START-->
									<div class="row">
										<div class="col-md-12">
											<?php
											$mf_name     = 'listing_image_gallery[]';
											$mf_id       = 'listing_image_gallery';
											$mf_label    = 'Upload Gallery Images (Multiple Images)';
											$mf_hint     = 'You can select several at once. New images are <strong>added</strong> to the ones below &mdash; nothing is replaced.'
											               . ($caps['can_photos'] && $caps['photo_limit'] >= 0
											                  ? ' Your package allows up to ' . (int) $caps['photo_limit'] . ' gallery photo(s).'
											                  : '')
											               . ' ' . image_guidance('gallery');
											$mf_current  = listing_gallery_paths(isset($listing->listing_image_gallery) ? $listing->listing_image_gallery : null);
											$mf_multiple = true;
											$mf_remove   = 'remove_gallery[]';
											$mf_disabled = !$caps['can_photos'];
											$mf_empty    = 'No gallery images uploaded yet.';
											include APPPATH . 'views/admin/partials/media_field.php';
											?>
											<?= cap_lock_note($caps['can_photos']) ?>
										</div>
									</div>
									<!--GALLERY IMAGES END-->
									<!--FILED END-->
									<h4>Video Gallery</h4>
									<ul>
										<li>
											<div class="row">
												<div class="col-md-12">
													<div class="form-group">
														<textarea id="listing_video" name="listing_video" class="form-control" placeholder="Paste Your Youtube Url here" <?= $caps['can_video'] ? '' : 'disabled' ?>><?= e($listing->listing_video) ?></textarea>
														<?php if ($caps['can_video'] && isset($caps['video_limit']) && (int) $caps['video_limit'] >= 0): ?>
															<small class="form-text text-muted">Your package includes <?= (int) $caps['video_limit'] ?> YouTube video<?= (int) $caps['video_limit'] === 1 ? '' : 's' ?> &mdash; one link per line.</small>
														<?php endif; ?>
														<?= cap_lock_note($caps['can_video'], 'Videos') ?>
													</div>
												</div>
											</div>
										</li>
									</ul>
								</div>
							</div>
						</div>

						<!-- Step 3: SEO Settings -->
						<div class="multistep-step-content" data-step="3">
							<div class="log add-list-map">
								<h4 class="pt30">SEO Setting</h4>
								<div class="login add-list-map">
									<div class="form-group">
										<label>Page title</label>
										<input type="text" name="listing_seo_title" placeholder="" class="form-control" value="<?= e_attr((string) $listing->listing_seo_title) ?>" <?= $caps['can_extra_details'] ? '' : 'disabled' ?>>
									</div>
									<div class="form-group">
										<label>Page keywords</label>
										<input type="text" name="listing_seo_keywords" class="form-control" placeholder="i.e:wedding hall, best wedding hall" value="<?= e_attr((string) $listing->listing_seo_keywords) ?>" <?= $caps['can_extra_details'] ? '' : 'disabled' ?>>
									</div>
									<div class="form-group">
										<label>Page descriptions</label>
										<input type="text" name="listing_seo_description" class="form-control" value="<?= e_attr((string) $listing->listing_seo_description) ?>" <?= $caps['can_extra_details'] ? '' : 'disabled' ?>>
											<?= cap_lock_note($caps['can_extra_details']) ?>
									</div>
								</div>
							</div>
						</div>

						<!-- Navigation buttons -->
						<div class="multistep-navigation-buttons">
							<button type="button" class="multistep-btn-prev" disabled>Previous</button>
							<button type="button" class="multistep-btn-next">Next</button>
							<button type="submit" name="listing_submit" class="multistep-btn-submit btn btn-primary" style="display: none;">Update Listing</button>
						</div>

						<!-- Original submit section (hidden) -->
						<div class="row" style="display: none;">
							<div class="col-md-12">
								<button type="submit" name="listing_submit" class="btn btn-primary">Update Listing</button>
							</div>
							<div class="col-md-12">
								<a href="<?= base_url('user/dashboard'); ?>" class="skip"> Go to user deshboard &gt;&gt;</a>
							</div>
						</div>
						</form>
					</div>
				</div>

			</div>

		</div>

	</div>

</section>

<script src="<?= base_url() ?>assets/ckeditor/ckeditor.js"></script>
<script>
	CKEDITOR.replace('listing_description');
</script>

<script src="../js/select-opt.js"></script>

<script>
	function getSubCategory(val) {
		$.ajax({
			type: "POST",
			url: '<?= base_url() ?>/admin/get_listing_sub_category',
			data: 'category_id=' + val,
			success: function(data) {
				$("#sub_category_id").html(data);
				$('#sub_category_id').trigger("chosen:updated");
			}
		});
	}
</script>

<script>
	// Multi-step form functionality
	document.addEventListener('DOMContentLoaded', function() {
		let currentStep = 1;
		const totalSteps = 3;
		const stepIndicators = document.querySelectorAll('.multistep-step-indicator');
		const stepContents = document.querySelectorAll('.multistep-step-content');
		const progressLine = document.querySelector('.multistep-progress-line');
		const prevBtn = document.querySelector('.multistep-btn-prev');
		const nextBtn = document.querySelector('.multistep-btn-next');
		const submitBtn = document.querySelector('.multistep-btn-submit');
		const navButtons = document.querySelector('.multistep-navigation-buttons');
		const originalSubmitSection = document.querySelector('.row[style="display: none;"]');

		function validateCurrentStep() {
			const currentStepContent = document.querySelector(`[data-step="${currentStep}"]`);
			let errorMessages = [];

			if (currentStep === 1) {
				// Step 1 validation - Required fields
				const listingName = document.querySelector('input[name="listing_name"]').value.trim();
				const categoryId = document.querySelector('select[name="category_id"]').value;
				const listingAddress = document.querySelector('input[name="listing_address"]').value.trim();

				if (!listingName) {
					errorMessages.push('Please enter a listing name');
				}

				if (!categoryId) {
					errorMessages.push('Please select a category');
				}

				if (!listingAddress) {
					errorMessages.push('Please enter your business address');
				}
			} else if (currentStep === 2) {
				// Step 2 validation - Optional for edit, but validate if provided
				// No strict validation needed for edit form
			} else if (currentStep === 3) {
				// Step 3 validation - SEO fields optional but if filled should be valid
				const seoTitle = document.querySelector('input[name="listing_seo_title"]').value.trim();
				if (seoTitle && seoTitle.length < 3) {
					errorMessages.push('SEO title should be at least 3 characters long');
				}
			}

			if (errorMessages.length > 0) {
				// Show error messages list
				showErrors(errorMessages);
				return false;
			}

			// Clear any existing error messages
			hideErrors();
			return true;
		}

		function showErrors(messages) {
			const errorContainer = document.getElementById('multistep-error-container');
			const errorList = document.getElementById('multistep-error-list');
			if (messages && messages.length > 0) {
				errorList.innerHTML = '';
				messages.forEach(message => {
					const li = document.createElement('li');
					li.textContent = message;
					errorList.appendChild(li);
				});
				errorContainer.classList.add('show');
			}
		}

		function hideErrors() {
			const errorContainer = document.getElementById('multistep-error-container');
			errorContainer.classList.remove('show');
		}

		// Backward compatibility functions
		function showErrorMessage(message) {
			showErrors([message]);
		}

		function clearErrorMessage() {
			hideErrors();
		}

		function updateStepDisplay() {
			// Update step indicators
			stepIndicators.forEach((indicator, index) => {
				const stepNumber = index + 1;
				indicator.classList.remove('multistep-active', 'multistep-completed');
				if (stepNumber < currentStep) {
					indicator.classList.add('multistep-completed');
				} else if (stepNumber === currentStep) {
					indicator.classList.add('multistep-active');
				}
			});
			// Update step content
			stepContents.forEach((content, index) => {
				content.classList.remove('multistep-active-content');
				if (index + 1 === currentStep) {
					content.classList.add('multistep-active-content');
				}
			});
			// Update progress line
			const progressWidth = ((currentStep - 1) / (totalSteps - 1)) * 100;
			progressLine.style.width = progressWidth + '%';
			// Update navigation buttons and submit section
			prevBtn.disabled = currentStep === 1;
			if (currentStep === totalSteps) {
				if (navButtons) navButtons.style.display = 'none';
				if (originalSubmitSection) originalSubmitSection.style.display = 'flex';
			} else {
				if (navButtons) navButtons.style.display = 'flex';
				if (originalSubmitSection) originalSubmitSection.style.display = 'none';
			}
		}

		// Next button click
		nextBtn.addEventListener('click', function() {
			if (validateCurrentStep() && currentStep < totalSteps) {
				currentStep++;
				updateStepDisplay();
			}
		});
		// Previous button click
		prevBtn.addEventListener('click', function() {
			if (currentStep > 1) {
				currentStep--;
				updateStepDisplay();
			}
		});
		// Step indicator click (allow direct navigation)
		stepIndicators.forEach((indicator, index) => {
			indicator.addEventListener('click', function() {
				const targetStep = index + 1;
				if (targetStep <= currentStep || (targetStep === currentStep + 1 && validateCurrentStep())) {
					currentStep = targetStep;
					updateStepDisplay();
				}
			});
		});
		// Initialize
		updateStepDisplay();
	});
</script>
<?php // Previews chosen files, opens stored images full size, greys a ticked Remove.
      // Same behaviour as the admin edit screens, which use the same partial. ?>
<script src="<?= base_url('assets/admin/js/media-preview.js') ?>"></script>
