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
	/** ZEBRA TOOLTIPS CSS AND JS APPEND **/
	wp_enqueue_style( 'zebra-tooltips-styles', '//cdn.jsdelivr.net/npm/zebra_tooltips@2.1.0/dist/css/default/zebra_tooltips.min.css', array(), '1.0.0', 'all' );
	wp_enqueue_script( 'zebra-tooltips-scripts', '//cdn.jsdelivr.net/npm/zebra_tooltips@latest/dist/zebra_tooltips.min.js', array('jquery'), '1.0.0', true );
	/** mCUSTOM SCROLLBAR ENQUEUE **/
	wp_enqueue_script( 'mCustomScrollbar-js', get_stylesheet_directory_uri() . '/inc/mCustomScrollbar/jquery.mCustomScrollbar.js', array('jquery'), '1.0.0', true );
	wp_enqueue_style( 'mCustomScrollbar-css', get_stylesheet_directory_uri() . '/inc/mCustomScrollbar/jquery.mCustomScrollbar.css', array(),  '1.0.0', 'all' );
	
}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );
/**************************************/
/** DASHBOARD PAGE USER IMAGE SHORTCODE **/
add_shortcode( 'dashboard_user_img', 'dashboard_user_img_fn' );
function dashboard_user_img_fn(){
	$out = '';
	ob_start();
	$author_id = get_current_user_id();
	$img_id = get_field( 'profile_picture', 'user_'.$author_id );
	
	if($img_id){
		echo wp_get_attachment_image( $img_id, array( '120', '120' ) );
	}else{
		echo '<img src="https://gravatar.com/avatar?s=120&d=mm">';
	}
	$out = ob_get_contents();
	ob_end_clean();
	return $out;
}
/***************************************/
/** DASHBOARD USER PROFILE PIC UPDATE ON CHANGE SHORTCODE **/
/** Function to show user profile Image **/ 
/** Default Image Set In ACF **/
add_action('acf/render_field_settings/type=image', 'add_default_value_to_image_field');
function add_default_value_to_image_field($field) {
	acf_render_field_setting( $field, array(
		'label'			=> 'Default Image',
		'instructions'		=> 'Appears when creating a new post',
		'type'			=> 'image',
		'name'			=> 'default_value',
	));
}
/* Default Image Set In ACF */
function update_user_profile_pic() {
	$user_id = 'user_'.get_current_user_id();
	$user_profile_photo = get_field_object('profile_picture', $user_id);	
	if(isset($_POST['photo_id'])){	
		if(update_field('profile_picture', $_POST['photo_id'], $user_id)){
			update_field('_profile_picture', 'field_63440b0b1d0da', $user_id);		
			wp_send_json_success();
		}else{
			wp_send_json_error();
		} 
	}
}
add_action('wp_ajax_update_user_profile_pic', 'update_user_profile_pic');

/************************************/
/** USER LOGOUT URL SHORTCODE **/
add_shortcode( 'user_logout_url', 'user_logout_url_fn' );
function user_logout_url_fn(){
	$out = '';
	ob_start(); 	?>	
	<div class="logout-container">
		<a href="<?php echo wp_logout_url( home_url() ); ?>"><i class="icon sp-i-twologout-solid"></i>Logout</a>	
	</div>	
<?php
	$out = ob_get_contents();
	ob_end_clean();
	return $out;
}
/************************************/
/** MEMBERSHIP TABLE FOR DASHBOARD PAGE SHORTCODE **/
add_shortcode( 'dashboard_user_memberships', 'dashboard_user_memberships_fn' );
function dashboard_user_memberships_fn() {
	// bail if Memberships isn't active or we're in the admin
	if ( ! function_exists( 'wc_memberships' ) || is_admin() ) {
		return;
	}
	// buffer contents
	ob_start();
	?>
	<div class="woocommerce">
		<?php
		wc_get_template( 'myaccount/my-memberships.php', array(
			'customer_memberships' => wc_memberships_get_user_memberships(),
			'user_id'              => get_current_user_id(),
		) );
		?>
	</div>
	<?php
	// output buffered content
	return ob_get_clean();
}
/************************************/
/** SUBSCRIPTION TABLE FOR DASHBOARD PAGE SHORTCODE **/
add_shortcode('dashboard_user_subscription', 'dashboard_user_subscription_fn');
function dashboard_user_subscription_fn(){
	if ( ! class_exists( 'WC_Subscriptions' )) {
		return;
	}
	ob_start();	
	include get_stylesheet_directory().'/template/myaccount/subscription-details.php';
	return ob_get_clean();
}


/***************************************/
add_action( 'template_redirect', 'woo_custom_redirect_after_purchase' );
function woo_custom_redirect_after_purchase() {
    global $wp;
    if ( is_wc_endpoint_url( 'order-received' ) ) {
        wp_redirect( site_url('thank-you?order='.$wp->query_vars['order-received']) );
        exit;
    }
}

/************************************/
/** CHECKOUT THANK YOU PAGE **/
add_shortcode('checkout-thank-you', 'checkout_thank_you_fn');
function checkout_thank_you_fn(){
	ob_start();	
	include get_stylesheet_directory().'/template/myaccount/thankyou.php';
	return ob_get_clean();
}
/************************************/
/** ESPORTS CPTs ARCHIVE SETUP FOR GETTING PARAMETER AND SHOW POSTS ACCORDINGLY **/
add_action('powerpack/query/insights-esports-filter', function( $query ){
	$esport = $_GET['esport'];
	$tax_query_atts = array(
		array(
			'taxonomy' => 'esport', 
			'terms' => $esport,
			'field' => 'slug'	
		)
	);
	//$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
	$query->set( 'tax_query', $tax_query_atts ); 
	//$query->set('posts_per_page', '1');
	//$query->set('paged', $paged);
	//print_r($query);
	
});
/**************************************/
/** ARTICLES SHOW POST TYPE NAME SHORTCODE **/
add_shortcode( 'custom_post_type_name', 'custom_post_type_name_fn' );
function custom_post_type_name_fn(){
	$out = '';
	ob_start();
	
	$post_type = get_post_type();
	$post_name = '';
	
	if($post_type == 'write_ups'){
		echo $post_name = 'Latest News';
	}elseif($post_type == 'projections'){
		echo $post_name = 'Projections';
	}elseif($post_type == 'ev_plays'){
		echo $post_name = 'EV+ Plays';
	}
	
	$out = ob_get_contents();
	ob_end_clean();
	return $out;
}
/**************************************/
/** ARTICLES SINGLE SIDEBAR LINKS SHORTCODE **/
add_shortcode( 'sidebar_links', 'sidebar_links_fn' );
function sidebar_links_fn(){
	$out = '';
	ob_start();
	
	$id = get_the_ID();
	/*echo '<pre>';
	print_r(wp_get_object_terms($id, 'esport'));
	echo '</pre>';*/
	
	$terms = wp_get_object_terms($id, 'esport');
	foreach($terms as $term){
		$esport_slug = $term->slug;
		$esport_name = $term->name;
	}
	if($term->slug == 'csgo'){
		$projections_link = get_permalink(1361);
	}elseif($term->slug == 'call-of-duty'){
		$projections_link = get_permalink(1555);
	}elseif($term->slug == 'dota-2'){
		$projections_link = get_permalink(1557);
	}elseif($term->slug == 'league-of-legends'){
		$projections_link = get_permalink(1559);
	}elseif($term->slug == 'rocket-league'){
		$projections_link = get_permalink(1561);
	}elseif($term->slug == 'valorant'){
		$projections_link = get_permalink(1565);
	}	
	
	?>
	<div class="sidebar-links-container">
		<div class="link-items">
			<ul class="link-item">
				<li class="item"><a href="<?php echo get_post_type_archive_link('write_ups').'?esport='.$esport_slug ?>"><i class="icon sp-i-oneaim-outlined"></i><?php //echo $esport_name ?> Latest News</a></li>
				<li class="item"><a href="<?php echo $projections_link ?>"><i class="icon sp-i-oneaim-outlined"></i>Projections</a></li>
				<li class="item"><a href="<?php echo '#' ?>"><i class="icon sp-i-oneaim-outlined"></i>EV+ Plays</a></li>
			</ul>
		</div>		
	</div>
<?php	
	$out = ob_get_contents();
	ob_end_clean();
	return $out;
}
/**************************************/
/** ARTICLES SINGLE POST TYPE HEADING ABOVE POST TITLE SHORTCODE **/
add_shortcode( 'custom_post_type_heading', 'custom_post_type_heading_fn' );
function custom_post_type_heading_fn(){
	$out = '';
	ob_start();
	
	global $post;
	/*echo '<pre>';
	print_r($post);
	echo '</pre>';*/
	
	$id = get_the_ID();
	/*echo '<pre>';
	print_r(wp_get_object_terms($id, 'esport'));
	echo '</pre>';*/
	$post_type = get_post_type_object($post->post_type);
	/*echo '<pre>';
	print_r($post_type->label);
	echo '</pre>';*/
	$terms = wp_get_object_terms($id, 'esport');
	foreach($terms as $term){
		$esport_name = $term->name;
	}	
	if($post_type->label == 'Write Ups'){
		$post_type->label = 'Latest News';
	}
	echo $esport_name.' '.$post_type->label;
	
	$out = ob_get_contents();
	ob_end_clean();
	return $out;
}
/***************************************/
/** DASHBOARD ACCOUNT SETTINGS PASSWORD UPDATE CHECK **/
add_filter( 'gform_validation_2', 'custom_validation', 10, 4 );
function custom_validation( $validation_result ) {
	$old_password 		= rgpost( 'input_6' );
	$new_password 		= rgpost( 'input_7' );
	$confirm_password 	= rgpost( 'input_7_2' );
	$pass_check			= true;
	$passupdatemsg 		= "";
   error_log(print_r($old_password, true));
   error_log(print_r($new_password, true));
   error_log(print_r($confirm_password, true));
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
			if ( $field->id == '7' ) {
				$field->failed_validation = true;
				$field->validation_message = $passupdatemsg;
				break;
			}
		}
	}
	
    $validation_result['form'] = $form;
    return $validation_result;
}
/**************************************/
/** ARTICLES ARCHIVE BREADCRUMBS SHORTCODE **/
add_shortcode( 'articles_archive_breadcrumbs', 'articles_archive_breadcrumbs_fn' );
function articles_archive_breadcrumbs_fn(){
	$out = '';
	ob_start();
	
	$home_link = get_home_url();
	
	$term = get_queried_object();	
// 	echo $term->name;	
// 	print_r($esport);
	
	if($term->name){
		echo '<div class="home_link"><a href="'.$home_link.'">Home</a> '. $term->name .' Latest News </div>';
	}else{
		echo '<div class="home_link"><a href="'.$home_link.'">Home</a> Latest News </div>';
	}
		
	$out = ob_get_contents();
	ob_end_clean();
	return $out;
}
add_action('woocommerce_thankyou_order_received_text', 'woocommerce_before_thankyou_discord_btn', 20, 2 );

function woocommerce_before_thankyou_discord_btn( $str, $order ) {
	if( is_user_logged_in() )
	{
		$user_id = sanitize_text_field( trim( get_current_user_id() ) );
		$access_token = sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_access_token', true ) ) );
		if( !$access_token )
			$str .= do_shortcode('[elementor-template id="2363"]');
		
		return $str;
	}
	return $str;
}
/************************************/
add_shortcode('test', function(){
	echo"<pre>";
// 	$users = get_users( array( 'role'    => 'subscriber', 'fields' => array( 'ID' ) ) );
// 	foreach($users as $user){
		
// 		print_r(get_user_meta ( $user->ID));
// 		echo "UserName : ".get_user_meta($user->ID, '_atm_saviorpro_discord_username', true)."<br>";
// 		echo "Discord User ID : ".get_user_meta($user->ID, '_atm_saviorpro_discord_user_id', true)."<br>";
// 		print_r(get_user_meta( 85 ));
		
// 	}
// 	print_r(get_user_meta( 436 ));
echo"dfdF";
	$_atm_saviorpro_discord_user_id		= sanitize_text_field( trim( get_user_meta( 4361, '_atm_saviorpro_discord_user_id', true ) ) );
		print_r($_atm_saviorpro_discord_user_id);
	echo"</pre>";

});