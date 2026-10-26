<?php

/**
 * Usermodel
 *
 * Core application data model handling users, listings, plans, payments.
 *
 * Linter Note: Dynamically provided CI properties ($this->db, $this->session, etc.)
 * are injected by CodeIgniter's loader. We annotate them here so static analysis
 * tools stop flagging them as undefined.
 *
 * @property CI_DB_query_builder $db
 * @property CI_Session $session
 * @property CI_Form_validation $form_validation
 */



class Usermodel extends CI_Model
{

	function __construct()
	{
		parent::__construct();
		/*Validate Form*/
		$this->load->database();
		$this->load->helper(array('form', 'url'));
		$this->load->library('form_validation');
		$this->load->library('session');
		$this->load->helper('cookie');
	}

	/* =========================
	   Newsletter subscription helpers
	   ========================= */

	/**
	 * Check if an email is already subscribed to the newsletter
	 * @param string $email
	 * @return bool
	 */
	public function is_newsletter_email_subscribed($email)
	{
		if (empty($email)) return false;
		$this->db->from('newsletter_subscriptions');
		$this->db->where('email', $email);
		$query = $this->db->get();
		return ($query && $query->num_rows() > 0);
	}

	/**
	 * Add a newsletter subscription record (creates confirmation token)
	 * @param array $data ['email' => '', 'name' => '']
	 * @return array ['success' => bool, 'id' => int|null, 'token' => string|null]
	 */
	public function add_newsletter_subscription($data)
	{
		if (empty($data['email'])) return ['success' => false];

		$insert = array(
			'email' => $data['email'],
			'name' => isset($data['name']) ? $data['name'] : null,
			'confirmation_token' => bin2hex(random_bytes(16)),
			'is_confirmed' => 0,
			'ip_address' => $this->input->ip_address(),
			'user_agent' => $this->input->user_agent(),
			'created_at' => date('Y-m-d H:i:s')
		);

		$ok = $this->db->insert('newsletter_subscriptions', $insert);
		if ($ok) {
			return [
				'success' => true,
				'id' => $this->db->insert_id(),
				'token' => $insert['confirmation_token']
			];
		}
		return ['success' => false];
	}

	/**
	 * Confirm a newsletter subscription by token
	 * @param string $token
	 * @return bool
	 */
	public function confirm_newsletter_subscription($token)
	{
		if (empty($token)) return false;
		$this->db->where('confirmation_token', $token);
		$this->db->where('is_confirmed', 0);
		$update = array('is_confirmed' => 1, 'confirmation_token' => NULL, 'updated_at' => date('Y-m-d H:i:s'));
		return $this->db->update('newsletter_subscriptions', $update);
	}
	function logged_user_id()
	{
		$session = $this->session->userdata('logged_user');
		$logged_id = $session['logged_id'];
		return $logged_id;
	}

	/*=========================
	        Register
	        ==========================*/

	function registerUser()
	{
		$this->form_validation->set_rules('name', 'Name', 'trim|required|min_length[3]|max_length[100]');

		// A phone rule, not just a length. "aaaaaaaaaa" used to pass, because
		// min_length[10] is the only thing that was checked.
		$this->form_validation->set_rules(
			'mobile_number',
			'Phone',
			'trim|required|min_length[10]|max_length[20]|regex_match[/^[0-9+\-\s()]{10,20}$/]',
			array('regex_match' => 'Please enter a valid %s number.')
		);

		$this->form_validation->set_rules(
			'email_id',
			'Email',
			'trim|required|valid_email|max_length[190]|is_unique[user.email_id]',
			array(
				'required'      => 'You have not provided %s.',
				'is_unique'     => 'This %s already exists.'
			)
		);

		// Eight, matching what the form has always promised ("At least 8
		// characters"). The server accepted six, so the field's own placeholder
		// was wrong and "123456" was a valid password. Confirmation catches the
		// typo that otherwise locks someone out of the account they just made.
		$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[8]|max_length[128]');
		$this->form_validation->set_rules(
			'password_confirm',
			'Confirm password',
			'trim|required|matches[password]',
			array('matches' => 'The two passwords do not match.')
		);

		// The honeypot, name filter and reCAPTCHA are checked before this is
		// called — User::register() → spamRefusal().
		if ($this->form_validation->run()) {
			// Generate a verification token (stored raw because we email
			// it to the user; lookups are by indexed exact match).
			$verification_token = bin2hex(random_bytes(32));

			// Store raw values; escape on output via html_escape().
			// htmlentities() at write-time corrupts data on round-trip.
			$form_data = array(
				'name'                         => $this->input->post('name'),
				'email_id'                     => $this->input->post('email_id'),
				'password'                     => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
				'mobile_number'                => $this->input->post('mobile_number'),
				'hear_us'                      => $this->input->post('hear_us'),
				'sale_representative'          => $this->input->post('sale_representative'),
				'user_zip_code'                => $this->input->post('zip_code'),
				'email_verified'               => 0,
				'email_verification_token'     => $verification_token,
				'email_verification_sent_at'   => date('Y-m-d H:i:s'),
			);

			if (!$this->db->insert('user', $form_data)) {
				return array('status' => FALSE, 'referror' => '', 'message' => 'Something Wrong please try again!');
			}

			// Fire off the verification email. Account creation still
			// succeeds if it fails — but the caller is TOLD, because login is
			// blocked until the link is clicked and "account created, now sign
			// in" in front of a mail that never left is how a signup becomes a
			// support ticket instead of a customer.
			$sent = $this->sendVerificationEmail(
				$this->input->post('email_id'),
				$this->input->post('name'),
				$verification_token
			);

			return array('status' => TRUE, 'email_sent' => $sent);
		}

		return array('status' => FALSE, 'referror' => '');
	}

	/*=========================
		Email verification + password reset
		==========================*/

	/**
	 * Build and send the "confirm your email" message.
	 *
	 * Goes through Mailer::render() like everything else the site sends. It
	 * used to be a bare string of <p> tags handed straight to send(): no
	 * document, no branding, no plain-text part. That is both the least
	 * trustworthy-looking message a new customer receives and one of the
	 * easiest shapes for a spam filter to score down — on the one email they
	 * must act on before they can use the account at all.
	 *
	 * @return bool whether the mailer accepted it for delivery
	 */
	public function sendVerificationEmail($email, $name, $token)
	{
		$this->load->library('mailer');

		$verify_url = base_url('verify-email/' . $token);
		$greeting   = trim((string) $name) !== ''
			? 'Welcome to Pearland411, ' . html_escape(trim($name)) . '.'
			: 'Welcome to Pearland411.';

		$body = '<p>' . $greeting . '</p>'
			. '<p>Confirm this address to activate your account. You will not be able to '
			. 'sign in until you do.</p>'
			. '<p style="margin:24px 0;">'
			. '<a href="' . html_escape($verify_url) . '" '
			. 'style="display:inline-block; background:#79B82A; color:#ffffff; font-weight:700; '
			. 'text-decoration:none; padding:12px 24px; border-radius:6px;">Confirm my email</a>'
			. '</p>'
			. '<p style="font-size:13px; color:#5A6472;">If the button does not work, paste this '
			. 'into your browser:<br><a href="' . html_escape($verify_url) . '">'
			. html_escape($verify_url) . '</a></p>'
			. '<p style="font-size:13px; color:#5A6472;">If you did not create this account you '
			. 'can safely ignore this message.</p>';

		return $this->mailer->send(
			$email,
			'Confirm your Pearland411 account',
			$this->mailer->render('Confirm your email', $body, array(
				'preheader' => 'One click activates your Pearland411 account.',
			)),
			null,
			array('log_type' => 'verification')
		);
	}

	/**
	 * Re-send the verification link for an address that has not been confirmed.
	 *
	 * Always reports the same shape, for the same reason requestPasswordReset()
	 * does: the form must not tell a stranger whether an address is registered.
	 * A user whose first mail was lost previously had no way back in at all —
	 * the account existed, login refused it, and nothing on the site could
	 * issue a new link.
	 *
	 * Re-issues the token as well as the mail, so a link from an older message
	 * stops working once a new one is asked for.
	 *
	 * @return bool TRUE when a message was handed to the mailer.
	 */
	public function resendVerification($email)
	{
		$email = trim((string) $email);
		if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return false;
		}

		$user = $this->db
			->where('email_id', $email)
			->limit(1)
			->get('user')
			->row();

		// No such account, already confirmed, or banned: nothing to send, and
		// the caller shows the same neutral message either way.
		if (!$user || (int) $user->banned === 1) {
			return false;
		}
		if (property_exists($user, 'email_verified') && (int) $user->email_verified === 1) {
			return false;
		}

		// Same defensive check login() makes: on a database where migration
		// 0001 has not been run there is nowhere to store a token, and an
		// unguarded UPDATE would be a fatal on a public form.
		if (!$this->db->field_exists('email_verification_token', 'user')) {
			log_message('error', 'resendVerification: user.email_verification_token is missing — run migration 0001_auth_flows.sql.');
			return false;
		}

		$token = bin2hex(random_bytes(32));
		$this->db->where('user_id', $user->user_id)->update('user', array(
			'email_verification_token'   => $token,
			'email_verification_sent_at' => date('Y-m-d H:i:s'),
		));

		return $this->sendVerificationEmail($user->email_id, $user->name, $token);
	}

	/**
	 * Look up a user by their email-verification token and, if found,
	 * mark them verified. Returns true on success.
	 */
	public function verifyEmailByToken($token)
	{
		if (empty($token) || !ctype_xdigit($token)) {
			return false;
		}
		$user = $this->db
			->where('email_verification_token', $token)
			->limit(1)
			->get('user')
			->row();
		if (!$user) {
			return false;
		}
		$this->db->where('user_id', $user->user_id)->update('user', array(
			'email_verified'           => 1,
			'email_verified_at'        => date('Y-m-d H:i:s'),
			'email_verification_token' => null,
		));
		return true;
	}

	/**
	 * Create a password-reset record + email the user a tokenized link.
	 * Always returns the same shape so callers cannot enumerate which
	 * emails exist in the system.
	 */
	public function requestPasswordReset($email, $ip = null)
	{
		$user = $this->db
			->where('email_id', $email)
			->limit(1)
			->get('user')
			->row();
		if (!$user) {
			// Silent success — do not leak which emails are registered.
			return true;
		}

		$token = bin2hex(random_bytes(32));
		$token_hash = hash('sha256', $token);

		$this->db->insert('password_resets', array(
			'user_id'    => $user->user_id,
			'email'      => $user->email_id,
			'token_hash' => $token_hash,
			'created_at' => date('Y-m-d H:i:s'),
			'expires_at' => date('Y-m-d H:i:s', time() + 3600), // 1 hour
			'ip'         => $ip,
		));

		$this->load->library('mailer');
		$reset_url = base_url('reset-password/' . $token);
		$body = '<p>We received a request to reset the password on your Pearland411 account.</p>'
			. '<p style="margin:24px 0;">'
			. '<a href="' . html_escape($reset_url) . '" '
			. 'style="display:inline-block; background:#79B82A; color:#ffffff; font-weight:700; '
			. 'text-decoration:none; padding:12px 24px; border-radius:6px;">Choose a new password</a>'
			. '</p>'
			. '<p style="font-size:13px; color:#5A6472;">If the button does not work, paste this '
			. 'into your browser:<br><a href="' . html_escape($reset_url) . '">'
			. html_escape($reset_url) . '</a></p>'
			. '<p style="font-size:13px; color:#5A6472;">This link expires in 1 hour. If you did '
			. 'not request a reset you can ignore this email — your password will not change.</p>';

		$this->mailer->send(
			$user->email_id,
			'Reset your Pearland411 password',
			$this->mailer->render('Reset your password', $body, array(
				'preheader' => 'Your Pearland411 password reset link (valid for 1 hour).',
			)),
			null,
			array('log_type' => 'password_reset')
		);

		return true;
	}

	/**
	 * Look up an unused, unexpired reset row by token. Returns the
	 * password_resets row (object) or null.
	 */
	public function findActiveResetByToken($token)
	{
		if (empty($token) || !ctype_xdigit($token)) {
			return null;
		}
		$token_hash = hash('sha256', $token);
		$row = $this->db
			->where('token_hash', $token_hash)
			->where('used_at', null)
			->where('expires_at >=', date('Y-m-d H:i:s'))
			->limit(1)
			->get('password_resets')
			->row();
		return $row ?: null;
	}

	/**
	 * Apply a password reset: hash the new password, write it to user,
	 * mark the reset row used. Returns true on success.
	 */
	public function completePasswordReset($reset_row, $new_password)
	{
		if (!$reset_row || empty($reset_row->user_id)) {
			return false;
		}
		$this->db->trans_start();
		$this->db->where('user_id', $reset_row->user_id)->update('user', array(
			'password' => password_hash($new_password, PASSWORD_DEFAULT),
		));
		$this->db->where('id', $reset_row->id)->update('password_resets', array(
			'used_at' => date('Y-m-d H:i:s'),
		));
		$this->db->trans_complete();
		return $this->db->trans_status();
	}

	/*====================
		    Login
	=====================*/

	function login()
	{
		$this->form_validation->set_rules('email_id', 'Email Address', 'required|trim|valid_email');

		$this->form_validation->set_rules('password', 'Password', 'required');

		if (!$this->form_validation->run()) {
			return array('status' => FALSE, 'referror' => '', 'message' => 'Please enter valid email and password.');
		}

		$email_id = $this->input->post('email_id');
		$password = (string) $this->input->post('password');

		// Look up by email only — verify the password in PHP so timing
		// attacks see uniform behaviour and so we never interpolate
		// user input into SQL.
		$user = $this->db
			->where('email_id', $email_id)
			->limit(1)
			->get('user')
			->row();

		if (!$user) {
			return array('status' => FALSE, 'message' => 'Email or Password is incorrect.', 'url' => 'login');
		}

		$stored = (string) $user->password;
		$verified = false;

		// Modern bcrypt/argon hashes start with $2y$ / $argon...
		if (strlen($stored) > 0 && $stored[0] === '$') {
			$verified = password_verify($password, $stored);
		} elseif (strlen($stored) === 32 && ctype_xdigit($stored)) {
			// Legacy MD5 — accept once, then transparently upgrade to bcrypt.
			if (hash_equals(strtolower($stored), md5($password))) {
				$verified = true;
				$this->db
					->where('user_id', $user->user_id)
					->update('user', array(
						'password' => password_hash($password, PASSWORD_DEFAULT),
					));
			}
		}

		if (!$verified) {
			return array('status' => FALSE, 'message' => 'Email or Password is incorrect.', 'url' => 'login');
		}

		if ((int) $user->banned === 1) {
			return array('status' => FALSE, 'message' => 'Your account has been removed. Please contact Administrator.', 'url' => 'login');
		}

		// Block login until the email is verified. The email_verified
		// column is added by migration 0001 and back-filled to 1 for
		// existing rows, so this only affects NEW signups.
		// The `code` is what lets /login offer a "send it again" link instead
		// of a dead end. Until this existed, a user whose verification mail
		// never arrived — which, for a stretch of 2026, was all of them — was
		// told to check an inbox that had nothing in it, on every attempt,
		// with no other path offered anywhere on the site.
		if (property_exists($user, 'email_verified') && (int) $user->email_verified === 0) {
			return array(
				'status'  => FALSE,
				'code'    => 'email_unverified',
				'message' => 'Please confirm your email before signing in. Check your inbox for the verification link.',
				'url'     => 'login',
			);
		}

		$this->session->set_userdata('logged_user', array(
			'logged_id'     => $user->user_id,
			'name'          => $user->name,
			'email_id'      => $user->email_id,
			'profile_image' => $user->profile_image,
			'user_created'  => $user->user_created,
		));

		return array('status' => TRUE, 'url' => 'dashboard', 'redirect' => true);
	}

	/**
	 * Increment views for any table
	 * @param string $table Table name
	 * @param string $id_field Primary key field name
	 * @param int $id_value Primary key value
	 */
	public function incrementViews($table, $id_field, $id_value)
	{
		if (!$table || !$id_field || !$id_value) return false;
		$this->db->set('views', 'views+1', FALSE);
		$this->db->where($id_field, $id_value);
		return $this->db->update($table);
	}

	/*=======================
		Get Dashboard Info
	========================*/

	public function getDashboardCategories()
	{
		// return $data = $this->db->select('*')->from('listing_categories')->limit(8)->order_by('category_id','DESC')->get()->result();
		return $data = $this->db->select('listing_categories.category_name as cat_name, listing_categories.category_id as cat_id,count(listings.listing_id) as totalValue, listing_categories.category_image as cat_image')
			->from('listing_categories, listings')
			->where('listing_categories.category_id = listings.category_id')
			->limit(8)->group_by('listing_categories.category_id');

		/*return $data = $this->db->select('sub_cate.category_id, main_cate.category_name, sub_cate.sub_category_name, main_cate.category_id, sub_cate.created_at, sub_cate.sub_category_id')
	     ->from('listing_sub_categories as sub_cate')
	     ->join('listing_categories as main_cate', 'sub_cate.category_id = main_cate.category_id', 'LEFT')
	     ->get()
	     ->result();*/
	}

	public function getDashboardEvents()
	{
		return $data = $this->db->select('*')
			->from('events')
			->limit(8)
			->order_by('event_id', 'DESC')
			->get()->result();
	}

	public function getDashboardProducts()
	{
		return $data = $this->db->select('*')
			->from('products')
			->limit(5)
			->order_by('product_id', 'DESC')
			->get()->result();
	}

	/*=======================
		Get User Information
	========================*/

	public function getUserInformation()
	{
		$session = $this->session->userdata('logged_user');
		$logged_id = $session['logged_id'];

		$result = $this->db->select('*')
			->from('user')
			->where('user_id', $logged_id)
			->get()->row();
		return $result;
	}

	/*==========================
	Get All Product Information
	===========================*/

	// public function getAllProduct()
	// {
	// 	return $data = $this->db->get('products')->result();
	// }


	public function getAllProduct($limit = NULL, $offset = NULL)
	{
		$this->db->limit($limit, $offset);
		$query = $this->db->get('products');
		return $query->result();
	}
	// Count total number of products
	public function countAllProducts()
	{
		return $this->db->count_all('products');
	}

	/**
	 * Get filtered products dynamically by category, subcategory, price, and offer
	 * @param array $filters (category_id, sub_category_id, price_range, offer_range)
	 * @return array
	 */
	public function getFilteredProductsDynamic($filters = array(), $limit = null, $offset = null)
	{
		$this->db->reset_query();
		if (!empty($filters['category_slug'])) {
			$cat_id = $this->getProductCategoryIdBySlug($filters['category_slug']);
			if ($cat_id) {
				$this->db->where('category_id', $cat_id);
			}
		} elseif (!empty($filters['category_id'])) {
			$this->db->where('category_id', $filters['category_id']);
		}
		if (!empty($filters['sub_category_id'])) {
			if (is_array($filters['sub_category_id'])) {
				$this->db->where_in('sub_category_id', $filters['sub_category_id']);
			} else {
				$this->db->where('sub_category_id', $filters['sub_category_id']);
			}
		}
		if (!empty($filters['price_range']) && is_array($filters['price_range'])) {
			$min = $filters['price_range'][0];
			$max = $filters['price_range'][1];
			$this->db->where('product_price >=', $min);
			$this->db->where('product_price <=', $max);
		}
		if (!empty($filters['discount_range']) && is_array($filters['discount_range'])) {
			$min = $filters['discount_range'][0];
			$max = $filters['discount_range'][1];
			$this->db->where('product_price_offer >=', $min);
			$this->db->where('product_price_offer <=', $max);
		}
		$this->db->where('product_status', 1);
		$this->db->where('is_deleted', 0);
		if ($limit !== null) {
			$this->db->limit($limit, $offset);
		}
		$query = $this->db->get('products');
		$results = $query->result();
		if (empty($results)) {
			return array('not_found' => true, 'products' => array());
		}
		return array('not_found' => false, 'products' => $results);
	}

	// Count products for pagination with filters
	public function countProductsWithFilters($filters = array())
	{
		$this->db->reset_query();
		if (!empty($filters['category_slug'])) {
			$cat_id = $this->getProductCategoryIdBySlug($filters['category_slug']);
			if ($cat_id) {
				$this->db->where('category_id', $cat_id);
			}
		} elseif (!empty($filters['category_id'])) {
			$this->db->where('category_id', $filters['category_id']);
		}
		if (!empty($filters['sub_category_id'])) {
			if (is_array($filters['sub_category_id'])) {
				$this->db->where_in('sub_category_id', $filters['sub_category_id']);
			} else {
				$this->db->where('sub_category_id', $filters['sub_category_id']);
			}
		}
		if (!empty($filters['price_range']) && is_array($filters['price_range'])) {
			$min = $filters['price_range'][0];
			$max = $filters['price_range'][1];
			$this->db->where('product_price >=', $min);
			$this->db->where('product_price <=', $max);
		}
		if (!empty($filters['discount_range']) && is_array($filters['discount_range'])) {
			$min = $filters['discount_range'][0];
			$max = $filters['discount_range'][1];
			$this->db->where('product_price_offer >=', $min);
			$this->db->where('product_price_offer <=', $max);
		}
		$this->db->where('product_status', 1);
		$this->db->where('is_deleted', 0);
		return $this->db->count_all_results('products');
	}

	// Get category_id by slug (or slugify name if not found)
	public function getProductCategoryIdBySlug($slugOrName)
	{
		$this->db->select('product_categories.category_id');
		$this->db->from('product_categories');
		$this->db->where('product_categories.category_slug', $slugOrName);
		$row = $this->db->get()->row();
		if ($row) return $row->category_id;
		// If not found, try slugify name
		$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $slugOrName)));
		$this->db->select('product_categories.category_id');
		$this->db->from('product_categories');
		$this->db->where('product_categories.category_slug', $slug);
		$row2 = $this->db->get()->row();
		if ($row2) return $row2->category_id;
		// Try by name
		$this->db->select('product_categories.category_id');
		$this->db->from('product_categories');
		$this->db->where('product_categories.category_name', $slugOrName);
		$row3 = $this->db->get()->row();
		if ($row3) return $row3->category_id;
		return null;
	}

	/**
	 * Get all product subcategories, optionally filtered by category
	 */
	// Accepts category_slug or category_id
	public function getAllSubCategories($category = null)
	{
		$this->db->from('product_sub_categories');
		if ($category) {
			if (!is_numeric($category)) {
				$category_id = $this->getCategoryIdBySlug($category);
			} else {
				$category_id = $category;
			}
			if ($category_id === null) {
				// No valid category found, return empty result
				return array();
			}
			$this->db->where('category_id', $category_id);
		}
		$query = $this->db->get();
		return $query->result();
	}


	/*==========================
	Get All Product Information
	===========================*/
	// Get all product categories (with slug)
	public function getAllCategory()
	{
		return $this->db->get('product_categories')->result();
	}

	/*==========================
	Get Categories with Pagination
	===========================*/
	public function getAllCategoryWithPagination($limit, $offset)
	{
		$this->db->limit($limit, $offset);
		$this->db->order_by('category_id', 'DESC');
		$query = $this->db->get('listing_categories');
		return $query->result();
	}

	/*==========================
	Count All Categories
	===========================*/
	public function countAllCategories()
	{
		return $this->db->count_all('listing_categories');
	}

	/*==========================
	Get Categories with Limit for Headers/Modals
	===========================*/
	public function getCategoriesForHeader($limit = 50)
	{
		return $this->db->select('category_id, category_name, category_slug, category_image')
			->from('listing_categories')
			// ->where('category_status', 1) // Only active categories
			->order_by('category_name', 'ASC')
			->limit($limit)
			->get()->result();
	}


	public function getRandomTenCategory($limit = 50)
	{
		return $this->db->select('category_id, category_name, category_slug, category_image')
			->from('listing_categories')
			// ->where('category_status', 1) // Uncomment if you want only active ones
			->order_by('RAND()') // 👈 Randomize results directly from database
			->limit($limit)
			->get()
			->result();
	}





	/*==========================
		Get All Pricing Plans
	===========================*/
	public function getAllPlans()
	{
		return $this->db->order_by('sort_order', 'ASC')->get('plans')->result();
	}

	/*==========================
		Get Plan By ID
	===========================*/
	public function getPlanById($plan_id)
	{
		return $this->db->where('id', $plan_id)->get('plans')->row();
	}

	/*==========================
		Subscription Management
	===========================*/
	public function createUserSubscription($subscription_data)
	{
		$session = $this->session->userdata('logged_user');
		$logged_id = $session['logged_id'];

		// Insert into subscriptions table
		$subscription_record = array(
			'user_id' => $logged_id,
			'plan_id' => $subscription_data['plan_id'],
			'status' => 'active',
			'start_date' => date('Y-m-d H:i:s'),
			'end_date' => $subscription_data['end_date'], // Calculate based on billing cycle
			'next_billing_date' => $subscription_data['next_billing_date'],
			'canceled_at' => NULL,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
			'payment_status' => 'pending',
			'total_amount' => $subscription_data['amount'],
			'currency' => 'USD',
			'billing_cycle' => $subscription_data['billing_cycle'],
			'auto_renew' => 1
		);

		if ($this->db->insert('subscriptions', $subscription_record)) {
			$local_subscription_id = $this->db->insert_id();

			// Insert initial transaction
			$transaction_data = array(
				'user_id' => $logged_id,
				'plan_id' => $subscription_data['plan_id'],
				'subscription_id' => isset($subscription_data['subscription_id']) ? $subscription_data['subscription_id'] : NULL,
				'txn_ref' => 'SUB_' . $local_subscription_id . '_' . time(),
				'amount' => $subscription_data['amount'],
				'currency' => 'USD',
				'method' => 'card',
				'status' => 'success', // Since Authorize.Net confirmed
				'response_payload' => json_encode($subscription_data),
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s'),
				'auth_net_transaction_id' => isset($subscription_data['auth_net_transaction_id']) ? $subscription_data['auth_net_transaction_id'] : NULL,
				'auth_net_response_code' => isset($subscription_data['response_code']) ? $subscription_data['response_code'] : NULL,
				'auth_net_auth_code' => isset($subscription_data['auth_code']) ? $subscription_data['auth_code'] : NULL,
				'gateway_fees' => 0.00
			);

			$this->db->insert('transactions', $transaction_data);

			// Store payment card info if provided
			if (isset($subscription_data['card_info'])) {
				$this->storePaymentCard($logged_id, $subscription_data['card_info'], isset($subscription_data['auth_net_payment_profile_id']) ? $subscription_data['auth_net_payment_profile_id'] : null);
			}

			// Send subscription confirmation email
			$this->send_subscription_email($subscription_data, 'created');

			return $local_subscription_id;
		}
		return false;
	}

	public function storePaymentCard($user_id, $card_info, $auth_net_profile_id = null)
	{
		// Check if card already exists (by last 4 digits)
		$existing_card = $this->db->where('user_id', $user_id)
			->where('card_last4', $card_info['card_last4'])
			->get('payment_cards')->row();

		if ($existing_card) {
			// Update existing card
			$update_data = array(
				'exp_month' => $card_info['exp_month'],
				'exp_year' => $card_info['exp_year'],
				'is_default' => 1,
				'updated_at' => date('Y-m-d H:i:s')
			);

			if ($auth_net_profile_id) {
				$update_data['auth_net_payment_profile_id'] = $auth_net_profile_id;
			}

			$this->db->where('id', $existing_card->id)->update('payment_cards', $update_data);
			return $existing_card->id;
		} else {
			// Insert new card
			$card_data = array(
				'user_id' => $user_id,
				'card_holder_name' => $card_info['card_holder_name'],
				'card_brand' => $card_info['card_brand'], // Visa, MasterCard, etc.
				'card_last4' => $card_info['card_last4'],
				'exp_month' => $card_info['exp_month'],
				'exp_year' => $card_info['exp_year'],
				'card_fingerprint' => isset($card_info['card_fingerprint']) ? $card_info['card_fingerprint'] : null,
				'pan_encrypted' => null, // We don't store full card numbers
				'is_default' => 1,
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s'),
				'auth_net_payment_profile_id' => $auth_net_profile_id
			);

			if ($this->db->insert('payment_cards', $card_data)) {
				return $this->db->insert_id();
			}
		}
		return false;
	}

	// Duplicate getUserSubscription() method removed - using comprehensive version later in file

	// Duplicate getSubscriptionLimits() method removed - using comprehensive version later in file

	public function updateSubscriptionStatus($auth_net_subscription_id, $status, $transaction_id = null)
	{
		$update_data = array(
			'status' => $status,
			'updated_at' => date('Y-m-d H:i:s')
		);

		if ($status == 'active') {
			// Calculate next billing date based on billing cycle
			$subscription = $this->db->where('subscription_id', $auth_net_subscription_id)
				->get('subscriptions')->row();

			if ($subscription) {
				$next_billing = ($subscription->billing_cycle == 'monthly') ? '+1 month' : '+1 year';
				$update_data['next_billing_date'] = date('Y-m-d H:i:s', strtotime($next_billing));
				$update_data['payment_status'] = 'pending';
			}
		}

		$updated = $this->db->where('subscription_id', $auth_net_subscription_id)
			->update('subscriptions', $update_data);

		// Insert renewal transaction if this is a payment
		if ($updated && $transaction_id && $status == 'active') {
			$subscription = $this->db->where('subscription_id', $auth_net_subscription_id)
				->get('subscriptions')->row();

			if ($subscription) {
				$transaction_record = array(
					'user_id' => $subscription->user_id,
					'plan_id' => $subscription->plan_id,
					'subscription_id' => $auth_net_subscription_id,
					'txn_ref' => 'RENEWAL_' . $subscription->id . '_' . time(),
					'amount' => $subscription->total_amount,
					'currency' => $subscription->currency,
					'method' => 'card',
					'status' => 'success',
					'response_payload' => json_encode(array('type' => 'auto_renewal', 'transaction_id' => $transaction_id)),
					'created_at' => date('Y-m-d H:i:s'),
					'updated_at' => date('Y-m-d H:i:s'),
					'auth_net_transaction_id' => $transaction_id,
					'gateway_fees' => 0.00
				);

				$this->db->insert('transactions', $transaction_record);
			}
		}

		return $updated;
	}

	public function cancelUserSubscription($user_id, $auth_net_subscription_id)
	{
		$data = array(
			'status' => 'cancelled',
			'canceled_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
			'auto_renew' => 0
		);

		return $this->db->where('user_id', $user_id)
			->where('subscription_id', $auth_net_subscription_id)
			->update('subscriptions', $data);
	}

	public function getUserTransactions($user_id, $limit = 10)
	{
		return $this->db->select('t.*, p.name as plan_name')
			->from('transactions t')
			->join('plans p', 't.plan_id = p.id', 'left')
			->where('t.user_id', $user_id)
			->order_by('t.created_at', 'DESC')
			->limit($limit)
			->get()->result();
	}

	public function getUserPaymentCards($user_id)
	{
		return $this->db->where('user_id', $user_id)
			->order_by('is_default', 'DESC')
			->order_by('created_at', 'DESC')
			->get('payment_cards')->result();
	}

	/**
	 * Get user info by ID for auto-fill checkout
	 */
	public function getUserById($user_id)
	{
		return $this->db->select('user_id, name, email_id, mobile_number, user_address as address, user_state as state, user_zip_code as zip')
			->where('user_id', $user_id)
			->get('user')->row();
	}

	/*==========================
		Email Functions
	===========================*/
	/**
	 * Subscription lifecycle mail.
	 *
	 * Goes through the Mailer library like everything else. It used to
	 * configure CI's email class itself and send `From: noreply@yoursite.com
	 * ("Your Site Name")` — the placeholder that shipped with the theme. A
	 * domain the site does not own fails SPF at every recipient, so these
	 * receipts were being discarded or spam-foldered on arrival, and the ones
	 * that did land looked like a phishing attempt for a payment the customer
	 * had just made.
	 */
	public function send_subscription_email($subscription_data, $type = 'created')
	{
		$this->load->library('mailer');

		$billing_info = $subscription_data['billing_info'];
		$plan_name = isset($subscription_data['plan_name']) ? $subscription_data['plan_name'] : 'Selected Plan';

		$subject = '';
		$message = '';

		switch ($type) {
			case 'created':
				$subject = 'Subscription Activated - ' . $plan_name;
				$message = $this->build_subscription_created_email($subscription_data);
				break;
			case 'renewed':
				$subject = 'Subscription Renewed - ' . $plan_name;
				$message = $this->build_subscription_renewed_email($subscription_data);
				break;
			case 'cancelled':
				$subject = 'Subscription Cancelled - ' . $plan_name;
				$message = $this->build_subscription_cancelled_email($subscription_data);
				break;
		}

		if ($subject === '' || empty($billing_info['email'])) {
			return false;
		}

		return $this->mailer->send(
			$billing_info['email'],
			$subject,
			$message,
			null,
			array('log_type' => 'subscription_' . $type)
		);
	}

	private function build_subscription_created_email($data)
	{
		$billing = $data['billing_info'];
		$plan_name = isset($data['plan_name']) ? $data['plan_name'] : 'Selected Plan';

		$html = '
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="utf-8">
			<title>Subscription Activated</title>
			<style>
				body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
				.container { max-width: 600px; margin: 0 auto; padding: 20px; }
				.header { background: #7bb73f; color: white; padding: 20px; text-align: center; }
				.content { padding: 20px; background: #f9f9f9; }
				.footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
				.details { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
			</style>
		</head>
		<body>
			<div class="container">
				<div class="header">
					<h1>🎉 Subscription Activated!</h1>
				</div>
				<div class="content">
					<h2>Hello ' . htmlspecialchars($billing['first_name'] . ' ' . $billing['last_name']) . ',</h2>
					<p>Your subscription has been successfully activated! Thank you for choosing our service.</p>
					
					<div class="details">
						<h3>Subscription Details:</h3>
						<p><strong>Plan:</strong> ' . htmlspecialchars($plan_name) . '</p>
						<p><strong>Amount:</strong> $' . number_format($data['amount'], 2) . '</p>
						<p><strong>Billing Cycle:</strong> ' . ucfirst($data['billing_cycle']) . '</p>
						<p><strong>Start Date:</strong> ' . date('M d, Y') . '</p>
						<p><strong>Next Billing:</strong> ' . date('M d, Y', strtotime($data['next_billing_date'])) . '</p>
					</div>

					<div class="details">
						<h3>Transaction Information:</h3>
						<p><strong>Subscription ID:</strong> ' . htmlspecialchars($data['subscription_id']) . '</p>
						<p><strong>Transaction ID:</strong> ' . htmlspecialchars(isset($data['transaction_id']) ? $data['transaction_id'] : 'N/A') . '</p>
					</div>

					<p>You can manage your subscription anytime from your dashboard.</p>
					<p><a href="' . base_url('user/dashboard') . '" style="background: #7bb73f; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Go to Dashboard</a></p>
				</div>
				<div class="footer">
					<p>This is an automated message. Please do not reply to this email.</p>
				</div>
			</div>
		</body>
		</html>';

		return $html;
	}

	private function build_subscription_renewed_email($data)
	{
		$billing = $data['billing_info'];
		$plan_name = isset($data['plan_name']) ? $data['plan_name'] : 'Selected Plan';

		$html = '
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="utf-8">
			<title>Subscription Renewed</title>
			<style>
				body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
				.container { max-width: 600px; margin: 0 auto; padding: 20px; }
				.header { background: #28a745; color: white; padding: 20px; text-align: center; }
				.content { padding: 20px; background: #f9f9f9; }
				.footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
				.details { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
			</style>
		</head>
		<body>
			<div class="container">
				<div class="header">
					<h1>✅ Subscription Renewed</h1>
				</div>
				<div class="content">
					<h2>Hello ' . htmlspecialchars($billing['first_name'] . ' ' . $billing['last_name']) . ',</h2>
					<p>Your subscription has been automatically renewed. Thank you for continuing with our service!</p>
					
					<div class="details">
						<h3>Renewal Details:</h3>
						<p><strong>Plan:</strong> ' . htmlspecialchars($plan_name) . '</p>
						<p><strong>Amount Charged:</strong> $' . number_format($data['amount'], 2) . '</p>
						<p><strong>Renewal Date:</strong> ' . date('M d, Y') . '</p>
						<p><strong>Next Billing:</strong> ' . date('M d, Y', strtotime($data['next_billing_date'])) . '</p>
					</div>

					<div class="details">
						<h3>Transaction Information:</h3>
						<p><strong>Subscription ID:</strong> ' . htmlspecialchars($data['subscription_id']) . '</p>
						<p><strong>Transaction ID:</strong> ' . htmlspecialchars($data['transaction_id']) . '</p>
					</div>

					<p>Your subscription is now active for another billing period.</p>
					<p><a href="' . base_url('user/my_transactions') . '" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">View Transactions</a></p>
				</div>
				<div class="footer">
					<p>This is an automated message. Please do not reply to this email.</p>
				</div>
			</div>
		</body>
		</html>';

		return $html;
	}

	private function build_subscription_cancelled_email($data)
	{
		$billing = $data['billing_info'];
		$plan_name = isset($data['plan_name']) ? $data['plan_name'] : 'Selected Plan';

		$html = '
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="utf-8">
			<title>Subscription Cancelled</title>
			<style>
				body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
				.container { max-width: 600px; margin: 0 auto; padding: 20px; }
				.header { background: #dc3545; color: white; padding: 20px; text-align: center; }
				.content { padding: 20px; background: #f9f9f9; }
				.footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
				.details { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
			</style>
		</head>
		<body>
			<div class="container">
				<div class="header">
					<h1>Subscription Cancelled</h1>
				</div>
				<div class="content">
					<h2>Hello ' . htmlspecialchars($billing['first_name'] . ' ' . $billing['last_name']) . ',</h2>
					<p>Your subscription has been cancelled as requested.</p>
					
					<div class="details">
						<h3>Cancelled Subscription:</h3>
						<p><strong>Plan:</strong> ' . htmlspecialchars($plan_name) . '</p>
						<p><strong>Subscription ID:</strong> ' . htmlspecialchars($data['subscription_id']) . '</p>
						<p><strong>Cancelled Date:</strong> ' . date('M d, Y') . '</p>
					</div>

					<p>Thank you for using our service. You can resubscribe anytime from your dashboard.</p>
					<p><a href="' . base_url('user/plans') . '" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">View Plans</a></p>
				</div>
				<div class="footer">
					<p>This is an automated message. Please do not reply to this email.</p>
				</div>
			</div>
		</body>
		</html>';

		return $html;
	}

	/**
	 * Get related blogs (excluding the current blog)
	 * @param int $exclude_blog_id
	 * @param int $limit
	 * @return array
	 */
	public function getRelatedBlogs($exclude_blog_id, $limit = 3)
	{
		$this->db->where('blog_status', 1);
		$this->db->where('blog_id !=', $exclude_blog_id);
		$this->db->order_by('created_at', 'DESC');
		$this->db->limit($limit);
		$query = $this->db->get('blogs');
		return $query->result();
	}

	/*==========================
		Get All Blog Information
	===========================*/
	public function getAllBlog()
	{
		// Published only. /all-blog listed unpublished drafts when browsing,
		// and searchBlogs() now filters them, so without this the same page
		// would show a draft to a browser and hide it from a searcher.
		return $data = $this->db->where('blog_status', 1)->get('blogs')->result();
	}
	public function countAllBlog()
	{
		return $this->db->count_all('blogs');
	}

	public function getAllBlogs($user_id = NULL, $limit = NULL, $offset = NULL)
	{
		return $data = $this->db->select('*')->from('blogs')
			->where('user_id', $user_id)
			// ->where('blog_status',1)
			->order_by('blog_id', 'DESC')
			->limit($limit, $offset)
			->get()->result();
	}

	/*==========================
		Get All Event Information
	===========================*/
	public function getAllEvent($user_id = NULL, $limit = NULL, $offset = NULL)
	{
		return $this->db->select('*')
			->from('events')
			->where('user_id', $user_id)
			->order_by('event_id', 'DESC')
			->limit($limit, $offset)
			->get()
			->result();
	}


	/*==========================
	  Get All Event Information
	===========================*/
	// Get events with search and pagination (public)
	/**
	 * @param string $search
	 * @param int    $limit
	 * @param int    $offset
	 * @param string $when  'upcoming' | 'past' | 'all'. A listing of events
	 *                      sorted newest-date-first shows next year's fair
	 *                      above tonight's concert and mixes in everything
	 *                      that has already happened, so /event asks for a
	 *                      window and the sort follows it: soonest first
	 *                      when looking forward, most recent first when
	 *                      looking back.
	 */
	public function getEventsWithPagination($search = '', $limit = 10, $offset = 0, $when = 'all')
	{
		$this->db->select('events.*, COALESCE(admin.admin_name, user.name) AS host_name, user.profile_image AS host_image', false)
			->from('events')
			->join('user', 'events.user_id = user.user_id', 'LEFT')
			->join('admin', 'admin.admin_id = events.admin_id', 'LEFT')
			->where('events.event_status', 1);
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('events.event_name', $search);
			$this->db->or_like('events.event_description', $search);
			$this->db->or_like('events.event_address', $search);
			$this->db->group_end();
		}

		// Bound as a plain value rather than CURDATE(), so CI3 escapes it.
		$today = date('Y-m-d');
		if ($when === 'upcoming')  $this->db->where('events.event_start_date >=', $today);
		elseif ($when === 'past')  $this->db->where('events.event_start_date <', $today);

		// Plain identifiers so CI3 can escape them safely on its own — no
		// raw expressions. event_id breaks ties for events with no date.
		$this->db->order_by('events.event_start_date', $when === 'upcoming' ? 'ASC' : 'DESC');
		$this->db->order_by('events.event_id', $when === 'upcoming' ? 'ASC' : 'DESC');
		$this->db->limit($limit, $offset);
		return $this->db->get()->result();
	}

	/** Must apply exactly the filters getEventsWithPagination() applies. */
	public function countEventsWithSearch($search = '', $when = 'all')
	{
		$this->db->from('events')->where('event_status', 1);
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('event_name', $search);
			$this->db->or_like('event_description', $search);
			$this->db->or_like('event_address', $search);
			$this->db->group_end();
		}

		$today = date('Y-m-d');
		if ($when === 'upcoming')  $this->db->where('event_start_date >=', $today);
		elseif ($when === 'past')  $this->db->where('event_start_date <', $today);

		return $this->db->count_all_results();
	}

	/*==========================
		Add Event Information
	===========================*/
	public function addEvents($data)
	{
		$session = $this->session->userdata('logged_user');
		$logged_id = $session['logged_id'];

		$data['user_id'] = $logged_id;

		if ($data['isenquiry'] == null) {
			$data['isenquiry'] = 0;
		}

		if ($this->db->insert('events', $data)) {
			return true;
		} else {
			return false;
		}
	}

	/*==========================
	  Delete Event Information
	===========================*/
	public function deleteUserEvent($slug)
	{
		$data = $this->db->select('*')->from('events')->where('event_slug', $slug)->get()->row();

		$img = '';

		if ($data->event_image) {
			$image_link = './' . $data->event_image;

			if (file_exists($image_link)) {
				$img = $image_link;
			} else {
				$img = '';
			}
		}

		if ($this->db->where('event_slug', $slug)->delete('events')) {
			if ($img != '') {
				unlink($img);
				return true;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	/*==========================
	  Update Event Information
	===========================*/
	public function updateEvent($event_slug, $data)
	{
		$this->db->where('event_slug', $event_slug);
		if ($this->db->update('events', $data)) {
			return true;
		} else {
			return false;
		}
	}

	/*====================
      LISTENING FUNCTION
    ====================*/

	public function addListing($data)
	{
		$session = $this->session->userdata('logged_user');
		$logged_id = $session['logged_id'];
		$data['user_id'] = $logged_id;

		if ($this->db->insert('listings', $data)) {
			return true;
		} else {
			return false;
		}
	}

	public function getAllListings()
	{
		return $data = $this->db->select('*')->from('listings')
			->where('listing_status', 1)
			->where('is_deleted', 0)
			->get()->result();
	}


	public function getRandFiveFeatureListing()
	{
		return $data = $this->db->select('*')->from('listings')
			->where('listing_status', 1)
			->where('is_deleted', 0)
			->where('featured_listing', 1)
			->limit(3)
			->order_by('rand()')
			->get()->result();
	}


	public function getListingsList()

	{

		return $data = $this->db->select('listing_id,listing_name')->from('listings')->where('is_deleted', 0)->get()->result_array();
	}



	public function getListingSingleValue($id, $table_id, $tableName)

	{

		return $data = $this->db->select('*')->from($tableName)->where($table_id, $id)->get()->result_array();
	}



	// public function addListing($tableName, $data)

	// {

	// 	$session = $this->session->userdata('logged_user');

	// 	$logged_id = $session['logged_id'];

	//     $data['user_id'] = $logged_id;



	//     if($this->db->insert($tableName,$data))

	//     {

	//     	$inserted_id =$this->db->insert_id();

	// 		if($inserted_id > 0){

	// 			return $inserted_id;

	// 		} else {

	// 			return 0;

	// 		}  

	//     } else {

	//         return 0;

	//     }   	

	// }



	public function addDuplicateListing($tableName, $data)

	{



		$session = $this->session->userdata('logged_user');

		$logged_id = $session['logged_id'];

		$data['user_id'] = $logged_id;



		if ($this->db->insert($tableName, $data)) {

			return TRUE;
		} else {

			return FALSE;
		}
	}



	/**
	 * THE definition of "a listing the public may see".
	 *
	 * `listing_status = 1` means published and `is_deleted = 0` means not
	 * binned. Both halves have to be applied together, everywhere, or a count
	 * and the list it labels can disagree — which is exactly what happened:
	 * getCategoriesWithCounts() filtered only is_deleted and reported 255 for
	 * a category whose page, filtering listing_status too, showed 13.
	 *
	 * Route every public listing query through this rather than hand-rolling
	 * the pair, so they cannot drift apart again.
	 *
	 * Note the convention: 1 = published. The CSV import and the add-listing
	 * form used to write 0 (the form wrote the *string* 'Active', which MySQL
	 * coerced to 0), while the admin panel, both dashboards and the sitemap
	 * all read 1. Migration 0020 normalised the data; both write paths are
	 * fixed in Admin::import_listings() and User::add_listing_scratch().
	 *
	 * @param string $alias table name or alias the caller used for `listings`
	 */
	private function applyPublishedListingFilter($alias = 'listings')
	{
		$this->db
			->where($alias . '.is_deleted', 0)
			->where($alias . '.listing_status', 1);
	}

	/**
	 * The same predicate as SQL text, for use inside a JOIN ... ON clause.
	 *
	 * Counting queries LEFT JOIN `listings` onto `listing_categories`; putting
	 * the predicate in WHERE instead would silently turn that into an INNER
	 * join and drop empty categories. Same rule, different position.
	 *
	 * @param string $alias table name or alias the caller used for `listings`
	 */
	private function publishedListingJoinCondition($alias = 'listings')
	{
		return '(' . $alias . '.is_deleted IS NULL OR ' . $alias . '.is_deleted = 0)'
			. ' AND ' . $alias . '.listing_status = 1';
	}

	/** table => (slug => category_id), built once per request by resolveCategoryIdBySlug(). */
	private $category_slug_map = array();

	/**
	 * Turn a category slug from a URL into a category_id.
	 *
	 * Tries the stored `category_slug` first — that is the normal case and a
	 * single indexed lookup. Categories created through the admin panel never
	 * had a slug written, though, so those fall through to matching on the
	 * name, and the match is done by running pearland_slug() over the names in
	 * PHP rather than by rebuilding the slug rule in SQL.
	 *
	 * That is the point: the links are built with pearland_slug(), so
	 * resolving them with the same function means the two can never disagree.
	 * The SQL this replaces compared against LOWER(REPLACE(name, ' ', '-')),
	 * which leaves punctuation in — so "Medical & Dental" was linked as
	 * medical-dental and looked up as medical-&-dental, and the page came back
	 * empty however many listings the category held. Migration 0020 backfills
	 * the missing slugs; this keeps working for any that get missed.
	 *
	 * Uses $this->db->query() rather than the query builder on purpose: the
	 * builder keeps one shared accumulator per request and callers are
	 * mid-way through composing a query when this runs.
	 *
	 * @param  string   $slug
	 * @param  string   $table category table to look in
	 * @return int|null category_id, or NULL when nothing matches
	 */
	private function resolveCategoryIdBySlug($slug, $table = 'listing_categories')
	{
		$slug  = trim((string) $slug);
		$table = preg_replace('/[^A-Za-z0-9_]/', '', (string) $table);
		if ($slug === '' || $table === '') return NULL;

		// Autoloaded, but the CLI controller can reach this model without it.
		if (!function_exists('pearland_slug')) $this->load->helper('view');

		$row = $this->db
			->query('SELECT category_id FROM `' . $table . '` WHERE `category_slug` = ? LIMIT 1', array($slug))
			->row();
		if ($row) return (int) $row->category_id;

		if (!isset($this->category_slug_map[$table])) {
			$map  = array();
			$rows = $this->db->query('SELECT category_id, category_name FROM `' . $table . '`')->result();
			foreach ($rows as $c) {
				$key = pearland_slug($c->category_name);
				// First one wins, so two categories that slugify the same way
				// resolve consistently instead of by row order.
				if ($key !== '' && !isset($map[$key])) {
					$map[$key] = (int) $c->category_id;
				}
			}
			$this->category_slug_map[$table] = $map;
		}

		$key = pearland_slug($slug);

		return isset($this->category_slug_map[$table][$key])
			? $this->category_slug_map[$table][$key]
			: NULL;
	}

	/**
	 * Shared FROM + WHERE for the public directory (/all-listing), so the
	 * page's list and its count can never disagree about what is visible.
	 *
	 * The join onto `user` is a LEFT join deliberately: admin-imported
	 * listings carry user_id = 0 (nobody has claimed them yet) and an INNER
	 * join dropped every one of them, which is why a directory with thousands
	 * of businesses could render "No Listings Found".
	 */
	private function directoryQuery($cat_identifier = NULL)
	{
		$this->db->from('listings')
			->join('user', 'listings.user_id = user.user_id', 'LEFT')
			->join('listing_categories', 'listings.category_id = listing_categories.category_id', 'LEFT');

		// Only filter by category if cat_identifier is provided
		if ($cat_identifier !== NULL && $cat_identifier !== '') {
			// Check if it's numeric (ID) or string (slug)
			if (is_numeric($cat_identifier)) {
				$this->db->where('listing_categories.category_id', (int) $cat_identifier);
			} else {
				$cat_id = $this->resolveCategoryIdBySlug($cat_identifier);
				if ($cat_id === NULL) {
					// Slug matches no category. Show an empty directory rather
					// than every listing on the site.
					$this->db->where('1 = 0', NULL, FALSE);
				} else {
					$this->db->where('listing_categories.category_id', $cat_id);
				}
			}
		}

		$this->applyPublishedListingFilter('listings');
	}

	/**
	 * One page of directory results.
	 *
	 * Featured and spotlight listings are INCLUDED and pinned to the top of
	 * the first page — that placement is what the Featured Listing package
	 * sells. They used to be excluded outright, so a paying customer's
	 * listing disappeared from the very page people browse.
	 *
	 * @param string|int|null $cat_identifier category slug or id
	 * @param int|null        $limit
	 * @param int|null        $offset
	 * @param string          $sort  featured|newest|name|rating
	 */
	public function getListingByCategory($cat_identifier = NULL, $limit = NULL, $offset = NULL, $sort = 'featured')
	{
		$this->db->select('listings.*,user.name,listing_categories.category_name,listing_categories.category_slug');
		$this->directoryQuery($cat_identifier);

		switch ($sort) {
			case 'name':
				$this->db->order_by('listings.listing_name ASC');
				break;
			case 'rating':
				// Only order by rating when the column is actually there;
				// older installs predate the review system.
				if ($this->db->field_exists('average_rating', 'listings')) {
					$this->db->order_by('listings.average_rating DESC, listings.total_reviews DESC');
				}
				$this->db->order_by('listings.listing_id DESC');
				break;
			case 'newest':
				$this->db->order_by('listings.created_at DESC, listings.listing_id DESC');
				break;
			case 'featured':
			default:
				$this->db->order_by('COALESCE(listings.featured_listing, 0) DESC, COALESCE(listings.spotlight_listing, 0) DESC, listings.created_at DESC, listings.listing_id DESC', '', false);
		}

		return $this->db->limit($limit, $offset)->get()->result();
	}

	public function countListingsByCategory($cat_identifier = NULL)
	{
		$this->directoryQuery($cat_identifier);

		return $this->db->count_all_results();
	}

	/**
	 * Featured businesses for the directory's sidebar rail. Prefers ones in
	 * the category being browsed and tops up with site-wide featured
	 * listings, so the rail is never empty on a thin category.
	 */
	public function getFeaturedListingsForDirectory($cat_identifier = NULL, $limit = 3)
	{
		$limit = max(1, (int) $limit);
		$out   = array();
		$seen  = array();

		$collect = function ($rows) use (&$out, &$seen, $limit) {
			foreach ($rows as $row) {
				if (count($out) >= $limit) return;
				if (isset($seen[$row->listing_id])) continue;
				$seen[$row->listing_id] = true;
				$out[] = $row;
			}
		};

		if ($cat_identifier !== NULL && $cat_identifier !== '') {
			$this->db->select('listings.*, listing_categories.category_name, listing_categories.category_slug');
			$this->directoryQuery($cat_identifier);
			$collect(
				$this->db
					->where('(listings.featured_listing = 1 OR listings.spotlight_listing = 1)')
					->order_by('RAND()', '', false)
					->limit($limit)
					->get()->result()
			);
		}

		if (count($out) < $limit) {
			$this->db->select('listings.*, listing_categories.category_name, listing_categories.category_slug');
			$this->directoryQuery(NULL);
			$collect(
				$this->db
					->where('(listings.featured_listing = 1 OR listings.spotlight_listing = 1)')
					->order_by('RAND()', '', false)
					->limit($limit)
					->get()->result()
			);
		}

		return $out;
	}

	/**
	 * Get related news (excluding the current news)
	 * @param int $exclude_news_id
	 * @param int $limit
	 * @return array
	 */
	/**
	 * @param int      $exclude_news_id
	 * @param int      $limit
	 * @param int|null $category_id  Prefer stories from this category, then
	 *                               top up site-wide so the block is never
	 *                               short just because a category is small.
	 *
	 * Also gained the `is_deleted = 0` filter the index has always had:
	 * a soft-deleted story could still surface here.
	 */
	public function getRelatedNews($exclude_news_id, $limit = 3, $category_id = NULL)
	{
		$limit    = (int) $limit;
		$picked   = array();
		$seen_ids = array((int) $exclude_news_id);

		$fetch = function ($category, $need) use (&$seen_ids) {
			$this->db->select('news.*, categories.category_name, categories.category_slug', false)
				->from('news')
				->join('news_categories as categories', 'categories.category_id = news.news_category_id', 'LEFT')
				->where('news.is_deleted', 0)
				->where('news.news_status', 1)
				->where_not_in('news.news_id', $seen_ids);
			if ($category !== NULL) $this->db->where('news.news_category_id', (int) $category);

			return $this->db->order_by('news.news_id', 'DESC')->limit((int) $need)->get()->result();
		};

		if ($category_id !== NULL) {
			foreach ($fetch((int) $category_id, $limit) as $row) {
				$picked[]   = $row;
				$seen_ids[] = (int) $row->news_id;
			}
		}
		if (count($picked) < $limit) {
			foreach ($fetch(NULL, $limit - count($picked)) as $row) {
				$picked[]   = $row;
				$seen_ids[] = (int) $row->news_id;
			}
		}

		return $picked;
	}

	/***********
	 ****NEWS****
	 ***********/
	public function getlatest_news($limit)
	{
		$data = $this->db->select('news.*,user.name')
			->from('news')
			->join('user', 'news.user_id = user.user_id')
			->where('news_status', 1)
			->limit($limit)
			->order_by('news_id', 'DESC')
			->get()->result();
		return $data;
	}

	// Get all news (public, no user filter)
	public function getAllNewsPublic($limit = NULL, $offset = NULL)
	{
		$this->db->select('*')->from('news')->where('is_deleted', 0);
		$this->db->order_by('news_id', 'DESC');
		if ($limit !== NULL) {
			$this->db->limit($limit, $offset);
		}
		return $this->db->get()->result();
	}

	// Get news with search and pagination (public)
	/**
	 * @param string   $search
	 * @param int      $limit
	 * @param int      $offset
	 * @param int|null $category_id  Filter to one news category.
	 *
	 * Selects `news.*` explicitly rather than `*`: the join brings in
	 * news_categories, which has its own created_at, and a bare `*` would
	 * let the category's timestamp overwrite the story's.
	 */
	public function getNewsWithSearch($search = '', $limit = 10, $offset = 0, $category_id = NULL)
	{
		$this->db->select('news.*, categories.category_name, categories.category_slug', false)
			->from('news')
			->join('news_categories as categories', 'categories.category_id = news.news_category_id', 'LEFT')
			->where('news.is_deleted', 0)
			->where('news.news_status', 1);
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('news.news_title', $search);
			$this->db->or_like('news.news_description', $search);
			$this->db->group_end();
		}
		if ($category_id !== NULL) $this->db->where('news.news_category_id', (int) $category_id);
		$this->db->order_by('news.news_id', 'DESC');
		$this->db->limit($limit, $offset); // $limit = per_page, $offset = offset
		return $this->db->get()->result();
	}

	/** Must apply exactly the filters getNewsWithSearch() applies. */
	public function countNewsWithSearch($search = '', $category_id = NULL)
	{
		$this->db->from('news')->where('is_deleted', 0)->where('news_status', 1);
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('news_title', $search);
			$this->db->or_like('news_description', $search);
			$this->db->group_end();
		}
		if ($category_id !== NULL) $this->db->where('news_category_id', (int) $category_id);
		return $this->db->count_all_results();
	}

	// Get all news
	public function getAllNews($user_id = NULL, $limit = NULL, $offset = NULL)
	{
		return $this->db->select('*')
			->from('news')
			->where('user_id', $user_id)
			->where('is_deleted', 0)
			->order_by('news_id', 'DESC')
			->limit($limit, $offset)
			->get()
			->result();
	}

	// Count user news posts
	public function countUserNews($user_id = NULL)
	{
		return $this->db->select('COUNT(*) as count')
			->from('news')
			->where('user_id', $user_id)
			->where('is_deleted', 0)
			->get()
			->row()->count;
	}



	public function getlatest_events($limit = NULL)
	{
		return $this->db->select('events.*, COALESCE(admin.admin_name, user.name) AS name', false)
			->from('events')
			->join('user', 'events.user_id = user.user_id', 'left')
			->join('admin', 'admin.admin_id = events.admin_id', 'left')
			->where('event_status', 1)
			->limit($limit)
			->order_by('event_id', 'DESC')
			->get()->result();
	}





	/**
	 * A member's listings that exist but are not public yet — added through
	 * the wizard and waiting for approval, or switched off. getListingById()
	 * only returns published ones, so screens that said "add a listing first"
	 * told members to add what they had already added.
	 */
	public function getUnpublishedListingsForUser($user_id)
	{
		return $this->db->select('listing_id, listing_name, listing_slug, listing_status, created_at')
			->from('listings')
			->where('user_id', (int) $user_id)
			->where('is_deleted', 0)
			->where('listing_status !=', 1)
			->order_by('created_at', 'DESC')
			->get()->result();
	}

	/**
	 * A listing slug no other listing holds — the same rule as the admin's
	 * Admin::makeUniqueListingSlug(). The member wizard used the bare name, so
	 * a second business with an existing name collided: where listing_slug is
	 * unique the insert failed, and where it is not, two businesses shared one
	 * URL (and the delete/restore paths that key on it).
	 */
	public function uniqueListingSlug($name)
	{
		$base = trim(preg_replace('/[^A-Za-z0-9-]+/', '-', (string) $name), '-');
		if ($base === '') $base = 'listing';
		$slug = $base;
		for ($n = 2; $this->db->where('listing_slug', $slug)->count_all_results('listings') > 0; $n++) {
			$slug = $base . '-' . $n;
		}
		return $slug;
	}

	/**
	 * Every listing a member owns that is not in the trash — published or
	 * not — for their own My Listings screen. The public filter
	 * (getListingById) hid a just-submitted or switched-off listing from its
	 * own owner.
	 */
	public function getOwnerListings($user_id, $limit = NULL, $offset = NULL)
	{
		return $this->db->select('*')->from('listings')
			->where('user_id', (int) $user_id)
			->where('is_deleted', 0)
			->order_by('created_at', 'DESC')
			->limit($limit, $offset)
			->get()->result();
	}

	public function countOwnerListings($user_id)
	{
		return (int) $this->db->from('listings')
			->where('user_id', (int) $user_id)
			->where('is_deleted', 0)
			->count_all_results();
	}

	public function getListingById($id = NULL, $limit = NULL, $offset = NULL)
	{
		return $data = $this->db->select('*')->from('listings')
			->where('user_id', $id)
			->where('is_deleted', 0)
			->where('listing_status', 1)
			->limit($limit, $offset)
			->order_by('created_at', 'DESC')
			->get()->result();
	}





	public function productSubCategoryByCateId($id)

	{

		return $data = $this->db->select('sub_cate.category_id, main_cate.category_name, sub_cate.sub_category_name, main_cate.category_id, sub_cate.created_at, sub_cate.sub_category_id')

			->from('product_sub_categories as sub_cate')

			->join('product_categories as main_cate', 'sub_cate.category_id = main_cate.category_id', 'LEFT')

			->where('sub_cate.category_id', $id)

			->get()->result();
	}

	// public function getAllProducts($limit,$offset)
	// {
	// 	 $this->db->select("*,products.id as p_id");
	//      $this->db->from('products');
	//      $this->db->join('product_images', 'products.id = product_images.product_id');
	//      $this->db->group_by('product_images.product_id')->limit($limit,$offset);
	//      return $query = $this->db->get()->result();
	// }



	/*===================
    PRODUCTS FUNCTION
  ===================*/
	public function getAllProducts()
	{
		$logged_id = $this->logged_user_id();

		return $data = $this->db->select('*')->from('products')
			->where('user_id', $logged_id)
			->where('product_status', 1)
			->where('is_deleted', 0)
			->get()->result();
	}

	public function getOnlyProduct($id)
	{
		return $data = $this->db->select('*')->from('products')->where('product_id', $id)->get()->row();
	}

	public function getSingleProduct($product_slug)
	{
		$data = $this->db->select('products.*, 
                               product_categories.category_name as cat_name, 
                               product_categories.category_id as pro_cat_id, 
                               user.name, 
                               user.user_created')
			->from('products')
			->join('product_categories', 'products.category_id = product_categories.category_id', 'left')
			->join('user', 'products.user_id = user.user_id', 'left')
			->where('products.product_slug', $product_slug)
			->get()
			->row();

		return $data;
	}

	/*==========================
		Add Product Information
	===========================*/
	public function addProduct($data)
	{
		// Set default values for required fields
		$data['product_status'] = 1; // Active by default
		$data['is_deleted'] = 0; // Not deleted
		$data['product_code'] = 'PROD_' . time(); // Generate unique product code

		if (!isset($data['created_at'])) {
			$data['created_at'] = date('Y-m-d H:i:s');
		}

		if ($this->db->insert('products', $data)) {
			return $this->db->insert_id();
		} else {
			return false;
		}
	}



	/*==========================
		Get Sub Categories by Category for Products
	===========================*/
	public function getSubCategoriesByCategory($category_id, $table = 'product_sub_categories')
	{
		return $this->db->select('*')
			->from($table)
			->where('category_id', $category_id)
			->get()
			->result();
	}

	/*==========================
		Get Related Products by Category
	===========================*/
	public function getRelatedProducts($category_id, $exclude_product_id, $limit = 3)
	{
		return $this->db->select('products.*, product_categories.category_name as cat_name')
			->from('products')
			->join('product_categories', 'products.category_id = product_categories.category_id', 'left')
			->where('products.category_id', $category_id)
			->where('products.product_id !=', $exclude_product_id)
			->where('products.product_status', 1)
			->where('products.is_deleted', 0)
			->limit($limit)
			->order_by('products.product_id', 'DESC')
			->get()
			->result();
	}


	/*==============
      BLOG FUNCTION
    ===============*/

	public function getBlogs()
	{
		return $data = $this->db->where('blog_status', 1)->get('blogs')->result();
	}

	public function deleteBlog($slug)
	{
		$data = $this->db->select('*')->from('blogs')->where('blog_slug', $slug)->get()->row();

		$img = '';

		if ($data->blog_image) {
			$image_link = './' . $data->blog_image;

			if (file_exists($image_link)) {
				$img = $image_link;
			} else {
				$img = '';
			}
		}

		if ($this->db->where('blog_slug', $slug)->delete('blogs')) {
			if ($img != '') {
				unlink($img);
				return true;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}



	/*============
		Coupon
	============*/
	public function getAllCoupons($user_id = NULL, $limit = NULL, $offset = NULL)
	{
		return $data = $this->db->select('*')->from('coupons')
			->where('coupon_user_id', $user_id)
			->where('coupon_status', 1)
			->order_by('coupon_id', 'DESC')
			->limit($limit, $offset)
			->get()->result();
	}
	// Get 4 Cupons For Home Page
	public function get_cupons4($limit)
	{
		$data = $this->db->select('coupons.*,user.name')
			->from('coupons')
			->join('user', 'coupons.user_id = user.user_id')
			->where('coupons.coupon_status', 1)
			->limit($limit)
			->order_by('coupons_id', 'DESC')
			->get()->result();
		return $data;
	}
	// public function getClassified4($limit)
	// {
	// 	$data = $this->db->select('classifieds.*,user.name')
	// 	->from('classifieds')
	// 	->join('user', 'classifieds.user_id = user.user_id')
	// 	->where('classifieds.classifieds_status', 1)
	// 		->limit($limit)
	// 		->order_by('classifieds_id', 'DESC')
	// 		->get()->result();
	// 	return $data;
	// }


	public function addCoupon($data)

	{

		$session = $this->session->userdata('logged_user');

		$logged_id = $session['logged_id'];

		$data['coupon_user_id'] = $logged_id;



		if ($this->db->insert('coupons', $data)) {

			return true;
		} else {

			return false;
		}
	}


	/*==================
      COMMON FUNCTION
    ==================*/
	public function getAllValues($tableName)
	{
		return $data = $this->db->get($tableName)->result();
	}

	public function getAllListingCategory()
	{
		return $this->db->select('*')
			->from('listing_categories')
			->order_by('category_name', 'ASC')
			->get()->result();
	}

	public function getSingleValue($id, $table_id, $tableName)
	{
		// Special handling for listings to join with user data
		if ($tableName === 'listings') {
			return $this->db->select('listings.*, user.name as user_name, user.profile_image as user_profile_image, user.user_created')
				->from('listings')
				->join('user', 'listings.user_id = user.user_id', 'left')
				->where('listings.' . $table_id, $id)
				->where('listings.is_deleted', 0) // Only get non-deleted listings
				->get()->row();
		}

		// Special handling for events to debug
		if ($tableName === 'events') {
			$query = $this->db->select('*')
				->from($tableName)
				->where($table_id, $id)
				->get();

			if ($query->num_rows() > 0) {
				return $query->row();
			} else {
				// If no event found with event_id, try event_slug
				$query = $this->db->select('*')
					->from($tableName)
					->where('event_slug', $id)
					->get();
				return $query->row();
			}
		}

		// Standard handling for other tables
		return $this->db->select('*')
			->from($tableName)
			->where($table_id, $id)
			->get()->row();
	}

	public function getClassifiedsBySlug($slug)
	{
		return $this->db->select('classifieds.*,user.name,user.profile_image,user.user_created')
			->from('classifieds')
			->join('user', 'classifieds.user_id = user.user_id', 'left')
			->where('classifieds.classifieds_slug', $slug)
			->where('classifieds.classifieds_status', 1)
			->get()->row();
	}

	/**
	 * Get event by slug, with fallback to name-based slug generation
	 */
	public function getEventBySlug($slug)
	{
		// First try to find by event_slug
		$event = $this->db->select('*')
			->from('events')
			->where('event_slug', $slug)
			->where('event_status', 1) // Only active events
			->get()->row();

		if ($event) {
			return $event;
		}

		// If not found, try to find by converting event names to slugs.
		//
		// Two generators are accepted, because two are in use: this model's
		// createSafeSlug() (drops every non-alphanumeric run to one hyphen)
		// and the shared pearland_slug() the views build links with (spaces to
		// hyphens, then strips the rest). They agree on "Market: A Market" and
		// disagree on "Don't Miss" — "don-t-miss" against "dont-miss" — so
		// resolving on only one of them makes a link that generation produced
		// unreachable. Accept both rather than pick a winner.
		if (!function_exists('pearland_slug')) $this->load->helper('view');

		$events = $this->db->select('*')
			->from('events')
			->where('event_status', 1)
			->get()->result();

		foreach ($events as $event) {
			// Create slug from event name if event_slug is empty
			if ($this->createSafeSlug($event->event_name) === $slug) return $event;
			if (pearland_slug($event->event_name) === $slug)         return $event;
		}

		return null;
	}

	/**
	 * Get rental by slug, with fallback to title-based slug generation
	 */
	public function getRentalBySlug($slug)
	{
		// Try exact slug match first
		$rental = $this->db->select('*')
			->from('rental')
			->where('rental_slug', $slug)
			->where('rental_status', 1)
			->get()->row();

		if ($rental) return $rental;

		// Fallback: generate slug from titles for records without slug
		$rentals = $this->db->select('*')
			->from('rental')
			->where('rental_status', 1)
			->get()->result();

		foreach ($rentals as $r) {
			$generated = $this->createSafeSlug($r->rental_title);
			if ($generated === $slug) return $r;
		}

		return null;
	}

	/**
	 * Get house by slug, with fallback to title-based slug generation
	 */
	public function getHouseBySlug($slug)
	{
		// Try exact slug match first
		$house = $this->db->select('*')
			->from('house')
			->where('house_slug', $slug)
			->where('house_status', 0) // keep existing convention (0 = active)
			->get()->row();

		if ($house) return $house;

		// Fallback: generate slug from titles for records without slug
		$houses = $this->db->select('*')
			->from('house')
			->where('house_status', 0)
			->get()->result();

		foreach ($houses as $h) {
			$generated = $this->createSafeSlug($h->house_title);
			if ($generated === $slug) return $h;
		}

		return null;
	}

	/**
	 * Create safe URL slug
	 */
	private function createSafeSlug($text)
	{
		// Remove HTML tags
		$text = strip_tags($text);

		// Convert to lowercase
		$text = strtolower($text);

		// Replace spaces and special characters with hyphens
		$text = preg_replace('/[^a-z0-9]+/', '-', $text);

		// Remove leading and trailing hyphens
		$text = trim($text, '-');

		// Remove multiple consecutive hyphens
		$text = preg_replace('/-+/', '-', $text);

		return $text;
	}

	/**
	 * Other businesses in the same category.
	 *
	 * Was a hand-built SQL string that interpolated the slug straight into
	 * the WHERE clause and filtered on nothing else — so it happily returned
	 * soft-deleted and unpublished listings. Now bound parameters, and only
	 * live rows, ordered so featured businesses surface first.
	 *
	 * @param string $tableName  kept for call-site compatibility
	 * @param string $listing_id the CURRENT listing's slug, excluded
	 */
	public function getRelatedListing($tableName, $listing_id, $category_id, $limit = 3)
	{
		if (empty($category_id)) return array();

		$this->db
			->select('l.*, lc.category_name, lc.category_slug')
			->from($tableName . ' l')
			->join('listing_categories lc', 'l.category_id = lc.category_id', 'left')
			->where('l.category_id', $category_id)
			->where('l.listing_slug !=', (string) $listing_id)
			->where('l.is_deleted', 0)
			->where('l.listing_status', 1)
			->order_by('COALESCE(l.featured_listing, 0) DESC, l.listing_id DESC', '', false)
			->limit((int) $limit);

		return $this->db->get()->result();
	}

	/**
	 * Get related events excluding current event
	 */
	/**
	 * Events to suggest beside the one being read: what is still to come
	 * first (soonest at the front), topped up with the most recent past
	 * events only if there aren't enough. Ordering by event_id alone put a
	 * concert from last spring above a festival next week.
	 */
	public function getRelatedEvents($current_event_id, $limit = 3)
	{
		$limit    = (int) $limit;
		$seen_ids = array((int) $current_event_id);

		$upcoming = $this->db->select('*')
			->from('events')
			->where('event_status', 1)
			->where_not_in('event_id', $seen_ids)
			->where('event_start_date >=', date('Y-m-d'))
			->order_by('event_start_date', 'ASC')
			->order_by('event_id', 'ASC')
			->limit($limit)
			->get()->result();

		foreach ($upcoming as $row) $seen_ids[] = (int) $row->event_id;
		if (count($upcoming) >= $limit) return $upcoming;

		$past = $this->db->select('*')
			->from('events')
			->where('event_status', 1)
			->where_not_in('event_id', $seen_ids)
			->order_by('event_start_date', 'DESC')
			->order_by('event_id', 'DESC')
			->limit($limit - count($upcoming))
			->get()->result();

		return array_merge($upcoming, $past);
	}

	/**
	 * Insert a row, defaulting user_id to the signed-in member.
	 *
	 * The default only applies when the caller did NOT supply a user_id. It
	 * used to overwrite one unconditionally, from `userdata('logged_user')` —
	 * but an admin session carries `logged_admin`, never `logged_user`, so for
	 * every admin-side caller that read NULL and blanked the author the admin
	 * had just picked from the "Choose a user" dropdown. Admin::add_blog() goes
	 * through here, which is why blog bylines came out as "Unknown" while news
	 * (which uses Adminmodel::add()) was fine. That is the reported "User Name
	 * still not updated across the entire website".
	 */
	public function add($tableName, $data)
	{
		if (!array_key_exists('user_id', $data) || $data['user_id'] === NULL || $data['user_id'] === '') {
			$session = $this->session->userdata('logged_user');
			if (is_array($session) && isset($session['logged_id'])) {
				$data['user_id'] = $session['logged_id'];
			}
		}

		if ($this->db->insert($tableName, $data)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function update($id, $info, $table_id, $tableName)
	{
		if ($this->db->where($table_id, $id)->update($tableName, $info)) {
			return true;
		} else {
			return false;
		}
	}

	// Product Delete
	public function delete($id, $table_id, $tableName)
	{
		$this->db->where($table_id, $id);
		$this->db->delete($tableName);

		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			error_log('Product deletion failed. Query: ' . $this->db->last_query());
			return false;
		}
	}



	public function getDashboardInfo($tableName, $isDeleted = NULL, $limit = NULL)
	{
		$limiter = '';
		$where 	 = '';

		if ($limit != NULL) {
			$limiter = $limit;
		}

		if ($isDeleted != NULL) {
			$where = 'WHERE is_deleted = ' . $isDeleted;
		}

		$query = $this->db->query("SELECT * FROM $tableName $where LIMIT 10")->result();

		return $query;
	}

	/**
	 * How many rows of $table this member owns — the "Used" figure on the
	 * dashboard. Listings in the Trash are not counted.
	 */
	public function countOwnedContent($table, $user_id)
	{
		$user_id = (int) $user_id;
		if ($user_id <= 0 || !in_array($table, array('listings', 'events', 'blogs'), TRUE)) return 0;

		$this->db->from($table)->where('user_id', $user_id);
		if ($table === 'listings') {
			$this->db->group_start()->where('is_deleted IS NULL', NULL, FALSE)->or_where('is_deleted', 0)->group_end();
		}
		return (int) $this->db->count_all_results();
	}

	/**
	 * @deprecated Unscoped: no WHERE on the owner, so the member dashboard
	 * showed the first ten events and blogs on the whole SITE as the
	 * member's own. Use getAllEvent() / getAllBlogs() with a user id.
	 */
	public function dashboardInfo($tableName, $status = NULL, $isDeleted = NULL, $limit = NULL)
	{
		$limiter = '';
		$where 	 = '';

		if ($limit != NULL) {
			$limiter = $limit;
		}

		if ($isDeleted != NULL) {
			$where = 'WHERE ' . $status . ' = ' . $isDeleted;
		}

		$query = $this->db->query("SELECT * FROM $tableName $where LIMIT 10")->result();

		return $query;
	}

	public function getEnquiries($receivingUserId)
	{
		return $data = $this->db->select('*')
			->from('enquiries')
			->where('receiving_user_id', $receivingUserId)
			->get()->result();
	}

	/**
	 * Scope a query on `enquiries` to the leads ONE member may see: those
	 * addressed to them, plus — where the column exists — any about a
	 * listing they own (rows saved before add_enquiry derived the receiver
	 * server-side carry receiving_user_id NULL/0).
	 */
	private function applyEnquiryReceiverScope($user_id)
	{
		$user_id = (int) $user_id;
		if (!$this->db->field_exists('listing_id', 'enquiries')) {
			$this->db->where('receiving_user_id', $user_id);
			return;
		}
		// A listing's enquiries belong to whoever owns the listing NOW. Before,
		// receiving_user_id (fixed when the enquiry was sent) also counted for
		// listing leads, so after a claim was approved the business's earlier
		// enquiries stayed with the data-entry login that used to own it —
		// tracker: "once they claim the listing, all the inquiries … should be
		// on their dashboards". Enquiries about anything else (an event, a
		// post) have no listing and still go by receiving_user_id.
		$listings = $this->db->dbprefix('listings');
		$this->db->group_start()
			->where('listing_id IN (SELECT listing_id FROM ' . $listings . ' WHERE user_id = ' . $user_id . ')', NULL, FALSE)
			->or_group_start()
				->group_start()
					->where('listing_id IS NULL', NULL, FALSE)
					->or_where('listing_id', 0)
				->group_end()
				->where('receiving_user_id', $user_id)
			->group_end()
		->group_end();
	}

	/**
	 * Point a listing's enquiries at its new owner — called when a claim is
	 * approved (Admin::claim_approve), so receiving_user_id agrees with who
	 * can see them. Legacy rows filed with no listing_id but the business's
	 * name, addressed to the previous owner, move too.
	 *
	 * @return int rows moved
	 */
	public function moveListingEnquiries($listing_id, $new_owner_id, $previous_owner_id = 0, $listing_name = '')
	{
		if (!$this->db->field_exists('listing_id', 'enquiries')) return 0;
		$this->db->where('listing_id', (int) $listing_id)
			->update('enquiries', array('receiving_user_id' => (int) $new_owner_id));
		$moved = (int) $this->db->affected_rows();

		if ($listing_name !== '' && $this->db->field_exists('enquiry_source', 'enquiries')) {
			$this->db->group_start()->where('listing_id IS NULL', NULL, FALSE)->or_where('listing_id', 0)->group_end()
				->where('enquiry_source', $listing_name)
				->where('receiving_user_id', (int) $previous_owner_id)
				->update('enquiries', array('listing_id' => (int) $listing_id, 'receiving_user_id' => (int) $new_owner_id));
			$moved += (int) $this->db->affected_rows();
		}
		return $moved;
	}

	/** A member's own leads, newest first. Replaces getAllEnquiries() on /user/enquiries. */
	public function getEnquiriesForReceiver($user_id, $limit = NULL, $offset = 0)
	{
		if ((int) $user_id <= 0) return array();

		$this->db->from('enquiries');
		$this->applyEnquiryReceiverScope($user_id);
		$this->db->order_by('enquiry_created_at', 'DESC')->order_by('enquiry_id', 'DESC');
		if ($limit !== NULL) $this->db->limit((int) $limit, (int) $offset);

		return $this->db->get()->result();
	}

	public function countEnquiriesForReceiver($user_id, $since = NULL)
	{
		if ((int) $user_id <= 0) return 0;

		$this->db->from('enquiries');
		$this->applyEnquiryReceiverScope($user_id);
		if ($since !== NULL && $this->db->field_exists('enquiry_created_at', 'enquiries')) {
			$this->db->where('enquiry_created_at >=', $since);
		}

		return (int) $this->db->count_all_results();
	}

	/**
	 * Total page views across what a member owns, for the dashboard's report
	 * panel — which used to print the hard-coded figures "00" and "02".
	 * 0 when the table has no views column.
	 */
	public function sumOwnedViews($table, $user_id)
	{
		if (!$this->db->table_exists($table) || !$this->db->field_exists('views', $table)) return 0;
		$this->db->select_sum('views', 'v')->from($table)->where('user_id', (int) $user_id);
		if ($this->db->field_exists('is_deleted', $table)) $this->db->where('is_deleted', 0);
		$row = $this->db->get()->row();
		return $row ? (int) $row->v : 0;
	}

	/** One enquiry, but only if this member may see it (same scope as the list). */
	public function getEnquiryForReceiver($enquiry_id, $user_id)
	{
		if ((int) $user_id <= 0) return NULL;

		$this->db->from('enquiries')->where('enquiry_id', (int) $enquiry_id);
		$this->applyEnquiryReceiverScope($user_id);

		return $this->db->limit(1)->get()->row();
	}

	/**
	 * Every enquiry on the site. ADMIN ONLY — the member page used this
	 * ("showing all for now"), which showed every member every other
	 * business's leads, names, emails and phone numbers included.
	 */
	/**
	 * SQL for "the name printed on this post": the byline (migration 0035)
	 * when one was typed, else the account's name. Falls back to the plain
	 * account name on an install without the column.
	 */
	private function bylineSelect($table, $column)
	{
		static $has = array();
		$key = $table . '.' . $column;
		if (!isset($has[$key])) $has[$key] = $this->db->field_exists($column, $table);

		return $has[$key]
			? 'COALESCE(NULLIF(TRIM(' . $table . '.' . $column . "), ''), user.name)"
			: 'user.name';
	}

	public function getAllEnquiries()
	{
		return $this->db->select('*')
			->from('enquiries')
			->order_by('enquiry_created_at', 'DESC')
			->get()->result();
	}

	/**
	 * Insert an enquiry, fitted to the columns this install actually has.
	 *
	 * `enquiries` predates the repo's migrations and is not defined in it, so
	 * its columns vary by install. add_enquiry() writes up to eight
	 * content-id columns (news_id, rental_id, video_id…); one missing column
	 * failed the whole insert and the visitor was told "Something went
	 * wrong". Unknown keys are dropped, and a NULL bound for a NOT NULL
	 * column without a default (an anonymous sender, a lead with no owner)
	 * becomes 0 / '' instead of a strict-mode error.
	 *
	 * @return int|false the new enquiry_id (TRUE-ish), or FALSE
	 */
	public function addEnquiry($data)
	{
		// The fitting and the try/catch live in db_safe_insert() now
		// (application/helpers/db_safe_helper.php) — this was the first copy of
		// that rule. A lead that cannot be saved still reaches the caller's
		// "could not send" path, never a 500.
		return db_safe_insert('enquiries', $data);
	}

	public function getEnquiryById($enquiry_id, $receiving_user_id)
	{
		return $this->db->select('*')
			->from('enquiries')
			->where('enquiry_id', $enquiry_id)
			->where('receiving_user_id', $receiving_user_id)
			->get()->row();
	}

	public function deleteEnquiry($enquiry_id, $receiving_user_id)
	{
		$this->db->where('enquiry_id', $enquiry_id);
		$this->db->where('receiving_user_id', $receiving_user_id);
		return $this->db->delete('enquiries');
	}

	public function getEnquiryByIdSimple($enquiry_id)
	{
		return $this->db->select('*')
			->from('enquiries')
			->where('enquiry_id', $enquiry_id)
			->get()->row();
	}

	/** Unscoped delete by id. ADMIN ONLY — members go through getEnquiryForReceiver() first. */
	public function deleteEnquirySimple($enquiry_id)
	{
		$this->db->where('enquiry_id', (int) $enquiry_id);
		$result = $this->db->delete('enquiries');

		// Log the database error if delete fails
		if (!$result) {
			log_message('error', 'Failed to delete enquiry. DB Error: ' . $this->db->error()['message']);
		}

		return $result;
	}

	public function claimBusiness($data)
	{
		$data['claim_created_at'] = date('Y-m-d H:i:s');
		// Schema-fitted: the role / verification / business-name columns
		// arrive with migration 0037 and are dropped on an install without it.
		$id = db_safe_insert('claim_listings', $data);
		return $id ? (int) $id : false;
	}

	/**
	 * The listings a member has asked to claim, newest first, with each
	 * claim's state — for /user/my-claims. claim_status: 0 pending review,
	 * 1 approved (the listing was transferred to them), 2 not approved
	 * (Admin::claim_reject). Only the latest claim per listing is returned:
	 * a member whose claim was turned down may submit a fresh one.
	 *
	 * @param  string|null $only 'pending' | 'approved' | 'rejected'
	 */
	public function getClaimsByUser($user_id, $only = NULL)
	{
		$map = array('pending' => 0, 'approved' => 1, 'rejected' => 2);

		$this->db->select('c.*, l.listing_name, l.listing_slug, l.listing_status, l.user_id AS listing_owner_id, l.is_deleted')
			->from('claim_listings c')
			->join('listings l', 'l.listing_id = c.listing_id', 'left')
			->where('c.enquiry_sender_id', (int) $user_id)
			// Latest claim per listing only.
			->where('c.claim_listing_id = (SELECT MAX(c2.claim_listing_id) FROM ' . $this->db->protect_identifiers('claim_listings', TRUE)
				. ' c2 WHERE c2.listing_id = c.listing_id AND c2.enquiry_sender_id = c.enquiry_sender_id)', NULL, FALSE)
			->order_by('c.claim_listing_id', 'DESC');
		if ($only !== NULL && isset($map[$only])) {
			$this->db->where('c.claim_status', $map[$only]);
		}
		return $this->db->get()->result();
	}

	/**
	 * Does this account's current plan include the Verified badge? (The
	 * listing packages, migration 0038: Starter and up.) Before 0038 there is
	 * no such flag and a claimed listing was "verified", so this says yes.
	 */
	public function ownerHasVerifiedBadge($user_id)
	{
		$user_id = (int) $user_id;
		if ($user_id <= 0) return FALSE;
		if (!$this->db->field_exists('verified_badge', 'plans')) return TRUE;
		return $this->db->from('subscriptions s')
			->join('plans p', 'p.id = s.plan_id')
			->where('s.user_id', $user_id)
			->where('s.status', 'active')
			->where('s.end_date >=', date('Y-m-d'))
			->where('p.verified_badge', 1)
			->count_all_results() > 0;
	}

	/**
	 * Leads (enquiries) per listing since a date — the "Leads" column of the
	 * analytics pages. A lead is the enquiries row itself; nothing is counted
	 * twice in activity_daily.
	 *
	 * @param  int[]  $listing_ids [] = every listing
	 * @param  string $since       Y-m-d
	 * @return array<int,int> listing_id => leads
	 */
	public function countListingLeads(array $listing_ids, $since)
	{
		if (!$this->db->field_exists('listing_id', 'enquiries')) return array();
		$this->db->select('listing_id, COUNT(*) AS n', FALSE)
			->from('enquiries')
			->where('listing_id >', 0)
			->where('enquiry_created_at >=', $since . ' 00:00:00');
		$listing_ids = array_values(array_filter(array_map('intval', $listing_ids)));
		if ($listing_ids) $this->db->where_in('listing_id', $listing_ids);
		$out = array();
		foreach ($this->db->group_by('listing_id')->get()->result() as $row) {
			$out[(int) $row->listing_id] = (int) $row->n;
		}
		return $out;
	}

	public function getClaimById($claim_id)
	{
		return $this->db
			->where('claim_listing_id', (int) $claim_id)
			->limit(1)
			->get('claim_listings')
			->row();
	}


	/*********************

	 ***   ADS QUERY   ***

	 *********************/



	// public function getAllAds($user_id = NULL, $limit = NULL, $offset = NULL)

	// {

	// 	return $query = $this->db->select('ads.*,u.name as username,u.profile_image as user_image,u.user_created as user_join,ap.name as ads_price_name')
	// 		->from('ads')
	// 		->where('ads.u_id', $user_id)
	// 		->where('ads.ad_status', 1)
	// 		->join('user as u', 'ads.u_id = u.user_id')
	// 		->join('ads_prices as ap', 'ads.ads_price_id = ap.id');
	// 	echo $this->db->last_query();  // Outputs the SQL query
	// 	return $query->get()->result();
	// }

	public function getAllAds($limit, $offset)
	{
		$this->db->limit($limit, $offset);
		$query = $this->db->get('ads');
		return $query->result();
	}
	// Count total number of ads
	public function countAllAds()
	{
		return $this->db->count_all('ads');
	}


	public function addAds($tableName, $data)

	{

		$session = $this->session->userdata('logged_user');

		$logged_id = $session['logged_id'];

		$data['u_id'] = $logged_id;

		// echo "<pre>";var_dump($data);die();

		if ($this->db->insert($tableName, $data)) {

			return TRUE;
		} else {

			return FALSE;
		}
	}



	/*==================
        ADS PRICE
    ==================*/
	public function allAdsPrices()
	{
		return $this->db->where('status', 1)->get('ads_prices')->result();
	}

	public function getAdsPrice($id)
	{
		return $this->db->where('id', $id)->get('ads_prices')->row();
	}

	public function updateAdsPrice($id, $info)
	{
		if ($this->db->where('id', $id)->update('ads_prices', $info)) {
			return true;
		} else {
			return false;
		}
	}

	public function getHPBSLAds()
	{
		return $this->db->select('ads.*,ap.name')
			->from('ads')
			->join('ads_prices as ap', 'ads.ads_price_id = ap.id')
			->where('ads.ad_status', 1)
			->where('ap.name', 'Home Page Below Slider left')
			->order_by('rand()')
			->get()->row();
	}

	public function getHPBSRAds()
	{
		return $this->db->select('ads.*,ap.name')
			->from('ads')
			->join('ads_prices as ap', 'ads.ads_price_id = ap.id')
			->where('ads.ad_status', 1)
			->where('ap.name', 'Home Page Below Slider right')
			->order_by('rand()')
			->get()->row();
	}

	public function getFBR1Ads()
	{
		return $this->db->select('ads.*,ap.name')
			->from('ads')
			->join('ads_prices as ap', 'ads.ads_price_id = ap.id')
			->where('ads.ad_status', 1)
			->where('ap.name', 'Feature Business Right 1')
			->order_by('rand()')
			->get()->row();
	}

	public function getFBR2Ads()
	{
		return $this->db->select('ads.*,ap.name')
			->from('ads')
			->join('ads_prices as ap', 'ads.ads_price_id = ap.id')
			->where('ads.ad_status', 1)
			->where('ap.name', 'Feature Business Right 2')
			->order_by('rand()')
			->get()->row();
	}

	public function getFBR3Ads()
	{
		return $this->db->select('ads.*,ap.name')
			->from('ads')
			->join('ads_prices as ap', 'ads.ads_price_id = ap.id')
			->where('ads.ad_status', 1)
			->where('ap.name', 'Feature Business Right 3')
			->order_by('rand()')
			->get()->row();
	}

	public function getAFLAds()
	{
		return $this->db->select('ads.*,ap.name')
			->from('ads')
			->join('ads_prices as ap', 'ads.ads_price_id = ap.id')
			->where('ads.ad_status', 1)
			->where('ap.name', 'Above Footer Left')
			->order_by('rand()')
			->get()->row();
	}

	public function getSpotlightListing($tableName, $limit = NULL, $type = NULL)
	{
		return $data = $this->db->select('listings.*,user.name,listing_categories.category_name')
			->from('listings')
			->join('user', 'listings.user_id = user.user_id')
			->join('listing_categories', 'listings.category_id = listing_categories.category_id')
			->where('listings.spotlight_listing', 1)
			->where('listings.is_deleted', 0)
			->where('listings.listing_status', 1)
			->order_by('listings.created_at', 'DESC')
			->limit($limit)
			->get()->result();
	}

	public function getFeaturedListing($tableName, $limit = NULL, $type = NULL)
	{
		return $data = $this->db->select('listings.*,user.name,listing_categories.category_name')
			->from('listings')
			->join('user', 'listings.user_id = user.user_id')
			->join('listing_categories', 'listings.category_id = listing_categories.category_id')
			->where('listings.featured_listing', 1)
			->where('listings.is_deleted', 0)
			->where('listings.listing_status', 1)
			->order_by('listings.created_at', 'DESC')
			->limit($limit)
			->get()->result();
	}


	/*User News Functions*/
	public function getUserNews()
	{
		$logged_id = $this->logged_user_id();

		return $data = $this->db->select('*')->from('news')
			->where('user_id', $logged_id)
			// ->where('news_status',1)
			->where('is_deleted', 0)
			->order_by('news_id', 'DESC')
			->get()->result();
	}

	public function deleteNews($slug)
	{
		$data = $this->db->select('*')->from('news')->where('news_slug', $slug)->get()->row();

		$img = '';

		if ($data->news_image) {
			$image_link = './' . $data->news_image;

			if (file_exists($image_link)) {
				$img = $image_link;
			} else {
				$img = '';
			}
		}

		if ($this->db->where('news_slug', $slug)->delete('news')) {
			if ($img != '') {
				unlink($img);
				return true;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	/**
	 * Get related videos (excluding the current video)
	 * @param int $exclude_video_id
	 * @param int $limit
	 * @return array
	 */
	/**
	 * @param int      $exclude_video_id
	 * @param int      $limit
	 * @param int|null $category_id  Prefer this category, then top up.
	 *
	 * The status filter is deliberately absent, matching getAllVideos():
	 * the index has never filtered on video_status, and filtering only here
	 * meant a page could list a video it then refused to suggest.
	 */
	public function getRelatedVideos($exclude_video_id, $limit = 3, $category_id = NULL)
	{
		$limit    = (int) $limit;
		$picked   = array();
		$seen_ids = array((int) $exclude_video_id);

		$fetch = function ($category, $need) use (&$seen_ids) {
			$this->db->select('video.video_id, video.video_title, video.video_slug, video.video_image, video.video_code, video.video_description, video.views, video.created_at, categories.category_name, categories.category_slug', false)
				->from('video')
				->join('video_categories as categories', 'categories.category_id = video.video_category_id', 'LEFT')
				->where_not_in('video.video_id', $seen_ids);
			if ($category !== NULL) $this->db->where('video.video_category_id', (int) $category);

			return $this->db->order_by('video.video_id', 'DESC')->limit((int) $need)->get()->result();
		};

		if ($category_id !== NULL) {
			foreach ($fetch((int) $category_id, $limit) as $row) {
				$picked[]   = $row;
				$seen_ids[] = (int) $row->video_id;
			}
		}
		if (count($picked) < $limit) {
			foreach ($fetch(NULL, $limit - count($picked)) as $row) {
				$picked[]   = $row;
				$seen_ids[] = (int) $row->video_id;
			}
		}

		return $picked;
	}


	// Global Videos
	// Get videos with search and pagination (public)
	/**
	 * @param int|null $category_id  Filter to one video category.
	 *
	 * video_code and views join the select list: without the code a video
	 * whose thumbnail was never uploaded had nothing to show but a broken
	 * image, and YouTube already hosts one for every video id.
	 */
	public function getAllVideos($search = '', $limit = 10, $offset = 0, $category_id = NULL)
	{
		$this->db->select('video.video_id,video.video_title, video.video_description,video.video_status,video.created_at,video.video_slug,video.video_image,video.video_code,video.views, user.user_id,user.name,categories.category_name,categories.category_slug')
			->from('video')
			->join('user', 'user.user_id = video.user_id', 'LEFT')
			->join('video_categories as categories', 'categories.category_id = video.video_category_id', 'LEFT');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('video_title', $search);
			$this->db->or_like('video_description', $search);
			$this->db->group_end();
		}
		if ($category_id !== NULL) $this->db->where('video.video_category_id', (int) $category_id);
		$this->db->order_by('video_id', 'DESC');
		$this->db->limit($limit, $offset);
		return $this->db->get()->result();
	}

	/** Must apply exactly the filters getAllVideos() applies. */
	public function countVideosWithSearch($search = '', $category_id = NULL)
	{
		$this->db->from('video');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('video_title', $search);
			$this->db->or_like('video_description', $search);
			$this->db->group_end();
		}
		if ($category_id !== NULL) $this->db->where('video_category_id', (int) $category_id);
		return $this->db->count_all_results();
	}

	/*=================
        User Video
    =================*/
	public function getVideo($logged_id = NULL, $limit = NULL, $offset = NULL)
	{
		return $data = $this->db->select('video.video_id,video.video_title, video.video_description,video.video_status,video.created_at,video.video_slug,video.video_image,video.views, user.user_id,user.name,categories.category_name')
			->from('video')
			->where('video.user_id', $logged_id)
			->join('user', 'user.user_id = video.user_id', 'LEFT')
			->join('video_categories as categories', 'categories.category_id = video.video_category_id', 'LEFT')
			->order_by('video_id', 'DESC')
			->limit($limit, $offset)
			->get()->result();
	}

	public function countUserVideos($logged_id = NULL)
	{
		return $this->db->select('COUNT(*) as total')
			->from('video')
			->where('video.user_id', $logged_id)
			->get()->row()->total;
	}

	public function getVideos4($limit)
	{
		$data = $this->db->select('video.*,user.name')
			->from('video')
			->join('user', 'video.user_id = user.user_id')
			->where('video.video_status', 1)
			->limit($limit)
			->order_by('video_id', 'DESC')
			->get()->result();
		return $data;
	}
	public function getLatestVideo()
	{
		$data = $this->db->select('video.*, user.name')
			->from('video')
			->join('user', 'video.user_id = user.user_id')
			->where('video.video_status', 1)
			->order_by('video_id', 'DESC') // Get the latest video
			->limit(1) // Fetch only the latest video
			->get()
			->row(); // Use row() instead of result() to get a single object

		return $data;
	}


	public function allVideoCategory()
	{
		return $data = $this->db->select('*')
			->from('video_categories')
			->order_by('category_name', 'ASC')
			->get()->result();
	}
	public function deleteVideo($slug)
	{
		$data = $this->db->select('*')->from('video')->where('video_slug', $slug)->get()->row();

		if (!$data) {
			return false; // Video not found
		}

		$img = '';

		if (!empty($data->video_image)) {
			$image_link = './' . $data->video_image;

			if (file_exists($image_link)) {
				$img = $image_link;
			}
		}

		if ($this->db->where('video_id', $data->video_id)->delete('video')) { // Fix: Use `$data->video_id`
			if (!empty($img)) {
				unlink($img);
			}
			return true;
		}

		return false;
	}



	/*==================
        Classifieds
    ==================*/
	// Category //
	public function allClassifiedsCategory()
	{
		return $data = $this->db->select('*')
			->from('classifieds_categories')
			->order_by('category_name', 'ASC')
			->get()->result();
	}


	public function getClassifieds($logged_id = NULL, $limit = NULL, $offset = NULL)
	{

		return $data = $this->db->select('classifieds.classifieds_id,classifieds.classifieds_title, classifieds.classifieds_description,classifieds.classifieds_status,classifieds.classifieds_slug,classifieds.created_at,classifieds.classifieds_image, classifieds.views, user.user_id,user.name,categories.category_name')
			->from('classifieds')
			->where('classifieds.user_id', $logged_id)
			->join('user', 'user.user_id = classifieds.user_id', 'LEFT')
			->join('classifieds_categories as categories', 'categories.category_id = classifieds.classifieds_category_id', 'LEFT')
			->order_by('classifieds_id', 'DESC')
			->limit($limit, $offset)
			->get()->result();
	}

	public function countUserClassifieds($logged_id = NULL)
	{
		return $this->db->select('COUNT(*) as total')
			->from('classifieds')
			->where('classifieds.user_id', $logged_id)
			->get()->row()->total;
	}

	/**
	 * Count user rental posts
	 */
	public function countUserRentals($logged_id = NULL)
	{
		return $this->db->select('COUNT(*) as total')
			->from('rental')
			->where('rental.user_id', $logged_id)
			->get()->row()->total;
	}

	/**
	 * Get user rentals with pagination
	 */
	public function getRentals($logged_id, $limit = 10, $offset = 0)
	{
		return $this->db->select('rental.*, rental_categories.category_name as category_name')
			->from('rental')
			->join('rental_categories', 'rental.rental_category_id = rental_categories.category_id', 'left')
			->where('rental.user_id', $logged_id)
			->order_by('rental_id', 'DESC')
			->limit($limit, $offset)
			->get()->result();
	}

	/**
	 * Get related classifieds (excluding the current classifieds)
	 * @param int $exclude_classifieds_id
	 * @param int $limit
	 * @return array
	 */
	public function getRelatedClassifieds($exclude_classifieds_id, $limit = 3)
	{
		$this->db->where('classifieds_status', 1);
		$this->db->where('classifieds_id !=', $exclude_classifieds_id);
		$this->db->order_by('created_at', 'DESC');
		$this->db->limit($limit);
		$query = $this->db->get('classifieds');
		return $query->result();
	}

	// Get All Classifieds Global
	public function getAllClassifieds($search = '', $limit = 10, $offset = 0)
	{
		$this->db->select('classifieds.classifieds_id,classifieds.classifieds_title, classifieds.classifieds_description,classifieds.classifieds_status,classifieds.created_at,classifieds.classifieds_slug,classifieds.classifieds_image, user.user_id,user.name,categories.category_name')
			->from('classifieds')
			->join('user', 'user.user_id = classifieds.user_id', 'LEFT')
			->join('classifieds_categories as categories', 'categories.category_id = classifieds.classifieds_category_id', 'LEFT');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('classifieds_title', $search);
			$this->db->or_like('classifieds_description', $search);
			$this->db->group_end();
		}
		$this->db->where('classifieds.classifieds_status', 1);
		// $this->db->where('classifieds.is_deleted', 0);
		$this->db->order_by('classifieds_id', 'DESC');
		$this->db->limit($limit, $offset);
		return $this->db->get()->result();
	}

	public function countClassifiedsWithSearch($search = '')
	{
		$this->db->from('classifieds');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('classifieds_title', $search);
			$this->db->or_like('classifieds_description', $search);
			$this->db->group_end();
		}
		$this->db->where('classifieds.classifieds_status', 1);
		// $this->db->where('classifieds.is_deleted', 0);
		return $this->db->count_all_results();
	}


	public function getClassified4($limit)
	{
		$data = $this->db->select('classifieds.*,user.name')
			->from('classifieds')
			->join('user', 'classifieds.user_id = user.user_id')
			->where('classifieds.classifieds_status', 1)
			->limit($limit)
			->order_by('classifieds_id', 'DESC')
			->get()->result();
		return $data;
	}

	public function deleteClassifieds($slug)
	{
		$data = $this->db->select('*')->from('classifieds')->where('classifieds_slug', $slug)->get()->row();

		$img = '';

		if ($data->video_image) {
			$image_link = './' . $data->video_image;

			if (file_exists($image_link)) {
				$img = $image_link;
			} else {
				$img = '';
			}
		}

		if ($this->db->where('classifieds_slug', $slug)->delete('classifieds')) {
			if ($img != '') {
				unlink($img);
				return true;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	/*==========================
        Apartments & Rentals    
    ==========================*/
	public function getRental($logged_id = NULL, $offset = NULL, $limit = NULL)
	{

		return $data = $this->db->select('*')
			->from('rental')
			->where('rental.user_id', $logged_id)
			->join('rental_categories as categories', 'categories.category_id = rental.rental_category_id', 'LEFT')
			->order_by('rental_id', 'DESC')
			->limit($offset, $limit)
			->get()->result();
	}

	public function getRentals4($limit)
	{
		$data = $this->db->select('rental.*,user.name')
			->from('rental')
			->join('user', 'rental.user_id = user.user_id')
			->where('rental.rental_status', 1)
			->limit($limit)
			->order_by('rental_id', 'DESC')
			->get()->result();
		return $data;
	}

	/**
	 * Get related rentals excluding the current rental.
	 * If a category_id is provided, try to fetch from same category first;
	 * if not enough results, fill remaining from other rentals.
	 *
	 * @param int $exclude_rental_id
	 * @param int|null $category_id
	 * @param int $limit
	 * @return array
	 */
	public function getRelatedRentals($exclude_rental_id, $category_id = null, $limit = 3)
	{
		$results = array();

		if (!empty($category_id)) {
			$this->db->select('rental.*')
				->from('rental')
				->where('rental.rental_status', 1)
				->where('rental.is_deleted', 0)
				->where('rental.rental_category_id', $category_id)
				->where('rental.rental_id !=', $exclude_rental_id)
				->order_by('rental.created_at', 'DESC')
				->limit($limit);

			$query = $this->db->get();
			$results = $query->result();
		}

		// If not enough related rentals found by category, fetch more excluding current
		if (count($results) < $limit) {
			$exclude_ids = array($exclude_rental_id);
			foreach ($results as $r) $exclude_ids[] = $r->rental_id;

			$needed = $limit - count($results);
			$this->db->select('rental.*')
				->from('rental')
				->where('rental.rental_status', 1)
				->where('rental.is_deleted', 0)
				->where_not_in('rental.rental_id', $exclude_ids)
				->order_by('rental.created_at', 'DESC')
				->limit($needed);

			$query2 = $this->db->get();
			$more = $query2->result();

			$results = array_merge($results, $more);
		}

		return $results;
	}
	/*=====================================
		Get Rental Category Information
	=====================================*/

	public function allRentalCategory()
	{
		return $data = $this->db->select('*')
			->from('rental_categories')
			->where('category_status', 1)
			->order_by('category_name', 'ASC')
			->get()->result();
	}

	public function deleteRental($slug)
	{
		$data = $this->db->select('*')->from('rental')->where('rental_slug', $slug)->get()->row();
		$img = '';

		if ($data->video_image) {
			$image_link = './' . $data->video_image;

			if (file_exists($image_link)) {
				$img = $image_link;
			} else {
				$img = '';
			}
		}

		if ($this->db->where('rental_slug', $slug)->delete('rental')) {
			if ($img != '') {
				unlink($img);
				return true;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}



	/*===========
		House
	===========*/

	public function allHouseCategory()
	{
		return $data = $this->db->select('*')
			->from('house_categories')
			->order_by('category_name', 'ASC')
			->get()->result();
	}

	public function getHouse($logged_id = NULL, $offset = NULL, $limit = NULL)
	{
		return $data = $this->db->select('*')
			->from('house')
			->where('house.user_id', $logged_id)
			->join('user', 'user.user_id = house.user_id', 'LEFT')
			->join('house_categories as categories', 'categories.category_id = house.house_category_id', 'LEFT')
			->order_by('house_id', 'DESC')
			->limit($limit, $offset)
			->get()->result();
	}

	/**
	 * Count user house posts
	 */
	public function countUserHouses($logged_id = NULL)
	{
		return $this->db->select('COUNT(*) as total')
			->from('house')
			->where('house.user_id', $logged_id)
			->get()->row()->total;
	}

	/**
	 * Get user houses with pagination and category info
	 */
	public function getHouses($logged_id, $limit = 10, $offset = 0)
	{
		return $this->db->select('house.*, house_categories.category_name as category_name')
			->from('house')
			->join('house_categories', 'house.house_category_id = house_categories.category_id', 'left')
			->where('house.user_id', $logged_id)
			->order_by('house_id', 'DESC')
			->limit($limit, $offset)
			->get()->result();
	}

	public function deleteHouse($slug)
	{

		$data = $this->db->select('*')->from('house')->where('house_slug', $slug)->get()->row();

		$img = '';

		if ($data->video_image) {
			$image_link = './' . $data->house_image;

			if (file_exists($image_link)) {
				$img = $image_link;
			} else {
				$img = '';
			}
		}


		if ($this->db->where('house_slug', $slug)->delete('house')) {
			if ($img != '') {
				unlink($img);
				return true;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	/*==========================
	   NEW HOME PAGE METHODS
	===========================*/

	// Get latest listings for home page
	public function getLatestListings($limit = 6)
	{
		// Build query step by step to ensure it works
		$this->db->select('listings.listing_id, listings.listing_name, listings.listing_description, listings.listing_image, listings.category_id, listings.listing_slug, listings.created_at, listings.featured_listing, listings.spotlight_listing, listing_categories.category_name');
		$this->db->from('listings');
		$this->db->join('listing_categories', 'listings.category_id = listing_categories.category_id', 'LEFT');

		// Status filters - Use 0 instead of 1 based on debug data
		$this->db->where('listings.listing_status', 1);   // 1 = published; see applyPublishedListingFilter()
		$this->db->where('listings.is_deleted', 0);		// Exclude featured and spotlight listings
		$this->db->where('(listings.featured_listing IS NULL OR listings.featured_listing = 0)');
		$this->db->where('(listings.spotlight_listing IS NULL OR listings.spotlight_listing = 0)');

		// Order by created_at if available, otherwise listing_id
		if ($this->db->field_exists('created_at', 'listings')) {
			$this->db->order_by('listings.created_at', 'DESC');
		} else {
			$this->db->order_by('listings.listing_id', 'DESC');
		}

		$this->db->limit($limit);

		return $this->db->get()->result();
	}

	// Get featured listings for home page
	public function getFeaturedListings($limit = 2)
	{
		// Build query step by step for featured and spotlight listings
		$this->db->select('listings.listing_id, listings.listing_name, listings.listing_description, listings.listing_image, listings.category_id, listings.listing_slug, listings.spotlight_listing, listings.featured_listing, listings.created_at, listing_categories.category_name');
		$this->db->from('listings');
		$this->db->join('listing_categories', 'listings.category_id = listing_categories.category_id', 'LEFT');

		// Status filters - Use 0 instead of 1 based on debug data
		$this->db->where('listings.listing_status', 1);   // 1 = published; see applyPublishedListingFilter()
		$this->db->where('listings.is_deleted', 0);		// Include featured OR spotlight listings
		$this->db->group_start();
		$this->db->where('listings.featured_listing', 1);
		$this->db->or_where('listings.spotlight_listing', 1);
		$this->db->group_end();

		// Order by created_at if available, otherwise listing_id
		if ($this->db->field_exists('created_at', 'listings')) {
			$this->db->order_by('listings.created_at', 'DESC');
		} else {
			$this->db->order_by('listings.listing_id', 'DESC');
		}

		$this->db->limit($limit);

		return $this->db->get()->result();
	}

	// Get latest videos for home page
	public function getLatestVideos($limit = 4)
	{
		return $this->db->select('video.video_id, video.video_title, video.video_code, video.video_category_id, video.video_description, video.video_image, video.video_slug')
			->from('video')
			->where('video.video_status', 1)
			->order_by('video.video_id', 'DESC')
			->limit($limit)
			->get()->result();
	}

	/**
	 * Latest published stories for the homepage.
	 *
	 * Selects the slug and the date as well as the body: the homepage links
	 * stories by id, which routes, but /news-details/<slug> is the canonical
	 * URL the rest of the site (and the sitemap, and the JSON-LD) uses, and
	 * the news band shows a date on every card.
	 *
	 * It also honours `is_deleted`, which this query used to ignore — a
	 * soft-deleted story stayed on the homepage until someone noticed.
	 */
	public function getLatestNews($limit = 5)
	{
		return $this->db->select('news.news_id, news.news_title, news.news_description, news.news_image,
		                          news.news_slug, news.created_at, news.news_category_id,
		                          categories.category_name, categories.category_slug', false)
			->from('news')
			->join('news_categories as categories', 'categories.category_id = news.news_category_id', 'LEFT')
			->where('news.is_deleted', 0)
			->where('news.news_status', 1)
			->order_by('news.news_id', 'DESC')
			->limit($limit)
			->get()->result();
	}

	// Get category name by category ID
	public function getCategoryName($category_id, $table = 'listing_categories', $id_field = 'category_id')
	{
		$result = $this->db->select('category_name')
			->from($table)
			->where($id_field, $category_id)
			->limit(1)
			->get()->row();
		return $result ? $result->category_name : 'General';
	}

	/**
	 * Heading for a category page. Resolved through resolveCategoryIdBySlug()
	 * so the title matches the listings underneath it — the SQL fallback this
	 * replaces used a different slug rule from the one the links are built
	 * with, so a punctuated category showed its businesses under "General".
	 */
	/**
	 * Heading for a category slug, or '' when the slug matches no category.
	 *
	 * Returns '' rather than the old 'General'. A slug that resolves to
	 * nothing also makes directoryQuery() add `where('1 = 0')`, so the page
	 * showed the heading "General" over an empty list and looked like a real,
	 * empty section — which is how a stale "Sports and Health" link read as
	 * "it redirects to the General section". The caller decides what to do
	 * with a miss; User::all_listing() now says the category was not found.
	 */
	/**
	 * The category id for a slug, or NULL. Public wrapper over
	 * resolveCategoryIdBySlug() so controllers can hand the id to the views
	 * that need it (category-targeted ad bookings, migration 0024) without
	 * each of them re-deriving it with a different slug rule.
	 */
	/**
	 * A whole category row for a slug, or NULL.
	 *
	 * Used by the top menu, which needs the name for the link text as well as
	 * the slug for the href.
	 */
	public function getCategoryBySlug($category_slug, $table = 'listing_categories')
	{
		$id = $this->resolveCategoryIdBySlug($category_slug, $table);
		if ($id === NULL) return NULL;

		return $this->db->select('category_id, category_name, category_slug')
			->from($table)
			->where('category_id', $id)
			->limit(1)
			->get()->row();
	}

	public function categoryIdBySlug($category_slug, $table = 'listing_categories')
	{
		return $this->resolveCategoryIdBySlug($category_slug, $table);
	}

	public function getCategoryNameBySlug($category_slug, $table = 'listing_categories')
	{
		$cat_id = $this->resolveCategoryIdBySlug($category_slug, $table);
		if ($cat_id === NULL) return '';

		return $this->getCategoryName($cat_id, $table);
	}

	public function getCategorySlugById($category_id, $table = 'listing_categories')
	{
		$result = $this->db->select('category_slug, category_name')
			->from($table)
			->where('category_id', $category_id)
			->limit(1)
			->get()->row();

		if ($result) {
			// If slug exists and is not empty, return it
			if (!empty($result->category_slug)) {
				return $result->category_slug;
			}
			// If slug is empty but we have category name, generate slug from
			// name — via the shared helper, so the URL handed out here is the
			// one resolveCategoryIdBySlug() will resolve back to this row.
			if (!empty($result->category_name)) {
				if (!function_exists('pearland_slug')) $this->load->helper('view');
				return pearland_slug($result->category_name);
			}
		}

		return null;
	}

	public function getCategoryIdBySlug($category_slug, $table = 'listing_categories')
	{
		return $this->resolveCategoryIdBySlug($category_slug, $table);
	}

	public function getCategoryBySlugOrId($identifier, $table = 'listing_categories')
	{
		// Try as ID first if numeric
		if (is_numeric($identifier)) {
			$result = $this->db->select('*')
				->from($table)
				->where('category_id', (int) $identifier)
				->limit(1)
				->get()->row();
			if ($result) return $result;
		}

		// Stored slug, then the name, both via the shared resolver.
		$cat_id = $this->resolveCategoryIdBySlug($identifier, $table);
		if ($cat_id === NULL) return NULL;

		return $this->db->select('*')
			->from($table)
			->where('category_id', $cat_id)
			->limit(1)
			->get()->row();
	}

	// Debug method to check categories and their slugs
	public function debugCategories()
	{
		$categories = $this->db->select('category_id, category_name, category_slug')
			->from('listing_categories')
			->get()->result();

		$debug_info = [];
		foreach ($categories as $cat) {
			$debug_info[] = [
				'id' => $cat->category_id,
				'name' => $cat->category_name,
				'slug' => $cat->category_slug,
				// The slug the links actually use when category_slug is empty.
				'generated_slug' => pearland_slug($cat->category_name)
			];
		}
		return $debug_info;
	}

	/*==========================
	   SUBSCRIPTION LIMIT METHODS
	===========================*/

	// Get user's current subscription plan
	public function getUserSubscription($user_id)
	{
		// `s.*, p.*` alone let the PLAN's id overwrite the subscription's, and
		// every caller that writes back by `$sub->id` — cancel auto-renew,
		// upgrade, scheduled downgrade, usage tracking — then wrote to the
		// subscription whose id happened to equal the plan id: someone else's,
		// or none. The plan's id is `plan_id` on the subscription row.
		$query = $this->db->select('s.*, p.*, s.id AS id', FALSE)
			->from('subscriptions s')
			->join('plans p', 's.plan_id = p.id')
			->where('s.user_id', $user_id)
			->where('s.status', 'active')
			->where('s.end_date >=', date('Y-m-d'))
			->order_by('s.id', 'DESC')
			->limit(1)
			->get();

		return $query->row();
	}

	// Get user's current usage for a specific content type
	public function getUserUsage($user_id, $content_type)
	{
		$column_map = [
			'listing' => 'listing_used',
			'product' => 'product_used',
			'event' => 'event_used',
			'classified' => 'classified_used',
			'rental' => 'rental_used',
			'house' => 'house_used',
			'blog' => 'blog_used',
			'video' => 'video_used',
			'ad_boost' => 'ad_boost_used',
			'news' => 'news_used',
			'ad' => 'ad_used',
			'placeholder_images' => 'placeholder_images_used'
		];

		$column = isset($column_map[$content_type]) ? $column_map[$content_type] : 'listing_used';

		$query = $this->db->select($column)
			->from('user_usage_tracking')
			->where('user_id', $user_id)
			->get();

		$result = $query->row();
		return $result ? $result->$column : 0;
	}

	// Check if user can add more content of specific type
	public function canUserAddContent($user_id, $content_type)
	{
		$subscription = $this->getUserSubscription($user_id);

		if (!$subscription) {
			// No active subscription - use default limits or deny
			return false;
		}

		$current_usage = $this->getUserUsage($user_id, $content_type);

		// Get the limit for the content type
		$limit_field = $content_type . '_limit';
		$limit = isset($subscription->$limit_field) ? $subscription->$limit_field : 0;

		// -1 and 0 both mean unlimited.
		//
		// They have to agree with the dashboards, which have always read
		// `($limit === -1 || $limit === 0)` as unlimited. This method used to
		// treat 0 as "none allowed", so a plan row with 0 in its *_limit column
		// — the column default — made canUserAddContent() refuse every add
		// while the page it came from said the allowance was unlimited. That is
		// "cannot add more news" and "cannot add more events" with nothing on
		// screen to explain it. A plan that genuinely allows nothing is
		// expressed by not granting the capability, not by a 0 quota.
		if ($limit == -1 || $limit == 0) {
			return true;
		}

		return $current_usage < $limit;
	}

	// Update user usage when they add new content
	public function updateUserUsage($user_id, $content_type)
	{
		$column_map = [
			'listing' => 'listing_used',
			'product' => 'product_used',
			'event' => 'event_used',
			'classified' => 'classified_used',
			'rental' => 'rental_used',
			'house' => 'house_used',
			'blog' => 'blog_used',
			'video' => 'video_used',
			'ad_boost' => 'ad_boost_used',
			'news' => 'news_used',
			'ad' => 'ad_used',
			'placeholder_images' => 'placeholder_images_used'
		];

		$column = isset($column_map[$content_type]) ? $column_map[$content_type] : 'listing_used';

		$existing = $this->db->select('id, ' . $column)
			->from('user_usage_tracking')
			->where('user_id', $user_id)
			->get()
			->row();

		if ($existing) {
			// Update existing record
			$this->db->where('id', $existing->id)
				->update('user_usage_tracking', [
					$column => $existing->$column + 1,
					'updated_at' => date('Y-m-d H:i:s')
				]);
		} else {
			// Insert new record
			$subscription = $this->getUserSubscription($user_id);
			$subscription_id = $subscription ? $subscription->id : null;

			$this->db->insert('user_usage_tracking', [
				'user_id' => $user_id,
				'subscription_id' => $subscription_id,
				'content_type' => 'general',
				$column => 1,
				'reset_date' => date('Y-m-d', strtotime('+30 days')),
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			]);
		}
	}

	// Get subscription limits for display
	public function getSubscriptionLimits($user_id)
	{
		$subscription = $this->getUserSubscription($user_id);

		if (!$subscription) {
			return null;
		}

		$limits = [
			'plan_name' => $subscription->name,
			'listing' => $subscription->listing_limit,
			'product' => $subscription->product_limit,
			'event' => $subscription->event_limit,
			'classified' => $subscription->classified_limit,
			'blog' => $subscription->blog_limit,
			'news' => $subscription->news_limit,
			'video' => $subscription->video_limit,
			'rental' => $subscription->rental_limit,
			'house' => $subscription->house_limit,
			'ad' => $subscription->ad_limit,
			'ad_boost' => $subscription->ad_boost_limit,
			'placeholder_images' => $subscription->placeholder_images_limit
		];

		return $limits;
	}

	// Get current usage counts for all content types
	public function getAllUsageCounts($user_id)
	{
		$query = $this->db->select('listing_used, product_used, event_used, classified_used, rental_used, house_used, blog_used, video_used, ad_boost_used, news_used, ad_used, placeholder_images_used')
			->from('user_usage_tracking')
			->where('user_id', $user_id)
			->get();

		$usage = [];
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$usage['listing'] = $row->listing_used;
			$usage['product'] = $row->product_used;
			$usage['event'] = $row->event_used;
			$usage['classified'] = $row->classified_used;
			$usage['rental'] = $row->rental_used;
			$usage['house'] = $row->house_used;
			$usage['blog'] = $row->blog_used;
			$usage['video'] = $row->video_used;
			$usage['ad_boost'] = $row->ad_boost_used;
			$usage['news'] = $row->news_used;
			$usage['ad'] = $row->ad_used;
			$usage['placeholder_images'] = $row->placeholder_images_used;
		}

		return $usage;
	}

	/*==========================
		Payment & Subscription Methods
	===========================*/

	// Insert payment card
	public function insertPaymentCard($card_data)
	{
		if ($this->db->insert('payment_cards', $card_data)) {
			return $this->db->insert_id();
		}
		return false;
	}

	// Create subscription - FIXED VERSION
	public function create_subscription($user_id, $plan_id, $payment_card_id, $billing_data)
	{
		// Get plan details
		$plan = $this->getPlanById($plan_id);
		if (!$plan) {
			return array('status' => false, 'message' => 'Invalid plan selected');
		}

		// Start transaction
		$this->db->trans_start();

		try {
			// 1. Insert subscription (FIXED: removed subscription_id from INSERT)
			$subscription_data = array(
				'user_id' => $user_id,
				'plan_id' => $plan_id,
				'payment_card_id' => $payment_card_id,
				'status' => 'active',
				'start_date' => date('Y-m-d'),
				'end_date' => date('Y-m-d', strtotime('+' . $plan->duration_months . ' months')),
				'amount' => $plan->price,
				'currency' => 'USD',
				'billing_cycle' => $plan->billing_cycle,
				'auto_renewal' => 1,
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			);

			$this->db->insert('subscriptions', $subscription_data);
			$subscription_id = $this->db->insert_id();

			if (!$subscription_id) {
				throw new Exception('Failed to create subscription');
			}

			// 2. Insert transaction record
			$transaction_data = array(
				'subscription_id' => $subscription_id,
				'user_id' => $user_id,
				'amount' => $plan->price,
				'currency' => 'USD',
				'payment_method' => 'credit_card',
				'transaction_type' => 'subscription_payment',
				'status' => 'completed',
				'gateway_transaction_id' => 'sim_' . uniqid(),
				'gateway_response' => json_encode(['status' => 'success', 'message' => 'Payment simulated']),
				'billing_name' => $billing_data['billing_name'],
				'billing_email' => $billing_data['billing_email'],
				'billing_address' => $billing_data['billing_address'],
				'billing_city' => $billing_data['billing_city'],
				'billing_state' => $billing_data['billing_state'],
				'billing_zip' => $billing_data['billing_zip'],
				'billing_country' => $billing_data['billing_country'],
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			);

			$this->db->insert('transactions', $transaction_data);
			$transaction_id = $this->db->insert_id();

			if (!$transaction_id) {
				throw new Exception('Failed to create transaction record');
			}

			// 3. Initialize user usage tracking
			$usage_data = array(
				'user_id' => $user_id,
				'subscription_id' => $subscription_id,
				'content_type' => 'general',
				'listing_used' => 0,
				'product_used' => 0,
				'event_used' => 0,
				'classified_used' => 0,
				'rental_used' => 0,
				'house_used' => 0,
				'blog_used' => 0,
				'video_used' => 0,
				'ad_boost_used' => 0,
				'news_used' => 0,
				'ad_used' => 0,
				'placeholder_images_used' => 0,
				'reset_date' => date('Y-m-d', strtotime('+30 days')),
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			);

			$this->db->insert('user_usage_tracking', $usage_data);

			// Complete transaction
			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				throw new Exception('Transaction failed');
			}

			return array(
				'status' => true,
				'message' => 'Subscription created successfully',
				'subscription_id' => $subscription_id,
				'transaction_id' => $transaction_id,
				'plan_name' => $plan->name,
				'amount' => $plan->price
			);
		} catch (Exception $e) {
			$this->db->trans_rollback();
			error_log('Subscription creation failed: ' . $e->getMessage());
			return array(
				'status' => false,
				'message' => 'Failed to create subscription: ' . $e->getMessage()
			);
		}
	}

	// Insert subscription
	public function insertSubscription($subscription_data)
	{
		if ($this->db->insert('subscriptions', $subscription_data)) {
			return $this->db->insert_id();
		}
		return false;
	}

	// Insert transaction
	public function insertTransaction($transaction_data)
	{
		if ($this->db->insert('transactions', $transaction_data)) {
			return $this->db->insert_id();
		}
		return false;
	}

	/*********************
		SEARCH FUNCTIONS
	 **********************/

	/**
	 * Split what someone typed into the words it is actually made of.
	 *
	 * Every search here used to send the whole box straight to `LIKE`, so
	 * "central bbq" became `listing_name LIKE '%central bbq%'` and never found
	 * "Central Texas BBQ" — the two words are real, they just are not adjacent.
	 * A search box is expected to work word by word.
	 *
	 * Punctuation is dropped, words are lower-cased and de-duplicated, and the
	 * list is capped: each word costs another LIKE across every column, so a
	 * pasted paragraph would otherwise build a hundred-clause query. Words of
	 * one character are skipped for the same reason — they match nearly every
	 * row — unless that is all there is.
	 *
	 * @return string[] possibly empty, in which case the caller must not search
	 */
	private function searchTerms($search_term, $max_terms = 6)
	{
		$clean = preg_replace('/[^\p{L}\p{N}]+/u', ' ', (string) $search_term);
		$parts = preg_split('/\s+/', trim((string) $clean), -1, PREG_SPLIT_NO_EMPTY);
		if (empty($parts)) {
			return array();
		}

		$lower = function ($s) {
			return function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s);
		};
		$len = function ($s) {
			return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s);
		};

		$terms = array();
		foreach ($parts as $part) {
			if ($len($part) < 2) continue;
			$terms[$lower($part)] = true;
			if (count($terms) >= $max_terms) break;
		}

		// "BJ" or a single initial is still worth looking for on its own.
		if (empty($terms)) {
			$terms[$lower($parts[0])] = true;
		}

		return array_keys($terms);
	}

	/**
	 * The forms a typed word may take in the data.
	 *
	 * Searching is `LIKE '%word%'`, so a shorter form covers a longer one:
	 * `%restaurant%` finds "restaurant" and "restaurants" both, and asking for
	 * the plural as well would only cost a clause. So most of the time this
	 * returns the STEM on its own, and the list is longer than one entry only
	 * where the singular is not a substring of the plural ("party"/"parties").
	 *
	 * This is why /search?q=restaurants returned an electrician and no
	 * restaurants: the only rows containing the literal string "restaurants"
	 * were the ones that mention serving them, while every actual restaurant
	 * writes "restaurant", "Restaurant" or nothing at all.
	 *
	 * The stem has to be at least four characters. "news" would otherwise fold
	 * to "new" and match every renewal and newsletter on the site, which is the
	 * opposite of the problem being fixed. That leaves short plurals ("cars",
	 * "pets") unfolded, and they stay that way: a three-letter stem matches too
	 * much to be worth it.
	 *
	 * @param  string $term one word, already lower-cased by searchTerms()
	 * @return string[] at least the term itself
	 */
	private function searchTermVariants($term)
	{
		$term = (string) $term;
		$len  = function ($s) {
			return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s);
		};

		$stem = NULL;
		$also = NULL;

		// Most specific ending first: "classes" has to be read as -sses before
		// the bare -s rule gets to it and leaves "classe", which matches
		// nothing at all. The four-character floor below is the only length
		// test that matters, so these only ask for enough word to test.
		if ($len($term) >= 5 && substr($term, -3) === 'ies') {
			// parties -> party, and keep parties: neither contains the other.
			$stem = substr($term, 0, -3) . 'y';
			$also = $term;
		} elseif ($len($term) >= 5 && preg_match('/(sses|ches|shes|xes|zes|ses)$/', $term)) {
			// services -> service, classes -> class, churches -> church.
			$stem = substr($term, 0, -2);
		} elseif ($len($term) >= 5 && substr($term, -1) === 's' && !preg_match('/(ss|us|is|os)$/', $term)) {
			// restaurants -> restaurant, plumbers -> plumber.
			$stem = substr($term, 0, -1);
		} elseif ($len($term) >= 5 && substr($term, -1) === 'y' && !preg_match('/[aeiou]y$/', $term)) {
			// bakery -> bakeries. The singular does not contain the plural.
			$stem = $term;
			$also = substr($term, 0, -1) . 'ies';
		}

		if ($stem === NULL || $len($stem) < 4) {
			return array($term);
		}

		$out = array($stem);
		if ($also !== NULL && $also !== $stem) $out[] = $also;

		return $out;
	}

	/**
	 * One column matching one word, as a raw SQL fragment.
	 *
	 * A word of one or two letters has to sit on a word boundary. "IT company"
	 * is the reported case: `LIKE '%it%'` is true of Smith, Whitney, furniture,
	 * capital and most of the directory, so ANDing it with "company" still left
	 * a page of businesses that have nothing to do with IT. Separators are
	 * flattened to spaces first so "Pearland IT, Inc." still matches; it is a
	 * stack of REPLACE() rather than a REGEXP because the word-boundary syntax
	 * MySQL understands changed between 5.7 and 8.0 and this has to run on both.
	 *
	 * Three letters and up keep plain substring matching: "spa" should still
	 * find "MedSpa", and the noise a three-letter word brings in is bounded by
	 * the other words it is ANDed with.
	 */
	private function searchColumnCondition($column, $term)
	{
		$term = (string) $term;
		$len  = function_exists('mb_strlen') ? mb_strlen($term, 'UTF-8') : strlen($term);
		$esc  = $this->db->escape_like_str($term);

		if ($len > 2) {
			return 'LOWER(' . $column . ") LIKE '%" . $esc . "%' ESCAPE '!'";
		}

		$flat = 'COALESCE(' . $column . ", '')";
		foreach (array(',', '.', '-', '/', '(', ')') as $ch) {
			$flat = 'REPLACE(' . $flat . ', ' . $this->db->escape($ch) . ", ' ')";
		}

		return "CONCAT(' ', LOWER(" . $flat . "), ' ') LIKE '% " . $esc . " %' ESCAPE '!'";
	}

	/**
	 * AND across the words, OR across the columns and each word's forms:
	 *
	 *   ( (name LIKE %central% OR descr LIKE %central%)
	 *     AND (name LIKE %bbq% OR descr LIKE %bbq%) )
	 *
	 * Every word has to appear somewhere in the row, but not in the same
	 * column and not next to each other — which is what makes "central bbq"
	 * find "Central Texas BBQ".
	 *
	 * Built as one SQL fragment rather than with like()/or_like(): a short word
	 * needs a word-boundary test the query builder cannot express, and mixing
	 * the two inside the same OR group is more error-prone than writing it out.
	 * Everything interpolated goes through escape_like_str()/escape() first, and
	 * searchTerms() has already dropped everything that is not a letter or a
	 * digit before that.
	 *
	 * @param string[] $columns fully-qualified column names
	 * @param string[] $terms   from searchTerms(); must not be empty
	 */
	private function applySearchTerms(array $columns, array $terms)
	{
		if (empty($columns) || empty($terms)) {
			return;
		}

		$and = array();
		foreach ($terms as $term) {
			$or = array();
			foreach ($this->searchTermVariants($term) as $variant) {
				foreach ($columns as $column) {
					$or[] = $this->searchColumnCondition($column, $variant);
				}
			}
			if ($or) $and[] = '(' . implode(' OR ', $or) . ')';
		}

		if ($and) {
			$this->db->where('(' . implode(' AND ', $and) . ')', NULL, FALSE);
		}
	}

	/**
	 * Relevance for a result set, as a raw ORDER BY fragment.
	 *
	 * Two parts. First the title column on its own: an exact match, then one
	 * that starts with what was typed, then one that contains it. Then a score
	 * over every column the caller weights, so WHERE a word matched decides the
	 * order and not just WHETHER it did.
	 *
	 * The weights are the other half of the /search?q=restaurants report. Every
	 * column used to count the same, and the tie-break below the title was the
	 * row id — so a business that merely lists restaurants among its customers
	 * outranked one filed under the Restaurants category, because it happened to
	 * be newer. Pass $weighted as column => weight, highest for the columns an
	 * operator curates (the category) and lowest for free prose (a description,
	 * which is where an incidental mention lives).
	 *
	 * @param string   $column   fully-qualified title column, e.g. 'listings.listing_name'
	 * @param string   $phrase   what the visitor typed, whole
	 * @param string[] $terms    from searchTerms()
	 * @param array    $weighted column => weight for the rest of the row
	 */
	private function searchRelevanceOrder($column, $phrase, array $terms, array $weighted = array())
	{
		$name   = 'LOWER(' . $column . ')';
		$phrase = trim(strtolower((string) $phrase));

		// escape_like_str() neutralises % and _ with a `!` prefix, which only
		// means anything to MySQL alongside an ESCAPE clause — the query
		// builder appends one for us on like(), but these fragments are hand
		// built, so they have to carry their own. Without it a business called
		// "50% Off Tyres" would rank by a pattern containing a literal `!`.
		$like = function ($pattern) use ($name) {
			return $name . " LIKE '" . $pattern . "' ESCAPE '!'";
		};
		$esc = $this->db->escape_like_str($phrase);

		$rank = 'CASE'
			. ' WHEN ' . $name . ' = ' . $this->db->escape($phrase) . ' THEN 0'
			. ' WHEN ' . $like($esc . '%') . ' THEN 1'
			. ' WHEN ' . $like('%' . $esc . '%') . ' THEN 2'
			. ' ELSE 3 END ASC';

		// The title column always carries the most weight; anything the caller
		// names is ranked beneath it.
		$columns = array($column => 8);
		foreach ($weighted as $col => $weight) {
			$weight = (int) $weight;
			if ($col !== $column && $weight > 0) $columns[$col] = $weight;
		}

		$score = array();
		foreach ($columns as $col => $weight) {
			foreach ($terms as $term) {
				$conds = array();
				foreach ($this->searchTermVariants($term) as $variant) {
					$conds[] = $this->searchColumnCondition($col, $variant);
				}
				$score[] = '(CASE WHEN (' . implode(' OR ', $conds) . ') THEN ' . (int) $weight . ' ELSE 0 END)';
			}
		}

		return $rank . ', (' . implode(' + ', $score) . ') DESC';
	}

	/**
	 * Global search across every content type.
	 *
	 * $limit is the cap PER SECTION, and it is honoured — the parameter was
	 * declared and then ignored, with each section hard-coded to a literal
	 * (listings 20, categories 10, …). A query matching 200 businesses showed
	 * 20 and /search had no pager, no "load more" and no offset, so there was
	 * no way to reach the 21st. That is "display all available results, not
	 * just the first 10".
	 *
	 * Each section fetches one row beyond the cap so the caller can tell
	 * "exactly $limit" from "at least $limit, probably more" without paying for
	 * a COUNT per section. The extra row is trimmed off here and recorded in
	 * `has_more`.
	 */
	public function globalSearch($search_term, $limit = 20, $offset = 0)
	{
		$results = array(
			'listings'    => array(),
			'news'        => array(),
			'events'      => array(),
			'blogs'       => array(),
			'products'    => array(),
			'house'       => array(),
			'rental'      => array(),
			'classifieds' => array(),
			'videos'      => array(),
			'categories'  => array(),
			'services'    => array(),
			'coupons'     => array(),
			'total_count' => 0,
		);

		// Nothing usable typed — don't run twelve full-table scans to find out.
		if (empty($search_term) || !$this->searchTerms($search_term)) {
			return $results;
		}

		// Only search sections that are switched on. This page used to offer
		// News, Events, Blogs, Products, Videos, Real Estate and Classifieds
		// results whatever /admin/setting said, so a switched-off section was
		// still reachable from search and every link in it 404'd — and it cost
		// a scan per section to produce them.
		$on = function ($key) {
			return site_setting($key, '1') !== '0';
		};

		$limit = max(1, min(500, (int) $limit));
		$probe = $limit + 1; // one past the cap, to detect "there are more"

		// The core directory, categories, the services catalog and coupons are
		// always on — see the note on /admin/setting.
		$results['listings']   = $this->searchListings($search_term, $probe);
		$results['categories'] = $this->searchCategories($search_term, $probe);
		$results['services']   = $this->searchServices($search_term, $probe);
		$results['coupons']    = $this->searchCoupons($search_term, $probe);

		if ($on('service_news_enabled'))        $results['news']        = $this->searchNews($search_term, $probe);
		if ($on('service_events_enabled'))      $results['events']      = $this->searchEvents($search_term, $probe);
		if ($on('service_blog_enabled'))        $results['blogs']       = $this->searchBlogs($search_term, $probe);
		if ($on('service_products_enabled'))    $results['products']    = $this->searchProducts($search_term, $probe);
		if ($on('service_videos_enabled'))      $results['videos']      = $this->searchVideo($search_term, $probe);
		if ($on('service_classifieds_enabled')) $results['classifieds'] = $this->searchClassifieds($search_term, $probe);
		if ($on('service_realestate_enabled')) {
			$results['house']  = $this->searchHouse($search_term, $probe);
			$results['rental'] = $this->searchRental($search_term, $probe);
		}

		// Trim the probe row back off and remember which sections were cut, so
		// the page can offer "show more" instead of silently stopping.
		$results['has_more'] = array();
		foreach ($results as $section => $rows) {
			if (!is_array($rows) || $section === 'has_more') continue;
			if (count($rows) > $limit) {
				$results['has_more'][$section] = TRUE;
				$results[$section] = array_slice($rows, 0, $limit);
			}
		}

		$results['limit'] = $limit;

		// Calculate total count
		$results['total_count'] = count($results['listings']) + count($results['news']) +
			count($results['events']) + count($results['blogs']) +
			count($results['products']) + count($results['house']) +
			count($results['rental']) + count($results['classifieds']) +
			count($results['videos']) + count($results['categories']) +
			count($results['services']) + count($results['coupons']);

		return $results;
	}

	public function searchServices($search_term, $limit = 10)
	{
		if (!$this->db->table_exists('services')) return array();
		$terms = $this->searchTerms($search_term);
		if (empty($terms)) return array();

		$this->db->from('services')->where('is_active', 1);
		$this->applySearchTerms(array('name', 'short_description', 'category', 'body'), $terms);

		return $this->db
			->order_by($this->searchRelevanceOrder('name', $search_term, $terms), '', false)
			->order_by('sort_order', 'ASC')
			->limit((int) $limit)
			->get()
			->result();
	}

	public function searchCoupons($search_term, $limit = 10)
	{
		$terms = $this->searchTerms($search_term);
		if (empty($terms)) return array();

		$today = date('Y-m-d');
		$this->db->from('coupons')
			->where('coupon_status', 1)
			->where('coupon_start_date <=', $today)
			->where('coupon_end_date >=',  $today);
		$this->applySearchTerms(array('coupon_name', 'coupon_code'), $terms);

		return $this->db
			->order_by($this->searchRelevanceOrder('coupon_name', $search_term, $terms), '', false)
			->order_by('coupon_cdt', 'DESC')
			->limit((int) $limit)
			->get()
			->result();
	}

	/**
	 * Search in listings table
	 */
	public function searchListings($search_term, $limit = 20)
	{
		$terms = $this->searchTerms($search_term);
		if (empty($terms)) return array();

		$this->db->select('listings.*, listing_categories.category_name, user.name as owner_name');
		$this->db->from('listings');
		$this->db->join('listing_categories', 'listings.category_id = listing_categories.category_id', 'left');
		$this->db->join('user', 'listings.user_id = user.user_id', 'left');
		$this->db->where('listings.is_deleted', 0);
		$this->db->where('listings.listing_status', 1);

		// service_offered is searched too: a barbecue place that does not say
		// "catering" in its description usually says it here.
		$this->applySearchTerms(array(
			'listings.listing_name',
			'listings.listing_description',
			'listings.listing_address',
			'listings.service_locations',
			'listings.service_offered',
			'listing_categories.category_name',
		), $terms);

		// Where the word was found decides the order. The category an operator
		// filed the business under is the strongest signal after the name; the
		// description is the weakest, because that is where "we also serve
		// restaurants and offices" lives — the sentence that used to put an
		// electrician at the top of a search for restaurants.
		$this->db->order_by($this->searchRelevanceOrder(
			'listings.listing_name',
			$search_term,
			$terms,
			array(
				'listing_categories.category_name' => 6,
				'listings.service_offered'         => 4,
				'listings.service_locations'       => 2,
				'listings.listing_address'         => 2,
				'listings.listing_description'     => 1,
			)
		), '', false);
		$this->db->order_by('COALESCE(listings.featured_listing, 0) DESC, listings.listing_id DESC', '', false);
		$this->db->limit($limit);

		return $this->db->get()->result();
	}

	/**
	 * Search in news table
	 */
	public function searchNews($search_term, $limit = 20)
	{
		$terms = $this->searchTerms($search_term);
		if (empty($terms)) return array();

		$this->db->select('news.*, ' . $this->bylineSelect('news', 'news_byline') . ' as author_name', FALSE);
		$this->db->from('news');
		$this->db->join('user', 'news.user_id = user.user_id', 'left');
		$this->db->where('news.news_status', 1);
		$this->applySearchTerms(array('news.news_title', 'news.news_description'), $terms);
		$this->db->order_by($this->searchRelevanceOrder('news.news_title', $search_term, $terms), '', false);
		$this->db->order_by('news.news_id', 'DESC');
		$this->db->limit($limit);

		return $this->db->get()->result();
	}

	/**
	 * Search in events table
	 */
	public function searchEvents($search_term, $limit = 15)
	{
		$terms = $this->searchTerms($search_term);
		if (empty($terms)) return array();

		$this->db->select('events.*, COALESCE(admin.admin_name, user.name) as organizer_name', false);
		$this->db->from('events');
		$this->db->join('user', 'events.user_id = user.user_id', 'left');
		$this->db->join('admin', 'admin.admin_id = events.admin_id', 'left');
		$this->db->where('events.event_status', 1);
		$this->applySearchTerms(array('events.event_name', 'events.event_description', 'events.event_address'), $terms);
		$this->db->order_by($this->searchRelevanceOrder('events.event_name', $search_term, $terms), '', false);
		$this->db->order_by('events.event_id', 'DESC');
		$this->db->limit($limit);

		return $this->db->get()->result();
	}

	/**
	 * Search in blogs table.
	 *
	 * Was the odd one out: it matched the title only, and unpublished drafts
	 * turned up in the results. Both are fixed here.
	 *
	 * $limit stays optional and defaults to no limit. /all-blog?q= slices this
	 * result set in PHP to paginate (User::all_blog), so capping it by default
	 * would silently truncate that page to one or two pages; the site-wide
	 * search passes an explicit 15 like every other section.
	 */
	public function searchBlogs($search_term, $limit = null)
	{
		$terms = $this->searchTerms($search_term);
		if (empty($terms)) return array();

		$this->db->select('*');
		$this->db->from('blogs');
		$this->db->where('blog_status', 1);
		$this->applySearchTerms(array('blog_name', 'blog_description'), $terms);
		$this->db->order_by($this->searchRelevanceOrder('blog_name', $search_term, $terms), '', false);
		$this->db->order_by('blog_id', 'DESC');
		if ($limit !== null) {
			$this->db->limit((int) $limit);
		}

		return $this->db->get()->result();
	}

	/**
	 * Search in products table
	 */
	public function searchProducts($search_term, $limit = 15)
	{
		$terms = $this->searchTerms($search_term);
		if (empty($terms)) return array();

		$this->db->select('products.*, user.name as seller_name');
		$this->db->from('products');
		$this->db->join('user', 'products.user_id = user.user_id', 'left');
		$this->db->where('products.product_status', 1);
		$this->applySearchTerms(array('products.product_name', 'products.product_description'), $terms);
		$this->db->order_by($this->searchRelevanceOrder('products.product_name', $search_term, $terms), '', false);
		$this->db->order_by('products.product_id', 'DESC');
		$this->db->limit($limit);

		return $this->db->get()->result();
	}

	/**
	 * Search in categories table
	 *
	 * listing_count comes from a correlated subquery rather than a JOIN +
	 * GROUP BY. Two reasons: it counts exactly what the category page will
	 * show (see applyPublishedListingFilter — this used to filter only
	 * is_deleted and so over-reported), and selecting `listing_categories.*`
	 * alongside a GROUP BY on one column is rejected outright by MySQL 5.7+
	 * under the default ONLY_FULL_GROUP_BY.
	 */
	public function searchCategories($search_term, $limit = 10)
	{
		$terms = $this->searchTerms($search_term);
		if (empty($terms)) return array();

		$this->db->select('listing_categories.*', false);
		$this->db->select(
			'(SELECT COUNT(*) FROM `listings` l'
			. ' WHERE l.category_id = listing_categories.category_id'
			. ' AND ' . $this->publishedListingJoinCondition('l')
			. ') AS listing_count',
			false
		);
		$this->db->from('listing_categories');
		$this->applySearchTerms(array('listing_categories.category_name', 'listing_categories.category_description'), $terms);
		$this->db->order_by($this->searchRelevanceOrder('listing_categories.category_name', $search_term, $terms), '', false);
		$this->db->order_by('listing_categories.category_id', 'DESC');
		$this->db->limit($limit);

		return $this->db->get()->result();
	}

	/**
	 * Search in house table
	 */
	public function searchHouse($search_term, $limit = 15)
	{
		$terms = $this->searchTerms($search_term);
		if (empty($terms)) return array();

		$this->db->select('house.*, user.name as owner_name');
		$this->db->from('house');
		$this->db->join('user', 'house.user_id = user.user_id', 'left');
		$this->db->where('house.house_status', 0); // 0 = active based on database
		$this->applySearchTerms(array('house.house_title', 'house.house_description', 'house.house_address'), $terms);
		$this->db->order_by($this->searchRelevanceOrder('house.house_title', $search_term, $terms), '', false);
		$this->db->order_by('house.house_id', 'DESC');
		$this->db->limit($limit);

		return $this->db->get()->result();
	}

	/**
	 * Search in rental table
	 */
	public function searchRental($search_term, $limit = 15)
	{
		$terms = $this->searchTerms($search_term);
		if (empty($terms)) return array();

		$this->db->select('rental.*, user.name as owner_name');
		$this->db->from('rental');
		$this->db->join('user', 'rental.user_id = user.user_id', 'left');
		$this->db->where('rental.rental_status', 0); // 0 = active based on database
		$this->applySearchTerms(array('rental.rental_title', 'rental.rental_description', 'rental.rental_address'), $terms);
		$this->db->order_by($this->searchRelevanceOrder('rental.rental_title', $search_term, $terms), '', false);
		$this->db->order_by('rental.rental_id', 'DESC');
		$this->db->limit($limit);

		return $this->db->get()->result();
	}

	/**
	 * Search in classifieds table
	 */
	public function searchClassifieds($search_term, $limit = 15)
	{
		$terms = $this->searchTerms($search_term);
		if (empty($terms)) return array();

		$this->db->select('classifieds.*, user.name as poster_name');
		$this->db->from('classifieds');
		$this->db->join('user', 'classifieds.user_id = user.user_id', 'left');
		$this->db->where('classifieds.classifieds_status', 1); // 1 = active based on database
		$this->applySearchTerms(array('classifieds.classifieds_title', 'classifieds.classifieds_description'), $terms);
		$this->db->order_by($this->searchRelevanceOrder('classifieds.classifieds_title', $search_term, $terms), '', false);
		$this->db->order_by('classifieds.classifieds_id', 'DESC');
		$this->db->limit($limit);

		return $this->db->get()->result();
	}

	/**
	 * Search in video table
	 */
	public function searchVideo($search_term, $limit = 15)
	{
		$terms = $this->searchTerms($search_term);
		if (empty($terms)) return array();

		$this->db->select('video.*, user.name as uploader_name');
		$this->db->from('video');
		$this->db->join('user', 'video.user_id = user.user_id', 'left');
		$this->db->where('video.video_status', 1); // 1 = active based on database
		$this->applySearchTerms(array('video.video_title', 'video.video_description'), $terms);
		$this->db->order_by($this->searchRelevanceOrder('video.video_title', $search_term, $terms), '', false);
		$this->db->order_by('video.video_id', 'DESC');
		$this->db->limit($limit);

		return $this->db->get()->result();
	}

	/**
	 * Get search suggestions for autocomplete
	 */
	public function getSearchSuggestions($search_term, $limit = 10)
	{
		// Same word-by-word matching as the results page, so the dropdown
		// cannot suggest nothing for a query the search page would answer.
		$terms = $this->searchTerms($search_term);
		if (empty($terms)) {
			return array();
		}

		// Get suggestions from listings
		$this->db->select('listing_name as suggestion, "listing" as type, listing_id as id');
		$this->db->from('listings');
		$this->db->where('listing_status', 1);
		$this->db->where('is_deleted', 0);
		$this->applySearchTerms(array('listing_name'), $terms);
		$this->db->order_by($this->searchRelevanceOrder('listing_name', $search_term, $terms), '', false);
		$this->db->limit(5);
		$listings = $this->db->get()->result();

		// Get suggestions from categories
		$this->db->select('category_name as suggestion, "category" as type, category_id as id');
		$this->db->from('listing_categories');
		$this->applySearchTerms(array('category_name'), $terms);
		$this->db->order_by($this->searchRelevanceOrder('category_name', $search_term, $terms), '', false);
		$this->db->limit(3);
		$categories = $this->db->get()->result();

		// Get suggestions from news — only when the section is switched on,
		// otherwise the dropdown offers a page that 404s.
		$news = array();
		if (site_setting('service_news_enabled', '1') !== '0') {
			$this->db->select('news_title as suggestion, "news" as type, news_id as id');
			$this->db->from('news');
			$this->db->where('news_status', 1);
			$this->applySearchTerms(array('news_title'), $terms);
			$this->db->order_by($this->searchRelevanceOrder('news_title', $search_term, $terms), '', false);
			$this->db->limit(2);
			$news = $this->db->get()->result();
		}

		return array_slice(array_merge($listings, $categories, $news), 0, $limit);
	}

	/**
	 * Advanced search with filters
	 */
	public function advancedSearch($params)
	{
		$results = array(
			'listings' => array(),
			'news' => array(),
			'events' => array(),
			'blogs' => array(),
			'products' => array(),
			'categories' => array(),
			'total_count' => 0
		);

		$query = isset($params['query']) ? $params['query'] : '';
		$category = isset($params['category']) ? $params['category'] : '';
		$type = isset($params['type']) ? $params['type'] : '';
		$location = isset($params['location']) ? $params['location'] : '';

		// Search based on type filter or all types
		if (empty($type) || $type === 'listings') {
			$results['listings'] = $this->advancedSearchListings($query, $category, $location);
		}

		if (empty($type) || $type === 'news') {
			$results['news'] = $this->advancedSearchNews($query);
		}

		if (empty($type) || $type === 'events') {
			$results['events'] = $this->advancedSearchEvents($query, $location);
		}

		if (empty($type) || $type === 'blogs') {
			$results['blogs'] = $this->advancedSearchBlogs($query);
		}

		if (empty($type) || $type === 'products') {
			$results['products'] = $this->advancedSearchProducts($query);
		}

		// Calculate total count
		$results['total_count'] = count($results['listings']) + count($results['news']) +
			count($results['events']) + count($results['blogs']) +
			count($results['products']);

		return $results;
	}

	/**
	 * Advanced search for listings with filters
	 */
	private function advancedSearchListings($query, $category = '', $location = '')
	{
		$this->db->select('listings.*, listing_categories.category_name, user.name as owner_name');
		$this->db->from('listings');
		$this->db->join('listing_categories', 'listings.category_id = listing_categories.category_id', 'left');
		$this->db->join('user', 'listings.user_id = user.user_id', 'left');
		$this->db->where('listings.is_deleted', 0);
		$this->db->where('listings.listing_status', 1);

		if (!empty($query)) {
			$this->db->group_start();
			$this->db->like('listings.listing_name', $query);
			$this->db->or_like('listings.listing_description', $query);
			$this->db->or_like('listings.listing_address', $query);
			$this->db->or_like('listings.service_locations', $query);
			$this->db->or_like('listing_categories.category_name', $query);
			$this->db->group_end();
		}

		if (!empty($category)) {
			$this->db->where('listings.category_id', $category);
		}

		if (!empty($location)) {
			$this->db->group_start();
			$this->db->like('listings.listing_address', $location);
			$this->db->or_like('listings.service_locations', $location);
			$this->db->group_end();
		}

		$this->db->limit(20);
		$this->db->order_by('listings.listing_id', 'DESC');

		return $this->db->get()->result();
	}

	/**
	 * Advanced search for news
	 */
	private function advancedSearchNews($query)
	{
		if (empty($query)) return array();

		$this->db->select('news.*, ' . $this->bylineSelect('news', 'news_byline') . ' as author_name', FALSE);
		$this->db->from('news');
		$this->db->join('user', 'news.user_id = user.user_id', 'left');
		$this->db->where('news.news_status', 1);
		$this->db->group_start();
		$this->db->like('news.news_title', $query);
		$this->db->or_like('news.news_description', $query);
		$this->db->or_like('news.news_content', $query);
		$this->db->group_end();
		$this->db->limit(20);
		$this->db->order_by('news.news_id', 'DESC');

		return $this->db->get()->result();
	}

	/**
	 * Advanced search for events
	 */
	private function advancedSearchEvents($query, $location = '')
	{
		if (empty($query) && empty($location)) return array();

		$this->db->select('events.*, COALESCE(admin.admin_name, user.name) as organizer_name', false);
		$this->db->from('events');
		$this->db->join('user', 'events.user_id = user.user_id', 'left');
		$this->db->join('admin', 'admin.admin_id = events.admin_id', 'left');
		$this->db->where('events.event_status', 1);

		if (!empty($query)) {
			$this->db->group_start();
			$this->db->like('events.event_name', $query);
			$this->db->or_like('events.event_description', $query);
			$this->db->group_end();
		}

		if (!empty($location)) {
			$this->db->group_start();
			$this->db->like('events.event_location', $location);
			$this->db->or_like('events.event_address', $location);
			$this->db->group_end();
		}

		$this->db->limit(15);
		$this->db->order_by('events.event_id', 'DESC');

		return $this->db->get()->result();
	}

	/**
	 * Advanced search for blogs
	 */
	private function advancedSearchBlogs($query)
	{
		if (empty($query)) return array();

		$this->db->select('blogs.*, ' . $this->bylineSelect('blogs', 'blog_byline') . ' as author_name', FALSE);
		$this->db->from('blogs');
		$this->db->join('user', 'blogs.user_id = user.user_id', 'left');
		$this->db->where('blogs.blog_status', 1);
		$this->db->group_start();
		$this->db->like('blogs.blog_title', $query);
		$this->db->or_like('blogs.blog_description', $query);
		$this->db->or_like('blogs.blog_content', $query);
		$this->db->group_end();
		$this->db->limit(15);
		$this->db->order_by('blogs.blog_id', 'DESC');

		return $this->db->get()->result();
	}

	/**
	 * Advanced search for products
	 */
	private function advancedSearchProducts($query)
	{
		if (empty($query)) return array();

		$this->db->select('products.*, user.name as seller_name');
		$this->db->from('products');
		$this->db->join('user', 'products.user_id = user.user_id', 'left');
		$this->db->where('products.product_status', 1);
		$this->db->group_start();
		$this->db->like('products.product_name', $query);
		$this->db->or_like('products.product_description', $query);
		$this->db->group_end();
		$this->db->limit(15);
		$this->db->order_by('products.product_id', 'DESC');

		return $this->db->get()->result();
	}

	/*==========================
		Authorize.Net Payment Processing
	===========================*/

	/**
	 * Process Authorize.Net Payment
	 */
	public function processAuthorizeNetPayment($payment_data)
	{
		// Validate required payment data
		if (!isset($payment_data['amount']) || empty($payment_data['amount'])) {
			error_log('Usermodel: Missing required amount in payment data');
			return array(
				'status' => false,
				'error_message' => 'Payment amount is required',
				'response_code' => 'VALIDATION_ERROR'
			);
		}

		if (!isset($payment_data['card_number']) || empty($payment_data['card_number'])) {
			error_log('Usermodel: Missing required card number in payment data');
			return array(
				'status' => false,
				'error_message' => 'Credit card number is required',
				'response_code' => 'VALIDATION_ERROR'
			);
		}

		error_log('Usermodel: processAuthorizeNetPayment called with data: ' . json_encode([
			'amount' => isset($payment_data['amount']) ? $payment_data['amount'] : 'missing',
			'card_last4' => isset($payment_data['card_number']) ? substr($payment_data['card_number'], -4) : 'missing',
			'has_billing_info' => isset($payment_data['billing_info']) ? 'yes' : 'no'
		]));

		// Load Authorize.Net library manually
		if (!class_exists('Authorize_net_lib')) {
			$lib_paths = array(
				dirname(__FILE__) . '/../libraries/Authorize_net_lib.php',
				dirname(__FILE__) . '/Authorize_net_lib.php',
				dirname(__FILE__) . '/../Authorize_net_lib.php'
			);

			foreach ($lib_paths as $lib_path) {
				error_log('Usermodel: Trying to load Authorize_net_lib from: ' . $lib_path);
				if (file_exists($lib_path)) {
					include_once($lib_path);
					if (class_exists('Authorize_net_lib')) {
						error_log('Usermodel: Successfully loaded Authorize_net_lib from: ' . $lib_path);
						break;
					}
				}
			}
		}

		if (!class_exists('Authorize_net_lib')) {
			error_log('Usermodel: Authorize_net_lib class not available after all includes');
			return array(
				'status' => false,
				'error_message' => 'Payment library not found',
				'response_code' => 'LIB_ERROR'
			);
		}

		try {
			error_log('Usermodel: Creating Authorize_net_lib instance');
			$authorize_net = new Authorize_net_lib();
			error_log('Usermodel: Authorize_net_lib instance created successfully');

			// Process payment through Authorize.Net
			error_log('Usermodel: Calling charge_credit_card method');
			$response = $authorize_net->charge_credit_card($payment_data);
			if (!is_array($response)) {
				error_log('Usermodel: Gateway returned non-array response type: ' . gettype($response));
				$response = array(
					'success' => false,
					'error_message' => 'Internal integration error (unexpected response type)',
					'cap' => 'RESP_TYPE'
				);
			}
			if (isset($response['network_detail'])) {
				error_log('Usermodel: Network detail present: ' . json_encode($response['network_detail']));
			}
			// Detect and log empty error_message early
			if ((isset($response['success']) && !$response['success']) && (!isset($response['error_message']) || trim($response['error_message']) === '')) {
				error_log('Usermodel: DEBUG empty error_message BEFORE normalization: ' . json_encode($response));
			}
			if (!isset($response['error_message']) && !$response['success']) {
				$response['error_message'] = 'Unknown payment error (no message from gateway)';
			}
			error_log('Usermodel: Received response from charge_credit_card: ' . json_encode($response));

			if (isset($response['success']) && $response['success']) {
				error_log('Usermodel: Payment successful');
				return array(
					'status' => true,
					'transaction_id' => $response['transaction_id'],
					'auth_code' => isset($response['auth_code']) ? $response['auth_code'] : '',
					'response_code' => $response['response_code'],
					'message' => $response['message'],
					'txn_ref' => $response['txn_ref'],
					'raw_response' => $response['raw_response']
				);
			} else {
				$raw_err = isset($response['error_message']) ? $response['error_message'] : '';
				if ($raw_err === '' || $raw_err === null) {
					$raw_err = 'No error detail from gateway (enable logs).';
				}
				if (!isset($response['response_code']) || $response['response_code'] === '') {
					$response['response_code'] = 'UNSPECIFIED';
				}
				if (!isset($response['txn_ref'])) {
					$response['txn_ref'] = 'UNSET_' . time();
				}
				error_log('Usermodel: Payment failed normalized error: ' . $raw_err . ' Code=' . $response['response_code']);
				return array(
					'status' => false,
					'error_message' => $raw_err,
					'response_code' => $response['response_code'],
					'transaction_id' => isset($response['transaction_id']) ? $response['transaction_id'] : '',
					'txn_ref' => $response['txn_ref']
				);
			}
		} catch (Exception $e) {
			error_log('Usermodel: Exception in payment processing: ' . $e->getMessage());
			error_log('Usermodel: Exception stack trace: ' . $e->getTraceAsString());
			return array(
				'status' => false,
				'error_message' => 'Payment processing failed: ' . $e->getMessage(),
				'response_code' => 'EXCEPTION'
			);
		}
	}

	/**
	 * Save Authorize.Net Transaction to Database
	 */
	public function saveAuthorizeNetTransaction($transaction_data)
	{
		// Validate required transaction data
		if (!isset($transaction_data['user_id'], $transaction_data['amount'])) {
			error_log('Usermodel: Missing required user_id or amount in transaction data');
			return false;
		}

		// Save to existing transactions table (no need for separate table)
		$legacy_data = array(
			'user_id' => $transaction_data['user_id'],
			'plan_id' => isset($transaction_data['plan_id']) ? $transaction_data['plan_id'] : null,
			'subscription_id' => isset($transaction_data['subscription_id']) ? $transaction_data['subscription_id'] : null,
			'txn_ref' => isset($transaction_data['txn_ref']) ? $transaction_data['txn_ref'] : '',
			'amount' => $transaction_data['amount'],
			'currency' => isset($transaction_data['currency']) ? $transaction_data['currency'] : 'USD',
			'method' => 'authorize_net',
			'status' => ($transaction_data['status'] === true || $transaction_data['status'] === 'true') ? 'completed' : 'failed',
			'auth_net_transaction_id' => isset($transaction_data['auth_net_transaction_id']) ? $transaction_data['auth_net_transaction_id'] : '',
			'auth_net_response_code' => isset($transaction_data['response_code']) ? $transaction_data['response_code'] : '',
			'auth_net_auth_code' => isset($transaction_data['auth_code']) ? $transaction_data['auth_code'] : '',
			'gateway_fees' => isset($transaction_data['gateway_fees']) ? $transaction_data['gateway_fees'] : 0.00,
			'response_payload' => isset($transaction_data['response_payload']) ? json_encode($transaction_data['response_payload']) : json_encode($transaction_data),
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s')
		);

		if ($this->db->insert('transactions', $legacy_data)) {
			return $this->db->insert_id();
		}

		return false;
	}
	/**
	 * Update Payment Card with Authorize.Net Profile ID
	 */
	public function updatePaymentCardProfile($card_id, $profile_data)
	{
		$data = array(
			'auth_net_payment_profile_id' => $profile_data['profile_id'],
			'updated_at' => date('Y-m-d H:i:s')
		);

		$this->db->where('id', $card_id);
		return $this->db->update('payment_cards', $data);
	}

	/**
	 * Create User Subscription with Real Payment
	 */
	public function createPaidSubscription($subscription_data)
	{
		// Validate required data and set defaults
		if (!isset($subscription_data['user_id'], $subscription_data['plan_id'])) {
			return false;
		}

		$data = array(
			'user_id' => $subscription_data['user_id'],
			'plan_id' => $subscription_data['plan_id'],
			'start_date' => date('Y-m-d H:i:s'),
			'end_date' => isset($subscription_data['end_date']) ? $subscription_data['end_date'] : date('Y-m-d H:i:s', strtotime('+30 days')),
			'status' => 'active',
			'payment_status' => 'paid',
			'total_amount' => isset($subscription_data['amount']) ? $subscription_data['amount'] : 0.00,
			'currency' => 'USD',
			'billing_cycle' => isset($subscription_data['billing_cycle']) ? $subscription_data['billing_cycle'] : 'monthly',
			'auto_renew' => isset($subscription_data['auto_renew']) ? $subscription_data['auto_renew'] : 0,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s')
		);

		if ($this->db->insert('subscriptions', $data)) {
			return $this->db->insert_id();
		}
		return false;
	}

	/**
	 * Get Transaction by Reference ID
	 */
	public function getTransactionByRef($txn_ref)
	{
		$this->db->where('txn_ref', $txn_ref);
		$query = $this->db->get('transactions');
		return $query->row();
	}

	/**
	 * Get User's Active Subscription
	 */
	public function getUserActiveSubscription($user_id)
	{
		$this->db->select('subscriptions.*, pricing_plans.name as plan_name');
		$this->db->from('subscriptions');
		$this->db->join('pricing_plans', 'subscriptions.plan_id = pricing_plans.id', 'left');
		$this->db->where('subscriptions.user_id', $user_id);
		$this->db->where('subscriptions.status', 'active');
		$this->db->order_by('subscriptions.created_at', 'DESC');
		$this->db->limit(1);

		$query = $this->db->get();
		return $query->row();
	}

	/**
	 * Update User Subscription Status after successful payment
	 */
	public function updateUserSubscriptionStatus($user_id, $plan_data, $transaction_id = null)
	{
		$this->db->trans_start();

		try {
			// Calculate subscription expiry date based on plan
			$expires_at = date('Y-m-d H:i:s', strtotime('+' . $plan_data['duration_days'] . ' days'));

			// Don't update user table - create subscription record instead
			// Based on your database structure, subscriptions should go to subscriptions table
			$subscription_data = array(
				'user_id' => $user_id,
				'plan_id' => isset($plan_data['plan_id']) ? $plan_data['plan_id'] : 0,
				'start_date' => date('Y-m-d H:i:s'),
				'end_date' => $expires_at,
				'status' => 'active',
				'payment_status' => 'paid',
				'total_amount' => $plan_data['amount'],
				'currency' => 'USD',
				'billing_cycle' => isset($plan_data['billing_cycle']) ? $plan_data['billing_cycle'] : 'monthly',
				'auto_renew' => 0,
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			);

			// Check if subscription already exists for this user
			$existing_subscription = $this->db->where('user_id', $user_id)
				->where('status', 'active')
				->get('subscriptions')
				->row();

			if (!$existing_subscription) {
				$this->db->insert('subscriptions', $subscription_data);
				$subscription_id = $this->db->insert_id();
			} else {
				// Update existing subscription
				$this->db->where('user_id', $user_id)
					->where('status', 'active')
					->update('subscriptions', $subscription_data);
				$subscription_id = $existing_subscription->id;
			}

			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				throw new Exception('Failed to update user subscription status');
			}

			return array(
				'success' => true,
				'message' => 'Subscription activated successfully',
				'expires_at' => $expires_at,
				'plan_name' => $plan_data['plan_name']
			);
		} catch (Exception $e) {
			$this->db->trans_rollback();
			error_log('Subscription Update Error: ' . $e->getMessage());
			return array(
				'success' => false,
				'error' => $e->getMessage()
			);
		}
	}

	/**
	 * Check if user has active subscription
	 */
	public function hasActiveSubscription($user_id)
	{
		$this->db->select('subscription_status, subscription_expires_at, subscription_plan');
		$this->db->from('user');
		$this->db->where('user_id', $user_id);
		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			$user = $query->row();

			if (
				$user->subscription_status === 'active' &&
				$user->subscription_expires_at &&
				strtotime($user->subscription_expires_at) > time()
			) {
				return array(
					'has_subscription' => true,
					'plan' => $user->subscription_plan,
					'expires_at' => $user->subscription_expires_at,
					'status' => $user->subscription_status
				);
			}
		}

		return array(
			'has_subscription' => false,
			'plan' => null,
			'expires_at' => null,
			'status' => 'inactive'
		);
	}

	/**
	 * Get user payment history
	 */
	public function getUserPaymentHistory($user_id, $limit = 10)
	{
		$this->db->select('
			ant.transaction_id,
			ant.amount,
			ant.currency,
			ant.status,
			ant.subscription_plan,
			ant.card_type,
			ant.card_last_four,
			ant.created_at,
			sh.starts_at,
			sh.expires_at
		');
		$this->db->from('authorize_net_transactions ant');
		$this->db->join('subscription_history sh', 'sh.transaction_id = ant.id', 'left');
		$this->db->where('ant.user_id', $user_id);
		$this->db->order_by('ant.created_at', 'DESC');
		$this->db->limit($limit);

		$query = $this->db->get();
		return $query->result_array();
	}

	// Get users who liked a specific listing
	public function getListingLikes($listing_id)
	{
		return $this->db->select('user.user_id, user.name, user.profile_image')
			->from('listing_likes ll')
			->join('user', 'll.user_id = user.user_id', 'inner')
			->where('ll.listing_id', $listing_id)
			->limit(10) // Limit to 10 users
			->get()->result();
	}

	/*====================
      REVIEW FUNCTIONS
    ====================*/

	// Add new review
	public function addReview($data)
	{
		// The debug error_log() calls that were here wrote every reviewer's
		// email and phone number into the server log.
		if ($this->db->insert('listing_reviews', $data)) {
			$insert_id = $this->db->insert_id();
			$this->updateListingRating($data['listing_id']);
			return $insert_id;
		}

		log_message('error', 'addReview failed: ' . $this->db->error()['message']);
		return false;
	}

	// Get reviews for a listing
	public function getListingReviews($listing_id, $limit = null, $offset = null)
	{
		$this->db->select('lr.*, u.name as user_name, u.profile_image');
		$this->db->from('listing_reviews lr');
		$this->db->join('user u', 'lr.user_id = u.user_id', 'left');
		$this->db->where('lr.listing_id', $listing_id);
		$this->db->where('lr.review_status', 1);
		$this->db->order_by('lr.created_at', 'DESC');

		if ($limit) {
			$this->db->limit($limit, $offset);
		}

		return $this->db->get()->result();
	}

	// Update listing rating calculation
	public function updateListingRating($listing_id)
	{
		// The cache columns predate the repo's migrations and are not on
		// every install. Writing a missing column failed the UPDATE — after
		// the review had been saved — which is one way a submitted review
		// came back as "this page isn't working" (db_debug) or vanished.
		// The listing page derives its figures from the reviews themselves
		// (getReviewStats), so a missing cache costs nothing.
		$update_data = array();
		$has_avg   = $this->db->field_exists('average_rating', 'listings');
		$has_total = $this->db->field_exists('total_reviews', 'listings');
		if (!$has_avg && !$has_total) {
			return true;
		}

		$avg_query = $this->db->select('AVG(rating) as avg_rating, COUNT(*) as total_reviews')
			->from('listing_reviews')
			->where('listing_id', (int) $listing_id)
			->where('review_status', 1)
			->get()->row();

		if ($has_avg) {
			$update_data['average_rating'] = ($avg_query && $avg_query->avg_rating) ? round($avg_query->avg_rating, 1) : 0;
		}
		if ($has_total) {
			$update_data['total_reviews'] = ($avg_query && $avg_query->total_reviews) ? (int) $avg_query->total_reviews : 0;
		}

		return $this->db->where('listing_id', (int) $listing_id)->update('listings', $update_data);
	}

	// Check if user already reviewed
	public function hasUserReviewed($listing_id, $user_id = null, $email = null)
	{
		$this->db->where('listing_id', $listing_id);

		if ($user_id) {
			$this->db->where('user_id', $user_id);
		} elseif ($email) {
			$this->db->where('reviewer_email', $email);
		}

		return $this->db->count_all_results('listing_reviews') > 0;
	}

	// Get review statistics for rating breakdown
	public function getReviewStats($listing_id)
	{
		$stats = array();
		for ($i = 5; $i >= 1; $i--) {
			$count = $this->db->where('listing_id', $listing_id)
				->where('rating', $i)
				->where('review_status', 1)
				->count_all_results('listing_reviews');
			$stats[$i] = $count;
		}
		return $stats;
	}

	/*==========================
		LISTING REVIEWS MANAGEMENT
	===========================*/

	/**
	 * Get user's listing reviews with pagination
	 * For listing owners to manage reviews on their listings
	 */
	public function getUserListingReviews($user_id, $limit = 20, $offset = 0)
	{
		$this->db->select('
			lr.review_id,
			lr.listing_id,
			lr.reviewer_name,
			lr.reviewer_email,
			lr.reviewer_mobile,
			lr.reviewer_city,
			lr.rating,
			lr.review_message,
			lr.created_at,
			lr.status,
			l.listing_name,
			l.listing_slug
		');
		$this->db->from('listing_reviews lr');
		$this->db->join('listings l', 'lr.listing_id = l.listing_id', 'left');
		$this->db->where('l.user_id', $user_id);
		$this->db->order_by('lr.created_at', 'DESC');
		$this->db->limit($limit, $offset);

		$query = $this->db->get();
		return $query->result();
	}

	/**
	 * Get total count of user's listing reviews
	 */
	public function getUserListingReviewsCount($user_id)
	{
		$this->db->select('COUNT(*) as total');
		$this->db->from('listing_reviews lr');
		$this->db->join('listings l', 'lr.listing_id = l.listing_id', 'left');
		$this->db->where('l.user_id', $user_id);

		$query = $this->db->get();
		$result = $query->row();
		return $result ? $result->total : 0;
	}

	/**
	 * Verify review ownership - check if review belongs to user's listing
	 */
	public function verifyReviewOwnership($review_id, $user_id)
	{
		$this->db->select('lr.review_id');
		$this->db->from('listing_reviews lr');
		$this->db->join('listings l', 'lr.listing_id = l.listing_id', 'inner');
		$this->db->where('lr.review_id', $review_id);
		$this->db->where('l.user_id', $user_id);

		$query = $this->db->get();
		return $query->num_rows() > 0;
	}

	/**
	 * Update review status (pending/approved/cancelled)
	 */
	public function updateReviewStatus($review_id, $status)
	{
		$allowed_statuses = ['pending', 'approved', 'cancelled'];

		if (!in_array($status, $allowed_statuses)) {
			return false;
		}

		$data = array(
			'status' => $status,
			'updated_at' => date('Y-m-d H:i:s')
		);

		$this->db->where('review_id', $review_id);
		return $this->db->update('listing_reviews', $data);
	}

	/**
	 * Get review statistics for user's listings (optional enhancement)
	 */
	public function getUserListingReviewStats($user_id)
	{
		// Get total reviews by status
		$this->db->select('
			lr.status,
			COUNT(*) as count,
			AVG(lr.rating) as avg_rating
		');
		$this->db->from('listing_reviews lr');
		$this->db->join('listings l', 'lr.listing_id = l.listing_id', 'inner');
		$this->db->where('l.user_id', $user_id);
		$this->db->group_by('lr.status');

		$query = $this->db->get();
		$results = $query->result();

		$stats = array(
			'pending' => 0,
			'approved' => 0,
			'cancelled' => 0,
			'total' => 0,
			'average_rating' => 0
		);

		$total_reviews = 0;
		$total_rating = 0;

		foreach ($results as $result) {
			$stats[$result->status] = $result->count;
			$total_reviews += $result->count;
			$total_rating += ($result->avg_rating * $result->count);
		}

		$stats['total'] = $total_reviews;
		$stats['average_rating'] = $total_reviews > 0 ? round($total_rating / $total_reviews, 1) : 0;

		return $stats;
	}

	/*===================
		LIKE FUNCTIONALITY
	===================*/

	/**
	 * Add a like to a listing
	 */
	public function addLike($listing_id, $user_id = null, $guest_identifier = null)
	{
		try {
			// Validate parameters
			if (!$listing_id || (!$user_id && !$guest_identifier)) {
				return array('success' => false, 'message' => 'Invalid parameters');
			}

			// Check if user/guest already liked this listing
			if ($this->checkUserLiked($listing_id, $user_id, $guest_identifier)) {
				return array('success' => false, 'message' => 'You have already liked this listing');
			}

			// Check rate limiting (max 10 likes per minute)
			if (!$this->checkLikeRateLimit($user_id, $guest_identifier)) {
				return array('success' => false, 'message' => 'Too many likes. Please wait a moment.');
			}

			// Prepare like data
			$like_data = array(
				'listing_id' => $listing_id,
				'user_id' => $user_id,
				'guest_identifier' => $guest_identifier,
				'like_type' => 'like',
				'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
				'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 500),
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			);

			// Insert like
			if ($this->db->insert('listing_likes', $like_data)) {
				// Update listing like count
				$this->updateListingLikeCount($listing_id);
				return array('success' => true, 'message' => 'Listing liked successfully');
			} else {
				return array('success' => false, 'message' => 'Failed to add like');
			}
		} catch (Exception $e) {
			error_log('Add like error: ' . $e->getMessage());
			return array('success' => false, 'message' => 'An error occurred');
		}
	}

	/**
	 * Remove a like from a listing
	 */
	public function removeLike($listing_id, $user_id = null, $guest_identifier = null)
	{
		try {
			// Validate parameters
			if (!$listing_id || (!$user_id && !$guest_identifier)) {
				return array('success' => false, 'message' => 'Invalid parameters');
			}

			// Build where conditions
			$this->db->where('listing_id', $listing_id);

			if ($user_id) {
				$this->db->where('user_id', $user_id);
			} else {
				$this->db->where('guest_identifier', $guest_identifier);
				$this->db->where('user_id IS NULL');
			}

			// Delete the like
			if ($this->db->delete('listing_likes')) {
				// Update listing like count
				$this->updateListingLikeCount($listing_id);
				return array('success' => true, 'message' => 'Like removed successfully');
			} else {
				return array('success' => false, 'message' => 'Failed to remove like');
			}
		} catch (Exception $e) {
			error_log('Remove like error: ' . $e->getMessage());
			return array('success' => false, 'message' => 'An error occurred');
		}
	}

	/**
	 * Check if user/guest has liked a listing
	 */
	public function checkUserLiked($listing_id, $user_id = null, $guest_identifier = null)
	{
		$this->db->where('listing_id', $listing_id);

		if ($user_id) {
			$this->db->where('user_id', $user_id);
		} else {
			$this->db->where('guest_identifier', $guest_identifier);
			$this->db->where('user_id IS NULL');
		}

		return $this->db->count_all_results('listing_likes') > 0;
	}

	/**
	 * Get total like count for a listing
	 */
	public function getLikeCount($listing_id)
	{
		// First try to get from cached count in listings table (with error handling)
		try {
			$listing = $this->db->select('total_likes')
				->where('listing_id', $listing_id)
				->get('listings')
				->row();

			if ($listing && isset($listing->total_likes)) {
				return (int)$listing->total_likes;
			}
		} catch (Exception $e) {
			// Column doesn't exist yet, log the error and continue with fallback
			error_log('total_likes column not found in listings table: ' . $e->getMessage());
		}

		// Fallback: count from listing_likes table
		$count = $this->db->where('listing_id', $listing_id)
			->where('like_type', 'like')
			->count_all_results('listing_likes');

		// Try to update the cached count (will fail silently if column doesn't exist)
		$this->updateListingLikeCount($listing_id);

		return $count;
	}

	/**
	 * Update the total_likes count in listings table
	 */
	public function updateListingLikeCount($listing_id)
	{
		try {
			// Count current likes
			$count = $this->db->where('listing_id', $listing_id)
				->where('like_type', 'like')
				->count_all_results('listing_likes');

			// Update listings table
			$this->db->where('listing_id', $listing_id)
				->update('listings', array('total_likes' => $count));

			return true;
		} catch (Exception $e) {
			error_log('Update like count error: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Check rate limiting for likes (anti-spam)
	 */
	private function checkLikeRateLimit($user_id = null, $guest_identifier = null)
	{
		$this->db->where('created_at >', date('Y-m-d H:i:s', strtotime('-1 minute')));

		if ($user_id) {
			$this->db->where('user_id', $user_id);
		} else {
			$this->db->where('guest_identifier', $guest_identifier);
		}

		$recent_likes = $this->db->count_all_results('listing_likes');

		// Allow max 10 likes per minute
		return $recent_likes < 10;
	}

	/**
	 * Get like statistics for a listing
	 */
	public function getLikeStats($listing_id)
	{
		$stats = array();

		// Total likes
		$stats['total_likes'] = $this->getLikeCount($listing_id);

		// Recent likes (last 24 hours)
		$recent_count = $this->db->where('listing_id', $listing_id)
			->where('like_type', 'like')
			->where('created_at >', date('Y-m-d H:i:s', strtotime('-24 hours')))
			->count_all_results('listing_likes');
		$stats['recent_likes'] = $recent_count;

		// User vs Guest likes
		$user_likes = $this->db->where('listing_id', $listing_id)
			->where('like_type', 'like')
			->where('user_id IS NOT NULL')
			->count_all_results('listing_likes');
		$stats['user_likes'] = $user_likes;
		$stats['guest_likes'] = $stats['total_likes'] - $user_likes;

		return $stats;
	}

	/**
	 * Get recent likers for a listing (for display)
	 */
	public function getRecentLikers($listing_id, $limit = 5)
	{
		$this->db->select('ll.*, u.name, u.profile_image')
			->from('listing_likes ll')
			->join('user u', 'll.user_id = u.user_id', 'left')
			->where('ll.listing_id', $listing_id)
			->where('ll.like_type', 'like')
			->where('ll.user_id IS NOT NULL') // Only registered users
			->order_by('ll.created_at', 'DESC')
			->limit($limit);

		return $this->db->get()->result();
	}

	/**
	 * Get user's liked listings
	 */
	public function getUserLikedListings($user_id, $limit = 10, $offset = 0)
	{
		$this->db->select('l.*, ll.created_at as liked_at')
			->from('listings l')
			->join('listing_likes ll', 'l.listing_id = ll.listing_id')
			->where('ll.user_id', $user_id)
			->where('ll.like_type', 'like')
			->where('l.listing_status', 1)
			->where('l.is_deleted', 0)
			->order_by('ll.created_at', 'DESC')
			->limit($limit, $offset);

		return $this->db->get()->result();
	}

	/**
	 * Get categories specifically for header navigation and modals
	 * Professional method with unique name to avoid conflicts
	 */
	public function getHeaderNavigationCategories($limit = 50)
	{
		return $this->getCategoriesForHeader($limit);
	}

	/*==========================
		PRICING PLAN METHODS
	===========================*/

	/**
	 * Get All Active Plans for Pricing Page
	 * Returns plans ordered by sort_order
	 */
	public function getActivePlans()
	{
		$this->db->select('*');
		$this->db->from('plans');
		$this->db->where('is_active', 1);
		$this->db->order_by('sort_order', 'ASC');
		$this->db->order_by('price', 'ASC'); // Secondary sort by price

		$query = $this->db->get();
		return $query->result();
	}

	/**
	 * Get User's Active Subscription for Pricing Page
	 * @param int $user_id
	 * @return object|null
	 */
	public function getUserActiveSubscriptionForPricing($user_id)
	{
		$this->db->select('s.*, p.name as plan_name, p.price as plan_price');
		$this->db->from('subscriptions s');
		$this->db->join('plans p', 's.plan_id = p.id', 'left');
		$this->db->where('s.user_id', $user_id);
		$this->db->where('s.status', 'active');
		$this->db->order_by('s.created_at', 'DESC');
		$this->db->limit(1);

		$query = $this->db->get();
		return $query->row();
	}

	/**
	 * Get Plan by ID for Pricing Page
	 * @param int $plan_id
	 * @return object|null
	 */
	public function getPlanByIdForPricing($plan_id)
	{
		$this->db->select('*');
		$this->db->from('plans');
		$this->db->where('id', $plan_id);
		$this->db->where('is_active', 1);

		$query = $this->db->get();
		return $query->row();
	}

	// Get all durations for a plan
	public function getPlanDurations($plan_id)
	{
		// Older databases predate migration 0015 and have no such table.
		if (!$this->db->table_exists('plan_durations')) return array();
		$this->db->select('*');
		$this->db->from('plan_durations');
		$this->db->where('plan_id', $plan_id);
		$this->db->where('is_active', 1);
		$this->db->order_by('duration_months', 'asc');
		$query = $this->db->get();
		return $query->result();
	}

	// Get duration details by id
	public function getDurationById($duration_id)
	{
		$this->db->select('*');
		$this->db->from('plan_durations');
		$this->db->where('id', $duration_id);
		$this->db->where('is_active', 1);
		$query = $this->db->get();
		return $query->row();
	}

	/**
	 * Create + activate a subscription after a validated Helcim payment.
	 * Inserts the subscription, a transaction row, and a fresh usage-tracking
	 * window inside one DB transaction. Any currently-active subscription for
	 * the user is expired first (upgrade/replace). Returns the new
	 * subscription id, or FALSE on failure.
	 *
	 * $args keys: user_id, plan_id, duration_id?, amount, currency,
	 *   helcim_transaction_id?, helcim_approval_code?, helcim_card_token?,
	 *   auto_renew? (default 0)
	 */
	public function activateHelcimSubscription($args)
	{
		$user_id = (int) $args['user_id'];
		$plan_id = (int) $args['plan_id'];
		if (!$user_id || !$plan_id) {
			return false;
		}

		// Resolve the term length from the chosen duration, else the plan.
		$duration_id     = !empty($args['duration_id']) ? (int) $args['duration_id'] : null;
		$duration_days   = 30;
		$duration_months = 1;
		if (!empty($args['duration_days']) && (int) $args['duration_days'] > 0) {
			// Explicit term from the cart (e.g. a 12-month or 2-year purchase),
			// which may be a multiple of the plan's base period rather than a
			// configured plan_durations row.
			$duration_days   = (int) $args['duration_days'];
			$duration_months = max(1, (int) round($duration_days / 30));
		} elseif ($duration_id) {
			$d = $this->getDurationById($duration_id);
			if ($d) {
				$duration_months = (int) $d->duration_months ?: 1;
				$duration_days   = (int) $d->duration_days ?: ($duration_months * 30);
			}
		} else {
			$plan = $this->db->select('duration_days')->from('plans')->where('id', $plan_id)->get()->row();
			if ($plan && (int) $plan->duration_days > 0) {
				$duration_days = (int) $plan->duration_days;
			}
			$duration_months = max(1, (int) round($duration_days / 30));
		}

		$now        = date('Y-m-d H:i:s');
		$end        = date('Y-m-d H:i:s', strtotime('+' . $duration_days . ' days'));
		$auto_renew = !empty($args['auto_renew']) ? 1 : 0;
		$currency   = !empty($args['currency']) ? $args['currency'] : 'USD';
		$amount     = (float) $args['amount'];

		$this->db->trans_start();

		// Replace any currently-active subscription (upgrade / renew).
		$this->db->where('user_id', $user_id)
				 ->where('status', 'active')
				 ->update('subscriptions', array('status' => 'expired', 'updated_at' => $now));

		$this->db->insert('subscriptions', array(
			'user_id'           => $user_id,
			'plan_id'           => $plan_id,
			'status'            => 'active',
			'start_date'        => $now,
			'end_date'          => $end,
			'next_billing_date' => $auto_renew ? $end : null,
			'created_at'        => $now,
			'updated_at'        => $now,
			'duration_id'       => $duration_id,
			'duration_days'     => $duration_days,
			'duration_months'   => $duration_months,
			'payment_status'    => 'paid',
			'total_amount'      => $amount,
			'currency'          => $currency,
			'billing_cycle'     => $duration_months >= 12 ? 'yearly' : 'monthly',
			'auto_renew'        => $auto_renew,
		));
		$subscription_id = (int) $this->db->insert_id();

		$this->db->insert('transactions', array(
			'user_id'               => $user_id,
			'plan_id'               => $plan_id,
			'subscription_id'       => $subscription_id,
			'txn_ref'               => 'HLC-' . $subscription_id . '-' . substr(md5($user_id . $now), 0, 8),
			'amount'                => $amount,
			'currency'              => $currency,
			'method'                => 'card',
			'status'                => 'success',
			'created_at'            => $now,
			'updated_at'            => $now,
			'helcim_transaction_id' => !empty($args['helcim_transaction_id']) ? $args['helcim_transaction_id'] : null,
			'helcim_approval_code'  => !empty($args['helcim_approval_code']) ? $args['helcim_approval_code'] : null,
			'helcim_card_token'     => !empty($args['helcim_card_token']) ? $args['helcim_card_token'] : null,
		));
		$transaction_id = (int) $this->db->insert_id();

		$this->db->where('id', $subscription_id)
				 ->update('subscriptions', array('last_transaction_id' => $transaction_id));

		// Fresh 30-day-style usage window for the new subscription.
		$this->db->insert('user_usage_tracking', array(
			'user_id'         => $user_id,
			'subscription_id' => $subscription_id,
			'reset_date'      => date('Y-m-d', strtotime('+' . $duration_days . ' days')),
			'created_at'      => $now,
			'updated_at'      => $now,
		));

		// Vault the Helcim card token when the buyer opted into auto-renew, so
		// the renewal cron can charge it later. last4/exp are placeholders —
		// we only need the token; the PAN is never stored.
		if (!empty($args['auto_renew']) && !empty($args['helcim_card_token'])) {
			$this->db->where('user_id', $user_id)->update('payment_cards', array('is_default' => 0));
			$this->db->insert('payment_cards', array(
				'user_id'           => $user_id,
				'card_holder_name'  => 'Card on file',
				'card_last4'        => '0000',
				'exp_month'         => 1,
				'exp_year'          => 2099,
				'is_default'        => 1,
				'created_at'        => $now,
				'updated_at'        => $now,
				'helcim_card_token' => $args['helcim_card_token'],
			));
		}

		$this->db->trans_complete();

		return $this->db->trans_status() ? $subscription_id : false;
	}

	/**
	 * Put a user on a zero-cost plan (the free "Basic Listing" tier).
	 *
	 * Deliberately simpler than activateHelcimSubscription(): nothing is
	 * charged, so there is no transaction row and no card to vault. The caller
	 * is responsible for checking that the plan really is free and that the
	 * user has no active subscription to downgrade — see
	 * User::start_free_plan().
	 *
	 * The term is open-ended rather than the plan's duration_days. Renewal is
	 * card-driven (getRenewableSubscriptions() only picks up auto_renew rows
	 * with a next_billing_date), so a 30-day end_date would quietly strand a
	 * free member with no way to renew. auto_renew stays 0 and
	 * next_billing_date stays NULL, so the renewal cron never looks at it.
	 *
	 * Returns the new subscription id, or FALSE on failure.
	 */
	public function activateFreePlan($user_id, $plan_id)
	{
		$user_id = (int) $user_id;
		$plan_id = (int) $plan_id;
		if (!$user_id || !$plan_id) {
			return false;
		}

		$now = date('Y-m-d H:i:s');
		$end = date('Y-m-d H:i:s', strtotime('+10 years'));

		$this->db->trans_start();

		$this->db->insert('subscriptions', array(
			'user_id'           => $user_id,
			'plan_id'           => $plan_id,
			'status'            => 'active',
			'start_date'        => $now,
			'end_date'          => $end,
			'next_billing_date' => null,
			'created_at'        => $now,
			'updated_at'        => $now,
			'duration_days'     => 3650,
			'duration_months'   => 120,
			'payment_status'    => 'paid', // nothing to collect
			'total_amount'      => 0,
			'currency'          => 'USD',
			'billing_cycle'     => 'monthly',
			'auto_renew'        => 0,
		));
		$subscription_id = (int) $this->db->insert_id();

		// Usage window, same as the paid path — the dashboards read counts from
		// this table to work out what is left of an allowance.
		$this->db->insert('user_usage_tracking', array(
			'user_id'         => $user_id,
			'subscription_id' => $subscription_id,
			'reset_date'      => date('Y-m-d', strtotime('+10 years')),
			'created_at'      => $now,
			'updated_at'      => $now,
		));

		$this->db->trans_complete();

		return $this->db->trans_status() ? $subscription_id : false;
	}

	/**
	 * Everything a user currently owns, for the "My packages" strip on the
	 * pricing page: the active listing subscription plus any paid ad
	 * placements (banners/videos/add-ons), each with a computed expiry.
	 * Returns a list of ['name','category','expires_at','days_left','auto_renew'].
	 */
	public function getUserPurchasedPackages($user_id)
	{
		$user_id = (int) $user_id;
		$out = array();
		if (!$user_id) {
			return $out;
		}

		// Active listing subscription(s).
		$subs = $this->db->select('s.end_date, s.auto_renew, p.name, p.category')
			->from('subscriptions s')
			->join('plans p', 's.plan_id = p.id', 'left')
			->where('s.user_id', $user_id)
			->where('s.status', 'active')
			->order_by('s.end_date', 'DESC')
			->get()->result();
		foreach ($subs as $s) {
			$out[] = array(
				'name'       => $s->name,
				'category'   => $s->category,
				'expires_at' => $s->end_date,
				'auto_renew' => (int) $s->auto_renew,
			);
		}

		// Paid ad placements. Prefer the term actually purchased (migration
		// 0016); fall back to the plan's base period on older databases.
		if ($this->db->table_exists('transactions')) {
			$has_term = $this->db->field_exists('expires_at', 'transactions');
			$select = 't.created_at, p.name, p.category, p.duration_days';
			if ($has_term) $select .= ', t.expires_at, t.term_days';

			$txns = $this->db->select($select)
				->from('transactions t')
				->join('plans p', 't.plan_id = p.id', 'inner')
				->where('t.user_id', $user_id)
				->where('t.status', 'success')
				->where('p.category !=', 'business-listing')
				->order_by('t.created_at', 'DESC')
				->get()->result();
			foreach ($txns as $t) {
				if ($has_term && !empty($t->expires_at)) {
					$exp = $t->expires_at;
				} elseif ((int) $t->duration_days > 0) {
					$exp = date('Y-m-d H:i:s', strtotime($t->created_at . ' +' . (int) $t->duration_days . ' days'));
				} else {
					continue;
				}
				if (strtotime($exp) < time()) {
					continue; // lapsed placement
				}
				$out[] = array(
					'name'       => $t->name,
					'category'   => $t->category,
					'expires_at' => $exp,
					'auto_renew' => 0,
				);
			}
		}

		foreach ($out as $i => $row) {
			$out[$i]['days_left'] = $row['expires_at']
				? max(0, (int) floor((strtotime($row['expires_at']) - time()) / 86400))
				: null;
		}
		return $out;
	}

	/**
	 * Fulfill a paid multi-item cart. The business-listing item (if any)
	 * activates the capability subscription; every other item (add-ons,
	 * banners, videos, special) is recorded as a paid transaction for admin
	 * fulfillment. One Helcim charge already covered the whole cart total.
	 * Returns TRUE on success.
	 */
	public function recordCartPurchase($user_id, $items, $total, $currency, $helcim_txn_id, $approval_code, $auto_renew = 0)
	{
		$user_id = (int) $user_id;
		if (!$user_id || empty($items)) {
			return false;
		}
		$now = date('Y-m-d H:i:s');

		$this->db->trans_start();

		foreach ($items as $it) {
			$plan_id     = (int) $it['plan_id'];
			$cat         = isset($it['category']) ? $it['category'] : '';
			$price       = (float) $it['price'];
			$duration_id = !empty($it['duration_id']) ? (int) $it['duration_id'] : null;
			// The term the customer actually bought — a 12-month banner must
			// not be filed with the plan's 30-day base period.
			$term_days   = !empty($it['term_days']) ? (int) $it['term_days'] : 0;

			if ($cat === 'business-listing') {
				// Activates the plan's capabilities (subscription + its own txn row).
				$this->activateHelcimSubscription(array(
					'user_id'               => $user_id,
					'plan_id'               => $plan_id,
					'duration_id'           => $duration_id,
					'duration_days'         => $term_days,
					'auto_renew'            => $auto_renew,
					'amount'                => $price,
					'currency'              => $currency,
					'helcim_transaction_id' => $helcim_txn_id,
					'helcim_approval_code'  => $approval_code,
				));
			} else {
				$row = array(
					'user_id'               => $user_id,
					'plan_id'               => $plan_id,
					'txn_ref'               => 'HLC-C-' . $plan_id . '-' . substr(md5($now . $user_id . $plan_id), 0, 8),
					'amount'                => $price,
					'currency'              => $currency,
					'method'                => 'card',
					'status'                => 'success',
					'created_at'            => $now,
					'updated_at'            => $now,
					'helcim_transaction_id' => $helcim_txn_id,
					'helcim_approval_code'  => $approval_code,
					'duration_id'           => $duration_id,
					'target_category_id'    => !empty($it['target_category_id']) ? (int) $it['target_category_id'] : null,
					'term_days'             => $term_days ?: null,
					'expires_at'            => $term_days
						? date('Y-m-d H:i:s', strtotime($now . ' +' . $term_days . ' days'))
						: null,
				);
				// term_days/expires_at/duration_id arrive with migration 0016;
				// drop them on databases that have not run it yet.
				// target_category_id arrives with 0033.
				foreach (array('duration_id', 'term_days', 'expires_at', 'target_category_id') as $col) {
					if (!$this->db->field_exists($col, 'transactions')) unset($row[$col]);
				}
				$this->db->insert('transactions', $row);
			}
		}

		$this->db->trans_complete();
		return $this->db->trans_status();
	}

	/**
	 * Vaulted Helcim card token for a user's default card (or newest), or null.
	 */
	public function getVaultedCardToken($user_id)
	{
		$row = $this->db->select('helcim_card_token')
			->from('payment_cards')
			->where('user_id', (int) $user_id)
			->where('helcim_card_token IS NOT NULL', null, false)
			->order_by('is_default', 'DESC')->order_by('id', 'DESC')
			->limit(1)->get()->row();
		return $row ? $row->helcim_card_token : null;
	}

	/**
	 * What the next auto-renew charge will be for a subscription, in the
	 * subscription's own currency.
	 *
	 * The chosen `plan_durations` price wins (someone on a 12-month term pays
	 * the 12-month price, not twelve times the monthly one); the plan's own
	 * price is the fallback. A scheduled downgrade (`pending_plan_id`) is
	 * priced at the plan it is moving TO, because that is the plan the renewal
	 * will land the customer on.
	 *
	 * Lives here rather than inline in Cli::renew_subscriptions() so that
	 * /admin/billing can show staff the exact figure the cron will take —
	 * previously nothing displayed it anywhere and the only way to know was to
	 * read the job. Returns 0.0 when neither a duration nor an active plan
	 * prices it, which the caller must treat as "do not charge".
	 */
	public function renewalAmountFor($subscription)
	{
		$amount = 0.0;

		/*
		 * An agreed price wins over everything (migration 0029). A founding
		 * member quoted $19 stays on $19 when the plan's list price is edited
		 * later — without this, the first price rise would silently re-price
		 * every customer on that plan, which is the whole risk the Rate Lock
		 * Policy exists to prevent.
		 */
		if (isset($subscription->custom_amount)
			&& $subscription->custom_amount !== null
			&& $subscription->custom_amount !== ''
			&& (float) $subscription->custom_amount > 0) {
			return round((float) $subscription->custom_amount, 2);
		}

		if (!empty($subscription->duration_id)) {
			$d = $this->getDurationById((int) $subscription->duration_id);
			if ($d) {
				$amount = (float) $d->price;
			}
		}

		if ($amount <= 0) {
			$plan_id = !empty($subscription->pending_plan_id)
				? (int) $subscription->pending_plan_id
				: (int) $subscription->plan_id;
			// A plan taken off sale (0038 retired the old listing tiers) still
			// renews at the price the customer signed up at; looking it up
			// among active plans only priced the renewal at 0, which the cron
			// treats as a failure and expires the subscription after three.
			$plan = $this->getPlanByIdForPricing($plan_id) ?: $this->getPlanById($plan_id);
			if ($plan) {
				$amount = (float) $plan->price;
			}
		}

		return $amount;
	}

	/**
	 * Subscriptions due for an auto-renew charge (or a scheduled plan switch).
	 * auto_renew on, active, and past their next_billing_date.
	 */
	public function getRenewableSubscriptions($limit = 200)
	{
		return $this->db->select('*')
			->from('subscriptions')
			->where('auto_renew', 1)
			->where('status', 'active')
			->where('next_billing_date IS NOT NULL', null, false)
			->where('next_billing_date <=', date('Y-m-d H:i:s'))
			->limit($limit)
			->get()->result();
	}

	/**
	 * Auto-renewing subscriptions that will be charged within $days and whose
	 * customer has not yet been told about THIS charge. Keyed on
	 * `renewal_reminded_for` (migration 0036) = the next_billing_date the
	 * reminder was sent for, so a nightly run reminds once per charge and a
	 * retry or the next term reminds afresh. Empty until 0036 has run.
	 */
	public function getSubscriptionsDueForReminder($days = 3, $limit = 200)
	{
		if (!$this->db->field_exists('renewal_reminded_for', 'subscriptions')) return array();

		return $this->db->select('*')
			->from('subscriptions')
			->where('auto_renew', 1)
			->where('status', 'active')
			->where('next_billing_date IS NOT NULL', null, false)
			->where('next_billing_date >', date('Y-m-d H:i:s'))
			->where('next_billing_date <=', date('Y-m-d H:i:s', strtotime('+' . max(1, (int) $days) . ' days')))
			->group_start()
				->where('renewal_reminded_for IS NULL', null, false)
				->or_where('renewal_reminded_for <> next_billing_date', null, false)
			->group_end()
			->limit($limit)
			->get()->result();
	}

	public function markRenewalReminded($subscription)
	{
		if (!$this->db->field_exists('renewal_reminded_for', 'subscriptions')) return false;
		return $this->db->where('id', (int) $subscription->id)
			->update('subscriptions', array('renewal_reminded_for' => $subscription->next_billing_date));
	}

	/** "Visa ending 4242" for the card renewals charge, or '' when unknown. */
	public function vaultedCardLabel($user_id)
	{
		$row = $this->db->select('*')
			->from('payment_cards')
			->where('user_id', (int) $user_id)
			->where('helcim_card_token IS NOT NULL', null, false)
			// Same order as getVaultedCardToken(): the card it names is the
			// card that will actually be charged.
			->order_by('is_default', 'DESC')->order_by('id', 'DESC')
			->limit(1)->get()->row();
		if (!$row || empty($row->card_last4) || $row->card_last4 === '0000') return '';
		$brand = !empty($row->card_brand) ? $row->card_brand : 'Card';
		return $brand . ' ending ' . $row->card_last4;
	}

	/**
	 * Record a successful renewal: extend the term, log the transaction,
	 * apply any scheduled downgrade, and reset the dunning counters.
	 */
	public function recordRenewalSuccess($subscription, $amount, $currency, $helcim_txn_id, $approval_code)
	{
		$now  = date('Y-m-d H:i:s');
		$days = (int) $subscription->duration_days ?: 30;
		// Extend from the later of now / current end so terms don't drift back.
		$base = (!empty($subscription->end_date) && strtotime($subscription->end_date) > time())
			? $subscription->end_date : $now;
		$new_end = date('Y-m-d H:i:s', strtotime($base . ' +' . $days . ' days'));

		$plan_id = !empty($subscription->pending_plan_id) ? (int) $subscription->pending_plan_id : (int) $subscription->plan_id;

		$this->db->trans_start();

		$this->db->insert('transactions', array(
			'user_id'               => (int) $subscription->user_id,
			'plan_id'               => $plan_id,
			'subscription_id'       => (int) $subscription->id,
			'txn_ref'               => 'HLC-R-' . $subscription->id . '-' . substr(md5($now . $subscription->id), 0, 8),
			'amount'                => (float) $amount,
			'currency'              => $currency,
			'method'                => 'card',
			'status'                => 'success',
			'created_at'            => $now,
			'updated_at'            => $now,
			'helcim_transaction_id' => $helcim_txn_id,
			'helcim_approval_code'  => $approval_code,
		));
		$txn_id = (int) $this->db->insert_id();

		$this->db->where('id', (int) $subscription->id)->update('subscriptions', array(
			'plan_id'            => $plan_id,
			'pending_plan_id'    => null,
			'end_date'           => $new_end,
			'next_billing_date'  => $new_end,
			'last_transaction_id' => $txn_id,
			'renewal_attempts'   => 0,
			'last_renewal_error' => null,
			'grace_until'        => null,
			'payment_status'     => 'paid',
			'updated_at'         => $now,
		));

		$this->db->trans_complete();
		return $this->db->trans_status();
	}

	/**
	 * Record a failed renewal: bump the dunning counter, keep access during a
	 * short grace window, and expire after $max_attempts consecutive failures.
	 * Returns 'retry' or 'expired'.
	 */
	public function recordRenewalFailure($subscription, $error, $max_attempts = 3, $grace_days = 2)
	{
		$now      = date('Y-m-d H:i:s');
		$attempts = (int) $subscription->renewal_attempts + 1;

		if ($attempts >= $max_attempts) {
			$this->db->where('id', (int) $subscription->id)->update('subscriptions', array(
				'status'             => 'expired',
				'renewal_attempts'   => $attempts,
				'last_renewal_error' => substr((string) $error, 0, 255),
				'updated_at'         => $now,
			));
			return 'expired';
		}

		$this->db->where('id', (int) $subscription->id)->update('subscriptions', array(
			'renewal_attempts'   => $attempts,
			'last_renewal_error' => substr((string) $error, 0, 255),
			'grace_until'        => date('Y-m-d H:i:s', strtotime('+' . $grace_days . ' days')),
			// Retry tomorrow.
			'next_billing_date'  => date('Y-m-d H:i:s', strtotime('+1 day')),
			'updated_at'         => $now,
		));
		return 'retry';
	}

	/**
	 * Apply an immediate plan upgrade (already paid the prorated difference):
	 * switch plan_id in place, keep the current term, log the transaction.
	 */
	public function applyPlanUpgrade($user_id, $new_plan_id, $amount, $currency, $helcim_txn_id, $approval_code)
	{
		$sub = $this->getUserSubscription((int) $user_id);
		if (!$sub) {
			return false;
		}
		$now = date('Y-m-d H:i:s');

		$this->db->trans_start();

		$this->db->insert('transactions', array(
			'user_id'               => (int) $user_id,
			'plan_id'               => (int) $new_plan_id,
			'subscription_id'       => (int) $sub->id,
			'txn_ref'               => 'HLC-U-' . $sub->id . '-' . substr(md5($now . $sub->id), 0, 8),
			'amount'                => (float) $amount,
			'currency'              => $currency,
			'method'                => 'card',
			'status'                => 'success',
			'created_at'            => $now,
			'updated_at'            => $now,
			'helcim_transaction_id' => $helcim_txn_id,
			'helcim_approval_code'  => $approval_code,
		));

		$this->db->where('id', (int) $sub->id)->update('subscriptions', array(
			'plan_id'         => (int) $new_plan_id,
			'pending_plan_id' => null,
			'updated_at'      => $now,
		));

		$this->db->trans_complete();
		return $this->db->trans_status();
	}

	/**
	 * Schedule a downgrade to take effect at the next renewal.
	 */
	public function scheduleDowngrade($user_id, $new_plan_id)
	{
		$sub = $this->getUserSubscription((int) $user_id);
		if (!$sub) {
			return false;
		}
		return $this->db->where('id', (int) $sub->id)->update('subscriptions', array(
			'pending_plan_id' => (int) $new_plan_id,
			'updated_at'      => date('Y-m-d H:i:s'),
		));
	}

	/**
	 * Cancel auto-renew: the plan runs to end_date, then simply isn't renewed.
	 */
	public function cancelAutoRenew($user_id)
	{
		$sub = $this->getUserSubscription((int) $user_id);
		if (!$sub) {
			return false;
		}
		return $this->db->where('id', (int) $sub->id)->update('subscriptions', array(
			'auto_renew'        => 0,
			'next_billing_date' => null,
			'pending_plan_id'   => null,
			'updated_at'        => date('Y-m-d H:i:s'),
		));
	}

	/**
	 * Check if user has any subscription
	 * @param int $user_id
	 * @return boolean
	 */
	public function userHasSubscription($user_id)
	{
		$this->db->select('COUNT(*) as count');
		$this->db->from('subscriptions');
		$this->db->where('user_id', $user_id);
		$this->db->where_in('status', ['active', 'pending']);

		$query = $this->db->get();
		$result = $query->row();

		return ($result->count > 0);
	}

	/**
	 * Get related houses excluding the current house.
	 * Prefers same category, fills from other houses if needed.
	 *
	 * @param int $exclude_house_id
	 * @param int|null $category_id
	 * @param int $limit
	 * @return array
	 */
	public function getRelatedHouses($exclude_house_id, $category_id = null, $limit = 3)
	{
		$results = array();

		if (!empty($category_id)) {
			$this->db->select('house.*')
				->from('house')
				->where('house.house_status', 1)
				->where('house.is_deleted', 0)
				->where('house.house_category_id', $category_id)
				->where('house.house_id !=', $exclude_house_id)
				->order_by('house.created_at', 'DESC')
				->limit($limit);

			$query = $this->db->get();
			$results = $query->result();
		}

		if (count($results) < $limit) {
			$exclude_ids = array($exclude_house_id);
			foreach ($results as $h) $exclude_ids[] = $h->house_id;

			$needed = $limit - count($results);
			$this->db->select('house.*')
				->from('house')
				->where('house.house_status', 1)
				->where('house.is_deleted', 0)
				->where_not_in('house.house_id', $exclude_ids)
				->order_by('house.created_at', 'DESC')
				->limit($needed);

			$query2 = $this->db->get();
			$more = $query2->result();

			$results = array_merge($results, $more);
		}

		return $results;
	}

	/*=========================
		IT / Web services catalog (public-side)
		==========================*/

	/**
	 * Returns active services ordered for display on /services.
	 * @param string|null $category Optional category filter.
	 * @return array of stdClass
	 */
	public function getActiveServices($category = null)
	{
		$this->db->from('services')->where('is_active', 1);
		if ($category) {
			$this->db->where('category', $category);
		}
		return $this->db
			->order_by('sort_order', 'ASC')
			->order_by('service_id', 'ASC')
			->get()
			->result();
	}

	/**
	 * Distinct category list for the services index page.
	 */
	public function getServiceCategories()
	{
		return $this->db
			->distinct()
			->select('category')
			->from('services')
			->where('is_active', 1)
			->where('category IS NOT NULL', null, false)
			->order_by('category', 'ASC')
			->get()
			->result();
	}

	/**
	 * Single service by slug. Returns null if missing or inactive.
	 */
	public function getServiceBySlug($slug)
	{
		if (empty($slug)) return null;
		return $this->db
			->where('slug', $slug)
			->where('is_active', 1)
			->limit(1)
			->get('services')
			->row();
	}

	/**
	 * Create a service request (quote) row. Returns insert id or null.
	 */
	public function createServiceRequest($data)
	{
		$data['created_at'] = date('Y-m-d H:i:s');
		$data['updated_at'] = date('Y-m-d H:i:s');
		if (!$this->db->insert('service_requests', $data)) {
			return null;
		}
		return $this->db->insert_id();
	}

	/*=========================
		Public coupons / deals
		==========================*/

	/**
	 * Coupons that are status=1 (active) and within their
	 * coupon_start_date / coupon_end_date window. Joined to
	 * listings + user when available so the public page can show
	 * who's offering each deal.
	 */
	public function getActiveCoupons($limit = null, $offset = 0)
	{
		$today = date('Y-m-d');
		// lc joins through the owner's listing, so the category is whatever
		// business posted the offer. It can be NULL — a coupon posted by an
		// account with no published listing has nothing to take it from — and
		// every caller treats it as optional.
		$this->db
			->select('c.*, l.listing_name, l.listing_slug, u.name AS owner_name, lc.category_name')
			->from('coupons c')
			->join('user u', 'u.user_id = c.coupon_user_id', 'left')
			->join('listings l', 'l.user_id = c.coupon_user_id AND (l.is_deleted IS NULL OR l.is_deleted = 0)', 'left')
			->join('listing_categories lc', 'lc.category_id = l.category_id', 'left')
			->where('c.coupon_status', 1)
			->where('c.coupon_start_date <=', $today)
			->where('c.coupon_end_date >=',  $today)
			->order_by('c.coupon_cdt', 'DESC');
		if ($limit !== null) $this->db->limit((int) $limit, (int) $offset);
		return $this->db->get()->result();
	}

	/**
	 * Active coupons, one row per coupon.
	 *
	 * getActiveCoupons() joins `listings` on the OWNER rather than the coupon,
	 * so a business with N listings returns the same coupon N times.
	 *
	 * This is deliberately fixed in PHP, not with GROUP BY: the select list
	 * carries columns from the joined tables that aren't functionally
	 * dependent on coupons.coupon_id, so `GROUP BY c.coupon_id` is rejected
	 * outright under ONLY_FULL_GROUP_BY (MySQL 5.7+ default) — the query then
	 * returns false and the page dies. Over-fetch and de-dupe here instead;
	 * it works on every MySQL configuration.
	 *
	 * @param  int $limit how many DISTINCT coupons you want back
	 * @return array
	 */
	public function getDistinctActiveCoupons($limit = 4)
	{
		$limit = max(1, (int) $limit);

		// Over-fetch to survive the fan-out, but stay bounded.
		$rows = $this->getActiveCoupons($limit * 8);
		if (!$rows) return array();

		$seen = array();
		$out  = array();
		foreach ($rows as $row) {
			$key = isset($row->coupon_id) ? $row->coupon_id : spl_object_hash($row);
			if (isset($seen[$key])) continue;
			$seen[$key] = true;
			$out[] = $row;
			if (count($out) >= $limit) break;
		}

		return $out;
	}

	/**
	 * One owner's live deals, for the "Current deals" card on their listing.
	 * Coupons are posted by an account, not a listing, so every business the
	 * account owns shows the same offers — the same rule /coupons uses.
	 */
	public function getActiveCouponsByUser($user_id, $limit = 3)
	{
		$user_id = (int) $user_id;
		if ($user_id <= 0 || !$this->db->table_exists('coupons')) return array();

		$today = date('Y-m-d');
		return $this->db
			->from('coupons')
			->where('coupon_user_id', $user_id)
			->where('coupon_status', 1)
			->where('coupon_start_date <=', $today)
			->where('coupon_end_date >=', $today)
			->order_by('coupon_end_date', 'ASC')
			->limit(max(1, (int) $limit))
			->get()
			->result();
	}

	public function getCouponById($id)
	{
		return $this->db
			->select('c.*, l.listing_name, l.listing_slug, u.name AS owner_name')
			->from('coupons c')
			->join('user u', 'u.user_id = c.coupon_user_id', 'left')
			->join('listings l', 'l.user_id = c.coupon_user_id AND (l.is_deleted IS NULL OR l.is_deleted = 0)', 'left')
			->where('c.coupon_id', (int) $id)
			->limit(1)
			->get()
			->row();
	}

	/*=========================
		Member-posted coupons (/user/my-coupons)

		Until now nothing anywhere in the application could create a row in
		`coupons` — not the member dashboard, not the admin panel. The public
		/coupons page read a table only phpMyAdmin could fill, and its "Post a
		deal" button pointed back at /coupons itself.
		==========================*/

	/**
	 * Which of the `coupons` columns this install actually has.
	 *
	 * The table predates the repo's migrations, so `coupon_details` and
	 * `coupon_use_members` are present on some installs and not others. Writing
	 * a column that is not there fails the whole INSERT, so the payload is
	 * filtered through this rather than assumed.
	 *
	 * @return array column name => TRUE
	 */
	private function couponColumns()
	{
		static $cols = NULL;
		if ($cols === NULL) {
			$cols = array();
			if ($this->db->table_exists('coupons')) {
				foreach ($this->db->list_fields('coupons') as $field) {
					$cols[$field] = TRUE;
				}
			}
		}
		return $cols;
	}

	/**
	 * One member's coupons, newest first, live and expired alike — this is the
	 * management list, so an expired offer has to stay visible to be edited.
	 */
	public function getCouponsByUser($user_id, $limit = NULL, $offset = 0)
	{
		$this->db->from('coupons')
			->where('coupon_user_id', (int) $user_id)
			->order_by('coupon_id', 'DESC');

		if ($limit !== NULL) $this->db->limit((int) $limit, (int) $offset);

		return $this->db->get()->result();
	}

	/**
	 * @param bool $live_only count only what the public can see today
	 */
	public function countCouponsByUser($user_id, $live_only = FALSE)
	{
		$this->db->from('coupons')->where('coupon_user_id', (int) $user_id);

		if ($live_only) {
			$today = date('Y-m-d');
			$this->db->where('coupon_status', 1)
				->where('coupon_end_date >=', $today);
		}

		return (int) $this->db->count_all_results();
	}

	/**
	 * A coupon, but only if this member posted it. Every edit and delete goes
	 * through here, so ownership is checked in one place instead of in each
	 * controller method.
	 */
	public function getCouponForOwner($coupon_id, $user_id)
	{
		return $this->db
			->from('coupons')
			->where('coupon_id', (int) $coupon_id)
			->where('coupon_user_id', (int) $user_id)
			->limit(1)
			->get()
			->row();
	}

	/**
	 * Insert or update, keeping only the columns that exist.
	 *
	 * @param  array    $data
	 * @param  int|null $coupon_id  NULL inserts
	 * @param  int|null $owner_id   required on update: the WHERE that stops one
	 *                              member editing another's coupon
	 * @return int|bool  new id on insert, TRUE/FALSE on update
	 */
	public function saveCoupon(array $data, $coupon_id = NULL, $owner_id = NULL)
	{
		$cols    = $this->couponColumns();
		$payload = array();
		foreach ($data as $key => $value) {
			if (isset($cols[$key])) $payload[$key] = $value;
		}
		if (empty($payload)) return FALSE;

		if ($coupon_id === NULL) {
			if (!$this->db->insert('coupons', $payload)) return FALSE;
			return (int) $this->db->insert_id();
		}

		$this->db->where('coupon_id', (int) $coupon_id);
		if ($owner_id !== NULL) $this->db->where('coupon_user_id', (int) $owner_id);

		return $this->db->update('coupons', $payload);
	}

	public function deleteCouponForOwner($coupon_id, $user_id)
	{
		return $this->db
			->where('coupon_id', (int) $coupon_id)
			->where('coupon_user_id', (int) $user_id)
			->delete('coupons');
	}

	/*=========================
		Ad-slot lookup (used by application/views/partials/ad_slot.php)
		See database/migrations/0006_ad_zones.sql for the slot list.
		==========================*/

	/**
	 * Return one active ad for the given ad zone (e.g. 'home-hero',
	 * 'listing-sidebar'). Rotates randomly when multiple ads target
	 * the same slot so impressions get shared.
	 *
	 * Returns an object with photo / link / size / slot_key, or null
	 * when there's nothing live for that slot.
	 */
	/**
	 * The ad to run in a slot, optionally for the category being browsed.
	 *
	 * $category_id is the listing category the visitor is looking at, or NULL
	 * on a page that has no category. An ad booked against that category wins
	 * over an untargeted one in the same slot; an untargeted booking still
	 * runs everywhere, which is every ad sold before migration 0024. Ads had
	 * one targeting dimension — the slot — so "category-specific banners"
	 * could not be sold at all.
	 */
	public function getAdForSlot($slot_key, $category_id = NULL)
	{
		if (empty($slot_key)) return null;

		$category_id = ($category_id === NULL || $category_id === '') ? NULL : (int) $category_id;

		// Served from primeAdSlots() when the page primed its zones up
		// front, so seven homepage slots cost one query, not seven. The cache
		// is only consulted for untargeted lookups — a category-specific one
		// has to ask, because the primed set is not filtered by category.
		if ($category_id === NULL && self::$ad_slot_cache !== null && array_key_exists($slot_key, self::$ad_slot_cache)) {
			$live = self::$ad_slot_cache[$slot_key]['ads'];
			if (!$live) return null;
			return $live[array_rand($live)];
		}

		if (!$this->db->table_exists('ads')) return null;
		if (!$this->db->field_exists('slot_key', 'ads_prices')) return null;

		$targeted = $category_id !== NULL && $this->db->field_exists('category_id', 'ads');

		$today = date('Y-m-d H:i:s');
		$this->db
			->select('a.id, a.ad_photo, a.ad_link, ap.size, ap.slot_key')
			->from('ads a')
			->join('ads_prices ap', 'ap.id = a.ads_price_id', 'inner')
			->where('ap.slot_key', $slot_key)
			->where('a.ad_status', 1)
			->where('a.ad_start_date <=', $today)
			->where('a.ad_end_date >=',   $today);

		if ($targeted) {
			// This category, or no category at all.
			$this->db->group_start()
				->where('a.category_id', $category_id)
				->or_where('a.category_id IS NULL', NULL, FALSE)
				->group_end();
			// A booking for this category outranks the general one.
			$this->db->order_by('a.category_id IS NULL', '', FALSE);
		} elseif ($this->db->field_exists('category_id', 'ads')) {
			// No category in play, so only untargeted bookings may run —
			// otherwise the Restaurants banner would show on the homepage.
			$this->db->where('a.category_id IS NULL', NULL, FALSE);
		}

		return $this->db
			->order_by('RAND()', '', false)
			->limit(1)
			->get()
			->row();
	}

	/**
	 * Per-request cache of ad-slot rows, keyed by slot_key.
	 * NULL = not primed yet. Filled by primeAdSlots().
	 */
	private static $ad_slot_cache = null;

	/**
	 * Fetch every requested ad zone in ONE query and cache the result.
	 *
	 * The homepage renders seven slots; without this each include would
	 * fire its own `ORDER BY RAND()` query. Driving the query from
	 * `ads_prices` (LEFT JOIN `ads`) instead of the other way round also
	 * means the slot's `size` is known even when nothing is sold, which
	 * is what lets the partial reserve the right box and avoid layout
	 * shift on the house ad.
	 *
	 * @param array $slot_keys e.g. array('home-hero', 'home-sidebar')
	 */
	public function primeAdSlots(array $slot_keys)
	{
		if (self::$ad_slot_cache === null) self::$ad_slot_cache = array();

		$slot_keys = array_values(array_unique(array_filter($slot_keys)));
		if (!$slot_keys) return;

		if (!$this->db->table_exists('ads_prices')) return;
		if (!$this->db->field_exists('slot_key', 'ads_prices')) return;

		$has_ads = $this->db->table_exists('ads');
		$today   = date('Y-m-d H:i:s');

		$this->db
			->select('ap.slot_key, ap.size, ap.cost, ap.name AS slot_name')
			->from('ads_prices ap')
			->where_in('ap.slot_key', $slot_keys);

		if ($has_ads) {
			// Untargeted bookings only. This cache answers getAdForSlot() calls
			// with no category, whose own query has always excluded
			// category-targeted ads — without the same filter here, a banner
			// sold for one category (Restaurants) ran on the homepage and every
			// other primed page as well.
			$untargeted = $this->db->field_exists('category_id', 'ads') ? ' AND a.category_id IS NULL' : '';
			$this->db
				->select('a.id, a.ad_photo, a.ad_link')
				->join(
					'ads a',
					'a.ads_price_id = ap.id'
						. ' AND a.ad_status = 1'
						. ' AND a.ad_start_date <= ' . $this->db->escape($today)
						. ' AND a.ad_end_date   >= ' . $this->db->escape($today)
						. $untargeted,
					'left'
				);
		}

		$rows = $this->db->get()->result();

		// Seed every requested key so a miss is remembered as "no slot".
		foreach ($slot_keys as $key) {
			if (!array_key_exists($key, self::$ad_slot_cache)) {
				self::$ad_slot_cache[$key] = array('meta' => null, 'ads' => array());
			}
		}

		foreach ($rows as $row) {
			$key = $row->slot_key;
			if (!isset(self::$ad_slot_cache[$key])) {
				self::$ad_slot_cache[$key] = array('meta' => null, 'ads' => array());
			}
			if (self::$ad_slot_cache[$key]['meta'] === null) {
				self::$ad_slot_cache[$key]['meta'] = (object) array(
					'slot_key'  => $row->slot_key,
					'size'      => isset($row->size) ? $row->size : null,
					'cost'      => isset($row->cost) ? $row->cost : null,
					'slot_name' => isset($row->slot_name) ? $row->slot_name : null,
				);
			}
			// A LEFT JOIN with no live ad still returns the slot row.
			if (!empty($row->ad_photo)) {
				self::$ad_slot_cache[$key]['ads'][] = (object) array(
					'id'       => isset($row->id) ? $row->id : 0,
					'ad_photo' => $row->ad_photo,
					'ad_link'  => isset($row->ad_link) ? $row->ad_link : '',
					'size'     => isset($row->size) ? $row->size : null,
					'slot_key' => $row->slot_key,
				);
			}
		}
	}

	/**
	 * Slot metadata (size / cost / name) regardless of whether anything
	 * is sold. Used by the ad partial to reserve the correct box.
	 *
	 * @return object|null
	 */
	public function getAdSlotMeta($slot_key)
	{
		if (empty($slot_key)) return null;

		if (self::$ad_slot_cache !== null && array_key_exists($slot_key, self::$ad_slot_cache)) {
			return self::$ad_slot_cache[$slot_key]['meta'];
		}

		if (!$this->db->table_exists('ads_prices')) return null;
		if (!$this->db->field_exists('slot_key', 'ads_prices')) return null;

		return $this->db
			->select('slot_key, size, cost, name AS slot_name')
			->from('ads_prices')
			->where('slot_key', $slot_key)
			->limit(1)
			->get()
			->row();
	}

	/**
	 * slot_key => size ("300x250") for every ad zone, so a package card can
	 * show the dimensions the zone really renders at. A plan's slug is its
	 * zone's slot_key.
	 */
	public function getAdZoneSizes()
	{
		if (!$this->db->table_exists('ads_prices')) return array();
		if (!$this->db->field_exists('slot_key', 'ads_prices')) return array();

		$out = array();
		$rows = $this->db->select('slot_key, size')->from('ads_prices')
			->where('slot_key IS NOT NULL', null, false)->where('slot_key !=', '')
			->get()->result();
		foreach ($rows as $r) {
			if (!isset($out[$r->slot_key]) && trim((string) $r->size) !== '') {
				$out[$r->slot_key] = trim((string) $r->size);
			}
		}
		return $out;
	}

	/**
	 * Public rate card for the "Advertise with us" block — the real
	 * `ads_prices` rows, so on-page prices can't drift from the catalog.
	 */
	public function getAdSlotRateCard($limit = null)
	{
		if (!$this->db->table_exists('ads_prices')) return array();
		if (!$this->db->field_exists('slot_key', 'ads_prices')) return array();

		$this->db
			->select('id, name, slot_key, size, cost')
			->from('ads_prices')
			->where('status', 1)
			->where('slot_key IS NOT NULL', null, false)
			->where('slot_key !=', '')
			->order_by('cost', 'DESC');

		if ($limit !== null) $this->db->limit((int) $limit);

		return $this->db->get()->result();
	}

	/**
	 * Events that have not happened yet, soonest first.
	 *
	 * `getlatest_events()` cannot be used for an "upcoming" list — it has
	 * no date filter and orders by `event_id DESC`, so it returns the most
	 * recently *created* events, which may all be in the past.
	 */
	public function getUpcomingEvents($limit = 6, $exclude_id = 0)
	{
		if (!$this->db->table_exists('events')) return array();

		// `name` is what the homepage widget reads; `host_name` is what the
		// events pages read. Same value, aliased twice, so neither caller
		// has to know about the other.
		$this->db
			->select('events.*, COALESCE(admin.admin_name, user.name) AS name, COALESCE(admin.admin_name, user.name) AS host_name', false)
			->from('events')
			->join('user',  'events.user_id  = user.user_id',   'left')
			->join('admin', 'admin.admin_id  = events.admin_id', 'left')
			->where('events.event_status', 1)
			->where('events.event_start_date >=', date('Y-m-d'));
		if ((int) $exclude_id > 0) $this->db->where('events.event_id !=', (int) $exclude_id);

		$this->db
			->order_by('events.event_start_date', 'ASC')
			->order_by('events.event_id', 'ASC')
			->limit((int) $limit);

		return $this->db->get()->result();
	}

	/**
	 * Listings with a live "Business Hotspot on Homepage" placement.
	 *
	 * Sold as plans.slug = 'business-hotspot' ($25 / 30 days). The paid term
	 * is stored as a date in listings.hotspot_until (migration 0018), so the
	 * placement drops off by itself the day after it expires — nobody has to
	 * remember to switch it off.
	 *
	 * Returns array() when 0018 hasn't been run, so the homepage degrades to
	 * hiding the section instead of erroring.
	 */
	public function getHotspotListings($limit = 8)
	{
		if (!$this->db->field_exists('hotspot_until', 'listings')) return array();

		$rows = $this->db
			->select('listings.listing_id, listings.listing_name, listings.listing_slug,
			          listings.listing_image, listings.hotspot_until, listing_categories.category_name', false)
			->from('listings')
			->join('listing_categories', 'listings.category_id = listing_categories.category_id', 'left')
			->where('listings.listing_status', 1)   // 1 = published
			->where('listings.is_deleted', 0)
			->where('listings.hotspot_until >=', date('Y-m-d'))
			->order_by('listings.hotspot_until', 'ASC')
			->limit(60)
			->get()
			->result();
		// More live Hotspots than places on the rail: rotate, as the Featured
		// row does, rather than always showing the ones that expire first.
		return array_slice($this->rotate($rows), 0, (int) $limit);
	}

	/**
	 * The current "Business Of the Week" listing.
	 *
	 * Sold as plans.slug = 'business-of-the-week' ($20 / 7 days), stored in
	 * listings.week_until (migration 0018). Deliberately returns ONE row: the
	 * homepage gives it a single wide spotlight card, and being the only one
	 * is what the customer is paying for. If several are live, the one
	 * expiring soonest wins so each buyer gets their turn.
	 *
	 * @return object|null
	 */
	public function getBusinessOfTheWeek()
	{
		if (!$this->db->field_exists('week_until', 'listings')) return null;

		return $this->db
			->select('listings.listing_id, listings.listing_name, listings.listing_slug,
			          listings.listing_description, listings.listing_image, listings.week_until,
			          listing_categories.category_name', false)
			->from('listings')
			->join('listing_categories', 'listings.category_id = listing_categories.category_id', 'left')
			->where('listings.listing_status', 1)   // 1 = published
			->where('listings.is_deleted', 0)
			->where('listings.week_until >=', date('Y-m-d'))
			->order_by('listings.week_until', 'ASC')
			->limit(1)
			->get()
			->row();
	}

	/**
	 * Top categories by live listing count, for the homepage category
	 * strip and sidebar. Stable ordering (unlike getRandomTenCategory)
	 * so the strip doesn't reshuffle on every page load.
	 */
	public function getCategoriesWithCounts($limit = 10)
	{
		if (!$this->db->table_exists('listing_categories')) return array();

		$this->db
			->select('lc.category_id, lc.category_name, lc.category_slug, COUNT(l.listing_id) AS listing_count', false)
			->from('listing_categories lc')
			->join('listings l', 'l.category_id = lc.category_id AND ' . $this->publishedListingJoinCondition('l'), 'left')
			->group_by('lc.category_id, lc.category_name, lc.category_slug')
			->having('listing_count >', 0)
			->order_by('listing_count', 'DESC')
			->order_by('lc.category_name', 'ASC')
			->limit((int) $limit);

		return $this->db->get()->result();
	}

	/**
	 * Every listing category, with the number of listings the public can
	 * actually see in each — the whole /all-category index in ONE query.
	 *
	 * Replaces getAllCategoryWithPagination(), which selected no count and
	 * ordered by `category_id DESC`, so the index opened on whichever
	 * categories happened to be created last and a visitor had no way to tell
	 * a category holding 257 businesses from one holding none.
	 *
	 * Deliberately unpaginated. A category index is a lookup table, not a feed:
	 * paging it meant a visitor hunting for "Plumbers" had to guess which of
	 * eight pages it was on, and browser find-in-page — the tool people
	 * actually reach for — could only ever see a twentieth of the list. The
	 * page renders all of it and filters in the browser instead. The cost is
	 * one row of markup per category (~120 bytes, and it gzips to far less),
	 * against one HTTP round trip per page of twenty.
	 *
	 * The search still runs in SQL for the no-JavaScript case and for
	 * shareable /all-category?q= links. The page used to filter in PHP over
	 * the twenty rows of the current page only, so searching for a category
	 * that lived on page 3 returned "no categories found".
	 *
	 * @param string $search  matched against name and description
	 * @param int    $limit   0 = no limit (the index); >0 caps the result
	 */
	public function getAllCategoriesWithCounts($search = '', $limit = 0)
	{
		if (!$this->db->table_exists('listing_categories')) return array();

		$has_desc  = $this->db->field_exists('category_description', 'listing_categories');
		$has_image = $this->db->field_exists('category_image', 'listing_categories');

		$cols = array('lc.category_id', 'lc.category_name', 'lc.category_slug');
		if ($has_image) $cols[] = 'lc.category_image';
		if ($has_desc)  $cols[] = 'lc.category_description';

		$this->db
			->select(implode(', ', $cols) . ', COUNT(l.listing_id) AS listing_count', false)
			->from('listing_categories lc')
			->join('listings l', 'l.category_id = lc.category_id AND ' . $this->publishedListingJoinCondition('l'), 'left');

		$search = trim((string) $search);
		if ($search !== '') {
			$this->db->group_start()->like('lc.category_name', $search);
			if ($has_desc) $this->db->or_like('lc.category_description', $search);
			$this->db->group_end();
		}

		// Every non-aggregate column is grouped, so this survives
		// ONLY_FULL_GROUP_BY (the MySQL 5.7+ default).
		$this->db
			->group_by($cols)
			->order_by('listing_count', 'DESC')
			->order_by('lc.category_name', 'ASC');

		if ((int) $limit > 0) $this->db->limit((int) $limit);

		return $this->db->get()->result();
	}

	/**
	 * Find a category by a word in its name — for navigation links that need
	 * to point at "whatever this site calls Healthcare" rather than at a slug
	 * typed into a template.
	 *
	 * The header's Sports and Health links were hard-coded to
	 * `all_listing/sports-clubs` and `all_listing/health-services`. Neither
	 * slug is guaranteed to exist: categories are operator-editable, and a
	 * link to a slug nothing matches lands on an empty directory page.
	 *
	 * $keywords is tried in order, so pass the specific term first
	 * ('healthcare' before 'health'). With $must_have_listings the category is
	 * only returned when the public can actually see something in it, which is
	 * what lets a caller hide the link instead of pointing at an empty page.
	 *
	 * @param  array $keywords lower-case substrings of the category name
	 * @param  bool  $must_have_listings
	 * @return object|null  category_id / category_name / category_slug / listing_count
	 */
	public function findCategoryByKeywords(array $keywords, $must_have_listings = true)
	{
		static $cache = null;

		if ($cache === null) {
			$cache = array();
			if ($this->db->table_exists('listing_categories')) {
				$cache = $this->db
					->select('lc.category_id, lc.category_name, lc.category_slug,
					          COUNT(l.listing_id) AS listing_count', false)
					->from('listing_categories lc')
					->join('listings l', 'l.category_id = lc.category_id AND ' . $this->publishedListingJoinCondition('l'), 'left')
					->group_by(array('lc.category_id', 'lc.category_name', 'lc.category_slug'))
					->get()->result();
			}
		}

		foreach ($keywords as $needle) {
			$needle = strtolower(trim((string) $needle));
			if ($needle === '') continue;

			// Whole word, not a substring. "recreation" matched "Travel and
			// Recreation", so the Sports menu item pointed at the travel
			// category — a plain strpos() will match any category that merely
			// contains the letters. The trailing \w* keeps simple plurals
			// ("sport" -> "Sports") working.
			$pattern = '/\b' . preg_quote($needle, '/') . '\w*/i';

			$best = null;
			foreach ($cache as $row) {
				if (!preg_match($pattern, (string) $row->category_name)) continue;
				if ($must_have_listings && (int) $row->listing_count < 1) continue;
				// Prefer the fullest category when several names match.
				if ($best === null || (int) $row->listing_count > (int) $best->listing_count) {
					$best = $row;
				}
			}
			if ($best !== null) return $best;
		}

		return null;
	}

	/**
	 * Classifieds categories with the number of live adverts in each, for the
	 * /classifieds sidebar. Empty categories are dropped — the rail is for
	 * navigating, and a link to nothing is not navigation.
	 */
	public function getClassifiedsCategoriesWithCounts($limit = 10)
	{
		if (!$this->db->table_exists('classifieds_categories')) return array();

		return $this->db
			->select('cc.category_id, cc.category_name, COUNT(c.classifieds_id) AS item_count', false)
			->from('classifieds_categories cc')
			->join('classifieds c', 'c.classifieds_category_id = cc.category_id AND c.classifieds_status = 1', 'left')
			->group_by(array('cc.category_id', 'cc.category_name'))
			->having('item_count >', 0)
			->order_by('item_count', 'DESC')
			->order_by('cc.category_name', 'ASC')
			->limit((int) $limit)
			->get()->result();
	}

	/* =====================================================================
	 * HOMEPAGE LISTING FEEDS
	 * ---------------------------------------------------------------------
	 * getFeaturedListings() / getLatestListings() above are the original
	 * pair. They select six columns, so a homepage card could not show a
	 * service area, a phone number or a rating; they apply no quality bar,
	 * so a listing with a one-character description could take a paid
	 * Featured slot; and neither knows what the other returned, so the same
	 * business could appear in Featured, in Hotspot and in the newest rail
	 * on one screen. The two below fix all three, and are what the homepage
	 * uses now. The originals stay for the other callers.
	 * ===================================================================*/

	/**
	 * A listing is "complete enough for the homepage" when it has a real
	 * description. Not a placeholder, not a dash: 25 characters is past
	 * "-", "n/a", "TBD" and past an empty "<p></p>" a rich-text editor
	 * leaves behind, and short of anything a real sentence would fail.
	 *
	 * Photographs are NOT required — every card now falls back to a
	 * category placeholder rather than the old green "no photo" tile — but
	 * a listing that has one sorts first, so the complete listings fill the
	 * page before the merely adequate ones do.
	 */
	const HOMEPAGE_MIN_DESCRIPTION = 25;

	/**
	 * Shared FROM/WHERE for both homepage feeds.
	 *
	 * @param array $exclude_ids listing_ids already shown further up the page
	 * @param bool  $require_description apply the completeness bar
	 */
	private function homepageListingQuery(array $exclude_ids = array(), $require_description = true)
	{
		// `listings.*` on purpose: `average_rating`, `display_phone` and
		// friends arrived in later migrations and naming them would break
		// an install that has not run them yet.
		// Double-quoted in PHP so the SQL string literals stay single-quoted:
		// under ANSI_QUOTES a "" would be read as an identifier, not as ''.
		$this->db
			->select("listings.*, listing_categories.category_name, listing_categories.category_slug,
			          (CASE WHEN listings.listing_image IS NULL OR TRIM(listings.listing_image) = '' THEN 1 ELSE 0 END) AS p411_no_image", false)
			->from('listings')
			->join('listing_categories', 'listings.category_id = listing_categories.category_id', 'LEFT');

		$this->applyPublishedListingFilter('listings');

		$exclude_ids = array_values(array_unique(array_filter(array_map('intval', $exclude_ids))));
		if ($exclude_ids) $this->db->where_not_in('listings.listing_id', $exclude_ids);

		if ($require_description) {
			$this->db->where(
				"CHAR_LENGTH(TRIM(COALESCE(listings.listing_description, ''))) >= " . (int) self::HOMEPAGE_MIN_DESCRIPTION,
				NULL,
				FALSE
			);
		}
	}

	/**
	 * Featured / spotlight businesses for the homepage's premium row.
	 *
	 * Only complete listings qualify. An empty Featured row reads as "nobody
	 * has bought this yet", which is true and recoverable; a Featured row of
	 * blank cards reads as "the paid placement is worthless", which is not.
	 * There is deliberately no top-up pass here for that reason.
	 *
	 * @param int   $limit
	 * @param array $exclude_ids
	 */
	public function getHomepageFeatured($limit = 3, array $exclude_ids = array())
	{
		$this->homepageListingQuery($exclude_ids, true);

		$this->db->group_start()
			->where('listings.featured_listing', 1)
			->or_where('listings.spotlight_listing', 1)
			->group_end();

		// Rotation (checklist §7, "homepage rotation works"). This returned
		// the newest featured listings every time, so with more paid
		// placements than slots the same ones always showed and the rest never
		// did. Fetch the pool, keep the ones with a picture in front, and
		// shuffle within each group, so every featured business gets its turn.
		$rows = $this->db->limit(60)->get()->result();
		return array_slice($this->rotate($rows), 0, (int) $limit);
	}

	/**
	 * Shuffle homepage rows, keeping the ones with a picture ahead of the
	 * ones without (p411_no_image, or an empty listing_image).
	 */
	private function rotate(array $rows)
	{
		$with = array();
		$without = array();
		foreach ($rows as $row) {
			$no_image = isset($row->p411_no_image) ? (int) $row->p411_no_image === 1 : empty($row->listing_image);
			if ($no_image) $without[] = $row; else $with[] = $row;
		}
		shuffle($with);
		shuffle($without);
		return array_merge($with, $without);
	}

	/**
	 * The general "Explore local businesses" feed.
	 *
	 * Complete listings first; if there aren't enough of those to fill the
	 * grid it tops up with the rest, newest first, rather than rendering a
	 * short row. Featured and spotlight listings are excluded — they have
	 * their own row directly above and appearing twice was one of the
	 * duplicates the 2026-08 review picked up.
	 *
	 * @param int   $limit       6–9 on the homepage; the brief caps it there
	 * @param array $exclude_ids listing_ids already shown further up the page
	 */
	public function getHomepageListings($limit = 6, array $exclude_ids = array())
	{
		$limit = max(1, (int) $limit);

		$fetch = function ($want, $skip, $require_description) {
			$this->homepageListingQuery($skip, $require_description);
			$this->db
				->where('(listings.featured_listing IS NULL OR listings.featured_listing = 0)')
				->where('(listings.spotlight_listing IS NULL OR listings.spotlight_listing = 0)')
				->order_by('p411_no_image', 'ASC');
			if ($this->db->field_exists('created_at', 'listings')) {
				$this->db->order_by('listings.created_at', 'DESC');
			}
			return $this->db->order_by('listings.listing_id', 'DESC')->limit((int) $want)->get()->result();
		};

		$rows = $fetch($limit, $exclude_ids, true);
		if (count($rows) >= $limit) return $rows;

		// Short. Widen the net, skipping what we already have.
		$seen = $exclude_ids;
		foreach ($rows as $row) $seen[] = $row->listing_id;

		foreach ($fetch($limit - count($rows), $seen, false) as $row) {
			$rows[] = $row;
		}

		return $rows;
	}

	/* =====================================================================
	 * CONTENT SECTIONS — events, news, videos
	 * ---------------------------------------------------------------------
	 * Backing queries for /event, /news, /videos and their detail pages,
	 * laid out like the directory: a filter (time window for events, a
	 * category for news and videos), a count that agrees with the list it
	 * labels, and a sidebar rail that isn't just the first page repeated.
	 * ===================================================================*/

	/**
	 * Turn a category slug from a /news or /videos URL back into an id.
	 *
	 * Public wrapper over the private resolver the directory uses, so link
	 * generation (stored `category_slug`, else pearland_slug() of the name)
	 * and link resolution run the same rule. The admin's category forms
	 * build their slug with a different expression again
	 * (`preg_replace('/[^A-Za-z0-9-]+/', '-', $name)`, which keeps capitals),
	 * so a stored slug is tried verbatim first and only then slugified.
	 *
	 * @param  string $slug
	 * @param  string $table  news_categories | video_categories
	 * @return int|null
	 */
	public function resolveContentCategoryId($slug, $table)
	{
		if (!in_array($table, array('news_categories', 'video_categories'), true)) return NULL;
		if (!$this->db->table_exists($table)) return NULL;

		return $this->resolveCategoryIdBySlug($slug, $table);
	}

	/**
	 * News categories that actually have published stories, most first.
	 * Empty categories are dropped: a strip of dead links is worse than a
	 * shorter strip.
	 */
	public function getNewsCategoriesWithCounts($limit = 12)
	{
		if (!$this->db->table_exists('news_categories')) return array();

		return $this->db
			->select('c.category_id, c.category_name, c.category_slug, COUNT(n.news_id) AS item_count', false)
			->from('news_categories c')
			->join('news n', 'n.news_category_id = c.category_id AND n.is_deleted = 0 AND n.news_status = 1', 'left')
			->group_by('c.category_id, c.category_name, c.category_slug')
			->having('item_count >', 0)
			->order_by('item_count', 'DESC')
			->order_by('c.category_name', 'ASC')
			->limit((int) $limit)
			->get()->result();
	}

	/** Video categories that have videos in them, most first. */
	public function getVideoCategoriesWithCounts($limit = 12)
	{
		if (!$this->db->table_exists('video_categories')) return array();

		return $this->db
			->select('c.category_id, c.category_name, c.category_slug, COUNT(v.video_id) AS item_count', false)
			->from('video_categories c')
			->join('video v', 'v.video_category_id = c.category_id', 'left')
			->group_by('c.category_id, c.category_name, c.category_slug')
			->having('item_count >', 0)
			->order_by('item_count', 'DESC')
			->order_by('c.category_name', 'ASC')
			->limit((int) $limit)
			->get()->result();
	}

	/** How many events are still to come. Drives the index's default filter. */
	public function countUpcomingEvents()
	{
		return $this->db
			->from('events')
			->where('event_status', 1)
			->where('event_start_date >=', date('Y-m-d'))
			->count_all_results();
	}

	/** Most-read published stories, for the news sidebar. */
	public function getPopularNews($limit = 5, $exclude_id = 0)
	{
		$this->db->select('news.*, categories.category_name, categories.category_slug', false)
			->from('news')
			->join('news_categories as categories', 'categories.category_id = news.news_category_id', 'LEFT')
			->where('news.is_deleted', 0)
			->where('news.news_status', 1);
		if ((int) $exclude_id > 0) $this->db->where('news.news_id !=', (int) $exclude_id);

		return $this->db
			->order_by('news.views', 'DESC')
			->order_by('news.news_id', 'DESC')
			->limit((int) $limit)
			->get()->result();
	}

	/** Most-watched videos, for the videos sidebar. */
	public function getPopularVideos($limit = 5, $exclude_id = 0)
	{
		$this->db->select('video.video_id, video.video_title, video.video_slug, video.video_image, video.video_code, video.views, video.created_at, categories.category_name, categories.category_slug', false)
			->from('video')
			->join('video_categories as categories', 'categories.category_id = video.video_category_id', 'LEFT');
		if ((int) $exclude_id > 0) $this->db->where('video.video_id !=', (int) $exclude_id);

		return $this->db
			->order_by('video.views', 'DESC')
			->order_by('video.video_id', 'DESC')
			->limit((int) $limit)
			->get()->result();
	}
}
