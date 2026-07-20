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
}
