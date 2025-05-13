<div class="wp-subscription-admin-box wp-subscription-customer-info" style="box-shadow: none; padding-left: 0px;">
	<table class="booking-customer-details" >
		<tbody>
			<tr>
				<td style="width:70px;">Name:</td>
				<td style="padding:6px 0;"><?php echo wp_kses_post( $order->get_formatted_billing_full_name() ); ?></td>
			</tr>
			<tr>
				<td>Email:</td>
				<td style=""><a href="mailto:<?php echo esc_html( $order->get_billing_email() ); ?>" style="color:#2271b1;text-decoration:none;"><?php echo esc_html( $order->get_billing_email() ); ?></a></td>
			</tr>
			<tr>
				<td>Address:</td>
				<td style=""><?php echo wp_kses_post( $order->get_formatted_billing_address() ); ?></td>
			</tr>
			<?php if ( ! empty( $order->get_billing_phone() ) ) : ?>
			<tr>
				<td>Phone:</td>
				<td style=""><?php echo esc_html( $order->get_billing_phone() ); ?></td>
			</tr>
			<?php endif; ?>
			<tr class="view">
				<td>&nbsp;</th>
				<td style=""><a class="button" style="font-size:12px;padding:4px 14px;background:#f3f4f6;color:#444;border:none;border-radius:5px;box-shadow:none;" target="_blank" href="<?php echo esc_html( $order->get_edit_order_url() ); ?>">View Order</a></td>
			</tr>
		</tbody>
	</table>
</div>
