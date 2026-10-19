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

<?php
/*
 * From User::renderListingWizard(): $categories, $caps (what the member's plan
 * — or the free Basic plan they will start on — lets a listing carry),
 * $has_plan, $free_plan, $can_add; after a refused POST also $errors (list)
 * and $old (what they typed, so nothing has to be entered twice).
 */
$caps    = (isset($caps) && is_array($caps)) ? $caps : array('can_photos' => false, 'photo_limit' => 0, 'can_map' => false,
	'can_services' => false, 'can_video' => false, 'video_limit' => 0, 'can_extra_details' => false, 'plan_name' => '');
$old     = (isset($old) && is_array($old)) ? $old : array();
$ov      = function ($k, $d = '') use ($old) { return isset($old[$k]) && !is_array($old[$k]) ? (string) $old[$k] : $d; };
$errors  = (isset($errors) && is_array($errors)) ? $errors : array();
$can_add = !isset($can_add) || $can_add;
$plan_label = !empty($has_plan) ? (string) $caps['plan_name'] : (!empty($free_plan) ? (string) $free_plan->name : '');
$limit_txt = function ($n, $one, $many) { return $n < 0 ? 'unlimited ' . $many : $n . ' ' . ($n === 1 ? $one : $many); };
$has_media = $caps['can_photos'] || $caps['can_video'];
?>
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
		<div id="multistep-error-container" class="multistep-error-message<?= $errors ? ' show' : '' ?>">
			<strong><?= $errors ? 'Your listing was not saved yet:' : 'To continue, please complete the following:' ?></strong>
			<ul id="multistep-error-list">
				<?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
			</ul>
			<?php if ($errors): ?><p style="margin:8px 0 0;">Everything you typed is still below &mdash; photos need choosing again.</p><?php endif; ?>
		</div>

		<div class="row">

			<div class="login-main add-list">

				<div class="log-bor">&nbsp;</div> <span class="steps">Add Listing</span>
				<?php if ($this->session->flashdata('message')) { ?>
					<div class="log-suc">
						<p><?= e($this->session->flashdata('message')) ?></p>
					</div>
				<?php } ?>

				<?php // What this listing can carry, said before the member fills anything in. ?>
				<div class="alert <?= $can_add ? 'alert-info' : 'alert-warning' ?>" style="margin:0 0 18px;">
					<?php if (!$can_add && !empty($has_plan)): ?>
						<p style="margin:0;">Your <strong><?= e($plan_label) ?></strong> plan does not allow another listing.
							<a href="<?= e_url(base_url('pricing-plan')) ?>">Upgrade your plan</a> to add more businesses.</p>
					<?php elseif (!$can_add): ?>
						<p style="margin:0;">Choose a plan before adding a business &mdash; see <a href="<?= e_url(base_url('pricing-plan')) ?>">our packages</a>,
							or <a href="<?= e_url(base_url('all-listing')) ?>">claim your business</a> if it is already listed.</p>
					<?php else: ?>
						<p style="margin:0 0 6px;">
							<?php if (!empty($has_plan)): ?>
								Your plan: <strong><?= e($plan_label) ?></strong>.
							<?php else: ?>
								You are not on a plan yet, so this will be a free <strong><?= e($plan_label) ?></strong> listing &mdash; submitting it starts that plan.
							<?php endif; ?>
						</p>
						<p style="margin:0;font-size:14px;">
							Includes: name, category, contact details, address and description<?=
								$caps['can_photos'] ? ', a logo' . ((int) $caps['photo_limit'] !== 0 ? ', ' . $limit_txt((int) $caps['photo_limit'], 'gallery photo', 'gallery photos') : '') : '' ?><?=
								$caps['can_video'] ? ', ' . $limit_txt((int) $caps['video_limit'], 'YouTube video', 'YouTube videos') : '' ?><?=
								$caps['can_map'] ? ', a map' : '' ?><?= $caps['can_services'] ? ', services offered' : '' ?><?= $caps['can_extra_details'] ? ', search (SEO) details' : '' ?>.
							<?php if (!$caps['can_photos'] || !$caps['can_video']): ?>
								<a href="<?= e_url(base_url('pricing-plan')) ?>">Upgrade</a> for <?= !$caps['can_photos'] ? 'a logo and photos' : '' ?><?= !$caps['can_photos'] && !$caps['can_video'] ? ' and ' : '' ?><?= !$caps['can_video'] ? 'video' : '' ?>.
							<?php endif; ?>
						</p>
					<?php endif; ?>
				</div>

				<div class="log">

					<div class="login">

						<?= form_open_multipart('user/add-listing-scratch', ['class' => 'listing_form_1', 'id' => 'listing_form_1']); ?>

						<!-- Step 1: Listing Details -->
						<div class="multistep-step-content multistep-active-content" data-step="1">
							<h4>Listing Details</h4>

							<!--FILED START-->

							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<input id="listing_name" name="listing_name" type="text" required="required" maxlength="150" class="form-control" placeholder="Listing name *" value="<?= e_attr($ov('listing_name')) ?>">
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<select onChange="getSubCategory(this.value);" name="category_id" id="category_id" class="form-control">
											<option value="">Select Category *</option>
											<?php foreach ((array) $categories as $value): ?>
												<option value="<?= (int) $value->category_id ?>" <?= $ov('category_id') === (string) $value->category_id ? 'selected' : '' ?>><?= e($value->category_name) ?></option>
											<?php endforeach; ?>
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
										<input type="text" name="listing_mobile" class="form-control" placeholder="Phone number" value="<?= e_attr($ov('listing_mobile')) ?>">
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<input type="text" name="listing_whatsapp" class="form-control" placeholder="Whatsapp Number" value="<?= e_attr($ov('listing_whatsapp')) ?>">
									</div>
								</div>
								<div class="col-md-12">
									<div class="form-group row align-items-center">
										<label class="col-sm-4">Display Phone Number?</label>
										<div class="col-sm-4">
											<div class="form-check form-check-inline">
												<input class="form-check-input" type="radio" name="display_phone" id="display_phone_yes" value="1" <?= $ov('display_phone', '1') !== '0' ? 'checked' : '' ?>>
												<label class="form-check-label" for="display_phone_yes">Yes</label>
											</div>
											<div class="form-check form-check-inline">
												<input class="form-check-input" type="radio" name="display_phone" id="display_phone_no" value="0" <?= $ov('display_phone', '1') === '0' ? 'checked' : '' ?>>
												<label class="form-check-label" for="display_phone_no">No</label>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<select name="county_name" class="form-control">
											<option value="">Choose a County</option>
											<?php foreach (array('Brazoria', 'Fort Bend', 'Harris') as $c): ?>
												<option value="<?= e_attr($c) ?>" <?= $ov('county_name') === $c ? 'selected' : '' ?>><?= e($c) ?></option>
											<?php endforeach; ?>
										</select>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<select name="postal_code" class="form-control">
											<option value="">Choose a Postal Code</option>
											<?php foreach (array('77584', '77581', '77588', '77047', '77089') as $z): ?>
												<option value="<?= e_attr($z) ?>" <?= $ov('postal_code') === $z ? 'selected' : '' ?>><?= e($z) ?></option>
											<?php endforeach; ?>
										</select>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<input type="text" name="listing_lat" class="form-control" placeholder="Latitude i.e 29.5636" value="<?= e_attr($ov('listing_lat')) ?>" <?= $caps['can_map'] ? '' : 'disabled' ?>>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<input type="text" name="listing_lng" class="form-control" placeholder="Longitude i.e -95.2860" value="<?= e_attr($ov('listing_lng')) ?>" <?= $caps['can_map'] ? '' : 'disabled' ?>>
										<?= cap_lock_note($caps['can_map'], 'Maps') ?>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<input type="email" name="listing_email" class="form-control" placeholder="Email id" value="<?= e_attr($ov('listing_email')) ?>">
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<input type="text" name="listing_website" class="form-control" placeholder="Website(www.website.com)" value="<?= e_attr($ov('listing_website')) ?>">
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<input type="text" name="listing_address" required="required" class="form-control" placeholder="Business address *" value="<?= e_attr($ov('listing_address')) ?>">
									</div>
								</div>
								<div class="col-md-12">
									<div class="form-group row">
										<label class="col-sm-4">Display Address?</label>
										<div class="col-sm-4">
											<div class="form-check form-check-inline">
												<input class="form-check-input" type="radio" name="display_address" value="1" id="display_address_yes" <?= $ov('display_address', '1') !== '0' ? 'checked' : '' ?>>
												<label class="form-check-label" for="display_address_yes">Yes</label>
											</div>
											<div class="form-check form-check-inline">
												<input class="form-check-input" type="radio" name="display_address" value="0" id="display_address_no" <?= $ov('display_address', '1') === '0' ? 'checked' : '' ?>>
												<label class="form-check-label" for="display_address_no">No</label>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">

								<div class="col-md-12">
									<div class="form-group">
										<label>Details about your listing</label>
										<textarea class="form-control" id="listing_description" name="listing_description" placeholder="Details about your listing"><?= e($ov('listing_description')) ?></textarea>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<textarea class="form-control" id="service_locations" name="service_locations" placeholder="Enter your service locations... &#10;(i.e) Pearland,Arlington, Austin"><?= e($ov('service_locations')) ?></textarea>
									</div>
								</div>
								<div class="col-md-12">
									<div class="form-group">
										<textarea class="form-control" id="service_offered" name="service_offered" placeholder="Enter Offered Services ... &#10;(i.e) Car Wash, Car Service" <?= $caps['can_services'] ? '' : 'disabled' ?>><?= e($ov('service_offered')) ?></textarea>
										<?= cap_lock_note($caps['can_services'], 'Services offered') ?>
									</div>
								</div>

							</div>
						</div>

						<!-- Step 2: Photo & Video Gallery — what the plan includes, and no more -->
						<div class="multistep-step-content" data-step="2">
							<div class="log add-list-map">
								<div class="login add-list-map">
									<h4 class="pt30">Logo &amp; photos</h4>
									<?php if (!$caps['can_photos']): ?>
										<div class="alert alert-light" style="border:1px dashed #cfd4e0;">
											<p style="margin:0;">&#128274; A logo and gallery photos are not included in the <?= e($plan_label ?: 'Basic') ?> listing.
												<a href="<?= e_url(base_url('pricing-plan')) ?>">Upgrade to Advanced or Featured</a> to add them &mdash; you can add them later from My Listings.</p>
										</div>
									<?php else: ?>
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label>Upload Listing Logo</label>
													<input type="file" name="listing_profile_image" class="form-control" accept="image/*">
													<small class="form-text text-muted">Optional. <?= image_guidance('logo') ?></small>
												</div>
											</div>
										</div>
										<?php if ((int) $caps['photo_limit'] !== 0): ?>
											<div class="row">
												<div class="col-md-12">
													<div class="form-group">
														<label>Upload Gallery Images (<?= $caps['photo_limit'] < 0 ? 'as many as you like' : 'up to ' . (int) $caps['photo_limit'] ?>)</label>
														<input type="file" name="listing_image_gallery[]" class="form-control" multiple accept="image/*"
															   data-max-files="<?= (int) $caps['photo_limit'] ?>">
														<small class="form-text text-muted">Optional. Your plan includes <?= e($limit_txt((int) $caps['photo_limit'], 'gallery photo', 'gallery photos')) ?>. <?= image_guidance('gallery') ?></small>
													</div>
												</div>
											</div>
										<?php else: ?>
											<p class="text-muted" style="font-size:14px;">Gallery photos are not included in your plan &mdash; <a href="<?= e_url(base_url('pricing-plan')) ?>">upgrade</a> to add them.</p>
										<?php endif; ?>
									<?php endif; ?>

									<h4>Video</h4>
									<?php if (!$caps['can_video']): ?>
										<div class="alert alert-light" style="border:1px dashed #cfd4e0;">
											<p style="margin:0;">&#128274; Video is not included in the <?= e($plan_label ?: 'Basic') ?> listing.
												<a href="<?= e_url(base_url('pricing-plan')) ?>">See which packages include video</a>.</p>
										</div>
									<?php else: ?>
										<div class="form-group">
											<textarea id="listing_video" name="listing_video" class="form-control" placeholder="Paste your YouTube link here" data-max-videos="<?= (int) $caps['video_limit'] ?>"><?= e($ov('listing_video')) ?></textarea>
											<small class="form-text text-muted">Optional. Your plan includes <?= e($limit_txt((int) $caps['video_limit'], 'YouTube video', 'YouTube videos')) ?> &mdash; one link per line.</small>
										</div>
									<?php endif; ?>
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
										<input type="text" name="listing_seo_title" value="<?= e_attr($ov('listing_seo_title')) ?>" placeholder="" class="form-control" <?= $caps['can_extra_details'] ? '' : 'disabled' ?>>
									</div>
									<div class="form-group">
										<label>Page keywords</label>
										<input type="text" name="listing_seo_keywords" value="<?= e_attr($ov('listing_seo_keywords')) ?>" class="form-control" placeholder="i.e:wedding hall, best wedding hall" <?= $caps['can_extra_details'] ? '' : 'disabled' ?>>
									</div>
									<div class="form-group">
										<label>Page descriptions</label>
										<input type="text" name="listing_seo_description" value="<?= e_attr($ov('listing_seo_description')) ?>" class="form-control" <?= $caps['can_extra_details'] ? '' : 'disabled' ?>>
										<?= cap_lock_note($caps['can_extra_details'], 'Search (SEO) details') ?>
									</div>
									<p class="text-muted" style="font-size:14px;margin-top:14px;">
										When you press <strong>Submit Listing</strong> your business goes live in the directory straight away,
										and we email you a confirmation with its link.
									</p>
								</div>
							</div>
						</div>

						<!-- Navigation buttons -->
						<div class="multistep-navigation-buttons">
							<button type="button" class="multistep-btn-prev" disabled>Previous</button>
							<button type="button" class="multistep-btn-next">Next</button>
							<button type="submit" name="listing_submit" class="multistep-btn-submit btn btn-primary" style="display: none;" <?= $can_add ? '' : 'disabled' ?>>Submit Listing</button>
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
	function getSubCategory(val, selected) {
		$.ajax({
			type: "POST",
			url: '<?= base_url() ?>user/get_listing_sub_category',
			data: 'category_id=' + val,
			success: function(data) {
				$("#sub_category_id").html(data);
				if (selected) $("#sub_category_id").val(String(selected));
				$('#sub_category_id').trigger("chosen:updated").trigger('change');
			}
		});
	}
	// Back after a refused submission: refill the sub-category list for the
	// category they had chosen, and re-select their sub-category.
	document.addEventListener('DOMContentLoaded', function () {
		var cat = document.getElementById('category_id');
		if (cat && cat.value && window.jQuery) getSubCategory(cat.value, <?= json_encode($ov('sub_category_id')) ?>);
	});
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
				// Step 2: pictures and video are optional — and on the Basic plan
				// not offered at all — so this used to demand "at least a logo or
				// gallery images" of members whose plan includes neither. It now
				// only checks the plan's counts, which the server enforces too.
				const gallery = document.querySelector('input[name="listing_image_gallery[]"]');
				const maxFiles = gallery ? parseInt(gallery.getAttribute('data-max-files'), 10) : -1;
				if (gallery && maxFiles >= 0 && gallery.files.length > maxFiles) {
					errorMessages.push('Your plan includes ' + maxFiles + ' gallery photo' + (maxFiles === 1 ? '' : 's') + ' — choose ' + maxFiles + ' or fewer.');
				}
				const video = document.querySelector('textarea[name="listing_video"]');
				const maxVideos = video ? parseInt(video.getAttribute('data-max-videos'), 10) : -1;
				if (video && maxVideos >= 0) {
					const links = video.value.split(/[\s,]+/).filter(Boolean);
					if (links.length > maxVideos) {
						errorMessages.push('Your plan includes ' + maxVideos + ' video' + (maxVideos === 1 ? '' : 's') + ' — remove the extra links.');
					}
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