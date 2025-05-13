<?php
/*
Plugin Name: Subscriptions for WooCommerce
Plugin URI: https://wordpress.org/plugins/subscription
Description: Enable WooCommerce Subscriptions and Start Recurring Revenue in Minutes.
Plugin URI: https://wpsubscription.co/

Author: converswp
Author URI: https://wpsubscription.co/

Version: 1.4.0
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wp_subscription
Domain Path: /languages
*/

// don't call the file directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Sdevs_Subscription class
 *
 * @class Sdevs_Subscription The class that holds the entire plugin
 */
final class Sdevs_Subscription {


	/**
	 * Plugin version
	 *
	 * @var string
	 */
	const version = '1.3.2';

	/**
	 * Holds various class instances
	 *
	 * @var array
	 */
	private $container = array();

	/**
	 * Constructor for the Sdevs_Wc_Subscription class
	 *
	 * Sets up all the appropriate hooks and actions
	 * within our plugin.
	 */
	private function __construct() {
		$this->define_constants();

		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
	}

	/**
	 * Initializes the Sdevs_Wc_Subscription() class
	 *
	 * Checks for an existing Sdevs_Wc_Subscription() instance
	 * and if it doesn't find one, creates it.
	 *
	 * @return Sdevs_Subscription|bool
	 */
	public static function init() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new Sdevs_Subscription();
		}

		return $instance;
	}

	/**
	 * Magic getter to bypass referencing plugin.
	 *
	 * @param mixed $prop Prop.
	 *
	 * @return mixed
	 */
	public function __get( $prop ) {
		if ( array_key_exists( $prop, $this->container ) ) {
			return $this->container[ $prop ];
		}

		return $this->{$prop};
	}

	/**
	 * Magic isset to bypass referencing plugin.
	 *
	 * @param mixed $prop Prop.
	 *
	 * @return bool
	 */
	public function __isset( $prop ) {
		return isset( $this->{$prop} ) || isset( $this->container[ $prop ] );
	}

	/**
	 * Define the constants
	 *
	 * @return void
	 */
	public function define_constants() {
		define( 'WP_SUBSCRIPTION_VERSION', self::version );
		define( 'WP_SUBSCRIPTION_FILE', __FILE__ );
		define( 'WP_SUBSCRIPTION_PATH', dirname( WP_SUBSCRIPTION_FILE ) );
		define( 'WP_SUBSCRIPTION_INCLUDES', WP_SUBSCRIPTION_PATH . '/includes' );
		define( 'WP_SUBSCRIPTION_TEMPLATES', WP_SUBSCRIPTION_PATH . '/templates/' );
		define( 'WP_SUBSCRIPTION_URL', plugins_url( '', WP_SUBSCRIPTION_FILE ) );
		define( 'WP_SUBSCRIPTION_ASSETS', WP_SUBSCRIPTION_URL . '/assets' );
	}

	/**
	 * Load the plugin after all plugins are loaded
	 *
	 * @return void
	 */
	public function init_plugin() {
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Placeholder for activation function
	 */
	public function activate() {
		$installer = new SpringDevs\Subscription\Installer();
		$installer->run();
	}

	/**
	 * Placeholder for deactivation function
	 *
	 * Nothing being called here yet.
	 */
	public function deactivate() {
		wp_clear_scheduled_hook( 'subscrpt_daily_cron' );
	}

	/**
	 * Include the required files
	 *
	 * @return void
	 */
	public function includes() {
		if ( $this->is_request( 'admin' ) ) {
			$this->container['admin'] = new SpringDevs\Subscription\Admin();
		}

		if ( $this->is_request( 'frontend' ) ) {
			$this->container['frontend'] = new SpringDevs\Subscription\Frontend();
		}

		$this->container['illuminate'] = new SpringDevs\Subscription\Illuminate();
	}

	/**
	 * Initialize the hooks
	 *
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'init', array( $this, 'init_classes' ) );
		add_action( 'init', array( $this, 'localization_setup' ) );
		add_action( 'init', array( $this, 'run_update' ) );
		
		// HPOS Compatibility: Declare support for custom order tables
		add_action('before_woocommerce_init', function() {
			if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
			}
		});

		// HPOS Compatibility: Register subscription post type with HPOS support
		add_action('init', function() {
			register_post_type('subscrpt_order', array(
				'hpos' => true,
				'public' => false,
				'show_ui' => true,
				'show_in_menu' => false,
				'supports' => array('title'),
				'capability_type' => 'post',
				'capabilities' => array(
					'create_posts' => false,
				),
				'map_meta_cap' => true,
			));
		}, 0);
	}

	/**
	 * Need to do some actions after update plugin
	 *
	 * @return void
	 */
	public function run_update() {
		$upgrade = new \SpringDevs\Subscription\Upgrade();
		$upgrade->run();
	}

	/**
	 * Instantiate the required classes
	 *
	 * @return void
	 */
	public function init_classes() {
		if ( $this->is_request( 'ajax' ) ) {
			$this->container['ajax'] = new SpringDevs\Subscription\Ajax();
		}

		$this->container['api']    = new SpringDevs\Subscription\Api();
		$this->container['assets'] = new SpringDevs\Subscription\Assets();
	}

	/**
	 * Initialize plugin for localization
	 *
	 * @uses load_plugin_textdomain()
	 */
	public function localization_setup() {
		load_plugin_textdomain( 'wp_subscription', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * What type of request is this?
	 *
	 * @param string $type admin, ajax, cron or frontend.
	 *
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();

			case 'ajax':
				return defined( 'DOING_AJAX' );

			case 'rest':
				return defined( 'REST_REQUEST' );

			case 'cron':
				return defined( 'DOING_CRON' );

			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}
} // Sdevs_Wc_Subscription

/**
 * Initialize the main plugin
 *
 * @return Sdevs_Subscription|bool
 */
function sdevs_subscription() {
	return Sdevs_Subscription::init();
}

/**
 *  Kick-off the plugin
 */
sdevs_subscription();
