# 💳 Stripe Setup Guide — Inner First Aid (step by step)

Everything from creating the Stripe account to activating the payment buttons
on the live WordPress site, plus the exact documents you must collect from the
client.

**Total time:** ~30 minutes (plus Stripe's verification review, usually a few
minutes to 1–2 days).

---

## PART 1 — What the client must give YOU first (documents checklist)

Collect these **before** creating the account, so the setup doesn't stall.
(These are the documents Stripe requires for verification — same list applies
in India, Slovenia/EU, UK, US.)

### A. Account & business details (plain information)

| # | What you need from the client | Example |
| --- | --- | --- |
| 1 | **Email address** to create the Stripe account with (use the client's email, not yours — they must own it) | `owner@innerfirstaid.com` |
| 2 | **Legal business name** (as registered) | "Inner First Aid d.o.o." / "Inner First Aid Pvt. Ltd." |
| 3 | **Business registration number** (if company) | Slovenia: matična številka (AJPES) · India: CIN / Udyam · UK: Company No. |
| 4 | **Country where the business is registered** | Slovenia / India / UK... |
| 5 | **Business type** | Individual / Sole trader / Company |
| 6 | **Registered business address** | Full address |
| 7 | **Business phone number** | +386 ... / +91 ... |
| 8 | **Website URL** | https://innerfirstaid.com |
| 9 | **Bank account for payouts** | EU: IBAN + BIC · India: Account no. + IFSC · UK: Sort code + account no. |
| 10 | **Owner's full legal name, date of birth & personal address** | For the KYC identity check |

### B. Document copies (scans/photos, clear and in colour)

| # | Document | Needed when |
| --- | --- | --- |
| 1 | **Government-issued photo ID of the owner** (passport / driver's licence / national ID) | Always |
| 2 | **Certificate of Incorporation / business registration extract** | Company accounts |
| 3 | **Tax registration certificate** | India: GST certificate + PAN card · EU: VAT number (if applicable) |
| 4 | **Proof of address** (recent bank statement or utility bill, max 3 months old) | If Stripe asks for it |
| 5 | **Beneficial owners' details** (name, DOB, address, ownership %, ID) | If a company is >25% owned by someone else |

> ⚠️ **Important rules to tell the client:**
> - The Stripe account **must be in the client's name/country**, never the developer's — payouts go to their bank and the account is legally theirs.
> - Stripe does not accept a "fake" business. Use the real legal entity.
> - Currency: payments will be taken in **EUR** (the site prices are EUR). Stripe handles the currency conversion if the client's bank is in another currency.

### C. Decisions the client must make (for you)

- [ ] Price per program — confirm **EUR 39.90** (or change it)
- [ ] Confirm **3 payment links**: English, Slovenian-female, Slovenian-male (same price, or different)
- [ ] Confirm which email should receive payout/verification emails

---

## PART 2 — Create the Stripe account (15 minutes)

1. Go to **https://stripe.com** → click **"Start now"** / **"Create account"**.
2. Enter the **client's email** + a password → **Create account** → verify the email from the confirmation link.
3. Stripe asks: **"Where is your business registered?"** → select the country (this can rarely be changed later — be careful).
4. Choose **business type**:
   - **Individual / Sole proprietor** — fastest (only the owner's ID needed)
   - **Company** — needs registration documents (see Part 1)
5. Fill in the **business details** from Part 1-A (name, address, phone, website).
6. Fill in the **owner's personal details** (legal name, DOB, personal address).
7. Enter the **bank account details** for payouts.
8. Upload the **ID documents** from Part 1-B (or do it later from the dashboard — Stripe will prompt you).
9. Click **Submit / Activate**.

⏳ Stripe reviews the account — usually minutes; sometimes 1–2 days.
You'll get an email when it's approved. You can still create test payment
links while waiting (see Part 3).

---

## PART 3 — Create the 3 Payment Links (5 minutes)

1. Log in to the **Stripe Dashboard**: https://dashboard.stripe.com
2. In the left menu click **"Payment Links"** → **"Create payment link"**.
3. Configure the first link:
   - **Price:** click "Add price" → **One-time** → amount **39.90** → currency **EUR**
     (name it, e.g. "21-Day Breakup Recovery Program — EN")
   - **Payment methods:** keep the defaults (cards, etc.)
   - **Link title** (optional): "21-day breakup recovery program"
   - Click **"Create link"**.
4. Copy the link URL — it looks like: `https://buy.stripe.com/xxxxx` (test mode links look like `https://buy.stripe.com/test_xxxxx`).
5. Repeat for the **second** and **third** links:
   - **SL female** → "21-dnevni program — za ženske"
   - **SL male** → "21-dnevni program — za moške"
6. Keep the 3 URLs in a text file — you'll paste them into WordPress next.

---

## PART 4 — Connect the links to WordPress (2 minutes)

1. Go to your WordPress admin: **`innerfirstaid.com/wp-admin`**
2. Left menu → **Settings → Inner First Aid**
3. Scroll to **"Payments (Stripe)"** and paste:
   - **Stripe link — English program** ← link 1 (EN)
   - **Stripe link — Slovenian (female)** ← link 2 (SL female)
   - **Stripe link — Slovenian (male)** ← link 3 (SL male)
4. Click **Save Changes** at the bottom.

✅ Done — reload `innerfirstaid.com`:
- "Start program →" (EN card) is now a working link
- On `/sl/` the gender buttons "Za ženske / Za moške" open the female/male links
- "Start now →" (bottom band) is also live

---

## PART 5 — Test the payment flow (5 minutes)

### Test mode (before launch — no real money)

1. In the Stripe dashboard, the toggle in the **top-right corner** switches **Test ⇄ Live**. Leave it on **Test** for now.
2. Test links (`/test_...`) open Stripe's test checkout — pay with the test card:
   - **Card number:** `4242 4242 4242 4242`
   - **Expiry:** any future date · **CVC:** any 3 digits
3. Complete the payment → you'll land on Stripe's success page.
4. Check the payment appeared in Dashboard → **Payments** (marked "test").

### Live mode (real money)

1. When the account is **verified** and you're ready to go live, flip the toggle to **Live**.
2. **Create the 3 payment links again in live mode** (test links don't work for real customers!) — same steps as Part 3, but while in Live mode. Copy the new `https://buy.stripe.com/...` URLs (no `/test_`).
3. Paste the **live** URLs into WordPress (Part 4 again).
4. Do one real purchase yourself (e.g. €1 test price → actually buy it → refund it) to confirm the full flow, payouts and emails.

---

## PART 6 — Optional but recommended

| Task | Where |
| --- | --- |
| **Notification on new leads** | Settings → Inner First Aid → "Email to notify on new lead" → save (optional) |
| **GA4 / Meta Pixel** | Same page → Analytics section (optional) |
| **Refunds** | Stripe Dashboard → Payments → find payment → Refund |
| **Payouts schedule** | Stripe Dashboard → Settings → Payouts (default is usually 2 days) |
| **Customer receipts** | On by default in Payment Links — no action needed |

---

## ❓ FAQ / Gotchas

- **"My buttons are still greyed out in WordPress"** → the pasted link is invalid: it must start with `https://buy.stripe.com/` (no `/test_` if live) and must NOT contain "REPLACE" or "placeholder". Re-paste and save.
- **Client's country not supported by Stripe?** → Check Stripe's global availability list (India, EU incl. Slovenia, UK, US, etc. are supported). If unsupported, alternatives: Paddle / Lemon Squeezy (merchant-of-record) — ask me and I'll set that up instead.
- **Payments in EUR but bank in India/other currency** → Stripe converts automatically; client receives local currency minus fees. Fees vary by country (India ~2% international cards + GST).
- **VAT/GST** → Stripe Payment Links don't calculate VAT automatically. For an EU customer, prices usually shown incl. VAT. Confirm with an accountant if needed.
- **The client should log in to Stripe with THEIR email** — never share your developer credentials; give them a link to this guide instead.

---

## 📋 Quick summary — the 3 things YOU must do after the client gives docs

1. Create the Stripe account (client's details, client's email)
2. Create 3 payment links in **live mode** after verification
3. Paste the 3 URLs into **Settings → Inner First Aid** and save
