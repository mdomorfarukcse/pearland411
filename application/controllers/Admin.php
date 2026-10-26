<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
{

	/*====================
	Add House
	=====================*/
	public function add_house()
	{
		$data['users'] = $this->adminmodel->getActiveUsersForDropdown();
		$data['all_house_category'] = $this->adminmodel->getAllValues('house_categories');
		$this->form_validation->set_rules('house_title', 'Property Title', 'trim|required');
		$this->form_validation->set_rules('house_price', 'Property Price', 'trim|required');
		$this->form_validation->set_rules('user_id', 'User', 'required');
		$this->form_validation->set_rules('house_category_id', 'Category', 'required');
		if ($this->form_validation->run() == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/add_house', $data);
			$this->load->view('admin/footer');
		} else {
			$user_id = $this->input->post('user_id');

			// Subscription check before adding house
			if (!$this->checkSubscriptionBeforeAction($user_id, 'house')) {
				redirect('admin/house-list');
				return;
			}

			$info = array();
			$info['user_id'] = $user_id;
			$info['house_title'] = $this->input->post('house_title');
			$info['house_price'] = $this->input->post('house_price');
			$info['house_category_id'] = $this->input->post('house_category_id');
			$info['featured_house'] = $this->input->post('featured_house') ? 1 : 0;
			$info['spotlight_house'] = $this->input->post('spotlight_house') ? 1 : 0;
			$info['display_contact'] = $this->input->post('display_contact') ? 1 : 0;
			$info['house_description'] = $this->input->post('house_description');
			$info['house_address'] = $this->input->post('house_address');
			$info['bedrooms'] = $this->input->post('bedrooms');
			$info['living_area'] = $this->input->post('living_area');
			$info['bathroom'] = $this->input->post('bathroom');
			$info['property_age'] = $this->input->post('property_age');
			$info['garage'] = $this->input->post('garage');
			$info['floors'] = $this->input->post('floors');
			$info['rent_to_own'] = $this->input->post('rent_to_own') ? 1 : 0;
			$info['can_sublet'] = $this->input->post('can_sublet') ? 1 : 0;
			$info['house_seo_title'] = $this->input->post('house_seo_title');
			$info['house_seo_keywords'] = $this->input->post('house_seo_keywords');
			$info['house_seo_description'] = $this->input->post('house_seo_description');
			$info['start_date'] = $this->input->post('start_date');
			$info['end_date'] = $this->input->post('end_date');
			$info['payment'] = $this->input->post('payment');
			// Generate slug
			$info['house_slug'] = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('house_title'));
			// Set house_status (default active)
			$info['house_status'] = 1;
			// Handle image upload
			if ($_FILES && isset($_FILES['house_image']) && $_FILES['house_image']['name']) {
				$img_info = [
					'upload_path' => 'images/houses',
					'quality' => '60%',
					'height' => 250,
					'width' => 400,
					'image_name' => 'house_image'
				];
				if (function_exists('uploadAndResize')) {
					$info['house_image'] = uploadAndResize($img_info);
				} else {
					$config['upload_path'] = 'images/houses';
					$config['allowed_types'] = 'jpg|jpeg|png|gif';
					$config['max_size'] = 2048;
					$this->load->library('upload', $config);
					if ($this->upload->do_upload('house_image')) {
						$upload_data = $this->upload->data();
						$info['house_image'] = 'images/houses/' . $upload_data['file_name'];
					} else {
						$info['house_image'] = '';
					}
				}
			} else {
				$info['house_image'] = '';
			}
			if ($this->adminmodel->addHouse($info)) {
				// Track usage after successful house creation
				$this->trackUsageAfterSuccess($user_id, 'house');

				$this->session->set_flashdata('message', 'House added successfully!');
				redirect('admin/house-list');
			} else {
				$this->session->set_flashdata('message', 'Failed to add house!');
				redirect('admin/add-house');
			}
		}
	}

	/*====================
	  Edit House
	=====================*/
	public function edit_house($slug)
	{
		$data['users'] = $this->adminmodel->getActiveUsersForDropdown();
		$data['house'] = $this->adminmodel->getSingleValue($slug, 'house_slug', 'house');
		$data['all_house_category'] = $this->adminmodel->getAllValues('house_categories');
		if ($this->form_validation->run('house') == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/edit_house', $data);
			$this->load->view('admin/footer');
		} else {
			$house = $data['house'];
			$info = array();
			$info['user_id'] = $this->input->post('user_id');
			$info['house_title'] = $this->input->post('house_title');
			$info['house_price'] = $this->input->post('house_price');
			$info['house_category_id'] = $this->input->post('house_category_id');
			$info['featured_house'] = $this->input->post('featured_house');
			$info['spotlight_house'] = $this->input->post('spotlight_house');
			$info['display_contact'] = $this->input->post('display_contact');
			$info['house_description'] = $this->input->post('house_description');
			$info['house_address'] = $this->input->post('house_address');
			$info['bedrooms'] = $this->input->post('bedrooms');
			$info['living_area'] = $this->input->post('living_area');
			$info['bathroom'] = $this->input->post('bathroom');
			$info['property_age'] = $this->input->post('property_age');
			$info['garage'] = $this->input->post('garage');
			$info['floors'] = $this->input->post('floors');
			$info['rent_to_own'] = $this->input->post('rent_to_own');
			$info['can_sublet'] = $this->input->post('can_sublet');
			$info['house_seo_title'] = $this->input->post('house_seo_title');
			$info['house_seo_keywords'] = $this->input->post('house_seo_keywords');
			$info['house_seo_description'] = $this->input->post('house_seo_description');
			$info['start_date'] = $this->input->post('start_date');
			$info['end_date'] = $this->input->post('end_date');
			$info['payment'] = $this->input->post('payment');
			$info['house_slug'] = $this->input->post('house_slug');
			// Set house_status (default active if not set)
			$info['house_status'] = $this->input->post('house_status') !== null ? $this->input->post('house_status') : 1;

			// Handle image upload
			$old_image = $house->house_image;
			if ($_FILES && isset($_FILES['house_image']) && $_FILES['house_image']['name']) {
				$img_info = [
					'upload_path' => 'images/houses',
					'quality'     => '60%',
					'height'      => 250,
					'width'       => 400,
					'image_name'  => 'house_image'
				];
				// Delete old image if exists
				if ($old_image && file_exists('./' . $old_image)) {
					unlink('./' . $old_image);
				}
				if (function_exists('uploadAndResize')) {
					$info['house_image'] = uploadAndResize($img_info);
				} else {
					// fallback: use default CI upload
					$config['upload_path'] = 'images/houses';
					$config['allowed_types'] = 'jpg|jpeg|png|gif';
					$config['max_size'] = 2048;
					$this->load->library('upload', $config);
					if ($this->upload->do_upload('house_image')) {
						$upload_data = $this->upload->data();
						$info['house_image'] = 'images/houses/' . $upload_data['file_name'];
					} else {
						$info['house_image'] = $old_image;
					}
				}
			} else {
				$info['house_image'] = $old_image;
			}

			// Update house record
			if ($this->adminmodel->update($slug, $info, 'house_slug', 'house')) {
				$this->session->set_flashdata('message', 'House updated successfully!');
				redirect('admin/house-list');
			} else {
				$this->session->set_flashdata('message', 'Failed to update house!');
				redirect('admin/edit_house/' . $slug);
			}
		}
	}

	/*====================
	  Delete House
	=====================*/
	public function delete_house($slug)
	{
		if ($this->adminmodel->deleteHouse($slug)) {
			$this->session->set_flashdata('message', 'House deleted successfully!');
		} else {
			$this->session->set_flashdata('message', 'Failed to delete house!');
		}
		redirect('admin/house-list');
	}
	/*====================
			All House List
		=====================*/
	public function house_list($page = 1)
	{
		$this->load->library('pagination');
		$total_rows = $this->adminmodel->countAllHouses2();
		$config = [
			'base_url' => base_url('admin/house-list'),
			'total_rows' => $total_rows,
			'per_page' => admin_per_page(20),
			'reuse_query_string' => TRUE,
			'uri_segment' => 3,
			'use_page_numbers' => TRUE,
			'num_links' => 3,
			'page_query_string' => FALSE,
			// Bootstrap 4 Pagination Styling
			'full_tag_open'    => '<ul class="pagination">',
			'full_tag_close'   => '</ul>',
			'first_tag_open'   => '<li class="page-item">',
			'first_tag_close'  => '</li>',
			'last_tag_open'    => '<li class="page-item">',
			'last_tag_close'   => '</li>',
			'cur_tag_open'     => '<li class="page-item active"><a href="#" class="page-link">',
			'cur_tag_close'    => '</a></li>',
			'num_tag_open'     => '<li class="page-item">',
			'num_tag_close'    => '</li>',
			'prev_tag_open'    => '<li class="page-item">',
			'prev_tag_close'   => '</li>',
			'next_tag_open'    => '<li class="page-item">',
			'next_tag_close'   => '</li>',
			'first_link'       => 'First',
			'last_link'        => 'Last',
			'next_link'        => 'Next',
			'prev_link'        => 'Previous',
			'attributes'       => ['class' => 'page-link'],
		];
		$this->pagination->initialize($config);
		$page = ($page < 1) ? 1 : $page;
		$offset = ($page - 1) * $config['per_page'];
		$data['start_number'] = $offset + 1;
		$data['houseList'] = $this->adminmodel->getHouses($config['per_page'], $offset);
		$data['link'] = $this->pagination->create_links();

		// Collect house IDs from the current page and fetch their view counts
		$house_ids = [];
		if (!empty($data['houseList']) && is_array($data['houseList'])) {
			foreach ($data['houseList'] as $h) {
				if (isset($h->house_id)) $house_ids[] = $h->house_id;
			}
		}
		$data['views_counts'] = $this->adminmodel->getViewsForIds('house', 'house_id', 'views', !empty($house_ids) ? $house_ids : null);
		$this->load->view('admin/header');
		$this->load->view('admin/all_house', $data);
		$this->load->view('admin/footer');
	}

	/*====================
	  Newsletter Subscribers
	=====================*/

	public function newsletter_subscribers($page = 1)
	{
		$this->load->library('pagination');
		$total_rows = $this->adminmodel->countAllNewsletterSubscribers();
		$config = [
			'base_url' => base_url('admin/newsletter-subscribers'),
			'total_rows' => $total_rows,
			'per_page' => admin_per_page(20),
			'reuse_query_string' => TRUE,
			'uri_segment' => 3,
			'use_page_numbers' => TRUE,
			'num_links' => 3,
			'page_query_string' => FALSE,
			'full_tag_open'    => '<ul class="pagination">',
			'full_tag_close'   => '</ul>',
			'first_tag_open'   => '<li class="page-item">',
			'first_tag_close'  => '</li>',
			'last_tag_open'    => '<li class="page-item">',
			'last_tag_close'   => '</li>',
			'cur_tag_open'     => '<li class="page-item active"><a href="#" class="page-link">',
			'cur_tag_close'    => '</a></li>',
			'num_tag_open'     => '<li class="page-item">',
			'num_tag_close'    => '</li>',
			'prev_tag_open'    => '<li class="page-item">',
			'prev_tag_close'   => '</li>',
			'next_tag_open'    => '<li class="page-item">',
			'next_tag_close'   => '</li>',
			'first_link'       => 'First',
			'last_link'        => 'Last',
			'next_link'        => 'Next',
			'prev_link'        => 'Previous',
			'attributes'       => ['class' => 'page-link'],
		];
		$this->pagination->initialize($config);
		$page = ($page < 1) ? 1 : $page;
		$offset = ($page - 1) * $config['per_page'];
		$data['start_number'] = $offset + 1;
		$data['subscribers'] = $this->adminmodel->getNewsletterSubscribers($config['per_page'], $offset);
		$data['link'] = $this->pagination->create_links();
		$this->load->view('admin/header');
		$this->load->view('admin/newsletter_subscribers', $data);
		$this->load->view('admin/footer');
	}

	public function delete_newsletter_subscriber($id)
	{
		if ($this->adminmodel->deleteNewsletterSubscriber($id)) {
			$this->session->set_flashdata('message', 'Subscriber deleted successfully!');
		} else {
			$this->session->set_flashdata('message', 'Failed to delete subscriber!');
		}
		redirect('admin/newsletter-subscribers');
	}

	public function cancel_newsletter_subscription($id)
	{
		// Set is_confirmed to 0 (cancel)
		if ($this->adminmodel->updateNewsletterSubscriberStatus($id, 0)) {
			$this->session->set_flashdata('message', 'Subscription cancelled successfully!');
		} else {
			$this->session->set_flashdata('message', 'Failed to cancel subscription!');
		}
		redirect('admin/newsletter-subscribers');
	}

	public function __construct()
	{
		parent::__construct();
		$this->load->model('adminmodel');
		$this->load->model('usermodel');
		$this->load->library('pagination');

		if ($this->input->post('logout')) {
			$this->session->unset_userdata('logged_admin');
			$this->session->sess_destroy();
			redirect(base_url('admin'));
			return;
		}

		// Admin pages reachable WITHOUT a session. Everything else
		// requires a logged-in admin (session key 'logged_admin' —
		// the end-user 'logged_user' key cannot satisfy this check).
		$public_actions = array('index', 'login', 'forgot_password');
		$page = $this->router->fetch_method();

		// Routes restricted to super_admin only — sub-admin management,
		// billing/subscriptions, ad sales, pricing-plan editing. Anything
		// not in this list is reachable by any logged-in admin.
		$super_admin_only = array(
			'sub_admin_all', 'sub_admin_create', 'sub_admin_edit', 'sub_admin_delete',
			'pricing', 'pricing_plan', 'add_pricing_plan', 'edit_pricing_plan',
			'delete_pricing_plan', 'toggle_pricing_plan',
			// The AJAX half of the pricing screen. Missing from this list, an
			// editor could not open /admin/pricing but could re-price any
			// term by posting to these directly.
			'get_pricing_plan', 'get_pricing_plan_durations', 'update_pricing_duration',
			'add_pricing_duration', 'delete_pricing_duration',
			'all_subscriptions', 'subscription_view',
			'cancel_subscription', 'delete_subscription', 'purchase_subscription',
			'billing', 'billing_customer', 'billing_card_init', 'billing_card_save',
			'billing_charge', 'billing_card_delete', 'billing_recurring_save',
			'current_ads', 'create_ads', 'ads_edit', 'ads_delete',
			'listing_banner_add', 'listing_banner_remove',
			'ads_price', 'create_ads_price', 'ads_price_edit',
			'ads_request', 'ads_history', 'ads_history_delete', 'approveAdStatus',
			// Shows the SMTP username and raw SMTP transcripts, and can send
			// mail from the site's address. Super-admin only.
			'email_diagnostics',
			// Member moderation: suspending or deleting an account.
			'user_suspend', 'user_restore', 'user_delete',
		);

		if (!in_array($page, $public_actions, true)) {
			$admin = $this->session->userdata('logged_admin');
			if (empty($admin) || empty($admin['logged_id'])) {
				redirect(base_url('admin'));
				return;
			}
			// Sessions written before this commit will lack the role
			// key — treat them as editor (safer default).
			if (in_array($page, $super_admin_only, true)) {
				$role = isset($admin['role']) ? $admin['role'] : 'editor';
				if ($role !== 'super_admin') {
					$this->session->set_flashdata('error', 'This area is restricted to super-admins.');
					redirect(base_url('admin/dashboard'));
					return;
				}
			}
		}
	}

	/*Home Page && Login Page*/
	public function index()
	{
		if (isset($_POST['admin_submit'])) {/*Registration Action*/
			//Register User If data valid
			$data['loginSucess'] = $this->adminmodel->login();
			if ($data['loginSucess']['status']) {
				redirect($data['loginSucess']['url']);
			} else {
				$data['reset'] = FALSE;
			}
		} else {
			$data['loginSucess'] = array('status' => FALSE, 'message' => '', 'referror' => '');
			$data['reset'] = FALSE;
		}
		$this->load->view('admin/login');
	}

	/*====================
	All House Category
	=====================*/
	public function all_house_category($page = 1)
	{
		$this->load->library('pagination');

		// Get total house category count
		$total_rows = $this->adminmodel->countAllHouseCategories();

		$config = [
			'base_url' => base_url('admin/all-house-category'),
			'total_rows' => $total_rows,
			'per_page' => admin_per_page(20),
			'reuse_query_string' => TRUE,
			'uri_segment' => 3,
			'use_page_numbers' => TRUE,
			'num_links' => 3,
			'page_query_string' => FALSE,
			'full_tag_open'    => '<ul class="pagination">',
			'full_tag_close'   => '</ul>',
			'first_tag_open'   => '<li class="page-item">',
			'first_tag_close'  => '</li>',
			'last_tag_open'    => '<li class="page-item">',
			'last_tag_close'   => '</li>',
			'cur_tag_open'     => '<li class="page-item active"><a href="#" class="page-link">',
			'cur_tag_close'    => '</a></li>',
			'num_tag_open'     => '<li class="page-item">',
			'num_tag_close'    => '</li>',
			'prev_tag_open'    => '<li class="page-item">',
			'prev_tag_close'   => '</li>',
			'next_tag_open'    => '<li class="page-item">',
			'next_tag_close'   => '</li>',
			'first_link'       => 'First',
			'last_link'        => 'Last',
			'next_link'        => 'Next',
			'prev_link'        => 'Previous',
			'attributes'       => ['class' => 'page-link'],
		];

		$this->pagination->initialize($config);

		// page may be page number (1-based) or offset depending on routing; normalize to offset
		$page = ($page < 1) ? 1 : (int)$page;
		$offset = ($page - 1) * $config['per_page'];

		$data = [];
		// Try to use a model method with counts if available
		if (method_exists($this->adminmodel, 'allHouseCategoryWithCount')) {
			$data['all_house_category'] = $this->adminmodel->allHouseCategoryWithCount($config['per_page'], $offset);
		} else {
			$data['all_house_category'] = $this->adminmodel->getHouseCategories($config['per_page'], $offset);
		}

		$data['start_number'] = $offset + 1;
		$data['link'] = $this->pagination->create_links();

		$this->load->view('admin/header');
		$this->load->view('admin/all_house_category', $data);
		$this->load->view('admin/footer');
	}

	/*====================
	Add House Category
	=====================*/
	public function add_house_category()
	{
		$this->form_validation->set_rules('category_name', 'Category Name', 'trim|required|is_unique[house_categories.category_name]');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/add_house_category');
			$this->load->view('admin/footer');
		} else {
			$category_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('category_name'));
			$data = array();
			$data['category_name'] = $this->input->post('category_name');
			$data['category_slug'] = $category_slug;
			$data['category_seo_title'] = $this->input->post('category_seo_title');
			$data['category_seo_keywords'] = $this->input->post('category_seo_keywords');
			$data['category_seo_description'] = $this->input->post('category_seo_description');

			if ($_FILES && isset($_FILES['category_image']['name']) && $_FILES['category_image']['name']) {
				$img_info = [
					'upload_path' => 'images/services',
					'quality' => '60%',
					'height' => 250,
					'width' => 400,
					'image_name' => 'category_image'
				];
				if (function_exists('uploadAndResize')) {
					$data['category_image'] = uploadAndResize($img_info);
				} else {
					$config['upload_path'] = 'images/services';
					$config['allowed_types'] = 'jpg|jpeg|png|gif';
					$config['max_size'] = 2048;
					$this->load->library('upload', $config);
					if ($this->upload->do_upload('category_image')) {
						$upload_data = $this->upload->data();
						$data['category_image'] = 'images/services/' . $upload_data['file_name'];
					} else {
						$data['category_image'] = '';
					}
				}
			} else {
				$data['category_image'] = '';
			}

			if ($this->adminmodel->addHouseCategory($data)) {
				$this->session->set_flashdata('message', 'Successfully Added House Category!!');
				redirect('admin/all-house-category');
			} else {
				$this->session->set_flashdata('message', 'Adding House Category failed!!');
				redirect('admin/add-house-category');
			}
		}
	}

	/*====================
	Edit House Category
	=====================*/
	public function edit_house_category($slug = NULL)
	{
		$data['category'] = $this->adminmodel->getSingleValue($slug, 'category_slug', 'house_categories');
		$old_image = isset($data['category']->category_image) ? $data['category']->category_image : '';

		$this->form_validation->set_rules('category_name', 'Category Name', 'trim|required');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/edit_house_category', $data);
			$this->load->view('admin/footer');
		} else {
			$category_slug_new = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('category_name'));
			$info = array();
			$info['category_name'] = $this->input->post('category_name');
			$info['category_slug'] = $category_slug_new;
			$info['category_seo_title'] = $this->input->post('category_seo_title');
			$info['category_seo_keywords'] = $this->input->post('category_seo_keywords');
			$info['category_seo_description'] = $this->input->post('category_seo_description');

			if ($_FILES && isset($_FILES['category_image']['name']) && $_FILES['category_image']['name']) {
				$img_info = [
					'upload_path' => 'images/services',
					'quality' => '60%',
					'height' => 250,
					'width' => 400,
					'image_name' => 'category_image'
				];
				if ($old_image && file_exists('./' . $old_image)) {
					@unlink('./' . $old_image);
				}
				if (function_exists('uploadAndResize')) {
					$info['category_image'] = uploadAndResize($img_info);
				} else {
					$config['upload_path'] = 'images/services';
					$config['allowed_types'] = 'jpg|jpeg|png|gif';
					$config['max_size'] = 2048;
					$this->load->library('upload', $config);
					if ($this->upload->do_upload('category_image')) {
						$upload_data = $this->upload->data();
						$info['category_image'] = 'images/services/' . $upload_data['file_name'];
					} else {
						$info['category_image'] = $old_image;
					}
				}
			} else {
				$info['category_image'] = $old_image;
			}

			if ($this->adminmodel->updateHouseCategory($slug, $info)) {
				$this->session->set_flashdata('message', 'Successfully Updated House Category!!');
				redirect('admin/all-house-category');
			} else {
				$this->session->set_flashdata('message', 'Sorry, Update House Category failed!!');
				redirect('admin/edit_house_category/' . $slug, 'refresh');
			}
		}
	}

	/*====================
	Delete House Category
	=====================*/
	public function delete_house_category($slug)
	{
		if ($this->adminmodel->deleteHouseCategory($slug)) {
			$this->session->set_flashdata('message', 'House Category has been Permanently Deleted!!!');
		} else {
			$this->session->set_flashdata('message', 'Deleting House Category failed!!');
		}
		redirect('admin/all-house-category', 'refresh');
	}

	/*Forget Password*/
	public function forgot_password()
	{
		$this->load->view('admin/header');
		$this->load->view('admin/forgot_password');
		$this->load->view('admin/footer');
	}
	/*Logout*/
	public function logout()
	{
		$this->session->unset_userdata('logged_admin');
		$this->session->sess_destroy();
		redirect(base_url());
		die();
	}

	/**
	 * Admin self-service password change (any logged-in admin).
	 * Requires the current password as a confirmation step so a
	 * left-unattended browser session can't be hijacked into a
	 * password reset.
	 */

	/**
	 * Site-wide settings: SEO/analytics tokens, social profile URLs,
	 * Google Maps API key, contact info, default OG image. Persisted
	 * to the `site_settings` table (see migration 0007) and read
	 * everywhere via the site_setting() helper.
	 */
	public function admin_setting()
	{
		// Keys we expose in the form. Anything else in the table is
		// left alone (and not echoed back to the page).
		$data['nav_categories'] = $this->usermodel->getAllValues('listing_categories');

		$known_keys = array(
			// General
			'site_title', 'site_description', 'site_keywords',
			'site_og_image', 'site_logo_url', 'contact_phone', 'contact_email',
			// Where /request-quote submissions are emailed.
			'service_quote_email',
			// SEO / analytics
			'google_analytics_id', 'google_site_verification',
			'bing_site_verification', 'facebook_app_id',
			// Maps
			'google_maps_api_key',
			// Social
			'social_facebook_url', 'social_twitter_url',
			'social_instagram_url', 'social_linkedin_url',
			'social_youtube_url',
			// Service registry — master on/off per optional content section.
			// A hidden field ships '0' when the checkbox is unticked, so the
			// generic save loop stores '1'/'0' correctly.
			'service_events_enabled', 'service_realestate_enabled',
			'service_classifieds_enabled', 'service_news_enabled',
			'service_blog_enabled', 'service_products_enabled',
			'service_videos_enabled',
			// Listings tools. Unlike the service keys above this one defaults
			// to OFF — see duplicate_listing_enabled() in view_helper.php.
			'feature_duplicate_listing_enabled',
			// The two category slots in the top menu. A category slug pins the
			// slot; blank falls back to keyword detection; '-' removes the item.
			'nav_category_1', 'nav_category_2',
			// One address for every public form. Blank falls back to
			// contact_email, then MAIL_FROM_ADDRESS.
			'forms_recipient_email',
			// Enquiry routing. The departments a visitor can address a message
			// to (one per line; blank uses the built-in list), and an optional
			// `Department = address` mailbox per line for each. These replaced
			// the named sales reps the forms used to list — see migration 0026.
			'enquiry_departments', 'enquiry_department_emails',
			// How many unsold zones on a page may fill with a house
			// ("advertise here") creative. Defaults to 2 — see ad_slot.php.
			// Every zone filling meant a page of house ads and no advertisers,
			// which reads as "nobody advertises here". 0 = never show one;
			// -1 = no cap, the old behaviour.
			'ads_house_max_per_page',
			// Ceiling on the freehand amount staff can charge at
			// /admin/billing. Every other charge in the system is priced
			// server-side from a plan; this one is typed, so it gets a limit.
			// Defaults to 25000 — see Admin::billingAmount(). 0 = no limit.
			'billing_manual_charge_max',
			// Who is copied on billing activity — a card going on file, a
			// payment taken, a recurring schedule changed, a renewal that
			// failed. One address per line, ON TOP OF every active admin.
			'billing_notify_emails',
			// How many live deals one member may have on /coupons at once.
			// Posting a deal is free with any listing, so this is a spam
			// ceiling rather than a plan entitlement. Defaults to 10 — see
			// User::couponCap().
			'coupon_max_per_member',
			// Customer billing terms: the renewal reminder's lead time
			// (Cli::send_renewal_reminders) and the refund policy's request
			// window and optional extra wording (partials/refund_policy.php).
			'renewal_reminder_days', 'refund_request_days', 'refund_policy_extra',
			// Spam protection on the public forms (helpers/spam_helper.php).
			// Both default ON; reCAPTCHA also needs its keys in .env.
			'recaptcha_enabled', 'spam_name_filter_enabled',
		);

		if ($this->input->method() === 'post') {
			$saved = 0;
			foreach ($known_keys as $k) {
				$v = (string) $this->input->post($k, true);
				if (site_setting_save($k, $v)) $saved++;
			}
			$this->session->set_flashdata(
				$saved > 0 ? 'message' : 'error',
				$saved > 0 ? "Settings saved ({$saved} updated)." : 'Could not save — has migration 0007_site_settings.sql been run?'
			);
			redirect('admin/setting');
			return;
		}

		$data['settings'] = array();
		foreach ($known_keys as $k) {
			$data['settings'][$k] = (string) site_setting($k, '');
		}
		$data['has_table'] = $this->db->table_exists('site_settings');

		// Payments status. is_configured() is a cheap local check and always
		// runs; health_check() makes a live API call, so it only runs when the
		// operator asks for it (?payments_check=1) rather than adding round-trip
		// latency to every load of this page.
		$this->load->library('helcim_lib');
		$data['helcim_configured'] = $this->helcim_lib->is_configured();
		$data['helcim_currency']   = $this->helcim_lib->currency();
		$data['helcim_health']     = ($data['helcim_configured'] && $this->input->get('payments_check'))
			? $this->helcim_lib->health_check()
			: null;

		$this->load->view('admin/header');
		$this->load->view('admin/admin_setting', $data);
		$this->load->view('admin/footer');
	}

	/**
	 * /admin/email-diagnostics — why the site's email is or isn't arriving.
	 *
	 * This page exists because of the September 2026 outage: signup
	 * verification and password-reset mail stopped being delivered on the live
	 * site, and there was no way to tell from inside the application whether
	 * the messages were being refused by the relay, accepted and then dropped
	 * by the recipient, or never attempted at all. The only failure report was
	 * a log_message('error') going to a directory that did not exist.
	 *
	 * Three things, in the order you need them:
	 *   1. What the mailer is configured to do, and which of those settings
	 *      are known to break delivery (Mailer::config_status()).
	 *   2. A test send that shows the actual SMTP error rather than a boolean.
	 *   3. The last N attempts from `email_log`, so you can see whether a
	 *      given customer's message left the building and when.
	 */
	public function email_diagnostics()
	{
		$this->load->library('mailer');

		$data = array();
		$data['nav_categories'] = $this->usermodel->getAllValues('listing_categories');

		if ($this->input->method() === 'post' && $this->input->post('send_test') !== null) {
			$result = $this->mailer->test_send($this->input->post('test_email', true));
			if (!empty($result['success'])) {
				$this->session->set_flashdata('message', 'Test message handed to the mail server. '
					. 'If it does not arrive within a few minutes the problem is delivery, not '
					. 'configuration — check the From-domain warning above.');
			} else {
				// The transcript names the problem; admins are the only
				// audience and it never reaches a public page.
				$this->session->set_flashdata('error', 'Test send failed: ' . $result['error']);
			}
			redirect('admin/email-diagnostics');
			return;
		}

		$data['mail_config']    = $this->mailer->config_summary();
		$data['mail_issues']    = $this->mailer->config_status();
		$data['mail_ready']     = $this->mailer->is_configured();
		$data['has_log_table']  = $this->db->table_exists('email_log');

		// Default recipient for the test box: whoever is signed in.
		$admin = $this->session->userdata('logged_admin');
		$data['test_default'] = (!empty($admin['admin_email'])) ? $admin['admin_email'] : '';

		// CI's logger writes nowhere when its directory is missing, which is
		// how the original failures vanished. Say so on the page rather than
		// leaving an admin to discover it.
		$log_path = (string) $this->config->item('log_path');
		if ($log_path === '') {
			$log_path = APPPATH . 'logs/';
		}
		$data['log_path']     = $log_path;
		$data['log_writable'] = is_dir($log_path) && is_writable($log_path);

		$data['recent'] = array();
		$data['stats']  = array('sent' => 0, 'failed' => 0);
		if ($data['has_log_table']) {
			$show = admin_per_page(50);
			$data['recent'] = $this->db
				->order_by('id', 'DESC')
				->limit($show)
				->get('email_log')
				->result();

			// Last 7 days, so a page loaded during an outage says "42 failed"
			// rather than making someone count rows.
			$since = date('Y-m-d H:i:s', strtotime('-7 days'));
			$data['stats']['sent'] = (int) $this->db
				->where('created_at >=', $since)->where('status', 'sent')
				->count_all_results('email_log');
			$data['stats']['failed'] = (int) $this->db
				->where('created_at >=', $since)->where('status', 'failed')
				->count_all_results('email_log');
		}

		$this->load->view('admin/header');
		$this->load->view('admin/email_diagnostics', $data);
		$this->load->view('admin/footer');
	}

	public function change_password()
	{
		$session = $this->session->userdata('logged_admin');
		$admin_id = (int) ($session['logged_id'] ?? 0);
		$admin = $this->adminmodel->getAdminByIdRaw($admin_id);
		if (!$admin) { show_404(); return; }

		if ($this->input->method() === 'post') {
			$this->form_validation->set_rules('current_password', 'Current Password', 'required');
			$this->form_validation->set_rules('new_password',     'New Password',     'required|min_length[8]');
			$this->form_validation->set_rules('new_password_confirm', 'Confirm New Password', 'required|matches[new_password]');

			if (!$this->form_validation->run()) {
				$this->session->set_flashdata('error', strip_tags(validation_errors()));
				redirect('admin/change-password');
				return;
			}

			// Verify the current password — accept either bcrypt or
			// the legacy MD5 hash so this also works as a one-off
			// upgrade for the seeded admin@gmail.com / 12345 account.
			$current = (string) $this->input->post('current_password');
			$stored  = (string) $admin->admin_password;
			$ok = false;
			if (strlen($stored) > 0 && $stored[0] === '$') {
				$ok = password_verify($current, $stored);
			} elseif (strlen($stored) === 32 && ctype_xdigit($stored)) {
				$ok = hash_equals(strtolower($stored), md5($current));
			}
			if (!$ok) {
				$this->session->set_flashdata('error', 'Current password is incorrect.');
				redirect('admin/change-password');
				return;
			}

			$this->adminmodel->updateAdminMeta(
				$admin_id,
				array(),
				(string) $this->input->post('new_password')
			);

			$this->session->set_flashdata('message', 'Password updated. Please sign in again with your new password.');
			$this->session->unset_userdata('logged_admin');
			$this->session->sess_destroy();
			redirect(base_url('admin'));
			return;
		}

		$data = array('admin_row' => $admin);
		$this->load->view('admin/header');
		$this->load->view('admin/change_password', $data);
		$this->load->view('admin/footer');
	}


	/*====================
	Dashboard
	=====================*/
	public function dashboard()
	{
		/*$data['user'] = $this->usermodal->getUserInformation();
		$data['wdhistory'] = $this->usermodal->withdrowhistory($data['user']->id);
		$data['tphistory'] = $this->usermodal->topuphistory($data['user']->id);
		if (isset($_POST['messageSend'])) {
			$data['messagerep'] = $this->usermodal->messageSend($_POST['message'], $data['user']->id, "me");
		}
		$data['options'] = $this->usermodal->getOptions();
		$data['title'] = "Starsfair | Dashboard";
		$data['cart'] = $this->usermodal->getCartItem();
		$data['qty'] = $this->usermodal->getCart();*/
		$data = array();
		$data['user_count'] = $this->adminmodel->countAllUsers();
		$data['listing_count'] = $this->adminmodel->countAllListings();
		$data['event_count'] = $this->adminmodel->countAllEvents();
		$data['product_count'] = $this->adminmodel->countAllProducts();
		$data['house_count'] = $this->adminmodel->countAllHouses();
		$data['classified_count'] = $this->adminmodel->countAllClassifieds();
		$data['rental_count'] = $this->adminmodel->countAllRentals();
		$data['ads_count'] = $this->adminmodel->countAllAds();
		$data['active_ads_count'] = $this->adminmodel->countActiveAds();
		$data['pending_review_count'] = $this->adminmodel->countAllEnquiries();

		// System Performance Data
		$server_load = is_array(sys_getloadavg()) ? round(sys_getloadavg()[0] * 10) : 0;
		$db_usage = 45; // Example: set manually or calculate based on your DB size/quota
		$storage_used = round((disk_total_space('/') - disk_free_space('/')) / disk_total_space('/') * 100);
		$data['system_performance'] = [
			'server_load' => $server_load,
			'db_usage' => $db_usage,
			'storage_used' => $storage_used
		];

		$data['monthly_user_stats'] = $this->adminmodel->getMonthlyUserStats();
		$data['monthly_revenue_stats'] = $this->adminmodel->getMonthlyRevenueStats();
		$data['monthly_listing_stats'] = $this->adminmodel->getMonthlyListingStats();
		$data['monthly_product_stats'] = $this->adminmodel->getMonthlyProductStats();

		// Total subscription revenue (sums subscriptions.total_amount)
		// We return a formatted value to the view; controller keeps raw numeric value too.
		$data['revenue'] = $this->adminmodel->getTotalSubscriptionRevenue();

		$this->load->view('admin/header');
		$this->load->view('admin/dashboard', $data);
		$this->load->view('admin/footer');
	}
	/*====================
	Duplicate listings
	=====================*/

	/**
	 * Businesses the directory is holding more than once.
	 *
	 * /all_listing/shopping-retail said "42 businesses" over a first page that
	 * repeated the same shops. Migration 0032 clears the unambiguous cases —
	 * where the duplicate is unclaimed, so hiding it takes nothing away from
	 * anybody. This screen is the rest: pairs where BOTH rows have an owner,
	 * and anything created since the migration ran.
	 *
	 * Nothing here is automatic. A person looks at the two rows and decides,
	 * because "same name, same phone number" is a franchise and a head office
	 * as often as it is one business entered twice.
	 */
	public function duplicate_listings()
	{
		$data = array();
		$data['user']   = $this->adminmodel->getAdminInformation();
		$data['groups'] = $this->adminmodel->getDuplicateListingGroups(admin_per_page(50));

		$this->load->view('admin/header');
		$this->load->view('admin/duplicate_listings', $data);
		$this->load->view('admin/footer');
	}

	/**
	 * Hide one listing as a duplicate of another.
	 *
	 * A soft delete, exactly as the Trash button does one — the row keeps its
	 * reviews and its enquiries and is one click from coming back at
	 * /admin/trash_listings. It also records which listing it duplicated, in
	 * the same `listing_duplicate_merges` table migration 0032 writes, so the
	 * two are indistinguishable afterwards.
	 */
	public function hide_duplicate_listing()
	{
		if ($this->input->method() !== 'post') {
			show_404();
			return;
		}

		$hide = (int) $this->input->post('listing_id');
		$keep = (int) $this->input->post('kept_listing_id');

		$result = $this->adminmodel->hideDuplicateListing($hide, $keep);

		$this->session->set_flashdata('message', $result['message']);
		redirect('admin/duplicate_listings');
	}

	/*====================
	All Category
	=====================*/

	public function all_listing_category()
	{
		// Searched in SQL, not in the browser. The old inline filter only saw
		// the rows the server had already sent, so a category on page 2 came
		// back "No matches". reuse_query_string carries ?q= across the pager.
		$q = trim((string) $this->input->get('q', TRUE));

		$config = [
			'base_url' => base_url('admin/all_listing_category'),
			'per_page' => admin_per_page(20),
			'reuse_query_string' => TRUE,
			'total_rows' => $this->adminmodel->countListingCategories($q),
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
		$page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
		$data['start_number'] = $page + 1;
		$data['all_listing_category']   = $this->adminmodel->allListingCategory($config['per_page'], $page, $q);
		$data['link'] 		   = $this->pagination->create_links();
		$data['search_query']  = $q;
		$data['total_rows']    = $config['total_rows'];

		//////////////////////////

		$data['user'] 				  = $this->adminmodel->getAdminInformation();

		// Categories that read as the same thing. Detected over the WHOLE
		// table, not the page being shown, because the two halves of a pair
		// are alphabetically adjacent and a pager still splits them often
		// enough ("Beauty & Personal Care" and "Beauty and Personal Care" sort
		// four rows apart, but nothing says they land on the same page).
		$data['duplicate_groups'] = $this->adminmodel->getDuplicateCategoryGroups();
		// Any pair, for the merges that are a judgement call rather than a
		// spelling difference.
		$data['merge_options']    = $this->adminmodel->getCategoriesForMerge();

		$this->load->view('admin/header');
		$this->load->view('admin/all_listing_category', $data);
		$this->load->view('admin/footer');
	}

	/**
	 * form_validation callback: no other category already reads like this one.
	 *
	 * @param string $name  what was typed
	 * @param int    $self  the category being renamed, excluded from the check
	 */
	public function category_name_available($name, $self = 0)
	{
		$clash = $this->adminmodel->findCategoryByMergeKey($name, (int) $self);

		if ($clash) {
			$this->form_validation->set_message(
				'category_name_available',
				'&ldquo;' . html_escape($clash->category_name) . '&rdquo; already covers that &mdash; '
				. 'the two names read the same. Use it, or rename it first.'
			);
			return FALSE;
		}

		return TRUE;
	}

	/*====================
	Merge one category into another
	=====================*/

	/**
	 * Move everything filed under `from` to `to`, then delete `from`.
	 *
	 * This is the manual half of migration 0031. The migration merges the
	 * pairs that are mechanically the same name — "Arts & Entertainment" and
	 * "Arts and Entertainment" — and stops there on purpose. Whether Finance
	 * and Financial Services are one category, or Education and Education &
	 * Childcare, is an editorial decision about this directory, and this is
	 * where it gets made.
	 *
	 * POST only, and it writes the same `listing_category_merges` audit row
	 * the migration does, so the two are indistinguishable afterwards.
	 */
	public function merge_listing_category()
	{
		if ($this->input->method() !== 'post') {
			show_404();
			return;
		}

		$from = (int) $this->input->post('from_category_id');
		$to   = (int) $this->input->post('to_category_id');

		$result = $this->adminmodel->mergeListingCategory($from, $to);

		$this->session->set_flashdata(!empty($result['ok']) ? 'message' : 'error', $result['message']);
		redirect('admin/all_listing_category');
	}


	/*====================
	Add new Category
	=====================*/

	public function add_new_listing_category()
	{
		// is_unique[] compares the string, so it happily accepted
		// "Arts and Entertainment" next to "Arts & Entertainment" — which is
		// how /all-category ended up offering both, with the businesses split
		// between them. category_name_available[] compares the two the way a
		// reader does.
		$this->form_validation->set_rules(
			'category_name',
			'Category Name',
			'trim|required|is_unique[listing_categories.category_name]|callback_category_name_available'
		);

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/add_new_listing_category');
			$this->load->view('admin/footer');
		} else {
			$data = array();
			$data['category_name'] = $this->input->post('category_name');
			$data['category_slug'] = $this->makeUniqueCategorySlug($data['category_name']);

			if ($_FILES && $_FILES['category_image']['name']) {
				$img_info = [
					'upload_path' => 'images/services',
					'quality'     => '60%',
					'height'      => 250,
					'width'       => 400,
					'image_name'  => 'category_image'
				];
				$data['category_image'] = uploadAndResize($img_info);
			}

			// Editorial header banner (migration 0027). Guarded on the column so
			// the form still works on an install that has not run 0027.
			if (!empty($_FILES['category_banner']['name'])
				&& $this->db->field_exists('category_banner', 'listing_categories')) {
				$data['category_banner'] = $this->uploadCategoryBanner($banner_error);
				if (!$data['category_banner']) {
					$this->session->set_flashdata('error', 'The category was added without a header banner: ' . $banner_error);
				}
			}

			if ($this->adminmodel->addListingCategory($data)) {
				$this->session->set_flashdata('message', 'Successfully Added Listing Category!!');
				redirect('admin/all_listing_category');
			} else {
				$this->session->set_flashdata('message', 'Adding Listing Category failed!!');
			}
		}
	}

	/*====================
	Edit Category
	=====================*/
	public function edit_listing_category($id)
	{
		$data['category'] = $this->adminmodel->getSingleValue($id, 'category_id', 'listing_categories');
		$old_image        = $data['category']->category_image;

		// The edit form had no uniqueness rule of any kind, so renaming a
		// category onto an existing one was simply allowed.
		$this->form_validation->set_rules(
			'category_name',
			'Category Name',
			'trim|required|callback_category_name_available[' . (int) $id . ']'
		);

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/edit_listing_category', $data);
			$this->load->view('admin/footer');
		} else {
			$info['category_name'] = $this->input->post('category_name');

			// Only (re)generate the slug when there isn't one. Rewriting it on
			// every rename would silently break every link and bookmark to
			// the category that is already out there.
			if (empty($data['category']->category_slug)) {
				$info['category_slug'] = $this->makeUniqueCategorySlug($info['category_name'], $id);
			}

			// Upload first, unlink after. Both images used to delete the old
			// file BEFORE the new upload ran, so a rejected upload (too big,
			// wrong type) left the category with a dead path and no picture.
			$upload_notes = array();

			$info['category_image'] = $old_image;
			if (!empty($_FILES['category_image']['name'])) {
				$new_image = uploadAndResize([
					'upload_path' => 'images/services',
					'quality'     => '60%',
					'height'      => 250,
					'width'       => 400,
					'image_name'  => 'category_image'
				]);
				if ($new_image) {
					$info['category_image'] = $new_image;
					if ($old_image && $old_image !== $new_image && file_exists('./' . $old_image)) {
						@unlink('./' . $old_image);
					}
				} else {
					$upload_notes[] = 'The icon was not changed: ' . (upload_last_error() ?: 'the file could not be read.');
				}
			}

			// The editorial header banner (migration 0027). Wide artwork behind
			// the title on /all_listing/<slug>; NULL means "use the standard
			// directory header", which is what every category did before.
			// Written only when the column exists, so the screen keeps working
			// on an install that has not run 0027 yet.
			if ($this->db->field_exists('category_banner', 'listing_categories')) {
				$old_banner = isset($data['category']->category_banner)
					? $data['category']->category_banner : null;
				$info['category_banner'] = $old_banner;

				if (!empty($_FILES['category_banner']['name'])) {
					$new_banner = $this->uploadCategoryBanner($banner_error);
					if ($new_banner) {
						$info['category_banner'] = $new_banner;
						if ($old_banner && $old_banner !== $new_banner && file_exists('./' . $old_banner)) {
							@unlink('./' . $old_banner);
						}
					} else {
						$upload_notes[] = 'The header banner was not changed: ' . $banner_error;
					}
				} elseif ($this->input->post('remove_category_banner')) {
					if ($old_banner && file_exists('./' . $old_banner)) {
						@unlink('./' . $old_banner);
					}
					$info['category_banner'] = null;
				}
			}

			if ($upload_notes) {
				$this->session->set_flashdata('error', implode(' ', $upload_notes));
			}

			if ($this->adminmodel->updateListingCategory($id, $info)) {
				$this->session->set_flashdata('message', 'Successfully Updated Listing Category!!');
				redirect('admin/all_listing_category', 'refresh');
			} else {
				$this->session->set_flashdata('message', 'Sorry, Update Listing Category failed!!');
				redirect('admin/edit_listing_category', 'refresh');
			}
		}
	}

	/**
	 * Upload a category header banner and check it is the right shape.
	 *
	 * The hero it fills is 1600x420 and is cropped with background-size:
	 * cover, so a square logo or a small photo was blown up and cut down to
	 * a meaningless strip. The upload is refused (and the new file removed)
	 * unless it is at least 1200px wide and at least 2.5x as wide as it is
	 * tall — 1600x420 is 3.8:1, so this still allows some latitude.
	 *
	 * @param  string $error set to the reason on failure
	 * @return string|null the stored path
	 */
	private function uploadCategoryBanner(&$error = '')
	{
		$error = '';
		$path  = uploadAndResize([
			'upload_path' => 'images/services',
			'quality'     => '80%',
			'height'      => 420,
			'width'       => 1600,
			'image_name'  => 'category_banner'
		]);
		if (!$path) {
			$error = upload_last_error() ?: 'the file could not be read.';
			return null;
		}

		// Measured on the ORIGINAL upload: uploadAndResize() may downscale
		// a very large file, which keeps the proportions anyway.
		$size = @getimagesize('./' . $path);
		if ($size && ($size[0] < 1200 || $size[0] < 2.5 * $size[1])) {
			@unlink('./' . $path);
			$error = 'it is ' . (int) $size[0] . ' x ' . (int) $size[1] . ' px. A header banner needs to be wide — '
				. '1600 x 420 px is ideal (at least 1200 px wide, and 2.5 times as wide as it is tall).';
			return null;
		}

		return $path;
	}

	/*====================
	Delete Listing Category
	=====================*/
	public function delete_listing_category($id)
	{
		if ($this->adminmodel->deleteCategory($id)) {
			redirect('admin/all_listing_category', 'refresh');
		} else {
			redirect('admin/all_listing_category', 'refresh');
		}
	}

	/*==============================
	Delete Multiple Listing Category
	===============================*/
	public function delete_selected_listing_category()
	{
		$ids = $this->input->post('category_ids');

		if (empty($ids) || !is_array($ids)) {
			$this->session->set_flashdata('message', 'No categories were selected to delete.');
			redirect('admin/all_listing_category', 'refresh');
			return;
		}

		$deleted = 0;
		foreach ($ids as $id) {
			$id = (int) $id;
			if ($id > 0 && $this->adminmodel->deleteCategory($id)) {
				$deleted++;
			}
		}

		if ($deleted > 0) {
			$this->session->set_flashdata('message', 'Successfully deleted ' . $deleted . ' listing ' . ($deleted === 1 ? 'category' : 'categories') . '!!');
		} else {
			$this->session->set_flashdata('message', 'Deleting selected listing categories failed!!');
		}

		redirect('admin/all_listing_category', 'refresh');
	}

	/*====================
	All Sub Category
	=====================*/
	public function all_listing_sub_category($page = 1)
	{
		$this->load->library('pagination');

		// Initialize data array. start_number is seeded here because the
		// filtered-by-category branch below does not paginate and would
		// otherwise leave the view numbering its rows from an undefined variable.
		$data = array();
		$data['link'] = '';
		$data['start_number'] = 1;
		$data['total_rows'] = 0;

		// Get category ID from URL if present
		$id = $this->input->get('category_id');

		// Searched in SQL for the same reason as the category list above: the
		// browser-side filter could only ever match the current page.
		$q = trim((string) $this->input->get('q', TRUE));
		$data['search_query'] = $q;

		if (!empty($id)) {
			$data['all_listing_sub_category'] = $this->adminmodel->ListingSubCategoryByCateId($id);
		} else {
			// Get total record count
			$total_rows = $this->adminmodel->countAllListingSubCategory($q);
			$data['total_rows'] = $total_rows;

			// Pagination Configuration
			// 100 per page, matching all_listing_category(): sub-categories are
			// few, the inline search in admin-table.js only sees the rows the
			// server sent, and the view asks for data-page-size="0" so the whole
			// page is visible at once. Bulk delete ticks what you can see.
			$config = [
				'base_url'         => base_url('admin/all_listing_sub_category'),
				'total_rows'       => $total_rows,
				'per_page' => admin_per_page(20),
				'reuse_query_string' => TRUE,
				'uri_segment'      => 3,
				'use_page_numbers' => TRUE,
				'num_links'        => 3,
				'page_query_string' => FALSE,

				// Bootstrap 4 Pagination Styling
				'full_tag_open'    => '<ul class="pagination">',
				'full_tag_close'   => '</ul>',
				'first_tag_open'   => '<li class="page-item">',
				'first_tag_close'  => '</li>',
				'last_tag_open'    => '<li class="page-item">',
				'last_tag_close'   => '</li>',
				'cur_tag_open'     => '<li class="page-item active"><a href="#" class="page-link">',
				'cur_tag_close'    => '</a></li>',
				'num_tag_open'     => '<li class="page-item">',
				'num_tag_close'    => '</li>',
				'prev_tag_open'    => '<li class="page-item">',
				'prev_tag_close'   => '</li>',
				'next_tag_open'    => '<li class="page-item">',
				'next_tag_close'   => '</li>',
				'first_link'       => 'First',
				'last_link'        => 'Last',
				'next_link'        => 'Next',
				'prev_link'        => 'Previous',
				'attributes'       => ['class' => 'page-link'],
			];

			$this->pagination->initialize($config);

			// Calculate the correct offset
			$page = ($page < 1) ? 1 : $page;
			$offset = ($page - 1) * $config['per_page'];
			$data['start_number'] = ($page - 1) * $config['per_page'] + 1;

			// Fetch paginated records
			$data['all_listing_sub_category'] = $this->adminmodel->allListingSubCategory($config['per_page'], $offset, $q);
			$data['link'] = $this->pagination->create_links();
		}

		$data['user'] = $this->adminmodel->getAdminInformation();

		// Load views
		$this->load->view('admin/header');
		$this->load->view('admin/all_listing_sub_category', $data);
		$this->load->view('admin/footer');
	}

	/*====================
	Add new Sub Category
	=====================*/
	public function add_new_listing_sub_category()
	{
		$this->form_validation->set_rules('sub_category_name', 'Sub Category Name', 'trim|required|is_unique[listing_sub_categories.sub_category_name]');

		if ($this->form_validation->run() == FALSE) {
			$data['all_listing_category'] = $this->adminmodel->allListingCategory();
			$this->load->view('admin/header');
			$this->load->view('admin/add_new_listing_sub_category', $data);
			$this->load->view('admin/footer');
		} else {
			$listing_sub_cat_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('sub_category_name'));
			$data = array();

			$data['sub_category_name'] 	= $this->input->post('sub_category_name');
			$data['category_id'] 		= $this->input->post('category_id');
			$data['sub_category_slug'] 	= $listing_sub_cat_slug;

			if ($this->adminmodel->addListingSubCategory($data)) {
				$this->session->set_flashdata('message', 'Sub Category has been Added Successfully!!');
				redirect('admin/all_listing_sub_category');
			} else {
				$this->session->set_flashdata('message', 'Sub Category has been Failed!!');
			}
		}
	}
	/*====================
	Add Edit Sub Category
	=====================*/

	public function edit_listing_sub_category($id)
	{
		$data['sub_category'] = $this->adminmodel->get_listing_sub_category_ById($id);
		$data['allListingCategory'] = $this->adminmodel->allListingCategory(null, null);

		$this->form_validation->set_rules('sub_category_name', 'Sub Category Name', 'trim|required');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/edit_listing_sub_category', $data);
			$this->load->view('admin/footer');
		} else {
			$listing_sub_cat_id = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('sub_category_name'));

			$info['category_id'] 		= $this->input->post('category_id');
			$info['sub_category_name'] 	= $this->input->post('sub_category_name');
			$info['sub_category_id'] 	= $listing_sub_cat_id;

			if ($this->adminmodel->updateListingSubCategory($id, $info)) {
				$this->session->set_flashdata('message', 'Sub Category has been Updated Successfully.');
				redirect('admin/all_listing_sub_category', 'refresh');
			} else {
				redirect('admin/edit_listing_sub_category', 'refresh');
			}
		}
	}

	/*====================
	Delete Listing Sub Category
	=====================*/
	public function delete_listing_sub_category($id)
	{
		if ($this->adminmodel->deleteListingSubCategory($id)) {
			$this->session->set_flashdata('message', 'Sub Category has been Permanently Deleted!!!');
			redirect('admin/all_listing_sub_category', 'refresh');
		} else {
			redirect('admin/all_listing_sub_category', 'refresh');
		}
	}

	/*==================================
	Delete Multiple Listing Sub Category
	===================================*/
	public function delete_selected_listing_sub_category()
	{
		$ids = $this->input->post('sub_category_ids');

		if (empty($ids) || !is_array($ids)) {
			$this->session->set_flashdata('message', 'No sub categories were selected to delete.');
			redirect('admin/all_listing_sub_category', 'refresh');
			return;
		}

		$deleted = 0;
		foreach ($ids as $id) {
			$id = (int) $id;
			if ($id > 0 && $this->adminmodel->deleteListingSubCategory($id)) {
				$deleted++;
			}
		}

		if ($deleted > 0) {
			$this->session->set_flashdata('message', 'Successfully deleted ' . $deleted . ' sub ' . ($deleted === 1 ? 'category' : 'categories') . '!!');
		} else {
			$this->session->set_flashdata('message', 'Deleting selected sub categories failed!!');
		}

		redirect('admin/all_listing_sub_category', 'refresh');
	}
	/*====================
	Get Listing Sub Category
	=====================*/
	public function get_listing_sub_category()
	{

		if (!empty($this->input->post('category_id'))) {
			$category_id = $this->input->post('category_id');
			$data['sub_categories'] = $this->adminmodel->ListingSubCategoryByCateId($category_id);
			// What is already ticked, so switching category does not wipe it.
			$data['selected'] = array_map('intval', (array) $this->input->post('selected'));
			$this->load->view('admin/get_sub_categories', $data);
		}
	}
	/*====================
	All Listing
	=====================*/
	public function all_listings()
	{
		// ?q= searches every listing in SQL. The box above this table used to be
		// a browser-side filter over the ten rows the server had already sent,
		// so a business on page four could not be found by typing its name.
		$q = trim((string) $this->input->get('q', TRUE));

		$config = [
			'base_url' => base_url('admin/all_listings'),
			'per_page' => admin_per_page(20),
			'total_rows' => $this->adminmodel->countAdminListings($q),
			'uri_segment' => 3,
			'reuse_query_string' => TRUE,
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

		$page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
		$data['AllListings']   = $this->adminmodel->getAllListings($config['per_page'], $page, $q);
		$data['start_number'] = $page + 1;
		$data['search_query'] = $q;
		$data['total_rows']   = $config['total_rows'];

		// Collect listing IDs and get their view counts via the new reusable model method
		$listing_ids = array();
		foreach ($data['AllListings'] as $row) {
			if (isset($row->listing_id)) {
				$listing_ids[] = $row->listing_id;
			}
		}

		$data['views_counts'] = $this->adminmodel->getViewsForIds('listings', 'listing_id', 'views', $listing_ids);

		$data['link'] 		   = $this->pagination->create_links();

		$this->load->view('admin/header');
		$this->load->view('admin/all_listings', $data);
		$this->load->view('admin/footer');
	}
	/*====================
	Add  Listing
	=====================*/
	public function add_new_listings()
	{
		// This page exists only to offer the choice between "from scratch" and
		// "duplicate". With duplication switched off there is nothing to choose,
		// so go straight to the form rather than making the admin click twice.
		if (!duplicate_listing_enabled()) {
			redirect('admin/add_listing_scratch');
			return;
		}

		$this->load->view('admin/header');
		$this->load->view('admin/add_new_listings');
		$this->load->view('admin/footer');
	}

	/*====================
	Add New Listing (Singular URL Handler)
	=====================*/
	public function add_new_listing()
	{
		// Same page under the singular URL — same shortcut.
		if (!duplicate_listing_enabled()) {
			redirect('admin/add_listing_scratch');
			return;
		}

		$this->load->view('admin/header');
		$this->load->view('admin/add_new_listings');
		$this->load->view('admin/footer');
	}

	/*====================
	Add New Listing From Scratch
	=====================*/
	public function add_listing_scratch()
	{
		$data['categories'] 	= $this->usermodel->getAllValues('listing_categories');
		$data['users'] 		  	= $this->adminmodel->getActiveUsersForDropdown();
		// Every POST goes through validation. This used to be gated on a
		// non-empty user_id, so submitting with no user selected fell straight
		// through to the render at the bottom: the form came back blank, with
		// no error and no listing created, which is what "the Submit Listing
		// button does nothing" was. user_id is a required rule, so an empty one
		// is now reported like any other missing field.
		if ($this->input->method(TRUE) === 'POST') {
			if ($this->form_validation->run('admin_listing_scratch') == FALSE) {
				$this->load->view('admin/header', $data);
				$this->load->view('admin/add_new_listings_start');
				$this->load->view('admin/footer');
				return;
			} else {
				// Two businesses with the same name produced the same slug, and
				// both the delete and the restore paths key on the slug — so one
				// delete took out both rows. Same helper the CSV importer uses.
				$listing_slug = $this->makeUniqueListingSlug($this->input->post('listing_name'));
				$info = array(
					'user_id' => $this->input->post('user_id'),
					'listing_name' => $this->input->post('listing_name'),
					'featured_listing' => ($this->input->post('featured_listing') ? 1 : 0),
					'spotlight_listing' => ($this->input->post('spotlight_listing') ? 1 : 0),
					'category_id' => $this->input->post('category_id'),
					// First of the selected sub-categories. The full set goes into
					// listing_sub_category_map after the write; this column is kept
					// in step so the readers that still use it keep working.
					'sub_category_id' => ($__subs = $this->postedSubCategoryIds()) ? $__subs[0] : NULL,
					'listing_mobile' => $this->input->post('listing_mobile'),
					'listing_whatsapp' => $this->input->post('listing_whatsapp'),
					'display_phone' => $this->input->post('display_phone'),
					'listing_email' => $this->input->post('listing_email'),
					'listing_website' => $this->input->post('listing_website'),
					'listing_address' => $this->input->post('listing_address'),
					'county_name' => $this->input->post('county_name'),
					'postal_code' => $this->input->post('postal_code'),
					'listing_lat' => $this->input->post('listing_lat'),
					'listing_lng' => $this->input->post('listing_lng'),
					'display_address' => $this->input->post('display_address'),
					'listing_description' => $this->input->post('listing_description'),
					'service_locations' => $this->input->post('service_locations'),
					'service_offered' => $this->input->post('service_offered'),
					'listing_video' => $this->input->post('listing_video'),
					'listing_seo_title' => $this->input->post('listing_seo_title'),
					'listing_seo_keywords' => $this->input->post('listing_seo_keywords'),
					'listing_seo_description' => $this->input->post('listing_seo_description'),
					'listing_slug' => $listing_slug,
					// Publish it. The column's default is the unpublished value,
					// so a listing added here used to save successfully and then
					// be invisible to every public query — the same defect
					// 0020_listing_status_normalise.sql fixed for the CSV import,
					// which is why import_listings() writes this explicitly too.
					'listing_status' => 1
				);

				// Auto-generate Google Map embed URL from lat/lng
				$lat = $this->input->post('listing_lat');
				$lng = $this->input->post('listing_lng');
				if (!empty($lat) && !empty($lng)) {
					$info['google_map'] = "https://maps.google.com/maps?q={$lat},{$lng}&output=embed";
				}

				if ($_FILES && $_FILES['listing_profile_image']['name']) {
					$img_info = [
						'upload_path' => 'images/listings',
						'quality'     => '60%',
						'image_name'  => 'listing_profile_image'
					];
					$info['listing_image'] = uploadWithoutResize($img_info);
				}

				$cover_notes = array();
				$this->applyListingCover($info, $cover_notes);

				// Handle Gallery Images Upload
				if ($_FILES && isset($_FILES['listing_image_gallery']['name'][0]) && !empty($_FILES['listing_image_gallery']['name'][0])) {
					$gallery_images = [];
					$files_count = count($_FILES['listing_image_gallery']['name']);

					for ($i = 0; $i < $files_count; $i++) {
						if (!empty($_FILES['listing_image_gallery']['name'][$i])) {
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
							}
						}
					}
					if (!empty($gallery_images)) {
						$info['listing_image_gallery'] = json_encode($gallery_images);
					}
				}

				if ($this->adminmodel->addListing($info)) {
					$this->adminmodel->saveListingSubCategories($this->db->insert_id(), $this->postedSubCategoryIds());
					$this->session->set_flashdata($cover_notes ? 'error' : 'message',
						'Listing added successfully.' . ($cover_notes ? ' ' . implode(' ', $cover_notes) : ''));
					redirect('admin/all_listings', 'refresh');
				} else {
					// Say why. This used to redirect with no flashdata at all, so a
					// rejected insert looked exactly like the button doing nothing.
					$this->session->set_flashdata('error', 'Could not save the listing. ' . $this->adminmodel->lastDbError());
					redirect('admin/add_listing_scratch', 'refresh');
				}
			}
		}
		$this->load->view('admin/header', $data);
		$this->load->view('admin/add_new_listings_start');
		$this->load->view('admin/footer');
	}
	/*==================================================
	Import Listings from a CSV / Excel (.xlsx) file
	==================================================*/
	public function import_listings()
	{
		$this->load->helper('spreadsheet');

		// Categories are shown in the on-screen reference so importers know
		// which category names are valid.
		$data['categories'] = $this->usermodel->getAllValues('listing_categories');

		if ($this->input->post('import_submit') === null) {
			$this->load->view('admin/header', $data);
			$this->load->view('admin/import_listings', $data);
			$this->load->view('admin/footer');
			return;
		}

		// --- File presence / upload-error checks -------------------------
		if (empty($_FILES['import_file']['name'])) {
			$this->session->set_flashdata('error', 'Please choose a CSV or Excel (.xlsx) file to import.');
			redirect('admin/import-listings');
			return;
		}
		if ($_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
			$this->session->set_flashdata('error', 'The file could not be uploaded. Please try again.');
			redirect('admin/import-listings');
			return;
		}

		$original_name = $_FILES['import_file']['name'];
		$ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
		$allowed = array('csv', 'txt', 'xlsx');
		if (!in_array($ext, $allowed, true)) {
			$this->session->set_flashdata('error', 'Unsupported file type ".' . $ext . '". Please upload a .csv or .xlsx file.');
			redirect('admin/import-listings');
			return;
		}

		list($rows, $read_error) = spreadsheet_read_rows($_FILES['import_file']['tmp_name'], $ext);
		if ($read_error !== null) {
			$this->session->set_flashdata('error', $read_error);
			redirect('admin/import-listings');
			return;
		}
		if (count($rows) < 2) {
			$this->session->set_flashdata('error', 'The file has no data rows. Add at least one listing under the header row.');
			redirect('admin/import-listings');
			return;
		}

		// --- Map the header row to known columns -------------------------
		$header = array_shift($rows);
		$col_map = array();
		foreach ($header as $index => $label) {
			$key = $this->normalizeImportHeader($label);
			if ($key !== '') {
				$col_map[$key] = $index;
			}
		}

		if (!isset($col_map['listing_name'])) {
			$this->session->set_flashdata('error', 'The file must contain a "listing_name" column. Download the sample template for the correct format.');
			redirect('admin/import-listings');
			return;
		}

		// Text columns that map straight through from the sheet.
		$text_columns = array(
			'listing_mobile', 'listing_whatsapp', 'listing_email', 'listing_website',
			'listing_address', 'county_name', 'postal_code', 'listing_lat', 'listing_lng',
			'listing_description', 'service_locations', 'service_offered', 'listing_video',
			'listing_seo_title', 'listing_seo_keywords', 'listing_seo_description',
		);

		$default_user_id = $this->adminmodel->getDefaultImportUserId();

		$imported = 0;
		$skipped = 0;
		$errors = array();
		$row_number = 1; // header was row 1

		foreach ($rows as $row) {
			$row_number++;

			$get = function ($key) use ($row, $col_map) {
				if (!isset($col_map[$key])) {
					return '';
				}
				$idx = $col_map[$key];
				return isset($row[$idx]) ? trim((string) $row[$idx]) : '';
			};

			$listing_name = $get('listing_name');
			if ($listing_name === '') {
				$skipped++;
				$errors[] = "Row {$row_number}: skipped (empty listing_name).";
				continue;
			}

			// Resolve category (by name or numeric id).
			$category_id = null;
			$category_val = $get('category');
			if ($category_val !== '') {
				$category_id = ctype_digit($category_val)
					? (int) $category_val
					: $this->adminmodel->getCategoryIdByName($category_val);
				if ($category_id === null) {
					$skipped++;
					$errors[] = "Row {$row_number}: skipped (unknown category \"{$category_val}\").";
					continue;
				}
			}

			// Resolve sub-category (optional).
			$sub_category_id = null;
			$sub_val = $get('sub_category');
			if ($sub_val !== '') {
				$sub_category_id = ctype_digit($sub_val)
					? (int) $sub_val
					: $this->adminmodel->getSubCategoryIdByName($sub_val, $category_id);
			}

			// Resolve owner user (by email or numeric id), else fall back.
			$user_id = null;
			$user_email = $get('user_email');
			$user_id_raw = $get('user_id');
			if ($user_email !== '') {
				$user_id = $this->adminmodel->getUserIdByEmail($user_email);
			} elseif ($user_id_raw !== '' && ctype_digit($user_id_raw)) {
				$user_id = (int) $user_id_raw;
			}
			if ($user_id === null) {
				$user_id = $default_user_id;
			}

			$info = array(
				'user_id'      => $user_id,
				'listing_name' => $listing_name,
				'category_id'  => $category_id,
				'sub_category_id' => $sub_category_id,
				'listing_slug' => $this->makeUniqueListingSlug($listing_name),
				// 1 = published. This wrote 0, so an entire CSV import landed
				// invisible: the category boxes counted the rows, every public
				// query filtered them out — 255 on the box, 13 on the page.
				// See Usermodel::applyPublishedListingFilter().
				'listing_status' => 1,
				'is_deleted'   => 0,
				'created_at'   => date('Y-m-d H:i:s'),
			);

			foreach ($text_columns as $col) {
				$val = $get($col);
				if ($val !== '') {
					$info[$col] = $val;
				}
			}

			// Auto-generate the Google Map embed from lat/lng when both given.
			if (!empty($info['listing_lat']) && !empty($info['listing_lng'])) {
				$info['google_map'] = 'https://maps.google.com/maps?q=' . $info['listing_lat'] . ',' . $info['listing_lng'] . '&output=embed';
			}

			if ($this->adminmodel->addListing($info)) {
				$imported++;
			} else {
				$skipped++;
				$errors[] = "Row {$row_number}: database insert failed.";
			}
		}

		$summary = "Import finished: {$imported} listing(s) added, {$skipped} skipped.";
		if (!empty($errors)) {
			// Keep the flash message readable — cap the detail list.
			$shown = array_slice($errors, 0, 15);
			$summary .= ' ' . implode(' ', $shown);
			if (count($errors) > count($shown)) {
				$summary .= ' …and ' . (count($errors) - count($shown)) . ' more.';
			}
		}

		if ($imported > 0) {
			$this->session->set_flashdata('message', $summary);
			redirect('admin/all_listings');
		} else {
			$this->session->set_flashdata('error', $summary);
			redirect('admin/import-listings');
		}
	}

	/**
	 * The ordered column list shared by the import template and the export.
	 * Keeping it in one place means a downloaded export re-imports cleanly.
	 */
	private function listingImportColumns()
	{
		return array(
			'listing_name', 'category', 'sub_category', 'user_email',
			'listing_mobile', 'listing_whatsapp', 'listing_email', 'listing_website',
			'listing_address', 'county_name', 'postal_code', 'listing_lat', 'listing_lng',
			'listing_description', 'service_locations', 'service_offered', 'listing_video',
			'listing_seo_title', 'listing_seo_keywords', 'listing_seo_description',
		);
	}

	/**
	 * Stream an array of associative rows to the browser as a CSV download.
	 * $columns fixes the column order; each row is looked up by those keys.
	 */
	private function streamListingsCsv($filename, $columns, $rows)
	{
		$this->output->set_content_type('text/csv');
		$this->output->set_header('Content-Disposition: attachment; filename="' . $filename . '"');
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');

		$out = fopen('php://temp', 'r+');
		fputcsv($out, $columns);
		foreach ($rows as $row) {
			$line = array();
			foreach ($columns as $col) {
				$line[] = isset($row[$col]) ? $row[$col] : '';
			}
			fputcsv($out, $line);
		}
		rewind($out);
		$csv = stream_get_contents($out);
		fclose($out);

		// Prepend a UTF-8 BOM so Excel opens accented characters correctly.
		$this->output->set_output("\xEF\xBB\xBF" . $csv);
	}

	/**
	 * Download a ready-to-fill CSV template (headers + one sample row) for
	 * the listing importer.
	 */
	public function download_listing_template()
	{
		$columns = $this->listingImportColumns();

		$sample = array(
			'listing_name'            => 'Pearland Family Dentistry',
			'category'                => 'Medical Services',
			'sub_category'            => 'Dentist',
			'user_email'              => '',
			'listing_mobile'          => '281-555-0100',
			'listing_whatsapp'        => '281-555-0100',
			'listing_email'           => 'info@example.com',
			'listing_website'         => 'https://example.com',
			'listing_address'         => '123 Main St, Pearland, TX',
			'county_name'             => 'Brazoria',
			'postal_code'             => '77584',
			'listing_lat'             => '29.5636',
			'listing_lng'             => '-95.2860',
			'listing_description'     => 'A friendly family dental clinic serving Pearland.',
			'service_locations'       => 'Pearland, Friendswood',
			'service_offered'         => 'Cleaning, Whitening',
			'listing_video'           => '',
			'listing_seo_title'       => 'Pearland Family Dentistry',
			'listing_seo_keywords'    => 'dentist, pearland',
			'listing_seo_description' => 'Trusted family dentist in Pearland, TX.',
		);

		$this->streamListingsCsv('listing_import_template.csv', $columns, array($sample));
	}

	/**
	 * Export every business listing to a CSV file that uses the very same
	 * column layout as the import template — so admins can download, edit
	 * in Excel, and re-import.
	 */
	public function export_listings()
	{
		$columns = $this->listingImportColumns();
		$rows = $this->adminmodel->getListingsForExport();

		$filename = 'listings_export_' . date('Y-m-d') . '.csv';
		$this->streamListingsCsv($filename, $columns, $rows);
	}

	/**
	 * Normalise a spreadsheet header cell to a known internal column key.
	 * Accepts friendly aliases (e.g. "Listing Name", "Phone", "Zip") so a
	 * hand-made file still lines up with the importer.
	 */
	private function normalizeImportHeader($label)
	{
		$slug = strtolower(trim((string) $label));
		$slug = preg_replace('/[^a-z0-9]+/', '_', $slug);
		$slug = trim($slug, '_');

		$aliases = array(
			'name'            => 'listing_name',
			'business_name'   => 'listing_name',
			'title'           => 'listing_name',
			'category_name'   => 'category',
			'sub_category_name' => 'sub_category',
			'subcategory'     => 'sub_category',
			'email'           => 'listing_email',
			'phone'           => 'listing_mobile',
			'mobile'          => 'listing_mobile',
			'whatsapp'        => 'listing_whatsapp',
			'website'         => 'listing_website',
			'address'         => 'listing_address',
			'county'          => 'county_name',
			'zip'             => 'postal_code',
			'zip_code'        => 'postal_code',
			'zipcode'         => 'postal_code',
			'postal'          => 'postal_code',
			'lat'             => 'listing_lat',
			'latitude'        => 'listing_lat',
			'lng'             => 'listing_lng',
			'lon'             => 'listing_lng',
			'longitude'       => 'listing_lng',
			'description'     => 'listing_description',
			'owner_email'     => 'user_email',
			'video'           => 'listing_video',
		);

		if (isset($aliases[$slug])) {
			return $aliases[$slug];
		}

		$known = array(
			'listing_name', 'category', 'sub_category', 'user_email', 'user_id',
			'listing_mobile', 'listing_whatsapp', 'listing_email', 'listing_website',
			'listing_address', 'county_name', 'postal_code', 'listing_lat', 'listing_lng',
			'listing_description', 'service_locations', 'service_offered', 'listing_video',
			'listing_seo_title', 'listing_seo_keywords', 'listing_seo_description',
		);

		return in_array($slug, $known, true) ? $slug : '';
	}

	/**
	 * Build a URL-safe, collision-free listing slug from a name.
	 */
	/**
	 * URL slug for a listing category.
	 *
	 * Categories added here never had a slug written, so the public side had
	 * to guess one from the name — and the three places that link to a
	 * category each guessed differently, so a name containing "&" or "/"
	 * reached a populated page from one menu and an empty one from another.
	 * Writing the slug once, with the same pearland_slug() the links use,
	 * removes the guessing.
	 *
	 * @param string   $name
	 * @param int|null $ignore_id category being edited, so it doesn't clash
	 *                            with its own existing slug
	 */
	private function makeUniqueCategorySlug($name, $ignore_id = NULL)
	{
		$this->load->helper('view');

		$base = pearland_slug($name);
		if ($base === '') {
			$base = 'category';
		}

		$slug   = $base;
		$suffix = 1;
		while ($this->categorySlugTaken($slug, $ignore_id)) {
			$suffix++;
			$slug = $base . '-' . $suffix;
		}

		return $slug;
	}

	private function categorySlugTaken($slug, $ignore_id = NULL)
	{
		$this->db->from('listing_categories')->where('category_slug', $slug);
		if ($ignore_id !== NULL) {
			$this->db->where('category_id !=', (int) $ignore_id);
		}

		return $this->db->count_all_results() > 0;
	}

	/**
	 * The sub-category ids posted by the admin listing forms.
	 *
	 * The field is `sub_category_id[]` now — a listing may sit under several.
	 * Returns ints, de-duplicated, empties dropped.
	 *
	 * @return int[]
	 */
	private function postedSubCategoryIds()
	{
		$ids = $this->input->post('sub_category_id');
		if (!is_array($ids)) {
			$ids = ($ids === NULL || $ids === '') ? array() : array($ids);
		}
		return array_values(array_unique(array_filter(array_map('intval', $ids))));
	}

	private function makeUniqueListingSlug($name)
	{
		$base = preg_replace('/[^A-Za-z0-9-]+/', '-', $name);
		$base = trim($base, '-');
		if ($base === '') {
			$base = 'listing';
		}

		$slug = $base;
		$suffix = 1;
		while ($this->adminmodel->listingSlugExists($slug)) {
			$suffix++;
			$slug = $base . '-' . $suffix;
		}
		return $slug;
	}

	/**
	 * A slug for $title that no row in $table.$column already holds.
	 *
	 * Two articles with the same headline produced the same slug. Where the
	 * column is unique that made the second INSERT fail — and the controller
	 * reported a flat "Adding News Failed!!" with no reason, which is what
	 * "cannot add more news" looks like from the admin's side. Where it is not
	 * unique it is worse but quieter: every lookup is
	 * getSingleValue($slug, …), so the second article is unreachable and edits
	 * meant for it land on the first.
	 *
	 * @param string      $title
	 * @param string      $table
	 * @param string      $column
	 * @param string|null $fallback Used when the title slugs to nothing.
	 */
	private function makeUniqueContentSlug($title, $table, $column, $fallback = 'post')
	{
		$base = pearland_slug($title);
		if ($base === '') {
			$base = $fallback;
		}

		$slug = $base;
		$suffix = 1;
		while ($this->adminmodel->slugExists($table, $column, $slug)) {
			$suffix++;
			$slug = $base . '-' . $suffix;
		}
		return $slug;
	}

	/*Edit Listing*/

	/**
	 * The listing's header banner — `listings.cover_image`, the wide picture
	 * behind the business name at the top of /listing-details.
	 *
	 * Neither admin listing form had a field for it (tracker: "no option to
	 * add … header banner in Business Listings"), so every business the team
	 * entered showed the stock background. Upload first; only a successful
	 * upload replaces the stored one, and a refusal is reported in $notes.
	 */
	private function applyListingCover(array &$info, array &$notes, $current = '')
	{
		if (!$this->db->field_exists('cover_image', 'listings')) return;

		if ($current !== '' && $this->input->post('remove_cover_image')) {
			$info['cover_image'] = NULL;
		}
		if (!empty($_FILES['cover_image']['name'])) {
			$new = uploadWithoutResize(array(
				'upload_path' => 'images/listings',
				'quality'     => '60%',
				'image_name'  => 'cover_image',
			));
			if ($new) {
				$info['cover_image'] = $new;
			} else {
				$notes[] = 'The header banner was not changed: ' . (upload_last_error() ?: 'the file could not be read.');
			}
		}
	}

	public function edit_listing($slug)
	{
		$data['categories']  = $this->usermodel->getAllValues('listing_categories');
		$data['users'] 		 = $this->adminmodel->getActiveUsersForDropdown();
		$data['slug'] = $slug;

		$data['listing'] 	 = $this->usermodel->getSingleValue($slug, 'listing_slug', 'listings');

		// Slug didn't match — bail out before the view dereferences null.
		if (empty($data['listing'])) {
			$this->session->set_flashdata('error', 'Listing not found.');
			redirect('admin/all-listings');
			return;
		}

		// Load the sub-category list unconditionally. Guarded on category_id, a
		// listing with no category rendered the select with zero options — not
		// even the placeholder — and the AJAX that fills it only fires onChange.
		$data['sub_category_listing'] = !empty($data['listing']->category_id)
			? $this->adminmodel->ListingSubCategoryByCateId($data['listing']->category_id)
			: array();

		// Every sub-category attached to this listing (migration 0023).
		$data['listing_sub_categories'] = $this->adminmodel->listingSubCategoryIds($data['listing']->listing_id);

		// Category banners sold to this business — booked from here, below the
		// form (listing_banner_add). Ad sales stay super-admin only.
		$__admin = $this->session->userdata('logged_admin');
		$data['can_book_banners'] = is_array($__admin) && ($__admin['role'] ?? 'editor') === 'super_admin';
		$data['banner_zones']     = $this->adminmodel->getCategoryBannerZones();
		$data['listing_banners']  = $this->adminmodel->getListingCategoryAds($data['listing']->listing_id);
		$data['banner_tracking']  = $this->db->field_exists('listing_id', 'ads');

		if ($this->form_validation->run('admin_listing_scratch') == FALSE) {
			$this->load->view('admin/header', $data);
			$this->load->view('admin/edit_listings');
			$this->load->view('admin/footer');
		} else {
			$listing_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('listing_name'));
			$info = array(
				'user_id' => $this->input->post('user_id'),
				'listing_name' => $this->input->post('listing_name'),
				'featured_listing' => ($this->input->post('featured_listing') ? 1 : 0),
				'spotlight_listing' => ($this->input->post('spotlight_listing') ? 1 : 0),
				'category_id' => $this->input->post('category_id'),
				// First of the selected sub-categories. The full set goes into
					// listing_sub_category_map after the write; this column is kept
					// in step so the readers that still use it keep working.
					'sub_category_id' => ($__subs = $this->postedSubCategoryIds()) ? $__subs[0] : NULL,
				'listing_mobile' => $this->input->post('listing_mobile'),
				'listing_whatsapp' => $this->input->post('listing_whatsapp'),
				'display_phone' => $this->input->post('display_phone'),
				'listing_email' => $this->input->post('listing_email'),
				'listing_website' => $this->input->post('listing_website'),
				'listing_address' => $this->input->post('listing_address'),
				'county_name' => $this->input->post('county_name'),
				'postal_code' => $this->input->post('postal_code'),
				'listing_lat' => $this->input->post('listing_lat'),
				'listing_lng' => $this->input->post('listing_lng'),
				'display_address' => $this->input->post('display_address'),
				'listing_description' => $this->input->post('listing_description'),
				'service_locations' => $this->input->post('service_locations'),
				'service_offered' => $this->input->post('service_offered'),
				'listing_video' => $this->input->post('listing_video'),
				'listing_seo_title' => $this->input->post('listing_seo_title'),
				'listing_seo_keywords' => $this->input->post('listing_seo_keywords'),
				'listing_seo_description' => $this->input->post('listing_seo_description'),
				'listing_slug' => $listing_slug
			);

			// Auto-generate Google Map embed URL from lat/lng
			$lat = $this->input->post('listing_lat');
			$lng = $this->input->post('listing_lng');
			if (!empty($lat) && !empty($lng)) {
				$info['google_map'] = "https://maps.google.com/maps?q={$lat},{$lng}&output=embed";
			}

			// A refused logo used to be written as NULL over the stored one, so
			// a too-large or wrong-type file deleted the business's logo while
			// the screen said "Listing updated successfully". Only a successful
			// upload replaces it now, and a refusal is reported.
			$upload_notes = array();
			if (!empty($_FILES['listing_profile_image']['name'])) {
				$img_info = [
					'upload_path' => 'images/listings',
					'quality'     => '60%',
					'image_name'  => 'listing_profile_image'
				];
				$new_logo = uploadWithoutResize($img_info);
				if ($new_logo) {
					$info['listing_image'] = $new_logo;
				} else {
					$upload_notes[] = 'The logo was not changed: ' . (upload_last_error() ?: 'the file could not be read.');
				}
			}

			$this->applyListingCover($info, $upload_notes, (string) ($data['listing']->cover_image ?? ''));

			// Gallery: keep what is already there, drop what was ticked for
			// removal, append what was just uploaded.
			//
			// This block used to build a fresh array from the POST alone and
			// json_encode it over the column, never reading the stored value —
			// so adding one photograph deleted every photograph already on the
			// listing, and there was no way to remove a single image because the
			// only way to change the gallery at all was to replace all of it.
			$existing_gallery = listing_gallery_paths($data['listing']->listing_image_gallery ?? null);
			$remove_gallery   = (array) $this->input->post('remove_gallery');
			$kept_gallery     = array_values(array_diff($existing_gallery, $remove_gallery));
			$gallery_changed  = (count($kept_gallery) !== count($existing_gallery));

			if ($_FILES && isset($_FILES['listing_image_gallery']['name'][0]) && !empty($_FILES['listing_image_gallery']['name'][0])) {
				$gallery_images = [];
				$files_count = count($_FILES['listing_image_gallery']['name']);

				for ($i = 0; $i < $files_count; $i++) {
					if (!empty($_FILES['listing_image_gallery']['name'][$i])) {
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
							$upload_notes[] = 'Photo "' . $_FILES['listing_image_gallery']['name'][$i] . '" was not added: '
								. (upload_last_error() ?: 'the file could not be read.');
						}
					}
				}
				if (!empty($gallery_images)) {
					$kept_gallery    = array_merge($kept_gallery, $gallery_images);
					$gallery_changed = TRUE;
				}
			}

			if ($gallery_changed) {
				$info['listing_image_gallery'] = empty($kept_gallery) ? NULL : json_encode(array_values($kept_gallery));
			}

			if ($this->adminmodel->update($slug, $info, 'listing_slug', 'listings')) {
				$this->adminmodel->saveListingSubCategories($data['listing']->listing_id, $this->postedSubCategoryIds());
				// Partial failures used to be hidden behind "updated successfully".
				$this->session->set_flashdata($upload_notes ? 'error' : 'message',
					'Listing updated successfully.' . ($upload_notes ? ' ' . implode(' ', $upload_notes) : ''));
				redirect('admin/all-listings');
			} else {
				$this->session->set_flashdata('error', 'Could not update the listing. ' . $this->adminmodel->lastDbError());
				redirect('admin/edit-listing/' . rawurlencode((string) $slug));
			}
		}
	}

	/*====================
		Category banners for one business (POST, super-admin)
	=====================*/
	/**
	 * Book a category-specific banner for a business from its own edit screen.
	 *
	 * Tracker: "No option to add category-specific banners and header banner in
	 * Business Listings". The banners are sold on /pricing-plan (Category
	 * Specific Banners, including the Category Header Banner), but placing one
	 * meant leaving the business for /admin/create-ads, picking the customer
	 * from every account on the site and re-typing the category, with cost
	 * fields to fill by hand. This takes the zone, the category (the
	 * business's own by default), the artwork, a link and the dates; prices it
	 * from the package; and records which business it was sold to
	 * (ads.listing_id, migration 0036), so the screen can list it afterwards.
	 */
	public function listing_banner_add($listing_id)
	{
		$listing = $this->db->where('listing_id', (int) $listing_id)->get('listings')->row();
		if (!$listing || $this->input->method() !== 'post') {
			redirect('admin/all_listings');
			return;
		}
		$back = 'admin/edit-listing/' . rawurlencode((string) $listing->listing_slug) . '#banners';

		$zones = array();
		foreach ($this->adminmodel->getCategoryBannerZones() as $z) $zones[(int) $z->id] = $z;
		$zone        = $zones[(int) $this->input->post('ads_price_id')] ?? NULL;
		$category_id = (int) $this->input->post('category_id');
		$category    = $category_id ? $this->db->where('category_id', $category_id)->get('listing_categories')->row() : NULL;
		$start_ts    = strtotime((string) $this->input->post('ad_start_date'));
		$end_ts      = strtotime((string) $this->input->post('ad_end_date'));

		$problems = array();
		if (!$zone)     $problems[] = 'choose a banner position';
		if (!$category) $problems[] = 'choose the category it runs in';
		if (!$start_ts || !$end_ts || $end_ts < $start_ts) $problems[] = 'give a start date and an end date on or after it';
		if (empty($_FILES['ad_photo']['name'])) $problems[] = 'attach the banner artwork';
		if ($problems) {
			$this->session->set_flashdata('banner_error', 'Banner not booked: ' . implode('; ', $problems) . '.');
			redirect($back);
			return;
		}

		$photo = uploadAndResize(array('upload_path' => 'images/ads/main', 'quality' => '60%', 'image_name' => 'ad_photo'));
		if (!$photo) {
			$this->session->set_flashdata('banner_error', 'Banner not booked: the artwork was refused — ' . (upload_last_error() ?: 'the file could not be read.'));
			redirect($back);
			return;
		}

		// The business's own page unless another address was given.
		$link = external_url($this->input->post('ad_link'));
		if ($link === '') $link = base_url('listing-details/' . rawurlencode((string) $listing->listing_slug));

		// Priced from the package: its price buys duration_days, so a booking
		// for exactly that term costs exactly the package price.
		$term    = max(1, (int) $zone->duration_days ?: 30);
		$days    = (int) floor(($end_ts - $start_ts) / 86400) + 1;
		$per_day = round((float) $zone->price / $term, 2);
		$total   = round((float) $zone->price * $days / $term, 2);

		$ok = db_safe_insert('ads', array(
			'u_id'            => (int) $listing->user_id,
			'listing_id'      => (int) $listing->listing_id,
			'ads_price_id'    => (int) $zone->id,
			'category_id'     => (int) $category->category_id,
			'ad_photo'        => $photo,
			'ad_link'         => $link,
			'ad_start_date'   => date('Y-m-d 00:00:00', $start_ts),
			'ad_end_date'     => date('Y-m-d 23:59:59', $end_ts),
			'ad_status'       => 1,
			'ad_total_days'   => $days,
			'ad_cost_per_day' => $per_day,
			'ad_total_cost'   => $total,
			'created_at'      => date('Y-m-d H:i:s'),
		));

		if ($ok) {
			$this->session->set_flashdata('banner_message', '"' . $zone->name . '" booked for ' . $listing->listing_name . ' in '
				. $category->category_name . ', ' . date('M j, Y', $start_ts) . ' – ' . date('M j, Y', $end_ts) . '.');
		} else {
			@unlink(FCPATH . $photo);
			$this->session->set_flashdata('banner_error', 'Banner not booked: ' . db_safe_last_error());
		}
		redirect($back);
	}

	/** Remove a category banner booked for a business (POST, super-admin). */
	public function listing_banner_remove($ad_id)
	{
		$ad = $this->db->where('id', (int) $ad_id)->get('ads')->row();
		$listing = ($ad && !empty($ad->listing_id))
			? $this->db->where('listing_id', (int) $ad->listing_id)->get('listings')->row()
			: NULL;
		if (!$ad || !$listing || $this->input->method() !== 'post') {
			redirect('admin/all_listings');
			return;
		}
		if ($this->adminmodel->deleteAds((int) $ad->id)) {
			$this->session->set_flashdata('banner_message', 'Banner removed from ' . $listing->listing_name . '.');
		} else {
			$this->session->set_flashdata('banner_error', 'Could not remove that banner.');
		}
		redirect('admin/edit-listing/' . rawurlencode((string) $listing->listing_slug) . '#banners');
	}

	/*listing Preview */
	public function listing_preview($listing_slug)
	{
		$data['logged_id'] = '';
		$data['listing'] = $this->usermodel->getSingleValue($listing_slug, 'listing_slug', 'listings');

		// If listing not found in active listings, check in deleted listings (for trash preview)
		if (empty($data['listing'])) {
			$deleted_listing = $this->adminmodel->getListingSingleValue($listing_slug, 'listing_slug', 'listings');
			if (!empty($deleted_listing)) {
				$data['listing'] = (object) $deleted_listing[0]; // Convert array to object for consistency
			}
		}

		// Only get related listings if main listing exists and has category_id
		if (!empty($data['listing']) && isset($data['listing']->category_id)) {
			$data['RelatedListing'] = $this->usermodel->getRelatedListing('listings', $listing_slug, $data['listing']->category_id);
		} else {
			$data['RelatedListing'] = []; // Empty array if no related listings
		}

		// The preview shares the public view, so give it the same data the
		// public controller does — otherwise an admin previews a page with no
		// reviews, no category breadcrumb and (because $page_css never reached
		// the header) no stylesheet.
		$data['user']             = null;
		$data['listing_reviews']  = array();
		$data['review_stats']     = array();
		$data['listing_category'] = null;
		$data['owner_socials']    = array('facebook' => '', 'twitter' => '', 'youtube' => '', 'website' => '');

		if (!empty($data['listing'])) {
			$data['listing_reviews'] = $this->usermodel->getListingReviews($data['listing']->listing_id, 10);
			$data['review_stats']    = $this->usermodel->getReviewStats($data['listing']->listing_id);

			if (!empty($data['listing']->category_id)) {
				$data['listing_category'] = $this->db
					->select('category_id, category_name, category_slug')
					->where('category_id', (int) $data['listing']->category_id)
					->limit(1)
					->get('listing_categories')
					->row();
			}
		}

		$data['title']    = 'Preview: ' . (!empty($data['listing']->listing_name) ? $data['listing']->listing_name : 'listing');
		// promo-cards.css too — the view includes the two house promo cards
		// and their styles no longer live in directory.css.
		$data['page_css'] = array('assets/css/directory.css', 'assets/css/promo-cards.css');

		$this->load->view('header', $data);
		$this->load->view('listing_details', $data);
		$this->load->view('footer');
	}

	/*====================
	Edit Listing
	=====================*/
	public function edit_listings()
	{

		$this->load->view('admin/header');
		$this->load->view('admin/edit_listings');
		$this->load->view('admin/footer');
	}


	/*====================
	Add Duplicate Listing
	=====================*/
	public function create_duplicate_listing()
	{
		// Hidden from every menu, and unreachable by URL too — otherwise
		// "hidden" only means "not linked". The tool itself is untouched below;
		// switch feature_duplicate_listing_enabled on at /admin/setting to use it.
		if (!duplicate_listing_enabled()) {
			redirect('admin/add_listing_scratch');
			return;
		}

		$data['users'] 		  	= $this->adminmodel->getActiveUsersForDropdown();
		$data['listings']		= $this->adminmodel->getAllListingNames();
		// echo "<pre>";var_dump($data['listings']);die();

		if ($this->form_validation->run('admin_duplicate_listing') == FALSE) {
			$this->load->view('admin/header', $data);
			$this->load->view('admin/create_duplicate_listing');
			$this->load->view('admin/footer');
		} else {
			$old_listing_id = $this->input->post('listing_id');
			$new_listing_name = $this->input->post('listing_name');

			$getOldData = $this->adminmodel->getListingSingleValue($old_listing_id, 'listing_id', 'listings');
			unset($getOldData[0]['listing_id']);

			$getOldData[0]['listing_name'] = $new_listing_name;

			if ($this->adminmodel->addDuplicateListing('listings', $getOldData[0])) {
				$this->session->set_flashdata('message', 'Successfully Duplicated Listing!!');
				redirect('admin/all-listings');
			} else {
				$this->session->set_flashdata('message', 'Duplicating Listing Failed!!');
				redirect('admin/create-duplicate-listing');
			}
		}
	}
	/*====================
	Claim Listing
	=====================*/
	public function claim_listing()
	{

		$this->load->view('admin/header');
		$this->load->view('admin/claim_listing');
		$this->load->view('admin/footer');
	}
	/*====================
	Trash Listing
	=====================*/
	public function trash_listing()
	{

		$config = [
			'base_url' => base_url('admin/trash_listings'),
			'per_page' => admin_per_page(20),
			'reuse_query_string' => TRUE,
			'total_rows' => count($this->adminmodel->getAllDeletedListings(NULL, NULL))
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


		$data['AllListings']   = $this->adminmodel->getAllDeletedListings($config['per_page'], $this->uri->segment(3));
		$data['link'] 		   = $this->pagination->create_links();


		$this->load->view('admin/header', $data);
		$this->load->view('admin/trash_listing');
		$this->load->view('admin/footer');
	}


	/*=================== 
    	Restore Listing 
	===================*/
	public function restore_listing($listing_id = NULL)
	{
		// Restore used to read the whole row, set two properties on it and write
		// every column back keyed on the slug. Three ways that 500'd: a slug that
		// matched nothing returned NULL and setting a property on NULL is fatal
		// in PHP 8; a trashed row with an empty slug called this with no argument
		// at all; and the write included `deleted_date`, a column nothing else in
		// the application creates or writes. Now it is an id and two columns.
		if ($listing_id === NULL || !ctype_digit((string) $listing_id)) {
			$this->session->set_flashdata('error', 'That listing could not be identified, so nothing was restored.');
			redirect('admin/trash-listing', 'refresh');
			return;
		}

		if ($this->adminmodel->restoreListing($listing_id)) {
			$this->session->set_flashdata('message', 'Listing restored.');
		} else {
			$this->session->set_flashdata('error', 'Could not restore that listing. ' . $this->adminmodel->lastDbError());
		}
		redirect('admin/trash-listing', 'refresh');
	}

	/*===============================
		Listing Delete Permanently
	=================================*/

	public function listing_delete($listing_id = NULL)
	{
		if ($listing_id === NULL || !ctype_digit((string) $listing_id)) {
			$this->session->set_flashdata('error', 'That listing could not be identified, so nothing was removed.');
			redirect('admin/trash-listing', 'refresh');
			return;
		}

		if ($this->adminmodel->permanentDeleteListing($listing_id)) {
			$this->session->set_flashdata('message', 'Listing permanently removed.');
			redirect('admin/trash-listing');
		} else {
			$this->session->set_flashdata('error', 'Could not remove that listing. ' . $this->adminmodel->lastDbError());
			redirect('admin/trash-listing', 'refresh');
		}
	}

	/*====================
		Delete Listing (Soft Delete - Move to Trash)
	=====================*/
	public function delete_listing($listing_id = NULL)
	{
		if ($listing_id === NULL || !ctype_digit((string) $listing_id)) {
			$this->session->set_flashdata('error', 'That listing could not be identified, so nothing was deleted.');
			redirect('admin/all-listings', 'refresh');
			return;
		}

		if ($this->adminmodel->softDeleteListing($listing_id)) {
			$this->session->set_flashdata('message', 'Listing successfully moved to trash!');
			redirect('admin/all-listings');
		} else {
			$this->session->set_flashdata('error', 'Failed to delete listing. ' . $this->adminmodel->lastDbError());
			redirect('admin/all-listings', 'refresh');
		}
	}

	/*====================
		Listing Enquiry
	=====================*/
	public function all_enquiry()
	{

		$this->load->view('admin/header');
		$this->load->view('admin/all_enquiry');
		$this->load->view('admin/footer');
	}
	/*====================
	Saved Listing Enquiry
	=====================*/
	// public function saved_enquiry(){

	// 	$this->load->view('admin/header');
	// 	$this->load->view('admin/saved_enquiry');
	// 	$this->load->view('admin/footer');
	// }

	/*====================
	All Product Category
	=====================*/
	public function all_product_category()
	{
		$this->load->library('pagination');

		// Initialize data array
		$data = array();
		$data['link'] = '';

		// Get total record count
		$total_rows = $this->adminmodel->countAllProductCategory();

		// Pagination Configuration
		$config = [
			'base_url'         => base_url('admin/all_product_category'),
			'total_rows'       => $total_rows,
			'per_page' => admin_per_page(20),
			'reuse_query_string' => TRUE,
			'uri_segment'      => 3,
			'use_page_numbers' => TRUE,
			'num_links'        => 3,
			'page_query_string' => FALSE,

			// Bootstrap 4 Pagination Styling
			'full_tag_open'    => '<ul class="pagination">',
			'full_tag_close'   => '</ul>',
			'first_tag_open'   => '<li class="page-item">',
			'first_tag_close'  => '</li>',
			'last_tag_open'    => '<li class="page-item">',
			'last_tag_close'   => '</li>',
			'cur_tag_open'     => '<li class="page-item active"><a href="#" class="page-link">',
			'cur_tag_close'    => '</a></li>',
			'num_tag_open'     => '<li class="page-item">',
			'num_tag_close'    => '</li>',
			'prev_tag_open'    => '<li class="page-item">',
			'prev_tag_close'   => '</li>',
			'next_tag_open'    => '<li class="page-item">',
			'next_tag_close'   => '</li>',
			'first_link'       => 'First',
			'last_link'        => 'Last',
			'next_link'        => 'Next',
			'prev_link'        => 'Previous',
			'attributes'       => ['class' => 'page-link'],
		];

		$this->pagination->initialize($config);

		// Calculate the correct offset
		$page = ($this->uri->segment(3)) ? (int) $this->uri->segment(3) : 1;
		$this->pagination->initialize($config);
		$page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
		$data['start_number'] = ($page - 1) * $config['per_page'] + 1;

		// Calculate offset for DB query (page numbers are 1-based)
		$offset = ($page - 1) * $config['per_page'];

		// Fetch paginated records
		$data['all_product_category'] = $this->adminmodel->allProductCategory($config['per_page'], $offset);
		$data['link'] = $this->pagination->create_links();

		$data['user'] = $this->adminmodel->getAdminInformation();

		$this->load->view('admin/header');
		$this->load->view('admin/all_product_category', $data);
		$this->load->view('admin/footer');
	}
	/*====================
	Add New Product Category
	=====================*/
	public function add_new_product_category()
	{
		$this->form_validation->set_rules('category_name', 'Category Name', 'trim|required|is_unique[product_categories.category_name]');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/add_new_product_category');
			$this->load->view('admin/footer');
		} else {
			$category_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('category_name'));

			$data = array();
			$data['category_name'] = $this->input->post('category_name');
			$data['category_slug'] = $category_slug;

			if ($_FILES && $_FILES['category_image']['name']) {
				$img_info = [
					'upload_path' => 'images/services',
					'quality'     => '60%',
					'height'      => 250,
					'width'       => 400,
					'image_name'  => 'category_image'
				];
				$data['category_image'] = uploadAndResize($img_info);
			}

			if ($this->adminmodel->addProductCategory($data)) {
				$this->session->set_flashdata('message', 'Successfully Added Product Category!!');
				redirect('admin/all_product_category');
			} else {
				$this->session->set_flashdata('message', 'Adding Product Category failed!!');
			}
		}
	}

	/*====================
	Edit Product Category
	=====================*/
	public function edit_product_category($id = NULL)
	{
		$data['category'] = $this->adminmodel->get_product_category_ById($id);
		$old_image        = $data['category']->category_image;

		// Set validation rule for category_name to be unique except current record
		$this->form_validation->set_rules('category_name', 'Category Name', 'trim|required|is_unique[product_categories.category_name.category_id.' . $id . ']');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/edit_product_category', $data);
			$this->load->view('admin/footer');
		} else {
			// Generate category_id from category_name
			$category_name = $this->input->post('category_name');
			$category_id = strtolower(url_title($category_name)); // Generates a URL-friendly slug

			// Ensure category_id is unique
			$original_category_id = $category_id;
			$counter = 1;
			while (TRUE) {
				$this->db->where('category_id', $category_id);
				$this->db->where('category_id !=', $id);
				$query = $this->db->get('product_categories');
				if ($query->num_rows() == 0) break;
				$category_id = $original_category_id . '-' . $counter;
				$counter++;
			}

			$info = array(
				'category_name' => $category_name,
				'category_id'   => $category_id
			);

			// Handle image upload
			if (!empty($_FILES['category_image']['name'])) {
				$img_info = array(
					'upload_path'   => 'images/services',
					'quality'       => '60%',
					'height'        => 250,
					'width'         => 400,
					'image_name'    => 'category_image'
				);

				// Delete old image
				$image_link = FCPATH . $old_image;
				if (file_exists($image_link)) {
					unlink($image_link);
				}

				$info['category_image'] = uploadAndResize($img_info);
			} else {
				$info['category_image'] = $old_image;
			}

			// Update category
			if ($this->adminmodel->updateProductCategory($id, $info)) {
				$this->session->set_flashdata('message', 'Successfully Updated Product Category!');
				redirect('admin/all_product_category');
			} else {
				$this->session->set_flashdata('message', 'No changes made or failed to update.');
				redirect('admin/edit_product_category/' . $id);
			}
		}
	}
	/*====================
	Delete Product Category
	=====================*/
	public function delete_product_category($id)
	{
		if ($this->adminmodel->deleteProductCategory($id)) {
			$this->session->set_flashdata('message', 'Prodcut Category has been Permanently Deleted!!!');
			redirect('admin/all_product_category', 'refresh');
		} else {
			redirect('admin/all_product_category', 'refresh');
		}
	}
	/*====================
	Product All Sub Category
	=====================*/
	public function all_product_sub_category($page = 1)
	{
		$this->load->library('pagination');

		// Initialize data array
		$data = array();
		$data['link'] = '';

		// Get category ID from URL if present
		$id = $this->input->get('category_id');

		if (!empty($id)) {
			$data['all_product_sub_category'] = $this->adminmodel->ProductSubCategoryByCateId($id);
		} else {
			// Get total record count
			$total_rows = $this->adminmodel->countAllProductSubCategory();

			// Pagination Configuration
			$config = [
				'base_url'         => base_url('admin/all_product_sub_category'),
				'total_rows'       => $total_rows,
				'per_page' => admin_per_page(20),
				'reuse_query_string' => TRUE,
				'uri_segment'      => 3,
				'use_page_numbers' => TRUE,
				'num_links'        => 3,
				'page_query_string' => FALSE,

				// Bootstrap 4 Pagination Styling
				'full_tag_open'    => '<ul class="pagination">',
				'full_tag_close'   => '</ul>',
				'first_tag_open'   => '<li class="page-item">',
				'first_tag_close'  => '</li>',
				'last_tag_open'    => '<li class="page-item">',
				'last_tag_close'   => '</li>',
				'cur_tag_open'     => '<li class="page-item active"><a href="#" class="page-link">',
				'cur_tag_close'    => '</a></li>',
				'num_tag_open'     => '<li class="page-item">',
				'num_tag_close'    => '</li>',
				'prev_tag_open'    => '<li class="page-item">',
				'prev_tag_close'   => '</li>',
				'next_tag_open'    => '<li class="page-item">',
				'next_tag_close'   => '</li>',
				'first_link'       => 'First',
				'last_link'        => 'Last',
				'next_link'        => 'Next',
				'prev_link'        => 'Previous',
				'attributes'       => ['class' => 'page-link'],
			];

			$this->pagination->initialize($config);

			// Calculate offset
			$page = ($page < 1) ? 1 : $page;
			$offset = ($page - 1) * $config['per_page'];
			$data['start_number'] = ($page - 1) * $config['per_page'] + 1;

			// Fetch paginated records
			$data['all_product_sub_category'] = $this->adminmodel->allProductSubCategory($config['per_page'], $offset);
			$data['link'] = $this->pagination->create_links();
		}

		$data['user'] = $this->adminmodel->getAdminInformation();

		// Load views
		$this->load->view('admin/header');
		$this->load->view('admin/all_product_sub_category', $data);
		$this->load->view('admin/footer');
	}

	/*====================
	Add new Product Sub Category
	=====================*/
	public function add_new_product_sub_category()
	{
		$this->form_validation->set_rules('sub_category_name', 'Sub Category Name', 'trim|required|is_unique[product_sub_categories.sub_category_name]');

		if ($this->form_validation->run() == FALSE) {
			$data['all_product_category'] = $this->adminmodel->allProductCategory();
			$this->load->view('admin/header');
			$this->load->view('admin/add_new_product_sub_category', $data);
			$this->load->view('admin/footer');
		} else {
			$sub_category_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('sub_category_name'));

			$data = array();
			$data['sub_category_name'] = $this->input->post('sub_category_name');
			$data['category_id'] = $this->input->post('category_id');
			$data['sub_category_slug'] = $sub_category_slug;


			if ($this->adminmodel->addProductSubCategory($data)) {
				$this->session->set_flashdata('message', 'Sub Category has been Added Successfully!!');
				redirect('admin/all_product_sub_category');
			} else {
				$this->session->set_flashdata('message', 'Sub Category has been Failed!!');
			}
		}
	}
	/*====================
	Add Edit Product Sub Category
	=====================*/

	public function edit_product_sub_category($slug)
	{
		$data['sub_category'] = $this->adminmodel->get_product_sub_category_ById($slug);
		// echo "<pre>";var_dump($data['sub_category']);die();
		$data['all_product_category'] = $this->adminmodel->allProductCategory();

		$this->form_validation->set_rules('sub_category_name', 'Sub Category Name', 'trim|required');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/edit_product_sub_category', $data);
			$this->load->view('admin/footer');
		} else {
			$sub_category_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('sub_category_name'));
			$info['category_id'] = $this->input->post('category_id');
			$info['sub_category_name'] = $this->input->post('sub_category_name');
			$info['sub_category_slug'] = $sub_category_slug;

			if ($this->adminmodel->updateProductSubCategory($slug, $info)) {
				$this->session->set_flashdata('message', 'Sub Category has been Updated Successfully.');
				redirect('admin/all_product_sub_category', 'refresh');
			} else {
				redirect('admin/edit_product_sub_category', 'refresh');
			}
		}
	}
	/*=================================
	    Delete Product Sub Category
	=================================*/
	public function delete_product_sub_category($slug)
	{
		if ($this->adminmodel->deleteProductSubCategory($slug)) {
			$this->session->set_flashdata('message', 'Sub Category has been Permanently Deleted!!!');
			redirect('admin/all_product_sub_category', 'refresh');
		} else {
			redirect('admin/all_product_sub_category', 'refresh');
		}
	}

	/*=============================
		Get Product Sub Category
	=============================*/
	public function get_product_sub_category()
	{

		if (!empty($this->input->post('category_id'))) {
			$category_id = $this->input->post('category_id');
			$data['sub_categories'] = $this->adminmodel->ProductSubCategoryByCateId($category_id);
			$this->load->view('admin/get_sub_categories', $data);
		}
	}

	/*===============
        PRODUCTS
    ================*/

	/*User All Products*/
	public function products()
	{
		// Pagination configuration
		$config = [
			'base_url' => base_url('admin/products'),
			'per_page' => admin_per_page(20),
			'reuse_query_string' => TRUE,
			'total_rows' => $this->adminmodel->countAllProducts(),
			'uri_segment' => 3,
		];

		// Pagination styling
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

		// Initialize pagination
		$this->pagination->initialize($config);

		$page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

		$data['allProducts'] = $this->adminmodel->getAllProducts($config['per_page'], $page);

		// Gather product IDs and fetch their view counts via reusable model method
		$product_ids = array();
		if (!empty($data['allProducts'])) {
			foreach ($data['allProducts'] as $p) {
				if (isset($p->product_id)) $product_ids[] = $p->product_id;
			}
		}

		$data['views_counts'] = $this->adminmodel->getViewsForIds('products', 'product_id', 'views', $product_ids);

		$data['start_number'] = $page + 1;
		$data['link'] = $this->pagination->create_links();

		// Load the view with data
		$this->load->view('admin/header');
		$this->load->view('admin/left_sidebar');
		$this->load->view(
			'admin/all_products',
			$data
		);
		$this->load->view('admin/footer');
	}

	// /*Product Details*/
	public function product_details($product_id = null)
	{
		if (empty($product_id)) {
			$this->session->set_flashdata("message", "Product ID is missing");
			redirect('admin/products');
			return;
		}

		$data['product'] = $this->adminmodel->getSingleProduct($product_id);

		if (!$data['product']) {
			$this->session->set_flashdata("message", "Product not found");
			redirect('admin/products');
			return;
		}

		$this->load->view('header');
		$this->load->view('admin/product_details', $data);
		$this->load->view('footer');
	}

	/*User Add Product*/
	public function add_product()
	{
		$data['productCategories'] = $this->adminmodel->getAllValues('product_categories');
		$data['users']             = $this->adminmodel->getActiveUsersForDropdown();

		if ($this->form_validation->run('admin_products') == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/add_new_product', $data);
			$this->load->view('admin/footer');
		} else {
			$product_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('product_name'));
			$info = array(
				'user_id'			    => $this->input->post('user_id'),
				'product_name'			=> $this->input->post('product_name'),
				'category_id'			=> $this->input->post('category_id'),
				'sub_category_id'		=> $this->input->post('sub_category_id'),
				'product_price'			=> $this->input->post('product_price'),
				'product_price_offer'	=> $this->input->post('product_price_offer'),
				'product_payment_link'	=> $this->input->post('product_payment_link'),
				'product_description'	=> $this->input->post('product_description'),
				'product_tags'			=> $this->input->post('product_tags'),
				'product_status'        => 1,
				'product_slug'			=> $product_slug
			);

			if (!empty($this->input->post('product_highlights'))) {
				$info['product_highlights'] = json_encode($this->input->post('product_highlights'));
			}
			if (!empty($this->input->post('product_info_question'))) {
				$info['product_info_question'] = json_encode($this->input->post('product_info_question'));
			}
			if (!empty($this->input->post('product_info_answer'))) {
				$info['product_info_answer'] = json_encode($this->input->post('product_info_answer'));
			}

			if ($_FILES && $_FILES['gallery_image']['name']) {
				$img_info = [
					'upload_path' => 'images/products',
					'quality'     => '60%',
					'height'      => 500,
					'width'       => 500,
					'image_name'  => 'gallery_image'
				];
				$info['gallery_image'] = uploadMultipleAndResize($img_info, 5);
			}
			$gallery_note = upload_last_error();

			if ($this->adminmodel->add('products', $info)) {
				$this->session->set_flashdata('message', 'Successfully Product Added!!'
					. ($gallery_note !== '' ? ' Note: ' . $gallery_note : ''));
				redirect('admin/products');
			} else {
				$this->session->set_flashdata('message', 'Adding Product Failed!!');
				redirect('admin/add-product', 'refresh');
			}
		}
	}

	/* User Edit Product */
	public function edit_products($product_id = null)
	{
		// Handle POST request (form submission)
		if ($this->input->server('REQUEST_METHOD') === 'POST') {
			$product_id = (int) $this->input->post('product_id');
			$data['product'] = $this->adminmodel->getOnlyProduct($product_id);
		} else {
			// Handle GET request (initial load)
			$data['product'] = $this->adminmodel->getOnlyProduct($product_id);
		}

		// If product not found, redirect
		if (empty($data['product'])) {
			$this->session->set_flashdata('message', 'Invalid Product ID.');
			redirect('admin/products');
			return;
		}

		$data['productCategories'] = $this->adminmodel->getAllValues('product_categories');
		$data['users'] = $this->adminmodel->getActiveUsersForDropdown();

		if ($data['product']->category_id != '') {
			$data['sub_category_product'] = $this->adminmodel->ProductSubCategoryByCateId($data['product']->category_id);
		}

		$old_images = $data['product']->gallery_image;

		if ($this->form_validation->run('admin_products') == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/edit_product', $data);
			$this->load->view('admin/footer');
		} else {

			$info = array(
				'user_id' => $this->input->post('user_id'),
				'product_name' => $this->input->post('product_name'),
				'category_id' => $this->input->post('category_id'),
				'sub_category_id' => $this->input->post('sub_category_id'),
				'product_price' => $this->input->post('product_price'),
				'product_price_offer' => $this->input->post('product_price_offer'),
				'product_payment_link' => $this->input->post('product_payment_link'),
				'product_description' => $this->input->post('product_description'),
				'product_tags' => $this->input->post('product_tags'),
			);

			// Handle highlights
			if (!empty($this->input->post('product_highlights'))) {
				$info['product_highlights'] = json_encode($this->input->post('product_highlights'));
			}

			// Handle specifications (Q/A)
			if (!empty($this->input->post('product_info_question'))) {
				$info['product_info_question'] = json_encode($this->input->post('product_info_question'));
			}
			if (!empty($this->input->post('product_info_answer'))) {
				$info['product_info_answer'] = json_encode($this->input->post('product_info_answer'));
			}

			// Robust image upload logic — validated via safe_upload_image()
			// (random filename, MIME-sniffed, extension allow-listed, .htaccess
			// auto-written to block PHP execution in the target dir).
			$gallery_images = array();
			if (isset($_FILES['gallery_image']) && $_FILES['gallery_image']['name'][0] != '') {
				$files = $_FILES['gallery_image'];
				$count = count($files['name']);
				for ($i = 0; $i < $count; $i++) {
					$single = array(
						'name'     => $files['name'][$i],
						'type'     => $files['type'][$i],
						'tmp_name' => $files['tmp_name'][$i],
						'error'    => $files['error'][$i],
						'size'     => $files['size'][$i],
					);
					$saved = safe_upload_image($single, 'uploads/products/');
					if ($saved !== false) {
						$gallery_images[] = $saved;
					}
				}
				// Only update if at least one image uploaded
				if (!empty($gallery_images)) {
					$info['gallery_image'] = json_encode($gallery_images);
				} else {
					// If upload failed, keep old images
					$info['gallery_image'] = $this->input->post('gallery_image_old');
				}
			} else {
				// No new images, keep old
				$info['gallery_image'] = $this->input->post('gallery_image_old');
			}

			if ($this->adminmodel->update($product_id, $info, 'product_id', 'products')) {
				$this->session->set_flashdata('message', 'Successfully Updated Product!!');
				redirect('admin/products/');
			} else {
				$this->session->set_flashdata('message', 'Updating Product Failed!!');
				redirect('admin/edit-products/' . $product_id, 'refresh');
			}
		}
	}

	/* User Delete Product */
	public function delete_product($product_id)
	{
		// Fetch the product to check if it exists
		$product = $this->adminmodel->getOnlyProduct($product_id);

		if (!$product) {
			$this->session->set_flashdata('message', 'Product not found!');
			redirect('admin/products');
			return;
		}

		// Delete the product permanently
		$this->db->where('product_id', $product_id);
		$delete = $this->db->delete('products');

		if ($delete) {
			$this->session->set_flashdata('message', 'Product successfully deleted!');
		} else {
			$this->session->set_flashdata('message', 'Failed to delete the product!');
		}

		redirect('admin/products');
	}



	/*================
	    BLOG POST
	================*/
	/*All Blogs*/
	public function blog_list($page = 1)
	{
		$this->load->library('pagination');

		// Get total blog count
		$total_rows = $this->adminmodel->countAllBlogs();

		// Pagination Configuration
		$config = [
			'base_url'         => base_url('admin/blog_list'),
			'total_rows'       => $total_rows,
			'per_page' => admin_per_page(20),
			'reuse_query_string' => TRUE,
			'uri_segment'      => 3,
			'use_page_numbers' => TRUE,
			'num_links'        => 3,
			'page_query_string' => FALSE,

			// Bootstrap 4 Pagination Styling
			'full_tag_open'    => '<ul class="pagination">',
			'full_tag_close'   => '</ul>',
			'first_tag_open'   => '<li class="page-item">',
			'first_tag_close'  => '</li>',
			'last_tag_open'    => '<li class="page-item">',
			'last_tag_close'   => '</li>',
			'cur_tag_open'     => '<li class="page-item active"><a href="#" class="page-link">',
			'cur_tag_close'    => '</a></li>',
			'num_tag_open'     => '<li class="page-item">',
			'num_tag_close'    => '</li>',
			'prev_tag_open'    => '<li class="page-item">',
			'prev_tag_close'   => '</li>',
			'next_tag_open'    => '<li class="page-item">',
			'next_tag_close'   => '</li>',
			'first_link'       => 'First',
			'last_link'        => 'Last',
			'next_link'        => 'Next',
			'prev_link'        => 'Previous',
			'attributes'       => ['class' => 'page-link'],
		];

		$this->pagination->initialize($config);

		// Calculate offset
		$page = ($page < 1) ? 1 : $page;
		$offset = ($page - 1) * $config['per_page'];
		$data['start_number'] = ($page - 1) * $config['per_page'] + 1;

		// Fetch paginated blog records
		$data['blogList'] = $this->adminmodel->getBlogs($config['per_page'], $offset);

		// Collect blog IDs and fetch view counts
		$blog_ids = [];
		if (!empty($data['blogList'])) {
			foreach ($data['blogList'] as $b) {
				if (isset($b->blog_id)) $blog_ids[] = $b->blog_id;
			}
		}

		$data['views_counts'] = $this->adminmodel->getViewsForIds('blogs', 'blog_id', 'views', $blog_ids);

		$data['link'] = $this->pagination->create_links();

		$this->load->view('admin/header');
		$this->load->view('admin/all_blogs', $data);
		$this->load->view('admin/footer');
	}


	/* Preview blog */
	public function blog_details($slug)
	{
		$data['blog'] = $this->usermodel->getSingleValue($slug, 'blog_slug', 'blogs');

		$this->load->view('header', $data);
		$this->load->view('admin/blog_details');
		$this->load->view('footer');
	}

	/* Add new Blog */
	/**
	 * Read the blog form's publish-date field into a storable datetime.
	 *
	 * The input is `datetime-local`, so it arrives as "2026-08-05T14:30" —
	 * which MySQL will not take. Anything unparseable is treated as blank
	 * rather than written through, so a typo cannot put another unusable date
	 * in the column.
	 *
	 * @param  string     $posted
	 * @param  mixed      $empty  what a blank field means: the default stamps
	 *                            "now" (adding), NULL means "leave it alone"
	 *                            (editing).
	 * @return string|null
	 */
	private function blogPublishDate($posted, $empty = 'now')
	{
		$posted = trim((string) $posted);
		if ($posted !== '') {
			$ts = strtotime(str_replace('T', ' ', $posted));
			if ($ts !== false && $ts > 0) {
				return date('Y-m-d H:i:s', $ts);
			}
		}

		return $empty === 'now' ? date('Y-m-d H:i:s') : $empty;
	}

	/**
	 * Form-validation callback for the author account on news and blog
	 * posts (config/form_validation.php). The leading underscore keeps it
	 * from being reachable as a URL.
	 */
	public function _content_author_exists($user_id)
	{
		$ok = (int) $user_id > 0
			&& $this->db->where('user_id', (int) $user_id)->count_all_results('user') > 0;
		if (!$ok) {
			$this->form_validation->set_message('_content_author_exists', 'Choose the account this post belongs to.');
		}
		return $ok;
	}

	/**
	 * Add the byline to a news/blog row when the column exists (0035). An
	 * empty byline is stored as NULL, which means "print the account name".
	 */
	private function applyByline(array &$info, $table, $column)
	{
		if (!$this->db->field_exists($column, $table)) return;
		$byline = trim((string) $this->input->post($column));
		$info[$column] = $byline !== '' ? mb_substr($byline, 0, 120) : NULL;
	}

	public function add_blog()
	{
		$data['users']             = $this->adminmodel->getActiveUsersForDropdown();
		if ($this->form_validation->run('blogs') == false) {
			$this->load->view('admin/header', $data);
			//$this->load->view('admin/user_add_new_blog');
			$this->load->view('admin/add_new_blogs');
			$this->load->view('admin/footer');
		} else {
			$isenquiry = $this->input->post('isenquiry') == 'on' ? 1 : 0;
			$blog_slug = $this->makeUniqueContentSlug($this->input->post('blog_name'), 'blogs', 'blog_slug', 'post');
			$info = array(
				'user_id'			=> $this->input->post('user_id'),
				// Stored as typed. These used to go through htmlentities(), which
				// encoded the editor's markup — so the column held
				// "&lt;p&gt;…" and, since every read escapes again on output,
				// readers saw a literal "<p>" and "&rsquo;" on the cards. The
				// public form (User::add_blog) never did this. Output is
				// escaped at the point of display, as it is everywhere else.
				'blog_name'    		=> $this->input->post('blog_name'),
				'blog_description'  => $this->input->post('blog_description'),
				'blog_status'		=> 1,
				'isenquiry'         => $isenquiry,
				'blog_slug'			=> $blog_slug,
				// This was missing, which is why every admin-created post showed
				// the same date: with nothing supplied the column took the zero
				// date, and the index printed the epoch for all of them.
				// The form's date field wins; an empty one means "now".
				'created_at'		=> $this->blogPublishDate($this->input->post('created_at')),
			);
			$this->applyByline($info, 'blogs', 'blog_byline');

			if ($_FILES && $_FILES['blog_image']['name']) {
				$img_info = [
					'upload_path' => 'images/blogs',
					'quality'     => '60%',
					'height'      => 500,
					'width'       => 1000,
					'image_name'  => 'blog_image'
				];
				$info['blog_image'] = uploadAndResize($img_info);
			}

			if ($this->usermodel->add('blogs', $info)) {
				$this->session->set_flashdata('message', 'Successfully Added Blog!!');
				redirect('admin/blog-list');
			} else {
				$this->session->set_flashdata('error', 'Could not save the blog post. ' . $this->adminmodel->lastDbError());
				// Redirect, don't fall through. These branches used to set the
				// message and then end the request with no view loaded at all —
				// a blank white page, nothing saved, no explanation.
				redirect('admin/blog-list');
			}
		}
	}

	/* Edit User Blog */
	public function edit_blog($slug)
	{
		$data['users']   = $this->adminmodel->getActiveUsersForDropdown();

		$data['blog']    = $this->usermodel->getSingleValue($slug, 'blog_slug', 'blogs');
		if (empty($data['blog'])) {
			$this->session->set_flashdata('error', 'Blog not found.');
			redirect('admin/blog-list');
			return;
		}
		$old_image       = $data['blog']->blog_image;

		if ($this->form_validation->run('blogs') == FALSE) {
			$this->load->view('admin/header', $data);
			$this->load->view('admin/user_edit_blog');
			$this->load->view('admin/footer');
		} else {
			$isenquiry = $this->input->post('isenquiry') == 'on' ? 1 : 0;
			// The slug is NOT regenerated. It is this post's permalink, and the
			// URL of the page the admin is standing on. Rewriting it from the
			// title on every save meant that saving once changed the address,
			// so going back to the bookmarked edit URL hit "Blog not found." —
			// which is what "the date is not updated, where can I change it?"
			// looked like from the outside. It also escalated: a title holding
			// an encoded apostrophe produced "America-039-s", then
			// "America-amp-039-s" the next time round.
			$info = array(
				'user_id'			=> $this->input->post('user_id'),
				// Stored as typed — see the note in add_blog().
				'blog_name'    		=> $this->input->post('blog_name'),
				'blog_description'  => $this->input->post('blog_description'),
				'isenquiry'         => $isenquiry,
			);
			$this->applyByline($info, 'blogs', 'blog_byline');

			// Publish date is editable here — posts that predate this field
			// carry no usable date at all, and there is nothing in the row to
			// recover it from, so an admin has to be able to say what it was.
			// Left blank on a post that already has one: keep what is stored.
			$posted_date = $this->blogPublishDate($this->input->post('created_at'), null);
			if ($posted_date !== null) {
				$info['created_at'] = $posted_date;
			} elseif (us_date_ts($data['blog']->created_at ?? null) === false) {
				// No date stored and none given — stamp it now rather than
				// leaving another row that prints as the epoch.
				$info['created_at'] = date('Y-m-d H:i:s');
			}

			if ($_FILES && $_FILES['blog_image']['name']) {
				$img_info = [
					'upload_path' => 'images/blogs',
					'quality'     => '60%',
					'height'      => 500,
					'width'       => 1000,
					'image_name'  => 'blog_image'
				];

				$image_link = $old_image;

				if ($image_link != null) {
					if (file_exists('./' . $image_link)) {
						unlink($image_link);
					}
				}

				$info['blog_image'] = uploadAndResize($img_info);
			} else {
				$info['blog_image'] = $old_image;
			}

			if ($this->adminmodel->update($slug, $info, 'blog_slug', 'blogs')) {
				$this->session->set_flashdata('message', 'Successfully Updated Blog!!');
				redirect('admin/blog-list', 'refresh');
			} else {
				$this->session->set_flashdata('message', 'Sorry, Update Blog Failed!!');
				redirect('admin/edit-blog' . $id, 'refresh');
			}
		}
	}

	/*Delete Blog*/
	public function delete_blog($slug)
	{
		if ($this->usermodel->deleteBlog($slug)) {
			$this->session->set_flashdata('message', 'Successfully Removed Blog!!');
			redirect('admin/blog-list');
		}
	}


	/*================
	      News
	================*/
	/*All News*/
	public function news_list($page = 1)
	{
		$this->load->library('pagination');
		$this->load->model('AdminModel');

		// Get total news count
		$total_rows = $this->AdminModel->countAllNews();

		// Pagination Configuration
		$config = [
			'base_url'         => base_url('admin/news_list'),
			'total_rows'       => $total_rows,
			'per_page' => admin_per_page(20),
			'reuse_query_string' => TRUE,
			'uri_segment'      => 3,
			'use_page_numbers' => TRUE,
			'num_links'        => 3,
			'page_query_string' => FALSE,

			// Bootstrap 4 Pagination Styling
			'full_tag_open'    => '<ul class="pagination">',
			'full_tag_close'   => '</ul>',
			'first_tag_open'   => '<li class="page-item">',
			'first_tag_close'  => '</li>',
			'last_tag_open'    => '<li class="page-item">',
			'last_tag_close'   => '</li>',
			'cur_tag_open'     => '<li class="page-item active"><a href="#" class="page-link">',
			'cur_tag_close'    => '</a></li>',
			'num_tag_open'     => '<li class="page-item">',
			'num_tag_close'    => '</li>',
			'prev_tag_open'    => '<li class="page-item">',
			'prev_tag_close'   => '</li>',
			'next_tag_open'    => '<li class="page-item">',
			'next_tag_close'   => '</li>',
			'first_link'       => 'First',
			'last_link'        => 'Last',
			'next_link'        => 'Next',
			'prev_link'        => 'Previous',
			'attributes'       => ['class' => 'page-link'],
		];

		$this->pagination->initialize($config);

		// Calculate offset
		$page = ($page < 1) ? 1 : $page;
		$offset = ($page - 1) * $config['per_page'];
		$data['start_number'] = ($page - 1) * $config['per_page'] + 1;

		// Fetch paginated news records
		$data['newsList'] = $this->AdminModel->getNews($config['per_page'], $offset);

		// Collect news IDs and fetch their view counts
		$news_ids = [];
		if (!empty($data['newsList'])) {
			foreach ($data['newsList'] as $n) {
				if (isset($n->news_id)) $news_ids[] = $n->news_id;
			}
		}

		$data['views_counts'] = $this->adminmodel->getViewsForIds('news', 'news_id', 'views', $news_ids);

		$data['link'] = $this->pagination->create_links();

		// Load views
		$this->load->view('admin/header');
		$this->load->view('admin/all_news', $data);
		$this->load->view('admin/footer');
	}

	/* Preview News */
	public function news_details($slug)
	{

		$data['logged_id'] = NULL;

		if ($this->session->userdata('logged_admin')) {
			$session 		   = $this->session->userdata('logged_admin');
			$data['logged_id'] = $session['logged_id'];
		}

		$data['news'] = $this->usermodel->getSingleValue($slug, 'news_slug', 'news');

		$this->load->view('header');
		$this->load->view('news_details_n', $data);
		$this->load->view('footer');
	}

	/* Add new News */
	public function add_news()
	{
		$data['users'] = $this->adminmodel->getActiveUsersForDropdown();
		if ($this->form_validation->run('news') == false) {
			$data['all_news_category'] = $this->adminmodel->allNewsCategory();
			$this->load->view('admin/header', $data);
			$this->load->view('admin/add_new_news');
			$this->load->view('admin/footer');
		} else {
			$news_slug = $this->makeUniqueContentSlug($this->input->post('news_title'), 'news', 'news_slug', 'news');
			$info = array(
				'user_id'			=> $this->input->post('user_id'),
				// Stored as typed, like the blog paths and like events. These used
				// to go through htmlentities(), so the column held "&lt;p&gt;…"
				// rather than markup — and because every read escapes again on
				// output, the reader saw a literal "<p>" on the News page. The
				// title had the same treatment, which is why an apostrophe showed
				// up as "&#039;".
				'news_title'    	=> $this->input->post('news_title'),
				'news_description'  => $this->input->post('news_description'),
				'news_category_id'  => $this->input->post('news_category_id'),
				'news_seo_title'  	=> $this->input->post('news_seo_title'),
				'news_seo_description' => $this->input->post('news_seo_description'),
				'news_seo_keywords' => $this->input->post('news_seo_keywords'),
				// 0/1, never NULL: an unticked box posts nothing, and the live
				// column is NOT NULL — that NULL was the 500 on "add news".
				'featured_news'  	=> $this->input->post('featured_news') ? 1 : 0,
				'news_slug'			=> $news_slug
			);
			$this->applyByline($info, 'news', 'news_byline');

			// isset(), not just a truthy $_FILES: a save with no picture attached
			// left $_FILES non-empty but without this key, which is an
			// "Undefined array key" warning on every text-only article.
			$upload_note = '';
			if (!empty($_FILES['news_image']['name'])) {
				$img_info = [
					'upload_path' => 'images/news',
					'quality'     => '60%',
					'height'      => 500,
					'width'       => 1000,
					'image_name'  => 'news_image'
				];
				$info['news_image'] = uploadAndResize($img_info);

				// A rejected upload used to store NULL in silence, so the
				// article saved without its picture and nothing said why.
				if (empty($info['news_image'])) {
					$upload_note = ' The picture was not saved: '
						. (upload_last_error() ?: 'the file could not be read.')
						. ' The article is saved — edit it to add the image again.';
				}
			}

			// db_safe_insert(): fitted to the live table and never a 500 — see
			// application/helpers/db_safe_helper.php.
			if (db_safe_insert('news', $info)) {
				$this->session->set_flashdata(
					$upload_note === '' ? 'message' : 'error',
					'Successfully Added News!!' . $upload_note
				);
				redirect('admin/news-list');
			} else {
				$this->session->set_flashdata('error', 'Could not save the news article: ' . db_safe_last_error());
				// Redirect, don't fall through. These branches used to set the
				// message and then end the request with no view loaded at all —
				// a blank white page, nothing saved, no explanation.
				redirect('admin/news-list');
			}
		}
	}

	/* Edit User News */
	public function edit_news($slug)
	{
		$data['users']   = $this->adminmodel->getActiveUsersForDropdown();

		$data['news']    = $this->usermodel->getSingleValue($slug, 'news_slug', 'news');
		if (empty($data['news'])) {
			$this->session->set_flashdata('error', 'News article not found.');
			redirect('admin/news-list');
			return;
		}
		$old_image       = $data['news']->news_image;

		if ($this->form_validation->run('news') == FALSE) {
			$data['all_news_category'] = $this->adminmodel->allNewsCategory();
			$this->load->view('admin/header');
			$this->load->view('admin/user_edit_news', $data);
			$this->load->view('admin/footer');
		} else {
			// Stored as typed — see the note in add_news(). The slug is
			// deliberately NOT regenerated here: it is this article's permalink,
			// and rewriting it on every save broke the admin's own edit URL.
			$info = array(
				'user_id'			=> $this->input->post('user_id'),
				'news_title'    		=> $this->input->post('news_title'),
				'news_description'  => $this->input->post('news_description'),
				'news_category_id'  => $this->input->post('news_category_id'),
				'news_seo_title'  => $this->input->post('news_seo_title'),
				'news_seo_description'  => $this->input->post('news_seo_description'),
				'news_seo_keywords'  => $this->input->post('news_seo_keywords'),
				'featured_news'  => $this->input->post('featured_news') ? 1 : 0
			);
			$this->applyByline($info, 'news', 'news_byline');

			$upload_note = '';

			if (!empty($_FILES['news_image']['name'])) {
				$img_info = [
					'upload_path' => 'images/news',
					'quality'     => '60%',
					'height'      => 500,
					'width'       => 1000,
					'image_name'  => 'news_image'
				];

				$new_image = uploadAndResize($img_info);

				if (!empty($new_image)) {
					// Delete the old file only now that a replacement exists.
					// This used to unlink() BEFORE the upload ran, so a picture
					// the upload then rejected — wrong type, too big, or the
					// resize that used to fatal on a phone photo — took the
					// existing one with it and the article was left with none.
					if (!empty($old_image) && $old_image !== $new_image && file_exists(FCPATH . $old_image)) {
						@unlink(FCPATH . $old_image);
					}
					$info['news_image'] = $new_image;
				} else {
					$info['news_image'] = $old_image;
					$upload_note = ' The new picture was not saved: '
						. (upload_last_error() ?: 'the file could not be read.')
						. ' The article keeps the picture it had.';
				}
			} else {
				$info['news_image'] = $old_image;
			}

			if (db_safe_update('news', $info, array('news_slug' => $slug))) {
				$this->session->set_flashdata(
					$upload_note === '' ? 'message' : 'error',
					'Successfully Updated News!!' . $upload_note
				);
				redirect('admin/news-list', 'refresh');
			} else {
				$this->session->set_flashdata('error', 'Sorry, Update News Failed: ' . db_safe_last_error());
				// The slash was missing, so a failed save redirected to
				// "admin/edit-news<slug>" — a 404 instead of the form.
				redirect('admin/edit-news/' . $slug, 'refresh');
			}
		}
	}

	/*Delete News*/
	public function delete_news($slug)
	{
		if ($this->adminmodel->deleteNews($slug)) {
			$this->session->set_flashdata('message', 'Successfully Removed News!!');
			redirect('admin/news-list');
		}
	}
	/*News Category*/

	/*====================
			All News Category
			=====================*/

	public function all_news_category()
	{
		$data['user'] = $this->adminmodel->getAdminInformation();
		$data['all_news_category'] = $this->adminmodel->allNewsCategory();

		$this->load->view('admin/header');
		$this->load->view('admin/all_news_category', $data);
		$this->load->view('admin/footer');
	}
	/*====================
			Add new Category
			=====================*/

	public function add_news_category()
	{
		$this->form_validation->set_rules('category_name', 'Category Name', 'trim|required|is_unique[news_categories.category_name]');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/add_news_category');
			$this->load->view('admin/footer');
		} else {
			$data = array();
			$category_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('category_name'));
			$data['category_name'] = $this->input->post('category_name');
			$data['category_seo_title'] = $this->input->post('category_seo_title');
			$data['category_seo_keywords'] = $this->input->post('category_seo_keywords');
			$data['category_seo_description'] = $this->input->post('category_seo_description');
			$data['category_slug'] = $category_slug;
			if ($_FILES && $_FILES['category_image']['name']) {
				$img_info = [
					'upload_path' => 'images/news',
					'quality'     => '60%',
					'image_name'  => 'category_image'
				];
				$data['category_image'] = uploadAndResize($img_info);
			}
			if ($this->adminmodel->addNewsCategory($data)) {
				$this->session->set_flashdata('message', 'Successfully Added News!!');
				redirect('admin/all_news_category');
			} else {
				$this->session->set_flashdata('message', 'Adding News Category failed!!');
			}
		}
	}

	/*====================
			Edit Category
			=====================*/
	public function edit_news_category($id)
	{
		$data['category'] = $this->adminmodel->getSingleValue($id, 'category_id', 'news_categories');
		$old_image        = $data['category']->category_image;

		$this->form_validation->set_rules('category_name', 'Category Name', 'trim|required');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/edit_news_category', $data);
			$this->load->view('admin/footer');
		} else {
			$info['category_name'] = $this->input->post('category_name');
			$category_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('category_name'));
			$info['category_name'] = $this->input->post('category_name');
			$info['category_seo_title'] = $this->input->post('category_seo_title');
			$info['category_seo_keywords'] = $this->input->post('category_seo_keywords');
			$info['category_seo_description'] = $this->input->post('category_seo_description');
			$info['category_slug'] = $category_slug;
			if ($_FILES && $_FILES['category_image']['name']) {
				$img_info = [
					'upload_path' => 'images/news',
					'quality'     => '60%',
					'image_name'  => 'category_image'
				];

				$image_link = $old_image;
				if ($image_link != null) {
					if (file_exists('./' . $image_link)) {
						unlink($image_link);
					}
				}


				$info['category_image'] = uploadAndResize($img_info);
			} else {
				$info['category_image'] = $old_image;
			}

			if ($this->adminmodel->updateNewsCategory($id, $info)) {
				$this->session->set_flashdata('message', 'Successfully Updated News Category!!');
				redirect('admin/all_news_category', 'refresh');
			} else {
				$this->session->set_flashdata('message', 'Sorry, Update Listing Category failed!!');
				redirect('admin/edit_news_category', 'refresh');
			}
		}
	}
	/*====================
			Delete News Category
			=====================*/
	public function delete_news_category($id)
	{
		if ($this->adminmodel->deleteNewsCategory($id)) {
			redirect('admin/all_news_category', 'refresh');
		} else {
			redirect('admin/all_news_category', 'refresh');
		}
	}
	/*News*/

	/*
			Start Video 
			*/
	/*====================
			All Video Category
			=====================*/

	public function all_video_category($page = 1)
	{
		$this->load->library('pagination');

		// Get total video category count
		$total_rows = $this->adminmodel->countAllVideoCategories();

		// Pagination Configuration
		$config = [
			'base_url'         => base_url('admin/all_video_category'),
			'total_rows'       => $total_rows,
			'per_page' => admin_per_page(20),
			'reuse_query_string' => TRUE,
			'uri_segment'      => 3,
			'use_page_numbers' => TRUE,
			'num_links'        => 3,
			'page_query_string' => FALSE,

			// Bootstrap 4 Pagination Styling
			'full_tag_open'    => '<ul class="pagination">',
			'full_tag_close'   => '</ul>',
			'first_tag_open'   => '<li class="page-item">',
			'first_tag_close'  => '</li>',
			'last_tag_open'    => '<li class="page-item">',
			'last_tag_close'   => '</li>',
			'cur_tag_open'     => '<li class="page-item active"><a href="#" class="page-link">',
			'cur_tag_close'    => '</a></li>',
			'num_tag_open'     => '<li class="page-item">',
			'num_tag_close'    => '</li>',
			'prev_tag_open'    => '<li class="page-item">',
			'prev_tag_close'   => '</li>',
		];
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

		// use_page_numbers is TRUE, so segment 3 is a PAGE NUMBER (1, 2, 3…),
		// not a row offset. It used to be passed straight through as the
		// offset, so page 2 started at row 2 and every category past the
		// first page could never be reached — which is where the "random"
		// junk categories at the end of the alphabet were hiding.
		$page   = max(1, (int) $this->uri->segment(3));
		$offset = ($page - 1) * (int) $config['per_page'];
		$data['start_number'] = $offset + 1;
		$data['all_video_category']   = $this->adminmodel->allVideoCategoryWithCount($config['per_page'], $offset);

		$data['link'] = $this->pagination->create_links();

		// Load views
		$this->load->view('admin/header');
		$this->load->view('admin/all_video_category', $data);
		$this->load->view('admin/footer');
	}

	/*====================
			Add new Category
			=====================*/
	public function add_video_category()
	{
		$this->form_validation->set_rules('category_name', 'Category Name', 'trim|required|is_unique[video_categories.category_name]');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/add_video_category');
			$this->load->view('admin/footer');
		} else {
			$data = array();
			$category_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('category_name'));
			$data['category_name'] = $this->input->post('category_name');
			$data['category_seo_title'] = $this->input->post('category_seo_title');
			$data['category_seo_keywords'] = $this->input->post('category_seo_keywords');
			$data['category_seo_description'] = $this->input->post('category_seo_description');
			$data['category_slug'] = $category_slug;
			if ($_FILES && $_FILES['category_image']['name']) {
				$img_info = [
					'upload_path' => 'images/video',
					'quality'     => '60%',
					'image_name'  => 'category_image'
				];
				$data['category_image'] = uploadAndResize($img_info);
			}
			if ($this->adminmodel->addVideoCategory($data)) {
				$this->session->set_flashdata('message', 'Successfully Added Category!!');
				redirect('admin/all_video_category');
			} else {
				$this->session->set_flashdata('message', 'Adding Video Category failed!!');
			}
		}
	}

	/*====================
			Edit Category
			=====================*/
	public function edit_video_category($slug)
	{
		$data['category'] = $this->adminmodel->getSingleValue($slug, 'category_slug', 'video_categories');
		if (!$data['category']) {
			$this->session->set_flashdata('error', 'Video category not found.');
			redirect('admin/all_video_category');
			return;
		}
		$old_image        = $data['category']->category_image;
		$category_id      = (int) $data['category']->category_id;

		// Renaming onto another category's name used to be allowed, leaving
		// two "Sports" entries — the add form checked, the edit form did not.
		$this->form_validation->set_rules('category_name', 'Category Name',
			'trim|required|max_length[100]|callback__video_category_name_free[' . $category_id . ']');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/edit_video_category', $data);
			$this->load->view('admin/footer');
		} else {
			$name = trim((string) $this->input->post('category_name'));
			$info = array(
				'category_name'            => $name,
				'category_seo_title'       => $this->input->post('category_seo_title'),
				'category_seo_keywords'    => $this->input->post('category_seo_keywords'),
				'category_seo_description' => $this->input->post('category_seo_description'),
			);

			// A new slug only when the NAME changed, and one no other
			// category has. It was regenerated on every save, and could
			// collide with an existing category's slug.
			if (strcasecmp($name, (string) $data['category']->category_name) !== 0) {
				$info['category_slug'] = $this->adminmodel->uniqueVideoCategorySlug($name, $category_id);
			}

			// Upload first, unlink after — a rejected upload used to leave the
			// category pointing at a deleted file.
			$info['category_image'] = $old_image;
			$upload_note = '';
			if (!empty($_FILES['category_image']['name'])) {
				$new_image = uploadAndResize([
					'upload_path' => 'images/video',
					'quality'     => '60%',
					'image_name'  => 'category_image'
				]);
				if ($new_image) {
					$info['category_image'] = $new_image;
					if ($old_image && $old_image !== $new_image && file_exists('./' . $old_image)) {
						@unlink('./' . $old_image);
					}
				} else {
					$upload_note = ' The new image was not saved: ' . (upload_last_error() ?: 'the file could not be read.');
				}
			}

			// updateVideoCategory(), not updatemarketplaceCategory(). This used
			// to call the marketplace one, which updates a different TABLE
			// (marketplace_categories) on a different KEY (category_id, an INT,
			// compared against a slug string that MySQL casts to 0). Nothing
			// ever matched — but CI3's update() returns TRUE whenever the query
			// runs, so renaming a video category reported "Successfully Updated"
			// every time while the name never changed.
			if ($this->adminmodel->updateVideoCategory($slug, $info)) {
				$this->session->set_flashdata($upload_note === '' ? 'message' : 'error', 'Video category updated.' . $upload_note);
				redirect('admin/all_video_category', 'refresh');
			} else {
				$this->session->set_flashdata('error', 'Sorry, updating the video category failed. ' . $this->adminmodel->lastDbError());
				redirect('admin/edit_video_category/' . rawurlencode((string) $slug), 'refresh');
			}
		}
	}

	/** Form-validation callback: is this video category name free (bar $except_id)? */
	public function _video_category_name_free($name, $except_id = 0)
	{
		$taken = $this->db->where('LOWER(TRIM(category_name)) =', mb_strtolower(trim((string) $name)))
			->where('category_id !=', (int) $except_id)
			->count_all_results('video_categories') > 0;
		if ($taken) {
			$this->form_validation->set_message('_video_category_name_free', 'There is already a video category with that name.');
		}
		return !$taken;
	}

	/*====================
			Delete Video Category
			=====================*/
	public function delete_video_category($slug)
	{
		// Videos filed under the category are kept and become uncategorised
		// (Adminmodel::deleteVideoCategory); they used to be left pointing at
		// a category id that no longer existed.
		$moved = $this->adminmodel->deleteVideoCategory($slug);
		if ($moved === false) {
			$this->session->set_flashdata('error', 'That video category could not be deleted (it may already be gone).');
		} else {
			$this->session->set_flashdata('message', 'Category deleted.'
				. ($moved > 0 ? ' ' . $moved . ' video' . ($moved === 1 ? ' was' : 's were') . ' in it and are now uncategorised — they are still published.' : ''));
		}
		redirect('admin/all_video_category', 'refresh');
	}
	/*End Video Category*/


	/*All Video*/
	public function video_list($page = 1)
	{
		$this->load->library('pagination');

		// Get total video count
		$total_rows = $this->adminmodel->countAllVideos();

		// Pagination Configuration
		$config = [
			'base_url'         => base_url('admin/video_list'),
			'total_rows'       => $total_rows,
			'per_page' => admin_per_page(20),
			'reuse_query_string' => TRUE,
			'uri_segment'      => 3,
			'use_page_numbers' => TRUE,
			'num_links'        => 3,
			'page_query_string' => FALSE,

			// Bootstrap 4 Pagination Styling
			'full_tag_open'    => '<ul class="pagination">',
			'full_tag_close'   => '</ul>',
			'first_tag_open'   => '<li class="page-item">',
			'first_tag_close'  => '</li>',
			'last_tag_open'    => '<li class="page-item">',
			'last_tag_close'   => '</li>',
			'cur_tag_open'     => '<li class="page-item active"><a href="#" class="page-link">',
			'cur_tag_close'    => '</a></li>',
			'num_tag_open'     => '<li class="page-item">',
			'num_tag_close'    => '</li>',
			'prev_tag_open'    => '<li class="page-item">',
			'prev_tag_close'   => '</li>',
		];
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

		$page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
		$offset = ($page < 1) ? 0 : ($page - 1) * $config['per_page'];
		$data['start_number'] = $page + 1;
		$data['videoList'] = $this->adminmodel->getVideo($config['per_page'], $offset);

		// Collect video IDs and fetch their view counts
		$video_ids = [];
		if (!empty($data['videoList'])) {
			foreach ($data['videoList'] as $v) {
				if (isset($v->video_id)) $video_ids[] = $v->video_id;
			}
		}

		$data['views_counts'] = $this->adminmodel->getViewsForIds('video', 'video_id', 'views', $video_ids);

		$data['link'] = $this->pagination->create_links();

		// Load views
		$this->load->view('admin/header');
		$this->load->view('admin/all_video', $data);
		$this->load->view('admin/footer');
	}


	/* Preview Video */
	public function video_details($slug)
	{
		$data['video'] = $this->usermodel->getSingleValue($slug, 'video_slug', 'video');

		$this->load->view('header');
		$this->load->view('admin/video_details', $data);
		$this->load->view('footer');
	}

	/* Add new Video */
	public function add_video()
	{
		$data['users'] = $this->adminmodel->getActiveUsersForDropdown();
		if ($this->form_validation->run('video') == false) {
			$data['all_video_category'] = $this->adminmodel->allVideoCategory(NULL, NULL);
			$this->load->view('admin/header', $data);
			$this->load->view('admin/add_new_video');
			$this->load->view('admin/footer');
		} else {
			$video_slug = $this->makeUniqueContentSlug($this->input->post('video_title'), 'video', 'video_slug', 'video');
			// Stored as typed — see the note in add_news(). video_code in
			// particular must not be encoded: youtube_id() parses it, and an
			// encoded "&" in a watch URL stops the id being found.
			$info = array(
				'user_id'			=> $this->input->post('user_id'),
				'video_title'    		=> $this->input->post('video_title'),
				'video_code'    		=> $this->input->post('video_code'),
				'featured_video'    		=> $this->input->post('featured_video') ? 1 : 0,
				'video_description'  => $this->input->post('video_description'),
				'video_category_id'  => $this->input->post('video_category_id'),
				'video_seo_title'  => $this->input->post('video_seo_title'),
				'video_seo_description'  => $this->input->post('video_seo_description'),
				'video_seo_keywords'  => $this->input->post('video_seo_keywords'),
				'video_slug'  => $video_slug
			);

			$upload_note = '';
			if (!empty($_FILES['video_image']['name'])) {
				$img_info = [
					'upload_path' => 'images/video',
					'quality'     => '60%',
					'image_name'  => 'video_image'
				];
				$info['video_image'] = uploadAndResize($img_info);
				if (!$info['video_image']) {
					// No still is fine — the YouTube thumbnail stands in — but
					// say so rather than dropping the upload in silence.
					$upload_note = ' The picture was not saved: '
						. (upload_last_error() ?: 'the file could not be read.')
						. ' The YouTube thumbnail will be shown instead.';
				}
			}

			if (db_safe_insert('video', $info)) {
				$this->session->set_flashdata($upload_note === '' ? 'message' : 'error', 'Successfully Added Video!!' . $upload_note);
				redirect('admin/video-list');
			} else {
				$this->session->set_flashdata('error', 'Could not save the video: ' . db_safe_last_error());
				// Redirect, don't fall through. These branches used to set the
				// message and then end the request with no view loaded at all —
				// a blank white page, nothing saved, no explanation.
				redirect('admin/video-list');
			}
		}
	}

	/* Edit Video */
	public function edit_video($slug)
	{
		$data['users']   = $this->adminmodel->getActiveUsersForDropdown();
		$data['video']    = $this->usermodel->getSingleValue($slug, 'video_slug', 'video');
		// A stale or mistyped link used to fatal on ->video_image of NULL.
		if (!$data['video']) {
			$this->session->set_flashdata('error', 'Video not found.');
			redirect('admin/video-list');
			return;
		}
		$old_image       = $data['video']->video_image;

		if ($this->form_validation->run('video') == FALSE) {
			$data['all_video_category'] = $this->adminmodel->allVideoCategory(NULL, NULL);
			$this->load->view('admin/header', $data);
			$this->load->view('admin/edit_video', $data);
			$this->load->view('admin/footer');
		} else {
			// Stored as typed — see the note in add_news(). The slug is not
			// regenerated: it is this video's permalink.
			$info = array(
				'user_id'			=> $this->input->post('user_id'),
				'video_title'    		=> $this->input->post('video_title'),
				'video_code'    		=> $this->input->post('video_code'),
				'featured_video'    		=> $this->input->post('featured_video') ? 1 : 0,
				'video_description'  => $this->input->post('video_description'),
				'video_category_id'  => $this->input->post('video_category_id'),
				'video_seo_title'  => $this->input->post('video_seo_title'),
				'video_seo_description'  => $this->input->post('video_seo_description'),
				'video_seo_keywords'  => $this->input->post('video_seo_keywords')
			);

			// Upload first, unlink after — as edit_news() does. This deleted the
			// old still BEFORE the upload ran, so a rejected file left the
			// video pointing at a picture that no longer existed.
			$info['video_image'] = $old_image;
			$upload_note = '';
			if (!empty($_FILES['video_image']['name'])) {
				$new_image = uploadWithoutResize([
					'upload_path' => 'images/video',
					'quality'     => '60%',
					'image_name'  => 'video_image'
				]);
				if ($new_image) {
					$info['video_image'] = $new_image;
					if ($old_image && $old_image !== $new_image && file_exists(FCPATH . $old_image)) {
						@unlink(FCPATH . $old_image);
					}
				} else {
					$upload_note = ' The new picture was not saved: '
						. (upload_last_error() ?: 'the file could not be read.')
						. ' The previous one was kept.';
				}
			}

			if (db_safe_update('video', $info, array('video_slug' => $slug))) {
				$this->session->set_flashdata($upload_note === '' ? 'message' : 'error', 'Successfully Updated Video!!' . $upload_note);
				redirect('admin/video-list', 'refresh');
			} else {
				$this->session->set_flashdata('error', 'Sorry, Update Video Failed: ' . db_safe_last_error());
				// The slash was missing ('admin/edit-video' . $slug), so a
				// failed save redirected to a URL that does not exist.
				redirect('admin/edit-video/' . rawurlencode($slug), 'refresh');
			}
		}
	}

	/*Delete Video*/
	public function delete_video($slug)
	{
		if ($this->adminmodel->deleteVideo($slug)) {
			$this->session->set_flashdata('message', 'Successfully Removed Video!!');
			redirect('admin/video-list');
		}
	}
	/*
			End Video 
			*/

	/*
		Start classifieds 
		*/
	/*=========================
		  All classifieds Category
		=========================*/

	public function all_classifieds_category($page = 1)
	{
		$this->load->library('pagination');

		// Get total classifieds category count
		$total_rows = $this->adminmodel->countAllClassifiedsCategories();

		// Pagination Configuration
		$config = [
			'base_url'         => base_url('admin/all_classifieds_category'),
			'total_rows'       => $total_rows,
			'per_page' => admin_per_page(20),
			'reuse_query_string' => TRUE,
			'uri_segment'      => 3,
			'use_page_numbers' => TRUE,
			'num_links'        => 3,
			'page_query_string' => FALSE,

			// Bootstrap 4 Pagination Styling
			'full_tag_open'    => '<ul class="pagination">',
			'full_tag_close'   => '</ul>',
			'first_tag_open'   => '<li class="page-item">',
			'first_tag_close'  => '</li>',
			'last_tag_open'    => '<li class="page-item">',
			'last_tag_close'   => '</li>',
			'cur_tag_open'     => '<li class="page-item active"><a href="#" class="page-link">',
			'cur_tag_close'    => '</a></li>',
			'num_tag_open'     => '<li class="page-item">',
			'num_tag_close'    => '</li>',
			'prev_tag_open'    => '<li class="page-link">',
			'prev_tag_close'   => '</li>',
		];
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

		$page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
		$data['start_number'] = $page + 1;
		$data['all_classifieds_category']   = $this->adminmodel->allClassifiedsCategory($config['per_page'], $page);
		$data['link'] 		   = $this->pagination->create_links();

		//////////////////////////

		$data['user'] 				  = $this->adminmodel->getAdminInformation();

		// $data['all_listing_category'] = $this->adminmodel->allListingCategory();
		// echo "<pre>";var_dump($data['all_listing_category']);die();

		$this->load->view('admin/header');
		$this->load->view('admin/all_classifieds_category', $data);
		$this->load->view('admin/footer');
	}


	/*====================
		Add new Category
		=====================*/

	public function add_classifieds_category()
	{
		$this->form_validation->set_rules('category_name', 'Category Name', 'trim|required|is_unique[classifieds_categories.category_name]');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/add_classifieds_category');
			$this->load->view('admin/footer');
		} else {
			$data = array();
			$category_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('category_name'));
			$data['category_name'] = $this->input->post('category_name');
			$data['category_seo_title'] = $this->input->post('category_seo_title');
			$data['category_seo_keywords'] = $this->input->post('category_seo_keywords');
			$data['category_seo_description'] = $this->input->post('category_seo_description');
			$data['category_slug'] = $category_slug;

			if ($_FILES && $_FILES['category_image']['name']) {
				$img_info = [
					'upload_path' => 'images/classifieds',
					'quality'     => '60%',
					'image_name'  => 'category_image'
				];
				$data['category_image'] = uploadAndResize($img_info);
			}
			// echo "<pre>";var_dump($data);die();
			if ($this->adminmodel->addClassifiedsCategory($data)) {
				$this->session->set_flashdata('message', 'Successfully Added Category!!');
				redirect('admin/all_classifieds_category');
			} else {
				$this->session->set_flashdata('message', 'Adding classifieds Category failed!!');
			}
		}
	}

	/*====================
		Edit Category
		=====================*/
	public function edit_classifieds_category($slug)
	{
		$data['category'] = $this->adminmodel->getSingleValue($slug, 'category_slug', 'classifieds_categories');
		$old_image        = $data['category']->category_image;
		$this->form_validation->set_rules('category_name', 'Category Name', 'trim|required');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/edit_classifieds_category', $data);
			$this->load->view('admin/footer');
		} else {
			$info['category_name'] = $this->input->post('category_name');
			$category_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('category_name'));
			$info['category_name'] = $this->input->post('category_name');
			$info['category_seo_title'] = $this->input->post('category_seo_title');
			$info['category_seo_keywords'] = $this->input->post('category_seo_keywords');
			$info['category_seo_description'] = $this->input->post('category_seo_description');
			$info['category_slug'] = $category_slug;
			if ($_FILES && $_FILES['category_image']['name']) {
				$img_info = [
					'upload_path' => 'images/classifieds',
					'quality'     => '60%',
					'image_name'  => 'category_image'
				];

				$image_link = $old_image;
				if ($image_link != null) {
					if (file_exists('./' . $image_link)) {
						unlink($image_link);
					}
				}


				$info['category_image'] = uploadAndResize($img_info);
			} else {
				$info['category_image'] = $old_image;
			}

			if ($this->adminmodel->updateClassifiedsCategory($slug, $info)) {
				$this->session->set_flashdata('message', 'Successfully Updated classifieds Category!!');
				redirect('admin/all_classifieds_category', 'refresh');
			} else {
				$this->session->set_flashdata('message', 'Sorry, Update Listing Category failed!!');
				redirect('admin/edit_classifieds_category', 'refresh');
			}
		}
	}
	/*====================
		Delete classifieds Category
		=====================*/
	public function delete_classifieds_category($slug)
	{
		if ($this->adminmodel->deleteClassifiedsCategory($slug)) {
			$this->session->set_flashdata('message', 'Category has been Permanently Deleted!!!');
			redirect('admin/all_classifieds_category', 'refresh');
		} else {
			redirect('admin/all_classifieds_category', 'refresh');
		}
	}
	/*End classifieds Category*/


	/**********************/
	/***All classifieds***/
	/********************/
	public function classifieds_list($page = 1)
	{
		$this->load->library('pagination');

		// Get total classifieds count
		$total_rows = $this->adminmodel->countAllClassifieds();

		// Pagination Configuration
		$config = [
			'base_url'         => base_url('admin/classifieds_list'),
			'total_rows'       => $total_rows,
			'per_page' => admin_per_page(20),
			'reuse_query_string' => TRUE,
			'uri_segment'      => 3,
			'use_page_numbers' => TRUE,
			'num_links'        => 3,
			'page_query_string' => FALSE,

			// Bootstrap 4 Pagination Styling
			'full_tag_open'    => '<ul class="pagination">',
			'full_tag_close'   => '</ul>',
			'first_tag_open'   => '<li class="page-item">',
			'first_tag_close'  => '</li>',
			'cur_tag_open'     => '<li class="page-item active"><a href="#" class="page-link">',
			'cur_tag_close'    => '</a></li>',
			'num_tag_open'     => '<li class="page-item">',
			'num_tag_close'    => '</li>',
			'prev_tag_open'    => '<li class="page-link">',
			'prev_tag_close'   => '</li>',
		];
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

		$page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
		$data['classifiedsList']   = $this->adminmodel->getAllClassifieds($config['per_page'], $page);

		// Collect classifieds IDs and fetch view counts
		$classifieds_ids = [];
		if (!empty($data['classifiedsList'])) {
			foreach ($data['classifiedsList'] as $c) {
				if (isset($c->classifieds_id)) $classifieds_ids[] = $c->classifieds_id;
			}
		}

		$data['views_counts'] = $this->adminmodel->getViewsForIds('classifieds', 'classifieds_id', 'views', $classifieds_ids);

		$data['start_number'] = $page + 1;
		$data['link'] 		   = $this->pagination->create_links();

		$this->load->view('admin/header');
		$this->load->view('admin/all_classifieds', $data);
		$this->load->view('admin/footer');
	}


	/* Preview classifieds */
	public function classifieds_details($slug)
	{

		$data['logged_id'] = NULL;

		if ($this->session->userdata('logged_admin')) {
			$session 		   = $this->session->userdata('logged_admin');
			$data['logged_id'] = $session['logged_id'];
		}

		$data['classifieds'] = $this->usermodel->getSingleValue($slug, 'classifieds_slug', 'classifieds');

		$this->load->view('header');
		$this->load->view('classifieds_details', $data);
		$this->load->view('footer');
	}

	/* Add new classifieds */
	public function add_classifieds()
	{
		$data['users'] = $this->adminmodel->getActiveUsersForDropdown();
		if ($this->form_validation->run('classifieds') == false) {
			$data['all_classifieds_category'] = $this->adminmodel->allclassifiedsCategory(NULL, NULL);
			$this->load->view('admin/header', $data);
			$this->load->view('admin/add_classifieds');
			$this->load->view('admin/footer');
		} else {
			$classifieds_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('classifieds_title'));
			$info = array(
				'user_id'					  => $this->input->post('user_id'),
				'classifieds_title'    		  => $this->input->post('classifieds_title'),
				'classifieds_price'    		  => $this->input->post('classifieds_price'),
				'classifieds_category_id'	  => $this->input->post('classifieds_category_id'),
				'featured_classifieds'  	  => ($this->input->post('featured_classifieds') ? 1 : 0),
				'spotlight_classifieds'  	  => ($this->input->post('spotlight_classifieds') ? 1 : 0),
				'display_contact'  	  		  => $this->input->post('display_contact'),
				'classifieds_description'	  => $this->input->post('classifieds_description'),
				'classifieds_seo_title' 	  => $this->input->post('classifieds_seo_title'),
				'classifieds_seo_description' => $this->input->post('classifieds_seo_description'),
				'classifieds_seo_keywords'    => $this->input->post('classifieds_seo_keywords'),
				'classifieds_slug'  		  => $classifieds_slug
			);

			if ($_FILES && $_FILES['classifieds_image']['name']) {
				$img_info = [
					'upload_path' => 'images/classifieds',
					'quality'     => '60%',
					'image_name'  => 'classifieds_image'
				];
				$info['classifieds_image'] = uploadAndResize($img_info);
			}

			if ($this->adminmodel->add('classifieds', $info)) {
				$this->session->set_flashdata('message', 'Successfully Added classifieds!!');
				redirect('admin/classifieds-list');
			} else {
				$this->session->set_flashdata('message', 'Adding classifieds Failed!!');
			}
		}
	}

	/* Edit classifieds */
	public function edit_classifieds($slug)
	{
		$data['users']   = $this->adminmodel->getActiveUsersForDropdown();
		$data['classifieds']    = $this->usermodel->getSingleValue($slug, 'classifieds_slug', 'classifieds');
		if (empty($data['classifieds'])) {
			$this->session->set_flashdata('error', 'Classified not found.');
			redirect('admin/classifieds-list');
			return;
		}
		$old_image       = $data['classifieds']->classifieds_image;

		if ($this->form_validation->run('classifieds') == FALSE) {
			$data['all_classifieds_category'] = $this->adminmodel->allClassifiedsCategory(NULL, NULL);
			$this->load->view('admin/header');
			$this->load->view('admin/edit_classifieds', $data);
			$this->load->view('admin/footer');
		} else {
			$classifieds_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('classifieds_title'));
			$info = array(
				'user_id'					  => $this->input->post('user_id'),
				'classifieds_title'    		  => $this->input->post('classifieds_title'),
				'classifieds_price'    		  => $this->input->post('classifieds_price'),
				'classifieds_category_id'	  => $this->input->post('classifieds_category_id'),
				'featured_classifieds'  	  => ($this->input->post('featured_classifieds') ? 1 : 0),
				'spotlight_classifieds'  	  => ($this->input->post('spotlight_classifieds') ? 1 : 0),
				'display_contact'  	  		  => $this->input->post('display_contact'),
				'classifieds_description'	  => $this->input->post('classifieds_description'),
				'classifieds_seo_title' 	  => $this->input->post('classifieds_seo_title'),
				'classifieds_seo_description' => $this->input->post('classifieds_seo_description'),
				'classifieds_seo_keywords'    => $this->input->post('classifieds_seo_keywords'),
				'classifieds_slug'  		  => $classifieds_slug
			);

			if ($_FILES && $_FILES['classifieds_image']['name']) {
				$img_info = [
					'upload_path' => 'images/classifieds',
					'quality'     => '60%',
					'image_name'  => 'classifieds_image'
				];

				$image_link = $old_image;

				if ($image_link != null) {
					if (file_exists('./' . $image_link)) {
						unlink($image_link);
					}
				}

				$info['classifieds_image'] = uploadWithoutResize($img_info);
			} else {
				$info['classifieds_image'] = $old_image;
			}

			if ($this->adminmodel->update($slug, $info, 'classifieds_slug', 'classifieds')) {
				$this->session->set_flashdata('message', 'Successfully Updated classifieds!!');
				redirect('admin/classifieds-list', 'refresh');
			} else {
				$this->session->set_flashdata('message', 'Sorry, Update classifieds Failed!!');
				redirect('admin/edit-classifieds' . $id, 'refresh');
			}
		}
	}

	/*Delete classifieds*/
	public function delete_classifieds($slug)
	{
		if ($this->adminmodel->deleteClassifieds($slug)) {
			$this->session->set_flashdata('message', 'Successfully Removed classifieds!!');
			redirect('admin/classifieds-list');
		}
	}
	/**********************/
	/***End Classifieds***/
	/********************/

	/*
			Start marketplace 
		*/
	/*====================
		All marketplace Category
		=====================*/

	public function all_marketplace_category()
	{
		$data['user'] = $this->adminmodel->getAdminInformation();
		$data['all_marketplace_category'] = $this->adminmodel->allMarketplaceCategory();

		$this->load->view('admin/header');
		$this->load->view('admin/all_marketplace_category', $data);
		$this->load->view('admin/footer');
	}
	/*====================
			Add new Category
			=====================*/

	public function add_marketplace_category()
	{
		$this->form_validation->set_rules('category_name', 'Category Name', 'trim|required|is_unique[marketplace_categories.category_name]');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/add_marketplace_category');
			$this->load->view('admin/footer');
		} else {
			$data = array();
			$category_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('category_name'));
			$data['category_name'] = $this->input->post('category_name');
			$data['category_seo_title'] = $this->input->post('category_seo_title');
			$data['category_seo_keywords'] = $this->input->post('category_seo_keywords');
			$data['category_seo_description'] = $this->input->post('category_seo_description');
			$data['category_slug'] = $category_slug;
			if ($_FILES && $_FILES['category_image']['name']) {
				$img_info = [
					'upload_path' => 'images/marketplace',
					'quality'     => '60%',
					'image_name'  => 'category_image'
				];
				$data['category_image'] = uploadAndResize($img_info);
			}
			if ($this->adminmodel->addmarketplaceCategory($data)) {
				$this->session->set_flashdata('message', 'Successfully Added Category!!');
				redirect('admin/all_marketplace_category');
			} else {
				$this->session->set_flashdata('message', 'Adding marketplace Category failed!!');
			}
		}
	}

	/*====================
			Edit Category
			=====================*/
	public function edit_marketplace_category($slug)
	{
		$data['category'] = $this->adminmodel->getSingleValue($slug, 'category_slug', 'marketplace_categories');
		if (empty($data['category'])) {
			// getSingleValue() returns NULL on a miss, and reading a property
			// off NULL is fatal in PHP 8.
			$this->session->set_flashdata('error', 'Marketplace category not found.');
			redirect('admin/all_marketplace_category');
			return;
		}
		$old_image        = $data['category']->category_image;

		$this->form_validation->set_rules('category_name', 'Category Name', 'trim|required');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/edit_marketplace_category', $data);
			$this->load->view('admin/footer');
		} else {
			$info['category_name'] = $this->input->post('category_name');
			$category_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('category_name'));
			$info['category_name'] = $this->input->post('category_name');
			$info['category_seo_title'] = $this->input->post('category_seo_title');
			$info['category_seo_keywords'] = $this->input->post('category_seo_keywords');
			$info['category_seo_description'] = $this->input->post('category_seo_description');
			$info['category_slug'] = $category_slug;

			if ($_FILES && $_FILES['category_image']['name']) {
				$img_info = [
					'upload_path' => 'images/marketplace',
					'quality'     => '60%',
					'image_name'  => 'category_image'
				];

				$image_link = $old_image;
				if ($image_link != null) {
					if (file_exists('./' . $image_link)) {
						unlink($image_link);
					}
				}

				$info['category_image'] = uploadAndResize($img_info);
			} else {
				$info['category_image'] = $old_image;
			}

			if ($this->adminmodel->updatemarketplaceCategory($slug, $info)) {
				$this->session->set_flashdata('message', 'Successfully Updated marketplace Category!!');
				redirect('admin/all_marketplace_category', 'refresh');
			} else {
				$this->session->set_flashdata('message', 'Sorry, Update marketplace Category failed!!');
				redirect('admin/edit_marketplace_category', 'refresh');
			}
		}
	}
	/*====================
			Delete marketplace Category
			=====================*/
	public function delete_marketplace_category($slug)
	{
		if ($this->adminmodel->deletemarketplaceCategory($slug)) {
			$this->session->set_flashdata('message', 'Category has been Permanently Deleted!!!');
			redirect('admin/all_marketplace_category', 'refresh');
		} else {
			redirect('admin/all_marketplace_category', 'refresh');
		}
	}
	/*End marketplace Category*/


	/**********************/
	/***All marketplace***/
	/********************/
	public function marketplace_list()
	{
		$data['marketplaceList'] = $this->adminmodel->getmarketplace();
		$this->load->view('admin/header');
		$this->load->view('admin/all_marketplace', $data);
		$this->load->view('admin/footer');
	}

	/* Preview marketplace */
	public function marketplace_details($id)
	{
		$data['blog'] = $this->usermodel->getSingleValue($id, 'marketplace_id', 'marketplace');

		$this->load->view('admin/inc/header');
		$this->load->view('admin/marketplace_details', $data);
		$this->load->view('admin/footer');
	}

	/* Add new marketplace */
	public function add_marketplace()
	{
		$data['users'] = $this->adminmodel->getActiveUsersForDropdown();
		if ($this->form_validation->run('marketplace') == false) {
			$data['all_marketplace_category'] = $this->adminmodel->allmarketplaceCategory();
			$this->load->view('admin/header', $data);
			$this->load->view('admin/add_marketplace');
			$this->load->view('admin/footer');
		} else {
			$marketplace_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('marketplace_title'));
			$info = array(
				'user_id'					  => $this->input->post('user_id'),
				'marketplace_title'    		  => $this->input->post('marketplace_title'),
				'marketplace_price'    		  => $this->input->post('marketplace_price'),
				'marketplace_category_id'	  => $this->input->post('marketplace_category_id'),
				'featured_marketplace'  	  => ($this->input->post('featured_marketplace') ? 1 : 0),
				'spotlight_marketplace'  	  => ($this->input->post('spotlight_marketplace') ? 1 : 0),
				'display_contact'  	  		  => $this->input->post('display_contact'),
				'marketplace_description'	  => $this->input->post('marketplace_description'),
				'marketplace_seo_title' 	  => $this->input->post('marketplace_seo_title'),
				'marketplace_seo_description' => $this->input->post('marketplace_seo_description'),
				'marketplace_seo_keywords'    => $this->input->post('marketplace_seo_keywords'),
				'marketplace_slug'  		  => $marketplace_slug
			);

			if ($_FILES && $_FILES['marketplace_image']['name']) {
				$img_info = [
					'upload_path' => 'images/marketplace',
					'quality'     => '60%',
					'image_name'  => 'marketplace_image'
				];
				$info['marketplace_image'] = uploadAndResize($img_info);
			}

			if ($this->adminmodel->add('marketplace', $info)) {
				$this->session->set_flashdata('message', 'Successfully Added marketplace!!');
				redirect('admin/marketplace-list');
			} else {
				$this->session->set_flashdata('message', 'Adding marketplace Failed!!');
			}
		}
	}

	/* Edit marketplace */
	public function edit_marketplace($id)
	{
		$data['users']   = $this->adminmodel->getActiveUsersForDropdown();
		$data['marketplace']    = $this->usermodel->getSingleValue($id, 'marketplace_id', 'marketplace');
		$old_image       = $data['marketplace']->marketplace_image;

		if ($this->form_validation->run('marketplace') == FALSE) {
			$data['all_marketplace_category'] = $this->adminmodel->allmarketplaceCategory();
			$this->load->view('admin/header', $data);
			$this->load->view('admin/edit_marketplace', $data);
			$this->load->view('admin/footer');
		} else {
			$marketplace_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('marketplace_title'));
			$info = array(
				'user_id'					  => $this->input->post('user_id'),
				'marketplace_title'    		  => $this->input->post('marketplace_title'),
				'marketplace_price'    		  => $this->input->post('marketplace_price'),
				'marketplace_category_id'	  => $this->input->post('marketplace_category_id'),
				'featured_marketplace'  	  => ($this->input->post('featured_marketplace') ? 1 : 0),
				'spotlight_marketplace'  	  => ($this->input->post('spotlight_marketplace') ? 1 : 0),
				'display_contact'  	  		  => $this->input->post('display_contact'),
				'marketplace_description'	  => $this->input->post('marketplace_description'),
				'marketplace_seo_title' 	  => $this->input->post('marketplace_seo_title'),
				'marketplace_seo_description' => $this->input->post('marketplace_seo_description'),
				'marketplace_seo_keywords'    => $this->input->post('marketplace_seo_keywords'),
				'marketplace_slug'  		  => $marketplace_slug
			);

			if ($_FILES && $_FILES['marketplace_image']['name']) {
				$img_info = [
					'upload_path' => 'images/marketplace',
					'quality'     => '60%',
					'image_name'  => 'marketplace_image'
				];

				$image_link = $old_image;

				if ($image_link != null) {
					if (file_exists('./' . $image_link)) {
						unlink($image_link);
					}
				}

				$info['marketplace_image'] = uploadWithoutResize($img_info);
			} else {
				$info['marketplace_image'] = $old_image;
			}

			if ($this->adminmodel->update($id, $info, 'marketplace_id', 'marketplace')) {
				$this->session->set_flashdata('message', 'Successfully Updated marketplace!!');
				redirect('admin/marketplace-list', 'refresh');
			} else {
				$this->session->set_flashdata('message', 'Sorry, Update marketplace Failed!!');
				redirect('admin/edit-marketplace' . $id, 'refresh');
			}
		}
	}

	/*Delete marketplace*/
	public function delete_marketplace($id)
	{
		if ($this->adminmodel->deletemarketplace($id)) {
			$this->session->set_flashdata('message', 'Successfully Removed marketplace!!');
			redirect('admin/marketplace-list');
		}
	}
	/**********************/
	/***End marketplace***/
	/********************/

	/*
		  Start rental 
		*/
	/*====================
		  All rental Category
		=====================*/

	public function all_rental_category($page = 1)
	{
		$this->load->library('pagination');

		// Get total rental categories count
		$total_rows = $this->adminmodel->countAllRentalCategories();

		// Pagination Configuration
		$config = [
			'base_url'         => base_url('admin/all_rental_category'),
			'total_rows'       => $total_rows,
			'per_page' => admin_per_page(20),
			'reuse_query_string' => TRUE,
			'uri_segment'      => 3,
			'use_page_numbers' => TRUE,
			'num_links'        => 3,
			'page_query_string' => FALSE,

			// Bootstrap 4 Pagination Styling
			'full_tag_open'    => '<ul class="pagination">',
			'full_tag_close'   => '</ul>',
			'first_tag_open'   => '<li class="page-item">',
			'first_tag_close'  => '</li>',
			'last_tag_open'    => '<li class="page-item">',
			'last_tag_close'   => '</li>',
			'cur_tag_open'     => '<li class="page-item active"><a href="#" class="page-link">',
			'cur_tag_close'    => '</a></li>',
			'num_tag_open'     => '<li class="page-item">',
			'num_tag_close'    => '</li>',
			'prev_tag_open'    => '<li class="page-item">',
			'prev_tag_close'   => '</li>',
		];
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

		$page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
		$data['start_number'] = $page + 1;
		$data['all_rental_category']   = $this->adminmodel->allRentalCategory($config['per_page'], $page);
		$data['link'] 		   = $this->pagination->create_links();

		//////////////////////////

		$data['user'] 				  = $this->adminmodel->getAdminInformation();

		// $data['all_listing_category'] = $this->adminmodel->allListingCategory();
		// echo "<pre>";var_dump($data['all_listing_category']);die();

		$this->load->view('admin/header');
		$this->load->view('admin/all_rental_category', $data);
		$this->load->view('admin/footer');
	}


	/*====================
		Add new Category
		=====================*/

	public function add_rental_category()
	{
		$this->form_validation->set_rules('category_name', 'Category Name', 'trim|required|is_unique[rental_categories.category_name]');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/add_rental_category');
			$this->load->view('admin/footer');
		} else {
			$data = array();
			$category_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('category_name'));
			$data['category_name'] = $this->input->post('category_name');
			$data['category_seo_title'] = $this->input->post('category_seo_title');
			$data['category_seo_keywords'] = $this->input->post('category_seo_keywords');
			$data['category_seo_description'] = $this->input->post('category_seo_description');
			$data['category_slug'] = $category_slug;
			if ($_FILES && $_FILES['category_image']['name']) {
				$img_info = [
					'upload_path' => 'images/rental',
					'quality'     => '60%',
					'image_name'  => 'category_image'
				];
				$data['category_image'] = uploadAndResize($img_info);
			}
			if ($this->adminmodel->addRentalCategory($data)) {
				$this->session->set_flashdata('message', 'Successfully Added Category!!');
				redirect('admin/all_rental_category');
			} else {
				$this->session->set_flashdata('message', 'Adding rental Category failed!!');
			}
		}
	}

	/*====================
		Edit Category
		=====================*/
	public function edit_rental_category($slug)
	{
		$data['category'] = $this->adminmodel->getSingleValue($slug, 'category_slug', 'rental_categories');
		$old_image        = $data['category']->category_image;
		$this->form_validation->set_rules('category_name', 'Category Name', 'trim|required');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/edit_rental_category', $data);
			$this->load->view('admin/footer');
		} else {
			$info['category_name'] = $this->input->post('category_name');
			$category_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('category_name'));
			$info['category_name'] = $this->input->post('category_name');
			$info['category_seo_title'] = $this->input->post('category_seo_title');
			$info['category_seo_keywords'] = $this->input->post('category_seo_keywords');
			$info['category_seo_description'] = $this->input->post('category_seo_description');
			$info['category_slug'] = $category_slug;

			if ($_FILES && $_FILES['category_image']['name']) {
				$img_info = [
					'upload_path' => 'images/rental',
					'quality'     => '60%',
					'image_name'  => 'category_image'
				];

				$image_link = $old_image;
				if ($image_link != null) {
					if (file_exists('./' . $image_link)) {
						unlink($image_link);
					}
				}


				$info['category_image'] = uploadAndResize($img_info);
			} else {
				$info['category_image'] = $old_image;
			}

			if ($this->adminmodel->updateRentalCategory($slug, $info)) {
				$this->session->set_flashdata('message', 'Successfully Updated rental Category!!');
				redirect('admin/all_rental_category', 'refresh');
			} else {
				$this->session->set_flashdata('message', 'Sorry, Update rental Category failed!!');
				redirect('admin/edit_rental_category', 'refresh');
			}
		}
	}
	/*====================
		Delete rental Category
		=====================*/
	public function delete_rental_category($slug)
	{
		if ($this->adminmodel->deleteRentalCategory($slug)) {
			$this->session->set_flashdata('message', 'Category has been Permanently Deleted!!!');
			redirect('admin/all_rental_category', 'refresh');
		} else {
			redirect('admin/all_rental_category', 'refresh');
		}
	}
	/*End rental Category*/

	/*====================
	  All Rental List
	  =====================*/
	public function rental_list($page = 1)
	{
		$this->load->library('pagination');

		// Get total rental count
		$total_rows = $this->adminmodel->countAllRentals2();

		// Pagination Configuration (match other list pages)
		$config = [
			'base_url'         => base_url('admin/rental-list'),
			'total_rows'       => $total_rows,
			'per_page' => admin_per_page(20),
			'reuse_query_string' => TRUE,
			'uri_segment'      => 3,
			'use_page_numbers' => TRUE,
			'num_links'        => 3,
			'page_query_string' => FALSE,
			// Bootstrap 4 Pagination Styling
			'full_tag_open'    => '<ul class="pagination">',
			'full_tag_close'   => '</ul>',
			'first_tag_open'   => '<li class="page-item">',
			'first_tag_close'  => '</li>',
			'last_tag_open'    => '<li class="page-item">',
			'last_tag_close'   => '</li>',
			'cur_tag_open'     => '<li class="page-item active"><a href="#" class="page-link">',
			'cur_tag_close'    => '</a></li>',
			'num_tag_open'     => '<li class="page-item">',
			'num_tag_close'    => '</li>',
			'prev_tag_open'    => '<li class="page-item">',
			'prev_tag_close'   => '</li>',
			'next_tag_open'    => '<li class="page-item">',
			'next_tag_close'   => '</li>',
			'first_link'       => 'First',
			'last_link'        => 'Last',
			'next_link'        => 'Next',
			'prev_link'        => 'Previous',
			'attributes'       => ['class' => 'page-link'],
		];

		$this->pagination->initialize($config);

		// Calculate offset using page numbers
		$page = ($page < 1) ? 1 : $page;
		$offset = ($page - 1) * $config['per_page'];

		$data['start_number'] = $offset + 1;
		$data['rentalList'] = $this->adminmodel->getRentals($config['per_page'], $offset);

		// Collect rental IDs and fetch view counts
		$rental_ids = [];
		if (!empty($data['rentalList'])) {
			foreach ($data['rentalList'] as $r) {
				if (isset($r->rental_id)) $rental_ids[] = $r->rental_id;
			}
		}

		$data['views_counts'] = $this->adminmodel->getViewsForIds('rental', 'rental_id', 'views', $rental_ids);

		$data['link'] = $this->pagination->create_links();

		$this->load->view('admin/header');
		$this->load->view('admin/all_rental', $data);
		$this->load->view('admin/footer');
	}

	/**
	 * Render Add Rental form and handle submission
	 */
	public function add_rental()
	{
		$data['users'] = $this->adminmodel->getActiveUsersForDropdown();
		$data['all_rental_category'] = $this->adminmodel->getAllValues('rental_categories');

		if ($this->input->server('REQUEST_METHOD') === 'POST') {
			// Collect fields from the form (simple, minimal validation)
			$info = [];
			$info['user_id'] = $this->input->post('user_id');
			$info['rental_title'] = $this->input->post('rental_title');
			$info['rental_price'] = $this->input->post('rental_price');
			$info['rental_category_id'] = $this->input->post('rental_category_id');
			$info['featured_rental'] = $this->input->post('featured_rental') ? 1 : 0;
			$info['spotlight_rental'] = $this->input->post('spotlight_rental') ? 1 : 0;
			$info['display_contact'] = $this->input->post('display_contact') ? 1 : 0;
			$info['rental_address'] = $this->input->post('rental_address');
			$info['rental_description'] = $this->input->post('rental_description');
			$info['bedrooms'] = $this->input->post('bedrooms');
			$info['living_area'] = $this->input->post('living_area');
			$info['bathroom'] = $this->input->post('bathroom');
			$info['property_age'] = $this->input->post('property_age');
			$info['garage'] = $this->input->post('garage');
			$info['floors'] = $this->input->post('floors');
			$info['rent_to_own'] = $this->input->post('rent_to_own') ? 1 : 0;
			$info['can_sublet'] = $this->input->post('can_sublet') ? 1 : 0;
			$info['rental_seo_title'] = $this->input->post('rental_seo_title');
			$info['rental_seo_keywords'] = $this->input->post('rental_seo_keywords');
			$info['rental_seo_description'] = $this->input->post('rental_seo_description');
			$info['rental_slug'] = preg_replace('/[^A-Za-z0-9-]+/', '-', $info['rental_title']);
			$info['rental_status'] = 1;

			// Handle image upload if provided
			if ($_FILES && isset($_FILES['rental_image']) && $_FILES['rental_image']['name']) {
				$img_info = [
					'upload_path' => 'images/rental',
					'quality' => '60%',
					'image_name' => 'rental_image'
				];
				if (function_exists('uploadAndResize')) {
					$info['rental_image'] = uploadAndResize($img_info);
				} else {
					$config['upload_path'] = 'images/rental';
					$config['allowed_types'] = 'jpg|jpeg|png|gif';
					$config['max_size'] = 2048;
					$this->load->library('upload', $config);
					if ($this->upload->do_upload('rental_image')) {
						$upload_data = $this->upload->data();
						$info['rental_image'] = 'images/rental/' . $upload_data['file_name'];
					} else {
						$info['rental_image'] = '';
					}
				}
			} else {
				$info['rental_image'] = '';
			}

			if ($this->adminmodel->add('rental', $info)) {
				$this->session->set_flashdata('message', 'Rental added successfully!');
				redirect('admin/rental-list');
			} else {
				$this->session->set_flashdata('message', 'Failed to add rental!');
				redirect('admin/add-rental');
			}
			return;
		}

		$this->load->view('admin/header');
		$this->load->view('admin/add_rental', $data);
		$this->load->view('admin/footer');
	}

	/**
	 * Edit rental by slug
	 */
	public function edit_rental($slug)
	{
		$data['rental'] = $this->adminmodel->getSingleValue($slug, 'rental_slug', 'rental');
		if (empty($data['rental'])) {
			$this->session->set_flashdata('message', 'Invalid rental');
			redirect('admin/rental-list');
			return;
		}

		$data['users'] = $this->adminmodel->getActiveUsersForDropdown();
		$data['all_rental_category'] = $this->adminmodel->getAllValues('rental_categories');

		if ($this->input->server('REQUEST_METHOD') === 'POST') {
			$info = [];
			$info['user_id'] = $this->input->post('user_id');
			$info['rental_title'] = $this->input->post('rental_title');
			$info['rental_price'] = $this->input->post('rental_price');
			$info['rental_category_id'] = $this->input->post('rental_category_id');
			$info['featured_rental'] = $this->input->post('featured_rental') ? 1 : 0;
			$info['spotlight_rental'] = $this->input->post('spotlight_rental') ? 1 : 0;
			$info['display_contact'] = $this->input->post('display_contact') ? 1 : 0;
			$info['rental_address'] = $this->input->post('rental_address');
			$info['rental_description'] = $this->input->post('rental_description');
			$info['bedrooms'] = $this->input->post('bedrooms');
			$info['living_area'] = $this->input->post('living_area');
			$info['bathroom'] = $this->input->post('bathroom');
			$info['property_age'] = $this->input->post('property_age');
			$info['garage'] = $this->input->post('garage');
			$info['floors'] = $this->input->post('floors');
			$info['rent_to_own'] = $this->input->post('rent_to_own') ? 1 : 0;
			$info['can_sublet'] = $this->input->post('can_sublet') ? 1 : 0;
			$info['rental_seo_title'] = $this->input->post('rental_seo_title');
			$info['rental_seo_keywords'] = $this->input->post('rental_seo_keywords');
			$info['rental_seo_description'] = $this->input->post('rental_seo_description');
			$info['rental_slug'] = preg_replace('/[^A-Za-z0-9-]+/', '-', $info['rental_title']);

			// Handle image upload
			$old_image = isset($data['rental']->rental_image) ? $data['rental']->rental_image : '';
			if ($_FILES && isset($_FILES['rental_image']) && $_FILES['rental_image']['name']) {
				$img_info = [
					'upload_path' => 'images/rental',
					'quality' => '60%',
					'image_name' => 'rental_image'
				];
				// delete old image if exists
				if ($old_image && file_exists('./' . $old_image)) {
					@unlink('./' . $old_image);
				}
				if (function_exists('uploadAndResize')) {
					$info['rental_image'] = uploadAndResize($img_info);
				} else {
					$config['upload_path'] = 'images/rental';
					$config['allowed_types'] = 'jpg|jpeg|png|gif';
					$config['max_size'] = 2048;
					$this->load->library('upload', $config);
					if ($this->upload->do_upload('rental_image')) {
						$upload_data = $this->upload->data();
						$info['rental_image'] = 'images/rental/' . $upload_data['file_name'];
					} else {
						$info['rental_image'] = $old_image;
					}
				}
			} else {
				$info['rental_image'] = $old_image;
			}

			if ($this->adminmodel->update($slug, $info, 'rental_slug', 'rental')) {
				$this->session->set_flashdata('message', 'Rental updated successfully!');
				redirect('admin/rental-list');
			} else {
				$this->session->set_flashdata('message', 'Failed to update rental!');
				redirect('admin/edit-rental/' . $slug);
			}
			return;
		}

		$this->load->view('admin/header');
		$this->load->view('admin/edit_rental', $data);
		$this->load->view('admin/footer');
	}

	/**
	 * Delete rental by slug
	 */
	public function delete_rental($slug)
	{
		if ($this->adminmodel->deleteRental($slug)) {
			$this->session->set_flashdata('message', 'Rental has been Permanently Deleted!!!');
		} else {
			$this->session->set_flashdata('message', 'Rental Delete Failed!');
		}
		redirect('admin/rental-list', 'refresh');
	}

	/**
	 * Admin preview of rental (opens in new tab)
	 */
	public function rental_details($slug)
	{

		// Load rental record
		$rental = $this->adminmodel->getSingleValue($slug, 'rental_slug', 'rental');
		if (empty($rental)) {
			$this->session->set_flashdata('message', 'Invalid rental');
			redirect('admin/rental-list');
			return;
		}

		// Prepare view data using the public view variable name ($rentals) so the template works
		$data = [];
		$data['rentals'] = $rental; // public view expects $rentals

		// Logged-in user id (used by enquiry form)
		$data['logged_id'] = NULL;
		if ($this->session->userdata('logged_admin')) {
			$session = $this->session->userdata('logged_admin');
			$data['logged_id'] = isset($session['logged_id']) ? $session['logged_id'] : NULL;
		}

		$this->load->view('header');
		// Try to load front-end rental details view if present; fallback to admin preview if public view missing
		if (file_exists(APPPATH . 'views/rental_details.php')) {
			$this->load->view('rental_details', $data);
		} elseif (file_exists(APPPATH . 'views/admin/rental_details.php')) {
			// if there's an admin-specific detail view, pass it the same data
			$this->load->view('admin/rental_details', $data);
		} else {
			$this->load->view('admin/rental_preview', $data);
		}
		$this->load->view('footer');
	}


	/*=====================
	      EVENTS
	      =====================*/

	/*All events*/
	public function all_events($page = 1)
	{
		$this->load->library('pagination');

		// Get total event count
		$total_rows = $this->adminmodel->countAllEvents();

		// Pagination Configuration
		$config = [
			'base_url'         => base_url('admin/all_events'),
			'total_rows'       => $total_rows,
			'per_page' => admin_per_page(20),
			'reuse_query_string' => TRUE,
			'uri_segment'      => 3,
			'use_page_numbers' => TRUE,
			'num_links'        => 3,
			'page_query_string' => FALSE,

			// Bootstrap 4 Pagination Styling
			'full_tag_open'    => '<ul class="pagination">',
			'full_tag_close'   => '</ul>',
			'first_tag_open'   => '<li class="page-item">',
			'first_tag_close'  => '</li>',
			'last_tag_open'    => '<li class="page-item">',
			'last_tag_close'   => '</li>',
			'cur_tag_open'     => '<li class="page-item active"><a href="#" class="page-link">',
			'cur_tag_close'    => '</a></li>',
			'num_tag_open'     => '<li class="page-item">',
			'num_tag_close'    => '</li>',
			'prev_tag_open'    => '<li class="page-item">',
			'prev_tag_close'   => '</li>',
			'next_tag_open'    => '<li class="page-item">',
			'next_tag_close'   => '</li>',
			'first_link'       => 'First',
			'last_link'        => 'Last',
			'next_link'        => 'Next',
			'prev_link'        => 'Previous',
			'attributes'       => ['class' => 'page-link'],
		];

		$this->pagination->initialize($config);

		// Calculate offset
		$page = ($page < 1) ? 1 : $page;
		$offset = ($page - 1) * $config['per_page'];
		$data['start_number'] = ($page - 1) * $config['per_page'] + 1;

		// Fetch paginated records
		$data['allEvents'] = $this->adminmodel->get_events($config['per_page'], $offset);

		// Collect event IDs and fetch their view counts
		$event_ids = [];
		if (!empty($data['allEvents'])) {
			foreach ($data['allEvents'] as $ev) {
				if (isset($ev->event_id)) $event_ids[] = $ev->event_id;
			}
		}

		$data['views_counts'] = $this->adminmodel->getViewsForIds('events', 'event_id', 'views', $event_ids);

		$data['link'] = $this->pagination->create_links();

		$this->load->view('admin/header');
		$this->load->view('admin/all_event', $data);
		$this->load->view('admin/footer');
	}


	/* Preview Event */
	public function event_details($slug)
	{

		$data['logged_id'] = NULL;

		if ($this->session->userdata('logged_admin')) {
			$session 		   = $this->session->userdata('logged_admin');
			$data['logged_id'] = $session['logged_id'];
		}

		$data['event'] = $this->adminmodel->getSingleValue($slug, 'event_slug', 'events');

		$this->load->view('header');
		$this->load->view('event_details', $data);
		$this->load->view('footer');
	}

	/* Add new Event */
	public function add_new_event()
	{
		// Host dropdown lists admin (staff) accounts only, so it shows our own
		// recognisable names instead of public users.
		$data['admins'] = $this->adminmodel->getActiveAdminsForDropdown();

		if ($this->form_validation->run('admin_event_rules') == false) {
			$this->load->view('admin/header');
			$this->load->view('admin/add_new_event', $data);
			$this->load->view('admin/footer');
		} else {
			$event_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('event_name'));

			$info = array(
				'user_id'           => 0, // events are hosted by an admin now (see admin_id)
				'admin_id'          => $this->input->post('admin_id'),
				'event_name'        => $this->input->post('event_name'),
				'event_address'     => $this->input->post('event_address'),
				'event_start_date'  => $this->input->post('event_start_date'),
				'event_time'        => $this->input->post('event_time'),
				'event_description' => $this->input->post('event_description'),
				'event_map'         => $this->input->post('event_map'),
				'event_contact_name' => $this->input->post('event_contact_name'),
				'event_mobile'      => $this->input->post('event_mobile'),
				'event_email'       => $this->input->post('event_email'),
				'event_website'     => $this->input->post('event_website'),
				'isenquiry'         => ($this->input->post('isenquiry') ? 1 : 0),
				'event_status'      => 1,
				'event_slug'		=> $event_slug
			);

			if ($_FILES && $_FILES['event_image']['name']) {
				$img_info = [
					'upload_path' => 'images/events',
					'quality'     => '60%',
					'height'      => 400,
					'width'       => 1000,
					'image_name'  => 'event_image'
				];
				$info['event_image'] = uploadAndResize($img_info);
			}

			if ($this->adminmodel->addEvent($info)) {
				$this->session->set_flashdata('message', 'Successfully Added Event!!');
				redirect('admin/all-events');
			} else {
				$this->session->set_flashdata('error', 'Could not save the event. ' . $this->adminmodel->lastDbError());
				redirect('admin/add-new-event');
			}
		}
	}

	/* Edit User Event */
	public function edit_event($slug)
	{
		$data['event']    = $this->adminmodel->getSingleValue($slug, 'event_slug', 'events');
		if (empty($data['event'])) {
			$this->session->set_flashdata('error', 'Event not found.');
			redirect('admin/all-events');
			return;
		}
		$data['admins']   = $this->adminmodel->getActiveAdminsForDropdown();
		$old_image        = $data['event']->event_image;

		if ($this->form_validation->run('event_rules') == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/edit_event', $data);
			$this->load->view('admin/footer');
		} else {
			$event_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('event_name'));
			$info = array(
				'admin_id'          => $this->input->post('admin_id'),
				'event_name'        => $this->input->post('event_name'),
				'event_address'     => $this->input->post('event_address'),
				'event_start_date'  => $this->input->post('event_start_date'),
				'event_time'        => $this->input->post('event_time'),
				'event_description' => $this->input->post('event_description'),
				'event_map'         => $this->input->post('event_map'),
				'event_contact_name' => $this->input->post('event_contact_name'),
				'event_mobile'      => $this->input->post('event_mobile'),
				'event_email'       => $this->input->post('event_email'),
				'event_website'     => $this->input->post('event_website'),
				'isenquiry'         => $this->input->post('isenquiry') == 'on' ? 1 : 0,
				'event_slug'		=> $event_slug
			);
			// );

			if ($_FILES && $_FILES['event_image']['name']) {
				$img_info = [
					'upload_path' => 'images/events',
					'quality'     => '60%',
					'height'      => 400,
					'width'       => 1000,
					'image_name'  => 'event_image'
				];

				$image_link = $old_image;

				if ($image_link != null) {
					if (file_exists('./' . $image_link)) {
						unlink($image_link);
					}
				}

				$info['event_image'] = uploadAndResize($img_info);
			} else {
				$info['event_image'] = $old_image;
			}

			if ($this->adminmodel->update($slug, $info, 'event_slug', 'events')) {
				$this->session->set_flashdata('message', 'Successfully Updated Event!!');
				redirect('admin/all-events', 'refresh');
			} else {
				$this->session->set_flashdata('message', 'Sorry, Update Event Failed!!');
				redirect('admin/edit_event', 'refresh');
			}
		}
	}

	/*Delete events*/
	public function delete_event($id)
	{
		// Fetch the event to check if it exists
		$event = $this->adminmodel->getSingleValue($id, 'event_id', 'events');

		if (!$event) {
			$this->session->set_flashdata('message', 'Event not found!');
			redirect('admin/all-events');
			return;
		}

		// Delete the event permanently
		$this->db->where('event_id', $id);
		$delete = $this->db->delete('events');

		if ($delete) {
			$this->session->set_flashdata('message', 'Event successfully deleted!');
		} else {
			$this->session->set_flashdata('message', 'Failed to delete the event!');
		}

		redirect('admin/all-events');
	}

	/*====================
	All Slider
	=====================*/
	public function slider_all()
	{
		$data['user'] = $this->adminmodel->getAdminInformation();
		$data['all_slider'] = $this->adminmodel->allSlider();
		$this->load->view('admin/header');
		$this->load->view('admin/slider_all', $data);
		$this->load->view('admin/footer');
	}
	/*====================
	Add Slider
	=====================*/

	public function slider_create()
	{
		$this->form_validation->set_rules('slider_link', 'Slider Link', 'trim');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/slider_create');
			$this->load->view('admin/footer');
		} else {
			$data = array();
			$data['slider_link'] = $this->input->post('slider_link');

			if ($_FILES && $_FILES['slider_photo']['name']) {
				$img_info = [
					'upload_path' => 'images/slider',
					'height'      => 160,
					'width'       => 960,
					'image_name'  => 'slider_photo'
				];
				$data['slider_photo'] = uploadAndResize($img_info);
			}

			if ($this->adminmodel->addSlider($data)) {
				$this->session->set_flashdata('message', 'Successfully Added New Slider!!');
				redirect('admin/slider_all');
			} else {
				$this->session->set_flashdata('message', 'Add New Slider failed!!');
			}
		}
	}
	/*====================
	Edit Slider
	=====================*/
	public function slider_edit($id)
	{
		$data['slider'] = $this->adminmodel->getsliderByid($id);
		$old_image        = $data['slider']->slider_photo;

		$this->form_validation->set_rules('slider_link', 'Slider Link', 'trim');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/slider_edit', $data);
			$this->load->view('admin/footer');
		} else {
			$info['slider_link'] = $this->input->post('slider_link');

			if ($_FILES && $_FILES['slider_photo']['name']) {
				$img_info = [
					'upload_path' => 'images/slider',
					'image_name'  => 'slider_photo',
					'quality'     => '60%',
					'width'       => 1350,
					'height'      => 325
				];

				$image_link = './' . $old_image;
				if (file_exists($image_link)) {
					unlink($image_link);
				}

				/*$tempPath=$_FILES["slider_photo"]["tmp_name"];
                $uploadTo = "./assets/images/slider/"; 
                 $imageName = $_FILES['slider_photo']['name'];
                    $basename = basename($imageName);
                    $originalPath = $uploadTo.$basename; 
                 $imageQuality= 60;
                 $info['slider_photo'] = compress_image($tempPath, $originalPath, $imageQuality);*/
				$info['slider_photo'] = uploadAndResize($img_info);
			} else {
				$info['slider_photo'] = $old_image;
			}

			if ($this->adminmodel->updateSlider($id, $info)) {
				$this->session->set_flashdata('message', 'Successfully Updated Slider!!');
				redirect('admin/slider_all', 'refresh');
			} else {
				$this->session->set_flashdata('message', 'Sorry, Slider Update failed!!');
				redirect('admin/slider_edit', 'refresh');
			}
		}
	}

	/*====================
	Delete Slider
	=====================*/
	public function slider_delete($id)
	{
		if ($this->adminmodel->deleteSlider($id)) {
			$this->session->set_flashdata('message', 'Slider has been Permanently Deleted!!!');
			redirect('admin/slider_all', 'refresh');
		} else {
			redirect('admin/slider_all', 'refresh');
		}
	}


	/*==============
		 ENQUIRY
		 ==============*/

	/**
	 * Every enquiry, with a tab per kind.
	 *
	 * `?type=package` is the one that matters: a callback request from the
	 * packages panel on /pricing-plan is a sales lead, not a website question,
	 * and until migration 0026 both were written as `contact` and shown in one
	 * undifferentiated list. See User::contact_submit().
	 */
	public function enquiries()
	{
		$data['logged_id'] = NULL;
		$session 		   = $this->session->userdata('logged_admin');
		$data['logged_id'] = $session['logged_id'];

		$counts = $this->adminmodel->getEnquiryTypeCounts();

		// Whitelisted against the types actually present, so the filter can
		// only ever narrow to something real.
		$type = trim((string) $this->input->get('type'));
		if ($type !== '' && !isset($counts[$type])) $type = '';

		$data['enquiry_type']   = $type;
		$data['enquiry_counts'] = $counts;
		$data['enquiries']      = $this->adminmodel->getEnquiries($type);

		$this->load->view('admin/header');
		// $this->load->view('admin/inc/left_sidebar');
		$this->load->view('admin/all_enquiry', $data);
		// $this->load->view('user_right_sidebar');
		$this->load->view('admin/footer');
	}

	public function saved_enquiry()
	{
		$data['logged_id'] = NULL;
		$session 		   = $this->session->userdata('logged_admin');
		$data['logged_id'] = $session['logged_id'];

		$data['enquiries'] = $this->adminmodel->savedEnquiry();

		$this->load->view('admin/header');
		// $this->load->view('admin/inc/left_sidebar');
		$this->load->view('admin/saved_enquiries', $data);
		// $this->load->view('user_right_sidebar');
		$this->load->view('admin/footer');
	}

	public function delete_enquiry($id)
	{
		if ($this->adminmodel->deleteEnquiry($id)) {
			redirect('admin/enquiries', 'refresh');
		} else {
			redirect('admin/enquiries', 'refresh');
		}
	}

	public function delete_saved_enquiry($id)
	{
		if ($this->adminmodel->deleteEnquiry($id)) {
			redirect('admin/saved-enquiry', 'refresh');
		} else {
			redirect('admin/saved-enquiry', 'refresh');
		}
	}

	/*==========================================
		ACTIVE/INACTIVE STATUS UPDATE FUNCTION
		==========================================

		Toggles a single status column on a single resource row from
		the admin's list pages.

		SECURITY NOTE
		Previously this method took tableName + fieldName + value
		directly from POST and ran an arbitrary UPDATE. Any caller
		could rewrite any column in any table (e.g.
		admin.admin_password). The current implementation strictly
		whitelists every (table, id-column, status-column) tuple the
		admin UI legitimately needs to flip, and accepts only
		integer 0/1 as the new value.
		==========================================*/

	public function update_status()
	{
		// (table => [ id_column, status_column ])
		$allowed = array(
			'listings'       => array('listing_id',       'listing_status'),
			'news'           => array('news_id',          'news_status'),
			'blogs'          => array('blog_id',          'blog_status'),
			'events'         => array('event_id',         'event_status'),
			'video'          => array('video_id',         'video_status'),
			'classifieds'    => array('classifieds_id',   'classifieds_status'),
			'products'       => array('product_id',       'product_status'),
			'marketplace'    => array('marketplace_id',   'marketplace_status'),
			'house'          => array('house_id',         'house_status'),
			'rental'         => array('rental_id',        'rental_status'),
			'enquiries'      => array('enquiry_id',       'enquiry_save'),
			'claim_listings' => array('claim_listing_id', 'claim_status'),
		);

		$tableName = (string) $this->input->post('tableName');
		$tableID   = (string) $this->input->post('tableIdName');
		$fieldName = (string) $this->input->post('fieldName');
		$id        = $this->input->post('id');
		$value     = $this->input->post('value');

		// Validate the (table, id-column, status-column) tuple.
		if (!isset($allowed[$tableName])
			|| $allowed[$tableName][0] !== $tableID
			|| $allowed[$tableName][1] !== $fieldName) {
			$this->output->set_status_header(400);
			echo json_encode(array('message' => 'error', 'reason' => 'invalid target'));
			return;
		}

		// Only allow integer 0 / 1 as the new status value.
		if (!is_numeric($id) || !in_array((int) $value, array(0, 1), true)) {
			$this->output->set_status_header(400);
			echo json_encode(array('message' => 'error', 'reason' => 'invalid value'));
			return;
		}

		$ok = $this->db
			->where($tableID, (int) $id)
			->update($tableName, array($fieldName => (int) $value));

		if ($ok) {
			$this->session->set_flashdata('message', 'Status Updated Successfully!');
			echo json_encode(array('message' => 'success'));
		} else {
			echo json_encode(array('message' => 'error', 'reason' => 'db error'));
		}
	}


	public function claimed_listings()
	{
		$data['logged_id'] = NULL;
		$session 		   = $this->session->userdata('logged_admin');
		$data['logged_id'] = $session['logged_id'];

		$data['listing_requests'] = $this->usermodel->getAllValues('claim_listings');

		$this->load->view('admin/header');
		// $this->load->view('admin/inc/left_sidebar');
		$this->load->view('admin/claim_listing', $data);
		// $this->load->view('user_right_sidebar');
		$this->load->view('admin/footer');
	}

	/**
	 * Approve a pending listing claim.
	 *
	 * Old behavior tried to overwrite the listing row with the claim's
	 * full-row data but malformed the array (`$getSingleListing[0]`
	 * was never populated), so listing.user_id was never actually
	 * updated and the claim flow silently no-op'd on the data side.
	 * This rewrite:
	 *   1) Marks the claim row approved.
	 *   2) Updates ONLY listings.user_id to the claimant's user_id.
	 *   3) Emails the claimant their approval notice.
	 * All steps run inside a transaction.
	 */
	public function claim_approve($id)
	{
		$claim = $this->db
			->where('claim_listing_id', (int) $id)
			->limit(1)
			->get('claim_listings')
			->row();
		if (!$claim) {
			$this->session->set_flashdata('message', 'Claim not found.');
			redirect('admin/claimed-listings');
			return;
		}

		$listing = $this->db
			->where('listing_id', (int) $claim->listing_id)
			->limit(1)
			->get('listings')
			->row();
		if (!$listing) {
			$this->session->set_flashdata('message', 'Underlying listing no longer exists.');
			redirect('admin/claimed-listings');
			return;
		}

		$this->db->trans_start();
		$this->db
			->where('claim_listing_id', (int) $id)
			->update('claim_listings', array('claim_status' => 1));
		$this->db
			->where('listing_id', (int) $listing->listing_id)
			->update('listings', array('user_id' => (int) $claim->enquiry_sender_id));
		$this->db->trans_complete();

		if (!$this->db->trans_status()) {
			$this->session->set_flashdata('message', 'Could not approve claim.');
			redirect('admin/claimed-listings');
			return;
		}

		// The business's enquiries go with it — the ones sent before the claim
		// were addressed to the previous owner (usually a data-entry login) and
		// stayed on the admin side only.
		$moved = $this->usermodel->moveListingEnquiries(
			(int) $listing->listing_id,
			(int) $claim->enquiry_sender_id,
			(int) $listing->user_id,
			(string) $listing->listing_name
		);

		// Notify the claimant.
		if (!empty($claim->enquiry_email)) {
			$this->load->library('mailer');
			$listing_url = base_url('listing-details/' . rawurlencode((string) $listing->listing_slug));
			$body = '<p>Good news &mdash; your claim for <strong>'
				. html_escape($listing->listing_name) . '</strong> has been approved.</p>'
				. '<p>The listing is now linked to your account. Sign in and visit '
				. '<a href="' . base_url('user/dashboard') . '">your dashboard</a> '
				. 'to edit photos, hours, and contact information.</p>'
				. '<p>Enquiries and quote requests for this business now come to you: they are on your dashboard and at '
				. '<a href="' . base_url('user/enquiries') . '">Lead enquiry</a>'
				. ($moved > 0 ? ', including the ' . (int) $moved . ' sent before your claim was approved' : '')
				. ', and new ones are emailed to you.</p>'
				. '<p>Public page: <a href="' . $listing_url . '">' . $listing_url . '</a></p>';
			$this->mailer->send($claim->enquiry_email, 'Your listing claim was approved', $body);
		}

		$this->session->set_flashdata('message', 'Claim approved and listing transferred.'
			. ($moved > 0 ? ' ' . (int) $moved . ' earlier enquir' . ($moved === 1 ? 'y' : 'ies') . ' moved to the new owner.' : ''));
		redirect('admin/claimed-listings');
	}

	/**
	 * Reject a pending claim without deleting the row (so the audit
	 * trail survives). Stores claim_status = 2 to distinguish from
	 * pending (0) and approved (1).
	 */
	public function claim_reject($id)
	{
		$claim = $this->db
			->where('claim_listing_id', (int) $id)
			->limit(1)
			->get('claim_listings')
			->row();
		if (!$claim) {
			$this->session->set_flashdata('message', 'Claim not found.');
			redirect('admin/claimed-listings');
			return;
		}

		$this->db
			->where('claim_listing_id', (int) $id)
			->update('claim_listings', array('claim_status' => 2));

		if (!empty($claim->enquiry_email)) {
			$this->load->library('mailer');
			$body = '<p>We reviewed your claim for <strong>'
				. html_escape($claim->enquiry_source) . '</strong> and were '
				. 'unable to verify it at this time.</p>'
				. '<p>If you believe this was an error, reply to this email with '
				. 'additional proof of ownership (current website, license, etc.) '
				. 'and we will take another look.</p>';
			$this->mailer->send($claim->enquiry_email, 'Update on your listing claim', $body);
		}

		$this->session->set_flashdata('message', 'Claim rejected and claimant notified.');
		redirect('admin/claimed-listings');
	}

	public function delete_claim($id)
	{
		if ($this->adminmodel->deleteClaim($id)) {
			$this->session->set_flashdata('message', 'Claim has been Permanently Deleted!!!');
			redirect('admin/claimed-listings', 'refresh');
		} else {
			$this->session->set_flashdata('message', 'Claim Delete Failed!');
			redirect('admin/claimed-listings', 'refresh');
		}
	}



	/*================================
		ALL ADS FUNCTION
		================================*/
	public function current_ads()
	{
		$data['logged_id'] 	= NULL;
		$session  			= $this->session->userdata('logged_admin');
		$data['logged_id'] 	= $session['logged_id'];

		$data['ads'] 		= $this->adminmodel->getAllAds();

		$this->load->view('admin/header', $data);
		$this->load->view('admin/current_ads');
		$this->load->view('admin/footer');
	}
	/* Ads Add*/
	public function create_ads()
	{
		$data['logged_id'] 	= NULL;
		$session  			= $this->session->userdata('logged_admin');
		$data['logged_id'] 	= $session['logged_id'];

		$data['users'] 		= $this->adminmodel->getAllValues('user');
		$data['adsPrices'] 	= $this->adminmodel->allAdsPrices();
		// Category targeting (migration 0024): an ad may be booked for one
		// listing category, or left open to run in its slot everywhere.
		$data['adCategories'] = $this->usermodel->getAllValues('listing_categories');

		$this->form_validation->set_rules('user_id', 'User', 'trim|required');
		$this->form_validation->set_rules('ads_price_id', 'Ads Position', 'trim|required');
		$this->form_validation->set_rules('ad_start_date', 'Start Date', 'trim|required');
		$this->form_validation->set_rules('ad_end_date', 'End Date', 'trim|required');
		$this->form_validation->set_rules('ad_link', 'Ads Link', 'trim|required');
		$this->form_validation->set_rules('ad_total_days', 'Total Days', 'trim|required');
		$this->form_validation->set_rules('ad_cost_per_day', 'Cost Per Day', 'trim|required');
		$this->form_validation->set_rules('ad_total_cost', 'Total Cost', 'trim|required');

		if ($this->form_validation->run() == false) {
			$this->load->view('admin/header', $data);
			$this->load->view('admin/create_ads');
			$this->load->view('admin/footer');
		} else {
			// Blank means "run everywhere in this slot", which is how every ad
			// booked before 0024 behaves.
			$ad_category = $this->input->post('category_id');
			$ad_category = ($ad_category === NULL || $ad_category === '') ? NULL : (int) $ad_category;

			$info = array(
				'u_id'            => $this->input->post('user_id'),
				'ads_price_id'    => $this->input->post('ads_price_id'),
				'category_id'     => $ad_category,
				'ad_start_date'   => date('Y-m-d 00:00:00', strtotime($this->input->post('ad_start_date'))),
				'ad_end_date'     => date('Y-m-d 23:59:59', strtotime($this->input->post('ad_end_date'))), // whole days: 'h' made these noon
				'ad_link'         => $this->input->post('ad_link'),
				'ad_total_days'   => $this->input->post('ad_total_days'),
				'ad_cost_per_day' => $this->input->post('ad_cost_per_day'),
				'ad_total_cost'   => $this->input->post('ad_total_cost')
			);

			if ($_FILES && $_FILES['ad_photo']['name']) {
				$img_info = [
					'upload_path' => 'images/ads/main',
					'quality'     => '60%',
					'image_name'  => 'ad_photo'
				];
				$info['ad_photo'] = uploadAndResize($img_info);
			}

			if ($this->adminmodel->add('ads', $info)) {
				$this->session->set_flashdata('message', 'Ads Added Successfully!!');
				redirect('admin/current-ads');
			} else {
				$this->session->set_flashdata('message', 'Failed to add Ads!!');
			}
		}
	}

	/* Ads edit*/
	public function ads_edit($id)
	{
		$data['logged_id'] 	= NULL;
		$session  			= $this->session->userdata('logged_admin');
		$data['logged_id'] 	= $session['logged_id'];

		$data['users'] 		= $this->adminmodel->getAllValues('user');
		$data['adsPrices'] 	= $this->adminmodel->allAdsPrices();
		$data['ad']			= $this->adminmodel->getSingleValue($id, 'id', 'ads');
		// Category targeting was added to the booking form but never to this
		// one, so a targeted ad could be created and then never changed.
		$data['adCategories'] = $this->usermodel->getAllValues('listing_categories');
		$old_image        	= $data['ad']->ad_photo;

		$this->form_validation->set_rules('user_id', 'User', 'trim|required');
		$this->form_validation->set_rules('ads_price_id', 'Ads Position', 'trim|required');
		$this->form_validation->set_rules('ad_start_date', 'Start Date', 'trim|required');
		$this->form_validation->set_rules('ad_end_date', 'End Date', 'trim|required');
		$this->form_validation->set_rules('ad_link', 'Ads Link', 'trim|required');
		$this->form_validation->set_rules('ad_total_days', 'Total Days', 'trim|required');
		$this->form_validation->set_rules('ad_cost_per_day', 'Cost Per Day', 'trim|required');
		$this->form_validation->set_rules('ad_total_cost', 'Total Cost', 'trim|required');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('admin/header', $data);
			$this->load->view('admin/ads_edit');
			$this->load->view('admin/footer');
		} else {
			$info = array(
				'u_id'            => $this->input->post('user_id'),
				'ads_price_id'    => $this->input->post('ads_price_id'),
				'ad_start_date'   => date('Y-m-d 00:00:00', strtotime($this->input->post('ad_start_date'))),
				'ad_end_date'     => date('Y-m-d 23:59:59', strtotime($this->input->post('ad_end_date'))), // whole days: 'h' made these noon
				'ad_link'         => $this->input->post('ad_link'),
				'ad_total_days'   => $this->input->post('ad_total_days'),
				'ad_cost_per_day' => $this->input->post('ad_cost_per_day'),
				'ad_total_cost'   => $this->input->post('ad_total_cost')
			);

			// Same normalisation as create_ads(): '' means site-wide, stored as
			// NULL. This column is now owned by the form, so the matching
			// <select> in views/admin/ads_edit.php is not optional — without it
			// every re-save would silently retarget a paid category booking to
			// run site-wide.
			$ad_category = $this->input->post('category_id');
			$info['category_id'] = ($ad_category === NULL || $ad_category === '')
				? NULL
				: (int) $ad_category;

			if ($_FILES && $_FILES['ad_photo']['name']) {
				$img_info = [
					'upload_path' => 'images/ads/main',
					'image_name'  => 'ad_photo',
					'quality'     => '60%',
					'height'      => 1350,
					'width'       => 325
				];

				$image_link = './' . $old_image;
				if (file_exists($image_link)) {
					unlink($image_link);
				}

				$info['ad_photo'] = uploadAndResize($img_info);
			} else {
				$info['ad_photo'] = $old_image;
			}

			if ($this->adminmodel->update($id, $info, 'id', 'ads')) {
				$this->session->set_flashdata('message', 'Ads Updated Successfully!!');
				redirect('admin/current-ads', 'refresh');
			} else {
				$this->session->set_flashdata('message', 'Sorry, Ads Update failed!!');
				redirect('admin/ads-edit/' . $id, 'refresh');
			}
		}
	}

	public function ads_delete($id)
	{
		if ($this->adminmodel->deleteAds($id)) {
			$this->session->set_flashdata('message', 'Ads has been Permanently Deleted!!!');
			redirect('admin/current-ads', 'refresh');
		} else {
			$this->session->set_flashdata('message', 'Ads  Delete Failed!');
			redirect('admin/current-ads', 'refresh');
		}
	}

	/* Ads Price*/
	public function ads_price()
	{
		$data['logged_id'] 	= NULL;
		$session  			= $this->session->userdata('logged_admin');
		$data['logged_id'] 	= $session['logged_id'];

		$data['prices']		= $this->adminmodel->allAdsPrices();

		$this->load->view('admin/header', $data);
		$this->load->view('admin/ads_price');
		$this->load->view('admin/footer');
	}
	/**
	 * "728" + "90" -> "728x90", the string `ads_prices.size` holds.
	 *
	 * Every view reads the zone's pixel box out of that one column and emits
	 * it as width/height so the browser reserves the space before the creative
	 * loads. The admin form collects two numbers; this is where they become
	 * the column. Returns '' when either side is missing or out of range, so
	 * a junk value can't reach ad_slot.php's parser.
	 */
	private function adsSizeString($width, $height)
	{
		$w = (int) preg_replace('/\D+/', '', (string) $width);
		$h = (int) preg_replace('/\D+/', '', (string) $height);

		if ($w < 10 || $h < 10 || $w > 9999 || $h > 9999) {
			return '';
		}

		return $w . 'x' . $h;
	}

	/**
	 * The slot_key for a pricing row: what a view asks for by name, and what a
	 * package's `plans.slug` must equal for buying it to fill the position.
	 *
	 * Falls back to a slug of the zone's name so a row created in admin is
	 * never left unreachable, which is what every hand-made row before the
	 * 0006 migration was. Restricted to the same [a-z0-9-] the partial's class
	 * filter and the preview filenames assume.
	 */
	private function adsSlotKey($posted, $name)
	{
		$key = strtolower(trim((string) $posted));

		if ($key === '') {
			$key = function_exists('pearland_slug')
				? pearland_slug((string) $name)
				: strtolower((string) $name);
		}

		$key = preg_replace('/[^a-z0-9]+/', '-', $key);
		$key = trim((string) $key, '-');

		return substr($key, 0, 60);
	}

	/* Ads Price Create*/
	public function create_ads_price()
	{
		$data['logged_id'] 	= NULL;
		$session  			= $this->session->userdata('logged_admin');
		$data['logged_id'] 	= $session['logged_id'];

		$this->form_validation->set_rules('name', 'Name', 'trim|required');
		$this->form_validation->set_rules('width', 'Ad Width', 'trim|required');
		$this->form_validation->set_rules('height', 'Ad Height', 'trim|required');
		$this->form_validation->set_rules('cost', 'Cost', 'trim|required');
		$this->form_validation->set_rules('status', 'Status', 'trim|required');

		if ($this->form_validation->run() == false) {
			$this->load->view('admin/header', $data);
			$this->load->view('admin/create_ads_price');
			$this->load->view('admin/footer');
		} else {
			// `ads_prices` has no width/height columns — it has `size`, the
			// "728x90" string every view parses to reserve the box before the
			// creative paints. The form collects the two numbers, so compose
			// it here; without this a zone created in admin had no pixel box
			// and ad_slot.php fell back to a guessed 3:1 shape.
			$info = array(
				'name'     => $this->input->post('name'),
				'slot_key' => $this->adsSlotKey($this->input->post('slot_key'), $this->input->post('name')),
				'size'     => $this->adsSizeString($this->input->post('width'), $this->input->post('height')),
				'cost'     => $this->input->post('cost'),
				'status'   => $this->input->post('status'),
			);

			// Was `'img' => $uploaded_image_path` — a variable that is never
			// assigned, so PHP 8 warned and stored NULL on every insert.
			if (!empty($_FILES['img']['name'])) {
				$uploaded = uploadAndResize(array(
					'upload_path' => 'images/ads',
					'image_name'  => 'img',
					'quality'     => '60%',
				));
				// uploadAndResize() returns nothing when the upload is
				// rejected; don't write NULL over a column the list reads.
				if (!empty($uploaded)) {
					$info['img'] = $uploaded;
				}
			}

			if ($this->adminmodel->add('ads_prices', $info)) {
				$this->session->set_flashdata('message', 'Ads Price has been Successfully Added!!');
				redirect('admin/ads-price', 'refresh');
			} else {
				$this->session->set_flashdata('message', 'Error Adding Ads Price!!');
				redirect('admin/create-ads-price');
			}
		}
	}

	/* Ads Price Edit*/
	public function ads_price_edit($id)
	{
		$data['logged_id'] 	= NULL;
		$session  			= $this->session->userdata('logged_admin');
		$data['logged_id'] 	= $session['logged_id'];

		$data['price']		= $this->adminmodel->getAdsPrice($id);

		// getAdsPrice() returns NULL for an id that isn't there, and reading
		// ->img off it was a fatal error rather than a redirect.
		if (empty($data['price'])) {
			$this->session->set_flashdata('message', 'That ad zone no longer exists.');
			redirect('admin/ads-price', 'refresh');
			return;
		}
		$old_image        	= (string) ($data['price']->img ?? '');

		$this->form_validation->set_rules('name', 'Name', 'trim|required');
		$this->form_validation->set_rules('width', 'Ad Width', 'trim|required');
		$this->form_validation->set_rules('height', 'Ad Height', 'trim|required');
		$this->form_validation->set_rules('cost', 'Cost', 'trim|required');
		$this->form_validation->set_rules('status', 'Status', 'trim|required');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('admin/header', $data);
			$this->load->view('admin/ads_price_edit');
			$this->load->view('admin/footer');
		} else {
			$info = array(
				'name'		=> $this->input->post('name'),
				// See create_ads_price() — the column is `size`, not width/height.
				'size'      => $this->adsSizeString($this->input->post('width'), $this->input->post('height')),
				'cost'    	=> $this->input->post('cost'),
				'status'    => $this->input->post('status')
			);

			// A zone with no slot_key is one no view can ask for, so naming it
			// is what makes it renderable. Re-keying a zone that already has
			// one would silently unhook whatever page renders it, so that is
			// deliberately not offered.
			if (trim((string) ($data['price']->slot_key ?? '')) === '') {
				$new_key = $this->adsSlotKey($this->input->post('slot_key'), $this->input->post('name'));
				if ($new_key !== '') {
					$info['slot_key'] = $new_key;
				}
			}

			$info['img'] = $old_image;

			if (!empty($_FILES['img']['name'])) {
				// Upload first, delete second. The old order unlinked the
				// existing artwork before the replacement was accepted, so a
				// rejected upload (wrong type, over 5 MB) left the zone with
				// no image at all.
				$uploaded = uploadAndResize(array(
					'upload_path' => 'images/ads',
					'image_name'  => 'img',
					'quality'     => '60%',
				));

				if (!empty($uploaded)) {
					$info['img'] = $uploaded;

					$image_link = FCPATH . ltrim((string) $old_image, '/');
					if ($old_image !== '' && $uploaded !== $old_image
						&& strpos((string) $old_image, '..') === false && is_file($image_link)) {
						@unlink($image_link);
					}
				}
			}

			if ($this->adminmodel->updateAdsPrice($id, $info)) {
				$this->session->set_flashdata('message', 'Successfully Updated Ads Price!!');
				redirect('admin/ads-price', 'refresh');
			} else {
				$this->session->set_flashdata('message', 'Sorry, Ads Price Update failed!!');
				redirect('admin/ads-price-edit/' . $id, 'refresh');
			}
		}
	}
	/* Ads Request*/
	public function ads_request()
	{
		$data['logged_id'] 	= NULL;
		$session  			= $this->session->userdata('logged_admin');
		$data['logged_id'] 	= $session['logged_id'];

		$data['ads'] 		= $this->adminmodel->getAllInactiveAds();

		$this->load->view('admin/header', $data);
		$this->load->view('admin/ads_request');
		$this->load->view('admin/footer');
	}
	/* Ads History*/
	public function ads_history()
	{
		$data['logged_id'] 	= NULL;
		$session  			= $this->session->userdata('logged_admin');
		$data['logged_id'] 	= $session['logged_id'];

		$data['ads'] 		= $this->adminmodel->getAllAdsHistory();

		$this->load->view('admin/header', $data);
		$this->load->view('admin/ads_history');
		$this->load->view('admin/footer');
	}
	public function ads_history_delete($id)
	{
		if ($this->adminmodel->deleteAds($id)) {
			$this->session->set_flashdata('message', 'Ad has been Permanently Deleted!!!');
			redirect('admin/ads-history', 'refresh');
		} else {
			$this->session->set_flashdata('message', 'Ad Delete Failed!');
			redirect('admin/ads-history', 'refresh');
		}
	}

	/*==========================================
	 ADS ACTIVE/INACTIVE STATUS UPDATE FUNCTION
	 ==========================================*/

	public function approveAdStatus()
	{
		$id = (int) $this->input->post('id');
		$statusValue = (int) $this->input->post('status');

		$data = $this->adminmodel->getSingleAdsAjaxValue($id);

		// Was hard-coded to 1, so the "Active" button — which posts 0 and reads
		// as "switch this off" — approved the ad again instead. There was no
		// way to take a live ad down from this screen short of deleting it.
		$data['ad_status'] = $statusValue === 1 ? 1 : 0;

		if ($this->db->where('id', $id)->update('ads', $data)) {
			$update['message'] = 'success';
			$this->session->set_flashdata('message', 'Status Updated Successfully!');
		} else {
			$update['message'] = 'error';
			$this->session->set_flashdata('message', 'Status Update Unsuccessful!');
		}
		echo json_encode($update);
	}

	/*====================
		Pricing Page
	=====================*/
	public function pricing()
	{
		// Initialize empty data to avoid errors
		$data = array();

		// Every plan, inactive ones included: a deactivated plan used to
		// vanish from this screen (getAllPricingPlans() is active-only), so
		// nothing could ever switch it back on.
		try {
			$data['pricing_plans'] = $this->adminmodel->getAllPricingPlansAdmin();
		} catch (Exception $e) {
			$data['pricing_plans'] = array(); // Empty array if database not ready
		}

		// No hardcoded placeholder fallback: the pricing catalog is data-driven.
		// If the DB has no active plans, the view renders its own empty state and
		// invites the admin to add one (or import the packages seed).

		$this->load->view('admin/header');
		$this->load->view('admin/pricing', $data);
		$this->load->view('admin/footer');
	}

	/*====================
		Pricing Plan Page (Public URL)
	=====================*/
	public function pricing_plan()
	{
		$this->pricing();
	}

	/** The categories /pricing-plan groups packages under. */
	private function pricingCategories()
	{
		return array('business-listing', 'add-ons', 'site-wide-banners', 'category-banners',
			'site-wide-videos', 'category-videos', 'special');
	}

	/**
	 * Validate the plan modal and build the `plans` row.
	 *
	 * Price, currency and term are real fields now. The modal used to have
	 * no price input at all while edit_pricing_plan() wrote
	 * `price => post('plan_price') ?? null` — so saving ANY change (a typo in
	 * the description) set the price to NULL, the public page read that as
	 * $0 and showed "Get started free", and /start-free would hand the paid
	 * tier out for nothing.
	 *
	 * @param  object|null $existing the plan being edited
	 * @return array|false  the row, or FALSE with a flash error set
	 */
	private function pricingPlanPost($existing = NULL)
	{
		$this->form_validation->set_rules('plan_name', 'Plan Name', 'trim|required|max_length[150]');
		$this->form_validation->set_rules('plan_price', 'Price', 'trim|required|numeric|greater_than_equal_to[0]');
		$this->form_validation->set_rules('duration_days', 'Term (days)', 'trim|required|is_natural_no_zero|less_than_equal_to[3650]');

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('error', strip_tags(validation_errors()));
			return FALSE;
		}

		$interval = strtolower(trim((string) $this->input->post('billing_interval')));
		if (!in_array($interval, array('monthly', 'yearly', 'weekly', 'one-time'), true)) $interval = 'monthly';

		$category = (string) $this->input->post('category');
		if (!in_array($category, $this->pricingCategories(), true)) $category = 'business-listing';

		$currency = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', (string) $this->input->post('currency')), 0, 3));
		if (strlen($currency) !== 3) $currency = 'USD';

		$int = function ($field, $default = 0) {
			$v = $this->input->post($field);
			return ($v === NULL || $v === '' || !is_numeric($v)) ? $default : (int) $v;
		};

		$row = array(
			'name'             => trim((string) $this->input->post('plan_name')),
			'price'            => round((float) $this->input->post('plan_price'), 2),
			'currency'         => $currency,
			'billing_interval' => $interval,
			'duration_days'    => (int) $this->input->post('duration_days'),
			'category'         => $category,
			'description'      => trim((string) $this->input->post('description')),
			'listing_limit'    => $int('listing_limit'),
			'product_limit'    => $int('product_limit'),
			'event_limit'      => $int('event_limit'),
			'classified_limit' => $int('classified_limit'),
			'blog_limit'       => $int('blog_limit'),
			'news_limit'       => $int('news_limit'),
			'video_limit'      => $int('video_limit'),
			'rental_limit'     => $int('rental_limit'),
			'house_limit'      => $int('house_limit'),
			'ad_limit'         => $int('ad_limit'),
			'ad_boost_limit'   => $int('ad_boost_limit'),
			'placeholder_images_limit' => $int('placeholder_images_limit'),
			'featured_listing' => $this->input->post('featured_listing') ? 1 : 0,
			'priority_support' => $this->input->post('priority_support') ? 1 : 0,
			'analytics_access' => $this->input->post('analytics_access') ? 1 : 0,
			// Field-level listing capabilities (freemium editing)
			'can_photos'        => $this->input->post('can_photos') ? 1 : 0,
			'photo_limit'       => $int('photo_limit'),
			'can_map'           => $this->input->post('can_map') ? 1 : 0,
			'can_services'      => $this->input->post('can_services') ? 1 : 0,
			'can_extra_details' => $this->input->post('can_extra_details') ? 1 : 0,
			'allow_video'       => $this->input->post('allow_video') ? 1 : 0,
			'updated_at'        => date('Y-m-d H:i:s'),
		);

		$sort = $this->input->post('sort_order');
		if ($sort !== NULL && $sort !== '' && is_numeric($sort)) {
			$row['sort_order'] = (int) $sort;
		}

		// Videos a listing on this plan may show (migration 0036). Only
		// written once the column exists: before it, writing it would fail
		// the whole save.
		if ($this->db->field_exists('listing_video_limit', 'plans')) {
			$row['listing_video_limit'] = max(-1, $int('listing_video_limit', 1));
		}

		return $row;
	}

	/*====================
		Add Pricing Plan
	=====================*/
	public function add_pricing_plan()
	{
		if ($this->input->method() !== 'post') {
			redirect('admin/pricing');
			return;
		}

		$data = $this->pricingPlanPost();
		if ($data === FALSE) {
			redirect('admin/pricing');
			return;
		}

		// A slug, which is also the ad-zone key a banner plan sells: a plan
		// added here used to have none, so it could never be matched to a
		// zone or told apart from another plan of the same name.
		$data['slug']       = $this->adminmodel->uniquePlanSlug(
			trim((string) $this->input->post('slug')) !== '' ? $this->input->post('slug') : $data['name']
		);
		$data['is_active']  = 1;
		$data['created_at'] = date('Y-m-d H:i:s');
		if (!isset($data['sort_order'])) {
			$data['sort_order'] = $this->adminmodel->nextPlanSortOrder();
		}

		$plan_id = $this->adminmodel->addPricingPlan($data);
		if ($plan_id) {
			// The base term, so the checkout's term picker and the card on
			// /pricing-plan quote the same figure from the first day.
			$this->adminmodel->syncPlanBaseTerm((int) $plan_id);
			$this->session->set_flashdata('message', 'Pricing plan added successfully!');
		} else {
			$err = $this->db->error();
			$this->session->set_flashdata('error', 'Failed to add pricing plan!'
				. (!empty($err['message']) ? ' Database: ' . $err['message'] : ''));
		}
		redirect('admin/pricing');
	}

	/*====================
		Get Pricing Plan (AJAX)
	=====================*/
	public function get_pricing_plan()
	{
		$id = $this->input->post('id');
		$plan = $this->adminmodel->getPricingPlan($id);

		if ($plan) {
			echo json_encode($plan);
		} else {
			echo json_encode(false);
		}
	}

	/*====================
		Get Pricing Plan Durations (AJAX)
	=====================*/
	public function get_pricing_plan_durations()
	{
		$plan_id = $this->input->post('plan_id');
		$data = $this->adminmodel->getPlanDurations($plan_id);
		echo json_encode($data ?: []);
	}

	/*====================
		Update Pricing Duration (AJAX)
	=====================*/
	public function update_pricing_duration()
	{
		$id = $this->input->post('id');
		$price = $this->input->post('price');
		if (!$id) {
			echo json_encode(['success' => false, 'error' => 'Missing id']);
			return;
		}
		$extra = array(
			'duration_months' => $this->input->post('duration_months'),
			'duration_days'   => $this->input->post('duration_days'),
			'currency'        => $this->input->post('currency'),
		);
		$ok = $this->adminmodel->updatePricingDuration($id, $price, $extra);
		if ($ok) {
			$row = $this->adminmodel->getPlanDurationById($id);
			// Re-pricing the base term re-prices the card too, so
			// /pricing-plan and the checkout never quote two figures.
			if ($row) $this->adminmodel->syncPlanPriceFromTerm($row);
			echo json_encode(['success' => true, 'duration' => $row]);
			return;
		}
		// Surface the actual DB error — "DB update failed" on its own sent us
		// hunting for a UI bug when the cause was a missing column.
		$err = $this->db->error();
		echo json_encode([
			'success' => false,
			'error'   => !empty($err['message'])
				? 'Database: ' . $err['message'] . ' (run migration 0015_plan_durations_columns.sql)'
				: 'Nothing was saved. Check that the duration still exists.',
		]);
	}

	/*====================
		Add / delete a plan term (AJAX)
	=====================*/
	public function add_pricing_duration()
	{
		$plan_id = (int) $this->input->post('plan_id');
		$months  = $this->input->post('duration_months');
		$days    = $this->input->post('duration_days');
		$price   = $this->input->post('price');

		if (!$plan_id) {
			echo json_encode(['success' => false, 'error' => 'Choose a plan first.']);
			return;
		}
		$new_id = $this->adminmodel->addPlanDuration($plan_id, $months, $days, $price, $this->input->post('currency') ?: 'USD');
		if ($new_id) {
			$row = $this->adminmodel->getPlanDurationById($new_id);
			if ($row) $this->adminmodel->syncPlanPriceFromTerm($row);
			echo json_encode(['success' => true, 'id' => $new_id]);
			return;
		}
		$err = $this->db->error();
		echo json_encode([
			'success' => false,
			'error'   => !empty($err['message'])
				? 'Database: ' . $err['message'] . ' (run migration 0015_plan_durations_columns.sql)'
				: 'Enter a term length greater than zero.',
		]);
	}

	public function delete_pricing_duration()
	{
		$id = (int) $this->input->post('id');
		if (!$id) {
			echo json_encode(['success' => false, 'error' => 'Missing id']);
			return;
		}
		echo json_encode(['success' => (bool) $this->adminmodel->deletePlanDuration($id)]);
	}

	/*====================
		Edit Pricing Plan
	=====================*/
	public function edit_pricing_plan($id)
	{
		$id   = (int) $id;
		$plan = $id ? $this->adminmodel->getPricingPlan($id) : NULL;
		if (!$plan || $this->input->method() !== 'post') {
			redirect('admin/pricing');
			return;
		}

		$data = $this->pricingPlanPost($plan);
		if ($data === FALSE) {
			redirect('admin/pricing');
			return;
		}

		// The slug is not editable here: for a banner plan it is the ad-zone
		// key, and renaming it would silently take the zone off sale.
		if ($this->adminmodel->updatePricingPlan($id, $data)) {
			// One price, two stores: plans.price (the card) and the base-term
			// plan_durations row (what checkout charges for that term).
			$this->adminmodel->syncPlanBaseTerm($id);
			$this->session->set_flashdata('message', 'Pricing plan updated successfully!');
		} else {
			$this->session->set_flashdata('error', 'Failed to update pricing plan!');
		}
		redirect('admin/pricing');
	}

	/*====================
		Deactivate / reactivate a plan (POST)
	=====================*/
	public function toggle_pricing_plan($id)
	{
		$id   = (int) $id;
		$plan = $id ? $this->adminmodel->getPricingPlan($id) : NULL;
		if (!$plan || $this->input->method() !== 'post') {
			redirect('admin/pricing');
			return;
		}

		// Never a hard delete: subscriptions and transactions point at the
		// plan id, and renewals read its price.
		$on = empty($plan->is_active) ? 1 : 0;
		if ($this->adminmodel->updatePricingPlan($id, array('is_active' => $on, 'updated_at' => date('Y-m-d H:i:s')))) {
			$this->session->set_flashdata('message', '"' . $plan->name . '" is now ' . ($on ? 'on sale.' : 'off sale (existing customers keep it).'));
		} else {
			$this->session->set_flashdata('error', 'Could not change that plan.');
		}
		redirect('admin/pricing');
	}

	/*====================
		Delete Pricing Plan
	=====================*/
	public function delete_pricing_plan($id)
	{
		// Kept for old links. It was always a soft delete (is_active = 0);
		// toggle_pricing_plan() is the POST equivalent the screen uses.
		if ($this->input->method() !== 'post') {
			redirect('admin/pricing');
			return;
		}
		if ($this->adminmodel->deletePricingPlan((int) $id)) {
			$this->session->set_flashdata('message', 'Pricing plan taken off sale.');
		} else {
			$this->session->set_flashdata('error', 'Failed to delete pricing plan!');
		}
		redirect('admin/pricing');
	}

	/*====================
		Subscription Management
	=====================*/
	public function all_subscriptions($page = 1)
	{
		$this->load->library('pagination');
		$total_rows = $this->adminmodel->countAllSubscriptions();
		$config = [
			'base_url' => base_url('admin/all_subscriptions'),
			'total_rows' => $total_rows,
			'per_page' => admin_per_page(20),
			'reuse_query_string' => TRUE,
			'uri_segment' => 3,
			'use_page_numbers' => TRUE,
			'first_link' => 'First',
			'last_link' => 'Last',
			'next_link' => 'Next',
			'prev_link' => 'Prev',
			'full_tag_open' => '<nav><ul class="pagination">',
			'full_tag_close' => '</ul></nav>',
			'first_tag_open' => '<li class="page-item">',
			'first_tag_close' => '</li>',
			'last_tag_open' => '<li class="page-item">',
			'last_tag_close' => '</li>',
			'next_tag_open' => '<li class="page-item">',
			'next_tag_close' => '</li>',
			'prev_tag_open' => '<li class="page-item">',
			'prev_tag_close' => '</li>',
			'num_tag_open' => '<li class="page-item">',
			'num_tag_close' => '</li>',
			'cur_tag_open' => '<li class="page-item active"><a class="page-link">',
			'cur_tag_close' => '</a></li>',
			'attributes' => array('class' => 'page-link')
		];
		$this->pagination->initialize($config);
		$page = ($page < 1) ? 1 : $page;
		$offset = ($page - 1) * $config['per_page'];
		$data['start_number'] = $offset + 1;
		$data['subscriptions'] = $this->adminmodel->getAllSubscriptions($config['per_page'], $offset);
		// Attach transactions for each subscription so the view can render them without an AJAX call
		// Build a map of subscription_id => transactions to avoid direct bracket->property assignment
		if (!empty($data['subscriptions'])) {
			$data['subscription_transactions'] = [];
			foreach ($data['subscriptions'] as $sub) {
				$sid = isset($sub->id) ? $sub->id : null;
				if ($sid) {
					$data['subscription_transactions'][$sid] = $this->adminmodel->getSubscriptionTransactions($sid);
				}
			}
		}
		$data['link'] = $this->pagination->create_links();
		$this->load->view('admin/header');
		$this->load->view('admin/all_subscriptions', $data);
		$this->load->view('admin/footer');
	}

	/**
	 * AJAX: return subscription detail HTML for modal
	 */
	public function subscription_view($id)
	{
		$sub = $this->adminmodel->getSubscriptionById($id);
		if (!$sub) {
			echo '<p>Subscription not found</p>';
			return;
		}
		$transactions = $this->adminmodel->getSubscriptionTransactions($id);
		$data = ['sub' => $sub, 'transactions' => $transactions];
		// Load the modal body view (large modal is declared in the listing page)
		// using `admin_subscription_modal` which contains modal body markup
		$this->load->view('admin_subscription_modal', $data);
	}

	public function cancel_subscription($id)
	{
		if ($this->adminmodel->cancelSubscriptionById($id)) {
			$this->session->set_flashdata('message', 'Subscription canceled successfully.');
		} else {
			$this->session->set_flashdata('error', 'Failed to cancel subscription.');
		}
		redirect('admin/all_subscriptions');
	}

	public function delete_subscription($id)
	{
		if ($this->adminmodel->deleteSubscriptionById($id)) {
			$this->session->set_flashdata('message', 'Subscription deleted successfully.');
		} else {
			$this->session->set_flashdata('error', 'Failed to delete subscription.');
		}
		redirect('admin/all_subscriptions');
	}

	public function user_usage_stats($user_id)
	{
		$data['user_stats'] = $this->adminmodel->getUserSubscriptionStats($user_id);
		$data['user_info'] = $this->adminmodel->getSingleValue($user_id, 'user_id', 'user');
		$this->load->view('admin/header');
		$this->load->view('admin/user_usage_stats', $data);
		$this->load->view('admin/footer');
	}

	/*====================
		Plan Usage Helpers
	=====================*/
	public function check_user_limit()
	{
		$user_id = $this->input->post('user_id');
		$type = $this->input->post('type');

		$can_add = $this->adminmodel->checkUserLimit($user_id, $type);
		echo json_encode(['can_add' => $can_add]);
	}

	/*====================
		Subscription Helper Methods in Controller
	=====================*/

	/**
	 * Check if user can add content before form submission
	 */
	private function checkSubscriptionBeforeAction($user_id, $type)
	{
		if (!$this->adminmodel->validateContentAddition($user_id, $type)) {
			$remaining = $this->adminmodel->getRemainingLimit($user_id, $type);
			$upgrade_url = base_url('admin/pricing');

			$error_message = "You have reached your $type limit. ";
			if ($remaining == 0) {
				$error_message .= "<a href='$upgrade_url'>Upgrade your plan</a> to add more $type.";
			} else {
				$error_message .= "Remaining: $remaining $type.";
			}

			$this->session->set_flashdata('error', $error_message);
			return false;
		}

		return true;
	}

	/**
	 * Track usage after successful content creation
	 */
	private function trackUsageAfterSuccess($user_id, $type)
	{
		return $this->adminmodel->trackUsageAfterCreation($user_id, $type);
	}

	/**
	 * Get subscription data for dashboard
	 */
	private function getSubscriptionDataForDashboard($user_id)
	{
		$data = array();
		$data['subscription_stats'] = $this->adminmodel->getUserStats($user_id);
		$data['days_remaining'] = $this->adminmodel->getDaysRemaining($user_id);
		$data['is_expired'] = $this->adminmodel->isSubscriptionExpired($user_id);

		// Usage percentages for progress bars
		$data['listing_usage_percent'] = $this->adminmodel->getUsagePercentage($user_id, 'listing');
		$data['product_usage_percent'] = $this->adminmodel->getUsagePercentage($user_id, 'product');
		$data['event_usage_percent'] = $this->adminmodel->getUsagePercentage($user_id, 'event');

		// Check feature access
		$data['has_analytics'] = $this->adminmodel->hasFeatureAccess($user_id, 'analytics_access');
		$data['has_priority_support'] = $this->adminmodel->hasFeatureAccess($user_id, 'priority_support');

		return $data;
	}

	/**
	 * Purchase subscription process
	 */
	public function purchase_subscription()
	{
		$user_id = $this->session->userdata('user_id');
		$plan_id = $this->input->post('plan_id');

		// Payment processing (integrate with payment gateway)
		$payment_data = array(
			'transaction_id' => 'TXN_' . time(), // From payment gateway
			'amount' => $this->input->post('amount'),
			'payment_method' => $this->input->post('payment_method')
		);

		// Create subscription
		$subscription_id = $this->adminmodel->createNewSubscription($user_id, $plan_id, $payment_data);

		if ($subscription_id) {
			$this->session->set_flashdata('message', 'Subscription activated successfully!');
			redirect('admin/dashboard');
		} else {
			$this->session->set_flashdata('error', 'Failed to activate subscription. Please try again.');
			redirect('admin/pricing');
		}
	}

	/**
	 * /admin/users — a read-only review list of member accounts.
	 *
	 * There was no user screen anywhere in the admin: the only place an
	 * account appeared was inside the owner dropdown on the listing form,
	 * as a bare name. So "are these users all allowed to publish content?
	 * Kindly review each user" could not be acted on by anybody — there was
	 * nothing to review.
	 *
	 * Deliberately read-only. Deleting a member orphans every listing, event
	 * and article they own, and that needs its own screen with its own
	 * warnings rather than a Delete link on a list built to answer a question.
	 */
	public function users($page = 1)
	{
		$q = trim((string) $this->input->get('q', TRUE));

		$per_page   = admin_per_page(20);
		$total_rows = $this->adminmodel->countUserDirectory($q);

		$config = array(
			'base_url'           => base_url('admin/users'),
			'total_rows'         => $total_rows,
			'per_page'           => $per_page,
			'uri_segment'        => 3,
			'use_page_numbers'   => TRUE,
			'reuse_query_string' => TRUE,
			'first_link'         => 'First',
			'last_link'          => 'Last',
			'next_link'          => 'Next',
			'prev_link'          => 'Prev',
			'full_tag_open'      => '<nav><ul class="pagination">',
			'full_tag_close'     => '</ul></nav>',
			'first_tag_open'     => '<li class="page-item">',
			'first_tag_close'    => '</li>',
			'last_tag_open'      => '<li class="page-item">',
			'last_tag_close'     => '</li>',
			'next_tag_open'      => '<li class="page-item">',
			'next_tag_close'     => '</li>',
			'prev_tag_open'      => '<li class="page-item">',
			'prev_tag_close'     => '</li>',
			'num_tag_open'       => '<li class="page-item">',
			'num_tag_close'      => '</li>',
			'cur_tag_open'       => '<li class="page-item active"><a class="page-link">',
			'cur_tag_close'      => '</a></li>',
			'attributes'         => array('class' => 'page-link'),
		);
		$this->pagination->initialize($config);

		$page   = ((int) $page < 1) ? 1 : (int) $page;
		$offset = ($page - 1) * $per_page;

		$data = array(
			'members'      => $this->adminmodel->getUserDirectory($per_page, $offset, $q),
			'start_number' => $offset + 1,
			'search_query' => $q,
			'total_rows'   => $total_rows,
			'link'         => $this->pagination->create_links(),
		);

		$this->load->view('admin/header');
		$this->load->view('admin/users', $data);
		$this->load->view('admin/footer');
	}

	/*=====================================================================
		Member moderation (super_admin) — POST from /admin/users

		/admin/users was read-only and nothing wrote user.banned, so the
		answer to "are these users all allowed to publish content?" was
		"yes, and there is no way to stop one". Suspend is the normal tool
		(reversible, keeps everything they own); delete is only offered for an
		account that owns nothing at all.
	=====================================================================*/
	public function user_suspend($user_id = 0)
	{
		$this->moderateUser((int) $user_id, TRUE);
	}

	public function user_restore($user_id = 0)
	{
		$this->moderateUser((int) $user_id, FALSE);
	}

	private function moderateUser($user_id, $ban)
	{
		if ($this->input->method() !== 'post' || $user_id < 1) {
			redirect('admin/users');
			return;
		}
		$user = $this->db->where('user_id', $user_id)->limit(1)->get('user')->row();
		if (!$user) {
			$this->session->set_flashdata('error', 'No such member.');
		} elseif (!$this->db->field_exists('banned', 'user')) {
			$this->session->set_flashdata('error', 'Run migration 0035 first: the user table has no `banned` column.');
		} elseif ($this->adminmodel->setUserBanned($user_id, $ban)) {
			$this->session->set_flashdata('message', $ban
				? '"' . $user->name . '" is suspended: they cannot sign in, and are signed out on their next click. Everything they own stays as it is.'
				: '"' . $user->name . '" is restored and can sign in again.');
		} else {
			$this->session->set_flashdata('error', 'Could not update that member. ' . $this->adminmodel->lastDbError());
		}
		redirect($this->usersReturnUrl());
	}

	public function user_delete($user_id = 0)
	{
		$user_id = (int) $user_id;
		if ($this->input->method() !== 'post' || $user_id < 1) {
			redirect('admin/users');
			return;
		}
		$user = $this->db->where('user_id', $user_id)->limit(1)->get('user')->row();
		if (!$user) {
			$this->session->set_flashdata('error', 'No such member.');
			redirect($this->usersReturnUrl());
			return;
		}

		$owned = $this->adminmodel->userOwnership($user_id);
		if ($owned) {
			$parts = array();
			foreach ($owned as $label => $n) $parts[] = $n . ' ' . $label;
			$this->session->set_flashdata('error', '"' . $user->name . '" was not deleted: they own ' . implode(', ', $parts)
				. '. Suspend the account instead, or move those to another owner first.');
		} elseif ($this->adminmodel->deleteEmptyUser($user_id)) {
			$this->session->set_flashdata('message', 'Deleted the empty account "' . $user->name . '".');
		} else {
			$this->session->set_flashdata('error', 'Could not delete that account. ' . $this->adminmodel->lastDbError());
		}
		redirect($this->usersReturnUrl());
	}

	/** Back to the members page the action came from, search and page kept. */
	private function usersReturnUrl()
	{
		$back = (string) $this->input->post('return_to');
		return (strpos($back, 'admin/users') === 0) ? $back : 'admin/users';
	}

	/*=====================================================================
		Deals & coupons (admin) — /admin/coupons

		Members post deals at /user/my-coupons; until this screen nothing in
		admin could see them, switch one off or remove one (the old sidebar
		links went to admin-coupons.html, a theme file that never existed).
	=====================================================================*/
	public function coupons($page = 1)
	{
		$q = trim((string) $this->input->get('q', TRUE));

		$per_page   = admin_per_page(20);
		$total_rows = $this->adminmodel->countCouponsAdmin($q);

		$this->pagination->initialize(array(
			'base_url'           => base_url('admin/coupons'),
			'total_rows'         => $total_rows,
			'per_page'           => $per_page,
			'uri_segment'        => 3,
			'use_page_numbers'   => TRUE,
			'reuse_query_string' => TRUE,
			'full_tag_open'      => '<nav><ul class="pagination">',
			'full_tag_close'     => '</ul></nav>',
			'num_tag_open'       => '<li class="page-item">',
			'num_tag_close'      => '</li>',
			'cur_tag_open'       => '<li class="page-item active"><a class="page-link">',
			'cur_tag_close'      => '</a></li>',
			'next_tag_open'      => '<li class="page-item">',
			'next_tag_close'     => '</li>',
			'prev_tag_open'      => '<li class="page-item">',
			'prev_tag_close'     => '</li>',
			'first_tag_open'     => '<li class="page-item">',
			'first_tag_close'    => '</li>',
			'last_tag_open'      => '<li class="page-item">',
			'last_tag_close'     => '</li>',
			'attributes'         => array('class' => 'page-link'),
		));

		$page   = max(1, (int) $page);
		$offset = ($page - 1) * $per_page;

		$data = array(
			'coupons'      => $this->adminmodel->getCouponsAdmin($per_page, $offset, $q),
			'start_number' => $offset + 1,
			'search_query' => $q,
			'total_rows'   => $total_rows,
			'link'         => $this->pagination->create_links(),
		);

		$this->load->view('admin/header');
		$this->load->view('admin/coupons', $data);
		$this->load->view('admin/footer');
	}

	/** POST — switch a deal on or off. */
	public function coupon_toggle($coupon_id = 0)
	{
		$coupon = $this->input->method() === 'post' ? $this->adminmodel->getCouponAdmin((int) $coupon_id) : NULL;
		if (!$coupon) {
			redirect('admin/coupons');
			return;
		}
		$on = empty($coupon->coupon_status) ? 1 : 0;
		if ($this->adminmodel->setCouponStatus((int) $coupon->coupon_id, $on)) {
			$this->session->set_flashdata('message', '"' . $coupon->coupon_name . '" is now ' . ($on ? 'live.' : 'switched off — hidden from /coupons and the homepage.'));
		} else {
			$this->session->set_flashdata('error', 'Could not change that deal.');
		}
		redirect('admin/coupons');
	}

	/** POST — delete a deal (and its picture). */
	public function coupon_delete($coupon_id = 0)
	{
		$coupon = $this->input->method() === 'post' ? $this->adminmodel->getCouponAdmin((int) $coupon_id) : NULL;
		if (!$coupon) {
			redirect('admin/coupons');
			return;
		}
		if ($this->adminmodel->deleteCouponAdmin((int) $coupon->coupon_id)) {
			$this->session->set_flashdata('message', 'Deleted the deal "' . $coupon->coupon_name . '".');
		} else {
			$this->session->set_flashdata('error', 'Could not delete that deal.');
		}
		redirect('admin/coupons');
	}

	/*=====================================================================
		Customer billing — /admin/billing

		Until this screen existed a card could only be vaulted by the CUSTOMER,
		during their own checkout, and only if they ticked auto-renew. Staff
		signing up a business over the phone had no way to put a card on file,
		take that month's payment, or say when billing should resume — and
		Cli::renew_subscriptions therefore had nothing to charge.

		Card data never reaches this server. "Add a card" opens the same
		HelcimPay.js iframe the storefront uses; the browser sends the PAN
		straight to Helcim and hands us back a token. What we store is that
		token, the last four digits, and the brand.
	======================================================================*/

	/** The admin acting on the current request, for the audit columns. */
	private function billingAdminId()
	{
		$admin = $this->session->userdata('logged_admin');

		return (int) (isset($admin['logged_id']) ? $admin['logged_id'] : 0);
	}

	/**
	 * Validate a staff-typed money amount.
	 *
	 * Accepts what somebody copying a figure off an invoice actually types —
	 * "$1,234.50" — and rejects anything non-numeric, zero, negative, or above
	 * the ceiling. The ceiling exists because this is the one amount in the
	 * system a human types freehand: everything else is priced server-side
	 * from a plan. An extra keystroke on a $39 invoice should not become a
	 * $3,900 charge on a customer's card without someone raising the limit
	 * deliberately (site setting `billing_manual_charge_max`, at /admin/setting).
	 *
	 * @return float|false
	 */
	private function billingAmount($raw)
	{
		$raw = str_replace(array('$', ',', ' '), '', trim((string) $raw));
		if ($raw === '' || !is_numeric($raw)) {
			return false;
		}

		$amount = round((float) $raw, 2);
		$max    = (float) site_setting('billing_manual_charge_max', 25000);

		if ($amount <= 0 || ($max > 0 && $amount > $max)) {
			return false;
		}

		return $amount;
	}

	/** Normalise a staff-typed date to a DATETIME, or NULL if unreadable. */
	private function billingDate($raw)
	{
		$raw = trim((string) $raw);
		if ($raw === '') {
			return null;
		}

		$ts = strtotime($raw);

		return $ts === false ? null : date('Y-m-d H:i:s', $ts);
	}

	/** A short, human-readable label for the Helcim dashboard. */
	private function billingDescription($customer, $note = '')
	{
		$who = '';
		if ($customer) {
			$who = !empty($customer->businesses) ? $customer->businesses : $customer->name;
		}

		// Built by joining what we have rather than by appending separators, so
		// a customer with no business name does not produce a dangling dash.
		$parts = array('Pearland411');
		if (trim((string) $who) !== '') {
			$parts[] = trim((string) $who);
		}
		if ($note !== '') {
			$parts[] = $note;
		}
		$label = implode(' — ', $parts);

		// Truncate by CHARACTER, not byte: a business name with an accent or a
		// curly apostrophe would otherwise be cut through the middle of one and
		// reach the Helcim dashboard as a replacement glyph.
		return function_exists('mb_substr') ? mb_substr($label, 0, 120, 'UTF-8') : substr($label, 0, 120);
	}

	/**
	 * Pull the displayable card details out of a validated HelcimPay.js
	 * response. Everything is optional — different card types and account
	 * settings return different fields — so each lookup falls back rather than
	 * failing, and only the last four digits are ever kept.
	 */
	private function billingCardDetails(array $txn)
	{
		$out = array('card_last4' => '', 'card_brand' => '', 'card_holder_name' => '');

		foreach (array('cardNumber', 'cardLast4', 'maskedNumber', 'cardMasked') as $key) {
			// is_scalar: Helcim has been known to nest card data, and casting an
			// array to string is a PHP warning, not a card number.
			if (!empty($txn[$key]) && is_scalar($txn[$key])) {
				$digits = preg_replace('/\D+/', '', (string) $txn[$key]);
				if (strlen($digits) >= 4) {
					$out['card_last4'] = substr($digits, -4);
					break;
				}
			}
		}

		foreach (array('cardType', 'cardBrand', 'brand') as $key) {
			if (!empty($txn[$key]) && is_string($txn[$key])) {
				$out['card_brand'] = substr($txn[$key], 0, 30);
				break;
			}
		}

		foreach (array('cardHolderName', 'cardholderName', 'customerName') as $key) {
			if (!empty($txn[$key]) && is_string($txn[$key])) {
				$out['card_holder_name'] = substr($txn[$key], 0, 100);
				break;
			}
		}

		return $out;
	}

	/** JSON helper — these endpoints are all called from the billing page. */
	private function billingJson($payload)
	{
		$this->output->set_content_type('application/json');

		return $this->output->set_output(json_encode($payload));
	}

	/**
	 * The billing list. `?q=` searches every customer by account name, email,
	 * or the name of a business they own; with no search the list shows only
	 * customers who have a subscription, a card or a payment.
	 */
	public function billing($page = 1)
	{
		$q = trim((string) $this->input->get('q', TRUE));

		$per_page   = admin_per_page(20);
		$total_rows = $this->adminmodel->countBillingCustomers($q);

		$config = array(
			'base_url'           => base_url('admin/billing'),
			'total_rows'         => $total_rows,
			'per_page'           => $per_page,
			'uri_segment'        => 3,
			'use_page_numbers'   => TRUE,
			'reuse_query_string' => TRUE,
			'first_link'         => 'First',
			'last_link'          => 'Last',
			'next_link'          => 'Next',
			'prev_link'          => 'Prev',
			'full_tag_open'      => '<nav><ul class="pagination">',
			'full_tag_close'     => '</ul></nav>',
			'first_tag_open'     => '<li class="page-item">',
			'first_tag_close'    => '</li>',
			'last_tag_open'      => '<li class="page-item">',
			'last_tag_close'     => '</li>',
			'next_tag_open'      => '<li class="page-item">',
			'next_tag_close'     => '</li>',
			'prev_tag_open'      => '<li class="page-item">',
			'prev_tag_close'     => '</li>',
			'num_tag_open'       => '<li class="page-item">',
			'num_tag_close'      => '</li>',
			'cur_tag_open'       => '<li class="page-item active"><a class="page-link">',
			'cur_tag_close'      => '</a></li>',
			'attributes'         => array('class' => 'page-link'),
		);
		$this->pagination->initialize($config);

		$page   = ((int) $page < 1) ? 1 : (int) $page;
		$offset = ($page - 1) * $per_page;

		$this->load->library('helcim_lib');

		$data = array(
			'businesses'        => $this->adminmodel->getBillingCustomers($per_page, $offset, $q),
			'start_number'      => $offset + 1,
			'search_query'      => $q,
			'total_rows'        => $total_rows,
			'helcim_configured' => $this->helcim_lib->is_configured(),
			'link'              => $this->pagination->create_links(),
			// Stamped by Cli::renew_subscriptions on every run. Without this
			// there is no way to tell a cron that has never been installed from
			// one that ran and found nothing due — and the first sign of the
			// difference is a customer who was never charged.
			'cron_last_run'     => site_setting('billing_cron_last_run', ''),
			'cron_last_summary' => site_setting('billing_cron_last_summary', ''),
		);

		$this->load->view('admin/header');
		$this->load->view('admin/billing', $data);
		$this->load->view('admin/footer');
	}

	/**
	 * One BUSINESS: card on file, take a payment, recurring schedule.
	 *
	 * The URL segment is a `listings.listing_id`. It was a `user_id` when this
	 * screen was first built, which is the wrong unit here — the listings were
	 * bulk-loaded under a few data-entry logins, so one account owns dozens of
	 * unrelated businesses and billing the account would bill all of them.
	 *
	 * The card still belongs to the owning ACCOUNT, because that is where
	 * Helcim vaults it and one owner paying for three of their businesses uses
	 * one card. The page says so rather than hiding it.
	 */
	public function billing_customer($listing_id = 0)
	{
		/*
		 * ENVIRONMENT is 'production', so display_errors is off and any fatal
		 * here reaches the browser as a bare "HTTP ERROR 500" with nothing in
		 * it. On a screen that touches eight tables — several with columns no
		 * migration ever created — that turns every schema gap into a guessing
		 * game played one deploy at a time.
		 *
		 * PHP 7+ Errors implement Throwable, so a "call to a member function
		 * row() on bool" (what a failed query becomes when db_debug is off) is
		 * caught here like anything else. Staff see what broke; the log keeps
		 * the file and line. This is a super-admin-only route, so the message
		 * is not exposed to anybody who should not see it.
		 */
		try {
			$this->billing_customer_page((int) $listing_id);
		} catch (Throwable $e) {
			log_message('error', 'Billing screen failed for listing ' . (int) $listing_id
				. ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());

			$this->load->view('admin/header');
			$this->load->view('admin/billing_error', array(
				'listing_id' => (int) $listing_id,
				'message'    => $e->getMessage(),
				'where'      => basename($e->getFile()) . ' line ' . $e->getLine(),
			));
			$this->load->view('admin/footer');
		}
	}

	/** The real body of billing_customer(), so the wrapper above can catch it. */
	private function billing_customer_page($listing_id = 0)
	{
		$customer = $this->adminmodel->getBillingCustomer((int) $listing_id);
		if (!$customer) {
			$this->session->set_flashdata('error', 'That business no longer exists.');
			redirect('admin/billing');
			return;
		}
		if (empty($customer->user_id)) {
			$this->session->set_flashdata('error',
				'"' . $customer->listing_name . '" has no owner account, so there is nobody to bill. '
				. 'Set an owner on the listing first.');
			redirect('admin/billing');
			return;
		}
		$user_id = (int) $customer->user_id;

		$this->load->library('helcim_lib');

		$plans = $this->adminmodel->getPlansForBilling();

		// What the renewal job would take on the next run, by the same rule the
		// job itself uses — so the figure on screen is the figure charged.
		$renewal_amount = !empty($customer->subscription_id)
			? $this->usermodel->renewalAmountFor($customer)
			: 0.0;

		$data = array(
			'customer'          => $customer,
			'cards'             => $this->adminmodel->getCustomerCards($user_id),
			'transactions'      => $this->adminmodel->getListingTransactions((int) $listing_id, $user_id, 25),
			'plans'             => $plans,
			'all_durations'     => $this->adminmodel->getAllPlanDurations(),
			'renewal_amount'    => $renewal_amount,
			'has_card'          => (bool) $this->usermodel->getVaultedCardToken($user_id),
			'helcim_configured' => $this->helcim_lib->is_configured(),
			'helcimpay_js_url'  => $this->helcim_lib->helcimpay_js_url(),
			'currency'          => !empty($customer->currency) ? $customer->currency : $this->helcim_lib->currency(),
		);

		$this->load->view('admin/header');
		$this->load->view('admin/billing_customer', $data);
		$this->load->view('admin/footer');
	}

	/**
	 * AJAX — open a HelcimPay.js session for this customer.
	 *
	 * mode=verify   put a card on file, charging nothing.
	 * mode=charge   take a payment on a card typed in now, optionally keeping
	 *               the token for future renewals.
	 *
	 * The secretToken stays server-side; billing_card_save() uses it to prove
	 * the response the browser posts back really came from Helcim.
	 * URL: /admin/billing-card-init (POST)
	 */
	public function billing_card_init()
	{
		if ($this->input->method() !== 'post') {
			return $this->billingJson(array('success' => false, 'error' => 'Not allowed.'));
		}

		$listing_id = (int) $this->input->post('listing_id');
		$customer   = $this->adminmodel->getBillingCustomer($listing_id);
		if (!$customer || empty($customer->user_id)) {
			return $this->billingJson(array('success' => false, 'error' => 'That business was not found, or has no owner account to bill.'));
		}
		$user_id = (int) $customer->user_id;

		$this->load->library('helcim_lib');
		if (!$this->helcim_lib->is_configured()) {
			return $this->billingJson(array(
				'success' => false,
				'error'   => 'Helcim is not configured — add HELCIM_API_TOKEN to .env, then check it at Admin > SEO & Setting.',
			));
		}

		$mode = $this->input->post('mode') === 'charge' ? 'charge' : 'verify';
		$note = trim((string) $this->input->post('note', TRUE));
		$save = $this->input->post('save_card') === '1' ? 1 : 0;

		$amount = 0.0;
		if ($mode === 'charge') {
			$amount = $this->billingAmount($this->input->post('amount'));
			if ($amount === false) {
				return $this->billingJson(array('success' => false, 'error' => 'Enter a payment amount greater than zero.'));
			}
			$init = $this->helcim_lib->initialize_checkout($amount, null, array(
				'description' => $this->billingDescription($customer, $note),
			));
		} else {
			// Vaulting only — no money moves.
			$init = $this->helcim_lib->initialize_card_verification(array(
				'description' => $this->billingDescription($customer, 'card on file'),
			));
		}

		if (empty($init['success'])) {
			// Staff-only screen: show what Helcim actually said, or nobody can
			// tell a missing scope from a declined card.
			log_message('error', 'Admin billing init failed: ' . $init['error']);
			$error = 'Helcim could not start this: ' . $init['error'];
			if ($mode === 'verify') {
				$error .= ' If your account does not allow zero-dollar card verification, take the payment instead — saving the card is part of that.';
			}

			return $this->billingJson(array('success' => false, 'error' => $error));
		}

		$this->session->set_userdata('helcim_admin_pending', array(
			'checkoutToken' => $init['checkoutToken'],
			'secretToken'   => $init['secretToken'],
			'user_id'       => $user_id,
			'listing_id'    => $listing_id,
			'mode'          => $mode,
			'amount'        => $amount,
			'currency'      => $this->helcim_lib->currency(),
			'note'          => $note,
			'save_card'     => $save,
			'admin_id'      => $this->billingAdminId(),
		));

		return $this->billingJson(array('success' => true, 'checkoutToken' => $init['checkoutToken']));
	}

	/**
	 * AJAX — finalize what billing_card_init() started: verify the hash, vault
	 * the card, and record the payment if there was one.
	 * URL: /admin/billing-card-save (POST)
	 */
	public function billing_card_save()
	{
		if ($this->input->method() !== 'post') {
			return $this->billingJson(array('success' => false, 'error' => 'Not allowed.'));
		}

		$pending = $this->session->userdata('helcim_admin_pending');
		if (!$pending || empty($pending['secretToken'])) {
			return $this->billingJson(array('success' => false, 'error' => 'This session expired. Please start again.'));
		}

		$checkout_token   = (string) $this->input->post('checkoutToken');
		$transaction_json = (string) $this->input->post('transaction_json');
		$hash             = (string) $this->input->post('hash');

		if ($checkout_token === '' || $checkout_token !== $pending['checkoutToken']) {
			return $this->billingJson(array('success' => false, 'error' => 'Payment token mismatch.'));
		}

		$this->load->library('helcim_lib');
		if (!$this->helcim_lib->validate_transaction($transaction_json, $hash, $pending['secretToken'])) {
			// Never log the payload itself — it carries card details.
			log_message('error', 'Admin billing hash validation failed for checkoutToken ' . $checkout_token);

			return $this->billingJson(array(
				'success' => false,
				'error'   => 'We could not verify that response from Helcim. Check the Helcim dashboard before trying again.',
			));
		}

		$txn = json_decode($transaction_json, true);
		$txn = is_array($txn) ? $txn : array();

		$helcim_txn_id = isset($txn['transactionId']) ? $txn['transactionId'] : (isset($txn['id']) ? $txn['id'] : null);
		$approval_code = isset($txn['approvalCode']) ? $txn['approvalCode'] : null;
		$card_token    = isset($txn['cardToken']) ? $txn['cardToken'] : null;
		$details       = $this->billingCardDetails($txn);

		$user_id    = (int) $pending['user_id'];
		$listing_id = isset($pending['listing_id']) ? (int) $pending['listing_id'] : 0;
		$customer   = $this->adminmodel->getBillingCustomer($listing_id);
		$saved    = false;
		$charged  = false;

		// One-time consume: clear it before doing the work so a replayed post
		// cannot record the same payment twice.
		$this->session->unset_userdata('helcim_admin_pending');

		if ($pending['mode'] === 'charge') {
			$txn_id = $this->adminmodel->recordManualCharge(array(
				'user_id'               => $user_id,
				'amount'                => (float) $pending['amount'],
				'currency'              => $pending['currency'],
				'plan_id'               => $customer && !empty($customer->plan_id) ? (int) $customer->plan_id : null,
				'subscription_id'       => $customer && !empty($customer->subscription_id) ? (int) $customer->subscription_id : null,
				'listing_id'            => $listing_id ?: null,
				'helcim_transaction_id' => $helcim_txn_id,
				'helcim_approval_code'  => $approval_code,
				'helcim_card_token'     => $card_token,
				'created_by_admin_id'   => (int) $pending['admin_id'],
				'note'                  => $pending['note'],
			));
			$charged = (bool) $txn_id;

			if (!$charged) {
				// The card WAS charged; only our bookkeeping failed. Say so
				// plainly rather than inviting a second attempt.
				log_message('error', 'Admin billing: charge succeeded but recording failed for user ' . $user_id
					. ' (Helcim txn ' . $helcim_txn_id . ')');

				return $this->billingJson(array(
					'success' => false,
					'error'   => 'The card was charged (Helcim transaction ' . $helcim_txn_id
						. ') but we could not record it here. Do NOT charge again — note that transaction id and contact support.',
				));
			}
		}

		$wanted_save = ($pending['mode'] === 'verify' || !empty($pending['save_card']));

		if ($card_token && $wanted_save) {
			$saved = (bool) $this->adminmodel->vaultCustomerCard($user_id, array(
				'helcim_card_token' => $card_token,
				'card_last4'        => $details['card_last4'],
				'card_brand'        => $details['card_brand'],
				'card_holder_name'  => $details['card_holder_name'],
				'card_source'       => 'admin',
				'added_by_admin_id' => (int) $pending['admin_id'],
			));
		}

		$message = array();
		if ($charged) {
			$message[] = 'Charged ' . $pending['currency'] . ' ' . number_format((float) $pending['amount'], 2) . '.';
		}
		// Three different outcomes hide behind "not saved", and telling staff
		// the wrong one sends them down the wrong path: a missing token means
		// use the other flow, a failed write means the card is genuinely not
		// on file yet.
		if ($wanted_save) {
			if ($saved) {
				$message[] = 'Card saved for recurring billing.';
			} elseif (!$card_token) {
				$message[] = ($pending['mode'] === 'verify')
					? 'Helcim approved the card but returned no token to store, so nothing was saved. Take the payment with "Enter a card & charge" instead — that always returns one.'
					: 'Helcim returned no card token, so nothing was saved for future renewals.';
			} else {
				$message[] = 'The card was accepted by Helcim but could not be saved here, so recurring billing still has no card. Has migration 0028 been run?';
			}
		}

		$this->session->set_flashdata(($charged || $saved) ? 'message' : 'error', implode(' ', $message));

		// Tell the team. Never blocks the response: the money has already moved.
		$this->billingNotify($customer, array(
			'charged'   => $charged ? (float) $pending['amount'] : null,
			'currency'  => $pending['currency'],
			'note'      => $pending['note'],
			'card_saved' => $saved,
			'card_last4' => $details['card_last4'],
			'card_brand' => $details['card_brand'],
			'txn'       => $helcim_txn_id,
		));

		return $this->billingJson(array(
			'success'  => true,
			'redirect' => base_url('admin/billing-customer/' . $listing_id),
		));
	}

	/**
	 * Charge the card already on file — no card entry, no browser involved.
	 * This is the one-click path for "run this month's bill".
	 * URL: /admin/billing-charge (POST)
	 */
	public function billing_charge()
	{
		$listing_id = (int) $this->input->post('listing_id');
		$back       = 'admin/billing-customer/' . $listing_id;

		if ($this->input->method() !== 'post') {
			redirect($back);
			return;
		}

		$customer = $this->adminmodel->getBillingCustomer($listing_id);
		if (!$customer || empty($customer->user_id)) {
			$this->session->set_flashdata('error', 'That business no longer exists, or has no owner account to bill.');
			redirect('admin/billing');
			return;
		}
		$user_id = (int) $customer->user_id;

		$amount = $this->billingAmount($this->input->post('amount'));
		if ($amount === false) {
			$this->session->set_flashdata('error', 'Enter a payment amount greater than zero.');
			redirect($back);
			return;
		}

		$token = $this->usermodel->getVaultedCardToken($user_id);
		if (!$token) {
			$this->session->set_flashdata('error', 'There is no card on file for this customer yet — add one first.');
			redirect($back);
			return;
		}

		$this->load->library('helcim_lib');
		$note     = trim((string) $this->input->post('note', TRUE));
		$currency = !empty($customer->currency) ? $customer->currency : $this->helcim_lib->currency();

		$res = $this->helcim_lib->charge_vaulted_card($token, $amount, array(
			'currency'    => $currency,
			'description' => $this->billingDescription($customer, $note),
		));

		if (empty($res['success'])) {
			$this->session->set_flashdata('error', 'The card was declined or the charge failed: ' . $res['error']);
			redirect($back);
			return;
		}

		$txn_id = $this->adminmodel->recordManualCharge(array(
			'user_id'               => $user_id,
			'amount'                => $amount,
			'currency'              => $currency,
			'plan_id'               => !empty($customer->plan_id) ? (int) $customer->plan_id : null,
			'subscription_id'       => !empty($customer->subscription_id) ? (int) $customer->subscription_id : null,
			'listing_id'            => $listing_id ?: null,
			'helcim_transaction_id' => $res['transactionId'],
			'helcim_approval_code'  => $res['approvalCode'],
			'helcim_card_token'     => $token,
			'created_by_admin_id'   => $this->billingAdminId(),
			'note'                  => $note,
		));

		if (!$txn_id) {
			log_message('error', 'Admin billing: vaulted charge succeeded but recording failed for user ' . $user_id
				. ' (Helcim txn ' . $res['transactionId'] . ')');
			$this->session->set_flashdata('error', 'The card was charged (Helcim transaction ' . $res['transactionId']
				. ') but we could not record it here. Do NOT charge again — note that transaction id and contact support.');
		} else {
			$this->session->set_flashdata('message', 'Charged ' . $currency . ' ' . number_format($amount, 2)
				. ' to the card on file. Helcim transaction ' . $res['transactionId'] . '.');
		}

		$this->billingNotify($customer, array(
			'charged'  => $amount,
			'currency' => $currency,
			'note'     => $note,
			'txn'      => $res['transactionId'],
		));

		redirect($back);
	}

	/** Remove a stored card. URL: /admin/billing-card-delete (POST) */
	public function billing_card_delete()
	{
		$listing_id = (int) $this->input->post('listing_id');
		$card_id    = (int) $this->input->post('card_id');
		$back       = 'admin/billing-customer/' . $listing_id;

		$customer = $listing_id ? $this->adminmodel->getBillingCustomer($listing_id) : null;
		$user_id  = ($customer && !empty($customer->user_id)) ? (int) $customer->user_id : 0;

		if ($this->input->method() !== 'post' || !$user_id || !$card_id) {
			redirect($back);
			return;
		}

		if ($this->adminmodel->deleteCustomerCard($card_id, $user_id)) {
			$this->session->set_flashdata('message', 'Card removed. Recurring charges will fail until another card is on file.');
		} else {
			$this->session->set_flashdata('error', 'That card could not be removed.');
		}

		redirect($back);
	}

	/**
	 * Save the recurring schedule.
	 *
	 * An ACTIVE subscription is rescheduled in place. Anything else — expired,
	 * cancelled, or no subscription at all — starts a new term on the chosen
	 * plan, because Cli::renew_subscriptions only ever looks at active rows:
	 * switching auto-renew on for an expired subscription would look like it
	 * worked and then never charge anything.
	 * URL: /admin/billing-recurring-save (POST)
	 */
	public function billing_recurring_save()
	{
		$listing_id = (int) $this->input->post('listing_id');
		$back       = 'admin/billing-customer/' . $listing_id;

		if ($this->input->method() !== 'post') {
			redirect($back);
			return;
		}

		$customer = $this->adminmodel->getBillingCustomer($listing_id);
		if (!$customer || empty($customer->user_id)) {
			$this->session->set_flashdata('error', 'That business no longer exists, or has no owner account to bill.');
			redirect('admin/billing');
			return;
		}
		$user_id = (int) $customer->user_id;

		$auto_renew = $this->input->post('auto_renew') ? 1 : 0;
		$next_date  = $this->billingDate($this->input->post('next_billing_date'));

		if ($auto_renew && !$next_date) {
			$this->session->set_flashdata('error', 'Pick the date the next charge should go out.');
			redirect($back);
			return;
		}

		/*
		 * The agreed price. Blank means "charge whatever the package costs";
		 * a figure here overrides it on every renewal, which is what makes a
		 * founding-member rate survive a later price rise on the plan.
		 */
		$custom_raw    = trim((string) $this->input->post('custom_amount'));
		$custom_amount = null;
		if ($custom_raw !== '') {
			$custom_amount = $this->billingAmount($custom_raw);
			if ($custom_amount === false) {
				$this->session->set_flashdata('error', 'That custom amount is not a valid price. Leave it blank to charge the package price.');
				redirect($back);
				return;
			}
		}

		$is_active = !empty($customer->subscription_id) && $customer->sub_status === 'active';
		$plan_name = !empty($customer->plan_name) ? $customer->plan_name : 'the current package';

		if ($is_active) {
			$ok = $this->adminmodel->updateRecurringBilling(
				(int) $customer->subscription_id, $auto_renew, $next_date, $custom_amount
			);
			$what = $auto_renew
				? 'Recurring billing is on for ' . $customer->listing_name
					. '. The next charge goes out on ' . date('M j, Y', strtotime($next_date)) . '.'
				: 'Recurring billing is off for ' . $customer->listing_name . '. Nothing further will be charged automatically.';
		} else {
			$plan_id = (int) $this->input->post('plan_id');
			$plan    = $plan_id ? $this->usermodel->getPlanByIdForPricing($plan_id) : null;
			if (!$plan) {
				$this->session->set_flashdata('error', 'Choose the package this business is on.');
				redirect($back);
				return;
			}

			// The package sets the entitlements and the fallback price; the
			// custom amount, when given, sets what is actually charged.
			$duration_id   = (int) $this->input->post('duration_id');
			$duration      = $duration_id ? $this->usermodel->getDurationById($duration_id) : null;
			$duration      = ($duration && (int) $duration->plan_id === $plan_id) ? $duration : null;
			$plan_amount   = $duration ? (float) $duration->price : (float) $plan->price;
			$duration_days = $duration
				? ((int) $duration->duration_days ?: ((int) $duration->duration_months * 30))
				: ((int) $plan->duration_days ?: 30);

			$new_id = $this->adminmodel->startSubscriptionForCustomer(array(
				'user_id'           => $user_id,
				'listing_id'        => $listing_id,
				'plan_id'           => $plan_id,
				'duration_id'       => $duration ? (int) $duration->id : null,
				'duration_days'     => $duration_days,
				'auto_renew'        => $auto_renew,
				'next_billing_date' => $next_date,
				'amount'            => $custom_amount !== null ? $custom_amount : $plan_amount,
				'custom_amount'     => $custom_amount,
				'currency'          => !empty($customer->currency) ? $customer->currency : 'USD',
			));
			$ok = (bool) $new_id;
			$plan_name = $plan->name;

			// Tie the payment staff just took to the term it paid for. Only a
			// payment from the last hour counts: the intended case is "took the
			// money, now start the plan", and a customer's unrelated purchase from
			// three months ago must not be re-attached to a term it did not buy.
			if ($ok) {
				$recent = $this->adminmodel->getListingTransactions($listing_id, $user_id, 1);
				if (!empty($recent)
					&& empty($recent[0]->subscription_id)
					&& !empty($recent[0]->created_at)
					&& strtotime($recent[0]->created_at) >= (time() - 3600)) {
					$this->adminmodel->linkTransactionToSubscription((int) $recent[0]->id, (int) $new_id);
				}
			}

			$what = $auto_renew
				? 'Started ' . $plan->name . ' for ' . $customer->listing_name
					. '. The next charge goes out on ' . date('M j, Y', strtotime($next_date)) . '.'
				: 'Started ' . $plan->name . ' for ' . $customer->listing_name . ' with recurring billing off.';
		}

		if (!$ok) {
			$this->session->set_flashdata('error', 'Those billing settings could not be saved.');
			redirect($back);
			return;
		}

		if ($custom_amount !== null) {
			$what .= ' Each renewal charges the agreed ' . number_format($custom_amount, 2)
				. ' rather than the package price.';
		}
		// A date already past is legitimate (a catch-up charge), but staff
		// should know it goes out tonight rather than on the day they picked.
		if ($auto_renew && strtotime($next_date) <= time()) {
			$what .= ' That date has already passed, so the charge will be taken on the next nightly run.';
		}
		if ($auto_renew && !$this->usermodel->getVaultedCardToken($user_id)) {
			$what .= ' There is still no card on file — add one, or the renewal will fail.';
		}

		$this->billingNotify($customer, array(
			'schedule'   => $what,
			'plan'       => $plan_name,
			'auto_renew' => $auto_renew,
			'next_date'  => $auto_renew ? $next_date : null,
			'custom'     => $custom_amount,
			'currency'   => !empty($customer->currency) ? $customer->currency : 'USD',
		));

		$this->session->set_flashdata('message', $what);
		redirect($back);
	}

	/**
	 * Email the team whenever money or a billing arrangement moves.
	 *
	 * Recipients are every active admin plus `billing_notify_emails` at
	 * /admin/setting — Mailer::billing_recipients() owns that list. Wrapped so a
	 * mail failure can never turn a successful charge into an error: the card has
	 * already been charged by the time this runs.
	 */
	private function billingNotify($customer, array $facts)
	{
		try {
			$this->load->library('mailer');

			$business = $customer && !empty($customer->listing_name) ? $customer->listing_name : 'a customer';
			$currency = !empty($facts['currency']) ? $facts['currency'] : 'USD';
			$rows     = array();

			$rows[] = array('Business', $business);
			if ($customer && !empty($customer->name)) {
				$rows[] = array('Account', $customer->name . ' (' . $customer->email_id . ')');
			}

			$subject = 'Pearland411 billing — ' . $business;
			$heading = 'Billing updated';
			$intro   = '';

			if (isset($facts['charged']) && $facts['charged'] !== null) {
				$heading = 'Payment received';
				$subject = 'Pearland411 payment — ' . $currency . ' '
					. number_format((float) $facts['charged'], 2) . ' — ' . $business;
				$intro   = 'A payment was taken by staff in the admin.';
				$rows[]  = array('Amount', $currency . ' ' . number_format((float) $facts['charged'], 2));
				if (!empty($facts['note'])) {
					$rows[] = array('For', $facts['note']);
				}
				if (!empty($facts['txn'])) {
					$rows[] = array('Helcim transaction', $facts['txn']);
				}
			}

			if (!empty($facts['card_saved'])) {
				$card = trim(($facts['card_brand'] ?? '') . ' ••••' . ($facts['card_last4'] ?? ''));
				$rows[] = array('Card on file', $card !== '••••' ? $card : 'saved');
				if (!isset($facts['charged']) || $facts['charged'] === null) {
					$heading = 'Card added';
					$subject = 'Pearland411 card on file — ' . $business;
					$intro   = 'A card was put on file for recurring billing.';
				}
			}

			if (!empty($facts['schedule'])) {
				$heading = 'Recurring billing updated';
				$subject = 'Pearland411 recurring billing — ' . $business;
				$intro   = $facts['schedule'];
				$rows[]  = array('Package', !empty($facts['plan']) ? $facts['plan'] : '—');
				$rows[]  = array('Auto-renew', !empty($facts['auto_renew']) ? 'On' : 'Off');
				if (!empty($facts['next_date'])) {
					$rows[] = array('Next charge', date('M j, Y', strtotime($facts['next_date'])));
				}
				if (isset($facts['custom']) && $facts['custom'] !== null) {
					$rows[] = array('Agreed price', $currency . ' ' . number_format((float) $facts['custom'], 2));
				}
			}

			$admin = $this->session->userdata('logged_admin');
			if (!empty($admin['name'])) {
				$rows[] = array('Done by', $admin['name']);
			}

			$body = $this->billingFactsTable($rows);
			if ($customer && !empty($customer->listing_id)) {
				$body .= '<p style="margin:18px 0 0;"><a href="'
					. base_url('admin/billing-customer/' . (int) $customer->listing_id)
					. '" style="color:#79B82A; font-weight:600;">Open this business in admin billing &rarr;</a></p>';
			}

			$this->mailer->notify_billing($subject, $heading, $body, array(
				'intro'     => $intro,
				'preheader' => $business . ' — ' . strtolower($heading),
			));
		} catch (Exception $e) {
			log_message('error', 'Billing notification failed: ' . $e->getMessage());
		}
	}

	/** Label/value rows as an email-safe table. */
	private function billingFactsTable(array $rows)
	{
		$html = '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;">';
		foreach ($rows as $row) {
			$html .= '<tr>'
				. '<td style="padding:7px 12px 7px 0; color:#5A6472; font-size:14px; white-space:nowrap; vertical-align:top;">'
				. e($row[0]) . '</td>'
				. '<td style="padding:7px 0; color:#1F2430; font-size:14px; font-weight:600;">'
				. e($row[1]) . '</td>'
				. '</tr>';
		}

		return $html . '</table>';
	}

	/*=========================
		IT / Web services catalog (admin side)
		See database/migrations/0002_services.sql.
		==========================*/

	public function services_list()
	{
		$data = array();
		$data['services'] = $this->adminmodel->getAllServices();
		$this->load->view('admin/header');
		$this->load->view('admin/services_list', $data);
		$this->load->view('admin/footer');
	}

	public function add_service()
	{
		if ($this->input->method() === 'post') {
			if ($this->_saveServiceFromPost(null)) {
				$this->session->set_flashdata('message', 'Service added.');
				redirect('admin/services');
				return;
			}
		}
		$data = array('service' => null);
		$this->load->view('admin/header');
		$this->load->view('admin/service_edit', $data);
		$this->load->view('admin/footer');
	}

	public function edit_service($id = 0)
	{
		$service = $this->adminmodel->getServiceById($id);
		if (!$service) { show_404(); return; }

		if ($this->input->method() === 'post') {
			if ($this->_saveServiceFromPost((int) $id)) {
				$this->session->set_flashdata('message', 'Service updated.');
				redirect('admin/services');
				return;
			}
		}
		$data = array('service' => $service);
		$this->load->view('admin/header');
		$this->load->view('admin/service_edit', $data);
		$this->load->view('admin/footer');
	}

	public function delete_service($id = 0)
	{
		$this->adminmodel->deleteService((int) $id);
		$this->session->set_flashdata('message', 'Service deleted.');
		redirect('admin/services');
	}

	private function _saveServiceFromPost($id)
	{
		$this->form_validation->set_rules('name', 'Name', 'trim|required|min_length[2]|max_length[160]');
		$this->form_validation->set_rules('slug', 'Slug', 'trim|required|alpha_dash|max_length[160]');
		$this->form_validation->set_rules('short_description', 'Short description', 'trim|required|max_length[500]');
		$this->form_validation->set_rules('price_unit', 'Price unit', 'trim|required');

		if (!$this->form_validation->run()) {
			$this->session->set_flashdata('error', strip_tags(validation_errors()));
			return false;
		}

		$allowed_units = array('one_time','monthly','annual','project','hourly','custom');
		$unit = $this->input->post('price_unit');
		if (!in_array($unit, $allowed_units, true)) $unit = 'custom';

		$base_price = $this->input->post('base_price');
		$base_price = ($base_price === '' || $base_price === null) ? null : round((float) $base_price, 2);

		$data = array(
			'name'              => $this->input->post('name'),
			'slug'              => $this->input->post('slug'),
			'short_description' => $this->input->post('short_description'),
			'body'              => $this->input->post('body'),
			'icon'              => $this->input->post('icon'),
			'category'          => $this->input->post('category'),
			'base_price'        => $base_price,
			'price_unit'        => $unit,
			'sort_order'        => (int) $this->input->post('sort_order'),
			'is_active'         => $this->input->post('is_active') ? 1 : 0,
		);

		if ($id === null) {
			return (bool) $this->adminmodel->insertService($data);
		}
		return (bool) $this->adminmodel->updateService($id, $data);
	}

	public function service_requests_list()
	{
		$data = array();
		$status = $this->input->get('status');
		$data['selected_status'] = $status;
		$data['requests'] = $this->adminmodel->getAllServiceRequests($status, 100);
		$this->load->view('admin/header');
		$this->load->view('admin/service_requests_list', $data);
		$this->load->view('admin/footer');
	}

	public function service_request_view($id = 0)
	{
		$request = $this->adminmodel->getServiceRequestById($id);
		if (!$request) { show_404(); return; }

		if ($this->input->method() === 'post') {
			$this->adminmodel->updateServiceRequestStatus(
				(int) $id,
				$this->input->post('status'),
				$this->input->post('admin_notes')
			);
			$this->session->set_flashdata('message', 'Request updated.');
			redirect('admin/service-requests/' . (int) $id);
			return;
		}

		$data = array('request' => $request);
		$this->load->view('admin/header');
		$this->load->view('admin/service_request_view', $data);
		$this->load->view('admin/footer');
	}

	public function service_request_delete($id = 0)
	{
		$this->adminmodel->deleteServiceRequest((int) $id);
		$this->session->set_flashdata('message', 'Request deleted.');
		redirect('admin/service-requests');
	}

	/*=========================
		Newsletter campaigns (admin side)
		See database/migrations/0003_newsletter_campaigns.sql.
		==========================*/

	public function campaigns_list()
	{
		$data = array();
		$data['campaigns']      = $this->adminmodel->getAllCampaigns();
		$data['subscribers']    = $this->adminmodel->countAllNewsletterSubscribers();
		$data['needs_migration'] = !$this->db->table_exists('newsletter_campaigns');
		$this->load->view('admin/header');
		$this->load->view('admin/campaigns_list', $data);
		$this->load->view('admin/footer');
	}

	public function campaign_compose($id = 0)
	{
		$campaign = $id ? $this->adminmodel->getCampaignById($id) : null;
		if ($id && !$campaign) { show_404(); return; }
		if ($campaign && $campaign->status !== 'draft') {
			$this->session->set_flashdata('error', 'This campaign has already been queued and can no longer be edited.');
			redirect('admin/campaigns');
			return;
		}

		if ($this->input->method() === 'post') {
			$this->form_validation->set_rules('subject',   'Subject',  'trim|required|max_length[255]');
			$this->form_validation->set_rules('body_html', 'Message',  'trim|required');

			if (!$this->form_validation->run()) {
				$this->session->set_flashdata('error', strip_tags(validation_errors()));
			} else {
				$session = $this->session->userdata('logged_admin');
				$data_in = array(
					'subject'      => $this->input->post('subject'),
					'body_html'    => $this->input->post('body_html'),
					'from_name'    => $this->input->post('from_name'),
					'from_address' => $this->input->post('from_address'),
					'created_by'   => $session ? (int) ($session['logged_id'] ?? 0) : null,
					'status'       => 'draft',
				);
				if ($campaign) {
					$this->adminmodel->updateCampaign($campaign->campaign_id, $data_in);
					$cid = (int) $campaign->campaign_id;
				} else {
					$cid = (int) $this->adminmodel->createCampaign($data_in);
				}
				$this->session->set_flashdata('message', 'Draft saved.');
				redirect('admin/campaigns/edit/' . $cid);
				return;
			}
		}

		$data = array('campaign' => $campaign);
		$this->load->view('admin/header');
		$this->load->view('admin/campaign_compose', $data);
		$this->load->view('admin/footer');
	}

	public function campaign_queue($id = 0)
	{
		$count = $this->adminmodel->queueCampaign((int) $id);
		if ($count === false) {
			$this->session->set_flashdata('error', 'Campaign cannot be queued (already sent, or not found).');
		} else {
			$this->session->set_flashdata('message', "Queued — {$count} recipients ready to send.");
		}
		redirect('admin/campaigns/view/' . (int) $id);
	}

	public function campaign_send_batch($id = 0)
	{
		$batch = max(1, min(200, (int) $this->input->post('batch_size', true) ?: 25));
		$result = $this->adminmodel->sendCampaignBatch((int) $id, $batch);
		$this->session->set_flashdata(
			'message',
			"Sent batch: {$result['sent']} delivered, {$result['failed']} failed, {$result['remaining']} remaining."
		);
		redirect('admin/campaigns/view/' . (int) $id);
	}

	public function campaign_view($id = 0)
	{
		$campaign = $this->adminmodel->getCampaignById($id);
		if (!$campaign) { show_404(); return; }
		$data = array('campaign' => $campaign);
		$this->load->view('admin/header');
		$this->load->view('admin/campaign_view', $data);
		$this->load->view('admin/footer');
	}

	public function campaign_delete($id = 0)
	{
		$this->adminmodel->deleteCampaign((int) $id);
		$this->session->set_flashdata('message', 'Campaign deleted.');
		redirect('admin/campaigns');
	}

	/*=========================
		Sub-admin / role management (super_admin only).
		Gated in the constructor via $super_admin_only.
		See database/migrations/0005_admin_roles.sql.
		==========================*/

	public function sub_admin_all()
	{
		$data['admins']  = $this->adminmodel->getAllAdmins();
		$data['current'] = $this->session->userdata('logged_admin');
		$this->load->view('admin/header');
		$this->load->view('admin/sub_admin_all', $data);
		$this->load->view('admin/footer');
	}

	public function sub_admin_create()
	{
		if ($this->input->method() === 'post') {
			$this->form_validation->set_rules('admin_name',  'Name',     'trim|required|min_length[2]|max_length[50]');
			$this->form_validation->set_rules('admin_email', 'Email',    'trim|required|valid_email|max_length[50]');
			$this->form_validation->set_rules('password',    'Password', 'required|min_length[8]');
			$this->form_validation->set_rules('role',        'Role',     'trim|required');

			if (!$this->form_validation->run()) {
				$this->session->set_flashdata('error', strip_tags(validation_errors()));
			} elseif ($this->adminmodel->adminEmailExists($this->input->post('admin_email'))) {
				$this->session->set_flashdata('error', 'An admin with that email already exists.');
			} else {
				$role = $this->input->post('role');
				if (!in_array($role, array('super_admin', 'editor'), true)) $role = 'editor';

				$id = $this->adminmodel->createAdmin(array(
					'admin_name'  => $this->input->post('admin_name'),
					'admin_email' => $this->input->post('admin_email'),
					'role'        => $role,
					'is_active'   => $this->input->post('is_active') ? 1 : 0,
				), (string) $this->input->post('password'));

				if ($id) {
					$this->session->set_flashdata('message', 'Sub-admin created.');
					redirect('admin/sub-admins');
					return;
				}
				$this->session->set_flashdata('error', 'Could not create the sub-admin.');
			}
		}

		$data = array('admin_row' => null);
		$this->load->view('admin/header');
		$this->load->view('admin/sub_admin_edit', $data);
		$this->load->view('admin/footer');
	}

	public function sub_admin_edit($id = 0)
	{
		$admin_row = $this->adminmodel->getAdminByIdRaw($id);
		if (!$admin_row) { show_404(); return; }

		if ($this->input->method() === 'post') {
			$this->form_validation->set_rules('admin_name',  'Name',  'trim|required|min_length[2]|max_length[50]');
			$this->form_validation->set_rules('admin_email', 'Email', 'trim|required|valid_email|max_length[50]');
			$this->form_validation->set_rules('role',        'Role',  'trim|required');

			if (!$this->form_validation->run()) {
				$this->session->set_flashdata('error', strip_tags(validation_errors()));
			} elseif ($this->adminmodel->adminEmailExists($this->input->post('admin_email'), (int) $id)) {
				$this->session->set_flashdata('error', 'Another admin with that email already exists.');
			} else {
				$role = $this->input->post('role');
				if (!in_array($role, array('super_admin', 'editor'), true)) $role = 'editor';

				$current  = $this->session->userdata('logged_admin');
				$self_id  = (int) ($current['logged_id'] ?? 0);

				// Refuse to let the only active super-admin demote
				// themselves or deactivate themselves.
				if ((int) $admin_row->admin_id === $self_id
					&& ($admin_row->role === 'super_admin')
					&& ($role !== 'super_admin' || !$this->input->post('is_active'))) {
					$other_super = 0;
					foreach ($this->adminmodel->getAllAdmins() as $a) {
						if ((int) $a->admin_id !== $self_id
							&& ($a->role ?? '') === 'super_admin'
							&& (int) ($a->is_active ?? 0) === 1) {
							$other_super++;
						}
					}
					if ($other_super === 0) {
						$this->session->set_flashdata('error',
							'You cannot demote or deactivate yourself while you are the only active super-admin.');
						redirect('admin/sub-admins/edit/' . (int) $id);
						return;
					}
				}

				$ok = $this->adminmodel->updateAdminMeta((int) $id, array(
					'admin_name'  => $this->input->post('admin_name'),
					'admin_email' => $this->input->post('admin_email'),
					'role'        => $role,
					'is_active'   => $this->input->post('is_active') ? 1 : 0,
				), $this->input->post('password') ?: null);

				if ($ok) {
					// Editing yourself has to refresh the session copy, which is
					// otherwise only written at login: the name would keep showing
					// the old value, and a self-demotion would leave super-admin
					// menu items on screen until logout.
					if ((int) $id === $self_id && is_array($current)) {
						$current['admin_name']  = $this->input->post('admin_name');
						$current['admin_email'] = $this->input->post('admin_email');
						$current['role']        = $role;
						$this->session->set_userdata('logged_admin', $current);
					}
					$this->session->set_flashdata('message', 'Sub-admin updated.');
					redirect('admin/sub-admins');
					return;
				}
				$this->session->set_flashdata('error', 'Could not save changes.');
			}
		}

		$data = array('admin_row' => $admin_row);
		$this->load->view('admin/header');
		$this->load->view('admin/sub_admin_edit', $data);
		$this->load->view('admin/footer');
	}

	public function sub_admin_delete($id = 0)
	{
		$current = $this->session->userdata('logged_admin');
		if ((int) ($current['logged_id'] ?? 0) === (int) $id) {
			$this->session->set_flashdata('error', 'You cannot delete the account you are signed in as.');
			redirect('admin/sub-admins');
			return;
		}
		if (!$this->adminmodel->deleteAdmin((int) $id)) {
			$this->session->set_flashdata('error', 'Cannot delete — this is the last active super-admin.');
		} else {
			$this->session->set_flashdata('message', 'Sub-admin deleted.');
		}
		redirect('admin/sub-admins');
	}
}
