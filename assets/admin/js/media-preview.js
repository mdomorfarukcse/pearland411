/*
 * Admin media widgets: lightbox, live file preview, live video preview.
 *
 * The edit screens showed uploaded images as bare 80px <img> tags with no way
 * to see them full size, no preview of what you had just picked, and — on the
 * listing form — no logo at all. This is markup-driven so a page opts in with
 * attributes instead of its own script:
 *
 *   <input type="file" data-preview="#target">
 *       Renders the chosen file(s) into #target as thumbnails, with name and
 *       size, replacing whatever was there. Multiple files are all shown.
 *
 *   <img data-lightbox src="..." >
 *       Click (or Enter/Space) to open full size in an overlay. Also applied
 *       automatically to any <img> inside [data-media-grid].
 *
 *   <textarea data-video-preview="#target">
 *       Parses YouTube URLs — one per line or comma separated — and renders a
 *       player for each, updating as you type.
 *
 * No dependencies: the admin pages load jQuery, Bootstrap and two select
 * libraries already, and none of them needs to be involved in this.
 */
(function () {
	'use strict';

	/* ---------------------------------------------------------------- lightbox */

	var overlay = null;

	function buildOverlay() {
		if (overlay) return overlay;
		overlay = document.createElement('div');
		overlay.className = 'p411-lb';
		overlay.setAttribute('role', 'dialog');
		overlay.setAttribute('aria-modal', 'true');
		overlay.setAttribute('aria-label', 'Image preview');
		overlay.innerHTML =
			'<button type="button" class="p411-lb__close" aria-label="Close preview">&times;</button>' +
			'<img class="p411-lb__img" alt="">' +
			'<p class="p411-lb__cap"></p>';
		overlay.addEventListener('click', function (e) {
			if (e.target === overlay || e.target.classList.contains('p411-lb__close')) closeLb();
		});
		document.body.appendChild(overlay);
		return overlay;
	}

	function openLb(src, caption) {
		var el = buildOverlay();
		el.querySelector('.p411-lb__img').src = src;
		el.querySelector('.p411-lb__img').alt = caption || '';
		el.querySelector('.p411-lb__cap').textContent = caption || '';
		el.classList.add('is-open');
		document.body.style.overflow = 'hidden';
		el.querySelector('.p411-lb__close').focus();
	}

	function closeLb() {
		if (!overlay) return;
		overlay.classList.remove('is-open');
		overlay.querySelector('.p411-lb__img').src = '';
		document.body.style.overflow = '';
	}

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') closeLb();
	});

	// Delegated, so images added by a preview are covered without re-binding.
	document.addEventListener('click', function (e) {
		var img = e.target.closest ? e.target.closest('img[data-lightbox], [data-media-grid] img') : null;
		if (!img || !img.src) return;
		e.preventDefault();
		openLb(img.getAttribute('data-full') || img.src, img.alt || '');
	});

	document.addEventListener('keydown', function (e) {
		if (e.key !== 'Enter' && e.key !== ' ') return;
		var img = document.activeElement;
		if (!img || img.tagName !== 'IMG') return;
		if (!img.matches('img[data-lightbox], [data-media-grid] img')) return;
		e.preventDefault();
		openLb(img.getAttribute('data-full') || img.src, img.alt || '');
	});

	/* ------------------------------------------------------- file live preview */

	function readableSize(bytes) {
		if (bytes < 1024) return bytes + ' B';
		if (bytes < 1024 * 1024) return Math.round(bytes / 1024) + ' KB';
		return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
	}

	function wirePreview(input) {
		var target = document.querySelector(input.getAttribute('data-preview'));
		if (!target) return;

		input.addEventListener('change', function () {
			target.innerHTML = '';
			// The container ships hidden so an untouched form shows no empty box.
			if (!input.files || !input.files.length) {
				target.style.display = 'none';
				return;
			}
			target.style.display = '';

			var head = document.createElement('p');
			head.className = 'p411-media__head';
			head.textContent = input.files.length === 1
				? 'Selected — not saved yet:'
				: 'Selected ' + input.files.length + ' images — not saved yet:';
			target.appendChild(head);

			var grid = document.createElement('div');
			grid.className = 'p411-media__grid';
			grid.setAttribute('data-media-grid', '');
			target.appendChild(grid);

			Array.prototype.forEach.call(input.files, function (file) {
				if (!/^image\//.test(file.type)) return;
				var cell = document.createElement('figure');
				cell.className = 'p411-media__cell is-new';

				var img = document.createElement('img');
				img.alt = file.name;
				// revoked on load, so a long form does not hold every blob open
				var url = URL.createObjectURL(file);
				img.src = url;
				img.addEventListener('load', function () { URL.revokeObjectURL(url); });

				var cap = document.createElement('figcaption');
				cap.textContent = file.name + ' · ' + readableSize(file.size);

				cell.appendChild(img);
				cell.appendChild(cap);
				grid.appendChild(cell);
			});
		});
	}

	/* ------------------------------------------------------ video live preview */

	// Accepts youtu.be/ID, /watch?v=ID, /embed/ID and /shorts/ID.
	function youtubeId(url) {
		var m = String(url).match(
			/(?:youtu\.be\/|youtube\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/))([A-Za-z0-9_-]{6,})/
		);
		return m ? m[1] : '';
	}

	function wireVideoPreview(field) {
		var target = document.querySelector(field.getAttribute('data-video-preview'));
		if (!target) return;

		function render() {
			var parts = String(field.value || '').split(/[\s,]+/).filter(Boolean);
			target.innerHTML = '';
			if (!parts.length) return;

			var seen = {};
			var ok = 0, bad = [];

			parts.forEach(function (raw) {
				var id = youtubeId(raw);
				if (!id) { bad.push(raw); return; }
				if (seen[id]) return;
				seen[id] = true;
				ok++;

				var cell = document.createElement('div');
				cell.className = 'p411-media__video';
				var frame = document.createElement('iframe');
				frame.src = 'https://www.youtube.com/embed/' + id;
				frame.title = 'Video preview';
				frame.loading = 'lazy';
				frame.setAttribute('allowfullscreen', '');
				frame.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
				cell.appendChild(frame);
				target.appendChild(cell);
			});

			if (bad.length) {
				var warn = document.createElement('p');
				warn.className = 'p411-media__warn';
				warn.textContent = bad.length === 1
					? 'Not a YouTube link, so it will not play: ' + bad[0]
					: bad.length + ' entries are not YouTube links and will not play.';
				target.appendChild(warn);
			}
			if (ok) {
				var note = document.createElement('p');
				note.className = 'p411-media__head';
				note.textContent = ok === 1 ? '1 video will show on the listing.'
				                            : ok + ' videos will show on the listing.';
				target.insertBefore(note, target.firstChild);
			}
		}

		var timer = null;
		field.addEventListener('input', function () {
			clearTimeout(timer);
			timer = setTimeout(render, 250);   // one render per pause, not per keystroke
		});
		render();
	}

	/* -------------------------------------------------------------------- init */

	/* --------------------------------------------------- remove-tick feedback */

	// Ticking Remove greys the image out, so the effect of saving is visible
	// before you save. Delegated: cells can be added by a preview render.
	document.addEventListener('change', function (e) {
		var box = e.target;
		if (!box || box.type !== 'checkbox') return;
		var cell = box.closest ? box.closest('.p411-media__cell') : null;
		if (!cell) return;
		cell.classList.toggle('is-removing', box.checked);
	});

	function init() {
		document.querySelectorAll('input[type="file"][data-preview]').forEach(wirePreview);
		document.querySelectorAll('[data-video-preview]').forEach(wireVideoPreview);
		// Existing images are focusable so the lightbox is reachable by keyboard.
		document.querySelectorAll('[data-media-grid] img, img[data-lightbox]').forEach(function (img) {
			if (!img.hasAttribute('tabindex')) img.setAttribute('tabindex', '0');
			img.style.cursor = 'zoom-in';
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
