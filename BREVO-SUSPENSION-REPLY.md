# Reply to Brevo support (transactional service suspended)

Copy the email below, adjust anything in [brackets] if needed, and send it as a
**reply** to the Brevo ticket. **Do the 3 setup steps FIRST** (below the email)
so every confirmation you send is true — Brevo re-checks your account.

---

## The reply

**To:** Brevo support (reply to the ticket email)

**Subject:** Re: Transactional email service suspended — corrective actions completed

---

Hi,

Thank you for the detailed explanation. We have completed the corrective
actions and confirm the following:

1. **reCAPTCHA — Confirmed.** All forms connected to this account are now
   protected with Google reCAPTCHA v2 ("I'm not a robot" checkbox), verified
   server-side on every submission. The affected forms are the lead-capture
   forms on innerfirstaid.com (English and Slovenian pages). We also keep
   additional bot protections enabled (honeypot field + IP rate limiting).

2. **Contact quality — Confirmed.** We reviewed our contact list and
   blocklisted inactive, invalid, and non-consented addresses (we kept them
   blocklisted rather than deleting them, as recommended).

3. **Sending activity audit — Confirmed.** We audited all campaigns and
   sending activity. Our account only sends transactional emails: the free
   guide PDF after a visitor opts in via our website form, plus internal
   admin notifications. We do not send marketing campaigns, and we have never
   used purchased or scraped lists.

4. **Opt-in consent — Confirmed.** All website forms now include an explicit
   consent checkbox ("I agree to receive the free guide and occasional
   emails") with a link to our Privacy Policy. Consent is recorded for every
   contact (the date, time, and form are stored in our database) and can be
   provided as documentation on request.

5. **Use case & context.** innerfirstaid.com is a psychological first-aid
   platform. Visitors who want the free guide enter their email on the
   landing page and explicitly tick the consent box. They then receive ONE
   transactional email containing the free guide PDF (per language). That is
   the entire sending volume — typically a handful of emails per day. The
   forms are live at https://innerfirstaid.com/ and
   https://innerfirstaid.com/sl/ — you can see the reCAPTCHA and consent
   checkbox on both.

Please let us know if you need anything else (for example, exported consent
logs or a walkthrough of the forms). We would appreciate having transactional
sending reactivated.

Best regards,
Roshan
Inner First Aid — innerfirstaid.com

---

## Do these FIRST (so the reply is true)

### 1. Update the plugin to v1.1.0
- Download `dist/ifa-core.zip` → delete the old `ifa-core` folder on the host → upload → activate.

### 2. Add reCAPTCHA (5 minutes, free)
1. Go to **https://www.google.com/recaptcha/admin/create**
2. Label: `Inner First Aid` · Type: **reCAPTCHA v2 → "I'm not a robot" Checkbox**
3. Domain: `innerfirstaid.com` (and `www.innerfirstaid.com`) → **Submit**
4. Copy the **Site key** and **Secret key**
5. WordPress → **Settings → Inner First Aid → Form protection (reCAPTCHA) & consent**
6. Paste both keys → **Save Changes**
7. Check the live form (innerfirstaid.com and /sl/) — the consent checkbox and
   the reCAPTCHA box now appear. (Quick test: submit the form yourself — the
   lead appears under **IFA Leads** with Consent = Yes.)

### 3. Clean up contacts in Brevo (10 minutes)
1. Brevo → **Contacts** → open each contact:
   - Inactive/invalid/non-consented → **"Blocklist"** (do NOT delete)
   - Add a consent attribute (optional but recommended): Contacts → settings →
     add attribute `consent` = "yes" + `consent_date`, fill for real leads
2. Brevo → **Senders**: confirm `contact@webweavers.tech` is still verified
   (it is, per your account) — and set it as the sender in
   **Settings → Inner First Aid → Email delivery (Brevo)**.

### 4. (Optional, strengthens the case) Send one real test email
Settings → Inner First Aid → **Verify Brevo & send test email** — once green,
Brevo's own dashboard will show the send, which supports your reply.

---

After steps 1–3 are done, send the reply above.
