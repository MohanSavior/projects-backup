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
	wp_enqueue_style( 'savior-pro-responsive', get_stylesheet_directory_uri() . '/assets/css/responsive-css-2.css', array(), time(), 'all' );

	wp_enqueue_style( 'savior-pro-responsive-styles', get_stylesheet_directory_uri() . '/assets/css/savior-pro-responsive-styles.css', array(), time(), 'all' );
	wp_enqueue_script( 'savior-pro-scripts', get_stylesheet_directory_uri() . '/assets/js/savior-pro-scripts.js', array('jquery'), time(), true );
	wp_localize_script('savior-pro-scripts','asgmt_ajax_object', 
					   [
							'ajax_url'  => admin_url( 'admin-ajax.php' ), 
							'security'  => wp_create_nonce( 'asgmt-security-nonce' ),
							'profile'   => site_url( 'my-asgmt-dashboard/my-profile/' ),
							'stock_quantities'	=> is_page(20551) || is_page(24902) ? get_all_product_stock_quantities() : '',
					   ]
					  );
	/** Scrollbar JS **/
	wp_enqueue_script( 'jquery-mCustomScrollbar-js', get_stylesheet_directory_uri() . '/scroll-bar/jquery.mCustomScrollbar.js', array('jquery'), '1.0.0', true );
	wp_enqueue_style( 'jquery-mCustomScrollbar-css', get_stylesheet_directory_uri() . '/scroll-bar/jquery.mCustomScrollbar.css', array(), '1.0.0', 'all' );
	/** Data Tables **/
	if(is_page(18338)){
		wp_enqueue_style( 'jquery-datatables-css', 'https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css', array(), '1.0.0', 'all' );
		wp_enqueue_style( 'jquery-datatables-buttons-css', get_stylesheet_directory_uri() . '/assets/css/datatable/buttons.dataTables.min.css', array(), time(), 'all' );
		wp_enqueue_script( 'jquery-datatables-min-js', get_stylesheet_directory_uri() . '/assets/js/datatable/jquery.dataTables.min.js', array('jquery'), time(), true );
		wp_enqueue_script( 'jquery-datatables-buttons-js', get_stylesheet_directory_uri() . '/assets/js/datatable/dataTables.buttons.min.js', array('jquery'), time(), true );
		wp_enqueue_script( 'jquery-datatables-pdfmake-js', get_stylesheet_directory_uri() . '/assets/js/datatable/pdfmake.min.js', array('jquery'), time(), true );
		wp_enqueue_script( 'jquery-datatables-vfs_fonts-js', get_stylesheet_directory_uri() . '/assets/js/datatable/vfs_fonts.js', array('jquery'), time(), true );
		wp_enqueue_script( 'jquery-datatables-buttons-html5-js', get_stylesheet_directory_uri() . '/assets/js/datatable/buttons.html5.min.js', array('jquery'), time(), true );
	}
	if(is_page(18660) || is_page(18338)  ){
		wp_enqueue_script( 'jquery-jspdf-js', get_stylesheet_directory_uri() . '/assets/js/html2pdf/jspdf.min.js', array(), time(), true );
		wp_enqueue_script( 'jquery-htmltopdf', get_stylesheet_directory_uri() . '/assets/js/html2pdf/html2pdf.bundle.min.js', array('jquery'), time(), true );
		wp_enqueue_script( 'invoice-print', get_stylesheet_directory_uri() . '/assets/js/html2pdf/invoice-print.js', array('jquery','jquery-jspdf-js','jquery-htmltopdf'), time(), true );
	}
}
add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

remove_action( 'woocommerce_order_details_after_order_table', 'woocommerce_order_again_button' );

/* Previous and Next Post Filters ID's */
add_action( 'powerpack/query/previous_post_filter', function( $query ) {
	$previous_post = get_previous_post();
	if(!empty($previous_post)){
		$query->set( 'post__in', array( $previous_post->ID ));
	}else{
		$query->set( 'post__in', array( 0 ));
	}    
});

add_action( 'powerpack/query/next_post_filter', function( $query ) {
	$next_post = get_next_post();
	if(!empty($next_post)){
		$query->set( 'post__in', array($next_post->ID));
	}else{
		$query->set( 'post__in', array(0));
	}    
});

add_shortcode('paper_by_year', function(){
	ob_start();
	$action_url = site_url('papers');
	if((isset($_GET['ee_search_query']) && !empty($_GET['ee_search_query'])) && (isset($_GET['ee_search_id']) && (!empty($_GET['ee_search_id']))) &&  (isset($_GET['s']) && (!empty($_GET['s']))) ){
		$action_url = site_url('/');
	}

?>
<div id="asgmt-year-list" class="dropdown-check-list" tabindex="100">
	<form action="<?php echo ($action_url);?>" method="get">
		<h2 class="drop-heading">Papers by year</h2>
		<?php 
		if(isset($_GET['ee_search_query']) && !empty($_GET['ee_search_query'])): 
		$ee_search_query_string = str_replace('\\', '/', $_GET['ee_search_query']);
		$ee_search_query_string = str_replace('\\', '/', '{"post_type":["product","paper","vendor_exhibit","wswebinars"],"s":"ASGMT"}');
		?>
		<input type="hidden" name="ee_search_query" value='<?php echo ($ee_search_query_string); ?>'>
		<?php
		endif;
		if(isset($_GET['ee_search_id']) && !empty($_GET['ee_search_id'])): ?>
		<input type="hidden" name="ee_search_id" value="<?php echo esc_attr($_GET['ee_search_id']); ?>">
		<?php 
			endif;
			if(isset($_GET['s']) && !empty($_GET['s'])): ?>
		<input type="hidden" name="s" value="<?php echo esc_attr($_GET['s']); ?>">
		<?php endif;?>			
		<ul class="years">	
			<?php
				$postlink = isset($_REQUEST['paper_by_year']) ? $_REQUEST['paper_by_year'] : [];
				foreach( array_keys(posts_by_year()) as $year )
				{
					$ch = in_array( $year, $postlink ) ? 'checked="checked"' : '';
					if(!is_admin())
						printf('<li><label><input type="checkbox" name="paper_by_year[]" value="%s" %s/>%s</label></li>', $year, $ch, $year);
				}
			?>
		</ul>
		<div id="paper_by_year_filter" style="display:none;"><button type="submit" class="btn btn-sm" onclick="this.form.submit(); this.disabled = true;">Filter</button></div>
	</form>
</div>
<?php
	$output_string = ob_get_contents();
	ob_end_clean();
	return $output_string;
});
function posts_by_year() {
	// array to use for results
	$years = array();
	// get posts from WP
	$posts = get_posts(array(
		'numberposts' 	=> -1,
		'orderby' 		=> 'post_date',
		'order' 		=> 'ASC',
		'post_type' 	=> 'paper',
		'post_status' 	=> 'publish'
	));
	// loop through posts, populating $years arrays
	foreach($posts as $post) {
		$years[date('Y', strtotime($post->post_date))][date('F', strtotime($post->post_date))] = $post;
	}

	// reverse sort by year
	krsort($years);

	return $years;
}

//[elementor-template id="698"]
add_action( 'powerpack/query/papers_by_years', function($query){
	$query->set( 'post_type', [ 'paper' ] );
	if(!is_user_logged_in())
	{
		$query->set( 'date_query', array(
			array(
				'column' => 'post_date',
				'before' => '15 days ago'
			),
			// array(
			// 	'column' => 'post_modified',
			// 	'before' => '15 days ago'
			// ),
		) ); 
	}
	if(isset($_GET['paper_by_year']) && !empty($_GET['paper_by_year'])){
		$a = [];
		$a['relation'] = 'OR';
		foreach ($_REQUEST['paper_by_year'] as $key => $value) {
			$a[]['year'] = $value;
		}
		$query->set( 'date_query' , $a);
	}
	if(isset($_GET['s']) && !empty($_GET['s'])){
		$query->set( 's' , $_GET['s']);
	}
});

/** Custom Logout Shortcode **/
add_shortcode('custom_logout', 'auto_redirect_external_after_logout');
function auto_redirect_external_after_logout(){
	// 	$log_out_url = add_query_arg('_wpnonce', wp_create_nonce('log-out'), wp_logout_url());
	return '<a href="'.esc_url( wp_logout_url(site_url()) ).'" class="pp-menu-item menu-link">Logout</a>';
}

add_shortcode('custom_logout_2', 'custom_logout_fn');
function custom_logout_fn(){
	return esc_url( wp_logout_url(site_url()) );
}


/** Shortcode For Order Table **/
add_shortcode('orders_table_of_current_user','shortcode_orders_table');
function shortcode_orders_table(){
	$out = '';
	ob_start();
	$out = wc_get_template( 'myaccount/my-orders.php');
	$out = ob_get_contents();
	ob_end_clean();
	return $out;
}

/** Shortcode to get Post Content **/
add_shortcode('papers-content','papers_content_fn');
function papers_content_fn(){
	if ( get_post_type() == 'paper' ) {
		ob_start();
		echo wp_trim_words(get_the_content());
		$output_string = ob_get_contents();
		ob_end_clean();
		return $output_string;
	}
}

/** Conditionally Redirected on Dashboard Page **/
add_shortcode('dashboard_url', 'dashboard_url_fn');
function dashboard_url_fn() {
	if(!is_admin()) {
		$current_user = wp_get_current_user();
		if ( 0 == $current_user->ID ) {
			return site_url("sign-in");
		} else {
			$role_exist = array(
				'student', 'virtualgmfstudent', 'virtuallmfstudent', 'instructormember','speaker', 'exhibitsmember', 'bodmember', 'lmf_in_person', 'gmf_in_person', 'gmf_virtual', 'lmf_virtual'
			);
			$user_roles = $current_user->roles;
			$result=array_intersect($role_exist, $user_roles);
			if(array_intersect($role_exist, $user_roles)){
				return site_url("/members-dashboard/");
// 				return site_url("/members-dashboard/");
// 			}elseif(in_array('virtualgmfstudent', $user_roles)){
// 				return site_url("/members-dashboard/");
// 			}elseif(in_array('virtuallmfstudent',$user_roles)){
// 				return site_url("/members-dashboard/");
// 			}elseif(in_array('instructormember',$user_roles)){
// 				return site_url("/members-dashboard/");
// 			}elseif(in_array('exhibitsmember',$user_roles)){
// 				return site_url("/members-dashboard/");
// 			}elseif(in_array('bodmember',$user_roles)){
// 				return site_url("/members-dashboard/");
			}
			else
			{
				return site_url("/my-asgmt-dashboard/");
			}
		}  
	}
}
add_action( 'wp', 'restrict_pages_by_role' );
function restrict_pages_by_role() {
	if ( get_post_type() == 'page' ) {
		global $post;
		$page_user_roles_set = get_post_meta($post->ID, 'user_roles', true);
		if(is_user_logged_in()){
			$user = wp_get_current_user();
			$user_roles = $user->roles;

			if ( !empty($page_user_roles_set) && count(array_intersect($user_roles, $page_user_roles_set)) == 0 ) {
				if( in_array('student', $user_roles)){
					wp_redirect( home_url("/members-dashboard/"));
					exit;
				}elseif( in_array('virtualgmfstudent', $user_roles)){
					wp_redirect( home_url("/members-dashboard/"));
					exit;
				}elseif( in_array('virtuallmfstudent',$user_roles)){
					wp_redirect( home_url("/members-dashboard/"));
					exit;
				}elseif( in_array('instructormember',$user_roles)){
					wp_redirect( home_url("/members-dashboard/"));
					exit;
				}elseif( in_array('exhibitsmember',$user_roles)){
					wp_redirect( home_url("/members-dashboard/"));
					exit;
				}elseif( in_array('bodmember',$user_roles)){
					wp_redirect( home_url("/members-dashboard/"));
					exit;
				}elseif( in_array('lmf_in_person',$user_roles)){
					wp_redirect( home_url("/members-dashboard/"));
					exit;
				}elseif( in_array('gmf_in_person',$user_roles)){
					wp_redirect( home_url("/members-dashboard/"));
					exit;
				}elseif( in_array('gmf_virtual',$user_roles)){
					wp_redirect( home_url("/members-dashboard/"));
					exit;
				}elseif( in_array('lmf_virtual',$user_roles)){
					wp_redirect( home_url("/members-dashboard/"));
					exit;
				}
				else{
					wp_redirect(home_url('/my-asgmt-dashboard/'));
					exit;
				}
				// wp_redirect(home_url());
				// exit;
			}
		}elseif(!is_user_logged_in() && !empty($page_user_roles_set)){
			wp_redirect(home_url());
			exit;
		}
	}
}
add_action('template_redirect','my_non_logged_redirect');
function my_non_logged_redirect() {
	/* get current page or post ID */
	$page_id = get_queried_object_id();	
	if(is_user_logged_in()) {  // (!current_user_can('administrator') &&)
		$current_user = wp_get_current_user();
		$user_roles = $current_user->roles;

		if( $page_id == 1582 || is_page('sign-in')){
			$login_redirect = array(
				'student' 			=> home_url("/members-dashboard/"),//in-person-student-dashboard/
				'virtualgmfstudent' => home_url("/members-dashboard/"),//gmf-courses-dashboard
				'virtuallmfstudent' => home_url("/members-dashboard/"),//lmf-courses-dashboard
				'instructormember' 	=> home_url("/members-dashboard/"),//instructor-dashboard
				'exhibitsmember' 	=> home_url("/members-dashboard/"),//exhibitors-dashboard
				'bodmember' 		=> home_url("/members-dashboard/"),//bod-members-dashboard
				'lmf_in_person' 	=> home_url("/members-dashboard/"),
				'gmf_in_person' 	=> home_url("/members-dashboard/"),
				'gmf_virtual' 		=> home_url("/members-dashboard/"),
				'lmf_virtual' 		=> home_url("/members-dashboard/")
			);

			$intersect = array_intersect(array_keys($login_redirect), $user_roles);
			if (empty($intersect)) {
				wp_redirect(home_url('/my-asgmt-dashboard/'));
				exit;
			} 
			if(count($user_roles) > 1)
			{
				wp_redirect($login_redirect[array_values($user_roles)[1]]);
				exit;
			}
			elseif (in_array('customer', $user_roles) || in_array('subscriber', $user_roles)) {
				wp_redirect(home_url('/my-asgmt-dashboard/'));
				exit;
			}else{
				wp_redirect(home_url('/my-asgmt-dashboard/'));
				exit;
			}
		}	
	}
}
/**
 * 
 */
// function hide_admin_bar_for_non_admins() {
// 	if (!current_user_can('administrator') && !is_admin()) {
// 		add_filter('show_admin_bar', '__return_false');
// 	}
// }

//add_action('after_setup_theme', 'hide_admin_bar_for_non_admins');



// PDF Generator

add_action( 'wp_ajax_nopriv_create_invoice', 'savior_create_invoice_ajax' );
add_action( 'wp_ajax_create_invoice', 'savior_create_invoice_ajax' );

function savior_create_invoice_ajax() {
	if(isset($_POST['orderId'])){
		$order_id = $_POST['orderId'];
		$order = wc_get_order( $order_id );

		$shop_address = "<p>Houston <br> Texas, United States, <br>77001</p>";

		if ( $order ) {
			$order_number 		= $order->get_order_number();
			$order_date 		= $order->get_date_created();
			$order_date 		= $order_date->date_i18n( get_option( 'date_format' ) );
			$order_total 		= $order->get_total();
			$subtotal 			= $order->get_subtotal();
			$currency_symbol 	= get_woocommerce_currency_symbol($order->get_currency());
			$items 				= $order->get_items();
			$data 				= create_order_items_table($items);
			$shipping_cost 		= ($order->get_shipping_total() > 0) ? $order->get_shipping_total() : 'Free Shipping';
			$billing_address 	= $order->get_formatted_billing_address();
			$user_id 			= $order->get_user_id();
			$user 				= get_user_by( 'ID', $user_id );
			$email 				= $user->user_email;
			$billing_email 		= $order->get_billing_email();
			$billing_phone 		= $order->get_billing_phone();
			$phone 				= get_user_meta( $user_id, 'billing_phone', true );
			$_gravity_form_entry_id = $order->get_meta('_gravity_form_entry_id');
			// we won't display anything if it is not a entry id
			$attendees = '';
			if (!empty($_gravity_form_entry_id)) {
				$entry = GFAPI::get_entry($_gravity_form_entry_id);
				
				if($entry['1034'] == 'yes'){
					$attendees .="<h6 class='attendees-info-heading' style='border-top: 1px solid #252F86;'>Attendees Information</h6><div class='attendee-info'>";
					// print_r($entry['1000']);
					$attendees .="<table class='attendee-info'><thead><tr><th>Attendee name</th><th>Attendee email</th><th>Attendee product</th></tr></thead><tbody>";
					foreach ($entry['1000'] as $attendee) {
						$attendees .= sprintf("<tr><td>%s</td><td>%s</td><td>%s</td></tr>", $attendee['1002'].' '.$attendee['1003'], $attendee['1001'], get_the_title( $attendee['1005'] ));
					}
					$attendees .="</tbody></table></div>";
				}
			}
			$html = <<<EOD
			<div style="font-family: Helvetica;">
				<div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 35px;">
					<div>
					<img style="width: 160px;" src="https://asgmt.com/wp-content/uploads/2019/01/asgmtlogo.png">
					<div class="shop_info" style="margin-top: 20px;">
						$shop_address
						<h6 style="color: rgb(37, 47, 134);">Order Date: <span style="font-size: 85%; font-weight: ligher; color: #444;">$order_date</span> </h6>
					</div>
					</div>
					<div>
						<h3 style="margin-top: 0; margin-bottom: 20px;"><span style="font-family: Helvetica; color: rgb(37, 47, 134);">Invoice #$order_number</span></h3>
						<h5 style="color: rgb(37, 47, 134);">Billing address</h5>
						<p>
						$billing_address <br>
						$billing_phone <br>
						$billing_email 
						</p>	
					</div>
				</div>
				<table border="1" style="width: 100%;">
					<thead style="backgorund-color: #fbfbfb">
						<tr>
							<th style="width: 75%;">
								<div style="text-align: left; color: rgb(37, 47, 134);"><strong>Products</strong></div>
							</th>
							<th style="width: 25%;">
								<div style="text-align: left; color: rgb(37, 47, 134);"><strong>Total</strong></div>
							</th>
						</tr>
					</thead>
					<tbody>
						$data
						<tr>
							<td style="width: 75%;">Sub Total</td>
							<td style="width: 25%;">$currency_symbol$subtotal</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td style="width: 75%;"><strong>Total </strong></td>
							<td style="width: 25%;"><strong>$currency_symbol$order_total</strong></td>
						</tr>
						<tr>
							<td style="width: 75%;">Paid </td>
							<td style="width: 25%;">$currency_symbol$order_total</td>
						</tr>
						<tr>
							<td style="width: 75%;"><strong>Amount Due </strong></td>
							<td style="width: 25%;"><strong>$0.00</strong></td>
						</tr>
					</tfoot>
				</table>
					$attendees
				<p><br></p>
			</div>
			EOD;			
			wp_send_json_success(['success' => true, 'data' => $html]);
		}
	}else{
		wp_send_json_success(['success' => false, 'message' => 'Please enter order id']);
	}
}

function create_order_items_table($items = []){
	ob_start();
	foreach ($items as $item) {
		$product = $item->get_product();
		$name = $product ? $product->get_name() : $item->get_name();
		$quantity = $item->get_quantity();
		$price = $item->get_total() / $quantity;
		echo '<tr>';
		echo '<td style="width: 75%;">' . $name .  ' x '.$quantity.'</td>';
		echo '<td style="width: 25%;">' . wc_price($price) . '</td>';
		echo '</tr>';
	}
	return ob_get_clean();
}

// Product render on registration page
add_filter( 'gform_pre_render_11', 'product_render_on_registration_page' );
add_filter( 'gform_pre_validation_11', 'product_render_on_registration_page' );
add_filter( 'gform_pre_submission_filter_11', 'product_render_on_registration_page' );
add_filter( 'gform_admin_pre_render_11', 'product_render_on_registration_page' );
function product_render_on_registration_page( $form ) {
	foreach( $form['fields'] as &$field )  {
		$field_id = 1027;
		if ( $field->id == $field_id) {			

			$product_ids = array( 18783, 22100, 22101, 22102, 18784 );
			$args = array(
				'post_type' => 'product',
				'post__in' 	=> $product_ids,
				'orderby' 	=> 'post__in'
			);
			$products = get_posts( $args );
			$products_choices = array();
			foreach( $products as $product ) {
				$itag = '';
				if($product->ID == 22100)
					$itag = "<span class='ceu-infor'>(i)</span> <span class='ceu-tooltip'><b>What is a CEU credit?</b> The Continuing Education Unit or CEU Provides a standard unit of measurement for continuing education and training, Quantify continuing education and training (CE/T) activities, and Accommodate for the diversity of providers, activities, and purposes in adult education.</span>";

				$sale_price = get_post_meta( $product->ID, '_sale_price', true );
				$regular_price = get_post_meta( $product->ID, '_regular_price', true );

				$product_price ='';

				if ( $sale_price ) {
					$product_price = $sale_price;
				} else {
					$product_price = $regular_price;
				}
				$checkBoxText = $product->post_title." ".$itag. " $".$product_price;
				$products_choices[] = array( 'text' => $checkBoxText, 'value' => $product->ID );
			}
			$field->choices = $products_choices;
		}
	}
	return $form;
}
add_filter( 'gform_countries', function ( $countries ) {
	$new_countries = array();
	asort($countries);
	foreach ( $countries as $country ) {
		$code                   = GF_Fields::get( 'address' )->get_country_code( $country );
		$new_countries[ $code ] = $country;
	}
	return $new_countries;
} );
add_filter( 'gform_form_post_get_meta_11', 'repeater_registration_form' );
function repeater_registration_form( $form ) {
	$email = GF_Fields::create( array(
		'type'   => 'email',
		'id'     => 1001, 
		'formId' => $form['id'],
		'label'  => 'Email *',
		'pageNumber'  => 1, 
		// 'isRequired'  => true, // Make the field required
	) );
	$first_name = GF_Fields::create( array(
		'type'   => 'text',
		'id'     => 1002, 
		'formId' => $form['id'],
		'label'  => 'First name *',
		'pageNumber'  => 1, 
		// 'isRequired'  => true, // Make the field required
	) );

	$last_name = GF_Fields::create( array(
		'type'   => 'text',
		'id'     => 1003, 
		'formId' => $form['id'],
		'label'  => 'Last name *',
		'pageNumber'  => 1, 
		// 'isRequired'  => true, // Make the field required
	) );

	$product_ids = array( 18783, 22100, 22101, 22102, 18784 );
	$args = array(
		'post_type' => 'product',
		'post__in' 	=> $product_ids,
		'orderby' 	=> 'post__in'
	);
	$products = get_posts( $args );
	$dynamic_choices = [];
	foreach( $products as $key => $product ) {
		$itag = '';
		if($product->ID == 22100)
		{
			$itag = "<span class='ceu-infor'>(i)</span> <span class='ceu-tooltip'><b>What is a CEU credit?</b> The Continuing Education Unit or CEU Provides a standard unit of measurement for continuing education and training, Quantify continuing education and training (CE/T) activities, and Accommodate for the diversity of providers, activities, and purposes in adult education.</span>";
		}

		$sale_price 	= get_post_meta( $product->ID, '_sale_price', true );
		$regular_price 	= get_post_meta( $product->ID, '_regular_price', true );

		$product_price ='';	
		if ( $sale_price ) {
			$product_price = $sale_price;
		} else {
			$product_price = $regular_price;
		}
		if($key == 0){
			$dynamic_choices[] = array( 'text' => $product->post_title." ".$itag. " $".$product_price, 'value' => $product->ID, 'isSelected' => true );
		}else{
			$dynamic_choices[] = array( 'text' => $product->post_title." ".$itag. " $".$product_price, 'value' => $product->ID, 'isSelected' => false );
		}
	}
	$product_choices = GF_Fields::create( array(
		'type' => 'radio',
		'id' => 1005, 
		'formId' => $form['id'],
		'label' => 'Choose Registration *',
		'pageNumber' => 1, 
		'choices' => $dynamic_choices,
		'setDefaultValues' => '18783'
	) );
	// Set the default selected option
	// create the radio field
	$course_type = GF_Fields::create( array(
		'type' => 'radio',
		'id' => 1028, 
		'formId' => $form['id'],
		'label' => 'Select Course Type',
		'pageNumber' => 1, 
		'choices' => array(
			array(
				'text' => 'In-person',
				'value' => 'in_person',
				'isSelected' => true,
			),
			array(
				'text' => 'Virtual',
				'value' => 'virtual'
			),
		) 
	) );
	$special_role = GF_Fields::create( 
		array(
			'type' => 'checkbox',
			'id' => 1029, // replace with a unique ID for this field
			'formId' => $form['id'],
			'label' => 'SPECIAL ROLE? (CHECK ALL THAT APPLY)',
			'pageNumber' => 1,
			'defaultChoice' => 'instructormember',
			'choices' => array(
				array(
					'text' => 'Is the attendee a speaker at ASGMT?',
					'value' => 'instructormember'
				),
				array(
					'text' => 'Is the attendee an Exhibitor?',
					'value' => 'exhibitsmember'
				)
			),
			'inputs' => array(
				array(
					'label' => 'Is the attendee a speaker at ASGMT?',
					'id' => '1029.1',
				),
				array(
					'label' => 'Is the attendee an Exhibitor?',
					'id' => '1029.2'
				)
			)
		) 
	);
	$repeater = GF_Fields::create( array(
		'type'             => 'repeater',
		'description'      => '',
		'id'               => 1000, // The Field ID must be unique on the form
		'formId'           => $form['id'],
		'label'            => '',
		'addButtonText'    => '+ Add Attendees', // Optional
		'removeButtonText' => '- Remove Attendee', // Optional
		'pageNumber'       => 1, // Ensure this is correct
		'fields'           => array( $first_name, $last_name, $email, $product_choices, $course_type, $special_role ), // Add the fields here.
	) );
	// $form['fields'][] = $repeater;
	array_splice( $form['fields'], 13, 0, array( $repeater ) );
	return $form;
}
// Remove the field before the form is saved. Adjust your form ID
add_filter( 'gform_form_update_meta_11', 'remove_product_render_on_registration_page', 10, 3 );
function remove_product_render_on_registration_page( $form_meta, $form_id, $meta_name ) {
	if ( $meta_name == 'display_meta' ) {
		$form_meta['fields'] = wp_list_filter( $form_meta['fields'], array( 'id' => 1000 ), 'NOT' );
	}
	return $form_meta;
}
add_action( 'gform_after_submission_11', 'add_to_cart_and_redirect_checkout', 100, 2 );

function add_to_cart_and_redirect_checkout( $entry, $form ) {	
	$main_product_id = rgar( $entry, '1027' );
	if( $main_product_id == 22101 )
	{
		$main_product_id = rgar( $entry, '1006' ) == 'in_person' ? 22101 : 27011;
	}
	if( $main_product_id == 22102 )
	{
		$main_product_id = rgar( $entry, '1006' ) == 'in_person' ? 22102 : 27010;
	}
	$products_addto_cart = array(
		array( 'id' => $main_product_id, 'quantity' => 1 )
	);
	//Get attendies 
	$check_attendees = rgar( $entry, '1034' );
	if($check_attendees == 'yes')
	{
		$get_all_attendees = rgar( $entry, '1000' );
		foreach ($get_all_attendees as $attendees) {
			if( $attendees[1005] == 22101 )
			{
				$attendees_product_id = $attendees[1028] == 'in_person' ? 22101 : 27011;
			}elseif( $attendees[1005] == 22102 )
			{
				$attendees_product_id = $attendees[1028] == 'in_person' ? 22102 : 27010;
			}else{
				$attendees_product_id = $attendees[1005];
			}
			$products_addto_cart[] = array( 'id' => $attendees_product_id, 'quantity' => 1 );
		}
	}
	// Empty the cart
	WC()->cart->empty_cart();
	foreach ( $products_addto_cart as $product ) {
		WC()->cart->add_to_cart( $product['id'], $product['quantity'] );
	}	

	$entry_id = $entry['id'];
	$_SESSION['entry_id'] = $entry_id;
	$_SESSION['entry_email'] = $entry['10'];
	$checkout_url = wc_get_checkout_url();
	wp_redirect( $checkout_url );
	exit;
}
function register_my_session()
{
	if( session_start()  === PHP_SESSION_NONE)
	{
		session_start();
	}
}

add_action('init', 'register_my_session');
//Logger
include_once 'inc/logger.php';
// Functionality for school online registration
include_once 'inc/create-attendees.php';
//Exhibitor Management
include_once 'inc/exhibitor-management.php';
//Exhibit Assistant Registration
include_once 'inc/exhibit-assistant-registration.php';
//Registration Reports
include_once 'inc/registration-reports.php';
/** Shortcode to get user's billing address **/
add_shortcode('users_billing_address', 'users_billing_address_fn');
function users_billing_address_fn() {
	if(is_user_logged_in())
	{
		$customer = new WC_Customer( get_current_user_id() );
		$billing_address_1  = $customer->get_billing_address_1();
		$billing_city       = $customer->get_billing_city();
		$billing_state      = $customer->get_billing_state();
		$billing_country    = $customer->get_billing_country();
		echo $billing_address_1." ".$billing_city." ".$billing_state.", ". $billing_country;
	}else{
		echo "No billing address found";
	}
}

/** Password update **/
add_filter( 'gform_validation_9', 'custom_validation', 10, 4 );
function custom_validation( $validation_result ) {
	$old_password 		= rgpost( 'input_16' );
	$new_password 		= rgpost( 'input_15' );
	$confirm_password 	= rgpost( 'input_15_2' );
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
		$passupdatemsg = "Your Existing Password Doesn't match";
	}
	if ( !empty($old_password) && $pass_check == false) 
	{
		$validation_result['is_valid'] = false;
		foreach( $form['fields'] as &$field ) {
			if ( $field->id == '15' ) {
				$field->failed_validation = true;
				$field->validation_message = $passupdatemsg;
				break;
			}
		}
	}
	$validation_result['form'] = $form;
	return $validation_result;
}


/** Function to update user profile picture using ajax **/ 
add_shortcode('user_profile_image', 'user_profile_image_fn');
function user_profile_image_fn($atts){
	ob_start(); 
	extract( shortcode_atts( array(
		'user_id' => '',
		'width' => '',
	), $atts ) );
	if(empty($user_id)){ $user_id = get_current_user_id();}
	$image_id = get_field('user_profile_photo', 'user_'. $user_id);
	if($image_id){
		$size = "user_profile_photo_image";
		$user_img = wp_get_attachment_image_src( $image_id, array(112,112) );
		$user_img = $user_img[0];
	}else{
		$user_img = esc_url('https://gravatar.com/avatar?s=150&d=mm');
	}
?>
<div class="elementor-widget-container user_profile_image">
	<div class="elementor-image">
		<img src="<?=$user_img?>" class="attachment-full size-full author_info" alt="" >
	</div>
</div>
<?php
		$output = ob_get_contents();
	ob_end_clean();
	return $output;
}
/*Function to show user profile Image*/ 
/* Default Image Set In ACF */
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
function update_user_profile_pic_resale() {
	$user_id = 'user_'.get_current_user_id();
	$user_profile_photo = get_field_object('user_profile_photo', $user_id);	
	if(isset($_POST['photo_id'])){	
		if(!empty($user_profile_photo)){
			if(update_field('user_profile_photo', $_POST['photo_id'], $user_id)){
				wp_die(json_encode(
					array('status' => true)
				));
			}else{
				wp_die(json_encode(
					array('status' => false)
				));
			} 
		}else{

			if(update_field('user_profile_photo', $_POST['photo_id'], $user_id)){
				update_field('_user_profile_photo', 'field_644b688d45c81', $user_id);
				wp_die(json_encode(
					array('status' => true)
				));
			}else{
				wp_die(json_encode(
					array('status' => false)
				));
			} 
		}
	}else{
		wp_die(json_encode(
			array('status' => false)
		));
	}
}
add_action('wp_ajax_asgmt_update_user_profile_pic', 'update_user_profile_pic_resale');
add_action('wp_ajax_nopriv_asgmt_update_user_profile_pic', 'update_user_profile_pic_resale');


add_shortcode( 'sign_in_after_checkout', function(){
	if(isset($_SESSION['entry_email']))
	{
		$user = get_user_by( 'email', $_SESSION['entry_email'] );
		$user_login = $user->user_login;
		$creds = array(
			'user_login'    => $user_login,
			'user_password' => $_SESSION['entry_key'],
			'remember'      => false
		);
		$wp = wp_signon( $creds, true );
		if ( is_wp_error( $wp ) ) {
			print_r($wp->get_error_message());
		}else{
			$checkout_url = wc_get_checkout_url();
			unset($_SESSION['entry_key']);
			unset($_SESSION['entry_email']);
			wp_redirect( $checkout_url );
			exit;
		}
	}else{
		wp_redirect( home_url( 'school-registration' ) );
		exit;
	}
});

/** Last Login Table **/
add_action( 'wp_login', 'smartwp_capture_login_time', 10, 2 );
function smartwp_capture_login_time( $user_login, $user ) {
	update_user_meta( $user->ID, 'last_login', time() );
}

add_filter( 'manage_users_columns', 'smartwp_user_last_login_column' );
add_filter( 'manage_users_custom_column', 'smartwp_last_login_column', 10, 3 );
function smartwp_user_last_login_column( $columns ) {
	$columns['last_login'] = 'Last Login';
	return $columns;
}

function smartwp_last_login_column( $output, $column_id, $user_id ){
	if( $column_id == 'last_login' ) {
		$last_login = get_user_meta( $user_id, 'last_login', true );
		$date_format = 'M j, Y';
		$hover_date_format = 'F j, Y, g:i a';
		$output = $last_login ? '<div title="Last login: '.date( $hover_date_format, $last_login ).'">'.human_time_diff( $last_login ).' ago </div>' : 'No record';
	}
	return $output;
}

add_filter( 'manage_users_sortable_columns', 'smartwp_sortable_last_login_column' );
add_action( 'pre_get_users', 'smartwp_sort_last_login_column' );
function smartwp_sortable_last_login_column( $columns ) {
	return wp_parse_args( array(
		'last_login' => 'last_login'
	), $columns );
}

function smartwp_sort_last_login_column( $query ) {
	if( !is_admin() ) {
		return $query;
	}
	$screen = get_current_screen();
	if( isset( $screen->base ) && $screen->base !== 'users' ) {
		return $query;
	}
	if( isset( $_GET[ 'orderby' ] ) && $_GET[ 'orderby' ] == 'last_login' ) {
		$query->query_vars['meta_key'] = 'last_login';
		$query->query_vars['orderby'] = 'meta_value';

	}
	return $query;
}

/** Show admin bar to specific users **/
function hide_admin_bar_based_on_email() {
	$user_email = wp_get_current_user()->user_email;
	$hidden_emails = array(
		'eharris@sempraglobal.com',
		'ignacio.torresjr@centerpointenergy.com',
		'kcolunga@hess.com',
		'charles.lame@h2obridge.com',
		'duane.harris@sick.com',
		'dev@hyperlilnksmedia.com'
	);
	if (in_array($user_email, $hidden_emails)) {
		show_admin_bar(true);
	}
	else {
		show_admin_bar(false);
	}
}
// add_action('after_setup_theme', 'hide_admin_bar_based_on_email');


/** Shortcode for Purchase CEU **/
function ceu_registered_fn() {
	$product_ids = array(22100, 22101, 22102, 18785);
	$user_id = get_current_user_id();
	$args = array(
		'limit' => -1,
		'status' => array('wc-completed'), // Replace with the desired order statuses
		'customer_id' => $user_id,
		'meta_query' => array(
			array(
				'key' => '_product_id',
				'value' => $product_ids,
				'compare' => 'IN',
			),
		),
	);
	
	$orders = wc_get_orders($args);
	$attendee_names = array();
	foreach ($orders as $order) {
		$items = $order->get_items();
		$total_quantity = 0;

		foreach ($items as $item_id => $item) {
			$qty = $item->get_quantity();
			$total_quantity += $qty;
		}
		if(empty($order->get_meta('_attendees_order_meta')))
		{
			$attendees_count = 0;
		}else{
			$attendees_count = count($order->get_meta('_attendees_order_meta'));
		}
		if($total_quantity > $attendees_count)
		{

			foreach ($items as $item_id => $item) {
				$product_id = $item->get_product_id();
				if(in_array($product_id, $product_ids))
				{
					$attendee_names[] = $product_id;
				}
				break;
			}
		}	
	}
	
	// unset($args['meta_key']);
	unset($args['customer_id']);
	$args['meta_query']  = array(
		array(
			'key'     => '_attendees_order_meta', 
			'compare' => '=',
		),
	);
	$orders = wc_get_orders($args);
	if(!empty($orders))
	{
		foreach ($orders as $order) {
			$user_meta = $order->get_meta('_attendees_order_meta');
			// print_r($user_meta);
			if(!empty($user_meta))
			{
				$filtered_meta_value = array_filter($user_meta, function($item) use ($user_id) {
					return $item['user_id'] === $user_id;
				});
				// $filtered_meta_value = array();
				if(!empty($filtered_meta_value))
				{
					foreach ($filtered_meta_value as $filtered_data) {
						if(in_array($filtered_data['product_id'], $product_ids))
						{
							$attendee_names[] = $filtered_data['product_id'];
						}
					}
				}
			}
		}
	}
	if(count($attendee_names)>0)
	{
		return true;
	}else{
		return false;
	}
}
add_shortcode('ceu_registered', 'ceu_registered');
function ceu_registered(){
	if ( ceu_registered_fn() ) {
		echo 'Registered <i aria-hidden="true" class="asgmtsf-01 asgmt-sf-01Check-mark"></i>';
	} else {
		printf('<a href="%s">Add Registration</a> <i aria-hidden="true" class="fas fa-plus"></i>', site_url('checkout?add-to-cart=18785'));
	}
}

// Add a custom function to redirect users after successful checkout
function redirect_after_checkout($order_id) {
	$order = wc_get_order($order_id);
	$order_status = $order->get_status();
	// Check if the order status is "processing" or "completed"
	if (in_array($order_status, array('processing', 'completed'))) {
		wp_enqueue_style( 'sweetalert2', get_stylesheet_directory_uri() . '/assets/css/sweetalert2.min.css', array(), time(), 'all' );
		wp_enqueue_script('sweetalert2', get_stylesheet_directory_uri() . '/assets/js/sweetalert2.all.min.js', array('jquery'), time(), true);
		wp_enqueue_script('confirmation-popup', get_stylesheet_directory_uri() . '/assets/js/confirmation-popup.js', array('jquery'), time(), true);
		
		wp_localize_script('confirmation-popup', 'confirmationPopupParams', array(
			'redirectURL' => dashboard_url_fn()
		));
	}else{

	}
}
add_action('woocommerce_thankyou', 'redirect_after_checkout', 10, 1);

/** Get Users Full Name **/
function get_user_fullname() {
	$user = wp_get_current_user();
	if ($user) {
		$billing_first_name = get_user_meta($user->ID, 'billing_first_name', true);
		$billing_last_name = get_user_meta($user->ID, 'billing_last_name', true);
		// if(empty($billing_first_name) || empty($billing_last_name))
		// {
			$firstname = $user->first_name;
			$lastname = $user->last_name;
			return $firstname . ' ' . $lastname;
		// }else{
		// 	return $billing_first_name . ' ' . $billing_last_name;
		// }
	}
	return '';
}
add_shortcode('user_fullname', 'get_user_fullname');

add_filter( 'gform_confirmation_9', 'custom_confirmation', 10, 4 );
function custom_confirmation( $confirmation, $form, $entry, $ajax ) {
    if( $form['id'] == '9' ) {
        $confirmation = array( 'redirect' => site_url( 'my-asgmt-dashboard/my-profile' ) );
    }
    return $confirmation;
}
// Define the shortcode function
function get_purchased_product_name_shortcode($atts) {
	$atts = shortcode_atts(
		array(
			'registerd' => false,
		), $atts, 'purchased_product_name' 
	);
	$customer_id = get_current_user_id(); // Replace with the customer ID
	$args = array(
		'limit' => -1,
		'status'         => 'wc-completed', // Retrieve completed orders
		'customer_id'    => $customer_id,
	);

	$Order_Query = new WC_Order_Query($args);

	$orders = $Order_Query->get_orders();
	$product_title = array();
	if(!empty($orders))
	{
		foreach ($orders as $order) {
			$items = $order->get_items();
			$attendees_cont = 0;

			if(!empty($order->get_meta('_attendees_order_meta'))){
				$attendees_cont = count($order->get_meta('_attendees_order_meta'));
			}
			$total_quantity = 0;

			foreach ($items as $item_id => $item) {
				$qty = $item->get_quantity();
				$total_quantity += $qty;
			}
			if($total_quantity > $attendees_cont)
			{
				foreach ($items as $item) {
					$product_id = $item->get_product_id();
					if($product_id != 18792 ) {
						$product 		 = $item->get_product();
						if(!empty($product))
						{
							error_log(print_r('$product',true));
							error_log(print_r($product,true));
							$product_title[] = $product->get_name();	
						}
					}
					break;
				}
			}	
		}
	}
		unset($args['customer_id']);
		$args['meta_query']  = array(
            array(
                'key'     => '_attendees_order_meta', 
				'value' => '',
				'compare' => '!=',
            ),
        );
		$orders = wc_get_orders( array(
			'limit'        => -1, // Query all orders
			'orderby'      => 'date',
			'order'        => 'DESC',
			'meta_key'     => '_attendees_order_meta',
			'meta_compare' => '!=',
			'meta_value'   => '',
		));
		if(!empty($orders))
		{
			$attendee_names = array();
			foreach ($orders as $order) {
				$user_meta = $order->get_meta('_attendees_order_meta');

				if(!empty($user_meta))
				{
					$filtered_meta_value = array_filter($user_meta, function($item) use ($customer_id) {
						return $item['user_id'] === $customer_id;
					});
					if(!empty($filtered_meta_value))
					{
						foreach ($filtered_meta_value as $filtered_data) {
							if($filtered_data['product_id'] != 18792 ) {
								$product_title[] = get_the_title($filtered_data['product_id']);					
							}						
						}
					}
				}
			}
		}
	// }
	if($atts['registerd']){
		if(!empty($product_title)) {
			echo '<p>Registered <i aria-hidden="true" class="asgmtsf-01 asgmt-sf-01Check-mark"></i></p>';
		}else{
			echo '<a href="'. site_url("school-registration").'" class="custom-register-btn">Register for School <i aria-hidden="true" class="fas fa-plus"></i></a>';
		}
	}else{
		if(!empty($product_title)) {
			print_r(implode(', ', $product_title));
		}else{
			echo "<p>Not Registered</p>";
		}
	}	
}
add_shortcode('purchased_product_name', 'get_purchased_product_name_shortcode');

add_action('woocommerce_order_details_after_order_table', 'add_custom_column_to_order_table_items');
function add_custom_column_to_order_table_items($order) {
	// this order meta checks if order is marked as a entry
	$_gravity_form_entry_id = $order->get_meta('_gravity_form_entry_id');
	// we won't display anything if it is not a entry id
	if (!empty($_gravity_form_entry_id)) {
		$entry = GFAPI::get_entry($_gravity_form_entry_id);
		if($entry['1034'] == 'yes' || $entry['form_id'] == 13){
			$user_order_metadata = $order->get_meta('_attendees_order_meta');
			echo"<h2 class='attendees-info-heading' style='border-top: 1px solid #252F86;'>Attendees Information</h2><div class='attendee-info'>";
			// print_r($entry['1000']);
			echo"<table class='attendee-info'><thead><tr><th>Attendee name</th><th>Attendee email</th><th>Attendee product</th></tr></thead><tbody>";
			foreach ($entry['1000'] as $key => $attendee) {
				$product_title = $user_order_metadata[$key]['product_id'];
				printf("<tr><td>%s</td><td>%s</td><td>%s</td></tr>", $attendee['1002'].' '.$attendee['1003'], $attendee['1001'], get_the_title( $product_title ));
			}
			echo"</tbody></table></div>";
		}
	}
}
add_filter( 'woocommerce_add_to_cart_validation', 'remove_cart_item_before_add_to_cart', 20, 3 );
function remove_cart_item_before_add_to_cart( $passed, $product_id, $quantity ) {
    if( ! WC()->cart->is_empty() )
        WC()->cart->empty_cart();
    return $passed;
}

/** Woo remove footer app text **/
function woo_disable_mobile_messaging( $mailer ) {
	remove_action( 'woocommerce_email_footer', array( $mailer->emails['WC_Email_New_Order'], 'mobile_messaging' ), 9 );
}
add_action( 'woocommerce_email', 'woo_disable_mobile_messaging' );

add_action('admin_menu', function(){
	add_menu_page(
        'My ASGMT', // $page_title
        'My ASGMT', // $menu_title 
        'manage_options', // $capability
        'my-asgmt-dashboard', // $menu_slug 
        'my_asgmt_dashboard', // $callback 
        'dashicons-admin-users', // $icon_url
        150 // $position
    );
});

function my_asgmt_dashboard()
{
    $redirect_url = site_url('members-dashboard');
    wp_redirect($redirect_url);
    exit();
}

function sort_roles_alphabetically($roles) {
	$user_obj = isset($_REQUEST['user_id']) ? $user_meta = get_userdata($_REQUEST['user_id']) : false;
	if($user_obj && !in_array('student', $user_obj->roles))
	{
		unset($roles['exhibits_committee_member']);
	}
    uasort($roles, function($a, $b) {
        return strcasecmp($b['name'], $a['name']);
    });

    return $roles;
}
add_filter('editable_roles', 'sort_roles_alphabetically');
add_action('init', 'load_companies_post_type_class');

function load_companies_post_type_class() {
    if (!is_admin()) {
        return; // Exit if not in the admin area
    }
	include_once 'inc/companies.php';
}

function check_committe_member_role($errors, $update, $user) {
    if ($update) {
        $user = new WP_User($user->ID);
        $isExhibitsCommitteeMember = $_POST['role'] === 'exhibits_committee_member';
        $hasStudentRole = in_array('student', $user->roles);

        if (!$hasStudentRole && $isExhibitsCommitteeMember) {
            $errors->add('committe_member', __('Committe member should be registered as a student', 'savior-pro'));

        } else {
            $newRoles = !empty($_POST['ure_other_roles']) ? explode(', ', $_POST['ure_other_roles']) : array();

            if (in_array('exhibits_committee_member', $newRoles) && !$hasStudentRole) {
                $errors->add('committe_member', __('Committe member should be registered as a student', 'savior-pro'));

            }
        }
    }
	return $errors;
}

// add_action('user_profile_update_errors', 'check_committe_member_role', 10, 3);
// Add "Billing Company" value to Stripe metadata
function add_custom_metadata_to_stripe_payment($meta_data, $order) {
    foreach ( $order->get_items( 'line_item' ) as $item ) {
		$product 	= $item->get_product();
		// if($item->get_product_id() !== 22101 && $item->get_product_id() !== 22102)
		// {
			$key   		= 'product_sku_' . $item->get_product_id();
			$meta_data[ $key ] = $product->get_sku();		
		// }
	}
	return $meta_data;
}

function get_all_product_stock_quantities(){
    global $wpdb;
    $query = "
        SELECT p.ID, p.post_title, pm.meta_value AS stock_quantity
        FROM {$wpdb->prefix}posts AS p
        LEFT JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id AND pm.meta_key = '_stock'
        WHERE p.post_type = 'product' AND p.post_status = 'publish'
    ";
    $results = $wpdb->get_results($query);
    $stock_quantities = array();
    foreach ($results as $result) {
        $product_id = $result->ID;
        $product_title = $result->post_title;
        $stock_quantity = $result->stock_quantity;

        $stock_quantities['stock_quantity_'.$product_id] = $stock_quantity;
    }
    return (object)$stock_quantities;
}
add_filter('wc_stripe_order_meta_data', 'add_custom_metadata_to_stripe_payment', 10, 2);

// Profile updates using the gravity form
add_action( 'gform_user_updated', 'change_role', 10, 4 );
function change_role( $user_id, $feed, $entry, $user_pass ) {
	global $wpdb;
	$user_id = get_current_user_id();
	if(!empty(rgar( $entry, '18' )))
	{
		$wpdb->update($wpdb->users, array('user_login' => rgar( $entry, '18' )), array('ID' => $user_id));
	}

	$speaker = rgar( $entry, '19.1' ); //field id 6
	
	$user_obj = new WP_User( $user_id );
	if ( $speaker == 'yes') {
		$user_obj->set_role( 'speaker' );
	}else{
		$user_obj->remove_role( 'speaker' );
	}
}

function handle_relation_between_orders( $query, $query_vars ) {
	if ( ! empty( $query_vars['_relation_between_orders'] ) ) {
		$query['meta_query'][] = array(
			'key' => '_relation_between_orders',
			'value' => esc_attr( $query_vars['_relation_between_orders'] ),
		);
	}

	return $query;
}
add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', 'handle_relation_between_orders', 10, 2 );


add_filter('gform_field_validation_13', 'custom_email_validation', 10, 4);
add_filter('gform_field_validation_11', 'custom_email_validation', 10, 4);

// Initialize an array to store entered email addresses.
$email_addresses = array();

function custom_email_validation($result, $value, $form, $field) {
    global $email_addresses; // Access the global array of email addresses.

	if (!empty($value) && is_array($value)) {
        foreach ($value as $index => $email) {
            if (isset($email['1001']) && !empty($email['1001'])) {
                $entered_email = $email['1001'];

                if (in_array($entered_email, $email_addresses)) {
                    // Set an error message for the specific field by field ID.
                    $result['is_valid'] = false;
                    $result['message'] = 'You cannot use the same email more than once in Field ID ' . $field['id'];
                    
                    // Highlight the field with an error.
                    $result['failed_validation'] = true;
                    $result['value'][$index]['1001'] = ''; // Clear the value to prompt correction.
                    break; // Stop checking further if a duplicate is found.
                } else {
                    $email_addresses[] = $entered_email;
                }
            }
        }
    }

    return $result;
}

add_action('gform_field_validation_13', function( $result, $value, $form, $field ){
	if( $field->id === 1001 && is_user_logged_in() && !empty($value))
	{
		$current_user = wp_get_current_user();
		if ( $current_user->user_email === trim($value) )
		{
			$result['is_valid'] = false;
			$field->validation_message = 'Signed in user should use the My Registration and not the Attendee Registration process.';
		}
	}
	return $result;
}, 10, 4);

// add_shortcode( 'test', function(){
// 	echo "<pre>";
// 	$pages = array(19579, 18332, 18334, 19349, 19515, 18601, 24902);
// 	$new_roles = array('lmf_in_person', 'gmf_in_person', 'gmf_virtual', 'lmf_virtual', 'exhibitscommitteeliaison', 'registrationcommittee2ndvicechairperson', 'registrationcommitteebodliaison', 'arrangementliaison', 'exhibitscommittee2ndvice', 'marketingcommittee2ndvice', 'marketingcommitteeliaison', 'programcommitteeliaison', 'programcommittee2ndvice', 'websitecommittee2ndvice', 'websitecommitteeliaison', 'generalchairperson', 'bodpresident', 'bodvicepresident', 'bodsecretary', 'bodtreasurer', 'bodhistorian', 'speaker');
// 	foreach ($pages as $page)
// 	{
// 		$postMeta = get_post_meta( $page, 'user_roles', true);
// 		$rol = array_unique( array_merge($postMeta, $new_roles) );
// 		update_post_meta( $page, 'user_roles', $rol);
// 	}
// 	echo "</pre>";
// });
