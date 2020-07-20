<?php

class AOC_WC_Logger {

	public static $logger;
	public static $context;
	public static $single_instance;

	protected function __construct() {
		self::get_logger();
	}

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since   1.0.0
	 * @return  AOC_WC_Logger A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Instantiates (if necessary) and returns logger
	 * 
	 * @since 1.0.0
	 */
	public static function get_logger() {
		if ( self::$logger === null ) {
			self::$logger = wc_get_logger();
		}
		if ( empty(self::$context) ) {
			self::$context = array( 'source' => 'the-rite-sites-profit-plugins' );
		}
	}

	/**
	 * Adds a debugging message to the log if debug mode is activated
	 * 
	 * @since 1.0.0
	 */
	public static function add_debug( $msg ) {
		if (  WP_DEBUG || ( defined( 'AOC_WC_DEBUG' ) && AOC_WC_DEBUG ) ) {

			self::get_logger();

			if ( is_array( $msg ) || is_object( $msg ) )
				self::$logger->debug( 'aoc-wc: ' . wc_print_r( $msg ), self::$context );
			else
				self::$logger->debug( 'aoc-wc: ' . $msg, self::$context );
		}
	}
}