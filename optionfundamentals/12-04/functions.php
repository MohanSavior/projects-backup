<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

$rdtheme_theme_data = wp_get_theme();
define( 'FINANCEPRO_VERSION', ( WP_DEBUG ) ? time() : $rdtheme_theme_data->get( 'Version' ) );
define( 'RDTHEME_AUTHOR_URI', $rdtheme_theme_data->get( 'AuthorURI' ) );

// DIR
define( 'RDTHEME_BASE_DIR',    get_template_directory(). '/' );
define( 'RDTHEME_INC_DIR',     RDTHEME_BASE_DIR . 'inc/' );
define( 'RDTHEME_VIEW_DIR',    RDTHEME_INC_DIR . 'views/' );
define( 'RDTHEME_LIB_DIR',     RDTHEME_BASE_DIR . 'lib/' );
define( 'RDTHEME_WID_DIR',     RDTHEME_INC_DIR . 'widgets/' );
define( 'RDTHEME_PLUGINS_DIR', RDTHEME_INC_DIR . 'plugins/' );
define( 'RDTHEME_ASSETS_DIR',  RDTHEME_BASE_DIR . 'assets/' );
define( 'RDTHEME_CSS_DIR',     RDTHEME_ASSETS_DIR . 'css/' );
define( 'RDTHEME_JS_DIR',      RDTHEME_ASSETS_DIR . 'js/' );

// URL
define( 'RDTHEME_BASE_URL',    get_template_directory_uri(). '/' );
define( 'RDTHEME_ASSETS_URL',  RDTHEME_BASE_URL . 'assets/' );
define( 'RDTHEME_CSS_URL',     RDTHEME_ASSETS_URL . 'css/' );
define( 'RDTHEME_JS_URL',      RDTHEME_ASSETS_URL . 'js/' );
define( 'RDTHEME_IMG_URL',     RDTHEME_ASSETS_URL . 'img/' );
define( 'RDTHEME_LIB_URL',     RDTHEME_BASE_URL . 'lib/' );

// Includes
require_once RDTHEME_INC_DIR . 'redux-config.php';
require_once RDTHEME_INC_DIR . 'rdtheme.php';
require_once RDTHEME_INC_DIR . 'helper-functions.php';
require_once RDTHEME_INC_DIR . 'general.php';
require_once RDTHEME_INC_DIR . 'scripts.php';
require_once RDTHEME_INC_DIR . 'template-vars.php';
require_once RDTHEME_INC_DIR . 'vc-settings.php';

// WooCommerce
if ( class_exists( 'WooCommerce' ) ) {
	require_once RDTHEME_INC_DIR . 'woo-functions.php';
	require_once RDTHEME_INC_DIR . 'woo-hooks.php';
}

// Widgets
require_once RDTHEME_WID_DIR . 'address-widget.php';
require_once RDTHEME_WID_DIR . 'social-widget.php';
require_once RDTHEME_WID_DIR . 'slider-widget.php';
require_once RDTHEME_WID_DIR . 'search-widget.php'; // override default

// TGM Plugin Activation
if ( is_admin() ) {
	require_once RDTHEME_LIB_DIR . 'class-tgm-plugin-activation.php';
	require_once RDTHEME_INC_DIR . 'tgm-config.php';
}
/**/
// function prevent_email_domain( $user_login, $user_email, $errors ) {
//     if ( strpos( $user_email, '@baddomain.com' ) != -1 ) {
//         $errors->add( 'bad_email_domain', '<strong>ERROR</strong>: This email domain is not allowed.' );
//     }
// }
// add_action( 'register_post', 'prevent_email_domain', 10, 3 );
function get_client_ip() {
	$ipaddress = '';
	if (isset($_SERVER['HTTP_CLIENT_IP']))
		$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else if(isset($_SERVER['HTTP_X_FORWARDED']))
		$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
		$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	else if(isset($_SERVER['HTTP_FORWARDED']))
		$ipaddress = $_SERVER['HTTP_FORWARDED'];
	else if(isset($_SERVER['REMOTE_ADDR']))
		$ipaddress = $_SERVER['REMOTE_ADDR'];
	else
		$ipaddress = 'UNKNOWN';
	return $ipaddress;
}
/*Woocommerce new customer created*/
function action_woocommerce_created_customer( $customer_id, $new_customer_data, $password_generated ) {
	update_user_meta($customer_id, 'user_ip_address', get_client_ip());
};
add_action( 'woocommerce_created_customer', 'action_woocommerce_created_customer', 10, 3 );

function save_new_user_ip_address($user_id){
	update_user_meta($user_id, 'user_ip_address', get_client_ip());
}
add_action('user_register','save_new_user_ip_address', 10, 1);

/*Existing user ip address update*/
function existing_user_update_ip_address( $user_login, $user ) {
	$user_ip_address = get_user_meta($user->ID, 'user_ip_address', true);
	if(!$user_ip_address){
		update_user_meta($user->ID, 'user_ip_address', get_client_ip());
	}
}
add_action('wp_login', 'existing_user_update_ip_address', 99, 2);

/**
 * Check current product is free
 */
function current_add_to_product_is_free(){
	$anyuser = false;
	foreach ( WC()->cart->get_cart() as $cart_item ) {
		$item_id = $cart_item['data']->get_id(); // item id free  42488
		if($item_id === 42488){
			$anyuser = true;
			break;
		}else{
			$anyuser = false;
		}
	}
	return $anyuser;
}
/**
 * Process the checkout
 */
// add_action( 'woocommerce_after_checkout_validation', 'optionfundamentals_check_user_ip', 10, 2 );

function optionfundamentals_check_user_ip( $data, $errors ){
	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();
		$user_ip_address = get_user_meta($user_id, 'user_ip_address', true);
		$billing_phone = get_user_meta( $user_id, 'billing_phone', true );

		if(!empty($user_ip_address)){
			if($user_ip_address === get_client_ip() && current_add_to_product_is_free() === true){
				$errors->add( 'validation', 'Your current IP Address has already registered with this Product.');
			}
		}
	}else{

		$user_ip_address_args = array(
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key' => 'user_ip_address',
					'value' => get_client_ip(),
					'compare' => '='
				),
				array(
					'key' => 'billing_phone',
					'value' => $_POST['billing_phone'],
					'compare' => '='
				)
			)
		);
		$user_ip_address_query = new WP_User_Query( $user_ip_address_args );

		if( !empty( $user_ip_address_query->get_results() )  && current_add_to_product_is_free() === true){
			$errors->add( 'validation', __( '<strong>Error</strong>: Your current IP Address has already registered with this Product.', 'woocommerce' ) );
			$errors->add( 'billing_phone_error', __( '<strong>Error</strong>: This mobile number is already registered.', 'woocommerce' ) );
		}
	}
}

/*Show user registerd IP Address on profile page*/
add_action( 'show_user_profile', 'show_registerd_user_ip_address' );
add_action( 'edit_user_profile', 'show_registerd_user_ip_address' );
function show_registerd_user_ip_address( $user ) { ?>
<h3>User IP Address</h3>
<table class="form-table">
	<tr>
		<th><label for="twitter">Registerd IP Address</label></th>
		<td>
			<?php
	$user_ip = get_user_meta($user->ID, 'user_ip_address', true);
	if($user_ip){
		echo esc_attr($user_ip);
	}else{
		echo 'This user has no IP Address registered yet.';
	}
			?>
		</td>
	</tr>
</table>
<?php }

/*Remove fields in checkout page and Address in Account pages*/

add_filter( 'woocommerce_checkout_fields' , 'savior_custom_override_checkout_fields' );
function savior_custom_override_checkout_fields( $fields ) {
	foreach ( WC()->cart->get_cart() as $cart_item ) {
		$item_id = $cart_item['data']->get_id(); // item id free  42488
		if($item_id === 42488){
			// unset($fields['billing']['billing_country']);
			unset($fields['shipping']['shipping_country']);
			//unset($fields['billing']['billing_company']);
			unset($fields['shipping']['shipping_company']);
			//unset($fields['billing']['billing_address_1']);
			unset($fields['shipping']['shipping_address_1']);
			//unset($fields['billing']['billing_address_2']);
			unset($fields['shipping']['shipping_address_2']);
			//unset($fields['billing']['billing_state']);
			unset($fields['shipping']['shipping_state']);
			//unset($fields['billing']['billing_city']);
			unset($fields['shipping']['shipping_city']);
			//unset($fields['billing']['billing_postcode']);
			unset($fields['shipping']['shipping_postcode']);
			unset($fields['shipping']['shipping_email']);
			unset($fields['order']['order_comments']);
			add_filter( 'woocommerce_enable_order_notes_field', '__return_false', 9999 );
			break;
		}
	}
	return $fields;
}

/** Send Customer Data to mobile-text-alerts.com **/
add_action( 'woocommerce_thankyou', 'optionfundamentals_mobile_text_alerts_save', 10, 1);
function optionfundamentals_mobile_text_alerts_save($order_id) {

	$order = wc_get_order($order_id);
	$user_id =  $order->get_customer_id();

	$url = 'https://mobile-text-alerts.com/rest/';
	$body_param = array(
		'key' => 'a2cf82ce105d0ee63b0341ef05c20d', 
		'request' => 'add_member'
	);
	if(!empty(get_user_meta($user_id, 'billing_phone'))){
		$body_param['number'] = get_user_meta($user_id, 'billing_phone', true);
	}
	if(!empty(get_user_meta($user_id, 'billing_first_name'))){
		$body_param['first_name'] = get_user_meta($user_id, 'billing_first_name', true);
	}
	if(!empty(get_user_meta($user_id, 'billing_last_name'))){
		$body_param['last_name'] = get_user_meta($user_id, 'billing_last_name', true);
	}
	if(!empty(get_user_meta($user_id, 'billing_email'))){
		$body_param['email'] = get_user_meta($user_id, 'billing_email', true);
	}
	if(!empty(get_user_meta($user_id, 'billing_email')) && !empty(get_user_meta($user_id, 'billing_phone')) && $user_id && $order_id){
		$response = wp_remote_post( $url, array(
				'method'      => 'POST',
				'blocking'    => true,
				'headers'     => array(),
				'body'        => $body_param,
			)
		);
		$body_param['key'] = '0c5b0b637d847b88dc9efd5f4da447';
		$response = wp_remote_post( $url, array(
				'method'      => 'POST',
				'blocking'    => true,
				'headers'     => array(),
				'body'        => $body_param,
			)
		);
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			//echo "Something went wrong: $error_message";
		}
	}

	/** Chnage Next Payment Date on free trial subscriptions **/
	$subscriptions_ids = wcs_get_subscriptions_for_order( $order_id );

}

/** Register WordPress CRON **/
add_filter( 'cron_schedules', 'optionfundamentals_add_cron_interval' );
function optionfundamentals_add_cron_interval( $schedules ) {
	$schedules['everyminute'] = array(
		'interval'  => 60, // time in seconds
		'display'   => 'Every Minute'
	);
	return $schedules;
}
if ( ! wp_next_scheduled( 'optionfundamentals_expire_subs' ) ) {
	wp_schedule_event( time(), 'everyminute', 'optionfundamentals_expire_subs' );
}
//add_shortcode('test_shortcode', 'test_shortcode_fn');
add_action('optionfundamentals_expire_subs', 'optionfundamentals_expire_subs_func');
function optionfundamentals_expire_subs_func(){
	$subscriptions = wcs_get_subscriptions(['subscriptions_per_page' => -1]);
	foreach ( $subscriptions as $subscription ) {
		$data = $subscription->get_data();
		if($data['status'] == 'active'){
			foreach( $subscription->get_items() as $item_id => $item ){
				if($item->get_product_id() == 42488){
					//echo $item->get_product_id(). '<br>';
					$order = wc_get_order( $item->get_order_id() );
					//print_r($order->order->customer_id);
					if(!wc_memberships_is_user_active_member($order->order->get_customer_id(), '10-days-free-trial')){
						WC_Subscriptions_Manager::expire_subscriptions_for_order( $order->order );
					}
				}
			}
		}
	}
}

// // define the woocommerce_after_order_notes callback
function action_woocommerce_after_order_notes( $wccs_custom_checkout_field_pro ) {
	echo '<h3>Billing Information Tips</h3>

<p>Please enter your information exactly as your credit card provider has your information on file, this can
Be verified by going online and simply click your Profile with your credit card issuer. If you receive an
error message after our checkout process, it is usually due to a Mismatch of information between what
you entered and what your credit card issuer has on file, this is the most common error among our
subscribers.</p>

<p>The most common errors are:</p>
<ol>
<li>Abbreviating PL for Place or Dr for Drive</li>
<li>The City could be Abbreviated St for Saint</li>
<li>Possibly using a shorter name like Tom instead of Thomas or Andy instead of Andrew</li>
<li>If your credit card issuer uses a Middle initial in your Profile please enter it in the middle box of
your name in the checkout process.</li>
<li>Our 3rd" party card processor Authorize.Net allows 3 attempts in a 24 hour time period</li>
</ol>

<p><a href="#">(NOTE: If None of the Above resolve your issues you can call Authorize.Net direct and they can usually
get your payment accepted 1-877-447-3938)</a></p>

<p>If you receive an error message from the credit card processor or pending status of payment that means
your credit card has not been charged and the above situation probably has occurred. Please take your
time and enter your information correctly and an error message will probably never occur.</p>

<p>We can always be reached at <a href="mailto:support@optionfundamentals.com">support@optionfundamentals.com</a> if you have any problems.</p>';
};


add_filter( "wc_memberships_members_area_my-memberships_actions", function($default_actions){
	$default_actions['view']['name'] =  __( 'VIEW ALERTS', 'woocommerce-memberships' );
	$default_actions['view-subscription']['name'] =  __( 'BILLING', 'woocommerce-memberships' );
	return $default_actions;
} );

/**
 * Auto login after registration.
 */
add_action( 'gform_user_registered', 'ofw_gravity_registration_autologin',  10, 4 );
function ofw_gravity_registration_autologin( $user_id, $user_config, $entry, $password ) {
	$user = get_userdata( $user_id );
	$user_login = $user->user_login;
	$user_password = $password;
	$user->set_role(get_option('default_role', 'customer'));

	wp_signon( array(
		'user_login' => $user_login,
		'user_password' =>  $user_password,
		'remember' => false

	) );
}
add_action( 'gform_pre_submission_1', 'pre_submission_handler' );
function pre_submission_handler( $form ) {
	$password = uniqid();
	$_POST['input_5'] = $password;
}
/* Password Strength Meter */
add_action('wp_enqueue_scripts', 'password_js_assets');
function password_js_assets(){
	wp_enqueue_script( 'password-strength-meter' );
	wp_enqueue_script( 'password-strength-meter-mediator', get_stylesheet_directory_uri() . '/assets/js/password-strength-meter-mediator.js', array('password-strength-meter'), time());
}

/* Password Strength Meter */
/* Add Custom Text on Forums Page */
//add_action ('bbp_template_before_forums_index' , 'bbpress_of_my_intro' );
function bbpress_of_my_intro () {
	echo "<div class='entry-content-image-ad'>
	<div class='verbiage_text_container'>
<p style='text-align: left' class='vc_custom_heading'>With Tradier Brokerage, you get unlimited commission-free* trading, access to a simple, yet powerful dashboard, fully customizable API access and so much more at your fingertips. Did we mention its only $10 per month? Yeah, $10 a month AND we don't take commissions like the other guys. So what's the hold up? Get access to everything Tradier Brokerage has to offer today! </p>
</div><p><a href='https://try.tradier.com/optionfundamentals/'><img src='https://www.optionfundamentals.com/wp-content/uploads/2021/09/TrdBrok_728x90_3months.png'></a></p></div>";
}
add_action ('bbp_template_before_forums_loop' , 'bbpress_of_my_intro_shortcode' );
function bbpress_of_my_intro_shortcode () {
	echo '<div class="horizontal-ads-space">'.do_shortcode('[ad_images_slider type="horizontal" ad_places="forums-only"]').'</div>';
}
//add_action( 'woocommerce_product_meta_end','add_meta_text_product_single' );
function add_meta_text_product_single(){
	echo '<h4 style="margin-top:15px;">RECEIVE OUR OPTION ALERTS FOR FREE TRIAL</h4>
<p class="product-description">Start trading options TODAY with our 100% free, no obligation option trade alerts. If you have subscribed to a stock alerts or option alerts service in the past, we think you will find we have an unmatched performance history. Option Fundamentals will exceed your expectations.</p>';
}


/*
 * Allow WooCommerce existing customers to checkout without being logged in (allow orders from existing customers in WooCommerce without logging in)
 */

function optionfundamentals_check_usser_caps( $allcaps, $caps, $args ) {
	if ( isset( $caps[0] ) ) {
		switch ( $caps[0] ) {
			case 'pay_for_order' :
				$order_id = isset( $args[2] ) ? $args[2] : null;
				$order = wc_get_order( $order_id );
				$user = $order->get_user();
				$user_id = $user->ID;
				if ( ! $order_id ) {
					$allcaps['pay_for_order'] = true;
					break;
				}

				$order = wc_get_order( $order_id );

				if ( $order && ( $user_id == $order->get_user_id() || ! $order->get_user_id() ) ) {
					$allcaps['pay_for_order'] = true;
				}
				break;
		}
	}
	return $allcaps;
}
//add_filter( 'user_has_cap', 'optionfundamentals_check_usser_caps', 10, 3 );

//add_filter( 'woocommerce_checkout_posted_data', 'optionfundamentals_filter_checkout_posted_data', 10, 1 );
function optionfundamentals_filter_checkout_posted_data( $data ) {
	$email = $data['billing_email'];
	$pass = $data['account_password'];
	if ( is_user_logged_in() ) {
	} else {
		if (email_exists( $email)){
			$user = get_user_by( 'email', $email );
			if ($user){
				$user_id = $user->ID;
				wc_set_customer_auth_cookie($user_id);
				session_start();
				$_SESSION['p33'] = "133";
				$_SESSION['u'] = $user_id;

				$user_id = $user->ID;
				if ( $user && wp_check_password( $pass, $user->data->user_pass, $user->ID ) ) {
					// Password is same or empty
					error_log('Password is same or empty '. print_r($data['account_password'], TRUE));
				} else {
					// Passowrd is changed
					error_log('Passowrd is changed '. print_r($data['account_password'], TRUE));
					wp_set_password( $pass, $user_id );
					$to = $email;
					$subject = 'Your Account Password has been updated.';
					$body = '
						<p>Your password for account email '.$email.' has been updated by you.</p>
						<p>The new password is '.$pass.'.</p>
					';
					$headers = array('Content-Type: text/html; charset=UTF-8');

					wp_mail( $to, $subject, $body, $headers );
				}

			} else {
				$user_id = false;
			}
		}
	}
	return $data;
}

//End Allow Woocommerce Order Pay Without LogIn
/* Shortcode to add Slider of Ads with Horizontal and Verticle ATTR*/
add_action('wp_enqueue_scripts', 'slick_asstes');
function slick_asstes(){
	wp_enqueue_script('slick-js','https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js',array('jquery'),'1.8.1');
	wp_enqueue_style('slick-css','https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css','1.8.1');
}

function ads_slider_with_all_images($attr){
	//[ad_images_slider type="horizontal" ad_places="homepage"]
	ob_start();
	$args = array(
		'post_type'  => 'advertisments',
	);
	$postslists = get_posts( $args );
	if(isset($attr['ad_places']) && $attr['ad_places'] != ''){
		$ad_places = $attr['ad_places'];
		$searchForValue = ',';
		if(strpos($ad_places, $searchForValue) !== false ) {
			$ad_places = explode (',', $ad_places);
		}else {
			$ad_places = $attr['ad_places'];
		}
	}else{
		$ad_places = '';

	}
?>
<div class="slick-carousel my-slick-carousel-wrapper">
	<?php
	foreach($postslists as $postslist){
		$post_ad_places = get_field('page_selection_for_this_ad', $postslist->ID);
		if(is_array($ad_places)){
			//print_r($ad_places);
			//print_r($post_ad_places);
			$post_ad_places_found = array_intersect($ad_places, $post_ad_places);
			//print_r(count($post_ad_places_found));
			if (!$post_ad_places_found || count($post_ad_places_found) == 0) {
				//echo 'yes';
				$found  = false;
			}else{
				$found  = true;
			}
		}else{
			if(in_array($ad_places, $post_ad_places)){
				$found  = true;
			}else{
				$found  = false;
			}
		}
		/*if($ad_places){
			$found  = true;
		}else{
			$found  = false;
		}*/
		if($found == true) :
		//print_r($found);
		if(isset($attr['type']) && $attr['type'] == 'horizontal'){
	?>
	<div class="container-div horizontal-image-size">
		<a href="<?php the_field('ad_url', $postslist->ID);?>"><img src="<?php the_field('ad_image__horizontal', $postslist->ID);?>"></a>
	</div>
	<?php }elseif(isset($attr['type']) && $attr['type'] == 'vertical'){?>
	<div class="container-div vertical-image-size">
		<a href="<?php the_field('ad_url', $postslist->ID);?>"><img src="<?php the_field('ad_image__vertical', $postslist->ID);?>"></a>
	</div>
	<?php }else{?>
	<div class="container-div vertical-image-size">
		<a href="<?php the_field('ad_url', $postslist->ID);?>"><img src="<?php the_field('ad_image__vertical', $postslist->ID);?>"></a>
	</div>
	<?php  }
		endif;
	}
	wp_reset_postdata();
	?>
</div>
<script>
	jQuery(document).ready(function(){
		jQuery('.slick-carousel').slick({
			infinite: false,
			slidesToShow: 1, // Shows a three slides at a time
			slidesToScroll: 1, // When you click an arrow, it scrolls 1 slide at a time
			speed: 500,
			autoplay: true,
			autoplaySpeed: 30000,
		});
	});
</script>
<?php
	$out = ob_get_contents();
	ob_end_clean();
	return $out;

	//print_r($postslist);
}
add_shortcode('ad_images_slider','ads_slider_with_all_images');
/* Shortcode to add Slider of Ads with Horizontal and Verticle ATTR*/
/* Shortcode to show Ads Archive on a page */
function show_all_ads_as_archive(){
	ob_start();
?>
<div class="main-list-column">
	<?php
	$args = array(
		'post_type'  => 'advertisments',
	);
	$postslists = get_posts( $args );
	foreach($postslists as $postslist){
	?>
	<div class="main-container-image">
		<a href="<?php the_field('ad_url', $postslist->ID);?>"><img src="<?php the_field('ad_image__vertical', $postslist->ID);?>"></a>
	</div>
	<?php
	}
	?>
</div>
<?php
	$out = ob_get_contents();
	ob_end_clean();
	return $out;
}
add_shortcode('ads-archive','show_all_ads_as_archive');
/* Shortcode to show Ads Archive on a page */
/*Login Form Above Ads */
//add_action( 'woocommerce_before_customer_login_form', 'ads_login_form' );
function ads_login_form() {
	if( ! is_user_logged_in() ){
		echo do_shortcode('[ad_images_slider type="horizontal"]');
	}
}
/* Login Form Above Ads */
/*Login Form below Ads */
//add_action( 'woocommerce_after_customer_login_form', 'ads_login_form_bottom' );
function ads_login_form_bottom() {
	if( ! is_user_logged_in() ){
		echo '<div class="login-form-ads">'.do_shortcode('[ad_images_slider type="vertical"]').'</div>';
	}
}
/* Login Form below Ads */


//add_filter( 'sliced_payment_option_fields', 'optionfundamentals_add_options_fields' );
function optionfundamentals_add_options_fields($options){
	$options['fields'][] = array(
		'name'          => __( 'Enable', 'sliced-invoices-woo-checkout' ),
		'desc'          => '',
		'type'          => 'checkbox',
		'id'            => 'woo_checkout_enabled',
		'before_row'    => '_optionfundamentals_settings_group_before',
		'after_row' => '_optionfundamentals_settings_group_after',
	);
	$options['fields'][] = array(

	);
	return $options;
}
function _optionfundamentals_settings_group_before(){ ?>
<table class="widefat" id="sliced-authorize-net-settings-wrapper">
	<tr id="sliced-authorize-net-settings-header">
		<th class="row-title"><h4><?php _e( 'WooCommerce Checkout', 'sliced-invoices-woo-checkout' ); ?></h4></th>
		<th class="row-toggle"><span class="dashicons dashicons-arrow-down" id="sliced-authorize-net-settings-toggle"></span></th>
	</tr>
	<tr id="sliced-authorize-net-settings">
		<td colspan="2">
			<?php
													}
function _optionfundamentals_settings_group_after(){
			?>
		</td>
	</tr>
</table>
<?php
}
add_filter( 'sliced_get_accepted_payment_methods', 'optionfundamentals_add_payment_method'  );
function optionfundamentals_add_payment_method( $pay_array ) {
	$payments = get_option( 'sliced_payments' );
	if ( ! empty( $payments['woo_checkout_enabled'] ) ) {
		$pay_array['woo_checkout'] = 'Woo Checkout';
	}
	return $pay_array;
}
add_action('sliced_do_payment', 'optionfundamentals_woo_checkout_payment_form');
function optionfundamentals_woo_checkout_payment_form() {

	if ( empty( $_POST ) ) {
		return;
	}

	// have we POSTED from the invoice page?
	if ( ! isset( $_POST['start-payment'] ) ) {
		return;
	}

	// is it for this payment gateway?
	if ( $_POST['sliced_gateway'] != 'woo_checkout' ) {
		return;
	}

	$id = intval( $_POST['sliced_payment_invoice_id'] );

	// check the nonce
	if( ! isset( $_POST['sliced_payment_nonce'] ) || ! wp_verify_nonce( $_POST['sliced_payment_nonce'], 'sliced_invoices_payment' ) ) {
		sliced_print_message( $id, __( 'There was an error with the form submission, please try again.', 'sliced-invoices-authorize-net' ), 'error' );
		return;
	}

	$woo_product_id  = get_field('woocommerce_product', $id);
	if(!empty($woo_product_id)){
		$checkout_page_url = wc_get_checkout_url();
		$checkout_page_url.= '?add-to-cart='.$woo_product_id;
		//echo $checkout_page_url;
		sliced_print_message( $id, __( 'Redirecting to Checkout Page...', 'sliced-invoices-authorize-net' ), 'success' );
?>
<script>
	setTimeout(function(){
		window.location.replace("<?php echo esc_url($checkout_page_url); ?>");
	},2000);
</script>
<?php
	}else{
		sliced_print_message( $id, __( 'There was an error with the form submission, please contact site administrator.', 'sliced-invoices-authorize-net' ), 'error' );
		return;
	}

}
// Advertise With Us Section Checkout Redirection
add_action( 'woocommerce_init', 'optionfundamentals_force_non_logged_user_wc_session' );
function optionfundamentals_force_non_logged_user_wc_session(){
	if( is_user_logged_in() || is_admin() )
		return;

	if ( isset(WC()->session) && ! WC()->session->has_session() )
		WC()->session->set_customer_session_cookie( true );
}

add_filter( 'gform_confirmation', 'checkout_confirmation_advertise_with_us', 10, 4 );
function checkout_confirmation_advertise_with_us( $confirmation, $form, $entry, $ajax ) {
	if( $form['id'] == '4' ) {
		//print_r($entry);
		$field_id = 15; // Update this number to your field id number
		$field = RGFormsModel::get_field( $form, $field_id );
		$value = is_object( $field ) ? $field->get_value_export( $entry ) : '';
		$array_product_names = explode(",", $value);
		//$value = str_replace(" ","", $value);
		//print_r($array_product_names);
		$product_ids = [];
		foreach($array_product_names as $array_product_name){
			$array_product_name = trim($array_product_name);
			//var_dump($array_product_name);
			if($array_product_name == 'homepage'){
				WC()->cart->add_to_cart( 37602 );
				array_push($product_ids , '37602');
			}

			if($array_product_name == 'members-login'){
				WC()->cart->add_to_cart( 37603 );
				array_push($product_ids , '37603');
			}

			if($array_product_name == 'forums-only'){
				WC()->cart->add_to_cart( 37604 );
				array_push($product_ids , '37604');
			}

			if($array_product_name == 'auto-trade-only'){
				WC()->cart->add_to_cart( 37605 );
				array_push($product_ids , '37605');
			}

			if($array_product_name == 'performance'){
				WC()->cart->add_to_cart( 37606 );
				array_push($product_ids , '37606');
			}

			if($array_product_name == 'testimonial-only'){
				WC()->cart->add_to_cart( 37607 );
				array_push($product_ids , '37607');
			}

			if($array_product_name == 'news-only'){
				WC()->cart->add_to_cart( 37608 );
				array_push($product_ids , '37608');
			}

			if($array_product_name == 'bundle-brokers'){
				WC()->cart->add_to_cart( 37609 );
				array_push($product_ids , '37609');
			}

			if($array_product_name == 'premierpass-addon'){
				WC()->cart->add_to_cart( 376012 );
				array_push($product_ids , '376012');
			}
		}


		//$product_ids = implode('|', $product_ids);

		//print_r(wc_get_checkout_url().'?add-to-cart='.$product_ids[0]);
		$confirmation = array( 'redirect' => wc_get_checkout_url() );
	}
	return $confirmation;
}
/**
 * ----- Add comment AFTER submit button on ALL Gravity Forms forms.
 * @link https://docs.gravityforms.com/gform_submit_button/#2-add-text-after-the-button-
 */

// Add consent statment after Submit button
//add_filter( 'gform_submit_button_4', 'add_paragraph_below_submit', 10, 2 );
function add_paragraph_below_submit( $button, $form ) {
	if( $form['id'] == '4' ) {
		return $button .= "<div class='more_options_link'><a href=\"https://www.optionfundamentals.com/request-for-information/\" target=\"_blank\">MORE OPTIONS</a></div>";
	}

}
/**
 ** Subscription Time Period Option Func Start
 **/

add_filter('woocommerce_subscription_period_interval_strings', function($intervals){
	$intervals = array( 1 => _x( 'every', 'period interval (eg "$10 _every_ 2 weeks")', 'woocommerce-subscriptions' ) );

	foreach ( range( 2, 50 ) as $i ) {
		// translators: period interval, placeholder is ordinal (eg "$10 every _2nd/3rd/4th_", etc)
		$intervals[ $i ] = sprintf( _x( 'every %s', 'period interval with ordinal number (e.g. "every 2nd"', 'woocommerce-subscriptions' ), WC_Subscriptions::append_numeral_suffix( $i ) );
	}

	// 	$intervals = apply_filters( 'woocommerce_subscription_period_interval_strings', $intervals );

	if ( empty( $interval ) ) {
		return $intervals;
	} else {
		return $intervals[ $interval ];
	}
});
/**
 * Notify admin when a new customer account is created
 */
add_action( 'woocommerce_created_customer', 'woocommerce_created_customer_admin_notification' );
function woocommerce_created_customer_admin_notification( $customer_id ) {
	wp_send_new_user_notifications( $customer_id, 'admin' );
}
/**
 * ADD g-recaptcha ON CHECKOUT PAGE
 */
add_action( 'woocommerce_before_checkout_shipping_form', function() {
	wp_nonce_field('ajax-register-nonce', 'signonsecurity');
	echo '<div class="g-recaptcha" data-sitekey="6Lf7a-oUAAAAAA3GKhe3W9zrqai8dSbtiSqYc44H"></div>';
});

/**
 * Disable Payment Method for Specific Product
*/


add_filter( 'woocommerce_available_payment_gateways', 'option_unset_gateway' );
function option_unset_gateway( $available_gateways ) {
	if ( is_admin() ) return $available_gateways;
	if ( ! is_checkout() ) return $available_gateways;
	$unset = false;
	foreach ( WC()->cart->get_cart_contents() as $key => $values ) {
		if($values['product_id'] == 42488 || $values['product_id'] == 45256){
			$unset = true;
			break;
		}
	}
	if ( $unset == true ) unset( $available_gateways['authnet'] );
	return $available_gateways;
}
/***********************************************/
add_action('wp_ajax_mobile_text_alerts_on_mobile_blur', 'mobile_text_alerts_on_mobile_blur');
add_action('wp_ajax_nopriv_mobile_text_alerts_on_mobile_blur', 'mobile_text_alerts_on_mobile_blur');
function mobile_text_alerts_on_mobile_blur() {
	$url = 'https://api.mobile-text-alerts.com/v3/subscribers';
	$body_param = array();
	$body_param['number'] 		= !empty( $_POST['billing_phone'] ) ? $_POST['billing_phone'] : '';
	$body_param['firstName'] 	= !empty( $_POST['billing_first_name'] ) ? $_POST['billing_first_name'] : '';
	$body_param['lastName'] 	= !empty( $_POST['billing_last_name'] ) ? $_POST['billing_last_name'] : '';
	$body_param['email'] 		= !empty( $_POST['billing_email'] ) ? $_POST['billing_email'] : '';
	if( $body_param['number'] && $body_param['firstName'] && $body_param['lastName'] && $body_param['email'] ){
		$response = wp_remote_post( $url, array(
				'method'      => 'POST',
				'blocking'    => true,
				'headers'     => array(
					'Authorization' => 'Bearer 0c5b0b637d847b88dc9efd5f4da447',
					'Content-Type'  => 'application/json'
				),
				'body'        => json_encode( $body_param ),
			)
		);
		$resData = json_decode($response['body']);
		if ( is_wp_error( $response ) || !empty($resData->error) ) {
			$error_message = !empty($resData->error) ? $resData->error :  $response->get_error_message();
			wp_send_json_error(['error_message' => $error_message]);
		}else{
			wp_send_json_success($response);
		}
	}else{
		wp_send_json_error(['error_message' => 'Please fill in all required fields']);
	}
}
/*
 * 
 */
add_action('wp_footer','referralLink_copy');
function referralLink_copy()
{
?>
<style>
	.page-id-3463 .woocommerce .woocommerce-MyAccount-content {
		width: 49%;
		display: inline-block;
	}
	.page-id-3463 .woocommerce .link-generator {
		width: 50%;
		display: inline-block;
	}
	.page-id-3463 .woocommerce .woocommerce-MyAccount-content #referralLink {
		width: 80% !important;
	}
	.page-id-3463 .woocommerce .link-generator .form-row input {
		width: 80% !important;
	}
	.page-id-3463 .woocommerce .copy-trigger {
		color: #cb1011;
		background-color: #cb011b;
		border: medium none;
		color: #fff;
		padding: 3px 15px;
	}
</style>
<script>
	if(jQuery('body').hasClass('page-id-3463') && jQuery('body').hasClass('logged-in')){
		// jQuery(window).load(functi=n(){
		if(jQuery('#referralLink').length > 0){
			console.log(jQuery('#referralLink'));
			let referralID = jQuery('#referralLink').val().split("?");
			console.log(referralID[1]);
			jQuery('#referralLink').val(`https://www.optionfundamentals.com/product/15-days-free-trial/?${referralID[1]}`);


			// });
			jQuery('#post-3463 .woocommerce').append(`
<div class="link-generator" style="position: static;">
<h4>Generate a custom URL:</h4>
<form method="post">
<p class="form-row">
<label for="original_url">Page URL</label>
<input type="url" name="original_url" class="origin-url" value="" id="original_url">
	</p>
<p class="form-row">
<label for="generated_url">Referral URL</label>
<span class="copy-field-wrapper">
<input type="url" name="generated_url" id="generated_url" class="copy-target generated-url" value="https://www.optionfundamentals.com/product/15-days-free-trial/?${referralID[1]}" readonly="">
<a href="javascript:void(0);" class="copy-trigger" onclick="CopyReferralLinkFunction()">
Copy URL </a>
	</span>
	</p>
	</form>
	</div>
`);
			jQuery('#original_url').on('change', function(){
				let newLink = jQuery(this).val();
				const last = newLink.charAt(newLink.length - 1);
				newLink = (last == '/') ? newLink : newLink+'/'
				jQuery('#generated_url').val(`${newLink}?${referralID[1]}`);
			});
			function CopyReferralLinkFunction() {
				var copyText = document.getElementById("generated_url");
				copyText.select();
				document.execCommand("copy");
				alert("Referral Link Copied!");
			}
		}	

	}
</script>
<?php
}
// Trade Alerts 
function option_fundamentals_members_area_sections( $sections ) {
	//$sections['tradealerts'] = __( 'Trade Alerts', 'option-fundamentals' );
	$sections = array('tradealerts' => __( 'Premium Trade Dashboard', 'option-fundamentals' )) + $sections;
	return $sections;
}
add_filter( 'wc_membership_plan_members_area_sections', 'option_fundamentals_members_area_sections' );

// Trade Alerts 
function option_fundamentals_members_area_sections_basic( $sections ) {
	//$sections['tradealerts'] = __( 'Trade Alerts', 'option-fundamentals' );
	$sections = array('trade-alerts' => __( 'Basic Trade Dashboard', 'option-fundamentals' )) + $sections;
	return $sections;
}
add_filter( 'wc_membership_plan_members_area_sections', 'option_fundamentals_members_area_sections_basic' );

// Trade Alerts 15 days free trial
function option_fundamentals_members_area_sections_15days( $sections ) {
	//$sections['tradealerts'] = __( '15 Days Trade Alerts', 'option-fundamentals' );
	$sections = array('tradealert' => __( 'Trade Dashboard', 'option-fundamentals' )) + $sections;
	return $sections;
}
add_filter( 'wc_membership_plan_members_area_sections', 'option_fundamentals_members_area_sections_15days' );
// Text on My account page
//add_action( 'woocommerce_account_content', 'action_woocommerce_account_content' );
function action_woocommerce_account_content( ) {
	global $current_user; // The WP_User Object
	$current_user_ID = $current_user->ID;
	$subscriptions = wcs_get_users_subscriptions( $current_user_ID );
	foreach ( $subscriptions as $subscription_id =>  $subscription ) {
		$subscription_status = $subscription->get_status();
		// 		echo $subscription_status;
		if($subscription_status == 'expired'){
			echo '<b><p>' . __(" Your 15 Days Free Trial Has Been Expired Please check Our Exciting Membership Plans With Exciting Offers ", "woocommerce") . '<a href="'.site_url().'/cart?add-to-cart=48120&coupon=member60">Check Here</a></p></b>';
		}
	}

};
//add_action('woocommerce_subscription_details_after_subscription_table', 'add_switch_button');
function add_switch_button() {       
	echo "<a href='' class='upgrade-now-btn'>Upgrade Now</a>";             
}

// Shortcode for Premium User Urls
function premium_user_urls(){
	global $current_user; // The WP_User Object
	$current_user_ID = $current_user->ID;
	$url = '<a href="'.site_url('/my-account/').'">Premium Members</a>';
	$memberships_info = wc_memberships_get_user_active_memberships($current_user_ID);
	//print_r($memberships_info);
	foreach ($memberships_info as $membership) {
		$membership_id = $membership->plan->get_id();
		//print_r($membership_id);
		if(is_user_logged_in() && 48142 == $membership_id){
			$url = '<a href="'.site_url().'/my-account/members-area/48142/tradealerts/">Premium Members</a>';
			return $url;
		}
		else{
			$url = '<a href="'.site_url().'/product/premium-package/">Premium Members</a>';
		}
		//return $url;
	}
	return $url;

}
add_shortcode('premium-user-urls','premium_user_urls');

// Shortcode for Premium User Urls
function standard_user_urls(){
	global $current_user; // The WP_User Object
	$current_user_ID = $current_user->ID;
	$url = '<a href="'.site_url('/my-account/').'">Standard Members</a>';
	$memberships_info = wc_memberships_get_user_active_memberships($current_user_ID);
	foreach ($memberships_info as $membership) {
		$membership_id = $membership->plan->get_id();
		// print_r($membership_id);
		if(is_user_logged_in() && 9506 == $membership_id){
			$url = '<a href="'.site_url().'/my-account/members-area/9506/trade-alerts/">Standard Members</a>';
			return $url;
		}
		else{
			$url = '<a href="'.site_url().'/product/monthly-recurring/">Standard Members</a>';
		}
		//return $url;
	}
	return $url;
}
add_shortcode('standard-user-urls','standard_user_urls');

// Shortcode for No Trial Signup
function notrial_user_urls(){
	global $current_user; // The WP_User Object
	$current_user_ID = $current_user->ID;
	$url = '<a href="'.site_url('/my-account/').'">Standard Members</a>';
	$memberships_info = wc_memberships_get_user_active_memberships($current_user_ID);
	foreach ($memberships_info as $membership) {
		$membership_id = $membership->plan->get_id();
		//print_r($membership_id);
		if(is_user_logged_in() && 4846 == $membership_id){
			$url = '<a href="'.site_url().'/my-account/members-area/4846/trade-alerts/">Standard Members</a>';
			return $url;
		}
		else{
			$url = '<a href="'.site_url().'/product/monthly-recurring/">Standard Members</a>';
		}
		//return $url;
	}
	return $url;
}
add_shortcode('notrial-user-urls','notrial_user_urls');

// Shortcode for 15 days Trial Signup
function days15_user_urls(){
	global $current_user; // The WP_User Object
	$current_user_ID = $current_user->ID;
	$url = '<a href="'.site_url('/my-account/').'">Standard Members</a>';
	$memberships_info = wc_memberships_get_user_active_memberships($current_user_ID);
	foreach ($memberships_info as $membership) {
		$membership_id = $membership->plan->get_id();
		//print_r($membership_id);
		if(is_user_logged_in() && 16921 == $membership_id){
			$url = '<a href="'.site_url().'/my-account/members-area/16921/trade-alerts/">Standard Members</a>';
			return $url;
		}
		else{
			$url = '<a href="'.site_url().'/product/monthly-recurring/">Standard Members</a>';
		}
		//return $url;
	}
	return $url;
}
add_shortcode('days15-user-urls','days15_user_urls');
/**
 * Loop through an order's items and see if there's a free trial set
 *
 */
function custom_order_has_trial() {
	$subscriptions = wcs_get_users_subscriptions(get_current_user_id());

	foreach ( $subscriptions as $subscription )
	{
		$subscription_trial_length = wcs_estimate_periods_between( $subscription->get_time( 'start' ), $subscription->get_time( 'trial_end' ), $subscription->get_trial_period() );
		// 		return $subscription_trial_length > 0 ? true :false;
		if( $subscription_trial_length > 0 )
		{
			return true;
		}
	}
	return false;
}
/**
 * Display custom item data in the cart
 */
function csi_align_subscription_length( $cart ) {
	if( is_user_logged_in() ){
		$orders_args = array(
			'customer_id' => get_current_user_id(),
			'limit' => -1,
		);
		$orders = wc_get_orders($orders_args);
		$productID = false;
		if( !empty( $orders ) )
		{		
			foreach ( $orders as $order )
			{
				foreach ($order->get_items() as $item_key => $item )
				{
					if( $item->get_product_id() == 42488 )
					{ 
						$productID = true;
					}else{
						$productID = false;
					}
				}

				if( custom_order_has_trial() )
				{
					$productID = true;
				}
				if($productID) break;
			}
		}
		$trial_length = $productID ? 0 : 15;
		foreach ( $cart->get_cart() as $item_key => $cart_item ){
			if ( (is_a( $cart_item['data'], 'WC_Product_Subscription' ) || is_a( $cart_item['data'], 'WC_Product_Subscription_Variation' ) ) &&  $cart_item['product_id'] !== 4844 ) {
				$cart_item['data']->update_meta_data( '_subscription_trial_length', $trial_length );
			}
		}
	}else{
		//Logged out users
		foreach ( $cart->get_cart() as $item_key => $cart_item ){
			if($cart_item['product_id'] == 48120 ){//[4844] => monthly-recurring, [48120] => premium-package
				if ( is_a( $cart_item['data'], 'WC_Product_Subscription' ) || is_a( $cart_item['data'], 'WC_Product_Subscription_Variation' )) {
					$length = (float) WC_Subscriptions_Product::get_length( $cart_item['data'] );
					$cart_item['data']->update_meta_data( '_subscription_trial_length', 15 );
				}
			}
		}
	}
}

//add_action( 'woocommerce_before_calculate_totals', 'csi_align_subscription_length', 5, 1 );

function fifteen_days_free_trial_product_expired( $product_id, $user_id ) {
	$args = array(
		"posts_per_page" 	=> -1,
		"customer_id" 		=> 127561,
		"orderby" 			=> 'date',
		"order" 			=> 'DESC'
	);
	$query = new WC_Order_Query( $args );
	$orders = $query->get_orders();
	foreach( $orders as $order_id ) {
		$order = wc_get_order( $order_id );
		$items = $order->get_items();
		foreach ($items as $order_item){
			if (42488 == $order_item->get_product_id()){
				$order_Date = $order->get_date_created();
				if(strtotime('now') > strtotime(date("Y-m-d", $order_Date->getTimestamp()) . " +15 days")){
					return true;
				}else{
					return false;
				}
			} 
		}
	}
	return false;
}
function check_fifteen_days_trial_subscription( $user_login, $user ) {
	if ( wc_customer_bought_product( $user->data->user_email, $user->ID, 42488 ) ) {
		if( fifteen_days_free_trial_product_expired( 42488, $user->ID ) )
		{
			$redirect_to = home_url("monthly-recurring");
			wp_safe_redirect( $redirect_to );
			exit;
		}
	}
}
add_action( 'wp_login', 'check_fifteen_days_trial_subscription', 10, 2 );

function check_fifteen_days_free_trial_product_expired() {
	if( is_page( 'monthly-recurring' ) ) 
	{
		if( fifteen_days_free_trial_product_expired( 42488, get_current_user_id() ) )
		{
			echo"<script>alert('Your 15 Days Free Trial Has Been Expired Please check Our Exciting Membership Plans With Exciting Offers!')</script>";
		}
	}
}
add_action( 'wp_footer', 'check_fifteen_days_free_trial_product_expired' );
// add_shortcode('test', function(){

// 	echo"<pre>";
// 	echo"</pre>";
// });
// Add Custom text before product title
//add_action( 'woocommerce_before_single_product_summary' , 'add_custom_text_before_product_title_options', 5 );
function add_custom_text_before_product_title_options() {
	echo '<span class="show-only-premium">List Price <p><strong>$297</strong>/month</p></span>';
}


add_action('admin_footer', 'free_trial_admin_add_js');
function free_trial_admin_add_js($data) {
?>
<script>
	if( jQuery('body').hasClass('yith-plugin-fw-panel') && jQuery('body').hasClass('wp-admin') )
	{
		jQuery('#origin_url').val('https://www.optionfundamentals.com/product/15-days-free-trial/');
		let generated_url =  jQuery('#generated_url');
		generated_url.val(generated_url.val().replace('https://www.optionfundamentals.com/', 'https://www.optionfundamentals.com/product/15-days-free-trial/'));

	}
</script>
<?php
	return $data;
}


/** Unset Fields from Free Trial Checkout **/
add_filter( 'woocommerce_checkout_fields' , 'disabling' );
function disabling($fields){
	$product_id = 42488; //product id which would trigger
	$in_cart = false;
	foreach( WC()->cart->get_cart() as $cart_item ) {
		$product_in_cart = $cart_item['product_id'];
		if ( $product_in_cart === $product_id ) $in_cart = true; // checks if the product is in cart
	}
	if ( $in_cart ){
		unset($fields['billing']['billing_address_1']);
		unset($fields['billing']['billing_address_2']);
		unset($fields['billing']['billing_city']);
		unset($fields['billing']['billing_state']);
		//unset($fields['billing']['billing_country']);
		unset($fields['billing']['billing_postcode']);
		return $fields;
	}
	else {
		return $fields;
	}
}

add_action( 'woocommerce_checkout_before_order_review', 'shipping_validate_city' );
function shipping_validate_city() {
	$product_id = 42488; //product id which would trigger
	$in_cart = false;
	foreach( WC()->cart->get_cart() as $cart_item ) {
		$product_in_cart = $cart_item['product_id'];
		if ( $product_in_cart === $product_id ) $in_cart = true; // checks if the product is in cart
	}
	if( $in_cart ){ ?>
<script>
	jQuery( document ).ready(function() {
		jQuery('.recurring-totals, .cart-subtotal.recurring-total, .order-total.recurring-total').hide();
		// Hide Biiling Address Heading
		var child_count = jQuery('.cfw-parsley-shipping-details').children().length;
		//alert(child_count);
		if(child_count == 3){
			jQuery('body').find('#cfw-customer-info-address h3').hide();
		}
	});
</script>
<?php	}

}
/**
 * Update Next payment date 
 */
add_shortcode('test', function(){
	echo "<pre>";
	echo "</pre>";
});
