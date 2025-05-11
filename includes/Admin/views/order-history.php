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
    <table class="widefat striped">
	<thead>
		<tr>
			<th><?php
			esc_html_e( 'Order', 'sdevs_subscrpt' ); ?></th>
			<th></th>
			<th><?php esc_html_e( 'Date', 'sdevs_subscrpt' ); ?></th>
			<th><?php esc_html_e( 'Status', 'sdevs_subscrpt' ); ?></th>
			<th><?php esc_html_e( 'Amount', 'sdevs_subscrpt' ); ?></th>
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
</div>
