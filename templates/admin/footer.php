<?php
/**
 * Admin Footer Template
 *
 * @package SpringDevs\Subscription\Templates\Admin
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div style="text-align:center;margin:38px 0 0 0;font-size:14px;color:#888;">
    <?php printf( __( 'Made with %s by the WPSubscription Team', 'wp_subscription' ), '<span style="color:#e25555;font-size:1.1em;">♥</span>' ); ?>
    <div style="margin-top:6px;">
        <a href="https://wpsubscription.co/contact" target="_blank" style="color:#2563eb;text-decoration:none;"><?php esc_html_e( 'Support', 'wp_subscription' ); ?></a>
        &nbsp;/&nbsp;
        <a href="https://docs.converslabs.com/en" target="_blank" style="color:#2563eb;text-decoration:none;"><?php esc_html_e( 'Docs', 'wp_subscription' ); ?></a>
    </div>
</div> 