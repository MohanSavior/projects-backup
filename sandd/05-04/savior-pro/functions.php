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
	wp_localize_script(
		'savior-pro-scripts',
		'savior_ajax_obj',
		array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'savior_nonce' ),
			'current' => get_current_user_details(),
			'check_offer_post' => check_offer_post()
		)
	);

	if(is_singular('startup') || is_page(1796)){
		wp_enqueue_style( 'savior-pro-mCustomScrollbar-styles', get_stylesheet_directory_uri() . '/inc/mCustomScrollbar/mCustomScrollbar.min.css', array(), time(), 'all' );
		wp_enqueue_script( 'savior-pro-mCustomScrollbar-scripts', get_stylesheet_directory_uri() . '/inc/mCustomScrollbar/mCustomScrollbar.js', array('jquery'), time(), true );
	}
	//
	if(is_page(2013)){
		wp_enqueue_style( 'dataTables-styles', get_stylesheet_directory_uri() . '/assets/css/jquery.dataTables.min.css', array(), time(), 'all' );
		wp_enqueue_style( 'dataTables-responsive-styles', get_stylesheet_directory_uri() . '/assets/css/responsive.dataTables.min.css', array(), time(), 'all' );
		wp_enqueue_script( 'dataTables-scripts', get_stylesheet_directory_uri() . '/assets/js/jquery.dataTables.min.js', array('jquery'), '1.13.4', true );
		wp_enqueue_script( 'dataTables-responsive-scripts', get_stylesheet_directory_uri() . '/assets/js/dataTables.responsive.min.js', array('jquery'), '2.4.1', true );
	}
}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );


require_once('inc/startup-post.php');
/** Get Percentage and Substraction Start **/
function get_current_user_details(){
	if(is_user_logged_in())
	{
		$user = get_user_by( 'id', get_current_user_id() );
		return array(
			'first_name'	=> $user->first_name,
			'last_name'		=> $user->last_name,
			'organization'	=> get_user_meta($user->ID, 'organization', true),
			'phone_number'	=> get_user_meta($user->ID, 'tp_phone_number', true),
			'offer_value'	=> is_singular( 'offers' ) ? get_post_meta(get_the_ID(), 'offer_value', true) : ''
		);
	}
	return [];
}
// Substraction
function display_discount_amount_post_func(){
	ob_start();

	$asking_price = get_field( "asking_price" );
	$discounted_asking_price = get_field( "discounted_asking_price" );
	$remaining_ammount = $asking_price-$discounted_asking_price;  
	$remaining_ammount_final = number_format($remaining_ammount);
	echo $remaining_ammount_final;  
	
	$output = ob_get_clean();
	return $output;	
}
add_shortcode('display_discount_amount_post', 'display_discount_amount_post_func');

// Percentage
function display_discount_percent_post_func(){
	ob_start();

	$asking_price = get_field( "asking_price" );
	$discounted_asking_price = get_field( "discounted_asking_price" );
	$percent = ($asking_price-$discounted_asking_price)/$discounted_asking_price*100;
	$total_percentage = number_format( $percent, 1 );
	echo $total_percentage . '% Off';

	$output = ob_get_clean();
	return $output;	
}
add_shortcode('display_discount_percent_post', 'display_discount_percent_post_func');

/** Get Percentage and Substraction End **/

/** Get Year Fucntion Start **/

function display_year_post_func(){
	ob_start();

	$field_date = strtotime(get_field( "year_of_incorporation" ));
	echo date_i18n( "Y", $field_date );

	$output = ob_get_clean();
	return $output;	
}
add_shortcode('display_year_post', 'display_year_post_func');

/** Get Year Fucntion End **/

/** Marketplace Features Repeater Field Section Func Start **/

function display_feature_repeater_func(){
	ob_start();

	$main_features = get_field('features');
		
	if( $main_features ) {
		echo '<div class="feature-main-sec-cls">';
		foreach( $main_features as $main_feature ) {
			$features_title = $main_feature['features_title'];
			$feature_description__ = $main_feature['feature_description__'];
			
			echo '<div class="feature-box-cls">';
				echo '<div class="feature-title-cls"><h4>'.$features_title.'</h4></div>';
				echo '<div class="feature-desc-cls"><p>'.$feature_description__.'</p></div>';
			echo '</div>';
		}
		echo '</div>';
	}

	$output = ob_get_clean();
	return $output;	
}
add_shortcode('display_feature_repeater', 'display_feature_repeater_func');

/** Marketplace Features Repeater Field Section Func end **/

/** Marketplace Features PDF Field Section Func Start **/

function display_feature_pdf_func(){
	ob_start();

	$future_profitability_and_competitive_performance = get_field('future_profitability_and_competitive_performance');	
	echo '<div class="future_profit_pdf">';
		echo '<iframe src="'.$future_profitability_and_competitive_performance.'" height="450px" ></iframe>';
	echo '</div>';

	$output = ob_get_clean();
	return $output;	
}
add_shortcode('display_feature_pdf', 'display_feature_pdf_func');

// Doc and excel
function display_financials_file_func(){
	ob_start();

	$balance_sheets = get_field('balance_sheets');	
	echo '<div class="future_profit_pdf">';
		echo '<iframe src="https://docs.google.com/viewer?url='.$balance_sheets.'&embedded=true" height="450px" ></iframe>';
	echo '</div>';

	$output = ob_get_clean();
	return $output;	
}
add_shortcode('display_financials_file', 'display_financials_file_func');

function display_income_statements_file_func(){
	ob_start();

	$income_cashflow_statements = get_field('income_cashflow_statements');	
	echo '<div class="future_profit_pdf">';
		echo '<iframe src="https://docs.google.com/viewer?url='.$income_cashflow_statements.'&embedded=true" height="450px" ></iframe>';
	echo '</div>';

	$output = ob_get_clean();
	return $output;	
}
add_shortcode('display_income_statements_file', 'display_income_statements_file_func');

/** Marketplace Features PDF Field Section Func End **/

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

// Logout URL and ShortCode

// Hide Admin bar
add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar() {
	if (!current_user_can('administrator') && !is_admin()) {
		show_admin_bar(false);
	}
}
// Hide Admin bar

// Market Place Side bar
function marketplace_filter_func(){
	ob_start();
	?>
	<div class="wrapper">
		<header>
			<h2>Price Range</h2>
		</header>
		<form class="filter-form">
			<div class="slider">
				<div class="progress"></div>
			</div>
			<div class="range-input">
				<input type="range" class="range-min" min="0" max="10000" value="2500" step="100">
				<input type="range" class="range-max" min="0" max="10000" value="7500" step="100">
			</div>
			<div class="price-input">
				<div class="field">
					<input type="number" class="input-min" value="2500">
				</div>
				<div class="separator">to</div>
				<div class="field">
					<input type="number" class="input-max" value="7500">
				</div>
				<div class="submit-btn">
					<input type="submit" value="Update">
				</div>
			</div>
			
			<div class="taxonomy-list-cls">
				<header>
					<h2>Filters</h2>
					<a href="#" class="reset-btn-cls">Reset</a>
				</header>
				<div class="filter-field-cls">
					<span class="select-fields-main-cls">
						<span class="search-field-cls">
							Lorem ipsum long
						</span>
					</span>
				</div>
			</div>
			
			<div class="taxonomy-list-cls">
				<header>
					<h2>Availability</h2>
				</header>
				<?php $categories = get_categories('taxonomy=availability&post_type=startup'); 
					foreach ($categories as $category) : ?>
					<label>
						<input type="checkbox" class="taxonomy-name" name="<?php echo $category->name; ?>">
						<?php echo $category->name; ?>
					</label>
				<?php 
					endforeach; 
				?>
			</div>
			
			<div class="taxonomy-list-cls">
				<header>
					<h2>Categories-specific filter</h2>
				</header>
				<?php $categories = get_categories('taxonomy=startup_categories&post_type=startup'); 
					foreach ($categories as $category) : ?>
					<label>
						<input type="checkbox" class="taxonomy-name" name="<?php echo $category->name; ?>">
						<?php echo $category->name; ?>
					</label>
				<?php 
					endforeach; 
				?>
			</div>
			
			<div class="taxonomy-list-cls">
				<header>
					<h2>Tags-specific filter</h2>
				</header>
				<?php $categories = get_categories('taxonomy=startup_tags&post_type=startup'); 
					foreach ($categories as $category) : ?>
					<label>
						<input type="checkbox" class="taxonomy-name" name="<?php echo $category->name; ?>">
						<?php echo $category->name; ?>
					</label>
				<?php 
					endforeach; 
				?>
			</div>
			
			<div class="taxonomy-list-cls">
				<header>
					<h2>Seller</h2>
				</header>
				<?php $categories = get_categories('taxonomy=seller&post_type=startup'); 
					foreach ($categories as $category) : ?>
					<label>
						<input type="checkbox" class="taxonomy-name" name="<?php echo $category->name; ?>">
						<?php echo $category->name; ?>
					</label>
				<?php 
					endforeach; 
				?>
			</div>
			
		</form>
	</div>
	<?php
	$output = ob_get_clean();
	return $output;	
}
add_shortcode('marketplace_filter', 'marketplace_filter_func');
// Market Place Side bar

// Sorting Filter
function shorting_filter_func(){
	ob_start();
	?>
	<div class="wrapper sorting-main-cls">
		<form class="sorting-form">
			<select name="name-sorting" id="name_sorting">
				<option value="" disabled selected>Sort By Name</option>
				<option value="A-Z" name="A-Z">A-Z</option>
				<option value="Z-A" name="Z-A">Z-A</option>
			</select>
		</form>
	</div>
	<?php
	$output = ob_get_clean();
	return $output;	
}
add_shortcode('shorting_filter', 'shorting_filter_func');
// Sorting Filter

// Filter Result Box
function filter_result_box_func(){
	ob_start();
	?>
	<div class="wrapper filter-result-box-main-cls">
		<header>
			<h2>Filter Results</h2>
			<a href="#" class="filter-close-btn">X</a>
		</header>	
		<form class="filter-result-box">
			<label><span class="label-cls">Tags</span>
				<input type="text" name="post-tag" id="post_tag">
			</label>
			<div class="slider slider-empl">
				<label><span class="label-cls">Employees</span></label>
				<input type="range" min="0" max="200" value="100" id="range_empl" oninput="rangeValueempl.innerText = this.value">
				<p id="rangeValueempl">100</p>
			</div>
			<div class="slider slider-mrr">
				<label><span class="label-cls">MRR</span></label>
				<input type="range" min="0" max="200" value="100" id="range_mrr" oninput="rangeValuemrr.innerText = this.value">
				<p id="rangeValuemrr">100</p>
			</div>
			<div class="slider slider-cltv">
				<label><span class="label-cls">CLTV</span></label>
				<input type="range" min="0" max="200" value="100" id="range_cltv" oninput="rangeValuecltv.innerText = this.value">
				<p id="rangeValuecltv">100</p>
			</div>
			<div class="slider slider-cac">
				<label><span class="label-cls">CAC</span></label>
				<input type="range" min="0" max="200" value="100" id="range_cac" oninput="rangeValuecac.innerText = this.value">
				<p id="rangeValuecac">100</p>
			</div>
			<input type="submit" value="Refresh Result" class="form-btn-cls">
		</form>
	</div>
	<?php
	$output = ob_get_clean();
	return $output;	
}
add_shortcode('filter_result_box', 'filter_result_box_func');
// Filter Result Box

// Gravity Form Repeater Field

add_filter( 'gform_form_post_get_meta_6', 'add_my_field' );
function add_my_field( $form ) {
	$field_id = 1000;
	// Create a Single Line text field for the team member's name
	$feature_title = GF_Fields::create( array(
		'type'   => 'text',
		'required' => true,
		'id'     => 1001, // The Field ID must be unique on the form
		'formId' => $form['id'],
		'label'  => 'Feature Title',
		'pageNumber'  => 1, // Ensure this is correct
		'placeholder' => 'Feature Title',
		'isRequired' => false,
	) );

	$feature_description = GF_Fields::create( array(
		'type'   => 'text',
		'required' => true,
		'id'     => 1002, // The Field ID must be unique on the form
		'formId' => $form['id'],
		'label'  => 'Feature Description',
		'pageNumber'  => 1, // Ensure this is correct
		'placeholder' => 'Feature Description',
		'isRequired' => false,
	) );

	// Create a repeater for the team members and add the name and email fields as the fields to display inside the repeater.
	$feature_details = GF_Fields::create( array(
		'type'             => 'repeater',
		'required' => true,
		'description'      => 'Maximum of 6 Feature Details',
		'id'               => $field_id, // The Field ID must be unique on the form
		'formId'           => $form['id'],
		'label'            => '',
		'addButtonText'    => 'Add More', // Optional
		'removeButtonText' => 'Remove', // Optional
		'maxItems'         => 6, // Optional
		'pageNumber'       => 1, // Ensure this is correct
		'fields'           => array( $feature_title, $feature_description ), // Add the fields here.
	) );

	$repeater_exists = false;
	foreach ( $form['fields'] as $field ) {
		if ( 'repeater' === $field->type && $field->id === $field_id ) {
			$repeater_exists = true;
		}
	}
	if ($repeater_exists === false ) {
		//$form['fields'][] = $team;
		array_splice( $form['fields'], 25, 0, array( $feature_details ) );
	}

	return $form;
}

// Gravity Form Repeater Field

// Offer Sent Layout
function offer_sent_layout_func(){
	ob_start();

	$offers_args = array( 
		'post_type' 		=> 'offers', 
		'posts_per_page' 	=> -1,
		'author' 			=> get_current_user_id(),
		'post_status'		=>'publish'
	);
	$offers_query = new WP_Query( $offers_args ); 
	?>
	<div class="offer-sent-main-sec-cls">		
		<div class="offer-header">
			<div class="offer-header-left-sec">
				<a href="#">All Offers</a>
				<a href="#">All Messages</a>
			</div>
			<div class="offer-header-right-sec">
				<div class="search-form">
					<form class="offer-search-form">
						<input type="search" name="s" title="Search" value="" placeholder="Search Offers">
						<select name="date-sorting" id="date_sorting">
							<option value="" disabled selected>Sort by: Date</option>
							<option value="Date" name="Date">Date</option>
							<option value="Name" name="Name">Name</option>
						</select>
					</form>
				</div>
			</div>
		</div>
		<?php
		if ( $offers_query->have_posts() ) : 
			while ( $offers_query->have_posts() ) : $offers_query->the_post(); 
		?>
		<div class="offer-list-post-main-cls">
			<div class="startup-post-sec-cls">
				<?php
					$business_profile_post_id = get_post_meta(get_the_ID(), 'offer_for_post_id', true);
					echo do_shortcode('[startup_post_template post_id="'.$business_profile_post_id.'"]');
				?>
			</div>			
			<div class="offer-post-sec-cls">
				<div class="offer-post-list-cls">					
					<div class="header-btn-cls">
						<a href="#">Send Follow up</a>
						<a href="#">Donload Offer</a>
						<a href="#">Contact Owner</a>
						<a href="#">Cancel Offer</a>
					</div>					
					<div class="post-details">
						<div class="post-detail-table" style="overflow-x:auto;">
							<a href="<?php echo get_permalink( get_the_ID() ); ?>">
								<table>
									<tr>
										<th>Offer Name</th>
										<th><?php the_title(); ?></th>
									</tr>
									<tr>
										<td>Offer Value</td>
										<?php printf('<td>$%s</td>', number_format((int)get_post_meta( get_the_ID(), 'offer_value', true ),0,".",",")); ?>
									</tr>
									<tr>
										<td>Sent Date</td>
										<td><?php echo sprintf( esc_html__( '%s ago', 'textdomain' ), human_time_diff(get_the_time ( 'U' ), current_time( 'timestamp' ) ) ). ': ' .get_the_date('d/m/Y'); ?></td>
									</tr>
									<tr>
										<td>Seen Date</td>
										<td>Unseen</td>
									</tr>
									<tr>
										<td>Replies & Coutner Offers</td>
										<td>0</td>
									</tr>
								</table>
							</a>
						</div>
					</div>							
				</div>		
			</div>			
		</div>
		<?php
			endwhile;
			wp_reset_postdata(); 
			?>
		<?php else:  ?>
		<p><?php _e( 'Sorry, You haven\'t created any offer yet.' ); ?></p>
		<?php endif; ?>
	</div>
	<?php
	$output = ob_get_contents();
	ob_get_clean();
	return $output;	
}
add_shortcode('offer_sent_layout', 'offer_sent_layout_func');
// Offer Sent Layout

// Offer sent file

function offer_sent_file_func(){
	ob_start();

	$letter_of_intent = get_field('letter_of_intent');	
	if(!empty($letter_of_intent)){
		printf(
			'<div class="letter_of_intent_file_cls">Document: <a href="%s" target="_Blank">%s</a> <i aria-hidden="true" class="fas fa-trash"></i></div>',
			$letter_of_intent['url'], $letter_of_intent['filename']
		);
	}
	$output = ob_get_clean();
	return $output;	
}
add_shortcode('offer_sent_file', 'offer_sent_file_func');
// Offer sent file

// Contact Directory Users Shortcode
function contact_direc_users_func(){
	ob_start();
	?>
	<table class="subs-user-list-cls">
		<thead>
			<tr>
				<th>Name</th>
				<th>Email</th>
				<th>Organization</th>
				<th>Date Added</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php
				$blogusers = get_users( array( 'role__in' => array( 'subscriber' ) ) );
				foreach ( $blogusers as $user ) {
			?>
			<tr>
				<th><?php echo esc_html( $user->display_name ); ?> </th>
				<th><?php echo esc_html( $user->user_email ); ?></th>
				<?php 
					$organization = get_field( "organization", $user ); 
					if(!empty($organization)){
						?>
						<th><?php echo $organization; ?></th>
					<?php
					}else {
						?>
						<th>Add organization</th>
						<?php
					}
				?>
				<?php $registered = $user->user_registered; ?>
				<th><?php echo date( "d/m/y", strtotime( $registered )); ?></th>
				<th><a href="#"><i aria-hidden="true" class="fas fa-ellipsis-h"></i></a></th>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php
	
	$output = ob_get_clean();
	return $output;	
}
add_shortcode('contact_direc_users', 'contact_direc_users_func');
// Contact Directory Users Shortcode

/** Watchlists Add Tag And Create New Tag **/
add_shortcode('watchlists_add_tag', 'watchlists_add_tag_func');
function watchlists_add_tag_func(){
	ob_start();
	if(is_singular('startup')){
		$args = array(
			'taxonomy'  => 'startup_tags',
			'hide_empty' => false
		);
		$startup_tags 		= get_terms( $args );
		$user_id 			= get_current_user_id();
		$user_checked_tag 	= get_user_meta( $user_id, 'startup_tag_ids', true );
		$tag_added 			= !empty(get_the_ID()) && !empty($user_checked_tag) ? (array_key_exists(get_the_ID(), $user_checked_tag) ? $user_checked_tag[get_the_ID()] : []) : [];
		?>
		<div class="watchlists-tags-main-sec">
			<ul>
				<?php 
					foreach( $startup_tags as $startup_tag){
						$checked_user_post_tag = !empty($tag_added) && in_array( $startup_tag->term_id, $tag_added ) ? 'checked="checked"' : '';
						printf(
							'<li><label for="watchlists-post-tag-%s"><input type="checkbox" id="watchlists-post-tag-%s" value="%s" name="watchlists-post-tag" %s data-name="%s"> %s </label></li>',
							$startup_tag->term_id,
							$startup_tag->term_id,
							$startup_tag->term_id,
							$checked_user_post_tag,
							$startup_tag->name,
							$startup_tag->name
						);
					}
				?>
				<li><label for="watchlists-post-other"><input type="checkbox" name="" id="watchlists-post-other"> Other </label></li>
				<li>
					<input type="text" name="watchlists-post-other-val" id="watchlists-post-other-val">
					<input type="hidden" name="post_id" id="watchlists-post-id" value="<?=get_the_ID()?>">
					<button id="watchlists-add-tags">
						<span>Add</span>
						<span class="spinner"></span>
					</button>
				</li>
			</ul>

		</div>
		<?php
	}
	$output = ob_get_contents();
	ob_get_clean();
	return $output;	
}

/**
 * Add new startup tag
 */
add_action( 'wp_ajax_add_startup_tags', 'add_new_startup_tag_fun' );
function add_new_startup_tag_fun() { 
    $nonce = $_POST['nonce'];

    if ( ! wp_verify_nonce( $nonce, 'savior_nonce' ) ) {
		wp_send_json_error(array('error_type' => 'nonce_not_verified', 'message' => 'Nonce value cannot be verified.'));
    }
 
    if ( isset( $_REQUEST ) && is_user_logged_in() ) {     
        $newstartup_tags 	= $_REQUEST['newstartup_tags'];
        $post_id 			= $_REQUEST['post_id'];
		$user_id 			= get_current_user_id();
		
		$taxonomy = 'startup_tags';
		if(term_exists( $newstartup_tags, $taxonomy )){
			wp_send_json_error(array('error_type' => 'term_exists', 'message' => 'Tag name : <b>'.$newstartup_tags.'</b> already exists.'));
		}else{
			$post_tag_obj = wp_insert_term($newstartup_tags, $taxonomy);
			if ( is_wp_error( $post_tag_obj ) ) {
				$error_string = $post_tag_obj->get_error_message();
				wp_send_json_error(array('error_type' => $post_tag_obj->get_error_code(), 'message' => $error_string));
			}else{
				$user_checked_tag 	= get_user_meta( $user_id, 'startup_tag_ids', true );
				if(empty($user_checked_tag)){
					update_user_meta($user_id, 'startup_tag_ids', array($post_id => array($post_tag_obj['term_id'])));
				}else{
					if(array_key_exists($post_id, $user_checked_tag))
					{
						$recursive = array_unique( array_merge($user_checked_tag[$post_id], array($post_tag_obj['term_id'])) );
						unset($user_checked_tag[$post_id]);
						if(!empty($user_checked_tag)){
							$startup_tag_ids = $user_checked_tag + array($post_id => $recursive);
						}else{
							$startup_tag_ids = array($post_id => $recursive);
						}
						update_user_meta($user_id, 'startup_tag_ids', $startup_tag_ids);
					}else{
						$recursive = $user_checked_tag + array($post_id => array($post_tag_obj['term_id']));
						update_user_meta($user_id, 'startup_tag_ids', $recursive);
					}
				}
			}
			wp_send_json_success(array('startup_tags'=>$post_tag_obj, 'message'=>'New StarUp Tag Added.'));
		}
    }else{
		wp_send_json_error(array('error_type' => 'no_data', 'message' => 'No data.'));
	}
}

/**
 * Tag Add or Remove
 */
add_action( 'wp_ajax_add_remove_startup_tags', 'add_remove_startup_tags_fun' );
function add_remove_startup_tags_fun() { 
    $nonce = $_POST['nonce'];

    if ( ! wp_verify_nonce( $nonce, 'savior_nonce' ) ) {
		wp_send_json_error(array('error_type' => 'nonce_not_verified', 'message' => 'Nonce value cannot be verified.'));
    }
 
    if ( is_user_logged_in() ) {     
        $post_id 			= $_REQUEST['post_id'];
		$user_id 			= get_current_user_id();
		$user_checked_tag 	= get_user_meta( $user_id, 'startup_tag_ids', true );
		if(empty($user_checked_tag) && !empty($_POST['newstartup_tags'])){
			update_user_meta($user_id, 'startup_tag_ids', array($post_id => $_POST['newstartup_tags']));
		}else{
			if(array_key_exists($post_id, $user_checked_tag))
			{
				$recursive = $_POST['newstartup_tags'];
				unset($user_checked_tag[$post_id]);
				if(!empty($user_checked_tag)){
					$startup_tag_ids = is_array($recursive) && !empty($recursive) ? $user_checked_tag + array($post_id => $recursive) : $user_checked_tag;
				}else{
					$startup_tag_ids = is_array($recursive) && !empty($recursive) ? array($post_id => $recursive) : $user_checked_tag;
				}
				update_user_meta($user_id, 'startup_tag_ids', $startup_tag_ids);
			}else{
				$recursive = $user_checked_tag + array($post_id => $_POST['newstartup_tags']);
				update_user_meta($user_id, 'startup_tag_ids', $recursive);
			}
		}
		wp_send_json_success();
	}else{
		wp_send_json_error(array('error_type' => 'no_data', 'message' => 'No data.'));
	}
}

/**
 * WatchList List Page
 */
add_shortcode('watch_list_page', 'watch_list_page_fn');
function watch_list_page_fn()
{
	ob_start();
	if ( is_user_logged_in() ) {    
		?>
			<div class="watch-list-page-wrapper">
				<?php
					$args = array(
						'taxonomy'  	=> 'startup_tags',
						'hide_empty' 	=> false,
						'fields'		=> 'ids'
					);
					$startup_tags		= get_terms( $args );
					$user_id 			= get_current_user_id();
					$user_checked_tags	= get_user_meta( $user_id, 'startup_tag_ids', true );
					if(!empty($user_checked_tags)){
						$all_startup_ids = get_posts(array(
							'post_type'       => 'startup',
							'fields'          => 'ids', // Only get post IDs
							'posts_per_page'  => -1,
							'post_status'     => 'publish'
						));
						$tag_with_post_data 	= array();
						$user_tags 		= array_flatten($user_checked_tags);
						
						foreach ( array_intersect( $startup_tags, $user_tags ) as $tag ) {
							foreach($user_checked_tags as $key => $rep){
								if( is_array($all_startup_ids) && in_array($key, $all_startup_ids) && is_array($rep) && in_array($tag, $rep) ) {
									$tag_with_post_data[$tag][] = $key;
								}
							}
						}
						foreach ($tag_with_post_data as $tag_term_id => $watchlist_posts_ids) {
							$tag_term_obj = get_term_by( 'term_id', $tag_term_id, 'startup_tags');
							$startup_the_query = new WP_Query( array( 
								'post_type' 		=> 'startup', 
								'post__in' 			=> $watchlist_posts_ids,
								'posts_per_page'  	=> -1,
								'post_status'     	=> 'publish'
							));
							$startup_posts_ids_obj = implode(',',$watchlist_posts_ids);
							?>
							<div class="watchlist-post-tag-rep" id="watchlist-tag-wrapper-<?=$tag_term_id?>" data-created="" data-updated="">
								<div class="watchlist-post-tag-list">
									<div class="watchlists-filters">
										<div class="filter-tag-heading">
											<h2>Watchlists “<?php echo $tag_term_obj->name; echo "” (" . count($watchlist_posts_ids) . ")"; ?></h2>
										</div>
										<div class="filter-action-cls">			
											<?php if(count($watchlist_posts_ids) > 1 ) : ?>								
											<a href="javascript:void(0);" data-startup_tag_id="<?=$tag_term_id?>" class="watchlists-edit-tag">Edit</a>
											<?php endif;?>
											<a href="javascript:void(0);" data-startup_tag_id="<?=$tag_term_id?>" data-ids="<?=$startup_posts_ids_obj?>" class="watchlists-delete-tag">Delete</a>
											<?php if(count($watchlist_posts_ids) > 1 ) : ?>
													<select name="" data-startup_tag_id="<?=$tag_term_id?>" class="watchlists-sort-by-rate">
														<option value="" disabled="disabled" selected="selected">Rate</option>
														<option value="mrr">MRR</option>
														<option value="cltv">CLTV</option>
														<option value="cac">CAC</option>
													</select>
											<?php endif;?>

											<div class="watchlists-tags-main-sec" id="watchlists-post-<?=$tag_term_id?>">
												<ul>
													<?php 
														$user_startup_meta 	= get_user_meta( $user_id, 'startup_tag_ids', true );
														$startup_posts_ids	= is_array($user_startup_meta) && !empty($user_startup_meta) ? array_keys($user_startup_meta) : [];
														if ( $startup_the_query->have_posts() ) {
															while ( $startup_the_query->have_posts() ) {
																$startup_the_query->the_post();											
																$checked_user_post_tag = !empty($startup_posts_ids) && in_array( get_the_ID(), $startup_posts_ids ) ? 'checked="checked"' : '';
																printf(
																	'<li class="watchlists-post-with-tag-li"><label for="watchlists-post-tag-%s"><input type="checkbox" id="watchlists-post-tag-%s" value="%s" name="watchlists-post-with-tag" %s data-name="%s" data-tag_id="%s"> %s </label></li>',
																	get_the_ID(),
																	get_the_ID(),
																	get_the_ID(),
																	$checked_user_post_tag,
																	get_the_title(),
																	$tag_term_id,
																	get_the_title()
																);
															}
															wp_reset_postdata();
														}
													?>	
												</ul>
	
											</div>
										</div>
									</div>
								</div>
								<div class="startup-post-sec-cls">
									<?php
									if ( $startup_the_query->have_posts() ) {
										while ( $startup_the_query->have_posts() ) {
											$startup_the_query->the_post();
											echo do_shortcode('[startup_post_template post_id="'.get_the_ID().'" tag_id="'.$tag_term_id.'"]');
										}
										wp_reset_postdata();
									}else{ ?>
										<p><?php _e( 'Sorry, no posts matched your criteria.' ); ?></p>
									<?php } ?>
								</div>
							</div>
				 <?php  }
					}else{ ?>
						<p><?php _e( 'Sorry, Your Watchlists Empty.' ); ?></p>
					<?php } 
				?>
			</div>
		<?php
	}
	$output = ob_get_contents();
	ob_get_clean();
	return $output;	
}
function array_flatten($array)
{
	$result = [];
	foreach ($array as $element) {
		if (is_array($element)) {
		$result = array_merge($result, array_flatten($element));
		} else {
		$result[] = $element;
		}
	}
	return $result;
}

/**
 * Remove Tags In Watch List
 */
add_action( 'wp_ajax_remove_watchlists_tags', 'remove_watchlists_tags_fun' );
function remove_watchlists_tags_fun() { 
    $nonce = $_POST['nonce'];

    if ( ! wp_verify_nonce( $nonce, 'savior_nonce' ) ) {
		wp_send_json_error(array('error_type' => 'nonce_not_verified', 'message' => 'Nonce value cannot be verified.'));
    }
    if ( is_user_logged_in() ) {     
        $post_id 			= $_REQUEST['post_id'];
		$user_id 			= get_current_user_id();
		$user_checked_tag 	= get_user_meta( $user_id, 'startup_tag_ids', true );

		if(empty($user_checked_tag) && !empty($_POST['tag_id'])){
			wp_send_json_error(array('error_type' => 'no_data', 'message' => 'No data.'));
		}else{
			if( is_array($post_id) ){
				foreach ($post_id as $pids) {
					$all_tag_with_this_post = $user_checked_tag[$pids];
					$tag_id 				= $_POST['tag_id'];
					if (($key = array_search($tag_id, $all_tag_with_this_post)) !== false) {
						unset($all_tag_with_this_post[$key]);
						unset($user_checked_tag[$pids]);
						$all_tag_data[$pids] = is_array($all_tag_with_this_post) && !empty($all_tag_with_this_post) ? $all_tag_with_this_post : [];
					}
				}
				update_user_meta($user_id, 'startup_tag_ids', $user_checked_tag + $all_tag_data);
			}else{
				if(array_key_exists($post_id, $user_checked_tag))
				{
					$all_tag_with_this_post = $user_checked_tag[$post_id];
					$tag_id 				= $_POST['tag_id'];
					if (($key = array_search($tag_id, $all_tag_with_this_post)) !== false) {
						unset($all_tag_with_this_post[$key]);
						unset($user_checked_tag[$post_id]);
						$all_tag_data = is_array($all_tag_with_this_post) && !empty($all_tag_with_this_post) ? $user_checked_tag + array($post_id => $all_tag_with_this_post) : $user_checked_tag;
						update_user_meta($user_id, 'startup_tag_ids', $all_tag_data);
					}
				}else{
					wp_send_json_error(array('error_type' => 'no_data', 'message' => 'No data.'));
				}
			}
		}
		wp_send_json_success();
	}else{
		wp_send_json_error(array('error_type' => 'no_data', 'message' => 'No data.'));
	}
}
/**
 * Make an offer Gravity form
 */
add_filter('gform_custom_merge_tags', 'organization_custom_merge_tags', 10, 4);
add_filter('gform_replace_merge_tags', 'organization_replace_merge_tags', 10, 7);
function organization_custom_merge_tags($merge_tags, $form_id, $fields, $element_id) {
    $merge_tags[] = array('label' => 'User Organization', 'tag' => '{user_organization}');
    return $merge_tags;
}
function organization_replace_merge_tags($text, $form, $lead, $url_encode, $esc_html, $nl2br, $format) {
    $userid = get_current_user_id();
    $organization = $userid ? get_user_meta($userid, 'organization', true) : '';
    $text = str_replace('{user_organization}', $organization, $text);
    return $text;
}
add_filter('gform_custom_merge_tags', 'current_user_id_custom_merge_tags', 10, 4);
add_filter('gform_replace_merge_tags', 'current_user_id_replace_merge_tags', 10, 7);
function current_user_id_custom_merge_tags($merge_tags, $form_id, $fields, $element_id) {
    $merge_tags[] = array('label' => 'Current User ID', 'tag' => '{current_user_id}');
    return $merge_tags;
}
function current_user_id_replace_merge_tags($text, $form, $lead, $url_encode, $esc_html, $nl2br, $format) {
    $userid = get_current_user_id();
    $text = str_replace('{current_user_id}', $userid ? $userid : '', $text);
    return $text;
}
/* User Meta Update */
add_action( 'gform_after_submission', 'user_meta_update_offer_sub', 10, 2 );
function user_meta_update_offer_sub( $entry, $form ) {

	if($form['id'] == 3 && is_user_logged_in()){		
		$first_name 	= rgar( $entry, '1.3' );
		$last_name 		= rgar( $entry, '1.6' );
		$phone 			= rgar( $entry, '3' );
		$organization 	= rgar( $entry, '7' );
		$user_id 		= get_current_user_id();
		$get_first_name = get_user_meta($user_id, 'first_name', true);
		$get_last_name  = get_user_meta($user_id, 'last_name', true);
		$get_organization = get_user_meta($user_id, 'organization', true);
		$get_phone  	= get_user_meta($user_id, 'tp_phone_number', true);
		if(empty($get_first_name)){
			update_user_meta($user_id, 'first_name', $first_name);
		}elseif( !empty($get_first_name) && strtolower($get_first_name) !== strtolower($first_name))
		{
			update_user_meta($user_id, 'first_name', $first_name);
		}
		//last name
		if(empty($get_last_name)){
			update_user_meta($user_id, 'last_name', $last_name);
		}elseif( !empty($get_last_name) && strtolower($get_last_name) !== strtolower($last_name))
		{
			update_user_meta($user_id, 'last_name', $last_name);
		}
		//Organization
		if(empty($get_organization)){
			update_user_meta($user_id, 'organization', $organization);
		}elseif( !empty($get_organization) && strtolower($get_organization) !== strtolower($organization))
		{
			update_user_meta($user_id, 'organization', $organization);
		}
		//Phone
		if(empty($get_phone)){
			update_user_meta($user_id, 'tp_phone_number', $phone);
		}elseif( !empty($get_phone) && strtolower($get_phone) !== strtolower($phone))
		{
			update_user_meta($user_id, 'tp_phone_number', $phone);
		}
	}
}
// Pre Render tags
add_filter( 'gform_pre_render_6', 'pre_render_startup_tags' );
add_filter( 'gform_pre_validation_6', 'pre_render_startup_tags' );
add_filter( 'gform_pre_submission_filter_6', 'pre_render_startup_tags' );
add_filter( 'gform_admin_pre_render_6', 'pre_render_startup_tags' );
function pre_render_startup_tags( $form ) { 
    foreach( $form['fields'] as &$field )  {
		//Tags
		$field_id = 7;
		if ( $field->id == $field_id ) {
			$args = array(
				'taxonomy'  => 'startup_tags',
				'hide_empty' => false
			);
			$startup_tags = get_terms( $args );
	
			foreach( $startup_tags as $post_startup_tags ) {
				$choices[] = array( 'text' => $post_startup_tags->name, 'value' => $post_startup_tags->term_id );
			}
			$field->choices = $choices;
		}
		//End tags
		//Startup Categories
		$field_id = 1008;
        if ( $field->id == $field_id ) {
			$args = array(
				'taxonomy'  => 'startup_categories',
				'hide_empty' => false
			);
			$startup_cats = get_terms( $args );
			foreach( $startup_cats as $post_startup_cats ) {
				$startup_categories[] = array( 'text' => $post_startup_cats->name, 'value' => $post_startup_cats->term_id );
			}
			$field->choices = $startup_categories;
		}
		//End Startup Categories
    }
    return $form;
}
/**
 * Advanced Post Creation Excerpt
 */
add_filter( 'gform_advancedpostcreation_excerpt', 'enable_excerpt', 10, 1 );
function enable_excerpt( $enable_excerpt ){
    return true;
}
// Use term_id instead of name to assign the taxonomy
add_action( 'gform_advancedpostcreation_post_after_creation_6', 'advancedpostcreation_checkboxes', 10, 4 );
	function advancedpostcreation_checkboxes( $post_id, $feed, $entry, $form ) {
		$the_post = array(
			'ID'           => $post_id,//the ID of the Post
			'post_excerpt' => $entry[9],
		);
	  	$updre = wp_update_post( $the_post );

		$categories = get_categories( array(
			'taxonomy'  => 'startup_categories',
			'hide_empty' => false,
			'fields'     => 'ids'
		) );
		$startup_cats = [];
		for($i=1; $i<=count($categories); $i++) {
			$startup_cats[] = isset($_POST['input_1008_'.$i]) ? $_POST['input_1008_'.$i] : '';			
		}
		if( !empty($startup_cats) ){
			wp_set_post_terms( $post_id, array_filter($startup_cats), 'startup_categories' ); 
		}
		//Tag
		$tags_categories = get_categories( array(
			'taxonomy'  => 'startup_tags',
			'hide_empty' => false,
			'fields'     => 'ids'
		) );
		$startup_tags = [];
		for($j=1; $j<=count($tags_categories); $j++) {
			$startup_tags[] = isset($_POST['input_7_'.$j]) ? $_POST['input_7_'.$j]: "";			
		}
		if( !empty($startup_tags) ){
			wp_set_post_terms( $post_id, array_filter($startup_tags), 'startup_tags' ); 
		}
		if(!empty($entry['17'])){
			$image_url = trim(str_replace( array( '\'', '"', ',' , ';', '<', '>', '[', ']' ), ' ', $entry['17']));
			$image = entry_upload_file_by_url($image_url);
			update_post_meta($post_id, 'main_photo', $image);
			update_post_meta($post_id, '_main_photo', 'field_637c9a7916892');
		}
		if(!empty($entry['19'])){
			$image_url_photo_1 = trim(str_replace( array( '\'', '"', ',' , ';', '<', '>', '[', ']' ), ' ', $entry['19']));
			$image_photo_1 = entry_upload_file_by_url($image_url_photo_1);
			update_post_meta($post_id, 'additional_photo_1', $image_photo_1);
			update_post_meta($post_id, '_additional_photo_1', 'field_637c9a9e16895');
		}
		if(!empty($entry['21'])){
			$image_url_photo_2 = trim(str_replace( array( '\'', '"', ',' , ';', '<', '>', '[', ']' ), ' ', $entry['21']));
			$image_photo_2 = entry_upload_file_by_url($image_url_photo_2);
			update_post_meta($post_id, 'additional_photo_2', $image_photo_2 );
			update_post_meta($post_id, '_additional_photo_2', 'field_637c9ad016896');
		}

		if(!empty($entry['39'])){
			$balance_sheets = trim(str_replace( array( '\'', '"', ',' , ';', '<', '>', '[', ']' ), ' ', $entry['39']));
			$image_balance_sheets = entry_upload_file_by_url($balance_sheets);
			update_post_meta($post_id, 'balance_sheets', $image_balance_sheets);
			update_post_meta($post_id, '_balance_sheets', 'field_637ca0281689f');
		}
		if(!empty($entry['37'])){
			$future_profitability = trim(str_replace( array( '\'', '"', ',' , ';', '<', '>', '[', ']' ), ' ', $entry['37']));
			$image_future_profitability = entry_upload_file_by_url($future_profitability);
			update_post_meta($post_id, 'future_profitability_and_competitive_performance', $image_future_profitability);
			update_post_meta($post_id, '_future_profitability_and_competitive_performance', 'field_637c9f951689d');
		}
		if(!empty($entry['42'])){
			$image_url_photo_2 = trim(str_replace( array( '\'', '"', ',' , ';', '<', '>', '[', ']' ), ' ', $entry['42']));
			$image_photo_2 = entry_upload_file_by_url($image_url_photo_2);
			update_post_meta($post_id, 'income_cashflow_statements', $image_photo_2 );
			update_post_meta($post_id, '_income_cashflow_statements', 'field_637ca06e168a1');
		}
		//Repeater Features 
		if(!empty($entry['1000']))
		{
			foreach($entry['1000'] as $features)
			{
				$field_keys=array("field_637c9c061689a","field_637c9c191689b");
				$row=array_combine($field_keys, $features);
				add_row('field_637c9bfa16899', $row, $post_id);
			}
		}
		// GFAPI::delete_entry( $entry['id'] );
	}
/**
 * File and image upload to media
 */
function entry_upload_file_by_url( $image_url ) {
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	$temp_file = download_url( stripslashes($image_url) );
	if( is_wp_error( $temp_file ) ) {
		return false;
	}
	$file = array(
		'name'     => basename( $image_url ),
		'type'     => mime_content_type( $temp_file ),
		'tmp_name' => $temp_file,
		'size'     => filesize( $temp_file ),
	);
	$sideload = wp_handle_sideload($file,array('test_form'=> false));
	$attachment_id = wp_insert_attachment(
		array(
			'guid'           => $sideload[ 'url' ],
			'post_mime_type' => $sideload[ 'type' ],
			'post_title'     => basename( $sideload[ 'file' ] ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$sideload[ 'file' ]
	);
	if( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return false;
	}
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	wp_update_attachment_metadata($attachment_id,wp_generate_attachment_metadata( $attachment_id, $sideload[ 'file' ] ));
	return $attachment_id;
}
/**
 * 
 */
// Use term_id instead of name to assign the taxonomy
add_action( 'gform_advancedpostcreation_post_after_creation_3', 'make_an_offer_postcreation', 10, 4 );
function make_an_offer_postcreation( $post_id, $feed, $entry, $form ){
	if(!empty($entry['14']))
	{
		update_post_meta( $post_id, 'offer_for_post_id', $entry['14'] );
	}
	if(!empty($entry['17'])){
		$image_url_photo_2 = trim(str_replace( array( '\'', '"', ',' , ';', '<', '>', '[', ']' ), ' ', $entry['17']));
		$image_photo_2 = entry_upload_file_by_url($image_url_photo_2);
		update_post_meta($post_id, 'letter_of_intent', $image_photo_2 );
		update_post_meta($post_id, '_letter_of_intent', 'field_640f1bb4a60fb');
	}

}
/**
 * Check made a offer for post
 */
function check_offer_post($post_id = '')
{
	if(is_user_logged_in() && is_singular( 'startup' ))
	{
		$post_id = $post_id ? $post_id : get_the_ID();
		$args = array(
			'post_type' 	=> 'offers',
			'post_status' 	=> 'publish',
			'author'        =>  get_current_user_id(),
			'posts_per_page'=> -1,
			'meta_key'      => 'offer_for_post_id',
			'meta_value'    => $post_id
		);
		$posts = get_posts($args);
		return count($posts) > 0 || get_current_user_id() == get_post($post_id)->post_author ? 1 : 0;
	}
}
add_shortcode( 'business_profile_title', 'get_name_business_profile' );
function get_name_business_profile()
{
	ob_start();
	if(is_user_logged_in() && is_singular( 'offers' ))
	{ 
		$business_profile_post_id = get_post_meta(get_the_ID(), 'offer_for_post_id', true);
		$post_thumbnail = get_the_post_thumbnail_url($business_profile_post_id) ? get_the_post_thumbnail_url($business_profile_post_id) : '';

		$author_id = get_post($business_profile_post_id)->post_author
		
		?>
		<div class="offer-single-main-cls">
			<div class="offer-post-heading">
				<h2>
					<?php
					$ret = '';
					foreach (explode(' ', get_the_title($business_profile_post_id)) as $word)
						$ret .= strtoupper($word[0]);

					echo $ret."#".$business_profile_post_id; // would output "PIVS"
					?>
				</h2>
			</div>
			<div class="offer-post-header-btn">
				<div class="left-side-btn">
					<a href="<?=site_url('offers-sent')?>">Back</a>
					<a href="#">All Message</a>
				</div>
				<div class="right-side-btn">
					<a href="mailto:<?php echo get_the_author_meta('user_email', $author_id);?>">Contact Seller</a>
					<?php 
						$count_comment = wp_count_comments(get_the_ID());
						if(get_current_user_id() == get_post($business_profile_post_id)->post_author && $count_comment->approved == 0){
							echo '<a href="javascript:void(0)" class="offers-revice-cls">Revice Offer</a>';
						}
					?>
					
					<a href="javascript:void(0)" data-id="<?=get_the_ID()?>" id="cancel_offer">Cancel Offer <i class="fas fa-spinner fa-spin"></i></a>
				</div>
			</div>
			
			<div class="offer-post-single">
				<div class="offer-post-details-cls">
					<?php 
						printf('<img src="%s">',$post_thumbnail); 
						printf('<h4>%s</h4>',get_the_title( $business_profile_post_id )); 
					?>
				</div>

				<table class="subs-user-list-cls">
					<thead>
						<tr>
						<th>Offer Name</th>
						<?php 
							printf('<th>%s #%s</th>',get_the_title( $business_profile_post_id ), $business_profile_post_id); 
						?>
						<tr>
					</thead>
					<tbody>
						<tr>
							<th>Offer Value</th>
							<?php printf('<td>$%s</td>', number_format((int)get_post_meta( get_the_ID(), 'offer_value', true ),0,".",",")); ?>
						</tr>
						<tr>
							<th>Sent Date</th>
							<td><?php echo sprintf( esc_html__( '%s ago', 'textdomain' ), human_time_diff(get_the_time ( 'U' ), current_time( 'timestamp' ) ) ). ': ' .get_the_date('d/m/Y'); ?></td>
						</tr>
						<tr>
							<th>Replies & Coutner Offers</th>
							<td><?=$count_comment->approved?></td>
						</tr>
					</tbody>
				</table>
			</div>

		</div>
	<?php }
	$output = ob_get_contents();
	ob_get_clean();
	return $output;	
	// return get_the_title( get_post_meta(get_the_ID(), 'offer_for_post_id', true) );
}
/**
 * Delete Offer
 */
add_action( 'wp_ajax_cancel_offer', 'cancel_offer_fun' );
function cancel_offer_fun()
{
	$nonce = $_POST['nonce'];

    if ( ! wp_verify_nonce( $nonce, 'savior_nonce' ) ) {
		wp_send_json_error(array('error_type' => 'nonce_not_verified', 'message' => 'Nonce value cannot be verified.'));
    }
	$args = array('author' => get_current_user_id(),'post_type'=> 'offers','p' => $_REQUEST['post_id'] );
	$author_posts = new WP_Query( $args );
	if(!isset( $_REQUEST ))
	{
		wp_send_json_error(array('error_type' => 'no_data', 'message' => 'Request not fount'));
	}

    if (is_user_logged_in() && $author_posts->post_count >= 1) { 
		$delete_post = wp_delete_post($_REQUEST['post_id']);
		if ( is_wp_error( $delete_post ) ) {
			$error_string = $delete_post->get_error_message();
			wp_send_json_error(array('error_type' => $delete_post->get_error_code(), 'message' => $error_string));
		}else{
			wp_send_json_success(array('message'=>'Offer deleted','site_url'=> site_url('offers-sent')));
		}
	}else{
		wp_send_json_error(array('error_type' => 'author_post', 'message' => 'Your are not author of this offer'));
	}
}
require_once('inc/offer-received.php');

add_filter( 'comments_open', 'offers_comments_open', 10, 2 );
function offers_comments_open( $open, $post_id ) {
  $post = get_post( $post_id );
  if ( 'offers' == $post->post_type )
      $open = true;

  return $open;
}
function add_counter_offer_comment( $field, $postId ) {
    $current_user = wp_get_current_user();
	error_log(print_r('comment Start',true));
    if ( comments_open( $postId ) ) {
        $data = array(
            'comment_post_ID'      => $postId,
            'comment_content'      => $field['comment'],
            'comment_parent'       => $field['comment_parent'],
            'user_id'              => $current_user->ID,
            'comment_author'       => $current_user->user_login,
            'comment_author_email' => $current_user->user_email,
            'comment_author_url'   => $current_user->user_url,
            'comment_meta'         => $field['comment_meta']
        );

        $comment_id = wp_insert_comment( $data );
        if ( ! is_wp_error( $comment_id ) ) {
            return $comment_id;
        }else{
			// $error_string = $comment_id->get_error_message();
			error_log(print_r('add_counter_offer_comment',true));
			error_log(print_r($comment_id->get_error_message(),true));
		}
    }else{
		error_log(print_r('no comment opend',true));
	}

    return false;
}
add_filter( 'gform_after_submission_7', 'after_submission_counter_offers', 10, 2 );
function after_submission_counter_offers(  $entry, $form  ) { 
	if(is_singular( 'offers' )){
		error_log(print_r('$entry',true));

		$offer_comment_field = array(
			'comment'			=> rgar( $entry, '20' ),
			'comment_parent'	=> '',
			'comment_meta'		=>	array(
				'offer_received' 	=> rgar( $entry, '18' ),
				'counter_offer' 	=> rgar( $entry, '19' ),
			)
		);
		if ( comments_open( rgar( $entry, '14' ) ) )
		{
			$offer_comment_id = add_counter_offer_comment( $offer_comment_field, rgar( $entry, '14' ) );
			error_log(print_r($offer_comment_id,true));
		}
	}
	
}
add_shortcode( 'test', function(){
	echo"<pre>";

	// $args = array('author' => 6,'post_type'=> 'offers','p' => 2185 );
// $author_posts = new WP_Query( $args );

		// print_r($author_posts->post_count);echo"<br>";
		print_r(get_post_meta(2201));echo"<br>";

	// $upload_dir = wp_upload_dir(); //uploads dir
	// $resume_url = explode('uploads', 'http://sandd.saviormarketing.com/wp-content/uploads/gravity_forms/6-271859ad24a0aa15fc8cd0bb004709f1/2023/03/image33.jpg');
	
	// print_r($resume_url);
	// $resume_file_name = end($resume_url );
    // $upload_dir = wp_upload_dir(); //uploads dir
    
    // echo $full_path = $upload_dir['basedir'] . $resume_file_name; //get full path of file
    // wp_delete_file( $full_path ); //delete the file

		echo"</pre>";
});