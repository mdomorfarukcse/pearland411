<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Schema-tolerant writes for the content tables that predate the migrations.
 *
 * `news`, `video` and most of the other theme tables were created by hand on
 * the live server and their column list is not in the repo. Several of their
 * columns are NOT NULL with no default — `featured_news` and `featured_video`
 * among them — so a save that left the "Featured" box unticked posted NULL
 * into a NOT NULL column. On PHP 8.1+ mysqli throws instead of returning
 * FALSE (CodeIgniter 3.1.11 predates that), so the error never reached the
 * "could not save" branch: the admin got Chrome's "This page isn't working"
 * (HTTP 500) and nothing was saved. That is the reported "adding the latest
 * news / videos resulted in an error".
 *
 * db_safe_insert() / db_safe_update() fit the row to the table actually
 * there before writing it:
 *   - a key the table has no column for is dropped (logged), not fatal;
 *   - NULL or '' for a NOT NULL column becomes its default, else 0 / '' /
 *     the current date-time, by column type;
 *   - '' for a numeric column that allows NULL becomes NULL;
 *   - a failure is caught and returned as FALSE, with the database's own
 *     message in db_safe_last_error(), so the caller's error path runs.
 *
 * Usermodel::addEnquiry() was the first copy of this rule; it now calls
 * these too.
 */

if (!function_exists('db_table_columns')) {
	/**
	 * name => [nullable, default, numeric, date, auto] for $table, or array()
	 * when the column list cannot be read (the write then goes through as
	 * given). Cached per request.
	 */
	function db_table_columns($table)
	{
		static $cache = array();
		if (isset($cache[$table])) return $cache[$table];

		$CI =& get_instance();
		$cols = array();
		try {
			$q = $CI->db->query('SHOW COLUMNS FROM ' . $CI->db->protect_identifiers($table, TRUE));
		} catch (Exception $e) {
			$q = FALSE;   // PHP 8.1+ throws where older PHP returned FALSE
		}
		if ($q) {
			foreach ($q->result_array() as $c) {
				$type = strtolower((string) $c['Type']);
				$cols[$c['Field']] = array(
					'nullable' => strtoupper((string) $c['Null']) === 'YES',
					'default'  => $c['Default'],
					'numeric'  => (bool) preg_match('/int|decimal|float|double|bit/', $type),
					'date'     => (bool) preg_match('/^(datetime|timestamp|date|time|year)/', $type),
					'datetype' => preg_replace('/\(.*$/', '', $type),
					'auto'     => stripos((string) $c['Extra'], 'auto_increment') !== FALSE,
				);
			}
		}
		return $cache[$table] = $cols;
	}
}

if (!function_exists('db_safe_last_error')) {
	/** The last failure from db_safe_insert()/db_safe_update(); pass a string to set it. */
	function db_safe_last_error($set = NULL)
	{
		static $last = '';
		if ($set !== NULL) $last = (string) $set;
		return $last;
	}
}

if (!function_exists('db_fit_row')) {
	/**
	 * $data reshaped to what $table will accept. $insert decides what an
	 * empty value for a NOT NULL column with a default becomes: on insert the
	 * key is dropped so the default applies; on update the default is written.
	 */
	function db_fit_row($table, array $data, $insert = TRUE)
	{
		$cols = db_table_columns($table);
		if (!$cols) return $data;

		$out = array();
		foreach ($data as $key => $value) {
			if (!isset($cols[$key])) {
				log_message('debug', "db_fit_row: `$table` has no column `$key`; value dropped.");
				continue;
			}
			$c = $cols[$key];

			$empty = $value === NULL || ($value === '' && ($c['numeric'] || $c['date']));
			if (!$empty) {
				$out[$key] = $value;
				continue;
			}
			if ($c['nullable']) {
				$out[$key] = NULL;
				continue;
			}
			if ($c['default'] !== NULL) {
				if ($insert) continue;   // leave it out; the default applies
				// CURRENT_TIMESTAMP and friends are expressions, not values.
				$out[$key] = preg_match('/^(current_timestamp|now)/i', (string) $c['default'])
					? ($c['datetype'] === 'date' ? date('Y-m-d') : date('Y-m-d H:i:s'))
					: $c['default'];
				continue;
			}
			if ($c['numeric']) {
				$out[$key] = 0;
			} elseif ($c['date']) {
				$out[$key] = $c['datetype'] === 'date' ? date('Y-m-d')
					: ($c['datetype'] === 'time' ? '00:00:00'
					: ($c['datetype'] === 'year' ? date('Y') : date('Y-m-d H:i:s')));
			} else {
				$out[$key] = '';
			}
		}
		return $out;
	}
}

if (!function_exists('db_safe_insert')) {
	/**
	 * Insert a schema-fitted row. Returns the new id, TRUE when the table has
	 * no auto-increment key, or FALSE (reason in db_safe_last_error()).
	 */
	function db_safe_insert($table, array $data)
	{
		$CI =& get_instance();
		db_safe_last_error('');
		$row = db_fit_row($table, $data, TRUE);
		if (!$row) {
			db_safe_last_error('nothing to save.');
			return FALSE;
		}

		try {
			$ok = $CI->db->insert($table, $row);
		} catch (Exception $e) {
			$ok = FALSE;
			db_safe_last_error($e->getMessage());
		}
		if (!$ok) {
			if (db_safe_last_error() === '') {
				$err = $CI->db->error();
				db_safe_last_error(!empty($err['message']) ? $err['message'] : 'the database refused the row.');
			}
			log_message('error', "db_safe_insert($table) failed: " . db_safe_last_error());
			return FALSE;
		}

		$id = (int) $CI->db->insert_id();
		return $id > 0 ? $id : TRUE;
	}
}

if (!function_exists('db_safe_update')) {
	/** Update rows matching $where with a schema-fitted $data. Returns bool. */
	function db_safe_update($table, array $data, array $where)
	{
		$CI =& get_instance();
		db_safe_last_error('');
		$row = db_fit_row($table, $data, FALSE);
		if (!$row || !$where) {
			db_safe_last_error('nothing to save.');
			return FALSE;
		}

		try {
			$ok = $CI->db->where($where)->update($table, $row);
		} catch (Exception $e) {
			$ok = FALSE;
			db_safe_last_error($e->getMessage());
		}
		if (!$ok) {
			if (db_safe_last_error() === '') {
				$err = $CI->db->error();
				db_safe_last_error(!empty($err['message']) ? $err['message'] : 'the database refused the change.');
			}
			log_message('error', "db_safe_update($table) failed: " . db_safe_last_error());
			return FALSE;
		}
		return TRUE;
	}
}
