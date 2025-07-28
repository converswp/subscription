<?php

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
use SpringDevs\Subscription\Utils\Product;
use SpringDevs\Subscription\Utils\ProductFactory;
use SpringDevs\Subscription\Utils\SubscriptionProduct;

/**
 * Generate Url for Subscription Action.
 *
 * @param string $action Action.
 * @param string $nonce nonce.
 * @param int    $subscription_id Subscription ID.
 *
 * @return string
 */
function subscrpt_get_action_url( $action, $nonce, $subscription_id ) {
	return add_query_arg(
		array(
			'subscrpt_id' => $subscription_id,
			'action'      => $action,
			'wpnonce'     => $nonce,
		),
		wc_get_endpoint_url( 'view-subscription', $subscription_id, wc_get_page_permalink( 'myaccount' ) )
	);
}


function subscrpt_get_typos( $number, $typo ) {
	if ( $number == 1 && $typo == 'days' ) {
		return __( 'day', 'wp_subscription' );
	} elseif ( $number == 1 && $typo == 'weeks' ) {
		return __( 'week', 'wp_subscription' );
	} elseif ( $number == 1 && $typo == 'months' ) {
		return __( 'month', 'wp_subscription' );
	} elseif ( $number == 1 && $typo == 'years' ) {
		return __( 'year', 'wp_subscription' );
	} else {
		return $typo;
	}
}

/**
 * Format time with trial.
 *
 * @param mixed       $time Time.
 * @param null|string $trial Trial.
 *
 * @return string
 */
function subscrpt_next_date( $time, $trial = null ) {
	if ( null === $trial ) {
		$start_date = time();
	} else {
		$start_date = strtotime( $trial );
	}

	return gmdate( 'F d, Y', strtotime( $time, $start_date ) );
}

/**
 * Check if subscription-pro activated.
 *
 * @return bool
 */
function subscrpt_pro_activated(): bool {
	return class_exists( 'Sdevs_Wc_Subscription_Pro' );
}

/**
 * Get renewal process settings.
 *
 * @return bool
 */
function subscrpt_is_auto_renew_enabled() {
	return 'auto' === get_option( 'subscrpt_renewal_process', 'auto' );
}

/**
 * Count total payments made for a subscription (including original + renewals).
 *
 * @param int $subscription_id Subscription ID.
 * @return int Number of payments made.
 */
function subscrpt_count_payments_made( $subscription_id ) {
	global $wpdb;
	
	$table_name = $wpdb->prefix . 'subscrpt_order_relation';
	
	// Get all relations for this subscription
	$relations = $wpdb->get_results( $wpdb->prepare(
		"SELECT sr.*, p.post_status, p.post_date 
		FROM {$table_name} sr 
		INNER JOIN {$wpdb->posts} p ON sr.order_id = p.ID 
		WHERE sr.subscription_id = %d
		ORDER BY p.post_date ASC",
		$subscription_id
	) );
	
	// Define all payment-related order types (allow filtering for extensibility)
	$payment_types = apply_filters( 'subscrpt_payment_order_types', array( 'new', 'renew', 'early-renew' ) );
	
	// Count successful payments
	$successful_count = 0;
	foreach ( $relations as $relation ) {
		// Count all payment-related types
		if ( in_array( $relation->type, $payment_types ) ) {
			// Get the actual WooCommerce order
			$order = wc_get_order( $relation->order_id );
			if ( $order ) {
				// Check if order was paid/successful
				if ( $order->is_paid() || in_array( $order->get_status(), array( 'completed', 'processing', 'on-hold' ) ) ) {
					$successful_count++;
				}
			}
		}
	}
	
	return $successful_count;
}

/**
 * Check if subscription has reached its maximum payment limit.
 *
 * @param int $subscription_id Subscription ID.
 * @return bool True if limit reached, false otherwise.
 */
function subscrpt_is_max_payments_reached( $subscription_id ) {
	// Get the product ID from subscription
	$product_id = get_post_meta( $subscription_id, '_subscrpt_product_id', true );
	if ( ! $product_id ) {
		return false;
	}
	
	// Get maximum payments from product
	$max_payments = get_post_meta( $product_id, '_subscrpt_max_no_payment', true );
	
	// Allow override of total installments
	$max_payments = apply_filters( 'subscrpt_split_payment_total_override', $max_payments, $subscription_id, $product_id );
	
	// If no limit set or unlimited, more payments are allowed
	if ( empty( $max_payments ) || $max_payments <= 0 ) {
		return false;
	}
	
	// Count payments made
	$payments_made = subscrpt_count_payments_made( $subscription_id );
	
	// Check if limit reached
	$is_reached = $payments_made >= $max_payments;
	
	// Fire action when split payment plan is completed (first time only)
	if ( $is_reached && ! get_post_meta( $subscription_id, '_subscrpt_split_payment_completed_fired', true ) ) {
		// Allow customization of subscription status after completion
		$expire_status = apply_filters( 'subscrpt_split_payment_expire_status', 'expired', $subscription_id, $payments_made, $max_payments );
		
		// Update subscription status if different from current
		$current_status = get_post_status( $subscription_id );
		if ( $current_status !== $expire_status ) {
			wp_update_post( array(
				'ID' => $subscription_id,
				'post_status' => $expire_status
			) );
		}
		
		do_action( 'subscrpt_split_payment_completed', $subscription_id, $payments_made, $max_payments );
		update_post_meta( $subscription_id, '_subscrpt_split_payment_completed_fired', true );
	}
	
	return $is_reached;
}

/**
 * Get remaining payments for a subscription.
 *
 * @param int $subscription_id Subscription ID.
 * @return int|string Number of remaining payments or 'unlimited'.
 */
function subscrpt_get_remaining_payments( $subscription_id ) {
	// Get the product ID from subscription
	$product_id = get_post_meta( $subscription_id, '_subscrpt_product_id', true );
	if ( ! $product_id ) {
		return 'unlimited';
	}
	
	// Get maximum payments from product
	$max_payments = get_post_meta( $product_id, '_subscrpt_max_no_payment', true );
	
	// If no limit set or unlimited
	if ( empty( $max_payments ) || $max_payments <= 0 ) {
		return 'unlimited';
	}
	
	// Count payments made
	$payments_made = subscrpt_count_payments_made( $subscription_id );
	
	// Calculate remaining
	$remaining = $max_payments - $payments_made;
	
	return max( 0, $remaining );
}

if ( ! function_exists( 'wps_subscription_order_relation_type_cast' ) ) {
	/**
	 * Return Label against key.
	 *
	 * @param string $key Key to return cast Value.
	 *
	 * @return string
	 */
	function order_relation_type_cast( string $key ) {
		// add Deprecated notice
		_deprecated_function( 'order_relation_type_cast', '1.5.3', 'wps_subscription_order_relation_type_cast' );
		return wps_subscription_order_relation_type_cast( $key );
	}
	function wps_subscription_order_relation_type_cast( string $key ) {
		$relational_type_keys = apply_filters(
			'subscrpt_order_relational_types',
			array(
				'new'   => __( 'New Subscription Order', 'wp_subscription' ),
				'renew' => __( 'Renewal Order', 'wp_subscription' ),
			)
		);

		return isset( $relational_type_keys[ $key ] ) ? $relational_type_keys[ $key ] : '-';
	}
}

if ( ! function_exists( 'wps_subscription_is_wc_order_hpos_enabled' ) ) {
	/**
	 * Check if HPOS enabled.
	 */
	function is_wc_order_hpos_enabled() {
		// add Deprecated notice
		_deprecated_function( 'is_wc_order_hpos_enabled', '1.5.3', 'wps_subscription_is_wc_order_hpos_enabled' );
		return wps_subscription_is_wc_order_hpos_enabled();
	}
	function wps_subscription_is_wc_order_hpos_enabled() {
		return function_exists( 'wc_get_container' ) ?
			wc_get_container()
				->get( CustomOrdersTableController::class )
				->custom_orders_table_usage_is_enabled()
			: false;
	}
}

if ( ! function_exists( 'sdevs_wp_strtotime' ) ) {
	/**
	 * Get strtotime with WordPress timezone config.
	 *
	 * @param string   $str string.
	 * @param int|null $base_timestamp base timestamp.
	 *
	 * @return int
	 */
	function sdevs_wp_strtotime( $str, $base_timestamp = null ) {
		return strtotime( wp_date( 'Y-m-d H:i:s', strtotime( $str, $base_timestamp ) ) );
	}
}

if ( ! function_exists( 'sdevs_order_status_label' ) ) {
	/**
	 * Get order status label from slug.
	 *
	 * @param string $status Status.
	 *
	 * @return string
	 */
	function sdevs_order_status_label( $status ) {
		$order_statuses = wc_get_order_statuses();

		return ( isset( $order_statuses[ "wc-{$status}" ] ) ? $order_statuses[ "wc-{$status}" ] : $status );
	}
}

if ( ! function_exists( 'wps_subscription_get_timing_types' ) ) {
	/**
	 * Get labels.
	 *
	 * @param bool $key_value key_value.
	 *
	 * @return array
	 */
	function get_timing_types( $key_value = false ): array {
		// add Deprecated notice
		_deprecated_function( 'get_timing_types', '1.5.3', 'wps_subscription_get_timing_types' );
		return wps_subscription_get_timing_types( $key_value );
	}
	function wps_subscription_get_timing_types( $key_value = false ): array {
		return $key_value ? array(
			'days'   => 'Daily',
			'weeks'  => 'Weekly',
			'months' => 'Monthly',
			'years'  => 'Yearly',
		) : array(
			array(
				'label' => __( 'Day', 'wp_subscription' ),
				'value' => 'days',
			),
			array(
				'label' => __( 'Week', 'wp_subscription' ),
				'value' => 'weeks',
			),
			array(
				'label' => __( 'Month', 'wp_subscription' ),
				'value' => 'months',
			),
			array(
				'label' => __( 'Year', 'wp_subscription' ),
				'value' => 'years',
			),
		);
	}
}

function sdevs_get_subscription_product( $product ): Product {
	if ( is_int( $product ) ) {
		$product = wc_get_product( $product );
	}

	return ProductFactory::load( $product );
}

/**
 * Logger
 *
 * @param mixed $message      Message.
 * @param bool  $should_print Print the output.
 */
function wp_subscrpt_write_log( $message, bool $should_print = false ): void {
	$logger = wc_get_logger();

	$message = is_array( $message ) || is_object( $message ) ? wp_json_encode( $message ) : $message;
	$logger->add( 'wp_subcription', $message );

	echo esc_html( $should_print ? $message : '' );
}

/**
 * Debug Logger
 *
 * @param mixed $log logs.
 */
function wp_subscrpt_write_debug_log( $log ): void {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
		if ( is_array( $log ) || is_object( $log ) ) {
			error_log( print_r( $log, true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
		} else {
			error_log( 'wp_subcription: ' . $log ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
		}
	}
}
