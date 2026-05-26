/**
 * Reads the CSRF token from the <meta name="csrf-token"> tag injected
 * by application/hooks/Csrf_inject.php and attaches it to every
 * non-GET request — both jQuery $.ajax and native fetch().
 *
 * Without this shim, native fetch() callers (the homepage newsletter
 * widget, anything written with modern JS) would silently 403 on every
 * POST because CodeIgniter's CSRF check fires before the controller
 * sees the request.
 */
(function () {
    function readToken() {
        var nameEl  = document.querySelector('meta[name="csrf-token-name"]');
        var valueEl = document.querySelector('meta[name="csrf-token"]');
        return {
            name:  nameEl  ? nameEl.getAttribute('content')  : '',
            value: valueEl ? valueEl.getAttribute('content') : ''
        };
    }

    /* ---------- jQuery AJAX ---------- */
    if (typeof window.jQuery !== 'undefined') {
        var $ = window.jQuery;
        $.ajaxPrefilter(function (options, original, xhr) {
            var t = readToken();
            if (!t.name || !t.value) return;
            var method = (options.type || options.method || 'GET').toUpperCase();
            if (method === 'GET' || method === 'HEAD') return;
            xhr.setRequestHeader('X-CSRF-TOKEN', t.value);

            if (typeof options.data === 'string') {
                options.data += (options.data.length ? '&' : '')
                    + encodeURIComponent(t.name) + '=' + encodeURIComponent(t.value);
            } else if (options.data && typeof options.data === 'object' && !(options.data instanceof FormData)) {
                options.data[t.name] = t.value;
            } else if (options.data instanceof FormData) {
                options.data.append(t.name, t.value);
            }
        });
    }

    /* ---------- window.fetch ---------- */
    if (typeof window.fetch === 'function') {
        var origFetch = window.fetch.bind(window);
        window.fetch = function (resource, init) {
            init = init || {};
            var method = (init.method || (resource && resource.method) || 'GET').toUpperCase();
            if (method === 'GET' || method === 'HEAD') {
                return origFetch(resource, init);
            }
            var t = readToken();
            if (!t.name || !t.value) {
                return origFetch(resource, init);
            }

            // Add X-CSRF-TOKEN header (some apps read it server-side).
            var headers = new Headers(init.headers || {});
            if (!headers.has('X-CSRF-TOKEN')) headers.set('X-CSRF-TOKEN', t.value);
            init.headers = headers;

            // CI3 reads the token from $_POST / cookie, so append to body
            // when we recognise the body type.
            var body = init.body;
            if (body instanceof FormData) {
                if (!body.has(t.name)) body.append(t.name, t.value);
            } else if (typeof URLSearchParams !== 'undefined' && body instanceof URLSearchParams) {
                if (!body.has(t.name)) body.append(t.name, t.value);
            } else if (typeof body === 'string') {
                var sep = body.length ? '&' : '';
                init.body = body + sep + encodeURIComponent(t.name) + '=' + encodeURIComponent(t.value);
            } else if (body == null) {
                var fd = new FormData();
                fd.append(t.name, t.value);
                init.body = fd;
            }
            // JSON / Blob / ArrayBuffer bodies: callers must include the
            // token themselves; we still send the header above.
            return origFetch(resource, init);
        };
    }
})();
