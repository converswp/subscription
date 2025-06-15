<?php

namespace SpringDevs\Subscription\Illuminate\Gateways;

/**
 * Class Paypal
 * PayPal Payment Gateway for Subscription Plugin
 *
 * @package SpringDevs\Subscription\Illuminate\Gateways
 */
class Paypal extends \WC_Payment_Gateway {

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                 = 'wp_subscription_paypal';
		$this->icon               = apply_filters( 'wp_subscription_paypal_icon', plugins_url( 'assets/images/paypal.svg', dirname( dirname( __DIR__ ) ) ) );
		$this->has_fields         = false;
		$this->method_title       = __( 'Paypal for WPSubscription', 'wp_subscription' );
		$this->method_description = __( 'Accept wp subscription recurring payments through PayPal.', 'wp_subscription' );
		$this->supports           = [ 'products', 'subscriptions', 'refunds' ];

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->enabled     = $this->get_option( 'enabled' );
		$this->testmode    = 'yes' === $this->get_option( 'testmode' );
		$this->email       = $this->get_option( 'email' );

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Initialize Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'     => array(
				'title'   => __( 'Enable/Disable', 'wp_subscription' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable PayPal Subscription', 'wp_subscription' ),
				'default' => 'no',
			),
			'title'       => array(
				'title'       => __( 'Title', 'wp_subscription' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'wp_subscription' ),
				'default'     => __( 'PayPal', 'wp_subscription' ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __( 'Description', 'wp_subscription' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'wp_subscription' ),
				'default'     => __( 'Pay via PayPal; you can pay with your credit card if you do not have a PayPal account.', 'wp_subscription' ),
			),
			'email'       => array(
				'title'       => __( 'PayPal Email', 'wp_subscription' ),
				'type'        => 'email',
				'description' => __( 'Please enter your PayPal email address; this is needed in order to take payment.', 'wp_subscription' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'testmode'    => array(
				'title'       => __( 'PayPal Sandbox', 'wp_subscription' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable PayPal Sandbox', 'wp_subscription' ),
				'default'     => 'no',
				'description' => __( 'PayPal sandbox can be used to test payments.', 'wp_subscription' ),
				'desc_tip'    => true,
			),
		);
	}

	/**
	 * Process Payment.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		// Return thankyou redirect.
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}

	/**
	 * Check if a product is a subscription product.
	 *
	 * @param object $product The product.
	 * @return boolean
	 */
	public function is_subscription_product( $product ) {
		// Implement your subscription product check logic here.
		return false;
	}
}
