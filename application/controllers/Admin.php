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
			'delete_pricing_plan', 'all_subscriptions', 'subscription_view',
			'cancel_subscription', 'delete_subscription', 'purchase_subscription',
			'current_ads', 'create_ads', 'ads_edit', 'ads_delete',
			'ads_price', 'create_ads_price', 'ads_price_edit',
			'ads_request', 'ads_history', 'ads_history_delete', 'approveAdStatus',
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
	All Category
	=====================*/

	public function all_listing_category()
	{
		// Categories are usually few — show 100 per page so the inline
		// search in admin-table.js can match against the whole list
		// without needing a server-side search round-trip.
		$config = [
			'base_url' => base_url('admin/all_listing_category'),
			'per_page' => admin_per_page(20),
			'reuse_query_string' => TRUE,
			'total_rows' => count($this->adminmodel->allListingCategory(NULL, NULL)),
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
		$data['all_listing_category']   = $this->adminmodel->allListingCategory($config['per_page'], $page);
		$data['link'] 		   = $this->pagination->create_links();

		//////////////////////////

		$data['user'] 				  = $this->adminmodel->getAdminInformation();

		// $data['all_listing_category'] = $this->adminmodel->allListingCategory();
		// echo "<pre>";var_dump($data['all_listing_category']);die();

		$this->load->view('admin/header');
		$this->load->view('admin/all_listing_category', $data);
		$this->load->view('admin/footer');
	}


	/*====================
	Add new Category
	=====================*/

	public function add_new_listing_category()
	{
		$this->form_validation->set_rules('category_name', 'Category Name', 'trim|required|is_unique[listing_categories.category_name]');

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

		$this->form_validation->set_rules('category_name', 'Category Name', 'trim|required');

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

			if ($_FILES && $_FILES['category_image']['name']) {
				$img_info = [
					'upload_path' => 'images/services',
					'quality'     => '60%',
					'height'      => 250,
					'width'       => 400,
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

			if ($this->adminmodel->updateListingCategory($id, $info)) {
				$this->session->set_flashdata('message', 'Successfully Updated Listing Category!!');
				redirect('admin/all_listing_category', 'refresh');
			} else {
				$this->session->set_flashdata('message', 'Sorry, Update Listing Category failed!!');
				redirect('admin/edit_listing_category', 'refresh');
			}
		}
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

		// Initialize data array
		$data = array();
		$data['link'] = '';

		// Get category ID from URL if present
		$id = $this->input->get('category_id');

		if (!empty($id)) {
			$data['all_listing_sub_category'] = $this->adminmodel->ListingSubCategoryByCateId($id);
		} else {
			// Get total record count
			$total_rows = $this->adminmodel->countAllListingSubCategory();

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
			$data['all_listing_sub_category'] = $this->adminmodel->allListingSubCategory($config['per_page'], $offset);
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
					'featured_listing' => $this->input->post('featured_listing'),
					'spotlight_listing' => $this->input->post('spotlight_listing'),
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
					$this->session->set_flashdata('message', 'Listing added successfully.');
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

		if ($this->form_validation->run('admin_listing_scratch') == FALSE) {
			$this->load->view('admin/header', $data);
			$this->load->view('admin/edit_listings');
			$this->load->view('admin/footer');
		} else {
			$listing_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('listing_name'));
			$info = array(
				'user_id' => $this->input->post('user_id'),
				'listing_name' => $this->input->post('listing_name'),
				'featured_listing' => $this->input->post('featured_listing'),
				'spotlight_listing' => $this->input->post('spotlight_listing'),
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

			if ($_FILES && $_FILES['listing_profile_image']['name']) {
				$img_info = [
					'upload_path' => 'images/listings',
					'quality'     => '60%',
					'image_name'  => 'listing_profile_image'
				];
				$info['listing_image'] = uploadWithoutResize($img_info);
			}

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
				$this->session->set_flashdata('message', 'Listing updated successfully.');
				redirect('admin/all-listings');
			} else {
				$this->session->set_flashdata('error', 'Could not update the listing. ' . $this->adminmodel->lastDbError());
				redirect('admin/edit-listing/' . rawurlencode((string) $slug));
			}
		}
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

			if ($this->adminmodel->add('products', $info)) {
				$this->session->set_flashdata('message', 'Successfully Product Added!!');
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
				'featured_news'  	=> $this->input->post('featured_news'),
				'news_slug'			=> $news_slug
			);

			if ($_FILES && $_FILES['news_image']['name']) {
				$img_info = [
					'upload_path' => 'images/news',
					'quality'     => '60%',
					'height'      => 500,
					'width'       => 1000,
					'image_name'  => 'news_image'
				];
				$info['news_image'] = uploadAndResize($img_info);
			}

			if ($this->adminmodel->add('news', $info)) {
				$this->session->set_flashdata('message', 'Successfully Added News!!');
				redirect('admin/news-list');
			} else {
				$this->session->set_flashdata('error', 'Could not save the news article. ' . $this->adminmodel->lastDbError());
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
				'featured_news'  => $this->input->post('featured_news')
			);

			if ($_FILES && $_FILES['news_image']['name']) {
				$img_info = [
					'upload_path' => 'images/news',
					'quality'     => '60%',
					'height'      => 500,
					'width'       => 1000,
					'image_name'  => 'news_image'
				];

				$image_link = $old_image;

				if ($image_link != null) {
					if (file_exists('./' . $image_link)) {
						unlink($image_link);
					}
				}

				$info['news_image'] = uploadAndResize($img_info);
			} else {
				$info['news_image'] = $old_image;
			}

			if ($this->adminmodel->update($slug, $info, 'news_slug', 'news')) {
				$this->session->set_flashdata('message', 'Successfully Updated News!!');
				redirect('admin/news-list', 'refresh');
			} else {
				$this->session->set_flashdata('message', 'Sorry, Update News Failed!!');
				redirect('admin/edit-news' . $slug, 'refresh');
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

		$page = ($this->uri->segment(3)) ? (int) $this->uri->segment(3) : 0;
		// When pagination uses offset in the URI segment, $page is already the offset.
		$offset = $page;
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
		$old_image        = $data['category']->category_image;

		$this->form_validation->set_rules('category_name', 'Category Name', 'trim|required');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/edit_video_category', $data);
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
					'upload_path' => 'images/video',
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

			// updateVideoCategory(), not updatemarketplaceCategory(). This used
			// to call the marketplace one, which updates a different TABLE
			// (marketplace_categories) on a different KEY (category_id, an INT,
			// compared against a slug string that MySQL casts to 0). Nothing
			// ever matched — but CI3's update() returns TRUE whenever the query
			// runs, so renaming a video category reported "Successfully Updated"
			// every time while the name never changed.
			if ($this->adminmodel->updateVideoCategory($slug, $info)) {
				$this->session->set_flashdata('message', 'Video category updated.');
				redirect('admin/all_video_category', 'refresh');
			} else {
				$this->session->set_flashdata('error', 'Sorry, updating the video category failed. ' . $this->adminmodel->lastDbError());
				redirect('admin/edit_video_category/' . rawurlencode((string) $slug), 'refresh');
			}
		}
	}
	/*====================
			Delete Video Category
			=====================*/
	public function delete_video_category($slug)
	{
		if ($this->adminmodel->deleteVideoCategory($slug)) {
			$this->session->set_flashdata('message', 'Category has been Permanently Deleted!!!');
			redirect('admin/all_video_category', 'refresh');
		} else {
			redirect('admin/all_video_category', 'refresh');
		}
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
				'featured_video'    		=> $this->input->post('featured_video'),
				'video_description'  => $this->input->post('video_description'),
				'video_category_id'  => $this->input->post('video_category_id'),
				'video_seo_title'  => $this->input->post('video_seo_title'),
				'video_seo_description'  => $this->input->post('video_seo_description'),
				'video_seo_keywords'  => $this->input->post('video_seo_keywords'),
				'video_slug'  => $video_slug
			);

			if ($_FILES && $_FILES['video_image']['name']) {
				$img_info = [
					'upload_path' => 'images/video',
					'quality'     => '60%',
					'image_name'  => 'video_image'
				];
				$info['video_image'] = uploadAndResize($img_info);
			}

			if ($this->adminmodel->add('video', $info)) {
				$this->session->set_flashdata('message', 'Successfully Added Video!!');
				redirect('admin/video-list');
			} else {
				$this->session->set_flashdata('error', 'Could not save the video. ' . $this->adminmodel->lastDbError());
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
				'featured_video'    		=> $this->input->post('featured_video'),
				'video_description'  => $this->input->post('video_description'),
				'video_category_id'  => $this->input->post('video_category_id'),
				'video_seo_title'  => $this->input->post('video_seo_title'),
				'video_seo_description'  => $this->input->post('video_seo_description'),
				'video_seo_keywords'  => $this->input->post('video_seo_keywords')
			);

			if ($_FILES && $_FILES['video_image']['name']) {
				$img_info = [
					'upload_path' => 'images/video',
					'quality'     => '60%',
					'image_name'  => 'video_image'
				];

				$image_link = $old_image;

				if ($image_link != null) {
					if (file_exists('./' . $image_link)) {
						unlink($image_link);
					}
				}

				$info['video_image'] = uploadWithoutResize($img_info);
			} else {
				$info['video_image'] = $old_image;
			}

			if ($this->adminmodel->update($slug, $info, 'video_slug', 'video')) {
				$this->session->set_flashdata('message', 'Successfully Updated Video!!');
				redirect('admin/video-list', 'refresh');
			} else {
				$this->session->set_flashdata('message', 'Sorry, Update Video Failed!!');
				redirect('admin/edit-video' . $slug, 'refresh');
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
				'featured_classifieds'  	  => $this->input->post('featured_classifieds'),
				'spotlight_classifieds'  	  => $this->input->post('spotlight_classifieds'),
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
				'featured_classifieds'  	  => $this->input->post('featured_classifieds'),
				'spotlight_classifieds'  	  => $this->input->post('spotlight_classifieds'),
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
				'featured_marketplace'  	  => $this->input->post('featured_marketplace'),
				'spotlight_marketplace'  	  => $this->input->post('spotlight_marketplace'),
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
				'featured_marketplace'  	  => $this->input->post('featured_marketplace'),
				'spotlight_marketplace'  	  => $this->input->post('spotlight_marketplace'),
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
				'isenquiry'         => $this->input->post('isenquiry'),
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

		// Notify the claimant.
		if (!empty($claim->enquiry_email)) {
			$this->load->library('mailer');
			$listing_url = base_url('listing-details/' . rawurlencode((string) $listing->listing_slug));
			$body = '<p>Good news &mdash; your claim for <strong>'
				. html_escape($listing->listing_name) . '</strong> has been approved.</p>'
				. '<p>The listing is now linked to your account. Sign in and visit '
				. '<a href="' . base_url('user/dashboard') . '">your dashboard</a> '
				. 'to edit photos, hours, and contact information.</p>'
				. '<p>Public page: <a href="' . $listing_url . '">' . $listing_url . '</a></p>';
			$this->mailer->send($claim->enquiry_email, 'Your listing claim was approved', $body);
		}

		$this->session->set_flashdata('message', 'Claim approved and listing transferred.');
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
				'ad_start_date'   => date('Y-m-d h:i:s', strtotime($this->input->post('ad_start_date'))),
				'ad_end_date'     => date('Y-m-d h:i:s', strtotime($this->input->post('ad_end_date'))),
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
				'ad_start_date'   => date('Y-m-d h:i:s', strtotime($this->input->post('ad_start_date'))),
				'ad_end_date'     => date('Y-m-d h:i:s', strtotime($this->input->post('ad_end_date'))),
				'ad_link'         => $this->input->post('ad_link'),
				'ad_total_days'   => $this->input->post('ad_total_days'),
				'ad_cost_per_day' => $this->input->post('ad_cost_per_day'),
				'ad_total_cost'   => $this->input->post('ad_total_cost')
			);

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

		// Try to get pricing plans, fallback to empty array if fails
		try {
			$data['pricing_plans'] = $this->adminmodel->getAllPricingPlans();
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
		$data = array();
		$data['pricing_plans'] = $this->adminmodel->getAllPricingPlans();
		$this->load->view('admin/header');
		$this->load->view('admin/pricing', $data);
		$this->load->view('admin/footer');
	}

	/*====================
		Add Pricing Plan
	=====================*/
	public function add_pricing_plan()
	{
		// Only require a plan name from the modal; price/duration managed via Durations editor
		$this->form_validation->set_rules('plan_name', 'Plan Name', 'trim|required');

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('error', validation_errors());
		} else {
			$data = array(
				'name' => $this->input->post('plan_name'),
				// keep price/duration optional with safe defaults for backward compatibility
				'price' => $this->input->post('plan_price') !== null ? $this->input->post('plan_price') : 0,
				'currency' => $this->input->post('currency') !== null ? $this->input->post('currency') : 'USD',
				'billing_interval' => $this->input->post('billing_interval') !== null ? $this->input->post('billing_interval') : 'monthly',
				'duration_days' => $this->input->post('duration_days') !== null ? $this->input->post('duration_days') : 30,
				'category' => $this->input->post('category') ? $this->input->post('category') : 'business-listing',
				'description' => $this->input->post('description'),
				'listing_limit' => $this->input->post('listing_limit'),
				'product_limit' => $this->input->post('product_limit'),
				'event_limit' => $this->input->post('event_limit'),
				'classified_limit' => $this->input->post('classified_limit'),
				'blog_limit' => $this->input->post('blog_limit'),
				'news_limit' => $this->input->post('news_limit'),
				'video_limit' => $this->input->post('video_limit'),
				'rental_limit' => $this->input->post('rental_limit'),
				'house_limit' => $this->input->post('house_limit'),
				'ad_limit' => $this->input->post('ad_limit'),
				'ad_boost_limit' => $this->input->post('ad_boost_limit'),
				'placeholder_images_limit' => $this->input->post('placeholder_images_limit'),
				'featured_listing' => $this->input->post('featured_listing') ? 1 : 0,
				'priority_support' => $this->input->post('priority_support') ? 1 : 0,
				'analytics_access' => $this->input->post('analytics_access') ? 1 : 0,
				// Field-level listing capabilities (freemium editing)
				'can_photos' => $this->input->post('can_photos') ? 1 : 0,
				'photo_limit' => $this->input->post('photo_limit') !== null ? (int) $this->input->post('photo_limit') : 0,
				'can_map' => $this->input->post('can_map') ? 1 : 0,
				'can_services' => $this->input->post('can_services') ? 1 : 0,
				'can_extra_details' => $this->input->post('can_extra_details') ? 1 : 0,
				'allow_video' => $this->input->post('allow_video') ? 1 : 0,
				'is_active' => 1,
				'sort_order' => 1,
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			);

			if ($this->adminmodel->addPricingPlan($data)) {
				$this->session->set_flashdata('message', 'Pricing plan added successfully!');
			} else {
				$this->session->set_flashdata('error', 'Failed to add pricing plan!');
			}
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
			echo json_encode(['success' => true, 'duration' => $this->adminmodel->getPlanDurationById($id)]);
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
		// Only require name; price/duration are edited in Durations UI
		$this->form_validation->set_rules('plan_name', 'Plan Name', 'trim|required');

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('error', validation_errors());
		} else {
			$data = array(
				'name' => $this->input->post('plan_name'),
				// optional fields preserved or defaulted
				'price' => $this->input->post('plan_price') !== null ? $this->input->post('plan_price') : null,
				'currency' => $this->input->post('currency') !== null ? $this->input->post('currency') : null,
				'billing_interval' => $this->input->post('billing_interval') !== null ? $this->input->post('billing_interval') : 'monthly',
				'duration_days' => $this->input->post('duration_days') !== null ? $this->input->post('duration_days') : null,
				'category' => $this->input->post('category') ? $this->input->post('category') : 'business-listing',
				'description' => $this->input->post('description'),
				'listing_limit' => $this->input->post('listing_limit'),
				'product_limit' => $this->input->post('product_limit'),
				'event_limit' => $this->input->post('event_limit'),
				'classified_limit' => $this->input->post('classified_limit'),
				'blog_limit' => $this->input->post('blog_limit'),
				'news_limit' => $this->input->post('news_limit'),
				'video_limit' => $this->input->post('video_limit'),
				'rental_limit' => $this->input->post('rental_limit'),
				'house_limit' => $this->input->post('house_limit'),
				'ad_limit' => $this->input->post('ad_limit'),
				'ad_boost_limit' => $this->input->post('ad_boost_limit'),
				'placeholder_images_limit' => $this->input->post('placeholder_images_limit'),
				'featured_listing' => $this->input->post('featured_listing') ? 1 : 0,
				'priority_support' => $this->input->post('priority_support') ? 1 : 0,
				'analytics_access' => $this->input->post('analytics_access') ? 1 : 0,
				// Field-level listing capabilities (freemium editing)
				'can_photos' => $this->input->post('can_photos') ? 1 : 0,
				'photo_limit' => $this->input->post('photo_limit') !== null ? (int) $this->input->post('photo_limit') : 0,
				'can_map' => $this->input->post('can_map') ? 1 : 0,
				'can_services' => $this->input->post('can_services') ? 1 : 0,
				'can_extra_details' => $this->input->post('can_extra_details') ? 1 : 0,
				'allow_video' => $this->input->post('allow_video') ? 1 : 0,
				'updated_at' => date('Y-m-d H:i:s')
			);

			if ($this->adminmodel->updatePricingPlan($id, $data)) {
				$this->session->set_flashdata('message', 'Pricing plan updated successfully!');
			} else {
				$this->session->set_flashdata('error', 'Failed to update pricing plan!');
			}
		}
		redirect('admin/pricing');
	}

	/*====================
		Delete Pricing Plan
	=====================*/
	public function delete_pricing_plan($id)
	{
		if ($this->adminmodel->deletePricingPlan($id)) {
			$this->session->set_flashdata('message', 'Pricing plan deleted successfully!');
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
