<style>
	.pagination-container .pagination {
		justify-content: center;
	}

	.us-ppg-com {
		padding-bottom: 120px;
	}
</style>
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
<!-- START -->
<section class=" blog-head eve-head" style="margin-top: 0px !important;">
	<div class="container">
		<div class="blog-head-inn">
			<h1>Events</h1>
			<?php if (isset($search_query) && !empty($search_query)): ?>
				<p>Search results for "<strong><?= htmlspecialchars($search_query); ?></strong>" - <?= count($Allevent); ?> event(s) found</p>

			<?php else: ?>
				<p>Here post your events, seminar, webinar, fetivals and more</p>
			<?php endif; ?>
		</div>
		<div class="ban-search" style="margin-top: 30px;">
			<form action="<?= base_url('user/event_search'); ?>" method="GET">
				<ul>
					<li class="sr-sea">
						<input type="text" name="q" id="event-search" class="autocomplete"
							placeholder="Search event in your city..."
							value="<?= isset($search_query) ? htmlspecialchars($search_query) : ''; ?>">
						<button type="submit" style="display: none;"></button>
					</li>
				</ul>
			</form>
		</div>
	</div>
</section>
<!--END-->
<!-- START -->
<section class=" event-body">
	<div class="container">
		<div class="us-ppg-com">
			<ul id="intseres">
				<?php
				if (count($Allevent) > 0) {
					foreach ($Allevent as $event) {
				?>
						<li>
							<div class="eve-box">
								<div>
									<?php
									// Create safe slug for event details URL
									$event_slug = !empty($event->event_slug) ? $event->event_slug : createSafeSlug($event->event_name);
									?>
									<a href="<?= base_url('user/event-details/' . $event_slug); ?>">

										<?php if (!empty($event->event_image)): ?>
										<?= responsive_img($event->event_image, $event->event_name) ?>
									<?php else: ?>
										<img src="<?= base_url('assets/images/no_image.png') ?>" alt="<?= e_attr($event->event_name) ?>" loading="lazy" decoding="async">
									<?php endif; ?>
										<span><?= date('M', strtotime($event->created_at)); ?><b><?= date('d', strtotime($event->created_at)); ?></b></span>
									</a>
								</div>
								<div>
									<h4>
										<a href="<?= base_url('event-details/' . $event_slug); ?>"><?= $event->event_name ?></a>
									</h4>
									<span class="addr"><?= $event->event_address ?></span>
									<span class="pho"> <?= $event->event_mobile ?> </span>
								</div>
								<div>
									<?php
									// Get user information for the event host
									$event_user = null;
									if (!empty($event->user_id)) {
										// Use CI instance to get usermodel and fetch user data
										$CI = &get_instance();
										$event_user = $CI->usermodel->getSingleValue($event->user_id, 'user_id', 'user');
									}

									// Set fallback values
									$user_name = $event_user ? $event_user->name : 'Directory Finder';
									$profile_image = 'https://img.freepik.com/premium-vector/user-icon-icon_1076610-59410.jpg';

									if ($event_user && !empty($event_user->profile_image)) {
										$profile_image = base_url($event_user->profile_image);
									}
									?>
									<div class="auth">
										<img src="<?= $profile_image; ?>" alt="<?= htmlspecialchars($user_name); ?>"> <b>Hosted by</b>
										<br>
										<h4><?= htmlspecialchars($user_name); ?></h4>
										<a target="_blank" href="<?= base_url('event-details/' . $event_slug); ?>" class="fclick"></a>
									</div>
								</div>
							</div>
						</li>
					<?php }
				} else { ?>
					<li style="text-align: center; padding: 80px 20px; width: 100%; float: none;">
						<div style="color: #666; font-size: 18px; max-width: 550px; margin: 0 auto;">
							<?php if (isset($search_query) && !empty($search_query)): ?>
								<h3 style="margin-bottom: 20px; color: #333;">No events found for "<?= htmlspecialchars($search_query); ?>"</h3>
								<p style="margin-bottom: 30px; line-height: 1.6;">Try searching with different keywords or <a href="<?= base_url('event'); ?>" style="color: #007bff; text-decoration: none;">browse all events</a></p>
							<?php else: ?>
								<h3 style="margin-bottom: 20px; color: #333;">No events available</h3>
								<p style="line-height: 1.6;">Check back later for upcoming events!</p>
							<?php endif; ?>
						</div>
					</li>
				<?php } ?>
			</ul>
			<?php if (!empty($pagination_links)): ?>
				<div class="pagination-wrapper" style="margin: 40px 0; text-align: center; clear: both; width: 100%; padding-bottom: 100px;">
					<div class="pagination-container" style="display: inline-block; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; max-width: 100%;">
						<div class="pagination-info" style="margin-bottom: 15px; color: #718096; font-size: 14px;">
							<i class="material-icons" style="vertical-align: middle; font-size: 18px; margin-right: 5px;">pages</i>
							Navigate through news
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
		</div>
	</div>
	</ul>
	</div>
	</div>
</section>

<script>
	// Event search functionality
	document.addEventListener('DOMContentLoaded', function() {
		const searchInput = document.getElementById('event-search');
		const searchForm = searchInput.closest('form');

		// Submit form on Enter key press
		searchInput.addEventListener('keypress', function(e) {
			if (e.key === 'Enter') {
				e.preventDefault();
				if (searchInput.value.trim() !== '') {
					searchForm.submit();
				}
			}
		});

		// Auto-submit on input if user stops typing for 1 second (optional)
		let searchTimeout;
		searchInput.addEventListener('input', function() {
			clearTimeout(searchTimeout);
			searchTimeout = setTimeout(function() {
				if (searchInput.value.trim() !== '' && searchInput.value.length > 2) {
					// Uncomment next line for auto-search on typing
					// searchForm.submit();
				}
			}, 1000);
		});
	});
</script>
<!--END-->