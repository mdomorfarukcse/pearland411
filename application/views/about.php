<?php
/**
 * /about-us — who Pearland411 is and what it's for.
 *
 * Rebuilt on the same design language as the homepage, the directory and the
 * content sections: the shared kit's hero, section headings, cards, buttons,
 * chips and closing owner band, plus the About-specific bands in
 * assets/css/content.css. Both stylesheets arrive through $page_css, so the
 * ~520 lines of inline <style> this view used to carry — and the purple
 * palette in them, which matched nothing else on the site — are gone.
 *
 * From User::about_us(): $about_stats, $lowest_plan_price, $ad_slots.
 * Both are optional; the page renders without either.
 */

$about_stats = (isset($about_stats) && is_array($about_stats)) ? $about_stats : array();
$ad_slots    = (isset($ad_slots)    && is_array($ad_slots))    ? $ad_slots    : array(
	'top'    => 'top-banner-site-wide',
	'bottom' => 'bottom-banner-site-wide',
);
$lowest_plan_price = isset($lowest_plan_price) ? $lowest_plan_price : null;

/** A stat tile, skipped entirely when the figure is zero or the section is off. */
$ab_stat = function ($key) use ($about_stats) {
	return isset($about_stats[$key]) && (int) $about_stats[$key]['value'] > 0 ? $about_stats[$key] : null;
};
$ab_visible = array_filter(array_map($ab_stat, array_keys($about_stats)));

$ab_listings   = $ab_stat('listings');
$ab_categories = $ab_stat('categories');
?>

<section class="p411-ct p411-ct--about">

	<!-- ============================================================ HERO -->
	<div class="p411-hero">
		<div class="p411-hero__bg" style="background-image:url('<?= base_url('assets/images/all-listing-bg.webp') ?>');" aria-hidden="true"></div>
		<div class="container">
			<nav class="p411-crumb" aria-label="Breadcrumb">
				<a href="<?= base_url() ?>">Home</a>
				<span aria-hidden="true">/</span>
				<span class="is-current">About us</span>
			</nav>

			<h1>About Pearland411</h1>
			<p class="p411-hero__lede">
				The local business directory and community hub for Pearland, Texas &mdash; built to connect
				residents, visitors and the businesses that serve them, in one place.
			</p>

			<div class="p411-hero__stats">
				<?php if ($ab_listings): ?>
					<div><b><?= number_format((int) $ab_listings['value']) ?></b>businesses listed</div>
				<?php endif; ?>
				<?php if ($ab_categories): ?>
					<div><b><?= number_format((int) $ab_categories['value']) ?></b>categories to browse</div>
				<?php endif; ?>
				<div><b>Free</b>to list your business</div>
			</div>

			<div class="p411-ct-cta">
				<a class="p411-btn" href="<?= e_url(base_url('all_listing')) ?>">Browse the directory</a>
				<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('register')) ?>">Add my business</a>
				<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('contact-us')) ?>">Talk to us</a>
			</div>
		</div>
	</div>

	<div class="container">
		<div class="p411-adrow" style="margin-top:22px;">
			<?php $slot = $ad_slots['top'];
			include APPPATH . 'views/partials/ad_slot.php'; ?>
		</div>
	</div>

	<!-- ==================================================== WHO WE ARE -->
	<div class="p411-ab-band">
		<div class="container">
			<?php // Text first in the source, image pulled to the left column only
			      // once there are two columns: on a phone the heading should
			      // still be the first thing after the hero, not a photograph. ?>
			<div class="p411-ab-split p411-ab-split--media-first">
				<div>
					<h2>Who we are</h2>
					<div class="p411-ab-lede">
						<p>
							Pearland411 is a local business directory and community hub for Pearland, Texas.
							The idea is a simple one: give residents one place to find a trusted local business,
							and give those businesses somewhere they can actually be found.
						</p>
						<p>
							Restaurants, contractors, clinics, real-estate agents, classifieds, events, deals and
							local news all live here side by side, because that is how people actually look
							things up &mdash; by what they need, not by which website has it.
						</p>
					</div>

					<div class="p411-ab-note">
						<b>Our commitment:</b> every listing on the site is checked before it is published, and
						we keep working on the platform so it stays accurate, quick and genuinely useful to the
						people who live and work here.
					</div>

					<div class="p411-ab-actions">
						<a class="p411-btn" href="<?= e_url(base_url('all_listing')) ?>">See what's listed</a>
						<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('contact-us')) ?>">Get in touch</a>
					</div>
				</div>

				<figure class="p411-ab-split__media">
					<img src="<?= base_url('assets/images/all-listing-bg.webp') ?>"
						 alt="Downtown Houston at sunset, a short drive north of Pearland"
						 loading="lazy" decoding="async" width="2100" height="1400">
					<figcaption>Pearland sits on the southern edge of the Houston metro &mdash; close enough to the city, far enough to be its own town.</figcaption>
				</figure>
			</div>
		</div>
	</div>

	<!-- ================================================= LIVE FIGURES -->
	<?php if (!empty($ab_visible)): ?>
		<div class="p411-ab-band p411-ab-band--tint">
			<div class="container">
				<div class="p411-ab-head">
					<h2>Pearland411 today</h2>
					<p>
						Not a brochure figure &mdash; these are counted off the live site every time this page
						loads, and each one links to the pages they came from.
					</p>
				</div>

				<div class="p411-ab-stats">
					<?php foreach ($ab_visible as $stat): ?>
						<div>
							<b><?= number_format((int) $stat['value']) ?></b>
							<span><?= e($stat['label']) ?></span>
							<?php if (!empty($stat['url'])): ?>
								<a href="<?= e_url($stat['url']) ?>"><?= e($stat['link']) ?> &rarr;</a>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<!-- =================================================== WHAT WE DO -->
	<div class="p411-ab-band">
		<div class="container">
			<div class="p411-ab-head">
				<h2>What we do</h2>
				<p>
					One platform with two jobs: help a local business get found, and help a local resident
					find one without wading through six other websites first.
				</p>
			</div>

			<div class="p411-feat4">
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></i>
					<h3>A directory people can search</h3>
					<p>Thousands of listings by category, with phone numbers, addresses, opening details, reviews and directions on every one.</p>
				</div>
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l7 4v5c0 4.6-3 8.6-7 10-4-1.4-7-5.4-7-10V7z"/><path d="m9 12 2 2 4-4"/></svg></i>
					<h3>Listings we've checked</h3>
					<p>Businesses are reviewed before they go live, and an owner can claim their listing to keep the details right themselves.</p>
				</div>
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 5.6a5 5 0 0 0-7.1 0L12 7.3l-1.7-1.7a5 5 0 0 0-7.1 7.1L12 21l8.8-8.3a5 5 0 0 0 0-7.1z"/></svg></i>
					<h3>The rest of local life</h3>
					<p>Events, news, videos, classifieds, coupons and property &mdash; the things that make a directory somewhere you come back to.</p>
				</div>
			</div>
		</div>
	</div>

	<!-- ============================================ VISION & MISSION -->
	<div class="p411-ab-band p411-ab-band--dark">
		<div class="container">
			<div class="p411-ab-head">
				<h2>Where we're going</h2>
				<p>What we're building towards, and what we hold ourselves to while we do it.</p>
			</div>

			<div class="p411-ab-panels">
				<div class="p411-ab-panel">
					<h3>
						<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6-10-6-10-6z"/><circle cx="12" cy="12" r="2.8" fill="currentColor" stroke="none"/></svg></i>
						Our vision
					</h3>
					<p>
						To be the most reliable, most community-driven place to look something up in Pearland
						&mdash; where every local business can be found and every resident can get to what they
						need without a fight.
					</p>
					<p>
						That means putting small local operators on the same footing as the chains, and using
						the site's reach to send real customers their way.
					</p>
				</div>

				<div class="p411-ab-panel">
					<h3>
						<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4.5"/><circle cx="12" cy="12" r="1.4" fill="currentColor" stroke="none"/></svg></i>
						Our mission
					</h3>
					<ul>
						<?php
						$ab_mission = array(
							'Help local businesses grow through a listing people actually reach, and promotions that are priced for a small business.',
							'Give Pearland residents one directory that covers what they need &mdash; services, events, deals, property, news.',
							'Back local events and initiatives, because a directory is only as strong as the community around it.',
							'Publish information we have checked, and correct it quickly when we get it wrong.',
						);
						foreach ($ab_mission as $ab_item): ?>
							<li>
								<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m4 12.5 5 5L20 6.5"/></svg>
								<span><?= $ab_item ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</div>
	</div>

	<div class="container">
		<div class="p411-adrow" style="margin:22px 0;">
			<?php $slot = $ad_slots['inline'] ?? '';
			$slot_creative = 'house-ad-cream';
			include APPPATH . 'views/partials/ad_slot.php'; ?>
		</div>
	</div>

	<!-- ============================================== WHY CHOOSE US -->
	<div class="p411-ab-band p411-ab-band--tint">
		<div class="container">
			<div class="p411-ab-head">
				<h2>Why people use Pearland411</h2>
				<p>Six reasons a resident bookmarks the site and a business owner keeps their listing current.</p>
			</div>

			<div class="p411-ab-why">
				<div>
					<i><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13M3.5 6h.01M3.5 12h.01M3.5 18h.01"/></svg></i>
					<div>
						<h3>Broad coverage</h3>
						<p>Businesses across every category the town actually has, not just the ones that paid.</p>
					</div>
				</div>
				<div>
					<i><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0z"/><circle cx="12" cy="10" r="2.6"/></svg></i>
					<div>
						<h3>Local, not national</h3>
						<p>Everything on the site is about Pearland and the streets around it.</p>
					</div>
				</div>
				<div>
					<i><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m4 3 15 6.5-6.4 2L10 18z"/></svg></i>
					<div>
						<h3>Quick to use</h3>
						<p>Search, filter, call. It works on a phone in a car park, which is where most people use it.</p>
					</div>
				</div>
				<div>
					<i><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3.4"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 5.2a3.4 3.4 0 0 1 0 5.6M18 20a6.5 6.5 0 0 0-2.3-4.9"/></svg></i>
					<div>
						<h3>Community driven</h3>
						<p>Reviews, events and stories come from the people who live here.</p>
					</div>
				</div>
				<div>
					<i><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"/><circle cx="7.5" cy="7.5" r="1.4"/></svg></i>
					<div>
						<h3>Free and paid, both real</h3>
						<p>A free listing is a proper listing. Paid packages add reach, not basic function.</p>
					</div>
				</div>
				<div>
					<i><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7.5V12l3 2.4"/></svg></i>
					<div>
						<h3>Kept up to date</h3>
						<p>Owners can claim and edit their own listing, so details don't go stale.</p>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- ========================================== FOR BUSINESS OWNERS -->
	<div class="p411-ab-band">
		<div class="container">
			<div class="p411-ab-split">
				<div>
					<h2>For business owners</h2>
					<div class="p411-ab-lede">
						<p>
							Pearland411 is more than a listing page &mdash; it's a marketing channel for a
							business that doesn't have a marketing department. Create an account, add your
							business, and you're in front of people already searching for what you do.
						</p>
						<p>
							When you're ready for more, a paid package adds photos, a map, your services, a
							video and featured placement above the other results in your category<?php
								if ($lowest_plan_price !== null): ?> &mdash; from
								<b>$<?= number_format((float) $lowest_plan_price, 2) ?> a month</b><?php
								endif; ?>.
						</p>
					</div>

					<div class="p411-chips" style="margin-top:20px;">
						<span>Featured placement</span>
						<span>Photo gallery</span>
						<span>Map &amp; directions</span>
						<span>Your services listed</span>
						<span>A video on your page</span>
						<span>Enquiries in one inbox</span>
					</div>

					<div class="p411-ab-actions">
						<a class="p411-btn" href="<?= e_url(base_url('register')) ?>">Add my business &mdash; free</a>
						<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('pricing-plan')) ?>">See the packages</a>
					</div>
				</div>

				<?php // A card of the three real steps, rather than a stock photo of a
				      // meeting: it tells an owner what actually happens next. ?>
				<div class="p411-card">
					<div class="p411-card__head">
						<h2>Getting listed</h2>
					</div>
					<ul class="p411-ab-steps">
						<li>
							<div>
								<b>Create your account</b>
								<span>Name, email, password. No card, and nothing to cancel later.</span>
							</div>
						</li>
						<li>
							<div>
								<b>Add your business &mdash; or claim it</b>
								<span>If we already list you, claim it and the listing transfers to you once approved.</span>
							</div>
						</li>
						<li>
							<div>
								<b>Upgrade when it's worth it</b>
								<span>Add photos, a map, your services and featured placement whenever you want them &mdash; monthly, and cancellable.</span>
							</div>
						</li>
					</ul>

					<a class="p411-btn p411-btn--block" style="margin-top:18px;" href="<?= e_url(base_url('register')) ?>">Start &mdash; it's free</a>
					<a class="p411-btn p411-btn--block p411-btn--ghost" style="margin-top:8px;" href="<?= e_url(base_url('all-listing')) ?>">Check whether you're already listed</a>
				</div>
			</div>
		</div>
	</div>

	<div class="container">
		<?php // Pearland411's own services. Never sold, never rotated, so the
		      // position is never empty. ?>
		<?php $promo_variant = 'banner';
		include APPPATH . 'views/partials/service_promo.php'; ?>

		<div class="p411-adrow" style="margin-top:6px;">
			<?php $slot = $ad_slots['bottom'];
			include APPPATH . 'views/partials/ad_slot.php'; ?>
		</div>
	</div>

	<!-- ================================================== OWNER BAND -->
	<div class="p411-owner">
		<div class="container">
			<div class="p411-owner__head">
				<h2>Join the community</h2>
				<p>
					Resident, business owner or just visiting &mdash; you're welcome here. Between us we can
					make Pearland easier to get around, and its local businesses easier to find.
				</p>
			</div>
			<div class="p411-feat4">
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></i>
					<h3>Find a business</h3>
					<p>Search the directory by category, name or what you need doing.</p>
				</div>
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V9l7-5 7 5v12"/><path d="M10 21v-6h4v6"/></svg></i>
					<h3>List your own</h3>
					<p>A free listing puts you in search and on your category page.</p>
				</div>
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/></svg></i>
					<h3>Tell us what's on</h3>
					<p>Submit an event or send a story and we'll put it in front of the town.</p>
				</div>
				<div>
					<i><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 15h10M7 11h6"/></svg></i>
					<h3>Advertise with us</h3>
					<p>Banner positions across the site, from $20 a month.</p>
				</div>
			</div>
			<div class="p411-owner__cta">
				<a class="p411-btn" href="<?= e_url(base_url('register')) ?>">Create an account</a>
				<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('all_listing')) ?>">Browse the directory</a>
				<a class="p411-btn p411-btn--ghost" href="<?= e_url(base_url('contact-us')) ?>">Contact us</a>
			</div>
		</div>
	</div>
</section>
