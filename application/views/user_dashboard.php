<?php
// Use subscription data from controller (already fetched properly)
$has_subscription = isset($has_subscription) ? $has_subscription : false;
$subscription_limits = isset($subscription_limits) && is_array($subscription_limits) ? $subscription_limits : [];
$usage_counts = isset($usage_counts) && is_array($usage_counts) ? $usage_counts : [];

// Extract plan information
$plan_name = isset($subscription_limits['plan_name']) ? $subscription_limits['plan_name'] : 'Free';

// Extract limits (-1 means Unlimited)
$listing_limit = isset($subscription_limits['listing']) ? (int)$subscription_limits['listing'] : -1;
$event_limit   = isset($subscription_limits['event'])   ? (int)$subscription_limits['event']   : -1;
$blog_limit    = isset($subscription_limits['blog'])    ? (int)$subscription_limits['blog']    : -1;

// Source arrays from controller (fallback to empty if not provided)
$allListingsArr = (isset($AllListings) && is_array($AllListings)) ? $AllListings : [];
$allEventsArr   = (isset($AllEvents)   && is_array($AllEvents))   ? $AllEvents   : [];
$allBlogsArr    = (isset($AllBlogs)    && is_array($AllBlogs))    ? $AllBlogs    : [];

// What the member owns, counted by the controller. count() of the arrays
// is the length of a 10-row preview, not usage.
$dashboard_used = (isset($dashboard_used) && is_array($dashboard_used)) ? $dashboard_used : array();
$listing_used = isset($dashboard_used['listing']) ? (int) $dashboard_used['listing'] : count($allListingsArr);
$event_used   = isset($dashboard_used['event'])   ? (int) $dashboard_used['event']   : count($allEventsArr);
$blog_used    = isset($dashboard_used['blog'])    ? (int) $dashboard_used['blog']    : count($allBlogsArr);

// Who to greet. This was the literal "Welcom back, User Test".
$dash_name = (isset($user) && is_object($user) && !empty($user->name)) ? (string) $user->name : '';

// Determine what to display on dashboard without altering design
if ($has_subscription) {
	// For unlimited plans (listing_limit === -1 or 0), show all listings
	if ($listing_limit === -1 || $listing_limit === 0) {
		$listingDisplay = $allListingsArr;
	} else {
		$listingDisplay = array_slice($allListingsArr, 0, max(0, $listing_limit));
	}
	$eventDisplay   = ($event_limit   === -1 || $event_limit === 0) ? $allEventsArr   : array_slice($allEventsArr,   0, max(0, $event_limit));
	$blogDisplay    = ($blog_limit    === -1 || $blog_limit === 0) ? $allBlogsArr    : array_slice($allBlogsArr,    0, max(0, $blog_limit));
} else {
	// Non-subscribed users see no data in sections
	$listingDisplay = [];
	$eventDisplay   = [];
	$blogDisplay    = [];
}

// Create safe slug function (if not already defined)
if (!function_exists('createSafeSlug')) {
	function createSafeSlug($string)
	{
		// Convert to lowercase
		$slug = strtolower($string);
		// Replace non-alphanumeric characters with hyphens
		$slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
		// Trim hyphens from start and end
		$slug = trim($slug, '-');
		return $slug;
	}
}
?>

<!--CENTER SECTION-->

<div class="ud-cen">

	<div class="log-bor">&nbsp;</div> <span class="udb-inst">User Dashboard</span>

	<div class="cd-cen-intr">

		<div class="cd-cen-intr-inn">

			<h2>Welcome back<?= $dash_name !== '' ? ', <b>' . e($dash_name) . '</b>' : '' ?></h2>

			<p>Stay up to date reports in your listing, products, events and blog reports here</p>

		</div>

	</div>

	<div class="ud-cen-s1">

		<ul>

			<li>

				<?php // Counts only. The plan limits are internal (tracker R192:
				      // "should not be displayed on the user-facing dashboard"); the
				      // add screens still enforce them and say so when one is reached. ?>
				<div> <b><?= (int) $listing_used ?></b>

					<h4>Your Listings</h4>

					<p>&nbsp;</p> <a href="<?= base_url('user-listing') ?>">&nbsp;</a>

				</div>

			</li>

			<li>

				<div> <b><?= (int) $event_used ?></b>

					<h4>Your Events</h4>

					<p>&nbsp;</p> <a href="<?= base_url('user-events') ?>">&nbsp;</a>

				</div>

			</li>

			<li>

				<div> <b><?= (int) $blog_used ?></b>

					<h4>Your Blog Posts</h4>

					<p>&nbsp;</p> <a href="<?= base_url('blog-list') ?>">&nbsp;</a>

				</div>

			</li>

		</ul>

	</div>

	<!-- START -->

	<!--<div class="ud-cen-s3 ud-cen-s4">-->

	<!--	<h2>Profile page</h2>-->

	<!--	<a href="db-my-profile-edit.html" class="db-tit-btn">Edit profile page</a>-->

	<!--	<div class="ud-payment ud-pro-link">-->

	<!--		<div class="pay-lhs">-->

	<!--			<div class="lis-pro-badg">-->

	<!--				<div>-->

	<!--					<img src="<?= base_url() ?>assets/images/user/62736rn53themes.png" alt="">-->

	<!--					<h4>Rn53 Themes</h4>-->

	<!--					<p>Member since 26, Mar 2021</p>-->

	<!--				</div> <a href="profile.html" class="fclick" target="_blank">&nbsp;</a>-->

	<!--			</div>-->

	<!--		</div>-->

	<!--		<div class="pay-rhs">-->

	<!--			<ul>-->

	<!--				<li><b>Name : </b> Rn53 Themes</li>-->

	<!--				<li><b>Followers : </b>  <span>07</span>-->

	<!--				</li>-->

	<!--				<li><b>City : </b> Sydney</li>-->

	<!--				<li><b>Email : </b> rn53themes@gmail.com</li>-->

	<!--				<li class="pro">-->

	<!--					<input type="text" value="profile.html" readonly>-->

	<!--				</li>-->

	<!--				<li class="pre"><a target="_blank" href="profile.html">View my profile page</a>-->

	<!--				</li>-->

	<!--			</ul>-->

	<!--		</div>-->

	<!--	</div>-->

	<!--</div>-->

	<!-- END -->

	<!-- START -->

	<!--<div class="ud-cen-s3 ud-cen-s4">-->

	<!--	<h2>Business page</h2>-->

	<!--	<a href="company-profile-edit.html" class="db-tit-btn">Edit business page</a>-->

	<!--	<div class="ud-payment ud-pro-link bus-pg">-->

	<!--		<div class="pay-lhs">-->

	<!--			<div class="lis-pro-badg">-->

	<!--				<div>-->

	<!--					<img src="<?= base_url() ?>assets/images/user/39791rn53-themes.png" alt="">-->

	<!--					<h4>Rn53 Themes net</h4>-->

	<!--					<p>Member since 26, Mar 2021</p>-->

	<!--				</div> <a href="company-profile.html" class="fclick" target="_blank">&nbsp;</a>-->

	<!--			</div>-->

	<!--		</div>-->

	<!--		<div class="pay-rhs">-->

	<!--			<ul>-->

	<!--				<li><b>Name : </b> Rn53 Themes net</li>-->

	<!--				<li><b>Page views : </b>  <span>19</span>-->

	<!--				</li>-->

	<!--				<li class="pro">-->

	<!--					<input type="text" value="company-profile.html" readonly>-->

	<!--				</li>-->

	<!--				<li class="pre"><a target="_blank" href="company-profile.html">View business page</a>-->

	<!--				</li>-->

	<!--			</ul>-->

	<!--		</div>-->

	<!--	</div>-->

	<!--</div>-->

	<!-- END -->

	<div class="ud-cen-s2">

		<h2>Listing Details</h2>



		<table data-admin-table class="responsive-table bordered">

			<thead>

				<tr>

					<th>No</th>

					<th>Listing Name</th>

					<th>Rating</th>

					<th>Views</th>

					<!--                    <th>Expiry on</th>-->

					<th>Status</th>

					<th>Edit</th>

					<!--                    <th>-->

					<!--</th>-->

					<th>Preview</th>

				</tr>

			</thead>

			<tbody>

				<?php

				if (count($listingDisplay) > 0) {

					$i = 1;

					foreach ($listingDisplay as $value) {

				?>

						<tr>

							<td><?= $i; ?></td>

							<td>

								<img src="<?= base_url($value->listing_image); ?>"><?= e($value->listing_name) ?> <span><?= date('d, M Y', strtotime($value->created_at)); ?></span>

							</td>

							<td><span class="db-list-rat">0</span>

							</td>

							<td><span class="db-list-rat">1</span>

							</td>

							<!--									<td><span class="db-list-ststus">8 June 2020</span></td>-->

							<td><span class="db-list-ststus"><?= $value->listing_status == 1 ? 'Active' : 'Inactive'; ?></span>

							</td>

							<td><a href="<?= base_url('user/edit-listing/' . $value->listing_slug); ?>" class="db-list-edit">Edit</a>

							</td>

							<!--									<td><a href="promote-business.html?code=-->

							<!--&&type=listing" class="db-list-edit">-->

							<!--</a></td>-->

							<td><a href="<?= base_url('listing-details/' . $value->listing_slug); ?>" class="db-list-edit" target="_blank">Preview</a>

							</td>

						</tr>

					<?php

						$i++;
					}
				} else {

					?>

					<tr>

						<td colspan="7">
							<p></p>No Listings Available!</p>
						</td>

					</tr>

				<?php

				}

				?>

			</tbody>

		</table>

	</div>

	<div class="ud-cen-s3">

		<h2>Events</h2>



		<ul>

			<?php

			if (count($eventDisplay) > 0) {

				foreach ($eventDisplay as $value) {

			?>

					<li>

						<div class="db-eve">

							<a href="<?= base_url('event-details/' . rawurlencode(!empty($value->event_slug) ? $value->event_slug : createSafeSlug($value->event_name))); ?>">

								<img src="<?= base_url($value->event_image); ?>" alt="">

								<h5><?= e($value->event_name); ?></h5>

								<span>Created: <?= date('d, M Y', strtotime($value->created_at)); ?></span>

							</a>

						</div>

					</li>

				<?php

				}
			} else {

				?>

				<li>No Events Available!</li>

			<?php

			}

			?>

		</ul>

	</div>

	<div class="ud-cen-s3 ud-cen-s4">

		<h2>Blog posts</h2>



		<ul>

			<?php

			if (count($blogDisplay) > 0) {

				foreach ($blogDisplay as $value) {

			?>

					<li>

						<div class="db-eve">

							<a href="<?= base_url('user/blog-details/' . $value->blog_slug); ?>">

								<img src="<?= base_url($value->blog_image); ?>" alt="">

								<h5><?= e($value->blog_name); ?></h5>

								<span>Created: <?= date('d, M Y', strtotime($value->created_at)); ?></span>

							</a>

						</div>

					</li>

				<?php

				}
			} else {

				?>

				<li>No Blog Posts Available!</li>

			<?php

			}

			?>

		</ul>

	</div>

</div>