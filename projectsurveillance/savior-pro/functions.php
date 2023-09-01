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
	wp_enqueue_script( 'savior-pro-scripts', get_stylesheet_directory_uri() . '/assets/js/savior-pro-scripts.js', array('jquery'), time(), true );

	/** mCustomScrollBar **/
	wp_enqueue_script( 'jquery-mCustomScrollbar-js', get_stylesheet_directory_uri() . '/scroll-bar/jquery.mCustomScrollbar.js', array('jquery'), '1.0.0', true );
	wp_enqueue_style( 'jquery-mCustomScrollbar-css', get_stylesheet_directory_uri() . '/scroll-bar/jquery.mCustomScrollbar.css', array(), '1.0.0', 'all' );
}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );
