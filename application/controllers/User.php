<?php

defined('BASEPATH') or exit('No direct script access allowed');



class User extends CI_Controller
{



	public function __construct()
	{
		parent::__construct();
		$this->load->model('usermodel');
		$this->load->model('adminmodel');
		$this->load->library('pagination');
		$this->load->library('form_validation');
		$this->load->library('rate_limit');
		$this->load->helper('url');

		if ($this->input->post('logout')) {
			$this->session->unset_userdata('logged_user');
			$this->session->sess_destroy();
			redirect(base_url());
			return;
		}

		// Public methods reachable without a session. Anything not in
		// this list requires a logged-in end-user (session key
		// 'logged_user'). Admin sessions ('logged_admin') do NOT
		// satisfy this — they're a different audience.
		$public_actions = array(
			// Auth pages
			'register', 'login', 'forgot_password',
			'reset_password', 'verify_email',
			// For people who cannot sign in until they verify: login,
			// register, verify-email and forgot-password all link here, and
			// without this entry a logged-out visitor was bounced to /login.
			'resend_verification',
			'logout', 'subscribe_newsletter', 'newsletter_confirm',
			'contact_submit',
			// Home + static pages
			'index', 'about_us', 'faq', 'contact_us', 'privacy_policy', 'terms_of_use',
			// Public browsing — listings/categories
			'all_category', 'category_search', 'all_listing',
			'listing_details',
			'pricing_plan', 'search', 'search_suggestions',
			// Public content sections
			'news', 'news_details',
			'all_blog', 'blog_details',
			'event', 'event_search', 'event_details',
			'videos', 'video_details',
			'classifieds', 'classifieds_details',
			'all_products', 'product_details',
			'house_details', 'rental_details',
			// Checkout. checkout() redirects to login when no session is
			// present; process_checkout()/helcim_init() re-check the session
			// themselves. helcim_webhook is server-to-server (Helcim calls it)
			// and is signature-verified instead of session-gated.
			'checkout', 'checkout_success', 'process_checkout', 'helcim_webhook',
			// Advertising cart — guests build a cart before logging in.
			'cart_add', 'cart_remove', 'cart_get', 'cart_clear', 'buy_now', 'cart_set_term', 'cart_set_category',
			// Enquiry submission (rate-limited; anonymous OK)
			'add_enquiry',
			// Services catalog + quote requests
			'services', 'service_details', 'request_service', 'request_quote',
			// Listing claim (form gates on session itself)
			'claim_listing',
			// Public coupons / deals
			'coupons', 'coupon_details',
			// Advertise landing page (form posts to /contact-submit)
			'post_your_ads',
		);

		$page = $this->router->fetch_method();

		// A member suspended at /admin/users is signed out on their next
		// request. login() already refused a banned account, but a session
		// opened before the suspension carried on publishing until it
		// expired. SELECT * rather than naming `banned`, so an install
		// without the column (pre-0035) is unaffected.
		$__sess = $this->session->userdata('logged_user');
		if (!empty($__sess['logged_id'])) {
			$__q = $this->db->query('SELECT * FROM ' . $this->db->protect_identifiers('user', TRUE) . ' WHERE user_id = ? LIMIT 1', array((int) $__sess['logged_id']));
			$__me = $__q ? $__q->row() : NULL;
			if ($__me && isset($__me->banned) && (int) $__me->banned === 1) {
				$this->session->unset_userdata('logged_user');
				$this->session->set_flashdata('login_message', 'Your account has been suspended. Please contact Pearland411 if you think this is a mistake.');
				redirect(base_url('login'));
				return;
			}
		}

		if (!in_array($page, $public_actions, true)) {
			if (!$this->session->userdata('logged_user')) {
				$this->session->set_userdata('redirect_url', current_url());
				redirect(base_url('login'));
				return;
			}
		}
	}

	/**
	 * Professional helper method to load header categories
	 * Uses unique method name to avoid conflicts
	 * Fixed to use working method and handle status filtering
	 */
	private function loadHeaderCategories(&$data)
	{
		// Try the original method first
		$header_cats = $this->usermodel->getCategoriesForHeader(50);

		// If no results, fall back to the busiest listing categories. This
		// used to fall back to getAllCategory(), which reads the marketplace
		// product_categories table — names that /all_listing/<slug> (listing
		// categories) cannot resolve.
		if (empty($header_cats)) {
			$data['header_categories'] = $this->usermodel->getCategoriesWithCounts(50);
		} else {
			$data['header_categories'] = $header_cats;
		}
	}


	/**
	 * Helper method to safely get server variables
	 * Prevents undefined index errors for $_SERVER variables
	 */
	private function getServerVar($key, $default = '')
	{
		return isset($_SERVER[$key]) ? $_SERVER[$key] : $default;
	}

	/**
	 * Where to send someone back to after an enquiry that could not be saved.
	 *
	 * The referer, but only when it is one of ours. The enquiry handler used to
	 * `redirect($_SERVER['HTTP_REFERER'])` with no isset() guard at all — an
	 * undefined-index notice and a redirect to an empty URL when there is no
	 * referer, and a redirect to wherever an attacker's page said otherwise.
	 */
	private function enquiryReturnUrl()
	{
		$referer = trim((string) $this->getServerVar('HTTP_REFERER', ''));
		if ($referer === '') {
			return base_url();
		}

		$here = rtrim((string) parse_url(base_url(), PHP_URL_HOST), '/');
		$there = (string) parse_url($referer, PHP_URL_HOST);

		return ($there !== '' && strcasecmp($there, $here) === 0) ? $referer : base_url();
	}

	/**
	 * Helper method to safely get POST variables
	 * Prevents undefined index errors for $_POST variables
	 */
	private function getPostVar($key, $default = '')
	{
		return isset($_POST[$key]) ? $_POST[$key] : $default;
	}

	/**
	 * Helper method to safely get GET variables
	 * Prevents undefined index errors for $_GET variables
	 */
	private function getGetVar($key, $default = '')
	{
		return isset($_GET[$key]) ? $_GET[$key] : $default;
	}

	/**
	 * The id of the currently logged-in end-user (0 when not logged in).
	 */
	private function currentUserId()
	{
		$sess = $this->session->userdata('logged_user');
		return (is_array($sess) && isset($sess['logged_id'])) ? (int) $sess['logged_id'] : 0;
	}

	/**
	 * Ownership guard for listing edit endpoints. Returns TRUE when the
	 * current user owns $listing; otherwise emits a 404 and returns FALSE.
	 * Callers must `return;` when this returns FALSE.
	 */
	private function requireOwnedListing($listing)
	{
		if (empty($listing) || (int) $listing->user_id !== $this->currentUserId()) {
			show_404();
			return false;
		}
		return true;
	}

	/**
	 * Layer-1 service gate. Optional content sections (events / news / blog /
	 * products / videos / classifieds / real estate) are switched on/off by the
	 * admin via site_settings. When a section is off its public pages 404.
	 * Defaults to '1' (visible) so a site that has not run migration 0011 keeps
	 * its current behaviour. Callers must `return;` when this returns FALSE.
	 */
	private function requireService($key)
	{
		if (site_setting($key, '1') === '0') {
			show_404();
			return false;
		}
		return true;
	}

	/**
	 * The six ad positions the content sections render — events, news and
	 * videos, index and detail alike.
	 *
	 * Identical to the directory's, and for the same reason: every key is a
	 * `plans.slug` from /pricing-plan (migrations 0017 + 0019), so buying the
	 * package fills the position. The 0006 zones these pages used to render
	 * ('events-sidebar', 'news-sidebar') match no package and so could never
	 * be bought — they stay reachable as $slot_alias in the views, so an ad an
	 * operator booked against one by hand keeps getting its impressions.
	 */
	private function contentAdSlots()
	{
		return array(
			'top'      => 'top-banner-site-wide',
			'inline'   => 'middle-banner-category',
			'side_1'   => 'right-side-banner-1',
			'side_2'   => 'right-side-banner-2',
			'side_sky' => 'right-panel-skyscraper',
			'bottom'   => 'bottom-banner-site-wide',
		);
	}

	/**
	 * The ad zones a page carries, primed in one query.
	 *
	 * Seventeen public pages rendered no advertising at all — search results,
	 * the FAQ, contact, the policy pages, products, classified/house/rental
	 * detail, pricing, services, quotes, claim and the auth screens. Every one
	 * of them is a page a visitor reaches from the directory, and none of them
	 * carried so much as a house ad, so the site read as having no advertisers.
	 *
	 * Three shapes, because what a page can hold depends on whether it has a
	 * column to hang a box off:
	 *
	 *   'full'   main + sidebar        6 zones — 3 wide, 3 down the side
	 *   'wide'   full-width editorial  3 wide zones (no sidebar to fill)
	 *   'banner' a single narrow form  2 wide zones, top and bottom
	 *
	 * Every key is a `plans.slug` sold on /pricing-plan, deliberately: a
	 * package a customer has ALREADY bought fills these positions the moment
	 * the page ships, rather than each page minting inventory nobody can buy.
	 * That is also why nothing here is page-specific — "site wide" finally
	 * means site wide. See migrations 0017 / 0019 / 0024.
	 *
	 * Priming matters: without it each include fires its own `ORDER BY RAND()`,
	 * so a six-zone page costs six queries instead of one.
	 *
	 * @param  string $shape 'full' | 'wide' | 'banner'
	 * @return array<string,string> view key => slot_key
	 */
	/**
	 * The house-ad budget for a page whose result set can come back empty.
	 *
	 * "No businesses in this category yet" beside a leaderboard, a sidebar box
	 * and a skyscraper is advertising outnumbering content — the one layout
	 * search engines actually penalise, and the exact page the client
	 * screenshotted. One house creative on an empty page; the site default
	 * (`ads_house_max_per_page`, 2) when there is something to read.
	 *
	 * NULL means "no override" — ad_slot.php then reads the site setting.
	 * A SOLD ad is never affected either way; this governs house creatives
	 * only, and an advertiser who paid for the position keeps it.
	 *
	 * @param  int      $result_count Rows the page is about to render.
	 * @return int|null
	 */
	private function thinPageAdCap($result_count)
	{
		return (int) $result_count > 0 ? NULL : 1;
	}

	private function pageAdSlots($shape = 'full')
	{
		$slots = array(
			'top'    => 'top-banner-site-wide',
			'bottom' => 'bottom-banner-site-wide',
		);

		if ($shape !== 'banner') {
			$slots['inline'] = 'middle-banner-category';
		}

		// The same six the directory and the content sections carry, in the
		// same order — see contentAdSlots(). Deliberately not a seventh box:
		// `right-side-banner-3` exists and is sold, but it is the homepage's
		// third sidebar position, and a sidebar that is three boxes plus a
		// skyscraper is an ad column with a page attached.
		if ($shape === 'full') {
			$slots['side_1']   = 'right-side-banner-1';
			$slots['side_2']   = 'right-side-banner-2';
			$slots['side_sky'] = 'right-panel-skyscraper';
		}

		if (method_exists($this->usermodel, 'primeAdSlots')) {
			$this->usermodel->primeAdSlots(array_values($slots));
		}

		return $slots;
	}

	/**
	 * Per-page stylesheets for the content sections: the shared page kit, the
	 * components those six pages add, and the two house promo cards. Loaded
	 * through the $page_css hook in views/header.php, after the shared sheets.
	 */
	private function contentPageCss()
	{
		return array(
			'assets/css/directory.css',
			'assets/css/content.css',
			'assets/css/promo-cards.css',
		);
	}

	/**
	 * Field-level listing capabilities for a listing owner, derived from
	 * their active plan (freemium model). With no active plan — a claimed
	 * listing on the free tier — every rich capability is off; only
	 * name / phone / email stay editable. `photo_limit` is -1 = unlimited,
	 * 0 = none, N = max gallery photos.
	 */
	private function listingCapabilities($owner_id)
	{
		$sub = $this->usermodel->getUserSubscription((int) $owner_id);
		return array(
			'can_photos'        => (bool) ($sub && !empty($sub->can_photos)),
			'photo_limit'       => (int)  ($sub && isset($sub->photo_limit) ? $sub->photo_limit : 0),
			'can_map'           => (bool) ($sub && !empty($sub->can_map)),
			'can_services'      => (bool) ($sub && !empty($sub->can_services)),
			'can_video'         => (bool) ($sub && !empty($sub->allow_video)),
			'can_extra_details' => (bool) ($sub && !empty($sub->can_extra_details)),
		);
	}

	/**
	 * Custom URL routing handler
	 * Handles hyphenated URLs like all-category/category-search
	 */
	public function _remap($method, $params = array())
	{
		// Handle hyphenated URLs
		if ($method == 'all-category') {
			// If there's a second parameter, handle sub-routes
			if (!empty($params[0])) {
				if ($params[0] == 'category-search') {
					$this->category_search();
					return;
				}
			} else {
				// Just all-category without sub-route
				$this->all_category();
				return;
			}
		}

		// Handle other hyphenated methods by converting to underscore
		$method_underscore = str_replace('-', '_', $method);

		// Check if the underscore method exists
		if (method_exists($this, $method_underscore)) {
			call_user_func_array(array($this, $method_underscore), $params);
		} else {
			// Method doesn't exist, show 404
			show_404();
		}
	}

	/*Home Page*/
	public function index()
	{

		// Initialize data array
		$data = array();

		// --- Optional sections -----------------------------------------
		// Each content type has an admin switch. When a service is off its
		// page 404s via requireService(), so the homepage must not link to
		// it either.
		$data['show_news']   = site_setting('service_news_enabled',   '1') !== '0';
		$data['show_videos'] = site_setting('service_videos_enabled', '1') !== '0';
		$data['show_events'] = site_setting('service_events_enabled', '1') !== '0';

		// --- Directory content -----------------------------------------
		//
		// Four blocks on this page draw from `listings`, and until the
		// 2026-08 rebuild none of them knew what the others had returned —
		// which is how one tree surgeon ended up in the Hotspot rail, the
		// Featured row and the newest-businesses grid on the same screen.
		// They are now filled in priority order (paid placements first) and
		// every block is told what has already been used.
		//
		// De-duplication is by listing_id AND by a flattened name: the
		// directory carries genuine duplicate rows for some businesses, and
		// two different ids for "Monster Tree Service of Pearland" look
		// identical to a reader.
		$seen_ids   = array();
		$seen_names = array();
		$dedupe = function ($rows) use (&$seen_ids, &$seen_names) {
			$out = array();
			foreach ((array) $rows as $row) {
				$id   = isset($row->listing_id) ? (int) $row->listing_id : 0;
				$name = isset($row->listing_name) ? (string) $row->listing_name : '';
				$key  = preg_replace('/[^a-z0-9]+/', '', strtolower($name));

				if ($id && in_array($id, $seen_ids, TRUE)) continue;
				if ($key !== '' && isset($seen_names[$key]))  continue;

				if ($id)          $seen_ids[] = $id;
				if ($key !== '')  $seen_names[$key] = TRUE;
				$out[] = $row;
			}
			return $out;
		};

		// The two homepage placements sold on /pricing-plan. Both are backed by
		// a DATE column (migration 0018), so they expire on their own and both
		// return empty until that migration has run. Paid placements are
		// claimed first — they are what somebody has actually bought.
		$data['home_hotspot'] = $dedupe($this->usermodel->getHotspotListings(8));

		$week = $this->usermodel->getBusinessOfTheWeek();
		$week = $week ? $dedupe(array($week)) : array();
		$data['home_week'] = $week ? $week[0] : NULL;

		// Featured: over-fetch, then de-dupe and cut to three. Only listings
		// with a real description qualify — see getHomepageFeatured().
		$data['featured_listings'] = array_slice(
			$dedupe($this->usermodel->getHomepageFeatured(8, $seen_ids)), 0, 3
		);

		// The general feed. Six on the homepage, not nine and not everything:
		// a long grid of business cards is what pushed the deals, events and
		// news below the fold. "View all Pearland businesses" carries the rest.
		$data['latest_listings'] = array_slice(
			$dedupe($this->usermodel->getHomepageListings(14, $seen_ids)), 0, 6
		);

		$data['latest_videos'] = $data['show_videos'] ? $this->usermodel->getLatestVideos(3) : array();
		// Five: one lead story with a large image plus four supporting rows.
		$data['latest_news']   = $data['show_news']   ? $this->usermodel->getLatestNews(5)   : array();
		$data['home_events']   = $data['show_events'] ? $this->usermodel->getUpcomingEvents(3) : array();

		// Real coupons, replacing the four invented "hot deals".
		// getDistinctActiveCoupons() de-dupes in PHP — see the model for why a
		// GROUP BY here is fatal under ONLY_FULL_GROUP_BY.
		$data['home_deals'] = $this->usermodel->getDistinctActiveCoupons(3);

		// Categories: stable, count-ordered for the strip and the sidebar.
		$data['home_categories']        = $this->usermodel->getCategoriesWithCounts(12);
		$data['all_listing_categories'] = $this->usermodel->getHeaderNavigationCategories(50);
		$data['header_categories']      = $data['all_listing_categories'];
		// The footer partial takes whatever the page already loaded rather
		// than running its own query.
		$data['footer_categories']      = array_slice($data['home_categories'], 0, 7);

		// Popular searches under the hero search box. Deliberately search
		// terms rather than category links: the category names in this
		// install run to things like "Glass-Circles & Other Special Shapes",
		// which reads badly in a one-line list, and /search?q= always
		// resolves to something.
		$data['popular_searches'] = array(
			'Restaurants', 'Plumbers', 'Hair Salons', 'Dentists', 'Auto Repair',
		);

		// --- Advertising ------------------------------------------------
		// Prime every zone the view renders in ONE query; without this each
		// ad_slot include fires its own ORDER BY RAND().
		// These keys are the plans.slug values of the packages sold on
		// /pricing-plan, so a purchase maps straight to a position. See 0017.
		$data['home_ad_slots'] = array(
			'top-banner-site-wide',
			'middle-banner-category',
			'bottom-banner-site-wide',
			'right-side-banner-1',
			'right-side-banner-2',
			'right-side-banner-3',
			'right-panel-skyscraper',
		);
		if (method_exists($this->usermodel, 'primeAdSlots')) {
			$this->usermodel->primeAdSlots($data['home_ad_slots']);
		}

		// Real prices for the "Advertise with us" block, so the page can't
		// drift from the catalog the way the hard-coded $29/$59/$99 did.
		$data['ad_rate_card'] = $this->usermodel->getAdSlotRateCard(4);
		$data['listing_plans'] = $this->usermodel->getActivePlans();

		// --- SEO -------------------------------------------------------
		$data['title']            = 'Pearland411 — Local Businesses, Events, Deals & News in Pearland, TX';
		$data['meta_description'] = 'Pearland411 is your community guide to Pearland, Texas — find local businesses, upcoming events, deals, news and jobs all in one place.';
		$data['meta_keywords']    = 'Pearland businesses, Pearland events, Pearland deals, Pearland news, Pearland TX directory, local Pearland community';
		$data['canonical_url']    = base_url('/');
		$data['og_image']         = base_url('assets/images/pearland-logo.png');
		// Home-page structured data: WebSite (with SiteLinks search box)
		// + Organization. Helps Google show the search box and the brand
		// knowledge panel.
		$data['structured_data']  = array(
			'@context' => 'https://schema.org',
			'@graph'   => array(
				array(
					'@type'           => 'WebSite',
					'name'            => 'Pearland411',
					'alternateName'   => 'Pearland 411',
					'url'             => base_url('/'),
					'potentialAction' => array(
						'@type'       => 'SearchAction',
						'target'      => array(
							'@type'       => 'EntryPoint',
							'urlTemplate' => base_url('search') . '?q={search_term_string}',
						),
						'query-input' => 'required name=search_term_string',
					),
				),
				array(
					'@type'    => 'Organization',
					'name'     => 'Pearland411',
					'url'      => base_url('/'),
					'logo'     => base_url('assets/images/pearland-logo.png'),
					// external_url(), as the footer icons use: a schemeless
					// "facebook.com/x" becomes https://, and anything that is
					// not http(s) is dropped rather than published as sameAs.
					'sameAs'   => array_values(array_filter(array(
						external_url(site_setting('social_facebook_url',  '')) ?: null,
						external_url(site_setting('social_twitter_url',   '')) ?: null,
						external_url(site_setting('social_instagram_url', '')) ?: null,
						external_url(site_setting('social_linkedin_url',  '')) ?: null,
						external_url(site_setting('social_youtube_url',   '')) ?: null,
					))),
				),
			),
		);

		// Load the view with space in filename
		$this->load->view('index', $data);
	}

	// About Us Page (static, with header/footer)
	public function about_us()
	{
		$data = array();

		// Live figures for the "Pearland411 today" strip. Each one is counted
		// with the same query the page it links to uses — countListingsByCategory()
		// runs directoryQuery(), the same helper /all-listing counts with — so a
		// visitor who clicks through sees the number they were just shown. The
		// page used to claim "thousands of verified businesses" in static copy.
		$stats = array(
			'listings' => array(
				'value' => (int) $this->usermodel->countListingsByCategory(NULL),
				'label' => 'businesses listed',
				'link'  => 'Browse the directory',
				'url'   => base_url('all_listing'),
			),
			// Categories that actually have a published listing in them, which
			// is what the directory's own strip shows — countAllCategories()
			// would include the empty ones and read higher than the directory.
			'categories' => array(
				'value' => count($this->usermodel->getCategoriesWithCounts(1000)),
				'label' => 'categories to browse',
				'link'  => 'See all categories',
				'url'   => base_url('all-category'),
			),
		);

		// A section that has been switched off in /admin/setting 404s, so don't
		// advertise its figure here.
		if (site_setting('service_events_enabled', '1') !== '0') {
			$stats['events'] = array(
				'value' => (int) $this->usermodel->countEventsWithSearch('', 'all'),
				'label' => 'community events',
				'link'  => "See what's on",
				'url'   => base_url('event'),
			);
		}
		if (site_setting('service_news_enabled', '1') !== '0') {
			$stats['news'] = array(
				'value' => (int) $this->usermodel->countNewsWithSearch(),
				'label' => 'local stories published',
				'link'  => 'Read the news',
				'url'   => base_url('news'),
			);
		}
		if (site_setting('service_videos_enabled', '1') !== '0') {
			$stats['videos'] = array(
				'value' => (int) $this->usermodel->countVideosWithSearch(),
				'label' => 'videos to watch',
				'link'  => 'Open the library',
				'url'   => base_url('videos'),
			);
		}
		$data['about_stats'] = $stats;

		// "from $N a month" read off the catalogue rather than typed into the
		// copy, so this page can't drift from what /pricing-plan charges.
		$data['lowest_plan_price'] = NULL;
		foreach ($this->usermodel->getActivePlans() as $plan) {
			$price = isset($plan->price) ? (float) $plan->price : 0;
			if ($price <= 0) continue;
			if ($data['lowest_plan_price'] === NULL || $price < $data['lowest_plan_price']) {
				$data['lowest_plan_price'] = $price;
			}
		}

		// Full-width editorial — no sidebar to hang a box off, so the three
		// wide positions and nothing else.
		$data['ad_slots'] = $this->pageAdSlots('wide');
		$data['page_css'] = $this->contentPageCss();

		$data['title']            = 'About Pearland411 — The Local Directory for Pearland, TX';
		$data['meta_description'] = 'Pearland411 is the local business directory and community hub for Pearland, '
			. 'Texas: ' . ($stats['listings']['value'] > 0 ? number_format($stats['listings']['value']) . ' businesses, ' : '')
			. 'events, news and deals in one place. Free to list your business.';
		$data['canonical_url']    = base_url('about-us');
		$data['og_image']         = base_url('assets/images/pearland-logo.png');

		$data['structured_data'] = array(
			'@context' => 'https://schema.org',
			'@graph'   => array(
				array(
					'@type'       => 'AboutPage',
					'url'         => $data['canonical_url'],
					'name'        => 'About Pearland411',
					'description' => $data['meta_description'],
					'breadcrumb'  => array(
						'@type'           => 'BreadcrumbList',
						'itemListElement' => array(
							array('@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => base_url()),
							array('@type' => 'ListItem', 'position' => 2, 'name' => 'About us', 'item' => $data['canonical_url']),
						),
					),
				),
				array(
					'@type'       => 'Organization',
					'name'        => 'Pearland411',
					'url'         => base_url(),
					'logo'        => base_url('assets/images/pearland-logo.png'),
					'description' => 'Local business directory and community hub for Pearland, Texas.',
					'areaServed'  => array('@type' => 'City', 'name' => 'Pearland', 'addressRegion' => 'TX'),
				),
			),
		);

		$this->loadHeaderCategories($data);

		$this->load->view('header', $data);
		$this->load->view('about', $data);
		$this->load->view('footer');
	}


	// FAQ Page (static, with header/footer)
	public function faq()
	{
		$this->load->model('usermodel');
		$data = array();
		$data['title'] = 'FAQ';
		// Advertising. Three wide zones; this page is full-width, with no column to hang a box off.
		$data['ad_slots'] = $this->pageAdSlots('wide');

		$this->load->view('header', $data);
		$this->load->view('faq');
		$this->load->view('footer');
	}


	// CONTACT Page (static, with header/footer)
	public function contact_us()
	{
		$data = array();
		$data['title'] = 'Contact Us';
		$data['meta_description'] = 'Call, email or message Pearland411 — the local business directory for Pearland, Texas.';
		$data['canonical_url']    = base_url('contact-us');
		$data['page_css'] = $this->contentPageCss();
		$this->loadHeaderCategories($data);
		// Advertising. Six zones — three wide, three down the sidebar.
		$data['ad_slots'] = $this->pageAdSlots('full');

		$this->load->view('header', $data);
		$this->load->view('contact-us', $data);
		$this->load->view('footer');
	}

	/**
	 * Handle the /contact-us form submission.
	 *
	 * The previous view had a JS handler that just alert()'d "thanks"
	 * and discarded the data — every contact message ever sent was
	 * silently lost. This action validates, persists into `enquiries`,
	 * notifies the admin address, acknowledges to the sender, and
	 * redirects back to /contact-us with a flash.
	 *
	 * Two forms post here: the contact page, and the callback panel on
	 * /pricing-plan (views/partials/sales_help.php). They are not the same
	 * enquiry. Someone leaving their number to buy a package is a sales lead;
	 * someone asking a question through /contact-us is not. Both used to be
	 * written with `enquiry_type = 'contact'`, so the admin list showed one
	 * undifferentiated pile and a package lead could sit unread among website
	 * questions. The posted `topic` (whitelisted below) now sets the type, and
	 * `department` records who it is for — Sales, Billing, Customer Service —
	 * instead of the named representative the panel used to ask for, most of
	 * whom no longer work here.
	 */
	public function contact_submit()
	{
		if ($this->input->method() !== 'post') {
			redirect('contact-us');
			return;
		}

		if (!$this->rate_limit->check_ip('contact_submit', 6, 3600)) {
			$this->rate_limit->reject(3600);
			return;
		}

		// Where to send the visitor afterwards. The callback panel on
		// /pricing-plan posts here too, and dumping someone on /contact-us
		// after they asked to be phoned back about a package loses their
		// place. Whitelisted — never redirect to a posted URL.
		$return_to = (string) $this->input->post('return_to');
		$back = in_array($return_to, array('pricing-plan', 'contact-us', 'post-your-ads'), true)
			? $return_to
			: 'contact-us';

		$this->form_validation->set_rules('fullName', 'Name',  'trim|required|min_length[2]');
		// Not `required`: the callback panel is aimed at people who would
		// rather be phoned than type an address. One of the two is enforced
		// below instead.
		$this->form_validation->set_rules('email',    'Email', 'trim|valid_email');
		$this->form_validation->set_rules('message',  'Message', 'trim|required|min_length[10]');

		if (!$this->form_validation->run()) {
			$this->session->set_flashdata('contact_error',
				strip_tags(validation_errors()) ?: 'Please complete the required fields.');
			redirect($back);
			return;
		}

		$name    = (string) $this->input->post('fullName');
		$email   = (string) $this->input->post('email');
		$phone   = (string) $this->input->post('phone');
		$subject = (string) $this->input->post('subject');
		$message = (string) $this->input->post('message');

		if (trim($email) === '' && trim($phone) === '') {
			$this->session->set_flashdata('contact_error',
				'Please leave either an email address or a phone number so we can reply.');
			redirect($back);
			return;
		}

		// Where it came from, so the sales team can see a callback request for
		// what it is rather than reading it as a general enquiry.
		$sources = array(
			'pricing-plan'  => 'Pricing plan — callback',
			'post-your-ads' => 'Advertise with us',
			'contact-us'    => 'Contact Us',
		);
		$source = $sources[$back];

		// Which inbox it belongs in. Whitelisted, not trusted: `topic` comes
		// off a hidden field, and the type column drives the admin filters.
		// A callback from the packages panel is a `package` lead; anything
		// else stays a `contact` message, which is what /contact-us posts.
		$topic = trim((string) $this->input->post('topic'));
		if (!in_array($topic, array('package', 'contact'), true)) {
			$topic = in_array($back, array('pricing-plan', 'post-your-ads'), true) ? 'package' : 'contact';
		}

		// Who it is for. Matched back against enquiry_departments() so the
		// column only ever holds a department we actually have.
		$department = normalise_enquiry_department($this->input->post('department'));

		$row = array(
			'enquiry_name'       => $name,
			'enquiry_email'      => $email,
			'enquiry_mobile'     => $phone,
			'enquiry_message'    => ($subject !== '' ? '[' . $subject . '] ' : '') . $message,
			'enquiry_source'     => $source,
			'enquiry_type'       => $topic,
			'enquiry_save'       => 0,
			'enquiry_created_at' => date('Y-m-d H:i:s'),
		);

		// `enquiries` predates the repo's migrations, so the column may not be
		// there yet on an install that hasn't run 0026. Losing the department
		// is survivable; losing the enquiry to a fatal is not — so when the
		// column is missing the department rides along in the message instead.
		if ($this->db->field_exists('enquiry_department', 'enquiries')) {
			$row['enquiry_department'] = $department;
		} else {
			$row['enquiry_message'] = 'For: ' . $department . "\n\n" . $row['enquiry_message'];
		}

		// Saved first, through the schema-tolerant insert add_enquiry uses.
		$saved = (bool) $this->usermodel->addEnquiry($row);

		$this->load->library('mailer');

		// Notify the admin address. A department with a mailbox of its own gets
		// it directly; everything else falls through to the site-wide address,
		// so leaving the department emails blank changes nothing. Reply-To is
		// the sender, so answering the alert answers them.
		$admin_sent = FALSE;
		$admin_to   = form_recipient_email(enquiry_department_email($department));
		if ($admin_to) {
			$alert = '<p>New ' . ($topic === 'package' ? 'package / advertising enquiry' : 'contact-form submission') . ':</p>'
				. '<ul>'
				. '<li>Department: ' . html_escape($department) . '</li>'
				. '<li>Name: '       . html_escape($name)       . '</li>'
				. '<li>Email: '      . html_escape($email)      . '</li>'
				. '<li>Phone: '      . html_escape($phone)      . '</li>'
				. '<li>Subject: '    . html_escape($subject)    . '</li>'
				. '<li>Page: '       . html_escape($source)     . '</li>'
				. '</ul>'
				. '<p>Message:</p><blockquote>' . nl2br(html_escape($message)) . '</blockquote>'
				. '<p>All enquiries: <a href="' . html_escape(base_url('admin/enquiries')) . '">' . html_escape(base_url('admin/enquiries')) . '</a></p>';
			$admin_sent = $this->mailer->send(
				$admin_to,
				'[Pearland411] ' . ($topic === 'package' ? 'Package enquiry' : 'Contact-Us message')
					. ' for ' . $department . ' from ' . $name,
				$this->mailer->render('New enquiry', $alert, array('preheader' => 'From ' . $name)),
				NULL,
				array('reply_to' => $email, 'reply_to_name' => $name, 'log_type' => 'contact')
			);
		}

		// Acknowledge to the sender — only when they left an address, and
		// only once we know the message is somewhere: saved, or delivered to
		// the team.
		if (trim($email) !== '' && ($saved || $admin_sent)) {
			$ack = '<p>Thanks — we received your message and passed it to '
				. html_escape($department) . '. Someone there will get back to you shortly.</p>'
				. '<p>For reference, you sent:</p>'
				. '<blockquote>' . nl2br(html_escape($message)) . '</blockquote>';
			$this->mailer->send($email, 'We received your message',
				$this->mailer->render('We received your message', $ack),
				NULL, array('log_type' => 'contact'));
		}

		// Both results used to be ignored, so the page said "Thanks" even when
		// the message had been neither stored nor sent. A failed email with a
		// saved row is still a success for the visitor (the team sees it at
		// /admin/enquiries, and the failure is in email_log); losing both is not.
		if (!$saved && !$admin_sent) {
			log_message('error', 'contact_submit: enquiry neither saved nor emailed (from ' . $email . ')');
			$this->session->set_flashdata('contact_error',
				'Sorry — your message could not be sent just now. Please try again in a few minutes, or call us.');
			redirect($back);
			return;
		}

		$thanks = array(
			'pricing-plan'  => 'Thanks! Someone from ' . $department . ' will call you back shortly.',
			'post-your-ads' => 'Thanks! Our sales team will contact you with placements and pricing within one business day.',
			'contact-us'    => 'Thanks! We passed this to ' . $department . ' and will be in touch soon.',
		);
		$this->session->set_flashdata('contact_success', $thanks[$back]);
		redirect($back);
	}


	// PRIVACY Page (static, with header/footer)
	public function privacy_policy()
	{
		$this->load->model('usermodel');
		$data = array();
		$data['title'] = 'Privacy Policy';
		// No advertising on this page. A page a payment processor, an app reviewer or a cautious customer opens
		// to check the site is legitimate. Advertising on it works against that.

		$this->load->view('header', $data);
		$this->load->view('privacy_policy');
		$this->load->view('footer');
	}


	// TERMS OF USE Page (static, with header/footer)
	public function terms_of_use()
	{
		$this->load->model('usermodel');
		$data = array();
		$data['title'] = 'Terms of Use';
		// No advertising on this page. See privacy_policy() — a trust page, not a browsing page.

		$this->load->view('header', $data);
		$this->load->view('terms_of_use');
		$this->load->view('footer');
	}


	// NEWS Page
	public function news()
	{
		if (!$this->requireService('service_news_enabled')) return;

		$search_query = $this->input->get('q', TRUE);
		$search_query = is_string($search_query) ? trim($search_query) : '';

		// Section filter. A query string rather than a path segment, so it
		// composes with ?q= and ?page= and can't collide with the routes.
		$cat_slug = $this->input->get('category', TRUE);
		$cat_slug = is_string($cat_slug) ? trim($cat_slug) : '';
		$cat_id   = $cat_slug !== '' ? $this->usermodel->resolveContentCategoryId($cat_slug, 'news_categories') : NULL;
		$cat_name = '';
		if ($cat_id !== NULL) {
			$cat_row  = $this->usermodel->getSingleValue($cat_id, 'category_id', 'news_categories');
			$cat_name = $cat_row ? (string) $cat_row->category_name : '';
		} elseif ($cat_slug !== '') {
			// Unknown slug: don't silently show everything under a heading
			// that says otherwise — filter to nothing and let the view
			// explain, the same as an empty category.
			$cat_id = 0;
		}

		$per_page = 12;
		$page = (int) $this->input->get('page', TRUE);
		if ($page < 1) $page = 1;

		$total_rows  = $this->usermodel->countNewsWithSearch($search_query, $cat_id);
		$total_pages = max(1, (int) ceil($total_rows / $per_page));
		if ($page > $total_pages) $page = $total_pages;
		$offset = ($page - 1) * $per_page;

		$all_news = $this->usermodel->getNewsWithSearch($search_query, $per_page, $offset, $cat_id);

		// Pagination config (reuse_query_string keeps ?q= and ?category=)
		$config = [
			'base_url' => base_url('news'),
			'total_rows' => $total_rows,
			'per_page' => $per_page,
			'page_query_string' => TRUE,
			'query_string_segment' => 'page',
			'reuse_query_string' => TRUE,
			'use_page_numbers' => TRUE,
			'attributes' => array('class' => 'page-link'),
			'full_tag_open' => '<ul class="pagination">',
			'full_tag_close' => '</ul>',
			'cur_tag_open' => '<li class="page-item active"><span class="page-link">',
			'cur_tag_close' => '</span></li>',
			'num_tag_open' => '<li class="page-item">',
			'num_tag_close' => '</li>',
			'prev_tag_open' => '<li class="page-item">',
			'prev_tag_close' => '</li>',
			'next_tag_open' => '<li class="page-item">',
			'next_tag_close' => '</li>',
			'first_tag_open' => '<li class="page-item">',
			'first_tag_close' => '</li>',
			'last_tag_open' => '<li class="page-item">',
			'last_tag_close' => '</li>',
			'next_link' => 'Next &rsaquo;',
			'prev_link' => '&lsaquo; Prev',
		];
		$this->pagination->initialize($config);

		$data = array();
		$data['all_news']         = $all_news;
		$data['pagination_links'] = $this->pagination->create_links();
		$data['search_query']     = $search_query;
		$data['total_news']       = $total_rows;
		$data['per_page']         = $per_page;
		$data['current_page']     = $page;
		$data['total_pages']      = $total_pages;
		$data['current_offset']   = $offset;

		$data['news_categories']        = $this->usermodel->getNewsCategoriesWithCounts(12);
		$data['current_category_slug']  = $cat_slug;
		$data['current_category_name']  = $cat_name;
		$data['popular_news']           = $this->usermodel->getPopularNews(5);

		if ($this->session->userdata('logged_user')) {
			$data['user'] = $this->usermodel->getUserInformation();
		}

		$data['ad_slots'] = $this->contentAdSlots();
		$this->usermodel->primeAdSlots(array_merge(
			array_values($data['ad_slots']),
			array('news-sidebar', 'right-banner-1', 'right-banner-2')
		));
		$data['page_css'] = $this->contentPageCss();

		$data['title'] = $cat_name !== ''
			? $cat_name . ' — Pearland News — Pearland411'
			: 'Pearland News — Local News & Announcements — Pearland411';
		$data['meta_description'] = 'Local news from Pearland, Texas: city announcements, business openings, '
			. 'public safety and community stories'
			. ($cat_name !== '' ? ' filed under ' . $cat_name . '.' : '.');
		$data['canonical_url'] = $cat_slug !== ''
			? base_url('news') . '?' . http_build_query(array('category' => $cat_slug))
			: base_url('news');

		$this->loadHeaderCategories($data);

		$this->load->view('header', $data);
		$this->load->view('news', $data);
		$this->load->view('footer');
	}



	/*===================
		Global Listing Details Page
		URL: /user/listing_details_global/slug
		This handles /listing-details/slug via routing
	===================*/
	/**
	 * Legacy alias for /listing-details/<slug>.
	 *
	 * It used to be a second, thinner copy of listing_details(): no reviews,
	 * no review stats, no owner socials, no SEO tags and no ad zones, so the
	 * same business rendered a visibly poorer page depending on which URL you
	 * arrived through — and the view had to guard every one of those missing
	 * variables. One code path now serves both.
	 */
	public function listing_details_global($slug)
	{
		$this->listing_details($slug);
	}

	/*Search Page*/
	public function search($slug = null)
	{
		try {
			$this->load->model('usermodel');

			// Get search query from URL slug, GET parameter, or POST
			$search_query = '';

			if (!empty($slug)) {
				// Convert slug back to search query
				$search_query = str_replace('-', ' ', $slug);
			} elseif ($this->input->get('q')) {
				$search_query = $this->input->get('q');
			} elseif ($this->input->post('search_query')) {
				$search_query = $this->input->post('search_query');
			}

			$search_query = trim($search_query);

			// Create URL-friendly slug
			$search_slug = url_title($search_query, 'dash', TRUE);

			$data = array();
			$data['search_query'] = $search_query;
			$data['search_slug'] = $search_slug;
			$data['search_results'] = array();
			$data['total_results'] = 0;

			// How many results per section. globalSearch() used to ignore its own
			// $limit and hard-code 20/15/10 per section, with no pager anywhere
			// on this page — so a query matching 200 businesses showed 20 and
			// there was no way to reach the 21st. ?show= raises the cap; the
			// model clamps it, so a hand-typed number cannot ask for the table.
			$data['search_limit'] = (int) ($this->input->get('show') ?: 20);

			if (!empty($search_query)) {
				// Get search results
				$data['search_results'] = $this->usermodel->globalSearch($search_query, $data['search_limit']);
				$data['total_results'] = isset($data['search_results']['total_count']) ? $data['search_results']['total_count'] : 0;
				$data['search_limit']  = isset($data['search_results']['limit']) ? (int) $data['search_results']['limit'] : 20;
				$data['search_has_more'] = !empty($data['search_results']['has_more']);
			}

			// Load additional data for the page
			try {
				// Business categories, busiest first, only ones with published
				// listings. This used to be getAllCategory() — MARKETPLACE
				// product_categories ("Laptop", "Simulator") — rendered as
				// /all_listing/<slug> links, which resolve against LISTING
				// categories, so every one of them opened "That category
				// doesn't exist" or an empty page.
				$data['categories'] = $this->usermodel->getCategoriesWithCounts(8);
				$data['latest_news'] = $this->usermodel->getlatest_news(5);
			} catch (Exception $e) {
				$data['categories'] = array();
				$data['latest_news'] = array();
			}

			// Set page title and meta
			$data['page_title'] = !empty($search_query) ? 'Search Results for "' . $search_query . '"' : 'Search';
			$data['meta_description'] = 'Search results for ' . $search_query . ' on Pearland411';

			// Load header, search page, and footer
			// Advertising. Six zones — three wide, three down the sidebar.
			// A query that matched nothing gets one house ad, not several.
			$data['ad_house_cap'] = $this->thinPageAdCap(
				isset($data['total_results']) ? $data['total_results'] : 0);
			$data['ad_slots'] = $this->pageAdSlots('full');
			// The sidebar's unsold zone falls back to banner_design_promo.php,
			// whose styling lives entirely in this sheet.
			$data['page_css'] = array('assets/css/promo-cards.css');

			$this->load->view('header', $data);
			$this->load->view('search', $data);
			$this->load->view('footer');
		} catch (Exception $e) {
			// If anything fails, show basic search page
			$data = array(
				'search_query' => isset($search_query) ? $search_query : '',
				'search_results' => array(),
				'total_results' => 0,
				'categories' => array(),
				'latest_news' => array(),
				'page_title' => 'Search',
				'meta_description' => 'Search Pearland411'
			);
			// Load header, search page, and footer
			$this->load->view('header', $data);
			$this->load->view('search', $data);
			$this->load->view('footer');
		}
	}

	/*AJAX Search Suggestions*/
	public function search_suggestions()
	{
		try {
			$this->load->model('usermodel');

			$search_term = $this->input->post('term');
			$suggestions = array();

			if (!empty($search_term) && strlen($search_term) >= 2) {
				$suggestions = $this->usermodel->getSearchSuggestions($search_term);
			}

			// Set proper JSON header and return data
			header('Content-Type: application/json');
			echo json_encode($suggestions ? $suggestions : array());
			exit;
		} catch (Exception $e) {
			// Return empty array on error
			header('Content-Type: application/json');
			echo json_encode(array());
			exit;
		}
	}

	/*Advanced Search with Filters*/
	public function advanced_search()
	{
		$this->load->model('usermodel');

		$data = array();
		$search_params = array(
			'query' => $this->input->get('q'),
			'category' => $this->input->get('category'),
			'type' => $this->input->get('type'),
			'location' => $this->input->get('location'),
			'date_from' => $this->input->get('date_from'),
			'date_to' => $this->input->get('date_to')
		);

		$data['search_params'] = $search_params;
		$data['search_results'] = array();
		$data['total_results'] = 0;
		$data['categories'] = $this->usermodel->getAllCategory();

		if (!empty($search_params['query']) || !empty($search_params['category']) || !empty($search_params['type'])) {
			$data['search_results'] = $this->usermodel->advancedSearch($search_params);
			$data['total_results'] = $data['search_results']['total_count'];
		}

		$this->load->view('advanced_search', $data);
	}


	public function user_events()
	{
		$data['logged_id'] = null;
		if ($this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$data['logged_id'] = $session['logged_id'];
		}

		// Subscription status and limits
		$data['user_subscription'] = $this->usermodel->getUserSubscription($data['logged_id']);
		$data['has_subscription'] = !empty($data['user_subscription']);

		if ($data['has_subscription']) {
			$data['subscription_limits'] = $this->usermodel->getSubscriptionLimits($data['logged_id']);
			$data['usage_counts'] = $this->usermodel->getAllUsageCounts($data['logged_id']);
			$data['can_add_event'] = $this->usermodel->canUserAddContent($data['logged_id'], 'event');
			$data['current_event_count'] = $this->usermodel->getUserUsage($data['logged_id'], 'event');
		} else {
			$data['subscription_limits'] = null;
			$data['usage_counts'] = [];
			$data['can_add_event'] = false;
			$data['current_event_count'] = 0;
		}

		// Get total count for pagination and progress bar
		$total_events = count($this->usermodel->getAllEvent($data['logged_id'], null, null));
		$data['total_rows'] = $total_events;

		// Check if user has unlimited plan
		$is_unlimited = false;
		if ($data['has_subscription'] && isset($data['subscription_limits']['event'])) {
			$event_limit = $data['subscription_limits']['event'];
			$is_unlimited = ($event_limit === -1 || $event_limit === 0);
		}

		// For unlimited plans, show more items per page to display all events
		$per_page = $is_unlimited ? max(50, $total_events) : 10;

		// Pagination config
		$config = [
			'base_url' => base_url('user/user-events'),
			'per_page' => $per_page,
			'total_rows' => $total_events,
			'uri_segment' => 3,
		];
		$config['full_tag_open'] = '<ul class="pagination">';
		$config['full_tag_close'] = '</ul>';
		$config['cur_tag_open'] = '<li class="page-item active"><a href="#" class="page-link">';
		$config['cur_tag_close'] = '</a></li>';
		$config['prev_tag_open'] = '<li class="page-link">';
		$config['prev_tag_close'] = '</li>';
		$config['next_tag_open'] = '<li class="page-link">';
		$config['next_tag_close'] = '</li>';
		$config['num_tag_open'] = '<li class="page-link">';
		$config['num_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li class="page-link">';
		$config['last_tag_close'] = '</li>';
		$config['first_tag_open'] = '<li class="page-link">';
		$config['first_tag_close'] = '</li>';
		$config['next_link'] = 'Next >';
		$config['prev_link'] = '< Prev';

		$this->pagination->initialize($config);
		$page = (int) ($this->uri->segment(3)) ? (int) ($this->uri->segment(3)) : 0;

		// Events for this user
		$data['allEvents'] = $this->usermodel->getAllEvent($data['logged_id'], $config['per_page'], $page);
		// Add views count for each event
		if (!empty($data['allEvents'])) {
			foreach ($data['allEvents'] as $key => $event) {
				$data['allEvents'][$key]->views = isset($event->views) ? $event->views : 0;
			}
		}
		$data['start_number'] = $page + 1;
		$data['per_page'] = $config['per_page'];
		$data['link'] = $this->pagination->create_links();

		$data['title'] = 'My Events';

		if (file_exists(APPPATH . 'views/user_events.php')) {
			$this->load->view('header', $data);
			$this->load->view('user_left_sidebar');
			$this->load->view('user_events', $data);
			$this->load->view('user_right_sidebar');
			$this->load->view('footer');
		} else {
			$this->load->view('header', $data);
			$this->load->view('user_left_sidebar');
			echo '<div class="container"><h2>Events</h2><p>No event view template found (expected user_events.php).</p></div>';
			$this->load->view('user_right_sidebar');
			$this->load->view('footer');
		}
	}

	/**
	 * User Blog Posts Page (similar to user_events)
	 * URL: /user/blog-list
	 */
	public function user_blog_posts()
	{
		$data['logged_id'] = null;
		if ($this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$data['logged_id'] = $session['logged_id'];
		}

		// Subscription status and limits
		$data['user_subscription'] = $this->usermodel->getUserSubscription($data['logged_id']);
		$data['has_subscription'] = !empty($data['user_subscription']);

		if ($data['has_subscription']) {
			$data['subscription_limits'] = $this->usermodel->getSubscriptionLimits($data['logged_id']);
			$data['usage_counts'] = $this->usermodel->getAllUsageCounts($data['logged_id']);
			$data['can_add_blog'] = $this->usermodel->canUserAddContent($data['logged_id'], 'blog');
			$data['current_blog_count'] = $this->usermodel->getUserUsage($data['logged_id'], 'blog');
		} else {
			$data['subscription_limits'] = null;
			$data['usage_counts'] = [];
			$data['can_add_blog'] = false;
			$data['current_blog_count'] = 0;
		}

		// Get total count for pagination and progress bar
		$total_blogs = count($this->usermodel->getAllBlogs($data['logged_id'], null, null));
		$data['total_rows'] = $total_blogs;

		// Check if user has unlimited plan
		$is_unlimited = false;
		if ($data['has_subscription'] && isset($data['subscription_limits']['blog'])) {
			$blog_limit = $data['subscription_limits']['blog'];
			$is_unlimited = ($blog_limit === -1 || $blog_limit === 0);
		}

		// For unlimited plans, show more items per page to display all blogs
		$per_page = $is_unlimited ? max(50, $total_blogs) : 10;

		// Pagination config
		$config = [
			'base_url' => base_url('user/blog-post'),
			'per_page' => $per_page,
			'total_rows' => $total_blogs,
			'uri_segment' => 3,
		];
		$config['full_tag_open'] = '<ul class="pagination">';
		$config['full_tag_close'] = '</ul>';
		$config['cur_tag_open'] = '<li class="page-item active"><a href="#" class="page-link">';
		$config['cur_tag_close'] = '</a></li>';
		$config['prev_tag_open'] = '<li class="page-link">';
		$config['prev_tag_close'] = '</li>';
		$config['next_tag_open'] = '<li class="page-link">';
		$config['next_tag_close'] = '</li>';
		$config['num_tag_open'] = '<li class="page-link">';
		$config['num_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li class="page-link">';
		$config['last_tag_close'] = '</li>';
		$config['first_tag_open'] = '<li class="page-link">';
		$config['first_tag_close'] = '</li>';
		$config['next_link'] = 'Next >';
		$config['prev_link'] = '< Prev';

		$this->pagination->initialize($config);
		$page = (int) ($this->uri->segment(3)) ? (int) ($this->uri->segment(3)) : 0;

		// Blogs for this user
		$data['allBlogs'] = $this->usermodel->getAllBlogs($data['logged_id'], $config['per_page'], $page);
		// Add views count for each blog
		if (!empty($data['allBlogs'])) {
			foreach ($data['allBlogs'] as $key => $blog) {
				$data['allBlogs'][$key]->views = isset($blog->views) ? $blog->views : 0;
			}
		}
		$data['start_number'] = $page + 1;
		$data['per_page'] = $config['per_page'];
		$data['link'] = $this->pagination->create_links();

		$data['title'] = 'My Blog Posts';

		if (file_exists(APPPATH . 'views/user_blog_posts.php')) {
			$this->load->view('header', $data);
			$this->load->view('user_left_sidebar');
			$this->load->view('user_blog_posts', $data);
			$this->load->view('user_right_sidebar');
			$this->load->view('footer');
		} else {
			$this->load->view('header', $data);
			$this->load->view('user_left_sidebar');
			echo '<div class="container"><h2>Blog Posts</h2><p>No blog posts view template found (expected user_blog_posts.php).</p></div>';
			$this->load->view('user_right_sidebar');
			$this->load->view('footer');
		}
	}

	/**
	 * Blog Post - Main URL for user blog posts
	 * URL: /user/blog-post
	 */
	public function blog_post()
	{
		$this->user_blog_posts();
	}

	/**
	 * Blog List - Alternative URL for user_blog_posts
	 * URL: /user/blog_list or /user/blog-list  
	 */
	public function blog_list()
	{
		$this->user_blog_posts();
	}

	/**
	 * Add Blog - Create new blog post
	 * URL: /user/add-blog
	 */
	public function add_blog()
	{
		// Check if user is logged in
		if (!$this->session->userdata('logged_user')) {
			redirect('user/login');
			return;
		}

		$session = $this->session->userdata('logged_user');
		$logged_id = $session['logged_id'];

		// Check subscription limits before allowing to add blog
		if (!$this->usermodel->canUserAddContent($logged_id, 'blog')) {
			$this->session->set_flashdata('message', 'You have reached your blog limit. Please upgrade your plan to add more blog posts.');
			redirect('user/blog-post');
			return;
		}

		// Handle form submission
		if (isset($_POST['blog_submit'])) {
			// Basic validation
			$required_fields = ['blog_name', 'blog_description'];
			$validation_errors = [];

			foreach ($required_fields as $field) {
				if (empty($_POST[$field])) {
					$validation_errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
				}
			}

			if (!empty($validation_errors)) {
				$this->session->set_flashdata('message', implode('. ', $validation_errors));
				redirect('user/add_blog');
				return;
			}

			// Generate slug
			$blog_name = $_POST['blog_name'];
			$blog_slug = pearland_slug($blog_name . '-' . time());

			// Handle image upload
			$blog_image = '';
			if ($_FILES && $_FILES['blog_image']['name']) {
				$img_info = [
					'upload_path' => 'images/blogs',
					'quality'     => '60%',
					'height'      => 500,
					'width'       => 500,
					'image_name'  => 'blog_image'
				];
				$blog_image = uploadAndResize($img_info);
			}

			// Prepare data
			$blog_data = array(
				'user_id' => $logged_id,
				'blog_name' => $_POST['blog_name'],
				'blog_description' => $_POST['blog_description'],
				'blog_image' => $blog_image,
				'blog_status' => 1, // Active by default
				'blog_slug' => $blog_slug,
				'isenquiry' => isset($_POST['isenquiry']) ? 1 : 0,
				'created_at' => date('Y-m-d H:i:s')
			);

			// Insert blog
			if ($this->db->insert('blogs', $blog_data)) {
				$this->session->set_flashdata('message', 'Blog post added successfully!');
				redirect('user/blog-post');
			} else {
				$this->session->set_flashdata('message', 'Failed to add blog post.');
				redirect('user/add_blog');
			}
		}

		// Load view
		$data['title'] = 'Add New Blog Post';
		$data['logged_id'] = $logged_id;

		$this->load->view('header', $data);
		$this->load->view('user_add_new_blog', $data);
		$this->load->view('footer');
	}



	/**
	 * Edit Blog - Edit existing blog post
	 * URL: /user/edit-blog/{blog_slug}
	 */
	public function edit_blog($blog_slug = null)
	{
		// Check if user is logged in
		if (!$this->session->userdata('logged_user')) {
			redirect('user/login');
			return;
		}

		if (!$blog_slug) {
			$this->session->set_flashdata('message', 'Blog not found.');
			redirect('user/blog-post');
			return;
		}

		$session = $this->session->userdata('logged_user');
		$logged_id = $session['logged_id'];

		// Get blog details
		$blog = $this->usermodel->getSingleValue($blog_slug, 'blog_slug', 'blogs');
		if (!$blog || $blog->user_id != $logged_id) {
			$this->session->set_flashdata('message', 'Blog not found or access denied.');
			redirect('user/blog-post');
			return;
		}

		// Handle form submission
		if (isset($_POST['blog_submit'])) {
			$info = array(
				'blog_name' => $_POST['blog_name'],
				'blog_description' => $_POST['blog_description'],
				'isenquiry' => isset($_POST['isenquiry']) ? 1 : 0
			);

			// Handle image upload
			if ($_FILES && $_FILES['blog_image']['name']) {
				$img_info = [
					'upload_path' => 'images/blogs',
					'quality'     => '60%',
					'height'      => 500,
					'width'       => 500,
					'image_name'  => 'blog_image'
				];
				$info['blog_image'] = uploadAndResize($img_info);
			}

			// Update blog
			$update_result = $this->usermodel->update($blog->blog_id, $info, 'blog_id', 'blogs');

			if ($update_result) {
				$this->session->set_flashdata('message', 'Blog updated successfully!');
				redirect('user/blog-post');
			} else {
				$this->session->set_flashdata('message', 'Update failed!');
				redirect('user/edit_blog/' . $blog_slug);
			}
		}

		// Load view
		$data['blog'] = $blog;
		$data['title'] = 'Edit Blog Post';

		// Load header categories using professional MVC approach
		$this->loadHeaderCategories($data);

		$this->load->view('header', $data);
		$this->load->view('user_edit_blog', $data);
		$this->load->view('footer');
	}

	/**
	 * Blog Details - View blog post details
	 * URL: /user/blog-details/{blog_slug}
	 */
	public function blog_details($blog_slug = null)
	{
		if (!$this->requireService('service_blog_enabled')) return;
		if (!$blog_slug) {
			show_404();
			return;
		}

		// Get blog details
		$data['blog'] = $this->usermodel->getSingleValue($blog_slug, 'blog_slug', 'blogs');
		// Increment views for this blog
		if ($data['blog']) {
			$this->usermodel->incrementViews('blogs', 'blog_id', $data['blog']->blog_id);
		}

		if (!$data['blog'] || $data['blog']->blog_status != 1) {
			show_404();
			return;
		}

		// Get logged user information if available
		$data['logged_id'] = null;
		$data['user'] = null;
		if ($this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$data['logged_id'] = $session['logged_id'];
			$data['user'] = $this->usermodel->getUserInformation();
		}

		$data['title'] = $data['blog']->blog_name . " | Blog Details";

		// Generate share URLs dynamically for this blog
		$blog_url = base_url('user/blog-details/' . $data['blog']->blog_slug);
		$blog_name = $data['blog']->blog_name;
		$blog_image = !empty($data['blog']->blog_image) ? base_url($data['blog']->blog_image) : '';
		$data['share_urls'] = [
			'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($blog_url) . '&quote=' . urlencode($blog_name),
			'twitter' => 'https://twitter.com/share?text=' . urlencode($blog_name) . '&url=' . urlencode($blog_url),
			'whatsapp' => 'https://wa.me/?text=' . urlencode($blog_name . ' ' . $blog_url),
			'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode($blog_url),
			'pinterest' => 'https://www.pinterest.com/pin/create/bookmarklet/?media=' . urlencode($blog_image) . '&url=' . urlencode($blog_url) . '&description=' . urlencode($blog_name),
		];

		// Get 3 related blogs (excluding current)
		$data['related_blogs'] = $this->usermodel->getRelatedBlogs($data['blog']->blog_id, 3);

		// Author. This was a raw `$this->db->select('name')` inside the view —
		// a query in the template, and one that blew up whenever the loader had
		// not copied `db` across. Resolved here, where the model belongs.
		// A byline typed at /admin (migration 0035) wins over the account name:
		// posts are often filed under a staff login whose name is not the one
		// the client wants on the article.
		$data['blog_author'] = trim((string) ($data['blog']->blog_byline ?? ''));
		if ($data['blog_author'] === '' && !empty($data['blog']->user_id)) {
			$author = $this->usermodel->getSingleValue((int) $data['blog']->user_id, 'user_id', 'user');
			if ($author && !empty($author->name)) {
				$data['blog_author'] = (string) $author->name;
			}
		}

		$data['ad_slots'] = $this->contentAdSlots();
		$data['page_css'] = $this->contentPageCss();

		// SEO: per-page meta + BlogPosting JSON-LD. Prefer admin-set
		// seo_* columns; fall back to trimmed body.
		$blog = $data['blog'];
		$desc_auto = trim(strip_tags((string) ($blog->blog_description ?? '')));
		$desc_auto = $desc_auto !== '' ? mb_substr($desc_auto, 0, 160) : ('Blog post: ' . $blog->blog_name);
		$data['title']            = !empty($blog->seo_title)
			? $blog->seo_title
			: ($blog->blog_name . ' — Pearland411');
		$data['meta_description'] = !empty($blog->seo_description)
			? $blog->seo_description
			: $desc_auto;
		$data['meta_keywords']    = !empty($blog->seo_keywords)
			? $blog->seo_keywords
			: '';
		$data['canonical_url']    = $blog_url;
		if (!empty($blog->blog_image)) $data['og_image'] = $blog_image;

		$data['json_ld'] = json_ld(array(
			'@context'         => 'https://schema.org',
			'@type'            => 'BlogPosting',
			'headline'         => (string) $blog->blog_name,
			'datePublished'    => !empty($blog->created_at) ? date('c', strtotime($blog->created_at)) : null,
			'image'            => $blog_image ?: null,
			'mainEntityOfPage' => $blog_url,
			'publisher'        => array(
				'@type' => 'Organization',
				'name'  => 'Pearland411',
				'logo'  => array('@type' => 'ImageObject', 'url' => base_url('assets/images/pearland-logo.png')),
			),
		));

		// Load views
		$this->load->view('header', $data);
		$this->load->view('blog_details', $data);
		$this->load->view('footer');
	}

	// Enquery Page

	/**
	 * All User Enquiries Page (subscription-aware, paginated, like user_events)
	 */
	public function enquiries()
	{
		$data['logged_id'] = null;
		if ($this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$data['logged_id'] = $session['logged_id'];
		}

		// If no logged user, redirect to login
		if (!$data['logged_id']) {
			redirect('login');
			return;
		}

		// Subscription status and limits
		$data['user_subscription'] = $this->usermodel->getUserSubscription($data['logged_id']);
		$data['has_subscription'] = !empty($data['user_subscription']);

		if ($data['has_subscription']) {
			$data['subscription_limits'] = $this->usermodel->getSubscriptionLimits($data['logged_id']);
			$data['usage_counts'] = $this->usermodel->getAllUsageCounts($data['logged_id']);
			$data['can_add_enquiry'] = $this->usermodel->canUserAddContent($data['logged_id'], 'enquiry');
			$data['current_enquiry_count'] = $this->usermodel->getUserUsage($data['logged_id'], 'enquiry');
		} else {
			$data['subscription_limits'] = null;
			$data['usage_counts'] = [];
			$data['can_add_enquiry'] = false;
			$data['current_enquiry_count'] = 0;
		}

		// Only this member's leads. This used getAllEnquiries() ("showing all
		// for now"), which put every business's enquiries — names, emails,
		// phone numbers, messages — in front of every signed-in member.
		$total_rows = $this->usermodel->countEnquiriesForReceiver($data['logged_id']);
		$data['total_rows'] = $total_rows;

		// Pagination config. The base URL used to be user/user-enquiries,
		// which is a different method, so page 2 never loaded this one.
		$config = [
			'base_url' => base_url('user/enquiries'),
			'per_page' => 10,
			'total_rows' => $total_rows,
			'uri_segment' => 3,
		];
		$config['full_tag_open'] = '<ul class="pagination">';
		$config['full_tag_close'] = '</ul>';
		$config['cur_tag_open'] = '<li class="page-item active"><a href="#" class="page-link">';
		$config['cur_tag_close'] = '</a></li>';
		$config['prev_tag_open'] = '<li class="page-link">';
		$config['prev_tag_close'] = '</li>';
		$config['next_tag_open'] = '<li class="page-link">';
		$config['next_tag_close'] = '</li>';
		$config['num_tag_open'] = '<li class="page-link">';
		$config['num_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li class="page-link">';
		$config['last_tag_close'] = '</li>';
		$config['first_tag_open'] = '<li class="page-link">';
		$config['first_tag_close'] = '</li>';
		$config['next_link'] = 'Next >';
		$config['prev_link'] = '< Prev';

		$this->pagination->initialize($config);
		$page = (int) ($this->uri->segment(3)) ? (int) ($this->uri->segment(3)) : 0;

		$data['allEnquiries'] = $this->usermodel->getEnquiriesForReceiver($data['logged_id'], $config['per_page'], $page);
		$data['start_number'] = $page + 1;
		$data['per_page'] = $config['per_page'];
		$data['link'] = $this->pagination->create_links();

		$data['title'] = 'My Enquiries';

		if (file_exists(APPPATH . 'views/user_enquiry.php')) {
			$this->load->view('header', $data);
			$this->load->view('user_left_sidebar');
			$this->load->view('user_enquiry', $data);
			$this->load->view('user_right_sidebar');
			$this->load->view('footer');
		} else {
			$this->load->view('header', $data);
			$this->load->view('user_left_sidebar');
			echo '<div class="container">
			<h2>Enquiries</h2>
			<p>No enquiry view template found (expected user_enquiry.php).</p>
		</div>';
			$this->load->view('user_right_sidebar');
			$this->load->view('footer');
		}
	}

	/**
	 * Delete one of the member's own enquiries.
	 *
	 * POST only (the CSRF hook covers every POST form) and owner-scoped: this
	 * used to be a GET link that deleted any enquiry by id, so any signed-in
	 * member could clear any business's leads — or a crafted <img> could do
	 * it for them.
	 */
	public function delete_enquiry($enquiry_id = null)
	{
		if ($this->input->method() !== 'post') {
			show_404();
			return;
		}

		$session   = (array) $this->session->userdata('logged_user');
		$logged_id = isset($session['logged_id']) ? (int) $session['logged_id'] : 0;
		if (!$logged_id) {
			$this->session->set_flashdata('error_msg', 'Please login to continue.');
			redirect('login');
			return;
		}

		$enquiry_id = (int) $enquiry_id;
		$enquiry    = $enquiry_id > 0 ? $this->usermodel->getEnquiryForReceiver($enquiry_id, $logged_id) : NULL;
		if (!$enquiry) {
			// Same answer for "no such enquiry" and "not yours".
			$this->session->set_flashdata('error_msg', 'Enquiry not found.');
			redirect('user/enquiries');
			return;
		}

		if ($this->usermodel->deleteEnquirySimple((int) $enquiry->enquiry_id)) {
			$this->session->set_flashdata('success_msg', 'Enquiry #' . $enquiry_id . ' deleted successfully.');
		} else {
			$this->session->set_flashdata('error_msg', 'Failed to delete enquiry #' . $enquiry_id . '. Please try again.');
		}

		redirect('user/enquiries');
	}

	/*All Product*/

	public function all_products()
	{
		if (!$this->requireService('service_products_enabled')) return;
		if ($this->session->userdata('logged_user')) {
			$data['user'] = $this->usermodel->getUserInformation();
		}

		$data['categories'] = $this->usermodel->getAllCategory();

		// Parse filters from GET
		$filters = array();
		$category_slug = $this->input->get('category');
		if (!empty($category_slug)) {
			$filters['category_slug'] = $category_slug;
		}
		$subcat = $this->input->get('sub_category_id');
		if (!empty($subcat)) {
			$filters['sub_category_id'] = is_array($subcat) ? $subcat : array($subcat);
		}
		$price = $this->input->get('price');
		if (!empty($price)) {
			if (strpos($price, '+') !== false) {
				$min = (int) filter_var($price, FILTER_SANITIZE_NUMBER_INT);
				$filters['price_range'] = array($min, 9999999);
			} elseif (strpos($price, '-') !== false) {
				list($min, $max) = explode('-', $price);
				$filters['price_range'] = array((int)$min, (int)$max);
			}
		}
		$discount = $this->input->get('discount');
		if (!empty($discount)) {
			if (strpos($discount, '+') !== false) {
				$min = (int) filter_var($discount, FILTER_SANITIZE_NUMBER_INT);
				$filters['discount_range'] = array($min, 100);
			} elseif (strpos($discount, '-') !== false) {
				list($min, $max) = explode('-', $discount);
				$filters['discount_range'] = array((int)$min, (int)$max);
			}
		}

		// Pagination setup
		$this->load->library('pagination');
		$per_page = 12;
		$page = (int) $this->input->get('page', TRUE);
		if ($page < 1) $page = 1;
		$offset = ($page - 1) * $per_page;

		// Get subcategories for selected category (or all if none selected)
		if (!empty($category_slug)) {
			$data['subcategories'] = $this->usermodel->getAllSubCategories($category_slug);
		} else {
			$data['subcategories'] = $this->usermodel->getAllSubCategories();
		}

		// Get total rows and paginated products
		$hasFilter = !empty($filters['category_slug']) || !empty($filters['sub_category_id']) || isset($filters['price_range']) || isset($filters['discount_range']);
		if ($hasFilter) {
			$total_rows = $this->usermodel->countProductsWithFilters($filters);
			$data['AllProduct'] = $this->usermodel->getFilteredProductsDynamic($filters, $per_page, $offset);
		} else {
			$total_rows = $this->usermodel->countAllProducts();
			$data['AllProduct'] = $this->usermodel->getAllProduct($per_page, $offset);
		}

		// Pagination config (same as news)
		$config = [
			'base_url' => base_url('all-products'),
			'total_rows' => $total_rows,
			'per_page' => $per_page,
			'page_query_string' => TRUE,
			'query_string_segment' => 'page',
			'reuse_query_string' => TRUE,
			'use_page_numbers' => TRUE,
			'full_tag_open' => '<ul class="pagination">',
			'full_tag_close' => '</ul>',
			'cur_tag_open' => '<li class="page-item active"><span class="page-link">',
			'cur_tag_close' => '</span></li>',
			'num_tag_open' => '<li class="page-item">',
			'num_tag_close' => '</li>',
			'prev_tag_open' => '<li class="page-item">',
			'prev_tag_close' => '</li>',
			'next_tag_open' => '<li class="page-item">',
			'next_tag_close' => '</li>',
			'first_tag_open' => '<li class="page-item">',
			'first_tag_close' => '</li>',
			'last_tag_open' => '<li class="page-item">',
			'last_tag_close' => '</li>',
			'next_link' => 'Next >',
			'prev_link' => '< Prev',
		];
		$this->pagination->initialize($config);
		$data['pagination_links'] = $this->pagination->create_links();

		$data['title'] = "Pearland | All Products";
		$this->loadHeaderCategories($data);
		// Advertising. Six zones — three wide, three down the sidebar.
		$data['ad_slots'] = $this->pageAdSlots('full');

		$this->load->view('header', $data);
		$this->load->view('all-products', $data);
		$this->load->view('footer');
	}

	/*===================
		Product Details Page
		URL: /user/product_details/product_id
	===================*/
	public function product_details($product_slug = null)
	{
		if (!$this->requireService('service_products_enabled')) return;
		// Check if product_slug is provided
		if (!$product_slug) {
			show_404();
			return;
		}

		// Get product details by slug
		$data['product'] = $this->usermodel->getSingleProduct($product_slug);
		// Increment views for this product
		if ($data['product']) {
			$this->usermodel->incrementViews('products', 'product_id', $data['product']->product_id);
		}

		if (!$data['product']) {
			show_404();
			return;
		}

		// Get logged user information if available
		$data['user'] = null;
		if ($this->session->userdata('logged_user')) {
			$data['user'] = $this->usermodel->getUserInformation();
		}

		// Get related products (same category)
		$data['related_products'] = array();
		if (!empty($data['product']->category_id)) {
			$data['related_products'] = $this->usermodel->getRelatedProducts($data['product']->category_id, $product_slug, 3);
		}

		$data['title'] = $data['product']->product_name . " | Product Details";

		// Load views
		// Advertising. Six zones — three wide, three down the sidebar.
		$data['ad_slots'] = $this->pageAdSlots('full');

		$this->load->view('header', $data);
		$this->load->view('product_details', $data);
		$this->load->view('footer');
	}





	/*All Category*/

	public function all_category($offset = 0)
	{
		// $offset is accepted and ignored: the index is not paginated any more
		// (see below), but /user/all_category/20 links are in the wild and must
		// not 404.
		if ($this->session->userdata('logged_user')) {

			$data['user'] = $this->usermodel->getUserInformation();
		}

		// The search runs in SQL, for visitors without JavaScript and for
		// shareable ?q= links. The view used to filter the twenty rows of the
		// current page in PHP, so a category that lived on page 3 came back as
		// "no categories found".
		$search = trim((string) $this->input->get('q', TRUE));
		$data['search_query'] = $search;

		// ONE query, the WHOLE index, no pagination. A category list is a
		// lookup table, not a feed: paged, a visitor hunting for "Plumbers"
		// had to guess which of eight pages held it, and find-in-page could
		// only ever see a twentieth of the list. The view renders all of it,
		// groups it A-Z and filters in the browser.
		$categories = $this->usermodel->getAllCategoriesWithCounts($search);
		$data['AllCategory']      = $categories;
		$data['total_categories'] = count($categories);

		// Split once here rather than twice in the view: the busiest dozen get
		// picture tiles, everything else goes in the A-Z index.
		$live = array();
		foreach ($categories as $category) {
			if ((int) ($category->listing_count ?? 0) > 0) $live[] = $category;
		}
		$data['live_categories'] = count($live);
		$data['popular_categories'] = array_slice($live, 0, 12);

		// A-Z buckets, in name order inside each letter. getAllCategoriesWithCounts()
		// returns busiest-first, which is what the tiles want and not what an
		// index wants.
		$by_letter = array();
		foreach ($categories as $category) {
			$name   = trim((string) $category->category_name);
			$letter = strtoupper(substr($name, 0, 1));
			if ($letter === '' || !preg_match('/[A-Z]/', $letter)) $letter = '#';
			$by_letter[$letter][] = $category;
		}
		ksort($by_letter);
		foreach ($by_letter as $letter => $rows) {
			usort($rows, function ($a, $b) {
				return strcasecmp((string) $a->category_name, (string) $b->category_name);
			});
			$by_letter[$letter] = $rows;
		}
		// '#' collects digits and punctuation; it belongs after Z, not before A.
		if (isset($by_letter['#'])) {
			$hash = $by_letter['#'];
			unset($by_letter['#']);
			$by_letter['#'] = $hash;
		}
		$data['categories_by_letter'] = $by_letter;

		$data['total_listings'] = (int) $this->usermodel->countListingsByCategory(NULL);

		$data['ad_house_cap'] = $this->thinPageAdCap(count($by_letter));

		// Same six zones as the directory and the content pages.
		$data['ad_slots'] = array(
			'top'      => 'top-banner-site-wide',
			'inline'   => 'middle-banner-category',
			'side_1'   => 'right-side-banner-1',
			'side_2'   => 'right-side-banner-2',
			'side_sky' => 'right-panel-skyscraper',
			'bottom'   => 'bottom-banner-site-wide',
		);
		$this->usermodel->primeAdSlots(array_values($data['ad_slots']));

		$data['page_css'] = $this->contentPageCss();

		$data['title']            = 'All Business Categories in Pearland, TX | Pearland411';
		$data['meta_description'] = 'Browse every business category on Pearland411 — home services, healthcare, '
			. 'restaurants, automotive, professional services and more, with the number of local businesses in each.';
		$data['canonical_url']    = base_url('all-category');

		// Load header categories using professional MVC approach
		$this->loadHeaderCategories($data);

		$this->load->view('header', $data);

		$this->load->view('all-category', $data);

		$this->load->view('footer');
	}

	/*Category Search*/
	public function category_search()
	{
		if ($this->session->userdata('logged_user')) {
			$data['user'] = $this->usermodel->getUserInformation();
		}

		// Get search query
		$search_query = $this->input->get('q');
		$search_query = $search_query ? trim($search_query) : '';

		// Initialize data array
		$data['search_query'] = $search_query;
		$data['title'] = !empty($search_query) ? 'Search Results for "' . $search_query . '" - Pearland' : 'Category Search - Pearland';

		if (!empty($search_query)) {
			// Search categories using existing searchCategories method (with proper limit)
			$data['categories'] = $this->usermodel->searchCategories($search_query, 50);
		} else {
			$data['categories'] = array();
		}

		// Load header categories using professional MVC approach
		$this->loadHeaderCategories($data);

		// Load the dedicated search results view
		$this->load->view('header', $data);
		$this->load->view('all-category-search', $data);
		$this->load->view('footer');
	}

	/*All Category Listings*/

	public function all_listing($cat_identifier = Null, $offset = 0)
	{
		// Simple URL parsing: 
		// /user/all_listing -> no category, offset = 0
		// /user/all_listing/10 -> no category, offset = 10  
		// /user/all_listing/category_slug -> category = slug, offset = 0
		// /user/all_listing/category_slug/10 -> category = slug, offset = 10

		$cat_slug = null;
		$current_offset = (int)$offset;

		if ($cat_identifier !== null) {
			// If first parameter is numeric and looks like offset (divisible by 10, >= 0)
			if (is_numeric($cat_identifier) && $cat_identifier >= 0 && ($cat_identifier == 0 || $cat_identifier % 10 == 0)) {
				// Treat as offset, no category
				$cat_slug = null;
				$current_offset = (int)$cat_identifier;
			} else {
				// Treat as category
				if (is_numeric($cat_identifier)) {
					// Try to get slug from ID
					$cat_slug = $this->usermodel->getCategorySlugById($cat_identifier);
					if (!$cat_slug) {
						$cat_slug = $cat_identifier; // fallback to ID
					}
				} else {
					// Already a slug
					$cat_slug = $cat_identifier;
				}
			}
		}

		$data['allCategories']  = $this->usermodel->getAllListingCategory();

		if ($this->session->userdata('logged_user')) {
			$data['user'] = $this->usermodel->getUserInformation();
		}

		// Sort is a query string, not a URI segment, so it can't collide with
		// the category/offset segments parsed above.
		$sort = (string) $this->input->get('sort');
		if (!in_array($sort, array('featured', 'newest', 'name', 'rating'), true)) {
			$sort = 'featured';
		}

		// Manual pagination. The URL shape is positional and long-standing:
		//   /all_listing                    /all_listing/20
		//   /all_listing/<category>         /all_listing/<category>/20
		$per_page       = 10;
		$total_listings = $this->usermodel->countListingsByCategory($cat_slug);
		$total_pages    = (int) ceil($total_listings / $per_page);

		/*
		 * `?page=N` is an alternative way in, and the one the "go to page" box
		 * under the pager posts to. The pager offers the current page plus two
		 * either side, then first and last — so from page 1 of 47 the only
		 * numbers on screen are 1, 2, 3 and 47, and reaching page 20 meant
		 * walking there two at a time. That is the reported "I don't want it to
		 * take me 17 clicks to find page 20".
		 *
		 * The positional /all_listing/<category>/<offset> URLs are untouched:
		 * every existing link, bookmark and indexed URL still resolves, and the
		 * pager still emits them so nothing about the page's canonical shape
		 * changes.
		 */
		$page_param = (int) $this->input->get('page');
		if ($page_param > 0) {
			$current_offset = ($page_param - 1) * $per_page;
		}

		// Clamp: a hand-typed offset past the end used to render an empty
		// results list with no explanation. Snap back to the last real page.
		if ($total_listings > 0 && $current_offset >= $total_listings) {
			$current_offset = max(0, ($total_pages - 1) * $per_page);
		}
		$current_page = (int) floor($current_offset / $per_page) + 1;

		$query_suffix = ($sort !== 'featured') ? '?sort=' . $sort : '';
		$page_url = function ($offset) use ($cat_slug, $query_suffix) {
			$path = 'all_listing';
			if ($cat_slug)   $path .= '/' . rawurlencode($cat_slug);
			if ($offset > 0) $path .= '/' . (int) $offset;
			return base_url($path) . $query_suffix;
		};

		$pagination_html = '';
		if ($total_pages > 1) {
			$pagination_html = '<ul class="pagination justify-content-center">';

			if ($current_page > 1) {
				$pagination_html .= '<li class="page-item"><a class="page-link" href="'
					. html_escape($page_url(($current_page - 2) * $per_page)) . '" rel="prev">&laquo; Prev</a></li>';
			}

			$start_page = max(1, $current_page - 2);
			$end_page   = min($total_pages, $current_page + 2);

			if ($start_page > 1) {
				$pagination_html .= '<li class="page-item"><a class="page-link" href="' . html_escape($page_url(0)) . '">1</a></li>';
				if ($start_page > 2) $pagination_html .= '<li class="page-item"><span class="page-link">&hellip;</span></li>';
			}

			for ($i = $start_page; $i <= $end_page; $i++) {
				if ($i == $current_page) {
					$pagination_html .= '<li class="page-item active"><span class="page-link" aria-current="page">' . $i . '</span></li>';
				} else {
					$pagination_html .= '<li class="page-item"><a class="page-link" href="'
						. html_escape($page_url(($i - 1) * $per_page)) . '">' . $i . '</a></li>';
				}
			}

			if ($end_page < $total_pages) {
				if ($end_page < $total_pages - 1) $pagination_html .= '<li class="page-item"><span class="page-link">&hellip;</span></li>';
				$pagination_html .= '<li class="page-item"><a class="page-link" href="'
					. html_escape($page_url(($total_pages - 1) * $per_page)) . '">' . $total_pages . '</a></li>';
			}

			if ($current_page < $total_pages) {
				$pagination_html .= '<li class="page-item"><a class="page-link" href="'
					. html_escape($page_url($current_page * $per_page)) . '" rel="next">Next &raquo;</a></li>';
			}

			$pagination_html .= '</ul>';
		}

		$data['allListings']    = $this->usermodel->getListingByCategory($cat_slug, $per_page, $current_offset, $sort);
		$data['link']           = $pagination_html;
		// Where the "go to page" box posts: the category's own URL with no
		// offset segment, so `?page=` is the only thing deciding the page.
		$data['pager_action']   = base_url('all_listing' . ($cat_slug ? '/' . rawurlencode($cat_slug) : ''));
		$data['total_pages']    = $total_pages;
		$data['current_page']   = $current_page;
		$data['total_listings'] = $total_listings;
		$data['current_offset'] = $current_offset;
		$data['per_page']       = $per_page;
		$data['sort']           = $sort;

		// Set page title based on category
		$category_name = $cat_slug ? $this->usermodel->getCategoryNameBySlug($cat_slug) : '';

		// A slug that matches no category used to be titled "General" over an
		// empty list, which reads as a real but empty section rather than a
		// dead link — the reported "clicking Sports and Health redirects to the
		// General section". Say so instead, and offer the full category list.
		$data['category_not_found'] = ($cat_slug !== '' && $category_name === '');

		if ($cat_slug) {
			$data['title']            = ucfirst($category_name ?: str_replace('-', ' ', $cat_slug)) . ' in Pearland, TX — Pearland411 Directory';
			$data['meta_description'] = 'Browse ' . ($total_listings ? $total_listings . ' ' : '')
				. strtolower($category_name ?: str_replace('-', ' ', $cat_slug))
				. ' businesses in Pearland, Texas. Phone numbers, addresses, reviews and directions on Pearland411.';
			$data['canonical_url']    = base_url('all_listing/' . rawurlencode($cat_slug));
		} else {
			$data['title']            = 'Pearland Business Directory — Pearland411';
			$data['meta_description'] = 'The Pearland, TX business directory: '
				. ($total_listings ? number_format($total_listings) . ' local ' : 'local ')
				. 'businesses with phone numbers, addresses, reviews and directions.';
			$data['canonical_url']    = base_url('all_listing');
		}

		$data['current_category_slug'] = $cat_slug;
		$data['current_category_name'] = $category_name;
		// For category-targeted ad bookings (migration 0024).
		$data['current_category_id']   = $cat_slug !== '' ? $this->usermodel->categoryIdBySlug($cat_slug) : NULL;

		// The category's own header artwork (migration 0027). NULL, a missing
		// column on an install that has not run 0027, or a file that is no
		// longer on disk all fall through to the standard directory hero.
		$data['current_category_banner'] = NULL;
		if ($cat_slug !== '' && $data['current_category_id']) {
			$__cat_row = $this->usermodel->getSingleValue(
				$data['current_category_id'], 'category_id', 'listing_categories');
			if ($__cat_row && !empty($__cat_row->category_banner)) {
				$data['current_category_banner'] = (string) $__cat_row->category_banner;
			}
		}

		// Featured rail — prefers the category being browsed, tops up site-wide.
		$data['randListings']       = $this->usermodel->getFeaturedListingsForDirectory($cat_slug, 3);
		$data['categoriesWithCounts'] = $this->usermodel->getCategoriesWithCounts(12);

		// A category with nothing in it gets one house ad, not six.
		$data['ad_house_cap'] = $this->thinPageAdCap($total_listings);

		// Ad zones. Priming means the six placements below cost one query
		// rather than six. Every key is a `plans.slug` from /pricing-plan, so
		// buying the package fills the position (see migrations 0017 + 0019).
		$data['ad_slots'] = array(
			'top'      => 'top-banner-site-wide',
			'inline'   => 'middle-banner-category',
			'side_1'   => 'right-side-banner-1',
			// Was 'right-banner-1', while listing-details used
			// 'right-side-banner-2' for the same position — so an ad sold for
			// the second sidebar slot showed on one page and not the other.
			// The category-priced zones stay reachable as aliases below.
			'side_2'   => 'right-side-banner-2',
			'side_sky' => 'right-panel-skyscraper',
			'bottom'   => $cat_slug ? 'bottom-banner-category' : 'bottom-banner-site-wide',
		);
		// The 0006 zones this page used to render ('listing-top', 'listing-inline',
		// 'listing-sidebar') match no package, so nothing could be bought for
		// them — but an operator may have booked one by hand in /admin. They stay
		// primed and are passed to the views as $slot_alias, so any live ad on
		// them keeps running in the equivalent new position.
		$this->usermodel->primeAdSlots(array_merge(
			array_values($data['ad_slots']),
			array('listing-top', 'listing-inline', 'listing-sidebar',
			      'right-banner-1', 'right-banner-2')
		));

		// promo-cards.css carries the two house promo cards this page
		// includes; it is shared with the homepage so they can't drift.
		$data['page_css'] = array('assets/css/directory.css', 'assets/css/promo-cards.css');

		$this->load->view('header', $data);
		$this->load->view('all-listing', $data);
		$this->load->view('footer');
	}


	/*All Blog Post*/

	public function all_blog()
	{
		if (!$this->requireService('service_blog_enabled')) return;
		// Get search query from GET parameter
		$search_query = $this->input->get('q');

		if ($this->session->userdata('logged_user')) {
			$data['user'] = $this->usermodel->getUserInformation();
		}

		// Custom pagination logic (same as event_search)
		$per_page = 12;
		$page = ($this->input->get('page')) ? (int)$this->input->get('page') : 1;
		$offset = ($page - 1) * $per_page;

		if (!empty($search_query)) {
			$all_blogs = $this->usermodel->searchBlogs($search_query);
			$data['search_query'] = $search_query;
			$data['title'] = "Search Results for '" . htmlspecialchars($search_query) . "' | Blog Posts";
		} else {
			$all_blogs = $this->usermodel->getAllBlog();
			$data['search_query'] = '';
			$data['title'] = "Pearland | All Blog";
		}

		$data['AllBlog'] = array_slice($all_blogs, $offset, $per_page);
		$total_blogs = count($all_blogs);

		// Build custom pagination links (same as event)
		$pagination_links = '';
		$total_pages = ceil($total_blogs / $per_page);
		if ($total_pages > 1) {
			$pagination_links .= '<ul class="pagination">';
			$base_url = base_url('all-blog');
			$query = $_GET;
			for ($i = 1; $i <= $total_pages; $i++) {
				$query['page'] = $i;
				$url = $base_url . '?' . http_build_query($query);
				$active = ($i == $page) ? 'active' : '';
				$pagination_links .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . $url . '">' . $i . '</a></li>';
			}
			$pagination_links .= '</ul>';
		}
		$data['pagination_links'] = $pagination_links;

		// The page kit's shell wants the same counters the news and video
		// indexes get, so the toolbar can say what it is showing.
		$data['total_blogs']    = $total_blogs;
		$data['per_page']       = $per_page;
		$data['current_page']   = $page;
		$data['total_pages']    = (int) $total_pages;
		$data['current_offset'] = $offset;

		// Most-read rail. Read off the same set rather than another query —
		// the whole list is already in memory here.
		$popular = $all_blogs;
		usort($popular, function ($a, $b) {
			return ((int) ($b->views ?? 0)) <=> ((int) ($a->views ?? 0));
		});
		$data['popular_blogs'] = array_slice(array_filter($popular, function ($b) {
			return (int) ($b->views ?? 0) > 0;
		}), 0, 5);

		$data['ad_slots'] = $this->contentAdSlots();
		$data['page_css'] = $this->contentPageCss();
		$data['meta_description'] = 'Stories, guides and updates from Pearland businesses and the Pearland411 community.';
		$data['canonical_url']    = base_url('all-blog');

		// Load header categories using professional MVC approach
		$this->loadHeaderCategories($data);

		$this->load->view('header', $data);
		$this->load->view('blog-posts', $data);
		$this->load->view('footer');
	}

	/*All Event Post*/

	public function event()
	{
		if (!$this->requireService('service_events_enabled')) return;

		$search_query = $this->input->get('q', TRUE);
		$search_query = is_string($search_query) ? trim($search_query) : '';

		// Time window. An events index sorted newest-date-first shows next
		// year's fair above tonight's concert and mixes in everything that
		// already happened, so the page asks for a window and defaults to
		// what is still to come.
		$when          = strtolower((string) $this->input->get('when', TRUE));
		$explicit_when = in_array($when, array('upcoming', 'past', 'all'), TRUE);
		if (!$explicit_when) $when = 'upcoming';

		$per_page = 12;
		$page = (int) $this->input->get('page', TRUE);
		if ($page < 1) $page = 1;

		// Counts for all three tabs: the strip has to be honest about which
		// windows have anything in them, and the default depends on it.
		$counts = array(
			'upcoming' => $this->usermodel->countEventsWithSearch($search_query, 'upcoming'),
			'past'     => $this->usermodel->countEventsWithSearch($search_query, 'past'),
			'all'      => $this->usermodel->countEventsWithSearch($search_query, 'all'),
		);

		// Never land on an empty page just because nothing is scheduled —
		// fall back to the archive and let the view say so.
		$when_auto = FALSE;
		if (!$explicit_when && $counts['upcoming'] === 0 && $counts['all'] > 0) {
			$when      = 'all';
			$when_auto = TRUE;
		}

		$total_rows  = (int) $counts[$when];
		$total_pages = max(1, (int) ceil($total_rows / $per_page));
		// An offset past the end (hand-typed, or after events were removed)
		// used to render a blank list; clamp to the last real page instead.
		if ($page > $total_pages) $page = $total_pages;
		$offset = ($page - 1) * $per_page;

		$all_events = $this->usermodel->getEventsWithPagination($search_query, $per_page, $offset, $when);

		$config = [
			'base_url' => base_url('event'),
			'total_rows' => $total_rows,
			'per_page' => $per_page,
			'page_query_string' => TRUE,
			'query_string_segment' => 'page',
			'reuse_query_string' => TRUE,
			'use_page_numbers' => TRUE,
			// Without this CI3 emits a bare <a>, and the shared pager styling
			// keys off .page-link.
			'attributes' => array('class' => 'page-link'),
			'full_tag_open' => '<ul class="pagination">',
			'full_tag_close' => '</ul>',
			'cur_tag_open' => '<li class="page-item active"><span class="page-link">',
			'cur_tag_close' => '</span></li>',
			'num_tag_open' => '<li class="page-item">',
			'num_tag_close' => '</li>',
			'prev_tag_open' => '<li class="page-item">',
			'prev_tag_close' => '</li>',
			'next_tag_open' => '<li class="page-item">',
			'next_tag_close' => '</li>',
			'first_tag_open' => '<li class="page-item">',
			'first_tag_close' => '</li>',
			'last_tag_open' => '<li class="page-item">',
			'last_tag_close' => '</li>',
			'next_link' => 'Next &rsaquo;',
			'prev_link' => '&lsaquo; Prev',
		];
		$this->pagination->initialize($config);

		$data = array();
		$data['Allevent']         = $all_events;
		$data['pagination_links'] = $this->pagination->create_links();
		$data['search_query']     = $search_query;
		$data['when']             = $when;
		$data['when_counts']      = $counts;
		$data['when_auto']        = $when_auto;
		$data['total_events']     = $total_rows;
		$data['per_page']         = $per_page;
		$data['current_page']     = $page;
		$data['total_pages']      = $total_pages;
		$data['current_offset']   = $offset;

		// Sidebar rail — what's next, whichever window is being browsed.
		$data['upcoming_events'] = $this->usermodel->getUpcomingEvents(5);

		if ($this->session->userdata('logged_user')) {
			$data['user'] = $this->usermodel->getUserInformation();
		}

		$data['ad_slots'] = $this->contentAdSlots();
		$this->usermodel->primeAdSlots(array_merge(
			array_values($data['ad_slots']),
			array('events-sidebar', 'right-banner-1', 'right-banner-2')
		));
		$data['page_css'] = $this->contentPageCss();

		$data['title'] = $search_query !== ''
			? 'Events matching "' . $search_query . '" — Pearland411'
			: 'Pearland Events — What&rsquo;s On in Pearland, TX';
		$data['meta_description'] = 'Concerts, markets, festivals and community events in Pearland, Texas. '
			. ($counts['upcoming'] > 0 ? $counts['upcoming'] . ' upcoming events with dates, times and venues.' : 'Dates, times, venues and contact details.');
		$data['canonical_url'] = base_url('event');

		// Load header categories using professional MVC approach
		$this->loadHeaderCategories($data);

		$this->load->view('header', $data);
		$this->load->view('events', $data);
		$this->load->view('footer');
	}

	/**
	 * Legacy alias for the events index.
	 *
	 * It used to be a second, thinner copy of event(): no pagination, no time
	 * window, no counts, no sidebar and no ad zones, so /user/event_search
	 * rendered a visibly poorer page than /event?q=… for the same search. The
	 * search box posts to /event now; one code path serves both.
	 */
	public function event_search()
	{
		$this->event();
	}

	// Videos Page
	public function videos()
	{
		if (!$this->requireService('service_videos_enabled')) return;

		$search_query = $this->input->get('q', TRUE);
		$search_query = is_string($search_query) ? trim($search_query) : '';

		$cat_slug = $this->input->get('category', TRUE);
		$cat_slug = is_string($cat_slug) ? trim($cat_slug) : '';
		$cat_id   = $cat_slug !== '' ? $this->usermodel->resolveContentCategoryId($cat_slug, 'video_categories') : NULL;
		$cat_name = '';
		if ($cat_id !== NULL) {
			$cat_row  = $this->usermodel->getSingleValue($cat_id, 'category_id', 'video_categories');
			$cat_name = $cat_row ? (string) $cat_row->category_name : '';
		} elseif ($cat_slug !== '') {
			$cat_id = 0;
		}

		$per_page = 12;
		$page = (int) $this->input->get('page', TRUE);
		if ($page < 1) $page = 1;

		$total_rows  = $this->usermodel->countVideosWithSearch($search_query, $cat_id);
		$total_pages = max(1, (int) ceil($total_rows / $per_page));
		if ($page > $total_pages) $page = $total_pages;
		$offset = ($page - 1) * $per_page;

		$all_videos = $this->usermodel->getAllVideos($search_query, $per_page, $offset, $cat_id);

		$config = [
			'base_url' => base_url('videos'),
			'total_rows' => $total_rows,
			'per_page' => $per_page,
			'page_query_string' => TRUE,
			'query_string_segment' => 'page',
			'reuse_query_string' => TRUE,
			'use_page_numbers' => TRUE,
			'attributes' => array('class' => 'page-link'),
			'full_tag_open' => '<ul class="pagination">',
			'full_tag_close' => '</ul>',
			'cur_tag_open' => '<li class="page-item active"><span class="page-link">',
			'cur_tag_close' => '</span></li>',
			'num_tag_open' => '<li class="page-item">',
			'num_tag_close' => '</li>',
			'prev_tag_open' => '<li class="page-item">',
			'prev_tag_close' => '</li>',
			'next_tag_open' => '<li class="page-item">',
			'next_tag_close' => '</li>',
			'first_tag_open' => '<li class="page-item">',
			'first_tag_close' => '</li>',
			'last_tag_open' => '<li class="page-item">',
			'last_tag_close' => '</li>',
			'next_link' => 'Next &rsaquo;',
			'prev_link' => '&lsaquo; Prev',
		];
		$this->pagination->initialize($config);

		$data = array();
		$data['all_videos']       = $all_videos;
		$data['pagination_links'] = $this->pagination->create_links();
		$data['search_query']     = $search_query;
		$data['total_videos']     = $total_rows;
		$data['per_page']         = $per_page;
		$data['current_page']     = $page;
		$data['total_pages']      = $total_pages;
		$data['current_offset']   = $offset;

		$data['video_categories']      = $this->usermodel->getVideoCategoriesWithCounts(12);
		$data['current_category_slug'] = $cat_slug;
		$data['current_category_name'] = $cat_name;
		$data['popular_videos']        = $this->usermodel->getPopularVideos(5);

		if ($this->session->userdata('logged_user')) {
			$data['user'] = $this->usermodel->getUserInformation();
		}

		$data['ad_slots'] = $this->contentAdSlots();
		$this->usermodel->primeAdSlots(array_merge(
			array_values($data['ad_slots']),
			array('right-banner-1', 'right-banner-2')
		));
		$data['page_css'] = $this->contentPageCss();

		$data['title'] = $cat_name !== ''
			? $cat_name . ' Videos — Pearland411'
			: 'Pearland Videos — Local Video from Pearland, TX — Pearland411';
		$data['meta_description'] = 'Watch local video from Pearland, Texas: reports, walk-throughs and community clips'
			. ($cat_name !== '' ? ' in ' . $cat_name . '.' : '.');
		$data['canonical_url'] = $cat_slug !== ''
			? base_url('videos') . '?' . http_build_query(array('category' => $cat_slug))
			: base_url('videos');

		// Load header categories using professional MVC approach
		$this->loadHeaderCategories($data);

		$this->load->view('header', $data);
		$this->load->view('videos', $data);
		$this->load->view('footer');
	}


	// Classifieds Page
	public function classifieds()
	{
		if (!$this->requireService('service_classifieds_enabled')) return;
		$this->load->model('usermodel');
		$this->load->library('pagination');

		$search_query = $this->input->get('q', TRUE);
		$page = (int) $this->input->get('page', TRUE);
		$per_page = 10;
		if ($page < 1) $page = 1;
		$offset = ($page - 1) * $per_page;

		$total_rows = $this->usermodel->countClassifiedsWithSearch($search_query);
		$all_classifieds = $this->usermodel->getAllClassifieds($search_query, $per_page, $offset);

		$config = [
			'base_url' => base_url('classifieds'),
			'total_rows' => $total_rows,
			'per_page' => $per_page,
			'page_query_string' => TRUE,
			'query_string_segment' => 'page',
			'reuse_query_string' => TRUE,
			'use_page_numbers' => TRUE,
			'full_tag_open' => '<ul class="pagination">',
			'full_tag_close' => '</ul>',
			'cur_tag_open' => '<li class="page-item active"><span class="page-link">',
			'cur_tag_close' => '</span></li>',
			'num_tag_open' => '<li class="page-item">',
			'num_tag_close' => '</li>',
			'prev_tag_open' => '<li class="page-item">',
			'prev_tag_close' => '</li>',
			'next_tag_open' => '<li class="page-item">',
			'next_tag_close' => '</li>',
			'first_tag_open' => '<li class="page-item">',
			'first_tag_close' => '</li>',
			'last_tag_open' => '<li class="page-item">',
			'last_tag_close' => '</li>',
			'next_link' => 'Next >',
			'prev_link' => '< Prev',
		];
		$this->pagination->initialize($config);
		$pagination_links = $this->pagination->create_links();

		$data['all_classifieds'] = $all_classifieds;
		$data['pagination_links'] = $pagination_links;
		$data['search_query'] = $search_query;

		// What the toolbar and the pager label themselves with. The view used
		// to print `count($all_classifieds)` as the result count, so a search
		// with 68 matches reported "10 classifieds found".
		$data['total_classifieds'] = (int) $total_rows;
		$data['per_page']       = $per_page;
		$data['current_page']   = $page;
		$data['total_pages']    = $per_page > 0 ? (int) ceil($total_rows / $per_page) : 1;
		$data['current_offset'] = $offset;

		$data['classifieds_categories'] = $this->usermodel->getClassifiedsCategoriesWithCounts(8);

		// Same six zones as the directory and the other content pages.
		$data['ad_slots'] = array(
			'top'      => 'top-banner-site-wide',
			'inline'   => 'middle-banner-category',
			'side_1'   => 'right-side-banner-1',
			'side_2'   => 'right-side-banner-2',
			'side_sky' => 'right-panel-skyscraper',
			'bottom'   => 'bottom-banner-site-wide',
		);
		$this->usermodel->primeAdSlots(array_values($data['ad_slots']));

		$data['page_css'] = $this->contentPageCss();

		$data['title'] = $search_query !== ''
			? 'Classifieds matching "' . $search_query . '" | Pearland411'
			: 'Pearland Classifieds — Buy, Sell, Rent and Hire Locally | Pearland411';
		$data['meta_description'] = 'Local classifieds for Pearland, Texas — vehicles, property, furniture, '
			. 'jobs and services posted by people and businesses in the area.';
		$data['canonical_url'] = base_url('classifieds');

		// Load header categories using professional MVC approach
		$this->loadHeaderCategories($data);

		$this->load->view('header', $data);
		$this->load->view('classifieds', $data);
		$this->load->view('footer');
	}


	/*Regitration*/

	public function register()

	{



		if (isset($_POST['register_submit'])) {/*Registration Action*/

			// 5 signups per IP per hour. Defends signup spam.
			if (!$this->rate_limit->check_ip('register', 5, 3600)) {
				$this->rate_limit->reject(3600);
				return;
			}

			//Register User If data valid

			$data['registrationSucess'] = $this->usermodel->registerUser();

			if ($data['registrationSucess']['status']) {

				$data['reset'] = TRUE;
			} else {

				$data['reset'] = FALSE;
			}
		} else {

			$data['registrationSucess'] = array('status' => FALSE, 'referror' => '');

			$data['reset'] = FALSE;
		}

		$data['title'] = "Pearland | Registration";
		$data['page_css'] = $this->contentPageCss();
		// The mega menu is emitted by header.php on every page; without this
		// the categories column came up empty on signup and sign-in.
		$this->loadHeaderCategories($data);

		// No advertising on this page. See login() — no advertising on an account form.

		$this->load->view('header', $data);

		$this->load->view('register', $data);

		$this->load->view('footer');
	}



	/*Login*/

	public function login()

	{

		if ($this->session->userdata('logged_user')) {

			redirect('dashboard');
		}

		if (isset($_POST['login_submit'])) {
			// 10 failed login attempts per IP per 15 minutes is plenty
			// for an honest user; anything more is brute-force.
			if (!$this->rate_limit->check_ip('login', 10, 900)) {
				$this->rate_limit->reject(900);
				return;
			}

			// Process login form submission
			$data['loginSucess'] = $this->usermodel->login();

			// Reset the bucket on success so an honest user who fat-
			// fingered a couple of times isn't stuck near the limit.
			if (!empty($data['loginSucess']['status'])) {
				$this->rate_limit->reset_ip('login');
			}

			if ($data['loginSucess']['status']) {
				// Check if there's a redirect URL stored in session
				$redirect_url = $this->session->userdata('redirect_url');
				if ($redirect_url) {
					// Clear the redirect URL from session
					$this->session->unset_userdata('redirect_url');
					// Redirect to the stored URL
					redirect($redirect_url);
				} else {
					// Default redirect to dashboard
					redirect('user/dashboard');
				}
				exit();
			} else {
				// Login failed - show error message
				$data['reset'] = FALSE;
				$data['error_message'] = isset($data['loginSucess']['message']) ? $data['loginSucess']['message'] : 'Login failed. Please try again.';
			}
		} else {
			// Initial page load
			$data['loginSucess'] = array('status' => FALSE, 'message' => '', 'referror' => '');
			$data['reset'] = FALSE;
		}

		// Fix: Safely handle HTTP_REFERER to prevent undefined index error
		$data['http_referer'] = $this->getServerVar('HTTP_REFERER', base_url());

		$data['title'] = isset($data['title']) ? $data['title'] : 'Sign in — Pearland411';
		$data['page_css'] = $this->contentPageCss();
		$this->loadHeaderCategories($data);

		// No advertising on this page. Someone typing a password should not be looking at an ad — it reads as
		// less trustworthy, and this traffic is repeat users who never click one.

		$this->load->view('header', $data);

		$this->load->view('login', $data);

		$this->load->view('footer');
	}



	/*=========================
		Password reset + email verification
		==========================*/

	/**
	 * GET  -> render the forgot-password form.
	 * POST -> accept an email address and (silently) email a reset link
	 *         if it matches a registered user. We always show the same
	 *         confirmation message so attackers can't enumerate emails.
	 */
	public function forgot_password()
	{
		$data = array();
		$this->loadHeaderCategories($data);
		$data['title']    = 'Pearland | Forgot Password';
		$data['page_css'] = $this->contentPageCss();

		if ($this->input->method() === 'post') {
			// 5 reset requests per IP per hour. Each one emails a real
			// person; we don't want this used as a free SMTP relay.
			if (!$this->rate_limit->check_ip('forgot_password', 5, 3600)) {
				$this->rate_limit->reject(3600);
				return;
			}

			$this->form_validation->set_rules('email_id', 'Email', 'trim|required|valid_email');

			if ($this->form_validation->run()) {
				$email = $this->input->post('email_id');
				$this->usermodel->requestPasswordReset(
					$email,
					$this->input->ip_address()
				);
				$this->session->set_flashdata(
					'forgot_message',
					'If an account exists for that email, we have sent a password reset link. ' .
					'It will expire in 1 hour.'
				);
				redirect('forgot-password');
				return;
			}
			$data['error'] = 'Please enter a valid email address.';
		}

		$this->load->view('header', $data);
		$this->load->view('forgot_password', $data);
		$this->load->view('footer');
	}

	/**
	 * GET  /reset-password/<token> -> render the reset form.
	 * POST same URL              -> verify token, write new password.
	 */
	public function reset_password($token = '')
	{
		$reset = $this->usermodel->findActiveResetByToken($token);

		$data = array();
		$this->loadHeaderCategories($data);
		$data['title']    = 'Pearland | Reset Password';
		$data['page_css'] = $this->contentPageCss();
		$data['token']    = $token;

		if (!$reset) {
			$data['expired'] = true;
			$this->load->view('header', $data);
			$this->load->view('reset_password', $data);
			$this->load->view('footer');
			return;
		}

		if ($this->input->method() === 'post') {
			$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[8]');
			$this->form_validation->set_rules('password_confirm', 'Confirm Password', 'trim|required|matches[password]');

			if ($this->form_validation->run()) {
				$ok = $this->usermodel->completePasswordReset($reset, $this->input->post('password'));
				if ($ok) {
					$this->session->set_flashdata('login_message', 'Your password has been reset. Please sign in with your new password.');
					redirect('login');
					return;
				}
				$data['error'] = 'Something went wrong. Please request a new reset link.';
			} else {
				$data['error'] = strip_tags(validation_errors()) ?: 'Please correct the errors above.';
			}
		}

		$this->load->view('header', $data);
		$this->load->view('reset_password', $data);
		$this->load->view('footer');
	}

	/**
	 * GET  /resend-verification -> render the form.
	 * POST same URL             -> re-issue and re-send the confirmation link.
	 *
	 * The missing half of the signup flow. An account whose verification email
	 * never arrived could not sign in (the model blocks it) and could not ask
	 * for another copy, because nothing on the site issued one — so the only
	 * route back in was a support request and a hand-edited row.
	 *
	 * Answers identically whether or not the address is registered, matching
	 * forgot_password(): this form must not become an account-enumeration
	 * oracle, especially as the negative cases include "exists and is already
	 * confirmed".
	 */
	public function resend_verification()
	{
		$data = array();
		$this->loadHeaderCategories($data);
		$data['title']    = 'Pearland | Resend Verification Email';
		$data['page_css'] = $this->contentPageCss();

		if ($this->input->method() === 'post') {
			// Same budget as forgot_password: each request emails a real
			// person, so the form cannot be used as a free relay.
			if (!$this->rate_limit->check_ip('resend_verification', 5, 3600)) {
				$this->rate_limit->reject(3600);
				return;
			}

			$this->form_validation->set_rules('email_id', 'Email', 'trim|required|valid_email');

			if ($this->form_validation->run()) {
				$this->usermodel->resendVerification($this->input->post('email_id'));
				$this->session->set_flashdata(
					'resend_message',
					'If that address has an unconfirmed Pearland411 account, we have just sent ' .
					'it a new confirmation link. It can take a few minutes to arrive — check ' .
					'your spam folder too.'
				);
				redirect('resend-verification');
				return;
			}
			$data['error'] = 'Please enter a valid email address.';
		}

		$this->load->view('header', $data);
		$this->load->view('resend_verification', $data);
		$this->load->view('footer');
	}

	/**
	 * GET /verify-email/<token> -> mark the user verified.
	 */
	public function verify_email($token = '')
	{
		$ok = $this->usermodel->verifyEmailByToken($token);

		$data = array();
		$this->loadHeaderCategories($data);
		$data['title']    = 'Pearland | Email Verification';
		$data['page_css'] = $this->contentPageCss();
		$data['verified'] = $ok;

		$this->load->view('header', $data);
		$this->load->view('verify_email', $data);
		$this->load->view('footer');
	}



	/*Logout*/

	public function logout()

	{

		$this->session->unset_userdata('logged_user');

		$this->session->sess_destroy();

		redirect(base_url());
		die();
	}







	/******* After User Login All Controller Below ***********/





	/*====================

	Dashboard

	=====================*/

	public function dashboard()
	{

		$data['logged_id'] = NULL;

		if ($this->session->userdata('logged_user')) {

			$session 		   = $this->session->userdata('logged_user');

			$data['logged_id'] = $session['logged_id'];
		}

		$data['user'] = $this->usermodel->getUserInformation();

		// Get subscription data
		$data['user_subscription'] = $this->usermodel->getUserSubscription($data['logged_id']);
		$data['has_subscription'] = !empty($data['user_subscription']);

		if ($data['has_subscription']) {
			$data['subscription_limits'] = $this->usermodel->getSubscriptionLimits($data['logged_id']);
			$data['usage_counts'] = $this->usermodel->getAllUsageCounts($data['logged_id']);
		} else {
			$data['subscription_limits'] = null;
			$data['usage_counts'] = [];
		}

		$uid = (int) $data['logged_id'];
		$data['AllListings'] = $this->usermodel->getListingById($uid, 10, NULL);

		// This member's own events and blog posts. Both used to come from
		// dashboardInfo(), which had no WHERE at all, so every member saw the
		// first ten events and blogs on the whole site as theirs — and the
		// "Used" figure was a count of other people's posts.
		$data['AllEvents'] = $uid ? $this->usermodel->getAllEvent($uid, 10, 0) : array();
		$data['AllBlogs']  = $uid ? $this->usermodel->getAllBlogs($uid, 10, 0) : array();

		// "Used" is a COUNT of what they own, not the length of a 10-row list.
		$data['dashboard_used'] = array(
			'listing' => $this->usermodel->countOwnedContent('listings', $uid),
			'event'   => $this->usermodel->countOwnedContent('events', $uid),
			'blog'    => $this->usermodel->countOwnedContent('blogs', $uid),
		);





		$data['title'] = "Pearland | Dashboard";



		$this->load->view('header', $data);

		$this->load->view('user_left_sidebar');

		$this->load->view('user_dashboard');

		$this->load->view('user_right_sidebar');

		$this->load->view('footer');
	}







	/*User Profile*/

	public function my_profile()

	{

		$this->load->view('header');
		$this->load->view('user_left_sidebar');
		$this->load->view('my_profile');
		$this->load->view('user_right_sidebar');
		$this->load->view('footer');
	}

	/*User Account Management*/
	public function my_account()
	{
		try {
			// Get user information
			$data['userInfo'] = $this->usermodel->getUserInformation();

			if (!$data['userInfo']) {
				// Redirect to login if no user found
				redirect('user/login');
				return;
			}

			// Get subscription information
			$data['has_subscription'] = $this->usermodel->getUserSubscription($data['userInfo']->user_id);
			$data['subscription_limits'] = $this->usermodel->getSubscriptionLimits($data['userInfo']->user_id);
			$data['usage_counts'] = $this->usermodel->getAllUsageCounts($data['userInfo']->user_id);

			// Manage-plan panel: available plans + Helcim front-end config.
			$data['all_plans'] = $this->usermodel->getActivePlans();
			$this->load->library('helcim_lib');
			$data['helcim_configured'] = $this->helcim_lib->is_configured();
			$data['helcimpay_js_url']  = $this->helcim_lib->helcimpay_js_url();

			// Transaction history (added for account transactions tab)
			if (method_exists($this->usermodel, 'getUserTransactions')) {
				$data['user_transactions'] = $this->usermodel->getUserTransactions($data['userInfo']->user_id, 50);
				$data['user_transactions_data'] = $data['user_transactions'];
			} else {
				$data['user_transactions'] = array();
				$data['user_transactions_data'] = array();
			}

			// Set page title
			$data['title'] = "My Account | Account Management";

			// Load views with header and sidebar
			$this->load->view('header', $data);
			$this->load->view('user_left_sidebar', $data);
			$this->load->view('user_account', $data);
			// $this->load->view('user_right_sidebar');
			$this->load->view('footer');
		} catch (Exception $e) {
			// Log error and show error page
			log_message('error', 'Account page error: ' . $e->getMessage());
			show_404();
		}
	}

	/*User Account Management - Alternative URL*/
	public function account()
	{
		$this->my_account();
	}

	/* Update Account (POST) */
	public function update_account()
	{
		if (!$this->session->userdata('logged_user')) {
			redirect('user/login');
			return;
		}

		$session = $this->session->userdata('logged_user');
		$user_id = $session['logged_id'];

		// Validate minimal fields
		$this->form_validation->set_rules('name', 'Full Name', 'trim|required|min_length[2]');
		$this->form_validation->set_rules('email_id', 'Email', 'trim|required|valid_email');
		$this->form_validation->set_rules('mobile_number', 'Phone', 'trim');

		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('message', validation_errors());
			redirect('user/my_account');
			return;
		}

		// Get current user info for profile image handling
		$current_user = $this->usermodel->getUserInformation();
		$old_image = isset($current_user->profile_image) ? $current_user->profile_image : null;

		$info = array(
			'name'          => $this->input->post('name'),
			'email_id'      => $this->input->post('email_id'),
			'mobile_number' => $this->input->post('mobile_number'),
			'user_address'  => $this->input->post('user_address'),
			'user_state'    => $this->input->post('user_state'),
			'user_zip_code' => $this->input->post('user_zip_code'),
			'user_contact_email' => $this->input->post('user_contact_email'),
			'user_website'  => $this->input->post('user_website'),
			'user_updated'  => date('Y-m-d H:i:s'),
		);

		// Optional: handle password change if provided
		if ($this->input->post('password') && trim($this->input->post('password')) !== '') {
			$info['password'] = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
		}

		// Handle profile image upload using project's upload helper function
		if (!empty($_FILES['profile_image']['name'])) {
			$img_info = [
				'upload_path' => 'images/user',
				'quality'     => '80%',
				'height'      => 400,
				'width'       => 400,
				'image_name'  => 'profile_image'
			];

			// Delete old image if exists
			if ($old_image && file_exists('./' . $old_image)) {
				unlink('./' . $old_image);
			}

			// Upload new image using project's helper function
			$uploaded_image = uploadAndResize($img_info);

			if ($uploaded_image) {
				$info['profile_image'] = $uploaded_image;
			} else {
				$this->session->set_flashdata('message', 'Failed to upload profile image. Please try again.');
				redirect('user/my_account');
				return;
			}
		}

		$updated = $this->usermodel->update($user_id, $info, 'user_id', 'user');
		if ($updated) {
			// Update session data with all relevant info for sidebar
			$updated_session = $this->session->userdata('logged_user');
			$updated_session['name'] = $info['name'];
			$updated_session['email_id'] = $info['email_id'];

			// Update profile image in session if it was changed
			if (isset($info['profile_image'])) {
				$updated_session['profile_image'] = $info['profile_image'];
			}

			$this->session->set_userdata('logged_user', $updated_session);

			$this->session->set_flashdata('message', 'Account updated successfully.');
		} else {
			$this->session->set_flashdata('message', 'No changes or update failed.');
		}

		redirect('user/my_account');
	}








	/*======================
	    USER LISTING
	======================*/


	/*===================
		Listing Details Page - Global Access
		This method should be moved to a main controller for global access
		Current URL: /user/listing_details/slug
		Desired URL: /listing-details/slug (requires routes.php or main controller)
	===================*/
	public function listing_details($slug)
	{
		// Get listing details by slug
		$data['listing'] = $this->usermodel->getSingleValue($slug, 'listing_slug', 'listings');
		// Increment views for this listing
		if ($data['listing']) {
			$this->usermodel->incrementViews('listings', 'listing_id', $data['listing']->listing_id);
		}

		if (!$data['listing']) {
			show_404();
			return;
		}

		// Debug: Check if service fields are present
		if (empty($data['listing']->service_offered)) {
			$data['listing']->service_offered = null; // Ensure it's properly set as null if empty
		}
		if (empty($data['listing']->service_locations)) {
			$data['listing']->service_locations = null; // Ensure it's properly set as null if empty
		}

		// Add sharing URL for social media
		$data['current_page_url'] = base_url('user/listing-details/' . $slug);

		// Get logged user ID for form prefilling
		$logged_id = NULL;
		$data['user'] = NULL;
		if ($this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$logged_id = $session['logged_id'];
			$data['user'] = $this->usermodel->getSingleValue($logged_id, 'user_id', 'user');
		}
		$data['logged_id'] = $logged_id;

		// Get related listings (same category)
		$data['RelatedListing'] = array();
		if (!empty($data['listing']->category_id)) {
			$data['RelatedListing'] = $this->usermodel->getRelatedListing('listings', $data['listing']->listing_slug, $data['listing']->category_id);
		}

		// Get listing likes data
		$data['listing_likes'] = $this->usermodel->getRecentLikers($data['listing']->listing_id, 5);

		// The owner's live deals. The post-a-deal screen promises the offer
		// appears "on your listing"; until this card it appeared only on
		// /coupons and the homepage.
		$data['listing_coupons'] = !empty($data['listing']->user_id)
			? $this->usermodel->getActiveCouponsByUser((int) $data['listing']->user_id, 3)
			: array();



		// Get listing reviews
		$data['listing_reviews'] = $this->usermodel->getListingReviews($data['listing']->listing_id, 10);

		// Get review statistics for rating breakdown: [5 => n, 4 => n, …]
		$data['review_stats'] = $this->usermodel->getReviewStats($data['listing']->listing_id);

		// Totals derived once here rather than recomputed in the view. The
		// listings row caches average_rating/total_reviews, but it is only
		// refreshed by updateListingRating(), so fall back to the breakdown.
		$review_total = array_sum($data['review_stats']);
		$review_avg   = 0.0;
		if ($review_total > 0) {
			$weighted = 0;
			foreach ($data['review_stats'] as $stars => $count) $weighted += ($stars * $count);
			$review_avg = $weighted / $review_total;
		} elseif (!empty($data['listing']->total_reviews)) {
			$review_total = (int) $data['listing']->total_reviews;
			$review_avg   = (float) ($data['listing']->average_rating ?? 0);
		}
		$data['review_total']   = $review_total;
		$data['review_average'] = $review_avg;

		// Category, for the breadcrumb and the "more like this" links.
		$data['listing_category'] = null;
		if (!empty($data['listing']->category_id)) {
			$data['listing_category'] = $this->db
				->select('category_id, category_name, category_slug')
				->where('category_id', (int) $data['listing']->category_id)
				->limit(1)
				->get('listing_categories')
				->row();
		}

		// Owner social-profile lookup so the "Company badge" block
		// can render real per-listing social links instead of the
		// placeholder facebook.com/twitter.com URLs that used to be
		// hardcoded in the view.
		$data['owner_socials'] = array(
			'facebook' => '', 'twitter' => '', 'youtube' => '', 'website' => '',
		);
		if (!empty($data['listing']->user_id)) {
			$owner = $this->db
				->select('user_facebook, user_twitter, user_youtube, user_website')
				->where('user_id', (int) $data['listing']->user_id)
				->limit(1)
				->get('user')
				->row();
			if ($owner) {
				$data['owner_socials'] = array(
					'facebook' => (string) ($owner->user_facebook ?? ''),
					'twitter'  => (string) ($owner->user_twitter  ?? ''),
					'youtube'  => (string) ($owner->user_youtube  ?? ''),
					'website'  => (string) ($owner->user_website  ?? ''),
				);
			}
		}

		// SEO: per-page meta + LocalBusiness JSON-LD.
		// Prefer admin-curated listing_seo_* columns when present,
		// fall back to a trimmed description / generated string.
		$listing = $data['listing'];
		$desc_auto = trim(strip_tags((string) ($listing->listing_description ?? '')));
		$desc_auto = $desc_auto !== '' ? mb_substr($desc_auto, 0, 160) : ('Listing details for ' . $listing->listing_name . ' on Pearland411.');
		$data['title']            = !empty($listing->listing_seo_title)
			? $listing->listing_seo_title
			: ($listing->listing_name . ' — Pearland411');
		$data['meta_description'] = !empty($listing->listing_seo_description)
			? $listing->listing_seo_description
			: $desc_auto;
		$data['meta_keywords']    = !empty($listing->listing_seo_keywords)
			? $listing->listing_seo_keywords
			: '';
		$data['canonical_url']    = base_url('listing-details/' . rawurlencode($listing->listing_slug));
		if (!empty($listing->listing_image)) {
			$data['og_image'] = base_url($listing->listing_image);
		}

		$ld = array(
			'@context' => 'https://schema.org',
			'@type'    => 'LocalBusiness',
			'name'     => (string) $listing->listing_name,
			'url'      => $data['canonical_url'],
		);
		if (!empty($listing->listing_address))  $ld['address']   = (string) $listing->listing_address;
		if (!empty($listing->listing_mobile))   $ld['telephone'] = (string) $listing->listing_mobile;
		if (!empty($listing->listing_email))    $ld['email']     = (string) $listing->listing_email;
		if (!empty($listing->listing_website))  $ld['sameAs']    = array((string) $listing->listing_website);
		if (!empty($listing->listing_image))    $ld['image']     = base_url($listing->listing_image);
		if (!empty($listing->listing_lat) && !empty($listing->listing_lng)) {
			$ld['geo'] = array(
				'@type'     => 'GeoCoordinates',
				'latitude'  => (float) $listing->listing_lat,
				'longitude' => (float) $listing->listing_lng,
			);
		}
		// getReviewStats() returns an ARRAY keyed 5..1, so the old
		// is_object()/->total_reviews test could never be true and no listing
		// ever emitted an aggregateRating. Use the totals derived above.
		if ($review_total > 0 && $review_avg > 0) {
			$ld['aggregateRating'] = array(
				'@type'       => 'AggregateRating',
				'ratingValue' => round($review_avg, 1),
				'reviewCount' => (int) $review_total,
			);
		}
		$data['json_ld'] = json_ld($ld);

		// Ad zones — same `plans.slug` vocabulary as the rest of the site, so
		// a purchased package renders here too (migrations 0017 + 0019).
		$data['ad_slots'] = array(
			'top'      => 'top-banner-site-wide',
			'inline'   => 'middle-banner-category',
			'side_1'   => 'right-side-banner-1',
			'side_2'   => 'right-side-banner-2',
			'side_sky' => 'right-panel-skyscraper',
			'bottom'   => 'bottom-banner-site-wide',
		);
		// Legacy 0006 zones and the category-priced directory zones kept
		// alive as aliases — see all_listing().
		$this->usermodel->primeAdSlots(array_merge(
			array_values($data['ad_slots']),
			array('listing-top', 'listing-inline', 'listing-sidebar',
			      'right-banner-1', 'right-banner-2')
		));

		// See all_listing() — the promo cards are shared with the homepage.
		$data['page_css'] = array('assets/css/directory.css', 'assets/css/promo-cards.css');

		// Load views
		$this->load->view('header', $data);
		$this->load->view('listing_details', $data);
		$this->load->view('footer');
	}

	/*===================
		Video Details Page
		URL: /user/video_details/slug
	===================*/
	public function video_details($slug)
	{
		if (!$this->requireService('service_videos_enabled')) return;

		$data = array();
		// Get video details by slug, falling back to the id so a link built
		// before slugs existed still resolves.
		$data['video'] = $this->usermodel->getSingleValue($slug, 'video_slug', 'video');
		if (!$data['video'] && ctype_digit((string) $slug)) {
			$data['video'] = $this->usermodel->getSingleValue((int) $slug, 'video_id', 'video');
		}

		if (!$data['video']) {
			show_404();
			return;
		}

		$video = $data['video'];

		// Increment views count for this video
		$this->usermodel->incrementViews('video', 'video_id', $video->video_id);

		// The row comes from `video` alone, so the channel name has to be
		// looked up separately for the breadcrumb and the category link.
		if (!empty($video->video_category_id)) {
			$cat = $this->usermodel->getSingleValue($video->video_category_id, 'category_id', 'video_categories');
			if ($cat) {
				$video->category_name = $cat->category_name;
				$video->category_slug = isset($cat->category_slug) ? $cat->category_slug : '';
			}
		}

		// Get logged user ID for form prefilling
		$logged_id = NULL;
		$data['user'] = NULL;
		if ($this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$logged_id = $session['logged_id'];
			$data['user'] = $this->usermodel->getSingleValue($logged_id, 'user_id', 'user');
		}
		$data['logged_id'] = $logged_id;

		$video_key   = !empty($video->video_slug) ? $video->video_slug : (string) $video->video_id;
		$video_url   = base_url('video-details/' . rawurlencode($video_key));
		$video_title = (string) $video->video_title;
		$video_image = !empty($video->video_image)
			? base_url($video->video_image)
			: youtube_thumb_url($video->video_code ?? '', 'maxresdefault');

		// Kept for any caller still reading it; the view rebuilds its own
		// against the canonical short URL.
		$data['share_urls'] = [
			'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($video_url),
			'twitter' => 'https://twitter.com/share?text=' . urlencode($video_title) . '&url=' . urlencode($video_url),
			'whatsapp' => 'https://wa.me/?text=' . urlencode($video_title . ' ' . $video_url),
			'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode($video_url),
			'pinterest' => 'https://www.pinterest.com/pin/create/bookmarklet/?media=' . urlencode($video_image) . '&url=' . urlencode($video_url) . '&description=' . urlencode($video_title),
		];

		// Related: same channel first, topped up from the whole library.
		$data['related_videos'] = $this->usermodel->getRelatedVideos(
			$video->video_id,
			3,
			!empty($video->video_category_id) ? (int) $video->video_category_id : NULL
		);
		$data['popular_videos'] = $this->usermodel->getPopularVideos(5, (int) $video->video_id);

		$data['ad_slots'] = $this->contentAdSlots();
		$this->usermodel->primeAdSlots(array_merge(
			array_values($data['ad_slots']),
			array('right-banner-1', 'right-banner-2')
		));
		$data['page_css'] = $this->contentPageCss();

		// SEO: per-page meta + VideoObject JSON-LD. Prefer the admin-set
		// video_seo_* columns; fall back to a trimmed description.
		$desc_auto = content_excerpt($video->video_description ?? '', 160);
		if ($desc_auto === '') $desc_auto = 'Watch "' . $video_title . '" on Pearland411.';

		$data['title'] = !empty($video->video_seo_title)
			? $video->video_seo_title
			: ($video_title . ' — Pearland411 Video');
		$data['meta_description'] = !empty($video->video_seo_description) ? $video->video_seo_description : $desc_auto;
		$data['meta_keywords']    = !empty($video->video_seo_keywords) ? $video->video_seo_keywords : '';
		$data['canonical_url']    = $video_url;
		if ($video_image !== '') $data['og_image'] = $video_image;

		$data['json_ld'] = json_ld(array(
			'@context'     => 'https://schema.org',
			'@type'        => 'VideoObject',
			'name'         => $video_title,
			'description'  => $data['meta_description'],
			'thumbnailUrl' => $video_image ?: null,
			'uploadDate'   => !empty($video->created_at) ? date('c', strtotime($video->created_at)) : null,
			'embedUrl'     => youtube_embed_url($video->video_code ?? '') ?: null,
			'url'          => $video_url,
			'publisher'    => array(
				'@type' => 'Organization',
				'name'  => 'Pearland411',
				'logo'  => array('@type' => 'ImageObject', 'url' => base_url('assets/images/pearland-logo.png')),
			),
		));

		// Load views
		$this->load->view('header', $data);
		$this->load->view('video_details', $data);
		$this->load->view('footer');
	}



	/**
	 * News Details - URL with hyphens 
	 * URL: /user/news-details/{slug_or_id}
	 */
	public function news_details($slug_or_id)
	{
		if (!$this->requireService('service_news_enabled')) return;

		$data = array();
		// Get news details by slug or ID
		if (is_numeric($slug_or_id)) {
			$data['news'] = $this->usermodel->getSingleValue($slug_or_id, 'news_id', 'news');
		} else {
			$data['news'] = $this->usermodel->getSingleValue($slug_or_id, 'news_slug', 'news');
		}

		if (!$data['news']) {
			show_404();
			return;
		}

		$news = $data['news'];

		// Increment views count for this news
		$this->usermodel->incrementViews('news', 'news_id', $news->news_id);

		// The row comes from `news` alone, so the section name has to be
		// looked up separately for the breadcrumb and the category link.
		if (!empty($news->news_category_id)) {
			$cat = $this->usermodel->getSingleValue($news->news_category_id, 'category_id', 'news_categories');
			if ($cat) {
				$news->category_name = $cat->category_name;
				$news->category_slug = isset($cat->category_slug) ? $cat->category_slug : '';
			}
		}

		// Byline. The view used to run this query itself, straight off
		// $this->db in the middle of the markup.
		// The byline field (migration 0035) first, then the account name.
		$data['news_author'] = trim((string) ($news->news_byline ?? ''));
		if ($data['news_author'] === '') $data['news_author'] = 'Pearland411';
		if (empty($news->news_byline) && !empty($news->user_id)) {
			$author = $this->usermodel->getSingleValue($news->user_id, 'user_id', 'user');
			if ($author && !empty($author->name)) $data['news_author'] = $author->name;
		}

		// Get logged user ID for form prefilling
		$logged_id = NULL;
		$data['user'] = NULL;
		if ($this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$logged_id = $session['logged_id'];
			$data['user'] = $this->usermodel->getSingleValue($logged_id, 'user_id', 'user');
		}
		$data['logged_id'] = $logged_id;

		// The canonical URL is the short one. It used to be the long
		// /user/news-details/… form, which is what every share button and the
		// JSON-LD then pointed at.
		$news_key   = !empty($news->news_slug) ? $news->news_slug : (string) $news->news_id;
		$news_url   = base_url('news-details/' . rawurlencode($news_key));
		$news_title = (string) $news->news_title;
		$news_image = !empty($news->news_image) ? base_url($news->news_image) : '';

		// Kept for any caller still reading it; the view rebuilds its own.
		$data['share_urls'] = [
			'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($news_url),
			'twitter' => 'https://twitter.com/share?text=' . urlencode($news_title) . '&url=' . urlencode($news_url),
			'whatsapp' => 'https://wa.me/?text=' . urlencode($news_title . ' ' . $news_url),
			'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode($news_url),
			'pinterest' => 'https://www.pinterest.com/pin/create/bookmarklet/?media=' . urlencode($news_image) . '&url=' . urlencode($news_url) . '&description=' . urlencode($news_title),
		];

		// Related: same section first, topped up from the whole newsroom.
		$data['related_news'] = $this->usermodel->getRelatedNews(
			$news->news_id,
			3,
			!empty($news->news_category_id) ? (int) $news->news_category_id : NULL
		);
		$data['popular_news'] = $this->usermodel->getPopularNews(5, (int) $news->news_id);

		$data['ad_slots'] = $this->contentAdSlots();
		$this->usermodel->primeAdSlots(array_merge(
			array_values($data['ad_slots']),
			array('news-sidebar', 'right-banner-1', 'right-banner-2')
		));
		$data['page_css'] = $this->contentPageCss();

		// SEO: per-page meta + NewsArticle JSON-LD. Prefer admin-set
		// news_seo_* columns; fall back to trimmed body.
		$desc_auto = content_excerpt($news->news_description ?? '', 160);
		if ($desc_auto === '') $desc_auto = 'News story: ' . $news_title;

		$data['title']            = !empty($news->news_seo_title)
			? $news->news_seo_title
			: ($news_title . ' — Pearland411');
		$data['meta_description'] = !empty($news->news_seo_description)
			? $news->news_seo_description
			: $desc_auto;
		$data['meta_keywords']    = !empty($news->news_seo_keywords)
			? $news->news_seo_keywords
			: '';
		$data['canonical_url']    = $news_url;
		if ($news_image !== '') $data['og_image'] = $news_image;

		$data['json_ld'] = json_ld(array(
			'@context'      => 'https://schema.org',
			'@type'         => 'NewsArticle',
			'headline'      => $news_title,
			'description'   => $data['meta_description'],
			'datePublished' => !empty($news->created_at) ? date('c', strtotime($news->created_at)) : null,
			'author'        => array('@type' => 'Person', 'name' => $data['news_author']),
			'image'         => $news_image ?: null,
			'mainEntityOfPage' => $news_url,
			'publisher'     => array(
				'@type' => 'Organization',
				'name'  => 'Pearland411',
				'logo'  => array('@type' => 'ImageObject', 'url' => base_url('assets/images/pearland-logo.png')),
			),
		));

		$this->loadHeaderCategories($data);

		// Load views
		$this->load->view('header', $data);
		$this->load->view('news_details', $data);
		$this->load->view('footer');
	}






	/*Edit Listing*/



	public function edit_listing_step_1($inserted_id)

	{

		$data['categories'] = $this->usermodel->getAllValues('listing_categories');

		$data['listing_id'] = $inserted_id;

		$data['listing'] = $this->usermodel->getSingleValue($inserted_id, 'listing_id', 'listings');

		// Ownership guard — only the listing owner may edit it.
		if (!$this->requireOwnedListing($data['listing'])) return;





		if ($this->form_validation->run('listing_step_1') == FALSE) {

			$this->load->view('header');

			$this->load->view('edit_listing_step_1', $data);

			$this->load->view('footer');
		} else {

			$info = array(

				'listing_name' 		=> $this->input->post('listing_name'),

				'listing_mobile'	=> $this->input->post('listing_mobile'),

				'listing_email'		=> $this->input->post('listing_email'),

				'listing_whatsapp'	=> $this->input->post('listing_whatsapp'),

				'listing_website'	=> $this->input->post('listing_website'),

				'listing_address'	=> $this->input->post('listing_address'),

				'listing_lat'		=> $this->input->post('listing_lat'),

				'listing_lng'		=> $this->input->post('listing_lng'),

				'country_id'		=> $this->input->post('country_id'),

				'city_name'			=> $this->input->post('city_name'),

				'category_id'		=> $this->input->post('category_id'),

				// 'sub_category_id'=>

				'listing_description' => $this->input->post('listing_description'),

				'service_locations' => $this->input->post('service_locations'),

			);

			if ($_FILES && $_FILES['profile_image']['name']) {

				$img_info = [

					'upload_path' => 'images/listings',

					'quality'     => '60%',

					'height'      => 500,

					'width'       => 500,

					'image_name'  => 'profile_image'

				];

				$info['profile_image'] = uploadAndResize($img_info);
			}

			if ($_FILES && $_FILES['cover_image']['name']) {

				$img_info = [

					'upload_path' => 'images/listings',

					'quality'     => '60%',

					'height'      => 400,

					'width'       => 1000,

					'image_name'  => 'cover_image'

				];

				$info['cover_image'] = uploadAndResize($img_info);
			}



			// $inserted_id = $this->usermodel->addListing('listings',$info);

			if ($this->usermodel->update($inserted_id, $info, 'listing_id', 'listings')) {

				// $this->session->set_flashdata('message', 'Successfully Added Listing!!');

				redirect('user/user_listing');
			} else {

				// $this->session->set_flashdata('message', 'Adding Listing Failed!!');

				redirect('user/edit-listing-step-1');
			}
		}
	}



	public function edit_listing_step_2($inserted_id = NULL)

	{

		$data['listing_id'] = $inserted_id;

		$data['listing'] = $this->usermodel->getSingleValue($inserted_id, 'listing_id', 'listings');

		// Ownership guard — only the listing owner may edit it.
		if (!$this->requireOwnedListing($data['listing'])) return;



		if ($this->form_validation->run('listing_step_2') == FALSE) {

			$this->load->view('header');

			$this->load->view('edit_listing_step_2', $data);

			$this->load->view('footer');
		} else {

			$inserted_id = $this->input->post('listing_id');

			$info = array(

				'service_name' 		=> $this->input->post('service_name'),

			);

			if ($_FILES && $_FILES['service_image']['name']) {

				$img_info = [

					'upload_path' => 'images/listings',

					'quality'     => '60%',

					'height'      => 500,

					'width'       => 500,

					'image_name'  => 'service_image'

				];

				$info['service_image'] = uploadAndResize($img_info);
			}



			if ($this->usermodel->update($inserted_id, $info, 'listing_id', 'listings')) {

				redirect('user/user_listing');
			} else {

				redirect('user/edit-listing-step-2/' . $inserted_id);
			}
		}
	}



	public function edit_listing_step_3($inserted_id = NULL)

	{

		$data['listing_id'] = $inserted_id;

		$data['listing'] = $this->usermodel->getSingleValue($inserted_id, 'listing_id', 'listings');

		// Ownership guard — only the listing owner may edit it.
		if (!$this->requireOwnedListing($data['listing'])) return;



		if ($this->form_validation->run('listing_step_3') == FALSE) {

			$this->load->view('header');

			$this->load->view('edit_listing_step_3', $data);

			$this->load->view('footer');
		} else {

			$inserted_id = $this->input->post('listing_id');



			$info = array(

				'service_1_name' 		=> $this->input->post('service_1_name'),

				'service_1_price' 		=> $this->input->post('service_1_price'),

				'service_1_detail' 		=> $this->input->post('service_1_detail'),

				'service_1_view_more' 	=> $this->input->post('service_1_view_more'),

			);

			if ($_FILES && $_FILES['service_1_image']['name']) {

				$img_info = [

					'upload_path' => 'images/listings',

					'quality'     => '60%',

					'height'      => 400,

					'width'       => 1000,

					'image_name'  => 'service_1_image'

				];

				$info['service_1_image'] = uploadAndResize($img_info);
			}



			if ($this->usermodel->update($inserted_id, $info, 'listing_id', 'listings')) {

				redirect('user/user_listing');
			} else {

				redirect('user/edit-listing-step-3/' . $inserted_id);
			}
		}
	}



	public function edit_listing_step_4($inserted_id = NULL)

	{

		$data['listing_id'] = $inserted_id;

		$data['listing'] = $this->usermodel->getSingleValue($inserted_id, 'listing_id', 'listings');

		// Ownership guard — only the listing owner may edit it.
		if (!$this->requireOwnedListing($data['listing'])) return;



		if ($this->form_validation->run('listing_step_4') == FALSE) {

			$this->load->view('header');

			$this->load->view('edit_listing_step_4', $data);

			$this->load->view('footer');
		} else {

			$inserted_id = $this->input->post('listing_id');





			$info = array(

				'google_map' 	=> $this->input->post('google_map'),

				'360_view' 		=> $this->input->post('360_view'),

			);

			if (!empty($this->input->post('listing_video'))) {

				$info['listing_video'] = json_encode($this->input->post('listing_video'));
			}



			if ($_FILES && $_FILES['gallery_image']['name']) {

				$img_info = [

					'upload_path' => 'images/listings',

					'quality'     => '60%',

					'height'      => 500,

					'width'       => 500,

					'image_name'  => 'gallery_image'

				];

				$info['gallery_image'] = uploadMultipleAndResize($img_info);
				if (upload_last_error() !== '') {
					$this->session->set_flashdata('error', upload_last_error());
				}
			}



			if ($this->usermodel->update($inserted_id, $info, 'listing_id', 'listings')) {

				redirect('user/user_listing');
			} else {

				redirect('user/edit-listing-step-4/' . $inserted_id);
			}
		}
	}



	public function edit_listing_step_5($inserted_id = NULL)

	{

		$data['listing_id'] = $inserted_id;

		$data['listing'] = $this->usermodel->getSingleValue($inserted_id, 'listing_id', 'listings');

		// Ownership guard — only the listing owner may edit it.
		if (!$this->requireOwnedListing($data['listing'])) return;



		if ($this->form_validation->run('listing_step_5') == FALSE) {

			$this->load->view('header');

			$this->load->view('edit_listing_step_5', $data);

			$this->load->view('footer');
		} else {

			$info = array();

			$inserted_id = $this->input->post('listing_id');



			if (!empty($this->input->post('listing_info_question'))) {

				$info['listing_info_question'] = json_encode($this->input->post('listing_info_question'));
			}



			if (!empty($this->input->post('listing_info_answer'))) {

				$info['listing_info_answer'] = json_encode($this->input->post('listing_info_answer'));
			}

			// echo "<pre>";

			// var_dump($info);

			// die();

			redirect('user/add-listing-step-6/' . $inserted_id);

			// if ($this->usermodel->update($inserted_id, $info, 'listing_id', 'listings')) {

			// 	redirect('user/add-listing-step-6/' . $inserted_id);

			// }

			// else 

			// {

			// 	redirect('user/add-listing-step-5/' . $inserted_id);

			// }	

		}
	}

	public function add_new_listing_start()
	{
		if (!$this->session->userdata('logged_user')) {
			redirect('user/login');
			return;
		}
		$session = $this->session->userdata('logged_user');
		$logged_id = $session['logged_id'];

		// Get all listings for this user for duplicate option
		$listings = $this->usermodel->getListingById($logged_id);
		$data = array();
		$data['listings'] = array();
		if ($listings) {
			foreach ($listings as $l) {
				$data['listings'][] = array(
					'listing_id' => $l->listing_id,
					'listing_name' => $l->listing_name
				);
			}
		}

		// Add categories for the form
		$data['categories'] = $this->usermodel->getAllValues('listing_categories');

		$this->load->view('header');
		$this->load->view('add_new_listing_start', $data);
		$this->load->view('footer');
	}

	/*Get Sub Categories via AJAX for Add Listing*/
	public function get_listing_sub_category()
	{
		$category_id = $this->input->post('category_id');

		if ($category_id) {
			$sub_categories = $this->adminmodel->ListingSubCategoryByCateId($category_id);

			if ($sub_categories) {
				echo '<option value="">Select Sub Category</option>';
				foreach ($sub_categories as $sub_category) {
					echo '<option value="' . $sub_category->sub_category_id . '">' . $sub_category->sub_category_name . '</option>';
				}
			} else {
				echo '<option value="">No Sub Categories Available</option>';
			}
		} else {
			echo '<option value="">Select Category First</option>';
		}
	}

	/*Test Listing Details URL*/


	/*======================
	    USER LISTING
	======================*/

	/*All Listing*/
	public function user_listing()
	{
		$data['logged_id'] = null;
		if ($this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$data['logged_id'] = $session['logged_id'];
		}

		// Subscription status and limits
		$data['user_subscription'] = $this->usermodel->getUserSubscription($data['logged_id']);
		$data['has_subscription'] = !empty($data['user_subscription']);

		if ($data['has_subscription']) {
			$data['subscription_limits'] = $this->usermodel->getSubscriptionLimits($data['logged_id']);
			$data['usage_counts'] = $this->usermodel->getAllUsageCounts($data['logged_id']);
			$data['can_add_listing'] = $this->usermodel->canUserAddContent($data['logged_id'], 'listing');
			$data['current_listing_count'] = $this->usermodel->getUserUsage($data['logged_id'], 'listing');
		} else {
			$data['subscription_limits'] = null;
			$data['usage_counts'] = [];
			$data['can_add_listing'] = false;
			$data['current_listing_count'] = 0;
		}

		// Pagination config (optional, for future use)
		$config = [
			'base_url' => base_url('user/user-listing'),
			'per_page' => 10,
			'total_rows' => count($this->usermodel->getListingById($data['logged_id'], null, null)),
			'uri_segment' => 3,
		];
		$config['full_tag_open'] = '<ul class="pagination">';
		$config['full_tag_close'] = '</ul>';
		$config['cur_tag_open'] = '<li class="page-item active"><a href="#" class="page-link">';
		$config['cur_tag_close'] = '</a></li>';
		$config['prev_tag_open'] = '<li class="page-link">';
		$config['prev_tag_close'] = '</li>';
		$config['next_tag_open'] = '<li class="page-link">';
		$config['next_tag_close'] = '</li>';
		$config['num_tag_open'] = '<li class="page-link">';
		$config['num_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li class="page-link">';
		$config['last_tag_close'] = '</li>';
		$config['first_tag_open'] = '<li class="page-link">';
		$config['first_tag_close'] = '</li>';
		$config['next_link'] = 'Next >';
		$config['prev_link'] = '< Prev';

		$this->pagination->initialize($config);
		$page = (int) ($this->uri->segment(3)) ? (int) ($this->uri->segment(3)) : 0;

		// Listings for this user
		$data['AllListings'] = $this->usermodel->getListingById($data['logged_id'], $config['per_page'], $page);
		// Add views count for each listing
		if (!empty($data['AllListings'])) {
			foreach ($data['AllListings'] as $key => $listing) {
				$data['AllListings'][$key]->views = isset($listing->views) ? $listing->views : 0;
			}
		}
		$data['start_number'] = $page + 1;
		$data['per_page'] = $config['per_page'];
		$data['total_rows'] = $config['total_rows']; // Pass total count to view
		$data['link'] = $this->pagination->create_links();

		$data['title'] = 'My Listings';

		if (file_exists(APPPATH . 'views/user_listing.php')) {
			$this->load->view('header', $data);
			$this->load->view('user_left_sidebar');
			$this->load->view('user_listing', $data);
			$this->load->view('user_right_sidebar');
			$this->load->view('footer');
		} else {
			$this->load->view('header', $data);
			$this->load->view('user_left_sidebar');
			echo '<div class="container"><h2>Listings</h2><p>No listing view template found (expected user_listing.php).</p></div>';
			$this->load->view('user_right_sidebar');
			$this->load->view('footer');
		}
	}

	/*====================
		Add New Listing
	=====================*/
	public function add_listing_scratch()
	{
		// Check if user is logged in
		$logged_id = NULL;
		if ($this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$logged_id = $session['logged_id'];
		}

		// Check subscription limits before allowing to add listing
		if (!$this->usermodel->canUserAddContent($logged_id, 'listing')) {
			$this->session->set_flashdata('error_message', 'You have reached your listing limit. Please upgrade your plan to add more listings.');
			redirect('user/user-listing');
			return;
		}

		$data['categories'] = $this->usermodel->getAllValues('listing_categories');

		// Debug: Always show if POST data exists
		if ($_POST) {
			$data['debug_message'] = "POST data received. Keys: " . implode(', ', array_keys($_POST)) . " | User ID: " . ($logged_id ? $logged_id : 'NULL');
		}

		// Check for form submission - since POST data exists with form fields, it's a form submission
		if ($_POST && (isset($_POST['listing_name']) || isset($_POST['category_id']))) {

			// Force show success message for testing
			$data['success_debug'] = "Form submission detected successfully!";

			// Form submitted, validate required fields
			$listing_name = $this->input->post('listing_name');
			$category_id = $this->input->post('category_id');
			$listing_address = $this->input->post('listing_address');

			// Debug: Check if form data is being received
			if (empty($listing_name) && empty($category_id) && empty($listing_address)) {
				$data['error'] = 'No form data received. Form data: ' . json_encode($post_data);
				$this->load->view('header', $data);
				$this->load->view('add_new_listing_start', $data);
				$this->load->view('footer');
				return;
			}

			if (empty($listing_name) || empty($category_id) || empty($listing_address)) {
				$data['error'] = 'Please fill in all required fields (Listing Name, Category, Address)';
				$this->load->view('header', $data);
				$this->load->view('add_new_listing_start', $data);
				$this->load->view('footer');
			} else {
				// Form validation passed, process the listing
				$listing_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('listing_name'));

				// Check if user is logged in properly
				if (!$logged_id) {
					$data['error'] = 'User not logged in properly. Please login again.';
					$this->load->view('header', $data);
					$this->load->view('add_new_listing_start', $data);
					$this->load->view('footer');
					return;
				}

				// First try with minimal required fields only
				$info = array(
					'user_id'			=> intval($logged_id),
					'listing_name'		=> trim($listing_name),
					'category_id'		=> intval($category_id),
					'listing_address'	=> trim($listing_address),
					'listing_slug' 		=> trim($listing_slug),
					// listing_status is an INT column. This wrote the string
					// 'Active', which MySQL coerced to 0 — i.e. unpublished —
					// so every business submitted through this form was
					// invisible. 1 = published; see
					// Usermodel::applyPublishedListingFilter().
					'listing_status' 	=> 1
				);

				// Add optional fields if they exist
				if ($this->input->post('sub_category_id')) {
					$info['sub_category_id'] = $this->input->post('sub_category_id');
				}
				if ($this->input->post('listing_mobile')) {
					$info['listing_mobile'] = $this->input->post('listing_mobile');
				}
				if ($this->input->post('listing_email')) {
					$info['listing_email'] = $this->input->post('listing_email');
				}
				if ($this->input->post('listing_description')) {
					$info['listing_description'] = $this->input->post('listing_description');
				}
				if ($this->input->post('service_locations')) {
					$info['service_locations'] = $this->input->post('service_locations');
				}
				if ($this->input->post('service_offered')) {
					$info['service_offered'] = $this->input->post('service_offered');
				}
				if ($this->input->post('listing_whatsapp')) {
					$info['listing_whatsapp'] = $this->input->post('listing_whatsapp');
				}
				if ($this->input->post('display_phone')) {
					$info['display_phone'] = $this->input->post('display_phone');
				}
				if ($this->input->post('display_address')) {
					$info['display_address'] = $this->input->post('display_address');
				}
				if ($this->input->post('county_name')) {
					$info['county_name'] = $this->input->post('county_name');
				}
				if ($this->input->post('postal_code')) {
					$info['postal_code'] = $this->input->post('postal_code');
				}
				if ($this->input->post('listing_lat')) {
					$info['listing_lat'] = $this->input->post('listing_lat');
				}
				if ($this->input->post('listing_lng')) {
					$info['listing_lng'] = $this->input->post('listing_lng');
				}
				if ($this->input->post('listing_website')) {
					$info['listing_website'] = $this->input->post('listing_website');
				}
				if ($this->input->post('listing_video')) {
					$info['listing_video'] = $this->input->post('listing_video');
				}
				if ($this->input->post('listing_seo_title')) {
					$info['listing_seo_title'] = $this->input->post('listing_seo_title');
				}
				if ($this->input->post('listing_seo_keywords')) {
					$info['listing_seo_keywords'] = $this->input->post('listing_seo_keywords');
				}
				if ($this->input->post('listing_seo_description')) {
					$info['listing_seo_description'] = $this->input->post('listing_seo_description');
				}

				if (!empty($_FILES['listing_profile_image']['name'])) {
					$img_info = [
						'upload_path' => 'images/listings',
						'quality'     => '60%',
						'height'      => 500,
						'width'       => 500,
						'image_name'  => 'listing_profile_image'
					];

					// Never write NULL over a stored logo because an upload failed.
					$new_logo = uploadAndResize($img_info);
					if ($new_logo) {
						$info['listing_image'] = $new_logo;
					}
				}

				// Handle Gallery Images Upload for User
				if ($_FILES && isset($_FILES['listing_image_gallery']['name'][0]) && !empty($_FILES['listing_image_gallery']['name'][0])) {
					$gallery_images = [];
					$files_count = count($_FILES['listing_image_gallery']['name']);

					// Debug: Log gallery upload attempt
					error_log("USER Gallery upload attempt - Files count: " . $files_count);

					for ($i = 0; $i < $files_count; $i++) {
						if (!empty($_FILES['listing_image_gallery']['name'][$i])) {
							// Debug: Log each file
							error_log("USER Processing gallery file: " . $_FILES['listing_image_gallery']['name'][$i]);

							// Create temporary file array for each image
							$_FILES['gallery_temp'] = array(
								'name' => $_FILES['listing_image_gallery']['name'][$i],
								'type' => $_FILES['listing_image_gallery']['type'][$i],
								'tmp_name' => $_FILES['listing_image_gallery']['tmp_name'][$i],
								'error' => $_FILES['listing_image_gallery']['error'][$i],
								'size' => $_FILES['listing_image_gallery']['size'][$i]
							);

							// Use same path as logo image - images/listings
							$gallery_img_info = [
								'upload_path' => 'images/listings',
								'quality'     => '60%',
								'image_name'  => 'gallery_temp'
							];
							$uploaded_image = uploadWithoutResize($gallery_img_info);
							if ($uploaded_image) {
								// Store path with assets prefix like logo image
								$gallery_images[] = 'assets/images/listings/' . basename($uploaded_image);
								error_log("USER Gallery image uploaded: " . $uploaded_image);
							} else {
								error_log("USER Gallery image upload failed for: " . $_FILES['listing_image_gallery']['name'][$i]);
							}
						}
					}
					if (!empty($gallery_images)) {
						$info['listing_image_gallery'] = json_encode($gallery_images);
						error_log("USER Gallery images stored in DB: " . $info['listing_image_gallery']);
					} else {
						error_log("USER No gallery images to store");
					}
				} else {
					error_log("USER No gallery files detected in upload");
				}

				// Debug: Show data being inserted with full info array
				$service_locations_debug = $this->input->post('service_locations') ? 'Present: "' . $this->input->post('service_locations') . '"' : 'MISSING';
				$service_offered_debug = $this->input->post('service_offered') ? 'Present: "' . $this->input->post('service_offered') . '"' : 'MISSING';

				$data['insert_debug'] = "SERVICE FIELDS DEBUG - service_locations: " . $service_locations_debug . " | service_offered: " . $service_offered_debug . "<br><br>Full data: " . json_encode($info, JSON_PRETTY_PRINT);

				// Use direct database insert to avoid model method issues
				$add_result = false;
				$error_msg = '';

				try {
					// Direct database insert
					$this->db->insert('listings', $info);
					$add_result = $this->db->affected_rows() > 0;

					if ($add_result) {
						$error_msg = "SUCCESS: Listing inserted with ID " . $this->db->insert_id();
					} else {
						$db_error = $this->db->error();
						$error_msg = "INSERT FAILED - Affected rows: " . $this->db->affected_rows() .
							". DB Error: " . $db_error['message'] .
							". Last Query: " . $this->db->last_query();
					}
				} catch (Exception $e) {
					$error_msg = "EXCEPTION: " . $e->getMessage();
				}

				$data['db_debug'] = $error_msg;

				if ($add_result) {
					// Update usage tracking after successful addition
					if (method_exists($this->usermodel, 'updateUserUsage')) {
						$this->usermodel->updateUserUsage($logged_id, 'listing');
					}

					$this->session->set_flashdata('message', 'Listing Added Successfully.');
					redirect('user/user-listing', 'refresh');
				} else {
					$this->session->set_flashdata('error', 'Failed to add listing. Please check your data and try again.');
					redirect('user/add-listing-scratch', 'refresh');
				}
			}
		} else {
			// Initial page load - show the form
			$this->load->view('header', $data);
			$this->load->view('add_new_listing_start', $data);
			$this->load->view('footer');
		}
	}




	/*====================
		Add New Listing
	=====================*/
	// public function add_listing()
	// {
	// 	// Check if user is logged in
	// 	$logged_id = NULL;
	// 	if ($this->session->userdata('logged_user')) {
	// 		$session = $this->session->userdata('logged_user');
	// 		$logged_id = $session['logged_id'];
	// 	}

	// 	// Check subscription limits before allowing to add listing
	// 	if (!$this->usermodel->canUserAddContent($logged_id, 'listing')) {
	// 		$this->session->set_flashdata('error_message', 'You have reached your listing limit. Please upgrade your plan to add more listings.');
	// 		redirect('user/user-listing');
	// 		return;
	// 	}

	// 	$data['categories'] = $this->usermodel->getAllValues('listing_categories');

	// 	if ($this->form_validation->run('user_listing') == FALSE) {
	// 		$this->load->view('header', $data);
	// 		$this->load->view('user_left_sidebar');
	// 		$this->load->view('user_add_new_listing');
	// 		$this->load->view('user_right_sidebar');
	// 		$this->load->view('footer');
	// 	} else {
	// 		$listing_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('listing_name'));

	// 		$info = array(
	// 			'listing_name'		=> $this->input->post('listing_name'),
	// 			'category_id'		=> $this->input->post('category_id'),
	// 			'sub_category_id'	=> $this->input->post('sub_category_id'),
	// 			'listing_mobile'	=> $this->input->post('listing_mobile'),
	// 			'listing_whatsapp'	=> $this->input->post('listing_whatsapp'),
	// 			'display_phone'		=> $this->input->post('display_phone'),
	// 			'listing_email'		=> $this->input->post('listing_email'),
	// 			'listing_website'	=> $this->input->post('listing_website'),
	// 			'listing_address'	=> $this->input->post('listing_address'),
	// 			'county_name'		=> $this->input->post('county_name'),
	// 			'postal_code'		=> $this->input->post('postal_code'),
	// 			'listing_lat'		=> $this->input->post('listing_lat'),
	// 			'listing_lng'		=> $this->input->post('listing_lng'),
	// 			'display_address'	=> $this->input->post('display_address'),
	// 			'listing_description' => $this->input->post('listing_description'),
	// 			'service_locations'	=> $this->input->post('service_locations'),
	// 			'service_offered'	=> $this->input->post('service_offered'),
	// 			'listing_video'		=> $this->input->post('listing_video'),
	// 			'listing_seo_title' => $this->input->post('listing_seo_title'),
	// 			'listing_seo_keywords' => $this->input->post('listing_seo_keywords'),
	// 			'listing_seo_description' => $this->input->post('listing_seo_description'),
	// 			'listing_slug' => $listing_slug
	// 		);

	// 		if ($_FILES && $_FILES['listing_profile_image']['name']) {
	// 			$img_info = [
	// 				'upload_path' => 'images/listings',
	// 				'quality'     => '60%',
	// 				'height'      => 500,
	// 				'width'       => 500,
	// 				'image_name'  => 'listing_profile_image'
	// 			];

	// 			$info['listing_image'] = uploadAndResize($img_info);
	// 		}

	// 		if ($this->usermodel->addListing($info)) {
	// 			// Update usage tracking after successful addition
	// 			$this->usermodel->updateUserUsage($logged_id, 'listing');

	// 			$this->session->set_flashdata('message', 'Lisitng Added Successfully.');
	// 			redirect('user/user-listing', 'refresh');
	// 		} else {
	// 			redirect('user/add-listing', 'refresh');
	// 		}
	// 	}
	// }


	/*===================
		Edit Listing
	===================*/
	public function edit_listing($slug)
	{
		$data['categories'] 	= $this->usermodel->getAllValues('listing_categories');
		$data['listing_slug']	= $slug;
		$data['listing'] 		= $this->usermodel->getSingleValue($slug, 'listing_slug', 'listings');

		// Ownership guard — a logged-in user may only edit a listing they own.
		// (Login itself is already enforced by the constructor whitelist.)
		if (!$this->requireOwnedListing($data['listing'])) return;

		// Field-level capabilities from the owner's plan (freemium gating).
		// Exposed to the view so locked fields render disabled + "upgrade".
		$caps = $this->listingCapabilities($data['listing']->user_id);
		$data['caps'] = $caps;

		// The logo and gallery fields render through the shared media_field
		// partial, whose styling lives here. Set before either view load below.
		$data['page_css'] = array('assets/admin/css/media-preview.css');

		if ($data['listing']->category_id != '') {
			$data['sub_category_listing'] = $this->adminmodel->ListingSubCategoryByCateId($data['listing']->category_id);
		}

		// Check if form is submitted (try multiple ways)
		if ($this->input->post('listing_submit') || $this->input->post('submit') || $_POST) {
			// Form was submitted, process update without strict validation for now

			// Simple validation - just check if required fields exist
			$listing_name = $this->input->post('listing_name');
			$category_id = $this->input->post('category_id');
			$listing_mobile = $this->input->post('listing_mobile');

			if (empty($listing_name) || empty($category_id) || empty($listing_mobile)) {
				// Required fields missing, show form with error
				$data['error'] = 'Please fill in all required fields (Listing Name, Category, Phone)';
				$this->load->view('header', $data);
				$this->load->view('edit_listings', $data);
				$this->load->view('footer');
			} else {
				// Validation passed, process the update
				$listing_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('listing_name'));

				$info = array(
					'listing_name'		=> $this->input->post('listing_name'),
					'category_id'		=> $this->input->post('category_id'),
					'sub_category_id'	=> $this->input->post('sub_category_id'),
					'listing_mobile'	=> $this->input->post('listing_mobile'),
					'listing_whatsapp'	=> $this->input->post('listing_whatsapp'),
					'display_phone'		=> $this->input->post('display_phone'),
					'listing_email'		=> $this->input->post('listing_email'),
					'listing_website'	=> $this->input->post('listing_website'),
					'listing_address'	=> $this->input->post('listing_address'),
					'county_name'		=> $this->input->post('county_name'),
					'postal_code'		=> $this->input->post('postal_code'),
					'listing_lat'		=> $this->input->post('listing_lat'),
					'listing_lng'		=> $this->input->post('listing_lng'),
					'display_address'	=> $this->input->post('display_address'),
					'listing_description' => $this->input->post('listing_description'),
					'service_locations'	=> $this->input->post('service_locations'),
					'service_offered'	=> $this->input->post('service_offered'),
					'listing_video'		=> $this->input->post('listing_video'),
					'listing_seo_title' => $this->input->post('listing_seo_title'),
					'listing_seo_keywords' => $this->input->post('listing_seo_keywords'),
					'listing_seo_description' => $this->input->post('listing_seo_description'),
					'listing_slug' => $listing_slug
				);

				// Freemium gating — drop rich fields the owner's plan does not
				// permit so a crafted POST cannot bypass the locked inputs.
				if (!$caps['can_map'])           { unset($info['listing_lat'], $info['listing_lng']); }
				if (!$caps['can_services'])      { unset($info['service_offered']); }
				if (!$caps['can_video'])         { unset($info['listing_video']); }
				if (!$caps['can_extra_details']) { unset($info['listing_seo_title'], $info['listing_seo_keywords'], $info['listing_seo_description']); }

				// Only a successful upload replaces the logo. A refused file used
				// to be written as NULL over the stored logo.
				$logo_note = '';
				if ($caps['can_photos'] && !empty($_FILES['listing_profile_image']['name'])) {
					$img_info = [
						'upload_path' => 'images/listings',
						'quality'     => '60%',
						'height'      => 500,
						'width'       => 500,
						'image_name'  => 'listing_profile_image'
					];

					$new_logo = uploadAndResize($img_info);
					if ($new_logo) {
						$info['listing_image'] = $new_logo;
					} else {
						$logo_note = 'Your logo was not changed: ' . (upload_last_error() ?: 'the file could not be read.');
					}
				}

				// Handle Gallery Images Upload for User Edit.
				//
				// Adding a photo used to replace the whole gallery, because this
				// path wrote json_encode() of just the newly uploaded files and
				// never read the stored column. The admin screen was fixed for
				// this in the 2026-08-06 round; the owner's own form was not, so
				// an owner adding one photo silently lost the rest.
				//
				// Removal is read outside the can_photos gate on purpose: an
				// owner on a downgraded plan can no longer add photos, but must
				// still be able to delete ones already published.
				$existing_gallery = listing_gallery_paths($data['listing']->listing_image_gallery ?? null);
				$remove_gallery   = (array) $this->input->post('remove_gallery');
				$kept_gallery     = array_values(array_diff($existing_gallery, $remove_gallery));
				$gallery_changed  = (count($kept_gallery) !== count($existing_gallery));

				if ($caps['can_photos'] && $_FILES && isset($_FILES['listing_image_gallery']['name'][0]) && !empty($_FILES['listing_image_gallery']['name'][0])) {
					$gallery_images = [];
					$files_count = count($_FILES['listing_image_gallery']['name']);
					// Cap gallery photos to the plan's photo_limit (-1 = unlimited).
					// The limit is on the gallery, not on one upload: now that new
					// files are added to the kept ones rather than replacing them,
					// it has to be measured against what will remain after this
					// save, or an owner could pass the cap one upload at a time.
					if ($caps['photo_limit'] >= 0) {
						$slots_left = (int) $caps['photo_limit'] - count($kept_gallery);
						if ($slots_left < 0) { $slots_left = 0; }
						if ($files_count > $slots_left) { $files_count = $slots_left; }
					}

					// Debug: Log gallery upload attempt
					error_log("USER EDIT Gallery upload attempt - Files count: " . $files_count);

					for ($i = 0; $i < $files_count; $i++) {
						if (!empty($_FILES['listing_image_gallery']['name'][$i])) {
							// Debug: Log each file
							error_log("USER EDIT Processing gallery file: " . $_FILES['listing_image_gallery']['name'][$i]);

							// Create temporary file array for each image
							$_FILES['gallery_temp'] = array(
								'name' => $_FILES['listing_image_gallery']['name'][$i],
								'type' => $_FILES['listing_image_gallery']['type'][$i],
								'tmp_name' => $_FILES['listing_image_gallery']['tmp_name'][$i],
								'error' => $_FILES['listing_image_gallery']['error'][$i],
								'size' => $_FILES['listing_image_gallery']['size'][$i]
							);

							// Use same path as logo image - images/listings
							$gallery_img_info = [
								'upload_path' => 'images/listings',
								'quality'     => '60%',
								'image_name'  => 'gallery_temp'
							];
							$uploaded_image = uploadWithoutResize($gallery_img_info);
							if ($uploaded_image) {
								// Store path with assets prefix like logo image
								$gallery_images[] = 'assets/images/listings/' . basename($uploaded_image);
							} else {
								// Said nothing before: the save reported success over a lost photo.
								$gallery_notes[] = 'Photo "' . $_FILES['listing_image_gallery']['name'][$i] . '" was not added: '
									. (upload_last_error() ?: 'the file could not be read.');
							}
						}
					}
					if (!empty($gallery_images)) {
						$kept_gallery    = array_merge($kept_gallery, $gallery_images);
						$gallery_changed = TRUE;
						error_log("USER EDIT Gallery images added: " . count($gallery_images));
					} else {
						error_log("USER EDIT No gallery images to store");
					}
				} else {
					error_log("USER EDIT No gallery files detected in upload");
				}

				// Only write the column when something actually changed, so a save
				// that touches no photos leaves the gallery exactly as it was.
				if ($gallery_changed) {
					$info['listing_image_gallery'] = empty($kept_gallery)
						? NULL
						: json_encode(array_values($kept_gallery));
					error_log("USER EDIT Gallery stored in DB: " . var_export($info['listing_image_gallery'], TRUE));
				}

				// Debug: Service fields check for edit
				$service_locations_edit = $this->input->post('service_locations') ? 'Present: "' . $this->input->post('service_locations') . '"' : 'MISSING';
				$service_offered_edit = $this->input->post('service_offered') ? 'Present: "' . $this->input->post('service_offered') . '"' : 'MISSING';

				// Store debug info in session for display after redirect
				$this->session->set_flashdata('debug_info', "EDIT DEBUG - service_locations: " . $service_locations_edit . " | service_offered: " . $service_offered_edit);

				// Try to update the listing
				$update_result = $this->usermodel->update($slug, $info, 'listing_slug', 'listings');

				if ($update_result) {
					$notes = array_filter(array_merge(array($logo_note), isset($gallery_notes) ? $gallery_notes : array()));
					if ($notes) {
						$this->session->set_flashdata('error', 'Listing updated, but: ' . implode(' ', $notes));
					}
					$this->session->set_flashdata('message', 'Listing Updated Successfully.');
					redirect('user/user-listing', 'refresh');
				} else {
					$this->session->set_flashdata('error', 'Failed to update listing. Please try again.');
					redirect('user/edit-listing/' . $slug);
				}
			}
		} else {
			// Initial page load - just show the form (no sidebars as requested)
			$this->load->view('header', $data);
			$this->load->view('edit_listings', $data);
			$this->load->view('footer');
		}
	}

	/**
	 * All User Products Page (subscription-aware, paginated, like user_events)
	 */
	public function products()
	{
		$data['logged_id'] = null;
		if ($this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$data['logged_id'] = $session['logged_id'];
		}

		// Subscription status and limits
		$data['user_subscription'] = $this->usermodel->getUserSubscription($data['logged_id']);
		$data['has_subscription'] = !empty($data['user_subscription']);

		if ($data['has_subscription']) {
			$data['subscription_limits'] = $this->usermodel->getSubscriptionLimits($data['logged_id']);
			$data['usage_counts'] = $this->usermodel->getAllUsageCounts($data['logged_id']);
			$data['can_add_product'] = $this->usermodel->canUserAddContent($data['logged_id'], 'product');
			$data['current_product_count'] = $this->usermodel->getUserUsage($data['logged_id'], 'product');
		} else {
			$data['subscription_limits'] = null;
			$data['usage_counts'] = [];
			$data['can_add_product'] = false;
			$data['current_product_count'] = 0;
		}

		// Pagination config
		$config = [
			'base_url' => base_url('user/products'),
			'per_page' => 10,
			'total_rows' => $this->usermodel->countAllProducts(),
			'uri_segment' => 3,
		];
		$config['full_tag_open'] = '<ul class="pagination">';
		$config['full_tag_close'] = '</ul>';
		$config['cur_tag_open'] = '<li class="page-item active"><a href="#" class="page-link">';
		$config['cur_tag_close'] = '</a></li>';
		$config['prev_tag_open'] = '<li class="page-link">';
		$config['prev_tag_close'] = '</li>';
		$config['next_tag_open'] = '<li class="page-link">';
		$config['next_tag_close'] = '</li>';
		$config['num_tag_open'] = '<li class="page-link">';
		$config['num_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li class="page-link">';
		$config['last_tag_close'] = '</li>';
		$config['first_tag_open'] = '<li class="page-link">';
		$config['first_tag_close'] = '</li>';
		$config['next_link'] = 'Next >';
		$config['prev_link'] = '< Prev';

		$this->pagination->initialize($config);
		$page = (int) ($this->uri->segment(3)) ? (int) ($this->uri->segment(3)) : 0;

		// Products for this user
		$data['allProducts'] = $this->usermodel->getAllProduct($config['per_page'], $page);
		// Add views count for each product
		if (!empty($data['allProducts'])) {
			foreach ($data['allProducts'] as $key => $product) {
				$data['allProducts'][$key]->views = isset($product->views) ? $product->views : 0;
			}
		}
		$data['start_number'] = $page + 1;
		$data['per_page'] = $config['per_page'];
		$data['total_rows'] = $config['total_rows']; // Pass total count to view
		$data['link'] = $this->pagination->create_links();

		$data['title'] = 'My Products';

		if (file_exists(APPPATH . 'views/user_products.php')) {
			$this->load->view('header', $data);
			$this->load->view('user_left_sidebar');
			$this->load->view('user_products', $data);
			$this->load->view('user_right_sidebar');
			$this->load->view('footer');
		} else {
			$this->load->view('header', $data);
			$this->load->view('user_left_sidebar');
			echo '<div class="container"><h2>Products</h2><p>No product view template found (expected user_products.php).</p></div>';
			$this->load->view('user_right_sidebar');
			$this->load->view('footer');
		}
	}

	/*===================
		Edit Product
	===================*/
	public function edit_products($product_id)
	{
		// Check if user is logged in
		$logged_id = null;
		if ($this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$logged_id = $session['logged_id'];
		} else {
			redirect('user/login');
			return;
		}

		// Get product details
		$data['product'] = $this->usermodel->getSingleValue($product_id, 'product_id', 'products');

		if (!$data['product']) {
			$this->session->set_flashdata('message', 'Product not found!');
			redirect('user/products');
			return;
		}

		// Verify product belongs to logged in user
		if ($data['product']->user_id != $logged_id) {
			$this->session->set_flashdata('message', 'Access denied!');
			redirect('user/products');
			return;
		}

		// Get product categories for dropdown
		$data['productCategories'] = $this->usermodel->getAllValues('product_categories');

		// Get sub categories for selected category
		$data['sub_category_products'] = $this->usermodel->getAllValues('product_sub_categories');

		// Check if form is submitted (same pattern as edit_listing)
		if ($this->input->post('product_submit') || $this->input->post('submit') || $_POST) {
			// Form was submitted, process update

			// Simple validation - check if required fields exist
			$product_name = $this->input->post('product_name');
			$category_id = $this->input->post('category_id');
			$product_price = $this->input->post('product_price');

			if (empty($product_name) || empty($category_id) || empty($product_price)) {
				// Required fields missing, show form with error
				$data['error'] = 'Please fill in all required fields (Product Name, Category, Price)';
				$this->load->view('header', $data);
				$this->load->view('edit_product', $data);
				$this->load->view('footer');
			} else {
				// Validation passed, process the update
				$update_data = array(
					'product_name' => $this->input->post('product_name'),
					'category_id' => $this->input->post('category_id'),
					'sub_category_id' => $this->input->post('sub_category_id'),
					'product_price' => $this->input->post('product_price'),
					'product_price_offer' => $this->input->post('product_price_offer'),
					'product_payment_link' => $this->input->post('product_payment_link'),
					'product_description' => $this->input->post('product_description'),
					'product_tags' => $this->input->post('product_tags')
				);

				// Handle product highlights
				if (!empty($this->input->post('product_highlights'))) {
					$update_data['product_highlights'] = json_encode($this->input->post('product_highlights'));
				}

				// Handle specifications (questions and answers)
				if (!empty($this->input->post('product_info_question')) && !empty($this->input->post('product_info_answer'))) {
					$update_data['product_info_question'] = json_encode($this->input->post('product_info_question'));
					$update_data['product_info_answer'] = json_encode($this->input->post('product_info_answer'));
				}

				// Handle file upload for gallery images
				if ($_FILES && !empty($_FILES['gallery_image']['name'][0])) {
					$img_info = [
						'upload_path' => 'images/products',
						'quality'     => '60%',
						'height'      => 500,
						'width'       => 500,
						'image_name'  => 'gallery_image'
					];
					// Use uploadMultipleAndResize if available, otherwise skip
					if (function_exists('uploadMultipleAndResize')) {
						$update_data['gallery_image'] = uploadMultipleAndResize($img_info);
					}
				}

				// Try to update the product
				$gallery_note  = isset($update_data['gallery_image']) ? upload_last_error() : '';
				$update_result = $this->usermodel->update($product_id, $update_data, 'product_id', 'products');

				if ($update_result) {
					// Partial gallery failures used to be hidden behind this message.
					$this->session->set_flashdata('message', 'Product updated successfully!'
						. ($gallery_note !== '' ? ' Note: ' . $gallery_note : ''));
					redirect('user/products', 'refresh');
				} else {
					$this->session->set_flashdata('error', 'Failed to update product. Please try again.');
					redirect('user/edit_products/' . $product_id);
				}
			}
		} else {
			// Initial page load - just show the form
			$data['title'] = 'Edit Product';
			$this->load->view('header', $data);
			$this->load->view('edit_product', $data);
			$this->load->view('footer');
		}
	}

	/*===================
		Add New Product
	===================*/
	public function add_product()
	{
		// Check if user is logged in
		$logged_id = null;
		if ($this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$logged_id = $session['logged_id'];
		} else {
			redirect('user/login');
			return;
		}

		// Get product categories for dropdown
		$data['productCategories'] = $this->usermodel->getAllValues('product_categories');

		// Check if form is submitted
		if ($this->input->post('product_submit') || $_POST) {
			// Form was submitted, process add product

			// Simple validation - check if required fields exist
			$product_name = $this->input->post('product_name');
			$category_id = $this->input->post('category_id');
			$product_price = $this->input->post('product_price');
			$product_payment_link = $this->input->post('product_payment_link');
			$product_description = $this->input->post('product_description');

			if (empty($product_name) || empty($category_id) || empty($product_price) || empty($product_payment_link) || empty($product_description)) {
				// Required fields missing, show form with error
				$data['error'] = 'Please fill in all required fields (Product Name, Category, Price, Payment Link, Description)';
				$this->load->view('header', $data);
				$this->load->view('add_new_product', $data);
				$this->load->view('footer');
			} else {
				// Validation passed, process the add
				$insert_data = array(
					'user_id' => $logged_id,
					'product_name' => $this->input->post('product_name'),
					'category_id' => $this->input->post('category_id'),
					'sub_category_id' => $this->input->post('sub_category_id'),
					'product_price' => $this->input->post('product_price'),
					'product_price_offer' => $this->input->post('product_price_offer'),
					'product_payment_link' => $this->input->post('product_payment_link'),
					'product_description' => $this->input->post('product_description'),
					'product_tags' => $this->input->post('product_tags'),
					'created_at' => date('Y-m-d H:i:s')
				);

				// Handle product highlights
				if (!empty($this->input->post('product_highlights'))) {
					$insert_data['product_highlights'] = json_encode($this->input->post('product_highlights'));
				}

				// Handle specifications (questions and answers)
				if (!empty($this->input->post('product_info_question')) && !empty($this->input->post('product_info_answer'))) {
					$insert_data['product_info_question'] = json_encode($this->input->post('product_info_question'));
					$insert_data['product_info_answer'] = json_encode($this->input->post('product_info_answer'));
				}

				// Handle file upload for gallery images
				if ($_FILES && !empty($_FILES['gallery_image']['name'][0])) {
					$img_info = [
						'upload_path' => 'images/products',
						'quality'     => '60%',
						'height'      => 500,
						'width'       => 500,
						'image_name'  => 'gallery_image'
					];
					// Use uploadMultipleAndResize if available, otherwise skip
					if (function_exists('uploadMultipleAndResize')) {
						$insert_data['gallery_image'] = uploadMultipleAndResize($img_info);
					}
				}

				// Try to insert the product
				$gallery_note  = isset($insert_data['gallery_image']) ? upload_last_error() : '';
				$insert_result = $this->usermodel->addProduct($insert_data);

				if ($insert_result) {
					$this->session->set_flashdata('message', 'Product added successfully!'
						. ($gallery_note !== '' ? ' Note: ' . $gallery_note : ''));
					redirect('user/products', 'refresh');
				} else {
					$this->session->set_flashdata('error', 'Failed to add product. Please try again.');
					redirect('user/add_product');
				}
			}
		} else {
			// Initial page load - just show the form
			$data['title'] = 'Add New Product';
			$this->load->view('header', $data);
			$this->load->view('add_new_product', $data);
			$this->load->view('footer');
		}
	}

	/*===================
		Add Product page - Alternative URL
	===================*/
	public function add_new_product()
	{
		$this->add_product();
	}

	/*Get Sub Categories via AJAX for Add Product*/
	public function get_product_sub_category()
	{
		$category_id = $this->input->post('category_id');

		if ($category_id) {
			$sub_categories = $this->usermodel->getSubCategoriesByCategory($category_id, 'product_sub_categories');

			$html = '<option value="">Choose a Sub Category</option>';
			if (!empty($sub_categories)) {
				foreach ($sub_categories as $sub_cat) {
					$html .= '<option value="' . $sub_cat->sub_category_id . '">' . $sub_cat->sub_category_name . '</option>';
				}
			}
			echo $html;
		} else {
			echo '<option value="">Choose a Sub Category</option>';
		}
	}

	public function user_house_for_sale()
	{
		if (!$this->requireService('service_realestate_enabled')) return;
		// Check if user is logged in
		if (!$this->session->userdata('logged_user')) {
			redirect('user/login');
			return;
		}

		$session = $this->session->userdata('logged_user');
		$logged_id = $session['logged_id'];

		// Enhanced subscription management
		$subscription_details = $this->usermodel->getUserSubscription($logged_id);
		$has_subscription = !empty($subscription_details) && $subscription_details->status == 'active';

		if ($has_subscription) {
			$subscription_limits = $this->usermodel->getSubscriptionLimits($logged_id);
			$plan_name = $subscription_limits['plan_name'] ?? 'Premium';
			$house_limit = $subscription_limits['house'] ?? -1; // -1 means unlimited
			$is_unlimited = ($house_limit == -1);
		} else {
			$subscription_limits = [];
			$plan_name = 'Free';
			$house_limit = 0;
			$is_unlimited = false;
		}

		// Get total count for pagination and subscription tracking
		$total_rows = $this->usermodel->countUserHouses($logged_id);

		// Set up pagination
		$config = array(
			'base_url' => base_url('user/user-house-for-sale'),
			'per_page' => 10,
			'total_rows' => $total_rows,
			'uri_segment' => 3,
			'full_tag_open' => '<ul class="pagination">',
			'full_tag_close' => '</ul>',
			'cur_tag_open' => '<li class="page-item active"><a href="#" class="page-link">',
			'cur_tag_close' => '</a></li>',
			'prev_tag_open' => '<li class="page-item"><a href="',
			'prev_tag_close' => '" class="page-link">Previous</a></li>',
			'next_tag_open' => '<li class="page-item"><a href="',
			'next_tag_close' => '" class="page-link">Next</a></li>',
			'num_tag_open' => '<li class="page-item"><a href="',
			'num_tag_close' => '" class="page-link"></a></li>',
		);

		$this->pagination->initialize($config);
		$page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

		// Load data with pagination
		if ($has_subscription) {
			$data['houseList'] = $this->usermodel->getHouses($logged_id, $config['per_page'], $page);
			$data['total_rows'] = $total_rows;
			$data['per_page'] = $config['per_page'];
			$data['pagination_links'] = $this->pagination->create_links();
			// Ensure views property is set for each house item
			if (is_array($data['houseList'])) {
				foreach ($data['houseList'] as &$house) {
					if (!isset($house->views)) {
						$house->views = 0;
					}
				}
			}
		} else {
			$data['houseList'] = [];
			$data['total_rows'] = 0;
			$data['per_page'] = 10;
			$data['pagination_links'] = '';
		}

		// Enhanced subscription data for view
		$data['has_subscription'] = $has_subscription;
		$data['subscription_limits'] = $subscription_limits;
		$data['subscription_limits']['plan_name'] = $plan_name;
		$data['is_unlimited'] = $is_unlimited;
		$data['start_number'] = $page + 1;
		$data['title'] = 'House For Sale';


		if (file_exists(APPPATH . 'views/all_house.php')) {
			$this->load->view('header', $data);
			$this->load->view('user_left_sidebar');
			$this->load->view('all_house', $data);
			$this->load->view('user_right_sidebar');
			$this->load->view('footer');
		} else {
			$this->load->view('header', $data);
			$this->load->view('user_left_sidebar');
			echo '<div class="container"><h2>House for Sale</h2><p>No house view template found (expected all_house.php).</p></div>';
			$this->load->view('user_right_sidebar');
			$this->load->view('footer');
		}
	}

	/**
	 * Add House - Create new house post
	 * URL: /user/add-house
	 */
	public function add_house()
	{
		// Check if user is logged in
		if (!$this->session->userdata('logged_user')) {
			redirect('user/login');
			return;
		}

		$session = $this->session->userdata('logged_user');
		$logged_id = $session['logged_id'];

		// Check subscription limits before allowing to add house
		if (!$this->usermodel->canUserAddContent($logged_id, 'house')) {
			$this->session->set_flashdata('message', 'You have reached your house limit. Please upgrade your plan to add more houses.');
			redirect('user/user-house-for-sale');
			return;
		}

		// Handle form submission
		if (isset($_POST['house_submit'])) {
			// Basic validation
			$required_fields = ['house_title', 'house_price', 'house_description', 'house_address'];
			$validation_errors = [];

			foreach ($required_fields as $field) {
				if (empty($_POST[$field])) {
					$validation_errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
				}
			}

			if (!empty($validation_errors)) {
				$this->session->set_flashdata('message', implode('. ', $validation_errors));
				redirect('user/add-house');
				return;
			}

			// Generate slug
			$house_title = $_POST['house_title'];
			$house_slug = pearland_slug($house_title . '-' . time());

			// Handle image upload
			$house_image = '';
			if ($_FILES && $_FILES['house_image']['name']) {
				$img_info = [
					'upload_path' => 'images/houses',
					'quality'     => '60%',
					'height'      => 500,
					'width'       => 500,
					'image_name'  => 'house_image'
				];
				$house_image = uploadAndResize($img_info);
			}

			// Prepare data
			$house_data = array(
				'user_id' => $logged_id,
				'house_title' => $_POST['house_title'],
				'house_price' => $_POST['house_price'],
				'house_category_id' => isset($_POST['house_category_id']) ? $_POST['house_category_id'] : null,
				'house_description' => $_POST['house_description'],
				'house_address' => $_POST['house_address'],
				'bedrooms' => isset($_POST['bedrooms']) ? $_POST['bedrooms'] : '',
				'living_area' => isset($_POST['living_area']) ? $_POST['living_area'] : '',
				'bathroom' => isset($_POST['bathroom']) ? $_POST['bathroom'] : '',
				'property_age' => isset($_POST['property_age']) ? $_POST['property_age'] : '',
				'garage' => isset($_POST['garage']) ? $_POST['garage'] : '',
				'floors' => isset($_POST['floors']) ? $_POST['floors'] : '',
				'rent_to_own' => isset($_POST['rent_to_own']) ? 1 : 0,
				'can_sublet' => isset($_POST['can_sublet']) ? 1 : 0,
				'house_seo_title' => isset($_POST['house_seo_title']) ? $_POST['house_seo_title'] : '',
				'house_seo_keywords' => isset($_POST['house_seo_keywords']) ? $_POST['house_seo_keywords'] : '',
				'house_seo_description' => isset($_POST['house_seo_description']) ? $_POST['house_seo_description'] : '',
				'featured_house' => isset($_POST['featured_house']) ? 1 : 0,
				'spotlight_house' => isset($_POST['spotlight_house']) ? 1 : 0,
				'display_contact' => isset($_POST['display_contact']) ? 1 : 0,
				'house_image' => $house_image,
				'house_status' => 1, // Active by default
				'house_slug' => $house_slug,
				'created_at' => date('Y-m-d H:i:s')
			);

			// Insert house
			if ($this->db->insert('house', $house_data)) {
				$this->session->set_flashdata('message', 'House added successfully!');
				redirect('user/user-house-for-sale');
			} else {
				$this->session->set_flashdata('message', 'Failed to add house.');
				redirect('user/add-house');
			}
		}

		// Load categories for dropdown
		$data['all_house_category'] = $this->usermodel->allHouseCategory();

		// Load view
		$data['title'] = 'Add New House';
		$data['logged_id'] = $logged_id;

		$this->load->view('header', $data);
		$this->load->view('add_house', $data);
		$this->load->view('footer');
	}

	/**
	 * Edit House - Edit existing house post
	 * URL: /user/edit-house/slug
	 */
	public function edit_house($house_slug)
	{
		// Check if user is logged in
		if (!$this->session->userdata('logged_user')) {
			redirect('user/login');
			return;
		}

		$session = $this->session->userdata('logged_user');
		$logged_id = $session['logged_id'];

		// Get house details
		$house = $this->usermodel->getSingleValue($house_slug, 'house_slug', 'house');
		if (!$house || $house->user_id != $logged_id) {
			$this->session->set_flashdata('message', 'House not found or access denied.');
			redirect('user/user-house-for-sale');
			return;
		}

		// Handle form submission
		if (isset($_POST['house_submit'])) {
			// Handle image upload
			$house_image = $house->house_image; // Keep existing image by default
			if ($_FILES && $_FILES['house_image']['name']) {
				$img_info = [
					'upload_path' => 'images/houses',
					'quality'     => '60%',
					'height'      => 500,
					'width'       => 500,
					'image_name'  => 'house_image'
				];
				$house_image = uploadAndResize($img_info);

				// Delete old image if new one uploaded
				if (!empty($house->house_image) && file_exists('./' . $house->house_image)) {
					unlink('./' . $house->house_image);
				}
			}

			// Prepare update data
			$house_data = array(
				'house_title' => $_POST['house_title'],
				'house_price' => $_POST['house_price'],
				'house_category_id' => isset($_POST['house_category_id']) ? $_POST['house_category_id'] : null,
				'house_description' => $_POST['house_description'],
				'house_address' => $_POST['house_address'],
				'bedrooms' => isset($_POST['bedrooms']) ? $_POST['bedrooms'] : '',
				'living_area' => isset($_POST['living_area']) ? $_POST['living_area'] : '',
				'bathroom' => isset($_POST['bathroom']) ? $_POST['bathroom'] : '',
				'property_age' => isset($_POST['property_age']) ? $_POST['property_age'] : '',
				'garage' => isset($_POST['garage']) ? $_POST['garage'] : '',
				'floors' => isset($_POST['floors']) ? $_POST['floors'] : '',
				'rent_to_own' => isset($_POST['rent_to_own']) ? 1 : 0,
				'can_sublet' => isset($_POST['can_sublet']) ? 1 : 0,
				'house_seo_title' => isset($_POST['house_seo_title']) ? $_POST['house_seo_title'] : '',
				'house_seo_keywords' => isset($_POST['house_seo_keywords']) ? $_POST['house_seo_keywords'] : '',
				'house_seo_description' => isset($_POST['house_seo_description']) ? $_POST['house_seo_description'] : '',
				'featured_house' => isset($_POST['featured_house']) ? 1 : 0,
				'spotlight_house' => isset($_POST['spotlight_house']) ? 1 : 0,
				'display_contact' => isset($_POST['display_contact']) ? 1 : 0,
				'house_image' => $house_image
			);

			// Update house
			if ($this->db->where('house_slug', $house_slug)->update('house', $house_data)) {
				$this->session->set_flashdata('message', 'House updated successfully!');
				redirect('user/user-house-for-sale');
			} else {
				$this->session->set_flashdata('message', 'Failed to update house.');
				redirect('user/edit-house/' . $house_slug);
			}
		}

		// Load categories for dropdown
		$data['all_house_category'] = $this->usermodel->allHouseCategory();
		$data['house'] = $house;
		$data['title'] = 'Edit House';
		$data['logged_id'] = $logged_id;

		$this->load->view('header', $data);
		$this->load->view('edit_house', $data);
		$this->load->view('footer');
	}

	/**
	 * House Details - Public house viewing page
	 * URL: /user/house-details/id
	 */
	public function house_details($house_slug = null)
	{
		if (!$this->requireService('service_realestate_enabled')) return;
		if (!$house_slug) {
			show_404();
			return;
		}

		// Resolve house by slug (with safe fallback)
		$houses = $this->usermodel->getHouseBySlug($house_slug);
		if (!$houses) {
			show_404();
			return;
		}

		// Increment views count for this house
		$this->usermodel->incrementViews('house', 'house_id', $houses->house_id);

		// Get logged user ID for enquiry form
		$logged_id = NULL;
		if ($this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$logged_id = $session['logged_id'];
		}

		$data['houses'] = $houses;
		$data['logged_id'] = $logged_id;
		$data['title'] = $houses->house_title;

		// Build share URLs dynamically for this house (use slug in URL)
		$house_url = base_url('user/house-details/' . $houses->house_slug);
		$house_title = $houses->house_title;
		$house_image = !empty($houses->house_image) ? base_url($houses->house_image) : '';
		$data['share_urls'] = [
			'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($house_url) . '&quote=' . urlencode($house_title),
			'twitter' => 'https://twitter.com/share?text=' . urlencode($house_title) . '&url=' . urlencode($house_url),
			'whatsapp' => 'https://wa.me/?text=' . urlencode($house_title . ' ' . $house_url),
			'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode($house_url),
			'pinterest' => 'https://www.pinterest.com/pin/create/bookmarklet/?media=' . urlencode($house_image) . '&url=' . urlencode($house_url) . '&description=' . urlencode($house_title),
		];

		// Get 3 related houses excluding current (prefer same category)
		$house_category_id = isset($houses->house_category_id) ? $houses->house_category_id : null;
		$data['related_houses'] = $this->usermodel->getRelatedHouses($houses->house_id, $house_category_id, 3);

		// Load header categories for consistency
		$this->loadHeaderCategories($data);

		// Load views
		// Advertising. Six zones — three wide, three down the sidebar.
		$data['ad_slots'] = $this->pageAdSlots('full');

		$this->load->view('header', $data);
		$this->load->view('house_details', $data);
		$this->load->view('footer');
	}

	/**
	 * Public "advertise on Pearland411" landing page. Sends buyers to
	 * /pricing-plan (self-serve cart + Helcim checkout); the form beside it
	 * posts to /contact-submit for anyone who would rather be called.
	 */
	public function post_your_ads()
	{
		$data = array();
		$this->loadHeaderCategories($data);
		$data['title'] = 'Advertise on Pearland411';
		$data['meta_description'] = 'Display ads on Pearland411 — homepage, listing and category placements. Buy a package online or ask our sales team for placements and pricing.';
		// Advertising. A banner above and below the form. No sidebar, and nothing between the fields.
		$data['ad_slots'] = $this->pageAdSlots('banner');

		$this->load->view('header', $data);
		$this->load->view('post_your_ads', $data);
		$this->load->view('footer');
	}

	public function ads_summary()
	{
		$data['logged_id'] = null;
		if ($this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$data['logged_id'] = $session['logged_id'];
		}

		// Subscription status and limits
		$data['user_subscription'] = $this->usermodel->getUserSubscription($data['logged_id']);
		$data['has_subscription'] = !empty($data['user_subscription']);

		if ($data['has_subscription']) {
			$data['subscription_limits'] = $this->usermodel->getSubscriptionLimits($data['logged_id']);
			$data['usage_counts'] = $this->usermodel->getAllUsageCounts($data['logged_id']);
			$data['can_add_ads'] = $this->usermodel->canUserAddContent($data['logged_id'], 'ads');
			$data['current_ads_count'] = $this->usermodel->getUserUsage($data['logged_id'], 'ads');
		} else {
			$data['subscription_limits'] = null;
			$data['usage_counts'] = [];
			$data['can_add_ads'] = false;
			$data['current_ads_count'] = 0;
		}

		// Pagination config
		$config = [
			'base_url' => base_url('user/ads-summary'),
			'per_page' => 10,
			'total_rows' => $this->usermodel->countAllAds(),
			'uri_segment' => 3,
		];
		$config['full_tag_open'] = '<ul class="pagination">';
		$config['full_tag_close'] = '</ul>';
		$config['cur_tag_open'] = '<li class="page-item active"><a href="#" class="page-link">';
		$config['cur_tag_close'] = '</a></li>';
		$config['prev_tag_open'] = '<li class="page-link">';
		$config['prev_tag_close'] = '</li>';
		$config['next_tag_open'] = '<li class="page-link">';
		$config['next_tag_close'] = '</li>';
		$config['num_tag_open'] = '<li class="page-link">';
		$config['num_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li class="page-link">';
		$config['last_tag_close'] = '</li>';
		$config['first_tag_open'] = '<li class="page-link">';
		$config['first_tag_close'] = '</li>';
		$config['next_link'] = 'Next >';
		$config['prev_link'] = '< Prev';

		$this->pagination->initialize($config);
		$page = (int) ($this->uri->segment(3)) ? (int) ($this->uri->segment(3)) : 0;

		// Ads data for this user
		$data['adsList'] = $this->usermodel->getAllAds($config['per_page'], $page);
		$data['start_number'] = $page + 1;
		$data['per_page'] = $config['per_page'];
		$data['link'] = $this->pagination->create_links();
		$data['title'] = 'Ads Summary';

		if (file_exists(APPPATH . 'views/post_ads.php')) {
			$this->load->view('header', $data);
			$this->load->view('user_left_sidebar');
			$this->load->view('post_ads', $data);
			$this->load->view('user_right_sidebar');
			$this->load->view('footer');
		} else {
			$this->load->view('header', $data);
			$this->load->view('user_left_sidebar');
			echo '<div class="container"><h2>Ads Summary</h2><p>No ads view template found (expected post_ads.php).</p></div>';
			$this->load->view('user_right_sidebar');
			$this->load->view('footer');
		}
	}

	/**
	 * User News - Display user's news with subscription management
	 */
	public function user_news()
	{
		// Check if user is logged in
		if (!$this->session->userdata('logged_user')) {
			redirect('user/login');
			return;
		}

		$session = $this->session->userdata('logged_user');
		$logged_id = $session['logged_id'];
		$data['logged_id'] = $logged_id;

		// Subscription status and limits
		$data['user_subscription'] = $this->usermodel->getUserSubscription($logged_id);
		$data['has_subscription'] = !empty($data['user_subscription']);

		if ($data['has_subscription']) {
			$data['subscription_limits'] = $this->usermodel->getSubscriptionLimits($logged_id);
		} else {
			$data['subscription_limits'] = [];
		}

		// Pagination configuration
		$config['base_url'] = base_url('user/user-news');
		$config['total_rows'] = $this->usermodel->countUserNews($logged_id);
		$config['per_page'] = 10;
		$config['uri_segment'] = 3;
		$config['use_page_numbers'] = FALSE;

		// Pagination design
		$config['full_tag_open'] = '<ul class="pagination">';
		$config['full_tag_close'] = '</ul>';
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['first_tag_open'] = '<li class="page-item"><span class="page-link">';
		$config['first_tag_close'] = '</span></li>';
		$config['prev_link'] = '&laquo';
		$config['prev_tag_open'] = '<li class="page-item"><span class="page-link">';
		$config['prev_tag_close'] = '</span></li>';
		$config['next_link'] = '&raquo';
		$config['next_tag_open'] = '<li class="page-item"><span class="page-link">';
		$config['next_tag_close'] = '</span></li>';
		$config['last_tag_open'] = '<li class="page-item"><span class="page-link">';
		$config['last_tag_close'] = '</span></li>';
		$config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';
		$config['cur_tag_close'] = '</a></li>';
		$config['num_tag_open'] = '<li class="page-item"><span class="page-link">';
		$config['num_tag_close'] = '</span></li>';
		$config['first_tag_close'] = '</li>';
		$config['next_link'] = 'Next >';
		$config['prev_link'] = '< Prev';

		$this->pagination->initialize($config);
		$page = (int) ($this->uri->segment(3)) ? (int) ($this->uri->segment(3)) : 0;

		// News for this user
		$data['allNews'] = $this->usermodel->getAllNews($logged_id, $config['per_page'], $page);
		// Ensure views property is set for each news item
		if (is_array($data['allNews'])) {
			foreach ($data['allNews'] as &$news) {
				if (!isset($news->views)) {
					$news->views = 0;
				}
			}
		}
		$data['start_number'] = $page + 1;
		$data['per_page'] = $config['per_page'];
		$data['total_rows'] = $config['total_rows'];
		$data['link'] = $this->pagination->create_links();

		$data['title'] = 'My News Posts';

		if (file_exists(APPPATH . 'views/user_news.php')) {
			$this->load->view('header', $data);
			$this->load->view('user_left_sidebar');
			$this->load->view('user_news', $data);
			$this->load->view('user_right_sidebar');
			$this->load->view('footer');
		} else {
			$this->load->view('header', $data);
			$this->load->view('user_left_sidebar');
			echo '<div class="container"><h2>User News</h2><p>No news view template found (expected user_news.php).</p></div>';
			$this->load->view('user_right_sidebar');
			$this->load->view('footer');
		}
	}

	/**
	 * Add News - Create new news post
	 * URL: /user/user-add-news
	 */
	public function user_add_news()
	{
		// Check if user is logged in
		if (!$this->session->userdata('logged_user')) {
			redirect('user/login');
			return;
		}

		$session = $this->session->userdata('logged_user');
		$logged_id = $session['logged_id'];

		// Check subscription and limits
		$user_subscription = $this->usermodel->getUserSubscription($logged_id);
		if (empty($user_subscription)) {
			$this->session->set_flashdata('message', 'Please subscribe to a plan to add news posts.');
			redirect('pricing-plan');
			return;
		}

		$subscription_limits = $this->usermodel->getSubscriptionLimits($logged_id);
		$news_limit = isset($subscription_limits['news']) ? (int)$subscription_limits['news'] : 0;

		if ($news_limit > 0) {
			$current_count = $this->usermodel->countUserNews($logged_id);
			if ($current_count >= $news_limit) {
				$this->session->set_flashdata('message', 'You have reached your news limit. Please upgrade your plan.');
				redirect('user/user-news');
				return;
			}
		}

		// Handle form submission
		if (isset($_POST['news_submit'])) {
			// Generate slug
			$news_slug = url_title($_POST['news_title'], 'dash', TRUE) . '-' . time();

			// Handle image upload
			$news_image = '';
			if ($_FILES && $_FILES['news_image']['name']) {
				$img_info = [
					'upload_path' => 'images/news',
					'quality'     => '60%',
					'height'      => 500,
					'width'       => 500,
					'image_name'  => 'news_image'
				];
				$news_image = uploadAndResize($img_info);
			}

			// Prepare news data
			$news_data = array(
				'user_id' => $logged_id,
				'news_title' => $_POST['news_title'],
				'news_category_id' => isset($_POST['news_category_id']) ? $_POST['news_category_id'] : 0,
				'news_description' => $_POST['news_description'],
				'news_seo_title' => isset($_POST['news_seo_title']) ? $_POST['news_seo_title'] : $_POST['news_title'],
				'news_seo_keywords' => isset($_POST['news_seo_keywords']) ? $_POST['news_seo_keywords'] : '',
				'news_seo_description' => isset($_POST['news_seo_description']) ? $_POST['news_seo_description'] : '',
				'featured_news' => isset($_POST['featured_news']) ? 1 : 0,
				'news_image' => $news_image,
				'news_status' => 1, // Active by default
				'news_slug' => $news_slug,
				'is_deleted' => 0,
				'created_at' => date('Y-m-d H:i:s')
			);

			// Insert news
			if ($this->db->insert('news', $news_data)) {
				$this->session->set_flashdata('message', 'News post added successfully!');
				redirect('user/user-news');
			} else {
				$this->session->set_flashdata('message', 'Failed to add news post.');
				redirect('user/user-add-news');
			}
		}

		// Load categories for dropdown
		$data['NewsCategories'] = $this->usermodel->getAllValues('news_categories');
		$data['title'] = 'Add New News Post';
		$data['logged_id'] = $logged_id;

		$this->load->view('header', $data);
		$this->load->view('user_left_sidebar');
		$this->load->view('user_add_news', $data);
		$this->load->view('user_right_sidebar');
		$this->load->view('footer');
	}

	/**
	 * Edit News - Edit existing news post
	 * URL: /user/user-edit-news/{news_slug}
	 */
	public function user_edit_news($news_slug = null)
	{
		// Check if user is logged in
		if (!$this->session->userdata('logged_user')) {
			redirect('user/login');
			return;
		}

		if (!$news_slug) {
			$this->session->set_flashdata('message', 'News not found.');
			redirect('user/user-news');
			return;
		}

		$session = $this->session->userdata('logged_user');
		$logged_id = $session['logged_id'];

		// Get news details
		$news = $this->usermodel->getSingleValue($news_slug, 'news_slug', 'news');
		if (!$news || $news->user_id != $logged_id) {
			$this->session->set_flashdata('message', 'News not found or access denied.');
			redirect('user/user-news');
			return;
		}

		// Handle form submission
		if (isset($_POST['news_submit'])) {
			// Handle image upload
			$news_image = $news->news_image; // Keep existing image by default
			if ($_FILES && $_FILES['news_image']['name']) {
				$img_info = [
					'upload_path' => 'images/news',
					'quality'     => '60%',
					'height'      => 500,
					'width'       => 500,
					'image_name'  => 'news_image'
				];
				$news_image = uploadAndResize($img_info);

				// Delete old image if new one uploaded
				if (!empty($news->news_image) && file_exists('./' . $news->news_image)) {
					unlink('./' . $news->news_image);
				}
			}

			// Update data
			$update_data = array(
				'news_title' => $_POST['news_title'],
				'news_category_id' => isset($_POST['news_category_id']) ? $_POST['news_category_id'] : $news->news_category_id,
				'news_description' => $_POST['news_description'],
				'news_seo_title' => isset($_POST['news_seo_title']) ? $_POST['news_seo_title'] : $_POST['news_title'],
				'news_seo_keywords' => isset($_POST['news_seo_keywords']) ? $_POST['news_seo_keywords'] : '',
				'news_seo_description' => isset($_POST['news_seo_description']) ? $_POST['news_seo_description'] : '',
				'featured_news' => isset($_POST['featured_news']) ? 1 : 0,
				'news_image' => $news_image
			);

			// Update news
			$update_result = $this->usermodel->update($news->news_id, $update_data, 'news_id', 'news');

			if ($update_result) {
				$this->session->set_flashdata('message', 'News updated successfully!');
				redirect('user/user-news');
			} else {
				$this->session->set_flashdata('message', 'Failed to update news.');
			}
		}

		// Load categories for dropdown
		$data['all_news_category'] = $this->usermodel->getAllValues('news_categories');
		$data['news'] = $news;
		$data['title'] = 'Edit News Post';
		$data['logged_id'] = $logged_id;

		$this->load->view('header', $data);
		$this->load->view('user_left_sidebar');
		$this->load->view('user_edit_news', $data);
		$this->load->view('user_right_sidebar');
		$this->load->view('footer');
	}

	/**
	 * News Post - Main URL for user news posts
	 * URL: /user/news-post
	 */
	public function news_post()
	{
		$this->user_news();
	}

	public function video_list()
	{
		$data['logged_id'] = null;
		if ($this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$data['logged_id'] = $session['logged_id'];
		}

		// Subscription status and limits
		$data['user_subscription'] = $this->usermodel->getUserSubscription($data['logged_id']);
		$data['has_subscription'] = !empty($data['user_subscription']);

		if ($data['has_subscription']) {
			$data['subscription_limits'] = $this->usermodel->getSubscriptionLimits($data['logged_id']);
			$data['usage_counts'] = $this->usermodel->getAllUsageCounts($data['logged_id']);

			// Pagination configuration
			$config['base_url'] = base_url('user/video-list');
			$config['total_rows'] = $this->usermodel->countUserVideos($data['logged_id']);
			$config['per_page'] = 10;
			$config['uri_segment'] = 3;
			$config['use_page_numbers'] = TRUE;
			$config['page_query_string'] = FALSE;

			$this->pagination->initialize($config);

			$page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
			$offset = ($page - 1) * $config['per_page'];

			// Get videos with pagination
			$data['videoList'] = $this->usermodel->getVideo($data['logged_id'], $config['per_page'], $offset);
			// Ensure views property is set for each video
			if (is_array($data['videoList'])) {
				foreach ($data['videoList'] as &$video) {
					if (!isset($video->views)) {
						$video->views = 0;
					}
				}
			}
			$data['total_rows'] = $config['total_rows'];
			$data['per_page'] = $config['per_page'];
			$data['pagination_links'] = $this->pagination->create_links();
		} else {
			$data['videoList'] = [];
			$data['total_rows'] = 0;
			$data['per_page'] = 10;
			$data['pagination_links'] = '';
		}

		$data['title'] = 'Video List';

		if (file_exists(APPPATH . 'views/user_videos.php')) {
			$this->load->view('header', $data);
			$this->load->view('user_left_sidebar');
			$this->load->view('user_videos', $data);
			$this->load->view('user_right_sidebar');
			$this->load->view('footer');
		} else {
			$this->load->view('header', $data);
			$this->load->view('user_left_sidebar');
			echo '<div class="container"><h2>Video List</h2><p>No video view template found (expected user_videos.php).</p></div>';
			$this->load->view('user_right_sidebar');
			$this->load->view('footer');
		}
	}

	/**
	 * Add Video - Create new video post
	 * URL: /user/add-video
	 */
	public function add_video()
	{
		// Check if user is logged in
		if (!$this->session->userdata('logged_user')) {
			redirect('user/login');
			return;
		}

		$session = $this->session->userdata('logged_user');
		$logged_id = $session['logged_id'];

		// Check subscription limits before allowing to add video
		if (!$this->usermodel->canUserAddContent($logged_id, 'video')) {
			$this->session->set_flashdata('message', 'You have reached your video limit. Please upgrade your plan to add more videos.');
			redirect('user/video-list');
			return;
		}

		// Handle form submission
		if (isset($_POST['video_submit'])) {
			// Basic validation
			$required_fields = ['video_title', 'video_code', 'video_description'];
			$validation_errors = [];

			foreach ($required_fields as $field) {
				if (empty($_POST[$field])) {
					$validation_errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
				}
			}

			if (!empty($validation_errors)) {
				$this->session->set_flashdata('message', implode('. ', $validation_errors));
				redirect('user/add-video');
				return;
			}

			// Generate slug
			$video_title = $_POST['video_title'];
			$video_slug = pearland_slug($video_title . '-' . time());

			// Handle image upload. `images/video` — the folder the admin side
			// uses and the one that exists. This said `images/videos`, which
			// does not, so CodeIgniter refused every member upload ("the upload
			// path does not appear to be valid") and the still was lost.
			$video_image = '';
			$upload_note = '';
			if (!empty($_FILES['video_image']['name'])) {
				$img_info = [
					'upload_path' => 'images/video',
					'quality'     => '60%',
					'height'      => 500,
					'width'       => 500,
					'image_name'  => 'video_image'
				];
				$video_image = (string) uploadAndResize($img_info);
				if ($video_image === '') {
					$upload_note = ' The picture was not saved: ' . (upload_last_error() ?: 'the file could not be read.')
						. ' The YouTube thumbnail will be shown instead.';
				}
			}

			// Prepare data
			$video_data = array(
				'user_id' => $logged_id,
				'video_title' => $_POST['video_title'],
				'video_code' => $_POST['video_code'],
				'video_category_id' => isset($_POST['video_category_id']) ? $_POST['video_category_id'] : null,
				'video_description' => $_POST['video_description'],
				'video_seo_title' => isset($_POST['video_seo_title']) ? $_POST['video_seo_title'] : '',
				'video_seo_keywords' => isset($_POST['video_seo_keywords']) ? $_POST['video_seo_keywords'] : '',
				'video_seo_description' => isset($_POST['video_seo_description']) ? $_POST['video_seo_description'] : '',
				'featured_video' => isset($_POST['featured_video']) ? 1 : 0,
				'video_image' => $video_image,
				'video_status' => 1, // Active by default
				'video_slug' => $video_slug,
				'created_at' => date('Y-m-d H:i:s')
			);

			// Insert video
			if ($this->db->insert('video', $video_data)) {
				$this->session->set_flashdata('message', 'Video added successfully!' . $upload_note);
				redirect('user/video-list');
			} else {
				$this->session->set_flashdata('message', 'Failed to add video.');
				redirect('user/add-video');
			}
		}

		// Load categories for dropdown
		$data['all_video_category'] = $this->usermodel->allVideoCategory();

		// Load view
		$data['title'] = 'Add New Video';
		$data['logged_id'] = $logged_id;

		$this->load->view('header', $data);
		$this->load->view('add_new_video', $data);
		$this->load->view('footer');
	}

	/**
	 * Edit Video - Edit existing video post
	 * URL: /user/edit-video/slug
	 */
	public function edit_video($video_slug)
	{
		// Check if user is logged in
		if (!$this->session->userdata('logged_user')) {
			redirect('user/login');
			return;
		}

		$session = $this->session->userdata('logged_user');
		$logged_id = $session['logged_id'];

		// Get video details
		$video = $this->usermodel->getSingleValue($video_slug, 'video_slug', 'video');
		if (!$video || $video->user_id != $logged_id) {
			$this->session->set_flashdata('message', 'Video not found or access denied.');
			redirect('user/video-list');
			return;
		}

		// Handle form submission
		if (isset($_POST['video_submit'])) {
			// Handle image upload
			$video_image = $video->video_image; // Keep existing image by default
			$upload_note = '';
			if (!empty($_FILES['video_image']['name'])) {
				$img_info = [
					'upload_path' => 'images/video',
					'quality'     => '60%',
					'height'      => 500,
					'width'       => 500,
					'image_name'  => 'video_image'
				];
				$new_image = uploadAndResize($img_info);

				// Only replace — and only then delete the old file — when the
				// upload actually succeeded. This unlinked the old still and
				// stored NULL whenever the upload failed, which (with the wrong
				// folder above) was every time.
				if ($new_image) {
					if (!empty($video->video_image) && $video->video_image !== $new_image && file_exists('./' . $video->video_image)) {
						@unlink('./' . $video->video_image);
					}
					$video_image = $new_image;
				} else {
					$upload_note = ' The new picture was not saved: ' . (upload_last_error() ?: 'the file could not be read.')
						. ' The previous one was kept.';
				}
			}

			// Prepare update data
			$video_data = array(
				'video_title' => $_POST['video_title'],
				'video_code' => $_POST['video_code'],
				'video_category_id' => isset($_POST['video_category_id']) ? $_POST['video_category_id'] : null,
				'video_description' => $_POST['video_description'],
				'video_seo_title' => isset($_POST['video_seo_title']) ? $_POST['video_seo_title'] : '',
				'video_seo_keywords' => isset($_POST['video_seo_keywords']) ? $_POST['video_seo_keywords'] : '',
				'video_seo_description' => isset($_POST['video_seo_description']) ? $_POST['video_seo_description'] : '',
				'featured_video' => isset($_POST['featured_video']) ? 1 : 0,
				'video_image' => $video_image
			);

			// Update video
			if ($this->db->where('video_slug', $video_slug)->update('video', $video_data)) {
				$this->session->set_flashdata('message', 'Video updated successfully!' . $upload_note);
				redirect('user/video-list');
			} else {
				$this->session->set_flashdata('message', 'Failed to update video.');
				redirect('user/edit-video/' . $video_slug);
			}
		}

		// Load categories for dropdown
		$data['all_video_category'] = $this->usermodel->allVideoCategory();
		$data['video'] = $video;
		$data['title'] = 'Edit Video';
		$data['logged_id'] = $logged_id;

		$this->load->view('header', $data);
		$this->load->view('edit_video', $data);
		$this->load->view('footer');
	}




	public function user_classifieds()
	{
		$data['logged_id'] = null;
		if ($this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$data['logged_id'] = $session['logged_id'];
		}

		// Subscription status and limits
		$data['user_subscription'] = $this->usermodel->getUserSubscription($data['logged_id']);
		$data['has_subscription'] = !empty($data['user_subscription']);

		if ($data['has_subscription']) {
			$data['subscription_limits'] = $this->usermodel->getSubscriptionLimits($data['logged_id']);
			$data['usage_counts'] = $this->usermodel->getAllUsageCounts($data['logged_id']);

			// Pagination configuration
			$config['base_url'] = base_url('user/user-classifieds');
			$config['total_rows'] = $this->usermodel->countUserClassifieds($data['logged_id']);
			$config['per_page'] = 10;
			$config['uri_segment'] = 3;
			$config['use_page_numbers'] = TRUE;
			$config['page_query_string'] = FALSE;

			$this->pagination->initialize($config);

			$page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
			$offset = ($page - 1) * $config['per_page'];

			// Get classifieds with pagination
			$data['classifiedsList'] = $this->usermodel->getClassifieds($data['logged_id'], $config['per_page'], $offset);
			// Ensure views property is set for each classifieds item
			if (is_array($data['classifiedsList'])) {
				foreach ($data['classifiedsList'] as &$classifieds) {
					if (!isset($classifieds->views)) {
						$classifieds->views = 0;
					}
				}
			}
			$data['total_rows'] = $config['total_rows'];
			$data['per_page'] = $config['per_page'];
			$data['pagination_links'] = $this->pagination->create_links();
		} else {
			$data['classifiedsList'] = [];
			$data['total_rows'] = 0;
			$data['per_page'] = 10;
			$data['pagination_links'] = '';
		}

		$data['title'] = 'User Classifieds';

		if (file_exists(APPPATH . 'views/all_classifieds.php')) {
			$this->load->view('header', $data);
			$this->load->view('user_left_sidebar');
			$this->load->view('all_classifieds', $data);
			$this->load->view('user_right_sidebar');
			$this->load->view('footer');
		} else {
			$this->load->view('header', $data);
			$this->load->view('user_left_sidebar');
			echo '<div class="container"><h2>User Classifieds</h2><p>No classifieds view template found (expected all_classifieds.php).</p></div>';
			$this->load->view('user_right_sidebar');
			$this->load->view('footer');
		}
	}

	/**
	 * Add Classifieds - Create new classifieds post
	 * URL: /user/add-classifieds
	 */
	public function add_classifieds()
	{
		// Check if user is logged in
		if (!$this->session->userdata('logged_user')) {
			redirect('user/login');
			return;
		}

		$session = $this->session->userdata('logged_user');
		$logged_id = $session['logged_id'];

		// Check subscription limits before allowing to add classifieds
		if (!$this->usermodel->canUserAddContent($logged_id, 'classified')) {
			$this->session->set_flashdata('message', 'You have reached your classifieds limit. Please upgrade your plan to add more classifieds.');
			redirect('user/user-classifieds');
			return;
		}

		// Handle form submission
		if (isset($_POST['classifieds_submit'])) {
			// Basic validation
			$required_fields = ['classifieds_title', 'classifieds_price', 'classifieds_description'];
			$validation_errors = [];

			foreach ($required_fields as $field) {
				if (empty($_POST[$field])) {
					$validation_errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
				}
			}

			if (!empty($validation_errors)) {
				$this->session->set_flashdata('message', implode('. ', $validation_errors));
				redirect('user/add-classifieds');
				return;
			}

			// Generate slug
			$classifieds_title = $_POST['classifieds_title'];
			$classifieds_slug = pearland_slug($classifieds_title . '-' . time());

			// Handle image upload
			$classifieds_image = '';
			if ($_FILES && $_FILES['classifieds_image']['name']) {
				$img_info = [
					'upload_path' => 'images/classifieds',
					'quality'     => '60%',
					'height'      => 500,
					'width'       => 500,
					'image_name'  => 'classifieds_image'
				];
				$classifieds_image = uploadAndResize($img_info);
			}

			// Prepare data
			$classifieds_data = array(
				'user_id' => $logged_id,
				'classifieds_title' => $_POST['classifieds_title'],
				'classifieds_price' => $_POST['classifieds_price'],
				'classifieds_category_id' => isset($_POST['classifieds_category_id']) ? $_POST['classifieds_category_id'] : null,
				'classifieds_description' => $_POST['classifieds_description'],
				'classifieds_seo_title' => isset($_POST['classifieds_seo_title']) ? $_POST['classifieds_seo_title'] : '',
				'classifieds_seo_keywords' => isset($_POST['classifieds_seo_keywords']) ? $_POST['classifieds_seo_keywords'] : '',
				'classifieds_seo_description' => isset($_POST['classifieds_seo_description']) ? $_POST['classifieds_seo_description'] : '',
				'featured_classifieds' => isset($_POST['featured_classifieds']) ? 1 : 0,
				'spotlight_classifieds' => isset($_POST['spotlight_classifieds']) ? 1 : 0,
				'display_contact' => isset($_POST['display_contact']) ? 1 : 0,
				'classifieds_image' => $classifieds_image,
				'classifieds_status' => 1, // Active by default
				'classifieds_slug' => $classifieds_slug,
				'created_at' => date('Y-m-d H:i:s')
			);

			// Insert classifieds
			if ($this->db->insert('classifieds', $classifieds_data)) {
				$this->session->set_flashdata('message', 'Classifieds added successfully!');
				redirect('user/user-classifieds');
			} else {
				$this->session->set_flashdata('message', 'Failed to add classifieds.');
				redirect('user/add-classifieds');
			}
		}

		// Load categories for dropdown
		$data['all_classifieds_category'] = $this->usermodel->allClassifiedsCategory();

		// Load view
		$data['title'] = 'Add New Classifieds';
		$data['logged_id'] = $logged_id;

		$this->load->view('header', $data);
		$this->load->view('add_classifieds', $data);
		$this->load->view('footer');
	}

	/**
	 * Edit Classifieds - Edit existing classifieds post
	 * URL: /user/edit-classifieds/slug
	 */
	public function edit_classifieds($classifieds_slug)
	{
		// Check if user is logged in
		if (!$this->session->userdata('logged_user')) {
			redirect('user/login');
			return;
		}

		$session = $this->session->userdata('logged_user');
		$logged_id = $session['logged_id'];

		// Get classifieds details
		$classifieds = $this->usermodel->getSingleValue($classifieds_slug, 'classifieds_slug', 'classifieds');
		if (!$classifieds || $classifieds->user_id != $logged_id) {
			$this->session->set_flashdata('message', 'Classifieds not found or access denied.');
			redirect('user/user-classifieds');
			return;
		}

		// Handle form submission
		if (isset($_POST['classifieds_submit'])) {
			// Handle image upload
			$classifieds_image = $classifieds->classifieds_image; // Keep existing image by default
			if ($_FILES && $_FILES['classifieds_image']['name']) {
				$img_info = [
					'upload_path' => 'images/classifieds',
					'quality'     => '60%',
					'height'      => 500,
					'width'       => 500,
					'image_name'  => 'classifieds_image'
				];
				$classifieds_image = uploadAndResize($img_info);

				// Delete old image if new one uploaded
				if (!empty($classifieds->classifieds_image) && file_exists('./' . $classifieds->classifieds_image)) {
					unlink('./' . $classifieds->classifieds_image);
				}
			}

			// Prepare update data
			$classifieds_data = array(
				'classifieds_title' => $_POST['classifieds_title'],
				'classifieds_price' => $_POST['classifieds_price'],
				'classifieds_category_id' => isset($_POST['classifieds_category_id']) ? $_POST['classifieds_category_id'] : null,
				'classifieds_description' => $_POST['classifieds_description'],
				'classifieds_seo_title' => isset($_POST['classifieds_seo_title']) ? $_POST['classifieds_seo_title'] : '',
				'classifieds_seo_keywords' => isset($_POST['classifieds_seo_keywords']) ? $_POST['classifieds_seo_keywords'] : '',
				'classifieds_seo_description' => isset($_POST['classifieds_seo_description']) ? $_POST['classifieds_seo_description'] : '',
				'featured_classifieds' => isset($_POST['featured_classifieds']) ? 1 : 0,
				'spotlight_classifieds' => isset($_POST['spotlight_classifieds']) ? 1 : 0,
				'display_contact' => isset($_POST['display_contact']) ? 1 : 0,
				'classifieds_image' => $classifieds_image
			);

			// Update classifieds
			if ($this->db->where('classifieds_slug', $classifieds_slug)->update('classifieds', $classifieds_data)) {
				$this->session->set_flashdata('message', 'Classifieds updated successfully!');
				redirect('user/user-classifieds');
			} else {
				$this->session->set_flashdata('message', 'Failed to update classifieds.');
				redirect('user/edit-classifieds/' . $classifieds_slug);
			}
		}

		// Load categories for dropdown
		$data['all_classifieds_category'] = $this->usermodel->allClassifiedsCategory();
		$data['classifieds'] = $classifieds;
		$data['title'] = 'Edit Classifieds';
		$data['logged_id'] = $logged_id;

		$this->load->view('header', $data);
		$this->load->view('edit_classifieds', $data);
		$this->load->view('footer');
	}

	/**
	 * Classifieds Details - Public classifieds viewing page
	 * URL: /user/classifieds-details/id
	 */
	public function classifieds_details($classifieds_slug)
	{
		if (!$this->requireService('service_classifieds_enabled')) return;
		// Get classifieds details by slug
		$classifieds = $this->usermodel->getClassifiedsBySlug($classifieds_slug);
		if (!$classifieds) {
			show_404();
			return;
		}

		// Increment views count for this classifieds
		$this->usermodel->incrementViews('classifieds', 'classifieds_id', $classifieds->classifieds_id);

		// Get logged user ID for enquiry form
		$logged_id = NULL;
		if ($this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$logged_id = $session['logged_id'];
		}

		// Dynamic share URLs
		$base_url = base_url('user/classifieds-details/' . $classifieds->classifieds_slug);
		$title = urlencode($classifieds->classifieds_title);
		$image = !empty($classifieds->classifieds_image) ? base_url($classifieds->classifieds_image) : base_url('assets/images/no_image.png');
		$share_urls = [
			'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($base_url) . '&quote=' . $title,
			'twitter' => 'https://twitter.com/intent/tweet?text=' . $title . '&url=' . urlencode($base_url),
			'whatsapp' => 'https://wa.me/?text=' . urlencode($base_url),
			'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode($base_url),
			'pinterest' => 'https://www.pinterest.com/pin/create/bookmarklet/?media=' . urlencode($image) . '&url=' . urlencode($base_url) . '&description=' . $title,
		];

		// Get 3 related classifieds (excluding current)
		$related_classifieds = $this->usermodel->getRelatedClassifieds($classifieds->classifieds_id, 3);

		$data['classifieds'] = $classifieds;
		$data['logged_id'] = $logged_id;
		$data['title'] = $classifieds->classifieds_title;
		$data['share_urls'] = $share_urls;
		$data['related_classifieds'] = $related_classifieds;

		// Load views
		// Advertising. Six zones — three wide, three down the sidebar.
		$data['ad_slots'] = $this->pageAdSlots('full');

		$this->load->view('header', $data);
		$this->load->view('classifieds_details', $data);
		$this->load->view('footer');
	}



	public function user_apartments_and_rental()
	{
		if (!$this->requireService('service_realestate_enabled')) return;
		$data['logged_id'] = null;
		if ($this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$data['logged_id'] = $session['logged_id'];
		}

		// Subscription status and limits
		$data['user_subscription'] = $this->usermodel->getUserSubscription($data['logged_id']);
		$data['has_subscription'] = !empty($data['user_subscription']);

		if ($data['has_subscription']) {
			$data['subscription_limits'] = $this->usermodel->getSubscriptionLimits($data['logged_id']);
			$data['usage_counts'] = $this->usermodel->getAllUsageCounts($data['logged_id']);

			// Pagination configuration
			$config['base_url'] = base_url('user/user-apartments-and-rental');
			$config['total_rows'] = $this->usermodel->countUserRentals($data['logged_id']);
			$config['per_page'] = 10;
			$config['uri_segment'] = 3;
			$config['use_page_numbers'] = TRUE;
			$config['page_query_string'] = FALSE;

			$this->pagination->initialize($config);


			$page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
			$offset = ($page - 1) * $config['per_page'];


			// Get rentals with pagination
			$data['rentalList'] = $this->usermodel->getRentals($data['logged_id'], $config['per_page'], $offset);
			$data['total_rows'] = $config['total_rows'];
			$data['per_page'] = $config['per_page'];
			$data['pagination_links'] = $this->pagination->create_links();
			// Ensure views property is set for each classifieds item
			if (is_array($data['rentalList'])) {
				foreach ($data['rentalList'] as &$rental) {
					if (!isset($rental->views)) {
						$rental->views = 0;
					}
				}
			}
		} else {
			$data['rentalList'] = [];
			$data['total_rows'] = 0;
			$data['per_page'] = 10;
			$data['pagination_links'] = '';
		}

		$data['title'] = 'User Apartments and Rental';

		if (file_exists(APPPATH . 'views/all_rental.php')) {
			$this->load->view('header', $data);
			$this->load->view('user_left_sidebar');
			$this->load->view('all_rental', $data);
			$this->load->view('user_right_sidebar');
			$this->load->view('footer');
		} else {
			$this->load->view('header', $data);
			$this->load->view('user_left_sidebar');
			echo '<div class="container"><h2>User Apartments and Rental</h2><p>No rental view template found (expected all_rental.php).</p></div>';
			$this->load->view('user_right_sidebar');
			$this->load->view('footer');
		}
	}

	/**
	 * Add Rental - Create new rental post
	 * URL: /user/add-rental
	 */
	public function add_rental()
	{
		// Check if user is logged in
		if (!$this->session->userdata('logged_user')) {
			redirect('user/login');
			return;
		}

		$session = $this->session->userdata('logged_user');
		$logged_id = $session['logged_id'];

		// Check subscription limits before allowing to add rental
		if (!$this->usermodel->canUserAddContent($logged_id, 'rental')) {
			$this->session->set_flashdata('message', 'You have reached your rental limit. Please upgrade your plan to add more rentals.');
			redirect('user/user-apartments-and-rental');
			return;
		}

		// Handle form submission
		if (isset($_POST['rental_submit'])) {
			// Basic validation
			$required_fields = ['rental_title', 'rental_price', 'rental_description', 'rental_address'];
			$validation_errors = [];

			foreach ($required_fields as $field) {
				if (empty($_POST[$field])) {
					$validation_errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
				}
			}

			if (!empty($validation_errors)) {
				$this->session->set_flashdata('message', implode('. ', $validation_errors));
				redirect('user/add-rental');
				return;
			}

			// Generate slug
			$rental_title = $_POST['rental_title'];
			$rental_slug = pearland_slug($rental_title . '-' . time());

			// Handle image upload
			$rental_image = '';
			if ($_FILES && $_FILES['rental_image']['name']) {
				$img_info = [
					'upload_path' => 'images/rentals',
					'quality'     => '60%',
					'height'      => 500,
					'width'       => 500,
					'image_name'  => 'rental_image'
				];
				$rental_image = uploadAndResize($img_info);
			}

			// Prepare data
			$rental_data = array(
				'user_id' => $logged_id,
				'rental_title' => $_POST['rental_title'],
				'rental_price' => $_POST['rental_price'],
				'rental_category_id' => isset($_POST['rental_category_id']) ? $_POST['rental_category_id'] : null,
				'rental_description' => $_POST['rental_description'],
				'rental_address' => $_POST['rental_address'],
				'bedrooms' => isset($_POST['bedrooms']) ? $_POST['bedrooms'] : '',
				'living_area' => isset($_POST['living_area']) ? $_POST['living_area'] : '',
				'bathroom' => isset($_POST['bathroom']) ? $_POST['bathroom'] : '',
				'property_age' => isset($_POST['property_age']) ? $_POST['property_age'] : '',
				'garage' => isset($_POST['garage']) ? $_POST['garage'] : '',
				'floors' => isset($_POST['floors']) ? $_POST['floors'] : '',
				'rent_to_own' => isset($_POST['rent_to_own']) ? 1 : 0,
				'can_sublet' => isset($_POST['can_sublet']) ? 1 : 0,
				'rental_seo_title' => isset($_POST['rental_seo_title']) ? $_POST['rental_seo_title'] : '',
				'rental_seo_keywords' => isset($_POST['rental_seo_keywords']) ? $_POST['rental_seo_keywords'] : '',
				'rental_seo_description' => isset($_POST['rental_seo_description']) ? $_POST['rental_seo_description'] : '',
				'featured_rental' => isset($_POST['featured_rental']) ? 1 : 0,
				'spotlight_rental' => isset($_POST['spotlight_rental']) ? 1 : 0,
				'display_contact' => isset($_POST['display_contact']) ? 1 : 0,
				'rental_image' => $rental_image,
				'rental_status' => 1, // Active by default
				'rental_slug' => $rental_slug,
				'created_at' => date('Y-m-d H:i:s')
			);

			// Insert rental
			if ($this->db->insert('rental', $rental_data)) {
				$this->session->set_flashdata('message', 'Rental added successfully!');
				redirect('user/user-apartments-and-rental');
			} else {
				$this->session->set_flashdata('message', 'Failed to add rental.');
				redirect('user/add-rental');
			}
		}

		// View Count Data


		// Load categories for dropdown
		$data['all_rental_category'] = $this->usermodel->allRentalCategory();

		// Load view
		$data['title'] = 'Add New Rental';
		$data['logged_id'] = $logged_id;

		$this->load->view('header', $data);
		$this->load->view('add_rental', $data);
		$this->load->view('footer');
	}

	/**
	 * Edit Rental - Edit existing rental post
	 * URL: /user/edit-rental/slug
	 */
	public function edit_rental($rental_slug)
	{
		// Check if user is logged in
		if (!$this->session->userdata('logged_user')) {
			redirect('user/login');
			return;
		}

		$session = $this->session->userdata('logged_user');
		$logged_id = $session['logged_id'];

		// Get rental details
		$rental = $this->usermodel->getSingleValue($rental_slug, 'rental_slug', 'rental');
		if (!$rental || $rental->user_id != $logged_id) {
			$this->session->set_flashdata('message', 'Rental not found or access denied.');
			redirect('user/user-apartments-and-rental');
			return;
		}

		// Handle form submission
		if (isset($_POST['rental_submit'])) {
			// Handle image upload
			$rental_image = $rental->rental_image; // Keep existing image by default
			if ($_FILES && $_FILES['rental_image']['name']) {
				$img_info = [
					'upload_path' => 'images/rentals',
					'quality'     => '60%',
					'height'      => 500,
					'width'       => 500,
					'image_name'  => 'rental_image'
				];
				$rental_image = uploadAndResize($img_info);

				// Delete old image if new one uploaded
				if (!empty($rental->rental_image) && file_exists('./' . $rental->rental_image)) {
					unlink('./' . $rental->rental_image);
				}
			}

			// Prepare update data
			$rental_data = array(
				'rental_title' => $_POST['rental_title'],
				'rental_price' => $_POST['rental_price'],
				'rental_category_id' => isset($_POST['rental_category_id']) ? $_POST['rental_category_id'] : null,
				'rental_description' => $_POST['rental_description'],
				'rental_address' => $_POST['rental_address'],
				'bedrooms' => isset($_POST['bedrooms']) ? $_POST['bedrooms'] : '',
				'living_area' => isset($_POST['living_area']) ? $_POST['living_area'] : '',
				'bathroom' => isset($_POST['bathroom']) ? $_POST['bathroom'] : '',
				'property_age' => isset($_POST['property_age']) ? $_POST['property_age'] : '',
				'garage' => isset($_POST['garage']) ? $_POST['garage'] : '',
				'floors' => isset($_POST['floors']) ? $_POST['floors'] : '',
				'rent_to_own' => isset($_POST['rent_to_own']) ? 1 : 0,
				'can_sublet' => isset($_POST['can_sublet']) ? 1 : 0,
				'rental_seo_title' => isset($_POST['rental_seo_title']) ? $_POST['rental_seo_title'] : '',
				'rental_seo_keywords' => isset($_POST['rental_seo_keywords']) ? $_POST['rental_seo_keywords'] : '',
				'rental_seo_description' => isset($_POST['rental_seo_description']) ? $_POST['rental_seo_description'] : '',
				'featured_rental' => isset($_POST['featured_rental']) ? 1 : 0,
				'spotlight_rental' => isset($_POST['spotlight_rental']) ? 1 : 0,
				'display_contact' => isset($_POST['display_contact']) ? 1 : 0,
				'rental_image' => $rental_image
			);

			// Update rental
			if ($this->db->where('rental_slug', $rental_slug)->update('rental', $rental_data)) {
				$this->session->set_flashdata('message', 'Rental updated successfully!');
				redirect('user/user-apartments-and-rental');
			} else {
				$this->session->set_flashdata('message', 'Failed to update rental.');
				redirect('user/edit-rental/' . $rental_slug);
			}
		}

		// Load categories for dropdown
		$data['all_rental_category'] = $this->usermodel->allRentalCategory();
		$data['rental'] = $rental;
		$data['title'] = 'Edit Rental';
		$data['logged_id'] = $logged_id;

		$this->load->view('header', $data);
		$this->load->view('user_left_sidebar');
		$this->load->view('edit_rental', $data);
		$this->load->view('user_right_sidebar');
		$this->load->view('footer');
	}

	/**
	 * Rental Details - Public rental viewing page
	 * URL: /user/rental-details/id
	 */
	public function rental_details($rental_slug = null)
	{
		if (!$this->requireService('service_realestate_enabled')) return;
		if (!$rental_slug) {
			show_404();
			return;
		}

		// Resolve rental by slug (with safe fallback)
		$rentals = $this->usermodel->getRentalBySlug($rental_slug);
		if (!$rentals) {
			show_404();
			return;
		}

		// Increment views count for this rental (use numeric id)
		$this->usermodel->incrementViews('rental', 'rental_id', $rentals->rental_id);

		// Get logged user ID for enquiry form
		$logged_id = NULL;
		if ($this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$logged_id = $session['logged_id'];
		}

		// Build share URLs dynamically for this rental (use slug in URL)
		$rental_url = base_url('user/rental-details/' . $rentals->rental_slug);
		$rental_title = $rentals->rental_title;
		$rental_image = !empty($rentals->rental_image) ? base_url($rentals->rental_image) : '';
		$data['share_urls'] = [
			'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($rental_url) . '&quote=' . urlencode($rental_title),
			'twitter' => 'https://twitter.com/share?text=' . urlencode($rental_title) . '&url=' . urlencode($rental_url),
			'whatsapp' => 'https://wa.me/?text=' . urlencode($rental_title . ' ' . $rental_url),
			'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode($rental_url),
			'pinterest' => 'https://www.pinterest.com/pin/create/bookmarklet/?media=' . urlencode($rental_image) . '&url=' . urlencode($rental_url) . '&description=' . urlencode($rental_title),
		];

		// Get 3 related rentals excluding current (prefer same category)
		$category_id = isset($rentals->rental_category_id) ? $rentals->rental_category_id : null;
		$data['related_rentals'] = $this->usermodel->getRelatedRentals($rentals->rental_id, $category_id, 3);

		$data['rentals'] = $rentals;
		$data['logged_id'] = $logged_id;
		$data['title'] = $rentals->rental_title;

		// Load header categories (keeps header consistent with other detail pages)
		$this->loadHeaderCategories($data);

		// Load views
		// Advertising. Six zones — three wide, three down the sidebar.
		$data['ad_slots'] = $this->pageAdSlots('full');

		$this->load->view('header', $data);
		$this->load->view('rental_details', $data);
		$this->load->view('footer');
	}

	/**
	 * Event Details Page
	 * Displays single event details page with slug support
	 */
	public function event_details($event_slug = null)
	{
		if (!$this->requireService('service_events_enabled')) return;
		if (!$event_slug) {
			show_404();
			return;
		}

		// Get logged user ID if available
		$data['logged_id'] = null;
		if ($this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$data['logged_id'] = $session['logged_id'];
			$data['user'] = $this->usermodel->getUserInformation();
		}

		// Get event by slug with fallback to name-based slug generation
		$event = $this->usermodel->getEventBySlug($event_slug);
		// Increment views for this event
		if ($event) {
			$this->usermodel->incrementViews('events', 'event_id', $event->event_id);
		}

		if (!$event) {
			show_404();
			return;
		}

		$data['event'] = $event;
		$data['title'] = $event->event_name . ' — Pearland411 Events';

		// Get related events excluding current event
		$data['related_events']  = $this->usermodel->getRelatedEvents($event->event_id, 3);
		$data['upcoming_events'] = $this->usermodel->getUpcomingEvents(5, (int) $event->event_id);

		// Who is hosting. Events are hosted by an admin (staff) account; fall
		// back to the legacy public user for events that predate admin_id.
		// This used to be worked out inside the view, via get_instance().
		$data['event_host'] = array('name' => 'Pearland411', 'image' => '', 'joined' => '');
		if (!empty($event->admin_id)) {
			$this->load->model('adminmodel');
			$event_admin = $this->adminmodel->getAdminByIdRaw($event->admin_id);
			if ($event_admin && !empty($event_admin->admin_name)) {
				$data['event_host']['name'] = $event_admin->admin_name;
			}
		} elseif (!empty($event->user_id)) {
			$event_user = $this->usermodel->getSingleValue($event->user_id, 'user_id', 'user');
			if ($event_user) {
				if (!empty($event_user->name))          $data['event_host']['name']   = $event_user->name;
				if (!empty($event_user->profile_image)) $data['event_host']['image']  = base_url($event_user->profile_image);
				if (!empty($event_user->user_created))  $data['event_host']['joined'] = date('M Y', strtotime($event_user->user_created));
			}
		}

		$data['ad_slots'] = $this->contentAdSlots();
		$this->usermodel->primeAdSlots(array_merge(
			array_values($data['ad_slots']),
			array('events-sidebar', 'right-banner-1', 'right-banner-2')
		));
		$data['page_css'] = $this->contentPageCss();

		// SEO: per-page meta + Event JSON-LD
		$desc = content_excerpt($event->event_description ?? '', 160);
		if ($desc === '') $desc = 'Event details for ' . $event->event_name;
		$data['meta_description'] = $desc;
		$data['canonical_url']    = base_url('event-details/' . rawurlencode(
			!empty($event->event_slug) ? $event->event_slug : pearland_slug($event->event_name)
		));
		if (!empty($event->event_image)) $data['og_image'] = base_url($event->event_image);

		$ld = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'Event',
			'name'        => (string) $event->event_name,
			'description' => $desc,
			'url'         => $data['canonical_url'],
			// Google flags an Event with no eventStatus/attendanceMode as
			// incomplete; both are safe defaults for a listed local event.
			'eventStatus' => 'https://schema.org/EventScheduled',
			'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
			'organizer'   => array('@type' => 'Organization', 'name' => $data['event_host']['name']),
		);
		if (!empty($event->event_start_date)) $ld['startDate'] = date('c', strtotime($event->event_start_date));
		if (!empty($event->event_end_date))   $ld['endDate']   = date('c', strtotime($event->event_end_date));
		if (!empty($event->event_address)) {
			$ld['location'] = array(
				'@type'   => 'Place',
				'name'    => (string) $event->event_address,
				'address' => (string) $event->event_address,
			);
		}
		if (!empty($event->event_image)) $ld['image'] = base_url($event->event_image);
		$data['json_ld'] = json_ld($ld);

		// Load header categories using professional MVC approach
		$this->loadHeaderCategories($data);

		// Load the event details view
		if (file_exists(APPPATH . 'views/event_details.php')) {
			$this->load->view('header', $data);
			$this->load->view('event_details', $data);
			$this->load->view('footer');
		} else {
			show_404();
		}
	}







	/**
	 * Add User Event - Fixed Version
	 */
	public function add_user_event()
	{
		// Check if user is logged in
		if (!$this->session->userdata('logged_user')) {
			redirect('user/login');
			return;
		}

		$session = $this->session->userdata('logged_user');
		$logged_id = $session['logged_id'];

		// Handle form submission
		if (isset($_POST['event_submit'])) {

			// Basic validation. Contact name, phone and email are deliberately
			// NOT here: plenty of events have no named contact, and requiring
			// all three meant those events could not be listed at all. Same
			// change in the admin_event_rules / event_rules groups.
			$required_fields = [
				'event_name',
				'event_address',
				'event_start_date',
				'event_time',
				'event_description'
			];

			$validation_errors = [];
			foreach ($required_fields as $field) {
				if (empty($_POST[$field])) {
					$validation_errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
				}
			}

			if (!empty($validation_errors)) {
				$this->session->set_flashdata('message', implode('. ', $validation_errors));
				redirect('user/add_user_event');
				return;
			}

			// Generate slug
			$event_name = $_POST['event_name'];
			$event_slug = pearland_slug($event_name . '-' . time());

			// Handle image upload via the safe helper — MIME-sniffed,
			// extension allow-listed, random filename, target dir
			// gets an auto-written .htaccess blocking PHP execution.
			$event_image = '';
			if (!empty($_FILES['event_image']['name'])) {
				$saved = safe_upload_image($_FILES['event_image'], 'uploads/events/');
				if ($saved !== false) {
					$event_image = $saved;
				}
			}

			// Prepare data - matching exact database fields
			$event_data = array(
				'user_id' => $logged_id,
				'event_name' => $_POST['event_name'],
				'event_description' => $_POST['event_description'],
				'event_image' => $event_image,
				'event_start_date' => $_POST['event_start_date'],
				'event_end_date' => $_POST['event_start_date'], // Same as start date
				'event_time' => $_POST['event_time'],
				'event_contact_name' => $_POST['event_contact_name'],
				'event_email' => $_POST['event_email'],
				'event_mobile' => $_POST['event_mobile'],
				'event_website' => $_POST['event_website'] ?? '',
				'event_address' => $_POST['event_address'],
				'event_map' => $_POST['event_map'] ?? '',
				'city_id' => 1, // Default
				'state_id' => 1, // Default
				'seo_title' => $_POST['event_name'],
				'seo_description' => substr($_POST['event_description'], 0, 160),
				'seo_keywords' => $_POST['event_name'],
				'event_status' => 1,
				'event_slug' => $event_slug,
				'event_type' => 'public',
				'isenquiry' => isset($_POST['isenquiry']) ? 1 : 0,
				'created_at' => date('Y-m-d H:i:s')
			);

			// Insert event using direct database insert
			if ($this->db->insert('events', $event_data)) {
				$this->session->set_flashdata('message', 'Event added successfully!');
				redirect('user/user-events');
			} else {
				$this->session->set_flashdata('message', 'Failed to add event. Database error: ' . $this->db->error()['message']);
				redirect('user/add_user_event');
			}
		}

		// Load view
		$data['title'] = 'Add New Event';
		$data['logged_id'] = $logged_id;

		$this->load->view('header', $data);
		$this->load->view('user_left_sidebar');
		$this->load->view('user_add_new_event', $data);
		$this->load->view('user_right_sidebar');
		$this->load->view('footer');
	}

	/*Edit User Event*/
	public function edit_user_event($event_slug = null)
	{
		// Check login
		if (!$this->session->userdata('logged_user')) {
			redirect('user/login');
		}

		$session = $this->session->userdata('logged_user');
		$user_id = $session['logged_id'];

		// Get event
		$event = $this->usermodel->getSingleValue($event_slug, 'event_slug', 'events');
		if (!$event) {
			show_404();
		}

		// Check ownership
		if ($event->user_id != $user_id) {
			$this->session->set_flashdata('message', 'Access denied');
			redirect('user/user-events');
		}

		// Process form submission
		if (isset($_POST['event_submit'])) {

			$info = array(
				'event_name' => $_POST['event_name'],
				'event_address' => $_POST['event_address'],
				'event_start_date' => $_POST['event_start_date'],
				'event_end_date' => $_POST['event_start_date'],
				'event_time' => $_POST['event_time'],
				'event_description' => $_POST['event_description'],
				'event_contact_name' => $_POST['event_contact_name'],
				'event_email' => $_POST['event_email'],
				'event_mobile' => $_POST['event_mobile'],
				'event_website' => $_POST['event_website'],
				'event_map' => $_POST['event_map'],
				'isenquiry' => isset($_POST['isenquiry']) ? 1 : 0
			);



			// Handle image upload
			if ($_FILES && $_FILES['event_image']['name']) {
				$img_info = [
					'upload_path' => 'images/events',
					'quality'     => '60%',
					'height'      => 500,
					'width'       => 500,
					'image_name'  => 'event_image'
				];
				$info['event_image'] = uploadAndResize($img_info);
			}

			// Update event in database
			$update_result = $this->usermodel->update($event->event_id, $info, 'event_id', 'events');

			if ($update_result) {
				echo "<div style='background: green; color: white; padding: 10px; margin: 10px;'>";
				echo "<h3>REDIRECTING TO SUCCESS PAGE...</h3>";
				echo "</div>";
				$this->session->set_flashdata('message', 'Event updated successfully!');
				redirect('user/user-events');
			} else {
				echo "<div style='background: red; color: white; padding: 10px; margin: 10px;'>";
				echo "<h3>UPDATE FAILED - STAYING ON EDIT PAGE</h3>";
				echo "</div>";
				$this->session->set_flashdata('message', 'Update failed!');
				redirect('user/edit_user_event/' . $event_slug);
			}
		}

		// Show form - EXACT same as listing
		$data['event'] = $event;
		$data['logged_id'] = $user_id;

		$this->load->view('header');
		$this->load->view('user_left_sidebar');
		$this->load->view('user_edit_event', $data);
		$this->load->view('user_right_sidebar');
		$this->load->view('footer');
	}

	/*====================
      REVIEW SUBMISSION
    ====================*/
	public function submit_review()
	{
		// Check if user is logged in
		if (!$this->session->userdata('logged_user')) {
			redirect('user/login');
			return;
		}

		// A GET here (a refresh of the redirect, a crawler) has nothing to save.
		if ($this->input->method() !== 'post') {
			redirect('all_listing');
			return;
		}

		// 10 reviews per IP per hour. Stops review-bombing.
		if (!$this->rate_limit->check_ip('submit_review', 10, 3600)) {
			$this->rate_limit->reject(3600);
			return;
		}

		// Get logged user info
		$session = $this->session->userdata('logged_user');
		$logged_id = $session['logged_id'];

		// Get form data
		$listing_id = $this->input->post('listing_id');
		$rating = $this->input->post('price_rating');
		$name = $this->input->post('review_name');
		$email = $this->input->post('review_email');
		$mobile = $this->input->post('review_mobile');
		$city = $this->input->post('review_city');
		$message = $this->input->post('review_message');

		// Get listing slug for proper redirect
		$listing = $this->db->get_where('listings', array('listing_id' => $listing_id))->row();
		$listing_slug = $listing ? $listing->listing_slug : $listing_id;

		// The star picker posts 1-5. Nothing checked that, and the value feeds
		// updateListingRating(), so a hand-posted `price_rating=500` moved the
		// business's public average to 500 stars. Clamp it, and refuse a review
		// with no listing behind it.
		$rating = (int) $rating;
		if ($rating < 1 || $rating > 5) {
			$rating = 0;
		}

		$errors = array();
		if (!$listing) {
			$errors[] = 'That business could not be found.';
		}
		if ($rating === 0) {
			$errors[] = 'Please choose a rating from one to five stars.';
		}
		if (trim((string) $message) === '') {
			$errors[] = 'Please write a few words about what you thought.';
		} elseif (mb_strlen((string) $message) > 2000) {
			$errors[] = 'That review is too long (2000 characters maximum).';
		}
		if (trim((string) $name) === '') {
			$errors[] = 'Please give the name to show on the review.';
		} elseif (mb_strlen(trim((string) $name)) > 100) {
			$errors[] = 'That name is too long (100 characters maximum).';
		}
		if (trim((string) $email) === '' || !filter_var(trim((string) $email), FILTER_VALIDATE_EMAIL)) {
			$errors[] = 'Please give a valid email address.';
		}

		if (!empty($errors)) {
			$this->session->set_flashdata('review_error', reset($errors));
			redirect($listing
				? 'listing-details/' . rawurlencode((string) $listing_slug) . '#write-review'
				: base_url('all_listing'));
			return;
		}

		$listing_id = (int) $listing->listing_id;
		$data = array(
			'listing_id' => $listing_id,
			'user_id' => (int) $logged_id,
			'reviewer_name' => trim((string) $name),
			'reviewer_email' => trim((string) $email),
			'reviewer_mobile' => (string) $mobile,
			'reviewer_city' => (string) $city,
			'rating' => $rating,
			'review_message' => (string) $message,
			'review_status' => 1,
		);

		// One review per member per business. Nothing checked before
		// inserting (hasUserReviewed() existed and was never called), so the
		// Back button and a second click each added another copy. A second
		// submission now REPLACES the member's earlier review — the newest
		// opinion is the one that counts — instead of stacking up.
		$existing = $this->db->select('review_id')
			->where('listing_id', $listing_id)
			->where('user_id', (int) $logged_id)
			->order_by('review_id', 'DESC')
			->limit(1)
			->get('listing_reviews')->row();

		// try/catch: on PHP 8.1+ a failed write throws (e.g. a double click
		// racing the 0034 unique key) instead of returning FALSE, and CI 3.1.11
		// would show it as a 500 page instead of the message below.
		try {
			if ($existing) {
				$ok = $this->db->where('review_id', (int) $existing->review_id)->update('listing_reviews', $data);
				$success_msg = 'Your review has been updated. You can only leave one review per business, so this replaces your earlier one.';
			} else {
				$data['created_at'] = date('Y-m-d H:i:s');
				$ok = $this->db->insert('listing_reviews', $data);
				$success_msg = 'Thank you! Your review has been submitted successfully.';
			}
		} catch (Exception $e) {
			$ok = FALSE;
			log_message('error', 'submit_review: ' . $e->getMessage());
		}

		if (!$ok) {
			// A failed insert used to be reported as a success.
			$err = $this->db->error();
			log_message('error', 'submit_review failed for listing ' . $listing_id . ': ' . ($err['message'] ?? 'unknown'));
			$this->session->set_flashdata('review_error', 'Sorry — your review could not be saved. Please try again in a moment.');
			redirect('listing-details/' . rawurlencode((string) $listing_slug) . '#write-review');
			return;
		}

		// Update listing average rating and review count
		$this->usermodel->updateListingRating($listing_id);

		// Set flash message
		$this->session->set_flashdata('review_success', $success_msg);

		// Back to the reviews section, not the top of the page — the banner
		// confirming the review sits beside the form it came from, and landing
		// in the hero left no sign that anything had happened.
		redirect('listing-details/' . rawurlencode((string) $listing_slug) . '#reviews');
	}

	/*===================
		Unified Enquiry Submission Handler
		Handles enquiries from all pages: listing, blog, event, product
		URL: /user/add-enquiry/{source}/{identifier}
	===================*/
	public function add_enquiry($source = null, $identifier = null)
	{
		// Check if form was submitted
		if ($this->input->method() !== 'post') {
			show_404();
			return;
		}

		// The enquiry table dump shows obvious anonymous spam — 8 per
		// IP per hour is generous for a real visitor messaging
		// multiple businesses, restrictive enough to deter bots.
		if (!$this->rate_limit->check_ip('add_enquiry', 8, 3600)) {
			$this->rate_limit->reject(3600);
			return;
		}

		// Get form data
		$enquiry_name = $this->input->post('enquiry_name');
		$enquiry_email = $this->input->post('enquiry_email');
		$enquiry_mobile = $this->input->post('enquiry_mobile');
		$enquiry_message = $this->input->post('enquiry_message');
		$enquiry_source = $this->input->post('enquiry_source');
		$enquiry_type = $this->input->post('enquiry_type');

		// Who is asking comes from the session and who it is for comes from
		// the content — never from the form. Both used to be hidden fields
		// taken as posted, so anyone could file an enquiry in another
		// member's name, or address one to any account id they liked.
		$__sess = (array) $this->session->userdata('logged_user');
		$enquiry_sender_id = !empty($__sess['logged_id']) ? (int) $__sess['logged_id'] : NULL;
		$receiving_user_id = NULL;

		// Get individual IDs from form (support multiple content types)
		$listing_id = $this->input->post('listing_id') ?: null;
		$blog_id = $this->input->post('blog_id') ?: null;
		$event_id = $this->input->post('event_id') ?: null;
		$product_id = $this->input->post('product_id') ?: null;
		// Additional possible id fields from various detail pages
		$news_id = $this->input->post('news_id') ?: null;
		$classifieds_id = $this->input->post('classifieds_id') ?: null;
		$rental_id = $this->input->post('rental_id') ?: null;
		$video_id = $this->input->post('video_id') ?: null;

		// Validation.
		//
		// This used to be `if (empty($name) || empty($email))` and nothing else
		// — no email format check, no length limits, no phone rule. The only
		// real constraints were the `required` / type="email" / pattern
		// attributes in the markup, which are gone the moment someone posts
		// directly or turns JavaScript off. That is "little constraint in
		// entering texts". The errors are keyed by field name so the page can
		// mark the offending box instead of showing one flat banner.
		$errors = array();

		if ($enquiry_name === NULL || trim($enquiry_name) === '') {
			$errors['enquiry_name'] = 'Please tell us your name.';
		} elseif (mb_strlen(trim($enquiry_name)) > 100) {
			$errors['enquiry_name'] = 'That name is too long (100 characters maximum).';
		}

		if ($enquiry_email === NULL || trim($enquiry_email) === '') {
			$errors['enquiry_email'] = 'Please give us an email address so they can reply.';
		} elseif (!filter_var(trim($enquiry_email), FILTER_VALIDATE_EMAIL)) {
			$errors['enquiry_email'] = 'That does not look like an email address.';
		} elseif (mb_strlen(trim($enquiry_email)) > 190) {
			$errors['enquiry_email'] = 'That email address is too long.';
		}

		if ($enquiry_mobile !== NULL && trim($enquiry_mobile) !== '') {
			if (!preg_match('/^[0-9+\-\s()]{7,20}$/', trim($enquiry_mobile))) {
				$errors['enquiry_mobile'] = 'Please use digits, spaces, brackets, + or - only.';
			}
		}

		if ($enquiry_message !== NULL && mb_strlen($enquiry_message) > 5000) {
			// A 10 MB message used to be inserted verbatim.
			$errors['enquiry_message'] = 'That message is too long (5000 characters maximum).';
		}

		if (!empty($errors)) {
			if ($this->input->is_ajax_request()) {
				echo json_encode(array(
					'success' => false,
					'message' => reset($errors),
					'errors'  => $errors,
				));
				return;
			}
			// Keep what they typed. The old branch flashed a message and
			// redirected, discarding the form, so "try again" meant retyping
			// everything — and it redirected to a raw $_SERVER['HTTP_REFERER']
			// with no isset() guard, which with no referer is an empty URL.
			$this->session->set_flashdata('error_msg', reset($errors));
			$this->session->set_flashdata('enquiry_errors', $errors);
			$this->session->set_flashdata('enquiry_old', array(
				'enquiry_name'    => $enquiry_name,
				'enquiry_email'   => $enquiry_email,
				'enquiry_mobile'  => $enquiry_mobile,
				'enquiry_message' => $enquiry_message,
			));
			redirect($this->withEnquiryAnchor($this->enquiryReturnUrl(), $source));
			return;
		}

		// Additional validation for required fields
		if (empty($enquiry_type)) {
			// Try to determine type from source parameter
			switch ($source) {
				case 'listing_details':
					$enquiry_type = 'listing';
					break;
				case 'blog_details':
					$enquiry_type = 'blog';
					break;
				case 'event_details':
					$enquiry_type = 'event';
					break;
				case 'product_details':
					$enquiry_type = 'product';
					break;
				default:
					$enquiry_type = 'general';
			}
		}

		// The owner of the thing being asked about. The URL segment comes
		// first for the pages whose forms posted the wrong id field (house
		// sent its slug as news_id, rentals and classifieds sent theirs as
		// product_id); the posted ids are the fallback for everything else.
		$__listing = NULL;
		if ($source === 'listing_details' && !empty($identifier)) {
			$__listing = $this->db->get_where('listings', array('listing_slug' => (string) $identifier))->row();
			if ($__listing) $listing_id = $__listing->listing_id;
		}
		if (!$__listing && !empty($listing_id)) {
			$__listing = $this->db->get_where('listings', array('listing_id' => (int) $listing_id))->row();
		}
		if ($source === 'house_details' && !empty($identifier)) {
			$__house = $this->db->get_where('house', array('house_slug' => (string) $identifier))->row();
			$receiving_user_id = $__house ? $__house->user_id : NULL;
			$news_id = NULL;
		} elseif ($source === 'rental_details' && !empty($identifier)) {
			$__rental = $this->db->get_where('rental', array('rental_slug' => (string) $identifier))->row();
			$receiving_user_id = $__rental ? $__rental->user_id : NULL;
			if ($__rental) $rental_id = $__rental->rental_id;
			$product_id = NULL;
		} elseif ($source === 'classifieds_details' && !empty($identifier)) {
			$__cls = $this->db->get_where('classifieds', array('classifieds_id' => (int) $identifier))->row();
			$receiving_user_id = $__cls ? $__cls->user_id : NULL;
			if ($__cls) $classifieds_id = $__cls->classifieds_id;
			$product_id = NULL;
		}

		if (empty($receiving_user_id)) {
			if ($__listing) {
				$receiving_user_id = $__listing->user_id;
			} elseif (!empty($blog_id)) {
				$blog = $this->db->get_where('blogs', array('blog_id' => $blog_id))->row();
				$receiving_user_id = $blog ? $blog->user_id : null;
			} elseif (!empty($event_id)) {
				$event = $this->db->get_where('events', array('event_slug' => $event_id))->row();
				$receiving_user_id = $event ? $event->user_id : null;
			} elseif (!empty($product_id)) {
				$product = $this->db->get_where('products', array('product_id' => $product_id))->row();
				$receiving_user_id = $product ? $product->user_id : null;
			} elseif (!empty($news_id)) {
				$news = $this->db->get_where('news', array('news_slug' => $news_id))->row();
				$receiving_user_id = $news ? $news->user_id : null;
			} elseif (!empty($classifieds_id)) {
				$cls = $this->db->get_where('classifieds', array('classifieds_id' => $classifieds_id))->row();
				$receiving_user_id = $cls ? $cls->user_id : null;
			} elseif (!empty($rental_id)) {
				$rental = $this->db->get_where('rental', array('rental_id' => $rental_id))->row();
				$receiving_user_id = $rental ? $rental->user_id : null;
			} elseif (!empty($video_id)) {
				$videoRow = $this->db->get_where('video', array('video_slug' => $video_id))->row();
				$receiving_user_id = $videoRow ? $videoRow->user_id : null;
			}
		}

		// Set default values for required fields
		$enquiry_source = !empty($enquiry_source) ? $enquiry_source : 'Website';
		$enquiry_message = !empty($enquiry_message) ? $enquiry_message : '';
		$enquiry_mobile = !empty($enquiry_mobile) ? $enquiry_mobile : '';

		// Prepare data for database
		// Build data array; include any available content-specific id fields so enquires can be associated
		$data = array(
			'listing_id' => $listing_id,
			'event_id' => $event_id,
			'blog_id' => $blog_id,
			'product_id' => $product_id,
			'news_id' => $news_id,
			'classifieds_id' => $classifieds_id,
			'rental_id' => $rental_id,
			'video_id' => $video_id,
			'receiving_user_id' => $receiving_user_id,
			'enquiry_sender_id' => $enquiry_sender_id,
			'enquiry_source' => $enquiry_source,
			'enquiry_name' => $enquiry_name,
			'enquiry_email' => $enquiry_email,
			'enquiry_mobile' => $enquiry_mobile,
			'enquiry_message' => $enquiry_message,
			'enquiry_type' => $enquiry_type,
			'enquiry_save' => 1,
			'enquiry_created_at' => date('Y-m-d H:i:s')
		);

		// Insert enquiry into database
		$enquiry_success = $this->usermodel->addEnquiry($data);

		// Tell somebody. Nothing was emailed before: a lead sat in the table
		// until the owner happened to open /user/enquiries.
		if ($enquiry_success) {
			$this->notifyEnquiry($data, $__listing);
		}

		// Check if it's an AJAX request
		if ($this->input->is_ajax_request()) {
			// Return JSON response for AJAX requests
			if ($enquiry_success) {
				echo json_encode(['success' => true, 'message' => 'Your enquiry has been submitted successfully! We will get back to you soon.']);
			} else {
				echo json_encode(['success' => false, 'message' => 'Something went wrong! Please try again.']);
			}
			return;
		}

		// Set flashdata for non-AJAX requests
		if ($enquiry_success) {
			$this->session->set_flashdata('message', 'Your enquiry has been submitted successfully! We will get back to you soon.');
		} else {
			$this->session->set_flashdata('error_msg', 'Something went wrong! Please try again.');
		}

		// Dynamic redirect back to the same page for non-AJAX requests — to
		// the form itself, where the confirmation is printed. Without the
		// anchor a phone landed at the top of the page, the banner a long
		// scroll below, and it looked as if nothing had happened.
		$redirect_url = $this->getDynamicRedirectUrl($source, $identifier);
		redirect($this->withEnquiryAnchor($redirect_url, $source));
	}

	/**
	 * Append the enquiry card's id to a return URL so the browser scrolls to
	 * the confirmation (or the error) rather than the top of the page.
	 */
	private function withEnquiryAnchor($url, $source)
	{
		$anchors = array(
			'listing_details'     => 'get-a-quote',
			'classifieds_details' => 'get-a-quote',
			'house_details'       => 'get-a-quote',
			'rental_details'      => 'get-a-quote',
			'event_details'       => 'enquire',
			'news_details'        => 'enquire',
			'video_details'       => 'enquire',
		);
		if (!isset($anchors[$source])) return $url;

		$url = preg_replace('/#.*$/', '', (string) $url);
		return $url . '#' . $anchors[$source];
	}

	/**
	 * Email a new enquiry to whoever should answer it, with the sender as
	 * Reply-To so hitting Reply reaches the visitor and not the site's
	 * no-reply box.
	 *
	 * The business first: for a listing, its public listing_email, else the
	 * owning account's address. The site's forms inbox
	 * (form_recipient_email()) gets it when there is no owner address — so
	 * a lead is never only in the table — and always when
	 * `enquiry_copy_admin` is '1' at /admin/setting.
	 */
	private function notifyEnquiry(array $row, $listing = NULL)
	{
		$to = array();

		if ($listing && !empty($listing->listing_email) && filter_var(trim($listing->listing_email), FILTER_VALIDATE_EMAIL)) {
			$to[] = trim($listing->listing_email);
		}
		if (!$to && !empty($row['receiving_user_id'])) {
			$owner = $this->db->select('email_id')
				->where('user_id', (int) $row['receiving_user_id'])
				->limit(1)->get('user')->row();
			if ($owner && !empty($owner->email_id) && filter_var(trim($owner->email_id), FILTER_VALIDATE_EMAIL)) {
				$to[] = trim($owner->email_id);
			}
		}

		$admin = form_recipient_email();
		if ($admin !== '' && (!$to || site_setting('enquiry_copy_admin', '0') === '1')) {
			$to[] = $admin;
		}
		$to = array_values(array_unique(array_map('strtolower', $to)));
		if (!$to) return FALSE;

		$about = (string) ($row['enquiry_source'] ?? 'your listing');

		$body = '<p>Someone has sent an enquiry about <strong>' . html_escape($about) . '</strong> on Pearland411.</p>'
			. '<ul>'
			. '<li>Name: '  . html_escape((string) ($row['enquiry_name']   ?? '')) . '</li>'
			. '<li>Email: ' . html_escape((string) ($row['enquiry_email']  ?? '')) . '</li>'
			. '<li>Phone: ' . html_escape((string) ($row['enquiry_mobile'] ?? '')) . '</li>'
			. '</ul>'
			. '<p>Message:</p><blockquote>' . nl2br(html_escape((string) ($row['enquiry_message'] ?? ''))) . '</blockquote>'
			. '<p>Reply to this email to answer them directly. Your enquiries are also listed at '
			. '<a href="' . html_escape(base_url('user/enquiries')) . '">' . html_escape(base_url('user/enquiries')) . '</a>.</p>';

		$this->load->library('mailer');
		$html = $this->mailer->render('New enquiry', $body, array(
			'preheader' => 'New enquiry from ' . (string) ($row['enquiry_name'] ?? 'a visitor'),
		));

		return $this->mailer->send(
			$to,
			'[Pearland411] New enquiry about ' . $about,
			$html,
			NULL,
			array(
				'reply_to'      => (string) ($row['enquiry_email'] ?? ''),
				'reply_to_name' => (string) ($row['enquiry_name']  ?? ''),
				'log_type'      => 'enquiry',
			)
		);
	}

	/*===================
		Helper function to get redirect URL based on source and identifier
	===================*/
	private function getDynamicRedirectUrl($source, $identifier)
	{
		// If a same-origin HTTP_REFERER is available, use it (most reliable).
		// enquiryReturnUrl() does the isset() guard and the host check that the
		// raw superglobal read here did not.
		$referer = $this->enquiryReturnUrl();
		if ($referer !== base_url()) {
			return $referer;
		}

		// Fallback: construct URL based on source and identifier
		switch ($source) {
			case 'listing_details':
				if ($identifier) {
					// Try to get listing slug
					$listing = $this->db->get_where('listings', array('listing_slug' => $identifier))->row();
					if ($listing) {
						return base_url('user/listing_details/' . $identifier);
					}
				}
				return base_url('all_listing');

			case 'blog_details':
				if ($identifier) {
					return base_url('user/blog_details/' . $identifier);
				}
				return base_url('user/all-blogs');

			case 'event_details':
				if ($identifier) {
					return base_url('user/event_details/' . $identifier);
				}
				return base_url('user/all-events');

			case 'news_details':
				if ($identifier) {
					return base_url('user/news_details/' . $identifier);
				}
				return base_url('user/all-news');

			case 'video_details':
				if ($identifier) {
					return base_url('user/video-details/' . $identifier);
				}
				return base_url('user/all-videos');

			case 'classifieds_details':
				if ($identifier) {
					return base_url('user/classifieds_details/' . $identifier);
				}
				return base_url('user/all-classifieds');

			case 'rental_details':
				if ($identifier) {
					return base_url('user/rental_details/' . $identifier);
				}
				return base_url('user/all-rentals');

			case 'product_details':
				if ($identifier) {
					return base_url('user/product_details/' . $identifier);
				}
				return base_url('user/all-products');

			default:
				return base_url();
		}
	}

	/*==========================
		LISTING REVIEWS MANAGEMENT
	===========================*/

	/**
	 * Display user listing reviews page
	 * Following existing user dashboard pattern
	 */
	public function user_listing_reviews($page = 1)
	{
		// Check if user is logged in
		if (!$this->session->userdata('logged_user')) {
			redirect('login');
			return;
		}

		$logged_id = $this->session->userdata('logged_user')['logged_id'];

		// Load required libraries and models  
		$this->load->library('pagination');
		$this->load->model('usermodel');

		// Set up pagination
		$per_page = 20;
		$page = max(1, (int)$page);
		$offset = ($page - 1) * $per_page;

		// Get user's listing reviews with pagination
		$total_reviews = $this->usermodel->getUserListingReviewsCount($logged_id);
		$AllReviews = $this->usermodel->getUserListingReviews($logged_id, $per_page, $offset);

		// Pagination configuration - matching existing user dashboard pattern
		$config = array(
			'base_url' => base_url('user/user_listing_reviews/'),
			'total_rows' => $total_reviews,
			'per_page' => $per_page,
			'cur_page' => $page,
			'use_page_numbers' => TRUE,
			'reuse_query_string' => TRUE,
			'page_query_string' => FALSE,
			'uri_segment' => 2,
			'num_links' => 3,

			// Bootstrap pagination styling
			'full_tag_open' => '<nav><ul class="pagination justify-content-center">',
			'full_tag_close' => '</ul></nav>',
			'first_link' => 'First',
			'first_tag_open' => '<li class="page-item">',
			'first_tag_close' => '</li>',
			'last_link' => 'Last',
			'last_tag_open' => '<li class="page-item">',
			'last_tag_close' => '</li>',
			'next_link' => 'Next',
			'next_tag_open' => '<li class="page-item">',
			'next_tag_close' => '</li>',
			'prev_link' => 'Prev',
			'prev_tag_open' => '<li class="page-item">',
			'prev_tag_close' => '</li>',
			'cur_tag_open' => '<li class="page-item active"><a class="page-link" href="#">',
			'cur_tag_close' => '</a></li>',
			'num_tag_open' => '<li class="page-item">',
			'num_tag_close' => '</li>',
			'attributes' => array('class' => 'page-link')
		);

		$this->pagination->initialize($config);
		$links = $this->pagination->create_links();

		// Get user information for sidebar
		$userInfo = $this->usermodel->getUserInformation();

		// Prepare data for view
		$data = array(
			'AllReviews' => $AllReviews,
			'total_rows' => $total_reviews,
			'per_page' => $per_page,
			'start_number' => $offset + 1,
			'link' => $links,
			'page_title' => 'Listing Reviews',
			'userInfo' => $userInfo
		);

		// Load view with user dashboard layout - following existing pattern
		$this->load->view('header');
		$this->load->view('user_left_sidebar', $data);
		$this->load->view('user_listing_reviews', $data);
		$this->load->view('footer');
	}

	/**
	 * AJAX endpoint for updating review status
	 * Handles approve/cancel actions for pending reviews
	 */
	public function update_review_status()
	{
		// Debug logging
		error_log("=== REVIEW STATUS UPDATE CALLED ===");
		error_log("POST DATA: " . print_r($_POST, true));
		error_log("REQUEST METHOD: " . $_SERVER['REQUEST_METHOD']);

		// Set JSON response header
		header('Content-Type: application/json');

		// Check if user is logged in
		if (!$this->session->userdata('logged_user')) {
			error_log("ERROR: User not authenticated");
			echo json_encode(['success' => false, 'message' => 'Not authenticated']);
			return;
		}

		$logged_id = $this->session->userdata('logged_user')['logged_id'];
		error_log("User ID: " . $logged_id);

		// Validate POST data
		$review_id = $this->input->post('review_id');
		$status = $this->input->post('status');

		error_log("Review ID: " . $review_id);
		error_log("Status: " . $status);

		if (!$review_id || !$status) {
			error_log("ERROR: Missing required parameters");
			echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
			return;
		}

		// Validate status
		$allowed_statuses = ['pending', 'approved', 'cancelled'];
		if (!in_array($status, $allowed_statuses)) {
			error_log("ERROR: Invalid status - " . $status);
			echo json_encode(['success' => false, 'message' => 'Invalid status']);
			return;
		}

		$this->load->model('usermodel');

		// Verify that this review belongs to user's listing
		$ownership_check = $this->usermodel->verifyReviewOwnership($review_id, $logged_id);
		error_log("Ownership check result: " . ($ownership_check ? 'true' : 'false'));

		if (!$ownership_check) {
			error_log("ERROR: Not authorized to update this review");
			echo json_encode(['success' => false, 'message' => 'Not authorized to update this review']);
			return;
		}

		// Update review status
		$update_result = $this->usermodel->updateReviewStatus($review_id, $status);
		error_log("Update result: " . ($update_result ? 'true' : 'false'));

		if ($update_result) {
			error_log("SUCCESS: Review status updated successfully");
			echo json_encode([
				'success' => true,
				'message' => 'Review status updated successfully'
			]);
		} else {
			error_log("ERROR: Failed to update review status");
			echo json_encode([
				'success' => false,
				'message' => 'Failed to update review status'
			]);
		}
	}

	// Handle clean URLs - this method redirects /all_listing to /user/all_listing
	public function handle_clean_url($category = null)
	{
		// Redirect to the proper URL structure
		if ($category) {
			redirect('user/all_listing/' . $category, 'location', 301);
		} else {
			redirect('user/all_listing', 'location', 301);
		}
	}

	/**
	 * Pricing Plans Page
	 * URL: /pricing-plan
	 * Displays all active pricing plans with user subscription status
	 */
	public function pricing_plan()
	{
		// Load model
		$this->load->model('usermodel');

		// Get all active plans ordered by sort_order
		$data['plans'] = $this->usermodel->getActivePlans();

		// Get user subscription info (if logged in)
		$data['user_subscription'] = null;
		$data['is_logged_in'] = (bool) $this->session->userdata('logged_user');
		if ($this->session->userdata('logged_user')) {
			$user_data = $this->session->userdata('logged_user');
			$user_id = $user_data['logged_id']; // Use logged_id based on the User model pattern
			$data['user_subscription'] = $this->usermodel->getUserActiveSubscriptionForPricing($user_id);
			// "My packages" strip: what they own and when each expires.
			$data['my_packages'] = $this->usermodel->getUserPurchasedPackages($user_id);
		}
		if (!isset($data['my_packages'])) {
			$data['my_packages'] = array();
		}
		$data['cart'] = $this->cartSummary();

		// Load header categories for navigation
		$this->loadHeaderCategories($data);

		// Page title and meta
		$data['title'] = 'Pricing Plans - Choose Your Plan';

		// Load views
		// No advertising on this page. This page sells the ad packages. Three house ads pitching ad packages on
		// the page that sells ad packages is circular — the content is the pitch.

		$this->load->view('header', $data);
		$this->load->view('pricing_plan', $data);
		$this->load->view('footer', $data);
	}

	/**
	 * POST /start-free — put a signed-in visitor on a zero-cost plan.
	 *
	 * The pricing page's "Get started free" button used to point at /register
	 * whenever `$user_subscription` was empty, which is also true for a member
	 * who is logged in but has not bought anything — so choosing the free tier
	 * showed them a signup form. Free plans are excluded from the cart and
	 * checkout (see cart_add()), so there was no other way to actually get one.
	 * This gives them one: it creates the subscription and drops them on their
	 * listings dashboard, where the plan's allowance is now live.
	 */
	public function start_free_plan()
	{
		if (!$this->session->userdata('logged_user')) {
			redirect('register');
			return;
		}
		$session = $this->session->userdata('logged_user');
		$user_id = (int) $session['logged_id'];

		$plan_id = (int) $this->input->post('plan_id');
		$plan    = $plan_id ? $this->usermodel->getPlanByIdForPricing($plan_id) : null;

		// Only ever hand out an active, genuinely free plan this way: a price
		// that is SET to 0 (a NULL price is a half-configured plan, and
		// (float) NULL is 0 — that is how a paid tier could be had for
		// nothing), a business-listing tier, and no paid term on it.
		$paid_term = FALSE;
		if ($plan) {
			foreach ($this->usermodel->getPlanDurations((int) $plan->id) as $__d) {
				if ((float) $__d->price > 0) { $paid_term = TRUE; break; }
			}
		}
		if (!$plan || $plan->price === NULL || $plan->price === '' || (float) $plan->price > 0
			|| (isset($plan->category) && $plan->category !== '' && $plan->category !== 'business-listing')
			|| $paid_term) {
			$this->session->set_flashdata('message', 'That plan is not available for free signup.');
			redirect('pricing-plan');
			return;
		}

		// Never downgrade somebody who is already paying for something.
		$current = $this->usermodel->getUserSubscription($user_id);
		if ($current) {
			$this->session->set_flashdata('message', 'You are already on the ' . $current->name . ' plan.');
			redirect('user/user-listing');
			return;
		}

		if ($this->usermodel->activateFreePlan($user_id, (int) $plan->id)) {
			$this->session->set_flashdata('message', 'You are on the ' . $plan->name . ' plan. You can add your listing now.');
			redirect('user/user-listing');
		} else {
			$this->session->set_flashdata('message', 'We could not start your free plan. Please try again.');
			redirect('pricing-plan');
		}
	}

	/**
	 * Checkout Page - Display checkout form with selected plan
	 * URL: /checkout?plan_id=X
	 */
	public function checkout()
	{
		// Old single-plan links (?plan_id=X) still work — fold that plan into
		// the cart, then everything below is cart-driven.
		$plan_id = $this->input->get('plan_id');
		if ($plan_id && is_numeric($plan_id)) {
			$p = $this->usermodel->getPlanByIdForPricing((int) $plan_id);
			if ($p && (float) $p->price > 0) {
				$lines = $this->cartLines();
				if ((isset($p->category) ? $p->category : '') === 'business-listing') {
					$lines = array_filter($lines, function ($l) {
						$x = $this->usermodel->getPlanByIdForPricing((int) $l['plan_id']);
						return $x && (isset($x->category) ? $x->category : '') !== 'business-listing';
					});
				}
				$already = false;
				foreach ($lines as $l) {
					if ((int) $l['plan_id'] === (int) $plan_id) { $already = true; break; }
				}
				if (!$already) {
					$lines[] = array(
						'plan_id'     => (int) $plan_id,
						'duration_id' => (int) $this->input->get('duration_id') ?: null,
						'multiplier'  => max(1, (int) $this->input->get('multiplier') ?: 1),
					);
				}
				$this->saveCartLines($lines);
			}
		}

		$data['cart'] = $this->cartSummary();
		if (empty($data['cart']['items'])) {
			$this->session->set_flashdata('error', 'Your cart is empty — please choose a package.');
			redirect('pricing-plan');
			return;
		}

		// No forced login: guests review the cart and sign in / register right
		// on this page. Bring them back to checkout after they authenticate.
		$data['is_logged_in'] = (bool) $this->session->userdata('logged_user');
		if (!$data['is_logged_in']) {
			$this->session->set_userdata('redirect_url', base_url('checkout'));
		}

		// Billing prefill for logged-in users.
		$data['billing_info'] = array();
		if ($data['is_logged_in']) {
			$user_info = $this->usermodel->getUserInformation();
			if ($user_info) {
				$data['billing_info'] = array(
					'name'  => isset($user_info->name) ? $user_info->name : '',
					'email' => isset($user_info->email_id) ? $user_info->email_id : '',
				);
			}
		}

		$this->loadHeaderCategories($data);
		$data['title'] = 'Checkout';

		// Category-specific banners/videos ask which category they are for.
		$data['cart_categories'] = array();
		foreach ($data['cart']['items'] as $__it) {
			if (!empty($__it['needs_category'])) {
				$data['cart_categories'] = $this->usermodel->getCategoriesForHeader(2000);
				break;
			}
		}

		// Helcim front-end config.
		$this->load->library('helcim_lib');
		$data['helcim_configured'] = $this->helcim_lib->is_configured();
		$data['helcimpay_js_url']  = $this->helcim_lib->helcimpay_js_url();

		$this->load->view('header', $data);
		$this->load->view('cart_checkout', $data);
		$this->load->view('footer');
	}

	/**
	 * AJAX — initialize a HelcimPay.js checkout session for the selected plan
	 * + duration and return the checkoutToken. The secretToken is stashed in
	 * the session (never sent to the browser) so process_checkout() can
	 * validate the returned transaction hash. URL: /helcim-init (POST)
	 */
	public function helcim_init()
	{
		$this->output->set_content_type('application/json');

		if ($this->input->method() !== 'post' || !$this->session->userdata('logged_user')) {
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'Not allowed.')));
		}

		$plan_id     = (int) $this->input->post('plan_id');
		$duration_id = $this->input->post('duration_id') ? (int) $this->input->post('duration_id') : null;
		$auto_renew  = $this->input->post('auto_renew') === '1' ? 1 : 0;

		$plan = $this->usermodel->getPlanByIdForPricing($plan_id);
		if (!$plan) {
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'Invalid plan.')));
		}

		// Resolve the amount server-side from the plan/duration — never trust a
		// client-supplied price.
		$amount   = (float) $plan->price;
		$duration = null;
		if ($duration_id) {
			$duration = $this->usermodel->getDurationById($duration_id);
			if ($duration && (int) $duration->plan_id === $plan_id) {
				$amount = (float) $duration->price;
			} else {
				$duration = null;
			}
		}
		if ($amount <= 0) {
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'This plan has no payable amount configured.')));
		}

		$this->load->library('helcim_lib');
		$init = $this->helcim_lib->initialize_checkout($amount, null, array(
			'description' => 'Pearland411 — ' . $plan->name,
		));
		if (!$init['success']) {
			log_message('error', 'Helcim init failed: ' . $init['error']);
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'Could not start payment. Please try again.')));
		}

		$user = $this->session->userdata('logged_user');
		$this->session->set_userdata('helcim_pending', array(
			'checkoutToken' => $init['checkoutToken'],
			'secretToken'   => $init['secretToken'],
			'plan_id'       => $plan_id,
			'duration_id'   => $duration_id,
			'amount'        => $amount,
			'currency'      => $this->helcim_lib->currency(),
			'user_id'       => (int) $user['logged_id'],
			'auto_renew'    => $auto_renew,
			'mode'          => 'new',
		));

		return $this->output->set_output(json_encode(array(
			'success'       => true,
			'checkoutToken' => $init['checkoutToken'],
		)));
	}

	/**
	 * Process Checkout — validate the HelcimPay.js transaction and activate
	 * the subscription. Called via AJAX from the checkout page's SUCCESS
	 * handler, which posts the transaction data + hash + checkoutToken.
	 *
	 * Card data is tokenized by HelcimPay.js and never reaches this server.
	 * We trust the payment only after recomputing and matching the response
	 * hash against the secretToken we stored at initialize time. Idempotent —
	 * a replayed post for an already-processed token is a no-op success.
	 *
	 * URL: /process-checkout (POST, JSON response)
	 */
	public function process_checkout()
	{
		$this->output->set_content_type('application/json');

		if ($this->input->method() !== 'post' || !$this->session->userdata('logged_user')) {
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'Not allowed.')));
		}

		$pending = $this->session->userdata('helcim_pending');
		if (!$pending || empty($pending['secretToken'])) {
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'Your session expired. Please try again.')));
		}

		$checkout_token   = (string) $this->input->post('checkoutToken');
		$transaction_json = (string) $this->input->post('transaction_json'); // exact JSON string that was hashed
		$hash             = (string) $this->input->post('hash');

		if ($checkout_token === '' || $checkout_token !== $pending['checkoutToken']) {
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'Payment token mismatch.')));
		}

		$this->load->library('helcim_lib');
		if (!$this->helcim_lib->validate_transaction($transaction_json, $hash, $pending['secretToken'])) {
			log_message('error', 'Helcim hash validation failed for checkoutToken ' . $checkout_token);
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'We could not verify this payment. You were not charged twice — please contact support if the amount shows as taken.')));
		}

		// Pull the useful fields out of the validated transaction data.
		$txn = json_decode($transaction_json, true);
		$txn = is_array($txn) ? $txn : array();
		$helcim_txn_id = isset($txn['transactionId']) ? $txn['transactionId'] : (isset($txn['id']) ? $txn['id'] : null);
		$approval_code = isset($txn['approvalCode']) ? $txn['approvalCode'] : null;
		$card_token    = isset($txn['cardToken']) ? $txn['cardToken'] : null;

		// Cart mode: fulfill every purchased item (listing tier activates the
		// capability subscription; other items are recorded for admin fulfillment).
		if (isset($pending['mode']) && $pending['mode'] === 'cart') {
			$ok = $this->usermodel->recordCartPurchase(
				(int) $pending['user_id'],
				isset($pending['cart_items']) ? $pending['cart_items'] : array(),
				(float) $pending['amount'],
				$pending['currency'],
				$helcim_txn_id,
				$approval_code,
				!empty($pending['auto_renew']) ? 1 : 0
			);
			if (!$ok) {
				return $this->output->set_output(json_encode(array('success' => false, 'error' => 'Payment verified but recording failed. Contact support with transaction id ' . $helcim_txn_id . '.')));
			}
			$count = count($pending['cart_items']);
			$this->session->unset_userdata('helcim_pending');
			$this->session->unset_userdata('ad_cart');
			$this->session->set_userdata('checkout_success_data', array(
				'plan_name'       => 'Your order (' . $count . ' item' . ($count === 1 ? '' : 's') . ')',
				'amount'          => (float) $pending['amount'],
				'currency'        => $pending['currency'],
				'transaction_id'  => $helcim_txn_id,
				'subscription_id' => null,
			));
			return $this->output->set_output(json_encode(array('success' => true, 'redirect' => base_url('checkout-success'))));
		}

		// Upgrade mode only charges the prorated difference and switches the
		// plan in place (keeping the current term); see switch_plan().
		if (isset($pending['mode']) && $pending['mode'] === 'upgrade') {
			$ok = $this->usermodel->applyPlanUpgrade(
				(int) $pending['user_id'],
				(int) $pending['plan_id'],
				(float) $pending['amount'],
				$pending['currency'],
				$helcim_txn_id,
				$approval_code
			);
			if (!$ok) {
				return $this->output->set_output(json_encode(array('success' => false, 'error' => 'Payment verified but the upgrade could not be applied. Please contact support with transaction id ' . $helcim_txn_id . '.')));
			}
			$plan = $this->usermodel->getPlanByIdForPricing((int) $pending['plan_id']);
			$this->session->unset_userdata('helcim_pending');
			$this->session->set_userdata('checkout_success_data', array(
				'plan_name'      => $plan ? $plan->name : 'Subscription',
				'amount'         => (float) $pending['amount'],
				'currency'       => $pending['currency'],
				'transaction_id' => $helcim_txn_id,
				'subscription_id' => null,
			));
			return $this->output->set_output(json_encode(array('success' => true, 'redirect' => base_url('checkout-success'))));
		}

		$sub_id = $this->usermodel->activateHelcimSubscription(array(
			'user_id'               => (int) $pending['user_id'],
			'plan_id'               => (int) $pending['plan_id'],
			'duration_id'           => $pending['duration_id'] ? (int) $pending['duration_id'] : null,
			'amount'                => (float) $pending['amount'],
			'currency'              => $pending['currency'],
			'auto_renew'            => !empty($pending['auto_renew']) ? 1 : 0,
			'helcim_transaction_id' => $helcim_txn_id,
			'helcim_approval_code'  => $approval_code,
			'helcim_card_token'     => $card_token,
		));

		if (!$sub_id) {
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'Payment verified but activation failed. Please contact support with your transaction id ' . $helcim_txn_id . '.')));
		}

		// One-time consume: clear the pending session so a replay can't
		// double-activate, and hand the receipt data to checkout_success().
		$plan = $this->usermodel->getPlanByIdForPricing((int) $pending['plan_id']);
		$this->session->unset_userdata('helcim_pending');
		$this->session->set_userdata('checkout_success_data', array(
			'plan_name'      => $plan ? $plan->name : 'Subscription',
			'amount'         => (float) $pending['amount'],
			'currency'       => $pending['currency'],
			'transaction_id' => $helcim_txn_id,
			'subscription_id' => $sub_id,
		));

		return $this->output->set_output(json_encode(array(
			'success'  => true,
			'redirect' => base_url('checkout-success'),
		)));
	}

	/**
	 * Helcim webhook receiver. Verifies the signature and logs the event so
	 * card/recurring notifications can be reconciled. Public (no login) but
	 * signature-gated. URL: /helcim-webhook (POST)
	 */
	public function helcim_webhook()
	{
		$this->output->set_content_type('application/json');
		$raw       = $this->input->raw_input_stream;
		$signature = isset($_SERVER['HTTP_WEBHOOK_SIGNATURE']) ? $_SERVER['HTTP_WEBHOOK_SIGNATURE'] : '';

		$this->load->library('helcim_lib');
		if (!$this->helcim_lib->verify_webhook($raw, $signature)) {
			$this->output->set_status_header(401);
			return $this->output->set_output(json_encode(array('ok' => false)));
		}

		// Signature valid — record it. Reconciliation of specific event types
		// (recurring success/failure) is handled by the renewal job.
		log_message('info', 'Helcim webhook received: ' . $raw);
		return $this->output->set_output(json_encode(array('ok' => true)));
	}

	/**
	 * AJAX — start a plan change from the account page. Upgrades (target price
	 * > current) initialize a HelcimPay.js session for the PRORATED difference
	 * and return a checkoutToken (mode=upgrade, finalized by process_checkout).
	 * Downgrades / same price are scheduled for the next renewal.
	 * URL: /plan-change-init (POST)
	 */
	public function plan_change_init()
	{
		$this->output->set_content_type('application/json');
		if ($this->input->method() !== 'post' || !$this->session->userdata('logged_user')) {
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'Not allowed.')));
		}
		$user    = $this->session->userdata('logged_user');
		$user_id = (int) $user['logged_id'];
		$target_plan_id = (int) $this->input->post('plan_id');

		$current = $this->usermodel->getUserSubscription($user_id);
		if (!$current) {
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'No active subscription. Please choose a plan first.')));
		}
		$target = $this->usermodel->getPlanByIdForPricing($target_plan_id);
		if (!$target) {
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'Invalid plan.')));
		}
		if ((int) $target->id === (int) $current->plan_id) {
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'You are already on this plan.')));
		}

		$current_price = (float) $current->total_amount > 0 ? (float) $current->total_amount : (float) $current->price;
		$target_price  = (float) $target->price;

		// Downgrade / same price -> schedule at renewal.
		if ($target_price <= $current_price) {
			$this->usermodel->scheduleDowngrade($user_id, $target_plan_id);
			return $this->output->set_output(json_encode(array(
				'success' => true, 'mode' => 'downgrade',
				'message' => 'Your plan will change to ' . $target->name . ' at your next renewal.',
			)));
		}

		// Upgrade -> charge the prorated difference for the remaining term.
		$term_days = (int) $current->duration_days ?: 30;
		$remaining = max(0, (int) ceil((strtotime($current->end_date) - time()) / 86400));
		$remaining = min($remaining, $term_days);
		$prorated  = round(($target_price - $current_price) * ($remaining / max(1, $term_days)), 2);

		if ($prorated < 0.50) {
			// Negligible difference — switch now, no charge.
			$this->usermodel->applyPlanUpgrade($user_id, $target_plan_id, 0, ($current->currency ?: 'USD'), null, null);
			return $this->output->set_output(json_encode(array('success' => true, 'mode' => 'switched', 'message' => 'You are now on ' . $target->name . '.')));
		}

		$this->load->library('helcim_lib');
		$init = $this->helcim_lib->initialize_checkout($prorated, null, array(
			'description' => 'Pearland411 upgrade to ' . $target->name,
		));
		if (!$init['success']) {
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'Could not start payment. Please try again.')));
		}
		$this->session->set_userdata('helcim_pending', array(
			'checkoutToken' => $init['checkoutToken'],
			'secretToken'   => $init['secretToken'],
			'plan_id'       => $target_plan_id,
			'duration_id'   => null,
			'amount'        => $prorated,
			'currency'      => $this->helcim_lib->currency(),
			'user_id'       => $user_id,
			'auto_renew'    => 0,
			'mode'          => 'upgrade',
		));
		return $this->output->set_output(json_encode(array(
			'success' => true, 'mode' => 'upgrade',
			'checkoutToken' => $init['checkoutToken'], 'amount' => $prorated,
		)));
	}

	/** Cancel auto-renew; the plan runs to end_date. URL: /cancel-subscription (POST) */
	public function cancel_subscription()
	{
		if ($this->input->method() !== 'post' || !$this->session->userdata('logged_user')) {
			redirect('user/my_account');
			return;
		}
		$user = $this->session->userdata('logged_user');
		if ($this->usermodel->cancelAutoRenew((int) $user['logged_id'])) {
			$this->session->set_flashdata('message', 'Auto-renew cancelled. Your plan stays active until the end of the current term.');
		} else {
			$this->session->set_flashdata('error', 'No active subscription to cancel.');
		}
		redirect('user/my_account');
	}

	/* ===================================================================
	 * Advertising cart (session-backed). Guests can build a cart without
	 * logging in; login/register happens at checkout. Business-listing is
	 * single-select (radio); every other category is multi-select.
	 * =================================================================== */

	/** Build the cart summary from the session's plan-id list. */
	/**
	 * Normalize the session cart into term-aware lines.
	 *
	 * Carts used to be a flat array of plan ids; they now carry the term the
	 * customer picked. Both shapes are accepted so a cart built before this
	 * change (or by an old tab) still works.
	 *
	 * @return array of ['plan_id', 'duration_id'|null, 'multiplier']
	 */
	private function cartLines()
	{
		$raw = $this->session->userdata('ad_cart');
		$raw = is_array($raw) ? $raw : array();
		$lines = array();
		foreach ($raw as $entry) {
			if (is_array($entry)) {
				$pid = isset($entry['plan_id']) ? (int) $entry['plan_id'] : 0;
				if (!$pid) continue;
				$lines[] = array(
					'plan_id'     => $pid,
					'duration_id' => !empty($entry['duration_id']) ? (int) $entry['duration_id'] : null,
					'multiplier'  => max(1, isset($entry['multiplier']) ? (int) $entry['multiplier'] : 1),
					// Category-specific banners/videos: which category it is for.
					'category_id' => !empty($entry['category_id']) ? (int) $entry['category_id'] : null,
				);
			} else {
				$pid = (int) $entry;
				if ($pid) {
					$lines[] = array('plan_id' => $pid, 'duration_id' => null, 'multiplier' => 1, 'category_id' => null);
				}
			}
		}
		return $lines;
	}

	private function saveCartLines($lines)
	{
		$this->session->set_userdata('ad_cart', array_values($lines));
	}

	/** "12 months", "2 years", "7 days" — how a term reads to a customer. */
	private function termLabel($days, $months = 0)
	{
		$days = (int) $days;
		$months = (int) $months;
		// Only call it "months" when the term really is a whole number of them.
		// Rounding turned a 42-day placement into "1 month", which misstates
		// what the customer is buying.
		if (!$months && $days >= 30 && $days % 30 === 0) {
			$months = (int) ($days / 30);
		}
		if ($months >= 12 && $months % 12 === 0) {
			$years = $months / 12;
			return $years . ' year' . ($years > 1 ? 's' : '');
		}
		if ($months >= 1) {
			return $months . ' month' . ($months > 1 ? 's' : '');
		}
		return $days . ' day' . ($days === 1 ? '' : 's');
	}

	/**
	 * The terms a customer may buy a plan for.
	 *
	 * Prefers the explicit `plan_durations` rows an admin configured (which may
	 * carry a long-term discount). When a plan has none, offer standard
	 * multiples of its base period so longer terms are still purchasable.
	 */
	private function planTermOptions($plan)
	{
		$base_days = (int) $plan->duration_days > 0 ? (int) $plan->duration_days : 30;
		$by_days = array();

		// Terms an admin configured explicitly. These win, because they may
		// carry a long-term discount rather than a plain multiple.
		$rows = method_exists($this->usermodel, 'getPlanDurations')
			? $this->usermodel->getPlanDurations((int) $plan->id)
			: array();
		foreach ($rows as $d) {
			$months = (int) $d->duration_months;
			$days   = (int) $d->duration_days ?: ($months * 30);
			if ($days <= 0) continue;
			$by_days[$days] = array(
				'duration_id' => (int) $d->id,
				'multiplier'  => null,
				'days'        => $days,
				'months'      => $months,
				'price'       => (float) $d->price,
				'label'       => $this->termLabel($days, $months),
			);
		}

		// Then fill in the standard multiples. Every plan is seeded with a
		// single 1-month row, so relying on configured rows alone left exactly
		// one option and no way to buy a longer term at all.
		foreach (array(1, 3, 6, 12, 24) as $m) {
			$days = $base_days * $m;
			if (isset($by_days[$days])) continue;
			$months = $base_days >= 28 ? (int) round($days / 30) : 0;
			$by_days[$days] = array(
				'duration_id' => null,
				'multiplier'  => $m,
				'days'        => $days,
				'months'      => $months,
				'price'       => round((float) $plan->price * $m, 2),
				'label'       => $this->termLabel($days, $months),
			);
		}

		ksort($by_days);
		return array_values($by_days);
	}

	/** Pick the option a line refers to, falling back to the shortest term. */
	private function resolveTerm($plan, $line)
	{
		$options = $this->planTermOptions($plan);
		if (empty($options)) return null;

		foreach ($options as $o) {
			if (!empty($line['duration_id']) && $o['duration_id'] === (int) $line['duration_id']) {
				return $o;
			}
		}
		if (empty($line['duration_id'])) {
			foreach ($options as $o) {
				if ($o['multiplier'] !== null && $o['multiplier'] === (int) $line['multiplier']) {
					return $o;
				}
			}
		}
		return $options[0];
	}

	private function cartSummary()
	{
		$lines = $this->cartLines();
		$items = array();
		$total = 0.0;
		$currency = 'USD';

		foreach ($lines as $idx => $line) {
			$p = $this->usermodel->getPlanByIdForPricing((int) $line['plan_id']);
			if (!$p) continue;
			$currency = $p->currency ?: 'USD';

			$term = $this->resolveTerm($p, $line);
			$days  = $term ? (int) $term['days'] : ((int) $p->duration_days ?: 30);
			$price = $term ? (float) $term['price'] : (float) $p->price;

			// A category-specific placement has to say which category.
			$needs_cat = $this->planNeedsTargetCategory($p);
			$cat_id    = $needs_cat && !empty($line['category_id']) ? (int) $line['category_id'] : null;
			$cat_name  = '';
			if ($cat_id) {
				$cat_row  = $this->usermodel->getSingleValue($cat_id, 'category_id', 'listing_categories');
				$cat_name = $cat_row ? (string) $cat_row->category_name : '';
				if (!$cat_row) $cat_id = null;
			}

			$items[] = array(
				'line'        => $idx,
				'plan_id'     => (int) $p->id,
				'name'        => $p->name,
				'price'       => $price,
				'base_price'  => (float) $p->price,
				'category'    => isset($p->category) ? $p->category : '',
				'currency'    => $currency,
				'duration_id' => $term ? $term['duration_id'] : null,
				'multiplier'  => $term ? $term['multiplier'] : 1,
				'term_days'   => $days,
				'term_label'  => $term ? $term['label'] : $this->termLabel($days),
				'expires_at'  => date('Y-m-d', strtotime('+' . $days . ' days')),
				'terms'       => $this->planTermOptions($p),
				'needs_category'       => $needs_cat,
				'target_category_id'   => $cat_id,
				'target_category_name' => $cat_name,
				// Only a business-listing tier becomes a subscription
				// (Usermodel::recordCartPurchase); everything else — the
				// special package included — is filed as a one-off purchase
				// with an end date. Flagging `special` here offered "Renew
				// automatically" for an item that could never renew.
				'recurring'   => (isset($p->category) ? $p->category : '') === 'business-listing',
			);
			$total += $price;
		}

		return array(
			'items'     => $items,
			'total'     => round($total, 2),
			'count'     => count($items),
			'currency'  => $currency,
			'recurring' => (bool) array_filter($items, function ($i) { return $i['recurring']; }),
		);
	}

	/** GET/POST — return the current cart as JSON. URL: /cart-get */
	public function cart_get()
	{
		$this->output->set_content_type('application/json');
		return $this->output->set_output(json_encode(array('success' => true, 'cart' => $this->cartSummary())));
	}

	/** POST — add a plan to the cart. URL: /cart-add */
	public function cart_add()
	{
		$this->output->set_content_type('application/json');
		if ($this->input->method() !== 'post') {
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'Not allowed.')));
		}
		$plan_id = (int) $this->input->post('plan_id');
		$plan = $this->usermodel->getPlanByIdForPricing($plan_id);
		if (!$plan) {
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'Invalid item.')));
		}
		if ((float) $plan->price <= 0) {
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'This is a free option — nothing to buy.')));
		}
		$lines = $this->cartLines();
		$cat = isset($plan->category) ? $plan->category : '';

		// Business-listing tier is single-select — drop any other tier first.
		if ($cat === 'business-listing') {
			$lines = array_filter($lines, function ($l) {
				$p = $this->usermodel->getPlanByIdForPricing((int) $l['plan_id']);
				return $p && (isset($p->category) ? $p->category : '') !== 'business-listing';
			});
		}

		$already = false;
		foreach ($lines as $l) {
			if ((int) $l['plan_id'] === $plan_id) { $already = true; break; }
		}
		if (!$already) {
			$lines[] = array(
				'plan_id'     => $plan_id,
				'duration_id' => (int) $this->input->post('duration_id') ?: null,
				'multiplier'  => max(1, (int) $this->input->post('multiplier') ?: 1),
			);
		}
		$this->saveCartLines($lines);
		return $this->output->set_output(json_encode(array('success' => true, 'cart' => $this->cartSummary())));
	}

	/** POST — remove a plan from the cart. URL: /cart-remove */
	public function cart_remove()
	{
		$this->output->set_content_type('application/json');
		if ($this->input->method() !== 'post') {
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'Not allowed.')));
		}
		$plan_id = (int) $this->input->post('plan_id');
		$lines = array_filter($this->cartLines(), function ($l) use ($plan_id) {
			return (int) $l['plan_id'] !== $plan_id;
		});
		$this->saveCartLines($lines);
		return $this->output->set_output(json_encode(array('success' => true, 'cart' => $this->cartSummary())));
	}

	/**
	 * AJAX — change the term (1 month / 12 months / 2 years ...) of one cart
	 * line. URL: /cart-set-term (POST plan_id + duration_id|multiplier)
	 */
	public function cart_set_term()
	{
		$this->output->set_content_type('application/json');
		if ($this->input->method() !== 'post') {
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'Not allowed.')));
		}
		$plan_id     = (int) $this->input->post('plan_id');
		$duration_id = (int) $this->input->post('duration_id');
		$multiplier  = (int) $this->input->post('multiplier');

		$plan = $this->usermodel->getPlanByIdForPricing($plan_id);
		if (!$plan) {
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'Invalid item.')));
		}

		// Only accept a term this plan actually offers — never trust the post.
		$valid = false;
		foreach ($this->planTermOptions($plan) as $o) {
			if ($duration_id && $o['duration_id'] === $duration_id) { $valid = true; break; }
			if (!$duration_id && $o['multiplier'] !== null && $o['multiplier'] === $multiplier) { $valid = true; break; }
		}
		if (!$valid) {
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'That term is not available for this package.')));
		}

		$lines = $this->cartLines();
		foreach ($lines as $i => $l) {
			if ((int) $l['plan_id'] === $plan_id) {
				$lines[$i]['duration_id'] = $duration_id ?: null;
				$lines[$i]['multiplier']  = max(1, $multiplier ?: 1);
			}
		}
		$this->saveCartLines($lines);
		return $this->output->set_output(json_encode(array('success' => true, 'cart' => $this->cartSummary())));
	}

	/** Is this a category-specific placement (sold per listing category)? */
	private function planNeedsTargetCategory($plan)
	{
		$cat = isset($plan->category) ? (string) $plan->category : '';
		return in_array($cat, array('category-banners', 'category-videos'), true);
	}

	/**
	 * POST — choose which listing category a category-specific banner or
	 * video is for. URL: /cart-set-category
	 *
	 * The cart had nowhere to record it, so a "Category Specific Banner" was
	 * paid for without anyone knowing which category it should run in.
	 */
	public function cart_set_category()
	{
		$this->output->set_content_type('application/json');
		if ($this->input->method() !== 'post') {
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'Not allowed.')));
		}
		$plan_id     = (int) $this->input->post('plan_id');
		$category_id = (int) $this->input->post('category_id');

		$plan = $this->usermodel->getPlanByIdForPricing($plan_id);
		if (!$plan || !$this->planNeedsTargetCategory($plan)) {
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'Invalid item.')));
		}
		if (!$category_id || !$this->usermodel->getSingleValue($category_id, 'category_id', 'listing_categories')) {
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'Choose a category from the list.')));
		}

		$lines = $this->cartLines();
		foreach ($lines as $i => $l) {
			if ((int) $l['plan_id'] === $plan_id) {
				$lines[$i]['category_id'] = $category_id;
			}
		}
		$this->saveCartLines($lines);
		return $this->output->set_output(json_encode(array('success' => true, 'cart' => $this->cartSummary())));
	}

	/** POST — empty the cart. URL: /cart-clear */
	public function cart_clear()
	{
		$this->output->set_content_type('application/json');
		$this->session->unset_userdata('ad_cart');
		return $this->output->set_output(json_encode(array('success' => true, 'cart' => $this->cartSummary())));
	}

	/**
	 * GET/POST — "Buy Now": replace the cart with this single item and go
	 * straight to checkout. URL: /buy-now?plan_id=X
	 */
	public function buy_now()
	{
		$plan_id = (int) ($this->input->post('plan_id') ?: $this->input->get('plan_id'));
		$plan = $this->usermodel->getPlanByIdForPricing($plan_id);
		if (!$plan || (float) $plan->price <= 0) {
			$this->session->set_flashdata('error', 'Please choose a valid package.');
			redirect('pricing-plan');
			return;
		}
		$this->saveCartLines(array(array(
			'plan_id'     => $plan_id,
			'duration_id' => (int) $this->input->get('duration_id') ?: null,
			'multiplier'  => max(1, (int) $this->input->get('multiplier') ?: 1),
		)));
		redirect('checkout');
	}

	/**
	 * AJAX (login required) — start a HelcimPay.js session for the whole cart
	 * total and stash the cart snapshot for process_checkout. URL: /cart-pay-init
	 */
	public function cart_pay_init()
	{
		$this->output->set_content_type('application/json');
		if ($this->input->method() !== 'post' || !$this->session->userdata('logged_user')) {
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'Please sign in to pay.')));
		}
		$cart = $this->cartSummary();
		if (empty($cart['items']) || $cart['total'] <= 0) {
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'Your cart is empty.')));
		}
		foreach ($cart['items'] as $it) {
			if (!empty($it['needs_category']) && empty($it['target_category_id'])) {
				return $this->output->set_output(json_encode(array('success' => false,
					'error' => 'Choose which category "' . $it['name'] . '" should run in, then pay.')));
			}
		}
		$this->load->library('helcim_lib');
		$init = $this->helcim_lib->initialize_checkout($cart['total'], null, array(
			'description' => 'Pearland411 order — ' . $cart['count'] . ' item(s)',
		));
		if (!$init['success']) {
			return $this->output->set_output(json_encode(array('success' => false, 'error' => 'Could not start payment. Please try again.')));
		}
		$user = $this->session->userdata('logged_user');
		$this->session->set_userdata('helcim_pending', array(
			'checkoutToken' => $init['checkoutToken'],
			'secretToken'   => $init['secretToken'],
			'user_id'       => (int) $user['logged_id'],
			'amount'        => $cart['total'],
			'currency'      => $this->helcim_lib->currency(),
			'mode'          => 'cart',
			'cart_items'    => $cart['items'],
			// Opt-in from the checkout checkbox; only meaningful for the
			// recurring listing tiers, which vault the card on activation.
			'auto_renew'    => (int) $this->input->post('auto_renew') ? 1 : 0,
		));
		return $this->output->set_output(json_encode(array('success' => true, 'checkoutToken' => $init['checkoutToken'])));
	}

	/**
	 * Checkout Success Page - Display success message with order details
	 * URL: /checkout-success
	 */
	public function checkout_success()
	{
		// Get success data from session
		$success_data = $this->session->userdata('checkout_success_data');

		if (!$success_data) {
			// No success data, redirect to pricing plans
			redirect('user/pricing_plan');
			return;
		}

		// Clear success data from session
		$this->session->unset_userdata('checkout_success_data');

		// Pass data to view
		$data['success_data'] = $success_data;

		// Load header categories
		$this->loadHeaderCategories($data);

		// Page title
		$data['title'] = 'Payment Successful - Thank You!';

		// Load views
		$this->load->view('header', $data);
		$this->load->view('checkout_success_view', $data);
		$this->load->view('footer');
	}

	/**
	 * Newsletter subscription endpoint (AJAX)
	 * URL: /user/subscribe_newsletter (POST)
	 */
	public function subscribe_newsletter()
	{
		header('Content-Type: application/json; charset=utf-8');

		// 10 newsletter signups per IP per hour. The signup sends an
		// email; rate-limiting matters more for email cost than for
		// DB rows.
		if (!$this->rate_limit->check_ip('subscribe_newsletter', 10, 3600)) {
			http_response_code(429);
			echo json_encode(['success' => false, 'message' => 'Too many subscribe requests. Try again in a few minutes.']);
			return;
		}

		// Basic email validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');

		if ($this->form_validation->run() == FALSE) {
			echo json_encode(['success' => false, 'message' => strip_tags(validation_errors())]);
			return;
		}

		$email = $this->input->post('email', TRUE);
		$name = $this->input->post('name', TRUE);

		// Prevent duplicate subscriptions
		if ($this->usermodel->is_newsletter_email_subscribed($email)) {
			echo json_encode(['success' => false, 'message' => 'This email is already subscribed to our newsletter.']);
			return;
		}

		// Add subscription record
		$result = $this->usermodel->add_newsletter_subscription(['email' => $email, 'name' => $name]);

		if (!empty($result['success'])) {
			// Send welcome email using PHP mail()
			$site_name = 'Pearland411';
			$confirm_url = base_url('user/newsletter_confirm/' . $result['token']);
			$subject = "Welcome to {$site_name} Newsletter!";
			$message = "<html><body>"
				. "<h2>Welcome to {$site_name}!</h2>"
				. "<p>Thank you for subscribing. Please confirm your email by clicking the link below:</p>"
				. "<p><a href='" . $confirm_url . "'>Confirm Subscription</a></p>"
				. "<p>If you cannot click the link, copy and paste this URL into your browser:</p>"
				. "<p>" . $confirm_url . "</p>"
				. "</body></html>";

			$headers = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
			$headers .= "From: {$site_name} <no-reply@pearland411.com>" . "\r\n";

			@mail($email, $subject, $message, $headers);

			echo json_encode(['success' => true, 'message' => 'Thank you for subscribing! Please check your email to confirm your subscription.']);
		} else {
			echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again later.']);
		}
	}

	/**
	 * Confirm newsletter subscription via token
	 * URL: /user/newsletter_confirm/{token}
	 */
	public function newsletter_confirm($token = '')
	{
		if (empty($token)) {
			echo '<!doctype html><html><body><p>Invalid confirmation link.</p></body></html>';
			return;
		}

		if ($this->usermodel->confirm_newsletter_subscription($token)) {
			echo '<!doctype html><html><body><p>Your subscription has been confirmed successfully!</p></body></html>';
		} else {
			echo '<!doctype html><html><body><p>Invalid or expired confirmation link.</p></body></html>';
		}
	}

	/*=========================
		IT / Web services catalog (public side)
		See database/migrations/0002_services.sql.
		==========================*/

	public function services()
	{
		$data = array();
		$category = $this->input->get('category');
		$category = $category ? trim($category) : null;

		$data['services']   = $this->usermodel->getActiveServices($category);
		$data['categories'] = $this->usermodel->getServiceCategories();
		$data['selected_category'] = $category;
		$data['title']      = 'IT & Web Services for Local Businesses';
		$data['page_css']   = $this->contentPageCss();
		$this->loadHeaderCategories($data);

		// Advertising. Three wide zones; this page is full-width, with no column to hang a box off.
		$data['ad_slots'] = $this->pageAdSlots('wide');

		$this->load->view('header', $data);
		$this->load->view('services_index', $data);
		$this->load->view('footer');
	}

	public function service_details($slug = '')
	{
		$service = $this->usermodel->getServiceBySlug($slug);
		if (!$service) {
			show_404();
			return;
		}

		$data = array();
		$data['service'] = $service;
		$data['title']   = $service->name . ' — Pearland411 Services';
		$data['meta_description'] = (string) $service->short_description;
		$data['canonical_url']    = base_url('services/' . rawurlencode($service->slug));
		if (!empty($service->image)) $data['og_image'] = base_url($service->image);

		$ld = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'Service',
			'name'        => (string) $service->name,
			'description' => (string) $service->short_description,
			'url'         => $data['canonical_url'],
			'provider'    => array(
				'@type' => 'Organization',
				'name'  => 'Pearland411',
				'url'   => base_url(),
			),
		);
		if (!empty($service->category)) $ld['serviceType'] = (string) $service->category;
		if ($service->base_price !== null && $service->price_unit !== 'custom') {
			$ld['offers'] = array(
				'@type'         => 'Offer',
				'price'         => number_format((float) $service->base_price, 2, '.', ''),
				'priceCurrency' => 'USD',
			);
		}
		$data['json_ld']  = json_ld($ld);
		$data['page_css'] = $this->contentPageCss();

		$this->loadHeaderCategories($data);

		// Pre-fill the request form with session data when available.
		$data['prefill'] = array('name' => '', 'email' => '', 'phone' => '', 'company' => '');
		if ($this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$data['prefill']['name']  = isset($session['name']) ? $session['name'] : '';
			$data['prefill']['email'] = isset($session['email_id']) ? $session['email_id'] : '';
		}

		// Advertising. Six zones — three wide, three down the sidebar.
		$data['ad_slots'] = $this->pageAdSlots('full');

		$this->load->view('header', $data);
		$this->load->view('service_details', $data);
		$this->load->view('footer');
	}

	/**
	 * GET /request-quote — the custom-quote form.
	 *
	 * /services offered "Request a custom quote" in three places, all pointing
	 * at /request-service. That is the POST handler below, and it bounces any
	 * GET straight back to /services — so the button looked broken: click it,
	 * land back where you started, no form. This is the page it should have
	 * been going to. It posts to the same handler, with no service preselected.
	 *
	 * ?service=<slug> preselects one, so a link from a service page can carry
	 * its context in.
	 */
	public function request_quote()
	{
		$data = array();

		// ?service=<slug> just preselects the dropdown; the value is checked
		// against the catalog on submit, so it does not need looking up here.
		$slug = trim((string) $this->input->get('service'));
		$data['services'] = $this->usermodel->getActiveServices();

		// Put back what they typed when validation sent them here — retyping a
		// project description because the email address had a typo is the
		// fastest way to lose the enquiry.
		$old = $this->session->flashdata('service_request_old');
		$data['prefill'] = array(
			'name'         => isset($old['name'])         ? $old['name']         : '',
			'email'        => isset($old['email'])        ? $old['email']        : '',
			'phone'        => isset($old['phone'])        ? $old['phone']        : '',
			'company'      => isset($old['company'])      ? $old['company']      : '',
			'budget_range' => isset($old['budget_range']) ? $old['budget_range'] : '',
			'message'      => isset($old['message'])      ? $old['message']      : '',
			'service_slug' => isset($old['service_slug']) ? $old['service_slug'] : $slug,
		);

		// A signed-in member should not have to type what we already know.
		if ($data['prefill']['name'] === '' && $this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$data['prefill']['name']  = isset($session['name']) ? $session['name'] : '';
			$data['prefill']['email'] = isset($session['email_id']) ? $session['email_id'] : '';
		}

		$data['title']            = 'Request a custom quote — Pearland411 Services';
		$data['meta_description'] = 'Tell us what you need built, hosted or supported and we will come back with a fixed price.';
		$data['canonical_url']    = base_url('request-quote');
		$data['page_css']         = $this->contentPageCss();
		$this->loadHeaderCategories($data);

		// Advertising. Six zones — three wide, three down the sidebar.
		$data['ad_slots'] = $this->pageAdSlots('full');

		$this->load->view('header', $data);
		$this->load->view('request_quote', $data);
		$this->load->view('footer');
	}

	/**
	 * Handle quote-request submission. Slug is optional — a generic
	 * "request quote" form (no service preselected) can post here
	 * with service_slug empty.
	 */
	public function request_service($slug = '')
	{
		if ($this->input->method() !== 'post') {
			// The standalone form lives at /request-quote; a GET here is
			// someone following an old link or refreshing.
			redirect($slug ? 'services/' . rawurlencode($slug) : 'request-quote');
			return;
		}

		// The service can also arrive in the body, from the standalone form's
		// dropdown rather than the URL.
		if ($slug === '') {
			$slug = trim((string) $this->input->post('service_slug'));
		}

		// Where to send them afterwards. Whitelisted — never redirect to a
		// posted URL. The standalone form asks to come back to itself so the
		// error, and what they typed, are next to the fields.
		$posted_back = (string) $this->input->post('return_to');
		$back = ($posted_back === 'request-quote')
			? 'request-quote'
			: ($slug ? 'services/' . rawurlencode($slug) : 'services');

		// 5 service requests per IP per hour. Each one sends two emails.
		if (!$this->rate_limit->check_ip('request_service', 5, 3600)) {
			$this->rate_limit->reject(3600);
			return;
		}

		$this->form_validation->set_rules('name',  'Name',  'trim|required|min_length[2]');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		$this->form_validation->set_rules('message', 'Message', 'trim|required|min_length[5]');

		$service = $slug ? $this->usermodel->getServiceBySlug($slug) : null;

		if (!$this->form_validation->run()) {
			$this->session->set_flashdata('service_request_error', strip_tags(validation_errors()) ?: 'Please complete the required fields.');
			$this->session->set_flashdata('service_request_old', array(
				'name'         => (string) $this->input->post('name'),
				'email'        => (string) $this->input->post('email'),
				'phone'        => (string) $this->input->post('phone'),
				'company'      => (string) $this->input->post('company'),
				'budget_range' => (string) $this->input->post('budget_range'),
				'message'      => (string) $this->input->post('message'),
				'service_slug' => $slug,
			));
			redirect($back);
			return;
		}

		$user_id = null;
		if ($this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$user_id = isset($session['logged_id']) ? (int) $session['logged_id'] : null;
		}

		$payload = array(
			'service_id'   => $service ? (int) $service->service_id : null,
			'user_id'      => $user_id,
			'name'         => $this->input->post('name'),
			'email'        => $this->input->post('email'),
			'phone'        => $this->input->post('phone'),
			'company'      => $this->input->post('company'),
			'budget_range' => $this->input->post('budget_range'),
			'message'      => $this->input->post('message'),
			'status'       => 'new',
			'ip'           => $this->input->ip_address(),
			'user_agent'   => substr((string) $this->input->user_agent(), 0, 255),
		);

		$request_id = $this->usermodel->createServiceRequest($payload);

		if (!$request_id) {
			$this->session->set_flashdata('service_request_error', 'Could not save your request. Please try again or call us.');
			redirect($back);
			return;
		}

		// Best-effort notifications:
		//   (a) acknowledge to the submitter
		//   (b) alert MAIL_FROM_ADDRESS that a new request landed.
		$this->load->library('mailer');
		$svc_name = $service ? $service->name : 'general inquiry';
		$ack_body = '<p>Thanks for your message — we received your request about <strong>' . html_escape($svc_name) . '</strong> and will be in touch shortly.</p>';
		$this->mailer->send($this->input->post('email'), 'We received your Pearland411 services request', $ack_body);

		// Quote requests go to whoever sells the services — set at
		// /admin/setting, defaulting to the address we were given. Falls back
		// to the site's own From address only if that were ever blanked out,
		// so a request can't end up going nowhere.
		// form_recipient_email() already walks the whole chain — the per-form
		// override, then the site-wide "all forms to" address, then
		// contact_email, then MAIL_FROM_ADDRESS — so the second lookup this
		// replaced could only ever return the same thing.
		$admin_to = form_recipient_email(site_setting('service_quote_email', 'joshua@spacecentersystems.com'));
		if ($admin_to) {
			$alert_body = '<p>New service request #' . (int) $request_id . '</p>'
				. '<ul>'
				. '<li>Service: ' . html_escape($svc_name) . '</li>'
				. '<li>Name: '    . html_escape($this->input->post('name')) . '</li>'
				. '<li>Email: '   . html_escape($this->input->post('email')) . '</li>'
				. '<li>Phone: '   . html_escape($this->input->post('phone')) . '</li>'
				. '<li>Company: ' . html_escape($this->input->post('company')) . '</li>'
				. '<li>Budget: '  . html_escape($this->input->post('budget_range')) . '</li>'
				. '</ul>'
				. '<p>Message:</p><blockquote>' . nl2br(html_escape($this->input->post('message'))) . '</blockquote>';
			// Reply hits the person who asked, not the site's no-reply box.
			$this->mailer->send(
				$admin_to,
				'[Pearland411] New service request — ' . $svc_name,
				$alert_body,
				null,
				array('reply_to' => $this->input->post('email'), 'reply_to_name' => $this->input->post('name'))
			);
		}

		$this->session->set_flashdata('service_request_success', 'Thanks! We received your request and will be in touch shortly.');
		redirect($back);
	}

	/*=========================
		Public coupons / deals
		==========================*/

	public function coupons()
	{
		$data = array();
		// getDistinctActiveCoupons(), not getActiveCoupons(): the join runs
		// through the OWNER's listings, so a business with four listings put
		// the same offer on this page four times. The homepage has always
		// called the de-duplicating one; this page never did.
		$data['coupons']  = $this->usermodel->getDistinctActiveCoupons(60);
		$data['ad_house_cap'] = $this->thinPageAdCap(count($data['coupons']));
		$data['ad_slots'] = $this->contentAdSlots();
		$data['page_css'] = $this->contentPageCss();
		$this->loadHeaderCategories($data);
		$data['title']         = 'Local Coupons &amp; Deals — Pearland411';
		$data['meta_description'] = 'Current coupons and deals from Pearland-area businesses listed on Pearland411.';
		$data['canonical_url'] = base_url('coupons');

		$this->load->view('header', $data);
		$this->load->view('coupons', $data);
		$this->load->view('footer');
	}

	public function coupon_details($id = 0)
	{
		$coupon = $this->usermodel->getCouponById($id);
		if (!$coupon) { show_404(); return; }

		// Date-window check so a freshly-deactivated coupon doesn't keep
		// returning success via a stale share link.
		$today = date('Y-m-d');
		if ((int) $coupon->coupon_status !== 1
			|| $coupon->coupon_start_date > $today
			|| $coupon->coupon_end_date < $today) {
			show_404();
			return;
		}

		$data = array();
		$data['coupon']   = $coupon;
		$data['page_css'] = $this->contentPageCss();
		$this->loadHeaderCategories($data);
		$data['title']            = $coupon->coupon_name . ' — Coupon — Pearland411';
		// The linked business, or the site — never `owner_name`, which is the
		// staff account that keyed the coupon in. It was reaching the public
		// page as "Posted by Mydynamic VA", and search engines were being told
		// the same thing here.
		$data['meta_description'] = 'Coupon code ' . $coupon->coupon_code . ' for '
			. (!empty($coupon->listing_name) ? $coupon->listing_name : 'a Pearland business')
			. ' on Pearland411.';
		$data['canonical_url']    = base_url('coupons/' . (int) $coupon->coupon_id);

		// Advertising. Six zones — three wide, three down the sidebar.
		$data['ad_slots'] = $this->pageAdSlots('full');

		$this->load->view('header', $data);
		$this->load->view('coupon_details', $data);
		$this->load->view('footer');
	}

	/*=========================
		Member coupons (the other half of /coupons)

		"Post a deal" used to link to base_url('user/coupons'), and
		/user/coupons resolves to User::coupons — the public page the button
		was on. Every one of the three buttons was a link back to the page you
		were already reading.

		It was not a typo in a href. The theme shipped views for a member
		coupon screen (user_coupons.php, add_coupons.php, edit_coupons.php),
		all three posting to user/add-coupon, user/edit-coupon and
		user/delete-coupon — and none of those methods was ever written, on
		this controller or on Admin. Nothing in the application could create a
		coupon. These four methods are the missing half.

		/user/my-coupons rather than /user/coupons, because that URL is taken
		by the public page and quietly shadowing it is how this started.
		==========================*/

	/**
	 * A member's own coupons: what is live, what has expired, and the way in
	 * to posting another.
	 */
	public function my_coupons()
	{
		$session   = (array) $this->session->userdata('logged_user');
		$logged_id = isset($session['logged_id']) ? (int) $session['logged_id'] : 0;

		$data = array();
		$data['logged_id'] = $logged_id;
		$data['coupons']   = $this->usermodel->getCouponsByUser($logged_id);
		$data['listings']  = $this->usermodel->getListingById($logged_id, NULL, NULL);
		$data['live_count'] = $this->usermodel->countCouponsByUser($logged_id, TRUE);
		$data['coupon_cap'] = $this->couponCap();
		$data['title']     = 'My deals & coupons';

		$this->load->view('header', $data);
		$this->load->view('user_left_sidebar', $data);
		$this->load->view('user_coupons', $data);
		$this->load->view('user_right_sidebar', $data);
		$this->load->view('footer');
	}

	/**
	 * How many live coupons one member may have at once.
	 *
	 * The page says posting a deal is free with any listing, so this is not a
	 * plan entitlement — it is a spam ceiling, and an operator can move it at
	 * /admin/setting without a deploy.
	 */
	private function couponCap()
	{
		return max(1, (int) site_setting('coupon_max_per_member', '10'));
	}

	/**
	 * Post a deal. GET renders the form, POST writes it.
	 */
	public function add_coupon()
	{
		$session   = (array) $this->session->userdata('logged_user');
		$logged_id = isset($session['logged_id']) ? (int) $session['logged_id'] : 0;

		$data = array();
		$data['logged_id'] = $logged_id;
		$data['coupon']    = NULL;
		$data['listings']  = $this->usermodel->getListingById($logged_id, NULL, NULL);
		$data['title']     = 'Post a deal';

		// A deal belongs to a business. Without a listing there is nothing for
		// it to appear on, and /coupons would show an offer from nobody.
		if (empty($data['listings'])) {
			// getListingById() only returns PUBLISHED listings, so this also
			// catches a listing that is still waiting on approval — say so,
			// rather than telling someone to add what they have already added.
			$this->session->set_flashdata('coupon_error',
				'You need a published business listing first: a deal is posted against a listing, so people can see whose it is. '
				. 'If you have just added or claimed one, it may still be waiting for approval.');
			redirect('user/my-coupons');
			return;
		}

		$live = $this->usermodel->countCouponsByUser($logged_id, TRUE);
		if ($live >= $this->couponCap()) {
			$this->session->set_flashdata('coupon_error',
				'You already have ' . $live . ' live deals, which is the limit. End or edit one of those to post another.');
			redirect('user/my-coupons');
			return;
		}

		if ($this->input->method() === 'post') {
			// 10 posts per IP per hour. A business posting its own offers never
			// comes near it; a script filling the public page does.
			if (!$this->rate_limit->check_ip('add_coupon', 10, 3600)) {
				$this->rate_limit->reject(3600);
				return;
			}

			$clean = $this->validateCouponPost($errors);
			// Checked before anything is written: a picture the member chose
			// and we refused used to vanish while the deal posted anyway.
			$photo = empty($errors) ? $this->uploadCouponPhoto($errors) : NULL;
			if (empty($errors)) {
				$clean['coupon_user_id'] = $logged_id;
				$clean['coupon_status']  = 1;
				$clean['coupon_cdt']     = date('Y-m-d H:i:s');

				// The picture is optional. Write '' rather than leaving the
				// column out, so an install whose coupon_photo is NOT NULL
				// with no default still accepts a deal without one.
				$clean['coupon_photo'] = $photo !== NULL ? $photo : '';

				$new_id = $this->usermodel->saveCoupon($clean);
				if ($new_id) {
					$this->session->set_flashdata('coupon_message',
						'Your deal is live — it is on the deals page, on your listing and on the homepage.');
					redirect('user/my-coupons');
					return;
				}
				$errors['general'] = 'Something went wrong saving that deal. Please try again.';
			}

			$data['coupon_errors'] = $errors;
			$data['coupon_old']    = $this->input->post();
		}

		$this->load->view('header', $data);
		$this->load->view('user_left_sidebar', $data);
		$this->load->view('add_coupons', $data);
		$this->load->view('user_right_sidebar', $data);
		$this->load->view('footer');
	}

	/**
	 * Edit a deal. The model's WHERE carries the ownership check, so another
	 * member's coupon id is a 404 and never an edit form.
	 */
	public function edit_coupon($coupon_id = 0)
	{
		$session   = (array) $this->session->userdata('logged_user');
		$logged_id = isset($session['logged_id']) ? (int) $session['logged_id'] : 0;

		$coupon = $this->usermodel->getCouponForOwner((int) $coupon_id, $logged_id);
		if (!$coupon) {
			show_404();
			return;
		}

		$data = array();
		$data['logged_id'] = $logged_id;
		$data['coupon']    = $coupon;
		$data['listings']  = $this->usermodel->getListingById($logged_id, NULL, NULL);
		$data['title']     = 'Edit deal';

		if ($this->input->method() === 'post') {
			$clean = $this->validateCouponPost($errors);
			$photo = empty($errors) ? $this->uploadCouponPhoto($errors) : NULL;
			if (empty($errors)) {
				$clean['coupon_status'] = $this->input->post('coupon_status') === '0' ? 0 : 1;

				if ($photo !== NULL) $clean['coupon_photo'] = $photo;

				if ($this->usermodel->saveCoupon($clean, (int) $coupon->coupon_id, $logged_id)) {
					$this->session->set_flashdata('coupon_message', 'Deal updated.');
					redirect('user/my-coupons');
					return;
				}
				$errors['general'] = 'Something went wrong saving that deal. Please try again.';
			}

			$data['coupon_errors'] = $errors;
			$data['coupon_old']    = $this->input->post();
		}

		$this->load->view('header', $data);
		$this->load->view('user_left_sidebar', $data);
		$this->load->view('edit_coupons', $data);
		$this->load->view('user_right_sidebar', $data);
		$this->load->view('footer');
	}

	/**
	 * Delete a deal. POST only: a GET link is one crawler away from clearing
	 * a member's offers, and the legacy view called it from an href.
	 */
	public function delete_coupon($coupon_id = 0)
	{
		$session   = (array) $this->session->userdata('logged_user');
		$logged_id = isset($session['logged_id']) ? (int) $session['logged_id'] : 0;

		if ($this->input->method() !== 'post') {
			show_404();
			return;
		}

		$coupon = $this->usermodel->getCouponForOwner((int) $coupon_id, $logged_id);
		if (!$coupon) {
			show_404();
			return;
		}

		$this->usermodel->deleteCouponForOwner((int) $coupon->coupon_id, $logged_id);
		$this->session->set_flashdata('coupon_message', 'Deal deleted.');
		redirect('user/my-coupons');
	}

	/**
	 * Shared validation for the add and edit forms.
	 *
	 * @param  array|null $errors filled with field => message
	 * @return array the columns to write
	 */
	private function validateCouponPost(&$errors)
	{
		$errors = array();

		$name  = trim((string) $this->input->post('coupon_name'));
		$code  = trim((string) $this->input->post('coupon_code'));
		$link  = trim((string) $this->input->post('coupon_link'));
		$from  = trim((string) $this->input->post('coupon_start_date'));
		$to    = trim((string) $this->input->post('coupon_end_date'));
		$terms = trim((string) $this->input->post('coupon_details'));
		$who   = trim((string) $this->input->post('coupon_use_members'));

		if ($name === '') {
			$errors['coupon_name'] = 'Give the deal a name — "20% off your first visit", say.';
		} elseif (mb_strlen($name) > 150) {
			$errors['coupon_name'] = 'That name is too long (150 characters maximum).';
		}

		if ($code === '') {
			$errors['coupon_code'] = 'Give the code people show in store or type at checkout.';
		} elseif (!preg_match('/^[A-Za-z0-9 _-]{2,40}$/', $code)) {
			$errors['coupon_code'] = 'Codes are 2 to 40 characters: letters, numbers, spaces, - and _.';
		}

		// external_url() refuses anything that is not http/https, so a
		// javascript: or data: link cannot reach the public page. It also
		// prefixes a bare domain with https://, which means it never returns
		// '' for plain nonsense — hence the second check: "not a url" would
		// otherwise be stored as https://not a url.
		$link_url = $link !== '' ? external_url($link) : '';
		if ($link !== '' && ($link_url === '' || !filter_var($link_url, FILTER_VALIDATE_URL))) {
			$errors['coupon_link'] = 'That link does not look like a web address.';
			$link_url = '';
		}

		$from_ts = $from !== '' ? strtotime($from) : FALSE;
		$to_ts   = $to   !== '' ? strtotime($to)   : FALSE;

		if ($from_ts === FALSE) {
			$errors['coupon_start_date'] = 'When does the deal start?';
		}
		if ($to_ts === FALSE) {
			$errors['coupon_end_date'] = 'When does the deal end?';
		} elseif ($from_ts !== FALSE && $to_ts < $from_ts) {
			$errors['coupon_end_date'] = 'The end date is before the start date.';
		}

		if (mb_strlen($terms) > 2000) {
			$errors['coupon_details'] = 'The terms are too long (2000 characters maximum).';
		}
		if (mb_strlen($who) > 150) {
			$errors['coupon_use_members'] = 'That is too long (150 characters maximum).';
		}

		return array(
			'coupon_name'        => $name,
			'coupon_code'        => $code,
			'coupon_link'        => $link_url,
			'coupon_details'     => $terms,
			'coupon_use_members' => $who,
			'coupon_start_date'  => $from_ts !== FALSE ? date('Y-m-d', $from_ts) : NULL,
			'coupon_end_date'    => $to_ts   !== FALSE ? date('Y-m-d', $to_ts)   : NULL,
		);
	}

	/**
	 * @param  array      $errors gains a coupon_photo message when a picture
	 *                            was chosen but refused
	 * @return string|null the stored path, or NULL when nothing was uploaded —
	 *                     which on an edit means "keep the picture you have".
	 */
	private function uploadCouponPhoto(array &$errors = array())
	{
		if (empty($_FILES['coupon_photo']['name'])) {
			return NULL;
		}

		$saved = safe_upload_image($_FILES['coupon_photo'], 'uploads/coupons/');
		if ($saved === FALSE) {
			$errors['coupon_photo'] = 'That picture was not accepted: '
				. (upload_last_error() ?: 'the file could not be read.')
				. ' Choose another, or leave the field empty — the picture is optional.';
			return NULL;
		}

		return $saved;
	}

	/*=========================
		Claim listing flow (public side)

		Anyone can submit a claim against a listing they say is their
		business. The submission is queued in claim_listings; an admin
		approves it from /admin/claimed-listings (Admin::claim_approve).
		Both submitter and admin get an email notification at each
		state change so the workflow doesn't stall silently.
		==========================*/

	public function claim_listing($slug = '')
	{
		// Visitors must be signed in so we can later transfer the
		// listing to a real user_id on approval.
		if (!$this->session->userdata('logged_user')) {
			$this->session->set_userdata('redirect_url', current_url());
			redirect('login');
			return;
		}

		$listing = $this->usermodel->getSingleValue($slug, 'listing_slug', 'listings');
		if (!$listing) { show_404(); return; }

		$session = $this->session->userdata('logged_user');
		$user_id = (int) $session['logged_id'];

		// One claim per (listing, user) makes sense — don't let people
		// spam a listing they already claimed.
		$existing = $this->db
			->where('listing_id',         (int) $listing->listing_id)
			->where('enquiry_sender_id',  $user_id)
			->limit(1)
			->get('claim_listings')
			->row();
		if ($existing) {
			$this->session->set_flashdata(
				'claim_message',
				'You have already submitted a claim for this listing — status: ' .
				((int) $existing->claim_status === 1 ? 'approved' : 'pending review') . '.'
			);
			redirect('listing-details/' . rawurlencode($slug));
			return;
		}

		if ($this->input->method() === 'post') {
			// Light rate-limit (3 claims per IP per hour).
			if (!$this->rate_limit->check_ip('claim_listing', 3, 3600)) {
				$this->rate_limit->reject(3600);
				return;
			}

			$this->form_validation->set_rules('enquiry_name',    'Your name',     'trim|required|min_length[2]');
			$this->form_validation->set_rules('enquiry_email',   'Email',         'trim|required|valid_email');
			$this->form_validation->set_rules('enquiry_message', 'Proof message', 'trim|required|min_length[20]');

			if (!$this->form_validation->run()) {
				$data = array(
					'listing'   => $listing,
					'error'     => strip_tags(validation_errors()) ?: 'Please complete the required fields.',
				);
				$this->loadHeaderCategories($data);
				$data['title'] = 'Claim ' . $listing->listing_name . ' — Pearland411';
		// No advertising on this page. A business owner proving they own a listing. Short, high-intent, and not
		// somewhere to sell a banner.

				$this->load->view('header', $data);
				$this->load->view('claim_listing', $data);
				$this->load->view('footer');
				return;
			}

			$claim_id = $this->usermodel->claimBusiness(array(
				'listing_id'        => (int) $listing->listing_id,
				'enquiry_sender_id' => $user_id,
				'enquiry_name'      => $this->input->post('enquiry_name'),
				'enquiry_email'     => $this->input->post('enquiry_email'),
				'enquiry_mobile'    => $this->input->post('enquiry_mobile'),
				'enquiry_message'   => $this->input->post('enquiry_message'),
				'enquiry_source'    => $listing->listing_name,
				'claim_status'      => 0,
			));

			if (!$claim_id) {
				$this->session->set_flashdata('claim_message', 'Could not save your claim. Please try again.');
				redirect('listing-details/' . rawurlencode($slug));
				return;
			}

			// Notifications.
			$this->load->library('mailer');
			$listing_url = base_url('listing-details/' . rawurlencode($listing->listing_slug));

			$ack = '<p>Thanks &mdash; we received your request to claim <strong>'
				. html_escape($listing->listing_name) . '</strong>.</p>'
				. '<p>An administrator will review the claim and email you when it is processed. '
				. 'Approvals usually take 1&ndash;2 business days.</p>'
				. '<p>Listing: <a href="' . $listing_url . '">' . $listing_url . '</a></p>';
			$this->mailer->send(
				$this->input->post('enquiry_email'),
				'We received your listing claim request',
				$ack
			);

			$admin_to = form_recipient_email();
			if ($admin_to) {
				$alert = '<p>New listing claim #' . $claim_id . '</p>'
					. '<ul>'
					. '<li>Listing: <a href="' . $listing_url . '">' . html_escape($listing->listing_name) . '</a></li>'
					. '<li>Claimant: ' . html_escape($this->input->post('enquiry_name')) . '</li>'
					. '<li>Email: '   . html_escape($this->input->post('enquiry_email')) . '</li>'
					. '<li>Phone: '   . html_escape($this->input->post('enquiry_mobile')) . '</li>'
					. '</ul>'
					. '<p>Message:</p><blockquote>'
					. nl2br(html_escape($this->input->post('enquiry_message')))
					. '</blockquote>'
					. '<p>Review &amp; approve at: '
					. base_url('admin/claimed-listings')
					. '</p>';
				$this->mailer->send($admin_to, '[Pearland411] New listing claim — ' . $listing->listing_name, $alert);
			}

			$this->session->set_flashdata('claim_message',
				'Thanks! Your claim has been submitted. We will email you when an administrator reviews it.');
			redirect('listing-details/' . rawurlencode($slug));
			return;
		}

		// GET — render the form.
		$data = array(
			'listing' => $listing,
			'prefill' => array(
				'enquiry_name'  => $session['name']     ?? '',
				'enquiry_email' => $session['email_id'] ?? '',
			),
		);
		$this->loadHeaderCategories($data);
		$data['title']         = 'Claim ' . $listing->listing_name . ' — Pearland411';
		$data['canonical_url'] = base_url('claim-listing/' . rawurlencode($slug));

		// No advertising on this page. A business owner proving they own a listing. Short, high-intent, and not
		// somewhere to sell a banner.

		$this->load->view('header', $data);
		$this->load->view('claim_listing', $data);
		$this->load->view('footer');
	}
}
