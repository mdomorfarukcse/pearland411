<?php
/**
 * /verify-email/<token> — the page the signup link lands on.
 *
 * Rebuilt on the shared page kit to match the rest of the auth flow. From
 * User::verify_email(): $verified.
 *
 * The failure branch now offers a way forward. It used to say only "invalid or
 * already used" with a button back to a login form that would refuse the
 * account — which, for anyone whose token had been superseded, was a loop with
 * no exit.
 */
$verified = !empty($verified);
?>

<section class="p411-ct p411-ct--auth">
	<div class="container">

		<nav class="p411-crumb" aria-label="Breadcrumb">
			<a href="<?= base_url() ?>">Home</a>
			<span aria-hidden="true">/</span>
			<span class="is-current">Email verification</span>
		</nav>

		<div class="p411-auth">

			<div class="p411-auth__card p411-form">
				<?php if ($verified): ?>
					<div class="p411-empty p411-empty--inline">
						<div class="p411-empty__icon">
							<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
								 stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<path d="M20 6 9 17l-5-5"/>
							</svg>
						</div>
						<h3>Email confirmed</h3>
						<p>Your address is verified. Sign in and you can add your business straight away.</p>
						<div class="p411-empty__actions">
							<a class="p411-btn" href="<?= base_url('login') ?>">Sign in</a>
							<a class="p411-btn p411-btn--ghost" href="<?= base_url('pricing-plan') ?>">See the packages</a>
						</div>
					</div>
				<?php else: ?>
					<h1>We couldn&rsquo;t confirm that link</h1>
					<div class="p411-note p411-note--err" role="alert">
						This verification link is invalid, or it has already been used.
					</div>
					<p class="p411-form__hint">
						If you have already confirmed this address, just sign in. If you asked for a
						second link, only the newest one works &mdash; use that email, or request
						another below.
					</p>
					<div class="p411-empty__actions">
						<a class="p411-btn" href="<?= base_url('resend-verification') ?>">Send me a new link</a>
						<a class="p411-btn p411-btn--ghost" href="<?= base_url('login') ?>">Back to sign in</a>
					</div>
				<?php endif; ?>
			</div>

			<aside class="p411-auth__side">
				<span class="p411-auth__eyebrow">Pearland411 account</span>
				<h2>Everything about your business, in one place</h2>
				<ul>
					<li>Edit your listing &mdash; hours, photos, phone, website</li>
					<li>Post events, deals and classifieds</li>
					<li>Reply to reviews and enquiries</li>
					<li>Buy and manage advertising</li>
				</ul>
			</aside>

		</div>
	</div>
</section>
