<?php
/**
 * /claim-listing/<slug> — "Claim This Business".
 *
 * On the shared page kit (directory.css + content.css via $page_css), in the
 * same two-column shell as sign in / register. It was an unstyled form with
 * inline styles and no container, edge to edge on a phone.
 *
 * Asks for what the checklist names (§1): business name, owner / contact
 * name, business email, phone, role at the business and how we can verify
 * them — plus an optional document and notes. From User::claim_listing():
 * $listing, $roles, $methods, $prefill, $claimed_by_other; on a refused post
 * also $error and $old (the posted values, kept).
 */

$old     = (isset($old) && is_array($old)) ? $old : array();
$prefill = (isset($prefill) && is_array($prefill)) ? $prefill : array();
$roles   = isset($roles) ? $roles : claim_roles();
$methods = isset($methods) ? $methods : claim_verification_methods();
$cv = function ($key) use ($old, $prefill) {
	if (array_key_exists($key, $old)) return e_attr((string) $old[$key]);
	return e_attr((string) ($prefill[$key] ?? ''));
};
$picked_role   = (string) ($old['claim_role'] ?? '');
$picked_method = (string) ($old['claim_verification'] ?? 'phone');
$ld_url = base_url('listing-details/' . rawurlencode($listing->listing_slug));
?>

<section class="p411-ct p411-ct--auth p411-ct--claim">
	<div class="container">

		<nav class="p411-crumb" aria-label="Breadcrumb">
			<a href="<?= base_url() ?>">Home</a>
			<span aria-hidden="true">/</span>
			<a href="<?= e_url($ld_url) ?>"><?= e($listing->listing_name) ?></a>
			<span aria-hidden="true">/</span>
			<span class="is-current">Claim this business</span>
		</nav>

		<div class="p411-auth">

			<!-- ================================================ FORM CARD -->
			<div class="p411-auth__card p411-form">
				<h1>Claim <?= e($listing->listing_name) ?></h1>
				<p class="p411-form__hint" style="margin-bottom:16px;">
					Is this your business? Tell us who you are and how we can check. Once a member of our team
					approves the claim, the listing moves into your account and you can edit it, add photos and see its enquiries.
				</p>

				<?php if (!empty($claimed_by_other)): ?>
					<div class="p411-note p411-note--info" role="status">
						This listing is already managed by an owner. If you believe that is wrong, send a claim anyway
						with as much proof as you can &mdash; we will look into it before changing anything.
					</div>
				<?php endif; ?>

				<?php if (!empty($error)): ?>
					<div class="p411-note p411-note--err" role="alert"><?= e($error) ?></div>
				<?php endif; ?>

				<form method="post" enctype="multipart/form-data" data-validate
					  action="<?= e_url(base_url('claim-listing/' . rawurlencode($listing->listing_slug))) ?>">

					<div class="form-group">
						<label for="claim_business_name">Business name <span class="p411-req">*</span></label>
						<input type="text" name="claim_business_name" id="claim_business_name" class="form-control"
							   maxlength="255" required value="<?= $cv('claim_business_name') ?>">
					</div>

					<div class="p411-form__row">
						<div class="form-group">
							<label for="enquiry_name">Owner / contact name <span class="p411-req">*</span></label>
							<input type="text" name="enquiry_name" id="enquiry_name" class="form-control" autocomplete="name"
								   minlength="2" maxlength="255" required value="<?= $cv('enquiry_name') ?>">
						</div>
						<div class="form-group">
							<label for="claim_role">Your role <span class="p411-req">*</span></label>
							<select name="claim_role" id="claim_role" class="form-control p411-select no-select2" required>
								<option value="">Choose&hellip;</option>
								<?php foreach ($roles as $key => $label): ?>
									<option value="<?= e_attr($key) ?>"<?= $picked_role === $key ? ' selected' : '' ?>><?= e($label) ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="p411-form__row">
						<div class="form-group">
							<label for="enquiry_email">Business email <span class="p411-req">*</span></label>
							<input type="email" name="enquiry_email" id="enquiry_email" class="form-control" autocomplete="email"
								   maxlength="255" required value="<?= $cv('enquiry_email') ?>">
						</div>
						<div class="form-group">
							<label for="enquiry_mobile">Phone <span class="p411-req">*</span></label>
							<input type="tel" name="enquiry_mobile" id="enquiry_mobile" class="form-control" autocomplete="tel"
								   minlength="7" maxlength="30" pattern="[0-9+().\s\-]+" title="Digits, spaces, + ( ) . and -"
								   required value="<?= $cv('enquiry_mobile') ?>">
						</div>
					</div>

					<fieldset class="form-group p411-claim__methods">
						<legend>How can we verify you? <span class="p411-req">*</span></legend>
						<?php foreach ($methods as $key => $label): ?>
							<label class="p411-claim__method">
								<input type="radio" name="claim_verification" value="<?= e_attr($key) ?>" required
									   <?= $picked_method === $key ? 'checked' : '' ?>>
								<span><?= e($label) ?></span>
							</label>
						<?php endforeach; ?>
					</fieldset>

					<div class="form-group">
						<label for="claim_document">Proof document <small>(optional unless you chose a document above)</small></label>
						<input type="file" name="claim_document" id="claim_document" class="form-control"
							   accept="image/jpeg,image/png,image/webp,application/pdf,.pdf">
						<p class="p411-form__hint" style="margin:6px 0 0;">
							A photo, scan or PDF up to 5 MB &mdash; business license, utility bill or EIN letter in the business&rsquo;s name.
						</p>
					</div>

					<div class="form-group">
						<label for="enquiry_message">Notes <small>(optional)</small></label>
						<textarea name="enquiry_message" id="enquiry_message" class="form-control" rows="4" maxlength="2000"
								  placeholder="Anything that helps us verify you quickly — e.g. the best time to call the business number."><?= e((string) ($old['enquiry_message'] ?? '')) ?></textarea>
					</div>

					<label class="p411-claim__confirm">
						<input type="checkbox" name="claim_confirm" value="1" required <?= !empty($old['claim_confirm']) ? 'checked' : '' ?>>
						<span>I confirm I am authorised to manage this business&rsquo;s listing, and the information above is true.</span>
					</label>

					<button type="submit" class="p411-btn p411-btn--block p411-btn--lg">Submit claim</button>
					<p class="p411-form__hint" style="margin:12px 0 0;">
						<a href="<?= e_url($ld_url) ?>">Cancel and go back to the listing</a>
					</p>
				</form>
			</div>

			<!-- ==================================================== ASIDE -->
			<aside class="p411-auth__side">
				<span class="p411-auth__eyebrow">Listing on file</span>
				<h2><?= e($listing->listing_name) ?></h2>
				<?php if (!empty($listing->listing_address)): ?>
					<p class="p411-auth__side-note" style="margin-bottom:6px;"><?= e($listing->listing_address) ?></p>
				<?php endif; ?>
				<?php if (!empty($listing->listing_mobile)): ?>
					<p class="p411-auth__side-note"><?= e($listing->listing_mobile) ?></p>
				<?php endif; ?>

				<span class="p411-auth__eyebrow" style="margin-top:18px;">What happens next</span>
				<ul>
					<li>We check your claim, usually within 1&ndash;2 business days</li>
					<li>You get an email when it&rsquo;s approved &mdash; or if we need more from you</li>
					<li>The listing moves into your account: edit it, add photos and hours</li>
					<li>Save, and the changes are live straight away</li>
				</ul>
				<p class="p411-auth__side-note">
					Follow your claim any time under My Listings &rarr; My claims.
				</p>
			</aside>

		</div>
	</div>
</section>
