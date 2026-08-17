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
		position: relative;
		z-index: 99;
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

		<!-- Error message area -->
		<div id="multistep-error-container" class="multistep-error-message">
			<strong>To continue, please complete the following:</strong>
			<ul id="multistep-error-list"></ul>
		</div>

		<div class="row">

			<div class="login-main add-list">

				<div class="log-bor">&nbsp;</div> <span class="steps">Add Listing</span>
				<?php if ($this->session->flashdata('message')) { ?>
					<div class="log-suc">
						<p><?= $this->session->flashdata('message') ?></p>
					</div>
				<?php } ?>
				<div class="log">

					<div class="login">

						<!-- Step 1: Listing Details -->
						<div class="multistep-step-content multistep-active-content" data-step="1">
							<h4>Listing Details</h4>
							<?php if ($this->session->flashdata('message')) { ?>
								<div class="log-suc">
									<p><?= $this->session->flashdata('message') ?></p>
								</div>
							<?php } ?>
							<?php if (isset($error)) { ?>
								<div class="alert alert-danger">
									<p><?= $error ?></p>
								</div>
							<?php } ?>
							<?php if (isset($debug_message)) { ?>
								<div class="alert alert-info">
									<p><strong>DEBUG:</strong> <?= $debug_message ?></p>
								</div>
							<?php } ?>
							<?php if (isset($success_debug)) { ?>
								<div class="alert alert-success">
									<p><strong>SUCCESS:</strong> <?= $success_debug ?></p>
								</div>
							<?php } ?>
							<?php if (isset($insert_debug)) { ?>
								<div class="alert alert-warning">
									<p><strong>INSERT:</strong> <?= $insert_debug ?></p>
								</div>
							<?php } ?>
							<?php if (isset($db_debug)) { ?>
								<div class="alert alert-danger">
									<p><strong>DATABASE:</strong> <?= $db_debug ?></p>
								</div>
							<?php } ?>

							<?= form_open_multipart('user/add-listing-scratch', ['class' => 'listing_form_1', 'id' => 'listing_form_1']); ?>

							<!--FILED START-->

							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<input id="listing_name" name="listing_name" type="text" required="required" class="form-control" placeholder="Listing name *">
									</div>
								</div>
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
													<option value="<?= $value->category_id; ?>"><?= $value->category_name; ?></option>
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
											<option value="">Select Sub Category</option>
										</select>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<input type="text" name="listing_mobile" class="form-control" placeholder="Phone number">
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<input type="text" name="listing_whatsapp" class="form-control" placeholder="Whatsapp Number">
									</div>
								</div>
								<div class="col-md-12">
									<div class="form-group row align-items-center">
										<label class="col-sm-4">Display Phone Number?</label>
										<div class="col-sm-4">
											<div class="form-check form-check-inline">
												<input class="form-check-input" type="radio" name="display_phone" id="display_phone" value="1">
												<label class="form-check-label">Yes</label>
											</div>
											<div class="form-check form-check-inline">
												<input class="form-check-input" type="radio" name="display_phone" id="display_phone" value="0">
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
											<option value="Brazoria">Brazoria</option>
											<option value="Fort Bend">Fort Bend</option>
											<option value="Harris">Harris</option>
										</select>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<select name="postal_code" id="" class="form-control">
											<option value="">Choose a Postal Code</option>
											<option value="77584">77584</option>
											<option value="77581">77581</option>
											<option value="77588">77588</option>
											<option value="77047">77047</option>
											<option value="77089">77089</option>
										</select>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<input type="text" name="listing_lat" class="form-control" placeholder="Latitude i.e 40.730610">
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<input type="text" name="listing_lng" class="form-control" placeholder="Longitude i.e -73.935242">
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<input type="email" name="listing_email" class="form-control" placeholder="Email id">
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<input type="text" name="listing_website" class="form-control" placeholder="Website(www.website.com)">
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<input type="text" name="listing_address" required="required" class="form-control" placeholder="Business address">
									</div>
								</div>
								<div class="col-md-12">
									<div class="form-group row">
										<label class="col-sm-4">Display Address?</label>
										<div class="col-sm-4">
											<div class="form-check form-check-inline">
												<input class="form-check-input" type="radio" name="display_address" value="1" id="display_phone">
												<label class="form-check-label">Yes</label>
											</div>
											<div class="form-check form-check-inline">
												<input class="form-check-input" type="radio" name="display_address" value="0" id="display_phone">
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
										<textarea class="form-control" id="listing_description" name="listing_description" placeholder="Details about your listing"></textarea>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<textarea class="form-control" id="service_locations" name="service_locations" placeholder="Enter your service locations... &#10;(i.e) Pearland,Arlington, Austin"></textarea>
									</div>
								</div>
								<div class="col-md-12">
									<div class="form-group">
										<textarea class="form-control" id="service_offered" name="service_offered" placeholder="Enter Offered Services ... &#10;(i.e) Car Wash, Car Service"></textarea>
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
											<div class="form-group">
												<label>Upload Listing Logo</label>
												<input type="file" name="listing_profile_image" class="form-control">
												<small class="form-text text-muted"><?= image_guidance('logo') ?></small>
											</div>
										</div>
									</div>
									<!--GALLERY IMAGES START-->
									<div class="row">
										<div class="col-md-12">
											<div class="form-group">
												<label>Upload Gallery Images (Multiple)</label>
												<input type="file" name="listing_image_gallery[]" class="form-control" multiple accept="image/*">
												<small class="form-text text-muted">You can select multiple images for gallery. <?= image_guidance('gallery') ?></small>
											</div>
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
														<textarea id="listing_video" name="listing_video" class="form-control" placeholder="Paste Your Youtube Url here"></textarea>
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
								<div class="login add-list-map">
									<h4 class="pt30">SEO Setting</h4>
									<div class="form-group">
										<label>Page title</label>
										<input type="text" name="listing_seo_title" value="" placeholder="" class="form-control">
									</div>
									<div class="form-group">
										<label>Page keywords</label>
										<input type="text" name="listing_seo_keywords" value="" class="form-control" placeholder="i.e:wedding hall, best wedding hall">
									</div>
									<div class="form-group">
										<label>Page descriptions</label>
										<input type="text" name="listing_seo_description" value="" class="form-control">
									</div>
								</div>
							</div>
						</div>

						<!-- Navigation buttons -->
						<div class="multistep-navigation-buttons">
							<button type="button" class="multistep-btn-prev" disabled>Previous</button>
							<button type="button" class="multistep-btn-next">Next</button>
							<button type="submit" name="listing_submit" class="multistep-btn-submit btn btn-primary" style="display: none;">Submit Listing</button>
						</div>

						<!-- Original submit section (hidden) -->
						<div class="row" style="display: none;">
							<div class="col-md-12">
								<button type="submit" name="listing_submit" class="btn btn-primary">Submit Listing</button>
							</div>
						</div>

						</form>

					</div>

				</div>

			</div>

		</div>

	</div>

</section>

<script src="../js/select-opt.js"></script>

<script>
	function getSubCategory(val) {
		$.ajax({
			type: "POST",
			url: '<?= base_url() ?>user/get_listing_sub_category',
			data: 'category_id=' + val,
			success: function(data) {
				$("#sub_category_id").html(data);
				$('#sub_category_id').trigger("chosen:updated");
			}
		});
	}
</script>

<script src="<?= base_url() ?>assets/ckeditor/ckeditor.js"></script>

<script>
	CKEDITOR.replace('listing_description');
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
				// Step 2 validation - At least logo or gallery image required
				const logoFile = document.querySelector('input[name="listing_profile_image"]').files.length;
				const galleryFiles = document.querySelector('input[name="listing_image_gallery[]"]').files.length;

				if (logoFile === 0 && galleryFiles === 0) {
					errorMessages.push('Please upload at least a logo image or gallery images to make your listing attractive');
				}
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
				// Last step - show submit button, hide next button
				nextBtn.style.display = 'none';
				submitBtn.style.display = 'inline-block';
			} else {
				// Not last step - show next button, hide submit button
				nextBtn.style.display = 'inline-block';
				submitBtn.style.display = 'none';
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