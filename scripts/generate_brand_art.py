#!/usr/bin/env python3
"""
Generate the Pearland411 brand artwork that the homepage (and the rest of the
site) serves as flat files:

  assets/images/placeholders/<category>.svg
      Per-category listing placeholders. A listing with no photograph used to
      render one big pear-green tile captioned "no photo"; repeated down a
      three-column grid that is what made the homepage read as unfinished.
      These are soft cream / light grey, carry a category-specific line glyph
      and only a small green accent, per the 2026-08 homepage brief.

  assets/images/ads/<creative>-<WxH>.svg
  assets/images/ads/<creative>-<shape>.svg
      The creative an unsold ad zone falls back to. Six of them — four house
      palettes, so two unsold zones on one page never show the same green
      rectangle twice, plus the web-development and banner-design pitches.

      One file is written per creative PER SIZE the catalogue sells, because
      the artwork is `object-fit`-ed into the zone: a 10.8:1 strip covering a
      7:1 box loses 35% of its width, which is how the top banner came to
      render as "D PLACEMENT" with its button sliced in half. The four shape
      names (strip 970x90 · wide 900x300 · box 600x500 · tall 300x600) are
      still written, as the fallback for a zone whose size isn't in the list.

      Layout is computed from (width, height) and every string is measured
      against the widest likely fallback face, so nothing overflows its box.

  assets/images/ads/templates/advertiser-<shape>.svg
      Editable templates for an advertiser who arrives with no artwork. Every
      colour is declared once at the top of the file; see the README written
      alongside them.

Run from the repository root:

    python3 scripts/generate_brand_art.py

It only writes files under assets/images/ and is safe to re-run — the output is
deterministic, so re-running produces no diff unless this script changed.
"""

import os

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

# --- Brand palette (2026-08 homepage brief) --------------------------------
NAVY        = "#20245B"
NAVY_DEEP   = "#171A45"
NAVY_LIFT   = "#2C3178"
GREEN       = "#79B82A"
GREEN_DEEP  = "#5E9220"
GREEN_LIFT  = "#8FCB46"
GREEN_PALE  = "#E8F2D8"
BURGUNDY    = "#6D263D"
BURGUNDY_LT = "#8A3D59"
BURGUNDY_DP = "#4E1A2B"
CREAM       = "#F7F5F0"
GRAY        = "#F2F3F5"
INK         = "#1F2430"
INK_MUTED   = "#5A6472"

SANS = "'Open Sans',system-ui,-apple-system,'Segoe UI',Roboto,Arial,sans-serif"
HEAD = "'Poppins','Open Sans',system-ui,-apple-system,'Segoe UI',Roboto,Arial,sans-serif"


def xml(text):
    """Escape a label for use in XML text / attribute content."""
    return (str(text).replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;"))


def write(rel_path, body):
    path = os.path.join(ROOT, rel_path)
    os.makedirs(os.path.dirname(path), exist_ok=True)
    with open(path, "w", encoding="utf-8") as handle:
        handle.write(body if body.endswith("\n") else body + "\n")
    print("wrote", rel_path)


# ===========================================================================
# 1. Category placeholders
# ===========================================================================
#
# Glyphs are 24x24 line art, drawn with stroke="currentColor" semantics and
# scaled up in place. Keeping them at 24 means they are the same drawings the
# category strip and the sidebar use, so a category reads the same everywhere.

GLYPHS = {
    "home-services": (
        "Home Services",
        '<path d="M3 11.5 12 4l9 7.5"/><path d="M5.5 10.5V20h13v-9.5"/>'
        '<path d="M10 20v-5h4v5"/>',
    ),
    "medical": (
        "Medical Services",
        '<rect x="3.5" y="6" width="17" height="14" rx="2.5"/>'
        '<path d="M9 6V4.5A1.5 1.5 0 0 1 10.5 3h3A1.5 1.5 0 0 1 15 4.5V6"/>'
        '<path d="M12 10v6M9 13h6"/>',
    ),
    "healthcare": (
        "Healthcare",
        '<path d="M20.5 8.6a4.6 4.6 0 0 0-8.5-2.4A4.6 4.6 0 0 0 3.5 8.6c0 4.7 8.5 10 8.5 10'
        's8.5-5.3 8.5-10z"/><path d="M3.9 12h3.4l1.4-2.6 2.1 4.6 1.4-2h4"/>',
    ),
    "auto": (
        "Auto Services",
        '<path d="M4 16.5h16"/><path d="M5.5 16.5V12l2-4.5h9l2 4.5v4.5"/>'
        '<path d="M5.5 12h13"/><circle cx="8" cy="17.5" r="1.7"/><circle cx="16" cy="17.5" r="1.7"/>',
    ),
    "business": (
        "Business Services",
        '<rect x="2.5" y="7" width="19" height="12.5" rx="2"/>'
        '<path d="M8.5 7V5.5A1.5 1.5 0 0 1 10 4h4a1.5 1.5 0 0 1 1.5 1.5V7"/>'
        '<path d="M2.5 12.5h19"/><path d="M10.5 12.5v1.5h3v-1.5"/>',
    ),
    "food": (
        "Food & Beverage",
        '<path d="M7 3.5v7a2.5 2.5 0 0 0 5 0v-7"/><path d="M9.5 13v7.5"/>'
        '<path d="M17.5 3.5c-1.3 1.8-2 3.6-2 5.4s.7 2.8 2 2.8v8.8"/>',
    ),
    "financial": (
        "Financial Services",
        '<path d="M12 3.5v17"/>'
        '<path d="M16.5 7H10a2.75 2.75 0 0 0 0 5.5h4a2.75 2.75 0 0 1 0 5.5H7"/>',
    ),
    "shopping": (
        "Shopping",
        '<path d="M5 8h14l-1.2 11.5H6.2z"/><path d="M8.7 8V6.2a3.3 3.3 0 0 1 6.6 0V8"/>',
    ),
    "education": (
        "Education",
        '<path d="m2.8 9 9.2-4.6L21.2 9 12 13.6z"/>'
        '<path d="M6.6 11v4.6c0 1.5 2.4 2.9 5.4 2.9s5.4-1.4 5.4-2.9V11"/>'
        '<path d="M20.4 9.4v5"/>',
    ),
    "beauty": (
        "Beauty & Personal Care",
        '<circle cx="6.5" cy="17.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>'
        '<path d="m8.4 15.7 8.2-11.2M15.6 15.7 7.4 4.5"/>',
    ),
    "pets": (
        "Pet Services",
        '<circle cx="8" cy="6.5" r="1.9"/><circle cx="16" cy="6.5" r="1.9"/>'
        '<circle cx="4.6" cy="12" r="1.7"/><circle cx="19.4" cy="12" r="1.7"/>'
        '<path d="M12 11.5c-3 0-5.2 2.6-5.2 5.1 0 1.7 1.3 3 3 3h4.4c1.7 0 3-1.3 3-3 0-2.5-2.2-5.1-5.2-5.1z"/>',
    ),
    "legal": (
        "Legal & Professional",
        '<path d="M12 3.5v17M6.5 20.5h11"/><path d="M4 7.5h16"/>'
        '<path d="m7.5 7.5-3 6h6zM16.5 7.5l-3 6h6z"/>',
    ),
    "real-estate": (
        "Real Estate",
        '<path d="M3.5 20.5V8.5l6-4 6 4v12"/><path d="M15.5 12.5h5v8"/>'
        '<path d="M6.5 11h2M6.5 14.5h2M6.5 18h2M17.5 15.5h1"/>',
    ),
    "technology": (
        "Technology",
        '<rect x="2.5" y="4.5" width="19" height="12.5" rx="2"/>'
        '<path d="M9 20.5h6M12 17v3.5"/><path d="M6.5 8.5h6"/>',
    ),
    "construction": (
        "Home Improvement",
        '<path d="M4 20.5h16"/><path d="M6.5 20.5v-6h11v6"/>'
        '<path d="M4.5 14.5 12 8l7.5 6.5"/><path d="M14 3.5h4v4"/>',
    ),
    "cleaning": (
        "Cleaning Services",
        '<path d="M8.5 20.5h7l-1-8h-5z"/><path d="M12 12.5V4M9.5 6.5h5"/>'
        '<path d="M17 5.5h3M17 9h3"/>',
    ),
    "general": (
        "Local Business",
        '<path d="M4 9.5 5.5 5h13L20 9.5"/>'
        '<path d="M4 9.5a2.4 2.4 0 0 0 4 1.6 2.4 2.4 0 0 0 4 0 2.4 2.4 0 0 0 4 0 2.4 2.4 0 0 0 4-1.6"/>'
        '<path d="M5.5 12.4V20h13v-7.6"/><path d="M10 20v-4.5h4V20"/>',
    ),
    # Not business categories — the news and video rails need their own, or a
    # story with no picture ends up illustrated with a shopfront.
    "news": (
        "Local News",
        '<path d="M3.5 5.5h13v14h-13z"/><path d="M16.5 9.5h4v8a2 2 0 0 1-4 0"/>'
        '<path d="M6.5 9h7M6.5 12.5h7M6.5 16h4"/>',
    ),
}


def placeholder_svg(key, label, glyph):
    """One 640x480 category placeholder."""
    uid = key.replace("-", "")
    label = xml(label)
    return f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 480" width="640" height="480"
     role="img" aria-label="{label} — photo coming soon">
  <title>{label} — photo coming soon</title>
  <!--
    Category placeholder. Cream/grey ground, one line glyph, a single green
    accent bar bottom-left. Deliberately quiet: it sits in a grid next to real
    photographs and must not compete with them.
    Generated by scripts/generate_brand_art.py — edit there, not here.
  -->
  <defs>
    <linearGradient id="bg{uid}" x1="0" y1="0" x2="0.35" y2="1">
      <stop offset="0" stop-color="{CREAM}"/>
      <stop offset="1" stop-color="{GRAY}"/>
    </linearGradient>
  </defs>

  <rect width="640" height="480" fill="url(#bg{uid})"/>

  <!-- barely-there diagonal weave, so the tile reads as designed, not broken -->
  <g stroke="{NAVY}" stroke-width="1" opacity="0.05">
    <path d="M-80 400 400 -80M0 480 480 0M80 560 560 80M160 640 640 160M240 720 720 240"/>
  </g>

  <g transform="translate(320 232)">
    <circle r="108" fill="#FFFFFF" opacity="0.9"/>
    <circle r="108" fill="none" stroke="{NAVY}" stroke-width="2" opacity="0.12"/>
    <g transform="translate(-69 -69) scale(5.75)" fill="none" stroke="{NAVY}" stroke-width="1.3"
       stroke-linecap="round" stroke-linejoin="round" opacity="0.72">{glyph}</g>
  </g>

  <!-- No category wording: the card that shows this already carries the
       listing's own category badge, and two different labels on one tile
       read as a mistake. The glyph carries the category. -->
  <text x="320" y="388" text-anchor="middle" font-family="{SANS}"
        font-size="19" fill="{INK_MUTED}">Photo coming soon</text>

  <!-- the small green accent the brief asks for, and nothing more -->
  <rect x="40" y="424" width="64" height="6" rx="3" fill="{GREEN}"/>
  <text x="600" y="431" text-anchor="end" font-family="{SANS}"
        font-size="15" font-weight="600" fill="{NAVY}" opacity="0.4">Pearland411</text>
</svg>"""


for key, (label, glyph) in GLYPHS.items():
    write(f"assets/images/placeholders/{key}.svg", placeholder_svg(key, label, glyph))


# ===========================================================================
# 2. Ad creatives — six families, drawn at each zone's exact pixel box
# ===========================================================================
#
# WHY EXACT SIZES. ad_slot.php sizes a zone from `ads_prices.size` and the
# image is `object-fit`-ed into it. The creatives used to come in four fixed
# shapes — strip 970x90, wide 900x300, box 600x500, tall 300x600 — and the
# nearest shape was picked by aspect ratio. But `top-banner-site-wide` is
# 700x100, a 7:1 box, and the strip is 10.8:1. Covering a 7:1 box with 10.8:1
# artwork throws away 35% of the width, 17% off each side: the reported bug was
# the navy banner rendering as "D PLACEMENT ... red business placement
# available" with its call-to-action button sliced in half.
#
# So the layout is computed from (width, height) instead, and one file is
# written per creative per size the catalogue actually sells. The four shape
# names are still emitted, at their own dimensions, as the fallback for a zone
# whose size we don't know about.
#
# WHY THE TEXT IS MEASURED. An <img> renders its SVG in isolation — it cannot
# reach the font the page loaded, so `Poppins` in these files is decorative and
# the real face is whatever the system supplies. Widths are measured against
# Liberation Sans, which is metrically identical to Arial, so the numbers match
# the widest thing likely to render. Every headline is then fitted to the space
# that is actually left after the mark and the button, rather than set at a
# hard-coded size and hoped for.

from PIL import ImageFont

SHAPES = {
    #  name  : (width, height)   — the generic fallback boxes
    "strip": (970, 90),
    "wide":  (900, 300),
    "box":   (600, 500),
    "tall":  (300, 600),
}

# Every size in the ads_prices catalogue (migrations 0006 / 0017 / 0019 / 0024).
# A creative is written for each, so no zone ever crops its artwork.
ZONE_SIZES = ["970x90", "900x100", "728x90", "700x100", "728x200", "300x250", "295x600"]

# Measured against DejaVu Sans, deliberately. The face named in these files is
# Poppins, but an <img> renders its SVG in isolation and cannot reach a font
# the page loaded, so what actually draws is a system fallback — Segoe UI,
# SF Pro, Roboto, DejaVu. DejaVu is the widest of those, so fitting to it means
# the headline that fits here fits everywhere. Measuring against Arial metrics
# instead is what let "Featured business placement" run under its own button.
_FACES = [
    "/usr/share/fonts/truetype/dejavu/DejaVuSans%s.ttf",
    "/usr/share/fonts/truetype/liberation/LiberationSans-%s.ttf",
]
_SAFETY = 1.04                            # hinting and rounding, not metrics
_FONTS = {}


def _advance(text, bold):
    """Width of `text` at font-size 1, in the widest common fallback face."""
    key = bool(bold)
    if key not in _FONTS:
        _FONTS[key] = None
        for tmpl in _FACES:
            path = tmpl % ("-Bold" if key and "DejaVu" in tmpl else
                           ("Bold" if key else ("" if "DejaVu" in tmpl else "Regular")))
            if os.path.isfile(path):
                _FONTS[key] = ImageFont.truetype(path, 100)
                break
    font = _FONTS[key]
    if font is None:                      # no font files at all — estimate wide
        return len(text) * (0.64 if key else 0.56)
    return font.getlength(text) / 100.0


def measure(text, size, bold=True, tracking=0.0):
    """Rendered width of `text` in px, including SVG letter-spacing."""
    return _advance(text, bold) * size * _SAFETY + tracking * max(0, len(text) - 1)


def fit(text, want, floor, avail, bold=True, tracking=0.0):
    """The largest font-size <= `want` at which `text` fits `avail` px."""
    size = want
    while size > floor and measure(text, size, bold, tracking) > avail:
        size -= 0.5
    return size


def clamp(v, lo, hi):
    return max(lo, min(hi, v))


# Each family: gradient stops, eyebrow colour, body colour, button fill/ink,
# the mark it carries, and the file it is written to.
FAMILIES = {
    "": {  # the original green set keeps its unsuffixed filenames
        "file": "house-ad", "mark": "pear",
        "stops": (GREEN_DEEP, "#6BA935", GREEN_LIFT),
        "eyebrow": "#DCEFC0", "body": "#EEF7DF",
        "btn": "#FFFFFF", "btn_ink": GREEN_DEEP,
        "leaf": "#DCEFC0", "pear": "#FFFFFF", "ink": "#FFFFFF",
        "copy": {
            "eyebrow": "ADVERTISE ON PEARLAND411",
            "one":   "Your ad here — reach Pearland",
            "big":   ["Advertise where", "Pearland searches"],
            "small": "Banner positions across the site.",
            "price": "",  # no figure baked into artwork: prices change at /admin/pricing
            "cta":   "See packages",
        },
        "alt": "Advertise your business on Pearland411",
    },
    "burgundy": {
        "file": "house-ad-burgundy", "mark": "pear",
        "stops": (BURGUNDY_DP, BURGUNDY, BURGUNDY_LT),
        "eyebrow": "#F0C8DC", "body": "#F6E3EC",
        "btn": "#FFFFFF", "btn_ink": BURGUNDY,
        "leaf": "#F0C8DC", "pear": "#FFFFFF", "ink": "#FFFFFF",
        "copy": {
            "eyebrow": "SPONSORED PLACEMENT",
            "one":   "Reach thousands of local customers",
            "big":   ["Reach thousands of", "Pearland customers"],
            "small": "Be where local customers already look.",
            "price": "",  # no figure baked into artwork: prices change at /admin/pricing
            "cta":   "Advertise",
        },
        "alt": "Reach thousands of Pearland customers — advertise on Pearland411",
    },
    "navy": {
        "file": "house-ad-navy", "mark": "pear",
        "stops": (NAVY_DEEP, NAVY, NAVY_LIFT),
        "eyebrow": GREEN_LIFT, "body": "#D9DCF0",
        "btn": GREEN, "btn_ink": "#FFFFFF",
        "leaf": GREEN_LIFT, "pear": "#FFFFFF", "ink": "#FFFFFF",
        "copy": {
            "eyebrow": "FEATURED PLACEMENT",
            "one":   "Featured business placement",
            "big":   ["Featured business", "placement available"],
            "small": "Top of the homepage and your category.",
            "price": "",  # no figure baked into artwork: prices change at /admin/pricing
            "cta":   "Get featured",
        },
        "alt": "Featured business placement available on Pearland411",
    },
    "cream": {
        "file": "house-ad-cream", "mark": "pear",
        "stops": ("#FFFFFF", CREAM, "#EDE8DE"),
        "eyebrow": BURGUNDY, "body": INK_MUTED,
        "btn": BURGUNDY, "btn_ink": "#FFFFFF",
        "leaf": GREEN_LIFT, "pear": GREEN, "ink": NAVY,
        "copy": {
            "eyebrow": "PROMOTE YOUR BUSINESS",
            "one":   "Get in front of local customers",
            "big":   ["Get your business in", "front of local customers"],
            "small": "Homepage, directory and category banners.",
            "price": "",  # no figure baked into artwork: prices change at /admin/pricing
            "cta":   "View packages",
        },
        "alt": "Get your business in front of local customers on Pearland411",
    },
    # The two service creatives. They used to be hand-authored at 800x100 with
    # a comment explaining which parts survived the crop in each zone — the
    # same problem, worked around rather than fixed. They are generated now,
    # so they get exact-size artwork like everything else.
    "scs": {
        "file": "scs-ad", "mark": "screen",
        "stops": ("#1E2430", "#2B3545", "#3A4759"),
        "eyebrow": "#8CC63F", "body": "#C6CEDA",
        "btn": "#8CC63F", "btn_ink": "#16202C",
        "leaf": "#8CC63F", "pear": "#8CC63F", "ink": "#FFFFFF",
        "copy": {
            "eyebrow": "SPACE CENTER SYSTEMS",
            "one":   "Websites that bring customers",
            "big":   ["Websites that", "bring customers"],
            "small": "Design, hosting and local SEO.",
            "price": "",
            "cta":   "Get a quote",
        },
        "alt": "Space Center Systems — website design, hosting and local SEO",
    },
    "design": {
        "file": "banner-design", "mark": "pen",
        "stops": ("#5A2540", "#7C3357", "#A2456F"),
        "eyebrow": "#F0C8DC", "body": "#EFD6E1",
        "btn": "#8CC63F", "btn_ink": "#231018",
        "leaf": "#F0C8DC", "pear": "#FFFFFF", "ink": "#FFFFFF",
        "copy": {
            "eyebrow": "PEARLAND411 DESIGN",
            "one":   "No banner? We'll design it",
            "big":   ["No banner?", "We'll design it"],
            "small": "Ad artwork made for these positions.",
            "price": "",
            "cta":   "Design my ad",
        },
        "alt": "Need a banner designed? Pearland411 designs your ad artwork",
    },
}


def pear(fill, leaf, scale=1.0, opacity="0.95"):
    """The Pearland411 pear mark, drawn at roughly 80x80 before scaling."""
    return (
        f'<g transform="scale({scale})" opacity="{opacity}">'
        f'<path d="M0-40c-11 0-20 10-20 24 0 9-9 15-14 25-8 17 6 38 34 38s42-21 34-38'
        f'c-5-10-14-16-14-25 0-14-9-24-20-24z" fill="{fill}"/>'
        f'<path d="M1-40c0-10 2-18 6-25" stroke="{fill}" stroke-width="5" '
        f'stroke-linecap="round" fill="none"/>'
        f'<path d="M9-61c7-14 22-21 36-19 1 15-9 26-26 28-6 1-10-4-10-9z" fill="{leaf}"/></g>'
    )


# Each mark's true drawn extent (x0, y0, x1, y1) in its own units. The pear is
# NOT symmetric about the origin — its stem and leaf reach to y -80 while the
# body stops at +47 — so scaling it about 0,0 hangs a third of the glyph above
# the point it is placed at, and the leaf was being cut off by the top edge of
# every short banner. Fit and centre on the box instead of assuming it.
MARK_BOX = {
    "pear":   (-34, -80, 45, 47),
    "screen": (-46, -38, 46, 40),
    "pen":    (-38, -36, 38, 28),
}


def mark_glyph(fam, size):
    """The family's mark, scaled to fit a `size` x `size` box centred on 0,0."""
    kind = fam["mark"]
    x0, y0, x1, y1 = MARK_BOX[kind]
    scale = size / float(max(x1 - x0, y1 - y0))
    dx, dy = -(x0 + x1) / 2.0, -(y0 + y1) / 2.0
    box = f'scale({scale:.4f}) translate({dx:.1f} {dy:.1f})'

    if kind == "pear":
        return f'<g transform="{box}">{pear(fam["pear"], fam["leaf"])}</g>'

    stroke = fam["leaf"]
    sw = 4.6
    if kind == "screen":            # a browser window with a code caret
        d = ('<rect x="-38" y="-30" width="76" height="52" rx="6"/>'
             '<path d="M-14 34h28M0 22v12"/>'
             f'<path d="M-18-12l-8 8 8 8M18-12l8 8-8 8M6-16l-12 24" stroke-width="{sw * 0.8:.1f}"/>')
    else:                            # "pen" — an artboard with a nib
        d = ('<rect x="-36" y="-34" width="72" height="60" rx="6"/>'
             '<path d="M-36-14h72"/>'
             '<path d="M-14 18l6-20 20 20z" stroke-linejoin="round"/>')
    return (f'<g transform="{box}" fill="none" stroke="{stroke}" '
            f'stroke-width="{sw:.1f}" stroke-linecap="round" stroke-linejoin="round">{d}</g>')


def cta_pill(fam, x, y, label, height, font_size, anchor_right=False):
    """
    A call-to-action pill wide enough for its own label AND its chevron.
    The chevron used to be carved out of the right-hand padding, so a label
    measured even slightly narrow ran underneath it.
    """
    pad = height * 0.44
    gap = height * 0.24
    text_w = measure(label, font_size, bold=True)
    chev = font_size * 0.52
    w = text_w + pad * 2 + gap + chev
    if anchor_right:
        x -= w
    tx = x + pad
    cx = x + pad + text_w + gap + chev / 2
    cy = y + height / 2
    r = chev * 0.46
    return w, (
        f'<g><rect x="{x:.1f}" y="{y:.1f}" width="{w:.1f}" height="{height:.1f}" '
        f'rx="{height / 2:.1f}" fill="{fam["btn"]}"/>'
        f'<text x="{tx:.1f}" y="{cy:.1f}" dominant-baseline="central" font-family="{HEAD}" '
        f'font-size="{font_size:.1f}" font-weight="700" fill="{fam["btn_ink"]}">{xml(label)}</text>'
        f'<path d="M{cx - r:.1f} {cy - r * 1.3:.1f}L{cx + r * 0.6:.1f} {cy:.1f}'
        f'L{cx - r:.1f} {cy + r * 1.3:.1f}" fill="none" stroke="{fam["btn_ink"]}" '
        f'stroke-width="{max(1.5, r * 0.62):.1f}" stroke-linecap="round" stroke-linejoin="round"/></g>'
    )


def _ground(fam, w, h, uid):
    """Gradient, two soft blobs and a hairline top edge."""
    cream = fam["file"].endswith("cream")
    blob = NAVY if cream else "#FFFFFF"
    op = "0.05" if cream else "0.10"
    # A cream creative sits on a cream page, so it needs an edge or it
    # dissolves: a hairline all round and a burgundy bar down the left.
    frame = (f'<rect x="0" y="0" width="{max(6, w * 0.016):.0f}" height="{h}" fill="{BURGUNDY}"/>'
             f'<rect x="0.5" y="0.5" width="{w - 1}" height="{h - 1}" fill="none" '
             f'stroke="#DED9CE" stroke-width="1"/>') if cream else (
             f'<rect x="0" y="0" width="{w}" height="1" fill="#FFFFFF" opacity="0.16"/>')
    r1, r2 = h * 0.85, h * 0.62
    return (
        f'<rect width="{w}" height="{h}" fill="url(#g{uid})"/>'
        f'<g opacity="{op}" fill="{blob}">'
        f'<circle cx="{w * 0.90:.0f}" cy="{-h * 0.12:.0f}" r="{r1:.0f}"/>'
        f'<circle cx="{w * 0.06:.0f}" cy="{h * 1.05:.0f}" r="{r2:.0f}"/></g>'
        + frame
    )


def creative(family_key, w, h):
    """One ad creative, laid out for exactly this pixel box."""
    fam = FAMILIES[family_key]
    c = fam["copy"]
    uid = (family_key or "green") + f"{w}x{h}"
    s0, s1, s2 = fam["stops"]
    ratio = w / float(h)

    if ratio >= 3.2:
        body = _bar(fam, c, w, h)
    elif ratio <= 0.9:
        body = _stack(fam, c, w, h, tall=True)
    else:
        body = _stack(fam, c, w, h, tall=False)

    return f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {w} {h}" width="{w}" height="{h}"
     role="img" aria-label="{xml(fam['alt'])}">
  <title>{xml(fam['alt'])}</title>
  <!-- Generated by scripts/generate_brand_art.py at exactly {w}x{h} — edit there, not here. -->
  <defs>
    <linearGradient id="g{uid}" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="{s0}"/>
      <stop offset="0.55" stop-color="{s1}"/>
      <stop offset="1" stop-color="{s2}"/>
    </linearGradient>
  </defs>
  {_ground(fam, w, h, uid)}
{body}
</svg>"""


def _bar(fam, c, w, h):
    """
    Horizontal banner: mark, then eyebrow over headline, then a pill hard
    right. Everything is measured, so the headline takes whatever size the
    remaining width allows rather than a size chosen for a different box.
    """
    pad = clamp(round(h * 0.20), 12, 34)
    roomy = h >= 130                      # tall enough for two lines and a price
    mark = clamp(h * (0.50 if roomy else 0.56), 30, 130)
    gap = clamp(h * 0.17, 12, 28)

    cta_h = clamp(h * (0.24 if roomy else 0.42), 30, 54)
    cta_fs = clamp(cta_h * 0.40, 12, 19)
    cta_w, _ = cta_pill(fam, 0, 0, c["cta"], cta_h, cta_fs)

    right_edge = w - pad
    text_x = pad + mark + gap
    avail = right_edge - cta_w - clamp(w * 0.055, 28, 64) - text_x

    out = [f'  <g transform="translate({pad + mark / 2:.1f} {h / 2:.1f})">'
           f'{mark_glyph(fam, mark)}</g>']

    if roomy:
        eb_fs = clamp(h * 0.075, 11, 15)
        hd_fs = clamp(h * 0.165, 16, 34)
        for line in c["big"]:
            hd_fs = min(hd_fs, fit(line, hd_fs, 14, avail, True, -0.4))
        sub_fs = clamp(h * 0.085, 12, 17)

        block = eb_fs * 1.7 + hd_fs * 2.5 + sub_fs * 1.7
        y = (h - block) / 2 + eb_fs
        out.append(_txt(text_x, y, c["eyebrow"], eb_fs, fam["eyebrow"], SANS, 600, h * 0.014))
        y += eb_fs * 0.75 + hd_fs
        for line in c["big"]:
            out.append(_txt(text_x, y, line, hd_fs, fam["ink"], HEAD, 700, -0.4))
            y += hd_fs * 1.25
        y += sub_fs * 0.1
        out.append(_txt(text_x, y, c["small"], sub_fs, fam["body"], SANS, 400))

        cy = h / 2
        if c["price"]:
            pr_fs = clamp(h * 0.10, 13, 20)
            out.append(_txt(right_edge, cy - cta_h * 0.55, c["price"], pr_fs,
                            fam["ink"], HEAD, 700, 0, anchor="end"))
            cy += pr_fs * 0.42
        _, pill = cta_pill(fam, right_edge, cy - cta_h / 2, c["cta"], cta_h, cta_fs, True)
        out.append('  ' + pill)
    else:
        # Short bar. The price rides on the eyebrow line rather than fighting
        # the headline for horizontal room.
        eyebrow = c["eyebrow"]
        if c["price"]:
            eyebrow += "  ·  " + c["price"].upper()
        eb_fs = clamp(h * 0.115, 9, 13)
        eb_tr = clamp(h * 0.022, 1.4, 2.6)
        while measure(eyebrow, eb_fs, False, eb_tr) > avail and eyebrow != c["eyebrow"]:
            eyebrow = c["eyebrow"]        # drop the price before shrinking type
        eb_fs = fit(eyebrow, eb_fs, 8.5, avail, False, eb_tr)

        hd_fs = fit(c["one"], clamp(h * 0.27, 14, 30), 13, avail, True, -0.3)

        block = eb_fs + hd_fs * 1.6
        y = (h - block) / 2 + eb_fs
        out.append(_txt(text_x, y, eyebrow, eb_fs, fam["eyebrow"], SANS, 600, eb_tr))
        out.append(_txt(text_x, y + hd_fs * 1.18, c["one"], hd_fs, fam["ink"], HEAD, 700, -0.3))

        _, pill = cta_pill(fam, right_edge, (h - cta_h) / 2, c["cta"], cta_h, cta_fs, True)
        out.append('  ' + pill)

    return "\n".join(out)


def _stack(fam, c, w, h, tall):
    """
    Box and skyscraper: everything centred on one axis.

    Height is a budget, not an afterthought. Laying this out top-down and
    hoping is what pushed the call-to-action off the bottom of the 300x250 —
    the pill was drawn at y 245 in a 250-tall box, so the sidebar creative
    showed a headline and a price and no way to act on either. Elements are
    shed in order of importance (the supporting line first, then the price)
    and only then is type shrunk, so the mark, the offer and the button
    always survive.
    """
    pad = clamp(w * 0.09, 16, 34)
    avail = w - pad * 2
    budget = h - pad * 2
    cx = w / 2

    mark = clamp(min(w, h) * (0.26 if tall else 0.30), 40, 150)
    eb_fs = clamp(w * 0.045, 10, 15)
    eb_tr = clamp(w * 0.008, 1.4, 2.6)
    hd_fs = clamp(w * 0.115, 15, 34)
    sub_fs = clamp(w * 0.055, 11, 18)
    pr_fs = clamp(w * 0.08, 14, 26)
    cta_h = clamp(h * (0.09 if tall else 0.13), 34, 56)

    show_sub = bool(c["small"])
    show_price = bool(c["price"])
    rule = tall and h > 420

    def fitted_head(size):
        for line in c["big"]:
            size = min(size, fit(line, size, 13, avail, True, -0.5))
        return size

    def block_of(mark, eb_fs, hd_fs, sub_fs, pr_fs, cta_h, show_sub, show_price, rule):
        return (mark + clamp(h * 0.05, 12, 34)
                + eb_fs * 1.9
                + hd_fs * 1.34 * len(c["big"])
                + (sub_fs * 2.1 if show_sub else 0)
                + (clamp(h * 0.035, 10, 26) if rule else 0)
                + (pr_fs * 1.9 if show_price else 0)
                + cta_h * 1.5)

    hd_fs = fitted_head(hd_fs)
    if show_sub and measure(c["small"], sub_fs, False) > avail:
        sub_fs = fit(c["small"], sub_fs, 10.5, avail, False)
        show_sub = measure(c["small"], sub_fs, False) <= avail

    for _ in range(120):
        block = block_of(mark, eb_fs, hd_fs, sub_fs, pr_fs, cta_h, show_sub, show_price, rule)
        if block <= budget:
            break
        if show_sub:
            show_sub = False
            continue
        if rule:
            rule = False
            continue
        if show_price and mark <= min(w, h) * 0.20:
            show_price = False
            continue
        mark = max(30.0, mark * 0.95)
        hd_fs = max(13.0, hd_fs - 0.5)
        eb_fs = max(9.5, eb_fs - 0.2)
        pr_fs = max(12.0, pr_fs - 0.5)
        sub_fs = max(10.5, sub_fs - 0.3)
        cta_h = max(28.0, cta_h - 0.4)

    cta_fs = clamp(cta_h * 0.36, 12, 19)
    cta_w, _ = cta_pill(fam, 0, 0, c["cta"], cta_h, cta_fs)
    while cta_w > avail and cta_fs > 10.5:
        cta_fs -= 0.5
        cta_w, _ = cta_pill(fam, 0, 0, c["cta"], cta_h, cta_fs)

    block = block_of(mark, eb_fs, hd_fs, sub_fs, pr_fs, cta_h, show_sub, show_price, rule)
    y = max(pad, (h - block) / 2)

    out = [f'  <g transform="translate({cx:.1f} {y + mark / 2:.1f})">{mark_glyph(fam, mark)}</g>']
    y += mark + clamp(h * 0.05, 12, 34)

    out.append(_txt(cx, y, c["eyebrow"], eb_fs, fam["eyebrow"], SANS, 600, eb_tr, "middle"))
    y += eb_fs * 0.9 + hd_fs

    for line in c["big"]:
        out.append(_txt(cx, y, line, hd_fs, fam["ink"], HEAD, 700, -0.5, "middle"))
        y += hd_fs * 1.24
    y -= hd_fs * 0.24

    if rule:
        rw = min(avail * 0.42, 130)
        y += clamp(h * 0.035, 10, 26)
        out.append(f'  <path d="M{cx - rw / 2:.0f} {y:.0f}h{rw:.0f}" stroke="{fam["ink"]}" '
                   f'stroke-width="2" opacity="0.35"/>')

    if show_sub:
        y += sub_fs * 1.9
        out.append(_txt(cx, y, c["small"], sub_fs, fam["body"], SANS, 400, 0, "middle"))
    if show_price:
        y += pr_fs * 1.7
        out.append(_txt(cx, y, c["price"], pr_fs, fam["ink"], HEAD, 700, 0, "middle"))

    y += cta_h * 0.7
    _, pill = cta_pill(fam, cx - cta_w / 2, y, c["cta"], cta_h, cta_fs)
    out.append('  ' + pill)
    return "\n".join(out)


def _txt(x, y, text, size, fill, family, weight, tracking=0.0, anchor="start"):
    ls = f' letter-spacing="{tracking:.2f}"' if tracking else ""
    an = f' text-anchor="{anchor}"' if anchor != "start" else ""
    return (f'  <text x="{x:.1f}" y="{y:.1f}"{an} font-family="{family}" '
            f'font-size="{size:.1f}" font-weight="{weight}" fill="{fill}"{ls}>{xml(text)}</text>')


for family_key, fam in FAMILIES.items():
    base = fam["file"]
    # The generic shape fallbacks, for a zone whose size isn't in the catalogue.
    for shape, (sw, sh) in SHAPES.items():
        write(f"assets/images/ads/{base}-{shape}.svg", creative(family_key, sw, sh))
    # And one per size actually sold, so nothing is ever cropped.
    for size in ZONE_SIZES:
        sw, sh = (int(n) for n in size.split("x"))
        write(f"assets/images/ads/{base}-{size}.svg", creative(family_key, sw, sh))


# ===========================================================================
# 3. Editable advertiser templates
# ===========================================================================
#
# For an advertiser who has no artwork. Every colour is declared once, in a
# <style> block at the top, so re-branding one is three hex edits.

TEMPLATE_LAYOUT = {
    "strip": dict(logo=(34, 17, 56, 56), eyebrow=(112, 36, 11, "start"),
                  name=(112, 60, 22, "start"), offer=(360, 56, 17, "start"),
                  contact=(600, 56, 15, "start"), btn=(772, 20, 164, 50, 17)),
    "wide":  dict(logo=(60, 96, 108, 108), eyebrow=(200, 108, 14, "start"),
                  name=(200, 148, 34, "start"), offer=(200, 186, 20, "start"),
                  contact=(200, 220, 17, "start"), btn=(200, 238, 216, 48, 17)),
    "box":   dict(logo=(246, 56, 108, 108), eyebrow=(300, 202, 13, "middle"),
                  name=(300, 248, 32, "middle"), offer=(300, 292, 20, "middle"),
                  contact=(300, 336, 17, "middle"), btn=(174, 386, 252, 52, 18)),
    "tall":  dict(logo=(96, 72, 108, 108), eyebrow=(150, 226, 13, "middle"),
                  name=(150, 272, 26, "middle"), offer=(150, 320, 18, "middle"),
                  contact=(150, 380, 16, "middle"), btn=(34, 486, 232, 52, 18)),
}


def advertiser_template(shape):
    w, h = SHAPES[shape]
    L = TEMPLATE_LAYOUT[shape]
    lx, ly, lw, lh = L["logo"]
    bx, by, bw, bh, bfs = L["btn"]
    ex, ey, efs, eanchor = L["eyebrow"]
    nx, ny, nfs, nanchor = L["name"]
    ox, oy, ofs, oanchor = L["offer"]
    cx, cy, cfs, canchor = L["contact"]

    return f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {w} {h}" width="{w}" height="{h}"
     role="img" aria-label="Advertiser banner template">
  <title>Advertiser banner template — {w}x{h}</title>
  <!--
    EDITABLE ADVERTISER TEMPLATE — {shape} ({w}x{h})

    Customise in four steps:
      1. .brand-bg / .brand-bg-2  the advertiser's two background colours.
         Use THEIR brand, not Pearland411 green — see README.md in this folder.
      2. .brand-ink / .brand-mute text colours (keep 4.5:1 contrast on the ground).
      3. .brand-btn / .brand-btn-ink the call-to-action pill.
      4. Replace the five text strings and swap <image> in for the logo box.

    Logo: drop the file in and keep the box, e.g.
      <image href="logo.png" x="{lx}" y="{ly}" width="{lw}" height="{lh}"
             preserveAspectRatio="xMidYMid meet"/>

    Calls to action that fit this pill: Call Today · Request a Quote ·
    Visit Our Website · Schedule an Appointment · Learn More · View Special Offer
  -->
  <style>
    .brand-bg   {{ stop-color: {NAVY_DEEP}; }}
    .brand-bg-2 {{ stop-color: {NAVY}; }}
    .brand-ink  {{ fill: #FFFFFF; }}
    .brand-mute {{ fill: #D9DCF0; }}
    .brand-btn  {{ fill: {GREEN}; }}
    .brand-btn-ink {{ fill: #FFFFFF; }}
    .t-head {{ font-family: {HEAD}; font-weight: 700; }}
    .t-body {{ font-family: {SANS}; }}
  </style>
  <defs>
    <linearGradient id="tpl{shape}" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" class="brand-bg"/>
      <stop offset="1" class="brand-bg-2"/>
    </linearGradient>
  </defs>

  <rect width="{w}" height="{h}" fill="url(#tpl{shape})"/>

  <!-- LOGO BOX — replace this rect with an <image> (see comment above) -->
  <g>
    <rect x="{lx}" y="{ly}" width="{lw}" height="{lh}" rx="12" fill="#FFFFFF" opacity="0.14"/>
    <text x="{lx + lw / 2}" y="{ly + lh / 2 + 5}" text-anchor="middle" class="t-body brand-mute"
          font-size="13">LOGO</text>
  </g>

  <text x="{ex}" y="{ey}" text-anchor="{eanchor}" class="t-body brand-mute"
        font-size="{efs}" font-weight="600" letter-spacing="2.4">CATEGORY OR TAGLINE</text>
  <text x="{nx}" y="{ny}" text-anchor="{nanchor}" class="t-head brand-ink"
        font-size="{nfs}">Business Name</text>
  <text x="{ox}" y="{oy}" text-anchor="{oanchor}" class="t-body brand-ink"
        font-size="{ofs}">Main service or offer</text>
  <text x="{cx}" y="{cy}" text-anchor="{canchor}" class="t-body brand-mute"
        font-size="{cfs}">(281) 000-0000 &#183; yourbusiness.com</text>

  <g transform="translate({bx} {by})">
    <rect width="{bw}" height="{bh}" rx="{bh / 2}" class="brand-btn"/>
    <text x="{bw / 2}" y="{bh / 2 + bfs / 3}" text-anchor="middle" class="t-head brand-btn-ink"
          font-size="{bfs}">Call Today</text>
  </g>
</svg>"""


for shape in SHAPES:
    write(f"assets/images/ads/templates/advertiser-{shape}.svg", advertiser_template(shape))


write("assets/images/ads/templates/README.md", f"""# Advertiser banner templates

Four editable SVG templates, one per banner shape the site sells. They are for an
advertiser who buys a position but arrives with no artwork.

| File | Pixels | Fits the zones |
|---|---|---|
| `advertiser-strip.svg` | 970x90 | leaderboards (`top-banner-site-wide`, `bottom-banner-site-wide` at 970x90) |
| `advertiser-wide.svg` | 900x300 | 728x200, 728x250, 970x250 horizontal banners |
| `advertiser-box.svg` | 600x500 | 300x250 sidebar and in-list boxes |
| `advertiser-tall.svg` | 300x600 | `right-panel-skyscraper` vertical sidebar ads |

The shape buckets are the same ones `application/views/partials/ad_slot.php`
picks between, so a creative drawn on one of these templates lands in its zone
without cropping anything that matters.

## Customising one

Open the file in any text editor (or Illustrator / Figma / Inkscape) and change:

1. **The `<style>` block at the top.** Six declarations — two background stops,
   two text colours, and the call-to-action pill's fill and ink.
2. **The five strings** — tagline, business name, offer, phone/website, and the
   button label.
3. **The logo.** Replace the `LOGO` rect with
   `<image href="their-logo.png" x="…" y="…" width="…" height="…"
   preserveAspectRatio="xMidYMid meet"/>` — the coordinates are already in a
   comment in each file.

**Use the advertiser's own brand colours.** Forcing every ad into Pearland411
green makes the page look like one long house ad and makes the paid placement
worth less. The Pearland411 palette below is only the fallback when a business
has no brand of its own.

| Role | Hex |
|---|---|
| Navy | `{NAVY}` |
| Pear green | `{GREEN}` |
| Burgundy | `{BURGUNDY}` |
| Light burgundy | `{BURGUNDY_LT}` |
| Soft cream | `{CREAM}` |
| Light gray | `{GRAY}` |

Keep body text at 4.5:1 contrast against the background it sits on, and leave
the outer ~16px clear — `object-fit: cover` crops a creative whose ratio does
not exactly match its zone.

## Regenerating

These files, the per-category listing placeholders and the four house-ad
palettes all come out of one script:

```
python3 scripts/generate_brand_art.py
```

It is deterministic, writes only under `assets/images/`, and is safe to re-run.
""")


# ===========================================================================
# 4. Ad-zone preview mockups (the admin "Ads Preview" column)
# ===========================================================================
#
# /admin/ads-price lists every sellable zone and shows a thumbnail of it. The
# thumbnail came from `ads_prices.img`, which only the handful of rows created
# by hand before the slot_key migrations ever had — every zone seeded by 0006 /
# 0017 / 0019 / 0024 had NULL, and the view rendered `base_url(NULL)`, i.e. the
# site root as an <img> src, which is the broken-image icon in the screenshot.
#
# These are what fills it: a page wireframe with the zone highlighted WHERE IT
# ACTUALLY SITS and labelled with its pixel size, so an operator selling
# "#2 Right Side Banner" can see it is the second box down the sidebar without
# opening the site. One file per slot_key, plus one per distinct size for rows
# whose slot_key we don't know, plus a generic.

PREVIEW_W, PREVIEW_H = 1024, 768

# Wireframe geometry. The page is drawn once and one region is highlighted, so
# every preview is the same drawing and the zones are comparable at a glance.
PV = {
    "pad":        20,
    "head_y":     20,  "head_h":  40,
    "top_y":      70,  "top_h":   58,
    "hero_y":    138,  "hero_h": 116,
    "body_y":    266,
    "main_x":     20,  "main_w": 640,
    "side_x":    676,  "side_w": 308,
    "row1_h":    156,               # main block A / side box 1
    "mid_y":     434,  "mid_h":   58,   # in-content banner
    "row2_h":    128,               # main block B
    "bot_y":     636,  "bot_h":   58,
    "foot_y":    706,  "foot_h":   42,
}
PV["side1_y"] = PV["body_y"]
PV["side2_y"] = PV["body_y"] + 168
PV["side3_y"] = PV["body_y"] + 336
PV["sky_y"]   = PV["body_y"]
PV["sky_h"]   = 428
PV["row2_y"]  = PV["mid_y"] + PV["mid_h"] + 12

# Where each zone lives on the page. Keys are ads_prices.slot_key.
PREVIEW_POSITIONS = {
    # full-width banners
    "top":    ("x", PV["pad"], PV["top_y"], PREVIEW_W - 2 * PV["pad"], PV["top_h"]),
    "mid":    ("x", PV["main_x"], PV["mid_y"], PV["main_w"], PV["mid_h"]),
    "bottom": ("x", PV["pad"], PV["bot_y"], PREVIEW_W - 2 * PV["pad"], PV["bot_h"]),
    "footer": ("x", PV["pad"], PV["foot_y"], PREVIEW_W - 2 * PV["pad"], PV["foot_h"]),
    # sidebar
    "side-1": ("y", PV["side_x"], PV["side1_y"], PV["side_w"], PV["row1_h"]),
    "side-2": ("y", PV["side_x"], PV["side2_y"], PV["side_w"], PV["row1_h"]),
    "side-3": ("y", PV["side_x"], PV["side3_y"], PV["side_w"], PV["row1_h"]),
    "sky":    ("y", PV["side_x"], PV["sky_y"],   PV["side_w"], PV["sky_h"]),
}

# slot_key -> position. Every zone the code or the migrations reference.
PREVIEW_SLOTS = {
    # 0017 — the site-wide packages sold on /pricing-plan
    "top-banner-site-wide":     ("top",    "700x100"),
    "middle-banner-category":   ("mid",    "900x100"),
    "bottom-banner-site-wide":  ("bottom", "900x100"),
    "right-side-banner-1":      ("side-1", "300x250"),
    "right-side-banner-2":      ("side-2", "300x250"),
    "right-side-banner-3":      ("side-3", "300x250"),
    "right-panel-skyscraper":   ("sky",    "295x600"),
    # 0019 + 0024 — the category-priced zones
    "top-banner-category":      ("top",    "900x100"),
    "bottom-banner-category":   ("bottom", "900x100"),
    "right-banner-1":           ("side-1", "300x250"),
    "right-banner-2":           ("side-2", "300x250"),
    # 0006 — the original zones, still bookable and still aliased
    "home-hero":                ("top",    "728x200"),
    "home-sidebar":             ("side-1", "300x250"),
    "listing-top":              ("top",    "728x90"),
    "listing-inline":           ("mid",    "728x90"),
    "listing-sidebar":          ("side-1", "300x250"),
    "events-sidebar":           ("side-1", "300x250"),
    "news-sidebar":             ("side-2", "300x250"),
    "footer-banner":            ("footer", "970x90"),
}

# Sizes that get a position-agnostic preview, for a row whose slot_key is not
# one of ours (an operator can add pricing rows by hand).
PREVIEW_SIZES = sorted({size for _, size in PREVIEW_SLOTS.values()})

PV_LINE   = "#BFD4EA"   # wireframe text rules
PV_BLOCK  = "#9FC1E4"   # wireframe image blocks
PV_SOFT   = "#D8E6F4"   # wireframe headings
PV_FRAME  = "#EDF3F9"   # page ground
PV_EDGE   = "#C7DBEF"


def _pv_lines(x, y, w, count, gap=11, indent_last=0.55):
    """A stack of text rules, the last one short, like real copy."""
    out = []
    for i in range(count):
        lw = w * indent_last if i == count - 1 else w
        out.append(f'<rect x="{x}" y="{y + i * gap}" width="{lw:.0f}" height="6" rx="3" fill="{PV_LINE}"/>')
    return "".join(out)


def _pv_wireframe(skip):
    """
    The page behind the highlight. `skip` names the region the highlight
    occupies so the wireframe doesn't draw its own block underneath it — the
    highlight is that block, in its true position.
    """
    p, w = PV["pad"], PREVIEW_W
    out = []

    # header: logo + nav
    out.append(f'<rect x="{p}" y="{PV["head_y"]}" width="{w - 2 * p}" height="{PV["head_h"]}" rx="4" fill="{PV_FRAME}"/>')
    out.append(f'<rect x="{p + 14}" y="{PV["head_y"] + 12}" width="104" height="16" rx="4" fill="{PV_BLOCK}"/>')
    for i in range(4):
        out.append(f'<rect x="{w - p - 60 - i * 78}" y="{PV["head_y"] + 16}" width="58" height="8" rx="4" fill="{PV_SOFT}"/>')

    if skip != "top":
        out.append(f'<rect x="{p}" y="{PV["top_y"]}" width="{w - 2 * p}" height="{PV["top_h"]}" rx="4" fill="{PV_FRAME}"/>')

    # hero
    out.append(f'<rect x="{p}" y="{PV["hero_y"]}" width="{w - 2 * p}" height="{PV["hero_h"]}" rx="6" fill="{PV_BLOCK}" opacity="0.75"/>')

    # main column
    mx, mw = PV["main_x"], PV["main_w"]
    out.append(f'<rect x="{mx}" y="{PV["body_y"]}" width="{mw * 0.46:.0f}" height="{PV["row1_h"]}" rx="5" fill="{PV_BLOCK}"/>')
    out.append(f'<rect x="{mx + mw * 0.5:.0f}" y="{PV["body_y"]}" width="{mw * 0.5:.0f}" height="18" rx="4" fill="{PV_SOFT}"/>')
    out.append(_pv_lines(mx + mw * 0.5, PV["body_y"] + 32, mw * 0.5, 9))

    if skip != "mid":
        out.append(f'<rect x="{mx}" y="{PV["mid_y"]}" width="{mw}" height="{PV["mid_h"]}" rx="4" fill="{PV_FRAME}"/>')

    r2 = PV["row2_y"]
    out.append(f'<rect x="{mx}" y="{r2}" width="{mw * 0.3:.0f}" height="18" rx="4" fill="{PV_SOFT}"/>')
    out.append(_pv_lines(mx, r2 + 30, mw * 0.44, 7))
    out.append(f'<rect x="{mx + mw * 0.52:.0f}" y="{r2}" width="{mw * 0.48:.0f}" height="{PV["row2_h"]}" rx="5" fill="{PV_BLOCK}" opacity="0.8"/>')

    # sidebar — three boxes, or one tall panel when the skyscraper is highlighted
    sx, sw = PV["side_x"], PV["side_w"]
    if skip != "sky":
        for key, y in (("side-1", PV["side1_y"]), ("side-2", PV["side2_y"]), ("side-3", PV["side3_y"])):
            if key == skip:
                continue
            out.append(f'<rect x="{sx}" y="{y}" width="{sw}" height="{PV["row1_h"]}" rx="5" fill="{PV_FRAME}"/>')
            out.append(f'<rect x="{sx + 16}" y="{y + 18}" width="{sw - 32}" height="{PV["row1_h"] - 62}" rx="4" fill="{PV_SOFT}"/>')
            out.append(_pv_lines(sx + 16, y + PV["row1_h"] - 34, sw - 32, 2))

    if skip != "bottom":
        out.append(f'<rect x="{p}" y="{PV["bot_y"]}" width="{w - 2 * p}" height="{PV["bot_h"]}" rx="4" fill="{PV_FRAME}"/>')

    # footer band
    if skip != "footer":
        out.append(f'<rect x="{p}" y="{PV["foot_y"]}" width="{w - 2 * p}" height="{PV["foot_h"]}" rx="4" fill="{PV_FRAME}"/>')
        for i in range(4):
            out.append(f'<rect x="{p + 22 + i * 246}" y="{PV["foot_y"] + 16}" width="180" height="8" rx="4" fill="{PV_SOFT}"/>')

    return "\n  ".join(out)


def ad_zone_preview(position, size, caption):
    """
    One wireframe with `position` highlighted in burgundy and labelled `size`.
    Burgundy rather than green: it is the palette's promotional band, and a
    green highlight on a green-accented site does not read as "this bit".
    """
    axis, x, y, w, h = PREVIEW_POSITIONS[position]
    # Label size: the footer strip and the sidebar boxes are much smaller than
    # a leaderboard, so a fixed size either overflows one or gets lost in the
    # other. Fit to the shorter edge, then clamp.
    fs = max(15, min(38, int(min(w * 0.13, h * 0.52))))
    vertical = axis == "y" and h > w * 1.4

    if vertical:
        # Rotate the caption for the skyscraper — a 295x600 box is too narrow
        # to set "295x600" horizontally without shrinking it to nothing.
        fs = max(16, min(30, int(w * 0.16)))
        label = (
            f'<text x="0" y="0" transform="translate({x + w / 2} {y + h / 2}) rotate(-90)" '
            f'text-anchor="middle" dominant-baseline="central" class="pv-cap" '
            f'font-size="{fs}">{xml(caption)}</text>'
        )
    else:
        label = (
            f'<text x="{x + w / 2}" y="{y + h / 2}" text-anchor="middle" '
            f'dominant-baseline="central" class="pv-cap" font-size="{fs}">{xml(caption)}</text>'
        )

    return f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {PREVIEW_W} {PREVIEW_H}"
     width="{PREVIEW_W}" height="{PREVIEW_H}" role="img"
     aria-label="{xml(caption)} advertising zone, shown in place on the page">
  <title>{xml(caption)} ad zone</title>
  <style>
    .pv-cap {{ font-family: {HEAD}; font-weight: 700; fill: #FFFFFF; letter-spacing: .01em; }}
  </style>
  <defs>
    <linearGradient id="pvZone" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="{BURGUNDY_LT}"/>
      <stop offset="1" stop-color="{BURGUNDY_DP}"/>
    </linearGradient>
    <linearGradient id="pvSheen" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="#FFFFFF" stop-opacity="0.55"/>
      <stop offset="0.55" stop-color="#FFFFFF" stop-opacity="0"/>
    </linearGradient>
  </defs>

  <rect width="{PREVIEW_W}" height="{PREVIEW_H}" fill="#FFFFFF"/>
  <rect x="4" y="4" width="{PREVIEW_W - 8}" height="{PREVIEW_H - 8}" rx="8"
        fill="none" stroke="{PV_EDGE}" stroke-width="4"/>

  {_pv_wireframe(position)}

  <rect x="{x}" y="{y}" width="{w}" height="{h}" rx="5" fill="url(#pvZone)"/>
  <rect x="{x}" y="{y}" width="{w}" height="{h}" rx="5" fill="url(#pvSheen)"/>
  {label}
</svg>"""


for _slot, (_pos, _size) in sorted(PREVIEW_SLOTS.items()):
    write(f"assets/images/ads/previews/{_slot}.svg", ad_zone_preview(_pos, _size, _size))

# Position-agnostic, keyed by size, for pricing rows we didn't seed. The shape
# buckets are ad_slot.php's: a wide banner is drawn as the top leaderboard, a
# box as the first sidebar slot, a tall one as the skyscraper.
for _size in PREVIEW_SIZES:
    _w, _h = (int(n) for n in _size.split("x"))
    _ratio = _w / _h
    _pos = "top" if _ratio >= 2.0 else ("sky" if _ratio <= 0.9 else "side-1")
    write(f"assets/images/ads/previews/zone-{_size}.svg", ad_zone_preview(_pos, _size, _size))

# The last resort: a row with no slot_key and an unparseable size.
write("assets/images/ads/previews/zone-generic.svg",
      ad_zone_preview("top", "", "Ad zone"))

print("\ndone")
