<!--PRICING DETAILS-->
<section class=" ud">
	<div class="ud-inn">
		<!--LEFT SECTION-->
		<div class="ud-lhs">
			<div class="ud-lhs-s1">
				<?php
				// Get user information from session if not passed from controller
				if (!isset($userInfo) || empty($userInfo)) {
					// Get user data from session
					$session = $this->session->userdata('logged_user');
					if ($session && isset($session['logged_id'])) {

						// Try to get from session first
						if (isset($session['name']) && !empty($session['name'])) {
							// Use session data if available
							$userInfo = (object) [
								'name' => $session['name'],
								'profile_image' => isset($session['profile_image']) ? $session['profile_image'] : '',
								'user_created' => isset($session['user_created']) ? $session['user_created'] : ''
							];
						} else {
							// Fallback: fetch fresh data from database
							$this->load->model('usermodel');
							$userInfo = $this->usermodel->getUserInformation();

							// Update session with fresh data
							if ($userInfo) {
								$updated_session = $session;
								$updated_session['name'] = $userInfo->name;
								$updated_session['profile_image'] = $userInfo->profile_image;
								$updated_session['user_created'] = $userInfo->user_created;
								$this->session->set_userdata('logged_user', $updated_session);
							}
						}
					}
				}

				// Get real user name
				$sidebarName = 'User';
				if (isset($userInfo) && !empty($userInfo->name)) {
					$sidebarName = $userInfo->name;
				}

				// Get join date
				$sidebarJoin = '';
				if (isset($userInfo) && !empty($userInfo->user_created)) {
					$sidebarJoin = date('d, M Y', strtotime($userInfo->user_created));
				}

				// Get real profile image - enhanced with better path checking
				$sidebarImg = base_url('assets/images/user/default-user.png'); // fallback image
				if (isset($userInfo) && !empty($userInfo->profile_image)) {
					// Check if it's already a full URL or just a path
					if (strpos($userInfo->profile_image, 'http') === 0) {
						$sidebarImg = $userInfo->profile_image;
					} else {
						$sidebarImg = base_url($userInfo->profile_image);
					}
				}

				// Generate initials as fallback
				$initials = 'U';
				if (!empty($sidebarName) && $sidebarName !== 'User') {
					$nameParts = explode(' ', $sidebarName);
					$initials = strtoupper(substr($nameParts[0], 0, 1));
					if (isset($nameParts[1])) {
						$initials .= strtoupper(substr($nameParts[1], 0, 1));
					}
				}
				?>
				<div style="position: relative; text-align: center;">
					<img src="<?= $sidebarImg ?>"
						alt="<?= htmlspecialchars($sidebarName) ?>"
						style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #21d78d;"
						onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
					<div style="display: none; width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(45deg, #667eea, #764ba2); color: white; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; border: 3px solid #21d78d; margin: 0 auto; margin-top: 60px;">
						<?= $initials ?>
					</div>
				</div>
				<h4><?= htmlspecialchars($sidebarName) ?></h4>
				<?php if ($sidebarJoin) { ?><b>Join on <?= $sidebarJoin ?></b><?php } ?>
				<a class="ud-lhs-view-pro" href="<?= base_url('user/my_account'); ?>">My profile</a>
			</div>
			<div class="ud-lhs-s2">
				<ul>
					<li>
						<a href="<?= base_url() ?>dashboard" class=""><img src="<?= base_url() ?>assets/images/icon/dbl1.png" alt="" />My Dashboard</a>
					</li>
					<li>
						<a href="<?= base_url() ?>user/my_account" class=""><img src="<?= base_url() ?>assets/images/icon/tick.png" alt="" />My Account</a>
					</li>

					<!-- Main Navigation -->
					<li>
						<a href="<?= base_url() ?>user-listing" class=""><img src="<?= base_url() ?>assets/images/icon/shop.png" alt="" />Listings</a>
					</li>
					<!-- <li>
						<a href="<?= base_url() ?>user/user_listing_reviews" class=""><img src="<?= base_url() ?>assets/images/icon/tick.png" alt="" />Listing Reviews</a>
					</li> -->
					<li>
						<a href="<?= base_url('user/enquiries'); ?>" class=""><img src="<?= base_url() ?>assets/images/icon/tick.png" alt="" />Lead enquiry</a>
					</li>
					<?php if (site_setting('service_products_enabled','1') !== '0'): ?>
					<li>
						<a href="<?= base_url() ?>products" class=""><img src="<?= base_url() ?>assets/images/icon/cart.png" alt="" />All Products</a>
					</li>
					<?php endif; ?>
					<?php if (site_setting('service_events_enabled','1') !== '0'): ?>
					<li>
						<a href="<?= base_url() ?>user-events" class=""><img src="<?= base_url() ?>assets/images/icon/calendar.png" alt="" />Events</a>
					</li>
					<?php endif; ?>
					<?php if (site_setting('service_blog_enabled','1') !== '0'): ?>
					<li>
						<a href="<?= base_url() ?>blog-list" class=""><img src="<?= base_url() ?>assets/images/icon/blog1.png" alt="" />Blog posts</a>
					</li>
					<?php endif; ?>
					<?php if (site_setting('service_news_enabled','1') !== '0'): ?>
					<li>
						<a href="<?= base_url() ?>user-news" class=""><img src="<?= base_url() ?>assets/images/icon/cart.png" alt="" />News</a>
					</li>
					<?php endif; ?>
					<?php if (site_setting('service_videos_enabled','1') !== '0'): ?>
					<li>
						<a href="<?= base_url() ?>Video-list" class=""><img src="<?= base_url() ?>assets/images/icon/cart.png" alt="" />Videos</a>
					</li>
					<?php endif; ?>
					<?php if (site_setting('service_classifieds_enabled','1') !== '0'): ?>
					<li>
						<a href="<?= base_url() ?>user-classifieds" class=""><img src="<?= base_url() ?>assets/images/icon/cart.png" alt="" />Classifieds</a>
					</li>
					<?php endif; ?>
					<?php if (site_setting('service_realestate_enabled','1') !== '0'): ?>
					<li>
						<a href="<?= base_url() ?>user-apartments-and-rental" class=""><img src="<?= base_url() ?>assets/images/icon/cart.png" alt="" />Apartments & Rentals</a>
					</li>
					<?php endif; ?>
					<?php if (site_setting('service_realestate_enabled','1') !== '0'): ?>
					<li>
						<a href="<?= base_url() ?>user-house-for-sale" class=""><img src="<?= base_url() ?>assets/images/icon/cart.png" alt="" />House For Sale</a>
					</li>
					<?php endif; ?>
					<li>
						<a href="<?= base_url() ?>ads-summary" class=""><img src="<?= base_url() ?>assets/images/icon/dbl11.png" alt="" />Ad Summary</a>
					</li>
					<li>
						<a href="<?= base_url() ?>logout"><img src="<?= base_url() ?>assets/images/icon/dbl12.png" alt="" />Log Out</a>
					</li>
				</ul>
			</div>
		</div>