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
?>
<div class="wp-subscription-admin-box" style="margin-bottom:18px;">
    <h3 style="font-family:Georgia,serif;font-size:1.1em;margin:0 0 12px 0;">Subscription History</h3>
    <table class="widefat striped wp-subscription-list-table" style="border-radius:8px;overflow:hidden;font-size:14px;">
        <thead>
            <tr>
                <th style="padding:8px 10px;">#</th>
                <th style="padding:8px 10px;">Started On</th>
                <th style="padding:8px 10px;">Recurring</th>
                <th style="padding:8px 10px;">Expiry Date</th>
                <th style="padding:8px 10px;">Status</th>
            </tr>
        </thead>
        <tbody>
        <?php
        foreach ( $histories as $history ) :
            $order_item_id = get_post_meta( $history->subscription_id, '_subscrpt_order_item_id', true );
            $order_item    = $order->get_item( $history->order_item_id );
            $price         = get_post_meta( $history->subscription_id, '_subscrpt_price', true );
            $trial         = get_post_meta( $history->subscription_id, '_subscrpt_trial', true );
            $start_date    = get_post_meta( $history->subscription_id, '_subscrpt_start_date', true );
            $next_date     = get_post_meta( $history->subscription_id, '_subscrpt_next_date', true );
            $status_object = get_post_status_object( get_post_status( $history->subscription_id ) );
        ?>
            <tr>
                <td style="padding:8px 10px;">
                    <a href="<?php echo esc_html( get_edit_post_link( $history->subscription_id ) ); ?>" target="_blank" style="color:#2271b1;text-decoration:underline;">#<?php echo esc_html( $history->subscription_id ); ?></a>
                </td>
                <td style="padding:8px 10px;">
                    <?php echo null == $trial ? ( ! empty( $start_date ) ? esc_html( gmdate( 'F d, Y', $start_date ) ) : '-' ) : '+' . esc_html( $trial ) . ' ' . __( 'free trial', 'sdevs_subscrpt' ); ?>
                </td>
                <td style="padding:8px 10px;">
                    <?php echo wp_kses_post( SpringDevs\Subscription\Illuminate\Helper::format_price_with_order_item( $price, $order_item->get_id() ) ); ?>
                </td>
                <td style="padding:8px 10px;">
                    <?php echo esc_html( ! empty( $start_date ) && ! empty( $next_date ) ? ( $trial == null ? gmdate( 'F d, Y', $next_date ) : gmdate( 'F d, Y', $start_date ) ) : '-' ); ?>
                </td>
                <td style="padding:8px 10px;"><span class="subscrpt-<?php echo esc_attr( $status_object->name ); ?>"><?php echo esc_html( $status_object->label ); ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
