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
		add_filter( 'manage_subscrpt_order_posts_columns', array( $this, 'add_custom_columns' ) );
		add_action( 'manage_subscrpt_order_posts_custom_column', array( $this, 'add_custom_columns_data' ), 10, 2 );
		add_action( 'add_meta_boxes', array( $this, 'create_meta_boxes' ) );
		add_action( 'admin_head-post.php', array( $this, 'some_styles' ) );
		add_action( 'admin_head-post-new.php', array( $this, 'some_styles' ) );
		add_action( 'admin_footer-post.php', array( $this, 'some_scripts' ) );
		add_action( 'admin_footer-post-new.php', array( $this, 'some_scripts' ) );
		add_action( 'save_post', array( $this, 'save_subscrpt_order' ) );
		add_filter( 'woocommerce_order_item_get_formatted_meta_data', array( $this, 'remove_order_meta' ), 10, 1 );
		add_filter( 'bulk_actions-edit-subscrpt_order', array( $this, 'remove_bulk_actions' ) );
		add_action( 'restrict_manage_posts', array( $this, 'add_subscription_filter_select' ) );
		add_action( 'admin_menu', array( $this, 'add_overview_submenu' ), 40 );
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
		$columns['subscrpt_start_date'] = __( 'Start Date', 'sdevs_subscrpt' );
		$columns['subscrpt_customer']   = __( 'Customer', 'sdevs_subscrpt' );
		$columns['subscrpt_next_date']  = __( 'Next Date', 'sdevs_subscrpt' );
		$columns['subscrpt_status']     = __( 'Status', 'sdevs_subscrpt' );
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
		if ( $order ) {
			if ( 'subscrpt_start_date' === $column ) {
				$start_date = get_post_meta( $post_id, '_subscrpt_start_date', true );
				echo ! empty( $start_date ) ? esc_html( gmdate( 'F d, Y', $start_date ) ) : '-';
			} elseif ( 'subscrpt_customer' === $column ) {
				?>
				<?php echo wp_kses_post( $order->get_formatted_billing_full_name() ); ?>
				<br />
				<a href="mailto:<?php echo wp_kses_post( $order->get_billing_email() ); ?>"><?php echo wp_kses_post( $order->get_billing_email() ); ?></a>
				<br />
				<?php if ( ! empty( $order->get_billing_phone() ) ) : ?>
					Phone : <a
						href="tel:<?php echo esc_js( $order->get_billing_phone() ); ?>"><?php echo esc_js( $order->get_billing_phone() ); ?></a>
				<?php endif; ?>
				<?php
			} elseif ( 'subscrpt_next_date' === $column ) {
				$next_date = get_post_meta( $post_id, '_subscrpt_next_date', true );
				echo ! empty( $next_date ) ? esc_html( gmdate( 'F d, Y', $next_date ) ) : '-';
			} elseif ( 'subscrpt_status' === $column ) {
				$status_obj = get_post_status_object( get_post_status( $post_id ) );
				?>
				<span
					class="subscrpt-<?php echo esc_html( $status_obj->name ); ?>"><?php echo esc_html( $status_obj->label ); ?></span>
				<?php
			}
		} else {
			esc_html_e( 'Order not found !!', 'sdevs_subscrpt' );
		}
	}

	/**
	 * Create meta boxes for admin subscriptions.
	 */
	public function create_meta_boxes() {
		remove_meta_box( 'submitdiv', 'subscrpt_order', 'side' );
		add_meta_box(
			'subscrpt_order_save_post',
			__( 'Subscription Action', 'sdevs_subscrpt' ),
			array( $this, 'subscrpt_order_save_post' ),
			'subscrpt_order',
			'side',
			'default'
		);

		add_meta_box(
			'subscrpt_customer_info',
			__( 'Customer Info', 'sdevs_subscrpt' ),
			array( $this, 'customer_info' ),
			'subscrpt_order',
			'side',
			'default'
		);

		add_meta_box(
			'subscrpt_order_info',
			__( 'Subscription Info', 'sdevs_subscrpt' ),
			array( $this, 'subscrpt_order_info' ),
			'subscrpt_order',
			'normal',
			'default'
		);

		add_meta_box(
			'subscrpt_order_history',
			__( 'Subscription History', 'sdevs_subscrpt' ),
			array( $this, 'order_histories' ),
			'subscrpt_order',
			'normal',
			'default'
		);

		add_meta_box(
			'subscrpt_order_activities',
			__( 'Subscription Activities', 'sdevs_subscrpt' ),
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
				?>
				<div class="wp-subscription-admin-box wp-subscription-upgrade-pro-banner" style="margin-bottom:18px;display:flex;align-items:center;gap:18px;justify-content:space-between;background:linear-gradient(90deg,#38bdf8 0%,#6366f1 100%);border-radius:10px;padding:22px 28px;box-shadow:0 2px 12px rgba(56,189,248,0.08);color:#fff;">
					<div style="flex:1;">
						<div style="display:flex;align-items:center;gap:14px;">
							<span style="font-size:2.2em;line-height:1;">🚀</span>
							<span style="font-family:Georgia,serif;font-size:1.25em;font-weight:bold;">Upgrade to WP Subscription Pro</span>
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
				'label' => __( 'Activate Subscription', 'sdevs_subscrpt' ),
				'value' => 'active',
			),
			'pending'      => array(
				'label' => __( 'Pending Subscription', 'sdevs_subscrpt' ),
				'value' => 'pending',
			),
			'expire'       => array(
				'label' => __( 'Expire Subscription', 'sdevs_subscrpt' ),
				'value' => 'expired',
			),
			'pe_cancelled' => array(
				'label' => __( 'Pending Cancel Subscription', 'sdevs_subscrpt' ),
				'value' => 'pe_cancelled',
			),
			'cancelled'    => array(
				'label' => __( 'Cancel Subscription', 'sdevs_subscrpt' ),
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
				'label' => __( 'Product', 'sdevs_subscrpt' ),
				'value' => '<a href="' . esc_html( $product_link ) . '" target="_blank">' . esc_html( $product_name ) . '</a>',
			),
			'cost'             => array(
				'label' => __( 'Cost', 'sdevs_subscrpt' ),
				'value' => Helper::format_price_with_order_item( get_post_meta( get_the_ID(), '_subscrpt_price', true ), $order_item->get_id() ),
			),
			'quantity'         => array(
				'label' => __( 'Qty', 'sdevs_subscrpt' ),
				'value' => "x{$order_item->get_quantity()}",
			),
			'start_date'       => array(
				'label' => __( 'Started date', 'sdevs_subscrpt' ),
				'value' => ! empty( $start_date ) ? gmdate( 'F d, Y', $trial && $trial_start_date ? $trial_start_date : $start_date ) : '-',
			),
			'next_date'        => array(
				'label' => __( 'Payment due date', 'sdevs_subscrpt' ),
				'value' => ! empty( $next_date ) ? gmdate( 'F d, Y', $trial && $trial_end_date && 'on' === $trial_mode ? $trial_end_date : ( $next_date ?? '-' ) ) : '-',
			),
			'status'           => array(
				'label' => __( 'Status', 'sdevs_subscrpt' ),
				'value' => '<span class="subscrpt-' . get_post_status() . '">' . get_post_status_object( get_post_status() )->label . '</span>',
			),
			'payment_method'   => array(
				'label' => __( 'Payment Method', 'sdevs_subscrpt' ),
				'value' => empty( $order->get_payment_method_title() ) ? '-' : $order->get_payment_method_title(),
			),
			'billing_address'  => array(
				'label' => __( 'Billing', 'sdevs_subscrpt' ),
				'value' => $order->get_formatted_billing_address() ? $order->get_formatted_billing_address() : __( 'No billing address set.', 'sdevs_subscrpt' ),
			),
			'shipping_address' => array(
				'label' => __( 'Shipping', 'sdevs_subscrpt' ),
				'value' => $order->get_formatted_shipping_address() ? $order->get_formatted_shipping_address() : __( 'No shipping address set.', 'sdevs_subscrpt' ),
			),
		);
		if ( $trial ) {
			$rows = array_slice( $rows, 0, 3, true ) + array(
				'trial'        => array(
					'label' => __( 'Trial', 'sdevs_subscrpt' ),
					'value' => $trial,
				),
				'trial_period' => array(
					'label' => __( 'Trial Period', 'sdevs_subscrpt' ),
					'value' => ( $trial_start_date && $trial_end_date ? ' [ ' . gmdate( 'F d, Y', $trial_start_date ) . ' - ' . gmdate( 'F d, Y', $trial_end_date ) . ' ] ' : __( 'Trial isn\'t activated yet! ', 'sdevs_subscrpt' ) ),
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
						'label' => __( 'Stripe Auto Renewal', 'sdevs_subscrpt' ),
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

	public function add_subscription_filter_select() {
		// Implementation of add_subscription_filter_select method
	}

	public function add_overview_submenu() {
		// Remove and re-add submenu to ensure Overview is first
		remove_submenu_page('edit.php?post_type=subscrpt_order', 'edit.php?post_type=subscrpt_order');
		add_submenu_page(
			'edit.php?post_type=subscrpt_order',
			__( 'Overview', 'wp_subscription' ),
			__( 'Overview', 'wp_subscription' ),
			'manage_options',
			'subscription_overview',
			array( $this, 'render_overview_page' ),
			0
		);
		add_submenu_page(
			'edit.php?post_type=subscrpt_order',
			__( 'All Subscriptions', 'wp_subscription' ),
			__( 'All Subscriptions', 'wp_subscription' ),
			'manage_options',
			'edit.php?post_type=subscrpt_order',
			'',
			1
		);
		if ( ! class_exists('Sdevs_Wc_Subscription_Pro') ) {
			add_submenu_page(
				'edit.php?post_type=subscrpt_order',
				__( 'Go Pro', 'wp_subscription' ),
				__( 'Go Pro', 'wp_subscription' ),
				'manage_options',
				'wp_subscription_go_pro',
				array( $this, 'render_go_pro_page' ),
				99
			);
		}
	}

	public function render_overview_page() {
		?>
		<div class="wrap wpsubscription-overview" style="max-width:1100px;margin:40px auto 0 auto;">
			<div class="wpsubscription-overview-card" style="background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,0.06);padding:40px 32px 32px 32px;">
				<div class="wpsubscription-overview-top" style="display:grid;grid-template-columns:1fr 1fr;gap:40px;align-items:start;margin-bottom:40px;">
					<div class="wpsubscription-overview-info" style="display:flex;flex-direction:column;gap:18px;">
						<h1 style="margin-bottom:0.2em;"><?php esc_html_e( 'WP Subscription Overview', 'wp_subscription' ); ?></h1>
						<p class="product-desc" style="font-size:1.15em;line-height:1.6;max-width:500px;">
							<?php esc_html_e( 'WP Subscription is the most seamless and reliable WooCommerce subscription solution for store owners looking to grow recurring revenue. Easily manage recurring payments, automate renewals, and delight your customers with flexible plans.', 'wp_subscription' ); ?>
						</p>
						<div class="wpsubscription-links" style="display:flex;gap:12px;flex-wrap:wrap;">
							<a href="https://converslabs.thrivedeskdocs.com/en" target="_blank" class="button button-secondary"><?php esc_html_e( 'Documentation', 'wp_subscription' ); ?></a>
							<a href="https://wpsubscription.co/" target="_blank" class="button button-secondary"><?php esc_html_e( 'Website', 'wp_subscription' ); ?></a>
						</div>
					</div>
					<div class="promo-video" style="text-align:center;">
						<iframe width="420" height="236" src="https://www.youtube.com/embed/2e6o5p0M7L4" title="WP Subscription Promo" frameborder="0" allowfullscreen style="max-width:100%;border-radius:8px;"></iframe>
					</div>
				</div>

				<div class="wpsubscription-what-section" style="margin-bottom:40px;">
					<h2><?php esc_html_e( 'What does Subscriptions for WooCommerce do?', 'wp_subscription' ); ?></h2>
					<p style="font-size:1.08em;max-width:900px;line-height:1.7;">
						<?php esc_html_e( 'Subscriptions for WooCommerce enables you to create and manage recurring payment products and services with ease. Automate renewals, offer flexible billing schedules, and provide your customers with a seamless subscription experience. Whether you sell digital content, physical goods, or memberships, WP Subscription gives you the tools to grow your recurring revenue.', 'wp_subscription' ); ?>
					</p>
				</div>

				<h2 style="margin-top:2em;"><?php esc_html_e( 'Highlights', 'wp_subscription' ); ?></h2>
				<div class="wpsubscription-features-grid">
					<div class="feature-box"><span class="dashicons dashicons-admin-generic"></span><h3>Easy Setup</h3><p>Get started in minutes with our intuitive onboarding wizard.</p></div>
					<div class="feature-box"><span class="dashicons dashicons-money"></span><h3>Multiple Gateways</h3><p>Support for Stripe, PayPal, and Paddle out of the box.</p></div>
					<div class="feature-box"><span class="dashicons dashicons-schedule"></span><h3>Flexible Plans</h3><p>Create and manage various subscription types and delivery schedules.</p></div>
					<div class="feature-box"><span class="dashicons dashicons-chart-line"></span><h3>Comprehensive Dashboard</h3><p>Monitor and manage all subscriptions in one place.</p></div>
				</div>
			</div>
		</div>
		<style>
		.wpsubscription-overview .promo-video { text-align:center; }
		.wpsubscription-features-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
			gap: 24px;
			margin-top: 32px;
		}
		.feature-box {
			background: #fff;
			border-radius: 10px;
			box-shadow: 0 2px 8px rgba(0,0,0,0.06);
			padding: 24px 18px 18px 18px;
			text-align: center;
			transition: transform 0.2s, box-shadow 0.2s;
			will-change: transform;
		}
		.feature-box:hover {
			transform: translateY(-6px) scale(1.03);
			box-shadow: 0 6px 24px rgba(0,0,0,0.10);
		}
		.feature-box .dashicons {
			font-size: 2.2em;
			color: #7f54b3;
			margin-bottom: 10px;
			display: block;
		}
		.feature-box h3 {
			margin: 12px 0 8px 0;
			font-size: 1.15em;
		}
		.feature-box p {
			color: #555;
			font-size: 1em;
			margin: 0;
		}
		</style>
		<?php
	}

	public function render_go_pro_page() {
		if ( class_exists('Sdevs_Wc_Subscription_Pro') ) {
			echo '<div class="notice notice-info" style="margin:40px auto;max-width:700px;text-align:center;font-size:1.2em;">Pro is already active.</div>';
			return;
		}
		?>
		<div class="wrap wpsubscription-go-pro" style="max-width:900px;margin:40px auto 0 auto;">
			<div class="wpsubscription-go-pro-card" style="background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,0.06);padding:40px 32px 32px 32px;">
				<h1 style="margin-bottom:0.5em;"><?php esc_html_e( 'Upgrade to WP Subscription Pro', 'wp_subscription' ); ?></h1>
				<p style="font-size:1.12em;max-width:600px;line-height:1.6;">
					<?php esc_html_e( 'Unlock the full power of subscriptions for WooCommerce. Get advanced features, priority support, and more ways to grow your recurring revenue.', 'wp_subscription' ); ?>
				</p>
				<table class="wpsubscription-compare-table" style="width:100%;margin:32px 0 40px 0;border-collapse:separate;border-spacing:0;box-shadow:0 1px 4px rgba(0,0,0,0.04);background:#fafbfc;border-radius:8px;overflow:hidden;">
					<thead>
						<tr style="background:#f8f9fa;">
							<th style="padding:18px 12px 18px 24px;font-size:1.08em;text-align:left;border:none;"></th>
							<th style="padding:18px 12px;font-size:1.08em;text-align:center;border:none;">Free</th>
							<th style="padding:18px 12px;font-size:1.08em;text-align:center;border:none;color:#7f54b3;">Pro</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td style="padding:16px 12px 16px 24px;">Simple subscription products</td>
							<td style="text-align:center;">✔️</td>
							<td style="text-align:center;">✔️</td>
						</tr>
						<tr style="background:#f6f7f7;">
							<td style="padding:16px 12px 16px 24px;">Automated recurring billing</td>
							<td style="text-align:center;">✔️</td>
							<td style="text-align:center;">✔️</td>
						</tr>
						<tr>
							<td style="padding:16px 12px 16px 24px;">Multiple payment gateways</td>
							<td style="text-align:center;">✔️</td>
							<td style="text-align:center;">✔️</td>
						</tr>
						<tr style="background:#f6f7f7;">
							<td style="padding:16px 12px 16px 24px;">Customer self-service portal</td>
							<td style="text-align:center;">✔️</td>
							<td style="text-align:center;">✔️</td>
						</tr>
						<tr>
							<td style="padding:16px 12px 16px 24px;">Priority support</td>
							<td style="text-align:center;">—</td>
							<td style="text-align:center;">✔️</td>
						</tr>
						<tr style="background:#f6f7f7;">
							<td style="padding:16px 12px 16px 24px;">Advanced reporting & analytics</td>
							<td style="text-align:center;">—</td>
							<td style="text-align:center;">✔️</td>
						</tr>
						<tr>
							<td style="padding:16px 12px 16px 24px;">Variable product support</td>
							<td style="text-align:center;">—</td>
							<td style="text-align:center;font-weight:600;color:#43a047;">✔️</td>
						</tr>
					</tbody>
				</table>
				<div style="text-align:center;margin-top:24px;">
					<a href="https://wpsubscription.co/" target="_blank" class="button button-primary button-hero" style="font-size:1.2em;padding:16px 40px 16px 40px;background:#7f54b3;border:none;box-shadow:0 2px 8px rgba(127,84,179,0.10);">
						<?php esc_html_e( 'Upgrade to Pro', 'wp_subscription' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php
	}
}
