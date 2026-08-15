#!/usr/bin/env python3
"""
Generate the advertising inventory documentation from the source, so it can't
drift from what the pages actually render:

  docs/AD_INVENTORY.md
      Every public page, the zones it carries, in page order, with the
      slot_key, the pixel box and the catalogue price of each.

  docs/images/ad-layout-<shape>.svg
      The three page shells, with every zone drawn where it sits and labelled
      with its size. This is the picture to hand an advertiser.

Run from the repository root:

    python3 scripts/ad_inventory.py

It reads application/controllers/User.php and application/views/*.php and
writes only under docs/. Deterministic and safe to re-run.
"""

import io
import os
import re

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

# ---------------------------------------------------------------------------
# The zone catalogue. Mirrors the seeds in database/migrations/0006, 0017,
# 0019 and 0024 — those files are the source of truth for the live rows; this
# is what the documentation quotes.
# ---------------------------------------------------------------------------
ZONES = {
    # slot_key                    (label,                       size,      $/day, migration)
    'top-banner-site-wide':      ('Top Banner (Site Wide)',     '700x100', 75.00, '0017'),
    'middle-banner-category':    ('Middle Banner (Category)',   '900x100', 25.00, '0017'),
    'bottom-banner-site-wide':   ('Bottom Banner (Site Wide)',  '900x100', 45.00, '0017'),
    'right-side-banner-1':       ('#1 Right Side Banner',       '300x250', 25.00, '0017'),
    'right-side-banner-2':       ('#2 Right Side Banner',       '300x250', 25.00, '0017'),
    'right-side-banner-3':       ('#3 Right Side Banner',       '300x250', 20.00, '0017'),
    'right-panel-skyscraper':    ('Right Panel Skyscraper',     '295x600', 20.00, '0017'),
    'top-banner-category':       ('Category Header Banner',     '900x100', 25.00, '0024'),
    'bottom-banner-category':    ('Bottom Banner (Category)',   '900x100', 25.00, '0019'),
    'right-banner-1':            ('#1 Right Banner (Category)', '300x250', 25.00, '0019'),
    'right-banner-2':            ('#2 Right Banner (Category)', '300x250', 25.00, '0019'),
    'footer-banner':             ('Footer banner',              '970x90',  29.00, '0006'),
    'home-hero':                 ('Homepage hero',              '728x200', 99.00, '0006'),
    'home-sidebar':              ('Homepage sidebar',           '300x250', 49.00, '0006'),
    'listing-top':               ('Listing list — top',    '728x90',  59.00, '0006'),
    'listing-inline':            ('Listing list — inline', '728x90',  49.00, '0006'),
    'listing-sidebar':           ('Listing detail sidebar',     '300x250', 49.00, '0006'),
    'events-sidebar':            ('Events page sidebar',        '300x250', 39.00, '0006'),
    'news-sidebar':              ('News article sidebar',       '300x250', 39.00, '0006'),
}

# What the view keys mean, in the order a visitor meets them going down a page.
KEY_ORDER = ['top', 'inline', 'side_1', 'side_2', 'side_3', 'side_sky', 'bottom']
KEY_LABEL = {
    'top':      'Leaderboard, above the content',
    'inline':   'In-content banner',
    'side_1':   'Sidebar box 1',
    'side_2':   'Sidebar box 2',
    'side_3':   'Sidebar box 3',
    'side_sky': 'Sidebar skyscraper',
    'bottom':   'Leaderboard, page foot',
}

SHAPES = {
    'full':   ['top', 'inline', 'side_1', 'side_2', 'side_sky', 'bottom'],
    'wide':   ['top', 'inline', 'bottom'],
    'banner': ['top', 'bottom'],
}
CONTENT_SLOTS = {
    'top': 'top-banner-site-wide', 'inline': 'middle-banner-category',
    'side_1': 'right-side-banner-1', 'side_2': 'right-side-banner-2',
    'side_sky': 'right-panel-skyscraper', 'bottom': 'bottom-banner-site-wide',
}
SHAPE_SLOTS = {
    'top': 'top-banner-site-wide', 'inline': 'middle-banner-category',
    'bottom': 'bottom-banner-site-wide', 'side_1': 'right-side-banner-1',
    'side_2': 'right-side-banner-2', 'side_3': 'right-side-banner-3',
    'side_sky': 'right-panel-skyscraper',
}

# URL for each controller method. Most fall out of the catch-all
# ($route['(:any)'] = 'User/$1' plus translate_uri_dashes), so only the
# parameterised and explicitly-routed ones are listed.
ROUTES = {
    'index': '/', 'about_us': '/about-us', 'all_listing': '/all-listing',
    'listing_details': '/listing-details/<slug>', 'all_category': '/all-category',
    'all_blog': '/all-blog', 'blog_details': '/blog-details/<slug>',
    'event': '/event', 'event_details': '/event-details/<slug>',
    'news': '/news', 'news_details': '/news-details/<slug>',
    'videos': '/videos', 'video_details': '/video-details/<slug>',
    'classifieds': '/classifieds', 'classifieds_details': '/classifieds-details/<slug>',
    'coupons': '/coupons', 'coupon_details': '/coupons/<id>',
    'all_products': '/all-products', 'product_details': '/product-details/<slug>',
    'house_details': '/house-details/<slug>', 'rental_details': '/rental-details/<slug>',
    'services': '/services', 'service_details': '/services/<slug>',
    'request_quote': '/request-quote', 'claim_listing': '/claim-listing/<slug>',
    'pricing_plan': '/pricing-plan', 'post_your_ads': '/post-your-ads',
    'contact_us': '/contact-us', 'privacy_policy': '/privacy-policy',
    'terms_of_use': '/terms-of-use', 'search': '/search?q=',
    'faq': '/faq', 'login': '/login', 'register': '/register',
}

# Pages deliberately left ad-free, and why.
EXCLUDED = [
    ('/checkout', 'cart_checkout.php',
     'Payment. An ad beside a card form costs conversions and invites phishing comparisons.'),
    ('/checkout-success', 'checkout_success_view.php',
     'The receipt. Same reason.'),
    ('/login, /register', 'login.php, register.php',
     'Someone typing a password should not be looking at an ad — it reads as less trustworthy, '
     'and the traffic is repeat users who never click one.'),
    ('/privacy-policy, /terms-of-use', 'privacy_policy.php, terms_of_use.php',
     'The pages a payment processor, an app reviewer or a cautious customer opens to check the '
     'site is legitimate. Advertising on them works against that.'),
    ('/pricing-plan', 'pricing_plan.php',
     'This page sells the ad packages. House ads pitching ad packages on the page that sells ad '
     'packages is circular — the content is the pitch.'),
    ('/claim-listing/<slug>', 'claim_listing.php',
     'A business owner proving they own a listing. Short, high-intent, and not somewhere to sell '
     'a banner.'),
    ('/user/*', 'user_* views',
     'The member dashboard is not public. Advertising to a customer inside the product they '
     'just bought is a separate decision.'),
    ('/admin/*', 'admin views', 'Staff only.'),
]


def read(rel):
    with io.open(os.path.join(ROOT, rel), encoding='utf-8') as fh:
        return fh.read()


def write(rel, body):
    path = os.path.join(ROOT, rel)
    os.makedirs(os.path.dirname(path), exist_ok=True)
    with io.open(path, 'w', encoding='utf-8') as fh:
        fh.write(body if body.endswith('\n') else body + '\n')
    print('wrote', rel)


# ---------------------------------------------------------------------------
# 1. Parse the controller: method -> {view key: slot_key}, and the views it loads
# ---------------------------------------------------------------------------
def parse_controller():
    src = read('application/controllers/User.php')
    methods = {}

    starts = [(m.group(1), m.end()) for m in re.finditer(r'\n\tpublic function (\w+)\(', src)]
    for i, (name, start) in enumerate(starts):
        end = starts[i + 1][1] if i + 1 < len(starts) else len(src)
        body = src[start:end]

        slots = {}
        for sm in re.finditer(r"pageAdSlots\('(\w+)'\)", body):
            for key in SHAPES[sm.group(1)]:
                slots[key] = SHAPE_SLOTS[key]
        if 'contentAdSlots()' in body:
            slots.update(CONTENT_SLOTS)
        for am in re.finditer(r"\$data\['(?:home_)?ad_slots'\]\s*=\s*array\((.*?)\);", body, re.S):
            inner = am.group(1)
            for k, v in re.findall(r"'(\w+)'\s*=>\s*'([\w\-]+)'", inner):
                slots[k] = v
            # the directory's bottom zone is chosen at runtime by category
            for k, a, b in re.findall(r"'(\w+)'\s*=>\s*\$\w+\s*\?\s*'([\w\-]+)'\s*:\s*'([\w\-]+)'", inner):
                slots[k] = '%s / %s' % (a, b)

        views = re.findall(r"load->view\('([\w\-\.]+)'", body)
        methods[name] = {
            'slots': slots,
            'views': [v for v in views if v not in ('header', 'footer')],
            # views/footer.php carries `footer-banner` and is loaded by every
            # page except the self-contained homepage, so that zone belongs in
            # every page's list — leaving it out under-counted the inventory by
            # one placement per page.
            'footer': 'footer' in views,
        }
    return methods


# ---------------------------------------------------------------------------
# 2. Parse a view: the slot references it renders, in page order
# ---------------------------------------------------------------------------
SLOT_REF = re.compile(
    r"\$slot\s*=\s*(?:"
    r"\$ad_slots\['(?P<key>\w+)'\]"            # $ad_slots['top']
    r"|'(?P<literal>[\w\-]+)'"                  # 'footer-banner'
    r"|\$\w+\s*!==\s*''\s*\?\s*'(?P<cond>[\w\-]+)'"  # $cat ? 'top-banner-category' : ...
    r")"
)


def parse_view(rel):
    if not os.path.isfile(os.path.join(ROOT, rel)):
        return []
    src = read(rel)
    out = []
    for m in SLOT_REF.finditer(src):
        if m.group('key'):
            out.append(('key', m.group('key')))
        elif m.group('literal'):
            out.append(('slot', m.group('literal')))
        elif m.group('cond'):
            out.append(('slot', m.group('cond')))
    return out


# ---------------------------------------------------------------------------
# 3. The page table
# ---------------------------------------------------------------------------
def build_pages(methods):
    pages = []
    for name, info in methods.items():
        if name not in ROUTES:
            continue
        seen, zones = set(), []
        for view in info['views']:
            for kind, value in parse_view('application/views/%s.php' % view):
                slot = info['slots'].get(value, value) if kind == 'key' else value
                if kind == 'key' and value not in info['slots']:
                    continue
                label = KEY_LABEL.get(value, 'In-page banner') if kind == 'key' else None
                if (slot, label) in seen:
                    continue
                seen.add((slot, label))
                zones.append((value if kind == 'key' else '—', slot, label))

        if info['footer']:
            for kind, value in parse_view('application/views/footer.php'):
                if kind == 'slot' and (value, 'Footer banner strip') not in seen:
                    seen.add((value, 'Footer banner strip'))
                    zones.append(('—', value, 'Footer banner strip'))

        if zones:
            pages.append((ROUTES[name], name, info['views'], zones))

    order = list(ROUTES.values())
    pages.sort(key=lambda p: order.index(p[0]))
    return pages


def zone_row(slot):
    """(size, price, name) for a slot_key, tolerating the runtime-choice pair."""
    if ' / ' in slot:
        first = slot.split(' / ')[0]
        label, size, cost, _ = ZONES.get(first, ('?', '?', 0, ''))
        return size, cost, label + ' (category pages) / site-wide'
    label, size, cost, _ = ZONES.get(slot, ('Unknown zone', '?', 0.0, ''))
    return size, cost, label


def markdown(pages):
    total_zones = sum(len(z) for _, _, _, z in pages)

    lines = []
    a = lines.append
    a('# Advertising inventory')
    a('')
    a('Every public page, the ad zones it renders and the pixel box of each.')
    a('')
    a('**Generated** by `python3 scripts/ad_inventory.py` from '
      '`application/controllers/User.php` and the views. Re-run it after adding '
      'or moving a zone; do not hand-edit this file.')
    a('')
    a('- **%d public pages** carry advertising, **%d zone placements** in total.' % (len(pages), total_zones))
    a('- Every `slot_key` below is also a `plans.slug` on `/pricing-plan`, so buying the package')
    a('  fills the position. That is why the same key repeats across pages: a *site-wide*')
    a('  purchase is meant to be site-wide.')
    a('- An unsold zone shows a **house creative** ("advertise here") — but only the first')
    a('  `ads_house_max_per_page` of them (default **2**, at `/admin/setting`). Past that budget an')
    a('  unsold zone renders nothing at all, so a page never looks like a wall of house ads. Pages')
    a('  whose search or category comes back empty use one. The skyscraper is held back until sold')
    a('  regardless. **A sold ad is never affected by any of this** — a real booking always wins')
    a('  its position, on every device.')
    a('- The global off switch is `service_ads_enabled` at `/admin/setting`; with it off every')
    a('  zone on this page renders nothing at all, not an empty box.')
    a('')

    # ---- the size summary --------------------------------------------------
    a('## Banner sizes')
    a('')
    a('| Size | Zone | `slot_key` | $/day | Where it appears |')
    a('|---|---|---|---|---|')
    used = {}
    for _, _, _, zones in pages:
        for _, slot, _ in zones:
            for part in slot.split(' / '):
                used[part] = used.get(part, 0) + 1
    for slot in sorted(used, key=lambda s: (-used[s], s)):
        label, size, cost, _mig = ZONES.get(slot, ('Unknown', '?', 0.0, ''))
        a('| **%s** | %s | `%s` | $%.2f | %d page%s |'
          % (size, label, slot, cost, used[slot], '' if used[slot] == 1 else 's'))
    a('')

    # ---- per page ----------------------------------------------------------
    a('## Zones per page')
    a('')
    a('| # | Page | View | Zones | Sizes |')
    a('|---|---|---|---|---|')
    for i, (route, method, views, zones) in enumerate(pages, 1):
        sizes = []
        for _, slot, _ in zones:
            sizes.append(zone_row(slot)[0])
        a('| %d | `%s` | `%s.php` | %d | %s |'
          % (i, route, views[0], len(zones), ', '.join(sizes)))
    a('')

    a('## Every placement, page by page')
    a('')
    for route, method, views, zones in pages:
        a('### `%s`' % route)
        a('')
        a('`User::%s` → `views/%s.php`' % (method, views[0]))
        a('')
        a('| Position | `slot_key` | Size | $/day |')
        a('|---|---|---|---|')
        for key, slot, label in zones:
            size, cost, name = zone_row(slot)
            pos = label or name
            a('| %s | `%s` | **%s** | $%.2f |' % (pos, slot, size, cost))
        a('')

    # ---- deliberately excluded --------------------------------------------
    a('## Pages deliberately left ad-free')
    a('')
    a('| Page | View | Why |')
    a('|---|---|---|')
    for route, view, why in EXCLUDED:
        a('| `%s` | `%s` | %s |' % (route, view, why))
    a('')

    a('## Layouts')
    a('')
    a('Three page shells cover every page above. `User::pageAdSlots($shape)` picks one.')
    a('')
    for shape, title in (('full', 'Main + sidebar'), ('wide', 'Full-width'), ('banner', 'Narrow form')):
        a('### `%s` — %s' % (shape, title))
        a('')
        a('![%s layout](images/ad-layout-%s.svg)' % (shape, shape))
        a('')

    a('## Adding a zone to a page')
    a('')
    a('1. In `User::<method>()`, before the header view loads:')
    a('   `$data[\'ad_slots\'] = $this->pageAdSlots(\'full\');` — it primes every zone in one')
    a('   query, which is the difference between one `ORDER BY RAND()` and six.')
    a('2. In the view, where the zone goes:')
    a('')
    a('   ```php')
    a('   <?php $slot = $ad_slots[\'top\'] ?? \'\';')
    a('   include APPPATH . \'views/partials/ad_slot.php\'; ?>')
    a('   ```')
    a('')
    a('   `?? \'\'` matters: `ad_slot.php` treats an empty key as a no-op, so a view rendered')
    a('   from a second controller method that forgot the map degrades to nothing instead of')
    a('   erroring.')
    a('3. Re-run `python3 scripts/ad_inventory.py` so this file matches.')
    a('')
    a('Selling a **new** position needs three things to line up, not one: a row in `ads_prices`')
    a('with the `slot_key`, a `plans` row whose `slug` is that same string, and a view that')
    a('includes it. Miss the first and nothing renders; miss the second and nobody can buy it.')
    return '\n'.join(lines)


# ---------------------------------------------------------------------------
# 4. Layout diagrams
# ---------------------------------------------------------------------------
NAVY, GREEN, BURGUNDY = '#20245B', '#79B82A', '#6D263D'
BURG_LT, BURG_DP = '#8A3D59', '#4E1A2B'
PAGE, EDGE, SOFT, LINE = '#FFFFFF', '#C7DBEF', '#D8E6F4', '#BFD4EA'
BLOCK, GROUND = '#9FC1E4', '#EDF3F9'

W, H = 1120, 900


def zone_rect(x, y, w, h, slot, note=''):
    size, cost, label = zone_row(slot)
    fs = max(13, min(26, int(min(w * 0.085, h * 0.4))))
    small = max(10, int(fs * 0.56))
    return f'''
  <g>
    <rect x="{x}" y="{y}" width="{w}" height="{h}" rx="6" fill="url(#zone)"/>
    <rect x="{x}" y="{y}" width="{w}" height="{h}" rx="6" fill="url(#sheen)"/>
    <text x="{x + w / 2}" y="{y + h / 2 - (small * 0.75 if h > 60 else 0)}" text-anchor="middle"
          dominant-baseline="central" class="zsize" font-size="{fs}">{size}</text>
    {'' if h <= 60 else f'<text x="{x + w / 2}" y="{y + h / 2 + fs * 0.85}" text-anchor="middle" dominant-baseline="central" class="zkey" font-size="{small}">{slot}</text>'}
  </g>
  {'' if h > 60 else f'<text x="{x + w - 8}" y="{y + h + 15}" text-anchor="end" class="zkey-out" font-size="11">{slot}</text>'}'''


def text_lines(x, y, w, n, gap=12):
    out = []
    for i in range(n):
        lw = w * 0.6 if i == n - 1 else w
        out.append(f'<rect x="{x}" y="{y + i * gap}" width="{lw:.0f}" height="6" rx="3" fill="{LINE}"/>')
    return ''.join(out)


def layout_svg(shape):
    pad = 28
    inner = W - 2 * pad
    body = []
    caption = {
        'full':   'Main column + sidebar — six zones',
        'wide':   'Full-width — three wide zones',
        'banner': 'Narrow form — two wide zones',
    }[shape]

    # chrome
    body.append(f'<rect x="{pad}" y="{pad}" width="{inner}" height="52" rx="6" fill="{NAVY}"/>')
    body.append(f'<rect x="{pad + 20}" y="{pad + 18}" width="128" height="16" rx="4" fill="#FFFFFF" opacity="0.85"/>')
    for i in range(5):
        body.append(f'<rect x="{W - pad - 76 - i * 88}" y="{pad + 22}" width="64" height="8" rx="4" fill="#FFFFFF" opacity="0.35"/>')

    y = pad + 68

    # top leaderboard
    body.append(zone_rect(pad, y, inner, 62, SHAPE_SLOTS['top']))
    y += 62 + 34

    # hero
    body.append(f'<rect x="{pad}" y="{y}" width="{inner}" height="104" rx="8" fill="{BLOCK}" opacity="0.7"/>')
    body.append(f'<text x="{pad + 24}" y="{y + 44}" class="wire" font-size="17">Page heading</text>')
    body.append(text_lines(pad + 24, y + 62, 420, 2))
    y += 104 + 26

    if shape == 'full':
        main_w, side_w = 748, 320
        side_x = pad + main_w + 24

        # main column
        body.append(f'<rect x="{pad}" y="{y}" width="{main_w * 0.46:.0f}" height="180" rx="6" fill="{BLOCK}"/>')
        body.append(f'<rect x="{pad + main_w * 0.5:.0f}" y="{y}" width="{main_w * 0.5:.0f}" height="20" rx="4" fill="{SOFT}"/>')
        body.append(text_lines(pad + main_w * 0.5, y + 36, main_w * 0.5, 10))

        # sidebar boxes
        body.append(zone_rect(side_x, y, side_w, 176, SHAPE_SLOTS['side_1']))
        body.append(zone_rect(side_x, y + 198, side_w, 176, SHAPE_SLOTS['side_2']))

        my = y + 200
        body.append(zone_rect(pad, my, main_w, 62, SHAPE_SLOTS['inline']))
        my += 62 + 26
        body.append(f'<rect x="{pad}" y="{my}" width="{main_w * 0.3:.0f}" height="20" rx="4" fill="{SOFT}"/>')
        body.append(text_lines(pad, my + 34, main_w * 0.44, 6))
        body.append(f'<rect x="{pad + main_w * 0.52:.0f}" y="{my}" width="{main_w * 0.48:.0f}" height="128" rx="6" fill="{BLOCK}" opacity="0.8"/>')

        my2 = my + 148
        body.append(f'<rect x="{pad}" y="{my2}" width="{main_w * 0.26:.0f}" height="20" rx="4" fill="{SOFT}"/>')
        body.append(text_lines(pad, my2 + 34, main_w * 0.92, 8))
        body.append(f'<rect x="{pad}" y="{my2 + 140}" width="{main_w * 0.32:.0f}" height="94" rx="6" fill="{BLOCK}" opacity="0.7"/>')
        body.append(f'<rect x="{pad + main_w * 0.35:.0f}" y="{my2 + 140}" width="{main_w * 0.32:.0f}" height="94" rx="6" fill="{BLOCK}" opacity="0.7"/>')
        body.append(f'<rect x="{pad + main_w * 0.7:.0f}" y="{my2 + 140}" width="{main_w * 0.3:.0f}" height="94" rx="6" fill="{BLOCK}" opacity="0.7"/>')

        body.append(zone_rect(side_x, y + 396, side_w, 246, SHAPE_SLOTS['side_sky']))
        body.append(f'<text x="{side_x + side_w / 2}" y="{y + 396 + 246 + 17}" text-anchor="middle" class="zkey-out" font-size="11">held back until sold</text>')

        y = max(my2 + 258, y + 396 + 246 + 30)

    elif shape == 'wide':
        body.append(f'<rect x="{pad}" y="{y}" width="{inner * 0.34:.0f}" height="188" rx="6" fill="{BLOCK}"/>')
        body.append(f'<rect x="{pad + inner * 0.38:.0f}" y="{y}" width="{inner * 0.62:.0f}" height="20" rx="4" fill="{SOFT}"/>')
        body.append(text_lines(pad + inner * 0.38, y + 36, inner * 0.62, 11))
        y += 206
        body.append(zone_rect(pad, y, inner, 62, SHAPE_SLOTS['inline']))
        y += 62 + 26
        body.append(f'<rect x="{pad}" y="{y}" width="{inner * 0.3:.0f}" height="20" rx="4" fill="{SOFT}"/>')
        body.append(text_lines(pad, y + 34, inner * 0.9, 7))
        y += 150

    else:  # banner
        fx, fw = pad + inner * 0.18, inner * 0.64
        body.append(f'<rect x="{fx:.0f}" y="{y}" width="{fw:.0f}" height="326" rx="8" fill="{GROUND}" stroke="{EDGE}"/>')
        body.append(f'<text x="{fx + 26:.0f}" y="{y + 40}" class="wire" font-size="16">Form</text>')
        for i in range(4):
            body.append(f'<rect x="{fx + 26:.0f}" y="{y + 64 + i * 58}" width="{fw - 52:.0f}" height="38" rx="6" fill="#FFFFFF" stroke="{EDGE}"/>')
        body.append(f'<rect x="{fx + 26:.0f}" y="{y + 292}" width="152" height="34" rx="17" fill="{GREEN}"/>')
        y += 326 + 30

    # bottom leaderboard
    body.append(zone_rect(pad, y, inner, 62, SHAPE_SLOTS['bottom']))
    y += 62 + 26

    # The footer strip used to sit here, on every page. It was removed: it
    # stacked directly under the page-foot banner, and its slot_key matched no
    # package, so it was inventory nobody could buy. See views/footer.php.

    # footer
    body.append(f'<rect x="{pad}" y="{y}" width="{inner}" height="56" rx="6" fill="{NAVY}"/>')
    for i in range(4):
        body.append(f'<rect x="{pad + 26 + i * 262}" y="{y + 24}" width="188" height="8" rx="4" fill="#FFFFFF" opacity="0.3"/>')
    height = y + 56 + pad

    return f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {W} {height}"
     width="{W}" height="{height}" role="img"
     aria-label="Pearland411 {shape} page layout: {caption}">
  <title>Pearland411 ad layout — {caption}</title>
  <style>
    .zsize   {{ font-family: 'Poppins','Open Sans',system-ui,sans-serif; font-weight: 700; fill: #FFFFFF; }}
    .zkey    {{ font-family: ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; fill: #FFFFFF; opacity: .8; }}
    .zkey-out{{ font-family: ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; fill: #5A6472; }}
    .wire    {{ font-family: 'Poppins','Open Sans',system-ui,sans-serif; font-weight: 600; fill: #20245B; opacity: .55; }}
  </style>
  <defs>
    <linearGradient id="zone" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="{BURG_LT}"/>
      <stop offset="1" stop-color="{BURG_DP}"/>
    </linearGradient>
    <linearGradient id="sheen" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="#FFFFFF" stop-opacity="0.4"/>
      <stop offset="0.6" stop-color="#FFFFFF" stop-opacity="0"/>
    </linearGradient>
  </defs>
  <rect width="{W}" height="{height}" fill="{PAGE}"/>
  {''.join(body)}
</svg>'''


# ---------------------------------------------------------------------------
if __name__ == '__main__':
    methods = parse_controller()
    pages = build_pages(methods)
    write('docs/AD_INVENTORY.md', markdown(pages))
    for shape in ('full', 'wide', 'banner'):
        write('docs/images/ad-layout-%s.svg' % shape, layout_svg(shape))
    print('\n%d pages, %d placements' % (len(pages), sum(len(z) for _, _, _, z in pages)))
