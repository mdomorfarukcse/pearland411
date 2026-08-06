/*
 * Live validation for the public forms.
 *
 * Every form on the site relied on the browser's own `required` / type="email"
 * handling, which says nothing until submit and says it in a tooltip that
 * vanishes. A phone box was `type="tel"`, which restricts nothing, so letters
 * went in and only the server rejected them — after a round trip that also
 * threw away what had been typed.
 *
 * This attaches to any `<form data-validate>` and:
 *   - checks each field as it is edited (after first blur, so it does not
 *     shout at someone halfway through typing their email),
 *   - writes the reason under the field and marks the control,
 *   - blocks submit while anything is invalid and focuses the first offender.
 *
 * Rules come from the markup — `required`, `type`, `minlength`, `maxlength`,
 * `pattern`, plus `data-match` for confirm-password fields — so a form opts in
 * by adding the attribute, and there is no per-form JavaScript to keep in step.
 *
 * The server validates independently. This is the courtesy layer; it is not
 * the guard.
 */
(function () {
	'use strict';

	var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
	var PHONE_RE = /^[0-9+\-\s()]{7,20}$/;

	function label(field) {
		var id = field.getAttribute('id');
		if (id) {
			var lab = document.querySelector('label[for="' + id + '"]');
			if (lab) return lab.textContent.replace(/\*/g, '').trim();
		}
		return (field.getAttribute('placeholder') || 'This field').replace(/\*/g, '').trim();
	}

	// Returns an error string, or '' when the field is acceptable.
	function problem(field) {
		var value = (field.value || '').trim();
		var name = label(field);

		if (field.hasAttribute('required') && value === '') {
			return name + ' is required.';
		}
		if (value === '') return '';   // optional and empty is fine

		var type = (field.getAttribute('type') || '').toLowerCase();

		if (type === 'email' && !EMAIL_RE.test(value)) {
			return 'Enter a valid email address, like name@example.com.';
		}
		if (type === 'tel' && !PHONE_RE.test(value)) {
			return 'Use digits, spaces, brackets, + or - only (7 to 20 characters).';
		}
		if (type === 'url' && !/^https?:\/\/[^\s.]+\.[^\s]{2,}$/i.test(value)) {
			return 'Enter a full web address, starting with http:// or https://.';
		}

		var min = parseInt(field.getAttribute('minlength'), 10);
		if (min && value.length < min) {
			return name + ' must be at least ' + min + ' characters.';
		}
		var max = parseInt(field.getAttribute('maxlength'), 10);
		if (max && value.length > max) {
			return name + ' must be ' + max + ' characters or fewer.';
		}

		var pattern = field.getAttribute('pattern');
		if (pattern) {
			try {
				if (!new RegExp('^(?:' + pattern + ')$').test(value)) {
					return field.getAttribute('title') || ('Please match the requested format for ' + name + '.');
				}
			} catch (e) { /* an unparseable pattern is the markup's problem, not the visitor's */ }
		}

		var matchSel = field.getAttribute('data-match');
		if (matchSel) {
			var other = field.form ? field.form.querySelector(matchSel) : null;
			if (other && other.value !== field.value) {
				return 'These do not match.';
			}
		}

		return '';
	}

	function noteFor(field) {
		var note = field.parentNode.querySelector('.p411-fv-note');
		if (!note) {
			note = document.createElement('small');
			note.className = 'p411-fv-note';
			note.setAttribute('role', 'alert');
			note.style.cssText = 'display:block;color:#c0392b;margin-top:4px;font-size:0.85em;line-height:1.4;';
			field.parentNode.appendChild(note);
		}
		return note;
	}

	function show(field, message) {
		var note = noteFor(field);
		if (message) {
			note.textContent = message;
			note.style.display = 'block';
			field.setAttribute('aria-invalid', 'true');
			field.style.borderColor = '#c0392b';
		} else {
			note.textContent = '';
			note.style.display = 'none';
			field.removeAttribute('aria-invalid');
			field.style.borderColor = '';
		}
	}

	function fields(form) {
		return Array.prototype.filter.call(
			form.querySelectorAll('input, select, textarea'),
			function (f) {
				return !f.disabled &&
					f.type !== 'hidden' && f.type !== 'submit' &&
					f.type !== 'button' && f.type !== 'file';
			}
		);
	}

	function enhance(form) {
		if (form.dataset.p411Validate === '1') return;
		form.dataset.p411Validate = '1';

		// The browser's own bubbles would fire first and hide ours.
		form.setAttribute('novalidate', 'novalidate');

		fields(form).forEach(function (field) {
			var touched = false;

			field.addEventListener('blur', function () {
				touched = true;
				show(field, problem(field));
			});

			field.addEventListener('input', function () {
				// Only correct in real time once they've left the field once,
				// so the message doesn't appear while a valid value is still
				// being typed. Clearing an existing error is always welcome.
				if (touched || field.getAttribute('aria-invalid')) {
					show(field, problem(field));
				}
			});
		});

		form.addEventListener('submit', function (event) {
			var first = null;

			fields(form).forEach(function (field) {
				var msg = problem(field);
				show(field, msg);
				if (msg && !first) first = field;
			});

			if (first) {
				event.preventDefault();
				event.stopPropagation();
				if (typeof first.scrollIntoView === 'function') {
					first.scrollIntoView({ block: 'center', behavior: 'smooth' });
				}
				first.focus();
			}
		// Capture phase: this must run before any other submit handler on the
		// form (the directory enquiry form posts over AJAX in its own listener,
		// which is registered first and would otherwise send an invalid form).
		}, true);
	}

	function init() {
		var forms = document.querySelectorAll('form[data-validate]');
		for (var i = 0; i < forms.length; i++) enhance(forms[i]);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
