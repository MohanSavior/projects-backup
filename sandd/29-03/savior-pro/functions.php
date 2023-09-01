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
			'current' => get_current_user_details()
		)
	);

	if(is_singular('startup') || is_page(1796)){
		wp_enqueue_style( 'savior-pro-mCustomScrollbar-styles', get_stylesheet_directory_uri() . '/inc/mCustomScrollbar/mCustomScrollbar.min.css', array(), time(), 'all' );
		wp_enqueue_script( 'savior-pro-mCustomScrollbar-scripts', get_stylesheet_directory_uri() . '/inc/mCustomScrollbar/mCustomScrollbar.js', array('jquery'), time(), true );
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

	$income_cashflow_statements = get_field('income_&_cashflow_statements');	
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
		
		<div class="offer-list-post-main-cls">
			<div class="startup-post-sec-cls">
				<?php 
					$args = array( 'post_type' => 'startup', 'posts_per_page' => 1 );
					$the_query = new WP_Query( $args ); 
					?>
					<?php if ( $the_query->have_posts() ) : ?>
						<?php 
							while ( $the_query->have_posts() ) : $the_query->the_post(); 
							echo do_shortcode('[startup_post_template post_id="'.get_the_ID().'"]');
							endwhile;
						wp_reset_postdata(); 
						?>
					<?php else:  ?>
					<p><?php _e( 'Sorry, no posts matched your criteria.' ); ?></p>
					<?php endif; ?>
			</div>
			
			<div class="offer-post-sec-cls">
				<?php 
					$args = array( 'post_type' => 'offers', 'posts_per_page' => 1 );
					$the_query = new WP_Query( $args ); 
					?>
					<?php if ( $the_query->have_posts() ) : ?>
						<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
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
										<?php 
											$offer_value_num = get_field( "offer_value", get_the_ID() );
										?>	
									  <td><?php echo $offer_value_num; ?></td>
									</tr>
									<tr>
									  <td>Sent Date</td>
									  <td>1 day ago: 8/8/22 </td>
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
					<?php endwhile;
						wp_reset_postdata(); ?>
					<?php else:  ?>
						<p><?php _e( 'Sorry, no posts matched your criteria.' ); ?></p>
					<?php endif; ?>
			</div>
			
		</div>
		
	</div>
	<?php
	$output = ob_get_clean();
	return $output;	
}
add_shortcode('offer_sent_layout', 'offer_sent_layout_func');
// Offer Sent Layout

// Offer sent file

function offer_sent_file_func(){
	ob_start();

	$letter_of_intent = get_field('letter_of_intent');	
	echo '<div class="letter_of_intent_file_cls">';
		echo  'Document: <a href="'.$letter_of_intent['url'].'" target="_Blank">'.$letter_of_intent['filename'].'</a> <i aria-hidden="true" class="fas fa-trash"></i>';
	echo '</div>';

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
		$tag_added 			= array_key_exists(get_the_ID(), $user_checked_tag) ? $user_checked_tag[get_the_ID()] : [];
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
        die( 'Nonce value cannot be verified.' );
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
        die( 'Nonce value cannot be verified.' );
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
        die( 'Nonce value cannot be verified.' );
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
function pre_render_startup_tags( $form ) {
 
    foreach( $form['fields'] as &$field )  {
        $field_id = 7;
        if ( $field->id != $field_id ) {
            continue;
        }
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
    return $form;
}

// Pre Render Category
add_filter( 'gform_pre_render_6', 'pre_render_startup_cat' );
function pre_render_startup_cat( $form ) {
 
    foreach( $form['fields'] as &$field )  {
        $field_id = 1003;
        if ( $field->id != $field_id ) {
            continue;
        }
		$args = array(
			'taxonomy'  => 'startup_categories',
			'hide_empty' => false
		);
		$startup_cats = get_terms( $args );
 
        foreach( $startup_cats as $post_startup_cats ) {
            $choices[] = array( 'text' => $post_startup_cats->name, 'value' => $post_startup_cats->term_id );
        }
        $field->choices = $choices;
    }
    return $form;
}


add_shortcode( 'test', function(){
	echo"<pre>";
	// print_r(update_user_meta(6, 'startup_tag_ids', ''));
	print_r(get_user_meta(6, 'startup_tag_ids', true));

		echo"</pre>";
});