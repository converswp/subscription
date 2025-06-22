<?php

namespace SpringDevs\Subscription\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Integrations class
 *
 * @package SpringDevs\Subscription\Admin
 */
class Integrations {
	/**
	 * Integrations list.
	 */
	protected $integrations = [];

	/**
	 * Initialize the class
	 */
	public function __construct() {
		// Set integrations.
		$this->integrations = $this->get_integrations();

		// Admin menu (sidebar).
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 20 );

		// WP Subscription navbar.
		add_filter( 'wp_subscription_admin_header_menu_items', [ $this, 'add_integrations_menu_item' ], 10, 2 );
	}

	/**
	 * Register submenu under `subscriptions` menu.
	 *
	 * @return void
	 */
	public function register_admin_menu() {
		$parent_slug = 'wp-subscription';
		add_submenu_page(
			$parent_slug,
			__( 'Integrations', 'wp_subscription' ),
			__( 'Integrations', 'wp_subscription' ),
			'manage_options',
			'wp-subscription-integrations',
			[ $this, 'render_integrations_page' ],
			40
		);
	}

	/**
	 * Add Integrations link to the WP Subscription admin header menu.
	 *
	 * @param array  $menu_items Array of menu items.
	 * @param string $current Current active menu item slug.
	 */
	public function add_integrations_menu_item( $menu_items, $current ) {
		$menu_items[] = [
			'slug'  => 'wp-subscription-integrations',
			'label' => __( 'Integrations', 'wp_subscription' ),
			'url'   => admin_url( 'admin.php?page=wp-subscription-integrations' ),
		];
		return $menu_items;
	}

	/**
	 * Get the list of integrations.
	 *
	 * @return array
	 */
	protected function get_integrations() {
		$integrations = [
			[
				'name'        => 'Paddle',
				'description' => 'Paddle gateway',
				'icon_url'    => 'https://example.com/paddle-icon.png',
				'is_active'   => true,
				'actions'     => [
					[
						'label'       => 'Install Now',
						'type'        => 'install_plugin',
						'plugin_slug' => 'wp-smartpay-paddle',
					],
					[
						'label' => 'Open Website',
						'type'  => 'external_link',
						'url'   => 'https://wpsmartpay.com/paddle',
					],
					[
						'label'      => 'Enable Gateway',
						'type'       => 'toggle_option',
						'option_key' => 'woocommerce_enable_paddle_gateway',
						'value'      => true,
					],
				],
			],
		];

		// Add more integrations as needed.
		add_filter( 'wp_subscription_integrations', $integrations );

		return $integrations;
	}

	/**
	 * Render the Integrations admin page.
	 */
	public function render_integrations_page() {
		$integrations = $this->integrations;

		include 'views/integrations.php';
	}
}
