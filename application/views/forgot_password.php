<?php
/**
 * /forgot-password — request a password reset link.
 *
 * Rebuilt on the shared page kit (assets/css/directory.css + content.css via
 * $page_css) to match /login and /register. It was still the theme's old
 * `login-reg` block, which is the reason the three pages of one flow looked
 * like three different sites — and the controller never set $page_css, so the
 * kit wasn't even loaded here.
 *
 * From User::forgot_password(): flashdata('forgot_message'), $error. The
 * confirmation is deliberately non-committal: it must not reveal whether an
 * address is registered.
 */
$msg   = $this->session->flashdata('forgot_message');
$error = isset($error) ? (string) $error : '';
?>

<section class="p411-ct p411-ct--auth">
	<div class="container">

		<nav class="p411-crumb" aria-label="Breadcrumb">
			<a href="<?= base_url() ?>">Home</a>
			<span aria-hidden="true">/</span>
			<a href="<?= base_url('login') ?>">Sign in</a>
			<span aria-hidden="true">/</span>
			<span class="is-current">Forgot password</span>
		</nav>

		<div class="p411-auth">

			<div class="p411-auth__card p411-form">
				<h1>Forgot your password?</h1>
				<p class="p411-form__hint">
					Enter the address you signed up with and we&rsquo;ll email you a link to set a
					new password. The link is good for one hour.
				</p>

				<?php if (!empty($msg)): ?>
					<div class="p411-note p411-note--ok" role="status"><?= e($msg) ?></div>
				<?php endif; ?>
				<?php if ($error !== ''): ?>
					<div class="p411-note p411-note--err" role="alert"><?= e($error) ?></div>
				<?php endif; ?>

				<form id="forget_form" name="forget_form" method="post"
					  action="<?= base_url('forgot-password') ?>" data-validate>
					<div class="form-group">
						<label for="email_id">Email address</label>
						<input type="email" autocomplete="email" name="email_id" id="email_id"
							   class="form-control" placeholder="you@example.com" required>
						<div class="formError"><?= form_error('email_id') ?></div>
					</div>

					<?= spam_form_fields() ?>
					<button type="submit" name="forgot_submit" class="p411-btn p411-btn--block p411-btn--lg">
						Email me a reset link
					</button>
				</form>

				<div class="p411-auth__alt">
					<a href="<?= base_url('login') ?>">Back to sign in</a>
					<span aria-hidden="true">&middot;</span>
					<a href="<?= base_url('register') ?>">Create an account</a>
				</div>
			</div>

			<aside class="p411-auth__side">
				<span class="p411-auth__eyebrow">Didn&rsquo;t arrive?</span>
				<h2>Check spam first</h2>
				<ul>
					<li>Look in your spam or junk folder, and the &ldquo;Promotions&rdquo; tab</li>
					<li>Mail can take a few minutes to land</li>
					<li>The link expires an hour after it&rsquo;s sent &mdash; ask for a fresh one if it&rsquo;s older</li>
				</ul>
				<p class="p411-auth__side-note">
					Never confirmed your account after signing up? You need the verification email, not this one.
				</p>
				<a class="p411-btn p411-btn--ghost p411-btn--block" href="<?= base_url('resend-verification') ?>">Resend verification email</a>
			</aside>

		</div>
	</div>
</section>
