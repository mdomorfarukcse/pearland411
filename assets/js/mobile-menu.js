/*
 * assets/js/mobile-menu.js — the off-canvas header menu (`.mob-me-all`).
 *
 * One implementation for both pages that render the theme's mobile menu:
 * views/header.php (every inner page) and views/index.php (the self-contained
 * homepage). It replaces two handlers that used to fight each other —
 * `custom.js` added `.mobmenu-show` and never removed it on a second press,
 * while an inline script in header.php toggled an inline `display` on
 * `.ud-lhs-s2`, the list inside the panel.
 *
 * What that combination produced, and what this fixes:
 *
 *   - Press, press again: the panel stayed open with its contents hidden,
 *     because only one of the two handlers toggled.
 *   - Widen the window past the burger's 992px breakpoint with the panel open:
 *     the burger disappeared, but the panel is `position: fixed` and stayed
 *     over the page with no way to close it. Reported as "broken image when
 *     pressing the burger menu then widening the view".
 *   - The burger is a <div>, so it could not be reached or fired from the
 *     keyboard.
 *
 * Plain DOM, no jQuery: this runs on the homepage too, and it must not depend
 * on load order relative to custom.js.
 */
(function () {
	'use strict';

	// The width at which style.css:26647 starts showing `.mob-me-ic`. Above it
	// the burger is not on screen, so an open panel is unreachable chrome.
	var BREAKPOINT = 992;

	function init() {
		var panel = document.querySelector('.mob-me-all');
		var opener = document.querySelector('.mob-me-ic');
		if (!panel || !opener) {
			return; // admin pages, and anything else without the theme header
		}

		var closer = panel.querySelector('.mob-me-clo');

		function isOpen() {
			return panel.classList.contains('mobmenu-show');
		}

		function open() {
			if (isOpen()) return;
			panel.classList.add('mobmenu-show');
			opener.setAttribute('aria-expanded', 'true');
			// The panel scrolls on its own; letting the page scroll behind it
			// is what makes a phone feel like it lost its place on close. The
			// class goes on <html> as well — see the note in mobile-menu.css.
			document.documentElement.classList.add('p411-menu-open');
			document.body.classList.add('p411-menu-open');
			// Move focus in so the close control is the next tab stop.
			if (closer && typeof closer.focus === 'function') {
				closer.focus();
			}
		}

		function close(refocus) {
			if (!isOpen()) return;
			panel.classList.remove('mobmenu-show');
			opener.setAttribute('aria-expanded', 'false');
			document.documentElement.classList.remove('p411-menu-open');
			document.body.classList.remove('p411-menu-open');
			if (refocus && typeof opener.focus === 'function') {
				opener.focus();
			}
		}

		function toggle() {
			if (isOpen()) close(true); else open();
		}

		/*
		 * No backdrop, deliberately. `.mob-me-all` is `width: 100%`, so an open
		 * panel already covers the whole viewport and there is no "outside" to
		 * dim. A scrim added here did have one visible effect: it sat over
		 * `.mob-me-ic` and swallowed the press that was meant to close the
		 * panel again. Escape, the close button, following a link and widening
		 * the window are the four ways out.
		 */

		opener.addEventListener('click', function (event) {
			event.preventDefault();
			toggle();
		});

		// `.mob-me-ic` is a <div role="button" tabindex="0">, so Enter and
		// Space have to be wired by hand.
		opener.addEventListener('keydown', function (event) {
			if (event.key === 'Enter' || event.key === ' ' || event.key === 'Spacebar') {
				event.preventDefault();
				toggle();
			}
		});

		if (closer) {
			closer.addEventListener('click', function (event) {
				event.preventDefault();
				close(true);
			});
			closer.addEventListener('keydown', function (event) {
				if (event.key === 'Enter' || event.key === ' ' || event.key === 'Spacebar') {
					event.preventDefault();
					close(true);
				}
			});
		}

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' || event.key === 'Esc') {
				close(true);
			}
		});

		// Following a link inside the panel navigates away; on a same-page
		// anchor it would not, and the panel would sit there over the target.
		panel.addEventListener('click', function (event) {
			var link = event.target.closest ? event.target.closest('a[href]') : null;
			if (link && panel.contains(link)) {
				close(false);
			}
		});

		// The reported bug: widen the window and the burger goes away while the
		// panel stays. Nothing else can close it at that width, so close it here.
		var onResize = function () {
			if (window.innerWidth > BREAKPOINT) {
				close(false);
			}
		};
		if (window.matchMedia) {
			var mq = window.matchMedia('(min-width: ' + (BREAKPOINT + 1) + 'px)');
			// Safari < 14 has no addEventListener on MediaQueryList.
			if (mq.addEventListener) {
				mq.addEventListener('change', onResize);
			} else if (mq.addListener) {
				mq.addListener(onResize);
			}
		}
		window.addEventListener('resize', onResize);

		// The state can survive a bfcache restore; the class cannot be trusted
		// to match what the visitor sees.
		window.addEventListener('pageshow', onResize);
		onResize();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
