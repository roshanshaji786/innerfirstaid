# Inner First Aid — WordPress + Elementor build

A complete, fully working WordPress application for the Inner First Aid landing
page (English + Slovenian), rebuilt from the Next.js prototype in
[`legacy-prototype/`](legacy-prototype/).

**The entire front end is editable in Elementor** — every headline, badge,
price, button label and image is a regular Elementor control. No code needed
after setup.

---

## What you get

| Feature | How it works |
| --- | --- |
| 🏠 Landing page (EN + SL) | Built as Elementor pages (`.home` + `/sl/`) — open in Elementor editor and change anything |
| ✉️ Lead capture form | AJAX → stored in WordPress (custom table), admin list at **IFA Leads**, CSV export, optional email notification, honeypot + rate limiting (10/hour/IP) + duplicate handling |
| 📕 **Free guide auto-delivery** | Every lead automatically receives the guide PDF by email — EN or SL based on the form language (per-language PDFs). Ready-made 10-page guides ship in `guide/` (rebuildable via `python3 guide/build_guide.py`); set them in **Settings → Inner First Aid → Leads & free guide** (subject + message editable per language) |
| 💳 Stripe payment buttons | Payment links set in **Settings → Inner First Aid**. Buttons auto-activate when a valid `https://buy.stripe.com/...` link is set; until then they render disabled ("Coming soon" style) |
| 🌍 Language switch | EN ↔ SL pages, per-language header/footer/cookie texts, correct `lang`/hreflang attributes |
| 🍪 Cookie consent | Banner (EN/SL texts, editable in settings) → consent stored in `localStorage`; GA4 + Meta Pixel load **only after consent** |
| 📄 Privacy / Terms | Elementor pages (EN + `/sl/` variants), linked from the footer |
| 🎨 Design | Original prototype design system (colors, Inter font self-hosted, hero image bundled) |
| ⚙️ One-click setup | `scripts/install.sh` or the **“Build pages now”** button in wp-admin |

## Tech stack (deliberately simple)

- **WordPress** (6.x/7.x, PHP 7.4+)
- **Elementor** (free) — front-end editing
- **Theme** `inner-first-aid` — header, footer, styles, Inter font (self-hosted)
- **Plugin** `ifa-core` — 7 Elementor widgets, lead storage, settings, cookie/analytics, page builder
- No build step, no Node, no external services required

---

## Quick start (any web host)

1. Install WordPress + the free **Elementor** plugin (Plugins → Add New).
2. Upload `wp-content/themes/inner-first-aid/` to `wp-content/themes/` and
   `wp-content/plugins/ifa-core/` to `wp-content/plugins/`.
3. Activate the **Inner First Aid** theme and the **Inner First Aid Core** plugin.
4. Go to **Settings → Inner First Aid** and click **“Build pages now”**.
   All pages are created as Elementor documents.
5. Paste your Stripe payment links and GA/Pixel IDs in the same settings page.
6. Done. Edit any page with Elementor (Pages → Edit with Elementor).

> Alternative: import `elementor-templates/*.json` via
> Elementor → Templates → Import Templates, then create pages from the templates.

## Local install (one command)

Requires PHP ≥ 7.4 CLI (+ MySQL/MariaDB, or nothing for SQLite mode):

```bash
bash scripts/install.sh --db=sqlite --base-url=http://localhost:8080
```

The script downloads WordPress + Elementor, installs the site, activates the
theme/plugin and builds all pages. It works with or without wp-cli
(no wp-cli → falls back to plain PHP). If wordpress.org is unreachable it
automatically uses the official GitHub mirrors.

## Editing the front end with Elementor

Every page is a real Elementor document:

- **Hero + lead form** — badge, headline, subheadline, placeholder, button
  label, note, success/error texts, background image, overlay color/opacity.
- **Lead form** (standalone) — drop it on any page.
- **Social proof bar** — unlimited title/subtitle items, colors.
- **Program cards** — card 1 (title, text, button, direct-link or
  female/male gender picker mode, payment link), card 2 “coming soon”.
- **How it works** — unlimited numbered steps.
- **Pricing / CTA** — old/new price, button text, note, payment link, colors.
- **Language switcher** — current/other language labels and URL.

All widgets live in the **“Inner First Aid”** panel category in the Elementor
editor.

## Where leads go

`Settings → Inner First Aid → Leads`:
- the **IFA Leads** menu shows every captured email (lang, source, IP, date)
  with delete + **Export CSV**
- optional notification email on new leads
- submissions are rate-limited (10/hour per IP) and honeypot-protected

## Settings reference (`Settings → Inner First Aid`)

| Group | Keys |
| --- | --- |
| Payments | Stripe link EN / SL female / SL male (must be `https://buy.stripe.com/...`) |
| Analytics | GA4 ID, Meta Pixel ID (loaded only after cookie consent) |
| Leads | Notification email |
| Header & footer | Logo text, CTA buttons (EN/SL), copyright, disclaimer, link labels |
| Cookie banner | Text + accept/decline labels (EN/SL) |
| Page URLs | EN/SL home, privacy, terms (auto-filled by the builder) |

## Testing

The repository ships an automated test suite that verifies every button and
functionality:

```bash
cd scripts/tests
npm install
bash run-tests.sh http://127.0.0.1:8080 /path/to/wordpress admin 'password'
```

What it covers:

1. **PHP lint** of every theme/plugin file.
2. **HTTP tests** — all pages return 200, every section renders, Elementor CSS
   is generated, no external font requests, lead API (valid 201 / invalid 400 /
   honeypot / bad nonce 403 / duplicates / rate limit 429), Stripe buttons
   disabled → enabled after configuration, admin pages, CSV export.
3. **DOM interaction tests** (jsdom, real clicks) — mobile menu open/close,
   invalid email error state, cookie banner accept/decline + `localStorage`,
   real AJAX lead submit (valid → success + stored), gender picker reveal,
   language switch, legal pages, 404.

## Project layout

```
wp-content/themes/inner-first-aid/   # theme (header/footer/styles/fonts)
wp-content/plugins/ifa-core/         # widgets, leads, settings, builder, consent
  includes/widgets/                  # 7 Elementor widgets
  includes/class-ifa-builder.php     # one-click page builder (Elementor data)
scripts/install.sh                   # one-command installer
scripts/tests/                       # automated test suite
elementor-templates/                 # importable Elementor templates (backup)
legacy-prototype/                    # original Next.js prototype (reference)
```

## Notes

- Payment buttons stay **disabled** until a valid Stripe payment link is set —
  by design (matches the prototype behaviour, no broken links ever).
- The cookie banner and analytics only run after explicit consent (GDPR).
- The theme works standalone (without the plugin) as a plain fallback.
