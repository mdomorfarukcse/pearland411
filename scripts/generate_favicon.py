#!/usr/bin/env python3
"""
Generate the Pearland411 favicon set from the brand mark.

The favicon the site shipped with was `assets/images/fav.ico` from the original
purchased theme — a Google Maps pin, in Google's four brand colours. It had a
single commit against it ("Database Update", the initial theme import) and had
never been touched, so it was what Google's own search results showed beside
pearland411.com. That is the "Wrong Favicon Logo" report, and using another
company's trademark as your site icon is worth fixing on its own account.

What this writes:

  favicon.ico                             multi-size ICO at the DOCUMENT ROOT,
                                          because browsers and crawlers probe
                                          /favicon.ico whatever the markup says
  assets/images/favicon.ico               the same file, for the existing
                                          <link rel="shortcut icon"> tags
  assets/images/favicon-16.png            PNG icons, which every current
  assets/images/favicon-32.png            browser prefers to an ICO
  assets/images/favicon-48.png
  assets/images/favicon-180.png           apple-touch-icon (iOS home screen)
  assets/images/favicon-512.png           manifest / large contexts

The mark is the pear-and-leaf from assets/images/pearland-logo.png, drawn here
rather than cropped: at 16px a scaled-down photograph of a logo turns to mush,
so the shape is redrawn as a solid silhouette with the detail removed. Brand
colours only — navy #20245B ground, pear green #79B82A mark (see
docs/PROJECT_OVERVIEW.md).

Run from the repository root:

    python3 scripts/generate_favicon.py

Deterministic and safe to re-run: the output depends only on this script.
Requires Pillow (`pip install pillow`).
"""

import os

from PIL import Image, ImageDraw

# Brand palette — the same values as assets/css/directory.css.
NAVY = (32, 36, 91, 255)
GREEN = (121, 184, 42, 255)

# Drawn at 512 and downsampled, so the small sizes stay smooth.
MASTER = 512


def _rounded_ground(size):
    """The navy tile the mark sits on."""
    img = Image.new("RGBA", (size, size), (0, 0, 0, 0))
    d = ImageDraw.Draw(img)
    d.rounded_rectangle([0, 0, size - 1, size - 1], radius=int(size * 0.22), fill=NAVY)
    return img


def _pear(size):
    """
    The pear silhouette, as a filled shape.

    Swept as a stack of circles whose radius grows down the vertical axis, so
    the shoulder blends into the belly instead of reading as two stacked lobes
    — a two-ellipse version looked like a snowman once it was scaled to 16px.
    The real mark is an open outline with a gap at the top; an outline of that
    weight disappears below about 32px, so this is the solid reading of it.
    """
    img = Image.new("RGBA", (size, size), (0, 0, 0, 0))
    d = ImageDraw.Draw(img)

    u = size / 100.0  # work in percentages of the tile

    # Body. t runs 0 (top of the shoulder) to 1 (base); the radius curve is what
    # makes it a pear rather than an egg — slow at the top, quick near the base.
    top, base = 32.0, 90.0
    for i in range(240):
        t = i / 239.0
        cy = top + (base - top) * t
        r = 9.0 + 20.0 * (t ** 1.55)
        if cy + r > base:  # keep the bottom flat against the base line
            r = base - cy
        if r <= 0:
            continue
        d.ellipse([(50 - r) * u, (cy - r) * u, (50 + r) * u, (cy + r) * u], fill=GREEN)

    # Stalk, leaning right out of the shoulder.
    d.line(
        [(51 * u, 33 * u), (60 * u, 15 * u)],
        fill=GREEN,
        width=max(1, int(round(5 * u))),
    )

    # Leaf, off the stalk to the right. Big and simple so it survives 16px.
    d.polygon(
        [(58 * u, 17 * u), (88 * u, 10 * u), (70 * u, 33 * u)],
        fill=GREEN,
    )
    return img


def build_master():
    ground = _rounded_ground(MASTER)
    ground.alpha_composite(_pear(MASTER))
    return ground


def main():
    root = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    images = os.path.join(root, "assets", "images")
    os.makedirs(images, exist_ok=True)

    master = build_master()

    ico_sizes = [(16, 16), (32, 32), (48, 48), (64, 64), (128, 128), (256, 256)]
    for path in (
        os.path.join(root, "favicon.ico"),
        os.path.join(images, "favicon.ico"),
    ):
        master.save(path, format="ICO", sizes=ico_sizes)
        print("wrote", os.path.relpath(path, root))

    for px in (16, 32, 48, 180, 512):
        out = os.path.join(images, "favicon-%d.png" % px)
        master.resize((px, px), Image.LANCZOS).save(out, format="PNG")
        print("wrote", os.path.relpath(out, root))


if __name__ == "__main__":
    main()
