<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * XML sitemap generator (/sitemap.xml).
 *
 * Streams a single-file sitemap of all public URLs: static pages,
 * service catalog, listings, listing categories, news, blogs,
 * events, classifieds, products. Cached for 1 hour via the simple
 * file cache.
 *
 * If the sitemap grows beyond 50k URLs (the protocol limit) split
 * into per-resource child sitemaps + a sitemapindex root — easy
 * follow-up.
 */
class Sitemap extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->driver('cache', array('adapter' => 'file', 'backup' => 'dummy'));
    }

    public function index()
    {
        $this->output->set_content_type('application/xml; charset=utf-8');

        $cached = $this->cache->get('sitemap_xml_v1');
        if ($cached) {
            $this->output->set_output($cached);
            return;
        }

        $urls = array();
        $today = date('Y-m-d');

        // Static / hub pages
        foreach (array(
            ''                  => '1.0',
            'about-us'          => '0.5',
            'faq'               => '0.3',
            'contact-us'        => '0.5',
            'privacy-policy'    => '0.2',
            'terms-of-use'      => '0.2',
            'services'          => '0.9',
            'pricing_plan'      => '0.7',
            'event'             => '0.8',
            'news'              => '0.8',
            'all_blog'          => '0.7',
            'classifieds'       => '0.7',
            'all_products'      => '0.6',
            'videos'            => '0.4',
            'coupons'           => '0.6',
        ) as $path => $priority) {
            $urls[] = array(base_url($path), $today, $priority);
        }

        $urls = array_merge(
            $urls,
            $this->_collect('services', 'slug', 'updated_at', 'services/', '0.8'),
            $this->_collect('listings', 'listing_slug', 'created_at', 'listing-details/', '0.7',
                array('listing_status' => 1, 'is_deleted' => 0)),
            $this->_collect('listing_categories', 'category_slug', null, 'all_listing/', '0.6'),
            $this->_collect('news', 'news_slug', 'created_at', 'news-details/', '0.6'),
            $this->_collect('blogs', 'blog_slug', 'created_at', 'blogs-details/', '0.5'),
            $this->_collect('events', 'event_slug', 'created_at', 'event-details/', '0.5'),
            $this->_collect('classifieds', 'classifieds_slug', 'created_at', 'classifieds-details/', '0.5'),
            $this->_collect('products', 'product_slug', 'created_at', 'products-details/', '0.4')
        );

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as list($loc, $lastmod, $priority)) {
            $xml .= "  <url>\n"
                . '    <loc>' . htmlspecialchars($loc, ENT_QUOTES | ENT_XML1, 'UTF-8') . "</loc>\n"
                . '    <lastmod>' . htmlspecialchars($lastmod, ENT_QUOTES | ENT_XML1, 'UTF-8') . "</lastmod>\n"
                . '    <priority>' . $priority . "</priority>\n"
                . "  </url>\n";
        }
        $xml .= '</urlset>' . "\n";

        $this->cache->save('sitemap_xml_v1', $xml, 3600);
        $this->output->set_output($xml);
    }

    /**
     * Fetches slug + lastmod from a table and returns rows for the sitemap.
     */
    private function _collect($table, $slug_col, $time_col, $url_prefix, $priority, $where = array())
    {
        if (!$this->db->table_exists($table)) return array();

        $cols = "$slug_col" . ($time_col ? ", $time_col" : '');
        $this->db->select($cols)->from($table);
        foreach ($where as $k => $v) {
            $this->db->where($k, $v);
        }
        $rows = $this->db->get()->result();

        $out = array();
        $fallback = date('Y-m-d');
        foreach ($rows as $r) {
            $slug = isset($r->$slug_col) ? $r->$slug_col : null;
            if (!$slug) continue;
            $lastmod = ($time_col && !empty($r->$time_col)) ? date('Y-m-d', strtotime($r->$time_col)) : $fallback;
            $out[] = array(base_url($url_prefix . rawurlencode($slug)), $lastmod, $priority);
        }
        return $out;
    }
}
