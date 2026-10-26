<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Spam protection for the anonymous public forms.
 *
 * The contact form was sending the client 700+ messages a day, from names
 * like "Новый перевод 52012 руб. …", and every one of them also mailed a
 * "We received your message" copy to whatever address the bot typed — the
 * site delivering the spammer's text to their target, from pearland411.com.
 * A per-IP rate limit does nothing against a botnet, so each form that saves
 * a row or sends an email now has three checks, in User::spamRefusal():
 *
 *   1. a honeypot — a field no person sees and simple bots fill in. Tripping
 *      it "succeeds" without saving or sending anything, so the bot learns
 *      nothing. Costs nothing, and works with no keys configured;
 *   2. a name filter — a link, or Cyrillic / CJK script, in a name field
 *      (switch: spam_name_filter_enabled at /admin/setting);
 *   3. Google reCAPTCHA v2 (libraries/Recaptcha.php), when keys are in .env.
 *
 * A form opts in with one call in its markup, just above the submit button:
 *
 *   <?= spam_form_fields() ?>
 *
 * and its handler calls $this->spamRefusal() after the rate limit.
 */

if (!function_exists('spam_form_fields')) {
	/**
	 * The honeypot, plus the reCAPTCHA box when this visitor needs one. The
	 * box stays empty until someone starts on the form; assets/js/recaptcha.js
	 * then loads Google's script and draws the checkbox in it, so the
	 * Help & Support slide-out on every page costs no download until used.
	 */
	function spam_form_fields()
	{
		// Not type="hidden" — a bot skips those; it fills in anything that
		// looks like a real field. Moved off-screen rather than display:none
		// for the same reason. No id: several forms can share a page.
		$html = '<div aria-hidden="true" style="position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;">'
			. '<label>Leave this field empty <input type="text" name="website_url" tabindex="-1" autocomplete="off" value=""></label>'
			. '</div>';

		$CI =& get_instance();
		$CI->load->library('recaptcha');
		if (!$CI->recaptcha->required()) {
			return $html;
		}

		// clear:both — the theme floats .form-group, and without it the box
		// rides up beside the fields instead of sitting above the button.
		$html .= '<div class="p411-captcha" style="clear:both;">'
			. '<div class="p411-recaptcha" data-sitekey="' . e_attr($CI->recaptcha->site_key()) . '"></div>'
			. '<p class="p411-captcha__note" role="alert" hidden style="margin:6px 0 0;color:#c0392b;font-size:13px;"></p>'
			. '<noscript><p style="margin:6px 0 0;font-size:13px;">Please turn on JavaScript to send this form, or call us.</p></noscript>'
			. '</div>';

		static $script_done = FALSE;
		if (!$script_done) {
			$script_done = TRUE;
			$html .= '<script src="' . e_attr(asset_css('assets/js/recaptcha.js')) . '" defer></script>';
		}
		return $html;
	}
}

if (!function_exists('spam_trap_tripped')) {
	/** TRUE when the honeypot came back filled in: not a person. */
	function spam_trap_tripped()
	{
		$CI =& get_instance();
		return trim((string) $CI->input->post('website_url')) !== '';
	}
}

if (!function_exists('spam_name_refusal')) {
	/**
	 * Why a name field cannot be accepted, or NULL when it can.
	 *
	 * A name is where this spam puts its payload — it is the part that lands
	 * in the subject line and in the greeting of the email we send back. A
	 * Pearland business's customer does not type a web address as their name,
	 * nor write it in Cyrillic or Chinese characters.
	 *
	 * @param  mixed $name
	 * @return string|null
	 */
	function spam_name_refusal($name)
	{
		if (site_setting('spam_name_filter_enabled', '1') === '0') {
			return NULL;
		}
		$name = trim((string) $name);
		if ($name === '') {
			return NULL;
		}

		if (preg_match('~(?:https?:|www\.|://|\b[a-z0-9-]+\.(?:com|net|org|info|biz|ru|su|ua|cn|xyz|top|site|online|link|click|shop|store|live|life|pro|io|me|cc|tk|ly|ws)\b)~iu', $name)) {
			return 'Please type your name without a web address.';
		}
		if (preg_match('/[\p{Cyrillic}\p{Han}\p{Hiragana}\p{Katakana}\p{Hangul}]/u', $name)) {
			return 'Please type your name in English (Latin) letters.';
		}
		if (mb_strlen($name) > 100) {
			return 'That name is too long (100 characters maximum).';
		}
		return NULL;
	}
}
