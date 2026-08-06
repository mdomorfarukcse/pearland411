<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Admin-panel helpers.
 */

if (!defined('ADMIN_PER_PAGE_ALL')) {
    /**
     * What "All" actually means, as a row cap.
     *
     * Not NULL and not PHP_INT_MAX. CI3's pagination divides total_rows by
     * per_page to count pages, so NULL would be a division by zero, and every
     * admin controller does its own `($page - 1) * $config['per_page']`
     * arithmetic that a NULL would quietly turn into 0.
     *
     * A cap is also the honest thing here: a browser given 50,000 <tr>s stops
     * responding, and the operator asking for "All" wants to see the whole list
     * rather than to find out where their machine's limit is. Five thousand
     * rows is far past any admin list this site has and still renders.
     */
    define('ADMIN_PER_PAGE_ALL', 5000);
}

if (!function_exists('admin_per_page_options')) {
    /**
     * The choices offered in the "Show N per page" dropdown.
     *
     * Keys are what appears in the URL (`?show=`); values are what the
     * operator reads. Deliberately NOT `per_page`: that is CodeIgniter's own
     * reserved pagination query-string segment, and `reuse_query_string`
     * strips it from every generated link, so the choice would be lost the
     * moment you turned a page.
     *
     * @return array<string,string>
     */
    function admin_per_page_options()
    {
        return array(
            '10'  => '10 per page',
            '20'  => '20 per page',
            '30'  => '30 per page',
            '50'  => '50 per page',
            'all' => 'Show all',
        );
    }
}

if (!function_exists('admin_per_page_choice')) {
    /**
     * The operator's current choice, as it appears in the URL ('10'…'all'),
     * or '' when they have not chosen and the page's own default applies.
     *
     * Read from `?show=` and then remembered in the session, so moving
     * between admin lists keeps the size you picked instead of snapping back
     * to ten on every screen. Anything not in the option list is ignored
     * rather than trusted — this value reaches a LIMIT clause.
     *
     * @return string
     */
    function admin_per_page_choice()
    {
        $CI =& get_instance();
        $options = admin_per_page_options();

        $posted = $CI->input->get('show', true);
        if ($posted !== NULL && $posted !== '') {
            $posted = (string) $posted;
            if (isset($options[$posted])) {
                $CI->session->set_userdata('admin_per_page', $posted);
                return $posted;
            }
            // Not one of ours — fall through to whatever was already chosen.
        }

        $stored = (string) $CI->session->userdata('admin_per_page');
        return isset($options[$stored]) ? $stored : '';
    }
}

if (!function_exists('admin_per_page')) {
    /**
     * Rows per page for an admin list.
     *
     * The operator's dropdown choice wins; otherwise $default. Every list uses
     * 20 now — the category pages used to ask for 100 so their inline search
     * covered the whole list rather than one page of it, which the dropdown
     * does properly.
     *
     * @param  int $default Rows per page when nothing has been chosen. 20 by
     *                      default: ten was small enough that every list needed
     *                      paging through.
     * @return int Always a positive int, so the pagination arithmetic in every
     *             caller keeps working. "All" is ADMIN_PER_PAGE_ALL.
     */
    function admin_per_page($default = 20)
    {
        $choice = admin_per_page_choice();

        if ($choice === 'all') {
            return ADMIN_PER_PAGE_ALL;
        }
        if ($choice !== '' && ctype_digit($choice)) {
            return max(1, (int) $choice);
        }

        return max(1, (int) $default);
    }
}
