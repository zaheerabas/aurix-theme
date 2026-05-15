# Aurix International WordPress Theme

**Version:** 17.0.0  
**Author:** ZaheerAbbas  
**GitHub:** https://github.com/zaheerabas/aurix-theme

---

## One-Time GitHub Setup (do this once)

1. Go to https://github.com/zaheerabas/aurix-theme
2. Create the repository if it doesn't exist (click "New repository", name it `aurix-theme`, set to **Public**)
3. Upload all theme files to the `main` branch
4. Go to **Releases** → **Create a new release**
5. Tag: `v17.0.0` | Title: `Aurix Theme v17.0.0`
6. Upload `aurix-theme.zip` as a release asset
7. Publish the release

---

## How Updates Work

1. Claude builds a new version and sends you updated files
2. You update `version.json` with the new version number
3. You upload the new `aurix-theme.zip` as a new GitHub Release
4. WordPress detects the update automatically (checks every 12 hours)
5. Go to **WP Admin → Appearance → Themes** — click **Update Now**
6. Done — no re-upload to WordPress needed

---

## Updating version.json (when releasing a new version)

Edit `version.json` and change the version number and changelog:

```json
{
  "version": "17.1.0",
  "download_url": "https://github.com/zaheerabas/aurix-theme/releases/latest/download/aurix-theme.zip",
  "changelog": "v18.0.0 — Description of what changed."
}
```

---

## Files Structure

```
aurix-v17/
├── functions.php          ← Theme logic + auto-updater
├── style.css              ← Global styles (version declared here)
├── header.php / footer.php
├── woocommerce.php        ← Shop routing
├── css/
│   ├── product.css        ← Product page styles
│   └── shop.css           ← Shop page styles
├── js/
│   ├── aurix-main.js      ← Global JS
│   ├── product.js         ← Product page JS
│   └── shop.js            ← Shop page JS
├── woocommerce/
│   ├── archive-product.php    ← Shop layout
│   ├── content-product.php    ← Product card
│   └── content-single-product.php  ← Product detail page
└── version.json           ← Version checker (on GitHub root)
```
