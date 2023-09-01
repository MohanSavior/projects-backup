<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Recurly_API class.
 *
 * Communicates with Recurly API.
 */
class WC_Recurly_API {

	/**
	 * Recurly API Endpoint
	 */
	const ID = 'recurly';
	const ENDPOINT           = 'https://v3.recurly.com/';
	const RECURLY_API_VERSION = 'v2021-02-25';

	/**
	 * Secret API Key.
	 *
	 * @var string
	 */
	private static $secret_key = '';

	/**
	 * Set secret API Key.
	 *
	 * @param string $key
	 */
	public static function set_secret_key( $secret_key ) {
		self::$secret_key = $secret_key;
	}

	/**
	 * Get secret key.
	 *
	 * @return string
	 */
	public static function get_secret_key() {
		if ( ! self::$secret_key ) {
			$options = get_option( 'woocommerce_recurly_settings' );

			if ( isset( $options['testmode'], $options['private_key'], $options['demo_private_key'] ) ) {
				self::set_secret_key( 'yes' === $options['testmode'] ? $options['demo_private_key'] : $options['private_key'] );
			}
		}
		return self::$secret_key;
	}

	/**
	 * Generates the user agent we use to pass to API request so
	 * Recurly can identify our application.
	 *
	 * @since 4.0.0
	 * @version 4.0.0
	 */
	public static function get_user_agent() {
		$app_info = [
			'name'       => 'Recurly',
			'version'    =>  WC_RECURLY_VERSION,
			'url'        => 'https://developers.recurly.com/api/v2021-02-25/'
		];

		return [
			'lang'         => 'php',
			'lang_version' => phpversion(),
			'publisher'    => 'recurly',
			'uname'        => php_uname(),
			'application'  => $app_info,
		];
	}

	/**
	 * Generates the headers to pass to API request.
	 *
	 * @since 4.0.0
	 * @version 4.0.0
	 */
	public static function get_headers() {
		$user_agent = self::get_user_agent();
		$app_info   = $user_agent['application'];

		$headers = apply_filters(
			'woocommerce_recurly_request_headers',
			[
				'Authorization'  => 'Basic ' . base64_encode( self::get_secret_key() . ':' ),
				'Recurly-Version' => self::ID.'.'.self::RECURLY_API_VERSION,
				'Accept' => 'application/vnd.'.self::ID.'.'.self::RECURLY_API_VERSION,
				'Content-Type' => 'application/json'
			]
		);
		// error_log(print_r('$headers',true)); 
		// error_log(print_r($headers,true)); 

		// These headers should not be overridden for this gateway.
		$headers['User-Agent'] = $app_info['name'] . '/' . $app_info['version'] . ' (' . $app_info['url'] . ')';
		$headers['X-Recurly-Client-User-Agent'] = wp_json_encode( $user_agent );

		return $headers;
	}

	/**
	 * Send the request to Recurly's API
	 *
	 * @since 3.1.0
	 * @version 4.0.6
	 * @param array  $request
	 * @param string $api
	 * @param string $method
	 * @param bool   $with_headers To get the response with headers.
	 * @return stdClass|array
	 * @throws WC_Recurly_Exception
	 */
	public static function request( $request, $api = 'charges', $method = 'POST', $with_headers = false ) {
		WC_Recurly_Logger::log( "{$api} request: " . print_r( $request, true ) );

		$headers         = self::get_headers();
		$idempotency_key = '';

		if ( 'charges' === $api && 'POST' === $method ) {
			$customer        = ! empty( $request['customer'] ) ? $request['customer'] : '';
			$source          = ! empty( $request['source'] ) ? $request['source'] : $customer;
			$idempotency_key = apply_filters( 'wc_recurly_idempotency_key', $request['metadata']['order_id'] . '-' . $source, $request );

			$headers['Idempotency-Key'] = $idempotency_key;
		}

		if(empty($request)){
			$headers['Content-Length'] = 0;
		}

		$response = wp_safe_remote_post(
			self::ENDPOINT . $api,
			[
				'method'  => $method,
				'headers' => $headers,
				'body'    => apply_filters( 'woocommerce_recurly_request_body', $request, $api ),
				'timeout' => 70,
			]
		);

		// error_log(print_r('---------wp_safe_remote_post----------',true));
        // error_log(print_r($response,true));

		if ( is_wp_error( $response ) || empty( $response['body'] ) ) {
			WC_Recurly_Logger::log(
				'Error Response: ' . print_r( $response, true ) . PHP_EOL . PHP_EOL . 'Failed request: ' . print_r(
					[
						'api'             => $api,
						'request'         => $request,
						'idempotency_key' => $idempotency_key,
					],
					true
				)
			);

			throw new WC_Recurly_Exception( print_r( $response, true ), __( 'There was a problem connecting to the Recurly API endpoint.', 'woocommerce-gateway-recurly' ) );
		}

		if ( $with_headers ) {
			return [
				'headers' => wp_remote_retrieve_headers( $response ),
				'body'    => json_decode( $response['body'] ),
			];
		}

		return json_decode( $response['body'] );
	}

	/**
	 * Retrieve API endpoint.
	 *
	 * @since 4.0.0
	 * @version 4.0.0
	 * @param string $api
	 */
	public static function retrieve( $api ) {
		WC_Recurly_Logger::log( "{$api}" );

		$response = wp_safe_remote_get(
			self::ENDPOINT . $api,
			[
				'method'  => 'GET',
				'headers' => self::get_headers(),
				'timeout' => 70,
			]
		);

		if ( is_wp_error( $response ) || empty( $response['body'] ) ) {
			WC_Recurly_Logger::log( 'Error Response: ' . print_r( $response, true ) );
			return new WP_Error( 'recurly_error', __( 'There was a problem connecting to the Recurly API endpoint.', 'woocommerce-gateway-recurly' ) );
		}

		return json_decode( $response['body'] );
	}

	public static function recurly_retrieve( $api, $method = 'PUT') {
		WC_Recurly_Logger::log( "{$api}" );

		$response = wp_safe_remote_get(
			self::ENDPOINT . $api,
			[
				'method'  => $method,
				'headers' => self::get_headers(),
				'timeout' => 70,
			]
		);

		if ( is_wp_error( $response ) || empty( $response['body'] ) ) {
			WC_Recurly_Logger::log( 'Error Response: ' . print_r( $response, true ) );
			return new WP_Error( 'recurly_error', __( 'There was a problem connecting to the Recurly API endpoint.', 'woocommerce-gateway-recurly' ) );
		}

		return json_decode( $response['body'] );
	}

	/**
	 * Send the request to Recurly's API with level 3 data generated
	 * from the order. If the request fails due to an error related
	 * to level3 data, make the request again without it to allow
	 * the payment to go through.
	 *
	 * @since 4.3.2
	 * @version 5.1.0
	 *
	 * @param array    $request     Array with request parameters.
	 * @param string   $api         The API path for the request.
	 * @param array    $level3_data The level 3 data for this request.
	 * @param WC_Order $order       The order associated with the payment.
	 *
	 * @return stdClass|array The response
	 */
	public static function request_with_level3_data( $request, $api, $level3_data, $order ) {
		// 1. Do not add level3 data if the array is empty.
		// 2. Do not add level3 data if there's a transient indicating that level3 was
		// not accepted by Recurly in the past for this account.
		// 3. Do not try to add level3 data if merchant is not based in the US.
		// https://recurly.com/docs/level3#level-iii-usage-requirements
		// (Needs to be authenticated with a level3 gated account to see above docs).
		if (
			empty( $level3_data ) ||
			get_transient( 'wc_recurly_level3_not_allowed' ) ||
			'US' !== WC()->countries->get_base_country()
		) {
			return self::request(
				$request,
				$api
			);
		}

		// Add level 3 data to the request.
		$request['level3'] = $level3_data;

		$result = self::request(
			$request,
			$api
		);

		$is_level3_param_not_allowed = (
			isset( $result->error )
			&& isset( $result->error->code )
			&& 'parameter_unknown' === $result->error->code
			&& isset( $result->error->param )
			&& 'level3' === $result->error->param
		);

		$is_level_3data_incorrect = (
			isset( $result->error )
			&& isset( $result->error->type )
			&& 'invalid_request_error' === $result->error->type
		);

		if ( $is_level3_param_not_allowed ) {
			// Set a transient so that future requests do not add level 3 data.
			// Transient is set to expire in 3 months, can be manually removed if needed.
			set_transient( 'wc_recurly_level3_not_allowed', true, 3 * MONTH_IN_SECONDS );
		} elseif ( $is_level_3data_incorrect ) {
			// Log the issue so we could debug it.
			WC_Recurly_Logger::log(
				'Level3 data sum incorrect: ' . PHP_EOL
				. print_r( $result->error->message, true ) . PHP_EOL
				. print_r( 'Order line items: ', true ) . PHP_EOL
				. print_r( $order->get_items(), true ) . PHP_EOL
				. print_r( 'Order shipping amount: ', true ) . PHP_EOL
				. print_r( $order->get_shipping_total(), true ) . PHP_EOL
				. print_r( 'Order currency: ', true ) . PHP_EOL
				. print_r( $order->get_currency(), true )
			);
		}

		// Make the request again without level 3 data.
		if ( $is_level3_param_not_allowed || $is_level_3data_incorrect ) {
			unset( $request['level3'] );
			return self::request(
				$request,
				$api
			);
		}

		return $result;
	}
}
