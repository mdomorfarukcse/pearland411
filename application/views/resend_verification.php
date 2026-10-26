<?php
/**
 * /resend-verification — ask for a fresh confirmation link.
 *
 * The page that did not exist. An account created but never confirmed could
 * not sign in and could not ask for another email, so every lost verification
 * mail became a support request. From User::resend_verification().
 *
 * Same shell as /login and /register (assets/css/directory.css + content.css
 * via $page_css), and the same deliberate vagueness as /forgot-password: the
 * confirmation never says whether the address is registered.
 */
$msg   = $this->session->flashdata('resend_message');
$error = isset($error) ? (string) $error : '';

// Pre-filled from the link on /login, so someone who has just been refused
// doesn't have to type the address again.
$prefill = trim((string) $this->input->get('email', true));
if ($prefill !== '' && !filter_var($prefill, FILTER_VALIDATE_EMAIL)) {
	$prefill = '';
}
?>

<section class="p411-ct p411-ct--auth">
	<div class="container">

		<nav class="p411-crumb" aria-label="Breadcrumb">
			<a href="<?= base_url() ?>">Home</a>
			<span aria-hidden="true">/</span>
			<a href="<?= base_url('login') ?>">Sign in</a>
			<span aria-hidden="true">/</span>
			<span class="is-current">Resend verification</span>
		</nav>

		<div class="p411-auth">

			<div class="p411-auth__card p411-form">
				<h1>Resend your confirmation email</h1>
				<p class="p411-form__hint">
					New accounts have to confirm their email address before they can sign in.
					Lost the message? Enter your address and we&rsquo;ll send a new link.
				</p>

				<?php if (!empty($msg)): ?>
					<div class="p411-note p411-note--ok" role="status"><?= e($msg) ?></div>
				<?php endif; ?>
				<?php if ($error !== ''): ?>
					<div class="p411-note p411-note--err" role="alert"><?= e($error) ?></div>
				<?php endif; ?>

				<form id="resend_form" name="resend_form" method="post"
					  action="<?= base_url('resend-verification') ?>" data-validate>
					<div class="form-group">
						<label for="email_id">Email address</label>
						<input type="email" autocomplete="email" name="email_id" id="email_id"
							   class="form-control" placeholder="you@example.com"
							   value="<?= e_attr($prefill) ?>" required>
						<div class="formError"><?= form_error('email_id') ?></div>
					</div>

					<?= spam_form_fields() ?>
					<button type="submit" name="resend_submit" class="p411-btn p411-btn--block p411-btn--lg">
						Send me a new link
					</button>
				</form>

				<div class="p411-auth__alt">
					<a href="<?= base_url('login') ?>">Back to sign in</a>
					<span aria-hidden="true">&middot;</span>
					<a href="<?= base_url('forgot-password') ?>">Forgot your password?</a>
				</div>
			</div>

			<aside class="p411-auth__side">
				<span class="p411-auth__eyebrow">Still nothing?</span>
				<h2>Three things to check</h2>
				<ul>
					<li>Your spam or junk folder &mdash; and any &ldquo;Promotions&rdquo; tab</li>
					<li>That the address is spelled the way you typed it at signup</li>
					<li>Give it five minutes; mail can queue</li>
				</ul>
				<p class="p411-auth__side-note">
					If it still hasn&rsquo;t arrived, tell us and we&rsquo;ll confirm the account for you.
				</p>
				<a class="p411-btn p411-btn--ghost p411-btn--block" href="<?= base_url('contact-us') ?>">Contact us</a>
			</aside>

		</div>
	</div>
</section>
