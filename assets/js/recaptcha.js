/*
 * Google reCAPTCHA v2 ("I'm not a robot") for the public forms.
 *
 * spam_form_fields() (application/helpers/spam_helper.php) prints an empty
 * `.p411-recaptcha` box in each protected form and this file once per page.
 * Google's script is fetched, and the checkbox drawn, as soon as one of those
 * forms comes on screen (within 200px), so a visible form shows it without a
 * click. Waiting for a click looked like a missing checkbox. The Help & Support
 * slide-out is parked off the right edge of every page until opened, so it
 * does not count as on screen, and pages where nobody opens it never fetch
 * Google's script for it.
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
		fresh.style.minHeight = box.style.minHeight;
		box.parentNode.replaceChild(fresh, box);
		draw(fresh);
	}

	// Draw each box once its form is on screen — the form, not the box: the
	// box sits at the foot of the form, below the fold while the fields are
	// already in view. Vertical margin only: the slide-out sits off to the
	// right and must not count until it slides in.
	function watch() {
		var boxes = document.querySelectorAll('.p411-recaptcha');
		if (!('IntersectionObserver' in window)) {
			Array.prototype.forEach.call(boxes, load);
			return;
		}
		var seen = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) {
				if (!en.isIntersecting) return;
				seen.unobserve(en.target);
				var box = boxIn(en.target) || en.target;
				load(box);
			});
		}, { rootMargin: '200px 0px' });
		Array.prototype.forEach.call(boxes, function (box) {
			seen.observe(box.closest ? (box.closest('form') || box) : box);
		});
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', watch);
	} else {
		watch();
	}

	// And on first use, should the observer not have fired (a form shown
	// some other way).
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
