<?php

namespace SpringDevs\Subscription\Illuminate\Gateways\Paypal;

use Exception;
use WC_Order;

/**
 * Class Paypal
 * PayPal Payment Gateway for Subscription Plugin
 *
 * @package SpringDevs\Subscription\Illuminate\Gateways
 */
class Paypal extends \WC_Payment_Gateway {

	/**
	 * Sandbox mode.
	 *
	 * @var bool
	 */
	public $sandbox_mode = false;

	/**
	 * PayPal Client ID.
	 *
	 * @var string
	 */
	public $client_id;

	/**
	 * PayPal Client Secret.
	 *
	 * @var string
	 */
	public $client_secret;

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                 = 'wp_subscription_paypal';
		$this->has_fields         = false;
		$this->method_title       = __( 'Paypal for WPSubscription', 'wp_subscription' );
		$this->method_description = __( 'Accept wp subscription recurring payments through PayPal.', 'wp_subscription' );
		$this->supports           = [ 'products', 'subscriptions', 'refunds' ];
		$this->icon               = apply_filters( 'wp_subscription_paypal_icon', WP_SUBSCRIPTION_URL . '/assets/images/paypal.svg' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Plugin variables.
		$this->enabled     = $this->get_option( 'enabled' );
		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );

		// Paypal Credentials.
		$this->sandbox_mode  = 'yes' === $this->get_option( 'testmode', 'no' );
		$this->client_id     = $this->get_option( 'client_id' );
		$this->client_secret = $this->get_option( 'client_secret' );

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Initialize Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = [
			'enabled'            => [
				'title'       => __( 'Enable/Disable', 'wp_subscription' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable PayPal for WPSubscription', 'wp_subscription' ),
				'default'     => 'no',
				'description' => __( 'Enable or Disable Paypal for WPSubscription payment gateway', 'wp_subscription' ),
				'desc_tip'    => true,
			],
			'title'              => [
				'title'       => __( 'Title', 'wp_subscription' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'wp_subscription' ),
				'default'     => __( 'PayPal', 'wp_subscription' ),
				'desc_tip'    => true,
			],
			'description'        => [
				'title'       => __( 'Description', 'wp_subscription' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'wp_subscription' ),
				'default'     => __( 'Pay via PayPal; you can pay with your credit card if you do not have a PayPal account.', 'wp_subscription' ),
				'desc_tip'    => true,
			],

			'paypal_creds_title' => [
				'title'       => __( 'PayPal Credentials', 'wp_subscription' ),
				'type'        => 'title',
				'description' => sprintf(
					// Translators: %1$s is the link to PayPal developer account, %2$s is the link to My Apps & Credentials.
					__( 'Create a <a href="%1$s" target="_blank">PayPal developer account</a>, go to <a href="%2$s" target="_blank">My Apps & Credentials</a>, select the toggle ( Sandbox or Live ), create an app, and copy <b>Client ID</b> and <b>Secret</b>.', 'wp_subscription' ),
					'https://developer.paypal.com',
					'https://developer.paypal.com/dashboard/applications'
				),
			],
			'testmode'           => [
				'title'       => __( 'PayPal Sandbox', 'wp_subscription' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable PayPal Sandbox', 'wp_subscription' ),
				'default'     => 'no',
				'description' => __( 'PayPal sandbox can be used to test payments without using real money.', 'wp_subscription' ),
				'desc_tip'    => true,
			],
			'email'              => [
				'title'       => __( 'Email', 'wp_subscription' ),
				'type'        => 'email',
				'description' => __( 'PayPal Email Address (used to receive payments)', 'wp_subscription' ),
				'default'     => '',
				'desc_tip'    => true,
			],
			'client_id'          => [
				'title'       => __( 'Client ID', 'wp_subscription' ),
				'type'        => 'password',
				'description' => __( 'Enter your PayPal Client ID copied from Paypal Apps & Credentials.', 'wp_subscription' ),
				'default'     => '',
				'desc_tip'    => true,
			],
			'client_secret'      => [
				'title'       => __( 'Secret', 'wp_subscription' ),
				'type'        => 'password',
				'description' => __( 'Enter your PayPal Secret copied from Paypal Apps & Credentials.', 'wp_subscription' ),
				'default'     => '',
				'desc_tip'    => true,
			],
		];
	}

	/**
	 * Check if paypal can be used for the currency selected in the store.
	 *
	 * @return boolean
	 */
	public function is_currency_supported() {
		return in_array(
			get_woocommerce_currency(),
			apply_filters(
				'wp_subs_paypal_supported_currencies',
				[ 'AUD', 'BRL', 'CAD', 'MXN', 'NZD', 'HKD', 'SGD', 'USD', 'EUR', 'JPY', 'NOK', 'CZK', 'DKK', 'HUF', 'ILS', 'MYR', 'PHP', 'PLN', 'SEK', 'CHF', 'TWD', 'THB', 'GBP', 'RUB', 'INR' ]
			),
			true
		);
	}

	/**
	 * Show admin options is valid for use.
	 *
	 * @since 1.0.0
	 */
	public function admin_options() {
		if ( $this->is_currency_supported() ) {
			parent::admin_options();
		} else {
			$currency_not_supported_message = sprintf(
				// Translators: %s is the title of the payment gateway.
				__( '<strong>%s</strong> options are disabled. PayPal Standard does not support your store currency.', 'wp_subscription' ),
				$this->title
			);

			?>
			<div class="inline error">
				<p>
					<?php echo wp_kses_post( $currency_not_supported_message ); ?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Get Paypal Access Token.
	 */
	protected function get_paypal_access_token(): ?string {
		$endpoint = $this->sandbox_mode ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';

		try {
			$response = wp_remote_post(
				$endpoint . '/v1/oauth2/token',
				[
					'method'      => 'POST',
					'timeout'     => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => [
						'Accept'          => 'application/json',
						'Accept-Language' => 'en_US',
						'Authorization'   => 'Basic ' . base64_encode( $this->client_id . ':' . $this->client_secret ),
					],
					'body'        => [
						'grant_type' => 'client_credentials',
					],
				]
			);

			$response_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( isset( $response_data->error ) || ! isset( $response_data->access_token ) ) {
				$log_message = 'Error retrieving PayPal access token: ' . $response_data->error_description;
				wp_subscrpt_write_log( $log_message );
				wp_subscrpt_write_debug_log( $log_message );

				return null;
			}

			return $response_data->access_token;
		} catch ( Exception $e ) {
			$log_message = 'Error retrieving PayPal access token: ' . $e->getMessage();
			wp_subscrpt_write_log( $log_message );
			wp_subscrpt_write_debug_log( $log_message );

			return null;
		}
	}

	/**
	 * Process Payment.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		// echo 'Processing payment for order ID: ' . $order_id;
		// die();

		$order = wc_get_order( $order_id );

		$response = $this->process_paypal_payments( $order );

		dd( '🔽 order', $order );

		// Return thankyou redirect.
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}

	/**
	 * Process payments in PayPal.
	 *
	 * TODO: add return types.
	 *
	 * @param WC_Order $order The order object.
	 */
	protected function process_paypal_payments( WC_Order $order ) {
		$access_token = $this->get_paypal_access_token();

		print_r( "access_token \n" );
		print_r( $access_token );
		die();
	}
}
