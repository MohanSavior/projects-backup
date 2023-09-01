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
	
}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

/********************************************/
/** HEADER CART ICON SHOW NUMBER OF ITEMS SHORTCODE **/

add_shortcode( 'header_cart_icon', 'header_cart_icon_fn' );
function header_cart_icon_fn(){
	$out = '';
	ob_start();
	
	global $woocommerce;
?>
	<a href="<?php echo wc_get_cart_url() ?>" class="header-cart"><i class="icon sp-i-oneicon_header_cart"></i> <span><?php echo WC()->cart->get_cart_contents_count() ?></span></a>

<?php
	$out = ob_get_contents();
	ob_end_clean();
	return $out;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'add_to_cart_fragment' );
function add_to_cart_fragment( $fragments ) {
	$fragments[ '.header-cart' ] = '<a href="' . wc_get_cart_url() . '" class="header-cart"><i class="icon sp-i-oneicon_header_cart"></i><span>' . WC()->cart->get_cart_contents_count() . '</span></a>';
 	return $fragments;

 }
/** DASHBOARD LOGOUT LINK URL SHORTCODE **/
add_shortcode( 'dashboard_logout_link', 'dashboard_logout_link_fn' );
function dashboard_logout_link_fn(){
	$out = '';
	ob_start();
	
	echo wp_logout_url( home_url() );
	
	$out = ob_get_contents();
	ob_end_clean();
	return $out;
}
/** DASHBOARD ACCOUNT DETAILS PAGE USER SHIPPING DETAILS SHORTCODE **/
add_shortcode( 'account_shipping_details', 'account_shipping_details_fn' );
function account_shipping_details_fn(){
	$out = '';
	ob_start();
	
	$user_id = get_current_user_id();
	$user_meta = get_user_meta($user_id);
	
	/*echo '<pre>';
	print_r($user_meta);
	echo '</pre>';*/
	
	$user_first_name = $user_meta['shipping_first_name'][0];
	$user_last_name = $user_meta['shipping_last_name'][0];
	
	$user_address_one = $user_meta['shipping_address_1'][0];
// 	echo 'one - '.$user_address_one;
	$user_address_two = $user_meta['shipping_address_2'][0];
// 	echo 'two - '.$user_address_two;
	$user_city = $user_meta['shipping_city'][0];
	$user_state = $user_meta['shipping_state'][0];
	$user_country = $user_meta['shipping_country'][0];
	$user_shipping_phone = $user_meta['shipping_phone'][0];
	$user_postcode = $user_meta['shipping_postcode'][0];
	
	$user_info = get_userdata($user_id);
	$user_email = $user_info->user_email;

	if($user_first_name && $user_last_name && $user_address_one && $user_address_two && $user_city && $user_state && $user_shipping_phone && $user_postcode){


?>
		<div class="shipping-info-container">
			<div class="details-row">
				<h1>Ship to</h1>
				<p><?php echo $user_first_name.' '.$user_last_name?></p>
			</div>
			<div class="details-row">
				<h1>Shipping Address</h1>
				<p><?php echo $user_address_one.' '.$user_address_two.', ' . $user_city .', '. $user_state .', '. $user_postcode .', '. $user_country  ?></p>
			</div>
			<div class="details-row">
				<h1>Email</h1>
				<p><?php echo $user_email ?></p>
			</div>
			<div class="details-row">
				<h1>Phone</h1>
				<p><?php echo $user_shipping_phone ?></p>
			</div>

		</div>
<?php
		}else{
		?>
		<div class="shipping-info-container">
			<div class="details-row">
				<h3 class="add_address_heading">
					<a href="<?php echo get_page_link('876')?>">+ Add Shipping Address</a>
				</h3>
			</div>
		</div>
<?php
	}
	
	$out = ob_get_contents();
	ob_end_clean();
	return $out;
}
/****************************************/
/** DASHBOARD ACCOUNT DETAILS PAGE USER SHIPPING DETAILS SHORTCODE **/
add_shortcode( 'account_billing_details', 'account_billing_details_fn' );
function account_billing_details_fn(){
	$out = '';
	ob_start();
	
	$user_id = get_current_user_id();
	$user_meta = get_user_meta($user_id);
	
	/*echo '<pre>';
	print_r($user_meta);
	echo '</pre>';*/
	
	$user_first_name = $user_meta['billing_first_name'][0];
	$user_last_name = $user_meta['billing_last_name'][0];
	
	$user_address_one = $user_meta['billing_address_1'][0];
// 	echo 'one - '.$user_address_one;
	$user_address_two = $user_meta['billing_address_2'][0];
// 	echo 'two - '.$user_address_two;
	$user_city = $user_meta['billing_city'][0];
	$user_state = $user_meta['billing_state'][0];
	$user_country = $user_meta['billing_country'][0];
	$user_shipping_phone = $user_meta['billing_phone'][0];
	$user_postcode = $user_meta['billing_postcode'][0];
	
	$user_info = get_userdata($user_id);
	$user_email = $user_info->user_email;

	if($user_first_name && $user_last_name && $user_address_one && $user_address_two && $user_city && $user_state && $user_shipping_phone && $user_postcode){


?>
		<div class="shipping-info-container">
			<div class="details-row">
				<h1>Ship to</h1>
				<p><?php echo $user_first_name.' '.$user_last_name?></p>
			</div>
			<div class="details-row">
				<h1>Shipping Address</h1>
				<p><?php echo $user_address_one.' '.$user_address_two.', ' . $user_city .', '. $user_state .', '. $user_postcode .', '. $user_country  ?></p>
			</div>
			<div class="details-row">
				<h1>Email</h1>
				<p><?php echo $user_email ?></p>
			</div>
			<div class="details-row">
				<h1>Phone</h1>
				<p><?php echo $user_shipping_phone ?></p>
			</div>

		</div>
<?php
		}else{
		?>
		<div class="shipping-info-container">
			<div class="details-row">
				<h3 class="add_address_heading">
					<a href="<?php echo get_page_link('876');?>">+ Add Billing Address</a>
				</h3>
			</div>
		</div>
<?php
	}
	
	$out = ob_get_contents();
	ob_end_clean();
	return $out;
}
/****************************************/
/** ACCOUNT DETAILS PAGE PASSWORD FORM PASSWORD UDPATE **/
add_filter( 'gform_validation_2', 'custom_validation', 10, 4 );
function custom_validation( $validation_result ) {
	$old_password 		= rgpost( 'input_4' );
	$new_password 		= rgpost( 'input_4' );
	$confirm_password 	= rgpost( 'input_4_2' );
	$pass_check			= true;
	$passupdatemsg 		= "";
	$form = $validation_result['form'];

	$user = wp_get_current_user();
	if( !empty($old_password) && wp_check_password( $old_password, $user->user_pass, $user->data->ID ))
	{
		if( !empty($new_password) && !empty($confirm_password))
		{
			if($new_password == $confirm_password)
			{
				$udata['ID'] = $user->data->ID;
				$udata['user_pass'] = $new_password;
				$uid = wp_update_user( $udata );
				if($uid) 
				{
					$passupdatemsg = "The password has been updated successfully";
					$pass_check = true;
				} else {
					$pass_check = false;
					$passupdatemsg = "Sorry! Failed to update your account details.";
				}
			}
			else
			{
				$pass_check = false;
				$passupdatemsg = "Confirm password doesn't match with new password";
			}
		}
		else
		{
			$pass_check = false;
			$passupdatemsg = "Please enter new password and confirm password";
		}
	} 
	else 
	{
		$pass_check = false;
		$passupdatemsg = "Old Password doesn't match the existing password";
	}
	if ( !empty($old_password) && $pass_check == false) 
	{
		$validation_result['is_valid'] = false;
		foreach( $form['fields'] as &$field ) {
			if ( $field->id == '4' ) {
				$field->failed_validation = true;
				$field->validation_message = $passupdatemsg;
				break;
			}
		}
	}

	$validation_result['form'] = $form;
	return $validation_result;
}
/** CONFIRMATION ORDER DETAILS **/
add_shortcode( 'confirmation-order-details', 'confirmation_order_details_fn' );
function confirmation_order_details_fn(){
	ob_start();
	if( is_page( 1108 ) && isset( $_REQUEST['order'] ) && !empty( $_REQUEST['order'] ) && is_int( $_REQUEST['order'] ) )
	{
		$order_id 	= $_REQUEST['order'];
		$order 		= wc_get_order( $order_id );
	?>
		<div class="details-container">
			<div class="order-number">
				<h3 class="title">Order Number</h3>
				<p class="value"></p>
			</div>
			<div class="order-date">
				<h3 class="title">Date</h3>
				<p class="value"></p>
			</div>
			<div class="ship-to">
				<h3 class="title">Ship to</h3>
				<p class="value"></p>
			</div>
			<div class="shipping-address">
				<h3 class="title">Shipping Address</h3>
				<p class="value"></p>
			</div>
			<div class="billing-address">
				<h3 class="title">Billing Address</h3>
				<p class="value"></p>
			</div>
			<div class="user-phone">
				<h3 class="title">Phone</h3>
				<p class="value"></p>
			</div>
			<div class="payment-method">
				<h3 class="title">Payment Method</h3>
				<p class="value">
				<?php 
					echo $order->get_payment_method();
					echo "<br>".$order->get_payment_method_title();
				?>
				</p>
			</div>
			<div class="total">
				<h3 class="title">Total</h3>
				<p class="value"></p>
			</div>
			<!-- Billing Address Same checkbox -->
			<div class="billing-same">
				<i class="fas fa-check"></i>
				<p>Billing address same as shipping address.</p>
			</div>
		</div>
	<?php
	}
	$out = ob_get_contents();
	ob_end_clean();
	return $out;
}