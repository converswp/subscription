<div class="wp-subscription-admin-box wp-subscription-customer-info" style="box-shadow: none; padding-left: 0px;">
	<div style="text-align: center; margin-bottom: 16px;">
		<h4 style="margin: 0 0 8px 0; color: #666;">Customer Information</h4>
	</div>
	<table class="booking-customer-details">
		<tbody>
			<tr class="view">
				<th><?php esc_html_e( 'Name', 'wp_subscription' ); ?></th>
				<td>
					<?php if ( $order->get_customer_id() ) : ?>
						<a href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . $order->get_customer_id() ) ); ?>" target="_blank">
							<?php echo esc_html( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ); ?>
						</a>
					<?php else : ?>
						<?php echo esc_html( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ); ?>
					<?php endif; ?>
				</td>
			</tr>
			<tr class="view">
				<th><?php esc_html_e( 'Email', 'wp_subscription' ); ?></th>
				<td><a href="mailto:<?php echo esc_attr( $order->get_billing_email() ); ?>"><?php echo esc_html( $order->get_billing_email() ); ?></a></td>
			</tr>
			<?php if ( ! empty( $order->get_billing_phone() ) ) : ?>
			<tr class="view">
				<th><?php esc_html_e( 'Phone', 'wp_subscription' ); ?></th>
				<td><a href="tel:<?php echo esc_attr( $order->get_billing_phone() ); ?>"><?php echo esc_html( $order->get_billing_phone() ); ?></a></td>
			</tr>
			<?php endif; ?>
			<tr class="view">
				<th><?php esc_html_e( 'Billing Address', 'wp_subscription' ); ?></th>
				<td><?php echo wp_kses_post( $order->get_formatted_billing_address() ? $order->get_formatted_billing_address() : __( 'No billing address set.', 'wp_subscription' ) ); ?></td>
			</tr>
			<tr class="view">
				<th><?php esc_html_e( 'Shipping Address', 'wp_subscription' ); ?></th>
				<td><?php echo wp_kses_post( $order->get_formatted_shipping_address() ? $order->get_formatted_shipping_address() : __( 'No shipping address set.', 'wp_subscription' ) ); ?></td>
			</tr>
		</tbody>
	</table>
	
	<div style="text-align: center; margin-top: 20px; border-top: 1px solid #eee; padding-top: 16px;">
		<h4 style="margin: 0 0 12px 0; color: #666;">Quick Actions</h4>
		<a class="button button-primary" style="margin-right: 8px;" target="_blank" href="<?php echo esc_url( $order->get_edit_order_url() ); ?>">
			<?php esc_html_e( 'View Order', 'wp_subscription' ); ?>
		</a>
		<a class="button" target="_blank" href="<?php echo esc_url( wc_get_endpoint_url( 'view-subscription', get_the_ID(), wc_get_page_permalink( 'myaccount' ) ) ); ?>">
			<?php esc_html_e( 'View Frontend', 'wp_subscription' ); ?>
		</a>
	</div>
</div>
