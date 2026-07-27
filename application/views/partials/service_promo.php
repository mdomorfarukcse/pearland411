<?php
/**
 * Static house promo for the Pearland411 service business —
 * website development, hosting and local SEO — pointing at /contact-us.
 *
 *   <?php include APPPATH.'views/partials/service_promo.php'; ?>
 *
 * Unlike partials/ad_slot.php this is NOT a sellable zone: it never queries
 * `ads` and never rotates, so the position is always filled. It sits in the
 * right column of the directory and listing-details pages, directly beneath a
 * paid slot, which is why it is visually distinct from the house creatives
 * ad_slot.php falls back to (dark green panel, no "Sponsored" microlabel).
 *
 * Optional variables (set them before the include):
 *   $promo_variant  'sidebar' (default) | 'banner'  — banner is full width.
 *   $promo_heading  Override the headline.
 *   $promo_intro    Override the one-line intro.
 *
 * Every option is unset at the end so a second include on the same page
 * doesn't inherit the first one's settings.
 */

$promo_variant = isset($promo_variant) ? $promo_variant : 'sidebar';
$promo_heading = isset($promo_heading) ? $promo_heading : 'Need a website that brings in customers?';
$promo_intro   = isset($promo_intro)   ? $promo_intro   : 'Pearland411 builds and looks after the websites of local businesses — one team for the build, the hosting and the search rankings.';

$promo_items = array(
	array(
		'title' => 'Website development',
		'text'  => 'Fast, mobile-first sites built to convert.',
		'icon'  => '<rect x="2" y="3" width="20" height="14" rx="2"></rect><path d="M8 21h8M12 17v4"></path>',
	),
	array(
		'title' => 'Hosting &amp; maintenance',
		'text'  => 'Managed hosting, backups, updates, uptime.',
		'icon'  => '<rect x="2" y="4" width="20" height="7" rx="2"></rect><rect x="2" y="13" width="20" height="7" rx="2"></rect><path d="M6 7.5h.01M6 16.5h.01"></path>',
	),
	array(
		'title' => 'Local SEO',
		'text'  => 'Get found when Pearland searches for you.',
		'icon'  => '<circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path>',
	),
);

$promo_contact = base_url('contact-us');
?>

<?php if ($promo_variant === 'banner'): ?>

	<section class="p411-promo p411-promo--banner" aria-label="Pearland411 web services">
		<div class="p411-promo__grid">
			<div>
				<span class="p411-promo__eyebrow">Pearland411 Services</span>
				<h3><?= $promo_heading ?></h3>
				<p><?= $promo_intro ?></p>
			</div>
			<ul>
				<?php foreach ($promo_items as $promo_item): ?>
					<li>
						<i><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $promo_item['icon'] ?></svg></i>
						<span style="color:inherit;">
							<b><?= $promo_item['title'] ?></b>
							<span><?= $promo_item['text'] ?></span>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
			<div>
				<a class="p411-btn" href="<?= e_url($promo_contact) ?>">Talk to us</a>
			</div>
		</div>
	</section>

<?php else: ?>

	<aside class="p411-promo" aria-label="Pearland411 web services">
		<span class="p411-promo__eyebrow">Pearland411 Services</span>
		<h3><?= $promo_heading ?></h3>
		<p><?= $promo_intro ?></p>

		<ul>
			<?php foreach ($promo_items as $promo_item): ?>
				<li>
					<i><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $promo_item['icon'] ?></svg></i>
					<span>
						<b><?= $promo_item['title'] ?></b>
						<span><?= $promo_item['text'] ?></span>
					</span>
				</li>
			<?php endforeach; ?>
		</ul>

		<a class="p411-btn p411-btn--block" href="<?= e_url($promo_contact) ?>">Contact us for a quote</a>
		<a class="p411-promo__foot" href="<?= e_url($promo_contact) ?>">Or tell us what you need &rarr;</a>
	</aside>

<?php endif; ?>

<?php unset($promo_variant, $promo_heading, $promo_intro, $promo_items, $promo_item, $promo_contact); ?>
