
	<?php

	class Adminmodel extends CI_Model
	{
		/**
		 * Get all house categories with dynamic house count
		 */
		public function allHouseCategoryWithCount($limit = null, $offset = null)
		{
			$sql = "SELECT hc.*, 
					   (SELECT COUNT(*) FROM house h WHERE h.house_category_id = hc.category_id) AS house_count
				FROM house_categories hc
				ORDER BY hc.category_name ASC";
			if ($limit !== null && $offset !== null) {
				$sql .= " LIMIT ?, ?";
				return $this->db->query($sql, [$offset, $limit])->result();
			} else {
				return $this->db->query($sql)->result();
			}
		}
		// Monthly revenue stats (for chart)
		public function getMonthlyRevenueStats($year = null)
		{
			if ($year === null) {
				$year = date('Y');
			}
			$result = $this->db->query("SELECT MONTH(ad_start_date) as month, SUM(ad_total_cost) as total FROM ads WHERE YEAR(ad_start_date) = ? GROUP BY MONTH(ad_start_date)", [$year])->result();
			$stats = array_fill(1, 12, 0);
			foreach ($result as $row) {
				$stats[(int)$row->month] = (float)$row->total;
			}
			return $stats;
		}

		// Monthly listings stats (for chart)
		public function getMonthlyListingStats($year = null)
		{
			if ($year === null) {
				$year = date('Y');
			}
			$result = $this->db->query("SELECT MONTH(created_at) as month, COUNT(*) as total FROM listings WHERE YEAR(created_at) = ? GROUP BY MONTH(created_at)", [$year])->result();
			$stats = array_fill(1, 12, 0);
			foreach ($result as $row) {
				$stats[(int)$row->month] = (int)$row->total;
			}
			return $stats;
		}

		// Monthly products stats (for chart)
		public function getMonthlyProductStats($year = null)
		{
			if ($year === null) {
				$year = date('Y');
			}
			$result = $this->db->query("SELECT MONTH(created_at) as month, COUNT(*) as total FROM products WHERE YEAR(created_at) = ? GROUP BY MONTH(created_at)", [$year])->result();
			$stats = array_fill(1, 12, 0);
			foreach ($result as $row) {
				$stats[(int)$row->month] = (int)$row->total;
			}
			return $stats;
		}

		// Dashboard Counters
		public function countAllUsers()
		{
			return $this->db->count_all('user');
		}

		public function countAllListings()
		{
			return $this->db->count_all('listings');
		}

		public function countAllEvents()
		{
			return $this->db->from('events')->count_all_results();
		}

		public function countAllProducts()
		{
			return $this->db->count_all('products');
		}

		public function countAllHouses()
		{
			return $this->db->from('house')->count_all_results();  // Count total rows in the house table
		}

		public function countAllClassifieds()
		{
			return $this->db->from('classifieds')->count_all_results();  // Count total rows in the classifieds table
		}

		public function countAllRentals()
		{
			return $this->db->from('rental')->count_all_results();  // Count total rows in the rental table
		}

		public function countAllAds()
		{
			return $this->db->count_all('ads');
		}

		public function countActiveAds()
		{
			return $this->db->where('ad_status', 1)->count_all_results('ads');
		}

		public function countAllEnquiries()
		{
			return $this->db->count_all('enquiries');
		}

		// Demo: Monthly user registration stats (for chart)
		public function getMonthlyUserStats($year = null)
		{
			if ($year === null) {
				$year = date('Y');
			}
			$result = $this->db->query("SELECT MONTH(user_created) as month, COUNT(*) as total FROM user WHERE YEAR(user_created) = ? GROUP BY MONTH(user_created)", [$year])->result();
			$stats = array_fill(1, 12, 0);
			foreach ($result as $row) {
				$stats[(int)$row->month] = (int)$row->total;
			}
			return $stats;
		}

		function __construct()
		{
			parent::__construct();
			$this->load->helper(array('form', 'url'));
			$this->load->library('form_validation');
			$this->load->library('session');
			$this->load->helper('cookie');
			$this->load->database();
		}

		// Login
		function login()
		{
			$this->form_validation->set_rules('admin_email', 'Email Address', 'required|trim|valid_email');
			$this->form_validation->set_rules('admin_password', 'Password', 'required');

			if (!$this->form_validation->run()) {
				return array('status' => FALSE, 'referror' => '', 'message' => 'Email or Password is incorrect.');
			}

			$admin_email = $this->input->post('admin_email');
			$admin_password = (string) $this->input->post('admin_password');

			$admin = $this->db
				->where('admin_email', $admin_email)
				->limit(1)
				->get('admin')
				->row();

			if (!$admin) {
				return array('status' => FALSE, 'message' => 'Email or Password is incorrect.', 'url' => '');
			}

			$stored = (string) $admin->admin_password;
			$verified = false;

			if (strlen($stored) > 0 && $stored[0] === '$') {
				$verified = password_verify($admin_password, $stored);
			} elseif (strlen($stored) === 32 && ctype_xdigit($stored)) {
				if (hash_equals(strtolower($stored), md5($admin_password))) {
					$verified = true;
					$this->db
						->where('admin_id', $admin->admin_id)
						->update('admin', array(
							'admin_password' => password_hash($admin_password, PASSWORD_DEFAULT),
						));
				}
			}

			if (!$verified) {
				return array('status' => FALSE, 'message' => 'Email or Password is incorrect.', 'url' => '');
			}

			if ((int) $admin->banned === 1) {
				return array('status' => FALSE, 'message' => 'Your account has been removed. Please contact Administrator.', 'url' => 'login');
			}

			// Dedicated admin session key so an end-user session can
			// never satisfy an admin access check.
			$this->session->set_userdata('logged_admin', array(
				'logged_id'   => $admin->admin_id,
				'admin_email' => $admin->admin_email,
			));

			return array('status' => TRUE, 'url' => 'admin/dashboard');
		}

		// Get Admin Information
		public function getAdminInformation()
		{

			/*$session = $this->session->userdata('logged_user');

		$logged_id = $session['logged_id'];

		$result =  $this->db->query("SELECT * FROM user WHERE user_id = $logged_id LIMIT 1");

		$result = $result->result();

		return $result[0];*/
		}

		// Get Users
		public function get_users()
		{
			return $data = $this->db->select('name,user_id')->from('user')->order_by('name', 'ASC')->get()->result();
		}

		/*===================================
		Get Listing Category Information
		====================================*/

		// public function allListingCategory()
		// {
		// 	return $data = $this->db->select('*')->from('listing_categories')->order_by('category_id', 'DESC')->get()->result();
		// }
		public function allListingCategory($limit = null, $offset = null)
		{
			$this->db->select('lc.*, 
			(SELECT COUNT(*) FROM listings l WHERE l.category_id = lc.category_id AND (l.is_deleted IS NULL OR l.is_deleted = 0)) as listing_count,
			(SELECT COUNT(*) FROM listing_sub_categories lsc WHERE lsc.category_id = lc.category_id) as sub_category_count');
			$this->db->from('listing_categories lc');
			$this->db->order_by('lc.category_id', 'DESC');

			if ($limit !== null && $offset !== null) {
				$this->db->limit($limit, $offset);
			}

			return $this->db->get()->result();
		}



		/*=======================
		Add Listing Category Information
		========================*/

		public function addListingCategory($data)
		{
			if ($this->db->insert('listing_categories', $data)) {
				return true;
			} else {
				return false;
			}
		}

		/*===================================
     Update Listing Category Information
     ====================================*/

		public function updateListingCategory($id, $info)
		{
			if ($this->db->where('category_id', $id)->update('listing_categories', $info)) {
				return true;
			} else {
				return false;
			}
		}

		/*===================================
		Delete Listing Category Information
		====================================*/

		public function deleteCategory($id)
		{
			$data = $this->db->select('*')->from('listing_categories')->where('category_id', $id)->get()->row();
			$img = '';
			if ($data->category_image) {
				$image_link = './' . $data->category_image;

				if (file_exists($image_link)) {
					$img = $image_link;
				} else {
					$img = '';
				}
			}

			if ($this->db->where('category_id', $id)->delete('listing_categories')) {
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


		/*===================================
		Get Listing Sub Category Information
		====================================*/
		public function allListingSubCategory($limit = NULL, $offset = 0)
		{
			$this->db->select('sub_cate.category_id, main_cate.category_name, 
                       sub_cate.sub_category_name, sub_cate.created_at, 
                       sub_cate.sub_category_id, sub_cate.sub_category_slug,
                       (SELECT COUNT(*) FROM listings l WHERE l.sub_category_id = sub_cate.sub_category_id AND (l.is_deleted IS NULL OR l.is_deleted = 0)) as listing_count')
				->from('listing_sub_categories as sub_cate')
				->join(
					'listing_categories as main_cate',
					'sub_cate.category_id = main_cate.category_id',
					'LEFT'
				)
				->order_by('sub_cate.sub_category_id', 'DESC');

			if ($limit !== NULL) {
				$this->db->limit($limit, $offset);
			}

			return $this->db->get()->result();
		}

		public function countAllListingSubCategory()
		{
			return $this->db->count_all('listing_sub_categories');
		}


		/*=======================================
	 Add Listing Sub Category Information
	 ========================================*/

		public function addListingSubCategory($data)
		{
			if ($this->db->insert('listing_sub_categories', $data)) {
				return true;
			} else {
				return false;
			}
		}
		/*=======================================
	 Get Single Listing Sub Category Information
	 ========================================*/

		public function get_listing_sub_category_ById($id)
		{
			return $data = $this->db->select('*')
				->from('listing_sub_categories')
				->where('sub_category_id', $id)
				->order_by('sub_category_id', 'DESC')
				->get()->row();
		}
		/*=======================================
	 Get Listing Sub Category Information By Main Category ID
	 ========================================*/

		public function ListingSubCategoryByCateId($id)
		{
			return $data = $this->db->select('sub_cate.category_id, main_cate.category_name, sub_cate.sub_category_name, main_cate.category_id, sub_cate.created_at, sub_cate.sub_category_id,sub_cate.sub_category_slug,
                       (SELECT COUNT(*) FROM listings l WHERE l.sub_category_id = sub_cate.sub_category_id AND (l.is_deleted IS NULL OR l.is_deleted = 0)) as listing_count')
				->from('listing_sub_categories as sub_cate')
				->join('listing_categories as main_cate', 'sub_cate.category_id = main_cate.category_id', 'LEFT')
				->where('sub_cate.category_id', $id)
				->get()
				->result();
		}
		/*=======================================
	 Update Listing Sub Category Information
	 ========================================*/

		public function updateListingSubCategory($id, $info)
		{
			if ($this->db->where('sub_category_id', $id)->update('listing_sub_categories', $info)) {
				return true;
			} else {
				return false;
			}
		}

		/*===================================
		Delete Listing Category Information
		====================================*/

		public function deleteListingSubCategory($id)
		{
			if ($this->db->where('sub_category_id', $id)->delete('listing_sub_categories')) {
				return true;
			} else {
				return false;
			}
		}

		/*===================================
		Get Product Category Information
		====================================*/
		public function allProductCategory($limit = NULL, $offset = 0)
		{
			$this->db->select('
        main_cate.category_id, 
        main_cate.category_name, 
        main_cate.category_image, 
        main_cate.created_at, 
        main_cate.category_slug, 
        COUNT(products.category_id) AS total_product, 
        COUNT(sub_cate.category_id) AS total_sub_cate')
				->from('product_categories as main_cate')
				->join('product_sub_categories as sub_cate', 'sub_cate.category_id = main_cate.category_id', 'LEFT')
				->join('products', 'products.category_id = main_cate.category_id', 'LEFT')
				->group_by("main_cate.category_id")
				->order_by("main_cate.category_id", "DESC");

			if ($limit !== NULL) {
				$this->db->limit($limit, $offset);
			}

			return $this->db->get()->result();
		}

		public function countAllProductCategory()
		{
			return $this->db->from('product_categories')->count_all_results(); // Correct function to count total records
		}

		/*===================================
		Add Product Category Information
		====================================*/
		public function addProductCategory($data)
		{
			if ($this->db->insert('product_categories', $data)) {
				return true;
			} else {
				return false;
			}
		}
		/*=======================================
	 Get Single Listing Sub Category Information
	 ========================================*/

		public function get_product_category_ById($id)
		{
			return $this->db->get_where('product_categories', array('category_id' => $id))->row();
		}


		/*=======================================
	 Update Product Category Information
	 ========================================*/

		public function updateProductCategory($id, $info)
		{
			$this->db->where('category_id', $id);
			$this->db->update('product_categories', $info);
			return $this->db->affected_rows() > 0; // Returns TRUE if any rows were updated
		}
		/*=======================================
	 Delete Product Category 
	 ========================================*/
		public function deleteProductCategory($id)
		{
			// Fetch the category record
			$query = $this->db->select('*')->from('product_categories')->where('category_id', $id)->get();

			// Check if data exists
			if ($query->num_rows() == 0) {
				return false; // No category found, return false
			}

			$data = $query->row();
			$img  = '';

			// Handle image deletion
			if (!empty($data->category_image)) {
				$image_link = FCPATH . $data->category_image; // Absolute path

				if (file_exists($image_link)) {
					$img = $image_link;
				}
			}

			// Delete category from database
			if ($this->db->where('category_id', $id)->delete('product_categories')) {
				if (!empty($img) && file_exists($img)) {
					unlink($img);
				}
				return true;
			}

			return false;
		}

		/*===================================
		Get Product Sub Category Information
	====================================*/

		public function allProductSubCategory($limit, $offset)
		{
			return $this->db->select('sub_cate.category_id, main_cate.category_name, sub_cate.sub_category_name, main_cate.category_id, sub_cate.sub_category_slug, sub_cate.created_at, sub_cate.sub_category_id, COUNT(products.sub_category_id) as total_product')
				->from('product_sub_categories as sub_cate')
				->join('product_categories as main_cate', 'sub_cate.category_id = main_cate.category_id', 'LEFT')
				->join('products', 'products.sub_category_id = sub_cate.sub_category_id', 'LEFT')
				->group_by("sub_cate.sub_category_id")
				->order_by('sub_cate.sub_category_id', 'DESC') // latest first
				->limit($limit, $offset)
				->get()
				->result();
		}

		// Function to count all records
		public function countAllProductSubCategory()
		{
			return $this->db->from('product_sub_categories')->count_all_results();
		}

		/*=======================================
	 Add Product Sub Category Information
	 ========================================*/

		public function addProductSubCategory($data)
		{
			if ($this->db->insert('product_sub_categories', $data)) {
				return true;
			} else {
				return false;
			}
		}
		/*===========================================
	 Get Single Product Sub Category Information
	 ==========================================*/

		public function get_product_sub_category_ById($slug)
		{
			return $data = $this->db->select('*')->from('product_sub_categories')->where('sub_category_slug', $slug)->get()->row();
		}
		/*=======================================================
	 Get Product Sub Category Information By Main Category ID
	 ======================================================*/

		// public function ProductSubCategoryByCateId($id)
		// {
		// 	return $data = $this->db->select('sub_cate.category_id, main_cate.category_name, sub_cate.sub_category_name, main_cate.category_id, sub_cate.created_at, sub_cate.sub_category_id')
		// 	->from('product_sub_categories as sub_cate')
		// 	->join('product_categories as main_cate', 'sub_cate.category_id = main_cate.category_id', 'LEFT')
		// 	->where('sub_cate.category_id',$id)
		// 	->get()
		// 	->result();
		// }
		/*=======================================
	 Update Product Sub Category Information
	========================================*/

		public function updateProductSubCategory($slug, $info)
		{
			if ($this->db->where('sub_category_slug', $slug)->update('product_sub_categories', $info)) {
				return true;
			} else {
				return false;
			}
		}

		/*===================================
		Delete Product Category Information
	====================================*/

		public function deleteProductSubCategory($slug)
		{
			if ($this->db->where('sub_category_slug', $slug)->delete('product_sub_categories')) {
				return true;
			} else {
				return false;
			}
		}


		/*========================================================
	 Get Product Sub Category Information By Main Category ID
	========================================================*/

		public function ProductSubCategoryByCateId($id)
		{
			return $data = $this->db->select('sub_cate.category_id, main_cate.category_name, sub_cate.sub_category_name, main_cate.category_id,sub_cate.sub_category_slug, sub_cate.created_at, sub_cate.sub_category_id')
				->from('product_sub_categories as sub_cate')
				->join('product_categories as main_cate', 'sub_cate.category_id = main_cate.category_id', 'LEFT')
				->where('sub_cate.category_id', $id)
				->get()
				->result();
		}



		/*======================
		Get All Slider
	======================*/

		public function allSlider()
		{
			return $data = $this->db->get('slider')->result();
		}

		/*=======================
		Add Sliter
	========================*/

		public function addSlider($data)
		{
			if ($this->db->insert('slider', $data)) {
				return true;
			} else {
				return false;
			}
		}

		/*=======================================
	 	Get Single Slider Information
	========================================*/

		public function getsliderByid($id)
		{
			return $data = $this->db->select('*')->from('slider')->where('slider_id', $id)->get()->row();
		}


		/*===================================
     	Update Slider Information
    ====================================*/

		public function updateSlider($id, $info)
		{
			if ($this->db->where('slider_id', $id)->update('slider', $info)) {
				return true;
			} else {
				return false;
			}
		}
		/*===================================
		Delete Slider Information
	====================================*/

		public function deleteSlider($id)
		{
			$data = $this->db->select('*')->from('slider')->where('slider_id', $id)->get()->row();
			$img = '';
			if ($data->slider_photo) {
				$image_link = './' . $data->slider_photo;

				if (file_exists($image_link)) {
					$img = $image_link;
				} else {
					$img = '';
				}
			}

			if ($this->db->where('slider_id', $id)->delete('slider')) {
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
		Get All Event Information
		===========================*/
		public function get_events($limit, $offset)
		{
			$this->db->select('events.event_id, events.user_id, events.event_start_date, events.event_name, events.event_status, events.event_slug, events.created_at, user.name');
			$this->db->from('events');
			$this->db->join('user', 'user.user_id = events.user_id', 'left');
			$this->db->order_by('events.event_id', 'DESC');
			if ($limit !== null && $offset !== null) {
				$this->db->limit($limit, $offset);
			}
			return $this->db->get()->result();
		}


		/*==========================
		Add Event Information
		===========================*/

		public function addEvent($data)
		{

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

		public function deleteEvent($id)
		{
			$data = $this->db->select('*')->from('events')->where('event_id', $id)->get()->row();

			$img = '';

			if ($data->event_image) {
				$image_link = './' . $data->event_image;

				if (file_exists($image_link)) {
					$img = $image_link;
				} else {
					$img = '';
				}
			}


			if ($this->db->where('event_id', $id)->delete('events')) {
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


		/*===================
      PRODUCTS FUNCTION
      ===================*/

		public function getAllProducts($limit, $offset)
		{
			// Fetch products ordered by created_at DESC (latest first)
			$this->db->order_by('created_at', 'DESC');
			if ($limit !== NULL && $offset !== NULL) {
				$query = $this->db->get('products', $limit, $offset);
			} else {
				$query = $this->db->get('products');
			}
			return $query->result();
		}

		// Count total number of products
		public function countAllProducts2()
		{
			return $this->db->count_all('products');
		}

		public function getOnlyProduct($product_id)
		{
			$this->db->where('product_id', $product_id);
			$query = $this->db->get('products');
			return $query->row(); // Return a single row
		}


		public function getSingleProduct($product_id)
		{
			$data = $this->db->select('products.*, 
                               product_categories.category_name as cat_name, 
                               product_categories.category_id as pro_cat_id, 
                               user.name, 
                               user.user_created')
				->from('products')
				->join('product_categories', 'products.category_id = product_categories.category_id', 'left')
				->join('user', 'products.user_id = user.user_id', 'left')
				->where('products.product_id', $product_id)
				->get()
				->row();

			return $data;
		}


		/*===============
      BLOG FUNCTION
    ===============*/
		public function getBlogs($limit, $offset)
		{
			return $this->db->select('blogs.*, user.name')
				->from('blogs')
				->join('user', 'user.user_id = blogs.user_id', 'left')
				->order_by('blog_id', 'DESC')
				->limit($limit, $offset)
				->get()
				->result();
		}

		// Function to count total blogs
		public function countAllBlogs()
		{
			return $this->db->from('blogs')->count_all_results();
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


		/*==============
      News FUNCTION
    ===============*/
		public function getNews($limit, $offset)
		{
			return $this->db->select('news.news_id, news.news_title, news.news_description, news.news_status, news.news_slug, news.created_at, news.news_image, user.user_id, user.name, categories.category_name')
				->from('news')
				->join('user', 'user.user_id = news.user_id', 'LEFT')
				->join('news_categories as categories', 'categories.category_id = news.news_category_id', 'LEFT')
				->order_by('news_id', 'DESC')
				->limit($limit, $offset)  // Apply pagination limit and offset
				->get()
				->result();
		}
		public function countAllNews()
		{
			return $this->db->from('news')->count_all_results();  // Count total rows in the news table
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


		/*===================================
		Get News Category Information
	====================================*/

		public function allNewsCategory()
		{
			return $data = $this->db->select('*')->from('news_categories')->order_by('category_name', 'ASC')->get()->result();
		}

		/*==================================
		Add News Category Information
	==================================*/

		public function addNewsCategory($data)
		{
			if ($this->db->insert('news_categories', $data)) {
				return true;
			} else {
				return false;
			}
		}

		/*======================================
    	Update News Category Information
    =======================================*/
		public function updateNewsCategory($id, $info)
		{
			if ($this->db->where('category_id', $id)->update('news_categories', $info)) {
				return true;
			} else {
				return false;
			}
		}

		/*===================================
			Delete News Category Information
			====================================*/

		public function deleteNewsCategory($id)
		{
			$data = $this->db->select('*')->from('news_categories')->where('category_id', $id)->get()->row();
			$img = '';
			if ($data->category_image) {
				$image_link = './' . $data->category_image;

				if (file_exists($image_link)) {
					$img = $image_link;
				} else {
					$img = '';
				}
			}

			if ($this->db->where('category_id', $id)->delete('news_categories')) {
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
		/*********** VIDEO SECTION **********/

		/*===================================
			Get Video Category Information
			====================================*/


		public function allVideoCategory($limit, $offset)
		{
			return $this->db->select('*')
				->from('video_categories')
				->order_by('category_name', 'ASC')
				->limit($limit, $offset) // Apply pagination limit and offset
				->get()->result();
		}


		/**
		 * Get all video categories with dynamic video count
		 */
		public function allVideoCategoryWithCount($limit, $offset)
		{
			// Ensure limit and offset are valid integers to avoid SQL errors
			$limit = isset($limit) ? (int) $limit : 10;
			$offset = isset($offset) ? (int) $offset : 0;

			$sql = "SELECT vc.*, 
						   (SELECT COUNT(*) FROM video v WHERE v.video_category_id = vc.category_id) AS video_count
						FROM video_categories vc
						ORDER BY vc.category_name ASC
						LIMIT ?, ?";
			return $this->db->query($sql, [$offset, $limit])->result();
		}

		// Function to count total video categories
		public function countAllVideoCategories()
		{
			return $this->db->from('video_categories')->count_all_results();  // Count total rows in the video_categories table
		}

		/*=======================
			Add Video Category Information
			========================*/

		public function addVideoCategory($data)
		{
			if ($this->db->insert('video_categories', $data)) {
				return true;
			} else {
				return false;
			}
		}

		/*===================================
	     Update Video Category Information
	     ====================================*/

		public function updateVideoCategory($slug, $info)
		{
			if ($this->db->where('category_slug', $slug)->update('video_categories', $info)) {
				return true;
			} else {
				return false;
			}
		}

		/*===================================
			Delete Video Category Information
			====================================*/

		public function deleteVideoCategory($slug)
		{
			$data = $this->db->select('*')->from('video_categories')->where('category_slug', $slug)->get()->row();
			$img = '';
			if ($data->category_image) {
				$image_link = './' . $data->category_image;

				if (file_exists($image_link)) {
					$img = $image_link;
				} else {
					$img = '';
				}
			}

			if ($this->db->where('category_slug', $slug)->delete('video_categories')) {
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
		public function getVideo($limit, $offset)
		{
			return $this->db->select('video.*, user.name, categories.category_name')
				->from('video')
				->join('user', 'user.user_id = video.user_id', 'LEFT')
				->join('video_categories as categories', 'categories.category_id = video.video_category_id', 'LEFT')
				->order_by('video.video_id', 'DESC')
				->limit($limit, $offset)
				->get()->result();
		}

		// Function to count total videos
		public function countAllVideos()
		{
			return $this->db->from('video')->count_all_results();  // Count total rows in the video table
		}


		public function deleteVideo($slug)
		{
			$data = $this->db->select('*')->from('video')->where('video_slug', $slug)->get()->row();

			$img = '';

			if ($data->video_image) {
				$image_link = './' . $data->video_image;

				if (file_exists($image_link)) {
					$img = $image_link;
				} else {
					$img = '';
				}
			}


			if ($this->db->where('video_slug', $slug)->delete('video')) {
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


		/*********** VIDEO SECTION **********/

		/*********** CLASSIFIED SECTION START**********/

		// Get all classifieds with category name and user name
		public function getAllClassifieds($limit = null, $offset = null)
		{
			$this->db->select('classifieds.*, classifieds_categories.category_name, user.name');
			$this->db->from('classifieds');
			$this->db->join('classifieds_categories', 'classifieds.classifieds_category_id = classifieds_categories.category_id', 'left');
			$this->db->join('user', 'classifieds.user_id = user.user_id', 'left');
			if ($limit !== null && $offset !== null) {
				$this->db->limit($limit, $offset);
			} elseif ($limit !== null) {
				$this->db->limit($limit);
			}
			$query = $this->db->get();
			return $query->result();
		}

		/*===================================
			Get classified Category Information
			====================================*/


		public function allClassifiedsCategory($limit, $offset)
		{
			return $this->db->select('*')
				->from('classifieds_categories')
				->order_by('category_name', 'ASC')
				->limit($limit, $offset) // Apply pagination limit and offset
				->get()->result();
		}

		/**
		 * Get all classifieds categories with dynamic classifieds count
		 */
		public function allClassifiedsCategoryWithCount($limit, $offset)
		{
			$sql = "SELECT cc.*, 
								   (SELECT COUNT(*) FROM classifieds c WHERE c.classifieds_category_id = cc.category_id) AS classifieds_count
							FROM classifieds_categories cc
							ORDER BY cc.category_name ASC
							LIMIT ?, ?";
			return $this->db->query($sql, [$offset, $limit])->result();
		}

		// Function to count total classifieds categories
		public function countAllClassifiedsCategories()
		{
			return $this->db->from('classifieds_categories')->count_all_results();  // Count total rows in the classifieds categories table
		}

		/*=======================
			Add classified Category Information
			========================*/

		public function addClassifiedsCategory($data)
		{
			if ($this->db->insert('classifieds_categories', $data)) {
				return true;
			} else {
				return false;
			}
		}
		/*===================================
     		Update classified Category Information
     		====================================*/

		public function updateClassifiedsCategory($slug, $info)
		{
			if ($this->db->where('category_slug', $slug)->update('classifieds_categories', $info)) {
				return true;
			} else {
				return false;
			}
		}
		/*===================================
			Delete classified Category Information
			====================================*/
		public function deleteClassifiedsCategory($slug)
		{
			$data = $this->db->select('*')->from('classifieds_categories')->where('category_slug', $slug)->get()->row();
			$img = '';
			if ($data->category_image) {
				$image_link = './' . $data->category_image;

				if (file_exists($image_link)) {
					$img = $image_link;
				} else {
					$img = '';
				}
			}

			if ($this->db->where('category_slug', $slug)->delete('classifieds_categories')) {
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
		public function getClassifieds($limit, $offset)
		{
			return $this->db->select('classifieds.classifieds_id,classifieds.classifieds_title, classifieds.classifieds_description,classifieds.classifieds_status,classifieds.classifieds_slug,classifieds.created_at,classifieds.classifieds_image, user.user_id,user.name,categories.category_name')
				->from('classifieds')
				->join('user', 'user.user_id = classifieds.user_id', 'LEFT')
				->join('classifieds_categories as categories', 'categories.category_id = classifieds.classifieds_category_id', 'LEFT')
				->order_by('classifieds_id', 'DESC')
				->limit($limit, $offset)  // Apply pagination limit and offset
				->get()->result();
		}

		// Function to count total classifieds
		public function countAllClassifieds2()
		{
			return $this->db->from('classifieds')->count_all_results();  // Count total rows in the classifieds table
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

		/*********** CLASSIFIED SECTION END**********/
		/*********** marketplace SECTION START**********/
		/*===================================
			Get marketplace Category Information
			====================================*/

		public function allmarketplaceCategory()
		{
			return $data = $this->db->select('*')->from('marketplace_categories')->order_by('category_name', 'ASC')->get()->result();
		}
		/*=======================
			Add marketplace Category Information
			========================*/

		public function addmarketplaceCategory($data)
		{
			if ($this->db->insert('marketplace_categories', $data)) {
				return true;
			} else {
				return false;
			}
		}
		/*===================================
     		Update marketplace Category Information
     		====================================*/

		public function updatemarketplaceCategory($id, $info)
		{
			if ($this->db->where('category_id', $id)->update('marketplace_categories', $info)) {
				return true;
			} else {
				return false;
			}
		}
		/*===================================
			Delete marketplace Category Information
			====================================*/
		public function deletemarketplaceCategory($id)
		{
			$data = $this->db->select('*')->from('marketplace_categories')->where('category_id', $id)->get()->row();
			$img = '';
			if ($data->category_image) {
				$image_link = './' . $data->category_image;

				if (file_exists($image_link)) {
					$img = $image_link;
				} else {
					$img = '';
				}
			}

			if ($this->db->where('category_id', $id)->delete('marketplace_categories')) {
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
		public function getmarketplace()
		{
			return $data = $this->db->select('marketplace.marketplace_id,marketplace.marketplace_title, marketplace.marketplace_description,marketplace.marketplace_status,marketplace.created_at,marketplace.marketplace_image, user.user_id,user.name,categories.category_name')->from('marketplace')
				->join('user', 'user.user_id = marketplace.user_id', 'LEFT')
				->join('marketplace_categories as categories', 'categories.category_id = marketplace.marketplace_category_id', 'LEFT')
				->order_by('marketplace_id', 'DESC')
				->get()->result();
		}

		public function deletemarketplace($id)
		{
			$data = $this->db->select('*')->from('marketplace')->where('marketplace_id', $id)->get()->row();

			$img = '';

			if ($data->video_image) {
				$image_link = './' . $data->video_image;

				if (file_exists($image_link)) {
					$img = $image_link;
				} else {
					$img = '';
				}
			}


			if ($this->db->where('marketplace_id', $id)->delete('marketplace')) {
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

		/*********** marketplace SECTION END**********/

		/*********** Rental SECTION START**********/

		/*===================================
			Get Rental Category Information
			====================================*/

		public function getRentalCategories($limit, $offset)
		{
			return $this->db->select('*')
				->from('rental_categories')
				->order_by('category_name', 'ASC')
				->limit($limit, $offset)  // Apply pagination limit and offset
				->get()->result();
		}

		/**
		 * Return rental categories with rental count (paginated)
		 * Compat function used by controller: allRentalCategory($limit, $offset)
		 */
		public function allRentalCategory($limit = null, $offset = null)
		{
			$limit = isset($limit) ? (int)$limit : 10;
			$offset = isset($offset) ? (int)$offset : 0;

			return $this->db->select('rc.*, (SELECT COUNT(*) FROM rental r WHERE r.rental_category_id = rc.category_id) AS rental_count')
				->from('rental_categories as rc')
				->order_by('rc.category_name', 'ASC')
				->limit($limit, $offset)
				->get()->result();
		}

		/**
		 * Get all rental categories with dynamic rental count
		 */
		public function allRentalCategoryWithCount($limit, $offset)
		{
			$sql = "SELECT rc.*, 
								   (SELECT COUNT(*) FROM rental r WHERE r.rental_category_id = rc.category_id) AS rental_count
							FROM rental_categories rc
							ORDER BY rc.category_name ASC
							LIMIT ?, ?";
			return $this->db->query($sql, [$offset, $limit])->result();
		}

		// Function to count total rental categories
		public function countAllRentalCategories()
		{
			return $this->db->from('rental_categories')->count_all_results();  // Count total rows in the rental_categories table
		}

		/*=======================
			Add Rental Category Information
			========================*/

		public function addRentalCategory($data)
		{
			if ($this->db->insert('rental_categories', $data)) {
				return true;
			} else {
				return false;
			}
		}
		/*===================================
     		Update Rental Category Information
     	====================================*/

		public function updateRentalCategory($slug, $info)
		{
			if ($this->db->where('category_slug', $slug)->update('rental_categories', $info)) {
				return true;
			} else {
				return false;
			}
		}
		/*===================================
			Delete Rental Category Information
			====================================*/
		public function deleteRentalCategory($slug)
		{
			$data = $this->db->select('*')->from('rental_categories')->where('category_slug', $slug)->get()->row();
			$img = '';
			if ($data->category_image) {
				$image_link = './' . $data->category_image;

				if (file_exists($image_link)) {
					$img = $image_link;
				} else {
					$img = '';
				}
			}

			if ($this->db->where('category_slug', $slug)->delete('rental_categories')) {
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
		public function getRentals($limit, $offset)
		{
			return $this->db->select('rental.rental_id, rental.rental_title, rental.rental_description, rental.rental_status, rental.rental_slug, rental.created_at, rental.rental_image, user.user_id, user.name, categories.category_name')
				->from('rental')
				->join('user', 'user.user_id = rental.user_id', 'LEFT')
				->join('rental_categories as categories', 'categories.category_id = rental.rental_category_id', 'LEFT')
				->order_by('rental_id', 'DESC')
				->limit($limit, $offset)  // Apply pagination limit and offset
				->get()->result();
		}

		// Function to count total rentals
		public function countAllRentals2()
		{
			return $this->db->from('rental')->count_all_results();  // Count total rows in the rental table
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


		/*********** HOUSE FOR SALE SECTION START**********/


		public function addHouse($data)
		{
			return $this->db->insert('house', $data);
		}


		/*===================================
			Get House Category Information
			====================================*/

		public function getHouseCategories($limit, $offset)
		{
			return $this->db->select('*')
				->from('house_categories')
				->order_by('category_name', 'ASC')
				->limit($limit, $offset)  // Apply pagination limit and offset
				->get()->result();
		}

		// Function to count total house categories
		public function countAllHouseCategories()
		{
			return $this->db->from('house_categories')->count_all_results();  // Count total rows in the house_categories table
		}

		/*=======================
			Add House Category Information
			========================*/

		public function addHouseCategory($data)
		{
			if ($this->db->insert('house_categories', $data)) {
				return true;
			} else {
				return false;
			}
		}
		/*===================================
     		Update House Category Information
     	====================================*/

		public function updateHouseCategory($slug, $info)
		{
			if ($this->db->where('category_slug', $slug)->update('house_categories', $info)) {
				return true;
			} else {
				return false;
			}
		}
		/*===================================
			Delete House Category Information
			====================================*/
		public function deleteHouseCategory($slug)
		{
			$data = $this->db->select('*')->from('house_categories')->where('category_slug', $slug)->get()->row();
			$img = '';
			if ($data->category_image) {
				$image_link = './' . $data->category_image;

				if (file_exists($image_link)) {
					$img = $image_link;
				} else {
					$img = '';
				}
			}

			if ($this->db->where('category_slug', $slug)->delete('house_categories')) {
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

		// All House

		public function getHouses($limit, $offset)
		{
			return $this->db->select('house.house_id, house.house_title, house.house_description, house.house_status, house.created_at, house.house_slug, house.house_image, user.user_id, user.name, categories.category_name')
				->from('house')
				->join('user', 'user.user_id = house.user_id', 'LEFT')
				->join('house_categories as categories', 'categories.category_id = house.house_category_id', 'LEFT')
				->limit($limit, $offset)  // Apply pagination limit and offset
				->order_by('house_id', 'DESC')
				->get()->result();
		}

		// Function to count total houses
		public function countAllHouses2()
		{
			return $this->db->from('house')->count_all_results();  // Count total rows in the house table
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

		/*********** HOUSE FOR SALE SECTION END**********/

		/*==================
    	Add LISTINGS
    ==================*/
		public function addListing($data)
		{
			if ($this->db->insert('listings', $data)) {
				return true;
			} else {
				return false;
			}
		}


		/*==============
		LISTINGS
    ==============*/
		public function getAllListings($limit = NULL, $offset = NULL)
		{
			return $data = $this->db->select('listings.listing_id,listings.listing_name, listings.postal_code,listings.listing_status,listings.listing_id,listings.listing_slug, listings.created_at,listings.listing_image, user.user_id,user.name,categories.category_name,sub_categories.sub_category_name')->from('listings')
				->join('user', 'listings.user_id = user.user_id', 'left')
				->where('is_deleted', 0)
				->join('listing_categories as categories', 'categories.category_id = listings.category_id', 'LEFT')
				->join('listing_sub_categories as sub_categories', 'sub_categories.sub_category_id = listings.sub_category_id', 'LEFT')
				->order_by('listing_id', 'DESC')
				->limit($limit, $offset)
				->get()->result();
		}
		public function getAllDeletedListings($limit = NULL, $offset = NULL)
		{
			return $data = $this->db->select('listings.listing_id,listings.listing_name, listings.postal_code,listings.listing_status,listings.listing_id,listings.listing_slug,listings.created_at,listings.listing_image, user.user_id,user.name,categories.category_name,sub_categories.sub_category_name')->from('listings')
				->join('user', 'listings.user_id = user.user_id', 'left')
				->where('is_deleted', 1)
				->join('listing_categories as categories', 'categories.category_id = listings.category_id', 'LEFT')
				->join('listing_sub_categories as sub_categories', 'sub_categories.sub_category_id = listings.sub_category_id', 'LEFT')
				->order_by('listing_id', 'DESC')
				->limit($limit, $offset)
				->get()->result();
		}

		public function getAllListingNames()
		{
			return $this->db->select('listing_id,listing_name')
				->from('listings')
				->where('is_deleted', 0)
				->get()->result_array();
		}

		public function getListingSingleValue($id, $table_id, $tableName)
		{
			return $data = $this->db->select('*')->from($tableName)->where($table_id, $id)->get()->result_array();
		}

		public function addDuplicateListing($tableName, $data)
		{
			// Remove listing_id to prevent duplication errors
			if (isset($data['listing_id'])) {
				unset($data['listing_id']);
			}

			// Insert the new record with a unique ID
			if ($this->db->insert($tableName, $data)) {
				return TRUE;
			} else {
				return FALSE;
			}
		}

		public function softDeleteListing($slug)
		{
			$data = array('is_deleted' => 1);
			if ($this->db->where('listing_slug', $slug)->update('listings', $data)) {
				return true;
			} else {
				return false;
			}
		}

		public function permanentDeleteListing($slug)
		{
			$data = $this->db->select('*')->from('listings')->where('listing_slug', $slug)->get()->row();

			if (!$data) {
				return false; // Return false if no record is found
			}

			$img = '';

			if (!empty($data->listing_image)) { // Corrected object access
				$image_link = './' . $data->listing_image;

				if (file_exists($image_link)) {
					$img = $image_link;
				}
			}

			if ($this->db->where('listing_slug', $slug)->delete('listings')) {
				if (!empty($img)) {
					unlink($img);
				}
				return true;
			} else {
				return false;
			}
		}


		/*==================
      COMMON FUNCTION
      ==================*/

		/**
		 * Get view counts for given IDs from a specified table.
		 *
		 * @param string $tableName Table name (e.g., 'listings')
		 * @param string $idField Column name for the id (e.g., 'listing_id' or 'listing_slug')
		 * @param string $viewField Column name that stores views (e.g., 'views')
		 * @param array|null $ids Optional array of id values to fetch. If null/empty, fetches all rows.
		 * @return array Associative array of id => views (int). If id field is text (slug), use slugs in keys.
		 */
		public function getViewsForIds($tableName, $idField, $viewField = 'views', $ids = null)
		{
			if (empty($tableName) || empty($idField) || empty($viewField)) return [];

			$this->db->select("{$idField}, {$viewField}")
				->from($tableName);

			if (!empty($ids) && is_array($ids)) {
				$this->db->where_in($idField, $ids);
			}

			$query = $this->db->get();

			$result = [];
			foreach ($query->result() as $row) {
				$key = $row->{$idField};
				$val = isset($row->{$viewField}) ? (int)$row->{$viewField} : 0;
				$result[$key] = $val;
			}

			return $result;
		}

		public function getAllValues($tableName)
		{
			return $data = $this->db->get($tableName)->result();
		}

		public function getSingleValue($id, $table_id, $tableName)
		{
			return $data = $this->db->select('*')->from($tableName)->where($table_id, $id)->get()->row();
		}

		public function add($tableName, $data)
		{
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




		public function getAllUsers($tableName)
		{
			return $data = $this->db->get($tableName)->result();
		}

		/*============================================
		COMMON AJAX ACTIVE/INACTIVE GET FUNCTION
		============================================*/
		public function getSingleAjaxValue($id, $table_id, $tableName)
		{
			return $data = $this->db->select('*')->from($tableName)->where($table_id, $id)->get()->row_array();
		}




		public function getEnquiries()
		{
			return $data = $this->db->get('enquiries')->result();
		}

		public function savedEnquiry()
		{
			return $data = $this->db->where('enquiry_save', 1)->get('enquiries')->result();
		}

		public function deleteEnquiry($id)
		{
			if ($this->db->where('enquiry_id', $id)->delete('enquiries')) {
				return true;
			} else {
				return false;
			}
		}


		public function deleteClaim($id)
		{
			if ($this->db->where('claim_listing_id', $id)->delete('claim_listings')) {
				return true;
			} else {
				return false;
			}
		}

		/*==================
          ADS
          ==================*/
		public function getAllAds()
		{
			$data =  $this->db->select('ads.*,u.name as username,u.profile_image as user_image,u.user_created as user_join,ap.name as ads_price_name')
				->from('ads')
				->where('ads.ad_status', 1)
				->join('user as u', 'ads.u_id = u.user_id')
				->join('ads_prices as ap', 'ads.ads_price_id = ap.id')
				->get()->result();
			return $data;
		}
		public function getAllAdsHistory()
		{
			$data =  $this->db->select('ads.*,u.name as username,u.profile_image as user_image,u.user_created as user_join,ap.name as ads_price_name')
				->from('ads')
				->join('user as u', 'ads.u_id = u.user_id')
				->join('ads_prices as ap', 'ads.ads_price_id = ap.id')
				->get()->result();
			return $data;
		}
		public function getAllInactiveAds()
		{
			$data =  $this->db->select('ads.*,u.name as username,u.profile_image as user_image,u.user_created as user_join,ap.name as ads_price_name')
				->from('ads')
				->where('ads.ad_status', 0)
				->join('user as u', 'ads.u_id = u.user_id')
				->join('ads_prices as ap', 'ads.ads_price_id = ap.id')
				->get()->result();
			return $data;
		}
		public function deleteAds($id)
		{
			$data = $this->db->select('*')->from('ads')->where('id', $id)->get()->row();

			$img = '';

			if ($data->ad_photo) {
				$image_link = './' . $data->ad_photo;

				if (file_exists($image_link)) {
					$img = $image_link;
				} else {
					$img = '';
				}
			}


			if ($this->db->where('id', $id)->delete('ads')) {
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

		public function getSingleAdsAjaxValue($id)
		{
			return $data = $this->db->select('*')->from('ads')->where('id', $id)->get()->row_array();
		}

		/*==================
            ADS PRICE
            ==================*/
		public function allAdsPrices()
		{
			// Select all columns to ensure img and status are included
			return $this->db->select('*')
				->from('ads_prices')
				->order_by('name', 'ASC')
				->get()
				->result();
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

		/*====================
		Pricing Plans Methods
	=====================*/
		public function getAllPricingPlans()
		{
			return $this->db->select('*')->from('plans')->where('is_active', 1)->order_by('sort_order', 'ASC')->get()->result();
		}

		public function getPricingPlan($id)
		{
			return $this->db->select('*')->from('plans')->where('id', $id)->get()->row();
		}

		public function addPricingPlan($data)
		{
			if ($this->db->insert('plans', $data)) {
				return true;
			} else {
				return false;
			}
		}

		public function updatePricingPlan($id, $data)
		{
			$this->db->where('id', $id);
			if ($this->db->update('plans', $data)) {
				return true;
			} else {
				return false;
			}
		}

		public function deletePricingPlan($id)
		{
			$data = array('is_active' => 0);
			$this->db->where('id', $id);
			if ($this->db->update('plans', $data)) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Get durations for a pricing plan
		 */
		public function getPlanDurations($plan_id)
		{
			if (!$plan_id) return [];
			return $this->db->select('*')->from('plan_durations')->where('plan_id', $plan_id)->order_by('duration_months', 'ASC')->get()->result();
		}

		/**
		 * Update a pricing duration price by id
		 */
		public function updatePricingDuration($id, $price)
		{
			if (!$id) return false;
			$price = floatval($price);
			return $this->db->where('id', $id)->update('plan_durations', ['price' => $price, 'updated_at' => date('Y-m-d H:i:s')]);
		}

		/*====================
		Subscription Methods
	=====================*/
		public function getUserActiveSubscription($user_id)
		{
			return $this->db->select('s.*, p.name as plan_name, p.listing_limit, p.product_limit, p.event_limit, p.classified_limit, p.rental_limit, p.house_limit, p.blog_limit, p.video_limit, p.ad_boost_limit, p.featured_listing, p.priority_support, p.analytics_access')
				->from('subscriptions s')
				->join('plans p', 's.plan_id = p.id', 'LEFT')
				->where('s.user_id', $user_id)
				->where('s.status', 'active')
				->where('s.end_date >=', date('Y-m-d'))
				->order_by('s.id', 'DESC')
				->get()->row();
		}

		/**
		 * Get total subscription revenue.
		 * Sums the `total_amount` column from `subscriptions` table.
		 * If $onlyPaid is true, only include subscriptions with payment_status = 'paid' (if that column exists)
		 * Returns float 0.00 when no records found.
		 */
		public function getTotalSubscriptionRevenue($onlyPaid = false)
		{
			// Basic sum of total_amount from subscriptions table
			$sql = "SELECT SUM(total_amount) as total FROM subscriptions";
			$params = array();
			if ($onlyPaid) {
				// If the column payment_status exists and stores 'paid' for completed payments
				$sql .= " WHERE payment_status = ?";
				$params[] = 'paid';
			}
			$query = $this->db->query($sql, $params)->row();
			if ($query && isset($query->total)) {
				return (float) $query->total;
			}
			return 0.00;
		}

		public function createSubscription($data)
		{
			if ($this->db->insert('subscriptions', $data)) {
				return $this->db->insert_id();
			} else {
				return false;
			}
		}

		public function getUserUsage($user_id, $subscription_id = null)
		{
			$this->db->where('user_id', $user_id);
			if ($subscription_id) {
				$this->db->where('subscription_id', $subscription_id);
			}
			return $this->db->get('user_usage_tracking')->row();
		}

		public function updateUserUsage($user_id, $type, $increment = 1)
		{
			$subscription = $this->getUserActiveSubscription($user_id);
			if (!$subscription) return false;

			$usage = $this->getUserUsage($user_id, $subscription->id);

			if (!$usage) {
				// Create new usage record
				$usage_data = array(
					'user_id' => $user_id,
					'subscription_id' => $subscription->id,
					$type . '_used' => $increment,
					'reset_date' => date('Y-m-d', strtotime('+30 days'))
				);
				return $this->db->insert('user_usage_tracking', $usage_data);
			} else {
				// Update existing usage
				$current_usage = $usage->{$type . '_used'};
				$new_usage = $current_usage + $increment;

				$this->db->where('id', $usage->id);
				return $this->db->update('user_usage_tracking', array($type . '_used' => $new_usage));
			}
		}

		public function checkUserLimit($user_id, $type)
		{
			$subscription = $this->getUserActiveSubscription($user_id);
			if (!$subscription) return false;

			$limit = $subscription->{$type . '_limit'};
			if ($limit == -1) return true; // Unlimited

			$usage = $this->getUserUsage($user_id, $subscription->id);
			$used = $usage ? $usage->{$type . '_used'} : 0;

			return $used < $limit;
		}

		public function getUserSubscriptionStats($user_id)
		{
			$subscription = $this->getUserActiveSubscription($user_id);
			if (!$subscription) return null;

			$usage = $this->getUserUsage($user_id, $subscription->id);

			$stats = array(
				'plan_name' => $subscription->plan_name,
				'plan_limits' => array(
					'listing_limit' => $subscription->listing_limit,
					'product_limit' => $subscription->product_limit,
					'event_limit' => $subscription->event_limit,
					'classified_limit' => $subscription->classified_limit,
					'rental_limit' => $subscription->rental_limit,
					'house_limit' => $subscription->house_limit,
					'blog_limit' => $subscription->blog_limit,
					'video_limit' => $subscription->video_limit,
					'ad_boost_limit' => $subscription->ad_boost_limit
				),
				'usage' => array(
					'listing_used' => $usage ? $usage->listing_used : 0,
					'product_used' => $usage ? $usage->product_used : 0,
					'event_used' => $usage ? $usage->event_used : 0,
					'classified_used' => $usage ? $usage->classified_used : 0,
					'rental_used' => $usage ? $usage->rental_used : 0,
					'house_used' => $usage ? $usage->house_used : 0,
					'blog_used' => $usage ? $usage->blog_used : 0,
					'video_used' => $usage ? $usage->video_used : 0,
					'ad_boost_used' => $usage ? $usage->ad_boost_used : 0
				),
				'features' => array(
					'featured_listing' => $subscription->featured_listing,
					'priority_support' => $subscription->priority_support,
					'analytics_access' => $subscription->analytics_access
				),
				'subscription_details' => array(
					'start_date' => $subscription->start_date,
					'end_date' => $subscription->end_date,
					'status' => $subscription->status
				)
			);

			return $stats;
		}


		/*====================
		Subscription Helper Methods
		(Previously in separate helper library)
	=====================*/

		/**
		 * Check if user can add new content of specified type
		 */
		public function canUserAdd($user_id, $type)
		{
			return $this->checkUserLimit($user_id, $type);
		}

		/**
		 * Update user usage when new content is added
		 */
		public function updateUsage($user_id, $type, $increment = 1)
		{
			return $this->updateUserUsage($user_id, $type, $increment);
		}

		/**
		 * Get user's subscription stats
		 */
		public function getUserStats($user_id)
		{
			return $this->getUserSubscriptionStats($user_id);
		}

		/**
		 * Get user's active subscription details
		 */
		public function getActiveSubscription($user_id)
		{
			return $this->getUserActiveSubscription($user_id);
		}

		/**
		 * Check specific feature access
		 */
		public function hasFeatureAccess($user_id, $feature)
		{
			$subscription = $this->getUserActiveSubscription($user_id);
			if (!$subscription) return false;

			return isset($subscription->$feature) && $subscription->$feature == 1;
		}

		/**
		 * Get remaining limit for specific type
		 */
		public function getRemainingLimit($user_id, $type)
		{
			$subscription = $this->getUserActiveSubscription($user_id);
			if (!$subscription) return 0;

			$limit = $subscription->{$type . '_limit'};
			if ($limit == -1) return -1; // Unlimited

			$usage = $this->getUserUsage($user_id, $subscription->id);
			$used = $usage ? $usage->{$type . '_used'} : 0;

			return max(0, $limit - $used);
		}

		/**
		 * Get usage percentage for a specific type
		 */
		public function getUsagePercentage($user_id, $type)
		{
			$subscription = $this->getUserActiveSubscription($user_id);
			if (!$subscription) return 100;

			$limit = $subscription->{$type . '_limit'};
			if ($limit == -1) return 0; // Unlimited

			$usage = $this->getUserUsage($user_id, $subscription->id);
			$used = $usage ? $usage->{$type . '_used'} : 0;

			return $limit > 0 ? round(($used / $limit) * 100, 2) : 100;
		}

		/**
		 * Create new subscription with plan data
		 */
		public function createNewSubscription($user_id, $plan_id, $payment_data = [])
		{
			$plan = $this->getPricingPlan($plan_id);
			if (!$plan) return false;

			$subscription_data = [
				'user_id' => $user_id,
				'plan_id' => $plan_id,
				'status' => 'active',
				'start_date' => date('Y-m-d'),
				'end_date' => date('Y-m-d', strtotime('+' . $plan->duration_days . ' days')),
				'next_billing_date' => date('Y-m-d', strtotime('+' . $plan->duration_days . ' days')),
				'created_at' => date('Y-m-d H:i:s')
			];

			// Add payment transaction ID if provided
			if (isset($payment_data['transaction_id'])) {
				$subscription_data['last_transaction_id'] = $payment_data['transaction_id'];
			}

			return $this->createSubscription($subscription_data);
		}

		/*====================
			Subscriptions Admin Helpers
		=====================*/

		/**
		 * Get list of subscriptions with user and plan info
		 */
		public function getAllSubscriptions($limit = NULL, $offset = NULL)
		{
			$this->db->select('s.*, u.name as user_name, p.name as plan_name, p.id as plan_id')
				->from('subscriptions s')
				->join('user u', 's.user_id = u.user_id', 'LEFT')
				->join('plans p', 's.plan_id = p.id', 'LEFT')
				->order_by('s.id', 'DESC');

			if ($limit !== NULL) {
				$this->db->limit($limit, $offset);
			}

			return $this->db->get()->result();
		}

		public function countAllSubscriptions()
		{
			return $this->db->from('subscriptions')->count_all_results();
		}

		/**
		 * Get single subscription with user and plan
		 */
		public function getSubscriptionById($id)
		{
			// user table has `name` and `email_id` columns per schema
			return $this->db->select('s.*, u.name as user_name, u.email_id as user_email, p.name as plan_name, p.id as plan_id, p.price as plan_price')
				->from('subscriptions s')
				->join('user u', 's.user_id = u.user_id', 'LEFT')
				->join('plans p', 's.plan_id = p.id', 'LEFT')
				->where('s.id', $id)
				->get()
				->row();
		}

		/**
		 * Try to fetch transactions related to a subscription. Will check several common table names and return empty array if none found.
		 */
		public function getSubscriptionTransactions($subscription_id)
		{
			$possibleTables = ['transactions', 'payments', 'subscription_transactions', 'payment_transactions'];
			foreach ($possibleTables as $table) {
				if ($this->db->table_exists($table)) {
					// Try by subscription_id column first
					if ($this->db->field_exists('subscription_id', $table)) {
						return $this->db->select('*')->from($table)->where('subscription_id', $subscription_id)->order_by('id', 'DESC')->get()->result();
					}
					// Fallback: try user or reference columns
					if ($this->db->field_exists('subscription', $table)) {
						return $this->db->select('*')->from($table)->where('subscription', $subscription_id)->order_by('id', 'DESC')->get()->result();
					}
					if ($this->db->field_exists('subscription_id', $table)) {
						return $this->db->select('*')->from($table)->where('subscription_id', $subscription_id)->order_by('id', 'DESC')->get()->result();
					}
				}
			}
			return [];
		}

		/**
		 * Cancel subscription by subscription id
		 */
		public function cancelSubscriptionById($subscription_id)
		{
			$data = [
				'status' => 'canceled',
				'canceled_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			];
			$this->db->where('id', $subscription_id);
			return $this->db->update('subscriptions', $data);
		}

		/**
		 * Permanently delete a subscription record
		 */
		public function deleteSubscriptionById($subscription_id)
		{
			return $this->db->where('id', $subscription_id)->delete('subscriptions');
		}

		/**
		 * Cancel subscription
		 */
		public function cancelSubscription($user_id)
		{
			$subscription = $this->getUserActiveSubscription($user_id);
			if (!$subscription) return false;

			$data = [
				'status' => 'canceled',
				'canceled_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			];

			$this->db->where('id', $subscription->id);
			return $this->db->update('subscriptions', $data);
		}

		/**
		 * Check if subscription is expired
		 */
		public function isSubscriptionExpired($user_id)
		{
			$subscription = $this->getUserActiveSubscription($user_id);
			if (!$subscription) return true;

			return strtotime($subscription->end_date) < strtotime(date('Y-m-d'));
		}

		/**
		 * Get days remaining in subscription
		 */
		public function getDaysRemaining($user_id)
		{
			$subscription = $this->getUserActiveSubscription($user_id);
			if (!$subscription) return 0;

			$end_date = strtotime($subscription->end_date);
			$today = strtotime(date('Y-m-d'));

			return max(0, ceil(($end_date - $today) / (60 * 60 * 24)));
		}

		/**
		 * Validate content addition with subscription limits
		 */
		public function validateContentAddition($user_id, $type)
		{
			return $this->checkUserLimit($user_id, $type);
		}

		/**
		 * Auto-track usage after successful content creation
		 */
		public function trackUsageAfterCreation($user_id, $type)
		{
			return $this->updateUserUsage($user_id, $type, 1);
		}

		/*====================
		    Newsletter Subscribers Methods
		=====================*/

		/**
		 * Get newsletter subscribers (paginated)
		 */
		public function getNewsletterSubscribers($limit = null, $offset = null)
		{
			$this->db->select('*')
				->from('newsletter_subscriptions')
				->order_by('id', 'DESC');
			if ($limit !== null) {
				$this->db->limit($limit, $offset);
			}
			return $this->db->get()->result();
		}

		/**
		 * Count all newsletter subscribers
		 */
		public function countAllNewsletterSubscribers()
		{
			return $this->db->from('newsletter_subscriptions')->count_all_results();
		}

		/**
		 * Delete a newsletter subscriber by id
		 */
		public function deleteNewsletterSubscriber($id)
		{
			return $this->db->where('id', $id)->delete('newsletter_subscriptions');
		}

		/**
		 * Update subscriber confirmation/status (0 or 1)
		 */
		public function updateNewsletterSubscriberStatus($id, $status)
		{
			$data = array('is_confirmed' => (int)$status, 'updated_at' => date('Y-m-d H:i:s'));
			return $this->db->where('id', $id)->update('newsletter_subscriptions', $data);
		}

		/*=========================
			IT / Web services catalog (admin-side)
			==========================*/

		public function getAllServices($limit = null, $offset = 0)
		{
			$this->db->from('services')->order_by('sort_order', 'ASC')->order_by('service_id', 'ASC');
			if ($limit !== null) $this->db->limit((int)$limit, (int)$offset);
			return $this->db->get()->result();
		}

		public function countServices() { return $this->db->count_all('services'); }

		public function getServiceById($id)
		{
			return $this->db->where('service_id', (int)$id)->limit(1)->get('services')->row();
		}

		public function insertService($data)
		{
			$data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');
			return $this->db->insert('services', $data) ? $this->db->insert_id() : null;
		}

		public function updateService($id, $data)
		{
			$data['updated_at'] = date('Y-m-d H:i:s');
			return $this->db->where('service_id', (int)$id)->update('services', $data);
		}

		public function deleteService($id)
		{
			return $this->db->where('service_id', (int)$id)->delete('services');
		}

		public function getAllServiceRequests($status = null, $limit = null, $offset = 0)
		{
			$this->db->select('sr.*, s.name AS service_name, s.slug AS service_slug')
				->from('service_requests sr')
				->join('services s', 's.service_id = sr.service_id', 'left')
				->order_by('sr.created_at', 'DESC');
			if ($status) $this->db->where('sr.status', $status);
			if ($limit !== null) $this->db->limit((int)$limit, (int)$offset);
			return $this->db->get()->result();
		}

		public function getServiceRequestById($id)
		{
			return $this->db->select('sr.*, s.name AS service_name, s.slug AS service_slug')
				->from('service_requests sr')
				->join('services s', 's.service_id = sr.service_id', 'left')
				->where('sr.request_id', (int)$id)
				->limit(1)
				->get()->row();
		}

		public function updateServiceRequestStatus($id, $status, $admin_notes = null)
		{
			$allowed = array('new','in_review','quoted','won','lost','spam');
			if (!in_array($status, $allowed, true)) return false;
			$data = array('status' => $status, 'updated_at' => date('Y-m-d H:i:s'));
			if ($admin_notes !== null) $data['admin_notes'] = $admin_notes;
			return $this->db->where('request_id', (int)$id)->update('service_requests', $data);
		}

		public function deleteServiceRequest($id)
		{
			return $this->db->where('request_id', (int)$id)->delete('service_requests');
		}

		/*=========================
			Newsletter campaigns (admin side)
			See database/migrations/0003_newsletter_campaigns.sql.
			==========================*/

		public function getAllCampaigns($limit = null, $offset = 0)
		{
			$this->db->from('newsletter_campaigns')->order_by('created_at', 'DESC');
			if ($limit !== null) $this->db->limit((int)$limit, (int)$offset);
			return $this->db->get()->result();
		}

		public function getCampaignById($id)
		{
			return $this->db->where('campaign_id', (int)$id)->limit(1)->get('newsletter_campaigns')->row();
		}

		public function createCampaign($data)
		{
			$data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');
			if (empty($data['status'])) $data['status'] = 'draft';
			return $this->db->insert('newsletter_campaigns', $data) ? $this->db->insert_id() : null;
		}

		public function updateCampaign($id, $data)
		{
			$data['updated_at'] = date('Y-m-d H:i:s');
			return $this->db->where('campaign_id', (int)$id)->update('newsletter_campaigns', $data);
		}

		public function deleteCampaign($id)
		{
			$this->db->where('campaign_id', (int)$id)->delete('newsletter_campaign_sends');
			return $this->db->where('campaign_id', (int)$id)->delete('newsletter_campaigns');
		}

		/**
		 * Move a draft campaign to 'queued' and populate the per-recipient
		 * send rows from confirmed newsletter subscribers. Returns the
		 * number of recipients enqueued, or false on failure.
		 */
		public function queueCampaign($campaign_id)
		{
			$campaign = $this->getCampaignById($campaign_id);
			if (!$campaign || $campaign->status !== 'draft') return false;

			$subs = $this->db
				->select('email')
				->from('newsletter_subscriptions')
				->where('is_confirmed', 1)
				->get()->result();

			$now = date('Y-m-d H:i:s');
			$rows = array();
			foreach ($subs as $s) {
				$rows[] = array(
					'campaign_id' => (int)$campaign_id,
					'email'       => $s->email,
					'status'      => 'pending',
					'created_at'  => $now,
				);
			}
			if (!empty($rows)) {
				$this->db->insert_batch('newsletter_campaign_sends', $rows);
			}

			$this->db->where('campaign_id', (int)$campaign_id)->update('newsletter_campaigns', array(
				'status'          => 'queued',
				'total_recipients' => count($rows),
				'updated_at'      => $now,
			));
			return count($rows);
		}

		/**
		 * Pop up to $batch_size pending recipients of the given (queued
		 * or sending) campaign, send each one via the Mailer library,
		 * and update the per-send row. Returns array(sent, failed,
		 * remaining).
		 */
		public function sendCampaignBatch($campaign_id, $batch_size = 25)
		{
			$campaign = $this->getCampaignById($campaign_id);
			if (!$campaign || !in_array($campaign->status, array('queued', 'sending'), true)) {
				return array('sent' => 0, 'failed' => 0, 'remaining' => 0);
			}

			if ($campaign->status === 'queued') {
				$this->db->where('campaign_id', (int)$campaign_id)->update('newsletter_campaigns', array(
					'status'     => 'sending',
					'started_at' => date('Y-m-d H:i:s'),
					'updated_at' => date('Y-m-d H:i:s'),
				));
			}

			$pending = $this->db
				->where('campaign_id', (int)$campaign_id)
				->where('status', 'pending')
				->order_by('send_id', 'ASC')
				->limit((int)$batch_size)
				->get('newsletter_campaign_sends')
				->result();

			$CI =& get_instance();
			$CI->load->library('mailer');

			$sent = 0; $failed = 0;
			foreach ($pending as $row) {
				$ok = $CI->mailer->send($row->email, $campaign->subject, $campaign->body_html);
				if ($ok) {
					$sent++;
					$this->db->where('send_id', $row->send_id)->update('newsletter_campaign_sends', array(
						'status' => 'sent',
						'sent_at' => date('Y-m-d H:i:s'),
					));
				} else {
					$failed++;
					$this->db->where('send_id', $row->send_id)->update('newsletter_campaign_sends', array(
						'status' => 'failed',
						'error'  => 'send_failed',
						'sent_at' => date('Y-m-d H:i:s'),
					));
				}
			}

			// Increment counters on the campaign and mark sent if done.
			$totals = $this->db
				->select("SUM(status='pending') AS pending, SUM(status='sent') AS sent_total, SUM(status='failed') AS failed_total", false)
				->from('newsletter_campaign_sends')
				->where('campaign_id', (int)$campaign_id)
				->get()->row();

			$remaining = (int) ($totals->pending ?? 0);
			$update = array(
				'sent_count'   => (int) ($totals->sent_total ?? 0),
				'failed_count' => (int) ($totals->failed_total ?? 0),
				'updated_at'   => date('Y-m-d H:i:s'),
			);
			if ($remaining === 0) {
				$update['status']      = 'sent';
				$update['finished_at'] = date('Y-m-d H:i:s');
			}
			$this->db->where('campaign_id', (int)$campaign_id)->update('newsletter_campaigns', $update);

			return array('sent' => $sent, 'failed' => $failed, 'remaining' => $remaining);
		}
	}
