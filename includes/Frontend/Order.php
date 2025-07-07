<?php

namespace SpringDevs\Subscription\Frontend;

use SpringDevs\Subscription\Illuminate\Helper;

/**
 * Order class of frontend.
 */
class Order {
	/**
	 * Initialize the class.
	 */
	public function __construct() {
		add_action( 'woocommerce_order_details_after_order_table', array( $this, 'display_subscrpt_details' ) );
	}

	/**
	 * Display subscriptions related to the order.
	 *
	 * @param \WC_Order $order Order Object.
	 *
	 * @return void
	 */
	public function display_subscrpt_details( $order ) {
		$histories = Helper::get_subscriptions_from_order( $order->get_id() );
		
		if ( is_array( $histories ) && count( $histories ) > 0 ) {
			wc_get_template(
				'myaccount/order-subscriptions.php',
				array(
					'order'    => $order,
					'histories' => $histories,
				),
				'subscription',
				WP_SUBSCRIPTION_TEMPLATES
			);
		}
	}
}
