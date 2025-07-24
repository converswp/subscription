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
	// If pro version is available, use its method
	if ( class_exists( '\\SpringDevs\\SubscriptionPro\\Illuminate\\LimitChecker' ) ) {
		$pro_renewals = \SpringDevs\SubscriptionPro\Illuminate\LimitChecker::get_subscrpt_renewals( $subscription_id );
		$remaining = \SpringDevs\SubscriptionPro\Illuminate\LimitChecker::get_remaining_renewals( $subscription_id );
		
		if ( is_numeric( $pro_renewals ) && is_numeric( $remaining ) ) {
			$payments_made = $pro_renewals - $remaining;
			error_log( "WPS DEBUG: Using pro version - Subscription #{$subscription_id} payments: {$payments_made} (total: {$pro_renewals}, remaining: {$remaining})" );
			return max( 0, $payments_made );
		}
	}
	
	global $wpdb;
	
	$table_name = $wpdb->prefix . 'subscrpt_order_relation';
	
	// Get all relation records for debugging
	$relations = $wpdb->get_results( $wpdb->prepare(
		"SELECT sr.*, p.post_status as order_status 
		FROM {$table_name} sr 
		INNER JOIN {$wpdb->posts} p ON sr.order_id = p.ID 
		WHERE sr.subscription_id = %d",
		$subscription_id
	) );
	
	// Debug: log what we find
	error_log( "WPS DEBUG: Subscription #{$subscription_id} relations: " . json_encode( $relations ) );
	
	// Count successful payment records
	$count = 0;
	foreach ( $relations as $relation ) {
		// Count 'new' and 'renew' type records where order is successful
		if ( in_array( $relation->type, array( 'new', 'renew' ) ) ) {
			// Check if order status indicates successful payment
			if ( in_array( $relation->order_status, array( 'wc-completed', 'wc-processing' ) ) ) {
				$count++;
			}
		}
	}
	
	error_log( "WPS DEBUG: Subscription #{$subscription_id} payment count: {$count}" );
	
	return $count;
}
//return (int) get_post_meta( $subscription_id, '_subscrpt_renewal_count', true );

/**
 * Check if subscription has reached its renewal limit.
 *
 * @param int $subscription_id Subscription ID.
 * @return bool True if limit reached, false otherwise.
 */
function subscrpt_is_renewal_limit_reached( $subscription_id ) {
	// Get the product ID from subscription
	$product_id = get_post_meta( $subscription_id, '_subscrpt_product_id', true );
	if ( ! $product_id ) {
		return false;
	}
	
	// Get renewal limit from product
	$renewal_limit = get_post_meta( $product_id, '_subscrpt_renewal_limit', true );
	
	// If no limit set or unlimited, renewal is allowed
	if ( empty( $renewal_limit ) || $renewal_limit <= 0 ) {
		return false;
	}
	
	// Count payments made
	$payments_made = subscrpt_count_payments_made( $subscription_id );
	
	// Check if limit reached
	return $payments_made >= $renewal_limit;
}

/**
 * Get remaining renewals for a subscription.
 *
 * @param int $subscription_id Subscription ID.
 * @return int|string Number of remaining renewals or 'unlimited'.
 */
function subscrpt_get_remaining_renewals( $subscription_id ) {
	// Get the product ID from subscription
	$product_id = get_post_meta( $subscription_id, '_subscrpt_product_id', true );
	if ( ! $product_id ) {
		return 'unlimited';
	}
	
	// Get renewal limit from product
	$renewal_limit = get_post_meta( $product_id, '_subscrpt_renewal_limit', true );
	
	// If no limit set or unlimited
	if ( empty( $renewal_limit ) || $renewal_limit <= 0 ) {
		return 'unlimited';
	}
	
	// Count payments made
	$payments_made = subscrpt_count_payments_made( $subscription_id );
	
	// Calculate remaining
	$remaining = $renewal_limit - $payments_made;
	
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
