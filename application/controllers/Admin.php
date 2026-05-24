<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
{

	/*====================
	Add House
	=====================*/
	public function add_house()
	{
		$data['users'] = $this->adminmodel->getAllUsers('user');
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
		$data['users'] = $this->adminmodel->getAllUsers('user');
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
			'per_page' => 10,
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
			'per_page' => 10,
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
		$public_actions = array('index', 'login', 'forgot_password', 'forget_password');
		$page = $this->router->fetch_method();

		if (!in_array($page, $public_actions, true)) {
			$admin = $this->session->userdata('logged_admin');
			if (empty($admin) || empty($admin['logged_id'])) {
				redirect(base_url('admin'));
				return;
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
			'per_page' => 10,
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
		$this->load->view('admin/header');
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

		$this->load->view('admin/dashboard', $data);
		$this->load->view('admin/footer');
	}
	/*====================
	All Category
	=====================*/

	public function all_listing_category()
	{
		$config = [
			'base_url' => base_url('admin/all_listing_category'),
			'per_page' => 10,
			'total_rows' => count($this->adminmodel->allListingCategory(NULL, NULL)),
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
			$config = [
				'base_url'         => base_url('admin/all_listing_sub_category'),
				'total_rows'       => $total_rows,
				'per_page'         => 10,
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
	/*====================
	Get Listing Sub Category
	=====================*/
	public function get_listing_sub_category()
	{

		if (!empty($this->input->post('category_id'))) {
			$category_id = $this->input->post('category_id');
			$data['sub_categories'] = $this->adminmodel->ListingSubCategoryByCateId($category_id);
			$this->load->view('admin/get_sub_categories', $data);
		}
	}
	/*====================
	All Listing
	=====================*/
	public function all_listings()
	{
		$config = [
			'base_url' => base_url('admin/all_listings'),
			'per_page' => 10,
			'total_rows' => count($this->adminmodel->getAllListings(NULL, NULL)),
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

		$page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
		$data['AllListings']   = $this->adminmodel->getAllListings($config['per_page'], $page);
		$data['start_number'] = $page + 1;

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

		$this->load->view('admin/header');
		$this->load->view('admin/add_new_listings');
		$this->load->view('admin/footer');
	}

	/*====================
	Add New Listing (Singular URL Handler)
	=====================*/
	public function add_new_listing()
	{
		// Redirect to the plural version or render the same page
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
		$data['users'] 		  	= $this->adminmodel->getAllUsers('user');
		if (!empty($this->input->post('user_id'))) {
			// Debug: Log form submission
			error_log("Form submitted with user_id: " . $this->input->post('user_id'));
			error_log("Files uploaded: " . print_r($_FILES, true));

			// Temporary: Skip validation to focus on upload logic
			$skip_validation = isset($_POST['listing_name']) && !empty($_POST['listing_name']);

			if (!$skip_validation && $this->form_validation->run('admin_listing_scratch') == FALSE) {
				error_log("Form validation failed");
				$this->load->view('admin/header', $data);
				$this->load->view('admin/add_new_listings_start');
				$this->load->view('admin/footer');
			} else {
				error_log("Processing listing (validation " . ($skip_validation ? "skipped" : "passed") . ")");
				$listing_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('listing_name'));
				$info = array(
					'user_id' => $this->input->post('user_id'),
					'listing_name' => $this->input->post('listing_name'),
					'featured_listing' => $this->input->post('featured_listing'),
					'spotlight_listing' => $this->input->post('spotlight_listing'),
					'category_id' => $this->input->post('category_id'),
					'sub_category_id' => $this->input->post('sub_category_id'),
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

				// Handle Gallery Images Upload
				if ($_FILES && isset($_FILES['listing_image_gallery']['name'][0]) && !empty($_FILES['listing_image_gallery']['name'][0])) {
					$gallery_images = [];
					$files_count = count($_FILES['listing_image_gallery']['name']);

					// Debug: Log gallery upload attempt
					error_log("Gallery upload attempt - Files count: " . $files_count);

					for ($i = 0; $i < $files_count; $i++) {
						if (!empty($_FILES['listing_image_gallery']['name'][$i])) {
							// Debug: Log each file
							error_log("Processing gallery file: " . $_FILES['listing_image_gallery']['name'][$i]);

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
								error_log("Gallery image uploaded: " . $uploaded_image);
							} else {
								error_log("Gallery image upload failed for: " . $_FILES['listing_image_gallery']['name'][$i]);
							}
						}
					}
					if (!empty($gallery_images)) {
						$info['listing_image_gallery'] = json_encode($gallery_images);
						error_log("Gallery images stored in DB: " . $info['listing_image_gallery']);
					} else {
						error_log("No gallery images to store");
					}
				} else {
					error_log("No gallery files detected in upload");
				}

				if ($this->adminmodel->addListing($info)) {
					$this->session->set_flashdata('message', 'Lisitng Added Successfully.');
					redirect('admin/all_listings', 'refresh');
				} else {
					redirect('admin/add_listing_scratch', 'refresh');
				}
			}
		}
		$this->load->view('admin/header', $data);
		$this->load->view('admin/add_new_listings_start');
		$this->load->view('admin/footer');
	}
	/*Edit Listing*/

	public function edit_listing($slug)
	{
		$data['categories']  = $this->usermodel->getAllValues('listing_categories');
		$data['users'] 		 = $this->adminmodel->getAllUsers('user');
		$data['slug'] = $slug;

		$data['listing'] 	 = $this->usermodel->getSingleValue($slug, 'listing_slug', 'listings');
		if ($data['listing']->category_id != '') {
			$data['sub_category_listing'] = $this->adminmodel->ListingSubCategoryByCateId($data['listing']->category_id);
		}

		if ($this->form_validation->run('admin_listing_scratch') == FALSE) {
			error_log("EDIT Form validation failed");
			$this->load->view('admin/header', $data);
			$this->load->view('admin/edit_listings');
			$this->load->view('admin/footer');
		} else {
			error_log("EDIT Form validation passed, processing listing update");
			error_log("EDIT Files uploaded: " . print_r($_FILES, true));
			$listing_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('listing_name'));
			$info = array(
				'user_id' => $this->input->post('user_id'),
				'listing_name' => $this->input->post('listing_name'),
				'featured_listing' => $this->input->post('featured_listing'),
				'spotlight_listing' => $this->input->post('spotlight_listing'),
				'category_id' => $this->input->post('category_id'),
				'sub_category_id' => $this->input->post('sub_category_id'),
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

			// Handle Gallery Images Upload for Edit
			if ($_FILES && isset($_FILES['listing_image_gallery']['name'][0]) && !empty($_FILES['listing_image_gallery']['name'][0])) {
				$gallery_images = [];
				$files_count = count($_FILES['listing_image_gallery']['name']);

				// Debug: Log gallery upload attempt for edit
				error_log("EDIT Gallery upload attempt - Files count: " . $files_count);

				for ($i = 0; $i < $files_count; $i++) {
					if (!empty($_FILES['listing_image_gallery']['name'][$i])) {
						// Debug: Log each file
						error_log("EDIT Processing gallery file: " . $_FILES['listing_image_gallery']['name'][$i]);

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
							error_log("EDIT Gallery image uploaded: " . $uploaded_image);
						} else {
							error_log("EDIT Gallery image upload failed for: " . $_FILES['listing_image_gallery']['name'][$i]);
						}
					}
				}
				if (!empty($gallery_images)) {
					$info['listing_image_gallery'] = json_encode($gallery_images);
					error_log("EDIT Gallery images stored in DB: " . $info['listing_image_gallery']);
				} else {
					error_log("EDIT No gallery images to store");
				}
			} else {
				error_log("EDIT No gallery files detected in upload");
			}

			if ($this->adminmodel->update($slug, $info, 'listing_slug', 'listings')) {
				$this->session->set_flashdata('message', 'Lisitng Updated Successfully.');
				redirect('admin/all-listings');
			} else {
				redirect('admin/edit-listing');
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
		//var_dump($data['RelatedListing']);

		$this->load->view('header');
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

		$data['users'] 		  	= $this->adminmodel->getAllUsers('user');
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
			'per_page' => 10,
			'total_rows' => count($this->adminmodel->getAllDeletedListings(NULL, NULL))
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


		$data['AllListings']   = $this->adminmodel->getAllDeletedListings($config['per_page'], $this->uri->segment(3));
		$data['link'] 		   = $this->pagination->create_links();


		$this->load->view('admin/header', $data);
		$this->load->view('admin/trash_listing');
		$this->load->view('admin/footer');
	}


	/*=================== 
    	Restore Listing 
	===================*/
	public function restore_listing($listing_slug)
	{
		$listings = $this->adminmodel->getSingleValue($listing_slug, 'listing_slug', 'listings');

		$listings->is_deleted 	= 0;
		$listings->deleted_date = date('Y-m-d H:i:s');

		if ($this->adminmodel->update($listing_slug, $listings, 'listing_slug', 'listings')) {
			$this->session->set_flashdata('message', 'Successfully Listing Restored!!');
			redirect('admin/trash-listing', 'refresh');
		} else {
			$this->session->set_flashdata('message', 'Listing Restore Failed!!');
			redirect('admin/trash-listing', 'refresh');
		}
	}

	/*===============================
		Listing Delete Permanently
	=================================*/

	public function listing_delete($slug)
	{
		if ($this->adminmodel->permanentDeleteListing($slug)) {
			$this->session->set_flashdata('message', 'Successfully Removed Listing!!');
			redirect('admin/trash-listing');
		} else {
			$this->session->set_flashdata('message', 'Listing Remove Failed!!');
			redirect('admin/trash-listing', 'refresh');
		}
	}

	/*====================
		Delete Listing (Soft Delete - Move to Trash)
	=====================*/
	public function delete_listing($slug)
	{
		if ($this->adminmodel->softDeleteListing($slug)) {
			$this->session->set_flashdata('message', 'Listing successfully moved to trash!');
			redirect('admin/all-listings');
		} else {
			$this->session->set_flashdata('message', 'Failed to delete listing!');
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
			'per_page'         => 10,
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
				'per_page'         => 10,
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
			'per_page' => 10,
			'total_rows' => $this->adminmodel->countAllProducts(),
			'uri_segment' => 3,
		];

		// Pagination styling
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
		$data['users']             = $this->adminmodel->getAllUsers('user');

		if ($this->form_validation->run('admin_products') == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/add_new_product', $data);
			$this->load->view('admin/footer');
		} else {
			$product_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('product_name'));
			$info = array(
				'user_id'			    => htmlentities($this->input->post('user_id'), ENT_QUOTES),
				'product_name'			=> htmlentities($this->input->post('product_name'), ENT_QUOTES),
				'category_id'			=> htmlentities($this->input->post('category_id'), ENT_QUOTES),
				'sub_category_id'		=> htmlentities($this->input->post('sub_category_id'), ENT_QUOTES),
				'product_price'			=> htmlentities($this->input->post('product_price'), ENT_QUOTES),
				'product_price_offer'	=> htmlentities($this->input->post('product_price_offer'), ENT_QUOTES),
				'product_payment_link'	=> htmlentities($this->input->post('product_payment_link'), ENT_QUOTES),
				'product_description'	=> htmlentities($this->input->post('product_description'), ENT_QUOTES),
				'product_tags'			=> htmlentities($this->input->post('product_tags'), ENT_QUOTES),
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
		$data['users'] = $this->adminmodel->getAllUsers('user');

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
				'user_id' => htmlentities($this->input->post('user_id'), ENT_QUOTES),
				'product_name' => htmlentities($this->input->post('product_name'), ENT_QUOTES),
				'category_id' => htmlentities($this->input->post('category_id'), ENT_QUOTES),
				'sub_category_id' => htmlentities($this->input->post('sub_category_id'), ENT_QUOTES),
				'product_price' => htmlentities($this->input->post('product_price'), ENT_QUOTES),
				'product_price_offer' => htmlentities($this->input->post('product_price_offer'), ENT_QUOTES),
				'product_payment_link' => htmlentities($this->input->post('product_payment_link'), ENT_QUOTES),
				'product_description' => htmlentities($this->input->post('product_description'), ENT_QUOTES),
				'product_tags' => htmlentities($this->input->post('product_tags'), ENT_QUOTES),
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
			'per_page'         => 10,
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
	public function add_blog()
	{
		$data['users']             = $this->adminmodel->getAllUsers('user');
		if ($this->form_validation->run('blogs') == false) {
			$this->load->view('admin/header', $data);
			//$this->load->view('admin/user_add_new_blog');
			$this->load->view('admin/add_new_blogs');
			$this->load->view('admin/footer');
		} else {
			$isenquiry = htmlentities($this->input->post('isenquiry'), ENT_QUOTES) == 'on' ? 1 : 0;
			$blog_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('blog_name'));
			$info = array(
				// 'blog_id'			=> htmlentities($this->input->post('blog_id'), ENT_QUOTES),
				'user_id'			=> htmlentities($this->input->post('user_id'), ENT_QUOTES),
				'blog_name'    		=> htmlentities($this->input->post('blog_name'), ENT_QUOTES),
				'blog_description'  => htmlentities($this->input->post('blog_description'), ENT_QUOTES),
				'blog_status'		=> 1,
				'isenquiry'         => $isenquiry,
				'blog_slug'			=> $blog_slug
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
				$this->session->set_flashdata('message', 'Adding Blog Failed!!');
			}
		}
	}

	/* Edit User Blog */
	public function edit_blog($slug)
	{
		$data['users']   = $this->adminmodel->getAllUsers('user');

		$data['blog']    = $this->usermodel->getSingleValue($slug, 'blog_slug', 'blogs');
		$old_image       = $data['blog']->blog_image;

		if ($this->form_validation->run('blogs') == FALSE) {
			$this->load->view('admin/header', $data);
			$this->load->view('admin/user_edit_blog');
			$this->load->view('admin/footer');
		} else {
			$isenquiry = htmlentities($this->input->post('isenquiry'), ENT_QUOTES) == 'on' ? 1 : 0;
			$blog_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('blog_name'));
			$info = array(
				'blog_id'			=> htmlentities($this->input->post('blog_id'), ENT_QUOTES),
				'user_id'			=> htmlentities($this->input->post('user_id'), ENT_QUOTES),
				'blog_name'    		=> htmlentities($this->input->post('blog_name'), ENT_QUOTES),
				'blog_description'  => htmlentities($this->input->post('blog_description'), ENT_QUOTES),
				'isenquiry'         => $isenquiry,
				'blog_slug'			=> $blog_slug
			);

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
			'per_page'         => 10,
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
		$data['users'] = $this->adminmodel->getAllUsers('user');
		if ($this->form_validation->run('news') == false) {
			$data['all_news_category'] = $this->adminmodel->allNewsCategory();
			$this->load->view('admin/header', $data);
			$this->load->view('admin/add_new_news');
			$this->load->view('admin/footer');
		} else {
			$news_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('news_title'));
			$info = array(
				'user_id'			=> htmlentities($this->input->post('user_id'), ENT_QUOTES),
				'news_title'    	=> htmlentities($this->input->post('news_title'), ENT_QUOTES),
				'news_description'  => htmlentities($this->input->post('news_description'), ENT_QUOTES),
				'news_category_id'  => htmlentities($this->input->post('news_category_id'), ENT_QUOTES),
				'news_seo_title'  	=> htmlentities($this->input->post('news_seo_title'), ENT_QUOTES),
				'news_seo_description' => htmlentities($this->input->post('news_seo_description'), ENT_QUOTES),
				'news_seo_keywords' => htmlentities($this->input->post('news_seo_keywords'), ENT_QUOTES),
				'featured_news'  	=> htmlentities($this->input->post('featured_news'), ENT_QUOTES),
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
				$this->session->set_flashdata('message', 'Adding News Failed!!');
			}
		}
	}

	/* Edit User News */
	public function edit_news($slug)
	{
		$data['users']   = $this->adminmodel->getAllUsers('user');

		$data['news']    = $this->usermodel->getSingleValue($slug, 'news_slug', 'news');
		$old_image       = $data['news']->news_image;

		if ($this->form_validation->run('news') == FALSE) {
			$data['all_news_category'] = $this->adminmodel->allNewsCategory();
			$this->load->view('admin/header');
			$this->load->view('admin/user_edit_news', $data);
			$this->load->view('admin/footer');
		} else {
			$info = array(
				'user_id'			=> htmlentities($this->input->post('user_id'), ENT_QUOTES),
				'news_title'    		=> htmlentities($this->input->post('news_title'), ENT_QUOTES),
				'news_description'  => htmlentities($this->input->post('news_description'), ENT_QUOTES),
				'news_category_id'  => htmlentities($this->input->post('news_category_id'), ENT_QUOTES),
				'news_seo_title'  => htmlentities($this->input->post('news_seo_title'), ENT_QUOTES),
				'news_seo_description'  => htmlentities($this->input->post('news_seo_description'), ENT_QUOTES),
				'news_seo_keywords'  => htmlentities($this->input->post('news_seo_keywords'), ENT_QUOTES),
				'featured_news'  => htmlentities($this->input->post('featured_news'), ENT_QUOTES)
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
			'per_page'         => 10,
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

			if ($this->adminmodel->updatemarketplaceCategory($slug, $info)) {
				$this->session->set_flashdata('message', 'Successfully Updated Video Category!!');
				redirect('admin/all_video_category', 'refresh');
			} else {
				$this->session->set_flashdata('message', 'Sorry, Update Video Category failed!!');
				redirect('admin/edit_video_category', 'refresh');
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
			'per_page'         => 10,
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
		$data['users'] = $this->adminmodel->getAllUsers('user');
		if ($this->form_validation->run('video') == false) {
			$data['all_video_category'] = $this->adminmodel->allVideoCategory(NULL, NULL);
			$this->load->view('admin/header', $data);
			$this->load->view('admin/add_new_video');
			$this->load->view('admin/footer');
		} else {
			$video_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('video_title'));
			$info = array(
				'user_id'			=> htmlentities($this->input->post('user_id'), ENT_QUOTES),
				'video_title'    		=> htmlentities($this->input->post('video_title'), ENT_QUOTES),
				'video_code'    		=> htmlentities($this->input->post('video_code'), ENT_QUOTES),
				'featured_video'    		=> htmlentities($this->input->post('featured_video'), ENT_QUOTES),
				'video_description'  => htmlentities($this->input->post('video_description'), ENT_QUOTES),
				'video_category_id'  => htmlentities($this->input->post('video_category_id'), ENT_QUOTES),
				'video_seo_title'  => htmlentities($this->input->post('video_seo_title'), ENT_QUOTES),
				'video_seo_description'  => htmlentities($this->input->post('video_seo_description'), ENT_QUOTES),
				'video_seo_keywords'  => htmlentities($this->input->post('video_seo_keywords'), ENT_QUOTES),
				'video_slug'  => htmlentities($video_slug, ENT_QUOTES)
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
				$this->session->set_flashdata('message', 'Adding Video Failed!!');
			}
		}
	}

	/* Edit Video */
	public function edit_video($slug)
	{
		$data['users']   = $this->adminmodel->getAllUsers('user');
		$data['video']    = $this->usermodel->getSingleValue($slug, 'video_slug', 'video');
		$old_image       = $data['video']->video_image;

		if ($this->form_validation->run('video') == FALSE) {
			$data['all_video_category'] = $this->adminmodel->allVideoCategory(NULL, NULL);
			$this->load->view('admin/header', $data);
			$this->load->view('admin/edit_video', $data);
			$this->load->view('admin/footer');
		} else {
			$video_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('video_title'));
			$info = array(
				'user_id'			=> htmlentities($this->input->post('user_id'), ENT_QUOTES),
				'video_title'    		=> htmlentities($this->input->post('video_title'), ENT_QUOTES),
				'video_code'    		=> htmlentities($this->input->post('video_code'), ENT_QUOTES),
				'featured_video'    		=> htmlentities($this->input->post('featured_video'), ENT_QUOTES),
				'video_description'  => htmlentities($this->input->post('video_description'), ENT_QUOTES),
				'video_category_id'  => htmlentities($this->input->post('video_category_id'), ENT_QUOTES),
				'video_seo_title'  => htmlentities($this->input->post('video_seo_title'), ENT_QUOTES),
				'video_seo_description'  => htmlentities($this->input->post('video_seo_description'), ENT_QUOTES),
				'video_seo_keywords'  => htmlentities($this->input->post('video_seo_keywords'), ENT_QUOTES),
				'video_slug'  => htmlentities($video_slug, ENT_QUOTES)
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
			'per_page'         => 10,
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
			'per_page'         => 10,
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
		$data['users'] = $this->adminmodel->getAllUsers('user');
		if ($this->form_validation->run('classifieds') == false) {
			$data['all_classifieds_category'] = $this->adminmodel->allclassifiedsCategory(NULL, NULL);
			$this->load->view('admin/header', $data);
			$this->load->view('admin/add_classifieds');
			$this->load->view('admin/footer');
		} else {
			$classifieds_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('classifieds_title'));
			$info = array(
				'user_id'					  => htmlentities($this->input->post('user_id'), ENT_QUOTES),
				'classifieds_title'    		  => htmlentities($this->input->post('classifieds_title'), ENT_QUOTES),
				'classifieds_price'    		  => htmlentities($this->input->post('classifieds_price'), ENT_QUOTES),
				'classifieds_category_id'	  => htmlentities($this->input->post('classifieds_category_id'), ENT_QUOTES),
				'featured_classifieds'  	  => htmlentities($this->input->post('featured_classifieds'), ENT_QUOTES),
				'spotlight_classifieds'  	  => htmlentities($this->input->post('spotlight_classifieds'), ENT_QUOTES),
				'display_contact'  	  		  => htmlentities($this->input->post('display_contact'), ENT_QUOTES),
				'classifieds_description'	  => htmlentities($this->input->post('classifieds_description'), ENT_QUOTES),
				'classifieds_seo_title' 	  => htmlentities($this->input->post('classifieds_seo_title'), ENT_QUOTES),
				'classifieds_seo_description' => htmlentities($this->input->post('classifieds_seo_description'), ENT_QUOTES),
				'classifieds_seo_keywords'    => htmlentities($this->input->post('classifieds_seo_keywords'), ENT_QUOTES),
				'classifieds_slug'  		  => htmlentities($classifieds_slug, ENT_QUOTES)
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
		$data['users']   = $this->adminmodel->getAllUsers('user');
		$data['classifieds']    = $this->usermodel->getSingleValue($slug, 'classifieds_slug', 'classifieds');
		$old_image       = $data['classifieds']->classifieds_image;

		if ($this->form_validation->run('classifieds') == FALSE) {
			$data['all_classifieds_category'] = $this->adminmodel->allClassifiedsCategory(NULL, NULL);
			$this->load->view('admin/header');
			$this->load->view('admin/edit_classifieds', $data);
			$this->load->view('admin/footer');
		} else {
			$classifieds_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('classifieds_title'));
			$info = array(
				'user_id'					  => htmlentities($this->input->post('user_id'), ENT_QUOTES),
				'classifieds_title'    		  => htmlentities($this->input->post('classifieds_title'), ENT_QUOTES),
				'classifieds_price'    		  => htmlentities($this->input->post('classifieds_price'), ENT_QUOTES),
				'classifieds_category_id'	  => htmlentities($this->input->post('classifieds_category_id'), ENT_QUOTES),
				'featured_classifieds'  	  => htmlentities($this->input->post('featured_classifieds'), ENT_QUOTES),
				'spotlight_classifieds'  	  => htmlentities($this->input->post('spotlight_classifieds'), ENT_QUOTES),
				'display_contact'  	  		  => htmlentities($this->input->post('display_contact'), ENT_QUOTES),
				'classifieds_description'	  => htmlentities($this->input->post('classifieds_description'), ENT_QUOTES),
				'classifieds_seo_title' 	  => htmlentities($this->input->post('classifieds_seo_title'), ENT_QUOTES),
				'classifieds_seo_description' => htmlentities($this->input->post('classifieds_seo_description'), ENT_QUOTES),
				'classifieds_seo_keywords'    => htmlentities($this->input->post('classifieds_seo_keywords'), ENT_QUOTES),
				'classifieds_slug'  		  => htmlentities($classifieds_slug, ENT_QUOTES)
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
		$data['users'] = $this->adminmodel->getAllUsers('user');
		if ($this->form_validation->run('marketplace') == false) {
			$data['all_marketplace_category'] = $this->adminmodel->allmarketplaceCategory();
			$this->load->view('admin/header', $data);
			$this->load->view('admin/add_marketplace');
			$this->load->view('admin/footer');
		} else {
			$marketplace_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('marketplace_title'));
			$info = array(
				'user_id'					  => htmlentities($this->input->post('user_id'), ENT_QUOTES),
				'marketplace_title'    		  => htmlentities($this->input->post('marketplace_title'), ENT_QUOTES),
				'marketplace_price'    		  => htmlentities($this->input->post('marketplace_price'), ENT_QUOTES),
				'marketplace_category_id'	  => htmlentities($this->input->post('marketplace_category_id'), ENT_QUOTES),
				'featured_marketplace'  	  => htmlentities($this->input->post('featured_marketplace'), ENT_QUOTES),
				'spotlight_marketplace'  	  => htmlentities($this->input->post('spotlight_marketplace'), ENT_QUOTES),
				'display_contact'  	  		  => htmlentities($this->input->post('display_contact'), ENT_QUOTES),
				'marketplace_description'	  => htmlentities($this->input->post('marketplace_description'), ENT_QUOTES),
				'marketplace_seo_title' 	  => htmlentities($this->input->post('marketplace_seo_title'), ENT_QUOTES),
				'marketplace_seo_description' => htmlentities($this->input->post('marketplace_seo_description'), ENT_QUOTES),
				'marketplace_seo_keywords'    => htmlentities($this->input->post('marketplace_seo_keywords'), ENT_QUOTES),
				'marketplace_slug'  		  => htmlentities($marketplace_slug, ENT_QUOTES)
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
		$data['users']   = $this->adminmodel->getAllUsers('user');
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
				'user_id'					  => htmlentities($this->input->post('user_id'), ENT_QUOTES),
				'marketplace_title'    		  => htmlentities($this->input->post('marketplace_title'), ENT_QUOTES),
				'marketplace_price'    		  => htmlentities($this->input->post('marketplace_price'), ENT_QUOTES),
				'marketplace_category_id'	  => htmlentities($this->input->post('marketplace_category_id'), ENT_QUOTES),
				'featured_marketplace'  	  => htmlentities($this->input->post('featured_marketplace'), ENT_QUOTES),
				'spotlight_marketplace'  	  => htmlentities($this->input->post('spotlight_marketplace'), ENT_QUOTES),
				'display_contact'  	  		  => htmlentities($this->input->post('display_contact'), ENT_QUOTES),
				'marketplace_description'	  => htmlentities($this->input->post('marketplace_description'), ENT_QUOTES),
				'marketplace_seo_title' 	  => htmlentities($this->input->post('marketplace_seo_title'), ENT_QUOTES),
				'marketplace_seo_description' => htmlentities($this->input->post('marketplace_seo_description'), ENT_QUOTES),
				'marketplace_seo_keywords'    => htmlentities($this->input->post('marketplace_seo_keywords'), ENT_QUOTES),
				'marketplace_slug'  		  => htmlentities($marketplace_slug, ENT_QUOTES)
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
			'per_page'         => 10,
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
			'per_page'         => 10,
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
		$data['users'] = $this->adminmodel->getAllUsers('user');
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

		$data['users'] = $this->adminmodel->getAllUsers('user');
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
			'per_page'         => 10,
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
		$data['users'] = $this->adminmodel->getAllUsers('user');

		if ($this->form_validation->run('admin_event_rules') == false) {
			$this->load->view('admin/header');
			$this->load->view('admin/add_new_event', $data);
			$this->load->view('admin/footer');
		} else {
			$event_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('event_name'));

			$info = array(
				'user_id'           => $this->input->post('user_id'),
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
				$this->session->set_flashdata('message', 'Adding Event Failed!!');
				redirect('admin/add-new-event');
			}
		}
	}

	/* Edit User Event */
	public function edit_event($slug)
	{
		$data['event']    = $this->adminmodel->getSingleValue($slug, 'event_slug', 'events');
		$data['allUsers'] = $this->adminmodel->get_users();
		$old_image        = $data['event']->event_image;

		if ($this->form_validation->run('event_rules') == FALSE) {
			$this->load->view('admin/header');
			$this->load->view('admin/edit_event', $data);
			$this->load->view('admin/footer');
		} else {
			$event_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('event_name'));
			$info = array(
				'user_id'           => $this->input->post('user_id'),
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
			$data['slider_link'] = htmlentities($this->input->post('slider_link'), ENT_QUOTES);

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

	public function enquiries()
	{
		$data['logged_id'] = NULL;
		$session 		   = $this->session->userdata('logged_admin');
		$data['logged_id'] = $session['logged_id'];

		$data['enquiries'] = $this->adminmodel->getEnquiries();

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

	public function claim_approve($id)
	{
		$claimListing = $this->db->select('*')->from('claim_listings')->where('claim_listing_id', $id)->get()->row_array();

		$getSingleListing = $this->db->select('*')->from('listings')->where('listing_id', $claimListing['listing_id'])->get()->row_array();

		$lisitngID = '';

		if ($getSingleListing != NULL) {

			$listing_id = $getSingleListing['listing_id'];

			$claimListing['claim_status'] = 1;
			unset($getSingleListing[0]['listing_id']);
			$getSingleListing[0]['user_id'] = $claimListing['enquiry_sender_id'];
			if ($this->db->where('claim_listing_id', $id)->update('claim_listings', $claimListing)) {

				if ($this->db->where('listing_id', $listing_id)->update('listings', $getSingleListing)) {
					$this->session->set_flashdata('message', 'Successfully Status Updated !');
					redirect('admin/claimed-listings', 'refresh');
				}
			} else {
				$this->session->set_flashdata('message', 'Status Update Unsuccessful!');
				redirect('admin/claimed-listings', 'refresh');
			}
		}
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
			$info = array(
				'u_id'            => htmlentities($this->input->post('user_id'), ENT_QUOTES),
				'ads_price_id'    => htmlentities($this->input->post('ads_price_id'), ENT_QUOTES),
				'ad_start_date'   => date('Y-m-d h:i:s', strtotime($this->input->post('ad_start_date'))),
				'ad_end_date'     => date('Y-m-d h:i:s', strtotime($this->input->post('ad_end_date'))),
				'ad_link'         => htmlentities($this->input->post('ad_link'), ENT_QUOTES),
				'ad_total_days'   => htmlentities($this->input->post('ad_total_days'), ENT_QUOTES),
				'ad_cost_per_day' => htmlentities($this->input->post('ad_cost_per_day'), ENT_QUOTES),
				'ad_total_cost'   => htmlentities($this->input->post('ad_total_cost'), ENT_QUOTES)
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
				'u_id'            => htmlentities($this->input->post('user_id'), ENT_QUOTES),
				'ads_price_id'    => htmlentities($this->input->post('ads_price_id'), ENT_QUOTES),
				'ad_start_date'   => date('Y-m-d h:i:s', strtotime($this->input->post('ad_start_date'))),
				'ad_end_date'     => date('Y-m-d h:i:s', strtotime($this->input->post('ad_end_date'))),
				'ad_link'         => htmlentities($this->input->post('ad_link'), ENT_QUOTES),
				'ad_total_days'   => htmlentities($this->input->post('ad_total_days'), ENT_QUOTES),
				'ad_cost_per_day' => htmlentities($this->input->post('ad_cost_per_day'), ENT_QUOTES),
				'ad_total_cost'   => htmlentities($this->input->post('ad_total_cost'), ENT_QUOTES)
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
			$info = array(
				'name'   => htmlentities($this->input->post('name'), ENT_QUOTES),
				'cost'   => htmlentities($this->input->post('cost'), ENT_QUOTES),
				'status' => htmlentities($this->input->post('status'), ENT_QUOTES),
				'img'    => $uploaded_image_path // set this after upload
				// Removed 'size', 'height', and 'width' from insert
			);

			if ($_FILES && $_FILES['img']['name']) {
				$img_info = [
					'upload_path' => 'images/ads',
					'image_name'  => 'img',
					'quality'     => '60%',
					'width'       => $info['width'],
					'height'      => $info['height']
				];

				$info['img'] = uploadAndResize($img_info);
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
		$old_image        	= $data['price']->img;

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
				'name'		=> htmlentities($this->input->post('name'), ENT_QUOTES),
				'cost'    	=> htmlentities($this->input->post('cost'), ENT_QUOTES),
				'status'    => htmlentities($this->input->post('status'), ENT_QUOTES)
			);

			if ($_FILES && $_FILES['img']['name']) {
				$img_info = [
					'upload_path' => 'images/ads',
					'image_name'  => 'img',
					'quality'     => '60%',
					'height'      => $info['height'],
					'width'       => $info['width']
				];

				$image_link = './' . $old_image;
				if (file_exists($image_link)) {
					unlink($image_link);
				}

				$info['img'] = uploadAndResize($img_info);
			} else {
				$info['img'] = $old_image;
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
		$id = $this->input->post('id');
		$statusValue = $this->input->post('status');

		$data = $this->adminmodel->getSingleAdsAjaxValue($id);

		$data['ad_status'] = 1;

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

		// If still empty, create some sample data for display
		if (empty($data['pricing_plans'])) {
			$data['pricing_plans'] = array(
				(object) array(
					'id' => 1,
					'name' => 'Basic Plan',
					'price' => 29,
					'currency' => 'USD',
					'billing_interval' => 'monthly',
					'duration_days' => 30,
					'listing_limit' => 5,
					'product_limit' => 2,
					'event_limit' => 1,
					'classified_limit' => 3,
					'blog_limit' => 2,
					'news_limit' => 1,
					'video_limit' => 1,
					'rental_limit' => 2,
					'house_limit' => 1,
					'ad_limit' => 3,
					'featured_listing' => 0,
					'analytics_access' => 0,
					'is_active' => 1
				),
				(object) array(
					'id' => 2,
					'name' => 'Standard Plan',
					'price' => 59,
					'currency' => 'USD',
					'billing_interval' => 'monthly',
					'duration_days' => 60,
					'listing_limit' => 15,
					'product_limit' => 10,
					'event_limit' => 5,
					'classified_limit' => 10,
					'blog_limit' => 8,
					'news_limit' => 5,
					'video_limit' => 6,
					'rental_limit' => 8,
					'house_limit' => 5,
					'ad_limit' => 10,
					'featured_listing' => 1,
					'analytics_access' => 0,
					'is_active' => 1
				),
				(object) array(
					'id' => 3,
					'name' => 'Premium Plan',
					'price' => 99,
					'currency' => 'USD',
					'billing_interval' => 'monthly',
					'duration_days' => 90,
					'listing_limit' => -1,
					'product_limit' => -1,
					'event_limit' => -1,
					'classified_limit' => -1,
					'blog_limit' => -1,
					'news_limit' => -1,
					'video_limit' => -1,
					'rental_limit' => -1,
					'house_limit' => -1,
					'ad_limit' => -1,
					'featured_listing' => 1,
					'analytics_access' => 1,
					'is_active' => 1
				)
			);
		}

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
		$ok = $this->adminmodel->updatePricingDuration($id, $price);
		if ($ok) echo json_encode(['success' => true]);
		else echo json_encode(['success' => false, 'error' => 'DB update failed']);
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
			'per_page' => 20,
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
	public function getSubscriptionDataForDashboard($user_id)
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
		$data['campaigns']   = $this->adminmodel->getAllCampaigns();
		$data['subscribers'] = $this->adminmodel->countAllNewsletterSubscribers();
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
}
