# 🚀 Easy Install Guide — Inner First Aid (WordPress + Elementor)

You don't need any coding skills for this. Follow the steps in order.
Total time: **~15 minutes** on a normal web host.

---

## 📦 What you need

| Thing | Where to get it |
| --- | --- |
| A WordPress website | Any host — Hostinger, Bluehost, SiteGround, cPanel, or an existing WordPress site |
| 2 ZIP files | From this repository (see Step 0) |

That's it. No terminal, no code, no Node, no Composer.

---

## Step 0 — Get the 2 ZIP files (1 minute)

In this repository you'll find a **`dist/`** folder containing:

1. **`inner-first-aid.zip`** ← the theme (the design)
2. **`ifa-core.zip`** ← the plugin (forms, buttons, settings)

Download both files to your computer.

> 📌 *If you downloaded the repository as a ZIP from GitHub (Code → Download ZIP), the `dist/` folder is inside it. If you ever change the code, re-create the zips with: `bash scripts/make-zips.sh`*

---

## Step 1 — Create your WordPress site (5 minutes)

**Don't have WordPress yet?**

1. Go to your host (e.g. Hostinger, Bluehost) and open the control panel.
2. Find **"WordPress"** or **"Website installer"** (often called *Softaculous* / *1-click install*).
3. Click **Install WordPress** and pick your domain.
4. WordPress creates itself. You'll get a **wp-admin URL**, a **username**, and a **password** — save them.

**Already have WordPress?** Skip to Step 2.

---

## Step 2 — Install the Elementor plugin (2 minutes)

1. Log in to your WordPress dashboard: **`yoursite.com/wp-admin`**
2. Go to **Plugins → Add New Plugin**
3. Search for **"Elementor"**
4. Click **Install Now** on "Elementor Website Builder"
5. Click **Activate**

---

## Step 3 — Upload the theme (2 minutes)

1. In the dashboard go to **Appearance → Themes**
2. Click the **"Add New"** button (top of the page)
3. Click **"Upload Theme"**
4. Choose **`inner-first-aid.zip`** from your computer
5. Click **Install Now**, then **Activate**

Your site now uses the Inner First Aid design.

---

## Step 4 — Upload the plugin (2 minutes)

1. Go to **Plugins → Add New Plugin**
2. Click the **"Upload Plugin"** button (top of the page)
3. Choose **`ifa-core.zip`** from your computer
4. Click **Install Now**, then **Activate**

---

## Step 5 — Build all the pages (1 click, 1 minute)

1. Go to **Settings → Inner First Aid**
2. Click the green button **"Build pages now"**

Done — WordPress creates:

- 🏠 Home page (English)
- 🇸🇮 Home page (Slovenian, at `/sl/`)
- 📄 Privacy Policy + Terms (English and Slovenian)

> The button is safe to click again any time — it just rebuilds the pages.

---

## Step 6 — Connect Stripe (your payment buttons) (2 minutes)

1. Log in to your **Stripe Dashboard** → **Payment Links** → **Create payment link** (one for each program, e.g. English / Slovenian-female / Slovenian-male).
2. Copy each link (they look like `https://buy.stripe.com/...`)
3. Back in WordPress: **Settings → Inner First Aid**
4. Paste the links into:
   - **Stripe link — English program**
   - **Stripe link — Slovenian (female)**
   - **Stripe link — Slovenian (male)**
5. Click **Save Changes**

🟢 The "Start program" and "Start now" buttons become active automatically.
🔘 If a link is missing, that button simply stays disabled — no broken links, ever.

*(Optional: paste your Google Analytics ID and Meta Pixel ID in the same page — they only load after a visitor accepts cookies.)*

---

## Step 7 — Edit anything with Elementor (forever)

1. Go to **Pages** in WordPress
2. Hover any page → click **"Edit with Elementor"**
3. Click any text or button → edit it in the left panel → **Update**

You can change every headline, price, badge, button label, image and color —
no code, no risk of breaking the site.

**Where captured emails go:** the **"IFA Leads"** menu in your dashboard shows
every email, with **Export CSV** and delete buttons.

### 📕 Send the free guide PDF automatically (recommended)

The site promises "Get free guide →" — and the **ready-made production guides
are already in this repository**:

- `guide/Inner-First-Aid-Guide-EN.pdf` — 10-page English guide (the 3 mistakes, first-aid toolkit, 21-day preview)
- `guide/Inner-First-Aid-Guide-SL.pdf` — the same, fully in Slovenian

To switch them on (3 steps):

1. **Media → Add New** → upload both PDFs (or drag & drop)
2. In the Media Library, click each PDF and copy its **URL** (ends in `.pdf`)
3. **Settings → Inner First Aid** → paste the EN URL into **"Guide PDF URL — English"** and the SL URL into **"Guide PDF URL — Slovenian"** → **Save**

From then on, every lead automatically gets an email (in their language) with
the correct PDF attached. You can edit the email subject/message per language
in the same settings page, and regenerate the PDFs any time with
`python3 guide/build_guide.py`.

> 📌 **Important for deliverability:** install a free SMTP plugin
> (WP Mail SMTP or FluentSMTP) and connect it to a mailbox so the emails land
> in the inbox instead of spam. On most hosts, without an SMTP plugin emails
> may not send at all.

---

## ✅ Checklist

- [ ] Elementor installed & activated
- [ ] `inner-first-aid.zip` uploaded & theme activated
- [ ] `ifa-core.zip` uploaded & plugin activated
- [ ] Pages built (v1.0.2+ builds them automatically on activation)
- [ ] Stripe links pasted (buttons active)
- [ ] (Optional) GA / Pixel IDs pasted
- [ ] Checked the site: `yoursite.com` and `yoursite.com/sl/`

---

## ⚡ Final launch checklist (do these in wp-admin once)

| # | Action | Where |
| --- | --- | --- |
| 1 | **Paste your Stripe payment links** — this is the ONLY thing that activates the "Start program" / "Start now" buttons | Settings → Inner First Aid → Payments |
| 2 | (Optional) GA4 ID + Meta Pixel ID | Settings → Inner First Aid → Analytics |
| 3 | (Optional) Notification email for new leads | Settings → Inner First Aid → Leads |
| 4 | Check your captured leads after test submissions | **IFA Leads** menu (left sidebar) |
| 5 | (Optional) Speed boost: browser caching + compression | See `htaccess-optimized.txt` in the repo — paste after `# END WordPress` in your root `.htaccess` |
| 6 | Check the site on a phone too (the menu button + forms are mobile-tested) | `yoursite.com` |

The security headers, SEO meta (description, Open Graph, JSON-LD), font preloading
and script deferring are already built into the theme — no plugin needed.

---

## 🖥️ Install on your own computer (localhost) — 1 command

If you want to test locally first (PHP 7.4+ required, works with or without MySQL):

```bash
bash scripts/install.sh --db=sqlite --base-url=http://localhost:8080
```

The script downloads WordPress + Elementor, installs everything, activates the
theme and plugin, and builds all pages automatically.

---

## 🛠️ Troubleshooting

| Problem | Fix |
| --- | --- |
| "The package could not be installed" | Make sure you upload the ZIPs from `dist/` — never re-zip the extracted folder with a different structure. |
| Pages show old design | Clear cache: Settings → (caching plugin) → purge, or hard refresh (Ctrl+Shift+R). |
| **"Edit with Elementor" stuck on "loading" forever** | Update the theme to v1.0.2+ (the old theme blocked Elementor's preview iframe with a security header — fixed). Then hard-refresh. If still stuck: (1) raise PHP memory to at least 128M in `wp-config.php`: `define('WP_MEMORY_LIMIT','256M');` (2) deactivate other plugins except Elementor + IFA Core to rule out a conflict, (3) purge server/caching-plugin cache. |
| Buttons greyed out | Stripe link is missing or not a `https://buy.stripe.com/...` link → re-check Step 6. |
| `yoursite.com/sl/` shows 404 | Re-run **"Build pages now"** in Settings → Inner First Aid. |
| Lead form says error | Check Settings → Inner First Aid → notification email is valid (optional) — and that you're not submitting the same email twice from one IP too fast (10/hour limit). |
| Want a fresh start | Re-run **"Build pages now"** — it overwrites the pages cleanly. |
