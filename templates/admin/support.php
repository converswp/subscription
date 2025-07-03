<?php
/**
 * Admin Support Page Template
 *
 * @package SpringDevs\Subscription\Templates\Admin
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wp-subscription-admin-content">
    <?php if ( ! class_exists('Sdevs_Wc_Subscription_Pro') ) : ?>
    <!-- HERO VARIANT 1: Emoji -->
    <div class="wp-subscription-hero-upgrade" style="margin-bottom:18px;">
        <div class="wp-subscription-hero-content">
            <span class="wp-subscription-hero-icon">✨</span>
            <span class="wp-subscription-hero-title">
                <?php esc_html_e( 'Unlock advanced features, priority support, and more subscription control and reporting.', 'wp_subscription' ); ?>
            </span>
        </div>
        <a href="https://wpsubscription.co/?utm_source=plugin&utm_medium=admin&utm_campaign=upgrade_pro"
           target="_blank"
           class="wp-subscription-hero-btn">
            <?php esc_html_e( 'UPGRADE TO PRO', 'wp_subscription' ); ?>
        </a>
    </div>
    <?php endif; ?>
    
    <!-- Product Overview & Video -->
    <div class="wp-subscription-admin-box" style="margin-bottom:24px;display:flex;gap:32px;align-items:flex-start;flex-wrap:wrap;">
        <div style="flex:1;">
            <h3><?php esc_html_e( 'Product Overview', 'wp_subscription' ); ?></h3>
            <p style="font-size:14px;line-height:1.7;margin:0 0 10px 0;">
                <?php esc_html_e( 'WPSubscription helps you to sell products and services on a recurring basis using your existing WooCommerce store. Whether you\'re offering digital licenses, physical product boxes, or ongoing service plans, this plugin provides the tools to build and manage subscription models.', 'wp_subscription' ); ?>
            </p>
            <ul style="font-size:14px;line-height:1.6;margin:0 0 0 18px;padding:0;list-style:disc;">
                <li><?php esc_html_e( 'Create simple or variable subscription products', 'wp_subscription' ); ?></li>
                <li><?php esc_html_e( 'Set billing intervals (daily, weekly, monthly, yearly)', 'wp_subscription' ); ?></li>
                <li><?php esc_html_e( 'Offer free trials and sign-up fees', 'wp_subscription' ); ?></li>
                <li><?php esc_html_e( 'Allow customers to cancel or renew subscriptions manually', 'wp_subscription' ); ?></li>
                <li><?php esc_html_e( 'View and manage subscriptions from the admin dashboard', 'wp_subscription' ); ?></li>
                <li><?php esc_html_e( 'Customize subscription behavior and role assignment', 'wp_subscription' ); ?></li>
                <li><?php esc_html_e( 'Integrate with payment gateways that support recurring billing (Stripe, PayPal)', 'wp_subscription' ); ?></li>
            </ul>
        </div>
        <div style="flex:1;">
            <div style="background:#f4f7fa;border-radius:8px;padding:10px 10px 6px 10px;box-shadow:0 2px 8px #e0e7ef;">
                <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:6px;text-align: center;">
                    <iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/o8usgcZp1nY?si=iPb5z7bvcy0rIyOQ" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- PRO Features List -->
    <div class="wp-subscription-admin-box wp-subscription-pro-features" style="margin-bottom:32px;">
        <div style="font-size:1.3em;font-weight:600;margin-bottom:12px;display:flex;align-items:center;gap:10px;">
            <svg width="28" height="28" fill="none" viewBox="0 0 28 28"><circle cx="14" cy="14" r="14" fill="#2196f3"/><path d="M9 14.5l3 3 7-7" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span><?php esc_html_e( 'WPSubscription PRO Features', 'wp_subscription' ); ?></span>
        </div>
        <ul class="wp-subscription-pro-feature-list" style="list-style:none;padding:0;margin:0;display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px;">
            <li style="background:#f4f7fa;border-radius:8px;padding:18px 20px;display:flex;align-items:center;gap:14px;box-shadow:0 2px 8px #e0e7ef;">
                <span style="font-size:1.5em;color:#2196f3;">🔀</span>
                <span><b><?php esc_html_e( 'Variable Product', 'wp_subscription' ); ?></b><br><span style="color:#555;font-size:0.98em;"><?php esc_html_e( 'Offer flexible subscription options for variable products.', 'wp_subscription' ); ?></span></span>
            </li>
            <li style="background:#f4f7fa;border-radius:8px;padding:18px 20px;display:flex;align-items:center;gap:14px;box-shadow:0 2px 8px #e0e7ef;">
                <span style="font-size:1.5em;color:#2196f3;">🚚</span>
                <span><b><?php esc_html_e( 'Delivery Schedule', 'wp_subscription' ); ?></b><br><span style="color:#555;font-size:0.98em;"><?php esc_html_e( 'Set custom delivery intervals for each subscription.', 'wp_subscription' ); ?></span></span>
            </li>
            <li style="background:#f4f7fa;border-radius:8px;padding:18px 20px;display:flex;align-items:center;gap:14px;box-shadow:0 2px 8px #e0e7ef;">
                <span style="font-size:1.5em;color:#2196f3;">📜</span>
                <span><b><?php esc_html_e( 'Subscription History', 'wp_subscription' ); ?></b><br><span style="color:#555;font-size:0.98em;"><?php esc_html_e( 'Track all changes and events for every subscription.', 'wp_subscription' ); ?></span></span>
            </li>
            <li style="background:#f4f7fa;border-radius:8px;padding:18px 20px;display:flex;align-items:center;gap:14px;box-shadow:0 2px 8px #e0e7ef;">
                <span style="font-size:1.5em;color:#2196f3;">⏳</span>
                <span><b><?php esc_html_e( 'More Subscription Durations', 'wp_subscription' ); ?></b><br><span style="color:#555;font-size:0.98em;"><?php esc_html_e( 'Offer more flexible and custom subscription periods.', 'wp_subscription' ); ?></span></span>
            </li>
            <li style="background:#f4f7fa;border-radius:8px;padding:18px 20px;display:flex;align-items:center;gap:14px;box-shadow:0 2px 8px #e0e7ef;">
                <span style="font-size:1.5em;color:#2196f3;">💶</span>
                <span><b><?php esc_html_e( 'Sign Up Fee', 'wp_subscription' ); ?></b><br><span style="color:#555;font-size:0.98em;"><?php esc_html_e( 'Charge a one-time sign up fee for new subscribers.', 'wp_subscription' ); ?></span></span>
            </li>
            <li style="background:#f4f7fa;border-radius:8px;padding:18px 20px;display:flex;align-items:center;gap:14px;box-shadow:0 2px 8px #e0e7ef;">
                <span style="font-size:1.5em;color:#2196f3;">⏩</span>
                <span><b><?php esc_html_e( 'Early Renewal', 'wp_subscription' ); ?></b><br><span style="color:#555;font-size:0.98em;"><?php esc_html_e( 'Allow customers to renew their subscription before expiry.', 'wp_subscription' ); ?></span></span>
            </li>
            <li style="background:#f4f7fa;border-radius:8px;padding:18px 20px;display:flex;align-items:center;gap:14px;box-shadow:0 2px 8px #e0e7ef;">
                <span style="font-size:1.5em;color:#2196f3;">💳</span>
                <span><b><?php esc_html_e( 'Renewal Price', 'wp_subscription' ); ?></b><br><span style="color:#555;font-size:0.98em;"><?php esc_html_e( 'Set a different price for subscription renewals.', 'wp_subscription' ); ?></span></span>
            </li>
        </ul>
    </div>

    <!-- Support Resources: 2 rows, 2 columns, each in its own box -->
    <div class="wp-subscription-support-resources" style="display:grid;grid-template-columns:repeat(2,1fr);gap:24px;margin-bottom:24px;">
        <div class="wp-subscription-admin-box">
            <h3><?php esc_html_e( 'Documentation', 'wp_subscription' ); ?></h3>
            <p style="font-size:14px;margin:0 0 8px 0;"><?php printf( __( 'Read our <a href="%s" target="_blank" style="color:#2271b1;">comprehensive docs</a> for setup, migration, and advanced usage.', 'wp_subscription' ), 'https://docs.converslabs.com/en' ); ?></p>
            <a href="https://docs.converslabs.com/en" target="_blank" class="button button-small" style="font-size:13px;padding:5px 14px;"><?php esc_html_e( 'View Docs', 'wp_subscription' ); ?></a>
        </div>
        <div class="wp-subscription-admin-box">
            <h3><?php esc_html_e( 'Facing An Issue?', 'wp_subscription' ); ?></h3>
            <p style="font-size:14px;margin:0 0 8px 0;"><?php printf( __( 'If you have a problem, <a href="%s" target="_blank" style="color:#d93025;">open a support ticket</a> or check our FAQ.', 'wp_subscription' ), 'https://wpsubscription.co/contact' ); ?></p>
            <a href="https://wpsubscription.co/contact" target="_blank" class="button button-small" style="font-size:13px;padding:5px 14px;"><?php esc_html_e( 'Get Support', 'wp_subscription' ); ?></a>
        </div>
        <div class="wp-subscription-admin-box">
            <h3><?php esc_html_e( 'Request a Feature', 'wp_subscription' ); ?></h3>
            <p style="font-size:14px;margin:0 0 8px 0;"><?php printf( __( 'Have an idea? <a href="%s" target="_blank" style="color:#2271b1;">Request a feature</a> or vote on others.', 'wp_subscription' ), 'https://wpsubscription.co/contact' ); ?></p>
            <a href="https://wpsubscription.co/contact" target="_blank" class="button button-small" style="font-size:13px;padding:5px 14px;"><?php esc_html_e( 'Request Feature', 'wp_subscription' ); ?></a>
        </div>
        <div class="wp-subscription-admin-box">
            <h3><?php esc_html_e( 'Show Your Love', 'wp_subscription' ); ?></h3>
            <p style="font-size:14px;margin:0 0 8px 0;"><?php printf( __( 'Enjoying WPSubscription? <a href="%s" target="_blank" style="color:#f59e42;">Leave us a review</a> or share your experience!', 'wp_subscription' ), 'https://wordpress.org/support/plugin/subscription/reviews/' ); ?></p>
            <a href="https://wordpress.org/support/plugin/subscription/reviews/" target="_blank" class="button button-small" style="font-size:13px;padding:5px 14px;"><?php esc_html_e( 'Leave a Review', 'wp_subscription' ); ?></a>
        </div>
    </div>
</div> 