<?php

namespace SpringDevs\Subscription\Illuminate;

/**
 * Post class - managing `subscrpt_order` post.
 */
class Post {

	/**
	 * Initialize the class.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'create_post_type' ) );
		add_filter( 'post_updated_messages', array( $this, 'update_post_labels' ) );
	}

	/**
	 * Add post labels on `subscrpt_order`.
	 *
	 * @param array $messages Messages.
	 *
	 * @return array
	 */
	public function update_post_labels( $messages ) {
		$messages['subscrpt_order'] = array(
			__( 'Subscription updated.', 'wp_subscription' ),
			__( 'Subscription updated.', 'wp_subscription' ),
			'Custom field updated.',
			'Custom field deleted.',
			__( 'Subscription updated.', 'wp_subscription' ),
			false,
			__( 'Subscription published.', 'wp_subscription' ),
			__( 'Subscription saved.', 'wp_subscription' ),
			__( 'Subscription submitted.', 'wp_subscription' ),
			false,
			__( 'Subscription draft updated.', 'wp_subscription' ),
		);
		return $messages;
	}

	/**
	 * Register `subscrpt_order` post type and statuses.
	 *
	 * @return void
	 */
	public function create_post_type() {
		$this->register_subscription_post_type();
		$this->register_subscription_item_post_type();
		$this->register_post_status();
	}

	/**
	 * Register ``subscrpt_order`` post type
	 */
	public function register_subscription_post_type() {
		$labels = array(
			'name'              => __( 'Subscriptions', 'wp_subscription' ),
			'singular_name'     => __( 'Subscription', 'wp_subscription' ),
			'name_admin_bar'    => __( 'Subscription\'s', 'wp_subscription' ),
			'archives'          => __( 'Item Archives', 'wp_subscription' ),
			'attributes'        => __( 'Item Attributes', 'wp_subscription' ),
			'parent_item_colon' => __( 'Parent :', 'wp_subscription' ),
			'all_items'         => __( 'Subscriptions', 'wp_subscription' ),
			'add_new_item'      => __( 'Add New Subscription', 'wp_subscription' ),
			'add_new'           => __( 'Add Subscription', 'wp_subscription' ),
			'new_item'          => __( 'New Subscription', 'wp_subscription' ),
			'edit_item'         => __( 'Edit Subscription', 'wp_subscription' ),
			'update_item'       => __( 'Update Subscription', 'wp_subscription' ),
			'view_item'         => __( 'View Subscription', 'wp_subscription' ),
			'view_items'        => __( 'View Subscription', 'wp_subscription' ),
			'search_items'      => __( 'Search Subscription', 'wp_subscription' ),
			'not_found'         => __( 'No subscriptions found.', 'wp_subscription' ),
			'item_updated'      => __( 'Subscription updated.', 'wp_subscription' ),
		);

		$args = array(
			'label'                 => __( 'Subscriptions', 'wp_subscription' ),
			'labels'                => $labels,
			'description'           => '',
			'public'                => false,
			'publicly_queryable'    => false,
			'show_ui'               => true,
			'delete_with_user'      => false,
			'show_in_rest'          => true,
			'rest_base'             => '',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'has_archive'           => false,
			'show_in_menu'          => false,
			'show_in_nav_menus'     => false,
			'exclude_from_search'   => false,
			'capability_type'       => 'post',
			'map_meta_cap'          => true,
			'capabilities'          => array(
				'create_posts' => false,
			),
			'hierarchical'          => false,
			'rewrite'               => array(
				'slug'       => 'subscrpt_order',
				'with_front' => true,
			),
			'query_var'             => true,
			'supports'              => false,
		);

		$args = apply_filters( 'subscrpt_order_post_args', $args );

		register_post_type( 'subscrpt_order', $args );
	}

	/**
	 * Register ``subscrpt_order_item`` post type
	 */
	public function register_subscription_item_post_type() {
		$args = array(
			'label'                 => __( 'Subscription Items', 'wp_subscription' ),
			// 'labels'                => ,
			'description'           => '',
			'public'                => false,
			'publicly_queryable'    => false,
			'show_ui'               => false,
			'delete_with_user'      => false,
			'show_in_rest'          => true,
			'rest_base'             => '',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'has_archive'           => false,
			'show_in_menu'          => false,
			'show_in_nav_menus'     => false,
			'exclude_from_search'   => false,
			'capability_type'       => 'post',
			'map_meta_cap'          => true,
			'capabilities'          => array(
				'create_posts' => false,
			),
			'hierarchical'          => false,
			'rewrite'               => array(
				'slug'       => 'subscription_item',
				'with_front' => false,
			),
			'query_var'             => true,
			'supports'              => false,
		);

		$args = apply_filters( 'subscrpt_order_item_post_args', $args );

		register_post_type( 'subscrpt_order_item', $args );
	}

	/**
	 * Register custom post status.
	 *
	 * @return void
	 */
	public function register_post_status() {
		register_post_status(
			'pending',
			array(
				'label'                     => _x( 'Pending', 'post status label', 'wp_subscription' ),
				'public'                    => true,
				// translators: pending posts count.
				'label_count'               => _n_noop( 'Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>', 'wp_subscription' ),
				'post_type'                 => array( 'subscrpt_order' ),
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'show_in_metabox_dropdown'  => true,
				'show_in_inline_dropdown'   => true,
				'dashicon'                  => '',
			)
		);

		register_post_status(
			'active',
			array(
				'label'                     => _x( 'Active', 'post status label', 'wp_subscription' ),
				'public'                    => true,
				// translators: active posts count.
				'label_count'               => _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'wp_subscription' ),
				'post_type'                 => array( 'subscrpt_order' ),
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'show_in_metabox_dropdown'  => true,
				'show_in_inline_dropdown'   => true,
				'dashicon'                  => '',
			)
		);

		register_post_status(
			'on_hold',
			array(
				'label'                     => _x( 'On Hold', 'post status label', 'wp_subscription' ),
				'public'                    => true,
				// translators: on-hold posts count.
				'label_count'               => _n_noop( 'On Hold <span class="count">(%s)</span>', 'On Hold <span class="count">(%s)</span>', 'wp_subscription' ),
				'post_type'                 => array( 'subscrpt_order' ),
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'show_in_metabox_dropdown'  => true,
				'show_in_inline_dropdown'   => true,
				'dashicon'                  => '',
			)
		);

		register_post_status(
			'cancelled',
			array(
				'label'                     => _x( 'Cancelled', 'post status label', 'wp_subscription' ),
				'public'                    => true,
				// translators: cancelled posts count.
				'label_count'               => _n_noop( 'Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'wp_subscription' ),
				'post_type'                 => array( 'subscrpt_order' ),
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'show_in_metabox_dropdown'  => true,
				'show_in_inline_dropdown'   => true,
				'dashicon'                  => '',
			)
		);

		register_post_status(
			'expired',
			array(
				'label'                     => _x( 'Expired', 'post status label', 'wp_subscription' ),
				'public'                    => true,
				// translators: expired posts count.
				'label_count'               => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'wp_subscription' ),
				'post_type'                 => array( 'subscrpt_order' ),
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'show_in_metabox_dropdown'  => true,
				'show_in_inline_dropdown'   => true,
				'dashicon'                  => '',
			)
		);

		register_post_status(
			'pe_cancelled',
			array(
				'label'                     => _x( 'Pending Cancellation', 'post status label', 'wp_subscription' ),
				'public'                    => true,
				// translators: pending cancellation posts count.
				'label_count'               => _n_noop( 'Pending Cancellation <span class="count">(%s)</span>', 'Pending Cancellation <span class="count">(%s)</span>', 'wp_subscription' ),
				'post_type'                 => array( 'subscrpt_order' ),
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'show_in_metabox_dropdown'  => true,
				'show_in_inline_dropdown'   => true,
				'dashicon'                  => '',
			)
		);
	}
}
