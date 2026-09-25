<?php
/**
 * /reset-password/<token> — choose a new password.
 *
 * Rebuilt on the shared page kit to match /login, /register and
 * /forgot-password. From User::reset_password(): $token, $expired, $error.
 *
 * The confirm field carries data-match so assets/js/form-validate.js catches a
 * mismatch before the post; the server checks it independently.
 */
$expired = !empty($expired);
$error   = isset($error) ? (string) $error : '';
$token   = isset($token) ? (string) $token : '';
?>

<section class="p411-ct p411-ct--auth">
	<div class="container">

		<nav class="p411-crumb" aria-label="Breadcrumb">
			<a href="<?= base_url() ?>">Home</a>
			<span aria-hidden="true">/</span>
			<a href="<?= base_url('login') ?>">Sign in</a>
			<span aria-hidden="true">/</span>
			<span class="is-current">Reset password</span>
		</nav>

		<div class="p411-auth">

			<div class="p411-auth__card p411-form">

				<?php if ($expired): ?>
					<h1>That link has expired</h1>
					<div class="p411-note p411-note--err" role="alert">
						This password reset link is invalid, already used, or more than an hour old.
					</div>
					<p class="p411-form__hint">
						Reset links are single-use and expire after one hour. Ask for a fresh one and
						it will work.
					</p>
					<div class="p411-empty__actions">
						<a class="p411-btn" href="<?= base_url('forgot-password') ?>">Request a new link</a>
						<a class="p411-btn p411-btn--ghost" href="<?= base_url('login') ?>">Back to sign in</a>
					</div>
				<?php else: ?>
					<h1>Choose a new password</h1>
					<p class="p411-form__hint">
						At least 8 characters. You&rsquo;ll be signed in with it straight after.
					</p>

					<?php if ($error !== ''): ?>
						<div class="p411-note p411-note--err" role="alert"><?= e($error) ?></div>
					<?php endif; ?>

					<form method="post" action="<?= base_url('reset-password/' . rawurlencode($token)) ?>" data-validate>
						<div class="form-group">
							<label for="password">New password</label>
							<input type="password" name="password" id="password" autocomplete="new-password"
								   class="form-control" placeholder="At least 8 characters"
								   minlength="8" maxlength="128" required>
							<div class="formError"><?= form_error('password') ?></div>
						</div>

						<div class="form-group">
							<label for="password_confirm">Confirm new password</label>
							<input type="password" name="password_confirm" id="password_confirm" autocomplete="new-password"
								   class="form-control" placeholder="Type it again"
								   minlength="8" maxlength="128" required data-match="#password">
							<div class="formError"><?= form_error('password_confirm') ?></div>
						</div>

						<button type="submit" class="p411-btn p411-btn--block p411-btn--lg">
							Set new password
						</button>
					</form>

					<div class="p411-auth__alt">
						<a href="<?= base_url('login') ?>">Back to sign in</a>
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
