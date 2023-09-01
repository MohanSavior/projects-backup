<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_script( 'savior-pro-scripts', get_stylesheet_directory_uri() . '/assets/js/savior-pro-scripts.js', array('jquery'), time(), true );
	wp_localize_script('savior-pro-scripts', 'ajax_obj', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
			'site_url'=> site_url()
        ));

	/** mCustomScrollbar Files **/
// 	wp_enqueue_script( 'mCustomScrollbar-js', get_stylesheet_directory_uri() . '/inc/mCustomScrollbar/jquery.mCustomScrollbar.js', array('jquery'), '1.0.0', true );
// 	wp_enqueue_style( 'mCustomScrollbar-css', get_stylesheet_directory_uri() . '/inc/mCustomScrollbar/jquery.mCustomScrollbar.css', array(),  '1.0.0', 'all' );
	
	// if( is_page(1386) || is_page(1461) )
	// {
	// 	wp_enqueue_script( 'savior-pro-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), time(), false );
	// 	add_filter('script_loader_tag', 'add_data_attribute', 10, 2);
	// }
	
	if(is_checkout())
	{
// 		wp_enqueue_style( 'int-tel-phone-style', 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/6.4.1/css/intlTelInput.css' );
// 		wp_enqueue_script('int-tel-phone-js','https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/6.4.1/js/intlTelInput.min.js');
	}
}
// function add_data_attribute($tag, $handle) {
//    if ( 'savior-pro-recaptcha' !== $handle )
//     return $tag;

//    return str_replace( ' src', ' async defer src', $tag );
// }


add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );
// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );

// END ENQUEUE PARENT ACTION


/*
 * Shortcode for Follower Counter
 */
add_shortcode('product_single_profile_details',function(){
	ob_start();
	if( is_product() && !empty(get_the_ID()) ){
		$product_id = get_the_ID();
		$product_slug = get_post_field('post_name', $product_id);
		$label='';$input_name='';$input_id='';$placeholder='';
		switch ($product_slug) {
			case 'buy-instagram-followers':
			case 'buy-instagram-likes':
			case 'buy-instagram-views':
			case 'buy-automatic-instagram-likes':
			case 'buy-instagram-story-views':
			case 'buy-instagram-comments':
			case 'buy-instagram-saves':
			case 'buy-instagram-shares':
			case 'buy-instagram-reels-views':
			case 'buy-instagram-post-impressions':	
				$label='Instagram Username';
				$placeholder='Enter Your Instagram Username';
				break;
			case 'buy-tiktok-followers':
				$label='Tiktok Username';
				$placeholder='Enter Your Tiktok Username';
				break;
			case 'buy-tiktok-likes':
			case 'buy-tiktok-views':
			case 'buy-tiktok-shares':
			case 'buy-tiktok-live-views':
			case 'buy-tiktok-video-saves':
			case 'buy-tiktok-video-download':
			case 'buy-tiktok-comments':
				$label='Tiktok Username';
				$placeholder='Enter Your Tiktok Username';
				break;
			case 'buy-facebook-page-likes':
				$label='Facebook Fan Page URL';
				$placeholder='Please enter your Facebook Fan Page URL';
				break;
			case 'buy-facebook-post-likes':
				$label='Facebook Post URL';
				$placeholder='Please enter your Facebook post URL';
				break;
			case 'buy-youtube-subscribers':
				$label='Youtube Channel URL';
				$placeholder='Enter a Youtube URL from your channel';
				break;
			case 'buy-youtube-views':
			case 'youtube-country-targeted-views':
			case 'buy-youtube-likes':
			case 'buy-youtube-comments':
				$label='Youtube Video URL';
				$placeholder='Please enter your Youtube Video URL';
				break;
		  default:
				
			break;
		}
		?>
			<div class="product-profile-detail-main-section">
				<div class="profile-content">
					<h1>Profile Details</h1>
					<p>We NEVER ask for any passwords</p>
				</div>

				<div class="profile-form-sec">
					<form id="product_profile_details" action="<?php echo wc_get_checkout_url(); ?>" method="POST" >
						<?php 
						$variation_id 	= (int)!empty( $_REQUEST['add_to_cart'] ) ? $_REQUEST['add_to_cart'] : 0;
						printf('<label> 							
									<span class="label-text">%s</span>
									<input type="text" name="product_single_input" id="product_single_input" placeholder="%s" size="40">
									<input type="hidden" id="service_type" name="service_type" value="%s">
									<input type="hidden" id="product_variation" name="product_variation" value="%s">
									<input type="hidden" id="product_single_page" name="product_single_page" value="1">
								</label>',
							   $label, 
							   $placeholder	,
							   $product_slug,
							   $variation_id
						);
						echo '<button>Continue <i class="fa fa-arrow-right continue-icon"></i><i class="fa fa-spinner fa-spin"></i></button>'; ?>
					</form>
				</div>

				<div class="profile-bottomn-desc">
					<p>By clicking "Continue" you are agreeing to the Privacy Policy and Terms and Conditions. </p>
				</div>

			</div>
		<?php
	}
	$output = ob_get_clean();
	return $output;	
});

/**
 * 
 */

add_shortcode('product_single_info_details', function(){
	ob_start();
	
	if(isset($_REQUEST['add_to_cart']) && !empty($_REQUEST['add_to_cart']) && is_numeric($_REQUEST['add_to_cart']))
    {
		if( ! WC()->cart->is_empty() )
		{
			WC()->cart->empty_cart();
		}
        $product_id = (int)$_REQUEST['add_to_cart'];
        $product_cart_id = WC()->cart->generate_cart_id( $product_id );
        if(!WC()->cart->find_product_in_cart( $product_cart_id )) {      
            WC()->cart->add_to_cart( $product_id);
			error_log(print_r('$product_id', true));
        }
    }
	
	if ( is_singular('product') || is_checkout() ) {
		if(is_wc_endpoint_url( 'order-received' )) return;

		if( ! WC()->cart->is_empty() )
		{
			?>
			<div class="product-single-details">
				<div class="variation-price-content">
					<h2>Summary</h2>
					<div class="product-price-details">
						<?php														
							$items = WC()->cart->get_cart();
							foreach($items as $item => $values) { 
								$_product =  wc_get_product( $values['data']->get_id()); 
								printf(
									'<div class="variation-info-single">
										<h4>%s</h4>
										<h4>$%s</h4>
									</div>',
									$_product->get_name(),
									number_format(	$_product->get_price(), 2 ,'.', ',')
								);					
							}							
						?>
					</div>
					<?php
						if(WC()->cart->has_discount()) :
							$totaldisc = WC()->cart->get_total_discount();
							$applied_coupons = WC()->cart->get_applied_coupons();
							?>
							<div class="variation-info-single-total coupon-code-section">
								<h4> Coupon: <?php echo $applied_coupons[0]; ?> </h4>
								<h4 style="text-align: right;">-<?php echo $totaldisc; ?> <a style="color: #ff8303;font-weight: 400;font-size: 14px;" href="<?php echo site_url(); ?>/cart/?remove_coupon=<?php echo $applied_coupons[0]; ?>" class="woocommerce-remove-coupon boost-coupon-remove-cls" data-coupon="<?php echo $applied_coupons[0]; ?>">[Remove]</a>
								</h4>
							</div>
							<?php
						endif;
					?>
					<div class="variation-info-single-total">
						<h4> Total: </h4>
						<h4><?php echo WC()->cart->get_cart_total(); ?></h4>
					</div>
				</div>
			</div>
			<?php 
		}
	}
	$output = ob_get_clean();
	return $output;	
});

add_shortcode('product_variations', function(){
	$args = array(
		'post_type' => 'product',
		'numberposts' => -1,
	);
	$products = get_posts( $args );
	echo '<pre>';
	foreach($products as $product):
	$product_s = wc_get_product( $product->ID );
	if ($product_s->product_type == 'variable') {
		print_r($product_s->get_name());
		$product = wc_get_product($product->ID);
		$variations = $product->get_available_variations();
		$variations_id = wp_list_pluck( $variations, 'variation_id' );
		foreach($variations_id as $variation_id){
			echo '<br>';
			print_r(get_permalink($variation_id).'&add_to_cart='.$variation_id);
			
		}
		echo '<br>';
		echo '========================================================<br>';

	}
	endforeach;
	echo '</pre>';
});


add_action('admin_head', 'my_custom_fonts');

function my_custom_fonts() {
  echo '<style>
/* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type=number] {
  -moz-appearance: textfield;
}
  </style>';
}



function dina_add_country_code_script(){
	?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js" integrity="sha512-+gShyB8GWoOiXNwOlBaYXdLTiZt10Iy6xjACGadpqMs20aJOoh+PJt3bwUVA6Cefe7yF7vblX6QwyXZiVwTWGg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
	
var input = document.querySelector("#billing_phone");
window.intlTelInput(input, {
	  initialCountry: "auto",
	  geoIpLookup: function(callback) {
		jQuery.get('https://ipinfo.io', function() {}, "jsonp").always(function(resp) {
		  var countryCode = (resp && resp.country) ? resp.country : "us";
		  callback(countryCode);
		});
	  },
	separateDialCode: true,
});
	
	jQuery('button#place_order').on('click',function(){
	let country_code = 	jQuery("#billing_phone").intlTelInput("getSelectedCountryData").dialCode;
	let phoneNumber = 	jQuery("#billing_phone").val();
		jQuery("#billing_phone").val(country_code + " - " + phoneNumber)
	})
	

	
	
	
</script>
<?php 
}
add_action('wp_footer', 'dina_add_country_code_script');

function dina_add_country_code_style(){
	?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css" integrity="sha512-gxWow8Mo6q6pLa1XH/CcH8JyiSDEtiwJV78E+D+QP0EVasFs8wKXq16G8CLD4CJ2SnonHr4Lm/yY2fSI2+cbmw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<?php
}
add_action('wp_head', 'dina_add_country_code_style');



// Add Country Dropdown field on checkout page if specific product.

add_filter( 'woocommerce_checkout_fields', 'savior_override_checkout_fields', 9999 );

function savior_override_checkout_fields( $fields ) {

    $products_ids = [40226,40225,40224,40223,40222,40221,40220, 40219,40218,40217,40216,40215, 44398, 40252, 46338 ];
    $is_in_cart = false;

    foreach ( WC()->cart->get_cart() as $cart_item ) {
        $cart_item_id = $cart_item['data']->get_id();
		$variation = wc_get_product($cart_item_id);
		$parent_product =$variation->get_parent_id();
        if (  in_array($cart_item_id, $products_ids) || in_array($parent_product, $products_ids) ) {
            $is_in_cart = true;
            break;
        }
    }

    if ( $is_in_cart ) {
        $fields['billing']['billing_country'] = array(
            'type' => 'country',
            'label'     => __(' Choose the target view country', 'woocommerce'),
            'placeholder'   => _x('Select Country', 'placeholder', 'woocommerce'),
            'required'  => true,
            'class'     => array('form-row-wide'),
            'clear'     => true,
			'priority'     => 1,
        );
    }


    return $fields;
}


add_action( 'woocommerce_admin_order_data_after_billing_address', 'savior_checkout_field_display_admin_order_meta', 10, 
    1 );

function savior_checkout_field_display_admin_order_meta($order){
    $code = get_post_meta( $order->get_id(), '_billing_country', true );
    if($code){
        $countries_obj = new WC_Countries();
        $countries_array = $countries_obj->get_countries();
        echo '<p><strong>'.__('Order Country ').':</strong> ' . $countries_array[$code] . '</p>';
    }
}

add_filter( 'default_checkout_billing_country', 'savior_change_default_checkout_country', 10, 1 );

function savior_change_default_checkout_country( $country ) {
    if ( WC()->customer->get_is_paying_customer() ) {
        return $country;
    }
	
	$geo      = new WC_Geolocation(); 
    $user_ip  = $geo->get_ip_address(); 
    $user_geo = $geo->geolocate_ip( $user_ip );
    $country  = $user_geo['country'];

    return $country;
}