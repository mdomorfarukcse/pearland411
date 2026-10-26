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
        // 'extra' => ['Label' => url, …] — row-specific shortcuts.
        if (!empty($links['extra']) && is_array($links['extra'])) {
            foreach ($links['extra'] as $text => $url) {
                $out .= '<a href="' . e_url($url) . '" class="db-list-edit" title="' . e_attr($text) . '">' . e($text) . '</a>';
            }
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

if (!function_exists('pearland_merge_key')) {
    /**
     * The key two names are compared on when deciding whether they are the
     * same thing written two ways.
     *
     * Lower-cased, "&" and "+" read as the word "and", punctuation dropped,
     * runs of whitespace collapsed. So:
     *
     *   "Arts & Entertainment"   -> "arts and entertainment"
     *   "Arts and Entertainment" -> "arts and entertainment"
     *   "Beauty & Personal Care" -> "beauty and personal care"
     *
     * That is the whole rule, and it is deliberately narrow. It catches a
     * category the directory was given twice in two house styles, which is
     * how /all-category came to offer "Community & Government" (empty) beside
     * "Community and Government" (14 businesses). It does not claim Finance
     * and Financial Services are the same thing, because they might not be —
     * that is a decision for whoever runs the directory, and /admin has a
     * Merge control for making it.
     *
     * Matches migration 0031's SQL, which does the same transformations in
     * the same order. If one changes, change both.
     */
    function pearland_merge_key($name)
    {
        $key = function_exists('mb_strtolower')
            ? mb_strtolower((string) $name, 'UTF-8')
            : strtolower((string) $name);

        $key = str_replace(array('&', '+'), ' and ', $key);
        $key = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $key);

        return trim((string) preg_replace('/\s+/', ' ', (string) $key));
    }
}

if (!function_exists('content_text')) {
    /**
     * Turn a stored rich-text body into clean plain text.
     *
     * Events, news, blogs and videos are all saved with pasted markup
     * (`<p>…<br />`). Escaping that whole string printed the tags on the
     * page — which is exactly what an event description looked like — and
     * echoing it raw is a stored-XSS sink. Block-level tags become
     * newlines, everything else is stripped, entities are decoded, runs of
     * blank lines collapse.
     *
     * RETURNS UNESCAPED TEXT. The caller escapes at output, as everywhere
     * else in this codebase:
     *
     *   <?= nl2br(e(content_text($news->news_description))) ?>
     *
     * @param  mixed $value
     * @return string
     */
    function content_text($value)
    {
        $value = (string) $value;

        // Some bodies are stored *entity-encoded*: the admin blog form ran the
        // pasted markup through htmlentities() before saving, so the column
        // holds "&lt;p&gt;…&lt;/p&gt;" rather than "<p>…</p>". Left alone, the
        // tag work below finds no tags to strip and the decode at the end turns
        // them into visible text — which is how "<p>Why Pearland is one of
        // America&rsquo;s…" ended up printed on the blog cards. Decoding first
        // gives the rest of this function real markup to work with.
        //
        // The signal is an encoded *closing* tag ("&lt;/p&gt;"), not just
        // "&lt;": a post that explains "use the &lt;p&gt; tag to start a
        // paragraph" is talking about markup rather than made of it, and
        // should keep showing what it typed.
        if (strpos($value, '<') === false && preg_match('#&lt;\s*/\s*[a-z]#i', $value)) {
            $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        $value = preg_replace('#<\s*(br|/p|/div|/li|/h[1-6]|/tr)\s*/?\s*>#i', "\n", $value);
        $value = strip_tags($value);
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = str_replace("\xC2\xA0", ' ', $value);           // stray &nbsp;

        // Normalise line endings BEFORE collapsing blank runs. CKEditor writes
        // its block tags separated by real newlines — "<p>a</p>\r\n\r\n<p>b</p>"
        // — and the rule above adds one more for the closing tag, so a single
        // paragraph break arrives here as "\n\r\n\r\n". The collapse below is
        // written for "\n", and the stray \r broke the run, so every paragraph
        // break rendered as three <br> once the caller ran nl2br() over it.
        $value = str_replace(array("\r\n", "\r"), "\n", $value);

        // Trim each line at both ends: the markup is indented, so a stripped
        // <li> arrived as "\n\tTwo" and the tab showed up in the output.
        $value = preg_replace("/[ \t]+\n/", "\n", $value);
        $value = preg_replace("/\n[ \t]+/", "\n", $value);
        $value = preg_replace("/\n{3,}/", "\n\n", trim($value));

        return $value;
    }
}

if (!function_exists('content_html')) {
    /**
     * A stored article body as safe, structural HTML — paragraphs kept.
     *
     * The sibling of content_text(), for the places that render a whole article
     * rather than summarise it. content_text() flattens the body to plain text
     * and the view rebuilds the breaks with nl2br(), which means the gap between
     * two paragraphs depends on how much incidental whitespace the editor left
     * between the tags: the same article reads evenly in one place and
     * cluttered in another. Keeping the author's own <p> elements and spacing
     * them in CSS makes the rhythm a property of the page, not of the paste.
     *
     * The tag whitelist is structure only, and every attribute is dropped apart
     * from href/title on a link — that is what stops an imported article's
     * inline `style="color:#ff0000"` from repainting the page, which is the
     * other half of the same complaint. Output is safe to echo unescaped;
     * do NOT wrap it in e().
     *
     * @param  string $value Stored body, possibly legacy entity-encoded.
     * @return string        Sanitised HTML.
     */
    function content_html($value)
    {
        $value = (string) $value;

        // Same legacy guard as content_text(): a body stored through
        // htmlentities() holds "&lt;p&gt;…" and has to be decoded before there
        // is any markup to keep. The signal is an encoded CLOSING tag, so a post
        // that merely talks about "&lt;p&gt;" keeps showing what it typed.
        if (strpos($value, '<') === false && preg_match('#&lt;\s*/\s*[a-z]#i', $value)) {
            $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // strip_tags() drops the tags but keeps what is between them, so a
        // <script> body would survive as visible text. Remove these outright.
        $value = preg_replace('#<\s*(script|style)\b[^>]*>.*?<\s*/\s*\1\s*>#is', '', $value);
        $value = preg_replace('#<\s*(script|style)\b[^>]*/?>#i', '', $value);

        $allowed = '<p><br><strong><b><em><i><u><ul><ol><li><a><h2><h3><h4><blockquote>';
        $value   = strip_tags($value, $allowed);

        // Anchors are handled as whole pairs — they cannot nest, so the lazy
        // match pairs each open tag with its own close. A link whose href does
        // not survive external_url() is unwrapped to its text rather than
        // rewritten, which keeps the tags balanced.
        $value = preg_replace_callback('#<\s*a\b([^>]*)>(.*?)<\s*/\s*a\s*>#is', function ($m) {
            $href = '';
            if (preg_match('#\bhref\s*=\s*(["\'])(.*?)\1#is', $m[1], $h)) {
                $href = external_url(html_entity_decode($h[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
            if ($href === '') {
                return $m[2];
            }
            $attrs = ' href="' . e_attr($href) . '" rel="nofollow noopener" target="_blank"';
            if (preg_match('#\btitle\s*=\s*(["\'])(.*?)\1#is', $m[1], $t)) {
                $attrs .= ' title="' . e_attr(html_entity_decode($t[2], ENT_QUOTES | ENT_HTML5, 'UTF-8')) . '"';
            }
            // Parked behind sentinels so the unpaired-tag sweep below cannot
            // mistake a link this function just built for one it has not seen.
            // strip_tags() has already run, so no literal "<" survives in the
            // text and these control characters cannot occur in the content.
            return "\x02a" . $attrs . "\x03" . $m[2] . "\x02/a\x03";
        }, $value);

        // Any unpaired anchor tag left over goes, so the markup stays balanced.
        $value = preg_replace('#<\s*/?\s*a\b[^>]*>#i', '', $value);

        // Everything else on the whitelist is structure — no attributes at all.
        $value = preg_replace('#<\s*(' . 'p|br|strong|b|em|i|u|ul|ol|li|h2|h3|h4|blockquote'
            . ')\b[^>]*?(/?)\s*>#i', '<$1$2>', $value);

        $value = str_replace(array("\x02", "\x03"), array('<', '>'), $value);

        $value = str_replace("\xC2\xA0", ' ', $value);           // stray &nbsp;
        $value = trim($value);

        // A legacy row stored as plain text has no block tags to space. Fall
        // back to content_text()'s paragraphing so those rows read the same.
        if ($value !== '' && !preg_match('#<\s*(p|ul|ol|h2|h3|h4|blockquote)\b#i', $value)) {
            $paras = preg_split("/\n{2,}/", content_text($value));
            $value = '';
            foreach ($paras as $para) {
                $para = trim($para);
                if ($para !== '') {
                    $value .= '<p>' . nl2br(e($para)) . '</p>';
                }
            }
        }

        return $value;
    }
}

if (!function_exists('content_excerpt')) {
    /**
     * A one-paragraph summary of a stored body, cut on a word boundary.
     * Unescaped, like content_text() — escape at output.
     *
     * @param  mixed $value
     * @param  int   $length  Maximum characters before the ellipsis.
     * @return string
     */
    function content_excerpt($value, $length = 160)
    {
        $text = preg_replace('/\s+/u', ' ', content_text($value));
        $text = trim($text);
        if ($text === '' || mb_strlen($text, 'UTF-8') <= $length) return $text;

        $cut = mb_substr($text, 0, $length, 'UTF-8');
        $gap = mb_strrpos($cut, ' ', 0, 'UTF-8');
        if ($gap !== false && $gap > (int) ($length * 0.6)) $cut = mb_substr($cut, 0, $gap, 'UTF-8');

        return rtrim($cut, " ,.;:—-") . '…';
    }
}

if (!function_exists('youtube_id')) {
    /**
     * Pull an 11-character YouTube id out of whatever was stored.
     *
     * `video.video_code` is meant to hold the bare id, but the admin form
     * accepts free text, so a full watch URL, a share link or an embed URL
     * all turn up in practice. Returns '' when nothing usable is found —
     * callers must handle that rather than building a broken embed.
     *
     * @param  mixed $value
     * @return string
     */
    function youtube_id($value)
    {
        $value = trim((string) $value);
        if ($value === '') return '';

        if (preg_match('~^[A-Za-z0-9_-]{11}$~', $value)) return $value;

        $patterns = array(
            '~[?&]v=([A-Za-z0-9_-]{11})~',            // watch?v=ID
            '~youtu\.be/([A-Za-z0-9_-]{11})~',        // youtu.be/ID
            '~/embed/([A-Za-z0-9_-]{11})~',           // /embed/ID
            '~/shorts/([A-Za-z0-9_-]{11})~',          // /shorts/ID
            '~/live/([A-Za-z0-9_-]{11})~',            // /live/ID
        );
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value, $m)) return $m[1];
        }

        return '';
    }
}

if (!function_exists('youtube_thumb_url')) {
    /**
     * YouTube's own still for a video. Used when nobody uploaded a
     * thumbnail — the alternative was a broken-image box on every card.
     * Returns '' when the id can't be read.
     */
    function youtube_thumb_url($value, $size = 'hqdefault')
    {
        $id = youtube_id($value);
        if ($id === '') return '';
        $size = in_array($size, array('default', 'mqdefault', 'hqdefault', 'sddefault', 'maxresdefault'), true)
            ? $size : 'hqdefault';

        return 'https://i.ytimg.com/vi/' . $id . '/' . $size . '.jpg';
    }
}

if (!function_exists('youtube_embed_url')) {
    /** Player URL for an id/URL, or '' when there is nothing to play. */
    function youtube_embed_url($value, array $params = array())
    {
        $id = youtube_id($value);
        if ($id === '') return '';
        $query = $params ? ('?' . http_build_query($params)) : '';

        return 'https://www.youtube.com/embed/' . $id . $query;
    }
}

if (!function_exists('embed_src')) {
    /**
     * Get a usable iframe src out of a stored "map" field.
     *
     * `events.event_map` is filled by pasting a Google Maps embed snippet,
     * and the view used to echo that snippet raw — so whatever an admin
     * pasted became live markup on a public page. Take the src out of it
     * and let the view build its own iframe; accept a bare URL too.
     * Returns '' for anything that isn't an http(s) URL.
     *
     * @param  mixed $value
     * @return string
     */
    function embed_src($value)
    {
        $value = trim((string) $value);
        if ($value === '') return '';

        if (stripos($value, '<iframe') !== false
            && preg_match('~<iframe[^>]*\ssrc\s*=\s*("|\')(.*?)\1~is', $value, $m)) {
            $value = html_entity_decode($m[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        $value = trim($value);
        return preg_match('~^https?://~i', $value) ? $value : '';
    }
}

if (!function_exists('category_placeholder')) {
    /**
     * URL of the placeholder image for a listing that has no photograph.
     *
     * The site used to serve one graphic for every empty listing — a pear on a
     * pear-green tile captioned "no photo". Repeated down a three-column grid
     * that single image is what made the homepage read as unfinished, so there
     * is now one quiet cream/grey placeholder per kind of business, each
     * carrying a glyph that says what the listing is even before the owner
     * uploads anything.
     *
     * Matching is on the category NAME rather than on an id, because the
     * category table is operator-editable and ids differ between installs. The
     * order of the map is the rule: the first keyword found in the name wins,
     * so the specific entries ("real estate", "pest control") are listed above
     * the general ones ("home", "service") they would otherwise be swallowed by.
     *
     * The artwork lives in assets/images/placeholders/ and is generated by
     * scripts/generate_brand_art.py.
     *
     * @param  string $category_name
     * @return string absolute URL
     */
    function category_placeholder($category_name = '')
    {
        static $map = null;

        if ($map === null) {
            // keyword => artwork basename. Order matters — see the docblock.
            $map = array(
                'real estate'   => 'real-estate',   'realtor'      => 'real-estate',
                'apartment'     => 'real-estate',   'property'     => 'real-estate',
                'mortgage'      => 'financial',     'title compan' => 'real-estate',

                'dental'        => 'medical',       'dentist'      => 'medical',
                'orthodont'     => 'medical',       'physician'    => 'medical',
                'doctor'        => 'medical',       'clinic'       => 'medical',
                'hospital'      => 'medical',       'pharmac'      => 'medical',
                'medical'       => 'medical',       'optom'        => 'medical',
                'chiropract'    => 'medical',       'urgent care'  => 'medical',

                'veterinar'     => 'pets',          'pet '         => 'pets',
                'pets'          => 'pets',          'grooming'     => 'pets',
                'kennel'        => 'pets',          'animal'       => 'pets',

                'health'        => 'healthcare',    'wellness'     => 'healthcare',
                'therapy'       => 'healthcare',    'fitness'      => 'healthcare',
                'gym'           => 'healthcare',    'nursing'      => 'healthcare',
                'senior care'   => 'healthcare',    'home care'    => 'healthcare',

                'restaurant'    => 'food',          'cafe'         => 'food',
                'coffee'        => 'food',          'bakery'       => 'food',
                'caterer'       => 'food',          'catering'     => 'food',
                'pizza'         => 'food',          'food'         => 'food',
                'beverage'      => 'food',          'dining'       => 'food',
                'grill'         => 'food',          'bar & '       => 'food',
                'brewer'        => 'food',          'deli'         => 'food',

                'automotive'    => 'auto',          'auto '        => 'auto',
                'car '          => 'auto',          'tire'         => 'auto',
                'collision'     => 'auto',          'towing'       => 'auto',
                'transmission'  => 'auto',          'motorcycle'   => 'auto',
                'vehicle'       => 'auto',          'body shop'    => 'auto',

                'attorney'      => 'legal',         'lawyer'       => 'legal',
                'legal'         => 'legal',         'law '         => 'legal',
                'notary'        => 'legal',

                'bank'          => 'financial',     'accounting'   => 'financial',
                'accountant'    => 'financial',     'tax '         => 'financial',
                'financial'     => 'financial',     'insurance'    => 'financial',
                'credit'        => 'financial',     'invest'       => 'financial',
                'loan'          => 'financial',     'bookkeep'     => 'financial',

                'salon'         => 'beauty',        'spa'          => 'beauty',
                'barber'        => 'beauty',        'hair'         => 'beauty',
                'nail'          => 'beauty',        'beauty'       => 'beauty',
                'massage'       => 'beauty',        'skin'         => 'beauty',
                'personal care' => 'beauty',

                'school'        => 'education',     'tutor'        => 'education',
                'academy'       => 'education',     'college'      => 'education',
                'education'     => 'education',     'daycare'      => 'education',
                'child care'    => 'education',     'childcare'    => 'education',
                'preschool'     => 'education',     'training'     => 'education',

                'computer'      => 'technology',    'software'     => 'technology',
                'technolog'     => 'technology',    'internet'     => 'technology',
                'electronic'    => 'technology',    'web design'   => 'technology',
                'it services'   => 'technology',

                'clean'         => 'cleaning',      'janitor'      => 'cleaning',
                'maid'          => 'cleaning',      'laundry'      => 'cleaning',
                'power wash'    => 'cleaning',      'pressure wash' => 'cleaning',

                'construct'     => 'construction',  'contractor'   => 'construction',
                'remodel'       => 'construction',  'builder'      => 'construction',
                'carpentry'     => 'construction',  'concrete'     => 'construction',
                'painting'      => 'construction',  'flooring'     => 'construction',
                'fence'         => 'construction',  'roofing'      => 'construction',

                'plumb'         => 'home-services', 'hvac'         => 'home-services',
                'air condition' => 'home-services', 'heating'      => 'home-services',
                'electric'      => 'home-services', 'landscap'     => 'home-services',
                'lawn'          => 'home-services', 'tree service' => 'home-services',
                'pest'          => 'home-services', 'handyman'     => 'home-services',
                'pool'          => 'home-services', 'garage door'  => 'home-services',
                'appliance'     => 'home-services', 'home '        => 'home-services',
                'house'         => 'home-services',

                'retail'        => 'shopping',      'boutique'     => 'shopping',
                'clothing'      => 'shopping',      'furniture'    => 'shopping',
                'jewel'         => 'shopping',      'florist'      => 'shopping',
                'grocery'       => 'shopping',      'gift'         => 'shopping',
                'shopping'      => 'shopping',      'store'        => 'shopping',
                'shop'          => 'shopping',

                'marketing'     => 'business',      'advertis'     => 'business',
                'consult'       => 'business',      'staffing'     => 'business',
                'printing'      => 'business',      'photograph'   => 'business',
                'professional'  => 'business',      'business'     => 'business',
                'office'        => 'business',      'agency'       => 'business',
            );
        }

        $name = ' ' . strtolower(trim((string) $category_name)) . ' ';
        $file = 'general';

        if ($name !== '  ') {
            foreach ($map as $needle => $art) {
                if (strpos($name, $needle) !== false) {
                    $file = $art;
                    break;
                }
            }
        }

        return base_url('assets/images/placeholders/' . $file . '.svg');
    }
}

if (!function_exists('enquiry_departments')) {
    /**
     * The departments a visitor can address an enquiry to.
     *
     * This replaces the named sales-rep list the signup form and the
     * /pricing-plan callback panel used to offer. Nine people were named in
     * those dropdowns and most of them no longer work here, so a caller could
     * ask by name for somebody who left — and the enquiry then sat waiting for
     * a person who was never going to pick it up. A department outlives whoever
     * staffs it, which is the whole point of routing to one.
     *
     * Editable without a deploy through the `enquiry_departments` setting at
     * /admin/setting — one department per line, or comma separated. The first
     * entry is the default: it is what a form preselects and what the server
     * falls back to when nothing valid was posted.
     *
     * @return string[] Never empty — the built-in list is the floor.
     */
    function enquiry_departments()
    {
        $fallback = array(
            'Sales Department',
            'Customer Service',
            'Billing Department',
            'Advertising & Marketing',
            'Technical Support',
        );

        $raw   = trim((string) site_setting('enquiry_departments', ''));
        $names = $raw !== '' ? preg_split('/[\r\n,]+/', $raw) : $fallback;

        $out = array();
        foreach ($names as $name) {
            $name = trim((string) $name);
            if ($name !== '' && !in_array($name, $out, true)) $out[] = $name;
        }

        return $out ?: $fallback;
    }
}

if (!function_exists('default_enquiry_department')) {
    /**
     * The department an enquiry goes to when the visitor picked none.
     *
     * @return string
     */
    function default_enquiry_department()
    {
        $departments = enquiry_departments();

        return (string) reset($departments);
    }
}

if (!function_exists('normalise_enquiry_department')) {
    /**
     * Resolve a posted department to one we actually have.
     *
     * The value arrives from a `<select>`, so anything at all can be posted.
     * Matching it back against enquiry_departments() means the column only ever
     * holds a real department — no free text, and no way to file an enquiry
     * under a department that doesn't exist. Unknown or blank falls back to the
     * default rather than being rejected: an enquiry is never worth losing over
     * a dropdown.
     *
     * @param  string $value Posted department label.
     * @return string        Always one of enquiry_departments().
     */
    function normalise_enquiry_department($value)
    {
        $value = trim((string) $value);

        if ($value !== '') {
            foreach (enquiry_departments() as $department) {
                if (strcasecmp($department, $value) === 0) return $department;
            }
        }

        return default_enquiry_department();
    }
}

if (!function_exists('image_guidance')) {
    /**
     * The recommended upload size for one kind of image, as small print.
     *
     * "What is the optimal image size?" was asked because nothing on any upload
     * form said. The listing logo no longer has a wrong answer: those cards
     * were switched from `object-fit: cover` to `contain`, so a logo of any
     * shape is shown whole rather than having its top and bottom cropped. The
     * size below is the recommendation for a sharp result, not a requirement.
     *
     * Gallery tiles DO still crop, to 4:3 — they hold photographs, where
     * filling the tile is right — so that copy still describes a crop. Keep
     * these two in step with the CSS: `.p411-card__media` /
     * `.p411-biz__media` (contain) and `.p411-gallery img` (cover).
     *
     * Defined once because the same wording has to appear on four forms — the
     * member and admin add screens and the member and admin edit screens.
     *
     * Returns literal HTML: media_field.php echoes $mf_hint unescaped, so never
     * interpolate stored data into these strings.
     *
     * @param  string $kind logo|gallery|cover|category
     * @return string       HTML for a <small class="form-text text-muted">.
     */
    function image_guidance($kind)
    {
        $copy = array(
            'logo' => 'Best size <strong>1200 &times; 675 px</strong> (landscape), JPG or PNG under 500&nbsp;KB. '
                . '<strong>Any shape works</strong> &mdash; square, round or wide &mdash; your whole logo is '
                . 'shown and nothing is cropped. A PNG with a transparent background sits most cleanly. '
                . 'Keep the longest side at 600&nbsp;px or more, or it will look soft.',
            'gallery' => 'Best size <strong>1600 &times; 1200 px</strong> (4:3), JPG under 500&nbsp;KB each. '
                . 'Tiles crop to 4:3 and open full size when clicked, so upload the good version &mdash; '
                . 'but nothing wider than 1600 px, or the page becomes slow to load.',
            'cover' => 'Best size <strong>1600 &times; 500 px</strong> (wide banner), JPG under 800&nbsp;KB. '
                . 'It sits behind a dark tint at the top of the listing, so avoid small text or fine detail.',
            'category' => 'Best size <strong>600 &times; 600 px</strong> (square), PNG with a transparent '
                . 'background, under 200&nbsp;KB. Shown as a small icon, so keep it simple.',
        );

        return isset($copy[$kind]) ? $copy[$kind] : '';
    }
}

if (!function_exists('author_name')) {
    /**
     * The name to print for whoever created a piece of content.
     *
     * Seeded rows carry `user_id = 0` and no admin, so the COALESCE in the
     * listing queries yields NULL. The public views each had their own inline
     * ternary falling back to "Pearland411" while the admin lists had none, so
     * the same event read "Hosted by Pearland411" on the site and blank in the
     * admin table. One definition, used by both.
     *
     * @param  string|null $name Stored author/creator name, possibly NULL.
     * @return string            Never empty.
     */
    function author_name($name)
    {
        $name = trim((string) $name);

        return $name !== '' ? $name : site_setting('site_title', 'Pearland411');
    }
}

if (!function_exists('billing_interval_noun')) {
    /**
     * `plans.billing_interval` as the noun the price copy needs.
     *
     * The column is legacy free text: the admin form writes the adjective
     * 'monthly', older rows hold NULL or '', and the pricing card printed
     * `'per ' . $value` with no guard — which is how a card came to read
     * "$15 per" with nothing after it, and "per monthly" once a row was
     * populated. Anything unrecognised collapses to the house default rather
     * than being echoed raw, because a dangling preposition on a price is
     * worse than a wrong-but-grammatical one, and only the recurring
     * categories reach this branch at all.
     *
     * @param  string $interval Stored interval, in any of its historical spellings.
     * @param  string $default  Noun to use when the value is blank or unknown.
     * @return string           A bare noun: month, year, week or day.
     */
    function billing_interval_noun($interval, $default = 'month')
    {
        $map = array(
            'month'  => 'month', 'monthly'  => 'month', 'mo' => 'month', '30' => 'month',
            'year'   => 'year',  'yearly'   => 'year',  'annual' => 'year', 'annually' => 'year',
            'week'   => 'week',  'weekly'   => 'week',
            'day'    => 'day',   'daily'    => 'day',
        );

        $key = strtolower(trim((string) $interval));

        return isset($map[$key]) ? $map[$key] : $default;
    }
}

if (!function_exists('event_time_range')) {
    /**
     * Pull a start (and maybe end) clock time out of the free-text
     * `events.event_time` — "4:00 PM – 9:00 PM", "6pm till late",
     * "10am-2pm", "18:00 - 21:00", "7:30 p.m.".
     *
     * Deliberately conservative: a number only counts as a time when it has
     * minutes ("6:30") or an am/pm ("6pm"), so a date or a price in the text
     * is never mistaken for one. Anything it cannot read returns NULL and the
     * caller falls back to an all-day entry, which is always correct.
     *
     * @return array|null ['start' => 'H:i', 'end' => 'H:i'|null]
     */
    function event_time_range($text)
    {
        $text = strtolower(trim((string) $text));
        if ($text === '') return null;
        if (preg_match('/\b(noon|midday)\b/', $text) && !preg_match('/\d/', $text)) {
            return array('start' => '12:00', 'end' => null);
        }

        $T  = '(\d{1,2})(?::([0-5]\d))?\s*(a\.?\s?m\.?|p\.?\s?m\.?)?';
        $mk = function ($h, $min, $mer) {
            $min = (string) $min;
            $mer = (string) $mer;
            return array(
                'h'       => (int) $h,
                'm'       => $min !== '' ? (int) $min : 0,
                'has_min' => $min !== '',
                'mer'     => $mer !== '' ? $mer[0] : '',   // 'a' | 'p' | ''
            );
        };
        $valid = function ($t) {
            return $t['h'] <= 23 && ($t['mer'] === '' || ($t['h'] >= 1 && $t['h'] <= 12));
        };
        $qualified = function ($t) {
            return $t['has_min'] || $t['mer'] !== '';
        };

        $times = array();

        // A range first ("4 - 9 pm", "10am-2pm", "18:00 – 21:00"): one side
        // may be a bare number as long as the other is unmistakably a time.
        if (preg_match('/' . $T . '\s*(?:-|–|—|to|till|until)\s*' . $T . '/u', $text, $r)) {
            $a = $mk($r[1], $r[2] ?? '', $r[3] ?? '');
            $b = $mk($r[4], $r[5] ?? '', $r[6] ?? '');
            if (($qualified($a) || $qualified($b)) && $valid($a) && $valid($b)) {
                $times = array($a, $b);
            }
        }

        // Otherwise the first single number that is unmistakably a time.
        // One with am/pm wins ("Doors 6:30, show 7:30pm" → 7:30 pm).
        if (!$times && preg_match_all('/' . $T . '/u', $text, $m, PREG_SET_ORDER)) {
            foreach ($m as $hit) {
                $t = $mk($hit[1], $hit[2] ?? '', $hit[3] ?? '');
                if (!$qualified($t) || !$valid($t)) continue;   // a bare number, or nonsense
                if (!$times) $times = array($t);
                if ($t['mer'] !== '') { $times = array($t); break; }
            }
        }
        if (!$times) return null;

        // No am/pm anywhere and every hour under 12 ("7:00", "6:30 - 9:00"):
        // morning or evening cannot be told apart, and guessing put a 7 PM
        // concert in the calendar at 7 AM. Fall back to the all-day entry.
        // A 24-hour time ("18:00", "12:30") is unambiguous and still counts.
        $has_mer = false;
        $max_h   = 0;
        foreach ($times as $t) {
            if ($t['mer'] !== '') $has_mer = true;
            $max_h = max($max_h, $t['h']);
        }
        if (!$has_mer && $max_h < 12) return null;

        // "4 - 9 pm": the start borrows the end's am/pm.
        if (count($times) === 2 && $times[0]['mer'] === '' && $times[1]['mer'] !== '' && $times[0]['h'] <= 12) {
            $times[0]['mer'] = $times[1]['mer'];
            // …unless that would put the start after the end ("11 - 2 pm").
            if ($times[0]['h'] > $times[1]['h'] && $times[0]['h'] !== 12) $times[0]['mer'] = 'a';
        }

        $fmt = function ($t) {
            $h = $t['h'];
            if ($t['mer'] === 'p' && $h < 12) $h += 12;
            if ($t['mer'] === 'a' && $h === 12) $h = 0;
            return sprintf('%02d:%02d', $h, $t['m']);
        };

        return array(
            'start' => $fmt($times[0]),
            'end'   => isset($times[1]) ? $fmt($times[1]) : null,
        );
    }
}

if (!function_exists('event_calendar_url')) {
    /**
     * Google Calendar "add event" link for an events row.
     *
     * Timed (with ctz=America/Chicago) when event_time can be read by
     * event_time_range(); otherwise an all-day entry on the event's dates —
     * which is what every link used to be, so "Add to calendar" dropped the
     * time even for "7:00 PM – 9:00 PM".
     *
     * @param object $event  events row
     * @param string $link   the event page, appended to the details
     * @param string $about  optional description text
     * @return string '' when the event has no start date
     */
    function event_calendar_url($event, $link = '', $about = '')
    {
        $start_ts = !empty($event->event_start_date) ? strtotime((string) $event->event_start_date) : false;
        if (!$start_ts) return '';
        $end_ts = !empty($event->event_end_date) ? strtotime((string) $event->event_end_date) : false;
        if (!$end_ts || $end_ts < $start_ts) $end_ts = $start_ts;

        $time_text = trim((string) ($event->event_time ?? ''));
        $range     = event_time_range($time_text);

        if ($range) {
            $from = strtotime(date('Y-m-d', $start_ts) . ' ' . $range['start']);
            if ($range['end'] !== null) {
                $to = strtotime(date('Y-m-d', $end_ts) . ' ' . $range['end']);
                if ($to <= $from) $to = strtotime('+1 day', $to);   // "8pm - 1am"
            } else {
                $to = strtotime('+2 hours', $from);                   // "6pm till late"
            }
            $dates = date('Ymd\THis', $from) . '/' . date('Ymd\THis', $to);
        } else {
            $dates = date('Ymd', $start_ts) . '/' . date('Ymd', strtotime('+1 day', $end_ts));
        }

        $details = implode("\n\n", array_filter(array(
            $time_text !== '' ? 'Time: ' . $time_text : '',
            trim((string) $about),
            (string) $link,
        ), 'strlen'));

        $query = array(
            'action'   => 'TEMPLATE',
            'text'     => (string) ($event->event_name ?? ''),
            'dates'    => $dates,
            'details'  => $details,
            'location' => (string) (!empty($event->event_address) ? $event->event_address : 'Pearland, TX'),
        );
        if ($range) $query['ctz'] = 'America/Chicago';

        return 'https://calendar.google.com/calendar/render?' . http_build_query($query);
    }
}

if (!function_exists('billing_interval_is_one_time')) {
    /**
     * Is a plans.billing_interval value a one-off ("one-time", "once",
     * "lifetime")? billing_interval_noun() maps anything unknown to "month",
     * so a one-time plan used to print "/ month" in admin.
     */
    function billing_interval_is_one_time($interval)
    {
        $key = strtolower(trim(str_replace(array('_', ' '), '-', (string) $interval)));
        return in_array($key, array('one-time', 'onetime', 'once', 'one-off', 'lifetime'), true);
    }
}

if (!function_exists('plan_term_copy')) {
    /**
     * The billing line under a package price on /pricing-plan, so every card
     * says plainly whether it renews.
     *
     *   business-listing  → "per month · renews only if you choose auto-renew"
     *   everything else   → "for 30 days · one-time, does not renew"
     *
     * Only a business-listing tier ever becomes a subscription (see
     * Usermodel::recordCartPurchase); banners, videos, add-ons and the
     * special package are one-off purchases with an end date.
     *
     * @param  object $plan a plans row
     * @return array  [price suffix, renewal note]
     */
    function plan_term_copy($plan)
    {
        $cat      = isset($plan->category) && $plan->category ? (string) $plan->category : 'business-listing';
        $interval = isset($plan->billing_interval) ? (string) $plan->billing_interval : '';
        $days     = isset($plan->duration_days) ? (int) $plan->duration_days : 0;

        // Tracker: "mention is it one time only or it will be a recurring
        // charge on card every month, or the owner will be notified". So each
        // note says what happens to the CARD, not just to the package — and
        // the recurring one promises only what Cli::renew_subscriptions()
        // does: a reminder email before the charge, a receipt after it.
        if ($cat === 'business-listing' && !billing_interval_is_one_time($interval)) {
            $noun = billing_interval_noun($interval);
            return array(
                'per ' . $noun,
                // Auto-renew starts ticked at checkout (client decision,
                // 2026-10); the customer can untick it for a single term.
                'Renews every ' . $noun . ': your card is charged each ' . $noun . ' until you cancel, and we email you '
                    . 'before each charge. Prefer one ' . $noun . ' only? Untick auto-renew at checkout.',
            );
        }

        $span = $days === 7 ? '1 week' : ($days > 0 && $days % 30 === 0
            ? ($days / 30) . ' month' . ($days === 30 ? '' : 's')
            : ($days > 0 ? $days . ' days' : ''));

        return array(
            $span !== '' ? 'for ' . $span : 'one-time',
            'One-time charge: your card is charged once. It runs for the term you buy, then ends; it never renews or charges again.',
        );
    }
}

if (!function_exists('listing_video_urls')) {
    /**
     * The video links in `listings.listing_video`, one entry each.
     *
     * The forms say "one per line, or separated by commas" (and the admin
     * preview splits that way), but the listing page split on commas only, so
     * two links on two lines became one broken embed. One parser for the page,
     * the forms and the per-plan video limit.
     *
     * @return string[]
     */
    function listing_video_urls($text)
    {
        $parts = preg_split('/[\s,]+/', trim((string) $text));
        return array_values(array_filter((array) $parts, 'strlen'));
    }
}

if (!function_exists('cap_lock_note')) {
    /**
     * The "locked on your package" hint under a listing field the member's
     * plan does not include (member add + edit forms). '' when allowed.
     */
    function cap_lock_note($allowed, $what = '')
    {
        if ($allowed) return '';
        return '<small class="form-text text-warning" style="display:block;margin-top:4px;">'
            . '&#128274; ' . ($what !== '' ? e($what) . (preg_match('/^(An?|The) /', $what) ? ' is not' : ' are not') : 'Not') . ' included in your current package. '
            . '<a href="' . base_url('pricing-plan') . '">Upgrade to unlock</a>.</small>';
    }
}

if (!function_exists('ad_price_from')) {
    /**
     * The lowest monthly price on sale for a group of ad packages — "$20" —
     * read from `plans`, for the "from $X a month" lines in the promo bands.
     *
     * Those lines were typed into nine views as "$20", so the first price
     * change at /admin/pricing made the site quote one figure on the page and
     * another on /pricing-plan and on the phone (tracker: "the pricing that I
     * am providing is different from calls"). Two of them were wrong already:
     * no category banner and no top banner has ever cost $20.
     *
     * @param  string|array $scope 'ads' (every banner and video package),
     *                             a plans.category, or a list of plans.slug
     * @return string '$20' / '$19.50', or '' when nothing in scope is on sale
     *                (callers then drop the price from the sentence)
     */
    function ad_price_from($scope = 'ads')
    {
        static $cache = array();
        $key = is_array($scope) ? 'slugs:' . implode(',', $scope) : (string) $scope;
        if (isset($cache[$key])) return $cache[$key];

        $CI =& get_instance();
        $price = NULL;
        try {
            $CI->db->select_min('price', 'p')->from('plans')
                ->where('is_active', 1)
                ->where('price >', 0)
                // "a month": only packages sold by the month, so a one-week
                // add-on can't make the sentence quote a weekly price.
                ->where('duration_days', 30);
            if (is_array($scope)) {
                $CI->db->where_in('slug', $scope);
            } elseif ($scope === 'ads') {
                $CI->db->where_in('category', array('site-wide-banners', 'category-banners', 'site-wide-videos', 'category-videos'));
            } else {
                $CI->db->where('category', $scope);
            }
            $row = $CI->db->get()->row();
            $price = $row && $row->p !== NULL ? (float) $row->p : NULL;
        } catch (Exception $e) {
            $price = NULL;   // PHP 8.1+ throws on a missing column; the copy degrades
        }

        if ($price === NULL || $price <= 0) return $cache[$key] = '';
        return $cache[$key] = '$' . (floor($price) == $price ? number_format($price, 0) : number_format($price, 2));
    }
}

if (!function_exists('enquiry_department_email')) {
    /**
     * The mailbox a department's enquiries should be delivered to, if one is set.
     *
     * Read from the `enquiry_department_emails` setting at /admin/setting: one
     * `Department = address` pair per line. A department with no line here (or
     * no setting at all) returns '' and the caller falls through
     * form_recipient_email() to the site-wide address, so this is purely an
     * opt-in refinement — nothing breaks by leaving it blank.
     *
     * @param  string $department
     * @return string '' when the department has no address of its own.
     */
    function enquiry_department_email($department)
    {
        $department = trim((string) $department);
        if ($department === '') return '';

        $raw = trim((string) site_setting('enquiry_department_emails', ''));
        if ($raw === '') return '';

        foreach (preg_split('/[\r\n]+/', $raw) as $line) {
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) continue;

            if (strcasecmp(trim($parts[0]), $department) !== 0) continue;

            $address = trim($parts[1]);

            return filter_var($address, FILTER_VALIDATE_EMAIL) ? $address : '';
        }

        return '';
    }
}

if (!function_exists('asset_css')) {
    /**
     * Cache-busted URL for a stylesheet this repo ships.
     *
     * Replaces the hand-written `?v=N` numbers. Those were maintained by
     * hand, and three fixes have now shipped that never reached a returning
     * visitor because the file changed and the number didn't:
     *
     *   directory.css     fixed once already (see the 2026-08-04 entry)
     *   site-header.css   a doubled dropdown caret and an unstyled search box
     *   site-footer.css   the white strip above the footer the client reported
     *   promo-cards.css   still on ?v=1 at four of its five call sites
     *
     * The file's own mtime is the version, so the key changes exactly when
     * the bytes do and there is nothing left to remember. Any `?query` on the
     * way in is dropped, so a caller that still passes `...css?v=3` gets the
     * mtime rather than a doubled query string. Falls back to the bare URL
     * when the file is not on disk.
     *
     * @param  string $path web-root-relative, e.g. 'assets/css/directory.css'
     * @return string
     */
    function asset_css($path)
    {
        $path = ltrim((string) $path, '/');
        $q    = strpos($path, '?');
        if ($q !== false) {
            $path = substr($path, 0, $q);
        }
        if ($path === '') {
            return base_url();
        }

        $abs = rtrim(FCPATH, '/\\') . '/' . $path;

        return is_file($abs)
            ? base_url($path) . '?v=' . filemtime($abs)
            : base_url($path);
    }
}

if (!function_exists('duplicate_listing_enabled')) {
    /**
     * Is the "create duplicate listing" tool switched on?
     *
     * Off by default. The tool copies an existing listing row wholesale, and
     * sitting one click from "Create listing from scratch" it was an easy
     * mis-click away from a duplicate business record — the directory already
     * carries enough of those that the homepage de-duplicates by name (see
     * User::index). The code is left in place rather than deleted; this flag
     * is what hides it.
     *
     * Note the inverted default: every `service_*_enabled` key defaults to '1'
     * so an un-migrated site keeps working, but this one has to be switched on
     * deliberately. Flip it at /admin/setting.
     */
    function duplicate_listing_enabled()
    {
        return site_setting('feature_duplicate_listing_enabled', '0') === '1';
    }
}

if (!function_exists('us_date')) {
    /**
     * A stored date, formatted the way the audience reads dates.
     *
     * Pearland411 is a Texas directory, but the content pages printed
     * `j M Y` ("5 Aug 2026") and the blog index printed `d, M Y`
     * ("05, Aug 2026") — neither of which is how anybody writes a date in the
     * US, and the second is not how anybody writes one anywhere.
     *
     * Just as important, it returns '' rather than a wrong date for input the
     * old calls turned into "01 Jan 1970": NULL, an empty string and MySQL's
     * zero date all reach `strtotime()` as FALSE, and `date($fmt, FALSE)`
     * cheerfully prints the epoch. Callers test the result and omit the line.
     *
     *   us_date($blog->created_at)              → "Aug 5, 2026"
     *   us_date($blog->created_at, 'long')      → "August 5, 2026"
     *   us_date($blog->created_at, 'numeric')   → "08/05/2026"
     *   us_date($blog->created_at, 'M')         → "Aug"      (any date() string)
     *   us_date('0000-00-00 00:00:00')          → ""
     *
     * @param  mixed  $value  date string or unix timestamp
     * @param  string $format named style, or a raw date() format
     * @return string
     */
    function us_date($value, $format = 'M j, Y')
    {
        $ts = us_date_ts($value);
        if ($ts === false) {
            return '';
        }

        $named = array(
            'short'    => 'M j, Y',        // Aug 5, 2026
            'long'     => 'F j, Y',        // August 5, 2026
            'numeric'  => 'm/d/Y',         // 08/05/2026
            'datetime' => 'M j, Y g:i A',  // Aug 5, 2026 2:30 PM
        );

        return date(isset($named[$format]) ? $named[$format] : $format, $ts);
    }
}

if (!function_exists('us_date_ts')) {
    /**
     * Timestamp for a stored date, or FALSE when there isn't a real one.
     *
     * MySQL's zero date is spelled several ways depending on the column type
     * and how it was written, and `strtotime('0000-00-00')` is FALSE on some
     * builds and a negative timestamp on others — so the zero dates are
     * matched before they get near strtotime().
     */
    function us_date_ts($value)
    {
        if ($value === null || $value === '' || $value === false) {
            return false;
        }
        if (is_int($value) || ctype_digit((string) $value)) {
            $ts = (int) $value;
            return $ts > 0 ? $ts : false;
        }

        $str = trim((string) $value);
        if ($str === '' || strpos($str, '0000-00-00') === 0) {
            return false;
        }

        $ts = strtotime($str);
        return ($ts === false || $ts <= 0) ? false : $ts;
    }
}

if (!function_exists('external_url')) {
    /**
     * An operator-supplied link, made safe to put in an href.
     *
     * Settings are stored verbatim, so a social URL saved as
     * "www.facebook.com/pearland411" — which is how people type them — is a
     * *relative* href. The browser resolves it against the current page and
     * the visitor lands back on Pearland411. That is the reported "clicking
     * the social media links returns you to the homepage": the links were
     * there and pointed at the right place, they just had no scheme.
     *
     * Adds https:// when a scheme is missing, and returns '' for anything that
     * is not an ordinary web link, so a stored "javascript:" can never reach an
     * href.
     *
     * @param  mixed $url
     * @return string '' when there is nothing safe to link to.
     */
    function external_url($url)
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }

        // Protocol-relative is already resolvable; give it a scheme anyway so
        // the link works from a page served over http during setup.
        if (strpos($url, '//') === 0) {
            $url = 'https:' . $url;
        }

        if (preg_match('#^[a-z][a-z0-9+.\-]*:#i', $url)) {
            $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
            if (!in_array($scheme, array('http', 'https', 'mailto', 'tel'), TRUE)) {
                return '';
            }
            return $url;
        }

        return 'https://' . ltrim($url, '/');
    }
}

if (!function_exists('listing_gallery_paths')) {
    /**
     * The gallery image paths stored on a listing, as a clean list.
     *
     * `listings.listing_image_gallery` holds a JSON array of paths. Four places
     * decoded it by hand — the two admin views, the public listing page and the
     * member's edit form — and the admin update path did not decode it at all,
     * which is how adding one photograph wiped the rest of the gallery. One
     * function so the read and the write agree on what the column contains.
     *
     * Returns [] for NULL, '', malformed JSON, or a JSON scalar.
     *
     * @param  mixed $value Raw column value.
     * @return array<int,string>
     */
    function listing_gallery_paths($value)
    {
        if (empty($value)) {
            return array();
        }

        $decoded = json_decode((string) $value, true);
        if (!is_array($decoded)) {
            return array();
        }

        return array_values(array_filter(array_map('trim', array_map('strval', $decoded)), 'strlen'));
    }
}

if (!function_exists('ad_zone_preview')) {
    /**
     * The preview thumbnail for one sellable ad zone, as a web path.
     *
     * /admin/ads-price shows a thumbnail of every zone so an operator can see
     * what they are selling. It read `ads_prices.img` and printed
     * `base_url($row->img)` unconditionally — but only the handful of rows
     * created by hand before the slot_key migrations ever had an image. Every
     * zone seeded by 0006 / 0017 / 0019 / 0024 has NULL there, and
     * `base_url(NULL)` is the site root, so the browser fetched the homepage as
     * an image and drew a broken-image icon. That is the empty "Ads Preview"
     * column in the report.
     *
     * Resolution order:
     *   1. An uploaded image, when the file is actually on disk. A row whose
     *      upload was later deleted falls through rather than breaking again.
     *   2. assets/images/ads/previews/<slot_key>.svg — the zone drawn in its
     *      real position on a page wireframe, labelled with its pixel size.
     *   3. assets/images/ads/previews/zone-<WxH>.svg — same drawing, placed by
     *      shape, for a pricing row an operator added by hand.
     *   4. zone-generic.svg.
     *
     * All of these are written by `python3 scripts/generate_brand_art.py`.
     *
     * @param  object|array|null $row An ads_prices row (or anything with
     *                                slot_key / size / img).
     * @return string Web path relative to the docroot, for base_url().
     */
    function ad_zone_preview($row)
    {
        $get = function ($key) use ($row) {
            if (is_array($row))  return isset($row[$key])  ? (string) $row[$key]  : '';
            if (is_object($row)) return isset($row->$key)  ? (string) $row->$key  : '';
            return '';
        };

        $base = defined('FCPATH') ? FCPATH : rtrim(getcwd(), '/\\') . '/';

        // 1. A real upload. Guard the path: `img` is operator-supplied and is
        // about to be handed to base_url(), so a stored "../" must not escape.
        $img = trim($get('img'));
        if ($img !== '' && strpos($img, '..') === false && !preg_match('#^(?:[a-z][a-z0-9+.-]*:)?//#i', $img)) {
            if (is_file($base . ltrim($img, '/'))) {
                return ltrim($img, '/');
            }
        }

        $dir = 'assets/images/ads/previews/';

        // 2. The zone's own drawing, keyed by slot_key.
        $slot = strtolower(trim($get('slot_key')));
        if ($slot !== '' && preg_match('/^[a-z0-9\-]+$/', $slot) && is_file($base . $dir . $slot . '.svg')) {
            return $dir . $slot . '.svg';
        }

        // 3. By size, for a pricing row we didn't seed.
        if (preg_match('/^\s*(\d{2,4})\s*[xX\*]\s*(\d{2,4})\s*$/', $get('size'), $m)) {
            $by_size = $dir . 'zone-' . (int) $m[1] . 'x' . (int) $m[2] . '.svg';
            if (is_file($base . $by_size)) {
                return $by_size;
            }
        }

        // 4. Something is always better than a broken image.
        return $dir . 'zone-generic.svg';
    }
}

if (!function_exists('user_option_label')) {
    /**
     * How one member account reads in an admin "choose a user" dropdown.
     *
     * The dropdowns used to print `user.name` alone. Accounts get named after
     * the business rather than the person, and test rows never get cleaned up,
     * so the Edit Listing owner picker was a list like "10 dollars Galveston
     * shuttle", "ACT Electrical", "Angel Coliflores- Test" with no way to tell
     * a real customer from a leftover — which is the reported "are these users
     * all allowed to publish content? Kindly review each user".
     *
     * The email identifies the account, and the listing count is what actually
     * separates a live customer from a test row. Both come from
     * Adminmodel::getActiveUsersForDropdown(); either may be absent, and the
     * label degrades to the bare name.
     *
     * Returns PLAIN text — callers escape it, like every other label.
     *
     * @param  object $row
     * @return string
     */
    function user_option_label($row)
    {
        $name = trim((string) (isset($row->name) ? $row->name : ''));
        if ($name === '') {
            $name = 'Unnamed account';
        }

        $label = $name;

        $email = trim((string) (isset($row->email_id) ? $row->email_id : ''));
        if ($email !== '' && strcasecmp($email, $name) !== 0) {
            $label .= ' — ' . $email;
        }

        if (isset($row->listing_count)) {
            $n = (int) $row->listing_count;
            $label .= ' · ' . ($n === 0 ? 'no listings' : $n . ' listing' . ($n === 1 ? '' : 's'));
        }

        // Suspended at /admin/users: still listed, so a record it owns keeps
        // showing its owner, but nobody picks it for new content by mistake.
        if (!empty($row->banned)) {
            $label .= ' · SUSPENDED';
        }

        return $label;
    }
}

if (!function_exists('listing_is_claimed')) {
    /**
     * Has the business owner got this listing?
     *
     * Not `!empty($listing->user_id)`. Every listing has an owner — the CSV
     * import and the admin form both require one — and the imported directory
     * belongs to a handful of data-entry logins, so that test called every
     * listing claimed and the "Claim This Business" button never showed.
     * `listings.is_claimed` (migration 0037) is set by an approved claim, by a
     * member adding their own business, or by an admin. Before 0037 has run,
     * the old rule applies so nothing changes.
     *
     * @param  object|array|null $listing a listings row
     * @return bool
     */
    function listing_is_claimed($listing)
    {
        $listing = (object) $listing;
        if (property_exists($listing, 'is_claimed')) {
            return (int) $listing->is_claimed === 1;
        }
        return !empty($listing->user_id);
    }
}

if (!function_exists('claim_roles')) {
    /**
     * "Your role at the business" on the claim form, key => label. The key is
     * what claim_listings.claim_role stores.
     *
     * @return array<string,string>
     */
    function claim_roles()
    {
        return array(
            'owner'    => 'Owner',
            'co_owner' => 'Co-owner / partner',
            'manager'  => 'Manager',
            'employee' => 'Employee (authorised by the owner)',
            'agency'   => 'Marketing agency / representative',
            'other'    => 'Other',
        );
    }
}

if (!function_exists('claim_verification_methods')) {
    /**
     * "How can we verify you?" on the claim form, key => label. The key is
     * what claim_listings.claim_verification stores.
     *
     * @return array<string,string>
     */
    function claim_verification_methods()
    {
        return array(
            'phone'    => 'Call me on the business phone number on the listing',
            'email'    => 'Email me at an address on the business’s own domain',
            'document' => 'I’ll upload a document (business license, utility bill, EIN letter)',
            'website'  => 'I can add a note to the business website or social page',
            'other'    => 'Something else — I’ll explain below',
        );
    }
}

if (!function_exists('claim_status_label')) {
    /**
     * What claim_listings.claim_status means, for people. 3 is "more
     * information requested" (migration 0037).
     */
    function claim_status_label($status)
    {
        switch ((int) $status) {
            case 1:  return 'Approved';
            case 2:  return 'Not approved';
            case 3:  return 'More information needed';
            default: return 'Pending review';
        }
    }
}

if (!function_exists('listing_social_links')) {
    /**
     * The business's own social links, keyed facebook / instagram / twitter /
     * youtube / linkedin, each made safe by external_url() ('' when unset).
     *
     * The listing columns come from migration 0037. Before them the listing
     * page used the OWNER'S profile links, which put a data-entry login's
     * Facebook page on every business it owned. $profile (the owner's
     * user_facebook / user_twitter / user_youtube) is only used for a listing
     * the owner has actually claimed, and only where the listing has nothing.
     *
     * @param  object      $listing
     * @param  object|null $profile owner's `user` row, or NULL
     * @return array<string,string>
     */
    function listing_social_links($listing, $profile = NULL)
    {
        $out = array();
        foreach (array('facebook', 'instagram', 'twitter', 'youtube', 'linkedin') as $net) {
            $value = isset($listing->{'listing_' . $net}) ? (string) $listing->{'listing_' . $net} : '';
            if ($value === '' && $profile && listing_is_claimed($listing) && isset($profile->{'user_' . $net})) {
                $value = (string) $profile->{'user_' . $net};
            }
            $out[$net] = external_url($value);
        }
        return $out;
    }
}

if (!function_exists('activity_record')) {
    /**
     * Count one analytics event from a view or partial (see
     * libraries/Activity.php). Views may not load libraries themselves — their
     * $this is the CI_Loader — so this goes through get_instance().
     *
     * @return bool
     */
    function activity_record($type, $id, $event)
    {
        $CI =& get_instance();
        if (!isset($CI->activity)) {
            $CI->load->library('activity');
        }
        return $CI->activity->record($type, $id, $event);
    }
}

if (!function_exists('service_price_label')) {
    /**
     * How a services-catalog price reads: array(amount, unit), plain text —
     * e.g. ('From $995', 'per project') or ('$39', 'a month'). NULL when the
     * service has no list price ("Custom quote"). `price_from` (migration 0038)
     * marks a starting price, as the packages document lists most of them.
     *
     * @param  object $service a services row
     * @return array|null
     */
    function service_price_label($service)
    {
        if (!isset($service->base_price) || $service->base_price === NULL || ($service->price_unit ?? 'custom') === 'custom') {
            return NULL;
        }
        $units = array(
            'monthly'  => 'a month',
            'annual'   => 'a year',
            'hourly'   => 'an hour',
            'project'  => 'per project',
            'one_time' => 'one-time',
        );
        $amount = '$' . number_format((float) $service->base_price, 0);
        if (!empty($service->price_from)) {
            $amount = 'From ' . $amount;
        }
        $unit = $units[(string) $service->price_unit] ?? str_replace('_', ' ', (string) $service->price_unit);
        return array($amount, $unit);
    }
}

if (!function_exists('listing_is_verified')) {
    /**
     * Does the listing show the Verified badge? Claimed by its owner AND the
     * owner's plan includes the badge (the listing packages, migration 0038:
     * Starter and up). Before 0038 a claimed listing was verified.
     * Cached per owner for the request, so a page of cards costs one query
     * per distinct owner.
     *
     * @param  object $listing a listings row (needs user_id + is_claimed)
     * @return bool
     */
    function listing_is_verified($listing)
    {
        static $owners = array();
        if (!listing_is_claimed($listing)) return FALSE;
        $uid = (int) ($listing->user_id ?? 0);
        if (!isset($owners[$uid])) {
            $owners[$uid] = (bool) get_instance()->usermodel->ownerHasVerifiedBadge($uid);
        }
        return $owners[$uid];
    }
}
