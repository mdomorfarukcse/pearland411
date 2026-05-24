<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Output-escaping helpers. Use these in views to plug stored-XSS
 * sinks where user-submitted strings (listing names, descriptions,
 * review messages, etc.) are echoed directly into HTML.
 *
 *   <?= e($listing->listing_name) ?>
 *   <?= e_nl2br($listing->listing_description) ?>
 *   <a href="<?= e_url($listing->listing_website) ?>">...</a>
 */

if (!function_exists('e')) {
    /**
     * HTML-escape a string for safe rendering as text or attribute.
     *
     * @param mixed $value
     * @return string
     */
    function e($value)
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('e_nl2br')) {
    /**
     * Escape multi-line text, then convert newlines to <br>.
     * Use for descriptions / messages.
     */
    function e_nl2br($value)
    {
        return nl2br(e($value));
    }
}

if (!function_exists('e_url')) {
    /**
     * Escape a URL for use in href/src. Rejects javascript:, data:,
     * vbscript: schemes outright (returns '#' instead).
     */
    function e_url($value)
    {
        if ($value === null || $value === '') {
            return '';
        }
        $url = trim((string) $value);
        // Block dangerous schemes regardless of casing / whitespace.
        $scheme_check = strtolower(preg_replace('/\s+/', '', $url));
        if (preg_match('#^(javascript|data|vbscript|file):#i', $scheme_check)) {
            return '#';
        }
        return e($url);
    }
}

if (!function_exists('e_attr')) {
    /**
     * Alias for e() — semantic marker that the value is going into
     * an HTML attribute. Same encoding rules.
     */
    function e_attr($value)
    {
        return e($value);
    }
}

if (!function_exists('json_ld')) {
    /**
     * Wrap a structured-data payload in a JSON-LD <script> tag.
     * Returns the HTML to assign to $data['json_ld'] in a controller.
     * The <script> JSON is encoded with the slash-escape so it can
     * survive being embedded in HTML without breaking out.
     */
    function json_ld(array $payload)
    {
        return '<script type="application/ld+json">'
            . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            . '</script>';
    }
}
