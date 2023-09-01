<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Recurly_Customer class.
 *
 * Represents a Recurly Customer.
 */
class WC_Recurly_Customer {

	/**
	 * Recurly customer ID
	 *
	 * @var string
	 */
	private $id = '';

	/**
	 * Recurly customer code
	 *
	 * @var string
	 */
	private $code = '';

	/**
	 * WP User ID
	 *
	 * @var integer
	 */
	private $user_id = 0;

	/**
	 * Data from API
	 *
	 * @var array
	 */
	private $customer_data = [];

	/**
	 * Constructor
	 *
	 * @param int $user_id The WP user ID
	 */
	public function __construct( $user_id = 0 ) {
		if ( $user_id ) {
			$this->set_user_id( $user_id );
			$this->set_id( $this->get_id_from_meta( $user_id ) );
			$this->set_code( $this->get_code_from_meta( $user_id ) );
		}
	}

	/**
	 * Get Recurly customer ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get Recurly customer code.
	 *
	 * @return string
	 */
	public function get_code() {
		return $this->code;
	}

	/**
	 * Set Recurly customer ID.
	 *
	 * @param [type] $id [description]
	 */
	public function set_id( $id ) {
		// Backwards compat for customer ID stored in array
		if ( is_array( $id ) && isset( $id['customer_id'] ) ) {
			$id = $id['customer_id'];

			$this->update_id_in_meta( $id );
		}

		$this->id = wc_clean( $id );
	}

	/**
	 * Set Recurly customer CODE.
	 *
	 * @param [type] $id [description]
	 */
	public function set_code( $code ) {
		// Backwards compat for customer CODE stored in array
		if ( is_array( $code ) && isset( $code['customer_code'] ) ) {
			$code = $code['customer_code'];

			$this->update_code_in_meta( $code );
		}

		$this->code = wc_clean( $code );
	}

	/**
	 * User ID in WordPress.
	 *
	 * @return int
	 */
	public function get_user_id() {
		return absint( $this->user_id );
	}

	/**
	 * Set User ID used by WordPress.
	 *
	 * @param int $user_id
	 */
	public function set_user_id( $user_id ) {
		$this->user_id = absint( $user_id );
	}

	/**
	 * Get user object.
	 *
	 * @return WP_User
	 */
	protected function get_user() {
		return $this->get_user_id() ? get_user_by( 'id', $this->get_user_id() ) : false;
	}

	/**
	 * Store data from the Recurly API about this customer
	 */
	public function set_customer_data( $data ) {
		$this->customer_data = $data;
	}

	/**
	 * Generates the customer request, used for both creating and updating customers.
	 *
	 * @param  array $args Additional arguments (optional).
	 * @return array
	 */
	protected function generate_customer_request( $args = [] ) {
		$billing_email = isset( $_POST['billing_email'] ) ? filter_var( wp_unslash( $_POST['billing_email'] ), FILTER_SANITIZE_EMAIL ) : '';
		$user          = is_user_logged_in() ? get_user_by('ID', get_current_user_id()) : $this->get_user();

		if ( $user ) {
			$billing_first_name = get_user_meta( $user->ID, 'billing_first_name', true );
			$billing_last_name  = get_user_meta( $user->ID, 'billing_last_name', true );

			$billing_address_1 	= get_user_meta( $user->ID, 'billing_address_1', true );
			$billing_address_2  = get_user_meta( $user->ID, 'billing_address_2', true );
			$billing_city 		= get_user_meta( $user->ID, 'billing_city', true );
			$billing_postcode  	= get_user_meta( $user->ID, 'billing_postcode', true );
			$billing_state 		= get_user_meta( $user->ID, 'billing_state', true );
			$billing_country  	= get_user_meta( $user->ID, 'billing_country', true );

			// If billing first name does not exists try the user first name.
			if ( empty( $billing_first_name ) ) {
				$billing_first_name = get_user_meta( $user->ID, 'first_name', true );
			}

			// If billing last name does not exists try the user last name.
			if ( empty( $billing_last_name ) ) {
				$billing_last_name = get_user_meta( $user->ID, 'last_name', true );
			}
				$user = get_user_by( 'id', $user->ID );

			$account_code = get_user_meta( $user->ID, '_recurly_account_code', true );
			$recurly_account_code = $account_code ? $account_code : $user->user_email;

			$defaults = [
				'code'		  	=> $recurly_account_code,
				'email'       	=> $user->user_email,
				"first_name" 	=> $billing_first_name,
				"last_name" 	=> $billing_last_name,
				"shipping_addresses" => [
					[
						"first_name" 	=> $billing_first_name,
						"last_name" 	=> $billing_last_name,
						"street1" 		=> $billing_address_1 ? $billing_address_1 : 'street1',
						"city" 			=> $billing_city ? $billing_city : 'Ney York City',
						"postal_code" 	=> $billing_postcode ? $billing_postcode : '10000',
						"country" 		=> $billing_country ? $billing_country : 'US',
						"street2" 		=> $billing_address_2 ? $billing_address_2 : 'street2',
						"region" 		=> $billing_state ? $billing_state : 'New York'
					]
				]
			];
		} else {
			$billing_first_name = isset( $_POST['billing_first_name'] ) ? filter_var( wp_unslash( $_POST['billing_first_name'] ), FILTER_SANITIZE_STRING ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$billing_last_name  = isset( $_POST['billing_last_name'] ) ? filter_var( wp_unslash( $_POST['billing_last_name'] ), FILTER_SANITIZE_STRING ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$billing_email  	= isset( $_POST['billing_email'] ) ? $_POST['billing_email'] : ''; // phpcs:ignore WordPress.Security.NonceVerification

			$defaults = [
				'code'		  	=> $billing_email,
				'email'       	=> $billing_email,
				"first_name" 	=> $billing_first_name,
				"last_name" 	=> $billing_last_name,
				"shipping_addresses" => [
					[
						"first_name" 	=> $billing_first_name,
						"last_name" 	=> $billing_last_name,
						"street1" 		=> $_POST['billing_address_1'],
						"city" 			=> $_POST['billing_city'],
						"postal_code" 	=> $_POST['billing_postcode'],
						"country" 		=> $_POST['billing_country'],
						"street2" 		=> $_POST['billing_address_2'],
						"region" 		=> $_POST['billing_state'],
						"phone"			=> $_POST['billing_phone']
					]
				]
			];
		}

		// $metadata                      = [];
		// $defaults['metadata']          = apply_filters( 'wc_recurly_customer_metadata', $metadata, $user );
		return wp_parse_args( $args, $defaults );
	}

	/**
	 * Create a customer via API.
	 *
	 * @param array $args
	 * @return WP_Error|int
	 */
	public function create_customer( $args = [] ) {
		$args     = $this->generate_customer_request( $args );
		$account_create = apply_filters( 'wc_recurly_create_customer_args', $args );
		if(is_user_logged_in()){
			$user_id = get_current_user_id();
			$account_code = get_user_meta( $user_id, '_recurly_account_code', true ) ? get_user_meta( $user_id, '_recurly_account_code', true ) : get_user_meta( $user_id, 'wp__recurly_account_code', true );
			if(!empty($account_code)){
				$response = WC_Recurly_API::request( [], 'accounts/' . 'code-'.$account_code, 'GET' );
				
			}else{
				$response = WC_Recurly_API::request( json_encode( $account_create ), 'accounts' );
				if ( ! empty( $response->error ) ) {
					if($response->error->message == 'Code has already been taken'){
						$account_code = $_POST['billing_email'];
						$response = WC_Recurly_API::request( [], 'accounts/' . 'code-'.$account_code, 'GET' );
						if ( empty( $response->error ) ) {
							$this->set_id( $response->id );
							$this->set_code( $response->code );
							$this->clear_cache();
							$this->set_customer_data( $response );

							if ( $this->get_user_id() ) {	
								$this->update_id_in_meta( $response->id );
								$this->update_code_in_meta( $response->code );
							}
							do_action( 'woocommerce_recurly_add_customer', $args, $response );
							return $response->id;
						}else{
							throw new WC_Recurly_Exception( print_r( $response, true ), $response->error->message);
						}
					}else{
						throw new WC_Recurly_Exception( print_r( $response, true ), $response->error->message );
					}
				}
			}
		}else{
			$response = WC_Recurly_API::request( json_encode( $account_create ), 'accounts' );
			if ( ! empty( $response->error ) ) {
				throw new WC_Recurly_Exception( print_r( $response, true ), $response->error->message );
			}
		}
		$this->set_id( $response->id );
		$this->set_code( $response->code );
		$this->clear_cache();
		$this->set_customer_data( $response );

		if ( $this->get_user_id() ) {	
			$this->update_id_in_meta( $response->id );
			$this->update_code_in_meta( $response->code );
		}

		do_action( 'woocommerce_recurly_add_customer', $args, $response );

		return $response->id;
	}

	/**
	 * Updates the Recurly customer through the API.
	 *
	 * @param array $args     Additional arguments for the request (optional).
	 * @param bool  $is_retry Whether the current call is a retry (optional, defaults to false). If true, then an exception will be thrown instead of further retries on error.
	 *
	 * @return string Customer ID
	 *
	 * @throws WC_Recurly_Exception
	 */
	public function update_customer( $args = [], $is_retry = false ) {
		if ( empty( $this->get_id() ) ) {
			throw new WC_Recurly_Exception( 'id_required_to_update_user', __( 'Attempting to update a Recurly customer without a customer ID.', 'woocommerce-gateway-recurly' ) );
		}

		$args     = $this->generate_customer_request( $args );
		$args     = apply_filters( 'wc_recurly_update_customer_args', $args );
		$response = WC_Recurly_API::request( json_encode( $args ), 'accounts/' . $this->get_id() );

		if ( ! empty( $response->error ) ) {
			if ( $this->is_no_such_customer_error( $response->error ) && ! $is_retry ) {
				// This can happen when switching the main Recurly account or importing users from another site.
				// If not already retrying, recreate the customer and then try updating it again.
				$this->recreate_customer();
				return $this->update_customer( $args, true );
			}

			throw new WC_Recurly_Exception( print_r( $response, true ), $response->error->message );
		}

		$this->clear_cache();
		$this->set_customer_data( $response );

		do_action( 'woocommerce_recurly_update_customer', $args, $response );

		return $this->get_id();
	}

	public function get_customer($customer_id) {
		if ( empty( $customer_id ) ) {
			throw new WC_Recurly_Exception( 'id_required_to_update_user', __( 'Attempting to get a Recurly customer without a customer ID.', 'woocommerce-gateway-recurly' ) );
		}
		// if( preg_match( '/^RC_/', $customer_id )){
		// 	$customer_id = 'code-'.$customer_id;
		// }

		if(is_user_logged_in())
		{
			$user_id = get_current_user_id();
			$_recurly_account_code = get_user_meta($user_id, '_recurly_account_code', true) ? get_user_meta($user_id, '_recurly_account_code', true) : get_user_meta($user_id, 'wp__recurly_account_code', true);
			if(!empty($_recurly_account_code))//wp__recurly_customer_id
			{
				$customer_id = 'code-'.$_recurly_account_code;
			}else{
				$customer_id = get_user_meta($user_id, '_recurly_customer_id', true) ? get_user_meta($user_id, '_recurly_customer_id', true) : get_user_meta($user_id, 'wp__recurly_customer_id', true);
			}
		}
		$response = WC_Recurly_API::request( [], 'accounts/' . $customer_id ,"GET" );
		if ( ! empty( $response->error ) ) {
			throw new WC_Recurly_Exception( print_r( $response, true ), $response->error->message );
		}

		return $response;
	}

	/**
	 * Checks to see if error is of invalid request
	 * error and it is no such customer.
	 *
	 * @since 4.1.2
	 * @param array $error
	 */
	public function is_no_such_customer_error( $error ) {
		return (
			$error &&
			'invalid_request_error' === $error->type &&
			preg_match( '/No such customer/i', $error->message )
		);
	}

	/**
	 * Checks to see if error is of invalid request
	 * error and it is no such customer.
	 *
	 * @since 4.5.6
	 * @param array $error
	 * @return bool
	 */
	public function is_source_already_attached_error( $error ) {
		return (
			$error &&
			'invalid_request_error' === $error->type &&
			preg_match( '/already been attached to a customer/i', $error->message )
		);
	}

	/**
	 * Add a source for this recurly customer.
	 *
	 * @param string $source_id
	 * @return WP_Error|int
	 */
	public function add_source( $source_id ) {
		$response = WC_Recurly_API::retrieve( 'sources/' . $source_id );

		if ( ! empty( $response->error ) || is_wp_error( $response ) ) {
			return $response;
		}

		// Add token to WooCommerce.
		$wc_token = false;

		if ( $this->get_user_id() && class_exists( 'WC_Payment_Token_CC' ) ) {
			if ( ! empty( $response->type ) ) {
				switch ( $response->type ) {
					case 'alipay':
						break;
					case 'sepa_debit':
						$wc_token = new WC_Payment_Token_SEPA();
						$wc_token->set_token( $response->id );
						$wc_token->set_gateway_id( 'recurly_sepa' );
						$wc_token->set_last4( $response->sepa_debit->last4 );
						break;
					default:
						if ( 'source' === $response->object && 'card' === $response->type ) {
							$wc_token = new WC_Payment_Token_CC();
							$wc_token->set_token( $response->id );
							$wc_token->set_gateway_id( 'recurly' );
							$wc_token->set_card_type( strtolower( $response->card->brand ) );
							$wc_token->set_last4( $response->card->last4 );
							$wc_token->set_expiry_month( $response->card->exp_month );
							$wc_token->set_expiry_year( $response->card->exp_year );
						}
						break;
				}
			} else {
				// Legacy.
				$wc_token = new WC_Payment_Token_CC();
				$wc_token->set_token( $response->id );
				$wc_token->set_gateway_id( 'recurly' );
				$wc_token->set_card_type( strtolower( $response->brand ) );
				$wc_token->set_last4( $response->last4 );
				$wc_token->set_expiry_month( $response->exp_month );
				$wc_token->set_expiry_year( $response->exp_year );
			}

			$wc_token->set_user_id( $this->get_user_id() );
			$wc_token->save();
		}

		$this->clear_cache();

		do_action( 'woocommerce_recurly_add_source', $this->get_id(), $wc_token, $response, $source_id );

		return $response->id;
	}

	/**
	 * Attaches a source to the Recurly customer.
	 *
	 * @param string $source_id The ID of the new source.
	 * @return object|WP_Error Either a source object, or a WP error.
	 */
	public function attach_source( $source_id ) {
		if ( ! $this->get_id() ) {
			$this->set_id( $this->create_customer() );
		}

		//https://v3.recurly.com/accounts/{account_id}/billing_info
		$response = WC_Recurly_API::request(json_encode(['token_id' => $source_id]),'accounts/' . $this->get_id() . '/billing_info', 'PUT');

		if ( ! empty( $response->error ) ) {
			// It is possible the WC user once was linked to a customer on Recurly
			// but no longer exists. Instead of failing, lets try to create a
			// new customer.
			if ( $this->is_no_such_customer_error( $response->error ) ) {
				$this->recreate_customer();
				return $this->attach_source( $source_id );
			} elseif ( $this->is_source_already_attached_error( $response->error ) ) {
				return WC_Recurly_API::request( [], 'sources/' . $source_id, 'GET' );
			} else {
				return $response;
			}
		} elseif ( empty( $response->id ) ) {
			return new WP_Error( 'error', __( 'Unable to add payment source.', 'woocommerce-gateway-recurly' ) );
		} else {
			return $response;
		}
	}

	/**
	 * Get a customers saved sources using their Recurly ID.
	 *
	 * @param  string $customer_id
	 * @return array
	 */
	public function get_sources() {
		if ( ! $this->get_id() ) {
			return [];
		}
		//https://v3.recurly.com/accounts/{account_id}/billing_info
		$sources = get_transient( 'recurly_sources_' . $this->get_id() );

		if ( false === $sources ) {
			$response = WC_Recurly_API::request(
				[
					//'limit' => 100,
				],
				'accounts/' . $this->get_id() . '/billing_info',
				'GET'
			);

			if ( ! empty( $response->error ) ) {
				return [];
			}

			if ( is_array( $response->data ) ) {
				$sources = $response->data;
			}

			set_transient( 'recurly_sources_' . $this->get_id(), $sources, DAY_IN_SECONDS );
		}

		return empty( $sources ) ? [] : $sources;
	}

	/**
	 * Delete a source from recurly.
	 *
	 * @param string $source_id
	 */
	public function delete_source( $source_id ) {
		if ( ! $this->get_id() ) {
			return false;
		}

		$response = WC_Recurly_API::request( [], 'customers/' . $this->get_id() . '/sources/' . sanitize_text_field( $source_id ), 'DELETE' );

		$this->clear_cache();

		if ( empty( $response->error ) ) {
			do_action( 'wc_recurly_delete_source', $this->get_id(), $response );

			return true;
		}

		return false;
	}

	/**
	 * Set default source in Recurly
	 *
	 * @param string $source_id
	 */
	public function set_default_source( $source_id ) {
		$response = WC_Recurly_API::request(
			[
				'default_source' => sanitize_text_field( $source_id ),
			],
			'customers/' . $this->get_id(),
			'POST'
		);

		$this->clear_cache();

		if ( empty( $response->error ) ) {
			do_action( 'wc_recurly_set_default_source', $this->get_id(), $response );

			return true;
		}

		return false;
	}

	/**
	 * Deletes caches for this users cards.
	 */
	public function clear_cache() {
		delete_transient( 'recurly_sources_' . $this->get_id() );
		delete_transient( 'recurly_customer_' . $this->get_id() );
		$this->customer_data = [];
	}

	/**
	 * Retrieves the Recurly Customer ID from the user meta.
	 *
	 * @param  int $user_id The ID of the WordPress user.
	 * @return string|bool  Either the Recurly ID or false.
	 */
	public function get_id_from_meta( $user_id ) {
		return get_user_option( '_recurly_customer_id', $user_id );
	}
	/**
	 * Retrieves the Recurly Customer CODE from the user meta.
	 *
	 * @param  int $user_id The CODE of the WordPress user.
	 * @return string|bool  Either the Recurly ID or false.
	 */
	public function get_code_from_meta( $user_id ) {
		return get_user_option( '_recurly_account_code', $user_id );
	}

	/**
	 * Updates the current user with the right Recurly ID in the meta table.
	 *
	 * @param string $id The Recurly customer ID.
	 */
	public function update_id_in_meta( $id ) {
		update_user_option( $this->get_user_id(), '_recurly_customer_id', $id, false );
	}

	/**
	 * Updates the current user with the right Recurly CODE in the meta table.
	 *
	 * @param string $id The Recurly customer CODE.
	 */
	public function update_code_in_meta( $id ) {
		update_user_option( $this->get_user_id(), '_recurly_account_code', $id, false );
	}

	/**
	 * Deletes the user ID from the meta table with the right key.
	 */
	public function delete_id_from_meta() {
		delete_user_option( $this->get_user_id(), '_recurly_customer_id', false );
	}

	/**
	 * Recreates the customer for this user.
	 *
	 * @return string ID of the new Customer object.
	 */
	private function recreate_customer() {
		$this->delete_id_from_meta();
		return $this->create_customer();
	}

	/**
	 * Get the customer's preferred locale based on the user or site setting.
	 *
	 * @param object $user The user being created/modified.
	 * @return array The matched locale string wrapped in an array, or empty default.
	 */
	public function get_customer_preferred_locale( $user ) {
		$locale = $this->get_customer_locale( $user );

		// Options based on Recurly locales.
		// https://support.recurly.com/questions/language-options-for-customer-emails
		$recurly_locales = [
			'ar'    => 'ar-AR',
			'da_DK' => 'da-DK',
			'de_DE' => 'de-DE',
			'en'    => 'en-US',
			'es_ES' => 'es-ES',
			'es_CL' => 'es-419',
			'es_AR' => 'es-419',
			'es_CO' => 'es-419',
			'es_PE' => 'es-419',
			'es_UY' => 'es-419',
			'es_PR' => 'es-419',
			'es_GT' => 'es-419',
			'es_EC' => 'es-419',
			'es_MX' => 'es-419',
			'es_VE' => 'es-419',
			'es_CR' => 'es-419',
			'fi'    => 'fi-FI',
			'fr_FR' => 'fr-FR',
			'he_IL' => 'he-IL',
			'it_IT' => 'it-IT',
			'ja'    => 'ja-JP',
			'nl_NL' => 'nl-NL',
			'nn_NO' => 'no-NO',
			'pt_BR' => 'pt-BR',
			'sv_SE' => 'sv-SE',
		];

		$preferred = isset( $recurly_locales[ $locale ] ) ? $recurly_locales[ $locale ] : 'en-US';
		return [ $preferred ];
	}

	/**
	 * Gets the customer's locale/language based on their setting or the site settings.
	 *
	 * @param object $user The user we're wanting to get the locale for.
	 * @return string The locale/language set in the user profile or the site itself.
	 */
	public function get_customer_locale( $user ) {
		// If we have a user, get their locale with a site fallback.
		return ( $user ) ? get_user_locale( $user->ID ) : get_locale();
	}
}
