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
			'nonce'   => wp_create_nonce( 'savior_nonce' )
		)
	);

	if(is_singular('startup')){
		wp_enqueue_style( 'savior-pro-mCustomScrollbar-styles', get_stylesheet_directory_uri() . '/inc/mCustomScrollbar/mCustomScrollbar.min.css', array(), time(), 'all' );
		wp_enqueue_script( 'savior-pro-mCustomScrollbar-scripts', get_stylesheet_directory_uri() . '/inc/mCustomScrollbar/mCustomScrollbar.js', array('jquery'), time(), true );
	}
}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

/** Get Percentage and Substraction Start **/

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
						<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
						<div class="startup-post-list-cls">
							<div class="post-thumbnail-cls">
								<?php the_post_thumbnail('thumbnail');?>
							</div>
							<h2 class="post-title"><?php the_title(); ?></h2>
							<?php $categories = get_the_terms($post->ID, 'startup_tags'); ?>
									<div class="tag-term-cls">
										<?php
											foreach ($categories as $category) {
												echo $category->name.','; 
											}
										?>
									</div>
							<div class="custom-fields-cls">
								<h5>Founded:</h5>
								<h5>
									<?php 
										$field_date = strtotime(get_field( "year_of_incorporation", $post->ID ));
										echo date_i18n( "Y", $field_date );
									?>
								</h5>
							</div>
							<div class="custom-fields-cls">
								<h5>Employees:</h5>
								<h5>
									<?php 
										$employees_num = get_field( "employees", $post->ID );
										echo $employees_num;
									?>
								</h5>
							</div>
							<div class="custom-fields-cls">
								<h5>MRR:</h5>
								<h5>
									<?php 
										$mrr_num = get_field( "mrr", $post->ID );
										echo '$'. $mrr_num;
									?>
								</h5>
							</div>
							<div class="custom-fields-cls">
								<h5>CLTV:</h5>
								<h5>
									<?php 
										$cltv_num = get_field( "cltv", $post->ID );
										echo '$'. $cltv_num;
									?>
								</h5>
							</div>
							<div class="custom-fields-cls">
								<h5>CAC:</h5>
								<h5>
									<?php 
										$cac_num = get_field( "cac", $post->ID );
										echo '$'. $cac_num;
									?>
								</h5>
							</div>
							<div class="post-single-btn">
								<a href="<?php echo get_permalink( $post->ID ); ?>">View Company</a>
							</div>
						</div>		
					<?php endwhile;
						wp_reset_postdata(); ?>
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
									<a href="<?php echo get_permalink( $post->ID ); ?>">
								  <table>
									<tr>
									  <th>Offer Name</th>
									  <th><?php the_title(); ?></th>
									</tr>
									<tr>
									  <td>Offer Value</td>
										<?php 
											$offer_value_num = get_field( "offer_value", $post->ID );
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

function watchlists_add_tag_func(){
	ob_start();
	$args = array(
		'taxonomy'  => 'startup_tags',
		'hide_empty' => false
	);
	$startup_tags = get_terms( $args );
	$get_post_terms = wp_get_post_terms(get_the_ID(), 'startup_tags', array('fields'=> 'ids'));

	?>
	<div class="watchlists-tags-main-sec">
		<ul>
			<?php 
				foreach( $startup_tags as $startup_tag){
					$checked_user_post_tag = in_array( $startup_tag->term_id, $get_post_terms ) ? 'checked="checked"' : '';
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
	$output = ob_get_contents();
	ob_get_clean();
	return $output;	
}
add_shortcode('watchlists_add_tag', 'watchlists_add_tag_func');
/**
 * Add new startup tag
 */
add_action( 'wp_ajax_add-startup-tags', 'add_new_startup_tag_fun' );
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
			$post_tag_obj = wp_insert_category(array(
				'taxonomy'             => $taxonomy,
				'cat_name'             => $newstartup_tags,
			));
			if ( is_wp_error( $post_tag_obj ) ) {
				$error_string = $post_tag_obj->get_error_message();
				wp_send_json_error(array('error_type' => $post_tag_obj->get_error_code(), 'message' => $error_string));
			}else{
				$user_checked_tag 	= get_user_meta( $user_id, 'startup_tag_ids', true );
				if(empty($user_checked_tag)){
					update_user_meta($user_id, 'startup_tag_ids', array($post_id => array($post_tag_obj)));
				}else{
					if(array_key_exists($post_id, $user_checked_tag))
					{
						$recursive = array_unique( array_merge($user_checked_tag[$post_id], array($post_tag_obj)) );
						update_user_meta($user_id, 'startup_tag_ids', $recursive);
					}else{
						$recursive = array_replace_recursive($user_checked_tag, array($post_id => array($post_tag_obj)));
						update_user_meta($user_id, 'startup_tag_ids', $recursive);
					}
				}
				wp_send_json_success($post_tag_obj);
			}
			wp_send_json_success($post_tag_obj);
		}
    }else{
		wp_send_json_error(array('error_type' => 'no_data', 'message' => 'No data.'));
	}
}
 