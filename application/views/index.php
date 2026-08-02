<!doctype html>
<html lang="en">
<?php
/**
 * ============================================================================
 * PEARLAND411 — HOMEPAGE
 * ----------------------------------------------------------------------------
 * Self-contained: this view carries its own header and footer chrome rather
 * than going through views/header.php. Data comes from User::index(); every
 * block hides itself or says something useful when its query returns nothing.
 *
 * Section order follows the 2026-08 brief:
 *   header · hero · categories · featured · deals · events · explore ·
 *   news + videos · (weather in the sidebar) · grow your business ·
 *   email signup · footer
 *
 * All styling is in assets/css/home-sections.css — the 2,200-line inline
 * <style> block this file used to open with is gone, along with the pear-green
 * gradients that made the site read as a landscaping company.
 *
 * Required in $data: featured_listings, latest_listings, home_categories,
 * home_deals, home_events, latest_news, latest_videos, home_hotspot,
 * home_week, header_categories, footer_categories, popular_searches,
 * show_news / show_videos / show_events.
 * ==========================================================================*/

/**
 * Category URL. The stored slug is used verbatim when there is one — that is
 * the string the directory matches on — and only a missing slug is generated,
 * via the shared pearland_slug() so every link on the site derives the same
 * URL from a given name. The category lists on this page each used to carry
 * their own copy of this rule and they disagreed on names containing "&":
 * "Medical & Dental" became medical-and-dental here and medical-dental there,
 * so one of the two links always landed on an empty page.
 */
$home_cat_url = function ($category) {
	$slug = !empty($category->category_slug)
		? (string) $category->category_slug
		: pearland_slug($category->category_name);
	return base_url('all_listing/' . rawurlencode($slug));
};

/** Trim a description without splitting a word or a multibyte character. */
$p411_excerpt = function ($text, $len = 105) {
	$text = trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode((string) $text, ENT_QUOTES, 'UTF-8'))));
	if ($text === '') return '';
	return function_exists('mb_strimwidth')
		? mb_strimwidth($text, 0, $len, '…')
		: (strlen($text) > $len ? substr($text, 0, $len - 1) . '…' : $text);
};

/** Icons, not emoji — inline SVG paths keyed by a word in the category name. */
$p411_icons = array(
	'home'         => '<path d="M3 11.5 12 4l9 7.5"/><path d="M5.5 10.5V20h13v-9.5"/><path d="M10 20v-5h4v5"/>',
	'health'       => '<path d="M20.5 8.6a4.6 4.6 0 0 0-8.5-2.4 4.6 4.6 0 0 0-8.5 2.4c0 4.7 8.5 10 8.5 10s8.5-5.3 8.5-10z"/>',
	'fitness'      => '<path d="M3.9 12h3.4l1.4-2.6 2.1 4.6 1.4-2h7.9"/>',
	'medical'      => '<rect x="3.5" y="6" width="17" height="14" rx="2.5"/><path d="M9 6V4.5A1.5 1.5 0 0 1 10.5 3h3A1.5 1.5 0 0 1 15 4.5V6"/><path d="M12 10v6M9 13h6"/>',
	'dental'       => '<rect x="3.5" y="6" width="17" height="14" rx="2.5"/><path d="M9 6V4.5A1.5 1.5 0 0 1 10.5 3h3A1.5 1.5 0 0 1 15 4.5V6"/><path d="M12 10v6M9 13h6"/>',
	'auto'         => '<path d="M4 16.5h16"/><path d="M5.5 16.5V12l2-4.5h9l2 4.5v4.5"/><path d="M5.5 12h13"/><circle cx="8" cy="17.5" r="1.7"/><circle cx="16" cy="17.5" r="1.7"/>',
	'real'         => '<path d="M3.5 20.5V8.5l6-4 6 4v12"/><path d="M15.5 12.5h5v8"/><path d="M6.5 11h2M6.5 14.5h2M6.5 18h2"/>',
	'food'         => '<path d="M7 3.5v7a2.5 2.5 0 0 0 5 0v-7"/><path d="M9.5 13v7.5"/><path d="M17.5 3.5c-1.3 1.8-2 3.6-2 5.4s.7 2.8 2 2.8v8.8"/>',
	'restaurant'   => '<path d="M7 3.5v7a2.5 2.5 0 0 0 5 0v-7"/><path d="M9.5 13v7.5"/><path d="M17.5 3.5c-1.3 1.8-2 3.6-2 5.4s.7 2.8 2 2.8v8.8"/>',
	'beauty'       => '<circle cx="6.5" cy="17.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/><path d="m8.4 15.7 8.2-11.2M15.6 15.7 7.4 4.5"/>',
	'professional' => '<rect x="2.5" y="7" width="19" height="12.5" rx="2"/><path d="M8.5 7V5.5A1.5 1.5 0 0 1 10 4h4a1.5 1.5 0 0 1 1.5 1.5V7"/><path d="M2.5 12.5h19"/>',
	'business'     => '<rect x="2.5" y="7" width="19" height="12.5" rx="2"/><path d="M8.5 7V5.5A1.5 1.5 0 0 1 10 4h4a1.5 1.5 0 0 1 1.5 1.5V7"/><path d="M2.5 12.5h19"/>',
	'financial'    => '<path d="M12 3.5v17"/><path d="M16.5 7H10a2.75 2.75 0 0 0 0 5.5h4a2.75 2.75 0 0 1 0 5.5H7"/>',
	'insurance'    => '<path d="M12 3l8 3v6c0 5-3.5 8.5-8 9-4.5-.5-8-4-8-9V6z"/><path d="m9 12 2 2 4-4"/>',
	'legal'        => '<path d="M12 3.5v17M6.5 20.5h11"/><path d="M4 7.5h16"/><path d="m7.5 7.5-3 6h6zM16.5 7.5l-3 6h6z"/>',
	'attorney'     => '<path d="M12 3.5v17M6.5 20.5h11"/><path d="M4 7.5h16"/><path d="m7.5 7.5-3 6h6zM16.5 7.5l-3 6h6z"/>',
	'education'    => '<path d="m2.8 9 9.2-4.6L21.2 9 12 13.6z"/><path d="M6.6 11v4.6c0 1.5 2.4 2.9 5.4 2.9s5.4-1.4 5.4-2.9V11"/>',
	'pet'          => '<circle cx="8" cy="6.5" r="1.9"/><circle cx="16" cy="6.5" r="1.9"/><circle cx="4.6" cy="12" r="1.7"/><circle cx="19.4" cy="12" r="1.7"/><path d="M12 11.5c-3 0-5.2 2.6-5.2 5.1 0 1.7 1.3 3 3 3h4.4c1.7 0 3-1.3 3-3 0-2.5-2.2-5.1-5.2-5.1z"/>',
	'shop'         => '<path d="M5 8h14l-1.2 11.5H6.2z"/><path d="M8.7 8V6.2a3.3 3.3 0 0 1 6.6 0V8"/>',
	'retail'       => '<path d="M5 8h14l-1.2 11.5H6.2z"/><path d="M8.7 8V6.2a3.3 3.3 0 0 1 6.6 0V8"/>',
	'construct'    => '<path d="M4 20.5h16"/><path d="M6.5 20.5v-6h11v6"/><path d="M4.5 14.5 12 8l7.5 6.5"/>',
	'clean'        => '<path d="M8.5 20.5h7l-1-8h-5z"/><path d="M12 12.5V4M9.5 6.5h5"/>',
	'tech'         => '<rect x="2.5" y="4.5" width="19" height="12.5" rx="2"/><path d="M9 20.5h6M12 17v3.5"/>',
	'_default'     => '<path d="M4 9.5 5.5 5h13L20 9.5"/><path d="M4 9.5a2.4 2.4 0 0 0 4 1.6 2.4 2.4 0 0 0 4 0 2.4 2.4 0 0 0 4 0 2.4 2.4 0 0 0 4-1.6"/><path d="M5.5 12.4V20h13v-7.6"/><path d="M10 20v-4.5h4V20"/>',
);
$p411_icon = function ($name) use ($p411_icons) {
	$n = strtolower((string) $name);
	foreach ($p411_icons as $k => $path) {
		if ($k !== '_default' && strpos($n, $k) !== false) return $path;
	}
	return $p411_icons['_default'];
};

/**
 * One business card.
 *
 * Featured and Explore render the same component so they cannot drift apart,
 * and so both are the same height: the media box has a fixed ratio and the
 * footer is pushed down by the flex column in the stylesheet, which is what
 * stops a listing with no description producing a card half the size of its
 * neighbour.
 *
 * $opts: compact (bool) — the six-up Explore grid
 *        featured (bool) — burgundy rule + a contact button
 */
$p411_card = function ($row, $opts = array()) use ($p411_excerpt) {
	$compact  = !empty($opts['compact']);
	$featured = !empty($opts['featured']);

	$name  = (string) ($row->listing_name ?? '');
	$url   = base_url('listing-details/' . rawurlencode((string) ($row->listing_slug ?? '')));
	$cat   = (string) ($row->category_name ?? '');
	$desc  = $p411_excerpt($row->listing_description ?? '', $compact ? 76 : 110);
	$rate  = (float) ($row->average_rating ?? 0);
	$votes = (int)   ($row->total_reviews  ?? 0);
	$phone = ((int) ($row->display_phone ?? 0) === 1 && !empty($row->listing_mobile))
		? (string) $row->listing_mobile : '';
	// City / service area. The owner's address when they have chosen to show
	// it, otherwise the area this directory covers.
	$area  = ((int) ($row->display_address ?? 0) === 1 && !empty($row->listing_address))
		? (string) $row->listing_address : 'Pearland, TX';
	$is_new = !empty($row->created_at) && strtotime((string) $row->created_at) > strtotime('-30 days');
	// A listing whose user_id is set has been claimed by its owner and the
	// claim approved by an admin — that is what "verified" means here.
	$is_claimed = (int) ($row->user_id ?? 0) > 0;
	?>
	<article class="p411-card<?= $compact ? ' p411-card--compact' : '' ?><?= $featured ? ' p411-card--featured' : '' ?>">
		<a class="p411-card__media" href="<?= e_url($url) ?>" tabindex="-1" aria-hidden="true">
			<?php if (!empty($row->listing_image)): ?>
				<?= responsive_img($row->listing_image, $name) ?>
			<?php else: ?>
				<img src="<?= e_attr(category_placeholder($cat)) ?>" alt="" loading="lazy" decoding="async">
			<?php endif; ?>
			<?php if ($cat !== ''): ?><span class="p411-card__cat"><?= e($cat) ?></span><?php endif; ?>
			<?php if ($featured): ?>
				<span class="p411-card__flag">Featured</span>
			<?php elseif ($is_new): ?>
				<span class="p411-card__flag p411-card__flag--new">New</span>
			<?php elseif ($is_claimed): ?>
				<span class="p411-card__flag p411-card__flag--verified">Verified</span>
			<?php endif; ?>
		</a>

		<div class="p411-card__body">
			<h3><a href="<?= e_url($url) ?>"><?= e($name) ?></a></h3>

			<?php if ($rate > 0 && $votes > 0): ?>
				<div class="p411-card__rate">
					<span class="p411-card__stars" aria-hidden="true"><?php
						$full = (int) round($rate);
						echo str_repeat('★', max(0, min(5, $full))) . str_repeat('☆', max(0, 5 - $full));
					?></span>
					<span><?= number_format($rate, 1) ?> (<?= $votes ?>)</span>
				</div>
			<?php endif; ?>

			<?php if ($desc !== ''): ?><p class="p411-card__desc"><?= e($desc) ?></p><?php endif; ?>

			<div class="p411-card__meta">
				<span>
					<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10.5c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10.5" r="2.8"/></svg>
					<?= e($area) ?>
				</span>
			</div>

			<div class="p411-card__foot">
				<a class="p411-btn p411-btn--sm" href="<?= e_url($url) ?>">View Business</a>
				<?php if ($featured && $phone !== ''): ?>
					<a class="p411-btn p411-btn--sm p411-btn--ghost" href="tel:<?= e_attr(preg_replace('/[^0-9+]/', '', $phone)) ?>">Call <?= e($phone) ?></a>
				<?php endif; ?>
			</div>
		</div>
	</article>
	<?php
};

// Guests see sign-in / register; owners see their dashboard.
$is_logged_in = !empty($_SESSION['logged_user']);
$user_name    = ($is_logged_in && !empty($_SESSION['logged_user']['name']))
	? $_SESSION['logged_user']['name'] : 'User';

// The light logo used on the navy header and footer. Set `site_logo_url` in
// /admin/setting to serve it from assets/images/ instead of the third-party
// host the site has always pointed at.
$site_logo = site_setting('site_logo_url', '') ?: 'https://i.ibb.co/93BYZ4DZ/pp.png';

/**
 * Hero backdrop.
 *
 * Drop a photograph at assets/images/home/hero-pearland.<ext> and it is used;
 * with no file there the hand-drawn illustration in the stylesheet shows
 * instead. Checked in PHP rather than layered in CSS on purpose — a missing
 * background layer would 404 on every single page view.
 *
 * The photograph the client supplied is Pearland Town Center at dusk with the
 * water tower; anything wide and dark works, because the type sits on an
 * overlay rather than on the image.
 */
$hero_photo = '';
foreach (array('jpg', 'jpeg', 'webp', 'png', 'avif') as $__ext) {
	$__rel = 'assets/images/home/hero-pearland.' . $__ext;
	if (is_file(FCPATH . $__rel)) { $hero_photo = base_url($__rel); break; }
}
unset($__ext, $__rel);
?>

<head>
  <!--== META TAGS ==-->
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <?php include APPPATH . 'views/partials/seo_head.php'; ?>

  <!--== FAV ICON ==-->
  <link rel="shortcut icon" href="<?= base_url() ?>assets/images/fav.ico" type="image/x-icon">
  <!--== GOOGLE FONTS ==-->
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
  <!--== WEB ICON FONTS ==-->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

  <!-- Theme base. Shared by ~200 views; never edited. -->
  <link rel="stylesheet" href="<?= base_url() ?>assets/css/style.css">
  <!-- Brand palette + typography overrides — loaded after the theme so it wins. -->
  <link rel="stylesheet" href="<?= base_url() ?>assets/css/pearland-brand.css?v=1">
  <!-- The homepage. Every selector is scoped to .p411-home and no other view
       loads this file, so it cannot affect the rest of the site. -->
  <link rel="stylesheet" href="<?= base_url() ?>assets/css/home-sections.css?v=3">
  <!-- The two house promo cards (partials/service_promo.php and
       partials/banner_design_promo.php), shared with the directory and
       listing-details pages so the cards can't drift. -->
  <link rel="stylesheet" href="<?= base_url() ?>assets/css/promo-cards.css?v=2">
  <!-- The footer (partials/site_footer.php), shared with every other page. -->
  <link rel="stylesheet" href="<?= base_url() ?>assets/css/site-footer.css?v=1">

  <?php // Both of these were loaded from a CDN. The repository already ships
        // the same versions (Bootstrap 4.3.1, jQuery UI 1.12.1), so serving
        // them locally drops two render-blocking third-party requests from
        // the site's most-visited page. ?>
  <link rel="stylesheet" href="<?= base_url() ?>assets/css/jquery-ui.css">
  <link rel="stylesheet" href="<?= base_url() ?>assets/css/bootstrap.css">
</head>

<body class="p411-home">

  <div class="full_wrapper">

    <!-- ==================================================== HEADER == -->
    <?php /* The theme's own header DOM (.hom-top / .hom-nav / .pop-menu /
             .mob-me-all) is kept as-is so assets/js/custom.js keeps driving
             the mega menu and the off-canvas panel. Only the paint changed. */ ?>
    <section class="fhns">
      <div class="str ind2-home">
        <div class="hom-head">
          <div class="hom-top">
            <div class="container px-10">
              <div class="row">
                <div class="hom-nav">

                  <a href="<?= base_url() ?>" class="top-log">
                    <img src="<?= e_attr($site_logo) ?>" alt="Pearland411 — local business directory" class="ic-logo">
                  </a>

                  <?php // The chevron is markup, not a pseudo-element: the
                        // theme's own `.menu::after` arrow is pinned to
                        // `right:-16px`, which in this flex header lands on top
                        // of the word. That rule is switched off in the CSS. ?>
                  <div class="menu" role="button" tabindex="0" aria-haspopup="true">
                    <h4>Explore
                      <svg width="11" height="7" viewBox="0 0 12 8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m1 1.5 5 5 5-5"/></svg>
                    </h4>
                  </div>

                  <!-- MEGA MENU -->
                  <div class="pop-menu">
                    <div class="container">
                      <div class="row">
                        <i class="material-icons clopme">close</i>
                        <div class="pmenu-spri">
                          <ul>
                            <li><a href="<?= base_url('all-category') ?>" class="act"><img src="<?= base_url() ?>assets/images/icon/shop.png" loading="lazy" alt="">All Services</a></li>
                            <?php if (!empty($show_news)): ?>
                              <li><a href="<?= base_url('news') ?>"><img src="<?= base_url() ?>assets/images/icon/news.png" loading="lazy" alt="">Local News</a></li>
                            <?php endif; ?>
                            <?php if (!empty($show_events)): ?>
                              <li><a href="<?= base_url('event') ?>"><img src="<?= base_url() ?>assets/images/icon/calendar.png" loading="lazy" alt="">Events</a></li>
                            <?php endif; ?>
                            <li><a href="<?= base_url('coupons') ?>"><img src="<?= base_url() ?>assets/images/icon/cart.png" loading="lazy" alt="">Deals &amp; Coupons</a></li>
                            <?php if (!empty($show_videos)): ?>
                              <li><a href="<?= base_url('videos') ?>"><img src="<?= base_url() ?>assets/images/icon/blog1.png" loading="lazy" alt="">Videos</a></li>
                            <?php endif; ?>
                          </ul>
                        </div>
                        <div class="pmenu-cat">
                          <h4>All Categories</h4>
                          <ul id="pg-resu">
                            <?php if (!empty($header_categories)): ?>
                              <?php foreach ($header_categories as $category): ?>
                                <li><a href="<?= e_url($home_cat_url($category)) ?>"><?= e($category->category_name) ?></a></li>
                              <?php endforeach; ?>
                            <?php else: ?>
                              <li><a href="<?= base_url('all_listing') ?>">Browse all businesses</a></li>
                            <?php endif; ?>
                          </ul>
                          <div style="text-align:right;margin-top:20px;padding-top:15px;">
                            <a href="<?= base_url('all-category') ?>" class="p411-btn p411-btn--sm">Explore all categories</a>
                          </div>
                        </div>
                        <div class="dir-home-nav-bot">
                          <ul>
                            <li>A few reasons you&rsquo;ll love the Pearland411 directory
                              <span>Call us on: <?= e(site_setting('contact_phone', '281-482-2031')) ?></span>
                            </li>
                            <li><a href="<?= base_url('post-your-ads') ?>" class="waves-effect waves-light btn-large"><i class="material-icons">font_download</i> Advertise with us</a></li>
                            <li><a href="<?= base_url('pricing-plan') ?>" class="waves-effect waves-light btn-large"><i class="material-icons">store</i> Add your business</a></li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- END MEGA MENU -->

                  <?php /* Hidden on the homepage — the hero carries the search
                          box. It stays in the DOM because the bottom quick-bar's
                          "Search" button toggles .top-ser-act on it. */ ?>
                  <div class="top-ser">
                    <form name="filter_form" id="filter_form" class="filter_form" method="GET" action="<?= base_url('search') ?>">
                      <ul>
                        <li class="sr-sea">
                          <label class="sr-only" for="top-select-search">Search Pearland411</label>
                          <input type="text" name="q" autocomplete="off" id="top-select-search" placeholder="What are you looking for?">
                          <ul id="tser-res1" class="tser-res tser-res2"></ul>
                        </li>
                        <li class="sbtn">
                          <button type="submit" class="btn btn-success" id="top_filter_submit"><i class="material-icons">search</i></button>
                        </li>
                      </ul>
                    </form>
                  </div>

                  <?php if (!$is_logged_in): ?>
                    <ul class="bl">
                      <li><a href="<?= base_url('pricing-plan') ?>">Add Business</a></li>
                      <li><a href="<?= base_url('login') ?>">Sign In</a></li>
                      <li><a href="<?= base_url('login?login=register') ?>">Create an Account</a></li>
                    </ul>
                  <?php else: ?>
                    <ul class="bl">
                      <li><a href="<?= base_url('pricing-details') ?>">Add Business</a></li>
                      <li><a href="<?= base_url('dashboard') ?>">My Dashboard</a></li>
                    </ul>
                  <?php endif; ?>

                  <!-- MOBILE MENU -->
                  <div class="mob-menu">
                    <div class="mob-me-ic"><i class="material-icons">menu</i></div>
                    <div class="mob-me-all">
                      <div class="mob-me-clo"><i class="material-icons">close</i></div>
                      <div class="mv-bus">
                        <h4></h4>
                        <ul>
                          <li><a href="<?= base_url('pricing-plan') ?>">Add Business</a></li>
                          <?php if (!$is_logged_in): ?>
                            <li><a href="<?= base_url('login') ?>">Sign In</a></li>
                            <li><a href="<?= base_url('login?login=register') ?>">Create an Account</a></li>
                          <?php else: ?>
                            <li><a href="<?= base_url('dashboard') ?>">My Dashboard</a></li>
                            <li><span style="color:#666;font-size:14px;padding:8px 16px;">Welcome, <?= e($user_name) ?></span></li>
                          <?php endif; ?>
                        </ul>
                      </div>
                      <div class="mv-cate">
                        <h4>All Categories</h4>
                        <ul>
                          <?php foreach (array_slice((array) $header_categories, 0, 12) as $category): ?>
                            <li><a href="<?= e_url($home_cat_url($category)) ?>"><?= e($category->category_name) ?></a></li>
                          <?php endforeach; ?>
                          <li><a href="<?= base_url('all-category') ?>">View all categories</a></li>
                        </ul>
                      </div>
                    </div>
                  </div>
                  <!-- END MOBILE MENU -->

                </div>
              </div>
            </div>
          </div>

          <!-- ================================================== HERO == -->
          <section class="p411-hero<?= $hero_photo !== '' ? ' p411-hero--photo' : '' ?>">
            <div class="p411-hero__art" aria-hidden="true"
                 <?= $hero_photo !== '' ? 'style="background-image:url(' . e_attr($hero_photo) . ')"' : '' ?>></div>
            <div class="container px-10">
              <div class="p411-hero__inner">
                <h1>Discover Pearland&rsquo;s<br>Best Local Businesses</h1>
                <p>Find trusted local services, restaurants, healthcare providers, shops, events, deals and community news throughout Pearland.</p>

                <form method="GET" action="<?= base_url('search') ?>" class="p411-hero__search">
                  <div class="p411-hero__field">
                    <label class="sr-only" for="heroSearch">Search for businesses, services or categories</label>
                    <input type="text" id="heroSearch" name="q" placeholder="Search for businesses, services, or categories..." required>
                    <button type="submit" aria-label="Search">
                      <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    </button>
                  </div>
                </form>

                <?php if (!empty($popular_searches)): ?>
                  <p class="p411-hero__popular">
                    <b>Popular:</b>
                    <?php foreach ($popular_searches as $term): ?>
                      <a href="<?= e_url(base_url('search') . '?q=' . rawurlencode($term)) ?>"><?= e($term) ?></a>
                    <?php endforeach; ?>
                    <a href="<?= base_url('services') ?>">Website Design</a>
                    <a href="<?= base_url('services') ?>">Marketing Services</a>
                  </p>
                <?php endif; ?>

                <div class="p411-hero__cta">
                  <a class="p411-btn p411-btn--lg" href="<?= base_url('all_listing') ?>">Explore Local Businesses</a>
                  <a class="p411-btn p411-btn--lg p411-btn--bu" href="<?= base_url('register') ?>">List Your Business Free</a>
                </div>
              </div>
            </div>
          </section>

        </div>
      </div>
    </section>

    <!-- ======================================== CATEGORY STRIP == -->
    <?php if (!empty($home_categories)): ?>
      <?php // Deliberately NOT .category-tabs — the theme hides that class on
            // mobile with !important (it was written for the old fake topic
            // tabs) and this strip is a responsive grid that earns its place. ?>
      <section class="p411-catstrip" aria-label="Browse by category">
        <div class="container px-10">
          <div class="p411-cats">
            <?php
            // Three accents in rotation, so eight tiles don't read as one
            // green block. Burgundy lands on two of every eight.
            $accents = array('green', 'bu', 'green', 'navy', 'green', 'bu', 'navy', 'green');
            foreach (array_slice($home_categories, 0, 8) as $i => $cat): ?>
              <a class="p411-cat p411-cat--<?= $accents[$i % count($accents)] ?>" href="<?= e_url($home_cat_url($cat)) ?>">
                <i><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $p411_icon($cat->category_name) ?></svg></i>
                <b><?= e($cat->category_name) ?></b>
                <?php if (isset($cat->listing_count)): ?>
                  <span><?= number_format((int) $cat->listing_count) ?> listing<?= ((int) $cat->listing_count === 1 ? '' : 's') ?></span>
                <?php endif; ?>
              </a>
            <?php endforeach; ?>
          </div>
          <div class="p411-catstrip__more">
            <a class="p411-more" href="<?= base_url('all-category') ?>">See all categories &rarr;</a>
          </div>
        </div>
      </section>
    <?php endif; ?>

    <!-- ================================== TOP BANNER (sold) == -->
    <?php // Part of the site-wide package, so the zone stays bookable — but
          // unsold it renders nothing rather than opening the page with a
          // "your ad here" box. ?>
    <div class="container px-10">
      <div class="p411-adrow p411-adrow--top">
        <?php $slot = 'top-banner-site-wide';
        $slot_fallback = false;
        include APPPATH . 'views/partials/ad_slot.php'; ?>
      </div>
    </div>

    <!-- ============================================ MAIN CONTENT == -->
    <section class="p411-main">
      <div class="container px-10">
        <div class="p411-shell">

          <!-- ==================================== MAIN COLUMN == -->
          <div class="p411-col">

            <?php // Business Hotspot — a slim logo rail, deliberately not
                  // another card row. Sold as plans.slug = business-hotspot. ?>
            <?php if (!empty($home_hotspot)): ?>
              <section class="p411-sec">
                <div class="p411-hotspot">
                  <div class="p411-hotspot__head">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 2s5 4.5 5 9a5 5 0 0 1-10 0c0-4.5 5-9 5-9z"/><path d="M12 22a7 7 0 0 0 7-7"/></svg>
                    <h2>Business Hotspot</h2>
                    <a class="p411-more" href="<?= base_url('all_listing') ?>">All businesses &rarr;</a>
                  </div>
                  <div class="p411-hotspot__rail">
                    <?php foreach ($home_hotspot as $spot): ?>
                      <a class="p411-hotspot__item" href="<?= e_url(base_url('listing-details/' . rawurlencode($spot->listing_slug))) ?>">
                        <?php // This rail shows logos at 46px tall. The artwork
                              // placeholder is a 640x480 tile with a caption —
                              // shrunk to a logo slot it is unreadable, so an
                              // icon stands in. ?>
                        <?php if (!empty($spot->listing_image)): ?>
                          <img src="<?= e_attr(base_url($spot->listing_image)) ?>" alt="<?= e_attr($spot->listing_name) ?>" loading="lazy" decoding="async">
                        <?php else: ?>
                          <span class="p411-hotspot__mark">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $p411_icon($spot->category_name ?? '') ?></svg>
                          </span>
                        <?php endif; ?>
                        <b><?= e($spot->listing_name) ?></b>
                        <?php if (!empty($spot->category_name)): ?><span><?= e($spot->category_name) ?></span><?php endif; ?>
                      </a>
                    <?php endforeach; ?>
                  </div>
                </div>
              </section>
            <?php endif; ?>

            <?php // Business of the Week — one wide spotlight. Being the only
                  // one is what the customer is paying for. ?>
            <?php if (!empty($home_week)): ?>
              <section class="p411-sec">
                <div class="p411-week">
                  <div class="p411-week__media">
                    <img src="<?= !empty($home_week->listing_image) ? base_url($home_week->listing_image) : e_attr(category_placeholder($home_week->category_name ?? '')) ?>"
                         alt="<?= e_attr($home_week->listing_name) ?>" loading="lazy" decoding="async">
                    <span class="p411-week__flag">Business of the Week</span>
                  </div>
                  <div class="p411-week__body">
                    <?php if (!empty($home_week->category_name)): ?>
                      <span class="p411-week__cat"><?= e($home_week->category_name) ?></span>
                    <?php endif; ?>
                    <h3><?= e($home_week->listing_name) ?></h3>
                    <?php if (!empty($home_week->listing_description)): ?>
                      <p><?= e($p411_excerpt($home_week->listing_description, 220)) ?></p>
                    <?php endif; ?>
                    <div class="p411-week__foot">
                      <a class="p411-btn" href="<?= e_url(base_url('listing-details/' . rawurlencode($home_week->listing_slug))) ?>">View Business</a>
                      <?php if (!empty($home_week->week_until) && strtotime($home_week->week_until)): ?>
                        <span class="p411-week__until">Featured until <?= e(date('M j', strtotime($home_week->week_until))) ?></span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </section>
            <?php endif; ?>

            <!-- ============================ FEATURED BUSINESSES == -->
            <section class="p411-sec">
              <div class="p411-sec__head">
                <h2>Featured Businesses</h2>
                <a class="p411-more" href="<?= base_url('all_listing') . '?sort=featured' ?>">View all featured &rarr;</a>
              </div>

              <?php if (!empty($featured_listings)): ?>
                <div class="p411-grid">
                  <?php foreach ($featured_listings as $item): ?>
                    <?php $p411_card($item, array('featured' => true)); ?>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <?php // Deliberately not padded out with whatever listings
                      // exist: a Featured row of blank cards is what makes the
                      // paid placement look worthless. ?>
                <div class="p411-empty p411-empty--bu">
                  <h3>Featured placement is available</h3>
                  <p>The top of the homepage, the top of the directory and the top of a category page &mdash; from $20 a month.</p>
                  <a class="p411-btn p411-btn--bu" href="<?= base_url('pricing-plan') ?>">See featured packages</a>
                </div>
              <?php endif; ?>
            </section>

            <!-- ================================ DEALS + EVENTS == -->
            <section class="p411-sec">
              <div class="p411-split">

                <div>
                  <h3 class="p411-subhead">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#6D263D" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M20 12V7H4v5a2 2 0 0 1 0 4v1h16v-1a2 2 0 0 1 0-4z"/><path d="M12 7v10"/></svg>
                    Deals &amp; Coupons
                    <a class="p411-more" href="<?= base_url('coupons') ?>">All deals &rarr;</a>
                  </h3>

                  <?php if (!empty($home_deals)): ?>
                    <div class="p411-stack">
                      <?php foreach (array_slice($home_deals, 0, 3) as $deal): ?>
                        <?php
                        $deal_url = base_url('coupons/' . (int) $deal->coupon_id);
                        $deal_biz = !empty($deal->listing_name) ? $deal->listing_name
                                  : (!empty($deal->owner_name) ? $deal->owner_name : 'Pearland411');
                        $deal_end = (!empty($deal->coupon_end_date) && strtotime($deal->coupon_end_date))
                                  ? strtotime($deal->coupon_end_date) : false;
                        ?>
                        <article class="p411-deal">
                          <span class="p411-deal__logo">
                            <?php if (!empty($deal->coupon_photo)): ?>
                              <img src="<?= e_attr(base_url($deal->coupon_photo)) ?>" alt="" loading="lazy" decoding="async">
                            <?php else: ?>
                              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#8B93A1" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><path d="M20 12V7H4v5a2 2 0 0 1 0 4v1h16v-1a2 2 0 0 1 0-4z"/><path d="M12 7v10"/></svg>
                            <?php endif; ?>
                          </span>
                          <span class="p411-deal__body">
                            <?php if (!empty($deal->coupon_code)): ?>
                              <span class="p411-deal__off"><?= e($deal->coupon_code) ?></span>
                            <?php endif; ?>
                            <span class="p411-deal__name"><a href="<?= e_url($deal_url) ?>"><?= e($deal->coupon_name) ?></a></span>
                            <span class="p411-deal__meta">
                              <span><?= e($deal_biz) ?></span>
                              <?php if (!empty($deal->category_name)): ?>
                                <span><?= e($deal->category_name) ?></span>
                              <?php endif; ?>
                              <?php if ($deal_end): ?>
                                <span class="p411-deal__exp">Ends <?= e(date('M j', $deal_end)) ?></span>
                              <?php endif; ?>
                            </span>
                            <span class="p411-deal__cta">
                              <a class="p411-btn p411-btn--sm p411-btn--bu" href="<?= e_url($deal_url) ?>">Redeem Deal</a>
                            </span>
                          </span>
                        </article>
                      <?php endforeach; ?>
                    </div>
                    <div style="margin-top:14px;">
                      <a class="p411-btn p411-btn--ghost p411-btn--block p411-btn--sm" href="<?= base_url('coupons') ?>">View All Deals</a>
                    </div>
                  <?php else: ?>
                    <div class="p411-empty p411-empty--bu">
                      <h3>No live coupons right now</h3>
                      <p>Running an offer? Deals posted by Pearland businesses show here for as long as they last.</p>
                      <a class="p411-btn p411-btn--bu" href="<?= base_url('register') ?>">Post a deal</a>
                    </div>
                  <?php endif; ?>
                </div>

                <?php if (!empty($show_events)): ?>
                  <div>
                    <h3 class="p411-subhead">
                      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#6D263D" stroke-width="2" stroke-linecap="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 11h18"/></svg>
                      Upcoming Events
                      <a class="p411-more" href="<?= base_url('event') ?>">All events &rarr;</a>
                    </h3>

                    <?php if (!empty($home_events)): ?>
                      <div class="p411-stack">
                        <?php foreach (array_slice($home_events, 0, 3) as $event): ?>
                          <?php
                          $ets  = !empty($event->event_start_date) ? strtotime($event->event_start_date) : false;
                          $eurl = !empty($event->event_slug)
                            ? base_url('event-details/' . rawurlencode($event->event_slug))
                            : base_url('event');
                          // "Add to calendar" without asking anyone to install
                          // anything: Google's template URL, as an all-day
                          // entry, because event_time is free text.
                          $gcal = '';
                          if ($ets) {
                            $ends = (!empty($event->event_end_date) && strtotime($event->event_end_date))
                              ? strtotime($event->event_end_date) : $ets;
                            $gcal = 'https://calendar.google.com/calendar/render?' . http_build_query(array(
                              'action'   => 'TEMPLATE',
                              'text'     => (string) $event->event_name,
                              'dates'    => date('Ymd', $ets) . '/' . date('Ymd', strtotime('+1 day', $ends)),
                              'details'  => $eurl,
                              'location' => (string) ($event->event_address ?? 'Pearland, TX'),
                            ), '', '&amp;');
                          }
                          ?>
                          <article class="p411-event">
                            <?php if ($ets): ?>
                              <span class="p411-event__date">
                                <i><?= e(date('M', $ets)) ?></i>
                                <b><?= e(date('j', $ets)) ?></b>
                              </span>
                            <?php endif; ?>
                            <span class="p411-event__body">
                              <b><a href="<?= e_url($eurl) ?>"><?= e($event->event_name) ?></a></b>
                              <?php if ($ets): ?>
                                <span>
                                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                                  <?= e(date('D, M j', $ets)) ?><?= !empty($event->event_time) ? ' &middot; ' . e($event->event_time) : '' ?>
                                </span>
                              <?php endif; ?>
                              <?php if (!empty($event->event_address)): ?>
                                <span>
                                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10.5c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10.5" r="2.8"/></svg>
                                  <?= e($event->event_address) ?>
                                </span>
                              <?php endif; ?>
                              <?php if ($gcal !== ''): ?>
                                <a class="p411-mini" href="<?= $gcal ?>" target="_blank" rel="noopener nofollow">
                                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M12 11v6M9 14h6"/></svg>
                                  Add to Calendar
                                </a>
                              <?php endif; ?>
                            </span>
                          </article>
                        <?php endforeach; ?>
                      </div>
                      <div style="margin-top:14px;">
                        <a class="p411-btn p411-btn--ghost p411-btn--block p411-btn--sm" href="<?= base_url('event') ?>">View All Events</a>
                      </div>
                    <?php else: ?>
                      <?php // Not a big empty white box — the brief was
                            // specific about this one. ?>
                      <div class="p411-empty">
                        <h3>Have an upcoming Pearland event?</h3>
                        <p>Add it free and it will appear here, on the events calendar and in the weekly email.</p>
                        <a class="p411-btn" href="<?= base_url('register') ?>">Submit an Event</a>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>

              </div>
            </section>

            <!-- ======================== EXPLORE LOCAL BUSINESSES == -->
            <section class="p411-sec">
              <div class="p411-sec__head">
                <h2>Explore Local Businesses</h2>
                <a class="p411-more" href="<?= base_url('all_listing') ?>">View all businesses &rarr;</a>
              </div>

              <?php if (!empty($latest_listings)): ?>
                <div class="p411-grid">
                  <?php foreach ($latest_listings as $row): ?>
                    <?php $p411_card($row, array('compact' => true)); ?>
                  <?php endforeach; ?>
                </div>
                <div class="p411-seeall">
                  <a class="p411-btn p411-btn--lg p411-btn--navy" href="<?= base_url('all_listing') ?>">View All Pearland Businesses</a>
                </div>
              <?php else: ?>
                <div class="p411-empty">
                  <h3>No businesses listed yet</h3>
                  <p>Once businesses are added to the directory they&rsquo;ll appear here, the most complete listings first.</p>
                  <a class="p411-btn" href="<?= base_url('register') ?>">Add the first business</a>
                </div>
              <?php endif; ?>
            </section>

            <!-- =========================== MIDDLE BANNER (sold) == -->
            <div class="p411-adrow">
              <?php $slot = 'middle-banner-category';
              include APPPATH . 'views/partials/ad_slot.php'; ?>
            </div>

            <!-- =============================== NEWS + VIDEOS == -->
            <?php if ((!empty($show_news) && !empty($latest_news)) || (!empty($show_videos) && !empty($latest_videos))): ?>
              <section class="p411-sec">
                <div class="p411-sec__head">
                  <h2>Local News &amp; Community</h2>
                  <?php if (!empty($show_news)): ?>
                    <a class="p411-more" href="<?= base_url('news') ?>">Read more news &rarr;</a>
                  <?php endif; ?>
                </div>

                <?php if (!empty($show_news) && !empty($latest_news)): ?>
                  <?php
                  /** Story URL. news_slug is the canonical key; fall back to the id. */
                  $news_url = function ($story) {
                    $key = !empty($story->news_slug) ? (string) $story->news_slug : (string) ($story->news_id ?? '');
                    return base_url('news-details/' . rawurlencode($key));
                  };
                  $lead = $latest_news[0];
                  $rest = array_slice($latest_news, 1, 4);
                  ?>
                  <div class="p411-news">

                    <a class="p411-news__lead" href="<?= e_url($news_url($lead)) ?>">
                      <span class="p411-news__media">
                        <?php if (!empty($lead->news_image)): ?>
                          <?= responsive_img($lead->news_image, (string) $lead->news_title) ?>
                        <?php else: ?>
                          <img src="<?= e_attr(base_url('assets/images/placeholders/news.svg')) ?>" alt="" loading="lazy" decoding="async">
                        <?php endif; ?>
                      </span>
                      <?php // A <div> rather than a <span>: <a> may contain flow
                            // content, but a <span> may not contain a <p>. ?>
                      <div class="p411-news__text">
                        <span class="p411-tag p411-tag--bu">News</span>
                        <b><?= e($lead->news_title) ?></b>
                        <?php if (!empty($lead->news_description)): ?>
                          <p><?= e($p411_excerpt($lead->news_description, 190)) ?></p>
                        <?php endif; ?>
                        <span class="p411-news__date"><?php
                          echo !empty($lead->created_at) && strtotime($lead->created_at)
                            ? e(date('M j, Y', strtotime($lead->created_at)))
                            : e($lead->category_name ?? 'Pearland');
                        ?></span>
                      </div>
                    </a>

                    <div class="p411-news__rest">
                      <?php foreach ($rest as $story): ?>
                        <a class="p411-news__item" href="<?= e_url($news_url($story)) ?>">
                          <?php // A ~100px thumb is too small for the artwork
                                // placeholder — its caption renders as unreadable
                                // grey mush. An icon in a tinted box scales. ?>
                          <?php if (!empty($story->news_image)): ?>
                            <span class="p411-news__thumb">
                              <img src="<?= e_attr(base_url($story->news_image)) ?>" alt="" loading="lazy" decoding="async">
                            </span>
                          <?php else: ?>
                            <span class="p411-news__thumb p411-thumb--empty">
                              <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3.5 5.5h13v14h-13z"/><path d="M16.5 9.5h4v8a2 2 0 0 1-4 0"/><path d="M6.5 9h7M6.5 12.5h7M6.5 16h4"/></svg>
                            </span>
                          <?php endif; ?>
                          <span class="p411-news__body">
                            <span class="p411-tag p411-tag--green">Community</span>
                            <b><?= e($story->news_title) ?></b>
                            <span class="p411-news__date"><?php
                              echo !empty($story->created_at) && strtotime($story->created_at)
                                ? e(date('M j, Y', strtotime($story->created_at)))
                                : e($story->category_name ?? '');
                            ?></span>
                          </span>
                        </a>
                      <?php endforeach; ?>

                      <?php if (!$rest): ?>
                        <div class="p411-empty">
                          <h3>More stories coming</h3>
                          <p>Local announcements and business news are published here as they land.</p>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endif; ?>

                <?php if (!empty($show_videos) && !empty($latest_videos)): ?>
                  <h3 class="p411-subhead" style="margin-top:26px;">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#79B82A" stroke-width="2" stroke-linecap="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m10 9 5 3-5 3z"/></svg>
                    Community Videos
                    <a class="p411-more" href="<?= base_url('videos') ?>">All videos &rarr;</a>
                  </h3>
                  <div class="p411-videos">
                    <?php foreach (array_slice($latest_videos, 0, 3) as $video): ?>
                      <?php
                      $vkey = !empty($video->video_slug) ? (string) $video->video_slug : (string) $video->video_id;
                      // YouTube hosts a still for every video id, so an
                      // uploaded thumbnail is only the first choice, not the
                      // only one.
                      $vthumb = !empty($video->video_image)
                        ? base_url($video->video_image)
                        : youtube_thumb_url($video->video_code ?? '');
                      ?>
                      <a class="p411-video" href="<?= e_url(base_url('video-details/' . rawurlencode($vkey))) ?>">
                        <?php if ($vthumb !== ''): ?>
                          <span class="p411-video__thumb">
                            <img src="<?= e_attr($vthumb) ?>" alt="" loading="lazy" decoding="async">
                            <span class="p411-play">
                              <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                            </span>
                          </span>
                        <?php else: ?>
                          <span class="p411-video__thumb p411-thumb--empty">
                            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2.5" y="5" width="19" height="14" rx="2.5"/><path d="m10 9.5 5 2.5-5 2.5z"/></svg>
                          </span>
                        <?php endif; ?>
                        <b><?= e($video->video_title) ?></b>
                      </a>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </section>
            <?php endif; ?>

          </div><!-- /.p411-col -->

          <!-- ========================================= SIDEBAR == -->
          <aside class="p411-rail">

            <?php // Sold zone. Unsold it carries the burgundy "reach thousands
                  // of Pearland customers" creative rather than a blank box. ?>
            <div class="p411-sidebar-ad">
              <?php $slot = 'right-side-banner-1';
              $slot_creative = 'house-ad-burgundy';
              include APPPATH . 'views/partials/ad_slot.php'; ?>
            </div>

            <!-- Weather (live, via the Weather::proxy endpoint) -->
            <div class="p411-rail__card p411-weather" id="weatherCard">
              <h3 class="p411-rail__title">Pearland Weather</h3>
              <div class="p411-weather__days" id="weatherDays"></div>
              <div class="p411-weather__now" id="weatherNow"></div>
              <button type="button" class="p411-weather__more" id="weatherMore" hidden>View full forecast &rarr;</button>
            </div>

            <!-- Business categories -->
            <div class="p411-rail__card">
              <h3 class="p411-rail__title">Business Categories</h3>
              <?php if (!empty($home_categories)): ?>
                <ul class="p411-catlist">
                  <?php foreach (array_slice($home_categories, 0, 10) as $cat): ?>
                    <li>
                      <a href="<?= e_url($home_cat_url($cat)) ?>">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $p411_icon($cat->category_name) ?></svg>
                        <?= e($cat->category_name) ?>
                        <?php if (isset($cat->listing_count)): ?><em><?= number_format((int) $cat->listing_count) ?></em><?php endif; ?>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
              <a class="p411-rail__foot" href="<?= base_url('all-category') ?>">View All Categories &rarr;</a>
            </div>

            <?php // Static house promos — never sold, never rotated, so these
                  // positions are always filled. Navy for web services,
                  // burgundy for design. Styles: promo-cards.css. ?>
            <?php include APPPATH . 'views/partials/service_promo.php'; ?>

            <div class="p411-sidebar-ad">
              <?php $slot = 'right-side-banner-2';
              // Unsold, this position becomes the ad/banner design card rather
              // than a flat picture of an advertisement. A real booking still
              // wins the slot.
              $slot_fallback_partial = 'banner_design_promo.php';
              include APPPATH . 'views/partials/ad_slot.php'; ?>
            </div>

            <?php // Two more sold zones. Both stay bookable, but neither shows
                  // a house creative when empty: the brief asked for fewer
                  // "your ad here" boxes, and this page already carries three. ?>
            <div class="p411-sidebar-ad">
              <?php $slot = 'right-side-banner-3';
              $slot_fallback = false;
              include APPPATH . 'views/partials/ad_slot.php'; ?>
            </div>

            <div class="p411-sidebar-ad">
              <?php $slot = 'right-panel-skyscraper';
              $slot_fallback = false;
              include APPPATH . 'views/partials/ad_slot.php'; ?>
            </div>

          </aside>

        </div>
      </div>
    </section>

    <!-- =============================== BOTTOM BANNER (sold) == -->
    <div class="container px-10">
      <div class="p411-adrow">
        <?php $slot = 'bottom-banner-site-wide';
        $slot_fallback = false;
        include APPPATH . 'views/partials/ad_slot.php'; ?>
      </div>
    </div>

    <!-- ========================================= OWNER BAND == -->
    <section class="p411-owner">
      <div class="container px-10">
        <div class="p411-owner__head">
          <h2>Proudly Supporting Pearland Businesses</h2>
          <p>Pearland411 connects residents with trusted local businesses, services, events, deals and community news. Whether you are searching for a restaurant, doctor, contractor, retailer or professional service &mdash; start local with Pearland411.</p>
        </div>

        <div class="p411-feat4">
          <div class="p411-feat p411-feat--green">
            <i><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 11.5 12 4l9 7.5"/><path d="M5.5 10.5V20h13v-9.5"/><path d="M10 20v-5h4v5"/></svg></i>
            <div>
              <h3>List Your Business</h3>
              <p>Get discovered by thousands of local customers. The basic listing is free.</p>
              <a href="<?= base_url('register') ?>">Add Your Business Free &rarr;</a>
            </div>
          </div>
          <div class="p411-feat p411-feat--bu">
            <i><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 2.6 5.6 6.4.8-4.6 4.3 1.1 6.3L12 17l-5.5 3 1.1-6.3L3 9.4l6.4-.8z"/></svg></i>
            <div>
              <h3>Get Seen on Top</h3>
              <p>Featured, Hotspot and Business of the Week placements, right here.</p>
              <a href="<?= base_url('pricing-plan') ?>">See Placements &rarr;</a>
            </div>
          </div>
          <div class="p411-feat p411-feat--navy">
            <i><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="14" rx="2"/><path d="M8 22h8M12 18v4"/></svg></i>
            <div>
              <h3>Websites &amp; Local SEO</h3>
              <p>Fast websites. Better rankings. More local customers.</p>
              <a href="<?= base_url('services') ?>">See Our Services &rarr;</a>
            </div>
          </div>
          <div class="p411-feat p411-feat--bu">
            <i><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 15h10M7 11h6"/></svg></i>
            <div>
              <h3>Advertise on Pearland411</h3>
              <p>Banner positions across the site, starting at $20 a month.</p>
              <a href="<?= base_url('post-your-ads') ?>">View Advertising Packages &rarr;</a>
            </div>
          </div>
        </div>

        <div class="p411-owner__cta">
          <a class="p411-btn p411-btn--lg" href="<?= base_url('register') ?>">Add My Business &mdash; Free</a>
          <a class="p411-btn p411-btn--lg p411-btn--ghost" href="<?= base_url('pricing-plan') ?>">See All Packages</a>
        </div>
      </div>
    </section>

    <!-- ========================================== NEWSLETTER == -->
    <section class="p411-signup">
      <div class="container px-10">
        <div class="p411-signup__row">
          <div class="p411-signup__copy">
            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2.5" y="4.5" width="19" height="15" rx="2"/><path d="m3 6 9 6.5L21 6"/></svg>
            <p>
              Get the best local deals, new openings and events in Pearland
              <span>Straight to your inbox &mdash; one email, no noise.</span>
            </p>
          </div>
          <form id="newsletterForm" class="p411-signup__form">
            <label class="sr-only" for="newsletterEmail">Email address</label>
            <input type="email" id="newsletterEmail" name="email" placeholder="Enter your email address" required>
            <button type="submit" id="newsletterSubmit">Subscribe</button>
          </form>
        </div>
        <div class="p411-signup__msg" id="newsletterMessage" role="status"></div>
      </div>
    </section>

  </div><!-- /.full_wrapper -->

  <!-- ============================================== FOOTER == -->
  <?php include APPPATH . 'views/partials/site_footer.php'; ?>

  <?php
  /* Theme's bottom quick-bar (phones). Each item is 20% wide, so five is what
     fits: a sixth turns the bar into a horizontal scroller and the last item
     sits half off the screen. Switched-off sections drop out and the next one
     moves up. */
  $quick = array(
    array(base_url(),               'home.png',    'Home'),
    array(null,                     'search1.png', 'Search'),
    array(base_url('all-category'), 'shop.png',    'Services'),
  );
  if (!empty($show_events)) $quick[] = array(base_url('event'), 'calendar.png', 'Events');
  $quick[] = array(base_url('coupons'), 'cart.png', 'Deals');
  if (!empty($show_news))   $quick[] = array(base_url('news'),  'news.png',     'News');
  ?>
  <div class="fqui-menu">
    <ul>
      <?php foreach (array_slice($quick, 0, 5) as $item): list($href, $icon, $label) = $item; ?>
        <li>
          <?php if ($href === null): ?>
            <span class="mob-sear"><img src="<?= base_url('assets/images/icon/' . $icon) ?>" alt=""><?= e($label) ?></span>
          <?php else: ?>
            <a href="<?= e_url($href) ?>"><img src="<?= base_url('assets/images/icon/' . $icon) ?>" alt=""><?= e($label) ?></a>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <script src="<?= base_url() ?>assets/js/jquery.min.js"></script>
  <script src="<?= base_url() ?>assets/js/popper.min.js"></script>
  <script src="<?= base_url() ?>assets/js/bootstrap.min.js"></script>
  <?php // custom.js calls .autocomplete() and .datepicker() unconditionally,
        // so jQuery UI has to be here or the rest of that file never runs. ?>
  <script src="<?= base_url() ?>assets/js/jquery-ui.js"></script>
  <script src="<?= base_url() ?>assets/js/custom.js"></script>

  <script>
  /* ------------------------------------------------------------------
   * Pearland weather — three days, with the rest behind a link.
   * Data comes from application/controllers/Weather.php (a server-side
   * proxy, so no CORS and no regional block on the upstream API).
   * ---------------------------------------------------------------- */
  (function () {
    var card  = document.getElementById('weatherCard');
    var days  = document.getElementById('weatherDays');
    var now   = document.getElementById('weatherNow');
    var more  = document.getElementById('weatherMore');
    if (!card || !days || !now) return;

    var VISIBLE_DAYS = 3;

    function icon(code, size) {
      if ([0, 1].indexOf(code) > -1) {
        return '<svg width="' + size + '" height="' + size + '" viewBox="0 0 48 48"><circle cx="24" cy="24" r="13" fill="#F5C14E"/></svg>';
      }
      if ([2, 3, 45, 48].indexOf(code) > -1) {
        return '<svg width="' + size + '" height="' + size + '" viewBox="0 0 48 48"><circle cx="18" cy="21" r="9" fill="#F5C14E"/><ellipse cx="27" cy="28" rx="13" ry="9" fill="#C6CFE0"/><ellipse cx="20" cy="30" rx="10" ry="7" fill="#DCE3EF"/></svg>';
      }
      if ([51, 53, 55, 56, 57, 61, 63, 65, 80, 81, 82, 95, 96, 99].indexOf(code) > -1) {
        return '<svg width="' + size + '" height="' + size + '" viewBox="0 0 48 48"><ellipse cx="24" cy="22" rx="14" ry="9" fill="#AEB9CD"/><ellipse cx="28" cy="24" rx="9" ry="7" fill="#C6CFE0"/><g stroke="#5B8FD6" stroke-width="3" stroke-linecap="round"><path d="M17 34v6M24 34v7M31 34v6"/></g></svg>';
      }
      if ([71, 73, 75, 77, 85, 86].indexOf(code) > -1) {
        return '<svg width="' + size + '" height="' + size + '" viewBox="0 0 48 48"><ellipse cx="24" cy="22" rx="14" ry="9" fill="#C6CFE0"/><g stroke="#8FB4E5" stroke-width="3" stroke-linecap="round"><path d="M17 34v5M24 34v6M31 34v5"/></g></svg>';
      }
      return '<svg width="' + size + '" height="' + size + '" viewBox="0 0 48 48"><ellipse cx="24" cy="25" rx="14" ry="9" fill="#C6CFE0"/><ellipse cx="29" cy="27" rx="9" ry="7" fill="#DCE3EF"/></svg>';
    }

    var DESC = {
      0: 'Clear sky', 1: 'Mainly clear', 2: 'Partly cloudy', 3: 'Overcast',
      45: 'Fog', 48: 'Rime fog', 51: 'Light drizzle', 53: 'Drizzle',
      55: 'Dense drizzle', 56: 'Freezing drizzle', 57: 'Freezing drizzle',
      61: 'Slight rain', 63: 'Rain', 65: 'Heavy rain', 71: 'Slight snow',
      73: 'Snow', 75: 'Heavy snow', 77: 'Snow grains', 80: 'Rain showers',
      81: 'Rain showers', 82: 'Heavy showers', 85: 'Snow showers',
      86: 'Snow showers', 95: 'Thunderstorm', 96: 'Thunderstorm', 99: 'Thunderstorm'
    };

    days.innerHTML = '<div class="p411-weather__day" style="grid-column:1/-1;">Loading Pearland weather&hellip;</div>';

    fetch('<?= base_url('weather') ?>')
      .then(function (res) { if (!res.ok) throw new Error('HTTP ' + res.status); return res.json(); })
      .then(function (data) {
        if (!data || data.error || !data.current || !data.daily) {
          throw new Error((data && data.message) || 'Weather data unavailable');
        }

        var daily = data.daily;
        var total = Math.min(7, daily.time.length);
        var html  = '';

        for (var i = 0; i < total; i++) {
          var d     = new Date(daily.time[i] + 'T12:00:00');
          var label = i === 0 ? 'Today' : d.toLocaleDateString('en-US', { weekday: 'short' });
          var code  = daily.weather_code[i];
          html += '<div class="p411-weather__day' + (i >= VISIBLE_DAYS ? ' p411-weather__day--extra' : '') + '">'
                +   '<div class="p411-weather__label">' + label + '</div>'
                +   '<div class="p411-weather__icon">' + icon(code, 28) + '</div>'
                +   '<div class="p411-weather__temp">' + Math.round(daily.temperature_2m_max[i]) + '&deg;/'
                +     Math.round(daily.temperature_2m_min[i]) + '&deg;</div>'
                +   '<div class="p411-weather__desc">' + (DESC[code] || 'Cloudy') + '</div>'
                + '</div>';
        }
        days.innerHTML = html;

        var c    = data.current;
        var dirs = ['N','NNE','NE','ENE','E','ESE','SE','SSE','S','SSW','SW','WSW','W','WNW','NW','NNW'];
        var wind = dirs[Math.round(c.wind_direction_10m / 22.5) % 16];
        var time = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

        now.innerHTML =
            '<div>' + icon(c.weather_code, 44) + '</div>'
          + '<div>'
          +   '<div class="p411-weather__stamp">Now &middot; ' + time + '</div>'
          +   '<div style="display:flex;align-items:baseline;gap:8px;">'
          +     '<span class="p411-weather__big">' + Math.round(c.temperature_2m) + '&deg;</span>'
          +     '<span class="p411-weather__cond">' + (DESC[c.weather_code] || 'Cloudy') + '</span>'
          +   '</div>'
          +   '<div class="p411-weather__sub">Feels like ' + Math.round(c.apparent_temperature) + '&deg;'
          +     ' &middot; Wind ' + Math.round(c.wind_speed_10m) + ' mph ' + wind
          +     ' &middot; Humidity ' + Math.round(c.relative_humidity_2m) + '%</div>'
          + '</div>';

        if (total > VISIBLE_DAYS && more) {
          more.hidden = false;
          more.addEventListener('click', function () {
            var open = card.classList.toggle('is-open');
            more.innerHTML = open ? 'Show less &uarr;' : 'View full forecast &rarr;';
          });
        }
      })
      .catch(function (err) {
        days.innerHTML = '';
        now.innerHTML  = '<div class="p411-weather__err">Pearland weather is unavailable right now. '
                       + 'Please try again shortly.</div>';
        if (window.console) console.error('Weather:', err);
      });
  })();

  /* ------------------------------------------------------------------
   * Newsletter signup — posts to User::subscribe_newsletter.
   * ---------------------------------------------------------------- */
  (function () {
    var form = document.getElementById('newsletterForm');
    if (!form) return;
    var input  = document.getElementById('newsletterEmail');
    var button = document.getElementById('newsletterSubmit');
    var box    = document.getElementById('newsletterMessage');
    var label  = button ? button.innerHTML : 'Subscribe';

    function say(message) {
      if (!box) return;
      box.textContent = message;
      box.classList.add('is-on');
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var email = (input.value || '').trim();
      if (!email) { say('Please enter your email address.'); return; }

      button.disabled = true;
      button.innerHTML = 'Sending&hellip;';

      var body = new FormData();
      body.append('email', email);

      fetch('<?= base_url('user/subscribe_newsletter') ?>', { method: 'POST', body: body })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (data && data.success) {
            input.value = '';
            say(data.message || 'You are subscribed — thank you.');
          } else {
            say((data && data.message) ? data.message : 'Subscription failed. Please try again.');
          }
        })
        .catch(function () { say('Something went wrong. Please try again.'); })
        .finally(function () {
          button.disabled = false;
          button.innerHTML = label;
        });
    });
  })();

  /* ------------------------------------------------------------------
   * Search suggestions for the header's mobile search box.
   * ---------------------------------------------------------------- */
  (function () {
    var input = document.getElementById('top-select-search');
    var list  = document.getElementById('tser-res1');
    if (!input || !list || !window.jQuery) return;

    var timer = null;
    jQuery(input).on('input', function () {
      var term = this.value;
      clearTimeout(timer);
      if (term.length < 2) { jQuery(list).hide(); return; }

      timer = setTimeout(function () {
        jQuery.post('<?= base_url('user/search_suggestions') ?>', { term: term }, function (rows) {
          jQuery(list).empty();
          if (rows && rows.length) {
            jQuery.each(rows, function (i, row) {
              jQuery('<li>').append(
                jQuery('<a>').attr('href', '<?= base_url('search') ?>?q=' + encodeURIComponent(row.suggestion))
                             .text(row.suggestion)
              ).appendTo(list);
            });
            jQuery(list).show();
          } else {
            jQuery(list).hide();
          }
        }, 'json').fail(function () { jQuery(list).hide(); });
      }, 300);
    });

    jQuery(document).on('click', function (e) {
      if (!jQuery(e.target).closest('.sr-sea').length) jQuery(list).hide();
    });
  })();
  </script>
</body>

</html>
