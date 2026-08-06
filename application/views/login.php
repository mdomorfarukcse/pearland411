<?php
/**
 * /login — member sign-in.
 *
 * Rebuilt on the shared page kit (assets/css/directory.css + content.css,
 * linked through $page_css) so it matches the homepage and the rest of the
 * site. What was here before was the theme's `login-reg` block: a bare form on
 * a white page with a blue submit button, plus a dead Google `g-signin2`
 * placeholder (nothing loads Google's platform.js, so it rendered an empty
 * box) and a copy of the theme's unused "Get quote" modal.
 *
 * From User::login(): $loginSucess, $http_referer. Both defaulted — the view
 * must not fatal if either is missing.
 */

$loginSucess  = (isset($loginSucess) && is_array($loginSucess)) ? $loginSucess : array();
$http_referer = isset($http_referer) ? (string) $http_referer : base_url();

$login_message = $this->session->flashdata('login_message');
$login_error   = !empty($loginSucess['message']) ? (string) $loginSucess['message'] : '';
?>

<section class="p411-ct p411-ct--auth">
	<div class="container">

		<nav class="p411-crumb" aria-label="Breadcrumb">
			<a href="<?= base_url() ?>">Home</a>
			<span aria-hidden="true">/</span>
			<span class="is-current">Sign in</span>
		</nav>

		<div class="p411-auth">

			<!-- ================================================ FORM CARD -->
			<div class="p411-auth__card p411-form">
				<h1>Sign in</h1>
				<p class="p411-form__hint">
					Manage your listing, post events and deals, and see how many people found you.
				</p>

				<?php if (!empty($login_message)): ?>
					<div class="p411-note p411-note--ok" role="status"><?= e($login_message) ?></div>
				<?php endif; ?>
				<?php if ($login_error !== ''): ?>
					<div class="p411-note p411-note--err" role="alert"><?= e($login_error) ?></div>
				<?php endif; ?>

				<form id="login_form" name="login_form" method="post" data-validate>
					<input type="hidden" name="lastpage" value="<?= e_attr($http_referer) ?>">

					<div class="form-group">
						<label for="email_id">Email address</label>
						<input type="email" autocomplete="username" name="email_id" id="email_id"
							   value="<?= set_value('email_id') ?>" class="form-control"
							   placeholder="you@example.com"
							   pattern="^[\w]{1,}[\w.+-]{0,}@[\w-]{2,}([.][a-zA-Z]{2,}|[.][\w-]{2,}[.][a-zA-Z]{2,})$"
							   title="Enter your email address" required>
						<div class="formError"><?= form_error('email_id') ?></div>
					</div>

					<div class="form-group">
						<label for="password">Password</label>
						<input type="password" name="password" id="password" autocomplete="current-password"
							   class="form-control" placeholder="Your password" required>
						<div class="formError"><?= form_error('password') ?></div>
					</div>

					<button type="submit" name="login_submit" value="submit" class="p411-btn p411-btn--block p411-btn--lg">
						Sign in
					</button>
				</form>

				<div class="p411-auth__alt">
					<a href="<?= base_url('forgot-password') ?>">Forgot your password?</a>
					<span aria-hidden="true">&middot;</span>
					<a href="<?= base_url('register') ?>">Create an account</a>
				</div>
			</div>

			<!-- ==================================================== ASIDE -->
			<aside class="p411-auth__side">
				<span class="p411-auth__eyebrow">Pearland411 account</span>
				<h2>Everything about your business, in one place</h2>
				<ul>
					<li>Edit your listing &mdash; hours, photos, phone, website</li>
					<li>Post events, deals and classifieds</li>
					<li>Reply to reviews and enquiries</li>
					<li>Buy and manage advertising</li>
				</ul>
				<p class="p411-auth__side-note">
					Not registered yet? Listing your business is free.
				</p>
				<a class="p411-btn p411-btn--ghost p411-btn--block" href="<?= base_url('register') ?>">Create an account</a>
			</aside>

		</div>

		<p class="p411-auth__help">
			Having trouble signing in? Call us on
			<?php $auth_phone = trim((string) site_setting('contact_phone', '281-482-2031')); ?>
			<a href="tel:<?= e_attr(preg_replace('/[^0-9+]/', '', $auth_phone)) ?>"><?= e($auth_phone) ?></a>
			or <a href="<?= base_url('contact-us') ?>">send us a message</a>.
		</p>

	</div>
</section>
