<div class="wp-subscription-admin-box wp-subscription-customer-info">
	<div class="customer-details">
		<div class="customer-field">
			<strong><?php esc_html_e( 'Name:', 'wp_subscription' ); ?></strong>
			<?php if ( $order->get_customer_id() ) : ?>
				<a href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . $order->get_customer_id() ) ); ?>" target="_blank">
					<?php echo esc_html( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ); ?>
				</a>
			<?php else : ?>
				<?php echo esc_html( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ); ?>
			<?php endif; ?>
		</div>
		
		<div class="customer-field">
			<strong><?php esc_html_e( 'Email:', 'wp_subscription' ); ?></strong>
			<a href="mailto:<?php echo esc_attr( $order->get_billing_email() ); ?>"><?php echo esc_html( $order->get_billing_email() ); ?></a>
		</div>
		
		<?php if ( ! empty( $order->get_billing_phone() ) ) : ?>
		<div class="customer-field">
			<strong><?php esc_html_e( 'Phone:', 'wp_subscription' ); ?></strong>
			<a href="tel:<?php echo esc_attr( $order->get_billing_phone() ); ?>"><?php echo esc_html( $order->get_billing_phone() ); ?></a>
		</div>
		<?php endif; ?>
		
		<div class="customer-field">
			<strong><?php esc_html_e( 'Billing Address:', 'wp_subscription' ); ?></strong>
			<div><?php echo wp_kses_post( $order->get_formatted_billing_address() ? $order->get_formatted_billing_address() : __( 'No billing address set.', 'wp_subscription' ) ); ?></div>
		</div>
		
		<div class="customer-field">
			<strong><?php esc_html_e( 'Shipping Address:', 'wp_subscription' ); ?></strong>
			<div><?php echo wp_kses_post( $order->get_formatted_shipping_address() ? $order->get_formatted_shipping_address() : __( 'No shipping address set.', 'wp_subscription' ) ); ?></div>
		</div>
	</div>
	
	<hr>
	
	<div class="quick-actions">
		<p><strong><?php esc_html_e( 'Quick Actions:', 'wp_subscription' ); ?></strong></p>
		<p>
			<a class="button button-primary" target="_blank" href="<?php echo esc_url( $order->get_edit_order_url() ); ?>">
				<?php esc_html_e( 'View Order', 'wp_subscription' ); ?>
			</a>
		</p>
		<p>
			<a class="button" target="_blank" href="<?php echo esc_url( wc_get_endpoint_url( 'view-subscription', get_the_ID(), wc_get_page_permalink( 'myaccount' ) ) ); ?>">
				<?php esc_html_e( 'View Frontend', 'wp_subscription' ); ?>
			</a>
		</p>
	</div>
</div>
