<?php

namespace SpringDevs\Subscription\Admin;

use SpringDevs\Subscription\Illuminate\Action;
use SpringDevs\Subscription\Illuminate\Helper;

// HPOS: This file is compatible with WooCommerce High-Performance Order Storage (HPOS).
// All WooCommerce order data is accessed via WooCommerce CRUD methods (wc_get_order, etc.).
// All direct post meta access is for subscription data only, not WooCommerce order data.
// If you add new order data access, use WooCommerce CRUD for HPOS compatibility.

/**
 * Subscriptions class
 *
 * @package SpringDevs\Subscription\Admin
 */
class Subscriptions {


	/**
	 * Initialize the class.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'post_row_actions', array( $this, 'post_row_actions' ) );
		// add_filter( 'manage_subscrpt_order_posts_columns', array( $this, 'add_custom_columns' ) );
		// add_action( 'manage_subscrpt_order_posts_custom_column', array( $this, 'add_custom_columns_data' ), 10, 2 );
		add_action( 'add_meta_boxes', array( $this, 'create_meta_boxes' ) );
		add_action( 'admin_head-post.php', array( $this, 'some_styles' ) );
		add_action( 'admin_head-post-new.php', array( $this, 'some_styles' ) );
		add_action( 'admin_footer-post.php', array( $this, 'some_scripts' ) );
		add_action( 'admin_footer-post-new.php', array( $this, 'some_scripts' ) );
		add_action( 'save_post', array( $this, 'save_subscrpt_order' ) );
		add_filter( 'woocommerce_order_item_get_formatted_meta_data', array( $this, 'remove_order_meta' ), 10, 1 );
		add_filter( 'bulk_actions-edit-subscrpt_order', array( $this, 'remove_bulk_actions' ) );
		add_action( 'restrict_manage_posts', array( $this, 'add_subscription_filter_select' ) );
	}

	/**
	 * Remove 'Edit` and 'Trash' from bulk actions.
	 *
	 * @param array $actions Action list.
	 *
	 * @return array
	 */
	public function remove_bulk_actions( $actions ) {
		unset( $actions['edit'] );
		unset( $actions['trash'] );

		return $actions;
	}

	/**
	 * Hide order meta key from custom fields.
	 *
	 * @param array $formatted_meta Data with key-value.
	 *
	 * @return array
	 */
	public function remove_order_meta( $formatted_meta ): array {
		$temp_metas = array();
		foreach ( $formatted_meta as $key => $meta ) {
			if ( isset( $meta->key ) && '_renew_subscrpt' !== $meta->key ) {
				$temp_metas[ $key ] = $meta;
			}
		}

		return $temp_metas;
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'subscrpt_admin_css' );
		wp_enqueue_style( 'subscrpt_status_css' );
	}

	/**
	 * Remove default post actions.
	 *
	 * @param array $actions Actions.
	 *
	 * @return array
	 */
	public function post_row_actions( $actions ) {
		global $current_screen;
		if ( 'subscrpt_order' !== $current_screen->post_type ) {
			return $actions;
		}
		unset( $actions['inline hide-if-no-js'] );
		unset( $actions['view'] );
		unset( $actions['trash'] );
		unset( $actions['edit'] );

		return $actions;
	}

	/**
	 * Register custom columns.
	 *
	 * @param array $columns Columns.
	 *
	 * @return array
	 */
	public function add_custom_columns( $columns ) {
		$columns['subscrpt_start_date'] = __( 'Start Date', 'wp_subscription' );
		$columns['subscrpt_customer']   = __( 'Customer', 'wp_subscription' );
		$columns['subscrpt_next_date']  = __( 'Next Date', 'wp_subscription' );
		$columns['subscrpt_status']     = __( 'Status', 'wp_subscription' );
		unset( $columns['date'] );
		unset( $columns['cb'] );

		return $columns;
	}

	/**
	 * Display column data.
	 *
	 * @param string $column Column.
	 * @param int    $post_id Post Id.
	 *
	 * @return void
	 */
	public function add_custom_columns_data( $column, $post_id ) {
		// HPOS: Safe. Only retrieves WooCommerce order via CRUD, and subscription meta via post meta.
		$order_id = get_post_meta( $post_id, '_subscrpt_order_id', true ); // HPOS: Only subscription meta, not order meta.
		$order = wc_get_order( $order_id ); // HPOS: Safe, uses WooCommerce CRUD.
		
		wc_get_template(
			'admin/column-data.php',
			array(
				'column'  => $column,
				'post_id' => $post_id,
				'order'   => $order,
			),
			'subscription',
			WP_SUBSCRIPTION_TEMPLATES
		);
	}

	/**
	 * Create meta boxes for admin subscriptions.
	 */
	public function create_meta_boxes() {
		remove_meta_box( 'submitdiv', 'subscrpt_order', 'side' );
		add_meta_box(
			'subscrpt_order_save_post',
			__( 'Subscription Action', 'wp_subscription' ),
			array( $this, 'subscrpt_order_save_post' ),
			'subscrpt_order',
			'side',
			'default'
		);

		add_meta_box(
			'subscrpt_customer_info',
			__( 'Customer Info', 'wp_subscription' ),
			array( $this, 'customer_info' ),
			'subscrpt_order',
			'side',
			'default'
		);

		add_meta_box(
			'subscrpt_order_info',
			__( 'Subscription Info', 'wp_subscription' ),
			array( $this, 'subscrpt_order_info' ),
			'subscrpt_order',
			'normal',
			'default'
		);

		add_meta_box(
			'subscrpt_order_history',
			__( 'Subscription History', 'wp_subscription' ),
			array( $this, 'order_histories' ),
			'subscrpt_order',
			'normal',
			'default'
		);

		add_meta_box(
			'subscrpt_order_activities',
			__( 'Subscription Activities', 'wp_subscription' ),
			array( $this, 'order_activities' ),
			'subscrpt_order',
			'normal',
			'default'
		);
	}

	/**
	 * Display Order Histories.
	 *
	 * @param \WP_Post $post Post Object.
	 *
	 * @return void
	 */
	public function order_histories( $post ) {
		$subscription_id = $post->ID;
		global $wpdb;
		$table_name = $wpdb->prefix . 'subscrpt_order_relation';
        // @phpcs:ignore
        $order_histories = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %i WHERE subscription_id=%d', array(
			$table_name,
			$subscription_id,
			)
			)
		);

		include 'views/order-history.php';
	}

	/**
	 * Display Order Activities
	 *
	 * @param \WP_Post $post Post Object.
	 *
	 * @return void
	 */
	public function order_activities( $post ) {
		if ( function_exists( 'subscrpt_pro_activated' ) ) :
			if ( subscrpt_pro_activated() ) :
				do_action( 'subscrpt_order_activities', $post->ID );
			else :
				wc_get_template(
					'admin/go-pro.php',
					[],
					'subscription',
					WP_SUBSCRIPTION_TEMPLATES
				);
				?>
				<div class="wp-subscription-admin-box wp-subscription-upgrade-pro-banner" style="margin-bottom:18px;display:flex;align-items:center;gap:18px;justify-content:space-between;background:linear-gradient(90deg,#38bdf8 0%,#6366f1 100%);border-radius:10px;padding:22px 28px;box-shadow:0 2px 12px rgba(56,189,248,0.08);color:#fff;">
					<div style="flex:1;">
						<div style="display:flex;align-items:center;gap:14px;">
							<span style="font-size:2.2em;line-height:1;">🚀</span>
							<span style="font-family:Georgia,serif;font-size:1.25em;font-weight:bold;">Upgrade to WPSubscription Pro</span>
						</div>
						<div style="margin-top:8px;font-size:1.08em;max-width:500px;opacity:0.95;">
							Unlock advanced features, automation, and priority support. Take your subscription business to the next level!
						</div>
					</div>
					<div style="flex-shrink:0;">
						<a href="https://wpsubscription.co/" target="_blank" class="button button-primary" style="background:#fff;color:#6366f1;font-weight:600;font-size:1.08em;padding:12px 28px;border:none;box-shadow:0 2px 8px rgba(99,102,241,0.10);border-radius:6px;">Upgrade to Pro</a>
					</div>
				</div>
				<?php
			endif;
		endif;
	}

	/**
	 * Save subscription HTML.
	 */
	public function subscrpt_order_save_post() {
		$actions_data = array(
			'active'       => array(
				'label' => __( 'Activate Subscription', 'wp_subscription' ),
				'value' => 'active',
			),
			'pending'      => array(
				'label' => __( 'Pending Subscription', 'wp_subscription' ),
				'value' => 'pending',
			),
			'expire'       => array(
				'label' => __( 'Expire Subscription', 'wp_subscription' ),
				'value' => 'expired',
			),
			'pe_cancelled' => array(
				'label' => __( 'Pending Cancel Subscription', 'wp_subscription' ),
				'value' => 'pe_cancelled',
			),
			'cancelled'    => array(
				'label' => __( 'Cancel Subscription', 'wp_subscription' ),
				'value' => 'cancelled',
			),
		);

		$map_actions = array(
			'pending'      => array( 'active', 'cancelled' ),
			'active'       => array( 'pe_cancelled', 'cancelled' ),
			'pe_cancelled' => array( 'active', 'cancelled' ),
			'cancelled'    => array( 'active' ),
			'expired'      => array(),
		);

		$status  = get_post_status( get_the_ID() );
		$actions = $map_actions[ $status ];

		include 'views/subscription-save-meta.php';
	}

	/**
	 * Display Customer Info
	 *
	 * @param \WP_Post $post Post Object.
	 *
	 * @return void
	 */
	public function customer_info( $post ) {
		$order_id = get_post_meta( $post->ID, '_subscrpt_order_id', true );
		$order    = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}
		include 'views/subscription-customer.php';
	}

	/**
	 * Display subscription info.
	 *
	 * @return void
	 */
	public function subscrpt_order_info() {
		$order_id         = get_post_meta( get_the_ID(), '_subscrpt_order_id', true );
		$order_item_id    = get_post_meta( get_the_ID(), '_subscrpt_order_item_id', true );
		$trial            = get_post_meta( get_the_ID(), '_subscrpt_trial', true );
		$start_date       = get_post_meta( get_the_ID(), '_subscrpt_start_date', true );
		$next_date        = get_post_meta( get_the_ID(), '_subscrpt_next_date', true );
		$trial_start_date = get_post_meta( get_the_ID(), '_subscrpt_trial_started', true );
		$trial_end_date   = get_post_meta( get_the_ID(), '_subscrpt_trial_ended', true );
		$trial_mode       = get_post_meta( get_the_ID(), '_subscrpt_trial_mode', true );
		$order            = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}
		$order_item = $order->get_item( $order_item_id );

		$product_name = $order_item->get_name();
		$product_link = get_the_permalink( $order_item->get_product_id() );
		$rows         = array(
			'product'          => array(
				'label' => __( 'Product', 'wp_subscription' ),
				'value' => '<a href="' . esc_html( $product_link ) . '" target="_blank">' . esc_html( $product_name ) . '</a>',
			),
			'cost'             => array(
				'label' => __( 'Cost', 'wp_subscription' ),
				'value' => Helper::format_price_with_order_item( get_post_meta( get_the_ID(), '_subscrpt_price', true ), $order_item->get_id() ),
			),
			'quantity'         => array(
				'label' => __( 'Qty', 'wp_subscription' ),
				'value' => "x{$order_item->get_quantity()}",
			),
			'start_date'       => array(
				'label' => __( 'Started date', 'wp_subscription' ),
				'value' => ! empty( $start_date ) ? gmdate( 'F d, Y', $trial && $trial_start_date ? $trial_start_date : $start_date ) : '-',
			),
			'next_date'        => array(
				'label' => __( 'Payment due date', 'wp_subscription' ),
				'value' => ! empty( $next_date ) ? gmdate( 'F d, Y', $trial && $trial_end_date && 'on' === $trial_mode ? $trial_end_date : ( $next_date ?? '-' ) ) : '-',
			),
			'status'           => array(
				'label' => __( 'Status', 'wp_subscription' ),
				'value' => '<span class="subscrpt-' . get_post_status() . '">' . get_post_status_object( get_post_status() )->label . '</span>',
			),
			'payment_method'   => array(
				'label' => __( 'Payment Method', 'wp_subscription' ),
				'value' => empty( $order->get_payment_method_title() ) ? '-' : $order->get_payment_method_title(),
			),
			'billing_address'  => array(
				'label' => __( 'Billing', 'wp_subscription' ),
				'value' => $order->get_formatted_billing_address() ? $order->get_formatted_billing_address() : __( 'No billing address set.', 'wp_subscription' ),
			),
			'shipping_address' => array(
				'label' => __( 'Shipping', 'wp_subscription' ),
				'value' => $order->get_formatted_shipping_address() ? $order->get_formatted_shipping_address() : __( 'No shipping address set.', 'wp_subscription' ),
			),
		);
		if ( $trial ) {
			$rows = array_slice( $rows, 0, 3, true ) + array(
				'trial'        => array(
					'label' => __( 'Trial', 'wp_subscription' ),
					'value' => $trial,
				),
				'trial_period' => array(
					'label' => __( 'Trial Period', 'wp_subscription' ),
					'value' => ( $trial_start_date && $trial_end_date ? ' [ ' . gmdate( 'F d, Y', $trial_start_date ) . ' - ' . gmdate( 'F d, Y', $trial_end_date ) . ' ] ' : __( 'Trial isn\'t activated yet! ', 'wp_subscription' ) ),
				),
			) + array_slice( $rows, 3, count( $rows ) - 1, true );
		}

		if ( class_exists( 'WC_Stripe' ) && 'stripe' === $order->get_payment_method() ) {
			$is_auto_renew = get_post_meta( get_the_ID(), '_subscrpt_auto_renew', true );
			$new_rows      = array();
			foreach ( $rows as $key => $value ) {
				$new_rows[ $key ] = $value;
				if ( 'payment_method' === $key ) {
					$new_rows['stripe_auto_renewal'] = array(
						'label' => __( 'Stripe Auto Renewal', 'wp_subscription' ),
						'value' => '0' !== $is_auto_renew ? 'On' : 'Off',
					);
				}
			}

			$rows = $new_rows;
		}

		$rows = apply_filters( 'subscrpt_admin_info_rows', $rows, get_the_ID(), $order );

		include 'views/subscription-info.php';
	}

	/**
	 * Include some styles.
	 *
	 * @return void
	 */
	public function some_styles() {
		global $post;
		if ( 'subscrpt_order' === $post->post_type ) :
			?>
			<style>
				.submitbox {
					display: flex;
					justify-content: space-around;
				}

				.subscrpt_sub_box {
					display: grid;
					line-height: 2;
				}
			</style>
			<?php
		endif;
	}

	/**
	 * Disable changes popup.
	 *
	 * @return void
	 */
	public function some_scripts() {
		global $post;
		if ( 'subscrpt_order' === $post->post_type ) :
			?>
			<script>
				jQuery(document).ready(function() {
					jQuery(window).off('beforeunload', null);
				});
			</script>
			<?php
		endif;
	}

	public function save_subscrpt_order( $post_id ) {
		if ( wp_is_post_revision( $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! isset( $_POST['subscrpt_order_action'] ) ) {
			return;
		}
		remove_all_actions( 'save_post' );

		$action     = sanitize_text_field( wp_unslash( $_POST['subscrpt_order_action'] ) );
		$old_status = get_post_status( $post_id );

		wp_update_post(
			array(
				'ID'          => $post_id,
				'post_status' => $action,
			)
		);

		if ( $old_status !== $action ) {
			$old_status_object = get_post_status_object( $old_status );
			$new_status_object = get_post_status_object( $action );
			WC()->mailer();
			do_action( 'subscrpt_status_changed_admin_email_notification', $post_id, $old_status_object->label, $new_status_object->label );
		}

		$order_id = get_post_meta( $post_id, '_subscrpt_order_id', true );
		if ( 'active' === $action ) {
			$order = wc_get_order( $order_id );
			$order->update_status( 'completed' );
			Action::status( $action, $post_id );
		} else {
			Action::status( $action, $post_id );
		}
	}

}
