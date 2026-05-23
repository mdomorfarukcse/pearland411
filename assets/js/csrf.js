/**
 * Reads the CSRF token from the <meta name="csrf-token"> tag injected
 * by application/hooks/Csrf_inject.php and attaches it to every
 * non-GET jQuery AJAX request as a POST/header parameter.
 *
 * After each response, if the server rotated the token it will appear
 * in a new <meta name="csrf-token"> — we re-read on each ajaxSuccess
 * to stay in sync.
 *
 * Plain `fetch()` callers should read the token from the meta tag and
 * include it in their request body / X-CSRF-TOKEN header.
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

    if (typeof window.jQuery === 'undefined') {
        return;
    }

    var $ = window.jQuery;

    $.ajaxPrefilter(function (options, original, xhr) {
        var t = readToken();
        if (!t.name || !t.value) {
            return;
        }
        var method = (options.type || options.method || 'GET').toUpperCase();
        if (method === 'GET' || method === 'HEAD') {
            return;
        }
        xhr.setRequestHeader('X-CSRF-TOKEN', t.value);

        // Also append to form-encoded payloads (CI3 reads the token from
        // POST/COOKIE, not from headers).
        if (typeof options.data === 'string') {
            if (options.data.length) {
                options.data += '&' + encodeURIComponent(t.name) + '=' + encodeURIComponent(t.value);
            } else {
                options.data = encodeURIComponent(t.name) + '=' + encodeURIComponent(t.value);
            }
        } else if (options.data && typeof options.data === 'object' && !(options.data instanceof FormData)) {
            options.data[t.name] = t.value;
        } else if (options.data instanceof FormData) {
            options.data.append(t.name, t.value);
        }
    });
})();
