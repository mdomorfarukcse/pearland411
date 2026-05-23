<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * display_override hook.
 *
 * - Adds <meta name="csrf-token-name" content="..."> and
 *        <meta name="csrf-token" content="..."> to the <head>
 *   so JavaScript (jQuery $.ajaxSetup, fetch, etc.) can pick up the
 *   token without round-tripping to the server.
 *
 * - Auto-inserts <input type="hidden" name="<token-name>" value="<token>">
 *   immediately after every <form ... method="post"> tag in the rendered
 *   HTML, so we don't have to manually patch ~125 raw <form> tags across
 *   the codebase. Forms that already include the hidden input are left
 *   alone (avoid duplicates).
 *
 * - Bails out for AJAX / JSON responses (content type starts with
 *   application/json or text/xml), and for non-2xx redirects, to avoid
 *   corrupting non-HTML payloads.
 */
function csrf_inject_into_output()
{
    $CI =& get_instance();
    $output = $CI->output->get_output();

    // Skip non-HTML responses. Use the explicit content type if set;
    // otherwise sniff the buffer.
    $content_type = '';
    foreach (headers_list() as $h) {
        if (stripos($h, 'Content-Type:') === 0) {
            $content_type = trim(substr($h, 13));
            break;
        }
    }
    $is_html = ($content_type === '' || stripos($content_type, 'text/html') === 0);
    if (!$is_html) {
        echo $output;
        return;
    }
    if ($output === '' || strpos($output, '<') === false) {
        echo $output;
        return;
    }

    $token_name  = $CI->security->get_csrf_token_name();
    $token_value = $CI->security->get_csrf_hash();

    if (!$token_name || !$token_value) {
        echo $output;
        return;
    }

    $token_name_esc  = htmlspecialchars($token_name, ENT_QUOTES, 'UTF-8');
    $token_value_esc = htmlspecialchars($token_value, ENT_QUOTES, 'UTF-8');

    // 1) Inject meta tags into <head>.
    $meta =
        '<meta name="csrf-token-name" content="' . $token_name_esc . '">' .
        '<meta name="csrf-token" content="' . $token_value_esc . '">';

    $output = preg_replace(
        '#(<head\b[^>]*>)#i',
        '$1' . "\n    " . $meta,
        $output,
        1
    );

    // 2) Inject hidden input into every POST form that doesn't already
    //    contain a field with the token name.
    $hidden = '<input type="hidden" name="' . $token_name_esc . '" value="' . $token_value_esc . '">';

    $output = preg_replace_callback(
        '#<form\b([^>]*)>#i',
        function ($m) use ($token_name, $hidden) {
            $attrs = $m[1];
            // Determine method (default GET). Only POST forms need a token.
            $method = 'get';
            if (preg_match('#\bmethod\s*=\s*["\']?\s*(post|get)\s*["\']?#i', $attrs, $mm)) {
                $method = strtolower($mm[1]);
            }
            if ($method !== 'post') {
                return $m[0];
            }
            return $m[0] . $hidden;
        },
        $output
    );

    echo $output;
}
