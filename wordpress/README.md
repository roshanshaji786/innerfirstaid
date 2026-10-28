# WordPress plugin (live site)

`plugins/ifa-core/` is the "Inner First Aid Core" plugin that runs on innerfirstaid.com
(WordPress + Elementor). It was copied from the live server (version 1.1.4) so changes
can be tracked in git. Settings such as Stripe links and API keys live in the WordPress
database (Settings > Inner First Aid), never in this folder.

To deploy a change, zip `plugins/ifa-core` and upload it in Plugins > Add Plugin > Upload,
or copy the files over the server's `wp-content/plugins/ifa-core/`.

## Stripe payments (v1.2.0)

Buy buttons open Stripe Payment Links. Each link gets `?client_reference_id=<program>`
added so the webhook knows which program was bought (a Payment Link metadata key
`ifa_program` = `en`, `sl_f` or `sl_m` also works and takes priority).

After payment, Stripe calls `https://innerfirstaid.com/wp-json/ifa/v1/stripe-webhook`.
The plugin verifies the signature, records the order (IFA Leads > Orders), adds the buyer
to the program's Brevo list (a Brevo "contact added to list" automation sends the 21 daily
emails), sends a welcome email, and notifies the lead notification address. Full refunds
mark the order refunded and remove the buyer from the list.

Setup: Settings > Inner First Aid > Payments (Stripe) lists the steps and shows the last
webhook result. Never commit Stripe keys or the webhook secret.
