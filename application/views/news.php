<style>
	.pagination-container .pagination {
		justify-content: center;
	}

	.blog-head {
		background: linear-gradient(to top, rgba(252, 252, 252, 0) 10%, #ffffff3d 90%), url(/assets/images/news-bg.jpg);
		background-size: cover;
		background-position: center;
		background-attachment: fixed;
		padding: 60px 0;
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
		gap: 30px;
		margin-bottom: 150px;
	}

	.lbc {
		width: 71%;
	}

	.rsb {
		width: 28%;
	}

	.blog-body .us-ppg-com {
		padding-top: 0px !important;
	}

	/* .blog-body .us-ppg-com ul {
		display: grid !important;
		grid-template-columns: repeat(3, 1fr) !important;
		gap: 15px !important;
	} */

	.blog-body .us-ppg-com ul {
		width: 95%;
	}

	.blog-body .us-ppg-com ul li {
		width: 33.3333% !important;
	}

	.pagination-wrapper {
		padding-bottom: 100px;
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

	}

	/* Sidebar Web Development Section */
</style>

<!-- START -->
<section class=" blog-head" style="margin-top: 0px !important; ">
	<div class="container">
		<div class="blog-head-inn">
			<h1>News</h1>
			<?php if (isset($search_query) && !empty($search_query)): ?>
				<p>Search results for "<strong><?= htmlspecialchars($search_query); ?></strong>" - <?= count($all_news); ?> news item(s) found</p>
			<?php else: ?>
				<p>Stay updated with the latest news and announcements.</p>
			<?php endif; ?>
		</div>
		<div class="ban-search">
			<form action="<?= base_url('news'); ?>" method="GET">
				<ul>
					<li class="sr-sea">
						<input type="text" name="q" id="news-search" class="autocomplete" placeholder="Search news..." value="<?= isset($search_query) ? htmlspecialchars($search_query) : '' ?>">
						<button type="submit" style="display: none;"></button>
					</li>
				</ul>
			</form>
		</div>
	</div>
</section>
<!--END-->
<div class="main_lay container">
	<div class="lbc">
		<section class=" blog-body" style=" display: flex; flex-direction: column; justify-content: center; align-items: center;">
			<div class="">
				<div class="us-ppg-com us-ppg-blog">
					<ul id="intseres">
						<?php if (!empty($all_news)) : ?>
							<?php foreach ($all_news as $news) : ?>
								<li>
									<div class="pro-eve-box" style="border-radius: 10px;">
										<div>
											<?php if (!empty($news->news_image)): ?>
												<?= responsive_img($news->news_image, $news->news_title) ?>
											<?php else: ?>
												<img src="<?= base_url('assets/images/no_image.png') ?>" alt="<?= e_attr($news->news_title) ?>" loading="lazy" decoding="async">
											<?php endif; ?>
										</div>
										<div>
											<p><?= date('d, M Y', strtotime($news->created_at)) ?></p>
											<h2><?= htmlspecialchars($news->news_title) ?></h2>
										</div>
										<a href="<?= base_url('news_details/' . ($news->news_slug ? $news->news_slug : $news->news_id)) ?>" class="fclick">&nbsp;</a>
									</div>
								</li>
							<?php endforeach; ?>
						<?php else : ?>
							<li style="text-align: center; padding: 80px 20px; width: 100%; float: none;">
								<div style="color: #666; font-size: 18px; max-width: 550px; margin: 0 auto;">
									<?php if (isset($search_query) && !empty($search_query)): ?>
										<h3 style="margin-bottom: 20px; color: #333;">No news found for "<?= htmlspecialchars($search_query); ?>"</h3>
										<p style="margin-bottom: 30px; line-height: 1.6;">Try searching with different keywords or <a href="<?= base_url('news'); ?>" style="color: #007bff; text-decoration: none;">browse all news</a></p>
									<?php else: ?>
										<h3 style="margin-bottom: 20px; color: #333;">No news available</h3>
										<p style="line-height: 1.6;">Check back later for the latest news and updates!</p>
									<?php endif; ?>
								</div>
							</li>
						<?php endif; ?>
					</ul>
				</div>
			</div>
		</section>
		<!--END-->

		<?php if (!empty($pagination_links)): ?>
			<div class="pagination-wrapper" style="margin: 40px 0; text-align: center; clear: both; width: 100%;">
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
</div>
</section>
<!--END-->