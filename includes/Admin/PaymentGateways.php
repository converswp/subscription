<?php

namespace SpringDevs\Subscription\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PaymentGateways class
 *
 * @package SpringDevs\Subscription\Admin
 */
class PaymentGateways {
	/**
	 * Initialize the class
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 20 );
		add_filter( 'wp_subscription_admin_header_menu_items', [ $this, 'add_delivery_menu_item' ], 10, 2 );
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
			__( 'Payment Gateways', 'wp_subscription' ),
			__( 'Payment Gateways', 'wp_subscription' ),
			'manage_options',
			'wp-subscription-payment-gateways',
			[ $this, 'render_payment_gateways_page' ],
			40
		);
	}

	/**
	 * Add Payment Gateway link to the WP Subscription admin header menu.
	 *
	 * @param array  $menu_items Array of menu items.
	 * @param string $current Current active menu item slug.
	 */
	public function add_delivery_menu_item( $menu_items, $current ) {
		$menu_items[] = [
			'slug'  => 'wp-subscription-payment-gateways',
			'label' => __( 'Payment Gateways', 'wp_subscription' ),
			'url'   => admin_url( 'admin.php?page=wp-subscription-payment-gateways' ),
		];
		return $menu_items;
	}

	/**
	 * Render the Payment Gateways admin page.
	 */
	public function render_payment_gateways_page() {
		echo '<div class="wrap"> yeeeeeeeeeeeeeeeeeeeeeeeee </div>';
	}
}
