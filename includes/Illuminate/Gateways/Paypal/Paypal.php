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
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
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

		return $this->process_paypal_payment( $order );
	}

	/**
	 * Process payments in PayPal.
	 *
	 * @param WC_Order $order The order object.
	 */
	protected function process_paypal_payment( WC_Order $order ): array {
		// Get PayPal Access Token.
		$access_token = $this->get_paypal_access_token();
		if ( ! $access_token ) {
			return [
				'result'   => 'error',
				'redirect' => '',
				'response' => 'PayPal payment failed. Please try again.',
			];
		}

		// Get the first order item.
		// Based on the logic, the order sould contain only one subscription item.
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
			return [
				'result'   => 'error',
				'redirect' => '',
				'response' => 'Invalid product in order. Please check the order details.',
			];
		}

		// Get Paypal Product ID.
		$paypal_product_id = $this->get_paypal_product_id( $wc_product_id, $access_token );

		if ( ! $paypal_product_id ) {
			return [
				'result'   => 'error',
				'redirect' => '',
				'response' => 'PayPal payment failed. Please try again.',
			];
		}

		// Get Paypal Plan ID.
		$paypal_plan_id = $this->get_paypal_plan_id( $wc_product_id, $wc_variation_id, $paypal_product_id, $access_token );

		if ( ! $paypal_plan_id ) {
			return [
				'result'   => 'error',
				'redirect' => '',
				'response' => 'PayPal payment failed. Please try again.',
			];
		}

		// Create Subscription in PayPal.
		$paypal_subscription_data = [
			'plan_id'             => $paypal_plan_id,
			'application_context' => [
				'return_url' => $this->get_return_url( $order ),
				'cancel_url' => $order->get_cancel_order_url(),
			],
		];

		$paypal_subscription = $this->create_paypal_subscription( $paypal_subscription_data, $access_token );

		if ( empty( $paypal_subscription->id ?? null ) ) {
			return [
				'result'   => 'error',
				'redirect' => '',
				'response' => 'PayPal payment failed. Please try again.',
			];
		}

		// Get payment link.
		$paypal_subscription_pay_link = null;
		foreach ( ( $paypal_subscription->links ?? [] ) as $link_obj ) {
			if ( 'approve' === $link_obj->rel ) {
				$paypal_subscription_pay_link = $link_obj->href;
				break;
			}
		}

		if ( empty( $paypal_subscription_pay_link ) ) {
			return [
				'result'   => 'error',
				'redirect' => '',
				'response' => 'PayPal payment failed. Please try again.',
			];
		} else {
			return [
				'result'   => 'success',
				'redirect' => $paypal_subscription_pay_link,
			];
		}
	}

	/**
	 * Get PayPal product ID.
	 *
	 * @param int    $wc_product_id WooCommerce Product ID.
	 * @param string $access_token  PayPal Access Token.
	 */
	public function get_paypal_product_id( int $wc_product_id, string $access_token ): ?string {
		$wc_product = wc_get_product( $wc_product_id );

		// Get data from product meta.
		// TODO: add home_url, wc_product_id etc to avoid duplication in paypal.
		$paypal_data = get_post_meta( $wc_product_id, $this->get_meta_key( 'product_data' ), true );

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
				update_post_meta( $wc_product_id, $this->get_meta_key( 'product_data' ), $data );
			}
		}

		// TODO: add logic to update image url if changed.
		// $current_image_url = $this->truncate_string( wp_get_attachment_url( $wc_product->get_image_id() ), 2000 );
		// if ( $paypal_product_id && $paypal_image_url !== $current_image_url ) {}

		// Return PayPal product ID or null if not found.
		return $paypal_product_id;
	}

	/**
	 * Get PayPal plan ID.
	 *
	 * @param int    $wc_product_id WooCommerce Product ID.
	 * @param int    $wc_variation_id WooCommerce Variation ID.
	 * @param string $paypal_product_id PayPal Product ID.
	 * @param string $access_token  PayPal Access Token.
	 */
	public function get_paypal_plan_id( int $wc_product_id, int $wc_variation_id, string $paypal_product_id, string $access_token ): ?string {
		$wc_product = wc_get_product( $wc_product_id );
		if ( 0 !== $wc_variation_id ) {
			$wc_product = wc_get_product( $wc_variation_id );
		}

		// Get data from product meta.
		$plan_id = get_post_meta( $wc_product_id, $this->get_meta_key( 'plan_id' ), true );
		// $plan_description = get_post_meta( $wc_product_id, $this->get_meta_key( 'plan_desc' ), true );

		if ( empty( $plan_id ) ) {
			$plan_id = null;
		}

		// Generate plan data.
		$plan_data = $this->generate_plan_data( $wc_product, $paypal_product_id );

		// TODO: implement logic to find and get plan.
		// ---------- .

		// Create plan if not available.
		if ( empty( $plan_id ) ) {
			$paypal_plan = $this->create_paypal_plan( $plan_data, $access_token );

			if ( $paypal_plan ) {
				$plan_id = $paypal_plan->id;

				// Save PayPal plan ID and description in WooCommerce product meta.
				update_post_meta( $wc_product_id, $this->get_meta_key( 'plan_id' ), $plan_id );
				update_post_meta( $wc_product_id, $this->get_meta_key( 'plan_desc' ), $paypal_plan->description ?? '' );
			}
		}

		// TODO: implement logic to update plan if changed.
		// ---------- .

		return $plan_id;
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

	/**
	 * Get Prefixed Meta Key.
	 * Prefix the key with '_wp_subs_' to avoid possible conflicts with other plugins.
	 *
	 * @param string $key The key to prefix.
	 */
	public function get_meta_key( string $key ): string {
		$keys         = [
			'product_data' => 'product_data',
			'plan_id'      => 'plan_id',
			'plan_desc'    => 'plan_description',
		];
		$selected_key = $keys[ $key ] ?? $key;
		return '_wp_subs_paypal_' . $selected_key;
	}

	/**
	 * Function to remove thousands separator.
	 *
	 * @param string $price The price to format.
	 */
	public function wpsubs_format_price( string $price ): string {
		$thousand_separator = wc_get_price_thousand_separator();
		$decimal_separator  = wc_get_price_decimal_separator();

		// Remove thousand separators.
		$price = str_replace( $thousand_separator, '', $price );

		// Remove trailing zeros.
		if ( strpos( $price, $decimal_separator ) !== false ) {
			$parts = explode( $decimal_separator, $price );

			// Remove trailing zeros from the decimal part.
			$parts[1] = rtrim( $parts[1], '0' );

			// Rejoin the parts.
			$price = '' === $parts[1] ? $parts[0] : $parts[0] . $decimal_separator . $parts[1];
		}
		return $price;
	}

	/**
	 * Generate Paypal Plan Data.
	 *
	 * @param WC_Product $wc_product WooCommerce Product.
	 * @param string     $paypal_product_id PayPal Product ID.
	 */
	public function generate_plan_data( WC_Product $wc_product, string $paypal_product_id ): array {
		// Get WPSubscription wrapped product.
		$wpsubs_product = sdevs_get_subscription_product( $wc_product );

		// Name.
		$name = $this->truncate_string( $wc_product->get_name(), 126 );

		// Description.
		$description = $this->truncate_string( $wc_product->get_short_description(), 126 );

		// Price.
		$price = wc_get_price_including_tax( $wpsubs_product );
		$price = $this->wpsubs_format_price( $price );

		// Convert plural interval to singular.
		// subscrpt_get_typos function of the plugin have translator on the intervals. PayPal will only accept english.
		$convert_interval = function ( $interval ) {
			switch ( strtolower( $interval ) ) {
				case 'day':
				case 'days':
					return 'DAY';
				case 'week':
				case 'weeks':
					return 'WEEK';
				case 'month':
				case 'months':
					return 'MONTH';
				case 'year':
				case 'years':
					return 'YEAR';
				default:
					return 'MONTH';
			}
		};

		// Recurring Details.
		$plan_length    = $wpsubs_product->get_timing_per();
		$plan_interval  = $convert_interval( $wpsubs_product->get_timing_option() );
		$trial_length   = $wpsubs_product->get_trial_timing_per();
		$trial_interval = $convert_interval( $wpsubs_product->get_trial_timing_option() );
		$signup_fee     = $wpsubs_product->get_signup_fee();

		// Billing Cycles.
		$billing_cycles = [];

		// Add trial cycle in billing cycles if available.
		if ( (int) $trial_length > 0 ) {
			$billing_cycles[] = [
				'tenure_type'  => 'TRIAL',
				'sequence'     => 1,
				'total_cycles' => 1,
				'frequency'    => [
					'interval_unit'  => $trial_interval,
					'interval_count' => (int) $trial_length,
				],
			];
		}

		// Add regular cycle in billing cycles.
		$billing_cycles[] = [
			'tenure_type'    => 'REGULAR',
			'sequence'       => count( $billing_cycles ) + 1,
			'total_cycles'   => 0,
			'pricing_scheme' => [
				'fixed_price' => [
					'value'         => $price,
					'currency_code' => get_woocommerce_currency(),
				],
			],
			'frequency'      => [
				'interval_unit'  => $plan_interval,
				'interval_count' => (int) $plan_length,
			],
		];

		// Payment Preferences.
		$payment_preferences = [
			'auto_bill_outstanding'     => true,
			'setup_fee_failure_action'  => 'CANCEL',
			'payment_failure_threshold' => 3,
			'setup_fee'                 => [
				'value'         => (string) $signup_fee,
				'currency_code' => get_woocommerce_currency(),
			],
		];

		// Final Data.
		$plan_data = [
			'product_id'          => $paypal_product_id,
			'name'                => $name,
			'description'         => $description,
			'billing_cycles'      => $billing_cycles,
			'quantity_supported'  => false,
			'payment_preferences' => $payment_preferences,
		];
		return $plan_data;
	}

	// * -------------------- Utility Methods [end] --------------------------- * //
	// * ---------------------------------------------------------------------- * //


	// * ---------------------------------------------------------------- * //
	// * -------------------- API Operations [start] -------------------- * //
	// ? Keep this section strictly for API operations. No other logic like data extraction should be added here.

	/**
	 * Get Paypal Access Token.
	 */
	private function get_paypal_access_token(): ?string {
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
	private function create_paypal_product( array $product_data, string $access_token ): ?object {
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
				$log_message = 'Error creating PayPal product: ' . ( $response_data->error_description ?? 'Unknown error' );
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

	/**
	 * Create PayPal plan.
	 *
	 * @param array  $plan_data     Plan data to create.
	 * @param string $access_token  PayPal Access Token.
	 */
	private function create_paypal_plan( array $plan_data, string $access_token ): ?object {
		// Prepare the body for the API request.
		$body = [
			'product_id'          => $plan_data['product_id'],
			'name'                => $plan_data['name'],
			'billing_cycles'      => $plan_data['billing_cycles'],
			'payment_preferences' => $plan_data['payment_preferences'],
		];
		if ( ! empty( $plan_data['description'] ?? null ) ) {
			$body['description'] = $plan_data['description'];
		}
		if ( ! empty( $plan_data['quantity_supported'] ?? null ) ) {
			$body['quantity_supported'] = $plan_data['quantity_supported'];
		}

		try {
			$url  = $this->api_endpoint . '/v1/billing/plans';
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
				$log_message = 'Error creating PayPal plan: ' . ( $response_data->error_description ?? 'Unknown error' );
				wp_subscrpt_write_log( $log_message );
				wp_subscrpt_write_debug_log( $log_message . ' ' . wp_json_encode( $response_data ) );
				return null;
			}

			return $response_data;
		} catch ( Exception $e ) {
			$log_message = 'Error creating PayPal plan: ' . $e->getMessage();
			wp_subscrpt_write_log( $log_message );
			wp_subscrpt_write_debug_log( $log_message );
			return null;
		}
	}

	/**
	 * Create PayPal subscription.
	 *
	 * @param array  $paypal_subscription_data PayPal subscription data.
	 * @param string $access_token    PayPal Access Token.
	 */
	private function create_paypal_subscription( array $paypal_subscription_data, string $access_token ): ?object {
		// Prepare the body for the API request.
		$body = [
			'plan_id'             => $paypal_subscription_data['plan_id'],
			'application_context' => $paypal_subscription_data['application_context'],
		];

		try {
			$url  = $this->api_endpoint . '/v1/billing/subscriptions';
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
				$log_message = 'Error creating PayPal subscription: ' . ( $response_data->error_description ?? 'Unknown error' );
				wp_subscrpt_write_log( $log_message );
				wp_subscrpt_write_debug_log( $log_message . ' ' . wp_json_encode( $response_data ) );
				return null;
			}

			return $response_data;
		} catch ( Exception $e ) {
			$log_message = 'Error creating PayPal subscription: ' . $e->getMessage();
			wp_subscrpt_write_log( $log_message );
			wp_subscrpt_write_debug_log( $log_message );
			return null;
		}
	}

	// * -------------------- API Operations [end] -------------------- * //
	// * -------------------------------------------------------------- * //
}
