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

if (!function_exists('admin_actions')) {
    /**
     * Render a uniform action cell (View / Edit / Delete) for the
     * admin / user list tables.
     *
     *   <td><?= admin_actions(array(
     *       'view'   => base_url('listing-details/' . $row->listing_slug),
     *       'edit'   => base_url('admin/edit-listing/' . $row->listing_slug),
     *       'delete' => base_url('admin/delete-listing/' . $row->listing_slug),
     *   ), 'listing') ?></td>
     *
     * Any key that's empty/false/null is omitted. The delete link
     * gets a default JS confirm() prompt (override with
     * 'delete_confirm' => 'Delete this campaign?'). The view link
     * opens in a new tab (target=_blank) so the admin doesn't lose
     * the list page.
     *
     * @param array  $links  ['view' => ..., 'edit' => ..., 'delete' => ..., 'delete_confirm' => ...]
     * @param string $label  Short label used in the default delete prompt ("item", "listing", "event"…).
     * @return string HTML
     */
    function admin_actions(array $links, $label = 'item')
    {
        $out = '<div class="admin-actions" style="display:inline-flex;gap:8px;flex-wrap:wrap;">';

        if (!empty($links['view'])) {
            $out .= '<a href="' . e_url($links['view']) . '"'
                 . ' target="_blank" rel="noopener noreferrer"'
                 . ' class="db-list-edit" title="View">View</a>';
        }
        if (!empty($links['edit'])) {
            $out .= '<a href="' . e_url($links['edit']) . '"'
                 . ' class="db-list-edit" title="Edit">Edit</a>';
        }
        if (!empty($links['delete'])) {
            $confirm = isset($links['delete_confirm'])
                ? $links['delete_confirm']
                : ('Delete this ' . $label . '?');
            $out .= '<a href="' . e_url($links['delete']) . '"'
                 . ' onclick="return confirm(' . json_encode($confirm) . ');"'
                 . ' class="db-list-edit" style="color:#c00;" title="Delete">Delete</a>';
        }

        $out .= '</div>';
        return $out;
    }
}

if (!function_exists('pearland_slug')) {
    /**
     * URL-safe slug for category / listing names.
     *
     * Views previously each declared their own copy of this as
     * `createSafeSlug()` — three times in views/index.php alone, one of
     * them unguarded. Keeping a single guarded copy here removes the
     * "Cannot redeclare" hazard that surfaces as soon as any of those
     * views is split into a partial.
     *
     * @param  string $text
     * @return string
     */
    function pearland_slug($text)
    {
        $slug = strtolower((string) $text);
        $slug = str_replace(' ', '-', $slug);
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
        $slug = preg_replace('/-+/', '-', $slug);

        return trim($slug, '-');
    }
}
