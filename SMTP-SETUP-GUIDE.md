# 📧 How to add the client's SMTP credentials (FluentSMTP)

Client's SMTP details:

| Setting | Value |
|---|---|
| SMTP Host | `info.innerfirstaid.com` |
| SMTP Port | `465` |
| Encryption | `SSL/TLS` |
| Username | `info@innerfirstaid.com` |
| Password | `v=t6.ibvtAi{t#)r` |

> ⚠️ **Before you start:** make sure you have **deleted/cleared the Brevo API key** in
> **Settings → Inner First Aid → Email delivery (Brevo)** (set it to empty + Save).
> Otherwise the plugin's built-in Brevo mailer intercepts ALL emails and the SMTP
> settings below would be ignored.

---

## Step 1 — Open FluentSMTP

1. Log in to WordPress admin: `innerfirstaid.com/wp-admin`
2. Left menu → **FluentSMTP** (or Settings → FluentSMTP)
3. Go to the **Connections** tab (top of the page)

## Step 2 — Add a new connection

1. Click **"Add Another Connection"** / **"Add Connection"**
2. Choose the mailer: **"Custom SMTP"** (or **"Other SMTP"** depending on the version)

## Step 3 — Fill in the connection

| Field | Enter exactly |
|---|---|
| Connection name (optional) | `Client Hosting Mail` |
| Host | `info.innerfirstaid.com` |
| Port | `465` |
| Encryption | `SSL` |
| Authentication | `Yes` / `SMTP Auth` |
| Username | `info@innerfirstaid.com` |
| Password | `v=t6.ibvtAi{t#)r` (copy-paste exactly — it has special characters `{ # ) =` — no extra spaces) |
| From Email | `info@innerfirstaid.com` |
| From Name | `Inner First Aid` |
| Force from email | ON (recommended) |

3. Click **Save Connection**

## Step 4 — Make it the DEFAULT connection

1. Go to **FluentSMTP → Mail Settings** (the Settings tab)
2. **Default Connection** → select **`Client Hosting Mail`** (the one you just made)
3. **From Email** → `info@innerfirstaid.com`
4. **From Name** → `Inner First Aid`
5. Click **Save Settings**

## Step 5 — Test it

1. FluentSMTP → **Email Test** tab
2. Enter **your own email** → click **Send Test Email**
3. Check your inbox (and spam!) for the test email from `info@innerfirstaid.com`
4. Check **FluentSMTP → Email Logs** → the new send should say **sent** and show the **connection: Client Hosting Mail**

## Step 6 — Test the real guide email

1. Go to **Settings → Inner First Aid**
2. **"Send test guide email"** → your email → Send
3. You should receive the email with the **guide PDF attached**, from `info@innerfirstaid.com`

## Step 7 — Verify a real lead (optional but recommended)

1. Open `innerfirstaid.com` → fill the form (tick consent + captcha) → submit
2. You get the guide email automatically
3. Check **IFA Leads** — the lead is saved there

---

## 🛠️ If the test fails

| Error / symptom | Fix |
|---|---|
| "Could not connect" | Wrong host or port. Try port `587` with encryption **STARTTLS** instead of 465/SSL. |
| "Authentication failed" | Username/password wrong, or the mailbox `info@innerfirstaid.com` doesn't exist yet at the host. Ask the client to confirm the mailbox is created (and the password is exactly right — special chars!). |
| "Connection refused" | The hosting firewall blocks SMTP from the website server. Check with the client's host (sometimes you must use the host's own SMTP server, e.g. `smtp.hostinger.com`, instead of the domain). |
| Emails land in spam | Ask the client to confirm **SPF + DKIM** DNS records for `innerfirstaid.com` (cPanel hosts usually add these automatically with the mailbox). |

---

## 📌 Remember

- **Brevo key must be EMPTY** in Settings → Inner First Aid, or Brevo intercepts the mail.
- The email address must exist as a real mailbox on the client's hosting (this is a hosting-mail SMTP, not a third-party service).
- After everything works, the guide emails are sent from `info@innerfirstaid.com` — branded, no "via gmail.com", no Brevo dependency.
