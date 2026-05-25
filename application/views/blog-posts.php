<!-- START -->
<section class=" blog-head" style="margin-top: 0px !important;">
	<div class="container">
		<div class="blog-head-inn">
			<h1>Blog posts</h1>
			<?php if (isset($search_query) && !empty($search_query)): ?>
				<p>Search results for "<strong><?= htmlspecialchars($search_query); ?></strong>" - <?= count($AllBlog); ?> blog(s) found</p>
			<?php else: ?>
				<p>Here submit your blogs and make your own audiance.</p>
			<?php endif; ?>
		</div>
		<div class="ban-search">
			<form action="<?= base_url('user/all-blog'); ?>" method="GET">
				<ul>
					<li class="sr-sea">
						<input type="text" name="q" id="blog-search" class="autocomplete" placeholder="Search blog posts..." value="<?= isset($search_query) ? htmlspecialchars($search_query) : ''; ?>">
						<button type="submit" style="display: none;"></button>
					</li>
				</ul>
			</form>
		</div>
	</div>
</section>
<!--END-->
<!-- START -->
<section class=" blog-body" style="padding-bottom: 170px !important;">
	<div class="container">
		<div class="us-ppg-com us-ppg-blog">
			<ul id="intseres">
				<?php
				if (count($AllBlog) > 0) {
					foreach ($AllBlog as $blog) {
				?>
						<li>
							<div class="pro-eve-box" style="border-radius: 10px;">
								<div>
									<?php if (!empty($blog->blog_image)): ?>
											<?= responsive_img($blog->blog_image, $blog->blog_name) ?>
										<?php else: ?>
											<img src="<?= base_url('assets/images/no_image.png') ?>" alt="<?= e_attr($blog->blog_name) ?>" loading="lazy" decoding="async">
										<?php endif; ?>
								</div>
								<div>
									<p><?= date('d, M Y', strtotime($blog->created_at)); ?></p>
									<h2><?= $blog->blog_name; ?></h2>
								</div> <a href="<?= base_url('user/blog-details/' . $blog->blog_slug); ?>" class="fclick">&nbsp;</a>
							</div>
						</li>
					<?php }
				} else { ?>
					<li style="text-align: center; padding: 80px 20px; width: 100%; float: none;">
						<div style="color: #666; font-size: 18px; max-width: 550px; margin: 0 auto;">
							<?php if (isset($search_query) && !empty($search_query)): ?>
								<h3 style="margin-bottom: 20px; color: #333;">No blog posts found for "<?= htmlspecialchars($search_query); ?>"</h3>
								<p style="margin-bottom: 30px; line-height: 1.6;">Try searching with different keywords or <a href="<?= base_url('all-blog'); ?>" style="color: #007bff; text-decoration: none;">browse all blog posts</a></p>
							<?php else: ?>
								<h3 style="margin-bottom: 20px; color: #333;">No blog posts available</h3>
								<p style="line-height: 1.6;">Check back later for new blog posts!</p>
							<?php endif; ?>
						</div>
					</li>
				<?php } ?>
			</ul>
		</div>

		<!-- Pagination -->
		<?php if (isset($pagination_links) && !empty($pagination_links)): ?>
			<div class="pagination" style="margin-top: 40px; text-align: center;">
				<?= $pagination_links; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
<!--END-->

<script>
	// Blog search functionality
	document.addEventListener('DOMContentLoaded', function() {
		const searchInput = document.getElementById('blog-search');
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