<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Command-line entry points. All actions refuse HTTP requests so
 * nobody can run them via a browser.
 *
 *   php index.php cli images_backfill
 *   php index.php cli images_backfill uploads
 *   php index.php cli images_backfill assets/images/listings
 */
class Cli extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->input->is_cli_request()) {
            show_404();
        }
        $this->load->helper('image');
    }

    /**
     * Walk an image directory and generate _thumb.jpg / _medium.jpg
     * variants for every image that does not already have them.
     *
     * Skips files that are themselves variants (`_thumb` / `_medium`
     * suffix) so re-runs are idempotent.
     */
    public function images_backfill($subdir = null)
    {
        $roots = $subdir
            ? array(rtrim($subdir, '/'))
            : array(
                'uploads',
                'assets/images/listings',
                'assets/images/news',
                'assets/images/events',
                'assets/images/blogs',
                'assets/images/products',
                'assets/images/house',
                'assets/images/rental',
                'assets/images/classifieds',
                'assets/images/user',
                'assets/images/coupons',
                'assets/images/ads',
            );

        $total = 0; $made = 0; $skipped = 0; $failed = 0;

        foreach ($roots as $root) {
            $abs_root = FCPATH . $root;
            if (!is_dir($abs_root)) {
                echo "skip (not a dir): $root\n";
                continue;
            }
            echo "==> $root\n";

            $iter = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($abs_root, FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iter as $file) {
                if (!$file->isFile()) continue;
                $total++;

                $ext = strtolower($file->getExtension());
                if (!in_array($ext, array('jpg', 'jpeg', 'png', 'webp'), true)) continue;

                // Don't re-process generated variants.
                $base = $file->getBasename('.' . $ext);
                if (substr($base, -6) === '_thumb' || substr($base, -7) === '_medium') {
                    continue;
                }

                $dir   = $file->getPath();
                $thumb = $dir . '/' . $base . '_thumb.jpg';
                $med   = $dir . '/' . $base . '_medium.jpg';
                if (is_file($thumb) && is_file($med)) {
                    $skipped++;
                    continue;
                }

                $mime = @mime_content_type($file->getPathname()) ?: ('image/' . ($ext === 'jpg' ? 'jpeg' : $ext));
                if (pearland411_generate_variants($file->getPathname(), $mime)) {
                    $made++;
                    if ($made % 50 === 0) echo "  ... $made variants generated\n";
                } else {
                    $failed++;
                }
            }
        }

        echo "\nscanned: $total  generated: $made  already_had: $skipped  failed: $failed\n";
    }

    /**
     * Auto-renew due subscriptions by charging the vaulted Helcim card token.
     * Extends the term on success; on failure applies a 3-retry / grace-window
     * dunning policy before expiring. Run daily via cron:
     *
     *   php index.php cli renew_subscriptions
     */
    public function renew_subscriptions()
    {
        $this->load->model('usermodel');
        $this->load->library('helcim_lib');
        $this->load->library('mailer');

        if (!$this->helcim_lib->is_configured()) {
            echo "Helcim not configured; aborting.\n";
            return;
        }

        $subs = $this->usermodel->getRenewableSubscriptions();
        echo 'due: ' . count($subs) . "\n";
        $ok = 0; $retry = 0; $expired = 0; $skipped = 0;

        foreach ($subs as $sub) {
            $plan_id = !empty($sub->pending_plan_id) ? (int) $sub->pending_plan_id : (int) $sub->plan_id;
            $plan    = $this->usermodel->getPlanByIdForPricing($plan_id);

            // Amount: prefer the chosen duration's price, else the plan price.
            $amount = 0.0;
            if (!empty($sub->duration_id)) {
                $d = $this->usermodel->getDurationById((int) $sub->duration_id);
                if ($d) $amount = (float) $d->price;
            }
            if ($amount <= 0 && $plan) $amount = (float) $plan->price;

            $token    = $this->usermodel->getVaultedCardToken((int) $sub->user_id);
            $currency = $sub->currency ?: $this->helcim_lib->currency();

            if (!$token || $amount <= 0) {
                $this->usermodel->recordRenewalFailure($sub, 'No vaulted card or amount on file');
                echo "  skip sub {$sub->id} (no vaulted card / amount)\n";
                $skipped++;
                continue;
            }

            $res = $this->helcim_lib->charge_vaulted_card($token, $amount, array(
                'currency'    => $currency,
                'description' => 'Pearland411 renewal — subscription ' . $sub->id,
            ));

            if (!empty($res['success'])) {
                $this->usermodel->recordRenewalSuccess($sub, $amount, $currency, $res['transactionId'], $res['approvalCode']);
                echo "  renewed sub {$sub->id}\n";
                $ok++;
            } else {
                $outcome = $this->usermodel->recordRenewalFailure($sub, isset($res['error']) ? $res['error'] : 'Charge failed');
                $this->notify_renewal_failure((int) $sub->user_id, $outcome);
                echo "  failed sub {$sub->id}: " . (isset($res['error']) ? $res['error'] : '') . " ({$outcome})\n";
                if ($outcome === 'expired') $expired++; else $retry++;
            }
        }

        echo "\nrenewed: $ok  retry: $retry  expired: $expired  skipped: $skipped\n";
    }

    /** Best-effort dunning / expiry email. Never aborts the cron. */
    private function notify_renewal_failure($user_id, $outcome)
    {
        try {
            $u = $this->db->select('email_id, name')->from('user')->where('user_id', (int) $user_id)->get()->row();
            if (!$u || empty($u->email_id)) return;

            if ($outcome === 'expired') {
                $subject = 'Your Pearland411 subscription has expired';
                $html = '<p>Hi ' . htmlspecialchars($u->name) . ',</p><p>We tried several times but could not renew your Pearland411 subscription, so it has now expired. '
                      . 'To continue, please <a href="' . base_url('pricing-plan') . '">choose a plan</a> and check out again.</p>';
            } else {
                $subject = 'We could not renew your Pearland411 subscription';
                $html = '<p>Hi ' . htmlspecialchars($u->name) . ',</p><p>Your latest renewal charge did not go through. We will retry shortly — your access continues during a short grace period. '
                      . 'Please make sure your card on file is valid from your <a href="' . base_url('user/my_account') . '">account</a>.</p>';
            }
            $this->mailer->send($u->email_id, $subject, $html);
        } catch (Exception $e) {
            log_message('error', 'Renewal notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Decode news and video rows that were stored entity-encoded.
     *
     * Admin::add_news()/edit_news()/add_video()/edit_video() used to save the
     * editor's markup through htmlentities(), so the columns hold
     * "&lt;p&gt;…" and "America&#039;s" rather than markup and an apostrophe.
     * Those write paths now store the text as typed, and content_text()
     * already decodes a legacy BODY on the way out — but titles never pass
     * through it, so an existing headline still renders a visible "&#039;".
     *
     * This walks the rows and decodes them in place, once.
     *
     *   php index.php cli decode_content_entities            # report only
     *   php index.php cli decode_content_entities apply      # write
     *   php index.php cli decode_content_entities apply force # write again
     *
     * Run once, and it records that it has run in `site_settings`. Decoding is
     * NOT safe to repeat blindly: a body containing a literal "&amp;" decodes
     * to "&" on the first pass and would keep unwrapping on later ones. The
     * flag is the guard; `force` overrides it deliberately.
     */
    public function decode_content_entities($mode = 'report', $force = '')
    {
        $apply = ($mode === 'apply');
        $this->load->helper('view');
        $this->load->helper('site_setting');

        $already = site_setting('content_entities_decoded_at', '');
        if ($apply && $already !== '' && $force !== 'force') {
            echo "Already run on {$already}. Decoding twice would unwrap literal entities\n";
            echo "(\"&amp;\" -> \"&\"). Re-run with:  php index.php cli decode_content_entities apply force\n";
            return;
        }

        $targets = array(
            array('table' => 'news',  'key' => 'news_id',  'cols' => array('news_title', 'news_description')),
            array('table' => 'video', 'key' => 'video_id', 'cols' => array('video_title', 'video_description')),
            array('table' => 'blogs', 'key' => 'blog_id',  'cols' => array('blog_name', 'blog_description')),
            // These sections encoded on input for longer — every text column
            // they wrote is affected, not just the body.
            array('table' => 'products', 'key' => 'product_id', 'cols' => array(
                'product_name', 'product_description', 'product_tags')),
            array('table' => 'classifieds', 'key' => 'classifieds_id', 'cols' => array(
                'classifieds_title', 'classifieds_description',
                'classifieds_seo_title', 'classifieds_seo_description', 'classifieds_seo_keywords')),
            array('table' => 'marketplace', 'key' => 'marketplace_id', 'cols' => array(
                'marketplace_title', 'marketplace_description',
                'marketplace_seo_title', 'marketplace_seo_description', 'marketplace_seo_keywords')),
        );

        echo $apply ? "Mode: APPLY (writing changes)\n" : "Mode: REPORT ONLY (pass 'apply' to write)\n";

        foreach ($targets as $t) {
            if (!$this->db->table_exists($t['table'])) {
                echo "-- {$t['table']}: table not present, skipped\n";
                continue;
            }

            $rows = $this->db->select(implode(',', array_merge(array($t['key']), $t['cols'])))
                ->from($t['table'])->get()->result();

            $changed = 0;
            foreach ($rows as $row) {
                $update = array();
                foreach ($t['cols'] as $col) {
                    $value = (string) ($row->$col ?? '');
                    if ($value === '' || !$this->looksEntityEncoded($value)) {
                        continue;
                    }
                    $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    if ($decoded !== $value) {
                        $update[$col] = $decoded;
                    }
                }

                if ($update) {
                    $changed++;
                    if ($apply) {
                        $this->db->where($t['key'], $row->{$t['key']})->update($t['table'], $update);
                    }
                }
            }

            echo sprintf("-- %-6s %d of %d row(s) %s\n", $t['table'] . ':', $changed, count($rows), $apply ? 'decoded' : 'would be decoded');
        }

        if ($apply) {
            site_setting_save('content_entities_decoded_at', date('Y-m-d H:i:s'));
            echo "\nDone. Recorded in site_settings so this cannot be run twice by accident.\n";
        } else {
            echo "\nNothing was written. Re-run with:  php index.php cli decode_content_entities apply\n";
        }
    }

    /**
     * True when a stored value is HTML that was run through htmlentities().
     *
     * A value already containing real "<" is left alone — that is content the
     * fixed write paths stored as typed, and decoding it again would be wrong.
     * Otherwise any HTML entity is enough: an encoded closing tag ("&lt;/p&gt;")
     * for a body, but also a bare "&#039;" or "&eacute;" for a headline, which
     * never passes through content_text() and so shows up verbatim on the page.
     */
    private function looksEntityEncoded($value)
    {
        if (strpos($value, '<') !== false) {
            return false;
        }

        // Named (&amp; &eacute;), decimal (&#039;) or hex (&#x27;) entities.
        return (bool) preg_match('/&(?:#\d{1,7}|#x[0-9a-f]{1,6}|[a-z][a-z0-9]{1,31});/i', $value);
    }
}
