=== Multi-Emails for WooCommerce ===

Contributors: Artiosmedia, steveneray, repon.wp
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=E7LS2JGFPLTH2
Tags: order emails, dropship email, fulfillment email, ship from source, additional emails
Requires at least: 4.6
Tested up to: 6.6.1
Version: 1.0.3
Stable tag: 1.0.3
Requires PHP: 7.4.33
License: GPLv3 or later license and included
URI: http://www.gnu.org/licenses/gpl-3.0.html

Add any number of recipients to receive orders via email based on category or product code. Also added optional emails to user accounts.

== Description ==

<strong>Multi-Emails for WooCommerce</strong> provides unique control over the "ship from" email and address assigned to a category of products or a single product or combination thereof added to WooCommerce. Instead of being limited to one email and address assigned within the WooCommerce store setup, now you can apply any number of emails along with the option of a unique "ship from" point of origin. This allows orders to be submitted to assigned emails apart from the WooCommerce default email and calculates shipping based on the point of origin address entered.

<strong>IMPORTANT NOTE:</strong> The logic of this plugin dictates that the buyer cannot combine products (WordPress default email address and assigned shipping address) in the same order. If an item is placed in the cart that is selected by the plugin to be shipped from a plugin-defined address, a cart message alerts the buyer that only the items listed in the "X" category(s) can be purchased at the same time, not in combination with other items not related to that unique shipping point. Otherwise, the calculated shipping results will not be accurate. The admin can customize the message for the cart notice in settings.

This order multi-email plugin can be combined with digital asset purchases and non-deliverable items, but these items must be assigned a unique category unrelated to physical products to avoid conflicts.

As a bonus, this plugin also allows an administrator to optionally choose to modify the registered user's contact profile by entering multiple email fields under their user profile and making them accessible from the shopping cart. This way a customer may choose to be contacted about an order from different entered emails for their convenience. The admin must create labels for each email field with titles like "Email Two" and "Email Three" as examples for adding two additional email fields.

The plugin's language support includes English, Spanish, French, and Russian.

== Installation ==

1. Upload the plugin files to the '/wp-content/plugins/plugin-name' directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Enter the required content from the settings screen link found in the WooCommerce menu. 

== Technical Details for Release 1.0.3 ==

Load time: 0.256 s; Memory usage: 3.49 MiB
PHP up to tested version: 8.3.11
MySQL up to tested version: 8.0.39
MariaDB up to tested version: 11.5.2
cURL up to tested version: 8.9.1, OpenSSL/3.3.1
PHP 7.4, 8.0, 8.1, 8.2, and 8.3 compliant.

== Using in Multisite Installation ==

1. Extract the zip file contents in the wp-content/mu-plugins/ directory of your WordPress installation. (This is not created by default. You must create it in the wp-content folder.) The 'mu' does not stand for multi-user like it did for WPMU, it stands for 'must-use' as any code placed in that folder will run without needing to be activated.
2. Access the Plugins settings panel named 'Multi-Emails for WooCommerce' under options.
3. Enter the required content from the settings screen link found in the WooCommerce menu.

== Frequently Asked Questions ==

= Is this plugin frequently updated to Wordpress compliance? =
Yes, attention is given on a staged installation with many other plugins via debug mode.

= Is the plugin as simple to use as it looks? =
Yes. No other plugin exists that adds an additional emails to Woocommerce so easily.

= Has there ever any compatibility issues? =
Since it's release, nothing has been noted.

= Is the code in the plugin proven stable? =

Please click the following link to check the current stability of this plugin:
<a href="https://plugintests.com/plugins/multi-emails-for-woocommerce/latest" rel="nofollow ugc">https://plugintests.com/plugins/multi-emails-for-woocommerce/latest</a>

== Screenshots ==

1. The overall Multiple Email Recipents settings panel
2. Shows where the unique address is entered related to recipients email
3. Shows implimentation of additional user emails added
4. Shows additional customer emails added to WooCommerce database

== Upgrade Notice ==

None to report as of the release version

== Changelog ==

1.0.3 09/01/24
- Minor edits to language files
- Assure compliance with WordPress 6.6.1
- Assure compliance with WooCommerce 9.2.3

1.0.2 04/06/24
- Adjustments and fixes applied
- Assure compliance with WordPress 6.5
- Assure compliance with WooCommerce 8.7.0

1.0.1 02/12/24
- Fix the user response banner timer
- Isolate an order to a category if plugin address used
- Fix message injection of conflicting cart items
- Assure compliance with WordPress 6.4.3
- Assure compliance with WooCommerce 8.5.2

1.0.0 01/08/24
- Initial release