<?php
/**
 * @package BuddyBoss Child
 * The parent theme functions are located at /buddyboss-theme/inc/theme/functions.php
 * Add your own functions at the bottom of this file.
 */


/****************************** THEME SETUP ******************************/

/**
 * Sets up theme for translation
 *
 * @since BuddyBoss Child 1.0.0
 */
function buddyboss_theme_child_languages()
{
  /**
   * Makes child theme available for translation.
   * Translations can be added into the /languages/ directory.
   */

  // Translate text from the PARENT theme.
  load_theme_textdomain( 'buddyboss-theme', get_stylesheet_directory() . '/languages' );

  // Translate text from the CHILD theme only.
  // Change 'buddyboss-theme' instances in all child theme files to 'buddyboss-theme-child'.
  // load_theme_textdomain( 'buddyboss-theme-child', get_stylesheet_directory() . '/languages' );

}
add_action( 'after_setup_theme', 'buddyboss_theme_child_languages' );

/**
 * Enqueues scripts and styles for child theme front-end.
 *
 * @since Boss Child Theme  1.0.0
 */
function buddyboss_theme_child_scripts_styles()
{
  /**
   * Scripts and Styles loaded by the parent theme can be unloaded if needed
   * using wp_deregister_script or wp_deregister_style.
   *
   * See the WordPress Codex for more information about those functions:
   * http://codex.wordpress.org/Function_Reference/wp_deregister_script
   * http://codex.wordpress.org/Function_Reference/wp_deregister_style
   **/

  // Styles
  wp_enqueue_style( 'buddyboss-child-css', get_stylesheet_directory_uri().'/assets/css/custom.css' );

  // Javascript
	wp_enqueue_script( 'buddyboss-child-js', get_stylesheet_directory_uri().'/assets/js/custom.js', array( 'jquery' ), time(), true );
	wp_add_inline_script( 'buddyboss-child-js', 'const MYSCRIPT = ' . json_encode( array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'is_user_logged_in' => is_user_logged_in(),
	) ), 'before' );
}
add_action( 'wp_enqueue_scripts', 'buddyboss_theme_child_scripts_styles', 9999 );


/****************************** CUSTOM FUNCTIONS ******************************/

// Add your own custom functions here
function remove_query_strings() {
   if(!is_admin()) {
       add_filter('script_loader_src', 'remove_query_strings_split', 15);
       add_filter('style_loader_src', 'remove_query_strings_split', 15);
   }
}

function remove_query_strings_split($src){
   $output = preg_split("/(&ver|\?ver)/", $src);
   return $output[0];
}
add_action('init', 'remove_query_strings');

/*******************************************/
/** SAVIOR DEVELOPMENT CUSTOM CODE **/

/*----------------------------------------------------------*/
/* Adds new header classes
/*----------------------------------------------------------*/
function set_dark_mode( $class = '' )
{	
	echo 'class="' . esc_attr( implode( ' ', get_html_class( $class ) ) ) . '"';
}
function get_html_class( $class = '' )
{
	$classes = array();
	if(isset($_COOKIE['darkModeEnabled']) && $_COOKIE['darkModeEnabled'] == 'true') {
		$classes[] = 'dark-mode-active';
	}elseif(get_post()->post_type === 'page' && !isset($_COOKIE['darkModeEnabled']) ) {
		if( get_post_meta(get_the_ID(), 'select_theme', true) == 'dark' ){
			$classes[] = 'dark-mode-active';
		}else{
			$classes[] = '';
		}
	}elseif (in_array('buddypress', get_body_class()) || in_array('single-sfwd-courses', get_body_class())) {
		$classes[] = 'dark-mode-active';
	}else{
		$classes[] = '';
	}
	if ( ! empty( $class ) ) {
		if ( ! is_array( $class ) ) {
			$class = preg_split( '#\s+#', $class );
		}
		$classes = array_merge( $classes, $class );
	} else {
		$class = array();
	}
	$classes = array_map( 'esc_attr', $classes );
	$classes = apply_filters( 'set_dark_mode', $classes, $class );

	return array_unique( $classes );
}

/*----------------------------------------------------------*/
/* Adds new body class
/*----------------------------------------------------------*/
add_filter( 'body_class', function( $classes ) {
	if(isset($_COOKIE['darkModeEnabled']) && $_COOKIE['darkModeEnabled'] == 'true') {
		return array_merge( $classes, array( 'bb-dark-theme' ) );
	}
	elseif ( (in_array('single-sfwd-topic', $classes) || in_array('single-sfwd-lessons', $classes) || in_array('single-sfwd-courses', $classes))) {
        return array_merge( $classes, array( 'bb-dark-theme' ) );
    }
	elseif(get_post()->post_type === 'page') {
		if( get_post_meta(get_the_ID(), 'select_theme', true) == 'dark' ){
			return array_merge( $classes, array( 'bb-dark-theme' ) );
		}
	}
	return $classes;
} );