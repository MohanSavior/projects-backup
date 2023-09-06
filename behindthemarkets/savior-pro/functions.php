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
	wp_enqueue_style( 'savior-select2-styles', get_stylesheet_directory_uri() . '/assets/plugins/select2/select2.min.css', array(), time(), 'all' );
	wp_enqueue_style( 'savior-datatable', '//cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css', array(), '1.11.5', 'all' );
	wp_enqueue_style( 'savior-slick', '//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css', array(), time(), 'all' );

	wp_enqueue_style( 'savior-pro-styles', get_stylesheet_directory_uri() . '/assets/css/savior-pro-styles.css', array(), time(), 'all' );
	wp_enqueue_style( 'savior-pro-responsive-styles', get_stylesheet_directory_uri() . '/assets/css/savior-pro-responsive-styles.css', array(), time(), 'all' );
	wp_enqueue_script( 'savior-select2-scripts', get_stylesheet_directory_uri() . '/assets/plugins/select2/select2.min.js', array('jquery'), time(), true );
	wp_enqueue_script( 'savior-datatable', '//cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js', array('jquery'), '1.11.5', true );
	wp_enqueue_script( 'savior-slick-min-js', '//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), time(), true );
	wp_enqueue_script( 'savior-pro-scripts', get_stylesheet_directory_uri() . '/assets/js/savior-pro-scripts.js', array('jquery', 'savior-datatable'), time(), true );
	wp_localize_script('savior-pro-scripts', 'ajax_obj', array(
		'admin_url' 		=> admin_url('admin-ajax.php'),
		'nonce' 			=> wp_create_nonce('ajaxnonce'),
// 		'getProductTitle' 	=> getProductTitle(),
		'home_url'			=> home_url(),
		'getCurrentPostID'	=> get_the_ID()
	));
	wp_enqueue_script( 'mcustomscroll-scripts', 'https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.js', '3.1.5' );
	wp_enqueue_style( 'mcustom-css', 'https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.css', '3.1.5' );
}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

/**
 ** Post Search Count Start
 **/

add_shortcode ('post_search_count', 'post_search_count_fn');
function post_search_count_fn() {
	global $wp_query;

	$post_Count_num = $wp_query->found_posts;

	if ($post_Count_num == 0){
		echo '<h4 class="search-details-cls no-post-found-cls">No Results for: “'.get_search_query().'” <br><span class="search-title">See related searches</span></h4>';
		$tags = get_tags(array(
			'hide_empty' => false
		));
		echo '<ul class="search-pg-tags">';
		foreach ($tags as $tag) {
			$tag_link = get_tag_link( $tag->term_id );
			echo '<li><a href='.$tag_link.' title=' .$tag->name. '>' . $tag->name . '</a></li>';
		}
		echo '</ul>';
	}else {
		echo '<h4 class="search-details-cls"> Search Results for: “'.get_search_query().'”</h4>';
	}
}

/**
 ** Post Search Count End
 **/

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
 ** Subscription Time Period Option Func End
 **/	

/**
 ** Hide Shipping on Cart Func Start
 **/	

function disable_shipping_calc_on_cart( $show_shipping ) {
	if( is_cart() ) {
		return false;
	}
	return $show_shipping;
}
add_filter( 'woocommerce_cart_ready_to_calc_shipping', 'disable_shipping_calc_on_cart', 99 );

/**
 ** Hide Shipping on Cart Func End
 **/	

/**
 ** Skip Cart Shipping on Cart Func Start
 **/	

add_filter('woocommerce_add_to_cart_redirect', 'lw_add_to_cart_redirect');
function lw_add_to_cart_redirect() {
	global $woocommerce;
	$lw_redirect_checkout = wc_get_checkout_url();
	return $lw_redirect_checkout;
}

/**
 ** Skip Cart Shipping on Cart Func End
 **/	

/**
 ** Thank you Page Redirection Func Start
 **/

add_action('template_redirect', 'webtoffee_custom_redirect_after_purchase');

function webtoffee_custom_redirect_after_purchase() {
	global $wp;
	if (is_checkout() && !empty($wp->query_vars['order-received'])) {
		wp_redirect(site_url()."/order-confirmation/");
		exit;
	}
}

/**
 ** Thank you Page Redirection Func End
 **/


/**
 ** Your Account Page Func Start
 **/


/**
 ** Subscription Func Start
 **/

add_shortcode('wdm_my_subscription', 'wmd_my_custom_function');
function wmd_my_custom_function(){
	WC_Subscriptions::get_my_subscriptions_template();
}

/**
 ** Subscription Page Func End
 **/

/**
 ** Order Func Start
 **/

function woocommerce_orders() {
	$user_id = get_current_user_id();
	if ($user_id == 0) {
		return do_shortcode('[woocommerce_my_account]'); 
	}else{
		ob_start();
		wc_get_template( 'myaccount/my-orders.php', array(
			'current_user'  => get_user_by( 'id', $user_id),
			'order_count'   => !empty($order_count) ? $order_count : 0
		) );
		return ob_get_clean();
	}

}
add_shortcode('woocommerce_orders', 'woocommerce_orders');

/**
 ** Order Func End
 **/

/**
 ** Edit Account Func Start
 **/

add_shortcode('edit_account', 'display_myaccount_edit_account');
function display_myaccount_edit_account()
{
	return WC_Shortcode_My_Account::edit_account();
}

/**
 ** Edit Account Func End
 **/

/**
 ** Edit Address Func Start
 **/

add_shortcode('edit_address', 'display_myaccount_edit_address');
function display_myaccount_edit_address()
{
	ob_start();
	WC_Shortcode_My_Account::edit_address( 'billing' );

	$output_string = ob_get_contents();
	ob_end_clean();
	return $output_string;
}

/**
 ** Edit Address Func End
 **/

/**
 ** Redirect Func Start
 **/

function action_woocommerce_customer_save_address( $user_id, $load_address ) { 
	wp_safe_redirect(site_url()."/your-account"); 
	exit;
}; 
add_action( 'woocommerce_customer_save_address', 'action_woocommerce_customer_save_address', 999, 2 );


// define the woocommerce_save_account_details callback 
function action_woocommerce_save_account_details( $user_id ) { 
	// make action magic happen here... 
	wp_safe_redirect(site_url()."/your-account"); 
	exit;
};  
// add the action 
add_action( 'woocommerce_save_account_details', 'action_woocommerce_save_account_details', 999, 1 ); 


add_action('wp_logout', 'wc_registration_redirect');
function wc_registration_redirect( $redirect_to) {
	wp_redirect(site_url());
	exit;
}

add_shortcode( 'intw_logout', 'intw_logout_shortcode_func' );
function intw_logout_shortcode_func( $atts ) {
	$homepage = get_bloginfo('url');
	extract( shortcode_atts( array(
		'redirect' => $homepage,
	), $atts ) );
	$content = esc_url( wp_logout_url());
	return $content;
}

add_filter('woocommerce_login_redirect', 'login_redirect');
function login_redirect($redirect_to) {
	return wp_safe_redirect(site_url()."/my-product");
}

/**
 ** Redirect Func End
 **/


/**
 ** Purchased Products By user
 **/


$GLOBALS['purchased_product_ids'] = purchased_product_ids();

function purchased_product_ids()
{
	$user_id = get_current_user_id();			
	global $wpdb;
	$count_subscriptions = $wpdb->get_results( "SELECT distinct p.ID as Sub_id, woim.meta_value as product_id, woi.order_id as Order_id 
	FROM {$wpdb->prefix}posts as p
	LEFT JOIN {$wpdb->prefix}posts AS p2 ON p.post_parent = p2.ID
	LEFT JOIN {$wpdb->prefix}postmeta AS pm ON p2.ID = pm.post_id
	LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS woi ON pm.post_id = woi.order_id
	LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS woim ON woi.order_item_id = woim.order_item_id
	WHERE p.post_type = 'shop_subscription' 
	AND p2.post_type = 'shop_order' AND woi.order_item_type LIKE 'line_item'
	AND pm.meta_key = '_customer_user'
	AND pm.meta_value = $user_id
	AND p.post_status = 'wc-active'  GROUP BY p.ID", OBJECT );
	$pro_ids = null;
	foreach( $count_subscriptions as $produc_ids){
		$relations = get_field('relation', $produc_ids->product_id);
		if(!empty($relations)){
			foreach($relations as $relation){
				$pro_ids[] = $relation->ID;
			} 
		}

	}
	return is_array($pro_ids) ? $pro_ids : array(0);
}


add_action( 'powerpack/query/purchased_by_user', function( $query ) {
	$user = new WP_User( get_current_user_id() );
	if(get_current_user_id() == 31){
		$query->set( 'post__in', array(2724, 2725, 2726, 2728, 2729) );
	}else{
		if(!empty(array_intersect(array('customerserviceagent', 'administrator', 'editor'), $user->roles))){
			$query->set( 'post__in', array(2724, 2725, 2726, 2728, 2729) );
		}elseif(!empty($GLOBALS['purchased_product_ids']) && is_user_logged_in()){
			$query->set( 'post__in', $GLOBALS['purchased_product_ids'] );
		}else{
			$query->set( 'post__in', array(0) );
		}	
	}
} );

/**
 ** Remaining Products By user
 **/

add_action( 'powerpack/query/remaining_by_user', function( $query ) {
	if(!empty($GLOBALS['purchased_product_ids'])){
		$query->set( 'post__not_in', $GLOBALS['purchased_product_ids'] );
	}
} );

/**
 ** Remaining Products By user
 **/

/**
 ** Custom BTM post update
 **/

// add_action( 'woocommerce_new_order', 'update_user_meta_product', 10, 1 ); 
// function update_user_meta_product( $order_id ){
// 	$product_ids = array();
// 	$order = wc_get_order( $order_id );
// 	$user_id = get_post_meta( $order_id, '_customer_user', true );
// 	$items = $order->get_items();
// 	foreach ( $items as $item ) {
// 		$product_id = $item->get_product_id();
// 		$relation_ids = get_field('relation', $product_id );
// 		$product_ids[] = $relation_ids[0]->ID;

// 	}
// 	$get_user_pro_ids = get_user_meta($user_id, '_product_ids', true);
// 	$pro_ids = is_array($get_user_pro_ids) ? $get_user_pro_ids : array();	
// 	update_user_meta($user_id, '_product_ids', array_merge($pro_ids, $product_ids));
// }

/**
 ** Custom BTM post update
 **/

/**
 ** Get All Product Title Func
 **/
add_action("wp_ajax_getProductTitle", "getProductTitle");
add_action("wp_ajax_nopriv_getProductTitle", "getProductTitle");
function getProductTitle(){
	$args = array(
		'post_type'      => 'product',
		'posts_per_page' => -1,
		'post__not_in'	 => array(104623)
	);
	$product_ids = array();
	$loop = new WP_Query( $args );

	while ( $loop->have_posts() ) : $loop->the_post();
	global $product;
	$relation_ids = get_field('relation', get_the_ID() );
	if(!empty($relation_ids))
		$product_ids[] = array('id'=>$relation_ids[0]->ID, 'link'=>get_permalink());

	endwhile;

	wp_reset_query();
	if(wp_doing_ajax())
	{
		wp_send_json_success(array('data' => $product_ids));
	}
	return $product_ids;
}

/**
 ** Get All Product Title Func
 **/

/**
 ** Latest Articles on Product Single
 **/

add_action( 'powerpack/query/latest_articles', function( $query ) {
	$get_post_object = get_queried_object();
	$latest_article_ids = get_post_meta($get_post_object->ID, 'select_articles', true);
	if(!empty($latest_article_ids)){
		$query->set( 'post__in', $latest_article_ids );
	}else{
		$query->set( 'post__in', array(0) );
	}	
} );

add_action( 'powerpack/query/related_articles', function( $query ) {
	$get_post_object = get_queried_object();
	$latest_article_ids = get_post_meta($get_post_object->ID, 'select_articles', true);
	$id_not_in = isset($_REQUEST['article_id']) && !empty($_REQUEST['article_id']) ? $_REQUEST['article_id'] : '';
	if(!empty($latest_article_ids)){
		$query->set( 'post__in', array_diff($latest_article_ids,array($id_not_in)) );
	}else{
		$query->set( 'post__in', array(0) );
	}
} );

/**
 ** Latest All Articles on Product Single
 **/

/**
 ** Stock Portfolio on Product Single
 **/

add_action( 'powerpack/query/main_stock_portfolio', function( $query ) {
	$get_post_object = get_queried_object();
	$latest_main_stock_ids = get_post_meta($get_post_object->ID, 'select_stock_portfolio', true);
	// 	$most_recent_alerts_updates = get_field('most_recent_alerts_updates');
	// 	echo $most_recent_alerts_updates;

	$id_not_in = isset($_REQUEST['stock_portfolio_position']) && $_REQUEST['stock_portfolio_position']=='SP' ? $_REQUEST['stock_portfolio_position'] : '';
	if(!empty($latest_main_stock_ids)){

		if(empty($id_not_in)){
			$open_closed_post_id =[];
			$checked_id =[];
			$get_post_modified_time =[];
			foreach($latest_main_stock_ids as $latest_stock_id){
				// 				if ( 'stock_portfolio' == get_post_type($latest_stock_id) ) 
				// 				{
				if(get_post_meta($latest_stock_id, 'most_recent_alerts_updates', true))
					$checked_id[] = $latest_stock_id;

				// 				}

				$get_post_modified_time[$latest_stock_id] = get_post_modified_time('U', true, $latest_stock_id);

				if(get_the_title($latest_stock_id) == 'Open Positions' || get_the_title($latest_stock_id) == 'Closed Positions' )
				{
					$open_closed_post_id[] = $latest_stock_id;
				}
			}
			if(!empty($open_closed_post_id)){	
				$max = max($get_post_modified_time);
				$result = array_filter($get_post_modified_time,function($v)use($max){ return $v == $max;});
				$query->set( 'post__in', array_unique(array_merge(array_keys($result), array_diff($latest_main_stock_ids,$open_closed_post_id))) );
				if(!empty($checked_id))
					$query->set( 'orderby' , 'post__in');
			}else {
				$query->set( 'post__in', array_unique(array_merge(array_keys($result) , $latest_main_stock_ids) ));
			}

		}else{
			$open_closed_post_id =[];
			foreach($latest_main_stock_ids as $latest_main_stock_id){
				if(get_the_title($latest_main_stock_id) == "Open Positions" || get_the_title($latest_main_stock_id) == "Closed Positions"){
					$open_closed_post_id[] = $latest_main_stock_id;
				}			
			}
			if(!empty($open_closed_post_id)){
				$query->set( 'post__in', $open_closed_post_id );
			}else {
				$query->set( 'post__in', array(0) );
			}
		}
	}else{
		$query->set( 'post__in', array(0) );
	}
} );
/*
 * 
 */
function keywordsearch($search_text)
{
	global $wpdb;
	$sql = "
    SELECT *
    FROM {$wpdb->posts} p 
    LEFT JOIN {$wpdb->postmeta} pm ON p.ID=pm.post_id 
    WHERE ( p.post_type='stock_portfolio' OR p.post_type='bonus_reports' ) AND pm.meta_key='add_keyword' AND pm.meta_value LIKE '%{$search_text}%' 
    ORDER BY pm.meta_value ASC";
	$quotes = $wpdb->get_results( $sql );
	return $quotes;
}
add_action( 'powerpack/query/latest_stock_portfolio', function( $query ) {
	$get_post_object = get_queried_object();

	$latest_stock_ids = get_post_meta($get_post_object->ID, 'select_stock_portfolio', true);//select_product //select_stock_portfolio
	//new code
	$search_text = isset($_REQUEST['keyword']) && !empty($_REQUEST['keyword']) ? $_REQUEST['keyword'] : '';	

	$paged = (get_query_var('paged') && empty($_REQUEST['page'])) ? get_query_var('paged') : 1;
	$query->set( 'order', 'DSC');
	$query->set( 'orderby' , 'publish_date');
	if(!empty(keywordsearch($search_text)) && is_array(keywordsearch($search_text)))
	{
		$meta_query = array(
			'relation' => 'OR',
			array(
				'Key'    => 'add_keyword',
				'value'    => $search_text,
				'compare'  => 'LIKE'
			)
		);
		$query->set( 'meta_query', $meta_query );
	}else{
		$query->set( 's', $search_text );
	}
	$query->set( 'post_status', 'publish');
	// 	echo '<pre>';
	// 	print_r(keywordsearch($search_text));
	// 	echo '</pre>';
	//end new code
	$id_not_in = isset($_REQUEST['stock_portfolio_id']) && !empty($_REQUEST['stock_portfolio_id']) ? $_REQUEST['stock_portfolio_id'] : '';
	if(!empty($latest_stock_ids)){
		$checked_id =[];
		if(empty($id_not_in)){
			$open_closed_post_id =[];
			foreach($latest_stock_ids as $latest_stock_id){
				// 				if(get_post_meta($latest_stock_id, 'most_recent_alerts_updates', true))
				// 						$checked_id[] = $latest_stock_id;

				if(get_the_title($latest_stock_id) === 'Open Positions' || get_the_title($latest_stock_id) === 'Closed Positions' )
				{
					$open_closed_post_id[] = $latest_stock_id;
				}
			}
			// 			if(!empty($checked_id))
			// 			{
			// 				$query->set( 'post__in', array_unique(array_merge($checked_id , $latest_stock_ids) ));
			// 				$query->set( 'orderby' , 'post__in');
			// 				$query->set( 'order' , 'ID');
			// 			}else{
			$query->set( 'post__in', array_diff($latest_stock_ids,$open_closed_post_id) );
			// 			}
		}else{
			//echo"hi321";
			$query->set( 'post__in', array_diff($latest_stock_ids,array($id_not_in)) );
		}
	}else{
		$query->set( 'post__in', array(0) );
	}
	//new code
	if (isset($_REQUEST['months']) && !empty($_REQUEST['months']) && isset($_REQUEST['years']) && !empty($_REQUEST['years'])){
		$date_query = [
			'relation' => 'AND',
			[
				'year' 	=> $_REQUEST['years'],
				'month' 	=> $_REQUEST['months'],
				'compare'   => '=',
			]
		];
		$query->set('date_query',$date_query);
	}
	if (isset($_REQUEST['years']) && !empty($_REQUEST['years'])){
		$date_query = [
			[
				'year'  	=> $_REQUEST['years'],
				'compare'   => '=',
			]
		];
		$query->set('date_query',$date_query);
	}
	if (isset($_REQUEST['months']) && !empty($_REQUEST['months'])){
		$date_query = [
			[
				'month' 	=> $_REQUEST['months'],
				'compare'   => '=',
			]
		];
		$query->set('date_query',$date_query);
	}
	//end new code
} );
//recent_alerts_by_checked
/*
 * 
 */
add_action( 'powerpack/query/recent_alerts_by_checked', function( $query )
		   {
			   $get_post_object = get_queried_object();
			   $get_post_modified_time =[];
			   $latest_stock_ids = get_post_meta($get_post_object->ID, 'select_stock_portfolio', true);//select_product //select_stock_portfolio

			   $query->set( 'post_status', 'publish');
			   $checked_id = [];
			   foreach($latest_stock_ids as $latest_stock_id)
			   {
				   if(get_post_meta($latest_stock_id, 'most_recent_alerts_updates', true))
				   {
					   $checked_id[] = $latest_stock_id;
					   $get_post_modified_time[$latest_stock_id] = get_post_modified_time('U', true, $latest_stock_id);
				   }

			   }
			   if(!empty($checked_id))
			   {
				   $max = max($get_post_modified_time);
				   $result = array_keys(array_filter($get_post_modified_time,function($v)use($max){ return $v == $max;}));
				   $get_post = get_post_meta($result[0],'select_product', true);
				   if($get_post[0] == $get_post_object->ID)
				   {
					   $query->set( 'post__in', $result );			
				   }else{
					   $query->set( 'order' , 'ID');
				   }
			   }else{
				   $query->set( 'post__in', $latest_stock_ids );	
				   $query->set( 'order' , 'ID');
			   }
		   });
/**
 ** Latest Articles Button URL
 **/

add_shortcode( 'article_post_link', 'article_permalink' );
// The Permalink Shortcode
function article_permalink() {
	ob_start();
	global $wp;
	$id = get_the_ID();
	$current_url = home_url(add_query_arg(array(),$wp->request));
	echo $current_url.'/?article_id='.$id;
	return ob_get_clean();
}

/**
 ** Latest Articles Single Shortcode
 **/

add_shortcode( 'latest_articles_single', 'display_custom_post_type' );

function display_custom_post_type(){

	// 	$article_id = if(isset($_REQUEST['article_id'] && !empty($_REQUEST['article_id'])) ? $_REQUEST['article_id'] "");

	$article_id = $_REQUEST['article_id'];
	$queried_post = get_post($article_id);
	$title = $queried_post->post_title;
	$meta = get_post_meta($article_id);
	$article_date = get_the_date( 'l, F j, Y', $article_id );
	$author_id = get_post_field( 'post_author', $article_id );
	$author_name = get_the_author_meta( 'display_name', $author_id );
	// 	$post_thumbnail_id = get_post_thumbnail_id( $article_id );
	// 	$img_ar =  wp_get_attachment_image_src( $post_thumbnail_id, 'full' );
	$article_content = $queried_post->post_content;	

	$stock_single_file_upload = get_field('stock_file_upload', $article_id);
	$stock_single_excel_file_upload = get_field('stock_excel_file_upload', $article_id);

	$string = '';

	$string .= '<div class="latest-article-single-section">';
	$string .= '<div class="article-title">';
	$string .= '<h4>'.$title.'</h4>';
	$string .= '</div>';

	$string .= '<div class="article-meta">';
	$show_author = get_field('show_author', $article_id);
	if( $show_author && in_array('true', $show_author) ) {
		$string .= '<h4><i aria-hidden="true" class="fas fa-user-alt"></i> By '.$author_name.'</h4>';
	}

	$show_date = get_field('show_date', $article_id);
	if( $show_date && in_array('true', $show_date) ) {
		$string .= '<h4><i aria-hidden="true" class="far fa-calendar"></i> '.$article_date.'</h4>';
	}else {

	}	
	// 	$string .= '<h4 class="tedt"><i aria-hidden="true" class="far fa-calendar"></i> '.$article_date.'</h4>';
	$string .= '</div>';

	// 	$string .= '<div class="article-image">';
	// 	$string .= '<img src="'.$img_ar[0].'" >';
	// 	$string .= '</div>';

	if(isset($stock_single_file_upload['url']) && $stock_single_file_upload['url'] != '' && !has_shortcode($queried_post->post_content, 'stock_file_upload')):
	$string .= '<div class="article-image stock_portfolio_iframe">';
	// 	$string .= '<iframe src="'.$stock_portfolio_iframe_URL.'" height="750px" ></iframe>';
	$string .= '<iframe src="'.$stock_single_file_upload['url'].'" height="750px" ></iframe>';
	$string .= '</div>';
	endif; 

	$stock_excel_sheet_iframe_url = $stock_single_excel_file_upload['url'];
	if(!empty($stock_excel_sheet_iframe_url) && !has_shortcode($queried_post->post_content, 'stock_excel_file_upload')){
		$string .= '<div class="article-image stock_portfolio_iframe stock_portfolio_excel_iframe">';
		// 	$string .= '<iframe src="'.$stock_portfolio_iframe_URL.'" height="750px" ></iframe>';
		$string .= '<iframe src="https://view.officeapps.live.com/op/embed.aspx?src='.$stock_excel_sheet_iframe_url.'" height="450px" ></iframe>';
		$string .= '</div>';
	}

	if (( \Elementor\Plugin::$instance->editor->is_edit_mode()) || (\Elementor\Plugin::$instance->preview->is_preview_mode() )) {
		$article_content = $queried_post->post_content;
	}else {
		$article_content = apply_filters('the_content', $article_content);
		$article_content = str_replace(']]>', ']]&gt;', $article_content);
	}		

	// 	$article_content = apply_filters('the_content', $article_content);
	// 	$article_content = str_replace(']]>', ']]&gt;', $article_content);

	$table_shortcode = get_field('insert_shortcode', $article_id);

	if(!empty($table_shortcode)){
		$string .= '<div class="table-btn">';
		$string .= '<a href="#" class="table_full_view">See Full Table</a>';
		$string .= '<div class="table-view-pg-cls">';
		$string .= do_shortcode($table_shortcode);
		$string .= '</div>';
		$string .= '</div>';
		$string .= '<div id="table_full_view_main_cls" class="modal">';
		$string .= '<div class="modal-content">';
		$string .= '<span class="close">&times;</span>';
		$string .= '<div class="table-full-view-sec-cls">';
		$string .= '<a class="print-table-popup"><i class="fa fa-print" aria-hidden="true"></i> Print</a>';
		$string .= do_shortcode($table_shortcode);
		$string .= '</div>';
		$string .= '</div>';
		$string .= '</div>';
	}


	$string .= '<div class="article-content">';
	$string .= $article_content;
	$string .= '</div>';

	$string .= '</div>';

	return $string;

}

/**
 ** Latest Articles Single Shortcode
 **/

/**
 ** Stock Portfolio Sidebar Menu URL
 **/

add_shortcode( 'stock_menu', 'stock_menu_permalink' );
// The Permalink Shortcode
function stock_menu_permalink() {
	ob_start();
	global $post;
	echo get_permalink($post->ID).'?stock_portfolio_position=SP';
	return ob_get_clean();
}

// Alerts & Updates Main Section URL 
add_shortcode( 'alerts_updates_menu', 'alerts_updates_menu_permalink' );
// The Permalink Shortcode
function alerts_updates_menu_permalink() {
	ob_start();
	global $post;
	echo get_permalink($post->ID).'?alerts_updates=au';
	return ob_get_clean();
}

/**
 ** Stock Single URL
 **/

add_shortcode( 'stock_single_link', 'stock_single_url' );
// The Permalink Shortcode
function stock_single_url() {
	ob_start();
	global $wp;
	$id = get_the_ID();
	$current_url = home_url(add_query_arg(array(),$wp->request));
	echo $current_url.'/?stock_portfolio_id='.$id;
	return ob_get_clean();
}

/**
 ** Stock Portfolio Sidebar Menu URL
 **/
add_shortcode('stock_file_upload', 'stock_file_upload_fn');
function stock_file_upload_fn(){
	$string = '';
	if(isset($_REQUEST['stock_portfolio_id'])){
		$stock_portfolio_id = $_REQUEST['stock_portfolio_id'];
		$stock_single_file_upload = get_field('stock_file_upload', $stock_portfolio_id);
		if(isset($stock_single_file_upload['url']) && $stock_single_file_upload['url'] != ''):
		$string .= '<div class="article-image stock_portfolio_iframe">';
		// 	$string .= '<iframe src="'.$stock_portfolio_iframe_URL.'" height="750px" ></iframe>';
		if(strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') || stristr($_SERVER['HTTP_USER_AGENT'], 'Macintosh')){
			$string .= '<iframe src="https://docs.google.com/viewer?url='.$stock_single_file_upload['url'].'&embedded=true" height="750px"></iframe> ';
		}else {
			$string .= '<iframe src="'.$stock_single_file_upload['url'].'" height="750px" ></iframe>';
		}
		$string .= '</div>';
		endif; 
	}else{
		$get_post_object = get_queried_object();
		// 	$iframe_url = get_post_meta($get_post_object->ID,'sheet_iframe_url', true);
		$stock_file_upload = get_field('stock_file_upload', $get_post_object->ID);	
		if(isset($stock_file_upload['url']) && $stock_file_upload['url'] != ''){
			if(strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') || stristr($_SERVER['HTTP_USER_AGENT'], 'Macintosh')){
				$string .= '<iframe src="https://docs.google.com/viewer?url='.$stock_file_upload['url'].'&embedded=true" height="750px" class="stock-upload-file"></iframe> ';
			}else {
				$string .= '<iframe src="'.$stock_file_upload['url'].'" height="750px" class="stock-upload-file"></iframe>'; 
			}

		}
	}
	return $string;
}

add_shortcode('stock_excel_file_upload', 'stock_excel_file_upload_fn');
function stock_excel_file_upload_fn(){
	$string = '';
	if(isset($_REQUEST['stock_portfolio_id'])){
		$stock_portfolio_id = $_REQUEST['stock_portfolio_id'];
		$stock_single_excel_file_upload = get_field('stock_excel_file_upload', $stock_portfolio_id);
		$stock_excel_sheet_iframe_url = $stock_single_excel_file_upload['url'];
		if(!empty($stock_excel_sheet_iframe_url)){
			$string .= '<div class="article-image stock_portfolio_iframe stock_portfolio_excel_iframe">';
			// 	$string .= '<iframe src="'.$stock_portfolio_iframe_URL.'" height="750px" ></iframe>';
			$string .= '<iframe src="https://view.officeapps.live.com/op/embed.aspx?src='.$stock_excel_sheet_iframe_url.'" height="450px" ></iframe>';
			$string .= '</div>';

		}
		return $string;
	}
}

add_shortcode( 'stock_portfolio_single', 'display_stock_portfolio_post_single' );

function display_stock_portfolio_post_single(){

	$stock_portfolio_id = $_REQUEST['stock_portfolio_id'];

	$queried_post = get_post($stock_portfolio_id);
	$title = $queried_post->post_title;
	$meta = get_post_meta($stock_portfolio_id);
	$stock_portfolio_date = get_the_date( 'l, F j, Y', $stock_portfolio_id );
	$author_id = get_post_field( 'post_author', $stock_portfolio_id );
	$author_name = get_the_author_meta( 'display_name', $author_id );


	$post_thumbnail_id = get_post_thumbnail_id( $stock_portfolio_id );
	$img_ar =  wp_get_attachment_image_src( $post_thumbnail_id, 'full' );


	if (( \Elementor\Plugin::$instance->editor->is_edit_mode()) || (\Elementor\Plugin::$instance->preview->is_preview_mode() )) {
		$stock_portfolio_content = $queried_post->post_content;
	}else {
		$stock_portfolio_content = $queried_post->post_content;
		$stock_portfolio_content = apply_filters('the_content', $stock_portfolio_content);
		$stock_portfolio_content = str_replace(']]>', ']]&gt;', $stock_portfolio_content);
	}	

	$stock_single_file_upload = get_field('stock_file_upload', $stock_portfolio_id);
	$stock_single_excel_file_upload = get_field('stock_excel_file_upload', $stock_portfolio_id);

	ob_start();
?>

<div class="latest-article-single-section">
	<div class="article-title">
		<h4><?php echo $title ?></h4>
	</div>

	<div class="article-meta">
		<h4><i aria-hidden="true" class="fas fa-user-alt"></i> By <?php echo $author_name ?></h4>
		<h4><i aria-hidden="true" class="far fa-calendar"></i> <?php echo $stock_portfolio_date ?></h4>
		<?php $terms = get_the_terms( $queried_post , 'alerts_categories' ); 
	if(!empty($terms)){
		?>
		<h4><i aria-hidden="true" class="fas fa-list"></i>
			<?php foreach ( $terms as $term ) {echo $term->name;} ?>
		</h4>
		<?php
	}
		?>
	</div>
	<?php
	if(isset($stock_single_file_upload['url']) && $stock_single_file_upload['url'] != '' && !has_shortcode($queried_post->post_content, 'stock_file_upload')):
	?>
	<div class="article-image stock_portfolio_iframe">
		<?php 
	if(strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') || stristr($_SERVER['HTTP_USER_AGENT'], 'Macintosh')){
		?>
		<iframe src="https://docs.google.com/viewer?url=<?php echo $stock_single_file_upload['url'] ?>&embedded=true"	height="668px">	</iframe> 
		<?php
	}else {
		?>
		<iframe src="<?php echo $stock_single_file_upload['url'] ?>" height="750px" ></iframe>
		<?php
	}
		?>
		<!-- 		<iframe src="<?php //echo $stock_single_file_upload['url'] ?>" height="750px" ></iframe> -->
	</div>
	<?php endif; 

	$stock_excel_sheet_iframe_url = $stock_single_excel_file_upload['url'];
	if(!empty($stock_excel_sheet_iframe_url) && !has_shortcode($stock_portfolio_content, 'stock_excel_file_upload')){
	?>
	<div class="article-image stock_portfolio_iframe stock_portfolio_excel_iframe">
		<iframe src="https://view.officeapps.live.com/op/embed.aspx?src=<?php echo $stock_excel_sheet_iframe_url ?>" height="450px" ></iframe>
	</div>
	<?php } ?>

	<div class="article-content">
		<!-- 		<p><?php //echo $queried_post->post_excerpt; ?></p> -->
		<?php echo $stock_portfolio_content; ?>
	</div>
	<?php
	if(!has_shortcode($queried_post->post_content, 'trade_alert_multiple_pdf')){
	?>
	<div class="alerts-multiple-pdf-cls">
		<?php
		if (have_rows('extra_pdf_upload', $stock_portfolio_id)) {
			while (have_rows('extra_pdf_upload', $stock_portfolio_id)) {
				the_row();
				$file = get_sub_field('upload_file', $stock_portfolio_id);
		?>
		<iframe src="<?php echo $file['url'] ?>" height="750px" ></iframe>
		<?php
			}
		}
		?>
	</div>
	<?php } ?>
</div>
<?php
	$output_string = ob_get_contents();
	ob_end_clean();
	return $output_string;

}

function get_the_content_with_formatting ($more_link_text = '(more...)', $stripteaser = 0, $more_file = '') {
	$content = get_the_content($more_link_text, $stripteaser, $more_file);
	$content = apply_filters('the_content', $content);
	$content = str_replace(']]>', ']]&gt;', $content);
	return $content;
}


// Trade alerts Multiple pdf show shortcode
add_shortcode('trade_alert_multiple_pdf', 'trade_alert_multiple_pdf_fun');
function trade_alert_multiple_pdf_fun(){
	ob_start();
	if(isset($_REQUEST['stock_portfolio_id'])){
		$stock_portfolio_id = $_REQUEST['stock_portfolio_id'];
?>
<div class="alerts-multiple-pdf-cls">
	<?php
		if (have_rows('extra_pdf_upload', $stock_portfolio_id)) {
			while (have_rows('extra_pdf_upload', $stock_portfolio_id)) {
				the_row();
				$file = get_sub_field('upload_file', $stock_portfolio_id);
	?>
	<iframe src="<?php echo $file['url'] ?>" height="750px" ></iframe>
	<?php
			}
		}
	?>
</div>
<?php
	}
	$output_string = ob_get_contents();
	ob_end_clean();
	return $output_string;
}
// Trade alerts Multiple pdf show shortcode

/**
 ** Stock Portfolio Single Content
 **/

add_shortcode( 'stock_portfolio_single_content', 'stock_portfolio_content' );

function stock_portfolio_content(){
	$get_post_object = get_queried_object();
	// 	$iframe_url = get_post_meta($get_post_object->ID,'sheet_iframe_url', true);
	$stock_file_upload = get_field('stock_file_upload', $get_post_object->ID);	
	$content = get_the_content($get_post_object->ID); 
	if(isset($stock_file_upload['url']) && $stock_file_upload['url'] != '' && !has_shortcode($content, 'stock_file_upload')){
		if(strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') || stristr($_SERVER['HTTP_USER_AGENT'], 'Macintosh')){
?>
<iframe src="https://docs.google.com/viewer?url=<?php echo $stock_file_upload['url']; ?>&embedded=true"	height="668px">	</iframe>
<?php }else {
?>
<iframe src="<?php echo $stock_file_upload['url']; ?>" class="stock-upload-file test" height="750px" ></iframe>
<?php
			} ?>
<br>
<?php 
	}


	$content = apply_filters('the_content', $content);
	$content = str_replace(']]>', ']]&gt;', $content);
	echo $content;	
?>
<?php
}

add_shortcode( 'stock_portfolio_related_single_content', 'stock_portfolio_related_content' );

function stock_portfolio_related_content(){
	$get_post_object = get_queried_object();
	$content = get_the_content($get_post_object->ID); 
	$content = apply_filters('the_content', $content);
	$content = str_replace(']]>', ']]&gt;', $content);
	echo wp_trim_words( $content, 12 );	
}

// Bonus Report Content
add_shortcode( 'bonus_report_related_main_content', 'bonus_report_related_content' );

function bonus_report_related_content(){
	$get_post_object = get_queried_object();
	$content = get_the_content($get_post_object->ID); 
	$content = apply_filters('the_content', $content);
	$content = str_replace(']]>', ']]&gt;', $content);
	echo wp_trim_words( $content, 10 );	
}

/**
 ** Trade Alerts Sidebar Menu URL
 **/

add_shortcode( 'trade_alert_menu', 'trade_alert_menu_permalink' );
// The Permalink Shortcode
function trade_alert_menu_permalink() {
	ob_start();
	global $post;
	echo get_permalink($post->ID).'?product_trade_alerts=TA';
	return ob_get_clean();
}

/**
 ** Trade Single URL
 **/

add_shortcode( 'trade_single_link', 'trade_single_url' );
// The Permalink Shortcode
function trade_single_url() {
	ob_start();
	global $wp;
	$id = get_the_ID();
	$current_url = home_url(add_query_arg(array(),$wp->request));
	echo $current_url.'/?trade_alerts_id='.$id;
	return ob_get_clean();
}

/**
 ** Trade Alert Single Content
 **/

add_shortcode( 'trade_alert_single_content', 'trade_alert_content' );

function trade_alert_content(){
	$get_post_object = get_queried_object();
	$trade_img_url = get_the_post_thumbnail_url($get_post_object->ID);
	if(!empty($trade_img_url)){
?>
<p><img src="<?php echo get_the_post_thumbnail_url($get_post_object->ID); ?>"></p><?php } ?>
<?php
	$content = get_the_content($get_post_object->ID); 
	$content = apply_filters('the_content', $content);
	$content = str_replace(']]>', ']]&gt;', $content);
	echo $content;	
}


/**
 ** Trade Alert on Product Single
 **/

add_action( 'powerpack/query/main_trade_alert', function( $query ) {
	$get_post_object = get_queried_object();
	$latest_main_trade_alert_ids = get_post_meta($get_post_object->ID, 'select_trade_alert', true);
	if(!empty($latest_main_trade_alert_ids)){
		$query->set( 'post__in', $latest_main_trade_alert_ids );
	}else{
		$query->set( 'post__in', array(0) );
	}
} );

add_action( 'powerpack/query/latest_trade_alert', function( $query ) {
	$get_post_object = get_queried_object();
	$latest_trade_alert_ids = get_post_meta($get_post_object->ID, 'select_trade_alert', true);
	$search_text = isset($_REQUEST['keyword']) && !empty($_REQUEST['keyword']) ? $_REQUEST['keyword'] : '';
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	$query->set( 'order', 'DSC');
	$query->set( 'orderby' , 'publish_date');
	$query->set( 's', $search_text );
	$query->set( 'post_status', 'publish');
	// 	$query->set( 'offset', '1' );
	// 	$query->set( 'post__in', $latest_trade_alert_ids );
	$id_not_in = isset($_REQUEST['trade_alerts_id']) && !empty($_REQUEST['trade_alerts_id']) ? $_REQUEST['trade_alerts_id'] : '';
	if(!empty($latest_trade_alert_ids)){
		$query->set( 'post__in', array_diff($latest_trade_alert_ids,array($id_not_in)) );
	}else{
		$query->set( 'post__in', array(0) );
	}
	if (isset($_REQUEST['months']) && !empty($_REQUEST['months']) && isset($_REQUEST['years']) && !empty($_REQUEST['years'])){
		$date_query = [
			'relation' => 'AND',
			[
				'year' 	=> $_REQUEST['years'],
				'month' 	=> $_REQUEST['months'],
				'compare'   => '=',
			]
		];
		$query->set('date_query',$date_query);
	}elseif (isset($_REQUEST['years']) && !empty($_REQUEST['years'])){
		$date_query = [
			[
				'year'  	=> $_REQUEST['years'],
				'compare'   => '=',
			]
		];
		$query->set('date_query',$date_query);
	}elseif (isset($_REQUEST['months']) && !empty($_REQUEST['months'])){
		$date_query = [
			[
				'month' 	=> $_REQUEST['months'],
				'compare'   => '=',
			]
		];
		$query->set('date_query',$date_query);
	}
} );

/**
 ** Trade Alert on Product Single
 **/

/**
 ** Latest Articles Single Shortcode
 **/

add_shortcode( 'trade_alert_single', 'display_trade_alert_post_single' );

function display_trade_alert_post_single(){

	$trade_alerts_id = $_REQUEST['trade_alerts_id'];

	$queried_post = get_post($trade_alerts_id);
	$title = $queried_post->post_title;
	$meta = get_post_meta($trade_alerts_id);
	$trade_alerts_date = get_the_date( 'l, F j, Y', $trade_alerts_id );
	$author_id = get_post_field( 'post_author', $trade_alerts_id );
	$author_name = get_the_author_meta( 'display_name', $author_id );
	$post_thumbnail_id = get_post_thumbnail_id( $trade_alerts_id );
	$img_ar =  wp_get_attachment_image_src( $post_thumbnail_id, 'full' );
	$trade_alerts_content = $queried_post->post_content;				 

	$string = '';

	$string .= '<div class="latest-article-single-section">';
	$string .= '<div class="article-title">';
	$string .= '<h4>'.$title.'</h4>';
	$string .= '</div>';

	$string .= '<div class="article-meta">';
	$string .= '<h4><i aria-hidden="true" class="fas fa-user-alt"></i> By '.$author_name.'</h4>';
	$string .= '<h4><i aria-hidden="true" class="far fa-calendar"></i> '.$trade_alerts_date.'</h4>';
	$string .= '</div>';

	if(!empty($img_ar)){
		$string .= '<div class="article-image">';
		$string .= '<img src="'.$img_ar[0].'" >';
		$string .= '</div>';
	}

	$trade_alerts_content = apply_filters('the_content', $trade_alerts_content);
	$trade_alerts_content = str_replace(']]>', ']]&gt;', $trade_alerts_content);

	$string .= '<div class="article-content">';
	$string .= $trade_alerts_content;
	$string .= '</div>';

	$string .= '</div>';

	return $string;

}

/**
 ** Trade Alert Single Shortcode
 **/

/**
 ** Welcome Video Sidebar Menu URL
 **/

add_shortcode( 'welcome_video_menu', 'welcome_video_menu_permalink' );
// The Permalink Shortcode
function welcome_video_menu_permalink() {
	ob_start();
	global $post;
	echo get_permalink($post->ID).'?product_welcome_video=WV';
	return ob_get_clean();
}

add_shortcode( 'welcome_video_stream_menu', 'welcome_video_stream_menu_permalink' );
// The Permalink Shortcode
function welcome_video_stream_menu_permalink() {
	ob_start();
	global $post;
	echo get_permalink($post->ID).'?product_video_stream=VS';
	return ob_get_clean();
}

/**
 ** Welcome Video Single Content
 **/

add_shortcode( 'welcome_video_single_content', 'welcome_video_content' );

function welcome_video_content(){
	$get_post_object = get_queried_object();
	$content = get_the_content($get_post_object->ID); 
	$content = apply_filters('the_content', $content);
	$content = str_replace(']]>', ']]&gt;', $content);
	echo $content;	
}

/**
 ** Welcome Video Content
 **/

add_shortcode( 'welcome_video_content', 'welcome_video_caption' );

function welcome_video_caption(){
	$get_post_object = get_queried_object();	
	// 	$welcome_id = $_REQUEST['product_welcome_video'];
	$meta = get_post_meta($get_post_object->ID);	
	$select_welcome_video_type = get_field('select_video_type', $get_post_object->ID);	
	$video_url = get_field('video',$get_post_object->ID);
	$video_script = get_field('video_script',$get_post_object->ID);
	if($select_welcome_video_type == 'videourl'){
		if(!empty($video_url)){
			echo '<iframe src="'.$video_url.'" height="450px"></iframe>';
		}
	}elseif ($select_welcome_video_type == 'script'){
		if(!empty($video_script)){
			echo $video_script;
		}
	}
?>
<?php
}

/**
 ** Expect Video Content
 **/

add_shortcode( 'welcome_expect_video_content', 'welcome_expect_video_caption' );

function welcome_expect_video_caption(){
	$get_post_object = get_queried_object();	
	// 	$welcome_id = $_REQUEST['product_welcome_video'];
	$meta = get_post_meta($get_post_object->ID);	
	$select_welcome_video_type = get_field('expect_select_video_type', $get_post_object->ID);	
	$video_url = get_field('expect_video_url',$get_post_object->ID);
	$video_script = get_field('expect_video_script',$get_post_object->ID);
	if($select_welcome_video_type == 'exvideourl'){
		if(!empty($video_url)){
			echo '<iframe src="'.$video_url.'" height="450px"></iframe>';
		}
	}elseif ($select_welcome_video_type == 'exscript'){
		if(!empty($video_script)){
			echo $video_script;
		}
	}
?>
<?php
}



/**
 ** Welcome Video on Product Single
 **/

add_action( 'powerpack/query/main_welcome_video', function( $query ) {
	$get_post_object = get_queried_object();
	$latest_main_trade_alert_ids = get_post_meta($get_post_object->ID, 'select_welcome_video', true);
	if(!empty($latest_main_trade_alert_ids)){
		$query->set( 'post__in', $latest_main_trade_alert_ids );
	}else{
		$query->set( 'post__in', array(0) );
	}
} );

/**
 ** Trade Alert on Product Single
 **/

/**
 ** What to Expect from this Service Sidebar Menu URL
 **/

add_shortcode( 'expect_from_this_service_menu', 'expect_from_this_service_menu_permalink' );
// The Permalink Shortcode
function expect_from_this_service_menu_permalink() {
	ob_start();
	global $post;
	echo get_permalink($post->ID).'?expect_from_this_service=ES';
	return ob_get_clean();
}

/**
 ** Expect Post Query on Product Single
 **/

add_action( 'powerpack/query/expect_from_this_service', function( $query ) {
	$get_post_object = get_queried_object();
	$expect_from_this_service_ids = get_post_meta($get_post_object->ID, 'select_expect_service_video', true);
	if(!empty($expect_from_this_service_ids)){
		$query->set( 'post__in', $expect_from_this_service_ids );
	}else{
		$query->set( 'post__in', array(0) );
	}
} );

/**
 ** Expect Post Query on Product Single
 **/

/**
 ** Bonus Reports Sidebar Menu URL
 **/

add_shortcode( 'bonus_report_menu', 'bonus_report_menu_permalink' );
// The Permalink Shortcode
function bonus_report_menu_permalink() {
	ob_start();
	global $post;
	echo get_permalink($post->ID).'?bonus_report=BR';
	return ob_get_clean();
}

/**
 ** Bonus Reports Single URL
 **/

add_shortcode( 'bonus_report_single_link', 'bonus_report_single_url' );
// The Permalink Shortcode
function bonus_report_single_url() {
	ob_start();
	global $wp; 
	$id = get_the_ID();
	$url = (substr(preg_replace('/[0-9]+/', '', $wp->request), -1) == '/') ? substr($wp->request, 0, -1) : $wp->request.'/';
	$current_url = home_url(add_query_arg(array(), $url));
	echo $current_url.'?bonus_report_id='.$id;
	return ob_get_clean();
}

/**
 ** Bonus Report on Product Single
 **/

add_action( 'powerpack/query/main_bonus_report', function( $query ) {
	$get_post_object = get_queried_object();
	$bonus_reports_ids = get_post_meta($get_post_object->ID, 'select_bonus_reports', true);
	if(!empty($bonus_reports_ids)){
		$query->set( 'post__in', $bonus_reports_ids );
	}else{
		$query->set( 'post__in', array(0) );
	}
} );

add_action( 'powerpack/query/related_bonus_reports', function( $query ) {
	$get_post_object = get_queried_object();
	$related_bonus_reports_ids = get_post_meta($get_post_object->ID, 'select_bonus_reports', true);
	$search_text = isset($_REQUEST['keyword']) && !empty($_REQUEST['keyword']) ? $_REQUEST['keyword'] : '';
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	$query->set( 'order', 'ASC');
	$query->set( 'orderby' , 'menu_order');
	//print_r(keywordsearch($search_text));
	if(!empty(keywordsearch($search_text)) && is_array(keywordsearch($search_text)))
	{
		$meta_query = array(
			'relation' => 'OR',
			array(
				'Key'    => 'add_keyword',
				'value'    => $search_text,
				'compare'  => 'LIKE'
			)
		);
		$query->set( 'meta_query', $meta_query );
	}else{
		$query->set( 's', $search_text );
	}
	$query->set( 'post_status', 'publish');
	// 	$query->set( 'offset', '1' );
	// 	$query->set( 'post__in', $related_bonus_reports_ids );
	$id_not_in = isset($_REQUEST['bonus_report_id']) && !empty($_REQUEST['bonus_report_id']) ? $_REQUEST['bonus_report_id'] : '';
	if(!empty($related_bonus_reports_ids)){
		$query->set( 'post__in', array_diff($related_bonus_reports_ids,array($id_not_in)) );
	}else{
		$query->set( 'post__in', array(0) );
	}
	if (isset($_REQUEST['months']) && !empty($_REQUEST['months']) && isset($_REQUEST['years']) && !empty($_REQUEST['years'])){
		$date_query = [
			'relation' => 'AND',
			[
				'year' 	=> $_REQUEST['years'],
				'month' 	=> $_REQUEST['months'],
				'compare'   => '=',
			]
		];
		$query->set('date_query',$date_query);
	}elseif (isset($_REQUEST['years']) && !empty($_REQUEST['years'])){
		$date_query = [
			[
				'year'  	=> $_REQUEST['years'],
				'compare'   => '=',
			]
		];
		$query->set('date_query',$date_query);
	}elseif (isset($_REQUEST['months']) && !empty($_REQUEST['months'])){
		$date_query = [
			[
				'month' 	=> $_REQUEST['months'],
				'compare'   => '=',
			]
		];
		$query->set('date_query',$date_query);
	}
} );

/**
 ** Bonus Report on Product Single
 **/

/**
 ** Bonus Report Single Shortcode
 **/

add_shortcode( 'bonus_report_single', 'display_bonus_report_post_single' );

function display_bonus_report_post_single(){
	$string = '';
	ob_start();

	if(isset($_REQUEST['bonus_report_id'])){
		$bonus_report_id = $_REQUEST['bonus_report_id'];

		$queried_post = get_posts(array(
			'include' => $bonus_report_id,
			'post_type' => 'bonus_reports',
		));

		if ( $queried_post ) {
			foreach ( $queried_post as $post ) :
			setup_postdata( $post ); 

?>
<div class="latest-article-single-section">
	<div class="article-title">
		<h4><?php echo $post->post_title; ?></h4>
	</div>
	<div class="article-content">
		<!-- 		<p><?php //echo $post->post_excerpt; ?></p> -->
		<?php the_content(); ?>
	</div>

	<?php
			$bonus_reports_pdf_file_upload = get_field('bonus_reports_pdf', $bonus_report_id);
			if(isset($bonus_reports_pdf_file_upload['url']) && $bonus_reports_pdf_file_upload['url'] != '' && !has_shortcode($post->post_content, 'btm_pdf_viewer')):
	?>
	<div class="article-image stock_portfolio_iframe">
		<iframe src="<?php echo $bonus_reports_pdf_file_upload['url'] ?>" height="750px" ></iframe>
	</div>
	<?php endif; 
	?>
</div>
<?php
			endforeach; 
			wp_reset_postdata();
		}
	}
	$string = ob_get_contents();
	ob_end_clean();
	return $string;
}

/**
 ** Bonus Report Single Shortcode
 **/

// function isIphone($user_agent=NULL) {
//     if(!isset($user_agent)) {
//         $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
//     }
//     return (strpos($user_agent, 'iPhone') !== FALSE);
// }


// Bonus report pdf shortcode
add_shortcode('btm_pdf_viewer', 'bonus_report_file_upload_fn');
function bonus_report_file_upload_fn(){
	$string = '';
	if(isset($_REQUEST['bonus_report_id'])){
		$bonus_report_id = $_REQUEST['bonus_report_id'];
		$bonus_reports_pdf_file_upload = get_field('bonus_reports_pdf', $bonus_report_id);
		if(isset($bonus_reports_pdf_file_upload['url']) && $bonus_reports_pdf_file_upload['url'] != ''):
		$string .= '<div class="article-image stock_portfolio_iframe">';
		if(strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') || stristr($_SERVER['HTTP_USER_AGENT'], 'Macintosh')){
			$string .= '<iframe src="https://docs.google.com/viewer?url='.$bonus_reports_pdf_file_upload['url'].'&embedded=true"	height="668px">	</iframe> ';
		}else {
			$string .= '<iframe src="'.$bonus_reports_pdf_file_upload['url'].'"	height="668px">	</iframe> ';
		}		
		// 		$string .= '<iframe src="'.$stock_single_file_upload['url'].'" height="750px" ></iframe>';
		$string .= '</div>';
		endif; 
	}
	return $string;
}



/**
 ** Shortcode for Main Pages - Product Single
 **/

add_shortcode( 'shortcode_for_product_articles', 'shortcode_for_product_single_articles' );

function shortcode_for_product_single_articles() {
	ob_start();

	if(is_user_logged_in())
	{
		global $wp_query; $post_id = $wp_query->get_queried_object_id();
		$user = new WP_User( get_current_user_id() );
		if(in_array($post_id, $GLOBALS['purchased_product_ids']) || !empty(array_intersect(array('customerserviceagent', 'administrator', 'editor', 'customerserviceeditor'), $user->roles)))
		{
			if (isset($_REQUEST['stock_portfolio_position'])=='SP'){
				echo do_shortcode( '[elementor-template id="3097"]' );
			}elseif (isset($_REQUEST['alerts_updates'])=='au'){
				echo do_shortcode( '[elementor-template id="120906"]' );
			}elseif (isset($_REQUEST['product_welcome_video'])=='WV'){
				echo do_shortcode( '[elementor-template id="3168"]' );
			}elseif (isset($_REQUEST['product_video_stream'])=='VS'){
				echo do_shortcode( '[elementor-template id="309977"]' );
			}elseif (isset($_REQUEST['product_trade_alerts'])=='TA'){
				echo do_shortcode( '[elementor-template id="3141"]' );
			}elseif (isset($_REQUEST['bonus_report'])=='BR'){
				echo do_shortcode( '[elementor-template id="3236"]' );
			}elseif (isset($_REQUEST['expect_from_this_service'])=='ES'){
				echo do_shortcode( '[elementor-template id="3199"]' );
			}elseif (isset($_REQUEST['search_tags'])){
				echo do_shortcode( '[elementor-template id="145704"]' );
			}elseif (isset($_REQUEST['article_id']) && !empty($_REQUEST['article_id']) ){
				//echo do_shortcode( '[elementor-template id="3041"]' );
				// 				echo do_shortcode( '[latest_articles_single]' );
				echo do_shortcode( '[elementor-template id="167146"]' );
			}elseif (isset($_REQUEST['stock_portfolio_id']) && !empty($_REQUEST['stock_portfolio_id']) ){
				echo do_shortcode( '[elementor-template id="3116"]' );
			}elseif (isset($_REQUEST['trade_alerts_id']) && !empty($_REQUEST['trade_alerts_id']) ){
				echo do_shortcode( '[elementor-template id="3146"]' );
			}elseif (isset($_REQUEST['bonus_report_id']) && !empty($_REQUEST['bonus_report_id']) ){
				echo do_shortcode( '[elementor-template id="3246"]' );
			}else {
				echo do_shortcode('[elementor-template id="3033"]');
			}
		}else{
			wp_safe_redirect( esc_url(site_url('my-product')) );
			exit;
		}
	}else{
		wp_safe_redirect( esc_url(site_url()) );
		exit;
	}
	return ob_get_clean();
}

/**
 ** Shortcode for Latest Articles On Welcome Video Pages - Product Single
 **/

/**
 ** Shortcode for Latest Articls on Video Pages
 **/

add_shortcode( 'latest_articles_on_welcome_video_page', 'shortcode_for_latest_articles_on_welcome_video_page' );

function shortcode_for_latest_articles_on_welcome_video_page() {
	ob_start();

	if (isset($_REQUEST['product_welcome_video'])=='WV'){
		echo do_shortcode( '[elementor-template id="3261"]' );
	}
	if (isset($_REQUEST['expect_from_this_service'])=='ES'){
		echo do_shortcode( '[elementor-template id="3261"]' );
	}

	return ob_get_clean();
}

/**
 ** Shortcode for Latest Articls on Video Pages
 **/

/**
 ** Shortcode for Breadcrumb Section - Product Single
 **/

add_shortcode( 'article_breadcrumb', 'shortcode_for_article_breadcrumb' );

function shortcode_for_article_breadcrumb() {
	ob_start();

	if (isset($_REQUEST['product_welcome_video'])=='WV'){
		echo 'Welcome Video';
	}elseif (isset($_REQUEST['product_video_stream'])=='VS'){
		echo 'Video Stream';
	}elseif (isset($_REQUEST['expect_from_this_service'])=='ES'){
		echo 'What to Expect from this Service';
	}elseif (isset($_REQUEST['stock_portfolio_position'])=='SP'){
		echo 'Stock Portfolio & Positions';
	}elseif (isset($_REQUEST['stock_portfolio_id']) && !empty($_REQUEST['stock_portfolio_id']) ){
		echo 'Stock Portfolio & Positions';
	}elseif (isset($_REQUEST['product_trade_alerts'])=='TA'){
		echo 'Trade Alerts';
	}elseif (isset($_REQUEST['trade_alerts_id']) && !empty($_REQUEST['trade_alerts_id']) ){
		echo 'Trade Alerts';
	}elseif (isset($_REQUEST['bonus_report'])=='BR'){
		echo 'Bonus Reports';
	}elseif (isset($_REQUEST['search_tags'])){
		echo 'Tags List';
	}elseif (isset($_REQUEST['bonus_report_id']) && !empty($_REQUEST['bonus_report_id']) ){
		echo 'Bonus Reports';
	} else {
		echo 'Latest Article';
	}

	return ob_get_clean();
}

/**
 ** Shortcode for Breadcrumb Section - Product Single
 **/


/**
 ** Sidebar Menu Active Shortcode
 **/

add_shortcode( 'sidebar_active_menu_class', 'sidebar_active_menu' );

function sidebar_active_menu() {
	ob_start();

	if (isset($_REQUEST['product_welcome_video'])=='WV'){
		echo 'welcome-video-active';
	}elseif (isset($_REQUEST['product_video_stream'])=='VS'){
		echo 'video_stream-active';
	}elseif (isset($_REQUEST['expect_from_this_service'])=='ES'){
		echo 'expect-service-active';
	}elseif (isset($_REQUEST['bonus_report'])=='BR'){
		echo 'bonus-reports';
	}elseif (isset($_REQUEST['bonus_report_id']) && !empty($_REQUEST['bonus_report_id']) ){
		echo 'bonus-reports';
	}else {
		echo '';
	}

	return ob_get_clean();
}

add_shortcode( 'sidebar_active_menu_class_second', 'sidebar_active_menu_second_one' );

function sidebar_active_menu_second_one() {
	ob_start();

	if (isset($_REQUEST['stock_portfolio_position'])=='SP'){
		echo 'stock-portfolio-active';
	}elseif (isset($_REQUEST['product_video_stream'])=='VS'){
		echo 'video_stream-active';
	}elseif (isset($_REQUEST['article_id']) && !empty($_REQUEST['article_id']) ){
		echo 'stock-portfolio-active';
	}elseif (isset($_REQUEST['product_trade_alerts'])=='TA'){
		echo 'trade-alerts-active';
	}elseif (isset($_REQUEST['tags_name'])=='tn'){
		echo '';
	}elseif (isset($_REQUEST['trade_alerts_id']) && !empty($_REQUEST['trade_alerts_id']) ){
		echo 'trade-alerts-active';
	}elseif (isset($_REQUEST['stock_portfolio_id']) && !empty($_REQUEST['stock_portfolio_id']) ){
		echo 'alerts-updates-active';
	}elseif (isset($_REQUEST['alerts_updates']) && !empty($_REQUEST['alerts_updates']) ){
		echo 'alerts-updates-active';
	} else {
		echo 'recent-alerts-active';
	}

	return ob_get_clean();
}

// Hide Month Search on Bonus Report
add_shortcode( 'hide_month_search_filter', 'hide_month_search_filter_func' );

function hide_month_search_filter_func() {
	ob_start();

	if (isset($_REQUEST['bonus_report'])=='BR'){
		echo 'hide-month-search_filter';
	}elseif (isset($_REQUEST['bonus_report_id']) && !empty($_REQUEST['bonus_report_id']) ){
		echo 'hide-month-search_filter';
	}else {
		echo '';
	}

	return ob_get_clean();
}

// Months Search Sidebar Cls
add_shortcode( 'sidebar_month_search_fn', 'sidebar_month_search' );

function sidebar_month_search() {
	ob_start();

	if (isset($_REQUEST['bonus_report']) && !empty($_REQUEST['bonus_report']) || isset($_REQUEST['bonus_report_id']) && !empty($_REQUEST['bonus_report_id']) || isset($_REQUEST['alerts_updates']) && !empty($_REQUEST['alerts_updates']) || isset($_REQUEST['stock_portfolio_id']) && !empty($_REQUEST['stock_portfolio_id']) ){
		echo 'disply-visible-cls';
	} else {
		echo 'display-hidden-cls';
	}

	return ob_get_clean();
}

/**
 ** Sidebar Menu Active Shortcode
 **/

/**
 ** Search Page Link for Stock Portfolio Shortcode
 **/

add_shortcode( 'stock_portfolio_search_single_link', 'stock_portfolio_search_single' );
// The Permalink Shortcode
function stock_portfolio_search_single() {
	ob_start();
	global $wp;
	$id = get_the_ID();
	$current_url = home_url();
	$get_post_object = get_queried_object();
	$select_product = get_post_meta($get_post_object->ID,'select_product',true);
	// 	$post_page = get_post($select_product[0]); 
	if(empty($select_product)){
		echo '#';	
	}else{
		echo $current_url.'/btm_products/'.basename(get_permalink($select_product[0])).'/?stock_portfolio_id='.$id;	
	}
	//    echo $post_page->post_name;
	return ob_get_clean();
}

/**
 ** Search Page Link for Trade Alerts Shortcode
 **/

add_shortcode( 'trade_alerts_search_single_link', 'trade_alerts_search_single' );
// The Permalink Shortcode
function trade_alerts_search_single() {
	ob_start();
	global $wp;
	$id = get_the_ID();
	$current_url = home_url();
	$get_post_object = get_queried_object();
	$select_product = get_post_meta($get_post_object->ID,'select_product',true);
	if(empty($select_product)){
		echo '#';	
	}else{
		echo $current_url.'/btm_products/'.basename(get_permalink($select_product[0])).'/?trade_alerts_id='.$id;	
	}
	return ob_get_clean();
}

/**
 ** Search Page Link for Bonus Reports Shortcode
 **/

add_shortcode( 'bonus_reports_search_single_link', 'bonus_reports_search_single' );
// The Permalink Shortcode
function bonus_reports_search_single() {
	ob_start();
	global $wp;
	$id = get_the_ID();
	$current_url = home_url();
	$get_post_object = get_queried_object();
	$select_product = get_post_meta($get_post_object->ID,'select_product',true);
	if(empty($select_product)){
		echo '#';	
	}else{
		echo $current_url.'/btm_products/'.basename(get_permalink($select_product[0])).'/?bonus_report_id='.$id;	
	}
	return ob_get_clean();
}

/**
 ** Search Page Link for Bonus Reports Shortcode
 **/

add_shortcode( 'latest_articles_search_single_link', 'latest_articles_search_single' );
// The Permalink Shortcode
function latest_articles_search_single() {
	ob_start();
	global $wp;
	$id = get_the_ID();
	$current_url = home_url();
	$get_post_object = get_queried_object();
	$select_product = get_post_meta($get_post_object->ID,'select_product',true);
	if(empty($select_product)){
		echo '#';	
	}else{
		echo $current_url.'/btm_products/'.basename(get_permalink($select_product[0])).'/?article_id='.$id;	
	}
	return ob_get_clean();
}


/** Shortcode for PDF Viewer **/
// add_shortcode('btm_pdf_viewer', 'btm_show_pdf_viewer_fn');
// function btm_show_pdf_viewer_fn(){
// 	if(is_single()){
// 		$post_id = get_the_ID();	
// 	} 	 
// 	return 'Test';
// }

/**
 ** Pruchase One Product At a time Function
 **/

add_filter( 'woocommerce_add_to_cart_validation', 'bbloomer_only_one_in_cart', 9999, 2 );

function bbloomer_only_one_in_cart( $passed, $added_product_id ) {
	wc_empty_cart();
	return $passed;
}

/**
 ** Not Logged In User Redirection
 **/

add_action( 'template_redirect', 'redirect_non_logged_users_to_specific_page' );

function redirect_non_logged_users_to_specific_page() {

	if ( !is_user_logged_in() && is_page('my-product')) {
		wp_redirect(site_url()."/login/");
		exit;
	}
}

add_action( 'template_redirect', 'redirect_non_logged_users_your_account' );

function redirect_non_logged_users_your_account() {

	if ( !is_user_logged_in() && is_page('your-account')) {
		wp_redirect(site_url()."/login/");
		exit;
	}
}

add_action( 'template_redirect', 'redirect_non_logged_users_my_account' );

function redirect_non_logged_users_my_account() {

	if ( !is_user_logged_in() && (is_page('my-account') || is_page('support'))) {
		wp_redirect(site_url()."/login/");
		exit;
	}
}

function custom_redirect() {        
	global $post;

	if ( !empty($post->post_type) && $post->post_type == 'btm_products' && ! is_user_logged_in() ) {
		wp_redirect(site_url()."/login/");
		exit();
	}    
}
add_action("template_redirect","custom_redirect");

/**
 ** Not Logged In User Redirection
 **/

/**
 ** Thank you Message for Purchasing Membership of Email
 **/

function custom_memberships_thank_you(){
	$thank_you_message = "Thanks for purchasing a membership!" ;
	return $thank_you_message;

}
add_filter( 'woocommerce_memberships_thank_you_message', 'custom_memberships_thank_you' );

add_shortcode( 'archives', 'get_posts_years_array' );

function get_posts_years_array($atts) {
	global $post;
	extract( shortcode_atts( array(
		'select' => '',
		'post_type' =>''
	), $atts));
	$reports_ids = get_post_meta($post->ID, $select, true);
	$terms_year = array(
		'post_type' => array($post_type),
		'post__in'  => is_array($reports_ids) ? $reports_ids : array($reports_ids)
	);

	if(isset($_REQUEST['bonus_report']) && !empty($_REQUEST['bonus_report']) || isset($_REQUEST['bonus_report_id']) && !empty($_REQUEST['bonus_report_id']) ){
		$bonus_report_date_filter = isset($_REQUEST['bonus_report_id']) && !empty($_REQUEST['bonus_report_id']) ? $_REQUEST['bonus_report_id'] : $_REQUEST['bonus_report'];
		$name_attr = isset($_REQUEST['bonus_report_id']) && !empty($_REQUEST['bonus_report_id']) ? 'bonus_report_id' : 'bonus_report';
	}
	if(isset($_REQUEST['stock_portfolio_position']) && !empty($_REQUEST['stock_portfolio_position']) || isset($_REQUEST['stock_portfolio_id']) && !empty($_REQUEST['stock_portfolio_id'])){
		$bonus_report_date_filter = isset($_REQUEST['stock_portfolio_id']) && !empty($_REQUEST['stock_portfolio_id']) ? $_REQUEST['stock_portfolio_id'] : $_REQUEST['stock_portfolio_position'];
		$name_attr = isset($_REQUEST['stock_portfolio_id']) && !empty($_REQUEST['stock_portfolio_id']) ? 'stock_portfolio_id' : 'stock_portfolio_position';
	}
	if(isset($_REQUEST['product_trade_alerts']) && !empty($_REQUEST['product_trade_alerts']) || isset($_REQUEST['trade_alerts_id']) && !empty($_REQUEST['trade_alerts_id'])){
		$bonus_report_date_filter = isset($_REQUEST['trade_alerts_id']) && !empty($_REQUEST['trade_alerts_id']) ? $_REQUEST['trade_alerts_id'] : $_REQUEST['product_trade_alerts'];
		$name_attr = isset($_REQUEST['trade_alerts_id']) && !empty($_REQUEST['trade_alerts_id']) ? 'trade_alerts_id' : 'product_trade_alerts';
	}
	if(isset($_REQUEST['alerts_updates']) && !empty($_REQUEST['alerts_updates'])){
		$bonus_report_date_filter = isset($_REQUEST['alerts_updates']) && !empty($_REQUEST['alerts_updates']) ? $_REQUEST['alerts_updates'] : '';
		$name_attr = isset($_REQUEST['alerts_updates']) && !empty($_REQUEST['alerts_updates']) ? 'alerts_updates' : '';
	}
?>
<form action="" method="get" class="bonus-report-filter-form">
	<input type="hidden" name="<?=$name_attr?>" value="<?php echo $bonus_report_date_filter; ?>">
	<input type="text" name="keyword" value="" class="search-keywords" placeholder="Search by stock name, key term or symbol...">
	<input type="hidden" name="page" value="1">
	<span class="sep-text">or</span>
	<select class="date-select-cls" name="years">
		<option value="">Year</option>
		<?php
	$years = array();
	$query_year = new WP_Query($terms_year);

	if ($query_year->have_posts()):
	while ($query_year->have_posts()): $query_year->the_post();
	$year = get_the_date('Y');
	$get_year = isset($_REQUEST['years']) && !empty($_REQUEST['years']) ? $_REQUEST['years'] : '';
	if (!in_array($year, $years)) {
		$years[] = $year;
		if($year == $get_year){
			$selected_year = 'selected';
		}else {
			$selected_year = '';
		}
		echo '<option value="'.$year.'" '.$selected_year.'>'.$year.'</option>';
	}
	endwhile;
	wp_reset_postdata();
	endif;
		?>
	</select>
	<span class="sep-text">or</span>
	<select class="date-select-cls" name="months">
		<option value="">Month</option>
		<?php
	$months = array();
	$query_month = new WP_Query($terms_year);
	$months_name = array(1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec');
	if ($query_month->have_posts()):
	while ($query_month->have_posts()): $query_month->the_post();
	$month = get_the_date('M');
	$get_month = isset($_REQUEST['months']) && !empty($_REQUEST['months']) ? $_REQUEST['months'] : '';
	if (!in_array($month, $months)) {
		$months[] = $month;
		$key = array_search ($month, $months_name);
		if($key == $get_month){
			$selected_month = 'selected';
		}else {
			$selected_month = '';
		}
		echo '<option value="'.$key.'" '.$selected_month.'>'.$month.'</option>';
	}
	endwhile;
	wp_reset_postdata();
	endif;
		?>
	</select>

	<button type="submit" class="filter-submit-cls btn"><i class="fa fa-search"></i></button>
</form>
<?php
}

/**
 ** BTM Content
 **/
include_once('inc/class-btm-content.php');

/** bi-directional value updates */
function bidirectional_acf_update_value( $value, $post_id, $field  ) {	

	$field_name = $field['name'];
	$field_key = $field['key'];
	$global_name = 'is_updating_' . $field_name;	
	if( !empty($GLOBALS[ $global_name ]) ) return $value;	
	$GLOBALS[ $global_name ] = 1;	
	if( is_array($value) ) {	
		foreach( $value as $post_id2 ) {			
			switch (get_post_type($post_id)) {
				case 'latest_articles':
					$field_name = 'select_articles';
					break;
				case 'stock_portfolio':
					$field_name = 'select_stock_portfolio';
					break;
				case 'trade_alerts':
					$field_name = 'select_trade_alert';
					break;
				case 'trade_alerts':
					$field_name = 'select_trade_alert';
					break;
				case 'welcome_video':
					$field_name = 'select_welcome_video';
					break;
				case 'expect_from_service':
					$field_name = 'select_expect_service_video';
					break;
				case 'bonus_reports':
					$field_name = 'select_bonus_reports';
					break;
				default:
					$field_name = '';
			} 			
			// load existing related posts
			$value2 = get_field($field_name, $post_id2, false);			
			// allow for selected posts to not contain a value
			if( empty($value2) ) {				
				$value2 = array();				
			}			
			// bail early if the current $post_id is already found in selected post's $value2
			if( in_array($post_id, $value2) ) continue;
			// append the current $post_id to the selected post's 'related_posts' value
			$value2[] = $post_id;
			// update the selected post's value (use field's key for performance)
			update_field($field_name, $value2, $post_id2);			
		}	
	}
	$GLOBALS[ $global_name ] = 0;	
	// return
	return $value;    
}

add_filter('acf/update_value/name=select_product', 'bidirectional_acf_update_value', 10, 3);

/**
 ** Search For Bonus Reports
 **/
add_action( 'powerpack/query/btm_search_results', function( $query ) {	
	if(isset($_REQUEST['ee_search_query']) && $_REQUEST['ee_search_query'] != '' ){
		$ee_search_query = $_REQUEST['ee_search_query'];
		$ee_search_query = str_replace('\\', '', $ee_search_query);
		$ee_search_query = json_decode($ee_search_query, JSON_UNESCAPED_SLASHES );
		if(isset($ee_search_query['current_post_id']) && $ee_search_query['current_post_id'] != ''){
			$related_bonus_reports_ids = get_post_meta($ee_search_query['current_post_id'], 'select_bonus_reports', true);
			$query->set( 'post_type', $ee_search_query['post_type']);
			$query->set( 'post__in', $related_bonus_reports_ids );
		}
	}
} );

/**
 ** Search For Stock Portfolio
 **/
add_action( 'powerpack/query/btm_stock_search_results', function( $query ) {	
	if(isset($_REQUEST['ee_search_query']) && $_REQUEST['ee_search_query'] != '' ){
		$ee_search_query = $_REQUEST['ee_search_query'];
		$ee_search_query = str_replace('\\', '', $ee_search_query);
		$ee_search_query = json_decode($ee_search_query, JSON_UNESCAPED_SLASHES );
		if(isset($ee_search_query['current_post_id']) && $ee_search_query['current_post_id'] != ''){
			$related_bonus_reports_ids = get_post_meta($ee_search_query['current_post_id'], 'select_stock_portfolio', true);
			$query->set( 'post_type', $ee_search_query['post_type']);
			$query->set( 'post__in', $related_bonus_reports_ids );
		}
	}
} );

/**
 ** Search For Trade Alerts
 **/
add_action( 'powerpack/query/btm_trade_search_results', function( $query ) {	
	if(isset($_REQUEST['ee_search_query']) && $_REQUEST['ee_search_query'] != '' ){
		$ee_search_query = $_REQUEST['ee_search_query'];
		$ee_search_query = str_replace('\\', '', $ee_search_query);
		$ee_search_query = json_decode($ee_search_query, JSON_UNESCAPED_SLASHES );
		if(isset($ee_search_query['current_post_id']) && $ee_search_query['current_post_id'] != ''){
			$related_bonus_reports_ids = get_post_meta($ee_search_query['current_post_id'], 'select_trade_alert', true);
			$query->set( 'post_type', $ee_search_query['post_type']);
			$query->set( 'post__in', $related_bonus_reports_ids );
		}
	}
} );

/**
 ** Search For Latest Articles
 **/
add_action( 'powerpack/query/btm_articles_search_results', function( $query ) {	
	if(isset($_REQUEST['ee_search_query']) && $_REQUEST['ee_search_query'] != '' ){
		$ee_search_query = $_REQUEST['ee_search_query'];
		$ee_search_query = str_replace('\\', '', $ee_search_query);
		$ee_search_query = json_decode($ee_search_query, JSON_UNESCAPED_SLASHES );
		if(isset($ee_search_query['current_post_id']) && $ee_search_query['current_post_id'] != ''){
			$related_bonus_reports_ids = get_post_meta($ee_search_query['current_post_id'], 'select_articles', true);
			$query->set( 'post_type', $ee_search_query['post_type']);
			$query->set( 'post__in', $related_bonus_reports_ids );
		}
	}
} );

/**
 ** Logout Menu
 **/

function change_menu($items){
	foreach($items as $item){
		if( $item->title == "Logout"){
			$item->url = $item->url . "&_wpnonce=" . wp_create_nonce( 'log-out' );
		}
	}
	return $items;

}
add_filter('wp_nav_menu_objects', 'change_menu');

/**
 ** Shortcode for Latest Post Hide on Search Filter
 **/

add_shortcode( 'latest_post_hide_sc', 'latest_post_hide_func' );

function latest_post_hide_func() {

	return ((isset($_REQUEST['keyword']) && !empty($_REQUEST['keyword'])) || (isset($_REQUEST['years']) && !empty($_REQUEST['years'])) || (isset($_REQUEST['months']) && !empty($_REQUEST['months']))) ? 1 : 0;

}


function cpt_add_edit_permissions( $post_type, $post_type_object ) {

	$allowed_post_types = ['btm_products', 'latest_articles', 'stock_portfolio', 'trade_alerts', 'welcome_video', 'expect_from_service', 'bonus_reports'];

	if(in_array($post_type, $allowed_post_types)){
		if ( !class_exists( 'URE_Lib') ) {
			return;
		}

		$roles = array('editor');
		$lib = URE_Lib::get_instance();
		$capabilities = $lib->get_edit_post_capabilities();
		foreach( $capabilities as $capability ) {
			if ( isset( $post_type_object->cap->$capability ) ) {
				URE_Capabilities::add_cap_to_roles( $roles, $post_type_object->cap->$capability );
			}        
		}	
	}

}

/**
 ** CM Agent and CS Agent
 **/

function btm_admin_head() {
	global $current_user; 
	wp_get_current_user();

	$user = new WP_User( get_current_user_id() );

	if(!empty(array_intersect(array('customerserviceagent', 'editor'), $user->roles)))
	{
		echo "<style>div#wpbody-content .bwf-notice.notice.error,.notice.notice-error{display: none !important;}</style>";
		echo "<style>.notice.notice-warning.is-dismissible{display: none !important;}</style>";
	}

	if(!empty(array_intersect(array('customerserviceagent'), $user->roles)))
	{
		echo "<style>#toplevel_page_wc-admin-path--analytics-overview,#toplevel_page_woocommerce,#toplevel_page_gf_edit_forms,#toplevel_page_woocommerce-marketing,#menu-posts-product,#toplevel_page_woofunnels, #menu-appearance {display: none;}#welcome-panel {display: block !important;}#dashboard-widgets .postbox-container {width: 33.333333%;}#dashboard-widgets #postbox-container-1 {width: 50%;} #toplevel_page_wpseo_workouts, #menu-posts-elementor_library, #toplevel_page_edit-post_type-shop_subscription .wp-first-item, #toplevel_page_mailchimp-general-settings {display: none; } .welcome-panel::before {content: none;}.welcome-panel .dwe-panel-content {background-color: #fff;}#welcome-panel {background: #fff !important;} li#toplevel_page_admin-page-wc-admin-path--2Fmarketing, li#toplevel_page_edit-post_type-product_redirect,li#menu-pages ul.wp-submenu.wp-submenu-wrap li:nth-child(3) {display: none;} #toplevel_page_woocommerce { display: block; } #toplevel_page_woocommerce ul.wp-submenu.wp-submenu-wrap li { display: none; } #toplevel_page_woocommerce ul.wp-submenu.wp-submenu-wrap li:nth-child(11) { display: block; } li#toplevel_page_woocommerce {display: none;} </style>";
	}

	if(!empty(array_intersect(array('chargebacksupport'), $user->roles)))
	{
		echo "<style>#toplevel_page_woocommerce, #menu-posts-elementor_library, #toplevel_page_mailchimp-general-settings {  display: none;}.wp-not-current-submenu.wp-menu-separator.woocommerce {  display: none;}</style>";
	}

	if(!empty(array_intersect(array('customerserviceeditor'), $user->roles)))
	{
		echo "<style>.notice, #menu-posts, #toplevel_page_gf_edit_forms, #toplevel_page_woocommerce, #menu-posts-product, .wp-not-current-submenu.wp-menu-separator.woocommerce, .wp-not-current-submenu.wp-menu-separator.elementor, .wp-not-current-submenu.wp-menu-separator, #toplevel_page_mailchimp-general-settings,li#menu-pages ul.wp-submenu.wp-submenu-wrap li:nth-child(3), li#toplevel_page_admin-page-check-member-on-mailchimp {display: none !important}</style>";
	}

	if(!empty(array_intersect(array('editor'), $user->roles)))
	{
		echo "<style>li#menu-pages ul.wp-submenu.wp-submenu-wrap li:nth-child(3), li#toplevel_page_gf_edit_forms, .gform-form-toolbar__form-title.gform-form-toolbar__form-title--form-editor, .gform-form-toolbar__form-title {display: none !important}</style>";
	}
	
	echo "<style>#wpfooter p { display: none;}";


}
add_action( 'admin_head', 'btm_admin_head' );
/**
 ** My Account Page Redirection Function
 **/
add_action( 'template_redirect', 'btm_redirect_to_my_product_page' );
function btm_redirect_to_my_product_page() {
	// Program to display URL of current page.
	if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
		$link = "https";
	else $link = "http";

	// Here append the common URL characters.
	$link .= "://";

	// Append the host(domain name, ip) to the URL.
	$link .= $_SERVER['HTTP_HOST'];

	// Append the requested resource location to the URL
	$link .= $_SERVER['REQUEST_URI'];

	// Print the link
	//echo $link;

	if ( $link == site_url('my-account') ) {	  
		wp_redirect(home_url('my-product'), 301 );
		exit;
	}
}


function eg_remove_my_subscriptions_button( $actions, $subscription ) {

	foreach ( $actions as $action_key => $action ) {
		switch ( $action_key ) {
			case 'resubscribe':     // Hide "Resubscribe" button from an expired or cancelled subscription?
				unset( $actions[ $action_key ] );
				break;
			case 'cancel':			// Hide "Cancel" button on subscriptions that are "active" or "on-hold"?
				unset( $actions[ $action_key ] );
				break;
			default: 
				break;
		}
	}

	return $actions;
}
add_filter( 'wcs_view_subscription_actions', 'eg_remove_my_subscriptions_button', 100, 2 );

add_filter( 'haet_mail_use_template', 'behindthemarkets_disable_email_template_by_subject', 10, 2 );
function behindthemarkets_disable_email_template_by_subject( $use_template, $email ){
	if( stripos( $email['subject'], 'Your Account Has Been Created!' ) !== false )
		$use_template = false;
	return $use_template;
}
/**
 ** Include welcome email template
 **/
include_once('inc/welcome-email-template.php');
// welcome_email_users('Mohan Chouhan', $username, $password);


add_action( 'wp_ajax_nopriv_welcome_email_template_mail', 'welcome_email_template_mail_fn' );
add_action( 'wp_ajax_welcome_email_template_mail', 'welcome_email_template_mail_fn' );
function welcome_email_template_mail_fn() {
	if(isset($_POST['action']) && $_POST['action'] == 'welcome_email_template_mail'){
		$imported_users = get_users(
			array(
				'number'     => 1,
				'meta_query' => array(
					array(
						'key' => 'customer_import',
						'value' => 'Import',
						'compare' => '='
					)
				),
				'role__in' => array( 'subscriber' )
			)
		);
		if(!empty($imported_users)){
			//print_r($imported_users);
			foreach($imported_users as $user){
				$username  = $user->data->user_login;
				$email = $user->data->user_email;
				$user_id  = $user->data->ID;
				//$password = wp_generate_password(12, true);
				$subject = 'Your Account Has Been Created!';
				$headers[]   = 'Reply-To: Behind The Markets <support@behindthemarkets.com>';
				$headers[]   = 'Content-Type: text/html; charset=UTF-8';
				$message = welcome_email_users($user->user_firstname);

				//wp_die(json_encode(array('username' => $username, 'success' => true)));
				if($username != ''){
					if(wcs_user_has_subscription( $user_id, '', 'active' )){
						$mail = wp_mail( $email, $subject, $message, $headers );
						//print_r($mail);
						if($mail){
							update_user_meta($user_id, 'customer_import', 'Welcome Mail Sent');
							//wp_set_password( $password, $user_id );
							wp_die(json_encode(array('username' => $username, 'email' => $email,  'success' => true, 'email_sent' => true)));
						}else{
							wp_die(json_encode(array('success'=> false)));
						}
					}else  {
						update_user_meta($user_id, 'customer_import', 'Welcome Mail Not Sent - No Active Subscription');
						//wp_set_password( $password, $user_id );
						wp_die(json_encode(array('username' => $username, 'email' => $email,  'success' => true, 'email_sent' => false)));

					}
				}else  {
					wp_die(json_encode(array('success'=> false)));
				}
				break;
			}
		}else{
			wp_die(json_encode(array('success'=> false)));
		}

	}else{
		wp_die(json_encode(array('success'=> false)));
	}		
}


remove_filter( 'authenticate', 'wp_authenticate_username_password' );
add_filter( 'authenticate', 'behindthemarkets_authenticate_username_password', 140, 3 );
/**
 * Remove WordPress filer and write our own with changed error text.
 */
function behindthemarkets_authenticate_username_password( $user, $username, $password ) {
	if ( is_a($user, 'WP_User') )
		return $user;

	if ( empty( $username ) || empty( $password ) ) {
		if ( is_wp_error( $user ) )
			return $user;

		$error = new WP_Error();

		if ( empty( $username ) )
			$error->add( 'empty_username', __('<strong>ERROR</strong>: The username field is empty.' ) );

		if ( empty( $password ) )
			$error->add( 'empty_password', __( '<strong>ERROR</strong>: The password field is empty.' ) );

		return $error;
	}

	$user = get_user_by( 'login', $username );

	if ( !$user )
		return new WP_Error( 'invalid_username', sprintf( __( '<strong>ERROR</strong>: The username or password you entered is incorrect.' ), wp_lostpassword_url() ) );

	$user = apply_filters( 'wp_authenticate_user', $user, $password );
	if ( is_wp_error( $user ) )
		return $user;

	if ( ! wp_check_password( $password, $user->user_pass, $user->ID ) )
		return new WP_Error( 'incorrect_password', sprintf( __( '<strong>Update Your Password: </strong>You need to update your password for the username <strong>%1$s</strong> if this is the first time you are signing into the new website or your password has expired. Click “Forgot Password” to reset and get access.' ),
														   $username, wp_lostpassword_url() ) );

	return $user;
}


add_filter( 'retrieve_password_message', 'behindthemarkets_retrieve_password_message', 140, 4 );
function behindthemarkets_retrieve_password_message( $message, $key, $user_login, $user_data ) {

	// Start with the default content.
	$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	$message .= __( 'Hi ' ) . $user_data->user_firstname . "\r\n\r\n";
	$message .= __( 'Someone has requested a password reset for the following account:' ) . "\r\n\r\n";
	/* translators: %s: site name */
	//$message .= sprintf( __( 'Site Name: %s' ), $site_name ) . "\r\n\r\n";
	/* translators: %s: user login */
	$message .= sprintf( __( 'Username: %s' ), $user_login ) . "\r\n\r\n";
	$message .= __( 'If this was a mistake, just ignore this email and nothing will happen.' ) . "\r\n\r\n";
	$message .= __( 'If you would like to proceed, ' ) . "\r\n\r\n";
	$message .= '<a href="' . site_url() . '/login/?lost_pass=1">Click here to reset your password</a>\r\n"';
	return $message;

}


function sent_welcome_emails_count(){
	$imported_users = get_users(
		array(
			'number'     => -1,
			'fields' => 'ids',
			'meta_query' => array(
				array(
					'key' => 'customer_import',
					'value' => 'Welcome Mail Sent',
					'compare' => 'LIKE'
				)
			),
			'role__in' => array( 'subscriber' )
		)
	);
	return count($imported_users);
}
add_shortcode('sent_welcome_emails_count','sent_welcome_emails_count'); 
function all_imported_user_count(){
	$imported_users = get_users(
		array(
			'number'     => -1,
			'fields' => 'ids',
			'meta_query' => array(
				array(
					'key' => 'customer_import'//,
					// 					'value' => 'Import',
					// 					'compare' => 'LIKE'
				)
			),
			'role__in' => array( 'subscriber' )
		)
	);
	return count($imported_users);
}
add_shortcode('all_imported_user_count','all_imported_user_count'); 
/*
 * 
 */
add_filter( 'request', 'subs_filter_retry_count', 999 );
function subs_filter_retry_count( $vars ){
	global $typenow;

	if ( 'shop_subscription' === $typenow ) {

		// Filter the orders by the posted customer.
		if ( isset( $_GET['_mobile_no_filter'] ) && $_GET['_mobile_no_filter'] > 0 ) {
			$mob = preg_replace('/\D+/', '', preg_replace("/[^0-9]/", "", $_GET['_mobile_no_filter']));
			$vars['meta_query'][] = array(
				'key'   	=> '_billing_phone',
				'value' 	=> $mob,
				'compare' 	=> 'LIKE', 
			);
		}
	}
	return $vars;
}

add_action('restrict_manage_posts', 'mobile_no_filter_function');
function mobile_no_filter_function(){
	global $typenow;

	if ( 'shop_subscription' !== $typenow ) {
		return;
	}
	echo '<div class="mobile_no_filter" style="width: 200px;display: inline-block;">';
	$mob = preg_replace('/\D+/', '', preg_replace("/[^0-9]/", "", $_GET['_mobile_no_filter']));
	woocommerce_wp_text_input( array(
		'id'          	=> '_mobile_no_filter',
		'placeholder' 	=> 'Search By Phone Number',
		'value' 		=> isset( $_GET['_mobile_no_filter'] ) ? $mob : '',//preg_replace("/[^0-9]/", "", $phone);
		// 		'label' => 'Retry count filter',
	) );
	echo '<style>.mobile_no_filter>p.form-field._mobile_no_filter_field {margin: 0;}</style></div>';
}
/*
 * 
 * Show custom fields on billing address
 */
add_action('woocommerce_admin_order_data_after_order_details', 'my_custom_billing_fields_display_admin_order_meta', 10, 1);

function my_custom_billing_fields_display_admin_order_meta($order) {
	if(get_post_type($order->id) == 'shop_subscription')
	{
		$args = array(
			'label' => 'Recurly Subscription Uuid', // Text in Label
			'placeholder' => 'Enter Recurly Subscription Uuid',
			'class' => '',
			'style' => '',
			'wrapper_class' => 'form-field-wide',
			'value' => get_post_meta($order->id, '_recurly_subscription_uuid', true), // if empty, retrieved from post meta where id is the meta_key
			'id' => '_recurly_subscription_uuid', // required
			'name' => '_recurly_subscription_uuid', //name will set from id if empty
			'type' => 'text',
			'desc_tip' => '',
			'data_type' => '',
			'custom_attributes' => '', // array of attributes 
			'description' => ''
		);

		woocommerce_wp_text_input( $args );
	}
}
// Save
function recurly_subscription_uuid_save(  $post_id, $post ){
	if( isset($_POST['_recurly_subscription_uuid']) ) {
		update_post_meta($post_id, '_recurly_subscription_uuid', sanitize_text_field( $_POST['_recurly_subscription_uuid'] ) );
	}
}
add_action( 'woocommerce_process_shop_subscription_meta', 'recurly_subscription_uuid_save', 10, 2 );

function mobile_no_filter_in_subscription()
{
	if($_REQUEST['post_type'] === 'shop_subscription')
	{
?>
<script>
	jQuery(document).ready(function(){
		jQuery('#post-query-submit').on('click',function(){
			jQuery('#_mobile_no_filter').val(jQuery('#_mobile_no_filter').val().replace(/\D/g, ''));
		});
	});
</script>
<?php
	}
}
add_action( 'admin_footer', 'mobile_no_filter_in_subscription' ); // For back-end

add_action('restrict_manage_posts', 'btm_products_filter_function');
function btm_products_filter_function(){
	global $typenow;
	$cpts = array( 'latest_articles', 'stock_portfolio','welcome_video','expect_from_service','bonus_reports' );
	if (!in_array($typenow, $cpts)) {
		return;
	}
	echo '<div class="btm_products_filter" style="width: 200px;display: inline-block;">';
	woocommerce_wp_select( array(
		'id'          	=> 'btm_products_filter',
		//'label'       => __( '', 'woocommerce' ),
		//'description' => __( '', 'woocommerce' ),
		'value'   => $_REQUEST['btm_products_filter'] ? $_REQUEST['btm_products_filter'] : '',
		'desc_tip'    => true,
		'options'     => array(
			'none'    => __( 'Filter from BTM Products', 'woocommerce' ),
			2729    => __( 'Breakthrough Wealth', 'woocommerce' ),
			2728 	=> __( 'Takeover Targets', 'woocommerce' ),
			2726 	=> __( 'Hidden Market Profits', 'woocommerce' ),
			2725 	=> __( 'Biotech Insider', 'woocommerce' ),
			2724 	=> __( 'Behind the Markets', 'woocommerce' )
		)
	) );
	echo '<style>.btm_products_filter>p.form-field.btm_products_filter_field {margin: 0;}</style></div>';
}

add_filter( 'request', 'request_btm_products_filter_function', 999 );
function request_btm_products_filter_function( $vars ){
	global $typenow;
	$cpts = array( 'latest_articles', 'stock_portfolio','welcome_video','expect_from_service','bonus_reports' );
	if (in_array($typenow, $cpts)) {
		// Filter the orders by the posted customer.
		if ( isset( $_GET['btm_products_filter'] ) && $_GET['btm_products_filter'] > 0 ) {
			$vars['meta_query'][] = array(
				'key'     => 'select_product',
				'value'   => $_GET['btm_products_filter'],
				'compare' => 'LIKE'
			);
		}
	}
	return $vars;
}

function show_posts_with_count( $views ){    
	global $typenow;
	$cpts = array( 'latest_articles', 'stock_portfolio','welcome_video','expect_from_service','bonus_reports' );
	if (in_array($typenow, $cpts)) {
		$all_posts = get_posts(array(
			'post_type' => 'btm_products',
			'fields'	=>	'ids'
		));
		echo"<div clsss=''>";
		foreach($all_posts as $key => $all_post)
		{
			$seprater = count($all_posts) === $key+1 ? '' : ' | ';
			printf('<a class="row-title" href="https://behindthemarkets.com/wp-admin/edit.php?post_type='.$typenow.'&btm_products_filter='.$all_post.'">%s</a>&nbsp;%s&nbsp;',  get_the_title($all_post),$seprater ); 
		}
		echo"</div>";
	}

	return $views; // return original input unchanged
}

add_filter('views_edit-latest_articles', 'show_posts_with_count',);
add_filter('views_edit-stock_portfolio', 'show_posts_with_count',);
add_filter('views_edit-welcome_video', 'show_posts_with_count',);
add_filter('views_edit-expect_from_service', 'show_posts_with_count',);
add_filter('views_edit-bonus_reports', 'show_posts_with_count',);

add_filter('manage_latest_articles_posts_columns', 'btm_products_new_column', 10, 1);
add_filter('manage_stock_portfolio_posts_columns', 'btm_products_new_column', 10, 1);
add_filter('manage_welcome_video_posts_columns', 'btm_products_new_column', 10, 1);
add_filter('manage_expect_from_service_posts_columns', 'btm_products_new_column', 10, 1);
add_filter('manage_bonus_reports_posts_columns', 'btm_products_new_column', 10, 1);

function btm_products_new_column ($columns) {
	return array_merge($columns, ['btm_products_col' => __('Related BTM Products', 'woocommerce')]);
}

add_action('manage_latest_articles_posts_custom_column', 'manage_btm_products_new_column', 10 , 2);
add_action('manage_stock_portfolio_posts_custom_column', 'manage_btm_products_new_column', 10 , 2);
add_action('manage_welcome_video_posts_custom_column', 'manage_btm_products_new_column', 10 , 2);
add_action('manage_expect_from_service_posts_custom_column', 'manage_btm_products_new_column', 10 , 2);
add_action('manage_bonus_reports_posts_custom_column', 'manage_btm_products_new_column', 10 , 2);
function manage_btm_products_new_column($column_key, $post_id) {
	if ($column_key == 'btm_products_col') {
		$select_product = get_post_meta($post_id, 'select_product', true);
		if ($select_product) {
			echo '<a class="row-title" href="'.get_admin_url().'post.php?post='.$select_product[0].'&amp;action=edit" aria-label="'.get_the_title($select_product[0]).'">'. get_the_title($select_product[0]).'</a>';
		}else{
			echo '-';
		}
	}
}

add_filter('manage_edit-wc_user_membership_columns', 'wc_user_membership_columns_login_last',100);
function wc_user_membership_columns_login_last($columns) {
	unset( $columns['last_login']);
	$columns['wc_last_login'] = __( 'Last login', 'woocommerce-memberships' );;
	return $columns;
}

add_filter('manage_wc_user_membership_posts_custom_column', 'manage_wc_last_login_active', 20 , 2);
function manage_wc_last_login_active( $column, $post_id ) {
	$user_membership = wc_memberships_get_user_membership( $post_id );
	$user            = $user_membership ? get_userdata( $user_membership->get_user_id() ) : null;
	if ( $column == 'wc_last_login' ) 
	{
		$last_active = $user instanceof \WP_User ? get_user_meta( $user->ID, 'wc_last_login_active', true ) : get_user_meta( $user->ID, 'wc_last_active', true );
		if(!is_numeric( $last_active )){
			$last_active = get_user_meta( $user->ID, 'wc_last_active', true );
		}
		echo is_numeric( $last_active ) ? sprintf(
			/* translators: Placeholder: %s last login since */
			esc_html__( '%s ago', 'woocommerce-memberships' ),
			human_time_diff( (int) $last_active )
		) : '&mdash;';
	}
}
add_action( 'wp_login', 'login_badge', 999, 2 );
function login_badge( $user_login, $user ) {
	update_user_meta( $user->ID, 'wc_last_login_active', strtotime("now") );
}
// Only Month Filter
add_shortcode( 'months_archives', 'get_posts_months_array' );

function get_posts_months_array() {
	global $post;
	extract( shortcode_atts( array(
		'select' => '',
		'post_type' =>''
	), $atts));
	$reports_ids = get_post_meta($post->ID, $select, true);
	$terms_year = array(
		'post_type' => array($post_type),
		'post__in'  => is_array($reports_ids) ? $reports_ids : array($reports_ids)
	);
	ob_start();
	$get_month = isset($_REQUEST['months']) && !empty($_REQUEST['months']) ? $_REQUEST['months'] : '';
	if(isset($_REQUEST['alerts_updates']) && !empty($_REQUEST['alerts_updates'])){
		$bonus_report_date_filter = isset($_REQUEST['alerts_updates']) && !empty($_REQUEST['alerts_updates']) ? $_REQUEST['alerts_updates'] : '';
		$name_attr = isset($_REQUEST['alerts_updates']) && !empty($_REQUEST['alerts_updates']) ? 'alerts_updates' : '';
	}
	if(isset($_REQUEST['bonus_report']) && !empty($_REQUEST['bonus_report'])){
		$bonus_report_date_filter = isset($_REQUEST['bonus_report']) && !empty($_REQUEST['bonus_report']) ? $_REQUEST['bonus_report'] : '';
		$name_attr = isset($_REQUEST['bonus_report']) && !empty($_REQUEST['bonus_report']) ? 'bonus_report' : '';
	}
	if(isset($_REQUEST['stock_portfolio_position']) && !empty($_REQUEST['stock_portfolio_position'])){
		$bonus_report_date_filter = isset($_REQUEST['stock_portfolio_position']) && !empty($_REQUEST['stock_portfolio_position']) ? $_REQUEST['stock_portfolio_position'] : '';
		$name_attr = isset($_REQUEST['stock_portfolio_position']) && !empty($_REQUEST['stock_portfolio_position']) ? 'stock_portfolio_position' : '';
	}
	if(isset($_REQUEST['stock_portfolio_id']) && !empty($_REQUEST['stock_portfolio_id'])){
		$bonus_report_date_filter = isset($_REQUEST['stock_portfolio_id']) && !empty($_REQUEST['stock_portfolio_id']) ? $_REQUEST['stock_portfolio_id'] : '';
		$name_attr = isset($_REQUEST['stock_portfolio_id']) && !empty($_REQUEST['stock_portfolio_id']) ? 'stock_portfolio_id' : '';
	}
?>
<form action="" method="get" class="bonus-report-filter-form only-months-filter">
	<input type="hidden" name="<?=$name_attr?>" value="<?php echo $bonus_report_date_filter; ?>">
	<input type="hidden" name="page" value="1">
	<select class="date-select-cls" name="months" id="post_months" onchange="this.form.submit()">
		<option value="" selected disabled>Month</option>
		<?php
	$selected_month = 0; //current month
	for ($i_month = 1; $i_month <= 12; $i_month++) { 
		$selected = ($get_month == $i_month) ? 'selected' : '';
		echo '<option value="'.$i_month.'"'.$selected.'>'. date('F', mktime(0,0,0,$i_month)).'</option>'."\n";
	}
		?>
	</select>
</form>
<?php
	$output_string = ob_get_contents();
	ob_end_clean();
	return $output_string;
}
//  Help Scout Code
function admin_footer_function() {
	echo do_shortcode('[hfcm id="1"]');
}
add_action('admin_footer', 'admin_footer_function');
/*
 * ADD NEW COLUMN FOR ASSIGN TO PAGES
 */
add_filter( 'manage_elementor_library_posts_columns', 'add_new_column_assign_pages_column' );
function add_new_column_assign_pages_column($columns) {
	unset($clumns['categories']);
	$columns['assign_pages'] = __( 'Assignd Pages', 'behindthemarkets' );
	$columns['roles'] = __( 'Roles', 'behindthemarkets' );
	return $columns;
}
// Add the data to the custom columns for the book post type:
add_action( 'manage_elementor_library_posts_custom_column' , 'show_assign_pages_elementor_library', 10, 2 );
function show_assign_pages_elementor_library( $column, $post_id ) {
	switch ( $column ) {
		case 'assign_pages' :
			$assign_pages = get_post_meta($post_id, 'assign_pages', true);
			// 			print_r($assign_pages);
			if(!empty($assign_pages)){			
				$get_pages = [];
				foreach( $assign_pages as $key => $assign_page)
				{
					if(!is_numeric($assign_page)){
						$post_type_object = get_post_type_object( $assign_page );
						$get_pages[] = $post_type_object->labels->name;
					}else{
						$get_pages[] = get_the_title($assign_page);
					}

				}

				echo implode(', ', $get_pages);
			}
			break;
		case 'roles' :
			global $wp_roles;
			$all_roles = $wp_roles->roles;
			$user_role = get_post_meta($post_id, 'user_role', true);
			print_r($all_roles[$user_role[0]]['name']);
			break;
	}
}
add_action('restrict_manage_posts', 'elementor_library_filter_function');
function elementor_library_filter_function(){
	global $typenow;
	if($typenow === 'elementor_library')
	{ ?>
<select name="elementor_library_page"> 
	<option selected="selected" disabled="disabled" value=""><?php echo esc_attr( __( 'Select page' ) ); ?></option> 
	<?php
	 $selected_page = isset( $_GET['elementor_library_page'] ) && !empty($_GET['elementor_library_page']) ? $_GET['elementor_library_page'] : '';
	 $pages = get_pages(); 
	 $post_types = array(
		 'latest_articles' 		=> 'Model Portfolios & Positions',
		 'stock_portfolio' 		=> 'Trade Alerts & Updates',
		 'welcome_video' 		=> 'Welcome Video',
		 'expect_from_service' 	=> 'What to Expect from this Service',
		 'bonus_reports' 		=> 'Bonus Reports'
	 );
	 $pages = array_merge($post_types,$pages);

	 foreach ( $pages as $key => $page ) {
		 if(!is_numeric($key))
		 {
			 $post_type_object = get_post_type_object( $key );
			 $option = '<option value="' . $post_type_object->name . '" ';
			 $option .= ( $post_type_object->name == $selected_page ) ? 'selected="selected"' : '';
			 $option .= '>';
			 $option .= $post_type_object->label;
			 $option .= '</option>';
			 echo $option;
		 }else{
			 $option = '<option value="' . $page->ID . '" ';
			 $option .= ( $page->ID == $selected_page ) ? 'selected="selected"' : '';
			 $option .= '>';
			 $option .= $page->post_title;
			 $option .= '</option>';
			 echo $option;
		 }
	 }

	?>
</select>
<?php
	}
}
add_filter( 'request', 'request_elementor_library_filter_function', 999 );
function request_elementor_library_filter_function( $vars ){
	global $typenow;
	if ($typenow === 'elementor_library') {
		// Filter the orders by the posted customer.
		if ( isset( $_GET['elementor_library_page'] ) && !empty($_GET['elementor_library_page']) ) {
			$vars['meta_query'][] = array(
				'key'     => 'assign_pages',
				'value'   => $_GET['elementor_library_page'],
				'compare' => 'LIKE'
			);
		}
	}
	return $vars;
}

function acf_load_aassign_pages_field_choices( $field ) {
	$field['choices'] = array();
	$field['choices'][ $page->ID ] = '';
	$post_types = array(
		'latest_articles' 		=> 'Model Portfolios & Positions',
		'stock_portfolio' 		=> 'Trade Alerts & Updates',
		'welcome_video' 		=> 'Welcome Video',
		'expect_from_service' 	=> 'What to Expect from this Service',
		'bonus_reports' 		=> 'Bonus Reports'
	);
	foreach ( $post_types as $key => $post_type )
	{
		$field['choices'][ $key ] = $post_type;//$page->ID.' : '.
	}
	$pages = get_pages(); 
	foreach ( $pages as $page ) {
		$field['choices'][ $page->ID ] = $page->post_title;
	}
	return $field;    
}

add_filter('acf/load_field/name=assign_pages', 'acf_load_aassign_pages_field_choices');

function acf_load_all_roles_field_choices( $field ) {
	$field['choices'] = array();

	global $wp_roles;

	$all_roles = $wp_roles->roles;
	$field['choices'][] = '';
	foreach ( $all_roles as $key => $all_role ) {
		$field['choices'][$key] = $all_role['name'];
	}
	return $field;    
}

add_filter('acf/load_field/name=user_role', 'acf_load_all_roles_field_choices');
add_action( 'admin_menu', 'add_help_menu_page' );
function add_help_menu_page() {
	global $current_user;
	if ( in_array( $current_user->roles[0], array( 'customerserviceagent', 'administrator' ) ) ) {
		add_menu_page( 'Help', 'Help', $current_user->roles[0], 'help', 'help_menu_page_contents', '', 999 );
	}
}    
function help_menu_page_contents() {
	return wp_redirect('https://behind-the-markets.helpscoutdocs.com/');
}

// 

// Only Month Filter
add_shortcode( 'bonus_report_post_date', 'bonus_report_post_publish_date' );

function bonus_report_post_publish_date() {
	ob_start();

	$show_date = get_field('show_date');
	if( $show_date && in_array('true', $show_date) ) {
		echo 'publish_date_visible';
	}else {
		echo 'publish_date_hide';
	}
	$output = ob_get_contents();
	ob_end_clean(); 
	return  $output;
}
add_shortcode( 'bonus_report_author_name', 'bonus_report_author_name_fun' );

function bonus_report_author_name_fun() {
	ob_start();
	$show_date = get_field('show_author');
	if( $show_date && in_array('true', $show_date) ) {
		echo 'author_name_visible';
	}else {
		echo 'author_name_hide';
	}
	$output = ob_get_contents();
	ob_end_clean(); 
	return  $output;
}

// Show Author name model portfolio
add_shortcode( 'model_portoflio_author_name', 'model_portoflio_author_name_fun' );

function model_portoflio_author_name_fun() {
	ob_start();

	$show_author = get_field('show_author');
	if( $show_author && in_array('true', $show_author) ) {
		echo 'author_name_visible';
	}else {
		echo 'author_name_hide';
	}
	$output = ob_get_contents();
	ob_end_clean(); 
	return  $output;
}


add_action( 'template_redirect', 'behindthemarkets_content_single_redirection', 10 );
function behindthemarkets_content_single_redirection() {
	// stock_portfolio
	if ( is_single() && 'stock_portfolio' == get_post_type() ) {
		$post_id = get_the_ID();
		$select_product = get_field('select_product', $post_id);
		//print_r($select_product[0]->ID);

		// Takeover Targets
		if($select_product[0]->ID == 2728){
			wp_redirect( home_url().'/btm_products/takeover-targets/?stock_portfolio_id='.$post_id, 301 );
			exit();
		}

		// Breakthrough Wealth
		if($select_product[0]->ID == 2729){
			wp_redirect( home_url().'/btm_products/breakthrough-wealth/?stock_portfolio_id='.$post_id, 301 );
			exit();
		}

		// Hidden market profits
		if($select_product[0]->ID == 2726){
			wp_redirect( home_url().'/btm_products/hidden-market-profits/?stock_portfolio_id='.$post_id, 301 );
			exit();
		}

		// Biotech Insider
		if($select_product[0]->ID == 2725){
			wp_redirect( home_url().'/btm_products/biotech-insider/?stock_portfolio_id='.$post_id, 301 );
			exit();
		}

		// Behind the markets
		if($select_product[0]->ID == 2724){
			wp_redirect( home_url().'/btm_products/behind-the-markets/?stock_portfolio_id='.$post_id, 301 );
			exit();
		}
	}

	// Bonus Report
	if ( is_single() && 'bonus_reports' == get_post_type() ) {
		$post_id = get_the_ID();
		$select_product = get_field('select_product', $post_id);
		//print_r($select_product[0]->ID);

		// Takeover Targets
		if($select_product[0]->ID == 2728){
			wp_redirect( home_url().'/btm_products/takeover-targets/?bonus_report_id='.$post_id, 301 );
			exit();
		}

		// Breakthrough Wealth
		if($select_product[0]->ID == 2729){
			wp_redirect( home_url().'/btm_products/breakthrough-wealth/?bonus_report_id='.$post_id, 301 );
			exit();
		}

		// Hidden market profits
		if($select_product[0]->ID == 2726){
			wp_redirect( home_url().'/btm_products/hidden-market-profits/?bonus_report_id='.$post_id, 301 );
			exit();
		}

		// Biotech Insider
		if($select_product[0]->ID == 2725){
			wp_redirect( home_url().'/btm_products/biotech-insider/?bonus_report_id='.$post_id, 301 );
			exit();
		}

		// Behind the markets
		if($select_product[0]->ID == 2724){
			wp_redirect( home_url().'/btm_products/behind-the-markets/?bonus_report_id='.$post_id, 301 );
			exit();
		}
	}

	// latest_articles
	if ( is_single() && 'latest_articles' == get_post_type() ) {
		$post_id = get_the_ID();
		$select_product = get_field('select_product', $post_id);

		// Takeover Targets
		if($select_product[0]->ID == 2728){
			wp_redirect( home_url().'/btm_products/takeover-targets/?article_id='.$post_id, 301 );
			exit();
		}

		// Breakthrough Wealth
		if($select_product[0]->ID == 2729){
			wp_redirect( home_url().'/btm_products/breakthrough-wealth/?article_id='.$post_id, 301 );
			exit();
		}

		// Hidden market profits
		if($select_product[0]->ID == 2726){
			wp_redirect( home_url().'/btm_products/hidden-market-profits/?article_id='.$post_id, 301 );
			exit();
		}

		// Biotech Insider
		if($select_product[0]->ID == 2725){
			wp_redirect( home_url().'/btm_products/biotech-insider/?article_id='.$post_id, 301 );
			exit();
		}

		// Behind the markets
		if($select_product[0]->ID == 2724){
			wp_redirect( home_url().'/btm_products/behind-the-markets/?article_id='.$post_id, 301 );
			exit();
		}
	}

	//welcome_video
	if ( is_single() && 'welcome_video' == get_post_type() ) {
		$post_id = get_the_ID();
		$select_product = get_field('select_product', $post_id);

		// Takeover Targets
		if($select_product[0]->ID == 2728){
			wp_redirect( home_url().'/btm_products/takeover-targets/?product_welcome_video=WV' );
			exit();
		}

		// Breakthrough Wealth
		if($select_product[0]->ID == 2729){
			wp_redirect( home_url().'/btm_products/breakthrough-wealth/?product_welcome_video=WV' );
			exit();
		}

		// Hidden market profits
		if($select_product[0]->ID == 2726){
			wp_redirect( home_url().'/btm_products/hidden-market-profits/?product_welcome_video=WV' );
			exit();
		}

		// Biotech Insider
		if($select_product[0]->ID == 2725){
			wp_redirect( home_url().'/btm_products/biotech-insider/?product_welcome_video=WV' );
			exit();
		}

		// Behind the markets
		if($select_product[0]->ID == 2724){
			wp_redirect( home_url().'/btm_products/behind-the-markets/?product_welcome_video=WV' );
			exit();
		}
	}
}


add_action('restrict_manage_posts', 'btm_products_tags_filter_function');
function btm_products_tags_filter_function(){
	global $typenow;
	$cpts = array( 'latest_articles', 'stock_portfolio','bonus_reports' );
	if (!in_array($typenow, $cpts)) {
		return;
	}
	echo '<div class="btm_products_filter tags_keyword_filter" style="width: 120px;display: inline-block;">';
	$terms = get_terms( array(
		'taxonomy' => 'tags',
		'hide_empty' => false,
	) );
	$taxWoo = [];
	$taxWoo['none'] =  __( 'Filter by tags', 'btm' );
	foreach ($terms as $term) {
		$taxWoo[$term->term_id] = $term->name;
	}
	woocommerce_wp_select( array(
		'id'          	=> 'btm_products_tags_filter',
		'value'   => $_REQUEST['btm_products_tags_filter'] ? $_REQUEST['btm_products_tags_filter'] : '',
		'desc_tip'    => true,
		'options'     => $taxWoo,
	) );
	echo '<style>.btm_products_filter>p.form-field.btm_products_filter_field,.btm_products_filter.tags_keyword_filter>p {margin: 0;}.btm_products_filter.tags_keyword_filter select#btm_products_tags_filter {max-width: 95%;}</style></div>';
}

add_filter( 'request', 'request_btm_products_tags_filter_function', 999 );
function request_btm_products_tags_filter_function( $vars ){
	global $typenow;
	$cpts = array( 'latest_articles', 'stock_portfolio','bonus_reports' );
	if (in_array($typenow, $cpts)) {
		// Filter the orders by the posted customer.
		if ( isset( $_GET['btm_products_tags_filter'] ) && $_GET['btm_products_tags_filter'] > 0 ) {
			$vars['tax_query'][] = array(
				'taxonomy'  => 'tags',
				'field' 	=> 'term_id',
				'terms' 	=> array($_REQUEST['btm_products_tags_filter'])
			);		
		}
	}
	return $vars;
}


add_filter( 'request', 'request_btm_products_tags_filter_search_function', 999 );
function request_btm_products_tags_filter_search_function( $vars ){
	global $typenow;
	$cpts = array( 'latest_articles', 'stock_portfolio','bonus_reports' );
	if (in_array($typenow, $cpts)) {
		if ( isset( $_GET['s'] ) ) {
			if(!empty(keywordsearch($_GET['s'])) && is_array(keywordsearch($_GET['s']))){
				$vars['meta_query'][] = array(
					'key'    => 'add_keyword',
					'value'    => $_GET['s'],
					'compare'  => 'LIKE'
				);
				unset($vars['s']);	
			}	
		}
	}
	return $vars;
}
/*****************************************************/
// define the wp_link_query callback 
// add the filter 
add_filter( 'wp_link_query', 'add_link_by_tags_keyword', 10, 2 ); 
function add_link_by_tags_keyword( $results, $query ) { 
	if(isset($query['s'])){
		$get_tags_by_name = get_term_by('name', $query['s'], 'tags');
		$ids = array_column($results, 'ID');
		if(!empty($get_tags_by_name) && !in_array($get_tags_by_name->term_id, $ids) && !empty($ids)){
			$results[] = array(
				'ID'        => $get_tags_by_name->term_id,
				'title'     => $get_tags_by_name->name,
				'permalink' => get_tag_link($get_tags_by_name->term_id),
				'info'      => 'hello',
			);
		}
	}
	return $results; 
}; 
add_action( 'powerpack/query/tags_related_post', function( $query ) {
	$get_post_object = get_queried_object();
	if(isset($get_post_object->ID) && !empty($get_post_object->ID)){
		$meta_query[] = array(
			'key'     => 'select_product',
			'value'   => $get_post_object->ID,
			'compare' => 'LIKE'
		);
		$query->set('meta_query', $meta_query);
	}
	if(isset($_GET['search_tags']) && !empty($_GET['search_tags'])){
		$tax_query[] = array(
			'taxonomy'	=> 'tags',
			'field'   	=> 'slug',
			'terms' 	=> $_GET['search_tags']
		);
		$query->set('tax_query', $tax_query);
	}
});
/*
 * Create product waiting list option
 */
require_once 'inc/class-product-waiting-list.php';
//End product waiting list
add_action('woocommerce_subscription_status_active','modify_subscriptions_for_some_products', 80, 1);
function modify_subscriptions_for_some_products( $subscription ) {

	$last_order = $subscription->get_last_order( 'all', 'any' );

	if ( wcs_order_contains_renewal( $last_order ) ) {
		return;
	} else {
		/**
		 * BTM - Alliance Package
		 */
		$alliance = array(
			'btm-alli',
			'comp-btm-alli',
			'csr-btm-alli'
		);//All product variations Any previous subscription should be inactive
		/**
		 * BTM - Lifetime
		 */
		$btm_lifetime = array(
			'btm-lifetime',
			'comp-btm-lifetime',
			'csr-btm-lifetime'
		);
		//BTM -Lifetime inactive
		$btm_lifetime_inactive = array(
			'btm-bronze-49',
			'btm-bronze',
			'btm-platinum-99',
			'btm-platinum',
			'btm-silver-99',
			'btm-silver',
			'btm-second-year',
			'csr-btm-bronze',
			'csr-btm-platinum',
			'csr-btm-second-year',
			'csr-btm-silver'
		);

		/**
		 * BTM - Second Year Offer
		 */
		$btm_second_year_offer = array(
			'btm-second-year',
			'comp-btm-second-year',
			'csr-btm-second-year'
		);
		//BTM -Second Year Offer inactive
		$btm_aecond_year_offer_inactive = array(
			'btm-bronze-49',
			'btm-bronze',
			'btm-platinum-99',
			'btm-platinum',
			'btm-silver-99',
			'btm-silver',
			'csr-btm-bronze',
			'csr-btm-platinum',
			'csr-btm-silver'
		);
		/**
		 * Biotech Insider - Lifetime
		 */
		$biotech_insider_lifetime = array(
			'bi-lifetime',
			'comp-bi-lifetime',
			'csr-bi-lifetime',
			'bi-upsell-lifetime'
		);
		$biotech_insider_lifetime_inactive = array(
			'bi-three-month',
			'bi-special-offer-annual-new',
			'csr-bi-special-offer-annual-new',
			'csr-special-offer-annual',
			'bi-upsell-annual',
			'bi-renewal-upsell-annual'
		);
		$current_variation_id = null;
		$current_subscription_recurly_plan_code = null;

		foreach ($subscription->get_items() as $item_key => $item ):			
			// $current_product_id   = $item->get_product_id(); // the Product id
			$current_variation_id = $item->get_variation_id(); // the Variation id
			$current_subscription_recurly_plan_code = trim( get_post_meta($current_variation_id, '_recurly_plan_code', true) );
		endforeach;

		//============ Get all active subscription ============
		// Getting the user ID from the current subscription object
		$user_id = $subscription->get_user_id();
		update_user_meta($user_id, 'inactive_subscriptions', '0');
		$all_user_subscriptions = wcs_get_users_subscriptions( $user_id );
		// $purchased_recurly_plan_code = null;
		$subscription_variation_id = '';
		foreach( $all_user_subscriptions as $subscription_obj )
		{
			if( $subscription_obj->get_status() === 'active' )
			{
				$items                  = $subscription_obj->get_items();
				foreach ($items as $item) {
					$subscription_variation_id = $item->get_variation_id();
				}
				$_recurly_plan_code = trim( get_post_meta($subscription_variation_id, '_recurly_plan_code', true) );

				//BTM - Alliance Package
				if( in_array( $current_subscription_recurly_plan_code, $alliance) )
				{
					if( ! in_array( $_recurly_plan_code, $alliance ) )
					{
						$subscription_obj->update_status( 'on-hold' );
						$uuid = $subscription_obj->get_meta('_recurly_subscription_uuid');
						if(!empty($uuid))
						{
							cancel_recurly_subscription_by_uuid($uuid);
						}
					}
				}
				if( in_array( $current_subscription_recurly_plan_code, $btm_lifetime ) )//BTM - Lifetime
				{
					if( ! in_array( $_recurly_plan_code, $btm_lifetime ) && in_array( $_recurly_plan_code, $btm_lifetime_inactive ) )
					{
						$subscription_obj->update_status( 'on-hold' );
						$uuid = $subscription_obj->get_meta('_recurly_subscription_uuid');
						if(!empty($uuid))
						{
							cancel_recurly_subscription_by_uuid($uuid);
						}
					}
				}
				if( in_array( $current_subscription_recurly_plan_code, $btm_second_year_offer ) )//BTM - Second Year Offer
				{
					if( ! in_array( $_recurly_plan_code, $btm_second_year_offer ) && in_array( $_recurly_plan_code, $btm_aecond_year_offer_inactive ) )
					{
						$subscription_obj->update_status( 'on-hold' );
						$uuid = $subscription_obj->get_meta('_recurly_subscription_uuid');
						if(!empty($uuid))
						{
							cancel_recurly_subscription_by_uuid($uuid);
						}
					}
				}
				if( in_array( $current_subscription_recurly_plan_code, $biotech_insider_lifetime ) )//Biotech Insider - Lifetime
				{
					if( ! in_array( $_recurly_plan_code, $biotech_insider_lifetime ) && in_array( $_recurly_plan_code, $biotech_insider_lifetime_inactive ) )
					{
						$subscription_obj->update_status( 'on-hold' );
						$uuid = $subscription_obj->get_meta('_recurly_subscription_uuid');
						if(!empty($uuid))
						{
							cancel_recurly_subscription_by_uuid($uuid);
						}
					}
				}
			}
		}
	}	
}

/**
 * Cron for inactive subscription if lifetime is active
 */
// Schedule the event every 10 minutes
// Add the 'every_10_minutes' custom interval
add_filter('cron_schedules', 'add_every_10_minutes_interval');
function add_every_10_minutes_interval($schedules) {
    $schedules['every_10_minutes'] = array(
        'interval' => 600, // 10 minutes in seconds
        'display'  => __('Every 10 Minutes')
    );
    return $schedules;
}
add_action('wp', 'check_higher_order_subscription_cron_schedule');
function check_higher_order_subscription_cron_schedule() {
    if (!wp_next_scheduled('check_higher_order_subscription_event')) {
        wp_schedule_event(time(), 'every_10_minutes', 'check_higher_order_subscription_event');
    }
}

// Hook into that action that'll fire every five minutes
add_action('check_higher_order_subscription_event', 'check_higher_order_subscription_event_callback');
function check_higher_order_subscription_event_callback() {
	error_log(print_r('Every 10 minutes : '.date("Y-m-d H:i:s", time()), true));
	$available_users = get_users(
		array(
			'fields' 	=> 'ids',
			'number' 	=> 10,
			'orderby' 	=> 'ID',
			'order' 	=> 'DESC',
			'role' 		=> 'subscriber',
			'meta_query' => array(
				array(
					'key' 		=> 'inactive_subscriptions',
					'value' 	=> 0
				)
			)
		)
	);

	/**
	 * BTM - Alliance Package
	 */
	$alliance = array(
		'btm-alli',
		'comp-btm-alli',
		'csr-btm-alli'
	);//All product variations Any previous subscription should be inactive
	/**
	 * BTM - Lifetime
	 */
	$btm_lifetime = array(
		'btm-lifetime',
		'comp-btm-lifetime',
		'csr-btm-lifetime'
	);
	//BTM -Lifetime inactive
	$btm_lifetime_inactive = array(
		'btm-bronze-49',
		'btm-bronze',
		'btm-platinum-99',
		'btm-platinum',
		'btm-silver-99',
		'btm-silver',
		'btm-second-year',
		'csr-btm-bronze',
		'csr-btm-platinum',
		'csr-btm-second-year',
		'csr-btm-silver'
	);

	/**
	 * BTM - Second Year Offer
	 */
	$btm_second_year_offer = array(
		'btm-second-year',
		'comp-btm-second-year',
		'csr-btm-second-year'
	);
	//BTM -Second Year Offer inactive
	$btm_aecond_year_offer_inactive = array(
		'btm-bronze-49',
		'btm-bronze',
		'btm-platinum-99',
		'btm-platinum',
		'btm-silver-99',
		'btm-silver',
		'csr-btm-bronze',
		'csr-btm-platinum',
		'csr-btm-silver'
	);
	/**
	 * Biotech Insider - Lifetime
	 */
	$biotech_insider_lifetime = array(
		'bi-lifetime',
		'comp-bi-lifetime',
		'csr-bi-lifetime',
		'bi-upsell-lifetime'
	);
	$biotech_insider_lifetime_inactive = array(
		'bi-three-month',
		'bi-special-offer-annual-new',
		'csr-bi-special-offer-annual-new',
		'csr-special-offer-annual',
		'bi-upsell-annual',
		'bi-renewal-upsell-annual'
	);

	$active_subscriptions = array();
	if(!empty( $available_users )) {
		foreach ( $available_users as $user_id ) {
			$all_user_subscriptions = wcs_get_users_subscriptions( $user_id );

			$subscription_variation_id = '';
			foreach( $all_user_subscriptions as $subscription_obj )
			{
				if( $subscription_obj->get_status() == 'active' )
				{
					$items                  = $subscription_obj->get_items();
					foreach ($items as $item) {
						$subscription_variation_id = $item->get_variation_id();
					}
					$active_subscriptions[$subscription_obj->get_id()] = trim( get_post_meta($subscription_variation_id, '_recurly_plan_code', true) );

				}
			}
			if( count($active_subscriptions) > 1)
			{								
				if( array_intersect( $alliance, $active_subscriptions ) )
				{
					$result=array_diff($active_subscriptions, $alliance);
					if(count($result) > 0)
					{
						error_log(print_r('$active_subscriptions', true));
						error_log(print_r($result, true));
						foreach ($result as $subscription_id => $subscription_recurly_code) {
							$subscription = wcs_get_subscription( $subscription_id );
							$subscription->update_status( 'on-hold' );
							$uuid = $subscription->get_meta('_recurly_subscription_uuid');
							if(!empty($uuid))
							{
								//cancel_recurly_subscription_by_uuid($uuid);
							}
						}
					}
				}else{
					if( array_intersect( $btm_lifetime, $active_subscriptions ) )
					{
						$result=array_diff($active_subscriptions, $btm_lifetime);
						$btm_lifetime_inactive_intersect = array_intersect($result, $btm_lifetime_inactive);
						if(count($btm_lifetime_inactive_intersect) > 0)
						{
							error_log(print_r('$biotech_insider_lifetime_inactive_intersect', true));
							error_log(print_r($biotech_insider_lifetime_inactive_intersect, true));
							foreach ($btm_lifetime_inactive_intersect as $subscription_id => $subscription_recurly_code) {
								$subscription = wcs_get_subscription( $subscription_id );
								$subscription->update_status( 'on-hold' );
								$uuid = $subscription->get_meta('_recurly_subscription_uuid');
								if(!empty($uuid))
								{
									//cancel_recurly_subscription_by_uuid($uuid);
								}
							}
						}
					}
					if( array_intersect( $btm_second_year_offer, $active_subscriptions ) )
					{
						$result=array_diff($active_subscriptions, $btm_second_year_offer);
						$btm_aecond_year_offer_inactive_intersect = array_intersect($result, $btm_aecond_year_offer_inactive);
						if(count($btm_lifetime_inactive_intersect) > 0)
						{
							error_log(print_r('$biotech_insider_lifetime_inactive_intersect', true));
							error_log(print_r($biotech_insider_lifetime_inactive_intersect, true));
							foreach ($btm_aecond_year_offer_inactive_intersect as $subscription_id => $subscription_recurly_code) {
								$subscription = wcs_get_subscription( $subscription_id );
								$subscription->update_status( 'on-hold' );
								$uuid = $subscription->get_meta('_recurly_subscription_uuid');
								if(!empty($uuid))
								{
									//cancel_recurly_subscription_by_uuid($uuid);
								}
							}
						}
					}
					if( array_intersect( $biotech_insider_lifetime, $active_subscriptions ) )
					{
						$result=array_diff($active_subscriptions, $biotech_insider_lifetime);
						$biotech_insider_lifetime_inactive_intersect = array_intersect($result, $biotech_insider_lifetime_inactive);
						if(count($biotech_insider_lifetime_inactive_intersect) > 0)
						{
							error_log(print_r('$biotech_insider_lifetime_inactive_intersect', true));
							error_log(print_r($biotech_insider_lifetime_inactive_intersect, true));
							foreach ($biotech_insider_lifetime_inactive_intersect as $subscription_id => $subscription_recurly_code) {
								$subscription = wcs_get_subscription( $subscription_id );
								$subscription->update_status( 'on-hold' );
								$uuid = $subscription->get_meta('_recurly_subscription_uuid');
								if(!empty($uuid))
								{
									//cancel_recurly_subscription_by_uuid($uuid);
								}
							}
						}
					}
				}
			}
			update_user_meta($user_id, 'inactive_subscriptions', '1');
			unset($active_subscriptions);
		}
	}
}

/**
 * End Cron for inactive subscription if lifetime is active
 */



// // Function to set same password for new user creation
// add_filter( 'wp_pre_insert_user_data', function ($data, $update) {
// 	if ($update) {
// 		return $data;
// 	}
// 	elseif($data['role'] == 'subscriber'){
// 		$data['user_pass'] = 'welcome2BTM!';
// 		return $data;
// 	}
// }, 99, 2);
// 

// Allow CSR to edit username
add_filter('username_changer_can_change_own_username', 'allow_CSR_role', 10, 1);

function allow_CSR_role ($allowed){
	$user_data     = wp_get_current_user();
	$user_roles    = $user_data->roles;

	if ( in_array( 'customerserviceagent', $user_roles, true ) ) {
		$allowed = true;
	}
	
	return $allowed;
}

function cancel_recurly_subscription_by_uuid($uuid) {
    if (class_exists('WC_Recurly_API')) {
        $response = WC_Recurly_API::request([], 'subscriptions/uuid-' . $uuid . '/cancel', 'PUT');
        
        if (empty($response->error)) {
			error_log(print_r("================== Subscriptions : ".$uuid." => Has been canceled, due to higher tire subscription exists =================", true));
        } else {
            error_log(print_r("================== Subscriptions : ".$uuid." => faild =================", true));
            error_log(print_r($response, true));
        }
    }
}

// ------------------------------Start WP_CLI_Command----------------------------------
if ( class_exists( 'WP_CLI_Command' ) )
{
	WP_CLI::add_command( 'delete_expired_subscriptions', 'delete_expired_subscriptions' ); 
	WP_CLI::add_command( 'active_subscriptions', 'active_subscriptions' ); 
	
}else{
	error_log(print_r('Error get_webhook_queue_list', true));
}

function delete_expired_subscriptions( $args, $assoc_args )
{
	global $wpdb;

    $original_table = $wpdb->prefix . 'posts';
    $migrated_table = $wpdb->prefix . 'shop_subscription_expired';
    $postmeta 		= $wpdb->prefix . 'postmeta';

	// Create migrated_data table if it doesn't exist
	if ($wpdb->get_var("SHOW TABLES LIKE '$migrated_table'") != $migrated_table) {
		// Fetch the structure of the original table
		$original_table_structure = $wpdb->get_results("DESCRIBE $original_table");
		// Build the SQL for creating the migrated_data table
		$create_table_sql = "CREATE TABLE $migrated_table (";
		foreach ($original_table_structure as $column) {
			$create_table_sql .= "{$column->Field} {$column->Type}, ";
		}
		$create_table_sql = rtrim($create_table_sql, ', '); // Remove trailing comma
		$create_table_sql .= ");";
		// Execute the SQL to create the table
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($create_table_sql);
	}

	$ps = isset($assoc_args['ps']) && !empty($assoc_args['ps']) ? $assoc_args['ps'] : 10;
	if($ps)
	{
		// Fetch subscription data from original table
		$subscription_data = $wpdb->get_results("SELECT * FROM $original_table WHERE `post_type`='shop_subscription' AND post_status='wc-expired' LIMIT $ps");
		if(!empty($subscription_data) && is_array($subscription_data))
		{
			foreach($subscription_data as $subscription)
			{
				$subscription_metadata = $wpdb->get_results("SELECT * FROM $postmeta WHERE `post_id`=$subscription->ID");
				$subscription_metadata_json = array('post_content' => json_encode($subscription_metadata));
				$insert_migrated_table = $wpdb->insert($migrated_table, (array) array_merge((array)$subscription, $subscription_metadata_json));
				if($insert_migrated_table)
				{
					$wpdb->delete($original_table,['ID' => $subscription->ID], ['%d']);// wp_posts
					WP_CLI::line( 'Deleted expired subscriptions from '.$original_table );
					$wpdb->delete($postmeta,['post_id' => $subscription->ID], ['%d']); //wp_postmeta
					WP_CLI::line( 'Deleted expired subscriptions metadata from '.$postmeta );
				}
			}
			WP_CLI::success( $ps.' Subscription data migrated successfully.' );
		}
	}else{
		WP_CLI::success( 'Process is empty' );
	}
}

function active_subscriptions($args, $assoc_args) {
    global $wpdb;
    $active_subscriptions = $wpdb->prefix . 'active_subscriptions_v5';
    $as = isset($assoc_args['as']) && !empty($assoc_args['as']) ? $assoc_args['as'] : 1;
    $uuids = isset($assoc_args['uuid']) && !empty($assoc_args['uuid']) ? $assoc_args['uuid'] : false;

    if ($uuids) {
        $subscription_data = explode(',', $uuids);
    } else {
        $active_sub_prepared = $wpdb->prepare("SELECT `uuid` FROM $active_subscriptions WHERE `status`=0 LIMIT %d", $as);
        $subscription_data = $wpdb->get_col($active_sub_prepared);
    }

    if (isset($subscription_data) && is_array($subscription_data)) {
		foreach ($subscription_data as $subscription_uuid) {
			$get_subscriptions = get_posts( array(
				'post_type'     => 'shop_subscription',
				'posts_per_page'=> -1,
				'post_status'   => 'wc-active',
				'fields'        => 'ids',
				'meta_query'    => array(
					array(
						'key'   => '_recurly_subscription_uuid',
						'value' => $subscription_uuid
					)
				)
			));
			if(empty($get_subscriptions))
			{ 
				$recurly_subscription = get_recurly_subscription($subscription_uuid);
				$args_product_variation = array(
					'post_type'     => 'product_variation',
					'post_status'   => array( 'publish' ),
					'numberposts'   => -1,
					'fields'        => 'ids'
				);
				$product_variation_ids = get_posts( $args_product_variation );
				$product_variation_id = null;
				$comp_code = array('comp-btm-bronze' =>'btm-bronze', 'comp-btm-silver' =>'btm-silver', 'comp-btm-platinum' =>'btm-platinum', 'comp-btm-lifetime' =>'btm-lifetime', 'comp-btm-alli' =>'btm-alli', 'comp-bi-annual' =>'bi-annual', 'comp-bi-two-year' =>'bi-two-year', 'comp-bi-lifetime' =>'bi-lifetime', 'comp-bw-annual' =>'bw-annual', 'comp-bw-two-year' =>'bw-two-year', 'comp-bw-lifetime' =>'bw-lifetime', 'comp-hmp-annual' =>'hmp-annual', 'comp-hmp-two-year' =>'hmp-two-year', 'comp-hmp-lifetime' =>'hmp-lifetime', 'comp-tt-annual' =>'tt-annual', 'comp-tt-two-year' =>'tt-two-year', 'comp-tt-lifetime' =>'tt-lifetime','bi-upsell-annual'=>'bi-annual','bi-upsell-lifetime' => 'bi-lifetime','csr-btm-alli' =>'btm-alli');
				
				foreach ($product_variation_ids as $variation_id) {
					$variation_plan_code = trim(get_post_meta($variation_id, '_recurly_plan_code', true));
					
					if (array_key_exists($variation_plan_code, $comp_code)) {
						$variation_plan_code = $comp_code[$variation_plan_code];
					}
					if ($variation_plan_code == $recurly_subscription->plan->code) {
						$product_variation_id = $variation_id;
						break;
					}
				}
				if(!$product_variation_id)
				{
					$active_subscriptions_v5_update = $wpdb->update($active_subscriptions, array('status'=> 2), array( 'uuid' => $subscription_uuid ));
					WP_CLI::line( 'active_subscriptions_v5_update' );
					WP_CLI::line( print_r($subscription_uuid) );
					// WP_CLI::error($subscription_uuid);
					continue;
				}
				if (!isset($recurly_subscription->error)) {
					$recurly_accounts = get_recurly_account($recurly_subscription->account->id);
					if (!isset($recurly_accounts->error)) {
						$user_check = 0;
						$user = $recurly_subscription->account->email ?: $recurly_subscription->account->code;

						if (email_exists($recurly_subscription->account->email) || email_exists($recurly_subscription->account->code)) {
							$user = get_user_by('email', $recurly_subscription->account->email) ?: get_user_by('email', $recurly_subscription->account->code);
							$user_check = 1;
						}

						$sub_data = create_subscription_data($user_check, $product_variation_id, $recurly_subscription, $recurly_accounts);
						WP_CLI::warning( 'Creating new subscription...' );
						$response = missing_subscription_create($sub_data);
						// WP_CLI::success(print_r($response));
					} else {
						WP_CLI::error($recurly_accounts->error->message);
					}
				} else {
					WP_CLI::error($recurly_subscription->error->message);
				}
			}else{
				WP_CLI::warning($subscription_uuid.' Subscription already exists.');
				$active_subscriptions_v5_update = $wpdb->update($active_subscriptions, array('status'=> 1), array( 'uuid' => $subscription_uuid ));
				if($active_subscriptions_v5_update)
					WP_CLI::success($subscription_uuid." Subscription table {$active_subscriptions} updated.");
			}
        }
    }
}

function get_recurly_subscription($subscription_uuid) {
    return WC_Recurly_API::request([], 'subscriptions/uuid-' . $subscription_uuid, 'GET');
}

function get_recurly_account($account_id) {
    return WC_Recurly_API::request([], 'accounts/' . $account_id, 'GET');
}

function create_subscription_data($user_check, $product_variation_id, $recurly_subscription, $recurly_accounts) {
	if(email_exists($recurly_subscription->account->code) || email_exists($recurly_subscription->account->email))
	{
		$user = get_user_by( 'email', $recurly_subscription->account->email ) ?? get_user_by( 'email', $recurly_subscription->account->code );
		$user_check = 1;
		$user_id = $user->ID;
	}else{
		$user_check = 0;
		$user = $recurly_subscription->account->email ?? $recurly_subscription->account->code;
	}
	$sub_data = array(
		'user_check'=> $user_check,
		'product_variation_id'      => $product_variation_id, 
		'subscription_uuid'         => $recurly_subscription->uuid, 
		'status'                    => $recurly_subscription->state,
		'current_period_started_at' => $recurly_subscription->current_period_started_at,
		'current_period_ends_at'    => $recurly_subscription->current_period_ends_at,
		'address' => array(
			'first_name'    => $recurly_accounts->first_name ? $recurly_accounts->first_name :'',
			'last_name'     => $recurly_accounts->last_name ? $recurly_accounts->last_name : '',
			'email'         => $recurly_accounts->email ? $recurly_accounts->email : '',
			'address_1'     => $recurly_accounts->address->street1 ? $recurly_accounts->address->street1 : '',
			'address_2'     => $recurly_accounts->address->street2 ? $recurly_accounts->address->street2 : '',
			'city'          => $recurly_accounts->address->city ? $recurly_accounts->address->city : '',
			'state'         => $recurly_accounts->address->region ? $recurly_accounts->address->region : '',
			'postcode'      => $recurly_accounts->address->postal_code ? $recurly_accounts->address->postal_code : '',
			'country'       => $recurly_accounts->address->country ? $recurly_accounts->address->country : '',
			'phone'         => $recurly_accounts->address->phone ? $recurly_accounts->address->phone : '',
		)
	);
	if($user_check)
	{
		$sub_data['user'] = $user->ID;
	}else{
		$sub_data['user'] = $user;
	}
    return $sub_data;
}

function missing_subscription_create($sub_data)
{
	extract($sub_data);
	$message = '';
	if(!$user_check)
	{
		$password = wp_generate_password(12, true);
		$result = wp_insert_user(array(
			'user_login'    => $address['email'],
			'user_pass'     => $password,
			'user_email'    => $address['email'],
			'first_name'    => $address['first_name'],
			'last_name'     => $address['last_name'],
			'display_name'  => $address['first_name'] . ' ' . $address['last_name'],
			'role'          => 'customer'
		));
		if(is_wp_error($result)){
			$error = $result->get_error_message();
			return $error;
		}
		$user_id = $result;
	}else{
		$user_id = $user;
	}
	if($user_id)
	{
		$sub_status = $status == 'active' ? $status : 'pending';
		// Now we create the order
		$order = wc_create_order(array('customer_id' => $user_id));

		if (is_wp_error($order)) {
			$error = $order->get_error_message();
			WP_CLI::error($error);
		}
		$product = wc_get_product($product_variation_id);

		$order->add_product($product, 1);
		$order->set_address($address, 'billing');
		$order->calculate_totals();
		$order->update_meta_data('_recurly_subscription_uuid', $subscription_uuid);
		$order->save();

		$start_date = gmdate('Y-m-d H:i:s', strtotime($current_period_started_at));// +10 SEC
		$sub = wcs_create_subscription(array(
			'order_id'          => $order->get_id(),
			'status'            => $sub_status,
			'billing_period'    => WC_Subscriptions_Product::get_period($product),
			'billing_interval'  => WC_Subscriptions_Product::get_interval($product)
		));

		if (is_wp_error($sub)) {
			$error = $sub->get_error_message();
			WP_CLI::error($error);
		}			
		// Add product to subscription
		$sub->add_product($product, 1);
		$sub->update_meta_data('_recurly_subscription_uuid', $subscription_uuid);
		$next_payment = gmdate('Y-m-d H:i:s', strtotime($current_period_started_at));// +10 SEC
		$dates = array(
			'start'             => $start_date,
			'next_payment'      => WC_Subscriptions_Product::get_first_renewal_payment_date($product, $start_date),
			'end'               => WC_Subscriptions_Product::get_expiration_date($product, $start_date),
			'trial_end'         => WC_Subscriptions_Product::get_trial_expiration_date($product, $start_date)
		);

		$sub->update_dates($dates);
		$sub->calculate_totals();
		$sub->set_address($address, 'billing');

		// Update order status with custom note
		$note = !empty($note) ? $note : __('Subscription created by manually admin option.');
		($sub_status == 'active') ? $order->update_status('completed', $note, true) : $order->update_status($sub_status, $note, true);
		// Also update subscription status to active from pending (and add note)
		$sub->update_status($sub_status, $note, true);
		update_post_meta( $sub->get_id(), '_requires_manual_renewal', "true" );
		$message ='';
		if ( function_exists( 'wc_memberships' ) ) {
			$plan_id = null;
			foreach(wc_memberships_get_membership_plans() as $membership_plan){
				$get_product_ids = $membership_plan->get_product_ids();
				if(in_array($product_variation_id, $membership_plan->get_product_ids()))
				{
					$plan_id = $membership_plan->get_id();
				}
			}
			if(!empty($plan_id))
			{					
				$active_memberships = wc_memberships_get_user_memberships( $user_id, array('status' => 'active') );
				foreach($active_memberships as $active_membership)
				{
					$active_membership_ids[] = $active_membership->plan_id;
				}
				$args = array(
					'plan_id'       => $plan_id,
					'user_id'       => $user_id,
					'product_id'    => $product_variation_id, 
					'order_id'      => $order->get_id(),
				);
				if(empty($active_membership_ids)){
					$membership = wc_memberships_create_user_membership( $args );
					$message .= '<div class="notice notice-success"><p>Membership created successfully.</p></div>';
				}
				elseif(!in_array($plan_id,$active_membership_ids))
				{
					$membership = wc_memberships_create_user_membership( $args );
					$message .= '<div class="notice notice-success"><p>Membership created successfully.</p></div>';
				} 
			}
		}              
		WP_CLI::success('New subscription has been created successfully, Subscription ID: '.$sub->get_id(). ' with Order ID: ' .$order->get_id()); 
		// WP_CLI::success(print_r(array('order_id' => $order->get_id(),'subscription_id' => $sub->get_id())));
		// return  array('order_id' => $order->get_id(),'subscription_id' => $sub->get_id());
	}else
	{
		WP_CLI::error('$error');
	}
}
// ------------------------------End WP_CLI_Command----------------------------------
/*
 * Expired Subscriptions
 */
require_once 'inc/class-expired-subscriptions.php';
// 
function test_fn() {
    echo "<pre>";
    // print_r($subscriptionExpired);
    echo "</pre>";
}

add_shortcode('test','test_fn');