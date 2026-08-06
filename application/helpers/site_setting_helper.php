<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Site-settings helper.
 *
 * Reads from the `site_settings` table (created by migration 0007).
 * Falls back to the same-named UPPERCASE env var, then to the supplied
 * default. Per-request cached so a page hits the DB at most once.
 *
 *   $ga = site_setting('google_analytics_id');
 *   $fb = site_setting('social_facebook_url', '');
 *
 * If the migration hasn't been run yet the helper silently degrades —
 * it just returns the env-or-default value. Nothing 500s on a fresh
 * checkout.
 */

if (!function_exists('site_setting_all'))
{
    /**
     * Returns the full settings map (key => value). Cached per request.
     */
    function site_setting_all()
    {
        static $cache = null;
        if ($cache !== null) return $cache;
        $cache = array();

        $CI =& get_instance();
        if (!isset($CI->db) || !is_object($CI->db)) return $cache;

        try {
            if (!$CI->db->table_exists('site_settings')) return $cache;
            $rows = $CI->db->select('setting_key, setting_value')
                           ->from('site_settings')
                           ->get()->result();
            foreach ($rows as $r) {
                $cache[$r->setting_key] = $r->setting_value;
            }
        } catch (Exception $e) {
            // Don't break the page if the table is missing or query fails.
        }
        return $cache;
    }
}

if (!function_exists('site_setting'))
{
    /**
     * Reads one key. Order: DB -> env (UPPERCASE key) -> $default.
     */
    function site_setting($key, $default = '')
    {
        $key = trim((string) $key);
        if ($key === '') return $default;

        $all = site_setting_all();
        if (array_key_exists($key, $all) && $all[$key] !== null && $all[$key] !== '') {
            return $all[$key];
        }

        $env = getenv(strtoupper($key));
        if ($env !== false && $env !== '') return $env;

        return $default;
    }
}

if (!function_exists('site_setting_save'))
{
    /**
     * Upserts one key. Returns TRUE on success, FALSE if the table is
     * missing or the query fails. Used by Admin::admin_setting().
     */
    function site_setting_save($key, $value)
    {
        $CI =& get_instance();
        if (!isset($CI->db) || !is_object($CI->db)) return false;

        try {
            if (!$CI->db->table_exists('site_settings')) return false;
            $CI->db->replace('site_settings', array(
                'setting_key'   => $key,
                'setting_value' => $value,
                'updated_at'    => date('Y-m-d H:i:s'),
            ));
            // Invalidate per-request cache so the next read sees the change.
            $GLOBALS['__site_setting_cache_dirty'] = true;
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('form_recipient_email')) {
    /**
     * Where a public form's alert should be delivered.
     *
     * One address for every form on the site, set at /admin/setting. Each form
     * used to reach for `env('MAIL_FROM_ADDRESS')` on its own, so changing who
     * receives enquiries meant a deploy and a look through four controllers.
     *
     * Resolution order, first non-empty wins:
     *   1. $override        — a form-specific setting, e.g. service_quote_email
     *   2. forms_recipient_email  — the site-wide "all forms to" setting
     *   3. contact_email          — the address already published on the site
     *   4. MAIL_FROM_ADDRESS      — the .env From address
     *
     * @param  string $override Optional per-form setting value.
     * @return string '' when nothing is configured anywhere.
     */
    function form_recipient_email($override = '')
    {
        $candidates = array(
            trim((string) $override),
            trim((string) site_setting('forms_recipient_email', '')),
            trim((string) site_setting('contact_email', '')),
            trim((string) env('MAIL_FROM_ADDRESS', '')),
        );

        foreach ($candidates as $address) {
            if ($address !== '' && filter_var($address, FILTER_VALIDATE_EMAIL)) {
                return $address;
            }
        }

        return '';
    }
}
