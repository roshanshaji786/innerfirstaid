# WordPress plugin (live site)

`plugins/ifa-core/` is the "Inner First Aid Core" plugin that runs on innerfirstaid.com
(WordPress + Elementor). It was copied from the live server (version 1.1.4) so changes
can be tracked in git. Settings such as Stripe links and API keys live in the WordPress
database (Settings > Inner First Aid), never in this folder.

To deploy a change, zip `plugins/ifa-core` and upload it in Plugins > Add Plugin > Upload,
or copy the files over the server's `wp-content/plugins/ifa-core/`.
