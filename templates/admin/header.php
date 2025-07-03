<?php
/**
 * Admin Header Template
 *
 * @package SpringDevs\Subscription\Templates\Admin
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
        'label' => __('Stats', 'wp_subscription'),
        'url'  => admin_url('admin.php?page=wp-subscription-stats'),
    ],
    [
        'slug' => 'wp-subscription-settings',
        'label' => __('Settings', 'wp_subscription'),
        'url'  => admin_url('admin.php?page=wp-subscription-settings'),
    ]
];
// Allow pro plugin to inject menu items
$menu_items = apply_filters('wp_subscription_admin_header_menu_items', $menu_items, $current);
$menu_items = array_merge($menu_items, [
    [
        'slug' => 'wp-subscription-support',
        'label' => __('Support', 'wp_subscription'),
        'url'  => admin_url('admin.php?page=wp-subscription-support'),
    ],
]);
?>
<div class="wp-subscription-admin-header">
    <div style="width:1240px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;">
        <div class="wp-subscription-admin-header-left" style="display:flex;align-items:center;gap:14px;">
            <img style="height:30px;" src="<?php echo esc_url( WP_SUBSCRIPTION_ASSETS . '/images/logo-title.svg' ); ?>" alt="WPSubscription" class="wp-subscription-logo">
            <nav class="wp-subscription-admin-header-menu">
                <?php foreach ($menu_items as $item): ?>
                    <a href="<?php echo esc_url($item['url']); ?>" class="<?php echo ($current === $item['slug']) ? 'current' : ''; ?>">
                        <?php echo esc_html($item['label']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
        <div class="wp-subscription-admin-header-right">
            <?php if ( ! class_exists('Sdevs_Wc_Subscription_Pro') ) : ?>
            <a target="_blank" href="https://wpsubscription.co/?utm_source=plugin&utm_medium=admin&utm_campaign=upgrade_pro" class="wp-subscription-upgrade-btn"><?php _e( 'Upgrade to Pro', 'wp_subscription' ); ?></a>
            <?php endif; ?>
        </div>
    </div>
</div> 