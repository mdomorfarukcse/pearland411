<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Simple DB-backed rate limiter.
 *
 *   $this->load->library('rate_limit');
 *
 *   // Reject if this IP has hit /login more than 5 times in 5 minutes
 *   if (!$this->rate_limit->check_ip('login', 5, 300)) {
 *       show_error('Too many requests. Try again in a few minutes.', 429);
 *   }
 *
 *   // After a successful flow you can reset the bucket:
 *   $this->rate_limit->reset_ip('login');
 *
 * Backed by the `rate_limits` table (see
 * database/migrations/0004_rate_limits.sql). Buckets are
 * (action, identifier) keyed; identifier defaults to the request IP.
 *
 * Implementation notes:
 *   - The library guards against the inevitable race between SELECT
 *     and UPDATE by using INSERT ... ON DUPLICATE KEY UPDATE on a
 *     UNIQUE bucket_key column. The "current attempts" is read from
 *     the affected row after the upsert.
 *   - Expired buckets (window_end < NOW) are reset, not deleted, so
 *     the next attempt starts fresh. A periodic GC sweep is done
 *     opportunistically (~1% of calls).
 *   - All checks are best-effort. A misbehaving caller can flood
 *     before we've persisted the first row; that's acceptable for
 *     anti-spam / anti-brute-force purposes.
 */
class Rate_limit
{
    /** @var CI_Controller */
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }

    /**
     * Increment the counter for ($action, ip) and return TRUE while
     * the count is at or below $max attempts in the rolling $window
     * seconds. Returns FALSE when the bucket is full.
     *
     * @param string   $action       Logical action name (e.g. 'login')
     * @param int      $max          Max attempts per window
     * @param int      $window_secs  Window length in seconds
     * @param string|null $identifier Override the keying identifier
     *                                (default: request IP)
     */
    public function check_ip($action, $max, $window_secs, $identifier = null)
    {
        if ($identifier === null) {
            $identifier = $this->CI->input->ip_address() ?: 'unknown';
        }
        return $this->check_bucket($action . ':' . $identifier, $max, $window_secs, $identifier);
    }

    /**
     * Reset a bucket on a successful action so honest users aren't
     * accumulating attempts indefinitely.
     */
    public function reset_ip($action, $identifier = null)
    {
        if ($identifier === null) {
            $identifier = $this->CI->input->ip_address() ?: 'unknown';
        }
        $this->CI->db
            ->where('bucket_key', $action . ':' . $identifier)
            ->delete('rate_limits');
    }

    /**
     * Core check. Returns TRUE if allowed, FALSE if over the limit.
     */
    public function check_bucket($key, $max, $window_secs, $identifier = null)
    {
        $key = substr($key, 0, 180);
        $now = date('Y-m-d H:i:s');
        $win_end = date('Y-m-d H:i:s', time() + (int) $window_secs);

        $existing = $this->CI->db
            ->where('bucket_key', $key)
            ->limit(1)
            ->get('rate_limits')
            ->row();

        if (!$existing) {
            // First hit — insert with attempts = 1.
            $this->CI->db->insert('rate_limits', array(
                'bucket_key'   => $key,
                'attempts'     => 1,
                'window_start' => $now,
                'window_end'   => $win_end,
                'last_ip'      => $identifier,
                'created_at'   => $now,
                'updated_at'   => $now,
            ));
            $this->maybe_gc();
            return true;
        }

        // Bucket expired -> reset to attempts = 1.
        if (strtotime($existing->window_end) <= time()) {
            $this->CI->db
                ->where('id', $existing->id)
                ->update('rate_limits', array(
                    'attempts'     => 1,
                    'window_start' => $now,
                    'window_end'   => $win_end,
                    'last_ip'      => $identifier,
                    'updated_at'   => $now,
                ));
            return true;
        }

        $next = ((int) $existing->attempts) + 1;
        if ($next > (int) $max) {
            // Touch updated_at so admins can see the most recent hit.
            $this->CI->db
                ->where('id', $existing->id)
                ->update('rate_limits', array(
                    'last_ip'    => $identifier,
                    'updated_at' => $now,
                ));
            return false;
        }

        $this->CI->db
            ->where('id', $existing->id)
            ->update('rate_limits', array(
                'attempts'   => $next,
                'last_ip'    => $identifier,
                'updated_at' => $now,
            ));
        return true;
    }

    /**
     * Random opportunistic cleanup of expired rows (~1% of calls).
     */
    private function maybe_gc()
    {
        if (random_int(1, 100) !== 1) return;
        $this->CI->db
            ->where('window_end <', date('Y-m-d H:i:s', time() - 86400))
            ->delete('rate_limits');
    }

    /**
     * Render a uniform 429 response and halt. Useful from controllers.
     */
    public function reject($retry_after_secs = 60)
    {
        $this->CI->output
            ->set_status_header(429)
            ->set_header('Retry-After: ' . (int) $retry_after_secs)
            ->set_content_type('text/html')
            ->set_output(
                '<!doctype html><html><head><title>Too many requests</title></head>'
                . '<body style="font-family:sans-serif;max-width:480px;margin:80px auto;text-align:center;">'
                . '<h1>Too many requests</h1>'
                . '<p>You&rsquo;ve made too many attempts in a short time. '
                . 'Please wait ' . (int) $retry_after_secs . ' seconds and try again.</p>'
                . '<p><a href="' . base_url() . '">Back to home</a></p>'
                . '</body></html>'
            );
    }
}
