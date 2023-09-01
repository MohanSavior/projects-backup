<?php
/**
 * WooCommerce Recurly Exception Class
 *
 * Extends Exception to provide additional data
 *
 * @since 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Recurly_Exception extends Exception {

	/**
	 * String sanitized/localized error message.
	 *
	 * @var string */
	protected $localized_message;

	/**
	 * Setup exception
	 *
	 * @since 1.0.1
	 * @param string $error_message Full response
	 * @param string $localized_message user-friendly translated error message
	 */
	public function __construct( $error_message = '', $localized_message = '' ) {
		$this->localized_message = $localized_message;
		parent::__construct( $error_message );
	}

	/**
	 * Returns the localized message.
	 *
	 * @since 1.0.1
	 * @return string
	 */
	public function getLocalizedMessage() {
		return $this->localized_message;
	}
}
