<?php

namespace SpringDevs\Subscription\Illuminate;

/**
 * Action [ helper class ]
 *
 * @package SpringDevs\Subscription\Illuminate
 */
class Action {

	/**
	 * Did when status changes.
	 *
	 * @param string $status Status.
	 * @param int    $subscription_id Subscription ID.
	 * @param bool   $write_comment Write comment?.
	 */
	public static function status( string $status, int $subscription_id, bool $write_comment = true ) {
		$old_status = get_post_status($subscription_id);
		
		wp_update_post(
			array(
				'ID'          => $subscription_id,
				'post_status' => $status,
			)
		);

		if ( $write_comment ) {
			self::write_comment( $status, $subscription_id );
		}

		self::user( $subscription_id );

		// Trigger status change action
		do_action('subscrpt_subscription_status_changed', $subscription_id, $old_status, $status);

		// Trigger resumption event if subscription is being activated from cancelled or pending cancellation
		if ($status === 'active' && in_array($old_status, array('cancelled', 'pe_cancelled'))) {
			do_action('subscrpt_subscription_resumed', $subscription_id, $old_status);
		}
	}

	/**
	 * Write Comment based on status.
	 *
	 * @param string $status Status.
	 * @param Int    $subscription_id Subscription ID.
	 */
	public static function write_comment( string $status, int $subscription_id ) {
		switch ( $status ) {
			case 'expired':
				self::expired( $subscription_id );
				break;
			case 'active':
				self::active( $subscription_id );
				break;
			case 'pending':
				self::pending( $subscription_id );
				break;
			case 'cancelled':
				self::cancelled( $subscription_id );
				break;
			case 'pe_cancelled':
				self::pe_cancelled( $subscription_id );
				break;
		}
	}

	/**
	 * Write Comment About expired Subscription.
	 *
	 * @param int $subscription_id Subscription ID.
	 */
	private static function expired( int $subscription_id ) {
		$comment_id = wp_insert_comment(
			array(
				'comment_author'  => __( 'Subscription for WooCommerce', 'wp_subscription' ),
				'comment_content' => __( 'Subscription is Expired', 'wp_subscription' ),
				'comment_post_ID' => $subscription_id,
				'comment_type'    => 'order_note',
			)
		);
		update_comment_meta( $comment_id, '_subscrpt_activity', __( 'Subscription Expired', 'wp_subscription' ) );

		do_action( 'subscrpt_subscription_expired', $subscription_id );
	}

	/**
	 * Write Comment About Active Subscription.
	 *
	 * @param int $subscription_id Subscription ID.
	 */
	private static function active( int $subscription_id ) {
		$comment_id = wp_insert_comment(
			array(
				'comment_author'  => __( 'Subscription for WooCommerce', 'wp_subscription' ),
				'comment_content' => __( 'Subscription activated. Next payment due date set.', 'wp_subscription' ),
				'comment_post_ID' => $subscription_id,
				'comment_type'    => 'order_note',
			)
		);
		update_comment_meta( $comment_id, '_subscrpt_activity', __( 'Subscription Activated', 'wp_subscription' ) );
		do_action( 'subscrpt_subscription_activated', $subscription_id );
	}

	/**
	 * Write Comment About Subscription Pending.
	 *
	 * @param int $subscription_id Subscription ID.
	 */
	private static function pending( int $subscription_id ) {
		$comment_id = wp_insert_comment(
			array(
				'comment_author'  => __( 'Subscription for WooCommerce', 'wp_subscription' ),
				'comment_content' => __( 'Subscription is pending.', 'wp_subscription' ),
				'comment_post_ID' => $subscription_id,
				'comment_type'    => 'order_note',
			)
		);
		update_comment_meta( $comment_id, '_subscrpt_activity', __( 'Subscription Pending', 'wp_subscription' ) );
		do_action( 'subscrpt_subscription_pending', $subscription_id );
	}

	/**
	 * Write Comment About Subscription Cancelled.
	 *
	 * @param int $subscription_id Subscription ID.
	 */
	private static function cancelled( int $subscription_id ) {
		$comment_id = wp_insert_comment(
			array(
				'comment_author'  => __( 'Subscription for WooCommerce', 'wp_subscription' ),
				'comment_content' => __( 'Subscription is Cancelled.', 'wp_subscription' ),
				'comment_post_ID' => $subscription_id,
				'comment_type'    => 'order_note',
			)
		);
		update_comment_meta( $comment_id, '_subscrpt_activity', __( 'Subscription Cancelled', 'wp_subscription' ) );

		WC()->mailer();
		do_action( 'subscrpt_subscription_cancelled_email_notification', $subscription_id );
		do_action( 'subscrpt_subscription_cancelled', $subscription_id );
	}

	/**
	 * Write Comment About Pending Cancellation.
	 *
	 * @param int $subscription_id Subscription ID.
	 */
	private static function pe_cancelled( int $subscription_id ) {
		$comment_id = wp_insert_comment(
			array(
				'comment_author'  => __( 'Subscription for WooCommerce', 'wp_subscription' ),
				'comment_content' => __( 'Subscription is Pending Cancellation.', 'wp_subscription' ),
				'comment_post_ID' => $subscription_id,
				'comment_type'    => 'order_note',
			)
		);
		update_comment_meta( $comment_id, '_subscrpt_activity', __( 'Subscription Pending Cancellation', 'wp_subscription' ) );
		do_action( 'subscrpt_subscription_pending_cancellation', $subscription_id );
	}

	/**
	 * Update user role.
	 *
	 * @param Int $subscription_id Subscription ID.
	 */
	private static function user( $subscription_id ) {
		$user = new \WP_User( get_current_user_id() );
		if ( ! empty( $user->roles ) && is_array( $user->roles ) && in_array( 'administrator', $user->roles, true ) ) {
			return;
		}

		if ( Helper::subscription_exists( $subscription_id, 'active' ) ) {
			$user->set_role( get_option( 'subscrpt_active_role', 'subscriber' ) );
		} elseif ( Helper::subscription_exists( $subscription_id, array( 'cancelled', 'expired' ) ) ) {
			$user->set_role( get_option( 'subscrpt_unactive_role', 'customer' ) );
		}
	}
}
