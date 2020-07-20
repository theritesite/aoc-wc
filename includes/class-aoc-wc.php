<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    AOC_WC
 * @subpackage AOC_WC/includes
 * @author     TheRiteSites <contact@theritesites.com>
 */
class AOC_WC {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      AOC_WC_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The reference to the settings class for the plugin
	 * 
	 * @since 	1.0.0
	 * @access	protected
	 * @var		class	$plugin_settings
	 */
	protected $plugin_settings;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'AOC_WC_VERSION' ) ) {
			$this->version = AOC_WC_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'additional-order-costs-for-woocommerce';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_global_hooks();
	}

	public function define_global_hooks() {
		$this->loader->add_filter( 'trs_wc_np_order_cost_extension', $this, 'add_additional_order_costs_to_net_profit', 10, 1 );

	}

	public function add_additional_order_costs_to_net_profit( $costs ) {
		$var = '_aoc_wc_additional_costs';
		if ( is_array( $costs ) ) {
			if ( ! isset( $costs[$var] ) ) {
				$costs[$var] = new StdClass();
				$costs[$var]->key = $var;
				$costs[$var]->category = 'additional_costs';
				$costs[$var]->function = 'aoc_wc_calculate_addition_costs_on_order';
			}
		}
		return $costs;
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/aoc-wc-functions.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-aoc-wc-ajax.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-aoc-wc-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-aoc-wc-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-aoc-wc-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-aoc-wc-admin.php';

		$this->loader = new AOC_WC_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the AOC_WC_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new AOC_WC_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new AOC_WC_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->plugin_settings = new AOC_WC_Settings( $this );
		$debug = $this->plugin_settings->get_key_value( 'aoc_wc_settings_title', 'aoc_wc_debug' );
		if ( ! defined( 'AOC_WC_DEBUG' ) ) {
			if ( WP_DEBUG || $debug ) {
				define( 'AOC_WC_DEBUG', true );
			} else {
				define( 'AOC_WC_DEBUG', false );
			}
		}
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    AOC_WC_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}
	
	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
