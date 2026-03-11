<!-- Custom Styles for All Category Page -->
<style>
	.sh-all-scat-box {
		transition: all 0.3s ease;
		border: 1px solid #eee;
		border-radius: 10px;
		padding: 15px;
		margin-bottom: 15px;
		background: white;
		box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
	}

	.sh-all-scat-box:hover {
		transform: translateY(-3px);
		box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
		border-color: #7bb73f;
	}

	.sh-all-scat-box .lhs {
		margin-right: 15px;
	}

	.sh-all-scat-box .rhs h4 {
		margin: 0 0 8px 0;
		font-size: 18px;
		font-weight: 600;
	}

	.sh-all-scat-box .rhs h4 a:hover {
		color: #7bb73f !important;
	}

	.search-container input:focus {
		outline: none;
		border-color: #7bb73f;
		box-shadow: 0 0 10px rgba(123, 183, 63, 0.3);
	}

	.search-container button:hover {
		background: #6ba534 !important;
		transform: translateY(-50%) scale(1.05);
	}

	.no-results-message {
		animation: fadeIn 0.5s ease-in;
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

	@media (max-width: 768px) {
		.sh-all-scat-box {
			padding: 12px;
		}

		.sh-all-scat-box .lhs img {
			width: 100px !important;
			height: 100px !important;
		}

		.sh-all-scat-box .rhs h4 {
			font-size: 16px;
		}
	}

	.sh-all-scat-box p {
		display: -webkit-box;
		-webkit-box-orient: vertical;
		-webkit-line-clamp: 1;
		overflow: hidden;
		text-overflow: ellipsis;
		width: 100%;
		white-space: normal;
	}

	.sh-all-scat-box h4 a {
		display: -webkit-box;
		-webkit-box-orient: vertical;
		-webkit-line-clamp: 1;
		overflow: hidden;
		text-overflow: ellipsis;
		width: 100%;
		white-space: normal;
	}

	.sh-all-scat-box img {
		border-radius: 8px;
	}

	/* Sidebar Web Development Section */
	.sidebar-marketing-section {
		width: 100%;

	}

	.sidebar-marketing-section .marketing-card {
		background: linear-gradient(145deg, #2563eb 0%, #1e40af 100%);
		border-radius: 14px;
		padding: 20px 15px;
		margin: 0 10px;
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

	.sidebar-marketing-section .marketing-card .card-content .icon-container {
		width: 44px;
		height: 44px;
		background: rgba(255, 255, 255, 0.15);
		border-radius: 8px;
		display: flex;
		align-items: center;
		justify-content: center;
		margin-bottom: 24px;
		backdrop-filter: blur(10px);
		border: 2px solid rgba(255, 255, 255, 0.25);
	}

	.sidebar-marketing-section .marketing-card .card-content .icon-container svg {
		width: 26px;
		height: 26px;
	}

	.sidebar-marketing-section .marketing-card .card-content .main-heading {
		color: white;
		font-size: 25px;
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
		font-size: 12px;
		margin-bottom: 12px;
	}

	.sidebar-marketing-section .marketing-card .card-content .features-wrapper .feature:last-child {
		margin-bottom: 0;
	}

	.sidebar-marketing-section .marketing-card .card-content .features-wrapper .feature .check-icon {
		width: 17px;
		height: 17px;
		margin-right: 10px;
		flex-shrink: 0;
	}

	.sidebar-marketing-section .marketing-card .card-content .primary-button {
		background: white;
		color: #2563eb;
		border: none;
		padding: 12px 22px;
		font-size: 14px;
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


	.main_lay {
		display: flex;
		align-items: start;
		margin-top: 60px;
		gap: 30px;
	}

	.lbc {
		width: 71%;
	}

	.rsb {
		width: 28%;
	}



	.blog-body .us-ppg-com ul {
		width: 100%;
	}

	.blog-body .us-ppg-com ul li {
		width: 33.3333% !important;
	}

	.pagination-wrapper {
		padding-bottom: 100px;
	}

	.all-cate-pg {
		padding-top: 0px !important;
	}

	.sh-all-scat-box .lhs img {
		border-radius: 8px;
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

		.main_lay {
			flex-direction: column;
		}

		.lbc {
			width: 100%;
		}

		.rsb {
			width: 100%;
			margin-bottom: 150px;
		}

		.blog-body .us-ppg-com ul li {
			width: 100% !important;
		}

		.pagination-wrapper {
			padding-bottom: 0px;
		}

		.all-cate-pg {
			padding-bottom: 0px !important;
		}

		.sh-all-scat-box .lhs {
			text-align: start !important;
		}

	}

	/* Sidebar Web Development Section */
</style>

<section class="abou-pg commun-pg-main">
	<div class="about-ban comunity-ban" style="margin-top: 0px !important;">
		<h1>All Services</h1>
		<p>Connect with the right Service Experts</p>
		<form method="GET" action="<?= base_url('user/all-category/category-search'); ?>" class="search-container" style="position: relative; max-width: 400px; margin: 20px auto;">
			<input type="text" name="q" id="tail-se" placeholder="Search categories here.." style="width: 100%; padding: 12px 50px 12px 15px; border: 2px solid #ddd; border-radius: 25px; font-size: 16px;">
			<button type="submit" id="search-btn" style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); background: #7bb73f; border: none; border-radius: 50%; width: 40px; height: 40px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
				<svg width="18" height="18" fill="white" viewBox="0 0 16 16">
					<path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85zm-5.242 1.656a5 5 0 1 1 0-10 5 5 0 0 1 0 10z" />
				</svg>
			</button>
		</form>
	</div>
</section>
<!-- START -->
<section class="main_lay container">
	<div class="str all-cate-pg lbc">
		<div class="">
			<div class="row">
				<div class="sh-all-scat">
					<?php
					// Check if this is a search request
					$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
					$categories_to_show = $AllCategory;

					// If search query exists, filter categories
					if (!empty($search_query)) {
						$categories_to_show = array();
						foreach ($AllCategory as $category) {
							// Search in category name and description
							$name_match = stripos($category->category_name, $search_query) !== false;
							$desc_match = !empty($category->category_description) && stripos($category->category_description, $search_query) !== false;

							if ($name_match || $desc_match) {
								$categories_to_show[] = $category;
							}
						}
					}

					if (count($categories_to_show) > 0) {
						foreach ($categories_to_show as $category) {
							// Generate category URL with slug
							$category_url = !empty($category->category_slug) ? $category->category_slug : strtolower(str_replace(' ', '-', $category->category_name));

							// Handle fallback image
							$category_image = !empty($category->category_image) ? $category->category_image : 'assets/images/no_photo.jpg';
					?>
							<ul id="tail-re">
								<li>
									<div class="sh-all-scat-box">
										<div class="lhs">
											<img src="<?= base_url($category_image); ?>" alt="<?= htmlspecialchars($category->category_name); ?>"
												onerror="this.src='<?= base_url('assets/images/no_photo.jpg'); ?>'"
												style="width: 150px; height: 150px; object-fit: cover; border-radius: 8px;">
										</div>
										<div class="rhs">
											<h4>
												<a href="<?= base_url('user/all_listing/' . $category_url); ?>" style="color: #333; text-decoration: none;">
													<?= htmlspecialchars($category->category_name); ?>
												</a>
											</h4>
											<?php if (!empty($category->category_description)): ?>
												<p style="color: #666; font-size: 14px; margin: 8px 0; line-height: 1.4;
												display: -webkit-box;
		                                        -webkit-box-orient: vertical;
		                                        -webkit-line-clamp: 1;
		                                        overflow: hidden;
		                                        text-overflow: ellipsis;
		                                        width: 100%;
		                                        white-space: normal;" class="cat_des_line">
													<?= htmlspecialchars(substr($category->category_description, 0, 120)) . (strlen($category->category_description) > 120 ? '...' : ''); ?>
												</p>
											<?php else: ?>
												<p style="color: #999; font-size: 14px; margin: 8px 0; font-style: italic;">
													Explore <?= htmlspecialchars($category->category_name); ?> services and connect with professionals.
												</p>
											<?php endif; ?>
										</div>
									</div>
								</li>
							</ul>
						<?php }
					} else if (!empty($search_query)) { ?>
						<!-- No Results Found -->
						<div class="no-results-container" style="text-align: center; padding: 60px 20px; background: #f8f9fa; border-radius: 15px; margin: 20px 0;">
							<div style="margin-bottom: 30px;">
								<svg width="80" height="80" fill="#ccc" viewBox="0 0 24 24" style="margin-bottom: 20px;">
									<path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" />
								</svg>
							</div>
							<h3 style="color: #666; margin-bottom: 15px; font-size: 24px;">No Categories Found</h3>
							<p style="color: #888; margin-bottom: 25px; font-size: 16px;">
								Sorry, no categories match your search for "<strong><?php echo htmlspecialchars($search_query); ?></strong>"
							</p>
							<p style="color: #999; margin-bottom: 30px;">
								Try searching with different keywords or browse all categories below.
							</p>
							<a href="/all-category" style="background: #7bb73f; color: white; padding: 12px 25px; border: none; border-radius: 25px; font-size: 16px; cursor: pointer; transition: all 0.3s ease; text-decoration: none; display: inline-block;">
								<i class="fa fa-arrow-left" style="margin-right: 8px;"></i> View All Categories
							</a>
						</div>
					<?php } ?>
				</div>

				<!-- Pagination Section (only show when not searching) -->
				<?php if (isset($link) && !empty($link) && empty($search_query)): ?>
					<div class="pagination-wrapper" style="margin: 40px 0; text-align: center; clear: both; width: 100%;">
						<div class="pagination-container" style="display: inline-block; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; max-width: 100%;">
							<div class="pagination-info" style="margin-bottom: 15px; color: #718096; font-size: 14px;">
								Showing <?php echo $showing_from; ?>-<?php echo $showing_to; ?> of <?php echo $total_categories; ?> categories
							</div>
							<div class="pagination-links">
								<?php echo $link; ?>
							</div>
						</div>
					</div>
					<style>
						/* Enhanced Pagination Styles */
						.pagination-wrapper .pagination {
							margin: 0;
							display: flex;
							justify-content: center;
							align-items: center;
							flex-wrap: wrap;
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
						}

						.pagination-wrapper .pagination li a:hover {
							background: #7bb73f;
							color: white !important;
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
							border-radius: 5px !important;
						}

						.pagination-wrapper .pagination li:first-child a:hover,
						.pagination-wrapper .pagination li:last-child a:hover {
							background: #180f5e;
							color: white;
							border-color: #180f5e;
						}

						/* Responsive Pagination */
						@media (max-width: 768px) {

							.pagination-wrapper .pagination li a,
							.pagination-wrapper .pagination li span {
								padding: 6px 10px;
								font-size: 12px;
								min-width: 36px;
							}

							.pagination-container {
								padding: 15px;
								margin: 0 10px;
							}

							.pagination-info {
								font-size: 12px;
								margin-bottom: 10px;
							}
						}

						@media (max-width: 480px) {
							.pagination-wrapper .pagination {
								gap: 3px;
							}

							.pagination-wrapper .pagination li a,
							.pagination-wrapper .pagination li span {
								padding: 5px 8px;
								font-size: 11px;
								min-width: 32px;
							}
						}

						/* Add animation */
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
	</div>
	<!-- Right Side Bar -->
	<div class="rsb">
		<!-- Web Development Card -->
		<section class="sidebar-marketing-section">
			<div class="marketing-card">
				<div class="card-content">
					<div class="icon-container">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<rect x="2" y="3" width="20" height="14" rx="2" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
							<path d="M8 21h8" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
							<path d="M12 17v4" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
							<path d="M7 7h10M7 11h6" stroke="white" stroke-width="1.5" stroke-linecap="round" />
						</svg>
					</div>

					<h2 class="main-heading">Do You Need a Website?</h2>
					<p class="description">
						Transform your business with a professional, modern website that drives results.
					</p>

					<div class="features-wrapper">
						<div class="feature">
							<svg class="check-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<circle cx="12" cy="12" r="10" fill="rgba(255, 255, 255, 0.25)" />
								<path d="M8 12.5l2.5 2.5 5.5-5.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
							</svg>
							<span>Custom design tailored to your brand</span>
						</div>
						<div class="feature">
							<svg class="check-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<circle cx="12" cy="12" r="10" fill="rgba(255, 255, 255, 0.25)" />
								<path d="M8 12.5l2.5 2.5 5.5-5.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
							</svg>
							<span>Mobile-responsive & fast loading</span>
						</div>
						<div class="feature">
							<svg class="check-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<circle cx="12" cy="12" r="10" fill="rgba(255, 255, 255, 0.25)" />
								<path d="M8 12.5l2.5 2.5 5.5-5.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
							</svg>
							<span>SEO optimized for better visibility</span>
						</div>
					</div>

					<a href="/contact-us" class="primary-button">Get Your Website</a>
				</div>
			</div>
		</section>
		<!-- Web Development Card -->
	</div>
</section>
<!-- END -->

<script>
	$(document).ready(function() {
		// Get current URL and check if it's a search
		const urlParams = new URLSearchParams(window.location.search);
		const searchQuery = urlParams.get('q');

		// If there's a search query in URL, populate search box and filter
		if (searchQuery) {
			$("#tail-se").val(searchQuery);
			performSearch();
		}

		// Enhanced search functionality for categories
		function performSearch() {
			var searchValue = $("#tail-se").val().toLowerCase().trim();
			var visibleCount = 0;

			$(".sh-all-scat-box").each(function() {
				var categoryName = $(this).find('h4 a').text().toLowerCase();
				var categoryDescription = $(this).find('p').text().toLowerCase();

				// Search in both category name and description
				var isMatch = categoryName.indexOf(searchValue) > -1 ||
					categoryDescription.indexOf(searchValue) > -1;

				if (searchValue === '' || isMatch) {
					$(this).closest('ul').show();
					visibleCount++;
				} else {
					$(this).closest('ul').hide();
				}
			});

			// Show "No results found" message if needed
			if (visibleCount === 0 && searchValue !== '') {
				if ($('.no-results-message').length === 0) {
					$('.sh-all-scat').append('<div class="no-results-message" style="text-align: center; padding: 40px; color: #666;"><h3>No categories found</h3><p>Try searching with different keywords</p></div>');
				}
			} else {
				$('.no-results-message').remove();
			}
		}

		// Function to redirect to search URL
		function redirectToSearch() {
			var searchValue = $("#tail-se").val().trim();
			if (searchValue !== '') {
				// Use the correct URL structure with user controller
				window.location.href = '<?= base_url("user/all-category/category-search"); ?>?q=' + encodeURIComponent(searchValue);
			}
		}

		// Search on keyup for real-time filtering
		$("#tail-se").on("keyup", function() {
			// Only perform real-time search, don't redirect
			performSearch();
		});

		// Search button click - perform search with URL update
		$("#search-btn").on("click", function(e) {
			e.preventDefault();
			var searchValue = $("#tail-se").val().trim();
			if (searchValue === '') {
				alert('Please enter a search term');
				return false;
			}
			performSearchWithURL(searchValue);
		});

		// Form submission handler
		$('.search-container').on("submit", function(e) {
			e.preventDefault();
			var searchValue = $("#tail-se").val().trim();
			if (searchValue === '') {
				alert('Please enter a search term');
				return false;
			}
			performSearchWithURL(searchValue);
		});

		// Enter key press - perform search
		$("#tail-se").on("keypress", function(e) {
			if (e.which === 13) {
				e.preventDefault();
				var searchValue = $(this).val().trim();
				if (searchValue === '') {
					alert('Please enter a search term');
					return false;
				}
				performSearchWithURL(searchValue);
			}
		});

		// Function to perform search and update URL
		function performSearchWithURL(searchValue) {
			// Update URL with search parameter
			var newUrl = window.location.pathname + '?q=' + encodeURIComponent(searchValue);
			window.history.pushState({
				search: searchValue
			}, '', newUrl);

			// Perform the search
			performSearch();
		}

		// Clear search functionality
		$("#tail-se").on("input", function() {
			if ($(this).val() === '') {
				$(".sh-all-scat-box").closest('ul').show();
				$('.no-results-message').remove();

				// If we're on search page and input is cleared, go back to all categories
				if (window.location.pathname.includes('category-search')) {
					window.location.href = '<?= base_url("user/all-category"); ?>';
				}
			}
		});

		// Clear search function
		function clearSearch() {
			$("#tail-se").val('');
			$('.no-results-container').hide();
			$(".sh-all-scat-box").closest('ul').show();
			// Remove search query from URL
			window.history.replaceState({}, document.title, window.location.pathname);
		}
	});
</script>