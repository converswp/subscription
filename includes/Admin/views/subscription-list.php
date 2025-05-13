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
<div class="wp-subscription-admin-content list-page">
    <div class="wp-subscription-list-title"><h1 class="wp-heading-inline">Subscriptions</h1></div>
    <div class="wp-subscription-list-header">
        <form method="get">
            <input type="hidden" name="page" value="wp-subscription" />
            <select name="subscrpt_status" value="<?php echo esc_attr($status); ?>">
                <option value=""><?php esc_html_e('All Status', 'wp_subscription'); ?></option>
                <option value="active" <?php selected($status, 'active'); ?>><?php esc_html_e('Active', 'wp_subscription'); ?></option>
                <option value="cancelled" <?php selected($status, 'cancelled'); ?>><?php esc_html_e('Cancelled', 'wp_subscription'); ?></option>
                <option value="draft" <?php selected($status, 'draft'); ?>><?php esc_html_e('Draft', 'wp_subscription'); ?></option>
            </select>
            <select name="date_filter" value="<?php echo esc_attr($date_filter); ?>">
                <option value=""><?php esc_html_e('All Dates', 'wp_subscription'); ?></option>
                <?php foreach ($months as $val => $label): ?>
                    <option value="<?php echo esc_attr($val); ?>" <?php selected($date_filter, $val); ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Search by subscription ID...', 'wp_subscription'); ?>" />
            <select name="per_page">
                <?php foreach ([10, 20, 50, 100] as $n): ?>
                    <option value="<?php echo $n; ?>" <?php selected(isset($_GET['per_page']) ? intval($_GET['per_page']) : 20, $n); ?>><?php echo $n; ?> per page</option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="button">Search</button>
            <?php if ($filters_active): ?>
                <a href="<?php echo esc_url(remove_query_arg(['subscrpt_status','date_filter','s','title','paged'])); ?>" class="button">Reset</a>
            <?php endif; ?>
            <?php if ($filters_active): ?>
                <span>Filters applied</span>
            <?php endif; ?>
        </form>
    </div>
    <h2 class="screen-reader-text">Subscriptions list</h2>
    <table class="wp-list-table widefat fixed striped wp-subscription-modern-table">
        <thead>
            <tr>
                <th style="width:200px;">ID</th>
                <th style="min-width:320px;">Title</th>
                <th style="width:200px;">Customer</th>
                <th style="width:100px;">Start Date</th>
                <th style="width:100px;">Renewal Date</th>
                <th style="width:80px;">Status</th>
                <th style="width:80px;">Actions</th>
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
                $customer_email = $order ? $order->get_billing_email() : '';
                $start_date = get_post_meta($subscription->ID, '_subscrpt_start_date', true);
                $renewal_date = get_post_meta($subscription->ID, '_subscrpt_next_date', true);
                $status_obj = get_post_status_object(get_post_status($subscription->ID));
            ?>
            <tr>
                <td>
                    <a href="<?php echo esc_url(get_edit_post_link($subscription->ID)); ?>" class="subscrpt-id-link">
                        #<?php echo esc_html(get_the_title($subscription->ID)); ?>
                    </a>
                </td>
                <td style="min-width:320px;">
                    <div class="wp-subscription-title-wrap">
                        <span><?php echo esc_html($product_name); ?></span>
                        <div class="wp-subscription-row-actions">
                            <a href="<?php echo esc_url(get_edit_post_link($subscription->ID)); ?>">View</a>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=wp-subscription&action=duplicate&sub_id=' . $subscription->ID)); ?>">Duplicate</a>
                            <a href="<?php echo esc_url(get_delete_post_link($subscription->ID)); ?>">Delete</a>
                        </div>
                    </div>
                </td>
                <td>
                    <?php if ($customer_url): ?>
                        <a href="<?php echo esc_url($customer_url); ?>" target="_blank"><?php echo esc_html($customer); ?></a>
                    <?php else: ?>
                        <?php echo esc_html($customer); ?>
                    <?php endif; ?>
                    <?php if ($customer_email): ?>
                        <div class="wp-subscription-customer-email"><?php echo esc_html($customer_email); ?></div>
                    <?php endif; ?>
                </td>
                <td><?php echo $start_date ? esc_html(gmdate('F d, Y', $start_date)) : '-'; ?></td>
                <td><?php echo $renewal_date ? esc_html(gmdate('F d, Y', $renewal_date)) : '-'; ?></td>
                <td>
                    <span class="subscrpt-status-badge subscrpt-status-<?php echo esc_attr($status_obj->name); ?>">
                        <?php echo esc_html($status_obj->label); ?>
                    </span>
                </td>
                <td>
                    <a href="<?php echo esc_url(get_edit_post_link($subscription->ID)); ?>" class="button button-small"><?php esc_html_e('Edit', 'wp_subscription'); ?></a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else : ?>
            <tr>
                <td colspan="8" class="wp-subscription-list-empty">
                    <?php esc_html_e('No subscriptions found.', 'wp_subscription'); ?>
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?php if ($max_num_pages > 1): ?>
    <div class="wp-subscription-pagination">
        <span class="total">Total <?php echo intval($total); ?></span>
        <?php
        $base_url = remove_query_arg('paged');
        $show_pages = $max_num_pages > 1 || $max_num_pages == 1;
        for ($i = 1; $i <= $max_num_pages; $i++):
            $url = add_query_arg(['paged' => $i, 'per_page' => $per_page], $base_url);
            $is_current = $i == $paged;
        ?>
            <a href="<?php echo esc_url($url); ?>" class="button<?php if ($is_current) echo ' button-primary'; ?>" <?php if ($is_current) echo 'disabled'; ?>><?php echo $i; ?></a>
        <?php endfor; ?>
        <span class="goto-label">Go to</span>
        <form method="get">
            <input type="hidden" name="page" value="wp-subscription" />
            <input type="number" name="paged" min="1" max="<?php echo $max_num_pages; ?>" value="<?php echo $paged; ?>" />
            <input type="hidden" name="per_page" value="<?php echo $per_page; ?>" />
            <button type="submit" class="button">OK</button>
        </form>
    </div>
    <?php endif; ?>
</div>
