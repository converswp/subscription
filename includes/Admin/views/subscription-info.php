<?php
/*
STYLE GUIDE FOR WP SUBSCRIPTION ADMIN PAGES:
- Use .wp-subscription-admin-content for main content area.
- Use .wp-subscription-admin-box for white card/box with shadow and 6-8px border-radius.
- Use .widefat.wp-subscription-list-table for tables, with no outer border, only row bottom borders.
- Use compact table cells: font-size 14px, padding 8px 10px.
- Use .button, .button-primary, .button-small for actions, with rounded corners, soft backgrounds, and subtle hover.
- Use pill-shaped status labels (e.g., .subscrpt-active) for status.
- Use Georgia, serif for titles, system sans-serif for body.
- Keep all sections visually consistent, compact, and modern.
- Avoid excessive spacing or large paddings.
- All new UI/UX changes must follow these conventions.
*/
if (!isset($post) || !is_object($post)) {
    global $post;
}
$order_id = get_post_meta($post->ID, '_subscrpt_order_id', true);
$order = wc_get_order($order_id);
$order_item_id = get_post_meta($post->ID, '_subscrpt_order_item_id', true);
$order_item = $order ? $order->get_item($order_item_id) : null;
$product_name = $order_item ? $order_item->get_name() : '-';
$customer = $order ? $order->get_formatted_billing_full_name() : '-';
$customer_id = $order ? $order->get_customer_id() : 0;
$customer_url = $customer_id ? admin_url('user-edit.php?user_id=' . $customer_id) : '';
$start_date = get_post_meta($post->ID, '_subscrpt_start_date', true);
$end_date = get_post_meta($post->ID, '_subscrpt_end_date', true);
$renewal_date = get_post_meta($post->ID, '_subscrpt_next_date', true);
$status_obj = get_post_status_object(get_post_status($post->ID));

// Load subscription history and activities
ob_start();
include dirname(__FILE__) . '/order-history.php';
$history_html = ob_get_clean();
ob_start();
include dirname(__FILE__) . '/required-notice.php';
$activities_html = ob_get_clean();
?>
<div class="wp-subscription-admin-content" style="max-width:900px;margin:0 auto;">
    <div class="wp-subscription-admin-box" style="max-width:600px;margin:24px auto 18px auto;">
        <h2 style="font-family:Georgia,serif;font-size:1.3em;margin:0 0 12px 0;">Subscription Details</h2>
        <table class="widefat striped wp-subscription-list-table" style="border-radius:8px;overflow:hidden;margin-bottom:16px;font-size:14px;">
            <tbody>
                <tr>
                    <th style="width:120px;padding:8px 10px;">Product</th>
                    <td style="padding:8px 10px;"><?php echo esc_html($product_name); ?></td>
                </tr>
                <tr>
                    <th style="padding:8px 10px;">Customer</th>
                    <td style="padding:8px 10px;">
                        <?php if ($customer_url): ?>
                            <a href="<?php echo esc_url($customer_url); ?>" target="_blank" style="color:#2271b1;text-decoration:underline;">
                                <?php echo esc_html($customer); ?>
                            </a>
                        <?php else: ?>
                            <?php echo esc_html($customer); ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th style="padding:8px 10px;">Start Date</th>
                    <td style="padding:8px 10px;"><?php echo $start_date ? esc_html(gmdate('F d, Y', $start_date)) : '-'; ?></td>
                </tr>
                <tr>
                    <th style="padding:8px 10px;">End Date</th>
                    <td style="padding:8px 10px;"><?php echo $end_date ? esc_html(gmdate('F d, Y', $end_date)) : '-'; ?></td>
                </tr>
                <tr>
                    <th style="padding:8px 10px;">Renewal Date</th>
                    <td style="padding:8px 10px;"><?php echo $renewal_date ? esc_html(gmdate('F d, Y', $renewal_date)) : '-'; ?></td>
                </tr>
                <tr>
                    <th style="padding:8px 10px;">Status</th>
                    <td style="padding:8px 10px;"><span class="subscrpt-<?php echo esc_attr($status_obj->name); ?>"><?php echo esc_html($status_obj->label); ?></span></td>
                </tr>
            </tbody>
        </table>
        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <a href="<?php echo esc_url(get_edit_post_link($post->ID)); ?>" class="button button-small button-primary" style="font-size:13px;padding:5px 14px;">Edit</a>
            <a href="<?php echo esc_url(get_delete_post_link($post->ID)); ?>" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this subscription?', 'wp_subscription'); ?>');" class="button button-small" style="font-size:13px;padding:5px 14px;color:#d93025;">
                <?php esc_html_e('Delete', 'wp_subscription'); ?>
            </a>
        </div>
    </div>
    <?php echo $history_html; ?>
    <?php echo $activities_html; ?>
</div>
