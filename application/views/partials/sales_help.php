<?php
/**
 * "Would you rather we did this for you?" — the human way to buy.
 *
 *   <?php include APPPATH . 'views/partials/sales_help.php'; ?>
 *
 * A large share of the people buying a listing or a banner are not going to
 * complete a checkout: they want to talk to somebody. This panel gives them
 * three ways to do that without leaving the page — call, email, or leave a
 * number for a callback — and lets them pick the department they need (from
 * enquiry_departments(), the same list the signup form offers).
 *
 * The callback form posts to /contact-submit, the same action the contact page
 * uses; `return_to` brings them back here rather than to /contact-us. Either an
 * email address or a phone number is enough — this form asks for the phone.
 *
 * `$help_topic` is what decides which inbox the enquiry lands in. This panel
 * sells packages, so it files as `package` and shows up under "Marketing
 * packages" in admin rather than mixed in with general contact-form messages —
 * which is what used to happen, and the reason a callback request could sit
 * unread among website questions.
 *
 * Variables (all optional):
 *   $help_title    (string) heading override
 *   $help_lede     (string) intro line override
 *   $help_return   (string) `return_to` value; must be whitelisted in
 *                  User::contact_submit(). Default 'pricing-plan'.
 *   $help_topic    (string) enquiry type; whitelisted in User::contact_submit().
 *                  Default 'package'.
 *
 * Like partials/ad_slot.php this ships its own CSS and emits it once per
 * request, because it is included by views that do not all load the page kit.
 */

$help_title  = isset($help_title)  ? (string) $help_title  : 'Would you rather we set it up for you?';
$help_lede   = isset($help_lede)   ? (string) $help_lede   : 'You do not have to order online. Call us and one of our sales representatives will take your details over the phone, pick the right package with you and get your business or your ad live — no forms, no card details typed into a website.';
$help_return = isset($help_return) ? (string) $help_return : 'pricing-plan';
$help_topic  = isset($help_topic)  ? (string) $help_topic  : 'package';

$help_phone = trim((string) site_setting('contact_phone', '281-482-2031'));
$help_email = trim((string) site_setting('contact_email', 'sales@pearland411.com'));
$help_tel   = preg_replace('/[^0-9+]/', '', $help_phone);
$help_depts = function_exists('enquiry_departments') ? enquiry_departments() : array();

// CI loads partials in the caller's scope, so the session comes off the
// instance rather than `$this` — this file is included from plain views too.
$__ci     = get_instance();
$help_ok  = $__ci->session->flashdata('contact_success');
$help_err = $__ci->session->flashdata('contact_error');

$help_css = !defined('PEARLAND411_SALES_HELP_CSS');
if ($help_css) define('PEARLAND411_SALES_HELP_CSS', 1);
?>

<?php if ($help_css): ?>
<style>
	.p411-help {
		background: #FFFFFF;
		border: 1px solid #E4E6EC;
		border-radius: 16px;
		box-shadow: 0 1px 2px rgba(31, 36, 48, .05), 0 10px 30px rgba(31, 36, 48, .07);
		overflow: hidden;
		display: grid;
		grid-template-columns: 1.05fr .95fr;
		margin: 30px 0;
		color: #1F2430;
		font-family: 'Open Sans', Arial, sans-serif;
		text-align: left;
	}

	/* ---- left: talk to a person --------------------------------------- */
	.p411-help__call {
		background: linear-gradient(150deg, #12143A 0%, #20245B 58%, #2C3178 100%);
		color: #fff;
		padding: 30px 32px 32px;
		/* The form column is the taller of the two and the grid stretches both,
		   so the surplus has to go somewhere. Centring spreads it evenly above
		   and below the content, which reads as a deliberately set panel.
		   Pushing the contact block to the foot instead (the first attempt at
		   this) only moved the problem: it left one ~250px hole between the
		   lede and the phone number, with the pill floating at the bottom. */
		display: flex;
		flex-direction: column;
		justify-content: center;
	}
	/* Both are inline-flex pills; without this the flex container stretches them
	   to the full column width. The 900px stacked layout below sets an explicit
	   width on the phone pill, so it still wins there. */
	.p411-help__phone,
	.p411-help__mail { align-self: flex-start; }
	.p411-help__eyebrow {
		display: inline-block;
		font-size: 11px;
		font-weight: 700;
		letter-spacing: .14em;
		text-transform: uppercase;
		color: #A9E060;
		margin-bottom: 10px;
	}
	.p411-help__call h2 {
		margin: 0 0 10px;
		font-family: 'Poppins', 'Open Sans', sans-serif;
		font-size: 25px;
		font-weight: 700;
		line-height: 1.25;
		color: #fff;
	}
	.p411-help__call p {
		margin: 0 0 20px;
		font-size: 14.5px;
		line-height: 1.6;
		color: rgba(255, 255, 255, .84);
	}

	/* 22px, not 25. At 25 the number matched the h2 above it, and as the only
	   saturated block of colour on a navy ground it won that tie — so the eye
	   landed on a green blob before reading what it was for. The heading leads,
	   the number answers it. `margin-top` is the deliberate gap after the lede;
	   the hours then tuck under the pill so the three lines read as one contact
	   block rather than three stray items. */
	/* An outline pill, not a solid one. The form column's "Request a call back"
	   is a solid green button, and a second solid green block beside it made the
	   two compete for the same job — the panel read as two primary actions with
	   no order between them. Outlined, the number keeps its full tap target and
	   its prominence but clearly sits one step behind the button, and it stops
	   out-shouting the heading it is supposed to be answering. Fills solid on
	   hover, so the affordance is still obvious. */
	.p411-help__phone {
		display: inline-flex;
		align-items: center;
		gap: 12px;
		margin-top: 26px;
		padding: 11px 26px 11px 18px;
		border-radius: 999px;
		background: transparent;
		border: 2px solid rgba(143, 203, 70, .55);
		color: #fff;
		font-family: 'Poppins', 'Open Sans', sans-serif;
		font-size: 24px;
		font-weight: 700;
		letter-spacing: .01em;
		text-decoration: none;
		transition: background .16s ease, border-color .16s ease, color .16s ease;
	}
	.p411-help__phone:hover,
	.p411-help__phone:focus {
		background: #79B82A;
		border-color: #79B82A;
		color: #fff;
		text-decoration: none;
	}
	/* The markup sets width/height 30 inline; at this size that read as an icon
	   with a number beside it rather than a number with an icon. The glyph is
	   drawn with stroke="currentColor", so `color` is what tints it. */
	.p411-help__phone svg { flex: none; width: 26px; height: 26px; color: #8FCB46; }
	.p411-help__phone:hover svg,
	.p411-help__phone:focus svg { color: #fff; }

	/* Scoped to .p411-help__call deliberately, to outrank `.p411-help__call p`
	   above (0,1,1). A bare `.p411-help__hours` is 0,1,0 and loses that fight
	   whatever order it sits in, so every margin ever set here was silently
	   discarded and the line rendered hard against the pill with margin-top 0.
	   The same rule was also giving it a 20px BOTTOM margin, which stacked with
	   the email link's own 22px top margin in a flex column — 42px where 22 was
	   intended. Setting all four sides here fixes both ends. */
	.p411-help__call .p411-help__hours {
		margin: 18px 0 0;
		font-size: 13px;
		color: rgba(255, 255, 255, .7);
	}
	.p411-help__hours b { color: #fff; font-weight: 600; }

	.p411-help__mail {
		display: inline-flex;
		align-items: center;
		gap: 8px;
		margin-top: 22px;
		font-size: 14px;
		font-weight: 600;
		color: #A9E060;
		text-decoration: none;
		word-break: break-all;
	}
	.p411-help__mail:hover { color: #fff; text-decoration: underline; }

	/* ---- right: leave us your number ---------------------------------- */
	.p411-help__form { padding: 28px 32px 30px; }
	.p411-help__form h3 {
		margin: 0 0 4px;
		font-family: 'Poppins', 'Open Sans', sans-serif;
		font-size: 18px;
		font-weight: 700;
		color: #20245B;
	}
	.p411-help__form > p {
		margin: 0 0 16px;
		font-size: 13.5px;
		line-height: 1.55;
		color: #5A6472;
	}
	.p411-help__row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
	.p411-help__field { margin-bottom: 12px; }
	.p411-help__field label {
		display: block;
		margin: 0 0 5px;
		font-size: 12.5px;
		font-weight: 600;
		color: #5A6472;
	}
	.p411-help__field input,
	.p411-help__field select,
	.p411-help__field textarea {
		display: block;
		width: 100%;
		min-height: 46px;
		padding: 11px 13px;
		border: 1.5px solid #E4E6EC;
		border-radius: 8px;
		background: #fff;
		font-family: inherit;
		font-size: 15px;
		color: #1F2430;
	}
	.p411-help__field textarea { min-height: 74px; resize: vertical; }
	.p411-help__field input:focus,
	.p411-help__field select:focus,
	.p411-help__field textarea:focus {
		border-color: #79B82A !important;
		box-shadow: 0 0 0 3px rgba(121, 184, 42, .22) !important;
		outline: none;
	}
	.p411-help__send {
		display: block;
		width: 100%;
		padding: 14px 20px;
		border: 0;
		border-radius: 999px;
		background: #79B82A;
		color: #fff;
		font-family: 'Poppins', 'Open Sans', sans-serif;
		font-size: 16px;
		font-weight: 700;
		cursor: pointer;
		transition: background .16s ease;
	}
	.p411-help__send:hover { background: #5E9220; }
	.p411-help__small { margin: 10px 0 0; font-size: 12px; color: #8B93A1; }

	.p411-help__note {
		border-radius: 8px;
		padding: 10px 13px;
		font-size: 13.5px;
		margin-bottom: 14px;
	}
	.p411-help__note--ok  { background: #F1F7E5; color: #3E6B15; border: 1px solid #CFE0AE; }
	.p411-help__note--err { background: #FBF1F5; color: #6D263D; border: 1px solid #E2CBD5; }

	@media (max-width: 900px) {
		.p411-help { grid-template-columns: 1fr; }
		.p411-help__call { padding: 26px 22px 28px; }
		.p411-help__form { padding: 24px 22px 26px; }
		.p411-help__phone { font-size: 22px; width: 100%; justify-content: center; padding: 14px 18px; }
		.p411-help__row { grid-template-columns: 1fr; gap: 0; }
	}
</style>
<?php endif; ?>

<section class="p411-help" id="talk-to-us">
	<div class="p411-help__call">
		<span class="p411-help__eyebrow">Need a hand?</span>
		<h2><?= e($help_title) ?></h2>
		<p><?= e($help_lede) ?></p>

		<?php if ($help_phone !== ''): ?>
			<a class="p411-help__phone" href="tel:<?= e_attr($help_tel) ?>">
				<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>
				</svg>
				<?= e($help_phone) ?>
			</a>
			<p class="p411-help__hours">
				<b>Mon&ndash;Fri</b> 9:00&nbsp;AM&ndash;6:00&nbsp;PM &nbsp;&middot;&nbsp;
				<b>Sat</b> 10:00&nbsp;AM&ndash;4:00&nbsp;PM
			</p>
		<?php endif; ?>

		<?php if ($help_email !== ''): ?>
			<a class="p411-help__mail" href="mailto:<?= e_attr($help_email) ?>">
				<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/>
				</svg>
				<?= e($help_email) ?>
			</a>
		<?php endif; ?>
	</div>

	<div class="p411-help__form">
		<h3>Or leave your number</h3>
		<p>Fill in these boxes and we will call you. You do not need an account, and there is nothing to pay to talk to us.</p>

		<?php if (!empty($help_ok)): ?>
			<div class="p411-help__note p411-help__note--ok"><?= e($help_ok) ?></div>
		<?php endif; ?>
		<?php if (!empty($help_err)): ?>
			<div class="p411-help__note p411-help__note--err"><?= e($help_err) ?></div>
		<?php endif; ?>

		<form method="post" action="<?= base_url('contact-submit') ?>">
			<input type="hidden" name="return_to" value="<?= e_attr($help_return) ?>">
			<input type="hidden" name="topic" value="<?= e_attr($help_topic) ?>">
			<input type="hidden" name="subject" value="Callback request">

			<div class="p411-help__field">
				<label for="help_name">Your name</label>
				<input id="help_name" type="text" name="fullName" required autocomplete="name">
			</div>

			<div class="p411-help__row">
				<div class="p411-help__field">
					<label for="help_phone">Phone number</label>
					<input id="help_phone" type="tel" name="phone" required autocomplete="tel"
						   pattern="[0-9+()\- ]{7,20}" title="Enter a phone number we can call you on">
				</div>
				<div class="p411-help__field">
					<label for="help_email">Email <span style="font-weight:400;">(optional)</span></label>
					<input id="help_email" type="email" name="email" autocomplete="email">
				</div>
			</div>

			<?php if (!empty($help_depts)): ?>
				<div class="p411-help__field">
					<label for="help_dept">Who do you need?</label>
					<select id="help_dept" name="department">
						<?php foreach ($help_depts as $department): ?>
							<option value="<?= e_attr($department) ?>"><?= e($department) ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>

			<div class="p411-help__field">
				<label for="help_msg">What would you like to do?</label>
				<textarea id="help_msg" name="message" rows="3"
						  placeholder="For example: I would like to add my business, or I want a banner ad."
						  required minlength="10">I would like help adding my business to Pearland411.</textarea>
			</div>

			<?= spam_form_fields() ?>
			<button type="submit" class="p411-help__send">Request a call back</button>
			<p class="p411-help__small">We only use your details to answer you. Nothing is charged until you say yes.</p>
		</form>
	</div>
</section>

<?php
unset($help_title, $help_lede, $help_return, $help_topic, $help_phone, $help_email,
	$help_tel, $help_depts, $help_ok, $help_err, $help_css, $__ci);
?>
