<?php
/**
 * Admin Settings Page Template
 *
 * @package SpringDevs\Subscription\Templates\Admin
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wp-subscription-admin-content">
    <div class="wp-subscription-admin-box">
        <?php include_once dirname(__FILE__) . '/../../includes/Admin/views/settings.php'; ?>
    </div>
</div> 