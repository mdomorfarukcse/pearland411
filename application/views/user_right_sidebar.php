<!--RIGHT SECTION-->

<div class="ud-rhs">

	<div class="ud-rhs-promo">

		<h3>Promote my business</h3>

		<p>Your listing show on the top of the respective category page</p> <a href="/pricing-plan">Start now</a>

	</div>

	<!--    //Total Point Section Starts-->

	<!-- <div class="ud-rhs-poin">

					<div class="ud-rhs-poin1">

						<h4>Your points</h4>

						<span class="count1">60</span>

					</div>

					<div class="ud-rhs-poin2">

						<h3>Earn more credit points</h3>

						<p>Use this poins to promote your listing. <a href="#">Click here</a> for demo</p> <a href="#" class="cta">Buy Points</a>

					</div>

				</div> -->

	<!--    //Total Point Section Ends-->

	<div class="ud-rhs-pay">

		<div class="ud-rhs-pay-inn">

			<h3>Payment Information</h3>

			<?php
			// Get logged user info
			$logged_id = NULL;
			if ($this->session->userdata('logged_user')) {
				$session = $this->session->userdata('logged_user');
				$logged_id = $session['logged_id'];
			}

			// Get subscription information
			$subscription_info = null;
			if ($logged_id) {
				$subscription_info = $this->usermodel->getUserSubscription($logged_id);
			}

			if ($subscription_info && !empty($subscription_info)) {
				// Calculate remaining days
				$end_date = new DateTime($subscription_info->end_date);
				$current_date = new DateTime();
				$remaining_days = $current_date < $end_date ? $current_date->diff($end_date)->days : 0;

				// Format dates
				$start_date_formatted = date('d, M Y', strtotime($subscription_info->start_date));
				$end_date_formatted = date('d, M Y', strtotime($subscription_info->end_date));

				// Calculate duration
				$start = new DateTime($subscription_info->start_date);
				$end = new DateTime($subscription_info->end_date);
				$duration_diff = $start->diff($end);
				$duration_text = '';
				if ($duration_diff->y > 0) {
					$duration_text = $duration_diff->y . ' year' . ($duration_diff->y > 1 ? 's' : '');
				} elseif ($duration_diff->m > 0) {
					$duration_text = $duration_diff->m . ' month' . ($duration_diff->m > 1 ? 's' : '');
				} else {
					$duration_text = $duration_diff->d . ' day' . ($duration_diff->d > 1 ? 's' : '');
				}

				// Determine payment status and button
				$payment_status = 'PENDING';
				if (isset($subscription_info->status)) {
					$payment_status = $subscription_info->status;
				} elseif (isset($subscription_info->payment_status)) {
					$payment_status = $subscription_info->payment_status;
				}

				// Get payment amount
				$payment_amount = '$0.00';
				if (isset($subscription_info->price) && $subscription_info->price > 0) {
					$payment_amount = '$' . number_format($subscription_info->price, 2);
				} elseif (isset($subscription_info->amount) && $subscription_info->amount > 0) {
					$payment_amount = '$' . number_format($subscription_info->amount, 2);
				}

				// Get plan name - check multiple possible fields
				$plan_name = 'Basic Plan';
				if (isset($subscription_info->name) && !empty($subscription_info->name)) {
					$plan_name = $subscription_info->name;
				} elseif (isset($subscription_info->plan_name) && !empty($subscription_info->plan_name)) {
					$plan_name = $subscription_info->plan_name;
				}
			?>

				<ul>
					<li><b>Plan name : </b> <?= htmlspecialchars($plan_name) ?></li>
					<li><b>Start date : </b> <?= $start_date_formatted ?></li>
					<li><b>Expiry date : </b> <?= $end_date_formatted ?></li>
					<li><b>Duration : </b> <?= $duration_text ?></li>
					<li><b>Remaining Days: </b> <?= $remaining_days ?></li>
					<li><span class="ud-stat-pay-btn"><b>Checkout cost:</b> <?= $payment_amount ?></span></li>
					<li><span class="ud-stat-pay-btn"><b>Payment Status:</b> <?= strtoupper($payment_status) ?></span></li>
				</ul>

				<?php
				// Check for successful payment status
				$is_payment_completed = false;
				$payment_status_lower = strtolower($payment_status);

				if (in_array($payment_status_lower, ['completed', 'success', 'active', 'paid'])) {
					$is_payment_completed = true;
				}

				if ($is_payment_completed):
				?>
					<a href="<?= base_url('user/my_account') ?>" class="btn btn2">View Details</a>
				<?php else: ?>
					<a href="#" class="btn btn2">Pay Now</a>
				<?php endif; ?>

			<?php } else { ?>
				<!-- No subscription found -->
				<ul>
					<li><b>Plan name : </b> No Active Plan</li>
					<li><b>Start date : </b> -</li>
					<li><b>Expiry date : </b> -</li>
					<li><b>Duration : </b> -</li>
					<li><b>Remaining Days: </b> 0</li>
					<li><span class="ud-stat-pay-btn"><b>Checkout cost:</b> $0.00</span></li>
					<li><span class="ud-stat-pay-btn"><b>Payment Status:</b> NO PLAN</span></li>
				</ul>
				<a href="<?= base_url('pricing-plan') ?>" class="btn btn2">Choose Plan</a>
			<?php } ?>

		</div>

	</div>

	<div class="ud-rhs-pay ud-rhs-repo">

		<div class="ud-rhs-pay-inn">

			<h3>Last week report</h3>

			<ul>

				<li> <span class="view">Enquiry</span>

					<span class="cout">00</span>

					<span class="name">Leads</span>

				</li>

				<li> <span class="view">Views</span>

					<span class="cout">02</span>

					<span class="name">Listings</span>

				</li>

				<li> <span class="view">Views</span>

					<span class="cout">00</span>

					<span class="name">Events</span>

				</li>

				<li> <span class="view">Views</span>

					<span class="cout">01</span>

					<span class="name">Blogs</span>

				</li>

				<li> <span class="view">Views</span>

					<span class="cout">00</span>

					<span class="name">Products</span>

				</li>

				<li> <span class="cout">00</span>

					<span class="name">Messages</span>

				</li>

			</ul>

		</div>

	</div>

	<!--    <div class="ud-rhs-sec-2">-->

	<!--        <h4>-->

	<!--</h4>-->

	<!--        <ul>-->

	<!--            -->

	<!--            <li>-->

	<!--                <a href="-->

	<!--">-->

	<!--                    <img src="<?= base_url() ?>assets/images/user/-->

	<!--" alt="">-->

	<!--                    <h5>-->

	<!--</h5>-->

	<!--                    <p>-->

	<!--: <b>-->

	<!--</b> -->

	<!--: <b> -->

	<!--</b></p>-->

	<!--                </a>-->

	<!--            </li>-->

	<!--                -->

	<!--        </ul>-->

	<!--    </div>-->


<!-- 
	<div class="ud-rhs-promo ud-rhs-promo-1">

		<h3>Community members</h3>

		<p>Follow your favirote business users and grove your online business now.</p> <a href="community">Community</a>

	</div> -->

	<div class="ud-rhs-sec-3">

		<div class="list-mig-like">

			<div class="list-ri-peo-like">

				<h3>Who all are follow you</h3>

				<ul>

					<li>

						<a href="#" target="_blank">

							<img src="<?= base_url() ?>assets/images/user/63520pexels-photo-1130626.jpeg" alt="">

						</a>

					</li>

					<li>

						<a href="#" target="_blank">

							<img src="<?= base_url() ?>assets/images/user/8766pexels-photo-774909.jpeg" alt="">

						</a>

					</li>

					<li>

						<a href="profile/claude-d-dial" target="_blank">

							<img src="<?= base_url() ?>assets/images/user/33654pexels-photo-91227.jpeg" alt="">

						</a>

					</li>

					<li>

						<a href="#" target="_blank">

							<img src="<?= base_url() ?>assets/images/user/4913411004989_334444876752279_544839968_n.jpg" alt="">

						</a>

					</li>

					<li>

						<a href="#" target="_blank">

							<img src="<?= base_url() ?>assets/images/user/475847.jpg" alt="">

						</a>

					</li>

					<li>

						<a href="#" target="_blank">

							<img src="<?= base_url() ?>assets/images/user/475847.jpg" alt="">

						</a>

					</li>

					<li>

						<a href="#" target="_blank">

							<img src="<?= base_url() ?>assets/images/user/475847.jpg" alt="">

						</a>

					</li>

					<!--                    <li>-->

					<!--                        <a href="profile" target="_blank">-->

					<!--                            <img src="<?= base_url() ?>assets/images/user/2.jpg" alt="">-->

					<!--                        </a>-->

					<!--                    </li>-->

					<!--                    <li>-->

					<!--                        <a href="profile" target="_blank">-->

					<!--                            <img src="<?= base_url() ?>assets/images/user/3.jpg" alt="">-->

					<!--                        </a>-->

					<!--                    </li>-->

					<!--                    <li>-->

					<!--                        <a href="profile" target="_blank">-->

					<!--                            <img src="<?= base_url() ?>assets/images/user/4.jpg" alt="">-->

					<!--                        </a>-->

					<!--                    </li>-->

					<!--                    <li>-->

					<!--                        <a href="profile" target="_blank">-->

					<!--                            <img src="<?= base_url() ?>assets/images/user/5.jpg" alt="">-->

					<!--                        </a>-->

					<!--                    </li>-->

					<!--                    <li>-->

					<!--                        <a href="profile" target="_blank">-->

					<!--                            <img src="<?= base_url() ?>assets/images/user/6.jpg" alt="">-->

					<!--                        </a>-->

					<!--                    </li>-->

					<!--                    <li>-->

					<!--                        <a href="profile" target="_blank">-->

					<!--                            <img src="<?= base_url() ?>assets/images/user/7.jpg" alt="">-->

					<!--                        </a>-->

					<!--                    </li>-->

					<!--                    <li>-->

					<!--                        <a href="profile" target="_blank">-->

					<!--                            <img src="<?= base_url() ?>assets/images/user/8.jpg" alt="">-->

					<!--                        </a>-->

					<!--                    </li>-->

				</ul>

			</div>

		</div>

	</div>

</div>

</div>

</section>

<!--END PRICING DETAILS-->