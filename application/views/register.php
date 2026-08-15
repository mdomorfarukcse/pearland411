<?php
/**
 * /register — create an account.
 *
 * Same shell as /login, on the shared page kit (assets/css/directory.css +
 * content.css through $page_css). Previously the theme's `login-reg` block: an
 * unlabelled form on a white page, with the zip-code radios styled as the
 * theme's `radi-v4` pills and a copy of the unused "Get quote" modal below.
 *
 * The sales-representative list now comes from sales_representatives()
 * (application/helpers/view_helper.php) — the same list the callback panel on
 * /pricing-plan offers, so the two cannot drift.
 *
 * From User::register(): $registrationSucess, $reset.
 */

$registrationSucess = (isset($registrationSucess) && is_array($registrationSucess))
	? $registrationSucess
	: array('status' => false);
$reset = !empty($reset);
$done  = !empty($registrationSucess['status']);

/** Repopulate a field unless we've just wiped the form after a success. */
$rv = function ($field) use ($reset) { return $reset ? '' : set_value($field); };

$zip_codes = array('77581', '77584', '77588');
$reps      = function_exists('sales_representatives') ? sales_representatives() : array();
?>

<section class="p411-ct p411-ct--auth">
	<div class="container">


		<nav class="p411-crumb" aria-label="Breadcrumb">
			<a href="<?= base_url() ?>">Home</a>
			<span aria-hidden="true">/</span>
			<span class="is-current">Create an account</span>
		</nav>

		<div class="p411-auth">

			<!-- ================================================ FORM CARD -->
			<div class="p411-auth__card p411-form">
				<?php if ($done): ?>
					<div class="p411-empty p411-empty--inline">
						<div class="p411-empty__icon">
							<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<path d="M20 6 9 17l-5-5"/>
							</svg>
						</div>
						<h3>Account created</h3>
						<p>You're all set. Sign in and you can add your business straight away.</p>
						<div class="p411-empty__actions">
							<a class="p411-btn" href="<?= base_url('login') ?>">Sign in</a>
							<a class="p411-btn p411-btn--ghost" href="<?= base_url('pricing-plan') ?>">See the packages</a>
						</div>
					</div>
				<?php else: ?>
					<h1>Create an account</h1>
					<p class="p411-form__hint">
						It takes about a minute, and listing your business is free.
					</p>

					<?php
					/* registerUser() returns a message only for a failed INSERT;
					   a validation failure comes back bare, and the old view
					   printed nothing at all for it. */
					$reg_errors = trim(strip_tags(validation_errors()));
					?>
					<?php if (!empty($registrationSucess['message'])): ?>
						<div class="p411-note p411-note--err" role="alert"><?= e($registrationSucess['message']) ?></div>
					<?php elseif ($reg_errors !== ''): ?>
						<div class="p411-note p411-note--err" role="alert"><?= nl2br(e($reg_errors)) ?></div>
					<?php endif; ?>

					<form name="register_form" id="register_form" method="post" data-validate>
						<div class="p411-form__row">
							<div class="form-group">
								<label for="name">Your name <span class="p411-req">*</span></label>
								<input type="text" autocomplete="name" name="name" id="name"
									   value="<?= $rv('name') ?>" class="form-control"
									   placeholder="First and last name" minlength="3" required>
								<div class="formError"><?= form_error('name') ?></div>
							</div>
							<div class="form-group">
								<label for="mobile_number">Phone <span class="p411-req">*</span></label>
								<input type="text" onkeypress="return isNumber(event)" autocomplete="tel"
									   name="mobile_number" id="mobile_number" value="<?= $rv('mobile_number') ?>"
									   class="form-control" placeholder="281-000-0000" minlength="10" required>
								<div class="formError"><?= form_error('mobile_number') ?></div>
							</div>
						</div>

						<div class="form-group">
							<label for="email_id">Email address <span class="p411-req">*</span></label>
							<input type="email" autocomplete="email" name="email_id" id="email_id"
								   value="<?= $rv('email_id') ?>" class="form-control"
								   placeholder="you@example.com" required>
							<div class="formError"><?= form_error('email_id') ?></div>
						</div>

						<div class="form-group">
							<label for="password">Password <span class="p411-req">*</span></label>
							<?php // minlength matches the server's min_length[8] and this field's
							      // own promise. The server accepted six, so "At least 8
							      // characters" was not true and nothing enforced it either end. ?>
							<input type="password" name="password" id="password" autocomplete="new-password"
								   class="form-control" placeholder="At least 8 characters" minlength="8" maxlength="128" required>
							<div class="formError"><?= form_error('password') ?></div>
						</div>

						<div class="form-group">
							<label for="password_confirm">Confirm password <span class="p411-req">*</span></label>
							<input type="password" name="password_confirm" id="password_confirm" autocomplete="new-password"
								   class="form-control" placeholder="Type it again" minlength="8" maxlength="128" required data-match="#password">
							<div class="formError"><?= form_error('password_confirm') ?></div>
						</div>

						<?php
						// Honeypot. Not type="hidden" — a bot skips those; it fills in
						// anything that looks like a real field. Moved off-screen rather
						// than display:none for the same reason. Never shown to a person,
						// so a value in it means the submission is automated.
						?>
						<div aria-hidden="true" style="position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;">
							<label for="website_url">Leave this field empty</label>
							<input type="text" name="website_url" id="website_url" tabindex="-1" autocomplete="off" value="">
						</div>

						<div class="form-group">
							<label for="hear_us">How did you hear about us?</label>
							<input type="text" autocomplete="off" value="<?= $rv('hear_us') ?>"
								   name="hear_us" id="hear_us" class="form-control"
								   placeholder="A friend, a flyer, a Pearland411 rep&hellip;">
						</div>

						<?php if (!empty($reps)): ?>
							<div class="form-group">
								<label for="sale_representative">Sales representative</label>
								<div class="p411-select">
									<select name="sale_representative" id="sale_representative" class="form-control">
										<option value="">None &mdash; I came here myself</option>
										<?php foreach ($reps as $rep): ?>
											<option value="<?= e_attr($rep) ?>"><?= e($rep) ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
						<?php endif; ?>

						<div class="form-group">
							<label id="zip-label">Zip code</label>
							<div class="p411-choices" role="radiogroup" aria-labelledby="zip-label">
								<?php foreach ($zip_codes as $i => $zip): ?>
									<label class="p411-choice" for="zip_<?= e_attr($zip) ?>">
										<input type="radio" id="zip_<?= e_attr($zip) ?>" name="zip_code"
											   value="<?= e_attr($zip) ?>" <?= $i === 0 ? 'checked' : '' ?>>
										<span><?= e($zip) ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						</div>

						<button type="submit" name="register_submit" class="p411-btn p411-btn--block p411-btn--lg">
							Create my account
						</button>
						<p class="p411-form__hint" style="margin:12px 0 0;">
							By registering you agree to our
							<a href="<?= base_url('terms-of-use') ?>">terms of use</a> and
							<a href="<?= base_url('privacy-policy') ?>">privacy policy</a>.
						</p>
					</form>

					<div class="p411-auth__alt">
						Already have an account?
						<a href="<?= base_url('login') ?>">Sign in</a>
					</div>
				<?php endif; ?>
			</div>

			<!-- ==================================================== ASIDE -->
			<aside class="p411-auth__side">
				<span class="p411-auth__eyebrow">Why register</span>
				<h2>Put your business in front of Pearland</h2>
				<ul>
					<li>A free listing with your hours, photos and contact details</li>
					<li>Post events, deals and classifieds all year</li>
					<li>Collect and reply to customer reviews</li>
					<li>Add banner advertising whenever you're ready</li>
				</ul>
				<p class="p411-auth__side-note">
					Would you rather do this over the phone? We're happy to set it up for you.
				</p>
				<a class="p411-btn p411-btn--ghost p411-btn--block" href="<?= base_url('pricing-plan') ?>#talk-to-us">Talk to a representative</a>
			</aside>

		</div>

		<p class="p411-auth__help">
			Stuck? Call us on
			<?php $auth_phone = trim((string) site_setting('contact_phone', '281-482-2031')); ?>
			<a href="tel:<?= e_attr(preg_replace('/[^0-9+]/', '', $auth_phone)) ?>"><?= e($auth_phone) ?></a>
			or <a href="<?= base_url('contact-us') ?>">send us a message</a>.
		</p>


	</div>
</section>
