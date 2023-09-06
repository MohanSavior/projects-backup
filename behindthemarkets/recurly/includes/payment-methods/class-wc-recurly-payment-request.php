<?php
/**
 * Recurly Payment Request API
 * Adds support for Apple Pay and Chrome Payment Request API buttons.
 * Utilizes the Recurly Payment Request Button to support checkout from the product detail and cart pages.
 *
 * @package WooCommerce_Recurly/Classes/Payment_Request
 * @since   4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Recurly_Payment_Request class.
 */
class WC_Recurly_Payment_Request {
	/**
	 * Enabled.
	 *
	 * @var
	 */
	public $recurly_settings;

	/**
	 * Total label
	 *
	 * @var
	 */
	public $total_label;

	/**
	 * Key
	 *
	 * @var
	 */
	public $publishable_key;

	/**
	 * Key
	 *
	 * @var
	 */
	public $secret_key;

	/**
	 * Is test mode active?
	 *
	 * @var bool
	 */
	public $testmode;

	/**
	 * This Instance.
	 *
	 * @var
	 */
	private static $_this;

	/**
	 * Initialize class actions.
	 *
	 * @since   3.0.0
	 * @version 4.0.0
	 */
	public function __construct() {
		self::$_this           = $this;
		$this->recurly_settings = get_option( 'woocommerce_recurly_settings', [] );
		$this->testmode        = ( ! empty( $this->recurly_settings['testmode'] ) && 'yes' === $this->recurly_settings['testmode'] ) ? true : false;
		$this->publishable_key = ! empty( $this->recurly_settings['publishable_key'] ) ? $this->recurly_settings['publishable_key'] : '';
		$this->secret_key      = ! empty( $this->recurly_settings['secret_key'] ) ? $this->recurly_settings['secret_key'] : '';
		$this->total_label     = ! empty( $this->recurly_settings['statement_descriptor'] ) ? WC_Recurly_Helper::clean_statement_descriptor( $this->recurly_settings['statement_descriptor'] ) : '';

		if ( $this->testmode ) {
			$this->publishable_key = ! empty( $this->recurly_settings['test_publishable_key'] ) ? $this->recurly_settings['test_publishable_key'] : '';
			$this->secret_key      = ! empty( $this->recurly_settings['test_secret_key'] ) ? $this->recurly_settings['test_secret_key'] : '';
		}

		$this->total_label = str_replace( "'", '', $this->total_label ) . apply_filters( 'wc_recurly_payment_request_total_label_suffix', ' (via WooCommerce)' );

		// Checks if Recurly Gateway is enabled.
		if ( empty( $this->recurly_settings ) || ( isset( $this->recurly_settings['enabled'] ) && 'yes' !== $this->recurly_settings['enabled'] ) ) {
			return;
		}

		// Checks if Payment Request is enabled.
		if ( ! isset( $this->recurly_settings['payment_request'] ) || 'yes' !== $this->recurly_settings['payment_request'] ) {
			return;
		}

		// Don't load for change payment method page.
		if ( isset( $_GET['change_payment_method'] ) ) {
			return;
		}

		$this->init();
	}

	/**
	 * Checks whether authentication is required for checkout.
	 *
	 * @since   5.1.0
	 * @version 5.3.0
	 *
	 * @return bool
	 */
	public function is_authentication_required() {
		// If guest checkout is disabled and account creation upon checkout is not possible, authentication is required.
		if ( 'no' === get_option( 'woocommerce_enable_guest_checkout', 'yes' ) && ! $this->is_account_creation_possible() ) {
			return true;
		}
		// If cart contains subscription and account creation upon checkout is not posible, authentication is required.
		if ( $this->has_subscription_product() && ! $this->is_account_creation_possible() ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks whether account creation is possible upon checkout.
	 *
	 * @since 5.1.0
	 *
	 * @return bool
	 */
	public function is_account_creation_possible() {
		// If automatically generate username/password are disabled, the Payment Request API
		// can't include any of those fields, so account creation is not possible.
		return (
			'yes' === get_option( 'woocommerce_enable_signup_and_login_from_checkout', 'no' ) &&
			'yes' === get_option( 'woocommerce_registration_generate_username', 'yes' ) &&
			'yes' === get_option( 'woocommerce_registration_generate_password', 'yes' )
		);
	}

	/**
	 * Checks if keys are set and valid.
	 *
	 * @since  4.0.6
	 * @return boolean True if the keys are set *and* valid, false otherwise (for example, if keys are empty or the secret key was pasted as publishable key).
	 */
	public function are_keys_set() {
		// NOTE: updates to this function should be added to are_keys_set()
		// in includes/abstracts/abstract-wc-recurly-payment-gateway.php
		if ( $this->testmode ) {
			return preg_match( '/^pk_test_/', $this->publishable_key )
				&& preg_match( '/^[rs]k_test_/', $this->secret_key );
		} else {
			return preg_match( '/^pk_live_/', $this->publishable_key )
				&& preg_match( '/^[rs]k_live_/', $this->secret_key );
		}
	}

	/**
	 * Get this instance.
	 *
	 * @since  4.0.6
	 * @return class
	 */
	public static function instance() {
		return self::$_this;
	}

	/**
	 * Sets the WC customer session if one is not set.
	 * This is needed so nonces can be verified by AJAX Request.
	 *
	 * @since   4.0.0
	 * @version 5.2.0
	 * @return void
	 */
	public function set_session() {
		if ( ! $this->is_product() || ( isset( WC()->session ) && WC()->session->has_session() ) ) {
			return;
		}

		WC()->session->set_customer_session_cookie( true );
	}

	/**
	 * Handles payment request redirect when the redirect dialog "Continue" button is clicked.
	 *
	 * @since 5.3.0
	 */
	public function handle_payment_request_redirect() {
		if (
			! empty( $_GET['wc_recurly_payment_request_redirect_url'] )
			&& ! empty( $_GET['_wpnonce'] )
			&& wp_verify_nonce( $_GET['_wpnonce'], 'wc-recurly-set-redirect-url' ) // @codingStandardsIgnoreLine
		) {
			$url = rawurldecode( esc_url_raw( wp_unslash( $_GET['wc_recurly_payment_request_redirect_url'] ) ) );
			// Sets a redirect URL cookie for 10 minutes, which we will redirect to after authentication.
			// Users will have a 10 minute timeout to login/create account, otherwise redirect URL expires.
			wc_setcookie( 'wc_recurly_payment_request_redirect_url', $url, time() + MINUTE_IN_SECONDS * 10 );
			// Redirects to "my-account" page.
			wp_safe_redirect( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );
			exit;
		}
	}

	/**
	 * Initialize hooks.
	 *
	 * @since   4.0.0
	 * @version 5.3.0
	 * @return  void
	 */
	public function init() {

		add_action( 'template_redirect', [ $this, 'set_session' ] );
		add_action( 'template_redirect', [ $this, 'handle_payment_request_redirect' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ] );

		add_action( 'woocommerce_after_add_to_cart_quantity', [ $this, 'display_payment_request_button_html' ], 1 );
		add_action( 'woocommerce_after_add_to_cart_quantity', [ $this, 'display_payment_request_button_separator_html' ], 2 );

		add_action( 'woocommerce_proceed_to_checkout', [ $this, 'display_payment_request_button_html' ], 1 );
		add_action( 'woocommerce_proceed_to_checkout', [ $this, 'display_payment_request_button_separator_html' ], 2 );

		add_action( 'woocommerce_checkout_before_customer_details', [ $this, 'display_payment_request_button_html' ], 1 );
		add_action( 'woocommerce_checkout_before_customer_details', [ $this, 'display_payment_request_button_separator_html' ], 2 );

		add_action( 'wc_ajax_wc_recurly_get_cart_details', [ $this, 'ajax_get_cart_details' ] );
		add_action( 'wc_ajax_wc_recurly_get_shipping_options', [ $this, 'ajax_get_shipping_options' ] );
		add_action( 'wc_ajax_wc_recurly_update_shipping_method', [ $this, 'ajax_update_shipping_method' ] );
		add_action( 'wc_ajax_wc_recurly_create_order', [ $this, 'ajax_create_order' ] );
		add_action( 'wc_ajax_wc_recurly_add_to_cart', [ $this, 'ajax_add_to_cart' ] );
		add_action( 'wc_ajax_wc_recurly_get_selected_product_data', [ $this, 'ajax_get_selected_product_data' ] );
		add_action( 'wc_ajax_wc_recurly_clear_cart', [ $this, 'ajax_clear_cart' ] );
		add_action( 'wc_ajax_wc_recurly_log_errors', [ $this, 'ajax_log_errors' ] );

		add_filter( 'woocommerce_gateway_title', [ $this, 'filter_gateway_title' ], 10, 2 );
		add_action( 'woocommerce_checkout_order_processed', [ $this, 'add_order_meta' ], 10, 2 );
		add_filter( 'woocommerce_login_redirect', [ $this, 'get_login_redirect_url' ], 10, 3 );
		add_filter( 'woocommerce_registration_redirect', [ $this, 'get_login_redirect_url' ], 10, 3 );
	}

	/**
	 * Gets the button type.
	 *
	 * @since   4.0.0
	 * @version 4.0.0
	 * @return  string
	 */
	public function get_button_type() {
		return isset( $this->recurly_settings['payment_request_button_type'] ) ? $this->recurly_settings['payment_request_button_type'] : 'default';
	}

	/**
	 * Gets the button theme.
	 *
	 * @since   4.0.0
	 * @version 4.0.0
	 * @return  string
	 */
	public function get_button_theme() {
		return isset( $this->recurly_settings['payment_request_button_theme'] ) ? $this->recurly_settings['payment_request_button_theme'] : 'dark';
	}

	/**
	 * Gets the button height.
	 *
	 * @since   4.0.0
	 * @version 4.0.0
	 * @return  string
	 */
	public function get_button_height() {
		return isset( $this->recurly_settings['payment_request_button_height'] ) ? str_replace( 'px', '', $this->recurly_settings['payment_request_button_height'] ) : '64';
	}

	/**
	 * Checks if the button is branded.
	 *
	 * @since   4.4.0
	 * @version 4.4.0
	 * @return  boolean
	 */
	public function is_branded_button() {
		return 'branded' === $this->get_button_type();
	}

	/**
	 * Gets the branded button type.
	 *
	 * @since   4.4.0
	 * @version 4.4.0
	 * @return  string
	 */
	public function get_button_branded_type() {
		return isset( $this->recurly_settings['payment_request_button_branded_type'] ) ? $this->recurly_settings['payment_request_button_branded_type'] : 'default';
	}

	/**
	 * Checks if the button is custom.
	 *
	 * @since   4.4.0
	 * @version 4.4.0
	 * @return  boolean
	 */
	public function is_custom_button() {
		return 'custom' === $this->get_button_type();
	}

	/**
	 * Returns custom button css selector.
	 *
	 * @since   4.4.0
	 * @version 4.4.0
	 * @return  string
	 */
	public function custom_button_selector() {
		return $this->is_custom_button() ? '#wc-recurly-custom-button' : '';
	}

	/**
	 * Gets the custom button label.
	 *
	 * @since   4.4.0
	 * @version 4.4.0
	 * @return  string
	 */
	public function get_button_label() {
		return isset( $this->recurly_settings['payment_request_button_label'] ) ? $this->recurly_settings['payment_request_button_label'] : 'Buy now';
	}

	/**
	 * Gets the product total price.
	 *
	 * @since 5.2.0
	 *
	 * @param object $product WC_Product_* object.
	 * @return integer Total price.
	 */
	public function get_product_price( $product ) {
		$product_price = $product->get_price();
		// Add subscription sign-up fees to product price.
		if ( 'subscription' === $product->get_type() && class_exists( 'WC_Subscriptions_Product' ) ) {
			$product_price = $product->get_price() + WC_Subscriptions_Product::get_sign_up_fee( $product );
		}

		return $product_price;
	}

	/**
	 * Gets the product data for the currently viewed page
	 *
	 * @since   4.0.0
	 * @version 5.2.0
	 * @return  mixed Returns false if not on a product page, the product information otherwise.
	 */
	public function get_product_data() {
		if ( ! $this->is_product() ) {
			return false;
		}

		$product = $this->get_product();

		if ( 'variable' === $product->get_type() ) {
			$variation_attributes = $product->get_variation_attributes();
			$attributes           = [];

			foreach ( $variation_attributes as $attribute_name => $attribute_values ) {
				$attribute_key = 'attribute_' . sanitize_title( $attribute_name );

				// Passed value via GET takes precedence. Otherwise get the default value for given attribute
				$attributes[ $attribute_key ] = isset( $_GET[ $attribute_key ] )
					? wc_clean( wp_unslash( $_GET[ $attribute_key ] ) )
					: $product->get_variation_default_attribute( $attribute_name );
			}

			$data_store   = WC_Data_Store::load( 'product' );
			$variation_id = $data_store->find_matching_product_variation( $product, $attributes );

			if ( ! empty( $variation_id ) ) {
				$product = wc_get_product( $variation_id );
			}
		}

		$data  = [];
		$items = [];

		$items[] = [
			'label'  => $product->get_name(),
			'amount' => WC_Recurly_Helper::get_recurly_amount( $this->get_product_price( $product ) ),
		];

		if ( wc_tax_enabled() ) {
			$items[] = [
				'label'   => __( 'Tax', 'woocommerce-gateway-recurly' ),
				'amount'  => 0,
				'pending' => true,
			];
		}

		if ( wc_shipping_enabled() && $product->needs_shipping() ) {
			$items[] = [
				'label'   => __( 'Shipping', 'woocommerce-gateway-recurly' ),
				'amount'  => 0,
				'pending' => true,
			];

			$data['shippingOptions'] = [
				'id'     => 'pending',
				'label'  => __( 'Pending', 'woocommerce-gateway-recurly' ),
				'detail' => '',
				'amount' => 0,
			];
		}

		$data['displayItems'] = $items;
		$data['total']        = [
			'label'   => apply_filters( 'wc_recurly_payment_request_total_label', $this->total_label ),
			'amount'  => WC_Recurly_Helper::get_recurly_amount( $this->get_product_price( $product ) ),
			'pending' => true,
		];

		$data['requestShipping'] = ( wc_shipping_enabled() && $product->needs_shipping() && 0 !== wc_get_shipping_method_count( true ) );
		$data['currency']        = strtolower( get_woocommerce_currency() );
		$data['country_code']    = substr( get_option( 'woocommerce_default_country' ), 0, 2 );

		return apply_filters( 'wc_recurly_payment_request_product_data', $data, $product );
	}

	/**
	 * Filters the gateway title to reflect Payment Request type
	 */
	public function filter_gateway_title( $title, $id ) {
		global $post;

		if ( ! is_object( $post ) ) {
			return $title;
		}

		$order        = wc_get_order( $post->ID );
		$method_title = is_object( $order ) ? $order->get_payment_method_title() : '';

		if ( 'recurly' === $id && ! empty( $method_title ) ) {
			if ( 'Apple Pay (Recurly)' === $method_title
				|| 'Google Pay (Recurly)' === $method_title
				|| 'Payment Request (Recurly)' === $method_title
			) {
				return $method_title;
			}

			// We renamed 'Chrome Payment Request' to just 'Payment Request' since Payment Requests
			// are supported by other browsers besides Chrome. As such, we need to check for the
			// old title to make sure older orders still reflect that they were paid via Payment
			// Request Buttons.
			if ( 'Chrome Payment Request (Recurly)' === $method_title ) {
				return 'Payment Request (Recurly)';
			}
		}

		return $title;
	}

	/**
	 * Normalizes postal code in case of redacted data from Apple Pay.
	 *
	 * @since 5.2.0
	 *
	 * @param string $postcode Postal code.
	 * @param string $country Country.
	 */
	public function get_normalized_postal_code( $postcode, $country ) {
		/**
		 * Currently, Apple Pay truncates the UK and Canadian postal codes to the first 4 and 3 characters respectively
		 * when passing it back from the shippingcontactselected object. This causes WC to invalidate
		 * the postal code and not calculate shipping zones correctly.
		 */
		if ( 'GB' === $country ) {
			// Replaces a redacted string with something like LN10***.
			return str_pad( preg_replace( '/\s+/', '', $postcode ), 7, '*' );
		}
		if ( 'CA' === $country ) {
			// Replaces a redacted string with something like L4Y***.
			return str_pad( preg_replace( '/\s+/', '', $postcode ), 6, '*' );
		}

		return $postcode;
	}

	/**
	 * Add needed order meta
	 *
	 * @param integer $order_id    The order ID.
	 * @param array   $posted_data The posted data from checkout form.
	 *
	 * @since   4.0.0
	 * @version 4.0.0
	 * @return  void
	 */
	public function add_order_meta( $order_id, $posted_data ) {
		if ( empty( $_POST['payment_request_type'] ) ) {
			return;
		}

		$order = wc_get_order( $order_id );

		$payment_request_type = wc_clean( wp_unslash( $_POST['payment_request_type'] ) );

		if ( 'apple_pay' === $payment_request_type ) {
			$order->set_payment_method_title( 'Apple Pay (Recurly)' );
			$order->save();
		} elseif ( 'google_pay' === $payment_request_type ) {
			$order->set_payment_method_title( 'Google Pay (Recurly)' );
			$order->save();
		} elseif ( 'payment_request_api' === $payment_request_type ) {
			$order->set_payment_method_title( 'Payment Request (Recurly)' );
			$order->save();
		}
	}

	/**
	 * Checks to make sure product type is supported.
	 *
	 * @since   3.1.0
	 * @version 4.0.0
	 * @return  array
	 */
	public function supported_product_types() {
		return apply_filters(
			'wc_recurly_payment_request_supported_types',
			[
				'simple',
				'variable',
				'variation',
				'subscription',
				'variable-subscription',
				'subscription_variation',
				'booking',
				'bundle',
				'composite',
			]
		);
	}

	/**
	 * Checks the cart to see if all items are allowed to be used.
	 *
	 * @since   3.1.4
	 * @version 4.0.0
	 * @return  boolean
	 */
	public function allowed_items_in_cart() {
		// Pre Orders compatibility where we don't support charge upon release.
		if ( class_exists( 'WC_Pre_Orders_Cart' ) && WC_Pre_Orders_Cart::cart_contains_pre_order() && class_exists( 'WC_Pre_Orders_Product' ) && WC_Pre_Orders_Product::product_is_charged_upon_release( WC_Pre_Orders_Cart::get_pre_order_product() ) ) {
			return false;
		}

		// If the cart is not available we don't have any unsupported products in the cart, so we
		// return true. This can happen e.g. when loading the cart or checkout blocks in Gutenberg.
		if ( is_null( WC()->cart ) ) {
			return true;
		}

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

			if ( ! in_array( $_product->get_type(), $this->supported_product_types() ) ) {
				return false;
			}

			// Trial subscriptions with shipping are not supported.
			if ( class_exists( 'WC_Subscriptions_Product' ) && WC_Subscriptions_Product::is_subscription( $_product ) && $_product->needs_shipping() && WC_Subscriptions_Product::get_trial_length( $_product ) > 0 ) {
				return false;
			}
		}

		// We don't support multiple packages with Payment Request Buttons because we can't offer
		// a good UX.
		$packages = WC()->cart->get_shipping_packages();
		if ( 1 < count( $packages ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks whether cart contains a subscription product or this is a subscription product page.
	 *
	 * @since   5.3.0
	 * @version 5.3.0
	 * @return boolean
	 */
	public function has_subscription_product() {
		if ( ! class_exists( 'WC_Subscriptions_Product' ) ) {
			return false;
		}

		if ( $this->is_product() ) {
			$product = $this->get_product();
			if ( WC_Subscriptions_Product::is_subscription( $product ) ) {
				return true;
			}
		} elseif ( WC_Recurly_Helper::has_cart_or_checkout_on_current_page() ) {
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				if ( WC_Subscriptions_Product::is_subscription( $_product ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Checks if this is a product page or content contains a product_page shortcode.
	 *
	 * @since 5.2.0
	 * @return boolean
	 */
	public function is_product() {
		return is_product() || wc_post_content_has_shortcode( 'product_page' );
	}

	/**
	 * Get product from product page or product_page shortcode.
	 *
	 * @since 5.2.0
	 * @return WC_Product Product object.
	 */
	public function get_product() {
		global $post;

		if ( is_product() ) {
			return wc_get_product( $post->ID );
		} elseif ( wc_post_content_has_shortcode( 'product_page' ) ) {
			// Get id from product_page shortcode.
			preg_match( '/\[product_page id="(?<id>\d+)"\]/', $post->post_content, $shortcode_match );

			if ( ! isset( $shortcode_match['id'] ) ) {
				return false;
			}

			return wc_get_product( $shortcode_match['id'] );
		}

		return false;
	}

	/**
	 * Returns the login redirect URL.
	 *
	 * @since 5.3.0
	 *
	 * @param string $redirect Default redirect URL.
	 * @return string Redirect URL.
	 */
	public function get_login_redirect_url( $redirect ) {
		$url = esc_url_raw( wp_unslash( isset( $_COOKIE['wc_recurly_payment_request_redirect_url'] ) ? $_COOKIE['wc_recurly_payment_request_redirect_url'] : '' ) );

		if ( empty( $url ) ) {
			return $redirect;
		}
		wc_setcookie( 'wc_recurly_payment_request_redirect_url', null );

		return $url;
	}

	/**
	 * Returns the JavaScript configuration object used for any pages with a payment request button.
	 *
	 * @return array  The settings used for the payment request button in JavaScript.
	 */
	public function javascript_params() {
		$needs_shipping = 'no';
		if ( ! is_null( WC()->cart ) && WC()->cart->needs_shipping() ) {
			$needs_shipping = 'yes';
		}

		return [
			'ajax_url'           => WC_AJAX::get_endpoint( '%%endpoint%%' ),
			'recurly'             => [
				'key'                => $this->publishable_key,
				'allow_prepaid_card' => apply_filters( 'wc_recurly_allow_prepaid_card', true ) ? 'yes' : 'no',
			],
			'nonce'              => [
				'payment'                   => wp_create_nonce( 'wc-recurly-payment-request' ),
				'shipping'                  => wp_create_nonce( 'wc-recurly-payment-request-shipping' ),
				'update_shipping'           => wp_create_nonce( 'wc-recurly-update-shipping-method' ),
				'checkout'                  => wp_create_nonce( 'woocommerce-process_checkout' ),
				'add_to_cart'               => wp_create_nonce( 'wc-recurly-add-to-cart' ),
				'get_selected_product_data' => wp_create_nonce( 'wc-recurly-get-selected-product-data' ),
				'log_errors'                => wp_create_nonce( 'wc-recurly-log-errors' ),
				'clear_cart'                => wp_create_nonce( 'wc-recurly-clear-cart' ),
			],
			'i18n'               => [
				'no_prepaid_card'  => __( 'Sorry, we\'re not accepting prepaid cards at this time.', 'woocommerce-gateway-recurly' ),
				/* translators: Do not translate the [option] placeholder */
				'unknown_shipping' => __( 'Unknown shipping option "[option]".', 'woocommerce-gateway-recurly' ),
			],
			'checkout'           => [
				'url'               => wc_get_checkout_url(),
				'currency_code'     => strtolower( get_woocommerce_currency() ),
				'country_code'      => substr( get_option( 'woocommerce_default_country' ), 0, 2 ),
				'needs_shipping'    => $needs_shipping,
				// Defaults to 'required' to match how core initializes this option.
				'needs_payer_phone' => 'required' === get_option( 'woocommerce_checkout_phone_field', 'required' ),
			],
			'button'             => [
				'type'         => $this->get_button_type(),
				'theme'        => $this->get_button_theme(),
				'height'       => $this->get_button_height(),
				'locale'       => apply_filters( 'wc_recurly_payment_request_button_locale', substr( get_locale(), 0, 2 ) ), // Default format is en_US.
				'is_custom'    => $this->is_custom_button(),
				'is_branded'   => $this->is_branded_button(),
				'css_selector' => $this->custom_button_selector(),
				'branded_type' => $this->get_button_branded_type(),
			],
			'login_confirmation' => $this->get_login_confirmation_settings(),
			'is_product_page'    => $this->is_product(),
			'product'            => $this->get_product_data(),
		];
	}

	/**
	 * Load public scripts and styles.
	 *
	 * @since   3.1.0
	 * @version 5.2.0
	 */
	public function scripts() {
		// If page is not supported, bail.
		// Note: This check is not in `should_show_payment_request_button()` because that function is
		//       also called by the blocks support class, and this check would fail *incorrectly* when
		//       called from there.
		if ( ! $this->is_page_supported() ) {
			return;
		}

		if ( ! $this->should_show_payment_request_button() ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'recurly', 'https://js.recurly.com/v3/', '', '3.0', true );
		wp_register_script( 'wc_recurly_payment_request', plugins_url( 'assets/js/recurly-payment-request' . $suffix . '.js', WC_STRIPE_MAIN_FILE ), [ 'jquery', 'recurly' ], WC_STRIPE_VERSION, true );

		wp_localize_script(
			'wc_recurly_payment_request',
			'wc_recurly_payment_request_params',
			apply_filters(
				'wc_recurly_payment_request_params',
				$this->javascript_params()
			)
		);

		wp_enqueue_script( 'wc_recurly_payment_request' );

		$gateways = WC()->payment_gateways->get_available_payment_gateways();
		if ( isset( $gateways['recurly'] ) ) {
			$gateways['recurly']->payment_scripts();
		}
	}

	/**
	 * Returns true if the current page supports Payment Request Buttons, false otherwise.
	 *
	 * @since   5.3.0
	 * @version 5.3.0
	 * @return  boolean  True if the current page is supported, false otherwise.
	 */
	private function is_page_supported() {
		return $this->is_product()
			|| WC_Recurly_Helper::has_cart_or_checkout_on_current_page()
			|| isset( $_GET['pay_for_order'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Display the payment request button.
	 *
	 * @since   4.0.0
	 * @version 5.2.0
	 */
	public function display_payment_request_button_html() {
		$gateways = WC()->payment_gateways->get_available_payment_gateways();

		if ( ! isset( $gateways['recurly'] ) ) {
			return;
		}

		if ( ! $this->is_page_supported() ) {
			return;
		}

		if ( ! $this->should_show_payment_request_button() ) {
			return;
		}

		?>
		<div id="wc-recurly-payment-request-wrapper" style="clear:both;padding-top:1.5em;display:none;">
			<div id="wc-recurly-payment-request-button">
				<?php
				if ( $this->is_custom_button() ) {
					$label      = esc_html( $this->get_button_label() );
					$class_name = esc_attr( 'button ' . $this->get_button_theme() );
					$style      = esc_attr( 'height:' . $this->get_button_height() . 'px;' );
					echo "<button id=\"wc-recurly-custom-button\" class=\"$class_name\" style=\"$style\"> $label </button>";
				}
				?>
				<!-- A Recurly Element will be inserted here. -->
			</div>
		</div>
		<?php
	}

	/**
	 * Display payment request button separator.
	 *
	 * @since   4.0.0
	 * @version 5.2.0
	 */
	public function display_payment_request_button_separator_html() {
		$gateways = WC()->payment_gateways->get_available_payment_gateways();

		if ( ! isset( $gateways['recurly'] ) ) {
			return;
		}

		if ( ! is_cart() && ! is_checkout() && ! $this->is_product() && ! isset( $_GET['pay_for_order'] ) ) {
			return;
		}

		if ( is_checkout() && ! in_array( 'checkout', $this->get_button_locations(), true ) ) {
			return;
		}
		?>
		<p id="wc-recurly-payment-request-button-separator" style="margin-top:1.5em;text-align:center;display:none;">&mdash; <?php esc_html_e( 'OR', 'woocommerce-gateway-recurly' ); ?> &mdash;</p>
		<?php
	}

	/**
	 * Returns true if Payment Request Buttons are supported on the current page, false
	 * otherwise.
	 *
	 * @since   5.3.0
	 * @version 5.3.0
	 * @return  boolean  True if PRBs are supported on current page, false otherwise
	 */
	public function should_show_payment_request_button() {
		// If keys are not set bail.
		if ( ! $this->are_keys_set() ) {
			WC_Recurly_Logger::log( 'Keys are not set correctly.' );
			return false;
		}

		// If no SSL bail.
		if ( ! $this->testmode && ! is_ssl() ) {
			WC_Recurly_Logger::log( 'Recurly Payment Request live mode requires SSL.' );
			return false;
		}

		// Don't show if on the cart or checkout page, or if page contains the cart or checkout
		// shortcodes, with items in the cart that aren't supported.
		if (
			WC_Recurly_Helper::has_cart_or_checkout_on_current_page()
			&& ! $this->allowed_items_in_cart()
		) {
			return false;
		}

		// Don't show on cart if disabled.
		if ( is_cart() && ! $this->should_show_prb_on_cart_page() ) {
			return false;
		}

		// Don't show on checkout if disabled.
		if ( is_checkout() && ! $this->should_show_prb_on_checkout_page() ) {
			return false;
		}

		// Don't show if product page PRB is disabled.
		if ( $this->is_product() && ! $this->should_show_prb_on_product_pages() ) {
			return false;
		}

		// Don't show if product on current page is not supported.
		if ( $this->is_product() && ! $this->is_product_supported( $this->get_product() ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Returns true if Payment Request Buttons are enabled on the cart page, false
	 * otherwise.
	 *
	 * @since   5.5.0
	 * @version 5.5.0
	 * @return  boolean  True if PRBs are enabled on the cart page, false otherwise
	 */
	public function should_show_prb_on_cart_page() {
		// Message we show for the deprecated PRB location filters. Intended for support so we
		// don't provide translations.
		$deprecation_message      =
			'Please configure Payment Request Button locations through the Recurly plugin settings.';
		$should_show_on_cart_page = in_array( 'cart', $this->get_button_locations(), true );

		// Respect the deprecated filters, but add a deprecation notice.
		return apply_filters_deprecated(
			'wc_recurly_show_payment_request_on_cart',
			[ $should_show_on_cart_page ],
			'5.5.0',
			'', // There is no replacement.
			$deprecation_message
		);
	}

	/**
	 * Returns true if Payment Request Buttons are enabled on the checkout page, false
	 * otherwise.
	 *
	 * @since   5.5.0
	 * @version 5.5.0
	 * @return  boolean  True if PRBs are enabled on the checkout page, false otherwise
	 */
	public function should_show_prb_on_checkout_page() {
		global $post;

		// Message we show for the deprecated PRB location filters. Intended for support so we
		// don't provide translations.
		$deprecation_message          =
			'Please configure Payment Request Button locations through the Recurly plugin settings.';
		$should_show_on_checkout_page = in_array( 'checkout', $this->get_button_locations(), true );

		// Respect the deprecated filters, but add a deprecation notice.
		return apply_filters_deprecated(
			'wc_recurly_show_payment_request_on_checkout',
			[ $should_show_on_checkout_page, $post ],
			'5.5.0',
			'', // There is no replacement.
			$deprecation_message
		);
	}

	/**
	 * Returns true if Payment Request Buttons are enabled on product pages, false
	 * otherwise.
	 *
	 * @since   5.5.0
	 * @version 5.5.0
	 * @return  boolean  True if PRBs are enabled on product pages, false otherwise
	 */
	public function should_show_prb_on_product_pages() {
		global $post;

		// Message we show for the deprecated PRB location filters. Intended for support so we
		// don't provide translations.
		$deprecation_message         =
			'Please configure Payment Request Button locations through the Recurly plugin settings.';
		$should_show_on_product_page = in_array( 'product', $this->get_button_locations(), true );

		// Respect the deprecated filters, but add a deprecation notice.
		// Note the negation because if the filter returns `true` that means we should hide the PRB.
		return ! apply_filters_deprecated(
			'wc_recurly_hide_payment_request_on_product_page',
			[ ! $should_show_on_product_page, $post ],
			'5.5.0',
			'', // There is no replacement.
			$deprecation_message
		);
	}

	/**
	 * Returns true if a the provided product is supported, false otherwise.
	 *
	 * @param WC_Product $param  The product that's being checked for support.
	 *
	 * @since   5.3.0
	 * @version 5.3.0
	 * @return boolean  True if the provided product is supported, false otherwise.
	 */
	private function is_product_supported( $product ) {
		if ( ! is_object( $product ) || ! in_array( $product->get_type(), $this->supported_product_types() ) ) {
			return false;
		}

		// Trial subscriptions with shipping are not supported.
		if ( class_exists( 'WC_Subscriptions_Product' ) && $product->needs_shipping() && WC_Subscriptions_Product::get_trial_length( $product ) > 0 ) {
			return false;
		}

		// Pre Orders charge upon release not supported.
		if ( class_exists( 'WC_Pre_Orders_Product' ) && WC_Pre_Orders_Product::product_is_charged_upon_release( $product ) ) {
			return false;
		}

		// Composite products are not supported on the product page.
		if ( class_exists( 'WC_Composite_Products' ) && function_exists( 'is_composite_product' ) && is_composite_product() ) {
			return false;
		}

		// File upload addon not supported
		if ( class_exists( 'WC_Product_Addons_Helper' ) ) {
			$product_addons = WC_Product_Addons_Helper::get_product_addons( $product->get_id() );
			foreach ( $product_addons as $addon ) {
				if ( 'file_upload' === $addon['type'] ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Log errors coming from Payment Request
	 *
	 * @since   3.1.4
	 * @version 4.0.0
	 */
	public function ajax_log_errors() {
		check_ajax_referer( 'wc-recurly-log-errors', 'security' );

		$errors = isset( $_POST['errors'] ) ? wc_clean( wp_unslash( $_POST['errors'] ) ) : '';

		WC_Recurly_Logger::log( $errors );

		exit;
	}

	/**
	 * Clears cart.
	 *
	 * @since   3.1.4
	 * @version 4.0.0
	 */
	public function ajax_clear_cart() {
		check_ajax_referer( 'wc-recurly-clear-cart', 'security' );

		WC()->cart->empty_cart();
		exit;
	}

	/**
	 * Get cart details.
	 */
	public function ajax_get_cart_details() {
		check_ajax_referer( 'wc-recurly-payment-request', 'security' );

		if ( ! defined( 'WOOCOMMERCE_CART' ) ) {
			define( 'WOOCOMMERCE_CART', true );
		}

		WC()->cart->calculate_totals();

		$currency = get_woocommerce_currency();

		// Set mandatory payment details.
		$data = [
			'shipping_required' => WC()->cart->needs_shipping(),
			'order_data'        => [
				'currency'     => strtolower( $currency ),
				'country_code' => substr( get_option( 'woocommerce_default_country' ), 0, 2 ),
			],
		];

		$data['order_data'] += $this->build_display_items();

		wp_send_json( $data );
	}

	/**
	 * Get shipping options.
	 *
	 * @see WC_Cart::get_shipping_packages().
	 * @see WC_Shipping::calculate_shipping().
	 * @see WC_Shipping::get_packages().
	 */
	public function ajax_get_shipping_options() {
		check_ajax_referer( 'wc-recurly-payment-request-shipping', 'security' );

		$shipping_address          = filter_input_array(
			INPUT_POST,
			[
				'country'   => FILTER_SANITIZE_STRING,
				'state'     => FILTER_SANITIZE_STRING,
				'postcode'  => FILTER_SANITIZE_STRING,
				'city'      => FILTER_SANITIZE_STRING,
				'address'   => FILTER_SANITIZE_STRING,
				'address_2' => FILTER_SANITIZE_STRING,
			]
		);
		$product_view_options      = filter_input_array( INPUT_POST, [ 'is_product_page' => FILTER_SANITIZE_STRING ] );
		$should_show_itemized_view = ! isset( $product_view_options['is_product_page'] ) ? true : filter_var( $product_view_options['is_product_page'], FILTER_VALIDATE_BOOLEAN );

		$data = $this->get_shipping_options( $shipping_address, $should_show_itemized_view );
		wp_send_json( $data );
	}

	/**
	 * Gets shipping options available for specified shipping address
	 *
	 * @param array   $shipping_address       Shipping address.
	 * @param boolean $itemized_display_items Indicates whether to show subtotals or itemized views.
	 *
	 * @return array Shipping options data.
	 * phpcs:ignore Squiz.Commenting.FunctionCommentThrowTag
	 */
	public function get_shipping_options( $shipping_address, $itemized_display_items = false ) {
		try {
			// Set the shipping options.
			$data = [];

			// Remember current shipping method before resetting.
			$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
			$this->calculate_shipping( apply_filters( 'wc_recurly_payment_request_shipping_posted_values', $shipping_address ) );

			$packages          = WC()->shipping->get_packages();
			$shipping_rate_ids = [];

			if ( ! empty( $packages ) && WC()->customer->has_calculated_shipping() ) {
				foreach ( $packages as $package_key => $package ) {
					if ( empty( $package['rates'] ) ) {
						throw new Exception( __( 'Unable to find shipping method for address.', 'woocommerce-gateway-recurly' ) );
					}

					foreach ( $package['rates'] as $key => $rate ) {
						if ( in_array( $rate->id, $shipping_rate_ids, true ) ) {
							// The Payment Requests will try to load indefinitely if there are duplicate shipping
							// option IDs.
							throw new Exception( __( 'Unable to provide shipping options for Payment Requests.', 'woocommerce-gateway-recurly' ) );
						}
						$shipping_rate_ids[]        = $rate->id;
						$data['shipping_options'][] = [
							'id'     => $rate->id,
							'label'  => $rate->label,
							'detail' => '',
							'amount' => WC_Recurly_Helper::get_recurly_amount( $rate->cost ),
						];
					}
				}
			} else {
				throw new Exception( __( 'Unable to find shipping method for address.', 'woocommerce-gateway-recurly' ) );
			}

			// The first shipping option is automatically applied on the client.
			// Keep chosen shipping method by sorting shipping options if the method still available for new address.
			// Fallback to the first available shipping method.
			if ( isset( $data['shipping_options'][0] ) ) {
				if ( isset( $chosen_shipping_methods[0] ) ) {
					$chosen_method_id         = $chosen_shipping_methods[0];
					$compare_shipping_options = function ( $a, $b ) use ( $chosen_method_id ) {
						if ( $a['id'] === $chosen_method_id ) {
							return -1;
						}

						if ( $b['id'] === $chosen_method_id ) {
							return 1;
						}

						return 0;
					};
					usort( $data['shipping_options'], $compare_shipping_options );
				}

				$first_shipping_method_id = $data['shipping_options'][0]['id'];
				$this->update_shipping_method( [ $first_shipping_method_id ] );
			}

			WC()->cart->calculate_totals();

			$data          += $this->build_display_items( $itemized_display_items );
			$data['result'] = 'success';
		} catch ( Exception $e ) {
			$data          += $this->build_display_items( $itemized_display_items );
			$data['result'] = 'invalid_shipping_address';
		}

		return $data;
	}

	/**
	 * Update shipping method.
	 */
	public function ajax_update_shipping_method() {
		check_ajax_referer( 'wc-recurly-update-shipping-method', 'security' );

		if ( ! defined( 'WOOCOMMERCE_CART' ) ) {
			define( 'WOOCOMMERCE_CART', true );
		}

		$shipping_methods = filter_input( INPUT_POST, 'shipping_method', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$this->update_shipping_method( $shipping_methods );

		WC()->cart->calculate_totals();

		$product_view_options      = filter_input_array( INPUT_POST, [ 'is_product_page' => FILTER_SANITIZE_STRING ] );
		$should_show_itemized_view = ! isset( $product_view_options['is_product_page'] ) ? true : filter_var( $product_view_options['is_product_page'], FILTER_VALIDATE_BOOLEAN );

		$data           = [];
		$data          += $this->build_display_items( $should_show_itemized_view );
		$data['result'] = 'success';

		wp_send_json( $data );
	}

	/**
	 * Updates shipping method in WC session
	 *
	 * @param array $shipping_methods Array of selected shipping methods ids.
	 */
	public function update_shipping_method( $shipping_methods ) {
		$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

		if ( is_array( $shipping_methods ) ) {
			foreach ( $shipping_methods as $i => $value ) {
				$chosen_shipping_methods[ $i ] = wc_clean( $value );
			}
		}

		WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
	}

	/**
	 * Gets the selected product data.
	 *
	 * @since   4.0.0
	 * @version 4.0.0
	 * @return  array $data
	 */
	public function ajax_get_selected_product_data() {
		check_ajax_referer( 'wc-recurly-get-selected-product-data', 'security' );

		try {
			$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
			$qty          = ! isset( $_POST['qty'] ) ? 1 : apply_filters( 'woocommerce_add_to_cart_quantity', absint( $_POST['qty'] ), $product_id );
			$addon_value  = isset( $_POST['addon_value'] ) ? max( floatval( $_POST['addon_value'] ), 0 ) : 0;
			$product      = wc_get_product( $product_id );
			$variation_id = null;

			if ( ! is_a( $product, 'WC_Product' ) ) {
				/* translators: %d is the product Id */
				throw new Exception( sprintf( __( 'Product with the ID (%d) cannot be found.', 'woocommerce-gateway-recurly' ), $product_id ) );
			}

			if ( 'variable' === $product->get_type() && isset( $_POST['attributes'] ) ) {
				$attributes = wc_clean( wp_unslash( $_POST['attributes'] ) );

				$data_store   = WC_Data_Store::load( 'product' );
				$variation_id = $data_store->find_matching_product_variation( $product, $attributes );

				if ( ! empty( $variation_id ) ) {
					$product = wc_get_product( $variation_id );
				}
			}

			// Force quantity to 1 if sold individually and check for existing item in cart.
			if ( $product->is_sold_individually() ) {
				$qty = apply_filters( 'wc_recurly_payment_request_add_to_cart_sold_individually_quantity', 1, $qty, $product_id, $variation_id );
			}

			if ( ! $product->has_enough_stock( $qty ) ) {
				/* translators: 1: product name 2: quantity in stock */
				throw new Exception( sprintf( __( 'You cannot add that amount of "%1$s"; to the cart because there is not enough stock (%2$s remaining).', 'woocommerce-gateway-recurly' ), $product->get_name(), wc_format_stock_quantity_for_display( $product->get_stock_quantity(), $product ) ) );
			}

			$total = $qty * $this->get_product_price( $product ) + $addon_value;

			$quantity_label = 1 < $qty ? ' (x' . $qty . ')' : '';

			$data  = [];
			$items = [];

			$items[] = [
				'label'  => $product->get_name() . $quantity_label,
				'amount' => WC_Recurly_Helper::get_recurly_amount( $total ),
			];

			if ( wc_tax_enabled() ) {
				$items[] = [
					'label'   => __( 'Tax', 'woocommerce-gateway-recurly' ),
					'amount'  => 0,
					'pending' => true,
				];
			}

			if ( wc_shipping_enabled() && $product->needs_shipping() ) {
				$items[] = [
					'label'   => __( 'Shipping', 'woocommerce-gateway-recurly' ),
					'amount'  => 0,
					'pending' => true,
				];

				$data['shippingOptions'] = [
					'id'     => 'pending',
					'label'  => __( 'Pending', 'woocommerce-gateway-recurly' ),
					'detail' => '',
					'amount' => 0,
				];
			}

			$data['displayItems'] = $items;
			$data['total']        = [
				'label'   => $this->total_label,
				'amount'  => WC_Recurly_Helper::get_recurly_amount( $total ),
				'pending' => true,
			];

			$data['requestShipping'] = ( wc_shipping_enabled() && $product->needs_shipping() );
			$data['currency']        = strtolower( get_woocommerce_currency() );
			$data['country_code']    = substr( get_option( 'woocommerce_default_country' ), 0, 2 );

			wp_send_json( $data );
		} catch ( Exception $e ) {
			wp_send_json( [ 'error' => wp_strip_all_tags( $e->getMessage() ) ] );
		}
	}

	/**
	 * Adds the current product to the cart. Used on product detail page.
	 *
	 * @since   4.0.0
	 * @version 4.0.0
	 * @return  array $data
	 */
	public function ajax_add_to_cart() {
		check_ajax_referer( 'wc-recurly-add-to-cart', 'security' );

		if ( ! defined( 'WOOCOMMERCE_CART' ) ) {
			define( 'WOOCOMMERCE_CART', true );
		}

		WC()->shipping->reset_shipping();

		$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$qty          = ! isset( $_POST['qty'] ) ? 1 : absint( $_POST['qty'] );
		$product      = wc_get_product( $product_id );
		$product_type = $product->get_type();

		// First empty the cart to prevent wrong calculation.
		WC()->cart->empty_cart();

		if ( ( 'variable' === $product_type || 'variable-subscription' === $product_type ) && isset( $_POST['attributes'] ) ) {
			$attributes = wc_clean( wp_unslash( $_POST['attributes'] ) );

			$data_store   = WC_Data_Store::load( 'product' );
			$variation_id = $data_store->find_matching_product_variation( $product, $attributes );

			WC()->cart->add_to_cart( $product->get_id(), $qty, $variation_id, $attributes );
		}

		if ( 'simple' === $product_type || 'subscription' === $product_type ) {
			WC()->cart->add_to_cart( $product->get_id(), $qty );
		}

		WC()->cart->calculate_totals();

		$data           = [];
		$data          += $this->build_display_items();
		$data['result'] = 'success';

		wp_send_json( $data );
	}

	/**
	 * Normalizes billing and shipping state fields.
	 *
	 * @since 4.0.0
	 * @version 5.1.0
	 */
	public function normalize_state() {
		$billing_country  = ! empty( $_POST['billing_country'] ) ? wc_clean( wp_unslash( $_POST['billing_country'] ) ) : '';
		$shipping_country = ! empty( $_POST['shipping_country'] ) ? wc_clean( wp_unslash( $_POST['shipping_country'] ) ) : '';
		$billing_state    = ! empty( $_POST['billing_state'] ) ? wc_clean( wp_unslash( $_POST['billing_state'] ) ) : '';
		$shipping_state   = ! empty( $_POST['shipping_state'] ) ? wc_clean( wp_unslash( $_POST['shipping_state'] ) ) : '';

		if ( $billing_state && $billing_country ) {
			$_POST['billing_state'] = $this->get_normalized_state( $billing_state, $billing_country );
		}

		if ( $shipping_state && $shipping_country ) {
			$_POST['shipping_state'] = $this->get_normalized_state( $shipping_state, $shipping_country );
		}
	}

	/**
	 * Checks if given state is normalized.
	 *
	 * @since 5.1.0
	 *
	 * @param string $state State.
	 * @param string $country Two-letter country code.
	 *
	 * @return bool Whether state is normalized or not.
	 */
	public function is_normalized_state( $state, $country ) {
		$wc_states = WC()->countries->get_states( $country );
		return (
			is_array( $wc_states ) &&
			in_array( $state, array_keys( $wc_states ), true )
		);
	}

	/**
	 * Sanitize string for comparison.
	 *
	 * @since 5.1.0
	 *
	 * @param string $string String to be sanitized.
	 *
	 * @return string The sanitized string.
	 */
	public function sanitize_string( $string ) {
		return trim( wc_strtolower( remove_accents( $string ) ) );
	}

	/**
	 * Get normalized state from Payment Request API dropdown list of states.
	 *
	 * @since 5.1.0
	 *
	 * @param string $state   Full state name or state code.
	 * @param string $country Two-letter country code.
	 *
	 * @return string Normalized state or original state input value.
	 */
	public function get_normalized_state_from_pr_states( $state, $country ) {
		// Include Payment Request API State list for compatibility with WC countries/states.
		include_once WC_STRIPE_PLUGIN_PATH . '/includes/constants/class-wc-recurly-payment-request-button-states.php';
		$pr_states = WC_Recurly_Payment_Request_Button_States::STATES;

		if ( ! isset( $pr_states[ $country ] ) ) {
			return $state;
		}

		foreach ( $pr_states[ $country ] as $wc_state_abbr => $pr_state ) {
			$sanitized_state_string = $this->sanitize_string( $state );
			// Checks if input state matches with Payment Request state code (0), name (1) or localName (2).
			if (
				( ! empty( $pr_state[0] ) && $sanitized_state_string === $this->sanitize_string( $pr_state[0] ) ) ||
				( ! empty( $pr_state[1] ) && $sanitized_state_string === $this->sanitize_string( $pr_state[1] ) ) ||
				( ! empty( $pr_state[2] ) && $sanitized_state_string === $this->sanitize_string( $pr_state[2] ) )
			) {
				return $wc_state_abbr;
			}
		}

		return $state;
	}

	/**
	 * Get normalized state from WooCommerce list of translated states.
	 *
	 * @since 5.1.0
	 *
	 * @param string $state   Full state name or state code.
	 * @param string $country Two-letter country code.
	 *
	 * @return string Normalized state or original state input value.
	 */
	public function get_normalized_state_from_wc_states( $state, $country ) {
		$wc_states = WC()->countries->get_states( $country );

		if ( is_array( $wc_states ) ) {
			foreach ( $wc_states as $wc_state_abbr => $wc_state_value ) {
				if ( preg_match( '/' . preg_quote( $wc_state_value, '/' ) . '/i', $state ) ) {
					return $wc_state_abbr;
				}
			}
		}

		return $state;
	}

	/**
	 * Gets the normalized state/county field because in some
	 * cases, the state/county field is formatted differently from
	 * what WC is expecting and throws an error. An example
	 * for Ireland, the county dropdown in Chrome shows "Co. Clare" format.
	 *
	 * @since 5.0.0
	 * @version 5.1.0
	 *
	 * @param string $state   Full state name or an already normalized abbreviation.
	 * @param string $country Two-letter country code.
	 *
	 * @return string Normalized state abbreviation.
	 */
	public function get_normalized_state( $state, $country ) {
		// If it's empty or already normalized, skip.
		if ( ! $state || $this->is_normalized_state( $state, $country ) ) {
			return $state;
		}

		// Try to match state from the Payment Request API list of states.
		$state = $this->get_normalized_state_from_pr_states( $state, $country );

		// If it's normalized, return.
		if ( $this->is_normalized_state( $state, $country ) ) {
			return $state;
		}

		// If the above doesn't work, fallback to matching against the list of translated
		// states from WooCommerce.
		return $this->get_normalized_state_from_wc_states( $state, $country );
	}

	/**
	 * The Payment Request API provides its own validation for the address form.
	 * For some countries, it might not provide a state field, so we need to return a more descriptive
	 * error message, indicating that the Payment Request button is not supported for that country.
	 *
	 * @since 5.1.0
	 */
	public function validate_state() {
		$wc_checkout     = WC_Checkout::instance();
		$posted_data     = $wc_checkout->get_posted_data();
		$checkout_fields = $wc_checkout->get_checkout_fields();
		$countries       = WC()->countries->get_countries();

		$is_supported = true;
		// Checks if billing state is missing and is required.
		if ( ! empty( $checkout_fields['billing']['billing_state']['required'] ) && '' === $posted_data['billing_state'] ) {
			$is_supported = false;
		}

		// Checks if shipping state is missing and is required.
		if ( WC()->cart->needs_shipping_address() && ! empty( $checkout_fields['shipping']['shipping_state']['required'] ) && '' === $posted_data['shipping_state'] ) {
			$is_supported = false;
		}

		if ( ! $is_supported ) {
			wc_add_notice(
				sprintf(
					/* translators: %s: country. */
					__( 'The Payment Request button is not supported in %s because some required fields couldn\'t be verified. Please proceed to the checkout page and try again.', 'woocommerce-gateway-recurly' ),
					isset( $countries[ $posted_data['billing_country'] ] ) ? $countries[ $posted_data['billing_country'] ] : $posted_data['billing_country']
				),
				'error'
			);
		}
	}

	/**
	 * Create order. Security is handled by WC.
	 *
	 * @since   3.1.0
	 * @version 5.1.0
	 */
	public function ajax_create_order() {
		if ( WC()->cart->is_empty() ) {
			wp_send_json_error( __( 'Empty cart', 'woocommerce-gateway-recurly' ) );
		}

		if ( ! defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
			define( 'WOOCOMMERCE_CHECKOUT', true );
		}

		// In case the state is required, but is missing, add a more descriptive error notice.
		$this->validate_state();

		// Normalizes billing and shipping state values.
		$this->normalize_state();

		WC()->checkout()->process_checkout();

		die( 0 );
	}

	/**
	 * Calculate and set shipping method.
	 *
	 * @param array $address Shipping address.
	 *
	 * @since   3.1.0
	 * @version 5.0.0
	 */
	protected function calculate_shipping( $address = [] ) {
		$country   = $address['country'];
		$state     = $address['state'];
		$postcode  = $address['postcode'];
		$city      = $address['city'];
		$address_1 = $address['address'];
		$address_2 = $address['address_2'];

		// Normalizes state to calculate shipping zones.
		$state = $this->get_normalized_state( $state, $country );

		// Normalizes postal code in case of redacted data from Apple Pay.
		$postcode = $this->get_normalized_postal_code( $postcode, $country );

		WC()->shipping->reset_shipping();

		if ( $postcode && WC_Validation::is_postcode( $postcode, $country ) ) {
			$postcode = wc_format_postcode( $postcode, $country );
		}

		if ( $country ) {
			WC()->customer->set_location( $country, $state, $postcode, $city );
			WC()->customer->set_shipping_location( $country, $state, $postcode, $city );
		} else {
			WC()->customer->set_billing_address_to_base();
			WC()->customer->set_shipping_address_to_base();
		}

		WC()->customer->set_calculated_shipping( true );
		WC()->customer->save();

		$packages = [];

		$packages[0]['contents']                 = WC()->cart->get_cart();
		$packages[0]['contents_cost']            = 0;
		$packages[0]['applied_coupons']          = WC()->cart->applied_coupons;
		$packages[0]['user']['ID']               = get_current_user_id();
		$packages[0]['destination']['country']   = $country;
		$packages[0]['destination']['state']     = $state;
		$packages[0]['destination']['postcode']  = $postcode;
		$packages[0]['destination']['city']      = $city;
		$packages[0]['destination']['address']   = $address_1;
		$packages[0]['destination']['address_2'] = $address_2;

		foreach ( WC()->cart->get_cart() as $item ) {
			if ( $item['data']->needs_shipping() ) {
				if ( isset( $item['line_total'] ) ) {
					$packages[0]['contents_cost'] += $item['line_total'];
				}
			}
		}

		$packages = apply_filters( 'woocommerce_cart_shipping_packages', $packages );

		WC()->shipping->calculate_shipping( $packages );
	}

	/**
	 * Builds the shippings methods to pass to Payment Request
	 *
	 * @since   3.1.0
	 * @version 4.0.0
	 */
	protected function build_shipping_methods( $shipping_methods ) {
		if ( empty( $shipping_methods ) ) {
			return [];
		}

		$shipping = [];

		foreach ( $shipping_methods as $method ) {
			$shipping[] = [
				'id'     => $method['id'],
				'label'  => $method['label'],
				'detail' => '',
				'amount' => WC_Recurly_Helper::get_recurly_amount( $method['amount']['value'] ),
			];
		}

		return $shipping;
	}

	/**
	 * Builds the line items to pass to Payment Request
	 *
	 * @since   3.1.0
	 * @version 4.0.0
	 */
	protected function build_display_items( $itemized_display_items = false ) {
		if ( ! defined( 'WOOCOMMERCE_CART' ) ) {
			define( 'WOOCOMMERCE_CART', true );
		}

		$items         = [];
		$lines         = [];
		$subtotal      = 0;
		$discounts     = 0;
		$display_items = ! apply_filters( 'wc_recurly_payment_request_hide_itemization', true ) || $itemized_display_items;

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$subtotal      += $cart_item['line_subtotal'];
			$amount         = $cart_item['line_subtotal'];
			$quantity_label = 1 < $cart_item['quantity'] ? ' (x' . $cart_item['quantity'] . ')' : '';
			$product_name   = $cart_item['data']->get_name();

			$lines[] = [
				'label'  => $product_name . $quantity_label,
				'amount' => WC_Recurly_Helper::get_recurly_amount( $amount ),
			];
		}

		if ( $display_items ) {
			$items = array_merge( $items, $lines );
		} else {
			// Default show only subtotal instead of itemization.

			$items[] = [
				'label'  => 'Subtotal',
				'amount' => WC_Recurly_Helper::get_recurly_amount( $subtotal ),
			];
		}

		if ( version_compare( WC_VERSION, '3.2', '<' ) ) {
			$discounts = wc_format_decimal( WC()->cart->get_cart_discount_total(), WC()->cart->dp );
		} else {
			$applied_coupons = array_values( WC()->cart->get_coupon_discount_totals() );

			foreach ( $applied_coupons as $amount ) {
				$discounts += (float) $amount;
			}
		}

		$discounts   = wc_format_decimal( $discounts, WC()->cart->dp );
		$tax         = wc_format_decimal( WC()->cart->tax_total + WC()->cart->shipping_tax_total, WC()->cart->dp );
		$shipping    = wc_format_decimal( WC()->cart->shipping_total, WC()->cart->dp );
		$items_total = wc_format_decimal( WC()->cart->cart_contents_total, WC()->cart->dp ) + $discounts;
		$order_total = version_compare( WC_VERSION, '3.2', '<' ) ? wc_format_decimal( $items_total + $tax + $shipping - $discounts, WC()->cart->dp ) : WC()->cart->get_total( false );

		if ( wc_tax_enabled() ) {
			$items[] = [
				'label'  => esc_html( __( 'Tax', 'woocommerce-gateway-recurly' ) ),
				'amount' => WC_Recurly_Helper::get_recurly_amount( $tax ),
			];
		}

		if ( WC()->cart->needs_shipping() ) {
			$items[] = [
				'label'  => esc_html( __( 'Shipping', 'woocommerce-gateway-recurly' ) ),
				'amount' => WC_Recurly_Helper::get_recurly_amount( $shipping ),
			];
		}

		if ( WC()->cart->has_discount() ) {
			$items[] = [
				'label'  => esc_html( __( 'Discount', 'woocommerce-gateway-recurly' ) ),
				'amount' => WC_Recurly_Helper::get_recurly_amount( $discounts ),
			];
		}

		if ( version_compare( WC_VERSION, '3.2', '<' ) ) {
			$cart_fees = WC()->cart->fees;
		} else {
			$cart_fees = WC()->cart->get_fees();
		}

		// Include fees and taxes as display items.
		foreach ( $cart_fees as $key => $fee ) {
			$items[] = [
				'label'  => $fee->name,
				'amount' => WC_Recurly_Helper::get_recurly_amount( $fee->amount ),
			];
		}

		return [
			'displayItems' => $items,
			'total'        => [
				'label'   => $this->total_label,
				'amount'  => max( 0, apply_filters( 'woocommerce_recurly_calculated_total', WC_Recurly_Helper::get_recurly_amount( $order_total ), $order_total, WC()->cart ) ),
				'pending' => false,
			],
		];
	}

	/**
	 * Settings array for the user authentication dialog and redirection.
	 *
	 * @since   5.3.0
	 * @version 5.3.0
	 *
	 * @return array
	 */
	public function get_login_confirmation_settings() {
		if ( is_user_logged_in() || ! $this->is_authentication_required() ) {
			return false;
		}

		/* translators: The text encapsulated in `**` can be replaced with "Apple Pay" or "Google Pay". Please translate this text, but don't remove the `**`. */
		$message      = __( 'To complete your transaction with **the selected payment method**, you must log in or create an account with our site.', 'woocommerce-gateway-recurly' );
		$redirect_url = add_query_arg(
			[
				'_wpnonce'                               => wp_create_nonce( 'wc-recurly-set-redirect-url' ),
				'wc_recurly_payment_request_redirect_url' => rawurlencode( home_url( add_query_arg( [] ) ) ), // Current URL to redirect to after login.
			],
			home_url()
		);

		return [
			'message'      => $message,
			'redirect_url' => $redirect_url,
		];
	}

	public function get_button_locations() {
		// If the locations have not been set return the default setting.
		if ( ! isset( $this->recurly_settings['payment_request_button_locations'] ) ) {
			return [ 'product', 'cart' ];
		}

		// If all locations are removed through the settings UI the location config will be set to
		// an empty string "". If that's the case (and if the settings are not an array for any
		// other reason) we should return an empty array.
		if ( ! is_array( $this->recurly_settings['payment_request_button_locations'] ) ) {
			return [];
		}

		return $this->recurly_settings['payment_request_button_locations'];
	}
}