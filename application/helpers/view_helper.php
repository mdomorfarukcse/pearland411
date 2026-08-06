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

if (!function_exists('sales_representatives')) {
    /**
     * The named sales reps a caller can be put through to.
     *
     * The signup form (views/register.php) has carried this list inline since
     * the site launched; the callback panel on /pricing-plan needs the same
     * names, and two hand-kept copies is how one of them goes stale. Editable
     * without a deploy through the `sales_representatives` setting in
     * /admin/setting — one name per line, or comma separated.
     *
     * @return string[]
     */
    function sales_representatives()
    {
        $raw = trim((string) site_setting('sales_representatives', ''));

        if ($raw !== '') {
            $names = preg_split('/[\r\n,]+/', $raw);
        } else {
            $names = array(
                'Joshua Hari', 'Gloria Bennett-Green', 'Shawn House', 'Melinda Trussell',
                'BJ Thomas', 'Melissa Kelton', 'Rashad Ramkissoon', 'Rafaela Ruffino',
                'Eddie Noschese',
            );
        }

        $out = array();
        foreach ($names as $name) {
            $name = trim((string) $name);
            if ($name !== '') $out[] = $name;
        }

        return $out;
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
