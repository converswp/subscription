<?php

namespace SpringDevs\Subscription\Admin;

/**
 * Menu class
 *
 * @package SpringDevs\Subscription\Admin
 */
class Menu {

    /**
     * Initialize the class
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'create_admin_menu' ) );
    }

    /**
     * Create Subscriptions Menu.
     */
    public function create_admin_menu() {
        $slug = 'edit.php?post_type=subscrpt_order';
        add_menu_page( 
            __( 'WP Subscription', 'wp_subscription' ), 
            __( 'WP Subscription', 'wp_subscription' ), 
            'manage_options', 
            $slug, 
            false, 
            WP_SUBSCRIPTION_ASSETS . '/images/icons/subscription-20-gray.png', 
            40 
        );
        // Add a submenu that redirects to the subscriptions list
        add_submenu_page(
            'woocommerce',
            __( 'WP Subscriptions', 'wp_subscription' ),
            __( 'WP Subscriptions', 'wp_subscription' ),
            'manage_woocommerce',
            'wp_subscriptions_list',
            function() {
                wp_safe_redirect( admin_url( 'edit.php?post_type=subscrpt_order' ) );
                exit;
            }
        );
    }
}
