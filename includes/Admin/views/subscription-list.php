<?php if (!isset($date_filter)) { $date_filter = ''; } ?>
<?php
// Determine if filters are active
$filters_active = !empty($status) || !empty($date_filter) || !empty($search);
$months = [];
for ($i = 0; $i < 12; $i++) {
    $month = strtotime("-$i month");
    $months[date('Y-m', $month)] = date('F Y', $month);
}
?>
<div><h1 class="wp-heading-inline">Subscriptions</h1></div>
<div class="wp-subscription-list-header">
    <form method="get">
        <input type="hidden" name="page" value="wp-subscription" />
        <select name="subscrpt_status">
            <option value=""><?php esc_html_e('All Status', 'wp_subscription'); ?></option>
            <option value="active" <?php selected($status, 'active'); ?>><?php esc_html_e('Active', 'wp_subscription'); ?></option>
            <option value="cancelled" <?php selected($status, 'cancelled'); ?>><?php esc_html_e('Cancelled', 'wp_subscription'); ?></option>
            <option value="draft" <?php selected($status, 'draft'); ?>><?php esc_html_e('Draft', 'wp_subscription'); ?></option>
        </select>
        <select name="date_filter">
            <option value=""><?php esc_html_e('All Dates', 'wp_subscription'); ?></option>
            <?php foreach ($months as $val => $label): ?>
                <option value="<?php echo esc_attr($val); ?>" <?php selected($date_filter, $val); ?>><?php echo esc_html($label); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Search Subscriptions...', 'wp_subscription'); ?>" />
        <button type="submit" class="button button-primary">Filter</button>
        <?php if ($filters_active): ?>
            <a href="<?php echo esc_url(remove_query_arg(['subscrpt_status','date_filter','s','paged'])); ?>" class="button">Reset</a>
        <?php endif; ?>
        <?php if ($filters_active): ?>
            <span>Filters applied</span>
        <?php endif; ?>
    </form>
</div>
<h2 class="screen-reader-text">Subscriptions list</h2>
<table class="widefat striped wp-subscription-list-table" style="border-radius:8px;overflow:hidden;">
    <thead>
        <tr>
            <th style="width:60px;">ID</th>
            <th style="width:32%">Title</th>
            <th style="width:18%">Customer</th>
            <th><?php esc_html_e('Start Date', 'wp_subscription'); ?></th>
            <th><?php esc_html_e('Renewal Date', 'wp_subscription'); ?></th>
            <th><?php esc_html_e('Status', 'wp_subscription'); ?></th>
            <th><?php esc_html_e('Actions', 'wp_subscription'); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php if (!empty($subscriptions)) : ?>
        <?php foreach ($subscriptions as $subscription) :
            $order_id = get_post_meta($subscription->ID, '_subscrpt_order_id', true);
            $order = wc_get_order($order_id);
            $order_item_id = get_post_meta($subscription->ID, '_subscrpt_order_item_id', true);
            $order_item = $order ? $order->get_item($order_item_id) : null;
            $product_name = $order_item ? $order_item->get_name() : '-';
            $customer = $order ? $order->get_formatted_billing_full_name() : '-';
            $customer_id = $order ? $order->get_customer_id() : 0;
            $customer_url = $customer_id ? admin_url('user-edit.php?user_id=' . $customer_id) : '';
            $start_date = get_post_meta($subscription->ID, '_subscrpt_start_date', true);
            $renewal_date = get_post_meta($subscription->ID, '_subscrpt_next_date', true);
            $status_obj = get_post_status_object(get_post_status($subscription->ID));
        ?>
        <tr>
            <td style="width:60px;">#<?php echo esc_html($subscription->ID); ?></td>
            <td style="width:32%">
                <div class="wp-subscription-title-wrap">
                    <?php echo esc_html($product_name); ?>
                    <div class="wp-subscription-row-actions">
                        <a href="<?php echo esc_url(get_edit_post_link($subscription->ID)); ?>"><?php esc_html_e('View', 'wp_subscription'); ?></a>
                        <a href="<?php echo esc_url(get_edit_post_link($subscription->ID)); ?>&action=duplicate"><?php esc_html_e('Duplicate', 'wp_subscription'); ?></a>
                        <a href="<?php echo esc_url(get_delete_post_link($subscription->ID)); ?>" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this subscription?', 'wp_subscription'); ?>');" style="color:#d93025;">
                            <?php esc_html_e('Delete', 'wp_subscription'); ?>
                        </a>
                    </div>
                </div>
            </td>
            <td style="width:18%">
                <?php if ($customer_url): ?>
                    <a href="<?php echo esc_url($customer_url); ?>" target="_blank" style="color:#2271b1;text-decoration:underline;">
                        <?php echo esc_html($customer); ?>
                    </a>
                <?php else: ?>
                    <?php echo esc_html($customer); ?>
                <?php endif; ?>
            </td>
            <td><?php echo $start_date ? esc_html(gmdate('F d, Y', $start_date)) : '-'; ?></td>
            <td><?php echo $renewal_date ? esc_html(gmdate('F d, Y', $renewal_date)) : '-'; ?></td>
            <td><span class="subscrpt-<?php echo esc_attr($status_obj->name); ?>"><?php echo esc_html($status_obj->label); ?></span></td>
            <td>
                <a href="<?php echo esc_url(get_edit_post_link($subscription->ID)); ?>" class="button button-small button-primary" style="font-size:13px;"><?php esc_html_e('View/Edit', 'wp_subscription'); ?></a>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php else : ?>
        <tr>
            <td colspan="8" style="text-align:center; color:#888; padding:40px 0;">
                <?php esc_html_e('No subscriptions found.', 'wp_subscription'); ?>
            </td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

<?php if ($max_num_pages > 1): ?>
<div class="wp-subscription-pagination" style="display:flex;justify-content:flex-end;align-items:center;gap:8px;margin-top:24px;">
    <span style="color:#888;font-size:13px;">Total <?php echo intval($total); ?></span>
    <?php
    $base_url = remove_query_arg('paged');
    for ($i = 1; $i <= $max_num_pages; $i++):
        $url = add_query_arg('paged', $i, $base_url);
        $is_current = $i == $paged;
    ?>
        <a href="<?php echo esc_url($url); ?>" class="button<?php if ($is_current) echo ' button-primary'; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>
    <span style="margin-left:16px;color:#888;font-size:13px;">Go to</span>
    <form method="get" style="display:inline-block;margin:0;">
        <input type="hidden" name="page" value="wp-subscription" />
        <input type="number" name="paged" min="1" max="<?php echo $max_num_pages; ?>" value="<?php echo $paged; ?>" style="width:48px;" />
        <button type="submit" class="button">OK</button>
    </form>
</div>
<?php endif; ?> 