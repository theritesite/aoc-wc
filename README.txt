=== Additional Order Costs for WooCommerce ===
Contributors: theritesites
Donate link: https://www.theritesites.com
Tags: Order costs, WooCommerce costs, Additional costs, Reporting
Requires at least: 4.0
Tested up to: 5.3.0
Stable tag: trunk
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Whether it's an extra invoice, or a credit from a merchant related to an order. Sometimes you just need a couple extra cost fields for your reporting.

== Description ==

Whether it's an extra invoice, or a credit from a merchant related to an order. Sometimes you just need a couple extra cost fields for your reporting.

After installing this plugin you’ll be able to manually add or adjust and label additional costs for each order on the WooCommerce Edit Order page. This starts with 5 additional cost fields that can be changed in the settings page with the plugin. 5 fields too much? Set it to 2! Not enough, set it to 9!
In the future we will be looking at making these a repeatable field rather than a set number, but we wanted to get users thinking of other expansions as well!

Once you start tracking the additional order costs you associate, you’ll want to use our [WooCommerce Net Profit](https://www.theritesites.com/plugins/woocommerce-net-profit) plugin that will give you beautiful, functional reports to fully analyze your profitability.
This also pairs well with our other cost tracking plguin, [WooCommerce Cost of Shipping](https://wordpress.org/plugins/woo-cost-of-shipping/).
While using the WooCommerce Net Profit plugin, you will individually be able to track Additional Costs, Cost of Shipping, and Cost of Goods. The Net Profit plugin has recently been expanded to allow for custom costs to be associated from any other third party plugin as well, though coding is necessary.


== Installation ==

1. Upload `additional-order-costs-for-woocommerce.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place `<?php do_action('plugin_name_hook'); ?>` in your templates

== Frequently Asked Questions ==

= A question that someone might have =

An answer to that question.

== Screenshots ==


== Changelog ==

= 1.0.2 =

* Repository tweak: removed the updater file from git

= 1.0.1 =

* New: Added is_woocommerce_active check for plugin functionality
* Change: Instead of writing to debug.log we are now utilizing WooCommerce logging to save to a more accessible area
* Tweak: Removed unused code
* Tweak: Completed sanitization of passed in data
* Tweak: Completed escaping of passed in data
* Tweak: Completed the i18n display text translation

= 1.0.0 =

* Initial release


== Upgrade Notice ==
