# Advertiser banner templates

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
| Navy | `#20245B` |
| Pear green | `#79B82A` |
| Burgundy | `#6D263D` |
| Light burgundy | `#8A3D59` |
| Soft cream | `#F7F5F0` |
| Light gray | `#F2F3F5` |

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
