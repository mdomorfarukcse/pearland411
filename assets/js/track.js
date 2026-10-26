/**
 * Click counters for the analytics pages (checklist §10).
 *
 * Markup-first, like form-validate.js: an element inside
 * [data-track-listing="<listing id>"] (or [data-track-coupon="<id>"]) that
 * carries data-track="<event>" is counted when clicked — phone, email,
 * website, map, whatsapp for a listing; click for a coupon. Nothing else on
 * the page is touched, and the link still does exactly what it did.
 *
 * Sent with fetch keepalive (so it survives the navigation the click starts),
 * with the CSRF token from the <meta> tags the CSRF hook adds to every page —
 * the homepage does not load csrf.js, so this reads them itself. Failures are
 * ignored: a counter must never get in the way of a phone call.
 */
(function () {
	'use strict';

	// The site root, from this script's own URL — the site may not live at /.
	var self = document.currentScript && document.currentScript.src;
	var endpoint = self ? self.replace(/assets\/js\/track\.js.*$/, '') + 'track' : '/track';

	function meta(name) {
		var el = document.querySelector('meta[name="' + name + '"]');
		return el ? el.getAttribute('content') : '';
	}

	function send(type, id, event) {
		try {
			var body = new FormData();
			body.append('type', type);
			body.append('id', id);
			body.append('event', event);
			var tokenName = meta('csrf-token-name');
			if (tokenName) body.append(tokenName, meta('csrf-token'));
			if (window.fetch) {
				window.fetch(endpoint, { method: 'POST', body: body, credentials: 'same-origin', keepalive: true })
					['catch'](function () {});
			}
		} catch (e) { /* never block the click */ }
	}

	document.addEventListener('click', function (ev) {
		var el = ev.target && ev.target.closest ? ev.target.closest('[data-track]') : null;
		if (!el) return;
		var scope = el.closest('[data-track-listing],[data-track-coupon]');
		if (!scope) return;
		if (scope.hasAttribute('data-track-listing')) {
			send('listing', scope.getAttribute('data-track-listing'), el.getAttribute('data-track'));
		} else {
			send('coupon', scope.getAttribute('data-track-coupon'), el.getAttribute('data-track'));
		}
	}, true);
})();
