<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.theritesites.com
 * @since             1.0.0
 * @package           AOC_WC
 *
 * @wordpress-plugin
 * Plugin Name:       Additional Order Costs for WooCommerce
 * Plugin URI:        https://www.theritesites.com/plugins/additional-order-costs
 * Description:       Whether it's an extra invoice, or a credit from a merchant related to an order. Sometimes you just need a couple extra cost fields for your reporting.
 * Version:           0.1.0
 * Author:            TheRiteSites
 * Author URI:        https://www.theritesites.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       additional-order-costs-for-woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'AOC_WC_VERSION', '0.1.0' );

define( 'AOC_WC_UPDATER_URL', 'https://www.theritesites.com' );

define( 'AOC_WC_ITEM_ID', 2375 );

define( 'AOC_WC_LICENSE_PAGE', 'the_rite_plugins_settings' );

define( 'AOC_WC_ITEM_NAME', 'Additional Order Costs for WooCommerce' );

define( 'AOC_WC_LICENSE_KEY', 'additional_order_costs_for_woocommerce_license_key' );

define( 'AOC_WC_LICENSE_STATUS', 'additional_order_costs_for_woocommerce_license_status' );

if ( file_exists( __DIR__ . '/cmb2/init.php' ) ) {
	require_once __DIR__ . '/cmb2/init.php';
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-aoc-wc-activator.php
 */
function activate_aoc_wc() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-aoc-wc-activator.php';
	AOC_WC_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-aoc-wc-deactivator.php
 */
function deactivate_aoc_wc() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-aoc-wc-deactivator.php';
	AOC_WC_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_aoc_wc' );
register_deactivation_hook( __FILE__, 'deactivate_aoc_wc' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-aoc-wc.php';


/**
 * Inits updater class to talk to https://www.theritesites.com for updates
 * 
 * @since 1.0.0
 */
function trs_aoc_wc_update_check() {

	if ( ! class_exists( 'AOC_WC_Settings' ) ) {
		// load our custom updater
		include( plugin_dir_path( __FILE__ ) . '/includes/class-aoc-wc-settings.php' );
	}

	if ( ! class_exists( 'AOC_WC_Plugin_Updater' ) ) {
		// load our custom updater
		include( plugin_dir_path( __FILE__ ) . '/includes/class-aoc-wc-plugin-updater.php' );
	}

	if ( class_exists( 'AOC_WC_Settings' ) ) {
		$license_key = AOC_WC_Settings::get_value(AOC_WC_LICENSE_KEY);
	}
	
	else {
		$opts = trim(get_option('the_rite_plugins_settings', false));

		$key = AOC_WC_LICENSE_KEY;
		if ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
			$license_key = $opts[ $key ];
		}
	}
	
	if ( ! class_exists( 'AOC_WC_Plugin_Updater' ) ) {
		return;
	}

	$plugin_updater = new AOC_WC_Plugin_Updater( AOC_WC_UPDATER_URL, __FILE__, array(
						'version'	=> AOC_WC_VERSION,
						'license'	=> $license_key,
						'item_id'	=> AOC_WC_ITEM_ID,
						'author'	=> 'TheRiteSites',
						'url'		=> home_url()
			)
	);

}
add_action( 'plugins_loaded', 'trs_aoc_wc_update_check');

/**
 * Add action links like settings and documentation
 * 
 * @since 1.0.0
 * @param $links array	Passed in links from WordPress plugin list
 */
function trs_aoc_wc_add_plugin_action_links( $links ) {
	$new_links = array(
	'<a href="' . admin_url( 'admin.php?page=aoc_wc_options' ) . '">Settings</a>',
	// '<a href="https://www.theritesites.com/docs/category/woocommerce-net-profit/">Docs</a>',
	'<a href="https://www.theritesites.com/plugin-support/">Support</a>',
	);
	return array_merge($new_links, $links);
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'trs_aoc_wc_add_plugin_action_links' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_aoc_wc() {

	$plugin = new AOC_WC();
	$plugin->run();

}
run_aoc_wc();
