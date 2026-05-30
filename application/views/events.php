<style>
	.pagination-container .pagination {
		justify-content: center;
	}

	.us-ppg-com {
		padding-bottom: 120px;
	}

	.ban-search .event-search-wrap {
		position: relative;
		max-width: 540px;
		margin: 0 auto;
	}

	.ban-search .event-search-wrap input.autocomplete {
		padding-right: 56px;
	}

	.ban-search .event-search-wrap .event-search-btn {
		position: absolute;
		top: 50%;
		right: 6px;
		transform: translateY(-50%);
		border: 0;
		background: #7bb73f;
		color: #fff;
		width: 42px;
		height: 42px;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		cursor: pointer;
		transition: background 0.2s ease;
	}

	.ban-search .event-search-wrap .event-search-btn:hover {
		background: #6ba532;
	}

	.event-card-meta {
		display: flex;
		flex-wrap: wrap;
		gap: 10px;
		margin: 8px 0 4px;
		font-size: 13px;
		color: #475569;
	}

	.event-card-meta span {
		display: inline-flex;
		align-items: center;
		gap: 4px;
		background: #f1f5f9;
		padding: 3px 10px;
		border-radius: 999px;
	}

	.event-card-meta .material-icons {
		font-size: 15px;
		color: #7bb73f;
	}
</style>
<?php
if (!function_exists('createSafeSlug')) {
	function createSafeSlug($text)
	{
		$text = strip_tags((string) $text);
		$text = strtolower($text);
		$text = preg_replace('/[^a-z0-9]+/', '-', $text);
		$text = trim($text, '-');
		return preg_replace('/-+/', '-', $text);
	}
}
?>
<!-- START -->
<section class=" blog-head eve-head" style="margin-top: 0px !important;">
	<div class="container">
		<div class="blog-head-inn">
			<h1>Events</h1>
			<?php if (!empty($search_query)): ?>
				<p>Search results for "<strong><?= e($search_query) ?></strong>" — <?= count($Allevent) ?> event<?= count($Allevent) === 1 ? '' : 's' ?> on this page</p>
			<?php else: ?>
				<p>Discover concerts, seminars, webinars, festivals and community events around Pearland.</p>
			<?php endif; ?>
		</div>
		<div class="ban-search" style="margin-top: 30px;">
			<form action="<?= base_url('event') ?>" method="GET" role="search">
				<div class="event-search-wrap">
					<input type="search" name="q" id="event-search" class="autocomplete"
						placeholder="Search events by name, description, or city…"
						value="<?= e_attr($search_query ?? '') ?>"
						aria-label="Search events">
					<button type="submit" class="event-search-btn" aria-label="Search">
						<i class="material-icons" aria-hidden="true">search</i>
					</button>
				</div>
			</form>
		</div>
	</div>
</section>
<!--END-->
<!-- START -->
<section class=" event-body">
	<div class="container">
		<?php $slot = 'events-sidebar'; include APPPATH . 'views/partials/ad_slot.php'; ?>
		<div class="us-ppg-com">
			<ul id="intseres">
				<?php if (!empty($Allevent)): ?>
					<?php foreach ($Allevent as $event): ?>
						<?php
							$event_slug = !empty($event->event_slug) ? $event->event_slug : createSafeSlug($event->event_name);
							$detail_url = base_url('event-details/' . rawurlencode($event_slug));

							// Prefer the actual event date; fall back to the row's created_at.
							$date_src = !empty($event->event_start_date) ? $event->event_start_date : $event->created_at;
							$date_ts  = $date_src ? strtotime($date_src) : false;

							$host_name = !empty($event->host_name) ? $event->host_name : 'Pearland411';
							$host_image = !empty($event->host_image)
								? base_url($event->host_image)
								: base_url('assets/images/no_photo.jpg');

							$is_upcoming = $date_ts && $date_ts >= strtotime('today');
						?>
						<li>
							<div class="eve-box">
								<div>
									<a href="<?= e_attr($detail_url) ?>">
										<?php if (!empty($event->event_image)): ?>
											<?= responsive_img($event->event_image, $event->event_name) ?>
										<?php else: ?>
											<img src="<?= base_url('assets/images/no_image.png') ?>" alt="<?= e_attr($event->event_name) ?>" loading="lazy" decoding="async">
										<?php endif; ?>
										<?php if ($date_ts): ?>
											<span><?= e(date('M', $date_ts)) ?><b><?= e(date('d', $date_ts)) ?></b></span>
										<?php endif; ?>
									</a>
								</div>
								<div>
									<h4>
										<a href="<?= e_attr($detail_url) ?>"><?= e($event->event_name) ?></a>
									</h4>
									<div class="event-card-meta">
										<?php if ($date_ts): ?>
											<span><i class="material-icons">event</i><?= e(date('D, M j, Y', $date_ts)) ?></span>
										<?php endif; ?>
										<?php if (!empty($event->event_time)): ?>
											<span><i class="material-icons">schedule</i><?= e($event->event_time) ?></span>
										<?php endif; ?>
										<?php if ($is_upcoming): ?>
											<span style="background:#dcfce7;color:#166534;"><i class="material-icons" style="color:#166534;">check_circle</i>Upcoming</span>
										<?php endif; ?>
									</div>
									<?php if (!empty($event->event_address)): ?>
										<span class="addr"><?= e($event->event_address) ?></span>
									<?php endif; ?>
									<?php if (!empty($event->event_mobile)): ?>
										<span class="pho"><?= e($event->event_mobile) ?></span>
									<?php endif; ?>
								</div>
								<div>
									<div class="auth">
										<img src="<?= e_attr($host_image) ?>" alt="<?= e_attr($host_name) ?>" loading="lazy" decoding="async">
										<b>Hosted by</b>
										<br>
										<h4><?= e($host_name) ?></h4>
										<a href="<?= e_attr($detail_url) ?>" class="fclick" aria-label="View event details"></a>
									</div>
								</div>
							</div>
						</li>
					<?php endforeach; ?>
				<?php else: ?>
					<li style="text-align: center; padding: 80px 20px; width: 100%; float: none;">
						<div style="color: #666; font-size: 18px; max-width: 550px; margin: 0 auto;">
							<?php if (!empty($search_query)): ?>
								<h3 style="margin-bottom: 20px; color: #333;">No events found for "<?= e($search_query) ?>"</h3>
								<p style="margin-bottom: 30px; line-height: 1.6;">Try different keywords or <a href="<?= base_url('event') ?>" style="color: #007bff; text-decoration: none;">browse all events</a>.</p>
							<?php else: ?>
								<h3 style="margin-bottom: 20px; color: #333;">No events available right now</h3>
								<p style="line-height: 1.6;">Check back soon — new events are added regularly.</p>
							<?php endif; ?>
						</div>
					</li>
				<?php endif; ?>
			</ul>

			<?php if (!empty($pagination_links)): ?>
				<div class="pagination-wrapper" style="margin: 40px 0; text-align: center; clear: both; width: 100%; padding-bottom: 100px;">
					<div class="pagination-container" style="display: inline-block; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; max-width: 100%;">
						<div class="pagination-info" style="margin-bottom: 15px; color: #718096; font-size: 14px;">
							<i class="material-icons" style="vertical-align: middle; font-size: 18px; margin-right: 5px;">pages</i>
							Navigate through events
						</div>
						<div class="pagination-links">
							<?= $pagination_links ?>
						</div>
					</div>
				</div>
				<style>
					.pagination-wrapper .pagination {
						margin: 0;
						display: flex;
						justify-content: center;
						align-items: center;
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
					.pagination-wrapper .pagination li.page-item.active a,
					.pagination-wrapper .pagination li.page-item.active span {
						background: linear-gradient(135deg, #7bb73f 0%, #6ba532 100%);
						color: white;
						border-color: #7bb73f;
						box-shadow: 0 4px 12px rgba(123, 183, 63, 0.4);
						font-weight: 600;
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
				</style>
			<?php endif; ?>
		</div>

		<?php $cta_variant = 'banner'; include APPPATH . 'views/partials/web_dev_cta.php'; ?>
	</div>
</section>

<script>
	(function () {
		var input = document.getElementById('event-search');
		if (!input) return;
		var form = input.closest('form');
		input.addEventListener('keypress', function (e) {
			if (e.key === 'Enter') {
				e.preventDefault();
				if (input.value.trim() !== '') form.submit();
			}
		});
	})();
</script>
<!--END-->
