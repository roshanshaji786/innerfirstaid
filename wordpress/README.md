# Inner First Aid — WordPress & Elementor Launch Kit

This directory contains the production-ready WordPress files and Elementor-importable JSON templates designed specifically for the final launch of **Inner First Aid** (both English and Slovenian landing pages and legal policies). 

---

## 📂 Directory Structure

```text
wordpress/
├── elementor-templates/           # Importable Elementor page templates
│   ├── innerfirstaid-en.json      # English Homepage (Hero, Programs, Steps, Pricing, Footer)
│   ├── innerfirstaid-sl.json      # Slovenian Homepage (incorporating female/male Stripe toggles)
│   ├── privacy-en.json            # English Privacy Policy Page
│   ├── privacy-sl.json            # Slovenian Privacy Policy Page
│   ├── terms-en.json              # English Terms of Service Page
│   └── terms-sl.json              # Slovenian Terms of Service Page
│
└── themes/
    └── innerfirstaid-theme/       # Lightweight Custom WordPress Theme
        ├── style.css              # Custom styling definitions & theme metadata
        ├── functions.php          # WordPress assets (Inter font, Tailwind support, Customizer controls)
        ├── header.php             # Core HTML template header
        ├── footer.php             # Core HTML template footer
        ├── page.php               # Elementor loop renderer
        ├── index.php              # Fallback theme index file
        ├── template-landing-en.php # Native English landing page template (Tailwind styled)
        ├── template-landing-sl.php # Native Slovenian landing page template (with interactive gender toggles)
        ├── template-privacy.php   # Native Privacy Policy template
        ├── template-terms.php     # Native Terms of Service template
        └── screenshot.png         # Beautiful WordPress dashboard preview screenshot
```

---

## 🚀 Option 1: Direct Elementor Templates Import (Visual Drag-and-Drop)
Use this option if you want to import the pages directly into an **existing WordPress website** built with Elementor.

### Step 1: Import Templates to Elementor Library
1. Log in to your WordPress dashboard.
2. Navigate to **Elementor** > **Templates** > **Saved Templates**.
3. Click the **Import Templates** button at the top.
4. Choose the JSON files from `wordpress/elementor-templates/` (e.g., `innerfirstaid-en.json` and `innerfirstaid-sl.json`) and click **Import Now**.

### Step 2: Create and Publish Your Pages
1. Go to **Pages** > **Add New**.
2. Title your page (e.g., "Home" or "Inner First Aid").
3. In the page settings (on the right-hand sidebar), set **Page Template / Page Attribute Layout** to **Elementor Canvas** (this hides standard headers/footers to load the pixel-perfect layout).
4. Click **Edit with Elementor**.
5. In the Elementor canvas, click the **Add Template (Folder Icon)** in the content area.
6. Click the **My Templates** tab, find your imported template, and click **Insert**.
7. Click **Publish**.

### Step 3: Customize Text and Stripe Links
- **Stripe Buttons**: Click on any action button (e.g. *Start program ->*). In the left-hand sidebar under **Link**, paste your live Stripe payment checkout link (replacing `REPLACE_ENGLISH_LINK`, `REPLACE_SLOVENIAN_FEMALE`, etc.).
- **Dynamic Gender Selection (Slovenian Card)**: The Slovenian template utilizes an embedded **HTML** widget for the Split Breakup Card, allowing users to select "Za zenske" (Female) or "Za moske" (Male). Open the HTML widget block to modify those Stripe links directly.

---

## 🎨 Option 2: Custom Theme Setup (Native PHP Templates)
Use this option to deploy a dedicated, high-performance website matching the exact Tailwind structure of the Next.js version with built-in customizer controls.

### Step 1: Zip and Install the Theme
1. Compress the directory `wordpress/themes/innerfirstaid-theme/` into a standard `.zip` file named `innerfirstaid-theme.zip`.
2. Go to **Appearance** > **Themes** > **Add New**.
3. Click **Upload Theme**, select `innerfirstaid-theme.zip`, and click **Install Now**.
4. Click **Activate**.

### Step 2: Create Pages Using the Custom Templates
1. Go to **Pages** > **Add New**.
2. Title your page (e.g., "Home" for English).
3. On the right-hand sidebar, open the **Template** dropdown and select **Landing Page (English)**.
4. Click **Publish**.
5. Create another page (e.g., "Slo" or "Sl"), set its template to **Landing Page (Slovenian)**, and click **Publish**.
6. Repeat the process for **Privacy Policy** and **Terms of Service** pages, assigning their respective templates.

### Step 3: Configure Stripe Payment Links (No Coding Required)
Our custom theme features native WordPress Customizer settings so you don't have to edit files:
1. Go to **Appearance** > **Customize** in your WordPress dashboard.
2. Select the **Stripe Payment Links** section.
3. Update the URLs with your live Stripe links:
   - **English Stripe Checkout Link** (replaces `NEXT_PUBLIC_STRIPE_EN`)
   - **Slovenian Stripe Link (Female)** (replaces `NEXT_PUBLIC_STRIPE_SL_F`)
   - **Slovenian Stripe Link (Male)** (replaces `NEXT_PUBLIC_STRIPE_SL_M`)
4. Click **Publish** to save changes.

---

## 📨 Lead Capture & Form Integrations

Both the native PHP templates and Elementor JSON templates incorporate active lead-capture forms pointing to the `/api/leads` endpoint (for instant compatibility with Vercel or custom backend routing).

### For WordPress Native Operations:
If you are deploying fully inside WordPress without the Next.js server running, you can easily integrate lead collection into your preferred WordPress plugins:

1. **WPForms / Contact Form 7 / Fluent Forms**:
   - Install your preferred form builder plugin.
   - Design a single-field email form.
   - Replace the **HTML** lead capture block in Elementor or the template files with your form plugin's **Shortcode** (e.g. `[wpforms id="123"]`).
2. **Mailchimp / Brevo / ActiveCampaign API**:
   - You can replace the form action URLs with your marketing suite's direct embed forms or webhooks to trigger automated delivery of the free guide instantly upon submission.

---

## 📊 Analytics and Retargeting Pixels

To maintain full trackability matching your original setup:
- Install the free plugin **Header and Footer Scripts** (or **Insert Headers and Footers**).
- Navigate to **Settings** > **Header and Footer Scripts**.
- Paste your **Google Analytics (GA4)** global site tag (`G-XXXXXXXXXX`) and **Meta Pixel (Facebook Pixel)** code directly into the header box. This will automatically execute across all landing pages and Stripe checkout flows.
