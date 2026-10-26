/*
 * Google reCAPTCHA v2 ("I'm not a robot") for the public forms.
 *
 * spam_form_fields() (application/helpers/spam_helper.php) prints an empty
 * `.p411-recaptcha` box in each protected form and this file once per page.
 * Google's script is fetched only when someone starts on one of those forms —
 * focus or a tap anywhere inside it — and the checkbox is drawn then. The
 * Help & Support slide-out is on every page; loading Google's script up front
 * would have added it to every page view for a form most visitors never open.
 *
 * A submit with the box unticked is stopped here, before the post, so nobody
 * loses what they typed to a round trip the server would refuse anyway. This
 * listens on the document in the capture phase, ahead of every listener on
 * the form itself (form-validate.js, and the forms that send over XHR), and
 * leaves a form with invalid fields to form-validate.js to report first.
 *
 * The two forms that send over XHR use window.p411Recaptcha:
 *   token(form)   the solved response ('' if none) — new FormData(form) has
 *                 it already, as `g-recaptcha-response`
 *   reset(form)   untick after a send, so the next one is checked afresh
 *   remount(form) redraw after the form is moved in the DOM (moving the
 *                 widget's iframe reloads it and breaks it)
 */
(function () {
	'use strict';

	var API = 'https://www.google.com/recaptcha/api.js?onload=p411RecaptchaReady&render=explicit';
	var state = 'idle';            // idle | loading | ready | failed
	var waiting = [];              // boxes to draw once the API is ready

	function boxIn(form) {
		return form && form.querySelector ? form.querySelector('.p411-recaptcha') : null;
	}

	function note(box, text) {
		var n = box.parentNode.querySelector('.p411-captcha__note');
		if (!n) return;
		n.textContent = text || '';
		n.hidden = !text;
	}

	function draw(box) {
		if (box.getAttribute('data-widget') !== null || !document.body.contains(box)) return;
		// The full widget is 304px wide; a narrower form (the slide-out on a
		// phone) gets the compact one rather than a box spilling out of it.
		var compact = box.parentNode.clientWidth > 0 && box.parentNode.clientWidth < 304;
		var id = window.grecaptcha.render(box, {
			sitekey: box.getAttribute('data-sitekey'),
			size: compact ? 'compact' : 'normal',
			callback: function () { note(box, ''); }
		});
		box.setAttribute('data-widget', String(id));
		box.parentNode.style.margin = '4px 0 14px';
	}

	function load(box) {
		if (state === 'ready') { draw(box); return; }
		if (state === 'failed') {
			note(box, 'The spam check could not load. Please check your connection and reload the page, or call us.');
			return;
		}
		if (waiting.indexOf(box) === -1) waiting.push(box);
		if (state === 'loading') return;

		state = 'loading';
		window.p411RecaptchaReady = function () {
			state = 'ready';
			waiting.splice(0).forEach(draw);
		};
		var s = document.createElement('script');
		s.src = API;
		s.async = true;
		s.defer = true;
		s.onerror = function () {
			state = 'failed';
			waiting.splice(0).forEach(load);
		};
		document.head.appendChild(s);
	}

	function widget(form) {
		var box = boxIn(form);
		var id = box ? box.getAttribute('data-widget') : null;
		return (id === null || !window.grecaptcha) ? null : Number(id);
	}

	function token(form) {
		var id = widget(form);
		return id === null ? '' : (window.grecaptcha.getResponse(id) || '');
	}

	function reset(form) {
		var id = widget(form);
		if (id !== null) window.grecaptcha.reset(id);
	}

	function remount(form) {
		var box = boxIn(form);
		if (!box || box.getAttribute('data-widget') === null) return;
		var fresh = document.createElement('div');
		fresh.className = box.className;
		fresh.setAttribute('data-sitekey', box.getAttribute('data-sitekey'));
		box.parentNode.replaceChild(fresh, box);
		draw(fresh);
	}

	function wake(e) {
		var form = e.target && e.target.closest ? e.target.closest('form') : null;
		var box = boxIn(form);
		if (box) load(box);
	}
	document.addEventListener('focusin', wake);
	document.addEventListener('pointerdown', wake);

	document.addEventListener('submit', function (e) {
		var form = e.target;
		var box = boxIn(form);
		if (!box || token(form)) return;
		// Bad fields first: form-validate.js marks them, and the box can be
		// ticked once they are right.
		if (typeof form.checkValidity === 'function' && !form.checkValidity()) return;

		e.preventDefault();
		e.stopPropagation();
		load(box);
		if (state !== 'failed') note(box, 'Please tick the “I’m not a robot” box, then send it again.');
		if (typeof box.scrollIntoView === 'function') box.scrollIntoView({ block: 'center', behavior: 'smooth' });
	}, true);

	window.p411Recaptcha = { token: token, reset: reset, remount: remount };
})();
