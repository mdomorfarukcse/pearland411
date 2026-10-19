<?php
/**
 * Refund & cancellation policy — one text, included by /terms-of-use (as a
 * numbered section, anchor #refunds) and /faq.
 *
 * Tracker: "Please add a clear refund and cancellation policy. If there is no
 * self-service cancellation option available, please specify whether the
 * customer needs to contact the help desk/support team to request a
 * cancellation or refund." Both pages only said "you agree to the payment
 * policies listed during checkout", and checkout listed none.
 *
 * It describes what the system actually does, so keep it in step with:
 *   - which packages renew  — plan_term_copy() (only business-listing tiers);
 *   - self-service cancel   — User::cancel_subscription (My Account →
 *                             "Cancel auto-renew"), plan runs to end_date;
 *   - the renewal reminder  — Cli::send_renewal_reminders, renewal_reminder_days;
 *   - refunds               — not self-service; staff refund through Helcim.
 * The request window and any extra wording are set at /admin/setting.
 *
 * Optional: $rp_heading_tag ('h2'/'h3'/'strong'), $rp_number ("7."), $rp_hide_title
 * (the FAQ supplies its own question as the heading).
 */
$rp_days    = max(0, (int) site_setting('refund_request_days', '30'));
$rp_remind  = max(1, (int) site_setting('renewal_reminder_days', '3'));
$rp_extra   = trim((string) site_setting('refund_policy_extra', ''));
$rp_email   = trim((string) site_setting('contact_email', ''));
if ($rp_email === '' || !filter_var($rp_email, FILTER_VALIDATE_EMAIL)) $rp_email = 'support@pearland411.com';
$rp_phone   = trim((string) site_setting('contact_phone', '281-482-2031'));
$rp_tag     = isset($rp_heading_tag) && in_array($rp_heading_tag, array('h2', 'h3', 'h4', 'strong'), true) ? $rp_heading_tag : 'strong';
$rp_num     = isset($rp_number) ? (string) $rp_number . ' ' : '';
?>
<div class="p411-refund-policy" id="refunds">
	<?php if (empty($rp_hide_title)): ?>
		<p><<?= $rp_tag ?>><?= e($rp_num) ?>Refund &amp; Cancellation Policy</<?= $rp_tag ?>></p>
	<?php endif; ?>

	<p><em>What renews, and what does not</em></p>
	<ul>
		<li><strong>Business listing plans</strong> (for example Advanced or Featured) are recurring <strong>only if you turn on
			auto-renew at checkout</strong>. With auto-renew on, your card is charged again at the start of every term until you
			cancel. We email you <?= (int) $rp_remind ?> day<?= $rp_remind === 1 ? '' : 's' ?> before each renewal charge, and send a
			receipt after it. Without auto-renew, the plan simply ends at the end of the term you bought.</li>
		<li><strong>Banners, videos, add-ons and packages</strong> are <strong>one-time charges</strong>. You are charged once; the
			placement runs for the term you bought and then ends. Nothing renews, so there is nothing to cancel.</li>
		<li>The free Basic listing costs nothing and is never charged.</li>
	</ul>

	<p><em>How to cancel</em></p>
	<ul>
		<li><strong>You can cancel yourself, at any time:</strong> sign in, open
			<a href="<?= e_url(base_url('user/my_account')) ?>">My Account</a> and press <strong>Cancel auto-renew</strong>.
			No further charges are made. Your plan stays active until the end of the term you have already paid for; after
			that your business stays in the directory and the paid features switch off.</li>
		<li>Prefer not to do it online? Contact our support team (details below) and we will cancel it for you.</li>
	</ul>

	<p><em>Refunds</em></p>
	<ul>
		<li>A listing upgrade or ad placement starts as soon as payment clears, so payments are <strong>not refundable once the
			term has started</strong>, except where:
			<ul>
				<li>you were charged in error &mdash; for example a duplicate charge, or a renewal taken after you had cancelled
					auto-renew; or</li>
				<li>a placement you paid for could not run because of a problem on our side.</li>
			</ul>
		</li>
		<li><strong>Refunds are not self-service &mdash; contact our support team to request one</strong><?php if ($rp_days > 0): ?>,
			within <?= (int) $rp_days ?> days of the charge<?php endif; ?>. Include the email address on your account, the
			business name and the date of the charge.</li>
		<li>Approved refunds go back to the card you paid with, through our payment processor (Helcim). Banks usually show
			the credit within 5&ndash;10 business days.</li>
		<?php if ($rp_extra !== ''): ?>
			<li><?= e($rp_extra) ?></li>
		<?php endif; ?>
	</ul>

	<p><em>Contact support for cancellations and refunds</em><br>
		Email <a href="mailto:<?= e_attr($rp_email) ?>"><?= e($rp_email) ?></a><?php if ($rp_phone !== ''): ?>,
		call <a href="tel:<?= e_attr(preg_replace('/[^0-9+]/', '', $rp_phone)) ?>"><?= e($rp_phone) ?></a><?php endif; ?>,
		or use the <a href="<?= e_url(base_url('contact-us')) ?>">contact form</a> and choose Billing.</p>
</div>
<?php unset($rp_days, $rp_remind, $rp_extra, $rp_email, $rp_phone, $rp_tag, $rp_num); ?>
