<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * The customer-lifecycle emails from the Website Update Checklist (§11):
 * claim submitted / approved / rejected / more information needed, purchase
 * confirmation, welcome & onboarding, listing published, and cancellation —
 * plus the matching alerts to the office.
 *
 *   $this->load->library('notifications');
 *   $this->notifications->claim_received($claim, $listing, $user);
 *
 * One place, so every one of them goes through Mailer::render() (the standard
 * layout) and Mailer::send() (logged in email_log, a real bool back), and so
 * the list of what a customer is told, and when, can be read in one file.
 *
 * Two rules from the 2026-10 spam work apply throughout:
 *   - customer emails go to the ACCOUNT's address (verified at sign-up), not
 *     to an address typed into a form, and
 *   - nothing a visitor typed is echoed into an email sent to an address they
 *     chose. Their own words only ever go to the office.
 *
 * Every method returns the Mailer's bool and never throws.
 */
class Notifications
{
    /** @var CI_Controller */
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library('mailer');
    }

    /* =====================================================================
     * Claim This Business
     * ===================================================================== */

    /** To the claimant: we have your claim, here is what happens next. */
    public function claim_received($claim, $listing, $user)
    {
        $body = '<p>Thanks &mdash; we received your request to claim <strong>' . e($listing->listing_name) . '</strong>.</p>'
            . '<p>Someone on our team will check it, usually within 1&ndash;2 business days, using the verification method you chose. '
            . 'We will email you as soon as it is approved, or if we need anything else from you.</p>'
            . $this->button(base_url('user/my-claims'), 'Follow your claim')
            . '<p style="color:#5A6472;font-size:14px;">Once it is approved you can edit the listing, add photos and see the enquiries it receives.</p>';

        return $this->to_member($user, $claim, 'We received your claim for ' . $listing->listing_name,
            'Claim received', $body, 'claim_received');
    }

    /**
     * To the office: a new claim, or the claimant's answer to "we need more
     * information". The claimant's own words are fine here — this goes to us.
     */
    public function claim_admin_alert($claim, $listing, $is_reply = FALSE)
    {
        $to = form_recipient_email();
        if ($to === '') {
            return FALSE;
        }

        $roles   = claim_roles();
        $methods = claim_verification_methods();
        $rows = array(
            'Listing'      => '<a href="' . e_attr(base_url('listing-details/' . rawurlencode($listing->listing_slug))) . '">' . e($listing->listing_name) . '</a>',
            'Business name given' => e((string) ($claim->claim_business_name ?? '')),
            'Claimant'     => e((string) $claim->enquiry_name),
            'Role'         => e($roles[$claim->claim_role ?? ''] ?? (string) ($claim->claim_role ?? '')),
            'Email'        => e((string) $claim->enquiry_email),
            'Phone'        => e((string) $claim->enquiry_mobile),
            'Verify by'    => e($methods[$claim->claim_verification ?? ''] ?? (string) ($claim->claim_verification ?? '')),
            'Document'     => !empty($claim->enquiry_image)
                ? '<a href="' . e_attr(base_url('admin/claim-document/' . (int) $claim->claim_listing_id)) . '">View the upload</a> (sign in to admin first)'
                : '—',
            'Listing now'  => listing_is_claimed($listing) ? 'Already claimed by another account' : 'Unclaimed',
        );

        $body = $this->rows($rows)
            . '<p><strong>' . ($is_reply ? 'Their reply' : 'Notes') . ':</strong></p>'
            . '<blockquote style="margin:0 0 16px 0;padding:10px 14px;border-left:3px solid #E4E6EC;color:#1F2430;">'
            . (trim((string) $claim->enquiry_message) !== '' ? e_nl2br($claim->enquiry_message) : '<em>none</em>')
            . '</blockquote>'
            . $this->button(base_url('admin/claimed-listings'), 'Review the claim');

        $subject = ($is_reply ? '[Pearland411] Claim reply — ' : '[Pearland411] New listing claim — ') . $listing->listing_name;
        return $this->CI->mailer->send($to, $subject,
            $this->CI->mailer->render($is_reply ? 'A claimant sent more information' : 'New listing claim #' . (int) $claim->claim_listing_id, $body),
            NULL, array(
                'reply_to'      => (string) $claim->enquiry_email,
                'reply_to_name' => (string) $claim->enquiry_name,
                'log_type'      => 'claim_admin',
            ));
    }

    /** To the claimant: approved — sign in and finish the listing. */
    public function claim_approved($claim, $listing, $user, $moved_enquiries = 0)
    {
        $edit = base_url('user/edit-listing/' . rawurlencode($listing->listing_slug));
        $body = '<p>Good news &mdash; your claim for <strong>' . e($listing->listing_name) . '</strong> was approved. '
            . 'The listing is now in your account.</p>'
            . '<p><strong>Next, make it yours:</strong></p>'
            . $this->steps(array(
                'Check the name, phone, address and website are right.',
                'Write a short description of what you do.',
                'Add your logo, photos, hours and social links (what your package includes is shown on the form).',
                'Save — the changes are live straight away.',
            ))
            . $this->button($edit, 'Edit your listing')
            . ($moved_enquiries > 0
                ? '<p>The listing&rsquo;s enquiries are in your account too, including the ' . (int) $moved_enquiries
                  . ' sent before your claim was approved: <a href="' . e_attr(base_url('user/enquiries')) . '">see your enquiries</a>.</p>'
                : '<p>Enquiries sent to the business from now on appear under <a href="' . e_attr(base_url('user/enquiries')) . '">My enquiries</a>.</p>')
            . '<p style="color:#5A6472;font-size:14px;">Want more people to find you? <a href="' . e_attr(base_url('pricing-plan')) . '">See the packages</a>.</p>';

        return $this->to_member($user, $claim, 'Your claim for ' . $listing->listing_name . ' was approved',
            'Your claim was approved', $body, 'claim_approved');
    }

    /** To the claimant: not approved, with the reviewer's reason when given. */
    public function claim_rejected($claim, $listing, $user, $reason = '')
    {
        $body = '<p>We reviewed your claim for <strong>' . e($listing->listing_name) . '</strong> and could not approve it.</p>'
            . (trim((string) $reason) !== ''
                ? '<p><strong>Why:</strong></p><blockquote style="margin:0 0 16px 0;padding:10px 14px;border-left:3px solid #E4E6EC;">' . e_nl2br($reason) . '</blockquote>'
                : '')
            . '<p>If this is your business, you can send a new claim with more proof &mdash; a business license, a utility bill '
            . 'in the business&rsquo;s name, or an email from the business&rsquo;s own domain all help.</p>'
            . $this->button(base_url('claim-listing/' . rawurlencode($listing->listing_slug)), 'Send a new claim')
            . '<p style="color:#5A6472;font-size:14px;">Questions? Reply to this email or <a href="' . e_attr(base_url('contact-us')) . '">contact us</a>.</p>';

        return $this->to_member($user, $claim, 'Update on your claim for ' . $listing->listing_name,
            'We could not approve your claim', $body, 'claim_rejected');
    }

    /** To the claimant: the reviewer needs something before deciding. */
    public function claim_more_info($claim, $listing, $user, $question)
    {
        $body = '<p>We&rsquo;re reviewing your claim for <strong>' . e($listing->listing_name) . '</strong> and need a little more from you first:</p>'
            . '<blockquote style="margin:0 0 16px 0;padding:10px 14px;border-left:3px solid #79B82A;">' . e_nl2br($question) . '</blockquote>'
            . '<p>Reply from your account &mdash; you can write a note and attach a document.</p>'
            . $this->button(base_url('user/my-claims'), 'Send the information');

        return $this->to_member($user, $claim, 'We need more information about your claim for ' . $listing->listing_name,
            'More information needed', $body, 'claim_more_info');
    }

    /* =====================================================================
     * Checkout & onboarding
     * ===================================================================== */

    /**
     * To the customer: the receipt for a checkout.
     *
     * @param object $user  the `user` row
     * @param array  $lines [['name', 'price', 'term_label', 'recurring' (bool), 'target_category_name']]
     * @param array  $opts  'auto_renew' (bool), 'next_billing' (Y-m-d H:i:s|null), 'txn' (string)
     */
    public function purchase_receipt($user, array $lines, $total, $currency, array $opts = array())
    {
        $rows = '';
        foreach ($lines as $line) {
            $detail = array();
            if (!empty($line['term_label']))           $detail[] = e($line['term_label']);
            if (!empty($line['target_category_name'])) $detail[] = 'in ' . e($line['target_category_name']);
            $detail[] = !empty($line['recurring']) && !empty($opts['auto_renew']) ? 'renews automatically' : 'one-time, does not renew';
            $rows .= '<tr>'
                . '<td style="padding:8px 0;border-bottom:1px solid #E4E6EC;"><strong>' . e($line['name']) . '</strong><br>'
                . '<span style="color:#5A6472;font-size:13px;">' . implode(' &middot; ', $detail) . '</span></td>'
                . '<td style="padding:8px 0;border-bottom:1px solid #E4E6EC;text-align:right;white-space:nowrap;">'
                . e($currency) . ' ' . number_format((float) $line['price'], 2) . '</td>'
                . '</tr>';
        }
        $rows .= '<tr><td style="padding:10px 0;"><strong>Total paid</strong></td>'
            . '<td style="padding:10px 0;text-align:right;"><strong>' . e($currency) . ' ' . number_format((float) $total, 2) . '</strong></td></tr>';

        $body = '<p>Thank you for your order. Your payment went through and your package is active.</p>'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size:15px;">' . $rows . '</table>'
            . (!empty($opts['txn']) ? '<p style="color:#5A6472;font-size:13px;">Transaction reference: ' . e($opts['txn']) . '</p>' : '')
            . (!empty($opts['auto_renew']) && !empty($opts['next_billing'])
                ? '<p>Your card will be charged again on <strong>' . e(us_date($opts['next_billing'])) . '</strong>. We&rsquo;ll email you a few days before. '
                  . 'You can turn renewal off at any time under <a href="' . e_attr(base_url('user/my_account')) . '">My Account</a>.</p>'
                : '<p>Nothing renews automatically: you will not be charged again unless you buy again.</p>')
            . $this->button(base_url('user/my_account'), 'View your account')
            . '<p style="color:#5A6472;font-size:13px;">Our refund &amp; cancellation policy: <a href="' . e_attr(base_url('terms-of-use#refunds')) . '">'
            . e(base_url('terms-of-use#refunds')) . '</a></p>';

        return $this->to_user($user, 'Payment received — your Pearland411 order', 'Payment received', $body, 'purchase_receipt',
            'Receipt for ' . $currency . ' ' . number_format((float) $total, 2));
    }

    /**
     * To the customer: welcome — here is how to get the most from what you
     * bought. Sent once per checkout, after the receipt.
     *
     * @param object   $user
     * @param array    $lines    as purchase_receipt()
     * @param object[] $listings the listings this account owns (may be empty)
     */
    public function welcome_onboarding($user, array $lines, array $listings)
    {
        $has_listing_plan = FALSE;
        $has_placement    = FALSE;
        foreach ($lines as $line) {
            if (($line['category'] ?? '') === 'business-listing') $has_listing_plan = TRUE;
            else $has_placement = TRUE;
        }

        $steps = array();
        if ($listings) {
            $first = $listings[0];
            $edit  = base_url('user/edit-listing/' . rawurlencode($first->listing_slug));
            $steps[] = 'Open <a href="' . e_attr($edit) . '">your listing</a> and check the name, phone, address and website.';
        } else {
            $steps[] = 'Add your business: <a href="' . e_attr(base_url('user/add_new_listing_start')) . '">add a listing</a>, or find it on the '
                . '<a href="' . e_attr(base_url('all-listing')) . '">directory</a> and press <em>Claim This Business</em>.';
        }
        if ($has_listing_plan) {
            $steps[] = 'Upload your logo and photos.';
            $steps[] = 'Write a description, and add your hours, services and social links.';
            $steps[] = 'Save. Changes are live straight away &mdash; open the listing to see them.';
        }
        if ($has_placement) {
            $steps[] = 'For a banner, video or homepage placement, reply to this email with your artwork (or YouTube link) and the page it should '
                . 'link to. Not sure what to send? We can design it for you &mdash; just ask.';
        }

        $body = '<p>Welcome to Pearland411' . (trim((string) ($user->name ?? '')) !== '' ? ', ' . e($user->name) : '') . '. '
            . 'Here&rsquo;s how to get your business in front of Pearland:</p>'
            . $this->steps($steps)
            . ($listings
                ? $this->button(base_url('user/edit-listing/' . rawurlencode($listings[0]->listing_slug)), 'Finish your listing')
                : $this->button(base_url('user/add_new_listing_start'), 'Add your business'))
            . '<p>Enquiries from customers arrive by email and under <a href="' . e_attr(base_url('user/enquiries')) . '">My enquiries</a>. '
            . 'Need a hand? Reply to this email or call us' . $this->phone_suffix() . '.</p>';

        return $this->to_user($user, 'Welcome to Pearland411 — let’s finish your listing', 'Welcome to Pearland411', $body,
            'welcome_onboarding', 'Your next steps');
    }

    /** To the office: an online order, with what still needs doing by hand. */
    public function purchase_admin_alert($user, array $lines, $total, $currency, $txn = '')
    {
        $items = '';
        $todo  = array();
        foreach ($lines as $line) {
            $items .= '<li>' . e($line['name'])
                . (!empty($line['term_label']) ? ' &middot; ' . e($line['term_label']) : '')
                . (!empty($line['target_category_name']) ? ' &middot; category: ' . e($line['target_category_name']) : '')
                . ' &middot; ' . e($currency) . ' ' . number_format((float) $line['price'], 2) . '</li>';
            $cat = $line['category'] ?? '';
            if ($cat === 'add-ons') {
                $todo[] = e($line['name']) . ': set the dates at <a href="' . e_attr(base_url('admin/homepage-placements')) . '">Homepage placements</a>.';
            } elseif ($cat !== 'business-listing') {
                $todo[] = e($line['name']) . ': collect the artwork and book it at <a href="' . e_attr(base_url('admin/create-ads')) . '">Create ad</a>.';
            }
        }

        $body = $this->rows(array(
                'Customer' => e((string) ($user->name ?? '')) . ' &lt;' . e((string) ($user->email_id ?? '')) . '&gt;',
                'Total'    => e($currency) . ' ' . number_format((float) $total, 2),
                'Helcim'   => e((string) $txn),
            ))
            . '<ul>' . $items . '</ul>'
            . ($todo ? '<p><strong>To do:</strong></p>' . $this->steps($todo) : '<p>Nothing to do by hand: the listing package is active.</p>');

        return $this->CI->mailer->notify_billing('[Pearland411] New online order — ' . $currency . ' ' . number_format((float) $total, 2),
            'New online order', $body);
    }

    /** To the customer: auto-renew is off; the plan runs to the end of the term. */
    public function subscription_cancelled($user, $subscription, $plan_name)
    {
        $end = !empty($subscription->end_date) ? us_date($subscription->end_date) : '';
        $body = '<p>Automatic renewal of <strong>' . e($plan_name) . '</strong> is now off. Your card will not be charged again.</p>'
            . ($end !== '' ? '<p>Your package stays active until <strong>' . e($end) . '</strong>. After that your listing stays on Pearland411 on the free plan.</p>' : '')
            . '<p>Changed your mind? You can buy again at any time from <a href="' . e_attr(base_url('pricing-plan')) . '">our packages</a>.</p>'
            . $this->button(base_url('user/my_account'), 'View your account');

        return $this->to_user($user, 'Your Pearland411 renewal is cancelled', 'Renewal cancelled', $body, 'subscription_cancelled');
    }

    /** To the owner: their listing went from not-live to live. */
    public function listing_published($listing, $user)
    {
        $url  = base_url('listing-details/' . rawurlencode($listing->listing_slug));
        $body = '<p><strong>' . e($listing->listing_name) . '</strong> is now live on Pearland411.</p>'
            . $this->button($url, 'See your listing')
            . '<p>Keep it current &mdash; hours, photos and offers bring more enquiries: '
            . '<a href="' . e_attr(base_url('user/edit-listing/' . rawurlencode($listing->listing_slug))) . '">edit your listing</a>.</p>';

        return $this->to_user($user, $listing->listing_name . ' is live on Pearland411', 'Your listing is live', $body, 'listing_published');
    }

    /* =====================================================================
     * Internals
     * ===================================================================== */

    /**
     * Send to a member about their claim: the account's address, falling
     * back to the one on the claim only for an account that has none.
     */
    protected function to_member($user, $claim, $subject, $heading, $body, $log_type)
    {
        $to = $user && !empty($user->email_id) ? (string) $user->email_id : (string) ($claim->enquiry_email ?? '');
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return FALSE;
        }
        return $this->CI->mailer->send($to, $subject, $this->CI->mailer->render($heading, $body), NULL, array('log_type' => $log_type));
    }

    protected function to_user($user, $subject, $heading, $body, $log_type, $preheader = '')
    {
        $to = $user && !empty($user->email_id) ? (string) $user->email_id : '';
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return FALSE;
        }
        try {
            return $this->CI->mailer->send($to, $subject,
                $this->CI->mailer->render($heading, $body, array('preheader' => $preheader)),
                NULL, array('log_type' => $log_type));
        } catch (Throwable $e) {
            log_message('error', 'Notifications: ' . $log_type . ' failed: ' . $e->getMessage());
            return FALSE;
        }
    }

    protected function button($url, $label)
    {
        return '<p style="margin:18px 0;"><a href="' . e_attr($url) . '" style="display:inline-block;background:#20245B;color:#ffffff;'
            . 'text-decoration:none;font-weight:700;padding:11px 20px;border-radius:6px;">' . e($label) . '</a></p>';
    }

    /** @param string[] $steps already-safe HTML */
    protected function steps(array $steps)
    {
        $out = '<ol style="margin:0 0 12px 0;padding-left:20px;">';
        foreach ($steps as $step) {
            $out .= '<li style="margin:0 0 6px 0;">' . $step . '</li>';
        }
        return $out . '</ol>';
    }

    /** @param array<string,string> $rows label => already-safe HTML */
    protected function rows(array $rows)
    {
        $out = '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="font-size:14px;margin:0 0 14px 0;">';
        foreach ($rows as $label => $value) {
            $out .= '<tr><td style="padding:3px 14px 3px 0;color:#5A6472;vertical-align:top;white-space:nowrap;">' . e($label) . '</td>'
                . '<td style="padding:3px 0;">' . ($value !== '' ? $value : '—') . '</td></tr>';
        }
        return $out . '</table>';
    }

    protected function phone_suffix()
    {
        $phone = trim((string) site_setting('contact_phone', ''));
        return $phone !== '' ? ' on ' . e($phone) : '';
    }
}
