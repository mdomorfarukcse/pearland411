<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Daily activity counters for the analytics pages (checklist §10: listing
 * views, website / phone / map clicks, coupon activity, promotional
 * placement activity).
 *
 *   $this->load->library('activity');
 *   $this->activity->record('listing', $listing_id, 'website');
 *
 * Backed by `activity_daily` (migration 0037): one row per subject, event and
 * day, incremented with INSERT … ON DUPLICATE KEY UPDATE. It is a counter,
 * not a log — nothing about the visitor is stored — so it stays small (a few
 * rows per business per day at most) and needs no clean-up job.
 *
 * Leads are not recorded here: they are the `enquiries` rows themselves, and
 * the report counts those directly.
 *
 * Everything is best-effort. A missing table (0037 not run), a crawler or an
 * unknown event is a silent no-op; nothing here may ever break a page.
 */
class Activity
{
    /** What may be counted, per subject type. */
    const EVENTS = array(
        'listing' => array('view', 'website', 'phone', 'email', 'map', 'whatsapp'),
        'coupon'  => array('view', 'click'),
        'ad'      => array('impression', 'click'),
    );

    /** @var CI_Controller */
    protected $CI;

    /** @var bool|null */
    protected $ready = NULL;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }

    /** Is the event one we count? */
    public function is_valid($type, $event)
    {
        return isset(self::EVENTS[$type]) && in_array($event, self::EVENTS[$type], TRUE);
    }

    /**
     * Count one event. A listing or coupon VIEW is counted once per visitor
     * session per day, so reloading the page does not inflate the figure the
     * owner pays to see; clicks and ad impressions are counted every time.
     *
     * @return bool whether a hit was recorded
     */
    public function record($type, $id, $event)
    {
        $id = (int) $id;
        if ($id <= 0 || !$this->is_valid($type, $event) || !$this->ready() || $this->is_bot()) {
            return FALSE;
        }

        $today = date('Y-m-d');
        if ($event === 'view') {
            $seen = $this->CI->session->userdata('activity_seen');
            $seen = (is_array($seen) && ($seen['d'] ?? '') === $today) ? $seen : array('d' => $today, 'k' => array());
            $key  = $type . ':' . $id;
            if (isset($seen['k'][$key])) {
                return FALSE;
            }
            // Bounded, so a long browsing session cannot grow the session file.
            if (count($seen['k']) < 300) {
                $seen['k'][$key] = 1;
                $this->CI->session->set_userdata('activity_seen', $seen);
            }
        }

        $table = $this->CI->db->protect_identifiers('activity_daily', TRUE);
        try {
            return (bool) $this->CI->db->query(
                'INSERT INTO ' . $table . ' (`subject_type`, `subject_id`, `event_type`, `event_date`, `hits`)
                 VALUES (?, ?, ?, ?, 1)
                 ON DUPLICATE KEY UPDATE `hits` = `hits` + 1',
                array($type, $id, $event, $today)
            );
        } catch (Throwable $e) {
            log_message('error', 'Activity::record failed: ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Totals per subject and event since a date.
     *
     * @param  string $type  listing | coupon | ad
     * @param  int[]  $ids   subjects to report on; [] = every subject
     * @param  string $since Y-m-d, inclusive
     * @return array<int,array<string,int>> subject_id => [event => hits]
     */
    public function totals($type, array $ids, $since)
    {
        $out = array();
        if (!$this->ready()) {
            return $out;
        }

        $this->CI->db->select('subject_id, event_type, SUM(hits) AS hits', FALSE)
            ->from('activity_daily')
            ->where('subject_type', $type)
            ->where('event_date >=', $since);
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if ($ids) {
            $this->CI->db->where_in('subject_id', $ids);
        }
        $rows = $this->CI->db->group_by(array('subject_id', 'event_type'))->get()->result();

        foreach ($rows as $row) {
            $out[(int) $row->subject_id][$row->event_type] = (int) $row->hits;
        }
        return $out;
    }

    /**
     * The busiest subjects for one event since a date, busiest first.
     *
     * @return array<int,int> subject_id => hits
     */
    public function top($type, $event, $since, $limit = 10)
    {
        $out = array();
        if (!$this->ready()) {
            return $out;
        }

        $rows = $this->CI->db->select('subject_id, SUM(hits) AS hits', FALSE)
            ->from('activity_daily')
            ->where('subject_type', $type)
            ->where('event_type', $event)
            ->where('event_date >=', $since)
            ->group_by('subject_id')
            ->order_by('hits', 'DESC')
            ->limit(max(1, (int) $limit))
            ->get()->result();

        foreach ($rows as $row) {
            $out[(int) $row->subject_id] = (int) $row->hits;
        }
        return $out;
    }

    /** activity_daily exists (migration 0037). */
    public function ready()
    {
        if ($this->ready === NULL) {
            $this->ready = $this->CI->db->table_exists('activity_daily');
        }
        return $this->ready;
    }

    /**
     * Crawlers, link previews and uptime monitors. They would otherwise be
     * most of the "views" a small business sees.
     */
    protected function is_bot()
    {
        $ua = (string) $this->CI->input->user_agent();
        if ($ua === '') {
            return TRUE;
        }
        return (bool) preg_match('/bot|crawl|spider|slurp|facebookexternalhit|embedly|preview|monitor|pingdom|uptime|curl|wget|python-|go-http|java\/|headless|lighthouse/i', $ua);
    }
}
