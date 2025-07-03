<?php
/**
 * Admin Stats Page Template
 *
 * @package SpringDevs\Subscription\Templates\Admin
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wp-subscription-admin-content">
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
</div> 