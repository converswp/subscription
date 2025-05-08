<?php

namespace SpringDevs\Subscription\Admin;

/**
 * Class Settings
 *
 * @package SpringDevs\Subscription\Admin
 */
class Settings {

	/**
	 * Initialize the class.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 30 );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register submenu on `Subscriptions` menu.
	 *
	 * @return void
	 */
	public function admin_menu() {
		$post_type_link = 'edit.php?post_type=subscrpt_order';

		add_submenu_page( 
			$post_type_link, 
			__( 'WP Subscription Settings', 'sdevs_subscrpt_pro' ),
			__( 'Settings', 'sdevs_subscrpt_pro' ), 
			'manage_options', 
			'edit.php?post_type=wp_subscription_settings',
			array( $this, 'settings_content' ),
			40
		);
	}

	/**
	 * Register settings options.
	 **/
	public function register_settings() {
		register_setting( 'wp_subscription_settings', 'wp_subscription_renewal_process' );
		register_setting( 'wp_subscription_settings', 'wp_subscription_manual_renew_cart_notice' );
		register_setting( 'wp_subscription_settings', 'wp_subscription_active_role' );
		register_setting( 'wp_subscription_settings', 'wp_subscription_unactive_role' );
		register_setting( 'wp_subscription_settings', 'wp_subscription_stripe_auto_renew' );
		register_setting( 'wp_subscription_settings', 'wp_subscription_auto_renewal_toggle' );

		do_action( 'subscrpt_register_settings', 'subscrpt_settings' );
	}

	/**
	 * Settings HTML.
	 */
	public function settings_content() {
		include 'views/settings.php';
	}
}
