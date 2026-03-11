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
		$this->form_validation->set_rules('name', 'Name', 'trim|required|min_length[3]');
		$this->form_validation->set_rules('mobile_number', 'Phone', 'trim|required|min_length[10]|max_length[20]');
		$this->form_validation->set_rules(
			'email_id',
			'Email',
			'trim|required|valid_email|is_unique[user.email_id]',
			array(
				'required'      => 'You have not provided %s.',
				'is_unique'     => 'This %s already exists.'
			)
		);

		$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]');

		if ($this->form_validation->run()) {
			$form_data 		= array(
				'name'				=> htmlentities($this->input->post('name'), ENT_QUOTES),
				'email_id'				=> htmlentities($this->input->post('email_id'), ENT_QUOTES),
				'password'				=> md5($this->input->post('password')),
				'mobile_number'			=> htmlentities($this->input->post('mobile_number'), ENT_QUOTES),
				'hear_us'				=> htmlentities($this->input->post('hear_us'), ENT_QUOTES),
				'sale_representative'	=> htmlentities($this->input->post('sale_representative'), ENT_QUOTES),
				'user_zip_code'				=> htmlentities($this->input->post('zip_code'), ENT_QUOTES)
			);

			if ($this->db->insert('user', $form_data)) {
				return array('status' => TRUE);
			} else {
				return array('status' => FALSE, 'referror' => '', 'message' => 'Something Wrong please try again!');
			}
		}

		return array('status' => FALSE, 'referror' => '');
	}

	/*====================
		    Login
	=====================*/

	function login()
	{
		$this->form_validation->set_rules('email_id', 'Email Address', 'required|trim|valid_email');

		$this->form_validation->set_rules('password', 'Password', 'required');

		if ($this->form_validation->run()) {
			$email_id = htmlentities($this->input->post('email_id'), ENT_QUOTES);
			$password  = md5($this->input->post('password'));
			$query = $this->db->query("SELECT * FROM user WHERE email_id = '$email_id' AND password = '$password'");
			$result = $query->result();

			if (sizeof($result) > 0) {

				if ($result[0]->banned != 1) {

					$sess_array = array(
						'logged_id' => $result[0]->user_id,
						'name' => $result[0]->name,
						'email_id' => $result[0]->email_id,
						'profile_image' => $result[0]->profile_image,
						'user_created' => $result[0]->user_created
					);
					$this->session->set_userdata('logged_user', $sess_array);

					// Force redirect to dashboard regardless of lastpage
					return array('status' => TRUE, 'url' => 'dashboard', 'redirect' => true);
				} else {

					return array('status' => FALSE, 'message' => 'Your account has been removed. Please contact Administrator.', 'url' => 'login');
				}
			} else {
				// No user found with given credentials
				return array('status' => FALSE, 'message' => 'Email or Password is incorrect.', 'url' => 'login');
			}
		} else {
			// Form validation failed
			return array('status' => FALSE, 'referror' => '', 'message' => 'Please enter valid email and password.');
		}
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
	public function send_subscription_email($subscription_data, $type = 'created')
	{
		$this->load->library('email');

		$email_config = array(
			'protocol' => 'smtp',
			'smtp_host' => 'smtp.gmail.com', // Change to your SMTP
			'smtp_port' => 587,
			'smtp_user' => 'your-email@gmail.com', // Change to your email
			'smtp_pass' => 'your-password', // Change to your password
			'smtp_crypto' => 'tls',
			'mailtype' => 'html',
			'charset' => 'utf-8'
		);

		$this->email->initialize($email_config);

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

		$this->email->from('noreply@yoursite.com', 'Your Site Name');
		$this->email->to($billing_info['email']);
		$this->email->subject($subject);
		$this->email->message($message);

		if ($this->email->send()) {
			error_log('Subscription email sent successfully to: ' . $billing_info['email']);
			return true;
		} else {
			error_log('Failed to send subscription email: ' . $this->email->print_debugger());
			return false;
		}
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
		return $data = $this->db->get('blogs')->result();
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
	public function getEventsWithPagination($search = '', $limit = 10, $offset = 0)
	{
		$this->db->select('*')->from('events')->where('event_status', 1);
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('event_name', $search);
			$this->db->or_like('event_description', $search);
			$this->db->group_end();
		}
		$this->db->order_by('event_id', 'DESC');
		$this->db->limit($limit, $offset); // $limit = per_page, $offset = offset
		return $this->db->get()->result();
	}

	public function countEventsWithSearch($search = '')
	{
		$this->db->from('events')->where('event_status', 1);
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('event_name', $search);
			$this->db->or_like('event_description', $search);
			$this->db->group_end();
		}
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



	public function getListingByCategory($cat_identifier = NULL, $limit = NULL, $offset = NULL)
	{
		$this->db->select('listings.*,user.name,listing_categories.category_name,listing_categories.category_slug')
			->from('listings')
			->join('user', 'listings.user_id = user.user_id')
			->join('listing_categories', 'listings.category_id = listing_categories.category_id', 'LEFT');

		// Only filter by category if cat_identifier is provided
		if ($cat_identifier !== NULL) {
			// Check if it's numeric (ID) or string (slug)
			if (is_numeric($cat_identifier)) {
				$this->db->where('listing_categories.category_id', $cat_identifier);
			} else {
				// For slug, try both database slug and generated slug from name
				$where_condition = "(listing_categories.category_slug = '" . $this->db->escape_str($cat_identifier) . "' OR LOWER(REPLACE(listing_categories.category_name, ' ', '-')) = '" . $this->db->escape_str($cat_identifier) . "')";
				$this->db->where($where_condition);
			}
		}

		return $this->db->where('listings.is_deleted', 0)
			->where('listings.listing_status', 1)
			->where('(listings.featured_listing IS NULL OR listings.featured_listing = 0)')
			->where('(listings.spotlight_listing IS NULL OR listings.spotlight_listing = 0)')
			->order_by('listings.created_at DESC, listings.listing_id DESC')
			->limit($limit, $offset)
			->get()->result();
	}

	public function countListingsByCategory($cat_identifier = NULL)
	{
		// Match exact same structure as getListingByCategory method
		$this->db->from('listings')
			->join('user', 'listings.user_id = user.user_id')
			->join('listing_categories', 'listings.category_id = listing_categories.category_id', 'LEFT');

		// Only filter by category if cat_identifier is provided
		if ($cat_identifier !== NULL) {
			// Check if it's numeric (ID) or string (slug)
			if (is_numeric($cat_identifier)) {
				$this->db->where('listing_categories.category_id', $cat_identifier);
			} else {
				// For slug, try both database slug and generated slug from name
				$where_condition = "(listing_categories.category_slug = '" . $this->db->escape_str($cat_identifier) . "' OR LOWER(REPLACE(listing_categories.category_name, ' ', '-')) = '" . $this->db->escape_str($cat_identifier) . "')";
				$this->db->where($where_condition);
			}
		}

		// Apply the exact same filters as getListingByCategory
		$this->db->where('listings.is_deleted', 0)
			->where('listings.listing_status', 1)
			->where('(listings.featured_listing IS NULL OR listings.featured_listing = 0)')
			->where('(listings.spotlight_listing IS NULL OR listings.spotlight_listing = 0)');

		return $this->db->count_all_results();
	}

	/**
	 * Get related news (excluding the current news)
	 * @param int $exclude_news_id
	 * @param int $limit
	 * @return array
	 */
	public function getRelatedNews($exclude_news_id, $limit = 3)
	{
		$this->db->where('news_status', 1);
		$this->db->where('news_id !=', $exclude_news_id);
		$this->db->order_by('created_at', 'DESC');
		$this->db->limit($limit);
		$query = $this->db->get('news');
		return $query->result();
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
	public function getNewsWithSearch($search = '', $limit = 10, $offset = 0)
	{
		$this->db->select('*')->from('news')->where('is_deleted', 0)->where('news_status', 1);
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('news_title', $search);
			$this->db->or_like('news_description', $search);
			$this->db->group_end();
		}
		$this->db->order_by('news_id', 'DESC');
		$this->db->limit($limit, $offset); // $limit = per_page, $offset = offset
		return $this->db->get()->result();
	}

	public function countNewsWithSearch($search = '')
	{
		$this->db->from('news')->where('is_deleted', 0)->where('news_status', 1);
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('news_title', $search);
			$this->db->or_like('news_description', $search);
			$this->db->group_end();
		}
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
		return $this->db->select('events.*,user.name')
			->from('events')
			->join('user', 'events.user_id = user.user_id')
			->where('event_status', 1)
			->limit($limit)
			->order_by('event_id', 'DESC')
			->get()->result();
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

		// If not found, try to find by converting event names to slugs
		$events = $this->db->select('*')
			->from('events')
			->where('event_status', 1)
			->get()->result();

		foreach ($events as $event) {
			// Create slug from event name if event_slug is empty
			$generated_slug = $this->createSafeSlug($event->event_name);
			if ($generated_slug === $slug) {
				return $event;
			}
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

	public function getRelatedListing($tableName, $listing_id, $category_id)
	{
		$where = "WHERE listing_slug !='" . $listing_id . "' AND category_id = '" . $category_id . "'";

		return $this->db->query("SELECT * FROM $tableName $where ORDER BY listing_id DESC LIMIT 3")->result();
	}

	/**
	 * Get related events excluding current event
	 */
	public function getRelatedEvents($current_event_id, $limit = 3)
	{
		return $this->db->select('*')
			->from('events')
			->where('event_status', 1)
			->where('event_id !=', $current_event_id)
			->order_by('event_id', 'DESC')
			->limit($limit)
			->get()->result();
	}

	public function add($tableName, $data)
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

	public function getAllEnquiries()
	{
		return $this->db->select('*')
			->from('enquiries')
			->order_by('enquiry_created_at', 'DESC')
			->get()->result();
	}

	public function addEnquiry($data)
	{
		if ($this->db->insert('enquiries', $data)) {
			return true;
		} else {
			return false;
		}
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

	public function deleteEnquirySimple($enquiry_id)
	{
		$this->db->where('enquiry_id', $enquiry_id);
		$result = $this->db->delete('enquiries');

		// Log the database error if delete fails
		if (!$result) {
			log_message('error', 'Failed to delete enquiry. DB Error: ' . $this->db->error()['message']);
		}

		return $result;
	}

	public function claimBusiness($data)
	{
		if ($this->db->insert('claim_listings', $data)) {
			return true;
		} else {
			return false;
		}
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
	public function getRelatedVideos($exclude_video_id, $limit = 3)
	{
		$this->db->where('video_status', 1);
		$this->db->where('video_id !=', $exclude_video_id);
		$this->db->order_by('created_at', 'DESC');
		$this->db->limit($limit);
		$query = $this->db->get('video');
		return $query->result();
	}


	// Global Videos
	// Get videos with search and pagination (public)
	public function getAllVideos($search = '', $limit = 10, $offset = 0)
	{
		$this->db->select('video.video_id,video.video_title, video.video_description,video.video_status,video.created_at,video.video_slug,video.video_image, user.user_id,user.name,categories.category_name')
			->from('video')
			->join('user', 'user.user_id = video.user_id', 'LEFT')
			->join('video_categories as categories', 'categories.category_id = video.video_category_id', 'LEFT');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('video_title', $search);
			$this->db->or_like('video_description', $search);
			$this->db->group_end();
		}
		$this->db->order_by('video_id', 'DESC');
		$this->db->limit($limit, $offset);
		return $this->db->get()->result();
	}

	public function countVideosWithSearch($search = '')
	{
		$this->db->from('video');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('video_title', $search);
			$this->db->or_like('video_description', $search);
			$this->db->group_end();
		}
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
		$this->db->where('listings.listing_status', 0);
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
		$this->db->where('listings.listing_status', 0);
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

	// Get latest news for home page
	public function getLatestNews($limit = 5)
	{
		return $this->db->select('news.news_id, news.news_title, news.news_description, news.news_image, news.news_category_id')
			->from('news')
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

	public function getCategoryNameBySlug($category_slug, $table = 'listing_categories')
	{
		// First try exact slug match
		$result = $this->db->select('category_name')
			->from($table)
			->where('category_slug', $category_slug)
			->limit(1)
			->get()->row();

		if ($result) {
			return $result->category_name;
		}

		// If not found, try generated slug match
		$where_condition = "LOWER(REPLACE(category_name, ' ', '-')) = '" . $this->db->escape_str($category_slug) . "'";
		$result = $this->db->select('category_name')
			->from($table)
			->where($where_condition)
			->limit(1)
			->get()->row();

		return $result ? $result->category_name : 'General';
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
			// If slug is empty but we have category name, generate slug from name
			if (!empty($result->category_name)) {
				return strtolower(str_replace(' ', '-', $result->category_name));
			}
		}

		return null;
	}

	public function getCategoryIdBySlug($category_slug, $table = 'listing_categories')
	{
		$table = $this->db->escape_str($table);
		$result = $this->db->select($table . '.category_id')
			->from($table)
			->where($table . '.category_slug', $category_slug)
			->limit(1)
			->get()->row();
		return $result ? $result->category_id : null;
	}

	public function getCategoryBySlugOrId($identifier, $table = 'listing_categories')
	{
		// Try as ID first if numeric
		if (is_numeric($identifier)) {
			$result = $this->db->select('*')
				->from($table)
				->where('category_id', $identifier)
				->limit(1)
				->get()->row();
			if ($result) return $result;
		}

		// Try exact slug match
		$result = $this->db->select('*')
			->from($table)
			->where('category_slug', $identifier)
			->limit(1)
			->get()->row();

		if ($result) return $result;

		// Try generated slug match
		$result = $this->db->select('*')
			->from($table)
			->where('LOWER(REPLACE(category_name, " ", "-"))', $identifier)
			->limit(1)
			->get()->row();

		return $result ? $result : null;
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
				'generated_slug' => strtolower(str_replace(' ', '-', $cat->category_name))
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
		$query = $this->db->select('s.*, p.*')
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

		// -1 means unlimited
		if ($limit == -1) {
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
	 * Global search function that searches across all content types
	 */
	public function globalSearch($search_term, $limit = 50, $offset = 0)
	{
		$results = array(
			'listings' => array(),
			'news' => array(),
			'events' => array(),
			'blogs' => array(),
			'products' => array(),
			'house' => array(),
			'rental' => array(),
			'classifieds' => array(),
			'videos' => array(),
			'categories' => array(),
			'total_count' => 0
		);

		if (empty($search_term)) {
			return $results;
		}



		// Search in Listings
		$results['listings'] = $this->searchListings($search_term, 20);

		// Search in News
		$results['news'] = $this->searchNews($search_term, 20);

		// Search in Events
		$results['events'] = $this->searchEvents($search_term, 15);

		// Search in Blogs
		$results['blogs'] = $this->searchBlogs($search_term, 15);

		// Search in Products
		$results['products'] = $this->searchProducts($search_term, 15);

		// Search in House
		$results['house'] = $this->searchHouse($search_term, 15);

		// Search in Rental
		$results['rental'] = $this->searchRental($search_term, 15);

		// Search in Classifieds
		$results['classifieds'] = $this->searchClassifieds($search_term, 15);

		// Search in Videos
		$results['videos'] = $this->searchVideo($search_term, 15);

		// Search in Categories
		$results['categories'] = $this->searchCategories($search_term, 10);

		// Calculate total count
		$results['total_count'] = count($results['listings']) + count($results['news']) +
			count($results['events']) + count($results['blogs']) +
			count($results['products']) + count($results['house']) +
			count($results['rental']) + count($results['classifieds']) +
			count($results['videos']) + count($results['categories']);

		return $results;
	}

	/**
	 * Search in listings table
	 */
	public function searchListings($search_term, $limit = 20)
	{
		$this->db->select('listings.*, listing_categories.category_name, user.name as owner_name');
		$this->db->from('listings');
		$this->db->join('listing_categories', 'listings.category_id = listing_categories.category_id', 'left');
		$this->db->join('user', 'listings.user_id = user.user_id', 'left');
		$this->db->where('listings.is_deleted', 0);
		$this->db->where('listings.listing_status', 1);
		$this->db->group_start();
		$this->db->like('listings.listing_name', $search_term);
		$this->db->or_like('listings.listing_description', $search_term);
		$this->db->or_like('listings.listing_address', $search_term);
		$this->db->or_like('listings.service_locations', $search_term);
		$this->db->or_like('listing_categories.category_name', $search_term);
		$this->db->group_end();
		$this->db->limit($limit);
		$this->db->order_by('listings.listing_id', 'DESC');

		return $this->db->get()->result();
	}

	/**
	 * Search in news table
	 */
	public function searchNews($search_term, $limit = 20)
	{
		$this->db->select('news.*, user.name as author_name');
		$this->db->from('news');
		$this->db->join('user', 'news.user_id = user.user_id', 'left');
		$this->db->where('news.news_status', 1);
		$this->db->group_start();
		$this->db->like('news.news_title', $search_term);
		$this->db->or_like('news.news_description', $search_term);
		$this->db->group_end();
		$this->db->limit($limit);
		$this->db->order_by('news.news_id', 'DESC');

		return $this->db->get()->result();
	}

	/**
	 * Search in events table
	 */
	public function searchEvents($search_term, $limit = 15)
	{
		$this->db->select('events.*, user.name as organizer_name');
		$this->db->from('events');
		$this->db->join('user', 'events.user_id = user.user_id', 'left');
		$this->db->where('events.event_status', 1);
		$this->db->group_start();
		$this->db->like('events.event_name', $search_term);
		$this->db->or_like('events.event_description', $search_term);
		$this->db->or_like('events.event_address', $search_term);
		$this->db->group_end();
		$this->db->limit($limit);
		$this->db->order_by('events.event_id', 'DESC');

		return $this->db->get()->result();
	}

	/**
	 * Search in blogs table
	 */
	public function searchBlogs($search_term)
	{
		$this->db->select('*');
		$this->db->from('blogs');
		$this->db->like('blog_name', $search_term, 'both');
		// Optionally search in description as well:
		// $this->db->or_like('blog_description', $query, 'both');
		$this->db->order_by('blog_id', 'DESC');
		return $this->db->get()->result();
	}

	/**
	 * Search in products table
	 */
	public function searchProducts($search_term, $limit = 15)
	{
		$this->db->select('products.*, user.name as seller_name');
		$this->db->from('products');
		$this->db->join('user', 'products.user_id = user.user_id', 'left');
		$this->db->where('products.product_status', 1);
		$this->db->group_start();
		$this->db->like('products.product_name', $search_term);
		$this->db->or_like('products.product_description', $search_term);
		$this->db->group_end();
		$this->db->limit($limit);
		$this->db->order_by('products.product_id', 'DESC');

		return $this->db->get()->result();
	}

	/**
	 * Search in categories table
	 */
	public function searchCategories($search_term, $limit = 10)
	{
		$this->db->select('listing_categories.*, COUNT(listings.listing_id) as listing_count');
		$this->db->from('listing_categories');
		$this->db->join('listings', 'listing_categories.category_id = listings.category_id AND listings.is_deleted = 0', 'left');
		$this->db->group_start();
		$this->db->like('listing_categories.category_name', $search_term);
		$this->db->or_like('listing_categories.category_description', $search_term);
		$this->db->group_end();
		$this->db->group_by('listing_categories.category_id');
		$this->db->limit($limit);
		$this->db->order_by('listing_categories.category_id', 'DESC');

		return $this->db->get()->result();
	}

	/**
	 * Search in house table
	 */
	public function searchHouse($search_term, $limit = 15)
	{
		$this->db->select('house.*, user.name as owner_name');
		$this->db->from('house');
		$this->db->join('user', 'house.user_id = user.user_id', 'left');
		$this->db->where('house.house_status', 0); // 0 = active based on database
		$this->db->group_start();
		$this->db->like('house.house_title', $search_term);
		$this->db->or_like('house.house_description', $search_term);
		$this->db->or_like('house.house_address', $search_term);
		$this->db->group_end();
		$this->db->limit($limit);
		$this->db->order_by('house.house_id', 'DESC');

		return $this->db->get()->result();
	}

	/**
	 * Search in rental table
	 */
	public function searchRental($search_term, $limit = 15)
	{
		$this->db->select('rental.*, user.name as owner_name');
		$this->db->from('rental');
		$this->db->join('user', 'rental.user_id = user.user_id', 'left');
		$this->db->where('rental.rental_status', 0); // 0 = active based on database
		$this->db->group_start();
		$this->db->like('rental.rental_title', $search_term);
		$this->db->or_like('rental.rental_description', $search_term);
		$this->db->or_like('rental.rental_address', $search_term);
		$this->db->group_end();
		$this->db->limit($limit);
		$this->db->order_by('rental.rental_id', 'DESC');

		return $this->db->get()->result();
	}

	/**
	 * Search in classifieds table
	 */
	public function searchClassifieds($search_term, $limit = 15)
	{
		$this->db->select('classifieds.*, user.name as poster_name');
		$this->db->from('classifieds');
		$this->db->join('user', 'classifieds.user_id = user.user_id', 'left');
		$this->db->where('classifieds.classifieds_status', 1); // 1 = active based on database
		$this->db->group_start();
		$this->db->like('classifieds.classifieds_title', $search_term);
		$this->db->or_like('classifieds.classifieds_description', $search_term);
		$this->db->group_end();
		$this->db->limit($limit);
		$this->db->order_by('classifieds.classifieds_id', 'DESC');

		return $this->db->get()->result();
	}

	/**
	 * Search in video table
	 */
	public function searchVideo($search_term, $limit = 15)
	{
		$this->db->select('video.*, user.name as uploader_name');
		$this->db->from('video');
		$this->db->join('user', 'video.user_id = user.user_id', 'left');
		$this->db->where('video.video_status', 1); // 1 = active based on database
		$this->db->group_start();
		$this->db->like('video.video_title', $search_term);
		$this->db->or_like('video.video_description', $search_term);
		$this->db->group_end();
		$this->db->limit($limit);
		$this->db->order_by('video.video_id', 'DESC');

		return $this->db->get()->result();
	}

	/**
	 * Get search suggestions for autocomplete
	 */
	public function getSearchSuggestions($search_term, $limit = 10)
	{
		$suggestions = array();

		if (strlen($search_term) >= 2) {
			// Get suggestions from listings
			$this->db->select('listing_name as suggestion, "listing" as type, listing_id as id');
			$this->db->from('listings');
			$this->db->like('listing_name', $search_term);
			$this->db->where('listing_status', 1);
			$this->db->where('is_deleted', 0);
			$this->db->limit(5);
			$listings = $this->db->get()->result();

			// Get suggestions from categories
			$this->db->select('category_name as suggestion, "category" as type, category_id as id');
			$this->db->from('listing_categories');
			$this->db->like('category_name', $search_term);
			$this->db->limit(3);
			$categories = $this->db->get()->result();

			// Get suggestions from news
			$this->db->select('news_title as suggestion, "news" as type, news_id as id');
			$this->db->from('news');
			$this->db->like('news_title', $search_term);
			$this->db->where('news_status', 1);
			$this->db->limit(2);
			$news = $this->db->get()->result();

			$suggestions = array_merge($listings, $categories, $news);
		}

		return array_slice($suggestions, 0, $limit);
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

		$this->db->select('news.*, user.name as author_name');
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

		$this->db->select('events.*, user.name as organizer_name');
		$this->db->from('events');
		$this->db->join('user', 'events.user_id = user.user_id', 'left');
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

		$this->db->select('blogs.*, user.name as author_name');
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
		// Debug: Log the data being inserted
		error_log('Inserting review data: ' . print_r($data, true));

		if ($this->db->insert('listing_reviews', $data)) {
			$insert_id = $this->db->insert_id();
			error_log('Review inserted successfully with ID: ' . $insert_id);

			// Update listing average rating
			$this->updateListingRating($data['listing_id']);
			return $insert_id;
		} else {
			// Log database error
			error_log('Database error: ' . $this->db->error()['message']);
			return false;
		}
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
		// Calculate average rating
		$avg_query = $this->db->select('AVG(rating) as avg_rating, COUNT(*) as total_reviews')
			->from('listing_reviews')
			->where('listing_id', $listing_id)
			->where('review_status', 1)
			->get()->row();

		// Debug: Log the calculated values
		error_log('Listing ID: ' . $listing_id . ' - Avg Rating: ' . $avg_query->avg_rating . ' - Total Reviews: ' . $avg_query->total_reviews);

		// Update listings table with proper handling of NULL values
		$update_data = [
			'average_rating' => $avg_query->avg_rating ? round($avg_query->avg_rating, 1) : 0,
			'total_reviews' => $avg_query->total_reviews ? $avg_query->total_reviews : 0
		];

		$result = $this->db->where('listing_id', $listing_id)->update('listings', $update_data);

		// Debug: Log the update result
		error_log('Update result for listing ' . $listing_id . ': ' . ($result ? 'SUCCESS' : 'FAILED'));

		return $result;
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
}
