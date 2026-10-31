# Aurix International — WordPress Theme

Custom WooCommerce theme for **[Aurix International](https://aurixinternational.com)**, a USA-based supplier of surgical and dental instruments.

The theme covers the shop and the brand pages. It also includes a **unified Inbox**, where every enquiry from the site lands in one place. The Inbox comes with a **mobile app** that has push notifications and staff access controlled by QR code.

[![Latest release](https://img.shields.io/github/v/release/zaheerabas/aurix-theme?label=latest&color=b8925a)](https://github.com/zaheerabas/aurix-theme/releases/latest)
![WordPress 6.0+](https://img.shields.io/badge/WordPress-6.0%2B-0d1b2a)
![WooCommerce 7.0+](https://img.shields.io/badge/WooCommerce-7.0%2B-0d1b2a)
![PHP 7.4+](https://img.shields.io/badge/PHP-7.4%2B-0d1b2a)

---

## Contents

- [Download & install](#download--install)
- [What's included](#whats-included)
- [Inbox — all messages in one place](#inbox--all-messages-in-one-place)
- [Inbox mobile app](#inbox-mobile-app)
- [Team access](#team-access)
- [Push notifications](#push-notifications)
- [Shortcodes](#shortcodes)
- [Page templates](#page-templates)
- [Customizer settings](#customizer-settings)
- [Project structure](#project-structure)
- [Releasing an update](#releasing-an-update)
- [Troubleshooting](#troubleshooting)

---

## Download & install

**[⬇ Download the latest version (aurix-theme.zip)](https://github.com/zaheerabas/aurix-theme/releases/latest/download/aurix-theme.zip)**

1. In WordPress go to **Appearance → Themes → Add New → Upload Theme**.
2. Choose `aurix-theme.zip`, click **Install Now**, then **Activate**.

After that, new versions show up as **Update now** under Appearance → Themes. You don't need to download the theme again.

> **Use the release zip, not GitHub's green Code → Download ZIP button.**
> That button creates a folder named `aurix-theme-main`. WordPress treats it as a different theme, so updates won't reach it.

### Requirements

| | Minimum |
|---|---|
| WordPress | 6.0 |
| WooCommerce | 7.0 |
| PHP | 7.4, with the OpenSSL extension (needed for push notifications) |
| HTTPS | Required for the mobile app and notifications |
| SMTP plugin | Recommended so emails get delivered, e.g. GoSMTP |

---

## What's included

### Shop (WooCommerce)
- Custom shop, product, cart, checkout and My Account templates (`woocommerce/`)
- Slide-in cart sidebar with AJAX quantity updates, plus mini cart
- Product cards with hover add-to-cart, a column switcher (2/3/4 on desktop, 1/2 on mobile) and quick view
- Wishlist (`[aurix_wishlist]`) and live product search in the header
- Product pages with a wholesale quote (RFQ) form, a "Ask a question" form, a QR share code and per-product FAQs (a metabox on the product edit screen)
- Back-in-stock alerts on out-of-stock products
- My Account page with tabbed login and register and an Instagram-style profile

### Pages & content
- About, Contact, FAQ, Distributor Community, Kit Builder and Dental Supplier Directory templates
- Blog listing with a featured spotlight, category pills, card grid and compact list. Single posts have a sticky table-of-contents sidebar.
- Dental supplier directory: grid and list views, region tabs, an A–Z bar, filters, a mobile filter drawer and a form for companies to submit a listing
- Mega menus for the main navigation and **All Categories**
- `front-*.html`: HTML sections for the home page, ready to paste into Elementor **HTML** widgets

### Brand
- Navy `#0d1b2a` and gold `#b8925a` / `#734608`
- Styles are split by area (`style.css`, `css/shop.css`, `css/product.css`, `css/cart.css`, …) and only load where they're needed

---

## Inbox — all messages in one place

**WP Admin → Inbox**

Every form on the site saves to one Inbox. Each message is labelled with its type and a plain-English line about what the person wants, e.g. *"Wants a wholesale quote for Mayo Scissors 6.5in (qty 250)"*.

| Type | Comes from |
|---|---|
| Distributor Partner | Distributor Community signup, supplier listings that tick "become a distributor" |
| Pioneer / Offer Code | Offer Codes Manager (Pioneer Program) applications |
| Wholesale Quote | Product page RFQ form |
| Kit Builder RFQ | Medical Kit Builder plugin |
| Contact Inquiry | Contact page form |
| Product Question | Product page question form |
| Newsletter | `[aurix_newsletter]`, contact form opt-in |
| VIP Waitlist | Kit Builder waitlist |
| Back-in-Stock | Out-of-stock product alerts |
| Supplier Listing | Dental supplier directory submissions |

**What you can do with a message:**
- Reply by email from inside WordPress. The reply uses the Aurix email design and is saved under the message.
- Add internal notes for your team.
- Set a status: New, Read, Replied, Archived or Spam.
- Search, filter by type, use bulk actions and export to CSV.
- See an unread badge on the menu, a count in the admin bar and a dashboard widget.

**Plugins:** emails that Medical Kit Builder, Offer Codes Manager and similar plugins send to the admin are **captured automatically**, so they need no changes. You can turn this off in Inbox → Settings & Import.

**Importing old messages:** **Inbox → Settings & Import** brings in the VIP waitlist, back-in-stock lists, supplier listings, Pioneer Program applications and plugin tables such as `mkb_quotes`. It's safe to run more than once: duplicates are skipped and nothing is deleted from the original source.

**Custom forms:** any HTML or Elementor form can post to the Inbox:

```js
fetch(aurixData.ajaxUrl, {
  method: 'POST',
  body: new URLSearchParams({
    action: 'aurix_inbox_submit',
    nonce:  aurixData.inboxNonce,
    type:   'distributor',          // newsletter | distributor | contact | quote | question | other
    email:  'name@company.com',
    name:   'Jane Doe',             // optional: name, phone, company, country, subject, message
    monthly_volume: '500 units'     // any extra field is saved under "Details"
  })
});
```

The endpoint is rate-limited to 8 submissions per IP per hour and includes a honeypot spam trap.

---

## Inbox mobile app

**`https://your-site.com/inbox-app/`**

A phone-first version of the Inbox that installs to the home screen like a normal app.

- **Tab bar:** Inbox · Unread · Team (admins only) · Settings
- **Message list:** grouped by day (Today, Yesterday, This week…), with filters by type and search
- **Gestures:** swipe right for **Done**, swipe left to **Archive** (with Undo), pull down to refresh
- **Message screen:** a "What they want" card, copy buttons for email and phone, and replies and notes shown like a chat
- **Quick-action bar:** **Call · WhatsApp · Reply · Note · Done**
- **Reply panel:** ready-made answers (Reply in 24h, Ask for phone, Send quote, Ask details)
- **Install prompt:** an *Add to Home Screen* card and a Settings row that both **hide once the app is installed**. iPhone users get a step-by-step guide.
- **Offline screen:** shown when there's no internet

**Opening it on a phone:** **Inbox → Open on phone** in WP Admin shows a QR code. Scan it with the phone camera.

---

## Team access

Staff can use the Inbox **without** getting access to WordPress.

**1. Someone scans the QR code (request → approve)**
1. They scan the QR code from **Inbox → Open on phone**.
2. Their phone shows a **Request access** form (name, email, optional phone and note).
3. Admins get an email and a push notification. The request appears at the top of the Inbox, and in the app's **Team** tab, with **Accept** and **Decline**.
4. When you accept, their phone opens the Inbox by itself. A back-up sign-in link is also emailed to them.

**2. Admin adds someone directly**
In **Inbox → Settings & Import → Team access**, enter their name and email. A **personal QR code** appears. It signs them in without a password, works **once** and expires after **48 hours**. You can also send it by WhatsApp or email.

**What team members can do:**

| Allowed | Not allowed |
|---|---|
| Read and reply to messages, Call, WhatsApp, notes, mark Done/Archive/Spam | Open WP Admin (they're sent back to the app), see their WordPress profile, orders, products, pages or settings |
| Turn notifications on for their phone | Delete messages, change Inbox settings, manage the team |

- Team members get the **Inbox Team** role (`aurix_inbox_team`), which has only `read` and `aurix_inbox`.
- They stay signed in for 60 days.
- **Remove** takes away access and signs them out on every device at once.

---

## Push notifications

New messages, and access requests for admins, trigger a **system notification** on every device that turned alerts on, even when the app is closed. Tapping the notification opens that message.

- **Turn on:** tap the bell in the app, or go to **Settings → New message alerts**. You can choose **Important** (skips newsletter, waitlist and back-in-stock signups) or **Everything**, and send a test notification.
- **Android / Chrome / Edge / Firefox:** works in the browser or the installed app.
- **iPhone / iPad:** works **after Add to Home Screen** (iOS 16.4+). The app explains this to the user.
- **How it works:** standard Web Push (VAPID). There's no third-party service or plugin, and the keys are created on your server the first time.
- **Privacy:** pushes carry **no message content**. The phone fetches the details from your site when the notification arrives.

---

## Shortcodes

| Shortcode | What it shows |
|---|---|
| `[aurix_newsletter title="Get industry updates" button="Subscribe"]` | Newsletter signup that saves to the Inbox |
| `[aurix_wishlist]` | The visitor's wishlist |
| `[aurix_kit_builder]` | Kit Builder section with VIP waitlist |
| `[aurix_supplier_directory]` | Dental supplier directory |

---

## Page templates

Choose these in **Page → Template** when editing a page:

| Template | File |
|---|---|
| About Us | `page-about.php` |
| Contact Us | `page-contact.php` |
| FAQ | `page-faq.php` |
| Distributor Community | `page-distributors.php` |
| Kit Builder Section | `page-kit-builder.php` |
| Dental Supplier Directory | `page-supplier-directory.php` |

---

## Customizer settings

**Appearance → Customize:**

- **Aurix — Contact Info:** phone, email, top bar, contact page texts, and contact form emails (recipient, CC, from name and address, subjects)
- **Aurix — Kit Builder Section:** headings and texts
- **Aurix — Out of Stock Text:** back-in-stock box texts
- **Aurix — Header Text / Footer Text:** header and footer texts
- **Aurix — Social Media Links:** Facebook, X, LinkedIn, Instagram, YouTube, Pinterest

---

## Project structure

```
aurix-theme/
├── style.css              Theme header (Version:) + global styles
├── functions.php          Setup, assets, WooCommerce tweaks, forms, Customizer, auto-updater
├── version.json           Version + changelog (used for releases)
├── header.php, footer.php, index.php, page*.php, single.php, author.php
├── front-*.html           Home page sections for Elementor HTML widgets
├── inc/
│   ├── inbox.php          Inbox: storage, admin screens, import, plugin email capture
│   ├── inbox-app.php      Mobile app (/inbox-app/)
│   ├── inbox-team.php     Team access, QR sign-in, access requests
│   ├── inbox-push.php     Push notifications (VAPID), service worker, app icons
│   ├── ajax-handlers.php  Cart + contact form AJAX
│   ├── supplier-directory.php
│   ├── blog.php
│   └── nav-walker.php
├── css/                   shop, product, cart, blog, supplier-directory
├── js/                    aurix-main, shop, product, blog, supplier-directory, qrcode.min (MIT)
├── template-parts/        Kit builder, blog and directory partials
├── woocommerce/           WooCommerce template overrides
├── data/suppliers.json    Starter list for the supplier directory
└── .github/workflows/release.yml
```

**Database:** messages are stored in the `{prefix}aurix_inbox` table, which is created automatically. Push subscriptions and team requests are stored as non-autoloaded options.

---

## Releasing an update

Sites running this theme check the latest GitHub release every 12 hours. When there's a new one, the normal **Update now** button appears in Appearance → Themes.

1. Commit the changed theme files to `main`.
2. Raise `Version:` at the top of `style.css`, e.g. `36.102.0` → `36.103.0`.
3. Update `version` and `changelog` in `version.json`. The changelog becomes the release notes.
4. Push or merge to `main`. The **Release theme** GitHub Action builds `aurix-theme.zip` and publishes the release.

To see the update on a site straight away: **Dashboard → Updates → Check again**.

> Always release from this repo. Installing an older copy of the theme by hand removes newer features, such as the Inbox, until the next update.

---

## Troubleshooting

| Problem | Fix |
|---|---|
| A change doesn't show on the site | Clear **SpeedyCache / LiteSpeed** and the browser cache. In the page source, check the `?ver=` number on the stylesheet. |
| Replies or alerts are not emailed | Check the SMTP plugin settings (GoSMTP) and send a test email. |
| "Install" isn't offered on iPhone | Open `/inbox-app/` in **Safari** → Share → **Add to Home Screen**. |
| No notifications on iPhone | They only work in the Home Screen app (iOS 16.4+). Open the app → Settings → turn alerts on. |
| Notifications are "Blocked" | Allow them in the phone's settings (Settings → Notifications → Inbox, or the browser's app settings), then turn them on again in the app. |
| A team member's QR code says "expired" | Codes work once and last 48 hours. Create a **New QR code** in Team access, or ask them to scan the Inbox QR code and accept their request. |
| A plugin's messages don't appear | Make sure **Capture plugin emails** is on in Inbox → Settings & Import, and that the plugin emails the admin address. |

---

© Aurix International · Theme by [Zaheer Abbas](https://www.linkedin.com/in/zaheerabbasza/)
