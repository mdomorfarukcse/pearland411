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
			// never satisfy an admin access check. Role is read by
			// Admin::requireRole() to gate sensitive screens.
			$role = property_exists($admin, 'role') && $admin->role
				? (string) $admin->role
				: 'editor';
			if (property_exists($admin, 'is_active') && (int) $admin->is_active === 0) {
				return array('status' => FALSE, 'message' => 'This admin account is disabled.', 'url' => 'login');
			}
			$this->session->set_userdata('logged_admin', array(
				'logged_id'   => $admin->admin_id,
				'admin_email' => $admin->admin_email,
				'admin_name'  => isset($admin->admin_name) ? $admin->admin_name : null,
				'role'        => $role,
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
		/**
		 * Word-by-word LIKE across the given columns, same shape as
		 * applyAdminListingSearch(): every term must match somewhere, so
		 * "home services" finds a row whichever order the words are in.
		 *
		 * @param string|null $q
		 * @param array       $columns Qualified column names.
		 */
		private function applyListLikeSearch($q, array $columns)
		{
			$terms = preg_split('/\s+/', trim((string) $q), -1, PREG_SPLIT_NO_EMPTY);
			if (empty($terms) || empty($columns)) {
				return;
			}
			$terms = array_slice(array_unique($terms), 0, 6);

			$this->db->group_start();
			foreach ($terms as $term) {
				$this->db->group_start();
				$first = true;
				foreach ($columns as $column) {
					$first ? $this->db->like($column, $term) : $this->db->or_like($column, $term);
					$first = false;
				}
				$this->db->group_end();
			}
			$this->db->group_end();
		}

		/** @return array Columns the category list searches. */
		private function listingCategorySearchColumns()
		{
			return array('lc.category_name');
		}

		/**
		 * @param int|null    $limit
		 * @param int|null    $offset
		 * @param string|null $q Search text, applied in SQL so it reaches rows
		 *                       on pages the browser was never sent.
		 */
		public function allListingCategory($limit = null, $offset = null, $q = NULL)
		{
			$this->db->select('lc.*,
			(SELECT COUNT(*) FROM listings l WHERE l.category_id = lc.category_id AND (l.is_deleted IS NULL OR l.is_deleted = 0)) as listing_count,
			(SELECT COUNT(*) FROM listing_sub_categories lsc WHERE lsc.category_id = lc.category_id) as sub_category_count');
			$this->db->from('listing_categories lc');
			$this->applyListLikeSearch($q, $this->listingCategorySearchColumns());
			$this->db->order_by('lc.category_id', 'DESC');

			if ($limit !== null && $offset !== null) {
				$this->db->limit($limit, $offset);
			}

			return $this->db->get()->result();
		}

		/**
		 * Row count for the category pager.
		 *
		 * Replaces count(allListingCategory(NULL, NULL)), which pulled every row
		 * — running two correlated subqueries per row — just to measure the pager.
		 *
		 * @param  string|null $q
		 * @return int
		 */
		public function countListingCategories($q = NULL)
		{
			$this->db->from('listing_categories lc');
			$this->applyListLikeSearch($q, $this->listingCategorySearchColumns());

			return (int) $this->db->count_all_results();
		}



		/*=======================
		Duplicate listings
		========================*/

		/**
		 * Businesses the directory is holding more than once.
		 *
		 * The same rule migration 0032 applies: the same name once case and
		 * punctuation are out, AND one hard identifier in common — the phone
		 * number, the street address or the website host. Name alone is never
		 * enough, because Pearland has more than one Subway and they are
		 * separate businesses.
		 *
		 * The migration acts on a group on its own only when the losers are
		 * unclaimed. This returns every group, including the ones it left
		 * alone, because those are the ones that need a person: two claimed
		 * rows for one business is either an owner with two accounts or two
		 * businesses that happen to share a phone line, and no rule can tell
		 * which from the row.
		 *
		 * Reads every published listing once and groups in PHP. That is a few
		 * thousand rows on this directory, and doing it in SQL means the same
		 * REPLACE chain in a second place, drifting from this one.
		 *
		 * @param  int $limit  how many GROUPS to return
		 * @return array of arrays of listing rows, biggest group first
		 */
		public function getDuplicateListingGroups($limit = 50)
		{
			if (!$this->db->table_exists('listings')) return array();

			$rows = $this->db
				->select('l.listing_id, l.listing_name, l.listing_slug, l.listing_mobile,
					l.listing_address, l.listing_website, l.listing_image, l.user_id,
					l.created_at, lc.category_name', FALSE)
				->from('listings l')
				->join('listing_categories lc', 'lc.category_id = l.category_id', 'left')
				->where('(l.is_deleted IS NULL OR l.is_deleted = 0)', NULL, FALSE)
				->where('l.listing_status', 1)
				->order_by('l.listing_id', 'ASC')
				->get()
				->result();

			$by_key = array();
			foreach ($rows as $row) {
				$name = pearland_merge_key($row->listing_name);
				if ($name === '') continue;

				foreach ($this->listingMatchKeys($row, $name) as $key) {
					$by_key[$key][(int) $row->listing_id] = $row;
				}
			}

			// One business can collide on the phone AND the address, which is
			// two keys and one duplicate. Keep the first group a listing turns
			// up in and drop the rest, so it is not reported twice.
			$seen   = array();
			$groups = array();
			foreach ($by_key as $members) {
				if (count($members) < 2) continue;

				$fresh = array();
				foreach ($members as $id => $row) {
					if (isset($seen[$id])) continue;
					$fresh[$id] = $row;
				}
				if (count($fresh) < 2) continue;

				foreach ($fresh as $id => $row) $seen[$id] = TRUE;
				$groups[] = array_values($fresh);
			}

			usort($groups, function ($a, $b) { return count($b) - count($a); });

			return array_slice($groups, 0, max(1, (int) $limit));
		}

		/**
		 * The keys one listing can be matched on. Mirrors the three passes in
		 * migration 0032, in the same order and with the same minimum lengths.
		 */
		private function listingMatchKeys($row, $name_key)
		{
			$keys = array();

			$phone = preg_replace('/\D+/', '', (string) ($row->listing_mobile ?? ''));
			if (strlen($phone) >= 7) {
				$keys[] = 'p:' . $name_key . '|' . substr($phone, -10);
			}

			$address = pearland_merge_key($row->listing_address ?? '');
			if (strlen($address) >= 6) {
				$keys[] = 'a:' . $name_key . '|' . $address;
			}

			$site = strtolower(trim((string) ($row->listing_website ?? '')));
			$site = preg_replace('#^https?://#', '', $site);
			$site = preg_replace('#^www\.#', '', $site);
			$site = explode('/', $site);
			$site = $site[0];
			if (strlen($site) >= 4) {
				$keys[] = 'w:' . $name_key . '|' . $site;
			}

			return $keys;
		}

		/**
		 * Hide $hide_id as a duplicate of $keep_id.
		 *
		 * The same soft delete the Trash button does, plus the audit row
		 * migration 0032 writes, so a listing hidden here and one hidden by
		 * the migration look the same afterwards and restore the same way.
		 *
		 * @return array {ok: bool, message: string}
		 */
		public function hideDuplicateListing($hide_id, $keep_id)
		{
			$hide_id = (int) $hide_id;
			$keep_id = (int) $keep_id;

			if ($hide_id < 1 || $keep_id < 1 || $hide_id === $keep_id) {
				return array('ok' => FALSE, 'message' => 'Pick the listing to hide and the one it duplicates.');
			}

			$hide = $this->getSingleValue($hide_id, 'listing_id', 'listings');
			$keep = $this->getSingleValue($keep_id, 'listing_id', 'listings');
			if (!$hide || !$keep) {
				return array('ok' => FALSE, 'message' => 'One of those listings no longer exists.');
			}

			$this->ensureListingDuplicateTable();

			$this->db->replace('listing_duplicate_merges', array(
				'listing_id'      => $hide_id,
				'kept_listing_id' => $keep_id,
				'listing_name'    => $hide->listing_name,
				'matched_on'      => 'admin',
				'merged_at'       => date('Y-m-d H:i:s'),
			));

			if (!$this->softDeleteListing($hide_id)) {
				return array('ok' => FALSE, 'message' => 'That listing could not be hidden.');
			}

			return array(
				'ok'      => TRUE,
				'message' => '"' . $hide->listing_name . '" is hidden as a duplicate of "'
					. $keep->listing_name . '". It is in Trash if you need it back.',
			);
		}

		/**
		 * The audit table migration 0032 creates. Created here too, so the
		 * screen works on an install that has not run 0032 yet.
		 */
		private function ensureListingDuplicateTable()
		{
			if ($this->db->table_exists('listing_duplicate_merges')) return;

			$this->db->query(
				'CREATE TABLE IF NOT EXISTS `listing_duplicate_merges` (
					`id`              INT(11) NOT NULL AUTO_INCREMENT,
					`listing_id`      INT(11) NOT NULL,
					`kept_listing_id` INT(11) NOT NULL,
					`listing_name`    VARCHAR(255) NULL DEFAULT NULL,
					`matched_on`      VARCHAR(16)  NULL DEFAULT NULL,
					`merged_at`       DATETIME NOT NULL,
					PRIMARY KEY (`id`),
					UNIQUE KEY `ldm_listing_uniq` (`listing_id`),
					KEY `ldm_kept_idx` (`kept_listing_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
				  COMMENT="Listings hidden as duplicates by migration 0032 / the admin Duplicates screen"'
			);
		}

		/*=======================
		Duplicate categories: detection and merging
		========================*/

		/**
		 * Categories that read as the same thing, grouped.
		 *
		 * Compared on pearland_merge_key() — lower-cased, "&" read as "and",
		 * punctuation dropped — which is the same rule migration 0031 applies
		 * in SQL. After that migration has run this normally returns nothing;
		 * it stays because a category added by hand afterwards can recreate
		 * the problem, and because it is the list the merge panel offers.
		 *
		 * @return array merge_key => array of rows (category_id, name, slug,
		 *               listing_count), busiest first
		 */
		/**
		 * Every listing category with its listing count, A-Z — the two
		 * pickers of the "merge any two categories" form. The lookalike panel
		 * only ever offered pairs whose NAMES matched, so the judgement calls
		 * the client actually asked about (Finance / Financial Services, the
		 * Education variants) could not be merged from any screen.
		 */
		public function getCategoriesForMerge()
		{
			if (!$this->db->table_exists('listing_categories')) return array();

			return $this->db
				->select('lc.category_id, lc.category_name,
					(SELECT COUNT(*) FROM listings l
					  WHERE l.category_id = lc.category_id
					    AND (l.is_deleted IS NULL OR l.is_deleted = 0)) AS listing_count', FALSE)
				->from('listing_categories lc')
				->order_by('lc.category_name', 'ASC')
				->get()
				->result();
		}

		public function getDuplicateCategoryGroups()
		{
			if (!$this->db->table_exists('listing_categories')) return array();

			$rows = $this->db
				->select('lc.category_id, lc.category_name, lc.category_slug,
					(SELECT COUNT(*) FROM listings l
					  WHERE l.category_id = lc.category_id
					    AND (l.is_deleted IS NULL OR l.is_deleted = 0)) AS listing_count', FALSE)
				->from('listing_categories lc')
				->order_by('lc.category_name', 'ASC')
				->get()
				->result();

			$by_key = array();
			foreach ($rows as $row) {
				$key = pearland_merge_key($row->category_name);
				if ($key === '') continue;
				$by_key[$key][] = $row;
			}

			$groups = array();
			foreach ($by_key as $key => $members) {
				if (count($members) < 2) continue;
				usort($members, function ($a, $b) {
					$diff = (int) $b->listing_count - (int) $a->listing_count;
					return $diff !== 0 ? $diff : ((int) $a->category_id - (int) $b->category_id);
				});
				$groups[$key] = $members;
			}

			return $groups;
		}

		/**
		 * The category whose name reads the same as $name, if there is one.
		 *
		 * @param  string $name
		 * @param  int    $exclude_id  the row being renamed, which is not a clash
		 *                             with itself
		 * @return object|null
		 */
		public function findCategoryByMergeKey($name, $exclude_id = 0)
		{
			$key = pearland_merge_key($name);
			if ($key === '') return NULL;

			$rows = $this->db
				->select('category_id, category_name')
				->from('listing_categories')
				->get()
				->result();

			foreach ($rows as $row) {
				if ((int) $row->category_id === (int) $exclude_id) continue;
				if (pearland_merge_key($row->category_name) === $key) return $row;
			}

			return NULL;
		}

		/**
		 * Move everything under $from_id to $to_id, then delete $from_id.
		 *
		 * The PHP half of migration 0031: the migration merges what is
		 * mechanically the same name, this merges what an operator decides is
		 * the same category. Both write the same `listing_category_merges`
		 * audit row, so either can be traced or unpicked afterwards.
		 *
		 * @return array {ok: bool, message: string}
		 */
		public function mergeListingCategory($from_id, $to_id)
		{
			$from_id = (int) $from_id;
			$to_id   = (int) $to_id;

			if ($from_id < 1 || $to_id < 1) {
				return array('ok' => FALSE, 'message' => 'Pick both a category to merge and one to merge it into.');
			}
			if ($from_id === $to_id) {
				return array('ok' => FALSE, 'message' => 'A category cannot be merged into itself.');
			}

			$from = $this->getSingleValue($from_id, 'category_id', 'listing_categories');
			$to   = $this->getSingleValue($to_id,   'category_id', 'listing_categories');
			if (!$from || !$to) {
				return array('ok' => FALSE, 'message' => 'One of those categories no longer exists.');
			}

			$moved = (int) $this->db->where('category_id', $from_id)->count_all_results('listings');

			$this->ensureCategoryMergeTable();

			$this->db->trans_start();

			$this->db->insert('listing_category_merges', array(
				'from_category_id'   => $from_id,
				'from_category_name' => $from->category_name,
				'from_category_slug' => isset($from->category_slug) ? $from->category_slug : NULL,
				'to_category_id'     => $to_id,
				'listings_moved'     => $moved,
				'merged_at'          => date('Y-m-d H:i:s'),
			));

			// The survivor inherits the slug only if it has none of its own:
			// rewriting a live slug breaks every link already pointing at it.
			if (empty($to->category_slug) && !empty($from->category_slug)) {
				$this->db->where('category_id', $to_id)
					->update('listing_categories', array('category_slug' => $from->category_slug));
			}

			$this->db->where('category_id', $from_id)
				->update('listings', array('category_id' => $to_id));

			if ($this->db->table_exists('listing_sub_categories')
				&& $this->db->field_exists('category_id', 'listing_sub_categories')) {
				$this->db->where('category_id', $from_id)
					->update('listing_sub_categories', array('category_id' => $to_id));
			}

			// Category-targeted ad bookings (migration 0024).
			if ($this->db->table_exists('ads') && $this->db->field_exists('category_id', 'ads')) {
				$this->db->where('category_id', $from_id)
					->update('ads', array('category_id' => $to_id));
			}

			$this->db->where('category_id', $from_id)->delete('listing_categories');

			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				return array('ok' => FALSE, 'message' => 'That merge could not be completed; nothing was changed.');
			}

			return array(
				'ok'      => TRUE,
				'message' => 'Merged "' . $from->category_name . '" into "' . $to->category_name . '". '
					. $moved . ' listing' . ($moved === 1 ? '' : 's') . ' moved.',
			);
		}

		/**
		 * The audit table migration 0031 creates. Created here too, so the
		 * merge control works on an install that has not run 0031 yet.
		 */
		private function ensureCategoryMergeTable()
		{
			if ($this->db->table_exists('listing_category_merges')) return;

			$this->db->query(
				'CREATE TABLE IF NOT EXISTS `listing_category_merges` (
					`id`                 INT(11) NOT NULL AUTO_INCREMENT,
					`from_category_id`   INT(11) NOT NULL,
					`from_category_name` VARCHAR(255) NULL DEFAULT NULL,
					`from_category_slug` VARCHAR(191) NULL DEFAULT NULL,
					`to_category_id`     INT(11) NOT NULL,
					`listings_moved`     INT(11) NOT NULL DEFAULT 0,
					`merged_at`          DATETIME NOT NULL,
					PRIMARY KEY (`id`),
					KEY `lcm_from_idx` (`from_category_id`),
					KEY `lcm_to_idx`   (`to_category_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
				  COMMENT="Categories merged by migration 0031 / the admin Merge tool"'
			);
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
		/** @return array Columns the sub-category list searches. */
		private function listingSubCategorySearchColumns()
		{
			return array('sub_cate.sub_category_name', 'main_cate.category_name');
		}

		/**
		 * @param int|null    $limit
		 * @param int         $offset
		 * @param string|null $q Search text, applied in SQL. Searching the parent
		 *                       category name too means "Home Services" lists all
		 *                       of its sub-categories.
		 */
		public function allListingSubCategory($limit = NULL, $offset = 0, $q = NULL)
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
				);
			$this->applyListLikeSearch($q, $this->listingSubCategorySearchColumns());
			$this->db->order_by('sub_cate.sub_category_id', 'DESC');

			if ($limit !== NULL) {
				$this->db->limit($limit, $offset);
			}

			return $this->db->get()->result();
		}

		/**
		 * Row count for the sub-category pager.
		 *
		 * Query-builder rather than count_all() so it can carry the same join and
		 * search as the list above — a count that ignored the filter would page
		 * a filtered list against the unfiltered total.
		 *
		 * @param  string|null $q
		 * @return int
		 */
		public function countAllListingSubCategory($q = NULL)
		{
			$this->db->from('listing_sub_categories as sub_cate')
				->join(
					'listing_categories as main_cate',
					'sub_cate.category_id = main_cate.category_id',
					'LEFT'
				);
			$this->applyListLikeSearch($q, $this->listingSubCategorySearchColumns());

			return (int) $this->db->count_all_results();
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
			$this->db->select('events.event_id, events.user_id, events.admin_id, events.event_start_date, events.event_name, events.event_status, events.event_slug, events.created_at, COALESCE(admin.admin_name, user.name) AS name', false);
			$this->db->from('events');
			$this->db->join('user', 'user.user_id = events.user_id', 'left');
			$this->db->join('admin', 'admin.admin_id = events.admin_id', 'left');
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
			// Confirm the row is actually there before reporting success.
			// CI3's update() returns TRUE whenever the query *runs*, so an
			// UPDATE that matched nothing still read as a save — which is how
			// renaming a video category could report "Successfully Updated"
			// without changing anything. affected_rows() alone is not enough:
			// MySQL reports 0 when the submitted values equal the stored ones,
			// and re-saving an unchanged form is not a failure.
			$exists = $this->db->select('category_id')
				->from('video_categories')
				->where('category_slug', $slug)
				->get()->row();

			if (!$exists) {
				return false;
			}

			return (bool) $this->db->where('category_slug', $slug)->update('video_categories', $info);
		}

		/*===================================
			Delete Video Category Information
			====================================*/

		/**
		 * Delete a video category. Its videos are kept, moved to "no
		 * category" (NULL, or 0 where the column is NOT NULL).
		 *
		 * @return int|false how many videos were uncategorised, FALSE if no
		 *                   such category or the delete failed
		 */
		public function deleteVideoCategory($slug)
		{
			$data = $this->db->select('*')->from('video_categories')->where('category_slug', $slug)->get()->row();
			if (!$data) {
				return false;
			}

			$moved = (int) $this->db->where('video_category_id', (int) $data->category_id)->count_all_results('video');
			if ($moved > 0) {
				// Decide NULL vs 0 BEFORE writing. Trying NULL and falling back on
				// the error does not work on PHP 8.1+: mysqli throws, and CI 3.1.11
				// lets that escape as an HTTP 500 whatever db_debug says.
				$nullable = $this->db->query(
					'SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS
					  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
					array($this->db->dbprefix('video'), 'video_category_id')
				)->row();
				$none = ($nullable && strtoupper((string) $nullable->IS_NULLABLE) === 'YES') ? NULL : 0;
				$this->db->where('video_category_id', (int) $data->category_id)->update('video', array('video_category_id' => $none));
			}

			if (!$this->db->where('category_id', (int) $data->category_id)->delete('video_categories')) {
				return false;
			}

			if (!empty($data->category_image) && file_exists('./' . $data->category_image)) {
				@unlink('./' . $data->category_image);
			}

			return $moved;
		}

		/** A video_categories.category_slug no other category has. */
		public function uniqueVideoCategorySlug($name, $except_id = 0)
		{
			$base = function_exists('pearland_slug') ? pearland_slug((string) $name) : '';
			if ($base === '') $base = 'category';

			$slug = $base;
			for ($n = 2; $this->db->where('category_slug', $slug)->where('category_id !=', (int) $except_id)->count_all_results('video_categories') > 0; $n++) {
				$slug = $base . '-' . $n;
			}
			return $slug;
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

		/**
		 * Update a marketplace category, keyed on its slug.
		 *
		 * The same defect the video category edit had: this keyed on
		 * `category_id`, an INT, while its only caller
		 * (Admin::edit_marketplace_category) loads the row by `category_slug`
		 * and passes that slug straight through. MySQL casts the string to 0,
		 * nothing matches — and CI3's update() returns TRUE whenever the query
		 * runs, so the admin was told "Successfully Updated" every time while
		 * the category never changed. Found while fixing tracker row 25.
		 */
		public function updatemarketplaceCategory($slug, $info)
		{
			$exists = $this->db->select('category_id')
				->from('marketplace_categories')
				->where('category_slug', $slug)
				->get()->row();

			if (!$exists) {
				return false;
			}

			return (bool) $this->db->where('category_slug', $slug)->update('marketplace_categories', $info);
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

		/*=========================================
		BULK / CSV-EXCEL LISTING IMPORT HELPERS
		=========================================*/

		/**
		 * Resolve a listing category by its (case-insensitive) name.
		 * Returns the category_id or null when no match is found.
		 */
		public function getCategoryIdByName($name)
		{
			$name = trim((string) $name);
			if ($name === '') {
				return null;
			}
			$row = $this->db->select('category_id')
				->from('listing_categories')
				->where('LOWER(category_name)', strtolower($name))
				->get()->row();
			return $row ? (int) $row->category_id : null;
		}

		/**
		 * Resolve a listing sub-category by name, optionally scoped to a category.
		 * Returns the sub_category_id or null when no match is found.
		 */
		public function getSubCategoryIdByName($name, $category_id = null)
		{
			$name = trim((string) $name);
			if ($name === '') {
				return null;
			}
			$this->db->select('sub_category_id')
				->from('listing_sub_categories')
				->where('LOWER(sub_category_name)', strtolower($name));
			if (!empty($category_id)) {
				$this->db->where('category_id', $category_id);
			}
			$row = $this->db->get()->row();
			return $row ? (int) $row->sub_category_id : null;
		}

		/**
		 * Resolve a user_id from an email address (case-insensitive).
		 * Returns the user_id or null when no match is found.
		 */
		public function getUserIdByEmail($email)
		{
			$email = trim((string) $email);
			if ($email === '') {
				return null;
			}
			$row = $this->db->select('user_id')
				->from('user')
				->where('LOWER(email_id)', strtolower($email))
				->get()->row();
			return $row ? (int) $row->user_id : null;
		}

		/**
		 * Return a fallback user_id to attribute imported listings to when a
		 * row does not name a user. Falls back to the first available user.
		 */
		public function getDefaultImportUserId()
		{
			$row = $this->db->select('user_id')
				->from('user')
				->order_by('user_id', 'ASC')
				->limit(1)
				->get()->row();
			return $row ? (int) $row->user_id : null;
		}

		/**
		 * True when a listing slug already exists (used to keep slugs unique
		 * on import).
		 */
		/*=================================================
		LISTING <-> SUB-CATEGORY (many-to-many, 0023)
		=================================================*/

		/**
		 * The sub_category_ids attached to a listing.
		 *
		 * @return int[]
		 */
		public function listingSubCategoryIds($listing_id)
		{
			if (!$this->db->table_exists('listing_sub_category_map')) {
				return array();
			}

			$rows = $this->db->select('sub_category_id')
				->from('listing_sub_category_map')
				->where('listing_id', (int) $listing_id)
				->get()->result();

			return array_map(function ($r) { return (int) $r->sub_category_id; }, $rows);
		}

		/**
		 * Replace a listing's sub-categories with exactly $ids.
		 *
		 * Deletes then inserts rather than diffing: the set is small, and the
		 * write is a single logical "these are the subcategories now".
		 *
		 * @param int   $listing_id
		 * @param int[] $ids
		 */
		public function saveListingSubCategories($listing_id, array $ids)
		{
			if (!$this->db->table_exists('listing_sub_category_map')) {
				return;
			}

			$listing_id = (int) $listing_id;
			$ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

			$this->db->where('listing_id', $listing_id)->delete('listing_sub_category_map');

			if (empty($ids)) {
				return;
			}

			$batch = array();
			foreach ($ids as $id) {
				$batch[] = array('listing_id' => $listing_id, 'sub_category_id' => $id);
			}
			$this->db->insert_batch('listing_sub_category_map', $batch);
		}

		public function listingSlugExists($slug)
		{
			return $this->db->from('listings')
				->where('listing_slug', $slug)
				->count_all_results() > 0;
		}

		/**
		 * Whether $table already has a row whose $column is $slug.
		 *
		 * The general form of listingSlugExists(), for the content tables that
		 * had no uniqueness pass at all. Table and column are checked against
		 * the schema rather than interpolated blind, since both reach the query
		 * builder as identifiers.
		 */
		public function slugExists($table, $column, $slug)
		{
			if (!$this->db->table_exists($table) || !$this->db->field_exists($column, $table)) {
				return false;
			}

			return $this->db->from($table)
				->where($column, $slug)
				->count_all_results() > 0;
		}

		/**
		 * Fetch every active (non-deleted) listing with the human-readable
		 * category / sub-category names and owner email, in the exact shape
		 * the CSV export / import template expects.
		 */
		public function getListingsForExport()
		{
			return $this->db->select(
				'listings.listing_name, ' .
				'categories.category_name AS category, ' .
				'sub_categories.sub_category_name AS sub_category, ' .
				'user.email_id AS user_email, ' .
				'listings.listing_mobile, listings.listing_whatsapp, listings.listing_email, ' .
				'listings.listing_website, listings.listing_address, listings.county_name, ' .
				'listings.postal_code, listings.listing_lat, listings.listing_lng, ' .
				'listings.listing_description, listings.service_locations, listings.service_offered, ' .
				'listings.listing_video, listings.listing_seo_title, listings.listing_seo_keywords, ' .
				'listings.listing_seo_description'
			)
				->from('listings')
				->join('user', 'listings.user_id = user.user_id', 'left')
				->join('listing_categories as categories', 'categories.category_id = listings.category_id', 'left')
				->join('listing_sub_categories as sub_categories', 'sub_categories.sub_category_id = listings.sub_category_id', 'left')
				->where('listings.is_deleted', 0)
				->order_by('listings.listing_id', 'DESC')
				->get()->result_array();
		}


		/*==============
		LISTINGS
    ==============*/
		/**
		 * The columns an admin listings search looks in.
		 *
		 * Name and address are the obvious ones; postal code and category are
		 * there because that is how staff actually look a business up.
		 */
		private function adminListingSearchColumns()
		{
			return array(
				'listings.listing_name',
				'listings.listing_address',
				'listings.postal_code',
				'listings.listing_email',
				'listings.listing_mobile',
				'categories.category_name',
				'user.name',
			);
		}

		/**
		 * Add a word-by-word search to the query currently being built.
		 *
		 * Every word has to appear somewhere in the row, but not in the same
		 * column and not next to each other — so "central bbq" finds "Central
		 * Texas BBQ". Same rule as the public search
		 * (Usermodel::applySearchTerms), which is private to that model.
		 * Capped at six words so a pasted paragraph cannot build a
		 * hundred-clause query.
		 */
		private function applyAdminListingSearch($q)
		{
			$terms = preg_split('/\s+/', trim((string) $q), -1, PREG_SPLIT_NO_EMPTY);
			if (empty($terms)) {
				return;
			}
			$terms   = array_slice(array_unique($terms), 0, 6);
			$columns = $this->adminListingSearchColumns();

			$this->db->group_start();
			foreach ($terms as $term) {
				$this->db->group_start();
				$first = true;
				foreach ($columns as $column) {
					$first ? $this->db->like($column, $term) : $this->db->or_like($column, $term);
					$first = false;
				}
				$this->db->group_end();
			}
			$this->db->group_end();
		}

		/**
		 * @param int|null    $limit
		 * @param int|null    $offset
		 * @param string|null $q Search text. Applied in SQL, across every
		 *                       listing — the admin table's box used to be a
		 *                       browser-side filter over the ten rows the
		 *                       server had already sent, so a business on page
		 *                       four could not be found by typing its name.
		 */
		public function getAllListings($limit = NULL, $offset = NULL, $q = NULL)
		{
			$this->db->select('listings.listing_id,listings.listing_name, listings.postal_code,listings.listing_status,listings.listing_id,listings.listing_slug, listings.created_at,listings.listing_image, user.user_id,user.name,categories.category_name,sub_categories.sub_category_name')->from('listings')
				->join('user', 'listings.user_id = user.user_id', 'left')
				->where('is_deleted', 0)
				->join('listing_categories as categories', 'categories.category_id = listings.category_id', 'LEFT')
				->join('listing_sub_categories as sub_categories', 'sub_categories.sub_category_id = listings.sub_category_id', 'LEFT');

			$this->applyAdminListingSearch($q);

			return $this->db->order_by('listing_id', 'DESC')
				->limit($limit, $offset)
				->get()->result();
		}

		/**
		 * How many listings the admin table has, honouring the same search.
		 *
		 * The pager used to be sized with count(getAllListings(NULL, NULL)),
		 * which pulled every row with all three joins on every page load just
		 * to measure it. Distinct from countAllListings(), which is the
		 * dashboard's count_all('listings') and includes trashed rows.
		 */
		public function countAdminListings($q = NULL)
		{
			$this->db->from('listings')
				->join('user', 'listings.user_id = user.user_id', 'left')
				->where('listings.is_deleted', 0)
				->join('listing_categories as categories', 'categories.category_id = listings.category_id', 'LEFT');

			$this->applyAdminListingSearch($q);

			return (int) $this->db->count_all_results();
		}
		public function getAllDeletedListings($limit = NULL, $offset = NULL)
		{
			return $data = $this->db->select('listings.listing_id,listings.listing_name, listings.postal_code,listings.listing_status,listings.listing_id,listings.listing_slug,listings.created_at,listings.deleted_date,listings.listing_image, user.user_id,user.name,categories.category_name,sub_categories.sub_category_name')->from('listings')
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

		/**
		 * The last database error message, for surfacing a rejected write to the
		 * operator instead of redirecting them to a blank form.
		 */
		public function lastDbError()
		{
			$err = $this->db->error();
			return empty($err['message']) ? 'No further detail was reported by the database.' : $err['message'];
		}

		/*
		 * The three listing delete/restore methods below key on `listing_id`.
		 *
		 * They used to key on `listing_slug`, which is not unique: both
		 * add_listing_scratch() and edit_listing() derived the slug from the
		 * business name with no uniqueness pass, so two businesses called the
		 * same thing carried the identical slug — and one click on Delete
		 * flipped is_deleted on both of them. That is the reported "deleting one
		 * duplicate listing also deletes the original". Permanent delete was
		 * worse: it removed every row sharing the slug and unlinked the image
		 * they had in common.
		 */

		public function softDeleteListing($listing_id)
		{
			$data = array(
				'is_deleted'   => 1,
				'deleted_date' => date('Y-m-d H:i:s'),
			);
			return (bool) $this->db->where('listing_id', (int) $listing_id)->update('listings', $data);
		}

		/**
		 * Bring a listing back out of the trash. Clears the deletion stamp —
		 * the old code *set* deleted_date to now while restoring, and wrote the
		 * whole row object back, which meant every column went into the UPDATE.
		 */
		public function restoreListing($listing_id)
		{
			$data = array(
				'is_deleted'   => 0,
				'deleted_date' => NULL,
			);
			return (bool) $this->db->where('listing_id', (int) $listing_id)->update('listings', $data);
		}

		public function permanentDeleteListing($listing_id)
		{
			$listing_id = (int) $listing_id;
			$data = $this->db->select('*')->from('listings')->where('listing_id', $listing_id)->get()->row();

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

			if ($this->db->where('listing_id', $listing_id)->delete('listings')) {
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

		/**
		 * Users sorted by display name — used to populate the "host"
		 * dropdown on event / blog / classifieds forms so admins don't
		 * have to scroll through years of test accounts.
		 *
		 * Hardened against schema drift: the query is wrapped in
		 * try/catch and returns an empty array on any DB error. That
		 * keeps the form page rendering (with an empty dropdown)
		 * instead of throwing a 500 across 10+ admin screens if a
		 * column expectation is wrong.
		 *
		 * NOTE: the `user` table has no soft-delete column, so we
		 * don't filter on one — earlier versions of this method
		 * referenced `is_deleted` and 500'd on production where the
		 * column doesn't exist.
		 */
		public function getActiveUsersForDropdown()
		{
			try {
				// listing_count comes back with the row so user_option_label()
				// can say which of these accounts actually own anything — the
				// one signal that separates a live customer from a leftover
				// test row in a list of business-shaped account names.
				return $this->db
					->select('u.user_id, u.name, u.email_id, '
						// Suspended accounts stay in the list (a record they own
						// must still show its owner) but are labelled.
						. ($this->db->field_exists('banned', 'user') ? 'u.banned, ' : '0 AS banned, ')
						. '(SELECT COUNT(*) FROM `listings` l
						  WHERE l.user_id = u.user_id AND l.is_deleted = 0) AS listing_count', false)
					->from('user u')
					->where("(u.name IS NOT NULL AND u.name != '')", null, false)
					->order_by('u.name', 'ASC')
					->get()->result();
			} catch (Exception $e) {
				log_message('error', 'getActiveUsersForDropdown failed: ' . $e->getMessage());
				return array();
			}
		}

		/**
		 * Every member account, for the read-only review list at /admin/users.
		 *
		 * There was no user screen in the admin at all, so "kindly review each
		 * user and provide context if those are final" could not be done by
		 * anyone: the only place accounts appeared was inside a dropdown on
		 * the listing form. Read-only on purpose — deleting a user orphans
		 * their listings, and that decision needs its own screen.
		 */
		public function getUserDirectory($limit, $offset, $q = '')
		{
			$bind  = array();
			$where = '1=1';

			$q = trim((string) $q);
			if ($q !== '') {
				$parts = array();
				foreach (preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY) as $term) {
					$like    = '%' . $term . '%';
					$parts[] = "(u.name LIKE ? OR u.email_id LIKE ?
						OR EXISTS (SELECT 1 FROM `listings` l2
						            WHERE l2.user_id = u.user_id
						              AND l2.is_deleted = 0
						              AND l2.listing_name LIKE ?))";
					$bind[] = $like; $bind[] = $like; $bind[] = $like;
				}
				$where = '(' . implode(' AND ', $parts) . ')';
			}

			$verified = $this->db->field_exists('email_verified', 'user')
				? 'u.email_verified' : 'NULL';

			$banned = $this->db->field_exists('banned', 'user') ? 'u.banned' : '0';

			$sql = "SELECT u.user_id, u.name, u.email_id, u.mobile_number, u.user_created,
					{$verified} AS email_verified, {$banned} AS banned,
					(SELECT COUNT(*) FROM `listings` l
					  WHERE l.user_id = u.user_id AND l.is_deleted = 0) AS listing_count,
					(SELECT GROUP_CONCAT(l.listing_name ORDER BY l.listing_id SEPARATOR ', ')
					   FROM `listings` l
					  WHERE l.user_id = u.user_id AND l.is_deleted = 0) AS businesses
				FROM `user` u
				WHERE {$where}
				ORDER BY listing_count DESC, u.name ASC
				LIMIT " . (int) $offset . ", " . (int) $limit;

			return $this->db->query($sql, $bind)->result();
		}

		/**
		 * Suspend (1) or restore (0) a member. Usermodel::login() refuses a
		 * suspended account and User::__construct() ends a session that is
		 * already open, so it takes effect on the member's next click.
		 */
		public function setUserBanned($user_id, $banned)
		{
			if (!$this->db->field_exists('banned', 'user')) return false;
			return $this->db->where('user_id', (int) $user_id)
				->update('user', array('banned' => $banned ? 1 : 0));
		}

		/**
		 * Everything a member owns, by kind — the guard on deleting one.
		 * A member who owns anything is suspended, not deleted: deleting them
		 * would leave listings, posts and payments pointing at nobody.
		 *
		 * @return array label => count, only the non-zero ones
		 */
		public function userOwnership($user_id)
		{
			$user_id = (int) $user_id;
			$checks = array(
				'listings'      => array('listings', 'user_id'),
				'events'        => array('events', 'user_id'),
				'blog posts'    => array('blogs', 'user_id'),
				'news articles' => array('news', 'user_id'),
				'videos'        => array('video', 'user_id'),
				'products'      => array('products', 'user_id'),
				'classifieds'   => array('classifieds', 'user_id'),
				'houses'        => array('house', 'user_id'),
				'rentals'       => array('rental', 'user_id'),
				'deals'         => array('coupons', 'coupon_user_id'),
				'subscriptions' => array('subscriptions', 'user_id'),
				'payments'      => array('transactions', 'user_id'),
				'claims'        => array('claim_listings', 'user_id'),
			);
			$owned = array();
			foreach ($checks as $label => $c) {
				if (!$this->db->table_exists($c[0]) || !$this->db->field_exists($c[1], $c[0])) continue;
				$n = (int) $this->db->where($c[1], $user_id)->count_all_results($c[0]);
				if ($n > 0) $owned[$label] = $n;
			}
			return $owned;
		}

		/** Delete a member who owns nothing. @return bool */
		public function deleteEmptyUser($user_id)
		{
			$user_id = (int) $user_id;
			if ($user_id < 1 || $this->userOwnership($user_id)) return false;

			foreach (array('user_usage_tracking', 'payment_cards') as $t) {
				if ($this->db->table_exists($t) && $this->db->field_exists('user_id', $t)) {
					$this->db->where('user_id', $user_id)->delete($t);
				}
			}
			return $this->db->where('user_id', $user_id)->delete('user');
		}

		/*
		 * Coupons (admin). The table predates the repo and its columns vary,
		 * so these stick to the columns User::add_coupon writes.
		 */
		private function couponAdminSearch($q)
		{
			$q = trim((string) $q);
			if ($q === '') return;
			$this->db->group_start()
				->like('c.coupon_name', $q)
				->or_like('c.coupon_code', $q)
				->or_like('u.name', $q)
				->or_like('u.email_id', $q)
				->group_end();
		}

		public function getCouponsAdmin($limit, $offset, $q = '')
		{
			if (!$this->db->table_exists('coupons')) return array();
			$this->db->select('c.*, u.name AS owner_name, u.email_id AS owner_email,
				(SELECT l.listing_name FROM listings l
				  WHERE l.user_id = c.coupon_user_id AND (l.is_deleted IS NULL OR l.is_deleted = 0)
				  ORDER BY l.listing_id LIMIT 1) AS listing_name', FALSE)
				->from('coupons c')
				->join('user u', 'u.user_id = c.coupon_user_id', 'left');
			$this->couponAdminSearch($q);
			return $this->db->order_by('c.coupon_id', 'DESC')
				->limit((int) $limit, (int) $offset)
				->get()->result();
		}

		public function countCouponsAdmin($q = '')
		{
			if (!$this->db->table_exists('coupons')) return 0;
			$this->db->from('coupons c')->join('user u', 'u.user_id = c.coupon_user_id', 'left');
			$this->couponAdminSearch($q);
			return (int) $this->db->count_all_results();
		}

		public function getCouponAdmin($coupon_id)
		{
			if (!$this->db->table_exists('coupons')) return NULL;
			return $this->db->where('coupon_id', (int) $coupon_id)->limit(1)->get('coupons')->row();
		}

		public function setCouponStatus($coupon_id, $on)
		{
			return $this->db->where('coupon_id', (int) $coupon_id)
				->update('coupons', array('coupon_status' => $on ? 1 : 0));
		}

		public function deleteCouponAdmin($coupon_id)
		{
			$row = $this->getCouponAdmin($coupon_id);
			if (!$row) return false;
			if (!$this->db->where('coupon_id', (int) $coupon_id)->delete('coupons')) return false;

			if (!empty($row->coupon_photo)) {
				$base = FCPATH . ltrim($row->coupon_photo, '/');
				foreach (array($base,
					preg_replace('/\.[a-z0-9]+$/i', '_thumb.jpg', $base),
					preg_replace('/\.[a-z0-9]+$/i', '_medium.jpg', $base)) as $f) {
					if ($f && is_file($f)) @unlink($f);
				}
			}
			return true;
		}

		/** Row count for getUserDirectory(), for the pager. */
		public function countUserDirectory($q = '')
		{
			$bind  = array();
			$where = '1=1';

			$q = trim((string) $q);
			if ($q !== '') {
				$parts = array();
				foreach (preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY) as $term) {
					$like    = '%' . $term . '%';
					$parts[] = "(u.name LIKE ? OR u.email_id LIKE ?
						OR EXISTS (SELECT 1 FROM `listings` l2
						            WHERE l2.user_id = u.user_id
						              AND l2.is_deleted = 0
						              AND l2.listing_name LIKE ?))";
					$bind[] = $like; $bind[] = $like; $bind[] = $like;
				}
				$where = '(' . implode(' AND ', $parts) . ')';
			}

			$row = $this->db->query("SELECT COUNT(*) AS c FROM `user` u WHERE {$where}", $bind)->row();

			return $row ? (int) $row->c : 0;
		}

		/**
		 * Active admin (staff) accounts for the event "Host" dropdown, so the
		 * list only shows our own recognisable names. Excludes banned and
		 * (where the column exists) inactive admins.
		 */
		public function getActiveAdminsForDropdown()
		{
			try {
				$this->db
					->select('admin_id, admin_name, admin_email')
					->from('admin')
					->where("(admin_name IS NOT NULL AND admin_name != '')", null, false);

				if ($this->db->field_exists('banned', 'admin')) {
					$this->db->where('banned', 0);
				}
				if ($this->db->field_exists('is_active', 'admin')) {
					$this->db->where('is_active', 1);
				}

				return $this->db->order_by('admin_name', 'ASC')->get()->result();
			} catch (Exception $e) {
				log_message('error', 'getActiveAdminsForDropdown failed: ' . $e->getMessage());
				return array();
			}
		}

		/*============================================
		COMMON AJAX ACTIVE/INACTIVE GET FUNCTION
		============================================*/
		public function getSingleAjaxValue($id, $table_id, $tableName)
		{
			return $data = $this->db->select('*')->from($tableName)->where($table_id, $id)->get()->row_array();
		}




		/**
		 * Enquiries for the admin list, newest first.
		 *
		 * $type filters on `enquiry_type`, which is what separates a package
		 * callback from a general contact-form message — see migration 0026.
		 * '' means every type. The SELECT used to be unordered as well as
		 * unfiltered, so the newest message could land anywhere in the table.
		 *
		 * @param  string $type Optional enquiry_type to restrict to.
		 * @return array
		 */
		public function getEnquiries($type = '')
		{
			$type = trim((string) $type);
			if ($type !== '') $this->db->where('enquiry_type', $type);

			return $this->db->order_by('enquiry_created_at', 'DESC')
							->order_by('enquiry_id', 'DESC')
							->get('enquiries')->result();
		}

		/**
		 * How many enquiries there are of each type, for the filter tabs.
		 *
		 * @return array enquiry_type => count
		 */
		public function getEnquiryTypeCounts()
		{
			$rows = $this->db->select('enquiry_type, COUNT(*) AS total')
							 ->group_by('enquiry_type')
							 ->get('enquiries')->result();

			$counts = array();
			foreach ($rows as $row) {
				$counts[(string) $row->enquiry_type] = (int) $row->total;
			}

			return $counts;
		}

		public function savedEnquiry()
		{
			return $this->db->where('enquiry_save', 1)
							->order_by('enquiry_created_at', 'DESC')
							->order_by('enquiry_id', 'DESC')
							->get('enquiries')->result();
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
		/**
		 * The zones a business can be sold inside ONE category — the
		 * "Category Specific Banners" section of /pricing-plan — with the
		 * package price each is sold at. A zone is an ads_prices row whose
		 * slot_key is the slug of a `category-banners` plan.
		 */
		public function getCategoryBannerZones()
		{
			if (!$this->db->field_exists('slot_key', 'ads_prices')) return array();
			try {
				return $this->db
					->select('ap.id, ap.name, ap.size, ap.slot_key, p.name AS plan_name, p.price, p.duration_days')
					->from('ads_prices ap')
					->join('plans p', 'p.slug = ap.slot_key', 'inner')
					->where('p.category', 'category-banners')
					->order_by('p.sort_order', 'ASC')
					->get()->result();
			} catch (Exception $e) {
				return array();
			}
		}

		/** Category banners booked for one business (ads.listing_id, migration 0036). */
		public function getListingCategoryAds($listing_id)
		{
			if (!$this->db->field_exists('listing_id', 'ads')) return array();
			return $this->db
				->select('a.*, ap.name AS zone_name, ap.size, ap.slot_key, lc.category_name')
				->from('ads a')
				->join('ads_prices ap', 'ap.id = a.ads_price_id', 'left')
				->join('listing_categories lc', 'lc.category_id = a.category_id', 'left')
				->where('a.listing_id', (int) $listing_id)
				->order_by('a.ad_end_date', 'DESC')
				->get()->result();
		}

		public function getAllAds()
		{
			// The category join MUST be LEFT. CodeIgniter's join() defaults to
			// INNER, and ads.category_id is NULL for every site-wide booking —
			// which today is most of the book — so an inner join would empty
			// this list rather than annotate it.
			$data =  $this->db->select('ads.*,u.name as username,u.profile_image as user_image,u.user_created as user_join,ap.name as ads_price_name,lc.category_name as ad_category_name')
				->from('ads')
				->where('ads.ad_status', 1)
				// LEFT: a banner booked from Business Listings for a business
				// nobody has claimed yet has no account (u_id 0), and an inner
				// join hid it from this list while it ran on the site.
				->join('user as u', 'ads.u_id = u.user_id', 'left')
				->join('ads_prices as ap', 'ads.ads_price_id = ap.id')
				->join('listing_categories as lc', 'lc.category_id = ads.category_id', 'left')
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

		/** Every plan, active or not, for the admin screen. */
		public function getAllPricingPlansAdmin()
		{
			return $this->db->from('plans')
				->order_by('is_active', 'DESC')
				->order_by('sort_order', 'ASC')
				->order_by('id', 'ASC')
				->get()->result();
		}

		/** @return int|false the new plan id */
		public function addPricingPlan($data)
		{
			foreach (array_keys($data) as $col) {
				if (!$this->db->field_exists($col, 'plans')) unset($data[$col]);
			}
			if ($this->db->insert('plans', $data)) {
				return (int) $this->db->insert_id();
			}
			return false;
		}

		/** A plans.slug no other plan has: `name` → `name`, `name-2`, `name-3`… */
		public function uniquePlanSlug($source)
		{
			$base = function_exists('pearland_slug') ? pearland_slug((string) $source) : '';
			if ($base === '') $base = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', (string) $source), '-'));
			if ($base === '') $base = 'plan';
			$base = substr($base, 0, 90);

			if (!$this->db->field_exists('slug', 'plans')) return $base;

			$slug = $base;
			for ($n = 2; $this->db->where('slug', $slug)->count_all_results('plans') > 0; $n++) {
				$slug = $base . '-' . $n;
			}
			return $slug;
		}

		public function nextPlanSortOrder()
		{
			$row = $this->db->select_max('sort_order', 'm')->get('plans')->row();
			return ($row && $row->m !== NULL) ? ((int) $row->m + 1) : 1;
		}

		/**
		 * Make the plan's base-term plan_durations row (the one whose length
		 * equals plans.duration_days) carry plans.price, creating it if the
		 * plan has none.
		 *
		 * The two stores used to drift: /pricing-plan prints plans.price, but
		 * the checkout charges the matching plan_durations row when one
		 * exists — so editing either one alone put two prices on the site.
		 */
		public function syncPlanBaseTerm($plan_id)
		{
			if (!$this->db->table_exists('plan_durations')) return false;
			$plan = $this->getPricingPlan((int) $plan_id);
			if (!$plan) return false;

			$days = (int) $plan->duration_days > 0 ? (int) $plan->duration_days : 30;
			$row  = $this->db->where('plan_id', (int) $plan->id)
				->where('duration_days', $days)
				->order_by('id', 'ASC')->limit(1)
				->get('plan_durations')->row();

			if ($row) {
				return $this->updatePricingDuration($row->id, (float) $plan->price, array(
					'currency'  => (string) $plan->currency,
					'is_active' => 1,
				));
			}

			$months = $days >= 28 ? (int) round($days / 30) : 0;
			return (bool) $this->addPlanDuration((int) $plan->id, $months, $days, (float) $plan->price, (string) ($plan->currency ?: 'USD'));
		}

		/** The reverse: a base-term row was re-priced, so re-price the plan. */
		public function syncPlanPriceFromTerm($term)
		{
			if (!$term || empty($term->plan_id)) return false;
			$plan = $this->getPricingPlan((int) $term->plan_id);
			if (!$plan) return false;

			$days = (int) $plan->duration_days > 0 ? (int) $plan->duration_days : 30;
			if ((int) $term->duration_days !== $days) return false;

			return $this->updatePricingPlan((int) $plan->id, array(
				'price'      => round((float) $term->price, 2),
				'updated_at' => date('Y-m-d H:i:s'),
			));
		}

		public function updatePricingPlan($id, $data)
		{
			foreach (array_keys($data) as $col) {
				if (!$this->db->field_exists($col, 'plans')) unset($data[$col]);
			}
			if (empty($data)) return false;
			$this->db->where('id', (int) $id);
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
			if (!$this->db->table_exists('plan_durations')) return [];
			return $this->db->select('*')->from('plan_durations')->where('plan_id', $plan_id)->order_by('duration_months', 'ASC')->get()->result();
		}

		public function getPlanDurationById($id)
		{
			if (!$id || !$this->db->table_exists('plan_durations')) return null;
			return $this->db->where('id', (int) $id)->limit(1)->get('plan_durations')->row();
		}

		/**
		 * Add a term to a plan (e.g. a discounted 12-month option). Without
		 * this the admin could only re-price the terms that already existed,
		 * so longer terms could never be offered.
		 */
		public function addPlanDuration($plan_id, $months, $days, $price, $currency = 'USD')
		{
			$plan_id = (int) $plan_id;
			if (!$plan_id || !$this->db->table_exists('plan_durations')) return false;

			$months = max(0, (int) $months);
			$days   = max(0, (int) $days);
			if (!$days && $months) $days = $months * 30;
			if (!$days) return false;

			$row = array(
				'plan_id'         => $plan_id,
				'duration_months' => $months,
				'duration_days'   => $days,
				'price'           => floatval($price),
				'currency'        => strtoupper(substr(trim($currency) ?: 'USD', 0, 10)),
				'is_active'       => 1,
				'created_at'      => date('Y-m-d H:i:s'),
				'updated_at'      => date('Y-m-d H:i:s'),
			);
			foreach (array_keys($row) as $col) {
				if (!$this->db->field_exists($col, 'plan_durations')) unset($row[$col]);
			}
			return $this->db->insert('plan_durations', $row) ? $this->db->insert_id() : false;
		}

		public function deletePlanDuration($id)
		{
			if (!$id || !$this->db->table_exists('plan_durations')) return false;
			return $this->db->where('id', (int) $id)->delete('plan_durations');
		}

		/**
		 * Update a pricing duration price by id
		 */
		/**
		 * Update one plan_durations row.
		 *
		 * Only writes columns that actually exist: older databases predate
		 * `currency`/`updated_at` (see migration 0015), and blindly setting a
		 * missing column made the UPDATE fail — which is why saving a duration
		 * price silently did nothing.
		 *
		 * @param int   $id
		 * @param float $price
		 * @param array $extra optional duration_months / duration_days / currency / is_active
		 */
		public function updatePricingDuration($id, $price, $extra = array())
		{
			if (!$id) return false;

			$data = array('price' => floatval($price));

			if (isset($extra['duration_months']) && $extra['duration_months'] !== '') {
				$data['duration_months'] = max(0, (int) $extra['duration_months']);
			}
			if (isset($extra['duration_days']) && $extra['duration_days'] !== '') {
				$data['duration_days'] = max(0, (int) $extra['duration_days']);
			} elseif (isset($data['duration_months']) && $data['duration_months'] > 0) {
				// Keep the day count — which drives every expiry calculation —
				// in step when only the month count was edited.
				$data['duration_days'] = $data['duration_months'] * 30;
			}
			if (isset($extra['currency']) && $extra['currency'] !== '') {
				$data['currency'] = strtoupper(substr(trim($extra['currency']), 0, 10));
			}
			if (isset($extra['is_active'])) {
				$data['is_active'] = (int) $extra['is_active'] ? 1 : 0;
			}
			$data['updated_at'] = date('Y-m-d H:i:s');

			foreach (array_keys($data) as $col) {
				if (!$this->db->field_exists($col, 'plan_durations')) {
					unset($data[$col]);
				}
			}
			if (empty($data)) return false;

			return $this->db->where('id', (int) $id)->update('plan_durations', $data);
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
			try {
				if (!$this->db->table_exists('newsletter_subscriptions')) return 0;
				return $this->db->from('newsletter_subscriptions')->count_all_results();
			} catch (Exception $e) {
				log_message('error', 'countAllNewsletterSubscribers failed: ' . $e->getMessage());
				return 0;
			}
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
			Sub-admin / role management
			See database/migrations/0005_admin_roles.sql.
			==========================*/

		public function getAllAdmins()
		{
			return $this->db
				->from('admin')
				->order_by('admin_id', 'ASC')
				->get()->result();
		}

		public function getAdminByIdRaw($id)
		{
			return $this->db
				->where('admin_id', (int)$id)
				->limit(1)
				->get('admin')->row();
		}

		public function adminEmailExists($email, $exclude_id = 0)
		{
			$this->db->where('admin_email', $email);
			if ($exclude_id) $this->db->where('admin_id !=', (int)$exclude_id);
			return $this->db->count_all_results('admin') > 0;
		}

		public function createAdmin($data, $plain_password)
		{
			$data['admin_password'] = password_hash($plain_password, PASSWORD_DEFAULT);
			$data['admin_created']  = date('Y-m-d H:i:s');
			if (!isset($data['role']))      $data['role']      = 'editor';
			if (!isset($data['is_active'])) $data['is_active'] = 1;
			return $this->db->insert('admin', $data) ? $this->db->insert_id() : null;
		}

		public function updateAdminMeta($id, $data, $plain_password = null)
		{
			if ($plain_password !== null && $plain_password !== '') {
				$data['admin_password'] = password_hash($plain_password, PASSWORD_DEFAULT);
			}
			return $this->db->where('admin_id', (int)$id)->update('admin', $data);
		}

		public function deleteAdmin($id)
		{
			// Refuse to delete the last super_admin so the system is
			// never left without anyone who can manage roles.
			$row = $this->getAdminByIdRaw($id);
			if (!$row) return false;
			if (($row->role ?? '') === 'super_admin') {
				$other_super = $this->db
					->where('role', 'super_admin')
					->where('admin_id !=', (int)$id)
					->where('is_active', 1)
					->count_all_results('admin');
				if ($other_super < 1) return false;
			}
			return $this->db->where('admin_id', (int)$id)->delete('admin');
		}

		/*=========================
			Newsletter campaigns (admin side)
			See database/migrations/0003_newsletter_campaigns.sql.
			==========================*/

		public function getAllCampaigns($limit = null, $offset = 0)
		{
			try {
				if (!$this->db->table_exists('newsletter_campaigns')) return array();
				$this->db->from('newsletter_campaigns')->order_by('created_at', 'DESC');
				if ($limit !== null) $this->db->limit((int)$limit, (int)$offset);
				return $this->db->get()->result();
			} catch (Exception $e) {
				log_message('error', 'getAllCampaigns failed: ' . $e->getMessage());
				return array();
			}
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

		/*=====================================================================
			Customer billing — /admin/billing
			Card on file, one-off payments, and the recurring schedule that
			Cli::renew_subscriptions charges. Columns added by
			database/migrations/0028_admin_billing.sql.
		======================================================================*/

		/**
		 * The FROM clause shared by the billing list, its count and the
		 * per-business page.
		 *
		 * Keyed on the LISTING, not the account. 0028 built this around `user`,
		 * because that is what `subscriptions` and `payment_cards` are keyed on —
		 * but the listings here were bulk-loaded under a handful of data-entry
		 * logins, so one account owns dozens of unrelated businesses. Searching
		 * "central bbq" returned the import account "10 dollars Galveston shuttle"
		 * with a hundred business names strung after it, and billing that row
		 * would have billed all of them at once.
		 *
		 * A subscription belongs to this business when `listing_id` matches. The
		 * card and the account stay on `user`, because that is where Helcim
		 * vaults them and one owner paying for three of their businesses uses one
		 * card.
		 */
		private function billingFromSql()
		{
			return "FROM `listings` b
				LEFT JOIN `user` u ON u.user_id = b.user_id
				LEFT JOIN `subscriptions` s
				       ON s.id = (SELECT s2.id
				                    FROM `subscriptions` s2
				                   WHERE s2.listing_id = b.listing_id
				                ORDER BY (s2.status = 'active') DESC, s2.id DESC
				                   LIMIT 1)
				LEFT JOIN `plans` p ON p.id = s.plan_id";
		}

		/**
		 * WHERE fragment + bindings shared by the list and the count.
		 *
		 * With no search the list is limited to businesses that have a billing
		 * relationship — a subscription or a payment. Two thousand imported
		 * listings would otherwise bury the handful who actually pay us.
		 *
		 * A search runs across EVERY listing, because the common case is setting
		 * up a business that has never been billed. Terms match word by word
		 * against the business name, and against the owning account's name and
		 * email so an owner can still be found by who they are.
		 */
		private function billingScopeWhere($q)
		{
			$q = trim((string) $q);

			$base = 'b.is_deleted = 0';

			if ($q === '') {
				return array(
					'sql' => "({$base} AND (s.id IS NOT NULL
						OR EXISTS (SELECT 1 FROM `transactions` t2 WHERE t2.listing_id = b.listing_id)))",
					'bind' => array(),
				);
			}

			$terms = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY);
			$parts = array();
			$bind  = array();

			foreach ($terms as $term) {
				$like    = '%' . $term . '%';
				$parts[] = "(b.listing_name LIKE ? OR u.name LIKE ? OR u.email_id LIKE ?)";
				$bind[] = $like;
				$bind[] = $like;
				$bind[] = $like;
			}

			return array('sql' => '(' . $base . ' AND ' . implode(' AND ', $parts) . ')', 'bind' => $bind);
		}

		/** One page of the billing list — one row per business. */
		public function getBillingCustomers($limit, $offset, $q = '')
		{
			$w = $this->billingScopeWhere($q);

			$sql = "SELECT b.listing_id, b.listing_name, b.listing_slug,
					u.user_id, u.name AS owner_name, u.email_id AS owner_email,
					s.id                 AS subscription_id,
					s.status             AS sub_status,
					s.end_date           AS end_date,
					s.next_billing_date  AS next_billing_date,
					s.auto_renew         AS auto_renew,
					s.currency           AS currency,
					s.custom_amount      AS custom_amount,
					s.renewal_attempts   AS renewal_attempts,
					s.last_renewal_error AS last_renewal_error,
					p.name               AS plan_name,
					p.price              AS plan_price,
					(SELECT COUNT(*) FROM `payment_cards` pc
					  WHERE pc.user_id = b.user_id
					    AND pc.helcim_card_token IS NOT NULL) AS card_count
				" . $this->billingFromSql() . "
				WHERE " . $w['sql'] . "
				ORDER BY (s.status = 'active') DESC, b.listing_name ASC
				LIMIT " . (int) $offset . ", " . (int) $limit;

			return $this->db->query($sql, $w['bind'])->result();
		}

		/** Row count for the same scope, for the pager. */
		public function countBillingCustomers($q = '')
		{
			$w   = $this->billingScopeWhere($q);
			$sql = "SELECT COUNT(*) AS c " . $this->billingFromSql() . " WHERE " . $w['sql'];
			$row = $this->db->query($sql, $w['bind'])->row();

			return $row ? (int) $row->c : 0;
		}

		/**
		 * One business for the billing detail page: the listing, the account that
		 * owns it (and therefore holds the card), and the subscription attached
		 * to this business.
		 *
		 * Deliberately two queries with `SELECT *` on the subscription, rather
		 * than one join naming every column.
		 *
		 * Naming them is what took this page down with a 500: it asked for
		 * `s.duration_id` and `s.duration_days`, and **no migration has ever
		 * created those columns** — they are written only by
		 * activateHelcimSubscription(), so on a database where no self-serve
		 * checkout has ever completed they simply do not exist, and MySQL answers
		 * a missing column with error 1054 rather than a NULL. A billing screen
		 * must not fall over because of a column the customer's history never
		 * happened to create; `SELECT *` plus `??` degrades to "not recorded"
		 * instead.
		 *
		 * The two rows are flattened onto one object with the names the view
		 * expects, which also sidesteps the collision that made the single join
		 * fragile: `s.user_id` overwriting `u.user_id` and blanking the owner of
		 * the very business being looked at.
		 */
		public function getBillingCustomer($listing_id)
		{
			$listing_id = (int) $listing_id;

			$row = $this->db->query(
				"SELECT b.listing_id, b.listing_name, b.listing_slug, b.user_id,
					u.name, u.email_id, u.mobile_number,
					b.listing_name AS businesses
				FROM `listings` b
				LEFT JOIN `user` u ON u.user_id = b.user_id
				WHERE b.listing_id = ? AND b.is_deleted = 0
				LIMIT 1",
				array($listing_id)
			)->row();

			if (!$row) {
				return null;
			}

			$sub = null;
			if ($this->db->table_exists('subscriptions')
				&& $this->db->field_exists('listing_id', 'subscriptions')) {
				$sub = $this->db->from('subscriptions')
					->where('listing_id', $listing_id)
					->order_by("(status = 'active')", 'DESC', false)
					->order_by('id', 'DESC')
					->limit(1)->get()->row();
			}

			// Every one of these is optional. A column this schema never created
			// reads as NULL and the screen says "not recorded", which is the truth.
			$row->subscription_id    = $sub ? (int) $sub->id : null;
			$row->plan_id            = $sub->plan_id            ?? null;
			$row->pending_plan_id    = $sub->pending_plan_id    ?? null;
			$row->duration_id        = $sub->duration_id        ?? null;
			$row->duration_days      = $sub->duration_days      ?? null;
			$row->custom_amount      = $sub->custom_amount      ?? null;
			$row->sub_status         = $sub->status             ?? null;
			$row->start_date         = $sub->start_date         ?? null;
			$row->end_date           = $sub->end_date           ?? null;
			$row->next_billing_date  = $sub->next_billing_date  ?? null;
			$row->auto_renew         = $sub->auto_renew         ?? null;
			$row->total_amount       = $sub->total_amount       ?? null;
			$row->currency           = $sub->currency           ?? null;
			$row->billing_cycle      = $sub->billing_cycle      ?? null;
			$row->payment_status     = $sub->payment_status     ?? null;
			$row->renewal_attempts   = $sub->renewal_attempts   ?? null;
			$row->last_renewal_error = $sub->last_renewal_error ?? null;
			$row->grace_until        = $sub->grace_until        ?? null;

			$row->plan_name  = null;
			$row->plan_price = null;
			if (!empty($row->plan_id)) {
				$plan = $this->db->select('name, price')->from('plans')
					->where('id', (int) $row->plan_id)->limit(1)->get()->row();
				if ($plan) {
					$row->plan_name  = $plan->name;
					$row->plan_price = $plan->price;
				}
			}

			return $row;
		}

		/**
		 * Every active plan term, in one query, so the billing screen can swap
		 * the term dropdown when staff pick a different plan without a round
		 * trip per plan.
		 */
		public function getAllPlanDurations()
		{
			if (!$this->db->table_exists('plan_durations')) {
				return array();
			}

			return $this->db->from('plan_durations')
				->where('is_active', 1)
				->order_by('plan_id', 'ASC')
				->order_by('duration_months', 'ASC')
				->get()->result();
		}

		/**
		 * The packages the billing screen can put a business on.
		 *
		 * Not Usermodel::getAllPlans(), which orders by `plans.sort_order`
		 * unconditionally — and which the billing screen was the only caller of
		 * anywhere in the codebase, so nothing had ever proved that column
		 * exists here. Ordering is applied only when the column is really
		 * there; a plan list in the wrong order is a cosmetic problem, a
		 * missing column is a 500.
		 */
		public function getPlansForBilling()
		{
			if (!$this->db->table_exists('plans')) {
				return array();
			}

			$this->db->from('plans');
			if ($this->db->field_exists('sort_order', 'plans')) {
				$this->db->order_by('sort_order', 'ASC');
			}
			$this->db->order_by('name', 'ASC');

			$out = array();
			foreach ($this->db->get()->result() as $plan) {
				// `is_active` predates this repo's migrations; treat a table
				// without it as "every plan is sellable".
				if (!property_exists($plan, 'is_active') || (int) $plan->is_active === 1) {
					$out[] = $plan;
				}
			}

			return $out;
		}

		/** Vaulted cards for a customer, default first. */
		public function getCustomerCards($user_id)
		{
			if (!$this->db->table_exists('payment_cards')) {
				return array();
			}

			return $this->db->from('payment_cards')
				->where('user_id', (int) $user_id)
				->order_by('is_default', 'DESC')
				->order_by('id', 'DESC')
				->get()->result();
		}

		/**
		 * Recent payments for one business, newest first.
		 *
		 * Scoped by listing rather than by account: the import logins own
		 * dozens of businesses, so an account-wide history would pool every
		 * unrelated business's payments into each one's page.
		 *
		 * Payments taken before 0029 have no listing_id, so they are included
		 * for the owning account when nothing is attached to this business
		 * yet — losing a customer's payment history to a schema change would
		 * be worse than showing a little too much.
		 */
		public function getListingTransactions($listing_id, $user_id, $limit = 25)
		{
			if (!$this->db->table_exists('transactions')) {
				return array();
			}

			if ($this->db->field_exists('listing_id', 'transactions')) {
				$this->db->group_start()
					->where('listing_id', (int) $listing_id)
					->or_group_start()
						->where('listing_id IS NULL', null, false)
						->where('user_id', (int) $user_id)
					->group_end()
				->group_end();
			} else {
				$this->db->where('user_id', (int) $user_id);
			}

			return $this->db->from('transactions')
				->order_by('id', 'DESC')
				->limit((int) $limit)
				->get()->result();
		}

		/**
		 * Put a Helcim card token on file for a customer and make it the one
		 * the renewal job will charge.
		 *
		 * Re-vaulting the same token updates the existing row instead of
		 * adding a duplicate — HelcimPay.js returns the same token for the
		 * same card, so staff re-entering a card the customer already has on
		 * file should not leave two rows behind.
		 *
		 * Columns from 0028 are written only when present, so the screen still
		 * works on a database where that migration has not been run yet.
		 */
		public function vaultCustomerCard($user_id, $args)
		{
			$user_id = (int) $user_id;
			$token   = isset($args['helcim_card_token']) ? trim((string) $args['helcim_card_token']) : '';
			if (!$user_id || $token === '') {
				return false;
			}

			$now = date('Y-m-d H:i:s');

			// Only the last four digits are ever stored; the PAN is tokenized
			// in the browser and never reaches this server.
			$row = array(
				'card_holder_name' => isset($args['card_holder_name']) && $args['card_holder_name'] !== ''
					? substr((string) $args['card_holder_name'], 0, 100) : 'Card on file',
				'card_last4'  => isset($args['card_last4']) && $args['card_last4'] !== '' ? (string) $args['card_last4'] : '0000',
				'exp_month'   => !empty($args['exp_month']) ? (int) $args['exp_month'] : 1,
				'exp_year'    => !empty($args['exp_year']) ? (int) $args['exp_year'] : 2099,
				'is_default'  => 1,
				'updated_at'  => $now,
				'helcim_card_token' => $token,
			);
			if (!empty($args['helcim_customer_code'])) {
				$row['helcim_customer_code'] = (string) $args['helcim_customer_code'];
			}

			$optional = array(
				'card_brand'        => isset($args['card_brand']) ? substr((string) $args['card_brand'], 0, 30) : null,
				'card_source'       => isset($args['card_source']) ? (string) $args['card_source'] : 'admin',
				'added_by_admin_id' => !empty($args['added_by_admin_id']) ? (int) $args['added_by_admin_id'] : null,
			);
			foreach ($optional as $col => $val) {
				if ($val !== null && $this->db->field_exists($col, 'payment_cards')) {
					$row[$col] = $val;
				}
			}

			$this->db->trans_start();

			// Only one card can be the renewal target.
			$this->db->where('user_id', $user_id)->update('payment_cards', array('is_default' => 0));

			$existing = $this->db->from('payment_cards')
				->where('user_id', $user_id)
				->where('helcim_card_token', $token)
				->limit(1)->get()->row();

			if ($existing) {
				$this->db->where('id', (int) $existing->id)->update('payment_cards', $row);
			} else {
				$row['user_id']    = $user_id;
				$row['created_at'] = $now;
				$this->db->insert('payment_cards', $row);
			}

			$this->db->trans_complete();

			return $this->db->trans_status();
		}

		/**
		 * Remove a stored card. Scoped by user so a hand-edited id cannot
		 * delete somebody else's card. Promotes the newest remaining card to
		 * default, or the renewal job would find no card to charge even though
		 * one is still on file.
		 */
		public function deleteCustomerCard($card_id, $user_id)
		{
			$card_id = (int) $card_id;
			$user_id = (int) $user_id;
			if (!$card_id || !$user_id) {
				return false;
			}

			$this->db->trans_start();
			$this->db->where('id', $card_id)->where('user_id', $user_id)->delete('payment_cards');

			$remaining = $this->db->from('payment_cards')
				->where('user_id', $user_id)
				->where('helcim_card_token IS NOT NULL', null, false)
				->order_by('id', 'DESC')->limit(1)->get()->row();

			if ($remaining) {
				$this->db->where('id', (int) $remaining->id)
					->update('payment_cards', array('is_default' => 1, 'updated_at' => date('Y-m-d H:i:s')));
			}
			$this->db->trans_complete();

			return $this->db->trans_status();
		}

		/**
		 * Record a payment staff took by hand — the August invoice, a
		 * catch-up charge, anything outside the self-serve checkout.
		 *
		 * The money has already moved at Helcim by the time this is called, so
		 * it never fails the caller for a column a migration has not added:
		 * unknown columns are dropped, exactly as recordCartPurchase() does.
		 * Returns the transaction id, or FALSE.
		 */
		public function recordManualCharge($args)
		{
			$now = date('Y-m-d H:i:s');

			$row = array(
				'user_id'    => (int) $args['user_id'],
				'amount'     => round((float) $args['amount'], 2),
				'currency'   => !empty($args['currency']) ? $args['currency'] : 'USD',
				'method'     => 'card',
				'status'     => 'success',
				'created_at' => $now,
				'updated_at' => $now,
				'txn_ref'    => 'ADM-' . (int) $args['user_id'] . '-' . substr(md5($now . uniqid('', true)), 0, 8),
				'plan_id'         => !empty($args['plan_id']) ? (int) $args['plan_id'] : null,
				'subscription_id' => !empty($args['subscription_id']) ? (int) $args['subscription_id'] : null,
				'listing_id'      => !empty($args['listing_id']) ? (int) $args['listing_id'] : null,
				'helcim_transaction_id' => !empty($args['helcim_transaction_id']) ? $args['helcim_transaction_id'] : null,
				'helcim_approval_code'  => !empty($args['helcim_approval_code']) ? $args['helcim_approval_code'] : null,
				'helcim_card_token'     => !empty($args['helcim_card_token']) ? $args['helcim_card_token'] : null,
				'created_by_admin_id'   => !empty($args['created_by_admin_id']) ? (int) $args['created_by_admin_id'] : null,
				'note'                  => !empty($args['note']) ? substr((string) $args['note'], 0, 255) : null,
			);

			// Every column, not a hand-picked list: the money has already moved
			// at Helcim by the time this runs, so a schema gap must cost us a
			// field, never the record of the payment.
			foreach (array_keys($row) as $col) {
				if (!$this->db->field_exists($col, 'transactions')) {
					log_message('debug', 'transactions has no column ' . $col . '; skipped on insert.');
					unset($row[$col]);
				}
			}

			if (!$this->db->insert('transactions', $row)) {
				return false;
			}

			return (int) $this->db->insert_id();
		}

		/**
		 * Turn recurring billing on or off and set the date the next charge
		 * goes out.
		 *
		 * Rescheduling clears the dunning state (`renewal_attempts`,
		 * `last_renewal_error`, `grace_until`). Those counters describe failed
		 * attempts against the OLD schedule — leaving them would mean a
		 * customer whose card had failed twice gets one attempt on the new
		 * date and is then expired.
		 */
		public function updateRecurringBilling($subscription_id, $auto_renew, $next_billing_date, $custom_amount = null)
		{
			$subscription_id = (int) $subscription_id;
			if (!$subscription_id) {
				return false;
			}

			$data = array(
				'auto_renew'         => $auto_renew ? 1 : 0,
				'next_billing_date'  => $auto_renew ? $next_billing_date : null,
				'renewal_attempts'   => 0,
				'last_renewal_error' => null,
				'grace_until'        => null,
				'updated_at'         => date('Y-m-d H:i:s'),
			);

			// The agreed price. NULL puts the subscription back on the plan's
			// own price; only written when the column exists, so the screen
			// still works before 0029 has been run.
			if ($this->db->field_exists('custom_amount', 'subscriptions')) {
				$data['custom_amount'] = ($custom_amount === null || $custom_amount === '')
					? null : round((float) $custom_amount, 2);
			}

			// A term that ends before the next charge would expire the
			// subscription between the two dates, and getRenewableSubscriptions()
			// only looks at active rows — so the renewal would never run.
			if ($auto_renew && $next_billing_date) {
				$sub = $this->db->select('end_date')->from('subscriptions')
					->where('id', $subscription_id)->get()->row();
				if ($sub && (empty($sub->end_date) || strtotime($sub->end_date) < strtotime($next_billing_date))) {
					$data['end_date'] = $next_billing_date;
				}
			}

			foreach (array_keys($data) as $col) {
				if (!$this->db->field_exists($col, 'subscriptions')) {
					unset($data[$col]);
				}
			}

			return $this->db->where('id', $subscription_id)->update('subscriptions', $data);
		}

		/**
		 * Start a subscription on a customer's behalf, for a payment taken
		 * outside the self-serve checkout.
		 *
		 * Deliberately NOT Usermodel::activateHelcimSubscription(): that method
		 * writes its own `transactions` row, and a staff-taken payment has
		 * already been recorded by recordManualCharge(). Calling it here would
		 * book the same money twice.
		 *
		 * Any currently-active subscription is expired first, matching the
		 * self-serve path — a customer holds one plan at a time.
		 *
		 * Returns the new subscription id, or FALSE.
		 */
		public function startSubscriptionForCustomer($args)
		{
			$user_id = (int) $args['user_id'];
			$plan_id = (int) $args['plan_id'];
			if (!$user_id || !$plan_id) {
				return false;
			}

			$now         = date('Y-m-d H:i:s');
			$start       = !empty($args['start_date']) ? $args['start_date'] : $now;
			$auto_renew  = !empty($args['auto_renew']) ? 1 : 0;
			$next_billing = !empty($args['next_billing_date']) ? $args['next_billing_date'] : null;
			$currency    = !empty($args['currency']) ? $args['currency'] : 'USD';
			$duration_id = !empty($args['duration_id']) ? (int) $args['duration_id'] : null;

			// `duration_days` is the RECURRING cadence, taken from the plan or
			// the chosen term — never from the length of this first term.
			// recordRenewalSuccess() extends by exactly this many days on each
			// renewal, so deriving it from the gap between today and the next
			// billing date would be a lasting mistake: a customer set up on the
			// 25th to resume on the 1st would be billed every SEVEN days from
			// then on, for as long as the subscription lived.
			$duration_days   = !empty($args['duration_days']) ? (int) $args['duration_days'] : 30;
			$duration_months = max(1, (int) round($duration_days / 30));

			// The first term, by contrast, may legitimately be short: it runs
			// to whatever date staff said the next charge goes out, so the two
			// can never disagree.
			$end = $next_billing
				? $next_billing
				: date('Y-m-d H:i:s', strtotime($start . ' +' . $duration_days . ' days'));

			$this->db->trans_start();

			// Replace the active subscription for THIS BUSINESS only. Expiring
			// every active row on the account would cancel the other twenty
			// businesses an import login happens to own.
			$has_listing_col = $this->db->field_exists('listing_id', 'subscriptions');
			$this->db->where('status', 'active');
			if (!empty($args['listing_id']) && $has_listing_col) {
				$this->db->where('listing_id', (int) $args['listing_id']);
			} else {
				$this->db->where('user_id', $user_id);
				if ($has_listing_col) {
					$this->db->where('listing_id IS NULL', null, false);
				}
			}
			$this->db->update('subscriptions', array('status' => 'expired', 'updated_at' => $now));

			$row = array(
				'user_id'           => $user_id,
				'plan_id'           => $plan_id,
				'status'            => 'active',
				'start_date'        => $start,
				'end_date'          => $end,
				'next_billing_date' => $auto_renew ? $next_billing : null,
				'created_at'        => $now,
				'updated_at'        => $now,
				'duration_id'       => $duration_id,
				'duration_days'     => $duration_days,
				'duration_months'   => $duration_months,
				'payment_status'    => 'paid',
				'total_amount'      => round((float) $args['amount'], 2),
				'currency'          => $currency,
				'billing_cycle'     => $duration_months >= 12 ? 'yearly' : 'monthly',
				'auto_renew'        => $auto_renew,
			);

			// Which business this bills for, and the agreed price if staff set
			// one. Both are 0029 columns, written only when present so the
			// screen degrades rather than erroring on an un-migrated database.
			if (!empty($args['listing_id']) && $this->db->field_exists('listing_id', 'subscriptions')) {
				$row['listing_id'] = (int) $args['listing_id'];
			}
			if ($this->db->field_exists('custom_amount', 'subscriptions')) {
				$row['custom_amount'] = (isset($args['custom_amount']) && $args['custom_amount'] !== null && $args['custom_amount'] !== '')
					? round((float) $args['custom_amount'], 2) : null;
			}

			/*
			 * Drop anything this schema does not have.
			 *
			 * `duration_id`, `duration_days` and `duration_months` are written
			 * by activateHelcimSubscription() but created by NO migration, so
			 * on a database where no self-serve checkout has ever completed
			 * they do not exist — and an INSERT naming a missing column is
			 * MySQL error 1054, which would have turned "Save recurring
			 * billing" into a 500. The subscription is worth more than the
			 * metadata: write what the table can hold.
			 */
			foreach (array_keys($row) as $col) {
				if (!$this->db->field_exists($col, 'subscriptions')) {
					log_message('debug', 'subscriptions has no column ' . $col . '; skipped on insert.');
					unset($row[$col]);
				}
			}

			$this->db->insert('subscriptions', $row);
			$subscription_id = (int) $this->db->insert_id();

			// A fresh usage window, so plan limits are counted from today
			// rather than inheriting whatever the previous plan had used.
			if ($subscription_id && $this->db->table_exists('user_usage_tracking')) {
				$this->db->insert('user_usage_tracking', array(
					'user_id'         => $user_id,
					'subscription_id' => $subscription_id,
					'reset_date'      => date('Y-m-d', strtotime($end)),
					'created_at'      => $now,
					'updated_at'      => $now,
				));
			}

			$this->db->trans_complete();

			return $this->db->trans_status() ? $subscription_id : false;
		}

		/** Attach a recorded payment to a subscription after the fact. */
		public function linkTransactionToSubscription($transaction_id, $subscription_id)
		{
			$transaction_id  = (int) $transaction_id;
			$subscription_id = (int) $subscription_id;
			if (!$transaction_id || !$subscription_id) {
				return false;
			}

			if ($this->db->field_exists('subscription_id', 'transactions')) {
				$this->db->where('id', $transaction_id)
					->update('transactions', array('subscription_id' => $subscription_id));
			}

			return $this->db->where('id', $subscription_id)
				->update('subscriptions', array('last_transaction_id' => $transaction_id));
		}
	}
