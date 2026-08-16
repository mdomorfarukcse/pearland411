<?php
/**
 * The navy section strip that runs under the header bar.
 *
 * Extracted from header.php so the homepage can show the same strip. index.php
 * is a self-contained page with its own header chrome, and this element simply
 * was not in it — which is the "some bars at the homepage are missing" report:
 * on every other page the strip sits under the bar, and on / the hero took its
 * place. Classifieds appeared nowhere on the homepage for the same reason.
 *
 * Included by application/views/header.php and application/views/index.php.
 * Keep it data-driven: anything hard-coded here has to be kept in step twice.
 */
	/*
	 * The Sports and Health links used to be hard-coded to
	 * `all_listing/sports-clubs` and `all_listing/health-services`. Categories
	 * are operator-editable, so neither slug is guaranteed to exist — and a
	 * slug that matches nothing lands the visitor on an empty directory page.
	 * Both are resolved by name now, against categories that actually hold
	 * published listings, and an item that resolves to nothing is left out
	 * rather than pointing at an empty page.
	 *
	 * One query, cached for the request — see findCategoryByKeywords().
	 */
	$__nav_ci = get_instance();
	$__nav_cat_url = function ($cat) {
		$slug = !empty($cat->category_slug) ? (string) $cat->category_slug : pearland_slug($cat->category_name);
		return base_url('all_listing/' . rawurlencode($slug));
	};
	/*
	 * Each slot is an operator setting at /admin/setting:
	 *   ''   auto — resolve by name (below)
	 *   '-'  hide the item entirely
	 *   slug pin the slot to that category
	 *
	 * Auto is what put "Sports" on the Travel and Recreation page: there was no
	 * sports category holding published listings, so it fell through to the
	 * second keyword, "recreation", which is a travel word here. That keyword is
	 * gone, and a slot that resolves to nothing is simply left out — but the
	 * setting is the real answer, because which categories belong in the menu is
	 * an editorial choice, not something to guess from names.
	 */
	$__nav_pick = function ($setting_key, array $keywords) use ($__nav_ci) {
		$choice = trim((string) site_setting($setting_key, ''));
		if ($choice === '-') {
			return null;                       // explicitly hidden
		}
		if ($choice !== '' && isset($__nav_ci->usermodel) && method_exists($__nav_ci->usermodel, 'getCategoryBySlug')) {
			$pinned = $__nav_ci->usermodel->getCategoryBySlug($choice);
			if ($pinned) return $pinned;
		}
		if (isset($__nav_ci->usermodel) && method_exists($__nav_ci->usermodel, 'findCategoryByKeywords')) {
			return $__nav_ci->usermodel->findCategoryByKeywords($keywords, true);
		}
		return null;
	};

	// "healthcare" first so it wins over "health services" / "mental health".
	//
	// "fitness" is deliberately NOT a sports keyword: the only category holding
	// it is "Health, Wellness & Fitness", so Auto resolved BOTH slots to health
	// categories and the strip read "Health, Wellness & Fitness | Healthcare"
	// where the client expected "Sports | Health". A slot that resolves to
	// nothing is left out, which is a clearer prompt to pin one at
	// /admin/setting than a link to the wrong section.
	$__nav_sports = $__nav_pick('nav_category_1', array('sport', 'athletic'));
	$__nav_health = $__nav_pick('nav_category_2', array('healthcare', 'health', 'medical'));

	// Never show the same category twice, whether by keyword or by pinning.
	if ($__nav_sports && $__nav_health
		&& (int) $__nav_sports->category_id === (int) $__nav_health->category_id) {
		$__nav_health = null;
	}
	?>
	<section class="news-top-menu">

		<div class="container">

			<div class="row">

				<div class="news-menu">

					<ul>

						<li><a href="<?= base_url() ?>" class="act">Home</a></li>

						<li><a href="<?= base_url() ?>all-listing" class="">Directory</a></li>

<?php if (site_setting('service_events_enabled','1') !== '0'): ?><li><a href="<?= base_url() ?>event" class="">Events</a></li><?php endif; ?>

<?php if (site_setting('service_news_enabled','1') !== '0'): ?><li><a href="<?= base_url() ?>news" class="">News</a></li><?php endif; ?>

<?php if (site_setting('service_videos_enabled','1') !== '0'): ?><li><a href="<?= base_url() ?>videos" class="">Videos</a></li><?php endif; ?>

<?php if (site_setting('service_classifieds_enabled','1') !== '0'): ?><li><a href="<?= base_url() ?>classifieds" class="">Classifieds</a></li><?php endif; ?>

						<?php // Label follows the category, so pinning a different one in
						      // /admin/setting does not leave the old word in the menu. ?>
						<?php if ($__nav_sports): ?><li><a href="<?= e_url($__nav_cat_url($__nav_sports)) ?>" class=""><?= e($__nav_sports->category_name) ?></a></li><?php endif; ?>

						<?php if ($__nav_health): ?><li><a href="<?= e_url($__nav_cat_url($__nav_health)) ?>" class=""><?= e($__nav_health->category_name) ?></a></li><?php endif; ?>

						<li><a href="<?= base_url() ?>all-category" class="">All categories</a></li>

					</ul>

				</div>

			</div>

		</div>

	</section>
	<?php unset($__nav_ci, $__nav_cat_url, $__nav_health, $__nav_sports); ?>
