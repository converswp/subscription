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
<div class="wp-subscription-admin-content" style="max-width:1240px;margin:32px auto 0 auto;background:#fff;padding:32px 24px 24px 24px;border-radius:12px;">
    <div style="margin-bottom:24px;"><h1 class="wp-heading-inline">Subscriptions</h1></div>
    <div class="wp-subscription-list-header" style="margin-bottom:28px;display:flex;justify-content:flex-start;align-items:center;flex-wrap:wrap;gap:12px;">
        <form method="get" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
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
            <select name="per_page" style="min-width:90px;">
                <?php foreach ([10, 20, 50, 100] as $n): ?>
                    <option value="<?php echo $n; ?>" <?php selected(isset($_GET['per_page']) ? intval($_GET['per_page']) : 20, $n); ?>><?php echo $n; ?> per page</option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="button button-primary">Search</button>
            <?php if ($filters_active): ?>
                <a href="<?php echo esc_url(remove_query_arg(['subscrpt_status','date_filter','s','title','paged'])); ?>" class="button">Reset</a>
            <?php endif; ?>
            <?php if ($filters_active): ?>
                <span>Filters applied</span>
            <?php endif; ?>
        </form>
    </div>
    <h2 class="screen-reader-text">Subscriptions list</h2>
    <table class="wp-list-table wp-modern-table widefat fixed striped" style="border-radius:12px;overflow:hidden;box-shadow:0 2px 8px #e0e7ef;">
        <thead>
            <tr>
                <th style="width:60px;">ID</th>
                <th style="min-width:320px;">Title</th>
                <th style="width:200px;">Customer</th>
                <th style="width:110px;">Start Date</th>
                <th style="width:110px;">Renewal Date</th>
                <th style="width:90px;">Status</th>
                <th style="width:110px;">Actions</th>
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
                <td style="width:60px;">#<?php echo esc_html($subscription->ID); ?></td>
                <td style="min-width:320px;">
                    <div class="wp-subscription-title-wrap" style="display:flex;align-items:center;">
                        <span><?php echo esc_html($product_name); ?></span>
                        <div class="wp-subscription-row-actions" style="display:flex;align-items:center;gap:10px;margin-left:18px;opacity:0;pointer-events:none;transition:opacity .18s;">
                            <a href="<?php echo esc_url(get_edit_post_link($subscription->ID)); ?>"><?php esc_html_e('View', 'wp_subscription'); ?></a>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=wp-subscription&action=duplicate&sub_id=' . $subscription->ID)); ?>"><?php esc_html_e('Duplicate', 'wp_subscription'); ?></a>
                            <a href="<?php echo esc_url(get_delete_post_link($subscription->ID)); ?>" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this subscription?', 'wp_subscription'); ?>');" style="color:#d93025;">
                                <?php esc_html_e('Delete', 'wp_subscription'); ?>
                            </a>
                        </div>
                    </div>
                </td>
                <td style="width:200px;">
                    <?php if ($customer_url): ?>
                        <a href="<?php echo esc_url($customer_url); ?>" target="_blank"><?php echo esc_html($customer); ?></a>
                    <?php else: ?>
                        <?php echo esc_html($customer); ?>
                    <?php endif; ?>
                    <?php if ($customer_email): ?>
                        <div style="color:#888;font-size:12px;line-height:1.3;word-break:break-all;"><?php echo esc_html($customer_email); ?></div>
                    <?php endif; ?>
                </td>
                <td style="width:110px;"><?php echo $start_date ? esc_html(gmdate('F d, Y', $start_date)) : '-'; ?></td>
                <td style="width:110px;"><?php echo $renewal_date ? esc_html(gmdate('F d, Y', $renewal_date)) : '-'; ?></td>
                <td style="width:90px;"><span class="subscrpt-<?php echo esc_attr($status_obj->name); ?>"><?php echo esc_html($status_obj->label); ?></span></td>
                <td style="width:110px;">
                    <a href="<?php echo esc_url(get_edit_post_link($subscription->ID)); ?>" class="button button-small button-primary"><?php esc_html_e('View/Edit', 'wp_subscription'); ?></a>
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
        $show_pages = $max_num_pages > 1 || $max_num_pages == 1;
        for ($i = 1; $i <= $max_num_pages; $i++):
            $url = add_query_arg(['paged' => $i, 'per_page' => $per_page], $base_url);
            $is_current = $i == $paged;
        ?>
            <a href="<?php echo esc_url($url); ?>" class="button<?php if ($is_current) echo ' button-primary'; ?>" <?php if ($is_current) echo 'disabled'; ?>><?php echo $i; ?></a>
        <?php endfor; ?>
        <span style="margin-left:16px;color:#888;font-size:13px;">Go to</span>
        <form method="get" style="display:inline-block;margin:0;">
            <input type="hidden" name="page" value="wp-subscription" />
            <input type="number" name="paged" min="1" max="<?php echo $max_num_pages; ?>" value="<?php echo $paged; ?>" style="width:48px;" />
            <input type="hidden" name="per_page" value="<?php echo $per_page; ?>" />
            <button type="submit" class="button">OK</button>
        </form>
    </div>
    <?php endif; ?>
    <style>
    .wp-subscription-title-wrap:hover .wp-subscription-row-actions {
        opacity: 1 !important;
        pointer-events: auto !important;
    }
    .wp-subscription-row-actions {
        display: flex !important;
        align-items: center;
        gap: 10px;
        margin-left: 18px;
        background: none !important;
        box-shadow: none !important;
        position: static !important;
        padding: 0 !important;
        border-radius: 0 !important;
        white-space: nowrap;
    }
    .wp-modern-table {
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 2px 8px #e0e7ef;
        border-collapse: separate;
        border-spacing: 0;
    }
    .wp-modern-table th, .wp-modern-table td {
        padding: 14px 12px;
        font-size: 15px;
        border-bottom: 1px solid #f0f2f5;
    }
    .wp-modern-table th {
        background: #f8fafc;
        font-weight: 600;
        color: #222;
    }
    .wp-modern-table tr:last-child td {
        border-bottom: none;
    }
    .wp-modern-table tbody tr:hover {
        background: #f4f7fa;
        transition: background 0.18s;
    }
    @media (max-width: 900px) {
        .wp-modern-table th, .wp-modern-table td { font-size: 13px; padding: 10px 6px; }
    }
    </style>
</div> 