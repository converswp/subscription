<?php

namespace SpringDevs\Subscription\Illuminate\Gateways\Paypal;

use Exception;
use WC_Order;
use WC_Order_Item_Product;
use WC_Product;

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
	protected $client_id;

	/**
	 * PayPal Client Secret.
	 *
	 * @var string
	 */
	protected $client_secret;

	/**
	 * API endpoint for PayPal.
	 *
	 * @var string
	 */
	protected $api_endpoint;

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

		// Set API endpoint.
		$this->api_endpoint = $this->sandbox_mode ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';

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
	 * Process Payment.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		$response = $this->process_paypal_payment( $order );

		print_r( "🔽 order - response \n" );
		print_r( $response );
		die();

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
	protected function process_paypal_payment( WC_Order $order ) {
		$access_token = $this->get_paypal_access_token();
		if ( ! $access_token ) {
			return array(
				'result'   => 'error',
				'redirect' => '',
				'response' => 'Error retrieving PayPal access token. Please check your PayPal credentials.',
			);
		}

		// Get the first order item.
		// Based on the logic, the order contains only one subscription item.
		$order_items = $order->get_items();
		$order_item  = ! empty( $order_items ) ? reset( $order_items ) : null;

		// Get WooCommerce Product.
		$wc_product_id   = null;
		$wc_variation_id = null;
		$wc_product      = null;
		if ( $order_item && $order_item instanceof WC_Order_Item_Product ) {
			$wc_product_id   = $order_item->get_product_id();
			$wc_variation_id = $order_item->get_variation_id();
			$wc_product      = wc_get_product( $wc_product_id );
		}

		if ( ! $wc_product ) {
			return array(
				'result'   => 'error',
				'redirect' => '',
				'response' => 'Invalid product in order. Please check the order details.',
			);
		}

		// Get Paypal Product ID.
		$paypal_product_id = $this->get_paypal_product_id( $wc_product, $access_token );

		print_r( "paypal_product_id \n" );
		print_r( $paypal_product_id );
		die();

		try {
			$request_id = uniqid( 'wp-subs-paypal-', true );
			$url        = $this->api_endpoint . '/v2/checkout/orders';
			$args       = array(
				'method'  => 'POST',
				'headers' => [
					'Authorization'                 => 'Bearer ' . $access_token,
					'Content-Type'                  => 'application/json',
					'Prefer'                        => 'return=representation',
					'PayPal-Request-Id'             => $request_id,
					'PayPal-Partner-Attribution-Id' => 'woo-wp-subs-paypal',
				],
				'body'    => wp_json_encode(
					array(
						'intent'              => 'CAPTURE',
						'purchase_units'      => array(
							self::get_purchase_details( $order ),
						),
						'application_context' => array(
							'brand_name'          => get_bloginfo( 'name' ),
							'return_url'          => $order->get_checkout_order_received_url(),
							'cancel_url'          => wc_get_checkout_url(),
							'landing_page'        => 'NO_PREFERENCE',
							'shipping_preference' => 'SET_PROVIDED_ADDRESS',
							'user_action'         => 'PAY_NOW',
						),
						'payment_method'      => array(
							'payee_preferred' => 'UNRESTRICTED',
							'payer_selected'  => 'PAYPAL',
						),
						'payer'               => array(
							'name'          => array(
								'given_name' => $order->get_billing_first_name(),
								'surname'    => $order->get_billing_last_name(),
							),
							'email_address' => $order->get_billing_email(),
							'address'       => array(
								'country_code'   => $order->get_shipping_country() ? $order->get_shipping_country() : $order->get_billing_country(),
								'address_line_1' => $order->get_shipping_address_1() ? $order->get_shipping_address_1() : $order->get_billing_address_1(),
								'address_line_2' => $order->get_shipping_address_2() ? $order->get_shipping_address_2() : $order->get_billing_address_2(),
								'postal_code'    => $order->get_shipping_postcode() ? $order->get_shipping_postcode() : $order->get_billing_postcode(),

							),
						),
						'payment_source'      => array(
							'paypal' => array(
								'attributes' => array(
									'customer' => array(
										'id' => 'wps_paypal_' . get_current_user_id(),
									),
									'vault'    => array(
										'confirm_payment_token' => 'ON_ORDER_COMPLETION',
										'usage_type'    => 'MERCHANT',
										'customer_type' => 'CONSUMER',
									),
								),
							),
						),
					)
				),
			);
		} catch ( Exception $e ) {
			// throw $th;
		}

		print_r( "access_token \n" );
		print_r( $access_token );
		die();
	}

	/**
	 * Get PayPal product ID.
	 *
	 * @param WC_Product $wc_product WooCommerce Product.
	 * @return string|null PayPal Product ID or null if not found.
	 */
	public function get_paypal_product_id( WC_Product $wc_product, string $access_token ): ?string {
		// Get data from product meta.
		// TODO: add home_url, wc_product_id etc to avoid duplication in paypal.
		$paypal_data = get_post_meta( $wc_product->get_id(), '_wp_subs_paypal_data', true );

		$paypal_product_id = $paypal_data['product_id'] ?? null;
		$paypal_image_url  = $paypal_data['image_url'] ?? null;

		// If PayPal product ID is not available in meta, get or create a new PayPal product.
		if ( ! $paypal_product_id ) {
			$paypal_product = $this->get_or_create_paypal_product( $wc_product, $access_token );

			if ( $paypal_product ) {
				$paypal_product_id = $paypal_product->id;

				// Save PayPal product ID in WooCommerce product meta.
				$data = [
					'product_id' => $paypal_product->id,
					'image_url'  => $paypal_product->image_url ?? '',
					'home_url'   => $product_data->home_url ?? '',
				];
				update_post_meta( $wc_product->get_id(), '_wp_subs_paypal_data', $data );
			}
		}

		// TODO: add logic to update image url if changed.
		// $current_image_url = $this->truncate_string( wp_get_attachment_url( $wc_product->get_image_id() ), 2000 );
		// if ( $paypal_product_id && $paypal_image_url !== $current_image_url ) {}

		// Return PayPal product ID or null if not found.
		return $paypal_product_id;
	}

	/**
	 * Get or create PayPal product.
	 *
	 * @param WC_Product $wc_product WooCommerce Product.
	 * @param string     $access_token PayPal Access Token.
	 */
	public function get_or_create_paypal_product( WC_Product $wc_product, string $access_token ) {
		// Prepare product data.
		$product_data = [
			'name'        => $this->truncate_string( $wc_product->get_name(), 126 ),
			'description' => $this->truncate_string( $wc_product->get_short_description(), 256 ),
			'type'        => $wc_product->get_virtual() ? 'DIGITAL' : 'PHYSICAL',
			'image_url'   => $wc_product->get_image_id() ? $this->truncate_string( wp_get_attachment_url( $wc_product->get_image_id() ), 2000 ) : '',
			'home_url'    => $this->truncate_string( get_permalink( $wc_product->get_id() ), 2000 ),
		];

		// TODO: implement logic to find existing PayPal product.
		// $paypal_product = $this->find_paypal_product( $product_data, $access_token );

		// If not found, create a new PayPal product.
		$paypal_product = $this->create_paypal_product( $product_data, $access_token );

		// Return PayPal product or null.
		return $paypal_product;
	}

	// * ------------------------------------------------------------------------ * //
	// * -------------------- Utility Methods [start] --------------------------- * //

	/**
	 * Truncate long string.
	 *
	 * @param string $long_string The long string to truncate.
	 * @param int    $max_length  The maximum length of the string.
	 * @return string The truncated string if it exceeds the maximum length, otherwise the original string
	 */
	public function truncate_string( string $long_string, int $max_length = 48 ): string {
		return strlen( $long_string ) <= $max_length ? $long_string : substr( $long_string, 0, $max_length );
	}

	// * -------------------- Utility Methods [end] --------------------------- * //
	// * ---------------------------------------------------------------------- * //


	// * ---------------------------------------------------------------- * //
	// * -------------------- API Operations [start] -------------------- * //
	// ? Keep this section strictly for API operations. No other logic like data extraction should be added here.

	/**
	 * Get Paypal Access Token.
	 */
	protected function get_paypal_access_token(): ?string {
		try {
			$url  = $this->api_endpoint . '/v1/oauth2/token';
			$args = [
				'method'  => 'POST',
				'headers' => [
					'Accept'          => 'application/json',
					'Accept-Language' => 'en_US',
					'Authorization'   => 'Basic ' . base64_encode( $this->client_id . ':' . $this->client_secret ),
				],
				'body'    => [
					'grant_type' => 'client_credentials',
				],
			];

			$response      = wp_remote_post( $url, $args );
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
	 * Create PayPal product.
	 *
	 * @param array  $product_data   Product data to create.
	 * @param string $access_token   PayPal Access Token.
	 */
	protected function create_paypal_product( array $product_data, string $access_token ): ?object {
		if ( empty( $product_data['name'] ?? null ) || empty( $product_data['type'] ?? null ) ) {
			$log_message = __( 'Paypal Product Creation Error: Product data is incomplete. Name and type are required.', 'wp_subscription' );
			wp_subscrpt_write_log( $log_message );
			wp_subscrpt_write_debug_log( $log_message );
			return null;
		}

		// Prepare the body for the API request.
		$body = [
			'name' => $product_data['name'],
			'type' => $product_data['type'],
		];
		if ( ! empty( $product_data['description'] ?? null ) ) {
			$body['description'] = $product_data['description'];
		}
		if ( ! empty( $product_data['category'] ?? null ) ) {
			$body['category'] = $product_data['category'];
		}
		if ( ! empty( $product_data['image_url'] ?? null ) && ! strpos( $product_data['image_url'], '.test' ) ) {
			$body['image_url'] = $product_data['image_url'];
		}
		if ( ! empty( $product_data['home_url'] ?? null ) && ! strpos( $product_data['home_url'], '.test' ) ) {
			$body['home_url'] = $product_data['home_url'];
		}

		try {
			$url  = $this->api_endpoint . '/v1/catalogs/products';
			$args = [
				'method'  => 'POST',
				'headers' => [
					'Authorization'     => 'Bearer ' . $access_token,
					'Content-Type'      => 'application/json',
					'Prefer'            => 'return=representation',
					'PayPal-Request-Id' => uniqid( 'wp-subs-paypal-', true ),
				],
				'body'    => wp_json_encode( $body ),
			];

			$response      = wp_remote_post( $url, $args );
			$response_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( empty( $response_data->id ?? null ) ) {
				$log_message = 'Error creating PayPal product: ' . ( $response_data->message ?? 'Unknown error' );
				wp_subscrpt_write_log( $log_message );
				wp_subscrpt_write_debug_log( $log_message . ' ' . wp_json_encode( $response_data ) );
				return null;
			}

			return $response_data;
		} catch ( Exception $e ) {
			$log_message = 'Error creating PayPal product: ' . $e->getMessage();
			wp_subscrpt_write_log( $log_message );
			wp_subscrpt_write_debug_log( $log_message );
			return null;
		}
	}

	// * -------------------- API Operations [end] -------------------- * //
	// * -------------------------------------------------------------- * //
}
