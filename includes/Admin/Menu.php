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
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets() {
        wp_enqueue_style(
            'wp-subscription-admin',
            WP_SUBSCRIPTION_ASSETS . '/css/admin.css',
            array(),
            WP_SUBSCRIPTION_VERSION
        );
    }

    /**
     * Create Subscriptions Menu.
     */
    public function create_admin_menu() {
        $parent_slug = 'wp-subscription';
        
        // Main menu
        add_menu_page(
            __( 'WP Subscription', 'wp_subscription' ),
            __( 'WP Subscription', 'wp_subscription' ),
            'manage_woocommerce',
            $parent_slug,
            array( $this, 'render_subscriptions_page' ),
            WP_SUBSCRIPTION_ASSETS . '/images/icons/subscription-20-gray.png',
            40
        );

        // Subscriptions List
        add_submenu_page(
            $parent_slug,
            __( 'Subscriptions', 'wp_subscription' ),
            __( 'Subscriptions', 'wp_subscription' ),
            'manage_woocommerce',
            $parent_slug,
            array( $this, 'render_subscriptions_page' )
        );

        // Stats Overview
        add_submenu_page(
            $parent_slug,
            __( 'Stats Overview', 'wp_subscription' ),
            __( 'Stats Overview', 'wp_subscription' ),
            'manage_woocommerce',
            'wp-subscription-stats',
            array( $this, 'render_stats_page' )
        );

        // Settings
        add_submenu_page(
            $parent_slug,
            __( 'Settings', 'wp_subscription' ),
            __( 'Settings', 'wp_subscription' ),
            'manage_woocommerce',
            'wp-subscription-settings',
            array( $this, 'render_settings_page' )
        );

        // Tools
        add_submenu_page(
            $parent_slug,
            __( 'Tools', 'wp_subscription' ),
            __( 'Tools', 'wp_subscription' ),
            'manage_woocommerce',
            'wp-subscription-tools',
            array( $this, 'render_tools_page' )
        );

        // Support
        add_submenu_page(
            $parent_slug,
            __( 'Support', 'wp_subscription' ),
            __( 'Support', 'wp_subscription' ),
            'manage_woocommerce',
            'wp-subscription-support',
            array( $this, 'render_support_page' )
        );
    }

    /**
     * Render the admin header
     */
    private function render_admin_header() {
        // Get current page slug
        $current = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : 'wp-subscription';
        $menu_items = [
            [
                'slug' => 'wp-subscription',
                'label' => __('Subscriptions', 'wp_subscription'),
                'url'  => admin_url('admin.php?page=wp-subscription'),
            ],
            [
                'slug' => 'wp-subscription-stats',
                'label' => __('Stats Overview', 'wp_subscription'),
                'url'  => admin_url('admin.php?page=wp-subscription-stats'),
            ],
            [
                'slug' => 'wp-subscription-settings',
                'label' => __('Settings', 'wp_subscription'),
                'url'  => admin_url('admin.php?page=wp-subscription-settings'),
            ],
            [
                'slug' => 'wp-subscription-tools',
                'label' => __('Tools', 'wp_subscription'),
                'url'  => admin_url('admin.php?page=wp-subscription-tools'),
            ],
            [
                'slug' => 'wp-subscription-support',
                'label' => __('Support', 'wp_subscription'),
                'url'  => admin_url('admin.php?page=wp-subscription-support'),
            ],
        ];
        ?>
        <div class="wp-subscription-admin-header">
            <div class="wp-subscription-admin-header-left">
                <img src="<?php echo esc_url( WP_SUBSCRIPTION_ASSETS . '/images/logo.png' ); ?>" alt="WP Subscription" class="wp-subscription-logo">
                <nav class="wp-subscription-admin-header-menu">
                    <?php foreach ($menu_items as $item): ?>
                        <a href="<?php echo esc_url($item['url']); ?>" class="<?php echo ($current === $item['slug']) ? 'current' : ''; ?>">
                            <?php echo esc_html($item['label']); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
            <div class="wp-subscription-admin-header-right">
                <a href="#" class="wp-subscription-upgrade-btn"><?php _e( 'Upgrade to Pro', 'wp_subscription' ); ?></a>
            </div>
        </div>
        <?php
    }

    /**
     * Render Subscriptions page
     */
    public function render_subscriptions_page() {
        $this->render_admin_header();
        ?>
        <div class="wp-subscription-admin-content">
            <div class="wp-subscription-admin-box">
                <?php
                // Redirect to the post type list
                wp_safe_redirect( admin_url( 'edit.php?post_type=subscrpt_order' ) );
                exit;
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render Stats page
     */
    public function render_stats_page() {
        $this->render_admin_header();
        ?>
        <div class="wp-subscription-admin-content">
            <div class="wp-subscription-admin-box">
                <h1 class="wp-subscription-admin-title"><?php _e( 'Stats Overview', 'wp_subscription' ); ?></h1>
                <!-- Stats content will go here -->
            </div>
        </div>
        <?php
    }

    /**
     * Render Settings page
     */
    public function render_settings_page() {
        $this->render_admin_header();
        ?>
        <div class="wp-subscription-admin-content">
            <div class="wp-subscription-admin-box">
                <?php include_once dirname(__FILE__) . '/views/settings.php'; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render Tools page
     */
    public function render_tools_page() {
        $this->render_admin_header();
        ?>
        <div class="wp-subscription-admin-content">
            <div class="wp-subscription-admin-box">
                <h1 class="wp-subscription-admin-title"><?php _e( 'Tools', 'wp_subscription' ); ?></h1>
                <!-- Tools content will go here -->
            </div>
        </div>
        <?php
    }

    /**
     * Render Support page
     */
    public function render_support_page() {
        $this->render_admin_header();
        ?>
        <div class="wp-subscription-admin-content">
            <div class="wp-subscription-admin-box">
                <h1 class="wp-subscription-admin-title"><?php _e( 'Support', 'wp_subscription' ); ?></h1>
                <!-- Support content will go here -->
            </div>
        </div>
        <?php
    }
}
