/**
 * window.ppPost(url, params, onOk, onErr) — the one way the cart / checkout
 * talk to the server.
 *
 * Why XMLHttpRequest and not fetch(): assets/js/csrf.js (and any analytics or
 * third-party script on the page) replaces window.fetch with a wrapper. If any
 * one of those wrappers misses a `return`, fetch() hands back undefined and
 * every caller dies on `.then of undefined` — which is exactly what broke
 * "Add to bundle" in production. XHR is not monkey-patched by our own code and
 * is far less likely to be patched by anyone else's, so the cart keeps working
 * no matter what else the page loads.
 *
 * It also does NOT depend on jQuery: footer.php loads jQuery *after* the view
 * markup, so inline view scripts cannot assume $ exists.
 *
 * CSRF: csrf_protection is ON (config.php) and cookie_httponly is TRUE, so JS
 * cannot read the token cookie. hooks/Csrf_inject.php puts the token in
 * <meta name="csrf-token"> — we read it at call time and append it to the body,
 * which is where CodeIgniter's Security::csrf_verify() looks.
 */
(function (w) {
    'use strict';

    function readToken() {
        var n = document.querySelector('meta[name="csrf-token-name"]');
        var v = document.querySelector('meta[name="csrf-token"]');
        if (!n || !v) return null;
        var name = n.getAttribute('content');
        var value = v.getAttribute('content');
        return (name && value) ? { name: name, value: value } : null;
    }

    /**
     * Keep the page's token in sync when the server rotates it
     * (csrf_regenerate). Updates the meta tag and every hidden input the
     * inject hook wrote into forms, so later posts don't 403.
     */
    function storeToken(name, value) {
        if (!name || !value) return;
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) meta.setAttribute('content', value);
        var inputs = document.querySelectorAll('input[type="hidden"][name="' + name + '"]');
        Array.prototype.forEach.call(inputs, function (i) { i.value = value; });
    }

    w.ppPost = function (url, params, onOk, onErr) {
        var t = readToken();
        var body = [];

        Object.keys(params || {}).forEach(function (k) {
            if (params[k] === null || params[k] === undefined) return;
            body.push(encodeURIComponent(k) + '=' + encodeURIComponent(params[k]));
        });
        if (t) {
            body.push(encodeURIComponent(t.name) + '=' + encodeURIComponent(t.value));
        }

        var xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        if (t) xhr.setRequestHeader('X-CSRF-TOKEN', t.value);

        xhr.onload = function () {
            var data = null;
            try { data = JSON.parse(xhr.responseText); } catch (e) { /* not JSON */ }

            if (data && data.csrf_name && data.csrf_hash) {
                storeToken(data.csrf_name, data.csrf_hash);
            }

            if (xhr.status >= 200 && xhr.status < 300 && data) {
                if (data.success === false && onErr) {
                    onErr(data.error || 'Something went wrong. Please try again.', xhr.status, data);
                    return;
                }
                if (onOk) onOk(data);
                return;
            }

            if (!onErr) return;
            if (xhr.status === 403) {
                // CSRF rejected — the token died with the session.
                onErr('Your session expired. Please refresh the page and try again.', 403, data);
            } else if (xhr.status === 0) {
                onErr('Network error. Please check your connection and try again.', 0, data);
            } else {
                onErr('Something went wrong. Please try again.', xhr.status, data);
            }
        };

        xhr.onerror = function () {
            if (onErr) onErr('Network error. Please check your connection and try again.', 0, null);
        };

        xhr.send(body.join('&'));
    };
})(window);
