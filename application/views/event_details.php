<?php
// Function to create safe URL slugs
function createSafeSlug($text)
{
	// Remove HTML tags
	$text = strip_tags($text);

	// Convert to lowercase
	$text = strtolower($text);

	// Replace spaces and special characters with hyphens
	$text = preg_replace('/[^a-z0-9]+/', '-', $text);

	// Remove leading and trailing hyphens
	$text = trim($text, '-');

	// Remove multiple consecutive hyphens
	$text = preg_replace('/-+/', '-', $text);

	return $text;
}
?>

<style>
	.eve-deta-pg .lhs .img:before {
		display: none !important;
	}

	.eve-deta-body .rhs .sec-3 {
		padding-top: 0px !important;
		border-top: none !important;
	}

	.eve-deta-body .eve-deta-body-main .rhs {
		margin-top: -40px;
	}

	.eve-deta-body .eve-deta-body-main .lhs {
		border-right: none !important;
	}
	
	.ud-lhs-s1 {
		margin-bottom: 30px !important;
	}

	/* Sidebar Web Development Section */
	.sidebar-marketing-section {
		width: 100%;
		margin-bottom: 30px;
	}

	.sidebar-marketing-section .marketing-card {
		background: linear-gradient(145deg, #1e68ac 0%, #1e40af 100%);
		border-radius: 14px;
		padding: 40px 32px;
		position: relative;
		overflow: hidden;
		/* box-shadow: 0 20px 60px rgba(37, 99, 235, 0.3); */
	}

	.sidebar-marketing-section .marketing-card::before {
		content: '';
		position: absolute;
		top: -50%;
		right: -50%;
		width: 200%;
		height: 200%;
		background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
		animation: pulseEffect 8s ease-in-out infinite;
	}

	@keyframes pulseEffect {

		0%,
		100% {
			transform: scale(1);
			opacity: 0.5;
		}

		50% {
			transform: scale(1.1);
			opacity: 0.8;
		}
	}

	.sidebar-marketing-section .marketing-card .card-content {
		position: relative;
		z-index: 2;
	}


	.sidebar-marketing-section .marketing-card .card-content .main-heading {
		color: white;
		font-size: 26px;
		font-weight: 700;
		margin-bottom: 12px;
		line-height: 1.3;
	}

	.sidebar-marketing-section .marketing-card .card-content .description {
		color: rgba(255, 255, 255, 0.9);
		font-size: 15px;
		line-height: 1.6;
		margin-bottom: 28px;
	}

	.sidebar-marketing-section .marketing-card .card-content .features-wrapper {
		margin-bottom: 28px;
	}

	.sidebar-marketing-section .marketing-card .card-content .features-wrapper .feature {
		display: flex;
		align-items: center;
		color: white;
		font-size: 14px;
		margin-bottom: 12px;
	}

	.sidebar-marketing-section .marketing-card .card-content .features-wrapper .feature:last-child {
		margin-bottom: 0;
	}

	.sidebar-marketing-section .marketing-card .card-content .features-wrapper .feature .check-icon {
		width: 20px;
		height: 20px;
		margin-right: 10px;
		flex-shrink: 0;
	}

	.sidebar-marketing-section .marketing-card .card-content .primary-button {
		background: white;
		color: #1e68ac;
		border: none;
		padding: 16px 32px;
		font-size: 16px;
		font-weight: 600;
		border-radius: 99px;
		cursor: pointer;
		transition: all 0.3s ease;
		display: block;
		text-align: center;
		width: 100%;
		box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
	}

	.sidebar-marketing-section .marketing-card .card-content .primary-button:hover {
		transform: translateY(-2px);
		box-shadow: 0 12px 32px rgba(0, 0, 0, 0.2);
		background: #f8fafc;
	}

	.sidebar-marketing-section .marketing-card .card-content .primary-button:active {
		transform: translateY(0);
	}

	.pg_title {
		font-size: 40px !important;
		font-weight: 700 !important;
		margin-bottom: 20px;
	}

	@media (max-width: 480px) {
		.sidebar-marketing-section .marketing-card {
			padding: 32px 24px;
		}

		.sidebar-marketing-section .marketing-card .card-content .main-heading {
			font-size: 22px;
		}

		.sidebar-marketing-section .marketing-card .card-content .description {
			font-size: 14px;
		}

		.pg_title {
			font-size: 30px !important;
		}
	}

	/* Sidebar Web Development Section */

	.event-detail-modern {
		max-width: 800px;
		margin: 0 auto;
	}

	.event-detail-modern .head {
		border-bottom: 3px solid #1e68ac;
		padding-bottom: 1.5rem;
		margin-bottom: 2rem;
	}

	.event-detail-modern .head h1 {
		font-size: 2rem;
		font-weight: 700;
		color: #1e293b;
		margin: 0;
		line-height: 1.3;
	}

	.event-detail-modern>p,
	.event-detail-modern>div:not(.head):not(.sec-1):not(.sec-2) {
		color: #475569;
		line-height: 1.8;
		font-size: 1rem;
		margin-bottom: 2.5rem;
	}

	.event-detail-modern .sec-1 {
		background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
		border-left: 4px solid #1e68ac;
		padding: 2rem 2.5rem;
		margin-bottom: 2.5rem;
	}

	.event-detail-modern .sec-1 h4 {
		font-size: 1.25rem;
		font-weight: 600;
		color: #1e293b;
		margin: 0 0 1.5rem 0;
		text-transform: uppercase;
		letter-spacing: 0.5px;
	}

	.event-detail-modern .sec-1 ul {
		list-style: none;
		padding: 0;
		margin: 0;
		/* display: grid;
		grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
		gap: 1rem; */
	}

	.event-detail-modern .sec-1 ul li {
		padding: 0.75rem 0;
		color: #334155;
		font-size: 0.95rem;
		border-bottom: 1px solid #e2e8f0;
	}

	.event-detail-modern .sec-1 ul li:last-child {
		border-bottom: none;
	}

	.event-detail-modern .sec-1 ul li b {
		color: #1e68ac;
		font-weight: 600;
		display: inline-block;
		min-width: 120px;
	}

	.event-detail-modern .sec-2 {
		border-top: 2px solid #e2e8f0;
		padding-top: 2rem;
	}

	.event-detail-modern .sec-2 h4 {
		font-size: 1.25rem;
		font-weight: 600;
		color: #1e293b;
		margin: 0 0 1.5rem 0;
		text-transform: uppercase;
		letter-spacing: 0.5px;
	}

	.event-detail-modern .sec-2 iframe,
	.event-detail-modern .sec-2 img {
		width: 100%;
		height: 400px;
		border: 2px solid #e2e8f0;
		border-radius: 4px;
		box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
	}

	.list-rhs-form {
		border: none !important;
	}

	/* Responsive */
	@media (max-width: 768px) {
		.event-detail-modern .head h1 {
			font-size: 1.5rem;
		}

		.event-detail-modern .sec-1 {
			padding: 1.5rem;
		}

		.event-detail-modern .sec-1 ul {
			grid-template-columns: 1fr;
		}

		.event-detail-modern .sec-2 iframe,
		.event-detail-modern .sec-2 img {
			height: 300px;
		}
	}
</style>
<!-- START -->
<section class="eve-deta-pg" style="margin-top: 0px !important; padding-top: 50px;">
	<div class="container">
		<div class="eve-deta-pg-main">
			<div class="lhs">
				<div class="img">
					<?php if ($event->event_image): ?>
						<img src="<?= base_url($event->event_image); ?>" alt="">
					<?php else: ?>
						<img src="https://placehold.co/600x400/EEE/31343C" alt="">
					<?php endif; ?>
				</div>


			</div>

			<div class="rhs">
				<div class="list-rhs-form pglist-bg pglist-p-com">
					<div class="quote-pop">
						<?php if ($this->session->flashdata('message')): ?>
							<div class="log-suc">
								<p><?= e($this->session->flashdata('message')); ?></p>
							</div>
						<?php endif; ?>

						<h3>Get Quote</h3>

						<div id="event_detail_enq_success" class="log" style="display: none;">
							<p>Your Enquiry Is Submitted Successfully</p>
						</div>
						<div id="event_detail_enq_same" class="log" style="display: none;">
							<p>You cannot make enquiry on your own event</p>
						</div>
						<div id="event_detail_enq_fail" class="log" style="display: none;">
							<p>Something Went Wrong!!!</p>
						</div>

						<form method="post" action="<?= base_url('user/add-enquiry/event_details/' . rawurlencode($event->event_slug)); ?>" name="detail_enquiry_form" id="detail_enquiry_form">
							<!-- Hidden fields for enquiry data -->
							<input type="hidden" name="listing_id" value="">
							<input type="hidden" name="event_id" value="<?= e_attr($event->event_slug); ?>">
							<input type="hidden" name="blog_id" value="">
							<input type="hidden" name="product_id" value="">
							<input type="hidden" name="receiving_user_id" value="<?= (int) $event->user_id; ?>">
							<input type="hidden" name="enquiry_sender_id" value="<?= (int) $logged_id; ?>">
							<input type="hidden" name="enquiry_source" value="<?= e_attr($event->event_name); ?>">
							<input type="hidden" name="enquiry_type" value="event">

							<?php $disabled = $logged_id == NULL ? 'disabled' : ''; ?>

							<div class="form-group ic-user">
								<input type="text" name="enquiry_name" value="<?= set_value('enquiry_name'); ?>" required="required" class="form-control" placeholder="Enter name*" <?= $disabled; ?>>
								<span class="text-danger"><?= form_error("enquiry_name") ? form_error("enquiry_name") : ''; ?></span>
							</div>

							<div class="form-group ic-eml">
								<input type="email" class="form-control" name="enquiry_email" placeholder="Enter email*" required="required" value="<?= set_value('enquiry_email'); ?>" pattern="^[\w]{1,}[\w.+-]{0,}@[\w-]{2,}([.][a-zA-Z]{2,}|[.][\w-]{2,}[.][a-zA-Z]{2,})$" title="Invalid email address" <?= $disabled; ?>>
								<span class="text-danger"><?= form_error("enquiry_email") ? form_error("enquiry_email") : ''; ?></span>
							</div>

							<div class="form-group ic-pho">
								<input type="text" class="form-control" name="enquiry_mobile" value="<?= set_value('enquiry_mobile'); ?>" placeholder="Enter mobile number *" pattern="[0-9]{10,15}" title="Please enter a valid phone number (10-15 digits)" required <?= $disabled; ?>>
								<span class="text-danger"><?= form_error("enquiry_mobile") ? form_error("enquiry_mobile") : ''; ?></span>
							</div>

							<div class="form-group">
								<textarea class="form-control" rows="3" name="enquiry_message" placeholder="Enter your query or message" <?= $disabled; ?>></textarea>
								<span class="text-danger"><?= form_error("enquiry_message") ? form_error("enquiry_message") : ''; ?></span>
							</div>

							<?php if ($logged_id != NULL): ?>
								<button type="submit" id="detail_enquiry_submit" name="enquiry_submit" class="btn btn-primary">Submit</button>
							<?php else: ?>
								<a href="<?= base_url('/login'); ?>"><button type="button" class="btn btn-primary" style="background:#f44336; border:none;">Please Log In First</button></a>
							<?php endif; ?>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<!--END--><!-- START -->
<section class="eve-deta-body">
	<div class="container">
		<div class="eve-deta-body-main">
			<div class="lhs event-detail-modern">
				<div class="head">
					<h1><?= e($event->event_name); ?></h1>
				</div>
				<?= e_nl2br($event->event_description); ?>

				<div class="sec-1">
					<h4>Event information:</h4>
					<ul>
						<li><b>Name</b>: <?= e($event->event_name); ?></li>
						<li><b>Date</b>: <?= date('d, M Y', strtotime($event->event_start_date)); ?></li>
						<li><b>Time</b>: <?= e($event->event_time); ?></li>
						<li><b>Address</b>: <?= e($event->event_address); ?></li>
						<li><b>Contact Person</b>: <?= e($event->event_contact_name); ?></li>
						<li><b>Phone</b>: <?= e($event->event_mobile); ?></li>
						<li><b>Email</b>: <?= e($event->event_email); ?></li>
						<li><b>Website</b>: <?= e($event->event_website); ?></li>
					</ul>
				</div>

				<div class="sec-2 mt-2">
					<h4>Location</h4>
					<?= $event->event_map; ?>
				</div>
			</div>

			<div class="rhs">


				<div class="sec-3 mb-3">
					<div class="ud-lhs-s1">
						<?php
						// Get user information for the event host (same logic as events card)
						$event_user = null;
						if (!empty($event->user_id)) {
							$CI = &get_instance();
							$event_user = $CI->usermodel->getSingleValue($event->user_id, 'user_id', 'user');
						}
						$user_name = $event_user ? $event_user->name : 'Directory Finder';
						$profile_image = 'https://img.freepik.com/premium-vector/user-icon-icon_1076610-59410.jpg';
						if ($event_user && !empty($event_user->profile_image)) {
							$profile_image = base_url($event_user->profile_image);
						}
						$joined_date = $event_user && !empty($event_user->user_created) ? date('d M, Y', strtotime($event_user->user_created)) : '';
						?>
						<div style="font-weight:600; color:#333; margin-bottom:2px;">Hosted by</div>
						<img src="<?= $profile_image; ?>" alt="<?= htmlspecialchars($user_name); ?>">
						<h4><?= htmlspecialchars($user_name); ?></h4>
						<?php if ($joined_date): ?>
							<b>Joined on <?= $joined_date; ?></b>
						<?php endif; ?>
					</div>
				</div>

				<?php $slot = 'events-sidebar'; include APPPATH . 'views/partials/ad_slot.php'; ?>
				<?php include APPPATH . 'views/partials/web_dev_cta.php'; ?>
			</div>
		</div>

		<div class="pro-bot-shar">
			<h4>Share this event</h4>
			<ul>
				<!-- Facebook -->
				<li>
					<div class="sh-pro-shar sh-pro-face">
						<a target="_blank"
							href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(base_url('user/event_details/' . $event->event_id)); ?>">
							Facebook
						</a>
					</div>
				</li>

				<!-- Twitter (X) -->
				<li>
					<div class="sh-pro-shar sh-pro-twi">
						<a target="_blank"
							href="https://twitter.com/intent/tweet?text=<?= urlencode($event->event_name); ?>&url=<?= urlencode(base_url('user/event_details/' . $event->event_id)); ?>">
							Twitter
						</a>
					</div>
				</li>

				<!-- WhatsApp -->
				<li>
					<div class="sh-pro-shar sh-pro-what">
						<a target="_blank"
							href="https://api.whatsapp.com/send?text=<?= urlencode($event->event_name . ' - ' . base_url('user/event_details/' . $event->event_id)); ?>">
							WhatsApp
						</a>
					</div>
				</li>

				<!-- LinkedIn -->
				<li>
					<div class="sh-pro-shar sh-pro-link">
						<a target="_blank"
							href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode(base_url('user/event_details/' . $event->event_id)); ?>">
							LinkedIn
						</a>
					</div>
				</li>

				<!-- Pinterest -->
				<li>
					<div class="sh-pro-shar sh-pro-pin" style="background-color: #da271a;">
						<a target="_blank"
							href="https://pinterest.com/pin/create/button/?url=<?= urlencode(base_url('user/event_details/' . $event->event_id)); ?>&media=<?= $event->event_image ? urlencode(base_url($event->event_image)) : ''; ?>&description=<?= urlencode($event->event_name); ?>">
							Pinterest
						</a>
					</div>
				</li>
			</ul>

		</div>

		<div class="pro-rel-events">
			<h4>Related Events</h4>
			<div class="event-body">
				<div class="us-ppg-com">
					<ul>
						<?php
						if (isset($related_events) && !empty($related_events)):
							foreach ($related_events as $rel_event):
								// Create safe slug for event details URL
								$rel_event_slug = !empty($rel_event->event_slug) ? $rel_event->event_slug : createSafeSlug($rel_event->event_name);

								// Set default image if event image is empty
								$event_image = !empty($rel_event->event_image) ? base_url($rel_event->event_image) : 'https://placehold.co/300x200/EEE/31343C?text=Event';
						?>
								<li>
									<div class="pro-eve-box">
										<div>
											<img src="<?= $event_image; ?>" alt="<?= htmlspecialchars($rel_event->event_name); ?>">
										</div>
										<div>
											<span><?= date('M', strtotime($rel_event->event_start_date)); ?> <b><?= date('d', strtotime($rel_event->event_start_date)); ?></b></span>
											<h2><?= htmlspecialchars($rel_event->event_name); ?></h2>
											<p><?= htmlspecialchars($rel_event->event_address); ?></p>
										</div>
										<a href="<?= base_url('event-details/' . $rel_event_slug); ?>" class="fclick">&nbsp;</a>
									</div>
								</li>
							<?php
							endforeach;
						else:
							?>
							<li style="text-align: center; padding: 30px;">
								<p style="color: #666;">No related events available at the moment.</p>
							</li>
						<?php endif; ?>
					</ul>
				</div>
			</div>
		</div>
	</div>
</section>

<script>
	// Event Details Page Enquiry Form Handler
	document.addEventListener('DOMContentLoaded', function() {
		const enquiryForm = document.getElementById('detail_enquiry_form');
		const submitButton = document.getElementById('detail_enquiry_submit');

		if (enquiryForm) {
			enquiryForm.addEventListener('submit', function(e) {
				e.preventDefault();

				// Disable submit button to prevent double submission
				submitButton.disabled = true;
				submitButton.textContent = 'Submitting...';

				// Get form data
				const formData = new FormData(enquiryForm);

				// Basic validation
				const name = formData.get('enquiry_name');
				const email = formData.get('enquiry_email');
				const mobile = formData.get('enquiry_mobile');

				if (!name || !email || !mobile) {
					showMessage('event_detail_enq_fail', 'Please fill in all required fields.');
					resetSubmitButton();
					return;
				}

				// Email validation
				const emailRegex = /^[\w]{1,}[\w.+-]{0,}@[\w-]{2,}([.][a-zA-Z]{2,}|[.][\w-]{2,}[.][a-zA-Z]{2,})$/;
				if (!emailRegex.test(email)) {
					showMessage('event_detail_enq_fail', 'Please enter a valid email address.');
					resetSubmitButton();
					return;
				}

				// Phone validation (10-15 digits)
				const phoneRegex = /^[0-9]{10,15}$/;
				if (!phoneRegex.test(mobile)) {
					showMessage('event_detail_enq_fail', 'Please enter a valid phone number (10-15 digits).');
					resetSubmitButton();
					return;
				}

				// Submit form via AJAX
				fetch(enquiryForm.action, {
						method: 'POST',
						body: formData,
						headers: {
							'X-Requested-With': 'XMLHttpRequest'
						}
					})
					.then(response => response.json())
					.then(data => {
						if (data.success) {
							showMessage('event_detail_enq_success', data.message || 'Your enquiry has been submitted successfully!');
							enquiryForm.reset(); // Clear form
						} else {
							showMessage('event_detail_enq_fail', data.message || 'Something went wrong! Please try again.');
						}
					})
					.catch(error => {
						console.error('Error:', error);
						showMessage('event_detail_enq_fail', 'Something went wrong! Please try again.');
					})
					.finally(() => {
						resetSubmitButton();
					});
			});
		}

		function showMessage(elementId, message) {
			// Hide all message divs
			document.getElementById('event_detail_enq_success').style.display = 'none';
			document.getElementById('event_detail_enq_same').style.display = 'none';
			document.getElementById('event_detail_enq_fail').style.display = 'none';

			// Show the specific message
			const messageDiv = document.getElementById(elementId);
			if (messageDiv) {
				messageDiv.style.display = 'block';
				if (message) {
					messageDiv.querySelector('p').textContent = message;
				}

				// Auto-hide message after 5 seconds
				setTimeout(() => {
					messageDiv.style.display = 'none';
				}, 5000);
			}
		}

		function resetSubmitButton() {
			submitButton.disabled = false;
			submitButton.textContent = 'Submit';
		}
	});
</script>
<!--END-->