<?php

 /**
 * @package              MULTI_EMAILS
 * @wordpress-plugin
 * 
 * Plugin Name:          Multi-Emails for WooCommerce
 * Plugin URI:           https://wordpress.org/plugins/multi-emails-for-woocommerce/
 * Description:          Add to WooCommerce the ability to send orders to specified vendor emails using product ID or category. Optionally, allow registered users or customers to add multiple emails in their profile.
 * Version:              1.0.3
 * Author:               Artios Media
 * Author URI:           http://www.artiosmedia.com
 * Assisting Developer:  Repon Hossain
 * Copyright:            © 2022-2024 Artios Media (email : contact@artiosmedia.com).
 * License:              GNU General Public License v3.0
 * License URI:          http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:          multi-emails-woocommerce
 * Domain Path:          /languages
 * Tested up to:         6.6.1
 * WC requires at least: 6.5.0
 * WC tested up to:      9.2.3
 * PHP tested up to:     8.3.11
 */

if (!defined('ABSPATH')) {
	exit;
}

define('MULTI_EMAILS_WOOCOMMERCE_FILE', __FILE__);
define('MULTI_EMAILS_WOOCOMMERCE_VERSION', '1.0.1');
define('MULTI_EMAILS_WOOCOMMERCE_BASENAME', plugin_basename(__FILE__));
define('MULTI_EMAILS_WOOCOMMERCE_URL', trailingslashit(plugins_url('/', __FILE__)));
define('MULTI_EMAILS_WOOCOMMERCE_PATH', trailingslashit(plugin_dir_path(__FILE__)));

define('MULTI_EMAILS_WOOCOMMERCE_MIN_PHP_VERSION', '7.4.3');

/**
 * Declare HPOS compatibility
 * 
 * @since 1.0.0
 */
add_action('before_woocommerce_init', function () {
	if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
	}
});

require_once MULTI_EMAILS_WOOCOMMERCE_PATH . '/inc/utils.php';

/**
 * Startup woocommerce request
 * 
 * @since 1.0.0
 */
function multi_emails_woocommerce_startup() {
	//Check PHP version. We need minimum version of PHP 7.4.3
	if (version_compare(PHP_VERSION, MULTI_EMAILS_WOOCOMMERCE_MIN_PHP_VERSION, '<')) {
		return add_action('admin_notices', 'multi_emails_woocommerce_php_missing');
	}

	//Check WooCommerce activate
	if (!class_exists('WooCommerce', false)) {
		return add_action('admin_notices', 'multi_emails_woocommerce_missing');
	}

	require_once MULTI_EMAILS_WOOCOMMERCE_PATH . '/inc/main.php';
}
add_action('plugins_loaded', 'multi_emails_woocommerce_startup');

/**
 * Show admin notice if PHP version less than the minimum version required
 * 
 * @since 1.0.0
 */
function multi_emails_woocommerce_php_missing() {
	$notice = sprintf(
		/* translators: 1 for plugin name, 2 for PHP */
		esc_html__('%1$s need %2$s version %3$s or greater.', 'multi-emails-woocommerce'),
		'<strong>Multi Emails for WooCommerce</strong>',
		'<strong>PHP</strong>',
		MULTI_EMAILS_WOOCOMMERCE_MIN_PHP_VERSION
	);

	printf('<div class="notice notice-warning"><p>%1$s</p></div>', wp_kses_post($notice));
}

/**
 * Admin notice for missing woocommerce
 * 
 * @since 1.0.0
 */
function multi_emails_woocommerce_missing() {
	if (file_exists(WP_PLUGIN_DIR . '/woocommerce/woocommerce.php')) {
		$notice_title = __('Activate WooCommerce', 'multi-emails-woocommerce');
		$notice_url = wp_nonce_url('plugins.php?action=activate&plugin=woocommerce/woocommerce.php&plugin_status=all&paged=1', 'activate-plugin_woocommerce/woocommerce.php');
	} else {
		$notice_title = __('Install WooCommerce', 'multi-emails-woocommerce');
		$notice_url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=woocommerce'), 'install-plugin_woocommerce');
	}

	$notice = sprintf(
		/* translators: 1 for plugin name, 2 for WooCommerce */
		__('%1$s need %2$s to be installed and activated to function properly. %3$s', 'multi-emails-woocommerce'),
		'<strong>Multi Emails for WooCommerce</strong>',
		'<strong>WooCommerce</strong>',
		'<a href="' . esc_url($notice_url) . '">' . $notice_title . '</a>'
	);

	printf('<div class="notice notice-warning"><p>%1$s</p></div>', wp_kses_post($notice));
}

/**
 * Load plugin text domain
 * 
 * @since 1.0.0
 */
function multi_emails_woocommerce_load_textdomain() {
	load_plugin_textdomain('multi-emails-woocommerce', false, basename(dirname(__FILE__)) . '/languages');
}
add_action('init', 'multi_emails_woocommerce_load_textdomain');

/**
 * Add install time after activated the plugin
 * 
 * @since 1.0.1
 */
function multi_emails_woocommerce_add_notice_timer() {
	$installed_time = get_option('multi_emails_woocommerce_installed_on');
	if ($installed_time === false) {
		add_option('multi_emails_woocommerce_installed_on', current_time('mysql'));
	}
}
add_action('init', 'multi_emails_woocommerce_add_notice_timer');