# Aurix International WordPress Theme v2.0

## Changes in v2.0
- Font Awesome loaded via direct `<link>` in `<head>` — icons always show
- Social media icons: proper brand colors (Facebook blue, X black, LinkedIn blue, Instagram gradient, YouTube red, Pinterest red)
- Cart sidebar: right-side slide panel matching the reference screenshot, with product images, SKU, qty, subtotal, notes, Continue/Checkout buttons
- Login + Register: tabbed interface on My Account page, both forms in one card with password toggle, benefits list
- Complete account dashboard with styled sidebar nav (icons, active states)
- Full cart & checkout page styling
- Mobile: full-width cart sidebar, responsive account layout
- Elementor compatible

## Installation
Upload via WordPress Admin → Appearance → Themes → Add New → Upload Theme

## Requirements
- WordPress 6.0+
- WooCommerce 7.0+

## Releasing an update (one-click update in WordPress)

Sites running this theme check the latest GitHub release every 12 hours and
show the normal **Update now** button in Appearance → Themes.

To ship a new version:

1. Put the new theme files in the repo (replace the old ones).
2. Raise the `Version:` number at the top of `style.css` (e.g. `36.94.0` → `36.95.0`).
3. Update `changelog` in `version.json` (this becomes the release notes).
4. Merge to `main`. The **Release theme** GitHub Action builds `aurix-theme.zip`
   and publishes the release automatically.

To see it immediately on a site: Dashboard → Updates → **Check again**.
