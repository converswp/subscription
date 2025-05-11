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
                'slug' => 'wp-subscription-support',
                'label' => __('Support', 'wp_subscription'),
                'url'  => admin_url('admin.php?page=wp-subscription-support'),
            ],
        ];
        ?>
        <div class="wp-subscription-admin-header">
            <div style="width:1240px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;">
                <div class="wp-subscription-admin-header-left" style="display:flex;align-items:center;gap:14px;">
                    <img src="<?php echo esc_url( WP_SUBSCRIPTION_ASSETS . '/images/logo.png' ); ?>" alt="WP Subscription" class="wp-subscription-logo">
                    <span style="font-family:Georgia,serif;font-size:1.5em;font-weight:bold;color:#222;">WP Subscription</span>
                    <nav class="wp-subscription-admin-header-menu">
                        <?php foreach ($menu_items as $item): ?>
                            <a href="<?php echo esc_url($item['url']); ?>" class="<?php echo ($current === $item['slug']) ? 'current' : ''; ?>">
                                <?php echo esc_html($item['label']); ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                </div>
                <div class="wp-subscription-admin-header-right">
                    <a href="https://wpsubscription.co/?utm_source=plugin&utm_medium=admin&utm_campaign=upgrade_pro" class="wp-subscription-upgrade-btn"><?php _e( 'Upgrade to Pro', 'wp_subscription' ); ?></a>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render Subscriptions page
     */
    public function render_subscriptions_page() {
        $this->render_admin_header();

        // Handle filters
        $status = isset($_GET['subscrpt_status']) ? sanitize_text_field($_GET['subscrpt_status']) : '';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
        $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 20;

        $args = [
            'post_type'      => 'subscrpt_order',
            'post_status'    => 'any',
            'posts_per_page' => $per_page,
            'paged'          => $paged,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];

        if ($status) {
            $args['post_status'] = $status;
        }
        if ($search) {
            $args['s'] = $search;
        }
        if ($date_from || $date_to) {
            $args['date_query'] = [];
            if ($date_from) {
                $args['date_query'][] = [ 'after' => $date_from ];
            }
            if ($date_to) {
                $args['date_query'][] = [ 'before' => $date_to ];
            }
        }

        $query = new \WP_Query($args);
        $subscriptions = $query->posts;
        $total = $query->found_posts;
        $max_num_pages = $query->max_num_pages;

        // Get all possible statuses for filter dropdown
        $all_statuses = get_post_stati(['show_in_admin_all_list' => true], 'objects');

        ?>
        <div class="wp-subscription-admin-content" style="max-width:100%;margin:0 auto;">
            <div class="wp-subscription-admin-box">
                <?php include dirname(__FILE__) . '/views/subscription-list.php'; ?>
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
        <div class="wp-subscription-admin-content" style="max-width:1240px;margin:0 auto;">
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
        <div class="wp-subscription-admin-content" style="max-width:1240px;margin:0 auto;">
            <div class="wp-subscription-admin-box">
                <?php include_once dirname(__FILE__) . '/views/settings.php'; ?>
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
        <div class="wp-subscription-admin-content" style="max-width:1240px;margin:0 auto;">
            <!-- HERO VARIANT 1: Emoji -->
            <div class="wp-subscription-hero-upgrade" style="margin-bottom:18px;">
                <div class="wp-subscription-hero-content">
                    <span class="wp-subscription-hero-icon">✨</span>
                    <span class="wp-subscription-hero-title">
                        Unlock advanced features, priority support,<br>
                        and more subscription control and reporting.
                    </span>
                </div>
                <a href="https://wpsubscription.co/?utm_source=plugin&utm_medium=admin&utm_campaign=upgrade_pro"
                   target="_blank"
                   class="wp-subscription-hero-btn">
                    UPGRADE TO PRO
                </a>
            </div>
            
            <!-- Product Overview & Video -->
            <div class="wp-subscription-admin-box" style="margin-bottom:24px;display:flex;gap:32px;align-items:flex-start;flex-wrap:wrap;">
                <div style="flex:2;min-width:260px;">
                    <h3>Product Overview</h3>
                    <p style="font-size:14px;line-height:1.7;margin:0 0 10px 0;">WP Subscription helps you manage WooCommerce subscriptions with ease. Enjoy a modern admin UI, powerful filters, detailed history, and seamless customer management. Designed for speed, clarity, and growth.</p>
                    <ul style="font-size:14px;line-height:1.6;margin:0 0 0 18px;padding:0;list-style:disc;">
                        <li>Modern, compact admin interface</li>
                        <li>Advanced filtering & search</li>
                        <li>Subscription history & activities</li>
                        <li>Easy migration from other plugins</li>
                        <li>Pro: Analytics, automation, integrations, and more!</li>
                    </ul>
                </div>
                <div style="flex:1;min-width:220px;max-width:320px;">
                    <div style="background:#f4f7fa;border-radius:8px;padding:10px 10px 6px 10px;box-shadow:0 2px 8px #e0e7ef;">
                        <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:6px;">
                            <iframe src="https://www.youtube.com/embed/XXXXXXXX" title="WP Subscription Overview" frameborder="0" allowfullscreen style="position:absolute;top:0;left:0;width:100%;height:100%;border-radius:6px;"></iframe>
                        </div>
                        <div style="text-align:center;font-size:13px;color:#888;margin-top:4px;">Watch: Quick Product Tour</div>
                    </div>
                </div>
            </div>

            <!-- Support Resources: 2 rows, 2 columns, each in its own box -->
            <div class="wp-subscription-support-resources" style="display:grid;grid-template-columns:repeat(2,1fr);gap:24px;margin-bottom:24px;">
                <div class="wp-subscription-admin-box">
                    <h3>Documentation</h3>
                    <p style="font-size:14px;margin:0 0 8px 0;">Read our <a href=\"https://wpsubscription.com/docs\" target=\"_blank\" style=\"color:#2271b1;\">comprehensive docs</a> for setup, migration, and advanced usage.</p>
                    <a href="https://wpsubscription.com/docs" target="_blank" class="button button-small" style="font-size:13px;padding:5px 14px;">View Docs</a>
                </div>
                <div class="wp-subscription-admin-box">
                    <h3>Facing An Issue?</h3>
                    <p style="font-size:14px;margin:0 0 8px 0;">If you have a problem, <a href=\"https://wpsubscription.com/support\" target=\"_blank\" style=\"color:#d93025;\">open a support ticket</a> or check our FAQ.</p>
                    <a href="https://wpsubscription.com/support" target="_blank" class="button button-small" style="font-size:13px;padding:5px 14px;">Get Support</a>
                </div>
                <div class="wp-subscription-admin-box">
                    <h3>Request a Feature</h3>
                    <p style="font-size:14px;margin:0 0 8px 0;">Have an idea? <a href=\"https://wpsubscription.com/feature-request\" target=\"_blank\" style=\"color:#2271b1;\">Request a feature</a> or vote on others.</p>
                    <a href="https://wpsubscription.com/feature-request" target="_blank" class="button button-small" style="font-size:13px;padding:5px 14px;">Request Feature</a>
                </div>
                <div class="wp-subscription-admin-box">
                    <h3>Show Your Love</h3>
                    <p style="font-size:14px;margin:0 0 8px 0;">Enjoying WP Subscription? <a href=\"https://wordpress.org/support/plugin/subscription/reviews/\" target=\"_blank\" style=\"color:#f59e42;\">Leave us a review</a> or share your experience!</p>
                    <a href="https://wordpress.org/support/plugin/subscription/reviews/" target="_blank" class="button button-small" style="font-size:13px;padding:5px 14px;">Leave a Review</a>
                </div>
            </div>

            <!-- Free vs Pro Section -->
            <div class="wp-subscription-admin-box" style="margin-bottom:24px;">
                <h3 style="text-align:center;">Free vs Pro: Unlock the Full Power</h3>
                <table class="widefat wp-subscription-list-table" style="margin:0 auto 12px auto;max-width:700px;font-size:14px;">
                    <thead>
                        <tr style="background:#f8fafc;">
                            <th style="width:40%;padding:8px 10px;text-align:left;">Feature</th>
                            <th style="width:30%;padding:8px 10px;text-align:center;">Free</th>
                            <th style="width:30%;padding:8px 10px;text-align:center;">Pro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding:8px 10px;">Modern Admin UI</td>
                            <td style="text-align:center;">✔️</td>
                            <td style="text-align:center;">✔️</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 10px;">Advanced Filters & Search</td>
                            <td style="text-align:center;">✔️</td>
                            <td style="text-align:center;">✔️</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 10px;">Subscription History</td>
                            <td style="text-align:center;">✔️</td>
                            <td style="text-align:center;">✔️</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 10px;">Migration Tools</td>
                            <td style="text-align:center;">✔️</td>
                            <td style="text-align:center;">✔️</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 10px;">Variable Product</td>
                            <td style="text-align:center;color:#bbb;">—</td>
                            <td style="text-align:center;">✔️</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 10px;">Delivery Schedule</td>
                            <td style="text-align:center;color:#bbb;">—</td>
                            <td style="text-align:center;">✔️</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 10px;">Analytics Dashboard</td>
                            <td style="text-align:center;color:#bbb;">—</td>
                            <td style="text-align:center;">✔️</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 10px;">Automations & Triggers</td>
                            <td style="text-align:center;color:#bbb;">—</td>
                            <td style="text-align:center;">✔️</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 10px;">Premium Integrations</td>
                            <td style="text-align:center;color:#bbb;">—</td>
                            <td style="text-align:center;">✔️</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 10px;">Priority Support</td>
                            <td style="text-align:center;color:#bbb;">—</td>
                            <td style="text-align:center;">✔️</td>
                        </tr>
                    </tbody>
                </table>
                <div style="text-align:center;margin-top:10px;">
                    <a href="https://wpsubscription.co/?utm_source=plugin&utm_medium=admin&utm_campaign=upgrade_pro" target="_blank" class="button button-primary button-small" style="font-size:15px;padding:8px 22px;">Upgrade to Pro</a>
                </div>
            </div>
        </div>
        <style>
        @keyframes floatY { 0% { transform: translateY(0); } 100% { transform: translateY(-10px); } }
        .wp-subscription-admin-box:hover { box-shadow:0 4px 16px #e0e7ef; transition:box-shadow .2s; }
        .wp-subscription-pro-callout { background:#2271b1!important; }
        .wp-subscription-pro-callout h2, .wp-subscription-pro-callout p { color:#fff!important; }
        .wp-subscription-pro-callout .button-primary { background:#fff!important;color:#2271b1!important; }
        .wp-subscription-pro-callout .button-primary:hover { background:#e0e7ef!important; }
        @media (max-width: 900px) {
            .wp-subscription-support-resources { grid-template-columns:1fr!important; }
        }
        .wp-subscription-hero-upgrade {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #2196f3;
            border-radius: 12px;
            padding: 32px 36px;
            margin-bottom: 28px;
            overflow: hidden;
            min-height: 90px;
        }
        .wp-subscription-hero-upgrade::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: repeating-linear-gradient(135deg, rgba(255,255,255,0.07) 0 2px, transparent 2px 40px);
            opacity: 0.25;
            pointer-events: none;
        }
        .wp-subscription-hero-content {
            display: flex;
            align-items: center;
            gap: 18px;
            z-index: 1;
        }
        .wp-subscription-hero-icon {
            font-size: 2em;
            line-height: 1;
        }
        .wp-subscription-hero-title {
            font-size: 2em;
            font-weight: bold;
            color: #fff;
            line-height: 1.2;
        }
        .wp-subscription-hero-btn {
            z-index: 1;
            background: #fff;
            color: #2196f3;
            font-weight: 600;
            font-size: 1.1em;
            padding: 16px 32px;
            border-radius: 8px;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(33,150,243,0.08);
            transition: background 0.18s, color 0.18s, transform 0.18s;
            border: none;
            outline: none;
            display: inline-block;
            text-align: center;
        }
        .wp-subscription-hero-btn:hover {
            background: #e3f0fd;
            color: #1565c0;
            transform: translateY(-2px) scale(1.03);
        }
        </style>
        <?php
    }
}
