# WordPress plugin (live site)

`plugins/ifa-core/` is the "Inner First Aid Core" plugin that runs on innerfirstaid.com
(WordPress + Elementor). It was copied from the live server (version 1.1.4) so changes
can be tracked in git. Settings such as Stripe links and API keys live in the WordPress
database (Settings > Inner First Aid), never in this folder.

To deploy a change, zip `plugins/ifa-core` and upload it in Plugins > Add Plugin > Upload,
or copy the files over the server's `wp-content/plugins/ifa-core/`.

## Payments (Lemon Squeezy, v1.2.0)

The three buy buttons open Lemon Squeezy checkout links. Each link gets
`checkout[custom][program]=en|sl_f|sl_m` added, so the webhook knows which program was bought.

After an order is created, Lemon Squeezy calls `https://innerfirstaid.com/wp-json/ifa/v1/lemonsqueezy-webhook`.
The plugin checks the `X-Signature` header (HMAC-SHA256 of the body with the signing secret), records the
order (IFA Leads > Orders), adds the buyer to the program's Brevo list (a Brevo "contact added to list"
automation sends the 21 daily emails), sends a welcome email, and notifies the lead notification address.
Refunds mark the order refunded and remove the buyer from the list. Orders are de-duplicated by order id.

Setup: Settings > Inner First Aid > Payments (Lemon Squeezy) lists the steps and shows the last
webhook result. Never commit checkout secrets or the webhook signing secret.
