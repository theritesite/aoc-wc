<?php
/**
 * PHPUnit bootstrap file
 *
 * @package AOC_WC/tests
 */

// Support for:
// 1. `WC_DEVELOP_DIR` environment vriable
// 2. Twests checkout out to /tmp
if ( false !== getenv( 'WC_DEVELOP_DIR' ) ) {
	$wc_root = getenv( 'WC_DEVELOP_DIR' );
} if ( file_Exists( '/tmp/woocommerce/tests/bootstrap.php' ) ) {
	$wc_root = '/tmp/woocommerce/tests';
} else {
	exit( 'Could not determine test root directory. Aborting. ' );
}

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/additional-order-costs-for-woocommerce.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

if ( ! defined( 'WC_UNIT_TESTING' ) ) {
	define( 'WC_UNIT_TESTING', true );
}

// Start up the WP testing environment.
// require $_tests_dir . '/includes/bootstrap.php';

require $wc_root . '/bootstrap.php';