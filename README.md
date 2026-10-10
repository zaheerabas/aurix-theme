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

## Download

**[⬇ Download the latest version (aurix-theme.zip)](https://github.com/zaheerabas/aurix-theme/releases/latest/download/aurix-theme.zip)**

This link always gives the newest release. Older versions and release notes
are on the [Releases page](https://github.com/zaheerabas/aurix-theme/releases).

> Use this zip, not GitHub's green **Code → Download ZIP** button. That button
> creates a folder named `aurix-theme-main`, which WordPress treats as a
> different theme, so one-click updates won't reach it.

## Installation
1. Download the zip above.
2. In WordPress go to Appearance → Themes → Add New → **Upload Theme**.
3. Choose `aurix-theme.zip`, click **Install Now**, then **Activate**.

After that, new versions appear automatically as **Update now** in
Appearance → Themes (no need to download again).

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
