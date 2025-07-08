<?php
/**
 * Admin Column Data Template
 *
 * @var string $column Column name.
 * @var int $post_id Post ID.
 * @var \WC_Order $order Order Object.
 */

// HPOS: Safe. Only retrieves WooCommerce order via CRUD, and subscription meta via post meta.
$order_id = get_post_meta( $post_id, '_subscrpt_order_id', true ); // HPOS: Only subscription meta, not order meta.
$order = wc_get_order( $order_id ); // HPOS: Safe, uses WooCommerce CRUD.

if ( $order ) :
	if ( 'subscrpt_start_date' === $column ) :
		$start_date = get_post_meta( $post_id, '_subscrpt_start_date', true );
		echo ! empty( $start_date ) ? esc_html( gmdate( 'F d, Y', $start_date ) ) : '-';
	elseif ( 'subscrpt_customer' === $column ) :
		?>
		<?php echo wp_kses_post( $order->get_formatted_billing_full_name() ); ?>
		<br />
		<a href="mailto:<?php echo wp_kses_post( $order->get_billing_email() ); ?>"><?php echo wp_kses_post( $order->get_billing_email() ); ?></a>
		<br />
		<?php if ( ! empty( $order->get_billing_phone() ) ) : ?>
			<?php esc_html_e( 'Phone', 'wp_subscription' ); ?> : <a
				href="tel:<?php echo esc_js( $order->get_billing_phone() ); ?>"><?php echo esc_js( $order->get_billing_phone() ); ?></a>
		<?php endif; ?>
		<?php
	elseif ( 'subscrpt_next_date' === $column ) :
		$next_date = get_post_meta( $post_id, '_subscrpt_next_date', true );
		echo ! empty( $next_date ) ? esc_html( gmdate( 'F d, Y', $next_date ) ) : '-';
	elseif ( 'subscrpt_status' === $column ) :
		$status_obj = get_post_status_object( get_post_status( $post_id ) );
		?>
		<span class="subscrpt-<?php echo esc_html( $status_obj->name ); ?>"><?php echo esc_html( $status_obj->label ); ?></span>
		<?php
	endif;
else :
	esc_html_e( 'Order not found !!', 'wp_subscription' );
endif;
?> 