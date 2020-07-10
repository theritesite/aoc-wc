<?php

/**
 * The file that defines the server side functionality during AJAX requests
 *
 * @since      1.1.2
 * @link       www.theritesites.com
 * @package    Enhanced_Ajax_Add_To_Cart_Wc
 * @subpackage Enhanced_Ajax_Add_To_Cart_Wc/includes
 * @author     TheRiteSites <contact@theritesites.com>
 */

defined('ABSPATH') || exit;

class AOC_WC_AJAX {

    public static function init() {

        add_action( 'init', array( __CLASS__, 'aoc_wc_define_ajax' ), 0 );
        // add_action( 'template_redirect', array( __CLASS__, 'do_aoc_wc_ajax' ), 0 );
        self::add_aoc_wc_ajax_events();
        
    }

    public static function aoc_wc_define_ajax() {
        if ( ! empty( $_POST['aoc_wc_action'] ) ) {
            if ( ! defined( 'DOING_AJAX' ) ) {
                define( 'DOING_AJAX', true );
            }
            if ( ! defined( 'WC_DOING_AJAX' ) ) {
                define( 'WC_DOING_AJAX', true );
            }
            if ( ! defined( 'AOC_WC_DOING_AJAX' ) ) {
                define( 'AOC_WC_DOING_AJAX', true );
            }
            if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
                @ini_set( 'display_errors', 0 );
            }
            $GLOBALS['wpdb']->hide_errors();
        }
    }

    public static function add_aoc_wc_ajax_events() {
        add_action( 'wp_ajax_aoc_wc_set_costs', array( __CLASS__, 'aoc_wc_set_costs_callback' ) );

    }

    /**
     * The server side callback when the save button is pressed to vverify and save "Additional Cost" fields to WC Orders.
     * 
     * @since 1.0.0
     */
    public static function aoc_wc_set_costs_callback() {

        ob_start();
        $data = array();

        if ( isset( $_POST['post_id'] ) && isset( $_POST['aoc'] ) && isset( $_POST['security'] ) ) {
            try {
                $order_id 	  = intval( sanitize_text_field( $_POST['post_id'] ) );
				$cost_data_agg = ( $_POST['aoc'] );
				$new_cost_data = array();

				foreach ( $cost_data_agg as $key => $cost_data ) {
					if ( isset( $cost_data['label'] ) && isset( $cost_data['cost'] ) ) {
						$new_cost_data[] = array(
								'label' => sanitize_text_field( $cost_data['label'] ),
								'cost'	=> floatval( sanitize_text_field( $cost_data['cost'] ) ),
						);
					}
				}
				
				if ( true === is_int( $order_id ) && 0 < $order_id ) {
					$order = wc_get_order( $order_id );
					if ( AOC_WC_DEBUG || WP_DEBUG ) {
						error_log( wc_print_r( $new_cost_data, true ) );
					}

					$order->update_meta_data( '_aoc_wc_additional_costs', $new_cost_data );
					$order_ret = $order->save();

					$data['payload'] = array( 'order' => $order_ret, 'cost_data' => $new_cost_data );
				}

            } catch ( Exception $e ) {
                return new WP_Error('add_additional_costs_error', $e->getMessage(), array( 'status' => 500 ) );
            }
        }
        else {
            if ( true === WP_DEBUG || true === AOC_WC_DEBUG ) {
                error_log( 'order id: ' . $_POST['post_id'] . ' aoc: ' .  wc_print_r( $_POST['aoc'], true ) );
            }
            $data['error'] = "no orders with additional cost fields received";
        }
        wc_get_notices( array() );
        wc_print_notices();
        $html = ob_get_contents();
        ob_end_clean();
        $data['html'] = $html;
        wp_send_json( $data );

        wp_die();
    }

    /**
	 * Catches activation button press and attempts to activate the license for this plugin.
	 * 
	 * @since
	 */
	public static function aoc_wc_maybe_activate_callback() {
		
		if ( isset( $_POST['action'] ) && isset( $_POST['key'] ) ) {

			if ( ! check_admin_referer( 'aoc_wc_nonce', 'security' ) )
				return wp_send_json_error( array( 'error' => 'nonce mismatch' ) );
			
			$license = get_option( AOC_WC_LICENSE_KEY );
			if ( isset( $_POST[AOC_WC_LICENSE_KEY] ) && ( $license != $_POST[AOC_WC_LICENSE_KEY] ) ) {
				$license = $_POST[AOC_WC_LICENSE_KEY];

				// Saves license value to metabox if activate is pressed
				update_option( AOC_WC_LICENSE_KEY, $license );
			}
			
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_name'  => urlencode( AOC_WC_ITEM_NAME ), // the name of our product in EDD
				'url'        => home_url()
			);
			
			// Call the custom API.
			$response = wp_remote_post( AOC_WC_UPDATER_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
	
			// make sure the response came back okay
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
	
				if ( is_wp_error( $response ) ) {
					$message = $response->get_error_message();
				} else {
					$message = __( 'An error occurred, please try again.' );
				}
	
			} else {
	
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );
	
				if ( false === $license_data->success ) {
	
					switch( $license_data->error ) {
	
						case 'expired' :
	
							$message = sprintf(
								__( 'Your license key expired on %s.' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
							);
							break;
	
						case 'disabled' :
						case 'revoked' :
	
							$message = __( 'Your license key has been disabled.' );
							break;
	
						case 'missing' :
	
							$message = __( 'Invalid license.' );
							break;
	
						case 'invalid' :
						case 'site_inactive' :
	
							$message = __( 'Your license is not active for this URL.' );
							break;
	
						case 'item_name_mismatch' :
	
							$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), AOC_WC_ITEM_NAME );
							break;
	
						case 'no_activations_left':
	
							$message = __( 'Your license key has reached its activation limit.' );
							break;
	
						default :
	
							$message = __( 'An error occurred, please try again.' );
							break;
					}
	
				}
	
			}
	
			// Check if anything passed on a message constituting a failure
			if ( ! empty( $message ) ) {
	
				wp_send_json_success( array( 'message' => $message ) );
				exit();
			}
	
			// $license_data->license will be either "valid" or "invalid"
			update_option( AOC_WC_LICENSE_STATUS, $license_data->license );
			wp_send_json_success( array( 'message' => __( 'License key accepted, and sent for verification. Your premium version should now be active!' ) ) );
			exit();
		}
	}

	/**
	 * Deactivates the plugin license
	 * 
	 * @since
	 */
	public static function aoc_wc_maybe_deactivate_callback() {
		if( isset($_POST['action']) && isset($_POST['key']) ) {
			if ( AOC_WC_DEBUG || WP_DEBUG ) {
				error_log( "this is request['security'] " . $_REQUEST['security'] );
			}
			
			if( ! check_admin_referer( 'aoc_wc_nonce', 'security' ) )
				return wp_send_json_success( array( 'error' => 'nonce mismatch' ) );
			
			$license = get_option( AOC_WC_LICENSE_KEY );
			
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $license,
				'item_name'  => urlencode( AOC_WC_ITEM_NAME ), // the name of our product in EDD
				'url'        => home_url()
			);

			
			// Call the custom API.
			$response = wp_remote_post( AOC_WC_UPDATER_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
	
			// make sure the response came back okay
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
	
				if ( is_wp_error( $response ) ) {
					$message = $response->get_error_message();
				} else {
					$message = __( 'An error occurred, please try again.' );
				}
				
				$base_url = admin_url( 'options-general.php?page=' . AOC_WC_LICENSE_PAGE );
				$redirect = add_query_arg( array( 'aoc_wc_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );
	
				wp_redirect( $redirect );
				exit();
			}
			
			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			
			if( $license_data->license == 'failed' ) {
				$message = __( 'An error occurred, that license does not seem valid, please try again.' );
				
                wp_send_json_success( array( 'message' => $message ) );
				exit();
			}
	
			// $license_data->license will be either "deactivated" or "failed"
			if( $license_data->license == 'deactivated' ) {
				delete_option( AOC_WC_LICENSE_STATUS );
			}
	
			wp_send_json_success( array( 'message' => __( 'License deactivated.' ) ) );
			exit();
		}
	}
}

AOC_WC_AJAX::init();