<?php
/**
 * /checkout-success — the receipt on screen, and what to do next.
 *
 * From User::checkout_success(): $success_data, as stored by
 * process_checkout() (plan_name, amount, currency, transaction_id,
 * subscription_id) plus what User::afterPurchase() adds: lines, auto_renew,
 * next_billing, customer_name, customer_email, emailed, listing_slug,
 * listing_name.
 *
 * It used to say "We've emailed a receipt" when nothing was sent, show a
 * "Next billing" date a month out for one-time purchases, leave "Customer"
 * blank, and pop a confirm() box after 30 seconds. Checklist §3/§4: payment →
 * confirmation → onboarding, so this page now says exactly what was bought,
 * whether the card is charged again, and how to finish the listing.
 */
$d        = (isset($success_data) && is_array($success_data)) ? $success_data : array();
$currency = !empty($d['currency']) ? (string) $d['currency'] : 'USD';
$amount   = (float) ($d['amount'] ?? 0);
$lines    = (isset($d['lines']) && is_array($d['lines'])) ? $d['lines'] : array(array(
	'name' => (string) ($d['plan_name'] ?? 'Your order'), 'price' => $amount, 'category' => '', 'recurring' => FALSE,
));
$auto      = !empty($d['auto_renew']);
$next      = !empty($d['next_billing']) ? us_date($d['next_billing']) : '';
$email     = (string) ($d['customer_email'] ?? '');
$slug      = (string) ($d['listing_slug'] ?? '');
$has_tier  = (bool) array_filter($lines, function ($l) { return ($l['category'] ?? '') === 'business-listing'; });
$has_promo = (bool) array_filter($lines, function ($l) { return ($l['category'] ?? '') !== 'business-listing'; });
?>

<section class="p411-ct p411-ct--auth p411-ct--paid">
	<div class="container">

		<nav class="p411-crumb" aria-label="Breadcrumb">
			<a href="<?= base_url() ?>">Home</a>
			<span aria-hidden="true">/</span>
			<span class="is-current">Payment received</span>
		</nav>

		<div class="p411-auth">

			<div class="p411-auth__card p411-form">
				<div class="p411-note p411-note--ok" role="status" style="margin:0 0 14px;">
					<strong>Payment successful.</strong> Thank you<?= !empty($d['customer_name']) ? ', ' . e($d['customer_name']) : '' ?>.
				</div>
				<h1>Your order</h1>

				<ul class="p411-facts" style="margin-bottom:14px;">
					<?php foreach ($lines as $line): ?>
						<li>
							<span>
								<?= e($line['name']) ?>
								<?php if (!empty($line['term_label']) || !empty($line['target_category_name'])): ?>
									<br><small><?= e(trim(($line['term_label'] ?? '') . (!empty($line['target_category_name']) ? ' · in ' . $line['target_category_name'] : ''), ' ·')) ?></small>
								<?php endif; ?>
							</span>
							<b><?= e($currency) ?> <?= number_format((float) $line['price'], 2) ?></b>
						</li>
					<?php endforeach; ?>
					<li><span><strong>Total paid</strong></span><b><?= e($currency) ?> <?= number_format($amount, 2) ?></b></li>
					<?php if (!empty($d['transaction_id'])): ?>
						<li><span>Transaction reference</span><b><?= e($d['transaction_id']) ?></b></li>
					<?php endif; ?>
				</ul>

				<p class="p411-form__hint">
					<?php if ($auto && $next !== ''): ?>
						Your card will be charged again on <strong><?= e($next) ?></strong>; we email you a few days before.
						You can turn renewal off at any time in <a href="<?= base_url('user/my_account') ?>">My Account</a>.
					<?php else: ?>
						Nothing renews automatically &mdash; you will not be charged again unless you buy again.
					<?php endif; ?>
				</p>
				<p class="p411-form__hint">
					<?php if (!empty($d['emailed']) && $email !== ''): ?>
						A receipt and your next steps are on their way to <strong><?= e($email) ?></strong>.
					<?php else: ?>
						Keep this page for your records &mdash; we could not email a receipt just now. Your payment and your package are not affected.
					<?php endif; ?>
				</p>

				<a class="p411-btn p411-btn--ghost p411-btn--block" href="<?= base_url('user/my_account') ?>">View my account</a>
			</div>

			<aside class="p411-auth__side">
				<span class="p411-auth__eyebrow">Next steps</span>
				<h2><?= $slug !== '' ? 'Finish your listing' : 'Get your business listed' ?></h2>
				<ul>
					<?php if ($slug !== ''): ?>
						<li>Check your name, phone, address and website</li>
						<?php if ($has_tier): ?>
							<li>Add your logo and photos</li>
							<li>Write a description; add hours, services and social links</li>
						<?php endif; ?>
						<li>Save &mdash; changes are live straight away</li>
					<?php else: ?>
						<li>Find your business in the directory and press <em>Claim This Business</em></li>
						<li>Or add it yourself &mdash; it goes live straight away</li>
					<?php endif; ?>
					<?php if ($has_promo): ?>
						<li>Banner, video or homepage spot: reply to your receipt email with your artwork (or YouTube link) and the page it should link to</li>
					<?php endif; ?>
				</ul>
				<?php if ($slug !== ''): ?>
					<a class="p411-btn p411-btn--block" href="<?= e_url(base_url('user/edit-listing/' . rawurlencode($slug))) ?>">Edit <?= e($d['listing_name'] ?? 'your listing') ?></a>
					<p class="p411-auth__side-note" style="margin-top:10px;">
						<a style="color:#fff;text-decoration:underline;" href="<?= e_url(base_url('listing-details/' . rawurlencode($slug))) ?>">View your live listing</a>
					</p>
				<?php else: ?>
					<a class="p411-btn p411-btn--block" href="<?= base_url('all-listing') ?>">Find my business</a>
					<a class="p411-btn p411-btn--ghost p411-btn--block" style="margin-top:8px;" href="<?= base_url('user/add_new_listing_start') ?>">Add my business</a>
				<?php endif; ?>
			</aside>

		</div>
	</div>
</section>
