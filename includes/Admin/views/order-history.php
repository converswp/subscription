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
<table class="wp-list-table widefat fixed striped wp-subscription-modern-table" style="border-radius:6px;overflow:hidden;box-shadow:0 2px 8px #e0e7ef;">
	<thead>
		<tr>
			<th><?php
			esc_html_e( 'Order', 'wp_subscription' ); ?></th>
			<th></th>
			<th><?php esc_html_e( 'Date', 'wp_subscription' ); ?></th>
			<th><?php esc_html_e( 'Status', 'wp_subscription' ); ?></th>
			<th><?php esc_html_e( 'Amount', 'wp_subscription' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $order_histories as $order_history ) : ?>
			<?php
			$order      = wc_get_order( $order_history->order_id );
			$order_item = $order->get_item( $order_history->order_item_id );
			?>
			<tr>
				<td><a href="<?php echo wp_kses_post( $order->get_edit_order_url() ); ?>" target="_blank"><?php echo wp_kses_post( $order_history->order_id ); ?></a></td>
				<td><?php echo wp_kses_post( order_relation_type_cast( $order_history->type ) ); ?></td>
				<td>
					<?php
					if ( $order ) {
						echo wp_kses_post( gmdate( 'F d, Y', strtotime( $order->get_date_created() ) ) );}
					?>
				</td>
				<td>
				<?php
				if ( $order ) {
					echo esc_html( sdevs_order_status_label( $order->get_status() ) );
				}
				?>
				</td>
				<td>
				<?php
				echo wc_price(
					$order_item->get_total(),
					array(
						'currency' => $order->get_currency(),
					)
				);
				?>
			</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
