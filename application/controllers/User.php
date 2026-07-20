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
			// Enquiry submission (rate-limited; anonymous OK)
			'add_enquiry',
			// Services catalog + quote requests
			'services', 'service_details', 'request_service',
			// Listing claim (form gates on session itself)
			'claim_listing',
			// Public coupons / deals
			'coupons', 'coupon_details',
			// Advertise landing page (form posts to /contact-submit)
			'post_your_ads',
		);

		$page = $this->router->fetch_method();

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

		// If no results (probably due to category_status filter), use getAllCategory with limit
		if (empty($header_cats)) {
			// Get all categories and limit to 50 for header/modal
			$all_cats = $this->usermodel->getAllCategory();
			$data['header_categories'] = array_slice($all_cats, 0, 50);
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

		// Load required data for home page with exact variable names used in template

		$data['latest_listings'] = $this->usermodel->getLatestListings(6);
		$data['latest_videos'] = $this->usermodel->getLatestVideos(3);
		$data['latest_news'] = $this->usermodel->getLatestNews(3);
		// Always use listing_categories for all_listing_categories and header_categories
		$data['all_listing_categories'] = $this->usermodel->getHeaderNavigationCategories(50);
		$data['random_listing_categories'] = $this->usermodel->getRandomTenCategory(10);
		$data['header_categories'] = $data['all_listing_categories'];

		// Debug mode - uncomment the line below to enable debugging
		// $data['debug_categories'] = true;

		// Optional: Add debug info to check data flow
		if (isset($_GET['debug']) && $_GET['debug'] == '1') {
			$data['debug_info'] = array(
				'all_listing_categories_count' => is_array($data['all_listing_categories']) ? count($data['all_listing_categories']) : 0,
				'header_categories_count' => isset($data['header_categories']) && is_array($data['header_categories']) ? count($data['header_categories']) : 0,
				'debug_enabled' => true
			);
		}

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
					'sameAs'   => array_values(array_filter(array(
						site_setting('social_facebook_url',  '') ?: null,
						site_setting('social_twitter_url',   '') ?: null,
						site_setting('social_instagram_url', '') ?: null,
						site_setting('social_linkedin_url',  '') ?: null,
						site_setting('social_youtube_url',   '') ?: null,
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
		// Load model
		$this->load->model('usermodel');

		$data = array();
		$data['title'] = 'About Us';
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
		$this->load->view('header', $data);
		$this->load->view('faq');
		$this->load->view('footer');
	}


	// CONTACT Page (static, with header/footer)
	public function contact_us()
	{
		$data = array();
		$data['title'] = 'Contact Us';
		$this->loadHeaderCategories($data);
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

		$this->form_validation->set_rules('fullName', 'Name',  'trim|required|min_length[2]');
		$this->form_validation->set_rules('email',    'Email', 'trim|required|valid_email');
		$this->form_validation->set_rules('message',  'Message', 'trim|required|min_length[10]');

		if (!$this->form_validation->run()) {
			$this->session->set_flashdata('contact_error',
				strip_tags(validation_errors()) ?: 'Please complete the required fields.');
			redirect('contact-us');
			return;
		}

		$name    = (string) $this->input->post('fullName');
		$email   = (string) $this->input->post('email');
		$phone   = (string) $this->input->post('phone');
		$subject = (string) $this->input->post('subject');
		$message = (string) $this->input->post('message');

		$this->db->insert('enquiries', array(
			'enquiry_name'       => $name,
			'enquiry_email'      => $email,
			'enquiry_mobile'     => $phone,
			'enquiry_message'    => ($subject !== '' ? '[' . $subject . '] ' : '') . $message,
			'enquiry_source'     => 'Contact Us',
			'enquiry_type'       => 'contact',
			'enquiry_save'       => 0,
			'enquiry_created_at' => date('Y-m-d H:i:s'),
		));

		$this->load->library('mailer');

		// Acknowledge to the sender.
		$ack = '<p>Thanks — we received your message and will get back to you shortly.</p>'
			. '<p>For reference, you sent:</p>'
			. '<blockquote>' . nl2br(html_escape($message)) . '</blockquote>';
		$this->mailer->send($email, 'We received your message', $ack);

		// Notify the admin address.
		$admin_to = env('MAIL_FROM_ADDRESS', '');
		if ($admin_to) {
			$alert = '<p>New contact-form submission:</p>'
				. '<ul>'
				. '<li>Name: '    . html_escape($name)    . '</li>'
				. '<li>Email: '   . html_escape($email)   . '</li>'
				. '<li>Phone: '   . html_escape($phone)   . '</li>'
				. '<li>Subject: ' . html_escape($subject) . '</li>'
				. '</ul>'
				. '<p>Message:</p><blockquote>' . nl2br(html_escape($message)) . '</blockquote>';
			$this->mailer->send($admin_to, '[Pearland411] Contact-Us message from ' . $name, $alert);
		}

		$this->session->set_flashdata('contact_success',
			'Thanks! We received your message and will be in touch soon.');
		redirect('contact-us');
	}


	// PRIVACY Page (static, with header/footer)
	public function privacy_policy()
	{
		$this->load->model('usermodel');
		$data = array();
		$data['title'] = 'Privacy Policy';
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
		$this->load->view('header', $data);
		$this->load->view('terms_of_use');
		$this->load->view('footer');
	}


	// NEWS Page
	public function news()
	{
		if (!$this->requireService('service_news_enabled')) return;
		$this->load->model('usermodel');
		$this->load->library('pagination');

		$search_query = $this->input->get('q', TRUE);
		$page = (int) $this->input->get('page', TRUE);
		$per_page = 10;
		if ($page < 1) $page = 1;
		$offset = ($page - 1) * $per_page;

		$total_rows = $this->usermodel->countNewsWithSearch($search_query);
		$all_news = $this->usermodel->getNewsWithSearch($search_query, $per_page, $offset);

		// Pagination config (custom, similar to events)
		// Pagination config (reuse_query_string fixes duplicate/empty links)
		$config = [
			'base_url' => base_url('news'),
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

		$data['all_news'] = $all_news;
		$data['pagination_links'] = $pagination_links;
		$data['search_query'] = $search_query;
		$data['title'] = 'News';
		$this->load->view('header', $data);
		$this->load->view('news', $data);
		$this->load->view('footer');
	}



	/*===================
		Global Listing Details Page
		URL: /user/listing_details_global/slug
		This handles /listing-details/slug via routing
	===================*/
	public function listing_details_global($slug)
	{
		// Get listing details by slug
		$data['listing'] = $this->usermodel->getSingleValue($slug, 'listing_slug', 'listings');

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

		// Get listing likes (if you have this functionality)
		$data['listing_likes'] = array();
		// Uncomment below if you have listing likes functionality
		// $data['listing_likes'] = $this->usermodel->getListingLikes($data['listing']->listing_id);

		// Load categories for header display - Professional approach
		$this->loadHeaderCategories($data);

		// Load views
		$this->load->view('header', $data);
		$this->load->view('listing_details', $data);
		$this->load->view('footer');
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

			if (!empty($search_query)) {
				// Get search results
				$data['search_results'] = $this->usermodel->globalSearch($search_query);
				$data['total_results'] = isset($data['search_results']['total_count']) ? $data['search_results']['total_count'] : 0;
			}

			// Load additional data for the page
			try {
				$data['categories'] = $this->usermodel->getAllCategory();
				$data['latest_news'] = $this->usermodel->getlatest_news(5);
			} catch (Exception $e) {
				$data['categories'] = array();
				$data['latest_news'] = array();
			}

			// Set page title and meta
			$data['page_title'] = !empty($search_query) ? 'Search Results for "' . $search_query . '"' : 'Search';
			$data['meta_description'] = 'Search results for ' . $search_query . ' on Pearland411';

			// Load header, search page, and footer
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
		$config['cur_tag_open'] = '<li class="page-item active"><a href="/" class="page-link">';
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
		$config['cur_tag_open'] = '<li class="page-item active"><a href="/" class="page-link">';
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
			$blog_slug = strtolower(str_replace(' ', '-', $blog_name)) . '-' . time();

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

		// Pagination config
		$config = [
			'base_url' => base_url('user/user-enquiries'),
			'per_page' => 10,
			'total_rows' => count($this->usermodel->getAllEnquiries()),
			'uri_segment' => 3,
		];
		$config['full_tag_open'] = '<ul class="pagination">';
		$config['full_tag_close'] = '</ul>';
		$config['cur_tag_open'] = '<li class="page-item active"><a href="/" class="page-link">';
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

		// Get enquiries - showing all for now (can be changed to user-specific later)
		$data['allEnquiries'] = $this->usermodel->getAllEnquiries();
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
	 * Delete a specific enquiry
	 */
	public function delete_enquiry($enquiry_id = null)
	{
		if (!$enquiry_id) {
			$this->session->set_flashdata('error_msg', 'Invalid enquiry ID.');
			redirect('user/enquiries');
			return;
		}

		$data['logged_id'] = null;
		if ($this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$data['logged_id'] = $session['logged_id'];
		}

		if (!$data['logged_id']) {
			$this->session->set_flashdata('error_msg', 'Please login to continue.');
			redirect('login');
			return;
		}

		// Check if enquiry exists
		$enquiry = $this->usermodel->getEnquiryByIdSimple($enquiry_id);
		if (!$enquiry) {
			$this->session->set_flashdata('error_msg', 'Enquiry not found.');
			redirect('user/enquiries');
			return;
		}

		// Delete the enquiry
		$delete_result = $this->usermodel->deleteEnquirySimple($enquiry_id);
		if ($delete_result) {
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
		$this->load->view('header', $data);
		$this->load->view('product_details', $data);
		$this->load->view('footer');
	}





	/*All Category*/

	public function all_category($offset = 0)
	{

		if ($this->session->userdata('logged_user')) {

			$data['user'] = $this->usermodel->getUserInformation();
		}

		// Pagination configuration
		$limit = 20; // 20 categories per page
		$offset = (int)$offset;

		// Get categories with pagination
		$data['AllCategory'] = $this->usermodel->getAllCategoryWithPagination($limit, $offset);

		// Get total count for pagination
		$total_categories = $this->usermodel->countAllCategories();

		// Load pagination library
		$this->load->library('pagination');

		// Pagination config
		$config['base_url'] = base_url('user/all_category/');
		$config['total_rows'] = $total_categories;
		$config['per_page'] = $limit;
		$config['uri_segment'] = 3;

		// Pagination styling (same as all-listing)
		$config['full_tag_open'] = '<ul class="pagination">';
		$config['full_tag_close'] = '</ul>';
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['first_tag_open'] = '<li class="page-item">';
		$config['first_tag_close'] = '</li>';
		$config['prev_link'] = '&laquo; Previous';
		$config['prev_tag_open'] = '<li class="page-item">';
		$config['prev_tag_close'] = '</li>';
		$config['next_link'] = 'Next &raquo;';
		$config['next_tag_open'] = '<li class="page-item">';
		$config['next_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li class="page-item">';
		$config['last_tag_close'] = '</li>';
		$config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';
		$config['cur_tag_close'] = '</a></li>';
		$config['num_tag_open'] = '<li class="page-item">';
		$config['num_tag_close'] = '</li>';
		$config['attributes'] = array('class' => 'page-link');

		$this->pagination->initialize($config);
		$data['link'] = $this->pagination->create_links();

		// Pagination info
		$data['total_categories'] = $total_categories;
		$data['current_page'] = floor($offset / $limit) + 1;
		$data['total_pages'] = ceil($total_categories / $limit);
		$data['showing_from'] = $offset + 1;
		$data['showing_to'] = min($offset + $limit, $total_categories);

		$data['title'] = "Pearland | All Category";

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

		// $data['allListings'] = $this->usermodel->getListingByCategory($cat_slug);

		// Determine the correct URI segment for pagination offset
		// URL Structure: 
		// /user/all_listing -> segment 3 is offset (if numeric)
		// /user/all_listing/category_slug -> segment 4 is offset (if numeric)
		// /user/all_listing/10 -> segment 3 is offset
		// /user/all_listing/category_slug/10 -> segment 4 is offset

		// Current offset is already set in the URL parsing above
		// No additional logic needed here

		// Manual pagination setup for better URL control
		$per_page = 10;
		$total_listings = $this->usermodel->countListingsByCategory($cat_slug);
		$total_pages = ceil($total_listings / $per_page);
		$current_page = floor($current_offset / $per_page) + 1;

		// Clean code - debug info removed for production		// Build manual pagination
		$pagination_html = '';
		if ($total_pages > 1) {
			$pagination_html = '<ul class="pagination justify-content-center">';

			// Previous link
			if ($current_page > 1) {
				$prev_offset = ($current_page - 2) * $per_page;
				if ($cat_slug) {
					$prev_url = base_url('user/all_listing/' . $cat_slug . ($prev_offset > 0 ? '/' . $prev_offset : ''));
				} else {
					$prev_url = base_url('user/all_listing' . ($prev_offset > 0 ? '/' . $prev_offset : ''));
				}
				$pagination_html .= '<li class="page-item"><a class="page-link" href="' . $prev_url . '">&laquo; Prev</a></li>';
			}

			// Page numbers
			$start_page = max(1, $current_page - 2);
			$end_page = min($total_pages, $current_page + 2);

			for ($i = $start_page; $i <= $end_page; $i++) {
				$page_offset = ($i - 1) * $per_page;
				if ($cat_slug) {
					$page_url = base_url('user/all_listing/' . $cat_slug . ($page_offset > 0 ? '/' . $page_offset : ''));
				} else {
					$page_url = base_url('user/all_listing' . ($page_offset > 0 ? '/' . $page_offset : ''));
				}

				if ($i == $current_page) {
					$pagination_html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
				} else {
					$pagination_html .= '<li class="page-item"><a class="page-link" href="' . $page_url . '">' . $i . '</a></li>';
				}
			}

			// Next link
			if ($current_page < $total_pages) {
				$next_offset = $current_page * $per_page;
				if ($cat_slug) {
					$next_url = base_url('user/all_listing/' . $cat_slug . '/' . $next_offset);
				} else {
					$next_url = base_url('user/all_listing/' . $next_offset);
				}
				$pagination_html .= '<li class="page-item"><a class="page-link" href="' . $next_url . '">Next &raquo;</a></li>';
			}

			$pagination_html .= '</ul>';
		}

		$config = array(); // Keep for compatibility but we'll use manual pagination

		// No need to initialize CI pagination anymore
		// $this->pagination->initialize($config);

		$data['allListings']   = $this->usermodel->getListingByCategory($cat_slug, $per_page, $current_offset);
		$data['link'] 		   = $pagination_html;
		$data['total_pages']   = $total_pages;
		$data['current_page']  = $current_page;
		$data['total_listings'] = $total_listings;

		// Set page title based on category
		if ($cat_slug) {
			$category_name = $this->usermodel->getCategoryNameBySlug($cat_slug);
			$data['title'] = "Pearland | " . ucfirst($category_name) . " Listings";
		} else {
			$data['title'] = "Pearland | All Listings";
		}
		$data['randListings']  = $this->usermodel->getRandFiveFeatureListing();
		$data['current_category_slug'] = $cat_slug;

		$this->load->view('header', $data);
		$this->load->view('all-listing');
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
			$base_url = base_url('user/all-blog');
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
		$this->load->model('usermodel');
		$this->load->library('pagination');

		$search_query = $this->input->get('q', TRUE);
		$page = (int) $this->input->get('page', TRUE);
		$per_page = 10;
		if ($page < 1) $page = 1;
		$offset = ($page - 1) * $per_page;

		$total_rows = $this->usermodel->countEventsWithSearch($search_query);
		$all_events = $this->usermodel->getEventsWithPagination($search_query, $per_page, $offset);

		$config = [
			'base_url' => base_url('event'),
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

		$data['Allevent'] = $all_events;
		$data['pagination_links'] = $pagination_links;
		$data['search_query'] = $search_query;
		$data['title'] = "Pearland | All Event";

		// Load header categories using professional MVC approach
		$this->loadHeaderCategories($data);

		$this->load->view('header', $data);
		$this->load->view('events', $data);
		$this->load->view('footer');
	}

	/**
	 * Event Search Method
	 * Handles event search functionality with GET parameter
	 * URL: /user/event_search?q=search_term
	 */
	public function event_search()
	{
		if (!$this->requireService('service_events_enabled')) return;
		// Get search query from GET parameter
		$search_query = $this->input->get('q');

		if ($this->session->userdata('logged_user')) {
			$data['user'] = $this->usermodel->getUserInformation();
		}

		// If search query exists, search events, otherwise show all events
		if (!empty($search_query)) {
			$data['Allevent'] = $this->usermodel->searchEvents($search_query, 50); // Increase limit to 50
			$data['search_query'] = $search_query;
			$data['title'] = "Search Results for '" . htmlspecialchars($search_query) . "' | Events";
		} else {
			$data['Allevent'] = $this->usermodel->get_events();
			$data['search_query'] = '';
			$data['title'] = "Pearland | All Event";
		}

		// Load header categories using professional MVC approach
		$this->loadHeaderCategories($data);

		$this->load->view('header', $data);
		$this->load->view('events', $data);
		$this->load->view('footer');
	}

	// Videos Page
	public function videos()
	{
		if (!$this->requireService('service_videos_enabled')) return;
		$this->load->model('usermodel');
		$this->load->library('pagination');

		$search_query = $this->input->get('q', TRUE);
		$page = (int) $this->input->get('page', TRUE);
		$per_page = 10;
		if ($page < 1) $page = 1;
		$offset = ($page - 1) * $per_page;

		$total_rows = $this->usermodel->countVideosWithSearch($search_query);
		$all_videos = $this->usermodel->getAllVideos($search_query, $per_page, $offset);

		$config = [
			'base_url' => base_url('videos'),
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

		$data['all_videos'] = $all_videos;
		$data['pagination_links'] = $pagination_links;
		$data['search_query'] = $search_query;
		$data['title'] = "Pearland | Videos";

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
		$data['title'] = "Pearland | Classifieds";

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

		$this->load->view('header', $data);

		$this->load->view('register');

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
		$data['title'] = 'Pearland | Forgot Password';

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
		$data['title'] = 'Pearland | Reset Password';
		$data['token'] = $token;

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
	 * GET /verify-email/<token> -> mark the user verified.
	 */
	public function verify_email($token = '')
	{
		$ok = $this->usermodel->verifyEmailByToken($token);

		$data = array();
		$this->loadHeaderCategories($data);
		$data['title']    = 'Pearland | Email Verification';
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

		$data['AllListings'] = $this->usermodel->getListingById($data['logged_id'], 10, NULL);

		$data['AllEvents'] = $this->usermodel->dashboardInfo('events', 'event_status', 0, NULL);

		$data['AllBlogs'] = $this->usermodel->dashboardInfo('blogs', 'blog_status', 0, NULL);





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
			'name'          => htmlentities($this->input->post('name'), ENT_QUOTES),
			'email_id'      => htmlentities($this->input->post('email_id'), ENT_QUOTES),
			'mobile_number' => htmlentities($this->input->post('mobile_number'), ENT_QUOTES),
			'user_address'  => htmlentities($this->input->post('user_address'), ENT_QUOTES),
			'user_state'    => htmlentities($this->input->post('user_state'), ENT_QUOTES),
			'user_zip_code' => htmlentities($this->input->post('user_zip_code'), ENT_QUOTES),
			'user_contact_email' => htmlentities($this->input->post('user_contact_email'), ENT_QUOTES),
			'user_website'  => htmlentities($this->input->post('user_website'), ENT_QUOTES),
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



		// Get listing reviews
		$data['listing_reviews'] = $this->usermodel->getListingReviews($data['listing']->listing_id, 10);

		// Get review statistics for rating breakdown
		$data['review_stats'] = $this->usermodel->getReviewStats($data['listing']->listing_id);

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
		$rs = $data['review_stats'] ?? null;
		if (is_object($rs) && !empty($rs->total_reviews) && (int) $rs->total_reviews > 0) {
			$ld['aggregateRating'] = array(
				'@type'       => 'AggregateRating',
				'ratingValue' => (float) $rs->average_rating,
				'reviewCount' => (int) $rs->total_reviews,
			);
		}
		$data['json_ld'] = json_ld($ld);

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
		// Get video details by slug
		$data['video'] = $this->usermodel->getSingleValue($slug, 'video_slug', 'video');

		if (!$data['video']) {
			show_404();
			return;
		}

		// Increment views count for this video
		$this->usermodel->incrementViews('video', 'video_id', $data['video']->video_id);

		// Get logged user ID for form prefilling
		$logged_id = NULL;
		$data['user'] = NULL;
		if ($this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$logged_id = $session['logged_id'];
			$data['user'] = $this->usermodel->getSingleValue($logged_id, 'user_id', 'user');
		}
		$data['logged_id'] = $logged_id;

		// Generate share URLs dynamically for this video
		$video_url = base_url('user/video-details/' . $data['video']->video_slug);
		$video_title = $data['video']->video_title;
		$video_image = !empty($data['video']->video_image) ? base_url($data['video']->video_image) : '';
		$data['share_urls'] = [
			'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($video_url) . '&quote=' . urlencode($video_title),
			'twitter' => 'https://twitter.com/share?text=' . urlencode($video_title) . '&url=' . urlencode($video_url),
			'whatsapp' => 'https://wa.me/?text=' . urlencode($video_title . ' ' . $video_url),
			'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode($video_url),
			'pinterest' => 'https://www.pinterest.com/pin/create/bookmarklet/?media=' . urlencode($video_image) . '&url=' . urlencode($video_url) . '&description=' . urlencode($video_title),
		];

		// Get 3 related videos (excluding current)
		$data['related_videos'] = $this->usermodel->getRelatedVideos($data['video']->video_id, 3);

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

		// Increment views count for this news
		$this->usermodel->incrementViews('news', 'news_id', $data['news']->news_id);

		// Get logged user ID for form prefilling
		$logged_id = NULL;
		$data['user'] = NULL;
		if ($this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$logged_id = $session['logged_id'];
			$data['user'] = $this->usermodel->getSingleValue($logged_id, 'user_id', 'user');
		}
		$data['logged_id'] = $logged_id;

		// Generate share URLs dynamically for this news
		$news_url = base_url('user/news-details/' . $data['news']->news_slug);
		$news_title = $data['news']->news_title;
		$news_image = !empty($data['news']->news_image) ? base_url($data['news']->news_image) : '';
		$data['share_urls'] = [
			'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($news_url) . '&quote=' . urlencode($news_title),
			'twitter' => 'https://twitter.com/share?text=' . urlencode($news_title) . '&url=' . urlencode($news_url),
			'whatsapp' => 'https://wa.me/?text=' . urlencode($news_title . ' ' . $news_url),
			'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode($news_url),
			'pinterest' => 'https://www.pinterest.com/pin/create/bookmarklet/?media=' . urlencode($news_image) . '&url=' . urlencode($news_url) . '&description=' . urlencode($news_title),
		];

		// Get 3 related news (excluding current)
		$data['related_news'] = $this->usermodel->getRelatedNews($data['news']->news_id, 3);

		// SEO: per-page meta + NewsArticle JSON-LD. Prefer admin-set
		// news_seo_* columns; fall back to trimmed body.
		$news = $data['news'];
		$desc_auto = trim(strip_tags((string) ($news->news_description ?? '')));
		$desc_auto = $desc_auto !== '' ? mb_substr($desc_auto, 0, 160) : ('News story: ' . $news->news_title);
		$data['title']            = !empty($news->news_seo_title)
			? $news->news_seo_title
			: ($news->news_title . ' — Pearland411');
		$data['meta_description'] = !empty($news->news_seo_description)
			? $news->news_seo_description
			: $desc_auto;
		$data['meta_keywords']    = !empty($news->news_seo_keywords)
			? $news->news_seo_keywords
			: '';
		$data['canonical_url']    = $news_url;
		if (!empty($news->news_image)) $data['og_image'] = $news_image;

		$data['json_ld'] = json_ld(array(
			'@context'      => 'https://schema.org',
			'@type'         => 'NewsArticle',
			'headline'      => (string) $news->news_title,
			'datePublished' => !empty($news->created_at) ? date('c', strtotime($news->created_at)) : null,
			'image'         => $news_image ?: null,
			'mainEntityOfPage' => $news_url,
			'publisher'     => array(
				'@type' => 'Organization',
				'name'  => 'Pearland411',
				'logo'  => array('@type' => 'ImageObject', 'url' => base_url('assets/images/pearland-logo.png')),
			),
		));

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

				'listing_name' 		=> htmlentities($this->input->post('listing_name'), ENT_QUOTES),

				'listing_mobile'	=> htmlentities($this->input->post('listing_mobile'), ENT_QUOTES),

				'listing_email'		=> htmlentities($this->input->post('listing_email'), ENT_QUOTES),

				'listing_whatsapp'	=> htmlentities($this->input->post('listing_whatsapp'), ENT_QUOTES),

				'listing_website'	=> htmlentities($this->input->post('listing_website'), ENT_QUOTES),

				'listing_address'	=> htmlentities($this->input->post('listing_address'), ENT_QUOTES),

				'listing_lat'		=> htmlentities($this->input->post('listing_lat'), ENT_QUOTES),

				'listing_lng'		=> htmlentities($this->input->post('listing_lng'), ENT_QUOTES),

				'country_id'		=> htmlentities($this->input->post('country_id'), ENT_QUOTES),

				'city_name'			=> htmlentities($this->input->post('city_name'), ENT_QUOTES),

				'category_id'		=> htmlentities($this->input->post('category_id'), ENT_QUOTES),

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

			$inserted_id = htmlentities($this->input->post('listing_id'), ENT_QUOTES);

			$info = array(

				'service_name' 		=> htmlentities($this->input->post('service_name'), ENT_QUOTES),

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

			$inserted_id = htmlentities($this->input->post('listing_id'), ENT_QUOTES);



			$info = array(

				'service_1_name' 		=> htmlentities($this->input->post('service_1_name'), ENT_QUOTES),

				'service_1_price' 		=> htmlentities($this->input->post('service_1_price'), ENT_QUOTES),

				'service_1_detail' 		=> htmlentities($this->input->post('service_1_detail'), ENT_QUOTES),

				'service_1_view_more' 	=> htmlentities($this->input->post('service_1_view_more'), ENT_QUOTES),

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

			$inserted_id = htmlentities($this->input->post('listing_id'), ENT_QUOTES);





			$info = array(

				'google_map' 	=> htmlentities($this->input->post('google_map'), ENT_QUOTES),

				'360_view' 		=> htmlentities($this->input->post('360_view'), ENT_QUOTES),

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

			$inserted_id = htmlentities($this->input->post('listing_id'), ENT_QUOTES);



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
		$config['cur_tag_open'] = '<li class="page-item active"><a href="/" class="page-link">';
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
					'listing_status' 	=> 'Active'
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

				if ($_FILES && $_FILES['listing_profile_image']['name']) {
					$img_info = [
						'upload_path' => 'images/listings',
						'quality'     => '60%',
						'height'      => 500,
						'width'       => 500,
						'image_name'  => 'listing_profile_image'
					];

					$info['listing_image'] = uploadAndResize($img_info);
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

				if ($caps['can_photos'] && $_FILES && $_FILES['listing_profile_image']['name']) {
					$img_info = [
						'upload_path' => 'images/listings',
						'quality'     => '60%',
						'height'      => 500,
						'width'       => 500,
						'image_name'  => 'listing_profile_image'
					];

					$info['listing_image'] = uploadAndResize($img_info);
				}

				// Handle Gallery Images Upload for User Edit
				if ($caps['can_photos'] && $_FILES && isset($_FILES['listing_image_gallery']['name'][0]) && !empty($_FILES['listing_image_gallery']['name'][0])) {
					$gallery_images = [];
					$files_count = count($_FILES['listing_image_gallery']['name']);
					// Cap gallery photos to the plan's photo_limit (-1 = unlimited).
					if ($caps['photo_limit'] >= 0 && $files_count > $caps['photo_limit']) {
						$files_count = (int) $caps['photo_limit'];
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
								error_log("USER EDIT Gallery image uploaded: " . $uploaded_image);
							} else {
								error_log("USER EDIT Gallery image upload failed for: " . $_FILES['listing_image_gallery']['name'][$i]);
							}
						}
					}
					if (!empty($gallery_images)) {
						$info['listing_image_gallery'] = json_encode($gallery_images);
						error_log("USER EDIT Gallery images stored in DB: " . $info['listing_image_gallery']);
					} else {
						error_log("USER EDIT No gallery images to store");
					}
				} else {
					error_log("USER EDIT No gallery files detected in upload");
				}

				// Debug: Service fields check for edit
				$service_locations_edit = $this->input->post('service_locations') ? 'Present: "' . $this->input->post('service_locations') . '"' : 'MISSING';
				$service_offered_edit = $this->input->post('service_offered') ? 'Present: "' . $this->input->post('service_offered') . '"' : 'MISSING';

				// Store debug info in session for display after redirect
				$this->session->set_flashdata('debug_info', "EDIT DEBUG - service_locations: " . $service_locations_edit . " | service_offered: " . $service_offered_edit);

				// Try to update the listing
				$update_result = $this->usermodel->update($slug, $info, 'listing_slug', 'listings');

				if ($update_result) {
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
		$config['cur_tag_open'] = '<li class="page-item active"><a href="/" class="page-link">';
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
				$update_result = $this->usermodel->update($product_id, $update_data, 'product_id', 'products');

				if ($update_result) {
					$this->session->set_flashdata('message', 'Product updated successfully!');
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
				$insert_result = $this->usermodel->addProduct($insert_data);

				if ($insert_result) {
					$this->session->set_flashdata('message', 'Product added successfully!');
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
			$house_slug = strtolower(str_replace(' ', '-', $house_title)) . '-' . time();

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
		$this->load->view('header', $data);
		$this->load->view('house_details', $data);
		$this->load->view('footer');
	}

	/**
	 * Public "advertise on Pearland411" landing page.
	 * Form is now wired to /contact-submit (see views/post_your_ads.php)
	 * — self-serve ad checkout is blocked on Authorize.Net.
	 */
	public function post_your_ads()
	{
		$data = array();
		$this->loadHeaderCategories($data);
		$data['title'] = 'Advertise on Pearland411';
		$data['meta_description'] = 'Display ads on Pearland411 — homepage, listing, and category placements. Request pricing and inventory from our sales team.';
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
		$config['cur_tag_open'] = '<li class="page-item active"><a href="/" class="page-link">';
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
			$video_slug = strtolower(str_replace(' ', '-', $video_title)) . '-' . time();

			// Handle image upload
			$video_image = '';
			if ($_FILES && $_FILES['video_image']['name']) {
				$img_info = [
					'upload_path' => 'images/videos',
					'quality'     => '60%',
					'height'      => 500,
					'width'       => 500,
					'image_name'  => 'video_image'
				];
				$video_image = uploadAndResize($img_info);
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
				$this->session->set_flashdata('message', 'Video added successfully!');
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
			if ($_FILES && $_FILES['video_image']['name']) {
				$img_info = [
					'upload_path' => 'images/videos',
					'quality'     => '60%',
					'height'      => 500,
					'width'       => 500,
					'image_name'  => 'video_image'
				];
				$video_image = uploadAndResize($img_info);

				// Delete old image if new one uploaded
				if (!empty($video->video_image) && file_exists('./' . $video->video_image)) {
					unlink('./' . $video->video_image);
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
				$this->session->set_flashdata('message', 'Video updated successfully!');
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
			$classifieds_slug = strtolower(str_replace(' ', '-', $classifieds_title)) . '-' . time();

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
			$rental_slug = strtolower(str_replace(' ', '-', $rental_title)) . '-' . time();

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
		$data['related_events'] = $this->usermodel->getRelatedEvents($event->event_id, 3);

		// SEO: per-page meta + Event JSON-LD
		$desc = trim(strip_tags((string) ($event->event_description ?? '')));
		$desc = $desc !== '' ? mb_substr($desc, 0, 160) : ('Event details for ' . $event->event_name);
		$data['meta_description'] = $desc;
		$data['canonical_url']    = base_url('event-details/' . rawurlencode($event->event_slug));
		if (!empty($event->event_image)) $data['og_image'] = base_url($event->event_image);

		$ld = array(
			'@context' => 'https://schema.org',
			'@type'    => 'Event',
			'name'     => (string) $event->event_name,
			'url'      => $data['canonical_url'],
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

			// Basic validation
			$required_fields = [
				'event_name',
				'event_address',
				'event_start_date',
				'event_time',
				'event_description',
				'event_contact_name',
				'event_mobile',
				'event_email'
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
			$event_slug = strtolower(str_replace(' ', '-', $event_name)) . '-' . time();

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

		// Simple direct database insert
		$data = array(
			'listing_id' => $listing_id,
			'user_id' => $logged_id,
			'reviewer_name' => $name,
			'reviewer_email' => $email,
			'reviewer_mobile' => $mobile,
			'reviewer_city' => $city,
			'rating' => $rating,
			'review_message' => $message,
			'review_status' => 1,
			'created_at' => date('Y-m-d H:i:s')
		);

		// Insert into database
		if ($this->db->insert('listing_reviews', $data)) {
			// Update listing average rating and review count
			$this->usermodel->updateListingRating($listing_id);
		}

		// Set flash message
		$this->session->set_flashdata('review_success', 'Thank you! Your review has been submitted successfully.');

		// Redirect back to listing page with slug
		redirect('user/listing_details/' . $listing_slug);
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
		$receiving_user_id = $this->input->post('receiving_user_id');
		$enquiry_sender_id = $this->input->post('enquiry_sender_id');

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

		// Validation
		if (empty($enquiry_name) || empty($enquiry_email)) {
			if ($this->input->is_ajax_request()) {
				echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
				return;
			}
			$this->session->set_flashdata('error_msg', 'Please fill in all required fields.');
			redirect($_SERVER['HTTP_REFERER']);
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

		// If receiving_user_id is empty, try to get it from the content
		if (empty($receiving_user_id)) {
			if (!empty($listing_id)) {
				$listing = $this->db->get_where('listings', array('listing_id' => $listing_id))->row();
				$receiving_user_id = $listing ? $listing->user_id : null;
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

		// Dynamic redirect back to the same page for non-AJAX requests
		$redirect_url = $this->getDynamicRedirectUrl($source, $identifier);
		redirect($redirect_url);
	}

	/*===================
		Helper function to get redirect URL based on source and identifier
	===================*/
	private function getDynamicRedirectUrl($source, $identifier)
	{
		// If HTTP_REFERER is available, use it (most reliable)
		if (!empty($_SERVER['HTTP_REFERER'])) {
			return $_SERVER['HTTP_REFERER'];
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
		if ($this->session->userdata('logged_user')) {
			$user_data = $this->session->userdata('logged_user');
			$user_id = $user_data['logged_id']; // Use logged_id based on the User model pattern
			$data['user_subscription'] = $this->usermodel->getUserActiveSubscriptionForPricing($user_id);
		}

		// Load header categories for navigation
		$this->loadHeaderCategories($data);

		// Page title and meta
		$data['title'] = 'Pricing Plans - Choose Your Plan';

		// Load views
		$this->load->view('header', $data);
		$this->load->view('pricing_plan', $data);
		$this->load->view('footer', $data);
	}

	/**
	 * Checkout Page - Display checkout form with selected plan
	 * URL: /checkout?plan_id=X
	 */
	public function checkout()
	{
		// Get plan ID from GET parameter
		$plan_id = $this->input->get('plan_id');

		if (!$plan_id || !is_numeric($plan_id)) {
			$this->session->set_flashdata('error', 'Please select a valid plan to continue.');
			redirect('user/pricing_plan');
			return;
		}

		// Get selected plan details
		$data['selected_plan'] = $this->usermodel->getPlanByIdForPricing($plan_id);

		if (!$data['selected_plan']) {
			$this->session->set_flashdata('error', 'Selected plan not found.');
			redirect('user/pricing_plan');
			return;
		}

		// A subscription belongs to a user, so checkout requires login.
		if (!$this->session->userdata('logged_user')) {
			$this->session->set_userdata('redirect_url', base_url('checkout?plan_id=' . (int) $plan_id));
			$this->session->set_flashdata('error', 'Please sign in to continue to checkout.');
			redirect('login');
			return;
		}

		// Initialize user and billing data
		$data['billing_info'] = array();
		$data['stored_payment_card'] = null;
		$data['is_upgrade'] = false;
		$data['existing_subscription'] = null;

		// Get user information if logged in
		if ($this->session->userdata('logged_user')) {
			$user_data = $this->session->userdata('logged_user');
			$user_id = $user_data['logged_id'];

			// Get user billing information
			$user_info = $this->usermodel->getUserInformation();
			if ($user_info) {
				$data['billing_info'] = array(
					'first_name' => isset($user_info->name) ? explode(' ', $user_info->name)[0] : '',
					'last_name' => isset($user_info->name) ? (count(explode(' ', $user_info->name)) > 1 ? explode(' ', $user_info->name)[1] : '') : '',
					'email' => $user_info->email_id,
					'address' => isset($user_info->user_address) ? $user_info->user_address : '',
					'city' => '',
					'state' => isset($user_info->user_state) ? $user_info->user_state : '',
					'zip' => isset($user_info->user_zip_code) ? $user_info->user_zip_code : '',
					'country' => 'US'
				);
			}

			// Check for existing subscription (upgrade scenario)
			$existing_subscription = $this->usermodel->getUserActiveSubscriptionForPricing($user_id);
			if ($existing_subscription) {
				$data['is_upgrade'] = true;
				$data['existing_subscription'] = $existing_subscription;
			}

			// Get stored payment cards
			$payment_cards = $this->usermodel->getUserPaymentCards($user_id);
			if (!empty($payment_cards)) {
				$data['stored_payment_card'] = $payment_cards[0]; // Use first card
			}
		}

		// Load header categories
		$this->loadHeaderCategories($data);

		// Page title
		$data['title'] = 'Checkout - ' . $data['selected_plan']->name;

		// Get all durations for this plan
		$data['plan_durations'] = $this->usermodel->getPlanDurations($plan_id);

		// Helcim front-end config for the checkout view.
		$this->load->library('helcim_lib');
		$data['helcim_configured'] = $this->helcim_lib->is_configured();
		$data['helcimpay_js_url']  = $this->helcim_lib->helcimpay_js_url();

		// Load views
		$this->load->view('header', $data);
		$this->load->view('checkout', $data);
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

		$sub_id = $this->usermodel->activateHelcimSubscription(array(
			'user_id'               => (int) $pending['user_id'],
			'plan_id'               => (int) $pending['plan_id'],
			'duration_id'           => $pending['duration_id'] ? (int) $pending['duration_id'] : null,
			'amount'                => (float) $pending['amount'],
			'currency'              => $pending['currency'],
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
		// (recurring success/failure) is handled by the renewal job in Sprint 7.
		log_message('info', 'Helcim webhook received: ' . $raw);
		return $this->output->set_output(json_encode(array('ok' => true)));
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
		$this->loadHeaderCategories($data);

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
		$data['json_ld'] = json_ld($ld);

		$this->loadHeaderCategories($data);

		// Pre-fill the request form with session data when available.
		$data['prefill'] = array('name' => '', 'email' => '', 'phone' => '', 'company' => '');
		if ($this->session->userdata('logged_user')) {
			$session = $this->session->userdata('logged_user');
			$data['prefill']['name']  = isset($session['name']) ? $session['name'] : '';
			$data['prefill']['email'] = isset($session['email_id']) ? $session['email_id'] : '';
		}

		$this->load->view('header', $data);
		$this->load->view('service_details', $data);
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
			redirect($slug ? 'services/' . rawurlencode($slug) : 'services');
			return;
		}

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
			redirect($slug ? 'services/' . rawurlencode($slug) : 'services');
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
			redirect($slug ? 'services/' . rawurlencode($slug) : 'services');
			return;
		}

		// Best-effort notifications:
		//   (a) acknowledge to the submitter
		//   (b) alert MAIL_FROM_ADDRESS that a new request landed.
		$this->load->library('mailer');
		$svc_name = $service ? $service->name : 'general inquiry';
		$ack_body = '<p>Thanks for your message — we received your request about <strong>' . html_escape($svc_name) . '</strong> and will be in touch shortly.</p>';
		$this->mailer->send($this->input->post('email'), 'We received your Pearland411 services request', $ack_body);

		$admin_to = env('MAIL_FROM_ADDRESS', '');
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
			$this->mailer->send($admin_to, '[Pearland411] New service request — ' . $svc_name, $alert_body);
		}

		$this->session->set_flashdata('service_request_success', 'Thanks! We received your request and will be in touch shortly.');
		redirect($slug ? 'services/' . rawurlencode($slug) : 'services');
	}

	/*=========================
		Public coupons / deals
		==========================*/

	public function coupons()
	{
		$data = array();
		$data['coupons'] = $this->usermodel->getActiveCoupons(60);
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
		$data['coupon'] = $coupon;
		$this->loadHeaderCategories($data);
		$data['title']            = $coupon->coupon_name . ' — Coupon — Pearland411';
		$data['meta_description'] = 'Coupon code ' . $coupon->coupon_code . ' for ' . ($coupon->listing_name ?? $coupon->owner_name);
		$data['canonical_url']    = base_url('coupons/' . (int) $coupon->coupon_id);

		$this->load->view('header', $data);
		$this->load->view('coupon_details', $data);
		$this->load->view('footer');
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

			$admin_to = env('MAIL_FROM_ADDRESS', '');
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

		$this->load->view('header', $data);
		$this->load->view('claim_listing', $data);
		$this->load->view('footer');
	}
}
