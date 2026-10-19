<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Command-line entry points. All actions refuse HTTP requests so
 * nobody can run them via a browser.
 *
 *   php index.php cli images_backfill
 *   php index.php cli images_backfill uploads
 *   php index.php cli images_backfill assets/images/listings
 *   php index.php cli mail_test you@example.com
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
     * Prove — or disprove — that the site can send email.
     *
     *   php index.php cli mail_test you@example.com
     *
     * Prints the effective configuration, every known-bad setting, and the
     * result of a real send with the mail server's own error text. The same
     * ground /admin/email-diagnostics covers, for when the problem is that
     * nobody can sign in to reach it — which was the case in September 2026,
     * when signup verification mail stopped being delivered site-wide.
     */
    public function mail_test($address = null)
    {
        $this->load->library('mailer');

        echo "Configuration\n-------------\n";
        foreach ($this->mailer->config_summary() as $key => $value) {
            printf("  %-14s %s\n", $key, ($value === '' || $value === null) ? '(not set)' : $value);
        }

        $issues = $this->mailer->config_status();
        echo "\nChecks\n------\n";
        if (empty($issues)) {
            echo "  No configuration problems found.\n";
        }
        foreach ($issues as $issue) {
            printf("  [%s] %s\n         %s\n", strtoupper($issue['level']), $issue['title'], $issue['detail']);
        }

        if ($address === null) {
            echo "\nPass an address to send a test message:\n";
            echo "  php index.php cli mail_test you@example.com\n";
            return;
        }

        echo "\nSending to {$address} ...\n";
        $result = $this->mailer->test_send($address);

        if (!empty($result['success'])) {
            echo "  OK — the mail server accepted the message.\n";
            echo "  If it does not arrive, the message is being discarded by the RECIPIENT,\n";
            echo "  not refused by the sender. Check SPF/DKIM/DMARC on the From domain.\n";
            return;
        }

        echo "  FAILED — " . $result['error'] . "\n";
        // Non-zero so a cron or deploy script can tell.
        exit(1);
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
            // Heartbeat first: "the cron ran but Helcim is not set up" and "the
            // cron never ran at all" are different problems with the same
            // symptom — nothing charged — and staff need to tell them apart.
            $this->recordCronRun('Helcim is not configured, so nothing was charged.');
            echo "Helcim not configured; aborting.\n";
            return;
        }

        // Warn first, then charge: a customer whose card is about to be taken
        // hears about it a few days ahead (tracker: "the owner will be
        // notified that the charges will be deducted").
        $reminded = $this->send_renewal_reminders();
        echo 'reminders sent: ' . $reminded . "\n";

        $subs = $this->usermodel->getRenewableSubscriptions();
        echo 'due: ' . count($subs) . "\n";
        $ok = 0; $retry = 0; $expired = 0; $skipped = 0;

        foreach ($subs as $sub) {
            // Amount: prefer the chosen duration's price, else the plan price.
            // Shared with /admin/billing so the figure staff are shown is the
            // figure this job will actually take.
            $amount = $this->usermodel->renewalAmountFor($sub);

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
                // Keyed on the subscription and the date it fell due, so a
                // second run the same night — a duplicate cron, or a hand-run
                // alongside the scheduled one — cannot take the money twice.
                // A retry tomorrow moves next_billing_date, so it keys anew
                // and does charge.
                'idempotency_key' => 'renew:' . $sub->id . ':' . $sub->next_billing_date,
            ));

            if (!empty($res['success'])) {
                $this->usermodel->recordRenewalSuccess($sub, $amount, $currency, $res['transactionId'], $res['approvalCode']);
                $this->notify_renewal_receipt($sub, $amount, $currency, $res['transactionId']);
                $this->notify_admins_renewal($sub, 'renewed', array(
                    'amount'   => $amount,
                    'currency' => $currency,
                    'txn'      => $res['transactionId'],
                ));
                echo "  renewed sub {$sub->id}\n";
                $ok++;
            } else {
                $outcome = $this->usermodel->recordRenewalFailure($sub, isset($res['error']) ? $res['error'] : 'Charge failed');
                $this->notify_renewal_failure((int) $sub->user_id, $outcome);
                $this->notify_admins_renewal($sub, $outcome, array(
                    'amount'   => $amount,
                    'currency' => $currency,
                    'error'    => isset($res['error']) ? $res['error'] : 'Charge failed',
                ));
                echo "  failed sub {$sub->id}: " . (isset($res['error']) ? $res['error'] : '') . " ({$outcome})\n";
                if ($outcome === 'expired') $expired++; else $retry++;
            }
        }

        $this->recordCronRun(sprintf(
            '%d due — %d renewed, %d retrying, %d expired, %d skipped.',
            count($subs), $ok, $retry, $expired, $skipped
        ));

        echo "\nrenewed: $ok  retry: $retry  expired: $expired  skipped: $skipped\n";
    }

    /**
     * Stamp when this job last ran and what it did, so /admin/billing can say
     * so on screen.
     *
     * Recurring billing only works if a cron actually calls this every night,
     * and there was no way to find that out short of SSH — a cron that was
     * never installed, or installed with a placeholder path still in it, looks
     * from the admin exactly like a cron that ran and found nothing due. The
     * first anyone would know is a customer whose card was never charged.
     *
     * Best effort: a failed write must never take the job down with it.
     */
    private function recordCronRun($summary)
    {
        try {
            if (function_exists('site_setting_save')) {
                site_setting_save('billing_cron_last_run', date('Y-m-d H:i:s'));
                site_setting_save('billing_cron_last_summary', (string) $summary);
            }
        } catch (Exception $e) {
            log_message('error', 'Could not record the renewal cron heartbeat: ' . $e->getMessage());
        }
    }

    /**
     * Tell the team what the nightly run did to one subscription.
     *
     * Recurring billing is the one thing on this site that moves money with
     * nobody watching, so every outcome — charged, retrying, expired — is
     * reported to the same people who are told when staff take a payment by
     * hand (Mailer::billing_recipients()). A silent failure at 2am that nobody
     * sees until the customer complains is the failure mode worth designing
     * against.
     *
     * Never aborts the cron: the charge has already happened either way.
     */
    private function notify_admins_renewal($sub, $outcome, array $facts)
    {
        try {
            $this->load->library('mailer');

            $business = '';
            if (!empty($sub->listing_id)) {
                $row = $this->db->select('listing_name')->from('listings')
                    ->where('listing_id', (int) $sub->listing_id)->get()->row();
                if ($row) $business = $row->listing_name;
            }
            $user = $this->db->select('name, email_id')->from('user')
                ->where('user_id', (int) $sub->user_id)->get()->row();

            $who      = $business !== '' ? $business : ($user ? $user->name : 'a customer');
            $currency = !empty($facts['currency']) ? $facts['currency'] : 'USD';
            $money    = $currency . ' ' . number_format((float) $facts['amount'], 2);

            if ($outcome === 'renewed') {
                $heading = 'Automatic renewal charged';
                $subject = 'Pearland411 renewal — ' . $money . ' — ' . $who;
                $intro   = 'The nightly renewal charged the card on file.';
            } elseif ($outcome === 'expired') {
                $heading = 'Subscription expired after failed renewals';
                $subject = 'Pearland411 renewal FAILED (expired) — ' . $who;
                $intro   = 'The card was declined too many times, so the subscription has expired.';
            } else {
                $heading = 'Automatic renewal failed — will retry';
                $subject = 'Pearland411 renewal failed — ' . $who;
                $intro   = 'The card was declined. It will be retried; access continues meanwhile.';
            }

            $rows = array(array('Business', $who));
            if ($user) {
                $rows[] = array('Account', $user->name . ' (' . $user->email_id . ')');
            }
            $rows[] = array('Amount', $money);
            if (!empty($facts['txn'])) {
                $rows[] = array('Helcim transaction', $facts['txn']);
            }
            if (!empty($facts['error'])) {
                $rows[] = array('Gateway said', $facts['error']);
            }

            $html = '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;">';
            foreach ($rows as $row) {
                $html .= '<tr><td style="padding:7px 12px 7px 0; color:#5A6472; font-size:14px; white-space:nowrap; vertical-align:top;">'
                    . htmlspecialchars((string) $row[0], ENT_QUOTES, 'UTF-8') . '</td>'
                    . '<td style="padding:7px 0; color:#1F2430; font-size:14px; font-weight:600;">'
                    . htmlspecialchars((string) $row[1], ENT_QUOTES, 'UTF-8') . '</td></tr>';
            }
            $html .= '</table>';

            if (!empty($sub->listing_id)) {
                $html .= '<p style="margin:18px 0 0;"><a href="'
                    . base_url('admin/billing-customer/' . (int) $sub->listing_id)
                    . '" style="color:#79B82A; font-weight:600;">Open this business in admin billing &rarr;</a></p>';
            }

            $this->mailer->notify_billing($subject, $heading, $html, array(
                'intro'     => $intro,
                'preheader' => $who . ' — ' . strtolower($heading),
            ));
        } catch (Exception $e) {
            log_message('error', 'Renewal admin notification failed: ' . $e->getMessage());
        }
    }

    /** Best-effort dunning / expiry email. Never aborts the cron. */
    /**
     * Email every customer whose auto-renewal falls due within
     * `renewal_reminder_days` (default 3, /admin/setting): what will be
     * charged, to which card, when, and how to stop it. Once per charge —
     * Usermodel::markRenewalReminded() records which billing date it covered.
     *
     * Before this, the only email a customer ever got about a renewal was the
     * one saying it had FAILED; a successful charge arrived unannounced.
     *
     * @return int reminders sent
     */
    private function send_renewal_reminders()
    {
        $days = max(1, (int) site_setting('renewal_reminder_days', 3));
        $sent = 0;

        foreach ($this->usermodel->getSubscriptionsDueForReminder($days) as $sub) {
            try {
                $u = $this->db->select('email_id, name')->from('user')->where('user_id', (int) $sub->user_id)->get()->row();
                $amount = $this->usermodel->renewalAmountFor($sub);
                if (!$u || empty($u->email_id) || $amount <= 0) {
                    // Nothing to tell, and nothing will be charged — mark it so
                    // the same row is not re-read every night until it falls due.
                    $this->usermodel->markRenewalReminded($sub);
                    continue;
                }

                $plan     = $this->usermodel->getPlanById(!empty($sub->pending_plan_id) ? (int) $sub->pending_plan_id : (int) $sub->plan_id);
                $currency = !empty($sub->currency) ? $sub->currency : 'USD';
                $card     = $this->usermodel->vaultedCardLabel((int) $sub->user_id);
                $when     = date('F j, Y', strtotime($sub->next_billing_date));
                $esc      = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };

                $body = '<p>Hi ' . $esc($u->name) . ',</p>'
                    . '<p>Your Pearland411 <strong>' . $esc($plan ? $plan->name : 'subscription') . '</strong> renews automatically on <strong>'
                    . $esc($when) . '</strong>. On that day we will charge <strong>' . $esc($currency . ' ' . number_format($amount, 2)) . '</strong> to '
                    . ($card !== '' ? 'your ' . $esc($card) : 'the card on file') . '.</p>'
                    . '<p>Nothing to do if you want to keep it. To stop the renewal, turn off auto-renew from '
                    . '<a href="' . base_url('user/my_account') . '">your account</a> before then — your plan stays active until the end of the term you have paid for.</p>'
                    . '<p>Questions about this charge? Reply to this email or <a href="' . base_url('contact-us') . '">contact us</a>. '
                    . 'Our <a href="' . base_url('terms-of-use') . '#refunds">refund &amp; cancellation policy</a> is on the website.</p>';

                $html = $this->mailer->render('Your renewal is coming up', $body, array(
                    'preheader' => 'We will charge ' . $currency . ' ' . number_format($amount, 2) . ' on ' . $when . '.',
                ));
                if ($this->mailer->send($u->email_id, 'Upcoming Pearland411 renewal on ' . $when, $html, null, array('log_type' => 'renewal_reminder'))) {
                    $sent++;
                }
                // Marked whether or not the send worked: a dead mailbox must
                // not be retried every night. email_log has the failure.
                $this->usermodel->markRenewalReminded($sub);
            } catch (Exception $e) {
                log_message('error', 'Renewal reminder failed for subscription ' . $sub->id . ': ' . $e->getMessage());
            }
        }
        return $sent;
    }

    /** The customer's receipt for a successful automatic renewal. */
    private function notify_renewal_receipt($sub, $amount, $currency, $txn)
    {
        try {
            $u = $this->db->select('email_id, name')->from('user')->where('user_id', (int) $sub->user_id)->get()->row();
            if (!$u || empty($u->email_id)) return;

            $fresh = $this->db->where('id', (int) $sub->id)->get('subscriptions')->row();
            $plan  = $this->usermodel->getPlanById($fresh ? (int) $fresh->plan_id : (int) $sub->plan_id);
            $card  = $this->usermodel->vaultedCardLabel((int) $sub->user_id);
            $esc   = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };

            $body = '<p>Hi ' . $esc($u->name) . ',</p>'
                . '<p>Your Pearland411 <strong>' . $esc($plan ? $plan->name : 'subscription') . '</strong> has renewed. We charged <strong>'
                . $esc($currency . ' ' . number_format((float) $amount, 2)) . '</strong> to ' . ($card !== '' ? 'your ' . $esc($card) : 'the card on file')
                . ($txn ? ' (transaction ' . $esc($txn) . ')' : '') . '.</p>'
                . ($fresh && !empty($fresh->end_date) ? '<p>Your plan now runs until <strong>' . $esc(date('F j, Y', strtotime($fresh->end_date))) . '</strong>.</p>' : '')
                . '<p>To stop future renewals, turn off auto-renew from <a href="' . base_url('user/my_account') . '">your account</a>. '
                . 'See our <a href="' . base_url('terms-of-use') . '#refunds">refund &amp; cancellation policy</a>.</p>';

            $html = $this->mailer->render('Payment received — thank you', $body);
            $this->mailer->send($u->email_id, 'Your Pearland411 renewal receipt', $html, null, array('log_type' => 'renewal_receipt'));
        } catch (Exception $e) {
            log_message('error', 'Renewal receipt failed: ' . $e->getMessage());
        }
    }

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

        // The guard is per table, not per run. The job originally covered six
        // tables; `listings` and `user` were added later, once the member-side
        // write paths stopped encoding too. A single global flag would have
        // meant the new tables could never run on any site that had already
        // done the first six — while clearing it would decode those six twice
        // and unwrap their literal "&amp;" into "&".
        $already = site_setting('content_entities_decoded_at', '');
        $legacy  = array('news', 'video', 'blogs', 'products', 'classifieds', 'marketplace');

        // When a table has no flag of its own but the old global one is set and
        // the table was in the original six, it is done by definition. Resolved
        // in memory so a report run stays read-only; persisted below on apply.
        $decoded_on = function ($table) use ($already, $legacy) {
            $own = site_setting('content_entities_decoded_' . $table, '');
            if ($own !== '') return $own;
            return ($already !== '' && in_array($table, $legacy, TRUE)) ? $already : '';
        };

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
            // Added with the round-3 fix that stopped User.php encoding on the
            // way in. A member-created business name stored "Joe&#039;s BBQ",
            // which is what the directory and /search then printed verbatim.
            array('table' => 'listings', 'key' => 'listing_id', 'cols' => array(
                'listing_name', 'listing_address', 'listing_description',
                'service_offered', 'service_locations', 'service_name',
                'listing_seo_title', 'listing_seo_description')),
            array('table' => 'user', 'key' => 'user_id', 'cols' => array(
                'name', 'user_address', 'user_state', 'user_website', 'user_contact_email')),
        );

        echo $apply ? "Mode: APPLY (writing changes)\n" : "Mode: REPORT ONLY (pass 'apply' to write)\n";

        foreach ($targets as $t) {
            if (!$this->db->table_exists($t['table'])) {
                echo "-- {$t['table']}: table not present, skipped\n";
                continue;
            }

            $flag_key  = 'content_entities_decoded_' . $t['table'];
            $table_run = $decoded_on($t['table']);
            if ($table_run !== '' && $force !== 'force') {
                echo sprintf("-- %-12s already decoded on %s, skipped (add 'force' to repeat)\n",
                    $t['table'] . ':', $table_run);
                continue;
            }

            // Columns vary between schema versions; ask before selecting, or the
            // whole query fails on an older install and takes the run with it.
            $cols = array();
            foreach ($t['cols'] as $__c) {
                if ($this->db->field_exists($__c, $t['table'])) { $cols[] = $__c; }
            }
            if (!$cols) {
                echo "-- {$t['table']}: none of the expected columns present, skipped\n";
                continue;
            }
            $t['cols'] = $cols;

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

            echo sprintf("-- %-12s %d of %d row(s) %s\n", $t['table'] . ':', $changed, count($rows), $apply ? 'decoded' : 'would be decoded');

            if ($apply) {
                site_setting_save($flag_key, date('Y-m-d H:i:s'));
            }
        }

        if ($apply) {
            site_setting_save('content_entities_decoded_at', date('Y-m-d H:i:s'));
            echo "\nDone. Each table records its own flag in site_settings, so this\n";
            echo "cannot decode the same table twice by accident.\n";
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
