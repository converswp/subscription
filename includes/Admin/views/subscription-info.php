<?php
use SpringDevs\Subscription\Illuminate\Helper;
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
$cost = $order_item ? Helper::format_price_with_order_item(get_post_meta($post->ID, '_subscrpt_price', true), $order_item_id) : '-';
$qty = $order_item ? 'x' . $order_item->get_quantity() : '-';
$customer = $order ? $order->get_formatted_billing_full_name() : '-';
$customer_id = $order ? $order->get_customer_id() : 0;
$customer_url = $customer_id ? admin_url('user-edit.php?user_id=' . $customer_id) : '';
$start_date = get_post_meta($post->ID, '_subscrpt_start_date', true);
$end_date = get_post_meta($post->ID, '_subscrpt_end_date', true);
$renewal_date = get_post_meta($post->ID, '_subscrpt_next_date', true);
$status_obj = get_post_status_object(get_post_status($post->ID));
$payment_method = $order ? $order->get_payment_method_title() : '-';
$billing_address = $order ? $order->get_formatted_billing_address() : '-';
$shipping_address = $order ? $order->get_formatted_shipping_address() : '-';


?>
<div class="wp-subscription-admin-content">
    <div class="wp-subscription-info-grid" style="display:flex;gap:28px;align-items:flex-start;flex-wrap:nowrap;">
        <!-- Left: Main Info -->
        <div class="wp-subscription-admin-box wp-subscription-info-left" style="background:#f3f4f5;flex:1 1 50%;max-width:50%;margin:25px 0 18px 18px;">
            <h2 style="font-family:Georgia,serif;font-size:1.3em;margin:0 0 12px 0;">Subscription Details</h2>
            <table class="widefat striped wp-subscription-list-table" style="border-radius:8px;overflow:hidden;margin-bottom:16px;font-size:14px;">
                <tbody>
                    <tr>
                        <th style="width:120px;padding:8px 10px;">Product</th>
                        <td style="padding:8px 10px;"><?php echo esc_html($product_name); ?></td>
                    </tr>
                    <tr>
                        <th style="padding:8px 10px;">Cost</th>
                        <td style="padding:8px 10px;"><?php echo $cost; ?></td>
                    </tr>
                    <tr>
                        <th style="padding:8px 10px;">Qty</th>
                        <td style="padding:8px 10px;"><?php echo esc_html($qty); ?></td>
                    </tr>
                    <tr>
                        <th style="padding:8px 10px;">Started date</th>
                        <td style="padding:8px 10px;"><?php echo $start_date ? esc_html(gmdate('F d, Y', $start_date)) : '-'; ?></td>
                    </tr>
                    <tr>
                        <th style="padding:8px 10px;">Payment due date</th>
                        <td style="padding:8px 10px;"><?php echo $renewal_date ? esc_html(gmdate('F d, Y', $renewal_date)) : '-'; ?></td>
                    </tr>
                    <tr>
                        <th style="padding:8px 10px;">Status</th>
                        <td style="padding:8px 10px;"><span class="subscrpt-status-badge subscrpt-status-<?php echo esc_attr($status_obj->name); ?>"><?php echo esc_html($status_obj->label); ?></span></td>
                    </tr>
                    <tr>
                        <th style="padding:8px 10px;">Payment Method</th>
                        <td style="padding:8px 10px;"><?php echo esc_html($payment_method); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Right: Billing & Shipping -->
        <div class="wp-subscription-admin-box wp-subscription-info-right" style="    box-shadow: 0 1px 3px rgb(29 35 39 / 30%);background:#fff;flex:1 1 50%;max-width:50%;margin:25px 0 18px 0;">
            <h2 style="font-family:Georgia,serif;font-size:1.1em;margin:0 0 10px 0;">Billing & Shipping</h2>
            <table class="widefat wp-subscription-list-table" style="border-radius:8px;overflow:hidden;margin-bottom:0;font-size:13px;background:none;">
                <tbody>
                    <tr>
                        <th style="width:70px;padding:8px 10px;color:#888;font-weight:500;">Billing</th>
                        <td style="padding:8px 10px;"><?php echo $billing_address ? wp_kses_post($billing_address) : '-'; ?></td>
                    </tr>
                    <tr>
                        <th style="width:70px;padding:8px 10px;color:#888;font-weight:500;">Shipping</th>
                        <td style="padding:8px 10px;"><?php echo $shipping_address ? wp_kses_post($shipping_address) : '-'; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<style>
.subscrpt-status-badge {
    display: inline-block;
    min-width: 48px;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    color: #222;
    text-align: center;
    letter-spacing: 0.01em;
    background: #e9ecef;
    box-shadow: none;
    text-transform: capitalize;
}
.subscrpt-status-active { background: #27c775 !important; color: #ffffff !important; }
.subscrpt-status-cancelled { background: #fee2e2 !important; color: #b91c1c !important; }
.subscrpt-status-draft { background: #e0e7ef !important; color: #374151 !important; }
.subscrpt-status-pending { background: #fef9c3 !important; color: #b45309 !important; }
.subscrpt-status-expired { background: #e5e7eb !important; color: #6b7280 !important; }
.subscrpt-status-pe_cancelled { background: #ffedd5 !important; color: #b45309 !important; }
@media (max-width: 900px) {
  .wp-subscription-info-left, .wp-subscription-info-right {
    flex-basis: 100% !important;
    max-width: 100% !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
  }
  .wp-subscription-info-grid {
    flex-direction: column !important;
    gap: 18px !important;
  }
}
</style>
