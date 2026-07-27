<?php
/**
 * Static house promo for Pearland411's ad + banner design service, pointing at
 * /request-service.
 *
 *   <?php include APPPATH.'views/partials/banner_design_promo.php'; ?>
 *
 * The sibling of partials/service_promo.php, in the burgundy palette the
 * banner-design creatives already use (assets/images/ads/banner-design-*.svg),
 * so the card and the flat creative read as the same product. Like that
 * partial it is NOT a sellable zone: it never queries `ads` and never rotates.
 *
 * Why a card and not just the SVG: an advertiser who has no artwork is the
 * person this position is for, and a flat image can't tell them what they get,
 * what it costs to start, or give them somewhere to click. It is also what
 * ad_slot.php falls back to on `right-side-banner-2` via
 * $slot_fallback_partial, so the unsold zone sells the service instead of
 * showing a picture of an advertisement.
 *
 * Optional variables (set them before the include):
 *   $bpromo_variant  'sidebar' (default) | 'banner'  — banner is full width.
 *   $bpromo_heading  Override the headline.
 *   $bpromo_intro    Override the one-line intro.
 *
 * Styles: assets/css/promo-cards.css. That file must be linked by any view
 * that includes this partial.
 *
 * Every option is unset at the end so a second include on the same page
 * doesn't inherit the first one's settings.
 */

$bpromo_variant = isset($bpromo_variant) ? $bpromo_variant : 'sidebar';
$bpromo_heading = isset($bpromo_heading) ? $bpromo_heading : 'No artwork for your ad? We&rsquo;ll design it.';
$bpromo_intro   = isset($bpromo_intro)   ? $bpromo_intro   : 'Every banner size on Pearland411, drawn to fit the slot you bought &mdash; so your ad looks like it belongs on the page.';

$bpromo_items = array(
	array(
		'title' => 'Banner &amp; display ads',
		'text'  => 'Every size on the rate card, built to spec.',
		'icon'  => '<rect x="2" y="4" width="20" height="12" rx="2"></rect><path d="M2 12h20M9 4v8"></path>',
	),
	array(
		'title' => 'Logos &amp; brand basics',
		'text'  => 'A mark that still reads at 300 pixels wide.',
		'icon'  => '<circle cx="12" cy="12" r="9"></circle><path d="M12 3a9 9 0 0 0 0 18M3 12h18"></path>',
	),
	array(
		'title' => 'Print &amp; social',
		'text'  => 'Flyers, menus and post artwork to match.',
		'icon'  => '<path d="M6 9V3h12v6"></path><rect x="4" y="9" width="16" height="7" rx="2"></rect><path d="M7 16h10v5H7z"></path>',
	),
);

$bpromo_link = base_url('request-service');
?>

<?php if ($bpromo_variant === 'banner'): ?>

	<section class="p411-bpromo p411-bpromo--banner" aria-label="Pearland411 ad and banner design">
		<div class="p411-bpromo__grid">
			<div>
				<span class="p411-bpromo__eyebrow">Pearland411 Design</span>
				<h3><?= $bpromo_heading ?></h3>
				<p><?= $bpromo_intro ?></p>
			</div>
			<ul>
				<?php foreach ($bpromo_items as $bpromo_item): ?>
					<li>
						<i><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $bpromo_item['icon'] ?></svg></i>
						<span>
							<b><?= $bpromo_item['title'] ?></b>
							<span><?= $bpromo_item['text'] ?></span>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
			<div>
				<a class="p411-bpromo__cta" href="<?= e_url($bpromo_link) ?>">Get my ad designed</a>
			</div>
		</div>
	</section>

<?php else: ?>

	<aside class="p411-bpromo" aria-label="Pearland411 ad and banner design">
		<span class="p411-bpromo__eyebrow">Pearland411 Design</span>
		<h3><?= $bpromo_heading ?></h3>
		<p><?= $bpromo_intro ?></p>

		<ul>
			<?php foreach ($bpromo_items as $bpromo_item): ?>
				<li>
					<i><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $bpromo_item['icon'] ?></svg></i>
					<span>
						<b><?= $bpromo_item['title'] ?></b>
						<span><?= $bpromo_item['text'] ?></span>
					</span>
				</li>
			<?php endforeach; ?>
		</ul>

		<a class="p411-bpromo__cta" href="<?= e_url($bpromo_link) ?>">Get my ad designed</a>
		<a class="p411-bpromo__foot" href="<?= e_url(base_url('pricing-plan')) ?>">See banner sizes &amp; prices &rarr;</a>
	</aside>

<?php endif; ?>

<?php unset($bpromo_variant, $bpromo_heading, $bpromo_intro, $bpromo_items, $bpromo_item, $bpromo_link); ?>
