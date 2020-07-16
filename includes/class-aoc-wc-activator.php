<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @link       https://www.theritesites.com
 * @since      1.0.0
 * @package    AOC_WC
 * @subpackage AOC_WC/includes
 * @author     TheRiteSites <contact@theritesites.com>
 */
class AOC_WC_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		if ( ! function_exists( 'is_woocommerce_active' ) ) {
			require_once( 'woo-includes/woo-functions.php' );
		}
		if ( ! is_callable( 'is_woocommerce_active' ) || false === is_woocommerce_active() ) {
			add_action( 'admin_notices', array( self, 'woocommerce_not_active_notice' ) );
		}
	}

	public static function woocommerce_not_active_notice() {
		?>
		<div class="notice notice-error is-dismissable">
			<p><?php esc_html_e( __( 'WooCommerce is required for Additional Order Costs for WooCommerce to function at all.', 'additional-order-costs-for-woocommerce' ) ); ?></p>
		</div>
		<?php
	}
}
