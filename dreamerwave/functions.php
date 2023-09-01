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
	wp_localize_script('savior-pro-scripts','admin_ajax_url',
					   array( 
						   'ajaxurl' => admin_url( 'admin-ajax.php' ) , 
						   'siteurl' => site_url(), 
						   'sitenonce' => wp_create_nonce('change_user_name'), 
						   'my_account' => site_url('my-account'), 
						   'logout_url'=> wp_logout_url(get_bloginfo('url'))
					   ) 
					  );
}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

// Logout URL and ShortCode
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
	$content = esc_url( wp_logout_url($homepage));
	return $content;
}
// Popular Post Query
add_action( 'powerpack/query/pop-posts', function( $query ) {
	$query->set( 'orderby', 'meta_value_num' );
	$query->set( 'meta_key', 'views' );
});

// Post Cat Class 
add_shortcode( 'getParentCategoryCls', 'getParentCategory' );
function getParentCategory( ) {
	ob_start();

	$category = get_the_category(); 
	$category_parent_id = $category[0]->category_parent;
	if ( $category_parent_id != 0 ) {
		$category_parent = get_term( $category_parent_id, 'category' );
		$css_slug = $category_parent[0]->slug;
		echo $css_slug .' post-category-cls';
	} else {
		$css_slug = $category[0]->slug;
		echo $css_slug .' post-category-cls';
	}

	$output = ob_get_contents();
	ob_end_clean(); 
	return  $output;

}
// Post Cat Class 

// Search Function only for Post
function SearchFilter($query) {
	if ($query->is_search && !is_admin()) {
		$query->set('post_type',array('post', 'replies'));
	}
	return $query;
}
add_filter('pre_get_posts','SearchFilter');
// Search Function only for Post

// Current User name
function display_user_name(){
	ob_start();
	$current_user = wp_get_current_user();
	// 	echo $current_user->display_name;
	echo $current_user->nickname;
	$output = ob_get_clean();
	return $output;	
}
add_shortcode('current_user_name', 'display_user_name');
// Current User name

/**
 * Capability To Customers Delete their own account
*/ 
function delete_customers_account_account_setting(){
	$user_id = get_current_user_id();
	wp_delete_user($user_id);
	exit();
}
add_action('wp_ajax_dreamerwave_delete_customers_account_account_setting', 'delete_customers_account_account_setting');
/**
 * Logout From All Session
*/ 
function destroy_sessions_fun(){
	$sessions = WP_Session_Tokens::get_instance( get_current_user_id() );
	$sessions->destroy_others(  wp_get_session_token() );
	wp_send_json_success( array( 'message' => __( 'You are now logged out everywhere else.' ) ) );
}
add_action('wp_ajax_wp_ajax_destroy_sessions', 'destroy_sessions_fun');

// Current user membership level

function display_current_user_membership_func(){
	ob_start();
	if(is_user_logged_in() && function_exists('pmpro_hasMembershipLevel') && pmpro_hasMembershipLevel()) {
		global $current_user;
		$current_user->membership_level = pmpro_getMembershipLevelForUser($current_user->ID);
?>
<div class="membership-details">
	<div class="membership-price">
		<h4><?php echo '$' . $current_user->membership_level->billing_amount;	?></h4>
	</div>
	<div class="membership-content-details">
		<h4><?php echo 'This is what you pay ' . $current_user->membership_level->name .'.'; ?></h4>
		<p>Payments are done <?php echo $current_user->membership_level->name ; ?> based on your plan preferences.</p>
	</div>

</div>

<div class="memb-plan-details-sec">
	<div class="memb-plan-name">
		<img src="http://dreamwaver.saviormarketing.com/wp-content/uploads/2022/03/plan-logo.png">
		<h4>Plan</h4>
	</div>
	<div class="memb-plan-details">
		<h4><?php echo $current_user->membership_level->name; ?></h4>
		<h5><?php echo '$' . $current_user->membership_level->billing_amount;	?></h5>
	</div>
	<div class="memb-plan-status">
		<?php 
		if(pmpro_hasMembershipLevel())	{
		?>
		<h4 class="memb-active"><i class="fas fa-check-circle"></i> Active</h4>
		<?php
		}else {
		?>
		<h4 class="memb-not-active"><i class="fas fa-times-circle"></i> Deactivate</h4>
		<?php
		}
		?>
	</div>
</div>		

<?php
	}else {
?>
<h4 class="memb-notice"> You did not have any active membership </h4>
<?php
	}
	$output = ob_get_clean();
	return $output;	
}
add_shortcode('display_current_user_membership', 'display_current_user_membership_func');

//  Custom Taxonomy List

function list_terms_custom_taxonomy() {
	ob_start();
	$terms = get_terms( array(
		'taxonomy' => 'topics',
		'hide_empty' => false,
	) );
	echo '<div class="helpdesk-post-caption">';
	foreach ($terms as $term) {
?>
<div class="helpdesk-post-taxo-main-sec">
	<div class="taxonony-post-content" term_name="<?php echo $term->slug; ?>">
		<div class="taxonomy-icon">
			<img src="<?php echo get_field('topic_icon', $term); ?>">
		</div>
		<div class="taxonomy-content">
			<div class="taxonomy-name">
				<h4><?php echo $term->name; ?></h4>
			</div>
			<div class="taxonomy-desc">
				<p><?php echo $term->description; ?></p>
			</div>

			<div class="taxonomy-footer-caption">
				<div class="taxonomy-author-cap">
					<?php 
							   $author_id = get_the_author_meta('ID'); 
							   $get_author_gravatar = get_avatar_url($author_id);
					?>
					<h5 class="author-img"><img src="<?php echo esc_url($get_author_gravatar); ?>"></h5>
					<h5 class="author-name">Written by <span><?php echo get_the_author_meta('user_nicename', $author_id); ?></span></h5>
				</div>
				<div class="taxonomy-post-count">
					<h5><?php echo $term->count; ?> articles</h5>
				</div>
			</div>
		</div>
	</div>

	<div class="taxonomy-post-cap" term_name="<?php echo $term->slug; ?>" style="display:none;">
		<?php
							   $args = array(
								   'tax_query' => array(
									   array(
										   'post_type' => 'helpdesk',
										   'taxonomy' => 'topics',
										   'terms' => $term->slug,
										   'field' => 'slug',
										   'include_children' => true,
										   'operator' => 'IN'
									   )
								   ),					
							   );
							   $the_query = new WP_Query($args);
							   if($the_query->have_posts()):
							   while($the_query->have_posts()) : $the_query->the_post();
		?>
		<div class="taxo-post-cap-content">
			<h3><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h3>
			<?php $content = get_the_content();	?>
			<p><?php echo mb_strimwidth($content, 0, 60, '...');  ?></p>
		</div>
		<?php
							   endwhile;
							   else:
		?>
		<div class="taxo-post-cap-content empty-dev">
			<p>There is no helpdesk post yet</p>
		</div>
		<?php
							   endif;
							   wp_reset_postdata();
		?>
	</div>
</div>	
<?php
							  }
	echo '</div>';
	$output = ob_get_clean();
	return $output;	
}
// Add a shortcode that executes our function
add_shortcode( 'helpdesk_taxo', 'list_terms_custom_taxonomy' );

// Add Fav Post
function add_fav_func() {
	ob_start();

	if ( is_user_logged_in() ) {
		echo wpfp_link();
	} else {
		echo '';
	}	

	$output = ob_get_clean();
	return $output;	
}
add_shortcode( 'AddFav', 'add_fav_func' );

// Display Name
function display_first_last_name_func() {
	ob_start();
	$current_user = wp_get_current_user();

	echo $current_user->user_firstname .' '. $current_user->user_lastname;

	// 	echo $current_user->user_nicename;

	$output = ob_get_clean();
	return $output;	

}
add_shortcode('display_first_last_name','display_first_last_name_func');

// Display author full Name
function display_author_full_name_func() {
	ob_start();
	// 	$fname = get_the_author_meta('first_name');
	// 	$lname = get_the_author_meta('last_name');
	// 	$full_name = '';

	// 	if( empty($fname)){
	// 		$full_name = $lname;
	// 	} elseif( empty( $lname )){
	// 		$full_name = $fname;
	// 	} else {
	// 		//both first name and last name are present
	// 		$full_name = "{$fname} {$lname}";
	// 	}
	$authorname = get_the_author_meta('user_login');
	echo $authorname;
	$output = ob_get_clean();
	return $output;	
}
add_shortcode('display_author_full_name','display_author_full_name_func');

// Comment Section Display full name
add_filter( 'get_comment_author', 'wpse_use_user_real_name', 10, 3 ) ;
//use registered commenter first and/or last names if available
function wpse_use_user_real_name( $author, $comment_id, $comment ) {

	//     $firstname = '' ;
	//     $lastname = '' ;
	$usernicename = '';
	//returns 0 for unregistered commenters
	$user_id = $comment->user_id ;

	if ( $user_id ) {
		$user_object = get_userdata( $user_id ) ;
		//$firstname = $user_object->user_firstname ;
		//$lastname = $user_object->user_lastname ; 
		$usernicename = $user_object->user_login ; 
	}

	if ( $usernicename ) {
		//$author = $firstname . ' ' . $lastname ; 
		$author = $usernicename  ; 
		//remove blank space if one of two names is missing
		$author = trim( $author ) ;
	}
	return $author ;

}

// Display full name func
// add_action ('admin_head','make_display_name_f_name_last_name');
// function make_display_name_f_name_last_name(){

// $users = get_users(array('fields'=>'all'));

// 	foreach($users as $user){
// 		$user = get_userdata($user->ID);    

// 		$display_name = $user->first_name . " " . $user->last_name;

// 		if($display_name!=' ') wp_update_user( array ('ID' => $user->ID, 'display_name' => $display_name) );
// 			else wp_update_user( array ('ID' => $user->ID, 'display_name' => $user->display_login) );

// 		if($user->display_name == '')
// 			wp_update_user( array ('ID' => $user->ID, 'display_name' => $user->display_login) );
// 	}
// }

// Current Page with post id shortcode
add_shortcode( 'page_article_response_url', 'article_response_url' );
// The Permalink Shortcode
function article_response_url() {
	ob_start();
	global $wp;
	$id = get_the_ID();
	$current_url = home_url(add_query_arg(array(),$wp->request));
	echo $current_url.'/add-replies/?post_id='.$id;
	return ob_get_clean();
}

add_shortcode( 'article_archive_response_url', 'article_archive_response_func' );
// The Permalink Shortcode
function article_archive_response_func() {
	ob_start();
	$queried_object = get_queried_object();
	$id = get_the_ID();
	if( get_post_type($queried_object->ID) == 'post' ){
		echo home_url('/replies-list/?post_id='.$queried_object->ID);
	}else{
		$post_id = get_post_meta($id, '_assign_main_post_id', true);
		echo home_url('/replies-list/?post_id='.$post_id);
	}
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

/**
 *  Add Response Hooks
 **/

function render_article_custom_hook( $form_id, $post_id, $form_settings ) {
	$value = '';
	$value = (isset($_REQUEST['post_id']) && !empty($_REQUEST['post_id'])) ? $_REQUEST['post_id'] : false; 
?>
<input type="hidden" name="_assign_post_id" value="<?php echo esc_attr( $value ); ?>">
<?php
}
add_action( 'add_response_article', 'render_article_custom_hook', 10, 3 );

// Response Post Query
function assign_reply_ids($assign_reply_ids){
	$reply_ids = [];
	if( !empty($assign_reply_ids) && is_array($assign_reply_ids))
	{	
		for( $i = 0; $i <= count($assign_reply_ids); $i++)
		{
			if(!empty($assign_reply_ids[$i])){
				if(get_post_type($assign_reply_ids[$i]) == 'post'){
					$response_article_list = get_post_meta($assign_reply_ids[$i], '_assign_reply_ids', true);	
					if(isset($response_article_list) && !empty($response_article_list))
					{
						foreach($response_article_list as $k => $val)
						{
							array_push($assign_reply_ids, $val);
						}				
					}
				}else{
					$response_article_list = $assign_reply_ids[$i];
				}			
				$reply_ids[] = $assign_reply_ids[$i];	
			}
		}
	}	
	return !empty($reply_ids) && is_array($reply_ids) ? $reply_ids : false;
}
add_action( 'powerpack/query/response_related_post', function( $query ) {
	$get_post_object = get_queried_object();
	$response_article_list = get_post_meta($get_post_object->ID, '_assign_main_post_id', true);
	$response_article_list = !empty($response_article_list)? $response_article_list : get_post_meta($get_post_object->ID, '_assign_reply_ids', true);
	$response_article_list = !empty($response_article_list) ? $response_article_list : $get_post_object->ID;

	$response_article_list = is_array($response_article_list) ? $response_article_list : array($response_article_list);
	$rep = assign_reply_ids($response_article_list);
	$rep = array_diff($rep,array($get_post_object->ID));

	$query->set( 'post_type', array('replies','post') );
	if(!empty($rep)){
		$query->set( 'post__in', $rep );
	}else{
		$query->set( 'post__in', array(0) );
	}
});
// Response Post Query
add_action( 'save_post_replies', 'save_post_replies_fn', 10, 3);
function save_post_replies_fn($reply_id, $args2, $args3) {
	$article_assing_post_ID = isset($_POST['_assign_post_id']) && !empty($_POST['_assign_post_id']) ? $_POST['_assign_post_id'] : false;
	//Main post id
	$main_post_id =  ( 'post' == get_post_type( $article_assing_post_ID )) ? $article_assing_post_ID : false;
	if( 'post' == get_post_type( $article_assing_post_ID ) && $article_assing_post_ID ){
		//Get article ids assign to main post 
		$get_article_ids = get_post_meta( $main_post_id, '_assign_reply_ids', true );
		if(!empty($get_article_ids)){
			$get_assign_article_ids = ( is_array($get_article_ids) && !empty($get_article_ids) ) ? $get_article_ids : array($get_article_ids);
			update_post_meta( $article_assing_post_ID, '_assign_reply_ids', array_unique( array_merge( $get_assign_article_ids, array($reply_id) ) ) );
		}else{
			update_post_meta( $article_assing_post_ID, '_assign_reply_ids', array($reply_id) );
		}
		// update post id in reply meta
		update_post_meta( $reply_id, '_assign_main_post_id', $article_assing_post_ID );
	}elseif( 'replies' == get_post_type( $article_assing_post_ID ) && $article_assing_post_ID ){
		//Get article ids assign to article post reply
		$get_reply_ids = get_post_meta( $article_assing_post_ID, '_assign_reply_ids', true );
		if(!empty($get_reply_ids)){
			$get_assign_article_ids = ( is_array($get_reply_ids) && !empty($get_reply_ids) ) ? $get_reply_ids : array($get_reply_ids);
			update_post_meta( $article_assing_post_ID, '_assign_reply_ids', array_unique( array_merge( $get_assign_article_ids, array($reply_id) ) ) );
		}else{
			update_post_meta( $article_assing_post_ID, '_assign_reply_ids', array($reply_id) );
		}
		// update post id in reply meta
		$get_assign_main_post_id = get_post_meta( $article_assing_post_ID, '_assign_main_post_id', true );
		update_post_meta( $reply_id, '_assign_main_post_id', $get_assign_main_post_id );
	}
}

// Response List Post Query
add_action( 'powerpack/query/response_related_list', function( $query ) {
	$response_article_list = get_post_meta($_REQUEST['post_id'], '_assign_reply_ids', true);
	$query->set( 'post_type', array('replies','post') );
	if(!empty($response_article_list)){
		$query->set( 'post__in', $response_article_list );
	}else{
		$query->set( 'post__in', array(0) );
	}
});
// Response List Post Query
/**
 *  Add Response Hooks
 **/

// Check post have replies shortcode
add_shortcode( 'check_post_have_replies', 'check_post_have_replies_func' );
// The Permalink Shortcode
function check_post_have_replies_func() {
	$id = get_the_ID();
	$response_article_list = !empty(get_post_meta($id, '_assign_reply_ids', true)) ? get_post_meta($id, '_assign_reply_ids', true) : get_post_meta($id, '_assign_main_post_id', true);
	return $response_article_list ? 'true' : 'false' ;
}
// Check post have replies shortcode

// Removed Post Id from meta
add_action( 'trashed_post', 'remove_post_id_in_replies' );
function remove_post_id_in_replies($post_id)
{
	if ( 'replies' != get_post_type( $post_id ) )
		return;

	$get_post_id = get_post_meta($post_id, '_assign_reply_ids', true);
	$get_post_ids = get_post_meta($get_post_id, '_assign_reply_ids', true);
	$post_ides=array_diff($get_post_ids,[$get_post_id]);
	update_post_meta($get_post_id, '_assign_reply_ids', $post_ides );
}


add_shortcode( 'replies_count', 'replies_count_func' );
function replies_count_func() {
	ob_start();
	$id = get_the_ID();
	$response_article_list = !empty(get_post_meta($id, '_assign_reply_ids', true)) ? get_post_meta($id, '_assign_reply_ids', true) : get_post_meta($id, '_assign_main_post_id', true);
	$publish_replies_ids = [];
	// 	echo '<pre>';
	// 	print_r($response_article_list);
	// 	echo '</pre>';
	if(!empty($response_article_list) && is_array($response_article_list)){
		foreach($response_article_list as $ids){
			if( get_post_status($ids) == 'publish' && !empty($ids) ){
				$publish_replies_ids[] = $ids;
			}
		}
	}
	$count_posts = count($publish_replies_ids) >0 ? count($publish_replies_ids) : 0 ;
	// 	$count_posts = count(assign_reply_ids($response_article_list));
	$count_posts > 1 ? printf(__('More articles... (%s) ','savior-pro'), $count_posts) : printf(__('More article... (%s) ','savior-pro'), $count_posts);
	return ob_get_clean();
}
add_shortcode( 'first_reply_count', 'first_reply_count_func' );
function first_reply_count_func() {
	ob_start();
	$id = get_the_ID();
	$response_article_list = get_post_meta($id, '_assign_reply_ids', true);
	$publish_replies_ids = [];
	if(!empty($response_article_list)){
		foreach($response_article_list as $ids){
			if( get_post_status($ids) == 'publish' && !empty($ids) ){
				$publish_replies_ids[] = $ids;
			}
		}

		$count_posts = count($publish_replies_ids);
		$count_posts === 0 ? print(__('Be first to reply with Article', 'savior-pro')) : print(__('Reply with Article','savior-pro'));
	}else{
		print(__('Be first to reply with Article', 'savior-pro'));
	}
	return ob_get_clean();
}
/*@ After login redirection */
function login_redirect_fn() {
	if( !isset( $_REQUEST['redirect_to'] ) && !is_user_logged_in() ){
		return site_url('login/?redirect_to='.get_permalink());
	}else {
		return home_url();		
	}
}

add_shortcode( 'login_redirect', 'login_redirect_fn', 10, 3 );
/*@ After login redirection login page */
function login_redirect_page_fn() {
	if( isset( $_REQUEST['redirect_to'] )){
		return $_REQUEST['redirect_to'];
	}else {
		return home_url('/feeds/');		
	}
}

add_shortcode( 'login_redirect_page', 'login_redirect_page_fn', 10, 3 );

function add_subscriber_delete_cap() {
	$role = get_role( 'subscriber' );
	$role->add_cap( 'edit_replies' ); 
	$role->add_cap( 'edit_other_replies' ); 
	$role->add_cap( 'publish_replies' ); 
	$role->add_cap( 'delete_replies' );
}
add_action( 'init', 'add_subscriber_delete_cap');

/**
 **Post Delet from front end side
 **/
function delete_post_from_front_side_func() {
	ob_start();
	$current_post_id = get_the_ID();
	if(is_user_logged_in() && current_user_can('delete_post', $current_post_id)) { 
		printf( 
			'<a href="%s&parent_post_ID=%s" >' . __( 'Delete Post' ) . '</a>',  
			esc_url(get_delete_post_link( $current_post_id )),
			get_post_meta($current_post_id, '_assign_main_post_id', true)
		); 
	}

	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}
add_shortcode( 'delete_post_from_front_side', 'delete_post_from_front_side_func' );
/*
 * Trash Reply
 */
add_action('wp_ajax_moverepliestotrash', function(){
	check_ajax_referer( 'trash-post_' . $_POST['post_id'] );
	wp_trash_post( $_POST['post_id'] );	
	wp_send_json_success(get_the_permalink($_POST['parent_post_ID']));
});
/*
 * Update Username
 */
add_action('wp_ajax_update_username', function(){
	check_ajax_referer('change_user_name', $_POST['_wpnonce'] );
	if(is_user_logged_in()){
		$user_id = get_current_user_id();
		$new_user_login = trim( $_POST['user_login'] );
		$user_login = get_user_by( 'user_login', $user_ID );
		if($user_login === $new_user_login){
			wp_send_json_error();
		}
		global $wpdb;
		$user_data = $wpdb->update(
			$wpdb->users, 
			['user_login' => $new_user_login], 
			['ID' => $user_id]
		);
		if ( is_wp_error( $user_data ) ) {
			wp_send_json_error($user_data->get_error_message());
		} else {
			wp_send_json_success(home_url('login'));
		}
	}else{
		wp_send_json_error();
	}

});

/**
 ** Replies post image function
 **/

function replies_image_post_func() {
	ob_start();
	$current_post_id = get_the_ID();
	$image = wp_get_attachment_image_src( get_post_thumbnail_id( get_post_meta($current_post_id, '_assign_main_post_id', true) ), 'single-post-thumbnail' );
	$parent_image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $current_post_id ) , 'single-post-thumbnail' );	

	if (has_post_thumbnail( $current_post_id) ){
?>
<div class="post-feature-img">
	<a href="<?php echo get_permalink($current_post_id); ?>">
		<img src="<?php echo $parent_image_url[0]; ?>" alt="post-img"/>
	</a>
</div>
<?php		
											   }else {
?>
<div class="post-feature-img">
	<a href="<?php echo get_permalink($current_post_id); ?>">
		<img src="<?php echo $image[0]; ?>" alt="post-img"/>
	</a>
</div>
<?php
													 }	

	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}
add_shortcode( 'replies_image_post', 'replies_image_post_func' );

/**
 ** Create Post Button
 **/
function display_create_post_btn_func(){
	ob_start();
	if(is_user_logged_in() && function_exists('pmpro_hasMembershipLevel') && pmpro_hasMembershipLevel()) {
		echo 'post-btn-hide';
	}else {
		echo 'post-btn-visible';

	}
	$output = ob_get_clean();
	return $output;	
}
add_shortcode('display_create_post_btn', 'display_create_post_btn_func');
/*
 * 
 */
function create_post_no_membership_nav_menu_items($items) {
	if(is_user_logged_in() && function_exists('pmpro_hasMembershipLevel') && ! pmpro_hasMembershipLevel()) {
		$homelink = '<li class="fas fa-plus mbl-menu menu-item menu-item-type-post_type menu-item-object-page"><a href="' . home_url( 'pricing' ) . '" class="pp-menu-item menu-link">' . __('Create New Post') . '</a></li>';
		$items = $items . $homelink;
	}
	return $items;
}
add_filter( 'wp_nav_menu_items', 'create_post_no_membership_nav_menu_items' );

// Report as spam

add_action( 'gform_after_submission', 'set_post_content', 10, 2 );
function set_post_content( $entry, $form ) {

	$post_id = rgar( $entry, '4' );

	wp_update_post(array('ID' => $post_id, 'post_status'   =>  'draft'));

}

/**
 ** Author post count 
 **/

function display_author_post_count_func(){
	ob_start();	
	$author_id = get_the_author_meta( 'ID' ); 
	$posts_count_var =  count_user_posts( $author_id , ['post','replies']  ); 
	echo $posts_count_var .' Articles';
	$output = ob_get_clean();
	return $output;	
}
add_shortcode('display_author_post_count', 'display_author_post_count_func');


function display_author_comment_count_func(){
	ob_start();	
	$author_id = get_the_author_meta( 'ID' ); 

	$args = array(
		'post_author' => $author_id, // fill in post author ID
	);
	$author_comments = get_comments($args);

	echo count($author_comments) .' Comments';

	$output = ob_get_clean();
	return $output;	
}
add_shortcode('display_author_comment_count', 'display_author_comment_count_func');

/**
 ** Author register day 
 **/

function display_author_membership_date_func(){
	ob_start();	

	$author_id = get_the_author_meta( 'ID' ); 

	// 	$user_meta_var = get_user_meta($author_id);

	// 	$order = new MemberOrder();
	// 	$order_date = $order->getTimeStamp( true );

	// 	$invoice = new MemberOrder($author_id);

	// 	echo date(get_option("date_format"), $invoice->getTimestamp());

	// 	echo '<pre>';
	// 	print_r($order->getTimeStamp( true ));
	// 	echo '</pre>';

	//Get the registration date
	$registered_date = get_the_author_meta( 'user_registered', $author_id );

	//Convert to desired format
	$output = date( 'M j, Y', strtotime($registered_date));

	//Echo
	echo 'Member since '. $output;

	$output = ob_get_clean();
	return $output;	
}
add_shortcode('display_author_membership_date', 'display_author_membership_date_func');

/**
 ** Author update day 
 **/

function display_author_update_date_func(){
	ob_start();	

	echo 'Last updated on '. get_the_modified_date( 'F j, Y' );

	$output = ob_get_clean();
	return $output;	
}
add_shortcode('display_author_update_date', 'display_author_update_date_func');

/**
 ** Author Post PP Query
 **/

add_action( 'powerpack/query/author_post', function( $query ) {
	$get_post_object = get_queried_object();
	$author_profile_id = $get_post_object->ID;
	$query->set( 'post_type', array('replies','post') );
	$query->set( 'author', $author_profile_id ); 
});

add_action( 'powerpack/query/author_pop_posts', function( $query ) {
	$get_post_object = get_queried_object();
	$author_profile_id = $get_post_object->ID;
	$query->set( 'orderby', 'meta_value_num' );
	$query->set( 'meta_key', 'views' );
	$query->set( 'post_type', array('replies','post') );
	$query->set( 'author', $author_profile_id ); 
});

add_action( 'powerpack/query/author_search_post', function( $query ) {
	$response_article_list = !empty($_REQUEST['ee_author_id']) ? $_REQUEST['ee_author_id'] : false;
	$query->set( 'post_type', array('replies','post') );
	if($response_article_list){
		$query->set( 'author', $response_article_list ); 
	}
});

function change_heading_widget_content( $widget_content, $widget ) {
	if( !\Elementor\Plugin::$instance->editor->is_edit_mode() ) {
		if ( 'ee-search-form' === $widget->get_name() ) {
			$get_post_object = get_queried_object();
			$author_profile_id = $get_post_object->ID ? $get_post_object->ID : $_REQUEST['ee_author_id'];
			$widget_content =  str_replace( '</form>', "<input type='hidden' class='ee-form__field__control--sent' name='ee_author_id' value='".$author_profile_id."'></form>", $widget_content );
			// 			error_log(print_r($widget_content, true));
		}
	}
	return $widget_content;
}
add_filter( 'elementor/widget/render_content', 'change_heading_widget_content', 10, 2 );
/*
 * Seach query filter by author
 */
add_action( 'powerpack/query/post_search_author', function( $query ) {
	$post_search_author = !empty($_REQUEST['s']) ? $_REQUEST['s'] : false;
	$user = get_user_by('login', $post_search_author);
	$query->set( 'post_type', array('replies','post') );
	if($user){
		$query->set( 'author', $user->ID ); 
	}else{
		$query->set( 's', $post_search_author ); 
	}
});



// // Current Page with post id shortcode
// add_shortcode( 'test_shortcode', 'test_shortcode_func' );
// The Permalink Shortcode
function test_shortcode_func() {
	echo"<pre>";
	echo get_post_meta(2140, '_assign_main_post_id', true);
	echo"</pre>";
}

add_action( 'template_redirect', 'redirect_testnitro' );
function redirect_testnitro() {
	if (is_front_page() && $_GET['testnitro'] == 1 ) {
		//wp_redirect(home_url(), 301 );
		//exit();
	}
}