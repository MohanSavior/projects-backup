<?php
/**
 * Savior-Pro Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Savior-Pro
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_SAVIOR_PRO_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'savior-pro-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_SAVIOR_PRO_VERSION, 'all' );
	wp_enqueue_style( 'savior-pro-styles', get_stylesheet_directory_uri() . '/assets/css/savior-pro-styles.css', array(), time(), 'all' );
	wp_enqueue_style( 'savior-pro-responsive-styles', get_stylesheet_directory_uri() . '/assets/css/savior-pro-responsive-styles.css', array(), time(), 'all' );
	wp_enqueue_style( 'savior-pro-styles-3', get_stylesheet_directory_uri() . '/assets/css/savior-pro-styles-3.css', array(), time(), 'all' );
	wp_enqueue_script( 'savior-pro-scripts', get_stylesheet_directory_uri() . '/assets/js/savior-pro-scripts.js', array('jquery'), time(), true );

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

/**  Redirect to Checkout Skip Cart Page */
add_filter('add_to_cart_redirect', 'cw_redirect_add_to_cart');
function cw_redirect_add_to_cart() {
	global $woocommerce;
	$cw_redirect_url_checkout = $woocommerce->cart->get_checkout_url();
	return $cw_redirect_url_checkout;
}

/** Redirect to Thank you after Checkout **/
add_action('template_redirect', 'webtoffee_custom_redirect_after_purchase');
function webtoffee_custom_redirect_after_purchase() {
	global $wp;
	if (is_checkout() && !empty($wp->query_vars['order-received'])) {
		wp_redirect('/thank-you');
		exit;
	}
}

/** Restrict non logged users to certain pages **/
add_action('template_redirect','my_non_logged_redirect');
function my_non_logged_redirect()
{
	if ((is_page('my-account')) && !is_user_logged_in() )
	{
		wp_redirect( home_url('/login') );
		die();
	}
} 	

add_action( 'template_redirect', 'onlinearcflash_woocommerce_clear_cart' );
function onlinearcflash_woocommerce_clear_cart() {

	if ( is_front_page() || is_product()) { 
		WC()->cart->empty_cart(); 
	}
}


/*****************************************/



add_action( 'woocommerce_review_order_after_order_total', 'ts_review_order_after_order_total', 10, 2 );
function ts_review_order_after_order_total(){
	// 	global $woocommerce;
	$_product = wc_get_product( 451 );
	$regular_amt = $_product->get_price();
	$checkout_total_amt = WC()->cart->get_cart_contents_total();
	$product_qty = WC()->cart->get_cart_contents_count(); 
	$final_res = (( $regular_amt * $product_qty) - $checkout_total_amt );
	echo '<tr class="custom-saved"><th>You Saved </th><td colspan="1">$' . $final_res . '</td> </tr>';
}