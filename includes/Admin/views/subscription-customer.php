<div class="wp-subscription-admin-box wp-subscription-customer-info" style="box-shadow: none; padding-left: 0px;">
	<table class="booking-customer-details" >
		<tbody>
			<tr>
				<th style="color:#888;font-weight:500;padding:6px 8px 6px 0;width:70px;">Name:</th>
				<td style="padding:6px 0;"><?php echo wp_kses_post( $order->get_formatted_billing_full_name() ); ?></td>
			</tr>
			<tr>
				<th style="color:#888;font-weight:500;padding:6px 8px 6px 0;">Email:</th>
				<td style="padding:6px 0;"><a href="mailto:<?php echo esc_html( $order->get_billing_email() ); ?>" style="color:#2271b1;text-decoration:none;"><?php echo esc_html( $order->get_billing_email() ); ?></a></td>
			</tr>
			<tr>
				<th style="color:#888;font-weight:500;padding:6px 8px 6px 0;">Address:</th>
				<td style="padding:6px 0;"><?php echo wp_kses_post( $order->get_formatted_billing_address() ); ?></td>
			</tr>
			<?php if ( ! empty( $order->get_billing_phone() ) ) : ?>
			<tr>
				<th style="color:#888;font-weight:500;padding:6px 8px 6px 0;">Phone:</th>
				<td style="padding:6px 0;"><?php echo esc_html( $order->get_billing_phone() ); ?></td>
			</tr>
			<?php endif; ?>
			<tr class="view">
				<th>&nbsp;</th>
				<td style="padding-top:10px;"><a class="button" style="font-size:12px;padding:4px 14px;background:#f3f4f6;color:#444;border:none;border-radius:5px;box-shadow:none;" target="_blank" href="<?php echo esc_html( $order->get_edit_order_url() ); ?>">View Order</a></td>
			</tr>
		</tbody>
	</table>
</div>
